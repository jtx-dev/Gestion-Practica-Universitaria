<?php
include('../conexion.php');

$mensaje = '';
$tipoMensaje = 'success';

// Función para validar el RUT chileno (Modulo 11)
function validarRUT($rut) {
    $rut = preg_replace('/[^0-9kK]/', '', $rut);
    if (strlen($rut) < 2) return false;
    
    $numero = substr($rut, 0, -1);
    $dvOriginal = strtoupper(substr($rut, -1));
    
    $suma = 0;
    $factor = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $numero[$i] * $factor;
        $factor = ($factor == 7) ? 2 : $factor + 1;
    }
    
    $dvCalculado = 11 - ($suma % 11);
    if ($dvCalculado == 11) $dvCalculado = '0';
    elseif ($dvCalculado == 10) $dvCalculado = 'K';
    else $dvCalculado = (string)$dvCalculado;
    
    return $dvOriginal === $dvCalculado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
    $rut_empresa = trim($_POST['rut_empresa'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_carrera = (int) ($_POST['id_carrera'] ?? 0);
    $correo = trim($_POST['correo'] ?? '');

    // 1. Validar dominio de correo (no público)
    $dominios_publicos = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com', 'live.com', 'msn.com'];
    $dominio = strtolower(substr(strrchr($correo, "@"), 1));

    // Validar RUT Empresa (Chile: Personas Jurídicas > 50.000.000)
    $rut_limpio = preg_replace('/[^0-9kK]/', '', $rut_empresa);
    $numero_rut = (int) substr($rut_limpio, 0, -1);

    if (in_array($dominio, $dominios_publicos)) {
        $mensaje = 'Error: No se permiten correos de dominios públicos (Gmail, Outlook, etc.). Por favor use un correo corporativo.';
        $tipoMensaje = 'danger';
    } elseif (!validarRUT($rut_empresa)) {
        $mensaje = 'Error: El RUT ingresado no es válido (Fallo en dígito verificador).';
        $tipoMensaje = 'danger';
    } elseif ($numero_rut <= 50000000) {
        $mensaje = 'Error: El RUT ingresado no corresponde a una empresa (debe ser superior a 50.000.000).';
        $tipoMensaje = 'danger';
    } elseif ($nombre_empresa === '' || $rut_empresa === '' || $titulo === '' || $descripcion === '' || $id_carrera === 0 || $correo === '') {
        $mensaje = 'Error: Todos los campos son obligatorios.';
        $tipoMensaje = 'danger';
    } else {
        mysqli_begin_transaction($conexion);
        try {
            // Verificar si el usuario ya existe
            $stmtCheck = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE correo = ? OR rut = ?");
            mysqli_stmt_bind_param($stmtCheck, "ss", $correo, $rut_empresa);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            $userRow = mysqli_fetch_assoc($resultCheck);
            mysqli_stmt_close($stmtCheck);

            if ($userRow) {
                $idUsuario = $userRow['id_usuario'];
            } else {
                // Buscar rol de Empresa o usar uno por defecto
                $resRol = mysqli_query($conexion, "SELECT id_rol FROM rol WHERE nombre_rol LIKE '%Empresa%' LIMIT 1");
                $rol = mysqli_fetch_assoc($resRol);
                $id_rol_empresa = $rol ? $rol['id_rol'] : 2; 

                $hash = password_hash($rut_empresa, PASSWORD_DEFAULT);
                $stmtUser = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, 'activa')");
                mysqli_stmt_bind_param($stmtUser, "isss", $id_rol_empresa, $rut_empresa, $correo, $hash);
                if (!mysqli_stmt_execute($stmtUser)) throw new Exception("Error al crear usuario.");
                $idUsuario = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmtUser);

                $stmtEmpresa = mysqli_prepare($conexion, "INSERT INTO empresa (id_usuario, razon_social, rut_empresa, nombre_empresa) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmtEmpresa, "isss", $idUsuario, $nombre_empresa, $rut_empresa, $nombre_empresa);
                if (!mysqli_stmt_execute($stmtEmpresa)) throw new Exception("Error al crear registro de empresa.");
                mysqli_stmt_close($stmtEmpresa);
            }

            // Insertar oferta de práctica
            $stmtOferta = mysqli_prepare($conexion, "INSERT INTO oferta_practica (id_carrera, id_empresa, titulo, descripcion, requisitos, cupos, duracion_meses, estado_oferta) VALUES (?, ?, ?, ?, ?, 1, 3, 'pendiente_aprobacion')");
            $requisitos = "No especificados";
            mysqli_stmt_bind_param($stmtOferta, "iisss", $id_carrera, $idUsuario, $titulo, $descripcion, $requisitos);
            if (!mysqli_stmt_execute($stmtOferta)) throw new Exception("Error al enviar la oferta.");
            mysqli_stmt_close($stmtOferta);

            mysqli_commit($conexion);
            $mensaje = '¡Oferta enviada con éxito! Será revisada por la coordinación en un plazo de 48 hrs.';
            $tipoMensaje = 'success';
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = 'Error al procesar la solicitud: ' . $e->getMessage();
            $tipoMensaje = 'danger';
        }
    }
}

