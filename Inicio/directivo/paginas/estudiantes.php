<?php
require_once 'config_directivo/conexion.php';


/** Filtros */
$filtro_estado = isset($_GET['estado']) ? mysqli_real_escape_string($conexion ,$_GET['estado']) : '';
$busqueda      = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion ,$_GET['buscar']) : '';

$where = "WHERE e.id_carrera = $id_carrera AND p.estado_practica NOT IN ('Cancelada')";
if ($filtro_estado) $where .= " AND p.estado_practica = '$filtro_estado'";
if ($busqueda)      $where .= " AND (e.nombre LIKE '%$busqueda%' OR e.apellido LIKE '%$busqueda%')";

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
        (SELECT COUNT(*) FROM bitacora b WHERE b.id_practica = p.id_practica) AS total_bitacoras,
        (SELECT SUM(b2.horas_registradas) FROM bitacora b2 WHERE b2.id_practica = p.id_practica) AS horas_bitacora
    FROM practica p
    JOIN estudiante e ON e.id_usuario = p.id_estudiante
    JOIN oferta_practica o ON o.id_oferta = p.id_oferta
    JOIN empresa emp ON emp.id_usuario = o.id_empresa
    $where
    ORDER BY e.apellido ASC
");


// Ver detalle de un estudiante
$ver_id = isset($_GET['ver']) ? (int)$_GET['ver'] : null;
?>

<!- - Consultas y filtros - - > 
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
                    <input type="text" name="buscar" class="form-control" placeholder="Nombre o apellido…"
                           value="<?= htmlspecialchars($busqueda) ?>">
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <label class="form-label small fw-semibold text-muted">Estado de práctica</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach (['Postulado','Asignado','En Curso','Informe Entregado','Evaluado','Finalizada'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filtro_estado === $s ? 'selected' : '' ?>><?= $s ?></option>
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
<! - - Consulta de estudiantes en practica - - >
<?php if (mysqli_num_rows($estudiantes) === 0): ?>
<div class="card-section p-5 text-center text-muted">
    <i class="bi bi-people fs-1 d-block mb-2"></i>
    <strong>No hay estudiantes en práctica para tu carrera actualmente.</strong>
    <p class="small mt-1 mb-0">Prueba cambiar los filtros de búsqueda.</p>
</div>

<?php else: ?>
<!- - Tabla estudiantes - - > 
<div class="card-section">
    <div class="card-header-custom justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i>
            <h6>Estudiantes en práctica</h6>
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
                    <th>Bitácoras</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($estudiantes)):
                $horas_realizadas = (int)($row['horas_bitacora'] ?? 0);
                $horas_esperadas  = (int)($row['duracion_meses'] ?? 1) * 4 * 42; // aprox
                $porcentaje = $horas_esperadas > 0 ? min(100, round($horas_realizadas / $horas_esperadas * 100)) : 0;
                $map = [
                    'Asignado' => 'badge-asignado','En Curso' => 'badge-en-curso',
                    'Finalizada' => 'badge-finalizada','Evaluado' => 'badge-evaluado',
                    'Postulado' => 'badge-postulado','Informe Entregado' => 'badge-postulado',
                ];
                $cls = $map[$row['estado_practica']] ?? 'badge-finalizada';
            ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?></div>
                    <div class="text-muted" style="font-size:.75rem;">Nivel <?= $row['nivel_curricular'] ?></div>
                </td>
                <td>
                    <div class="fw-semibold" style="font-size:.82rem;"><?= htmlspecialchars($row['razon_social']) ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($row['oferta_titulo']) ?></div>
                </td>
                <td><span class="badge-estado <?= $cls ?>"><?= htmlspecialchars($row['estado_practica']) ?></span></td>
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
                        <i class="bi bi-journal-text me-1"></i><?= $row['total_bitacoras'] ?>
                    </span>
                </td>
                <td>
                    <a href="inicio.php?pagina=estudiantes&ver=<?= $row['id_usuario'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Ver detalle
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
// Detalle estudiantes 
if ($ver_id):
    $conn2 = $conexion;
    $resultado = mysqli_query($conn2,"
        SELECT e.nombre, e.apellido, e.nivel_curricular, e.habilidades,
               p.id_practica, p.estado_practica, p.fecha_inicio, p.fecha_termino, p.nota_final,
               o.titulo, emp.razon_social
        FROM practica p
        JOIN estudiante e ON e.id_usuario = p.id_estudiante
        JOIN oferta_practica o ON o.id_oferta = p.id_oferta
        JOIN empresa emp ON emp.id_usuario = o.id_empresa
        WHERE p.id_estudiante = $ver_id AND e.id_carrera = $id_carrera
        ORDER BY p.id_practica DESC LIMIT 1
    ");
    $p = mysqli_fetch_assoc($resultado);

    if ($p):
        $id_practica = $p['id_practica'];
        $bitacoras = mysqli_query($conn2,"
            SELECT fecha_registro, actividades, logros, horas_registradas
            FROM bitacora WHERE id_practica = $id_practica ORDER BY fecha_registro DESC
        ");
?>
<div class="mt-4 card-section">
    <div class="card-header-custom justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary"></i>
            <h6>Detalle — <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></h6>
        </div>
        <a href="inicio.php?pagina=estudiantes" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
    <div class="p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Empresa</p>
                <p class="fw-semibold mb-0"><?= htmlspecialchars($p['razon_social']) ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Oferta</p>
                <p class="fw-semibold mb-0"><?= htmlspecialchars($p['titulo']) ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Estado</p>
                <?php $map=['Asignado'=>'badge-asignado','En Curso'=>'badge-en-curso','Finalizada'=>'badge-finalizada','Evaluado'=>'badge-evaluado','Postulado'=>'badge-postulado','Informe Entregado'=>'badge-postulado'];$cls=$map[$p['estado_practica']]??'badge-finalizada'; ?>
                <span class="badge-estado <?= $cls ?>"><?= $p['estado_practica'] ?></span>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Fecha inicio</p>
                <p class="fw-semibold mb-0"><?= $p['fecha_inicio'] ?? '—' ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Fecha término</p>
                <p class="fw-semibold mb-0"><?= $p['fecha_termino'] ?? 'En curso' ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Nota final</p>
                <p class="fw-semibold mb-0 <?= $p['nota_final'] ? 'text-success' : 'text-muted' ?>">
                    <?= $p['nota_final'] ? number_format($p['nota_final'],1) : 'Sin calificar' ?>
                </p>
            </div>
        </div>
        // busqueda de bitacoras
        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-journal-text me-2"></i>Bitácoras registradas</h6>
        <?php if (mysqli_num_rows($bitacoras) === 0): ?>
            <p class="text-muted">Este estudiante no ha registrado bitácoras aún.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Fecha</th><th>Actividades</th><th>Logros</th><th>Horas</th></tr>
                </thead>
                <tbody>
                <?php while ($b = mysqli_fetch_assoc($bitacoras)): ?>
                <tr>
                    <td><?= htmlspecialchars($b['fecha_registro']) ?></td>
                    <td><?= nl2br(htmlspecialchars(substr($b['actividades'], 0, 120))) ?><?= strlen($b['actividades']) > 120 ? '…' : '' ?></td>
                    <td><?= $b['logros'] ? nl2br(htmlspecialchars(substr($b['logros'], 0, 100))) : '<span class="text-muted">—</span>' ?></td>
                    <td><strong><?= $b['horas_registradas'] ?>h</strong></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif;
    mysqli_close($conn2);
endif;
?>
