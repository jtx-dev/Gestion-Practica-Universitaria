<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php

/* APROBAR OFERTA */
if (isset($_GET['aprobar'])) {
    $id_oferta = (int)$_GET['aprobar'];

    // Asegurar que la oferta pertenece a la carrera del coordinador
    $sql = "UPDATE oferta_practica
            SET estado_oferta = 'activa'
            WHERE id_oferta = ? AND id_carrera = ?";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_oferta, $id_carrera);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: inicio.php?pagina=ofertas");
    exit();
}

/* RECHAZAR OFERTA */
if (isset($_GET['rechazar'])) {
    $id_oferta = (int)$_GET['rechazar'];

    // Asegurar que la oferta pertenece a la carrera del coordinador
    $sql = "UPDATE oferta_practica
            SET estado_oferta = 'rechazada'
            WHERE id_oferta = ? AND id_carrera = ?";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_oferta, $id_carrera);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: inicio.php?pagina=ofertas");
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
                WHERE o.estado_oferta = 'pendiente_aprobacion' AND o.id_carrera = ?
                ORDER BY o.fecha_publicacion DESC";

$stmt_ofertas = mysqli_prepare($conexion, $sql_ofertas);
mysqli_stmt_bind_param($stmt_ofertas, "i", $id_carrera);
mysqli_stmt_execute($stmt_ofertas);
$resultado = mysqli_stmt_get_result($stmt_ofertas);
?>

<header class="mb-5 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold mb-1">Gestión de Empresas / Ofertas</h2>
        <p class="text-muted">Revisión de ofertas de práctica enviadas por empresas.</p>
    </div>
    <span class="badge bg-warning text-dark fs-6">Ofertas pendientes</span>
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
                            <?php echo htmlspecialchars($fila['titulo']); ?>
                        </h5>

                        <div class="mb-2">
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars($fila['nombre_carrera']); ?>
                            </span>
                            <span class="badge bg-warning text-dark">
                                <?php echo htmlspecialchars($fila['estado_oferta']); ?>
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
                            <i class="bi bi-building me-1"></i>
                            <strong>Empresa:</strong>
                            <?php echo htmlspecialchars($fila['nombre_empresa']); ?>
                            <span class="mx-2">|</span>
                            <strong>RUT:</strong>
                            <?php echo htmlspecialchars($fila['rut_empresa']); ?>
                        </div>

                        <div class="small text-muted mt-1">
                            <i class="bi bi-people me-1"></i>
                            <strong>Cupos:</strong>
                            <?php echo htmlspecialchars($fila['cupos']); ?>
                            <span class="mx-2">|</span>
                            <i class="bi bi-calendar-event me-1"></i>
                            <strong>Duración:</strong>
                            <?php echo htmlspecialchars($fila['duracion_meses']); ?> meses
                            <span class="mx-2">|</span>
                            <strong>Cierre:</strong>
                            <?php echo htmlspecialchars($fila['fecha_cierre']); ?>
                        </div>
                    </div>

                    <div class="text-end" style="min-width: 150px;">
                        <a href="inicio.php?pagina=ofertas&aprobar=<?php echo $fila['id_oferta']; ?>"
                           class="btn btn-success btn-sm w-100 mb-2"
                           onclick="return confirm('¿Deseas aprobar esta oferta?');">
                            <i class="bi bi-check-circle me-1"></i>
                            Aprobar
                        </a>

                        <a href="inicio.php?pagina=ofertas&rechazar=<?php echo $fila['id_oferta']; ?>"
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
            No existen ofertas pendientes de aprobación para tu carrera.
        </div>
    <?php } ?>
</div>
