<?php 
require_once 'conexion/bd.php';

// Obtener asignaciones con horarios y datos relacionados
try {
    $sql = "SELECT ad.*, 
                   d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.codigo_docente,
                   s.grado, s.seccion, s.aula_asignada, s.codigo as seccion_codigo,
                   ne.nombre as nivel_nombre,
                   ac.nombre as area_nombre, ac.codigo as area_codigo,
                   pa.nombre as periodo_nombre
            FROM asignaciones_docentes ad
            INNER JOIN docentes d ON ad.docente_id = d.id
            INNER JOIN secciones s ON ad.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
            WHERE ad.activo = 1 AND pa.actual = 1
            ORDER BY d.apellidos, d.nombres, ac.nombre";
    
    $stmt_asignaciones = $conexion->prepare($sql);
    $stmt_asignaciones->execute();
    $asignaciones = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asignaciones = [];
    $error_asignaciones = "Error al cargar asignaciones: " . $e->getMessage();
}

// Obtener lista de docentes para filtros
try {
    $stmt_docentes = $conexion->prepare("SELECT DISTINCT d.id, d.nombres, d.apellidos, d.codigo_docente 
                                        FROM docentes d 
                                        INNER JOIN asignaciones_docentes ad ON d.id = ad.docente_id 
                                        WHERE d.activo = 1 AND ad.activo = 1
                                        ORDER BY d.apellidos, d.nombres");
    $stmt_docentes->execute();
    $docentes = $stmt_docentes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $docentes = [];
}

// Obtener áreas curriculares para filtros
try {
    $stmt_areas = $conexion->prepare("SELECT * FROM areas_curriculares WHERE activo = 1 ORDER BY nombre");
    $stmt_areas->execute();
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $areas = [];
}

// Procesar horarios y detectar cruces
$horarios_procesados = [];
$cruces_horarios = [];
$carga_docentes = [];

foreach ($asignaciones as $asignacion) {
    $docente_id = $asignacion['docente_id'];
    $horarios_json = json_decode($asignacion['horarios'], true) ?: [];
    
    // Inicializar carga del docente
    if (!isset($carga_docentes[$docente_id])) {
        $carga_docentes[$docente_id] = [
            'docente' => $asignacion['docente_nombres'] . ' ' . $asignacion['docente_apellidos'],
            'codigo' => $asignacion['codigo_docente'],
            'total_horas' => 0,
            'asignaciones' => 0,
            'es_tutor' => false,
            'areas' => []
        ];
    }
    
    $carga_docentes[$docente_id]['total_horas'] += $asignacion['horas_semanales'];
    $carga_docentes[$docente_id]['asignaciones']++;
    if ($asignacion['es_tutor']) {
        $carga_docentes[$docente_id]['es_tutor'] = true;
    }
    
    if (!in_array($asignacion['area_nombre'], $carga_docentes[$docente_id]['areas'])) {
        $carga_docentes[$docente_id]['areas'][] = $asignacion['area_nombre'];
    }
    
    foreach ($horarios_json as $horario) {
        $horario_item = [
            'asignacion_id' => $asignacion['id'],
            'docente_id' => $docente_id,
            'docente_nombre' => $asignacion['docente_nombres'] . ' ' . $asignacion['docente_apellidos'],
            'area_nombre' => $asignacion['area_nombre'],
            'seccion' => $asignacion['grado'] . ' ' . $asignacion['seccion'],
            'aula' => $horario['aula'] ?? $asignacion['aula_asignada'],
            'dia' => $horario['dia'],
            'hora_inicio' => $horario['hora_inicio'],
            'hora_fin' => $horario['hora_fin'],
            'nivel' => $asignacion['nivel_nombre']
        ];
        
        $horarios_procesados[] = $horario_item;
        
        // Detectar cruces horarios para el mismo docente
        foreach ($horarios_procesados as $h_existente) {
            if ($h_existente['docente_id'] == $docente_id && 
                $h_existente['dia'] == $horario['dia'] &&
                $h_existente['asignacion_id'] != $asignacion['id']) {
                
                // Verificar solapamiento de horarios
                $inicio_actual = strtotime($horario['hora_inicio']);
                $fin_actual = strtotime($horario['hora_fin']);
                $inicio_existente = strtotime($h_existente['hora_inicio']);
                $fin_existente = strtotime($h_existente['hora_fin']);
                
                if (($inicio_actual < $fin_existente && $fin_actual > $inicio_existente)) {
                    $cruces_horarios[] = [
                        'docente' => $asignacion['docente_nombres'] . ' ' . $asignacion['docente_apellidos'],
                        'dia' => $horario['dia'],
                        'horario1' => $horario['hora_inicio'] . '-' . $horario['hora_fin'] . ' (' . $asignacion['area_nombre'] . ')',
                        'horario2' => $h_existente['hora_inicio'] . '-' . $h_existente['hora_fin'] . ' (' . $h_existente['area_nombre'] . ')',
                        'aula1' => $horario['aula'],
                        'aula2' => $h_existente['aula']
                    ];
                }
            }
        }
    }
}

// Calcular estadísticas
$total_docentes = count($carga_docentes);
$total_horas_semanales = array_sum(array_column($carga_docentes, 'total_horas'));
$promedio_horas = $total_docentes > 0 ? round($total_horas_semanales / $total_docentes, 1) : 0;
$docentes_sobrecargados = count(array_filter($carga_docentes, function($d) { return $d['total_horas'] > 30; }));

// Días de la semana
$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes', 
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];

