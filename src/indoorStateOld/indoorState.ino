/*
  SISTEMA DE CONTROL AUTOMATIZADO PARA CULTIVOS INDOOR
  Autor: [Tu Angel Ramos Jain]
  Descripción: Controla humedad, ventilación e iluminación mediante relays
  con interfaz LCD y menú de configuración con botones
  Incluye esqueleto para notificaciones de errores de sensor, ventilador y luces
*/

// ======================= BIBLIOTECAS =======================
#include <LiquidCrystal_I2C.h>  // Librería para controlar pantalla LCD vía I2C

// =================== DEFINICIÓN DE PINES ====================
#define SENSOR_HUMEDAD      A0  // Pin analógico del sensor de humedad
#define RELE_HUMEDAD         2  // Relay para bomba de riego
#define RELE_VENTILADOR      3  // Relay para ventilador
#define RELE_LUZ             4  // Relay para iluminación

#define BOTON1               5  // Botón Izquierda / Decremento
#define BOTON2               6  // Botón Derecha / Incremento
#define BOTON3               7  // Botón Confirmar / Seleccionar

// ================ CONFIGURACIÓN INICIAL LCD ================
LiquidCrystal_I2C lcd(0x27, 16, 2);  // Dirección I2C: 0x27, Display 16x2 caracteres

// ================ PARÁMETROS CONFIGURABLES ================
float humedad_minima   = 40.0;  // Porcentaje mínimo para activar riego
float humedad_maxima   = 65.0;  // Porcentaje máximo para detener riego
float porcentaje_actual = 0.0;  // Humedad actual medida
bool ventilador_encendido = false; // Estado deseado del ventilador
unsigned long horas_encendido = 18; // Horas de encendido de luz
unsigned long horas_apagado  = 6;  // Horas de apagado de luz
bool luz_encendida      = false; // Estado actual de la iluminación

// ================ VARIABLES DE CONTROL DE TIEMPO ================
unsigned long milis_anteriores     = 0;  // Marca de tiempo para temporizador de luz
unsigned long ultima_actualizacion_lcd  = 0;  // Marca de tiempo para refresco de LCD

// ================ CONFIGURACIÓN ANTI-REBOTE BOTONES ================
typedef unsigned long ul;
ul ultima_pulsacion_boton = 0;           // Última lectura de botón válida
const ul tiempo_antirebote = 200;      // Tiempo mínimo entre pulsaciones (ms)

// ================ ESQUELETO DE NOTIFICACIONES =================
enum ErrorType { ERROR_SENSOR, ERROR_VENTILADOR, ERROR_LUZ };

class NotificationManager {
public:
  static void notificar(ErrorType tipo) {
    switch(tipo) {
      case ERROR_SENSOR:
        // TODO: implementar notificación de fallo de sensor
        break;
      case ERROR_VENTILADOR:
        // TODO: implementar notificación de fallo de ventilador
        break;
      case ERROR_LUZ:
        // TODO: implementar notificación de fallo de iluminación
        break;
    }
  }
};

// ================ PATRÓN DE ESTADOS ======================
class Contexto;
class Estado {
public:
  virtual void alEntrar(Contexto& ctx) {}            // Al entrar al estado
  virtual void alSalir(Contexto& ctx) {}             // Al salir del estado
  virtual void manejar(Contexto& ctx) {}              // Lógica continua del estado
  virtual void manejarEntrada(Contexto& ctx, int op) {} // Manejo de entrada de botones
  virtual ~Estado() {}
};

class Contexto {
public:
  Estado* estado_actual;
  Contexto(Estado* inicio): estado_actual(nullptr) { cambiarEstado(inicio); }
  
  void cambiarEstado(Estado* siguiente) {
    if (estado_actual) estado_actual->alSalir(*this);
    estado_actual = siguiente;
    if (estado_actual) estado_actual->alEntrar(*this);
  }

  void actualizar() { if (estado_actual) estado_actual->manejar(*this); }
  void entrada(int opcion) { if (estado_actual) estado_actual->manejarEntrada(*this, opcion); }
};

// ================ DECLARACIONES AUXILIARES ================
void manejarTemporizadorLuz();                           
void pantallaPrincipal();                                
int leerBoton();                                         
void ajusteValor(const char* nombre, float &valor, float paso, float min_val, float max_val); 
void ajusteTiempo(const char* nombre, ul &valor, ul paso);     

