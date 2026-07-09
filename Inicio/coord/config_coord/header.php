<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Coordinador - Gestión de Prácticas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/base.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Panel Coordinador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?= $pagina_actual === 'inicio' ? 'active' : '' ?>" href="inicio.php?pagina=inicio">
                <i class="bi bi-speedometer2 me-2"></i> Vista Global
            </a>
            <a class="nav-link <?= $pagina_actual === 'alumnos' ? 'active' : '' ?>" href="inicio.php?pagina=alumnos">
                <i class="bi bi-people me-2"></i> Alumnos
            </a>
            <a class="nav-link <?= $pagina_actual === 'ofertas' ? 'active' : '' ?>" href="inicio.php?pagina=ofertas">
                <i class="bi bi-building me-2"></i> Empresas / Ofertas
            </a>
            <a class="nav-link <?= $pagina_actual === 'ofertas_aprobadas' ? 'active' : '' ?>"
                href="inicio.php?pagina=ofertas_aprobadas">
                <i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas
            </a>
            <a class="nav-link <?= $pagina_actual === 'ofertas_rechazadas' ? 'active' : '' ?>"
                href="inicio.php?pagina=ofertas_rechazadas">
                <i class="bi bi-x-circle me-2"></i> Ofertas Rechazadas
            </a>
            <a class="nav-link <?= $pagina_actual === 'validacion' ? 'active' : '' ?>"
                href="inicio.php?pagina=validacion">
                <i class="bi bi-file-earmark-check me-2"></i> Validaciones
            </a>
            <a class="nav-link <?= $pagina_actual === 'postulaciones' ? 'active' : '' ?>"
                href="inicio.php?pagina=postulaciones">
                <i class="bi bi-file-earmark-person me-2"></i> Postulaciones
            </a>

            <a class="nav-link text-danger mt-auto mb-4" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
<header class="mb-5 d-flex justify-content-between align-items-center">

    <!-- Lado izquierdo -->
    <div class="d-flex align-items-center gap-3">

        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
             style="width:52px;height:52px;">

            <?php if ($pagina_actual === 'inicio'): ?>
                <i class="bi bi-speedometer2 text-primary fs-4"></i>

            <?php elseif ($pagina_actual === 'alumnos'): ?>
                <i class="bi bi-people-fill text-primary fs-4"></i>

            <?php elseif ($pagina_actual === 'ofertas'): ?>
                <i class="bi bi-building-fill text-primary fs-4"></i>

            <?php elseif ($pagina_actual === 'ofertas_aprobadas'): ?>
                <i class="bi bi-check-circle-fill text-success fs-4"></i>

            <?php elseif ($pagina_actual === 'ofertas_rechazadas'): ?>
                <i class="bi bi-x-circle-fill text-danger fs-4"></i>

            <?php elseif ($pagina_actual === 'validacion'): ?>
                <i class="bi bi-file-earmark-check-fill text-primary fs-4"></i>

            <?php elseif ($pagina_actual === 'postulaciones'): ?>
                <i class="bi bi-file-earmark-person-fill text-primary fs-4"></i>
            <?php endif; ?>

        </div>

        <div>

            <?php if ($pagina_actual === 'inicio'): ?>

                <h4 class="fw-bold mb-1">Resumen de Gestión</h4>
                <p class="text-muted fw-semibold mb-0">
                    Control estadístico de tu carrera en tiempo real.
                </p>

            <?php elseif ($pagina_actual === 'alumnos'): ?>

                <h4 class="fw-bold mb-1">Listado de Alumnos</h4>
                <p class="text-muted fw-semibold mb-0">
                    Gestión y seguimiento de estudiantes de la carrera.
                </p>

            <?php elseif ($pagina_actual === 'ofertas'): ?>

                <h4 class="fw-bold mb-1">Gestión de Empresas / Ofertas</h4>
                <p class="text-muted fw-semibold mb-0">
                    Revisión de ofertas de práctica enviadas por empresas.
                </p>

            <?php elseif ($pagina_actual === 'ofertas_aprobadas'): ?>

                <h4 class="fw-bold mb-1">Ofertas Aprobadas</h4>
                <p class="text-muted fw-semibold mb-0">
                    Ofertas activas con sistema de matching de postulantes para tu carrera.
                </p>

            <?php elseif ($pagina_actual === 'ofertas_rechazadas'): ?>

                <h4 class="fw-bold mb-1">Ofertas Rechazadas</h4>
                <p class="text-muted fw-semibold mb-0">
                    Historial de ofertas rechazadas por el coordinador.
                </p>

            <?php elseif ($pagina_actual === 'validacion'): ?>

                <h4 class="fw-bold mb-1">Validación de Documentos</h4>
                <p class="text-muted fw-semibold mb-0">
                    Revisa y aprueba la documentación obligatoria de tus estudiantes.
                </p>

            <?php elseif ($pagina_actual === 'postulaciones'): ?>

                <h4 class="fw-bold mb-1">Bandeja de Postulaciones</h4>
                <p class="text-muted fw-semibold mb-0">
                    Visualiza las postulaciones activas de los estudiantes.
                </p>

            <?php endif; ?>

        </div>

    </div>

    <!-- Lado derecho -->
    <?php if ($pagina_actual === 'inicio'): ?>
        <a href="inicio.php?pagina=ofertas"
           class="btn btn-primary shadow-sm px-4">
            <i class="bi bi-search me-2"></i>
            Revisar Ofertas Pendientes
        </a>
    <?php endif; ?>

</header>
