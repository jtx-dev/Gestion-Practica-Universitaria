<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php
$mensaje = null;
$tipo_mensaje = 'success';

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
    $tipo_mensaje = "success";
}

/* MANEJAR RECOMENDACIÓN */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'recomendar') {
    $id_est_rec = (int)$_POST['id_estudiante'];
    $titulo_oferta_rec = $_POST['titulo_oferta'];
    
    $titulo_notif = "¡Práctica Recomendada!";
    $mensaje_notif = "Tu coordinador te recomienda revisar la oferta: '$titulo_oferta_rec'. Tu perfil hace match con lo que buscan.";
    
    $sql_notif = "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES (?, ?, ?, 'recomendacion')";
    $stmt_notif = mysqli_prepare($conexion, $sql_notif);
    $stmt_notif = mysqli_prepare($conexion, $sql_notif);
    mysqli_stmt_bind_param($stmt_notif, "iss", $id_est_rec, $titulo_notif, $mensaje_notif);
    mysqli_stmt_execute($stmt_notif);
    mysqli_stmt_close($stmt_notif);
    $mensaje = "Recomendación enviada al alumno.";
    $tipo_mensaje = "success";
}


/* CONSULTAR OFERTAS APROBADAS */
$sql_ofertas = "SELECT
                    o.id_oferta,
                    o.id_carrera,
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
                WHERE o.estado_oferta = 'activa' AND o.id_carrera = ?
                ORDER BY o.fecha_publicacion DESC";

$stmt_ofertas = mysqli_prepare($conexion, $sql_ofertas);
mysqli_stmt_bind_param($stmt_ofertas, "i", $id_carrera);
mysqli_stmt_execute($stmt_ofertas);
$resultado = mysqli_stmt_get_result($stmt_ofertas);
?>



