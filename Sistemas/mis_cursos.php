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
    
    $estudiante_id = $estudiante['id']; // Este es el ID real de la tabla estudiantes
    
} catch (PDOException $e) {
    die("Error al cargar datos del estudiante: " . $e->getMessage());
}

// Obtener SOLO los cursos donde el estudiante está inscrito
try {
    $sql = "SELECT c.*, 
                d.nombres as docente_nombres, 
                d.apellidos as docente_apellidos,
                ac.nombre as area_nombre, 
                ac.codigo as area_codigo,
                s.grado, 
                s.seccion,
                n.nombre as nivel_nombre,
                pa.nombre as periodo_nombre,
                pa.anio as periodo_anio
            FROM cursos c
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN docentes d ON ad.docente_id = d.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            INNER JOIN secciones s ON ad.seccion_id = s.id
            INNER JOIN niveles_educativos n ON s.nivel_id = n.id
            INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
            WHERE JSON_SEARCH(c.estudiantes_inscritos, 'one', :estudiante_id, NULL, '$[*].estudiante_id') IS NOT NULL
            ORDER BY c.fecha_creacion DESC";
    
    $stmt_cursos = $conexion->prepare($sql);
    $stmt_cursos->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_cursos->execute();
    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $cursos = [];
    $error_cursos = "Error al cargar cursos: " . $e->getMessage();
}

// Procesar estadísticas del estudiante
$total_cursos = count($cursos);
$cursos_activos = 0;
$progreso_total = 0;

foreach ($cursos as &$curso) {
    $config = json_decode($curso['configuraciones'], true) ?? [];
    $estudiantes = json_decode($curso['estudiantes_inscritos'], true) ?? [];
    
    // Buscar datos específicos del estudiante en este curso
    $mi_progreso = 0;
    $mi_estado = 'INACTIVO';
    $fecha_inscripcion = null;
    
    if (is_array($estudiantes)) {
        foreach ($estudiantes as $est) {
            if (isset($est['estudiante_id']) && $est['estudiante_id'] == $estudiante_id) {
                $mi_progreso = $est['progreso'] ?? 0;
                $mi_estado = $est['estado'] ?? 'INACTIVO';
                $fecha_inscripcion = $est['fecha_inscripcion'] ?? null;
                break;
            }
        }
    }
    
    $curso['mi_progreso'] = $mi_progreso;
    $curso['mi_estado'] = $mi_estado;
    $curso['fecha_inscripcion'] = $fecha_inscripcion;
    $progreso_total += $mi_progreso;
    
    if (isset($config['estado']) && $config['estado'] === 'ACTIVO') {
        $cursos_activos++;
    }
}

$promedio_progreso = $total_cursos > 0 ? round($progreso_total / $total_cursos, 1) : 0;

