<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/DataBase.php';

class SocioModel extends Model
{
    protected $table = "socios";
    protected $primaryKey = "id_socio";

    public static function obtenerTodos()
    {
        $sql = "SELECT * FROM socios WHERE eliminado = 0 ORDER BY id_socio DESC";
        $res = DataBase::getRecords($sql);
        return $res['status'] ? $res['resultado'] : [];
    }

    public static function buscarPorDni($dni)
    {
        $sql = "SELECT * FROM socios WHERE dni = :dni AND eliminado = 0 LIMIT 1";
        return DataBase::getRecord($sql, [':dni' => $dni]);
    }

    public static function obtenerPorId($id)
    {
        $sql = "SELECT * FROM socios WHERE id_socio = :id AND eliminado = 0 LIMIT 1";
        return DataBase::getRecord($sql, [':id' => $id]);
    }

    public static function crear($datos)
    {
        $sql = "INSERT INTO socios (dni, nombre, apellido, telefono, email, fecha_nacimiento, fecha_alta, fecha_vencimiento)
                VALUES (:dni, :nombre, :apellido, :telefono, :email, :fecha_nacimiento, :fecha_alta, :fecha_vencimiento)";
        $res = DataBase::execute($sql, $datos);
        if ($res['status']) {
            $last = DataBase::getRecord("SELECT LAST_INSERT_ID() AS id");
            return $last ? $last->id : true;
        }
        return false;
    }

    public static function actualizar($datos)
    {
        $sql = "UPDATE socios SET dni = :dni, nombre = :nombre, apellido = :apellido,
                telefono = :telefono, email = :email, fecha_nacimiento = :fecha_nacimiento,
                fecha_alta = :fecha_alta, fecha_vencimiento = :fecha_vencimiento
                WHERE id_socio = :id_socio AND eliminado = 0";
        return DataBase::execute($sql, $datos);
    }

    public static function eliminar($id_socio)
    {
        $sql = "UPDATE socios SET eliminado = 1 WHERE id_socio = :id_socio";
        return DataBase::execute($sql, [':id_socio' => $id_socio]);
    }

    public static function registrarPago($id_socio, $fecha_pago, $fecha_vencimiento)
    {
        $sql = "INSERT INTO pagos (id_socio, fecha_pago, fecha_vencimiento)
                VALUES (:id_socio, :fecha_pago, :fecha_vencimiento)";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':fecha_pago' => $fecha_pago,
            ':fecha_vencimiento' => $fecha_vencimiento
        ]);
    }

    public static function registrarAsistencia($id_socio, $permitido)
    {
        $sql = "INSERT INTO asistencias (id_socio, acceso_permitido) VALUES (:id_socio, :permitido)";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':permitido' => $permitido ? 1 : 0
        ]);
    }

    public static function obtenerHistorialAsistencias($id_socio)
    {
        $sql = "SELECT fecha_hora, acceso_permitido FROM asistencias
                WHERE id_socio = :id_socio ORDER BY fecha_hora DESC LIMIT 30";
        $res = DataBase::getRecords($sql, [':id_socio' => $id_socio]);
        return $res['status'] ? $res['resultado'] : [];
    }

    public static function obtenerRutina($id_socio)
    {
        $sql = "SELECT * FROM rutinas WHERE id_socio = :id_socio LIMIT 1";
        return DataBase::getRecord($sql, [':id_socio' => $id_socio]);
    }

    public static function guardarRutina($id_socio, $titulo, $detalle)
    {
        $existente = self::obtenerRutina($id_socio);
        if ($existente) {
            $sql = "UPDATE rutinas SET titulo = :titulo, detalle = :detalle WHERE id_socio = :id_socio";
        } else {
            $sql = "INSERT INTO rutinas (id_socio, titulo, detalle) VALUES (:id_socio, :titulo, :detalle)";
        }
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':titulo' => $titulo,
            ':detalle' => $detalle
        ]);
    }

    public static function actualizarPerfil($id_socio, $nombre, $apellido, $telefono, $email, $fecha_nacimiento)
    {
        $sql = "UPDATE socios SET nombre = :nombre, apellido = :apellido, telefono = :telefono,
                email = :email, fecha_nacimiento = :fecha_nacimiento
                WHERE id_socio = :id_socio AND eliminado = 0";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':telefono' => $telefono,
            ':email' => $email,
            ':fecha_nacimiento' => $fecha_nacimiento
        ]);
    }

    public static function actualizarFoto($id_socio, $foto_url)
    {
        $sql = "UPDATE socios SET foto_url = :foto_url WHERE id_socio = :id_socio AND eliminado = 0";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':foto_url' => $foto_url
        ]);
    }
}
?>