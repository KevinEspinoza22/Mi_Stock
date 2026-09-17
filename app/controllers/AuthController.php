<?php
// ==============================================================================
// CONTROLADOR DE AUTENTICACIÓN (app/controllers/AuthController.php)
// Procesa las peticiones del formulario de Login y gestiona las sesiones PHP.
// ==============================================================================

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    /**
     * Procesa los datos enviados desde la vista de Login.
     */
    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario']);
            $password = trim($_POST['password']);

            $usuarioValido = $this->model->autenticar($usuario, $password);

            if ($usuarioValido) {
                // Iniciar sesión y guardar variables globales
                session_start();
                $_SESSION['user_id'] = $usuarioValido['id_usuario'];
                $_SESSION['nombre']  = $usuarioValido['nombre'];
                $_SESSION['rol']     = $usuarioValido['rol'];

                // Redireccionar según el rol
                header('Location: ../views/dashboard.php');
                exit();
            } else {
                // Redireccionar con mensaje de error
                header('Location: ../../public/index.php?error=1');
                exit();
            }
        }
    }
}
?>