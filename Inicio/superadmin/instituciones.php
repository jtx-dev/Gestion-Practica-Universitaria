<?php
include('../../conexion.php');

$mensaje = '';
$tipoMensaje = 'success';
$administradores = [];

function limpiarTexto($valor)
{
    return trim((string) $valor);
}

$sqlAdministradores = "SELECT u.id_usuario, u.correo, a.nombre, a.apellido
FROM Usuario u
INNER JOIN Administrador a ON a.id_usuario = u.id_usuario
INNER JOIN Rol r ON r.id_rol = u.id_rol
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
            $stmt = mysqli_prepare($conexion, "INSERT INTO Institucion (nombre, logo_institucion, estado_institucion, id_administrador) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $logo, $estado, $idAdministrador);

            if (mysqli_stmt_execute($stmt)) {
                $mensaje = 'Institución creada correctamente.';
                $idInstitucionNueva = mysqli_insert_id($conexion);

                if ($idAdministrador > 0) {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE Usuario SET id_institucion = ? WHERE id_usuario = ?");
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
            $consultaActual = mysqli_prepare($conexion, "SELECT id_administrador FROM Institucion WHERE id_institucion = ?");
            mysqli_stmt_bind_param($consultaActual, "i", $id);
            mysqli_stmt_execute($consultaActual);
            $resultadoActual = mysqli_stmt_get_result($consultaActual);
            $institucionActual = $resultadoActual ? mysqli_fetch_assoc($resultadoActual) : null;
            mysqli_stmt_close($consultaActual);

            $idAdministradorAnterior = (int) ($institucionActual['id_administrador'] ?? 0);

            $stmt = mysqli_prepare($conexion, "UPDATE Institucion SET nombre = ?, logo_institucion = ?, estado_institucion = ?, id_administrador = ? WHERE id_institucion = ?");
            mysqli_stmt_bind_param($stmt, "sssii", $nombre, $logo, $estado, $idAdministrador, $id);

            if (mysqli_stmt_execute($stmt)) {
                if ($idAdministradorAnterior > 0 && $idAdministradorAnterior !== $idAdministrador) {
                    $stmtLimpiarUsuario = mysqli_prepare($conexion, "UPDATE Usuario SET id_institucion = NULL WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtLimpiarUsuario, "i", $idAdministradorAnterior);
                    mysqli_stmt_execute($stmtLimpiarUsuario);
                    mysqli_stmt_close($stmtLimpiarUsuario);
                }

                $stmtAsignarUsuario = mysqli_prepare($conexion, "UPDATE Usuario SET id_institucion = ? WHERE id_usuario = ?");
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
            $stmt = mysqli_prepare($conexion, "UPDATE Institucion SET estado_institucion = ? WHERE id_institucion = ?");
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
            $consultaValidacion = mysqli_prepare($conexion, "SELECT id_administrador FROM Institucion WHERE id_institucion = ?");
            mysqli_stmt_bind_param($consultaValidacion, "i", $id);
            mysqli_stmt_execute($consultaValidacion);
            $resultadoValidacion = mysqli_stmt_get_result($consultaValidacion);
            $institucionValidacion = $resultadoValidacion ? mysqli_fetch_assoc($resultadoValidacion) : null;
            mysqli_stmt_close($consultaValidacion);

            $idAdministradorAsignado = (int) ($institucionValidacion['id_administrador'] ?? 0);

            if ($idAdministradorAsignado > 0) {
                $mensaje = 'No se puede eliminar la institución mientras tenga un administrador asignado. Elimina primero ese administrador.';
                $tipoMensaje = 'warning';
            } else {
                $stmt = mysqli_prepare($conexion, "DELETE FROM Institucion WHERE id_institucion = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);

                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = 'Institución eliminada correctamente.';
                } else {
                    $mensaje = 'No se pudo eliminar la institución.';
                    $tipoMensaje = 'danger';
                }

                mysqli_stmt_close($stmt);
            }
        } else {
            $mensaje = 'ID de institución inválido.';
            $tipoMensaje = 'danger';
        }
    }
}

$instituciones = [];
$consulta = mysqli_query($conexion, "SELECT i.id_institucion, i.nombre, i.logo_institucion, i.estado_institucion, i.id_administrador, u.correo AS correo_administrador
FROM Institucion i
LEFT JOIN Usuario u ON u.id_usuario = i.id_administrador
ORDER BY i.id_institucion DESC");
if ($consulta) {
    while ($fila = mysqli_fetch_assoc($consulta)) {
        $instituciones[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Instituciones - SuperAdmin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Super Administrador</div>
        </div>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link" href="inicio.php"><i class="bi bi-shield-lock me-2"></i> Dashboard</a>
            <a class="nav-link active" href="instituciones.php"><i class="bi bi-person-gear me-2"></i> Gestionar instituciones</a>
            <a class="nav-link" href="administradores.php"><i class="bi bi-person-badge me-2"></i> Gestionar administradores</a>
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php"><i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión</a>
        </nav>
    </div>

    <div class="main-content">
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
                        <div class="institution-card p-3">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                                <div class="flex-shrink-0">
                                    <?php if ($logoSrc !== ''): ?>
                                        <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo de <?= htmlspecialchars($institucion['nombre']) ?>" class="institution-logo" style="width:48px;height:48px;max-width:48px;max-height:48px;object-fit:contain;display:block;">
                                    <?php else: ?>
                                        <div class="institution-logo-placeholder">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEditar = document.getElementById('modalEditar');
        modalEditar.addEventListener('show.bs.modal', function (event) {
            const boton = event.relatedTarget;
            document.getElementById('editar_id').value = boton.getAttribute('data-id') || '';
            document.getElementById('editar_nombre').value = boton.getAttribute('data-nombre') || '';
            document.getElementById('editar_logo').value = boton.getAttribute('data-logo') || '';
            document.getElementById('editar_estado').value = boton.getAttribute('data-estado') || 'activa';
            document.getElementById('editar_id_administrador').value = boton.getAttribute('data-admin') || '0';
        });
    </script>
</body>
</html>
