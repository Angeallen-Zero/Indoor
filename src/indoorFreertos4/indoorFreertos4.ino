/*
 * Proyecto: Invernadero Inteligente con ESP32, FreeRTOS y Firebase
 * Funciones:
 * 1. Lee sensor de humedad capacitivo y lo sube a Firebase.
 * 2. Controla relé de bomba de agua según umbral en Firebase.
 * 3. Controla relé de ventilador según switch en Firebase.
 * 4. Controla relé de luces según horario en Firebase, usando NTP para la hora.
 * Utiliza FreeRTOS para tareas concurrentes y Mutex para proteger datos compartidos y escrituras de Firebase.
 *
 * *** CÓDIGO CORREGIDO (Versión Estable) ***
 * - Añadido Mutex de escritura (xFirebaseWriteMutex) para evitar conflictos de red.
 * - Movidos los objetos FirebaseData de escritura a globales (fbdo_sensor_writer, fbdo_control_writer) para prevenir Fragmentación de Heap.
 * - Aumentados los Stacks de las tareas a 12288 y 24576 para prevenir Stack Overflow.
 */

// --- 1. BIBLIOTECAS Y DEFINICIONES ---
#include <WiFi.h>
#include <WiFiManager.h>
#include <Firebase_ESP_Client.h>
#include "addons/RTDBHelper.h" // <-- necesario para FirebaseStream y helpers
#include <NTPClient.h>
#include <WiFiUdp.h>


// --- Configuración de Firebase ---


// --- Configuración de Hardware (Pines) ---
#define SOIL_SENSOR_PIN 35  // Pin ADC para el sensor capacitivo
#define PUMP_RELAY_PIN  25  // Relé para la bomba de agua
#define FAN_RELAY_PIN   26  // Relé para el ventilador
#define LIGHT_RELAY_PIN 27  // Relé para las luces
#define RESET_BUTTON    32  // boton de reset wifi

// --- Lógica de Relés (Ajusta según tu hardware) ---
#define RELAY_ON  LOW
#define RELAY_OFF HIGH

// --- Calibración del Sensor (¡IMPORTANTE!) ---
#define SENSOR_SECO_VAL 3100  // Valor ADC con el sensor al aire (ejemplo)
#define SENSOR_HUMEDO_VAL 1250 // Valor ADC con el sensor en agua (ejemplo)

// --- Configuración de Tareas ---
#define SENSOR_TASK_CORE 1   // Tarea de sensores en el Core 0 (con WiFi)
#define CONTROL_TASK_CORE 1  // Tarea de actuadores en el Core 1 (separado)

// --- Objetos Globales ---
FirebaseData fbdo;
// Objeto global SOLO para el stream (lectura)

// --- INICIO DE CORRECCIÓN 1 ---
// Se usa un objeto ÚNICO para todas las escrituras
FirebaseData fbdo_writer;  
// --- FIN DE CORRECCIÓN 1 ---

FirebaseAuth auth;
FirebaseConfig config;
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "mx.pool.ntp.org", -21600); // UTC-6 (CDMX) = -21600 segundos.
WiFiManager wm;

// --- 2. ESTADO GLOBAL COMPARTIDO ---
SemaphoreHandle_t xStateMutex; // <-- Protege las variables g_
SemaphoreHandle_t xFirebaseWriteMutex; // <-- Protege las escrituras a Firebase

float g_currentSoilMoisture = 0.0;
int   g_umbralHumedad = 30;
bool  g_ventilacionEstado = false;
String g_horaEncendido = "08:00";
String g_horaApagado = "18:00";

// --- 3. PROTOTIPOS DE TAREAS Y FUNCIONES ---
void vTaskReadSensors(void *pvParameters);
void vTaskActuatorControl(void *pvParameters);
void streamCallback(FirebaseStream data);
void streamTimeoutCallback(bool timeout);
void initNTP();
void initFirebase();

