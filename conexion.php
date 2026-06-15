<?php
$conexion = mysqli_connect("localhost", "root", "", "sgppe");

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
