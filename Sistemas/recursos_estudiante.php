<?php 
require_once 'conexion/bd.php';
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

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

// Obtener lecciones con recursos de los cursos donde está inscrito
try {
    $sql = "SELECT l.*, 
                u.titulo as unidad_titulo,
                u.curso_id,
                c.nombre as curso_nombre,
                c.codigo_curso,
                ac.nombre as area_nombre,
                pe.estado as progreso_estado,
                pe.progreso as progreso_porcentaje,
                pe.fecha_inicio as progreso_inicio,
                pe.fecha_completado as progreso_completado
            FROM lecciones l
            INNER JOIN unidades u ON l.unidad_id = u.id
            INNER JOIN cursos c ON u.curso_id = c.id
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            LEFT JOIN progreso_estudiantes pe ON l.id = pe.leccion_id AND pe.estudiante_id = :estudiante_id
            WHERE JSON_SEARCH(c.estudiantes_inscritos, 'one', :estudiante_id2, NULL, '$[*].estudiante_id') IS NOT NULL
            ORDER BY c.nombre ASC, u.orden ASC, l.orden ASC";
    
    $stmt_lecciones = $conexion->prepare($sql);
    $stmt_lecciones->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_lecciones->bindParam(':estudiante_id2', $estudiante_id, PDO::PARAM_INT);
    $stmt_lecciones->execute();
    $lecciones = $stmt_lecciones->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $lecciones = [];
    $error_lecciones = "Error al cargar recursos: " . $e->getMessage();
}

// Procesar recursos y estadísticas
$total_lecciones = count($lecciones);
$total_recursos = 0;
$recursos_completados = 0;
$lecciones_completadas = 0;
$recursos_por_tipo = [
    'PDF' => 0,
    'VIDEO' => 0,
    'ENLACE' => 0,
    'OTROS' => 0
];

$cursos_unicos = [];
$unidades_por_curso = [];

foreach ($lecciones as &$leccion) {
    $config = json_decode($leccion['configuraciones'], true) ?? [];
    $recursos = json_decode($leccion['recursos'], true) ?? [];
    
    $leccion['estado_config'] = $config['estado'] ?? 'BORRADOR';
    $leccion['obligatorio'] = $config['obligatorio'] ?? false;
    $leccion['tiempo_estimado'] = $config['tiempo_estimado'] ?? 0;
    $leccion['recursos_array'] = $recursos;
    $leccion['total_recursos'] = count($recursos);
    
    // Contar recursos por tipo
    foreach ($recursos as $recurso) {
        $tipo = $recurso['tipo'] ?? 'OTROS';
        if (isset($recursos_por_tipo[$tipo])) {
            $recursos_por_tipo[$tipo]++;
        } else {
            $recursos_por_tipo['OTROS']++;
        }
        $total_recursos++;
    }
    
    // Progreso
    if ($leccion['progreso_estado'] === 'COMPLETADO') {
        $lecciones_completadas++;
        $recursos_completados += count($recursos);
    }
    
    // Agrupar por curso y unidad
    $curso_nombre = $leccion['curso_nombre'];
    $unidad_titulo = $leccion['unidad_titulo'];
    
    $cursos_unicos[$curso_nombre] = true;
    
    if (!isset($unidades_por_curso[$curso_nombre])) {
        $unidades_por_curso[$curso_nombre] = [];
    }
    if (!isset($unidades_por_curso[$curso_nombre][$unidad_titulo])) {
        $unidades_por_curso[$curso_nombre][$unidad_titulo] = [];
    }
    $unidades_por_curso[$curso_nombre][$unidad_titulo][] = $leccion;
}

