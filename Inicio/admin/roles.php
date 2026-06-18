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

// --- MODO MANTENIMIENTO ---
// Se ha deshabilitado la creación y modificación de roles.
// Mostrando una lista fija de roles predefinidos.
$roles = [
    ['id_rol' => 1, 'nombre_rol' => 'Coordinador', 'descripcion' => 'Rol para coordinadores de carrera.', 'estado' => 'activo'],
    ['id_rol' => 2, 'nombre_rol' => 'Directivo', 'descripcion' => 'Rol para personal directivo de la institución.', 'estado' => 'activo'],
    ['id_rol' => 3, 'nombre_rol' => 'Estudiante', 'descripcion' => 'Rol para estudiantes postulantes.', 'estado' => 'activo'],
    ['id_rol' => 4, 'nombre_rol' => 'Empresa', 'descripcion' => 'Rol para representantes de empresas ofertantes.', 'estado' => 'activo'],
];

admin_layout_header('Gestión de Roles', 'Catálogo de roles del sistema.');
?>

<div class="alert alert-warning" role="alert">
    <h4 class="alert-heading">Modo Mantenimiento</h4>
    <p>La creación y modificación de roles se encuentra temporalmente deshabilitada. Solo se muestran los roles base del sistema.</p>
</div>

<div class="card card-custom">
    <div class="card-body table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $rol): ?>
                    <tr>
                        <td><?= (int) $rol['id_rol'] ?></td>
                        <td><?= admin_e($rol['nombre_rol']) ?></td>
                        <td><?= admin_e($rol['descripcion'] ?? '') ?></td>
                        <td><span class="badge bg-<?= admin_badge_estado($rol['estado']) ?>"><?= admin_e($rol['estado']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_layout_footer(); ?>
