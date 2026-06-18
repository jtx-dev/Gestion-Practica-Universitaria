<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$mensaje = '';
$tipoMensaje = 'success';
$idInstitucionActual = admin_obtener_id_institucion_actual($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre']);
        $id_carrera = (int)$_POST['id_carrera'];
        $tipo = $_POST['tipo'];

        if (!empty($nombre) && $id_carrera > 0) {
            $stmt = mysqli_prepare($conexion, "INSERT INTO competencias (nombre, id_carrera, tipo) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sis", $nombre, $id_carrera, $tipo);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Competencia creada con éxito.";
            } else {
                $mensaje = "Error al crear competencia.";
                $tipoMensaje = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($accion === 'eliminar') {
        $id = (int)$_POST['id'];
        mysqli_query($conexion, "DELETE FROM competencias WHERE id = $id");
        $mensaje = "Competencia eliminada.";
    }
}

$carreras = admin_query_all($conexion, "SELECT id_carrera, nombre_carrera FROM carrera WHERE id_institucion = $idInstitucionActual");
$competencias = admin_query_all($conexion, "SELECT c.*, ca.nombre_carrera FROM competencias c JOIN carrera ca ON c.id_carrera = ca.id_carrera WHERE ca.id_institucion = $idInstitucionActual ORDER BY ca.nombre_carrera, c.nombre");

admin_layout_header('Mantenedor de Competencias', 'Define las etiquetas que usarán alumnos y empresas para el matching.');
?>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card card-custom mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Nueva Competencia / Etiqueta</h5>
        <form method="POST" class="row g-3">
            <input type="hidden" name="accion" value="crear">
            <div class="col-md-4">
                <label class="form-label">Nombre de la Competencia</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: RCP Avanzado" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Carrera</label>
                <select name="id_carrera" class="form-select" required>
                    <option value="">Seleccionar Carrera</option>
                    <?php foreach ($carreras as $ca): ?>
                        <option value="<?= $ca['id_carrera'] ?>"><?= htmlspecialchars($ca['nombre_carrera']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="técnica">Técnica</option>
                    <option value="blanda">Blanda</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Agregar</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Listado de Competencias por Carrera</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Competencia</th>
                        <th>Tipo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($competencias)): ?>
                        <?php foreach ($competencias as $comp): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($comp['nombre_carrera']) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($comp['nombre']) ?></td>
                                <td><?= ucfirst($comp['tipo']) ?></td>
                                <td class="text-end">
                                    <form method="POST" onsubmit="return confirm('¿Eliminar esta competencia?');" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $comp['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No hay competencias definidas aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php admin_layout_footer(); ?>
