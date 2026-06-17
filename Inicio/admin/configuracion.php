<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tiempoSesion = (int) ($_POST['tiempo_sesion'] ?? 30);
    $correoSoporte = trim((string) ($_POST['correo_soporte'] ?? ''));
    $estadoSistema = ($_POST['estado_sistema'] ?? 'activo') === 'mantenimiento' ? 'mantenimiento' : 'activo';

    $claves = [
        'tiempo_sesion' => (string) $tiempoSesion,
        'correo_soporte' => $correoSoporte,
        'estado_sistema' => $estadoSistema,
    ];
    foreach ($claves as $clave => $valor) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ss', $clave, $valor);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    $mensaje = 'Configuracion actualizada.';
    admin_registrar_auditoria($conexion, 'Actualizacion de configuracion', 'configuracion', 'Tiempo sesion: ' . $tiempoSesion . ' | Correo soporte: ' . $correoSoporte . ' | Estado sistema: ' . $estadoSistema);
}

$config = [
    'tiempo_sesion' => '30',
    'correo_soporte' => 'soporte@sgppe.cl',
    'estado_sistema' => 'activo',
];

$configuracionHabilitada = admin_table_exists($conexion, 'configuracion');
if ($configuracionHabilitada) {
    $filas = admin_query_all($conexion, "SELECT clave, valor FROM configuracion");
    foreach ($filas as $fila) {
        $config[$fila['clave']] = $fila['valor'];
    }
}

admin_layout_header('Configuracion', 'Parametros generales del modulo administrativo.');
?>
<?php if ($mensaje !== ''): ?><div class="alert alert-<?= admin_e($tipoMensaje) ?>"><?= admin_e($mensaje) ?></div><?php endif; ?>

<div class="card card-custom p-4">
    <?php if (!$configuracionHabilitada): ?>
        <div class="alert alert-warning">La tabla <code>configuracion</code> no existe en este esquema. Se muestran valores por defecto.</div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Tiempo de sesion</label>
            <input type="number" name="tiempo_sesion" class="form-control" value="<?= admin_e($config['tiempo_sesion'] ?? '30') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Correo soporte</label>
            <input type="email" name="correo_soporte" class="form-control" value="<?= admin_e($config['correo_soporte'] ?? 'soporte@sgppe.cl') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado sistema</label>
            <select name="estado_sistema" class="form-select">
                <option value="activo" <?= ($config['estado_sistema'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="mantenimiento" <?= ($config['estado_sistema'] ?? '') === 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
            </select>
        </div>
        <div class="col-12 d-grid d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </div>
    </form>
</div>
<?php admin_layout_footer(); ?>
