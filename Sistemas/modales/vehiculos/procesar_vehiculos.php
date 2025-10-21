<?php
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

// Validar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'agregar':
            agregarVehiculo($conexion);
            break;
        
        case 'editar':
            editarVehiculo($conexion);
            break;
        
        case 'eliminar':
            eliminarVehiculo($conexion);
            break;
        
        case 'obtener':
            obtenerVehiculo($conexion);
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

function agregarVehiculo($conexion) {
    // Validar campos obligatorios
    $placa = trim($_POST['placa'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $capacidad = trim($_POST['capacidad'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    
    // Validaciones
    if (empty($placa) || strlen($placa) < 3 || strlen($placa) > 20) {
        echo json_encode([
            'success' => false,
            'message' => 'La placa debe tener entre 3 y 20 caracteres'
        ]);
        return;
    }
    
    if (empty($modelo) || strlen($modelo) < 3 || strlen($modelo) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El modelo debe tener entre 3 y 200 caracteres'
        ]);
        return;
    }
    
    if (empty($capacidad) || !is_numeric($capacidad) || $capacidad < 1 || $capacidad > 999) {
        echo json_encode([
            'success' => false,
            'message' => 'La capacidad debe ser un número entre 1 y 999'
        ]);
        return;
    }
    
    if (!in_array($estado, ['ACTIVO', 'MANTENIMIENTO', 'INACTIVO'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Estado no válido'
        ]);
        return;
    }
    
    // Convertir placa a mayúsculas
    $placa = strtoupper($placa);
    
    // Verificar que la placa no exista
    $sql_check = "SELECT id FROM vehiculos_transporte WHERE placa = :placa AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':placa', $placa);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un vehículo con esta placa'
        ]);
        return;
    }
    
    // Construir JSON de datos_vehiculo
    $datos_vehiculo = [
        'marca' => trim($_POST['marca'] ?? ''),
        'anio' => trim($_POST['anio'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'num_motor' => strtoupper(trim($_POST['num_motor'] ?? '')),
        'num_chasis' => strtoupper(trim($_POST['num_chasis'] ?? '')),
        'num_serie' => strtoupper(trim($_POST['num_serie'] ?? '')),
        'combustible' => trim($_POST['combustible'] ?? '')
    ];
    
    // Validar año si existe
    if (!empty($datos_vehiculo['anio'])) {
        if (!is_numeric($datos_vehiculo['anio']) || $datos_vehiculo['anio'] < 1900 || $datos_vehiculo['anio'] > 2100) {
            echo json_encode([
                'success' => false,
                'message' => 'El año debe estar entre 1900 y 2100'
            ]);
            return;
        }
    }
    
    // Construir JSON de documentacion
    $documentacion = [
        'tarjeta_propiedad' => trim($_POST['tarjeta_propiedad'] ?? ''),
        'fecha_venc_tarjeta' => trim($_POST['fecha_venc_tarjeta'] ?? ''),
        'soat' => trim($_POST['soat'] ?? ''),
        'fecha_venc_soat' => trim($_POST['fecha_venc_soat'] ?? ''),
        'revision_tecnica' => trim($_POST['revision_tecnica'] ?? ''),
        'fecha_venc_revision' => trim($_POST['fecha_venc_revision'] ?? '')
    ];
    
    // Validar fechas si existen
    foreach (['fecha_venc_tarjeta', 'fecha_venc_soat', 'fecha_venc_revision'] as $fecha_campo) {
        if (!empty($documentacion[$fecha_campo])) {
            $fecha = DateTime::createFromFormat('Y-m-d', $documentacion[$fecha_campo]);
            if (!$fecha || $fecha->format('Y-m-d') !== $documentacion[$fecha_campo]) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Formato de fecha inválido en documentación'
                ]);
                return;
            }
        }
    }
    
    // Construir JSON de personal
    $conductor_telefono = trim($_POST['conductor_telefono'] ?? '');
    $copiloto_telefono = trim($_POST['copiloto_telefono'] ?? '');
    $copiloto_dni = trim($_POST['copiloto_dni'] ?? '');
    
    // Validar teléfonos
    if (!empty($conductor_telefono) && !preg_match('/^[0-9+\-\s()]+$/', $conductor_telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de teléfono del conductor inválido'
        ]);
        return;
    }
    
    if (!empty($copiloto_telefono) && !preg_match('/^[0-9+\-\s()]+$/', $copiloto_telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de teléfono del copiloto inválido'
        ]);
        return;
    }
    
    // Validar DNI
    if (!empty($copiloto_dni) && !preg_match('/^[0-9]+$/', $copiloto_dni)) {
        echo json_encode([
            'success' => false,
            'message' => 'El DNI debe contener solo números'
        ]);
        return;
    }
    
    $personal = [
        'conductor_nombre' => trim($_POST['conductor_nombre'] ?? ''),
        'conductor_licencia' => strtoupper(trim($_POST['conductor_licencia'] ?? '')),
        'conductor_telefono' => $conductor_telefono,
        'copiloto_nombre' => trim($_POST['copiloto_nombre'] ?? ''),
        'copiloto_dni' => $copiloto_dni,
        'copiloto_telefono' => $copiloto_telefono
    ];
    
    // Validar longitud de campos de texto
    if (!empty($personal['conductor_nombre']) && strlen($personal['conductor_nombre']) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre del conductor es demasiado largo (máx. 200 caracteres)'
        ]);
        return;
    }
    
    if (!empty($personal['copiloto_nombre']) && strlen($personal['copiloto_nombre']) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre del copiloto es demasiado largo (máx. 200 caracteres)'
        ]);
        return;
    }
    
    // Observaciones
    $observaciones = trim($_POST['observaciones'] ?? '');
    if (strlen($observaciones) > 500) {
        echo json_encode([
            'success' => false,
            'message' => 'Las observaciones no pueden exceder los 500 caracteres'
        ]);
        return;
    }
    
    // Convertir arrays a JSON
    $datos_vehiculo_json = json_encode($datos_vehiculo, JSON_UNESCAPED_UNICODE);
    $documentacion_json = json_encode($documentacion, JSON_UNESCAPED_UNICODE);
    $personal_json = json_encode($personal, JSON_UNESCAPED_UNICODE);
    
    // Insertar en la base de datos
    $sql = "INSERT INTO vehiculos_transporte 
            (placa, modelo, capacidad, datos_vehiculo, documentacion, personal, estado, observaciones, activo, fecha_creacion) 
            VALUES 
            (:placa, :modelo, :capacidad, :datos_vehiculo, :documentacion, :personal, :estado, :observaciones, 1, CURRENT_TIMESTAMP)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':placa', $placa);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':capacidad', $capacidad);
    $stmt->bindParam(':datos_vehiculo', $datos_vehiculo_json);
    $stmt->bindParam(':documentacion', $documentacion_json);
    $stmt->bindParam(':personal', $personal_json);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':observaciones', $observaciones);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Vehículo agregado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar el vehículo'
        ]);
    }
}

