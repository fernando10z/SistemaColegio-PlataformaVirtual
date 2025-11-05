<?php
// modales/menus/procesar_menu.php
require_once '../../conexion/bd.php';
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Lima');

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
            
        case 'eliminar':
            $response = eliminarMenu();
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

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;

/**
 * CREAR MENÚ
 */
function crearMenu() {
    global $conexion;
    
    // ========== VALIDACIONES ==========
    if (empty($_POST['fecha'])) {
        throw new Exception('La fecha es requerida');
    }
    
    if (empty($_POST['entrada_nombre'])) {
        throw new Exception('El nombre de la entrada es requerido');
    }
    
    if (empty($_POST['principal_nombre'])) {
        throw new Exception('El nombre del plato principal es requerido');
    }
    
    if (empty($_POST['bebida_nombre'])) {
        throw new Exception('El nombre de la bebida es requerido');
    }
    
    if (empty($_POST['tipo_menu'])) {
        throw new Exception('El tipo de menú es requerido');
    }
    
    if (!isset($_POST['precio']) || floatval($_POST['precio']) <= 0) {
        throw new Exception('El precio debe ser mayor a cero');
    }
    
    if (!isset($_POST['cantidad_disponible']) || intval($_POST['cantidad_disponible']) <= 0) {
        throw new Exception('La cantidad disponible debe ser mayor a cero');
    }
    
    $fecha = $_POST['fecha'];
    
    // Validar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception('Formato de fecha inválido');
    }
    
    // Validar que la fecha no sea pasada
    $fechaObj = new DateTime($fecha);
    $hoy = new DateTime();
    $hoy->setTime(0, 0, 0);
    
    if ($fechaObj < $hoy) {
        throw new Exception('La fecha no puede ser anterior a hoy');
    }
    
    // Verificar duplicado
    $stmt = $conexion->prepare("SELECT id FROM menus_comedor WHERE fecha = ?");
    $stmt->execute([$fecha]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe un menú para la fecha: ' . date('d/m/Y', strtotime($fecha)));
    }
    
    // Validar horas
    $hora_inicio = $_POST['hora_inicio'] ?? '12:00';
    $hora_fin = $_POST['hora_fin'] ?? '14:00';
    
    if ($hora_fin <= $hora_inicio) {
        throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
    }
    
    // ========== PREPARAR DATOS ==========
    
    // Preparar platos
    $platos = [];
    
    // Entrada
    if (!empty($_POST['entrada_nombre'])) {
        $platos[] = [
            'tipo' => 'ENTRADA',
            'nombre' => sanitizeInput($_POST['entrada_nombre']),
            'calorias' => intval($_POST['entrada_calorias'] ?? 0),
            'ingredientes' => sanitizeInput($_POST['entrada_ingredientes'] ?? '')
        ];
    }
    
    // Plato Principal (obligatorio)
    $platos[] = [
        'tipo' => 'PRINCIPAL',
        'nombre' => sanitizeInput($_POST['principal_nombre']),
        'calorias' => intval($_POST['principal_calorias'] ?? 0),
        'ingredientes' => sanitizeInput($_POST['principal_ingredientes'] ?? '')
    ];
    
    // Guarnición
    if (!empty($_POST['guarnicion_nombre'])) {
        $platos[] = [
            'tipo' => 'GUARNICION',
            'nombre' => sanitizeInput($_POST['guarnicion_nombre']),
            'calorias' => intval($_POST['guarnicion_calorias'] ?? 0),
            'ingredientes' => ''
        ];
    }
    
    // Postre
    if (!empty($_POST['postre_nombre'])) {
        $platos[] = [
            'tipo' => 'POSTRE',
            'nombre' => sanitizeInput($_POST['postre_nombre']),
            'calorias' => intval($_POST['postre_calorias'] ?? 0),
            'ingredientes' => ''
        ];
    }
    
    // Bebida (obligatorio)
    $platos[] = [
        'tipo' => 'BEBIDA',
        'nombre' => sanitizeInput($_POST['bebida_nombre']),
        'calorias' => intval($_POST['bebida_calorias'] ?? 0),
        'ingredientes' => ''
    ];
    
    // Calcular total de calorías
    $total_calorias = array_sum(array_column($platos, 'calorias'));
    
    // Preparar restricciones
    $restricciones = [];
    if (isset($_POST['restricciones']) && is_array($_POST['restricciones'])) {
        $restricciones = $_POST['restricciones'];
    }
    
    // Preparar alérgenos
    $alergenos = [];
    if (!empty($_POST['alergenos'])) {
        $alergenos = array_map('trim', explode(',', $_POST['alergenos']));
    }
    
    // JSON Configuración
    $configuracion = [
        'tipo_menu' => $_POST['tipo_menu'],
        'precio' => floatval($_POST['precio']),
        'hora_inicio' => $hora_inicio,
        'hora_fin' => $hora_fin,
        'limite_pedidos_usuario' => !empty($_POST['limite_pedidos']) ? intval($_POST['limite_pedidos']) : null,
        'restricciones' => $restricciones,
        'alergenos' => $alergenos,
        'activo' => true,
        'visible' => true
    ];
    
    // JSON Detalles
    $detalles = [
        'platos' => $platos,
        'descripcion_general' => sanitizeInput($_POST['descripcion_general'] ?? ''),
        'total_platos' => count($platos),
        'calorias_totales' => $total_calorias
    ];
    
    // JSON Disponibilidad
    $cantidad_disponible = intval($_POST['cantidad_disponible']);
    $disponibilidad = [
        'porciones_totales' => $cantidad_disponible,
        'porciones_disponibles' => $cantidad_disponible,
        'porciones_reservadas' => 0,
        'estado' => 'DISPONIBLE',
        'ultima_actualizacion' => date('Y-m-d H:i:s')
    ];
    
    // ========== INSERTAR EN BD ==========
    $conexion->beginTransaction();
    
    try {
        $stmt = $conexion->prepare("
            INSERT INTO menus_comedor (fecha, configuracion, detalles, disponibilidad, imagen_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $fecha,
            json_encode($configuracion, JSON_UNESCAPED_UNICODE),
            json_encode($detalles, JSON_UNESCAPED_UNICODE),
            json_encode($disponibilidad, JSON_UNESCAPED_UNICODE),
            $_POST['imagen_url'] ?? ''
        ]);
        
        $menu_id = $conexion->lastInsertId();
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Menú creado exitosamente para ' . date('d/m/Y', strtotime($fecha)),
            'menu_id' => $menu_id
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception('Error al crear el menú: ' . $e->getMessage());
    }
}

