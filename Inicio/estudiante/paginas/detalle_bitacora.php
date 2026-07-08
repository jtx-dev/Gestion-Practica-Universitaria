<?php
// Las variables $conexion e $id_estudiante vienen de auth.php

if (!isset($_GET['id'])) {
    header('Location: inicio.php?pagina=documentos');
    exit;
}

$id_bitacora = (int)$_GET['id'];

// Obtener detalles de la bitácora y validar que pertenezca al estudiante logueado
$sql = "
SELECT b.*, p.id_estudiante, p.estado_practica
FROM bitacora b
INNER JOIN practica p ON b.id_practica = p.id_practica
WHERE b.id_bitacora = $id_bitacora 
AND p.id_estudiante = $id_estudiante
LIMIT 1
";

$resultado = mysqli_query($conexion, $sql);
$bitacora = $resultado ? mysqli_fetch_assoc($resultado) : null;

if (!$bitacora) {
    header('Location: inicio.php?pagina=documentos');
    exit;
}

$error = null;
$mensaje = null;

if (isset($_POST['modificar_bitacora'])) {
    if ($bitacora['estado_practica'] !== 'en_curso') {
        $error = "No puedes modificar esta bitácora porque tu práctica no se encuentra activa en curso.";
    } else {
        $actividades = mysqli_real_escape_string($conexion, $_POST['actividades']);
        $logros = mysqli_real_escape_string($conexion, $_POST['logros']);
        $horas = (int)$_POST['horas_registradas'];

        if (empty($actividades) || empty($horas)) {
            $error = "Por favor completa todos los campos obligatorios";
        } elseif ($horas > 42) {
            $error = "Has superado el límite de 42 horas semanales permitidas por norma institucional";
        }

        if ($error === null) {
            $sqlUpdate = "
            UPDATE bitacora
            SET actividades = '$actividades',
                logros = '$logros',
                horas_registradas = $horas
            WHERE id_bitacora = $id_bitacora
            ";

            if (mysqli_query($conexion, $sqlUpdate)) {
                $mensaje = "Bitácora actualizada correctamente";
                // Actualizar los datos locales del array
                $bitacora['actividades'] = $_POST['actividades'];
                $bitacora['logros'] = $_POST['logros'];
                $bitacora['horas_registradas'] = $horas;
            } else {
                $error = "Error al actualizar la bitácora";
            }
        }
    }
}
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
            title: 'Modificación Exitosa',
            text: '<?php echo htmlspecialchars($mensaje); ?>',
            confirmButtonColor: '#0d6efd',
            background: '#ffffff',
            color: '#495057'
        });
    </script>
<?php } ?>

<header class="mb-5">
    <h2 class="mb-1 fw-bold">Detalle de Bitácora</h2>
    <p class="text-muted">Visualiza y edita los detalles del registro de tu bitácora profesional.</p>
</header>

<div class="card card-custom mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Detalles del Registro</h4>
            <span class="badge bg-secondary px-3 py-2 fs-6">
                Fecha Registro: <?php echo date("d-m-Y", strtotime($bitacora['fecha_registro'])); ?>
            </span>
        </div>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Horas Registradas</label>
                    <input type="number" name="horas_registradas" class="form-control" 
                           value="<?php echo htmlspecialchars($bitacora['horas_registradas']); ?>" 
                           <?php echo $bitacora['estado_practica'] !== 'en_curso' ? 'disabled' : ''; ?> required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Actividades Realizadas</label>
                <textarea name="actividades" class="form-control" rows="4" 
                          <?php echo $bitacora['estado_practica'] !== 'en_curso' ? 'disabled' : ''; ?> required><?php echo htmlspecialchars($bitacora['actividades']); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Logros Obtenidos</label>
                <textarea name="logros" class="form-control" rows="4"
                          <?php echo $bitacora['estado_practica'] !== 'en_curso' ? 'disabled' : ''; ?>><?php echo htmlspecialchars($bitacora['logros']); ?></textarea>
            </div>
            
            <div class="mt-4">
                <a href="inicio.php?pagina=documentos" class="btn btn-secondary me-2">Volver</a>
                <?php if ($bitacora['estado_practica'] === 'en_curso'): ?>
                    <button type="submit" name="modificar_bitacora" class="btn btn-primary">Guardar Cambios</button>
                <?php else: ?>
                    <button type="button" class="btn btn-warning text-dark" disabled><i class="bi bi-lock-fill me-1"></i>Modificación Bloqueada</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
