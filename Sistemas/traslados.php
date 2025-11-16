<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
} 
require_once 'conexion/bd.php';

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

// Obtener período académico actual
try {
    $stmt_periodo = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 AND actual = 1 LIMIT 1");
    $stmt_periodo->execute();
    $periodo_actual = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodo_actual = null;
}

// Obtener secciones con información de estudiantes matriculados
try {
    $sql = "SELECT s.*, 
                   ne.nombre as nivel_nombre,
                   COUNT(CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN 1 END) as estudiantes_actuales,
                   ROUND((COUNT(CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN 1 END) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion,
                   CASE 
                       WHEN COUNT(CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN 1 END) = 0 THEN 'VACIA'
                       WHEN COUNT(CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN 1 END) < s.capacidad_maxima THEN 'DISPONIBLE'
                       WHEN COUNT(CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN 1 END) >= s.capacidad_maxima THEN 'COMPLETA'
                   END as estado_capacidad
            FROM secciones s
            LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.periodo_academico_id = ?
            WHERE s.activo = 1 " . ($periodo_actual ? "AND s.periodo_academico_id = ?" : "") . "
            GROUP BY s.id
            ORDER BY ne.orden ASC, s.grado ASC, s.seccion ASC";
    
    $params = [$periodo_actual['id'] ?? 1];
    if ($periodo_actual) {
        $params[] = $periodo_actual['id'];
    }
    
    $stmt_secciones = $conexion->prepare($sql);
    $stmt_secciones->execute($params);
    $secciones = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $secciones = [];
    $error_secciones = "Error al cargar secciones: " . $e->getMessage();
}

// Obtener estudiantes matriculados con información completa
try {
    $sql = "SELECT m.*, 
                   e.nombres, e.apellidos, e.codigo_estudiante, e.documento_numero,
                   e.fecha_nacimiento, e.genero,
                   s.grado, s.seccion, s.aula_asignada,
                   ne.nombre as nivel_nombre,
                   CONCAT(a.nombres, ' ', a.apellidos) as apoderado_nombre,
                   JSON_UNQUOTE(JSON_EXTRACT(a.datos_personales, '$.telefono')) as apoderado_telefono
            FROM matriculas m
            INNER JOIN estudiantes e ON m.estudiante_id = e.id
            INNER JOIN secciones s ON m.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            LEFT JOIN estudiante_apoderados ea ON e.id = ea.estudiante_id AND ea.es_principal = 1
            LEFT JOIN apoderados a ON ea.apoderado_id = a.id
            WHERE m.estado = 'MATRICULADO' AND m.activo = 1 
                  AND m.periodo_academico_id = ?
            ORDER BY e.apellidos ASC, e.nombres ASC";
    
    $stmt_estudiantes = $conexion->prepare($sql);
    $stmt_estudiantes->execute([$periodo_actual['id'] ?? 1]);
    $estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes = [];
    error_log("Error al cargar estudiantes: " . $e->getMessage());
}
// Agrupar estudiantes por sección
$estudiantes_por_seccion = [];
foreach ($estudiantes as $estudiante) {
    $seccion_id = $estudiante['seccion_id'];
    if (!isset($estudiantes_por_seccion[$seccion_id])) {
        $estudiantes_por_seccion[$seccion_id] = [];
    }
    $estudiantes_por_seccion[$seccion_id][] = $estudiante;
}

// Obtener niveles para filtros
try {
    $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
    $stmt_niveles->execute();
    $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $niveles = [];
}

// Calcular estadísticas
$total_secciones = count($secciones);
$secciones_completas = count(array_filter($secciones, function($s) { return $s['estado_capacidad'] === 'COMPLETA'; }));
$secciones_vacias = count(array_filter($secciones, function($s) { return $s['estado_capacidad'] === 'VACIA'; }));
$total_estudiantes = count($estudiantes);
$capacidad_total = array_sum(array_column($secciones, 'capacidad_maxima'));
$ocupacion_general = $capacidad_total > 0 ? round(($total_estudiantes / $capacidad_total) * 100, 1) : 0;

// Obtener historial de traslados recientes
try {
    $sql = "SELECT t.*, 
                   e.nombres, e.apellidos, e.codigo_estudiante,
                   so.grado as grado_origen, so.seccion as seccion_origen,
                   sd.grado as grado_destino, sd.seccion as seccion_destino,
                   u.nombres as usuario_nombre
            FROM (
                SELECT m.estudiante_id, m.seccion_id, m.fecha_actualizacion, m.id,
                       LAG(m.seccion_id) OVER (PARTITION BY m.estudiante_id ORDER BY m.fecha_actualizacion) as seccion_anterior,
                       ROW_NUMBER() OVER (PARTITION BY m.estudiante_id ORDER BY m.fecha_actualizacion DESC) as rn
                FROM matriculas m 
                WHERE m.periodo_academico_id = ? AND m.fecha_actualizacion > DATE_SUB(NOW(), INTERVAL 30 DAY)
            ) t
            INNER JOIN estudiantes e ON t.estudiante_id = e.id
            INNER JOIN secciones sd ON t.seccion_id = sd.id
            LEFT JOIN secciones so ON t.seccion_anterior = so.id
            LEFT JOIN usuarios u ON 1=1  -- Placeholder for user tracking
            WHERE t.seccion_anterior IS NOT NULL AND t.rn <= 3
            ORDER BY t.fecha_actualizacion DESC
            LIMIT 10";
    
    $stmt_traslados = $conexion->prepare($sql);
    $stmt_traslados->execute([$periodo_actual['id'] ?? 1]);
    $traslados_recientes = $stmt_traslados->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $traslados_recientes = [];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Traslados - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.css" />
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
        .seccion-card {
            min-height: 400px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .seccion-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.1);
        }
        .seccion-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .seccion-nombre {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .seccion-info {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .capacidad-bar {
            width: 100%;
            height: 8px;
            background-color: rgba(255,255,255,0.3);
            border-radius: 4px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        .capacidad-fill {
            height: 100%;
            background-color: rgba(255,255,255,0.8);
            transition: width 0.3s ease;
        }
        .estudiantes-container {
            padding: 1rem;
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
        }
        .estudiante-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: move;
            transition: all 0.2s ease;
        }
        .estudiante-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .estudiante-item.gu-transit {
            background: #fff3cd !important;
            border-color: #ffc107 !important;
            transform: rotate(5deg);
        }
        .estudiante-item.gu-mirror {
            background: #d1ecf1 !important;
            border-color: #0dcaf0 !important;
            opacity: 0.8;
        }
        .estudiante-nombre {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }
        .estudiante-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .seccion-completa {
            border-color: #dc3545 !important;
        }
        .seccion-completa .seccion-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }
        .seccion-vacia {
            border-style: dashed;
            opacity: 0.7;
        }
        .drop-zone {
            border: 2px dashed #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .card-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .traslado-item {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 0 0.375rem 0.375rem 0;
        }
        .vista-toggle {
            border-radius: 25px;
        }
        .vista-toggle.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .seccion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .nivel-section {
            margin-bottom: 2rem;
        }
        .nivel-title {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #1976d2;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper" style="top: 20px;">
            <div class="container-fluid">
                
                <!-- Header -->
                <?php include 'includes/header.php'; ?>

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-0">Gestión de Traslados</h4>
                                <p class="mb-0 text-muted">Cambio de estudiantes entre secciones - Período: <?= htmlspecialchars($periodo_actual['nombre'] ?? 'No definido') ?></p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="btn-group vista-toggle" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="vistaSecciones" onclick="cambiarVista('secciones')">
                                        <i class="ti ti-layout-grid me-1"></i> Vista Secciones
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="vistaTabla" onclick="cambiarVista('tabla')">
                                        <i class="ti ti-table me-1"></i> Vista Tabla
                                    </button>
                                </div>
                                <button type="button" class="btn btn-outline-info" onclick="verHistorialTraslados()">
                                    <i class="ti ti-history me-2"></i>
                                    Historial
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTrasladoManual">
                                    <i class="ti ti-transfer me-2"></i>
                                    Traslado Manual
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Nivel</label>
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos los niveles</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado Capacidad</label>
                                <select class="form-select" id="filtroCapacidad">
                                    <option value="">Todas</option>
                                    <option value="VACIA">Vacías</option>
                                    <option value="DISPONIBLE">Disponibles</option>
                                    <option value="COMPLETA">Completas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar Estudiante</label>
                                <input type="text" class="form-control" id="buscarEstudiante" placeholder="Nombre o código de estudiante...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" title="Limpiar Filtros" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" title="Actualizar Vista" onclick="actualizarVista()">
                                        <i class="ti ti-reload"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarTraslados()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Secciones (Drag & Drop) -->
                <div id="contenedorSecciones">
                    <?php 
                    // Agrupar secciones por nivel
                    $secciones_por_nivel = [];
                    foreach ($secciones as $seccion) {
                        $nivel = $seccion['nivel_nombre'];
                        if (!isset($secciones_por_nivel[$nivel])) {
                            $secciones_por_nivel[$nivel] = [];
                        }
                        $secciones_por_nivel[$nivel][] = $seccion;
                    }
                    ?>
                    
                    <?php foreach ($secciones_por_nivel as $nivel => $secciones_nivel): ?>
                        <div class="nivel-section" data-nivel="<?= $nivel ?>">
                            <div class="nivel-title">
                                <i class="ti ti-school me-2"></i>
                                <?= htmlspecialchars($nivel) ?>
                                <span class="badge bg-primary ms-2"><?= count($secciones_nivel) ?> secciones</span>
                            </div>
                            
                            <div class="seccion-grid">
                                <?php foreach ($secciones_nivel as $seccion): ?>
                                    <div class="seccion-card card <?= $seccion['estado_capacidad'] === 'COMPLETA' ? 'seccion-completa' : '' ?> <?= $seccion['estado_capacidad'] === 'VACIA' ? 'seccion-vacia' : '' ?>" 
                                         data-seccion-id="<?= $seccion['id'] ?>" data-capacidad="<?= $seccion['capacidad_maxima'] ?>">
                                        
                                        <div class="seccion-header">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="seccion-nombre">
                                                        <?= htmlspecialchars($seccion['grado']) ?> - Sección <?= htmlspecialchars($seccion['seccion']) ?>
                                                    </div>
                                                    <div class="seccion-info">
                                                        <i class="ti ti-door me-1"></i>
                                                        <?= htmlspecialchars($seccion['aula_asignada'] ?: 'Sin aula') ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold">
                                                        <?= $seccion['estudiantes_actuales'] ?>/<?= $seccion['capacidad_maxima'] ?>
                                                    </div>
                                                    <div class="small"><?= $seccion['porcentaje_ocupacion'] ?>%</div>
                                                </div>
                                            </div>
                                            <div class="capacidad-bar">
                                                <div class="capacidad-fill" style="width: <?= min($seccion['porcentaje_ocupacion'], 100) ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="estudiantes-container" data-seccion-id="<?= $seccion['id'] ?>">
                                            <?php if (isset($estudiantes_por_seccion[$seccion['id']])): ?>
                                                <?php foreach ($estudiantes_por_seccion[$seccion['id']] as $estudiante): ?>
                                                    <div class="estudiante-item" 
                                                         data-estudiante-id="<?= $estudiante['estudiante_id'] ?>"
                                                         data-matricula-id="<?= $estudiante['id'] ?>">
                                                        <div class="estudiante-nombre">
                                                            <?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?>
                                                        </div>
                                                        <div class="estudiante-info">
                                                            <i class="ti ti-id-badge me-1"></i>
                                                            <?= htmlspecialchars($estudiante['codigo_estudiante']) ?>
                                                            | 
                                                            <i class="ti ti-calendar me-1"></i>
                                                            <?= date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) ?>
                                                            <?php if ($estudiante['apoderado_telefono']): ?>
                                                                | <i class="ti ti-phone me-1"></i>
                                                                <?= htmlspecialchars($estudiante['apoderado_telefono']) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="text-center text-muted py-5">
                                                    <i class="ti ti-users-off mb-2" style="font-size: 2rem;"></i>
                                                    <div>Sección vacía</div>
                                                    <small>Arrastra estudiantes aquí</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vista Tabla -->
                <div class="card" id="contenedorTabla" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Estudiantes por Sección</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaEstudiantes">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Estudiante</th>
                                        <th>Sección Actual</th>
                                        <th>Documento</th>
                                        <th>Apoderado</th>
                                        <th>Teléfono</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?= htmlspecialchars($estudiante['codigo_estudiante']) ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?></div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) ?> |
                                                        <?= $estudiante['genero'] === 'M' ? 'Masculino' : 'Femenino' ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($estudiante['grado']) ?> - <?= htmlspecialchars($estudiante['seccion']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($estudiante['nivel_nombre']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($estudiante['documento_numero']) ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars(($estudiante['apoderado_nombre'] ?? '') . ' ' . ($estudiante['apoderado_apellido'] ?? '')) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($estudiante['apoderado_telefono'] ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="trasladarEstudiante(<?= $estudiante['id'] ?>, <?= $estudiante['estudiante_id'] ?>)"
                                                        title="Trasladar">
                                                    <i class="ti ti-arrows-right-left"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                    <!-- Panel de Traslados Recientes -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="ti ti-history me-2"></i>
                                            Traslados Recientes (últimos 30 días)
                                        </h6>
                                        <span class="badge bg-primary"><?= count($traslados_recientes) ?> registros</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($traslados_recientes)): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="ti ti-transfer-out mb-2" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mb-0">No hay traslados registrados en los últimos 30 días</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Estudiante</th>
                                                        <th>Sección Origen</th>
                                                        <th></th>
                                                        <th>Sección Destino</th>
                                                        <th>Fecha</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($traslados_recientes as $traslado): ?>
                                                        <tr>
                                                            <td>
                                                                <div>
                                                                    <strong><?= htmlspecialchars($traslado['apellidos'] . ', ' . $traslado['nombres']) ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <i class="ti ti-id-badge"></i>
                                                                        <?= htmlspecialchars($traslado['codigo_estudiante']) ?>
                                                                    </small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-danger fs-6">
                                                                    <?= htmlspecialchars($traslado['grado_origen'] . '-' . $traslado['seccion_origen']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <i class="ti ti-arrow-right text-primary fs-4"></i>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success fs-6">
                                                                    <?= htmlspecialchars($traslado['grado_destino'] . '-' . $traslado['seccion_destino']) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <i class="ti ti-calendar"></i>
                                                                    <?= date('d/m/Y H:i', strtotime($traslado['fecha_traslado'])) ?>
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Procesando traslado...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/traslados/modal_traslado_manual.php'; ?>
    <?php include 'modales/traslados/modal_confirmacion.php'; ?>
    <?php include 'modales/traslados/modal_historial.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>}
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>

    <script>
        let tablaEstudiantes;
        let drake; // Dragula instance

        $(document).ready(function() {
            // Inicializar DataTable
            tablaEstudiantes = $('#tablaEstudiantes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Inicializar Drag & Drop
            inicializarDragDrop();

            // Filtros
            $('#filtroNivel, #filtroCapacidad').on('change', aplicarFiltros);
            $('#buscarEstudiante').on('keyup', aplicarFiltros);
        });

        function inicializarDragDrop() {
            // Obtener todos los contenedores de estudiantes
            const contenedores = Array.from(document.querySelectorAll('.estudiantes-container'));
            
            drake = dragula(contenedores, {
                moves: function (el, source, handle, sibling) {
                    return el.classList.contains('estudiante-item');
                },
                accepts: function (el, target, source, sibling) {
                    const seccionId = target.getAttribute('data-seccion-id');
                    const seccionCard = document.querySelector(`[data-seccion-id="${seccionId}"]`);
                    const capacidadMaxima = parseInt(seccionCard.getAttribute('data-capacidad'));
                    const estudiantesActuales = target.querySelectorAll('.estudiante-item').length;
                    
                    // No permitir drop si la sección está completa (excluyendo el elemento siendo arrastrado)
                    if (source !== target && estudiantesActuales >= capacidadMaxima) {
                        mostrarError('La sección destino está completa');
                        return false;
                    }
                    
                    return true;
                },
                invalid: function (el, handle) {
                    return false;
                }
            });

            drake.on('drop', function (el, target, source, sibling) {
                if (source === target) return; // Same container, no change

                const estudianteId = el.getAttribute('data-estudiante-id');
                const matriculaId = el.getAttribute('data-matricula-id');
                const seccionDestinoId = target.getAttribute('data-seccion-id');
                const seccionOrigenId = source.getAttribute('data-seccion-id');

                // Confirmar traslado
                confirmarTraslado(matriculaId, estudianteId, seccionOrigenId, seccionDestinoId, el, source, target);
            });

            drake.on('drag', function (el, source) {
                el.classList.add('gu-transit');
            });

            drake.on('dragend', function (el) {
                el.classList.remove('gu-transit');
            });
        }

        function confirmarTraslado(matriculaId, estudianteId, seccionOrigen, seccionDestino, elemento, contenedorOrigen, contenedorDestino) {
            const estudianteNombre = elemento.querySelector('.estudiante-nombre').textContent;
            
            // Obtener nombres de secciones
            const seccionOrigenCard = document.querySelector(`[data-seccion-id="${seccionOrigen}"]`);
            const seccionDestinoCard = document.querySelector(`[data-seccion-id="${seccionDestino}"]`);
            
            const nombreOrigen = seccionOrigenCard.querySelector('.seccion-nombre').textContent;
            const nombreDestino = seccionDestinoCard.querySelector('.seccion-nombre').textContent;

            Swal.fire({
                title: 'Confirmar Traslado',
                html: `
                    <div class="text-left">
                        <p><strong>Estudiante:</strong> ${estudianteNombre}</p>
                        <p><strong>Desde:</strong> ${nombreOrigen}</p>
                        <p><strong>Hacia:</strong> ${nombreDestino}</p>
                        <hr>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Esta acción actualizará la matrícula del estudiante
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmar Traslado',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    return new Promise((resolve) => {
                        resolve(true);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarTraslado(matriculaId, seccionDestino);
                } else {
                    // Revertir el cambio visual
                    contenedorOrigen.appendChild(elemento);
                }
            });
        }

        function ejecutarTraslado(matriculaId, seccionDestinoId) {
            mostrarCarga();

            fetch('modales/traslados/procesar_traslados.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=trasladar&matricula_id=${matriculaId}&seccion_destino=${seccionDestinoId}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito(data.message);
                    setTimeout(() => {
                        location.reload(); // Recargar para actualizar contadores
                    }, 1500);
                } else {
                    mostrarError(data.message);
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al procesar traslado');
                setTimeout(() => location.reload(), 2000);
            });
        }

        function cambiarVista(vista) {
            if (vista === 'secciones') {
                $('#contenedorSecciones').show();
                $('#contenedorTabla').hide();
                $('#vistaSecciones').addClass('active');
                $('#vistaTabla').removeClass('active');
            } else {
                $('#contenedorSecciones').hide();
                $('#contenedorTabla').show();
                $('#vistaTabla').addClass('active');
                $('#vistaSecciones').removeClass('active');
            }
        }

        function aplicarFiltros() {
            const nivelFiltro = $('#filtroNivel').val();
            const capacidadFiltro = $('#filtroCapacidad').val();
            const busquedaEstudiante = $('#buscarEstudiante').val().toLowerCase();

            // Filtrar secciones por nivel
            $('.nivel-section').each(function() {
                const nivelSection = $(this);
                let mostrarNivel = false;
                
                // Si hay filtro de nivel, verificar si coincide con el data-nivel
                const nivelSeccionTexto = nivelSection.attr('data-nivel');
                if (nivelFiltro) {
                    // Buscar si alguna sección del nivel tiene el nivel_id correcto
                    let nivelCoincide = false;
                    nivelSection.find('.seccion-card').each(function() {
                        // Comparar por el nombre del nivel en el título de la sección
                        <?php foreach ($niveles as $niv): ?>
                        if (nivelFiltro === '<?= $niv['id'] ?>' && nivelSeccionTexto === '<?= addslashes($niv['nombre']) ?>') {
                            nivelCoincide = true;
                            return false;
                        }
                        <?php endforeach; ?>
                    });
                    
                    if (!nivelCoincide) {
                        nivelSection.hide();
                        return;
                    }
                }

                nivelSection.find('.seccion-card').each(function() {
                    const seccionCard = $(this);
                    let mostrarSeccion = true;

                    // Filtro por capacidad
                    if (capacidadFiltro && mostrarSeccion) {
                        const estudiantesActuales = seccionCard.find('.estudiante-item').length;
                        const capacidadMaxima = parseInt(seccionCard.data('capacidad'));
                        
                        let estado = 'DISPONIBLE';
                        if (estudiantesActuales === 0) estado = 'VACIA';
                        else if (estudiantesActuales >= capacidadMaxima) estado = 'COMPLETA';
                        
                        if (estado !== capacidadFiltro) {
                            mostrarSeccion = false;
                        }
                    }

                    // Filtro por estudiante
                    if (busquedaEstudiante && mostrarSeccion) {
                        let tieneEstudianteBuscado = false;
                        seccionCard.find('.estudiante-item').each(function() {
                            const nombreEstudiante = $(this).find('.estudiante-nombre').text().toLowerCase();
                            const infoEstudiante = $(this).find('.estudiante-info').text().toLowerCase();
                            if (nombreEstudiante.includes(busquedaEstudiante) || infoEstudiante.includes(busquedaEstudiante)) {
                                tieneEstudianteBuscado = true;
                                return false;
                            }
                        });
                        if (!tieneEstudianteBuscado) {
                            mostrarSeccion = false;
                        }
                    }

                    seccionCard.toggle(mostrarSeccion);
                    if (mostrarSeccion) mostrarNivel = true;
                });

                nivelSection.toggle(mostrarNivel);
            });

            // Filtrar tabla
            if (tablaEstudiantes) {
                tablaEstudiantes.search(busquedaEstudiante || '').draw();
            }
        }

        function limpiarFiltros() {
            $('#filtroNivel, #filtroCapacidad').val('');
            $('#buscarEstudiante').val('');
            aplicarFiltros();
        }

        function actualizarVista() {
            location.reload();
        }

        function trasladarEstudiante(matriculaId, estudianteId) {
            $('#modalTrasladoManual').modal('show');
            // Cargar datos del estudiante en el modal
        }

        function verHistorialTraslados() {
            $('#modalHistorialTraslados').modal('show');
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function exportarTraslados() {
            // Capturar traslados desde la tabla
            const trasladosVisibles = [];
            
            // Buscar en la tabla de traslados recientes
            $('#tablaEstudiantes tbody tr:visible').each(function() {
                const fila = $(this);
                
                // Código del estudiante
                const codigo = fila.find('td:eq(0) .badge').text().trim();
                
                // Nombre completo del estudiante
                const nombreCompleto = fila.find('td:eq(1) .fw-bold').text().trim();
                
                // Información adicional (fecha nacimiento y género)
                const infoAdicional = fila.find('td:eq(1) small').text().trim();
                
                // Documento
                const documento = fila.find('td:eq(3) small').text().trim();
                
                const estudiante = nombreCompleto + '\n' + codigo + '\n' + documento;
                
                // Sección actual (origen)
                const seccionActual = fila.find('td:eq(2) .fw-bold').text().trim();
                
                // Nivel
                const nivel = fila.find('td:eq(2) small').text().trim();
                
                // Apoderado
                const apoderado = fila.find('td:eq(4) small').text().trim();
                
                // Teléfono
                const telefono = fila.find('td:eq(5) small').text().trim();
                
                // Construir datos del traslado
                trasladosVisibles.push([
                    estudiante,                    // 0: Estudiante completo
                    nivel,                         // 1: Nivel educativo
                    seccionActual,                 // 2: Sección origen
                    'Por definir',                 // 3: Sección destino (pendiente)
                    new Date().toISOString().split('T')[0], // 4: Fecha actual
                    'Origen: ' + seccionActual + '\nDestino: Pendiente', // 5: Capacidades
                    'Consulta de traslados - ' + apoderado + ' - Tel: ' + telefono // 6: Motivo/Observaciones
                ]);
            });
            
            // Si no hay datos en la tabla de estudiantes, intentar con la tabla de traslados recientes
            if (trasladosVisibles.length === 0) {
                $('table.table-hover tbody tr').each(function() {
                    const fila = $(this);
                    
                    // Verificar si la fila tiene los badges de origen/destino
                    const badges = fila.find('.badge');
                    if (badges.length < 2) return; // Skip si no tiene la estructura correcta
                    
                    // Estudiante
                    const nombreCompleto = fila.find('strong').first().text().trim();
                    const codigoMatch = fila.find('small').first().text().match(/[A-Z0-9]+/);
                    const codigo = codigoMatch ? codigoMatch[0] : '';
                    const estudiante = nombreCompleto + '\n' + codigo + '\nDNI';
                    
                    // Secciones
                    let seccion_origen = '';
                    let seccion_destino = '';
                    let nivel = 'No especificado';
                    
                    badges.each(function() {
                        const badge = $(this);
                        const texto = badge.text().trim();
                        
                        if (badge.hasClass('bg-danger')) {
                            seccion_origen = texto;
                            // Extraer nivel del grado (1ro, 2do, etc.)
                            if (texto.includes('1ro')) nivel = 'Primaria';
                            else if (texto.includes('2do')) nivel = 'Primaria';
                            else if (texto.includes('3ro')) nivel = 'Primaria';
                            else if (texto.includes('4to')) nivel = 'Primaria';
                            else if (texto.includes('5to')) nivel = 'Primaria';
                            else if (texto.includes('6to')) nivel = 'Primaria';
                        } else if (badge.hasClass('bg-success')) {
                            seccion_destino = texto;
                        }
                    });
                    
                    // Fecha
                    const fechaElement = fila.find('small:contains("📅")');
                    let fecha = new Date().toISOString().split('T')[0];
                    if (fechaElement.length > 0) {
                        const fechaTexto = fechaElement.text().trim();
                        const match = fechaTexto.match(/(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})/);
                        if (match) {
                            fecha = `${match[3]}-${match[2]}-${match[1]} ${match[4]}:${match[5]}`;
                        }
                    }
                    
                    // Capacidades
                    const capacidades = 'Origen: ' + seccion_origen + '\nDestino: ' + seccion_destino;
                    
                    // Motivo
                    const motivo = 'Traslado registrado el ' + fecha;
                    
                    trasladosVisibles.push([
                        estudiante,       // 0
                        nivel,           // 1
                        seccion_origen,  // 2
                        seccion_destino, // 3
                        fecha,           // 4
                        capacidades,     // 5
                        motivo           // 6
                    ]);
                });
            }
            
            // Verificar si hay datos para exportar
            if (trasladosVisibles.length === 0) {
                Swal.fire({
                    title: 'Sin datos',
                    text: 'No hay traslados disponibles para exportar. Asegúrate de tener traslados registrados en el sistema.',
                    icon: 'warning',
                    confirmButtonColor: '#fd7e14'
                });
                return;
            }
            
            // Mostrar confirmación
            Swal.fire({
                title: 'Exportar Traslados',
                text: `Se exportarán ${trasladosVisibles.length} registro(s) de traslados.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, exportar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear formulario y enviar datos
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'reportes/exportar_traslados.php';
                    form.target = '_blank';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'datosTraslados';
                    input.value = JSON.stringify(trasladosVisibles);
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    Swal.fire({
                        title: '¡Exportando!',
                        text: 'Se está generando el reporte PDF...',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function mostrarExito(mensaje) {
            Swal.fire({
                title: '¡Traslado Exitoso!',
                text: mensaje,
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function mostrarError(mensaje) {
            Swal.fire({
                title: 'Error en Traslado',
                text: mensaje,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    </script>
</body>
</html>