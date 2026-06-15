<?php
include('../conexion.php');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - Gestión de Prácticas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
        }
        body {
            background-color: var(--secondary-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand img { max-height: 50px; }
        
        /* Estilos específicos */
        .header-title {
            background-color: white;
            padding: 60px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .requirement-card {
            border: none;
            border-radius: 12px;
            background-color: white;
            height: 100%;
        }
        .offer-section {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 40px;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>

    <!-- Navbar (Mantenido exactamente igual) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="inicio.php">
                <div class="bg-secondary text-white p-2 d-inline-block rounded" style="width: 120px; text-align: center;">TU LOGO</div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="proceso.php">¿Cómo funciona?</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link" href="soporte.php">Soporte</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="iniciar_sesion.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Informativo -->
    <header class="header-title text-center">
        <div class="container">
            <h1 class="fw-bold">Portal de Alianzas Empresariales</h1>
            <p class="text-muted mx-auto" style="max-width: 800px;">
                Colaboramos con organizaciones comprometidas con la formación de futuros profesionales. 
                Toda oferta de práctica está sujeta a validación académica.
            </p>
        </div>
    </header>

    <!-- Rigurosidades y Requisitos -->
    <section class="py-5">
        <div class="container">
            <h3 class="fw-bold mb-4 text-center">Requisitos para Ofertar</h3>
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Validación Legal</h5>
                        <p class="small text-muted mb-0">La empresa debe contar con RUT vigente y representante legal para la firma de convenios institucionales.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Plan de Trabajo</h5>
                        <p class="small text-muted mb-0">Es obligatorio definir un tutor interno y un plan de actividades que aporte al perfil de egreso del alumno.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Seguro Escolar</h5>
                        <p class="small text-muted mb-0">La institución cubre el seguro, pero la empresa debe garantizar condiciones de seguridad e higiene adecuadas.</p>
                    </div>
                </div>
            </div>

            <!-- Formulario de Oferta Rápida -->
            <div class="offer-section shadow-sm border">
                <h3 class="fw-bold mb-4">Enviar Oferta de Práctica</h3>
                <p class="text-muted small mb-4">* Los campos son obligatorios. Al enviar, la coordinación revisará la propuesta en un plazo de 48 hrs.</p>
                
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control shadow-sm" placeholder="Ej: Tech Solutions SpA" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RUT Empresa</label>
                            <input type="text" class="form-control shadow-sm" placeholder="12.345.678-9" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Título de la Práctica</label>
                            <input type="text" class="form-control shadow-sm" placeholder="Ej: Practicante de Desarrollo Web Laravel" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción de Actividades</label>
                            <textarea class="form-control shadow-sm" rows="4" placeholder="Detalle las tareas que realizará el alumno..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera Solicitada</label>
                            <select class="form-select shadow-sm">
                                <option selected>Seleccione una opción...</option>
                                <option>Ingeniería Civil Informática</option>
                                <option>Ingeniería Comercial</option>
                                <option>Psicología</option>
                                <option>Otras carreras</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo de Contacto (RRHH)</label>
                            <input type="email" class="form-control shadow-sm" placeholder="contacto@empresa.cl" required>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small" for="terms">
                                    Confirmo que la empresa cuenta con un supervisor disponible para guiar al estudiante.
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">Enviar Propuesta para Revisión</button>
                        </div>
                    </div>
                </form>
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