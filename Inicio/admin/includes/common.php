<?php

function admin_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function admin_query_all(mysqli $conexion, string $sql): array
{
    $items = [];
    try {
        $resultado = mysqli_query($conexion, $sql);
    } catch (Throwable $e) {
        return [];
    }
    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $items[] = $fila;
        }
    }
    return $items;
}

function admin_query_scalar(mysqli $conexion, string $sql): int
{
    try {
        $resultado = mysqli_query($conexion, $sql);
        if ($resultado && ($fila = mysqli_fetch_row($resultado))) {
            return (int) ($fila[0] ?? 0);
        }
    } catch (Throwable $e) {
        return 0;
    }
    return 0;
}

function admin_table_exists(mysqli $conexion, string $tableName): bool
{
    $tableName = trim($tableName);
    $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $tableName);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = $resultado ? mysqli_fetch_row($resultado) : null;
    mysqli_stmt_close($stmt);
    return (int) ($fila[0] ?? 0) > 0;
}

function admin_obtener_id_institucion_actual(mysqli $conexion): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        return 0;
    }

    if (isset($_SESSION['id_institucion']) && (int) $_SESSION['id_institucion'] > 0) {
        return (int) $_SESSION['id_institucion'];
    }

    $idUsuarioSesion = (int) ($_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? 0);
    if ($idUsuarioSesion > 0) {
        $stmt = mysqli_prepare($conexion, "SELECT COALESCE(u.id_institucion, i.id_institucion, 0) AS id_institucion
            FROM usuario u
            INNER JOIN rol r ON r.id_rol = u.id_rol
            LEFT JOIN institucion i ON i.id_administrador = u.id_usuario
            WHERE u.id_usuario = ? AND r.nombre_rol = 'Administrador'
            LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $idUsuarioSesion);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
            mysqli_stmt_close($stmt);
            $idInstitucion = (int) ($fila['id_institucion'] ?? 0);
            if ($idInstitucion > 0) {
                return $idInstitucion;
            }
        }
    }

    $correoSesion = trim((string) ($_SESSION['correo'] ?? $_SESSION['email'] ?? ''));
    if ($correoSesion !== '') {
        $stmt = mysqli_prepare($conexion, "SELECT COALESCE(u.id_institucion, i.id_institucion, 0) AS id_institucion
            FROM usuario u
            INNER JOIN rol r ON r.id_rol = u.id_rol
            LEFT JOIN institucion i ON i.id_administrador = u.id_usuario
            WHERE u.correo = ? AND r.nombre_rol = 'Administrador'
            LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $correoSesion);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;
            mysqli_stmt_close($stmt);
            return (int) ($fila['id_institucion'] ?? 0);
        }
    }

    return 0;
}

function admin_registrar_auditoria(mysqli $conexion, string $accion, string $modulo, string $detalle = ''): void
{
    $tabla = admin_table_exists($conexion, 'auditoria') ? 'auditoria' : (admin_table_exists($conexion, 'logs') ? 'logs' : '');
    if ($tabla === '') {
        return;
    }

    $usuario = 'administrador';
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
        $posiblesClaves = ['usuario', 'correo', 'email', 'nombre_usuario'];
        foreach ($posiblesClaves as $clave) {
            if (!empty($_SESSION[$clave])) {
                $usuario = (string) $_SESSION[$clave];
                break;
            }
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $sql = "INSERT INTO {$tabla} (usuario, accion, modulo, ip, detalle) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 'sssss', $usuario, $accion, $modulo, $ip, $detalle);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function admin_badge_estado(string $estado): string
{
    $estado = strtolower(trim($estado));
    return match ($estado) {
        'activa', 'activo', 'habilitado' => 'success',
        'inactiva', 'inactivo', 'desactivado' => 'secondary',
        'bloqueado' => 'danger',
        'pendiente', 'pendiente_aprobacion' => 'warning',
        default => 'info',
    };
}

function admin_layout_header(string $titulo, string $subtitulo = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= admin_e($titulo) ?> - SGPPE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="sidebar d-flex flex-column">
        <div class="p-4 mb-2">
            <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">Administrador</div>
        </div>
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' || basename($_SERVER['PHP_SELF']) === 'inicio.php' ? 'active' : '' ?>" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>" href="usuarios.php"><i class="bi bi-people me-2"></i> Usuarios</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'roles.php' ? 'active' : '' ?>" href="roles.php"><i class="bi bi-card-checklist me-2"></i> Roles</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'carreras.php' ? 'active' : '' ?>" href="carreras.php"><i class="bi bi-backpack3 me-2"></i> Carreras</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'asignaciones.php' ? 'active' : '' ?>" href="asignaciones.php"><i class="bi bi-diagram-3 me-2"></i> Asignaciones</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'logs.php' ? 'active' : '' ?>" href="logs.php"><i class="bi bi-journal-text me-2"></i> Auditoría</a>
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'configuracion.php' ? 'active' : '' ?>" href="configuracion.php"><i class="bi bi-gear me-2"></i> Configuracion</a>
            <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php"><i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesion</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-1"><?= admin_e($titulo) ?></h2>
                <?php if ($subtitulo !== ''): ?>
                    <p class="text-muted mb-0"><?= admin_e($subtitulo) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php
}

function admin_layout_footer(): void
{
    ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    <?php
}
