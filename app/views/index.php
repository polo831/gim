<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Gimnasio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex flex-column justify-content-center align-items-center vh-100">

    <div class="card p-5 shadow-lg bg-secondary text-white text-center" style="width: 450px;">
        <h2 class="mb-4 fw-bold">CONTROL DE ACCESO</h2>
        <p class="text-light mb-4">Por favor, ingrese su número de DNI</p>
        
        <form action="resultado_dni.php" method="POST">
            <div class="mb-3">
                <input type="text" name="dni" class="form-control form-control-lg text-center" placeholder="Ej: 35123456" required autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Verificar Ingreso</button>
        </form>
        
        <div class="mt-4">
            <a href="admin/dashboard.php" class="text-decoration-none text-light small">Panel de Administración</a>
        </div>
    </div>

</body>
</html>