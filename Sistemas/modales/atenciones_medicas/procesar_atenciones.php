<?php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            crearAtencion($conexion, $usuario_id);
            break;
            
        case 'administrar_medicamento':
            administrarMedicamento($conexion, $usuario_id);
            break;
            
        case 'obtener':
            obtenerAtencion($conexion);
            break;
            
        case 'obtener_atenciones_estudiante':
            obtenerAtencionesEstudiante($conexion);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// ============================================================================
// FUNCIÓN: CREAR ATENCIÓN MÉDICA
// ============================================================================
function crearAtencion($conexion, $usuario_id) {
    // Validar campos requeridos
    $campos_requeridos = ['estudiante_id', 'fecha_atencion', 'hora_atencion', 'tipo_atencion', 'motivo_consulta'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            echo json_encode(['success' => false, 'message' => "El campo $campo es requerido"]);
            exit();
        }
    }

    $estudiante_id = intval($_POST['estudiante_id']);
    $fecha_atencion = $_POST['fecha_atencion'];
    $hora_atencion = $_POST['hora_atencion'];
    $tipo_atencion = trim($_POST['tipo_atencion']);
    $motivo_consulta = trim($_POST['motivo_consulta']);
    
    // Decodificar JSON recibidos
    $signos_vitales = $_POST['signos_vitales'] ?? null;
    $tratamiento = $_POST['tratamiento'] ?? null;
    $contacto_apoderado = $_POST['contacto_apoderado'] ?? null;

    // Validar que sean JSON válidos
    if ($signos_vitales) {
        $signos_vitales_array = json_decode($signos_vitales, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Error en formato de signos vitales']);
            exit();
        }
        // Limpiar valores nulos o vacíos
        $signos_vitales_array = array_filter($signos_vitales_array, function($value) {
            return $value !== null && $value !== '';
        });
        $signos_vitales = !empty($signos_vitales_array) ? json_encode($signos_vitales_array) : null;
    }

    if ($tratamiento) {
        $tratamiento_array = json_decode($tratamiento, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Error en formato de tratamiento']);
            exit();
        }
        // Limpiar valores nulos o vacíos
        $tratamiento_array = array_filter($tratamiento_array, function($value) {
            return $value !== null && $value !== '';
        });
        $tratamiento = !empty($tratamiento_array) ? json_encode($tratamiento_array) : null;
    }

    if ($contacto_apoderado) {
        $contacto_array = json_decode($contacto_apoderado, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Error en formato de contacto apoderado']);
            exit();
        }
        $contacto_apoderado = json_encode($contacto_array);
    }

    // Validar que el estudiante exista
    $stmt_validar = $conexion->prepare("SELECT id FROM estudiantes WHERE id = ? AND activo = 1");
    $stmt_validar->execute([$estudiante_id]);
    if (!$stmt_validar->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Estudiante no válido']);
        exit();
    }

    // Insertar atención médica
    $sql = "INSERT INTO atenciones_medicas 
            (estudiante_id, fecha_atencion, hora_atencion, tipo_atencion, motivo_consulta, 
             signos_vitales, tratamiento, contacto_apoderado, enfermero_atiende) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([
        $estudiante_id,
        $fecha_atencion,
        $hora_atencion,
        $tipo_atencion,
        $motivo_consulta,
        $signos_vitales,
        $tratamiento,
        $contacto_apoderado,
        $usuario_id
    ]);

    if ($resultado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Atención médica registrada exitosamente',
            'id' => $conexion->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar la atención médica']);
    }
}

