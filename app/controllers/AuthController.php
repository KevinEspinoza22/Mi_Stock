<?php
require_once __DIR__ . '/../models/UsuarioModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/index.php');
    exit;
}

$correo = trim($_POST['correo'] ?? '');
$password = (string)($_POST['password'] ?? '');
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: ../../public/index.php?error=1');
    exit;
}

$usuario = (new UsuarioModel())->autenticar($correo, $password);
if (!$usuario) {
    header('Location: ../../public/index.php?error=1');
    exit;
}

session_start();
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$usuario['IdUsuario'];
$_SESSION['nombre'] = $usuario['Nombre'].' '.$usuario['Apellido'];
$_SESSION['rol'] = $usuario['Rol'];
$_SESSION['correo'] = $usuario['Correo'];

header('Location: ../views/dashboard.php');
exit;
