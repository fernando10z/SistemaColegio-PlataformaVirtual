<?php
require_once '../../conexion/bd.php';
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_POST['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $accion = $_POST['accion'];

    switch ($accion) {
        case 'crear':
            $response = crearMenu();
            break;
            
        case 'actualizar':
            $response = actualizarMenu();
            break;
            
        case 'obtener':
            $response = obtenerMenu();
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

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

function crearMenu() {
    global $conexion;
    
    // Validaciones
    if (empty($_POST['fecha'])) {
        throw new Exception('La fecha es requerida');
    }
    
    if (empty($_POST['entrada']) || empty($_POST['plato_principal'])) {
        throw new Exception('Entrada y plato principal son requeridos');
    }
    
    $fecha = $_POST['fecha'];
    
    // Verificar duplicado
    $stmt = $conexion->prepare("SELECT id FROM menus_comedor WHERE fecha = ?");
    $stmt->execute([$fecha]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un menú para esta fecha');
    }
    
    // Preparar datos
    $configuracion = [
        'alergenos' => $_POST['alergenos'] ?? '',
        'info_nutricional' => $_POST['info_nutricional'] ?? ''
    ];
    
    $detalles = [
        'entrada' => trim($_POST['entrada']),
        'plato_principal' => trim($_POST['plato_principal']),
        'postre' => trim($_POST['postre'] ?? ''),
        'bebida' => trim($_POST['bebida'] ?? '')
    ];
    
    $disponibilidad = [
        'porciones_totales' => intval($_POST['porciones'] ?? 0),
        'porciones_disponibles' => intval($_POST['porciones'] ?? 0)
    ];
    
    // Insertar
    $stmt = $conexion->prepare("
        INSERT INTO menus_comedor (fecha, configuracion, detalles, disponibilidad)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $fecha,
        json_encode($configuracion, JSON_UNESCAPED_UNICODE),
        json_encode($detalles, JSON_UNESCAPED_UNICODE),
        json_encode($disponibilidad, JSON_UNESCAPED_UNICODE)
    ]);
    
    return [
        'success' => true,
        'message' => 'Menú creado exitosamente',
        'id' => $conexion->lastInsertId()
    ];
}

function actualizarMenu() {
    global $conexion;
    
    if (empty($_POST['id'])) {
        throw new Exception('ID de menú no especificado');
    }
    
    $id = intval($_POST['id']);
    
    // Verificar que existe
    $stmt = $conexion->prepare("SELECT id FROM menus_comedor WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('El menú no existe');
    }
    
    // Preparar datos
    $configuracion = [
        'alergenos' => $_POST['alergenos'] ?? '',
        'info_nutricional' => $_POST['info_nutricional'] ?? ''
    ];
    
    $detalles = [
        'entrada' => trim($_POST['entrada']),
        'plato_principal' => trim($_POST['plato_principal']),
        'postre' => trim($_POST['postre'] ?? ''),
        'bebida' => trim($_POST['bebida'] ?? '')
    ];
    
    $disponibilidad = [
        'porciones_totales' => intval($_POST['porciones'] ?? 0),
        'porciones_disponibles' => intval($_POST['porciones'] ?? 0)
    ];
    
    // Actualizar
    $stmt = $conexion->prepare("
        UPDATE menus_comedor SET 
            fecha = ?,
            configuracion = ?,
            detalles = ?,
            disponibilidad = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $_POST['fecha'],
        json_encode($configuracion, JSON_UNESCAPED_UNICODE),
        json_encode($detalles, JSON_UNESCAPED_UNICODE),
        json_encode($disponibilidad, JSON_UNESCAPED_UNICODE),
        $id
    ]);
    
    return [
        'success' => true,
        'message' => 'Menú actualizado exitosamente'
    ];
}

function obtenerMenu() {
    global $conexion;
    
    if (empty($_POST['id'])) {
        throw new Exception('ID no especificado');
    }
    
    $id = intval($_POST['id']);
    
    $stmt = $conexion->prepare("SELECT * FROM menus_comedor WHERE id = ?");
    $stmt->execute([$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$menu) {
        throw new Exception('Menú no encontrado');
    }
    
    $menu['configuracion'] = json_decode($menu['configuracion'], true) ?: [];
    $menu['detalles'] = json_decode($menu['detalles'], true) ?: [];
    $menu['disponibilidad'] = json_decode($menu['disponibilidad'], true) ?: [];
    
    return [
        'success' => true,
        'menu' => $menu
    ];
}