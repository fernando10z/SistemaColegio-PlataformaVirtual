<?php
session_start();
require_once 'conexion/bd.php';

// Verificar sesión y rol de director
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../index.php');
    exit();
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
$usuario_nombre = $_SESSION['username'];

// ==================== FILTROS ====================
$filtro_nivel = $_GET['nivel'] ?? '';
$filtro_grado = $_GET['grado'] ?? '';
$filtro_seccion = $_GET['seccion'] ?? '';
$filtro_estado = $_GET['estado'] ?? 'activo';

// ==================== KPI 1: TOTAL ESTUDIANTES ====================
try {
    $sql = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $total_estudiantes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_estudiantes = 0;
}

// ==================== KPI 2: ESTUDIANTES CON MATRÍCULA ACTIVA ====================
try {
    $sql = "SELECT COUNT(DISTINCT estudiante_id) as total 
            FROM matriculas 
            WHERE activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $estudiantes_matriculados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $estudiantes_matriculados = 0;
}

// ==================== KPI 3: PROMEDIO GENERAL ====================
try {
    $sql = "SELECT AVG(calificacion) as promedio 
            FROM calificaciones 
            WHERE MONTH(fecha_registro) = MONTH(CURDATE())
            AND YEAR(fecha_registro) = YEAR(CURDATE())";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $promedio_general = round($stmt->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0, 1);
} catch (PDOException $e) {
    $promedio_general = 0;
}

// ==================== KPI 4: ASISTENCIA PROMEDIO ====================
try {
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'PRESENTE' THEN 1 ELSE 0 END) as presentes
            FROM asistencias 
            WHERE MONTH(fecha) = MONTH(CURDATE())
            AND YEAR(fecha) = YEAR(CURDATE())";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $asistencia_promedio = $data['total'] > 0 
        ? round(($data['presentes'] / $data['total']) * 100, 1) 
        : 0;
} catch (PDOException $e) {
    $asistencia_promedio = 0;
}

// ==================== KPI 5: ESTUDIANTES EN RIESGO ====================
try {
    $sql = "SELECT COUNT(DISTINCT e.id) as total
            FROM estudiantes e
            LEFT JOIN calificaciones c ON e.id = c.estudiante_id
            WHERE e.activo = 1
            AND c.calificacion < 11
            AND MONTH(c.fecha_registro) = MONTH(CURDATE())";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $estudiantes_riesgo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $estudiantes_riesgo = 0;
}

// ==================== KPI 6: INCIDENCIAS DEL MES ====================
try {
    $sql = "SELECT COUNT(*) as total 
            FROM incidencias_disciplinarias 
            WHERE MONTH(fecha_incidencia) = MONTH(CURDATE())
            AND YEAR(fecha_incidencia) = YEAR(CURDATE())";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $incidencias_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $incidencias_mes = 0;
}

// ==================== GRÁFICO 1: DISTRIBUCIÓN POR NIVEL ====================
try {
    $sql = "SELECT 
                n.nombre as nivel,
                COUNT(DISTINCT m.estudiante_id) as total
            FROM niveles_educativos n
            LEFT JOIN secciones s ON n.id = s.nivel_id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1
            WHERE n.activo = 1
            GROUP BY n.id, n.nombre, n.orden
            ORDER BY n.orden";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $distribucion_nivel = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $distribucion_nivel = [];
}

// ==================== GRÁFICO 2: RENDIMIENTO POR GRADO ====================
try {
    $sql = "SELECT 
                CONCAT(s.grado, ' - ', n.nombre) as grado_nivel,
                AVG(c.calificacion) as promedio,
                COUNT(DISTINCT c.estudiante_id) as total_estudiantes
            FROM secciones s
            INNER JOIN niveles_educativos n ON s.nivel_id = n.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1
            LEFT JOIN calificaciones c ON m.estudiante_id = c.estudiante_id
                AND MONTH(c.fecha_registro) = MONTH(CURDATE())
            WHERE s.activo = 1
            GROUP BY s.id, s.grado, n.nombre, n.orden
            ORDER BY n.orden, s.grado";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $rendimiento_grado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rendimiento_grado = [];
}

