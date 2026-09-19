<?php
// ==============================================================================
// CONTROLADOR DE VENTAS (app/controllers/VentaController.php)
// Procesa las solicitudes AJAX enviadas desde la vista POS.
// ==============================================================================

session_start();
require_once __DIR__ . '/../models/VentaModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['detalles']) || empty($data['total'])) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit();
    }

    $idUsuario = $_SESSION['user_id'];
    $total = $data['total'];
    $detalles = $data['detalles'];

    $ventaModel = new VentaModel();
    $resultado = $ventaModel->registrarVenta($idUsuario, $total, $detalles);

    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Venta registrada con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al procesar la venta en la base de datos.']);
    }
}
?>