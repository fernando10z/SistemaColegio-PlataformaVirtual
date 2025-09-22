<?php 
    require_once 'conexion/bd.php';

    // Obtener todos los períodos académicos
    try {
        $sql = "SELECT pa.*, 
                    COUNT(DISTINCT s.id) as total_secciones,
                    COUNT(DISTINCT m.id) as total_matriculas
                FROM periodos_academicos pa
                LEFT JOIN secciones s ON pa.id = s.periodo_academico_id AND s.activo = 1
                LEFT JOIN matriculas m ON pa.id = m.periodo_academico_id AND m.activo = 1
                GROUP BY pa.id
                ORDER BY pa.anio DESC, pa.fecha_inicio DESC";
        
        $stmt_periodos = $conexion->prepare($sql);
        $stmt_periodos->execute();
        $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $periodos = [];
        $error_periodos = "Error al cargar períodos: " . $e->getMessage();
    }

    // Obtener período actual
    $periodo_actual = null;
    foreach ($periodos as $periodo) {
        if ($periodo['actual']) {
            $periodo_actual = $periodo;
            break;
        }
    }

    // Obtener años únicos para filtros
    $anios_disponibles = [];
    foreach ($periodos as $periodo) {
        if (!in_array($periodo['anio'], $anios_disponibles)) {
            $anios_disponibles[] = $periodo['anio'];
        }
    }
    sort($anios_disponibles, SORT_NUMERIC);

    // Calcular estadísticas
    $total_periodos = count($periodos);
    $periodos_activos = count(array_filter($periodos, function($p) { return $p['activo']; }));
    $periodos_inactivos = $total_periodos - $periodos_activos;

    // Calcular progreso del período actual
    $progreso_actual = 0;
    $dias_transcurridos = 0;
    $dias_totales = 0;
    if ($periodo_actual) {
        $hoy = time();
        $inicio = strtotime($periodo_actual['fecha_inicio']);
        $fin = strtotime($periodo_actual['fecha_fin']);
        
        $dias_totales = ceil(($fin - $inicio) / (60 * 60 * 24));
        
        if ($hoy >= $inicio && $hoy <= $fin) {
            $dias_transcurridos = ceil(($hoy - $inicio) / (60 * 60 * 24));
            $progreso_actual = round(($dias_transcurridos / $dias_totales) * 100, 1);
        } elseif ($hoy > $fin) {
            $progreso_actual = 100;
            $dias_transcurridos = $dias_totales;
        }
    }

    // Función para generar períodos de evaluación automáticos
    function generarPeriodosEvaluacion($fecha_inicio, $fecha_fin, $tipo_periodo) {
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $duracion_total = $inicio->diff($fin)->days;
        
        $periodos = [];
        
        switch ($tipo_periodo) {
            case 'BIMESTRE':
                $num_periodos = 4;
                $nombre_base = 'Bimestre';
                break;
            case 'TRIMESTRE':
                $num_periodos = 3;
                $nombre_base = 'Trimestre';
                break;
            case 'SEMESTRE':
                $num_periodos = 2;
                $nombre_base = 'Semestre';
                break;
            default:
                return [];
        }
        
        $dias_por_periodo = floor($duracion_total / $num_periodos);
        $fecha_actual = clone $inicio;
        
        for ($i = 1; $i <= $num_periodos; $i++) {
            $fecha_fin_periodo = clone $fecha_actual;
            
            if ($i < $num_periodos) {
                $fecha_fin_periodo->add(new DateInterval('P' . $dias_por_periodo . 'D'));
            } else {
                $fecha_fin_periodo = clone $fin;
            }
            
            $periodos[] = [
                'numero' => $i,
                'nombre' => roman_numeral($i) . ' ' . $nombre_base,
                'fecha_inicio' => $fecha_actual->format('Y-m-d'),
                'fecha_fin' => $fecha_fin_periodo->format('Y-m-d')
            ];
            
            $fecha_actual = clone $fecha_fin_periodo;
            $fecha_actual->add(new DateInterval('P1D'));
        }
        
        return $periodos;
    }

    function roman_numeral($number) {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Períodos Académicos - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
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
        .periodo-info {
            line-height: 1.3;
        }
        .periodo-nombre {
            font-weight: 600;
            color: #495057;
            font-size: 1.1rem;
        }
        .periodo-detalles {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .tipo-periodo-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .estado-badge {
            font-size: 0.75rem;
        }
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .card-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
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
        .progreso-periodo {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            position: relative;
        }
        .progreso-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
        }
        .progreso-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
        }
        .periodo-actual-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #1976d2;
        }
        .evaluacion-item {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 0.5rem;
            margin: 0.25rem 0;
            font-size: 0.85rem;
        }
        .evaluacion-expandida {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .fecha-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .duracion-badge {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
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

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-0">Períodos Académicos</h4>
                                <p class="mb-0 text-muted">Gestión de años académicos y períodos de evaluación</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarPeriodo">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Período
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Período Actual -->
                <?php if ($periodo_actual): ?>
                <div class="card periodo-actual-card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-calendar-check me-2 text-primary"></i>
                                    <h6 class="mb-0 text-primary">Período Académico Actual</h6>
                                </div>
                                <h5 class="mb-1"><?= htmlspecialchars($periodo_actual['nombre']) ?></h5>
                                <div class="fecha-info">
                                    <?= date('d/m/Y', strtotime($periodo_actual['fecha_inicio'])) ?> - 
                                    <?= date('d/m/Y', strtotime($periodo_actual['fecha_fin'])) ?>
                                    <span class="ms-2">
                                        <span class="badge bg-info"><?= $periodo_actual['tipo_periodo'] ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-end mb-2">
                                    <small class="text-muted">Progreso del período</small>
                                </div>
                                <div class="progreso-periodo mb-2">
                                    <div class="progreso-fill" style="width: <?= $progreso_actual ?>%"></div>
                                    <div class="progreso-text"><?= $progreso_actual ?>%</div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">
                                        Día <?= $dias_transcurridos ?> de <?= $dias_totales ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Año</label>
                                <select class="form-select" id="filtroAnio">
                                    <option value="">Todos los años</option>
                                    <?php foreach ($anios_disponibles as $anio): ?>
                                        <option value="<?= $anio ?>"><?= $anio ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Período</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="BIMESTRE">Bimestre</option>
                                    <option value="TRIMESTRE">Trimestre</option>
                                    <option value="SEMESTRE">Semestre</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Período Actual</label>
                                <select class="form-select" id="filtroActual">
                                    <option value="">Todos</option>
                                    <option value="1">Período Actual</option>
                                    <option value="0">No Actual</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarPeriodo" placeholder="Buscar período...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarPeriodos()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Períodos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Períodos Académicos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaPeriodos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Período Académico</th>
                                        <th>Año</th>
                                        <th>Tipo / Duración</th>
                                        <th>Períodos de Evaluación</th>
                                        <th>Secciones / Matrículas</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($periodos as $periodo): 
                                        $periodos_eval = json_decode($periodo['periodos_evaluacion'], true) ?: [];
                                        $duracion_dias = ceil((strtotime($periodo['fecha_fin']) - strtotime($periodo['fecha_inicio'])) / (60 * 60 * 24));
                                    ?>
                                        <tr data-anio="<?= $periodo['anio'] ?>" 
                                            data-tipo="<?= $periodo['tipo_periodo'] ?>" 
                                            data-estado="<?= $periodo['activo'] ?>"
                                            data-actual="<?= $periodo['actual'] ?>">
                                            <td>
                                                <div class="periodo-info">
                                                    <div class="periodo-nombre">
                                                        <?= htmlspecialchars($periodo['nombre']) ?>
                                                        <?php if ($periodo['actual']): ?>
                                                            <span class="badge bg-success ms-2">ACTUAL</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="periodo-detalles">
                                                        <i class="ti ti-calendar-event"></i>
                                                        <?= date('d/m/Y', strtotime($periodo['fecha_inicio'])) ?> - 
                                                        <?= date('d/m/Y', strtotime($periodo['fecha_fin'])) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <h6 class="mb-0"><?= $periodo['anio'] ?></h6>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="badge tipo-periodo-badge <?php
                                                        switch($periodo['tipo_periodo']) {
                                                            case 'BIMESTRE': echo 'bg-primary'; break;
                                                            case 'TRIMESTRE': echo 'bg-info'; break;
                                                            case 'SEMESTRE': echo 'bg-success'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                        <?= $periodo['tipo_periodo'] ?>
                                                    </span>
                                                    <div class="duracion-badge mt-1">
                                                        <?= $duracion_dias ?> días
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <strong><?= count($periodos_eval) ?> períodos</strong>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                            onclick="toggleEvaluaciones(<?= $periodo['id'] ?>)">
                                                        <i class="ti ti-eye"></i> Ver detalle
                                                    </button>
                                                    <div id="evaluaciones-<?= $periodo['id'] ?>" class="evaluacion-expandida" style="display: none;">
                                                        <?php foreach ($periodos_eval as $eval): ?>
                                                            <div class="evaluacion-item">
                                                                <strong><?= htmlspecialchars($eval['nombre']) ?></strong><br>
                                                                <small class="text-muted">
                                                                    <?= date('d/m/Y', strtotime($eval['fecha_inicio'])) ?> - 
                                                                    <?= date('d/m/Y', strtotime($eval['fecha_fin'])) ?>
                                                                </small>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div>
                                                        <span class="badge bg-info"><?= $periodo['total_secciones'] ?></span>
                                                        secciones
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-success"><?= $periodo['total_matriculas'] ?></span>
                                                        matrículas
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="badge estado-badge <?= $periodo['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $periodo['activo'] ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                    <?php if ($periodo['actual']): ?>
                                                        <br><span class="badge bg-primary estado-badge mt-1">Período Actual</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarPeriodo(<?= $periodo['id'] ?>)" 
                                                            title="Editar Período">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <?php if (!$periodo['actual']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="marcarComoActual(<?= $periodo['id'] ?>)" 
                                                                title="Marcar como Actual">
                                                            <i class="ti ti-star"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm <?= $periodo['activo'] ? 'btn-outline-warning' : 'btn-outline-info' ?>" 
                                                            onclick="toggleEstadoPeriodo(<?= $periodo['id'] ?>, <?= $periodo['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $periodo['activo'] ? 'Desactivar' : 'Activar' ?> Período">
                                                        <i class="ti <?= $periodo['activo'] ? 'ti-toggle-right' : 'ti-toggle-left' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="generarEvaluaciones(<?= $periodo['id'] ?>)" 
                                                            title="Regenerar Evaluaciones">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            onclick="duplicarPeriodo(<?= $periodo['id'] ?>)" 
                                                            title="Duplicar Período">
                                                        <i class="ti ti-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/periodos/modal_agregar.php'; ?>
    <?php include 'modales/periodos/modal_editar.php'; ?>
    <?php include 'modales/periodos/modal_evaluaciones.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaPeriodos;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaPeriodos = $('#tablaPeriodos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros
            $('#filtroAnio, #filtroTipo, #filtroEstado, #filtroActual').on('change', aplicarFiltros);
            $('#buscarPeriodo').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const anioFiltro = $('#filtroAnio').val();
            const tipoFiltro = $('#filtroTipo').val();
            const estadoFiltro = $('#filtroEstado').val();
            const actualFiltro = $('#filtroActual').val();
            const busqueda = $('#buscarPeriodo').val().toLowerCase();

            $('#tablaPeriodos tbody tr').each(function() {
                const fila = $(this);
                const anio = fila.data('anio');
                const tipo = fila.data('tipo');
                const estado = fila.data('estado');
                const actual = fila.data('actual');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (anioFiltro && anio != anioFiltro) mostrar = false;
                if (tipoFiltro && tipo !== tipoFiltro) mostrar = false;
                if (estadoFiltro !== '' && estado != estadoFiltro) mostrar = false;
                if (actualFiltro !== '' && actual != actualFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroAnio, #filtroTipo, #filtroEstado, #filtroActual').val('');
            $('#buscarPeriodo').val('');
            aplicarFiltros();
        }

        function toggleEvaluaciones(id) {
            $(`#evaluaciones-${id}`).slideToggle();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarPeriodo(id) {
            mostrarCarga();
            
            fetch('modales/periodos/procesar_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.periodo);
                    $('#modalEditarPeriodo').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del período');
            });
        }

        function marcarComoActual(id) {
            Swal.fire({
                title: '¿Marcar como período actual?',
                text: 'Esto desactivará el período actual y establecerá este como activo.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, marcar como actual',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarMarcarActual(id);
                }
            });
        }

        function ejecutarMarcarActual(id) {
            mostrarCarga();

            fetch('modales/periodos/procesar_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=marcar_actual&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al marcar período como actual');
            });
        }

        function toggleEstadoPeriodo(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este período académico?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstado(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstado(id, estado) {
            mostrarCarga();

            fetch('modales/periodos/procesar_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=toggle_estado&id=${id}&estado=${estado}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cambiar estado del período');
            });
        }

        function generarEvaluaciones(id) {
            Swal.fire({
                title: '¿Regenerar períodos de evaluación?',
                text: 'Esto sobrescribirá los períodos existentes con fechas calculadas automáticamente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, regenerar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarRegeneracion(id);
                }
            });
        }

        function ejecutarRegeneracion(id) {
            mostrarCarga();

            fetch('modales/periodos/procesar_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=regenerar_evaluaciones&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al regenerar evaluaciones');
            });
        }

        function duplicarPeriodo(id) {
            Swal.fire({
                title: '¿Duplicar período académico?',
                text: 'Esto creará una copia del período para el próximo año.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, duplicar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarDuplicacion(id);
                }
            });
        }

        function ejecutarDuplicacion(id) {
            mostrarCarga();

            fetch('modales/periodos/procesar_periodos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=duplicar&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al duplicar período');
            });
        }

        function exportarPeriodos() {
            window.open('reportes/exportar_periodos.php', '_blank');
        }

        function mostrarExito(mensaje) {
            Swal.fire({
                title: '¡Éxito!',
                text: mensaje,
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function mostrarError(mensaje) {
            Swal.fire({
                title: 'Error',
                text: mensaje,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    </script>
</body>
</html>