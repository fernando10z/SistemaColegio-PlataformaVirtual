<?php
require_once '../../conexion/bd.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $accion = $_POST['accion'];

    switch ($accion) {
        case 'crear':
            $response = crearLeccion();
            break;
            
        case 'actualizar':
            $response = actualizarLeccion();
            break;
            
        case 'obtener':
            $response = obtenerLeccion();
            break;
            
        case 'eliminar':
            $response = eliminarLeccion();
            break;
            
        case 'obtener_progreso':
            $response = obtenerProgresoEstudiantes();
            break;
            
        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);

// ==================== FUNCIÓN CREAR LECCIÓN ====================
function crearLeccion() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['unidad_id', 'titulo', 'descripcion', 'orden', 'tipo', 'tiempo_estimado', 'estado'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    $conexion->beginTransaction();

    try {
        // Validaciones exhaustivas del lado del servidor
        validarDatosLeccion($_POST);
        
        // Verificar que la unidad exista
        $stmt = $conexion->prepare("SELECT id FROM unidades WHERE id = ?");
        $stmt->execute([$_POST['unidad_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('La unidad seleccionada no existe');
        }

        // Verificar orden único en la unidad
        $stmt = $conexion->prepare("SELECT id FROM lecciones WHERE unidad_id = ? AND orden = ?");
        $stmt->execute([$_POST['unidad_id'], $_POST['orden']]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe una lección con ese orden en esta unidad');
        }

        // Preparar configuraciones
        $configuraciones = [
            'estado' => $_POST['estado'],
            'tiempo_estimado' => (int)$_POST['tiempo_estimado'],
            'obligatorio' => isset($_POST['obligatorio']) && $_POST['obligatorio'] == '1'
        ];

        // Procesar recursos
        $recursos = isset($_POST['recursos']) ? json_decode($_POST['recursos'], true) : [];
        
        // Validar recursos procesados
        if (!empty($recursos)) {
            validarRecursos($recursos);
        }

        // Sanitizar contenido HTML
        $contenido = isset($_POST['contenido']) ? sanitizarContenidoHTML($_POST['contenido']) : '';

        // Insertar lección
        $stmt = $conexion->prepare("
            INSERT INTO lecciones (
                unidad_id, titulo, descripcion, contenido, orden, tipo,
                configuraciones, recursos, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $_POST['unidad_id'],
            trim($_POST['titulo']),
            trim($_POST['descripcion']),
            $contenido,
            $_POST['orden'],
            $_POST['tipo'],
            json_encode($configuraciones),
            json_encode($recursos)
        ]);

        $leccion_id = $conexion->lastInsertId();

        // Registrar en auditoría
        registrarAuditoria($conexion, 'LECCIONES', 'CREACION', 'lecciones', $leccion_id, [
            'titulo' => $_POST['titulo'],
            'unidad_id' => $_POST['unidad_id'],
            'tipo' => $_POST['tipo']
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Lección creada exitosamente',
            'leccion_id' => $leccion_id
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN ACTUALIZAR LECCIÓN ====================
function actualizarLeccion() {
    global $conexion;
    
    if (!isset($_POST['leccion_id'])) {
        throw new Exception('ID de la lección no especificado');
    }

    $leccion_id = (int)$_POST['leccion_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que la lección existe
        $stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id = ?");
        $stmt->execute([$leccion_id]);
        $leccion_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$leccion_actual) {
            throw new Exception('Lección no encontrada');
        }

        // Validaciones exhaustivas del lado del servidor
        validarDatosLeccion($_POST, $leccion_id);

        // Verificar que la unidad exista
        $stmt = $conexion->prepare("SELECT id FROM unidades WHERE id = ?");
        $stmt->execute([$_POST['unidad_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('La unidad seleccionada no existe');
        }

        // Verificar orden único en la unidad (excluyendo la lección actual)
        $stmt = $conexion->prepare("SELECT id FROM lecciones WHERE unidad_id = ? AND orden = ? AND id != ?");
        $stmt->execute([$_POST['unidad_id'], $_POST['orden'], $leccion_id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otra lección con ese orden en esta unidad');
        }

        // Preparar configuraciones
        $configuraciones = [
            'estado' => $_POST['estado'],
            'tiempo_estimado' => (int)$_POST['tiempo_estimado'],
            'obligatorio' => isset($_POST['obligatorio']) && $_POST['obligatorio'] == '1'
        ];

        // Procesar recursos
        $recursos = isset($_POST['recursos']) ? json_decode($_POST['recursos'], true) : [];
        
        // Validar recursos procesados
        if (!empty($recursos)) {
            validarRecursos($recursos);
        }

        // Sanitizar contenido HTML
        $contenido = isset($_POST['contenido']) ? sanitizarContenidoHTML($_POST['contenido']) : '';

        // Actualizar lección
        $stmt = $conexion->prepare("
            UPDATE lecciones SET 
                unidad_id = ?, titulo = ?, descripcion = ?, contenido = ?, 
                orden = ?, tipo = ?, configuraciones = ?, recursos = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['unidad_id'],
            trim($_POST['titulo']),
            trim($_POST['descripcion']),
            $contenido,
            $_POST['orden'],
            $_POST['tipo'],
            json_encode($configuraciones),
            json_encode($recursos),
            $leccion_id
        ]);

        // Registrar en auditoría
        registrarAuditoria($conexion, 'LECCIONES', 'ACTUALIZACION', 'lecciones', $leccion_id, [
            'titulo_anterior' => $leccion_actual['titulo'],
            'titulo_nuevo' => $_POST['titulo'],
            'cambios' => 'Actualización de lección'
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Lección actualizada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN OBTENER LECCIÓN ====================
function obtenerLeccion() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la lección no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT l.*, 
               u.titulo as unidad_titulo,
               u.curso_id,
               c.nombre as curso_nombre,
               COUNT(DISTINCT pe.id) as total_estudiantes,
               AVG(pe.progreso) as progreso_promedio
        FROM lecciones l
        INNER JOIN unidades u ON l.unidad_id = u.id
        INNER JOIN cursos c ON u.curso_id = c.id
        LEFT JOIN progreso_estudiantes pe ON l.id = pe.leccion_id
        WHERE l.id = ?
        GROUP BY l.id
    ");
    $stmt->execute([$id]);
    $leccion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leccion) {
        throw new Exception('Lección no encontrada');
    }

    // Decodificar JSON
    $leccion['configuraciones'] = json_decode($leccion['configuraciones'], true);
    $leccion['recursos'] = json_decode($leccion['recursos'], true);

    return [
        'success' => true,
        'leccion' => $leccion
    ];
}

// ==================== FUNCIÓN ELIMINAR LECCIÓN ====================
function eliminarLeccion() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la lección no especificado');
    }

    $id = (int)$_POST['id'];

    $conexion->beginTransaction();

    try {
        // Verificar que la lección existe
        $stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id = ?");
        $stmt->execute([$id]);
        $leccion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$leccion) {
            throw new Exception('Lección no encontrada');
        }

        // Verificar si hay progreso de estudiantes
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM progreso_estudiantes WHERE leccion_id = ?");
        $stmt->execute([$id]);
        $progreso = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($progreso['total'] > 0) {
            throw new Exception('No se puede eliminar una lección con progreso de estudiantes registrado. Considere desactivarla en lugar de eliminarla.');
        }

        // Eliminar la lección
        $stmt = $conexion->prepare("DELETE FROM lecciones WHERE id = ?");
        $stmt->execute([$id]);

        // Registrar en auditoría
        registrarAuditoria($conexion, 'LECCIONES', 'ELIMINACION', 'lecciones', $id, [
            'titulo' => $leccion['titulo'],
            'unidad_id' => $leccion['unidad_id']
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Lección eliminada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN OBTENER PROGRESO DE ESTUDIANTES ====================
function obtenerProgresoEstudiantes() {
    global $conexion;
    
    if (!isset($_POST['leccion_id'])) {
        throw new Exception('ID de la lección no especificado');
    }

    $leccion_id = (int)$_POST['leccion_id'];

    $stmt = $conexion->prepare("
        SELECT pe.*, 
               e.codigo_estudiante,
               CONCAT(e.nombres, ' ', e.apellidos) as estudiante_nombre,
               pe.progreso,
               pe.estado,
               pe.tiempo_dedicado
        FROM progreso_estudiantes pe
        INNER JOIN estudiantes e ON pe.estudiante_id = e.id
        WHERE pe.leccion_id = ?
        ORDER BY pe.progreso DESC, e.apellidos ASC
    ");
    $stmt->execute([$leccion_id]);
    $progreso = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'progreso' => $progreso
    ];
}

// ==================== VALIDACIONES EXHAUSTIVAS ====================
function validarDatosLeccion($datos, $leccion_id = null) {
    $errores = [];

    // 1. Validar unidad_id
    if (!isset($datos['unidad_id']) || !is_numeric($datos['unidad_id']) || $datos['unidad_id'] <= 0) {
        $errores[] = "ID de unidad inválido";
    }

    // 2-6. Validar título
    $titulo = trim($datos['titulo'] ?? '');
    if (empty($titulo)) {
        $errores[] = "El título es obligatorio";
    } elseif (strlen($titulo) < 5) {
        $errores[] = "El título debe tener al menos 5 caracteres";
    } elseif (strlen($titulo) > 255) {
        $errores[] = "El título no puede superar 255 caracteres";
    } elseif (preg_match('/^\d+$/', $titulo)) {
        $errores[] = "El título no puede ser solo números";
    } elseif (preg_match_all('/[^a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ\-\:]/', $titulo) > 5) {
        $errores[] = "El título tiene demasiados caracteres especiales";
    }

    // 7-10. Validar descripción
    $descripcion = trim($datos['descripcion'] ?? '');
    if (empty($descripcion)) {
        $errores[] = "La descripción es obligatoria";
    } elseif (strlen($descripcion) < 10) {
        $errores[] = "La descripción debe tener al menos 10 caracteres";
    } elseif (strlen($descripcion) > 500) {
        $errores[] = "La descripción no puede superar 500 caracteres";
    } elseif (strlen(preg_replace('/[\s\n\r]/', '', $descripcion)) < 10) {
        $errores[] = "La descripción debe tener contenido real";
    }

    // 11-13. Validar orden
    $orden = $datos['orden'] ?? '';
    if (empty($orden) || !is_numeric($orden)) {
        $errores[] = "El orden es obligatorio y debe ser numérico";
    } elseif ($orden < 1 || $orden > 100) {
        $errores[] = "El orden debe estar entre 1 y 100";
    } elseif ($orden != intval($orden)) {
        $errores[] = "El orden debe ser un número entero";
    }

    // 14. Validar tipo
    $tipos_validos = ['CONTENIDO', 'ACTIVIDAD', 'EVALUACION'];
    if (empty($datos['tipo']) || !in_array($datos['tipo'], $tipos_validos)) {
        $errores[] = "Tipo de lección inválido";
    }

    // 15-17. Validar tiempo estimado
    $tiempo = $datos['tiempo_estimado'] ?? '';
    if (empty($tiempo) || !is_numeric($tiempo)) {
        $errores[] = "El tiempo estimado es obligatorio y debe ser numérico";
    } elseif ($tiempo < 1 || $tiempo > 300) {
        $errores[] = "El tiempo estimado debe estar entre 1 y 300 minutos";
    } elseif ($tiempo != intval($tiempo)) {
        $errores[] = "El tiempo estimado debe ser un número entero";
    }

    // 18. Validar estado
    $estados_validos = ['BORRADOR', 'PUBLICADO'];
    if (empty($datos['estado']) || !in_array($datos['estado'], $estados_validos)) {
        $errores[] = "Estado inválido";
    }

    // 19-22. Validar contenido HTML
    $contenido = $datos['contenido'] ?? '';
    if (!empty($contenido)) {
        if (strlen($contenido) > 50000) {
            $errores[] = "El contenido excede el límite de 50,000 caracteres";
        }
        
        // Verificar tags prohibidos
        $tags_prohibidos = ['script', 'iframe', 'object', 'embed', 'link', 'style'];
        foreach ($tags_prohibidos as $tag) {
            if (stripos($contenido, "<$tag") !== false) {
                $errores[] = "El contenido contiene tags HTML no permitidos ($tag)";
                break;
            }
        }
        
        // Validar balanceo de tags
        if (!validarHTMLBalanceado($contenido)) {
            $errores[] = "El HTML tiene tags desbalanceados";
        }
    }

    // 23. Validar coherencia tipo-contenido para EVALUACION
    if (isset($datos['tipo']) && $datos['tipo'] === 'EVALUACION' && !empty($contenido) && strlen($contenido) > 2000) {
        $errores[] = "Las evaluaciones no deberían tener contenido tan extenso";
    }

    // 24. Validar coherencia tipo-contenido para CONTENIDO
    if (isset($datos['tipo']) && $datos['tipo'] === 'CONTENIDO' && (empty($contenido) || strlen(trim($contenido)) < 50)) {
        $errores[] = "Las lecciones de contenido deben tener al menos 50 caracteres";
    }

    // 25. Validar tiempo mínimo para evaluaciones
    if (isset($datos['tipo']) && $datos['tipo'] === 'EVALUACION' && isset($tiempo) && $tiempo < 10) {
        $errores[] = "Las evaluaciones deben durar al menos 10 minutos";
    }

    // 26. Validar estado PUBLICADO con contenido
    if (isset($datos['estado']) && $datos['estado'] === 'PUBLICADO' && (empty($contenido) || strlen(trim($contenido)) < 20)) {
        $errores[] = "No puede publicar una lección sin contenido suficiente";
    }

    // 27. Validar longitud máxima de título con espacios
    if (isset($titulo) && str_word_count($titulo) > 25) {
        $errores[] = "El título no debe exceder 25 palabras";
    }

    // 28. Validar que el título no contenga URLs
    if (isset($titulo) && preg_match('/https?:\/\/|www\./i', $titulo)) {
        $errores[] = "El título no debe contener URLs";
    }

    // 29. Validar caracteres mínimos sin HTML en contenido
    if (!empty($contenido)) {
        $contenido_texto = strip_tags($contenido);
        if (strlen(trim($contenido_texto)) < 20 && isset($datos['estado']) && $datos['estado'] === 'PUBLICADO') {
            $errores[] = "El contenido debe tener al menos 20 caracteres de texto real (sin HTML)";
        }
    }

    // 30. Validar que descripción no sea igual al título
    if (isset($titulo) && isset($descripcion) && strtolower($titulo) === strtolower($descripcion)) {
        $errores[] = "La descripción no puede ser idéntica al título";
    }

    // Si hay errores, lanzar excepción con todos los errores
    if (!empty($errores)) {
        throw new Exception("Errores de validación:\n• " . implode("\n• ", $errores));
    }

    return true;
}

// ==================== VALIDAR RECURSOS ====================
function validarRecursos($recursos) {
    if (!is_array($recursos)) {
        throw new Exception("El formato de recursos es inválido");
    }

    if (count($recursos) > 10) {
        throw new Exception("Solo se permiten hasta 10 recursos por lección");
    }

    $tipos_validos = ['PDF', 'VIDEO', 'ENLACE', 'IMAGEN'];
    $urls_vistas = [];

    foreach ($recursos as $index => $recurso) {
        $num = $index + 1;

        // Validar estructura
        if (!isset($recurso['tipo']) || !isset($recurso['titulo']) || !isset($recurso['url'])) {
            throw new Exception("Recurso $num: estructura incompleta");
        }

        // Validar tipo
        if (!in_array($recurso['tipo'], $tipos_validos)) {
            throw new Exception("Recurso $num: tipo inválido");
        }

        // Validar título
        $titulo = trim($recurso['titulo']);
        if (strlen($titulo) < 3) {
            throw new Exception("Recurso $num: título muy corto (mínimo 3 caracteres)");
        }
        if (strlen($titulo) > 100) {
            throw new Exception("Recurso $num: título muy largo (máximo 100 caracteres)");
        }

        // Validar URL
        $url = trim($recurso['url']);
        if (empty($url)) {
            throw new Exception("Recurso $num: URL vacía");
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Recurso $num: URL inválida");
        }
        if (strlen($url) > 500) {
            throw new Exception("Recurso $num: URL muy larga (máximo 500 caracteres)");
        }

        // Validar URLs duplicadas
        if (in_array($url, $urls_vistas)) {
            throw new Exception("Recurso $num: URL duplicada");
        }
        $urls_vistas[] = $url;

        // Validar coherencia tipo-URL
        if ($recurso['tipo'] === 'VIDEO') {
            if (!preg_match('/(youtube|vimeo|youtu\.be|\.mp4)/i', $url)) {
                throw new Exception("Recurso $num: URL no parece ser de video");
            }
        }

        if ($recurso['tipo'] === 'PDF') {
            if (!preg_match('/\.pdf$/i', $url)) {
                throw new Exception("Recurso $num: URL no parece ser PDF");
            }
        }

        if ($recurso['tipo'] === 'IMAGEN') {
            if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
                throw new Exception("Recurso $num: URL no parece ser imagen");
            }
        }

        // Validar protocolo seguro para recursos externos
        if (!preg_match('/^https:\/\//i', $url)) {
            throw new Exception("Recurso $num: Solo se permiten URLs con protocolo HTTPS");
        }
    }

    return true;
}

// ==================== VALIDAR HTML BALANCEADO ====================
function validarHTMLBalanceado($html) {
    $tags_permitidos = ['h2', 'h3', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'br', 'hr'];
    $stack = [];
    
    preg_match_all('/<\/?([a-z][a-z0-9]*)\b[^>]*>/i', $html, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $tag = strtolower($match[1]);
        
        // Verificar si el tag está permitido
        if (!in_array($tag, $tags_permitidos)) {
            return false;
        }
        
        // Tags auto-cerrados
        if (in_array($tag, ['br', 'hr', 'img'])) {
            continue;
        }
        
        $is_closing = $match[0][1] === '/';
        
        if ($is_closing) {
            if (empty($stack) || end($stack) !== $tag) {
                return false;
            }
            array_pop($stack);
        } else {
            $stack[] = $tag;
        }
    }
    
    return empty($stack);
}

// ==================== SANITIZAR CONTENIDO HTML ====================
function sanitizarContenidoHTML($html) {
    if (empty($html)) {
        return '';
    }

    // Lista blanca de tags permitidos
    $tags_permitidos = '<h2><h3><p><strong><em><ul><ol><li><br><hr>';
    
    // Eliminar tags no permitidos
    $html_limpio = strip_tags($html, $tags_permitidos);
    
    // Eliminar atributos peligrosos
    $html_limpio = preg_replace('/<(\w+)[^>]*?(on\w+\s*=)[^>]*?>/i', '<$1>', $html_limpio);
    
    // Eliminar javascript:
    $html_limpio = preg_replace('/javascript:/i', '', $html_limpio);
    
    // Limpiar espacios excesivos
    $html_limpio = preg_replace('/\s+/', ' ', $html_limpio);
    
    return trim($html_limpio);
}

// ==================== REGISTRAR AUDITORÍA ====================
function registrarAuditoria($conexion, $modulo, $accion, $tabla, $registro_id, $datos_cambio) {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, registro_id, 
                datos_cambio, fecha_evento
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        
        $stmt->execute([
            $usuario_id,
            $modulo,
            $accion,
            $tabla,
            $registro_id,
            json_encode($datos_cambio)
        ]);
        
        return true;
    } catch (Exception $e) {
        // No lanzar excepción si falla el registro de auditoría
        error_log("Error en auditoría: " . $e->getMessage());
        return false;
    }
}

// ==================== FUNCIÓN AUXILIAR: LIMPIAR ENTRADA ====================
function limpiarEntrada($data) {
    if (is_array($data)) {
        return array_map('limpiarEntrada', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

// ==================== VALIDACIONES ADICIONALES DE SEGURIDAD ====================
function validarSesionActiva() {
    session_start();
    
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        throw new Exception('Sesión no válida. Por favor, inicie sesión nuevamente.');
    }
    
    // Validar tiempo de sesión
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];
        $tiempo_maximo = 7200; // 2 horas
        
        if ($tiempo_inactivo > $tiempo_maximo) {
            session_destroy();
            throw new Exception('Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
        }
    }
    
    $_SESSION['ultimo_acceso'] = time();
    
    return true;
}

// ==================== VALIDAR PERMISOS DE USUARIO ====================
function validarPermisos($accion_requerida) {
    global $conexion;
    
    if (!isset($_SESSION['rol_id'])) {
        throw new Exception('No se puede determinar el rol del usuario');
    }
    
    $stmt = $conexion->prepare("SELECT permisos FROM roles WHERE id = ?");
    $stmt->execute([$_SESSION['rol_id']]);
    $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rol) {
        throw new Exception('Rol no encontrado');
    }
    
    $permisos = json_decode($rol['permisos'], true);
    
    // Super admin tiene todos los permisos
    if (in_array('*', $permisos)) {
        return true;
    }
    
    // Validar permiso específico
    $permisos_requeridos = [
        'crear' => 'eva.contenidos',
        'actualizar' => 'eva.contenidos',
        'eliminar' => 'eva.contenidos'
    ];
    
    $permiso_necesario = $permisos_requeridos[$accion_requerida] ?? 'eva.*';
    
    if (!in_array($permiso_necesario, $permisos) && !in_array('eva.*', $permisos)) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }
    
    return true;
}

// ==================== VALIDAR LÍMITE DE TASA (RATE LIMITING) ====================
function validarRateLimiting($max_intentos = 20, $tiempo_ventana = 60) {
    session_start();
    
    $clave = 'rate_limit_lecciones_' . ($_SESSION['usuario_id'] ?? session_id());
    
    if (!isset($_SESSION[$clave])) {
        $_SESSION[$clave] = [
            'intentos' => 1,
            'tiempo_inicio' => time()
        ];
        return true;
    }
    
    $tiempo_transcurrido = time() - $_SESSION[$clave]['tiempo_inicio'];
    
    if ($tiempo_transcurrido > $tiempo_ventana) {
        // Reiniciar contador
        $_SESSION[$clave] = [
            'intentos' => 1,
            'tiempo_inicio' => time()
        ];
        return true;
    }
    
    $_SESSION[$clave]['intentos']++;
    
    if ($_SESSION[$clave]['intentos'] > $max_intentos) {
        throw new Exception('Ha excedido el límite de solicitudes. Espere ' . ($tiempo_ventana - $tiempo_transcurrido) . ' segundos.');
    }
    
    return true;
}

// ==================== VALIDACIÓN DE TAMAÑO DE DATOS ====================
function validarTamanioDatos() {
    $max_size = 5 * 1024 * 1024; // 5 MB
    $content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
    
    if ($content_length > $max_size) {
        throw new Exception('Los datos enviados exceden el tamaño máximo permitido (5MB)');
    }
    
    return true;
}

// ==================== LOGGING DE ERRORES CRÍTICOS ====================
function logErrorCritico($error, $contexto = []) {
    $log_file = '../../logs/lecciones_errors.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
        }
    
    $timestamp = date('Y-m-d H:i:s');
    $usuario_id = $_SESSION['usuario_id'] ?? 'NO_AUTH';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    $log_message = sprintf(
        "[%s] Usuario: %s | IP: %s | Error: %s | Contexto: %s\n",
        $timestamp,
        $usuario_id,
        $ip,
        $error,
        json_encode($contexto)
    );
    
    error_log($log_message, 3, $log_file);
}

// ==================== VALIDACIÓN DE INYECCIÓN SQL ====================
function detectarInyeccionSQL($datos) {
    $patrones_sospechosos = [
        '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
        '/--/',
        '/\/\*/',
        '/;/',
        '/\bOR\b\s+\d+\s*=\s*\d+/i',
        '/\bAND\b\s+\d+\s*=\s*\d+/i'
    ];
    
    if (is_array($datos)) {
        foreach ($datos as $valor) {
            if (detectarInyeccionSQL($valor)) {
                return true;
            }
        }
        return false;
    }
    
    if (!is_string($datos)) {
        return false;
    }
    
    foreach ($patrones_sospechosos as $patron) {
        if (preg_match($patron, $datos)) {
            logErrorCritico('Posible intento de inyección SQL detectado', [
                'patron' => $patron,
                'dato_sospechoso' => substr($datos, 0, 100)
            ]);
            return true;
        }
    }
    
    return false;
}

// ==================== VALIDACIÓN DE XSS ====================
function detectarXSS($datos) {
    $patrones_xss = [
        '/<script\b[^>]*>.*?<\/script>/is',
        '/on\w+\s*=\s*["\'].*?["\']/i',
        '/javascript:/i',
        '/<iframe\b[^>]*>/i',
        '/<object\b[^>]*>/i',
        '/<embed\b[^>]*>/i'
    ];
    
    if (is_array($datos)) {
        foreach ($datos as $valor) {
            if (detectarXSS($valor)) {
                return true;
            }
        }
        return false;
    }
    
    if (!is_string($datos)) {
        return false;
    }
    
    foreach ($patrones_xss as $patron) {
        if (preg_match($patron, $datos)) {
            logErrorCritico('Posible intento de XSS detectado', [
                'patron' => $patron,
                'dato_sospechoso' => substr($datos, 0, 100)
            ]);
            return true;
        }
    }
    
    return false;
}

// ==================== MIDDLEWARE DE SEGURIDAD ====================
function aplicarSeguridadMiddleware() {
    try {
        // 1. Validar tamaño de datos
        validarTamanioDatos();
        
        // 2. Validar rate limiting
        validarRateLimiting();
        
        // 3. Validar sesión (comentado para desarrollo, descomentar en producción)
        // validarSesionActiva();
        
        // 4. Detectar inyección SQL
        if (detectarInyeccionSQL($_POST)) {
            throw new Exception('Solicitud rechazada por seguridad: contenido sospechoso detectado');
        }
        
        // 5. Detectar XSS
        if (detectarXSS($_POST)) {
            throw new Exception('Solicitud rechazada por seguridad: código malicioso detectado');
        }
        
        // 6. Validar origen de la solicitud (CSRF básico)
        validarOrigenSolicitud();
        
        return true;
        
    } catch (Exception $e) {
        logErrorCritico('Error en middleware de seguridad: ' . $e->getMessage(), $_POST);
        throw $e;
    }
}

// ==================== VALIDAR ORIGEN DE SOLICITUD (CSRF) ====================
function validarOrigenSolicitud() {
    // Validar que la solicitud venga del mismo dominio
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    if (!empty($referer) && !empty($host)) {
        $referer_host = parse_url($referer, PHP_URL_HOST);
        
        if ($referer_host !== $host) {
            logErrorCritico('Posible ataque CSRF detectado', [
                'referer' => $referer,
                'host' => $host
            ]);
            throw new Exception('Solicitud rechazada: origen no válido');
        }
    }
    
    return true;
}

// ==================== VALIDACIÓN DE INTEGRIDAD DE DATOS ====================
function validarIntegridadDatos($datos) {
    // Validar que los datos críticos no estén vacíos
    $campos_criticos = ['unidad_id', 'titulo', 'tipo'];
    
    foreach ($campos_criticos as $campo) {
        if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
            throw new Exception("Campo crítico '$campo' está vacío o no existe");
        }
    }
    
    // Validar tipos de datos
    if (isset($datos['unidad_id']) && !is_numeric($datos['unidad_id'])) {
        throw new Exception('ID de unidad debe ser numérico');
    }
    
    if (isset($datos['orden']) && !is_numeric($datos['orden'])) {
        throw new Exception('Orden debe ser numérico');
    }
    
    if (isset($datos['tiempo_estimado']) && !is_numeric($datos['tiempo_estimado'])) {
        throw new Exception('Tiempo estimado debe ser numérico');
    }
    
    return true;
}

// ==================== FUNCIÓN DE BACKUP ANTES DE ACTUALIZAR ====================
function crearBackupLeccion($leccion_id) {
    global $conexion;
    
    try {
        // Obtener datos actuales de la lección
        $stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id = ?");
        $stmt->execute([$leccion_id]);
        $leccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leccion) {
            return false;
        }
        
        // Guardar backup en tabla de auditoría
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, registro_id,
                datos_cambio, metadatos, fecha_evento
            ) VALUES (?, 'LECCIONES', 'BACKUP', 'lecciones', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $leccion_id,
            json_encode($leccion),
            json_encode(['tipo_backup' => 'PRE_ACTUALIZACION'])
        ]);
        
        return true;
        
    } catch (Exception $e) {
        logErrorCritico('Error al crear backup: ' . $e->getMessage(), ['leccion_id' => $leccion_id]);
        return false;
    }
}

// ==================== VALIDAR DEPENDENCIAS ANTES DE ELIMINAR ====================
function validarDependenciasEliminacion($leccion_id) {
    global $conexion;
    
    $dependencias = [];
    
    // Verificar progreso de estudiantes
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM progreso_estudiantes WHERE leccion_id = ?");
    $stmt->execute([$leccion_id]);
    $progreso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($progreso['total'] > 0) {
        $dependencias[] = "Progreso de {$progreso['total']} estudiante(s)";
    }
    
    // Aquí se pueden agregar más validaciones de dependencias
    // Por ejemplo: calificaciones, comentarios, etc.
    
    return $dependencias;
}

// ==================== ESTADÍSTICAS DE LECCIÓN ====================
function obtenerEstadisticasLeccion($leccion_id) {
    global $conexion;
    
    try {
        $estadisticas = [];
        
        // Total de estudiantes
        $stmt = $conexion->prepare("
            SELECT COUNT(DISTINCT estudiante_id) as total 
            FROM progreso_estudiantes 
            WHERE leccion_id = ?
        ");
        $stmt->execute([$leccion_id]);
        $estadisticas['total_estudiantes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Progreso promedio
        $stmt = $conexion->prepare("
            SELECT AVG(progreso) as promedio 
            FROM progreso_estudiantes 
            WHERE leccion_id = ?
        ");
        $stmt->execute([$leccion_id]);
        $estadisticas['progreso_promedio'] = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 2);
        
        // Estudiantes completados
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as total 
            FROM progreso_estudiantes 
            WHERE leccion_id = ? AND estado = 'COMPLETADO'
        ");
        $stmt->execute([$leccion_id]);
        $estadisticas['estudiantes_completados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tiempo promedio dedicado
        $stmt = $conexion->prepare("
            SELECT AVG(tiempo_dedicado) as promedio 
            FROM progreso_estudiantes 
            WHERE leccion_id = ? AND tiempo_dedicado > 0
        ");
        $stmt->execute([$leccion_id]);
        $estadisticas['tiempo_promedio'] = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'], 0);
        
        return $estadisticas;
        
    } catch (Exception $e) {
        logErrorCritico('Error al obtener estadísticas: ' . $e->getMessage(), ['leccion_id' => $leccion_id]);
        return [];
    }
}

// ==================== NOTIFICAR CAMBIOS A ESTUDIANTES ====================
function notificarCambiosEstudiantes($leccion_id, $tipo_cambio) {
    global $conexion;
    
    try {
        // Obtener información de la lección
        $stmt = $conexion->prepare("
            SELECT l.titulo, u.titulo as unidad_titulo, c.nombre as curso_nombre
            FROM lecciones l
            INNER JOIN unidades u ON l.unidad_id = u.id
            INNER JOIN cursos c ON u.curso_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$leccion_id]);
        $leccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leccion) {
            return false;
        }
        
        // Obtener estudiantes que tienen progreso en esta lección
        $stmt = $conexion->prepare("
            SELECT DISTINCT pe.estudiante_id, e.usuario_id
            FROM progreso_estudiantes pe
            INNER JOIN estudiantes e ON pe.estudiante_id = e.id
            WHERE pe.leccion_id = ? AND e.usuario_id IS NOT NULL
        ");
        $stmt->execute([$leccion_id]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Crear notificaciones
        $mensajes = [
            'actualizado' => "La lección '{$leccion['titulo']}' ha sido actualizada",
            'eliminado' => "La lección '{$leccion['titulo']}' ha sido eliminada",
            'publicado' => "Nueva lección disponible: '{$leccion['titulo']}'"
        ];
        
        $mensaje = $mensajes[$tipo_cambio] ?? "Cambios en la lección '{$leccion['titulo']}'";
        
        foreach ($estudiantes as $estudiante) {
            $stmt = $conexion->prepare("
                INSERT INTO notificaciones_sistema (
                    usuario_id, titulo, mensaje, tipo, origen, 
                    configuracion, fecha_creacion
                ) VALUES (?, 'Actualización de Contenido', ?, 'ANUNCIO', ?, ?, NOW())
            ");
            
            $stmt->execute([
                $estudiante['usuario_id'],
                $mensaje,
                json_encode(['tipo' => 'leccion', 'id' => $leccion_id]),
                json_encode(['leida' => false, 'activa' => true])
            ]);
        }
        
        return true;
        
    } catch (Exception $e) {
        logErrorCritico('Error al notificar cambios: ' . $e->getMessage(), [
            'leccion_id' => $leccion_id,
            'tipo_cambio' => $tipo_cambio
        ]);
        return false;
    }
}

// ==================== VALIDAR CONSISTENCIA DE ORDEN ====================
function validarConsistenciaOrden($unidad_id, $orden, $leccion_id = null) {
    global $conexion;
    
    try {
        // Obtener todas las lecciones de la unidad
        $sql = "SELECT id, orden FROM lecciones WHERE unidad_id = ?";
        $params = [$unidad_id];
        
        if ($leccion_id) {
            $sql .= " AND id != ?";
            $params[] = $leccion_id;
        }
        
        $sql .= " ORDER BY orden ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar si el orden ya existe
        foreach ($lecciones as $leccion) {
            if ($leccion['orden'] == $orden) {
                return false;
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        logErrorCritico('Error al validar consistencia de orden: ' . $e->getMessage(), [
            'unidad_id' => $unidad_id,
            'orden' => $orden
        ]);
        return false;
    }
}

// ==================== REORDENAR LECCIONES AUTOMÁTICAMENTE ====================
function reordenarLeccionesUnidad($unidad_id) {
    global $conexion;
    
    try {
        // Obtener todas las lecciones ordenadas
        $stmt = $conexion->prepare("
            SELECT id FROM lecciones 
            WHERE unidad_id = ? 
            ORDER BY orden ASC, fecha_creacion ASC
        ");
        $stmt->execute([$unidad_id]);
        $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Reordenar secuencialmente
        $orden = 1;
        foreach ($lecciones as $leccion) {
            $stmt = $conexion->prepare("UPDATE lecciones SET orden = ? WHERE id = ?");
            $stmt->execute([$orden, $leccion['id']]);
            $orden++;
        }
        
        return true;
        
    } catch (Exception $e) {
        logErrorCritico('Error al reordenar lecciones: ' . $e->getMessage(), ['unidad_id' => $unidad_id]);
        return false;
    }
}

// ==================== EXPORTAR LECCIÓN ====================
function exportarLeccion($leccion_id) {
    global $conexion;
    
    try {
        $stmt = $conexion->prepare("
            SELECT l.*, 
                   u.titulo as unidad_titulo,
                   c.nombre as curso_nombre
            FROM lecciones l
            INNER JOIN unidades u ON l.unidad_id = u.id
            INNER JOIN cursos c ON u.curso_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$leccion_id]);
        $leccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leccion) {
            throw new Exception('Lección no encontrada');
        }
        
        // Decodificar JSON
        $leccion['configuraciones'] = json_decode($leccion['configuraciones'], true);
        $leccion['recursos'] = json_decode($leccion['recursos'], true);
        
        // Agregar estadísticas
        $leccion['estadisticas'] = obtenerEstadisticasLeccion($leccion_id);
        
        return [
            'success' => true,
            'leccion' => $leccion,
            'formato' => 'json',
            'fecha_exportacion' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al exportar lección: ' . $e->getMessage());
    }
}

// ==================== CLONAR LECCIÓN ====================
function clonarLeccion($leccion_id) {
    global $conexion;
    
    $conexion->beginTransaction();
    
    try {
        // Obtener lección original
        $stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id = ?");
        $stmt->execute([$leccion_id]);
        $leccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$leccion) {
            throw new Exception('Lección no encontrada');
        }
        
        // Obtener siguiente orden disponible
        $stmt = $conexion->prepare("SELECT MAX(orden) as max_orden FROM lecciones WHERE unidad_id = ?");
        $stmt->execute([$leccion['unidad_id']]);
        $max_orden = $stmt->fetch(PDO::FETCH_ASSOC)['max_orden'];
        $nuevo_orden = $max_orden + 1;
        
        // Insertar clon
        $stmt = $conexion->prepare("
            INSERT INTO lecciones (
                unidad_id, titulo, descripcion, contenido, orden, tipo,
                configuraciones, recursos, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $nuevo_titulo = $leccion['titulo'] . ' (Copia)';
        
        $stmt->execute([
            $leccion['unidad_id'],
            $nuevo_titulo,
            $leccion['descripcion'],
            $leccion['contenido'],
            $nuevo_orden,
            $leccion['tipo'],
            $leccion['configuraciones'],
            $leccion['recursos']
        ]);
        
        $nuevo_id = $conexion->lastInsertId();
        
        // Registrar en auditoría
        registrarAuditoria($conexion, 'LECCIONES', 'CLONACION', 'lecciones', $nuevo_id, [
            'leccion_original_id' => $leccion_id,
            'nuevo_titulo' => $nuevo_titulo
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Lección clonada exitosamente',
            'nuevo_id' => $nuevo_id
        ];
        
    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception('Error al clonar lección: ' . $e->getMessage());
    }
}

// ==================== APLICAR MIDDLEWARE DE SEGURIDAD ====================
try {
    aplicarSeguridadMiddleware();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>