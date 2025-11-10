<?php
session_start();
require_once 'conexion/bd.php';

// Verificar sesión y rol de director
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['username'];

// ==================== OBTENER PERÍODO ACADÉMICO ACTIVO ====================
try {
    $stmt_periodo = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 LIMIT 1");
    $stmt_periodo->execute();
    $periodo_activo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
    $periodo_id = $periodo_activo['id'] ?? 0;
} catch (PDOException $e) {
    $periodo_id = 0;
}

// ==================== FILTROS ====================
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$filtro_tipo = $_GET['tipo_filtro'] ?? 'mes';

// Ajustar fechas según el tipo de filtro
switch ($filtro_tipo) {
    case 'dia':
        $filtro_fecha_inicio = $filtro_fecha_fin = date('Y-m-d');
        break;
    case 'mes':
        $filtro_fecha_inicio = date('Y-m-01');
        $filtro_fecha_fin = date('Y-m-t');
        break;
    case 'anio':
        $filtro_fecha_inicio = date('Y-01-01');
        $filtro_fecha_fin = date('Y-12-31');
        break;
}

// ==================== KPI 1: TOTAL ESTUDIANTES ACTIVOS ====================
try {
    $sql_estudiantes = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
    $stmt = $conexion->prepare($sql_estudiantes);
    $stmt->execute();
    $total_estudiantes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_estudiantes = 0;
}

// ==================== KPI 2: TOTAL DOCENTES ACTIVOS ====================
try {
    $sql_docentes = "SELECT COUNT(*) as total FROM docentes WHERE activo = 1";
    $stmt = $conexion->prepare($sql_docentes);
    $stmt->execute();
    $total_docentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_docentes = 0;
}

// ==================== KPI 3: ASISTENCIA PROMEDIO ====================
try {
    $sql_asistencia = "SELECT 
                        COUNT(*) as total_registros,
                        SUM(CASE WHEN estado = 'PRESENTE' THEN 1 ELSE 0 END) as presentes
                       FROM asistencias 
                       WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
    $stmt = $conexion->prepare($sql_asistencia);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $data_asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
    $porcentaje_asistencia = $data_asistencia['total_registros'] > 0 
        ? round(($data_asistencia['presentes'] / $data_asistencia['total_registros']) * 100, 1) 
        : 0;
} catch (PDOException $e) {
    $porcentaje_asistencia = 0;
}

// ==================== KPI 4: PROMEDIO GENERAL DE NOTAS ====================
try {
    $sql_notas = "SELECT AVG(calificacion) as promedio 
                  FROM calificaciones 
                  WHERE fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    $stmt = $conexion->prepare($sql_notas);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $promedio_notas = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0, 1);
} catch (PDOException $e) {
    $promedio_notas = 0;
}

// ==================== KPI 5: TAREAS PENDIENTES ====================
try {
    $sql_tareas = "SELECT COUNT(*) as total 
                   FROM tareas 
                   WHERE activo = 1 AND fecha_vencimiento >= CURDATE()";
    $stmt = $conexion->prepare($sql_tareas);
    $stmt->execute();
    $tareas_activas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $tareas_activas = 0;
}

// ==================== KPI 6: INCIDENCIAS DISCIPLINARIAS ====================
try {
    $sql_incidencias = "SELECT COUNT(*) as total 
                        FROM incidencias_disciplinarias 
                        WHERE fecha_incidencia BETWEEN :fecha_inicio AND :fecha_fin";
    $stmt = $conexion->prepare($sql_incidencias);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $total_incidencias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_incidencias = 0;
}

// ==================== GRÁFICO 1: ASISTENCIA POR DÍA ====================
try {
    $sql_asistencia_dia = "SELECT 
                            fecha,
                            COUNT(*) as total,
                            SUM(CASE WHEN estado = 'PRESENTE' THEN 1 ELSE 0 END) as presentes,
                            SUM(CASE WHEN estado = 'AUSENTE' THEN 1 ELSE 0 END) as ausentes,
                            SUM(CASE WHEN estado = 'TARDANZA' THEN 1 ELSE 0 END) as tardanzas
                           FROM asistencias 
                           WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin
                           GROUP BY fecha
                           ORDER BY fecha ASC";
    $stmt = $conexion->prepare($sql_asistencia_dia);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $asistencia_por_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asistencia_por_dia = [];
}

