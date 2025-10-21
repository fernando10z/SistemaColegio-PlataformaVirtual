<?php
session_start();
require_once '../../conexion/bd.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['id'];
$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            crearFichaMedica();
            break;
        case 'actualizar':
            actualizarFichaMedica();
            break;
        case 'obtener':
            obtenerFichaMedica();
            break;
        case 'detalles_completos':
            obtenerDetallesCompletos();
            break;
        case 'eliminar':
            eliminarFichaMedica();
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function crearFichaMedica() {
    global $conexion, $usuario_id;
    
    // Validar datos obligatorios
    $estudiante_id = filter_input(INPUT_POST, 'estudiante_id', FILTER_VALIDATE_INT);
    $tipo_sangre = trim($_POST['tipo_sangre'] ?? '');
    $peso_kg = filter_input(INPUT_POST, 'peso_kg', FILTER_VALIDATE_FLOAT);
    $talla_cm = filter_input(INPUT_POST, 'talla_cm', FILTER_VALIDATE_FLOAT);
    
    if (!$estudiante_id) {
        throw new Exception('Debe seleccionar un estudiante');
    }
    
    if (empty($tipo_sangre) || !in_array($tipo_sangre, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
        throw new Exception('Tipo de sangre inválido');
    }
    
    if (!$peso_kg || $peso_kg < 5 || $peso_kg > 150) {
        throw new Exception('El peso debe estar entre 5 y 150 kg');
    }
    
    if (!$talla_cm || $talla_cm < 50 || $talla_cm > 250) {
        throw new Exception('La talla debe estar entre 50 y 250 cm');
    }
    
    // Verificar que el estudiante no tenga ya una ficha médica
    $sql_check = "SELECT id FROM fichas_medicas WHERE estudiante_id = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->execute([$estudiante_id]);
    if ($stmt_check->fetch()) {
        throw new Exception('Este estudiante ya tiene una ficha médica registrada');
    }
    
    // Calcular IMC
    $imc = $peso_kg / pow(($talla_cm / 100), 2);
    
    // Datos médicos
    $datos_medicos = json_encode([
        'tipo_sangre' => $tipo_sangre,
        'peso_kg' => $peso_kg,
        'talla_cm' => $talla_cm,
        'imc' => round($imc, 1)
    ], JSON_UNESCAPED_UNICODE);
    
    // Historial médico
    $historial_medico = json_encode([
        'alergias_conocidas' => substr(trim($_POST['alergias_conocidas'] ?? 'Ninguna'), 0, 500),
        'enfermedades_cronicas' => substr(trim($_POST['enfermedades_cronicas'] ?? 'Ninguna'), 0, 500),
        'medicamentos_actuales' => substr(trim($_POST['medicamentos_actuales'] ?? 'Ninguno'), 0, 500),
        'cirugias_previas' => substr(trim($_POST['cirugias_previas'] ?? 'Ninguna'), 0, 500),
        'vacunas_completas' => isset($_POST['vacunas_completas']) ? 1 : 0
    ], JSON_UNESCAPED_UNICODE);
    
    // Contactos de emergencia
    $contactos = [];
    if (isset($_POST['contacto_nombre']) && is_array($_POST['contacto_nombre'])) {
        $contacto_principal = $_POST['contacto_principal'] ?? 1;
        
        foreach ($_POST['contacto_nombre'] as $index => $nombre) {
            $nombre = substr(trim($nombre), 0, 100);
            $parentesco = substr(trim($_POST['contacto_parentesco'][$index] ?? ''), 0, 50);
            $telefono = substr(trim($_POST['contacto_telefono'][$index] ?? ''), 0, 20);
            
            if (empty($nombre) || empty($parentesco) || empty($telefono)) {
                throw new Exception('Complete todos los datos de los contactos de emergencia');
            }
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
                throw new Exception('Formato de teléfono inválido');
            }
            
            if (strlen($telefono) < 7) {
                throw new Exception('El teléfono debe tener al menos 7 dígitos');
            }
            
            $contactos[] = [
                'nombre' => $nombre,
                'parentesco' => $parentesco,
                'telefono' => $telefono,
                'es_principal' => ($index + 1) == $contacto_principal
            ];
        }
    }
    
    if (empty($contactos)) {
        throw new Exception('Debe agregar al menos un contacto de emergencia');
    }
    
    $contactos_emergencia = json_encode($contactos, JSON_UNESCAPED_UNICODE);
    
    // Médico tratante
    $medico_nombre = substr(trim($_POST['medico_nombre'] ?? ''), 0, 100);
    $medico_especialidad = substr(trim($_POST['medico_especialidad'] ?? ''), 0, 100);
    $medico_telefono = substr(trim($_POST['medico_telefono'] ?? ''), 0, 20);
    $medico_direccion = substr(trim($_POST['medico_direccion'] ?? ''), 0, 200);
    
    // Validar teléfono del médico si se proporciona
    if (!empty($medico_telefono) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $medico_telefono)) {
        throw new Exception('Formato de teléfono del médico inválido');
    }
    
    $medico_tratante = json_encode([
        'nombre' => $medico_nombre,
        'especialidad' => $medico_especialidad,
        'telefono' => $medico_telefono,
        'direccion_consultorio' => $medico_direccion
    ], JSON_UNESCAPED_UNICODE);
    
    // Observaciones
    $observaciones = substr(trim($_POST['observaciones_adicionales'] ?? ''), 0, 1000);
    
    // Estado
    $vigente = isset($_POST['vigente']) ? 1 : 0;
    
    // Insertar ficha médica
    $sql = "INSERT INTO fichas_medicas 
            (estudiante_id, datos_medicos, historial_medico, contactos_emergencia, 
             medico_tratante, observaciones_adicionales, vigente, usuario_actualiza, 
             fecha_actualizacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $estudiante_id,
        $datos_medicos,
        $historial_medico,
        $contactos_emergencia,
        $medico_tratante,
        $observaciones,
        $vigente,
        $usuario_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ficha médica creada exitosamente'
    ]);
}

