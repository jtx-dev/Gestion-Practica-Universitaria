<?php
include('../../conexion.php');
include(__DIR__ . '/includes/common.php');

$totalUsuarios = admin_query_scalar($conexion, "SELECT COUNT(*) FROM usuario");
$usuariosActivos = admin_query_scalar($conexion, "SELECT COUNT(*) FROM usuario WHERE estado_cuenta = 'activa'");
$usuariosInactivos = admin_query_scalar($conexion, "SELECT COUNT(*) FROM usuario WHERE estado_cuenta = 'inactiva'");
$totalEmpresas = admin_query_scalar($conexion, "SELECT COUNT(*) FROM empresa");
$totalEstudiantes = admin_query_scalar($conexion, "SELECT COUNT(*) FROM estudiante");
$totalCoordinadores = admin_query_scalar($conexion, "SELECT COUNT(*) FROM coordinador");
$totalDirectivos = admin_query_scalar($conexion, "SELECT COUNT(*) FROM directivo");
$practicasActivas = admin_query_scalar($conexion, "SELECT COUNT(*) FROM oferta_practica WHERE estado_oferta IN ('activa', 'aprobada', 'publicada')");
$ultimosUsuarios = admin_query_all($conexion, "SELECT u.id_usuario, u.correo, COALESCE(CONCAT(e.nombre, ' ', e.apellido), CONCAT(a.nombre, ' ', a.apellido), 'Usuario') AS nombre_completo, COALESCE(r.nombre_rol, 'Sin rol') AS rol, u.estado_cuenta, u.fecha_creacion
FROM usuario u
LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
LEFT JOIN coordinador c ON c.id_usuario = u.id_usuario
LEFT JOIN directivo d ON d.id_usuario = u.id_usuario
LEFT JOIN administrador a ON a.id_usuario = u.id_usuario
LEFT JOIN rol r ON r.id_rol = u.id_rol
ORDER BY u.id_usuario DESC
LIMIT 5");

admin_layout_header('Dashboard Administrativo', 'Resumen operativo del modulo de administracion.');
?>
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Total Usuarios', $totalUsuarios, 'bi-people', 'primary'],
        ['Usuarios Activos', $usuariosActivos, 'bi-check-circle', 'success'],
        ['Usuarios Inactivos', $usuariosInactivos, 'bi-slash-circle', 'secondary'],
        ['Empresas', $totalEmpresas, 'bi-buildings', 'warning'],
        ['Estudiantes', $totalEstudiantes, 'bi-mortarboard', 'info'],
        ['Coordinadores', $totalCoordinadores, 'bi-diagram-3', 'danger'],
        ['Directivos', $totalDirectivos, 'bi-person-badge', 'dark'],
        ['Practicas Activas', $practicasActivas, 'bi-briefcase', 'success'],
    ];
    foreach ($kpis as [$label, $valor, $icono, $color]) :
    ?>
    <div class="col-md-6 col-xl-3">
        <div class="card card-custom h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1"><?= admin_e($label) ?></p>
                        <h2 class="fw-bold mb-0"><?= (int) $valor ?></h2>
                    </div>
                    <div class="bg-<?= $color ?> bg-opacity-10 p-3 rounded">
                        <i class="bi <?= $icono ?> text-<?= $color ?> fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card card-custom">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">Ultimos usuarios registrados</h5>
                <p class="text-muted mb-0">Vista rapida de altas recientes y su estado actual.</p>
            </div>
            <a href="usuarios.php" class="btn btn-primary btn-sm">Gestionar usuarios</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ultimosUsuarios): foreach ($ultimosUsuarios as $usuario): ?>
                        <tr>
                            <td><?= admin_e($usuario['nombre_completo']) ?></td>
                            <td><?= admin_e($usuario['correo']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= admin_e($usuario['rol']) ?></span></td>
                            <td><span class="badge bg-<?= admin_badge_estado($usuario['estado_cuenta'] ?? '') ?>"><?= admin_e($usuario['estado_cuenta'] ?? 'N/A') ?></span></td>
                            <td><?= admin_e((string) ($usuario['fecha_creacion'] ?? 'No disponible')) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php admin_layout_footer(); ?>
