<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Redirigir si no es coordinador
if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'Coordinador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

include('../../conexion.php');
/** @var mysqli $conexion */

$id_coordinador = $_SESSION['id_usuario'];

// Obtener carrera del coordinador
$sql_coord = "SELECT id_carrera FROM coordinador WHERE id_usuario = ?";
$stmt_coord = mysqli_prepare($conexion, $sql_coord);
mysqli_stmt_bind_param($stmt_coord, "i", $id_coordinador);
mysqli_stmt_execute($stmt_coord);
$res_coord = mysqli_stmt_get_result($stmt_coord);
$coord_data = mysqli_fetch_assoc($res_coord);
$id_carrera = $coord_data['id_carrera'] ?? 0;

// Consulta de alumnos de la carrera
$sql_alumnos = "SELECT e.id_usuario, e.nombre, e.apellido, e.nivel_curricular, u.correo, u.estado_cuenta,
                       p.estado_practica
                FROM estudiante e
                JOIN usuario u ON e.id_usuario = u.id_usuario
                LEFT JOIN practica p ON e.id_usuario = p.id_estudiante
                WHERE e.id_carrera = ?
                ORDER BY e.apellido ASC";

$stmt_alumnos = mysqli_prepare($conexion, $sql_alumnos);
mysqli_stmt_bind_param($stmt_alumnos, "i", $id_carrera);
mysqli_stmt_execute($stmt_alumnos);
$resultado = mysqli_stmt_get_result($stmt_alumnos);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos - Panel Coordinador</title>
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
            <a class="nav-link active" href="alumnos.php"><i class="bi bi-people me-2"></i> Alumnos</a>
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
        <header class="mb-5">
            <h2 class="mb-1">Listado de Alumnos</h2>
            <p class="text-muted">Gestión y seguimiento de estudiantes de la carrera.</p>
        </header>

        <div class="card card-custom p-4 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Correo</th>
                            <th>Nivel Curricular</th>
                            <th>Estado Práctica</th>
                            <th>Cuenta</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado) > 0): ?>
                            <?php while ($alumno = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></div>
                                        <small class="text-muted">ID: <?php echo $alumno['id_usuario']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                                    <td>Nivel <?php echo $alumno['nivel_curricular']; ?></td>
                                    <td>
                                        <?php 
                                            $estado = $alumno['estado_practica'] ?? 'No iniciada';
                                            $badge_class = 'bg-secondary';
                                            if ($estado === 'En Curso') $badge_class = 'bg-primary';
                                            if ($estado === 'Finalizada') $badge_class = 'bg-success';
                                            if ($estado === 'Postulado') $badge_class = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $alumno['estado_cuenta'] === 'activa' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($alumno['estado_cuenta']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" title="Ver Perfil"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Enviar Mensaje"><i class="bi bi-envelope"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron alumnos para esta carrera.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>