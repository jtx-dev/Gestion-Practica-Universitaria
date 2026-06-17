<?php

require_once 'config_directivo/auth.php';

$paginas_validas = ['inicio', 'estudiantes', 'indicadores', 'evaluacion', 'reportes'];

$pagina_actual = isset($_GET['pagina']) && in_array($_GET['pagina'], $paginas_validas)
    ? $_GET['pagina']
    : 'inicio';
echo $pagina_actual;
require_once 'config_directivo/header.php';
require_once "paginas/{$pagina_actual}.php";
require_once 'config_directivo/footer.php';
?>