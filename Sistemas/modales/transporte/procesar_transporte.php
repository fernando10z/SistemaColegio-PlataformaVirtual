<?php
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
            crearRuta($conexion);
            break;
            
        case 'asignar_vehiculo':
            asignarVehiculo($conexion);
            break;
            
        case 'asignar_estudiantes':
            asignarEstudiantes($conexion);
            break;
            
        case 'obtener_ruta':
            obtenerRuta($conexion);
            break;
            
        case 'eliminar':
            eliminarRuta($conexion);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function crearRuta($conexion) {
    $codigo_ruta = trim($_POST['codigo_ruta'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $horario_salida = $_POST['horario_salida'] ?? '';
    $horario_retorno = $_POST['horario_retorno'] ?? '';
    $tarifa = $_POST['tarifa'] ?? 0;
    $activo = $_POST['activo'] ?? 1;
    $paraderos_json = $_POST['paraderos_json'] ?? '[]';
    
    if (empty($codigo_ruta)) {
        echo json_encode(['success' => false, 'message' => 'El código de la ruta es obligatorio']);
        return;
    }
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la ruta es obligatorio']);
        return;
    }
    
    if (empty($horario_salida) || empty($horario_retorno)) {
        echo json_encode(['success' => false, 'message' => 'Los horarios son obligatorios']);
        return;
    }
    
    if ($horario_retorno <= $horario_salida) {
        echo json_encode(['success' => false, 'message' => 'El horario de retorno debe ser posterior al de salida']);
        return;
    }
    
    $paraderos = json_decode($paraderos_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($paraderos)) {
        echo json_encode(['success' => false, 'message' => 'Debe agregar al menos un paradero']);
        return;
    }
    
    $sql_check = "SELECT id FROM rutas_transporte WHERE codigo_ruta = :codigo_ruta";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':codigo_ruta', $codigo_ruta);
    $stmt_check->execute();
    
    if ($stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una ruta con ese código']);
        return;
    }
    
    $configuracion = json_encode([
        'horario_salida' => $horario_salida,
        'horario_retorno' => $horario_retorno
    ]);
    
    $sql = "INSERT INTO rutas_transporte (codigo_ruta, nombre, configuracion, paraderos, tarifa, activo) 
            VALUES (:codigo_ruta, :nombre, :configuracion, :paraderos, :tarifa, :activo)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':codigo_ruta', $codigo_ruta);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':configuracion', $configuracion);
    $stmt->bindParam(':paraderos', $paraderos_json);
    $stmt->bindParam(':tarifa', $tarifa);
    $stmt->bindParam(':activo', $activo);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ruta creada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la ruta']);
    }
}

function asignarVehiculo($conexion) {
    $ruta_id = $_POST['ruta_id'] ?? '';
    $vehiculo_id = $_POST['vehiculo_id'] ?? '';
    $periodo_id = $_POST['periodo_id'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    if (empty($ruta_id) || !is_numeric($ruta_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de ruta no válido']);
        return;
    }
    
    if (empty($vehiculo_id) || !is_numeric($vehiculo_id)) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar un vehículo']);
        return;
    }
    
    if (empty($periodo_id)) {
        echo json_encode(['success' => false, 'message' => 'No hay período académico activo']);
        return;
    }
    
    $sql_vehiculo = "SELECT id FROM vehiculos_transporte WHERE id = :id AND activo = 1";
    $stmt_vehiculo = $conexion->prepare($sql_vehiculo);
    $stmt_vehiculo->bindParam(':id', $vehiculo_id);
    $stmt_vehiculo->execute();
    
    if (!$stmt_vehiculo->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El vehículo seleccionado no es válido']);
        return;
    }
    
    $sql_ruta = "SELECT id FROM rutas_transporte WHERE id = :id";
    $stmt_ruta = $conexion->prepare($sql_ruta);
    $stmt_ruta->bindParam(':id', $ruta_id);
    $stmt_ruta->execute();
    
    if (!$stmt_ruta->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La ruta seleccionada no existe']);
        return;
    }
    
    $sql_check = "SELECT id FROM asignaciones_transporte 
                  WHERE ruta_id = :ruta_id AND periodo_academico_id = :periodo_id AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':ruta_id', $ruta_id);
    $stmt_check->bindParam(':periodo_id', $periodo_id);
    $stmt_check->execute();
    
    $configuracion = json_encode([
        'observaciones' => $observaciones
    ]);
    
    if ($asignacion_existente = $stmt_check->fetch()) {
        $sql = "UPDATE asignaciones_transporte 
                SET vehiculo_id = :vehiculo_id, 
                    configuracion = :configuracion
                WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':vehiculo_id', $vehiculo_id);
        $stmt->bindParam(':configuracion', $configuracion);
        $stmt->bindParam(':id', $asignacion_existente['id']);
    } else {
        $sql = "INSERT INTO asignaciones_transporte 
                (ruta_id, vehiculo_id, periodo_academico_id, configuracion, activo) 
                VALUES (:ruta_id, :vehiculo_id, :periodo_id, :configuracion, 1)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':ruta_id', $ruta_id);
        $stmt->bindParam(':vehiculo_id', $vehiculo_id);
        $stmt->bindParam(':periodo_id', $periodo_id);
        $stmt->bindParam(':configuracion', $configuracion);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vehículo asignado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al asignar vehículo']);
    }
}

