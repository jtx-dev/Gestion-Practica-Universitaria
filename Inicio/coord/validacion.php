<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es coordinador
if (!isset($_SESSION['nombre_rol']) || strtolower($_SESSION['nombre_rol']) !== 'coordinador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

include('../../conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Documentos - Panel Coordinador</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/base.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Panel Coordinador</div>
        </div>
        
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link" href="inicio.php"><i class="bi bi-speedometer2 me-2"></i> Vista Global</a>
            <a class="nav-link" href="alumnos.php"><i class="bi bi-people me-2"></i> Alumnos</a>
            <a class="nav-link" href="ofertas.php"><i class="bi bi-building me-2"></i> Empresas / Ofertas</a>
            <a class="nav-link" href="ofertas_aprobadas.php"><i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas</a>
            <a class="nav-link" href="ofertas_rechazadas.php"><i class="bi bi-x-circle me-2"></i> Ofertas Rechazadas</a>
            <a class="nav-link active" href="validacion.php"><i class="bi bi-file-earmark-check me-2"></i> Validaciones</a>
            
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
        <header class="mb-5">
            <h2 class="mb-1 fw-bold">Validación de Documentos</h2>
            <p class="text-muted">Revisa y aprueba la documentación obligatoria de tus estudiantes para autorizar sus prácticas.</p>
        </header>

        <!-- AVISO DE MOCKUP -->
        <div class="alert alert-warning border-0 border-start border-warning border-4 shadow-sm mb-4">
            <i class="bi bi-tools me-2"></i> <strong>Modo Interfaz Gráfica (Mockup):</strong> Esta pantalla es solo visual para estructurar el diseño. Los datos mostrados son ejemplos y los botones no están conectados aún a la base de datos.
        </div>

        <div class="card card-custom p-4 bg-white shadow-sm">
            <h5 class="fw-bold mb-4 text-primary">Estudiantes Pendientes de Revisión</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Estudiante</th>
                            <th class="text-center">Cédula Identidad</th>
                            <th class="text-center">Cert. Alumno Regular</th>
                            <th class="text-center">Curriculum Vitae</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <!-- Ejemplo 1: Todo subido, listo para aprobar -->
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">Valentina Ramos</div>
                                <div class="text-muted small">Nivel 8</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pendiente Revisión</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-success shadow-sm" title="Aprobar documentos"><i class="bi bi-check-lg"></i> Aprobar</button>
                                <button class="btn btn-sm btn-outline-danger shadow-sm" title="Rechazar por documentos erróneos"><i class="bi bi-x-lg"></i> Rechazar</button>
                            </td>
                        </tr>

                        <!-- Ejemplo 2: Le falta un documento -->
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">Matías Silva</div>
                                <div class="text-muted small">Nivel 7</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                <a href="#" class="small text-decoration-none text-muted">Ver PDF</a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger mb-1"><i class="bi bi-exclamation-circle me-1"></i> Falta</span><br>
                                <span class="small text-muted">-</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                <a href="#" class="small text-decoration-none text-muted">Ver PDF</a>
                            </td>
                            <td>
                                <span class="badge bg-danger px-3 py-2 rounded-pill">Incompleto</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-success opacity-50 shadow-sm" style="cursor:not-allowed;" title="No se puede aprobar si falta documentación"><i class="bi bi-check-lg"></i> Aprobar</button>
                                <button class="btn btn-sm btn-outline-secondary shadow-sm" title="Enviar recordatorio automático al alumno"><i class="bi bi-bell"></i> Recordar</button>
                            </td>
                        </tr>

                        <!-- Ejemplo 3: Documentos Aprobados (Historial rápido) -->
                        <tr class="table-light">
                            <td>
                                <div class="fw-bold text-muted">Camila Fernández</div>
                                <div class="text-muted small">Nivel 9</div>
                            </td>
                            <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                            <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                            <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                            <td>
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-shield-check me-1"></i>Validado</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary shadow-sm" title="Ver historial"><i class="bi bi-eye"></i> Detalles</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>