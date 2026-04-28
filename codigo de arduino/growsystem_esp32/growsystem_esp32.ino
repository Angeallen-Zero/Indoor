/*
 * ============================================================
 *  GrowSystem — Firmware ESP32
 *  Versión : 4.0
 *  Arquitectura: ESP32 + FreeRTOS → API REST PHP → MySQL.
 *
 *  Tareas FreeRTOS:
 *    vTaskLeerSensores     — Lee humedad cada 10 s, (simula nutrientes)
 *    vTaskControlActuadores — Evalúa lógica y actualiza relés cada 5 s
 *    vTaskEnviarEstado     — POST estado + sensores al servidor cada 5 s
 *    vTaskGuardarLectura   — POST lectura histórica al servidor cada 10 s
 *    vTaskSincronizarControl — GET parámetros del servidor cada 30 s
 *
 *  Pines:
 *    35 → Sensor humedad suelo (capacitivo, ADC)
 *    14 → Relé surtidor nutrientes
 *    25 → Relé bomba de agua
 *    26 → Relé ventilador
 *    27 → Relé luces
 *    32 → Botón reset WiFi
 *
 *  Seguridad:
 *    Cada request lleva el header  X-API-Token: <API_TOKEN>
 *    y el campo                    serie: <NUMERO_SERIE>
 * ============================================================
 */


//  1. BIBLIOTECAS
#include <WiFi.h>
#include <WiFiManager.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <NTPClient.h>
#include <WiFiUDP.h>


//  2. IDENTIDAD DEL DISPOSITIVO
//     *** CAMBIAR EN CADA ESP32 ANTES DE FLASHEAR ***
#define NUMERO_SERIE  "IND-0003"
#define API_TOKEN     "f4c3b2a1e6d9c8b7a5f0e1d2c3b4a59687f0e1d2c3b4a5f6e7d8c9b0a1b2c3d4"


//  3. SERVIDOR  (IP local del XAMPP en la misma red)
//     *** CAMBIAR si la IP del servidor cambia ***
#define SERVER_IP     "192.168.0.00"   // IP del PC 
#define SERVER_PORT   80
#define API_BASE      "http://" SERVER_IP "/growsystem/api"

#define URL_CONTROL   API_BASE "/control.php"
#define URL_ESTADO    API_BASE "/estado.php"
#define URL_LECTURA   API_BASE "/lectura.php"

//  4. PINES DE HARDWARE
#define SOIL_SENSOR_PIN   35
#define PUMP_RELAY_PIN    25
#define FAN_RELAY_PIN     26
#define LIGHT_RELAY_PIN   27
#define NUTRIENT_RELAY_PIN 14   // Surtidor de nutrientes simulado
#define RESET_BUTTON_PIN  32

// Lógica de relés
#define RELAY_ON   LOW
#define RELAY_OFF  HIGH

//  5. CALIBRACIÓN SENSOR DE HUMEDAD
#define SENSOR_SECO_VAL   3100
#define SENSOR_HUMEDO_VAL 1250

//  6. INTERVALOS DE TAREAS (milisegundos)
#define INTERVALO_SENSORES       10000   // 10 s
#define INTERVALO_ACTUADORES      5000   //  5 s
#define INTERVALO_ENVIAR_ESTADO   5000   //  5 s
#define INTERVALO_GUARDAR_LECTURA 10000  // 10 s
#define INTERVALO_SYNC_CONTROL   30000   // 30 s

//  7. ESTADO GLOBAL COMPARTIDO
SemaphoreHandle_t xMutexEstado;   // Protege variables g_
SemaphoreHandle_t xMutexHTTP;     // Solo un request HTTP a la vez

// --- Sensores (escritos por vTaskLeerSensores) ---
float g_humedad     = 0.0;
float g_nutrientes  = 0.0;   // Simulado hasta tener sensor real

// --- Parámetros de control (escritos por vTaskSincronizarControl) ---
int   g_umbralHumedad    = 30;
int   g_umbralNutrientes = 40;
bool  g_ventilacionForzada = false;
String g_horaEncendido   = "08:00";
String g_horaApagado     = "18:00";

// --- Estado de relés (escrito por vTaskControlActuadores) ---
bool g_bombaON     = false;
bool g_ventiladorON = false;
bool g_lucesON     = false;
bool g_surtidorON  = false;

//  8. OBJETOS GLOBALES
WiFiManager   wm;
WiFiUDP       ntpUDP;
NTPClient     timeClient(ntpUDP, "mx.pool.ntp.org", -21600);  // UTC-6 CDMX

