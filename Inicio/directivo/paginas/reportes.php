<?php
$estadoMap     = directivo_estados_practica_ui();
$filtro_estado = isset($_GET['estado']) ? directivo_estado_practica_normalizado((string) $_GET['estado']) : '';
$filtro_anio   = (int) ($_GET['anio'] ?? date('Y'));
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
                <form method="GET" action="/Gestion-Practica-Universitaria/Inicio/directivo/exportar.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Año academico</label>
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
                    <?php foreach ([
                        ['bi-person-fill',    'Nombre y apellido del estudiante'],
                        ['bi-building',       'Empresa y oferta de practica'],
                        ['bi-arrow-repeat',   'Estado actual de la practica'],
                        ['bi-calendar-range', 'Fechas de inicio y termino'],
                        ['bi-star-fill',      'Nota final si esta disponible'],
                        ['bi-clock-fill',     'Total de horas acumuladas'],
                    ] as [$ic, $txt]): ?>
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