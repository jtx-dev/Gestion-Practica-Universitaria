<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar que haya sesión activa y que el rol sea Directivo
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_rol'])) {
    header('Location: ../iniciar_sesion.php');
    exit;
}

if (trim($_SESSION['nombre_rol']) !== 'Directivo') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

// Datos del directivo desde la sesión
$id_directivo     = (int) $_SESSION['id_usuario'];
$nombre_directivo = trim($_SESSION['nombre_completo'] ?? ($_SESSION['nombre_usuario'] . ' ' . $_SESSION['apellido_usuario']));

// Obtener id_carrera del directivo desde la BD
// (no viene en sesión, hay que consultarlo)
require_once __DIR__ . '/../../../conexion.php';
mysqli_set_charset($conexion, 'utf8mb4');

$stmt = mysqli_prepare($conexion, "SELECT id_carrera FROM directivo WHERE id_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_directivo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$fila) {
    // El usuario no tiene registro en la tabla directivo
    header('Location: ../iniciar_sesion.php');
    exit;
}

$id_carrera = (int) $fila['id_carrera'];
?>