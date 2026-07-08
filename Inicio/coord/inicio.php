<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config_coord/auth.php';

$paginas_validas = ['inicio', 'alumnos', 'ofertas', 'ofertas_aprobadas', 'ofertas_rechazadas', 'validacion', 'postulaciones'];
$pagina_actual   = isset($_GET['pagina']) && in_array($_GET['pagina'], $paginas_validas, true)
                   ? $_GET['pagina']
                   : 'inicio';

require_once 'config_coord/header.php';
require_once "paginas/{$pagina_actual}.php";
require_once 'config_coord/footer.php';
?>