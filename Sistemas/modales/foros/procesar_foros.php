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
            $response = crearForo();
            break;
            
        case 'actualizar':
            $response = actualizarForo();
            break;
            
        case 'obtener':
            $response = obtenerForo();
            break;
            
        case 'obtener_mensajes':
            $response = obtenerMensajesForo();
            break;
            
        case 'crear_mensaje':
            $response = crearMensaje();
            break;
            
        case 'toggle_estado':
            $response = toggleEstadoForo();
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

// ==================== FUNCIÓN CREAR FORO ====================
function crearForo() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['curso_id', 'titulo', 'descripcion', 'tipo', 'estado'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    // Validaciones adicionales del lado del servidor
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $curso_id = (int)$_POST['curso_id'];
    $tipo = $_POST['tipo'];
    $estado = $_POST['estado'];
    $moderado = isset($_POST['moderado']) ? 1 : 0;

    // Validación 1: Título (5-255 caracteres)
    if (strlen($titulo) < 5 || strlen($titulo) > 255) {
        throw new Exception('El título debe tener entre 5 y 255 caracteres');
    }

    // Validación 2: Caracteres especiales no permitidos
    if (preg_match('/[<>{}[\]\\\\\/]/', $titulo)) {
        throw new Exception('El título contiene caracteres no permitidos');
    }

    // Validación 3: No todo en mayúsculas
    if ($titulo === strtoupper($titulo) && strlen($titulo) > 10) {
        throw new Exception('No escriba el título todo en MAYÚSCULAS');
    }

    // Validación 4: Descripción (20-1000 caracteres)
    if (strlen($descripcion) < 20 || strlen($descripcion) > 1000) {
        throw new Exception('La descripción debe tener entre 20 y 1000 caracteres');
    }

    // Validación 5: Caracteres especiales en descripción
    if (preg_match('/[<>{}[\]\\\\\/]/', $descripcion)) {
        throw new Exception('La descripción contiene caracteres no permitidos');
    }

    // Validación 6: Al menos 5 palabras en descripción
    if (str_word_count($descripcion) < 5) {
        throw new Exception('La descripción debe contener al menos 5 palabras');
    }

    // Validación 7: Verificar que el curso existe y está activo
    $stmt = $conexion->prepare("
        SELECT c.id, c.nombre,
               CONCAT(ac.nombre, ' - ', s.grado, ' ', s.seccion) as curso_completo
        FROM cursos c
        INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        WHERE c.id = ? AND JSON_EXTRACT(c.configuraciones, '$.estado') = 'ACTIVO'
    ");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        throw new Exception('El curso seleccionado no existe o no está activo');
    }

    // Validación 8: Tipo de foro válido
    $tipos_validos = ['GENERAL', 'PREGUNTA_RESPUESTA', 'DEBATE', 'ANUNCIO'];
    if (!in_array($tipo, $tipos_validos)) {
        throw new Exception('Tipo de foro no válido');
    }

    // Validación 9: Estado válido
    $estados_validos = ['ABIERTO', 'CERRADO'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado de foro no válido');
    }

    // Validación 10: Verificar que no exista otro foro con el mismo título en el mismo curso
    $stmt = $conexion->prepare("SELECT id FROM foros WHERE curso_id = ? AND LOWER(titulo) = LOWER(?)");
    $stmt->execute([$curso_id, $titulo]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un foro con este título en el mismo curso');
    }

    // Validación 11: Título y descripción no pueden ser iguales
    if (strtolower($titulo) === strtolower($descripcion)) {
        throw new Exception('El título y la descripción no pueden ser idénticos');
    }

    // Validación 12: Usuario debe existir (simulamos usuario_id = 1 por ahora)
    $usuario_id = 1; // En producción, obtener de sesión
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$usuario_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Usuario no válido');
    }

    $conexion->beginTransaction();

    try {
        // Preparar configuraciones
        $configuraciones = [
            'tipo' => $tipo,
            'estado' => $estado,
            'moderado' => (bool)$moderado
        ];

        // Inicializar estadísticas vacías
        $estadisticas = [
            'total_mensajes' => 0,
            'participantes' => 0,
            'mensaje_mas_reciente' => null
        ];

        // Insertar foro
        $stmt = $conexion->prepare("
            INSERT INTO foros (
                curso_id, titulo, descripcion, configuraciones, 
                mensajes, estadisticas, usuario_creacion, fecha_creacion
            ) VALUES (?, ?, ?, ?, '[]', ?, ?, NOW())
        ");

        $stmt->execute([
            $curso_id,
            $titulo,
            $descripcion,
            json_encode($configuraciones),
            json_encode($estadisticas),
            $usuario_id
        ]);

        $foro_id = $conexion->lastInsertId();

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Foro creado exitosamente',
            'foro_id' => $foro_id
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN ACTUALIZAR FORO ====================
function actualizarForo() {
    global $conexion;
    
    if (!isset($_POST['foro_id'])) {
        throw new Exception('ID del foro no especificado');
    }

    $foro_id = (int)$_POST['foro_id'];

    // Validaciones similares a crear
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];
    $estado = $_POST['estado'];
    $moderado = isset($_POST['moderado']) ? 1 : 0;

    // Validación 1: Título
    if (strlen($titulo) < 5 || strlen($titulo) > 255) {
        throw new Exception('El título debe tener entre 5 y 255 caracteres');
    }

    // Validación 2: Caracteres especiales
    if (preg_match('/[<>{}[\]\\\\\/]/', $titulo) || preg_match('/[<>{}[\]\\\\\/]/', $descripcion)) {
        throw new Exception('El contenido contiene caracteres no permitidos');
    }

    // Validación 3: Descripción
    if (strlen($descripcion) < 20 || strlen($descripcion) > 1000) {
        throw new Exception('La descripción debe tener entre 20 y 1000 caracteres');
    }

    // Validación 4: Verificar que el foro existe
    $stmt = $conexion->prepare("SELECT * FROM foros WHERE id = ?");
    $stmt->execute([$foro_id]);
    $foro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foro) {
        throw new Exception('Foro no encontrado');
    }

    // Validación 5: Verificar título único en el curso (excluyendo el actual)
    $stmt = $conexion->prepare("
        SELECT id FROM foros 
        WHERE curso_id = ? AND LOWER(titulo) = LOWER(?) AND id != ?
    ");
    $stmt->execute([$foro['curso_id'], $titulo, $foro_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe otro foro con este título en el mismo curso');
    }

    // Validación 6: Tipo válido
    $tipos_validos = ['GENERAL', 'PREGUNTA_RESPUESTA', 'DEBATE', 'ANUNCIO'];
    if (!in_array($tipo, $tipos_validos)) {
        throw new Exception('Tipo de foro no válido');
    }

    // Validación 7: Estado válido
    $estados_validos = ['ABIERTO', 'CERRADO'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado de foro no válido');
    }

    // Validación 8: Si hay mensajes y se cambia a CERRADO, avisar
    $mensajes = json_decode($foro['mensajes'], true) ?: [];
    if (count($mensajes) > 0 && $estado === 'CERRADO') {
        // Solo una advertencia, no bloquear la operación
    }

    $conexion->beginTransaction();

    try {
        // Obtener configuraciones actuales y actualizarlas
        $config_actual = json_decode($foro['configuraciones'], true) ?: [];
        
        $configuraciones = [
            'tipo' => $tipo,
            'estado' => $estado,
            'moderado' => (bool)$moderado
        ];

        // Actualizar foro
        $stmt = $conexion->prepare("
            UPDATE foros SET 
                titulo = ?, 
                descripcion = ?, 
                configuraciones = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $titulo,
            $descripcion,
            json_encode($configuraciones),
            $foro_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Foro actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN OBTENER FORO ====================
function obtenerForo() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del foro no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT f.*, 
               c.nombre as curso_nombre,
               c.codigo_curso,
               CONCAT(ac.nombre, ' - ', s.grado, ' ', s.seccion) as curso_completo,
               u.nombres as creador_nombres,
               u.apellidos as creador_apellidos
        FROM foros f
        INNER JOIN cursos c ON f.curso_id = c.id
        INNER JOIN usuarios u ON f.usuario_creacion = u.id
        INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        WHERE f.id = ?
    ");
    $stmt->execute([$id]);
    $foro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foro) {
        throw new Exception('Foro no encontrado');
    }

    // Decodificar JSON
    $foro['configuraciones'] = json_decode($foro['configuraciones'], true);
    $foro['mensajes'] = json_decode($foro['mensajes'], true);
    $foro['estadisticas'] = json_decode($foro['estadisticas'], true);

    return [
        'success' => true,
        'foro' => $foro
    ];
}

// ==================== FUNCIÓN OBTENER MENSAJES FORO ====================
function obtenerMensajesForo() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del foro no especificado');
    }

    $id = (int)$_POST['id'];

    // Obtener foro
    $stmt = $conexion->prepare("
        SELECT f.*, 
               c.nombre as curso_nombre,
               c.codigo_curso
        FROM foros f
        INNER JOIN cursos c ON f.curso_id = c.id
        WHERE f.id = ?
    ");
    $stmt->execute([$id]);
    $foro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foro) {
        throw new Exception('Foro no encontrado');
    }

    // Decodificar JSON
    $mensajes = json_decode($foro['mensajes'], true) ?: [];
    $foro['estadisticas'] = json_decode($foro['estadisticas'], true);
    $foro['configuraciones'] = json_decode($foro['configuraciones'], true);

    // Enriquecer mensajes con información de usuarios
    $mensajes = enriquecerMensajes($mensajes);

    return [
        'success' => true,
        'foro' => $foro,
        'mensajes' => $mensajes
    ];
}

// Función auxiliar para enriquecer mensajes con datos de usuarios
function enriquecerMensajes($mensajes) {
    global $conexion;
    
    foreach ($mensajes as &$mensaje) {
        if (isset($mensaje['usuario_id'])) {
            $stmt = $conexion->prepare("
                SELECT CONCAT(nombres, ' ', apellidos) as nombre_completo
                FROM usuarios WHERE id = ?
            ");
            $stmt->execute([$mensaje['usuario_id']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $mensaje['usuario_nombre'] = $usuario ? $usuario['nombre_completo'] : 'Usuario Desconocido';
        }
        
        // Enriquecer respuestas recursivamente
        if (isset($mensaje['respuestas']) && is_array($mensaje['respuestas'])) {
            $mensaje['respuestas'] = enriquecerMensajes($mensaje['respuestas']);
        }
    }
    
    return $mensajes;
}

// ==================== FUNCIÓN CREAR MENSAJE ====================
function crearMensaje() {
    global $conexion;
    
    if (!isset($_POST['foro_id'])) {
        throw new Exception('ID del foro no especificado');
    }

    $foro_id = (int)$_POST['foro_id'];
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido']);
    $mensaje_padre_id = isset($_POST['mensaje_padre_id']) && !empty($_POST['mensaje_padre_id']) 
                        ? (int)$_POST['mensaje_padre_id'] 
                        : null;

    // Validación 1: Contenido obligatorio
    if (empty($contenido)) {
        throw new Exception('El contenido del mensaje es obligatorio');
    }

    // Validación 2: Longitud del contenido (10-2000 caracteres)
    if (strlen($contenido) < 10 || strlen($contenido) > 2000) {
        throw new Exception('El mensaje debe tener entre 10 y 2000 caracteres');
    }

    // Validación 3: Caracteres especiales
    if (preg_match('/[<>{}[\]\\\\]/', $contenido)) {
        throw new Exception('El mensaje contiene caracteres no permitidos');
    }

    // Validación 4: Título si se proporciona (máximo 150 caracteres)
    if (!empty($titulo) && strlen($titulo) > 150) {
        throw new Exception('El título no puede superar los 150 caracteres');
    }

    // Validación 5: Verificar que el foro existe y está abierto
    $stmt = $conexion->prepare("SELECT * FROM foros WHERE id = ?");
    $stmt->execute([$foro_id]);
    $foro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foro) {
        throw new Exception('Foro no encontrado');
    }

    $config = json_decode($foro['configuraciones'], true) ?: [];
    if (($config['estado'] ?? 'ABIERTO') === 'CERRADO') {
        throw new Exception('Este foro está cerrado y no acepta nuevos mensajes');
    }

    // Validación 6: Usuario válido (simulado por ahora)
    $usuario_id = 1; // En producción, obtener de sesión
    $stmt = $conexion->prepare("SELECT id, CONCAT(nombres, ' ', apellidos) as nombre_completo FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception('Usuario no válido');
    }

    $conexion->beginTransaction();

    try {
        // Obtener mensajes actuales
        $mensajes = json_decode($foro['mensajes'], true) ?: [];
        
        // Crear nuevo mensaje
        $nuevo_mensaje = [
            'id' => count($mensajes) + 1,
            'usuario_id' => $usuario_id,
            'usuario_nombre' => $usuario['nombre_completo'],
            'titulo' => $titulo,
            'contenido' => $contenido,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'respuestas' => []
        ];

        // Si es una respuesta, agregarla al mensaje padre
        if ($mensaje_padre_id) {
            $mensajes = agregarRespuesta($mensajes, $mensaje_padre_id, $nuevo_mensaje);
        } else {
            // Es un mensaje principal
            $mensajes[] = $nuevo_mensaje;
        }

        // Actualizar estadísticas
        $stats = json_decode($foro['estadisticas'], true) ?: [];
        $stats['total_mensajes'] = contarTodosMensajes($mensajes);
        $stats['mensaje_mas_reciente'] = date('Y-m-d H:i:s');
        
        // Contar participantes únicos
        $participantes_unicos = obtenerParticipantesUnicos($mensajes);
        $stats['participantes'] = count($participantes_unicos);

        // Actualizar foro
        $stmt = $conexion->prepare("
            UPDATE foros SET 
                mensajes = ?,
                estadisticas = ?
            WHERE id = ?
        ");

        $stmt->execute([
            json_encode($mensajes),
            json_encode($stats),
            $foro_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Mensaje publicado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// Función auxiliar para agregar respuesta a mensaje específico
function agregarRespuesta(&$mensajes, $padre_id, $nueva_respuesta) {
    foreach ($mensajes as &$mensaje) {
        if ($mensaje['id'] == $padre_id) {
            if (!isset($mensaje['respuestas'])) {
                $mensaje['respuestas'] = [];
            }
            $nueva_respuesta['id'] = count($mensaje['respuestas']) + 1;
            $mensaje['respuestas'][] = $nueva_respuesta;
            return $mensajes;
        }
        
        if (isset($mensaje['respuestas']) && is_array($mensaje['respuestas'])) {
            $mensaje['respuestas'] = agregarRespuesta($mensaje['respuestas'], $padre_id, $nueva_respuesta);
        }
    }
    
    return $mensajes;
}

// Función auxiliar para contar todos los mensajes (incluidas respuestas)
function contarTodosMensajes($mensajes) {
    $total = count($mensajes);
    
    foreach ($mensajes as $mensaje) {
        if (isset($mensaje['respuestas']) && is_array($mensaje['respuestas'])) {
            $total += contarTodosMensajes($mensaje['respuestas']);
        }
    }
    
    return $total;
}

// Función auxiliar para obtener participantes únicos
function obtenerParticipantesUnicos($mensajes, &$participantes = []) {
    foreach ($mensajes as $mensaje) {
        if (isset($mensaje['usuario_id'])) {
            $participantes[$mensaje['usuario_id']] = true;
        }
        
        if (isset($mensaje['respuestas']) && is_array($mensaje['respuestas'])) {
            obtenerParticipantesUnicos($mensaje['respuestas'], $participantes);
        }
    }
    
    return $participantes;
}

// ==================== FUNCIÓN TOGGLE ESTADO FORO ====================
function toggleEstadoForo() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $nuevo_estado = $_POST['estado'];

    // Validación 1: Estado válido
    $estados_validos = ['ABIERTO', 'CERRADO'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        throw new Exception('Estado no válido');
    }

    // Validación 2: Verificar que el foro existe
    $stmt = $conexion->prepare("SELECT * FROM foros WHERE id = ?");
    $stmt->execute([$id]);
    $foro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foro) {
        throw new Exception('Foro no encontrado');
    }

    $conexion->beginTransaction();

    try {
        // Obtener configuraciones actuales
        $config = json_decode($foro['configuraciones'], true) ?: [];
        
        // Actualizar estado
        $config['estado'] = $nuevo_estado;

        // Actualizar foro
        $stmt = $conexion->prepare("
            UPDATE foros SET 
                configuraciones = ?
            WHERE id = ?
        ");

        $stmt->execute([
            json_encode($config),
            $id
        ]);

        $conexion->commit();

        $accion = $nuevo_estado === 'ABIERTO' ? 'abierto' : 'cerrado';

        return [
            'success' => true,
            'message' => "Foro $accion exitosamente"
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}
?>