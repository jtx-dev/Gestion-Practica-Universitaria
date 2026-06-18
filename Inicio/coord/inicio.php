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
/** @var mysqli $conexion */

$id_coordinador = $_SESSION['id_usuario'];

// 1. Obtener carrera del coordinador
$sql_coord = "SELECT id_carrera FROM coordinador WHERE id_usuario = ?";
$stmt_coord = mysqli_prepare($conexion, $sql_coord);
mysqli_stmt_bind_param($stmt_coord, "i", $id_coordinador);
mysqli_stmt_execute($stmt_coord);
$res_coord = mysqli_stmt_get_result($stmt_coord);
$coord_data = mysqli_fetch_assoc($res_coord);
$id_carrera = $coord_data['id_carrera'] ?? 0;
mysqli_stmt_close($stmt_coord);

// 2. Obtener métricas reales
// Total alumnos
$res_alumnos = mysqli_query($conexion, "SELECT COUNT(*) as total FROM estudiante WHERE id_carrera = $id_carrera");
$total_alumnos = mysqli_fetch_assoc($res_alumnos)['total'] ?? 0;

// Ofertas pendientes de aprobación
$res_pendientes = mysqli_query($conexion, "SELECT COUNT(*) as total FROM oferta_practica WHERE id_carrera = $id_carrera AND estado_oferta = 'pendiente_aprobacion'");
$ofertas_pendientes = mysqli_fetch_assoc($res_pendientes)['total'] ?? 0;

// Total de vacantes (cupos) disponibles en ofertas activas
$res_vacantes = mysqli_query($conexion, "SELECT SUM(cupos) as total FROM oferta_practica WHERE id_carrera = $id_carrera AND estado_oferta = 'activa'");
$vacantes_disponibles = mysqli_fetch_assoc($res_vacantes)['total'] ?? 0;

// Empresas activas (que tienen ofertas publicadas para esta carrera)
$res_empresas = mysqli_query($conexion, "SELECT COUNT(DISTINCT id_empresa) as total FROM oferta_practica WHERE id_carrera = $id_carrera AND estado_oferta != 'rechazada'");
$empresas_activas = mysqli_fetch_assoc($res_empresas)['total'] ?? 0;

// 3. Últimas postulaciones recibidas
$sql_postulaciones = "SELECT e.nombre, e.apellido, o.titulo as oferta, p.fecha_postulacion, p.estado_postulacion
                      FROM postulacion p
                      INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
                      INNER JOIN oferta_practica o ON p.id_oferta = o.id_oferta
                      WHERE o.id_carrera = $id_carrera
                      ORDER BY p.fecha_postulacion DESC LIMIT 5";
$res_postulaciones = mysqli_query($conexion, $sql_postulaciones);

// 4. Ofertas Activas Disponibles
$sql_ofertas_activas = "SELECT e.nombre_empresa, o.titulo, o.cupos, o.id_oferta 
                        FROM oferta_practica o 
                        INNER JOIN empresa e ON o.id_empresa = e.id_usuario 
                        WHERE o.id_carrera = $id_carrera AND o.estado_oferta = 'activa' 
                        ORDER BY o.fecha_publicacion DESC LIMIT 4";
$res_ofertas_activas = mysqli_query($conexion, $sql_ofertas_activas);

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
    <link href="../../assets/css/base.css" rel="stylesheet">
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
            <a class="nav-link" href="ofertas_aprobadas.php"><i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas</a>
            <a class="nav-link" href="ofertas_rechazadas.php"><i class="bi bi-x-circle me-2"></i> Ofertas Rechazadas</a>
            <a class="nav-link" href="validacion.php"><i class="bi bi-file-earmark-check me-2"></i> Validaciones</a>
            
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <main class="main-content">
        <header class="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1 fw-bold">Resumen de Gestión</h2>
                <p class="text-muted">Control estadístico de tu carrera en tiempo real.</p>
            </div>
            <a href="ofertas.php" class="btn btn-primary shadow-sm"><i class="bi bi-search me-2"></i>Revisar Ofertas Pendientes</a>
        </header>

        <!-- Métricas Rápidas -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white stat-card">
                    <small class="text-muted fw-bold">Total Alumnos</small>
                    <h3 class="fw-bold text-primary mb-0"><?= $total_alumnos ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-warning" style="border-left: 4px solid #ffc107;">
                    <small class="text-muted fw-bold">Ofertas por Revisar</small>
                    <h3 class="fw-bold mb-0"><?= $ofertas_pendientes ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-success" style="border-left: 4px solid #198754;">
                    <small class="text-muted fw-bold">Vacantes Activas</small>
                    <h3 class="fw-bold mb-0"><?= $vacantes_disponibles ?: 0 ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-custom p-3 bg-white border-left border-info" style="border-left: 4px solid #0dcaf0;">
                    <small class="text-muted fw-bold">Empresas Aliadas</small>
                    <h3 class="fw-bold mb-0"><?= $empresas_activas ?></h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Últimas Postulaciones -->
            <div class="col-md-7">
                <div class="card card-custom p-4 bg-white h-100 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i>Últimas Postulaciones Recibidas</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Oferta</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($res_postulaciones) > 0): ?>
                                    <?php while ($post = mysqli_fetch_assoc($res_postulaciones)): 
                                        $estado = strtolower($post['estado_postulacion']);
                                        $badge = match($estado) {
                                            'aceptada' => 'bg-success',
                                            'rechazada' => 'bg-danger',
                                            default => 'bg-warning text-dark'
                                        };
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($post['nombre'] . ' ' . $post['apellido']) ?></td>
                                            <td class="text-muted small text-truncate" style="max-width: 200px;"><?= htmlspecialchars($post['oferta']) ?></td>
                                            <td class="text-muted small"><?= date('d/m/Y', strtotime($post['fecha_postulacion'])) ?></td>
                                            <td class="text-end"><span class="badge <?= $badge ?> rounded-pill"><?= ucfirst($post['estado_postulacion']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay postulaciones recientes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Ofertas Activas Destacadas -->
            <div class="col-md-5">
                <div class="card card-custom p-4 bg-white h-100 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-briefcase text-success me-2"></i>Ofertas Activas Recientes</h5>
                        <a href="ofertas_aprobadas.php" class="small text-decoration-none">Ver todas</a>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <?php if (mysqli_num_rows($res_ofertas_activas) > 0): ?>
                            <?php while ($oferta = mysqli_fetch_assoc($res_ofertas_activas)): ?>
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($oferta['titulo']) ?></h6>
                                            <div class="small text-muted mb-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($oferta['nombre_empresa']) ?></div>
                                        </div>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 border border-success border-opacity-25">
                                            <?= $oferta['cupos'] ?> Cupo(s)
                                        </span>
                                    </div>
                                    <a href="ofertas_aprobadas.php" class="btn btn-outline-secondary btn-sm" style="font-size: 0.75rem;">Ver postulantes</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-light text-center border p-4 mt-3">
                                <i class="bi bi-inbox text-muted fs-3 d-block mb-2"></i>
                                <span class="text-muted small">Aún no hay ofertas activas. Revisa tus ofertas pendientes.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>