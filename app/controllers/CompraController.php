<?php
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();
require_once __DIR__ . '/../models/CompraModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/compras.php'); exit;
}

try {
    $detalles = json_decode($_POST['detalles_json'] ?? '[]', true) ?: [];
    if (!$detalles) throw new RuntimeException('Agrega al menos un producto.');

    $archivo = null;
    if (!empty($_FILES['documento']['name'])) {
        if ($_FILES['documento']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No se pudo subir el documento.');
        if ($_FILES['documento']['size'] > 5 * 1024 * 1024) throw new RuntimeException('El documento supera los 5 MB.');
        $permitidas = ['pdf','png','jpg','jpeg'];
        $ext = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidas, true)) throw new RuntimeException('Solo se permiten PDF, PNG o JPG.');
        $nombre = uniqid('doc_', true).'.'.$ext;
        $destino = __DIR__ . '/../../public/uploads/documentos/'.$nombre;
        if (!move_uploaded_file($_FILES['documento']['tmp_name'], $destino)) throw new RuntimeException('No se pudo guardar el documento.');
        $archivo = $nombre;
    }

    $idCompra = (new CompraModel())->registrarCompra((int)$_SESSION['user_id'], [
        'proveedor' => $_POST['proveedor'] ?? 0,
        'tipo_documento' => strtoupper(trim($_POST['tipo_documento'] ?? 'FACTURA')),
        'numero_documento' => trim($_POST['numero_documento'] ?? ''),
        'archivo_documento' => $archivo,
        'total' => (float)($_POST['total'] ?? 0),
    ], $detalles);

    header('Location: ../views/compras.php?ok='.urlencode('Compra #'.$idCompra.' registrada correctamente.'));
} catch (Throwable $e) {
    header('Location: ../views/compras.php?error='.urlencode($e->getMessage()));
}
