<?php

$conn = new mysqli("localhost","root","","indoor");

$id = $_POST['id'];
$nombre = $_POST['nombre_comun'];
$cientifico = $_POST['nombre_cientifico'];
$familia = $_POST['familia'];
$genero = $_POST['genero'];

$imagenActual = $_POST['imagen_actual'];
$rutaImagen = $imagenActual;

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){

$carpeta = "uploads/";

$nombreArchivo = time()."_".$_FILES['imagen']['name'];

$rutaImagen = $carpeta.$nombreArchivo;

move_uploaded_file($_FILES['imagen']['tmp_name'],$rutaImagen);

}

$sql = "UPDATE plantas SET

nombre_comun='$nombre',
nombre_cientifico='$cientifico',
familia='$familia',
genero='$genero',
imagen_subida='$rutaImagen'

WHERE id=$id";

$conn->query($sql);

header("Location: planta.php?id=$id");

?>