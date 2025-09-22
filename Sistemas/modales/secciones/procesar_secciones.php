<?php
// modales/secciones/procesar_secciones.php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['accion'])) {
        throw new Exception('Acción no especificada');
    }

    $accion = $_POST['accion'];
    $response = ['success' => false, 'message' => ''];

    switch ($accion) {
        case 'crear':
            $response = crearSeccion();
            break;
        
        case 'obtener':
            $response = obtenerSeccion();
            break;
        
        case 'editar':
            $response = editarSeccion();
            break;
        
        case 'detalle':
            $response = obtenerDetalleSeccion();
            break;
        
        case 'estudiantes':
            $response = obtenerEstudiantesSeccion();
            break;
        
        case 'toggle_estado':
            $response = toggleEstadoSeccion();
            break;
        
        case 'validar_cambios':
            $response = validarCambiosSeccion();
            break;
        
        default:
            throw new Exception('Acción no válida');
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function crearSeccion() {
    global $conexion;
    
    try {
        // Validar datos requeridos
        $camposRequeridos = ['nivel_id', 'periodo_academico_id', 'grado', 'seccion', 'capacidad_maxima'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        $nivel_id = intval($_POST['nivel_id']);
        $periodo_id = intval($_POST['periodo_academico_id']);
        $grado = trim($_POST['grado']);
        $seccion = strtoupper(trim($_POST['seccion']));
        $capacidad_maxima = intval($_POST['capacidad_maxima']);
        $aula_asignada = !empty($_POST['aula_asignada']) ? trim($_POST['aula_asignada']) : null;
        
        // Generar código automático si no se proporcionó
        $codigo = !empty($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : null;
        if (!$codigo) {
            $codigo = generarCodigoSeccion($nivel_id, $grado, $seccion, $periodo_id);
        }
        
        // Validar que el código no exista
        $stmt = $conexion->prepare("SELECT id FROM secciones WHERE codigo = ? AND activo = 1");
        $stmt->execute([$codigo]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe una sección con ese código');
        }
        
        // Validar que no exista la combinación nivel+grado+sección+período
        $stmt = $conexion->prepare("SELECT id FROM secciones WHERE nivel_id = ? AND grado = ? AND seccion = ? AND periodo_academico_id = ? AND activo = 1");
        $stmt->execute([$nivel_id, $grado, $seccion, $periodo_id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe una sección con esa combinación de nivel, grado y sección para este período');
        }
        
        // Validar capacidad
        if ($capacidad_maxima < 1 || $capacidad_maxima > 50) {
            throw new Exception('La capacidad debe estar entre 1 y 50 estudiantes');
        }
        
        // Validar que el nivel y período existan
        $stmt = $conexion->prepare("SELECT nombre FROM niveles_educativos WHERE id = ? AND activo = 1");
        $stmt->execute([$nivel_id]);
        if (!$stmt->fetch()) {
            throw new Exception('El nivel educativo seleccionado no existe o está inactivo');
        }
        
        $stmt = $conexion->prepare("SELECT nombre FROM periodos_academicos WHERE id = ? AND activo = 1");
        $stmt->execute([$periodo_id]);
        if (!$stmt->fetch()) {
            throw new Exception('El período académico seleccionado no existe o está inactivo');
        }
        
        // Insertar nueva sección
        $sql = "INSERT INTO secciones (nivel_id, grado, seccion, codigo, capacidad_maxima, aula_asignada, periodo_academico_id, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nivel_id, $grado, $seccion, $codigo, $capacidad_maxima, $aula_asignada, $periodo_id]);
        
        return [
            'success' => true,
            'message' => 'Sección creada exitosamente',
            'id' => $conexion->lastInsertId()
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al crear sección: ' . $e->getMessage());
    }
}

function obtenerSeccion() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de sección no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT s.*, 
                    ne.nombre as nivel_nombre,
                    pa.nombre as periodo_nombre,
                    COUNT(m.id) as estudiantes_matriculados,
                    COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) as estudiantes_activos
                FROM secciones s
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
                LEFT JOIN periodos_academicos pa ON s.periodo_academico_id = pa.id
                LEFT JOIN matriculas m ON s.id = m.seccion_id
                WHERE s.id = ?
                GROUP BY s.id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccion) {
            throw new Exception('Sección no encontrada');
        }
        
        return [
            'success' => true,
            'seccion' => $seccion
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener sección: ' . $e->getMessage());
    }
}

function editarSeccion() {
    global $conexion;
    
    try {
        $camposRequeridos = ['id', 'nivel_id', 'periodo_academico_id', 'grado', 'seccion', 'codigo', 'capacidad_maxima'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        $id = intval($_POST['id']);
        $nivel_id = intval($_POST['nivel_id']);
        $periodo_id = intval($_POST['periodo_academico_id']);
        $grado = trim($_POST['grado']);
        $seccion = strtoupper(trim($_POST['seccion']));
        $codigo = strtoupper(trim($_POST['codigo']));
        $capacidad_maxima = intval($_POST['capacidad_maxima']);
        $aula_asignada = !empty($_POST['aula_asignada']) ? trim($_POST['aula_asignada']) : null;
        $activo = intval($_POST['activo']);
        
        // Validar que el código no exista en otra sección
        $stmt = $conexion->prepare("SELECT id FROM secciones WHERE codigo = ? AND id != ? AND activo = 1");
        $stmt->execute([$codigo, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otra sección con ese código');
        }
        
        // Validar que no exista la combinación en otra sección
        $stmt = $conexion->prepare("SELECT id FROM secciones WHERE nivel_id = ? AND grado = ? AND seccion = ? AND periodo_academico_id = ? AND id != ? AND activo = 1");
        $stmt->execute([$nivel_id, $grado, $seccion, $periodo_id, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otra sección con esa combinación para este período');
        }
        
        // Validar capacidad vs estudiantes matriculados
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM matriculas WHERE seccion_id = ? AND estado = 'MATRICULADO' AND activo = 1");
        $stmt->execute([$id]);
        $estudiantesActivos = $stmt->fetch(PDO::FETCH_COLUMN);
        
        if ($capacidad_maxima < $estudiantesActivos) {
            throw new Exception("No se puede reducir la capacidad a {$capacidad_maxima}. Hay {$estudiantesActivos} estudiantes matriculados actualmente.");
        }
        
        // Actualizar sección
        $sql = "UPDATE secciones 
                SET nivel_id = ?, grado = ?, seccion = ?, codigo = ?, capacidad_maxima = ?, 
                    aula_asignada = ?, periodo_academico_id = ?, activo = ?
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nivel_id, $grado, $seccion, $codigo, $capacidad_maxima, $aula_asignada, $periodo_id, $activo, $id]);
        
        return [
            'success' => true,
            'message' => 'Sección actualizada exitosamente'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al actualizar sección: ' . $e->getMessage());
    }
}

function obtenerDetalleSeccion() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de sección no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT s.*, 
                    ne.nombre as nivel_nombre,
                    pa.nombre as periodo_nombre,
                    COUNT(m.id) as estudiantes_matriculados,
                    COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) as estudiantes_activos,
                    (s.capacidad_maxima - COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END)) as cupos_disponibles
                FROM secciones s
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
                LEFT JOIN periodos_academicos pa ON s.periodo_academico_id = pa.id
                LEFT JOIN matriculas m ON s.id = m.seccion_id
                WHERE s.id = ?
                GROUP BY s.id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccion) {
            throw new Exception('Sección no encontrada');
        }
        
        return [
            'success' => true,
            'seccion' => $seccion
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener detalle: ' . $e->getMessage());
    }
}

