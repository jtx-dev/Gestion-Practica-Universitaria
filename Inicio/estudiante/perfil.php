<?php
include('../../conexion.php');

$idUsuario = 3;

if (isset($_POST['guardar'])) {

    $habilidades = $_POST['habilidades'];

    mysqli_query($conexion, "
        UPDATE estudiante
        SET habilidades = '$habilidades'
        WHERE id_usuario = $idUsuario
    ");

    $mensaje = "Perfil actualizado correctamente";
}

if (isset($_POST['reemplazar_cv']) && !empty($_FILES['cv']['name'])) {

    $consultaCV = mysqli_query($conexion, "
    SELECT cv_estudiante
    FROM estudiante
    WHERE id_usuario = $idUsuario
    ");

    $cvActual = mysqli_fetch_assoc($consultaCV);

    if (!empty($cvActual['cv_estudiante'])) {

        $archivoAnterior = "../../archivos/cv/" . $cvActual['cv_estudiante'];

        if (file_exists($archivoAnterior)) {
            unlink($archivoAnterior);
        }
    }

    $nombreArchivo = $_FILES['cv']['name'];

    move_uploaded_file(
        $_FILES['cv']['tmp_name'],
        "../../archivos/cv/" . $nombreArchivo
    );

    mysqli_query($conexion, "
        UPDATE estudiante
        SET cv_estudiante = '$nombreArchivo'
        WHERE id_usuario = $idUsuario        
    ");
    $mensaje = "CV actualizado correctamente";
}

if (isset($_POST['eliminar_cv'])) {

    $consultaCV = mysqli_query($conexion, "
        SELECT cv_estudiante
        FROM estudiante
        WHERE id_usuario = $idUsuario
    ");

    $cvActual = mysqli_fetch_assoc($consultaCV);

    if (!empty($cvActual['cv_estudiante'])) {

        $archivo = "../../archivos/cv/" . $cvActual['cv_estudiante'];

        if (file_exists($archivo)) {
            unlink($archivo);
        }

        mysqli_query($conexion, "
            UPDATE estudiante
            SET cv_estudiante = ''
            WHERE id_usuario = $idUsuario
        ");
    }

    header("Location: perfil.php");
    exit();

}

$resultado = mysqli_query($conexion, "
    SELECT e.*, c.nombre_carrera
    FROM estudiante e
    INNER JOIN carrera c
    ON e.id_carrera = c.id_carrera
    WHERE e.id_usuario = $idUsuario
");

$estudiante = mysqli_fetch_assoc($resultado);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>

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

        <h1 class="mb-1">Configuración del Perfil</h1>
        <p class="text-muted mb-4">Gestiona la información que verán las empresas en tus postulaciones.</p>

        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-success">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-8">

                <div class="card card-custom mb-4">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-4">

                            <div class="avatar">
                                <?php echo strtoupper(substr($estudiante['nombre'], 0, 1)); ?>
                            </div>

                            <div class="ms-4">
                                <h3 class="mb-1"><?php echo $estudiante['nombre'] . ' ' . $estudiante['apellido']; ?></h3>
                                <p class="text-muted mb-2"><?php echo $estudiante['nombre_carrera']; ?></p>
                                <span class="badge bg-success">Perfil Activo</span>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="fw-bold mb-2">Nivel Curricular</label>
                                <input type="text" class="form-control" value="<?php echo $estudiante['nivel_curricular']; ?>" readonly></div>

                            <div class="col-md-6 mb-3">

                                <label class="fw-bold mb-2">Ramos Aprobados</label>
                                <input type="text" class="form-control" value="<?php echo $estudiante['ramos_aprobados']; ?>" readonly>

                            </div>

                        </div>
                        <form method="POST">
                            <div class="mb-3">

                                <label class="fw-bold mb-2">Habilidades</label>
                                <textarea name="habilidades" class="form-control" rows="5"><?php echo $estudiante['habilidades']; ?></textarea>
                            </div>

                            <button type="submit" name="guardar" class="btn btn-primary">Guardar Cambios</button>
                        </form>

                    </div>
                </div>
                <div class="card card-custom">
                    <div class="card-body">

                        <h4 class="fw-bold mb-4">Currículum Vitae</h4>

                        <div class="text-center py-4">

                            <div class="text-primary" style="font-size:60px;"><i class="bi bi-person-vcard-fill"></i></div>

                            <h5>
                                <?php
                                echo !empty($estudiante['cv_estudiante'])
                                    ? $estudiante['cv_estudiante']
                                    : 'No hay CV cargado';
                                ?>
                            </h5>

                            <p class="text-muted">
                                Gestiona tu currículum para las postulaciones.
                            </p>

                            <form method="POST" enctype="multipart/form-data">

                                <?php if (!empty($estudiante['cv_estudiante'])) { ?>

                                    <div class="mb-3">

                                        <a href="../../archivos/cv/<?php echo $estudiante['cv_estudiante']; ?>" target="_blank"
                                            class="btn btn-success me-2">
                                            Ver CV
                                        </a>

                                        <button type="submit" name="eliminar_cv" class="btn btn-danger">
                                            Eliminar CV
                                        </button>

                                    </div>

                                <?php } ?>

                                <input type="file" name="cv" class="form-control mb-3" accept=".pdf">

                                <button type="submit" name="reemplazar_cv" class="btn btn-primary">

                                    <?php echo !empty($estudiante['cv_estudiante']) ? 'Reemplazar CV' : 'Subir CV'; ?>

                                </button>

                            </form>

                        </div>

                    </div>
                </div>

            </div>
            <div class="col-lg-4"><!-- DERECHA -->

                <div class="card card-custom mb-4">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            Resumen Académico
                        </h5>

                        <hr>

                        <p>
                            <strong>Carrera:</strong><br>
                            <?php echo $estudiante['nombre_carrera']; ?>
                        </p>

                        <p>
                            <strong>Nivel Curricular:</strong><br>
                            <?php echo $estudiante['nivel_curricular']; ?>
                        </p>

                        <p>
                            <strong>Ramos Aprobados:</strong><br>
                            <?php echo $estudiante['ramos_aprobados']; ?>
                        </p>

                    </div>

                </div>

                <div class="card card-custom">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            Habilidades Destacadas
                        </h5>

                        <hr>

                        <p>
                            <?php echo $estudiante['habilidades']; ?>
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>