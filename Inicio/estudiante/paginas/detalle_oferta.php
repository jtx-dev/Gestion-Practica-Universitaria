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

// Verificar si tiene una práctica activa en curso o asignada
$sqlActiva = "
SELECT estado_practica
FROM practica
WHERE id_estudiante = $id_estudiante
AND estado_practica IN ('asignado', 'en_curso', 'informe_entregado', 'evaluado')
LIMIT 1
";
$resActiva = mysqli_query($conexion, $sqlActiva);
$tieneActiva = $resActiva && mysqli_num_rows($resActiva) > 0;
$practicaActiva = $tieneActiva ? mysqli_fetch_assoc($resActiva) : null;

// Obtener el CV y estado de aprobación de documentos del estudiante desde su perfil
$sqlCV = "SELECT cv_estudiante, documentos_aprobados FROM estudiante WHERE id_usuario = $id_estudiante LIMIT 1";
$resCV = mysqli_query($conexion, $sqlCV);
$estCV = mysqli_fetch_assoc($resCV);
$cvEstudiante = $estCV['cv_estudiante'] ?? '';
$tieneCV = !empty($cvEstudiante);
$docsAprobados = ($estCV['documentos_aprobados'] ?? 0) == 1;

if (isset($_POST['postular']) && !$yaPostulo && !$tieneActiva && $tieneCV && $docsAprobados && $oferta['estado_oferta'] == 'activa') {
    $token = bin2hex(random_bytes(24));
    $expires = date('Y-m-d H:i:s', strtotime('+15 days'));

    $sqlPostular = "
    INSERT INTO postulacion
    (
        id_estudiante,
        id_oferta,
        cv_estudiante,
        token_confirmacion,
        fecha_limite_confirmacion
    )
    VALUES
    (
        $id_estudiante,
        $id_oferta,
        '" . mysqli_real_escape_string($conexion, $cvEstudiante) . "',
        '$token',
        '$expires'
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

        <h5>Requisitos (Aptitudes requeridas)</h5>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php
                $res_comps = mysqli_query($conexion, "SELECT c.nombre FROM oferta_competencias oc INNER JOIN competencias c ON oc.id_competencia = c.id WHERE oc.id_oferta = " . $id_oferta);
                if (mysqli_num_rows($res_comps) > 0) {
                    while ($comp = mysqli_fetch_assoc($res_comps)) {
                        echo '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill p-2" style="font-size: 0.85rem;">' . htmlspecialchars($comp['nombre']) . '</span>';
                    }
                } else {
                    echo '<span class="text-muted small">No se especificaron aptitudes requeridas.</span>';
                }
            ?>
        </div>

        <h5>Estado de Oferta</h5>
        <p>
            <span class="badge bg-success">
                <?php echo htmlspecialchars(ucfirst($oferta['estado_oferta'])); ?>
            </span>
        </p>

        <h5>Fecha de Publicación</h5>
        <p><?php echo date("d-m-Y", strtotime($oferta['fecha_publicacion'])); ?></p>

        <?php if ($tieneActiva) { ?>
            <div class="alert alert-warning border-0 border-start border-warning border-4 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Ya cuentas con un proceso de práctica registrado (Estado actual: <strong class="text-capitalize"><?php echo htmlspecialchars($practicaActiva['estado_practica']); ?></strong>). No se permiten nuevas postulaciones.
            </div>
        <?php } elseif (!$tieneCV && !$yaPostulo) { ?>
            <div class="alert alert-danger border-0 border-start border-danger border-4 shadow-sm mb-4">
                <i class="bi bi-x-circle-fill me-2"></i>
                No has cargado tu Currículum Vitae en tu perfil. Por favor, sube tu CV en la sección de <a href="inicio.php?pagina=perfil" class="alert-link fw-bold">Mi Perfil</a> antes de postular.
            </div>
        <?php } elseif (!$docsAprobados && !$yaPostulo) { ?>
            <div class="alert alert-danger border-0 border-start border-danger border-4 shadow-sm mb-4">
                <i class="bi bi-shield-x me-2"></i>
                Tus documentos de práctica aún no han sido aprobados por tu coordinador. Por favor, sube toda tu documentación obligatoria (CV, Cédula, Cert. Alumno Regular) en <a href="inicio.php?pagina=perfil" class="alert-link fw-bold">Mi Perfil</a> y espera la validación del coordinador antes de postular.
            </div>
        <?php } ?>

        <div class="mt-4">
            <a href="inicio.php?pagina=dashboard" class="btn btn-secondary me-2">Volver</a>
            <?php if ($oferta['estado_oferta'] == 'cerrada') { ?>
                <button class="btn btn-danger" disabled>Oferta Cerrada</button>
            <?php } elseif ($tieneActiva) { ?>
                <button class="btn btn-warning text-dark" disabled><i class="bi bi-lock-fill me-1"></i>Práctica en Curso</button>
            <?php } elseif (!$tieneCV) { ?>
                <button class="btn btn-danger" disabled><i class="bi bi-file-earmark-pdf-fill me-1"></i>Sube tu CV para postular</button>
            <?php } elseif (!$docsAprobados) { ?>
                <button class="btn btn-danger" disabled><i class="bi bi-shield-lock-fill me-1"></i>Falta Validación Académica</button>
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
