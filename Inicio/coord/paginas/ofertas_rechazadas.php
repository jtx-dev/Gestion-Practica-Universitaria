<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php

/* MANEJAR REVERSIÓN A PENDIENTE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'revertir') {
    $id_revertir = (int)$_POST['id_oferta'];
    
    // Asegurar que la oferta pertenezca a la carrera del coordinador
    $sql_revertir = "UPDATE oferta_practica SET estado_oferta = 'pendiente_aprobacion' WHERE id_oferta = ? AND id_carrera = ?";
    $stmt_rev = mysqli_prepare($conexion, $sql_revertir);
    mysqli_stmt_bind_param($stmt_rev, "ii", $id_revertir, $id_carrera);
    mysqli_stmt_execute($stmt_rev);
    mysqli_stmt_close($stmt_rev);
    $mensaje = "Oferta devuelta a estado pendiente de revisión.";
}

/* CONSULTAR OFERTAS RECHAZADAS */
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
            WHERE o.estado_oferta = 'rechazada' AND o.id_carrera = ?
            ORDER BY o.fecha_publicacion DESC";

$stmt_ofertas = mysqli_prepare($conexion, $consulta);
mysqli_stmt_bind_param($stmt_ofertas, "i", $id_carrera);
mysqli_stmt_execute($stmt_ofertas);
$resultado = mysqli_stmt_get_result($stmt_ofertas);
?>

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
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <?php if (mysqli_num_rows($resultado) > 0) { ?>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
            <div class="offer-card p-4 mb-3 border rounded shadow-sm bg-white">

                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-danger mb-0">
                        <?php echo htmlspecialchars($fila['titulo']); ?>
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
                        <?php echo htmlspecialchars($fila['nombre_carrera']); ?>
                    </span>
                    <span class="badge bg-danger">
                        <i class="bi bi-x-circle-fill me-1"></i>Rechazada
                    </span>
                </div>

                <p class="text-muted small mb-2">
                    <?php echo htmlspecialchars($fila['descripcion']); ?>
                </p>

                <p class="small mb-2">
                    <strong>Requisitos:</strong>
                    <?php echo htmlspecialchars($fila['requisitos']); ?>
                </p>

                <div class="small text-muted">
                    <strong>Empresa:</strong>
                    <?php echo htmlspecialchars($fila['nombre_empresa']); ?>
                    <span class="mx-2">|</span>
                    <strong>RUT:</strong>
                    <?php echo htmlspecialchars($fila['rut_empresa']); ?>
                </div>

                <div class="small text-muted mt-1">
                    <strong>Cupos:</strong>
                    <?php echo htmlspecialchars($fila['cupos']); ?>
                    <span class="mx-2">|</span>
                    <strong>Duración:</strong>
                    <?php echo htmlspecialchars($fila['duracion_meses']); ?> meses
                    <span class="mx-2">|</span>
                    <strong>Fecha publicación:</strong>
                    <?php echo htmlspecialchars($fila['fecha_publicacion']); ?>
                </div>

            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            No hay ofertas rechazadas para tu carrera.
        </div>
    <?php } ?>
</div>
