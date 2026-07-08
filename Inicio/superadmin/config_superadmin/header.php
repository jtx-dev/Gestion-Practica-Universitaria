<?php
$pagina_actual = isset($pagina_actual) ? $pagina_actual : 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - Gestión de Prácticas</title>
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
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">SGPPE - Super Administrador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?= $pagina_actual === 'dashboard' ? 'active' : '' ?>" href="inicio.php?pagina=dashboard"><i class="bi bi-shield-lock me-2"></i> Dashboard</a>
            <a class="nav-link <?= $pagina_actual === 'instituciones' ? 'active' : '' ?>" href="inicio.php?pagina=instituciones"><i class="bi bi-person-gear me-2"></i> Gestionar instituciones</a>
            <a class="nav-link <?= $pagina_actual === 'administradores' ? 'active' : '' ?>" href="inicio.php?pagina=administradores"><i class="bi bi-person-badge me-2"></i> Gestionar administradores</a>
            <a class="nav-link text-danger mt-auto mb-4" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="main-content">
