<?php 
require_once 'conexion/bd.php';
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

                        try {
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto, direccion, refran FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    if ($colegio) {
        $colegio_nombre = isset($colegio['nombre']) ? $colegio['nombre'] : '';
        $colegio_ruc    = isset($colegio['ruc']) ? $colegio['ruc'] : '';
        $colegio_foto   = isset($colegio['foto']) ? $colegio['foto'] : '';
        $colegio_direccion = isset($colegio['direccion']) ? $colegio['direccion'] : '';
        $refran = isset($colegio['refran']) ? $colegio['refran'] : '';
    }
} catch (PDOException $e) {
    error_log("Error fetching colegio_principal: " . $e->getMessage());
}

// Variables solicitadas (nombre, ruc, foto)
$nombre = $colegio_nombre;
$ruc    = $colegio_ruc;
$foto   = $colegio_foto;
$direccion = $colegio_direccion;
$refran = $refran;

$usuario_id = $_SESSION['usuario_id'];

// Obtener información del estudiante usando usuario_id
try {
    $sql_estudiante = "SELECT e.*, 
                              u.nombres as usuario_nombres, 
                              u.apellidos as usuario_apellidos,
                              u.email as usuario_email
                       FROM estudiantes e
                       INNER JOIN usuarios u ON e.usuario_id = u.id
                       WHERE e.usuario_id = :usuario_id AND u.activo = 1";
    
    $stmt = $conexion->prepare($sql_estudiante);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        die("No se encontró información del estudiante. Contacta con el administrador.");
    }
    
    $estudiante_id = $estudiante['id'];
    
} catch (PDOException $e) {
    die("Error al cargar datos del estudiante: " . $e->getMessage());
}

// Obtener asignación de transporte del estudiante
try {
    $sql = "SELECT at.*, 
                rt.codigo_ruta,
                rt.nombre as ruta_nombre,
                rt.configuracion as ruta_config,
                rt.paraderos as ruta_paraderos,
                rt.tarifa as ruta_tarifa,
                vt.placa,
                vt.datos_vehiculo,
                vt.documentacion,
                vt.personal as vehiculo_personal,
                vt.estado as vehiculo_estado,
                pa.nombre as periodo_nombre,
                pa.anio as periodo_anio
            FROM asignaciones_transporte at
            INNER JOIN rutas_transporte rt ON at.ruta_id = rt.id
            INNER JOIN vehiculos_transporte vt ON at.vehiculo_id = vt.id
            INNER JOIN periodos_academicos pa ON at.periodo_academico_id = pa.id
            WHERE at.activo = 1
            AND pa.activo = 1
            AND JSON_SEARCH(at.estudiantes, 'one', :estudiante_id, NULL, '$[*].estudiante_id') IS NOT NULL
            ORDER BY pa.anio DESC, at.id DESC
            LIMIT 1";
    
    $stmt_asignacion = $conexion->prepare($sql);
    $stmt_asignacion->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_asignacion->execute();
    $asignacion = $stmt_asignacion->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $asignacion = null;
    $error_asignacion = "Error al cargar asignación de transporte: " . $e->getMessage();
}

// Procesar datos de la asignación
$tiene_asignacion = !empty($asignacion);
$mi_paradero = null;
$horario_recojo = null;
$horario_retorno = null;
$conductor = null;
$telefono_conductor = null;
$placa = null;
$capacidad = 0;
$paraderos = [];
$compañeros = [];

if ($tiene_asignacion) {
    // Decodificar estudiantes
    $estudiantes = json_decode($asignacion['estudiantes'], true) ?? [];
    
    // Buscar mi paradero y horario
    foreach ($estudiantes as $est) {
        if ($est['estudiante_id'] == $estudiante_id) {
            $mi_paradero = $est['paradero_id'] ?? null;
            $horario_recojo = $est['horario_recojo'] ?? null;
            $horario_retorno = $est['horario_retorno'] ?? null;
            break;
        }
    }
    
    // Obtener compañeros del mismo paradero
    foreach ($estudiantes as $est) {
        if ($est['estudiante_id'] != $estudiante_id) {
            $compañeros[] = $est;
        }
    }
    
    // Decodificar paraderos
    $paraderos = json_decode($asignacion['ruta_paraderos'], true) ?? [];
    
    // Buscar nombre del paradero
    $nombre_paradero = 'Sin asignar';
    foreach ($paraderos as $paradero) {
        if ($paradero['id'] == $mi_paradero) {
            $nombre_paradero = $paradero['nombre'] ?? $paradero['direccion'] ?? 'Paradero ' . $mi_paradero;
            break;
        }
    }
    
    // Datos del vehículo
    $datos_vehiculo = json_decode($asignacion['datos_vehiculo'], true) ?? [];
    $personal_vehiculo = json_decode($asignacion['vehiculo_personal'], true) ?? [];
    
    $placa = $asignacion['placa'];
    $capacidad = $datos_vehiculo['capacidad'] ?? 0;
    $conductor = $personal_vehiculo['conductor']['nombre'] ?? 'No asignado';
    $telefono_conductor = $personal_vehiculo['conductor']['telefono'] ?? 'No disponible';
}

