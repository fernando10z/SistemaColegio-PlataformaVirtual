<?php
// modales/periodos/procesar_periodos.php
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            $resultado = crearPeriodo();
            break;
        case 'actualizar':
            $resultado = actualizarPeriodo();
            break;
        case 'obtener':
            $resultado = obtenerPeriodo();
            break;
        case 'obtener_evaluaciones':
            $resultado = obtenerEvaluaciones();
            break;
        case 'marcar_actual':
            $resultado = marcarComoActual();
            break;
        case 'toggle_estado':
            $resultado = toggleEstado();
            break;
        case 'regenerar_evaluaciones':
            $resultado = regenerarEvaluaciones();
            break;
        case 'duplicar':
            $resultado = duplicarPeriodo();
            break;
        default:
            $resultado = ['success' => false, 'message' => 'Acción no válida'];
    }
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en procesar_periodos.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

function crearPeriodo() {
    global $conexion;
    
    // Validar datos requeridos
    $campos_requeridos = ['nombre', 'anio', 'fecha_inicio', 'fecha_fin', 'tipo_periodo'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            return ['success' => false, 'message' => "El campo {$campo} es requerido"];
        }
    }
    
    $nombre = trim($_POST['nombre']);
    $anio = intval($_POST['anio']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo_periodo = $_POST['tipo_periodo'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $actual = isset($_POST['actual']) ? 1 : 0;
    $periodos_evaluacion = $_POST['periodos_evaluacion'] ?? '[]';
    
    // Validaciones
    if ($anio < 2020 || $anio > 2030) {
        return ['success' => false, 'message' => 'El año debe estar entre 2020 y 2030'];
    }
    
    if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        return ['success' => false, 'message' => 'La fecha de fin debe ser posterior a la fecha de inicio'];
    }
    
    if (!in_array($tipo_periodo, ['BIMESTRE', 'TRIMESTRE', 'SEMESTRE'])) {
        return ['success' => false, 'message' => 'Tipo de período no válido'];
    }
    
    // Validar períodos de evaluación
    $evaluaciones = json_decode($periodos_evaluacion, true);
    if (!$evaluaciones || count($evaluaciones) == 0) {
        return ['success' => false, 'message' => 'Debe configurar al menos un período de evaluación'];
    }
    
    // Verificar si ya existe un período con el mismo nombre
    $stmt_check = $conexion->prepare("SELECT id FROM periodos_academicos WHERE nombre = ? AND anio = ?");
    $stmt_check->execute([$nombre, $anio]);
    if ($stmt_check->fetch()) {
        return ['success' => false, 'message' => 'Ya existe un período con ese nombre para el año ' . $anio];
    }
    
    $conexion->beginTransaction();
    
    try {
        // Si se marca como actual, desactivar el período actual
        if ($actual) {
            $stmt_deactivate = $conexion->prepare("UPDATE periodos_academicos SET actual = 0");
            $stmt_deactivate->execute();
        }
        
        // Insertar nuevo período
        $sql = "INSERT INTO periodos_academicos (nombre, anio, fecha_inicio, fecha_fin, tipo_periodo, 
                periodos_evaluacion, activo, actual) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nombre,
            $anio,
            $fecha_inicio,
            $fecha_fin,
            $tipo_periodo,
            $periodos_evaluacion,
            $activo,
            $actual
        ]);
        
        $periodo_id = $conexion->lastInsertId();
        
        // Registrar en auditoría
        registrarAuditoria('CREAR_PERIODO', 'periodos_academicos', $periodo_id, [
            'nombre' => $nombre,
            'anio' => $anio,
            'tipo_periodo' => $tipo_periodo
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => 'Período académico creado exitosamente',
            'periodo_id' => $periodo_id
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

function actualizarPeriodo() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID de período no válido'];
    }
    
    // Verificar que el período existe
    $stmt_check = $conexion->prepare("SELECT * FROM periodos_academicos WHERE id = ?");
    $stmt_check->execute([$id]);
    $periodo_actual = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo_actual) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // Validar datos
    $nombre = trim($_POST['nombre']);
    $anio = intval($_POST['anio']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo_periodo = $_POST['tipo_periodo'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $actual = isset($_POST['actual']) ? 1 : 0;
    $periodos_evaluacion = $_POST['periodos_evaluacion'] ?? '[]';
    
    // Validaciones
    if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
        return ['success' => false, 'message' => 'La fecha de fin debe ser posterior a la fecha de inicio'];
    }
    
    // Verificar si hay cambios críticos que afecten datos existentes
    $cambios_criticos = false;
    if ($periodo_actual['fecha_inicio'] != $fecha_inicio || 
        $periodo_actual['fecha_fin'] != $fecha_fin ||
        $periodo_actual['tipo_periodo'] != $tipo_periodo) {
        
        // Verificar si hay matrículas o calificaciones
        $stmt_matriculas = $conexion->prepare("SELECT COUNT(*) as total FROM matriculas WHERE periodo_academico_id = ?");
        $stmt_matriculas->execute([$id]);
        $matriculas_count = $stmt_matriculas->fetch()['total'];
        
        if ($matriculas_count > 0) {
            $cambios_criticos = true;
        }
    }
    
    $conexion->beginTransaction();
    
    try {
        // Si se marca como actual, desactivar otros períodos actuales
        if ($actual && !$periodo_actual['actual']) {
            $stmt_deactivate = $conexion->prepare("UPDATE periodos_academicos SET actual = 0");
            $stmt_deactivate->execute();
        }
        
        // Actualizar período
        $sql = "UPDATE periodos_academicos SET 
                nombre = ?, anio = ?, fecha_inicio = ?, fecha_fin = ?, 
                tipo_periodo = ?, periodos_evaluacion = ?, activo = ?, actual = ?
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nombre, $anio, $fecha_inicio, $fecha_fin,
            $tipo_periodo, $periodos_evaluacion, $activo, $actual, $id
        ]);
        
        // Registrar cambios en auditoría
        $cambios = [
            'anterior' => $periodo_actual,
            'nuevo' => [
                'nombre' => $nombre,
                'anio' => $anio,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'tipo_periodo' => $tipo_periodo,
                'activo' => $activo,
                'actual' => $actual
            ]
        ];
        
        registrarAuditoria('ACTUALIZAR_PERIODO', 'periodos_academicos', $id, $cambios);
        
        $conexion->commit();
        
        $mensaje = 'Período académico actualizado exitosamente';
        if ($cambios_criticos) {
            $mensaje .= '. Los cambios pueden afectar matrículas existentes.';
        }
        
        return ['success' => true, 'message' => $mensaje];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

