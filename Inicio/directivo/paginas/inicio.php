<?php
require_once __DIR__ . '/../../../conexion.php';

$joinAsignacion = directivo_join_asignacion($id_directivo);
$stats = [];

$r = mysqli_query($conexion, "SELECT COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    {$joinAsignacion}
    WHERE p.estado_practica NOT IN ('finalizado', 'cancelada')");
$stats['activos'] = (int) (mysqli_fetch_assoc($r)['total'] ?? 0);

$r = mysqli_query($conexion, "SELECT COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    {$joinAsignacion}
    WHERE p.estado_practica = 'finalizado'");
$stats['finalizadas'] = (int) (mysqli_fetch_assoc($r)['total'] ?? 0);

$r = mysqli_query($conexion, "SELECT COUNT(*) AS total
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    {$joinAsignacion}
    WHERE p.estado_practica IN ('informe_entregado', 'en_curso')
      AND p.nota_final IS NULL");
$stats['pendientes_eval'] = (int) (mysqli_fetch_assoc($r)['total'] ?? 0);

$r = mysqli_query($conexion, "SELECT ROUND(AVG(p.nota_final), 1) AS promedio
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    {$joinAsignacion}
    WHERE p.nota_final IS NOT NULL");
$stats['promedio'] = mysqli_fetch_assoc($r)['promedio'] ?? '—';

$recientes = mysqli_query($conexion, "
    SELECT e.nombre, e.apellido, p.estado_practica, p.fecha_inicio, o.titulo
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    {$joinAsignacion}
    WHERE p.estado_practica NOT IN ('cancelada')
    ORDER BY p.fecha_inicio DESC
    LIMIT 5
");
mysqli_close($conexion);
?>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <i class="bi bi-person-check-fill" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-value"><?= (int) $stats['activos'] ?></div>
                <div class="stat-label">Prácticas activas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5;">
                <i class="bi bi-patch-check-fill" style="color:#065f46;"></i>
            </div>
            <div>
                <div class="stat-value"><?= (int) $stats['finalizadas'] ?></div>
                <div class="stat-label">Prácticas finalizadas</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;">
                <i class="bi bi-hourglass-split" style="color:#92400e;"></i>
            </div>
            <div>
                <div class="stat-value"><?= (int) $stats['pendientes_eval'] ?></div>
                <div class="stat-label">Evaluaciones pendientes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;">
                <i class="bi bi-star-fill" style="color:#5b21b6;"></i>
            </div>
            <div>
                <div class="stat-value"><?= htmlspecialchars((string) $stats['promedio']) ?></div>
                <div class="stat-label">Promedio notas finales</div>
            </div>
        </div>
    </div>
</div>

<!-- Actividad reciente -->
<div class="card-section">
    <div class="card-header-custom">
        <i class="bi bi-clock-history text-primary"></i>
        <h6>Actividad reciente</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Oferta</th>
                    <th>Estado</th>
                    <th>Fecha inicio</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($recientes) === 0): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                        No hay prácticas registradas para tus asignaciones.
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($recientes)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></strong></td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td>
                        <?php $cls = directivo_clase_estado_practica((string) ($row['estado_practica'] ?? '')); ?>
                        <span class="badge-estado <?= $cls ?>">
                            <?= htmlspecialchars(directivo_etiqueta_estado_practica((string) ($row['estado_practica'] ?? ''))) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars((string) ($row['fecha_inicio'] ?? '—')) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
