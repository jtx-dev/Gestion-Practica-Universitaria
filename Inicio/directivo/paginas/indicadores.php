<?php
require_once __DIR__ . '/../../../conexion.php';

$dist_estado = mysqli_query($conexion, "
    SELECT p.estado_practica, COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    INNER JOIN asignacion a ON a.id_estudiante = e.id_usuario
    WHERE a.id_directivo = $id_directivo
      AND LOWER(TRIM(a.estado)) = 'activa'
    GROUP BY p.estado_practica
");

$top_empresas = mysqli_query($conexion, "
    SELECT emp.razon_social, COUNT(*) AS total, ROUND(AVG(p.nota_final), 1) AS promedio
    FROM practica p
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    INNER JOIN asignacion a ON a.id_estudiante = e.id_usuario
    WHERE a.id_directivo = $id_directivo
      AND LOWER(TRIM(a.estado)) = 'activa'
    GROUP BY emp.id_usuario, emp.razon_social
    ORDER BY total DESC
    LIMIT 5
");

$por_mes = mysqli_query($conexion, "
    SELECT DATE_FORMAT(fecha_inicio, '%Y-%m') AS mes, COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    INNER JOIN asignacion a ON a.id_estudiante = e.id_usuario
    WHERE a.id_directivo = $id_directivo
      AND LOWER(TRIM(a.estado)) = 'activa'
      AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
");

$notas = mysqli_query($conexion, "
    SELECT ROUND(AVG(nota_final), 2) AS promedio, COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    INNER JOIN asignacion a ON a.id_estudiante = e.id_usuario
    WHERE a.id_directivo = $id_directivo
      AND LOWER(TRIM(a.estado)) = 'activa'
      AND nota_final IS NOT NULL
");
$nota_data = mysqli_fetch_assoc($notas) ?: ['promedio' => null, 'total' => 0];

$estados_db = [];
$estados_labels = [];
$estados_data = [];
$colores_estado = [
    'postulado' => '#fbbf24',
    'asignado' => '#60a5fa',
    'en_curso' => '#34d399',
    'informe_entregado' => '#a78bfa',
    'evaluado' => '#818cf8',
    'finalizado' => '#9ca3af',
    'cancelada' => '#f87171',
];
while ($row = mysqli_fetch_assoc($dist_estado)) {
    $estado = directivo_estado_practica_normalizado((string) $row['estado_practica']);
    $estados_db[] = $estado;
    $estados_labels[] = directivo_etiqueta_estado_practica($estado);
    $estados_data[] = (int) $row['total'];
}

$meses_labels = [];
$meses_data = [];
while ($row = mysqli_fetch_assoc($por_mes)) {
    $meses_labels[] = $row['mes'];
    $meses_data[] = (int) $row['total'];
}
?>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <i class="bi bi-graph-up-arrow" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-value"><?= array_sum($estados_data) ?></div>
                <div class="stat-label">Total practicas registradas</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <i class="bi bi-award-fill" style="color:#065f46;"></i>
            </div>
            <div>
                <div class="stat-value"><?= htmlspecialchars((string) ($nota_data['promedio'] ?? '—')) ?></div>
                <div class="stat-label">Nota promedio asignada</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <i class="bi bi-building" style="color:#92400e;"></i>
            </div>
            <div>
                <div class="stat-value"><?= mysqli_num_rows($top_empresas) ?></div>
                <div class="stat-label">Empresas colaboradoras</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;">
                <i class="bi bi-clipboard2-data-fill" style="color:#5b21b6;"></i>
            </div>
            <div>
                <div class="stat-value"><?= (int) ($nota_data['total'] ?? 0) ?></div>
                <div class="stat-label">Practicas con nota final</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-pie-chart-fill text-primary"></i>
                <h6>Distribucion por estado</h6>
            </div>
            <div class="p-3 d-flex justify-content-center align-items-center" style="min-height:260px;">
                <?php if (empty($estados_labels)): ?>
                    <p class="text-muted mb-0">Sin datos disponibles.</p>
                <?php else: ?>
                    <canvas id="chartEstados" style="max-height:240px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                <h6>Practicas iniciadas por mes (ultimos 6 meses)</h6>
            </div>
            <div class="p-3" style="min-height:260px;">
                <?php if (empty($meses_labels)): ?>
                    <p class="text-muted">Sin datos de los ultimos 6 meses.</p>
                <?php else: ?>
                    <canvas id="chartMeses" style="max-height:240px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card-section">
    <div class="card-header-custom">
        <i class="bi bi-trophy-fill text-warning"></i>
        <h6>Top empresas colaboradoras</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Empresa</th><th>Practicas</th><th>Nota promedio</th></tr>
            </thead>
            <tbody>
            <?php
            mysqli_data_seek($top_empresas, 0);
            $i = 1;
            while ($row = mysqli_fetch_assoc($top_empresas)):
            ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $i++ ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($row['razon_social']) ?></td>
                    <td><span class="badge bg-primary"><?= (int) $row['total'] ?></span></td>
                    <td>
                        <?php if ($row['promedio'] !== null): ?>
                            <span class="fw-bold <?= (float) $row['promedio'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format((float) $row['promedio'], 1) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($i === 1): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Sin datos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($estados_labels)): ?>
const ctxE = document.getElementById('chartEstados').getContext('2d');
const coloresMap = <?= json_encode($colores_estado) ?>;
const estadosKeys = <?= json_encode($estados_db) ?>;
const estadosLabels = <?= json_encode($estados_labels) ?>;
const estadosData = <?= json_encode($estados_data) ?>;
const bgColors = estadosKeys.map((key) => coloresMap[key] || '#94a3b8');

new Chart(ctxE, {
    type: 'doughnut',
    data: { labels: estadosLabels, datasets: [{ data: estadosData, backgroundColor: bgColors, borderWidth: 2 }] },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }, cutout: '60%' }
});
<?php endif; ?>

<?php if (!empty($meses_labels)): ?>
const ctxM = document.getElementById('chartMeses').getContext('2d');
new Chart(ctxM, {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses_labels) ?>,
        datasets: [{
            label: 'Practicas iniciadas',
            data: <?= json_encode($meses_data) ?>,
            backgroundColor: 'rgba(46,134,222,0.7)',
            borderRadius: 6
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
<?php endif; ?>
</script>
