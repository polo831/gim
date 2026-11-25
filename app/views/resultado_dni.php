<?php
require_once __DIR__ . '/../controllers/SocioController.php';
$res = SocioController::verificarIngreso();

if (!$res) {
    header("Location: index.php");
    exit();
}

$permitido = ($res['encontrado'] && $res['permitido']);
$diasRestantes = isset($res['dias_restantes']) ? (int)$res['dias_restantes'] : 0;
$fechaVenc = isset($res['vencimiento']) ? date('d/m/Y', strtotime($res['vencimiento'])) : '-';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Acceso - Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!$res['encontrado']): ?>
        <meta http-equiv="refresh" content="5;url=index.php">
    <?php endif; ?>
</head>
<body class="vh-100 d-flex justify-content-center align-items-center text-white 
    <?php echo $permitido ? 'bg-success' : 'bg-danger'; ?>">

    <div class="text-center p-5 shadow-lg rounded-4 bg-dark bg-opacity-50" style="max-width: 650px; width: 92%;">
        <?php if ($res['encontrado']): ?>
            <?php if ($res['permitido']): ?>
                <div class="display-2 mb-2">✅</div>
                <h1 class="display-4 fw-bold mb-2">¡ACCESO PERMITIDO!</h1>
                <h2 class="my-3 fw-bold text-warning"><?php echo htmlspecialchars($res['nombre']); ?></h2>
                <p class="fs-5 text-light mb-4">Tu cuota está al día.</p>

                <div class="card bg-white text-dark shadow border-0 my-3 p-4 rounded-4">
                    <div class="text-uppercase tracking-wide text-muted fw-bold small mb-2">Estado de tu membresía</div>

                    <?php if ($diasRestantes > 1): ?>
                        <div class="display-6 fw-bold text-success mb-2">
                            Te quedan <span class="badge bg-success px-3 py-2 fs-3"><?php echo $diasRestantes; ?> días</span> de saldo
                        </div>
                    <?php elseif ($diasRestantes == 1): ?>
                        <div class="display-6 fw-bold text-warning mb-2">
                            ⚠️ ¡Te queda <span class="badge bg-warning text-dark px-3 py-2 fs-3">1 solo día</span> de saldo!
                        </div>
                    <?php else: ?>
                        <div class="display-6 fw-bold text-danger mb-2">
                            ⚠️ ¡Tu cuota vence hoy!
                        </div>
                    <?php endif; ?>

                    <hr class="my-2">
                    <div class="fs-5 mt-2 text-secondary">
                        Fecha de vencimiento: <strong class="text-dark"><?php echo $fechaVenc; ?></strong>
                    </div>
                </div>

            <?php else: ?>
                <div class="display-2 mb-2">❌</div>
                <h1 class="display-4 fw-bold mb-2">ACCESO DENEGADO</h1>
                <h2 class="my-3 fw-bold text-warning"><?php echo htmlspecialchars($res['nombre']); ?></h2>
                <p class="fs-5 text-light mb-4">Tu cuota se encuentra vencida.</p>

                <div class="card bg-white text-dark shadow border-0 my-3 p-4 rounded-4">
                    <div class="text-uppercase tracking-wide text-danger fw-bold small mb-2">Sin saldo disponible</div>

                    <div class="fs-4 text-danger fw-bold mb-2">
                        <?php if ($diasRestantes < 0): ?>
                            Cuota vencida hace <?php echo abs($diasRestantes); ?> día(s)
                        <?php else: ?>
                            Cuota vencida
                        <?php endif; ?>
                    </div>

                    <hr class="my-2">
                    <div class="fs-5 text-secondary">
                        Venció el: <strong class="text-dark"><?php echo $fechaVenc; ?></strong>
                    </div>

                    <div class="alert alert-warning mb-0 mt-3 py-2 fw-semibold">
                        Por favor, acércate a recepción para renovar tu cuota.
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4 d-flex flex-wrap justify-content-center gap-3">
                <a href="perfil.php?dni=<?php echo urlencode($res['dni']); ?>" class="btn btn-warning btn-lg px-4 fw-bold text-dark shadow">Ver mi perfil</a>
                <a href="index.php" class="btn btn-light btn-lg px-4 fw-bold shadow">Volver al Inicio</a>
            </div>

        <?php else: ?>
            <div class="display-2 mb-2">❓</div>
            <h1 class="display-4 fw-bold mb-3">DNI NO REGISTRADO</h1>
            <p class="fs-4 text-light">El documento ingresado no figura en nuestra base de datos.</p>
            <p class="text-light text-opacity-75">Por favor, acércate a administración para registrarte.</p>

            <div class="mt-4">
                <a href="index.php" class="btn btn-light btn-lg px-5 fw-bold shadow">Volver al Inicio</a>
            </div>
            <p class="small text-light text-opacity-75 mt-3 mb-0">Esta pantalla volverá al inicio automáticamente en 5 segundos...</p>
        <?php endif; ?>
    </div>

</body>
</html>