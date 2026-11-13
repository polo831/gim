<?php
class Socio {

    // Buscar socio por DNI (para la pantalla de entrada / tótem)
    public static function buscarPorDni($dni) {
        $sql = "SELECT * FROM socios WHERE dni = :dni AND eliminado = 0 LIMIT 1";
        return DataBase::getRecord($sql, [':dni' => $dni]);
    }

    // Registrar la asistencia en la tabla asistencias
    public static function registrarAsistencia($id_socio, $permitido) {
        $sql = "INSERT INTO asistencias (id_socio, acceso_permitido) VALUES (:id_socio, :permitido)";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':permitido' => $permitido ? 1 : 0
        ]);
    }

    // Obtener todos los socios activos para el panel del admin
    public static function obtenerTodos() {
        $sql = "SELECT * FROM socios WHERE eliminado = 0 ORDER BY fecha_alta DESC";
        $resultado = DataBase::getRecords($sql);
        return $resultado['status'] ? $resultado['resultado'] : [];
    }

    // Obtener un socio por su ID (para editar)
    public static function obtenerPorId($id) {
        $sql = "SELECT * FROM socios WHERE id_socio = :id LIMIT 1";
        return DataBase::getRecord($sql, [':id' => $id]);
    }

    // Crear un socio nuevo
    public static function crear($datos) {
        $sql = "INSERT INTO socios (dni, nombre, apellido, telefono, email, fecha_nacimiento, fecha_alta, fecha_vencimiento, estado) 
                VALUES (:dni, :nombre, :apellido, :telefono, :email, :fecha_nacimiento, :fecha_alta, :fecha_vencimiento, 'activo')";
        return DataBase::execute($sql, $datos);
    }

    // Actualizar un socio existente
    public static function actualizar($datos) {
        $sql = "UPDATE socios SET dni = :dni, nombre = :nombre, apellido = :apellido, telefono = :telefono, 
                email = :email, fecha_nacimiento = :fecha_nacimiento, fecha_vencimiento = :fecha_vencimiento 
                WHERE id_socio = :id_socio";
        return DataBase::execute($sql, $datos);
    }

    // Borrado lógico de un socio
    public static function eliminar($id) {
        $sql = "UPDATE socios SET eliminado = 1 WHERE id_socio = :id";
        return DataBase::execute($sql, [':id' => $id]);
    }
}
?>