<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Registro - GrowSystem</title>

<style>
body{
background:#f4f7fb;
font-family:sans-serif;
}

.header{
background:#fff;
padding:20px 40px;
display:flex;
align-items:center;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

.logo{
display:flex;
align-items:center;
gap:10px;
}

.logo img{
width:40px;
}

.Titulo{
color:rgb(49,192,72);
font-size:26px;
font-weight:600;
}

.container{
height:80vh;
display:flex;
justify-content:center;
align-items:center;
}

.card{
background:white;
padding:40px;
border-radius:20px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
width:100%;
max-width:400px;
}

input{
width:100%;
padding:10px;
margin-top:10px;
border-radius:8px;
border:1px solid #ddd;
}

button{
margin-top:15px;
width:100%;
padding:12px;
background:#3d5a6c;
color:white;
border:none;
border-radius:10px;
cursor:pointer;
}

button:hover{
background:#2c4655;
}

.error{
color:red;
margin-top:10px;
}

</style>
</head>

<body>

<header class="header">
<div class="logo">
<img src="icono.png">
<h1 class="Titulo">GrowSystem</h1>
</div>
</header>

<div class="container">

<div class="card">

<h2>Crear cuenta</h2>

<form action="procesar-registro.php" method="POST">

<input type="text" name="nombre" placeholder="Nombre" required>
<input type="email" name="correo" placeholder="Correo" required>
<input type="password" name="password" placeholder="Contraseña" required>

<button>Registrarse</button>

</form>

<?php if(isset($_GET['error'])){ ?>
<div class="error">Ese correo ya existe</div>
<?php } ?>

</div>

</div>

</body>
</html>