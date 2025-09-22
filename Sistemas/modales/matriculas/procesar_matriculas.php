<?php
// modales/matriculas/procesar_matriculas.php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            echo json_encode(crearMatricula());
            break;
        case 'obtener':
            echo json_encode(obtenerMatricula());
            break;
        case 'actualizar':
            echo json_encode(actualizarMatricula());
            break;
        case 'detalle':
            echo json_encode(obtenerDetalleMatricula());
            break;
        case 'cambiar_estado':
            echo json_encode(cambiarEstadoMatricula());
            break;
        case 'validar_capacidad':
            echo json_encode(validarCapacidadSeccion());
            break;
        case 'generar_constancia':
            echo json_encode(generarConstanciaMatricula());
            break;
        case 'historial':
            echo json_encode(obtenerHistorialMatricula());
            break;
        case 'historial_completo':
            echo json_encode(obtenerHistorialCompleto());
            break;
        case 'obtener_apoderados':
            echo json_encode(obtenerApoderadosEstudiante());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    error_log("Error en procesar_matriculas.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function crearMatricula() {
    global $conexion;
    
    // Validar datos requeridos
    $estudiante_id = $_POST['estudiante_id'] ?? '';
    $seccion_id = $_POST['seccion_id'] ?? '';
    $tipo_matricula = $_POST['tipo_matricula'] ?? '';
    $fecha_matricula = $_POST['fecha_matricula'] ?? '';
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? '';
    $codigo_matricula = $_POST['codigo_matricula'] ?? '';
    
    if (empty($estudiante_id) || empty($seccion_id) || empty($tipo_matricula) || 
        empty($fecha_matricula) || empty($periodo_academico_id) || empty($codigo_matricula)) {
        return ['success' => false, 'message' => 'Todos los campos requeridos deben ser completados'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Verificar que el estudiante no tenga una matrícula activa en el mismo período
        $stmt_check_estudiante = $conexion->prepare("
            SELECT id FROM matriculas 
            WHERE estudiante_id = ? AND periodo_academico_id = ? AND estado = 'MATRICULADO' AND activo = 1
        ");
        $stmt_check_estudiante->execute([$estudiante_id, $periodo_academico_id]);
        
        if ($stmt_check_estudiante->fetch()) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'El estudiante ya tiene una matrícula activa en este período académico'];
        }
        
        // Verificar que el código de matrícula no exista
        $stmt_check_codigo = $conexion->prepare("SELECT id FROM matriculas WHERE codigo_matricula = ?");
        $stmt_check_codigo->execute([$codigo_matricula]);
        
        if ($stmt_check_codigo->fetch()) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'El código de matrícula ya existe'];
        }
        
        // Verificar capacidad de la sección
        $capacidad_check = verificarCapacidadSeccion($seccion_id);
        if (!$capacidad_check['disponible']) {
            $conexion->rollBack();
            return ['success' => false, 'message' => $capacidad_check['mensaje']];
        }
        
        // Preparar datos adicionales
        $datos_matricula = [
            'observaciones' => $_POST['observaciones'] ?? '',
            'documentos_completos' => isset($_POST['documentos_completos']),
            'generar_constancia' => isset($_POST['generar_constancia'])
        ];
        
        // Insertar nueva matrícula
        $stmt = $conexion->prepare("
            INSERT INTO matriculas (
                codigo_matricula, estudiante_id, seccion_id, periodo_academico_id, 
                fecha_matricula, estado, tipo_matricula, datos_matricula, activo
            ) VALUES (?, ?, ?, ?, ?, 'MATRICULADO', ?, ?, 1)
        ");
        
        $stmt->execute([
            $codigo_matricula,
            $estudiante_id,
            $seccion_id,
            $periodo_academico_id,
            $fecha_matricula,
            $tipo_matricula,
            json_encode($datos_matricula)
        ]);
        
        $matricula_id = $conexion->lastInsertId();
        
        // Registrar en auditoria
        registrarAuditoria('MATRICULA_CREADA', 'matriculas', $matricula_id, 
            ['estudiante_id' => $estudiante_id, 'seccion_id' => $seccion_id]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => 'Matrícula registrada correctamente',
            'matricula_id' => $matricula_id
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al crear matrícula: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al registrar la matrícula'];
    }
}

function obtenerMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT m.*, 
                   e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                   e.codigo_estudiante, e.documento_numero, e.foto_url, e.datos_personales,
                   s.codigo as seccion_codigo, s.grado, s.seccion, s.aula_asignada,
                   ne.nombre as nivel_nombre,
                   pa.nombre as periodo_nombre, pa.anio
            FROM matriculas m
            INNER JOIN estudiantes e ON m.estudiante_id = e.id
            INNER JOIN secciones s ON m.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            INNER JOIN periodos_academicos pa ON m.periodo_academico_id = pa.id
            WHERE m.id = ?
        ");
        
        $stmt->execute([$id]);
        $matricula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$matricula) {
            return ['success' => false, 'message' => 'Matrícula no encontrada'];
        }
        
        return [
            'success' => true,
            'matricula' => $matricula
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener matrícula: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener la matrícula'];
    }
}

function actualizarMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    $seccion_id = $_POST['seccion_id'] ?? '';
    $tipo_matricula = $_POST['tipo_matricula'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $fecha_matricula = $_POST['fecha_matricula'] ?? '';
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($id) || empty($seccion_id) || empty($tipo_matricula) || 
        empty($estado) || empty($fecha_matricula) || empty($periodo_academico_id)) {
        return ['success' => false, 'message' => 'Todos los campos requeridos deben ser completados'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Obtener datos actuales para auditoria
        $stmt_current = $conexion->prepare("SELECT * FROM matriculas WHERE id = ?");
        $stmt_current->execute([$id]);
        $datos_anteriores = $stmt_current->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos_anteriores) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'Matrícula no encontrada'];
        }
        
        // Si se está cambiando de sección, verificar capacidad
        if ($datos_anteriores['seccion_id'] != $seccion_id) {
            $capacidad_check = verificarCapacidadSeccion($seccion_id);
            if (!$capacidad_check['disponible']) {
                $conexion->rollBack();
                return ['success' => false, 'message' => $capacidad_check['mensaje']];
            }
        }
        
        // Preparar datos adicionales
        $datos_matricula = [
            'observaciones' => $_POST['observaciones'] ?? '',
            'documentos_completos' => isset($_POST['documentos_completos'])
        ];
        
        // Actualizar matrícula
        $stmt = $conexion->prepare("
            UPDATE matriculas 
            SET seccion_id = ?, tipo_matricula = ?, estado = ?, fecha_matricula = ?, 
                periodo_academico_id = ?, datos_matricula = ?, activo = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $seccion_id,
            $tipo_matricula,
            $estado,
            $fecha_matricula,
            $periodo_academico_id,
            json_encode($datos_matricula),
            $activo,
            $id
        ]);
        
        // Registrar cambios en auditoria
        $cambios = [];
        if ($datos_anteriores['seccion_id'] != $seccion_id) $cambios[] = 'Sección';
        if ($datos_anteriores['estado'] != $estado) $cambios[] = 'Estado';
        if ($datos_anteriores['tipo_matricula'] != $tipo_matricula) $cambios[] = 'Tipo';
        
        if (!empty($cambios)) {
            registrarAuditoria('MATRICULA_ACTUALIZADA', 'matriculas', $id, 
                ['cambios' => $cambios, 'estado_anterior' => $datos_anteriores['estado'], 'estado_nuevo' => $estado]);
        }
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => 'Matrícula actualizada correctamente'
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al actualizar matrícula: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al actualizar la matrícula'];
    }
}

function obtenerDetalleMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT m.*, 
                   e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                   e.codigo_estudiante, e.documento_numero, e.documento_tipo, e.fecha_nacimiento,
                   e.foto_url, e.datos_personales,
                   s.codigo as seccion_codigo, s.grado, s.seccion, s.aula_asignada, s.capacidad_maxima,
                   ne.nombre as nivel_nombre,
                   pa.nombre as periodo_nombre, pa.anio,
                   COUNT(m2.id) as companeros_seccion
            FROM matriculas m
            INNER JOIN estudiantes e ON m.estudiante_id = e.id
            INNER JOIN secciones s ON m.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            INNER JOIN periodos_academicos pa ON m.periodo_academico_id = pa.id
            LEFT JOIN matriculas m2 ON s.id = m2.seccion_id AND m2.estado = 'MATRICULADO' AND m2.activo = 1
            WHERE m.id = ?
            GROUP BY m.id
        ");
        
        $stmt->execute([$id]);
        $matricula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$matricula) {
            return ['success' => false, 'message' => 'Matrícula no encontrada'];
        }
        
        return [
            'success' => true,
            'matricula' => $matricula
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener detalle de matrícula: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener el detalle de la matrícula'];
    }
}

function cambiarEstadoMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    $nuevo_estado = $_POST['estado'] ?? '';
    $motivo = $_POST['motivo'] ?? '';
    
    if (empty($id) || empty($nuevo_estado)) {
        return ['success' => false, 'message' => 'ID y estado son requeridos'];
    }
    
    $estados_validos = ['MATRICULADO', 'TRASLADADO', 'RETIRADO', 'RESERVADO'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        return ['success' => false, 'message' => 'Estado no válido'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Obtener estado actual
        $stmt_current = $conexion->prepare("SELECT estado, estudiante_id FROM matriculas WHERE id = ?");
        $stmt_current->execute([$id]);
        $datos_actuales = $stmt_current->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos_actuales) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'Matrícula no encontrada'];
        }
        
        $estado_anterior = $datos_actuales['estado'];
        
        // Actualizar estado
        $stmt = $conexion->prepare("UPDATE matriculas SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        
        // Registrar en auditoria
        registrarAuditoria('CAMBIO_ESTADO_MATRICULA', 'matriculas', $id, [
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $nuevo_estado,
            'motivo' => $motivo
        ]);
        
        $conexion->commit();
        
        $mensaje = "Estado cambiado de {$estado_anterior} a {$nuevo_estado}";
        if ($motivo) {
            $mensaje .= " - Motivo: {$motivo}";
        }
        
        return [
            'success' => true,
            'message' => $mensaje
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al cambiar estado de matrícula: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al cambiar el estado de la matrícula'];
    }
}

function validarCapacidadSeccion() {
    $seccion_id = $_POST['seccion_id'] ?? '';
    
    if (empty($seccion_id)) {
        return ['success' => false, 'message' => 'ID de sección requerido'];
    }
    
    $capacidad_check = verificarCapacidadSeccion($seccion_id);
    
    return [
        'success' => true,
        'disponible' => $capacidad_check['disponible'],
        'mensaje' => $capacidad_check['mensaje'],
        'datos' => $capacidad_check['datos']
    ];
}

function verificarCapacidadSeccion($seccion_id) {
    global $conexion;
    
    try {
        $stmt = $conexion->prepare("
            SELECT s.capacidad_maxima,
                   COUNT(m.id) as estudiantes_matriculados,
                   (s.capacidad_maxima - COUNT(m.id)) as vacantes_disponibles
            FROM secciones s
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.estado = 'MATRICULADO' AND m.activo = 1
            WHERE s.id = ?
            GROUP BY s.id
        ");
        
        $stmt->execute([$seccion_id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$datos) {
            return [
                'disponible' => false,
                'mensaje' => 'Sección no encontrada',
                'datos' => null
            ];
        }
        
        $disponible = $datos['vacantes_disponibles'] > 0;
        $mensaje = $disponible 
            ? "Sección disponible ({$datos['vacantes_disponibles']} vacantes)"
            : "Sección completa (0 vacantes disponibles)";
        
        return [
            'disponible' => $disponible,
            'mensaje' => $mensaje,
            'datos' => $datos
        ];
        
    } catch (PDOException $e) {
        error_log("Error al verificar capacidad: " . $e->getMessage());
        return [
            'disponible' => false,
            'mensaje' => 'Error al verificar capacidad de la sección',
            'datos' => null
        ];
    }
}

function generarConstanciaMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        // Obtener datos completos de la matrícula
        $stmt = $conexion->prepare("
            SELECT m.*, 
                   e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                   e.codigo_estudiante, e.documento_numero, e.documento_tipo,
                   s.grado, s.seccion, s.aula_asignada,
                   ne.nombre as nivel_nombre,
                   pa.nombre as periodo_nombre, pa.anio
            FROM matriculas m
            INNER JOIN estudiantes e ON m.estudiante_id = e.id
            INNER JOIN secciones s ON m.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            INNER JOIN periodos_academicos pa ON m.periodo_academico_id = pa.id
            WHERE m.id = ?
        ");
        
        $stmt->execute([$id]);
        $matricula = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$matricula) {
            return ['success' => false, 'message' => 'Matrícula no encontrada'];
        }
        
        // Simular generación de PDF (aquí iría la lógica real de generación)
        $nombre_archivo = "constancia_matricula_{$matricula['codigo_matricula']}.pdf";
        $url_pdf = "/reportes/constancias/{$nombre_archivo}";
        
        // Registrar en auditoria
        registrarAuditoria('CONSTANCIA_GENERADA', 'matriculas', $id, [
            'tipo' => 'CONSTANCIA_MATRICULA',
            'archivo' => $nombre_archivo
        ]);
        
        return [
            'success' => true,
            'message' => 'Constancia generada correctamente',
            'url_pdf' => $url_pdf,
            'nombre_archivo' => $nombre_archivo
        ];
        
    } catch (PDOException $e) {
        error_log("Error al generar constancia: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al generar la constancia'];
    }
}

function obtenerHistorialMatricula() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT a.*, u.nombres, u.apellidos
            FROM auditoria_sistema a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.tabla_afectada = 'matriculas' AND a.registro_id = ?
            ORDER BY a.fecha_evento DESC
            LIMIT 10
        ");
        
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear historial
        $historial_formateado = [];
        foreach ($historial as $evento) {
            $historial_formateado[] = [
                'accion' => formatearAccionAuditoria($evento['accion']),
                'fecha' => $evento['fecha_evento'],
                'usuario' => $evento['nombres'] ? ($evento['nombres'] . ' ' . $evento['apellidos']) : 'Sistema',
                'detalles' => formatearDetallesAuditoria($evento['datos_cambio'])
            ];
        }
        
        return [
            'success' => true,
            'historial' => $historial_formateado
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener historial: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener el historial'];
    }
}

