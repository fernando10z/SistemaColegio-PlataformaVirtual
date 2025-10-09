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
            $response = crearComunicacion();
            break;
            
        case 'obtener':
            $response = obtenerComunicacion();
            break;
            
        case 'reenviar':
            $response = reenviarComunicacion();
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

function crearComunicacion() {
    global $conexion;
    
    // Validar campos requeridos
    if (empty($_POST['postulacion_id']) || empty($_POST['tipo']) || empty($_POST['destinatario'])) {
        throw new Exception("Faltan campos requeridos");
    }

    $conexion->beginTransaction();

    try {
        // Verificar que la postulación existe
        $stmt = $conexion->prepare("SELECT * FROM postulaciones WHERE id = ?");
        $stmt->execute([$_POST['postulacion_id']]);
        $postulacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$postulacion) {
            throw new Exception('Postulación no encontrada');
        }

        // Preparar configuración
        $configuracion = [
            'tipo' => $_POST['tipo'],
            'destinatario' => $_POST['destinatario'],
            'asunto' => $_POST['asunto'] ?? '',
            'mensaje' => $_POST['mensaje'] ?? '',
            'prioridad' => $_POST['prioridad'] ?? 'NORMAL'
        ];

        // Preparar metadatos
        $metadatos = [
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'enviar_inmediato' => isset($_POST['enviar_inmediato']) ? true : false,
            'ip_creacion' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];

        // Determinar estado inicial
        $estado = isset($_POST['enviar_inmediato']) ? 'ENVIADO' : 'PENDIENTE';
        
        if ($estado == 'ENVIADO') {
            $metadatos['fecha_envio'] = date('Y-m-d H:i:s');
        }

        // Insertar comunicación
        $stmt = $conexion->prepare("
            INSERT INTO comunicaciones_admision (
                postulacion_id, configuracion, estado, metadatos
            ) VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['postulacion_id'],
            json_encode($configuracion),
            $estado,
            json_encode($metadatos)
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Comunicación creada y ' . ($estado == 'ENVIADO' ? 'enviada' : 'programada') . ' exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}

function obtenerComunicacion() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de comunicación no especificado');
    }

    $id = (int)$_POST['id'];

    $stmt = $conexion->prepare("
        SELECT ca.*, 
               p.codigo_postulacion,
               p.grado_solicitado,
               p.datos_postulante,
               p.datos_apoderado,
               p.estado as estado_postulacion
        FROM comunicaciones_admision ca
        INNER JOIN postulaciones p ON ca.postulacion_id = p.id
        WHERE ca.id = ?
    ");
    $stmt->execute([$id]);
    $comunicacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comunicacion) {
        throw new Exception('Comunicación no encontrada');
    }

    // Decodificar JSON
    $comunicacion['configuracion'] = json_decode($comunicacion['configuracion'], true);
    $comunicacion['metadatos'] = json_decode($comunicacion['metadatos'], true);
    $comunicacion['datos_postulante'] = json_decode($comunicacion['datos_postulante'], true);
    $comunicacion['datos_apoderado'] = json_decode($comunicacion['datos_apoderado'], true);

    return [
        'success' => true,
        'comunicacion' => $comunicacion
    ];
}

function reenviarComunicacion() {
    global $conexion;
    
    if (!isset($_POST['id'])) {
        throw new Exception('ID de comunicación no especificado');
    }

    $id = (int)$_POST['id'];

    $conexion->beginTransaction();

    try {
        // Obtener comunicación original
        $stmt = $conexion->prepare("SELECT * FROM comunicaciones_admision WHERE id = ?");
        $stmt->execute([$id]);
        $comunicacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comunicacion) {
            throw new Exception('Comunicación no encontrada');
        }

        // Actualizar metadatos
        $metadatos = json_decode($comunicacion['metadatos'], true);
        $metadatos['fecha_reenvio'] = date('Y-m-d H:i:s');
        $metadatos['motivo_reenvio'] = $_POST['motivo'] ?? '';
        
        // Actualizar destinatario si se proporciona uno nuevo
        if (!empty($_POST['nuevo_destinatario'])) {
            $configuracion = json_decode($comunicacion['configuracion'], true);
            $configuracion['destinatario'] = $_POST['nuevo_destinatario'];
            $configuracion_json = json_encode($configuracion);
        } else {
            $configuracion_json = $comunicacion['configuracion'];
        }

        // Actualizar comunicación
        $stmt = $conexion->prepare("
            UPDATE comunicaciones_admision 
            SET configuracion = ?, estado = 'ENVIADO', metadatos = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $configuracion_json,
            json_encode($metadatos),
            $id
        ]);

        $conexion->commit();

        return [
            'success' => true,
            'message' => 'Comunicación reenviada exitosamente'
        ];

    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
}
?>