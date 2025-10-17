<?php
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $baseDeDatos = "rulemkw";

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);
    $enlace -> set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="css/lr.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <main>
        <div class="lr-container">
            <form action="" name="rulemkw" method="post">
                <h1>Bienvenido a RuleMKW</h1> 
                <p>Por favor, inicia sesión para continuar.</p>
                <div class="input-contenedor">
                    <input type="text" id="username" name="username" placeholder="Usuario" required>
                    <i class='bxr  bxs-user'  ></i> 
                </div>
                <div class="input-contenedor">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class='bxr  bxs-lock'  ></i> 
                </div>
                <div class="check">
                    <label>
                        <input type="checkbox" name="aceptar"> Acepto los términos y condiciones
                    </label>
                </div>
                <button type="submit" name="btn-iniciar" class="btn">Iniciar sesión</button>
                <div class="InYRe">
                    <p>¿No tienes una cuenta? <a href="localhost/proyecto/index.php">Regístrate aquí</a></p>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
<?php
    if(!empty($_POST["btn-iniciar"])){
        if(!empty($_POST["username"]) && !empty($_POST["password"])){
            $usuario = $_POST["username"];
            $pass = $_POST["password"];
            $sql = $enlace->query("SELECT * FROM usuarios WHERE usuario = '$usuario' AND pass = '$pass'");
            if($datos=$sql->fetch_object()){
                header("location: inicio.html");
            } else {
                echo "<script>alert('Usuario o contraseña incorrectos.');</script>";
            }
        } else {
            echo "<script>alert('Por favor complete los campos.');</script>";
        }
    }
?>