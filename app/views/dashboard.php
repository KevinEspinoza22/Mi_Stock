<?php
session_start();

// Proteger la vista: si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - Mi Almacén POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <h4>¡Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h4>
            <span class="badge bg-primary fs-6">Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></span>
        </div>
        <p class="lead">El módulo de autenticación se ha configurado correctamente.</p>
    </div>
</body>
</html>