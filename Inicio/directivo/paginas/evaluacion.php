<?php
require_once __DIR__ . '/../../../conexion.php';

$realizadas = mysqli_query($conexion, "
    SELECT p.id_practica, e.nombre, e.apellido, o.titulo, emp.razon_social,
           ev.nota_final, ev.comentarios, ev.fecha_evaluacion
    FROM evaluacion ev
    JOIN practica p ON p.id_practica = ev.id_practica
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    ORDER BY ev.fecha_evaluacion DESC
");

// Evaluaciones de empresas hechas por estudiantes
$eval_empresas = mysqli_query($conexion, "
    SELECT e.nombre, e.apellido, emp.razon_social,
           ee.nota_final, ee.comentarios, ee.fecha_evaluacion
    FROM evaluacion_empresa ee
    JOIN practica p ON p.id_practica = ee.id_practica
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    " . directivo_join_asignacion($id_directivo) . "
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    ORDER BY ee.fecha_evaluacion DESC
");
?>

<!-- Evaluaciones académicas -->
<div class="card-section mb-4">
    <div class="card-header-custom">
        <i class="bi bi-clipboard2-check-fill text-primary"></i>
        <h6>Evaluaciones académicas de estudiantes</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>Estudiante</th><th>Empresa</th><th>Oferta</th><th>Nota</th><th>Comentarios</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($realizadas) === 0): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-clipboard2 fs-4 d-block mb-1"></i>
                    Aún no hay evaluaciones académicas registradas.
                </td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($realizadas)): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                    <td><?= htmlspecialchars($row['razon_social']) ?></td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td>
                        <span class="fw-bold fs-6 <?= $row['nota_final'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($row['nota_final'], 1) ?>
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:.8rem;max-width:200px;">
                        <?= $row['comentarios'] ? htmlspecialchars(substr($row['comentarios'], 0, 100)) . (strlen($row['comentarios']) > 100 ? '…' : '') : '—' ?>
                    </td>
                    <td><?= htmlspecialchars($row['fecha_evaluacion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Evaluaciones de empresas por estudiantes -->
<div class="card-section">
    <div class="card-header-custom">
        <i class="bi bi-building-check text-success"></i>
        <h6>Evaluaciones de empresas realizadas por estudiantes</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>Estudiante</th><th>Empresa</th><th>Nota</th><th>Comentarios</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($eval_empresas) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-building fs-4 d-block mb-1"></i>
                    Aún no hay evaluaciones de empresas registradas.
                </td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($eval_empresas)): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                    <td><?= htmlspecialchars($row['razon_social']) ?></td>
                    <td>
                        <span class="fw-bold fs-6 <?= $row['nota_final'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($row['nota_final'], 1) ?>
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:.8rem;max-width:200px;">
                        <?= $row['comentarios'] ? htmlspecialchars(substr($row['comentarios'], 0, 100)) . (strlen($row['comentarios']) > 100 ? '…' : '') : '—' ?>
                    </td>
                    <td><?= htmlspecialchars($row['fecha_evaluacion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
