<?php
function renderNavbar() {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $active = function (string $page) use ($currentPage): string {
        return $page === $currentPage ? ' active fw-bold' : '';
    };
    ?>
    <!-- Navbar -->
    <nav class="mb-5 navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top align-items-center">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="inicio.php">
                <div class="bg-secondary text-white p-2 d-inline-block rounded"
                    style="width: 120px; text-align: center;"><span class="align-middle material-symbols-outlined">
                        business_center
                    </span> <strong>SGPPE</strong></div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse align-items-center" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link<?php echo $active('proceso.php'); ?>" href="proceso.php">¿Cómo funciona?</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $active('empresas.php'); ?>" href="empresas.php">Empresas</a></li>
                    <li class="nav-item"><a class="nav-link<?php echo $active('soporte.php'); ?>" href="soporte.php">Soporte</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="iniciar_sesion.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}
?>