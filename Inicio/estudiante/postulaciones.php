<?php
include('../../conexion.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no es estudiante
if (!isset($_SESSION['id_rol']) || strtolower($_SESSION['nombre_rol']) !== 'estudiante') {
    header('Location: ../iniciar_sesion.php');
    exit;
}

$id_estudiante = $_SESSION['id_usuario'];

//Para cancelar postulaicon y actualice en la tabla de ofertas de practica
if (isset($_GET['cancelar'])) {

    $id_postulacion = $_GET['cancelar'];

    $sql = "
    SELECT id_oferta
    FROM postulacion
    WHERE id_postulacion = $id_postulacion
    ";

    $resultado = mysqli_query($conexion, $sql);
    $postulacion = mysqli_fetch_assoc($resultado);

    mysqli_query(
        $conexion,
        "DELETE FROM postulacion
        WHERE id_postulacion = $id_postulacion"
    );

    mysqli_query(
        $conexion,
        "UPDATE oferta_practica
        SET cupos = cupos + 1,
            estado_oferta = 'activa'
        WHERE id_oferta = " . $postulacion['id_oferta']
    );

    header("Location: postulaciones.php");
    exit();
}
$sql = "
SELECT
    p.id_postulacion,
    p.id_oferta,
    o.titulo,
    p.fecha_postulacion,
    p.estado_postulacion,
    p.cv_estudiante
FROM postulacion p
INNER JOIN oferta_practica o
ON p.id_oferta = o.id_oferta
WHERE p.id_estudiante = $id_estudiante

";

$resultado = mysqli_query($conexion, $sql);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis Postulaciones</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">


</head>

<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-custom p-4">

                    <h1 class="mb-1">Mis Postulaciones</h1>
                    <p class="text-muted mb-4">Revisa el estado de tus postulaciones realizadas.</p>

                    <div class="card shadow-sm p-4">

                        <table class="table table-hover">

                            <thead class="table-primary">
                                <tr>
                                    <th>Oferta</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>CV</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                                    <tr>

                                        <td><?php echo $fila['titulo']; ?></td>

                                        <td><?php echo date("d-m-Y", strtotime($fila['fecha_postulacion'])); ?></td>

                                        <td>
                                            <?php
                                            if ($fila['estado_postulacion'] == "aceptada") {
                                                echo '<span class="badge bg-success">Aceptada</span>';
                                            } elseif ($fila['estado_postulacion'] == "rechazada") {
                                                echo '<span class="badge bg-danger">Rechazada</span>';
                                            } else {
                                                echo '<span class="badge bg-warning text-dark">En espera</span>';
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo !empty($fila['cv_estudiante']) ? $fila['cv_estudiante'] : 'Sin CV'; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a href="detalle_oferta.php?id=<?php echo $fila['id_oferta']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalle</a>
                                            <a href="postulaciones.php?cancelar=<?php echo $fila['id_postulacion']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Deseas cancelar esta postulación?')">Cancelar</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="col-lg-4">

                <div class="card card-custom">

                    <div class="card-body">

                        <h5 class="fw-bold">Resumen</h5>

                        <hr>

                        <p>Total Postulaciones<span class="float-end fw-bold"><?php echo mysqli_num_rows($resultado); ?></span></p>
                        <a href="inicio.php" class="btn btn-primary w-100">Explorar Ofertas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>