//  9. PROTOTIPOS
void vTaskLeerSensores      (void *pvParameters);
void vTaskControlActuadores (void *pvParameters);
void vTaskEnviarEstado      (void *pvParameters);
void vTaskGuardarLectura    (void *pvParameters);
void vTaskSincronizarControl(void *pvParameters);

bool httpPost(const char* url, String &jsonBody);
bool httpGet (const char* url, String &respuesta);
void parsearControlJSON(String &json);
void initNTP();
void initWiFi();
void initPines();
void initMutex();

//  SETUP
void setup() {
    Serial.begin(115200);
    delay(200);
    Serial.println("\n===== GrowSystem " NUMERO_SERIE " arrancando =====");

    initPines();
    initMutex();
    initWiFi();
    initNTP();

    // Obtener parámetros de control antes de crear tareas
    Serial.println("[Setup] Obteniendo parámetros iniciales del servidor...");
    String respuesta;
    String url = String(URL_CONTROL) + "?serie=" + NUMERO_SERIE;
    if (httpGet(url.c_str(), respuesta)) {
        parsearControlJSON(respuesta);
    } else {
        Serial.println("[Setup] No se pudo obtener control, usando valores por defecto.");
    }

    Serial.println("[Setup] Creando tareas FreeRTOS...");

    xTaskCreatePinnedToCore(
        vTaskLeerSensores,
        "Sensores",
        8192,
        NULL, 1, NULL, 1);

    xTaskCreatePinnedToCore(
        vTaskControlActuadores,
        "Actuadores",
        8192,
        NULL, 2, NULL, 1);

    xTaskCreatePinnedToCore(
        vTaskEnviarEstado,
        "EnviarEstado",
        12288,
        NULL, 1, NULL, 1);

    xTaskCreatePinnedToCore(
        vTaskGuardarLectura,
        "GuardarLectura",
        12288,
        NULL, 1, NULL, 1);

    xTaskCreatePinnedToCore(
        vTaskSincronizarControl,
        "SyncControl",
        12288,
        NULL, 1, NULL, 1);

    Serial.println("[Setup] Listo. FreeRTOS tiene el control.");
    vTaskDelete(NULL);
}

//  LOOP  (vacío — FreeRTOS maneja todo)
void loop() {
    vTaskDelay(pdMS_TO_TICKS(10000));
}

//  TAREA 1 — LEER SENSORES  (cada 10 s)
//  Lee humedad real del ADC.
//  Simula nutrientes con variación aleatoria hasta tener sensor.
void vTaskLeerSensores(void *pvParameters) {
    TickType_t xLastWake = xTaskGetTickCount();

    // Semilla aleatoria para simulación de nutrientes
    srand(esp_random());
    float nutrientesSimulado = 75.0;

    for (;;) {
        vTaskDelayUntil(&xLastWake, pdMS_TO_TICKS(INTERVALO_SENSORES));

        // --- Humedad real ---
        int rawHumedad = analogRead(SOIL_SENSOR_PIN);
        float humedad  = constrain(
            map(rawHumedad, SENSOR_SECO_VAL, SENSOR_HUMEDO_VAL, 0, 100),
            0, 100
        );

        // --- Nutrientes simulados ---
        // Baja lentamente y sube cuando el surtidor estuvo activo
        bool surtidorActivo;
        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(100)) == pdTRUE) {
            surtidorActivo = g_surtidorON;
            xSemaphoreGive(xMutexEstado);
        } else {
            surtidorActivo = false;
        }

        if (surtidorActivo) {
            nutrientesSimulado += (float)(rand() % 5 + 2);   // +2..6 cuando dosifica
        } else {
            nutrientesSimulado -= (float)(rand() % 3 + 1);   // -1..3 consumo natural
        }
        nutrientesSimulado = constrain(nutrientesSimulado, 5.0, 100.0);

        // --- Guardar en estado global ---
        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(200)) == pdTRUE) {
            g_humedad    = humedad;
            g_nutrientes = nutrientesSimulado;
            xSemaphoreGive(xMutexEstado);
        }

        Serial.printf("[Sensores] Humedad: %.1f%% (raw:%d) | Nutrientes: %.1f%% (sim)\n",
                      humedad, rawHumedad, nutrientesSimulado);
    }
}

