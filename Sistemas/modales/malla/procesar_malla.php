<?php
// modales/malla/procesar_malla.php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            echo json_encode(crearAsignacion());
            break;
        case 'obtener':
            echo json_encode(obtenerAsignacion());
            break;
        case 'actualizar':
            echo json_encode(actualizarAsignacion());
            break;
        case 'obtener_competencias':
            echo json_encode(obtenerCompetencias());
            break;
        case 'actualizar_competencias':
            echo json_encode(actualizarCompetencias());
            break;
        case 'eliminar':
            echo json_encode(eliminarAsignacion());
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    error_log("Error en procesar_malla.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function crearAsignacion() {
    global $conexion;
    
    // Validar datos requeridos
    $nivel_id = $_POST['nivel_id'] ?? '';
    $grado = $_POST['grado'] ?? '';
    $area_id = $_POST['area_id'] ?? '';
    $horas_semanales = $_POST['horas_semanales'] ?? '';
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? '';
    
    if (empty($nivel_id) || empty($grado) || empty($area_id) || empty($horas_semanales) || empty($periodo_academico_id)) {
        return ['success' => false, 'message' => 'Todos los campos son requeridos'];
    }
    
    // Validar rango de horas
    if ($horas_semanales < 1 || $horas_semanales > 10) {
        return ['success' => false, 'message' => 'Las horas semanales deben estar entre 1 y 10'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Verificar que no exista la misma asignación
        $stmt_check = $conexion->prepare("
            SELECT id FROM malla_curricular 
            WHERE nivel_id = ? AND grado = ? AND area_id = ? AND periodo_academico_id = ? AND activo = 1
        ");
        $stmt_check->execute([$nivel_id, $grado, $area_id, $periodo_academico_id]);
        
        if ($stmt_check->fetch()) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'Ya existe una asignación de esta área para el mismo nivel, grado y período'];
        }
        
        // Procesar competencias
        $competencias = [];
        if (isset($_POST['competencias']) && is_array($_POST['competencias'])) {
            $competencias = array_filter($_POST['competencias'], function($comp) {
                return !empty(trim($comp));
            });
        }
        
        $competencias_json = !empty($competencias) ? json_encode(array_values($competencias)) : null;
        
        // Insertar nueva asignación
        $stmt = $conexion->prepare("
            INSERT INTO malla_curricular (nivel_id, grado, area_id, horas_semanales, competencias_grado, periodo_academico_id, activo) 
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $nivel_id,
            $grado,
            $area_id,
            $horas_semanales,
            $competencias_json,
            $periodo_academico_id
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => 'Área curricular asignada correctamente',
            'id' => $conexion->lastInsertId()
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al crear asignación: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al crear la asignación'];
    }
}

function obtenerAsignacion() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT mc.*, 
                   ne.nombre as nivel_nombre, ne.codigo as nivel_codigo,
                   ac.nombre as area_nombre, ac.codigo as area_codigo,
                   pa.nombre as periodo_nombre, pa.anio as periodo_anio
            FROM malla_curricular mc
            INNER JOIN niveles_educativos ne ON mc.nivel_id = ne.id
            INNER JOIN areas_curriculares ac ON mc.area_id = ac.id
            INNER JOIN periodos_academicos pa ON mc.periodo_academico_id = pa.id
            WHERE mc.id = ?
        ");
        
        $stmt->execute([$id]);
        $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignacion) {
            return ['success' => false, 'message' => 'Asignación no encontrada'];
        }
        
        return [
            'success' => true,
            'asignacion' => $asignacion
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener asignación: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener la asignación'];
    }
}