// --- 4. FUNCIÓN DE SETUP ---
void setup() {
  Serial.begin(115200);
  delay(100);

  pinMode(RESET_BUTTON, INPUT_PULLUP);
  wm.setConfigPortalTimeout(180);
  if (digitalRead(RESET_BUTTON) == LOW) {
    Serial.println("Borrando configuración WiFi...");
    wm.resetSettings();
    delay(1000);
  }

  Serial.println("Iniciando WiFiManager...");
  if (!wm.autoConnect("Indoor")) {
    Serial.println("No se pudo conectar o tiempo agotado. Reiniciando...");
    delay(2000);
    ESP.restart();
  }

  Serial.println("Conectado a WiFi!");
  Serial.println("Iniciando Invernadero Inteligente...");

  pinMode(PUMP_RELAY_PIN, OUTPUT);
  pinMode(FAN_RELAY_PIN, OUTPUT);
  pinMode(LIGHT_RELAY_PIN, OUTPUT);

  digitalWrite(PUMP_RELAY_PIN, RELAY_OFF);
  digitalWrite(FAN_RELAY_PIN, RELAY_OFF);
  digitalWrite(LIGHT_RELAY_PIN, RELAY_OFF);
  pinMode(SOIL_SENSOR_PIN, INPUT);

  // --- CORRECCIÓN DE MUTEX ---
  // Crear ambos Mutex
  xStateMutex = xSemaphoreCreateMutex();
  xFirebaseWriteMutex = xSemaphoreCreateMutex(); // <-- Creado
  
  if (xStateMutex == NULL || xFirebaseWriteMutex == NULL) { // <-- Comprobado
      Serial.println("Error al crear un Mutex. Reiniciando...");
      delay(1000);
      ESP.restart();
  }
  // --- FIN CORRECCIÓN ---

  initNTP();
  initFirebase();

  Serial.println("Creando tareas de FreeRTOS...");
  // --- CORRECCIÓN DE STACK ---
  xTaskCreatePinnedToCore(
      vTaskReadSensors,
      "Task_ReadSensors",
      12288,  // <-- Aumentado para la escritura SSL
      NULL,
      1,
      NULL,
      SENSOR_TASK_CORE);

  xTaskCreatePinnedToCore(
      vTaskActuatorControl,
      "Task_ActuatorControl",
      24576,  // <-- Aumentado masivamente para 3x escrituras SSL + lógica
      NULL,
      2,
      NULL,
      CONTROL_TASK_CORE);
  // --- FIN CORRECCIÓN ---

  Serial.println("Setup completado. FreeRTOS tiene el control.");
  vTaskDelete(NULL);
}

// --- 5. LOOP (VACÍO) ---
void loop() {
  // FreeRTOS se encarga de todo
  vTaskDelay(pdMS_TO_TICKS(1000)); // El loop puede dormir
}

// --- 6. DEFINICIÓN DE TAREAS DE FREERTOS ---
void vTaskReadSensors(void *pvParameters) {
    TickType_t xLastWakeTime;
    const TickType_t xFrequency = pdMS_TO_TICKS(10000); // 10 segundos
    xLastWakeTime = xTaskGetTickCount();

    for (;;) {
        vTaskDelayUntil(&xLastWakeTime, xFrequency);

        int rawValue = analogRead(SOIL_SENSOR_PIN);
        long mappedValue = map(rawValue, SENSOR_SECO_VAL, SENSOR_HUMEDO_VAL, 0, 100);
        float humedad = constrain(mappedValue, 0, 100);

        if (xSemaphoreTake(xStateMutex, (TickType_t)10) == pdTRUE) {
            g_currentSoilMoisture = humedad;
            xSemaphoreGive(xStateMutex);
        } else {
            Serial.println("[Sensores] Advertencia: No se pudo adquirir Mutex (Estado).");
        }

        Serial.printf("[Sensores] Humedad del suelo: %.1f%% (Raw: %d)\n", humedad, rawValue);

        if (WiFi.status() == WL_CONNECTED && Firebase.ready()) {
            
            // --- CORRECCIÓN DE TIMEOUT DE MUTEX ---
            // Esperar hasta 1.5 segundos (1500ms) en lugar de 10ms
            if (xSemaphoreTake(xFirebaseWriteMutex, pdMS_TO_TICKS(1500)) == pdTRUE) {
            // --- FIN CORRECCIÓN ---
         
                // --- INICIO DE CORRECCIÓN 1 ---
                // Usar el objeto de escritura unificado
                if (!Firebase.RTDB.setFloat(&fbdo_writer, "/sensores/humedadSuelo", humedad)) {
                    Serial.println("[Firebase] Error al enviar datos de humedad: " + fbdo_writer.errorReason());
                }
                // --- FIN DE CORRECCIÓN 1 ---

                vTaskDelay(1);
                
                xSemaphoreGive(xFirebaseWriteMutex);
            } else {
                // Este error ya no debería ocurrir, o será muy raro.
                Serial.println("[Sensores] Advertencia: No se pudo adquirir Mutex (Escritura Firebase).");
            }
        }
    }
}

