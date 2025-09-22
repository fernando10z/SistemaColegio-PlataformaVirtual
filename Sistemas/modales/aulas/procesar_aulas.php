<?php
// modales/aulas/procesar_aulas.php

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Incluir archivo de conexión
    require_once '../../conexion/bd.php';

    // Verificar método de petición
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener acción
    $accion = $_POST['accion'] ?? '';

    // Validar acción
    $accionesPermitidas = ['crear', 'obtener', 'actualizar', 'detalle', 'estudiantes', 'programacion'];
    if (!in_array($accion, $accionesPermitidas)) {
        throw new Exception('Acción no válida');
    }

    // Procesar según acción
    switch ($accion) {
        case 'crear':
            $resultado = crearAula($conexion);
            break;
        case 'obtener':
            $resultado = obtenerAula($conexion);
            break;
        case 'actualizar':
            $resultado = actualizarAula($conexion);
            break;
        case 'detalle':
            $resultado = detalleAula($conexion);
            break;
        case 'estudiantes':
            $resultado = estudiantesAula($conexion);
            break;
        case 'programacion':
            $resultado = programacionAula($conexion);
            break;
        default:
            throw new Exception('Acción no implementada');
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Error en procesar_aulas.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Crear nueva aula/sección
 */
function crearAula($conexion) {
    // Validar datos requeridos
    $camposRequeridos = ['nivel_id', 'grado', 'seccion', 'aula_asignada', 'capacidad_maxima', 'periodo_academico_id'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    // Obtener y sanitizar datos
    $nivel_id = intval($_POST['nivel_id']);
    $grado = trim($_POST['grado']);
    $seccion = trim($_POST['seccion']);
    $aula_asignada = trim($_POST['aula_asignada']);
    $capacidad_maxima = intval($_POST['capacidad_maxima']);
    $periodo_academico_id = intval($_POST['periodo_academico_id']);
    $codigo = trim($_POST['codigo'] ?? '');
    $activo = !empty($_POST['activo']) ? 1 : 0;

    // Validaciones específicas
    if ($capacidad_maxima < 1 || $capacidad_maxima > 100) {
        throw new Exception('La capacidad debe estar entre 1 y 100 estudiantes');
    }

    // Verificar que el nivel educativo existe
    $stmt = $conexion->prepare("SELECT id FROM niveles_educativos WHERE id = ? AND activo = 1");
    $stmt->execute([$nivel_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Nivel educativo no válido');
    }

    // Verificar que el período académico existe
    $stmt = $conexion->prepare("SELECT id FROM periodos_academicos WHERE id = ? AND activo = 1");
    $stmt->execute([$periodo_academico_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Período académico no válido');
    }

    // Verificar que no existe otra sección igual en el mismo período
    $stmt = $conexion->prepare("SELECT id FROM secciones WHERE nivel_id = ? AND grado = ? AND seccion = ? AND periodo_academico_id = ? AND activo = 1");
    $stmt->execute([$nivel_id, $grado, $seccion, $periodo_academico_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe una sección con estos datos en el período académico seleccionado');
    }

    // Generar código si no se proporcionó
    if (empty($codigo)) {
        $codigo = generarCodigoSeccion($conexion, $nivel_id, $grado, $seccion);
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    try {
        // Insertar nueva sección
        $sql = "INSERT INTO secciones (
                    nivel_id, grado, seccion, codigo, capacidad_maxima, 
                    aula_asignada, periodo_academico_id, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nivel_id, $grado, $seccion, $codigo, $capacidad_maxima,
            $aula_asignada, $periodo_academico_id, $activo
        ]);

        // Confirmar transacción
        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Aula creada exitosamente',
            'aula_id' => $conexion->lastInsertId()
        ];

    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

/**
 * Obtener datos de aula para edición
 */
function obtenerAula($conexion) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de aula no válido');
    }

    $sql = "SELECT s.*, 
                   ne.nombre as nivel_nombre,
                   COUNT(m.id) as estudiantes_matriculados,
                   ROUND((COUNT(m.id) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion
            FROM secciones s
            LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1 AND m.estado = 'MATRICULADO'
            WHERE s.id = ?
            GROUP BY s.id";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $aula = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aula) {
        throw new Exception('Aula no encontrada');
    }

    return [
        'success' => true,
        'aula' => $aula
    ];
}

/**
 * Actualizar aula existente
 */
function actualizarAula($conexion) {
    // Validar datos requeridos
    $camposRequeridos = ['aula_id', 'nivel_id', 'grado', 'seccion', 'aula_asignada', 'capacidad_maxima', 'periodo_academico_id'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    $aula_id = intval($_POST['aula_id']);
    $nivel_id = intval($_POST['nivel_id']);
    $grado = trim($_POST['grado']);
    $seccion = trim($_POST['seccion']);
    $aula_asignada = trim($_POST['aula_asignada']);
    $capacidad_maxima = intval($_POST['capacidad_maxima']);
    $periodo_academico_id = intval($_POST['periodo_academico_id']);
    $codigo = trim($_POST['codigo'] ?? '');
    $activo = intval($_POST['activo'] ?? 1);

    // Validaciones específicas
    if ($capacidad_maxima < 1 || $capacidad_maxima > 100) {
        throw new Exception('La capacidad debe estar entre 1 y 100 estudiantes');
    }

    // Verificar que el aula existe
    $stmt = $conexion->prepare("SELECT id FROM secciones WHERE id = ?");
    $stmt->execute([$aula_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Aula no encontrada');
    }

    // Verificar que no existe otra sección igual (excluyendo la actual)
    $stmt = $conexion->prepare("SELECT id FROM secciones WHERE nivel_id = ? AND grado = ? AND seccion = ? AND periodo_academico_id = ? AND id != ? AND activo = 1");
    $stmt->execute([$nivel_id, $grado, $seccion, $periodo_academico_id, $aula_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe otra sección con estos datos en el período académico seleccionado');
    }

    // Verificar restricciones de capacidad si hay estudiantes matriculados
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM matriculas WHERE seccion_id = ? AND activo = 1 AND estado = 'MATRICULADO'");
    $stmt->execute([$aula_id]);
    $estudiantes_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($estudiantes_actuales > $capacidad_maxima) {
        throw new Exception("No se puede reducir la capacidad por debajo de {$estudiantes_actuales} (estudiantes actuales matriculados)");
    }

    // Generar código si no se proporcionó
    if (empty($codigo)) {
        $codigo = generarCodigoSeccion($conexion, $nivel_id, $grado, $seccion, $aula_id);
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    try {
        // Actualizar sección
        $sql = "UPDATE secciones SET 
                nivel_id = ?, grado = ?, seccion = ?, codigo = ?, 
                capacidad_maxima = ?, aula_asignada = ?, periodo_academico_id = ?, activo = ?
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nivel_id, $grado, $seccion, $codigo,
            $capacidad_maxima, $aula_asignada, $periodo_academico_id, $activo,
            $aula_id
        ]);

        // Confirmar transacción
        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Aula actualizada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollBack();
        throw $e;
    }
}

/**
 * Obtener detalle completo del aula
 */
function detalleAula($conexion) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de aula no válido');
    }

    // Información principal del aula
    $sql = "SELECT s.*, 
                   ne.nombre as nivel_nombre,
                   pa.nombre as periodo_nombre, pa.anio as periodo_anio,
                   COUNT(m.id) as estudiantes_matriculados,
                   ROUND((COUNT(m.id) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion,
                   CASE 
                       WHEN COUNT(m.id) = 0 THEN 'DISPONIBLE'
                       WHEN COUNT(m.id) < s.capacidad_maxima THEN 'OCUPADA'
                       WHEN COUNT(m.id) >= s.capacidad_maxima THEN 'COMPLETA'
                       ELSE 'DISPONIBLE'
                   END as estado_ocupacion
            FROM secciones s
            LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
            LEFT JOIN periodos_academicos pa ON s.periodo_academico_id = pa.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1 AND m.estado = 'MATRICULADO'
            WHERE s.id = ?
            GROUP BY s.id";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $aula = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aula) {
        throw new Exception('Aula no encontrada');
    }

    // Obtener asignaciones de docentes
    $sql_asignaciones = "SELECT ad.*, d.nombres, d.apellidos, ac.nombre as area_nombre
                        FROM asignaciones_docentes ad
                        JOIN docentes d ON ad.docente_id = d.id
                        JOIN areas_curriculares ac ON ad.area_id = ac.id
                        WHERE ad.seccion_id = ? AND ad.activo = 1";
    
    $stmt_asignaciones = $conexion->prepare($sql_asignaciones);
    $stmt_asignaciones->execute([$id]);
    $asignaciones = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);

    $aula['asignaciones'] = $asignaciones;

    return [
        'success' => true,
        'aula' => $aula
    ];
}

/**
 * Obtener estudiantes del aula
 */
function estudiantesAula($conexion) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de aula no válido');
    }

    $sql = "SELECT e.id, e.codigo_estudiante, e.nombres, e.apellidos, e.documento_numero, 
                   e.foto_url, e.activo, m.fecha_matricula, m.estado as estado_matricula
            FROM estudiantes e
            JOIN matriculas m ON e.id = m.estudiante_id
            WHERE m.seccion_id = ? AND m.activo = 1
            ORDER BY e.apellidos, e.nombres";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'estudiantes' => $estudiantes,
        'total' => count($estudiantes)
    ];
}

/**
 * Obtener programación del aula
 */
function programacionAula($conexion) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        throw new Exception('ID de aula no válido');
    }

    // Por ahora devolver programación de ejemplo
    // En una implementación completa, esto vendría de una tabla de horarios
    $programacion = [
        'aula_id' => $id,
        'horarios' => [
            [
                'dia' => 'Lunes',
                'bloques' => [
                    ['hora' => '8:00-8:45', 'area' => 'Matemática', 'docente' => 'Luis Correa'],
                    ['hora' => '8:45-9:30', 'area' => 'Matemática', 'docente' => 'Luis Correa'],
                    ['hora' => '10:00-10:45', 'area' => 'Comunicación', 'docente' => 'María Rojas'],
                    ['hora' => '10:45-11:30', 'area' => 'Comunicación', 'docente' => 'María Rojas']
                ]
            ],
            [
                'dia' => 'Martes',
                'bloques' => [
                    ['hora' => '8:00-8:45', 'area' => 'Comunicación', 'docente' => 'María Rojas'],
                    ['hora' => '8:45-9:30', 'area' => 'Comunicación', 'docente' => 'María Rojas'],
                    ['hora' => '10:00-10:45', 'area' => 'Matemática', 'docente' => 'Luis Correa'],
                    ['hora' => '10:45-11:30', 'area' => 'Matemática', 'docente' => 'Luis Correa']
                ]
            ]
        ]
    ];

    return [
        'success' => true,
        'programacion' => $programacion
    ];
}

