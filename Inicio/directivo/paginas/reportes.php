<?php
require_once 'config_directivo/conexion.php';

$exportar = $_GET['exportar'] ?? '';
$formato = $_GET['formato'] ?? '';
$estadoMap = directivo_estados_practica_ui();

if ($exportar === '1' && in_array($formato, ['pdf', 'excel'], true)) {
    $filtro_estado = isset($_GET['estado']) ? directivo_estado_practica_normalizado((string) $_GET['estado']) : '';
    $filtro_anio = (int) ($_GET['anio'] ?? date('Y'));

    if ($filtro_estado !== '' && !array_key_exists($filtro_estado, $estadoMap)) {
        $filtro_estado = '';
    }

    $where = "WHERE a.id_directivo = $id_directivo AND LOWER(TRIM(a.estado)) = 'activa'";
    if ($filtro_estado !== '') {
        $where .= " AND p.estado_practica = '$filtro_estado'";
    }
    if ($filtro_anio > 0) {
        $where .= " AND YEAR(p.fecha_inicio) = " . (int) $filtro_anio;
    }

    $datos = mysqli_query($conexion, "
        SELECT
            e.nombre,
            e.apellido,
            e.nivel_curricular,
            o.titulo,
            emp.razon_social,
            p.estado_practica,
            p.fecha_inicio,
            p.fecha_termino,
            p.nota_final,
            p.horas_totales
        FROM practica p
        JOIN estudiante e ON e.id_usuario = p.id_estudiante
        INNER JOIN asignacion a ON a.id_estudiante = e.id_usuario
        JOIN oferta_practica o ON o.id_oferta = p.id_oferta
        JOIN empresa emp ON emp.id_usuario = o.id_empresa
        $where
        ORDER BY e.apellido ASC
    ");

    $filas = [];
    while ($row = mysqli_fetch_assoc($datos)) {
        $filas[] = $row;
    }

    if ($formato === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_practicas_' . date('Ymd') . '.xls"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF";
        echo "Nombre\tApellido\tNivel\tOferta\tEmpresa\tEstado\tFecha Inicio\tFecha Termino\tNota Final\tHoras Totales\n";

        foreach ($filas as $f) {
            echo implode("\t", [
                $f['nombre'],
                $f['apellido'],
                $f['nivel_curricular'],
                $f['titulo'],
                $f['razon_social'],
                directivo_etiqueta_estado_practica((string) $f['estado_practica']),
                $f['fecha_inicio'] ?? '',
                $f['fecha_termino'] ?? '',
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
        <p class="meta">Año: <?= (int) $filtro_anio ?> | Estado: <?= $filtro_estado !== '' ? htmlspecialchars($estadoMap[$filtro_estado]) : 'Todos' ?> | Generado: <?= date('d/m/Y H:i') ?></p>
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
}

$filtro_estado = isset($_GET['estado']) ? directivo_estado_practica_normalizado((string) $_GET['estado']) : '';
$filtro_anio = (int) ($_GET['anio'] ?? date('Y'));
if ($filtro_estado !== '' && !array_key_exists($filtro_estado, $estadoMap)) {
    $filtro_estado = '';
}
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-section">
            <div class="card-header-custom">
                <i class="bi bi-sliders text-primary"></i>
                <h6>Configurar reporte</h6>
            </div>
            <div class="p-4">
                <form method="GET" action="inicio.php" id="formReporte">
                    <input type="hidden" name="pagina" value="reportes">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ano academico</label>
                        <select name="anio" class="form-select">
                            <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 4; $y--): ?>
                                <option value="<?= $y ?>" <?= $filtro_anio === $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Estado de practica</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estadoMap as $valor => $label): ?>
                                <option value="<?= htmlspecialchars($valor) ?>" <?= $filtro_estado === $valor ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <p class="small fw-semibold text-muted mb-2">Formato de exportacion</p>
                    <div class="d-grid gap-2">
                        <button type="submit" name="exportar" value="1"
                                onclick="document.getElementById('fmto').value='excel'"
                                class="btn btn-success d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-5"></i>
                            Exportar a Excel (.xls)
                        </button>
                        <button type="submit" name="exportar" value="1"
                                onclick="document.getElementById('fmto').value='pdf'"
                                class="btn btn-danger d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                            Exportar a PDF (impresion)
                        </button>
                    </div>
                    <input type="hidden" name="formato" id="fmto" value="excel">
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <h6>Que incluye el reporte?</h6>
            </div>
            <div class="p-4">
                <p class="text-muted small mb-3">Los reportes contienen la informacion de las practicas asignadas a tu gestion:</p>

                <ul class="list-unstyled" style="font-size:.875rem;">
                    <?php
                    $items = [
                        ['bi-person-fill', 'Nombre y apellido del estudiante'],
                        ['bi-building', 'Empresa y oferta de practica'],
                        ['bi-arrow-repeat', 'Estado actual de la practica'],
                        ['bi-calendar-range', 'Fechas de inicio y termino'],
                        ['bi-star-fill', 'Nota final si esta disponible'],
                        ['bi-clock-fill', 'Total de horas acumuladas'],
                    ];
                    foreach ($items as [$ic, $txt]):
                    ?>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:28px;height:28px;background:#dbeafe;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi <?= $ic ?>" style="color:#1e40af;font-size:.8rem;"></i>
                            </div>
                            <?= htmlspecialchars($txt) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="alert alert-info d-flex gap-2 mt-3 mb-0" style="font-size:.8rem;">
                    <i class="bi bi-lightbulb-fill flex-shrink-0 mt-1"></i>
                    <div>
                        Para exportar a <strong>PDF</strong>, se abrira una ventana con el informe listo para imprimir o guardar como PDF.
                        El formato <strong>Excel</strong> se descarga directamente para analisis posterior.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