function obtenerPeriodo() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    $sql = "SELECT pa.*, 
                COUNT(DISTINCT s.id) as total_secciones,
                COUNT(DISTINCT m.id) as total_matriculas
            FROM periodos_academicos pa
            LEFT JOIN secciones s ON pa.id = s.periodo_academico_id AND s.activo = 1
            LEFT JOIN matriculas m ON pa.id = m.periodo_academico_id AND m.activo = 1
            WHERE pa.id = ?
            GROUP BY pa.id";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // Decodificar períodos de evaluación
    $periodo['periodos_evaluacion'] = json_decode($periodo['periodos_evaluacion'], true) ?: [];
    
    return ['success' => true, 'periodo' => $periodo];
}

function obtenerEvaluaciones() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    $stmt = $conexion->prepare("SELECT * FROM periodos_academicos WHERE id = ?");
    $stmt->execute([$id]);
    $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // Decodificar períodos de evaluación
    $periodo['periodos_evaluacion'] = json_decode($periodo['periodos_evaluacion'], true) ?: [];
    
    return ['success' => true, 'periodo' => $periodo];
}

function marcarComoActual() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    // Verificar que el período existe y está activo
    $stmt_check = $conexion->prepare("SELECT nombre, activo FROM periodos_academicos WHERE id = ?");
    $stmt_check->execute([$id]);
    $periodo = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    if (!$periodo['activo']) {
        return ['success' => false, 'message' => 'No se puede marcar como actual un período inactivo'];
    }
    
    $conexion->beginTransaction();
    
    try {
        // Desmarcar período actual
        $stmt_deactivate = $conexion->prepare("UPDATE periodos_academicos SET actual = 0");
        $stmt_deactivate->execute();
        
        // Marcar nuevo período como actual
        $stmt_activate = $conexion->prepare("UPDATE periodos_academicos SET actual = 1 WHERE id = ?");
        $stmt_activate->execute([$id]);
        
        // Registrar en auditoría
        registrarAuditoria('MARCAR_ACTUAL', 'periodos_academicos', $id, [
            'periodo_nombre' => $periodo['nombre']
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => "'{$periodo['nombre']}' marcado como período actual"
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

function toggleEstado() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    $estado = $_POST['estado'] === 'true' ? 1 : 0;
    
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    // Verificar que el período existe
    $stmt_check = $conexion->prepare("SELECT nombre, actual FROM periodos_academicos WHERE id = ?");
    $stmt_check->execute([$id]);
    $periodo = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // No permitir desactivar el período actual
    if ($estado == 0 && $periodo['actual']) {
        return ['success' => false, 'message' => 'No se puede desactivar el período actual'];
    }
    
    $conexion->beginTransaction();
    
    try {
        $stmt = $conexion->prepare("UPDATE periodos_academicos SET activo = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        
        // Registrar en auditoría
        registrarAuditoria('TOGGLE_ESTADO', 'periodos_academicos', $id, [
            'periodo_nombre' => $periodo['nombre'],
            'nuevo_estado' => $estado ? 'Activo' : 'Inactivo'
        ]);
        
        $conexion->commit();
        
        $accion = $estado ? 'activado' : 'desactivado';
        return [
            'success' => true, 
            'message' => "Período '{$periodo['nombre']}' {$accion} exitosamente"
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

function regenerarEvaluaciones() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    // Obtener datos del período
    $stmt = $conexion->prepare("SELECT * FROM periodos_academicos WHERE id = ?");
    $stmt->execute([$id]);
    $periodo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // Generar nuevos períodos de evaluación
    $nuevas_evaluaciones = generarPeriodosEvaluacionAutomaticos(
        $periodo['fecha_inicio'],
        $periodo['fecha_fin'],
        $periodo['tipo_periodo']
    );
    
    if (empty($nuevas_evaluaciones)) {
        return ['success' => false, 'message' => 'Error al generar períodos de evaluación'];
    }
    
    try {
        $stmt_update = $conexion->prepare("UPDATE periodos_academicos SET periodos_evaluacion = ? WHERE id = ?");
        $stmt_update->execute([json_encode($nuevas_evaluaciones), $id]);
        
        // Registrar en auditoría
        registrarAuditoria('REGENERAR_EVALUACIONES', 'periodos_academicos', $id, [
            'periodo_nombre' => $periodo['nombre'],
            'num_evaluaciones' => count($nuevas_evaluaciones)
        ]);
        
        return [
            'success' => true, 
            'message' => 'Períodos de evaluación regenerados exitosamente'
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

function duplicarPeriodo() {
    global $conexion;
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        return ['success' => false, 'message' => 'ID no válido'];
    }
    
    // Obtener datos del período original
    $stmt = $conexion->prepare("SELECT * FROM periodos_academicos WHERE id = ?");
    $stmt->execute([$id]);
    $periodo_original = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$periodo_original) {
        return ['success' => false, 'message' => 'Período no encontrado'];
    }
    
    // Calcular nuevo año
    $nuevo_anio = $periodo_original['anio'] + 1;
    $nuevo_nombre = str_replace($periodo_original['anio'], $nuevo_anio, $periodo_original['nombre']);
    
    // Verificar si ya existe un período para el nuevo año
    $stmt_check = $conexion->prepare("SELECT id FROM periodos_academicos WHERE anio = ? AND tipo_periodo = ?");
    $stmt_check->execute([$nuevo_anio, $periodo_original['tipo_periodo']]);
    if ($stmt_check->fetch()) {
        return ['success' => false, 'message' => "Ya existe un período {$periodo_original['tipo_periodo']} para el año {$nuevo_anio}"];
    }
    
    // Calcular nuevas fechas (mantener el mismo período del año)
    $fecha_inicio_original = new DateTime($periodo_original['fecha_inicio']);
    $fecha_fin_original = new DateTime($periodo_original['fecha_fin']);
    
    $nueva_fecha_inicio = clone $fecha_inicio_original;
    $nueva_fecha_fin = clone $fecha_fin_original;
    
    $nueva_fecha_inicio->setDate($nuevo_anio, $fecha_inicio_original->format('n'), $fecha_inicio_original->format('j'));
    $nueva_fecha_fin->setDate($nuevo_anio, $fecha_fin_original->format('n'), $fecha_fin_original->format('j'));
    
    // Generar nuevos períodos de evaluación
    $nuevas_evaluaciones = generarPeriodosEvaluacionAutomaticos(
        $nueva_fecha_inicio->format('Y-m-d'),
        $nueva_fecha_fin->format('Y-m-d'),
        $periodo_original['tipo_periodo']
    );
    
    $conexion->beginTransaction();
    
    try {
        // Insertar período duplicado
        $sql = "INSERT INTO periodos_academicos (nombre, anio, fecha_inicio, fecha_fin, tipo_periodo, 
                periodos_evaluacion, activo, actual) VALUES (?, ?, ?, ?, ?, ?, 1, 0)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nuevo_nombre,
            $nuevo_anio,
            $nueva_fecha_inicio->format('Y-m-d'),
            $nueva_fecha_fin->format('Y-m-d'),
            $periodo_original['tipo_periodo'],
            json_encode($nuevas_evaluaciones)
        ]);
        
        $nuevo_periodo_id = $conexion->lastInsertId();
        
        // Registrar en auditoría
        registrarAuditoria('DUPLICAR_PERIODO', 'periodos_academicos', $nuevo_periodo_id, [
            'periodo_original' => $periodo_original['nombre'],
            'periodo_nuevo' => $nuevo_nombre,
            'anio_original' => $periodo_original['anio'],
            'anio_nuevo' => $nuevo_anio
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => "Período duplicado exitosamente como '{$nuevo_nombre}'",
            'nuevo_periodo_id' => $nuevo_periodo_id
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

function generarPeriodosEvaluacionAutomaticos($fecha_inicio, $fecha_fin, $tipo_periodo) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $duracion_total = $inicio->diff($fin)->days;
    
    $periodos = [];
    
    switch ($tipo_periodo) {
        case 'BIMESTRE':
            $num_periodos = 4;
            $nombre_base = 'Bimestre';
            break;
        case 'TRIMESTRE':
            $num_periodos = 3;
            $nombre_base = 'Trimestre';
            break;
        case 'SEMESTRE':
            $num_periodos = 2;
            $nombre_base = 'Semestre';
            break;
        default:
            return [];
    }
    
    $dias_por_periodo = floor($duracion_total / $num_periodos);
    $fecha_actual = clone $inicio;
    
    for ($i = 1; $i <= $num_periodos; $i++) {
        $fecha_fin_periodo = clone $fecha_actual;
        
        if ($i < $num_periodos) {
            $fecha_fin_periodo->add(new DateInterval('P' . $dias_por_periodo . 'D'));
        } else {
            $fecha_fin_periodo = clone $fin;
        }
        
        $periodos[] = [
            'numero' => $i,
            'nombre' => numeroRomano($i) . ' ' . $nombre_base,
            'fecha_inicio' => $fecha_actual->format('Y-m-d'),
            'fecha_fin' => $fecha_fin_periodo->format('Y-m-d')
        ];
        
        $fecha_actual = clone $fecha_fin_periodo;
        $fecha_actual->add(new DateInterval('P1D'));
    }
    
    return $periodos;
}

function numeroRomano($numero) {
    $valores = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1];
    $simbolos = ['M', 'CM', 'D', 'CD', 'C', 'XC', 'L', 'XL', 'X', 'IX', 'V', 'IV', 'I'];
    $resultado = '';
    
    for ($i = 0; $i < count($valores); $i++) {
        while ($numero >= $valores[$i]) {
            $resultado .= $simbolos[$i];
            $numero -= $valores[$i];
        }
    }
    
    return $resultado;
}

function registrarAuditoria($accion, $tabla, $registro_id, $datos) {
    global $conexion;
    
    try {
        $sql = "INSERT INTO auditoria_sistema (usuario_id, modulo, accion, tabla_afectada, 
                registro_id, datos_cambio, metadatos) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $metadatos = [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $_SESSION['usuario_id'] ?? 1, // ID del usuario logueado
            'PERIODOS_ACADEMICOS',
            $accion,
            $tabla,
            $registro_id,
            json_encode($datos),
            json_encode($metadatos)
        ]);
        
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Error registrando auditoría: " . $e->getMessage());
    }
}
?>