/**
 * Generar código único de sección
 */
function generarCodigoSeccion($conexion, $nivel_id, $grado, $seccion, $excluir_id = null) {
    // Obtener información del nivel
    $stmt = $conexion->prepare("SELECT codigo FROM niveles_educativos WHERE id = ?");
    $stmt->execute([$nivel_id]);
    $nivel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$nivel) {
        throw new Exception('Nivel educativo no encontrado');
    }

    // Generar código base
    $nivelCodigo = '';
    switch (strtoupper($nivel['codigo'])) {
        case 'INI':
            $nivelCodigo = 'I';
            break;
        case 'PRI':
            $nivelCodigo = 'P';
            break;
        case 'SEC':
            $nivelCodigo = 'S';
            break;
        default:
            $nivelCodigo = substr($nivel['codigo'], 0, 1);
    }

    $anio = date('Y');
    $codigo_base = "{$grado}{$nivelCodigo}{$seccion}-{$anio}";
    
    // Verificar si el código ya existe
    $sql = "SELECT id FROM secciones WHERE codigo = ?";
    $params = [$codigo_base];
    
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    
    // Si no existe, usar el código base
    if (!$stmt->fetch()) {
        return $codigo_base;
    }
    
    // Si existe, agregar un sufijo numérico
    $contador = 1;
    do {
        $codigo_nuevo = "{$codigo_base}-{$contador}";
        
        $params = [$codigo_nuevo];
        if ($excluir_id) {
            $params[] = $excluir_id;
        }
        
        $stmt->execute($params);
        $existe = $stmt->fetch();
        
        if (!$existe) {
            return $codigo_nuevo;
        }
        
        $contador++;
    } while ($contador <= 99);
    
    throw new Exception('No se pudo generar un código único para la sección');
}

/**
 * Validar permisos de usuario (implementar según tu sistema de permisos)
 */
function validarPermisos($accion) {
    // Por ahora siempre retorna true
    // Implementar validación de permisos según tu sistema
    return true;
}

?>