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

function admin_rol_requiere_carrera_local(string $nombreRol): bool
{
    return in_array($nombreRol, ['Estudiante', 'Coordinador', 'Directivo'], true);
}

function admin_rol_soportado_local(string $nombreRol): bool
{
    return in_array($nombreRol, ['Estudiante', 'Coordinador', 'Directivo'], true);
}

function admin_reiniciar_perfiles_usuario(mysqli $conexion, int $idUsuario): void
{
    $tablas = ['estudiante', 'coordinador', 'directivo', 'administrador', 'empresa', 'superadministrador'];
    foreach ($tablas as $tabla) {
        $stmt = mysqli_prepare($conexion, "DELETE FROM {$tabla} WHERE id_usuario = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

$mensaje = '';
$tipoMensaje = 'success';
$idInstitucionActual = admin_obtener_id_institucion_actual($conexion);
$nombreInstitucionActual = 'Sin institucion';
$usuarios = [];

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
}

$roles = $idInstitucionActual > 0 ? admin_query_all(
    $conexion,
    "SELECT id_rol, nombre_rol
    FROM rol
    WHERE estado = 'activo'
      AND nombre_rol IN ('Estudiante', 'Coordinador', 'Directivo')
    ORDER BY nombre_rol"
) : [];

$carreras = $idInstitucionActual > 0 ? admin_query_all(
    $conexion,
    "SELECT id_carrera, nombre_carrera, codigo
    FROM carrera
    WHERE id_institucion = " . (int) $idInstitucionActual . "
    ORDER BY nombre_carrera"
) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($idInstitucionActual <= 0) {
        $mensaje = 'No se pudo identificar la institucion del administrador. Inicia sesion nuevamente.';
        $tipoMensaje = 'danger';
    } elseif ($accion === 'crear') {
        $nombre = admin_limpiar_texto_local($_POST['nombre'] ?? '');
        $apellido = admin_limpiar_texto_local($_POST['apellido'] ?? '');
        $rut = admin_limpiar_texto_local($_POST['rut'] ?? '');
        $correo = admin_limpiar_texto_local($_POST['correo'] ?? '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $confirmarContrasena = (string) ($_POST['confirmar_contrasena'] ?? '');
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idCarrera = (int) ($_POST['id_carrera'] ?? 0);
        $estado = 'activa';

        $rolSeleccionado = admin_buscar_por_id($roles, $idRol, 'id_rol');
        $carreraSeleccionada = $idCarrera > 0 ? admin_buscar_por_id($carreras, $idCarrera, 'id_carrera') : null;

        if ($nombre === '' || $apellido === '' || $rut === '' || $correo === '' || $contrasena === '' || $confirmarContrasena === '') {
            $mensaje = 'Completa todos los datos del usuario.';
            $tipoMensaje = 'danger';
        } elseif ($contrasena !== $confirmarContrasena) {
            $mensaje = 'Las contrasenas no coinciden.';
            $tipoMensaje = 'danger';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo ingresado no es valido.';
            $tipoMensaje = 'danger';
        } elseif (!$rolSeleccionado) {
            $mensaje = 'Selecciona un rol valido.';
            $tipoMensaje = 'danger';
        } elseif (!admin_rol_soportado_local((string) $rolSeleccionado['nombre_rol'])) {
            $mensaje = 'El rol seleccionado no esta habilitado en este modulo.';
            $tipoMensaje = 'danger';
        } elseif (admin_rol_requiere_carrera_local((string) $rolSeleccionado['nombre_rol']) && !$carreraSeleccionada) {
            $mensaje = 'Debes seleccionar una carrera valida para ese rol.';
            $tipoMensaje = 'danger';
        } elseif ($carreraSeleccionada && (int) $carreraSeleccionada['id_carrera'] <= 0) {
            $mensaje = 'La carrera seleccionada no pertenece a tu institucion.';
            $tipoMensaje = 'danger';
        } else {
            mysqli_begin_transaction($conexion);
            try {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmtUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, id_institucion, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmtUsuario) {
                    throw new Exception('No se pudo preparar el alta del usuario.');
                }
                mysqli_stmt_bind_param($stmtUsuario, 'iissss', $idRol, $idInstitucionActual, $rut, $correo, $hash, $estado);
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                $idUsuario = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmtUsuario);

                $nombreRol = (string) $rolSeleccionado['nombre_rol'];
                if ($nombreRol === 'Administrador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO administrador (id_usuario, nombre, apellido) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iss', $idUsuario, $nombre, $apellido);
                } elseif ($nombreRol === 'Estudiante') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO estudiante (id_usuario, id_carrera, nombre, apellido, nivel_curricular, habilidades, ramos_aprobados) VALUES (?, ?, ?, ?, 1, '', 0)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRol === 'Coordinador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO coordinador (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRol === 'Directivo') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO directivo (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } else {
                    throw new Exception('Rol no soportado.');
                }

                if (!$stmtPerfil) {
                    throw new Exception('No se pudo preparar el perfil del usuario.');
                }
                if (!mysqli_stmt_execute($stmtPerfil)) {
                    throw new Exception(mysqli_stmt_error($stmtPerfil));
                }
                mysqli_stmt_close($stmtPerfil);

                if ($nombreRol === 'Administrador') {
                    $stmtInstitucion = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    if ($stmtInstitucion) {
                        mysqli_stmt_bind_param($stmtInstitucion, 'ii', $idUsuario, $idInstitucionActual);
                        mysqli_stmt_execute($stmtInstitucion);
                        mysqli_stmt_close($stmtInstitucion);
                    }
                }

                mysqli_commit($conexion);
                admin_registrar_auditoria($conexion, 'Creacion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Rol: ' . $nombreRol . ' | Institucion: ' . $idInstitucionActual);
                $mensaje = 'Usuario creado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo crear el usuario.';
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($accion === 'editar') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = admin_limpiar_texto_local($_POST['nombre'] ?? '');
        $apellido = admin_limpiar_texto_local($_POST['apellido'] ?? '');
        $rut = admin_limpiar_texto_local($_POST['rut'] ?? '');
        $correo = admin_limpiar_texto_local($_POST['correo'] ?? '');
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idCarrera = (int) ($_POST['id_carrera'] ?? 0);
        $estado = ($_POST['estado_cuenta'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $confirmarContrasena = (string) ($_POST['confirmar_contrasena'] ?? '');

        $usuarioActual = null;
        $stmtActual = mysqli_prepare($conexion, "SELECT u.id_usuario, u.id_rol, u.id_institucion
            FROM usuario u
            WHERE u.id_usuario = ? AND u.id_institucion = ?
            LIMIT 1");
        if ($stmtActual) {
            mysqli_stmt_bind_param($stmtActual, 'ii', $idUsuario, $idInstitucionActual);
            mysqli_stmt_execute($stmtActual);
            $resultadoActual = mysqli_stmt_get_result($stmtActual);
            $usuarioActual = $resultadoActual ? mysqli_fetch_assoc($resultadoActual) : null;
            mysqli_stmt_close($stmtActual);
        }

        $rolSeleccionado = admin_buscar_por_id($roles, $idRol, 'id_rol');
        $carreraSeleccionada = $idCarrera > 0 ? admin_buscar_por_id($carreras, $idCarrera, 'id_carrera') : null;

        if (!$usuarioActual) {
            $mensaje = 'El usuario no pertenece a tu institucion o no existe.';
            $tipoMensaje = 'danger';
        } elseif ($nombre === '' || $apellido === '' || $rut === '' || $correo === '') {
            $mensaje = 'Completa los campos obligatorios para editar.';
            $tipoMensaje = 'danger';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo ingresado no es valido.';
            $tipoMensaje = 'danger';
        } elseif (!$rolSeleccionado) {
            $mensaje = 'Selecciona un rol valido.';
            $tipoMensaje = 'danger';
        } elseif (!admin_rol_soportado_local((string) $rolSeleccionado['nombre_rol'])) {
            $mensaje = 'El rol seleccionado no esta habilitado en este modulo.';
            $tipoMensaje = 'danger';
        } elseif (admin_rol_requiere_carrera_local((string) $rolSeleccionado['nombre_rol']) && !$carreraSeleccionada) {
            $mensaje = 'Debes seleccionar una carrera valida para ese rol.';
            $tipoMensaje = 'danger';
        } elseif ($contrasena !== '' && $contrasena !== $confirmarContrasena) {
            $mensaje = 'Las contrasenas no coinciden.';
            $tipoMensaje = 'danger';
        } else {
            mysqli_begin_transaction($conexion);
            try {
                if ($contrasena !== '') {
                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_rol = ?, rut = ?, correo = ?, estado_cuenta = ?, contrasena_hash = ?, id_institucion = ? WHERE id_usuario = ?");
                    if (!$stmtUsuario) {
                        throw new Exception('No se pudo preparar la actualizacion del usuario.');
                    }
                    mysqli_stmt_bind_param($stmtUsuario, 'issssii', $idRol, $rut, $correo, $estado, $hash, $idInstitucionActual, $idUsuario);
                } else {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_rol = ?, rut = ?, correo = ?, estado_cuenta = ?, id_institucion = ? WHERE id_usuario = ?");
                    if (!$stmtUsuario) {
                        throw new Exception('No se pudo preparar la actualizacion del usuario.');
                    }
                    mysqli_stmt_bind_param($stmtUsuario, 'isssii', $idRol, $rut, $correo, $estado, $idInstitucionActual, $idUsuario);
                }
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                mysqli_stmt_close($stmtUsuario);

                admin_reiniciar_perfiles_usuario($conexion, $idUsuario);

                $nombreRol = (string) $rolSeleccionado['nombre_rol'];
                if ($nombreRol === 'Administrador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO administrador (id_usuario, nombre, apellido) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iss', $idUsuario, $nombre, $apellido);
                } elseif ($nombreRol === 'Estudiante') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO estudiante (id_usuario, id_carrera, nombre, apellido, nivel_curricular, habilidades, ramos_aprobados) VALUES (?, ?, ?, ?, 1, '', 0)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRol === 'Coordinador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO coordinador (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRol === 'Directivo') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO directivo (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } else {
                    throw new Exception('Rol no soportado.');
                }

                if (!$stmtPerfil) {
                    throw new Exception('No se pudo preparar el perfil del usuario.');
                }
                if (!mysqli_stmt_execute($stmtPerfil)) {
                    throw new Exception(mysqli_stmt_error($stmtPerfil));
                }
                mysqli_stmt_close($stmtPerfil);

                $stmtClearAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_institucion = ? AND id_administrador = ?");
                if ($stmtClearAdmin) {
                    mysqli_stmt_bind_param($stmtClearAdmin, 'ii', $idInstitucionActual, $idUsuario);
                    mysqli_stmt_execute($stmtClearAdmin);
                    mysqli_stmt_close($stmtClearAdmin);
                }

                if ($nombreRol === 'Administrador') {
                    $stmtSetAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    if ($stmtSetAdmin) {
                        mysqli_stmt_bind_param($stmtSetAdmin, 'ii', $idUsuario, $idInstitucionActual);
                        mysqli_stmt_execute($stmtSetAdmin);
                        mysqli_stmt_close($stmtSetAdmin);
                    }
                }

                mysqli_commit($conexion);
                admin_registrar_auditoria($conexion, 'Edicion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Rol: ' . $nombreRol . ' | Estado: ' . $estado);
                $mensaje = 'Usuario actualizado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo actualizar el usuario.';
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($accion === 'estado') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';

        $stmt = mysqli_prepare($conexion, "UPDATE usuario SET estado_cuenta = ? WHERE id_usuario = ? AND id_institucion = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sii', $estado, $idUsuario, $idInstitucionActual);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = $estado === 'activa' ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
                admin_registrar_auditoria($conexion, 'Cambio de estado de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Estado: ' . $estado);
            } else {
                $mensaje = 'No se pudo cambiar el estado.';
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($accion === 'eliminar') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

        mysqli_begin_transaction($conexion);
        try {
            $stmtValidar = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND id_institucion = ? LIMIT 1");
            if (!$stmtValidar) {
                throw new Exception('No se pudo validar el usuario.');
            }
            mysqli_stmt_bind_param($stmtValidar, 'ii', $idUsuario, $idInstitucionActual);
            mysqli_stmt_execute($stmtValidar);
            $resultadoValidar = mysqli_stmt_get_result($stmtValidar);
            $usuarioValido = $resultadoValidar ? mysqli_fetch_assoc($resultadoValidar) : null;
            mysqli_stmt_close($stmtValidar);

            if (!$usuarioValido) {
                throw new Exception('El usuario no existe o no pertenece a tu institucion.');
            }

            $stmtClearAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_institucion = ? AND id_administrador = ?");
            if ($stmtClearAdmin) {
                mysqli_stmt_bind_param($stmtClearAdmin, 'ii', $idInstitucionActual, $idUsuario);
                mysqli_stmt_execute($stmtClearAdmin);
                mysqli_stmt_close($stmtClearAdmin);
            }

            admin_reiniciar_perfiles_usuario($conexion, $idUsuario);

            $stmtUsuario = mysqli_prepare($conexion, "DELETE FROM usuario WHERE id_usuario = ? AND id_institucion = ?");
            if (!$stmtUsuario) {
                throw new Exception('No se pudo preparar la eliminacion.');
            }
            mysqli_stmt_bind_param($stmtUsuario, 'ii', $idUsuario, $idInstitucionActual);
            if (!mysqli_stmt_execute($stmtUsuario)) {
                throw new Exception(mysqli_stmt_error($stmtUsuario));
            }
            mysqli_stmt_close($stmtUsuario);

            mysqli_commit($conexion);
            admin_registrar_auditoria($conexion, 'Eliminacion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario);
            $mensaje = 'Usuario eliminado correctamente.';
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            $mensaje = 'No se pudo eliminar el usuario.';
            $tipoMensaje = 'danger';
        }
    }
}

if ($idInstitucionActual > 0) {
    $usuarios = admin_query_all(
        $conexion,
        "SELECT u.id_usuario, u.id_rol, u.rut, u.correo, u.estado_cuenta, u.fecha_creacion, r.nombre_rol,
            COALESCE(e.nombre, c.nombre, d.nombre, a.nombre, em.nombre_empresa, '') AS nombre,
            COALESCE(e.apellido, c.apellido, d.apellido, a.apellido, '') AS apellido,
            COALESCE(CONCAT(e.nombre, ' ', e.apellido), CONCAT(c.nombre, ' ', c.apellido), CONCAT(d.nombre, ' ', d.apellido), CONCAT(a.nombre, ' ', a.apellido), em.nombre_empresa, 'Usuario') AS nombre_completo,
            COALESCE(e.id_carrera, c.id_carrera, d.id_carrera, 0) AS id_carrera,
            COALESCE(ec.nombre_carrera, cc.nombre_carrera, dc.nombre_carrera, 'Sin carrera') AS carrera_nombre
        FROM usuario u
        LEFT JOIN rol r ON r.id_rol = u.id_rol
        LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
        LEFT JOIN coordinador c ON c.id_usuario = u.id_usuario
        LEFT JOIN directivo d ON d.id_usuario = u.id_usuario
        LEFT JOIN administrador a ON a.id_usuario = u.id_usuario
        LEFT JOIN empresa em ON em.id_usuario = u.id_usuario
        LEFT JOIN carrera ec ON ec.id_carrera = e.id_carrera
        LEFT JOIN carrera cc ON cc.id_carrera = c.id_carrera
        LEFT JOIN carrera dc ON dc.id_carrera = d.id_carrera
        WHERE u.id_institucion = " . (int) $idInstitucionActual . "
          AND r.nombre_rol IN ('Estudiante', 'Coordinador', 'Directivo')
        ORDER BY u.id_usuario DESC"
    );
}

admin_layout_header('Gestion de Usuarios', 'Alta, edicion, cambio de estado y eliminacion por institucion.');
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
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear" <?= $idInstitucionActual <= 0 ? 'disabled' : '' ?>>
        <i class="bi bi-person-plus me-2"></i>Nuevo usuario
    </button>
</div>

<?php if ($idInstitucionActual <= 0): ?>
    <div class="alert alert-warning">No se pudo identificar la institucion del administrador. La creacion y edicion de usuarios esta deshabilitada.</div>
<?php endif; ?>

<div class="card card-custom">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Carrera</th>
                        <th>Fecha creacion</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= (int) $usuario['id_usuario'] ?></td>
                                <td><?= admin_e($usuario['nombre_completo']) ?></td>
                                <td><?= admin_e($usuario['correo']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= admin_e($usuario['nombre_rol'] ?? 'Sin rol') ?></span></td>
                                <td><?= admin_e($usuario['carrera_nombre'] ?? 'Sin carrera') ?></td>
                                <td><?= admin_e((string) ($usuario['fecha_creacion'] ?? 'No disponible')) ?></td>
                                <td><span class="badge bg-<?= admin_badge_estado($usuario['estado_cuenta']) ?>"><?= admin_e($usuario['estado_cuenta']) ?></span></td>
                                <td class="d-flex gap-2 flex-wrap">
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar"
                                        data-id="<?= (int) $usuario['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($usuario['nombre_completo'], ENT_QUOTES) ?>"
                                        data-nombre-usuario="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES) ?>"
                                        data-apellido-usuario="<?= htmlspecialchars($usuario['apellido'], ENT_QUOTES) ?>"
                                        data-correo="<?= htmlspecialchars($usuario['correo'], ENT_QUOTES) ?>"
                                        data-rut="<?= htmlspecialchars($usuario['rut'], ENT_QUOTES) ?>"
                                        data-rol="<?= (int) (($usuario['id_rol'] ?? 0)) ?>"
                                        data-carrera="<?= (int) (($usuario['id_carrera'] ?? 0)) ?>"
                                        data-estado="<?= htmlspecialchars($usuario['estado_cuenta'], ENT_QUOTES) ?>"
                                    >
                                        Editar
                                    </button>

                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="estado">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
                                        <input type="hidden" name="estado" value="<?= $usuario['estado_cuenta'] === 'activa' ? 'inactiva' : 'activa' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $usuario['estado_cuenta'] === 'activa' ? 'warning' : 'success' ?>" type="submit">
                                            <?= $usuario['estado_cuenta'] === 'activa' ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>

                                    <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados para esta institucion.</td>
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
                    <h5 class="modal-title">Nuevo usuario</h5>
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
                            <input type="text" name="rut" class="form-control" required>
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
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="confirmar_contrasena" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="crear_id_rol" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= (int) $rol['id_rol'] ?>"><?= admin_e($rol['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera</label>
                            <select name="id_carrera" id="crear_id_carrera" class="form-select">
                                <option value="0">Sin carrera</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?= (int) $carrera['id_carrera'] ?>"><?= admin_e($carrera['nombre_carrera'] . ' (' . $carrera['codigo'] . ')') ?></option>
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
                    <button type="submit" class="btn btn-primary">Guardar usuario</button>
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
                    <h5 class="modal-title">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_usuario" id="editar_id_usuario">
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
                            <input type="text" name="rut" id="editar_rut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="editar_correo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="contrasena" id="editar_contrasena" class="form-control" placeholder="Dejar en blanco para mantener">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="confirmar_contrasena" id="editar_confirmar_contrasena" class="form-control" placeholder="Solo si cambias la contraseña">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="editar_id_rol" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= (int) $rol['id_rol'] ?>"><?= admin_e($rol['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera</label>
                            <select name="id_carrera" id="editar_id_carrera" class="form-select">
                                <option value="0">Sin carrera</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?= (int) $carrera['id_carrera'] ?>"><?= admin_e($carrera['nombre_carrera'] . ' (' . $carrera['codigo'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="estado_cuenta" id="editar_estado_cuenta" class="form-select">
                                <option value="activa">Activa</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institucion</label>
                            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function ajustarCarreraSegunRol(selectRolId, selectCarreraId) {
        const rolSelect = document.getElementById(selectRolId);
        const carreraSelect = document.getElementById(selectCarreraId);
        if (!rolSelect || !carreraSelect) {
            return;
        }

        const rolTexto = rolSelect.options[rolSelect.selectedIndex]?.text || '';
        const requiereCarrera = ['Estudiante', 'Coordinador', 'Directivo'].includes(rolTexto);
        carreraSelect.required = requiereCarrera;
        carreraSelect.disabled = false;
    }

    document.getElementById('crear_id_rol')?.addEventListener('change', function () {
        ajustarCarreraSegunRol('crear_id_rol', 'crear_id_carrera');
    });

    document.getElementById('editar_id_rol')?.addEventListener('change', function () {
        ajustarCarreraSegunRol('editar_id_rol', 'editar_id_carrera');
    });

    const modalEditar = document.getElementById('modalEditar');
    modalEditar.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('editar_id_usuario').value = boton.getAttribute('data-id') || '';
        document.getElementById('editar_nombre').value = boton.getAttribute('data-nombre-usuario') || '';
        document.getElementById('editar_apellido').value = boton.getAttribute('data-apellido-usuario') || '';
        document.getElementById('editar_correo').value = boton.getAttribute('data-correo') || '';
        document.getElementById('editar_rut').value = boton.getAttribute('data-rut') || '';
        document.getElementById('editar_id_rol').value = boton.getAttribute('data-rol') || '';
        document.getElementById('editar_id_carrera').value = boton.getAttribute('data-carrera') || '0';
        document.getElementById('editar_estado_cuenta').value = boton.getAttribute('data-estado') || 'activa';
        document.getElementById('editar_contrasena').value = '';
        document.getElementById('editar_confirmar_contrasena').value = '';
        ajustarCarreraSegunRol('editar_id_rol', 'editar_id_carrera');
    });

    ajustarCarreraSegunRol('crear_id_rol', 'crear_id_carrera');
</script>
<?php admin_layout_footer(); ?>