function obtenerEstudiantesSeccion() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de sección no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT e.codigo_estudiante, e.nombres, e.apellidos, m.estado, m.fecha_matricula, m.activo
                FROM estudiantes e
                INNER JOIN matriculas m ON e.id = m.estudiante_id
                WHERE m.seccion_id = ?
                ORDER BY e.apellidos, e.nombres";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'estudiantes' => $estudiantes
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener estudiantes: ' . $e->getMessage());
    }
}

function toggleEstadoSeccion() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || !isset($_POST['estado'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $estado = $_POST['estado'] === 'true' ? 1 : 0;
        
        // Verificar si tiene estudiantes activos antes de desactivar
        if ($estado == 0) {
            $sql = "SELECT COUNT(*) FROM matriculas WHERE seccion_id = ? AND estado = 'MATRICULADO' AND activo = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id]);
            $estudiantes = $stmt->fetch(PDO::FETCH_COLUMN);
            
            if ($estudiantes > 0) {
                throw new Exception("No se puede desactivar la sección. Tiene {$estudiantes} estudiantes matriculados activos.");
            }
        }
        
        // Actualizar estado
        $sql = "UPDATE secciones SET activo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$estado, $id]);
        
        $mensaje = $estado ? 'Sección activada exitosamente' : 'Sección desactivada exitosamente';
        
        return [
            'success' => true,
            'message' => $mensaje
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al cambiar estado: ' . $e->getMessage());
    }
}

function validarCambiosSeccion() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || !isset($_POST['capacidad_maxima'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $nuevaCapacidad = intval($_POST['capacidad_maxima']);
        
        $errores = [];
        $advertencias = [];
        
        // Obtener datos actuales de la sección
        $stmt = $conexion->prepare("SELECT capacidad_maxima, codigo FROM secciones WHERE id = ?");
        $stmt->execute([$id]);
        $seccionActual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$seccionActual) {
            throw new Exception('Sección no encontrada');
        }
        
        // Verificar estudiantes matriculados
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM matriculas WHERE seccion_id = ? AND estado = 'MATRICULADO' AND activo = 1");
        $stmt->execute([$id]);
        $estudiantesActivos = $stmt->fetch(PDO::FETCH_COLUMN);
        
        // Validación de capacidad
        if ($nuevaCapacidad < $estudiantesActivos) {
            $errores[] = "No se puede reducir la capacidad a {$nuevaCapacidad}. Hay {$estudiantesActivos} estudiantes matriculados activos.";
        } elseif ($nuevaCapacidad < $seccionActual['capacidad_maxima'] && $estudiantesActivos > 0) {
            $advertencias[] = "Se reducirá la capacidad de {$seccionActual['capacidad_maxima']} a {$nuevaCapacidad} estudiantes. Actualmente hay {$estudiantesActivos} matriculados.";
        }
        
        // Verificar si la nueva capacidad causará sobrecupo en otras validaciones del sistema
        if ($nuevaCapacidad > 40) {
            $advertencias[] = "La capacidad de {$nuevaCapacidad} estudiantes es elevada. Se recomienda no exceder los 35 estudiantes por sección.";
        }
        
        return [
            'success' => true,
            'errores' => $errores,
            'advertencias' => $advertencias,
            'ok' => empty($errores) && empty($advertencias)
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error en validación: ' . $e->getMessage());
    }
}

function generarCodigoSeccion($nivel_id, $grado, $seccion, $periodo_id) {
    global $conexion;
    
    try {
        // Obtener información del nivel
        $stmt = $conexion->prepare("SELECT codigo FROM niveles_educativos WHERE id = ?");
        $stmt->execute([$nivel_id]);
        $nivelCodigo = $stmt->fetch(PDO::FETCH_COLUMN);
        
        // Obtener año del período
        $stmt = $conexion->prepare("SELECT anio FROM periodos_academicos WHERE id = ?");
        $stmt->execute([$periodo_id]);
        $anio = $stmt->fetch(PDO::FETCH_COLUMN);
        
        if (!$nivelCodigo || !$anio) {
            throw new Exception('No se pudo generar el código automáticamente');
        }
        
        // Formato: [GRADO][NIVEL_CODIGO][SECCION]-[AÑO]
        // Ejemplo: 1SA-2025 (1ro Secundaria A - 2025)
        $codigo = $grado . $nivelCodigo . $seccion . '-' . $anio;
        
        return strtoupper($codigo);
        
    } catch (Exception $e) {
        throw new Exception('Error al generar código: ' . $e->getMessage());
    }
}

function validarDuplicadosSeccion($nivel_id, $grado, $seccion, $periodo_id, $excluir_id = null) {
    global $conexion;
    
    $sql = "SELECT id FROM secciones WHERE nivel_id = ? AND grado = ? AND seccion = ? AND periodo_academico_id = ? AND activo = 1";
    $params = [$nivel_id, $grado, $seccion, $periodo_id];
    
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch() !== false;
}
?>