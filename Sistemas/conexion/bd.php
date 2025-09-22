<?php
$host = "localhost";       // Servidor de la base de datos
$dbname = "eva_colegio_aac";       // Nombre de la base de datos
$username = "root";        // Usuario de MySQL
$password = "";            // Contraseña de MySQL

try {
    // Crear conexión con PDO
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar para mostrar errores
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
}
?>