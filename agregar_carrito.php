<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (!isset($_SESSION['cantidad_temp'])) {
    $_SESSION['cantidad_temp'] = [];
}

$id = $_POST['id'];
$accion = $_POST['accion'];

if (!isset($_SESSION['cantidad_temp'][$id])) {
    $_SESSION['cantidad_temp'][$id] = 0;
}

// ➕ sumar
if ($accion == "mas") {
    $_SESSION['cantidad_temp'][$id]++;
}

// ➖ restar
if ($accion == "menos") {
    if ($_SESSION['cantidad_temp'][$id] > 0) {
        $_SESSION['cantidad_temp'][$id]--;
    }
}

// 🛒 agregar al carrito
if ($accion == "agregar") {

    $cantidad = $_SESSION['cantidad_temp'][$id];

    if ($cantidad > 0) {

        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }
    }

    // reset
    $_SESSION['cantidad_temp'][$id] = 0;
}

header("Location: productos.php");
?>