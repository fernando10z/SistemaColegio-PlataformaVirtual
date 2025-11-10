<?php 
require_once 'conexion/bd.php';
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

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

// Obtener tareas de los cursos donde está inscrito el estudiante
try {
    $sql = "SELECT t.*, 
                c.nombre as curso_nombre,
                c.codigo_curso,
                ac.nombre as area_nombre,
                d.nombres as docente_nombres,
                d.apellidos as docente_apellidos,
                et.id as entrega_id,
                et.estado as estado_entrega,
                et.calificacion as calificacion_json,
                et.contenido as contenido_entrega,
                et.metadatos as metadatos_entrega
            FROM tareas t
            INNER JOIN cursos c ON t.curso_id = c.id
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            INNER JOIN docentes d ON ad.docente_id = d.id
            LEFT JOIN entregas_tareas et ON t.id = et.tarea_id AND et.estudiante_id = :estudiante_id
            WHERE JSON_SEARCH(c.estudiantes_inscritos, 'one', :estudiante_id2, NULL, '$[*].estudiante_id') IS NOT NULL
            AND t.estado IN ('PUBLICADA', 'CERRADA')
            ORDER BY t.fecha_creacion DESC";
    
    $stmt_tareas = $conexion->prepare($sql);
    $stmt_tareas->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_tareas->bindParam(':estudiante_id2', $estudiante_id, PDO::PARAM_INT);
    $stmt_tareas->execute();
    $tareas = $stmt_tareas->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $tareas = [];
    $error_tareas = "Error al cargar tareas: " . $e->getMessage();
}

// Procesar estadísticas
$total_tareas = count($tareas);
$tareas_pendientes = 0;
$tareas_enviadas = 0;
$tareas_calificadas = 0;
$tareas_por_vencer = 0;

$ahora = new DateTime();

foreach ($tareas as &$tarea) {
    $config = json_decode($tarea['configuracion'], true) ?? [];
    $calificacion = json_decode($tarea['calificacion_json'], true) ?? null;
    
    $fecha_entrega = isset($config['fecha_entrega']) ? new DateTime($config['fecha_entrega']) : null;
    $tarea['fecha_entrega_formatted'] = $fecha_entrega ? $fecha_entrega->format('d/m/Y H:i') : 'Sin fecha';
    
    // Calcular días restantes
    if ($fecha_entrega) {
        $diferencia = $ahora->diff($fecha_entrega);
        $dias_restantes = $fecha_entrega > $ahora ? $diferencia->days : -$diferencia->days;
        $tarea['dias_restantes'] = $dias_restantes;
        
        if ($dias_restantes >= 0 && $dias_restantes <= 3 && !$tarea['estado_entrega']) {
            $tareas_por_vencer++;
        }
    } else {
        $tarea['dias_restantes'] = null;
    }
    
    // Estado de la tarea
    if (!$tarea['estado_entrega']) {
        $tareas_pendientes++;
        $tarea['estado_real'] = 'PENDIENTE';
    } elseif ($tarea['estado_entrega'] === 'ENVIADA') {
        $tareas_enviadas++;
        $tarea['estado_real'] = 'ENVIADA';
    } elseif ($tarea['estado_entrega'] === 'CALIFICADA') {
        $tareas_calificadas++;
        $tarea['estado_real'] = 'CALIFICADA';
        $tarea['nota_final'] = $calificacion['nota'] ?? null;
    } else {
        $tarea['estado_real'] = $tarea['estado_entrega'] ?? 'PENDIENTE';
    }
}

