<?php

//  GrowSystem — Cerrar sesión
//  Archivo: logout.php

session_start();
$_SESSION = [];
session_destroy();
header('Location: inicio_indoor.php');
exit;
