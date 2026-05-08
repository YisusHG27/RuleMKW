<?php
/* ========================================================================== 
   LOGIN.PHP - VALIDACIÓN Y AUTENTICACIÓN DE USUARIOS
   ========================================================================== */

/* ========== 1. INICIALIZACIÓN ========== */
// ===== 1.1. INICIAR SESIÓN =====
session_start();

// ===== 1.2. INCLUIR DEPENDENCIAS =====
include 'includes/conexion.php';
require_once 'includes/Logger.php';

/* ========== 2. VARIABLES GLOBALES ========== */
$error = '';

/* ========== 3. PROCESAR FORMULARIO DE LOGIN ========== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["btn-iniciar"])) {
    
    // ===== 3.1. VALIDAR QUE LOS CAMPOS NO ESTÉN VACÍOS =====
    if (empty($_POST["email"]) || empty($_POST["password"])) {
        $error = "Por favor complete todos los campos";
        AppLogger::warning("Intento de login con campos vacíos", [
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    } else {
        // ===== 3.2. LIMPIAR DATOS DE ENTRADA =====
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        
        // ===== 3.3. BUSCAR USUARIO EN LA BD CON SENTENCIA PREPARADA =====
        $stmt = $enlace->prepare("SELECT id, usuario, pass, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // ===== 3.4. VERIFICAR SI EL USUARIO EXISTE =====
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            
            // ===== 3.5. VERIFICAR CONTRASEÑA CON HASH =====
            if (password_verify($password, $fila['pass'])) {
                // ===== 3.6. CREAR SESIÓN EXITOSA =====
                $_SESSION['usuario_id'] = $fila['id'];
                $_SESSION['usuario_nombre'] = $fila['usuario'];
                $_SESSION['usuario_rol'] = $fila['rol'];
                $_SESSION['usuario_email'] = $email;
                
                // ===== 3.7. REGISTRAR LOGIN EXITOSO EN LOG =====
                AppLogger::info("Login exitoso", [
                    'usuario_id' => $fila['id'],
                    'usuario' => $fila['usuario'],
                    'rol' => $fila['rol'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
                
                // ===== 3.8. REDIRIGIR SEGÚN ROL =====
                if ($fila['rol'] === 'admin') {
                    header("Location: /admin/dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                // ===== 3.9. CONTRASEÑA INCORRECTA =====
                $error = "Correo o contraseña incorrectos";
                AppLogger::warning("Intento de login fallido - contraseña incorrecta", [
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
            }
        } else {
            // ===== 3.10. EMAIL NO REGISTRADO =====
            $error = "Correo o contraseña incorrectos";
            AppLogger::warning("Intento de login fallido - email no registrado", [
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]);
        }
        
        $stmt->close();
    }
}

/* ========== 4. VERIFICAR SI USUARIO YA ESTÁ LOGUEADO ========== */
// Si ya está logueado, redirigir a la página correspondiente
if (isset($_SESSION['usuario_id'])) {
    // ===== 4.1. REDIRIGIR SEGÚN ROL =====
    if ($_SESSION['usuario_rol'] === 'admin') {
        header("Location: /admin/dashboard.php");
    } else {
        header("Location: ../index.php");
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
    <link rel="icon" href="../media/iconos/Mario_Kart_World_Logo.png" type="image/png">
    <link rel="stylesheet" href="../css/loginRegistro.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
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
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../media/iconos/logo.png" alt="RuleMKW" height="40" width="160" class="me-2">
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
    <!-- Fuente Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>