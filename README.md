🌱 GrowSystem - Sistema Indoor de Control Ambiental
Solución integral para monitorear y controlar automáticamente los niveles de humedad en entornos cerrados

📋 Descripción
GrowSystem es un software especializado diseñado para automatizar y monitorear las condiciones ambientales en espacios cerrados como:

🌾 Invernaderos
🏠 Cultivos interiores (indoor farming)
📦 Espacios de almacenamiento
🔬 Laboratorios controlados
El sistema mantiene condiciones óptimas mediante regulación automática basada en umbrales configurables, permitiendo a los usuarios controlar humedad, luz y ventilación de forma eficiente.

✨ Características Principales
🎛️ Control Ambiental
✅ Monitoreo en tiempo real de niveles de humedad
✅ Control automático de bomba de agua
✅ Gestión de ventilación (ON/OFF manual)
✅ Programación de ciclos de iluminación
✅ Umbrales configurables por el usuario
🛒 Tienda Integrada (GrowSystem)
✅ Catálogo de productos para cultivos
✅ Carrito de compras dinámico (AJAX)
✅ Sistema de checkout con captura de datos
✅ Gestión de pedidos
✅ Interfaz responsive y moderna
🔐 Seguridad
✅ Autenticación de usuarios
✅ Validación de sesiones
✅ Protección contra XSS (htmlspecialchars)
✅ Conexión segura a base de datos
📱 Experiencia de Usuario
✅ Interfaz intuitiva y responsiva
✅ Menú configurable
✅ Actualizaciones dinámicas sin recarga
✅ Contador de carrito en tiempo real
🏗️ Estructura del Proyecto
Angeallen-Zero/Indoor/ ├── htdocs/ │ └── Indoor/ │ ├── productos.php # Tienda de productos │ ├── carrito.php # Gestión del carrito │ ├── checkout.php # Finalización de pedidos │ ├── agregar_carrito.php # Lógica de carrito (AJAX) │ ├── eliminar_carrito.php # Eliminar productos │ ├── procesar_pedido.php # Procesamiento de pedidos │ ├── conect.php # Conexión a base de datos │ ├── inicio_indoor.php # Página de inicio/login │ ├── registro.php # Registro de usuarios │ ├── styles.css # Estilos globales │ ├── tienda.css # Estilos de tienda │ ├── carrito.css # Estilos del carrito │ ├── checkout.css # Estilos del checkout │ ├── js/ │ │ └── script.js # JavaScript global │ ├── icono.png # Logo de la aplicación │ └── README.md # Este archivo ├── docs/ │ ├── SRS.md # Especificación de Requisitos │ └── IEEE1016.md # Documentación de Diseño └── LICENSE # Licencia MIT 

## 🚀 Instalación y Configuración

### Requisitos Previos
- **Xampp
- **MySQL 

### Pasos de Instalación

#### 1️⃣ **Clonar el repositorio**
 descargar y importar la bd 
```bash
git clone https://github.com/Angeallen-Zero/Indoor.git
cd Indoor
4️⃣ Configurar conect.php
Edita htdocs/Indoor/conect.php con tus credenciales:

PHP
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "growsystem";

$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
