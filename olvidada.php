<?php
include('conexion.php');

function cleanRut(string $rut): string
{
    return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
}

function ensurePasswordResetTable(mysqli $conexion): void
{
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id_reset INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        token VARCHAR(128) NOT NULL,
        expires_at DATETIME NOT NULL,
        usado TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (id_usuario),
        INDEX (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($conexion, $sql);
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = cleanRut($_POST['rut'] ?? '');

    if ($rut === '') {
        $message = 'Ingresa tu RUT para continuar.';
        $messageType = 'warning';
    } else {
        $sql = "SELECT id_usuario, correo, rut FROM usuario WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ? LIMIT 1";
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $rut);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $usuario = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);
        } else {
            $usuario = null;
        }

        if ($usuario) {
            ensurePasswordResetTable($conexion);
            $token = bin2hex(random_bytes(24));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $idUsuario = (int) $usuario['id_usuario'];

            $insert = mysqli_prepare($conexion, "INSERT INTO password_resets (id_usuario, token, expires_at) VALUES (?, ?, ?)");
            if ($insert) {
                mysqli_stmt_bind_param($insert, 'iss', $idUsuario, $token, $expiresAt);
                mysqli_stmt_execute($insert);
                mysqli_stmt_close($insert);
            }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $resetLink = sprintf('%s://%s/Inicio/restablecer.php?token=%s', $protocol, $_SERVER['HTTP_HOST'], urlencode($token));

            $subject = 'Recuperación de contraseña';
            $body = "Hola,\n\nRecibimos una solicitud para cambiar tu contraseña.\n\nIngresa al siguiente enlace:\n\n" . $resetLink . "\n\nSi no solicitaste este cambio, puedes ignorar este mensaje. El enlace expira en 1 hora.\n\nSaludos.";
            $headers = 'From: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" .
                       'Reply-To: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();

            if (@mail($usuario['correo'], $subject, $body, $headers)) {
                $message = 'Si el RUT existe en el sistema, recibirás un correo con el enlace para restablecer tu contraseña.';
                $messageType = 'success';
            } else {
                $message = 'Se generó el enlace de recuperación. En un servidor configurado, se enviaría por correo.';
                $messageType = 'warning';
            }
        } else {
            $message = 'Si el RUT existe en el sistema, recibirás un correo con el enlace para restablecer tu contraseña.';
            $messageType = 'success';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Gestión de Prácticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">¿Olvidaste tu contraseña?</h1>
                        <p class="text-muted">Ingresa tu RUT y te enviaremos un enlace para restablecer tu contraseña.</p>

                        <?php if ($message !== ''): ?>
                            <div class="alert alert-<?= htmlspecialchars($messageType) ?>" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="olvidada.php" autocomplete="off">
                            <div class="mb-3">
                                <label for="rut" class="form-label">RUT</label>
                                <input type="text" id="rut" name="rut" class="form-control" placeholder="11.111.111-1" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enviar enlace de recuperación</button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="Inicio/iniciar_sesion.php" class="text-decoration-none">Volver a iniciar sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