function actualizarFichaMedica() {
    global $conexion, $usuario_id;
    
    $ficha_id = filter_input(INPUT_POST, 'ficha_id', FILTER_VALIDATE_INT);
    
    if (!$ficha_id) {
        throw new Exception('ID de ficha inválido');
    }
    
    // Validar datos obligatorios
    $tipo_sangre = trim($_POST['tipo_sangre'] ?? '');
    $peso_kg = filter_input(INPUT_POST, 'peso_kg', FILTER_VALIDATE_FLOAT);
    $talla_cm = filter_input(INPUT_POST, 'talla_cm', FILTER_VALIDATE_FLOAT);
    
    if (empty($tipo_sangre) || !in_array($tipo_sangre, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
        throw new Exception('Tipo de sangre inválido');
    }
    
    if (!$peso_kg || $peso_kg < 5 || $peso_kg > 150) {
        throw new Exception('El peso debe estar entre 5 y 150 kg');
    }
    
    if (!$talla_cm || $talla_cm < 50 || $talla_cm > 250) {
        throw new Exception('La talla debe estar entre 50 y 250 cm');
    }
    
    // Calcular IMC
    $imc = $peso_kg / pow(($talla_cm / 100), 2);
    
    // Datos médicos
    $datos_medicos = json_encode([
        'tipo_sangre' => $tipo_sangre,
        'peso_kg' => $peso_kg,
        'talla_cm' => $talla_cm,
        'imc' => round($imc, 1)
    ], JSON_UNESCAPED_UNICODE);
    
    // Historial médico
    $historial_medico = json_encode([
        'alergias_conocidas' => substr(trim($_POST['alergias_conocidas'] ?? 'Ninguna'), 0, 500),
        'enfermedades_cronicas' => substr(trim($_POST['enfermedades_cronicas'] ?? 'Ninguna'), 0, 500),
        'medicamentos_actuales' => substr(trim($_POST['medicamentos_actuales'] ?? 'Ninguno'), 0, 500),
        'cirugias_previas' => substr(trim($_POST['cirugias_previas'] ?? 'Ninguna'), 0, 500),
        'vacunas_completas' => isset($_POST['vacunas_completas']) ? 1 : 0
    ], JSON_UNESCAPED_UNICODE);
    
    // Contactos de emergencia
    $contactos = [];
    if (isset($_POST['contacto_nombre']) && is_array($_POST['contacto_nombre'])) {
        $contacto_principal = $_POST['contacto_principal'] ?? 1;
        
        foreach ($_POST['contacto_nombre'] as $index => $nombre) {
            $nombre = substr(trim($nombre), 0, 100);
            $parentesco = substr(trim($_POST['contacto_parentesco'][$index] ?? ''), 0, 50);
            $telefono = substr(trim($_POST['contacto_telefono'][$index] ?? ''), 0, 20);
            
            if (empty($nombre) || empty($parentesco) || empty($telefono)) {
                throw new Exception('Complete todos los datos de los contactos de emergencia');
            }
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
                throw new Exception('Formato de teléfono inválido');
            }
            
            if (strlen($telefono) < 7) {
                throw new Exception('El teléfono debe tener al menos 7 dígitos');
            }
            
            $contactos[] = [
                'nombre' => $nombre,
                'parentesco' => $parentesco,
                'telefono' => $telefono,
                'es_principal' => ($index + 1) == $contacto_principal
            ];
        }
    }
    
    if (empty($contactos)) {
        throw new Exception('Debe agregar al menos un contacto de emergencia');
    }
    
    $contactos_emergencia = json_encode($contactos, JSON_UNESCAPED_UNICODE);
    
    // Médico tratante
    $medico_nombre = substr(trim($_POST['medico_nombre'] ?? ''), 0, 100);
    $medico_especialidad = substr(trim($_POST['medico_especialidad'] ?? ''), 0, 100);
    $medico_telefono = substr(trim($_POST['medico_telefono'] ?? ''), 0, 20);
    $medico_direccion = substr(trim($_POST['medico_direccion'] ?? ''), 0, 200);
    
    // Validar teléfono del médico si se proporciona
    if (!empty($medico_telefono) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $medico_telefono)) {
        throw new Exception('Formato de teléfono del médico inválido');
    }
    
    $medico_tratante = json_encode([
        'nombre' => $medico_nombre,
        'especialidad' => $medico_especialidad,
        'telefono' => $medico_telefono,
        'direccion_consultorio' => $medico_direccion
    ], JSON_UNESCAPED_UNICODE);
    
    // Observaciones
    $observaciones = substr(trim($_POST['observaciones_adicionales'] ?? ''), 0, 1000);
    
    // Estado
    $vigente = isset($_POST['vigente']) ? 1 : 0;
    
    // Actualizar ficha médica
    $sql = "UPDATE fichas_medicas 
            SET datos_medicos = ?, 
                historial_medico = ?, 
                contactos_emergencia = ?, 
                medico_tratante = ?, 
                observaciones_adicionales = ?, 
                vigente = ?, 
                usuario_actualiza = ?, 
                fecha_actualizacion = NOW() 
            WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $datos_medicos,
        $historial_medico,
        $contactos_emergencia,
        $medico_tratante,
        $observaciones,
        $vigente,
        $usuario_id,
        $ficha_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ficha médica actualizada exitosamente'
    ]);
}

