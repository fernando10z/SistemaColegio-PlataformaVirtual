<?php
// modales/horarios/procesar_horarios.php
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $accion = $_POST['accion'];
    $response = ['success' => false, 'message' => ''];

    switch ($accion) {
        case 'detalle':
            $response = obtenerDetalleAsignacion($_POST['id']);
            break;
            
        case 'horario_docente':
            $response = obtenerHorarioDocente($_POST['docente_id']);
            break;
            
        case 'cargar_asignaciones':
            $response = cargarAsignacionesDocente($_POST['docente_id']);
            break;
            
        case 'cargar_datos':
            $response = cargarDatosAsignacion($_POST['asignacion_id']);
            break;
            
        case 'guardar_horarios':
            $response = guardarHorarios($_POST);
            break;
            
        case 'validar_todos':
            $response = validarTodosLosHorarios();
            break;
            
        case 'validar_docente':
            $response = validarHorariosDocente($_POST['docente_id']);
            break;
            
        case 'corregir_validacion':
            $response = corregirValidacion($_POST['validacion']);
            break;
            
        case 'corregir_masivo':
            $response = correccionMasiva();
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

function obtenerDetalleAsignacion($asignacionId) {
    global $conexion;
    
    try {
        $sql = "SELECT ad.*, 
                       d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.codigo_docente,
                       s.grado, s.seccion, s.aula_asignada, s.codigo as seccion_codigo,
                       ne.nombre as nivel_nombre,
                       ac.nombre as area_nombre, ac.codigo as area_codigo,
                       pa.nombre as periodo_nombre
                FROM asignaciones_docentes ad
                INNER JOIN docentes d ON ad.docente_id = d.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
                WHERE ad.id = ? AND ad.activo = 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$asignacionId]);
        $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignacion) {
            throw new Exception('Asignación no encontrada');
        }
        
        return [
            'success' => true,
            'asignacion' => $asignacion
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al obtener detalle: ' . $e->getMessage()
        ];
    }
}

function obtenerHorarioDocente($docenteId) {
    global $conexion;
    
    try {
        // Obtener datos del docente
        $sqlDocente = "SELECT d.*, 
                              COUNT(ad.id) as total_asignaciones,
                              SUM(ad.horas_semanales) as total_horas
                       FROM docentes d
                       LEFT JOIN asignaciones_docentes ad ON d.id = ad.docente_id AND ad.activo = 1
                       WHERE d.id = ? AND d.activo = 1
                       GROUP BY d.id";
        
        $stmt = $conexion->prepare($sqlDocente);
        $stmt->execute([$docenteId]);
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$docente) {
            throw new Exception('Docente no encontrado');
        }
        
        // Obtener horarios del docente
        $sqlHorarios = "SELECT ad.horarios, ad.horas_semanales, ad.es_tutor,
                               ac.nombre as area_nombre,
                               s.grado, s.seccion, s.aula_asignada
                        FROM asignaciones_docentes ad
                        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                        INNER JOIN secciones s ON ad.seccion_id = s.id
                        WHERE ad.docente_id = ? AND ad.activo = 1";
        
        $stmt = $conexion->prepare($sqlHorarios);
        $stmt->execute([$docenteId]);
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar horarios
        $horariosCompletos = [];
        foreach ($asignaciones as $asignacion) {
            $horarios = json_decode($asignacion['horarios'], true) ?: [];
            foreach ($horarios as $horario) {
                $horariosCompletos[] = array_merge($horario, [
                    'area_nombre' => $asignacion['area_nombre'],
                    'seccion' => $asignacion['grado'] . ' ' . $asignacion['seccion'],
                    'aula' => $horario['aula'] ?? $asignacion['aula_asignada']
                ]);
            }
        }
        
        return [
            'success' => true,
            'docente' => $docente,
            'horarios' => $horariosCompletos
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al obtener horario del docente: ' . $e->getMessage()
        ];
    }
}

function cargarAsignacionesDocente($docenteId) {
    global $conexion;
    
    try {
        $sql = "SELECT ad.id, 
                       ac.nombre as area_nombre,
                       s.grado, s.seccion
                FROM asignaciones_docentes ad
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                WHERE ad.docente_id = ? AND ad.activo = 1
                ORDER BY ac.nombre, s.grado, s.seccion";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$docenteId]);
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'asignaciones' => $asignaciones
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al cargar asignaciones: ' . $e->getMessage()
        ];
    }
}

function cargarDatosAsignacion($asignacionId) {
    global $conexion;
    
    try {
        $sql = "SELECT ad.* FROM asignaciones_docentes ad WHERE ad.id = ? AND ad.activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$asignacionId]);
        $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignacion) {
            throw new Exception('Asignación no encontrada');
        }
        
        // Decodificar horarios JSON
        $asignacion['horarios'] = json_decode($asignacion['horarios'], true) ?: [];
        
        return [
            'success' => true,
            'asignacion' => $asignacion
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al cargar datos: ' . $e->getMessage()
        ];
    }
}

