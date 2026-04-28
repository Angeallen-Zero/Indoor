<?php
require('fpdf/fpdf.php');
include "conect.php";

if (!isset($_GET['id'])) {
    die("Pedido inválido");
}

$pedido_id = (int)$_GET['id'];

/* PEDIDO */
$pedido = $conexion->query("
    SELECT * FROM pedidos WHERE id=$pedido_id
")->fetch_assoc();

if (!$pedido) {
    die("No existe el pedido");
}

/* DETALLE DEL PEDIDO */
$detalles = $conexion->query("
    SELECT pd.*, p.nombre, p.imagen
    FROM pedido_detalle pd
    JOIN productos p ON pd.producto_id = p.id
    WHERE pd.pedido_id = $pedido_id
")->fetch_all(MYSQLI_ASSOC);

class PDF extends FPDF {

    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'GROW SYSTEM - TICKET DE COMPRA',0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Gracias por tu compra',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

/* INFO CLIENTE */
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Cliente: ".$pedido['nombre'],0,1);
$pdf->Cell(0,8,"Correo: ".$pedido['correo'],0,1);
$pdf->Cell(0,8,"Direccion: ".$pedido['direccion'],0,1);
$pdf->Cell(0,8,"Telefono: ".$pedido['celular'],0,1);
$pdf->Cell(0,8,"Fecha: ".$pedido['fecha'],0,1);

$pdf->Ln(5);

/* TABLA PRODUCTOS */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(50,10,'Producto',1);
$pdf->Cell(25,10,'Cantidad',1);
$pdf->Cell(35,10,'Precio',1);
$pdf->Cell(35,10,'Subtotal',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);

$total = 0;

foreach ($detalles as $d) {

    $subtotal = $d['cantidad'] * $d['precio'];
    $total += $subtotal;

    // Producto
    $pdf->Cell(50,20,$d['nombre'],1);

    // Cantidad
    $pdf->Cell(25,20,$d['cantidad'],1);

    // Precio
    $pdf->Cell(35,20,'$'.$d['precio'],1);

    // Subtotal
    $pdf->Cell(35,20,'$'.$subtotal,1);

    $pdf->Ln();
}

/* TOTAL */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(110,10,'TOTAL',1);
$pdf->Cell(35,10,'$'.$total,1);

$pdf->Output();
?>