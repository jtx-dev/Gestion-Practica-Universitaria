<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../conexion.php');

/** @var mysqli $conexion */
// esta linea evita ver errores del $conexion del intelephense.

$mensaje = '';
$tipoMensaje = 'success';

/**
 * Función para validar el RUT chileno (Modulo 11)
 * 
 * @param string $rut El RUT a validar.
 * @return bool True si es válido, False si no.
 */

function validarRUT(string $rut): bool {
    $rut = strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
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
    $ip_usuario = $_SERVER['REMOTE_ADDR'];

    // 0. Control de Tasa (Rate Limiting) - RNF-SEC-01
    $queryLimit = "SELECT COUNT(*) as total FROM intentos_envio WHERE ip_usuario = ? AND fecha_intento > (NOW() - INTERVAL 10 MINUTE)";
    $EstadoLimite = mysqli_prepare($conexion, $queryLimit);
    if ($EstadoLimite) {
        mysqli_stmt_bind_param($EstadoLimite, "s", $ip_usuario);
        mysqli_stmt_execute($EstadoLimite);
        $resLimit = mysqli_stmt_get_result($EstadoLimite);
        $dataLimit = mysqli_fetch_assoc($resLimit);
        mysqli_stmt_close($EstadoLimite);
    } else {
        $dataLimit = ['total' => 0]; // Fallback si falla la query
    }

    if ($dataLimit['total'] >= 3) {
        $mensaje = 'Por seguridad, ha superado el límite de intentos. Por favor, espere 10 minutos antes de volver a intentarlo.';
        $tipoMensaje = 'warning';
    } else {
        $nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
        $rut_empresa = trim($_POST['rut_empresa'] ?? '');
        $nombre_encargado = trim($_POST['nombre_encargado'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $nombre_carrera_input = trim($_POST['nombre_carrera'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $cupos = (int) ($_POST['cupos'] ?? 1);
        $requisitos = trim($_POST['requisitos'] ?? '');
        $duracion = (int) ($_POST['duracion'] ?? 3);

        // Resolver ID de carrera por nombre
        $id_carrera = 0;
        if ($nombre_carrera_input !== '') {
            $BusquedaCarrera = mysqli_prepare($conexion, "SELECT id_carrera FROM carrera WHERE nombre_carrera = ?");
            if ($BusquedaCarrera) {
                mysqli_stmt_bind_param($BusquedaCarrera, "s", $nombre_carrera_input);
                mysqli_stmt_execute($BusquedaCarrera);
                $resC = mysqli_stmt_get_result($BusquedaCarrera);
                if ($rowC = mysqli_fetch_assoc($resC)) {
                    $id_carrera = (int)$rowC['id_carrera'];
                }
                mysqli_stmt_close($BusquedaCarrera);
            }

            // Manejo de casos demo si no hay base de datos poblada
            if ($id_carrera === 0) {
                if (strpos($nombre_carrera_input, 'Informática') !== false) $id_carrera = 1;
                elseif (strpos($nombre_carrera_input, 'Comercial') !== false) $id_carrera = 2;
                elseif (strpos($nombre_carrera_input, 'Psicología') !== false) $id_carrera = 3;
            }
        }

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
        $mensaje = 'Error: El RUT ingresado no es válido. Revisa el dígito verificador.';
        $tipoMensaje = 'danger';
    } elseif ($numero_rut <= 50000000) {
        $mensaje = 'Error: El RUT ingresado no corresponde a una empresa (debe ser superior a 50.000.000).';
        $tipoMensaje = 'danger';
    } elseif ($nombre_empresa === '' || $rut_empresa === '' || $titulo === '' || $descripcion === '' || $id_carrera === 0 || $correo === '' || $requisitos === '' || $nombre_encargado === '' || $telefono === '' || $direccion === '') {
        $faltantes = [];
        if ($nombre_empresa === '') $faltantes[] = 'Nombre Empresa';
        if ($rut_empresa === '') $faltantes[] = 'RUT Empresa';
        if ($nombre_encargado === '') $faltantes[] = 'Nombre Encargado';
        if ($telefono === '') $faltantes[] = 'Teléfono';
        if ($direccion === '') $faltantes[] = 'Dirección';
        if ($titulo === '') $faltantes[] = 'Título';
        if ($descripcion === '') $faltantes[] = 'Descripción';
        if ($requisitos === '') $faltantes[] = 'Requisitos';
        if ($correo === '') $faltantes[] = 'Correo';
        if ($id_carrera === 0) $faltantes[] = 'Carrera (Debe seleccionar una válida de la lista)';
        
        $mensaje = 'Error: Faltan campos o son inválidos: ' . implode(', ', $faltantes);
        $tipoMensaje = 'danger';
    } else {
        // Registrar el intento (Solo si pasó todas las validaciones de campos)
        $EstadoIntento = mysqli_prepare($conexion, "INSERT INTO intentos_envio (ip_usuario) VALUES (?)");
        if ($EstadoIntento) {
            mysqli_stmt_bind_param($EstadoIntento, "s", $ip_usuario);
            mysqli_stmt_execute($EstadoIntento);
            mysqli_stmt_close($EstadoIntento);
        }

        mysqli_begin_transaction($conexion);
        try {
            // Verificar si el usuario ya existe
            $ValidarUsuario = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE correo = ? OR rut = ?");
            if (!$ValidarUsuario) throw new Exception("Error al preparar validación de usuario.");
            mysqli_stmt_bind_param($ValidarUsuario, "ss", $correo, $rut_empresa);
            mysqli_stmt_execute($ValidarUsuario);
            $resultCheck = mysqli_stmt_get_result($ValidarUsuario);
            $userRow = mysqli_fetch_assoc($resultCheck);
            mysqli_stmt_close($ValidarUsuario);

            if ($userRow) {
                $idUsuario = $userRow['id_usuario'];
            } else {
                // Buscar rol de Empresa o usar uno por defecto
                $resRol = mysqli_query($conexion, "SELECT id_rol FROM rol WHERE nombre_rol LIKE '%Empresa%' LIMIT 1");
                $rol = mysqli_fetch_assoc($resRol);
                $id_rol_empresa = $rol ? $rol['id_rol'] : 2; 

                $hash = password_hash($rut_empresa, PASSWORD_DEFAULT);
                $RegistroUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, 'activa')");
                if (!$RegistroUsuario) throw new Exception("Error al preparar creación de usuario.");
                mysqli_stmt_bind_param($RegistroUsuario, "isss", $id_rol_empresa, $rut_empresa, $correo, $hash);
                if (!mysqli_stmt_execute($RegistroUsuario)) throw new Exception("Error al crear usuario.");
                $idUsuario = mysqli_insert_id($conexion);
                mysqli_stmt_close($RegistroUsuario);

                $RegistroEmpresa = mysqli_prepare($conexion, "INSERT INTO empresa (id_usuario, razon_social, rut_empresa, direccion, telefono, nombre_encargado, nombre_empresa) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$RegistroEmpresa) throw new Exception("Error al preparar creación de empresa.");
                mysqli_stmt_bind_param($RegistroEmpresa, "issssss", $idUsuario, $nombre_empresa, $rut_empresa, $direccion, $telefono, $nombre_encargado, $nombre_empresa);
                if (!mysqli_stmt_execute($RegistroEmpresa)) throw new Exception("Error al crear registro de empresa.");
                mysqli_stmt_close($RegistroEmpresa);
            }

            // Insertar oferta de práctica
            $RegistroOferta = mysqli_prepare($conexion, "INSERT INTO oferta_practica (id_carrera, id_empresa, titulo, descripcion, requisitos, cupos, duracion_meses, estado_oferta) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente_aprobacion')");
            if (!$RegistroOferta) throw new Exception("Error al preparar envío de oferta (posible falta de columnas en la BD).");
            mysqli_stmt_bind_param($RegistroOferta, "iisssii", $id_carrera, $idUsuario, $titulo, $descripcion, $requisitos, $cupos, $duracion);
            if (!mysqli_stmt_execute($RegistroOferta)) throw new Exception("Error al enviar la oferta.");
            mysqli_stmt_close($RegistroOferta);

            mysqli_commit($conexion);
            $mensaje = '¡Oferta enviada con éxito! Será revisada por la coordinación en un plazo de 48 hrs.';
            $tipoMensaje = 'success';

            // Resetear variables para limpiar el formulario tras el éxito
            $nombre_empresa = $rut_empresa = $nombre_encargado = $telefono = $direccion = $titulo = $descripcion = $correo = $requisitos = $nombre_carrera_input = '';
            $id_carrera = 0;
            $cupos = 1;
            $duracion = 3;
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = 'Error al procesar la solicitud: ' . $e->getMessage();
            $tipoMensaje = 'danger';
        }
    }
} // Fin del else de Rate Limiting
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
                            <input type="text" name="rut_empresa" class="form-control shadow-sm" placeholder="12.345.678-5" value="<?= htmlspecialchars($rut_empresa ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Encargado</label>
                            <input type="text" name="nombre_encargado" class="form-control shadow-sm" placeholder="Ej: Juan Pérez" value="<?= htmlspecialchars($nombre_encargado ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono de Contacto</label>
                            <input type="tel" name="telefono" class="form-control shadow-sm" placeholder="Ej: +569 1234 5678" value="<?= htmlspecialchars($telefono ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Dirección de la Empresa</label>
                            <input type="text" name="direccion" class="form-control shadow-sm" placeholder="Ej: Av. Principal 123, Oficina 402" value="<?= htmlspecialchars($direccion ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Título de la Práctica</label>
                            <input type="text" name="titulo" class="form-control shadow-sm" placeholder="Ej: Practicante de Desarrollo Web Laravel" value="<?= htmlspecialchars($titulo ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción de Actividades</label>
                            <textarea name="descripcion" class="form-control shadow-sm" rows="4" placeholder="Detalle las tareas que realizará el alumno..." required><?= htmlspecialchars($descripcion ?? '') ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Requisitos de la Práctica</label>
                            <textarea name="requisitos" class="form-control shadow-sm" rows="3" placeholder="Ej: Conocimientos en PHP, SQL, disponibilidad inmediata..." required><?= htmlspecialchars($requisitos ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera Solicitada</label>
                            <input type="text" name="nombre_carrera" list="carrerasList" class="form-control shadow-sm" placeholder="Escriba para buscar carrera..." value="<?= htmlspecialchars($nombre_carrera_input ?? '') ?>" required>
                            <datalist id="carrerasList">
                                <?php if (count($carreras) > 0): ?>
                                    <?php foreach ($carreras as $carrera): ?>
                                        <option value="<?= htmlspecialchars($carrera['nombre_carrera']) ?>">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="Ingeniería Civil Informática">
                                    <option value="Ingeniería Comercial">
                                    <option value="Psicología">
                                <?php endif; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo de Contacto (RRHH)</label>
                            <input type="email" name="correo" class="form-control shadow-sm" placeholder="contacto@empresa.cl" value="<?= htmlspecialchars($correo ?? '') ?>" autocomplete="off" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupos Disponibles</label>
                            <input type="number" name="cupos" class="form-control shadow-sm" min="1" max="50" value="<?= htmlspecialchars($cupos ?? 1) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duración de la Práctica (Meses)</label>
                            <input type="number" name="duracion" class="form-control shadow-sm" min="1" max="12" value="<?= htmlspecialchars($duracion ?? 3) ?>" required>
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
            <span class="text-muted small">© 2026 Gestión de Prácticas - &lt;nombre institucional&gt;</span>
        </div>
    </footer>


    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
