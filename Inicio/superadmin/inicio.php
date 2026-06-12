<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio / SuperAdmin - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-blue: #0d6efd;
            --bg-gray: #f8f9fa;
        }

        body {
            background-color: var(--bg-gray);
        }

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

        .nav-link {
            color: #495057;
            padding: 12px 20px;
            margin: 4px 15px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            background-color: #f1f3f5;
            color: var(--primary-blue);
        }

        .nav-link.active {
            background-color: #e7f1ff;
            color: var(--primary-blue);
        }

        .nav-link.text-danger:hover {
            background-color: #fff5f5;
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Super Administrador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-shield-lock me-2"></i> Control Maestro</a>
            <a class="nav-link" href="#"><i class="bi bi-person-gear me-2"></i> Gestionar Admins</a>
            <a class="nav-link" href="#"><i class="bi bi-server me-2"></i> Estado del Sistema</a>
            <a class="nav-link" href="#"><i class="bi bi-journal-text me-2"></i> Logs de Auditoría</a>
            <a class="nav-link" href="#"><i class="bi bi-database-up me-2"></i> Backups Globales</a>
            <a class="nav-link" href="#"><i class="bi bi-gear-wide-connected me-2"></i> Configuración</a>
            <a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i> Mi Perfil</a>

            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Super Administrador</h2>
                <p class="text-muted">Gestión global de administradores y recursos del sistema.</p>
            </div>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-md-6 col-xl-3">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="text-muted mb-1">Administradores</p>
                                <h2 class="fw-bold mb-0">12</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                        <a href="#" class="btn btn-outline-primary w-100">Gestionar Admins</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="text-muted mb-1">Carga Servidor</p>
                                <h2 class="fw-bold mb-0">24%</h2>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-cpu text-success fs-4"></i>
                            </div>
                        </div>
                        <a href="#" class="btn btn-outline-success w-100">Ver Estado</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="text-muted mb-1">Alertas Globales</p>
                                <h2 class="fw-bold mb-0">0</h2>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                            </div>
                        </div>
                        <a href="#" class="btn btn-outline-warning w-100">Ver Alertas</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="text-muted mb-1">Último Backup</p>
                                <h2 class="fw-bold mb-0">Hoy</h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-database text-danger fs-4"></i>
                            </div>
                        </div>
                        <a href="#" class="btn btn-outline-danger w-100">Gestionar Backups</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Administradores de Sede</h5>
                            <button class="btn btn-sm btn-primary">Registrar Nuevo Admin</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Administrador</th>
                                        <th>Sede / Facultad</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">Carlos Mendoza</div>
                                            <small class="text-muted">c.mendoza@u.cl</small>
                                        </td>
                                        <td>Sede Central - Ingeniería</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">Ana Silva</div>
                                            <small class="text-muted">a.silva@u.cl</small>
                                        </td>
                                        <td>Sede Norte - Salud</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-custom h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Actividad del Sistema</h5>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 border-0 mb-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold small">Backup Automático</h6>
                                    <small class="text-muted">03:00 AM</small>
                                </div>
                                <p class="mb-1 small text-muted">Ejecutado correctamente en AWS S3.</p>
                            </div>
                            <div class="list-group-item px-0 border-0 mb-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold small">Acceso Denegado</h6>
                                    <small class="text-muted">08:45 AM</small>
                                </div>
                                <p class="mb-1 small text-muted">Múltiples intentos desde IP 192.168.1.1</p>
                            </div>
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold small">Actualización de Sistema</h6>
                                    <small class="text-muted">Ayer</small>
                                </div>
                                <p class="mb-1 small text-muted">Parche de seguridad v2.4.1 aplicado.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>