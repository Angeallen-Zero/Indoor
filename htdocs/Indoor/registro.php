<?php

//  GrowSystem — Registro de usuario
//  Archivo: registro.php

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error  = '';
$exito  = false;
$campos = ['nombre' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/api/db.php';

    $nombre    = trim($_POST['nombre']    ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = trim($_POST['password']  ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    $campos['nombre'] = $nombre;
    $campos['email']  = $email;

    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo no tiene un formato válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $db = getDB();

        // Verificar que el email no exista
        $chk = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $chk->bind_param('s', $email);
        $chk->execute();
        $existe = $chk->get_result()->num_rows > 0;
        $chk->close();

        if ($existe) {
            $error = 'Ya existe una cuenta con ese correo.';
            $db->close();
        } else {
            // Guardar con bcrypt
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
            $stmt->bind_param('sss', $nombre, $email, $hash);

            if ($stmt->execute()) {
                $nuevoId = $stmt->insert_id;
                $stmt->close();
                $db->close();

                // Iniciar sesión automáticamente
                $_SESSION['usuario_id'] = $nuevoId;
                $_SESSION['nombre']     = $nombre;
                $_SESSION['rol']        = 'cliente';

                // Redirigir a activar dispositivo (no tiene ninguno aún)
                header('Location: index.php');
                exit;
            } else {
                $error = 'Error al crear la cuenta. Inténtalo de nuevo.';
                $stmt->close();
                $db->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GrowSystem — Crear cuenta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --verde:       #31C048;
  --verde-dark:  #219E35;
  --verde-suave: #EAF7ED;
  --verde-mid:   #C5EAC9;
  --gris-bg:     #f0f4f2;
  --gris-borde:  #e2ebe4;
  --gris-texto:  #6b7c6e;
  --texto:       #1e2d22;
  --rojo:        #e05252;
  --font:        'DM Sans', sans-serif;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {

  font-family: var(--font);
  background: url('images/banner-item1.jpg') no-repeat center center fixed;
  background-size: cover;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}


.auth-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: 100%;
  max-width: 880px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 48px rgba(30,45,34,.12);
  overflow: hidden;
  min-height: 560px;
}

.auth-panel {
  background: linear-gradient(160deg, #219E35 0%, #0f6621 100%);
  padding: 50px 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}
.auth-panel::before,
.auth-panel::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,.06);
}
.auth-panel::before { width: 260px; height: 260px; top: -80px; right: -80px; }
.auth-panel::after  { width: 180px; height: 180px; bottom: -60px; left: -40px; }

.panel-logo {
  display: flex; align-items: center; gap: 12px; z-index: 1;
}
.panel-logo img { width: 42px; height: 42px; object-fit: contain; }
.panel-logo span { font-size: 24px; font-weight: 600; color: #fff; }

.panel-copy { z-index: 1; }
.panel-copy h2 { font-size: 26px; font-weight: 600; color: #fff; line-height: 1.3; margin-bottom: 10px; }
.panel-copy p  { font-size: 14px; color: rgba(255,255,255,.75); line-height: 1.6; }

.panel-pasos { z-index: 1; }
.paso {
  display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;
}
.paso-num {
  width: 24px; height: 24px;
  border-radius: 50%;
  background: rgba(255,255,255,.2);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
}
.paso div { font-size: 13px; color: rgba(255,255,255,.85); line-height: 1.4; }
.paso div strong { color: #fff; display: block; font-size: 13px; }

/* Formulario */
.auth-form-wrap {
  padding: 46px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.form-titulo { font-size: 24px; font-weight: 600; color: var(--texto); margin-bottom: 6px; }
.form-sub    { font-size: 14px; color: var(--gris-texto); margin-bottom: 28px; }

.campo { margin-bottom: 16px; }
.campo label {
  display: block; font-size: 13px; font-weight: 500;
  color: var(--gris-texto); margin-bottom: 5px;
}
.campo input {
  width: 100%; padding: 11px 14px;
  border: 1.5px solid var(--gris-borde);
  border-radius: 10px; font-size: 14px;
  font-family: var(--font); color: var(--texto);
  background: var(--gris-bg); outline: none;
  transition: border-color .2s, background .2s;
}
.campo input:focus { border-color: var(--verde); background: #fff; }
.campo input.invalido { border-color: var(--rojo); }

/* Indicador fortaleza contraseña */
.pwd-fuerza {
  margin-top: 6px; height: 3px;
  background: var(--gris-borde); border-radius: 2px; overflow: hidden;
}
.pwd-barra {
  height: 100%; border-radius: 2px;
  width: 0%; transition: width .3s, background .3s;
}

.error-msg {
  background: #fef2f2; border: 1px solid #fecaca;
  color: var(--rojo); font-size: 13px;
  padding: 10px 14px; border-radius: 8px; margin-bottom: 16px;
}

.btn-submit {
  width: 100%; padding: 13px;
  background: var(--verde); color: #fff; border: none;
  border-radius: 10px; font-size: 15px; font-weight: 600;
  font-family: var(--font); cursor: pointer;
  transition: background .2s, transform .1s; margin-top: 6px;
}
.btn-submit:hover  { background: var(--verde-dark); }
.btn-submit:active { transform: scale(.98); }

.form-footer {
  text-align: center; margin-top: 20px;
  font-size: 13px; color: var(--gris-texto);
}
.form-footer a { color: var(--verde-dark); font-weight: 500; text-decoration: none; }
.form-footer a:hover { text-decoration: underline; }

@media (max-width: 640px) {
  .auth-wrap { grid-template-columns: 1fr; }
  .auth-panel { display: none; }
  .auth-form-wrap { padding: 36px 28px; }
}
</style>
</head>
<body>

<div class="auth-wrap">

  <!-- Panel izquierdo -->
  <div class="auth-panel">
    <div class="panel-logo">
      <img src="icono.png" alt="GrowSystem">
      <span>GrowSystem</span>
    </div>

    <div class="panel-copy">
      <h2>Empieza a cultivar con inteligencia.</h2>
      <p>Crea tu cuenta y conecta tu primer invernadero en minutos.</p>
    </div>

    <div class="panel-pasos">
      <div class="paso">
        <div class="paso-num">1</div>
        <div><strong>Crea tu cuenta</strong>Solo necesitas correo y contraseña.</div>
      </div>
      <div class="paso">
        <div class="paso-num">2</div>
        <div><strong>Activa tu dispositivo</strong>Ingresa el ID único de tu ESP32.</div>
      </div>
      <div class="paso">
        <div class="paso-num">3</div>
        <div><strong>Identifica tu planta</strong>Toma una foto y el sistema la reconoce.</div>
      </div>
    </div>
  </div>

  <!-- Formulario -->
  <div class="auth-form-wrap">
    <h1 class="form-titulo">Crear cuenta</h1>
    <p class="form-sub">Todos los campos son obligatorios</p>

    <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="formRegistro" novalidate>

      <div class="campo">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre"
               placeholder="Ej: Juan Pérez"
               value="<?= htmlspecialchars($campos['nombre']) ?>"
               required autofocus>
      </div>

      <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email"
               placeholder="tu@correo.com"
               value="<?= htmlspecialchars($campos['email']) ?>"
               required>
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="Mínimo 6 caracteres"
               required oninput="evaluarPassword(this.value)">
        <div class="pwd-fuerza">
          <div class="pwd-barra" id="pwdBarra"></div>
        </div>
      </div>

      <div class="campo">
        <label for="password2">Confirmar contraseña</label>
        <input type="password" id="password2" name="password2"
               placeholder="Repite tu contraseña" required>
      </div>

      <button type="submit" class="btn-submit">Crear cuenta</button>
    </form>

    <p class="form-footer">
      ¿Ya tienes cuenta? <a href="inicio_indoor.php">Iniciar sesión</a>
    </p>
  </div>

</div>

<script>
function evaluarPassword(val) {
  const barra = document.getElementById('pwdBarra');
  let fuerza = 0;
  if (val.length >= 6)  fuerza += 25;
  if (val.length >= 10) fuerza += 25;
  if (/[A-Z]/.test(val) && /[0-9]/.test(val)) fuerza += 25;
  if (/[^A-Za-z0-9]/.test(val)) fuerza += 25;

  barra.style.width = fuerza + '%';
  barra.style.background =
    fuerza <= 25 ? '#e05252' :
    fuerza <= 50 ? '#f0a500' :
    fuerza <= 75 ? '#31C048cc' : '#31C048';
}

// Validar coincidencia de contraseñas en tiempo real
document.getElementById('password2').addEventListener('input', function() {
  const p1 = document.getElementById('password').value;
  this.classList.toggle('invalido', this.value !== p1 && this.value.length > 0);
});
</script>
</body>
</html>
