<?php 
require_once 'conexion/bd.php';

// Obtener asignaciones con información completa
try {
    $sql = "SELECT a.*, 
                   d.nombres as docente_nombres, d.apellidos as docente_apellidos, d.codigo_docente,
                   s.grado, s.seccion, s.codigo as seccion_codigo, s.aula_asignada,
                   ne.nombre as nivel_nombre,
                   ac.nombre as area_nombre, ac.codigo as area_codigo,
                   pa.nombre as periodo_nombre, pa.anio,
                   COUNT(m.id) as total_estudiantes
            FROM asignaciones_docentes a
            INNER JOIN docentes d ON a.docente_id = d.id
            INNER JOIN secciones s ON a.seccion_id = s.id
            INNER JOIN niveles_educativos ne ON s.nivel_id = ne.id
            INNER JOIN areas_curriculares ac ON a.area_id = ac.id
            INNER JOIN periodos_academicos pa ON a.periodo_academico_id = pa.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1 AND m.estado = 'MATRICULADO'
            WHERE a.activo = 1
            GROUP BY a.id
            ORDER BY d.apellidos, d.nombres, s.grado, s.seccion";
    
    $stmt_asignaciones = $conexion->prepare($sql);
    $stmt_asignaciones->execute();
    $asignaciones = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asignaciones = [];
    $error_asignaciones = "Error al cargar asignaciones: " . $e->getMessage();
}

// Obtener docentes para filtros y selects
try {
    $stmt_docentes = $conexion->prepare("SELECT id, codigo_docente, nombres, apellidos FROM docentes WHERE activo = 1 ORDER BY apellidos, nombres");
    $stmt_docentes->execute();
    $docentes = $stmt_docentes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $docentes = [];
}

// Obtener áreas curriculares
try {
    $stmt_areas = $conexion->prepare("SELECT * FROM areas_curriculares WHERE activo = 1 ORDER BY nombre");
    $stmt_areas->execute();
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $areas = [];
}

// Obtener niveles educativos
try {
    $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden");
    $stmt_niveles->execute();
    $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $niveles = [];
}

// Obtener período académico actual
try {
    $stmt_periodo = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 AND actual = 1 LIMIT 1");
    $stmt_periodo->execute();
    $periodo_actual = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodo_actual = null;
}

// Calcular estadísticas y validaciones de negocio
$total_asignaciones = count($asignaciones);
$tutores_asignados = count(array_filter($asignaciones, function($a) { return $a['es_tutor']; }));
$docentes_activos = count(array_unique(array_column($asignaciones, 'docente_id')));

// Calcular carga académica promedio
$carga_total = array_sum(array_column($asignaciones, 'horas_semanales'));
$carga_promedio = $docentes_activos > 0 ? round($carga_total / $docentes_activos, 1) : 0;

// Detectar conflictos y validaciones
$conflictos_horarios = [];
$secciones_sin_tutor = [];
$docentes_sobrecargados = [];

// Validar tutores únicos por sección
$secciones_con_tutor = [];
foreach ($asignaciones as $asignacion) {
    if ($asignacion['es_tutor']) {
        $seccion_id = $asignacion['seccion_id'];
        if (isset($secciones_con_tutor[$seccion_id])) {
            $conflictos_horarios[] = "Sección {$asignacion['grado']}-{$asignacion['seccion']} tiene múltiples tutores";
        } else {
            $secciones_con_tutor[$seccion_id] = true;
        }
    }
}

// Calcular carga por docente
$carga_por_docente = [];
foreach ($asignaciones as $asignacion) {
    $docente_id = $asignacion['docente_id'];
    if (!isset($carga_por_docente[$docente_id])) {
        $carga_por_docente[$docente_id] = [
            'nombre' => $asignacion['docente_nombres'] . ' ' . $asignacion['docente_apellidos'],
            'horas' => 0,
            'asignaciones' => 0
        ];
    }
    $carga_por_docente[$docente_id]['horas'] += $asignacion['horas_semanales'];
    $carga_por_docente[$docente_id]['asignaciones']++;
}

