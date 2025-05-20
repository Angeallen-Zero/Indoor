#ifndef AJUSTAR_PARAMETRO_HUMEDAD_H
#define AJUSTAR_PARAMETRO_HUMEDAD_H

#include <Arduino.h>
#include <LiquidCrystal_I2C.h>
#include "defines.h"  // Para conocer 'boton1', 'boton2', 'boton3'
#include "globals.h"  // Para conocer 'lcd'

// Función para ajustar un parámetro (por ejemplo, la humedad máxima o mínima)
void ajustarParametro(const char* tipo, float& valor) {
  bool enAjuste = true;
  lcd.clear();
  
  while (enAjuste) {
    // Mostrar el parámetro a ajustar y su valor actual
    lcd.setCursor(0, 0);
    lcd.print("Ajustar ");
    lcd.print(tipo);
    lcd.setCursor(0, 1);
    lcd.print("Valor: ");
    lcd.print(valor, 2);

    // Leer botones: aumentar, disminuir o confirmar
    if (digitalRead(boton1) == LOW) {
      valor += 0.5;
      delay(200);  // Antirrebote
    } 
    else if (digitalRead(boton2) == LOW) {
      valor -= 0.5;
      delay(200);  // Antirrebote
    } 
    else if (digitalRead(boton3) == LOW) {
      enAjuste = false;
      delay(200);  // Antirrebote
    }
  }
}

#endif  // AJUSTAR_PARAMETRO_H
