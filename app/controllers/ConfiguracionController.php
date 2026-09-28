<?php
require_once __DIR__ . '/../helpers/auth.php';
requireAdmin();
require_once __DIR__ . '/../models/ConfiguracionModel.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../views/configuracion.php'); exit; }
$correo = trim($_POST['correo'] ?? '');
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../views/configuracion.php?error='.urlencode('Ingresa un correo válido.')); exit;
}
(new ConfiguracionModel())->guardar($correo, isset($_POST['alerta_stock']), isset($_POST['reporte_diario']), isset($_POST['reporte_mensual']), isset($_POST['activo']));
header('Location: ../views/configuracion.php?ok=1');
