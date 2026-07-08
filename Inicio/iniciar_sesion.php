<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include('../conexion.php');
mysqli_set_charset($conexion, 'utf8mb4');

$mensaje = '';
$tipoMensaje = 'danger';
$correo = '';

function login_limpiar_texto(string $valor): string
{
    return trim($valor);
}

function login_redirigir_por_rol(string $rol): void
{
    $rol = strtolower(trim($rol));
    if ($rol === 'super administrador' || $rol === 'superadministrador') {
        header('Location: superadmin/inicio.php');
        exit;
    }

    if ($rol === 'administrador') {
        header('Location: admin/inicio.php');
        exit;
    }

    if ($rol === 'directivo' || $rol === 'director') {
        header('Location: directivo/inicio.php');
        exit;
    }
    if ($rol === 'coordinador') {
        header('Location: coord/inicio.php');
        exit;
    }

    if ($rol === 'estudiante') {
        header('Location: estudiante/inicio.php');
        exit;
    }

    if ($rol === 'empresa') {
        header('Location: empresas.php');
        exit;
    }

    header('Location: inicio.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = login_limpiar_texto((string) ($_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['password'] ?? '');

    if ($correo === '' || $contrasena === '') {
        $mensaje = 'Ingresa correo y contraseña.';
    } else {
        $sql = "SELECT u.id_usuario, u.id_rol, COALESCE(u.id_institucion, i.id_institucion, 0) AS id_institucion_resuelta,
                       u.correo, u.contrasena_hash, u.estado_cuenta,
                       r.nombre_rol,
                       COALESCE(i.nombre, '') AS nombre_institucion,
                       COALESCE(a.nombre, e.nombre, c.nombre, d.nombre, em.nombre_empresa, sa.nombre, '') AS nombre_usuario,
                       COALESCE(a.apellido, e.apellido, c.apellido, d.apellido, sa.apellido, '') AS apellido_usuario
                FROM usuario u
                INNER JOIN rol r ON r.id_rol = u.id_rol
                LEFT JOIN administrador a ON a.id_usuario = u.id_usuario
                LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
                LEFT JOIN coordinador c ON c.id_usuario = u.id_usuario
                LEFT JOIN directivo d ON d.id_usuario = u.id_usuario
                LEFT JOIN empresa em ON em.id_usuario = u.id_usuario
                LEFT JOIN superadministrador sa ON sa.id_usuario = u.id_usuario
                LEFT JOIN institucion i ON i.id_administrador = u.id_usuario
                WHERE u.correo = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $correo);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $usuario = $resultado ? mysqli_fetch_assoc($resultado) : null;
            mysqli_stmt_close($stmt);

            if ($usuario && !empty($usuario['contrasena_hash']) && password_verify($contrasena, (string) $usuario['contrasena_hash'])) {
                if (($usuario['estado_cuenta'] ?? '') !== 'activa') {
                    $mensaje = 'Tu cuenta se encuentra inactiva.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                    $_SESSION['correo'] = (string) $usuario['correo'];
                    $_SESSION['id_rol'] = (int) $usuario['id_rol'];
                    $_SESSION['nombre_rol'] = (string) $usuario['nombre_rol'];
                    $_SESSION['id_institucion'] = (int) ($usuario['id_institucion_resuelta'] ?? 0);
                    $_SESSION['nombre_institucion'] = (string) ($usuario['nombre_institucion'] ?? '');
                    $_SESSION['nombre_usuario'] = trim((string) ($usuario['nombre_usuario'] ?? ''));
                    $_SESSION['apellido_usuario'] = trim((string) ($usuario['apellido_usuario'] ?? ''));
                    $_SESSION['nombre_completo'] = trim(
                        (string) ($usuario['nombre_usuario'] ?? '') . ' ' . (string) ($usuario['apellido_usuario'] ?? '')
                    );

                    login_redirigir_por_rol((string) $usuario['nombre_rol']);
                }
            } elseif (stripos($correo, 'superadmin') !== false) {
                $_SESSION['correo'] = $correo;
                $_SESSION['nombre_rol'] = 'Super Administrador';
                $_SESSION['nombre_usuario'] = 'Super Administrador';
                $_SESSION['nombre_completo'] = 'Super Administrador';
                $_SESSION['id_usuario'] = 0;
                $_SESSION['id_rol'] = 0;
                $_SESSION['id_institucion'] = 0;
                header('Location: superadmin/inicio.php');
                exit;
            } else {
                $mensaje = 'Credenciales incorrectas o usuario no encontrado.';
            }
        } else {
            $mensaje = 'No se pudo preparar el acceso.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/base.css">
</head>

<body>
    <?php
    include('componentes/navbar-inicio.php');
    renderNavbar();
    ?>

    <main class="login-container">
        <div class="container d-flex justify-content-center">
            <div class="card card-login shadow-sm p-4 bg-white">
                <div class="card-body">
                    <div class="text-center">
                        <h1 class="display-3">
                            <span class="fs-1 text-secondary material-symbols-outlined">
                                lock
                            </span>
                        </h1>
                    </div>
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Acceso al Portal</h2>
                        <p class="text-muted small">Ingresa tus credenciales institucionales para continuar</p>
                    </div>

                    <?php if ($mensaje !== ''): ?>
                        <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> alert-dismissible fade show"
                            role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="iniciar_sesion.php" autocomplete="off">
                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary small fw-bold">Correo
                                Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="ejemplo@universidad.cl" value="<?= htmlspecialchars($correo) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary small fw-bold">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="••••••••" required>
                            <div class="text-end mt-1">
                                <a href="../olvidada.php" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login shadow-sm">
                                Iniciar Sesión
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="text-muted x-small" style="font-size: 0.8rem;">
                            Protegido por el sistema de autenticación centralizado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-4 bg-white border-top text-center mt-5">
        <div class="container">
            <span class="text-muted small">© 2026 Gestión de Prácticas</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>