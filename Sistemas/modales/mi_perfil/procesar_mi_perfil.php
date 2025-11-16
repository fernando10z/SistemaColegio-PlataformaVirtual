<?php
require_once '../../conexion/bd.php';
session_start();

// Configuración de respuesta JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida. Por favor inicia sesión nuevamente.'
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar que se recibió una acción
if (!isset($_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'No se especificó ninguna acción.'
    ]);
    exit;
}

$accion = $_POST['accion'];

try {
    switch ($accion) {
        case 'cambiar_foto_perfil':
            cambiarFotoPerfil($conexion, $usuario_id);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'mensaje' => 'Acción no reconocida.'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    
    // Log del error
    error_log("Error en procesar_mi_perfil.php: " . $e->getMessage());
}

/**
 * Cambiar foto de perfil del usuario
 */
function cambiarFotoPerfil($conexion, $usuario_id) {
    // Validar que se recibió el archivo
    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'No se recibió ningún archivo.'
        ]);
        return;
    }
    
    $archivo = $_FILES['foto_perfil'];
    
    // Validar errores de subida
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal.',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.'
        ];
        
        echo json_encode([
            'success' => false,
            'mensaje' => $errores[$archivo['error']] ?? 'Error desconocido al subir el archivo.'
        ]);
        return;
    }
    
    // Validar tamaño del archivo (5MB máximo)
    $tamano_maximo = 5 * 1024 * 1024; // 5MB en bytes
    if ($archivo['size'] > $tamano_maximo) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El archivo es muy grande. Tamaño máximo: 5MB.'
        ]);
        return;
    }
    
    // Validar tipo MIME del archivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo_mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($tipo_mime, $tipos_permitidos)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, GIF.'
        ]);
        return;
    }
    
    // Validar extensión del archivo
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Extensión de archivo no permitida.'
        ]);
        return;
    }
    
    // Validar que es una imagen real
    $imagen_info = getimagesize($archivo['tmp_name']);
    if ($imagen_info === false) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El archivo no es una imagen válida.'
        ]);
        return;
    }
    
    try {
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Obtener foto actual del usuario - CORREGIDO: foto_url en lugar de foto_perfil
        $sql_actual = "SELECT foto_url FROM usuarios WHERE id = :usuario_id";
        $stmt_actual = $conexion->prepare($sql_actual);
        $stmt_actual->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt_actual->execute();
        $usuario_actual = $stmt_actual->fetch(PDO::FETCH_ASSOC);
        $foto_anterior = $usuario_actual['foto_url'] ?? null;
        
        // Crear directorio si no existe
        $directorio_destino = '../uploads/perfiles/';
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $nombre_archivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;
        $ruta_bd = 'uploads/perfiles/' . $nombre_archivo;
        
        // Procesar y redimensionar la imagen
        $imagen_procesada = procesarImagen($archivo['tmp_name'], $tipo_mime, 500, 500);
        
        if ($imagen_procesada === false) {
            throw new Exception('Error al procesar la imagen.');
        }
        
        // Guardar imagen procesada
        $guardado = false;
        switch ($tipo_mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $guardado = imagejpeg($imagen_procesada, $ruta_completa, 90);
                break;
            case 'image/png':
                $guardado = imagepng($imagen_procesada, $ruta_completa, 8);
                break;
            case 'image/gif':
                $guardado = imagegif($imagen_procesada, $ruta_completa);
                break;
        }
        
        imagedestroy($imagen_procesada);
        
        if (!$guardado) {
            throw new Exception('Error al guardar la imagen.');
        }
        
        // Actualizar base de datos - CORREGIDO: foto_url en lugar de foto_perfil
        $sql_update = "UPDATE usuarios 
                       SET foto_url = :foto_url,
                           fecha_actualizacion = NOW()
                       WHERE id = :usuario_id";
        
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bindParam(':foto_url', $ruta_bd, PDO::PARAM_STR);
        $stmt_update->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        
        if (!$stmt_update->execute()) {
            throw new Exception('Error al actualizar la base de datos.');
        }
        
        // Eliminar foto anterior si existe y no es la default
        if (!empty($foto_anterior) && 
            $foto_anterior !== 'assets/images/profile/user-default.jpg' &&
            file_exists('../' . $foto_anterior)) {
            @unlink('../' . $foto_anterior);
        }
        
        // Registrar en log de auditoría (opcional)
        registrarAuditoria($conexion, $usuario_id, 'ACTUALIZAR', 'usuarios', $usuario_id, 
                          'Cambió su foto de perfil');
        
        // Confirmar transacción
        $conexion->commit();
        
        // Actualizar sesión si existe
        if (isset($_SESSION['foto_url'])) {
            $_SESSION['foto_url'] = $ruta_bd;
        }
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Foto de perfil actualizada correctamente.',
            'nueva_foto' => '../' . $ruta_bd
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        
        // Eliminar archivo si se guardó
        if (isset($ruta_completa) && file_exists($ruta_completa)) {
            @unlink($ruta_completa);
        }
        
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al cambiar la foto: ' . $e->getMessage()
        ]);
        
        error_log("Error al cambiar foto de perfil: " . $e->getMessage());
    }
}

