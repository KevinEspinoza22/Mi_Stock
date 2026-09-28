<?php
require_once __DIR__ . '/../../config/database.php';

class UsuarioModel {
    private PDO $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function autenticar(string $correo, string $password): array|false {
        $sql = "SELECT u.IdUsuario, u.Nombre, u.Apellido, u.Correo, u.Password, r.Nombre AS Rol
                FROM Usuarios u
                INNER JOIN Roles r ON r.IdRol = u.IdRol
                WHERE u.Correo = :correo AND u.Estado = 1 AND r.Estado = 1
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $correo]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $stored = (string)$row['Password'];
        $valid = password_verify($password, $stored) || hash_equals($stored, $password);
        if (!$valid) return false;

        unset($row['Password']);
        return $row;
    }
}
