<?php
require_once __DIR__ . '/../../controllers/AdminAuthController.php';
AdminAuthController::verificarSesionAdmin();

require_once __DIR__ . '/../../controllers/SocioController.php';

// Si hacen clic en eliminar
if (isset($_GET['action']) && $_GET['action'] == 'eliminar') {
    SocioController::borrar();
}

$socios = SocioController::listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Gimnasio</title>
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
                <a href="../index.php" class="btn btn-outline-light btn-sm">Tótem DNI</a>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Gestión de Socios</h2>
                <p class="text-muted small mb-0">Control de membresías, pagos y registros de asistencia</p>
            </div>
            <a href="form_socio.php" class="btn btn-primary fw-bold shadow-sm">+ Nuevo Socio</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>DNI</th>
                                <th>Socio</th>
                                <th>Teléfono</th>
                                <th class="text-center">Fecha de Pago</th>
                                <th class="text-center">Vencimiento</th>
                                <th class="text-center">Días de Saldo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Último Ingreso Tótem</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($socios)): ?>
                                <?php foreach ($socios as $socio): 
                                    $hoy = date('Y-m-d');
                                    $venc = $socio->fecha_vencimiento ?? '';
                                    $alDia = (!empty($venc) && $venc >= $hoy);
                                    
                                    // Cálculo de días restantes de saldo
                                    $diasRestantes = !empty($venc) ? (int)round((strtotime($venc) - strtotime($hoy)) / 86400) : 0;

                                    // Formato de fecha de pago (de pagos o fecha_alta)
                                    $fechaPagoRaw = !empty($socio->fecha_pago) ? $socio->fecha_pago : ($socio->fecha_alta ?? null);
                                    $fechaPagoFmt = $fechaPagoRaw ? date('d/m/Y', strtotime($fechaPagoRaw)) : '<span class="text-muted">-</span>';

                                    // Formato de fecha de vencimiento
                                    $fechaVencFmt = !empty($venc) ? date('d/m/Y', strtotime($venc)) : '<span class="text-muted">-</span>';

                                    // Formato del último ingreso registrado en el tótem
                                    $ultimoIngresoFmt = '<span class="badge bg-secondary-subtle text-secondary border">Sin registros</span>';
                                    if (!empty($socio->ultimo_ingreso)) {
                                        $horaIngreso = date('d/m/Y H:i', strtotime($socio->ultimo_ingreso));
                                        $icono = (isset($socio->ultimo_ingreso_permitido) && $socio->ultimo_ingreso_permitido == 1) 
                                            ? '<span class="badge bg-success-subtle text-success border border-success ms-1">Permitido</span>' 
                                            : '<span class="badge bg-danger-subtle text-danger border border-danger ms-1">Denegado</span>';
                                        $ultimoIngresoFmt = "<div class='small fw-semibold'>📅 {$horaIngreso} hs</div>{$icono}";
                                    }
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($socio->dni); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($socio->nombre . ' ' . $socio->apellido); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($socio->email ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($socio->telefono ?? '-'); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                📅 <?php echo $fechaPagoFmt; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="<?php echo $alDia ? 'text-success fw-bold' : 'text-danger fw-bold'; ?>">
                                                <?php echo $fechaVencFmt; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($alDia): ?>
                                                <?php if ($diasRestantes > 1): ?>
                                                    <span class="badge bg-success px-2 py-1"><?php echo $diasRestantes; ?> días</span>
                                                <?php elseif ($diasRestantes == 1): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">1 día restante</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">Vence hoy</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-danger px-2 py-1">
                                                    Vencido (<?php echo $diasRestantes < 0 ? abs($diasRestantes) . ' d' : 'hoy'; ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($alDia): ?>
                                                <span class="badge bg-success">Al día</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Vencido</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $ultimoIngresoFmt; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="form_socio.php?id=<?php echo $socio->id_socio; ?>" class="btn btn-outline-primary btn-sm" title="Editar socio">✏️ Editar</a>
                                                <a href="dashboard.php?action=eliminar&id=<?php echo $socio->id_socio; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este socio?');" title="Eliminar socio">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="display-6 mb-2">👥</div>
                                        No hay socios registrados en el sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>