function obtenerFichaMedica() {
    global $conexion;
    
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        throw new Exception('ID inválido');
    }
    
    $sql = "SELECT 
                fm.*,
                e.codigo_estudiante,
                e.nombres as estudiante_nombres,
                e.apellidos as estudiante_apellidos,
                e.foto_url as estudiante_foto,
                e.fecha_nacimiento,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre
            FROM fichas_medicas fm
            INNER JOIN estudiantes e ON fm.estudiante_id = e.id
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            WHERE fm.id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ficha) {
        throw new Exception('Ficha médica no encontrada');
    }
    
    echo json_encode([
        'success' => true,
        'ficha' => $ficha
    ]);
}

function obtenerDetallesCompletos() {
    global $conexion;
    
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        throw new Exception('ID inválido');
    }
    
    $sql = "SELECT 
                fm.*,
                e.codigo_estudiante,
                e.nombres as estudiante_nombres,
                e.apellidos as estudiante_apellidos,
                e.foto_url as estudiante_foto,
                e.fecha_nacimiento,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                u.nombres as usuario_nombres,
                u.apellidos as usuario_apellidos
            FROM fichas_medicas fm
            INNER JOIN estudiantes e ON fm.estudiante_id = e.id
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            LEFT JOIN usuarios u ON fm.usuario_actualiza = u.id
            WHERE fm.id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ficha) {
        throw new Exception('Ficha médica no encontrada');
    }
    
    echo json_encode([
        'success' => true,
        'ficha' => $ficha
    ]);
}

function eliminarFichaMedica() {
    global $conexion;
    
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        throw new Exception('ID inválido');
    }
    
    $sql = "DELETE FROM fichas_medicas WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo eliminar la ficha médica');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ficha médica eliminada exitosamente'
    ]);
}
?>