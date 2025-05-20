#ifndef CONTROL_RELE_H
#define CONTROL_RELE_H

#include <Arduino.h>
#include "defines.h"  // Para conocer 'rele'

// Función para controlar el relé en función del porcentaje de humedad
void control_rele(float porcentaje_actual, float humedad_minima, float humedad_maxima) {
  if (porcentaje_actual <= humedad_minima) {
    digitalWrite(rele, HIGH);
  }
  else if (porcentaje_actual >= humedad_maxima) {
    digitalWrite(rele, LOW);
  }
}

#endif  // CONTROL_RELE_H
