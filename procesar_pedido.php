<?php
session_start();
include "conect.php";

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================
// DATOS
// ==========================
$nombre    = trim($_POST['nombre'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$celular   = trim($_POST['celular'] ?? '');

// ==========================
// VALIDACIÓN
// ==========================
if ($nombre === '' || $correo === '' || $direccion === '' || $celular === '') {
    die("❌ Faltan datos del formulario");
}

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    die("❌ El carrito está vacío");
}

// ==========================
// CALCULAR TOTAL
// ==========================
$total = 0;
$productosHTML = "";

foreach ($_SESSION['carrito'] as $id => $cantidad) {

    $id = (int)$id;

    $res = $conexion->query("SELECT * FROM productos WHERE id=$id");

    if ($res && $res->num_rows > 0) {

        $p = $res->fetch_assoc();

        $subtotal = $p['precio'] * $cantidad;
        $total += $subtotal;

        $productosHTML .= "
        <tr>
            <td>{$p['nombre']}</td>
            <td>$ {$p['precio']}</td>
            <td>{$cantidad}</td>
            <td>$ {$subtotal}</td>
        </tr>";
    }
}

// ==========================
// GUARDAR PEDIDO
// ==========================
$stmt = $conexion->prepare("
INSERT INTO pedidos (nombre, correo, direccion, celular, total)
VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssd", $nombre, $correo, $direccion, $celular, $total);
$stmt->execute();

// 🔴 AQUÍ ESTABA TU ERROR
$pedido_id = $conexion->insert_id;
$_SESSION['ultimo_pedido'] = $pedido_id;

$stmt->close();

// ==========================
// EMAIL
// ==========================
$mail = new PHPMailer(true);

try {

    $mail->SMTPDebug = 0;
    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'growsystem448@gmail.com';
    $mail->Password = 'zhbhogrmwjxurxio';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('growsystem448@gmail.com', 'GrowSystem');
    $mail->addAddress($correo, $nombre);

    $mail->isHTML(true);
    $mail->Subject = "Compra confirmada - GrowSystem";

    $mail->Body = "
    <h2>🌱 Gracias por tu compra $nombre</h2>

    <h3>🛒 Productos:</h3>
    <table border='1' cellpadding='6' cellspacing='0'>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
        $productosHTML
    </table>

    <h2>💰 Total: $$total</h2>
    ";

    $mail->send();

    unset($_SESSION['carrito']);

    header("Location: finalizar.php");
    exit;

} catch (Exception $e) {
    echo "❌ ERROR SMTP: {$mail->ErrorInfo}";
}