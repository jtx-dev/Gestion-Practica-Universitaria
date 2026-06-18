<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es coordinador
if (!isset($_SESSION['nombre_rol']) || strtolower($_SESSION['nombre_rol']) !== 'coordinador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

include('../../conexion.php');
include('../../helpers.php');
/** @var mysqli $conexion */

/* MANEJAR REVERSIÓN A PENDIENTE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'revertir') {
    $id_revertir = (int)$_POST['id_oferta'];
    $sql_revertir = "UPDATE oferta_practica SET estado_oferta = 'pendiente_aprobacion' WHERE id_oferta = ?";
    $stmt_rev = mysqli_prepare($conexion, $sql_revertir);
    mysqli_stmt_bind_param($stmt_rev, "i", $id_revertir);
    mysqli_stmt_execute($stmt_rev);
    mysqli_stmt_close($stmt_rev);
    $mensaje = "Oferta devuelta a estado pendiente de revisión.";
}

/* MANEJAR RECOMENDACIÓN */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'recomendar') {
    $id_est_rec = (int)$_POST['id_estudiante'];
    $titulo_oferta_rec = $_POST['titulo_oferta'];
    
    $titulo_notif = "¡Práctica Recomendada!";
    $mensaje_notif = "Tu coordinador te recomienda revisar la oferta: '$titulo_oferta_rec'. Tu perfil hace match con lo que buscan.";
    
    $sql_notif = "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES (?, ?, ?, 'recomendacion')";
    $stmt_notif = mysqli_prepare($conexion, $sql_notif);
    mysqli_stmt_bind_param($stmt_notif, "iss", $id_est_rec, $titulo_notif, $mensaje_notif);
    mysqli_stmt_execute($stmt_notif);
    mysqli_stmt_close($stmt_notif);
    $mensaje = "Recomendación enviada al alumno.";
}

/* CONSULTAR OFERTAS APROBADAS */
$sql_ofertas = "SELECT
                    o.id_oferta,
                    o.id_carrera,
                    o.titulo,
                    o.descripcion,
                    o.requisitos,
                    o.cupos,
                    o.duracion_meses,
                    o.estado_oferta,
                    o.fecha_publicacion,
                    o.fecha_cierre,
                    e.nombre_empresa,
                    e.rut_empresa,
                    c.nombre_carrera
                FROM oferta_practica o
                INNER JOIN empresa e ON o.id_empresa = e.id_usuario
                INNER JOIN carrera c ON o.id_carrera = c.id_carrera
                WHERE o.estado_oferta = 'activa'
                ORDER BY o.fecha_publicacion DESC";

