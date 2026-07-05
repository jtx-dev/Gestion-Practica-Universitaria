<?php
// Las variables $conexion e $id_estudiante vienen de auth.php

if (!isset($_GET['id'])) {
    header('Location: inicio.php?pagina=dashboard');
    exit;
}

$id_oferta = (int)$_GET['id'];

$sql = "
SELECT o.*, e.nombre_empresa
FROM oferta_practica o
INNER JOIN empresa e
ON o.id_empresa = e.id_usuario
WHERE o.id_oferta = $id_oferta 
AND o.estado_oferta = 'activa'
";

$resultado = mysqli_query($conexion, $sql);
$oferta = $resultado ? mysqli_fetch_assoc($resultado) : null;

if (!$oferta) {
    header('Location: inicio.php?pagina=dashboard');
    exit;
}

$sqlExiste = "
SELECT *
FROM postulacion
WHERE id_estudiante = $id_estudiante
AND id_oferta = $id_oferta
";

$resExiste = mysqli_query($conexion, $sqlExiste);
$yaPostulo = $resExiste && mysqli_num_rows($resExiste) > 0;

if (isset($_POST['postular']) && !$yaPostulo && $oferta['estado_oferta'] == 'activa') {
    $sqlPostular = "
    INSERT INTO postulacion
    (
        id_estudiante,
        id_oferta,
        cv_estudiante
    )
    VALUES
    (
        $id_estudiante,
        $id_oferta,
        'cv.pdf'
    )
    ";

    mysqli_query($conexion, $sqlPostular);
    mysqli_query(
        $conexion,
        "UPDATE oferta_practica
        SET cupos = cupos - 1
        WHERE id_oferta = $id_oferta
        AND cupos > 0"
    );

    $sqlCupos = "
    SELECT cupos
    FROM oferta_practica
    WHERE id_oferta = $id_oferta
    ";

    $resCupos = mysqli_query($conexion, $sqlCupos);
    $ofertaActualizada = $resCupos ? mysqli_fetch_assoc($resCupos) : null;

    if ($ofertaActualizada && $ofertaActualizada['cupos'] == 0) {
        mysqli_query(
            $conexion,
            "UPDATE oferta_practica
            SET estado_oferta = 'cerrada'
            WHERE id_oferta = $id_oferta"
        );
    }

    header("Location: inicio.php?pagina=postulaciones");
    exit();
}
?>

<div class="card card-custom">
    <div class="card-body p-4">
        <h2 class="mb-2"><?php echo htmlspecialchars($oferta['titulo']); ?></h2>
        <p class="text-muted mb-4">
            <i class="bi bi-building"></i>
            <?php echo htmlspecialchars($oferta['nombre_empresa']); ?>
        </p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <h5>Duración</h5>
                <span class="badge bg-primary">
                    <?php echo htmlspecialchars($oferta['duracion_meses']); ?> meses
                </span>
            </div>
            <div class="col-md-6 mb-3">
                <h5>Cupos</h5>
                <span class="badge bg-success">
                    <?php echo htmlspecialchars($oferta['cupos']); ?> cupos
                </span>
            </div>
        </div>

        <hr>

        <h5>Descripción</h5>
        <p><?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?></p>

        <h5>Requisitos</h5>
        <p><?php echo nl2br(htmlspecialchars($oferta['requisitos'])); ?></p>

        <h5>Estado de Oferta</h5>
        <p>
            <span class="badge bg-success">
                <?php echo htmlspecialchars(ucfirst($oferta['estado_oferta'])); ?>
            </span>
        </p>

        <h5>Fecha de Publicación</h5>
        <p><?php echo date("d-m-Y", strtotime($oferta['fecha_publicacion'])); ?></p>

        <div class="mt-4">
            <a href="inicio.php?pagina=dashboard" class="btn btn-secondary me-2">Volver</a>
            <?php if ($oferta['estado_oferta'] == 'cerrada') { ?>
                <button class="btn btn-danger" disabled>Oferta Cerrada</button>
            <?php } elseif (!$yaPostulo) { ?>
                <form method="POST" class="d-inline">
                    <button type="submit" name="postular" class="btn btn-primary">Postular</button>
                </form>
            <?php } else { ?>
                <button class="btn btn-success" disabled>Ya postulaste</button>
            <?php } ?>
        </div>
    </div>
</div>
