<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php

$mensaje = '';
$tipo_mensaje = 'success';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alumno = isset($_POST['id_alumno']) ? (int)$_POST['id_alumno'] : 0;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    // Validar que el alumno pertenezca a la carrera del coordinador
    $check_carrera = mysqli_query($conexion, "SELECT id_usuario FROM estudiante WHERE id_usuario = $id_alumno AND id_carrera = $id_carrera");
    if (mysqli_num_rows($check_carrera) > 0) {
        if ($accion === 'aprobar') {
            mysqli_query($conexion, "UPDATE estudiante SET documentos_aprobados = 1, motivo_rechazo = NULL WHERE id_usuario = $id_alumno");
            
            // Insertar notificación para el alumno
            $msg = mysqli_real_escape_string($conexion, "Tus documentos de práctica han sido aprobados por tu coordinador. Ya estás habilitado para postular a ofertas.");
            mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_alumno, 'Documentos Aprobados', '$msg', 'validacion')");
            
            $mensaje = "Documentación aprobada y habilitación concedida correctamente.";
            $tipo_mensaje = "success";
        } elseif ($accion === 'rechazar') {
            $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
            if (empty($motivo)) {
                $mensaje = "Error: Debes ingresar obligatoriamente un motivo para el rechazo.";
                $tipo_mensaje = "danger";
            } else {
                $motivoEscaped = mysqli_real_escape_string($conexion, $motivo);
                mysqli_query($conexion, "UPDATE estudiante SET documentos_aprobados = 0, motivo_rechazo = '$motivoEscaped' WHERE id_usuario = $id_alumno");
                
                // Insertar notificación para el alumno
                $msg = mysqli_real_escape_string($conexion, "Tus documentos de práctica fueron rechazados por el coordinador. Motivo: " . $motivo);
                mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_alumno, 'Documentos Rechazados', '$msg', 'rechazo_docs')");
                
                $mensaje = "Documentación rechazada. Se ha notificado al alumno.";
                $tipo_mensaje = "warning";
            }
        } elseif ($accion === 'recordar') {
            // Insertar notificación de recordatorio para el alumno
            $msg = mysqli_real_escape_string($conexion, "Recuerda subir tu documentación obligatoria (CV, Cédula, Cert. Alumno Regular) en tu perfil para poder postular a ofertas.");
            mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_alumno, 'Completar Documentación', '$msg', 'recordatorio')");
            
            $mensaje = "Recordatorio enviado correctamente al alumno.";
            $tipo_mensaje = "success";
        }
    }
}

// Consultar estudiantes (Solo mostrar los que no están validados y no han sido rechazados aún)
$sql_estudiantes = "
    SELECT e.id_usuario, e.nombre, e.apellido, e.nivel_curricular, e.cv_estudiante, e.archivo_cedula, e.archivo_alumno_regular, e.documentos_aprobados, e.motivo_rechazo, u.correo
    FROM estudiante e
    JOIN usuario u ON e.id_usuario = u.id_usuario
    WHERE e.id_carrera = $id_carrera
      AND e.documentos_aprobados = 0
      AND (e.motivo_rechazo IS NULL OR e.motivo_rechazo = '')
    ORDER BY e.apellido ASC
";
$resultado = mysqli_query($conexion, $sql_estudiantes);
?>



