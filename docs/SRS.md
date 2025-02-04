# Especificación de Requisitos de Software (SRS)

## 1. Introducción

### 1.1 Propósito
Este documento especifica los requisitos de software para el desarrollo del **Sistema Indoor** para la Universidad Autónoma de la Ciudad de México (UACM). El software tiene como propósito monitorear y controlar automáticamente los niveles de humedad, luz y ventilación en entornos cerrados (indoor), como invernaderos, cultivos interiores o espacios de almacenamiento, utilizando el microcontrolador Wemos R1 D1. Se garantiza el mantenimiento de condiciones óptimas mediante un sistema de regulación basado en umbrales configurables por el usuario.

### 1.2 Alcance
El sistema está diseñado para:
- **Visualización de humedad en tiempo real.**
- **Configuración de umbrales de humedad mínima/máxima.**
- **Accionar la bomba de agua** cuando la humedad alcance el límite definido.
- **Control manual del ventilador.**
- **Programación de ciclos de encendido/apagado de luces.**

### 1.3 Definiciones, Acrónimos y Abreviaturas
- **SRS**: Documento de Requisitos de Software.
- **LCD**: Liquid Crystal Display; pantalla de cristal líquido.
- **I2C**: Inter-Integrated Circuit; protocolo de comunicación serial.
- **Relé**: Dispositivo electromecánico para controlar circuitos de alta potencia.
- **Wemos R1 D1**: Microcontrolador basado en ESP8266 con Wi-Fi integrado.
- **GPIO**: Pines de Entrada/Salida de Propósito General.

### 1.4 Referencias
- IEEE 29148:2018 - Estándar para especificación de requisitos de software.
- Diagramas de casos de uso (Figura 1) y de clases (Figura 2) adjuntos.

### 1.5 Visión General
El documento se organiza en secciones de requisitos funcionales, no funcionales, interfaces, restricciones y otros elementos, siguiendo el estándar IEEE 29148.

## 2. Descripción General

### 2.1 Perspectiva del Producto
El **Sistema Indoor** es una solución integrada de hardware y software, destinada a optimizar el cuidado de plantas en entornos cerrados (invernaderos, huertos urbanos, cultivos interiores). Se utiliza hardware accesible y software especializado para reemplazar métodos manuales, reduciendo errores humanos y asegurando condiciones ideales para el crecimiento vegetal.

#### Interfaces Externas
- **Hardware**:  
  - Sensores de humedad  
  - Pantalla LCD con I2C  
  - Ventiladores, luces y relés  
  - Microcontrolador Wemos R1 D1  
  - Pulsadores (botones)  
  - Cables y fuente de poder  
- **Software**:  
  - Archivos `.ino` y `.h` para la gestión de menús y lógica del sistema, tales como:
    - `pantalla_principal.h`
    - `ajustarParametroHumedad.h`
    - `control_rele.h`
    - `leer_boton.h`
    - `menu.h`
    - `indoor.ino`

#### Funcionalidades Clave
Ver Sección 3 para el detalle de requisitos específicos.

### 2.2 Funciones del Producto
| ID    | Función                           | Autor   | Descripción                                                                 |
|-------|-----------------------------------|---------|-----------------------------------------------------------------------------|
| F-001 | Visualizar humedad actual         | Usuario | Muestra la humedad de la planta o cultivo en el LCD.                        |
| F-002 | Modificar umbrales de humedad     | Usuario | Permite ajustar los valores mínimos y máximos de humedad aceptada.          |
| F-003 | Accionar bomba de agua            | Sistema | Activa la bomba automáticamente al alcanzar el umbral mínimo y la desactiva al alcanzar el umbral máximo. |
| F-004 | Control de ventilador             | Usuario | Permite encender o apagar manualmente el ventilador.                        |
| F-005 | Programación de ciclos de luz     | Usuario | Define los tiempos de encendido y apagado de la luz.                        |

### 2.3 Características del Usuario
- **Nivel técnico:** Conocimiento básico en sistemas embebidos y manejo de menús de configuración.
- **Responsabilidades:** Configurar parámetros y supervisar el funcionamiento del sistema.

