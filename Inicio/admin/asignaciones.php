<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function admin_limpiar_texto_local($valor): string
{
    return trim((string) $valor);
}

function admin_buscar_por_id(array $items, int $id, string $clave): ?array
{
    foreach ($items as $item) {
        if ((int) ($item[$clave] ?? 0) === $id) {
            return $item;
        }
    }

    return null;
}

function admin_normalizar_rol_local(string $nombreRol): string
{
    return strtolower(trim($nombreRol));
}

function admin_rol_es_directivo_local(string $nombreRol): bool
{
    return in_array(admin_normalizar_rol_local($nombreRol), ['director', 'directivo'], true);
}

function admin_traer_asignacion_por_id(mysqli $conexion, int $idAsignacion, int $idInstitucionActual): ?array
{
    $sql = "SELECT a.id_asignacion, a.id_estudiante, a.id_coordinador, a.id_directivo, a.fecha_asignacion, a.estado,
            CONCAT(es.nombre, ' ', es.apellido) AS estudiante_nombre,
            CONCAT(co.nombre, ' ', co.apellido) AS coordinador_nombre,
            CONCAT(di.nombre, ' ', di.apellido) AS directivo_nombre,
            ce.nombre_carrera AS estudiante_carrera,
            cc.nombre_carrera AS coordinador_carrera,
            cd.nombre_carrera AS directivo_carrera
        FROM asignacion a
        INNER JOIN estudiante es ON es.id_usuario = a.id_estudiante
        INNER JOIN usuario ues ON ues.id_usuario = es.id_usuario AND ues.id_institucion = ?
        INNER JOIN coordinador co ON co.id_usuario = a.id_coordinador
        INNER JOIN usuario uco ON uco.id_usuario = co.id_usuario AND uco.id_institucion = ?
        INNER JOIN directivo di ON di.id_usuario = a.id_directivo
        INNER JOIN usuario udi ON udi.id_usuario = di.id_usuario AND udi.id_institucion = ?
        LEFT JOIN carrera ce ON ce.id_carrera = es.id_carrera
        LEFT JOIN carrera cc ON cc.id_carrera = co.id_carrera
        LEFT JOIN carrera cd ON cd.id_carrera = di.id_carrera
        WHERE a.id_asignacion = ?
        LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'iiii', $idInstitucionActual, $idInstitucionActual, $idInstitucionActual, $idAsignacion);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
    mysqli_stmt_close($stmt);

    return $fila ?: null;
}

$mensaje = '';
$tipoMensaje = 'success';
$idInstitucionActual = admin_obtener_id_institucion_actual($conexion);
$nombreInstitucionActual = 'Sin institucion';
$asignacionesHabilitada = admin_table_exists($conexion, 'asignacion');
$estudiantes = [];
$coordinadores = [];
$directivos = [];
$asignaciones = [];

