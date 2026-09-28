<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/services/EmailService.php';
require_once __DIR__ . '/ProductoModel.php';

class CompraModel {
    private PDO $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    public function registrarCompra(int $idUsuario, array $data, array $detalles): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO Compras (IdProveedor, IdUsuario, FechaCompra, TipoDocumento, NumeroDocumento, ArchivoDocumento, Total) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([
                (int)$data['proveedor'], $idUsuario, date('Y-m-d H:i:s'),
                $data['tipo_documento'], $data['numero_documento'] ?: null,
                $data['archivo_documento'] ?: null, (float)$data['total']
            ]);
            $idCompra = (int)$this->db->lastInsertId();

            $mov = $this->db->prepare("INSERT INTO Movimientos (TipoMovimiento, IdUsuario, Observacion) VALUES ('ENTRADA', ?, ?)");
            $mov->execute([$idUsuario, 'Compra #'.$idCompra.($data['numero_documento'] ? ' - Documento '.$data['numero_documento'] : '')]);
            $idMovimiento = (int)$this->db->lastInsertId();

            $det = $this->db->prepare("INSERT INTO DetalleCompras (IdCompra, IdProducto, Cantidad, PrecioUnitario, Subtotal) VALUES (?,?,?,?,?)");
            $detMov = $this->db->prepare("INSERT INTO DetalleMovimiento (IdMovimiento, IdProducto, Cantidad) VALUES (?,?,?)");
            foreach ($detalles as $item) {
                $idProducto = (int)$item['id_producto'];
                $cantidad = (int)$item['cantidad'];
                $precio = (float)$item['precio_unitario'];
                if ($cantidad <= 0) throw new RuntimeException('Cantidad inválida.');
                $det->execute([$idCompra, $idProducto, $cantidad, $precio, $cantidad*$precio]);
                $detMov->execute([$idMovimiento, $idProducto, $cantidad]);
            }
            $this->db->commit();
            return $idCompra;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function recientes(int $limite=20): array {
        $limite = max(1, min($limite, 100));
        $sql = "SELECT c.IdCompra, c.FechaCompra, c.TipoDocumento, c.NumeroDocumento, c.Total, p.Nombre AS Proveedor,
                       CONCAT(u.Nombre,' ',u.Apellido) AS Usuario, c.ArchivoDocumento
                FROM Compras c
                INNER JOIN Proveedores p ON p.IdProveedor=c.IdProveedor
                INNER JOIN Usuarios u ON u.IdUsuario=c.IdUsuario
                ORDER BY c.FechaCompra DESC LIMIT $limite";
        return $this->db->query($sql)->fetchAll();
    }
}
