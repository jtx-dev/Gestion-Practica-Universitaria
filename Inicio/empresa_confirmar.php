<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../conexion.php');

$token = $_GET['token'] ?? '';
$mensaje = '';
$tipo_mensaje = '';
$postulacion = null;
$gestionado = false;

if ($token === '') {
    $mensaje = 'Token de confirmación no válido o ausente.';
    $tipo_mensaje = 'danger';
} else {
    // 1. Obtener la postulación y sus detalles
    $sql = "SELECT p.id_postulacion, p.id_estudiante, p.id_oferta, p.cv_estudiante, p.fecha_limite_confirmacion, p.estado_postulacion,
                   e.nombre AS nombre_estudiante, e.apellido AS apellido_estudiante, e.id_carrera,
                   o.titulo AS titulo_oferta, o.duracion_meses,
                   emp.nombre_empresa, emp.nombre_encargado,
                   c.nombre_carrera
            FROM postulacion p
            INNER JOIN estudiante e ON p.id_estudiante = e.id_usuario
            INNER JOIN oferta_practica o ON p.id_oferta = o.id_oferta
            INNER JOIN empresa emp ON o.id_empresa = emp.id_usuario
            INNER JOIN carrera c ON e.id_carrera = c.id_carrera
            WHERE p.token_confirmacion = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $postulacion = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
    }

    if (!$postulacion) {
        $mensaje = 'Enlace de confirmación no válido o expirado.';
        $tipo_mensaje = 'danger';
    } elseif ($postulacion['estado_postulacion'] !== 'espera') {
        $mensaje = 'Esta postulación ya ha sido respondida previamente como: ' . ucfirst($postulacion['estado_postulacion']) . '.';
        $tipo_mensaje = 'info';
        $gestionado = true;
    } elseif (new DateTime() > new DateTime($postulacion['fecha_limite_confirmacion'])) {
        $mensaje = 'El plazo de confirmación para este enlace ha expirado.';
        $tipo_mensaje = 'warning';
    }

    // 2. Procesar acción de aceptación o rechazo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postulacion && !$gestionado) {
        $accion = $_POST['accion'] ?? '';
        $id_postulacion = (int)$postulacion['id_postulacion'];
        $id_estudiante = (int)$postulacion['id_estudiante'];
        $id_oferta = (int)$postulacion['id_oferta'];
        $id_carrera = (int)$postulacion['id_carrera'];

        mysqli_begin_transaction($conexion);
        try {
            if ($accion === 'aceptar') {
                // Actualizar estado de postulación
                mysqli_query($conexion, "UPDATE postulacion SET estado_postulacion = 'aceptada' WHERE id_postulacion = $id_postulacion");

                // Buscar coordinador de la carrera
                $res_coord = mysqli_query($conexion, "SELECT id_usuario FROM coordinador WHERE id_carrera = $id_carrera LIMIT 1");
                $coord = mysqli_fetch_assoc($res_coord);
                $id_coordinador = $coord ? (int)$coord['id_usuario'] : 'NULL';

                // Buscar directivo de la carrera
                $res_dir = mysqli_query($conexion, "SELECT id_usuario FROM directivo WHERE id_carrera = $id_carrera LIMIT 1");
                $dir = mysqli_fetch_assoc($res_dir);
                $id_directivo = $dir ? (int)$dir['id_usuario'] : 'NULL';

                $fecha_hoy = date('Y-m-d');

                // Crear o actualizar la práctica en estado 'en_curso'
                // Primero revisar si ya tiene un registro de práctica
                $check_practica = mysqli_query($conexion, "SELECT id_practica FROM practica WHERE id_estudiante = $id_estudiante LIMIT 1");
                if (mysqli_num_rows($check_practica) > 0) {
                    mysqli_query($conexion, "UPDATE practica 
                        SET estado_practica = 'en_curso', id_oferta = $id_oferta, id_coordinador = $id_coordinador, id_directivo = $id_directivo, fecha_inicio = '$fecha_hoy' 
                        WHERE id_estudiante = $id_estudiante");
                } else {
                    mysqli_query($conexion, "INSERT INTO practica 
                        (id_estudiante, id_oferta, id_coordinador, id_directivo, estado_practica, fecha_inicio) 
                        VALUES ($id_estudiante, $id_oferta, $id_coordinador, $id_directivo, 'en_curso', '$fecha_hoy')");
                }

                // Enviar notificación al estudiante
                $titulo_not = mysqli_real_escape_string($conexion, "¡Postulación Aceptada!");
                $msg_not = mysqli_real_escape_string($conexion, "La empresa " . $postulacion['nombre_empresa'] . " ha aceptado tu postulación para '" . $postulacion['titulo_oferta'] . "'. Tu práctica profesional se encuentra ahora EN CURSO.");
                mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_estudiante, '$titulo_not', '$msg_not', 'confirmacion')");

                // También crear una asignación académica formal si no existe
                $check_asig = mysqli_query($conexion, "SELECT id_asignacion FROM asignacion WHERE id_estudiante = $id_estudiante LIMIT 1");
                if (mysqli_num_rows($check_asig) === 0 && $id_coordinador !== 'NULL' && $id_directivo !== 'NULL') {
                    mysqli_query($conexion, "INSERT INTO asignacion (id_estudiante, id_coordinador, id_directivo, estado) VALUES ($id_estudiante, $id_coordinador, $id_directivo, 'activa')");
                }

                mysqli_commit($conexion);
                $mensaje = 'Candidato aceptado con éxito. El estudiante ha sido notificado y su proceso de práctica está "En curso".';
                $tipo_mensaje = 'success';
                $gestionado = true;

            } elseif ($accion === 'rechazar') {
                // Actualizar estado de postulación
                mysqli_query($conexion, "UPDATE postulacion SET estado_postulacion = 'rechazada' WHERE id_postulacion = $id_postulacion");

                // Devolver el cupo de la oferta
                mysqli_query($conexion, "UPDATE oferta_practica SET cupos = cupos + 1 WHERE id_oferta = $id_oferta");

                // Enviar notificación al estudiante
                $titulo_not = mysqli_real_escape_string($conexion, "Actualización de Postulación");
                $msg_not = mysqli_real_escape_string($conexion, "La empresa " . $postulacion['nombre_empresa'] . " ha revisado tu postulación para '" . $postulacion['titulo_oferta'] . "' y ha decidido continuar con otros perfiles.");
                mysqli_query($conexion, "INSERT INTO notificacion (id_usuario, titulo, mensaje, tipo_evento) VALUES ($id_estudiante, '$titulo_not', '$msg_not', 'rechazo')");

                mysqli_commit($conexion);
                $mensaje = 'Postulación rechazada. El estudiante ha sido notificado.';
                $tipo_mensaje = 'warning';
                $gestionado = true;
            }
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = 'Error al procesar la solicitud: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Candidato - SGPPE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card-confirm {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            max-width: 600px;
            width: 100%;
        }
        .btn-action {
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card card-confirm p-4">
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 bg-primary bg-opacity-10 text-primary rounded-circle mb-3">
                    <i class="bi bi-building fs-1"></i>
                </div>
                <h2 class="fw-bold">Confirmación de Práctica</h2>
                <p class="text-muted">Resolución de postulación de estudiante sin credenciales</p>
            </div>

            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show border-0 shadow-sm p-3 mb-4 rounded-3" role="alert">
                    <i class="bi <?php 
                        echo match($tipo_mensaje) {
                            'success' => 'bi-check-circle-fill',
                            'danger' => 'bi-x-circle-fill',
                            'warning' => 'bi-exclamation-triangle-fill',
                            default => 'bi-info-circle-fill'
                        };
                    ?> me-2"></i>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if ($postulacion && !$gestionado && new DateTime() <= new DateTime($postulacion['fecha_limite_confirmacion'])): ?>
                <div class="border rounded-3 p-4 bg-light bg-opacity-50 mb-4">
                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person-fill me-2"></i>Datos del Estudiante</h5>
                    <p class="mb-2"><strong>Nombre:</strong> <?= htmlspecialchars($postulacion['nombre_estudiante'] . ' ' . $postulacion['apellido_estudiante']) ?></p>
                    <p class="mb-2"><strong>Carrera:</strong> <?= htmlspecialchars($postulacion['nombre_carrera']) ?></p>
                    <p class="mb-3"><strong>Oferta:</strong> <?= htmlspecialchars($postulacion['titulo_oferta']) ?> (<?= htmlspecialchars($postulacion['duracion_meses']) ?> meses)</p>
                    
                    <a href="archivos/cv/<?= urlencode($postulacion['cv_estudiante']) ?>" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Ver Currículum Vitae
                    </a>
                </div>

                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info small mb-4 rounded-3">
                    <i class="bi bi-info-circle-fill me-1"></i> Al confirmar, la práctica del estudiante pasará a estar activa y en estado "En curso", notificando al alumno de inmediato.
                </div>

                <form method="POST" class="d-flex gap-3 justify-content-center">
                    <button type="submit" name="accion" value="aceptar" class="btn btn-success btn-action btn-lg w-50">
                        <i class="bi bi-check-lg me-2"></i>Aceptar Candidato
                    </button>
                    <button type="submit" name="accion" value="rechazar" class="btn btn-outline-danger btn-action btn-lg w-50" onclick="return confirm('¿Estás seguro de que deseas rechazar este postulante?');">
                        <i class="bi bi-x-lg me-2"></i>Rechazar
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p class="text-muted small">Este enlace es de uso único para la empresa encargada de resolver la postulación.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
