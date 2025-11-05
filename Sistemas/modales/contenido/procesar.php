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
        // ACCIÓN 1: Gestión de Unidades
        case 'crear_unidad':
        case 'actualizar_unidad':
        case 'obtener_unidad':
        case 'eliminar_unidad':
            $response = gestionarUnidad($accion);
            break;
            
        // ACCIÓN 2: Gestión de Lecciones
        case 'crear_leccion':
        case 'actualizar_leccion':
        case 'obtener_leccion':
        case 'eliminar_leccion':
            $response = gestionarLeccion($accion);
            break;
            
        // ACCIÓN 3: Gestión de Recursos
        case 'crear_recurso':
            $response = gestionarRecurso($accion);
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

// ==================== ACCIÓN 1: GESTIÓN DE UNIDADES ====================
function gestionarUnidad($accion) {
    global $conexion;
    
    switch ($accion) {
        case 'crear_unidad':
            if (!isset($_POST['curso_id']) || !isset($_POST['titulo'])) {
                throw new Exception('Datos incompletos');
            }
            
            // VALIDACIÓN DE FECHAS
            $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
            $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
            
            // Validar que las fechas no sean anteriores a hoy
            if ($fecha_inicio) {
                $hoy = date('Y-m-d');
                if ($fecha_inicio < $hoy) {
                    throw new Exception('La fecha de inicio no puede ser anterior a la fecha actual');
                }
            }
            
            if ($fecha_fin) {
                $hoy = date('Y-m-d');
                if ($fecha_fin < $hoy) {
                    throw new Exception('La fecha fin no puede ser anterior a la fecha actual');
                }
            }
            
            // Validar que fecha fin sea posterior a fecha inicio
            if ($fecha_inicio && $fecha_fin) {
                if ($fecha_fin <= $fecha_inicio) {
                    throw new Exception('La fecha fin debe ser posterior a la fecha de inicio');
                }
            }
            
            $configuraciones = [
                'estado' => $_POST['estado'] ?? 'BORRADOR',
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ];
            
            $stmt = $conexion->prepare("
                INSERT INTO unidades (curso_id, titulo, descripcion, orden, configuraciones, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $_POST['curso_id'],
                $_POST['titulo'],
                $_POST['descripcion'] ?? '',
                $_POST['orden'],
                json_encode($configuraciones)
            ]);
            
            return ['success' => true, 'message' => 'Unidad creada exitosamente'];
            
        case 'actualizar_unidad':
            if (!isset($_POST['unidad_id'])) {
                throw new Exception('ID de unidad no especificado');
            }
            
            // VALIDACIÓN DE FECHAS
            $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
            $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
            
            // Obtener fecha de creación de la unidad para validaciones
            $stmt = $conexion->prepare("SELECT fecha_creacion FROM unidades WHERE id = ?");
            $stmt->execute([$_POST['unidad_id']]);
            $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$unidad) {
                throw new Exception('Unidad no encontrada');
            }
            
            $fecha_creacion = date('Y-m-d', strtotime($unidad['fecha_creacion']));
            $hoy = date('Y-m-d');
            
            // Validar fecha de inicio
            if ($fecha_inicio) {
                // Si la unidad se creó hoy o después, validar contra hoy
                // Si es antigua, permitir mantener su fecha pero no retrocederla
                if ($fecha_creacion >= $hoy && $fecha_inicio < $hoy) {
                    throw new Exception('La fecha de inicio no puede ser anterior a la fecha actual');
                }
            }
            
            // Validar fecha fin
            if ($fecha_fin) {
                if ($fecha_fin < $hoy) {
                    throw new Exception('La fecha fin no puede ser anterior a la fecha actual');
                }
            }
            
            // Validar coherencia entre fechas
            if ($fecha_inicio && $fecha_fin) {
                if ($fecha_fin <= $fecha_inicio) {
                    throw new Exception('La fecha fin debe ser posterior a la fecha de inicio');
                }
            }
            
            $configuraciones = [
                'estado' => $_POST['estado'] ?? 'BORRADOR',
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ];
            
            $stmt = $conexion->prepare("
                UPDATE unidades 
                SET titulo = ?, descripcion = ?, orden = ?, configuraciones = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['titulo'],
                $_POST['descripcion'] ?? '',
                $_POST['orden'],
                json_encode($configuraciones),
                $_POST['unidad_id']
            ]);
            
            return ['success' => true, 'message' => 'Unidad actualizada exitosamente'];
            
        case 'obtener_unidad':
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $conexion->prepare("SELECT * FROM unidades WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$unidad) {
                throw new Exception('Unidad no encontrada');
            }
            
            $unidad['configuraciones'] = json_decode($unidad['configuraciones'], true);
            
            return ['success' => true, 'data' => $unidad];
            
        case 'eliminar_unidad':
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $conexion->beginTransaction();
            
            try {
                // Eliminar lecciones asociadas
                $stmt = $conexion->prepare("DELETE FROM lecciones WHERE unidad_id = ?");
                $stmt->execute([$_POST['id']]);
                
                // Eliminar unidad
                $stmt = $conexion->prepare("DELETE FROM unidades WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                
                $conexion->commit();
                
                return ['success' => true, 'message' => 'Unidad eliminada exitosamente'];
                
            } catch (Exception $e) {
                $conexion->rollback();
                throw $e;
            }
    }
}

