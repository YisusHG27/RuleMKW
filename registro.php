<?php
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $baseDeDatos = "rulemkw";

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="css/lr.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <main>
        <div class="lr-container">
            <h1>Bienvenido a RuleMKW</h1>
            <p>Por favor, registrate para continuar.</p>
            <form action="inicio.html" name="rulemkw" method="post">
                <div class="input-contenedor">
                    <input type="text" id="username" name="username" placeholder="Usuario" required>
                    <i class='bxr  bxs-user'  ></i> 
                </div>
                <div class="input-contenedor">
                    <input type="email" id="email" name="email" placeholder="Correo electrónico" required>
                    <i class='bxr  bxs-envelope'  ></i> 
                </div>
                <div class="input-contenedor">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class='bxr  bxs-lock'  ></i> 
                </div>
                <div class="check">
                    <label>
                        <input type="checkbox" name="aceptar" required> Acepto los términos y condiciones
                    </label>
                </div>
                <button type="submit" class="btn" name="registro">Registrarse</button>
                <div class="InYRe">
                    <p>¿Ya tienes una cuenta? <a href="login.html">Inicia sesión aquí</a></p>
                </div>
        </div>
    </main>
</body>
</html>
<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $usuario = $_POST["username"];
        $pass = $_POST["password"];

        $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' && pass = '$pass'";
        $resultado = mysqli_query($enlace, $sql);
    }
?>