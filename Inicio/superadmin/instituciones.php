<?php
include('../../conexion.php');

$mensaje = '';
$tipoMensaje = 'success';

function limpiarTexto($valor)
{
    return trim((string) $valor);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $logo = limpiarTexto($_POST['logo'] ?? '');
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';

        if ($nombre === '') {
            $mensaje = 'El nombre de la institución es obligatorio.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = mysqli_prepare($conexion, "INSERT INTO Institucion (nombre, logo_institucion, estado_institucion) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $nombre, $logo, $estado);

            if (mysqli_stmt_execute($stmt)) {
                $mensaje = 'Institución creada correctamente.';
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

        if ($id <= 0 || $nombre === '') {
            $mensaje = 'Datos inválidos para actualizar.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = mysqli_prepare($conexion, "UPDATE Institucion SET nombre = ?, logo_institucion = ?, estado_institucion = ? WHERE id_institucion = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $logo, $estado, $id);

            if (mysqli_stmt_execute($stmt)) {
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
            $stmt = mysqli_prepare($conexion, "DELETE FROM Institucion WHERE id_institucion = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                $mensaje = 'Institución eliminada correctamente.';
            } else {
                $mensaje = 'No se pudo eliminar la institución.';
                $tipoMensaje = 'danger';
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensaje = 'ID de institución inválido.';
            $tipoMensaje = 'danger';
        }
    }
}

$instituciones = [];
$consulta = mysqli_query($conexion, "SELECT id_institucion, nombre, logo_institucion, estado_institucion FROM Institucion ORDER BY id_institucion DESC");
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
            <a class="nav-link" href="#"><i class="bi bi-server me-2"></i> Estado del Sistema</a>
            <a class="nav-link" href="#"><i class="bi bi-journal-text me-2"></i> Logs de Auditoría</a>
            <a class="nav-link" href="#"><i class="bi bi-database-up me-2"></i> Backups Globales</a>
            <a class="nav-link" href="#"><i class="bi bi-gear-wide-connected me-2"></i> Configuración</a>
            <a class="nav-link" href="#"><i class="bi bi-person-circle me-2"></i> Mi Perfil</a>
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

        <div class="card-custom p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Logo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($instituciones) > 0): ?>
                            <?php foreach ($instituciones as $institucion): ?>
                                <tr>
                                    <td><?= (int) $institucion['id_institucion'] ?></td>
                                    <td><?= htmlspecialchars($institucion['nombre']) ?></td>
                                    <td class="text-truncate" style="max-width: 260px;">
                                        <?= htmlspecialchars($institucion['logo_institucion'] ?? '') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $institucion['estado_institucion'] === 'activa' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($institucion['estado_institucion']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditar"
                                            data-id="<?= (int) $institucion['id_institucion'] ?>"
                                            data-nombre="<?= htmlspecialchars($institucion['nombre'], ENT_QUOTES) ?>"
                                            data-logo="<?= htmlspecialchars($institucion['logo_institucion'] ?? '', ENT_QUOTES) ?>"
                                            data-estado="<?= htmlspecialchars($institucion['estado_institucion'], ENT_QUOTES) ?>"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <?php if ($institucion['estado_institucion'] === 'activa'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                                <input type="hidden" name="estado" value="inactiva">
                                                <button class="btn btn-sm btn-outline-warning me-1" type="submit">
                                                    <i class="bi bi-toggle-off"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                                <input type="hidden" name="estado" value="activa">
                                                <button class="btn btn-sm btn-outline-success me-1" type="submit">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta institución?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_institucion" value="<?= (int) $institucion['id_institucion'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay instituciones registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
        });
    </script>
</body>

</html>
