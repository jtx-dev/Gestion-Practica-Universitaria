<?php
// Las variables $conexion, $id_estudiante ($idUsuario), y $estudiante vienen de auth.php
$idUsuario = $id_estudiante;

$mensaje = null;
$tipo_mensaje = 'success';

if (isset($_POST['guardar'])) {
    $habilidades = mysqli_real_escape_string($conexion, $_POST['habilidades']);

    mysqli_begin_transaction($conexion);
    try {
        // Actualizar el campo de texto (fallback)
        mysqli_query($conexion, "
            UPDATE estudiante
            SET habilidades = '$habilidades'
            WHERE id_usuario = $idUsuario        
        ");

        // Limpiar competencias anteriores
        mysqli_query($conexion, "DELETE FROM estudiante_competencias WHERE id_estudiante = $idUsuario");

        // Guardar nuevas competencias por ID
        if (!empty($_POST['competencias_ids'])) {
            $ids = explode(',', $_POST['competencias_ids']);
            if (count($ids) > 7) {
                throw new Exception("No puedes seleccionar más de 7 habilidades destacadas.");
            }
            foreach ($ids as $id_comp) {
                $id_comp = (int)$id_comp;
                if ($id_comp > 0) {
                    mysqli_query($conexion, "INSERT INTO estudiante_competencias (id_estudiante, id_competencia) VALUES ($idUsuario, $id_comp)");
                }
            }
        }
        mysqli_commit($conexion);
        $mensaje = "Habilidades y perfil actualizados correctamente";
        $tipo_mensaje = 'success';
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $mensaje = $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

if (isset($_POST['reemplazar_cv']) && !empty($_FILES['cv']['name'])) {
    $consultaCV = mysqli_query($conexion, "
    SELECT cv_estudiante
    FROM estudiante
    WHERE id_usuario = $idUsuario
    ");

    $cvActual = mysqli_fetch_assoc($consultaCV);

    if (!empty($cvActual['cv_estudiante'])) {
        $archivoAnterior = "../archivos/cv/" . $cvActual['cv_estudiante'];
        if (file_exists($archivoAnterior)) {
            unlink($archivoAnterior);
        }
    }

    $nombreArchivo = $_FILES['cv']['name'];
    
    // Crear el directorio si no existe
    if (!is_dir("../archivos/cv/")) {
        mkdir("../archivos/cv/", 0777, true);
    }

    move_uploaded_file(
        $_FILES['cv']['tmp_name'],
        "../archivos/cv/" . $nombreArchivo
    );

    $nombreArchivoEscaped = mysqli_real_escape_string($conexion, $nombreArchivo);
    mysqli_query($conexion, "
        UPDATE estudiante
        SET cv_estudiante = '$nombreArchivoEscaped'
        WHERE id_usuario = $idUsuario        
    ");
    $mensaje = "CV actualizado correctamente";
    $tipo_mensaje = 'success';
}

if (isset($_POST['eliminar_cv'])) {
    $consultaCV = mysqli_query($conexion, "
        SELECT cv_estudiante
        FROM estudiante
        WHERE id_usuario = $idUsuario
    ");

    $cvActual = mysqli_fetch_assoc($consultaCV);

    if (!empty($cvActual['cv_estudiante'])) {
        $archivo = "../archivos/cv/" . $cvActual['cv_estudiante'];
        if (file_exists($archivo)) {
            unlink($archivo);
        }

        mysqli_query($conexion, "
            UPDATE estudiante
            SET cv_estudiante = ''
            WHERE id_usuario = $idUsuario
        ");
    }

    header("Location: inicio.php?pagina=perfil");
    exit();
}

// Recargar los datos del estudiante
$resultado = mysqli_query($conexion, "
    SELECT e.*, c.nombre_carrera
    FROM estudiante e
    INNER JOIN carrera c
    ON e.id_carrera = c.id_carrera
    WHERE e.id_usuario = $idUsuario
");
$estudiante = mysqli_fetch_assoc($resultado);
?>

<?php if (isset($mensaje)) { ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php } ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="avatar" style="width: 50px; height: 50px; border-radius: 50%; background: #0d6efd; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 24px;">
                        <?php echo strtoupper(substr($estudiante['nombre'], 0, 1)); ?>
                    </div>
                    <div class="ms-4">
                        <h3 class="mb-1"><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h3>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($estudiante['nombre_carrera']); ?></p>
                        <span class="badge bg-success">Perfil Activo</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold mb-2">Nivel Curricular</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($estudiante['nivel_curricular']); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold mb-2">Ramos Aprobados</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($estudiante['ramos_aprobados']); ?>" readonly>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="fw-bold mb-3"><i class="bi bi-stars text-primary me-2"></i>Mis Habilidades Clave (Matching Inteligente)</label>
                        <p class="text-muted small">Selecciona las etiquetas que mejor describen tus conocimientos técnicos y blandos.</p>
                        
                        <div class="d-flex flex-wrap gap-2 p-3 border rounded bg-white shadow-sm mb-3" id="contenedor-habilidades" style="min-height: 50px;">
                            <!-- Se cargan dinámicamente -->
                        </div>

                        <input type="hidden" name="competencias_ids" id="competencias_ids">
                        <input type="hidden" name="habilidades" id="habilidades_texto">
                    </div>

                    <button type="submit" name="guardar" class="btn btn-primary px-4 fw-bold shadow-sm">Guardar Cambios y Habilidades</button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', async function() {
                    const carrera = "<?php echo $estudiante['nombre_carrera']; ?>";
                    const idEstudiante = "<?php echo $idUsuario; ?>";
                    const contenedor = document.getElementById('contenedor-habilidades');
                    
                    try {
                        const response = await fetch(`../api_competencias.php?carrera=${encodeURIComponent(carrera)}&id_estudiante=${idEstudiante}`);
                        const competencias = await response.json();

                        if (competencias.length > 0) {
                            contenedor.innerHTML = '';
                            competencias.forEach(comp => {
                                const checked = comp.marcada ? 'checked' : '';
                                contenedor.innerHTML += `
                                    <div>
                                        <input type="checkbox" class="btn-check" id="hab_${comp.id}" value="${comp.id}" data-nombre="${comp.nombre}" ${checked} onchange="actualizarHabilidades(event)">
                                        <label class="btn btn-outline-primary btn-sm rounded-pill" for="hab_${comp.id}">+ ${comp.nombre}</label>
                                    </div>
                                `;
                            });
                            actualizarHabilidades(); // Inicializar campos ocultos
                        } else {
                            contenedor.innerHTML = '<p class="text-muted small">No hay etiquetas predefinidas para tu carrera aún. Tu coordinador debe cargarlas.</p>';
                        }
                    } catch (error) {
                        console.error('Error al cargar habilidades:', error);
                    }
                });

                function actualizarHabilidades(event) {
                    const checks = document.querySelectorAll('#contenedor-habilidades input:checked');
                    if (checks.length > 7) {
                        alert('Puedes seleccionar un máximo de 7 habilidades destacadas.');
                        if (event && event.target) {
                            event.target.checked = false;
                        }
                        return;
                    }
                    const updatedChecks = document.querySelectorAll('#contenedor-habilidades input:checked');
                    const ids = Array.from(updatedChecks).map(c => c.value);
                    const nombres = Array.from(updatedChecks).map(c => c.getAttribute('data-nombre'));
                    
                    document.getElementById('competencias_ids').value = ids.join(',');
                    document.getElementById('habilidades_texto').value = nombres.join(', ');
                }
                </script>
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
                            ? htmlspecialchars($estudiante['cv_estudiante'])
                            : 'No hay CV cargado';
                        ?>
                    </h5>
                    <p class="text-muted">Gestiona tu currículum para las postulaciones.</p>

                    <form method="POST" enctype="multipart/form-data">
                        <?php if (!empty($estudiante['cv_estudiante'])) { ?>
                            <div class="mb-3">
                                <a href="../archivos/cv/<?php echo htmlspecialchars($estudiante['cv_estudiante']); ?>" target="_blank" class="btn btn-success me-2">Ver CV</a>
                                <button type="submit" name="eliminar_cv" class="btn btn-danger">Eliminar CV</button>
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
    
    <div class="col-lg-4">
        <div class="card card-custom mb-4">
            <div class="card-body">
                <h5 class="fw-bold">Resumen Académico</h5>
                <hr>
                <p><strong>Carrera:</strong><br><?php echo htmlspecialchars($estudiante['nombre_carrera']); ?></p>
                <p><strong>Nivel Curricular:</strong><br><?php echo htmlspecialchars($estudiante['nivel_curricular']); ?></p>
                <p><strong>Ramos Aprobados:</strong><br><?php echo htmlspecialchars($estudiante['ramos_aprobados']); ?></p>
            </div>
        </div>
        <div class="card card-custom">
            <div class="card-body">
                <h5 class="fw-bold">Habilidades Destacadas</h5>
                <hr>
                <p><?php echo htmlspecialchars($estudiante['habilidades']); ?></p>
            </div>
        </div>
    </div>
</div>
