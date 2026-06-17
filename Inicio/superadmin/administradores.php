<?php
include('../../conexion.php');

$mensaje = '';
$tipoMensaje = 'success';
$administradores = [];
$instituciones = [];

function limpiarTexto($valor)
{
    return trim((string) $valor);
}

function rut_normalizar(string $rut): string
{
    return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
}

function rut_formatear(string $rut): string
{
    $rut = rut_normalizar($rut);

    if (strlen($rut) < 2) {
        return $rut;
    }

    $dv = substr($rut, -1);
    $numero = substr($rut, 0, -1);
    $formateado = '';

    while (strlen($numero) > 3) {
        $formateado = '.' . substr($numero, -3) . $formateado;
        $numero = substr($numero, 0, -3);
    }

    return $numero . $formateado . '-' . $dv;
}

function validarRUT(string $rut): bool
{
    $rut = rut_normalizar($rut);
    if (strlen($rut) < 2) {
        return false;
    }

    $numero = substr($rut, 0, -1);
    $dvOriginal = strtoupper(substr($rut, -1));

    $suma = 0;
    $factor = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += ((int) $numero[$i]) * $factor;
        $factor = ($factor === 7) ? 2 : $factor + 1;
    }

    $dvCalculado = 11 - ($suma % 11);
    if ($dvCalculado === 11) {
        $dvCalculado = '0';
    } elseif ($dvCalculado === 10) {
        $dvCalculado = 'K';
    } else {
        $dvCalculado = (string) $dvCalculado;
    }

    return $dvOriginal === $dvCalculado;
}

