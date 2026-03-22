<?php
session_start();

$conn = new mysqli("localhost", "root", "", "indoor");

if ($conn->connect_error) {
    die("Error de conexión");
}

$correo = $_POST['correo'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $usuario = $result->fetch_assoc();

    // ⚠️ si usas password_hash
    if (password_verify($password, $usuario['password'])) {

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];

        header("Location: index.php");
        exit;

    } else {
        header("Location: login.php?error=1");
    }

} else {
    header("Location: login.php?error=1");
}

$stmt->close();
$conn->close();
?>