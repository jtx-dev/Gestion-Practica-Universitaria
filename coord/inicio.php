<?php
include('../conexion.php');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Coordinador - Gestión de Prácticas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Panel Coordinador</div>
        </div>
        
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-speedometer2 me-2"></i> Vista Global</a>
            <a class="nav-link" href="alumnos.php"><i class="bi bi-people me-2"></i> Alumnos</a>
            <a class="nav-link" href="ofertas.php"><i class="bi bi-building me-2"></i> Empresas / Ofertas</a>
            <a class="nav-link" href="validacion.php"><i class="bi bi-file-earmark-check me-2"></i> Validaciones</a>
            
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
        <header class="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Resumen de Gestión</h2>
                <p class="text-muted">Control de procesos académicos y vinculación empresarial.</p>
            </div>
            <button class="btn btn-primary"><i class="bi bi-download me-2"></i>Generar Reporte Semanal</button>
        </header>

        <!-- Métricas Rápidas -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white stat-card">
                    <small class="text-muted fw-bold">Alumnos Totales</small>
                    <h3 class="fw-bold mb-0">124</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-warning" style="border-left: 4px solid #ffc107;">
                    <small class="text-muted fw-bold">Pendientes de Práctica</small>
                    <h3 class="fw-bold mb-0">15</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-danger" style="border-left: 4px solid #dc3545;">
                    <small class="text-muted fw-bold">Alertas de Atraso</small>
                    <h3 class="fw-bold mb-0">8</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-success" style="border-left: 4px solid #198754;">
                    <small class="text-muted fw-bold">Convenios Activos</small>
                    <h3 class="fw-bold mb-0">42</h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tabla de Alumnos con Alertas -->
            <div class="col-md-7">
                <div class="card card-custom p-4 bg-white">
                    <h5 class="fw-bold mb-4">Seguimiento de Alumnos (Alertas Críticas)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Estado</th>
                                    <th>Alerta</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger-subtle">
                                    <td>
                                        <div class="fw-bold">Juan Pérez</div>
                                        <small class="text-muted">Ing. Civil Informática</small>
                                    </td>
                                    <td><span class="badge bg-primary">En Práctica</span></td>
                                    <td><span class="text-danger fw-bold small"><i class="bi bi-clock-history"></i> Bitácora Q2 Atrasada</span></td>
                                    <td class="text-end"><button class="btn btn-sm btn-danger">Notificar</button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Sofía Castro</div>
                                        <small class="text-muted">Ing. Civil Informática</small>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">Buscando</span></td>
                                    <td><span class="text-muted small">Sin postulaciones activas</span></td>
                                    <td class="text-end"><button class="btn btn-sm btn-outline-primary">Ver Perfil</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gestión de Empresas / Ofertas -->
            <div class="col-md-5">
                <div class="card card-custom p-4 bg-white h-100">
                    <h5 class="fw-bold mb-4">Estado de Vacantes / Empresas</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold">Tech Solutions A</span>
                            <span class="small text-muted">2/5 Vacantes</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 40%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold">Banco Nacional</span>
                            <span class="small text-muted">4/4 Vacantes</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="p-3 border rounded bg-light">
                        <h6 class="fw-bold small mb-2"><i class="bi bi-info-circle me-2"></i>Solicitud de Nuevo Convenio</h6>
                        <p class="small text-muted mb-3">Empresa "Software Factory" desea inscribirse como centro de práctica.</p>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button class="btn btn-sm btn-outline-secondary">Rechazar</button>
                            <button class="btn btn-sm btn-primary">Revisar Datos</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>