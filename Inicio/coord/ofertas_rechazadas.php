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

/* MANEJAR REVERSIÓN A PENDIENTE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'revertir') {
    $id_revertir = (int)$_POST['id_oferta'];
    $sql_revertir = "UPDATE oferta_practica SET estado_oferta = 'pendiente_aprobacion' WHERE id_oferta = ?";
    $stmt_rev = mysqli_prepare($conexion, $sql_revertir);
    mysqli_stmt_bind_param($stmt_rev, "i", $id_revertir);
    mysqli_stmt_execute($stmt_rev);
    mysqli_stmt_close($stmt_rev);
    $mensaje = "Oferta devuelta a estado pendiente de revisión.";
}

/** @var mysqli $conexion */
$consulta = "SELECT
                o.id_oferta,
                o.titulo,
                o.descripcion,
                o.requisitos,
                o.cupos,
                o.duracion_meses,
                o.fecha_publicacion,
                e.nombre_empresa,
                e.rut_empresa,
                c.nombre_carrera
            FROM oferta_practica o
            INNER JOIN empresa e ON o.id_empresa = e.id_usuario
            INNER JOIN carrera c ON o.id_carrera = c.id_carrera
            WHERE o.estado_oferta = 'rechazada'
            ORDER BY o.fecha_publicacion DESC";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas Rechazadas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/base.css" rel="stylesheet">
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

            <a class="nav-link" href="ofertas.php">
                <i class="bi bi-building me-2"></i> Empresas / Ofertas
            </a>

            <a class="nav-link" href="ofertas_aprobadas.php">
                <i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas
            </a>

            <a class="nav-link active" href="ofertas_rechazadas.php">
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

        <header class="mb-5">
            <h2 class="fw-bold mb-1">Ofertas Rechazadas</h2>
            <p class="text-muted">Historial de ofertas rechazadas por el coordinador.</p>
        </header>

        <div class="card card-custom p-4 bg-white">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Listado de Ofertas Rechazadas</h4>
            </div>

            <?php if (isset($mensaje)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php if (mysqli_num_rows($resultado) > 0) { ?>

                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                    <div class="offer-card p-4 mb-3 border rounded shadow-sm bg-white">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-danger mb-0">
                                <?php echo $fila['titulo']; ?>
                            </h5>

                            <!-- Botón para revertir -->
                            <form method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas enviar esta oferta de vuelta a revisión?');">
                                <input type="hidden" name="accion" value="revertir">
                                <input type="hidden" name="id_oferta" value="<?php echo $fila['id_oferta']; ?>">
                                <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Deshacer Rechazo
                                </button>
                            </form>
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-secondary me-1">
                                <?php echo $fila['nombre_carrera']; ?>
                            </span>

                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle-fill me-1"></i>Rechazada
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
                            <strong>Empresa:</strong>
                            <?php echo $fila['nombre_empresa']; ?>

                            <span class="mx-2">|</span>

                            <strong>RUT:</strong>
                            <?php echo $fila['rut_empresa']; ?>
                        </div>

                        <div class="small text-muted mt-1">
                            <strong>Cupos:</strong>
                            <?php echo $fila['cupos']; ?>

                            <span class="mx-2">|</span>

                            <strong>Duración:</strong>
                            <?php echo $fila['duracion_meses']; ?> meses

                            <span class="mx-2">|</span>

                            <strong>Fecha publicación:</strong>
                            <?php echo $fila['fecha_publicacion']; ?>
                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    No hay ofertas rechazadas.
                </div>

            <?php } ?>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>