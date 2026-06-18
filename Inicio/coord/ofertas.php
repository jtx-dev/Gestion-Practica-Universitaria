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

/* APROBAR OFERTA */
if (isset($_GET['aprobar'])) {

    $id_oferta = $_GET['aprobar'];

    $sql = "UPDATE oferta_practica
            SET estado_oferta = 'activa'
            WHERE id_oferta = '$id_oferta'";

    mysqli_query($conexion, $sql);

    header("Location: ofertas.php");
    exit();
}

/* RECHAZAR OFERTA */
if (isset($_GET['rechazar'])) {

    $id_oferta = $_GET['rechazar'];

    $sql = "UPDATE oferta_practica
            SET estado_oferta = 'rechazada'
            WHERE id_oferta = '$id_oferta'";

    mysqli_query($conexion, $sql);

    header("Location: ofertas.php");
    exit();
}

/* CONSULTAR OFERTAS PENDIENTES */
$sql_ofertas = "SELECT 
                    o.id_oferta,
                    o.titulo,
                    o.descripcion,
                    o.requisitos,
                    o.cupos,
                    o.duracion_meses,
                    o.estado_oferta,
                    o.fecha_publicacion,
                    o.fecha_cierre,
                    e.nombre_empresa,
                    e.rut_empresa,
                    c.nombre_carrera
                FROM oferta_practica o
                INNER JOIN empresa e ON o.id_empresa = e.id_usuario
                INNER JOIN carrera c ON o.id_carrera = c.id_carrera
                WHERE o.estado_oferta = 'pendiente_aprobacion'
                ORDER BY o.fecha_publicacion DESC";

$resultado = mysqli_query($conexion, $sql_ofertas);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas / Ofertas - Coordinador</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/base.css">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">
                Panel Coordinador
            </div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link" href="inicio.php">
                <i class="bi bi-speedometer2 me-2"></i> Vista Global
            </a>

            <a class="nav-link" href="alumnos.php">
                <i class="bi bi-people me-2"></i> Alumnos
            </a>

            <a class="nav-link active" href="ofertas.php">
                <i class="bi bi-building me-2"></i> Empresas / Ofertas
            </a>

            <a class="nav-link" href="ofertas_aprobadas.php">
                <i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas
            </a>

            <a class="nav-link" href="ofertas_rechazadas.php">
                <i class="bi bi-x-circle me-2"></i> Ofertas Rechazadas
            </a>

            <a class="nav-link" href="validacion.php">
                <i class="bi bi-file-earmark-check me-2"></i> Validaciones
            </a>

            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <!-- Contenido principal -->
    <main class="main-content">

        <header class="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Gestión de Empresas / Ofertas</h2>
                <p class="text-muted">
                    Revisión de ofertas de práctica enviadas por empresas.
                </p>
            </div>

        </header>

        <div class="card card-custom p-4 bg-white">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Ofertas Pendientes de Aprobación</h4>
                    <p class="text-muted mb-0 small">
                        El coordinador debe aprobar o rechazar cada oferta antes de que sea visible para los estudiantes.
                    </p>
                </div>
            </div>

            <?php if (mysqli_num_rows($resultado) > 0) { ?>

                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                    <div class="offer-card p-4 mb-3">

                        <div class="d-flex justify-content-between align-items-start">

                            <div class="pe-4">

                                <h5 class="fw-bold mb-1">
                                    <?php echo $fila['titulo']; ?>
                                </h5>

                                <div class="mb-2">
                                    <span class="badge bg-primary">
                                        <?php echo $fila['nombre_carrera']; ?>
                                    </span>

                                    <span class="badge bg-warning text-dark">
                                        <?php echo $fila['estado_oferta']; ?>
                                    </span>
                                </div>

                                <p class="text-muted small mb-2">
                                    <?php echo $fila['descripcion']; ?>
                                </p>

                                <p class="small mb-2">
                                    <strong>Requisitos:</strong>
                                    <?php echo $fila['requisitos']; ?>
                                </p>

                                <div class="small text-muted">
                                    <i class="bi bi-building me-1"></i>
                                    <strong>Empresa:</strong>
                                    <?php echo $fila['nombre_empresa']; ?>

                                    <span class="mx-2">|</span>

                                    <strong>RUT:</strong>
                                    <?php echo $fila['rut_empresa']; ?>
                                </div>

                                <div class="small text-muted mt-1">
                                    <i class="bi bi-people me-1"></i>
                                    <strong>Cupos:</strong>
                                    <?php echo $fila['cupos']; ?>

                                    <span class="mx-2">|</span>

                                    <i class="bi bi-calendar-event me-1"></i>
                                    <strong>Duración:</strong>
                                    <?php echo $fila['duracion_meses']; ?> meses

                                    <span class="mx-2">|</span>

                                    <strong>Cierre:</strong>
                                    <?php echo $fila['fecha_cierre']; ?>
                                </div>

                            </div>

                            <div class="text-end" style="min-width: 150px;">

                                <a href="ofertas.php?aprobar=<?php echo $fila['id_oferta']; ?>"
                                   class="btn btn-success btn-sm w-100 mb-2"
                                   onclick="return confirm('¿Deseas aprobar esta oferta?');">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Aprobar
                                </a>

                                <a href="ofertas.php?rechazar=<?php echo $fila['id_oferta']; ?>"
                                   class="btn btn-outline-danger btn-sm w-100"
                                   onclick="return confirm('¿Deseas rechazar esta oferta?');">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Rechazar
                                </a>

                            </div>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    No existen ofertas pendientes de aprobación.
                </div>

            <?php } ?>

        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>