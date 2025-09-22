<?php
// modales/niveles/procesar_niveles.php
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
            $response = crearNivel();
            break;
        
        case 'obtener':
            $response = obtenerNivel();
            break;
        
        case 'editar':
            $response = editarNivel();
            break;
        
        case 'toggle_estado':
            $response = toggleEstadoNivel();
            break;
        
        case 'actualizar_orden':
            $response = actualizarOrdenNiveles();
            break;
        
        case 'estudiantes_nivel':
            $response = obtenerEstudiantesNivel();
            break;
        
        case 'validar_cambios':
            $response = validarCambiosNivel();
            break;
        
        case 'validar_sistema':
            $response = ejecutarValidacionesSistema();
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

function crearNivel() {
    global $conexion;
    
    try {
        // Validar datos requeridos
        if (empty($_POST['nombre']) || empty($_POST['codigo'])) {
            throw new Exception('Nombre y código son requeridos');
        }
        
        $nombre = trim($_POST['nombre']);
        $codigo = strtoupper(trim($_POST['codigo']));
        $orden = intval($_POST['orden']) ?: 1;
        $grados = isset($_POST['grados']) ? json_decode($_POST['grados'], true) : [];
        
        // Validar que el código no exista
        $stmt = $conexion->prepare("SELECT id FROM niveles_educativos WHERE codigo = ? AND activo = 1");
        $stmt->execute([$codigo]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un nivel con ese código');
        }
        
        // Validar grados
        if (empty($grados)) {
            throw new Exception('Debe agregar al menos un grado al nivel');
        }
        
        foreach ($grados as $grado) {
            if (empty($grado['nombre']) || empty($grado['codigo'])) {
                throw new Exception('Todos los grados deben tener nombre y código');
            }
            
            if (!isset($grado['edad_min']) || !isset($grado['edad_max'])) {
                throw new Exception('Todos los grados deben tener edades definidas');
            }
            
            if ($grado['edad_min'] > $grado['edad_max']) {
                throw new Exception('La edad mínima no puede ser mayor que la máxima');
            }
        }
        
        // Obtener el siguiente orden si no se especificó
        if (!$orden) {
            $stmt = $conexion->prepare("SELECT COALESCE(MAX(orden), 0) + 1 as siguiente_orden FROM niveles_educativos");
            $stmt->execute();
            $orden = $stmt->fetch(PDO::FETCH_COLUMN);
        }
        
        // Insertar nuevo nivel
        $sql = "INSERT INTO niveles_educativos (nombre, codigo, grados, orden, activo) VALUES (?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $codigo, json_encode($grados), $orden]);
        
        return [
            'success' => true,
            'message' => 'Nivel educativo creado exitosamente',
            'id' => $conexion->lastInsertId()
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al crear nivel: ' . $e->getMessage());
    }
}

function obtenerNivel() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de nivel no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT ne.*, 
                    COUNT(DISTINCT s.id) as total_secciones,
                    COUNT(DISTINCT m.id) as total_estudiantes,
                    COUNT(DISTINCT CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN m.id END) as estudiantes_activos
                FROM niveles_educativos ne
                LEFT JOIN secciones s ON ne.id = s.nivel_id AND s.activo = 1
                LEFT JOIN matriculas m ON s.id = m.seccion_id
                WHERE ne.id = ?
                GROUP BY ne.id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $nivel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$nivel) {
            throw new Exception('Nivel no encontrado');
        }
        
        // Decodificar grados
        $nivel['grados'] = json_decode($nivel['grados'], true) ?: [];
        
        return [
            'success' => true,
            'nivel' => $nivel
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener nivel: ' . $e->getMessage());
    }
}

function editarNivel() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || empty($_POST['nombre']) || empty($_POST['codigo'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $codigo = strtoupper(trim($_POST['codigo']));
        $orden = intval($_POST['orden']);
        $activo = intval($_POST['activo']);
        $grados = isset($_POST['grados']) ? json_decode($_POST['grados'], true) : [];
        
        // Validar que el código no exista en otro nivel
        $stmt = $conexion->prepare("SELECT id FROM niveles_educativos WHERE codigo = ? AND id != ? AND activo = 1");
        $stmt->execute([$codigo, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otro nivel con ese código');
        }
        
        // Validar grados
        if (empty($grados)) {
            throw new Exception('Debe tener al menos un grado configurado');
        }
        
        foreach ($grados as $grado) {
            if (empty($grado['nombre']) || empty($grado['codigo'])) {
                throw new Exception('Todos los grados deben tener nombre y código');
            }
            
            if (!isset($grado['edad_min']) || !isset($grado['edad_max'])) {
                throw new Exception('Todos los grados deben tener edades definidas');
            }
            
            if ($grado['edad_min'] > $grado['edad_max']) {
                throw new Exception('La edad mínima no puede ser mayor que la máxima');
            }
        }
        
        // Actualizar nivel
        $sql = "UPDATE niveles_educativos 
                SET nombre = ?, codigo = ?, grados = ?, orden = ?, activo = ?
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $codigo, json_encode($grados), $orden, $activo, $id]);
        
        return [
            'success' => true,
            'message' => 'Nivel educativo actualizado exitosamente'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al actualizar nivel: ' . $e->getMessage());
    }
}