// Obtener carreras para el select
$carreras = [];
$resCarreras = mysqli_query($conexion, "SELECT id_carrera, nombre_carrera FROM carrera");
if ($resCarreras) {
    while ($row = mysqli_fetch_assoc($resCarreras)) {
        $carreras[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - Gestión de Prácticas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-bg: #f8f9fa;
        }
        body {
            background-color: var(--secondary-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand img { max-height: 50px; }
        
        /* Estilos específicos */
        .header-title {
            background-color: white;
            padding: 60px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .requirement-card {
            border: none;
            border-radius: 12px;
            background-color: white;
            height: 100%;
        }
        .offer-section {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 40px;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>

    <!-- Navbar (Mantenido exactamente igual) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="inicio.php">
                <div class="bg-secondary text-white p-2 d-inline-block rounded" style="width: 120px; text-align: center;">TU LOGO</div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="proceso.php">¿Cómo funciona?</a></li>
                    <li class="nav-item"><a class="nav-link active fw-bold" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link" href="soporte.php">Soporte</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="iniciar_sesion.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Informativo -->
    <header class="header-title text-center">
        <div class="container">
            <h1 class="fw-bold">Portal de Alianzas Empresariales</h1>
            <p class="text-muted mx-auto" style="max-width: 800px;">
                Colaboramos con organizaciones comprometidas con la formación de futuros profesionales. 
                Toda oferta de práctica está sujeta a validación académica.
            </p>
        </div>
    </header>

    <!-- Rigurosidades y Requisitos -->
    <section class="py-5">
        <div class="container">
            <h3 class="fw-bold mb-4 text-center">Requisitos para Ofertar</h3>
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Validación Legal</h5>
                        <p class="small text-muted mb-0">La empresa debe contar con RUT vigente y representante legal para la firma de convenios institucionales.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Plan de Trabajo</h5>
                        <p class="small text-muted mb-0">Es obligatorio definir un tutor interno y un plan de actividades que aporte al perfil de egreso del alumno.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card requirement-card shadow-sm p-4">
                        <h5 class="fw-bold text-primary">Seguro Escolar</h5>
                        <p class="small text-muted mb-0">La institución cubre el seguro, pero la empresa debe garantizar condiciones de seguridad e higiene adecuadas.</p>
                    </div>
                </div>
            </div>

            <!-- Formulario de Oferta Rápida -->
            <div class="offer-section shadow-sm border">
                <h3 class="fw-bold mb-4">Enviar Oferta de Práctica</h3>
                <p class="text-muted small mb-4">* Los campos son obligatorios. Al enviar, la coordinación revisará la propuesta en un plazo de 48 hrs.</p>
                
                <?php if ($mensaje !== ''): ?>
                    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="empresas.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Empresa</label>
                            <input type="text" name="nombre_empresa" class="form-control shadow-sm" placeholder="Ej: Tech Solutions SpA" value="<?= htmlspecialchars($nombre_empresa ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RUT Empresa</label>
                            <input type="text" name="rut_empresa" class="form-control shadow-sm" placeholder="12.345.678-9" value="<?= htmlspecialchars($rut_empresa ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Título de la Práctica</label>
                            <input type="text" name="titulo" class="form-control shadow-sm" placeholder="Ej: Practicante de Desarrollo Web Laravel" value="<?= htmlspecialchars($titulo ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción de Actividades</label>
                            <textarea name="descripcion" class="form-control shadow-sm" rows="4" placeholder="Detalle las tareas que realizará el alumno..." required><?= htmlspecialchars($descripcion ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera Solicitada</label>
                            <select name="id_carrera" class="form-select shadow-sm" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php if (count($carreras) > 0): ?>
                                    <?php foreach ($carreras as $carrera): ?>
                                        <option value="<?= $carrera['id_carrera'] ?>" <?= (isset($id_carrera) && $id_carrera == $carrera['id_carrera']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($carrera['nombre_carrera']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1" <?= (isset($id_carrera) && $id_carrera == 1) ? 'selected' : '' ?>>Ingeniería Civil Informática (Demo)</option>
                                    <option value="2" <?= (isset($id_carrera) && $id_carrera == 2) ? 'selected' : '' ?>>Ingeniería Comercial (Demo)</option>
                                    <option value="3" <?= (isset($id_carrera) && $id_carrera == 3) ? 'selected' : '' ?>>Psicología (Demo)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo de Contacto (RRHH)</label>
                            <input type="email" name="correo" class="form-control shadow-sm" placeholder="contacto@empresa.cl" value="<?= htmlspecialchars($correo ?? '') ?>" required>
                        </div>
                        <div class="col-12 mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small" for="terms">
                                    Confirmo que la empresa cuenta con un supervisor disponible para guiar al estudiante.
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">Enviar Propuesta para Revisión</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-white border-top text-center mt-auto">
        <div class="container">
            <span class="text-muted small">© 2026 Gestión de Prácticas - &lt nombre institucional &gt</span>
        </div>
    </footer>


    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>