<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || strtolower($_SESSION['nombre_rol']) !== 'administrador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

$auditoriaHabilitada = admin_table_exists($conexion, 'auditoria');
$auditoriaLegacy = !$auditoriaHabilitada && admin_table_exists($conexion, 'logs');
$tablaAuditoria = $auditoriaHabilitada ? 'auditoria' : ($auditoriaLegacy ? 'logs' : '');
$auditoria = $tablaAuditoria !== '' ? admin_query_all($conexion, "SELECT a.usuario, a.accion, a.fecha, a.ip, a.modulo FROM {$tablaAuditoria} a ORDER BY a.fecha DESC LIMIT 50") : [];
admin_layout_header('Auditoría del Sistema', 'Trazabilidad de acciones realizadas por administradores.');
?>
<div class="card card-custom">
    <div class="card-body table-responsive">
        <?php if (!$auditoriaHabilitada && !$auditoriaLegacy): ?>
            <div class="alert alert-warning">La tabla <code>auditoria</code> no existe en la base de datos actual.</div>
        <?php elseif ($auditoriaLegacy): ?>
            <div class="alert alert-info">Se detectó la tabla antigua <code>logs</code>. Considera migrarla a <code>auditoria</code>.</div>
        <?php endif; ?>
        <table class="table align-middle mb-0">
            <thead><tr><th>Usuario</th><th>Accion</th><th>Fecha</th><th>IP</th><th>Modulo</th></tr></thead>
            <tbody>
                <?php foreach ($auditoria as $log): ?>
                    <tr>
                        <td><?= admin_e($log['usuario'] ?? '') ?></td>
                        <td><?= admin_e($log['accion'] ?? '') ?></td>
                        <td><?= admin_e($log['fecha'] ?? '') ?></td>
                        <td><?= admin_e($log['ip'] ?? '') ?></td>
                        <td><?= admin_e($log['modulo'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_layout_footer(); ?>
