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

// Obtener calificaciones del estudiante con toda la información relacionada
try {
    $sql = "SELECT c.*, 
                ac.nombre as area_nombre,
                ac.codigo as area_codigo,
                d.nombres as docente_nombres,
                d.apellidos as docente_apellidos,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                pa.nombre as periodo_nombre
            FROM calificaciones c
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            INNER JOIN docentes d ON c.docente_id = d.id
            INNER JOIN secciones s ON ad.seccion_id = s.id
            INNER JOIN niveles_educativos n ON s.nivel_id = n.id
            INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
            WHERE c.estudiante_id = :estudiante_id
            ORDER BY c.fecha_evaluacion DESC, ac.nombre ASC";
    
    $stmt_calificaciones = $conexion->prepare($sql);
    $stmt_calificaciones->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_calificaciones->execute();
    $calificaciones = $stmt_calificaciones->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $calificaciones = [];
    $error_calificaciones = "Error al cargar calificaciones: " . $e->getMessage();
}

// Procesar estadísticas y agrupaciones
$total_calificaciones = count($calificaciones);
$promedio_general = 0;
$nota_maxima = 0;
$nota_minima = 20;

$calificaciones_por_area = [];
$calificaciones_por_periodo = [];
$calificaciones_por_tipo = [];
$areas_unicas = [];
$periodos_unicos = [];

foreach ($calificaciones as &$cal) {
    $area = $cal['area_nombre'];
    $periodo = $cal['periodo_evaluacion'];
    $tipo = $cal['tipo_origen'] ?? 'MANUAL';
    $nota = floatval($cal['calificacion']);
    $peso = floatval($cal['peso']);
    
    // Metadatos
    $metadatos = json_decode($cal['metadatos'], true) ?? [];
    $cal['observaciones'] = $metadatos['observaciones'] ?? '';
    $cal['es_recuperacion'] = $metadatos['recuperacion'] ?? false;
    $cal['exonerado'] = $metadatos['exonerado'] ?? false;
    
    // Estadísticas generales
    $promedio_general += $nota * $peso;
    $nota_maxima = max($nota_maxima, $nota);
    $nota_minima = min($nota_minima, $nota);
    
    // Agrupar por área
    if (!isset($calificaciones_por_area[$area])) {
        $calificaciones_por_area[$area] = [
            'suma_notas' => 0,
            'suma_pesos' => 0,
            'cantidad' => 0,
            'notas' => []
        ];
    }
    $calificaciones_por_area[$area]['suma_notas'] += $nota * $peso;
    $calificaciones_por_area[$area]['suma_pesos'] += $peso;
    $calificaciones_por_area[$area]['cantidad']++;
    $calificaciones_por_area[$area]['notas'][] = $nota;
    
    // Agrupar por periodo
    if (!isset($calificaciones_por_periodo[$periodo])) {
        $calificaciones_por_periodo[$periodo] = [
            'suma_notas' => 0,
            'suma_pesos' => 0,
            'cantidad' => 0
        ];
    }
    $calificaciones_por_periodo[$periodo]['suma_notas'] += $nota * $peso;
    $calificaciones_por_periodo[$periodo]['suma_pesos'] += $peso;
    $calificaciones_por_periodo[$periodo]['cantidad']++;
    
    // Agrupar por tipo
    if (!isset($calificaciones_por_tipo[$tipo])) {
        $calificaciones_por_tipo[$tipo] = [
            'suma_notas' => 0,
            'suma_pesos' => 0,
            'cantidad' => 0
        ];
    }
    $calificaciones_por_tipo[$tipo]['suma_notas'] += $nota * $peso;
    $calificaciones_por_tipo[$tipo]['suma_pesos'] += $peso;
    $calificaciones_por_tipo[$tipo]['cantidad']++;
    
    $areas_unicas[$area] = true;
    $periodos_unicos[$periodo] = true;
}

// Calcular promedios
$suma_pesos_total = array_sum(array_column($calificaciones, 'peso'));
$promedio_general = $suma_pesos_total > 0 ? round($promedio_general / $suma_pesos_total, 2) : 0;

foreach ($calificaciones_por_area as $area => &$data) {
    $data['promedio'] = $data['suma_pesos'] > 0 ? round($data['suma_notas'] / $data['suma_pesos'], 2) : 0;
}

