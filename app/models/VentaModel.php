<?php
// ==============================================================================
// MODELO DE VENTAS (app/models/VentaModel.php)
// Gestiona el registro de ventas y la deducción automática de stock en SQL Server.
// ==============================================================================

require_once __DIR__ . '/../../config/database.php';

class VentaModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Registra una nueva venta con sus detalles y descuenta el stock dentro de una transacción SQL.
     * @param int $idUsuario ID del usuario que procesa la venta
     * @param float $total Monto total de la venta
     * @param array $detalles Lista de productos en la venta [['id_producto', 'cantidad', 'precio_unitario']]
     * @return bool
     */
    public function registrarVenta($idUsuario, $total, $detalles) {
        // Iniciar transacción en SQL Server para garantizar integridad de datos
        if (sqlsrv_begin_transaction($this->db) === false) {
            return false;
        }

        try {
            // 1. Insertar la cabecera de la venta
            $sqlVenta = "INSERT INTO Ventas (id_usuario, fecha, total) VALUES (?, GETDATE(), ?);
                         SELECT SCOPE_IDENTITY() AS id_venta;";
            $paramsVenta = array($idUsuario, $total);
            
            $stmtVenta = sqlsrv_query($this->db, $sqlVenta, $paramsVenta);
            if ($stmtVenta === false) {
                throw new Exception("Error al registrar cabecera de venta.");
            }

            sqlsrv_next_result($stmtVenta);
            $rowVenta = sqlsrv_fetch_array($stmtVenta, SQLSRV_FETCH_ASSOC);
            $idVenta = $rowVenta['id_venta'];

            // 2. Insertar cada detalle y actualizar el stock
            foreach ($detalles as $item) {
                // Insertar detalle
                $sqlDetalle = "INSERT INTO DetalleVentas (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
                               VALUES (?, ?, ?, ?, ?)";
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $paramsDetalle = array($idVenta, $item['id_producto'], $item['cantidad'], $item['precio_unitario'], $subtotal);
                
                $stmtDetalle = sqlsrv_query($this->db, $sqlDetalle, $paramsDetalle);
                if ($stmtDetalle === false) {
                    throw new Exception("Error al registrar detalle de venta.");
                }

                // Descontar stock del producto
                $sqlStock = "UPDATE Productos SET stock = stock - ? WHERE id_producto = ?";
                $paramsStock = array($item['cantidad'], $item['id_producto']);
                
                $stmtStock = sqlsrv_query($this->db, $sqlStock, $paramsStock);
                if ($stmtStock === false) {
                    throw new Exception("Error al actualizar stock.");
                }
            }

            // Si todo salió bien, confirmar la transacción
            sqlsrv_commit($this->db);
            return true;

        } catch (Exception $e) {
            // Ante cualquier fallo, revertir los cambios
            sqlsrv_rollback($this->db);
            return false;
        }
    }
}
?>