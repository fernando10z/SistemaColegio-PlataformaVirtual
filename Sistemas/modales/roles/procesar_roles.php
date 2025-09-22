<?php
// Procesador de Roles y Permisos
// Archivo: modales/roles/procesar_roles.php

session_start();
require_once '../../conexion/bd.php';

// Configuración de respuesta JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Función para registrar en auditoría
function registrarAuditoria($conexion, $usuario_id, $accion, $tabla, $registro_id, $datos_cambio) {
    try {
        $sql_auditoria = "INSERT INTO auditoria_sistema 
                         (usuario_id, modulo, accion, tabla_afectada, registro_id, datos_cambio) 
                         VALUES (?, 'ROLES', ?, ?, ?, ?)";
        $stmt_auditoria = $conexion->prepare($sql_auditoria);
        $stmt_auditoria->execute([$usuario_id, $accion, $tabla, $registro_id, json_encode($datos_cambio)]);
    } catch (Exception $e) {
        error_log("Error en auditoría: " . $e->getMessage());
    }
}

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar sesión de usuario (ajustar según tu sistema de autenticación)
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        
        case 'crear':
            // Crear nuevo rol
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $nivel_acceso = intval($_POST['nivel_acceso'] ?? 1);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $permisos = $_POST['permisos'] ?? [];
            
            // Validaciones
            if (empty($nombre)) {
                throw new Exception('El nombre del rol es obligatorio');
            }
            
            if ($nivel_acceso < 1 || $nivel_acceso > 10) {
                throw new Exception('El nivel de acceso debe estar entre 1 y 10');
            }
            
            // Verificar que no exista otro rol con el mismo nombre
            $sql_verificar = "SELECT id FROM roles WHERE nombre = ?";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            $stmt_verificar->execute([$nombre]);
            
            if ($stmt_verificar->fetch()) {
                throw new Exception('Ya existe un rol con este nombre');
            }
            
            // Procesar permisos
            $permisos_json = json_encode($permisos);
            
            // Insertar nuevo rol
            $sql = "INSERT INTO roles (nombre, descripcion, nivel_acceso, permisos, activo) 
                   VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $nivel_acceso, $permisos_json, $activo]);
            
            $nuevo_rol_id = $conexion->lastInsertId();
            
            // Registrar auditoría
            registrarAuditoria($conexion, $usuario_id, 'CREAR_ROL', 'roles', $nuevo_rol_id, [
                'nombre' => $nombre,
                'nivel_acceso' => $nivel_acceso,
                'permisos_count' => count($permisos)
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Rol creado exitosamente',
                'rol_id' => $nuevo_rol_id
            ]);
            break;
            
        case 'obtener':
            // Obtener datos de un rol específico
            $rol_id = intval($_POST['id'] ?? 0);
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            $sql = "SELECT r.*, 
                           COUNT(u.id) as total_usuarios,
                           COUNT(CASE WHEN u.activo = 1 THEN 1 END) as usuarios_activos
                    FROM roles r 
                    LEFT JOIN usuarios u ON r.id = u.rol_id 
                    WHERE r.id = ?
                    GROUP BY r.id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rol_id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            
            // Decodificar permisos
            $rol['permisos'] = json_decode($rol['permisos'], true) ?: [];
            
            echo json_encode(['success' => true, 'rol' => $rol]);
            break;
            
        case 'actualizar':
            // Actualizar rol existente
            $rol_id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $nivel_acceso = intval($_POST['nivel_acceso'] ?? 1);
            $activo = isset($_POST['activo']) ? 1 : 0;
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            if (empty($nombre)) {
                throw new Exception('El nombre del rol es obligatorio');
            }
            
            if ($nivel_acceso < 1 || $nivel_acceso > 10) {
                throw new Exception('El nivel de acceso debe estar entre 1 y 10');
            }
            
            // Verificar que no exista otro rol con el mismo nombre (excepto el actual)
            $sql_verificar = "SELECT id FROM roles WHERE nombre = ? AND id != ?";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            $stmt_verificar->execute([$nombre, $rol_id]);
            
            if ($stmt_verificar->fetch()) {
                throw new Exception('Ya existe otro rol con este nombre');
            }
            
            // Obtener datos actuales para auditoría
            $sql_actual = "SELECT * FROM roles WHERE id = ?";
            $stmt_actual = $conexion->prepare($sql_actual);
            $stmt_actual->execute([$rol_id]);
            $datos_anteriores = $stmt_actual->fetch(PDO::FETCH_ASSOC);
            
            if (!$datos_anteriores) {
                throw new Exception('Rol no encontrado');
            }
            
            // Actualizar rol
            $sql = "UPDATE roles 
                   SET nombre = ?, descripcion = ?, nivel_acceso = ?, activo = ? 
                   WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $nivel_acceso, $activo, $rol_id]);
            
            // Registrar auditoría
            registrarAuditoria($conexion, $usuario_id, 'ACTUALIZAR_ROL', 'roles', $rol_id, [
                'cambios' => [
                    'nombre' => ['anterior' => $datos_anteriores['nombre'], 'nuevo' => $nombre],
                    'nivel_acceso' => ['anterior' => $datos_anteriores['nivel_acceso'], 'nuevo' => $nivel_acceso],
                    'activo' => ['anterior' => $datos_anteriores['activo'], 'nuevo' => $activo]
                ]
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Rol actualizado exitosamente']);
            break;
            
        case 'obtener_permisos':
            // Obtener solo los permisos de un rol
            $rol_id = intval($_POST['id'] ?? 0);
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            $sql = "SELECT permisos FROM roles WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rol_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado) {
                throw new Exception('Rol no encontrado');
            }
            
            $permisos = json_decode($resultado['permisos'], true) ?: [];
            
            echo json_encode(['success' => true, 'permisos' => $permisos]);
            break;
            
        case 'obtener_permisos_completos':
            // Obtener rol completo con permisos para gestión
            $rol_id = intval($_POST['id'] ?? 0);
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            $sql = "SELECT r.*, 
                           COUNT(u.id) as total_usuarios,
                           COUNT(CASE WHEN u.activo = 1 THEN 1 END) as usuarios_activos
                    FROM roles r 
                    LEFT JOIN usuarios u ON r.id = u.rol_id 
                    WHERE r.id = ?
                    GROUP BY r.id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rol_id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                throw new Exception('Rol no encontrado');
            }
            
            // Decodificar permisos
            $rol['permisos'] = json_decode($rol['permisos'], true) ?: [];
            
            echo json_encode(['success' => true, 'rol' => $rol]);
            break;
            
        case 'actualizar_permisos':
            // Actualizar permisos de un rol
            $rol_id = intval($_POST['id'] ?? 0);
            $permisos = $_POST['permisos'] ?? [];
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            // Obtener datos actuales para auditoría
            $sql_actual = "SELECT nombre, permisos FROM roles WHERE id = ?";
            $stmt_actual = $conexion->prepare($sql_actual);
            $stmt_actual->execute([$rol_id]);
            $datos_anteriores = $stmt_actual->fetch(PDO::FETCH_ASSOC);
            
            if (!$datos_anteriores) {
                throw new Exception('Rol no encontrado');
            }
            
            $permisos_anteriores = json_decode($datos_anteriores['permisos'], true) ?: [];
            
            // Procesar permisos
            $permisos_json = json_encode($permisos);
            
            // Actualizar permisos
            $sql = "UPDATE roles SET permisos = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$permisos_json, $rol_id]);
            
            // Registrar auditoría
            registrarAuditoria($conexion, $usuario_id, 'ACTUALIZAR_PERMISOS', 'roles', $rol_id, [
                'rol_nombre' => $datos_anteriores['nombre'],
                'permisos_anteriores' => $permisos_anteriores,
                'permisos_nuevos' => $permisos,
                'total_permisos' => count($permisos)
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Permisos actualizados exitosamente',
                'total_permisos' => count($permisos)
            ]);
            break;
            
        case 'toggle_estado':
            // Cambiar estado activo/inactivo de un rol
            $rol_id = intval($_POST['id'] ?? 0);
            $nuevo_estado = $_POST['estado'] === 'true' ? 1 : 0;
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            // Verificar que el rol existe
            $sql_verificar = "SELECT nombre, activo FROM roles WHERE id = ?";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            $stmt_verificar->execute([$rol_id]);
            $rol_actual = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol_actual) {
                throw new Exception('Rol no encontrado');
            }
            
            // Si se va a desactivar, verificar que no sea un rol crítico con usuarios
            if ($nuevo_estado == 0) {
                $sql_usuarios = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = ? AND activo = 1";
                $stmt_usuarios = $conexion->prepare($sql_usuarios);
                $stmt_usuarios->execute([$rol_id]);
                $usuarios_activos = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($usuarios_activos > 0) {
                    throw new Exception("No se puede desactivar el rol porque tiene {$usuarios_activos} usuarios activos asignados");
                }
            }
            
            // Actualizar estado
            $sql = "UPDATE roles SET activo = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nuevo_estado, $rol_id]);
            
            $accion_texto = $nuevo_estado ? 'activado' : 'desactivado';
            $accion_auditoria = $nuevo_estado ? 'ACTIVAR_ROL' : 'DESACTIVAR_ROL';
            
            // Registrar auditoría
            registrarAuditoria($conexion, $usuario_id, $accion_auditoria, 'roles', $rol_id, [
                'rol_nombre' => $rol_actual['nombre'],
                'estado_anterior' => $rol_actual['activo'],
                'estado_nuevo' => $nuevo_estado
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => "Rol {$accion_texto} exitosamente"
            ]);
            break;
            
        case 'usuarios_rol':
            // Obtener usuarios asignados a un rol
            $rol_id = intval($_POST['id'] ?? 0);
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            $sql = "SELECT u.nombres, u.apellidos, u.email, u.activo, u.ultimo_acceso,
                           r.nombre as rol_nombre
                    FROM usuarios u 
                    INNER JOIN roles r ON u.rol_id = r.id 
                    WHERE u.rol_id = ? 
                    ORDER BY u.activo DESC, u.apellidos ASC, u.nombres ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rol_id]);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear fechas de último acceso
            foreach ($usuarios as &$usuario) {
                if ($usuario['ultimo_acceso']) {
                    $usuario['ultimo_acceso_formato'] = date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']));
                } else {
                    $usuario['ultimo_acceso_formato'] = 'Nunca';
                }
            }
            
            echo json_encode([
                'success' => true, 
                'usuarios' => $usuarios,
                'total' => count($usuarios)
            ]);
            break;
            
        case 'eliminar':
            // Eliminar rol (solo si no tiene usuarios asignados)
            $rol_id = intval($_POST['id'] ?? 0);
            
            if ($rol_id <= 0) {
                throw new Exception('ID de rol no válido');
            }
            
            // Verificar que el rol existe
            $sql_verificar = "SELECT nombre FROM roles WHERE id = ?";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            $stmt_verificar->execute([$rol_id]);
            $rol_actual = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol_actual) {
                throw new Exception('Rol no encontrado');
            }
            
            // Verificar que no tenga usuarios asignados
            $sql_usuarios = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = ?";
            $stmt_usuarios = $conexion->prepare($sql_usuarios);
            $stmt_usuarios->execute([$rol_id]);
            $total_usuarios = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($total_usuarios > 0) {
                throw new Exception("No se puede eliminar el rol porque tiene {$total_usuarios} usuarios asignados");
            }
            
            // Eliminar rol
            $sql = "DELETE FROM roles WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$rol_id]);
            
            // Registrar auditoría
            registrarAuditoria($conexion, $usuario_id, 'ELIMINAR_ROL', 'roles', $rol_id, [
                'rol_nombre' => $rol_actual['nombre']
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Rol eliminado exitosamente'
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (PDOException $e) {
    error_log("Error de base de datos en procesar_roles.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error en la base de datos. Por favor, intente nuevamente.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    
} catch (Error $e) {
    error_log("Error fatal en procesar_roles.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>