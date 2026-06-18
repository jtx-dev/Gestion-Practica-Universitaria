<?php
include('../conexion.php');

$carrera = $_GET['carrera'] ?? '';
$id_estudiante = isset($_GET['id_estudiante']) ? (int)$_GET['id_estudiante'] : 0;

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
