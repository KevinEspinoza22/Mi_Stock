<?php
// ==============================================================================
// MODELO DE PRODUCTOS (app/models/ProductoModel.php)
// Administra las consultas de inventario, productos y stock crítico en SQL Server.
// ==============================================================================

require_once __DIR__ . '/../../config/database.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Obtiene el listado completo de productos registrados.
     * @return array
     */
    public function obtenerTodos() {
        $sql = "SELECT id_producto, codigo_barra, nombre, precio, stock, stock_minimo 
                FROM Productos 
                ORDER BY nombre ASC";

        $stmt = sqlsrv_query($this->db, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $productos = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $productos[] = $row;
        }

        return $productos;
    }

    /**
     * Consulta los productos que se encuentran por debajo del limite minimo de stock.
     * @return array
     */
    public function obtenerStockCritico() {
        $sql = "SELECT id_producto, codigo_barra, nombre, stock, stock_minimo 
                FROM Productos 
                WHERE stock <= stock_minimo 
                ORDER BY stock ASC";

        $stmt = sqlsrv_query($this->db, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $alertas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $alertas[] = $row;
        }

        return $alertas;
    }
}
?>