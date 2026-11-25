<?php
require_once __DIR__ . '/../core/DataBase.php';
require_once __DIR__ . '/../models/SocioModel.php';

if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/../../.env')) {
    $envLines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
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

class SocioController
{
    public static function verificarIngreso()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dni'])) {
            $dni = trim($_POST['dni']);
            $socio = SocioModel::buscarPorDni($dni);

            $hoy = date('Y-m-d');

            if ($socio) {
                $vencimiento = $socio->fecha_vencimiento;
                $permitido = ($vencimiento >= $hoy);

                SocioModel::registrarAsistencia($socio->id_socio, $permitido);

                $diffSegundos = strtotime($vencimiento) - strtotime($hoy);
                $dias_restantes = (int)round($diffSegundos / 86400);

                return [
                    'encontrado' => true,
                    'id_socio' => $socio->id_socio,
                    'dni' => $socio->dni,
                    'nombre' => $socio->nombre . ' ' . $socio->apellido,
                    'vencimiento' => $vencimiento,
                    'permitido' => $permitido,
                    'dias_restantes' => $dias_restantes
                ];
            } else {
                return ['encontrado' => false];
            }
        }
        return null;
    }

    public static function listar()
    {
        return SocioModel::obtenerTodos();
    }

    public static function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fechaPago = !empty($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');
            $fechaVencimiento = $_POST['fecha_vencimiento'];

            if (isset($_POST['id_socio']) && !empty($_POST['id_socio'])) {
                SocioModel::actualizar([
                    ':id_socio' => $_POST['id_socio'],
                    ':dni' => $_POST['dni'],
                    ':nombre' => $_POST['nombre'],
                    ':apellido' => $_POST['apellido'],
                    ':telefono' => $_POST['telefono'],
                    ':email' => $_POST['email'],
                    ':fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    ':fecha_alta' => $fechaPago,
                    ':fecha_vencimiento' => $fechaVencimiento
                ]);
                $idSocio = $_POST['id_socio'];
            } else {
                $idSocio = SocioModel::crear([
                    ':dni' => $_POST['dni'],
                    ':nombre' => $_POST['nombre'],
                    ':apellido' => $_POST['apellido'],
                    ':telefono' => $_POST['telefono'],
                    ':email' => $_POST['email'],
                    ':fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    ':fecha_alta' => $fechaPago,
                    ':fecha_vencimiento' => $fechaVencimiento
                ]);
            }

            if ($idSocio && method_exists('SocioModel', 'registrarPago')) {
                SocioModel::registrarPago($idSocio, $fechaPago, $fechaVencimiento);
            }

            header("Location: dashboard.php");
            exit();
        }
    }

    public static function borrar()
    {
        if (isset($_GET['id'])) {
            SocioModel::eliminar($_GET['id']);
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>