if ($idInstitucionActual > 0) {
    $stmtInstitucion = mysqli_prepare($conexion, "SELECT nombre FROM institucion WHERE id_institucion = ? LIMIT 1");
    if ($stmtInstitucion) {
        mysqli_stmt_bind_param($stmtInstitucion, 'i', $idInstitucionActual);
        mysqli_stmt_execute($stmtInstitucion);
        $resultadoInstitucion = mysqli_stmt_get_result($stmtInstitucion);
        $filaInstitucion = $resultadoInstitucion ? mysqli_fetch_assoc($resultadoInstitucion) : null;
        mysqli_stmt_close($stmtInstitucion);
        if ($filaInstitucion && !empty($filaInstitucion['nombre'])) {
            $nombreInstitucionActual = (string) $filaInstitucion['nombre'];
        }
    }

    $estudiantes = admin_query_all($conexion, "SELECT u.id_usuario,
            CONCAT(e.nombre, ' ', e.apellido) AS nombre,
            e.id_carrera,
            c.nombre_carrera
        FROM usuario u
        INNER JOIN rol r ON r.id_rol = u.id_rol
        INNER JOIN estudiante e ON e.id_usuario = u.id_usuario
        LEFT JOIN carrera c ON c.id_carrera = e.id_carrera
        WHERE u.id_institucion = " . (int) $idInstitucionActual . "
          AND LOWER(TRIM(r.nombre_rol)) = 'estudiante'
        ORDER BY e.nombre, e.apellido");

    $coordinadores = admin_query_all($conexion, "SELECT u.id_usuario,
            CONCAT(c.nombre, ' ', c.apellido) AS nombre,
            c.id_carrera,
            ca.nombre_carrera
        FROM usuario u
        INNER JOIN rol r ON r.id_rol = u.id_rol
        INNER JOIN coordinador c ON c.id_usuario = u.id_usuario
        LEFT JOIN carrera ca ON ca.id_carrera = c.id_carrera
        WHERE u.id_institucion = " . (int) $idInstitucionActual . "
          AND LOWER(TRIM(r.nombre_rol)) = 'coordinador'
        ORDER BY c.nombre, c.apellido");

    $directivos = admin_query_all($conexion, "SELECT u.id_usuario,
            CONCAT(d.nombre, ' ', d.apellido) AS nombre,
            d.id_carrera,
            ca.nombre_carrera
        FROM usuario u
        INNER JOIN rol r ON r.id_rol = u.id_rol
        INNER JOIN directivo d ON d.id_usuario = u.id_usuario
        LEFT JOIN carrera ca ON ca.id_carrera = d.id_carrera
        WHERE u.id_institucion = " . (int) $idInstitucionActual . "
          AND LOWER(TRIM(r.nombre_rol)) IN ('director', 'directivo')
        ORDER BY d.nombre, d.apellido");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($idInstitucionActual <= 0) {
        $mensaje = 'No se pudo identificar la institucion del administrador. Inicia sesion nuevamente.';
        $tipoMensaje = 'danger';
    } elseif (!$asignacionesHabilitada) {
        $mensaje = 'La tabla asignacion no existe en este esquema.';
        $tipoMensaje = 'danger';
    } elseif ($accion === 'crear' || $accion === 'editar') {
        $idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);
        $idEstudiante = (int) ($_POST['id_estudiante'] ?? 0);
        $idCoordinador = (int) ($_POST['id_coordinador'] ?? 0);
        $idDirectivo = (int) ($_POST['id_directivo'] ?? 0);

        $est = admin_buscar_por_id($estudiantes, $idEstudiante, 'id_usuario');
        $coor = admin_buscar_por_id($coordinadores, $idCoordinador, 'id_usuario');
        $dir = admin_buscar_por_id($directivos, $idDirectivo, 'id_usuario');

        if (!$est || !$coor || !$dir) {
            $mensaje = 'Selecciona estudiante, coordinador y director validos de tu institucion.';
            $tipoMensaje = 'danger';
        } elseif ((int) $est['id_carrera'] <= 0 || (int) $coor['id_carrera'] <= 0 || (int) $dir['id_carrera'] <= 0) {
            $mensaje = 'Todos los participantes deben tener una carrera valida.';
            $tipoMensaje = 'danger';
        } elseif ((int) $est['id_carrera'] !== (int) $coor['id_carrera'] || (int) $est['id_carrera'] !== (int) $dir['id_carrera']) {
            $mensaje = 'El coordinador o director no pertenece a la carrera del estudiante.';
            $tipoMensaje = 'danger';
        } else {
            mysqli_begin_transaction($conexion);
            try {
                if ($accion === 'crear') {
                    $stmt = mysqli_prepare($conexion, "INSERT INTO asignacion (id_estudiante, id_coordinador, id_directivo) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception('No se pudo preparar la asignacion.');
                    }
                    mysqli_stmt_bind_param($stmt, 'iii', $idEstudiante, $idCoordinador, $idDirectivo);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_stmt_error($stmt));
                    }
                    mysqli_stmt_close($stmt);
                    $mensaje = 'Asignacion registrada correctamente.';
                    admin_registrar_auditoria($conexion, 'Creacion de asignacion', 'asignaciones', 'Estudiante: ' . $idEstudiante . ' | Coordinador: ' . $idCoordinador . ' | Director: ' . $idDirectivo);
                } else {
                    $asignacionActual = admin_traer_asignacion_por_id($conexion, $idAsignacion, $idInstitucionActual);
                    if (!$asignacionActual) {
                        throw new Exception('La asignacion no existe o no pertenece a tu institucion.');
                    }

                    $stmt = mysqli_prepare($conexion, "UPDATE asignacion SET id_estudiante = ?, id_coordinador = ?, id_directivo = ? WHERE id_asignacion = ?");
                    if (!$stmt) {
                        throw new Exception('No se pudo preparar la actualizacion.');
                    }
                    mysqli_stmt_bind_param($stmt, 'iiii', $idEstudiante, $idCoordinador, $idDirectivo, $idAsignacion);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_stmt_error($stmt));
                    }
                    mysqli_stmt_close($stmt);
                    $mensaje = 'Asignacion actualizada correctamente.';
                    admin_registrar_auditoria($conexion, 'Edicion de asignacion', 'asignaciones', 'ID asignacion: ' . $idAsignacion . ' | Estudiante: ' . $idEstudiante . ' | Coordinador: ' . $idCoordinador . ' | Director: ' . $idDirectivo);
                }

                mysqli_commit($conexion);
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo guardar la asignacion.';
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($accion === 'eliminar') {
        $idAsignacion = (int) ($_POST['id_asignacion'] ?? 0);

        mysqli_begin_transaction($conexion);
        try {
            $asignacionActual = admin_traer_asignacion_por_id($conexion, $idAsignacion, $idInstitucionActual);
            if (!$asignacionActual) {
                throw new Exception('La asignacion no existe o no pertenece a tu institucion.');
            }

            $stmt = mysqli_prepare($conexion, "DELETE FROM asignacion WHERE id_asignacion = ?");
            if (!$stmt) {
                throw new Exception('No se pudo preparar la eliminacion.');
            }
            mysqli_stmt_bind_param($stmt, 'i', $idAsignacion);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            mysqli_commit($conexion);
            admin_registrar_auditoria($conexion, 'Eliminacion de asignacion', 'asignaciones', 'ID asignacion: ' . $idAsignacion);
            $mensaje = 'Asignacion eliminada correctamente.';
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            $mensaje = 'No se pudo eliminar la asignacion.';
            $tipoMensaje = 'danger';
        }
    }
}