<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show border-0 shadow-sm p-3 mb-4 rounded-3" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card card-custom p-4 bg-white shadow-sm">
    <h5 class="fw-bold mb-4 text-primary">Estado de Documentación de Alumnos</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th class="text-center">Cédula Identidad</th>
                    <th class="text-center">Cert. Alumno Regular</th>
                    <th class="text-center">Curriculum Vitae</th>
                    <th>Estado Validación</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($alumno = mysqli_fetch_assoc($resultado)): ?>
                        <?php
                            $has_cedula = !empty($alumno['archivo_cedula']);
                            $has_cert = !empty($alumno['archivo_alumno_regular']);
                            $has_cv = !empty($alumno['cv_estudiante']);
                            $todo_subido = ($has_cedula && $has_cert && $has_cv);
                            
                            $estado = 'Incompleto';
                            $badge_class = 'bg-danger';
                            if ($alumno['documentos_aprobados'] == 1) {
                                $estado = 'Validado';
                                $badge_class = 'bg-success';
                            } elseif ($todo_subido) {
                                $estado = 'Pendiente Revisión';
                                $badge_class = 'bg-warning text-dark';
                            }
                        ?>
                        <tr class="<?php echo $alumno['documentos_aprobados'] == 1 ? 'table-light' : ''; ?>">
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></div>
                                <div class="text-muted small">Nivel <?php echo $alumno['nivel_curricular']; ?></div>
                                <?php if ($alumno['documentos_aprobados'] == 0 && !empty($alumno['motivo_rechazo'])): ?>
                                    <div class="text-danger small mt-1" style="font-size: 0.75rem;"><i class="bi bi-x-circle me-1"></i> Rechazado: <?php echo htmlspecialchars($alumno['motivo_rechazo']); ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Cédula -->
                            <td class="text-center">
                                <?php if ($has_cedula): ?>
                                    <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                    <a href="../archivos/cedula/<?php echo htmlspecialchars($alumno['archivo_cedula']); ?>" target="_blank" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary mb-1"><i class="bi bi-exclamation-circle me-1"></i> Falta</span>
                                <?php endif; ?>
                            </td>

                            <!-- Certificado Alumno Regular -->
                            <td class="text-center">
                                <?php if ($has_cert): ?>
                                    <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                    <a href="../archivos/alumno_regular/<?php echo htmlspecialchars($alumno['archivo_alumno_regular']); ?>" target="_blank" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary mb-1"><i class="bi bi-exclamation-circle me-1"></i> Falta</span>
                                <?php endif; ?>
                            </td>

                            <!-- Currículum -->
                            <td class="text-center">
                                <?php if ($has_cv): ?>
                                    <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                                    <a href="../archivos/cv/<?php echo htmlspecialchars($alumno['cv_estudiante']); ?>" target="_blank" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary mb-1"><i class="bi bi-exclamation-circle me-1"></i> Falta</span>
                                <?php endif; ?>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span class="badge <?php echo $badge_class; ?> px-3 py-2 rounded-pill"><?php echo $estado; ?></span>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end">
                                <?php if ($alumno['documentos_aprobados'] == 1): ?>
                                    <button type="button" onclick="abrirRechazo(<?php echo $alumno['id_usuario']; ?>)" class="btn btn-sm btn-outline-danger shadow-sm" title="Revocar validación si hay problemas"><i class="bi bi-shield-x me-1"></i> Revocar</button>
                                <?php elseif ($todo_subido): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_alumno" value="<?php echo $alumno['id_usuario']; ?>">
                                        <button type="submit" name="accion" value="aprobar" class="btn btn-sm btn-success shadow-sm me-1" title="Aprobar todos los documentos"><i class="bi bi-check-lg me-1"></i> Aprobar</button>
                                    </form>
                                    <button type="button" onclick="abrirRechazo(<?php echo $alumno['id_usuario']; ?>)" class="btn btn-sm btn-outline-danger shadow-sm" title="Rechazar y avisar"><i class="bi bi-x-lg"></i> Rechazar</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success opacity-50 shadow-sm me-1" style="cursor:not-allowed;" disabled title="Falta documentación para poder aprobar"><i class="bi bi-check-lg"></i> Aprobar</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_alumno" value="<?php echo $alumno['id_usuario']; ?>">
                                        <button type="submit" name="accion" value="recordar" class="btn btn-sm btn-outline-secondary shadow-sm" title="Enviar recordatorio de documentos"><i class="bi bi-bell"></i> Recordar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay alumnos con documentos pendientes de validación en tu carrera.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Motivo de Rechazo -->
<div class="modal fade" id="modalRechazo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Rechazar Documentación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="formRechazo">
                <div class="modal-body pt-3">
                    <p class="small text-muted mb-3">Ingresa el motivo detallado del rechazo. Este mensaje será enviado como notificación al estudiante para que proceda con la corrección.</p>
                    <input type="hidden" name="id_alumno" id="rechazo_id_alumno">
                    <input type="hidden" name="accion" value="rechazar">
                    
                    <div class="mb-3">
                        <label for="motivo_input" class="form-label fw-bold small text-secondary">Motivo del Rechazo (Obligatorio)</label>
                        <textarea class="form-control" name="motivo" id="motivo_input" rows="3" placeholder="Ej: La foto de la cédula está borrosa / El certificado no es del semestre actual..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirRechazo(idAlumno) {
    document.getElementById('rechazo_id_alumno').value = idAlumno;
    document.getElementById('motivo_input').value = '';
    new bootstrap.Modal(document.getElementById('modalRechazo')).show();
}
</script>
