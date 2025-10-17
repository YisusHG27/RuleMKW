<?php
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $baseDeDatos = "rulemkw";

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);
    $enlace -> set_charset("utf8mb4");
?>