void vTaskActuatorControl(void *pvParameters) {
    TickType_t xLastWakeTime;
    // --- PRUEBA: Cambiado a 7 segundos para reducir la carga de red ---
    const TickType_t xFrequency = pdMS_TO_TICKS(7000); // 7 segundos (en lugar de 2000)
    xLastWakeTime = xTaskGetTickCount();

    float localSoil, localUmbral;
    bool localFan;
    String localOn, localOff;
    String estadoBomba, estadoVentilador, estadoLuces;

    // --- OPTIMIZACIÓN: Crear el objeto JSON una vez, fuera del bucle ---
    FirebaseJson json_estado;

    for (;;) {
        vTaskDelayUntil(&xLastWakeTime, xFrequency);

        timeClient.update();

        if (xSemaphoreTake(xStateMutex, (TickType_t)10) == pdTRUE) {
            localSoil   = g_currentSoilMoisture;
            localUmbral = g_umbralHumedad;
            localFan    = g_ventilacionEstado;
            localOn     = g_horaEncendido;
            localOff    = g_horaApagado;
            xSemaphoreGive(xStateMutex);
        } else {
            Serial.println("[Control] Advertencia: No se pudo adquirir Mutex (Estado). Saltando ciclo.");
            continue;
        }

        // ... (Toda tu lógica de control para Riego, Ventilación y Luces va aquí ... ) ...
        // 1. Control de Riego (Bomba)
        if (localSoil < localUmbral) {
            digitalWrite(PUMP_RELAY_PIN, RELAY_ON);
            estadoBomba = "ON";
        } else {
            digitalWrite(PUMP_RELAY_PIN, RELAY_OFF);
            estadoBomba = "OFF";
        }
        // 2. Control de Ventilación
        if (localFan) {
            digitalWrite(FAN_RELAY_PIN, RELAY_ON);
            estadoVentilador = "ON";
        } else {
            digitalWrite(FAN_RELAY_PIN, RELAY_OFF);
            estadoVentilador = "OFF";
        }
        // 3. Control de Iluminación (por Horario)
        int currentHour = timeClient.getHours();
        int currentMinute = timeClient.getMinutes();
        float currentTimeInMinutes = (currentHour * 60) + currentMinute;
        int onHour = localOn.substring(0, 2).toInt();
        int onMin = localOn.substring(3, 5).toInt();
        float onTimeInMinutes = (onHour * 60) + onMin;
        int offHour = localOff.substring(0, 2).toInt();
        int offMin = localOff.substring(3, 5).toInt();
        float offTimeInMinutes = (offHour * 60) + offMin;
        bool shouldBeOn = false;
        if (onTimeInMinutes < offTimeInMinutes) {
            if (currentTimeInMinutes >= onTimeInMinutes && currentTimeInMinutes < offTimeInMinutes) {
                shouldBeOn = true;
            }
        } else {
            if (currentTimeInMinutes >= onTimeInMinutes || currentTimeInMinutes < offTimeInMinutes) {
                shouldBeOn = true;
            }
        }
        if (shouldBeOn) {
            digitalWrite(LIGHT_RELAY_PIN, RELAY_ON);
            estadoLuces = "ON";
        } else {
            digitalWrite(LIGHT_RELAY_PIN, RELAY_OFF);
            estadoLuces = "OFF";
        }
        // ... (Fin de la lógica de control) ...


        // --- CORRECCIÓN DE FRAGMENTACIÓN Y CONFLICTO (VERSIÓN OPTIMIZADA) ---
        if (WiFi.status() == WL_CONNECTED && Firebase.ready()) {
            
            // Pedir el Mutex de ESCRITURA (esperar hasta 1 segundo)
            if (xSemaphoreTake(xFirebaseWriteMutex, pdMS_TO_TICKS(1000)) == pdTRUE) 
            {
                
                // --- INICIO DE LA OPTIMIZACIÓN ---
                // Configurar los 3 valores en el objeto JSON
                json_estado.set("bomba", estadoBomba);
                json_estado.set("ventilador", estadoVentilador);
                json_estado.set("luces", estadoLuces);

                // --- INICIO DE CORRECCIÓN 1 ---
                // Enviar el JSON completo a la ruta /estado en UNA sola operación
                // Usar el objeto de escritura unificado
                if (!Firebase.RTDB.updateNode(&fbdo_writer, "/estado", &json_estado)) {
                    Serial.println("[Firebase] Error al actualizar /estado: " + fbdo_writer.errorReason());
                }
                // --- FIN DE CORRECCIÓN 1 ---
                // --- FIN DE LA OPTIMIZACIÓN ---

                // Soltar el Mutex de ESCRITURA
                xSemaphoreGive(xFirebaseWriteMutex);
            } else {
                 Serial.println("[Control] Advertencia: No se pudo adquirir Mutex (Escritura Firebase).");
            }
        }
        // --- FIN CORRECCIÓN ---

        Serial.printf("[Control] Riego: %s (%.1f%% < %d%%) | Vent: %s | Luces: %s (%s)\n",
                      estadoBomba.c_str(), localSoil, (int)localUmbral,
                      estadoVentilador.c_str(), estadoLuces.c_str(),
                      timeClient.getFormattedTime().c_str());
    }
}

