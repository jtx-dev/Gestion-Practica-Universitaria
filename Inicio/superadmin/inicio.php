<?php
include('../../conexion.php');

$sqlInstitucionesActivas = "SELECT COUNT(*) AS total FROM Institucion WHERE estado_institucion = 'activa'";
$resultadoInstitucionesActivas = mysqli_query($conexion, $sqlInstitucionesActivas);
$institucionesActivas = 0;

if ($resultadoInstitucionesActivas) {
    $fila = mysqli_fetch_assoc($resultadoInstitucionesActivas);
    $institucionesActivas = (int) $fila['total'];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio / SuperAdmin - Gestión de Prácticas</title>


    <!-- Google Font Railway -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- ------------------- -->

    <!-- Google Font Fredoka -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- ------------------- -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=../../assets/css/base.css>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Super Administrador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-shield-lock me-2"></i> Dashboard</a>
            <a class="nav-link" href="instituciones.php"><i class="bi bi-person-gear me-2"></i> Gestionar
                instituciones</a>
            <a class="nav-link" href="#"><i class="bi bi-server me-2"></i> Estado del Sistema</a>
            <a class="nav-link" href="#"><i class="bi bi-journal-text me-2"></i> Logs de Auditoría</a>
            <a class="nav-link" href="#"><i class="bi bi-database-up me-2"></i> Backups Globales</a>
            <a class="nav-link" href="#"><i class="bi bi-gear-wide-connected me-2"></i> Configuración</a>
            <a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i> Mi Perfil</a>

            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard Super Administrador</h2>
                <p class="text-muted">Gestión global de administradores e instituciones.</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a class="d-block text-decoration-none text-reset" href="instituciones.php">
                    <div class="card-custom">
                        <p class="fw-bold m-3"><i class="bi bi-mortarboard me-2"></i> Instituciones activas </p>
                        <h3 class="px-3 pb-3 mb-0"><?= $institucionesActivas ?></h3>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="sistema.php" class="d-block text-decoration-none text-reset">
                    <div class="card-custom">
                        <p class="fw-bold m-3">
                            <i class="bi bi-database-up me-2"></i> Consumo del sistema
                        </p>
                        <h3 class="px-3 pb-3 mb-0" id="consumoDisco">Cargando...</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function actualizarDisco() {
            fetch('componentes/disco.php', { cache: 'no-store' })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('consumoDisco').textContent = data.porcentaje + '%';
                })
                .catch(() => {
                    document.getElementById('consumoDisco').textContent = 'Error';
                });
        }

        actualizarDisco();
        setInterval(actualizarDisco, 20000);
    </script>
</body>

</html>
