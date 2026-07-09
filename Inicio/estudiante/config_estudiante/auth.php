<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es estudiante
if (!isset($_SESSION['id_rol']) || strtolower($_SESSION['nombre_rol']) !== 'estudiante') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

require_once __DIR__ . '/../../../conexion.php';
require_once __DIR__ . '/../../../helpers.php';

$id_estudiante = $_SESSION['id_usuario'];

$sqlEstudiante = "
    SELECT e.*, c.nombre_carrera
    FROM estudiante e
    INNER JOIN carrera c
    ON e.id_carrera = c.id_carrera
    WHERE e.id_usuario = $id_estudiante
";
$resEstudiante = mysqli_query($conexion, $sqlEstudiante);
$estudiante = mysqli_fetch_assoc($resEstudiante);
?>
