<?php
require_once '../config_directivo/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de evaluación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_evaluacion'])) {
    $id_practica     = (int)$_POST['id_practica'];
    $nota_final      = (float)str_replace(',', '.', $_POST['nota_final']);
    $comentarios     = mysqli_real_escape_string($conexion, $_POST['comentarios'] ?? '');
    $fecha_hoy       = date('Y-m-d');

    // Validaciones
    if ($nota_final < 1.0 || $nota_final > 7.0) {
        $mensaje = 'La nota debe estar entre 1.0 y 7.0.';
        $tipo_mensaje = 'danger';
    } else {
        // Verificar que la práctica pertenece a este directivo y carrera
        $check = mysqli_query($conexion, "
            SELECT p.id_practica FROM Practica p
            JOIN Estudiante e ON e.id_usuario = p.id_estudiante
            WHERE p.id_practica = $id_practica
              AND p.id_directivo = $id_directivo
              AND e.id_carrera = $id_carrera
        ");

        if (mysqli_num_rows($check) === 0) {
            $mensaje = 'No tienes permiso para evaluar esta práctica.';
            $tipo_mensaje = 'danger';
        } else {
            // Insertar evaluación
            mysqli_query($conexion,"
                INSERT INTO Evaluacion (id_practica, nota_final, comentarios, fecha_evaluacion)
                VALUES ($id_practica, $nota_final, '$comentarios', '$fecha_hoy')
            ");

            // Actualizar nota_final en Práctica y estado
            mysqli_query($conexion,"
                UPDATE Practica
                SET nota_final = $nota_final, estado_practica = 'Evaluado'
                WHERE id_practica = $id_practica
            ");

            $mensaje = 'Evaluación registrada correctamente.';
            $tipo_mensaje = 'success';
        }
    }
}

// Prácticas pendientes de evaluación por el directivo
$pendientes = mysqli_query($conexion,"
    SELECT p.id_practica, e.nombre, e.apellido, o.titulo, emp.razon_social,
           p.fecha_inicio, p.estado_practica
    FROM Practica p
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    JOIN Oferta_Practica o ON o.id_oferta = p.id_oferta
    JOIN Empresa emp ON emp.id_usuario = o.id_empresa
    WHERE p.id_directivo = $id_directivo
      AND e.id_carrera = $id_carrera
      AND p.estado_practica IN ('Informe Entregado','En Curso')
      AND p.nota_final IS NULL
    ORDER BY p.fecha_inicio DESC
");

// Evaluaciones ya realizadas por el directivo
$realizadas = mysqli_query($conexion,"
    SELECT p.id_practica, e.nombre, e.apellido, o.titulo,
           ev.nota_final, ev.comentarios, ev.fecha_evaluacion
    FROM Evaluacion ev
    JOIN Practica p ON p.id_practica = ev.id_practica
    JOIN Estudiante e ON e.id_usuario = p.id_estudiante
    JOIN Oferta_Practica o ON o.id_oferta = p.id_oferta
    WHERE p.id_directivo = $id_directivo
      AND e.id_carrera = $id_carrera
    ORDER BY ev.fecha_evaluacion DESC
    LIMIT 10
");
mysqli_close($conexion);

$modal_id = isset($_GET['evaluar']) ? (int)$_GET['evaluar'] : null;
?>

<?php if ($mensaje): ?>
<div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi <?= $tipo_mensaje === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
    <div><?= htmlspecialchars($mensaje) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Pendientes -->
<div class="card-section mb-4">
    <div class="card-header-custom justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hourglass-split text-warning"></i>
            <h6>Pendientes de evaluación</h6>
        </div>
        <?php if (mysqli_num_rows($pendientes) > 0): ?>
        <span class="badge bg-warning text-dark"><?= $pendientes->num_rows ?> pendiente(s)</span>
        <?php endif; ?>
    </div>

    <?php if (mysqli_num_rows($pendientes) === 0): ?>
    <div class="p-5 text-center text-muted">
        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
        <strong>¡Sin evaluaciones pendientes!</strong>
        <p class="small mt-1 mb-0">Todas las prácticas asignadas han sido evaluadas.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>Estudiante</th><th>Oferta</th><th>Empresa</th><th>Fecha inicio</th><th>Acción</th></tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($pendientes)): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                <td><?= htmlspecialchars($row['titulo']) ?></td>
                <td><?= htmlspecialchars($row['razon_social']) ?></td>
                <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalEvaluar"
                        data-id="<?= $row['id_practica'] ?>"
                        data-nombre="<?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?>"
                        data-oferta="<?= htmlspecialchars($row['titulo']) ?>">
                        <i class="bi bi-pencil-fill me-1"></i>Evaluar
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Realizadas -->
<div class="card-section">
    <div class="card-header-custom">
        <i class="bi bi-clipboard2-check-fill text-success"></i>
        <h6>Evaluaciones realizadas (últimas 10)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr><th>Estudiante</th><th>Oferta</th><th>Nota</th><th>Comentarios</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($realizadas) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aún no has registrado evaluaciones.</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($realizadas)): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></td>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td>
                        <span class="fw-bold fs-6 <?= $row['nota_final'] >= 4.0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($row['nota_final'],1) ?>
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

<!-- Modal Evaluación -->
<div class="modal fade" id="modalEvaluar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--color-primary);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-clipboard2-check-fill me-2"></i>Registrar evaluación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?pagina=evaluacion">
                <div class="modal-body">
                    <input type="hidden" name="id_practica" id="modal_id_practica">
                    <p class="mb-3 text-muted small">
                        Evaluando a: <strong id="modal_nombre_estudiante"></strong><br>
                        Oferta: <span id="modal_oferta"></span>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nota final <span class="text-danger">*</span></label>
                        <input type="number" name="nota_final" class="form-control"
                               min="1.0" max="7.0" step="0.1" placeholder="Ej: 5.5" required>
                        <div class="form-text">Escala 1.0 – 7.0</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Comentarios</label>
                        <textarea name="comentarios" class="form-control" rows="4"
                            placeholder="Observaciones sobre el desempeño del estudiante…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="guardar_evaluacion" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Guardar evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const modalEvaluar = document.getElementById('modalEvaluar');
modalEvaluar.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modal_id_practica').value     = btn.getAttribute('data-id');
    document.getElementById('modal_nombre_estudiante').textContent = btn.getAttribute('data-nombre');
    document.getElementById('modal_oferta').textContent    = btn.getAttribute('data-oferta');
});
</script>
