<?php
require_once __DIR__ . '/../../config/database.php';
class ConfiguracionModel {
    private PDO $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    public function obtener(): array {
        $row = $this->db->query("SELECT * FROM ConfiguracionCorreo ORDER BY IdConfiguracion LIMIT 1")->fetch();
        return $row ?: ['CorreoDestino'=>'', 'AlertaStock'=>1, 'ReporteDiario'=>1, 'ReporteMensual'=>1, 'Activo'=>1];
    }

    public function guardar(string $correo, bool $alerta, bool $diario, bool $mensual, bool $activo): void {
        $count = (int)$this->db->query("SELECT COUNT(*) FROM ConfiguracionCorreo")->fetchColumn();
        if ($count === 0) {
            $stmt = $this->db->prepare("INSERT INTO ConfiguracionCorreo (CorreoDestino, AlertaStock, ReporteDiario, ReporteMensual, Activo) VALUES (?,?,?,?,?)");
            $stmt->execute([$correo, $alerta?1:0, $diario?1:0, $mensual?1:0, $activo?1:0]);
        } else {
            $id = (int)$this->db->query("SELECT IdConfiguracion FROM ConfiguracionCorreo ORDER BY IdConfiguracion LIMIT 1")->fetchColumn();
            $stmt = $this->db->prepare("UPDATE ConfiguracionCorreo SET CorreoDestino=?, AlertaStock=?, ReporteDiario=?, ReporteMensual=?, Activo=? WHERE IdConfiguracion=?");
            $stmt->execute([$correo, $alerta?1:0, $diario?1:0, $mensual?1:0, $activo?1:0, $id]);
        }
    }
}