// ==================== GRÁFICO 3: ASISTENCIA SEMANAL ====================
try {
    $sql = "SELECT 
                DATE(fecha) as fecha,
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'PRESENTE' THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN estado = 'AUSENTE' THEN 1 ELSE 0 END) as ausentes,
                SUM(CASE WHEN estado = 'TARDANZA' THEN 1 ELSE 0 END) as tardanzas
            FROM asistencias
            WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(fecha)
            ORDER BY fecha ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $asistencia_semanal = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asistencia_semanal = [];
}

// ==================== GRÁFICO 4: INCIDENCIAS POR TIPO ====================
try {
    $sql = "SELECT 
                gravedad,
                COUNT(*) as total
            FROM incidencias_disciplinarias
            WHERE MONTH(fecha_incidencia) = MONTH(CURDATE())
            AND YEAR(fecha_incidencia) = YEAR(CURDATE())
            GROUP BY gravedad";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $incidencias_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $incidencias_tipo = [];
}

// ==================== TABLA: LISTADO COMPLETO DE ESTUDIANTES ====================
try {
    $sql = "SELECT 
                e.id,
                e.codigo_estudiante,
                e.nombres,
                e.apellidos,
                e.foto_url,
                e.documento_numero,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                m.fecha_matricula,
                m.activo as matricula_activa,
                (SELECT AVG(c.calificacion) 
                 FROM calificaciones c 
                 WHERE c.estudiante_id = e.id 
                 AND MONTH(c.fecha_registro) = MONTH(CURDATE())) as promedio_mes,
                (SELECT COUNT(*) 
                 FROM asistencias a 
                 WHERE a.estudiante_id = e.id 
                 AND a.estado = 'AUSENTE'
                 AND MONTH(a.fecha) = MONTH(CURDATE())) as inasistencias_mes,
                (SELECT COUNT(*) 
                 FROM incidencias_disciplinarias i 
                 WHERE i.estudiante_id = e.id 
                 AND MONTH(i.fecha_incidencia) = MONTH(CURDATE())) as incidencias_mes,
                (SELECT COUNT(*) 
                 FROM atenciones_medicas am 
                 WHERE am.estudiante_id = e.id 
                 AND MONTH(am.fecha_atencion) = MONTH(CURDATE())) as atenciones_mes
            FROM estudiantes e
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            WHERE e.activo = 1";
    
    // Aplicar filtros
    if (!empty($filtro_nivel)) {
        $sql .= " AND n.id = :nivel";
    }
    if (!empty($filtro_grado)) {
        $sql .= " AND s.grado = :grado";
    }
    if (!empty($filtro_seccion)) {
        $sql .= " AND s.seccion = :seccion";
    }
    
    $sql .= " ORDER BY n.orden, s.grado, s.seccion, e.apellidos, e.nombres";
    
    $stmt = $conexion->prepare($sql);
    
    if (!empty($filtro_nivel)) {
        $stmt->bindParam(':nivel', $filtro_nivel);
    }
    if (!empty($filtro_grado)) {
        $stmt->bindParam(':grado', $filtro_grado);
    }
    if (!empty($filtro_seccion)) {
        $stmt->bindParam(':seccion', $filtro_seccion);
    }
    
    $stmt->execute();
    $listado_estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $listado_estudiantes = [];
}

// ==================== OBTENER NIVELES PARA FILTRO ====================
try {
    $stmt = $conexion->prepare("SELECT id, nombre FROM niveles_educativos WHERE activo = 1 ORDER BY orden");
    $stmt->execute();
    $niveles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $niveles = [];
}

// ==================== OBTENER GRADOS PARA FILTRO ====================
try {
    $stmt = $conexion->prepare("SELECT DISTINCT grado FROM secciones WHERE activo = 1 ORDER BY grado");
    $stmt->execute();
    $grados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $grados = [];
}

// ==================== OBTENER SECCIONES PARA FILTRO ====================
try {
    $stmt = $conexion->prepare("SELECT DISTINCT seccion FROM secciones WHERE activo = 1 ORDER BY seccion");
    $stmt->execute();
    $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $secciones = [];
}

