<?php
require_once __DIR__ . '/../core/DataBase.php';

class AdminModel {

    // Buscar usuario administrador por su columna 'usuario' (puede ser email o username)
    public static function buscarPorUsuario($usuario) {
        $sql = "SELECT * FROM usuarios_admin WHERE usuario = :usuario LIMIT 1";
        return DataBase::getRecord($sql, [':usuario' => $usuario]);
    }

    // Buscar si el email ingresado pertenece a la tabla socios
    public static function buscarSocioPorEmail($email) {
        $sql = "SELECT * FROM socios WHERE email = :email AND eliminado = 0 LIMIT 1";
        return DataBase::getRecord($sql, [':email' => $email]);
    }

    // Actualizar hash de contraseña si estaba en texto plano
    public static function actualizarPasswordHash($id_admin, $nuevoHash) {
        $sql = "UPDATE usuarios_admin SET password_hash = :hash WHERE id_admin = :id";
        return DataBase::execute($sql, [':hash' => $nuevoHash, ':id' => $id_admin]);
    }

    // Crear un usuario administrador por defecto si la tabla está vacía
    public static function crearAdminPorDefectoSiVacio() {
        $sqlCount = "SELECT COUNT(*) as total FROM usuarios_admin";
        $res = DataBase::getRecord($sqlCount);
        if ($res && isset($res->total) && intval($res->total) === 0) {
            $sqlInsert = "INSERT INTO usuarios_admin (nombre, usuario, password_hash, rol) 
                          VALUES ('Administrador', 'admin@gym.com', :pass, 'admin')";
            DataBase::execute($sqlInsert, [
                ':pass' => password_hash('admin123', PASSWORD_DEFAULT)
            ]);
            return true;
        }
        return false;
    }
}
