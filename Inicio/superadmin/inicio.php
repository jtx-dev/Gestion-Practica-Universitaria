<?php
include('../../conexion.php');

$institucionesTotal = 0;
$institucionesActivas = 0;
$institucionesInactivas = 0;
$ultimasInstituciones = [];

$sqlResumen = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN estado_institucion = 'activa' THEN 1 ELSE 0 END) AS activas,
    SUM(CASE WHEN estado_institucion = 'inactiva' THEN 1 ELSE 0 END) AS inactivas
FROM institucion";
$resultadoResumen = mysqli_query($conexion, $sqlResumen);

if ($resultadoResumen) {
    $fila = mysqli_fetch_assoc($resultadoResumen);
    $institucionesTotal = (int) ($fila['total'] ?? 0);
    $institucionesActivas = (int) ($fila['activas'] ?? 0);
    $institucionesInactivas = (int) ($fila['inactivas'] ?? 0);
}

$sqlUltimas = "SELECT nombre, estado_institucion
FROM institucion
ORDER BY id_institucion DESC
LIMIT 5";
$resultadoUltimas = mysqli_query($conexion, $sqlUltimas);

if ($resultadoUltimas) {
    while ($fila = mysqli_fetch_assoc($resultadoUltimas)) {
        $ultimasInstituciones[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio / SuperAdmin - Gestión de Prácticas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Super Administrador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-shield-lock me-2"></i> Dashboard</a>
            <a class="nav-link" href="instituciones.php"><i class="bi bi-person-gear me-2"></i> Gestionar instituciones</a>
            <a class="nav-link" href="administradores.php"><i class="bi bi-person-badge me-2"></i> Gestionar administradores</a>
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard Super Administrador</h2>
                <p class="text-muted mb-0">Resumen simple del estado de las instituciones.</p>
            </div>
            <a href="instituciones.php" class="btn btn-primary">
                <i class="bi bi-building-gear me-2"></i>Gestionar instituciones
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="instituciones.php" class="d-block text-decoration-none text-reset">
                    <div class="card-custom p-3 h-100">
                        <p class="fw-bold mb-2"><i class="bi bi-building me-2"></i>Total instituciones</p>
                        <h3 class="mb-0"><?= $institucionesTotal ?></h3>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="instituciones.php" class="d-block text-decoration-none text-reset">
                    <div class="card-custom p-3 h-100">
                        <p class="fw-bold mb-2"><i class="bi bi-check-circle me-2"></i>Activas</p>
                        <h3 class="mb-0"><?= $institucionesActivas ?></h3>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="instituciones.php" class="d-block text-decoration-none text-reset">
                    <div class="card-custom p-3 h-100">
                        <p class="fw-bold mb-2"><i class="bi bi-slash-circle me-2"></i>Inactivas</p>
                        <h3 class="mb-0"><?= $institucionesInactivas ?></h3>
                    </div>
                </a>
            </div>
        </div>

        <div class="card-custom p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Últimas instituciones</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($ultimasInstituciones) > 0): ?>
                            <?php foreach ($ultimasInstituciones as $institucion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($institucion['nombre']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $institucion['estado_institucion'] === 'activa' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($institucion['estado_institucion']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No hay instituciones registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
