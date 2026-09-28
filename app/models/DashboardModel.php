<?php
require_once __DIR__ . '/../../config/database.php';
class DashboardModel {
    private PDO $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }
    public function resumen(): array {
        $stats = [];
        $stats['productos'] = (int)$this->db->query("SELECT COUNT(*) FROM Productos WHERE Estado = 1")->fetchColumn();
        $stats['stock_bajo'] = (int)$this->db->query("SELECT COUNT(*) FROM Productos WHERE Estado = 1 AND StockActual <= StockMinimo")->fetchColumn();
        $stats['ventas_hoy'] = (int)$this->db->query("SELECT COUNT(*) FROM Ventas WHERE DATE(FechaVenta) = CURDATE()")->fetchColumn();
        $stats['monto_hoy'] = (float)$this->db->query("SELECT COALESCE(SUM(Total),0) FROM Ventas WHERE DATE(FechaVenta) = CURDATE()")->fetchColumn();
        $stats['monto_mes'] = (float)$this->db->query("SELECT COALESCE(SUM(Total),0) FROM Ventas WHERE YEAR(FechaVenta)=YEAR(CURDATE()) AND MONTH(FechaVenta)=MONTH(CURDATE())")->fetchColumn();
        $stats['valor_inventario'] = (float)$this->db->query("SELECT COALESCE(SUM(StockActual * PrecioVenta),0) FROM Productos WHERE Estado=1")->fetchColumn();
        return $stats;
    }

    public function ventasUltimosDias(int $dias = 7): array {
        $dias = max(1, min($dias, 31));
        $stmt = $this->db->query("SELECT DATE(FechaVenta) AS Dia, COALESCE(SUM(Total),0) AS Total
                                  FROM Ventas
                                  WHERE FechaVenta >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)
                                  GROUP BY DATE(FechaVenta)
                                  ORDER BY Dia ASC");
        return $stmt->fetchAll();
    }
}
