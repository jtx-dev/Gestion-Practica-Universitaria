<?php
$conexion = mysqli_connect("localhost", "root", "", "sgppe");

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