$resultado = mysqli_query($conexion, $sql_ofertas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertas Aprobadas - Coordinador</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/base.css" rel="stylesheet">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">
                Panel Coordinador
            </div>
        </div>

        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link" href="inicio.php">
                <i class="bi bi-speedometer2 me-2"></i> Vista Global
            </a>

            <a class="nav-link" href="alumnos.php">
                <i class="bi bi-people me-2"></i> Alumnos
            </a>

            <a class="nav-link" href="ofertas.php">
                <i class="bi bi-building me-2"></i> Empresas / Ofertas
            </a>

            <a class="nav-link active" href="ofertas_aprobadas.php">
                <i class="bi bi-check-circle me-2"></i> Ofertas Aprobadas
            </a>

            <a class="nav-link" href="ofertas_rechazadas.php">
                <i class="bi bi-x-circle me-2"></i> Ofertas Rechazadas
            </a>

            <a class="nav-link" href="validacion.php">
                <i class="bi bi-file-earmark-check me-2"></i> Validaciones
            </a>

            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <!-- Contenido principal -->
    <main class="main-content">

        <header class="mb-5">
            <h2 class="fw-bold mb-1">Ofertas Aprobadas</h2>
            <p class="text-muted">
                Ofertas activas con sistema de matching de postulantes.
            </p>
        </header>

        <div class="card card-custom p-4 bg-white">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Listado de Ofertas Aprobadas</h4>
            </div>

            <?php if (isset($mensaje)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php if (mysqli_num_rows($resultado) > 0) { ?>

                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                    <div class="offer-card p-4 mb-3 border rounded shadow-sm bg-white">
                        
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-primary mb-0">
                                <?php echo $fila['titulo']; ?>
                            </h5>
                            
                            <!-- Botón para revertir -->
                            <form method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas enviar esta oferta de vuelta a revisión? (Los estudiantes ya no la verán)');">
                                <input type="hidden" name="accion" value="revertir">
                                <input type="hidden" name="id_oferta" value="<?php echo $fila['id_oferta']; ?>">
                                <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Deshacer Aprobación
                                </button>
                            </form>
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-secondary me-1">
                                <?php echo $fila['nombre_carrera']; ?>
                            </span>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Aprobada
                            </span>
                        </div>

                        <p class="text-muted small mb-2">
                            <?php echo $fila['descripcion']; ?>
                        </p>

                        <p class="small mb-2">
                            <strong>Requisitos:</strong>
                            <?php echo $fila['requisitos']; ?>
                        </p>

                        <div class="small text-muted">
                            <strong>Empresa:</strong>
                            <?php echo $fila['nombre_empresa']; ?>

                            <span class="mx-2">|</span>

                            <strong>RUT:</strong>
                            <?php echo $fila['rut_empresa']; ?>
                        </div>

                        <div class="small text-muted mt-1">
                            <strong>Cupos:</strong>
                            <?php echo $fila['cupos']; ?>

                            <span class="mx-2">|</span>

                            <strong>Duración:</strong>
                            <?php echo $fila['duracion_meses']; ?> meses
                        </div>

                        <hr>

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-trophy me-2"></i>Top 2 Postulantes con Mayor Afinidad</h6>
                        <?php
                        $id_oferta = $fila['id_oferta'];
                        $id_carrera_oferta = $fila['id_carrera'];
                        $titulo_oferta = htmlspecialchars($fila['titulo']);

                        // 1. OBTENER POSTULANTES
                        $sql_postulantes = "SELECT e.id_usuario, e.nombre, e.apellido, e.habilidades, p.cv_estudiante
                                            FROM postulacion p
                                            INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
                                            WHERE p.id_oferta = '$id_oferta'";
                        $res_post = mysqli_query($conexion, $sql_postulantes);
                        $lista_postulantes = [];
                        if ($res_post) {
                            while ($p = mysqli_fetch_assoc($res_post)) {
                                $p['porcentaje_afinidad'] = calcular_afinidad_tags($conexion, $p['id_usuario'], $id_oferta);
                                $lista_postulantes[] = $p;
                            }
                        }
                        
                        usort($lista_postulantes, function($a, $b) { return $b['porcentaje_afinidad'] <=> $a['porcentaje_afinidad']; });
                        $top_postulantes = array_slice($lista_postulantes, 0, 2);

                        if (!empty($top_postulantes)) {
                            foreach ($top_postulantes as $al) {
                                $afinidad = $al['porcentaje_afinidad'];
                                $badge_color = $afinidad >= 70 ? 'bg-success' : 'bg-warning text-dark';
                                ?>
                                <div class="d-flex justify-content-between align-items-center p-2 mb-1 border-bottom">
                                    <div>
                                        <span class="fw-bold small"><?php echo htmlspecialchars($al['nombre'] . " " . $al['apellido']); ?></span>
                                        <div class="x-small text-muted" style="font-size: 0.75rem;">Habilidades: <?php echo htmlspecialchars($al['habilidades'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge <?php echo $badge_color; ?>"><?php echo $afinidad; ?>%</span>
                                        <?php if ($al['cv_estudiante']) { ?>
                                            <a href="../<?php echo $al['cv_estudiante']; ?>" target="_blank" class="btn btn-outline-primary btn-sm d-block mt-1" style="font-size: 0.7rem; padding: 0.1rem 0.3rem;"><i class="bi bi-file-pdf"></i> CV</a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted small mb-3">Aún no hay postulantes para esta oferta.</p>';
                        }
                        ?>

                        <h6 class="fw-bold text-success mb-3 mt-4"><i class="bi bi-stars me-2"></i>Sugerencias del Sistema (No han postulado)</h6>
                        <?php
                        // 2. OBTENER SUGERENCIAS (No postulantes y máximo 2 recomendaciones previas)
                        $sql_sugerencias = "SELECT e.id_usuario, e.nombre, e.apellido, e.habilidades
                                            FROM estudiante e
                                            WHERE e.id_carrera = '$id_carrera_oferta'
                                            AND e.id_usuario NOT IN (SELECT id_estudiante FROM postulacion WHERE id_oferta = '$id_oferta')
                                            AND (SELECT COUNT(*) FROM notificacion n WHERE n.id_usuario = e.id_usuario AND n.tipo_evento = 'recomendacion') < 2";
                        $res_sug = mysqli_query($conexion, $sql_sugerencias);
                        $lista_sugerencias = [];
                        if ($res_sug) {
                            while ($s = mysqli_fetch_assoc($res_sug)) {
                                $s['porcentaje_afinidad'] = calcular_afinidad_tags($conexion, $s['id_usuario'], $id_oferta);
                                if ($s['porcentaje_afinidad'] > 0) {
                                    $lista_sugerencias[] = $s;
                                }
                            }
                        }
                        
                        usort($lista_sugerencias, function($a, $b) { return $b['porcentaje_afinidad'] <=> $a['porcentaje_afinidad']; });
                        $top_sugerencias = array_slice($lista_sugerencias, 0, 2);

                        if (!empty($top_sugerencias)) {
                            foreach ($top_sugerencias as $al) {
                                $afinidad = $al['porcentaje_afinidad'];
                                $badge_color = $afinidad >= 70 ? 'bg-success' : 'bg-warning text-dark';
                                ?>
                                <div class="d-flex justify-content-between align-items-center p-2 mb-1 border-bottom">
                                    <div>
                                        <span class="fw-bold small"><?php echo htmlspecialchars($al['nombre'] . " " . $al['apellido']); ?></span>
                                        <div class="x-small text-muted" style="font-size: 0.75rem;">Habilidades: <?php echo htmlspecialchars($al['habilidades'] ?: 'N/A'); ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?php echo $badge_color; ?>"><?php echo $afinidad; ?>%</span>
                                        <form method="POST" class="m-0">
                                            <input type="hidden" name="accion" value="recomendar">
                                            <input type="hidden" name="id_estudiante" value="<?php echo $al['id_usuario']; ?>">
                                            <input type="hidden" name="titulo_oferta" value="<?php echo $titulo_oferta; ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm" style="font-size: 0.7rem; padding: 0.1rem 0.3rem;" title="Notificar a este alumno recomendándole postular">
                                                <i class="bi bi-bell-fill"></i> Avisar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted small mb-0">No hay otros alumnos compatibles registrados.</p>';
                        }
                        ?>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="alert alert-info mb-0">
                    No hay ofertas aprobadas.
                </div>

            <?php } ?>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>