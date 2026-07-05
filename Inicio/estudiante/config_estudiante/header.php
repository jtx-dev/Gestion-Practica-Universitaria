<?php
$pagina_actual = isset($pagina_actual) ? $pagina_actual : 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Estudiante - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">
                PRÁCTICAS ALUMNOS
            </div>
        </div>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?= ($pagina_actual == 'dashboard' || $pagina_actual == 'detalle_oferta') ? 'active' : ''; ?>" href="inicio.php?pagina=dashboard">
                <i class="bi bi-grid-1x2-fill me-2"></i>Inicio / Ofertas
            </a>
            <a class="nav-link <?= ($pagina_actual == 'documentos') ? 'active' : ''; ?>" href="inicio.php?pagina=documentos">
                <i class="bi bi-file-earmark-arrow-up me-2"></i> Mis Documentos
            </a>
            <a class="nav-link <?= ($pagina_actual == 'postulaciones') ? 'active' : ''; ?>" href="inicio.php?pagina=postulaciones">
                <i class="bi bi-briefcase me-2"></i> Postulaciones
            </a>
            <a class="nav-link <?= ($pagina_actual == 'perfil') ? 'active' : ''; ?>" href="inicio.php?pagina=perfil">
                <i class="bi bi-person me-2"></i> Mi Perfil
            </a>
            <a class="nav-link text-danger mt-auto mb-4" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="mb-1">Panel Estudiante</h1>
                <?php if ($pagina_actual === 'dashboard'): ?>
                    Hola <?= htmlspecialchars($estudiante['nombre'] ?? ''); ?>, estas son las ofertas disponibles.
                <?php elseif ($pagina_actual === 'documentos'): ?>
                    Sube y gestiona tus documentos requeridos para la práctica.
                <?php elseif ($pagina_actual === 'postulaciones'): ?>
                    Revisa el estado de tus postulaciones a ofertas de práctica.
                <?php elseif ($pagina_actual === 'perfil'): ?>
                    Completa tu perfil académico, profesional y técnico.
                <?php elseif ($pagina_actual === 'detalle_oferta'): ?>
                    Detalle de la oferta de práctica y postulación.
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="mb-0 fw-bold"><?= htmlspecialchars(($estudiante['nombre'] ?? '') . ' ' . ($estudiante['apellido'] ?? '')); ?></p>
                    <small class="text-muted"><?= htmlspecialchars($estudiante['nombre_carrera'] ?? ''); ?></small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </header>
