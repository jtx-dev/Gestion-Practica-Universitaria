<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

$mensaje = '';
$tipoMensaje = 'success';
$asignacionesHabilitada = admin_table_exists($conexion, 'asignacion');

$estudiantes = admin_query_all($conexion, "SELECT e.id_usuario, CONCAT(e.nombre, ' ', e.apellido) AS nombre, e.id_carrera, c.nombre_carrera FROM estudiante e LEFT JOIN carrera c ON c.id_carrera = e.id_carrera ORDER BY e.nombre");
$coordinadores = admin_query_all($conexion, "SELECT c.id_usuario, CONCAT(c.nombre, ' ', c.apellido) AS nombre, c.id_carrera, ca.nombre_carrera FROM coordinador c LEFT JOIN carrera ca ON ca.id_carrera = c.id_carrera ORDER BY c.nombre");
$directivos = admin_query_all($conexion, "SELECT d.id_usuario, CONCAT(d.nombre, ' ', d.apellido) AS nombre, d.id_carrera, ca.nombre_carrera FROM directivo d LEFT JOIN carrera ca ON ca.id_carrera = d.id_carrera ORDER BY d.nombre");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstudiante = (int) ($_POST['id_estudiante'] ?? 0);
    $idCoordinador = (int) ($_POST['id_coordinador'] ?? 0);
    $idDirectivo = (int) ($_POST['id_directivo'] ?? 0);

    $est = array_values(array_filter($estudiantes, fn($x) => (int) $x['id_usuario'] === $idEstudiante))[0] ?? null;
    $coor = array_values(array_filter($coordinadores, fn($x) => (int) $x['id_usuario'] === $idCoordinador))[0] ?? null;
    $dir = array_values(array_filter($directivos, fn($x) => (int) $x['id_usuario'] === $idDirectivo))[0] ?? null;

    if (!$est || !$coor || !$dir) {
        $mensaje = 'Selecciona estudiante, coordinador y directivo validos.';
        $tipoMensaje = 'danger';
    } elseif ((int) $est['id_carrera'] !== (int) $coor['id_carrera'] || (int) $est['id_carrera'] !== (int) $dir['id_carrera']) {
        $mensaje = 'El coordinador o directivo no pertenece a la carrera del estudiante.';
        $tipoMensaje = 'danger';
    } else {
        if ($asignacionesHabilitada) {
            $stmt = mysqli_prepare($conexion, "INSERT INTO asignacion (id_estudiante, id_coordinador, id_directivo) VALUES (?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'iii', $idEstudiante, $idCoordinador, $idDirectivo);
                if (mysqli_stmt_execute($stmt)) {
                    $mensaje = 'Asignacion registrada correctamente.';
                    admin_registrar_auditoria($conexion, 'Creacion de asignacion', 'asignaciones', 'Estudiante: ' . $idEstudiante . ' | Coordinador: ' . $idCoordinador . ' | Directivo: ' . $idDirectivo);
                } else {
                    $mensaje = 'No se pudo guardar la asignacion.';
                    $tipoMensaje = 'danger';
                }
                mysqli_stmt_close($stmt);
            } else {
                $mensaje = 'No se pudo preparar la asignacion.';
                $tipoMensaje = 'danger';
            }
        } else {
            $mensaje = 'La tabla asignacion no existe en este esquema.';
            $tipoMensaje = 'danger';
        }
    }
}

admin_layout_header('Asignaciones', 'Relacion entre estudiante, coordinador y directivo.');
?>
<?php if ($mensaje !== ''): ?><div class="alert alert-<?= admin_e($tipoMensaje) ?>"><?= admin_e($mensaje) ?></div><?php endif; ?>

<div class="card card-custom p-3">
    <?php if (!$asignacionesHabilitada): ?>
        <div class="alert alert-warning mb-3">Este esquema no incluye la tabla <code>asignacion</code>. Puedes crearla o dejar esta pantalla solo como referencia funcional.</div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Estudiante</label>
            <select name="id_estudiante" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($estudiantes as $est): ?>
                    <option value="<?= (int) $est['id_usuario'] ?>"><?= admin_e($est['nombre'] . ' - ' . ($est['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Coordinador</label>
            <select name="id_coordinador" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($coordinadores as $coor): ?>
                    <option value="<?= (int) $coor['id_usuario'] ?>"><?= admin_e($coor['nombre'] . ' - ' . ($coor['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Directivo</label>
            <select name="id_directivo" class="form-select" required>
                <option value="">Seleccione</option>
                <?php foreach ($directivos as $dir): ?>
                    <option value="<?= (int) $dir['id_usuario'] ?>"><?= admin_e($dir['nombre'] . ' - ' . ($dir['nombre_carrera'] ?? 'Sin carrera')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 d-grid d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Guardar asignacion</button>
        </div>
    </form>
</div>
<?php admin_layout_footer(); ?>
