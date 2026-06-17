<?php
$pagina_actual = isset($pagina_actual) ? $pagina_actual : 'inicio';
$nav_items = [
    'inicio'      => ['icono' => 'bi-speedometer2',     'label' => 'Inicio'],
    'estudiantes' => ['icono' => 'bi-people-fill',      'label' => 'Progreso Estudiantes'],
    'indicadores' => ['icono' => 'bi-bar-chart-fill',   'label' => 'Indicadores'],
    'evaluacion'  => ['icono' => 'bi-clipboard2-check-fill', 'label' => 'Evaluaciones'],
    'reportes'    => ['icono' => 'bi-file-earmark-arrow-down-fill', 'label' => 'Exportar Reportes'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Directivo — SGPPE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --color-primary: #1a3a5c;
            --color-accent:  #2e86de;
            --color-light:   #f0f4f8;
            --color-success: #27ae60;
            --color-warning: #e67e22;
            --color-danger:  #e74c3c;
            --color-muted:   #6c757d;
        }

        body {
            background: var(--color-light);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--color-primary);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease;
        }

        #sidebar .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }
        #sidebar .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: .95rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        #sidebar .sidebar-brand small {
            color: rgba(255,255,255,.5);
            font-size: .75rem;
        }

        #sidebar .nav-link {
            color: rgba(255,255,255,.7);
            padding: .7rem 1.25rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: .7rem;
            font-size: .88rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            border-left: 3px solid transparent;
        }
        #sidebar .nav-link:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }
        #sidebar .nav-link.active {
            background: rgba(46,134,222,.2);
            color: #fff;
            border-left-color: var(--color-accent);
        }
        #sidebar .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.12);
        }
        #sidebar .sidebar-footer .user-info small {
            color: rgba(255,255,255,.5);
            font-size: .72rem;
        }
        #sidebar .sidebar-footer .user-info span {
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
            display: block;
        }

        /* ── Main ── */
        #main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #topbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }
        #topbar .page-title {
            font-weight: 700;
            color: var(--color-primary);
            font-size: 1rem;
            margin: 0;
        }
        #topbar .badge-rol {
            background: var(--color-accent);
            color: #fff;
            font-size: .7rem;
            padding: .3em .7em;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: .03em;
        }

        #content {
            padding: 1.75rem 1.5rem;
            flex: 1;
        }

        /* ── Cards ── */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            border: 1px solid #e4e9f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--color-primary);
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: .75rem;
            color: var(--color-muted);
            margin-top: .2rem;
        }

        .card-section {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e4e9f0;
            overflow: hidden;
        }
        .card-section .card-header-custom {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e4e9f0;
            display: flex; align-items: center; gap: .6rem;
        }
        .card-section .card-header-custom h6 {
            margin: 0;
            font-weight: 700;
            color: var(--color-primary);
            font-size: .9rem;
        }

        /* Badges de estado */
        .badge-estado {
            font-size: .72rem;
            padding: .35em .75em;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-asignado    { background: #dbeafe; color: #1e40af; }
        .badge-en-curso    { background: #d1fae5; color: #065f46; }
        .badge-finalizada  { background: #f3f4f6; color: #374151; }
        .badge-evaluado    { background: #ede9fe; color: #5b21b6; }
        .badge-postulado   { background: #fef3c7; color: #92400e; }
        .badge-cancelada   { background: #fee2e2; color: #991b1b; }

        /* Progress bar */
        .progress { height: 6px; border-radius: 4px; }

        /* Tabla */
        .table-custom th {
            background: #f8fafc;
            color: var(--color-primary);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 2px solid #e4e9f0;
        }
        .table-custom td {
            font-size: .85rem;
            vertical-align: middle;
        }

        /* Botón sidebar mobile */
        #sidebarToggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: var(--color-primary);
        }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
            #sidebarToggle { display: block; }
        }
    </style>
</head>
<body>

<!-- ── Sidebar ── -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <h5><i class="bi bi-mortarboard-fill me-2"></i>SGPPE</h5>
        <small>Panel del Directivo</small>
    </div>

    <ul class="nav flex-column mt-2">
        <?php foreach ($nav_items as $key => $item): ?>
        <li class="nav-item">
            <a class="nav-link <?= $pagina_actual === $key ? 'active' : '' ?>"
               href="inicio.php?pagina=<?= $key ?>">
                <i class="bi <?= $item['icono'] ?>"></i>
                <?= $item['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <small>Sesión iniciada como</small>
            <span><?= htmlspecialchars($nombre_directivo) ?></span>
        </div>
        <a href="../logout.php" class="btn btn-sm btn-outline-light mt-2 w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</nav>

<!-- ── Main wrapper ── -->
<div id="main">
    <div id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="sidebarToggle"><i class="bi bi-list"></i></button>
            <h1 class="page-title">
                <i class="bi <?= $nav_items[$pagina_actual]['icono'] ?> me-2 text-primary"></i>
                <?= $nav_items[$pagina_actual]['label'] ?>
            </h1>
        </div>
        <span class="badge-rol"><i class="bi bi-shield-fill me-1"></i>Directivo</span>
    </div>

    <div id="content">
