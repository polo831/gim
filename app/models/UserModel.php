<?php

namespace app\models;

use PDO;
use PDOException;

class UserModel {

         public static function registrar($nombre, $email, $password, $direccion, $telefono) {
        // El modelo es el único que hashea la contraseña en texto plano
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = \DataBase::connection();
            
            // Verificar si el correo ya existe
            $check = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
            $check->execute(['email' => $email]);
            if ($check->rowCount() > 0) {
                return false; // El correo ya existe
            }

            // Insertar usuario con el hash limpio
            $sql = "INSERT INTO usuarios (nombre, email, password, direccion, telefono, rol) VALUES (:nombre, :email, :password, :direccion, :telefono, 'cliente')";
            $stmt = $db->prepare($sql);

            return $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $passwordHash,
                'direccion' => $direccion,
                'telefono' => $telefono
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    public static function login($email, $password) {
        try {
            $db = \DataBase::connection();
            
            // 1. Buscamos primero al usuario por su correo
            $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Si el usuario no existe, salimos
            if (!$usuario) {
                return false;
            }

            // 2. TRUCO SEGURO PARA EL ADMIN: Si entra con la contraseña '1234' 
            // y es el correo del admin, nos aseguramos de que tenga rol 'admin'
            if ($password === '1234' && $email === 'admin@gym.com') {
                // Si la contraseña en la BD no es un hash válido de 1234, la actualizamos
                if (!password_verify('1234', $usuario['password'])) {
                    $nuevoHash = password_hash('1234', PASSWORD_DEFAULT);
                    $update = $db->prepare("UPDATE usuarios SET password = :pass, rol = 'admin' WHERE email = :email");
                    $update->execute(['pass' => $nuevoHash, 'email' => $email]);
                    
                    // Volvemos a consultar el usuario ya actualizado
                    $stmt->execute(['email' => $email]);
                    $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
                }
            }

            // 3. Verificación estándar con password_verify
            if ($usuario && password_verify($password, $usuario['password'])) {
                return $usuario;
            }

            return false;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
  

  

    public static function getAllUsers() {
        try {
            $db = \DataBase::connection();
            if (!($db instanceof \PDO)) {
                return [];
            }
            $sql = "SELECT * FROM usuarios";
            $stmt = $db->query($sql);
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\PDOException $e) {
            return [];
        }
    }

    public static function actualizarPasswordPorEmail($email, $passwordHash) {
        try {
            $db = \DataBase::connection();
            $sql = "UPDATE usuarios SET password = :password WHERE email = :email";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'password' => $passwordHash,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

}