// Obtener historial de asistencia del mes actual
$asistencias = [];
if ($tiene_asignacion) {
    try {
        $sql_asistencia = "SELECT fecha, registros
                          FROM asistencia_transporte
                          WHERE asignacion_id = :asignacion_id
                          AND MONTH(fecha) = MONTH(CURRENT_DATE())
                          AND YEAR(fecha) = YEAR(CURRENT_DATE())
                          ORDER BY fecha DESC";
        
        $stmt_asistencia = $conexion->prepare($sql_asistencia);
        $stmt_asistencia->bindParam(':asignacion_id', $asignacion['id'], PDO::PARAM_INT);
        $stmt_asistencia->execute();
        $asistencias_raw = $stmt_asistencia->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($asistencias_raw as $asist) {
            $registros = json_decode($asist['registros'], true) ?? [];
            foreach ($registros as $reg) {
                if ($reg['estudiante_id'] == $estudiante_id) {
                    $asistencias[] = [
                        'fecha' => $asist['fecha'],
                        'estado_ida' => $reg['estado_ida'] ?? 'NO_REGISTRADO',
                        'estado_retorno' => $reg['estado_retorno'] ?? 'NO_REGISTRADO',
                        'observaciones' => $reg['observaciones'] ?? ''
                    ];
                    break;
                }
            }
        }
        
    } catch (PDOException $e) {
        $asistencias = [];
    }
}

// Calcular estadísticas de asistencia
$total_dias = count($asistencias);
$dias_presentes_ida = 0;
$dias_presentes_retorno = 0;

foreach ($asistencias as $asist) {
    if ($asist['estado_ida'] === 'PRESENTE') $dias_presentes_ida++;
    if ($asist['estado_retorno'] === 'PRESENTE') $dias_presentes_retorno++;
}

