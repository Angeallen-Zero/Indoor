#ifndef MENU_H
#define MENU_H

#include <Arduino.h>
#include <LiquidCrystal_I2C.h>
#include "defines.h"   // Para conocer 'boton3'
#include "globals.h"   // Para conocer 'lcd', 'boton_opcion', 'humedad_maxima', 'humedad_minima'
#include "ajustarParametroHumedad.h"  // Para usar la función ajustarParametro()

// Función auxiliar para mostrar un mensaje en el LCD y esperar que se presione boton3
void mostrarMensaje(const char* mensaje) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(mensaje);
  
  // Espera hasta que se presione boton3 para salir
  while (digitalRead(boton3) == HIGH) {  
    delay(100);
  }
  delay(200);  // Antirrebote
}

// Función para gestionar el menú según el botón presionado
void menu() {
  switch (boton_opcion) {
    case 1:
      ajustarParametro("maxima", humedad_maxima);
      ajustarParametro("minima", humedad_minima);
      lcd.clear();
      boton_opcion = 0;
      break;
      
    case 2:
      mostrarMensaje("hola");
      boton_opcion = 0;
      break;
      
    case 3:
      mostrarMensaje("adios");
      boton_opcion = 0;
      break;
      
    default:
      break;
  }
}

#endif  // MENU_H