<div class="card card-custom p-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Listado de Ofertas Aprobadas</h4>
    </div>

    <?php if (isset($mensaje)) { ?>
        <div class="alert alert-<?php echo $tipo_mensaje ?? 'success'; ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-info-circle me-2"></i><?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <?php if (mysqli_num_rows($resultado) > 0) { ?>
        <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
            <div class="offer-card p-4 mb-3 border rounded shadow-sm bg-white">
                
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-primary mb-0">
                        <?php echo htmlspecialchars($fila['titulo']); ?>
                    </h5>
                    
                    <!-- Botón para revertir -->
                    <form method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas enviar esta oferta de vuelta a revisión? (Los estudiantes ya no la verán)');">
                        <input type="hidden" name="accion" value="revertir">
                        <input type="hidden" name="id_oferta" value="<?php echo $fila['id_oferta']; ?>">
                        <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-counterclockwise"></i> Deshacer Aprobación
                        </button>
                    </form>
                </div>

                <div class="mb-3">
                    <span class="badge bg-secondary me-1">
                        <?php echo htmlspecialchars($fila['nombre_carrera']); ?>
                    </span>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle-fill me-1"></i>Aprobada
                    </span>
                </div>

                <p class="text-muted small mb-2">
                    <?php echo htmlspecialchars($fila['descripcion']); ?>
                </p>

                <div class="mb-2 small">
                    <strong>Requisitos (Aptitudes):</strong>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <?php
                            $res_comps = mysqli_query($conexion, "SELECT c.nombre FROM oferta_competencias oc INNER JOIN competencias c ON oc.id_competencia = c.id WHERE oc.id_oferta = " . $fila['id_oferta']);
                            if (mysqli_num_rows($res_comps) > 0) {
                                while ($comp = mysqli_fetch_assoc($res_comps)) {
                                    echo '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">' . htmlspecialchars($comp['nombre']) . '</span>';
                                }
                            } else {
                                echo '<span class="text-muted small">No se especificaron aptitudes.</span>';
                            }
                        ?>
                    </div>
                </div>

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
                </div>

                <hr>

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-people me-2"></i>Postulantes a esta Práctica (Ordenados por Afinidad)</h6>
                <?php
                $id_oferta = (int)$fila['id_oferta'];
                $id_carrera_oferta = (int)$fila['id_carrera'];
                $titulo_oferta = htmlspecialchars($fila['titulo']);

                // 1. OBTENER POSTULANTES
                $sql_postulantes = "SELECT e.id_usuario, e.nombre, e.apellido, e.habilidades, p.cv_estudiante, p.token_confirmacion, p.estado_postulacion
                                    FROM postulacion p
                                    INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
                                    WHERE p.id_oferta = ?";
                $stmt_post = mysqli_prepare($conexion, $sql_postulantes);
                mysqli_stmt_bind_param($stmt_post, "i", $id_oferta);
                mysqli_stmt_execute($stmt_post);
                $res_post = mysqli_stmt_get_result($stmt_post);
                $lista_postulantes = [];
                if ($res_post) {
                    while ($p = mysqli_fetch_assoc($res_post)) {
                        $p['porcentaje_afinidad'] = calcular_afinidad_tags($conexion, $p['id_usuario'], $id_oferta);
                        $lista_postulantes[] = $p;
                    }
                }
                mysqli_stmt_close($stmt_post);
                
                usort($lista_postulantes, function($a, $b) { return $b['porcentaje_afinidad'] <=> $a['porcentaje_afinidad']; });

                if (!empty($lista_postulantes)) {
                    foreach ($lista_postulantes as $al) {
                        $afinidad = $al['porcentaje_afinidad'];
                        $badge_color = $afinidad >= 70 ? 'bg-success' : 'bg-warning text-dark';
                        ?>
                        <div class="d-flex justify-content-between align-items-center p-2 mb-1 border-bottom">
                            <div>
                                <span class="fw-bold small"><?php echo htmlspecialchars($al['nombre'] . " " . $al['apellido']); ?></span>
                                <div class="x-small text-muted" style="font-size: 0.75rem;">Habilidades: <?php echo htmlspecialchars($al['habilidades'] ?: 'N/A'); ?></div>
                            </div>
                            <div class="text-end" style="min-width: 150px;">
                                <span class="badge <?php echo $badge_color; ?> mb-1"><?php echo $afinidad; ?>% afinidad</span>
                                <?php if ($al['cv_estudiante']) { ?>
                                    <a href="../archivos/cv/<?php echo htmlspecialchars($al['cv_estudiante']); ?>" target="_blank" class="btn btn-outline-primary btn-sm d-block mb-1" style="font-size: 0.65rem; padding: 0.1rem 0.3rem;"><i class="bi bi-file-pdf"></i> Ver CV</a>
                                <?php } ?>
                                <?php if ($al['token_confirmacion'] && $al['estado_postulacion'] === 'espera') { ?>
                                    <a href="../empresa_confirmar.php?token=<?php echo $al['token_confirmacion']; ?>" target="_blank" class="btn btn-success btn-sm d-block mb-1" style="font-size: 0.65rem; padding: 0.1rem 0.3rem;" title="Simular confirmación de empresa"><i class="bi bi-link-45deg"></i> Link Empresa</a>
                                <?php } elseif ($al['estado_postulacion'] !== 'espera') { ?>
                                    <?php
                                        $badge_state_color = $al['estado_postulacion'] === 'aceptada' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge_state_color ?> d-block mt-1 text-capitalize" style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">Postulación <?= htmlspecialchars($al['estado_postulacion']) ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="text-muted small mb-3">Aún no hay postulantes para esta oferta.</p>';
                }
                ?>

                <h6 class="fw-bold text-success mb-3 mt-4"><i class="bi bi-stars me-2"></i>Sugerencias del Sistema (No han postulado)</h6>
                <?php
                // 2. OBTENER SUGERENCIAS (No postulantes y máximo 2 recomendaciones previas)
                $sql_sugerencias = "SELECT e.id_usuario, e.nombre, e.apellido, e.habilidades
                                    FROM estudiante e
                                    WHERE e.id_carrera = ?
                                    AND e.id_usuario NOT IN (SELECT id_estudiante FROM postulacion WHERE id_oferta = ?)
                                    AND (SELECT COUNT(*) FROM notificacion n WHERE n.id_usuario = e.id_usuario AND n.tipo_evento = 'recomendacion') < 2";
                $stmt_sug = mysqli_prepare($conexion, $sql_sugerencias);
                mysqli_stmt_bind_param($stmt_sug, "ii", $id_carrera_oferta, $id_oferta);
                mysqli_stmt_execute($stmt_sug);
                $res_sug = mysqli_stmt_get_result($stmt_sug);
                $lista_sugerencias = [];
                if ($res_sug) {
                    while ($s = mysqli_fetch_assoc($res_sug)) {
                        $s['porcentaje_afinidad'] = calcular_afinidad_tags($conexion, $s['id_usuario'], $id_oferta);
                        if ($s['porcentaje_afinidad'] > 0) {
                            $lista_sugerencias[] = $s;
                        }
                    }
                }
                mysqli_stmt_close($stmt_sug);
                
                usort($lista_sugerencias, function($a, $b) { return $b['porcentaje_afinidad'] <=> $a['porcentaje_afinidad']; });
                $top_sugerencias = array_slice($lista_sugerencias, 0, 2);

                if (!empty($top_sugerencias)) {
                    foreach ($top_sugerencias as $al) {
                        $afinidad = $al['porcentaje_afinidad'];
                        $badge_color = $afinidad >= 70 ? 'bg-success' : 'bg-warning text-dark';
                        ?>
                        <div class="d-flex justify-content-between align-items-center p-2 mb-1 border-bottom">
                            <div>
                                <span class="fw-bold small"><?php echo htmlspecialchars($al['nombre'] . " " . $al['apellido']); ?></span>
                                <div class="x-small text-muted" style="font-size: 0.75rem;">Habilidades: <?php echo htmlspecialchars($al['habilidades'] ?: 'N/A'); ?></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?php echo $badge_color; ?>"><?php echo $afinidad; ?>%</span>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="accion" value="recomendar">
                                    <input type="hidden" name="id_estudiante" value="<?php echo $al['id_usuario']; ?>">
                                    <input type="hidden" name="titulo_oferta" value="<?php echo $titulo_oferta; ?>">
                                    <button type="submit" class="btn btn-outline-success btn-sm" style="font-size: 0.7rem; padding: 0.1rem 0.3rem;" title="Notificar a este alumno recomendándole postular">
                                        <i class="bi bi-bell-fill"></i> Avisar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="text-muted small mb-0">No hay otros alumnos compatibles registrados.</p>';
                }
                ?>

            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="alert alert-info mb-0">
            No hay ofertas aprobadas para tu carrera.
        </div>
    <?php } ?>
</div>
