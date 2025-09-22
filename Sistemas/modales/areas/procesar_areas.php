<?php
// modales/areas/procesar_areas.php
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
            $response = crearArea();
            break;
        
        case 'obtener':
            $response = obtenerArea();
            break;
        
        case 'editar':
            $response = editarArea();
            break;
        
        case 'toggle_estado':
            $response = toggleEstadoArea();
            break;
        
        case 'obtener_competencias':
            $response = obtenerCompetenciasArea();
            break;
        
        case 'obtener_completo':
            $response = obtenerAreaCompleta();
            break;
        
        case 'docentes_area':
            $response = obtenerDocentesArea();
            break;
        
        case 'guardar_competencias':
            $response = guardarCompetencias();
            break;
        
        case 'validar_area':
            $response = validarArea();
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

function crearArea() {
    global $conexion;
    
    try {
        // Validar datos requeridos
        if (empty($_POST['nombre']) || empty($_POST['codigo'])) {
            throw new Exception('Nombre y código son requeridos');
        }
        
        $nombre = trim($_POST['nombre']);
        $codigo = strtoupper(trim($_POST['codigo']));
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usar_predefinidas = isset($_POST['usar_predefinidas']) && $_POST['usar_predefinidas'] == '1';
        
        // Validar que el código no exista
        $stmt = $conexion->prepare("SELECT id FROM areas_curriculares WHERE codigo = ? AND activo = 1");
        $stmt->execute([$codigo]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un área con ese código');
        }
        
        // Validar longitud del código
        if (strlen($codigo) < 2) {
            throw new Exception('El código debe tener al menos 2 caracteres');
        }
        
        $competencias = null;
        
        // Generar competencias predefinidas si se solicitó
        if ($usar_predefinidas && isset($_POST['niveles_aplicar'])) {
            $niveles_aplicar = json_decode($_POST['niveles_aplicar'], true);
            if (!empty($niveles_aplicar)) {
                $competencias = generarCompetenciasPredefinidas($codigo, $niveles_aplicar);
            }
        }
        
        // Insertar nueva área
        $sql = "INSERT INTO areas_curriculares (nombre, codigo, descripcion, competencias, activo) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $nombre, 
            $codigo, 
            $descripcion ?: null, 
            $competencias ? json_encode($competencias) : null
        ]);
        
        $mensaje = 'Área curricular creada exitosamente';
        if ($competencias) {
            $total_competencias = contarCompetencias($competencias);
            $mensaje .= " con {$total_competencias} competencias predefinidas";
        }
        
        return [
            'success' => true,
            'message' => $mensaje,
            'id' => $conexion->lastInsertId()
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al crear área: ' . $e->getMessage());
    }
}

function obtenerArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de área no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT ac.*, 
                    COUNT(DISTINCT ad.id) as total_asignaciones,
                    COUNT(DISTINCT ad.docente_id) as docentes_asignados,
                    COUNT(DISTINCT s.nivel_id) as niveles_atendidos,
                    GROUP_CONCAT(DISTINCT ne.nombre SEPARATOR ', ') as niveles_nombres
                FROM areas_curriculares ac
                LEFT JOIN asignaciones_docentes ad ON ac.id = ad.area_id AND ad.activo = 1
                LEFT JOIN secciones s ON ad.seccion_id = s.id AND s.activo = 1
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id AND ne.activo = 1
                WHERE ac.id = ?
                GROUP BY ac.id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $area = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$area) {
            throw new Exception('Área no encontrada');
        }
        
        return [
            'success' => true,
            'area' => $area
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener área: ' . $e->getMessage());
    }
}

function editarArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || empty($_POST['nombre']) || empty($_POST['codigo'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $codigo = strtoupper(trim($_POST['codigo']));
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = intval($_POST['activo'] ?? 1);
        
        // Validar que el código no exista en otra área
        $stmt = $conexion->prepare("SELECT id FROM areas_curriculares WHERE codigo = ? AND id != ? AND activo = 1");
        $stmt->execute([$codigo, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe otra área con ese código');
        }
        
        // Validar longitud del código
        if (strlen($codigo) < 2) {
            throw new Exception('El código debe tener al menos 2 caracteres');
        }
        
        // Verificar si tiene asignaciones activas antes de desactivar
        if ($activo == 0) {
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM asignaciones_docentes WHERE area_id = ? AND activo = 1");
            $stmt->execute([$id]);
            $asignaciones = $stmt->fetch(PDO::FETCH_COLUMN);
            
            if ($asignaciones > 0) {
                throw new Exception("No se puede desactivar el área. Tiene {$asignaciones} asignaciones docentes activas.");
            }
        }
        
        // Actualizar área
        $sql = "UPDATE areas_curriculares 
                SET nombre = ?, codigo = ?, descripcion = ?, activo = ?
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $codigo, $descripcion ?: null, $activo, $id]);
        
        return [
            'success' => true,
            'message' => 'Área curricular actualizada exitosamente'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al actualizar área: ' . $e->getMessage());
    }
}

function toggleEstadoArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id']) || !isset($_POST['estado'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $id = intval($_POST['id']);
        $estado = $_POST['estado'] === 'true' ? 1 : 0;
        
        // Verificar si tiene asignaciones activas
        if ($estado == 0) {
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM asignaciones_docentes WHERE area_id = ? AND activo = 1");
            $stmt->execute([$id]);
            $asignaciones = $stmt->fetch(PDO::FETCH_COLUMN);
            
            if ($asignaciones > 0) {
                throw new Exception("No se puede desactivar el área. Tiene {$asignaciones} asignaciones docentes activas.");
            }
        }
        
        // Actualizar estado
        $sql = "UPDATE areas_curriculares SET activo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$estado, $id]);
        
        $mensaje = $estado ? 'Área activada exitosamente' : 'Área desactivada exitosamente';
        
        return [
            'success' => true,
            'message' => $mensaje
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al cambiar estado: ' . $e->getMessage());
    }
}

function obtenerCompetenciasArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de área no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $stmt = $conexion->prepare("SELECT competencias FROM areas_curriculares WHERE id = ?");
        $stmt->execute([$id]);
        $competencias_json = $stmt->fetch(PDO::FETCH_COLUMN);
        
        $competencias = null;
        if ($competencias_json) {
            $competencias = json_decode($competencias_json, true);
        }
        
        return [
            'success' => true,
            'competencias' => $competencias
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener competencias: ' . $e->getMessage());
    }
}

function obtenerAreaCompleta() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de área no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $stmt = $conexion->prepare("SELECT * FROM areas_curriculares WHERE id = ?");
        $stmt->execute([$id]);
        $area = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$area) {
            throw new Exception('Área no encontrada');
        }
        
        return [
            'success' => true,
            'area' => $area
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener área completa: ' . $e->getMessage());
    }
}

function obtenerDocentesArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de área no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $sql = "SELECT d.nombres, d.apellidos, s.grado, s.seccion, ad.horas_semanales, ad.es_tutor
                FROM docentes d
                INNER JOIN asignaciones_docentes ad ON d.id = ad.docente_id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                WHERE ad.area_id = ? AND ad.activo = 1 AND s.activo = 1
                ORDER BY s.grado, s.seccion, d.apellidos, d.nombres";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'docentes' => $docentes
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al obtener docentes: ' . $e->getMessage());
    }
}

function guardarCompetencias() {
    global $conexion;
    
    try {
        if (!isset($_POST['area_id']) || !isset($_POST['competencias'])) {
            throw new Exception('Datos requeridos faltantes');
        }
        
        $area_id = intval($_POST['area_id']);
        $competencias_json = $_POST['competencias'];
        
        // Validar JSON
        $competencias = json_decode($competencias_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Formato de competencias inválido');
        }
        
        // Validar estructura de competencias
        $errores_validacion = validarEstructuraCompetencias($competencias);
        if (!empty($errores_validacion)) {
            throw new Exception('Errores en competencias: ' . implode(', ', $errores_validacion));
        }
        
        // Actualizar competencias
        $sql = "UPDATE areas_curriculares SET competencias = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$competencias_json, $area_id]);
        
        $total_competencias = contarCompetencias($competencias);
        
        return [
            'success' => true,
            'message' => "Competencias guardadas exitosamente. Total: {$total_competencias} competencias"
        ];
        
    } catch (Exception $e) {
        throw new Exception('Error al guardar competencias: ' . $e->getMessage());
    }
}

