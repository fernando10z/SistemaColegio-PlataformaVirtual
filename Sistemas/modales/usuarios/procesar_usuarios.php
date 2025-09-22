<?php
// modales/usuarios/procesar_usuarios.php

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Incluir archivo de conexión
    require_once '../../conexion/bd.php';

    // Verificar método de petición
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener acción
    $accion = $_POST['accion'] ?? '';

    // Validar acción
    $accionesPermitidas = ['crear', 'obtener', 'actualizar', 'toggle_estado', 'reset_password'];
    if (!in_array($accion, $accionesPermitidas)) {
        throw new Exception('Acción no válida');
    }

    // Procesar según acción
    switch ($accion) {
        case 'crear':
            $resultado = crearUsuario($conexion);
            break;
        case 'obtener':
            $resultado = obtenerUsuario($conexion);
            break;
        case 'actualizar':
            $resultado = actualizarUsuario($conexion);
            break;
        case 'toggle_estado':
            $resultado = toggleEstadoUsuario($conexion);
            break;
        case 'reset_password':
            $resultado = resetearPassword($conexion);
            break;
        default:
            throw new Exception('Acción no implementada');
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Error en procesar_usuarios.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Crear nuevo usuario
 */
function crearUsuario($conexion) {
    // Validar datos requeridos
    $camposRequeridos = ['username', 'email', 'password', 'nombres', 'apellidos', 'documento_tipo', 'documento_numero', 'rol_id'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    // Obtener y sanitizar datos
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $documento_tipo = $_POST['documento_tipo'];
    $documento_numero = trim($_POST['documento_numero']);
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $rol_id = intval($_POST['rol_id']);

    // Validaciones específicas
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        throw new Exception('El usuario solo puede contener letras, números, puntos y guiones');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email no válido');
    }

    if (strlen($password) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }

    if ($documento_tipo === 'DNI' && !preg_match('/^[0-9]{8}$/', $documento_numero)) {
        throw new Exception('DNI debe tener exactamente 8 dígitos');
    }

    // Verificar duplicados
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ? OR documento_numero = ?");
    $stmt->execute([$username, $email, $documento_numero]);
    if ($stmt->fetch()) {
        throw new Exception('El usuario, email o número de documento ya existe');
    }

    // Verificar rol existe
    $stmt = $conexion->prepare("SELECT id FROM roles WHERE id = ? AND activo = 1");
    $stmt->execute([$rol_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Rol no válido');
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    try {
        // Generar código de usuario único
        $codigo_usuario = generarCodigoUsuario($conexion);

        // Hash de contraseña
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        // Procesar foto si existe
        $foto_url = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_url = procesarFotoPerfil($_FILES['foto'], $codigo_usuario);
        }

        // Insertar usuario
        $sql = "INSERT INTO usuarios (
                    codigo_usuario, username, email, password_hash, nombres, apellidos,
                    documento_tipo, documento_numero, telefono, direccion, foto_url, rol_id,
                    activo, debe_cambiar_password, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $codigo_usuario, $username, $email, $password_hash, $nombres, $apellidos,
            $documento_tipo, $documento_numero, $telefono ?: null, $direccion ?: null, 
            $foto_url, $rol_id
        ]);

        // Confirmar transacción
        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Usuario creado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollBack();
        // Eliminar foto si se subió
        if ($foto_url && file_exists($foto_url)) {
            unlink($foto_url);
        }
        throw $e;
    }
}

/**
 * Obtener datos de usuario para edición
 */
function obtenerUsuario($conexion) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de usuario no válido');
    }

    $sql = "SELECT u.*, r.nombre as rol_nombre, r.nivel_acceso
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            WHERE u.id = ?";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Limpiar datos sensibles
    unset($usuario['password_hash']);

    return [
        'success' => true,
        'usuario' => $usuario
    ];
}

/**
 * Actualizar usuario existente
 */
