<?php
require_once __DIR__ . '/../../controllers/AdminAuthController.php';

// Si ya está logueado como admin, va directo al dashboard
if (AdminAuthController::estaAutenticadoAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = AdminAuthController::login($usuario, $password);

    if ($res['status']) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = $res['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex justify-content-center align-items-center vh-100 text-white">

    <div class="card shadow-lg bg-secondary text-white p-4" style="width: 100%; max-width: 420px; border-radius: 12px;">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="display-4 mb-2">🏋️‍♂️</div>
                <h3 class="fw-bold">Acceso Admin</h3>
                <p class="text-light small">Panel de Control del Gimnasio</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show text-dark fw-semibold" role="alert">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Usuario o Correo</label>
                    <input type="text" name="usuario" class="form-control form-control-lg" placeholder="admin@gym.com" required autofocus autocomplete="username" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">
                    Iniciar Sesión
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none text-light small">
                    ← Volver al Tótem de Entrada (DNI)
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