function asignarEstudiantes($conexion) {
    $ruta_id = $_POST['ruta_id'] ?? '';
    $paraderos_json = $_POST['paraderos_json'] ?? '[]';
    
    if (empty($ruta_id) || !is_numeric($ruta_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de ruta no válido']);
        return;
    }
    
    $paraderos = json_decode($paraderos_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($paraderos)) {
        echo json_encode(['success' => false, 'message' => 'Formato de paraderos inválido']);
        return;
    }
    
    $sql_ruta = "SELECT id FROM rutas_transporte WHERE id = :id";
    $stmt_ruta = $conexion->prepare($sql_ruta);
    $stmt_ruta->bindParam(':id', $ruta_id);
    $stmt_ruta->execute();
    
    if (!$stmt_ruta->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La ruta seleccionada no existe']);
        return;
    }
    
    $estudiantes_asignados = [];
    foreach ($paraderos as $paradero) {
        if (isset($paradero['estudiantes']) && is_array($paradero['estudiantes'])) {
            $estudiantes_asignados = array_merge($estudiantes_asignados, $paradero['estudiantes']);
        }
    }
    
    if (!empty($estudiantes_asignados)) {
        $placeholders = implode(',', array_fill(0, count($estudiantes_asignados), '?'));
        $sql_est_check = "SELECT COUNT(*) as total FROM estudiantes WHERE id IN ($placeholders) AND activo = 1";
        $stmt_est_check = $conexion->prepare($sql_est_check);
        $stmt_est_check->execute($estudiantes_asignados);
        $result = $stmt_est_check->fetch();
        
        if ($result['total'] != count($estudiantes_asignados)) {
            echo json_encode(['success' => false, 'message' => 'Algunos estudiantes seleccionados no son válidos']);
            return;
        }
    }
    
    $sql = "UPDATE rutas_transporte SET paraderos = :paraderos WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':paraderos', $paraderos_json);
    $stmt->bindParam(':id', $ruta_id);
    
    if ($stmt->execute()) {
        $total_estudiantes = count($estudiantes_asignados);
        echo json_encode([
            'success' => true, 
            'message' => "Estudiantes asignados exitosamente ({$total_estudiantes} estudiantes)"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al asignar estudiantes']);
    }
}

function obtenerRuta($conexion) {
    $id = $_POST['id'] ?? '';
    
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de ruta no válido']);
        return;
    }
    
    $sql_periodo = "SELECT id FROM periodos_academicos WHERE activo = 1 LIMIT 1";
    $stmt_periodo = $conexion->prepare($sql_periodo);
    $stmt_periodo->execute();
    $periodo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
    
    $sql = "SELECT 
                rt.*,
                at.vehiculo_id,
                vt.capacidad
            FROM rutas_transporte rt
            LEFT JOIN asignaciones_transporte at ON rt.id = at.ruta_id AND at.periodo_academico_id = :periodo_id AND at.activo = 1
            LEFT JOIN vehiculos_transporte vt ON at.vehiculo_id = vt.id
            WHERE rt.id = :id";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':periodo_id', $periodo['id']);
    $stmt->execute();
    
    $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ruta) {
        echo json_encode(['success' => true, 'ruta' => $ruta]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
    }
}

function eliminarRuta($conexion) {
    $id = $_POST['id'] ?? '';
    
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de ruta no válido']);
        return;
    }
    
    $sql_check = "SELECT id FROM rutas_transporte WHERE id = :id";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':id', $id);
    $stmt_check->execute();
    
    if (!$stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'La ruta no existe']);
        return;
    }
    
    $conexion->beginTransaction();
    
    try {
        $sql_asignaciones = "DELETE FROM asignaciones_transporte WHERE ruta_id = :id";
        $stmt_asignaciones = $conexion->prepare($sql_asignaciones);
        $stmt_asignaciones->bindParam(':id', $id);
        $stmt_asignaciones->execute();
        
        $sql = "DELETE FROM rutas_transporte WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $conexion->commit();
        
        echo json_encode(['success' => true, 'message' => 'Ruta eliminada exitosamente']);
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la ruta: ' . $e->getMessage()]);
    }
}
?>