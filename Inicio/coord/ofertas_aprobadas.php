<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Redirigir si no es coordinador
if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'Coordinador') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

include('../../conexion.php');
/** @var mysqli $conexion */

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

            <h4 class="fw-bold mb-4">Listado de Ofertas Aprobadas</h4>

            <?php if (mysqli_num_rows($resultado) > 0) { ?>

                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                    <div class="offer-card p-4 mb-3">

                        <h5 class="fw-bold mb-1">
                            <?php echo $fila['titulo']; ?>
                        </h5>

                        <div class="mb-2">
                            <span class="badge bg-primary">
                                <?php echo $fila['nombre_carrera']; ?>
                            </span>

                            <span class="badge bg-success">
                                Aprobada
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

                        <h6 class="fw-bold">Matching de postulantes (Algoritmo de Afinidad)</h6>

                        <?php
                        $id_oferta = $fila['id_oferta'];

                        // Nuevo Algoritmo de Afinidad: 
                        // Calcula el porcentaje basado en la cercanía de las competencias del estudiante a los requisitos de la oferta.
                        $sql_postulantes = "SELECT 
                                                e.id_usuario,
                                                e.nombre,
                                                e.apellido,
                                                e.nivel_curricular,
                                                p.id_postulacion,
                                                p.cv_estudiante,
                                                -- Obtenemos las palabras clave (competencias) relacionadas
                                                GROUP_CONCAT(CONCAT(c.nombre, ' (Lvl ', ec.nivel_actual, ')') SEPARATOR ', ') as palabras_clave,
                                                -- Cálculo de afinidad: 100 menos el promedio de las diferencias (escalado de 1-5 a 0-100)
                                                ROUND(100 - AVG(ABS(r.nivel_requerido - ec.nivel_actual) * 20), 0) AS porcentaje_afinidad
                                            FROM postulacion p
                                            INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
                                            INNER JOIN oferta_requisitos r ON p.id_oferta = r.id_oferta
                                            INNER JOIN competencias c ON r.id_competencia = c.id
                                            INNER JOIN estudiante_competencias ec ON ec.id_estudiante = e.id_usuario 
                                                AND ec.id_competencia = r.id_competencia
                                            WHERE p.id_oferta = '$id_oferta'
                                            GROUP BY e.id_usuario
                                            ORDER BY porcentaje_afinidad DESC";

                        $resultado_postulantes = mysqli_query($conexion, $sql_postulantes);

                        if (mysqli_num_rows($resultado_postulantes) > 0) {

                            while ($postulante = mysqli_fetch_assoc($resultado_postulantes)) {
                                $afinidad = $postulante['porcentaje_afinidad'];
                                $badge_color = 'bg-danger';
                                if ($afinidad >= 80) $badge_color = 'bg-success';
                                elseif ($afinidad >= 50) $badge_color = 'bg-warning text-dark';
                        ?>

                                <div class="border rounded p-3 mb-2 bg-light shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong class="text-primary">
                                                <?php echo htmlspecialchars($postulante['nombre'] . " " . $postulante['apellido']); ?>
                                            </strong>
                                            <div class="small text-muted">
                                                Nivel curricular: <?php echo $postulante['nivel_curricular']; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge <?php echo $badge_color; ?> fs-6">
                                                <?php echo $afinidad; ?>% afinidad
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block fw-bold">Palabras clave / Competencias:</small>
                                        <span class="small text-dark">
                                            <i class="bi bi-tags-fill me-1 text-secondary"></i>
                                            <?php echo htmlspecialchars($postulante['palabras_clave']); ?>
                                        </span>
                                    </div>

                                    <?php if ($postulante['cv_estudiante'] != "") { ?>
                                        <a href="../<?php echo $postulante['cv_estudiante']; ?>" 
                                           target="_blank"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-earmark-pdf"></i> Ver CV
                                        </a>
                                    <?php } ?>
                                </div>

                        <?php
                            }

                        } else {
                            // Si no hay datos en las nuevas tablas de competencias, mostramos un aviso
                            // o podrías mantener un fallback a la lógica antigua.
                        ?>

                            <div class="alert alert-info mt-2 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay postulantes registrados o falta configurar sus competencias para el matching.
                            </div>

                        <?php } ?>

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