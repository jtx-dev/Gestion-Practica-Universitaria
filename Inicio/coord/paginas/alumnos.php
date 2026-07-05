<?php
// Consulta de alumnos de la carrera
$sql_alumnos = "SELECT e.id_usuario, e.nombre, e.apellido, e.nivel_curricular, e.habilidades, u.correo, u.estado_cuenta,
                       p.estado_practica
                FROM estudiante e
                JOIN usuario u ON e.id_usuario = u.id_usuario
                LEFT JOIN practica p ON e.id_usuario = p.id_estudiante
                WHERE e.id_carrera = ?
                ORDER BY e.apellido ASC";

$stmt_alumnos = mysqli_prepare($conexion, $sql_alumnos);
mysqli_stmt_bind_param($stmt_alumnos, "i", $id_carrera);
mysqli_stmt_execute($stmt_alumnos);
$resultado = mysqli_stmt_get_result($stmt_alumnos);

// Pre-cargar ofertas activas para el matching
$sql_ofertas = "SELECT id_oferta, titulo, requisitos, empresa.nombre_empresa 
                FROM oferta_practica 
                JOIN empresa ON oferta_practica.id_empresa = empresa.id_usuario
                WHERE oferta_practica.id_carrera = ? AND estado_oferta = 'activa'";
$stmt_o = mysqli_prepare($conexion, $sql_ofertas);
mysqli_stmt_bind_param($stmt_o, "i", $id_carrera);
mysqli_stmt_execute($stmt_o);
$res_ofertas = mysqli_stmt_get_result($stmt_o);
$ofertas_base = [];
while ($o = mysqli_fetch_assoc($res_ofertas)) {
    $ofertas_base[] = $o;
}
?>

<header class="mb-5">
    <h2 class="mb-1">Listado de Alumnos</h2>
    <p class="text-muted">Gestión y seguimiento de estudiantes de la carrera.</p>
</header>

<div class="card card-custom p-4 bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Alumno</th>
                    <th>Correo</th>
                    <th>Nivel Curricular</th>
                    <th>Estado Práctica</th>
                    <th>Cuenta</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($alumno = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></div>
                                <small class="text-muted">ID: <?php echo $alumno['id_usuario']; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($alumno['correo']); ?></td>
                            <td>Nivel <?php echo $alumno['nivel_curricular']; ?></td>
                            <td>
                                <?php 
                                    $estado = $alumno['estado_practica'] ?? 'No iniciada';
                                    $badge_class = 'bg-secondary';
                                    if ($estado === 'En Curso') $badge_class = 'bg-primary';
                                    if ($estado === 'Finalizada') $badge_class = 'bg-success';
                                    if ($estado === 'Postulado') $badge_class = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo $alumno['estado_cuenta'] === 'activa' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($alumno['estado_cuenta']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-success" 
                                        title="Ofertas sugeridas" 
                                        onclick='verSugerencias(<?php echo json_encode([
                                            "nombre" => $alumno["nombre"] . " " . $alumno["apellido"],
                                            "habilidades" => $alumno["habilidades"] ?: "Sin registrar",
                                            "sugerencias" => array_map(function($o) use ($alumno, $conexion) {
                                                return [
                                                    "titulo" => $o["titulo"],
                                                    "empresa" => $o["nombre_empresa"],
                                                    "afinidad" => calcular_afinidad_tags($conexion, $alumno["id_usuario"], $o["id_oferta"])
                                                ];
                                            }, $ofertas_base)
                                        ]); ?>)'>
                                    <i class="bi bi-stars"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" title="Ver Perfil"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No se encontraron alumnos para esta carrera.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Sugerencias -->
<div class="modal fade" id="modalSugerencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-stars text-success me-2"></i>Ofertas Recomendadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalSugerenciasCuerpo"></div>
            </div>
        </div>
    </div>
</div>

<script>
function verSugerencias(data) {
    let cuerpo = `<h6>Alumno: <strong>${data.nombre}</strong></h6>`;
    cuerpo += `<p class="small text-muted">Habilidades: ${data.habilidades}</p><hr>`;

    let sugerencias = data.sugerencias
        .filter(s => s.afinidad > 0)
        .sort((a, b) => b.afinidad - a.afinidad);

    if (sugerencias.length > 0) {
        sugerencias.forEach(s => {
            let color = s.afinidad >= 70 ? 'success' : (s.afinidad >= 40 ? 'warning text-dark' : 'danger');
            cuerpo += `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <div class="fw-bold">${s.titulo}</div>
                        <div class="small text-muted">${s.empresa}</div>
                    </div>
                    <span class="badge bg-${color}">${s.afinidad}%</span>
                </div>
            `;
        });
    } else {
        cuerpo += `<div class="alert alert-warning small">No se encontraron ofertas compatibles con las habilidades de este alumno.</div>`;
    }

    document.getElementById('modalSugerenciasCuerpo').innerHTML = cuerpo;
    new bootstrap.Modal(document.getElementById('modalSugerencias')).show();
}
</script>
