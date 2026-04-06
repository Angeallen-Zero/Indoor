<?php
session_start();

if(isset($_SESSION['usuario_id'])){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - GrowSystem</title>
<link rel="stylesheet" href="styles.css">

<style>

body{
background:#f4f7fb;
font-family:sans-serif;
}

/* HEADER IGUAL QUE TU APP */

.header{
background:#fff;
padding:20px 40px;
display:flex;
align-items:center;
justify-content:space-between;
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

/* LOGIN CARD */

.login-container{
height:80vh;
display:flex;
align-items:center;
justify-content:center;
}

.login-card{
background:white;
padding:40px;
border-radius:20px;
box-shadow:0 10px 25px rgba(0,0,0,0.1);
width:100%;
max-width:400px;
text-align:center;
}

.login-card h2{
margin-bottom:10px;
}

.login-card p{
color:#777;
margin-bottom:25px;
}

/* INPUTS */

.input-group{
text-align:left;
margin-bottom:15px;
}

.input-group label{
font-size:14px;
color:#555;
}

.input-group input{
width:100%;
padding:10px;
margin-top:5px;
border-radius:8px;
border:1px solid #ddd;
outline:none;
}

.input-group input:focus{
border-color:#3d5a6c;
}

/* BOTON */

.btn-login{
width:100%;
padding:12px;
background:#3d5a6c;
color:white;
border:none;
border-radius:10px;
cursor:pointer;
font-size:15px;
margin-top:10px;
transition:.2s;
}

.btn-login:hover{
background:#2c4655;
}

/* ERROR */

.error{
color:red;
margin-top:10px;
font-size:14px;
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

<div class="login-container">

<div class="login-card">

<h2>Iniciar Sesión</h2>
<p>Accede a tu sistema indoor</p>

<form action="procesar-login.php" method="POST">

<div class="input-group">
<label>Correo</label>
<input type="email" name="correo" required>
</div>

<div class="input-group">
<label>Contraseña</label>
<input type="password" name="password" required>
</div>

<button type="submit" class="btn-login">
Ingresar
</button>

</form>
<div style="margin-top:20px; font-size:14px; text-align:center;">
    ¿No tienes cuenta? 
    <a href="registro.php" style="color:#31c048; font-weight:600;">Crear cuenta</a>
</div>

<?php if(isset($_GET['error'])){ ?>
<div class="error">
Credenciales incorrectas
</div>
<?php } ?>

</div>

</div>

</body>
</html>