// ============================================================================
// FUNCIÓN: ADMINISTRAR MEDICAMENTO
// ============================================================================
function administrarMedicamento($conexion, $usuario_id) {
    // Validar campos requeridos
    if (empty($_POST['atencion_id']) || empty($_POST['producto_id']) || empty($_POST['cantidad'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        exit();
    }

    $atencion_id = intval($_POST['atencion_id']);
    $producto_id = intval($_POST['producto_id']);
    $cantidad = floatval($_POST['cantidad']);
    
    $tratamiento = $_POST['tratamiento'] ?? null;
    $autorizaciones = $_POST['autorizaciones'] ?? null;

    // Validar que la cantidad sea positiva
    if ($cantidad <= 0) {
        echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a 0']);
        exit();
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    try {
        // Verificar que la atención exista
        $stmt_atencion = $conexion->prepare("SELECT id FROM atenciones_medicas WHERE id = ?");
        $stmt_atencion->execute([$atencion_id]);
        if (!$stmt_atencion->fetch()) {
            throw new Exception('Atención médica no encontrada');
        }

        // Verificar stock disponible
        $stmt_producto = $conexion->prepare("SELECT stock_actual, nombre_producto FROM inventario_enfermeria WHERE id = ? AND activo = 1");
        $stmt_producto->execute([$producto_id]);
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            throw new Exception('Producto no encontrado o inactivo');
        }

        if ($producto['stock_actual'] < $cantidad) {
            throw new Exception('Stock insuficiente. Disponible: ' . $producto['stock_actual']);
        }

        // Actualizar stock del producto
        $nuevo_stock = $producto['stock_actual'] - $cantidad;
        $stmt_actualizar_stock = $conexion->prepare("UPDATE inventario_enfermeria SET stock_actual = ? WHERE id = ?");
        $stmt_actualizar_stock->execute([$nuevo_stock, $producto_id]);

        // Obtener tratamiento y autorizaciones actuales
        $stmt_datos = $conexion->prepare("SELECT tratamiento, autorizaciones FROM atenciones_medicas WHERE id = ?");
        $stmt_datos->execute([$atencion_id]);
        $datos_actuales = $stmt_datos->fetch(PDO::FETCH_ASSOC);

        // Procesar tratamiento
        $tratamiento_actual = $datos_actuales['tratamiento'] ? json_decode($datos_actuales['tratamiento'], true) : [];
        $tratamiento_nuevo = $tratamiento ? json_decode($tratamiento, true) : [];
        
        // Si hay tratamiento actual, convertirlo en array si no lo es
        if (!isset($tratamiento_actual[0])) {
            $tratamiento_actual = !empty($tratamiento_actual) ? [$tratamiento_actual] : [];
        }
        
        // Agregar nuevo tratamiento
        $tratamiento_actual[] = $tratamiento_nuevo;
        $tratamiento_final = json_encode($tratamiento_actual);

        // Procesar autorizaciones
        $autorizaciones_actual = $datos_actuales['autorizaciones'] ? json_decode($datos_actuales['autorizaciones'], true) : [];
        $autorizaciones_nueva = $autorizaciones ? json_decode($autorizaciones, true) : [];
        
        // Si hay autorización actual, convertirla en array si no lo es
        if (!isset($autorizaciones_actual[0])) {
            $autorizaciones_actual = !empty($autorizaciones_actual) ? [$autorizaciones_actual] : [];
        }
        
        // Agregar nueva autorización
        $autorizaciones_actual[] = $autorizaciones_nueva;
        $autorizaciones_final = json_encode($autorizaciones_actual);

        // Actualizar atención médica con tratamiento y autorizaciones
        $sql_actualizar = "UPDATE atenciones_medicas 
                          SET tratamiento = ?, autorizaciones = ? 
                          WHERE id = ?";
        $stmt_actualizar = $conexion->prepare($sql_actualizar);
        $stmt_actualizar->execute([$tratamiento_final, $autorizaciones_final, $atencion_id]);

        // Registrar movimiento en inventario (opcional - actualizar campo inventario JSON)
        $movimiento_inventario = [
            'fecha' => date('Y-m-d H:i:s'),
            'tipo' => 'SALIDA',
            'cantidad' => $cantidad,
            'motivo' => 'Administración médica',
            'atencion_id' => $atencion_id,
            'usuario_id' => $usuario_id,
            'stock_anterior' => $producto['stock_actual'],
            'stock_nuevo' => $nuevo_stock
        ];

        $stmt_inv_actual = $conexion->prepare("SELECT inventario FROM inventario_enfermeria WHERE id = ?");
        $stmt_inv_actual->execute([$producto_id]);
        $inv_data = $stmt_inv_actual->fetch(PDO::FETCH_ASSOC);
        
        $inventario_array = $inv_data['inventario'] ? json_decode($inv_data['inventario'], true) : [];
        if (!isset($inventario_array[0])) {
            $inventario_array = !empty($inventario_array) ? [$inventario_array] : [];
        }
        $inventario_array[] = $movimiento_inventario;
        
        $stmt_actualizar_inv = $conexion->prepare("UPDATE inventario_enfermeria SET inventario = ? WHERE id = ?");
        $stmt_actualizar_inv->execute([json_encode($inventario_array), $producto_id]);

        // Confirmar transacción
        $conexion->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Medicamento administrado exitosamente. Stock actualizado.',
            'nuevo_stock' => $nuevo_stock
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conexion->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ============================================================================
// FUNCIÓN: OBTENER ATENCIÓN MÉDICA
// ============================================================================
function obtenerAtencion($conexion) {
    if (empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        exit();
    }

    $id = intval($_POST['id']);

    $sql = "SELECT 
                am.*,
                e.codigo_estudiante,
                e.nombres as estudiante_nombres,
                e.apellidos as estudiante_apellidos,
                e.foto_url as estudiante_foto,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                u.nombres as enfermero_nombres,
                u.apellidos as enfermero_apellidos
            FROM atenciones_medicas am
            INNER JOIN estudiantes e ON am.estudiante_id = e.id
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            LEFT JOIN usuarios u ON am.enfermero_atiende = u.id
            WHERE am.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $atencion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($atencion) {
        echo json_encode([
            'success' => true,
            'atencion' => $atencion
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Atención no encontrada']);
    }
}

// ============================================================================
// FUNCIÓN: OBTENER ATENCIONES DE UN ESTUDIANTE
// ============================================================================
function obtenerAtencionesEstudiante($conexion) {
    if (empty($_POST['estudiante_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante no proporcionado']);
        exit();
    }

    $estudiante_id = intval($_POST['estudiante_id']);

    $sql = "SELECT 
                id,
                fecha_atencion,
                hora_atencion,
                tipo_atencion,
                motivo_consulta
            FROM atenciones_medicas
            WHERE estudiante_id = ?
            ORDER BY fecha_atencion DESC, hora_atencion DESC
            LIMIT 50";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$estudiante_id]);
    $atenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($atenciones) {
        echo json_encode([
            'success' => true,
            'atenciones' => $atenciones
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'atenciones' => [],
            'message' => 'No hay atenciones registradas para este estudiante'
        ]);
    }
}
?>