$progreso_total = $total_lecciones > 0 ? round(($lecciones_completadas / $total_lecciones) * 100, 2) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recursos Educativos - <?php echo $nombre; ?></title>
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

        .curso-section {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8e8e8;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .curso-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .curso-header:hover {
            background: linear-gradient(135deg, #9AC8D8 0%, #8AB8C8 100%);
        }

        .curso-titulo {
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .curso-toggle {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .curso-toggle.open {
            transform: rotate(180deg);
        }

        .curso-body {
            display: none;
            padding: 1.5rem;
        }

        .curso-body.open {
            display: block;
        }

        .unidad-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            border-left: 4px solid #A8D8EA;
        }

        .unidad-titulo {
            font-weight: 700;
            font-size: 1.05rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .leccion-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .leccion-card:hover {
            border-color: #A8D8EA;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .leccion-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }

        .leccion-titulo {
            font-weight: 600;
            font-size: 1rem;
            color: #2c3e50;
            flex: 1;
        }

        .tipo-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }

        .tipo-contenido {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
        }

        .tipo-actividad {
            background: linear-gradient(45deg, #FFAAA5, #FF9A95);
            color: white;
        }

        .tipo-evaluacion {
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .leccion-descripcion {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .recursos-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .recurso-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .recurso-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .recurso-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .recurso-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .recurso-icon.pdf {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
            color: white;
        }

        .recurso-icon.video {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
        }

        .recurso-icon.enlace {
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .recurso-detalles {
            flex: 1;
        }

        .recurso-titulo {
            font-weight: 600;
            font-size: 0.9rem;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .recurso-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .btn-recurso {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-ver {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
        }

        .btn-ver:hover {
            background: linear-gradient(135deg, #9AC8D8 0%, #8AB8C8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(168, 216, 234, 0.4);
        }

        .btn-descargar {
            background: linear-gradient(135deg, #FFAAA5 0%, #FF9A95 100%);
            color: white;
        }

        .btn-descargar:hover {
            background: linear-gradient(135deg, #FF9A95 0%, #FF8A85 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 170, 165, 0.4);
        }

        .progreso-badge {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
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

        .progreso-bar-container {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progreso-bar {
            background: linear-gradient(90deg, #4CAF50, #45a049);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .tiempo-badge {
            background: linear-gradient(45deg, #FFDDC1, #FAD0C4);
            color: #8B4513;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
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
                        <i class="ti ti-books me-2"></i>
                        Recursos Educativos
                    </h4>
                    <p>Accede a videos, documentos, enlaces y materiales de estudio de todos tus cursos.</p>
                    <div class="progreso-bar-container">
                        <div class="progreso-bar" style="width: <?= $progreso_total ?>%"></div>
                    </div>
                    <small class="d-block mt-2">Progreso general: <?= $progreso_total ?>% completado</small>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?= $total_recursos ?></div>
                            <div class="stats-label">
                                <i class="ti ti-files me-1"></i>
                                Total de Recursos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card secondary">
                            <div class="stats-number"><?= $recursos_por_tipo['PDF'] ?></div>
                            <div class="stats-label">
                                <i class="ti ti-file-text me-1"></i>
                                Documentos PDF
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card tertiary">
                            <div class="stats-number"><?= $recursos_por_tipo['VIDEO'] ?></div>
                            <div class="stats-label">
                                <i class="ti ti-video me-1"></i>
                                Videos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card quaternary">
                            <div class="stats-number"><?= $recursos_por_tipo['ENLACE'] ?></div>
                            <div class="stats-label">
                                <i class="ti ti-link me-1"></i>
                                Enlaces Externos
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
                                Tipo de Recurso
                            </label>
                            <select class="form-select" id="filtroTipo">
                                <option value="">Todos los tipos</option>
                                <option value="PDF">Documentos PDF</option>
                                <option value="VIDEO">Videos</option>
                                <option value="ENLACE">Enlaces</option>
                            </select>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-search me-1"></i>
                                Buscar
                            </label>
                            <input type="text" class="form-control" id="buscarRecurso" placeholder="Buscar recurso...">
                        </div>
                    </div>
                </div>

                <!-- Recursos por Curso -->
                <div id="cursosContainer">
                    <?php if (empty($unidades_por_curso)): ?>
                        <div class="alert alert-info text-center" role="alert" style="border-radius: 12px; padding: 2rem;">
                            <i class="ti ti-info-circle fs-1 mb-3" style="color: #A8D8EA;"></i>
                            <h5>No hay recursos disponibles</h5>
                            <p class="mb-0">Aún no se han publicado recursos en tus cursos.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unidades_por_curso as $curso => $unidades): ?>
                        <div class="curso-section" data-curso="<?= htmlspecialchars($curso) ?>">
                            <div class="curso-header" onclick="toggleCurso(this)">
                                <div class="curso-titulo">
                                    <i class="ti ti-book-2"></i>
                                    <?= htmlspecialchars($curso) ?>
                                </div>
                                <i class="ti ti-chevron-down curso-toggle"></i>
                            </div>
                            <div class="curso-body">
                                <?php foreach ($unidades as $unidad => $lecciones_unidad): ?>
                                <div class="unidad-section">
                                    <div class="unidad-titulo">
                                        <i class="ti ti-folder"></i>
                                        <?= htmlspecialchars($unidad) ?>
                                    </div>
                                    
                                    <?php foreach ($lecciones_unidad as $leccion): ?>
                                    <div class="leccion-card" 
                                         data-leccion-id="<?= $leccion['id'] ?>"
                                         data-titulo="<?= htmlspecialchars($leccion['titulo']) ?>">
                                        <div class="leccion-header">
                                            <div class="leccion-titulo">
                                                <?= htmlspecialchars($leccion['titulo']) ?>
                                                <span class="tipo-badge tipo-<?= strtolower($leccion['tipo']) ?>">
                                                    <?= $leccion['tipo'] ?>
                                                </span>
                                                <?php if ($leccion['progreso_estado'] === 'COMPLETADO'): ?>
                                                    <span class="progreso-badge">
                                                        <i class="ti ti-check"></i>
                                                        Completado
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($leccion['tiempo_estimado'] > 0): ?>
                                                    <span class="tiempo-badge">
                                                        <i class="ti ti-clock"></i>
                                                        <?= $leccion['tiempo_estimado'] ?> min
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($leccion['descripcion'])): ?>
                                        <div class="leccion-descripcion">
                                            <?= htmlspecialchars($leccion['descripcion']) ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($leccion['recursos_array'])): ?>
                                        <div class="recursos-list">
                                            <?php foreach ($leccion['recursos_array'] as $recurso): 
                                                $tipo_recurso = $recurso['tipo'] ?? 'ENLACE';
                                                $titulo_recurso = $recurso['titulo'] ?? 'Recurso sin título';
                                                $url_recurso = $recurso['url'] ?? '#';
                                                $descargable = $recurso['descargable'] ?? false;
                                                $duracion = $recurso['duracion'] ?? null;
                                                
                                                $icon_class = 'ti-file';
                                                $tipo_class = 'enlace';
                                                
                                                if ($tipo_recurso === 'PDF') {
                                                    $icon_class = 'ti-file-text';
                                                    $tipo_class = 'pdf';
                                                } elseif ($tipo_recurso === 'VIDEO') {
                                                    $icon_class = 'ti-video';
                                                    $tipo_class = 'video';
                                                } elseif ($tipo_recurso === 'ENLACE') {
                                                    $icon_class = 'ti-link';
                                                    $tipo_class = 'enlace';
                                                }
                                            ?>
                                            <div class="recurso-item" data-tipo="<?= $tipo_recurso ?>">
                                                <div class="recurso-info">
                                                    <div class="recurso-icon <?= $tipo_class ?>">
                                                        <i class="ti <?= $icon_class ?>"></i>
                                                    </div>
                                                    <div class="recurso-detalles">
                                                        <div class="recurso-titulo"><?= htmlspecialchars($titulo_recurso) ?></div>
                                                        <div class="recurso-meta">
                                                            <?= $tipo_recurso ?>
                                                            <?php if ($duracion): ?>
                                                                • <?= floor($duracion / 60) ?> min <?= $duracion % 60 ?> seg
                                                            <?php endif; ?>
                                                            <?php if ($descargable): ?>
                                                                • Descargable
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="<?= htmlspecialchars($url_recurso) ?>" 
                                                       target="_blank" 
                                                       class="btn btn-recurso btn-ver"
                                                       onclick="registrarAcceso(<?= $leccion['id'] ?>, '<?= $tipo_recurso ?>')">
                                                        <i class="ti ti-eye me-1"></i>
                                                        Ver
                                                    </a>
                                                    <?php if ($descargable): ?>
                                                    <a href="<?= htmlspecialchars($url_recurso) ?>" 
                                                       download 
                                                       class="btn btn-recurso btn-descargar">
                                                        <i class="ti ti-download me-1"></i>
                                                        Descargar
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-warning mb-0" role="alert">
                                            <i class="ti ti-alert-triangle me-2"></i>
                                            No hay recursos disponibles para esta lección.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/recursos_estudiante/modal_ver_recurso.php'; ?>
    <?php include 'modales/recursos_estudiante/modal_progreso_leccion.php'; ?>
    <?php include 'modales/recursos_estudiante/modal_marcar_completado.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const leccionesData = <?= json_encode($lecciones, JSON_UNESCAPED_UNICODE) ?>;
        const estudianteId = <?= $estudiante_id ?>;

        $(document).ready(function() {
            // Abrir primer curso por defecto
            $('.curso-section').first().find('.curso-body').addClass('open');
            $('.curso-section').first().find('.curso-toggle').addClass('open');
            
            // Aplicar filtros
            $('#filtroTipo, #filtroCurso, #buscarRecurso').on('change keyup', aplicarFiltros);
        });

        function toggleCurso(header) {
            const $body = $(header).siblings('.curso-body');
            const $toggle = $(header).find('.curso-toggle');
            
            $body.toggleClass('open').slideToggle(300);
            $toggle.toggleClass('open');
        }

        function aplicarFiltros() {
            const tipoFiltro = $('#filtroTipo').val().toUpperCase();
            const cursoFiltro = $('#filtroCurso').val().toLowerCase();
            const busqueda = $('#buscarRecurso').val().toLowerCase();

            $('.curso-section').each(function() {
                const $cursoSection = $(this);
                const cursoNombre = $cursoSection.data('curso').toString().toLowerCase();
                
                let cursoVisible = !cursoFiltro || cursoNombre.includes(cursoFiltro);
                let leccionesVisibles = 0;

                if (cursoVisible) {
                    $cursoSection.find('.leccion-card').each(function() {
                        const $leccion = $(this);
                        const titulo = $leccion.data('titulo').toString().toLowerCase();
                        let mostrar = true;

                        if (busqueda && !titulo.includes(busqueda)) {
                            mostrar = false;
                        }

                        if (tipoFiltro) {
                            const tieneRecursoTipo = $leccion.find(`.recurso-item[data-tipo="${tipoFiltro}"]`).length > 0;
                            if (!tieneRecursoTipo) {
                                mostrar = false;
                            }
                        }

                        $leccion.toggle(mostrar);
                        if (mostrar) leccionesVisibles++;
                    });

                    cursoVisible = leccionesVisibles > 0;
                }

                $cursoSection.toggle(cursoVisible);
            });
        }

        function registrarAcceso(leccionId, tipoRecurso) {
            // Registrar que el estudiante accedió al recurso
            $.ajax({
                url: 'procesadores/recursos_estudiante/registrar_acceso.php',
                method: 'POST',
                data: {
                    leccion_id: leccionId,
                    estudiante_id: estudianteId,
                    tipo_recurso: tipoRecurso
                },
                success: function(response) {
                    console.log('Acceso registrado');
                },
                error: function(error) {
                    console.error('Error al registrar acceso:', error);
                }
            });
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