function actualizarUsuario($conexion) {
    // Validar datos requeridos
    $camposRequeridos = ['user_id', 'username', 'email', 'nombres', 'apellidos', 'documento_tipo', 'documento_numero', 'rol_id'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $documento_tipo = $_POST['documento_tipo'];
    $documento_numero = trim($_POST['documento_numero']);
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $rol_id = intval($_POST['rol_id']);
    $activo = intval($_POST['activo'] ?? 1);

    // Validaciones específicas
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        throw new Exception('El usuario solo puede contener letras, números, puntos y guiones');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email no válido');
    }

    if ($documento_tipo === 'DNI' && !preg_match('/^[0-9]{8}$/', $documento_numero)) {
        throw new Exception('DNI debe tener exactamente 8 dígitos');
    }

    // Verificar que el usuario existe
    $stmt = $conexion->prepare("SELECT codigo_usuario, foto_url FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuarioActual) {
        throw new Exception('Usuario no encontrado');
    }

    // Verificar duplicados (excluyendo el usuario actual)
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE (username = ? OR email = ? OR documento_numero = ?) AND id != ?");
    $stmt->execute([$username, $email, $documento_numero, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('El usuario, email o número de documento ya existe');
    }

    // Verificar rol existe
    $stmt = $conexion->prepare("SELECT id FROM roles WHERE id = ? AND activo = 1");
    $stmt->execute([$rol_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Rol no válido');
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    try {
        // Procesar nueva foto si existe
        $foto_url = $usuarioActual['foto_url'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Eliminar foto anterior
            if ($foto_url && file_exists($foto_url)) {
                unlink($foto_url);
            }
            $foto_url = procesarFotoPerfil($_FILES['foto'], $usuarioActual['codigo_usuario']);
        }

        // Preparar SQL base
        $sql = "UPDATE usuarios SET 
                username = ?, email = ?, nombres = ?, apellidos = ?,
                documento_tipo = ?, documento_numero = ?, telefono = ?, direccion = ?,
                foto_url = ?, rol_id = ?, activo = ?, fecha_actualizacion = NOW()";
        
        $params = [
            $username, $email, $nombres, $apellidos,
            $documento_tipo, $documento_numero, $telefono ?: null, $direccion ?: null,
            $foto_url, $rol_id, $activo
        ];

        // Procesar cambio de contraseña si se especifica
        $debe_cambiar_password = 0;
        if (!empty($_POST['cambiar_password']) && !empty($_POST['password'])) {
            $password = $_POST['password'];
            if (strlen($password) < 8) {
                throw new Exception('La contraseña debe tener al menos 8 caracteres');
            }

            $password_hash = password_hash($password, PASSWORD_ARGON2ID);
            $sql .= ", password_hash = ?";
            $params[] = $password_hash;

            // Verificar si debe cambiar password en próximo acceso
            if (!empty($_POST['debe_cambiar_password'])) {
                $debe_cambiar_password = 1;
            }
        }

        $sql .= ", debe_cambiar_password = ? WHERE id = ?";
        $params[] = $debe_cambiar_password;
        $params[] = $user_id;

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        // Confirmar transacción
        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

/**
 * Cambiar estado de usuario (activo/inactivo)
 */
function toggleEstadoUsuario($conexion) {
    $id = intval($_POST['id'] ?? 0);
    $estado = intval($_POST['estado'] ?? 0);

    if (!$id) {
        throw new Exception('ID de usuario no válido');
    }

    // Verificar que el usuario exists
    $stmt = $conexion->prepare("SELECT username FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Actualizar estado
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = ?, fecha_actualizacion = NOW() WHERE id = ?");
    $stmt->execute([$estado, $id]);

    $accion = $estado ? 'activado' : 'desactivado';
    
    return [
        'success' => true,
        'message' => "Usuario {$accion} exitosamente"
    ];
}

/**
 * Resetear contraseña de usuario
 */
function resetearPassword($conexion) {
    $id = intval($_POST['id'] ?? 0);
    
    if (!$id) {
        throw new Exception('ID de usuario no válido');
    }

    // Verificar que el usuario existe
    $stmt = $conexion->prepare("SELECT username, nombres, apellidos FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Generar nueva contraseña temporal
    $nueva_password = generarPasswordTemporal();
    $password_hash = password_hash($nueva_password, PASSWORD_ARGON2ID);

    // Actualizar contraseña y forzar cambio
    $stmt = $conexion->prepare("UPDATE usuarios SET password_hash = ?, debe_cambiar_password = 1, fecha_actualizacion = NOW() WHERE id = ?");
    $stmt->execute([$password_hash, $id]);

    return [
        'success' => true,
        'message' => 'Contraseña reseteada exitosamente',
        'nueva_password' => $nueva_password
    ];
}

/**
 * Generar código único de usuario
 */
function generarCodigoUsuario($conexion) {
    $prefijo = 'USR';
    $intentos = 0;
    $max_intentos = 100;
    
    do {
        $numero = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $codigo = $prefijo . $numero;
        
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE codigo_usuario = ?");
        $stmt->execute([$codigo]);
        $existe = $stmt->fetch();
        
        $intentos++;
    } while ($existe && $intentos < $max_intentos);
    
    if ($intentos >= $max_intentos) {
        throw new Exception('No se pudo generar un código único');
    }
    
    return $codigo;
}

/**
 * Generar contraseña temporal segura
 */
function generarPasswordTemporal() {
    $minusculas = 'abcdefghijklmnopqrstuvwxyz';
    $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numeros = '0123456789';
    $simbolos = '@#$%&*';
    
    // Asegurar al menos uno de cada tipo
    $password = '';
    $password .= $mayusculas[mt_rand(0, strlen($mayusculas) - 1)];
    $password .= $minusculas[mt_rand(0, strlen($minusculas) - 1)];
    $password .= $numeros[mt_rand(0, strlen($numeros) - 1)];
    $password .= $simbolos[mt_rand(0, strlen($simbolos) - 1)];
    
    // Completar hasta 10 caracteres
    $todos_caracteres = $minusculas . $mayusculas . $numeros . $simbolos;
    for ($i = 4; $i < 10; $i++) {
        $password .= $todos_caracteres[mt_rand(0, strlen($todos_caracteres) - 1)];
    }
    
    // Mezclar caracteres
    return str_shuffle($password);
}

/**
 * Procesar foto de perfil
 */
function procesarFotoPerfil($archivo, $codigo_usuario) {
    // Validar archivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        throw new Exception('Tipo de archivo no permitido. Use JPG, PNG o GIF');
    }

    // Validar tamaño (2MB máximo)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        throw new Exception('El archivo es muy grande. Máximo 2MB permitido');
    }

    // Crear directorio si no existe
    $directorio = '../uploads/usuarios/';
    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de subida');
        }
    }

    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = $codigo_usuario . '_' . time() . '.' . strtolower($extension);
    $ruta_completa = $directorio . $nombre_archivo;

    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        throw new Exception('Error al subir el archivo');
    }

    // Redimensionar imagen si es necesario
    try {
        redimensionarImagen($ruta_completa, 300, 300);
    } catch (Exception $e) {
        // Si falla el redimensionamiento, continuar con la imagen original
        error_log('Error al redimensionar imagen: ' . $e->getMessage());
    }

    return $ruta_completa;
}

/**
 * Redimensionar imagen manteniendo proporciones
 */
function redimensionarImagen($ruta, $ancho_max, $alto_max) {
    $info = getimagesize($ruta);
    if (!$info) {
        throw new Exception('No se pudo leer la imagen');
    }

    $ancho_orig = $info[0];
    $alto_orig = $info[1];
    $tipo = $info[2];

    // Calcular nuevas dimensiones
    $ratio = min($ancho_max / $ancho_orig, $alto_max / $alto_orig);
    $ancho_nuevo = round($ancho_orig * $ratio);
    $alto_nuevo = round($alto_orig * $ratio);

    // Crear imagen desde archivo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagen_orig = imagecreatefromjpeg($ruta);
            break;
        case IMAGETYPE_PNG:
            $imagen_orig = imagecreatefrompng($ruta);
            break;
        case IMAGETYPE_GIF:
            $imagen_orig = imagecreatefromgif($ruta);
            break;
        default:
            throw new Exception('Tipo de imagen no soportado para redimensionamiento');
    }

    // Crear nueva imagen
    $imagen_nueva = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);

    // Preservar transparencia para PNG y GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagealphablending($imagen_nueva, false);
        imagesavealpha($imagen_nueva, true);
        $transparente = imagecolorallocatealpha($imagen_nueva, 255, 255, 255, 127);
        imagefilledrectangle($imagen_nueva, 0, 0, $ancho_nuevo, $alto_nuevo, $transparente);
    }

    // Redimensionar
    imagecopyresampled($imagen_nueva, $imagen_orig, 0, 0, 0, 0, $ancho_nuevo, $alto_nuevo, $ancho_orig, $alto_orig);

    // Guardar imagen redimensionada
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($imagen_nueva, $ruta, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($imagen_nueva, $ruta);
            break;
        case IMAGETYPE_GIF:
            imagegif($imagen_nueva, $ruta);
            break;
    }

    // Limpiar memoria
    imagedestroy($imagen_orig);
    imagedestroy($imagen_nueva);
}

?>