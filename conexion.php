<?php
    $conexion = mysqli_connect("localhost:3306","root","","sgppe"); // ("direccion", "usuario", "contraseña", "nombre base de datos")

    if($conexion->connect_error){
        die("Conexión fallida: " . $conn->connect_error);
    }
?>