// ================ DECLARACIONES DE ESTADOS ================
class EstadoMenu         : public Estado { void manejarEntrada(Contexto&,int) override; };
class EstadoConfigHumedad: public Estado { void alEntrar(Contexto&) override; };
class EstadoConfigVentilador: public Estado { void alEntrar(Contexto&) override; };
class EstadoConfigLuz  : public Estado { void alEntrar(Contexto&) override; };
class EstadoOperacional  : public Estado { void manejar(Contexto&) override; };

// Instancias globales de cada estado
EstadoOperacional estadoOperacional;
EstadoMenu       menuEstado;
EstadoConfigHumedad cfgHum;
EstadoConfigVentilador cfgVent;
EstadoConfigLuz    cfgLuz;

// ================ IMPLEMENTACIÓN DE ESTADOS ================

void EstadoMenu::manejarEntrada(Contexto& ctx, int opcion) {
  switch(opcion) {
    case 1: ctx.cambiarEstado(&cfgHum); break;   
    case 2: ctx.cambiarEstado(&cfgVent); break;   
    case 3: ctx.cambiarEstado(&cfgLuz); break; 
    default: ctx.cambiarEstado(&estadoOperacional); break; 
  }
}

void EstadoConfigHumedad::alEntrar(Contexto& ctx) {
  ajusteValor("Hum. Minima", humedad_minima, 1.0, 20.0, 60.0);
  ajusteValor("Hum. Maxima", humedad_maxima, 1.0, humedad_minima+5.0, 90.0);
  ctx.cambiarEstado(&estadoOperacional);
}

void EstadoConfigVentilador::alEntrar(Contexto& ctx) {
  lcd.clear(); lcd.setCursor(0,0);
  lcd.print("Ventilador: "); lcd.print(ventilador_encendido?"ON":"OFF");
  lcd.setCursor(0,1); lcd.print("1-OFF 2-ON 3-OK");
  bool enAjuste=true;
  while(enAjuste) {
    int op=leerBoton();
    if(op==1) { ventilador_encendido=false; delay(tiempo_antirebote); }
    if(op==2) { ventilador_encendido=true;  delay(tiempo_antirebote); }
    if(op==3) { enAjuste=false; delay(tiempo_antirebote); }
    lcd.setCursor(11,0); lcd.print(ventilador_encendido?"ON ":"OFF");
  }
  ctx.cambiarEstado(&estadoOperacional);
}

void EstadoConfigLuz::alEntrar(Contexto& ctx) {
  ajusteTiempo("Horas ON",  horas_encendido, 1);
  ajusteTiempo("Horas OFF", horas_apagado, 1);
  milis_anteriores = millis(); 
  ctx.cambiarEstado(&estadoOperacional);
}

void EstadoOperacional::manejar(Contexto& ctx) {
  unsigned long ahora = millis();
  static unsigned long ultima_lectura_sensor = 0;

  if(ahora - ultima_lectura_sensor >= 2000) {
    ultima_lectura_sensor = ahora;
    int lectura = analogRead(SENSOR_HUMEDAD);
    porcentaje_actual = 100.0 - ((lectura - 222.0)/(525.0-222.0)*100.0);
    porcentaje_actual = constrain(porcentaje_actual, 0, 100);

    if(lectura<0 || lectura>1023) {
      NotificationManager::notificar(ERROR_SENSOR);
    }

    digitalWrite(RELE_HUMEDAD,
      (porcentaje_actual<=humedad_minima)?HIGH :
      (porcentaje_actual>=humedad_maxima)?LOW : digitalRead(RELE_HUMEDAD)
    );

    Serial.print("Humedad: "); Serial.print(porcentaje_actual);
    Serial.print("% Vent: "); Serial.print(ventilador_encendido?"ON":"OFF");
    Serial.print(" Luz: "); Serial.println(luz_encendida?"ON":"OFF");
  }

  digitalWrite(RELE_VENTILADOR, ventilador_encendido?HIGH:LOW);
  manejarTemporizadorLuz();

  if(ahora - ultima_actualizacion_lcd >= 1000) {
    ultima_actualizacion_lcd = ahora;
    pantallaPrincipal();
  }

  int opcion = leerBoton();
  if(opcion && ahora - ultima_pulsacion_boton > tiempo_antirebote) {
    ultima_pulsacion_boton = ahora;
    ctx.cambiarEstado(&menuEstado);
    ctx.entrada(opcion);
  }
}

