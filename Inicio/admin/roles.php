<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idRol = (int) ($_POST['id_rol'] ?? 0);
    $nombre = trim((string) ($_POST['nombre_rol'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $estado = ($_POST['estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';

    if ($accion === 'crear' && $nombre !== '') {
        $stmt = mysqli_prepare($conexion, "INSERT INTO rol (nombre_rol, descripcion, estado) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $nombre, $descripcion, $estado);
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = 'Rol creado correctamente.';
            admin_registrar_auditoria($conexion, 'Creacion de rol', 'roles', 'Rol: ' . $nombre . ' | Estado: ' . $estado);
        } else {
            $mensaje = 'No se pudo crear el rol.';
            $tipoMensaje = 'danger';
        }
        mysqli_stmt_close($stmt);
    }

    if ($accion === 'estado' && $idRol > 0) {
        $stmt = mysqli_prepare($conexion, "UPDATE rol SET estado = ? WHERE id_rol = ?");
        mysqli_stmt_bind_param($stmt, 'si', $estado, $idRol);
        if (mysqli_stmt_execute($stmt)) {
            admin_registrar_auditoria($conexion, 'Cambio de estado de rol', 'roles', 'ID rol: ' . $idRol . ' | Estado: ' . $estado);
        }
        mysqli_stmt_close($stmt);
        $mensaje = 'Estado del rol actualizado.';
    }
}

$roles = admin_query_all($conexion, "SELECT id_rol, nombre_rol, descripcion, estado FROM rol ORDER BY id_rol DESC");
admin_layout_header('Gestion de Roles', 'CRUD simple para el catalogo de roles.');
?>
<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= admin_e($tipoMensaje) ?> alert-dismissible fade show" role="alert">
        <?= admin_e($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card card-custom p-3 mb-4">
    <form method="post" class="row g-3">
        <input type="hidden" name="accion" value="crear">
        <div class="col-md-4"><input type="text" name="nombre_rol" class="form-control" placeholder="Nombre del rol" required></div>
        <div class="col-md-5"><input type="text" name="descripcion" class="form-control" placeholder="Descripcion"></div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <div class="col-md-1 d-grid"><button class="btn btn-primary" type="submit">Crear</button></div>
    </form>
</div>

<div class="card card-custom">
    <div class="card-body table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>ID</th><th>Nombre</th><th>Descripcion</th><th>Estado</th><th>Accion</th></tr></thead>
            <tbody>
                <?php foreach ($roles as $rol): ?>
                    <tr>
                        <td><?= (int) $rol['id_rol'] ?></td>
                        <td><?= admin_e($rol['nombre_rol']) ?></td>
                        <td><?= admin_e($rol['descripcion'] ?? '') ?></td>
                        <td><span class="badge bg-<?= admin_badge_estado($rol['estado']) ?>"><?= admin_e($rol['estado']) ?></span></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="accion" value="estado">
                                <input type="hidden" name="id_rol" value="<?= (int) $rol['id_rol'] ?>">
                                <input type="hidden" name="estado" value="<?= $rol['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Cambiar estado</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_layout_footer(); ?>
