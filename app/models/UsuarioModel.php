<?php
// ==============================================================================
// MODELO DE USUARIO (app/models/UsuarioModel.php)
// Administra las consultas SQL hacia la tabla de usuarios existente.
// ==============================================================================

require_once __DIR__ . '/../../config/database.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Valida las credenciales ingresadas en el Login.
     * @param string $usuario Nombre de usuario o correo
     * @param string $password Contraseña ingresada
     * @return array|false Retorna los datos del usuario o false si falla.
     */
    public function autenticar($usuario, $password) {
        // Consulta SQL parametrizada para evitar inyecciones SQL
        $sql = "SELECT id_usuario, nombre, usuario, password, rol 
                FROM Usuarios 
                WHERE usuario = ? AND estado = 1";

        $params = array($usuario);
        $stmt = sqlsrv_query($this->db, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Si se encuentra el registro, verificamos la contraseña
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Nota: Se valida texto plano si la BD base viene así, o password_verify si viene encriptada
            if ($password === $row['password']) {
                return $row;
            }
        }

        return false;
    }
}
?>