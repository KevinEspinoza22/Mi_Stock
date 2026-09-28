<?php
require_once __DIR__ . '/../../config/database.php';

class ProductoModel {
    private PDO $db;
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos(): array {
        $sql = "SELECT p.IdProducto, p.Codigo, p.Nombre, p.Descripcion, p.StockActual,
                       p.StockMinimo, p.PrecioVenta, p.PrecioCompra,
                       c.Nombre AS Categoria, pr.Nombre AS Proveedor
                FROM Productos p
                INNER JOIN Categorias c ON c.IdCategoria = p.IdCategoria
                LEFT JOIN Proveedores pr ON pr.IdProveedor = p.IdProveedor
                WHERE p.Estado = 1
                ORDER BY p.Nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM Productos WHERE IdProducto = ? AND Estado = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function obtenerStockCritico(): array {
        $stmt = $this->db->query("SELECT p.IdProducto, p.Codigo, p.Nombre, p.StockActual, p.StockMinimo
                                  FROM Productos p
                                  WHERE p.Estado = 1 AND p.StockActual <= p.StockMinimo
                                  ORDER BY p.StockActual ASC, p.Nombre ASC");
        return $stmt->fetchAll();
    }

    public function obtenerCategorias(): array {
        return $this->db->query("SELECT IdCategoria, Nombre FROM Categorias WHERE Estado = 1 ORDER BY Nombre")->fetchAll();
    }

    public function obtenerProveedores(): array {
        return $this->db->query("SELECT IdProveedor, Nombre FROM Proveedores WHERE Estado = 1 ORDER BY Nombre")->fetchAll();
    }

    public function crear(array $data): bool {
        $sql = "INSERT INTO Productos
                (Codigo, Nombre, Descripcion, IdCategoria, IdProveedor, StockActual, StockMinimo, PrecioCompra, PrecioVenta, Ubicacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            trim($data['codigo']), trim($data['nombre']), trim($data['descripcion'] ?? ''),
            (int)$data['categoria'], !empty($data['proveedor']) ? (int)$data['proveedor'] : null,
            (int)($data['stock'] ?? 0), (int)($data['stock_minimo'] ?? 0),
            (float)($data['precio_compra'] ?? 0), (float)($data['precio_venta'] ?? 0),
            trim($data['ubicacion'] ?? '')
        ]);
    }

    public function obtenerCriticosPorIds(array $ids): array {
        if (!$ids) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT IdProducto, Codigo, Nombre, StockActual, StockMinimo
                FROM Productos
                WHERE Estado = 1 AND IdProducto IN ($placeholders) AND StockActual <= StockMinimo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_map('intval', $ids));
        return $stmt->fetchAll();
    }
}
