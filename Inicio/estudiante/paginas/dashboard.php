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

// OBTENER ESTADO DINÁMICO DEL PROCESO
$resPost = mysqli_query($conexion, "SELECT estado_postulacion, cv_estudiante FROM postulacion WHERE id_estudiante = $id_estudiante ORDER BY fecha_postulacion DESC LIMIT 1");
$post = mysqli_fetch_assoc($resPost);

$resPrac = mysqli_query($conexion, "SELECT estado_practica FROM practica WHERE id_estudiante = $id_estudiante LIMIT 1");
$prac = mysqli_fetch_assoc($resPrac);

// Carga de Documentos
$docPorcentaje = 0;
$docBadgeText = "0%";
$docDetalleText = "Pendiente (Falta cargar CV en perfil)";
$docClase = "bg-secondary";
$docIcono = "bi-exclamation-circle";

if ($post && !empty($post['cv_estudiante'])) {
    $docPorcentaje = 100;
    $docBadgeText = "100%";
    $docDetalleText = "Completado (CV y antecedentes cargados)";
    $docClase = "bg-success";
    $docIcono = "bi-check-circle-fill";
}

// Validación de Empresa
$empPorcentaje = 0;
$empBadgeText = "0%";
$empDetalleText = "Sin postulaciones (No has iniciado postulaciones)";
$empClase = "bg-secondary";
$empIcono = "bi-info-circle";

if ($prac) {
    $estadoPrac = $prac['estado_practica'];
    if ($estadoPrac === 'en_curso') {
        $empPorcentaje = 100;
        $empBadgeText = "100%";
        $empDetalleText = "Confirmada (Empresa aceptó postulación, práctica en curso)";
        $empClase = "bg-success";
        $empIcono = "bi-check-circle-fill";
    } elseif (in_array($estadoPrac, ['finalizado', 'evaluado', 'informe_entregado'])) {
        $empPorcentaje = 100;
        $empBadgeText = "100%";
        $empDetalleText = "Finalizada (Práctica completada con éxito)";
        $empClase = "bg-success";
        $empIcono = "bi-check-circle-fill";
    } elseif ($estadoPrac === 'asignado') {
        $empPorcentaje = 70;
        $empBadgeText = "70%";
        $empDetalleText = "Asignada (Coordinador asignó estudiante, pendiente inicio)";
        $empClase = "bg-info text-dark";
        $empIcono = "bi-clock-history";
    } else { // postulado
        $empPorcentaje = 40;
        $empBadgeText = "40%";
        $empDetalleText = "Postulado (A la espera de respuesta de la empresa)";
        $empClase = "bg-warning text-dark";
        $empIcono = "bi-hourglass-split";
    }
} elseif ($post) {
    $estadoPost = $post['estado_postulacion'];
    if ($estadoPost === 'espera') {
        $empPorcentaje = 40;
        $empBadgeText = "40%";
        $empDetalleText = "En revisión (A la espera de respuesta de la empresa)";
        $empClase = "bg-warning text-dark";
        $empIcono = "bi-hourglass-split";
    } elseif ($estadoPost === 'aceptada') {
        $empPorcentaje = 100;
        $empBadgeText = "100%";
        $empDetalleText = "Aceptada (Empresa aceptó, listo para formalizar)";
        $empClase = "bg-success";
        $empIcono = "bi-check-circle-fill";
    } elseif ($estadoPost === 'rechazada') {
        $empPorcentaje = 0;
        $empBadgeText = "0%";
        $empDetalleText = "Rechazada (Empresa declinó postulación)";
        $empClase = "bg-danger";
        $empIcono = "bi-x-circle-fill";
    }
}
?>

<div class="row g-4 mb-5">
    <div class="col-md-12">
        <div class="card card-custom bg-white p-4">
            <h5 class="fw-bold mb-4">Estado de mi Proceso</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small fw-bold text-secondary">Carga de Documentos</label>
                        <span class="badge rounded-pill <?php echo $docClase; ?> small"><?php echo $docBadgeText; ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?php echo $docClase; ?>" style="width: <?php echo $docPorcentaje; ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block small"><i class="bi <?php echo $docIcono; ?> me-1"></i> <?php echo $docDetalleText; ?></small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small fw-bold text-secondary">Validación de Empresa</label>
                        <span class="badge rounded-pill <?php echo $empClase; ?> small"><?php echo $empBadgeText; ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?php echo $empClase; ?>" style="width: <?php echo $empPorcentaje; ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block small"><i class="bi <?php echo $empIcono; ?> me-1"></i> <?php echo $empDetalleText; ?></small>
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