// --- 7. FUNCIONES AUXILIARES ---
void initNTP() {
  Serial.println("Iniciando cliente NTP (mx.pool.ntp.org)...");
  timeClient.begin();
  Serial.print("Sincronizando hora...");

  int attempts = 0;
  while (!timeClient.forceUpdate() && attempts < 30) {
    Serial.print(".");
    vTaskDelay(pdMS_TO_TICKS(1000)); // Espera 1 segundo
    attempts++;
  }

  if (attempts >= 30) {
    Serial.println("\n--- ERROR FATAL: No se pudo sincronizar NTP ---");
    Serial.println("Firebase fallará. Reiniciando en 10s...");
    delay(10000);
    ESP.restart();
  } else {
    Serial.println("\n¡Hora NTP sincronizada! " + timeClient.getFormattedTime());
  }
}

void initFirebase() {
    Serial.println("Iniciando Firebase...");
    config.host = FIREBASE_HOST;
    config.api_key = FIREBASE_AUTH;
    config.database_url = FIREBASE_DATABASE_URL;
    
    auth.user.email = "angel.ramos.jain@estudiante.uacm.edu.mx";
    auth.user.password = "";

    Serial.println("Configurando Firebase...");
    Firebase.begin(&config, &auth);
    Firebase.reconnectWiFi(true);

    Serial.print("Conectando a Firebase...");
    int waitCnt = 0;
    while (!Firebase.ready() && waitCnt < 40) {
        Serial.print(".");
        delay(500);
        waitCnt++;
    }
    
    if (!Firebase.ready()) {
        Serial.println("\nERROR: No se pudo conectar a Firebase.");
        Serial.println("Motivo: " + fbdo.errorReason());
    } else {
        Serial.println("\nFirebase conectado!");
        Serial.println("Iniciando stream en la ruta /control");
        // Usar el objeto 'fbdo' global SOLO para el stream
        if (!Firebase.RTDB.beginStream(&fbdo, "/control")) {
            Serial.println("Error al iniciar el stream: " + fbdo.errorReason());
        } else {
            Serial.println("Stream iniciado exitosamente.");
            Firebase.RTDB.setStreamCallback(&fbdo, streamCallback, streamTimeoutCallback);
        }
    }
}