foreach ($calificaciones_por_periodo as $periodo => &$data) {
    $data['promedio'] = $data['suma_pesos'] > 0 ? round($data['suma_notas'] / $data['suma_pesos'], 2) : 0;
}

// Ordenar por promedio descendente
uasort($calificaciones_por_area, function($a, $b) {
    return $b['promedio'] <=> $a['promedio'];
});

ksort($periodos_unicos);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Calificaciones - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        .calificacion-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .calificacion-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .calificacion-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calificacion-titulo {
            font-weight: 700;
            font-size: 1rem;
        }

        .nota-badge {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.3rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .nota-badge.excelente {
            background: linear-gradient(45deg, #4CAF50, #45a049);
        }

        .nota-badge.bueno {
            background: linear-gradient(45deg, #2196F3, #1976d2);
        }

        .nota-badge.regular {
            background: linear-gradient(45deg, #FFA502, #FF7F50);
        }

        .nota-badge.bajo {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
        }

        .calificacion-body {
            padding: 1.25rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .tipo-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .tipo-tarea {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
        }

        .tipo-cuestionario {
            background: linear-gradient(45deg, #FFAAA5, #FF9A95);
            color: white;
        }

        .tipo-examen {
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .tipo-participacion {
            background: linear-gradient(45deg, #FFDDC1, #FAD0C4);
            color: #8B4513;
        }

        .tipo-manual {
            background: linear-gradient(45deg, #E0E0E0, #BDBDBD);
            color: #424242;
        }

        .chart-container {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8e8e8;
            margin-bottom: 1.5rem;
            height: 400px;
        }

        .chart-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
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

        .area-promedio-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .area-nombre {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .area-promedio {
            font-weight: 700;
            font-size: 1.3rem;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
        }

        .recuperacion-badge {
            background: linear-gradient(45deg, #FFA502, #FF7F50);
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        .observacion-text {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
            border-left: 3px solid #A8D8EA;
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
                        <i class="ti ti-certificate me-2"></i>
                        Mis Calificaciones
                    </h4>
                    <p>Revisa tu rendimiento académico, calificaciones por curso y estadísticas de desempeño.</p>
                </div>

                <!-- Estadísticas Generales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?= number_format($promedio_general, 2) ?></div>
                            <div class="stats-label">
                                <i class="ti ti-chart-line me-1"></i>
                                Promedio General
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card secondary">
                            <div class="stats-number"><?= $total_calificaciones ?></div>
                            <div class="stats-label">
                                <i class="ti ti-clipboard-check me-1"></i>
                                Evaluaciones
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card tertiary">
                            <div class="stats-number"><?= number_format($nota_maxima, 2) ?></div>
                            <div class="stats-label">
                                <i class="ti ti-arrow-up me-1"></i>
                                Nota Más Alta
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card quaternary">
                            <div class="stats-number"><?= number_format($nota_minima, 2) ?></div>
                            <div class="stats-label">
                                <i class="ti ti-arrow-down me-1"></i>
                                Nota Más Baja
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos de Rendimiento -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="chart-title">
                                <i class="ti ti-chart-bar me-2"></i>
                                Promedio por Área Curricular
                            </h6>
                            <canvas id="chartAreas"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h6 class="chart-title">
                                <i class="ti ti-chart-line me-2"></i>
                                Evolución por Periodo
                            </h6>
                            <canvas id="chartPeriodos"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Promedios por Área -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                            <div class="card-header" style="background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%); color: white; border-radius: 14px 14px 0 0;">
                                <h6 class="mb-0 fw-bold">
                                    <i class="ti ti-report-analytics me-2"></i>
                                    Rendimiento por Área Curricular
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($calificaciones_por_area as $area => $data): 
                                        $promedio = $data['promedio'];
                                        $clase = '';
                                        if ($promedio >= 18) $clase = 'excelente';
                                        elseif ($promedio >= 14) $clase = 'bueno';
                                        elseif ($promedio >= 11) $clase = 'regular';
                                        else $clase = 'bajo';
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="area-promedio-card">
                                            <div>
                                                <div class="area-nombre"><?= htmlspecialchars($area) ?></div>
                                                <small class="text-muted"><?= $data['cantidad'] ?> evaluaciones</small>
                                            </div>
                                            <div class="area-promedio <?= $clase ?>">
                                                <?= number_format($promedio, 2) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-book me-1"></i>
                                Área Curricular
                            </label>
                            <select class="form-select" id="filtroArea">
                                <option value="">Todas las áreas</option>
                                <?php foreach (array_keys($areas_unicas) as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-calendar me-1"></i>
                                Periodo
                            </label>
                            <select class="form-select" id="filtroPeriodo">
                                <option value="">Todos los periodos</option>
                                <?php foreach (array_keys($periodos_unicos) as $periodo): ?>
                                    <option value="<?= $periodo ?>">Periodo <?= $periodo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-filter me-1"></i>
                                Tipo de Evaluación
                            </label>
                            <select class="form-select" id="filtroTipo">
                                <option value="">Todos los tipos</option>
                                <option value="TAREA">Tareas</option>
                                <option value="CUESTIONARIO">Cuestionarios</option>
                                <option value="EXAMEN">Exámenes</option>
                                <option value="PARTICIPACION">Participación</option>
                                <option value="MANUAL">Manual</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-search me-1"></i>
                                Buscar
                            </label>
                            <input type="text" class="form-control" id="buscarCalificacion" placeholder="Buscar evaluación...">
                        </div>
                    </div>
                </div>

                <!-- Lista de Calificaciones -->
                <div class="row" id="calificacionesContainer">
                    <?php if (empty($calificaciones)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert" style="border-radius: 12px; padding: 2rem;">
                                <i class="ti ti-info-circle fs-1 mb-3" style="color: #A8D8EA;"></i>
                                <h5>No tienes calificaciones registradas</h5>
                                <p class="mb-0">Aún no se han publicado calificaciones en tus cursos.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($calificaciones as $cal): 
                            $nota = floatval($cal['calificacion']);
                            $clase_nota = '';
                            if ($nota >= 18) $clase_nota = 'excelente';
                            elseif ($nota >= 14) $clase_nota = 'bueno';
                            elseif ($nota >= 11) $clase_nota = 'regular';
                            else $clase_nota = 'bajo';
                            
                            $tipo = $cal['tipo_origen'] ?? 'MANUAL';
                            $clase_tipo = 'tipo-' . strtolower($tipo);
                        ?>
                        <div class="col-md-6 calificacion-card-wrapper"
                             data-area="<?= htmlspecialchars($cal['area_nombre']) ?>"
                             data-periodo="<?= $cal['periodo_evaluacion'] ?>"
                             data-tipo="<?= $tipo ?>"
                             data-instrumento="<?= htmlspecialchars($cal['instrumento']) ?>">
                            <div class="calificacion-card">
                                <div class="calificacion-header">
                                    <div>
                                        <div class="calificacion-titulo">
                                            <?= htmlspecialchars($cal['instrumento']) ?>
                                            <?php if ($cal['es_recuperacion']): ?>
                                                <span class="recuperacion-badge">RECUPERACIÓN</span>
                                            <?php endif; ?>
                                        </div>
                                        <small><?= htmlspecialchars($cal['area_nombre']) ?></small>
                                    </div>
                                    <div class="nota-badge <?= $clase_nota ?>">
                                        <?= number_format($nota, 2) ?>
                                    </div>
                                </div>
                                <div class="calificacion-body">
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-user me-1"></i>
                                            Docente
                                        </span>
                                        <span class="info-value">
                                            <?= htmlspecialchars($cal['docente_apellidos']) ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-calendar-event me-1"></i>
                                            Fecha
                                        </span>
                                        <span class="info-value">
                                            <?= date('d/m/Y', strtotime($cal['fecha_evaluacion'])) ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-chart-dots me-1"></i>
                                            Periodo
                                        </span>
                                        <span class="info-value">
                                            Periodo <?= $cal['periodo_evaluacion'] ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-category me-1"></i>
                                            Tipo
                                        </span>
                                        <span class="tipo-badge <?= $clase_tipo ?>">
                                            <?= $tipo ?>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="ti ti-weight me-1"></i>
                                            Peso
                                        </span>
                                        <span class="info-value">
                                            <?= number_format($cal['peso'], 2) ?>x
                                        </span>
                                    </div>
                                    <?php if (!empty($cal['observaciones'])): ?>
                                    <div class="observacion-text">
                                        <i class="ti ti-message-circle me-1"></i>
                                        <?= htmlspecialchars($cal['observaciones']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const calificacionesData = <?= json_encode($calificaciones, JSON_UNESCAPED_UNICODE) ?>;
        const calificacionesPorArea = <?= json_encode($calificaciones_por_area, JSON_UNESCAPED_UNICODE) ?>;
        const calificacionesPorPeriodo = <?= json_encode($calificaciones_por_periodo, JSON_UNESCAPED_UNICODE) ?>;

        $(document).ready(function() {
            // Inicializar gráficos
            inicializarGraficos();
            
            // Aplicar filtros
            $('#filtroArea, #filtroPeriodo, #filtroTipo, #buscarCalificacion').on('change keyup', aplicarFiltros);
        });

        function inicializarGraficos() {
            // Gráfico de Áreas
            const ctxAreas = document.getElementById('chartAreas').getContext('2d');
            const areasLabels = Object.keys(calificacionesPorArea);
            const areasData = Object.values(calificacionesPorArea).map(d => d.promedio);
            
            new Chart(ctxAreas, {
                type: 'bar',
                data: {
                    labels: areasLabels,
                    datasets: [{
                        label: 'Promedio',
                        data: areasData,
                        backgroundColor: [
                            'rgba(168, 216, 234, 0.8)',
                            'rgba(255, 170, 165, 0.8)',
                            'rgba(199, 206, 234, 0.8)',
                            'rgba(255, 221, 193, 0.8)',
                            'rgba(212, 165, 165, 0.8)'
                        ],
                        borderColor: [
                            'rgba(168, 216, 234, 1)',
                            'rgba(255, 170, 165, 1)',
                            'rgba(199, 206, 234, 1)',
                            'rgba(255, 221, 193, 1)',
                            'rgba(212, 165, 165, 1)'
                        ],
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    }
                }
            });

            // Gráfico de Periodos
            const ctxPeriodos = document.getElementById('chartPeriodos').getContext('2d');
            const periodosLabels = Object.keys(calificacionesPorPeriodo).map(p => `Periodo ${p}`);
            const periodosData = Object.values(calificacionesPorPeriodo).map(d => d.promedio);
            
            new Chart(ctxPeriodos, {
                type: 'line',
                data: {
                    labels: periodosLabels,
                    datasets: [{
                        label: 'Promedio',
                        data: periodosData,
                        borderColor: 'rgba(168, 216, 234, 1)',
                        backgroundColor: 'rgba(168, 216, 234, 0.2)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: 'rgba(168, 216, 234, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    }
                }
            });
        }

        function aplicarFiltros() {
            const areaFiltro = $('#filtroArea').val().toLowerCase();
            const periodoFiltro = $('#filtroPeriodo').val();
            const tipoFiltro = $('#filtroTipo').val();
            const busqueda = $('#buscarCalificacion').val().toLowerCase();

            let calificacionesVisibles = 0;

            $('.calificacion-card-wrapper').each(function() {
                const card = $(this);
                const area = card.data('area').toString().toLowerCase();
                const periodo = card.data('periodo').toString();
                const tipo = card.data('tipo').toString();
                const instrumento = card.data('instrumento').toString().toLowerCase();
                
                let mostrar = true;

                if (areaFiltro && !area.includes(areaFiltro)) {
                    mostrar = false;
                }

                if (periodoFiltro && periodo !== periodoFiltro) {
                    mostrar = false;
                }

                if (tipoFiltro && tipo !== tipoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !instrumento.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
                if (mostrar) calificacionesVisibles++;
            });

            // Mostrar mensaje si no hay resultados
            if (calificacionesVisibles === 0 && $('.calificacion-card-wrapper').length > 0) {
                if ($('#noResultados').length === 0) {
                    $('#calificacionesContainer').append(`
                        <div class="col-12" id="noResultados">
                            <div class="alert alert-warning text-center">
                                <i class="ti ti-search-off fs-4 mb-2"></i>
                                <h5>No se encontraron calificaciones</h5>
                                <p class="mb-0">Intenta con otros criterios de búsqueda</p>
                            </div>
                        </div>
                    `);
                }
            } else {
                $('#noResultados').remove();
            }
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