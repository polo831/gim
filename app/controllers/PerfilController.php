<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/SocioModel.php';

class PerfilController
{
    public static function entrarConDni($dni)
    {
        $socio = SocioModel::buscarPorDni(trim($dni));
        if ($socio) {
            $_SESSION['socio_id'] = $socio->id_socio;
            $_SESSION['socio_dni'] = $socio->dni;
            return true;
        }
        return false;
    }

    public static function obtenerSocioLogueado()
    {
        if (!isset($_SESSION['socio_id'])) {
            return null;
        }
        return SocioModel::obtenerPorId($_SESSION['socio_id']);
    }

    public static function procesarAcciones($id_socio)
    {
        $mensaje = null;
        $tipoAlerta = 'success';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar_datos') {
            $ok = SocioModel::actualizarPerfil(
                $id_socio,
                trim($_POST['nombre']),
                trim($_POST['apellido']),
                trim($_POST['telefono']),
                trim($_POST['email']),
                $_POST['fecha_nacimiento'] ?: null
            );
            $mensaje = $ok ? "Datos personales actualizados correctamente." : "No se pudieron actualizar los datos.";
            $tipoAlerta = $ok ? 'success' : 'danger';
        }

        if ($accion === 'guardar_rutina') {
            $ok = SocioModel::guardarRutina(
                $id_socio,
                trim($_POST['titulo_rutina'] ?: 'Mi Rutina'),
                trim($_POST['detalle_rutina'])
            );
            $mensaje = $ok ? "Rutina guardada correctamente." : "Error al guardar la rutina.";
            $tipoAlerta = $ok ? 'success' : 'danger';
        }

        if ($accion === 'subir_foto' && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
            $tipoArchivo = mime_content_type($_FILES['foto']['tmp_name']);
            $tamanoMax = 2 * 1024 * 1024;

            if (!in_array($tipoArchivo, $permitidos)) {
                $mensaje = "Solo se permiten imágenes JPG, PNG o WEBP.";
                $tipoAlerta = 'danger';
            } elseif ($_FILES['foto']['size'] > $tamanoMax) {
                $mensaje = "La imagen no debe superar los 2 MB.";
                $tipoAlerta = 'danger';
            } else {
                $carpetaDestino = __DIR__ . '/../../public/uploads/perfiles/';
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }

                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = 'socio_' . $id_socio . '_' . time() . '.' . strtolower($extension);
                $rutaFisica = $carpetaDestino . $nombreArchivo;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaFisica)) {
                    $rutaWeb = '../../public/uploads/perfiles/' . $nombreArchivo;
                    SocioModel::actualizarFoto($id_socio, $rutaWeb);
                    $mensaje = "Foto de perfil actualizada.";
                    $tipoAlerta = 'success';
                } else {
                    $mensaje = "No se pudo mover la imagen al servidor.";
                    $tipoAlerta = 'danger';
                }
            }
        }

        return ['mensaje' => $mensaje, 'tipo' => $tipoAlerta];
    }

    public static function cerrarSesion()
    {
        unset($_SESSION['socio_id'], $_SESSION['socio_dni']);
        header("Location: index.php");
        exit();
    }
}
?>