if ($idInstitucionActual > 0 && $asignacionesHabilitada) {
    $asignaciones = admin_query_all($conexion, "SELECT a.id_asignacion, a.id_estudiante, a.id_coordinador, a.id_directivo, a.fecha_asignacion, a.estado,
            CONCAT(es.nombre, ' ', es.apellido) AS estudiante_nombre,
            CONCAT(co.nombre, ' ', co.apellido) AS coordinador_nombre,
            CONCAT(di.nombre, ' ', di.apellido) AS directivo_nombre,
            ce.nombre_carrera AS estudiante_carrera,
            cc.nombre_carrera AS coordinador_carrera,
            cd.nombre_carrera AS directivo_carrera
        FROM asignacion a
        INNER JOIN estudiante es ON es.id_usuario = a.id_estudiante
        INNER JOIN usuario ues ON ues.id_usuario = es.id_usuario AND ues.id_institucion = " . (int) $idInstitucionActual . "
        INNER JOIN coordinador co ON co.id_usuario = a.id_coordinador
        INNER JOIN usuario uco ON uco.id_usuario = co.id_usuario AND uco.id_institucion = " . (int) $idInstitucionActual . "
        INNER JOIN directivo di ON di.id_usuario = a.id_directivo
        INNER JOIN usuario udi ON udi.id_usuario = di.id_usuario AND udi.id_institucion = " . (int) $idInstitucionActual . "
        LEFT JOIN carrera ce ON ce.id_carrera = es.id_carrera
        LEFT JOIN carrera cc ON cc.id_carrera = co.id_carrera
        LEFT JOIN carrera cd ON cd.id_carrera = di.id_carrera
        ORDER BY a.id_asignacion DESC");
}

admin_layout_header('Asignaciones', 'Gestion de relaciones entre estudiante, coordinador y director.');
?>
<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= admin_e($tipoMensaje) ?> alert-dismissible fade show" role="alert">
        <?= admin_e($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <p class="text-muted mb-1">Institucion actual</p>
        <h5 class="mb-0"><?= admin_e($nombreInstitucionActual) ?></h5>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear" <?= $idInstitucionActual <= 0 || !$asignacionesHabilitada ? 'disabled' : '' ?>>
        <i class="bi bi-plus-circle me-2"></i>Nueva asignacion
    </button>
</div>

<?php if ($idInstitucionActual <= 0): ?>
    <div class="alert alert-warning">No se pudo identificar la institucion del administrador. La creacion y edicion de asignaciones esta deshabilitada.</div>
<?php elseif (!$asignacionesHabilitada): ?>
    <div class="alert alert-warning">Este esquema no incluye la tabla <code>asignacion</code>. La pantalla queda disponible solo como referencia funcional.</div>
<?php endif; ?>

<div class="card card-custom">
    <div class="card-body table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Carrera</th>
                    <th>Coordinador</th>
                    <th>Director</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($asignaciones)): ?>
                    <?php foreach ($asignaciones as $asignacion): ?>
                        <tr>
                            <td><?= (int) $asignacion['id_asignacion'] ?></td>
                            <td><?= admin_e($asignacion['estudiante_nombre'] ?? 'Sin dato') ?></td>
                            <td><?= admin_e($asignacion['estudiante_carrera'] ?? 'Sin carrera') ?></td>
                            <td><?= admin_e($asignacion['coordinador_nombre'] ?? 'Sin dato') ?></td>
                            <td><?= admin_e($asignacion['directivo_nombre'] ?? 'Sin dato') ?></td>
                            <td><?= admin_e((string) ($asignacion['fecha_asignacion'] ?? 'No disponible')) ?></td>
                            <td><span class="badge bg-<?= admin_badge_estado((string) ($asignacion['estado'] ?? 'activa')) ?>"><?= admin_e((string) ($asignacion['estado'] ?? 'activa')) ?></span></td>
                            <td class="d-flex gap-2 flex-wrap">
                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"
                                    data-id="<?= (int) $asignacion['id_asignacion'] ?>"
                                    data-estudiante="<?= (int) $asignacion['id_estudiante'] ?>"
                                    data-coordinador="<?= (int) $asignacion['id_coordinador'] ?>"
                                    data-directivo="<?= (int) $asignacion['id_directivo'] ?>"
                                >
                                    Editar
                                </button>

                                <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta asignacion?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_asignacion" value="<?= (int) $asignacion['id_asignacion'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay asignaciones registradas para esta institucion.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva asignacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estudiante</label>
                            <select name="id_estudiante" id="crear_id_estudiante" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                    <option value="<?= (int) $estudiante['id_usuario'] ?>"><?= admin_e($estudiante['nombre'] . ' - ' . ($estudiante['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coordinador</label>
                            <select name="id_coordinador" id="crear_id_coordinador" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($coordinadores as $coordinador): ?>
                                    <option value="<?= (int) $coordinador['id_usuario'] ?>"><?= admin_e($coordinador['nombre'] . ' - ' . ($coordinador['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Director</label>
                            <select name="id_directivo" id="crear_id_directivo" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($directivos as $directivo): ?>
                                    <option value="<?= (int) $directivo['id_usuario'] ?>"><?= admin_e($directivo['nombre'] . ' - ' . ($directivo['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Institucion</label>
                            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar asignacion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Editar asignacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_asignacion" id="editar_id_asignacion">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estudiante</label>
                            <select name="id_estudiante" id="editar_id_estudiante" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                    <option value="<?= (int) $estudiante['id_usuario'] ?>"><?= admin_e($estudiante['nombre'] . ' - ' . ($estudiante['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coordinador</label>
                            <select name="id_coordinador" id="editar_id_coordinador" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($coordinadores as $coordinador): ?>
                                    <option value="<?= (int) $coordinador['id_usuario'] ?>"><?= admin_e($coordinador['nombre'] . ' - ' . ($coordinador['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Director</label>
                            <select name="id_directivo" id="editar_id_directivo" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($directivos as $directivo): ?>
                                    <option value="<?= (int) $directivo['id_usuario'] ?>"><?= admin_e($directivo['nombre'] . ' - ' . ($directivo['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Institucion</label>
                            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar asignacion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modalEditar = document.getElementById('modalEditar');
    modalEditar.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('editar_id_asignacion').value = boton.getAttribute('data-id') || '';
        document.getElementById('editar_id_estudiante').value = boton.getAttribute('data-estudiante') || '';
        document.getElementById('editar_id_coordinador').value = boton.getAttribute('data-coordinador') || '';
        document.getElementById('editar_id_directivo').value = boton.getAttribute('data-directivo') || '';
    });
</script>
<?php admin_layout_footer(); ?>