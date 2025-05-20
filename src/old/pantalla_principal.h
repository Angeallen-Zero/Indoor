#ifndef PANTALLA_PRINCIPAL_H
#define PANTALLA_PRINCIPAL_H

#include <LiquidCrystal_I2C.h>
#include "globals.h"  // Para acceder a la variable global 'lcd'

// Función para mostrar la pantalla principal con la humedad actual
void mostrarPantallaPrincipal(float porcentaje_actual) {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Humedad actual:");
  lcd.setCursor(0, 1);
  lcd.print(porcentaje_actual, 2);
}

#endif  // PANTALLA_PRINCIPAL_H
