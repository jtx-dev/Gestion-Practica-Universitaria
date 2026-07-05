<?php
// Las variables $conexion e $id_institucion ($filtroInstitucion) vienen de auth.php
$filtroInstitucion = $id_institucion > 0 ? (int) $id_institucion : 0;
$filtroUsuarios = $filtroInstitucion > 0
    ? "u.id_institucion = {$filtroInstitucion} AND LOWER(TRIM(r.nombre_rol)) IN ('estudiante', 'coordinador', 'directivo', 'director')"
    : "1 = 0";

$totalUsuarios = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}");
$usuariosActivos = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}
  AND u.estado_cuenta = 'activa'");
$usuariosInactivos = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}
  AND u.estado_cuenta = 'inactiva'");
$totalEmpresas = admin_query_scalar($conexion, "SELECT COUNT(DISTINCT e.id_usuario)
FROM empresa e
INNER JOIN oferta_practica op ON op.id_empresa = e.id_usuario
INNER JOIN carrera c ON c.id_carrera = op.id_carrera
WHERE c.id_institucion = {$filtroInstitucion}");
$totalEstudiantes = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}
  AND LOWER(TRIM(r.nombre_rol)) = 'estudiante'");
$totalCoordinadores = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}
  AND LOWER(TRIM(r.nombre_rol)) = 'coordinador'");
$totalDirectivos = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE {$filtroUsuarios}
  AND LOWER(TRIM(r.nombre_rol)) IN ('directivo', 'director')");
$practicasActivas = admin_query_scalar($conexion, "SELECT COUNT(*)
FROM oferta_practica op
INNER JOIN carrera c ON c.id_carrera = op.id_carrera
WHERE c.id_institucion = {$filtroInstitucion}
  AND op.estado_oferta IN ('activa', 'aprobada', 'publicada')");
$ultimosUsuarios = admin_query_all($conexion, "SELECT u.id_usuario, u.correo, COALESCE(CONCAT(e.nombre, ' ', e.apellido), CONCAT(c.nombre, ' ', c.apellido), CONCAT(d.nombre, ' ', d.apellido), 'Usuario') AS nombre_completo, COALESCE(r.nombre_rol, 'Sin rol') AS rol, u.estado_cuenta, u.fecha_creacion
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
LEFT JOIN coordinador c ON c.id_usuario = u.id_usuario
LEFT JOIN directivo d ON d.id_usuario = u.id_usuario
WHERE {$filtroUsuarios}
ORDER BY u.id_usuario DESC
LIMIT 5");
?>
<?php if ($id_institucion <= 0): ?>
    <div class="alert alert-warning mb-4">
        No se pudo identificar la institución del administrador. Los indicadores se muestran vacíos hasta que vuelvas a iniciar sesión.
    </div>
<?php endif; ?>
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Total Usuarios', $totalUsuarios, 'bi-people', 'primary'],
        ['Usuarios Activos', $usuariosActivos, 'bi-check-circle', 'success'],
        ['Usuarios Inactivos', $usuariosInactivos, 'bi-slash-circle', 'secondary'],
        ['Empresas Vinculadas', $totalEmpresas, 'bi-buildings', 'warning'],
        ['Estudiantes', $totalEstudiantes, 'bi-mortarboard', 'info'],
        ['Coordinadores', $totalCoordinadores, 'bi-diagram-3', 'danger'],
        ['Directivos', $totalDirectivos, 'bi-person-badge', 'dark'],
        ['Prácticas Activas', $practicasActivas, 'bi-briefcase', 'success'],
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
                <h5 class="fw-bold mb-1">Últimos usuarios registrados</h5>
                <p class="text-muted mb-0">Vista rápida de altas recientes y su estado actual.</p>
            </div>
            <a href="inicio.php?pagina=usuarios" class="btn btn-primary btn-sm">Gestionar usuarios</a>
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
