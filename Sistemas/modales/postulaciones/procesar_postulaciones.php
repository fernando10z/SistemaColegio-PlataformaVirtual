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
            $response = crearPostulacion();
            break;
            
        case 'evaluar':
            $response = evaluarPostulacion();
            break;
            
        case 'obtener_detalle':
            $response = obtenerDetallePostulacion();
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

function crearPostulacion() {
    global $conexion;
    
    // Validar campos requeridos
    if (empty($_POST['proceso_id']) || empty($_POST['grado_solicitado']) || 
        empty($_POST['postulante_nombres']) || empty($_POST['apoderado_nombres'])) {
        throw new Exception('Faltan campos requeridos');
    }

    $conexion->beginTransaction();

    try {
        // Generar código de postulación
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM postulaciones WHERE proceso_id = ?");
        $stmt->execute([$_POST['proceso_id']]);
        $count = $stmt->fetchColumn();
        $codigo_postulacion = 'POST' . $_POST['proceso_id'] . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        // Preparar datos del postulante
        $datos_postulante = [
            'nombres' => $_POST['postulante_nombres'],
            'apellidos' => $_POST['postulante_apellidos'],
            'documento_tipo' => $_POST['postulante_documento_tipo'],
            'documento_numero' => $_POST['postulante_documento_numero'],
            'fecha_nacimiento' => $_POST['postulante_fecha_nacimiento'] ?? null,
            'lugar_nacimiento' => $_POST['postulante_lugar_nacimiento'] ?? '',
            'colegio_procedencia' => $_POST['postulante_colegio_procedencia'] ?? ''
        ];

        // Preparar datos del apoderado
        $datos_apoderado = [
            'nombres' => $_POST['apoderado_nombres'],
            'apellidos' => $_POST['apoderado_apellidos'],
            'documento_tipo' => $_POST['apoderado_documento_tipo'],
            'documento_numero' => $_POST['apoderado_documento_numero'],
            'parentesco' => $_POST['apoderado_parentesco'] ?? '',
            'email' => $_POST['apoderado_email'] ?? '',
            'telefono' => $_POST['apoderado_telefono'] ?? '',
            'direccion' => $_POST['apoderado_direccion'] ?? ''
        ];

        // Insertar postulación
        $stmt = $conexion->prepare("
            INSERT INTO postulaciones (
                proceso_id, codigo_postulacion, grado_solicitado,
                datos_postulante, datos_apoderado, estado, fecha_postulacion
            ) VALUES (?, ?, ?, ?, ?, 'REGISTRADA', NOW())
        ");

        $stmt->execute([
            $_POST['proceso_id'],
            $codigo_postulacion,
            $_POST['grado_solicitado'],
            json_encode($datos_postulante),
            json_encode($datos_apoderado)
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Postulación registrada exitosamente con código: ' . $codigo_postulacion
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function evaluarPostulacion() {
    global $conexion;
    
    if (!isset($_POST['postulacion_id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $postulacion_id = (int)$_POST['postulacion_id'];
    $estado = $_POST['estado'];

    $conexion->beginTransaction();

    try {
        // Preparar evaluaciones
        $evaluaciones = [
            'nota_entrevista' => $_POST['nota_entrevista'] ?? null,
            'nota_evaluacion' => $_POST['nota_evaluacion'] ?? null,
            'promedio_final' => $_POST['promedio_final'] ?? null,
            'observaciones_evaluacion' => $_POST['observaciones_evaluacion'] ?? '',
            'recomendaciones' => $_POST['recomendaciones'] ?? '',
            'fecha_evaluacion' => date('Y-m-d H:i:s')
        ];

        // Preparar metadatos
        $metadatos = [
            'prioridad' => $_POST['prioridad'] ?? 'NORMAL',
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        // Actualizar postulación
        $stmt = $conexion->prepare("
            UPDATE postulaciones SET 
                estado = ?, evaluaciones = ?, metadatos = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $estado,
            json_encode($evaluaciones),
            json_encode($metadatos),
            $postulacion_id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Evaluación guardada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerDetallePostulacion() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de postulación no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT p.*, 
               pa.nombre as proceso_nombre,
               pa.anio_academico
        FROM postulaciones p
        INNER JOIN procesos_admision pa ON p.proceso_id = pa.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $postulacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$postulacion) {
        throw new Exception('Postulación no encontrada');
    }

    // Decodificar JSON
    $postulacion['datos_postulante'] = json_decode($postulacion['datos_postulante'], true);
    $postulacion['datos_apoderado'] = json_decode($postulacion['datos_apoderado'], true);
    $postulacion['documentos'] = json_decode($postulacion['documentos'], true);
    $postulacion['evaluaciones'] = json_decode($postulacion['evaluaciones'], true);
    $postulacion['metadatos'] = json_decode($postulacion['metadatos'], true);

    return [
        'success' => true,
        'postulacion' => $postulacion
    ];
}
?>