function validarArea() {
    global $conexion;
    
    try {
        if (!isset($_POST['id'])) {
            throw new Exception('ID de área no especificado');
        }
        
        $id = intval($_POST['id']);
        
        $errores = [];
        $advertencias = [];
        
        // Obtener área
        $stmt = $conexion->prepare("SELECT * FROM areas_curriculares WHERE id = ?");
        $stmt->execute([$id]);
        $area = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$area) {
            throw new Exception('Área no encontrada');
        }
        
        // Validar competencias
        if (empty($area['competencias']) || $area['competencias'] === 'null') {
            $advertencias[] = "El área '{$area['nombre']}' no tiene competencias definidas";
        } else {
            $competencias = json_decode($area['competencias'], true);
            if (!$competencias) {
                $errores[] = "Las competencias del área tienen formato inválido";
            } else {
                // Validar estructura de competencias
                $errores_comp = validarEstructuraCompetencias($competencias);
                $errores = array_merge($errores, $errores_comp);
                
                // Validar que hay competencias para todos los niveles con asignaciones
                $stmt = $conexion->prepare("
                    SELECT DISTINCT ne.nombre as nivel_nombre
                    FROM asignaciones_docentes ad
                    INNER JOIN secciones s ON ad.seccion_id = s.id
                    INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
                    WHERE ad.area_id = ? AND ad.activo = 1 AND s.activo = 1 AND ne.activo = 1
                ");
                $stmt->execute([$id]);
                $niveles_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($niveles_asignados as $nivel) {
                    $nivel_lower = strtolower($nivel);
                    if (!isset($competencias[$nivel_lower]) || empty($competencias[$nivel_lower])) {
                        $advertencias[] = "No hay competencias definidas para el nivel '{$nivel}' pero tiene asignaciones";
                    }
                }
            }
        }
        
        // Validar asignaciones docentes
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as total,
                   COUNT(CASE WHEN ad.es_tutor = 1 THEN 1 END) as tutores
            FROM asignaciones_docentes ad
            WHERE ad.area_id = ? AND ad.activo = 1
        ");
        $stmt->execute([$id]);
        $asignaciones_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($asignaciones_info['total'] == 0) {
            $advertencias[] = "El área no tiene docentes asignados";
        }
        
        // Validar código duplicado
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM areas_curriculares WHERE codigo = ? AND id != ? AND activo = 1");
        $stmt->execute([$area['codigo'], $id]);
        if ($stmt->fetch(PDO::FETCH_COLUMN) > 0) {
            $errores[] = "El código '{$area['codigo']}' está duplicado en otra área";
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

// Funciones auxiliares

function generarCompetenciasPredefinidas($codigo, $niveles_aplicar) {
    global $conexion;
    
    // Competencias predefinidas según DCN Perú
    $competencias_predefinidas = [
        'MAT' => [
            'Resuelve problemas de cantidad',
            'Resuelve problemas de regularidad, equivalencia y cambio',
            'Resuelve problemas de forma, movimiento y localización',
            'Resuelve problemas de gestión de datos e incertidumbre'
        ],
        'COM' => [
            'Se comunica oralmente en lengua materna',
            'Lee diversos tipos de textos en lengua materna',
            'Escribe diversos tipos de textos en lengua materna'
        ],
        'CYT' => [
            'Indaga mediante métodos científicos para construir conocimientos',
            'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía',
            'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno'
        ],
        'PS' => [
            'Construye su identidad',
            'Convive y participa democráticamente en la búsqueda del bien común',
            'Construye interpretaciones históricas',
            'Gestiona responsablemente el espacio y el ambiente',
            'Gestiona responsablemente los recursos económicos'
        ],
        'AYC' => [
            'Aprecia de manera crítica manifestaciones artístico-culturales',
            'Crea proyectos desde los lenguajes artísticos'
        ],
        'EF' => [
            'Se desenvuelve de manera autónoma a través de su motricidad',
            'Asume una vida saludable',
            'Interactúa a través de sus habilidades sociomotrices'
        ],
        'ER' => [
            'Construye su identidad como persona humana, amada por Dios',
            'Asume la experiencia del encuentro personal y comunitario con Dios'
        ],
        'ING' => [
            'Se comunica oralmente en inglés como lengua extranjera',
            'Lee diversos tipos de textos escritos en inglés como lengua extranjera',
            'Escribe diversos tipos de textos en inglés como lengua extranjera'
        ]
    ];
    
    if (!isset($competencias_predefinidas[$codigo])) {
        return null;
    }
    
    $competencias = [];
    
    // Obtener grados de los niveles seleccionados
    $placeholders = str_repeat('?,', count($niveles_aplicar) - 1) . '?';
    $stmt = $conexion->prepare("SELECT id, nombre, grados FROM niveles_educativos WHERE id IN ($placeholders) AND activo = 1");
    $stmt->execute($niveles_aplicar);
    $niveles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($niveles as $nivel) {
        $nivel_nombre = strtolower($nivel['nombre']);
        $grados = json_decode($nivel['grados'], true) ?: [];
        
        $competencias[$nivel_nombre] = [];
        
        foreach ($grados as $grado) {
            $grado_nombre = $grado['nombre'];
            $competencias[$nivel_nombre][$grado_nombre] = [];
            
            foreach ($competencias_predefinidas[$codigo] as $competencia) {
                $competencias[$nivel_nombre][$grado_nombre][] = [
                    'texto' => $competencia,
                    'capacidades' => '',
                    'estandares' => ''
                ];
            }
        }
    }
    
    return $competencias;
}

function validarEstructuraCompetencias($competencias) {
    $errores = [];
    
    if (!is_array($competencias)) {
        $errores[] = 'Las competencias deben ser un objeto válido';
        return $errores;
    }
    
    foreach ($competencias as $nivel => $grados) {
        if (!is_array($grados)) {
            $errores[] = "El nivel '{$nivel}' debe contener grados válidos";
            continue;
        }
        
        foreach ($grados as $grado => $competencias_grado) {
            if (!is_array($competencias_grado)) {
                $errores[] = "El grado '{$grado}' del nivel '{$nivel}' debe contener competencias válidas";
                continue;
            }
            
            foreach ($competencias_grado as $index => $competencia) {
                if (!is_array($competencia) || empty($competencia['texto'])) {
                    $errores[] = "Competencia inválida en {$nivel} - {$grado} (posición " . ($index + 1) . ")";
                }
            }
        }
    }
    
    return $errores;
}

function contarCompetencias($competencias) {
    $total = 0;
    
    if (!is_array($competencias)) {
        return $total;
    }
    
    foreach ($competencias as $nivel => $grados) {
        if (is_array($grados)) {
            foreach ($grados as $grado => $competencias_grado) {
                if (is_array($competencias_grado)) {
                    $total += count($competencias_grado);
                }
            }
        }
    }
    
    return $total;
}
?>