// Estadísticas por área
$cursos_por_area = [];
foreach ($cursos as $curso) {
    $area = $curso['area_nombre'];
    $cursos_por_area[$area] = ($cursos_por_area[$area] ?? 0) + 1;
}
arsort($cursos_por_area);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Cursos - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
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

        .curso-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .curso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .curso-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .curso-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .curso-codigo {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .curso-nombre {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .curso-body {
            padding: 1.25rem;
        }

        .curso-info-item {
            padding: 0.65rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .curso-info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .curso-info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .curso-info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .progreso-container {
            margin-top: 1rem;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
        }

        .progreso-bar {
            height: 12px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progreso-fill {
            height: 100%;
            background: linear-gradient(90deg, #A8D8EA, #6CB4D8);
            transition: width 0.5s ease;
            border-radius: 10px;
        }

        .progreso-text {
            font-size: 0.9rem;
            color: #2c3e50;
            margin-top: 0.5rem;
            font-weight: 700;
            text-align: center;
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

        .badge-progreso {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
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
    </style>
    </style>
    </style>
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
                        <i class="ti ti-book-2 me-2"></i>
                        Mis Cursos
                    </h4>
                    <p>Bienvenido/a <?= htmlspecialchars($estudiante['nombres']) ?> <?= htmlspecialchars($estudiante['apellidos']) ?>. Aquí puedes acceder a todos tus cursos, ver tu progreso y materiales de estudio.</p>
                </div>

                <!-- Estadísticas del Estudiante -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?= $total_cursos ?></div>
                            <div class="stats-label">
                                <i class="ti ti-book-2 me-1"></i>
                                Cursos Inscritos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card secondary">
                            <div class="stats-number"><?= $cursos_activos ?></div>
                            <div class="stats-label">
                                <i class="ti ti-circle-check me-1"></i>
                                Cursos Activos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card tertiary">
                            <div class="stats-number"><?= $promedio_progreso ?>%</div>
                            <div class="stats-label">
                                <i class="ti ti-chart-line me-1"></i>
                                Progreso Promedio
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-filter me-1"></i>
                                Área Curricular
                            </label>
                            <select class="form-select" id="filtroArea">
                                <option value="">Todas las áreas</option>
                                <?php foreach (array_keys($cursos_por_area) as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-search me-1"></i>
                                Buscar Curso
                            </label>
                            <input type="text" class="form-control" id="buscarCurso" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-sort-ascending me-1"></i>
                                Ordenar por
                            </label>
                            <select class="form-select" id="ordenarPor">
                                <option value="reciente">Más recientes</option>
                                <option value="progreso">Mayor progreso</option>
                                <option value="nombre">Nombre A-Z</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Cursos Grid -->
                <div class="row" id="cursosContainer">
                    <?php if (empty($cursos)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert" style="border-radius: 12px; padding: 2rem;">
                                <i class="ti ti-info-circle fs-1 mb-3" style="color: #A8D8EA;"></i>
                                <h5>No estás inscrito en ningún curso</h5>
                                <p class="mb-0">Contacta con la secretaría académica para realizar tu inscripción.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cursos as $curso): 
                            $mi_progreso = $curso['mi_progreso'] ?? 0;
                            $mi_estado = $curso['mi_estado'] ?? 'INACTIVO';
                            $docente_completo = 'Prof. ' . $curso['docente_apellidos'] . ', ' . $curso['docente_nombres'];
                        ?>
                        <div class="col-md-4 curso-card-wrapper" 
                             data-area="<?= htmlspecialchars($curso['area_nombre']) ?>"
                             data-progreso="<?= $mi_progreso ?>"
                             data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                            <div class="curso-card">
                                <!-- Header -->
                                <div class="curso-header">
                                    <div class="curso-codigo"><?= htmlspecialchars($curso['codigo_curso']) ?></div>
                                    <h5 class="curso-nombre"><?= htmlspecialchars($curso['nombre']) ?></h5>
                                </div>

                                <!-- Body -->
                                <div class="curso-body">
                                    <div class="curso-info-item">
                                        <span class="curso-info-label">
                                            <i class="ti ti-book"></i>
                                            Área
                                        </span>
                                        <span class="curso-info-value"><?= htmlspecialchars($curso['area_nombre']) ?></span>
                                    </div>

                                    <div class="curso-info-item">
                                        <span class="curso-info-label">
                                            <i class="ti ti-user"></i>
                                            Docente
                                        </span>
                                        <span class="curso-info-value" style="font-size: 0.85rem;">
                                            <?= htmlspecialchars($docente_completo) ?>
                                        </span>
                                    </div>

                                    <div class="curso-info-item">
                                        <span class="curso-info-label">
                                            <i class="ti ti-school"></i>
                                            Sección
                                        </span>
                                        <span class="curso-info-value">
                                            <?= htmlspecialchars($curso['grado']) ?> "<?= htmlspecialchars($curso['seccion']) ?>"
                                        </span>
                                    </div>

                                    <div class="curso-info-item">
                                        <span class="curso-info-label">
                                            <i class="ti ti-calendar"></i>
                                            Periodo
                                        </span>
                                        <span class="curso-info-value"><?= htmlspecialchars($curso['periodo_nombre']) ?></span>
                                    </div>

                                    <!-- Progreso del Estudiante -->
                                    <div class="progreso-container">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="curso-info-label">
                                                <i class="ti ti-progress"></i>
                                                Mi Progreso
                                            </span>
                                            <span class="badge-progreso"><?= $mi_progreso ?>%</span>
                                        </div>
                                        <div class="progreso-bar">
                                            <div class="progreso-fill" style="width: <?= $mi_progreso ?>%;"></div>
                                        </div>
                                    </div>

                                    <!-- Acciones del Estudiante -->
                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-action btn-primary-custom flex-fill" 
                                                onclick="accederCurso(<?= $curso['id'] ?>)"
                                                title="Ver contenido del curso">
                                            <i class="ti ti-book-2 me-1"></i>
                                            Ver Curso
                                        </button>
                                        <button class="btn btn-action btn-success-custom flex-fill" 
                                                onclick="verCalificaciones(<?= $curso['id'] ?>)"
                                                title="Ver mis calificaciones">
                                            <i class="ti ti-certificate me-1"></i>
                                            Notas
                                        </button>
                                        <button class="btn btn-action btn-info-custom" 
                                                onclick="verEstadisticas(<?= $curso['id'] ?>)"
                                                title="Ver estadísticas">
                                            <i class="ti ti-chart-bar"></i>
                                        </button>
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

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const cursosData = <?= json_encode($cursos, JSON_UNESCAPED_UNICODE) ?>;
        const estudianteId = <?= $estudiante_id ?>;

        $(document).ready(function() {
            $('#filtroArea, #buscarCurso, #ordenarPor').on('change keyup', aplicarFiltrosYOrden);
        });

        function aplicarFiltrosYOrden() {
            const areaFiltro = $('#filtroArea').val().toLowerCase();
            const busqueda = $('#buscarCurso').val().toLowerCase();
            const orden = $('#ordenarPor').val();

            let cursosVisibles = $('.curso-card-wrapper').get();

            cursosVisibles.forEach(function(card) {
                const $card = $(card);
                const area = $card.data('area').toString().toLowerCase();
                const nombre = $card.data('nombre').toString().toLowerCase();
                
                let mostrar = true;

                if (areaFiltro && !area.includes(areaFiltro)) {
                    mostrar = false;
                }

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                $card.toggle(mostrar);
            });

            cursosVisibles = $('.curso-card-wrapper:visible').get();
            cursosVisibles.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);

                if (orden === 'progreso') {
                    return $b.data('progreso') - $a.data('progreso');
                } else if (orden === 'nombre') {
                    return $a.data('nombre').localeCompare($b.data('nombre'));
                }
                return 0;
            });

            $('#cursosContainer').append(cursosVisibles);
        }

        function accederCurso(cursoId) {
            mostrarCarga();
            window.location.href = `contenido_curso.php?curso_id=${cursoId}`;
        }

        function verCalificaciones(cursoId) {
            mostrarAlerta('info', 'Próximamente', 'La funcionalidad de calificaciones estará disponible pronto.');
        }

        function verEstadisticas(cursoId) {
            mostrarAlerta('info', 'Próximamente', 'Las estadísticas del curso estarán disponibles pronto.');
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