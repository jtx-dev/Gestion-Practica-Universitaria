<?php
// Las variables $conexion e $id_estudiante vienen de auth.php

// Obtener la práctica activa del estudiante
$resPractica = mysqli_query($conexion, "SELECT id_practica FROM practica WHERE id_estudiante = $id_estudiante LIMIT 1");
$practica = mysqli_fetch_assoc($resPractica);
$idPractica = $practica['id_practica'] ?? 0;

$error = null;
$mensaje = null;

if (isset($_POST['guardar_bitacora'])) {
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha_registro']);
    $actividades = mysqli_real_escape_string($conexion, $_POST['actividades']);
    $logros = mysqli_real_escape_string($conexion, $_POST['logros']);
    $horas = (int)$_POST['horas_registradas'];

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

    if ($error === null) {
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
            $horas
        )
        ";

        if (mysqli_query($conexion, $sql)) {
            $mensaje = "Bitácora guardada correctamente";
        } else {
            $error = "Error al guardar la bitácora";
        }
    }
}

$resBitacoras = mysqli_query($conexion, "
    SELECT *
    FROM bitacora
    WHERE id_practica = $idPractica
    ORDER BY fecha_registro DESC
");
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($error)) { ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo htmlspecialchars($error); ?>',
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
            text: '<?php echo htmlspecialchars($mensaje); ?>',
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
        <div class="table-responsive">
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
                    <?php if ($resBitacoras && mysqli_num_rows($resBitacoras) > 0): ?>
                        <?php while ($bitacora = mysqli_fetch_assoc($resBitacoras)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bitacora['fecha_registro']); ?></td>
                                <td><?php echo htmlspecialchars($bitacora['horas_registradas']); ?></td>
                                <td><?php echo htmlspecialchars(substr($bitacora['actividades'], 0, 40)); ?>...</td>
                                <td>
                                    <a href="inicio.php?pagina=detalle_bitacora&id=<?php echo $bitacora['id_bitacora']; ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay registros de bitácoras aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
