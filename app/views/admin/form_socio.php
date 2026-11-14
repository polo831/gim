<?php
require_once __DIR__ . '/../../controllers/AdminAuthController.php';
AdminAuthController::verificarSesionAdmin();

require_once __DIR__ . '/../../controllers/SocioController.php';

$socioActual = null;
if (isset($_GET['id'])) {
    $socioActual = Socio::obtenerPorId($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    SocioController::guardar();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $socioActual ? 'Editar Socio' : 'Nuevo Socio'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">🏋️‍♂️ Admin Gimnasio</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light small me-2">
                    👤 <strong><?php echo htmlspecialchars($_SESSION['admin_nombre'] ?? 'Administrador'); ?></strong>
                    <span class="badge bg-success ms-1">Admin</span>
                </span>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">Panel Socios</a>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="mb-4 text-center fw-bold"><?php echo $socioActual ? 'Editar Socio' : 'Registrar Nuevo Socio'; ?></h2>
                
                <form action="form_socio.php" method="POST">
                    <?php if ($socioActual): ?>
                        <input type="hidden" name="id_socio" value="<?php echo $socioActual->id_socio; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" value="<?php echo $socioActual ? $socioActual->dni : ''; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $socioActual ? $socioActual->nombre : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo $socioActual ? $socioActual->apellido : ''; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo $socioActual ? $socioActual->telefono : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $socioActual ? $socioActual->email : ''; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $socioActual ? $socioActual->fecha_nacimiento : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Fecha de Pago</label>
                            <input type="date" name="fecha_pago" class="form-control" value="<?php echo $socioActual ? ($socioActual->fecha_pago ?? $socioActual->fecha_alta) : date('Y-m-d'); ?>" required>
                            <div class="form-text">Día que abonó la cuota</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Fecha de Vencimiento</label>
                            <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo $socioActual ? $socioActual->fecha_vencimiento : date('Y-m-d', strtotime('+30 days')); ?>" required>
                            <div class="form-text">Vencimiento del pase</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4">Guardar Socio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>