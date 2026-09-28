<?php
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();
require_once __DIR__ . '/../models/VentaModel.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método no permitido.');
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $detalles = $data['detalles'] ?? [];
    $tipo = strtoupper(trim($data['tipo_documento'] ?? 'BOLETA'));
    $numero = trim($data['numero_documento'] ?? '');
    if (!$detalles) throw new RuntimeException('La venta está vacía.');
    $resultado = (new VentaModel())->registrarVenta((int)$_SESSION['user_id'], $detalles, $tipo, $numero ?: null);
    echo json_encode(['success'=>true, 'message'=>'Venta registrada correctamente.', 'id_venta'=>$resultado['id_venta'], 'stock_bajo'=>count($resultado['criticos'])]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
