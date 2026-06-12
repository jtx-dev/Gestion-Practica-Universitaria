<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte - Gestión de Prácticas</title>
    <script src="soporte.js"></script>
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
        
        .support-header {
            background-color: white;
            padding: 60px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-card {
            border: none;
            border-radius: 12px;
            background-color: white;
        }
        .accordion-button:not(.collapsed) {
            background-color: rgba(13, 110, 253, 0.05);
            color: var(--primary-color);
        }
        .contact-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold" href="soporte.php">Soporte</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="iniciar_sesion.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="support-header text-center">
        <div class="container">
            <h1 class="fw-bold">Centro de Ayuda</h1>
            <p class="text-muted">¿Tienes dudas con el proceso de prácticas? Estamos para ayudarte.</p>
        </div>
    </header>

    <main class="container py-5">
        
        <!-- SECCIÓN SUPERIOR: FORMULARIO E INFO CONTACTO -->
        <div class="row g-4 mb-5">
            <!-- Formulario de Contacto -->
            <div class="col-lg-8">
                <div class="card info-card shadow-sm p-4 h-100">
                    <h4 class="fw-bold mb-3">Envíanos un mensaje</h4>
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Asunto</label>
                                <select class="form-select shadow-sm" id="selectAsunto" onchange="toggleOtroAsunto()">
                                    <option value="login" selected>Problemas con el login</option>
                                    <option value="convenio">Duda sobre convenios</option>
                                    <option value="error">Error en la plataforma</option>
                                    <option value="otro">Otro (especificar)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="bloqueOtroAsunto">
                                <label class="form-label small fw-bold">Especifique el asunto</label>
                                <input type="text" class="form-control shadow-sm" placeholder="Ej: Seguro escolar">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control shadow-sm" placeholder="usuario@universidad.cl" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label small fw-bold">Descripción</label>
                                <textarea class="form-control shadow-sm" rows="4" placeholder="Explica tu situación detalladamente..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm px-5">Enviar Ticket</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Canales Directos -->
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-3 h-100">
                    <div class="bg-white p-4 rounded shadow-sm text-center flex-grow-1 d-flex flex-column justify-content-center">
                        <div class="contact-icon">📧</div>
                        <h6 class="fw-bold">Email de Soporte</h6>
                        <p class="small text-muted mb-0">soporte@universidad.cl</p>
                    </div>
                    <div class="bg-white p-4 rounded shadow-sm text-center flex-grow-1 d-flex flex-column justify-content-center">
                        <div class="contact-icon">📍</div>
                        <h6 class="fw-bold">Atención Presencial</h6>
                        <p class="small text-muted mb-0">Edificio Central, Piso 2</p>
                        <p class="x-small text-muted mt-1" style="font-size: 0.7rem;">Lunes a Viernes: 09:00 - 18:00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN INFERIOR: FAQ ÁREA COMPLETA -->
        <div class="row">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Preguntas Frecuentes</h3>
                <div class="accordion shadow-sm" id="accordionFAQ">
                    <div class="accordion-item border-0 mb-2 rounded shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Cómo sé si mi práctica fue aceptada?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-muted">
                                Recibirás un correo electrónico automático una vez que el Coordinador de Carrera valide tu oferta. También puedes revisar el estado en tu panel principal tras iniciar sesión.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2 rounded shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Olvidé mi contraseña institucional
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-muted">
                                Al ser un acceso centralizado, debes recuperar tu clave a través del portal de servicios generales de la Universidad o contactar a la mesa de ayuda técnica de la facultad.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 rounded shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ¿Qué documentos debo subir al finalizar?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                            <div class="accordion-body text-muted">
                                Debes cargar tu informe final de práctica y el certificado de evaluación firmado por tu supervisor directo en la empresa.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 bg-white border-top text-center mt-auto">
        <span class="text-muted small">© 2026 Gestión de Prácticas</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>