// ================ FUNCIONES AUXILIARES ================

void pantallaPrincipal() {
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Hum: "); lcd.print(porcentaje_actual,1); lcd.print("%");
  lcd.setCursor(12,0);
  lcd.print(luz_encendida?"L:ON":"L:OFF");

  lcd.setCursor(0,1);
  lcd.print("Vent: "); lcd.print(ventilador_encendido?"ON":"OFF");
  lcd.setCursor(10,1);
  lcd.print(humedad_minima,0); lcd.print("-"); lcd.print(humedad_maxima,0);
}

int leerBoton() {
  if(digitalRead(BOTON1)==LOW) return 1;
  if(digitalRead(BOTON2)==LOW) return 2;
  if(digitalRead(BOTON3)==LOW) return 3;
  return 0;
}

void ajusteValor(const char* nombre, float &valor, float paso, float min_val, float max_val) {
  bool enAjuste = true;
  while(enAjuste) {
    lcd.clear(); lcd.setCursor(0,0);
    lcd.print(nombre);
    lcd.print(": "); lcd.print(valor,1);
    lcd.setCursor(0,1); lcd.print("1-  2+  3 OK");

    int opcion = leerBoton();
    if(opcion==1 && valor>min_val) { valor = max(min_val, valor-paso); delay(tiempo_antirebote); }
    if(opcion==2 && valor<max_val) { valor = min(max_val, valor+paso); delay(tiempo_antirebote); }
    if(opcion==3) { enAjuste=false; delay(tiempo_antirebote); }
  }
}

void ajusteTiempo(const char* nombre, ul &valor, ul paso) {
  bool enAjuste = true;
  while(enAjuste) {
    lcd.clear(); lcd.setCursor(0,0);
    lcd.print(nombre);
    lcd.print(": "); lcd.print(valor);
    lcd.setCursor(0,1); lcd.print("1-  2+  3 OK");

    int opcion = leerBoton();
    if(opcion==1 && valor>=paso) { valor -= paso; delay(tiempo_antirebote); }
    if(opcion==2)              { valor += paso; delay(tiempo_antirebote); }
    if(opcion==3)              { enAjuste=false; delay(tiempo_antirebote); }
  }
}

void manejarTemporizadorLuz() {
  unsigned long ahora = millis();
  unsigned long t_encendido  = horas_encendido * 3600UL * 1000UL;
  unsigned long t_apagado = horas_apagado  * 3600UL * 1000UL;

  if(ahora < milis_anteriores) milis_anteriores = ahora;

  if(luz_encendida) {
    if(ahora - milis_anteriores >= t_encendido) {
      luz_encendida = false;
      milis_anteriores = ahora;
      digitalWrite(RELE_LUZ, LOW);
    }
  } else {
    if(ahora - milis_anteriores >= t_apagado) {
      luz_encendida = true;
      milis_anteriores = ahora;
      digitalWrite(RELE_LUZ, HIGH);
    }
  }
}

// ================ SETUP Y LOOP PRINCIPAL =================

void setup() {
  Serial.begin(57600);                  

  pinMode(RELE_HUMEDAD, OUTPUT);
  pinMode(RELE_VENTILADOR, OUTPUT);
  pinMode(RELE_LUZ, OUTPUT);
  pinMode(BOTON1, INPUT_PULLUP);
  pinMode(BOTON2, INPUT_PULLUP);
  pinMode(BOTON3, INPUT_PULLUP);

  digitalWrite(RELE_HUMEDAD, LOW);
  digitalWrite(RELE_VENTILADOR, LOW);
  digitalWrite(RELE_LUZ, LOW);

  lcd.init(); lcd.backlight(); lcd.clear();
  lcd.setCursor(0,0); lcd.print("Sistema Indoor");
  lcd.setCursor(0,1); lcd.print("Iniciando...");
  delay(1000);
}

void loop() {
  static Contexto contexto(&estadoOperacional);
  contexto.actualizar();                 
}