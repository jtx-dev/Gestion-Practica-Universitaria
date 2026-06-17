<?php
require_once 'includes/auth.php';

$paginas_validas = ['inicio', 'estudiantes', 'indicadores', 'evaluacion', 'reportes'];
$pagina_actual   = isset($_GET['pagina']) && in_array($_GET['pagina'], $paginas_validas)
                   ? $_GET['pagina']
                   : 'inicio';

require_once 'includes/header.php';
require_once "pages/{$pagina_actual}.php";
require_once 'includes/footer.php';
?>
