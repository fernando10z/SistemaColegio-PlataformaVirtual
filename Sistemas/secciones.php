<?php 
    require_once 'conexion/bd.php';

    // Obtener secciones con información de ocupación
    try {
        $sql = "SELECT s.*, 
                    ne.nombre as nivel_nombre,
                    pa.nombre as periodo_nombre,
                    pa.anio as periodo_anio,
                    COUNT(m.id) as estudiantes_matriculados,
                    COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) as estudiantes_activos,
                    ROUND((COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion,
                    CASE 
                        WHEN COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) = 0 THEN 'DISPONIBLE'
                        WHEN COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) < s.capacidad_maxima THEN 'OCUPADA'
                        WHEN COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END) >= s.capacidad_maxima THEN 'COMPLETA'
                        ELSE 'DISPONIBLE'
                    END as estado_ocupacion,
                    (s.capacidad_maxima - COUNT(CASE WHEN m.activo = 1 AND m.estado = 'MATRICULADO' THEN 1 END)) as cupos_disponibles
                FROM secciones s
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
                LEFT JOIN periodos_academicos pa ON s.periodo_academico_id = pa.id
                LEFT JOIN matriculas m ON s.id = m.seccion_id
                GROUP BY s.id
                ORDER BY ne.orden ASC, s.grado ASC, s.seccion ASC";
        
        $stmt_secciones = $conexion->prepare($sql);
        $stmt_secciones->execute();
        $secciones = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $secciones = [];
        $error_secciones = "Error al cargar secciones: " . $e->getMessage();
    }

    // Obtener niveles educativos para filtros
    try {
        $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
        $stmt_niveles->execute();
        $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $niveles = [];
    }

    // Obtener períodos académicos
    try {
        $stmt_periodos = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 ORDER BY anio DESC, fecha_inicio DESC");
        $stmt_periodos->execute();
        $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $periodos = [];
    }

    // Calcular estadísticas
    $total_secciones = count($secciones);
    $secciones_activas = count(array_filter($secciones, function($s) { return $s['activo']; }));
    $secciones_completas = count(array_filter($secciones, function($s) { return $s['estado_ocupacion'] === 'COMPLETA'; }));
    $capacidad_total = array_sum(array_column($secciones, 'capacidad_maxima'));
    $estudiantes_matriculados_total = array_sum(array_column($secciones, 'estudiantes_activos'));
    $ocupacion_promedio = $capacidad_total > 0 ? round(($estudiantes_matriculados_total / $capacidad_total) * 100, 1) : 0;
    $cupos_disponibles_total = $capacidad_total - $estudiantes_matriculados_total;

    // Extraer grados únicos para filtros
    $grados_disponibles = array_unique(array_column($secciones, 'grado'));
    sort($grados_disponibles);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Secciones - ANDRÉS AVELINO CÁCERES</title>
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
        .seccion-info {
            line-height: 1.3;
        }
        .seccion-codigo {
            font-weight: 600;
            color: #495057;
        }
        .seccion-detalles {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .ocupacion-bar {
            width: 100%;
            height: 25px;
            background-color: #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            margin-bottom: 5px;
        }
        .ocupacion-fill {
            height: 100%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ocupacion-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
            text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
        }
        .estado-badge {
            font-size: 0.75rem;
        }
        .nivel-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .capacidad-info {
            text-align: center;
            font-size: 0.85rem;
        }
        .capacidad-numero {
            font-weight: 600;
            font-size: 1.1rem;
            color: #495057;
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
        .aula-info {
            background-color: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            color: #495057;
        }
        .ocupacion-detalles {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 2px;
        }
        .reporte-ocupacion {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .porcentaje-grande {
            font-size: 2rem;
            font-weight: bold;
            color: #495057;
        }
        .sobrecupo {
            color: #dc3545 !important;
            font-weight: bold;
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
                                <h4 class="fw-bold mb-0">Gestión de Secciones</h4>
                                <p class="mb-0 text-muted">Control de capacidad, ocupación y administración de aulas</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarSeccion">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Sección
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reporte General de Ocupación -->
                <div class="reporte-ocupacion">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">Resumen General de Ocupación</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="porcentaje-grande <?= $ocupacion_promedio > 90 ? 'sobrecupo' : '' ?>"><?= $ocupacion_promedio ?>%</div>
                                        <small class="text-muted">Ocupación Promedio</small>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="ocupacion-bar">
                                        <div class="ocupacion-fill <?php 
                                            if ($ocupacion_promedio >= 100) echo 'bg-danger';
                                            elseif ($ocupacion_promedio >= 90) echo 'bg-warning';
                                            elseif ($ocupacion_promedio >= 70) echo 'bg-info';
                                            else echo 'bg-success';
                                        ?>" style="width: <?= min($ocupacion_promedio, 100) ?>%"></div>
                                        <div class="ocupacion-text">
                                            <?= $estudiantes_matriculados_total ?>/<?= $capacidad_total ?> estudiantes
                                        </div>
                                    </div>
                                    <div class="ocupacion-detalles text-center">
                                        Cupos disponibles: <?= $cupos_disponibles_total >= 0 ? $cupos_disponibles_total : '0 (SOBRECUPO: ' . abs($cupos_disponibles_total) . ')' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row g-2">
                                <div class="col-6 text-center">
                                    <div class="h5 text-primary"><?= $total_secciones ?></div>
                                    <small class="text-muted">Total Secciones</small>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="h5 text-success"><?= $secciones_activas ?></div>
                                    <small class="text-muted">Activas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Nivel Educativo</label>
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos los niveles</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Grado</label>
                                <select class="form-select" id="filtroGrado">
                                    <option value="">Todos los grados</option>
                                    <?php foreach ($grados_disponibles as $grado): ?>
                                        <option value="<?= htmlspecialchars($grado) ?>"><?= htmlspecialchars($grado) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado Ocupación</label>
                                <select class="form-select" id="filtroOcupacion">
                                    <option value="">Todos</option>
                                    <option value="DISPONIBLE">Disponible</option>
                                    <option value="OCUPADA">Ocupada</option>
                                    <option value="COMPLETA">Completa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Período</label>
                                <select class="form-select" id="filtroPeriodo">
                                    <option value="">Todos</option>
                                    <?php foreach ($periodos as $periodo): ?>
                                        <option value="<?= $periodo['id'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarSeccion" placeholder="Buscar sección, aula...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarSecciones()">
                                    <i class="ti ti-download"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Secciones -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Secciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaSecciones">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sección</th>
                                        <th>Nivel/Grado</th>
                                        <th>Aula Asignada</th>
                                        <th>Capacidad</th>
                                        <th>Ocupación</th>
                                        <th>Estado</th>
                                        <th>Período</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($secciones as $seccion): 
                                        // Color de la barra de ocupación
                                        $ocupacion_color = 'bg-success';
                                        if ($seccion['porcentaje_ocupacion'] >= 100) {
                                            $ocupacion_color = 'bg-danger';
                                        } elseif ($seccion['porcentaje_ocupacion'] >= 90) {
                                            $ocupacion_color = 'bg-warning';
                                        } elseif ($seccion['porcentaje_ocupacion'] >= 70) {
                                            $ocupacion_color = 'bg-info';
                                        }
                                    ?>
                                        <tr data-nivel="<?= $seccion['nivel_id'] ?>" 
                                            data-grado="<?= htmlspecialchars($seccion['grado']) ?>"
                                            data-ocupacion="<?= $seccion['estado_ocupacion'] ?>"
                                            data-periodo="<?= $seccion['periodo_academico_id'] ?>">
                                            <td>
                                                <div class="seccion-info">
                                                    <div class="seccion-codigo"><?= htmlspecialchars($seccion['codigo']) ?></div>
                                                    <div class="seccion-detalles">
                                                        <?= htmlspecialchars($seccion['grado']) ?> - Sección <?= htmlspecialchars($seccion['seccion']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge nivel-badge bg-primary">
                                                    <?= htmlspecialchars($seccion['nivel_nombre']) ?>
                                                </span>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($seccion['grado']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($seccion['aula_asignada']): ?>
                                                    <div class="aula-info">
                                                        <i class="ti ti-door me-1"></i>
                                                        <?= htmlspecialchars($seccion['aula_asignada']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin aula asignada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="capacidad-info">
                                                    <div class="capacidad-numero"><?= $seccion['capacidad_maxima'] ?></div>
                                                    <small class="text-muted">estudiantes</small>
                                                </div>
                                            </td>
                                            <td style="min-width: 180px;">
                                                <div class="ocupacion-bar">
                                                    <div class="ocupacion-fill <?= $ocupacion_color ?>" 
                                                         style="width: <?= min($seccion['porcentaje_ocupacion'], 100) ?>%"></div>
                                                    <div class="ocupacion-text">
                                                        <?= $seccion['estudiantes_activos'] ?>/<?= $seccion['capacidad_maxima'] ?>
                                                    </div>
                                                </div>
                                                <div class="ocupacion-detalles">
                                                    <?= $seccion['porcentaje_ocupacion'] ?>% ocupado
                                                    <?php if ($seccion['cupos_disponibles'] < 0): ?>
                                                        <span class="sobrecupo">(Sobrecupo: <?= abs($seccion['cupos_disponibles']) ?>)</span>
                                                    <?php elseif ($seccion['cupos_disponibles'] > 0): ?>
                                                        <span class="text-success">(<?= $seccion['cupos_disponibles'] ?> cupos libres)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge estado-badge <?php
                                                    switch($seccion['estado_ocupacion']) {
                                                        case 'DISPONIBLE': echo 'bg-success'; break;
                                                        case 'OCUPADA': echo 'bg-warning text-dark'; break;
                                                        case 'COMPLETA': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= $seccion['estado_ocupacion'] ?>
                                                </span>
                                                <br>
                                                <span class="badge estado-badge <?= $seccion['activo'] ? 'bg-info' : 'bg-secondary' ?> mt-1">
                                                    <?= $seccion['activo'] ? 'Activa' : 'Inactiva' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($seccion['periodo_nombre']) ?><br>
                                                    <?= $seccion['periodo_anio'] ?>
                                                </small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarSeccion(<?= $seccion['id'] ?>)" 
                                                            title="Editar Sección">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalleSeccion(<?= $seccion['id'] ?>)" 
                                                            title="Ver Detalle">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <?php if ($seccion['estudiantes_activos'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="verEstudiantes(<?= $seccion['id'] ?>)" 
                                                                title="Ver Estudiantes">
                                                            <i class="ti ti-users"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm <?= $seccion['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoSeccion(<?= $seccion['id'] ?>, <?= $seccion['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $seccion['activo'] ? 'Desactivar' : 'Activar' ?> Sección">
                                                        <i class="ti <?= $seccion['activo'] ? 'ti-eye-off' : 'ti-eye' ?>"></i>
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
    <?php include 'modales/secciones/modal_agregar.php'; ?>
    <?php include 'modales/secciones/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaSecciones;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaSecciones = $('#tablaSecciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Filtros personalizados
            $('#filtroNivel, #filtroGrado, #filtroOcupacion, #filtroPeriodo').on('change', aplicarFiltros);
            $('#buscarSeccion').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const nivelFiltro = $('#filtroNivel').val();
            const gradoFiltro = $('#filtroGrado').val();
            const ocupacionFiltro = $('#filtroOcupacion').val();
            const periodoFiltro = $('#filtroPeriodo').val();
            const busqueda = $('#buscarSeccion').val().toLowerCase();

            $('#tablaSecciones tbody tr').each(function() {
                const fila = $(this);
                const nivel = fila.data('nivel');
                const grado = fila.data('grado');
                const ocupacion = fila.data('ocupacion');
                const periodo = fila.data('periodo');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (nivelFiltro && nivel != nivelFiltro) mostrar = false;
                if (gradoFiltro && grado !== gradoFiltro) mostrar = false;
                if (ocupacionFiltro && ocupacion !== ocupacionFiltro) mostrar = false;
                if (periodoFiltro && periodo != periodoFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroNivel, #filtroGrado, #filtroOcupacion, #filtroPeriodo').val('');
            $('#buscarSeccion').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarSeccion(id) {
            mostrarCarga();
            
            fetch('modales/secciones/procesar_secciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionSeccion(data.seccion);
                    $('#modalEditarSeccion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la sección');
            });
        }

        function verDetalleSeccion(id) {
            mostrarCarga();
            
            fetch('modales/secciones/procesar_secciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetalleSeccion(data.seccion);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalle de la sección');
            });
        }

        function mostrarDetalleSeccion(seccion) {
            const sobrecupo = seccion.cupos_disponibles < 0;
            const porcentajeOcupacion = Math.round((seccion.estudiantes_activos / seccion.capacidad_maxima) * 100);
            
            Swal.fire({
                title: `${seccion.codigo} - ${seccion.grado} ${seccion.seccion}`,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Información General:</strong><br>
                                <small>Nivel: ${seccion.nivel_nombre}</small><br>
                                <small>Aula: ${seccion.aula_asignada || 'Sin asignar'}</small><br>
                                <small>Período: ${seccion.periodo_nombre}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Ocupación:</strong><br>
                                <small>Capacidad: ${seccion.capacidad_maxima} estudiantes</small><br>
                                <small>Matriculados: ${seccion.estudiantes_activos} estudiantes</small><br>
                                <small>Porcentaje: ${porcentajeOcupacion}%</small><br>
                                ${sobrecupo ? 
                                    `<small class="text-danger"><strong>SOBRECUPO: ${Math.abs(seccion.cupos_disponibles)} estudiantes</strong></small>` :
                                    `<small class="text-success">Cupos libres: ${seccion.cupos_disponibles}</small>`
                                }
                            </div>
                        </div>
                        <hr>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar ${sobrecupo || porcentajeOcupacion >= 100 ? 'bg-danger' : porcentajeOcupacion >= 90 ? 'bg-warning' : porcentajeOcupacion >= 70 ? 'bg-info' : 'bg-success'}" 
                                 style="width: ${Math.min(porcentajeOcupacion, 100)}%">
                                ${porcentajeOcupacion}%
                            </div>
                        </div>
                        ${sobrecupo ? '<div class="alert alert-danger mt-2 mb-0"><small><i class="ti ti-alert-triangle"></i> Esta sección tiene sobrecupo. Se recomienda redistribuir estudiantes.</small></div>' : ''}
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function verEstudiantes(id) {
            mostrarCarga();
            
            fetch('modales/secciones/procesar_secciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=estudiantes&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarEstudiantesSeccion(data.estudiantes);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar estudiantes');
            });
        }

        function mostrarEstudiantesSeccion(estudiantes) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Código</th><th>Estudiante</th><th>Estado</th><th>Fecha Matrícula</th></tr></thead><tbody>';
            
            estudiantes.forEach(estudiante => {
                html += `<tr>
                    <td>${estudiante.codigo_estudiante}</td>
                    <td>${estudiante.nombres} ${estudiante.apellidos}</td>
                    <td><span class="badge ${estudiante.estado === 'MATRICULADO' ? 'bg-success' : 'bg-warning'}">${estudiante.estado}</span></td>
                    <td>${new Date(estudiante.fecha_matricula).toLocaleDateString('es-ES')}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Estudiantes de la Sección',
                html: html,
                width: '700px',
                confirmButtonText: 'Cerrar'
            });
        }

        function toggleEstadoSeccion(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} esta sección?`,
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

            fetch('modales/secciones/procesar_secciones.php', {
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
                mostrarError('Error al cambiar estado de la sección');
            });
        }

        function exportarSecciones() {
            window.open('reportes/exportar_secciones.php', '_blank');
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