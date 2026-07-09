<?php
include('../conexion.php');
include('componentes/navbar-inicio.php');

$sql = "SELECT COUNT(*) AS total FROM estudiante";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$totalUsuarios = (int) $row['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Prácticas Universitarias</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/base.css">
    </style>
</head>

<body>

    <?php
    renderNavbar();
    ?>

    <header class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Portal de Prácticas Profesionales</h1>
            <p class="lead text-muted">La plataforma centralizada para conectar el mundo académico con el profesional.
            </p>
        </div>
    </header>

    </div>
    </div>
    </section>

    <hr class="my-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-md-3">
                <div class="card shadow-sm p-4 mb-4">
                    <div class="card-header">
                        <h5 class="fw-bold">Estudiantes</h5>
                    </div>    
                    <div class="card-body">
                        <div id="contador-usuarios" class="text-center fs-1 fw-bold text-primary" data-total="<?php echo $totalUsuarios; ?>">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-4 mb-4">
                    <div class="card-header">
                        <h5 class="fw-bold">Ofertas de Práctica</h5>
                    </div>
                    <div class="card-body">
                        <div id="contador-ofertas" class="text-center fs-1 fw-bold text-primary" data-total="<?php echo $totalOfertas; ?>">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-4 mb-4">
                    <div class="card-header">
                        <h5 class="fw-bold">Empresas Registradas</h5>
                    </div>
                    <div class="card-body">
                        <div id="contador-empresas" class="text-center fs-1 fw-bold text-primary" data-total="<?php echo $totalEmpresas; ?>">0
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cartas informativas con los RNF y requisitos del mercado generalizados (estéticos) -->
    <div class="py-5">
        <div class="container">
            <div class="row ">
                <div class="col-4">
                    <div class="card card-hover">
                        <div class="card-header text-bg-primary">
                            <div class="row">
                                <div class="col-10">
                                    <div class="text-start">
                                        <span class="align-middle fs-3">
                                            Trazabilidad
                                        </span>
                                    </div>

                                </div>

                                <div class="border-start text-end col-1">
                                    <span class="fs-1 align-middle material-symbols-outlined">
                                        steps
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            Visualización del progreso del estudiante a tiempo real.<br>
                            Trazabilidad clara de procesos.

                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card card-hover">
                        <div class="card-header text-bg-primary">
                            <div class="row">
                                <div class="col-10">
                                    <div class="text-start">
                                        <span class="align-middle fs-3">
                                            Seguridad
                                        </span>
                                    </div>

                                </div>

                                <div class="border-start text-end col-1">
                                    <span class="fs-1 align-middle material-symbols-outlined">
                                        security
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            Contraseñas encriptadas limitando vulnerabilidades en nuestro sistema. <br>
                            Tus datos están seguros con nosotros.

                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card card-hover">
                        <div class="card-header text-bg-primary">
                            <div class="row">
                                <div class="col-10">
                                    <div class="text-start">
                                        <span class="align-middle fs-3">
                                            Soporte
                                        </span>
                                    </div>

                                </div>

                                <div class="border-start text-end col-1">
                                    <span class="fs-1 align-middle material-symbols-outlined">
                                        contact_support
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            Sistema de soporte integrado para consultas o ayudas específicas.<br>
                            Tus consultas nos importan.

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="inicio.js"></script>
</body>

</html>