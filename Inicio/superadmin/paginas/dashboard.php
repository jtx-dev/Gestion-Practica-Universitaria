<?php

$institucionesTotal = 0;
$institucionesActivas = 0;
$institucionesInactivas = 0;
$ultimasInstituciones = [];

$sqlResumen = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN estado_institucion = 'activa' THEN 1 ELSE 0 END) AS activas,
    SUM(CASE WHEN estado_institucion = 'inactiva' THEN 1 ELSE 0 END) AS inactivas
FROM institucion";
$resultadoResumen = mysqli_query($conexion, $sqlResumen);

if ($resultadoResumen) {
    $fila = mysqli_fetch_assoc($resultadoResumen);
    $institucionesTotal = (int) ($fila['total'] ?? 0);
    $institucionesActivas = (int) ($fila['activas'] ?? 0);
    $institucionesInactivas = (int) ($fila['inactivas'] ?? 0);
}

$sqlUltimas = "SELECT nombre, estado_institucion
FROM institucion
ORDER BY id_institucion DESC
LIMIT 5";
$resultadoUltimas = mysqli_query($conexion, $sqlUltimas);

if ($resultadoUltimas) {
    while ($fila = mysqli_fetch_assoc($resultadoUltimas)) {
        $ultimasInstituciones[] = $fila;
    }
}

$estadoLabels = ['Activa', 'Inactiva'];
$estadoData = [$institucionesActivas, $institucionesInactivas];

$periodoSeleccionado = $_GET['periodo'] ?? 'mensual';
$periodosValidos = [
    'mensual' => '%Y-%m',
    'anual' => '%Y'
];
$periodosEtiquetas = [
    'mensual' => 'Mensual',
    'anual' => 'Anual'
];
if (!isset($periodosValidos[$periodoSeleccionado])) {
    $periodoSeleccionado = 'mensual';
}

mysqli_query($conexion, "ALTER TABLE institucion ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

$periodoFormato = $periodosValidos[$periodoSeleccionado];
$sqlHistograma = "SELECT DATE_FORMAT(fecha_creacion, '$periodoFormato') AS periodo, COUNT(*) AS total
FROM institucion
GROUP BY periodo
ORDER BY periodo ASC";
$resultadoHistograma = mysqli_query($conexion, $sqlHistograma);
$histogramaLabels = [];
$histogramaData = [];
if ($resultadoHistograma) {
    while ($row = mysqli_fetch_assoc($resultadoHistograma)) {
        $histogramaLabels[] = $row['periodo'];
        $histogramaData[] = (int) $row['total'];
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold text-primary">Dashboard - Super Administrador</h2>
    </div>
    <a href="inicio.php?pagina=instituciones" class="btn btn-primary">
        <i class="bi bi-building-gear me-2"></i>Gestionar instituciones
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="inicio.php?pagina=instituciones" class="d-block text-decoration-none text-reset">
            <div class="card-custom p-3 h-100">
                <p class="fw-bold mb-2"><i class="bi bi-building me-2"></i>Total instituciones</p>
                <h3 class="mb-0"><?= $institucionesTotal ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="inicio.php?pagina=instituciones" class="d-block text-decoration-none text-reset">
            <div class="card-custom p-3 h-100">
                <p class="fw-bold mb-2"><i class="bi bi-check-circle me-2"></i>Activas</p>
                <h3 class="mb-0"><?= $institucionesActivas ?></h3>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="inicio.php?pagina=instituciones" class="d-block text-decoration-none text-reset">
            <div class="card-custom p-3 h-100">
                <p class="fw-bold mb-2"><i class="bi bi-slash-circle me-2"></i>Inactivas</p>
                <h3 class="mb-0"><?= $institucionesInactivas ?></h3>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Distribución de instituciones</h5>
                    <p class="text-muted small mb-0">Activas vs inactivas</p>
                </div>
                <span class="badge bg-secondary">Total <?= $institucionesTotal ?></span>
            </div>
            <div class="d-flex justify-content-center align-items-center" style="min-height:280px;">
                <canvas id="institucionesEstadoChart" style="max-height:260px; width:100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Últimas instituciones</h5>
                    <p class="text-muted small mb-0">Registro reciente</p>
                </div>
                <a href="inicio.php?pagina=instituciones" class="btn btn-sm btn-outline-primary">Ver todo</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($ultimasInstituciones) > 0): ?>
                            <?php foreach ($ultimasInstituciones as $institucion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($institucion['nombre']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $institucion['estado_institucion'] === 'activa' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars($institucion['estado_institucion']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No hay instituciones registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card-custom p-3 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <h5 class="mb-1">Histograma de instituciones</h5>
            <p class="text-muted small mb-0">Visualiza cuántas instituciones se registraron por período.</p>
        </div>
        <div class="btn-group" role="group" aria-label="Filtros de periodo">
            <?php foreach ($periodosEtiquetas as $clave => $etiqueta): ?>
                <a href="inicio.php?pagina=dashboard&periodo=<?= $clave ?>#histograma" class="btn btn-sm <?= $periodoSeleccionado === $clave ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $etiqueta ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="histograma" class="d-flex justify-content-center align-items-center" style="min-height:320px;">
        <canvas id="histogramaInstituciones" style="width:100%; max-height:320px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const ctxInstituciones = document.getElementById('institucionesEstadoChart').getContext('2d');
new Chart(ctxInstituciones, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($estadoLabels) ?>,
        datasets: [{
            data: <?= json_encode($estadoData) ?>,
            backgroundColor: ['blue', '#6c757d'],
            borderColor: ['#ffffff', '#ffffff'],
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
            }
        },
        cutout: '60%'
    }
});

const ctxHistograma = document.getElementById('histogramaInstituciones').getContext('2d');
new Chart(ctxHistograma, {
    type: 'bar',
    data: {
        labels: <?= json_encode($histogramaLabels) ?>,
        datasets: [{
            label: 'Instituciones',
            data: <?= json_encode($histogramaData) ?>,
            backgroundColor: 'blue',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 48
        }]
    },
    options: {
        scales: {
            x: {
                grid: { display: false },
                ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }
            },
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        }
    }
});
</script>
