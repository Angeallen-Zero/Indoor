<?php
//  GrowSystem — Login
//  Archivo: login.php
session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/api/db.php';

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Completa todos los campos.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();

        if (!$row) {
            $error = 'Correo o contraseña incorrectos.';
        } else {
            // Soporta bcrypt (password_verify) y SHA-256 legacy
            $valido = password_verify($password, $row['password'])
                   || hash('sha256', $password) === $row['password'];

            if (!$valido) {
                $error = 'Correo o contraseña incorrectos.';
            } else {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['nombre']     = $row['nombre'];
                $_SESSION['rol']        = $row['rol'];

                // Admin → panel admin, cliente → dashboard
                header('Location: ' . ($row['rol'] === 'admin' ? 'admin.php' : 'dashboard.php'));
                exit;
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
<title>GrowSystem — Iniciar sesión</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
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
  --mono:        'DM Mono', monospace;
}


* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: var(--font);
  background: var(--gris-bg);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Panel decorativo lateral */
.auth-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: 100%;
  max-width: 880px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 48px rgba(30,45,34,.12);
  overflow: hidden;
  min-height: 520px;
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

/* Círculos decorativos */
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
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 1;
}
.panel-logo img { width: 42px; height: 42px; object-fit: contain; }
.panel-logo span {
  font-size: 24px;
  font-weight: 600;
  color: #fff;
  letter-spacing: -.3px;
}

.panel-copy { z-index: 1; }
.panel-copy h2 {
  font-size: 28px;
  font-weight: 600;
  color: #fff;
  line-height: 1.3;
  margin-bottom: 12px;
}
.panel-copy p {
  font-size: 14px;
  color: rgba(255,255,255,.75);
  line-height: 1.6;
}

.panel-features { z-index: 1; }
.feature {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}
.feature-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,.5);
  flex-shrink: 0;
}
.feature span {
  font-size: 13px;
  color: rgba(255,255,255,.8);
}

/* Formulario */
.auth-form-wrap {
  padding: 50px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-titulo {
  font-size: 24px;
  font-weight: 600;
  color: var(--texto);
  margin-bottom: 6px;
}
.form-sub {
  font-size: 14px;
  color: var(--gris-texto);
  margin-bottom: 32px;
}

.campo {
  margin-bottom: 18px;
}
.campo label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: var(--gris-texto);
  margin-bottom: 6px;
}
.campo input {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid var(--gris-borde);
  border-radius: 10px;
  font-size: 14px;
  font-family: var(--font);
  color: var(--texto);
  background: var(--gris-bg);
  outline: none;
  transition: border-color .2s, background .2s;
}
.campo input:focus {
  border-color: var(--verde);
  background: #fff;
}

.error-msg {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: var(--rojo);
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 8px;
  margin-bottom: 18px;
}

.btn-submit {
  width: 100%;
  padding: 13px;
  background: var(--verde);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: background .2s, transform .1s;
  margin-top: 4px;
}
.btn-submit:hover  { background: var(--verde-dark); }
.btn-submit:active { transform: scale(.98); }

.form-footer {
  text-align: center;
  margin-top: 22px;
  font-size: 13px;
  color: var(--gris-texto);
}
.form-footer a {
  color: var(--verde-dark);
  font-weight: 500;
  text-decoration: none;
}
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

  <!-- Panel izquierdo decorativo -->
  <div class="auth-panel">
    <div class="panel-logo">
      <img src="icono.png" alt="GrowSystem">
      <span>GrowSystem</span>
    </div>

    <div class="panel-copy">
      <h2>Tu invernadero,<br>bajo control.</h2>
      <p>Monitorea y controla tus plantas desde cualquier lugar de tu red local.</p>
    </div>

    <div class="panel-features">
      <div class="feature"><div class="feature-dot"></div><span>Sensores en tiempo real</span></div>
      <div class="feature"><div class="feature-dot"></div><span>Control de riego y nutrientes</span></div>
      <div class="feature"><div class="feature-dot"></div><span>Historial y bitácora de cultivo</span></div>
      <div class="feature"><div class="feature-dot"></div><span>Múltiples invernaderos</span></div>
    </div>
  </div>

  <!-- Formulario -->
  <div class="auth-form-wrap">
    <h1 class="form-titulo">Iniciar sesión</h1>
    <p class="form-sub">Ingresa con tu cuenta GrowSystem</p>

    <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">
      <div class="campo">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email"
               placeholder="tu@correo.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autofocus>
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn-submit">Entrar</button>
    </form>

    <p class="form-footer">
      ¿No tienes cuenta?
      <a href="registro.php">Crear cuenta</a>
    </p>
  </div>

</div>

</body>
</html>
