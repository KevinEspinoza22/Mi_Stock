<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../public/index.php');
    exit();
}

require_once __DIR__ . '/../models/ProductoModel.php';
$productoModel = new ProductoModel();
$productos =$productoModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Punto de Venta - Mi Almacén POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>🛒 Punto de Venta (POS)</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver al Panel</a>
        </div>

        <div class="row">
            <!-- Selector de Productos -->
            <div class="col-md-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white fw-bold">Catálogo de Productos</div>
                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as$prod): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($prod['codigo_barra']); ?></code></td>
                                        <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $prod['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $prod['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="agregarAlCarrito(<?php echo $prod['id_producto']; ?>, '<?php echo addslashes($prod['nombre']); ?>', <?php echo $prod['precio']; ?>, <?php echo$prod['stock']; ?>)"
                                                <?php echo $prod['stock'] <= 0 ? 'disabled' : ''; ?>>
                                                + Agregar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Carrito de Compras -->
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">Detalle de la Venta</div>
                    <div class="card-body">
                        <table class="table table-sm" id="tablaCarrito">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 80px;">Cant.</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="bodyCarrito">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">El carrito está vacío</td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Total:</h4>
                            <h3 class="text-success fw-bold" id="totalVenta">$0</h3>
                        </div>
                        <button class="btn btn-success w-100 py-2 fw-bold" onclick="procesarVenta()">Completar Venta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para interacción con el Carrito -->
    <script>
        let carrito = [];

        function agregarAlCarrito(id, nombre, precio, stockMax) {
            let item = carrito.find(i => i.id_producto === id);
            if (item) {
                if (item.cantidad + 1 > stockMax) {
                    alert('No hay más stock disponible.');
                    return;
                }
                item.cantidad++;
            } else {
                carrito.push({ id_producto: id, nombre: nombre, precio_unitario: precio, cantidad: 1, stockMax: stockMax });
            }
            renderCarrito();
        }

        function eliminarDelCarrito(id) {
            carrito = carrito.filter(i => i.id_producto !== id);
            renderCarrito();
        }

        function renderCarrito() {
            const tbody = document.getElementById('bodyCarrito');
            let total = 0;

            if (carrito.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">El carrito está vacío</td></tr>';
                document.getElementById('totalVenta').innerText = '$0';
                return;
            }

            tbody.innerHTML = '';
            carrito.forEach(item => {
                let subtotal = item.cantidad * item.precio_unitario;
                total += subtotal;
                tbody.innerHTML += `
                    <tr>
                        <td>${item.nombre}</td>
                        <td>${item.cantidad}</td>
                        <td>$${item.precio_unitario}</td>
                        <td>$${subtotal}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${item.id_producto})">×</button></td>
                    </tr>
                `;
            });

            document.getElementById('totalVenta').innerText = `$${total.toLocaleString()}`;
        }

        function procesarVenta() {
            if (carrito.length === 0) {
                alert('Agrega al menos un producto.');
                return;
            }

            let total = carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);

            fetch('../controllers/VentaController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ detalles: carrito, total: total })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    carrito = [];
                    location.reload();
                }
            })
            .catch(err => console.error(err));
        }
    </script>
</body>
</html>