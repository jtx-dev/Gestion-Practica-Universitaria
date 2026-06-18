<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar que haya sesión activa y que el rol sea Directivo
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['nombre_rol'])) {
    header('Location: ../iniciar_sesion.php');
    exit;
}

$rolSesion = strtolower(trim((string) $_SESSION['nombre_rol']));
if (!in_array($rolSesion, ['directivo', 'director'], true)) {
    header('Location: ../iniciar_sesion.php');
    exit;
}

// Datos del directivo desde la sesión
$id_directivo     = (int) $_SESSION['id_usuario'];
$nombre_directivo = trim($_SESSION['nombre_completo'] ?? ($_SESSION['nombre_usuario'] . ' ' . $_SESSION['apellido_usuario']));

// Obtener id_carrera del directivo desde la BD
// (no viene en sesión, hay que consultarlo)
require_once __DIR__ . '/conexion.php';
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

function directivo_join_asignacion(int $idDirectivo, string $aliasEstudiante = 'e'): string
{
    return "INNER JOIN asignacion a ON a.id_estudiante = {$aliasEstudiante}.id_usuario
        AND a.id_directivo = " . (int) $idDirectivo . "
        AND LOWER(TRIM(a.estado)) = 'activa'";
}

function directivo_estado_practica_normalizado(string $estado): string
{
    return strtolower(trim($estado));
}

function directivo_estados_practica_ui(): array
{
    return [
        'postulado' => 'Postulado',
        'asignado' => 'Asignado',
        'en_curso' => 'En curso',
        'informe_entregado' => 'Informe entregado',
        'evaluado' => 'Evaluado',
        'finalizado' => 'Finalizada',
        'cancelada' => 'Cancelada',
    ];
}

function directivo_etiqueta_estado_practica(string $estado): string
{
    $estado = directivo_estado_practica_normalizado($estado);
    $mapa = directivo_estados_practica_ui();
    return $mapa[$estado] ?? ($estado !== '' ? ucwords(str_replace('_', ' ', $estado)) : 'Sin estado');
}

function directivo_clase_estado_practica(string $estado): string
{
    $estado = directivo_estado_practica_normalizado($estado);
    return match ($estado) {
        'asignado' => 'badge-asignado',
        'en_curso' => 'badge-en-curso',
        'finalizado', 'evaluado' => 'badge-finalizada',
        'informe_entregado' => 'badge-evaluado',
        'postulado' => 'badge-postulado',
        'cancelada' => 'badge-cancelada',
        default => 'badge-finalizada',
    };
}
?>
