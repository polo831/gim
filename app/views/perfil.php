<?php
require_once __DIR__ . '/../controllers/PerfilController.php';

if (isset($_GET['logout'])) {
    PerfilController::cerrarSesion();
}

$dniParam = isset($_GET['dni']) ? trim($_GET['dni']) : '';
$socio = null;

if ($dniParam !== '') {
    $socio = SocioModel::buscarPorDni($dniParam);
    if ($socio) {
        $id = is_object($socio) ? ($socio->id_socio ?? null) : ($socio['id_socio'] ?? null);
        if ($id) {
            $_SESSION['socio_id'] = $id;
            $_SESSION['socio_dni'] = is_object($socio) ? ($socio->dni ?? $dniParam) : ($socio['dni'] ?? $dniParam);
        }
    }
} elseif (!empty($_SESSION['socio_id'])) {
    $socio = SocioModel::obtenerPorId($_SESSION['socio_id']);
}

if (!$socio) {
    header("Location: index.php");
    exit();
}

$idSocioActual = is_object($socio) ? $socio->id_socio : $socio['id_socio'];
$feedback = PerfilController::procesarAcciones($idSocioActual);
$socio = SocioModel::obtenerPorId($idSocioActual);
$rutina = SocioModel::obtenerRutina($idSocioActual);
$historial = SocioModel::obtenerHistorialAsistencias($idSocioActual);

$fotoMostrar = !empty($socio->foto_url)
    ? htmlspecialchars($socio->foto_url)
    : "https://via.placeholder.com/150?text=Sin+Foto";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - <?php echo htmlspecialchars($socio->nombre); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold">🏋️‍♂️ Mi Perfil de Socio</span>
        <div>
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">Entrada DNI</a>
            <a href="perfil.php?logout=1" class="btn btn-danger btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <?php if ($feedback && $feedback['mensaje']): ?>
        <div class="alert alert-<?php echo $feedback['tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($feedback['mensaje']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm text-center p-4">
                <img src="<?php echo $fotoMostrar; ?>" alt="Foto de perfil"
                     class="rounded-circle mx-auto mb-3 border"
                     style="width: 140px; height: 140px; object-fit: cover;">

                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($socio->nombre . ' ' . $socio->apellido); ?></h4>
                <p class="text-muted mb-2">DNI: <?php echo htmlspecialchars($socio->dni); ?></p>

                <?php if ($socio->fecha_vencimiento >= date('Y-m-d')): ?>
                    <span class="badge bg-success mb-2">Cuota al día</span>
                <?php else: ?>
                    <span class="badge bg-danger mb-2">Cuota vencida</span>
                <?php endif; ?>
                <p class="small text-muted mb-3">Vencimiento: <strong><?php echo $socio->fecha_vencimiento; ?></strong></p>

                <form method="POST" enctype="multipart/form-data" class="text-start border-top pt-3">
                    <input type="hidden" name="accion" value="subir_foto">
                    <input type="hidden" name="dni" value="<?php echo htmlspecialchars($socio->dni); ?>">
                    <label class="form-label small fw-bold">Cambiar foto de perfil</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm mb-2" required>
                    <button type="submit" class="btn btn-dark btn-sm w-100">Subir Foto</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <ul class="nav nav-tabs mb-3" id="perfilTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-rutina" type="button">Mi Rutina</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button">Historial</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-datos" type="button">Mis Datos</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-rutina">
                    <div class="card shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Cargar / Actualizar Rutina</h5>
                        <form method="POST">
                            <input type="hidden" name="accion" value="guardar_rutina">
                            <input type="hidden" name="dni" value="<?php echo htmlspecialchars($socio->dni); ?>">
                            <div class="mb-3">
                                <label class="form-label">Nombre de la rutina</label>
                                <input type="text" name="titulo_rutina" class="form-control"
                                       value="<?php echo htmlspecialchars($rutina->titulo ?? 'Rutina Semanal'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ejercicios, series y repeticiones</label>
                                <textarea name="detalle_rutina" rows="8" class="form-control"
                                          placeholder="Ej:&#10;Lunes - Pecho y Tríceps:&#10;- Press banca 4x10&#10;- Aperturas 3x12..." required><?php echo htmlspecialchars($rutina->detalle ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary fw-bold">Guardar Rutina</button>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-historial">
                    <div class="card shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Historial de Asistencias</h5>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Estado del Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($historial)): ?>
                                        <?php foreach ($historial as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item->fecha_hora); ?></td>
                                                <td>
                                                    <?php if ($item->acceso_permitido): ?>
                                                        <span class="badge bg-success">Permitido</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Denegado</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">Aún no tienes asistencias registradas.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-datos">
                    <div class="card shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Modificar Datos Personales</h5>
                        <form method="POST">
                            <input type="hidden" name="accion" value="actualizar_datos">
                            <input type="hidden" name="dni" value="<?php echo htmlspecialchars($socio->dni); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" class="form-control"
                                           value="<?php echo htmlspecialchars($socio->nombre); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" name="apellido" class="form-control"
                                           value="<?php echo htmlspecialchars($socio->apellido); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" name="telefono" class="form-control"
                                           value="<?php echo htmlspecialchars($socio->telefono ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?php echo htmlspecialchars($socio->email ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control"
                                       value="<?php echo htmlspecialchars($socio->fecha_nacimiento ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-success fw-bold">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>