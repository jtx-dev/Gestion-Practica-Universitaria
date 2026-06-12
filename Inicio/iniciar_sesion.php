<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Gestión de Prácticas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="iniciar_sesion.js"></script>
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
        
        /* Contenedor centralizado para el Login */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .card-login {
            border: none;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
        }
        .btn-login {
            padding: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link" href="soporte.php">Soporte</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <main class="login-container">
        <div class="container d-flex justify-content-center">
            <div class="card card-login shadow-sm p-4 bg-white">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Acceso al Portal</h2>
                        <p class="text-muted small">Ingresa tus credenciales institucionales para continuar</p>
                    </div>
                    
                    <form>
                        <!-- Campo Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary small fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" placeholder="ejemplo@universidad.cl" required>
                        </div>
                        
                        <!-- Campo Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label text-secondary small fw-bold">Contraseña</label>
                            <input type="password" class="form-control" id="password" placeholder="••••••••" required>
                            <div class="text-end mt-1">
                                <a href="#" class="text-decoration-none small">¿Olvidaste tu contraseña?</a>
                            </div>
                        </div>

                        <!-- Botón Acceso -->
                        <div class="d-grid">
                            <!-- Busca tu botón de login y cámbialo a esto -->
                            <button type="submit" onclick="ejecutarLogin(event)" class="btn btn-primary btn-login shadow-sm">
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