//  TAREA 2 — CONTROL DE ACTUADORES  (cada 5 s)
//  Lee estado global y decide el estado de cada relé.
//  Escribe relés físicos y actualiza g_bombaON, etc.
void vTaskControlActuadores(void *pvParameters) {
    TickType_t xLastWake = xTaskGetTickCount();

    for (;;) {
        vTaskDelayUntil(&xLastWake, pdMS_TO_TICKS(INTERVALO_ACTUADORES));

        // Leer valores compartidos
        float localHumedad, localNutrientes;
        int   localUmbralHum, localUmbralNut;
        bool  localVentForzada;
        String localHoraON, localHoraOFF;

        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(200)) == pdTRUE) {
            localHumedad      = g_humedad;
            localNutrientes   = g_nutrientes;
            localUmbralHum    = g_umbralHumedad;
            localUmbralNut    = g_umbralNutrientes;
            localVentForzada  = g_ventilacionForzada;
            localHoraON       = g_horaEncendido;
            localHoraOFF      = g_horaApagado;
            xSemaphoreGive(xMutexEstado);
        } else {
            Serial.println("[Actuadores] No se pudo adquirir mutex, saltando ciclo.");
            continue;
        }

        // --- Lógica BOMBA (riego) ---
        bool nuevaBomba = (localHumedad < localUmbralHum);

        // --- Lógica VENTILADOR ---
        bool nuevoVentilador = localVentForzada;

        // --- Lógica LUCES (horario NTP) ---
        timeClient.update();
        bool nuevasLuces = false;
        int horaActual   = timeClient.getHours();
        int minActual    = timeClient.getMinutes();
        int minutoActual = horaActual * 60 + minActual;

        int hON  = localHoraON.substring(0, 2).toInt();
        int mON  = localHoraON.substring(3, 5).toInt();
        int hOFF = localHoraOFF.substring(0, 2).toInt();
        int mOFF = localHoraOFF.substring(3, 5).toInt();

        int minON  = hON  * 60 + mON;
        int minOFF = hOFF * 60 + mOFF;

        if (minON < minOFF) {
            nuevasLuces = (minutoActual >= minON && minutoActual < minOFF);
        } else {
            // Cruza medianoche
            nuevasLuces = (minutoActual >= minON || minutoActual < minOFF);
        }

        // --- Lógica SURTIDOR (nutrientes) ---
        bool nuevoSurtidor = (localNutrientes < localUmbralNut);

        // --- Aplicar a relés físicos ---
        digitalWrite(PUMP_RELAY_PIN,     nuevaBomba      ? RELAY_ON : RELAY_OFF);
        digitalWrite(FAN_RELAY_PIN,      nuevoVentilador ? RELAY_ON : RELAY_OFF);
        digitalWrite(LIGHT_RELAY_PIN,    nuevasLuces     ? RELAY_ON : RELAY_OFF);
        digitalWrite(NUTRIENT_RELAY_PIN, nuevoSurtidor   ? RELAY_ON : RELAY_OFF);

        // --- Actualizar estado global ---
        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(200)) == pdTRUE) {
            g_bombaON      = nuevaBomba;
            g_ventiladorON = nuevoVentilador;
            g_lucesON      = nuevasLuces;
            g_surtidorON   = nuevoSurtidor;
            xSemaphoreGive(xMutexEstado);
        }

        Serial.printf("[Actuadores] Bomba:%s Vent:%s Luces:%s Surtidor:%s | %s\n",
            nuevaBomba      ? "ON" : "OFF",
            nuevoVentilador ? "ON" : "OFF",
            nuevasLuces     ? "ON" : "OFF",
            nuevoSurtidor   ? "ON" : "OFF",
            timeClient.getFormattedTime().c_str());
    }
}

//  TAREA 3 — ENVIAR ESTADO  (cada 5 s)
//  POST /api/estado.php
//  Actualiza estado_actual en MySQL (para el dashboard).
void vTaskEnviarEstado(void *pvParameters) {
    TickType_t xLastWake = xTaskGetTickCount();

    for (;;) {
        vTaskDelayUntil(&xLastWake, pdMS_TO_TICKS(INTERVALO_ENVIAR_ESTADO));

        if (WiFi.status() != WL_CONNECTED) {
            Serial.println("[EstadoPOST] Sin WiFi, saltando.");
            continue;
        }

        // Leer estado global
        float lHumedad, lNutrientes;
        bool  lBomba, lVent, lLuces, lSurtidor;

        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(200)) == pdTRUE) {
            lHumedad    = g_humedad;
            lNutrientes = g_nutrientes;
            lBomba      = g_bombaON;
            lVent       = g_ventiladorON;
            lLuces      = g_lucesON;
            lSurtidor   = g_surtidorON;
            xSemaphoreGive(xMutexEstado);
        } else {
            continue;
        }

        // Construir JSON
        StaticJsonDocument<256> doc;
        doc["serie"]      = NUMERO_SERIE;
        doc["bomba"]      = lBomba      ? 1 : 0;
        doc["ventilador"] = lVent       ? 1 : 0;
        doc["luces"]      = lLuces      ? 1 : 0;
        doc["surtidor"]   = lSurtidor   ? 1 : 0;
        doc["humedad"]    = serialized(String(lHumedad,    1));
        doc["nutrientes"] = serialized(String(lNutrientes, 1));

        String body;
        serializeJson(doc, body);

        bool ok = httpPost(URL_ESTADO, body);
        Serial.printf("[EstadoPOST] %s\n", ok ? "OK" : "ERROR");
    }
}

