<?php
// Las variables $conexion e $id_carrera vienen definidas desde auth.php

// Consultar todas las postulaciones activas (estado = 'espera')
$sql_postulaciones = "SELECT p.id_postulacion, p.id_estudiante, p.id_oferta, p.fecha_postulacion, p.cv_estudiante, p.token_confirmacion,
                             e.nombre AS nombre_estudiante, e.apellido AS apellido_estudiante, e.habilidades,
                             o.titulo AS titulo_oferta,
                             emp.nombre_empresa
                      FROM postulacion p
                      INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
                      INNER JOIN oferta_practica o ON p.id_oferta = o.id_oferta
                      INNER JOIN empresa emp ON o.id_empresa = emp.id_usuario
                      WHERE e.id_carrera = ? AND p.estado_postulacion = 'espera'
                      ORDER BY p.fecha_postulacion DESC";

$stmt = mysqli_prepare($conexion, $sql_postulaciones);
mysqli_stmt_bind_param($stmt, "i", $id_carrera);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<header class="mb-4">
    <h2 class="fw-bold mb-1">Bandeja de Postulaciones</h2>
    <p class="text-muted">Visualiza de forma manual todas las postulaciones activas de los estudiantes de tu carrera.</p>
</header>

<!-- Filtros Reactivos -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text bg-white text-muted border-end-0 shadow-sm"><i class="bi bi-search"></i></span>
            <input type="text" id="filtro-texto" class="form-control border-start-0 shadow-sm" placeholder="Buscar por alumno, empresa u oferta..." onkeyup="filtrarTabla()">
        </div>
    </div>
    <div class="col-md-4">
        <select id="filtro-afinidad" class="form-select shadow-sm" onchange="filtrarTabla()">
            <option value="todos">Todas las afinidades</option>
            <option value="alta">Afinidad Alta (>= 70%)</option>
            <option value="media-baja">Afinidad Media/Baja (< 70%)</option>
        </select>
    </div>
</div>

<div class="card card-custom p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Alumno</th>
                    <th>Oferta Laboral / Empresa</th>
                    <th class="text-center">Afinidad</th>
                    <th>Fecha Postulación</th>
                    <th class="text-end">Simulación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($resultado)): 
                        $afinidad = calcular_afinidad_tags($conexion, $row['id_estudiante'], $row['id_oferta']);
                        $badge_color = $afinidad >= 70 ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['nombre_estudiante'] . ' ' . $row['apellido_estudiante']); ?></div>
                                <div class="x-small text-muted" style="font-size: 0.75rem;">Aptitudes: <?php echo htmlspecialchars($row['habilidades'] ?: 'N/A'); ?></div>
                                <?php if ($row['cv_estudiante']): ?>
                                    <a href="../archivos/cv/<?php echo htmlspecialchars($row['cv_estudiante']); ?>" target="_blank" class="small text-decoration-none d-inline-block mt-1"><i class="bi bi-file-earmark-pdf"></i> Descargar CV</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['titulo_oferta']); ?></div>
                                <div class="small text-muted"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($row['nombre_empresa']); ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $badge_color; ?> px-2 py-1"><?php echo $afinidad; ?>%</span>
                            </td>
                            <td>
                                <?php echo date('d-m-Y H:i', strtotime($row['fecha_postulacion'])); ?>
                            </td>
                            <td class="text-end">
                                <!-- Botón de Simulación de Enlace de Empresa -->
                                <?php if ($row['token_confirmacion']): ?>
                                    <a href="../empresa_confirmar.php?token=<?php echo $row['token_confirmacion']; ?>" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1" title="Simular enlace de la empresa">
                                        <i class="bi bi-link-45deg"></i> Link Empresa
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Sin token</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr id="sin-postulaciones-tr">
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-clipboard-x d-block mb-2" style="font-size: 2.5rem;"></i>
                            No hay postulaciones activas pendientes de revisión para tu carrera.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filtrarTabla() {
    const texto = document.getElementById('filtro-texto').value.toLowerCase();
    const afinidad = document.getElementById('filtro-afinidad').value;
    const filas = document.querySelectorAll('tbody tr');
    let visibles = 0;

    filas.forEach(fila => {
        // Ignorar fila de "no hay datos"
        if (fila.id === 'sin-postulaciones-tr') return;

        const colAlumno = fila.cells[0].textContent.toLowerCase();
        const colOferta = fila.cells[1].textContent.toLowerCase();
        
        // Obtener valor numérico de afinidad
        const badgeAfinidad = fila.cells[2].querySelector('.badge');
        const afinidadValor = badgeAfinidad ? parseInt(badgeAfinidad.textContent) : 0;

        // Validar filtro de texto
        const coincideTexto = colAlumno.includes(texto) || colOferta.includes(texto);

        // Validar filtro de afinidad
        let coincideAfinidad = true;
        if (afinidad === 'alta') {
            coincideAfinidad = (afinidadValor >= 70);
        } else if (afinidad === 'media-baja') {
            coincideAfinidad = (afinidadValor < 70);
        }

        if (coincideTexto && coincideAfinidad) {
            fila.style.display = '';
            visibles++;
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>

<?php
mysqli_stmt_close($stmt);
?>