function obtenerHistorialCompleto() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT a.*, u.nombres, u.apellidos
            FROM auditoria_sistema a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.tabla_afectada = 'matriculas' AND a.registro_id = ?
            ORDER BY a.fecha_evento DESC
        ");
        
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear historial completo
        $historial_formateado = [];
        foreach ($historial as $evento) {
            $historial_formateado[] = [
                'accion' => formatearAccionAuditoria($evento['accion']),
                'fecha' => $evento['fecha_evento'],
                'usuario' => $evento['nombres'] ? ($evento['nombres'] . ' ' . $evento['apellidos']) : 'Sistema',
                'detalles' => formatearDetallesAuditoria($evento['datos_cambio'])
            ];
        }
        
        return [
            'success' => true,
            'historial' => $historial_formateado
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener historial completo: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener el historial completo'];
    }
}

function obtenerApoderadosEstudiante() {
    global $conexion;
    
    $estudiante_id = $_POST['estudiante_id'] ?? '';
    
    if (empty($estudiante_id)) {
        return ['success' => false, 'message' => 'ID de estudiante requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT a.*, ea.parentesco, ea.es_principal
            FROM estudiante_apoderados ea
            INNER JOIN apoderados a ON ea.apoderado_id = a.id
            WHERE ea.estudiante_id = ? AND ea.activo = 1
            ORDER BY ea.es_principal DESC, a.apellidos ASC
        ");
        
        $stmt->execute([$estudiante_id]);
        $apoderados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos personales
        foreach ($apoderados as &$apoderado) {
            $datos_personales = json_decode($apoderado['datos_personales'], true);
            $apoderado['telefono'] = $datos_personales['telefono'] ?? '';
            $apoderado['email'] = $datos_personales['email'] ?? '';
        }
        
        return [
            'success' => true,
            'apoderados' => $apoderados
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener apoderados: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener información de apoderados'];
    }
}

// Funciones auxiliares
function registrarAuditoria($accion, $tabla, $registro_id, $datos = []) {
    global $conexion;
    
    try {
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_sistema (usuario_id, modulo, accion, tabla_afectada, registro_id, datos_cambio)
            VALUES (?, 'MATRICULAS', ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['usuario_id'],
            $accion,
            $tabla,
            $registro_id,
            json_encode($datos)
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar auditoría: " . $e->getMessage());
    }
}

function formatearAccionAuditoria($accion) {
    $acciones = [
        'MATRICULA_CREADA' => 'Matrícula registrada',
        'MATRICULA_ACTUALIZADA' => 'Matrícula actualizada',
        'CAMBIO_ESTADO_MATRICULA' => 'Estado modificado',
        'CONSTANCIA_GENERADA' => 'Constancia generada'
    ];
    
    return $acciones[$accion] ?? $accion;
}

function formatearDetallesAuditoria($datos_json) {
    if (!$datos_json) return '';
    
    $datos = json_decode($datos_json, true);
    if (!$datos) return '';
    
    $detalles = [];
    
    if (isset($datos['estado_anterior']) && isset($datos['estado_nuevo'])) {
        $detalles[] = "De {$datos['estado_anterior']} a {$datos['estado_nuevo']}";
    }
    
    if (isset($datos['motivo']) && !empty($datos['motivo'])) {
        $detalles[] = "Motivo: {$datos['motivo']}";
    }
    
    if (isset($datos['cambios']) && is_array($datos['cambios'])) {
        $detalles[] = "Campos modificados: " . implode(', ', $datos['cambios']);
    }
    
    return implode(' | ', $detalles);
}
?>