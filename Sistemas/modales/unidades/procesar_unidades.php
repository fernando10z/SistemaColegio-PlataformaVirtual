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
        case 'crear':
            $response = crearUnidad();
            break;
            
        case 'actualizar':
            $response = actualizarUnidad();
            break;
            
        case 'obtener':
            $response = obtenerUnidad();
            break;
            
        case 'eliminar':
            $response = eliminarUnidad();
            break;
            
        case 'obtener_lecciones':
            $response = obtenerLecciones();
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

function crearUnidad() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['curso_id', 'titulo', 'orden', 'estado'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    $conexion->beginTransaction();

    try {
        // Verificar que el curso existe
        $stmt = $conexion->prepare("SELECT id FROM cursos WHERE id = ?");
        $stmt->execute([$_POST['curso_id']]);
        if (!$stmt->fetch()) {
            throw new Exception('El curso seleccionado no existe');
        }

        // Preparar configuraciones
        $configuraciones = [
            'estado' => $_POST['estado'],
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null
        ];

        // Insertar unidad
        $stmt = $conexion->prepare("
            INSERT INTO unidades (
                curso_id, titulo, descripcion, orden, configuraciones, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $_POST['curso_id'],
            $_POST['titulo'],
            $_POST['descripcion'] ?? null,
            $_POST['orden'],
            json_encode($configuraciones)
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Unidad creada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function actualizarUnidad() {
    global $conexion;
    
    if (!isset($_POST['unidad_id'])) {
        throw new Exception('ID de la unidad no especificado');
    }

    $unidad_id = (int)$_POST['unidad_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que la unidad existe
        $stmt = $conexion->prepare("SELECT * FROM unidades WHERE id = ?");
        $stmt->execute([$unidad_id]);
        $unidad = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unidad) {
            throw new Exception('Unidad no encontrada');
        }

        // Preparar configuraciones
        $configuraciones = [
            'estado' => $_POST['estado'],
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => $_POST['fecha_fin'] ?? null
        ];

        // Actualizar unidad
        $stmt = $conexion->prepare("
            UPDATE unidades SET 
                curso_id = ?, titulo = ?, descripcion = ?, 
                orden = ?, configuraciones = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['curso_id'],
            $_POST['titulo'],
            $_POST['descripcion'] ?? null,
            $_POST['orden'],
            json_encode($configuraciones),
            $unidad_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Unidad actualizada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerUnidad() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la unidad no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT u.*, c.nombre as curso_nombre
        FROM unidades u
        INNER JOIN cursos c ON u.curso_id = c.id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $unidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$unidad) {
        throw new Exception('Unidad no encontrada');
    }

    // Decodificar JSON
    $unidad['configuraciones'] = json_decode($unidad['configuraciones'], true);

    return [
        'success' => true,
        'unidad' => $unidad
    ];
}

function eliminarUnidad() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la unidad no especificado');
    }

    $id = (int)$_POST['id'];

    $conexion->beginTransaction();

    try {
        // Verificar que la unidad existe
        $stmt = $conexion->prepare("SELECT id FROM unidades WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            throw new Exception('Unidad no encontrada');
        }

        // Eliminar lecciones asociadas
        $stmt = $conexion->prepare("DELETE FROM lecciones WHERE unidad_id = ?");
        $stmt->execute([$id]);

        // Eliminar unidad
        $stmt = $conexion->prepare("DELETE FROM unidades WHERE id = ?");
        $stmt->execute([$id]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Unidad eliminada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerLecciones() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de la unidad no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT id, titulo, orden, tipo
        FROM lecciones
        WHERE unidad_id = ?
        ORDER BY orden ASC
    ");
    $stmt->execute([$id]);
    $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'lecciones' => $lecciones
    ];
}
?>