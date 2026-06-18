<?php
include('../../conexion.php');
$idPractica = 1;

if (isset($_POST['guardar_bitacora'])) {

    $fecha = $_POST['fecha_registro'];
    $actividades = $_POST['actividades'];
    $logros = $_POST['logros'];
    $horas = $_POST['horas_registradas'];

    $consultaUltima = mysqli_query(
        $conexion,
        "SELECT fecha_registro
        FROM bitacora
        WHERE id_practica = $idPractica
        ORDER BY fecha_registro DESC
        LIMIT 1"
    );

    $ultimaBitacora = mysqli_fetch_assoc($consultaUltima);

    if (empty($fecha) || empty($actividades) || empty($horas)) {

        $error = "Por favor completa todos los campos";

    } elseif ($horas > 42) {

        $error = "Has superado el límite de 42 horas semanales permitidas por norma institucional";

    } elseif ($ultimaBitacora) {

        $dias = floor(
            (strtotime($fecha) - strtotime($ultimaBitacora['fecha_registro']))
            / 86400
        );

        if ($dias < 15) {

            $error = "Deben transcurrir 15 días entre cada registro de bitácora";

        }

    }

    if (!isset($error)) {

        $sql = "
        INSERT INTO bitacora
        (
            id_practica,
            fecha_registro,
            actividades,
            logros,
            horas_registradas
        )
        VALUES
        (
            $idPractica,
            '$fecha',
            '$actividades',
            '$logros',
            '$horas'
        )
        ";

        mysqli_query($conexion, $sql);

        $mensaje = "Bitácora guardada correctamente";
    }
}


$resBitacoras = mysqli_query($conexion, "
    SELECT *
    FROM bitacora
    WHERE id_practica = $idPractica
    ORDER BY fecha_registro DESC
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis Documentos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include('sidebar.php'); ?>

    <div class="main-content">

        <h1 class="mb-1">Mis Documentos</h1>
        <p class="text-muted mb-4">Seguimiento de tu practica profesional.</p>
        <?php if (isset($error)) { ?>

            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $error; ?>',
                    confirmButtonColor: '#0d6efd',
                    background: '#ffffff',
                    color: '#495057'
                });
            </script>

        <?php } ?>
        <?php if (isset($mensaje)) { ?>

            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Bitácora registrada',
                    text: '<?php echo $mensaje; ?>',
                    confirmButtonColor: '#0d6efd',
                    background: '#ffffff',
                    color: '#495057'
                });
            </script>

        <?php } ?>

        <div class="card card-custom mb-4">

            <div class="card-body p-4">

                <h4 class="mb-4">Registrar Bitácora</h4>

                <form method="POST">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha_registro" class="form-control" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Horas</label>
                            <input type="number" name="horas_registradas" class="form-control" required>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Actividades</label>
                        <textarea name="actividades" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">Logros</label>
                        <textarea name="logros" class="form-control" rows="3"></textarea>

                    </div>

                    <button type="submit" name="guardar_bitacora" class="btn btn-primary">Guardar Registro</button>

                </form>

            </div>

        </div>

        <div class="card card-custom">

            <div class="card-body p-4">
                <div class="alert alert-info">
                    Las bitácoras deben registrarse cada 15 días según la normativa de práctica profesional.
                </div>
                <h4 class="fw-bold mb-4">Historial de Bitácoras</h4>

                <table class="table table-hover">

                    <thead class="table-light">

                        <tr>
                            <th>Fecha</th>
                            <th>Horas</th>
                            <th>Actividad</th>
                            <th>Acción</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($bitacora = mysqli_fetch_assoc($resBitacoras)) { ?>

                            <tr>

                                <td>
                                    <?php echo $bitacora['fecha_registro']; ?>
                                </td>

                                <td>
                                    <?php echo $bitacora['horas_registradas']; ?>
                                </td>

                                <td>
                                    <?php echo substr($bitacora['actividades'], 0, 40); ?>
                                </td>

                                <td>

                                    <a href="detalle_bitacora.php?id=<?php echo $bitacora['id_bitacora']; ?>"
                                        class="btn btn-sm btn-outline-primary">Ver</a>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>



    </div>

    </div>

</body>

</html>