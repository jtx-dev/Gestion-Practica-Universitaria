<?php
require_once __DIR__ . '/../../../conexion.php';

// 1. Distribución por estado
$dist_estado = mysqli_query($conexion, "
    SELECT p.estado_practica, COUNT(*) as total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    GROUP BY p.estado_practica
");

// 2. Tasa de aprobación
$tasa = mysqli_query($conexion, "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN p.nota_final >= 4.0 THEN 1 ELSE 0 END) as aprobados,
        SUM(CASE WHEN p.nota_final < 4.0 AND p.nota_final IS NOT NULL THEN 1 ELSE 0 END) as reprobados,
        ROUND(AVG(p.nota_final), 1) as promedio
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    WHERE p.nota_final IS NOT NULL
");
$tasa_data = mysqli_fetch_assoc($tasa);

// 3. Estudiantes por empresa
$por_empresa = mysqli_query($conexion, "
    SELECT emp.razon_social, COUNT(*) as total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    WHERE p.estado_practica NOT IN ('cancelada')
    GROUP BY emp.id_usuario, emp.razon_social
    ORDER BY total DESC
    LIMIT 6
");

// 4. Ranking empresas mejor evaluadas por estudiantes
$ranking_empresas = mysqli_query($conexion, "
    SELECT emp.razon_social,
           COUNT(ee.id_evaluacion_empresa) as total_eval,
           ROUND(AVG(ee.nota_final), 1) as promedio
    FROM evaluacion_empresa ee
    JOIN practica p ON p.id_practica = ee.id_practica
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    GROUP BY emp.id_usuario, emp.razon_social
    ORDER BY promedio DESC
    LIMIT 5
");

// 5. Prácticas por mes últimos 6 meses
$por_mes = mysqli_query($conexion, "
    SELECT DATE_FORMAT(p.fecha_inicio, '%Y-%m') as mes, COUNT(*) as total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    WHERE p.fecha_inicio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mes ORDER BY mes ASC
");

// 6. Total prácticas activas
$r_activas = mysqli_query($conexion, "
    SELECT COUNT(*) as total FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    WHERE p.estado_practica NOT IN ('finalizado','cancelada')
");
$total_activas = mysqli_fetch_assoc($r_activas)['total'];

// Preparar datos JS
$estados_labels = [];
$estados_data   = [];
$colores_estado = [
    'postulado'=>'#fbbf24','asignado'=>'#60a5fa','en_curso'=>'#34d399',
    'informe_entregado'=>'#a78bfa','evaluado'=>'#818cf8',
    'finalizado'=>'#9ca3af','cancelada'=>'#f87171'
];
while ($row = mysqli_fetch_assoc($dist_estado)) {
    $estados_labels[] = directivo_etiqueta_estado_practica($row['estado_practica']);
    $estados_data[]   = (int)$row['total'];
}

$meses_labels = [];
$meses_data   = [];
while ($row = mysqli_fetch_assoc($por_mes)) {
    $meses_labels[] = $row['mes'];
    $meses_data[]   = (int)$row['total'];
}
?>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <i class="bi bi-people-fill" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $total_activas ?></div>
                <div class="stat-label">Estudiantes en práctica activa</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <i class="bi bi-patch-check-fill" style="color:#065f46;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $tasa_data['aprobados'] ?? 0 ?></div>
                <div class="stat-label">Prácticas aprobadas (nota ≥ 4.0)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <i class="bi bi-award-fill" style="color:#92400e;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $tasa_data['promedio'] ?? '—' ?></div>
                <div class="stat-label">Nota promedio general</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2;">
                <i class="bi bi-x-circle-fill" style="color:#991b1b;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $tasa_data['reprobados'] ?? 0 ?></div>
                <div class="stat-label">Prácticas reprobadas (nota < 4.0)</div>
            </div>
        </div>
    </div>
</div>

<!-- Tasa de aprobación -->
<?php
$total_eval = (int)($tasa_data['total'] ?? 0);
$aprobados  = (int)($tasa_data['aprobados'] ?? 0);
$tasa_porc  = $total_eval > 0 ? round($aprobados / $total_eval * 100) : 0;
?>
<div class="card-section mb-4">
    <div class="card-header-custom">
        <i class="bi bi-graph-up-arrow text-success"></i>
        <h6>Tasa de aprobación</h6>
    </div>
    <div class="p-4">
        <?php if ($total_eval === 0): ?>
            <p class="text-muted mb-0">Sin prácticas evaluadas aún.</p>
        <?php else: ?>
        <div class="d-flex justify-content-between mb-2">
            <span style="font-size:.85rem;">
                <strong><?= $aprobados ?></strong> de <strong><?= $total_eval ?></strong> prácticas aprobadas
            </span>
            <span class="fw-bold <?= $tasa_porc >= 70 ? 'text-success' : 'text-danger' ?>">
                <?= $tasa_porc ?>%
            </span>
        </div>
        <div class="progress" style="height:12px;border-radius:6px;">
            <div class="progress-bar <?= $tasa_porc >= 70 ? 'bg-success' : 'bg-danger' ?>"
                 style="width:<?= $tasa_porc ?>%;border-radius:6px;"></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-pie-chart-fill text-primary"></i>
                <h6>Distribución por estado</h6>
            </div>
            <div class="p-3 d-flex justify-content-center align-items-center" style="min-height:240px;">
                <?php if (empty($estados_labels)): ?>
                    <p class="text-muted mb-0">Sin datos disponibles.</p>
                <?php else: ?>
                    <canvas id="chartEstados" style="max-height:220px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                <h6>Prácticas iniciadas por mes (últimos 6 meses)</h6>
            </div>
            <div class="p-3" style="min-height:240px;">
                <?php if (empty($meses_labels)): ?>
                    <p class="text-muted">Sin datos de los últimos 6 meses.</p>
                <?php else: ?>
                    <canvas id="chartMeses" style="max-height:220px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Estudiantes por empresa -->
    <div class="col-lg-6">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-building text-primary"></i>
                <h6>Estudiantes por empresa</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead><tr><th>#</th><th>Empresa</th><th>Estudiantes</th></tr></thead>
                    <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($por_empresa)): ?>
                    <tr>
                        <td class="text-muted fw-bold"><?= $i++ ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['razon_social']) ?></td>
                        <td><span class="badge bg-primary"><?= $row['total'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($i === 1): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Sin datos registrados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ranking empresas mejor evaluadas -->
    <div class="col-lg-6">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-trophy-fill text-warning"></i>
                <h6>Ranking empresas mejor evaluadas por estudiantes</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead><tr><th>#</th><th>Empresa</th><th>Evaluaciones</th><th>Promedio</th></tr></thead>
                    <tbody>
                    <?php $i = 1; while ($row = mysqli_fetch_assoc($ranking_empresas)): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?= $i++ ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['razon_social']) ?></td>
                        <td><span class="badge bg-secondary"><?= $row['total_eval'] ?></span></td>
                        <td>
                            <span class="fw-bold <?= $row['promedio'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($row['promedio'], 1) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($i === 1): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin evaluaciones de empresas registradas aún.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($estados_labels)): ?>
const bgColors = <?= json_encode(array_values(array_slice($colores_estado, 0, count($estados_labels)))) ?>;
new Chart(document.getElementById('chartEstados').getContext('2d'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($estados_labels) ?>, datasets: [{ data: <?= json_encode($estados_data) ?>, backgroundColor: bgColors, borderWidth: 2 }] },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }, cutout: '60%' }
});
<?php endif; ?>
<?php if (!empty($meses_labels)): ?>
new Chart(document.getElementById('chartMeses').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses_labels) ?>,
        datasets: [{ label: 'Prácticas iniciadas', data: <?= json_encode($meses_data) ?>, backgroundColor: 'rgba(13,110,253,0.7)', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
<?php endif; ?>
</script>
