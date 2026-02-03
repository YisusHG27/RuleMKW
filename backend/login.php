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
        $stmt = $enlace->prepare("SELECT id, usuario, pass FROM usuarios WHERE email = ?");
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="../frontend/css/loginRegistro.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
</head>
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
</style>
<body>
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
</body>
</html>