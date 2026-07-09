<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es coordinador
if (!isset($_SESSION['nombre_rol']) || strtolower($_SESSION['nombre_rol']) !== 'coordinador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . '/../../../conexion.php';
// Helper functions
require_once __DIR__ . '/../../../helpers.php';
/** @var mysqli $conexion */

$id_coordinador = (int) $_SESSION['id_usuario'];
$nombre_coordinador = trim($_SESSION['nombre_completo'] ?? ($_SESSION['nombre_usuario'] . ' ' . $_SESSION['apellido_usuario']));

// Obtener carrera del coordinador
$sql_coord = "SELECT id_carrera FROM coordinador WHERE id_usuario = ?";
$stmt_coord = mysqli_prepare($conexion, $sql_coord);
mysqli_stmt_bind_param($stmt_coord, "i", $id_coordinador);
mysqli_stmt_execute($stmt_coord);
$res_coord = mysqli_stmt_get_result($stmt_coord);
$coord_data = mysqli_fetch_assoc($res_coord);
$id_carrera = (int) ($coord_data['id_carrera'] ?? 0);
mysqli_stmt_close($stmt_coord);

if ($id_carrera === 0) {
    header('Location: ../iniciar_sesion.php');
    exit;
}
?>