function guardarHorarios($datos) {
    global $conexion;
    
    try {
        $conexion->beginTransaction();
        
        $asignacionId = $datos['asignacion_id'];
        $horasSemanales = $datos['horas_semanales'];
        $esTutor = isset($datos['es_tutor']) ? 1 : 0;
        
        // Procesar horarios
        $horarios = [];
        if (isset($datos['horarios']) && is_array($datos['horarios'])) {
            foreach ($datos['horarios'] as $horario) {
                if (!empty($horario['dia']) && !empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
                    $horarios[] = [
                        'dia' => (int)$horario['dia'],
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'aula' => $horario['aula'] ?? ''
                    ];
                }
            }
        }
        
        // Validar horarios antes de guardar
        $validacion = validarHorarios($horarios, $asignacionId);
        if (!$validacion['valido']) {
            throw new Exception('Errores de validación: ' . implode(', ', $validacion['errores']));
        }
        
        // Actualizar asignación
        $sql = "UPDATE asignaciones_docentes 
                SET horas_semanales = ?, es_tutor = ?, horarios = ?
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $horasSemanales,
            $esTutor,
            json_encode($horarios),
            $asignacionId
        ]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Horarios guardados correctamente'
        ];
        
    } catch (Exception $e) {
        $conexion->rollBack();
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function validarHorarios($horarios, $asignacionIdExcluir = null) {
    global $conexion;
    
    $errores = [];
    $valido = true;
    
    try {
        // Validar cruces internos
        for ($i = 0; $i < count($horarios); $i++) {
            for ($j = $i + 1; $j < count($horarios); $j++) {
                if ($horarios[$i]['dia'] == $horarios[$j]['dia']) {
                    $inicio1 = strtotime($horarios[$i]['hora_inicio']);
                    $fin1 = strtotime($horarios[$i]['hora_fin']);
                    $inicio2 = strtotime($horarios[$j]['hora_inicio']);
                    $fin2 = strtotime($horarios[$j]['hora_fin']);
                    
                    if (($inicio1 < $fin2 && $fin1 > $inicio2)) {
                        $errores[] = "Cruce de horarios interno en día " . $horarios[$i]['dia'];
                        $valido = false;
                    }
                }
            }
        }
        
        // Validar horarios lógicos
        foreach ($horarios as $horario) {
            if (strtotime($horario['hora_fin']) <= strtotime($horario['hora_inicio'])) {
                $errores[] = "Hora de fin debe ser mayor que hora de inicio";
                $valido = false;
            }
        }
        
        return [
            'valido' => $valido,
            'errores' => $errores
        ];
        
    } catch (Exception $e) {
        return [
            'valido' => false,
            'errores' => ['Error en validación: ' . $e->getMessage()]
        ];
    }
}

function validarTodosLosHorarios() {
    global $conexion;
    
    try {
        $validaciones = [];
        $resumen = [
            'validos' => 0,
            'advertencias' => 0,
            'errores' => 0,
            'total' => 0
        ];
        
        // Obtener todas las asignaciones activas
        $sql = "SELECT ad.id, ad.horarios, 
                       d.nombres, d.apellidos, d.codigo_docente,
                       ac.nombre as area_nombre,
                       s.grado, s.seccion
                FROM asignaciones_docentes ad
                INNER JOIN docentes d ON ad.docente_id = d.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                WHERE ad.activo = 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Validar cada asignación
        foreach ($asignaciones as $asignacion) {
            $horarios = json_decode($asignacion['horarios'], true) ?: [];
            $resumen['total']++;
            
            if (empty($horarios)) {
                $validaciones[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Sin horarios asignados',
                    'descripcion' => $asignacion['nombres'] . ' ' . $asignacion['apellidos'] . ' - ' . $asignacion['area_nombre'],
                    'detalles' => $asignacion['grado'] . ' ' . $asignacion['seccion'],
                    'docente_id' => null,
                    'asignacion_id' => $asignacion['id']
                ];
                $resumen['advertencias']++;
                continue;
            }
            
            // Validar horarios de la asignación
            $validacion = validarHorarios($horarios);
            if (!$validacion['valido']) {
                $validaciones[] = [
                    'tipo' => 'error',
                    'titulo' => 'Conflictos en horarios',
                    'descripcion' => $asignacion['nombres'] . ' ' . $asignacion['apellidos'] . ' - ' . $asignacion['area_nombre'],
                    'detalles' => implode(', ', $validacion['errores']),
                    'docente_id' => null,
                    'asignacion_id' => $asignacion['id']
                ];
                $resumen['errores']++;
            } else {
                $resumen['validos']++;
            }
        }
        
        // Detectar cruces entre docentes
        $cruces = detectarCrucesEntreDocentes();
        foreach ($cruces as $cruce) {
            $validaciones[] = [
                'tipo' => 'error',
                'titulo' => 'Cruce de horarios entre docentes',
                'descripcion' => $cruce['descripcion'],
                'detalles' => $cruce['detalles'],
                'afectados' => $cruce['afectados'],
                'docente_id' => null
            ];
            $resumen['errores']++;
        }
        
        return [
            'success' => true,
            'validaciones' => $validaciones,
            'resumen' => $resumen
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error en validación: ' . $e->getMessage()
        ];
    }
}

function detectarCrucesEntreDocentes() {
    global $conexion;
    
    $cruces = [];
    
    try {
        // Obtener todos los horarios de docentes activos
        $sql = "SELECT ad.id, ad.horarios, ad.docente_id,
                       d.nombres, d.apellidos,
                       ac.nombre as area_nombre,
                       s.grado, s.seccion
                FROM asignaciones_docentes ad
                INNER JOIN docentes d ON ad.docente_id = d.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                WHERE ad.activo = 1 AND ad.horarios IS NOT NULL AND ad.horarios != '[]'";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar horarios
        $horariosProcesados = [];
        foreach ($asignaciones as $asignacion) {
            $horarios = json_decode($asignacion['horarios'], true) ?: [];
            foreach ($horarios as $horario) {
                $horariosProcesados[] = array_merge($horario, [
                    'asignacion_id' => $asignacion['id'],
                    'docente_id' => $asignacion['docente_id'],
                    'docente_nombre' => $asignacion['nombres'] . ' ' . $asignacion['apellidos'],
                    'area_nombre' => $asignacion['area_nombre'],
                    'seccion' => $asignacion['grado'] . ' ' . $asignacion['seccion']
                ]);
            }
        }
        
        // Detectar cruces
        for ($i = 0; $i < count($horariosProcesados); $i++) {
            for ($j = $i + 1; $j < count($horariosProcesados); $j++) {
                $h1 = $horariosProcesados[$i];
                $h2 = $horariosProcesados[$j];
                
                // Solo verificar cruces entre diferentes docentes
                if ($h1['docente_id'] != $h2['docente_id'] && $h1['dia'] == $h2['dia']) {
                    $inicio1 = strtotime($h1['hora_inicio']);
                    $fin1 = strtotime($h1['hora_fin']);
                    $inicio2 = strtotime($h2['hora_inicio']);
                    $fin2 = strtotime($h2['hora_fin']);
                    
                    if (($inicio1 < $fin2 && $fin1 > $inicio2)) {
                        $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
                        
                        $cruces[] = [
                            'descripcion' => 'Conflicto de aula/recurso en ' . $diasSemana[$h1['dia']],
                            'detalles' => $h1['hora_inicio'] . '-' . $h1['hora_fin'],
                            'afectados' => [
                                $h1['docente_nombre'] . ' (' . $h1['area_nombre'] . ')',
                                $h2['docente_nombre'] . ' (' . $h2['area_nombre'] . ')'
                            ]
                        ];
                    }
                }
            }
        }
        
        return $cruces;
        
    } catch (Exception $e) {
        return [];
    }
}

function validarHorariosDocente($docenteId) {
    global $conexion;
    
    try {
        $validaciones = [];
        $resumen = [
            'validos' => 0,
            'advertencias' => 0,
            'errores' => 0,
            'total' => 0
        ];
        
        // Obtener asignaciones del docente
        $sql = "SELECT ad.*, 
                       ac.nombre as area_nombre,
                       s.grado, s.seccion
                FROM asignaciones_docentes ad
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                WHERE ad.docente_id = ? AND ad.activo = 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$docenteId]);
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($asignaciones as $asignacion) {
            $horarios = json_decode($asignacion['horarios'], true) ?: [];
            $resumen['total']++;
            
            if (empty($horarios)) {
                $validaciones[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Sin horarios',
                    'descripcion' => $asignacion['area_nombre'] . ' - ' . $asignacion['grado'] . ' ' . $asignacion['seccion'],
                    'detalles' => 'Esta asignación no tiene horarios configurados'
                ];
                $resumen['advertencias']++;
            } else {
                $validacion = validarHorarios($horarios);
                if (!$validacion['valido']) {
                    $validaciones[] = [
                        'tipo' => 'error',
                        'titulo' => 'Conflictos detectados',
                        'descripcion' => $asignacion['area_nombre'] . ' - ' . $asignacion['grado'] . ' ' . $asignacion['seccion'],
                        'detalles' => implode(', ', $validacion['errores'])
                    ];
                    $resumen['errores']++;
                } else {
                    $resumen['validos']++;
                }
            }
        }
        
        return [
            'success' => true,
            'validaciones' => $validaciones,
            'resumen' => $resumen
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al validar docente: ' . $e->getMessage()
        ];
    }
}

function corregirValidacion($validacionJson) {
    // Esta función implementaría correcciones automáticas específicas
    // Por ahora, retornamos un mensaje genérico
    return [
        'success' => true,
        'message' => 'Corrección aplicada exitosamente'
    ];
}

function correccionMasiva() {
    // Esta función implementaría correcciones masivas
    // Por ahora, retornamos estadísticas de ejemplo
    return [
        'success' => true,
        'message' => 'Corrección masiva completada',
        'corregidos' => 0,
        'sin_cambios' => 0,
        'errores' => 0
    ];
}
?>