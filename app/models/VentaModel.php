<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/services/EmailService.php';
require_once __DIR__ . '/ProductoModel.php';

class VentaModel {
    private PDO $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    public function registrarVenta(int $idUsuario, array $detalles, string $tipoDocumento='BOLETA', ?string $numeroDocumento=null): array {
        if (!$detalles) throw new RuntimeException('La venta no contiene productos.');
        $productoModel = new ProductoModel();
        $idsAfectados = [];
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO Ventas (IdUsuario, FechaVenta, TipoDocumento, NumeroDocumento, Total) VALUES (?, NOW(), ?, ?, 0)");
            $stmt->execute([$idUsuario, $tipoDocumento, $numeroDocumento]);
            $idVenta = (int)$this->db->lastInsertId();

            $mov = $this->db->prepare("INSERT INTO Movimientos (TipoMovimiento, IdUsuario, Observacion) VALUES ('SALIDA', ?, ?)");
            $mov->execute([$idUsuario, 'Venta #'.$idVenta.($numeroDocumento ? ' - Documento '.$numeroDocumento : '')]);
            $idMovimiento = (int)$this->db->lastInsertId();

            $det = $this->db->prepare("INSERT INTO DetalleVentas (IdVenta, IdProducto, Cantidad, PrecioUnitario, Subtotal) VALUES (?,?,?,?,?)");
            $detMov = $this->db->prepare("INSERT INTO DetalleMovimiento (IdMovimiento, IdProducto, Cantidad) VALUES (?,?,?)");
            $stockCheck = $this->db->prepare("SELECT StockActual, PrecioVenta FROM Productos WHERE IdProducto=? AND Estado=1 FOR UPDATE");
            $totalCalculado = 0.0;

            foreach ($detalles as $item) {
                $idProducto = (int)$item['id_producto'];
                $cantidad = (int)$item['cantidad'];
                if ($cantidad <= 0) throw new RuntimeException('Cantidad inválida.');
                $stockCheck->execute([$idProducto]);
                $producto = $stockCheck->fetch();
                if (!$producto) throw new RuntimeException('Producto no encontrado.');
                if ((int)$producto['StockActual'] < $cantidad) throw new RuntimeException('Stock insuficiente para el producto #'.$idProducto.'.');
                $precio = (float)$producto['PrecioVenta'];
                $subtotal = $cantidad * $precio;
                $totalCalculado += $subtotal;
                $det->execute([$idVenta, $idProducto, $cantidad, $precio, $subtotal]);
                $detMov->execute([$idMovimiento, $idProducto, $cantidad]);
                $idsAfectados[] = $idProducto;
            }

            $updateTotal = $this->db->prepare("UPDATE Ventas SET Total=? WHERE IdVenta=?");
            $updateTotal->execute([$totalCalculado, $idVenta]);
            $this->db->commit();
            $criticos = $productoModel->obtenerCriticosPorIds(array_unique($idsAfectados));
            if ($criticos) (new EmailService())->enviarAlertaStock($criticos);
            return ['id_venta'=>$idVenta, 'criticos'=>$criticos];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function resumenHoy(): array {
        $row = $this->db->query("SELECT COUNT(*) AS cantidad, COALESCE(SUM(Total),0) AS total FROM Ventas WHERE DATE(FechaVenta)=CURDATE()")->fetch();
        return $row ?: ['cantidad'=>0,'total'=>0];
    }
}