function toggleEstadoNivel() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || !isset($_POST['estado'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $estado = $_POST['estado'] === 'true' ? 1 : 0;
        
        // Verificar si tiene estudiantes activos
        if ($estado == 0) {
            $sql = "SELECT COUNT(DISTINCT m.id) as estudiantes_activos
                    FROM secciones s
                    INNER JOIN matriculas m ON s.id = m.seccion_id
                    WHERE s.nivel_id = ? AND m.estado = 'MATRICULADO' AND m.activo = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id]);
            $estudiantes = $stmt->fetch(PDO::FETCH_COLUMN);
            
            if ($estudiantes > 0) {
                throw new Exception("No se puede desactivar el nivel. Tiene {$estudiantes} estudiantes activos matriculados.");
            }
        }
        
        // Actualizar estado
        $sql = "UPDATE niveles_educativos SET activo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$estado, $id]);
        
        $mensaje = $estado ? 'Nivel activado exitosamente' : 'Nivel desactivado exitosamente';
        
        return [
            'success' => true,
            'message' => $mensaje
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al cambiar estado: ' . $e->getMessage());
    }
}

function actualizarOrdenNiveles() {
    global $conexion;
    
    try {
        if (!isset($_POST['orden'])) {
            throw new Exception('Datos de orden no especificados');
        }
        
        $orden = json_decode($_POST['orden'], true);
        
        if (!is_array($orden)) {
            throw new Exception('Formato de orden inválido');
        }
        
        $conexion->beginTransaction();
        
        foreach ($orden as $item) {
            $sql = "UPDATE niveles_educativos SET orden = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$item['orden'], $item['id']]);
        }
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Orden actualizado exitosamente'
        ];
        
    } catch (Exception $e) {
        $conexion->rollback();
        throw new Exception('Error al actualizar orden: ' . $e->getMessage());
    }
}

function obtenerEstudiantesNivel() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de nivel no especificado');
        }
        
        $id = intval($_POST['id']);
        
        // Obtener nombre del nivel
        $stmt = $conexion->prepare("SELECT nombre FROM niveles_educativos WHERE id = ?");
        $stmt->execute([$id]);
        $nivel_nombre = $stmt->fetch(PDO::FETCH_COLUMN);
        
        if (!$nivel_nombre) {
            throw new Exception('Nivel no encontrado');
        }
        
        // Obtener estudiantes del nivel
        $sql = "SELECT e.nombres, e.apellidos, s.grado, s.seccion, m.activo
                FROM estudiantes e
                INNER JOIN matriculas m ON e.id = m.estudiante_id
                INNER JOIN secciones s ON m.seccion_id = s.id
                WHERE s.nivel_id = ? AND m.estado = 'MATRICULADO'
                ORDER BY s.grado, s.seccion, e.apellidos, e.nombres";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'estudiantes' => $estudiantes,
            'nivel_nombre' => $nivel_nombre
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener estudiantes: ' . $e->getMessage());
    }
}

