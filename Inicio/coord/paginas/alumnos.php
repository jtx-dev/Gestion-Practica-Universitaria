<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php

$mensaje = '';
$tipo_mensaje = 'success';

// Procesar evaluación si se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_evaluar'])) {
    $id_alumno = isset($_POST['id_alumno']) ? (int)$_POST['id_alumno'] : 0;
    $id_practica = isset($_POST['id_practica']) ? (int)$_POST['id_practica'] : 0;
    $nota = isset($_POST['nota']) ? (float)$_POST['nota'] : 0.0;
    $comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';

    // Validar carrera del alumno y existencia de la práctica
    $check_alumno = mysqli_query($conexion, "
        SELECT e.id_usuario, p.id_practica 
        FROM estudiante e
        LEFT JOIN practica p ON e.id_usuario = p.id_estudiante
        WHERE e.id_usuario = $id_alumno AND e.id_carrera = $id_carrera AND p.id_practica = $id_practica
    ");

    if (mysqli_num_rows($check_alumno) > 0) {
        if ($nota < 1.0 || $nota > 7.0) {
            $mensaje = "Error: La nota debe estar entre 1.0 y 7.0.";
            $tipo_mensaje = "danger";
        } else {
            $comentariosEscaped = mysqli_real_escape_string($conexion, $comentarios);
            
            // 1. Actualizar la tabla practica
            $sql_update_practica = "UPDATE practica 
                                    SET nota_final = ?, estado_practica = 'evaluado', fecha_termino = IFNULL(fecha_termino, CURRENT_DATE()) 
                                    WHERE id_practica = ? AND id_estudiante = ?";
            $stmt_up = mysqli_prepare($conexion, $sql_update_practica);
            mysqli_stmt_bind_param($stmt_up, "dii", $nota, $id_practica, $id_alumno);
            $success = mysqli_stmt_execute($stmt_up);
            mysqli_stmt_close($stmt_up);

            if ($success) {
                // 2. Gestionar la tabla evaluacion (verificar si ya existe evaluación)
                $sql_check_eval = "SELECT id_evaluacion FROM evaluacion WHERE id_practica = ?";
                $stmt_ce = mysqli_prepare($conexion, $sql_check_eval);
                mysqli_stmt_bind_param($stmt_ce, "i", $id_practica);
                mysqli_stmt_execute($stmt_ce);
                $res_eval = mysqli_stmt_get_result($stmt_ce);
                $eval_data = mysqli_fetch_assoc($res_eval);
                mysqli_stmt_close($stmt_ce);

                if ($eval_data) {
                    // Actualizar evaluación existente
                    $id_eval = $eval_data['id_evaluacion'];
                    $sql_update_eval = "UPDATE evaluacion 
                                        SET nota_final = ?, comentarios = ?, fecha_evaluacion = CURRENT_DATE() 
                                        WHERE id_evaluacion = ?";
                    $stmt_ue = mysqli_prepare($conexion, $sql_update_eval);
                    mysqli_stmt_bind_param($stmt_ue, "dsi", $nota, $comentariosEscaped, $id_eval);
                    mysqli_stmt_execute($stmt_ue);
                    mysqli_stmt_close($stmt_ue);
                } else {
                    // Insertar nueva evaluación
                    // Obtener la última bitácora si existe
                    $sql_last_bit = "SELECT MAX(id_bitacora) as last_bit FROM bitacora WHERE id_practica = ?";
                    $stmt_lb = mysqli_prepare($conexion, $sql_last_bit);
                    mysqli_stmt_bind_param($stmt_lb, "i", $id_practica);
                    mysqli_stmt_execute($stmt_lb);
                    $res_bit = mysqli_stmt_get_result($stmt_lb);
                    $bit_data = mysqli_fetch_assoc($res_bit);
                    $id_bitacora = $bit_data['last_bit'] ? (int)$bit_data['last_bit'] : null;
                    mysqli_stmt_close($stmt_lb);

                    $sql_insert_eval = "INSERT INTO evaluacion (id_practica, id_bitacora, nota_final, comentarios, fecha_evaluacion) 
                                        VALUES (?, ?, ?, ?, CURRENT_DATE())";
                    $stmt_ie = mysqli_prepare($conexion, $sql_insert_eval);
                    mysqli_stmt_bind_param($stmt_ie, "iids", $id_practica, $id_bitacora, $nota, $comentariosEscaped);
                    mysqli_stmt_execute($stmt_ie);
                    mysqli_stmt_close($stmt_ie);
                }

                // 3. Crear notificación para el alumno
                $msg_notif = mysqli_real_escape_string($conexion, "Tu coordinador ha evaluado tu práctica con nota " . number_format($nota, 1) . ".");
                mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_alumno, 'Práctica Evaluada', '$msg_notif', 'evaluacion')");

                $mensaje = "Práctica evaluada correctamente con nota " . number_format($nota, 1) . ".";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar la práctica en la base de datos.";
                $tipo_mensaje = "danger";
            }
        }
    } else {
        $mensaje = "Error: Alumno o práctica no válidos para su carrera.";
        $tipo_mensaje = "danger";
    }
}

