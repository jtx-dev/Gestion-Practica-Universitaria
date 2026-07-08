<?php
$pagina_actual = isset($pagina_actual) ? $pagina_actual : 'inicio';
$nav_items = [
    'inicio' => ['icono' => 'bi-speedometer2', 'label' => 'Inicio'],
    'estudiantes' => ['icono' => 'bi-people-fill', 'label' => 'Progreso Estudiantes'],
    'indicadores' => ['icono' => 'bi-bar-chart-fill', 'label' => 'Indicadores'],
    'evaluacion' => ['icono' => 'bi-clipboard2-check-fill', 'label' => 'Evaluaciones'],
    'reportes' => ['icono' => 'bi-file-earmark-arrow-down-fill', 'label' => 'Exportar Reportes'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Directivo — SGPPE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/base.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Estilos propios del directivo que complementan base.css */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            height: 100%;
            border: 1px solid #e4e9f0;
            border-left: 4px solid var(--primary-blue);
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a3a5c;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: .75rem;
            color: #6c757d;
            margin-top: .2rem;
        }

        .badge-estado {
            font-size: .72rem;
            padding: .35em .75em;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-asignado {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-en-curso {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-finalizada {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-evaluado {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-postulado {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-cancelada {
            background: #fee2e2;
            color: #991b1b;
        }

        .progress {
            height: 6px;
            border-radius: 4px;
        }

        .card-section {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e4e9f0;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-section .card-header-custom {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e4e9f0;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .card-section .card-header-custom h6 {
            margin: 0;
            font-weight: 700;
            color: #1a3a5c;
            font-size: .9rem;
        }

        .table-custom th {
            background: #f8fafc;
            color: #1a3a5c;
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
    </style>
</head>

<body>

    <!-- Sidebar igual al de los compañeros -->
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Panel Directivo</div>
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

    <!-- Contenido principal -->
    <main class="main-content">