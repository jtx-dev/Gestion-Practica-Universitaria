<?php
include('../conexion.php');

function cleanRut(string $rut): string
{
    return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
}

$token = $_GET['token'] ?? '';
$message = '';
$messageType = 'danger';
$passwordChanged = false;

if ($token === '') {
    $message = 'Token inválido.';
} else {
    $sql = "SELECT pr.id_reset, pr.id_usuario, pr.expires_at, pr.usado, u.correo FROM password_resets pr JOIN usuario u ON u.id_usuario = pr.id_usuario WHERE pr.token = ? LIMIT 1";
    $stmt = mysqli_prepare($conexion, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $reset = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
    }

    if (!$reset) {
        $message = 'Token no válido o ya usado.';
    } elseif ($reset['usado']) {
        $message = 'El enlace ya fue utilizado.';
    } elseif (new DateTime() > new DateTime($reset['expires_at'])) {
        $message = 'El enlace ha expirado.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password === '' || $password2 === '') {
            $message = 'Ingresa la nueva contraseña en ambos campos.';
        } elseif ($password !== $password2) {
            $message = 'Las contraseñas no coinciden.';
        } elseif (strlen($password) < 8) {
            $message = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_prepare($conexion, "UPDATE usuario SET contrasena_hash = ? WHERE id_usuario = ?");
            if ($update) {
                mysqli_stmt_bind_param($update, 'si', $hash, $reset['id_usuario']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            $mark = mysqli_prepare($conexion, "UPDATE password_resets SET usado = 1 WHERE id_reset = ?");
            if ($mark) {
                mysqli_stmt_bind_param($mark, 'i', $reset['id_reset']);
                mysqli_stmt_execute($mark);
                mysqli_stmt_close($mark);
            }

            $message = 'Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.';
            $messageType = 'success';
            $passwordChanged = true;
        }
    } else {
        $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Restablecer contraseña</h1>

                        <?php if ($message !== ''): ?>
                            <div class="alert alert-<?= htmlspecialchars($messageType) ?>" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$passwordChanged && $token !== '' && isset($reset) && !$reset['usado'] && new DateTime() <= new DateTime($reset['expires_at'])): ?>
                            <form method="post" action="restablecer.php?token=<?= urlencode(htmlspecialchars($token)) ?>">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nueva contraseña</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password2" class="form-label">Repetir contraseña</label>
                                    <input type="password" id="password2" name="password2" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Guardar contraseña</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center mt-4">
                                <a href="iniciar_sesion.php" class="btn btn-secondary">Volver a iniciar sesión</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
