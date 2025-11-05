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
            $response = crearMaterial();
            break;
            
        case 'actualizar':
            $response = actualizarMaterial();
            break;
            
        case 'obtener':
            $response = obtenerMaterial();
            break;
            
        case 'toggle_estado':
            $response = toggleEstadoMaterial();
            break;
            
        case 'obtener_ejemplares':
            $response = obtenerEjemplares();
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

function crearMaterial() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['tipo', 'titulo', 'isbn', 'editorial', 'anio_publicacion', 'idioma', 'paginas', 'categoria', 'codigo_dewey', 'ubicacion', 'ejemplares', 'estado_general', 'fecha_adquisicion'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    // Validar autores
    if (empty($_POST['autores'])) {
        throw new Exception("Debe proporcionar al menos un autor");
    }

    $conexion->beginTransaction();

    try {
        // Generar código de barras si no existe
        $codigo_barras = $_POST['codigo_barras'];
        if (empty($codigo_barras)) {
            $isbn_limpio = preg_replace('/[^0-9]/', '', $_POST['isbn']);
            $codigo_barras = $isbn_limpio;
        }

        // Verificar que no exista el ISBN
        $stmt = $conexion->prepare("SELECT id FROM material_bibliografico WHERE datos_basicos->>'$.isbn' = ?");
        $stmt->execute([$_POST['isbn']]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un material con este ISBN');
        }

        // Verificar que no exista el código de barras
        $stmt = $conexion->prepare("SELECT id FROM material_bibliografico WHERE codigo_barras = ?");
        $stmt->execute([$codigo_barras]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un material con este código de barras');
        }

        // Preparar datos básicos
        $datos_basicos = [
            'tipo' => $_POST['tipo'],
            'titulo' => $_POST['titulo'],
            'subtitulo' => $_POST['subtitulo'] ?? '',
            'isbn' => $_POST['isbn']
        ];

        // Preparar datos de publicación
        $datos_publicacion = [
            'editorial' => $_POST['editorial'],
            'anio_publicacion' => (int)$_POST['anio_publicacion'],
            'edicion' => $_POST['edicion'] ?? '',
            'idioma' => $_POST['idioma'],
            'paginas' => (int)$_POST['paginas']
        ];

        // Preparar clasificación
        $clasificacion = [
            'categoria' => $_POST['categoria'],
            'codigo_dewey' => $_POST['codigo_dewey'],
            'palabras_clave' => $_POST['palabras_clave'] ?? ''
        ];

        // Preparar datos físicos
        $datos_fisicos = [
            'ubicacion' => $_POST['ubicacion'],
            'ejemplares' => (int)$_POST['ejemplares'],
            'estado_general' => $_POST['estado_general']
        ];

        // Preparar autores
        $autores = json_decode($_POST['autores'], true);
        if (empty($autores)) {
            throw new Exception('Debe proporcionar al menos un autor');
        }

        // Validar que haya al menos un autor principal
        $hayPrincipal = false;
        foreach ($autores as $autor) {
            if ($autor['principal']) {
                $hayPrincipal = true;
                break;
            }
        }
        if (!$hayPrincipal) {
            throw new Exception('Debe marcar al menos un autor como principal');
        }

        // Preparar datos de adquisición
        $datos_adquisicion = [
            'fecha_adquisicion' => $_POST['fecha_adquisicion'],
            'precio' => !empty($_POST['precio']) ? (float)$_POST['precio'] : null,
            'proveedor' => $_POST['proveedor'] ?? ''
        ];

        // Insertar material bibliográfico
        $stmt = $conexion->prepare("
            INSERT INTO material_bibliografico (
                codigo_barras, datos_basicos, datos_publicacion, clasificacion,
                datos_fisicos, autores, datos_adquisicion, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $codigo_barras,
            json_encode($datos_basicos),
            json_encode($datos_publicacion),
            json_encode($clasificacion),
            json_encode($datos_fisicos),
            json_encode($autores),
            json_encode($datos_adquisicion)
        ]);

        $material_id = $conexion->lastInsertId();

        // Crear ejemplares
        $cantidad_ejemplares = (int)$_POST['ejemplares'];
        for ($i = 1; $i <= $cantidad_ejemplares; $i++) {
            $codigo_inventario = $codigo_barras . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            
            $stmt_ejemplar = $conexion->prepare("
                INSERT INTO ejemplares (
                    material_id, numero_ejemplar, codigo_inventario, estado,
                    ubicacion_especifica, observaciones, fecha_creacion
                ) VALUES (?, ?, ?, 'DISPONIBLE', ?, 'En buen estado', NOW())
            ");

            $stmt_ejemplar->execute([
                $material_id,
                $i,
                $codigo_inventario,
                $_POST['ubicacion']
            ]);
        }

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Material bibliográfico creado exitosamente con ' . $cantidad_ejemplares . ' ejemplar(es)'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function actualizarMaterial() {
    global $conexion;
    
    if (!isset($_POST['material_id'])) {
        throw new Exception('ID del material no especificado');
    }

    $material_id = (int)$_POST['material_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que el material existe
        $stmt = $conexion->prepare("SELECT * FROM material_bibliografico WHERE id = ?");
        $stmt->execute([$material_id]);
        $material = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$material) {
            throw new Exception('Material no encontrado');
        }

        // Preparar datos
        $datos_basicos = [
            'tipo' => $_POST['tipo'],
            'titulo' => $_POST['titulo'],
            'subtitulo' => $_POST['subtitulo'] ?? '',
            'isbn' => $_POST['isbn']
        ];

        $datos_publicacion = [
            'editorial' => $_POST['editorial'],
            'anio_publicacion' => (int)$_POST['anio_publicacion'],
            'edicion' => $_POST['edicion'] ?? '',
            'idioma' => $_POST['idioma'],
            'paginas' => (int)$_POST['paginas']
        ];

        $clasificacion = [
            'categoria' => $_POST['categoria'],
            'codigo_dewey' => $_POST['codigo_dewey'],
            'palabras_clave' => $_POST['palabras_clave'] ?? ''
        ];

        $datos_fisicos = json_decode($material['datos_fisicos'], true);
        $datos_fisicos['ubicacion'] = $_POST['ubicacion'];
        $datos_fisicos['estado_general'] = $_POST['estado_general'];

        $autores = json_decode($_POST['autores'], true);
        if (empty($autores)) {
            throw new Exception('Debe proporcionar al menos un autor');
        }

        $datos_adquisicion = [
            'fecha_adquisicion' => $_POST['fecha_adquisicion'],
            'precio' => !empty($_POST['precio']) ? (float)$_POST['precio'] : null,
            'proveedor' => $_POST['proveedor'] ?? ''
        ];

        // Actualizar material
        $stmt = $conexion->prepare("
            UPDATE material_bibliografico SET 
                datos_basicos = ?, datos_publicacion = ?, clasificacion = ?,
                datos_fisicos = ?, autores = ?, datos_adquisicion = ?
            WHERE id = ?
        ");

        $stmt->execute([
            json_encode($datos_basicos),
            json_encode($datos_publicacion),
            json_encode($clasificacion),
            json_encode($datos_fisicos),
            json_encode($autores),
            json_encode($datos_adquisicion),
            $material_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Material bibliográfico actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerMaterial() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del material no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT mb.*, 
               COUNT(DISTINCT e.id) as total_ejemplares,
               SUM(CASE WHEN e.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as ejemplares_disponibles,
               SUM(CASE WHEN e.estado = 'PRESTADO' THEN 1 ELSE 0 END) as ejemplares_prestados
        FROM material_bibliografico mb
        LEFT JOIN ejemplares e ON mb.id = e.material_id
        WHERE mb.id = ?
        GROUP BY mb.id
    ");
    $stmt->execute([$id]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$material) {
        throw new Exception('Material no encontrado');
    }

    // Decodificar JSON
    $material['datos_basicos'] = json_decode($material['datos_basicos'], true);
    $material['datos_publicacion'] = json_decode($material['datos_publicacion'], true);
    $material['clasificacion'] = json_decode($material['clasificacion'], true);
    $material['datos_fisicos'] = json_decode($material['datos_fisicos'], true);
    $material['autores'] = json_decode($material['autores'], true);
    $material['datos_adquisicion'] = json_decode($material['datos_adquisicion'], true);

    return [
        'success' => true,
        'material' => $material
    ];
}

function toggleEstadoMaterial() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $estado = $_POST['estado'] === 'true' ? 1 : 0;

    $stmt = $conexion->prepare("UPDATE material_bibliografico SET activo = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    $accion = $estado ? 'activado' : 'desactivado';

    return [
        'success' => true,
        'message' => "Material $accion exitosamente"
    ];
}

function obtenerEjemplares() {
    global $conexion;
    
    if (!isset($_POST['material_id'])) {
        throw new Exception('ID del material no especificado');
    }

    $material_id = (int)$_POST['material_id'];

    $stmt = $conexion->prepare("
        SELECT * FROM ejemplares 
        WHERE material_id = ? 
        ORDER BY numero_ejemplar ASC
    ");
    $stmt->execute([$material_id]);
    $ejemplares = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'ejemplares' => $ejemplares
    ];
}
?>