### 2.4 Restricciones
- **Hardware:**
  - Microcontrolador: Wemos R1 D1 (ESP8266, 4MB Flash, 80 MHz).
  - Sensores compatibles: DHT11/DHT22 para humedad.
- **Software:**
  - Compatible con Arduino IDE y la plataforma ESP8266.

### 2.5 Supuestos y Dependencias
- **Supuesto:** El sistema operará en entornos con temperatura estable (10°C a 40°C).
- **Dependencias:**
  - Librería `LiquidCrystal_I2C.h` para el control del LCD.
  - Driver CH340G para la comunicación serial con el Wemos R1 D1.
  - Entorno de desarrollo Arduino 2.3.4.

### 2.6 Lenguajes de Programación
- **C++**

## 3. Requisitos Específicos

### 3.1 Requisitos Funcionales
| ID      | Requisito                                                                                 | Prioridad | Componente                   |
|---------|-------------------------------------------------------------------------------------------|-----------|------------------------------|
| RF-001  | El sistema mostrará la humedad actual en el LCD cada 2 segundos.                           | Alta      | PantallaPrincipal.h          |
| RF-002  | El sistema monitoreará en tiempo real la humedad del entorno (sensor capacitivo GPIO A0).    | Alta      | PantallaPrincipal.h          |
| RF-003  | El usuario ajustará umbrales de humedad (mínimo y máximo) mediante un menú interactivo.     | Alta      | AjustarParametroHumedad.h    |
| RF-004  | Al superar el umbral mínimo, el sistema activará la bomba de agua (relé GPIO D5).            | Alta      | control_rele.h               |
| RF-005  | Al superar el umbral máximo, el sistema desactivará la bomba de agua (relé GPIO D5).          | Alta      | control_rele.h               |
| RF-006  | El usuario definirá ciclos de luz con precisión de ±1 min (tiempo de encendido y apagado).   | Media     | ajustarTiempoLuz.h           |
| RF-007  | El sistema activará o desactivará las luces de acuerdo al tiempo definido (relé GPIO D10).    | Media     | control_rele.h               |
| RF-008  | Lectura de botones cada 100 ms para respuestas en tiempo real.                            | Alta      | LeerBoton.h                  |
| RF-009  | El usuario podrá activar o desactivar manualmente el ventilador (relé GPIO D9).             | Media     | EstadoVentilador.h           |
| RF-010  | El sistema tendrá un menú de configuración para modificar parámetros.                     | Alta      | Menu.h                       |
| RF-011  | El menú será navegable mediante un botón físico o conjunto de botones (pulsadores GPIO D6, D7, D8). | Alta | Menu.h                  |
| RF-012  | El sistema funcionará de manera continua sin intervención del usuario (control automático de relés). | Alta  | control_rele.h               |
| RF-013  | Se mostrará un mensaje de error o advertencia en la pantalla si algún componente falla (por ejemplo, el sensor de humedad). | Media | PantallaPrincipal.h |
| RF-014  | El sistema será modular, permitiendo futuras expansiones o mejoras sin alterar el núcleo del código. | Baja | Indoor.ino              |
| RF-015  | La documentación del código incluirá comentarios claros que expliquen cada módulo y función. | Baja  | -                            |
| RF-016  | La interfaz de usuario será intuitiva y no requerirá conocimientos avanzados para su uso.    | Media     | Menu.h                       |
| RF-017  | Se incluirán medidas de seguridad para evitar errores al configurar parámetros críticos (por ejemplo, humedad negativa). | Media | AjustarParametroHumedad.h |

