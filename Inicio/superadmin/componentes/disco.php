<?php
function formatearBytes($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;

    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $unidades[$i];
}

$unidad = "C:";
$total = disk_total_space($unidad);
$libre = disk_free_space($unidad);
$usado = $total - $libre;
$porcentaje = ($usado / $total) * 100;

echo json_encode([
    'porcentaje' => round($porcentaje, 1),
    'usado' => formatearBytes($usado),
    'total' => formatearBytes($total)
]);