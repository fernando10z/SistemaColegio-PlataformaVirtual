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
        case 'trasladar':
            $response = trasladarEstudiante();
            break;
            
        case 'traslado_manual':
            $response = ejecutarTrasladoManual();
            break;
            
        case 'obtener_secciones':
            $response = obtenerSecciones();
            break;
            
        case 'obtener_estudiantes':
            $response = obtenerEstudiantes();
            break;
            
        case 'obtener_info_estudiante':
            $response = obtenerInfoEstudiante();
            break;
            
        case 'obtener_historial':
            $response = obtenerHistorial();
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

function trasladarEstudiante() {
    global $conexion;
    
    if (!isset($_POST['matricula_id']) || !isset($_POST['seccion_destino'])) {
        throw new Exception('Datos incompletos para el traslado');
    }

    $matriculaId = (int)$_POST['matricula_id'];
    $seccionDestinoId = (int)$_POST['seccion_destino'];

    $conexion->beginTransaction();

    try {
        // Verificar que la matrícula existe
        $stmt = $conexion->prepare("
            SELECT m.*, e.nombres, e.apellidos, e.codigo_estudiante,
                   s.grado as grado_origen, s.seccion as seccion_origen,
                   s.capacidad_maxima as capacidad_origen
            FROM matriculas m
            INNER JOIN estudiantes e ON m.estudiante_id = e.id
            INNER JOIN secciones s ON m.seccion_id = s.id
            WHERE m.id = ? AND m.estado = 'MATRICULADO' AND m.activo = 1
        ");
        $stmt->execute([$matriculaId]);
        $matricula = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$matricula) {
            throw new Exception('Matrícula no encontrada o inactiva');
        }

        // Verificar capacidad de la sección destino
        $stmt = $conexion->prepare("
            SELECT s.*, 
                   COUNT(m.id) as estudiantes_actuales
            FROM secciones s
            LEFT JOIN matriculas m ON s.id = m.seccion_id 
                                    AND m.estado = 'MATRICULADO' 
                                    AND m.activo = 1
            WHERE s.id = ? AND s.activo = 1
            GROUP BY s.id
        ");
        $stmt->execute([$seccionDestinoId]);
        $seccionDestino = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seccionDestino) {
            throw new Exception('Sección destino no encontrada');
        }

        if ($seccionDestino['estudiantes_actuales'] >= $seccionDestino['capacidad_maxima']) {
            throw new Exception('La sección destino está completa');
        }

        // Verificar que no sea la misma sección
        if ($matricula['seccion_id'] == $seccionDestinoId) {
            throw new Exception('El estudiante ya está en esa sección');
        }

        // Actualizar la matrícula
        $stmt = $conexion->prepare("
            UPDATE matriculas 
            SET seccion_id = ?, 
                fecha_actualizacion = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$seccionDestinoId, $matriculaId]);

        // Registrar en auditoría si existe la tabla
        try {
            $stmt = $conexion->prepare("
                INSERT INTO auditoria_sistema (usuario_id, modulo, accion, tabla_afectada, registro_id, datos_cambio)
                VALUES (?, 'TRASLADOS', 'TRASLADO_ESTUDIANTE', 'matriculas', ?, ?)
            ");
            $datosAuditoria = json_encode([
                'estudiante' => $matricula['nombres'] . ' ' . $matricula['apellidos'],
                'codigo_estudiante' => $matricula['codigo_estudiante'],
                'seccion_origen' => $matricula['grado_origen'] . '-' . $matricula['seccion_origen'],
                'seccion_destino' => $seccionDestino['grado'] . '-' . $seccionDestino['seccion']
            ]);
            $stmt->execute([1, $matriculaId, $datosAuditoria]); // Usuario 1 por defecto
        } catch (Exception $e) {
            // Si falla la auditoría, continuar (no es crítico)
        }

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Estudiante trasladado exitosamente de ' . 
                        $matricula['grado_origen'] . '-' . $matricula['seccion_origen'] . 
                        ' a ' . $seccionDestino['grado'] . '-' . $seccionDestino['seccion']
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function ejecutarTrasladoManual() {
    global $conexion;
    
    if (!isset($_POST['estudiante_id']) || !isset($_POST['seccion_destino'])) {
        throw new Exception('Datos incompletos para el traslado manual');
    }

    $matriculaId = (int)$_POST['estudiante_id'];
    $seccionDestinoId = (int)$_POST['seccion_destino'];
    $motivo = trim($_POST['motivo_traslado'] ?? '');

    // Reutilizar la función de traslado
    $_POST['matricula_id'] = $matriculaId;
    $resultado = trasladarEstudiante();
    
    if ($resultado['success'] && !empty($motivo)) {
        // Guardar el motivo en algún lugar si es necesario
        try {
            $stmt = $conexion->prepare("
                INSERT INTO auditoria_sistema (usuario_id, modulo, accion, tabla_afectada, registro_id, datos_cambio)
                VALUES (?, 'TRASLADOS', 'MOTIVO_TRASLADO', 'matriculas', ?, ?)
            ");
            $stmt->execute([1, $matriculaId, json_encode(['motivo' => $motivo])]);
        } catch (Exception $e) {
            // No es crítico si falla
        }
    }
    
    return $resultado;
}

function obtenerSecciones() {
    global $conexion;
    
    if (!isset($_POST['nivel_id'])) {
        throw new Exception('Nivel no especificado');
    }

    $nivelId = (int)$_POST['nivel_id'];

    $stmt = $conexion->prepare("
        SELECT s.id, s.grado, s.seccion, s.capacidad_maxima,
               COUNT(m.id) as estudiantes_actuales
        FROM secciones s
        LEFT JOIN matriculas m ON s.id = m.seccion_id 
                                AND m.estado = 'MATRICULADO' 
                                AND m.activo = 1
        WHERE s.nivel_id = ? AND s.activo = 1
        GROUP BY s.id
        ORDER BY s.grado ASC, s.seccion ASC
    ");
    $stmt->execute([$nivelId]);
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'secciones' => $secciones
    ];
}

function obtenerEstudiantes() {
    global $conexion;
    
    if (!isset($_POST['seccion_id'])) {
        throw new Exception('Sección no especificada');
    }

    $seccionId = (int)$_POST['seccion_id'];

    $stmt = $conexion->prepare("
        SELECT m.id as matricula_id, e.id as estudiante_id,
               e.nombres, e.apellidos, e.codigo_estudiante
        FROM matriculas m
        INNER JOIN estudiantes e ON m.estudiante_id = e.id
        WHERE m.seccion_id = ? AND m.estado = 'MATRICULADO' AND m.activo = 1
        ORDER BY e.apellidos ASC, e.nombres ASC
    ");
    $stmt->execute([$seccionId]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'estudiantes' => $estudiantes
    ];
}

function obtenerInfoEstudiante() {
    global $conexion;
    
    if (!isset($_POST['matricula_id'])) {
        throw new Exception('Matrícula no especificada');
    }

    $matriculaId = (int)$_POST['matricula_id'];

    $stmt = $conexion->prepare("
        SELECT e.*, m.fecha_matricula,
               s.grado, s.seccion,
               ne.nombre as nivel_nombre
        FROM matriculas m
        INNER JOIN estudiantes e ON m.estudiante_id = e.id
        INNER JOIN secciones s ON m.seccion_id = s.id
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        WHERE m.id = ? AND m.estado = 'MATRICULADO' AND m.activo = 1
    ");
    $stmt->execute([$matriculaId]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        throw new Exception('Estudiante no encontrado');
    }

    // Formatear fecha de nacimiento
    $estudiante['fecha_nacimiento'] = date('d/m/Y', strtotime($estudiante['fecha_nacimiento']));

    return [
        'success' => true,
        'estudiante' => $estudiante
    ];
}

function obtenerHistorial() {
    global $conexion;
    
    $fechaDesde = $_POST['fecha_desde'] ?? date('Y-m-01');
    $fechaHasta = $_POST['fecha_hasta'] ?? date('Y-m-d');
    $busqueda = trim($_POST['busqueda'] ?? '');

    $sql = "
        SELECT 
            DATE_FORMAT(m.fecha_actualizacion, '%d/%m/%Y %H:%i') as fecha_traslado,
            e.nombres, e.apellidos, e.codigo_estudiante,
            CONCAT(e.apellidos, ', ', e.nombres) as estudiante_nombre,
            'Traslado de sección' as motivo,
            'Sistema' as usuario_nombre
        FROM matriculas m
        INNER JOIN estudiantes e ON m.estudiante_id = e.id
        WHERE DATE(m.fecha_actualizacion) BETWEEN ? AND ?
    ";

    $params = [$fechaDesde, $fechaHasta];

    if (!empty($busqueda)) {
        $sql .= " AND (e.nombres LIKE ? OR e.apellidos LIKE ? OR e.codigo_estudiante LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params[] = $busquedaParam;
        $params[] = $busquedaParam;
        $params[] = $busquedaParam;
    }

    $sql .= " ORDER BY m.fecha_actualizacion DESC LIMIT 100";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agregar información de secciones origen/destino (simulado por simplicidad)
    foreach ($historial as &$traslado) {
        $traslado['seccion_origen'] = 'Origen';
        $traslado['seccion_destino'] = 'Destino';
    }

    return [
        'success' => true,
        'historial' => $historial
    ];
}
?>