// Consulta de alumnos de la carrera
$sql_alumnos = "SELECT e.id_usuario, e.nombre, e.apellido, e.nivel_curricular, e.habilidades, e.ramos_aprobados, e.cv_estudiante, u.correo, u.estado_cuenta,
                       p.id_practica, p.estado_practica, p.fecha_inicio, p.nota_final,
                       (SELECT MAX(b.fecha_registro) FROM bitacora b WHERE b.id_practica = p.id_practica) as ultima_bitacora,
                       (SELECT comentarios FROM evaluacion WHERE id_practica = p.id_practica LIMIT 1) as comentarios_evaluacion
                FROM estudiante e
                JOIN usuario u ON e.id_usuario = u.id_usuario
                LEFT JOIN practica p ON e.id_usuario = p.id_estudiante
                WHERE e.id_carrera = ?
                ORDER BY e.apellido ASC";

$stmt_alumnos = mysqli_prepare($conexion, $sql_alumnos);
mysqli_stmt_bind_param($stmt_alumnos, "i", $id_carrera);
mysqli_stmt_execute($stmt_alumnos);
$resultado = mysqli_stmt_get_result($stmt_alumnos);

// Pre-cargar ofertas activas para el matching
$sql_ofertas = "SELECT id_oferta, titulo, requisitos, empresa.nombre_empresa 
                FROM oferta_practica 
                JOIN empresa ON oferta_practica.id_empresa = empresa.id_usuario
                WHERE oferta_practica.id_carrera = ? AND estado_oferta = 'activa'";
$stmt_o = mysqli_prepare($conexion, $sql_ofertas);
mysqli_stmt_bind_param($stmt_o, "i", $id_carrera);
mysqli_stmt_execute($stmt_o);
$res_ofertas = mysqli_stmt_get_result($stmt_o);
$ofertas_base = [];
while ($o = mysqli_fetch_assoc($res_ofertas)) {
    $ofertas_base[] = $o;
}
?>



