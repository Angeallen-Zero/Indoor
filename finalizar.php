<?php
session_start();

$pedido_id = $_SESSION['ultimo_pedido'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compra realizada</title>

<style>
body{
  font-family: Arial;
  background:#f3f6f4;
}

.card{
  max-width:420px;
  margin:100px auto;
  background:#fff;
  padding:30px;
  border-radius:12px;
  text-align:center;
  box-shadow:0 10px 20px rgba(0,0,0,.08);
}

.btn{
  display:block;
  margin-top:15px;
  padding:12px;
  border-radius:10px;
  text-decoration:none;
  font-weight:bold;
}

.primary{background:#31C048;color:#fff;}
.secondary{border:1px solid #31C048;color:#31C048;}
</style>
</head>

<body>

<div class="card">

  <h2>✅ Compra realizada</h2>
  <p>Tu pedido fue registrado correctamente</p>

  <?php if ($pedido_id): ?>
    <a class="btn primary" target="_blank" href="ticket_pdf.php?id=<?= $pedido_id ?>">
      Descargar PDF
    </a>
  <?php else: ?>
    <p style="color:red;">No se encontró el pedido</p>
  <?php endif; ?>

  <a class="btn secondary" href="productos.php">
    Seguir comprando
  </a>

</div>

</body>
</html>