function validarCambiosNivel() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || !isset($_POST['grados'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $nuevosGrados = json_decode($_POST['grados'], true);
        
        $errores = [];
        $advertencias = [];
        
        // Obtener grados actuales
        $stmt = $conexion->prepare("SELECT grados FROM niveles_educativos WHERE id = ?");
        $stmt->execute([$id]);
        $gradosActuales = json_decode($stmt->fetch(PDO::FETCH_COLUMN), true) ?: [];
        
        // Validar rangos de edad sin solapamientos entre niveles
        $sql = "SELECT id, nombre, grados FROM niveles_educativos WHERE id != ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $otrosNiveles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($nuevosGrados as $nuevoGrado) {
            foreach ($otrosNiveles as $otroNivel) {
                $otrosGrados = json_decode($otroNivel['grados'], true) ?: [];
                foreach ($otrosGrados as $otroGrado) {
                    // Verificar solapamiento de edades
                    if (($nuevoGrado['edad_min'] >= $otroGrado['edad_min'] && $nuevoGrado['edad_min'] <= $otroGrado['edad_max']) ||
                        ($nuevoGrado['edad_max'] >= $otroGrado['edad_min'] && $nuevoGrado['edad_max'] <= $otroGrado['edad_max'])) {
                        $advertencias[] = "El grado '{$nuevoGrado['nombre']}' tiene edades que se solapan con el grado '{$otroGrado['nombre']}' del nivel '{$otroNivel['nombre']}'";
                    }
                }
            }
        }
        
        // Verificar estudiantes que podrían verse afectados
        $sql = "SELECT s.grado, COUNT(m.id) as total_estudiantes
                FROM secciones s
                INNER JOIN matriculas m ON s.id = m.seccion_id
                WHERE s.nivel_id = ? AND m.estado = 'MATRICULADO' AND m.activo = 1
                GROUP BY s.grado";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $estudiantesPorGrado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $gradosActualesNombres = array_column($gradosActuales, 'nombre');
        $nuevosGradosNombres = array_column($nuevosGrados, 'nombre');
        
        foreach ($estudiantesPorGrado as $estudiantesGrado) {
            if (!in_array($estudiantesGrado['grado'], $nuevosGradosNombres)) {
                $advertencias[] = "El grado '{$estudiantesGrado['grado']}' será eliminado pero tiene {$estudiantesGrado['total_estudiantes']} estudiantes matriculados";
            }
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

function ejecutarValidacionesSistema() {
    global $conexion;
    
    try {
        $errores = [];
        $advertencias = [];
        
        // Validar niveles duplicados
        $sql = "SELECT codigo, COUNT(*) as total 
                FROM niveles_educativos 
                WHERE activo = 1 
                GROUP BY codigo 
                HAVING total > 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($duplicados as $dup) {
            $errores[] = "Código '{$dup['codigo']}' duplicado en {$dup['total']} niveles";
        }
        
        // Validar niveles sin grados
        $sql = "SELECT nombre FROM niveles_educativos 
                WHERE activo = 1 AND (grados IS NULL OR grados = '[]' OR grados = '')";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $sinGrados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($sinGrados as $nivel) {
            $errores[] = "El nivel '{$nivel}' no tiene grados configurados";
        }
        
        // Validar solapamientos de edad entre niveles
        $sql = "SELECT id, nombre, grados FROM niveles_educativos WHERE activo = 1 ORDER BY orden";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $niveles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        for ($i = 0; $i < count($niveles); $i++) {
            $grados1 = json_decode($niveles[$i]['grados'], true) ?: [];
            
            for ($j = $i + 1; $j < count($niveles); $j++) {
                $grados2 = json_decode($niveles[$j]['grados'], true) ?: [];
                
                foreach ($grados1 as $grado1) {
                    foreach ($grados2 as $grado2) {
                        if (isset($grado1['edad_min']) && isset($grado2['edad_min'])) {
                            if (($grado1['edad_min'] >= $grado2['edad_min'] && $grado1['edad_min'] <= $grado2['edad_max']) ||
                                ($grado1['edad_max'] >= $grado2['edad_min'] && $grado1['edad_max'] <= $grado2['edad_max'])) {
                                $advertencias[] = "Solapamiento de edades entre '{$niveles[$i]['nombre']}' grado '{$grado1['nombre']}' y '{$niveles[$j]['nombre']}' grado '{$grado2['nombre']}'";
                            }
                        }
                    }
                }
            }
        }
        
        // Validar estudiantes con edades fuera de rango
        $sql = "SELECT e.nombres, e.apellidos, e.fecha_nacimiento, s.grado, ne.nombre as nivel
                FROM estudiantes e
                INNER JOIN matriculas m ON e.id = m.estudiante_id
                INNER JOIN secciones s ON m.seccion_id = s.id
                INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
                WHERE m.estado = 'MATRICULADO' AND m.activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($estudiantes as $estudiante) {
            $edad = calcularEdad($estudiante['fecha_nacimiento']);
            // Aquí se podría validar si la edad del estudiante coincide con el grado
            // Esta validación requeriría más lógica específica
        }
        
        return [
            'success' => true,
            'errores' => $errores,
            'advertencias' => $advertencias,
            'ok' => empty($errores) && empty($advertencias)
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error en validaciones del sistema: ' . $e->getMessage());
    }
}

function calcularEdad($fechaNacimiento) {
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    return $hoy->diff($nacimiento)->y;
}
?>