<?php
// Las variables $conexion vienen de auth.php

$mensaje = '';
$tipoMensaje = 'success';
$administradores = [];

function limpiarTexto($valor)
{
    return trim((string) $valor);
}

$sqlAdministradores = "SELECT u.id_usuario, u.correo, a.nombre, a.apellido
FROM usuario u
INNER JOIN administrador a ON a.id_usuario = u.id_usuario
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE r.nombre_rol = 'Administrador' AND u.estado_cuenta = 'activa'
ORDER BY u.correo";
$resultadoAdministradores = mysqli_query($conexion, $sqlAdministradores);
if ($resultadoAdministradores) {
    while ($fila = mysqli_fetch_assoc($resultadoAdministradores)) {
        $administradores[] = $fila;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $logo = limpiarTexto($_POST['logo'] ?? '');
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';
        $idAdministrador = (int) ($_POST['id_administrador'] ?? 0);

        if ($nombre === '' || $idAdministrador <= 0) {
            $mensaje = 'El nombre y el administrador de la institución son obligatorios.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = mysqli_prepare($conexion, "INSERT INTO institucion (nombre, logo_institucion, estado_institucion, id_administrador) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $logo, $estado, $idAdministrador);

            if (mysqli_stmt_execute($stmt)) {
                $mensaje = 'Institución creada correctamente.';
                $idInstitucionNueva = mysqli_insert_id($conexion);

                if ($idAdministrador > 0) {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_institucion = ? WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtUsuario, "ii", $idInstitucionNueva, $idAdministrador);
                    mysqli_stmt_execute($stmtUsuario);
                    mysqli_stmt_close($stmtUsuario);
                }
            } else {
                $mensaje = 'No se pudo crear la institución: ' . mysqli_stmt_error($stmt);
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($accion === 'editar') {
        $id = (int) ($_POST['id_institucion'] ?? 0);
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $logo = limpiarTexto($_POST['logo'] ?? '');
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';
        $idAdministrador = (int) ($_POST['id_administrador'] ?? 0);

        if ($id <= 0 || $nombre === '' || $idAdministrador <= 0) {
            $mensaje = 'El nombre y el administrador son obligatorios para actualizar.';
            $tipoMensaje = 'danger';
        } else {
            $consultaActual = mysqli_prepare($conexion, "SELECT id_administrador FROM institucion WHERE id_institucion = ?");
            mysqli_stmt_bind_param($consultaActual, "i", $id);
            mysqli_stmt_execute($consultaActual);
            $resultadoActual = mysqli_stmt_get_result($consultaActual);
            $institucionActual = $resultadoActual ? mysqli_fetch_assoc($resultadoActual) : null;
            mysqli_stmt_close($consultaActual);

            $idAdministradorAnterior = (int) ($institucionActual['id_administrador'] ?? 0);

            $stmt = mysqli_prepare($conexion, "UPDATE institucion SET nombre = ?, logo_institucion = ?, estado_institucion = ?, id_administrador = ? WHERE id_institucion = ?");
            mysqli_stmt_bind_param($stmt, "sssii", $nombre, $logo, $estado, $idAdministrador, $id);

            if (mysqli_stmt_execute($stmt)) {
                if ($idAdministradorAnterior > 0 && $idAdministradorAnterior !== $idAdministrador) {
                    $stmtLimpiarUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_institucion = NULL WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtLimpiarUsuario, "i", $idAdministradorAnterior);
                    mysqli_stmt_execute($stmtLimpiarUsuario);
                    mysqli_stmt_close($stmtLimpiarUsuario);
                }

                $stmtAsignarUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_institucion = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmtAsignarUsuario, "ii", $id, $idAdministrador);
                mysqli_stmt_execute($stmtAsignarUsuario);
                mysqli_stmt_close($stmtAsignarUsuario);

                $mensaje = 'Institución actualizada correctamente.';
            } else {
                $mensaje = 'No se pudo actualizar la institución: ' . mysqli_stmt_error($stmt);
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($accion === 'cambiar_estado') {
        $id = (int) ($_POST['id_institucion'] ?? 0);
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';

        if ($id > 0) {
            $stmt = mysqli_prepare($conexion, "UPDATE institucion SET estado_institucion = ? WHERE id_institucion = ?");
            mysqli_stmt_bind_param($stmt, "si", $estado, $id);

            if (mysqli_stmt_execute($stmt)) {
                $mensaje = $estado === 'activa' ? 'Institución activada correctamente.' : 'Institución desactivada correctamente.';
            } else {
                $mensaje = 'No se pudo cambiar el estado de la institución.';
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensaje = 'ID de institución inválido.';
            $tipoMensaje = 'danger';
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id_institucion'] ?? 0);

        if ($id > 0) {
            mysqli_begin_transaction($conexion);
            try {
                // Helper para ejecutar un DELETE con una lista de IDs
                $execute_delete = function($table, $column, $ids) use ($conexion) {
                    if (empty($ids)) return true;
                    $ids_list = implode(',', array_map('intval', $ids));
                    $sql = "DELETE FROM $table WHERE $column IN ($ids_list)";
                    $stmt = mysqli_prepare($conexion, $sql);
                    if (!$stmt || !mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_error($conexion));
                    }
                    mysqli_stmt_close($stmt);
                    return true;
                };

                // 1. Obtener todos los IDs relacionados con la institución
                $get_ids = function($sql, $params = []) use ($conexion) {
                    $stmt = mysqli_prepare($conexion, $sql);
                    if ($params) {
                        mysqli_stmt_bind_param($stmt, str_repeat('i', count($params)), ...$params);
                    }
                    if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_error($conexion));
                    $result = mysqli_stmt_get_result($stmt);
                    $ids = [];
                    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
                        $ids[] = $row[0];
                    }
                    mysqli_stmt_close($stmt);
                    return $ids;
                };

                $user_ids = $get_ids("SELECT id_usuario FROM usuario WHERE id_institucion = ?", [$id]);
                $carrera_ids = $get_ids("SELECT id_carrera FROM carrera WHERE id_institucion = ?", [$id]);
                $oferta_ids = !empty($carrera_ids) ? $get_ids("SELECT id_oferta FROM oferta_practica WHERE id_carrera IN (" . implode(',', $carrera_ids) . ")") : [];
                $practica_ids_by_user = !empty($user_ids) ? $get_ids("SELECT id_practica FROM practica WHERE id_estudiante IN (" . implode(',', $user_ids) . ")") : [];
                $practica_ids_by_oferta = !empty($oferta_ids) ? $get_ids("SELECT id_practica FROM practica WHERE id_oferta IN (" . implode(',', $oferta_ids) . ")") : [];
                $practica_ids = array_unique(array_merge($practica_ids_by_user, $practica_ids_by_oferta));

                // 2. Ejecutar borrados en orden inverso de dependencia
                
                // Nivel 5: Dependencias de 'practica'
                $execute_delete('asistencia', 'id_practica', $practica_ids);
                $execute_delete('bitacora', 'id_practica', $practica_ids);
                $execute_delete('evaluacion', 'id_practica', $practica_ids);
                $execute_delete('evaluacion_empresa', 'id_practica', $practica_ids);

                // Nivel 4: Dependencias de 'oferta' y 'estudiante' (usuario)
                $execute_delete('oferta_competencias', 'id_oferta', $oferta_ids);
                $execute_delete('postulacion', 'id_oferta', $oferta_ids);
                if (!empty($user_ids)) {
                    $execute_delete('postulacion', 'id_estudiante', $user_ids);
                    $execute_delete('estudiante_competencias', 'id_estudiante', $user_ids);
                }

                // Nivel 3: 'practica', 'asignacion', 'oferta_practica'
                $execute_delete('practica', 'id_practica', $practica_ids);
                if (!empty($user_ids)) {
                     $execute_delete('asignacion', 'id_estudiante', $user_ids);
                }
                $execute_delete('oferta_practica', 'id_oferta', $oferta_ids);

                // Nivel 2: Perfiles de usuario y competencias
                if (!empty($carrera_ids)) {
                     $execute_delete('competencias', 'id_carrera', $carrera_ids);
                }
                if (!empty($user_ids)) {
                    $execute_delete('notificacion', 'id_usuario', $user_ids);
                    $execute_delete('estudiante', 'id_usuario', $user_ids);
                    $execute_delete('coordinador', 'id_usuario', $user_ids);
                    $execute_delete('directivo', 'id_usuario', $user_ids);
                    $execute_delete('empresa', 'id_usuario', $user_ids);
                    // Importante: Des-asigna al administrador de la institución antes de borrarlo
                    $stmt_unassign = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_institucion = ?");
                    mysqli_stmt_bind_param($stmt_unassign, "i", $id);
                    mysqli_stmt_execute($stmt_unassign);
                    mysqli_stmt_close($stmt_unassign);
                    $execute_delete('administrador', 'id_usuario', $user_ids);
                }
                
                // Nivel 1: 'usuario' y 'carrera'
                $execute_delete('usuario', 'id_institucion', [$id]);
                $execute_delete('carrera', 'id_institucion', [$id]);

                // Nivel 0: 'institucion'
                $execute_delete('institucion', 'id_institucion', [$id]);

                mysqli_commit($conexion);
                $mensaje = 'Institución y todos sus datos asociados fueron eliminados correctamente.';
                $tipoMensaje = 'success';
            } catch (Exception $e) {
                mysqli_rollback($conexion);
                $mensaje = 'Error al eliminar la institución: ' . $e->getMessage();
                $tipoMensaje = 'danger';
            }
        } else {
            $mensaje = 'ID de institución inválido.';
            $tipoMensaje = 'danger';
        }
    }
}

$instituciones = [];
$consulta = mysqli_query($conexion, "SELECT i.id_institucion, i.nombre, i.logo_institucion, i.estado_institucion, i.id_administrador, u.correo AS correo_administrador
FROM institucion i
LEFT JOIN usuario u ON u.id_usuario = i.id_administrador
ORDER BY i.id_institucion DESC");
if ($consulta) {
    while ($fila = mysqli_fetch_assoc($consulta)) {
        $instituciones[] = $fila;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Mantenedor de Instituciones</h2>
        <p class="text-muted mb-0">Crear, editar, activar, desactivar y eliminar instituciones.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-plus-lg me-2"></i>Nueva institución
    </button>
</div>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php if (count($instituciones) > 0): ?>
        <?php foreach ($instituciones as $institucion): ?>
            <?php
            $logo = trim((string) ($institucion['logo_institucion'] ?? ''));
            $logoSrc = '';
            if ($logo !== '') {
                $logoSrc = preg_match('#^https?://#i', $logo) ? $logo : '../../' . ltrim($logo, '/');
            }
            ?>
            <div class="col-12">
                <div class="institution-card p-3" style="background: #ffffff; border: 1px solid #e3e6f0; border-radius: 8px; margin-bottom: 1rem;">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        <div class="flex-shrink-0">
                            <?php if ($logoSrc !== ''): ?>
                                <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo de <?= htmlspecialchars($institucion['nombre']) ?>" class="institution-logo" style="width:48px;height:48px;max-width:48px;max-height:48px;object-fit:contain;display:block;">
                            <?php else: ?>
                                <div class="institution-logo-placeholder" style="width:48px;height:48px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;border-radius:50%;color:#6c757d;font-size:24px;">
                                    <i class="bi bi-building"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="mb-0"><?= htmlspecialchars($institucion['nombre']) ?></h5>
                                <span class="badge bg-<?= $institucion['estado_institucion'] === 'activa' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($institucion['estado_institucion']) ?>
                                </span>
                            </div>
                            <div class="text-muted small">ID <?= (int) $institucion['id_institucion'] ?></div>
                            <div class="small text-muted mt-2 text-break">
                                <?= $logo !== '' ? htmlspecialchars($logo) : 'Sin logo registrado' ?>
                            </div>
                            <div class="small mt-2">
                                <span class="text-muted">Administrador:</span>
                                <?= !empty($institucion['correo_administrador']) ? htmlspecialchars($institucion['correo_administrador']) : '<span class="text-muted">Sin asignar</span>' ?>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <button
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditar"
                                data-id="<?= (int) $institucion['id_institucion'] ?>"
                                data-nombre="<?= htmlspecialchars($institucion['nombre'], ENT_QUOTES) ?>"
                                data-logo="<?= htmlspecialchars($institucion['logo_institucion'] ?? '', ENT_QUOTES) ?>"
                                data-estado="<?= htmlspecialchars($institucion['estado_institucion'], ENT_QUOTES) ?>"
                                data-admin="<?= (int) ($institucion['id_administrador'] ?? 0) ?>"
                            >
                                <i class="bi bi-pencil-square me-1"></i>Editar
                            </button>
                            <?php if ($institucion['estado_institucion'] === 'activa'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                    <input type="hidden" name="estado" value="inactiva">
                                    <button class="btn btn-sm btn-outline-warning" type="submit">
                                        <i class="bi bi-toggle-off me-1"></i>Desactivar
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                    <input type="hidden" name="estado" value="activa">
                                    <button class="btn btn-sm btn-outline-success" type="submit">
                                        <i class="bi bi-toggle-on me-1"></i>Activar
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta institución?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                    <i class="bi bi-trash me-1"></i>Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card-custom p-4 text-center text-muted">No hay instituciones registradas.</div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="activa">Activa</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input type="text" name="logo" class="form-control" placeholder="URL o ruta del logo">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Administrador</label>
                            <select name="id_administrador" class="form-select" required>
                                <option value="" disabled selected>Seleccione</option>
                                <?php foreach ($administradores as $administrador): ?>
                                    <option value="<?= (int) $administrador['id_usuario'] ?>">
                                        <?= htmlspecialchars($administrador['correo']) ?> - <?= htmlspecialchars($administrador['nombre'] . ' ' . $administrador['apellido']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar institución</button>
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
                    <h5 class="modal-title">Editar institución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_institucion" id="editar_id">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="editar_estado" class="form-select">
                                <option value="activa">Activa</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input type="text" name="logo" id="editar_logo" class="form-control" placeholder="URL o ruta del logo">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Administrador</label>
                            <select name="id_administrador" id="editar_id_administrador" class="form-select" required>
                                <option value="" disabled>Seleccione</option>
                                <?php foreach ($administradores as $administrador): ?>
                                    <option value="<?= (int) $administrador['id_usuario'] ?>">
                                        <?= htmlspecialchars($administrador['correo']) ?> - <?= htmlspecialchars($administrador['nombre'] . ' ' . $administrador['apellido']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar institución</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEditar = document.getElementById('modalEditar');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function (event) {
                const boton = event.relatedTarget;
                document.getElementById('editar_id').value = boton.getAttribute('data-id') || '';
                document.getElementById('editar_nombre').value = boton.getAttribute('data-nombre') || '';
                document.getElementById('editar_logo').value = boton.getAttribute('data-logo') || '';
                document.getElementById('editar_estado').value = boton.getAttribute('data-estado') || 'activa';
                document.getElementById('editar_id_administrador').value = boton.getAttribute('data-admin') || '0';
            });
        }
    });
</script>
