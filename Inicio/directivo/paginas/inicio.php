<?php
require_once '../config_directivo/conexion.php';

// Estadísticas rápidas para la carrera del directivo
$stats = [];

// Total estudiantes en práctica activa
$r = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera
    AND p.estado_practica NOT IN ('Finalizada','Cancelada')");

$stats['activos'] = mysqli_fetch_assoc($r)['total'];

// Prácticas finalizadas
$r = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera AND p.estado_practica = 'Finalizada'");

$stats['finalizadas'] = mysqli_fetch_assoc($r)['total'];

// Evaluaciones pendientes del directivo
$r = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE p.id_directivo = $id_directivo
    AND p.estado_practica = 'Informe Entregado'
    AND p.nota_final IS NULL");

$stats['pendientes_eval'] = mysqli_fetch_assoc($r)['total'];

// Promedio nota final
$r = mysqli_query($conexion, "SELECT ROUND(AVG(p.nota_final),1) as promedio FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    WHERE e.id_carrera = $id_carrera AND p.nota_final IS NOT NULL");

$stats['promedio'] = mysqli_fetch_assoc($r)['promedio'] ?? '—';
// Últimas 5 prácticas con actividad reciente
$recientes = mysqli_query($conexion,"
    SELECT e.nombre, e.apellido, p.estado_practica, p.fecha_inicio, o.titulo
    FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    JOIN Oferta_Practica o ON o.id_oferta = p.id_oferta
    WHERE e.id_carrera = $id_carrera
    ORDER BY p.fecha_inicio DESC
    LIMIT 5
");
mysqli_close($conexion)
?>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">
                <i class="bi bi-person-check-fill" style="color:#1e40af;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $stats['activos'] ?></div>
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
                <div class="stat-value"><?= $stats['finalizadas'] ?></div>
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
                <div class="stat-value"><?= $stats['pendientes_eval'] ?></div>
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
                <div class="stat-value"><?= $stats['promedio'] ?></div>
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
                <tr><td colspan="4" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                    No hay prácticas registradas para tu carrera.
                </td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($recientes)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></strong></td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td><?php
                        $map = [
                            'Asignado'          => 'badge-asignado',
                            'En Curso'          => 'badge-en-curso',
                            'Finalizada'        => 'badge-finalizada',
                            'Evaluado'          => 'badge-evaluado',
                            'Postulado'         => 'badge-postulado',
                            'Cancelada'         => 'badge-cancelada',
                            'Informe Entregado' => 'badge-postulado',
                        ];
                        $cls = $map[$row['estado_practica']] ?? 'badge-finalizada';
                    ?>
                    <span class="badge-estado <?= $cls ?>"><?= htmlspecialchars($row['estado_practica']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
