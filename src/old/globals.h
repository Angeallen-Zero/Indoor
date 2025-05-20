#ifndef GLOBALS_H
#define GLOBALS_H

#include <LiquidCrystal_I2C.h>

// Declaración de la instancia LCD (se define en el .ino)
extern LiquidCrystal_I2C lcd;

// Declaración de variables globales
extern int boton_opcion;
extern float humedad_maxima;
extern float humedad_minima;

#endif  // GLOBALS_H
