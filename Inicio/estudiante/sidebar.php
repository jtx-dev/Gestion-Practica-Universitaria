<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar d-flex flex-column">

    <div class="p-4 mb-2">
        <div class="bg-primary text-white p-2 rounded text-center fw-bold shadow-sm">
            PRACTICAS ALUMNOS
        </div>
    </div>

    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link <?php echo ($pagina == 'inicio.php') ? 'active' : ''; ?>" href="inicio.php"> <i class="bi bi-grid-1x2-fill me-2"></i>Inicio / Ofertas</a>
        <a class="nav-link <?php echo ($pagina == 'documentos.php') ? 'active' : ''; ?>" href="documentos.php"> <i class="bi bi-file-earmark-arrow-up me-2"></i> Mis Documentos</a>
        <a class="nav-link <?php echo ($pagina == 'postulaciones.php') ? 'active' : ''; ?>" href="postulaciones.php"> <i class="bi bi-briefcase me-2"></i> Postulaciones</a>
        <a class="nav-link <?php echo ($pagina == 'perfil.php') ? 'active' : ''; ?>" href="perfil.php"> <i class="bi bi-person me-2"></i> Mi Perfil</a>
        <a class="nav-link text-danger mt-auto mb-4" href="../inicio.php"> <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión</a>
    </nav>

</div>