// ==================== GRÁFICO 2: DESEMPEÑO POR ÁREA CURRICULAR ====================
try {
    $sql_desempeno_area = "SELECT 
                            ac.nombre as area,
                            COUNT(DISTINCT c.id) as total_cursos,
                            COUNT(DISTINCT ad.docente_id) as total_docentes,
                            AVG(cal.calificacion) as promedio_notas
                           FROM areas_curriculares ac
                           LEFT JOIN asignaciones_docentes ad ON ac.id = ad.area_id
                           LEFT JOIN calificaciones cal ON ad.id = cal.asignacion_id 
                                AND cal.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin
                           LEFT JOIN cursos c ON ad.id = c.asignacion_id
                           WHERE ac.activo = 1
                           GROUP BY ac.id, ac.nombre
                           ORDER BY promedio_notas DESC";
    $stmt = $conexion->prepare($sql_desempeno_area);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $desempeno_por_area = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $desempeno_por_area = [];
}

// ==================== GRÁFICO 3: ENTREGAS DE TAREAS ====================
try {
    $sql_entregas = "SELECT 
                        DATE(et.fecha_entrega) as fecha,
                        COUNT(*) as total_entregas,
                        AVG(et.calificacion) as promedio_calificacion
                     FROM entregas_tareas et
                     WHERE et.fecha_entrega BETWEEN :fecha_inicio AND :fecha_fin
                     GROUP BY DATE(et.fecha_entrega)
                     ORDER BY fecha ASC";
    $stmt = $conexion->prepare($sql_entregas);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $entregas_tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $entregas_tareas = [];
}

// ==================== GRÁFICO 4: DISTRIBUCIÓN DE ESTUDIANTES POR GRADO ====================
try {
    $sql_estudiantes_grado = "SELECT 
                                s.grado,
                                n.nombre as nivel,
                                COUNT(DISTINCT m.estudiante_id) as total_estudiantes
                              FROM secciones s
                              INNER JOIN niveles_educativos n ON s.nivel_id = n.id
                              LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1
                              WHERE s.activo = 1
                              GROUP BY s.grado, n.nombre, n.orden
                              ORDER BY n.orden, s.grado";
    $stmt = $conexion->prepare($sql_estudiantes_grado);
    $stmt->execute();
    $estudiantes_por_grado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes_por_grado = [];
}

// ==================== TABLA: RESUMEN POR DOCENTE ====================
try {
    $sql_resumen_docente = "SELECT 
                            d.id,
                            d.nombres,
                            d.apellidos,
                            CONCAT(d.apellidos, ', ', d.nombres) as nombre_completo,
                            COUNT(DISTINCT ad.id) as asignaciones,
                            COUNT(DISTINCT cal.estudiante_id) as estudiantes_evaluados,
                            AVG(cal.calificacion) as promedio_calificaciones,
                            COUNT(DISTINCT t.id) as tareas_creadas,
                            COUNT(DISTINCT et.id) as entregas_recibidas
                           FROM docentes d
                           LEFT JOIN asignaciones_docentes ad ON d.id = ad.docente_id AND ad.activo = 1
                           LEFT JOIN calificaciones cal ON ad.id = cal.asignacion_id 
                                AND cal.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin
                           LEFT JOIN cursos c ON ad.id = c.asignacion_id
                           LEFT JOIN tareas t ON c.id = t.curso_id
                           LEFT JOIN entregas_tareas et ON t.id = et.tarea_id
                           WHERE d.activo = 1
                           GROUP BY d.id, d.nombres, d.apellidos
                           ORDER BY d.apellidos ASC";
    $stmt = $conexion->prepare($sql_resumen_docente);
    $stmt->execute([':fecha_inicio' => $filtro_fecha_inicio, ':fecha_fin' => $filtro_fecha_fin]);
    $resumen_docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $resumen_docentes = [];
}

