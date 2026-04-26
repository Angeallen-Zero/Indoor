# 🌱 GrowSystem - Tienda Online + Sistema Indoor de Control Ambiental

> **Solución completa para monitoreo ambiental automático y gestión de tienda online de productos agrícolas**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)

---

## 📋 Descripción

**GrowSystem** es una plataforma integrada que combina:

### 🛒 **Tienda Online**
Sistema completo de e-commerce para compra de productos agrícolas con carrito dinámico, checkout seguro y confirmación por correo.

### 🎛️ **Sistema Indoor de Control Ambiental**
Software de monitoreo y automatización para invernaderos y cultivos interiores que controla:
- 💧 Niveles de humedad
- 💡 Ciclos de iluminación
- 💨 Ventilación automática
- 🌡️ Condiciones ambientales óptimas

---

## ✨ Características Principales

### 🛍️ **Módulo de Tienda**

#### Gestión de Productos
```php
productos.php      // Catálogo de productos con grid responsive
carrito.php        // Visualización del carrito con cálculos dinámicos
checkout.php       // Proceso de compra con captura de datos
Funcionalidades:

✅ Catálogo de productos desde base de datos
✅ Carrito de compras con sesiones PHP
✅ Control de cantidades (aumentar/disminuir)
✅ Cálculo automático de subtotales y totales
✅ Eliminación de productos del carrito
✅ Badge contador en tiempo real
✅ Interfaz responsiva y moderna
Procesamiento de Pedidos
agregar_carrito.php    // Lógica de adición/sustracción de productos
procesar_pedido.php    // Validación, almacenamiento y confirmación por email
Funcionalidades:

✅ Validación de datos de cliente
✅ Almacenamiento en base de datos
✅ Integración con PHPMailer para confirmación por correo
✅ Plantillas HTML para email
✅ Manejo seguro de sesiones
✅ Redirección post-compra
🎛️ Sistema Indoor
Hardware:

🖥️ Microcontrolador Wemos R1 D1 (ESP8266)
💧 Sensor capacitivo de humedad (GPIO A0)
📺 Pantalla LCD con protocolo I2C
🔌 Relés para control de bomba, ventilador y luces
🔘 3 botones de control (GPIO D5, D6, D7)
Software:

Arduino Framework (.ino y .h)
Lectura de sensores en tiempo real
Control automático con umbrales configurables
Interfaz en pantalla LCD
Sistema de menú navegable
🔐 Seguridad
✅ Conexión segura a base de datos MySQL
✅ Validación de datos en formularios
✅ Protección contra XSS
✅ Gestión de sesiones
✅ Credenciales en archivo separado (conect.php)
📱 Experiencia de Usuario
✅ Interfaz intuitiva y limpia
✅ Diseño responsivo (mobile-first)
✅ Animaciones y transiciones suaves
✅ Actualizaciones sin recarga de página
✅ Feedback visual inmediato
Angeallen-Zero/Indoor/
├── htdocs/Indoor/                          # Raíz del servidor web
│   ├── TIENDA (Módulo de E-commerce)
│   │   ├── productos.php                   # Catálogo de productos
│   │   ├── carrito.php                     # Visualización del carrito
│   │   ��── checkout.php                    # Formulario de compra
│   │   ├── agregar_carrito.php             # Lógica de carrito (POST)
│   │   ├── procesar_pedido.php             # Procesa compra y envía email
│   │   ├── eliminar_carrito.php            # Elimina producto del carrito
│   │   └── finalizar.php                   # Página de confirmación
│   │
│   ├── AUTENTICACIÓN
│   │   ├── inicio_indoor.php               # Login/Inicio
│   │   └── registro.php                    # Registro de usuarios
│   │
│   ├── BASE DE DATOS
│   │   └── conect.php                      # Conexión MySQL
│   │
│   ├── ESTILOS CSS
│   │   ├── styles.css                      # Estilos globales
│   │   ├── tienda.css                      # Estilos de tienda
│   │   ├── carrito.css                     # Estilos del carrito
│   │   ├── checkout.css                    # Estilos del checkout
│   │   └── registro.css                    # Estilos de autenticación
│   │
│   ├── JAVASCRIPT
│   │   ├── js/script.js                    # Script global (Isotope, Swiper)
│   │   ├── calendario.js                   # Calendario de eventos
│   │   └── agregar_carrito.js              # AJAX del carrito
│   │
│   ├── LIBRERÍAS EXTERNAS
│   │   └── PHPMailer/                      # Librería para envío de emails
│   │       ├── src/Exception.php
│   │       ├── src/PHPMailer.php
│   │       └── src/SMTP.php
│   │
│   ├── ASSETS
│   │   ├── icono.png                       # Logo de GrowSystem
│   │   ├── imagenes/                       # Imágenes de eventos
│   │   └── productos_img/                  # Imágenes de productos
│   │
│   └── README.md                           # Este archivo
│
├── docs/                                   # Documentación técnica
│   ├── SRS.md                              # Especificación de Requisitos
│   └── IEEE1016.md                         # Documento de Diseño (IEEE 1016)
│
└── LICENSE                                 # Licencia MIT