/**
 * Procesar y redimensionar imagen manteniendo aspecto
 */
function procesarImagen($ruta_origen, $tipo_mime, $ancho_max, $alto_max) {
    // Crear imagen desde el origen según el tipo
    switch ($tipo_mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $imagen_origen = imagecreatefromjpeg($ruta_origen);
            break;
        case 'image/png':
            $imagen_origen = imagecreatefrompng($ruta_origen);
            break;
        case 'image/gif':
            $imagen_origen = imagecreatefromgif($ruta_origen);
            break;
        default:
            return false;
    }
    
    if ($imagen_origen === false) {
        return false;
    }
    
    // Obtener dimensiones originales
    $ancho_original = imagesx($imagen_origen);
    $alto_original = imagesy($imagen_origen);
    
    // Calcular nuevas dimensiones manteniendo aspecto
    $ratio = min($ancho_max / $ancho_original, $alto_max / $alto_original);
    $nuevo_ancho = round($ancho_original * $ratio);
    $nuevo_alto = round($alto_original * $ratio);
    
    // Crear imagen de destino
    $imagen_destino = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Preservar transparencia para PNG y GIF
    if ($tipo_mime === 'image/png' || $tipo_mime === 'image/gif') {
        imagealphablending($imagen_destino, false);
        imagesavealpha($imagen_destino, true);
        $transparente = imagecolorallocatealpha($imagen_destino, 255, 255, 255, 127);
        imagefilledrectangle($imagen_destino, 0, 0, $nuevo_ancho, $nuevo_alto, $transparente);
    }
    
    // Redimensionar imagen
    imagecopyresampled(
        $imagen_destino, 
        $imagen_origen, 
        0, 0, 0, 0, 
        $nuevo_ancho, 
        $nuevo_alto, 
        $ancho_original, 
        $alto_original
    );
    
    // Liberar memoria de imagen origen
    imagedestroy($imagen_origen);
    
    return $imagen_destino;
}

/**
 * Registrar acción en log de auditoría
 */
function registrarAuditoria($conexion, $usuario_id, $accion, $tabla, $registro_id, $descripcion) {
    try {
        // Verificar si la tabla existe
        $sql_check = "SHOW TABLES LIKE 'logs_auditoria'";
        $stmt_check = $conexion->query($sql_check);
        
        if ($stmt_check->rowCount() == 0) {
            // La tabla no existe, no hacer nada
            return;
        }
        
        $sql = "INSERT INTO logs_auditoria 
                (usuario_id, accion, tabla, registro_id, descripcion, ip_address, user_agent, fecha_creacion) 
                VALUES 
                (:usuario_id, :accion, :tabla, :registro_id, :descripcion, :ip, :user_agent, NOW())";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->bindParam(':registro_id', $registro_id, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONOCIDO';
        
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
        
        $stmt->execute();
    } catch (PDOException $e) {
        // No detener el proceso si falla el log
        error_log("Error al registrar auditoría: " . $e->getMessage());
    }
}
?>