/**
 * ACTUALIZAR MENÚ
 */
function actualizarMenu() {
    global $conexion;
    
    // CORRECCIÓN: Buscar 'menu_id' en lugar de 'id'
    if (empty($_POST['menu_id'])) {
        throw new Exception('ID de menú no especificado');
    }
    
    $id = intval($_POST['menu_id']); // <-- Cambiar de 'id' a 'menu_id'
    
    // Verificar que existe
    $stmt = $conexion->prepare("SELECT * FROM menus_comedor WHERE id = ?");
    $stmt->execute([$id]);
    $menu_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$menu_actual) {
        throw new Exception('El menú no existe');
    }
    
    // Validaciones básicas
    if (empty($_POST['entrada_nombre'])) {
        throw new Exception('El nombre de la entrada es requerido');
    }
    
    if (empty($_POST['principal_nombre'])) {
        throw new Exception('El nombre del plato principal es requerido');
    }
    
    if (empty($_POST['bebida_nombre'])) {
        throw new Exception('El nombre de la bebida es requerido');
    }
    
    // Preparar platos
    $platos = [];
    
    // Entrada
    if (!empty($_POST['entrada_nombre'])) {
        $platos[] = [
            'tipo' => 'ENTRADA',
            'nombre' => sanitizeInput($_POST['entrada_nombre']),
            'calorias' => intval($_POST['entrada_calorias'] ?? 0),
            'ingredientes' => sanitizeInput($_POST['entrada_ingredientes'] ?? '')
        ];
    }
    
    // Principal
    $platos[] = [
        'tipo' => 'PRINCIPAL',
        'nombre' => sanitizeInput($_POST['principal_nombre']),
        'calorias' => intval($_POST['principal_calorias'] ?? 0),
        'ingredientes' => sanitizeInput($_POST['principal_ingredientes'] ?? '')
    ];
    
    // Guarnición
    if (!empty($_POST['guarnicion_nombre'])) {
        $platos[] = [
            'tipo' => 'GUARNICION',
            'nombre' => sanitizeInput($_POST['guarnicion_nombre']),
            'calorias' => intval($_POST['guarnicion_calorias'] ?? 0),
            'ingredientes' => ''
        ];
    }
    
    // Postre
    if (!empty($_POST['postre_nombre'])) {
        $platos[] = [
            'tipo' => 'POSTRE',
            'nombre' => sanitizeInput($_POST['postre_nombre']),
            'calorias' => intval($_POST['postre_calorias'] ?? 0),
            'ingredientes' => ''
        ];
    }
    
    // Bebida
    $platos[] = [
        'tipo' => 'BEBIDA',
        'nombre' => sanitizeInput($_POST['bebida_nombre']),
        'calorias' => intval($_POST['bebida_calorias'] ?? 0),
        'ingredientes' => ''
    ];
    
    $total_calorias = array_sum(array_column($platos, 'calorias'));
    
    // Restricciones
    $restricciones = [];
    if (isset($_POST['restricciones']) && is_array($_POST['restricciones'])) {
        $restricciones = $_POST['restricciones'];
    }
    
    // Alérgenos
    $alergenos = [];
    if (!empty($_POST['alergenos'])) {
        $alergenos = array_map('trim', explode(',', $_POST['alergenos']));
    }
    
    // Estado del menú
    $estado = isset($_POST['estado_menu']) ? $_POST['estado_menu'] : 'DISPONIBLE';
    if (!in_array($estado, ['DISPONIBLE', 'AGOTADO', 'CANCELADO'])) {
        $estado = 'DISPONIBLE';
    }
    
    // Configuración
    $configuracion = [
        'tipo_menu' => $_POST['tipo_menu'] ?? 'REGULAR',
        'precio' => floatval($_POST['precio'] ?? 0),
        'hora_inicio' => $_POST['hora_inicio'] ?? '12:00',
        'hora_fin' => $_POST['hora_fin'] ?? '14:00',
        'limite_pedidos_usuario' => !empty($_POST['limite_pedidos']) ? intval($_POST['limite_pedidos']) : null,
        'restricciones' => $restricciones,
        'alergenos' => $alergenos,
        'activo' => true,
        'visible' => true
    ];
    
    // Detalles
    $detalles = [
        'platos' => $platos,
        'descripcion_general' => sanitizeInput($_POST['descripcion_general'] ?? ''),
        'total_platos' => count($platos),
        'calorias_totales' => $total_calorias
    ];
    
    // Disponibilidad
    $disponibilidad_actual = json_decode($menu_actual['disponibilidad'], true) ?: [];
    $cantidad_nueva = intval($_POST['cantidad_disponible'] ?? $disponibilidad_actual['porciones_totales'] ?? 0);
    
    $disponibilidad = [
        'porciones_totales' => $cantidad_nueva,
        'porciones_disponibles' => $cantidad_nueva,
        'porciones_reservadas' => $disponibilidad_actual['porciones_reservadas'] ?? 0,
        'estado' => $estado,
        'ultima_actualizacion' => date('Y-m-d H:i:s')
    ];
    
    // Transacción
    $conexion->beginTransaction();
    
    try {
        $stmt = $conexion->prepare("
            UPDATE menus_comedor SET 
                fecha = ?,
                configuracion = ?,
                detalles = ?,
                disponibilidad = ?,
                imagen_url = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['fecha'] ?? $menu_actual['fecha'],
            json_encode($configuracion, JSON_UNESCAPED_UNICODE),
            json_encode($detalles, JSON_UNESCAPED_UNICODE),
            json_encode($disponibilidad, JSON_UNESCAPED_UNICODE),
            $_POST['imagen_url'] ?? $menu_actual['imagen_url'],
            $id
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Menú actualizado exitosamente'
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception('Error al actualizar: ' . $e->getMessage());
    }
}

/**
 * OBTENER MENÚ
 */
function obtenerMenu() {
    global $conexion;
    
    // Aceptar tanto 'id' como 'menu_id'
    $id = null;
    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
    } elseif (!empty($_POST['menu_id'])) {
        $id = intval($_POST['menu_id']);
    }
    
    if (!$id) {
        throw new Exception('ID no especificado');
    }
    
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

/**
 * ELIMINAR MENÚ
 */
function eliminarMenu() {
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
    
    // Verificar que no tenga reservas
    $disponibilidad = json_decode($menu['disponibilidad'], true);
    if (isset($disponibilidad['porciones_reservadas']) && $disponibilidad['porciones_reservadas'] > 0) {
        throw new Exception('No se puede eliminar un menú con reservas activas');
    }
    
    $conexion->beginTransaction();
    
    try {
        $stmt = $conexion->prepare("DELETE FROM menus_comedor WHERE id = ?");
        $stmt->execute([$id]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Menú eliminado exitosamente'
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception('Error al eliminar: ' . $e->getMessage());
    }
}

/**
 * FUNCIONES AUXILIARES
 */
function sanitizeInput($data) {
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>