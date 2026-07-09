<?php
// Las variables $conexion e $id_estudiante vienen de auth.php

// Para cancelar postulación y actualizar en la tabla de ofertas de práctica
if (isset($_GET['cancelar'])) {
    $id_postulacion = (int) $_GET['cancelar'];

    $sql = "
    SELECT id_oferta, estado_postulacion
    FROM postulacion
    WHERE id_postulacion = $id_postulacion
    AND id_estudiante = $id_estudiante
    ";

    $resultado = mysqli_query($conexion, $sql);
    $postulacion = $resultado ? mysqli_fetch_assoc($resultado) : null;

    if ($postulacion && $postulacion['estado_postulacion'] == 'espera') {
        mysqli_query(
            $conexion,
            "DELETE FROM postulacion
            WHERE id_postulacion = $id_postulacion"
        );

        mysqli_query(
            $conexion,
            "UPDATE oferta_practica
            SET cupos = cupos + 1,
                estado_oferta = 'activa'
            WHERE id_oferta = " . (int) $postulacion['id_oferta']
        );
    }

    header("Location: inicio.php?pagina=postulaciones");
    exit();
}

$sql = "
SELECT
    p.id_postulacion,
    p.id_oferta,
    o.titulo,
    p.fecha_postulacion,
    p.estado_postulacion,
    p.cv_estudiante
FROM postulacion p
INNER JOIN oferta_practica o
ON p.id_oferta = o.id_oferta
WHERE p.id_estudiante = $id_estudiante
";

$resultado = mysqli_query($conexion, $sql);
$postulaciones_lista = [];
if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $postulaciones_lista[] = $fila;
    }
}
$total_postulaciones = count($postulaciones_lista);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <h1 class="mb-1">Mis Postulaciones</h1>
            <p class="text-muted mb-4">Revisa el estado de tus postulaciones realizadas.</p>

            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Oferta</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>CV</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($postulaciones_lista)): ?>
                                <?php foreach ($postulaciones_lista as $fila): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                                        <td><?php echo date("d-m-Y", strtotime($fila['fecha_postulacion'])); ?></td>
                                        <td>
                                            <?php
                                            if ($fila['estado_postulacion'] == "aceptada") {
                                                echo '<span class="badge bg-success">Aceptada</span>';
                                            } elseif ($fila['estado_postulacion'] == "rechazada") {
                                                echo '<span class="badge bg-danger">Rechazada</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark">En espera</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo !empty($fila['cv_estudiante']) ? htmlspecialchars($fila['cv_estudiante']) : 'Sin CV'; ?>
                                            </span>
                                        </td>
                                        <td>
                                        <td>
                                            <a href="inicio.php?pagina=detalle_oferta&id=<?php echo $fila['id_oferta']; ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                Ver Detalle
                                            </a>

                                            <?php if ($fila['estado_postulacion'] == 'espera'): ?>
                                                <a href="inicio.php?pagina=postulaciones&cancelar=<?php echo $fila['id_postulacion']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('¿Deseas cancelar esta postulación?')">
                                                    Cancelar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Aún no has realizado ninguna
                                        postulación.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom">
            <div class="card-body">
                <h5 class="fw-bold">Resumen</h5>
                <hr>
                <p>Total Postulaciones<span class="float-end fw-bold"><?php echo $total_postulaciones; ?></span></p>
                <a href="inicio.php?pagina=dashboard" class="btn btn-primary w-100">Explorar Ofertas</a>
            </div>
        </div>
    </div>
</div>