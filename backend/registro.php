<?php
session_start();
include 'includes/conexion.php';

$mensaje = '';
$tipo_mensaje = '';
$nombre = $email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registro'])) {
    
    // Obtener datos del formulario
    $nombre = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($nombre) || empty($email) || empty($pass) || empty($confirm_pass)) {
        $errores[] = "Todos los campos son obligatorios";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    if (strlen($pass) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    if ($pass !== $confirm_pass) {
        $errores[] = "Las contraseñas no coinciden";
    }
    
    // Si no hay errores, proceder
    if (empty($errores)) {
        // Verificar si el email ya existe
        $stmt = $enlace->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $mensaje = "Este correo ya está registrado";
            $tipo_mensaje = "error";
        } else {
            // Verificar si el usuario ya existe
            $stmt2 = $enlace->prepare("SELECT id FROM usuarios WHERE usuario = ?");
            $stmt2->bind_param("s", $nombre);
            $stmt2->execute();
            $stmt2->store_result();
            
            if ($stmt2->num_rows > 0) {
                $mensaje = "Este nombre de usuario ya está en uso";
                $tipo_mensaje = "error";
            } else {
                // Encriptar contraseña
                $passHash = password_hash($pass, PASSWORD_DEFAULT);
                
                // INSERTAR USUARIO (NO especificar fecha_registro - MySQL la pone automáticamente)
                $stmt3 = $enlace->prepare("INSERT INTO usuarios (usuario, email, pass) VALUES (?, ?, ?)");
                
                if ($stmt3 === false) {
                    $mensaje = "Error al preparar la consulta: " . $enlace->error;
                    $tipo_mensaje = "error";
                } else {
                    $stmt3->bind_param("sss", $nombre, $email, $passHash);
                    
                    if ($stmt3->execute()) {
                        // Obtener ID del nuevo usuario
                        $usuario_id = $stmt3->insert_id;
                        
                        // Hacer admin al PRIMER usuario registrado
                        if ($usuario_id == 1) {
                            $stmt4 = $enlace->prepare("UPDATE usuarios SET rol = 'admin' WHERE id = ?");
                            $stmt4->bind_param("i", $usuario_id);
                            $stmt4->execute();
                            $stmt4->close();
                            $_SESSION['usuario_rol'] = 'admin';
                        } else {
                            $_SESSION['usuario_rol'] = 'usuario';
                        }
                        
                        // Iniciar sesión automáticamente
                        $_SESSION['usuario_id'] = $usuario_id;
                        $_SESSION['usuario_nombre'] = $nombre;
                        $_SESSION['usuario_email'] = $email;
                        
                        // Redirigir a la página principal
                        header("Location: ../index.php");
                        exit();
                    } else {
                        $mensaje = "Error al registrar: " . $stmt3->error;
                        $tipo_mensaje = "error";
                    }
                    
                    $stmt3->close();
                }
            }
            $stmt2->close();
        }
        $stmt->close();
    } else {
        // Mostrar errores
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="../css/loginRegistro.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .mensaje {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../media/iconos/logo.png" alt="RuleMKW" height="60" class="me-2">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </nav>
    <main>
        <div class="lr-container">
            <h1>Bienvenido a RuleMKW</h1>
            <p>Por favor, regístrate para continuar.</p>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="input-contenedor">
                    <input type="text" id="username" name="username" placeholder="Usuario" required
                           value="<?php echo htmlspecialchars($nombre); ?>">
                    <i class='bxr  bxs-user'></i> 
                </div>
                <div class="input-contenedor">
                    <input type="email" id="email" name="email" placeholder="Correo electrónico" required
                           value="<?php echo htmlspecialchars($email); ?>">
                    <i class='bxr  bxs-envelope'></i> 
                </div>
                <div class="input-contenedor">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class='bxr  bxs-lock'></i> 
                </div>
                <div class="input-contenedor">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar Contraseña" required>
                    <i class='bxr  bxs-lock-alt'></i> 
                </div>
                <div class="check">
                    <label>
                        <input type="checkbox" name="aceptar" required> Acepto los términos y condiciones
                    </label>
                </div>
                <button type="submit" class="btn" name="registro">Registrarse</button>
                <div class="InYRe">
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                </div>
            </form>
        </div>
    </main>
</body>
</html>