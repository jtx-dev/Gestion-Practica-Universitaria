<?php
include('../conexion.php');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio / Dashboard - Gestión de Prácticas</title>
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

        .requirement-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Administrador</div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link active" href="inicio.php"><i class="bi bi-speedometer2 me-2"></i> Inicio - Dashboard</a>
            <a class="nav-link" href="mantenedores_admin.php"><i class="bi bi-flag me-2"></i> Gestionar Recursos</a>
            <a class="nav-link" href="usuarios.php"><i class="bi bi-people me-2"></i> Usuarios</a>
            <a class="nav-link" href="empresas.php"><i class="bi bi-buildings me-2"></i></i> Empresas</a>
            <a class="nav-link" href="carreras.php"><i class="bi bi-backpack3 me-2"></i></i> Carreras</a>
            <a class="nav-link" href="roles.php"><i class="bi bi-card-checklist me-2"></i> Roles</a>
            <a class="nav-link" href="respaldos.php"><i class="bi bi-database me-2"></i></i> Respaldos</a>
            <a class="nav-link" href="configuracion.php"><i class="bi bi-gear me-2"></i></i> Configuración</a>
            <a class="nav-link" href="perfil.php"><i class="bi bi-person-circle me-2"></i></i> Mi Perfil</a>

            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Administrador</h2>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="text-muted mb-1">Estudiantes en práctica</p>
                            <h2 class="fw-bold mb-0">45</h2>
                        </div>

                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-mortarboard text-primary fs-4"></i>
                        </div>
                    </div>

                    <a href="#" class="btn btn-outline-primary w-100">
                        Ver estudiantes
                    </a>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="text-muted mb-1">Ofertas activas</p>
                            <h2 class="fw-bold mb-0">10</h2>
                        </div>

                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-briefcase text-success fs-4"></i>
                        </div>
                    </div>

                    <a href="ofertas.php" class="btn btn-outline-success w-100">
                        Ver ofertas
                    </a>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="text-muted mb-1">Empresas registradas</p>
                            <h2 class="fw-bold mb-0">28</h2>
                        </div>

                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-buildings text-warning fs-4"></i>
                        </div>
                    </div>

                    <a href="empresas.php" class="btn btn-outline-warning w-100">
                        Ver empresas
                    </a>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="text-muted mb-1">Usuarios activos</p>
                            <h2 class="fw-bold mb-0">132</h2>
                        </div>

                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people text-danger fs-4"></i>
                        </div>
                    </div>

                    <a href="usuarios.php" class="btn btn-outline-danger w-100">
                        Gestionar usuarios
                    </a>

                </div>
            </div>
        </div>

    </div>

    <!-- PANEL INFERIOR -->
    <div class="row g-4">

        <!-- ACTIVIDAD DEL SISTEMA -->
        <div class="col-lg-8">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Actividad reciente</h5>
                            <p class="text-muted mb-0">
                                Últimos movimientos registrados en el sistema.
                            </p>
                        </div>

                        <button class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            Actualizar
                        </button>
                    </div>

                    <div class="list-group list-group-flush">

                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        Nueva oferta registrada
                                    </h6>
                                    <small class="text-muted">
                                        Empresa "Tech Solutions SPA"
                                    </small>
                                </div>

                                <small class="text-muted">
                                    Hace 5 min
                                </small>
                            </div>
                        </div>

                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        Usuario actualizado
                                    </h6>
                                    <small class="text-muted">
                                        Cambio de permisos de coordinador
                                    </small>
                                </div>

                                <small class="text-muted">
                                    Hace 20 min
                                </small>
                            </div>
                        </div>

                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        Respaldo automático completado
                                    </h6>
                                    <small class="text-muted">
                                        Backup nocturno del sistema
                                    </small>
                                </div>

                                <small class="text-muted">
                                    00:15 AM
                                </small>
                            </div>
                        </div>

                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">
                                        Nueva empresa registrada
                                    </h6>
                                    <small class="text-muted">
                                        Empresa pendiente de revisión
                                    </small>
                                </div>

                                <small class="text-muted">
                                    Hace 1 hora
                                </small>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS -->
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-body">

                    <h5 class="fw-bold mb-1">Accesos rápidos</h5>

                    <p class="text-muted mb-4">
                        Funciones principales del administrador.
                    </p>

                    <div class="d-grid gap-3">

                        <a href="#" class="btn btn-outline-primary text-start">
                            <i class="bi bi-person-plus me-2"></i>
                            Registrar usuario
                        </a>

                        <a href="#" class="btn btn-outline-success text-start">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Asignar coordinadores
                        </a>

                        <a href="#" class="btn btn-outline-warning text-start">
                            <i class="bi bi-buildings me-2"></i>
                            Gestionar empresas
                        </a>

                        <a href="#" class="btn btn-outline-danger text-start">
                            <i class="bi bi-database-fill-gear me-2"></i>
                            Respaldos del sistema
                        </a>

                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
</body>

</html>