<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show border-0 shadow-sm p-3 mb-4 rounded-3" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card card-custom p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Alumno</th>
                    <th>Correo</th>
                    <th>Nivel Curricular</th>
                    <th>Estado Práctica</th>
                    <th>Cuenta</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($alumno = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td>
                                <div class="fw-bold d-inline-block"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></div>
                                <?php 
                                    $atrasado = false;
                                    if (strtolower($alumno['estado_practica'] ?? '') === 'en_curso') {
                                        $fechaRef = $alumno['ultima_bitacora'] ?: $alumno['fecha_inicio'];
                                        if ($fechaRef) {
                                            $dias = floor((time() - strtotime($fechaRef)) / 86400);
                                            if ($dias > 15) {
                                                $atrasado = true;
                                            }
                                        }
                                    }
                                    if ($atrasado) {
                                        echo '<span class="badge bg-danger ms-2 align-middle" title="No registra bitácora hace más de 15 días"><i class="bi bi-exclamation-triangle"></i> Bitácora Atrasada</span>';
                                    }
                                ?>
                                <br>
                                <small class="text-muted">ID: <?php echo $alumno['id_usuario']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                            <td>Nivel <?php echo $alumno['nivel_curricular']; ?></td>
                            <td>
                                <?php 
                                    $estado = $alumno['estado_practica'] ?? 'No iniciada';
                                    $badge_class = 'bg-secondary';
                                    if ($estado === 'En Curso' || strtolower($estado) === 'en_curso') $badge_class = 'bg-primary';
                                    if ($estado === 'Finalizada' || strtolower($estado) === 'finalizado') $badge_class = 'bg-success';
                                    if ($estado === 'Postulado' || strtolower($estado) === 'postulado') $badge_class = 'bg-warning text-dark';
                                    if (strtolower($estado) === 'evaluado') $badge_class = 'bg-info text-dark';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $estado)); ?></span>
                                <?php if ($alumno['nota_final'] !== null): ?>
                                    <br><small class="text-success fw-bold">Nota: <?php echo number_format($alumno['nota_final'], 1); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $alumno['estado_cuenta'] === 'activa' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($alumno['estado_cuenta']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($alumno['id_practica']) && in_array(strtolower($alumno['estado_practica'] ?? ''), ['en_curso', 'informe_entregado', 'evaluado', 'finalizado'])): ?>
                                    <button class="btn btn-sm btn-outline-warning" 
                                            title="Evaluar Práctica" 
                                            onclick='abrirModalEvaluar(<?php echo json_encode([
                                                "id_practica" => $alumno["id_practica"],
                                                "id_alumno" => $alumno["id_usuario"],
                                                "nombre" => $alumno["nombre"] . " " . $alumno["apellido"],
                                                "nota" => $alumno["nota_final"] !== null ? number_format($alumno["nota_final"], 1) : "",
                                                "comentarios" => $alumno["comentarios_evaluacion"] ?? ""
                                            ]); ?>)'>
                                        <i class="bi bi-clipboard-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-success" 
                                        title="Ofertas sugeridas" 
                                        onclick='verSugerencias(<?php echo json_encode([
                                            "nombre" => $alumno["nombre"] . " " . $alumno["apellido"],
                                            "habilidades" => $alumno["habilidades"] ?: "Sin registrar",
                                            "sugerencias" => array_map(function($o) use ($alumno, $conexion) {
                                                return [
                                                    "titulo" => $o["titulo"],
                                                    "empresa" => $o["nombre_empresa"],
                                                    "afinidad" => calcular_afinidad_tags($conexion, $alumno["id_usuario"], $o["id_oferta"])
                                                ];
                                            }, $ofertas_base)
                                        ]); ?>)'>
                                    <i class="bi bi-stars"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" 
                                        title="Ver Perfil" 
                                        onclick='verPerfil(<?php echo json_encode([
                                            "nombre" => $alumno["nombre"] . " " . $alumno["apellido"],
                                            "correo" => $alumno["correo"],
                                            "nivel" => $alumno["nivel_curricular"],
                                            "ramos" => $alumno["ramos_aprobados"],
                                            "habilidades" => $alumno["habilidades"] ?: "Sin registrar",
                                            "cv" => $alumno["cv_estudiante"],
                                            "estado" => $alumno["estado_practica"] ?? "No iniciada"
                                        ]); ?>)'>
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No se encontraron alumnos para esta carrera.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Sugerencias -->
<div class="modal fade" id="modalSugerencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-stars text-success me-2"></i>Ofertas Recomendadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalSugerenciasCuerpo"></div>
            </div>
        </div>
    </div>
</div>

<script>
function verSugerencias(data) {
    let cuerpo = `<h6>Alumno: <strong>${data.nombre}</strong></h6>`;
    cuerpo += `<p class="small text-muted">Habilidades: ${data.habilidades}</p><hr>`;

    let sugerencias = data.sugerencias
        .filter(s => s.afinidad > 0)
        .sort((a, b) => b.afinidad - a.afinidad);

    if (sugerencias.length > 0) {
        sugerencias.forEach(s => {
            let color = s.afinidad >= 70 ? 'success' : (s.afinidad >= 40 ? 'warning text-dark' : 'danger');
            cuerpo += `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <div class="fw-bold">${s.titulo}</div>
                        <div class="small text-muted">${s.empresa}</div>
                    </div>
                    <span class="badge bg-${color}">${s.afinidad}%</span>
                </div>
            `;
        });
    } else {
        cuerpo += `<div class="alert alert-warning small">No se encontraron ofertas compatibles con las habilidades de este alumno.</div>`;
    }

    document.getElementById('modalSugerenciasCuerpo').innerHTML = cuerpo;
    new bootstrap.Modal(document.getElementById('modalSugerencias')).show();
}

