<?php

$conn = new mysqli("localhost", "root", "", "indoor");

$id = intval($_GET['id']);

$result = $conn->query("SELECT * FROM plantas WHERE id=$id");

$planta = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo $planta['nombre_comun']; ?> - GrowSystem</title>

<link rel="stylesheet" href="styles.css">

<style>

.detalle-container{
max-width:1200px;
margin:auto;
margin-top:30px;
}

.layout-planta{
display:grid;
grid-template-columns:1fr 1fr;
gap:25px;
}

.planta-card{
background:white;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
overflow:hidden;
}

.planta-img{
width:100%;
height:320px;
object-fit:cover;
}

.planta-info{
padding:25px;
}

.planta-info input{
width:100%;
padding:8px;
margin-top:5px;
border-radius:6px;
border:1px solid #ddd;
}

.file-input{
margin-top:10px;
}

.guardar-btn{
margin-top:10px;
padding:10px;
width:100%;
background:#3d5a6c;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
font-weight:bold;
}

.guardar-btn:hover{
background:#2c4655;
}

.panel-grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:20px;
}

.panel-card{
background:white;
border-radius:15px;
padding:20px;
box-shadow:0 10px 20px rgba(0,0,0,0.08);
text-align:center;
}

.panel-card h3{
margin-bottom:10px;
font-size:15px;
color:#3d5a6c;
}

.panel-valor{
font-size:26px;
font-weight:bold;
}

.panel-estado{
font-size:12px;
color:#777;
margin-top:5px;
}

.btn-volver{
display:inline-block;
margin-bottom:20px;
padding:8px 15px;
background:#3d5a6c;
color:white;
border-radius:8px;
text-decoration:none;
}

@media(max-width:900px){

.layout-planta{
grid-template-columns:1fr;
}

.panel-grid{
grid-template-columns:1fr 1fr;
}

}

</style>

</head>

<body>

<header class="header">

<div class="logo">

<div class="plantIcon">
<img src="icono.png" width="40">
</div>

<h1 class="Titulo">GrowSystem</h1>

</div>

<div class="avatar">

<svg viewBox="0 0 24 24" width="24" height="24" fill="#3d5a6c">
<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
10-4.48 10-10S17.52 2 12 2z"/>
</svg>

</div>

</header>

<div class="container detalle-container">

<a href="index.php" class="btn-volver">⬅ Volver</a>

<div class="layout-planta">

<!-- IZQUIERDA -->

<div class="planta-card">

<img src="<?php echo $planta['imagen_subida']; ?>" 
class="planta-img" 
id="previewImagen">

<form action="editar-planta.php" 
method="POST" 
enctype="multipart/form-data" 
class="planta-info">

<input type="hidden" name="id" value="<?php echo $planta['id']; ?>">

<input type="hidden" 
name="imagen_actual" 
value="<?php echo $planta['imagen_subida']; ?>">

<h2 id="nombreTexto">
<?php echo $planta['nombre_comun']; ?>
</h2>

<input type="text"
name="nombre_comun"
id="nombreInput"
value="<?php echo $planta['nombre_comun']; ?>"
style="display:none">

<p>

<strong>Nombre científico:</strong>

<span id="cientificoTexto">
<?php echo $planta['nombre_cientifico']; ?>
</span>

<input type="text"
name="nombre_cientifico"
id="cientificoInput"
value="<?php echo $planta['nombre_cientifico']; ?>"
style="display:none">

</p>

<p>

<strong>Familia:</strong>

<span id="familiaTexto">
<?php echo $planta['familia']; ?>
</span>

<input type="text"
name="familia"
id="familiaInput"
value="<?php echo $planta['familia']; ?>"
style="display:none">

</p>

<p>

<strong>Género:</strong>

<span id="generoTexto">
<?php echo $planta['genero']; ?>
</span>

<input type="text"
name="genero"
id="generoInput"
value="<?php echo $planta['genero']; ?>"
style="display:none">

</p>

<p>
<strong>Fecha:</strong>
<?php echo $planta['fecha']; ?>
</p>

<input type="file"
name="imagen"
id="imagenInput"
class="file-input"
style="display:none"
accept="image/*">

<div style="margin-top:15px">

<button type="button"
onclick="activarEdicion()"
id="btnEditar"
class="guardar-btn">

✏️ Editar

</button>

<button type="submit"
id="btnGuardar"
class="guardar-btn"
style="display:none">

💾 Guardar

</button>

<button type="button"
onclick="cancelarEdicion()"
id="btnCancelar"
class="guardar-btn"
style="display:none;background:#777">

Cancelar

</button>

</div>

</form>

</div>

<!-- PANEL DERECHO -->

<div class="panel-grid">

<div class="panel-card">
<h3>🌡 Temperatura</h3>
<div class="panel-valor">24°</div>
<div class="panel-estado">Óptima</div>
</div>

<div class="panel-card">
<h3>💧 Humedad</h3>
<div class="panel-valor">60%</div>
<div class="panel-estado">Normal</div>
</div>

<div class="panel-card">
<h3>💡 Iluminación</h3>
<div class="panel-valor">ON</div>
<div class="panel-estado">LED Activa</div>
</div>

<div class="panel-card">
<h3>🌬 Ventilación</h3>
<div class="panel-valor">ON</div>
<div class="panel-estado">Circulando</div>
</div>

<div class="panel-card">
<h3>💦 Riego</h3>
<div class="panel-valor">OFF</div>
<div class="panel-estado">Manual</div>
</div>

<div class="panel-card">
<h3>🌱 Estado</h3>
<div class="panel-valor">OK</div>
<div class="panel-estado">Crecimiento</div>
</div>

</div>

</div>

</div>

<script>

function activarEdicion(){

document.getElementById("nombreTexto").style.display="none"
document.getElementById("cientificoTexto").style.display="none"
document.getElementById("familiaTexto").style.display="none"
document.getElementById("generoTexto").style.display="none"

document.getElementById("nombreInput").style.display="block"
document.getElementById("cientificoInput").style.display="block"
document.getElementById("familiaInput").style.display="block"
document.getElementById("generoInput").style.display="block"

document.getElementById("imagenInput").style.display="block"

document.getElementById("btnEditar").style.display="none"
document.getElementById("btnGuardar").style.display="block"
document.getElementById("btnCancelar").style.display="block"

}

function cancelarEdicion(){

location.reload()

}

document
.getElementById("imagenInput")
.addEventListener("change", function(e){

const reader = new FileReader()

reader.onload = function(){

document.getElementById("previewImagen").src = reader.result

}

reader.readAsDataURL(e.target.files[0])

})

</script>

</body>
</html>