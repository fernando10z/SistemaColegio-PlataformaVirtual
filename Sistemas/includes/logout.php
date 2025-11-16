<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar el cierre de sesión en logs (opcional)
if (isset($_SESSION['usuario_id'])) {
    try {
        // Incluir conexión a base de datos
        require_once '../conexion/bd.php';

        // Actualizar último acceso del usuario
        $stmt = $conexion->prepare("
            UPDATE usuarios 
            SET ultimo_acceso = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['usuario_id']]);

        // Registrar en auditoría (si existe la tabla)
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema 
            (usuario_id, modulo, accion, tabla_afectada, fecha_evento, detalles)
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        
        $detalles = json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'tipo' => 'CIERRE_SESION'
        ]);
        
        $stmt->execute([
            $_SESSION['usuario_id'],
            'AUTENTICACION',
            'LOGOUT',
            'usuarios',
            $detalles
        ]);

    } catch (PDOException $e) {
        // Si falla el registro, continuar con el logout de todas formas
        error_log("Error al registrar logout: " . $e->getMessage());
    }
}

// Guardar información temporal si es necesario
$mensaje_logout = "Sesión cerrada exitosamente";

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(), 
        '', 
        time() - 3600, 
        '/',
        '',
        isset($_SERVER['HTTPS']), // secure
        true // httponly
    );
}

// Destruir la sesión
session_destroy();

// Limpiar cualquier cookie adicional del sistema
$cookies_sistema = ['remember_token', 'user_pref', 'last_activity'];
foreach ($cookies_sistema as $cookie_name) {
    if (isset($_COOKIE[$cookie_name])) {
        setcookie($cookie_name, '', time() - 3600, '/');
    }
}

// Prevenir caché de navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Redirigir al index con mensaje
header("Location: ../index.php?logout=success");
exit();
?>