function editarVehiculo($conexion) {
    // Validar ID
    $id = trim($_POST['id'] ?? '');
    if (empty($id) || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de vehículo no válido'
        ]);
        return;
    }
    
    // Verificar que el vehículo existe
    $sql_check = "SELECT id FROM vehiculos_transporte WHERE id = :id AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':id', $id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Vehículo no encontrado'
        ]);
        return;
    }
    
    // Validar campos obligatorios
    $placa = trim($_POST['placa'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $capacidad = trim($_POST['capacidad'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    
    // Validaciones
    if (empty($placa) || strlen($placa) < 3 || strlen($placa) > 20) {
        echo json_encode([
            'success' => false,
            'message' => 'La placa debe tener entre 3 y 20 caracteres'
        ]);
        return;
    }
    
    if (empty($modelo) || strlen($modelo) < 3 || strlen($modelo) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El modelo debe tener entre 3 y 200 caracteres'
        ]);
        return;
    }
    
    if (empty($capacidad) || !is_numeric($capacidad) || $capacidad < 1 || $capacidad > 999) {
        echo json_encode([
            'success' => false,
            'message' => 'La capacidad debe ser un número entre 1 y 999'
        ]);
        return;
    }
    
    if (!in_array($estado, ['ACTIVO', 'MANTENIMIENTO', 'INACTIVO'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Estado no válido'
        ]);
        return;
    }
    
    // Convertir placa a mayúsculas
    $placa = strtoupper($placa);
    
    // Verificar que la placa no exista en otro vehículo
    $sql_check_placa = "SELECT id FROM vehiculos_transporte WHERE placa = :placa AND id != :id AND activo = 1";
    $stmt_check_placa = $conexion->prepare($sql_check_placa);
    $stmt_check_placa->bindParam(':placa', $placa);
    $stmt_check_placa->bindParam(':id', $id);
    $stmt_check_placa->execute();
    
    if ($stmt_check_placa->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otro vehículo con esta placa'
        ]);
        return;
    }
    
    // Construir JSON de datos_vehiculo
    $datos_vehiculo = [
        'marca' => trim($_POST['marca'] ?? ''),
        'anio' => trim($_POST['anio'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'num_motor' => strtoupper(trim($_POST['num_motor'] ?? '')),
        'num_chasis' => strtoupper(trim($_POST['num_chasis'] ?? '')),
        'num_serie' => strtoupper(trim($_POST['num_serie'] ?? '')),
        'combustible' => trim($_POST['combustible'] ?? '')
    ];
    
    // Validar año si existe
    if (!empty($datos_vehiculo['anio'])) {
        if (!is_numeric($datos_vehiculo['anio']) || $datos_vehiculo['anio'] < 1900 || $datos_vehiculo['anio'] > 2100) {
            echo json_encode([
                'success' => false,
                'message' => 'El año debe estar entre 1900 y 2100'
            ]);
            return;
        }
    }
    
    // Construir JSON de documentacion
    $documentacion = [
        'tarjeta_propiedad' => trim($_POST['tarjeta_propiedad'] ?? ''),
        'fecha_venc_tarjeta' => trim($_POST['fecha_venc_tarjeta'] ?? ''),
        'soat' => trim($_POST['soat'] ?? ''),
        'fecha_venc_soat' => trim($_POST['fecha_venc_soat'] ?? ''),
        'revision_tecnica' => trim($_POST['revision_tecnica'] ?? ''),
        'fecha_venc_revision' => trim($_POST['fecha_venc_revision'] ?? '')
    ];
    
    // Validar fechas si existen
    foreach (['fecha_venc_tarjeta', 'fecha_venc_soat', 'fecha_venc_revision'] as $fecha_campo) {
        if (!empty($documentacion[$fecha_campo])) {
            $fecha = DateTime::createFromFormat('Y-m-d', $documentacion[$fecha_campo]);
            if (!$fecha || $fecha->format('Y-m-d') !== $documentacion[$fecha_campo]) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Formato de fecha inválido en documentación'
                ]);
                return;
            }
        }
    }
    
    // Construir JSON de personal
    $conductor_telefono = trim($_POST['conductor_telefono'] ?? '');
    $copiloto_telefono = trim($_POST['copiloto_telefono'] ?? '');
    $copiloto_dni = trim($_POST['copiloto_dni'] ?? '');
    
    // Validar teléfonos
    if (!empty($conductor_telefono) && !preg_match('/^[0-9+\-\s()]+$/', $conductor_telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de teléfono del conductor inválido'
        ]);
        return;
    }
    
    if (!empty($copiloto_telefono) && !preg_match('/^[0-9+\-\s()]+$/', $copiloto_telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de teléfono del copiloto inválido'
        ]);
        return;
    }
    
    // Validar DNI
    if (!empty($copiloto_dni) && !preg_match('/^[0-9]+$/', $copiloto_dni)) {
        echo json_encode([
            'success' => false,
            'message' => 'El DNI debe contener solo números'
        ]);
        return;
    }
    
    $personal = [
        'conductor_nombre' => trim($_POST['conductor_nombre'] ?? ''),
        'conductor_licencia' => strtoupper(trim($_POST['conductor_licencia'] ?? '')),
        'conductor_telefono' => $conductor_telefono,
        'copiloto_nombre' => trim($_POST['copiloto_nombre'] ?? ''),
        'copiloto_dni' => $copiloto_dni,
        'copiloto_telefono' => $copiloto_telefono
    ];
    
    // Validar longitud de campos de texto
    if (!empty($personal['conductor_nombre']) && strlen($personal['conductor_nombre']) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre del conductor es demasiado largo (máx. 200 caracteres)'
        ]);
        return;
    }
    
    if (!empty($personal['copiloto_nombre']) && strlen($personal['copiloto_nombre']) > 200) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre del copiloto es demasiado largo (máx. 200 caracteres)'
        ]);
        return;
    }
    
    // Observaciones
    $observaciones = trim($_POST['observaciones'] ?? '');
    if (strlen($observaciones) > 500) {
        echo json_encode([
            'success' => false,
            'message' => 'Las observaciones no pueden exceder los 500 caracteres'
        ]);
        return;
    }
    
    // Convertir arrays a JSON
    $datos_vehiculo_json = json_encode($datos_vehiculo, JSON_UNESCAPED_UNICODE);
    $documentacion_json = json_encode($documentacion, JSON_UNESCAPED_UNICODE);
    $personal_json = json_encode($personal, JSON_UNESCAPED_UNICODE);
    
    // Actualizar en la base de datos
    $sql = "UPDATE vehiculos_transporte 
            SET placa = :placa,
                modelo = :modelo,
                capacidad = :capacidad,
                datos_vehiculo = :datos_vehiculo,
                documentacion = :documentacion,
                personal = :personal,
                estado = :estado,
                observaciones = :observaciones
            WHERE id = :id AND activo = 1";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':placa', $placa);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':capacidad', $capacidad);
    $stmt->bindParam(':datos_vehiculo', $datos_vehiculo_json);
    $stmt->bindParam(':documentacion', $documentacion_json);
    $stmt->bindParam(':personal', $personal_json);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':observaciones', $observaciones);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Vehículo actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el vehículo'
        ]);
    }
}