// Obtener cursos únicos para filtro
$cursos_unicos = [];
foreach ($tareas as $tarea) {
    $cursos_unicos[$tarea['curso_nombre']] = true;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Tareas - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
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

        .stats-card {
            background: linear-gradient(135deg, #A8D8EA 0%, #D4A5A5 100%);
            color: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            margin-bottom: 1.5rem;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .stats-card.secondary {
            background: linear-gradient(135deg, #FFAAA5 0%, #FFD3B6 100%);
        }

        .stats-card.tertiary {
            background: linear-gradient(135deg, #C7CEEA 0%, #B8C6DB 100%);
        }

        .stats-card.quaternary {
            background: linear-gradient(135deg, #FFDDC1 0%, #FAD0C4 100%);
        }

        .stats-number {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            opacity: 0.95;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .tarea-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .tarea-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .tarea-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .tarea-header.pendiente {
            background: linear-gradient(135deg, #FFAAA5 0%, #FF9A95 100%);
        }

        .tarea-header.enviada {
            background: linear-gradient(135deg, #FFDDC1 0%, #FAD0C4 100%);
        }

        .tarea-header.calificada {
            background: linear-gradient(135deg, #C7CEEA 0%, #B7BFD8 100%);
        }

        .tarea-titulo {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .tarea-curso {
            font-size: 0.85rem;
            opacity: 0.95;
            font-weight: 500;
        }

        .tarea-body {
            padding: 1.25rem;
        }

        .tarea-info-item {
            padding: 0.65rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tarea-info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .tarea-info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tarea-info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .estado-badge {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .estado-pendiente {
            background: linear-gradient(45deg, #FFAAA5, #FF9A95);
            color: white;
        }

        .estado-enviada {
            background: linear-gradient(45deg, #FFDDC1, #FAD0C4);
            color: #8B4513;
        }

        .estado-calificada {
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .dias-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .dias-urgente {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
            color: white;
        }

        .dias-proximo {
            background: linear-gradient(45deg, #FFA502, #FF7F50);
            color: white;
        }

        .dias-normal {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
        }

        .nota-badge {
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.2rem;
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .btn-action {
            padding: 0.65rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
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

        .btn-success-custom {
            background: linear-gradient(135deg, #FFAAA5 0%, #FF9A95 100%);
            color: white;
        }

        .btn-success-custom:hover {
            background: linear-gradient(135deg, #FF9A95 0%, #FF8A85 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 170, 165, 0.4);
        }

        .btn-info-custom {
            background: linear-gradient(135deg, #C7CEEA 0%, #B7BFD8 100%);
            color: white;
        }

        .btn-info-custom:hover {
            background: linear-gradient(135deg, #B7BFD8 0%, #A7AFC8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 206, 234, 0.4);
        }

        .filtros-container {
            background: #fafafa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e8e8e8;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d0d0d0;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #A8D8EA;
            box-shadow: 0 0 0 0.2rem rgba(168, 216, 234, 0.25);
        }

        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner-border-custom {
            width: 4rem;
            height: 4rem;
            border-width: 0.4em;
            border-color: #A8D8EA;
            border-right-color: transparent;
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

        .vencido-badge {
            background: linear-gradient(45deg, #FF4444, #CC0000);
            color: white;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
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
                        <i class="ti ti-clipboard-check me-2"></i>
                        Mis Tareas
                    </h4>
                    <p>Gestiona tus tareas, entregas y revisa tus calificaciones.</p>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?= $total_tareas ?></div>
                            <div class="stats-label">
                                <i class="ti ti-clipboard-list me-1"></i>
                                Total de Tareas
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card secondary">
                            <div class="stats-number"><?= $tareas_pendientes ?></div>
                            <div class="stats-label">
                                <i class="ti ti-clock me-1"></i>
                                Pendientes
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card tertiary">
                            <div class="stats-number"><?= $tareas_enviadas ?></div>
                            <div class="stats-label">
                                <i class="ti ti-send me-1"></i>
                                Enviadas
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card quaternary">
                            <div class="stats-number"><?= $tareas_calificadas ?></div>
                            <div class="stats-label">
                                <i class="ti ti-certificate me-1"></i>
                                Calificadas
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerta de Tareas por Vencer -->
                <?php if ($tareas_por_vencer > 0): ?>
                <div class="alert alert-warning" role="alert" style="border-radius: 12px; border-left: 4px solid #FFA502;">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <strong>¡Atención!</strong> Tienes <?= $tareas_por_vencer ?> tarea<?= $tareas_por_vencer != 1 ? 's' : '' ?> por vencer en los próximos 3 días.
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-book me-1"></i>
                                Curso
                            </label>
                            <select class="form-select" id="filtroCurso">
                                <option value="">Todos los cursos</option>
                                <?php foreach (array_keys($cursos_unicos) as $curso): ?>
                                    <option value="<?= htmlspecialchars($curso) ?>"><?= htmlspecialchars($curso) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-filter me-1"></i>
                                Estado
                            </label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="PENDIENTE">Pendientes</option>
                                <option value="ENVIADA">Enviadas</option>
                                <option value="CALIFICADA">Calificadas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-search me-1"></i>
                                Buscar
                            </label>
                            <input type="text" class="form-control" id="buscarTarea" placeholder="Buscar tarea...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-sort-ascending me-1"></i>
                                Ordenar por
                            </label>
                            <select class="form-select" id="ordenarPor">
                                <option value="reciente">Más recientes</option>
                                <option value="fecha_entrega">Fecha de entrega</option>
                                <option value="curso">Curso</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tareas Grid -->
                <div class="row" id="tareasContainer">
                    <?php if (empty($tareas)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert" style="border-radius: 12px; padding: 2rem;">
                                <i class="ti ti-info-circle fs-1 mb-3" style="color: #A8D8EA;"></i>
                                <h5>No tienes tareas asignadas</h5>
                                <p class="mb-0">Aún no hay tareas publicadas en tus cursos.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tareas as $tarea): 
                            $estado_clase = strtolower($tarea['estado_real']);
                            $dias_restantes = $tarea['dias_restantes'];
                        ?>
                        <div class="col-md-4 tarea-card-wrapper" 
                             data-curso="<?= htmlspecialchars($tarea['curso_nombre']) ?>"
                             data-estado="<?= htmlspecialchars($tarea['estado_real']) ?>"
                             data-titulo="<?= htmlspecialchars($tarea['titulo']) ?>"
                             data-fecha="<?= $dias_restantes ?? 999 ?>">
                            <div class="tarea-card">
                                <!-- Header -->
                                <div class="tarea-header <?= $estado_clase ?>">
                                    <div class="tarea-titulo"><?= htmlspecialchars($tarea['titulo']) ?></div>
                                    <div class="tarea-curso">
                                        <i class="ti ti-book me-1"></i>
                                        <?= htmlspecialchars($tarea['curso_nombre']) ?>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="tarea-body">
                                    <div class="tarea-info-item">
                                        <span class="tarea-info-label">
                                            <i class="ti ti-user"></i>
                                            Docente
                                        </span>
                                        <span class="tarea-info-value" style="font-size: 0.8rem;">
                                            <?= htmlspecialchars($tarea['docente_apellidos']) ?>
                                        </span>
                                    </div>

                                    <div class="tarea-info-item">
                                        <span class="tarea-info-label">
                                            <i class="ti ti-calendar-event"></i>
                                            Entrega
                                        </span>
                                        <span class="tarea-info-value" style="font-size: 0.85rem;">
                                            <?= $tarea['fecha_entrega_formatted'] ?>
                                        </span>
                                    </div>

                                    <div class="tarea-info-item">
                                        <span class="tarea-info-label">
                                            <i class="ti ti-clock"></i>
                                            Tiempo
                                        </span>
                                        <?php if ($dias_restantes !== null): ?>
                                            <?php if ($dias_restantes < 0): ?>
                                                <span class="vencido-badge">VENCIDA</span>
                                            <?php elseif ($dias_restantes == 0): ?>
                                                <span class="dias-badge dias-urgente">HOY</span>
                                            <?php elseif ($dias_restantes <= 1): ?>
                                                <span class="dias-badge dias-urgente"><?= $dias_restantes ?> día</span>
                                            <?php elseif ($dias_restantes <= 3): ?>
                                                <span class="dias-badge dias-proximo"><?= $dias_restantes ?> días</span>
                                            <?php else: ?>
                                                <span class="dias-badge dias-normal"><?= $dias_restantes ?> días</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="tarea-info-value">Sin límite</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="tarea-info-item">
                                        <span class="tarea-info-label">
                                            <i class="ti ti-status-change"></i>
                                            Estado
                                        </span>
                                        <span class="estado-badge estado-<?= $estado_clase ?>">
                                            <?= $tarea['estado_real'] ?>
                                        </span>
                                    </div>

                                    <?php if ($tarea['estado_real'] === 'CALIFICADA' && $tarea['nota_final']): ?>
                                    <div class="tarea-info-item">
                                        <span class="tarea-info-label">
                                            <i class="ti ti-certificate"></i>
                                            Calificación
                                        </span>
                                        <span class="nota-badge"><?= number_format($tarea['nota_final'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Acciones -->
                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-action btn-primary-custom flex-fill" 
                                                onclick="verDetalleTarea(<?= $tarea['id'] ?>)"
                                                title="Ver detalles">
                                            <i class="ti ti-eye me-1"></i>
                                            Ver Detalle
                                        </button>
                                        
                                        <?php if ($tarea['estado_real'] === 'PENDIENTE'): ?>
                                        <button class="btn btn-action btn-success-custom flex-fill" 
                                                onclick="entregarTarea(<?= $tarea['id'] ?>)"
                                                title="Entregar tarea">
                                            <i class="ti ti-upload me-1"></i>
                                            Entregar
                                        </button>
                                        <?php elseif ($tarea['estado_real'] === 'CALIFICADA'): ?>
                                        <button class="btn btn-action btn-info-custom flex-fill" 
                                                onclick="verCalificacion(<?= $tarea['id'] ?>)"
                                                title="Ver calificación">
                                            <i class="ti ti-certificate me-1"></i>
                                            Calificación
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border spinner-border-custom" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/mis_tareas/modal_detalle_tarea.php'; ?>
    <?php include 'modales/mis_tareas/modal_entregar_tarea.php'; ?>
    <?php include 'modales/mis_tareas/modal_ver_calificacion.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const tareasData = <?= json_encode($tareas, JSON_UNESCAPED_UNICODE) ?>;
        const estudianteId = <?= $estudiante_id ?>;

        $(document).ready(function() {
            $('#filtroCurso, #filtroEstado, #buscarTarea, #ordenarPor').on('change keyup', aplicarFiltrosYOrden);
        });

        function aplicarFiltrosYOrden() {
            const cursoFiltro = $('#filtroCurso').val().toLowerCase();
            const estadoFiltro = $('#filtroEstado').val().toUpperCase();
            const busqueda = $('#buscarTarea').val().toLowerCase();
            const orden = $('#ordenarPor').val();

            let tareasVisibles = $('.tarea-card-wrapper').get();

            // Filtrar
            tareasVisibles.forEach(function(card) {
                const $card = $(card);
                const curso = $card.data('curso').toString().toLowerCase();
                const estado = $card.data('estado').toString().toUpperCase();
                const titulo = $card.data('titulo').toString().toLowerCase();
                
                let mostrar = true;

                if (cursoFiltro && !curso.includes(cursoFiltro)) {
                    mostrar = false;
                }

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !titulo.includes(busqueda)) {
                    mostrar = false;
                }

                $card.toggle(mostrar);
            });

            // Ordenar
            tareasVisibles = $('.tarea-card-wrapper:visible').get();
            tareasVisibles.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);

                if (orden === 'fecha_entrega') {
                    return $a.data('fecha') - $b.data('fecha');
                } else if (orden === 'curso') {
                    return $a.data('curso').localeCompare($b.data('curso'));
                }
                return 0;
            });

            $('#tareasContainer').append(tareasVisibles);
        }

        function verDetalleTarea(tareaId) {
            const tarea = tareasData.find(t => t.id == tareaId);
            if (tarea) {
                cargarDetalleTarea(tarea);
                $('#modalDetalleTarea').modal('show');
            }
        }

        function entregarTarea(tareaId) {
            const tarea = tareasData.find(t => t.id == tareaId);
            if (tarea) {
                cargarFormularioEntrega(tarea);
                $('#modalEntregarTarea').modal('show');
            }
        }

        function verCalificacion(tareaId) {
            const tarea = tareasData.find(t => t.id == tareaId);
            if (tarea) {
                cargarCalificacionTarea(tarea);
                $('#modalVerCalificacion').modal('show');
            }
        }

        function mostrarCarga() {
            $('#loadingOverlay').addClass('active');
        }

        function ocultarCarga() {
            $('#loadingOverlay').removeClass('active');
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