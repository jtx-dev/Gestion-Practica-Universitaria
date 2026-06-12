<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Prácticas Universitarias</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
        }
        .navbar-brand img { max-height: 50px; }
        .hero-section { padding: 80px 0; background-color: var(--secondary-bg); }
        .card-role { transition: transform 0.3s; cursor: pointer; }
        .card-role:hover { transform: translateY(-10px); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="inicio.php">
                <!-- Espacio para el LOGO -->
                <div class="bg-secondary text-white p-2 d-inline-block rounded" style="width: 120px; text-align: center;">TU LOGO</div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="proceso.php">¿Cómo funciona?</a></li>
                    <li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link" href="soporte.php">Soporte</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="iniciar_sesion.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Portal de Prácticas Profesionales</h1>
            <p class="lead text-muted">La plataforma centralizada para conectar el mundo académico con el profesional.</p>
        </div>
    </header>

            </div>
        </div>
    </section>  

    <hr class="my-5">