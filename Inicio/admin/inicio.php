<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || strtolower($_SESSION['nombre_rol']) !== 'administrador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}
header('Location: dashboard.php');
exit;