function actualizarAsignacion() {
    global $conexion;
    
    // Validar datos requeridos
    $id = $_POST['id'] ?? '';
    $nivel_id = $_POST['nivel_id'] ?? '';
    $grado = $_POST['grado'] ?? '';
    $area_id = $_POST['area_id'] ?? '';
    $horas_semanales = $_POST['horas_semanales'] ?? '';
    $periodo_academico_id = $_POST['periodo_academico_id'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($id) || empty($nivel_id) || empty($grado) || empty($area_id) || empty($horas_semanales) || empty($periodo_academico_id)) {
        return ['success' => false, 'message' => 'Todos los campos son requeridos'];
    }
    
    // Validar rango de horas
    if ($horas_semanales < 1 || $horas_semanales > 10) {
        return ['success' => false, 'message' => 'Las horas semanales deben estar entre 1 y 10'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Verificar que no exista otra asignación igual (excluyendo la actual)
        $stmt_check = $conexion->prepare("
            SELECT id FROM malla_curricular 
            WHERE nivel_id = ? AND grado = ? AND area_id = ? AND periodo_academico_id = ? AND activo = 1 AND id != ?
        ");
        $stmt_check->execute([$nivel_id, $grado, $area_id, $periodo_academico_id, $id]);
        
        if ($stmt_check->fetch()) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'Ya existe otra asignación de esta área para el mismo nivel, grado y período'];
        }
        
        // Actualizar asignación
        $stmt = $conexion->prepare("
            UPDATE malla_curricular 
            SET nivel_id = ?, grado = ?, area_id = ?, horas_semanales = ?, periodo_academico_id = ?, activo = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $nivel_id,
            $grado,
            $area_id,
            $horas_semanales,
            $periodo_academico_id,
            $activo,
            $id
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true, 
            'message' => 'Asignación actualizada correctamente'
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al actualizar asignación: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al actualizar la asignación'];
    }
}

function obtenerCompetencias() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $stmt = $conexion->prepare("
            SELECT mc.*, 
                   ne.nombre as nivel_nombre, ne.codigo as nivel_codigo,
                   ac.nombre as area_nombre, ac.codigo as area_codigo,
                   pa.nombre as periodo_nombre, pa.anio as periodo_anio
            FROM malla_curricular mc
            INNER JOIN niveles_educativos ne ON mc.nivel_id = ne.id
            INNER JOIN areas_curriculares ac ON mc.area_id = ac.id
            INNER JOIN periodos_academicos pa ON mc.periodo_academico_id = pa.id
            WHERE mc.id = ?
        ");
        
        $stmt->execute([$id]);
        $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignacion) {
            return ['success' => false, 'message' => 'Asignación no encontrada'];
        }
        
        return [
            'success' => true,
            'asignacion' => $asignacion
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener competencias: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al obtener las competencias'];
    }
}

function actualizarCompetencias() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    $competencias_raw = $_POST['competencias'] ?? '[]';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        // Decodificar competencias
        $competencias = json_decode($competencias_raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Formato de competencias inválido'];
        }
        
        // Filtrar competencias vacías
        $competencias_filtradas = array_filter($competencias, function($comp) {
            return !empty(trim($comp));
        });
        
        $competencias_json = !empty($competencias_filtradas) ? json_encode(array_values($competencias_filtradas)) : null;
        
        $conexion->beginTransaction();
        
        // Actualizar competencias
        $stmt = $conexion->prepare("
            UPDATE malla_curricular 
            SET competencias_grado = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$competencias_json, $id]);
        
        $conexion->commit();
        
        $mensaje = empty($competencias_filtradas) 
            ? 'Competencias removidas correctamente'
            : 'Competencias actualizadas correctamente (' . count($competencias_filtradas) . ' competencias)';
        
        return [
            'success' => true,
            'message' => $mensaje
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al actualizar competencias: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al actualizar las competencias'];
    }
}

function eliminarAsignacion() {
    global $conexion;
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        return ['success' => false, 'message' => 'ID requerido'];
    }
    
    try {
        $conexion->beginTransaction();
        
        // Verificar que la asignación existe
        $stmt_check = $conexion->prepare("SELECT id FROM malla_curricular WHERE id = ?");
        $stmt_check->execute([$id]);
        
        if (!$stmt_check->fetch()) {
            $conexion->rollBack();
            return ['success' => false, 'message' => 'Asignación no encontrada'];
        }
        
        // Soft delete - marcar como inactivo
        $stmt = $conexion->prepare("UPDATE malla_curricular SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Asignación eliminada correctamente'
        ];
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error al eliminar asignación: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al eliminar la asignación'];
    }
}
?>