function verPerfil(data) {
    let cvHTML = '';
    if (data.cv && data.cv.trim() !== '') {
        cvHTML = `<a href="../archivos/cv/${encodeURIComponent(data.cv)}" target="_blank" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-2"><i class="bi bi-file-earmark-pdf-fill"></i> Descargar Currículum Vitae</a>`;
    } else {
        cvHTML = `<span class="badge bg-secondary p-2"><i class="bi bi-exclamation-circle-fill me-1"></i> Sin CV cargado aún</span>`;
    }

    let habilidadesBadges = '';
    if (data.habilidades && data.habilidades.trim() !== '' && data.habilidades !== 'Sin registrar') {
        let lista = data.habilidades.split(',');
        lista.forEach(h => {
            habilidadesBadges += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-1 mb-1" style="font-size: 0.8rem;">${h.trim()}</span>`;
        });
    } else {
        habilidadesBadges = `<span class="text-muted small">Sin registrar habilidades</span>`;
    }

    let body = `
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold fs-3 mb-2" style="width: 60px; height: 60px;">
                ${data.nombre.charAt(0).toUpperCase()}
            </div>
            <h5 class="fw-bold mb-1">${data.nombre}</h5>
            <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i>${data.correo}</p>
        </div>
        <div class="border rounded-3 p-3 bg-light bg-opacity-50 mb-3">
            <div class="row g-2 small">
                <div class="col-6"><strong>Nivel Curricular:</strong></div>
                <div class="col-6 text-end">Nivel ${data.nivel}</div>
                <div class="col-6"><strong>Ramos Aprobados:</strong></div>
                <div class="col-6 text-end">${data.ramos} ramos</div>
                <div class="col-6"><strong>Estado Práctica:</strong></div>
                <div class="col-6 text-end"><span class="badge bg-secondary text-capitalize">${data.estado}</span></div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">Aptitudes Declaradas</label>
            <div class="d-flex flex-wrap">${habilidadesBadges}</div>
        </div>
        <div class="mb-2 text-center">
            ${cvHTML}
        </div>
    `;

    document.getElementById('modalPerfilCuerpo').innerHTML = body;
    new bootstrap.Modal(document.getElementById('modalPerfil')).show();
}

function abrirModalEvaluar(data) {
    document.getElementById('eval_id_practica').value = data.id_practica;
    document.getElementById('eval_id_alumno').value = data.id_alumno;
    document.getElementById('eval_nombre_alumno').value = data.nombre;
    document.getElementById('eval_nota').value = data.nota;
    document.getElementById('eval_comentarios').value = data.comentarios;
    
    new bootstrap.Modal(document.getElementById('modalEvaluar')).show();
}
</script>

<!-- Modal de Perfil del Estudiante -->
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-circle text-primary me-2"></i>Perfil del Estudiante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="modalPerfilCuerpo"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Evaluación -->
<div class="modal fade" id="modalEvaluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="inicio.php?pagina=alumnos">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-clipboard-check text-warning me-2"></i>Evaluar Práctica Profesional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <input type="hidden" name="id_practica" id="eval_id_practica">
                    <input type="hidden" name="id_alumno" id="eval_id_alumno">
                    <input type="hidden" name="accion_evaluar" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Alumno</label>
                        <input type="text" id="eval_nombre_alumno" class="form-control bg-light" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eval_nota" class="form-label fw-semibold text-secondary small">Nota Final (1.0 - 7.0)</label>
                        <input type="number" name="nota" id="eval_nota" class="form-control" step="0.1" min="1.0" max="7.0" placeholder="Ej: 6.5" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eval_comentarios" class="form-label fw-semibold text-secondary small">Comentarios / Observaciones</label>
                        <textarea name="comentarios" id="eval_comentarios" class="form-control" rows="4" placeholder="Ingrese comentarios sobre el desempeño del alumno..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">Guardar Calificación</button>
                </div>
            </form>
        </div>
    </div>
</div>