//  TAREA 4 — GUARDAR LECTURA HISTÓRICA  (cada 10 s)
//  POST /api/lectura.php
//  Inserta en tabla lecturas (fuente de gráficas).
void vTaskGuardarLectura(void *pvParameters) {
    TickType_t xLastWake = xTaskGetTickCount();

    for (;;) {
        vTaskDelayUntil(&xLastWake, pdMS_TO_TICKS(INTERVALO_GUARDAR_LECTURA));

        if (WiFi.status() != WL_CONNECTED) {
            Serial.println("[LecturaPOST] Sin WiFi, saltando.");
            continue;
        }

        float lHumedad, lNutrientes;

        if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(200)) == pdTRUE) {
            lHumedad    = g_humedad;
            lNutrientes = g_nutrientes;
            xSemaphoreGive(xMutexEstado);
        } else {
            continue;
        }

        StaticJsonDocument<128> doc;
        doc["serie"]      = NUMERO_SERIE;
        doc["humedad"]    = serialized(String(lHumedad,    1));
        doc["nutrientes"] = serialized(String(lNutrientes, 1));

        String body;
        serializeJson(doc, body);

        bool ok = httpPost(URL_LECTURA, body);
        Serial.printf("[LecturaPOST] Humedad:%.1f Nutrientes:%.1f → %s\n",
                      lHumedad, lNutrientes, ok ? "OK" : "ERROR");
    }
}

//  TAREA 5 — SINCRONIZAR CONTROL  (cada 30 s)
//  GET /api/control.php?serie=IND-XXXX
//  Actualiza parámetros si el usuario los cambió en el dashboard.
void vTaskSincronizarControl(void *pvParameters) {
    TickType_t xLastWake = xTaskGetTickCount();

    for (;;) {
        vTaskDelayUntil(&xLastWake, pdMS_TO_TICKS(INTERVALO_SYNC_CONTROL));

        if (WiFi.status() != WL_CONNECTED) {
            Serial.println("[SyncControl] Sin WiFi, saltando.");
            continue;
        }

        String url = String(URL_CONTROL) + "?serie=" + NUMERO_SERIE;
        String respuesta;

        if (httpGet(url.c_str(), respuesta)) {
            parsearControlJSON(respuesta);
            Serial.println("[SyncControl] Parámetros actualizados.");
        } else {
            Serial.println("[SyncControl] ERROR al obtener parámetros.");
        }
    }
}


//  FUNCIONES AUXILIARES HTTP

/*
 * httpPost — Envía JSON al servidor con autenticación por token.
 * Protegido por xMutexHTTP para evitar requests simultáneos.
 */
bool httpPost(const char* url, String &jsonBody) {
    if (xSemaphoreTake(xMutexHTTP, pdMS_TO_TICKS(3000)) != pdTRUE) {
        Serial.println("[HTTP] No se pudo adquirir mutex HTTP.");
        return false;
    }

    bool exito = false;
    HTTPClient http;

    http.begin(url);
    http.setTimeout(5000);
    http.addHeader("Content-Type",  "application/json");
    http.addHeader("X-API-Token",   API_TOKEN);

    int httpCode = http.POST(jsonBody);

    if (httpCode == HTTP_CODE_OK) {
        exito = true;
    } else {
        Serial.printf("[HTTP POST] Error %d en %s\n", httpCode, url);
    }

    http.end();
    xSemaphoreGive(xMutexHTTP);
    return exito;
}

/*
 * httpGet — Obtiene JSON del servidor con autenticación por token.
 */
