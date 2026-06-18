<?php
include('../../conexion.php');
//Para ocultar ofertas ya postuladas
$id_estudiante = 3;
$sql = "
SELECT o.*, e.nombre_empresa
FROM oferta_practica o
INNER JOIN empresa e
ON o.id_empresa = e.id_usuario
WHERE o.id_oferta NOT IN
(
    SELECT id_oferta
    FROM postulacion
    WHERE id_estudiante = $id_estudiante
)
";
$sqlEstudiante = "
    SELECT e.*, c.nombre_carrera
    FROM estudiante e
    INNER JOIN carrera c
    ON e.id_carrera = c.id_carrera
    WHERE e.id_usuario = 3
";

$resEstudiante = mysqli_query($conexion, $sqlEstudiante);
$estudiante = mysqli_fetch_assoc($resEstudiante);
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Estudiante - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include('sidebar.php'); ?>

    <main class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="mb-1">Panel Estudiante</h1>
                Hola <?php echo $estudiante['nombre']; ?>, estas son las ofertas disponibles.
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="mb-0 fw-bold"><?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?></p>
                    <small class="text-muted"><?php echo $estudiante['nombre_carrera']; ?></small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-12">
                <div class="card card-custom bg-white p-4">
                    <h5 class="fw-bold mb-4">Estado de mi Proceso</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-secondary mb-2">Carga de Documentos</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                            <small class="text-success mt-1 d-inline-block">Completado <i
                                    class="bi bi-check-circle"></i></small>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-secondary mb-2">Validación de Empresa</label>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 40%"></div>
                            </div>
                            <small class="text-muted mt-1 d-inline-block">En proceso (40%)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-custom bg-white">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">Ofertas Para Practica</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Empresa / Oferta</th>
                                        <th>Duración</th>
                                        <th>Cupos</th>
                                        <th>Estado</th>
                                        <th class="pe-4 text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php while ($oferta = mysqli_fetch_assoc($resultado)) { ?>

                                        <tr>

                                            <td class="ps-4">
                                                <div class="fw-bold">
                                                    <?php echo $oferta['titulo']; ?>
                                                </div>
                                            
                                                <small class="text-muted d-block">
                                                    Empresa: <?php echo $oferta['nombre_empresa']; ?>
                                                </small>
                                            
                                                <small class="text-muted">
                                                    <?php
                                                    if ($oferta['estado_oferta'] == 'activa') {
                                                        echo "Oferta disponible";
                                                    } else {
                                                        echo "Oferta cerrada";
                                                    }
                                                    ?>
                                                </small>
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo $oferta['duracion_meses']; ?> meses
                                                </span>
                                            </td>

                                            <td class="fw-bold <?php echo ($oferta['cupos'] > 0) ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $oferta['cupos']; ?> cupos
                                            </td>

                                            <td>
                                                <?php if ($oferta['estado_oferta'] == 'activa') { ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php } else { ?>
                                                    <span class="badge bg-danger">Cerrada</span>
                                                <?php } ?>

                                            </td>

                                            <td class="pe-4 text-end">
                                                <a href="detalle_oferta.php?id=<?php echo $oferta['id_oferta']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalle</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>