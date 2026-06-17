<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$mensaje = '';
$tipoMensaje = 'success';
$idInstitucionActual = admin_obtener_id_institucion_actual($conexion);
$nombreInstitucionActual = 'Sin institucion';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = trim((string) ($_POST['nombre_carrera'] ?? ''));
    $codigo = trim((string) ($_POST['codigo'] ?? ''));

    if ($accion === 'crear' && $nombre !== '') {
        if ($idInstitucionActual <= 0) {
            $mensaje = 'No se pudo determinar la institucion del administrador. Inicia sesion nuevamente.';
            $tipoMensaje = 'danger';
        } else {
            $stmt = mysqli_prepare($conexion, "INSERT INTO carrera (nombre_carrera, codigo, id_institucion) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $codigo, $idInstitucionActual);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = 'Carrera creada correctamente.';
                admin_registrar_auditoria($conexion, 'Creacion de carrera', 'carreras', 'Carrera: ' . $nombre . ' | Codigo: ' . $codigo);
            } else {
                $mensaje = 'No se pudo crear la carrera.';
                $tipoMensaje = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$carreras = $idInstitucionActual > 0 ? admin_query_all(
    $conexion,
    "SELECT c.id_carrera, c.nombre_carrera, c.codigo, i.nombre AS institucion
     FROM carrera c
     LEFT JOIN institucion i ON i.id_institucion = c.id_institucion
     WHERE c.id_institucion = " . (int) $idInstitucionActual . "
     ORDER BY c.id_carrera DESC"
) : [];

admin_layout_header('Gestion de Carreras', 'Solo puedes crear carreras para tu institucion: ' . $nombreInstitucionActual);
?>
<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= admin_e($tipoMensaje) ?>"><?= admin_e($mensaje) ?></div>
<?php endif; ?>

<div class="card card-custom p-3 mb-4">
    <?php if ($idInstitucionActual <= 0): ?>
        <div class="alert alert-warning mb-3">No se pudo identificar la institucion del administrador. La creacion de carreras esta deshabilitada.</div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="accion" value="crear">
        <div class="col-md-5">
            <input type="text" name="nombre_carrera" class="form-control" placeholder="Nombre carrera" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="codigo" class="form-control" placeholder="Codigo">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-primary" type="submit" <?= $idInstitucionActual <= 0 ? 'disabled' : '' ?>>Crear</button>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="card-body table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Codigo</th>
                    <th>Institucion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carreras as $carrera): ?>
                    <tr>
                        <td><?= (int) $carrera['id_carrera'] ?></td>
                        <td><?= admin_e($carrera['nombre_carrera']) ?></td>
                        <td><?= admin_e($carrera['codigo'] ?? '') ?></td>
                        <td><?= admin_e($carrera['institucion'] ?? 'Sin institucion') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_layout_footer(); ?>
