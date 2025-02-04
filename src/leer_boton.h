#ifndef LEER_BOTON_H
#define LEER_BOTON_H

#include <Arduino.h>
#include "defines.h"  // Para conocer 'boton1', 'boton2', 'boton3'

// Función para leer los botones y devolver el número correspondiente al botón presionado.
int leer_boton() {
  if (digitalRead(boton1) == LOW) return 1;
  else if (digitalRead(boton2) == LOW) return 2;
  else if (digitalRead(boton3) == LOW) return 3;
  return 0;
}

#endif  // LEER_BOTON_H
