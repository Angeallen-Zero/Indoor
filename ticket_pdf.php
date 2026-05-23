
<?php
ob_clean();
ob_start();

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
    SELECT pd.*, p.nombre
    FROM pedido_detalle pd
    JOIN productos p ON pd.producto_id = p.id
    WHERE pd.pedido_id = $pedido_id
")->fetch_all(MYSQLI_ASSOC);

class PDF extends FPDF {

    function Header() {

        $this->SetFont('Arial','B',20);
        $this->SetTextColor(34,34,34);
        $this->Cell(0,12,'GROW SYSTEM',0,1,'C');

        $this->SetFont('Arial','',11);
        $this->SetTextColor(90,90,90);
        $this->Cell(0,8,'Comprobante Oficial de Compra',0,1,'C');

        $this->Ln(5);

        $this->SetDrawColor(180,180,180);
        $this->Line(10,35,200,35);

        $this->Ln(10);
    }

    function Footer() {

        $this->SetY(-30);

        $this->SetDrawColor(200,200,200);
        $this->Line(10,$this->GetY(),200,$this->GetY());

        $this->Ln(5);

        $this->SetFont('Arial','I',9);
        $this->SetTextColor(80,80,80);

        $this->Cell(0,6,'Gracias por confiar en GROW SYSTEM.',0,1,'C');
        $this->Cell(0,6,'Tu pedido ha sido procesado correctamente.',0,1,'C');
        $this->Cell(0,6,'Este documento funciona como comprobante oficial de compra.',0,1,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

/* CAJA CONFIRMACION */

$pdf->SetFillColor(240,248,255);
$pdf->Rect(10,45,190,25,'F');

$pdf->SetXY(15,50);

$pdf->SetFont('Arial','B',16);
$pdf->SetTextColor(0,120,0);
$pdf->Cell(0,8,'COMPRA EXITOSA',0,1);

$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(60,60,60);

$pdf->SetX(15);
$pdf->Cell(0,6,'Tu pedido fue registrado y confirmado exitosamente.',0,1);

$pdf->Ln(12);

/* CLIENTE */

$pdf->SetFont('Arial','B',13);
$pdf->SetTextColor(30,30,30);
$pdf->Cell(0,8,'Informacion del Cliente',0,1);

$pdf->SetFont('Arial','',11);

$pdf->Cell(0,7,"Cliente: ".$pedido['nombre'],0,1);
$pdf->Cell(0,7,"Correo: ".$pedido['correo'],0,1);
$pdf->Cell(0,7,"Direccion: ".$pedido['direccion'],0,1);
$pdf->Cell(0,7,"Telefono: ".$pedido['celular'],0,1);
$pdf->Cell(0,7,"Fecha del pedido: ".$pedido['fecha'],0,1);
$pdf->Cell(0,7,"Numero de pedido: #".$pedido['id'],0,1);

$pdf->Ln(10);




/* MENSAJE FINAL */

$pdf->Ln(15);

$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0,100,0);

$pdf->MultiCell(
    0,
    8,
    utf8_decode("Tu compra fue procesada exitosamente y actualmente se encuentra en preparación. Recibirás futuras actualizaciones sobre el estado de tu pedido mediante los datos de contacto proporcionados."),
    0,
    'J'
);

$pdf->Ln(8);

$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(100,100,100);

$pdf->MultiCell(
    0,
    6,
    utf8_decode("GROW SYSTEM agradece tu preferencia. Nuestro compromiso es brindarte un servicio rápido, seguro y profesional."),
    0,
    'C'
);

/* GENERAR PDF */

$pdf->Output();

ob_end_flush();
?>