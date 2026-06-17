<?php
include('../../conexion.php');

$id_oferta = $_GET['id'];
$id_estudiante = 3;

$sql = "
SELECT *
FROM oferta_practica
WHERE id_oferta = $id_oferta
";

$resultado = mysqli_query($conexion,$sql);
$oferta = mysqli_fetch_assoc($resultado);
$sqlExiste = "
SELECT *
FROM postulacion
WHERE id_estudiante = $id_estudiante
AND id_oferta = $id_oferta
";

$resExiste = mysqli_query($conexion,$sqlExiste);

$yaPostulo = mysqli_num_rows($resExiste) > 0;
if(isset($_POST['postular']) && !$yaPostulo){

    $sqlPostular = "
    INSERT INTO postulacion
    (
        id_estudiante,
        id_oferta,
        cv_estudiante
    )
    VALUES
    (
        $id_estudiante,
        $id_oferta,
        'cv.pdf'
    )
    ";

    mysqli_query($conexion,$sqlPostular);

    header("Location: postulaciones.php");
    exit();
}
if(!$oferta){
    die("Oferta no encontrada");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detalle Oferta</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../assets/css/base.css">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">

    <h1 class="mb-1">
        Detalle de Oferta
    </h1>

    <p class="text-muted mb-4">
        Información completa de la oferta seleccionada.
    </p>

    <div class="card card-custom">

        <div class="card-body p-4">

            <h2 class="mb-4">
                <?php echo $oferta['titulo']; ?>
            </h2>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <h5>Duración</h5>

                    <span class="badge bg-primary">
                        <?php echo $oferta['duracion_meses']; ?> meses
                    </span>

                </div>

                <div class="col-md-6 mb-3">

                    <h5>Cupos</h5>

                    <span class="badge bg-success">
                        <?php echo $oferta['cupos']; ?> cupos
                    </span>

                </div>

            </div>

            <hr>

            <h5>Descripción</h5>

            <p>
                <?php echo $oferta['descripcion']; ?>
            </p>

            <h5>Requisitos</h5>

            <p>
                <?php echo $oferta['requisitos']; ?>
            </p>

            <h5>Estado de Oferta</h5>

            <p>
                <span class="badge bg-success">
                    <?php echo ucfirst($oferta['estado_oferta']); ?>
                </span>
            </p>

            <h5>Fecha de Publicación</h5>

            <p>
                <?php echo date("d-m-Y", strtotime($oferta['fecha_publicacion'])); ?>
            </p>

            <a href="postulaciones.php" class="btn btn-secondary">
                Volver
            </a>
            <?php if(!$yaPostulo){ ?>

                <form method="POST" class="d-inline">
                
                    <button
                        type="submit"
                        name="postular"
                        class="btn btn-primary">
                
                        Postular
                
                    </button>
                
                </form>
                
                <?php } else { ?>
                
                <button
                    class="btn btn-success"
                    disabled>
                
                    Ya postulaste
                
                </button>

            <?php } ?>
        </div>

    </div>

</div>

</body>
</html>