// ==================== CONVERTIR A JSON ====================
$json_nivel = json_encode($distribucion_nivel);
$json_rendimiento = json_encode($rendimiento_grado);
$json_asistencia = json_encode($asistencia_semanal);
$json_incidencias = json_encode($incidencias_tipo);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Estudiantes - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
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
        .kpi-card.kpi-5 { border-left-color: #FFCCBC; }
        .kpi-card.kpi-6 { border-left-color: #E1BEE7; }

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
            font-size: 0.85rem;
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
            font-size: 0.9rem;
        }

        .foto-estudiante {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #E5F0FF;
        }

        .badge-status {
            padding: 0.3rem 0.7rem;
            border-radius: 10px;
            font-size: 0.75rem;
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

        .badge-alert {
            background: #FFCCBC;
            color: #BF360C;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .alert-icon {
            color: #BF360C;
            font-size: 1.2rem;
        }

        .student-name {
            font-weight: 600;
            color: #333;
        }

        .student-code {
            font-size: 0.8rem;
            color: #666;
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

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            border: none;
            font-size: 0.8rem;
            transition: all 0.3s;
        }

        .btn-view {
            background: #E1BEE7;
            color: #4A148C;
        }

        .btn-view:hover {
            background: #CE93D8;
            color: #4A148C;
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
                                <i class="ti ti-users me-2"></i>
                                Dashboard de Estudiantes
                            </h3>
                            <p class="mb-0 text-muted">Información completa del alumnado institucional</p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-1"><?= htmlspecialchars($usuario_nombre) ?></h5>
                            <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-1 position-relative">
                            <i class="ti ti-users kpi-icon"></i>
                            <div class="kpi-value" style="color: #01579B;">
                                <?= $total_estudiantes ?>
                            </div>
                            <div class="kpi-label">Total Estudiantes</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-2 position-relative">
                            <i class="ti ti-user-check kpi-icon"></i>
                            <div class="kpi-value" style="color: #1B5E20;">
                                <?= $estudiantes_matriculados ?>
                            </div>
                            <div class="kpi-label">Matriculados</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-3 position-relative">
                            <i class="ti ti-star kpi-icon"></i>
                            <div class="kpi-value" style="color: #E65100;">
                                <?= $promedio_general ?>
                            </div>
                            <div class="kpi-label">Promedio General</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-4 position-relative">
                            <i class="ti ti-calendar-check kpi-icon"></i>
                            <div class="kpi-value" style="color: #880E4F;">
                                <?= $asistencia_promedio ?>%
                            </div>
                            <div class="kpi-label">Asistencia</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-5 position-relative">
                            <i class="ti ti-alert-circle kpi-icon"></i>
                            <div class="kpi-value" style="color: #BF360C;">
                                <?= $estudiantes_riesgo ?>
                            </div>
                            <div class="kpi-label">En Riesgo</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="kpi-card kpi-6 position-relative">
                            <i class="ti ti-alert-triangle kpi-icon"></i>
                            <div class="kpi-value" style="color: #4A148C;">
                                <?= $incidencias_mes ?>
                            </div>
                            <div class="kpi-label">Incidencias</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Fila 1 -->
                <div class="row g-3 mb-3">
                    <!-- Distribución por Nivel -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-pie me-2"></i>
                                Distribución por Nivel Educativo
                            </h5>
                            <?php if (!empty($distribucion_nivel)): ?>
                                <div class="chart-container">
                                    <canvas id="chartNivel"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-pie"></i>
                                    <p>No hay datos de distribución disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Rendimiento por Grado -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-bar me-2"></i>
                                Promedio Académico por Grado
                            </h5>
                            <?php if (!empty($rendimiento_grado)): ?>
                                <div class="chart-container">
                                    <canvas id="chartRendimiento"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-bar"></i>
                                    <p>No hay datos de rendimiento disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Fila 2 -->
                <div class="row g-3 mb-3">
                    <!-- Asistencia Semanal -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-calendar-stats me-2"></i>
                                Asistencia de los Últimos 7 Días
                            </h5>
                            <?php if (!empty($asistencia_semanal)): ?>
                                <div class="chart-container">
                                    <canvas id="chartAsistencia"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-calendar-x"></i>
                                    <p>No hay datos de asistencia disponibles</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Incidencias por Tipo -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-alert-triangle me-2"></i>
                                Incidencias Disciplinarias del Mes
                            </h5>
                            <?php if (!empty($incidencias_tipo)): ?>
                                <div class="chart-container">
                                    <canvas id="chartIncidencias"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-mood-smile"></i>
                                    <p>No hay incidencias registradas este mes</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-card">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nivel</label>
                            <select name="nivel" class="form-select">
                                <option value="">Todos los niveles</option>
                                <?php foreach ($niveles as $nivel): ?>
                                    <option value="<?= $nivel['id'] ?>" <?= $filtro_nivel == $nivel['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Grado</label>
                            <select name="grado" class="form-select">
                                <option value="">Todos los grados</option>
                                <?php foreach ($grados as $grado): ?>
                                    <option value="<?= $grado['grado'] ?>" <?= $filtro_grado == $grado['grado'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($grado['grado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Sección</label>
                            <select name="seccion" class="form-select">
                                <option value="">Todas las secciones</option>
                                <?php foreach ($secciones as $seccion): ?>
                                    <option value="<?= $seccion['seccion'] ?>" <?= $filtro_seccion == $seccion['seccion'] ? 'selected' : '' ?>>
                                        Sección <?= htmlspecialchars($seccion['seccion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-filter w-100">
                                <i class="ti ti-filter me-2"></i>Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Estudiantes -->
                <div class="chart-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="chart-title mb-0">
                            <i class="ti ti-list me-2"></i>
                            Listado Completo de Estudiantes
                            <span class="badge bg-primary ms-2"><?= count($listado_estudiantes) ?></span>
                        </h5>
                        <div class="export-buttons">
                            <button onclick="exportarPDF()" class="btn btn-export btn-export-pdf">
                                <i class="ti ti-file-type-pdf me-1"></i>PDF
                            </button>
                            <button onclick="exportarExcel()" class="btn btn-export btn-export-excel">
                                <i class="ti ti-file-spreadsheet me-1"></i>Excel
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($listado_estudiantes)): ?>
                        <div class="table-wrapper">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Estudiante</th>
                                        <th>Grado/Sección</th>
                                        <th>Promedio</th>
                                        <th>Asistencia</th>
                                        <th>Inasistencias</th>
                                        <th>Incidencias</th>
                                        <th>Atenciones</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listado_estudiantes as $estudiante): 
                                        $promedio = round($estudiante['promedio_mes'] ?? 0, 1);
                                        $inasistencias = $estudiante['inasistencias_mes'] ?? 0;
                                        
                                        // Determinar badge de promedio
                                        $badge_promedio = 'badge-low';
                                        if ($promedio >= 16) {
                                            $badge_promedio = 'badge-excellent';
                                        } elseif ($promedio >= 14) {
                                            $badge_promedio = 'badge-good';
                                        } elseif ($promedio >= 11) {
                                            $badge_promedio = 'badge-regular';
                                        }
                                        
                                        // Alertas
                                        $tiene_alerta = $promedio > 0 && $promedio < 11;
                                        $alerta_inasistencia = $inasistencias > 3;
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="student-code">
                                                    <?= htmlspecialchars($estudiante['codigo_estudiante']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= !empty($estudiante['foto_url']) ? $estudiante['foto_url'] : '../assets/images/profile/user-1.jpg' ?>" 
                                                         class="foto-estudiante" alt="Foto">
                                                    <div>
                                                        <div class="student-name">
                                                            <?= htmlspecialchars($estudiante['apellidos']) ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($estudiante['nombres']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($estudiante['grado'] ?? 'S/G') ?> - 
                                                    <?= htmlspecialchars($estudiante['seccion'] ?? 'S/S') ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($estudiante['nivel_nombre'] ?? 'Sin nivel') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($promedio > 0): ?>
                                                    <span class="badge-status <?= $badge_promedio ?>">
                                                        <?= $promedio ?>
                                                    </span>
                                                    <?php if ($tiene_alerta): ?>
                                                        <i class="ti ti-alert-circle alert-icon ms-1" title="Bajo rendimiento"></i>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $porcentaje_asist = $inasistencias > 0 
                                                    ? round((1 - ($inasistencias / 20)) * 100, 0) 
                                                    : 100;
                                                ?>
                                                <span class="badge bg-<?= $porcentaje_asist >= 90 ? 'success' : ($porcentaje_asist >= 80 ? 'warning' : 'danger') ?>">
                                                    <?= $porcentaje_asist ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($alerta_inasistencia): ?>
                                                    <span class="badge-status badge-alert">
                                                        <?= $inasistencias ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= $inasistencias ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $incidencias = $estudiante['incidencias_mes'] ?? 0;
                                                if ($incidencias > 0): ?>
                                                    <span class="badge bg-warning"><?= $incidencias ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $atenciones = $estudiante['atenciones_mes'] ?? 0;
                                                if ($atenciones > 0): ?>
                                                    <span class="badge bg-info"><?= $atenciones ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($estudiante['matricula_activa']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-action btn-view btn-sm" 
                                                        onclick="verDetalleEstudiante(<?= $estudiante['id'] ?>)"
                                                        title="Ver Detalle">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="ti ti-user-off"></i>
                            <p>No se encontraron estudiantes con los filtros seleccionados</p>
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
        const dataNivel = <?= $json_nivel ?>;
        const dataRendimiento = <?= $json_rendimiento ?>;
        const dataAsistencia = <?= $json_asistencia ?>;
        const dataIncidencias = <?= $json_incidencias ?>;

        // Colores pastel
        const coloresPastel = [
            '#B3E5FC', '#C8E6C9', '#FFE5B4', '#F8BBD0', 
            '#E1BEE7', '#FFCCBC', '#D1C4E9', '#B2DFDB'
        ];

        // ========== GRÁFICO 1: DISTRIBUCIÓN POR NIVEL ==========
        <?php if (!empty($distribucion_nivel)): ?>
        const ctxNivel = document.getElementById('chartNivel');
        if (ctxNivel) {
            new Chart(ctxNivel, {
                type: 'doughnut',
                data: {
                    labels: dataNivel.map(d => d.nivel),
                    datasets: [{
                        data: dataNivel.map(d => d.total),
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
                                font: { size: 12 },
                                padding: 15
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 2: RENDIMIENTO POR GRADO ==========
        <?php if (!empty($rendimiento_grado)): ?>
        const ctxRendimiento = document.getElementById('chartRendimiento');
        if (ctxRendimiento) {
            new Chart(ctxRendimiento, {
                type: 'bar',
                data: {
                    labels: dataRendimiento.map(d => d.grado_nivel),
                    datasets: [{
                        label: 'Promedio',
                        data: dataRendimiento.map(d => d.promedio ? parseFloat(d.promedio).toFixed(1) : 0),
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
                            max: 20,
                            ticks: { stepSize: 2 }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 3: ASISTENCIA SEMANAL ==========
        <?php if (!empty($asistencia_semanal)): ?>
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
                            ticks: { stepSize: 5 }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 4: INCIDENCIAS ==========
        <?php if (!empty($incidencias_tipo)): ?>
        const ctxIncidencias = document.getElementById('chartIncidencias');
        if (ctxIncidencias) {
            new Chart(ctxIncidencias, {
                type: 'bar',
                data: {
                    labels: dataIncidencias.map(d => d.gravedad),
                    datasets: [{
                        label: 'Cantidad',
                        data: dataIncidencias.map(d => d.total),
                        backgroundColor: ['#FFE5B4', '#FFCCBC', '#F8BBD0'],
                        borderColor: ['#FFC107', '#FF5722', '#E91E63'],
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

        // ========== FUNCIONES ==========
        function verDetalleEstudiante(id) {
            Swal.fire({
                title: 'Cargando información...',
                text: 'Por favor espere',
                icon: 'info',
                showConfirmButton: false,
                timer: 1000
            }).then(() => {
                window.location.href = `perfil_estudiante.php?id=${id}`;
            });
        }

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