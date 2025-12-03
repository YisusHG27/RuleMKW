<?php
include 'includes/conexion.php';

// Solo ejecutar una vez manualmente
$admin_usuario = "admin";
$admin_email = "admin@rulemkw.com";
$admin_pass = password_hash("Admin123", PASSWORD_DEFAULT);

$stmt = $enlace->prepare("INSERT INTO usuarios (usuario, email, pass, rol) VALUES (?, ?, ?, 'admin')");
$stmt->bind_param("sss", $admin_usuario, $admin_email, $admin_pass);

if($stmt->execute()) {
    echo "Administrador creado exitosamente";
} else {
    echo "Error: " . $enlace->error;
}
?>