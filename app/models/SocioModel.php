<?php
class Socio {

    // Buscar socio por DNI (para la pantalla de entrada / tótem)
    public static function buscarPorDni($dni) {
        $sql = "SELECT * FROM socios WHERE dni = :dni AND eliminado = 0 LIMIT 1";
        return DataBase::getRecord($sql, [':dni' => $dni]);
    }

    // Registrar la asistencia en la tabla asistencias guardando fecha y hora exacta
    public static function registrarAsistencia($id_socio, $permitido) {
        $sql = "INSERT INTO asistencias (id_socio, fecha_hora, acceso_permitido) VALUES (:id_socio, NOW(), :permitido)";
        return DataBase::execute($sql, [
            ':id_socio' => $id_socio,
            ':permitido' => $permitido ? 1 : 0
        ]);
    }

    // Obtener todos los socios activos para el panel del admin, con su última fecha de pago y último ingreso por tótem
    public static function obtenerTodos() {
        $sql = "SELECT 
                    s.*,
                    COALESCE(
                        (SELECT p.fecha_pago FROM pagos p WHERE p.id_socio = s.id_socio ORDER BY p.fecha_pago DESC, p.id_pago DESC LIMIT 1),
                        s.fecha_alta
                    ) AS fecha_pago,
                    (SELECT a.fecha_hora FROM asistencias a WHERE a.id_socio = s.id_socio ORDER BY a.fecha_hora DESC, a.id_asistencia DESC LIMIT 1) AS ultimo_ingreso,
                    (SELECT a.acceso_permitido FROM asistencias a WHERE a.id_socio = s.id_socio ORDER BY a.fecha_hora DESC, a.id_asistencia DESC LIMIT 1) AS ultimo_ingreso_permitido
                FROM socios s 
                WHERE s.eliminado = 0 
                ORDER BY s.id_socio DESC";
        $resultado = DataBase::getRecords($sql);
        return $resultado['status'] ? $resultado['resultado'] : [];
    }

    // Obtener un socio por su ID (para editar)
    public static function obtenerPorId($id) {
        $sql = "SELECT 
                    s.*,
                    COALESCE(
                        (SELECT p.fecha_pago FROM pagos p WHERE p.id_socio = s.id_socio ORDER BY p.fecha_pago DESC, p.id_pago DESC LIMIT 1),
                        s.fecha_alta
                    ) AS fecha_pago
                FROM socios s 
                WHERE s.id_socio = :id LIMIT 1";
        return DataBase::getRecord($sql, [':id' => $id]);
    }

    // Crear un socio nuevo
    public static function crear($datos) {
        $sql = "INSERT INTO socios (dni, nombre, apellido, telefono, email, fecha_nacimiento, fecha_alta, fecha_vencimiento, estado) 
                VALUES (:dni, :nombre, :apellido, :telefono, :email, :fecha_nacimiento, :fecha_alta, :fecha_vencimiento, 'activo')";
        DataBase::execute($sql, $datos);
        $res = DataBase::getRecord("SELECT LAST_INSERT_ID() as id");
        return $res ? $res->id : null;
    }

    // Actualizar un socio existente
    public static function actualizar($datos) {
        $sql = "UPDATE socios SET dni = :dni, nombre = :nombre, apellido = :apellido, telefono = :telefono, 
                email = :email, fecha_nacimiento = :fecha_nacimiento, fecha_alta = :fecha_alta, fecha_vencimiento = :fecha_vencimiento 
                WHERE id_socio = :id_socio";
        return DataBase::execute($sql, $datos);
    }

    // Registrar historial de pago en la tabla pagos (si está disponible)
    public static function registrarPago($id_socio, $fecha_pago, $nuevo_vencimiento, $monto = 0, $metodo_pago = 'efectivo') {
        try {
            $adminId = $_SESSION['admin_id'] ?? null;
            $plan = DataBase::getRecord("SELECT id_plan FROM planes WHERE activo = 1 LIMIT 1");
            $idPlan = ($plan && isset($plan->id_plan)) ? $plan->id_plan : null;

            $sql = "INSERT INTO pagos (id_socio, id_plan, id_admin, fecha_pago, monto, metodo_pago, nuevo_vencimiento) 
                    VALUES (:id_socio, :id_plan, :id_admin, :fecha_pago, :monto, :metodo_pago, :nuevo_vencimiento)";
            return DataBase::execute($sql, [
                ':id_socio' => $id_socio,
                ':id_plan' => $idPlan,
                ':id_admin' => $adminId,
                ':fecha_pago' => $fecha_pago,
                ':monto' => $monto,
                ':metodo_pago' => $metodo_pago,
                ':nuevo_vencimiento' => $nuevo_vencimiento
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    // Borrado lógico de un socio
    public static function eliminar($id) {
        $sql = "UPDATE socios SET eliminado = 1 WHERE id_socio = :id";
        return DataBase::execute($sql, [':id' => $id]);
    }
}
?>