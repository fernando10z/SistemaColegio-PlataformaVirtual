<?php 
require_once 'conexion/bd.php';

// Obtener período académico actual
try {
    $stmt_periodo_actual = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 AND actual = 1 LIMIT 1");
    $stmt_periodo_actual->execute();
    $periodo_actual = $stmt_periodo_actual->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodo_actual = null;
    $error_periodo = "Error al cargar período actual: " . $e->getMessage();
}

// Obtener todos los períodos académicos
try {
    $stmt_periodos = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 ORDER BY anio DESC, fecha_inicio DESC");
    $stmt_periodos->execute();
    $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodos = [];
}

// Crear tabla temporal para eventos si no existe
try {
    $conexion->exec("CREATE TABLE IF NOT EXISTS eventos_calendario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        periodo_academico_id INT,
        titulo VARCHAR(255) NOT NULL,
        descripcion TEXT,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE,
        tipo_evento ENUM('FERIADO','EVALUACION','EVENTO_ESPECIAL','REUNION','CAPACITACION','ACTIVIDAD_ACADEMICA','SUSPENSION_CLASES') DEFAULT 'EVENTO_ESPECIAL',
        configuracion JSON,
        color VARCHAR(7) DEFAULT '#0d6efd',
        notificacion_automatica BOOLEAN DEFAULT TRUE,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_creacion INT,
        FOREIGN KEY (periodo_academico_id) REFERENCES periodos_academicos(id),
        FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id)
    )");
} catch (PDOException $e) {
    // Tabla ya existe o error en creación
}

// Obtener eventos del período actual
$eventos = [];
if ($periodo_actual) {
    try {
        $sql = "SELECT ec.*, u.nombres as creador_nombre, u.apellidos as creador_apellido 
                FROM eventos_calendario ec
                LEFT JOIN usuarios u ON ec.usuario_creacion = u.id
                WHERE ec.periodo_academico_id = ? AND ec.activo = 1
                ORDER BY ec.fecha_inicio ASC";
        $stmt_eventos = $conexion->prepare($sql);
        $stmt_eventos->execute([$periodo_actual['id']]);
        $eventos = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $eventos = [];
    }
}

// Agregar eventos automáticos de períodos de evaluación si existen
if ($periodo_actual && !empty($periodo_actual['periodos_evaluacion'])) {
    $periodos_eval = json_decode($periodo_actual['periodos_evaluacion'], true);
    foreach ($periodos_eval as $periodo) {
        $eventos[] = [
            'id' => 'eval_' . $periodo['numero'],
            'titulo' => $periodo['nombre'],
            'descripcion' => 'Período de evaluación académica',
            'fecha_inicio' => $periodo['fecha_inicio'],
            'fecha_fin' => $periodo['fecha_fin'],
            'tipo_evento' => 'EVALUACION',
            'color' => '#fd7e14',
            'es_automatico' => true
        ];
    }
}

// Calcular estadísticas
$total_eventos = count($eventos);
$eventos_proximos = count(array_filter($eventos, function($e) {
    return strtotime($e['fecha_inicio']) >= strtotime('today') && 
           strtotime($e['fecha_inicio']) <= strtotime('+7 days');
}));
$feriados_mes = count(array_filter($eventos, function($e) {
    return $e['tipo_evento'] === 'FERIADO' && 
           date('Y-m', strtotime($e['fecha_inicio'])) === date('Y-m');
}));