function eliminarVehiculo($conexion) {
    // Validar ID
    $id = trim($_POST['id'] ?? '');
    if (empty($id) || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de vehículo no válido'
        ]);
        return;
    }
    
    // Verificar que el vehículo existe
    $sql_check = "SELECT id FROM vehiculos_transporte WHERE id = :id AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bindParam(':id', $id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Vehículo no encontrado'
        ]);
        return;
    }
    
    // Verificar si el vehículo está asignado a alguna ruta activa
    $sql_check_asignacion = "SELECT COUNT(*) as total 
                             FROM asignaciones_transporte 
                             WHERE vehiculo_id = :id AND activo = 1";
    $stmt_check_asignacion = $conexion->prepare($sql_check_asignacion);
    $stmt_check_asignacion->bindParam(':id', $id);
    $stmt_check_asignacion->execute();
    $asignacion = $stmt_check_asignacion->fetch(PDO::FETCH_ASSOC);
    
    if ($asignacion['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar. El vehículo está asignado a una ruta activa'
        ]);
        return;
    }
    
    // Eliminación ficticia: cambiar activo de 1 a 0
    $sql = "UPDATE vehiculos_transporte SET activo = 0 WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Vehículo eliminado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el vehículo'
        ]);
    }
}

function obtenerVehiculo($conexion) {
    // Validar ID
    $id = trim($_POST['id'] ?? '');
    if (empty($id) || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de vehículo no válido'
        ]);
        return;
    }
    
    // Obtener datos del vehículo
    $sql = "SELECT 
                id,
                placa,
                modelo,
                capacidad,
                datos_vehiculo,
                documentacion,
                personal,
                estado,
                observaciones
            FROM vehiculos_transporte 
            WHERE id = :id AND activo = 1";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($vehiculo) {
        // Decodificar JSON
        $vehiculo['datos_vehiculo'] = json_decode($vehiculo['datos_vehiculo'], true) ?: [];
        $vehiculo['documentacion'] = json_decode($vehiculo['documentacion'], true) ?: [];
        $vehiculo['personal'] = json_decode($vehiculo['personal'], true) ?: [];
        
        echo json_encode([
            'success' => true,
            'vehiculo' => $vehiculo
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Vehículo no encontrado'
        ]);
    }
}
?>