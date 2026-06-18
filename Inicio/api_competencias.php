<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../conexion.php');

$carrera = $_GET['carrera'] ?? '';
$id_estudiante = isset($_GET['id_estudiante']) ? (int)$_GET['id_estudiante'] : 0;

// Si se solicita un estudiante específico, aplicar seguridad de sesión y autorización
if ($id_estudiante > 0) {
    if (!isset($_SESSION['id_usuario'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }

    // Un estudiante solo puede consultar sus propias competencias
    if ($id_estudiante !== (int)$_SESSION['id_usuario']) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para este recurso']);
        exit;
    }
}


if (empty($carrera)) {
    echo json_encode([]);
    exit;
}

// 1. Obtener ID de carrera por nombre
$stmt = mysqli_prepare($conexion, "SELECT id_carrera FROM carrera WHERE nombre_carrera = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $carrera);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$carrera_data = mysqli_fetch_assoc($res);
$id_carrera = $carrera_data['id_carrera'] ?? 0;
mysqli_stmt_close($stmt);

if ($id_carrera === 0) {
    echo json_encode([]);
    exit;
}

// 2. Obtener competencias de esa carrera
$sql = "SELECT id, nombre, tipo FROM competencias WHERE id_carrera = $id_carrera ORDER BY tipo, nombre";
$res_comp = mysqli_query($conexion, $sql);
$competencias = [];

// 3. Si se pasa id_estudiante, marcar las que ya tiene
$marcadas = [];
if ($id_estudiante > 0) {
    $res_m = mysqli_query($conexion, "SELECT id_competencia FROM estudiante_competencias WHERE id_estudiante = $id_estudiante");
    while($m = mysqli_fetch_assoc($res_m)) {
        $marcadas[] = $m['id_competencia'];
    }
}

while ($c = mysqli_fetch_assoc($res_comp)) {
    $c['marcada'] = in_array($c['id'], $marcadas);
    $competencias[] = $c;
}

header('Content-Type: application/json');
echo json_encode($competencias);
