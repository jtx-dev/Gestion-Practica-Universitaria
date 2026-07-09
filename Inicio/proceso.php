<?php
include('../conexion.php');
include('componentes/navbar-inicio.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Cómo funciona? - Gestión de Prácticas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/base.css">
</head>

<body>
    <?php
    renderNavbar();
    ?>
    <!-- Header Principal -->
    <header class="hero-simple text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Flujo del Proceso de Prácticas</h1>
            <p class="text-muted">Guía paso a paso para el uso correcto de la plataforma institucional.</p>
        </div>
    </header>

    <!-- Pasos del Proceso -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-hover h-100 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-inline-flex bg-primary text-white py-1 px-2 rounded-circle">
                                01
                            </div>
                            <span class="fs-1 material-symbols-outlined">
                                assignment
                            </span>
                        </div>

                        <h5 class="fw-bold">
                            Postulación y Registro</h5>
                        <p class="text-muted small">El estudiante completa su ficha y selecciona una oferta validada o
                            inscribe una práctica externa para revisión.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover h-100 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-inline-flex bg-primary text-white py-1 px-2 rounded-circle">
                                02
                            </div>
                            <span class="fs-1 material-symbols-outlined">
                                school
                            </span>
                        </div>
                        <h5 class="fw-bold">Validación Académica</h5>
                        <p class="text-muted small">Los coordinadores y tutores verifican que la práctica cumpla con los
                            estándares curriculares requeridos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover h-100 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-inline-flex bg-primary text-white py-1 px-2 rounded-circle">
                                03
                            </div>
                            <span class="fs-1 material-symbols-outlined">
                                planner_review
                            </span>
                        </div>
                        <h5 class="fw-bold">Ejecución y Evaluación</h5>
                        <p class="text-muted small">Seguimiento de horas, carga de informes mensuales y entrega de la
                            evaluación final por parte del supervisor.</p>
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
                <div class="col-lg-3">
                    <div class="card p-4 h-700 shadow-sm">
                        <h4 class="fw-bold text-center">Estudiantes</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Gestión de documentos y CV.</li>
                            <li class="mb-2">Bitácora de actividades diaria.</li>
                            <li class="mb-2">Seguimiento de estado de aprobación.</li>
                        </ul>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="col-lg-3">
                    <div class="card p-4 h-700 shadow-sm">
                        <h4 class="fw-bold text-center">Empresas / Supervisores</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Publicación de vacantes.</li>
                            <li class="mb-2">Revisión de perfiles de alumnos.</li>
                            <li class="mb-2">Evaluación final del desempeño.</li>
                        </ul>
                    </div>
                </div>

                <!-- Coordinador -->
                <div class="col-lg-3">
                    <div class="card p-4 h-700 shadow-sm">
                        <h4 class="fw-bold text-center">Coordinadores</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Visión completa del dashboard.</li>
                            <li class="mb-2">Gestión de convenios y seguros.</li>
                            <li class="mb-2">Reportes estadísticos en tiempo real.</li>
                        </ul>
                    </div>
                </div>
                <!-- Director -->
                <div class="col-lg-3">
                    <div class="card p-4 h-700 shadow-sm">
                        <h4 class="fw-bold text-center">Directores</h4>
                        <hr class="my-3 opacity-10">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">Visión estratégica del programa.</li>
                            <li class="mb-2">Aprobación de convenios y políticas.</li>
                            <li class="mb-2">Evaluación del programa.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-white border-top text-center mt-auto">
        <div class="container">
            <span class="text-muted small">© 2026 Gestión de Prácticas</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>