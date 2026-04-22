<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$id = $_POST['id'];
$accion = $_POST['accion'];

/* 🛒 agregar directo desde producto */
if ($accion == "agregar") {
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]++;
    } else {
        $_SESSION['carrito'][$id] = 1;
    }
}

/* ➕ desde carrito */
if ($accion == "mas") {
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]++;
    }
}

/* ➖ desde carrito */
if ($accion == "menos") {
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]--;

        if ($_SESSION['carrito'][$id] <= 0) {
            unset($_SESSION['carrito'][$id]);
        }
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>