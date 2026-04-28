<?php
session_start();
include "conect.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: acceso.php');
    exit;
}

// ELIMINAR PEDIDO
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM pedidos WHERE id=$id");
    header("Location: admin_pedidos.php");
    exit;
}

// CAMBIAR ESTADO
if (isset($_GET['enviado'])) {
    $id = (int)$_GET['enviado'];
    $conexion->query("UPDATE pedidos SET estado='enviado' WHERE id=$id");
    header("Location: admin_pedidos.php");
    exit;
}

$pedidos = $conexion->query("SELECT * FROM pedidos ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem — Pedidos</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">

<style>
:root {
  --verde:#31C048;
  --verde-dark:#219E35;
  --gris-bg:#f0f4f2;
  --gris-borde:#e2ebe4;
  --gris-texto:#6b7c6e;
  --texto:#1e2d22;
  --rojo:#e05252;
  --azul:#3d5a6c;
  --font:'DM Sans',sans-serif;
  --mono:'DM Mono',monospace;
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
  font-family:var(--font);
  background:var(--gris-bg);
}

/* HEADER */
.header{
  background:#fff;
  border-bottom:1px solid var(--gris-borde);
  padding:0 32px;
  height:64px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  flex-wrap:wrap;
}

.header-logo{
  display:flex;
  align-items:center;
  gap:10px;
  text-decoration:none;
}

.header-logo img{width:34px;height:34px;}
.header-logo span{font-size:20px;font-weight:600;color:var(--verde);}

/* BOTONES */
.btn{
  padding:8px 14px;
  border-radius:8px;
  text-decoration:none;
  font-size:13px;
  font-weight:600;
  display:inline-block;
}

.btn-verde{background:var(--verde);color:#fff;}
.btn-rojo{background:var(--rojo);color:#fff;}
.btn-azul{background:var(--azul);color:#fff;}

/* PAGE */
.page{
  max-width:1200px;
  margin:0 auto;
  padding:32px;
}

.titulo{
  font-size:26px;
  font-weight:600;
  margin-bottom:20px;
  color:var(--texto);
}

/* TABLA WRAP (IMPORTANTE) */
.tabla-wrap{
  width:100%;
  overflow-x:auto;
}

/* TABLA */
table{
  width:100%;
  min-width:900px;
  background:#fff;
  border-radius:12px;
  overflow:hidden;
  border:1px solid var(--gris-borde);
  border-collapse:collapse;
}

th{
  background:var(--gris-bg);
  padding:12px;
  font-size:12px;
  text-transform:uppercase;
  color:var(--gris-texto);
}

td{
  padding:12px;
  font-size:13px;
  border-bottom:1px solid var(--gris-borde);
  text-align:center;
  vertical-align:middle;
}

/* ESTADOS */
.estado{font-weight:600;}
.pendiente{color:orange;}
.enviado{color:var(--verde-dark);}

/* 🔥 BOTONES EN ACCIONES */
td:last-child{
  display:flex;
  gap:6px;
  justify-content:center;
  flex-wrap:wrap;
}

/* =========================
   RESPONSIVE FIX
========================= */

@media (max-width:768px){

  .page{
    padding:16px;
  }

  .header{
    height:auto;
    padding:12px;
    gap:10px;
  }

  .header div{
    width:100%;
    display:flex;
    justify-content:flex-end;
    gap:8px;
  }

  td:last-child{
    flex-direction:column;
    align-items:stretch;
  }

  .btn{
    width:100%;
    text-align:center;
  }
  @media (max-width: 768px) {

  table {
    min-width: unset;
    display: block;
  }

  table thead {
    display: none;
  }

  table, tbody, tr, td {
    display: block;
    width: 100%;
  }

  tr {
    background: #fff;
    border: 1px solid var(--gris-borde);
    border-radius: 12px;
    margin-bottom: 12px;
    padding: 12px;
  }

  td {
    text-align: left;
    border: none;
    padding: 6px 0;
    display: flex;
    justify-content: space-between;
  }

  td::before {
    content: attr(data-label);
    font-weight: 600;
    color: var(--gris-texto);
  }

  td:last-child {
    flex-direction: column;
    gap: 6px;
  }

  .btn {
    width: 100%;
  }
}
}
</style>
</head>

<body>

<header class="header">
  <a href="admin.php" class="header-logo">
    <img src="icono.png">
    <span>GrowSystem</span>
  </a>

  <div>
    <a href="admin.php" class="btn btn-azul">Panel</a>
    <a href="admin_productos.php" class="btn btn-verde">Productos</a>
  </div>
</header>

<div class="page">

<h1 class="titulo">Pedidos realizados</h1>

<div class="tabla-wrap">
<table>
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Dirección</th>
    <th>Celular</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Fecha</th>
    <th>Acciones</th>
</tr>

<?php while($row = $pedidos->fetch_assoc()) { ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['nombre'] ?></td>
    <td><?= $row['correo'] ?></td>
    <td><?= $row['direccion'] ?></td>
    <td><?= $row['celular'] ?></td>
    <td>$<?= $row['total'] ?></td>

    <td class="estado <?= $row['estado'] ?>">
        <?= $row['estado'] ?? 'pendiente' ?>
    </td>

    <td><?= $row['fecha'] ?></td>

    <td>
        <?php if(($row['estado'] ?? 'pendiente') != 'enviado'): ?>
            <a class="btn btn-verde" href="?enviado=<?= $row['id'] ?>">✔</a>
        <?php endif; ?>

        <a class="btn btn-rojo" href="?eliminar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar pedido?')">🗑</a>

        <a class="btn btn-azul" href="ticket_pdf.php?id=<?= $row['id'] ?>">PDF</a>
    </td>
</tr>
<?php } ?>

</table>
</div>

</div>

</body>
</html>