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
            $response = crearAnuncio();
            break;
            
        case 'actualizar':
            $response = actualizarAnuncio();
            break;
            
        case 'obtener':
            $response = obtenerAnuncio();
            break;
            
        case 'toggle_estado':
            $response = toggleEstadoAnuncio();
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

// ==================== FUNCIÓN CREAR ANUNCIO ====================

function crearAnuncio() {
    global $conexion;
    
    // ===== VALIDACIÓN 1: Campos requeridos =====
    $campos_requeridos = ['curso_id', 'titulo', 'contenido', 'tipo', 'prioridad', 'destinatario', 'fecha_publicacion'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    $conexion->beginTransaction();

    try {
        // ===== VALIDACIÓN 2: Curso existe y está activo =====
        $stmt = $conexion->prepare("
            SELECT c.id, c.nombre, c.configuraciones 
            FROM cursos c 
            WHERE c.id = ?
        ");
        $stmt->execute([$_POST['curso_id']]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$curso) {
            throw new Exception('El curso seleccionado no existe');
        }
        
        $config_curso = json_decode($curso['configuraciones'], true);
        if (isset($config_curso['estado']) && $config_curso['estado'] !== 'ACTIVO') {
            throw new Exception('El curso seleccionado no está activo');
        }

        // ===== VALIDACIÓN 3: Título - longitud y formato =====
        $titulo = trim($_POST['titulo']);
        if (strlen($titulo) < 5) {
            throw new Exception('El título debe tener al menos 5 caracteres');
        }
        if (strlen($titulo) > 255) {
            throw new Exception('El título no puede superar los 255 caracteres');
        }
        
        // ===== VALIDACIÓN 4: Título - solo espacios =====
        if (preg_replace('/\s+/', '', $titulo) === '') {
            throw new Exception('El título no puede contener solo espacios');
        }
        
        // ===== VALIDACIÓN 5: Título - caracteres especiales =====
        if (preg_match('/[<>{}[\]\\\\\/]/', $titulo)) {
            throw new Exception('El título contiene caracteres no permitidos');
        }
        
        // ===== VALIDACIÓN 6: Título - mínimo de palabras =====
        $palabras_titulo = preg_split('/\s+/', trim($titulo));
        if (count($palabras_titulo) < 2) {
            throw new Exception('El título debe contener al menos 2 palabras');
        }
        
        // ===== VALIDACIÓN 7: Título - palabras muy largas =====
        foreach ($palabras_titulo as $palabra) {
            if (strlen($palabra) > 50) {
                throw new Exception('El título contiene palabras excesivamente largas');
            }
        }
        
        // ===== VALIDACIÓN 8: Título - caracteres de control =====
        if (preg_match('/[\x00-\x1F\x7F]/', $titulo)) {
            throw new Exception('El título contiene caracteres no válidos');
        }

        // ===== VALIDACIÓN 9: Contenido - longitud =====
        $contenido = trim($_POST['contenido']);
        if (strlen($contenido) < 10) {
            throw new Exception('El contenido debe tener al menos 10 caracteres');
        }
        if (strlen($contenido) > 5000) {
            throw new Exception('El contenido no puede superar los 5000 caracteres');
        }
        
        // ===== VALIDACIÓN 10: Contenido - solo espacios =====
        if (preg_replace('/\s+/', '', $contenido) === '') {
            throw new Exception('El contenido no puede contener solo espacios');
        }
        
        // ===== VALIDACIÓN 11: Contenido - mínimo de palabras =====
        $palabras_contenido = preg_split('/\s+/', trim($contenido));
        if (count($palabras_contenido) < 3) {
            throw new Exception('El contenido debe contener al menos 3 palabras');
        }
        
        // ===== VALIDACIÓN 12: Contenido - caracteres de control =====
        if (preg_match('/[\x00-\x1F\x7F]/', $contenido)) {
            throw new Exception('El contenido contiene caracteres no válidos');
        }

        // ===== VALIDACIÓN 13: Tipo - valores válidos =====
        $tipos_validos = ['INFORMATIVO', 'RECORDATORIO', 'URGENTE', 'EVENTO'];
        $tipo = strtoupper(trim($_POST['tipo']));
        if (!in_array($tipo, $tipos_validos)) {
            throw new Exception('El tipo de anuncio no es válido');
        }

        // ===== VALIDACIÓN 14: Prioridad - valores válidos =====
        $prioridades_validas = ['BAJA', 'NORMAL', 'ALTA'];
        $prioridad = strtoupper(trim($_POST['prioridad']));
        if (!in_array($prioridad, $prioridades_validas)) {
            throw new Exception('La prioridad no es válida');
        }

        // ===== VALIDACIÓN 15: Destinatario - valores válidos =====
        $destinatarios_validos = ['ESTUDIANTES', 'APODERADOS', 'TODOS'];
        $destinatario = strtoupper(trim($_POST['destinatario']));
        if (!in_array($destinatario, $destinatarios_validos)) {
            throw new Exception('El destinatario no es válido');
        }

        // ===== VALIDACIÓN 16: Coherencia tipo-prioridad =====
        if ($tipo === 'URGENTE' && $prioridad !== 'ALTA') {
            throw new Exception('Los anuncios URGENTES deben tener prioridad ALTA');
        }

        // ===== VALIDACIÓN 17: Fecha de publicación - formato =====
        $fecha_publicacion = $_POST['fecha_publicacion'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha_publicacion)) {
            throw new Exception('Formato de fecha de publicación inválido');
        }
        
        // ===== VALIDACIÓN 18: Fecha de publicación - no pasada =====
        $fecha_pub_dt = new DateTime($fecha_publicacion);
        $ahora = new DateTime();
        $ahora->modify('-5 minutes'); // Tolerancia de 5 minutos
        
        if ($fecha_pub_dt < $ahora) {
            throw new Exception('La fecha de publicación no puede ser pasada');
        }
        
        // ===== VALIDACIÓN 19: Fecha de publicación - no muy futura =====
        $un_anio = new DateTime();
        $un_anio->modify('+1 year');
        
        if ($fecha_pub_dt > $un_anio) {
            throw new Exception('La fecha de publicación no puede ser mayor a 1 año en el futuro');
        }

        // ===== VALIDACIÓN 20-24: Fecha de expiración (si existe) =====
        $fecha_expiracion = null;
        if (!empty($_POST['fecha_expiracion'])) {
            $fecha_expiracion = $_POST['fecha_expiracion'];
            
            // Validación 20: Formato
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha_expiracion)) {
                throw new Exception('Formato de fecha de expiración inválido');
            }
            
            $fecha_exp_dt = new DateTime($fecha_expiracion);
            
            // Validación 21: Posterior a publicación
            if ($fecha_exp_dt <= $fecha_pub_dt) {
                throw new Exception('La fecha de expiración debe ser posterior a la fecha de publicación');
            }
            
            // Validación 22: Diferencia mínima de 1 hora
            $diferencia = $fecha_exp_dt->getTimestamp() - $fecha_pub_dt->getTimestamp();
            if ($diferencia < 3600) { // 3600 segundos = 1 hora
                throw new Exception('La expiración debe ser al menos 1 hora después de la publicación');
            }
            
            // Validación 23: No muy lejana (máximo 2 años desde publicación)
            $dos_anios = clone $fecha_pub_dt;
            $dos_anios->modify('+2 years');
            
            if ($fecha_exp_dt > $dos_anios) {
                throw new Exception('La fecha de expiración no puede ser mayor a 2 años desde la publicación');
            }
            
            // Validación 24: Convertir a formato SQL
            $fecha_expiracion = $fecha_exp_dt->format('Y-m-d H:i:s');
        }

        // ===== VALIDACIÓN 25: Duplicados de título en el mismo curso =====
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as total 
            FROM anuncios 
            WHERE curso_id = ? 
            AND LOWER(titulo) = LOWER(?) 
            AND activo = 1
        ");
        $stmt->execute([$_POST['curso_id'], $titulo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            throw new Exception('Ya existe un anuncio con el mismo título en este curso');
        }

        // ===== VALIDACIÓN 26: Usuario existe =====
        // Simulamos que el usuario es el ID 1 (Director) - en producción usar sesión
        $usuario_creacion = 1;
        
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
        $stmt->execute([$usuario_creacion]);
        if (!$stmt->fetch()) {
            throw new Exception('Usuario no válido para crear anuncios');
        }

        // ===== VALIDACIÓN 27: Límite de anuncios activos por curso =====
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as total 
            FROM anuncios 
            WHERE curso_id = ? 
            AND activo = 1
        ");
        $stmt->execute([$_POST['curso_id']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] >= 50) {
            throw new Exception('El curso ha alcanzado el límite de 50 anuncios activos');
        }

        // ===== VALIDACIÓN 28: Sanitización final del contenido =====
        $contenido = strip_tags($contenido, '<br><p><b><i><u><strong><em>');

        // ===== PREPARAR CONFIGURACIONES =====
        $configuraciones = [
            'tipo' => $tipo,
            'prioridad' => $prioridad,
            'destinatario' => $destinatario,
            'fecha_expiracion' => $fecha_expiracion
        ];

        // ===== CONVERTIR FECHA DE PUBLICACIÓN A FORMATO SQL =====
        $fecha_pub_sql = $fecha_pub_dt->format('Y-m-d H:i:s');

        // ===== INSERTAR ANUNCIO =====
        $stmt = $conexion->prepare("
            INSERT INTO anuncios (
                curso_id, titulo, contenido, configuraciones,
                fecha_publicacion, usuario_creacion, activo
            ) VALUES (?, ?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            $_POST['curso_id'],
            $titulo,
            $contenido,
            json_encode($configuraciones),
            $fecha_pub_sql,
            $usuario_creacion
        ]);

        $anuncio_id = $conexion->lastInsertId();

        // ===== REGISTRO DE AUDITORÍA =====
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, 
                registro_id, datos_cambio, fecha_evento
            ) VALUES (?, 'ANUNCIOS', 'CREAR', 'anuncios', ?, ?, NOW())
        ");
        
        $stmt->execute([
            $usuario_creacion,
            $anuncio_id,
            json_encode([
                'curso_id' => $_POST['curso_id'],
                'titulo' => $titulo,
                'tipo' => $tipo,
                'prioridad' => $prioridad
            ])
        ]);

        // ===== CREAR NOTIFICACIONES AUTOMÁTICAS =====
        crearNotificacionesAnuncio($conexion, $anuncio_id, $_POST['curso_id'], $titulo, $destinatario);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Anuncio creado exitosamente',
            'anuncio_id' => $anuncio_id
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN ACTUALIZAR ANUNCIO ====================

function actualizarAnuncio() {
    global $conexion;
    
    // ===== VALIDACIÓN 1: ID del anuncio =====
    if (!isset($_POST['anuncio_id']) || empty($_POST['anuncio_id'])) {
        throw new Exception('ID del anuncio no especificado');
    }

    $anuncio_id = (int)$_POST['anuncio_id'];

    $conexion->beginTransaction();

    try {
        // ===== VALIDACIÓN 2: Anuncio existe =====
        $stmt = $conexion->prepare("SELECT * FROM anuncios WHERE id = ?");
        $stmt->execute([$anuncio_id]);
        $anuncio_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$anuncio_actual) {
            throw new Exception('Anuncio no encontrado');
        }

        // ===== VALIDACIÓN 3: Campos requeridos =====
        $campos_requeridos = ['curso_id', 'titulo', 'contenido', 'tipo', 'prioridad', 'destinatario', 'fecha_publicacion'];
        
        foreach ($campos_requeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo $campo es requerido");
            }
        }

        // ===== VALIDACIÓN 4: Curso existe y está activo =====
        $stmt = $conexion->prepare("
            SELECT c.id, c.nombre, c.configuraciones 
            FROM cursos c 
            WHERE c.id = ?
        ");
        $stmt->execute([$_POST['curso_id']]);
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$curso) {
            throw new Exception('El curso seleccionado no existe');
        }

        // ===== VALIDACIONES 5-12: Título (mismas que en crear) =====
        $titulo = trim($_POST['titulo']);
        if (strlen($titulo) < 5 || strlen($titulo) > 255) {
            throw new Exception('El título debe tener entre 5 y 255 caracteres');
        }
        
        if (preg_replace('/\s+/', '', $titulo) === '') {
            throw new Exception('El título no puede contener solo espacios');
        }
        
        if (preg_match('/[<>{}[\]\\\\\/]/', $titulo)) {
            throw new Exception('El título contiene caracteres no permitidos');
        }
        
        $palabras_titulo = preg_split('/\s+/', trim($titulo));
        if (count($palabras_titulo) < 2) {
            throw new Exception('El título debe contener al menos 2 palabras');
        }

        // ===== VALIDACIONES 13-16: Contenido =====
        $contenido = trim($_POST['contenido']);
        if (strlen($contenido) < 10 || strlen($contenido) > 5000) {
            throw new Exception('El contenido debe tener entre 10 y 5000 caracteres');
        }
        
        if (preg_replace('/\s+/', '', $contenido) === '') {
            throw new Exception('El contenido no puede contener solo espacios');
        }
        
        $palabras_contenido = preg_split('/\s+/', trim($contenido));
        if (count($palabras_contenido) < 3) {
            throw new Exception('El contenido debe contener al menos 3 palabras');
        }

        // ===== VALIDACIONES 17-19: Tipo, Prioridad, Destinatario =====
        $tipos_validos = ['INFORMATIVO', 'RECORDATORIO', 'URGENTE', 'EVENTO'];
        $tipo = strtoupper(trim($_POST['tipo']));
        if (!in_array($tipo, $tipos_validos)) {
            throw new Exception('El tipo de anuncio no es válido');
        }

        $prioridades_validas = ['BAJA', 'NORMAL', 'ALTA'];
        $prioridad = strtoupper(trim($_POST['prioridad']));
        if (!in_array($prioridad, $prioridades_validas)) {
            throw new Exception('La prioridad no es válida');
        }

        $destinatarios_validos = ['ESTUDIANTES', 'APODERADOS', 'TODOS'];
        $destinatario = strtoupper(trim($_POST['destinatario']));
        if (!in_array($destinatario, $destinatarios_validos)) {
            throw new Exception('El destinatario no es válido');
        }

        // ===== VALIDACIÓN 20: Coherencia tipo-prioridad =====
        if ($tipo === 'URGENTE' && $prioridad !== 'ALTA') {
            throw new Exception('Los anuncios URGENTES deben tener prioridad ALTA');
        }

        // ===== VALIDACIONES 21-24: Fechas =====
        $fecha_publicacion = $_POST['fecha_publicacion'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha_publicacion)) {
            throw new Exception('Formato de fecha de publicación inválido');
        }
        
        $fecha_pub_dt = new DateTime($fecha_publicacion);
        $fecha_pub_sql = $fecha_pub_dt->format('Y-m-d H:i:s');

        $fecha_expiracion = null;
        if (!empty($_POST['fecha_expiracion'])) {
            $fecha_expiracion = $_POST['fecha_expiracion'];
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha_expiracion)) {
                throw new Exception('Formato de fecha de expiración inválido');
            }
            
            $fecha_exp_dt = new DateTime($fecha_expiracion);
            
            if ($fecha_exp_dt <= $fecha_pub_dt) {
                throw new Exception('La fecha de expiración debe ser posterior a la publicación');
            }
            
            $diferencia = $fecha_exp_dt->getTimestamp() - $fecha_pub_dt->getTimestamp();
            if ($diferencia < 3600) {
                throw new Exception('La expiración debe ser al menos 1 hora después de la publicación');
            }
            
            $fecha_expiracion = $fecha_exp_dt->format('Y-m-d H:i:s');
        }

        // ===== VALIDACIÓN 25: Duplicados (excluyendo el actual) =====
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as total 
            FROM anuncios 
            WHERE curso_id = ? 
            AND LOWER(titulo) = LOWER(?) 
            AND id != ?
            AND activo = 1
        ");
        $stmt->execute([$_POST['curso_id'], $titulo, $anuncio_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            throw new Exception('Ya existe otro anuncio con el mismo título en este curso');
        }

        // ===== SANITIZACIÓN =====
        $contenido = strip_tags($contenido, '<br><p><b><i><u><strong><em>');

        // ===== PREPARAR CONFIGURACIONES =====
        $configuraciones = [
            'tipo' => $tipo,
            'prioridad' => $prioridad,
            'destinatario' => $destinatario,
            'fecha_expiracion' => $fecha_expiracion
        ];

        // ===== ACTUALIZAR ANUNCIO =====
        $stmt = $conexion->prepare("
            UPDATE anuncios SET 
                curso_id = ?,
                titulo = ?,
                contenido = ?,
                configuraciones = ?,
                fecha_publicacion = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['curso_id'],
            $titulo,
            $contenido,
            json_encode($configuraciones),
            $fecha_pub_sql,
            $anuncio_id
        ]);

        // ===== REGISTRO DE AUDITORÍA =====
        $usuario_actualizacion = 1; // En producción usar sesión
        
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, 
                registro_id, datos_cambio, fecha_evento
            ) VALUES (?, 'ANUNCIOS', 'ACTUALIZAR', 'anuncios', ?, ?, NOW())
        ");
        
        $stmt->execute([
            $usuario_actualizacion,
            $anuncio_id,
            json_encode([
                'titulo_anterior' => $anuncio_actual['titulo'],
                'titulo_nuevo' => $titulo,
                'tipo_anterior' => json_decode($anuncio_actual['configuraciones'], true)['tipo'] ?? '',
                'tipo_nuevo' => $tipo
            ])
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Anuncio actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN OBTENER ANUNCIO ====================

function obtenerAnuncio() {
    global $conexion;
    
    // ===== VALIDACIÓN: ID proporcionado =====
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID del anuncio no especificado');
    }

    $id = (int)$_POST['id'];

    // ===== CONSULTAR ANUNCIO =====
    $stmt = $conexion->prepare("
        SELECT a.*, 
               c.nombre as curso_nombre,
               u.nombres as creador_nombres,
               u.apellidos as creador_apellidos
        FROM anuncios a
        INNER JOIN cursos c ON a.curso_id = c.id
        INNER JOIN usuarios u ON a.usuario_creacion = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio) {
        throw new Exception('Anuncio no encontrado');
    }

    // ===== DECODIFICAR JSON =====
    $anuncio['configuraciones'] = json_decode($anuncio['configuraciones'], true);

    return [
        'success' => true,
        'anuncio' => $anuncio
    ];
}

// ==================== FUNCIÓN TOGGLE ESTADO ====================

function toggleEstadoAnuncio() {
    global $conexion;
    
    // ===== VALIDACIÓN: Parámetros =====
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $estado = $_POST['estado'] === 'true' ? 1 : 0;

    $conexion->beginTransaction();

    try {
        // ===== VALIDACIÓN: Anuncio existe =====
        $stmt = $conexion->prepare("SELECT id, titulo FROM anuncios WHERE id = ?");
        $stmt->execute([$id]);
        $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$anuncio) {
            throw new Exception('Anuncio no encontrado');
        }

        // ===== ACTUALIZAR ESTADO =====
        $stmt = $conexion->prepare("UPDATE anuncios SET activo = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);

        // ===== AUDITORÍA =====
        $usuario = 1; // En producción usar sesión
        $accion = $estado ? 'ACTIVAR' : 'DESACTIVAR';
        
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (
                usuario_id, modulo, accion, tabla_afectada, 
                registro_id, datos_cambio, fecha_evento
            ) VALUES (?, 'ANUNCIOS', ?, 'anuncios', ?, ?, NOW())
        ");
        
        $stmt->execute([
            $usuario,
            $accion,
            $id,
            json_encode(['titulo' => $anuncio['titulo']])
        ]);

        $conexion->commit();

        $mensaje = $estado ? 'activado' : 'desactivado';
        return [
            'success' => true,
            'message' => "Anuncio $mensaje exitosamente"
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN AUXILIAR: CREAR NOTIFICACIONES ====================

function crearNotificacionesAnuncio($conexion, $anuncio_id, $curso_id, $titulo, $destinatario) {
    try {
        // Obtener estudiantes del curso según destinatario
        $usuarios_notificar = [];
        
        if ($destinatario === 'ESTUDIANTES' || $destinatario === 'TODOS') {
            // Obtener estudiantes inscritos en el curso
            $stmt = $conexion->prepare("
                SELECT JSON_EXTRACT(estudiantes_inscritos, '$[*].estudiante_id') as estudiante_ids
                FROM cursos
                WHERE id = ?
            ");
            $stmt->execute([$curso_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['estudiante_ids']) {
                $estudiante_ids = json_decode($resultado['estudiante_ids'], true);
                
                // Obtener usuario_id de cada estudiante
                if (!empty($estudiante_ids)) {
                    $placeholders = implode(',', array_fill(0, count($estudiante_ids), '?'));
                    $stmt = $conexion->prepare("
                        SELECT usuario_id FROM estudiantes 
                        WHERE id IN ($placeholders) AND usuario_id IS NOT NULL
                    ");
                    $stmt->execute($estudiante_ids);
                    $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $usuarios_notificar = array_merge($usuarios_notificar, $usuarios);
                }
            }
        }
        
        if ($destinatario === 'APODERADOS' || $destinatario === 'TODOS') {
            // Obtener apoderados de estudiantes del curso
            $stmt = $conexion->prepare("
                SELECT DISTINCT a.usuario_id
                FROM estudiante_apoderados ea
                INNER JOIN apoderados a ON ea.apoderado_id = a.id
                INNER JOIN estudiantes e ON ea.estudiante_id = e.id
                INNER JOIN matriculas m ON e.id = m.estudiante_id
                INNER JOIN cursos c ON m.seccion_id IN (
                    SELECT seccion_id FROM asignaciones_docentes WHERE id = c.asignacion_id
                )
                WHERE c.id = ? AND a.usuario_id IS NOT NULL AND ea.activo = 1
            ");
            $stmt->execute([$curso_id]);
            $apoderados = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $usuarios_notificar = array_merge($usuarios_notificar, $apoderados);
        }
        
        // Crear notificaciones
        $usuarios_notificar = array_unique($usuarios_notificar);
        
        foreach ($usuarios_notificar as $usuario_id) {
            $stmt = $conexion->prepare("
                INSERT INTO notificaciones_sistema (
                    usuario_id, titulo, mensaje, tipo, origen, configuracion
                ) VALUES (?, ?, ?, 'ANUNCIO', ?, ?)
            ");
            
            $stmt->execute([
                $usuario_id,
                'Nuevo Anuncio: ' . $titulo,
                'Se ha publicado un nuevo anuncio en tu curso',
                json_encode(['id' => $anuncio_id, 'tipo' => 'anuncio']),
                json_encode(['leida' => false, 'activa' => true])
            ]);
        }
        
    } catch (Exception $e) {
        // No lanzar excepción, solo registrar error
        error_log("Error al crear notificaciones: " . $e->getMessage());
    }
}
?>  