bool httpGet(const char* url, String &respuesta) {
    if (xSemaphoreTake(xMutexHTTP, pdMS_TO_TICKS(3000)) != pdTRUE) {
        Serial.println("[HTTP] No se pudo adquirir mutex HTTP.");
        return false;
    }

    bool exito = false;
    HTTPClient http;

    http.begin(url);
    http.setTimeout(5000);
    http.addHeader("X-API-Token", API_TOKEN);

    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
        respuesta = http.getString();
        exito = true;
    } else {
        Serial.printf("[HTTP GET] Error %d en %s\n", httpCode, url);
    }

    http.end();
    xSemaphoreGive(xMutexHTTP);
    return exito;
}

void parsearControlJSON(String &json) {
    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, json);

    if (err) {
        Serial.printf("[Control] Error al parsear JSON: %s\n", err.c_str());
        return;
    }

    if (xSemaphoreTake(xMutexEstado, pdMS_TO_TICKS(300)) == pdTRUE) {
        if (doc.containsKey("umbral_humedad"))    g_umbralHumedad       = doc["umbral_humedad"].as<int>();
        if (doc.containsKey("umbral_nutrientes")) g_umbralNutrientes    = doc["umbral_nutrientes"].as<int>();
        if (doc.containsKey("ventilacion"))       g_ventilacionForzada  = doc["ventilacion"].as<bool>();
        if (doc.containsKey("hora_encendido"))    g_horaEncendido       = doc["hora_encendido"].as<String>();
        if (doc.containsKey("hora_apagado"))      g_horaApagado         = doc["hora_apagado"].as<String>();
        xSemaphoreGive(xMutexEstado);

        Serial.printf("[Control] Umbral hum:%d%% nut:%d%% | Vent:%s | Luces %s-%s\n",
            g_umbralHumedad, g_umbralNutrientes,
            g_ventilacionForzada ? "ON" : "OFF",
            g_horaEncendido.c_str(), g_horaApagado.c_str());
    }
}


//  INICIALIZACIÓN

void initPines() {
    pinMode(PUMP_RELAY_PIN,      OUTPUT);
    pinMode(FAN_RELAY_PIN,       OUTPUT);
    pinMode(LIGHT_RELAY_PIN,     OUTPUT);
    pinMode(NUTRIENT_RELAY_PIN,  OUTPUT);
    pinMode(SOIL_SENSOR_PIN,     INPUT);
    pinMode(RESET_BUTTON_PIN,    INPUT_PULLUP);

    // Todos los relés apagados al arrancar
    digitalWrite(PUMP_RELAY_PIN,      RELAY_OFF);
    digitalWrite(FAN_RELAY_PIN,       RELAY_OFF);
    digitalWrite(LIGHT_RELAY_PIN,     RELAY_OFF);
    digitalWrite(NUTRIENT_RELAY_PIN,  RELAY_OFF);

    Serial.println("[Init] Pines configurados.");
}

void initMutex() {
    xMutexEstado = xSemaphoreCreateMutex();
    xMutexHTTP   = xSemaphoreCreateMutex();

    if (xMutexEstado == NULL || xMutexHTTP == NULL) {
        Serial.println("[Init] ERROR: No se pudo crear un mutex. Reiniciando...");
        delay(1000);
        ESP.restart();
    }
    Serial.println("[Init] Mutex creados.");
}

void initWiFi() {
    Serial.println("[Init] Iniciando WiFiManager...");

    pinMode(RESET_BUTTON_PIN, INPUT_PULLUP);
    wm.setConfigPortalTimeout(180);

    if (digitalRead(RESET_BUTTON_PIN) == LOW) {
        Serial.println("[Init] Botón reset pulsado, borrando WiFi...");
        wm.resetSettings();
        delay(1000);
    }

    // El SSID del portal captivo incluye el número de serie
    // para identificar cada dispositivo en campo
    String apName = "GrowSystem-" + String(NUMERO_SERIE);

    if (!wm.autoConnect(apName.c_str())) {
        Serial.println("[Init] Sin conexión WiFi. Reiniciando...");
        delay(2000);
        ESP.restart();
    }

    Serial.printf("[Init] WiFi conectado. IP: %s\n", WiFi.localIP().toString().c_str());
}

void initNTP() {
    Serial.println("[Init] Sincronizando NTP (mx.pool.ntp.org)...");
    timeClient.begin();

    int intentos = 0;
    while (!timeClient.forceUpdate() && intentos < 20) {
        Serial.print(".");
        delay(1000);
        intentos++;
    }

    if (intentos >= 20) {
        Serial.println("\n[Init] ADVERTENCIA: NTP no sincronizó. Las luces usarán hora incorrecta.");
        // No se reinicia — el resto del sistema puede funcionar igual
    } else {
        Serial.printf("\n[Init] NTP OK → %s\n", timeClient.getFormattedTime().c_str());
    }
}