// Horas del día (bloque escolar típico)
$horas_dia = [
    '08:00', '08:45', '09:30', '10:15', '11:00', '11:45', '12:30', '13:15', '14:00', '14:45', '15:30', '16:15'
];
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Horarios Docentes - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <style>
      /* Estilos base heredados del sistema */
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
      
      .btn:focus,
      .nav-link:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
      }

      /* CSS para left-sidebar */
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

      .left-sidebar > div {
        height: 100vh !important;
        display: flex;
        flex-direction: column;
        margin: 0 !important;
        padding: 0 !important;
      }

      .left-sidebar .brand-logo {
        flex-shrink: 0;
        padding: 20px 24px;
        margin: 0 !important;
        border-bottom: 1px solid #e9ecef;
      }

      .left-sidebar .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        margin: 0 !important;
        padding: 0 !important;
      }

      .left-sidebar #sidebarnav {
        margin: 0 !important;
        padding: 0 !important;
        list-style: none;
      }

      .left-sidebar .sidebar-item {
        margin: 0 !important;
        padding: 0 !important;
      }

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

      .left-sidebar .sidebar-link:hover {
        background-color: #f8f9fa !important;
        color: #0d6efd !important;
      }

      .left-sidebar .sidebar-link.active {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        font-weight: 500;
      }

      .left-sidebar .nav-small-cap {
        padding: 20px 24px 8px 24px !important;
        margin: 0 !important;
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .left-sidebar .sidebar-divider {
        margin: 16px 24px !important;
        height: 1px;
        background-color: #e9ecef;
        border: none;
      }

      .left-sidebar .badge {
        font-size: 0.625rem !important;
        padding: 4px 8px !important;
      }

      .left-sidebar .collapse {
        margin: 0 !important;
        padding: 0 !important;
      }

      .left-sidebar .first-level .sidebar-item .sidebar-link {
        padding-left: 48px !important;
        font-size: 0.875rem;
      }

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

      .left-sidebar .sidebar-nav {
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.1) transparent;
      }

      @media (max-width: 1199.98px) {
        .left-sidebar {
          margin-left: -270px;
          transition: margin-left 0.25s ease;
        }
        
        .left-sidebar.show {
          margin-left: 0;
        }
      }

      .mini-sidebar .left-sidebar {
        width: 80px !important;
      }

      .mini-sidebar .left-sidebar .hide-menu {
        display: none !important;
      }

      .mini-sidebar .left-sidebar .brand-logo img {
        width: 40px !important;
      }
      
      @media (prefers-reduced-motion: reduce) {
        .card {
          transition: none;
        }
      }
    </style>
    <style>
        /* Estilos específicos para horarios */
        .horario-grid {
            min-height: 500px;
            overflow-x: auto;
        }
        .horario-cell {
            min-height: 60px;
            border: 1px solid #dee2e6;
            position: relative;
            vertical-align: top;
            padding: 4px;
        }
        .horario-bloque {
            background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);
            color: white;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-bottom: 2px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .horario-bloque:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        .horario-bloque.area-matematica { background: linear-gradient(135deg, #2196F3 0%, #64B5F6 100%); }
        .horario-bloque.area-comunicacion { background: linear-gradient(135deg, #FF9800 0%, #FFB74D 100%); }
        .horario-bloque.area-ciencia { background: linear-gradient(135deg, #9C27B0 0%, #BA68C8 100%); }
        .horario-bloque.area-social { background: linear-gradient(135deg, #795548 0%, #A1887F 100%); }
        .horario-bloque.area-arte { background: linear-gradient(135deg, #E91E63 0%, #F06292 100%); }
        .horario-bloque.area-educacion { background: linear-gradient(135deg, #607D8B 0%, #90A4AE 100%); }
        .horario-bloque.tutor { border: 2px solid #FFD700; }
        
        .hora-label {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            font-size: 0.8rem;
            color: #495057;
        }
        .dia-label {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: center;
            color: #495057;
        }
        
        .carga-docente {
            line-height: 1.3;
        }
        .carga-nombre {
            font-weight: 600;
            color: #495057;
        }
        .carga-detalle {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .carga-horas {
            font-weight: 600;
        }
        .carga-normal { color: #28a745; }
        .carga-alta { color: #fd7e14; }
        .carga-sobrecarga { color: #dc3545; }
        
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
        
        .vista-toggle {
            border-radius: 25px;
        }
        .vista-toggle.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .cruce-alerta {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin: 0.25rem 0;
        }
        .cruce-titulo {
            font-weight: 600;
            color: #856404;
        }
        .cruce-detalle {
            font-size: 0.9rem;
            color: #664d03;
        }
        
        .docente-individual {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0 0.375rem 0.375rem 0;
        }
        
        .areas-badge {
            font-size: 0.65rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Horarios Docentes</h4>
                                <p class="mb-0 text-muted">Control de horarios académicos y carga docente</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="btn-group vista-toggle" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="vistaGrilla" onclick="cambiarVista('grilla')">
                                        <i class="ti ti-grid-dots me-1"></i> Grilla
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="vistaLista" onclick="cambiarVista('lista')">
                                        <i class="ti ti-list me-1"></i> Lista
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="vistaDocente" onclick="cambiarVista('docente')">
                                        <i class="ti ti-user me-1"></i> Por Docente
                                    </button>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditarHorario">
                                    <i class="ti ti-edit me-2"></i>
                                    Editar Horarios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Cruces Horarios -->
                <?php if (!empty($cruces_horarios)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="ti ti-alert-triangle me-2"></i>Cruces de Horarios Detectados</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach (array_slice($cruces_horarios, 0, 5) as $cruce): ?>
                                    <div class="cruce-alerta">
                                        <div class="cruce-titulo"><?= htmlspecialchars($cruce['docente']) ?> - <?= $dias_semana[$cruce['dia']] ?></div>
                                        <div class="cruce-detalle">
                                            Conflicto: <?= $cruce['horario1'] ?> vs <?= $cruce['horario2'] ?><br>
                                            Aulas: <?= $cruce['aula1'] ?> y <?= $cruce['aula2'] ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($cruces_horarios) > 5): ?>
                                    <div class="text-muted mt-2">
                                        <small>Y <?= count($cruces_horarios) - 5 ?> cruces adicionales...</small>
                                    </div>
                                <?php endif; ?>
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
                                <label class="form-label">Filtrar por Docente</label>
                                <select class="form-select" id="filtroDocente">
                                    <option value="">Todos los docentes</option>
                                    <?php foreach ($docentes as $docente): ?>
                                        <option value="<?= $docente['id'] ?>">
                                            <?= htmlspecialchars($docente['apellidos'] . ', ' . $docente['nombres']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Área</label>
                                <select class="form-select" id="filtroArea">
                                    <option value="">Todas las áreas</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Día</label>
                                <select class="form-select" id="filtroDia">
                                    <option value="">Todos</option>
                                    <?php foreach ($dias_semana as $num => $nombre): ?>
                                        <option value="<?= $num ?>"><?= $nombre ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Carga Horaria</label>
                                <select class="form-select" id="filtroCarga">
                                    <option value="">Todas</option>
                                    <option value="baja">Baja (≤20h)</option>
                                    <option value="normal">Normal (21-30h)</option>
                                    <option value="alta">Alta (>30h)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarHorario" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarNiveles()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Grilla de Horarios -->
                <div class="card" id="contenedorGrilla">
                    <div class="card-header">
                        <h5 class="mb-0">Grilla de Horarios Semanal</h5>
                    </div>
                    <div class="card-body">
                        <div class="horario-grid">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th class="hora-label">Hora</th>
                                            <?php foreach ($dias_semana as $dia_num => $dia_nombre): ?>
                                                <th class="dia-label"><?= $dia_nombre ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($h = 0; $h < count($horas_dia) - 1; $h++): ?>
                                            <tr>
                                                <td class="hora-label">
                                                    <?= $horas_dia[$h] ?> - <?= $horas_dia[$h + 1] ?>
                                                </td>
                                                <?php foreach ($dias_semana as $dia_num => $dia_nombre): ?>
                                                    <td class="horario-cell">
                                                        <?php
                                                        $hora_inicio = $horas_dia[$h];
                                                        $hora_fin = $horas_dia[$h + 1];
                                                        
                                                        foreach ($horarios_procesados as $horario) {
                                                            if ($horario['dia'] == $dia_num && 
                                                                $horario['hora_inicio'] <= $hora_inicio && 
                                                                $horario['hora_fin'] >= $hora_fin) {
                                                                
                                                                $area_class = '';
                                                                switch (strtolower($horario['area_nombre'])) {
                                                                    case 'matemática': $area_class = 'area-matematica'; break;
                                                                    case 'comunicación': $area_class = 'area-comunicacion'; break;
                                                                    case 'ciencia y tecnología': $area_class = 'area-ciencia'; break;
                                                                    case 'personal social': $area_class = 'area-social'; break;
                                                                    case 'arte y cultura': $area_class = 'area-arte'; break;
                                                                    default: $area_class = 'area-educacion';
                                                                }
                                                                ?>
                                                                <div class="horario-bloque <?= $area_class ?>" 
                                                                     onclick="verDetalleHorario(<?= $horario['asignacion_id'] ?>)"
                                                                     title="<?= htmlspecialchars($horario['docente_nombre'] . ' - ' . $horario['area_nombre']) ?>">
                                                                    <div style="font-weight: 600;"><?= htmlspecialchars($horario['area_nombre']) ?></div>
                                                                    <div style="font-size: 0.7rem;"><?= htmlspecialchars($horario['docente_nombre']) ?></div>
                                                                    <div style="font-size: 0.65rem;"><?= htmlspecialchars($horario['seccion']) ?></div>
                                                                    <div style="font-size: 0.65rem;"><?= htmlspecialchars($horario['aula']) ?></div>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Lista de Carga Docente -->
                <div class="card" id="contenedorLista" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Carga Horaria por Docente</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaCargaDocente">
                                <thead class="table-light">
                                    <tr>
                                        <th>Docente</th>
                                        <th>Áreas Asignadas</th>
                                        <th>Total Horas</th>
                                        <th>Asignaciones</th>
                                        <th>Rol Adicional</th>
                                        <th>Estado Carga</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($carga_docentes as $docente_id => $carga): ?>
                                        <tr data-docente="<?= $docente_id ?>" data-carga="<?= $carga['total_horas'] ?>">
                                            <td>
                                                <div class="carga-docente">
                                                    <div class="carga-nombre"><?= htmlspecialchars($carga['docente']) ?></div>
                                                    <div class="carga-detalle">Código: <?= htmlspecialchars($carga['codigo']) ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php foreach ($carga['areas'] as $area): ?>
                                                    <span class="badge bg-secondary areas-badge me-1"><?= htmlspecialchars($area) ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <span class="carga-horas <?php
                                                    if ($carga['total_horas'] <= 20) echo 'carga-normal';
                                                    elseif ($carga['total_horas'] <= 30) echo 'carga-normal';
                                                    else echo 'carga-sobrecarga';
                                                ?>">
                                                    <?= $carga['total_horas'] ?>h
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= $carga['asignaciones'] ?></span>
                                            </td>
                                            <td>
                                                <?php if ($carga['es_tutor']): ?>
                                                    <span class="badge bg-warning text-dark">Tutor</span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($carga['total_horas'] <= 20): ?>
                                                    <span class="badge bg-success">Normal</span>
                                                <?php elseif ($carga['total_horas'] <= 30): ?>
                                                    <span class="badge bg-warning text-dark">Media</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Sobrecarga</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="verHorarioDocente(<?= $docente_id ?>)" 
                                                            title="Ver Horario">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="editarHorarioDocente(<?= $docente_id ?>)" 
                                                            title="Editar Horario">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="exportarHorarioDocente(<?= $docente_id ?>)" 
                                                            title="Exportar">
                                                        <i class="ti ti-download"></i>
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

                <!-- Vista Individual por Docente -->
                <div class="card" id="contenedorDocente" style="display: none;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Horario Individual por Docente</h5>
                            <select class="form-select w-auto" id="selectorDocenteIndividual">
                                <option value="">Seleccionar docente...</option>
                                <?php foreach ($docentes as $docente): ?>
                                    <option value="<?= $docente['id'] ?>">
                                        <?= htmlspecialchars($docente['apellidos'] . ', ' . $docente['nombres']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="horarioDocenteIndividual">
                            <p class="text-muted text-center">Selecciona un docente para ver su horario individual</p>
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
    <?php include 'modales/horarios/modal_editar.php'; ?>
    <?php include 'modales/horarios/modal_detalle.php'; ?>
    <?php include 'modales/horarios/modal_validacion.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaCargaDocente;
        const horariosData = <?= json_encode($horarios_procesados) ?>;
        const cargaDocentes = <?= json_encode($carga_docentes) ?>;
        const diasSemana = <?= json_encode($dias_semana) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaCargaDocente = $('#tablaCargaDocente').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros
            $('#filtroDocente, #filtroArea, #filtroDia, #filtroCarga').on('change', aplicarFiltros);
            $('#buscarHorario').on('keyup', aplicarFiltros);
            
            // Selector de docente individual
            $('#selectorDocenteIndividual').on('change', function() {
                const docenteId = $(this).val();
                if (docenteId) {
                    cargarHorarioIndividual(docenteId);
                } else {
                    $('#horarioDocenteIndividual').html('<p class="text-muted text-center">Selecciona un docente para ver su horario individual</p>');
                }
            });
        });

        function cambiarVista(vista) {
            // Ocultar todas las vistas
            $('#contenedorGrilla, #contenedorLista, #contenedorDocente').hide();
            $('.vista-toggle .btn').removeClass('active');

            // Mostrar vista seleccionada
            switch(vista) {
                case 'grilla':
                    $('#contenedorGrilla').show();
                    $('#vistaGrilla').addClass('active');
                    break;
                case 'lista':
                    $('#contenedorLista').show();
                    $('#vistaLista').addClass('active');
                    break;
                case 'docente':
                    $('#contenedorDocente').show();
                    $('#vistaDocente').addClass('active');
                    break;
            }
        }

        function aplicarFiltros() {
            const docenteFiltro = $('#filtroDocente').val();
            const areaFiltro = $('#filtroArea').val();
            const diaFiltro = $('#filtroDia').val();
            const cargaFiltro = $('#filtroCarga').val();
            const busqueda = $('#buscarHorario').val().toLowerCase();

            // Filtrar tabla de carga docente
            $('#tablaCargaDocente tbody tr').each(function() {
                const fila = $(this);
                const docenteId = fila.data('docente');
                const carga = parseInt(fila.data('carga'));
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (docenteFiltro && docenteId != docenteFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;
                
                if (cargaFiltro) {
                    switch(cargaFiltro) {
                        case 'baja': 
                            if (carga > 20) mostrar = false;
                            break;
                        case 'normal':
                            if (carga <= 20 || carga > 30) mostrar = false;
                            break;
                        case 'alta':
                            if (carga <= 30) mostrar = false;
                            break;
                    }
                }

                fila.toggle(mostrar);
            });

            // Filtrar grilla de horarios
            filtrarGrillaHorarios(docenteFiltro, areaFiltro, diaFiltro);
        }

        function filtrarGrillaHorarios(docenteFiltro, areaFiltro, diaFiltro) {
            $('.horario-bloque').each(function() {
                const bloque = $(this);
                const docenteNombre = bloque.attr('title');
                let mostrar = true;

                // Aquí implementarías la lógica de filtrado según los IDs
                // Por simplicidad, mostramos todos si no hay filtros específicos
                
                bloque.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroDocente, #filtroArea, #filtroDia, #filtroCarga').val('');
            $('#buscarHorario').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function verDetalleHorario(asignacionId) {
            mostrarCarga();
            
            fetch('modales/horarios/procesar_horarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalle&id=${asignacionId}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetalleHorario(data.asignacion);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalle del horario');
            });
        }

        function mostrarDetalleHorario(asignacion) {
            const horarios = JSON.parse(asignacion.horarios || '[]');
            let horariosHtml = '';
            
            horarios.forEach(h => {
                horariosHtml += `<p><strong>${diasSemana[h.dia]}:</strong> ${h.hora_inicio} - ${h.hora_fin} (${h.aula})</p>`;
            });

            Swal.fire({
                title: `${asignacion.area_nombre} - ${asignacion.grado} ${asignacion.seccion}`,
                html: `
                    <div class="text-left">
                        <p><strong>Docente:</strong> ${asignacion.docente_nombre}</p>
                        <p><strong>Horas Semanales:</strong> ${asignacion.horas_semanales}h</p>
                        <p><strong>Es Tutor:</strong> ${asignacion.es_tutor ? 'Sí' : 'No'}</p>
                        <hr>
                        <h6>Horarios:</h6>
                        ${horariosHtml || '<p class="text-muted">Sin horarios asignados</p>'}
                    </div>
                `,
                width: '500px',
                confirmButtonText: 'Cerrar'
            });
        }

        function verHorarioDocente(docenteId) {
            $('#selectorDocenteIndividual').val(docenteId);
            cambiarVista('docente');
            cargarHorarioIndividual(docenteId);
        }

        function cargarHorarioIndividual(docenteId) {
            mostrarCarga();
            
            fetch('modales/horarios/procesar_horarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=horario_docente&docente_id=${docenteId}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    generarHorarioIndividual(data.docente, data.horarios);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar horario del docente');
            });
        }

        function generarHorarioIndividual(docente, horarios) {
            let html = `
                <div class="docente-individual mb-4">
                    <h5>${docente.nombres} ${docente.apellidos}</h5>
                    <p class="mb-0">Código: ${docente.codigo} | Carga: ${docente.total_horas}h semanales</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Hora</th>`;
            
            Object.values(diasSemana).forEach(dia => {
                html += `<th class="dia-label">${dia}</th>`;
            });
            
            html += `</tr></thead><tbody>`;

            // Generar filas de horario
            const horasRango = ['08:00-08:45', '08:45-09:30', '09:30-10:15', '10:15-11:00', '11:00-11:45', 
                              '11:45-12:30', '12:30-13:15', '13:15-14:00', '14:00-14:45', '14:45-15:30'];

            horasRango.forEach(rango => {
                html += `<tr><td class="hora-label">${rango}</td>`;
                
                Object.keys(diasSemana).forEach(diaNum => {
                    html += '<td class="horario-cell">';
                    
                    const [inicio, fin] = rango.split('-');
                    const horarioEncontrado = horarios.find(h => 
                        h.dia == diaNum && h.hora_inicio <= inicio && h.hora_fin >= fin
                    );
                    
                    if (horarioEncontrado) {
                        html += `
                            <div class="horario-bloque">
                                <strong>${horarioEncontrado.area_nombre}</strong><br>
                                <small>${horarioEncontrado.seccion}</small><br>
                                <small>${horarioEncontrado.aula}</small>
                            </div>
                        `;
                    }
                    
                    html += '</td>';
                });
                
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            
            $('#horarioDocenteIndividual').html(html);
        }

        function editarHorarioDocente(docenteId) {
            // Implementar edición de horarios
            $('#modalEditarHorario').modal('show');
        }

        function exportarHorarios() {
            window.open('reportes/exportar_horarios.php', '_blank');
        }

        function exportarHorarioDocente(docenteId) {
            window.open(`reportes/exportar_horario_docente.php?docente_id=${docenteId}`, '_blank');
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