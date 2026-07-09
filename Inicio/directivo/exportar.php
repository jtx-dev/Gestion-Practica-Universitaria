<?php
require_once 'config_directivo/auth.php';

$formato = $_GET['formato'] ?? '';
$exportar = $_GET['exportar'] ?? '';

if ($exportar !== '1' || !in_array($formato, ['pdf', 'excel'], true)) {
    header('Location: inicio.php?pagina=reportes');
    exit;
}

$estadoMap = directivo_estados_practica_ui();
$filtro_estado = isset($_GET['estado']) ? directivo_estado_practica_normalizado((string) $_GET['estado']) : '';
$filtro_anio   = (int) ($_GET['anio'] ?? date('Y'));

if ($filtro_estado !== '' && !array_key_exists($filtro_estado, $estadoMap)) {
    $filtro_estado = '';
}

$joinAsignacion = directivo_join_asignacion($id_directivo);
$where = "WHERE 1=1";
if ($filtro_estado !== '') $where .= " AND p.estado_practica = '$filtro_estado'";
if ($filtro_anio > 0)      $where .= " AND YEAR(p.fecha_inicio) = $filtro_anio";

$datos = mysqli_query($conexion, "
    SELECT e.nombre, e.apellido, e.nivel_curricular,
           o.titulo, emp.razon_social,
           p.estado_practica, p.fecha_inicio, p.fecha_termino,
           p.nota_final, p.horas_totales
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    $joinAsignacion
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    $where
    ORDER BY e.apellido ASC
");

$filas = [];
while ($row = mysqli_fetch_assoc($datos)) $filas[] = $row;

if ($formato === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_practicas_' . date('Ymd') . '.xls"');
    header('Cache-Control: max-age=0');
    echo "\xEF\xBB\xBF";
    echo "Nombre\tApellido\tNivel\tOferta\tEmpresa\tEstado\tFecha Inicio\tFecha Termino\tNota Final\tHoras Totales\n";
    foreach ($filas as $f) {
        echo implode("\t", [
            $f['nombre'], $f['apellido'], $f['nivel_curricular'],
            $f['titulo'], $f['razon_social'],
            directivo_etiqueta_estado_practica((string) $f['estado_practica']),
            $f['fecha_inicio'] ?? '', $f['fecha_termino'] ?? '',
            $f['nota_final'] ? number_format((float) $f['nota_final'], 1) : '',
            $f['horas_totales'] ?? '',
        ]) . "\n";
    }
    exit;
}

if ($formato === 'pdf') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte Practicas <?= (int) $filtro_anio ?></title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            h2 { color: #1a3a5c; }
            table { width:100%; border-collapse:collapse; margin-top:16px; }
            th { background:#1a3a5c; color:#fff; padding:6px 8px; text-align:left; font-size:10px; }
            td { padding:5px 8px; border-bottom:1px solid #e5e7eb; }
            tr:nth-child(even) td { background:#f9fafb; }
            .meta { color:#6b7280; font-size:10px; margin-top:4px; }
            @media print { button { display:none; } }
        </style>
    </head>
    <body>
        <button onclick="window.print()" style="margin-bottom:12px;padding:6px 14px;background:#1a3a5c;color:#fff;border:none;border-radius:4px;cursor:pointer;">
            Imprimir / Guardar como PDF
        </button>
        <h2>Reporte de Practicas Profesionales</h2>
        <p class="meta">
            Año: <?= (int) $filtro_anio ?> |
            Estado: <?= $filtro_estado !== '' ? htmlspecialchars($estadoMap[$filtro_estado]) : 'Todos' ?> |
            Generado: <?= date('d/m/Y H:i') ?>
        </p>
        <table>
            <thead>
                <tr><th>Estudiante</th><th>Empresa</th><th>Oferta</th><th>Estado</th><th>Inicio</th><th>Termino</th><th>Nota</th><th>Horas</th></tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['nombre'] . ' ' . $f['apellido']) ?></td>
                    <td><?= htmlspecialchars($f['razon_social']) ?></td>
                    <td><?= htmlspecialchars($f['titulo']) ?></td>
                    <td><?= htmlspecialchars(directivo_etiqueta_estado_practica((string) $f['estado_practica'])) ?></td>
                    <td><?= htmlspecialchars((string) ($f['fecha_inicio'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars((string) ($f['fecha_termino'] ?? '—')) ?></td>
                    <td><?= !empty($f['nota_final']) ? number_format((float) $f['nota_final'], 1) : '—' ?></td>
                    <td><?= htmlspecialchars((string) ($f['horas_totales'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($filas)): ?>
                <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:16px;">Sin registros para los filtros seleccionados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}