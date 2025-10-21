<?php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            crearItem($conexion);
            break;
        case 'actualizar':
            actualizarItem($conexion);
            break;
        case 'eliminar':
            eliminarItem($conexion);
            break;
        case 'obtener':
            obtenerItem($conexion);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function crearItem($conexion) {
    // Validar campos obligatorios
    if (empty($_POST['nombre_producto']) || empty($_POST['tipo']) || 
        !isset($_POST['stock_actual']) || empty($_POST['fecha_ingreso'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        return;
    }

    // Validar y sanitizar datos
    $nombre_producto = trim($_POST['nombre_producto']);
    $tipo = $_POST['tipo'];
    $stock_actual = floatval($_POST['stock_actual']);
    $proveedor = !empty($_POST['proveedor']) ? trim($_POST['proveedor']) : null;
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $observaciones = !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
    
    // Validar tipo enum
    $tipos_validos = ['MEDICAMENTO', 'MATERIAL_CURACION', 'EQUIPO_MEDICO'];
    if (!in_array($tipo, $tipos_validos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de producto no válido']);
        return;
    }

    // Validar stock
    if ($stock_actual < 0) {
        echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
        return;
    }

    // Validar y procesar JSON de datos_item
    $datos_item = null;
    if (!empty($_POST['datos_item'])) {
        $datos_item_raw = trim($_POST['datos_item']);
        $json_decoded = json_decode($datos_item_raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Formato JSON de Datos Item inválido']);
            return;
        }
        $datos_item = $datos_item_raw;
    }

    // Validar y procesar JSON de inventario
    $inventario = null;
    if (!empty($_POST['inventario'])) {
        $inventario_raw = trim($_POST['inventario']);
        $json_decoded = json_decode($inventario_raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Formato JSON de Inventario inválido']);
            return;
        }
        $inventario = $inventario_raw;
    }

    // Validar fecha
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_ingreso);
    if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_ingreso) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        return;
    }

    // Validar que la fecha no sea futura
    if ($fecha_obj > new DateTime()) {
        echo json_encode(['success' => false, 'message' => 'La fecha de ingreso no puede ser futura']);
        return;
    }

    // Insertar en base de datos
    $sql = "INSERT INTO inventario_enfermeria 
            (nombre_producto, tipo, datos_item, inventario, stock_actual, proveedor, observaciones, fecha_ingreso, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
    
    $stmt = $conexion->prepare($sql);
    $result = $stmt->execute([
        $nombre_producto,
        $tipo,
        $datos_item,
        $inventario,
        $stock_actual,
        $proveedor,
        $observaciones,
        $fecha_ingreso
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item agregado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar el item']);
    }
}

function actualizarItem($conexion) {
    // Validar campos obligatorios
    if (empty($_POST['id']) || empty($_POST['nombre_producto']) || empty($_POST['tipo']) || 
        !isset($_POST['stock_actual']) || empty($_POST['fecha_ingreso'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        return;
    }

    // Validar y sanitizar datos
    $id = intval($_POST['id']);
    $nombre_producto = trim($_POST['nombre_producto']);
    $tipo = $_POST['tipo'];
    $stock_actual = floatval($_POST['stock_actual']);
    $proveedor = !empty($_POST['proveedor']) ? trim($_POST['proveedor']) : null;
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $observaciones = !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
    
    // Validar tipo enum
    $tipos_validos = ['MEDICAMENTO', 'MATERIAL_CURACION', 'EQUIPO_MEDICO'];
    if (!in_array($tipo, $tipos_validos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de producto no válido']);
        return;
    }

    // Validar stock
    if ($stock_actual < 0) {
        echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
        return;
    }

    // Validar y procesar JSON de datos_item
    $datos_item = null;
    if (!empty($_POST['datos_item'])) {
        $datos_item_raw = trim($_POST['datos_item']);
        $json_decoded = json_decode($datos_item_raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Formato JSON de Datos Item inválido']);
            return;
        }
        $datos_item = $datos_item_raw;
    }

    // Validar y procesar JSON de inventario
    $inventario = null;
    if (!empty($_POST['inventario'])) {
        $inventario_raw = trim($_POST['inventario']);
        $json_decoded = json_decode($inventario_raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Formato JSON de Inventario inválido']);
            return;
        }
        $inventario = $inventario_raw;
    }

    // Validar fecha
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_ingreso);
    if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_ingreso) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        return;
    }

    // Validar que la fecha no sea futura
    if ($fecha_obj > new DateTime()) {
        echo json_encode(['success' => false, 'message' => 'La fecha de ingreso no puede ser futura']);
        return;
    }

    // Verificar que el item existe y está activo
    $sql_check = "SELECT id FROM inventario_enfermeria WHERE id = ? AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$id]);
    
    if ($stmt_check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'El item no existe o está inactivo']);
        return;
    }

    // Actualizar en base de datos
    $sql = "UPDATE inventario_enfermeria SET 
            nombre_producto = ?, 
            tipo = ?, 
            datos_item = ?, 
            inventario = ?, 
            stock_actual = ?, 
            proveedor = ?, 
            observaciones = ?, 
            fecha_ingreso = ?
            WHERE id = ? AND activo = 1";
    
    $stmt = $conexion->prepare($sql);
    $result = $stmt->execute([
        $nombre_producto,
        $tipo,
        $datos_item,
        $inventario,
        $stock_actual,
        $proveedor,
        $observaciones,
        $fecha_ingreso,
        $id
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el item']);
    }
}

function eliminarItem($conexion) {
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        return;
    }

    $id = intval($_POST['id']);

    // Verificar que el item existe
    $sql_check = "SELECT id FROM inventario_enfermeria WHERE id = ? AND activo = 1";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$id]);
    
    if ($stmt_check->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'El item no existe o ya está inactivo']);
        return;
    }

    // Soft delete: marcar como inactivo
    $sql = "UPDATE inventario_enfermeria SET activo = 0 WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el item']);
    }
}

function obtenerItem($conexion) {
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        return;
    }

    $id = intval($_POST['id']);

    $sql = "SELECT * FROM inventario_enfermeria WHERE id = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
    }
}
?>