$resultadoInstituciones = mysqli_query($conexion, "SELECT id_institucion, nombre FROM institucion ORDER BY nombre");
if ($resultadoInstituciones) {
    while ($fila = mysqli_fetch_assoc($resultadoInstituciones)) {
        $instituciones[] = $fila;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $idInstitucion = (int) ($_POST['id_institucion'] ?? 0);
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $apellido = limpiarTexto($_POST['apellido'] ?? '');
        $rut = limpiarTexto($_POST['rut'] ?? '');
        $correo = limpiarTexto($_POST['correo'] ?? '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $rutNormalizado = rut_normalizar($rut);

        if ($nombre === '' || $apellido === '' || $rut === '' || $correo === '' || $contrasena === '') {
            $mensaje = 'Completa todos los datos del administrador.';
            $tipoMensaje = 'danger';
        } elseif (!validarRUT($rut)) {
            $mensaje = 'El RUT ingresado no es válido. Revisa el dígito verificador.';
            $tipoMensaje = 'danger';
        } else {
            $sqlRolAdmin = "SELECT id_rol FROM rol WHERE nombre_rol = 'Administrador' LIMIT 1";
            $resultadoRol = mysqli_query($conexion, $sqlRolAdmin);
            $filaRol = $resultadoRol ? mysqli_fetch_assoc($resultadoRol) : null;
            $idRolAdmin = (int) ($filaRol['id_rol'] ?? 0);

            if ($idRolAdmin <= 0) {
                $mensaje = 'No se encontró el rol Administrador.';
                $tipoMensaje = 'danger';
            } else {
                mysqli_begin_transaction($conexion);
                try {
                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                    if ($idInstitucion > 0) {
                        $stmtUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, id_institucion, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, ?, 'activa')");
                        mysqli_stmt_bind_param($stmtUsuario, "iisss", $idRolAdmin, $idInstitucion, $rutNormalizado, $correo, $hash);
                    } else {
                        $stmtUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, id_institucion, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, NULL, ?, ?, ?, 'activa')");
                        mysqli_stmt_bind_param($stmtUsuario, "isss", $idRolAdmin, $rutNormalizado, $correo, $hash);
                    }
                    if (!mysqli_stmt_execute($stmtUsuario)) {
                        throw new Exception(mysqli_stmt_error($stmtUsuario));
                    }
                    $idUsuario = mysqli_insert_id($conexion);
                    mysqli_stmt_close($stmtUsuario);

                    $stmtAdmin = mysqli_prepare($conexion, "INSERT INTO administrador (id_usuario, nombre, apellido) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmtAdmin, "iss", $idUsuario, $nombre, $apellido);
                    if (!mysqli_stmt_execute($stmtAdmin)) {
                        throw new Exception(mysqli_stmt_error($stmtAdmin));
                    }
                    mysqli_stmt_close($stmtAdmin);

                    $stmtInstitucion = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    mysqli_stmt_bind_param($stmtInstitucion, "ii", $idUsuario, $idInstitucion);
                    if (!mysqli_stmt_execute($stmtInstitucion)) {
                        throw new Exception(mysqli_stmt_error($stmtInstitucion));
                    }
                    mysqli_stmt_close($stmtInstitucion);

                    mysqli_commit($conexion);
                    $mensaje = 'Administrador creado correctamente.';
                } catch (Throwable $e) {
                    mysqli_rollback($conexion);
                    $mensaje = 'No se pudo crear el administrador.';
                    $tipoMensaje = 'danger';
                }
            }
        }
    }

    if ($accion === 'cambiar_estado') {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';

        if ($id > 0) {
            $stmt = mysqli_prepare($conexion, "UPDATE usuario SET estado_cuenta = ? WHERE id_usuario = ?");
            mysqli_stmt_bind_param($stmt, "si", $estado, $id);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = $estado === 'activa' ? 'Administrador activado correctamente.' : 'Administrador desactivado correctamente.';
            } else {
                $mensaje = 'No se pudo cambiar el estado.';
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($accion === 'editar') {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = limpiarTexto($_POST['nombre'] ?? '');
        $apellido = limpiarTexto($_POST['apellido'] ?? '');
        $rut = limpiarTexto($_POST['rut'] ?? '');
        $correo = limpiarTexto($_POST['correo'] ?? '');
        $idInstitucion = (int) ($_POST['id_institucion'] ?? 0);
        $rutNormalizado = rut_normalizar($rut);
        $rutOriginalNormalizado = rut_normalizar((string) ($_POST['rut_original'] ?? ''));
        $rutActualNormalizado = '';

        if ($id > 0) {
            $stmtRut = mysqli_prepare($conexion, "SELECT rut FROM usuario WHERE id_usuario = ? LIMIT 1");
            if ($stmtRut) {
                mysqli_stmt_bind_param($stmtRut, "i", $id);
                mysqli_stmt_execute($stmtRut);
                $resultadoRut = mysqli_stmt_get_result($stmtRut);
                $filaRut = $resultadoRut ? mysqli_fetch_assoc($resultadoRut) : null;
                $rutActualNormalizado = rut_normalizar((string) ($filaRut['rut'] ?? ''));
                mysqli_stmt_close($stmtRut);
            }
        }
        $rutReferenciaNormalizado = $rutOriginalNormalizado !== '' ? $rutOriginalNormalizado : $rutActualNormalizado;

        if ($id > 0 && $nombre !== '' && $apellido !== '' && $rut !== '' && $correo !== '') {
            if ($rutReferenciaNormalizado === '' || $rutNormalizado !== $rutReferenciaNormalizado) {
                if (!validarRUT($rut)) {
                    $mensaje = 'El RUT ingresado no es válido. Revisa el dígito verificador.';
                    $tipoMensaje = 'danger';
                } else {
            mysqli_begin_transaction($conexion);
            try {
                if ($idInstitucion > 0) {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET rut = ?, correo = ?, id_institucion = ? WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtUsuario, "ssii", $rutNormalizado, $correo, $idInstitucion, $id);
                } else {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET rut = ?, correo = ?, id_institucion = NULL WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtUsuario, "ssi", $rutNormalizado, $correo, $id);
                }
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                mysqli_stmt_close($stmtUsuario);

                $stmtAdmin = mysqli_prepare($conexion, "UPDATE administrador SET nombre = ?, apellido = ? WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmtAdmin, "ssi", $nombre, $apellido, $id);
                if (!mysqli_stmt_execute($stmtAdmin)) {
                    throw new Exception(mysqli_stmt_error($stmtAdmin));
                }
                mysqli_stmt_close($stmtAdmin);

                $stmtLimpiar = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_administrador = ?");
                mysqli_stmt_bind_param($stmtLimpiar, "i", $id);
                if (!mysqli_stmt_execute($stmtLimpiar)) {
                    throw new Exception(mysqli_stmt_error($stmtLimpiar));
                }
                mysqli_stmt_close($stmtLimpiar);

                $stmtInstitucion = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                mysqli_stmt_bind_param($stmtInstitucion, "ii", $id, $idInstitucion);
                if (!mysqli_stmt_execute($stmtInstitucion)) {
                    throw new Exception(mysqli_stmt_error($stmtInstitucion));
                }
                mysqli_stmt_close($stmtInstitucion);

                mysqli_commit($conexion);
                $mensaje = 'Administrador actualizado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo actualizar el administrador.';
                $tipoMensaje = 'danger';
            }
                }
            } else {
                mysqli_begin_transaction($conexion);
                try {
                    if ($idInstitucion > 0) {
                        $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET rut = ?, correo = ?, id_institucion = ? WHERE id_usuario = ?");
                        mysqli_stmt_bind_param($stmtUsuario, "ssii", $rutNormalizado, $correo, $idInstitucion, $id);
                    } else {
                        $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET rut = ?, correo = ?, id_institucion = NULL WHERE id_usuario = ?");
                        mysqli_stmt_bind_param($stmtUsuario, "ssi", $rutNormalizado, $correo, $id);
                    }
                    if (!mysqli_stmt_execute($stmtUsuario)) {
                        throw new Exception(mysqli_stmt_error($stmtUsuario));
                    }
                    mysqli_stmt_close($stmtUsuario);

                    $stmtAdmin = mysqli_prepare($conexion, "UPDATE administrador SET nombre = ?, apellido = ? WHERE id_usuario = ?");
                    mysqli_stmt_bind_param($stmtAdmin, "ssi", $nombre, $apellido, $id);
                    if (!mysqli_stmt_execute($stmtAdmin)) {
                        throw new Exception(mysqli_stmt_error($stmtAdmin));
                    }
                    mysqli_stmt_close($stmtAdmin);

                    $stmtLimpiar = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_administrador = ?");
                    mysqli_stmt_bind_param($stmtLimpiar, "i", $id);
                    if (!mysqli_stmt_execute($stmtLimpiar)) {
                        throw new Exception(mysqli_stmt_error($stmtLimpiar));
                    }
                    mysqli_stmt_close($stmtLimpiar);

                    $stmtInstitucion = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    mysqli_stmt_bind_param($stmtInstitucion, "ii", $id, $idInstitucion);
                    if (!mysqli_stmt_execute($stmtInstitucion)) {
                        throw new Exception(mysqli_stmt_error($stmtInstitucion));
                    }
                    mysqli_stmt_close($stmtInstitucion);

                    mysqli_commit($conexion);
                    $mensaje = 'Administrador actualizado correctamente.';
                } catch (Throwable $e) {
                    mysqli_rollback($conexion);
                    $mensaje = 'No se pudo actualizar el administrador.';
                    $tipoMensaje = 'danger';
                }
            }
        } else {
            $mensaje = 'Completa todos los datos para actualizar.';
            $tipoMensaje = 'danger';
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        if ($id > 0) {
            mysqli_begin_transaction($conexion);
            try {
                $stmtAdmin = mysqli_prepare($conexion, "DELETE FROM administrador WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmtAdmin, "i", $id);
                if (!mysqli_stmt_execute($stmtAdmin)) {
                    throw new Exception(mysqli_stmt_error($stmtAdmin));
                }
                mysqli_stmt_close($stmtAdmin);

                $stmtUsuario = mysqli_prepare($conexion, "DELETE FROM usuario WHERE id_usuario = ?");
                mysqli_stmt_bind_param($stmtUsuario, "i", $id);
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                mysqli_stmt_close($stmtUsuario);

                mysqli_commit($conexion);
                $mensaje = 'Administrador eliminado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo eliminar el administrador.';
                $tipoMensaje = 'danger';
            }
        }
    }
}

$consulta = mysqli_query($conexion, "SELECT u.id_usuario, u.correo, u.estado_cuenta, u.rut, i.nombre AS institucion, a.nombre AS nombre_admin, a.apellido
FROM usuario u
INNER JOIN administrador a ON a.id_usuario = u.id_usuario
LEFT JOIN institucion i ON i.id_institucion = u.id_institucion
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE r.nombre_rol = 'Administrador'
ORDER BY u.id_usuario DESC");
if ($consulta) {
    while ($fila = mysqli_fetch_assoc($consulta)) {
        $administradores[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Administradores - SuperAdmin</title>
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
            <a class="nav-link" href="instituciones.php"><i class="bi bi-person-gear me-2"></i> Gestionar instituciones</a>
            <a class="nav-link active" href="administradores.php"><i class="bi bi-person-badge me-2"></i> Gestionar administradores</a>
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php"><i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestión de Administradores</h2>
                <p class="text-muted mb-0">Crear, activar, desactivar y eliminar administradores.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="bi bi-person-plus me-2"></i>Nuevo administrador
            </button>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <?php if (count($administradores) > 0): ?>
                <?php foreach ($administradores as $admin): ?>
                    <div class="col-12">
                        <div class="institution-card p-3">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="mb-0"><?= htmlspecialchars($admin['nombre_admin'] . ' ' . $admin['apellido']) ?></h5>
                                        <span class="badge bg-<?= $admin['estado_cuenta'] === 'activa' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($admin['estado_cuenta']) ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted">Correo: <?= htmlspecialchars($admin['correo']) ?></div>
                                    <div class="small text-muted">RUT: <?= htmlspecialchars(rut_formatear((string) $admin['rut'])) ?></div>
                                    <div class="small text-muted">Institución: <?= htmlspecialchars($admin['institucion'] ?? 'Sin asignar') ?></div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar"
                                        data-id="<?= (int) $admin['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($admin['nombre_admin'], ENT_QUOTES) ?>"
                                        data-apellido="<?= htmlspecialchars($admin['apellido'], ENT_QUOTES) ?>"
                                        data-rut="<?= htmlspecialchars(rut_formatear((string) $admin['rut']), ENT_QUOTES) ?>"
                                        data-correo="<?= htmlspecialchars($admin['correo'], ENT_QUOTES) ?>"
                                        data-institucion="<?= (int) ($admin['id_institucion'] ?? 0) ?>"
                                    >
                                        Editar
                                    </button>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="cambiar_estado">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $admin['id_usuario'] ?>">
                                        <input type="hidden" name="estado" value="<?= $admin['estado_cuenta'] === 'activa' ? 'inactiva' : 'activa' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $admin['estado_cuenta'] === 'activa' ? 'warning' : 'success' ?>" type="submit">
                                            <?= $admin['estado_cuenta'] === 'activa' ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este administrador?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $admin['id_usuario'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card-custom p-4 text-center text-muted">No hay administradores registrados.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo administrador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="crear">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">RUT</label>
                                <input type="text" name="rut" class="form-control" placeholder="12.345.678-5" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" name="correo" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="contrasena" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Institución</label>
                                <select name="id_institucion" class="form-select">
                                    <option value="0" selected>Sin institución</option>
                                    <?php foreach ($instituciones as $institucion): ?>
                                        <option value="<?= (int) $institucion['id_institucion'] ?>"><?= htmlspecialchars($institucion['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar administrador</button>
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
                        <h5 class="modal-title">Editar administrador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id_usuario" id="editar_id_usuario">
                        <input type="hidden" name="rut_original" id="editar_rut_original">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input type="text" name="apellido" id="editar_apellido" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">RUT</label>
                                <input type="text" name="rut" id="editar_rut" class="form-control" placeholder="12.345.678-5" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" name="correo" id="editar_correo" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Institución</label>
                                <select name="id_institucion" id="editar_id_institucion" class="form-select">
                                    <option value="0" selected>Sin institución</option>
                                    <?php foreach ($instituciones as $institucion): ?>
                                        <option value="<?= (int) $institucion['id_institucion'] ?>"><?= htmlspecialchars($institucion['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar administrador</button>
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
            document.getElementById('editar_id_usuario').value = boton.getAttribute('data-id') || '';
            document.getElementById('editar_rut_original').value = boton.getAttribute('data-rut') || '';
            document.getElementById('editar_nombre').value = boton.getAttribute('data-nombre') || '';
            document.getElementById('editar_apellido').value = boton.getAttribute('data-apellido') || '';
            document.getElementById('editar_rut').value = boton.getAttribute('data-rut') || '';
            document.getElementById('editar_correo').value = boton.getAttribute('data-correo') || '';
            document.getElementById('editar_id_institucion').value = boton.getAttribute('data-institucion') || '';
        });
    </script>
</body>
</html>
