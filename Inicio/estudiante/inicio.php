<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config_estudiante/auth.php';

$paginas_validas = ['dashboard', 'documentos', 'postulaciones', 'perfil', 'detalle_oferta', 'detalle_bitacora'];
$pagina_actual   = isset($_GET['pagina']) && in_array($_GET['pagina'], $paginas_validas, true)
                   ? $_GET['pagina']
                   : 'dashboard';

require_once 'config_estudiante/header.php';
require_once "paginas/{$pagina_actual}.php";
require_once 'config_estudiante/footer.php';
?>