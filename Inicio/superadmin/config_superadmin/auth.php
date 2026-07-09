<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es Super Administrador
if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'Super Administrador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

require_once __DIR__ . '/../../../conexion.php';
?>
