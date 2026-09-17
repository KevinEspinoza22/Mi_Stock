<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/index.php');
    exit();
}

require_once __DIR__ . '/../models/ProductoModel.php';
$model = new ProductoModel();
$productos = $model->obtenerTodos();
$alertasStock = $model->obtenerStockCritico();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario y Productos - Mi Almacén POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Inventario</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver al Panel</a>
        </div>

        <!-- Alertas de Stock Crítico -->
        <?php if (!empty($alertasStock)): ?>
            <div class="alert alert-warning shadow-sm" role="alert">
                <h5 class="alert-heading fw-bold">⚠️ Alertas de Stock Crítico</h5>
                <ul class="mb-0">
                    <?php foreach ($alertasStock as $alerta): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($alerta['nombre']); ?></strong> 
                            - Stock actual: <span class="badge bg-danger"><?php echo $alerta['stock']; ?> unidades</span> 
                            (Mínimo requerido: <?php echo $alerta['stock_minimo']; ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tabla de Productos -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($prod['codigo_barra']); ?></code></td>
                                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                <td><?php echo $prod['stock']; ?></td>
                                <td>
                                    <?php if ($prod['stock'] <= $prod['stock_minimo']): ?>
                                        <span class="badge bg-danger">Reabastecer</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Optimo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>