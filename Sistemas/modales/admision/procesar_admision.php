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
            $response = crearProceso();
            break;
            
        case 'actualizar':
            $response = actualizarProceso();
            break;
            
        case 'obtener':
            $response = obtenerProceso();
            break;
            
        case 'detalles':
            $response = obtenerDetallesProceso();
            break;
            
        case 'toggle_activo':
            $response = toggleActivoProceso();
            break;
            
        case 'cambiar_estado':
            $response = cambiarEstadoProceso();
            break;
            
        case 'eliminar':
            $response = eliminarProceso();
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

function crearProceso() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['nombre', 'anio_academico', 'estado', 'fecha_inicio', 'fecha_fin', 'costo_inscripcion'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    $conexion->beginTransaction();

    try {
        // Validar año académico
        $anio_academico = (int)$_POST['anio_academico'];
        $anio_actual = date('Y');
        
        if ($anio_academico < $anio_actual || $anio_academico > ($anio_actual + 5)) {
            throw new Exception('El año académico debe estar entre ' . $anio_actual . ' y ' . ($anio_actual + 5));
        }

        // Validar fechas
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        
        if ($fecha_fin < $fecha_inicio) {
            throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        // Validar costo
        $costo = (float)$_POST['costo_inscripcion'];
        if ($costo < 0 || $costo > 9999.99) {
            throw new Exception('El costo debe estar entre 0.00 y 9999.99');
        }

        // Validar nombre único para el mismo año académico
        $stmt = $conexion->prepare("SELECT id FROM procesos_admision WHERE nombre = ? AND anio_academico = ?");
        $stmt->execute([$_POST['nombre'], $anio_academico]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un proceso con este nombre para el año académico ' . $anio_academico);
        }

        // Preparar configuración
        $configuracion = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fecha_examen' => $_POST['fecha_examen'] ?? null,
            'costo_inscripcion' => $costo,
            'requisitos' => $_POST['requisitos'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? ''
        ];

        // Procesar vacantes
        $vacantes = [];
        $total_vacantes = 0;
        
        if (isset($_POST['vacantes']) && is_array($_POST['vacantes'])) {
            foreach ($_POST['vacantes'] as $nivel_id => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad > 0) {
                    // Obtener nombre del nivel
                    $stmt = $conexion->prepare("SELECT nombre FROM niveles_educativos WHERE id = ?");
                    $stmt->execute([$nivel_id]);
                    $nivel = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($nivel) {
                        $vacantes[] = [
                            'nivel_id' => (int)$nivel_id,
                            'nivel' => $nivel['nombre'],
                            'cantidad' => $cantidad
                        ];
                        $total_vacantes += $cantidad;
                    }
                }
            }
        }

        // Validar que haya al menos una vacante
        if ($total_vacantes === 0) {
            throw new Exception('Debe asignar al menos una vacante');
        }

        // Determinar estado activo
        $activo = isset($_POST['activo']) && $_POST['activo'] === 'on' ? 1 : 0;

        // Insertar proceso
        $stmt = $conexion->prepare("
            INSERT INTO procesos_admision (
                nombre, anio_academico, configuracion, vacantes, 
                estado, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $_POST['nombre'],
            $anio_academico,
            json_encode($configuracion),
            json_encode($vacantes),
            $_POST['estado'],
            $activo
        ]);

        $proceso_id = $conexion->lastInsertId();

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Proceso de admisión creado exitosamente',
            'proceso_id' => $proceso_id
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function actualizarProceso() {
    global $conexion;
    
    if (!isset($_POST['proceso_id'])) {
        throw new Exception('ID del proceso no especificado');
    }

    $proceso_id = (int)$_POST['proceso_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que el proceso existe
        $stmt = $conexion->prepare("SELECT * FROM procesos_admision WHERE id = ?");
        $stmt->execute([$proceso_id]);
        $proceso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$proceso) {
            throw new Exception('Proceso no encontrado');
        }

        // Validar año académico
        $anio_academico = (int)$_POST['anio_academico'];
        $anio_actual = date('Y');
        
        if ($anio_academico < $anio_actual || $anio_academico > ($anio_actual + 5)) {
            throw new Exception('El año académico debe estar entre ' . $anio_actual . ' y ' . ($anio_actual + 5));
        }

        // Validar fechas
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        
        if ($fecha_fin < $fecha_inicio) {
            throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        // Validar costo
        $costo = (float)$_POST['costo_inscripcion'];
        if ($costo < 0 || $costo > 9999.99) {
            throw new Exception('El costo debe estar entre 0.00 y 9999.99');
        }

        // Validar nombre único para el mismo año académico (excepto el proceso actual)
        $stmt = $conexion->prepare("SELECT id FROM procesos_admision WHERE nombre = ? AND anio_academico = ? AND id != ?");
        $stmt->execute([$_POST['nombre'], $anio_academico, $proceso_id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otro proceso con este nombre para el año académico ' . $anio_academico);
        }

        // Preparar configuración
        $configuracion = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'fecha_examen' => $_POST['fecha_examen'] ?? null,
            'costo_inscripcion' => $costo,
            'requisitos' => $_POST['requisitos'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? ''
        ];

        // Procesar vacantes
        $vacantes = [];
        $total_vacantes = 0;
        
        if (isset($_POST['vacantes']) && is_array($_POST['vacantes'])) {
            foreach ($_POST['vacantes'] as $nivel_id => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad > 0) {
                    // Obtener nombre del nivel
                    $stmt = $conexion->prepare("SELECT nombre FROM niveles_educativos WHERE id = ?");
                    $stmt->execute([$nivel_id]);
                    $nivel = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($nivel) {
                        $vacantes[] = [
                            'nivel_id' => (int)$nivel_id,
                            'nivel' => $nivel['nombre'],
                            'cantidad' => $cantidad
                        ];
                        $total_vacantes += $cantidad;
                    }
                }
            }
        }

        // Validar que haya al menos una vacante
        if ($total_vacantes === 0) {
            throw new Exception('Debe asignar al menos una vacante');
        }

        // Determinar estado activo
        $activo = isset($_POST['activo']) && $_POST['activo'] === 'on' ? 1 : 0;

        // Actualizar proceso
        $stmt = $conexion->prepare("
            UPDATE procesos_admision SET 
                nombre = ?, anio_academico = ?, configuracion = ?, 
                vacantes = ?, estado = ?, activo = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['nombre'],
            $anio_academico,
            json_encode($configuracion),
            json_encode($vacantes),
            $_POST['estado'],
            $activo,
            $proceso_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Proceso de admisión actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerProceso() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del proceso no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT p.*, 
               COUNT(DISTINCT po.id) as total_postulaciones,
               COUNT(DISTINCT CASE WHEN po.estado = 'ADMITIDO' THEN po.id END) as admitidos,
               COUNT(DISTINCT CASE WHEN po.estado = 'LISTA_ESPERA' THEN po.id END) as lista_espera
        FROM procesos_admision p
        LEFT JOIN postulaciones po ON p.id = po.proceso_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$id]);
    $proceso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proceso) {
        throw new Exception('Proceso no encontrado');
    }

    // Decodificar JSON
    $proceso['configuracion'] = json_decode($proceso['configuracion'], true);
    $proceso['vacantes'] = json_decode($proceso['vacantes'], true);

    return [
        'success' => true,
        'proceso' => $proceso
    ];
}

function obtenerDetallesProceso() {
    return obtenerProceso(); // Reutilizar la función
}

function toggleActivoProceso() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['activo'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $activo = $_POST['activo'] === 'true' ? 1 : 0;

    $stmt = $conexion->prepare("UPDATE procesos_admision SET activo = ? WHERE id = ?");
    $stmt->execute([$activo, $id]);

    $accion = $activo ? 'activado' : 'desactivado';

    return [
        'success' => true,
        'message' => "Proceso $accion exitosamente"
    ];
}

function cambiarEstadoProceso() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $estado = $_POST['estado'];

    // Validar estado
    $estados_validos = ['CONFIGURACION', 'ABIERTO', 'CERRADO', 'FINALIZADO'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado no válido');
    }

    $stmt = $conexion->prepare("UPDATE procesos_admision SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    return [
        'success' => true,
        'message' => "Estado del proceso cambiado a: $estado"
    ];
}

function eliminarProceso() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del proceso no especificado');
    }

    $id = (int)$_POST['id'];

    $conexion->beginTransaction();

    try {
        // Verificar si hay postulaciones asociadas
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM postulaciones WHERE proceso_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            throw new Exception("No se puede eliminar el proceso porque tiene $count postulaciones asociadas. Considere desactivarlo en lugar de eliminarlo.");
        }

        // Eliminar el proceso
        $stmt = $conexion->prepare("DELETE FROM procesos_admision WHERE id = ?");
        $stmt->execute([$id]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Proceso de admisión eliminado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}
?>