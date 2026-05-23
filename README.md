# 🌱 GrowSystem - Sistema Inteligente de Invernadero Indoor

![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)
![ESP32](https://img.shields.io/badge/ESP32-IoT-red)
![XAMPP](https://img.shields.io/badge/XAMPP-Required-yellow)
![Status](https://img.shields.io/badge/Status-Development-green)

## 📖 Descripción

**GrowSystem** es una plataforma web desarrollada para la administración y monitoreo de un invernadero indoor inteligente utilizando **ESP32**, sensores ambientales y una interfaz web en **PHP + MySQL**.

El sistema permite:

* 🌡️ Monitoreo de temperatura y humedad
* 💧 Control automático de riego
* 💡 Gestión de ciclos de iluminación
* 🌿 Registro y administración de plantas
* 🛒 Sistema de tienda y carrito de compras
* 📅 Calendario de eventos
* 📄 Generación de tickets PDF
* 📡 Comunicación entre ESP32 y servidor web
* 🔐 Sistema de autenticación de usuarios

---

# 🛠️ Tecnologías Utilizadas

* PHP
* MySQL
* JavaScript
* HTML5 / CSS3
* Bootstrap
* ESP32
* Arduino IDE
* PHPMailer
* FPDF

---

# 📂 Estructura del Proyecto

```bash
Indoor/
│
├── api/                  # Endpoints y controladores
├── uploads/              # Imágenes subidas
├── docs/                 # Documentación y diagramas
├── codigo de arduino/    # Código para ESP32
├── PHPMailer/            # Librería de correos
├── fpdf/                 # Librería para PDFs
├── css/                  # Estilos
├── js/                   # Scripts JavaScript
└── growsystem.sql        # Base de datos
```

---

# ⚙️ Requisitos

Antes de comenzar necesitas tener instalado:

* ✅ XAMPP
* ✅ PHP 8 o superior
* ✅ MySQL
* ✅ Arduino IDE (opcional para ESP32)
* ✅ Cuenta y API Key de PlantNet

---

# 🚀 Instalación

## 1️⃣ Clonar el repositorio

```bash
git clone https://github.com/Angeallen-Zero/Indoor.git
```

---

## 2️⃣ Mover el proyecto a htdocs

Coloca la carpeta del proyecto dentro de:

```bash
xampp/htdocs/
```

---

## 3️⃣ Importar la base de datos

1. Abrir **phpMyAdmin**
2. Crear una base de datos llamada:

```bash
growsystem
```

3. Importar el archivo:

```bash
growsystem.sql
```

---

## 4️⃣ Configurar conexión a la base de datos

Editar el archivo:

```bash
api/db.php
```

o

```bash
conexion.php
```

Configurar:

```php
$host = "localhost";
$user = "root";
$password = "";
$db = "growsystem";
```

---

## 5️⃣ Configurar API Key de PlantNet

Obtener una API Key desde:

https://my.plantnet.org/

Luego agregarla en el archivo correspondiente del proyecto.

---

# ▶️ Ejecutar el Proyecto

Iniciar:

* Apache
* MySQL

Desde XAMPP.

Luego abrir:

```bash
http://localhost/Indoor
```

---

# 📸 Funcionalidades Principales

## 🌿 Gestión de Plantas

* Registro de plantas
* Edición y eliminación
* Subida de imágenes
* Detección mediante PlantNet

## 📡 Integración IoT

* Comunicación ESP32 ↔ Servidor
* Sensores ambientales
* Automatización de riego

## 🛒 Sistema Ecommerce

* Catálogo de productos
* Carrito de compras
* Checkout
* Tickets PDF

## 📅 Calendario

* Eventos personalizados
* Organización de tareas del cultivo

---

# 🧠 Arquitectura del Sistema

El proyecto utiliza una arquitectura basada en:

* Cliente Web
* API PHP
* Base de Datos MySQL
* Dispositivo ESP32
* Sensores y actuadores

---

# 📄 Documentación

Dentro de la carpeta `docs/` encontrarás:

* Diagramas UML
* Arquitectura del software
* Diagramas de flujo
* Casos de uso
* IEEE1016
* SRS

---

# 🔒 Seguridad

* Sistema de login y registro
* Validación de sesiones
* Manejo de autenticación
* Protección básica de rutas administrativas

---

# 👨‍💻 Autores

Desarrollado por:

* **Miguel Pantoja**
* **Angel Ramos**
* **Jonathan Alonso**

GitHub del proyecto:
https://github.com/Angeallen-Zero/Indoor


---

# 📌 Estado del Proyecto

🚧 Proyecto en desarrollo activo.

---

# ⭐ Contribuciones

Las contribuciones, mejoras y sugerencias son bienvenidas.

---

# 📜 Licencia

Este proyecto es de uso académico y educativo.
