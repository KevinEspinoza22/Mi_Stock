<?php
// ==============================================================================
// ARCHIVO DE CONEXIÓN A LA BASE DE DATOS (SQL SERVER)
// Este archivo define una clase orientada a objetos para conectar PHP con SQL Server.
// ==============================================================================

class Database {
    // --------------------------------------------------------------------------
    // ATRIBUTOS PRIVADOS (Configuración del Servidor)
    // --------------------------------------------------------------------------
    
    // Dirección del servidor local de SQL Server (ej: LOCALHOST o LOCALHOST\SQLEXPRESS)
    private $serverName = "LOCALHOST"; 

    // Opciones de configuración requeridas por el controlador nativo 'sqlsrv'
    private $connectionOptions = array(
        "Database" => "MiAlmacenDB",  // Nombre exacto de la BD en SQL Server
        "Uid" => "sa",                // Usuario administrador de SQL Server
        "PWD" => "123456",            // Contraseña de acceso a SQL Server
        "CharacterSet" => "UTF-8"     // Codificación para aceptar tildes y caracteres especiales
    );

    // Variable encargada de guardar la conexión activa (Patrón Singleton básico)
    private $conn = null;

    // --------------------------------------------------------------------------
    // MÉTODOS PÚBLICOS
    // --------------------------------------------------------------------------
    
    /**
     * Establece o retorna la conexión activa con la base de datos SQL Server.
     * @return resource|null Retorna el recurso de conexión activo.
     */
    public function getConnection() {
        // Verifica si la conexión aún no ha sido creada para evitar duplicados
        if ($this->conn === null) {
            
            // Función nativa de PHP para conectar a SQL Server usando el controlador sqlsrv
            $this->conn = sqlsrv_connect($this->serverName, $this->connectionOptions);

            // Si la conexión falla, interrumpe el script y muestra el error detallado
            if ($this->conn === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
        
        // Retorna la conexión para ser reutilizada en los Controllers y Models
        return $this->conn;
    }
}
?>