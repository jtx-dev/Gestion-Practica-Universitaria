<?php
require_once '../config_directivo/conexion.php';

// Distribución por estado de práctica
$dist_estado = mysqli_query($conexion, "
    SELECT p.estado_practica, COUNT(*) as total
    FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera
    GROUP BY p.estado_practica
");

// Top empresas con más prácticas
$top_empresas = mysqli_query($conexion, "
    SELECT emp.razon_social, COUNT(*) as total, ROUND(AVG(p.nota_final),1) as promedio
    FROM Practica p
    JOIN Oferta_Practica o ON o.id_oferta = p.id_oferta
    JOIN Empresa emp ON emp.id_usuario = o.id_empresa
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera
    GROUP BY emp.id_usuario, emp.razon_social
    ORDER BY total DESC LIMIT 5
");

// Prácticas por mes (últimos 6 meses)
$por_mes = mysqli_query($conexion, "
    SELECT DATE_FORMAT(fecha_inicio,'%Y-%m') as mes, COUNT(*) as total
    FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera
      AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mes ORDER BY mes ASC
");

// Promedio notas
$notas     = mysqli_query($conexion, "
    SELECT ROUND(AVG(nota_final),2) as promedio, COUNT(*) as total
    FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera AND nota_final IS NOT NULL
");
$nota_data = mysqli_fetch_assoc($notas);

// Preparar datos para gráficos JS
$estados_labels = [];
$estados_data   = [];
$colores_estado = ['Postulado'=>'#fbbf24','Asignado'=>'#60a5fa','En Curso'=>'#34d399','Informe Entregado'=>'#a78bfa','Evaluado'=>'#818cf8','Finalizada'=>'#9ca3af','Cancelada'=>'#f87171'];
while ($row = mysqli_fetch_assoc($dist_estado)) {
    $estados_labels[] = $row['estado_practica'];
    $estados_data[]   = $row['total'];
}

$meses_labels = [];
$meses_data   = [];
while ($row = mysqli_fetch_assoc($por_mes)) {
    $meses_labels[] = $row['mes'];
    $meses_data[]   = $row['total'];
}
?>

<div class="row g-3 mb-4">
    <!-- Resumen general -->
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <i class="bi bi-graph-up-arrow" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-value"><?= array_sum($estados_data) ?></div>
                <div class="stat-label">Total prácticas registradas</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <i class="bi bi-award-fill" style="color:#065f46;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $nota_data['promedio'] ?? '—' ?></div>
                <div class="stat-label">Nota promedio carrera</div>
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
                <div class="stat-value"><?= $nota_data['total'] ?? 0 ?></div>
                <div class="stat-label">Prácticas con nota final</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Gráfico distribución por estado -->
    <div class="col-lg-5">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-pie-chart-fill text-primary"></i>
                <h6>Distribución por estado</h6>
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

    <!-- Gráfico prácticas por mes -->
    <div class="col-lg-7">
        <div class="card-section h-100">
            <div class="card-header-custom">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                <h6>Prácticas iniciadas por mes (últimos 6 meses)</h6>
            </div>
            <div class="p-3" style="min-height:260px;">
                <?php if (empty($meses_labels)): ?>
                    <p class="text-muted">Sin datos de los últimos 6 meses.</p>
                <?php else: ?>
                    <canvas id="chartMeses" style="max-height:240px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top empresas -->
<div class="card-section">
    <div class="card-header-custom">
        <i class="bi bi-trophy-fill text-warning"></i>
        <h6>Top empresas colaboradoras</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Empresa</th><th>Prácticas</th><th>Nota promedio</th></tr>
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
                <td><span class="badge bg-primary"><?= $row['total'] ?></span></td>
                <td>
                    <?php if ($row['promedio']): ?>
                    <span class="fw-bold <?= $row['promedio'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($row['promedio'],1) ?>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($estados_labels)): ?>
const ctxE = document.getElementById('chartEstados').getContext('2d');
const coloresMap = <?= json_encode($colores_estado) ?>;
const estadosLabels = <?= json_encode($estados_labels) ?>;
const estadosData   = <?= json_encode($estados_data) ?>;
const bgColors = estadosLabels.map(l => coloresMap[l] || '#94a3b8');

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
            label: 'Prácticas iniciadas',
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