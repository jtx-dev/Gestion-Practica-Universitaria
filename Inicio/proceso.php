<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Cómo funciona? - Gestión de Prácticas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
            --dark-text: #343a40;
        }
        body {
            background-color: var(--secondary-bg);
            color: var(--dark-text);
            min-height: 100vh;
        }
        
        /* Hero sutil */
        .hero-simple {
            padding: 60px 0;
            background-color: white;
            border-bottom: 1px solid #dee2e6;
        }

        /* Tarjetas de pasos con estética similar al login */
        .step-card {
            border: none;
            border-radius: 12px;
            background: white;
            transition: all 0.2s ease;
        }

        .step-number {
            width: 35px;
            height: 35px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        /* Sección de perfiles limpia */
        .profile-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            height: 100%;
            border-left: 5px solid var(--primary-color);
        }
        
        .profile-box.coordinator { border-left-color: #6c757d; }
        .profile-box.company { border-left-color: #198754; }

    </style>
</head>
<body>

    <!-- Navbar -->    <!-- Navbar -->
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


    <!-- Header Principal -->
    <header class="hero-simple text-center">
        <div class="container">
            <h1 class="fw-bold">Flujo del Proceso de Prácticas</h1>
            <p class="text-muted">Guía paso a paso para el uso correcto de la plataforma institucional.</p>
        </div>
    </header>

    <!-- Pasos del Proceso -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 step-card shadow-sm p-4">
                        <div class="step-number">01</div>
                        <h5 class="fw-bold">Postulación y Registro</h5>
                        <p class="text-muted small">El estudiante completa su ficha y selecciona una oferta validada o inscribe una práctica externa para revisión.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 step-card shadow-sm p-4">
                        <div class="step-number">02</div>
                        <h5 class="fw-bold">Validación Académica</h5>
                        <p class="text-muted small">Los coordinadores y tutores verifican que la práctica cumpla con los estándares curriculares requeridos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 step-card shadow-sm p-4">
                        <div class="step-number">03</div>
                        <h5 class="fw-bold">Ejecución y Evaluación</h5>
                        <p class="text-muted small">Seguimiento de horas, carga de informes mensuales y entrega de la evaluación final por parte del supervisor.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfiles de Usuario -->
    <section class="py-5 bg-light border-top">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Roles en la Plataforma</h2>
                <p class="text-muted">Cada usuario cuenta con herramientas específicas según su función.</p>
            </div>

            <div class="row g-4">
                <!-- Estudiante -->
                <div class="col-lg-4">
                    <div class="profile-box shadow-sm">
                        <h4 class="fw-bold">Estudiantes</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">✓ Gestión de documentos y CV.</li>
                            <li class="mb-2">✓ Bitácora de actividades diaria.</li>
                            <li class="mb-2">✓ Seguimiento de estado de aprobación.</li>
                        </ul>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="col-lg-4">
                    <div class="profile-box company shadow-sm">
                        <h4 class="fw-bold">Empresas / Supervisores</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">✓ Publicación de vacantes.</li>
                            <li class="mb-2">✓ Revisión de perfiles de alumnos.</li>
                            <li class="mb-2">✓ Evaluación final del desempeño.</li>
                        </ul>
                    </div>
                </div>

                <!-- Coordinador -->
                <div class="col-lg-4">
                    <div class="profile-box coordinator shadow-sm">
                        <h4 class="fw-bold">Coordinadores</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">✓ Visión completa del dashboard.</li>
                            <li class="mb-2">✓ Gestión de convenios y seguros.</li>
                            <li class="mb-2">✓ Reportes estadísticos en tiempo real.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-white border-top text-center mt-auto">
        <div class="container">
            <span class="text-muted small">© 2026 Gestión de Prácticas - &lt nombre institucional &gt</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>