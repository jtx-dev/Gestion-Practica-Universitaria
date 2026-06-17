<?php
session_start();



// Auth uso solamente de prueba y sesión ficticia

if (!isset($_SESSION['id_usuario'])) {
    // Sesión de prueba — reemplazar por redirección al login real
    $_SESSION['id_usuario'] = 1;
    $_SESSION['id_carrera'] = 1234;
    $_SESSION['nombre']     = 'Roberto';
    $_SESSION['apellido']   = 'Fuentes';
    $_SESSION['rol']        = 'directivo';
}

if ($_SESSION['rol'] !== 'directivo') {
    header('Location: ../login.php');
    exit;
}

$id_directivo = $_SESSION['id_usuario'];
$id_carrera   = $_SESSION['id_carrera'];
$nombre_directivo = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
?>
