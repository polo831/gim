<?php
// Carga de core y modelos necesarios
require_once __DIR__ . '/../core/DataBase.php';
require_once __DIR__ . '/../models/AdminModel.php';

// Asegurar carga de variables de entorno desde .env si no están presentes
if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/../../.env')) {
    $envLines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}

class AdminAuthController {

    public static function iniciarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function estaAutenticadoAdmin() {
        self::iniciarSesion();
        return (
            isset($_SESSION['admin_logueado']) && 
            $_SESSION['admin_logueado'] === true && 
            isset($_SESSION['admin_rol']) && 
            $_SESSION['admin_rol'] === 'admin'
        );
    }

    // Proteger páginas administrativas: si no está logueado o no es admin, redirige al login
    public static function verificarSesionAdmin() {
        if (!self::estaAutenticadoAdmin()) {
            header("Location: login.php");
            exit();
        }
    }

    // Procesar intento de login
    public static function login($usuario, $password) {
        self::iniciarSesion();

        $usuario = trim($usuario);
        $password = trim($password);

        if (empty($usuario) || empty($password)) {
            return [
                'status' => false,
                'error' => 'Por favor, complete todos los campos.'
            ];
        }

        // Si la tabla de administradores está vacía, creamos un admin por defecto
        AdminModel::crearAdminPorDefectoSiVacio();

        // 1. Buscar en la tabla usuarios_admin
        $admin = AdminModel::buscarPorUsuario($usuario);

        if (!$admin) {
            // Verificar si el correo pertenece a un socio
            $socio = AdminModel::buscarSocioPorEmail($usuario);
            if ($socio) {
                return [
                    'status' => false,
                    'error' => 'Acceso denegado: Esta cuenta pertenece a un socio, no a un administrador del sistema.'
                ];
            }

            return [
                'status' => false,
                'error' => 'El usuario o correo ingresado no se encuentra registrado.'
            ];
        }

        // 2. Verificar que tenga rol de admin
        if (strtolower($admin->rol) !== 'admin') {
            return [
                'status' => false,
                'error' => 'Acceso no permitido: Tu cuenta no tiene permisos de administrador (Rol: ' . htmlspecialchars($admin->rol) . ').'
            ];
        }

        // 3. Verificar contraseña (soporta password_hash y texto plano por si se cargó manual)
        $passwordValida = false;
        if (password_verify($password, $admin->password_hash)) {
            $passwordValida = true;
        } elseif ($password === $admin->password_hash) {
            // Si la contraseña estaba en texto plano en la BD, la actualizamos automáticamente a hash seguro
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            AdminModel::actualizarPasswordHash($admin->id_admin, $nuevoHash);
            $passwordValida = true;
        }

        if (!$passwordValida) {
            return [
                'status' => false,
                'error' => 'La contraseña ingresada es incorrecta.'
            ];
        }

        // 4. Iniciar sesión administrativa con éxito
        $_SESSION['admin_logueado'] = true;
        $_SESSION['admin_id']       = $admin->id_admin;
        $_SESSION['admin_nombre']   = $admin->nombre;
        $_SESSION['admin_usuario']  = $admin->usuario;
        $_SESSION['admin_rol']      = $admin->rol;

        return ['status' => true];
    }

    // Cerrar sesión
    public static function logout() {
        self::iniciarSesion();
        unset(
            $_SESSION['admin_logueado'],
            $_SESSION['admin_id'],
            $_SESSION['admin_nombre'],
            $_SESSION['admin_usuario'],
            $_SESSION['admin_rol']
        );
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
