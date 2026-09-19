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
<body class="bg-light p-4">
    <div class="container">
        <!-- Encabezado de Bienvenida -->
        <div class="alert alert-success d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h4 class="mb-0">¡Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h4>
                <small class="text-muted">Sistema de Control e Inventario</small>
            </div>
            <span class="badge bg-primary fs-6">Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></span>
        </div>

        <!-- Opciones del Sistema -->
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">📦 Control de Inventario</h5>
                        <p class="card-text text-muted">Consulta productos, revisa niveles de existencia y alertas de stock crítico.</p>
                        <a href="productos.php" class="btn btn-primary">Ir a Productos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">🛒 Punto de Venta (POS)</h5>
                        <p class="card-text text-muted">Módulo para registrar ventas rápidas y generación de comprobantes.</p>
                        <button class="btn btn-secondary" disabled>Próximamente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>