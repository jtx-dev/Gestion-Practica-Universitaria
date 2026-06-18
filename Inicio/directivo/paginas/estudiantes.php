<?php
require_once 'config_directivo/conexion.php';

$filtro_estado = isset($_GET['estado']) ? directivo_estado_practica_normalizado((string) $_GET['estado']) : '';
$busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, (string) $_GET['buscar']) : '';

$estadosPermitidos = array_keys(directivo_estados_practica_ui());
if ($filtro_estado !== '' && !in_array($filtro_estado, $estadosPermitidos, true)) {
    $filtro_estado = '';
}

$where = "WHERE a.id_directivo = $id_directivo AND LOWER(TRIM(a.estado)) = 'activa'";
if ($filtro_estado !== '') {
    $where .= " AND p.estado_practica = '$filtro_estado'";
}
if ($busqueda !== '') {
    $where .= " AND (e.nombre LIKE '%$busqueda%' OR e.apellido LIKE '%$busqueda%')";
}

$estudiantes = mysqli_query($conexion, "
    SELECT
        e.id_usuario,
        e.nombre,
        e.apellido,
        e.nivel_curricular,
        p.id_practica,
        p.estado_practica,
        p.fecha_inicio,
        p.horas_totales,
        o.titulo AS oferta_titulo,
        o.duracion_meses,
        emp.razon_social,
        COALESCE((SELECT COUNT(*) FROM bitacora b WHERE b.id_practica = p.id_practica), 0) AS total_bitacoras,
        COALESCE((SELECT SUM(b2.horas_registradas) FROM bitacora b2 WHERE b2.id_practica = p.id_practica), 0) AS horas_bitacora
    FROM asignacion a
    JOIN estudiante e ON e.id_usuario = a.id_estudiante
    LEFT JOIN practica p ON p.id_estudiante = e.id_usuario
    LEFT JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    LEFT JOIN empresa emp ON emp.id_usuario = o.id_empresa
    $where
    ORDER BY e.apellido ASC, e.nombre ASC, p.fecha_inicio DESC
");

$ver_id = isset($_GET['ver']) ? (int) $_GET['ver'] : 0;
?>

<div class="card-section mb-4">
    <div class="card-header-custom">
        <i class="bi bi-funnel-fill text-primary"></i>
        <h6>Filtrar estudiantes</h6>
    </div>
    <div class="p-3">
        <form method="GET" action="inicio.php" class="row g-2 align-items-end">
            <input type="hidden" name="pagina" value="estudiantes">
            <div class="col-sm-6 col-md-5">
                <label class="form-label small fw-semibold text-muted">Buscar por nombre</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="buscar" class="form-control" placeholder="Nombre o apellido"
                           value="<?= htmlspecialchars($busqueda) ?>">
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <label class="form-label small fw-semibold text-muted">Estado de practica</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach (directivo_estados_practica_ui() as $valor => $label): ?>
                        <option value="<?= htmlspecialchars($valor) ?>" <?= $filtro_estado === $valor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
                <a href="inicio.php?pagina=estudiantes" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if (mysqli_num_rows($estudiantes) === 0): ?>
    <div class="card-section p-5 text-center text-muted">
        <i class="bi bi-people fs-1 d-block mb-2"></i>
        <strong>No hay estudiantes asignados con practica para tus filtros actuales.</strong>
        <p class="small mt-1 mb-0">Prueba cambiar los filtros de busqueda.</p>
    </div>
<?php else: ?>
    <div class="card-section">
        <div class="card-header-custom justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i>
                <h6>Estudiantes asignados</h6>
            </div>
            <span class="badge bg-primary"><?= mysqli_num_rows($estudiantes) ?> resultados</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Empresa / Oferta</th>
                        <th>Estado</th>
                        <th>Avance horas</th>
                        <th>Bitacoras</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($estudiantes)): ?>
                    <?php
                        $horas_realizadas = (int) ($row['horas_bitacora'] ?? 0);
                        $horas_esperadas = (int) ($row['duracion_meses'] ?? 0) * 4 * 42;
                        $porcentaje = $horas_esperadas > 0 ? min(100, round($horas_realizadas / $horas_esperadas * 100)) : 0;
                        $estadoPractica = (string) ($row['estado_practica'] ?? '');
                        $textoEstado = $estadoPractica !== '' ? directivo_etiqueta_estado_practica($estadoPractica) : 'Sin practica';
                        $clsEstado = $estadoPractica !== '' ? directivo_clase_estado_practica($estadoPractica) : 'badge-secondary';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></div>
                            <div class="text-muted" style="font-size:.75rem;">Nivel <?= htmlspecialchars((string) ($row['nivel_curricular'] ?? '—')) ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:.82rem;"><?= htmlspecialchars((string) ($row['razon_social'] ?? 'Sin practica asignada')) ?></div>
                            <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars((string) ($row['oferta_titulo'] ?? 'Sin oferta asignada')) ?></div>
                        </td>
                        <td><span class="badge-estado <?= $clsEstado ?>"><?= htmlspecialchars($textoEstado) ?></span></td>
                        <td style="min-width:130px;">
                            <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;">
                                <span><?= $horas_realizadas ?>h registradas</span>
                                <span class="text-muted"><?= $porcentaje ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width:<?= $porcentaje ?>%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-journal-text me-1"></i><?= (int) ($row['total_bitacoras'] ?? 0) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($row['id_practica'])): ?>
                                <a href="inicio.php?pagina=estudiantes&ver=<?= (int) $row['id_usuario'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Ver detalle
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Sin practica asignada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
if ($ver_id > 0) {
    $resultado = mysqli_query($conexion, "
        SELECT e.nombre, e.apellido, e.nivel_curricular, e.habilidades,
               p.id_practica, p.estado_practica, p.fecha_inicio, p.fecha_termino, p.nota_final,
               o.titulo, emp.razon_social
        FROM asignacion a
        JOIN estudiante e ON e.id_usuario = a.id_estudiante
        LEFT JOIN practica p ON p.id_estudiante = e.id_usuario
        LEFT JOIN oferta_practica o ON o.id_oferta = p.id_oferta
        LEFT JOIN empresa emp ON emp.id_usuario = o.id_empresa
        WHERE a.id_directivo = $id_directivo
          AND a.id_estudiante = $ver_id
          AND LOWER(TRIM(a.estado)) = 'activa'
        ORDER BY p.id_practica DESC
        LIMIT 1
    ");
    $p = $resultado ? mysqli_fetch_assoc($resultado) : null;

    if ($p && !empty($p['id_practica'])) {
        $id_practica = (int) $p['id_practica'];
        $bitacoras = mysqli_query($conexion, "
            SELECT fecha_registro, actividades, logros, horas_registradas
            FROM bitacora
            WHERE id_practica = $id_practica
            ORDER BY fecha_registro DESC
        ");
        ?>
        <div class="mt-4 card-section">
            <div class="card-header-custom justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <h6>Detalle - <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></h6>
                </div>
                <a href="inicio.php?pagina=estudiantes" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Empresa</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars((string) ($p['razon_social'] ?? 'Sin empresa')) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Oferta</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars((string) ($p['titulo'] ?? 'Sin oferta')) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Estado</p>
                        <span class="badge-estado <?= directivo_clase_estado_practica((string) ($p['estado_practica'] ?? '')) ?>">
                            <?= htmlspecialchars(directivo_etiqueta_estado_practica((string) ($p['estado_practica'] ?? ''))) ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Fecha inicio</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars((string) ($p['fecha_inicio'] ?? '—')) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Fecha termino</p>
                        <p class="fw-semibold mb-0"><?= htmlspecialchars((string) ($p['fecha_termino'] ?? 'En curso')) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Nota final</p>
                        <p class="fw-semibold mb-0 <?= !empty($p['nota_final']) ? 'text-success' : 'text-muted' ?>">
                            <?= !empty($p['nota_final']) ? number_format((float) $p['nota_final'], 1) : 'Sin calificar' ?>
                        </p>
                    </div>
                </div>
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-journal-text me-2"></i>Bitacoras registradas</h6>
                <?php if (mysqli_num_rows($bitacoras) === 0): ?>
                    <p class="text-muted">Este estudiante no ha registrado bitacoras aún.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr><th>Fecha</th><th>Actividades</th><th>Logros</th><th>Horas</th></tr>
                            </thead>
                            <tbody>
                            <?php while ($b = mysqli_fetch_assoc($bitacoras)): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $b['fecha_registro']) ?></td>
                                    <td><?= nl2br(htmlspecialchars(substr((string) $b['actividades'], 0, 120))) ?><?= strlen((string) $b['actividades']) > 120 ? '…' : '' ?></td>
                                    <td><?= !empty($b['logros']) ? nl2br(htmlspecialchars(substr((string) $b['logros'], 0, 100))) : '<span class="text-muted">—</span>' ?></td>
                                    <td><strong><?= (int) $b['horas_registradas'] ?>h</strong></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } elseif ($p) {
        ?>
        <div class="mt-4 card-section p-4 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <strong>El estudiante tiene asignacion, pero aun no registra una practica asociada.</strong>
        </div>
        <?php
    }
}
?>
