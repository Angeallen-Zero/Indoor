<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Planta - GrowSystem</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="styles.css">

<style>

.resultado-dinamico{
margin-top:30px;
}

.card-resultado{
background:white;
padding:25px;
border-radius:16px;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
text-align:center;
}

.card-resultado img{
max-width:220px;
border-radius:10px;
margin-top:10px;
}

.loader{
margin-top:15px;
font-weight:bold;
color:#2e7d32;
}

.success{
color:green;
font-weight:bold;
}

.error{
color:red;
font-weight:bold;
}

/* BOTON VOLVER */

.boton-volver{
font-size:22px;
text-decoration:none;
margin-right:10px;
color:#3d5a6c;
font-weight:bold;
}

.encabezado{
background:#fff;
padding:20px 40px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.seccion-logo{
display:flex;
align-items:center;
gap:10px;
}

.icono-planta{
font-size:30px;
}

.titulo-app{
color:rgb(49,192,72);
font-size:26px;
font-weight:600;
}

.contenedor{
width:85%;
max-width:1000px;
margin:60px auto;
}

.titulo-pagina{
font-size:36px;
margin-bottom:10px;
}

.subtitulo-pagina{
color:#6b7c8d;
margin-bottom:40px;
}

.contenedor-opciones{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
gap:25px;
}

.tarjeta-opcion{
background:white;
padding:30px;
border-radius:16px;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
text-align:center;
}

.tarjeta-opcion:hover{
transform:translateY(-4px);
transition:.2s;
}

.boton-opcion{
margin-top:20px;
background:#3d5a6c;
color:white;
border:none;
padding:12px 20px;
border-radius:10px;
cursor:pointer;
font-size:15px;
}

.boton-opcion:hover{
background:#2c4655;
}

</style>

</head>

<body>

<header class="encabezado">

<div class="seccion-logo">

<a href="index.php" class="boton-volver">←</a>

<div class="plantIcon">
<img src="icono.png" width="35">
</div>

<h1 class="titulo-app">GrowSystem</h1>

</div>

</header>

<main class="contenedor">

<section class="encabezado-pagina">

<h2 class="titulo-pagina">Agregar Nueva Planta</h2>

<p class="subtitulo-pagina">
Elige cómo deseas agregar tu planta
</p>

</section>

<section class="contenedor-opciones">

<!-- OPCION MANUAL -->

<div class="tarjeta-opcion">

<h3 class="titulo-opcion">Agregar Manualmente</h3>

<p class="descripcion-opcion">
Ingresa los detalles manualmente.
</p>

<button class="boton-opcion"
onclick="location.href='add-manual.php'">

Ingresar Datos

</button>

</div>

<!-- OPCION ESCANER -->

<div class="tarjeta-opcion">

<h3 class="titulo-opcion">Escanear con Foto</h3>

<p class="descripcion-opcion">
Toma una foto y obtén información automática.
</p>

<button class="boton-opcion" id="btnEscanear">

Tomar Foto

</button>

<input
type="file"
id="inputFoto"
accept="image/*"
capture="environment"
style="display:none;">

</div>

</section>

<div id="resultadoEscaneo" class="resultado-dinamico"></div>

</main>

<script>

const btnEscanear = document.getElementById("btnEscanear");
const inputFoto = document.getElementById("inputFoto");
const resultado = document.getElementById("resultadoEscaneo");

btnEscanear.addEventListener("click", () => {
inputFoto.click();
});

inputFoto.addEventListener("change", async () => {

const archivo = inputFoto.files[0];
if(!archivo) return;

resultado.innerHTML =
"<div class='loader'>🔍 Analizando imagen...</div>";

const formData = new FormData();
formData.append("imagen", archivo);

try{

const respuesta = await fetch("procesar-imagen.php",{
method:"POST",
body:formData
});

const datos = await respuesta.json();

if(datos.error){

resultado.innerHTML =
"<div class='error'>Error: "+datos.error+"</div>";

return;

}

resultado.innerHTML = `

<div class="card-resultado">

<h2>${datos.nombreComun}</h2>

<p><i>${datos.nombreCientifico}</i></p>

<p><b>Familia:</b> ${datos.familia}</p>

<p><b>Género:</b> ${datos.genero}</p>

<p><b>Confianza:</b> ${datos.confianza}%</p>

<img src="${datos.imagenSubida}">

${datos.imagenReferencia ? `<img src="${datos.imagenReferencia}">` : ""}

<br><br>

<button
type="button"
id="guardarBtn"
class="boton-opcion">

💾 Guardar Planta

</button>

</div>
`;

document
.getElementById("guardarBtn")
.addEventListener("click", async()=>{

try{

const guardar = await fetch("guardar-planta.php",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify(datos)

});

const resGuardar = await guardar.json();

if(resGuardar.success){

window.location.href="index.php";

}else{

resultado.innerHTML +=
"<div class='error'>Error: "+
(resGuardar.error || "No se pudo guardar")
+"</div>";

}

}catch(error){

resultado.innerHTML +=
"<div class='error'>Error al conectar con guardar-planta.php</div>";

}

});

}catch(error){

resultado.innerHTML =
"<div class='error'>Error de conexión</div>";

}

});

</script>

</body>
</html>