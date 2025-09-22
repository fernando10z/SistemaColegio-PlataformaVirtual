<?php
// modales/asignaciones/procesar_asignaciones.php
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

if (!isset($_POST['accion'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

$accion = $_POST['accion'];

try {
    switch ($accion) {
        case 'crear':
            crearAsignacion();
            break;
        case 'obtener':
            obtenerAsignacion();
            break;
        case 'actualizar':
            actualizarAsignacion();
            break;
        case 'eliminar':
            eliminarAsignacion();
            break;
        case 'toggle_tutor':
            toggleTutor();
            break;
        case 'detalle':
            obtenerDetalleAsignacion();
            break;
        case 'detectar_conflictos':
            detectarConflictos();
            break;
        case 'obtener_secciones':
            obtenerSecciones();
            break;
        case 'verificar_tutor':
            verificarTutor();
            break;
        case 'obtener_horarios':
            obtenerHorarios();
            break;
        case 'actualizar_horarios':
            actualizarHorarios();
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function crearAsignacion() {
    global $conexion;
    
    // Validar datos requeridos
    $docente_id = $_POST['docente_id'] ?? null;
    $seccion_id = $_POST['seccion_id'] ?? null;
    $area_id = $_POST['area_id'] ?? null;
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? null;
    $horas_semanales = $_POST['horas_semanales'] ?? 2;
    $es_tutor = isset($_POST['es_tutor']) ? 1 : 0;
    
    if (!$docente_id || !$seccion_id || !$area_id || !$periodo_academico_id) {
        throw new Exception('Todos los campos requeridos deben ser completados');
    }
    
    // Validar que no exista la misma asignación
    $stmt_check = $conexion->prepare("
        SELECT id FROM asignaciones_docentes 
        WHERE docente_id = ? AND seccion_id = ? AND area_id = ? AND periodo_academico_id = ? AND activo = 1
    ");
    $stmt_check->execute([$docente_id, $seccion_id, $area_id, $periodo_academico_id]);
    
    if ($stmt_check->fetch()) {
        throw new Exception('Ya existe una asignación igual para este docente, sección y área');
    }
    
    // Si es tutor, remover tutorías existentes en la sección
    if ($es_tutor) {
        $stmt_update_tutor = $conexion->prepare("
            UPDATE asignaciones_docentes SET es_tutor = 0 
            WHERE seccion_id = ? AND periodo_academico_id = ? AND activo = 1
        ");
        $stmt_update_tutor->execute([$seccion_id, $periodo_academico_id]);
    }
    
    // Procesar horarios
    $horarios = [];
    if (isset($_POST['horarios']) && is_array($_POST['horarios'])) {
        foreach ($_POST['horarios'] as $horario) {
            if (!empty($horario['dia']) && !empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
                $horarios[] = [
                    'dia' => intval($horario['dia']),
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'aula' => $horario['aula'] ?? ''
                ];
            }
        }
    }
    
    // Insertar asignación
    $stmt = $conexion->prepare("
        INSERT INTO asignaciones_docentes (
            docente_id, seccion_id, area_id, periodo_academico_id, 
            es_tutor, horas_semanales, horarios, activo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    $horarios_json = !empty($horarios) ? json_encode($horarios) : null;
    
    $stmt->execute([
        $docente_id, $seccion_id, $area_id, $periodo_academico_id,
        $es_tutor, $horas_semanales, $horarios_json
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asignación docente creada exitosamente',
        'id' => $conexion->lastInsertId()
    ]);
}

function obtenerAsignacion() {
    global $conexion;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de asignación requerido');
    }
    
    $stmt = $conexion->prepare("
        SELECT a.*, 
               d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.codigo_docente,
               s.grado, s.seccion, s.codigo as seccion_codigo, s.aula_asignada,
               ne.nombre as nivel_nombre,
               ac.nombre as area_nombre, ac.codigo as area_codigo,
               pa.nombre as periodo_nombre, pa.anio
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        INNER JOIN secciones s ON a.seccion_id = s.id
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        INNER JOIN areas_curriculares ac ON a.area_id = ac.id
        INNER JOIN periodos_academicos pa ON a.periodo_academico_id = pa.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$id]);
    $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignacion) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Decodificar horarios
    $asignacion['horarios'] = $asignacion['horarios'] ? json_decode($asignacion['horarios'], true) : [];
    
    echo json_encode([
        'success' => true,
        'asignacion' => $asignacion
    ]);
}

function actualizarAsignacion() {
    global $conexion;
    
    $id = $_POST['asignacion_id'] ?? null;
    $docente_id = $_POST['docente_id'] ?? null;
    $seccion_id = $_POST['seccion_id'] ?? null;
    $area_id = $_POST['area_id'] ?? null;
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? null;
    $horas_semanales = $_POST['horas_semanales'] ?? 2;
    $es_tutor = isset($_POST['es_tutor']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (!$id || !$docente_id || !$seccion_id || !$area_id || !$periodo_academico_id) {
        throw new Exception('Todos los campos requeridos deben ser completados');
    }
    
    // Verificar que la asignación existe
    $stmt_check = $conexion->prepare("SELECT id FROM asignaciones_docentes WHERE id = ?");
    $stmt_check->execute([$id]);
    if (!$stmt_check->fetch()) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Validar que no exista otra asignación igual
    $stmt_check_dup = $conexion->prepare("
        SELECT id FROM asignaciones_docentes 
        WHERE docente_id = ? AND seccion_id = ? AND area_id = ? AND periodo_academico_id = ? 
        AND id != ? AND activo = 1
    ");
    $stmt_check_dup->execute([$docente_id, $seccion_id, $area_id, $periodo_academico_id, $id]);
    
    if ($stmt_check_dup->fetch()) {
        throw new Exception('Ya existe otra asignación igual para este docente, sección y área');
    }
    
    // Si es tutor, remover otras tutorías en la sección
    if ($es_tutor) {
        $stmt_update_tutor = $conexion->prepare("
            UPDATE asignaciones_docentes SET es_tutor = 0 
            WHERE seccion_id = ? AND periodo_academico_id = ? AND id != ? AND activo = 1
        ");
        $stmt_update_tutor->execute([$seccion_id, $periodo_academico_id, $id]);
    }
    
    // Actualizar asignación
    $stmt = $conexion->prepare("
        UPDATE asignaciones_docentes SET 
            docente_id = ?, seccion_id = ?, area_id = ?, periodo_academico_id = ?,
            es_tutor = ?, horas_semanales = ?, activo = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $docente_id, $seccion_id, $area_id, $periodo_academico_id,
        $es_tutor, $horas_semanales, $activo, $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asignación actualizada exitosamente'
    ]);
}

function eliminarAsignacion() {
    global $conexion;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de asignación requerido');
    }
    
    // Verificar que la asignación existe
    $stmt_check = $conexion->prepare("SELECT id FROM asignaciones_docentes WHERE id = ?");
    $stmt_check->execute([$id]);
    if (!$stmt_check->fetch()) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Soft delete
    $stmt = $conexion->prepare("UPDATE asignaciones_docentes SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asignación eliminada exitosamente'
    ]);
}

function toggleTutor() {
    global $conexion;
    
    $id = $_POST['id'] ?? null;
    $es_tutor = $_POST['es_tutor'] ?? null;
    
    if (!$id || $es_tutor === null) {
        throw new Exception('Datos incompletos para cambiar tutoría');
    }
    
    $es_tutor = $es_tutor === 'true' ? 1 : 0;
    
    // Obtener datos de la asignación
    $stmt_get = $conexion->prepare("
        SELECT seccion_id, periodo_academico_id FROM asignaciones_docentes WHERE id = ?
    ");
    $stmt_get->execute([$id]);
    $asignacion = $stmt_get->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignacion) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Si se está asignando como tutor, remover otros tutores
    if ($es_tutor) {
        $stmt_update_otros = $conexion->prepare("
            UPDATE asignaciones_docentes SET es_tutor = 0 
            WHERE seccion_id = ? AND periodo_academico_id = ? AND id != ? AND activo = 1
        ");
        $stmt_update_otros->execute([$asignacion['seccion_id'], $asignacion['periodo_academico_id'], $id]);
    }
    
    // Actualizar la asignación actual
    $stmt = $conexion->prepare("UPDATE asignaciones_docentes SET es_tutor = ? WHERE id = ?");
    $stmt->execute([$es_tutor, $id]);
    
    $mensaje = $es_tutor ? 'Tutoría asignada exitosamente' : 'Tutoría removida exitosamente';
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ]);
}

function obtenerDetalleAsignacion() {
    global $conexion;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de asignación requerido');
    }
    
    $stmt = $conexion->prepare("
        SELECT a.*, 
               d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.codigo_docente,
               s.grado, s.seccion, s.aula_asignada,
               ne.nombre as nivel_nombre,
               ac.nombre as area_nombre, ac.codigo as area_codigo,
               pa.nombre as periodo_nombre, pa.anio,
               COUNT(m.id) as total_estudiantes
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        INNER JOIN secciones s ON a.seccion_id = s.id
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        INNER JOIN areas_curriculares ac ON a.area_id = ac.id
        INNER JOIN periodos_academicos pa ON a.periodo_academico_id = pa.id
        LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1 AND m.estado = 'MATRICULADO'
        WHERE a.id = ?
        GROUP BY a.id
    ");
    
    $stmt->execute([$id]);
    $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignacion) {
        throw new Exception('Asignación no encontrada');
    }
    
    echo json_encode([
        'success' => true,
        'asignacion' => $asignacion
    ]);
}

function detectarConflictos() {
    global $conexion;
    
    $conflictos = [];
    
    // Detectar conflictos de horarios
    $stmt_horarios = $conexion->prepare("
        SELECT a.id, a.horarios, 
               d.nombres as docente_nombres, d.apellidos as docente_apellidos,
               s.grado, s.seccion, ne.nombre as nivel_nombre
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        INNER JOIN secciones s ON a.seccion_id = s.id
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        WHERE a.activo = 1 AND a.horarios IS NOT NULL AND a.horarios != ''
    ");
    $stmt_horarios->execute();
    $asignaciones_con_horarios = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($asignaciones_con_horarios as $asignacion1) {
        $horarios1 = json_decode($asignacion1['horarios'], true);
        if (!$horarios1) continue;
        
        foreach ($asignaciones_con_horarios as $asignacion2) {
            if ($asignacion1['id'] >= $asignacion2['id']) continue;
            
            $horarios2 = json_decode($asignacion2['horarios'], true);
            if (!$horarios2) continue;
            
            // Verificar solapamiento de horarios
            foreach ($horarios1 as $h1) {
                foreach ($horarios2 as $h2) {
                    if ($h1['dia'] == $h2['dia']) {
                        $inicio1 = strtotime($h1['hora_inicio']);
                        $fin1 = strtotime($h1['hora_fin']);
                        $inicio2 = strtotime($h2['hora_inicio']);
                        $fin2 = strtotime($h2['hora_fin']);
                        
                        if ($inicio1 < $fin2 && $fin1 > $inicio2) {
                            $conflictos[] = [
                                'tipo' => 'Conflicto de Horario',
                                'descripcion' => "Solapamiento entre {$asignacion1['docente_nombres']} {$asignacion1['docente_apellidos']} y {$asignacion2['docente_nombres']} {$asignacion2['docente_apellidos']}"
                            ];
                        }
                    }
                }
            }
        }
    }
    
    // Detectar secciones sin tutor
    $stmt_sin_tutor = $conexion->prepare("
        SELECT s.id, s.grado, s.seccion, ne.nombre as nivel_nombre
        FROM secciones s
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        WHERE s.activo = 1 
        AND s.id NOT IN (
            SELECT DISTINCT seccion_id 
            FROM asignaciones_docentes 
            WHERE es_tutor = 1 AND activo = 1
        )
    ");
    $stmt_sin_tutor->execute();
    $secciones_sin_tutor = $stmt_sin_tutor->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($secciones_sin_tutor as $seccion) {
        $conflictos[] = [
            'tipo' => 'Sección sin Tutor',
            'descripcion' => "La sección {$seccion['nivel_nombre']} - {$seccion['grado']} \"{$seccion['seccion']}\" no tiene tutor asignado"
        ];
    }
    
    // Detectar docentes sobrecargados
    $stmt_sobrecarga = $conexion->prepare("
        SELECT d.nombres, d.apellidos, SUM(a.horas_semanales) as total_horas
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        WHERE a.activo = 1
        GROUP BY a.docente_id
        HAVING total_horas > 30
    ");
    $stmt_sobrecarga->execute();
    $docentes_sobrecargados = $stmt_sobrecarga->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($docentes_sobrecargados as $docente) {
        $conflictos[] = [
            'tipo' => 'Sobrecarga Horaria',
            'descripcion' => "{$docente['nombres']} {$docente['apellidos']} tiene {$docente['total_horas']} horas semanales (máximo recomendado: 30h)"
        ];
    }
    
    echo json_encode([
        'success' => true,
        'conflictos' => $conflictos
    ]);
}

function obtenerSecciones() {
    global $conexion;
    
    $stmt = $conexion->prepare("
        SELECT s.id, s.grado, s.seccion, s.aula_asignada, ne.nombre as nivel_nombre
        FROM secciones s
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        WHERE s.activo = 1
        ORDER BY ne.orden, s.grado, s.seccion
    ");
    $stmt->execute();
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'secciones' => $secciones
    ]);
}

function verificarTutor() {
    global $conexion;
    
    $seccion_id = $_POST['seccion_id'] ?? null;
    $excluir_asignacion = $_POST['excluir_asignacion'] ?? null;
    
    if (!$seccion_id) {
        throw new Exception('ID de sección requerido');
    }
    
    $sql = "
        SELECT a.id, d.nombres, d.apellidos
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        WHERE a.seccion_id = ? AND a.es_tutor = 1 AND a.activo = 1
    ";
    $params = [$seccion_id];
    
    if ($excluir_asignacion) {
        $sql .= " AND a.id != ?";
        $params[] = $excluir_asignacion;
    }
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'tiene_tutor' => (bool)$tutor,
        'tutor_nombre' => $tutor ? "{$tutor['nombres']} {$tutor['apellidos']}" : null
    ]);
}

function obtenerHorarios() {
    global $conexion;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de asignación requerido');
    }
    
    $stmt = $conexion->prepare("
        SELECT a.*, 
               d.nombres as docente_nombres, d.apellidos as docente_apellidos,
               s.grado, s.seccion,
               ne.nombre as nivel_nombre,
               ac.nombre as area_nombre, ac.codigo as area_codigo
        FROM asignaciones_docentes a
        INNER JOIN docentes d ON a.docente_id = d.id
        INNER JOIN secciones s ON a.seccion_id = s.id
        INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
        INNER JOIN areas_curriculares ac ON a.area_id = ac.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$id]);
    $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignacion) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Decodificar horarios
    $asignacion['horarios'] = $asignacion['horarios'] ? json_decode($asignacion['horarios'], true) : [];
    
    echo json_encode([
        'success' => true,
        'asignacion' => $asignacion
    ]);
}

function actualizarHorarios() {
    global $conexion;
    
    $id = $_POST['asignacion_id'] ?? null;
    if (!$id) {
        throw new Exception('ID de asignación requerido');
    }
    
    // Verificar que la asignación existe
    $stmt_check = $conexion->prepare("SELECT id FROM asignaciones_docentes WHERE id = ?");
    $stmt_check->execute([$id]);
    if (!$stmt_check->fetch()) {
        throw new Exception('Asignación no encontrada');
    }
    
    // Procesar horarios
    $horarios = [];
    if (isset($_POST['horarios']) && is_array($_POST['horarios'])) {
        foreach ($_POST['horarios'] as $horario) {
            if (!empty($horario['dia']) && !empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
                $horarios[] = [
                    'dia' => intval($horario['dia']),
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'aula' => $horario['aula'] ?? ''
                ];
            }
        }
    }
    
    // Actualizar horarios
    $horarios_json = !empty($horarios) ? json_encode($horarios) : null;
    
    $stmt = $conexion->prepare("UPDATE asignaciones_docentes SET horarios = ? WHERE id = ?");
    $stmt->execute([$horarios_json, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Horarios actualizados exitosamente'
    ]);
}
?>