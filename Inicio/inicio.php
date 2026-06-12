<?php
include('../connection/connection.php');

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/base.css"> <!-- CSS con las fuentes -->

    <!-- Google Font Railway -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- ------------------- -->

    <!-- Google Font Fredoka -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- ------------------- -->

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- ------------- -->

    <title>Gestión de prácticas y empleabilidad</title>
</head>

<body>
    <div class="bg-body-tertiary shadow-lg">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <a class="navbar-brand  m-0 p-0" href="inicio.php"><img src="..\assets\images\logo_SGEPP.png"
                            width="95" height="90" alt="logo_sgppe"></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0 fs-5 px-4">
                            <li class="nav-item">
                                <a class="nav-link active px-4" href="inicio.php">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-4 link-opacity-10" href="snosotros.php">Sobre nosotros</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-4 link-opacity-10" href="soporte.php">Soporte</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <a class="btn btn-outline-primary mx-2 btn-lg " href="inicio_sesion.php">Iniciar sesión</a>
                            <a class="btn btn-outline-secondary mx-2 btn-lg" href="empresa.php">Empresas</a>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="mt-5">
                    <h1 class="display-4">Sistema <span class="text-primary">de</span> gestión de <span class="text-primary">prácticas
                            profesionales</span> y <span class="text-primary-emphasis">empleabilidad</span></h1>
                </div>

            </div>
            <div class="col-6">

            </div>
        </div>
    </div>






    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <!-- ------------ -->
</body>

</html>