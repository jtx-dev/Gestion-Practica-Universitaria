<?php
include('../../conexion.php');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Estudiante - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-blue: #0d6efd;
            --bg-gray: #f8f9fa;
        }

        body { background-color: var(--bg-gray); }

        /* Sidebar Fijo */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: white;
            border-right: 1px solid #dee2e6;
            z-index: 1000;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
        }

        /* Estilo de los links del menú */
        .nav-link {
            color: #495057;
            padding: 12px 20px;
            margin: 4px 15px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .nav-link:hover { background-color: #f1f3f5; color: var(--primary-blue); }
        .nav-link.active { background-color: #e7f1ff; color: var(--primary-blue); }
        .nav-link.text-danger:hover { background-color: #fff5f5; }

        /* Estilo de las tarjetas (Cards) */
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .progress { height: 10px; border-radius: 10px; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">PRACTICAS ALUMNOS</div>
        </div>
        
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-grid-1x2-fill me-2"></i> Inicio / Ofertas</a>
            <a class="nav-link" href="documentos.php"><i class="bi bi-file-earmark-arrow-up me-2"></i> Mis Documentos</a>
            <a class="nav-link" href="postulaciones.php"><i class="bi bi-briefcase me-2"></i> Postulaciones</a>
            <a class="nav-link" href="perfil.php"><i class="bi bi-gear me-2"></i> Mi Perfil</a>
            
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-1">Panel Estudiante</h2>
                <p class="text-muted">Hola Juan Pérez, estas son las novedades de hoy.</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="mb-0 fw-bold">Juan Pérez</p>
                    <small class="text-muted">Ing. Civil Informática</small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-12">
                <div class="card card-custom bg-white p-4">
                    <h5 class="fw-bold mb-4">Estado de mi Proceso</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-secondary mb-2">Carga de Documentos</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <small class="text-success mt-1 d-inline-block">Completado <i class="bi bi-check-circle"></i></small>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-secondary mb-2">Validación de Empresa</label>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 40%"></div>
                            </div>
                            <small class="text-muted mt-1 d-inline-block">En proceso (40%)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-custom bg-white">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">Ofertas Disponibles</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Empresa / Oferta</th>
                                        <th>Modalidad</th>
                                        <th>Remuneración</th>
                                        <th>Estado</th>
                                        <th class="pe-4 text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">Desarrollador Web Jr.</div>
                                            <small class="text-muted">Tech Solutions A</small>
                                        </td>
                                        <td><span class="badge bg-light text-dark">Remoto</span></td>
                                        <td class="text-success fw-bold">$250.000</td>
                                        <td><span class="status-badge bg-success text-white">Activa</span></td>
                                        <td class="pe-4 text-end"><button class="btn btn-sm btn-outline-primary">Ver Detalles</button></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">Analista de Sistemas</div>
                                            <small class="text-muted">Banco Nacional</small>
                                        </td>
                                        <td><span class="badge bg-light text-dark">Híbrido</span></td>
                                        <td class="text-success fw-bold">$300.000</td>
                                        <td><span class="status-badge bg-success text-white">Activa</span></td>
                                        <td class="pe-4 text-end"><button class="btn btn-sm btn-outline-primary">Ver Detalles</button></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">QA Engineer Trainee</div>
                                            <small class="text-muted">Software Factory</small>
                                        </td>
                                        <td><span class="badge bg-light text-dark">Presencial</span></td>
                                        <td class="text-success fw-bold">$200.000</td>
                                        <td><span class="status-badge bg-success text-white">Activa</span></td>
                                        <td class="pe-4 text-end"><button class="btn btn-sm btn-outline-primary">Ver Detalles</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center border-0 py-3">
                        <a href="#" class="text-decoration-none small fw-bold">Ver todas las ofertas disponibles</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> <tr>
                                    