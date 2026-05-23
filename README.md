🌱 GrowSystem

Sistema web para la gestión de invernaderos inteligentes, control de plantas y monitoreo mediante integración con ESP32, base de datos MySQL y API de reconocimiento de plantas.

📌 Descripción

GrowSystem es una plataforma web desarrollada en PHP que permite:

Gestión de usuarios y autenticación
Administración de plantas e inventario
Control y monitoreo de un sistema de invernadero con ESP32
Registro de eventos y tareas del cultivo
Sistema de carrito y compras
Generación de tickets en PDF
Reconocimiento de plantas mediante la API de PlantNet
⚙️ Tecnologías utilizadas
PHP (Backend)
MySQL (Base de datos)
JavaScript (Frontend dinámico)
HTML5 / CSS3
ESP32 (IoT / sensores)
FPDF (Generación de PDFs)
PHPMailer (Correos electrónicos)
PlantNet API (Reconocimiento de plantas)
🚀 Instalación

Sigue estos pasos para ejecutar el proyecto en local:

1. Clonar el repositorio
git clone https://github.com/Angeallen-Zero/Indoor.git
2. Mover el proyecto a XAMPP

Coloca la carpeta dentro de:

C:\xampp\htdocs\

Ejemplo:

C:\xampp\htdocs\Indoor
3. Iniciar servicios

Abre XAMPP y enciende:

Apache
MySQL
4. Importar base de datos

Abre phpMyAdmin:

http://localhost/phpmyadmin
Crea una base de datos llamada:
growsystem
Importa el archivo:
growsystem.sql
5. Configurar conexión a base de datos

Revisa el archivo:

conect.php

Y asegúrate de tener algo como:

$conexion = new mysqli("localhost", "root", "", "growsystem");
6. Configurar API de PlantNet 🌿

Para usar el reconocimiento de plantas necesitas una API Key:

Regístrate en:
https://my.plantnet.org/
Obtén tu API Key
Colócala en el archivo correspondiente del proyecto (ejemplo):
$apiKey = "TU_API_KEY_AQUI";
📂 Estructura del proyecto
Indoor/
│
├── api/
├── Conexion con esp32/
├── css/
├── docs/
├── fpdf/
├── imagenes/
├── uploads/
├── PHPMailer/
│
├── index.php
├── login.php
├── carrito.php
├── checkout.php
├── ticket_pdf.php
├── procesar_pedido.php
└── growsystem.sql
🧾 Funcionalidades principales
🪴 Gestión de plantas
📦 Sistema de pedidos y carrito
📄 Generación de tickets PDF
📧 Envío de confirmaciones por correo
🌡️ Control de invernadero con ESP32
📷 Reconocimiento de plantas con IA (PlantNet)
🛠️ Notas importantes
El proyecto está diseñado para ejecutarse en entorno local con XAMPP
Asegúrate de tener habilitado mysqli y curl en PHP
La base de datos debe llamarse exactamente growsystem
👨‍💻 Autor

Desarrollado por Angel ramos 
                 Miguel Pantoja
                 Jonathan Alonso
