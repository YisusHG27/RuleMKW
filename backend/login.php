<?php
session_start();
include 'includes/conexion.php';

// Inicializar variable de error
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btn-iniciar"])) {
    
    // Validar que los campos no estén vacíos
    if (empty($_POST["email"]) || empty($_POST["password"])) {
        $error = "Por favor complete todos los campos";
    } else {
        // Limpiar datos
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        
        // Preparar la consulta con sentencias preparadas
        // CORRECCIÓN: Incluir el campo 'rol' en la consulta
        $stmt = $enlace->prepare("SELECT id, usuario, pass, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            
            // Verificar contraseña con password_verify
            if (password_verify($password, $fila['pass'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $fila['id'];
                $_SESSION['usuario_nombre'] = $fila['usuario'];
                $_SESSION['usuario_rol'] = $fila['rol'];
                $_SESSION['usuario_email'] = $email;
                
                // Redirigir
                if ($fila['rol'] === 'admin') {
                    header("Location: admin_panel.php");
                } else {
                    header("Location: ../frontend/index.html");
                }
                exit();
            } else {
                $error = "Correo o contraseña incorrectos";
            }
        } else {
            $error = "Correo o contraseña incorrectos";
        }
        
        $stmt->close();
    }
}

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_rol'] === 'admin') {
        header("Location: admin_panel.php");
    } else {
        header("Location: ../frontend/index.html");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión - RuleMKW</title>
    <!-- Navbar incluida en este archivo también -->
    <link rel="stylesheet" href="../frontend/css/loginRegistro.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <!-- Bootstrap para navbar -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        body {
            padding-top: 80px;
        }
    </style>
</head>
<body>
    <!-- NAVBAR para login.php -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../frontend/index.php">
                <img src="../frontend/media/iconos/logo.png" alt="RuleMKW" height="60" class="me-2">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../frontend/index.php">
                    <i class="fas fa-home me-1"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </nav>
    
    <main>
        <div class="lr-container">
            <form action="" method="post">
                <h1>Bienvenido a RuleMKW</h1> 
                <p>Por favor, inicia sesión para continuar.</p>
                
                <?php if (!empty($error)): ?>
                    <div class="mensaje-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="input-contenedor">
                    <input type="email" id="email" name="email" placeholder="Correo electrónico" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class='bxr  bxs-envelope'></i> 
                </div>
                <div class="input-contenedor">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class='bxr  bxs-lock'></i> 
                </div>
                <div class="check">
                    <label>
                        <input type="checkbox" name="aceptar" required> Acepto los términos y condiciones
                    </label>
                </div>
                <button type="submit" name="btn-iniciar" class="btn">Iniciar sesión</button>
                <div class="InYRe">
                    <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>