// Días académicos restantes en el período actual
$dias_restantes = 0;
if ($periodo_actual) {
    $hoy = strtotime('today');
    $fin_periodo = strtotime($periodo_actual['fecha_fin']);
    if ($fin_periodo > $hoy) {
        $dias_restantes = ceil(($fin_periodo - $hoy) / (60 * 60 * 24));
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario Académico - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" />
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
        .calendar-container {
            min-height: 600px;
        }
        .fc-event {
            cursor: pointer;
            border-radius: 4px;
        }
        .fc-event:hover {
            opacity: 0.8;
        }
        .evento-info {
            line-height: 1.3;
        }
        .evento-titulo {
            font-weight: 600;
            color: #495057;
        }
        .evento-fecha {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .tipo-evento-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
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
        .evento-color-preview {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .periodo-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .periodo-nombre {
            font-weight: 600;
            color: #1976d2;
            font-size: 1.1rem;
        }
        .periodo-fechas {
            color: #424242;
            font-size: 0.9rem;
        }
        .vista-toggle {
            border-radius: 25px;
        }
        .vista-toggle.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .evento-proximo {
            border-left: 4px solid #28a745;
            padding-left: 12px;
        }
        .notificacion-badge {
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
                                <h4 class="fw-bold mb-0">Calendario Académico</h4>
                                <p class="mb-0 text-muted">Gestión de eventos, feriados y fechas especiales</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="btn-group vista-toggle" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="vistaCalendario" onclick="cambiarVista('calendario')">
                                        <i class="ti ti-calendar me-1"></i> Calendario
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="vistaLista" onclick="cambiarVista('lista')">
                                        <i class="ti ti-list me-1"></i> Lista
                                    </button>
                                </div>
                                <button type="button" class="btn btn-outline-info" onclick="exportarCalendario()">
                                    <i class="ti ti-download me-2"></i>
                                    Exportar
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEvento">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Evento
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
                            <div class="periodo-fechas">
                                <i class="ti ti-calendar"></i>
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_inicio'])) ?> - 
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_fin'])) ?>
                                | <?= $periodo_actual['tipo_periodo'] ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted">Días restantes</div>
                            <div class="h4 text-primary"><?= $dias_restantes ?> días</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Período Académico</label>
                                <select class="form-select" id="filtroPeriodo">
                                    <?php foreach ($periodos as $periodo): ?>
                                        <option value="<?= $periodo['id'] ?>" <?= $periodo_actual && $periodo['id'] == $periodo_actual['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($periodo['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo de Evento</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="FERIADO">Feriados</option>
                                    <option value="EVALUACION">Evaluaciones</option>
                                    <option value="EVENTO_ESPECIAL">Eventos Especiales</option>
                                    <option value="REUNION">Reuniones</option>
                                    <option value="CAPACITACION">Capacitaciones</option>
                                    <option value="ACTIVIDAD_ACADEMICA">Act. Académicas</option>
                                    <option value="SUSPENSION_CLASES">Suspensiones</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Mes</label>
                                <select class="form-select" id="filtroMes">
                                    <option value="">Todos</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Notificaciones</label>
                                <select class="form-select" id="filtroNotificacion">
                                    <option value="">Todas</option>
                                    <option value="1">Con notificación</option>
                                    <option value="0">Sin notificación</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarEvento" placeholder="Buscar eventos...">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Calendario -->
                <div class="card" id="contenedorCalendario">
                    <div class="card-header">
                        <h5 class="mb-0">Vista de Calendario</h5>
                    </div>
                    <div class="card-body">
                        <div id="calendario" class="calendar-container"></div>
                    </div>
                </div>

                <!-- Vista Lista -->
                <div class="card" id="contenedorLista" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Eventos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaEventos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Evento</th>
                                        <th>Tipo</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Notificación</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eventos as $evento): ?>
                                        <tr data-tipo="<?= $evento['tipo_evento'] ?>" 
                                            data-mes="<?= date('m', strtotime($evento['fecha_inicio'])) ?>"
                                            data-notificacion="<?= $evento['notificacion_automatica'] ?? 1 ?>">
                                            <td>
                                                <div class="evento-info">
                                                    <div class="d-flex align-items-center">
                                                        <div class="evento-color-preview" style="background-color: <?= $evento['color'] ?>"></div>
                                                        <div>
                                                            <div class="evento-titulo"><?= htmlspecialchars($evento['titulo']) ?></div>
                                                            <div class="evento-fecha">
                                                                <?= htmlspecialchars($evento['descripcion'] ?: 'Sin descripción') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge tipo-evento-badge <?php
                                                    switch($evento['tipo_evento']) {
                                                        case 'FERIADO': echo 'bg-success'; break;
                                                        case 'EVALUACION': echo 'bg-warning text-dark'; break;
                                                        case 'EVENTO_ESPECIAL': echo 'bg-primary'; break;
                                                        case 'REUNION': echo 'bg-info'; break;
                                                        case 'CAPACITACION': echo 'bg-secondary'; break;
                                                        case 'ACTIVIDAD_ACADEMICA': echo 'bg-success'; break;
                                                        case 'SUSPENSION_CLASES': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= str_replace('_', ' ', $evento['tipo_evento']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= $evento['fecha_fin'] ? date('d/m/Y', strtotime($evento['fecha_fin'])) : 'Un día' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (isset($evento['notificacion_automatica']) && $evento['notificacion_automatica']): ?>
                                                    <span class="badge bg-success notificacion-badge">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary notificacion-badge">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (strtotime($evento['fecha_inicio']) >= strtotime('today')): ?>
                                                    <span class="badge bg-info">Próximo</span>
                                                <?php elseif (strtotime($evento['fecha_inicio']) < strtotime('today')): ?>
                                                    <span class="badge bg-secondary">Pasado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1">
                                                    <?php if (!isset($evento['es_automatico'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editarEvento(<?= $evento['id'] ?>)" 
                                                                title="Editar Evento">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="duplicarEvento(<?= $evento['id'] ?>)" 
                                                                title="Duplicar Evento">
                                                            <i class="ti ti-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="eliminarEvento(<?= $evento['id'] ?>)" 
                                                                title="Eliminar Evento">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Automático</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Eventos Próximos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Eventos Próximos (7 días)</h6>
                            </div>
                            <div class="card-body">
                                <?php 
                                $eventos_proximos_lista = array_filter($eventos, function($e) {
                                    return strtotime($e['fecha_inicio']) >= strtotime('today') && 
                                           strtotime($e['fecha_inicio']) <= strtotime('+7 days');
                                });
                                ?>
                                <?php if (empty($eventos_proximos_lista)): ?>
                                    <p class="text-muted">No hay eventos próximos</p>
                                <?php else: ?>
                                    <?php foreach (array_slice($eventos_proximos_lista, 0, 5) as $evento): ?>
                                        <div class="evento-proximo mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="evento-color-preview me-2" style="background-color: <?= $evento['color'] ?>"></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?= htmlspecialchars($evento['titulo']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?> - 
                                                        <?= str_replace('_', ' ', $evento['tipo_evento']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Resumen por Tipo</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $tipos_count = [];
                                foreach ($eventos as $evento) {
                                    $tipo = $evento['tipo_evento'];
                                    $tipos_count[$tipo] = ($tipos_count[$tipo] ?? 0) + 1;
                                }
                                ?>
                                <?php foreach ($tipos_count as $tipo => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= str_replace('_', ' ', $tipo) ?></span>
                                        <span class="badge bg-primary"><?= $count ?></span>
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
    <?php include 'modales/calendario/modal_agregar.php'; ?>
    <?php include 'modales/calendario/modal_editar.php'; ?>
    <?php include 'modales/calendario/modal_detalle.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

    <script>
        let tablaEventos;
        let calendario;
        const eventos = <?= json_encode($eventos) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaEventos = $('#tablaEventos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[2, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Inicializar FullCalendar
            inicializarCalendario();

            // Filtros
            $('#filtroTipo, #filtroMes, #filtroNotificacion, #filtroPeriodo').on('change', aplicarFiltros);
            $('#buscarEvento').on('keyup', aplicarFiltros);
        });

        function inicializarCalendario() {
            const calendarEl = document.getElementById('calendario');
            
            calendario = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                height: 600,
                events: eventos.map(evento => ({
                    id: evento.id,
                    title: evento.titulo,
                    start: evento.fecha_inicio,
                    end: evento.fecha_fin,
                    backgroundColor: evento.color,
                    borderColor: evento.color,
                    extendedProps: {
                        tipo: evento.tipo_evento,
                        descripcion: evento.descripcion,
                        notificacion: evento.notificacion_automatica
                    }
                })),
                eventClick: function(info) {
                    mostrarDetalleEvento(info.event);
                },
                dateClick: function(info) {
                    $('#modalAgregarEvento').modal('show');
                    $('#fecha_inicio').val(info.dateStr);
                },
                eventDidMount: function(info) {
                    // Agregar tooltip
                    $(info.el).attr('title', info.event.extendedProps.descripcion || info.event.title);
                }
            });
            
            calendario.render();
        }

        function cambiarVista(vista) {
            if (vista === 'calendario') {
                $('#contenedorCalendario').show();
                $('#contenedorLista').hide();
                $('#vistaCalendario').addClass('active');
                $('#vistaLista').removeClass('active');
                calendario.render();
            } else {
                $('#contenedorCalendario').hide();
                $('#contenedorLista').show();
                $('#vistaLista').addClass('active');
                $('#vistaCalendario').removeClass('active');
            }
        }

        function aplicarFiltros() {
            const tipoFiltro = $('#filtroTipo').val();
            const mesFiltro = $('#filtroMes').val();
            const notificacionFiltro = $('#filtroNotificacion').val();
            const busqueda = $('#buscarEvento').val().toLowerCase();

            $('#tablaEventos tbody tr').each(function() {
                const fila = $(this);
                const tipo = fila.data('tipo');
                const mes = fila.data('mes').toString().padStart(2, '0');
                const notificacion = fila.data('notificacion');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (tipoFiltro && tipo !== tipoFiltro) mostrar = false;
                if (mesFiltro && mes !== mesFiltro) mostrar = false;
                if (notificacionFiltro !== '' && notificacion != notificacionFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });

            // Filtrar eventos en calendario
            const eventosFiltrados = eventos.filter(evento => {
                const mes = new Date(evento.fecha_inicio).getMonth() + 1;
                const mesStr = mes.toString().padStart(2, '0');
                
                let mostrar = true;
                if (tipoFiltro && evento.tipo_evento !== tipoFiltro) mostrar = false;
                if (mesFiltro && mesStr !== mesFiltro) mostrar = false;
                if (notificacionFiltro !== '' && (evento.notificacion_automatica ? 1 : 0) != notificacionFiltro) mostrar = false;
                if (busqueda && !evento.titulo.toLowerCase().includes(busqueda)) mostrar = false;
                
                return mostrar;
            });

            calendario.removeAllEvents();
            calendario.addEventSource(eventosFiltrados.map(evento => ({
                id: evento.id,
                title: evento.titulo,
                start: evento.fecha_inicio,
                end: evento.fecha_fin,
                backgroundColor: evento.color,
                borderColor: evento.color,
                extendedProps: {
                    tipo: evento.tipo_evento,
                    descripcion: evento.descripcion,
                    notificacion: evento.notificacion_automatica
                }
            })));
        }

        function limpiarFiltros() {
            $('#filtroTipo, #filtroMes, #filtroNotificacion').val('');
            $('#buscarEvento').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function mostrarDetalleEvento(event) {
            const evento = eventos.find(e => e.id == event.id);
            if (!evento) return;

            Swal.fire({
                title: evento.titulo,
                html: `
                    <div class="text-left">
                        <p><strong>Tipo:</strong> ${evento.tipo_evento.replace('_', ' ')}</p>
                        <p><strong>Descripción:</strong> ${evento.descripcion || 'Sin descripción'}</p>
                        <p><strong>Fecha:</strong> ${new Date(evento.fecha_inicio).toLocaleDateString('es-ES')}</p>
                        ${evento.fecha_fin ? `<p><strong>Fecha fin:</strong> ${new Date(evento.fecha_fin).toLocaleDateString('es-ES')}</p>` : ''}
                        <p><strong>Notificaciones:</strong> ${evento.notificacion_automatica ? 'Activas' : 'Inactivas'}</p>
                    </div>
                `,
                showDenyButton: !evento.es_automatico,
                showCancelButton: true,
                confirmButtonText: 'Cerrar',
                denyButtonText: 'Editar',
                cancelButtonText: 'Eliminar',
                cancelButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isDenied) {
                    editarEvento(evento.id);
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    eliminarEvento(evento.id);
                }
            });
        }

        function editarEvento(id) {
            // Implementar edición
            $('#modalEditarEvento').modal('show');
        }

        function duplicarEvento(id) {
            mostrarCarga();
            
            fetch('modales/calendario/procesar_calendario.php', {
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
                mostrarError('Error al duplicar evento');
            });
        }

        function eliminarEvento(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas eliminar este evento?',
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

            fetch('modales/calendario/procesar_calendario.php', {
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
                mostrarError('Error al eliminar evento');
            });
        }

        function exportarCalendario() {
            window.open('reportes/exportar_calendario.php', '_blank');
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