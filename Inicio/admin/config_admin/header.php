<?php
$pagina_actual = isset($pagina_actual) ? $pagina_actual : 'dashboard';
$nav_items = [
    'dashboard'     => ['icono' => 'bi-speedometer2',   'label' => 'Dashboard',       'sub' => 'Vista general de métricas.'],
    'usuarios'      => ['icono' => 'bi-people',         'label' => 'Usuarios',        'sub' => 'Gestión de cuentas y accesos.'],
    'roles'         => ['icono' => 'bi-card-checklist', 'label' => 'Roles',           'sub' => 'Catálogo de roles en la plataforma.'],
    'carreras'      => ['icono' => 'bi-backpack3',      'label' => 'Carreras',        'sub' => 'Administración de carreras.'],
    'competencias'  => ['icono' => 'bi-tags',           'label' => 'Competencias',    'sub' => 'Competencias y habilidades.'],
    'asignaciones'  => ['icono' => 'bi-diagram-3',      'label' => 'Asignaciones',    'sub' => 'Relaciones entre estudiantes y directivos/coordinadores.'],
    'logs'          => ['icono' => 'bi-journal-text',   'label' => 'Auditoría',       'sub' => 'Registro de auditoría y actividades.'],
    'configuracion' => ['icono' => 'bi-gear',           'label' => 'Configuración',   'sub' => 'Ajustes globales de la institución.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= admin_e($nav_items[$pagina_actual]['label']) ?> - SGPPE</title>
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
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Administrador</div>
        </div>
        <nav class="nav flex-column flex-grow-1">
            <?php foreach ($nav_items as $key => $item): ?>
                <a class="nav-link <?= $pagina_actual === $key ? 'active' : '' ?>" href="inicio.php?pagina=<?= $key ?>">
                    <i class="bi <?= $item['icono'] ?> me-2"></i> <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
            <a class="nav-link text-danger mt-auto mb-4" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-1"><?= admin_e($nav_items[$pagina_actual]['label']) ?></h2>
                <p class="text-muted mb-0"><?= admin_e($nav_items[$pagina_actual]['sub']) ?></p>
            </div>
        </div>