void streamCallback(FirebaseStream data) {
    // Imprime la ruta y el tipo de dato recibido. ¡Muy útil para depurar!
    Serial.printf("[Firebase Stream] Datos recibidos. Path: %s, Tipo: %s\n", data.dataPath().c_str(), data.dataType().c_str());

    if (xSemaphoreTake(xStateMutex, (TickType_t)10) == pdTRUE) {

        // CASO 1: El path es "/umbralHumedad" (Actualización simple del slider)
        if (data.dataPath() == "/umbralHumedad") {
            if (data.dataType() == "int" || data.dataType() == "number") {
                g_umbralHumedad = data.to<int>();
                Serial.printf("[Firebase Stream] Nuevo umbral de humedad: %d\n", g_umbralHumedad);
            } else {
                Serial.printf("[Firebase Stream] ERROR: /umbralHumedad no es 'int', es '%s'\n", data.dataType().c_str());
            }
        }
        
        // --- INICIO DE CORRECCIÓN ---
        // CASO 2: El path es "/ventilacion" (Actualización simple del toggle)
        else if (data.dataPath() == "/ventilacion") {
            bool newState = false;
            bool updateSuccess = false;

            // --- CAMBIO AQUÍ: "bool" -> "boolean" ---
            if (data.dataType() == "boolean") { 
                newState = data.to<bool>();
                updateSuccess = true;
            } 
            else if (data.dataType() == "string") {
                newState = (data.to<String>() == "true");
                updateSuccess = true;
            } 
            else if (data.dataType() == "int") {
                newState = (data.to<int>() == 1);
                updateSuccess = true;
            }

            if (updateSuccess) {
                g_ventilacionEstado = newState;
                Serial.printf("[Firebase Stream] Nuevo estado de ventilación: %s\n", g_ventilacionEstado ? "ON" : "OFF");
            } else {
                 Serial.printf("[Firebase Stream] ERROR: /ventilacion no es 'boolean' ni 'string' ni 'int', es '%s'\n", data.dataType().c_str());
            }
        }
        // --- FIN DE CORRECCIÓN ---

        // CASO 3: El path es "/iluminacion" (Actualización del objeto de luces)
        else if (data.dataPath() == "/iluminacion") {
            if (data.dataType() == "json") {
                FirebaseJson *jsonIluminacion = data.to<FirebaseJson *>();
                FirebaseJsonData jsonHora;

                if (jsonIluminacion->get(jsonHora, "horaEncendido")) {
                    if (jsonHora.type == "string") {
                        g_horaEncendido = jsonHora.to<String>();
                        Serial.printf("[Firebase Stream] Nueva hora de encendido: %s\n", g_horaEncendido.c_str());
                    }
                }
                if (jsonIluminacion->get(jsonHora, "horaApagado")) {
                    if (jsonHora.type == "string") {
                        g_horaApagado = jsonHora.to<String>();
                        Serial.printf("[Firebase Stream] Nueva hora de apagado: %s\n", g_horaApagado.c_str());
                    }
                }
            } else {
                Serial.printf("[Firebase Stream] ERROR: /iluminacion no es 'json', es '%s'\n", data.dataType().c_str());
            }
        }

        // CASO 4: El path es "/" (Carga inicial o actualización completa de /control)
        else if (data.dataPath() == "/") {
            if (data.dataType() == "json") {
                Serial.println("[Firebase Stream] Procesando JSON completo de /control...");
                FirebaseJson *json = data.to<FirebaseJson *>();
                FirebaseJsonData jsonData;

                if (json->get(jsonData, "umbralHumedad")) {
                    if (jsonData.type == "int" || jsonData.type == "number") g_umbralHumedad = jsonData.to<int>();
                }
                if (json->get(jsonData, "ventilacion")) {
                    
                    // --- CAMBIO TAMBIÉN AQUÍ: "bool" -> "boolean" ---
                    if (jsonData.type == "boolean") g_ventilacionEstado = jsonData.to<bool>();
                    else if (jsonData.type == "string") g_ventilacionEstado = (jsonData.to<String>() == "true");
                    else if (jsonData.type == "int") g_ventilacionEstado = (jsonData.to<int>() == 1);
                }
                if (json->get(jsonData, "iluminacion")) {
                    if (jsonData.type == "json") {
                        FirebaseJson jsonIluminacion;
                        jsonData.get(jsonIluminacion);
                        FirebaseJsonData jsonHora;
                        if (jsonIluminacion.get(jsonHora, "horaEncendido")) g_horaEncendido = jsonHora.to<String>();
                        if (jsonIluminacion.get(jsonHora, "horaApagado")) g_horaApagado = jsonHora.to<String>();
                    }
                }
                Serial.printf("[Firebase Stream] Carga inicial: Umbral: %d, Vent: %s, ON: %s, OFF: %s\n",
                              g_umbralHumedad, g_ventilacionEstado ? "true" : "false", g_horaEncendido.c_str(), g_horaApagado.c_str());
            }
        }
        
        // CASO 5: Otros paths no manejados
        else {
            Serial.printf("[Firebase Stream] Dato no manejado (path simple anidado): %s\n", data.dataPath().c_str());
        }

        xSemaphoreGive(xStateMutex);
    
    } else {
        Serial.println("[Firebase Stream] Advertencia: No se pudo adquirir Mutex (Estado).");
    }
}

void streamTimeoutCallback(bool timeout) {
    if (timeout) {
        Serial.println("[Firebase Stream] Timeout detectado, intentando reconectar...");
    }
}