### 3.2 Requisitos No Funcionales
| ID      | Requisito                                                                     | Categoría              |
|---------|-------------------------------------------------------------------------------|------------------------|
| RNF-001 | Parámetros guardados en la EEPROM del Wemos R1 D1.                           | Fiabilidad             |
| RNF-002 | Consumo máximo de 3.3V @ 200mA en operación.                                  | Eficiencia energética  |
| RNF-003 | El sistema debe ser portátil y funcionar con alimentación por batería o fuente externa. | Portabilidad   |
| RNF-004 | Debe ser compatible con diferentes modelos de sensores de humedad.            | Compatibilidad         |
| RNF-005 | El sistema operará en entornos con alta humedad o temperaturas extremas sin afectar su funcionalidad. | Robustez   |
| RNF-006 | Los módulos de hardware serán reemplazables sin necesidad de reconfigurar el software. | Modularidad  |
| RNF-007 | El sistema será compatible con al menos dos idiomas.                        | Usabilidad             |
| RNF-008 | Permitirá el control de variables ambientales adicionales (temperatura y CO₂).  | Escalabilidad          |
| RNF-009 | Se integrará con redes inalámbricas (Wi-Fi, Bluetooth, IoT).                  | Conectividad           |
| RNF-010 | Contará con una interfaz gráfica compleja (por ejemplo, para pantallas táctiles). | Interfaz de usuario   |

### 3.3 Arquitectura del Sistema

#### 3.3.1 Diagrama de Componentes
*(Pendiente de incluir)*

#### 3.3.2 Descripción de Componentes
| Componente                | Responsabilidad                                               | Hardware Vinculado             |
|---------------------------|---------------------------------------------------------------|--------------------------------|
| **Indoor.ino**           | Flujo principal del sistema (setup/loop).                     | Wemos R1 D1 (microcontrolador)  |
| **ajustarParametroHumedad.h** | Gestión de umbrales de humedad (mínimo y máximo).             | Botones físicos, LCD 16x2       |
| **control_rele.h**        | Control de relés para ventilador, luces y bomba de agua.        | Relés (GPIO D5, D9, D10)         |
| **leer_boton.h**          | Lectura de pulsadores para navegación y configuración.         | Botones físicos (GPIO D6, D7, D8)|
| **menu.h**                | Gestión del menú de opciones para modificar parámetros.         | Botones físicos, LCD 16x2       |
| **pantalla_principal.h**  | Visualización de la humedad en tiempo real.                     | Pantalla LCD 16x2 (I2C: GPIO D3, D4) |
| **LiquidCrystal_I2C.h**   | Comunicación I2C con el LCD.                                    | Pantalla LCD 16x2              |

### 3.4 Requisitos de Interfaz Externa

#### Interfaz de Usuario
- **Menú navegable:**  
  - Botón en GPIO D6: Disminuir valores o moverse hacia atrás.  
  - Botón en GPIO D7: Incrementar valores o moverse hacia adelante.  
  - Botón en GPIO D8: Seleccionar opción.
- **Pantalla Principal:**  
  - Visualización de humedad actual.  
  - Opciones para modificar umbrales, configurar ciclos de luz y controlar el ventilador.

#### Interfaz de Hardware
- **Sensor:**  
  - Sensor capacitivo conectado a GPIO A0.
- **Salidas:**  
  - Relé en GPIO D5: Bomba de agua.  
  - Relé en GPIO D9: Ventilador.  
  - Relé en GPIO D10: Luz.

## 4. Anexos

### 4.1 Diagramas de Casos de Uso
*(Adjuntar diagramas correspondientes)*

### 4.2 Configuración Hardware
**Wemos R1 D1:**
- GPIO A0: Sensor capacitivo.
- GPIO D9: Relé para ventilador.
- GPIO D10: Relé para luz.
- GPIO D3 (SDA): Pantalla LCD (I2C).
- GPIO D4 (SCL): Pantalla LCD (I2C).
- GPIO D5: Relé para bomba de agua.
- GPIO D6: Botón 1.
- GPIO D7: Botón 2.
- GPIO D8: Botón 3.

## 5. Requerimientos Futuros

### 5.1 Integración con IoT (Internet de las Cosas)
| ID     | Título                        | Descripción                                                                                 | Beneficio                              |
|--------|-------------------------------|---------------------------------------------------------------------------------------------|----------------------------------------|
| FR-001 | Monitoreo remoto vía Wi-Fi    | Conectar el Wemos R1 D1 a una plataforma cloud para visualizar datos (humedad, estado de actuadores) desde una app móvil. | Control remoto del sistema desde cualquier ubicación. |
| FR-002 | Notificaciones push           | Envío de alertas en tiempo real (por ejemplo, humedad crítica, fallo de sensores) al usuario.  | Respuesta rápida ante incidentes.      |
