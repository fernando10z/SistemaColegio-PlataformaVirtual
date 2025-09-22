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
            $response = crearDocente();
            break;
            
        case 'actualizar':
            $response = actualizarDocente();
            break;
            
        case 'obtener':
            $response = obtenerDocente();
            break;
            
        case 'perfil_completo':
            $response = obtenerPerfilCompleto();
            break;
            
        case 'toggle_estado':
            $response = toggleEstadoDocente();
            break;
            
        case 'asignar_usuario':
            $response = asignarUsuario();
            break;
            
        case 'asignaciones':
            $response = obtenerAsignaciones();
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

function crearDocente() {
    global $conexion;
    
    // Validar campos requeridos
    $campos_requeridos = ['nombres', 'apellidos', 'documento_tipo', 'documento_numero', 'grado_academico', 'categoria', 'tipo_contrato'];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es requerido");
        }
    }

    $conexion->beginTransaction();

    try {
        // Generar código de docente si no se proporciona
        $codigo_docente = $_POST['codigo_docente'];
        if (empty($codigo_docente)) {
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM docentes WHERE codigo_docente LIKE 'DOC%'");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            $codigo_docente = 'DOC' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        // Verificar que no exista el código
        $stmt = $conexion->prepare("SELECT id FROM docentes WHERE codigo_docente = ?");
        $stmt->execute([$codigo_docente]);
        if ($stmt->fetch()) {
            throw new Exception('El código de docente ya existe');
        }

        // Verificar que no exista el documento
        $stmt = $conexion->prepare("SELECT id FROM docentes WHERE documento_numero = ?");
        $stmt->execute([$_POST['documento_numero']]);
        if ($stmt->fetch()) {
            throw new Exception('Ya existe un docente con este número de documento');
        }

        // Preparar datos
        $datos_personales = [
            'email' => $_POST['email'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? ''
        ];

        $datos_profesionales = [
            'grado_academico' => $_POST['grado_academico'],
            'universidad' => $_POST['universidad'] ?? '',
            'especialidad' => $_POST['especialidad'] ?? '',
            'colegiatura' => $_POST['colegiatura'] ?? ''
        ];

        $datos_laborales = [
            'categoria' => $_POST['categoria'],
            'tipo_contrato' => $_POST['tipo_contrato'],
            'nivel_magisterial' => $_POST['nivel_magisterial'] ?? '',
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null
        ];

        // Procesar especialidades
        $areas_especialidad = isset($_POST['areas_especialidad']) ? $_POST['areas_especialidad'] : [];

        // Procesar foto si se sube
        $foto_url = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_url = procesarFotoDocente($_FILES['foto'], $codigo_docente);
        }

        // Insertar docente
        $stmt = $conexion->prepare("
            INSERT INTO docentes (
                codigo_docente, nombres, apellidos, documento_tipo, documento_numero,
                datos_personales, datos_profesionales, datos_laborales, areas_especialidad,
                foto_url, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");

        $stmt->execute([
            $codigo_docente,
            $_POST['nombres'],
            $_POST['apellidos'],
            $_POST['documento_tipo'],
            $_POST['documento_numero'],
            json_encode($datos_personales),
            json_encode($datos_profesionales),
            json_encode($datos_laborales),
            json_encode($areas_especialidad),
            $foto_url
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Docente creado exitosamente con código: ' . $codigo_docente
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function actualizarDocente() {
    global $conexion;
    
    if (!isset($_POST['docente_id'])) {
        throw new Exception('ID del docente no especificado');
    }

    $docente_id = (int)$_POST['docente_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que el docente existe
        $stmt = $conexion->prepare("SELECT * FROM docentes WHERE id = ?");
        $stmt->execute([$docente_id]);
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$docente) {
            throw new Exception('Docente no encontrado');
        }

        // Preparar datos
        $datos_personales = [
            'email' => $_POST['email'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? ''
        ];

        $datos_profesionales = [
            'grado_academico' => $_POST['grado_academico'] ?? '',
            'universidad' => $_POST['universidad'] ?? '',
            'especialidad' => $_POST['especialidad'] ?? '',
            'colegiatura' => $_POST['colegiatura'] ?? ''
        ];

        $datos_laborales = [
            'categoria' => $_POST['categoria'] ?? '',
            'tipo_contrato' => $_POST['tipo_contrato'] ?? '',
            'nivel_magisterial' => $_POST['nivel_magisterial'] ?? '',
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null
        ];

        // Procesar especialidades
        $areas_especialidad = isset($_POST['areas_especialidad']) ? $_POST['areas_especialidad'] : [];

        // Procesar foto si se sube
        $foto_url = $docente['foto_url'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_url = procesarFotoDocente($_FILES['foto'], $docente['codigo_docente']);
        }

        // Actualizar docente
        $stmt = $conexion->prepare("
            UPDATE docentes SET 
                nombres = ?, apellidos = ?, documento_tipo = ?, documento_numero = ?,
                datos_personales = ?, datos_profesionales = ?, datos_laborales = ?, 
                areas_especialidad = ?, foto_url = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['nombres'],
            $_POST['apellidos'],
            $_POST['documento_tipo'],
            $_POST['documento_numero'],
            json_encode($datos_personales),
            json_encode($datos_profesionales),
            json_encode($datos_laborales),
            json_encode($areas_especialidad),
            $foto_url,
            $docente_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Docente actualizado exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerDocente() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del docente no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT d.*, 
               COUNT(DISTINCT ad.id) as total_asignaciones,
               COUNT(DISTINCT ad.seccion_id) as secciones_asignadas
        FROM docentes d
        LEFT JOIN asignaciones_docentes ad ON d.id = ad.docente_id AND ad.activo = 1
        WHERE d.id = ?
        GROUP BY d.id
    ");
    $stmt->execute([$id]);
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$docente) {
        throw new Exception('Docente no encontrado');
    }

    // Decodificar JSON
    $docente['datos_personales'] = json_decode($docente['datos_personales'], true);
    $docente['datos_profesionales'] = json_decode($docente['datos_profesionales'], true);
    $docente['datos_laborales'] = json_decode($docente['datos_laborales'], true);
    $docente['areas_especialidad'] = json_decode($docente['areas_especialidad'], true);

    return [
        'success' => true,
        'docente' => $docente
    ];
}

function obtenerPerfilCompleto() {
    return obtenerDocente(); // Reutilizar la función
}

function toggleEstadoDocente() {
    global $conexion;
    
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $id = (int)$_POST['id'];
    $estado = $_POST['estado'] === 'true' ? 1 : 0;

    $stmt = $conexion->prepare("UPDATE docentes SET activo = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);

    $accion = $estado ? 'activado' : 'desactivado';

    return [
        'success' => true,
        'message' => "Docente $accion exitosamente"
    ];
}

function asignarUsuario() {
    global $conexion;
    
    if (!isset($_POST['docente_id']) || !isset($_POST['usuario_id'])) {
        throw new Exception('Datos incompletos');
    }

    $docente_id = (int)$_POST['docente_id'];
    $usuario_id = (int)$_POST['usuario_id'];

    $conexion->beginTransaction();

    try {
        // Verificar que el usuario no esté asignado a otro docente
        $stmt = $conexion->prepare("SELECT id FROM docentes WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        if ($stmt->fetch()) {
            throw new Exception('El usuario ya está asignado a otro docente');
        }

        // Asignar usuario al docente
        $stmt = $conexion->prepare("UPDATE docentes SET usuario_id = ? WHERE id = ?");
        $stmt->execute([$usuario_id, $docente_id]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Usuario asignado exitosamente al docente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerAsignaciones() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID del docente no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT ad.*, 
               ac.nombre as area_nombre,
               s.grado, s.seccion
        FROM asignaciones_docentes ad
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        INNER JOIN secciones s ON ad.seccion_id = s.id
        WHERE ad.docente_id = ? AND ad.activo = 1
        ORDER BY s.grado ASC, s.seccion ASC
    ");
    $stmt->execute([$id]);
    $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'asignaciones' => $asignaciones
    ];
}

function procesarFotoDocente($archivo, $codigo_docente) {
    $directorio = '../../uploads/docentes/';
    
    // Crear directorio si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = $codigo_docente . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio . $nombre_archivo;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return 'uploads/docentes/' . $nombre_archivo;
    }

    throw new Exception('Error al subir la foto');
}
?>