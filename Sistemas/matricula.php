<?php 
    require_once 'conexion/bd.php';

    // Obtener período académico actual
    try {
        $stmt_periodo_actual = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 AND actual = 1 LIMIT 1");
        $stmt_periodo_actual->execute();
        $periodo_actual = $stmt_periodo_actual->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $periodo_actual = null;
    }

    // Obtener todos los períodos académicos
    try {
        $stmt_periodos = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 ORDER BY anio DESC, fecha_inicio DESC");
        $stmt_periodos->execute();
        $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $periodos = [];
    }

    // Obtener matrículas con información completa
    try {
        $sql = "SELECT m.*, 
                    e.nombres as estudiante_nombres, e.apellidos as estudiante_apellidos,
                    e.codigo_estudiante, e.documento_numero, e.foto_url,
                    s.codigo as seccion_codigo, s.grado, s.seccion, s.aula_asignada, s.capacidad_maxima,
                    ne.nombre as nivel_nombre,
                    pa.nombre as periodo_nombre, pa.anio,
                    COUNT(m2.id) as companeros_seccion,
                    ROUND((COUNT(m2.id) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion
                FROM matriculas m
                LEFT JOIN estudiantes e ON m.estudiante_id = e.id
                LEFT JOIN secciones s ON m.seccion_id = s.id
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
                LEFT JOIN periodos_academicos pa ON m.periodo_academico_id = pa.id
                LEFT JOIN matriculas m2 ON s.id = m2.seccion_id AND m2.estado = 'MATRICULADO' AND m2.activo = 1
                WHERE m.activo = 1
                GROUP BY m.id
                ORDER BY m.fecha_matricula DESC, m.codigo_matricula ASC";
        
        $stmt_matriculas = $conexion->prepare($sql);
        $stmt_matriculas->execute();
        $matriculas = $stmt_matriculas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $matriculas = [];
        $error_matriculas = "Error al cargar matrículas: " . $e->getMessage();
    }

    // Obtener secciones disponibles
    try {
        $sql_secciones = "SELECT s.*, ne.nombre as nivel_nombre,
                                COUNT(m.id) as estudiantes_matriculados,
                                (s.capacidad_maxima - COUNT(m.id)) as vacantes_disponibles
                        FROM secciones s
                        LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
                        LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.estado = 'MATRICULADO' AND m.activo = 1
                        WHERE s.activo = 1";
        if ($periodo_actual) {
            $sql_secciones .= " AND s.periodo_academico_id = " . $periodo_actual['id'];
        }
        $sql_secciones .= " GROUP BY s.id ORDER BY ne.orden ASC, s.grado ASC, s.seccion ASC";
        
        $stmt_secciones = $conexion->prepare($sql_secciones);
        $stmt_secciones->execute();
        $secciones = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $secciones = [];
    }

    // Calcular estadísticas
    $total_matriculas = count($matriculas);
    $matriculas_activas = count(array_filter($matriculas, function($m) { return $m['estado'] === 'MATRICULADO'; }));
    $matriculas_retiradas = count(array_filter($matriculas, function($m) { return $m['estado'] === 'RETIRADO'; }));
    $matriculas_trasladadas = count(array_filter($matriculas, function($m) { return $m['estado'] === 'TRASLADADO'; }));

    $estudiantes_nuevos = count(array_filter($matriculas, function($m) { return $m['tipo_matricula'] === 'NUEVO'; }));
    $estudiantes_continuadores = count(array_filter($matriculas, function($m) { return $m['tipo_matricula'] === 'CONTINUADOR'; }));

    // Capacidad total y ocupación
    $capacidad_total = array_sum(array_column($secciones, 'capacidad_maxima'));
    $ocupacion_total = array_sum(array_column($secciones, 'estudiantes_matriculados'));
    $ocupacion_promedio = $capacidad_total > 0 ? round(($ocupacion_total / $capacidad_total) * 100, 1) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Matrículas - ANDRÉS AVELINO CÁCERES</title>
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
        .estudiante-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .estudiante-info {
            line-height: 1.3;
        }
        .estudiante-nombre {
            font-weight: 600;
            color: #495057;
        }
        .estudiante-codigo {
            font-size: 0.85rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        .matricula-codigo {
            font-weight: 600;
            color: #0d6efd;
            font-family: 'Courier New', monospace;
        }
        .estado-badge {
            font-size: 0.75rem;
        }
        .tipo-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .seccion-info {
            line-height: 1.2;
        }
        .seccion-nombre {
            font-weight: 600;
            color: #495057;
        }
        .seccion-detalles {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .ocupacion-indicator {
            width: 60px;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        .ocupacion-fill {
            height: 100%;
            transition: width 0.3s ease;
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
        .periodo-info {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .periodo-nombre {
            font-weight: 600;
            color: #2e7d32;
            font-size: 1.1rem;
        }
        .alerta-capacidad {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
        }
        .capacidad-info {
            font-size: 0.8rem;
            color: #6c757d;
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
                                <h4 class="fw-bold mb-0">Gestión de Matrículas</h4>
                                <p class="mb-0 text-muted">Control integral de matrículas estudiantiles</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaMatricula">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Matrícula
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Período Actual -->
                <?php if ($periodo_actual): ?>
                <div class="periodo-info">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="periodo-nombre"><?= htmlspecialchars($periodo_actual['nombre']) ?></div>
                            <div class="text-muted">
                                <i class="ti ti-calendar"></i>
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_inicio'])) ?> - 
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_fin'])) ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted">Ocupación Global</div>
                            <div class="h4 text-success"><?= $ocupacion_promedio ?>%</div>
                            <small class="text-muted"><?= $ocupacion_total ?>/<?= $capacidad_total ?> estudiantes</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Período Académico</label>
                                <select class="form-select" id="filtroPeriodo">
                                    <?php foreach ($periodos as $periodo): ?>
                                        <option value="<?= $periodo['id'] ?>" <?= $periodo_actual && $periodo['id'] == $periodo_actual['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($periodo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="MATRICULADO">Matriculado</option>
                                    <option value="RETIRADO">Retirado</option>
                                    <option value="TRASLADADO">Trasladado</option>
                                    <option value="RESERVADO">Reservado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo Matrícula</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="NUEVO">Nuevo</option>
                                    <option value="CONTINUADOR">Continuador</option>
                                    <option value="TRASLADO">Traslado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sección</label>
                                <select class="form-select" id="filtroSeccion">
                                    <option value="">Todas</option>
                                    <?php foreach ($secciones as $seccion): ?>
                                        <option value="<?= $seccion['id'] ?>">
                                            <?= htmlspecialchars($seccion['nivel_nombre'] . ' - ' . $seccion['grado'] . $seccion['seccion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarMatricula" placeholder="Buscar estudiante, código...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarMatricula()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Matrículas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Matrículas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaMatriculas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código Matrícula</th>
                                        <th>Estudiante</th>
                                        <th>Sección</th>
                                        <th>Estado / Tipo</th>
                                        <th>Capacidad</th>
                                        <th>Fecha Matrícula</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matriculas as $matricula): ?>
                                        <tr data-periodo="<?= $matricula['periodo_academico_id'] ?>" 
                                            data-estado="<?= $matricula['estado'] ?>"
                                            data-tipo="<?= $matricula['tipo_matricula'] ?>"
                                            data-seccion="<?= $matricula['seccion_id'] ?>">
                                            <td>
                                                <div class="matricula-codigo"><?= htmlspecialchars($matricula['codigo_matricula']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($matricula['periodo_nombre']) ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $matricula['foto_url'] ?: '../assets/images/profile/user-default.jpg' ?>" 
                                                         class="estudiante-avatar me-2" alt="Avatar">
                                                    <div class="estudiante-info">
                                                        <div class="estudiante-nombre">
                                                            <?= htmlspecialchars($matricula['estudiante_nombres'] . ' ' . $matricula['estudiante_apellidos']) ?>
                                                        </div>
                                                        <div class="estudiante-codigo"><?= htmlspecialchars($matricula['codigo_estudiante']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($matricula['documento_numero']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="seccion-info">
                                                    <div class="seccion-nombre">
                                                        <?= htmlspecialchars($matricula['nivel_nombre'] . ' - ' . $matricula['grado'] . $matricula['seccion']) ?>
                                                    </div>
                                                    <div class="seccion-detalles">
                                                        <?= htmlspecialchars($matricula['aula_asignada'] ?: 'Sin aula') ?>
                                                        <br>
                                                        <span class="capacidad-info">
                                                            <?= $matricula['companeros_seccion'] ?>/<?= $matricula['capacidad_maxima'] ?> estudiantes
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge estado-badge <?php
                                                    switch($matricula['estado']) {
                                                        case 'MATRICULADO': echo 'bg-success'; break;
                                                        case 'RETIRADO': echo 'bg-danger'; break;
                                                        case 'TRASLADADO': echo 'bg-warning text-dark'; break;
                                                        case 'RESERVADO': echo 'bg-info'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= $matricula['estado'] ?>
                                                </span>
                                                <br>
                                                <span class="badge tipo-badge bg-secondary mt-1">
                                                    <?= $matricula['tipo_matricula'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="ocupacion-indicator mx-auto">
                                                        <div class="ocupacion-fill <?php
                                                            if ($matricula['porcentaje_ocupacion'] >= 100) echo 'bg-danger';
                                                            elseif ($matricula['porcentaje_ocupacion'] >= 90) echo 'bg-warning';
                                                            else echo 'bg-success';
                                                        ?>" style="width: <?= min($matricula['porcentaje_ocupacion'], 100) ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= $matricula['porcentaje_ocupacion'] ?>%</small>
                                                    <?php if ($matricula['porcentaje_ocupacion'] >= 100): ?>
                                                        <br><span class="badge bg-danger" style="font-size: 0.6rem;">Completa</span>
                                                    <?php elseif ($matricula['porcentaje_ocupacion'] >= 90): ?>
                                                        <br><span class="badge bg-warning" style="font-size: 0.6rem;">Casi llena</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($matricula['fecha_matricula'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                $datos = json_decode($matricula['datos_matricula'], true);
                                                $observaciones = $datos['observaciones'] ?? '';
                                                ?>
                                                <small class="text-muted">
                                                    <?= $observaciones ? htmlspecialchars(substr($observaciones, 0, 50)) . '...' : 'Sin observaciones' ?>
                                                </small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarMatricula(<?= $matricula['id'] ?>)" 
                                                            title="Editar Matrícula">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalleMatricula(<?= $matricula['id'] ?>)" 
                                                            title="Ver Detalle">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <?php if ($matricula['estado'] === 'MATRICULADO'): ?>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                <i class="ti ti-exchange"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $matricula['id'] ?>, 'TRASLADADO')">
                                                                    <i class="ti ti-arrow-right me-2"></i>Trasladar
                                                                </a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $matricula['id'] ?>, 'RETIRADO')">
                                                                    <i class="ti ti-user-minus me-2"></i>Retirar
                                                                </a></li>
                                                                <li><a class="dropdown-item" href="#" onclick="cambiarEstado(<?= $matricula['id'] ?>, 'RESERVADO')">
                                                                    <i class="ti ti-clock me-2"></i>Reservar
                                                                </a></li>
                                                            </ul>
                                                        </div>
                                                    <?php elseif ($matricula['estado'] !== 'MATRICULADO'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="reactivarMatricula(<?= $matricula['id'] ?>)" 
                                                                title="Reactivar Matrícula">
                                                            <i class="ti ti-user-plus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            onclick="imprimirConstancia(<?= $matricula['id'] ?>)" 
                                                            title="Imprimir Constancia">
                                                        <i class="ti ti-printer"></i>
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

                <!-- Resumen por Secciones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Resumen de Capacidad por Secciones</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($secciones as $seccion): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="border rounded p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($seccion['nivel_nombre'] . ' - ' . $seccion['grado'] . $seccion['seccion']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($seccion['aula_asignada'] ?: 'Sin aula') ?></small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold"><?= $seccion['estudiantes_matriculados'] ?>/<?= $seccion['capacidad_maxima'] ?></div>
                                                        <small class="text-muted"><?= $seccion['vacantes_disponibles'] ?> vacantes</small>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <?php 
                                                    $porcentaje = $seccion['capacidad_maxima'] > 0 ? 
                                                        round(($seccion['estudiantes_matriculados'] / $seccion['capacidad_maxima']) * 100, 1) : 0;
                                                    ?>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar <?php
                                                            if ($porcentaje >= 100) echo 'bg-danger';
                                                            elseif ($porcentaje >= 90) echo 'bg-warning';
                                                            else echo 'bg-success';
                                                        ?>" style="width: <?= min($porcentaje, 100) ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= $porcentaje ?>% ocupado</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/matriculas/modal_nueva.php'; ?>
    <?php include 'modales/matriculas/modal_editar.php'; ?>
    <?php include 'modales/matriculas/modal_detalle.php'; ?>

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
        let tablaMatriculas;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaMatriculas = $('#tablaMatriculas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[5, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Filtros personalizados
            $('#filtroPeriodo, #filtroEstado, #filtroTipo, #filtroSeccion').on('change', aplicarFiltros);
            $('#buscarMatricula').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const periodoFiltro = $('#filtroPeriodo').val();
            const estadoFiltro = $('#filtroEstado').val();
            const tipoFiltro = $('#filtroTipo').val();
            const seccionFiltro = $('#filtroSeccion').val();
            const busqueda = $('#buscarMatricula').val().toLowerCase();

            $('#tablaMatriculas tbody tr').each(function() {
                const fila = $(this);
                const periodo = fila.data('periodo');
                const estado = fila.data('estado');
                const tipo = fila.data('tipo');
                const seccion = fila.data('seccion');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (periodoFiltro && periodo != periodoFiltro) mostrar = false;
                if (estadoFiltro && estado !== estadoFiltro) mostrar = false;
                if (tipoFiltro && tipo !== tipoFiltro) mostrar = false;
                if (seccionFiltro && seccion != seccionFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #filtroTipo, #filtroSeccion').val('');
            $('#buscarMatricula').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarMatricula(id) {
            mostrarCarga();
            
            fetch('modales/matriculas/procesar_matriculas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionMatricula(data.matricula);
                    $('#modalEditarMatricula').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la matrícula');
            });
        }

        function verDetalleMatricula(id) {
            mostrarCarga();
            
            fetch('modales/matriculas/procesar_matriculas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetalleMatricula(data.matricula);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalle de la matrícula');
            });
        }

        function cambiarEstado(id, nuevoEstado) {
            let mensaje = '';
            let colorConfirm = '#fd7e14';
            
            switch(nuevoEstado) {
                case 'TRASLADADO':
                    mensaje = '¿Deseas trasladar este estudiante a otra sección?';
                    break;
                case 'RETIRADO':
                    mensaje = '¿Deseas retirar definitivamente este estudiante?';
                    colorConfirm = '#dc3545';
                    break;
                case 'RESERVADO':
                    mensaje = '¿Deseas reservar temporalmente esta matrícula?';
                    colorConfirm = '#0dcaf0';
                    break;
            }

            Swal.fire({
                title: 'Cambiar Estado',
                text: mensaje,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: colorConfirm,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                input: 'textarea',
                inputPlaceholder: 'Motivo del cambio (opcional)',
                inputAttributes: {
                    'maxlength': 500,
                    'rows': 3
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarCambioEstado(id, nuevoEstado, result.value || '');
                }
            });
        }

        function ejecutarCambioEstado(id, estado, motivo) {
            mostrarCarga();

            fetch('modales/matriculas/procesar_matriculas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=cambiar_estado&id=${id}&estado=${estado}&motivo=${encodeURIComponent(motivo)}`
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
                mostrarError('Error al cambiar estado de la matrícula');
            });
        }

        function reactivarMatricula(id) {
            Swal.fire({
                title: 'Reactivar Matrícula',
                text: '¿Deseas reactivar esta matrícula como MATRICULADO?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarCambioEstado(id, 'MATRICULADO', 'Reactivación de matrícula');
                }
            });
        }

        function imprimirConstancia(id) {
            mostrarCarga();
            
            fetch('modales/matriculas/procesar_matriculas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=generar_constancia&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    window.open(data.url_pdf, '_blank');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al generar constancia');
            });
        }

        function mostrarDetalleMatricula(matricula) {
            const ocupacionPorcentaje = matricula.capacidad_maxima > 0 ? 
                Math.round((matricula.companeros_seccion / matricula.capacidad_maxima) * 100) : 0;
            
            Swal.fire({
                title: `Matrícula: ${matricula.codigo_matricula}`,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Estudiante:</strong><br>
                                <small>${matricula.estudiante_nombres} ${matricula.estudiante_apellidos}</small><br>
                                <small>Código: ${matricula.codigo_estudiante}</small><br>
                                <small>Documento: ${matricula.documento_numero}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Sección:</strong><br>
                                <small>${matricula.nivel_nombre} - ${matricula.grado}${matricula.seccion}</small><br>
                                <small>Aula: ${matricula.aula_asignada || 'Sin asignar'}</small><br>
                                <small>Capacidad: ${matricula.companeros_seccion}/${matricula.capacidad_maxima} (${ocupacionPorcentaje}%)</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Estado:</strong> <span class="badge bg-primary">${matricula.estado}</span><br>
                                <strong>Tipo:</strong> <span class="badge bg-secondary">${matricula.tipo_matricula}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha Matrícula:</strong><br>
                                <small>${new Date(matricula.fecha_matricula).toLocaleDateString('es-ES')}</small>
                            </div>
                        </div>
                        ${matricula.observaciones ? `<hr><strong>Observaciones:</strong><br><small>${matricula.observaciones}</small>` : ''}
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        // Función para generar código automático de matrícula
        function generarCodigoMatricula(periodo_id, tipo_matricula) {
            const año = new Date().getFullYear();
            const prefijo = tipo_matricula === 'NUEVO' ? 'MAT' : 
                           tipo_matricula === 'CONTINUADOR' ? 'CON' : 'TRA';
            const timestamp = Date.now().toString().slice(-4);
            
            return `${prefijo}${año}${timestamp}`;
        }

        // Validar capacidad de sección antes de matricular
        function validarCapacidadSeccion(seccion_id) {
            return fetch('modales/matriculas/procesar_matriculas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=validar_capacidad&seccion_id=${seccion_id}`
            })
            .then(response => response.json());
        }

        function exportarMatricula() {
            window.open('reportes/exportar_matricula.php', '_blank');
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

        function mostrarAdvertencia(mensaje) {
            Swal.fire({
                title: 'Advertencia',
                text: mensaje,
                icon: 'warning',
                confirmButtonColor: '#fd7e14'
            });
        }
    </script>
</body>
</html>