<?php
// modales/cursos/procesar_cursos.php
require_once '../../conexion/bd.php';
header('Content-Type: application/json; charset=utf-8');

// Configurar zona horaria
date_default_timezone_set('America/Lima');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $accion = $_POST['accion'];

    switch ($accion) {
        case 'crear':
            $response = crearCurso();
            break;
            
        case 'actualizar':
            $response = actualizarCurso();
            break;
            
        case 'obtener':
            $response = obtenerCurso();
            break;
            
        case 'detalles_completos':
            $response = obtenerDetallesCompletos();
            break;
            
        default:
            throw new Exception('Acción no válida: ' . $accion);
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => basename($e->getFile())
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;

/**
 * ============================================
 * CREAR NUEVO CURSO
 * ============================================
 * Validaciones exhaustivas + Transacción
 */
function crearCurso() {
    global $conexion;
    
    // ========== VALIDACIONES DE CAMPOS REQUERIDOS ==========
    $campos_requeridos = [
        'codigo_curso' => 'Código del curso',
        'nombre' => 'Nombre del curso',
        'descripcion' => 'Descripción',
        'asignacion_id' => 'Asignación docente',
        'estado' => 'Estado',
        'fecha_inicio' => 'Fecha de inicio',
        'fecha_fin' => 'Fecha de fin'
    ];
    
    foreach ($campos_requeridos as $campo => $nombre) {
        if (empty($_POST[$campo]) && $_POST[$campo] !== '0') {
            throw new Exception("El campo '{$nombre}' es requerido");
        }
    }

    // ========== VALIDACIÓN 1-5: CÓDIGO DEL CURSO ==========
    $codigo_curso = trim($_POST['codigo_curso']);
    
    // 1. Validar longitud
    if (strlen($codigo_curso) < 5 || strlen($codigo_curso) > 50) {
        throw new Exception('El código del curso debe tener entre 5 y 50 caracteres');
    }
    
    // 2. Validar formato (solo mayúsculas, números y guiones)
    if (!preg_match('/^[A-Z0-9\-]+$/', $codigo_curso)) {
        throw new Exception('El código solo puede contener mayúsculas, números y guiones');
    }
    
    // 3. Validar que contenga al menos un guión
    if (strpos($codigo_curso, '-') === false) {
        throw new Exception('El código debe contener al menos un guión separador');
    }
    
    // 4. Validar caracteres prohibidos
    if (preg_match('/[<>\'"&]/', $codigo_curso)) {
        throw new Exception('El código contiene caracteres no permitidos');
    }
    
    // 5. Verificar que no exista duplicado
    $stmt = $conexion->prepare("SELECT id FROM cursos WHERE codigo_curso = ?");
    $stmt->execute([$codigo_curso]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un curso con este código: ' . $codigo_curso);
    }

    // ========== VALIDACIÓN 6-10: NOMBRE DEL CURSO ==========
    $nombre = trim($_POST['nombre']);
    
    // 6. Validar longitud
    if (strlen($nombre) < 5 || strlen($nombre) > 200) {
        throw new Exception('El nombre debe tener entre 5 y 200 caracteres');
    }
    
    // 7. Validar que no sea solo números
    if (preg_match('/^\d+$/', $nombre)) {
        throw new Exception('El nombre no puede contener solo números');
    }
    
    // 8. Validar palabras de una letra
    $palabras = explode(' ', $nombre);
    foreach ($palabras as $palabra) {
        if (strlen($palabra) === 1 && !preg_match('/^[ABCD]$/i', $palabra)) {
            throw new Exception('El nombre contiene palabras de una sola letra no válidas');
        }
    }
    
    // 9. Validar repetición excesiva
    $palabrasUnicas = array_unique(array_map('strtolower', $palabras));
    if (count($palabrasUnicas) < count($palabras) * 0.5 && count($palabras) > 3) {
        throw new Exception('El nombre contiene demasiadas repeticiones');
    }
    
    // 10. Verificar duplicado de nombre para la misma asignación
    $stmt = $conexion->prepare("SELECT id FROM cursos WHERE nombre = ? AND asignacion_id = ?");
    $stmt->execute([$nombre, $_POST['asignacion_id']]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un curso con este nombre para la misma asignación');
    }

    // ========== VALIDACIÓN 11-15: DESCRIPCIÓN ==========
    $descripcion = trim($_POST['descripcion']);
    
    // 11. Validar longitud
    if (strlen($descripcion) < 10 || strlen($descripcion) > 500) {
        throw new Exception('La descripción debe tener entre 10 y 500 caracteres');
    }
    
    // 12. Validar que no sea igual al nombre
    if (strtolower($nombre) === strtolower($descripcion)) {
        throw new Exception('La descripción no puede ser igual al nombre del curso');
    }
    
    // 13. Validar repetitividad
    $palabrasDesc = explode(' ', strtolower($descripcion));
    $palabrasUnicasDesc = array_unique($palabrasDesc);
    if (count($palabrasUnicasDesc) < count($palabrasDesc) * 0.5 && count($palabrasDesc) > 10) {
        throw new Exception('La descripción es muy repetitiva');
    }
    
    // 14. Validar caracteres especiales excesivos
    preg_match_all('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,;:¿?¡!()\-]/', $descripcion, $matches);
    if (count($matches[0]) > 10) {
        throw new Exception('La descripción contiene demasiados caracteres especiales');
    }
    
    // 15. Validar similitud con el nombre (Levenshtein)
    $similitud = 0;
    similar_text(strtolower($nombre), strtolower($descripcion), $similitud);
    if ($similitud > 80) {
        throw new Exception('La descripción es demasiado similar al nombre');
    }

    // ========== VALIDACIÓN 16-17: ASIGNACIÓN DOCENTE ==========
    $asignacion_id = intval($_POST['asignacion_id']);
    
    // 16. Validar que sea un número válido
    if ($asignacion_id <= 0) {
        throw new Exception('ID de asignación inválido');
    }
    
    // 17. Verificar que la asignación existe y está activa
    $stmt = $conexion->prepare("
        SELECT ad.*, 
               d.nombres as docente_nombres, d.apellidos as docente_apellidos,
               ac.nombre as area_nombre,
               s.grado, s.seccion
        FROM asignaciones_docentes ad
        INNER JOIN docentes d ON ad.docente_id = d.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        WHERE ad.id = ? AND ad.activo = 1
    ");
    $stmt->execute([$asignacion_id]);
    $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignacion) {
        throw new Exception('La asignación docente seleccionada no existe o no está activa');
    }

    // ========== VALIDACIÓN 18: ESTADO ==========
    $estado = $_POST['estado'];
    $estados_validos = ['ACTIVO', 'BORRADOR', 'FINALIZADO'];
    
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado no válido. Debe ser: ACTIVO, BORRADOR o FINALIZADO');
    }

    // ========== VALIDACIÓN 19-25: FECHAS ==========
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    // 19. Validar formato de fechas
    if (!validateDate($fecha_inicio, 'Y-m-d')) {
        throw new Exception('Formato de fecha de inicio inválido');
    }
    
    if (!validateDate($fecha_fin, 'Y-m-d')) {
        throw new Exception('Formato de fecha de fin inválido');
    }
    
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $hoy = new DateTime();
    $hoy->setTime(0, 0, 0);
    
    // 20. Validar que fecha fin sea posterior a fecha inicio
    if ($fin <= $inicio) {
        throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
    }
    
    // 21. Validar duración mínima (7 días)
    $diferencia = $inicio->diff($fin);
    $dias = $diferencia->days;
    
    if ($dias < 7) {
        throw new Exception('El curso debe durar al menos 7 días (1 semana)');
    }
    
    // 22. Validar duración máxima (365 días)
    if ($dias > 365) {
        throw new Exception('El curso no puede durar más de 365 días (1 año)');
    }
    
    // 23. Validar coherencia con estado FINALIZADO
    if ($estado === 'FINALIZADO' && $fin > $hoy) {
        throw new Exception('No puede marcar como FINALIZADO un curso con fecha de fin futura');
    }
    
    // 24. Validar coherencia con estado ACTIVO (advertencia)
    if ($estado === 'ACTIVO') {
        $diasHastaInicio = $hoy->diff($inicio)->days;
        if ($inicio > $hoy && $diasHastaInicio > 30) {
            // Solo advertencia en logs, no bloquea
            error_log("ADVERTENCIA: Curso ACTIVO con inicio en más de 30 días");
        }
    }
    
    // 25. Validar que las fechas no estén en un rango excesivamente antiguo
    $hace5Anos = new DateTime();
    $hace5Anos->modify('-5 years');
    
    if ($inicio < $hace5Anos) {
        throw new Exception('La fecha de inicio no puede ser mayor a 5 años en el pasado');
    }

    // ========== VALIDACIÓN 26-27: CONFIGURACIONES ADICIONALES ==========
    $configuraciones = [];
    
    // 26. Color del tema
    $color_tema = isset($_POST['color_tema']) ? $_POST['color_tema'] : '#667eea';
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_tema)) {
        throw new Exception('Color del tema inválido. Debe ser formato hexadecimal #RRGGBB');
    }
    
    if (strtolower($color_tema) === '#ffffff') {
        throw new Exception('El color blanco no es recomendable para el tema del curso');
    }
    
    // 27. URL de imagen
    $imagen_portada = isset($_POST['imagen_portada']) ? trim($_POST['imagen_portada']) : '';
    if (!empty($imagen_portada)) {
        if (strlen($imagen_portada) > 255) {
            throw new Exception('La URL de la imagen es demasiado larga (máximo 255 caracteres)');
        }
        
        if (!preg_match('/^\\/[\w\-\\/\.]+\.(jpg|jpeg|png|gif|webp)$/i', $imagen_portada)) {
            throw new Exception('Formato de URL de imagen inválido. Debe empezar con / y terminar en jpg, png, gif o webp');
        }
    }
    
    // Inscripción libre
    $inscripcion_libre = isset($_POST['inscripcion_libre']) && $_POST['inscripcion_libre'] == '1';
    
    // Construir JSON de configuraciones
    $configuraciones = [
        'estado' => $estado,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'color_tema' => $color_tema,
        'imagen_portada' => $imagen_portada,
        'inscripcion_libre' => $inscripcion_libre
    ];

    // ========== INICIAR TRANSACCIÓN ==========
    $conexion->beginTransaction();

    try {
        // Insertar el curso
        $stmt = $conexion->prepare("
            INSERT INTO cursos (
                codigo_curso, nombre, descripcion, asignacion_id,
                configuraciones, estudiantes_inscritos, estadisticas,
                fecha_creacion, fecha_actualizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        // Inicializar JSON vacíos para estudiantes y estadísticas
        $estudiantes_inscritos = json_encode([]);
        $estadisticas = json_encode([
            'total_estudiantes' => 0,
            'progreso_promedio' => 0,
            'participacion_activa' => 0
        ]);

        $stmt->execute([
            $codigo_curso,
            $nombre,
            $descripcion,
            $asignacion_id,
            json_encode($configuraciones, JSON_UNESCAPED_UNICODE),
            $estudiantes_inscritos,
            $estadisticas
        ]);

        $curso_id = $conexion->lastInsertId();

        // Registrar en auditoría
        registrarAuditoria($conexion, 'CURSOS', 'CREACION', 'cursos', $curso_id, [
            'codigo_curso' => $codigo_curso,
            'nombre' => $nombre,
            'asignacion_id' => $asignacion_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Curso creado exitosamente: ' . $nombre,
            'curso_id' => $curso_id,
            'codigo_curso' => $codigo_curso
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception('Error al crear el curso: ' . $e->getMessage());
    }
}

/**
 * ============================================
 * ACTUALIZAR CURSO EXISTENTE
 * ============================================
 */
function actualizarCurso() {
    global $conexion;
    
    // Validar ID del curso
    if (!isset($_POST['curso_id']) || intval($_POST['curso_id']) <= 0) {
        throw new Exception('ID de curso inválido');
    }

    $curso_id = intval($_POST['curso_id']);

    // Verificar que el curso existe
    $stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->execute([$curso_id]);
    $curso_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso_actual) {
        throw new Exception('El curso no existe');
    }

    // ========== APLICAR TODAS LAS VALIDACIONES DE CREACIÓN ==========
    // (Reutilizar lógica de validación del método crearCurso)
    
    // Validar código
    $codigo_curso = trim($_POST['codigo_curso']);
    if (strlen($codigo_curso) < 5 || strlen($codigo_curso) > 50) {
        throw new Exception('El código debe tener entre 5 y 50 caracteres');
    }
    
    if (!preg_match('/^[A-Z0-9\-]+$/', $codigo_curso)) {
        throw new Exception('El código solo puede contener mayúsculas, números y guiones');
    }
    
    // Verificar duplicado (excepto el mismo curso)
    $stmt = $conexion->prepare("SELECT id FROM cursos WHERE codigo_curso = ? AND id != ?");
    $stmt->execute([$codigo_curso, $curso_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe otro curso con este código');
    }

    // Validar nombre
    $nombre = trim($_POST['nombre']);
    if (strlen($nombre) < 5 || strlen($nombre) > 200) {
        throw new Exception('El nombre debe tener entre 5 y 200 caracteres');
    }
    
    if (preg_match('/^\d+$/', $nombre)) {
        throw new Exception('El nombre no puede ser solo números');
    }

    // Validar descripción
    $descripcion = trim($_POST['descripcion']);
    if (strlen($descripcion) < 10 || strlen($descripcion) > 500) {
        throw new Exception('La descripción debe tener entre 10 y 500 caracteres');
    }
    
    if (strtolower($nombre) === strtolower($descripcion)) {
        throw new Exception('La descripción no puede ser igual al nombre');
    }

    // Validar asignación
    $asignacion_id = intval($_POST['asignacion_id']);
    if ($asignacion_id <= 0) {
        throw new Exception('Asignación inválida');
    }
    
    $stmt = $conexion->prepare("SELECT id FROM asignaciones_docentes WHERE id = ? AND activo = 1");
    $stmt->execute([$asignacion_id]);
    if (!$stmt->fetch()) {
        throw new Exception('La asignación docente no existe o no está activa');
    }

    // Validar estado
    $estado = $_POST['estado'];
    if (!in_array($estado, ['ACTIVO', 'BORRADOR', 'FINALIZADO'])) {
        throw new Exception('Estado no válido');
    }

    // Validar fechas
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    if (!validateDate($fecha_inicio, 'Y-m-d') || !validateDate($fecha_fin, 'Y-m-d')) {
        throw new Exception('Formato de fecha inválido');
    }
    
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    
    if ($fin <= $inicio) {
        throw new Exception('La fecha de fin debe ser posterior a la de inicio');
    }
    
    $dias = $inicio->diff($fin)->days;
    if ($dias < 7) {
        throw new Exception('El curso debe durar al menos 7 días');
    }
    
    if ($dias > 365) {
        throw new Exception('El curso no puede durar más de 365 días');
    }

    // Validar color
    $color_tema = isset($_POST['color_tema']) ? $_POST['color_tema'] : '#667eea';
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_tema)) {
        throw new Exception('Color inválido');
    }

    // Validar imagen
    $imagen_portada = isset($_POST['imagen_portada']) ? trim($_POST['imagen_portada']) : '';
    if (!empty($imagen_portada)) {
        if (strlen($imagen_portada) > 255) {
            throw new Exception('URL de imagen muy larga');
        }
        
        if (!preg_match('/^\\/[\w\-\\/\.]+\.(jpg|jpeg|png|gif|webp)$/i', $imagen_portada)) {
            throw new Exception('Formato de URL de imagen inválido');
        }
    }

    $inscripcion_libre = isset($_POST['inscripcion_libre']) && $_POST['inscripcion_libre'] == '1';

    // Preparar configuraciones
    $configuraciones = [
        'estado' => $estado,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'color_tema' => $color_tema,
        'imagen_portada' => $imagen_portada,
        'inscripcion_libre' => $inscripcion_libre
    ];

    // ========== TRANSACCIÓN DE ACTUALIZACIÓN ==========
    $conexion->beginTransaction();

    try {
        $stmt = $conexion->prepare("
            UPDATE cursos SET 
                codigo_curso = ?,
                nombre = ?,
                descripcion = ?,
                asignacion_id = ?,
                configuraciones = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $codigo_curso,
            $nombre,
            $descripcion,
            $asignacion_id,
            json_encode($configuraciones, JSON_UNESCAPED_UNICODE),
            $curso_id
        ]);

        // Registrar auditoría
        registrarAuditoria($conexion, 'CURSOS', 'ACTUALIZACION', 'cursos', $curso_id, [
            'cambios' => [
                'codigo_anterior' => $curso_actual['codigo_curso'],
                'codigo_nuevo' => $codigo_curso,
                'nombre_anterior' => $curso_actual['nombre'],
                'nombre_nuevo' => $nombre
            ]
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Curso actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception('Error al actualizar: ' . $e->getMessage());
    }
}

/**
 * ============================================
 * OBTENER DATOS DE UN CURSO
 * ============================================
 */
function obtenerCurso() {
    global $conexion;
    
    if (!isset($_POST['id']) || intval($_POST['id']) <= 0) {
        throw new Exception('ID de curso inválido');
    }

    $id = intval($_POST['id']);

    $stmt = $conexion->prepare("
        SELECT c.*, 
               ad.id as asignacion_id,
               d.nombres as docente_nombres, d.apellidos as docente_apellidos,
               ac.nombre as area_nombre,
               s.grado, s.seccion,
               pa.nombre as periodo_nombre
        FROM cursos c
        INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
        INNER JOIN docentes d ON ad.docente_id = d.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
        WHERE c.id = ?
    ");
    
    $stmt->execute([$id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        throw new Exception('Curso no encontrado');
    }

    // Decodificar JSON
    $curso['configuraciones'] = json_decode($curso['configuraciones'], true) ?: [];
    $curso['estudiantes_inscritos'] = json_decode($curso['estudiantes_inscritos'], true) ?: [];
    $curso['estadisticas'] = json_decode($curso['estadisticas'], true) ?: [];

    return [
        'success' => true,
        'curso' => $curso
    ];
}

/**
 * ============================================
 * OBTENER DETALLES COMPLETOS DEL CURSO
 * ============================================
 */
function obtenerDetallesCompletos() {
    global $conexion;
    
    if (!isset($_POST['id']) || intval($_POST['id']) <= 0) {
        throw new Exception('ID de curso inválido');
    }

    $id = intval($_POST['id']);

    // Obtener datos del curso
    $stmt = $conexion->prepare("
        SELECT c.*, 
               ad.id as asignacion_id,
               d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.foto_url as docente_foto,
               ac.nombre as area_nombre, ac.codigo as area_codigo,
               s.grado, s.seccion, s.codigo as seccion_codigo,
               n.nombre as nivel_nombre,
               pa.nombre as periodo_nombre, pa.anio
        FROM cursos c
        INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
        INNER JOIN docentes d ON ad.docente_id = d.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        INNER JOIN niveles_educativos n ON s.nivel_id = n.id
        INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
        WHERE c.id = ?
    ");
    
    $stmt->execute([$id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        throw new Exception('Curso no encontrado');
    }

    // Decodificar JSON
    $curso['configuraciones'] = json_decode($curso['configuraciones'], true) ?: [];
    $curso['estudiantes_inscritos'] = json_decode($curso['estudiantes_inscritos'], true) ?: [];
    $curso['estadisticas'] = json_decode($curso['estadisticas'], true) ?: [];

    // Obtener unidades del curso
    $stmt = $conexion->prepare("
        SELECT id, titulo, descripcion, orden, configuraciones, fecha_creacion
        FROM unidades
        WHERE curso_id = ?
        ORDER BY orden ASC
    ");
    $stmt->execute([$id]);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decodificar configuraciones de cada unidad
    foreach ($unidades as &$unidad) {
        $unidad['configuraciones'] = json_decode($unidad['configuraciones'], true) ?: [];
    }

    $curso['unidades'] = $unidades;
    $curso['total_unidades'] = count($unidades);

    return [
        'success' => true,
        'curso' => $curso
    ];
}

/**
 * ============================================
 * FUNCIONES AUXILIARES
 * ============================================
 */

/**
 * Validar formato de fecha
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Registrar en auditoría
 */
function registrarAuditoria($conexion, $modulo, $accion, $tabla, $registro_id, $datos) {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, registro_id, datos_cambio, fecha_evento
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        $stmt->execute([
            $usuario_id,
            $modulo,
            $accion,
            $tabla,
            $registro_id,
            json_encode($datos, JSON_UNESCAPED_UNICODE)
        ]);
    } catch (Exception $e) {
        error_log("Error en auditoría: " . $e->getMessage());
    }
}

/**
 * Sanitizar entrada
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>