// Detectar docentes sobrecargados (más de 30 horas)
foreach ($carga_por_docente as $docente_id => $info) {
    if ($info['horas'] > 30) {
        $docentes_sobrecargados[] = $info['nombre'] . " ({$info['horas']} horas)";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignaciones Docentes - ANDRÉS AVELINO CÁCERES</title>
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
        .docente-info {
            line-height: 1.3;
        }
        .docente-nombre {
            font-weight: 600;
            color: #495057;
        }
        .docente-codigo {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .seccion-info {
            line-height: 1.3;
        }
        .seccion-nombre {
            font-weight: 600;
            color: #495057;
        }
        .seccion-detalles {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .tutor-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .carga-horaria {
            text-align: center;
        }
        .carga-numero {
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
        .horario-preview {
            font-size: 0.8rem;
            max-height: 60px;
            overflow-y: auto;
        }
        .horario-item {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 0.2rem 0.4rem;
            margin: 0.1rem;
            display: inline-block;
            font-size: 0.7rem;
        }
        .conflicto-alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .area-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .estudiantes-count {
            text-align: center;
            font-size: 0.9rem;
        }
        .estudiantes-numero {
            font-weight: 600;
            color: #495057;
        }
        .sobrecarga-warning {
            background-color: #f8d7da;
            color: #721c24;
        }
        .carga-normal {
            color: #198754;
        }
        .carga-alta {
            color: #fd7e14;
        }
        .carga-critica {
            color: #dc3545;
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
                                <h4 class="fw-bold mb-0">Asignaciones Docentes</h4>
                                <p class="mb-0 text-muted">Gestión de carga académica y asignación de tutorías</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-warning" onclick="detectarConflictos()">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    Detectar Conflictos
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarDocente">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Asignación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Validación -->
                <?php if (!empty($conflictos_horarios) || !empty($docentes_sobrecargados)): ?>
                <div class="conflicto-alert">
                    <h6 class="text-warning mb-2">
                        <i class="ti ti-alert-triangle me-1"></i>
                        Advertencias del Sistema
                    </h6>
                    <?php if (!empty($conflictos_horarios)): ?>
                        <div class="mb-2">
                            <strong>Conflictos de Tutoría:</strong>
                            <ul class="mb-0">
                                <?php foreach ($conflictos_horarios as $conflicto): ?>
                                    <li><?= htmlspecialchars($conflicto) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($docentes_sobrecargados)): ?>
                        <div>
                            <strong>Docentes Sobrecargados (>30h):</strong>
                            <ul class="mb-0">
                                <?php foreach ($docentes_sobrecargados as $docente): ?>
                                    <li><?= htmlspecialchars($docente) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Docente</label>
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
                                <label class="form-label">Área Curricular</label>
                                <select class="form-select" id="filtroArea">
                                    <option value="">Todas las áreas</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
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
                                <label class="form-label">Tutoría</label>
                                <select class="form-select" id="filtroTutor">
                                    <option value="">Todos</option>
                                    <option value="1">Solo Tutores</option>
                                    <option value="0">Sin Tutoría</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Carga Horaria</label>
                                <select class="form-select" id="filtroCarga">
                                    <option value="">Todas</option>
                                    <option value="baja">Baja (1-10h)</option>
                                    <option value="normal">Normal (11-20h)</option>
                                    <option value="alta">Alta (21-30h)</option>
                                    <option value="critica">Crítica (>30h)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarAsignacion" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarAsignaciones()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Asignaciones -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Asignaciones Docentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaAsignaciones">
                                <thead class="table-light">
                                    <tr>
                                        <th>Docente</th>
                                        <th>Sección</th>
                                        <th>Área Curricular</th>
                                        <th>Carga Horaria</th>
                                        <th>Estudiantes</th>
                                        <th>Tutoría</th>
                                        <th>Horarios</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asignaciones as $asignacion): 
                                        // Determinar clase de carga horaria
                                        $carga_clase = 'carga-normal';
                                        if ($asignacion['horas_semanales'] <= 10) {
                                            $carga_clase = 'carga-normal';
                                        } elseif ($asignacion['horas_semanales'] <= 20) {
                                            $carga_clase = 'carga-normal';
                                        } elseif ($asignacion['horas_semanales'] <= 30) {
                                            $carga_clase = 'carga-alta';
                                        } else {
                                            $carga_clase = 'carga-critica';
                                        }

                                        // Obtener nivel del docente
                                        $nivel_seccion = '';
                                        foreach ($niveles as $nivel) {
                                            if ($nivel['id'] == $asignacion['seccion_id']) { // Este es un aproximado, necesitaríamos JOIN
                                                $nivel_seccion = $nivel['id'];
                                                break;
                                            }
                                        }
                                    ?>
                                        <tr data-docente="<?= $asignacion['docente_id'] ?>" 
                                            data-area="<?= $asignacion['area_id'] ?>"
                                            data-nivel="<?= $nivel_seccion ?>"
                                            data-tutor="<?= $asignacion['es_tutor'] ? 1 : 0 ?>"
                                            data-carga="<?= $asignacion['horas_semanales'] ?>">
                                            <td>
                                                <div class="docente-info">
                                                    <div class="docente-nombre">
                                                        <?= htmlspecialchars($asignacion['docente_apellidos'] . ', ' . $asignacion['docente_nombres']) ?>
                                                    </div>
                                                    <div class="docente-codigo">
                                                        Código: <?= htmlspecialchars($asignacion['codigo_docente']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="seccion-info">
                                                    <div class="seccion-nombre">
                                                        <?= htmlspecialchars($asignacion['nivel_nombre']) ?>
                                                    </div>
                                                    <div class="seccion-detalles">
                                                        <?= htmlspecialchars($asignacion['grado'] . ' - ' . $asignacion['seccion']) ?><br>
                                                        <small class="text-muted"><?= htmlspecialchars($asignacion['aula_asignada']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge area-badge bg-primary">
                                                    <?= htmlspecialchars($asignacion['area_codigo']) ?>
                                                </span>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($asignacion['area_nombre']) ?></small>
                                            </td>
                                            <td>
                                                <div class="carga-horaria">
                                                    <div class="carga-numero <?= $carga_clase ?>">
                                                        <?= $asignacion['horas_semanales'] ?>h
                                                    </div>
                                                    <small class="text-muted">por semana</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="estudiantes-count">
                                                    <div class="estudiantes-numero"><?= $asignacion['total_estudiantes'] ?></div>
                                                    <small class="text-muted">estudiantes</small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($asignacion['es_tutor']): ?>
                                                    <span class="badge tutor-badge bg-success">
                                                        <i class="ti ti-user-star me-1"></i>
                                                        Tutor
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge tutor-badge bg-secondary">Docente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="horario-preview">
                                                    <?php 
                                                    $horarios = json_decode($asignacion['horarios'], true) ?: [];
                                                    $dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                                                    
                                                    foreach ($horarios as $horario): 
                                                        $dia_nombre = $dias_semana[$horario['dia'] - 1] ?? 'Día ' . $horario['dia'];
                                                    ?>
                                                        <div class="horario-item">
                                                            <?= $dia_nombre ?>: <?= $horario['hora_inicio'] ?>-<?= $horario['hora_fin'] ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (empty($horarios)): ?>
                                                        <small class="text-muted">Sin horarios definidos</small>
                                                    <?php endif; ?>
                                                </div>
                                                <!-- <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                        onclick="verHorarioCompleto(<?= $asignacion['id'] ?>)">
                                                    <i class="ti ti-calendar-time"></i> Ver completo
                                                </button> -->
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarAsignacion(<?= $asignacion['id'] ?>)" 
                                                            title="Editar Asignación">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $asignacion['es_tutor'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleTutor(<?= $asignacion['id'] ?>, <?= $asignacion['es_tutor'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $asignacion['es_tutor'] ? 'Quitar' : 'Asignar' ?> Tutoría">
                                                        <i class="ti <?= $asignacion['es_tutor'] ? 'ti-user-minus' : 'ti-user-plus' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalleAsignacion(<?= $asignacion['id'] ?>)" 
                                                            title="Ver Detalle">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="eliminarAsignacion(<?= $asignacion['id'] ?>)" 
                                                            title="Eliminar Asignación">
                                                        <i class="ti ti-trash"></i>
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

                <!-- Resumen de Carga por Docente -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Carga Académica por Docente</h6>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($carga_por_docente as $docente_id => $info): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 
                                            <?= $info['horas'] > 30 ? 'sobrecarga-warning' : '' ?> rounded">
                                        <div>
                                            <strong><?= htmlspecialchars($info['nombre']) ?></strong><br>
                                            <small class="text-muted"><?= $info['asignaciones'] ?> asignaciones</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?= $info['horas'] > 30 ? 'danger' : ($info['horas'] > 20 ? 'warning' : 'success') ?>">
                                                <?= $info['horas'] ?>h
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Área</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $areas_count = [];
                                foreach ($asignaciones as $asignacion) {
                                    $area = $asignacion['area_nombre'];
                                    $areas_count[$area] = ($areas_count[$area] ?? 0) + 1;
                                }
                                ?>
                                <?php foreach ($areas_count as $area => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($area) ?></span>
                                        <span class="badge bg-primary"><?= $count ?> asignaciones</span>
                                    </div>
                                <?php endforeach; ?>
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
    <?php include 'modales/asignaciones/modal_asignar.php'; ?>
    <?php include 'modales/asignaciones/modal_editar.php'; ?>
    <?php include 'modales/asignaciones/modal_horarios.php'; ?>

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
        let tablaAsignaciones;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaAsignaciones = $('#tablaAsignaciones').DataTable({
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
            $('#filtroDocente, #filtroArea, #filtroNivel, #filtroTutor, #filtroCarga').on('change', aplicarFiltros);
            $('#buscarAsignacion').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const docenteFiltro = $('#filtroDocente').val();
            const areaFiltro = $('#filtroArea').val();
            const nivelFiltro = $('#filtroNivel').val();
            const tutorFiltro = $('#filtroTutor').val();
            const cargaFiltro = $('#filtroCarga').val();
            const busqueda = $('#buscarAsignacion').val().toLowerCase();

            $('#tablaAsignaciones tbody tr').each(function() {
                const fila = $(this);
                const docente = fila.data('docente');
                const area = fila.data('area');
                const nivel = fila.data('nivel');
                const tutor = fila.data('tutor');
                const carga = parseInt(fila.data('carga'));
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtros básicos
                if (docenteFiltro && docente != docenteFiltro) mostrar = false;
                if (areaFiltro && area != areaFiltro) mostrar = false;
                if (nivelFiltro && nivel != nivelFiltro) mostrar = false;
                if (tutorFiltro !== '' && tutor != tutorFiltro) mostrar = false;

                // Filtro de carga horaria
                if (cargaFiltro) {
                    switch(cargaFiltro) {
                        case 'baja': if (carga > 10) mostrar = false; break;
                        case 'normal': if (carga <= 10 || carga > 20) mostrar = false; break;
                        case 'alta': if (carga <= 20 || carga > 30) mostrar = false; break;
                        case 'critica': if (carga <= 30) mostrar = false; break;
                    }
                }

                // Filtro de búsqueda
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroDocente, #filtroArea, #filtroNivel, #filtroTutor, #filtroCarga').val('');
            $('#buscarAsignacion').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarAsignacion(id) {
            mostrarCarga();
            
            fetch('modales/asignaciones/procesar_asignaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionAsignacion(data.asignacion);
                    $('#modalEditarAsignacion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la asignación');
            });
        }

        function toggleTutor(id, esTutor) {
            const accion = esTutor === 'true' ? 'asignar tutoría' : 'quitar tutoría';
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${accion} a este docente?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: esTutor === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleTutor(id, esTutor);
                }
            });
        }

        function ejecutarToggleTutor(id, esTutor) {
            mostrarCarga();

            fetch('modales/asignaciones/procesar_asignaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=toggle_tutor&id=${id}&es_tutor=${esTutor}`
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
                mostrarError('Error al cambiar tutoría');
            });
        }

        function verDetalleAsignacion(id) {
            mostrarCarga();
            
            fetch('modales/asignaciones/procesar_asignaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetalleAsignacion(data.asignacion);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalle');
            });
        }

        function mostrarDetalleAsignacion(asignacion) {
            Swal.fire({
                title: `${asignacion.docente_nombres} ${asignacion.docente_apellidos}`,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Asignación:</strong><br>
                                <small>Área: ${asignacion.area_nombre}</small><br>
                                <small>Sección: ${asignacion.grado} - ${asignacion.seccion}</small><br>
                                <small>Aula: ${asignacion.aula_asignada || 'No asignada'}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Carga Académica:</strong><br>
                                <small>Horas semanales: ${asignacion.horas_semanales}h</small><br>
                                <small>Estudiantes: ${asignacion.total_estudiantes}</small><br>
                                <small>Tutor: ${asignacion.es_tutor ? 'Sí' : 'No'}</small>
                            </div>
                        </div>
                    </div>
                `,
                width: '500px',
                confirmButtonText: 'Cerrar'
            });
        }

        function verHorarioCompleto(id) {
            // Implementar vista de horario completo
            $('#modalHorariosAsignacion').modal('show');
        }

        function eliminarAsignacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas eliminar esta asignación?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarEliminacion(id);
                }
            });
        }

        function ejecutarEliminacion(id) {
            mostrarCarga();

            fetch('modales/asignaciones/procesar_asignaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=eliminar&id=${id}`
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
                mostrarError('Error al eliminar asignación');
            });
        }

        function detectarConflictos() {
            mostrarCarga();
            
            fetch('modales/asignaciones/procesar_asignaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'accion=detectar_conflictos'
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarConflictos(data.conflictos);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al detectar conflictos');
            });
        }

        function mostrarConflictos(conflictos) {
            let html = '<div class="text-left">';
            
            if (conflictos.length === 0) {
                html += '<p class="text-success">✅ No se detectaron conflictos en las asignaciones</p>';
            } else {
                html += '<div class="alert alert-warning"><strong>Conflictos detectados:</strong></div>';
                conflictos.forEach(conflicto => {
                    html += `<div class="mb-2 p-2 border-left border-warning">
                                <strong>${conflicto.tipo}:</strong> ${conflicto.descripcion}
                             </div>`;
                });
            }
            
            html += '</div>';
            
            Swal.fire({
                title: 'Análisis de Conflictos',
                html: html,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function exportarAsignaciones() {
            window.open('reportes/exportar_asignaciones.php', '_blank');
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