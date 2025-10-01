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
            $response = crearRecurso();
            break;
            
        case 'actualizar':
            $response = actualizarRecurso();
            break;
            
        case 'obtener':
            $response = obtenerRecurso();
            break;
            
        case 'toggle_estado':
            $response = toggleEstadoRecurso();
            break;
            
        case 'vincular':
            $response = vincularRecurso();
            break;
            
        case 'desvincular':
            $response = desvincularRecurso();
            break;
            
        case 'obtener_vinculaciones':
            $response = obtenerVinculaciones();
            break;
            
        case 'obtener_unidades':
            $response = obtenerUnidadesPorCurso();
            break;
            
        case 'obtener_lecciones':
            $response = obtenerLeccionesPorUnidad();
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

// ==================== FUNCIÓN CREAR RECURSO ====================
function crearRecurso() {
    global $conexion;
    
    // Validar campos requeridos
    if (empty($_POST['titulo']) || empty($_POST['tipo'])) {
        throw new Exception('Título y tipo son requeridos');
    }

    // Validar longitud del título
    if (strlen($_POST['titulo']) < 5 || strlen($_POST['titulo']) > 255) {
        throw new Exception('El título debe tener entre 5 y 255 caracteres');
    }

    // Validar tipo
    $tipos_validos = ['VIDEO', 'PDF', 'IMAGEN', 'AUDIO', 'ENLACE', 'DOCUMENTO', 'PRESENTACION', 'OTRO'];
    if (!in_array($_POST['tipo'], $tipos_validos)) {
        throw new Exception('Tipo de recurso no válido');
    }

    $conexion->beginTransaction();

    try {
        $tipo = $_POST['tipo'];
        $url = '';
        $metadata = [];

        // Procesar según tipo
        if ($tipo === 'ENLACE') {
            // Validar URL
            if (empty($_POST['url'])) {
                throw new Exception('La URL es requerida para enlaces');
            }
            
            if (!filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
                throw new Exception('URL no válida');
            }
            
            if (strlen($_POST['url']) > 500) {
                throw new Exception('La URL no puede superar los 500 caracteres');
            }
            
            $url = $_POST['url'];
            
        } else {
            // Validar y procesar archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Debe seleccionar un archivo');
            }
            
            $archivo = $_FILES['archivo'];
            
            // Validar archivo según tipo
            $validacion = validarArchivoSegunTipo($archivo, $tipo);
            if (!$validacion['valido']) {
                throw new Exception($validacion['mensaje']);
            }
            
            // Procesar y subir archivo
            $resultado_subida = subirArchivo($archivo, $tipo);
            $url = $resultado_subida['url'];
            $metadata = array_merge($metadata, $resultado_subida['metadata']);
        }

        // Preparar metadata adicional
        if (!empty($_POST['duracion'])) {
            if (!preg_match('/^([0-9]{2}):([0-5][0-9]):([0-5][0-9])$/', $_POST['duracion'])) {
                throw new Exception('Formato de duración inválido');
            }
            $metadata['duracion'] = $_POST['duracion'];
        }

        // Procesar etiquetas
        if (!empty($_POST['etiquetas'])) {
            $etiquetas = array_map('trim', explode(',', $_POST['etiquetas']));
            $etiquetas = array_filter($etiquetas);
            
            if (count($etiquetas) > 10) {
                throw new Exception('Máximo 10 etiquetas permitidas');
            }
            
            foreach ($etiquetas as $etiqueta) {
                if (strlen($etiqueta) < 2 || strlen($etiqueta) > 20) {
                    throw new Exception('Cada etiqueta debe tener entre 2 y 20 caracteres');
                }
            }
            
            $metadata['etiquetas'] = $etiquetas;
        }

        // Campos de configuración
        $publico = isset($_POST['publico']) ? 1 : 0;
        $descargable = isset($_POST['descargable']) ? 1 : 0;
        
        // Validar coherencia
        if ($descargable && $tipo === 'ENLACE') {
            $descargable = 0;
        }

        // Usuario de creación (deberías obtenerlo de la sesión)
        $usuario_creacion = 1; // CAMBIAR por el ID del usuario en sesión

        // Insertar recurso
        $stmt = $conexion->prepare("
            INSERT INTO recursos (
                titulo, descripcion, tipo, url, metadata,
                publico, descargable, usuario_creacion,
                activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $_POST['titulo'],
            $_POST['descripcion'] ?? null,
            $tipo,
            $url,
            json_encode($metadata),
            $publico,
            $descargable,
            $usuario_creacion
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Recurso creado exitosamente',
            'recurso_id' => $conexion->lastInsertId()
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN ACTUALIZAR RECURSO ====================
function actualizarRecurso() {
    global $conexion;
    
    if (!isset($_POST['recurso_id'])) {
        throw new Exception('ID del recurso no especificado');
    }

    $recurso_id = (int)$_POST['recurso_id'];

    // Validar campos requeridos
    if (empty($_POST['titulo'])) {
        throw new Exception('El título es requerido');
    }

    if (strlen($_POST['titulo']) < 5 || strlen($_POST['titulo']) > 255) {
        throw new Exception('El título debe tener entre 5 y 255 caracteres');
    }

    $conexion->beginTransaction();

    try {
        // Obtener recurso actual
        $stmt = $conexion->prepare("SELECT * FROM recursos WHERE id = ?");
        $stmt->execute([$recurso_id]);
        $recurso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recurso) {
            throw new Exception('Recurso no encontrado');
        }

        $tipo = $recurso['tipo'];
        $url = $recurso['url'];
        $metadata = json_decode($recurso['metadata'], true) ?: [];

        // Procesar según tipo
        if ($tipo === 'ENLACE') {
            // Actualizar URL si cambió
            if (!empty($_POST['url'])) {
                if (!filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
                    throw new Exception('URL no válida');
                }
                
                if (strlen($_POST['url']) > 500) {
                    throw new Exception('La URL no puede superar los 500 caracteres');
                }
                
                $url = $_POST['url'];
            }
        } else {
            // Verificar si se reemplaza el archivo
            if (isset($_FILES['nuevo_archivo']) && $_FILES['nuevo_archivo']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['nuevo_archivo'];
                
                // Validar nuevo archivo
                $validacion = validarArchivoSegunTipo($archivo, $tipo);
                if (!$validacion['valido']) {
                    throw new Exception($validacion['mensaje']);
                }
                
                // Eliminar archivo anterior
                if (file_exists('../../' . $recurso['url'])) {
                    unlink('../../' . $recurso['url']);
                }
                
                // Subir nuevo archivo
                $resultado_subida = subirArchivo($archivo, $tipo);
                $url = $resultado_subida['url'];
                $metadata = array_merge($metadata, $resultado_subida['metadata']);
            }
        }

        // Actualizar metadata
        if (!empty($_POST['duracion'])) {
            if (!preg_match('/^([0-9]{2}):([0-5][0-9]):([0-5][0-9])$/', $_POST['duracion'])) {
                throw new Exception('Formato de duración inválido');
            }
            $metadata['duracion'] = $_POST['duracion'];
        } else {
            unset($metadata['duracion']);
        }

        // Procesar etiquetas
        if (!empty($_POST['etiquetas'])) {
            $etiquetas = array_map('trim', explode(',', $_POST['etiquetas']));
            $etiquetas = array_filter($etiquetas);
            
            if (count($etiquetas) > 10) {
                throw new Exception('Máximo 10 etiquetas permitidas');
            }
            
            foreach ($etiquetas as $etiqueta) {
                if (strlen($etiqueta) < 2 || strlen($etiqueta) > 20) {
                    throw new Exception('Cada etiqueta debe tener entre 2 y 20 caracteres');
                }
            }
            
            $metadata['etiquetas'] = $etiquetas;
        } else {
            unset($metadata['etiquetas']);
        }

        // Configuración
        $publico = isset($_POST['publico']) ? 1 : 0;
        $descargable = isset($_POST['descargable']) ? 1 : 0;
        
        if ($descargable && $tipo === 'ENLACE') {
            $descargable = 0;
        }

        // Actualizar recurso
        $stmt = $conexion->prepare("
            UPDATE recursos SET 
                titulo = ?, descripcion = ?, url = ?, metadata = ?,
                publico = ?, descargable = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['titulo'],
            $_POST['descripcion'] ?? null,
            $url,
            json_encode($metadata),
            $publico,
            $descargable,
            $recurso_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Recurso actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN OBTENER RECURSO ====================
function obtenerRecurso() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del recurso no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT r.*, 
               u.username as usuario_nombre,
               COUNT(DISTINCT cr.curso_id) as cursos_vinculados,
               COUNT(DISTINCT lr.leccion_id) as lecciones_vinculadas
        FROM recursos r
        LEFT JOIN usuarios u ON r.usuario_creacion = u.id
        LEFT JOIN curso_recursos cr ON r.id = cr.recurso_id
        LEFT JOIN leccion_recursos lr ON r.id = lr.recurso_id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    
    $stmt->execute([$id]);
    $recurso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recurso) {
        throw new Exception('Recurso no encontrado');
    }

    return [
        'success' => true,
        'recurso' => $recurso
    ];
}

// ==================== FUNCIÓN TOGGLE ESTADO ====================
function toggleEstadoRecurso() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $estado = $_POST['estado'] === 'true' ? 1 : 0;

    $stmt = $conexion->prepare("UPDATE recursos SET activo = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    $accion = $estado ? 'activado' : 'desactivado';

    return [
        'success' => true,
        'message' => "Recurso $accion exitosamente"
    ];
}

// ==================== FUNCIÓN VINCULAR RECURSO ====================
function vincularRecurso() {
    global $conexion;
    
    if (!isset($_POST['recurso_id']) || !isset($_POST['tipo_vinculacion'])) {
        throw new Exception('Datos incompletos');
    }

    $recurso_id = (int)$_POST['recurso_id'];
    $tipo = $_POST['tipo_vinculacion'];

    // Validar tipo de vinculación
    if (!in_array($tipo, ['CURSO', 'LECCION', 'AMBOS'])) {
        throw new Exception('Tipo de vinculación no válido');
    }

    $conexion->beginTransaction();

    try {
        // Vincular a curso
        if ($tipo === 'CURSO' || $tipo === 'AMBOS') {
            if (empty($_POST['curso_id'])) {
                throw new Exception('Debe seleccionar un curso');
            }

            $curso_id = (int)$_POST['curso_id'];
            $orden = isset($_POST['orden_curso']) ? (int)$_POST['orden_curso'] : 1;
            $destacado = isset($_POST['destacado_curso']) ? 1 : 0;

            // Validar orden
            if ($orden < 1 || $orden > 100) {
                throw new Exception('El orden debe estar entre 1 y 100');
            }

            // Verificar si ya existe la vinculación
            $stmt = $conexion->prepare("
                SELECT id FROM curso_recursos 
                WHERE recurso_id = ? AND curso_id = ?
            ");
            $stmt->execute([$recurso_id, $curso_id]);
            
            if ($stmt->fetch()) {
                throw new Exception('El recurso ya está vinculado a este curso');
            }

            // Insertar vinculación
            $stmt = $conexion->prepare("
                INSERT INTO curso_recursos (curso_id, recurso_id, orden, destacado, fecha_vinculacion)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$curso_id, $recurso_id, $orden, $destacado]);
        }

        // Vincular a lección
        if ($tipo === 'LECCION' || $tipo === 'AMBOS') {
            if (empty($_POST['leccion_id'])) {
                throw new Exception('Debe seleccionar una lección');
            }

            $leccion_id = (int)$_POST['leccion_id'];
            $orden = isset($_POST['orden_leccion']) ? (int)$_POST['orden_leccion'] : 1;
            $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;

            // Validar orden
            if ($orden < 1 || $orden > 100) {
                throw new Exception('El orden debe estar entre 1 y 100');
            }

            // Verificar si ya existe la vinculación
            $stmt = $conexion->prepare("
                SELECT id FROM leccion_recursos 
                WHERE recurso_id = ? AND leccion_id = ?
            ");
            $stmt->execute([$recurso_id, $leccion_id]);
            
            if ($stmt->fetch()) {
                throw new Exception('El recurso ya está vinculado a esta lección');
            }

            // Insertar vinculación
            $stmt = $conexion->prepare("
                INSERT INTO leccion_recursos (leccion_id, recurso_id, orden, obligatorio, fecha_vinculacion)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$leccion_id, $recurso_id, $orden, $obligatorio]);
        }

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Recurso vinculado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

// ==================== FUNCIÓN DESVINCULAR RECURSO ====================
function desvincularRecurso() {
    global $conexion;
    
    if (!isset($_POST['vinculacion_id']) || !isset($_POST['tipo'])) {
        throw new Exception('Datos incompletos');
    }

    $vinculacion_id = (int)$_POST['vinculacion_id'];
    $tipo = $_POST['tipo'];

    if ($tipo === 'CURSO') {
        $stmt = $conexion->prepare("DELETE FROM curso_recursos WHERE id = ?");
    } else if ($tipo === 'LECCION') {
        $stmt = $conexion->prepare("DELETE FROM leccion_recursos WHERE id = ?");
    } else {
        throw new Exception('Tipo de vinculación no válido');
    }

    $stmt->execute([$vinculacion_id]);

    return [
        'success' => true,
        'message' => 'Vinculación eliminada exitosamente'
    ];
}

// ==================== FUNCIÓN OBTENER VINCULACIONES ====================
function obtenerVinculaciones() {
    global $conexion;
    
    if (!isset($_POST['recurso_id'])) {
        throw new Exception('ID del recurso no especificado');
    }

    $recurso_id = (int)$_POST['recurso_id'];
    $vinculaciones = [];

    // Obtener vinculaciones a cursos
    $stmt = $conexion->prepare("
        SELECT cr.id, 'CURSO' as tipo, c.nombre, c.codigo_curso,
               s.grado, s.seccion, ac.nombre as area_nombre
        FROM curso_recursos cr
        INNER JOIN cursos c ON cr.curso_id = c.id
        INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        WHERE cr.recurso_id = ?
    ");
    $stmt->execute([$recurso_id]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vinculaciones[] = $row;
    }

    // Obtener vinculaciones a lecciones
    $stmt = $conexion->prepare("
        SELECT lr.id, 'LECCION' as tipo, l.titulo as nombre,
               u.titulo as unidad_nombre
        FROM leccion_recursos lr
        INNER JOIN lecciones l ON lr.leccion_id = l.id
        INNER JOIN unidades u ON l.unidad_id = u.id
        WHERE lr.recurso_id = ?
    ");
    $stmt->execute([$recurso_id]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vinculaciones[] = $row;
    }

    return [
        'success' => true,
        'vinculaciones' => $vinculaciones
    ];
}

// ==================== FUNCIÓN OBTENER UNIDADES ====================
function obtenerUnidadesPorCurso() {
    global $conexion;
    
    if (!isset($_POST['curso_id'])) {
        throw new Exception('ID del curso no especificado');
    }

    $curso_id = (int)$_POST['curso_id'];

    $stmt = $conexion->prepare("
        SELECT id, titulo, orden
        FROM unidades
        WHERE curso_id = ?
        ORDER BY orden ASC
    ");
    $stmt->execute([$curso_id]);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'unidades' => $unidades
    ];
}

// ==================== FUNCIÓN OBTENER LECCIONES ====================
function obtenerLeccionesPorUnidad() {
    global $conexion;
    
    if (!isset($_POST['unidad_id'])) {
        throw new Exception('ID de la unidad no especificado');
    }

    $unidad_id = (int)$_POST['unidad_id'];

    $stmt = $conexion->prepare("
        SELECT id, titulo, orden, tipo
        FROM lecciones
        WHERE unidad_id = ?
        ORDER BY orden ASC
    ");
    $stmt->execute([$unidad_id]);
    $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'lecciones' => $lecciones
    ];
}

// ==================== FUNCIÓN VALIDAR ARCHIVO ====================
function validarArchivoSegunTipo($archivo, $tipo) {
    $nombre = strtolower($archivo['name']);
    $tamano = $archivo['size'];
    
    $validaciones = [
        'VIDEO' => [
            'extensiones' => ['.mp4', '.avi', '.mov', '.wmv'],
            'tamanoMax' => 50 * 1024 * 1024, // 50MB
            'mensaje' => 'Solo se permiten videos MP4, AVI, MOV, WMV (máx. 50MB)'
        ],
        'PDF' => [
            'extensiones' => ['.pdf'],
            'tamanoMax' => 20 * 1024 * 1024, // 20MB
            'mensaje' => 'Solo se permiten archivos PDF (máx. 20MB)'
        ],
        'IMAGEN' => [
            'extensiones' => ['.jpg', '.jpeg', '.png', '.gif', '.svg'],
            'tamanoMax' => 5 * 1024 * 1024, // 5MB
            'mensaje' => 'Solo se permiten imágenes JPG, PNG, GIF, SVG (máx. 5MB)'
        ],
        'AUDIO' => [
            'extensiones' => ['.mp3', '.wav', '.ogg'],
            'tamanoMax' => 20 * 1024 * 1024, // 20MB
            'mensaje' => 'Solo se permiten audios MP3, WAV, OGG (máx. 20MB)'
        ],
        'DOCUMENTO' => [
            'extensiones' => ['.doc', '.docx', '.txt', '.rtf'],
            'tamanoMax' => 10 * 1024 * 1024, // 10MB
            'mensaje' => 'Solo se permiten documentos DOC, DOCX, TXT, RTF (máx. 10MB)'
        ],
        'PRESENTACION' => [
            'extensiones' => ['.ppt', '.pptx'],
            'tamanoMax' => 20 * 1024 * 1024, // 20MB
            'mensaje' => 'Solo se permiten presentaciones PPT, PPTX (máx. 20MB)'
        ],
        'OTRO' => [
            'extensiones' => [],
            'tamanoMax' => 20 * 1024 * 1024, // 20MB
            'mensaje' => 'Archivo no puede superar 20MB'
        ]
    ];
    
    $validacion = $validaciones[$tipo] ?? $validaciones['OTRO'];
    
    // Validar extensión
    if (!empty($validacion['extensiones'])) {
        $extension_valida = false;
        foreach ($validacion['extensiones'] as $ext) {
            if (substr($nombre, -strlen($ext)) === $ext) {
                $extension_valida = true;
                break;
            }
        }
        
        if (!$extension_valida) {
            return ['valido' => false, 'mensaje' => $validacion['mensaje']];
        }
    }
    
    // Validar tamaño
    if ($tamano > $validacion['tamanoMax']) {
        $tamanoMaxMB = $validacion['tamanoMax'] / (1024 * 1024);
        return ['valido' => false, 'mensaje' => "El archivo supera el tamaño máximo de {$tamanoMaxMB}MB"];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

// ==================== FUNCIÓN SUBIR ARCHIVO ====================
function subirArchivo($archivo, $tipo) {
    $directorio_base = '../../uploads/recursos/';
    
    // Crear subdirectorio por tipo
    $subdirectorio = strtolower($tipo) . 's/';
    $directorio_completo = $directorio_base . $subdirectorio;
    
    // Crear directorios si no existen
    if (!is_dir($directorio_base)) {
        mkdir($directorio_base, 0755, true);
    }
    if (!is_dir($directorio_completo)) {
        mkdir($directorio_completo, 0755, true);
    }

    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio_completo . $nombre_archivo;

    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        throw new Exception('Error al subir el archivo');
    }

    // Preparar metadata
    $metadata = [
        'nombre_original' => $archivo['name'],
        'tamano_bytes' => $archivo['size'],
        'extension' => $extension,
        'fecha_subida' => date('Y-m-d H:i:s')
    ];

    // Metadata adicional para imágenes
    if ($tipo === 'IMAGEN') {
        $info_imagen = getimagesize($ruta_completa);
        if ($info_imagen) {
            $metadata['ancho'] = $info_imagen[0];
            $metadata['alto'] = $info_imagen[1];
            $metadata['mime_type'] = $info_imagen['mime'];
        }
    }

    return [
        'url' => 'uploads/recursos/' . $subdirectorio . $nombre_archivo,
        'metadata' => $metadata
    ];
}
?>