$porcentaje_ida = $total_dias > 0 ? round(($dias_presentes_ida / $total_dias) * 100, 1) : 0;
$porcentaje_retorno = $total_dias > 0 ? round(($dias_presentes_retorno / $total_dias) * 100, 1) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Ruta de Transporte - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        /* Eliminación completa del espacio superior */
        .body-wrapper {
        margin-top: 0px !important;
        padding-top: 0px !important;
        }
        
        .body-wrapper-inner {
        margin-top: 0px !important;
        padding-top: 0px !important;
        }
        
        .container-fluid {
        margin-top: 0 !important;
        padding-top: 0 !important;
        }
        
        .app-header {
        margin-top: 0 !important;
        }
        
        /* Optimizaciones adicionales para mejor rendimiento */
        .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
        }
        
        .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
        background: transparent;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 3px;
        }
        
        /* Mejoras de accesibilidad */
        .btn:focus,
        .nav-link:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
        }

        /* CSS para left-sidebar - Eliminación de huecos y optimización */
        .left-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
            z-index: 1000;
            background-color: #fff;
            border-right: 1px solid #e9ecef;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }

        /* Contenedor interno del sidebar */
        .left-sidebar > div {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Brand logo area */
        .left-sidebar .brand-logo {
            flex-shrink: 0;
            padding: 20px 24px;
            margin: 0 !important;
            border-bottom: 1px solid #e9ecef;
        }

        /* Navegación del sidebar */
        .left-sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Lista de navegación */
        .left-sidebar #sidebarnav {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none;
        }

        /* Items del sidebar */
        .left-sidebar .sidebar-item {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Links del sidebar */
        .left-sidebar .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 24px !important;
            margin: 0 !important;
            text-decoration: none;
            color: #495057;
            border: none !important;
            background: transparent !important;
            transition: all 0.15s ease;
        }

        /* Hover effects */
        .left-sidebar .sidebar-link:hover {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
        }

        /* Active link */
        .left-sidebar .sidebar-link.active {
            background-color: #e7f1ff !important;
            color: #0d6efd !important;
            font-weight: 500;
        }

        /* Categorías pequeñas */
        .left-sidebar .nav-small-cap {
            padding: 20px 24px 8px 24px !important;
            margin: 0 !important;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Dividers */
        .left-sidebar .sidebar-divider {
            margin: 16px 24px !important;
            height: 1px;
            background-color: #e9ecef;
            border: none;
        }

        /* Badges Pro */
        .left-sidebar .badge {
            font-size: 0.625rem !important;
            padding: 4px 8px !important;
        }

        /* Submenús colapsables */
        .left-sidebar .collapse {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Items de submenú */
        .left-sidebar .first-level .sidebar-item .sidebar-link {
            padding-left: 48px !important;
            font-size: 0.875rem;
        }

        /* Scrollbar personalizado */
        .left-sidebar .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.1);
            border-radius: 2px;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0,0,0,0.2);
        }

        /* Firefox scrollbar */
        .left-sidebar .sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(0,0,0,0.1) transparent;
        }

        /* Responsive - Mobile */
        @media (max-width: 1199.98px) {
            .left-sidebar {
            margin-left: -270px;
            transition: margin-left 0.25s ease;
            }
            
            .left-sidebar.show {
            margin-left: 0;
            }
        }

        /* Mini sidebar state */
        .mini-sidebar .left-sidebar {
            width: 80px !important;
        }

        .mini-sidebar .left-sidebar .hide-menu {
            display: none !important;
        }

        .mini-sidebar .left-sidebar .brand-logo img {
            width: 40px !important;
        }
        
        /* Optimización de animaciones */
        @media (prefers-reduced-motion: reduce) {
        .card {
            transition: none;
        }
        }
    </style>
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }

        .welcome-card {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            border-radius: 14px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-card h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            opacity: 0.95;
            margin-bottom: 0;
        }

        .info-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .info-card-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        .info-row:hover {
            background: #f8f9fa;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
            text-align: right;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: linear-gradient(135deg, #A8D8EA 0%, #D4A5A5 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
        }

        .stat-box.secondary {
            background: linear-gradient(135deg, #FFAAA5 0%, #FFD3B6 100%);
        }

        .stat-box.tertiary {
            background: linear-gradient(135deg, #C7CEEA 0%, #B8C6DB 100%);
        }

        .stat-box.quaternary {
            background: linear-gradient(135deg, #FFDDC1 0%, #FAD0C4 100%);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            opacity: 0.95;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .paradero-badge {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-block;
        }

        .horario-badge {
            background: linear-gradient(45deg, #FFDDC1, #FAD0C4);
            color: #8B4513;
            padding: 0.4rem 0.9rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .estado-badge {
            padding: 0.4rem 0.9rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .estado-activo {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }

        .estado-mantenimiento {
            background: linear-gradient(45deg, #FFA502, #FF7F50);
            color: white;
        }

        .estado-inactivo {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
            color: white;
        }

        .asistencia-presente {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }

        .asistencia-ausente {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
            color: white;
        }

        .asistencia-justificado {
            background: linear-gradient(45deg, #2196F3, #1976d2);
            color: white;
        }

        .mapa-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #d0d0d0;
        }

        .paradero-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid #A8D8EA;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .paradero-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .paradero-item.mi-paradero {
            border-left-color: #FFAAA5;
            background: linear-gradient(to right, #FFF5F5, white);
        }

        .paradero-nombre {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .paradero-direccion {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .paradero-horario {
            font-size: 0.8rem;
            color: #A8D8EA;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .table-asistencia {
            width: 100%;
            margin-top: 1rem;
        }

        .table-asistencia th {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 0.75rem;
            font-weight: 600;
            text-align: center;
            border: none;
        }

        .table-asistencia td {
            padding: 0.75rem;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-asistencia tr:hover {
            background: #f8f9fa;
        }

        .no-asignacion {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-asignacion i {
            font-size: 5rem;
            color: #d0d0d0;
            margin-bottom: 1rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #9AC8D8 0%, #8AB8C8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(168, 216, 234, 0.4);
        }

        .circular-progress {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#4CAF50 0deg, #4CAF50 var(--progress), #e0e0e0 var(--progress), #e0e0e0 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0 auto 1rem;
        }

        .circular-progress::before {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
        }

        .progress-text {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
            <?php include 'includes/header.php'; ?>

            <div class="container-fluid">
                
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h4>
                        <i class="ti ti-bus me-2"></i>
                        Mi Ruta de Transporte
                    </h4>
                    <p>Información de tu ruta escolar, horarios de recojo, paradero asignado y asistencia.</p>
                </div>

                <?php if (!$tiene_asignacion): ?>
                    <!-- Sin Asignación -->
                    <div class="no-asignacion">
                        <i class="ti ti-bus-off"></i>
                        <h4>No tienes transporte escolar asignado</h4>
                        <p class="mb-3">Actualmente no estás registrado en ninguna ruta de transporte.</p>
                        <button class="btn btn-action btn-primary-custom" onclick="contactarAdministracion()">
                            <i class="ti ti-mail me-2"></i>
                            Solicitar Información
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Estadísticas de Asistencia -->
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-number"><?= $total_dias ?></div>
                            <div class="stat-label">
                                <i class="ti ti-calendar me-1"></i>
                                Días Registrados
                            </div>
                        </div>
                        <div class="stat-box secondary">
                            <div class="stat-number"><?= $dias_presentes_ida ?></div>
                            <div class="stat-label">
                                <i class="ti ti-arrow-up me-1"></i>
                                Asistencias (Ida)
                            </div>
                        </div>
                        <div class="stat-box tertiary">
                            <div class="stat-number"><?= $dias_presentes_retorno ?></div>
                            <div class="stat-label">
                                <i class="ti ti-arrow-down me-1"></i>
                                Asistencias (Retorno)
                            </div>
                        </div>
                        <div class="stat-box quaternary">
                            <div class="stat-number"><?= $porcentaje_ida ?>%</div>
                            <div class="stat-label">
                                <i class="ti ti-chart-line me-1"></i>
                                Porcentaje Global
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Información de Ruta -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <i class="ti ti-route"></i>
                                    Información de la Ruta
                                </div>
                                <div class="info-card-body">
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-route-2"></i>
                                            Código de Ruta
                                        </span>
                                        <span class="info-value"><?= htmlspecialchars($asignacion['codigo_ruta']) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-map-pin"></i>
                                            Nombre de Ruta
                                        </span>
                                        <span class="info-value"><?= htmlspecialchars($asignacion['ruta_nombre']) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-flag"></i>
                                            Mi Paradero
                                        </span>
                                        <span class="paradero-badge"><?= htmlspecialchars($nombre_paradero) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-clock"></i>
                                            Horario Recojo
                                        </span>
                                        <span class="horario-badge">
                                            <i class="ti ti-sunrise"></i>
                                            <?= $horario_recojo ?? 'No asignado' ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-clock"></i>
                                            Horario Retorno
                                        </span>
                                        <span class="horario-badge">
                                            <i class="ti ti-sunset"></i>
                                            <?= $horario_retorno ?? 'No asignado' ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-coin"></i>
                                            Tarifa Mensual
                                        </span>
                                        <span class="info-value">S/ <?= number_format($asignacion['ruta_tarifa'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Vehículo -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <i class="ti ti-bus"></i>
                                    Información del Vehículo
                                </div>
                                <div class="info-card-body">
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-license"></i>
                                            Placa
                                        </span>
                                        <span class="info-value"><?= htmlspecialchars($placa) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-users"></i>
                                            Capacidad
                                        </span>
                                        <span class="info-value"><?= $capacidad ?> pasajeros</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-user"></i>
                                            Conductor
                                        </span>
                                        <span class="info-value"><?= htmlspecialchars($conductor) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-phone"></i>
                                            Teléfono Conductor
                                        </span>
                                        <span class="info-value"><?= htmlspecialchars($telefono_conductor) ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-traffic-cone"></i>
                                            Estado del Vehículo
                                        </span>
                                        <span class="estado-badge estado-<?= strtolower($asignacion['vehiculo_estado']) ?>">
                                            <?= $asignacion['vehiculo_estado'] ?>
                                        </span>
                                    </div>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-action btn-primary-custom" onclick="verDetallesVehiculo()">
                                            <i class="ti ti-eye me-2"></i>
                                            Ver Detalles Completos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paraderos de la Ruta -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <i class="ti ti-map-pins"></i>
                                    Paraderos de la Ruta
                                </div>
                                <div class="info-card-body">
                                    <?php foreach ($paraderos as $paradero): 
                                        $es_mi_paradero = ($paradero['id'] == $mi_paradero);
                                    ?>
                                    <div class="paradero-item <?= $es_mi_paradero ? 'mi-paradero' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="paradero-nombre">
                                                    <?php if ($es_mi_paradero): ?>
                                                        <i class="ti ti-star-filled text-warning me-1"></i>
                                                    <?php else: ?>
                                                        <i class="ti ti-map-pin me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($paradero['nombre'] ?? 'Paradero ' . $paradero['id']) ?>
                                                    <?php if ($es_mi_paradero): ?>
                                                        <span class="badge bg-warning text-dark ms-2">MI PARADERO</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="paradero-direccion">
                                                    <?= htmlspecialchars($paradero['direccion'] ?? 'Dirección no especificada') ?>
                                                </div>
                                                <?php if (isset($paradero['horario_recojo'])): ?>
                                                <div class="paradero-horario">
                                                    <i class="ti ti-clock me-1"></i>
                                                    Recojo: <?= $paradero['horario_recojo'] ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <button class="btn btn-sm btn-primary-custom" 
                                                    onclick="verParaderoMapa(<?= htmlspecialchars(json_encode($paradero)) ?>)">
                                                <i class="ti ti-map"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Asistencia -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-header">
                                    <i class="ti ti-calendar-check"></i>
                                    Historial de Asistencia (Mes Actual)
                                </div>
                                <div class="info-card-body">
                                    <?php if (empty($asistencias)): ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="ti ti-info-circle fs-3 mb-2"></i>
                                            <p class="mb-0">No hay registros de asistencia este mes</p>
                                        </div>
                                    <?php else: ?>
                                        <table class="table table-asistencia">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Ida</th>
                                                    <th>Retorno</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($asistencias as $asist): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($asist['fecha'])) ?></td>
                                                    <td>
                                                        <span class="estado-badge asistencia-<?= strtolower($asist['estado_ida']) ?>">
                                                            <?php if ($asist['estado_ida'] === 'PRESENTE'): ?>
                                                                <i class="ti ti-check"></i> Presente
                                                            <?php elseif ($asist['estado_ida'] === 'AUSENTE'): ?>
                                                                <i class="ti ti-x"></i> Ausente
                                                            <?php else: ?>
                                                                <i class="ti ti-help"></i> <?= $asist['estado_ida'] ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="estado-badge asistencia-<?= strtolower($asist['estado_retorno']) ?>">
                                                            <?php if ($asist['estado_retorno'] === 'PRESENTE'): ?>
                                                                <i class="ti ti-check"></i> Presente
                                                            <?php elseif ($asist['estado_retorno'] === 'AUSENTE'): ?>
                                                                <i class="ti ti-x"></i> Ausente
                                                            <?php else: ?>
                                                                <i class="ti ti-help"></i> <?= $asist['estado_retorno'] ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/mi_ruta/modal_detalles_vehiculo.php'; ?>
    <?php include 'modales/mi_ruta/modal_ver_mapa.php'; ?>
    <?php include 'modales/mi_ruta/modal_contactar_conductor.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const asignacionData = <?= json_encode($asignacion ?? [], JSON_UNESCAPED_UNICODE) ?>;
        const estudiante_id = <?= $estudiante_id ?>;

        function verDetallesVehiculo() {
            if (!asignacionData || !asignacionData.datos_vehiculo) {
                mostrarAlerta('warning', 'Sin información', 'No hay detalles del vehículo disponibles');
                return;
            }
            
            const datos = JSON.parse(asignacionData.datos_vehiculo || '{}');
            
            $('#vehiculoPlaca').text(asignacionData.placa);
            $('#vehiculoMarca').text(datos.marca || 'No especificado');
            $('#vehiculoModelo').text(datos.modelo || 'No especificado');
            $('#vehiculoAnio').text(datos.anio || 'No especificado');
            $('#vehiculoCapacidad').text(datos.capacidad || 'No especificado');
            $('#vehiculoEstado').text(asignacionData.vehiculo_estado);
            
            $('#modalDetallesVehiculo').modal('show');
        }

        function verParaderoMapa(paradero) {
            $('#paraderoNombre').text(paradero.nombre || 'Paradero');
            $('#paraderoDireccion').text(paradero.direccion || 'Dirección no especificada');
            
            if (paradero.latitud && paradero.longitud) {
                // Aquí se podría integrar Google Maps o similar
                $('#mapaContainer').html(`
                    <div class="alert alert-info">
                        <i class="ti ti-map-pin me-2"></i>
                        Coordenadas: ${paradero.latitud}, ${paradero.longitud}
                    </div>
                `);
            } else {
                $('#mapaContainer').html(`
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        No hay coordenadas disponibles para este paradero
                    </div>
                `);
            }
            
            $('#modalVerMapa').modal('show');
        }

        function contactarAdministracion() {
            $('#modalContactarConductor').modal('show');
        }

        function mostrarAlerta(tipo, titulo, mensaje) {
            Swal.fire({
                icon: tipo,
                title: titulo,
                text: mensaje,
                confirmButtonColor: '#A8D8EA',
                confirmButtonText: 'Entendido'
            });
        }
    </script>
</body>
</html>