// ==================== ACCIÓN 2: GESTIÓN DE LECCIONES ====================
function gestionarLeccion($accion) {
    global $conexion;
    
    switch ($accion) {
        case 'crear_leccion':
            if (!isset($_POST['unidad_id']) || !isset($_POST['titulo'])) {
                throw new Exception('Datos incompletos');
            }
            
            $configuraciones = [
                'estado' => $_POST['estado'] ?? 'BORRADOR',
                'tiempo_estimado' => $_POST['tiempo_estimado'] ?? null,
                'obligatorio' => true
            ];
            
            $stmt = $conexion->prepare("
                INSERT INTO lecciones (unidad_id, titulo, descripcion, orden, tipo, configuraciones, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $_POST['unidad_id'],
                $_POST['titulo'],
                $_POST['descripcion'] ?? '',
                $_POST['orden'],
                $_POST['tipo'],
                json_encode($configuraciones)
            ]);
            
            return ['success' => true, 'message' => 'Lección creada exitosamente'];
            
        case 'actualizar_leccion':
            if (!isset($_POST['leccion_id'])) {
                throw new Exception('ID de lección no especificado');
            }
            
            $configuraciones = [
                'estado' => $_POST['estado'] ?? 'BORRADOR',
                'tiempo_estimado' => $_POST['tiempo_estimado'] ?? null,
                'obligatorio' => true
            ];
            
            $stmt = $conexion->prepare("
                UPDATE lecciones 
                SET titulo = ?, descripcion = ?, orden = ?, tipo = ?, configuraciones = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['titulo'],
                $_POST['descripcion'] ?? '',
                $_POST['orden'],
                $_POST['tipo'],
                json_encode($configuraciones),
                $_POST['leccion_id']
            ]);
            
            return ['success' => true, 'message' => 'Lección actualizada exitosamente'];
            
        case 'obtener_leccion':
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $leccion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$leccion) {
                throw new Exception('Lección no encontrada');
            }
            
            $leccion['configuraciones'] = json_decode($leccion['configuraciones'], true);
            
            return ['success' => true, 'data' => $leccion];
            
        case 'eliminar_leccion':
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $conexion->prepare("DELETE FROM lecciones WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            return ['success' => true, 'message' => 'Lección eliminada exitosamente'];
    }
}

// ==================== ACCIÓN 3: GESTIÓN DE RECURSOS ====================
function gestionarRecurso($accion) {
    global $conexion;
    
    if ($accion === 'crear_recurso') {
        if (!isset($_POST['curso_id']) || !isset($_POST['tipo_recurso']) || !isset($_POST['titulo_recurso'])) {
            throw new Exception('Datos incompletos');
        }
        
        $tipo = $_POST['tipo_recurso'];
        $curso_id = $_POST['curso_id'];
        $titulo = $_POST['titulo_recurso'];
        $descripcion = $_POST['descripcion_recurso'] ?? '';
        
        switch ($tipo) {
            case 'TAREA':
                $configuracion = [
                    'fecha_apertura' => date('Y-m-d H:i:s'),
                    'tipo_entrega' => 'ARCHIVO'
                ];
                
                $stmt = $conexion->prepare("
                    INSERT INTO tareas (curso_id, titulo, descripcion, configuracion, estado, usuario_creacion, fecha_creacion)
                    VALUES (?, ?, ?, ?, 'BORRADOR', 1, NOW())
                ");
                
                $stmt->execute([
                    $curso_id,
                    $titulo,
                    $descripcion,
                    json_encode($configuracion)
                ]);
                break;
                
            case 'CUESTIONARIO':
                $configuracion = [
                    'tipo' => 'EVALUACION',
                    'intentos_permitidos' => 1
                ];
                
                $stmt = $conexion->prepare("
                    INSERT INTO cuestionarios (curso_id, titulo, descripcion, configuracion, estado, usuario_creacion, fecha_creacion)
                    VALUES (?, ?, ?, ?, 'BORRADOR', 1, NOW())
                ");
                
                $stmt->execute([
                    $curso_id,
                    $titulo,
                    $descripcion,
                    json_encode($configuracion)
                ]);
                break;
                
            case 'ANUNCIO':
                $configuraciones = [
                    'tipo' => 'INFORMATIVO',
                    'prioridad' => 'NORMAL',
                    'destinatario' => 'ESTUDIANTES'
                ];
                
                $stmt = $conexion->prepare("
                    INSERT INTO anuncios (curso_id, titulo, contenido, configuraciones, fecha_publicacion, usuario_creacion, activo)
                    VALUES (?, ?, ?, ?, NOW(), 1, 1)
                ");
                
                $stmt->execute([
                    $curso_id,
                    $titulo,
                    $descripcion,
                    json_encode($configuraciones)
                ]);
                break;
                
            default:
                throw new Exception('Tipo de recurso no válido');
        }
        
        return ['success' => true, 'message' => ucfirst(strtolower($tipo)) . ' creado exitosamente'];
    }
    
    throw new Exception('Acción no válida');
}
?>