// ==================== CONVERTIR DATOS A JSON PARA GRÁFICOS ====================
$json_asistencia = json_encode($asistencia_por_dia);
$json_areas = json_encode($desempeno_por_area);
$json_entregas = json_encode($entregas_tareas);
$json_estudiantes_grado = json_encode($estudiantes_por_grado);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Director | ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            background-color: #FFFFFF;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 50%, #E5FFE5 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .kpi-card.kpi-1 { border-left-color: #B3E5FC; }
        .kpi-card.kpi-2 { border-left-color: #C8E6C9; }
        .kpi-card.kpi-3 { border-left-color: #FFE5B4; }
        .kpi-card.kpi-4 { border-left-color: #F8BBD0; }
        .kpi-card.kpi-5 { border-left-color: #E1BEE7; }
        .kpi-card.kpi-6 { border-left-color: #FFCCBC; }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .kpi-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-icon {
            font-size: 2.5rem;
            opacity: 0.15;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }

        .chart-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #E5F0FF;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .filter-card {
            background: linear-gradient(135deg, #E5F0FF, #FFE5F0);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn-filter {
            background-color: #B3E5FC;
            color: #01579B;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            background-color: #81D4FA;
            color: #01579B;
            transform: translateY(-2px);
        }

        .btn-filter.active {
            background-color: #01579B;
            color: #FFFFFF;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, #E5F0FF, #FFE5F0);
            padding: 1rem;
            font-weight: 600;
            color: #333;
            border: none;
        }

        .table-modern tbody tr {
            background: #FFFFFF;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .table-modern tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table-modern tbody td {
            padding: 1rem;
            border: none;
            vertical-align: middle;
        }

        .badge-performance {
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-excellent {
            background: #C8E6C9;
            color: #1B5E20;
        }

        .badge-good {
            background: #B3E5FC;
            color: #01579B;
        }

        .badge-regular {
            background: #FFE5B4;
            color: #E65100;
        }

        .badge-low {
            background: #FFCCBC;
            color: #BF360C;
        }

        .export-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-export {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-export-pdf {
            background: #FFCCBC;
            color: #BF360C;
        }

        .btn-export-excel {
            background: #C8E6C9;
            color: #1B5E20;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .no-data i {
            font-size: 4rem;
            color: #DDD;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="container-fluid">
                
                <!-- Header -->
                <header class="app-header">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <ul class="navbar-nav">
                            <li class="nav-item d-block d-xl-none">
                                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                                    <i class="ti ti-menu-2"></i>
                                </a>
                            </li>
                        </ul>
                        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                                <li class="nav-item">
                                    <span class="badge bg-primary fs-2 rounded-4 lh-sm">Sistema AAC</span>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </header>

                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bold mb-2">
                                <i class="ti ti-dashboard me-2"></i>
                                Panel de Control
                            </h3>
                            <p class="mb-0 text-muted">Vista general del desempeño académico e institucional</p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-1">Bienvenido, <?= htmlspecialchars($usuario_nombre) ?></h5>
                            <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-card">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo de Filtro</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="tipo_filtro" value="dia" 
                                        class="btn btn-filter <?= $filtro_tipo == 'dia' ? 'active' : '' ?>">
                                    Hoy
                                </button>
                                <button type="submit" name="tipo_filtro" value="mes" 
                                        class="btn btn-filter <?= $filtro_tipo == 'mes' ? 'active' : '' ?>">
                                    Este Mes
                                </button>
                                <button type="submit" name="tipo_filtro" value="anio" 
                                        class="btn btn-filter <?= $filtro_tipo == 'anio' ? 'active' : '' ?>">
                                    Este Año
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="<?= $filtro_fecha_inicio ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="<?= $filtro_fecha_fin ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-filter w-100">
                                <i class="ti ti-filter me-2"></i>Aplicar Filtro
                            </button>
                        </div>
                    </form>
                </div>

                <!-- KPIs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-1 position-relative">
                            <i class="ti ti-users kpi-icon"></i>
                            <div class="kpi-value" style="color: #01579B;">
                                <?= $total_estudiantes ?>
                            </div>
                            <div class="kpi-label">Estudiantes Activos</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-2 position-relative">
                            <i class="ti ti-school kpi-icon"></i>
                            <div class="kpi-value" style="color: #1B5E20;">
                                <?= $total_docentes ?>
                            </div>
                            <div class="kpi-label">Docentes Activos</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-3 position-relative">
                            <i class="ti ti-chart-line kpi-icon"></i>
                            <div class="kpi-value" style="color: #E65100;">
                                <?= $porcentaje_asistencia ?>%
                            </div>
                            <div class="kpi-label">Asistencia Promedio</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-4 position-relative">
                            <i class="ti ti-star kpi-icon"></i>
                            <div class="kpi-value" style="color: #880E4F;">
                                <?= $promedio_notas ?>
                            </div>
                            <div class="kpi-label">Promedio General</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-5 position-relative">
                            <i class="ti ti-clipboard-check kpi-icon"></i>
                            <div class="kpi-value" style="color: #4A148C;">
                                <?= $tareas_activas ?>
                            </div>
                            <div class="kpi-label">Tareas Activas</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-6 position-relative">
                            <i class="ti ti-alert-triangle kpi-icon"></i>
                            <div class="kpi-value" style="color: #BF360C;">
                                <?= $total_incidencias ?>
                            </div>
                            <div class="kpi-label">Incidencias</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Fila 1 -->
                <div class="row g-3 mb-3">
                    <!-- Gráfico Asistencia -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-calendar-check me-2"></i>
                                Asistencia Diaria
                            </h5>
                            <?php if (!empty($asistencia_por_dia)): ?>
                                <div class="chart-container">
                                    <canvas id="chartAsistencia"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-line"></i>
                                    <p>No hay datos de asistencia en el período seleccionado</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Gráfico Entregas -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-file-check me-2"></i>
                                Entregas de Tareas
                            </h5>
                            <?php if (!empty($entregas_tareas)): ?>
                                <div class="chart-container">
                                    <canvas id="chartEntregas"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-file-x"></i>
                                    <p>No hay entregas de tareas en el período seleccionado</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Fila 2 -->
                <div class="row g-3 mb-3">
                    <!-- Gráfico Áreas -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-book me-2"></i>
                                Promedio por Área Curricular
                            </h5>
                            <?php if (!empty($desempeno_por_area)): ?>
                                <div class="chart-container">
                                    <canvas id="chartAreas"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-bar"></i>
                                    <p>No hay datos de áreas curriculares</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Gráfico Estudiantes por Grado -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-users-group me-2"></i>
                                Distribución de Estudiantes por Grado
                            </h5>
                            <?php if (!empty($estudiantes_por_grado)): ?>
                                <div class="chart-container">
                                    <canvas id="chartEstudiantesGrado"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-user-x"></i>
                                    <p>No hay estudiantes matriculados</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabla Resumen Docentes -->
                <div class="chart-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="chart-title mb-0">
                            <i class="ti ti-report me-2"></i>
                            Resumen de Desempeño por Docente
                        </h5>
                        <div class="export-buttons">
                            <button onclick="exportarPDF()" class="btn btn-export btn-export-pdf">
                                <i class="ti ti-file-type-pdf me-1"></i>Exportar PDF
                            </button>
                            <!-- <button onclick="exportarExcel()" class="btn btn-export btn-export-excel">
                                <i class="ti ti-file-spreadsheet me-1"></i>Exportar Excel
                            </button> -->
                        </div>
                    </div>

                    <?php if (!empty($resumen_docentes)): ?>
                        <div class="table-wrapper">
                            <table class="table table-modern" id="tablaDocentes">
                                <thead>
                                    <tr>
                                        <th>Docente</th>
                                        <th>Asignaciones</th>
                                        <th>Estudiantes Evaluados</th>
                                        <th>Promedio Calificaciones</th>
                                        <th>Tareas Creadas</th>
                                        <th>Entregas Recibidas</th>
                                        <th>Rendimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resumen_docentes as $docente): 
                                        $promedio = round($docente['promedio_calificaciones'] ?? 0, 1);
                                        $badge_class = 'badge-low';
                                        $badge_text = 'Sin Datos';
                                        
                                        if ($promedio >= 16) {
                                            $badge_class = 'badge-excellent';
                                            $badge_text = 'Excelente';
                                        } elseif ($promedio >= 14) {
                                            $badge_class = 'badge-good';
                                            $badge_text = 'Bueno';
                                        } elseif ($promedio >= 11) {
                                            $badge_class = 'badge-regular';
                                            $badge_text = 'Regular';
                                        } elseif ($promedio > 0) {
                                            $badge_class = 'badge-low';
                                            $badge_text = 'Bajo';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($docente['nombre_completo']) ?></div>
                                            </td>
                                            <td><span class="badge bg-info"><?= $docente['asignaciones'] ?></span></td>
                                            <td><?= $docente['estudiantes_evaluados'] ?></td>
                                            <td>
                                                <strong style="color: #01579B;"><?= $promedio ?></strong>
                                            </td>
                                            <td><?= $docente['tareas_creadas'] ?></td>
                                            <td><?= $docente['entregas_recibidas'] ?></td>
                                            <td>
                                                <span class="badge-performance <?= $badge_class ?>">
                                                    <?= $badge_text ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="ti ti-user-off"></i>
                            <p>No hay datos de docentes disponibles</p>
                        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <script>
        // Datos de PHP a JavaScript
        const dataAsistencia = <?= $json_asistencia ?>;
        const dataAreas = <?= $json_areas ?>;
        const dataEntregas = <?= $json_entregas ?>;
        const dataEstudiantesGrado = <?= $json_estudiantes_grado ?>;

        // Colores pastel para los gráficos
        const coloresPastel = [
            '#B3E5FC', '#C8E6C9', '#FFE5B4', '#F8BBD0', 
            '#E1BEE7', '#FFCCBC', '#D1C4E9', '#B2DFDB'
        ];

        // ========== GRÁFICO 1: ASISTENCIA DIARIA ==========
        <?php if (!empty($asistencia_por_dia)): ?>
        const ctxAsistencia = document.getElementById('chartAsistencia');
        if (ctxAsistencia) {
            new Chart(ctxAsistencia, {
                type: 'line',
                data: {
                    labels: dataAsistencia.map(d => {
                        const fecha = new Date(d.fecha);
                        return fecha.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' });
                    }),
                    datasets: [
                        {
                            label: 'Presentes',
                            data: dataAsistencia.map(d => d.presentes),
                            borderColor: '#C8E6C9',
                            backgroundColor: 'rgba(200, 230, 201, 0.2)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Ausentes',
                            data: dataAsistencia.map(d => d.ausentes),
                            borderColor: '#FFCCBC',
                            backgroundColor: 'rgba(255, 204, 188, 0.2)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Tardanzas',
                            data: dataAsistencia.map(d => d.tardanzas),
                            borderColor: '#FFE5B4',
                            backgroundColor: 'rgba(255, 229, 180, 0.2)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'top',
                            labels: { font: { size: 12 } }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 2: ENTREGAS DE TAREAS ==========
        <?php if (!empty($entregas_tareas)): ?>
        const ctxEntregas = document.getElementById('chartEntregas');
        if (ctxEntregas) {
            new Chart(ctxEntregas, {
                type: 'bar',
                data: {
                    labels: dataEntregas.map(d => {
                        const fecha = new Date(d.fecha);
                        return fecha.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' });
                    }),
                    datasets: [{
                        label: 'Total Entregas',
                        data: dataEntregas.map(d => d.total_entregas),
                        backgroundColor: '#B3E5FC',
                        borderColor: '#81D4FA',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 3: PROMEDIO POR ÁREA ==========
        <?php if (!empty($desempeno_por_area)): ?>
        const ctxAreas = document.getElementById('chartAreas');
        if (ctxAreas) {
            new Chart(ctxAreas, {
                type: 'bar',
                data: {
                    labels: dataAreas.map(d => d.area),
                    datasets: [{
                        label: 'Promedio de Notas',
                        data: dataAreas.map(d => d.promedio_notas ? parseFloat(d.promedio_notas).toFixed(1) : 0),
                        backgroundColor: coloresPastel,
                        borderColor: coloresPastel.map(c => c.replace('0.6', '1')),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true,
                            max: 20,
                            ticks: { stepSize: 2 }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 4: ESTUDIANTES POR GRADO ==========
        <?php if (!empty($estudiantes_por_grado)): ?>
        const ctxEstudiantesGrado = document.getElementById('chartEstudiantesGrado');
        if (ctxEstudiantesGrado) {
            new Chart(ctxEstudiantesGrado, {
                type: 'doughnut',
                data: {
                    labels: dataEstudiantesGrado.map(d => `${d.grado} ${d.nivel}`),
                    datasets: [{
                        data: dataEstudiantesGrado.map(d => d.total_estudiantes),
                        backgroundColor: coloresPastel,
                        borderColor: '#FFFFFF',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { 
                                font: { size: 11 },
                                padding: 10
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== FUNCIONES DE EXPORTACIÓN ==========
        function exportarPDF() {
            Swal.fire({
                title: 'Exportando a PDF',
                text: 'Generando documento...',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                Swal.fire({
                    title: 'Funcionalidad en desarrollo',
                    text: 'La exportación a PDF estará disponible próximamente',
                    icon: 'info',
                    confirmButtonColor: '#01579B'
                });
            });
        }

        function exportarExcel() {
            Swal.fire({
                title: 'Exportando a Excel',
                text: 'Generando archivo...',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                Swal.fire({
                    title: 'Funcionalidad en desarrollo',
                    text: 'La exportación a Excel estará disponible próximamente',
                    icon: 'info',
                    confirmButtonColor: '#1B5E20'
                });
            });
        }
    </script>
</body>
</html>