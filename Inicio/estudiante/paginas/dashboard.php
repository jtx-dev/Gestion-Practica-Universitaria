<?php
// Las variables $conexion, $id_estudiante, y $estudiante vienen preestablecidas por auth.php

$sql = "
SELECT o.*, e.nombre_empresa
FROM oferta_practica o
INNER JOIN empresa e
ON o.id_empresa = e.id_usuario
WHERE o.id_carrera = {$estudiante['id_carrera']}
AND o.estado_oferta = 'activa'
AND o.id_oferta NOT IN
(
    SELECT id_oferta
    FROM postulacion
    WHERE id_estudiante = $id_estudiante
)
";

$resultado = mysqli_query($conexion, $sql);

// OBTENER NOTIFICACIONES (Recomendaciones)
$sql_notif = "SELECT id_notificacion, titulo, mensaje FROM notificacion WHERE id_usuario = $id_estudiante AND leida = 0 ORDER BY fecha_envio DESC";
$res_notif = mysqli_query($conexion, $sql_notif);
$notificaciones = [];
if ($res_notif) {
    while($n = mysqli_fetch_assoc($res_notif)) {
        $notificaciones[] = $n;
    }
}

if (!empty($notificaciones)) {
    mysqli_query($conexion, "UPDATE notificacion SET leida = 1 WHERE id_usuario = $id_estudiante");
}
?>

<div class="row g-4 mb-5">
    <div class="col-md-12">
        <div class="card card-custom bg-white p-4">
            <h5 class="fw-bold mb-4">Estado de mi Proceso</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="small fw-bold text-secondary mb-2">Carga de Documentos</label>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                    <small class="text-success mt-1 d-inline-block">Completado <i class="bi bi-check-circle"></i></small>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-secondary mb-2">Validación de Empresa</label>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: 40%"></div>
                    </div>
                    <small class="text-muted mt-1 d-inline-block">En proceso (40%)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($notificaciones)): ?>
<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-bell-fill me-2"></i>Nuevas Recomendaciones de tu Coordinador</h5>
        <?php foreach ($notificaciones as $notif): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><?= htmlspecialchars($notif['titulo']) ?></h6>
                <p class="mb-0 small"><?= htmlspecialchars($notif['mensaje']) ?></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card card-custom bg-white">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0">Ofertas Para Práctica</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Empresa / Oferta</th>
                                <th>Afinidad</th>
                                <th>Duración</th>
                                <th>Cupos</th>
                                <th>Estado</th>
                                <th class="pe-4 text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php 
                            $ofertas_lista = [];
                            if ($resultado) {
                                while ($o = mysqli_fetch_assoc($resultado)) {
                                    $o['afinidad'] = calcular_afinidad_tags($conexion, $id_estudiante, $o['id_oferta']);
                                    $ofertas_lista[] = $o;
                                }
                            }

                            // Ordenar por afinidad
                            usort($ofertas_lista, function($a, $b) {
                                return $b['afinidad'] <=> $a['afinidad'];
                            });

                            if (!empty($ofertas_lista)):
                                foreach ($ofertas_lista as $oferta) { 
                                    $color_afinidad = $oferta['afinidad'] >= 70 ? 'success' : ($oferta['afinidad'] >= 40 ? 'warning text-dark' : 'secondary');
                                ?>

                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">
                                                <?php echo htmlspecialchars($oferta['titulo']); ?>
                                            </div>
                                        
                                            <small class="text-muted d-block">
                                                Empresa: <?php echo htmlspecialchars($oferta['nombre_empresa']); ?>
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-<?php echo $color_afinidad; ?> p-2">
                                                <?php echo $oferta['afinidad']; ?>%
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo htmlspecialchars($oferta['duracion_meses']); ?> meses
                                            </span>
                                        </td>

                                        <td class="fw-bold <?php echo ($oferta['cupos'] > 0) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo htmlspecialchars($oferta['cupos']); ?> cupos
                                        </td>

                                        <td>
                                            <?php if ($oferta['estado_oferta'] == 'activa') { ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php } else { ?>
                                                <span class="badge bg-danger">Cerrada</span>
                                            <?php } ?>
                                        </td>

                                        <td class="pe-4 text-end">
                                            <a href="inicio.php?pagina=detalle_oferta&id=<?php echo $oferta['id_oferta']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalle</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay ofertas de práctica disponibles en este momento.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
