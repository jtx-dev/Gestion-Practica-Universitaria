<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea administrador
if (!isset($_SESSION['id_rol']) || strtolower($_SESSION['nombre_rol']) !== 'administrador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . '/../../../conexion.php';
// Funciones comunes de administración
require_once __DIR__ . '/../includes/common.php';
/** @var mysqli $conexion */

// Establecer id_institucion
$id_institucion = admin_obtener_id_institucion_actual($conexion);
?>
