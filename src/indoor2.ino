#include "defines.h"
#include "globals.h"

#include <LiquidCrystal_I2C.h>
#include "control_rele.h"
#include "leer_boton.h"
#include "ajustarParametroHumedad.h"
#include "menu.h"
#include "pantalla_principal.h"  // Incluir la cabecera de la pantalla principal

// Definición de la instancia del LCD (se define aquí, por eso en globals.h usamos extern)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Definición de las variables globales (las declaraciones extern están en globals.h)
int boton_opcion = 0;
float humedad_maxima = 65.0;
float humedad_minima = 40.0;

float porcentaje_actual = 0.0;
float humedad = 0.0;

void setup() {
  Serial.begin(57600);
  
  // Configuración de pines
  pinMode(rele, OUTPUT);
  pinMode(boton1, INPUT);
  pinMode(boton2, INPUT);
  pinMode(boton3, INPUT);
  
  // Inicialización del LCD
  lcd.init();
  lcd.backlight();
}

void loop() {
  // Lectura y mapeo del sensor de humedad
  humedad = analogRead(sensor_humedad);
  // Cálculo del porcentaje de humedad (ajusta los valores según tu sensor)
  porcentaje_actual = 100 - ((humedad - 222.0) / (525.0 - 222.0) * 100.0);

  // Mostrar la pantalla principal usando la función definida en pantalla_principal.h
  mostrarPantallaPrincipal(porcentaje_actual);

  // Leer botón y gestionar menú
  boton_opcion = leer_boton();
  menu();
  
  // Controlar el relé según el porcentaje de humedad
  control_rele(porcentaje_actual, humedad_minima, humedad_maxima);
}

