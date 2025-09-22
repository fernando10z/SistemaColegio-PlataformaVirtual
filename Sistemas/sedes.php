<?php 
require_once 'conexion/bd.php';

// Obtener información de aulas con ocupación actual
try {
    $sql = "SELECT s.*, 
                   ne.nombre as nivel_nombre,
                   COUNT(m.id) as estudiantes_matriculados,
                   ROUND((COUNT(m.id) / s.capacidad_maxima) * 100, 1) as porcentaje_ocupacion,
                   CASE 
                       WHEN COUNT(m.id) = 0 THEN 'DISPONIBLE'
                       WHEN COUNT(m.id) < s.capacidad_maxima THEN 'OCUPADA'
                       WHEN COUNT(m.id) >= s.capacidad_maxima THEN 'COMPLETA'
                       ELSE 'DISPONIBLE'
                   END as estado_ocupacion
            FROM secciones s
            LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id
            LEFT JOIN matriculas m ON s.id = m.seccion_id AND m.activo = 1 AND m.estado = 'MATRICULADO'
            WHERE s.activo = 1
            GROUP BY s.id
            ORDER BY s.aula_asignada ASC";
    
    $stmt_aulas = $conexion->prepare($sql);
    $stmt_aulas->execute();
    $aulas = $stmt_aulas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $aulas = [];
    $error_aulas = "Error al cargar aulas: " . $e->getMessage();
}

// Obtener niveles educativos para filtros
try {
    $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
    $stmt_niveles->execute();
    $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $niveles = [];
}

// Extraer sedes únicas de las aulas
$sedes = [];
foreach ($aulas as $aula) {
    if ($aula['aula_asignada']) {
        // Extraer sede del nombre del aula (ej: "Aula 101" -> "Principal", "Lab Ciencias" -> "Laboratorio")
        $sede = 'Principal'; // Valor por defecto
        if (strpos($aula['aula_asignada'], 'Lab') !== false) {
            $sede = 'Laboratorio';
        } elseif (strpos($aula['aula_asignada'], 'Inicial') !== false) {
            $sede = 'Inicial';
        } elseif (strpos($aula['aula_asignada'], 'Patio') !== false) {
            $sede = 'Deportiva';
        }
        $sedes[$sede] = $sede;
    }
}

// Calcular estadísticas
$total_aulas = count($aulas);
$aulas_ocupadas = count(array_filter($aulas, function($a) { return $a['estudiantes_matriculados'] > 0; }));
$aulas_disponibles = $total_aulas - $aulas_ocupadas;
$aulas_completas = count(array_filter($aulas, function($a) { return $a['estado_ocupacion'] === 'COMPLETA'; }));

$capacidad_total = array_sum(array_column($aulas, 'capacidad_maxima'));
$estudiantes_total = array_sum(array_column($aulas, 'estudiantes_matriculados'));
$ocupacion_promedio = $capacidad_total > 0 ? round(($estudiantes_total / $capacidad_total) * 100, 1) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Sedes y Aulas - ANDRÉS AVELINO CÁCERES</title>
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
        .aula-info {
            line-height: 1.3;
        }
        .aula-nombre {
            font-weight: 600;
            color: #495057;
        }
        .aula-detalles {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .ocupacion-bar {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .ocupacion-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        .ocupacion-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
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
        .capacidad-info {
            font-size: 0.85rem;
        }
        .capacidad-numero {
            font-weight: 600;
            color: #495057;
        }
        .sede-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .programacion-item {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            font-size: 0.75rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Sedes y Aulas</h4>
                                <p class="mb-0 text-muted">Control de ocupación y programación de espacios educativos</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="verProgramacionGeneral()">
                                    <i class="ti ti-calendar me-2"></i>
                                    Programación
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarAula">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Aula
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Filtrar por Sede</label>
                                <select class="form-select" id="filtroSede">
                                    <option value="">Todas las sedes</option>
                                    <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= $sede ?>"><?= $sede ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Filtrar por Nivel</label>
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos los niveles</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado Ocupación</label>
                                <select class="form-select" id="filtroOcupacion">
                                    <option value="">Todos</option>
                                    <option value="DISPONIBLE">Disponible</option>
                                    <option value="OCUPADA">Ocupada</option>
                                    <option value="COMPLETA">Completa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Capacidad</label>
                                <select class="form-select" id="filtroCapacidad">
                                    <option value="">Todas</option>
                                    <option value="1-20">1-20 estudiantes</option>
                                    <option value="21-30">21-30 estudiantes</option>
                                    <option value="31-40">31-40 estudiantes</option>
                                    <option value="40+">Más de 40</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarAula" placeholder="Buscar aula...">
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

                <!-- Tabla de Aulas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Inventario de Aulas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaAulas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Aula / Sede</th>
                                        <th>Nivel / Grado</th>
                                        <th>Capacidad</th>
                                        <th>Ocupación Actual</th>
                                        <th>Estado</th>
                                        <th>Programación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aulas as $aula): 
                                        // Determinar sede
                                        $sede = 'Principal';
                                        if (strpos($aula['aula_asignada'], 'Lab') !== false) {
                                            $sede = 'Laboratorio';
                                        } elseif (strpos($aula['aula_asignada'], 'Inicial') !== false) {
                                            $sede = 'Inicial';
                                        } elseif (strpos($aula['aula_asignada'], 'Patio') !== false) {
                                            $sede = 'Deportiva';
                                        }
                                        
                                        // Color de la barra de ocupación
                                        $ocupacion_color = 'bg-success';
                                        if ($aula['porcentaje_ocupacion'] > 80) {
                                            $ocupacion_color = 'bg-danger';
                                        } elseif ($aula['porcentaje_ocupacion'] > 60) {
                                            $ocupacion_color = 'bg-warning';
                                        }
                                    ?>
                                        <tr data-sede="<?= $sede ?>" 
                                            data-nivel="<?= $aula['nivel_id'] ?>" 
                                            data-ocupacion="<?= $aula['estado_ocupacion'] ?>"
                                            data-capacidad="<?= $aula['capacidad_maxima'] ?>">
                                            <td>
                                                <div class="aula-info">
                                                    <div class="aula-nombre"><?= htmlspecialchars($aula['aula_asignada'] ?: 'Sin asignar') ?></div>
                                                    <span class="badge sede-badge bg-secondary"><?= $sede ?></span>
                                                    <div class="aula-detalles">
                                                        Código: <?= htmlspecialchars($aula['codigo'] ?: 'N/A') ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($aula['nivel_nombre']) ?></strong><br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($aula['grado']) ?> - Sección <?= htmlspecialchars($aula['seccion']) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="capacidad-info text-center">
                                                    <div class="capacidad-numero"><?= $aula['capacidad_maxima'] ?></div>
                                                    <small class="text-muted">estudiantes</small>
                                                </div>
                                            </td>
                                            <td style="min-width: 150px;">
                                                <div class="ocupacion-bar">
                                                    <div class="ocupacion-fill <?= $ocupacion_color ?>" 
                                                         style="width: <?= min($aula['porcentaje_ocupacion'], 100) ?>%"></div>
                                                    <div class="ocupacion-text">
                                                        <?= $aula['estudiantes_matriculados'] ?>/<?= $aula['capacidad_maxima'] ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block text-center mt-1">
                                                    <?= $aula['porcentaje_ocupacion'] ?>% ocupado
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge estado-badge <?php
                                                    switch($aula['estado_ocupacion']) {
                                                        case 'DISPONIBLE': echo 'bg-success'; break;
                                                        case 'OCUPADA': echo 'bg-warning text-dark'; break;
                                                        case 'COMPLETA': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= $aula['estado_ocupacion'] ?>
                                                </span>
                                                <?php if ($aula['activo']): ?>
                                                    <br><span class="badge bg-info estado-badge mt-1">Activa</span>
                                                <?php else: ?>
                                                    <br><span class="badge bg-secondary estado-badge mt-1">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="programacion-preview">
                                                    <span class="programacion-item">Lun-Vie: 8:00-16:00</span>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                            onclick="verProgramacionAula(<?= $aula['id'] ?>)">
                                                        <i class="ti ti-calendar-time"></i> Ver horarios
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarAula(<?= $aula['id'] ?>)" 
                                                            title="Editar Aula">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalleAula(<?= $aula['id'] ?>)" 
                                                            title="Ver Detalle">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="programarAula(<?= $aula['id'] ?>)" 
                                                            title="Programar Uso">
                                                        <i class="ti ti-calendar-plus"></i>
                                                    </button>
                                                    <?php if ($aula['estudiantes_matriculados'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="verEstudiantes(<?= $aula['id'] ?>)" 
                                                                title="Ver Estudiantes">
                                                            <i class="ti ti-users"></i>
                                                        </button>
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
    <?php include 'modales/aulas/modal_agregar.php'; ?>
    <?php include 'modales/aulas/modal_editar.php'; ?>
    <?php include 'modales/aulas/modal_programacion.php'; ?>

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
        let tablaAulas;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaAulas = $('#tablaAulas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 5,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros personalizados
            $('#filtroSede, #filtroNivel, #filtroOcupacion, #filtroCapacidad').on('change', aplicarFiltros);
            $('#buscarAula').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const sedeFiltro = $('#filtroSede').val();
            const nivelFiltro = $('#filtroNivel').val();
            const ocupacionFiltro = $('#filtroOcupacion').val();
            const capacidadFiltro = $('#filtroCapacidad').val();
            const busqueda = $('#buscarAula').val().toLowerCase();

            $('#tablaAulas tbody tr').each(function() {
                const fila = $(this);
                const sede = fila.data('sede');
                const nivel = fila.data('nivel');
                const ocupacion = fila.data('ocupacion');
                const capacidad = parseInt(fila.data('capacidad'));
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtro por sede
                if (sedeFiltro && sede !== sedeFiltro) {
                    mostrar = false;
                }

                // Filtro por nivel
                if (nivelFiltro && nivel != nivelFiltro) {
                    mostrar = false;
                }

                // Filtro por ocupación
                if (ocupacionFiltro && ocupacion !== ocupacionFiltro) {
                    mostrar = false;
                }

                // Filtro por capacidad
                if (capacidadFiltro) {
                    const [min, max] = capacidadFiltro.includes('+') ? 
                        [parseInt(capacidadFiltro.replace('+', '')), Infinity] :
                        capacidadFiltro.split('-').map(x => parseInt(x));
                    
                    if (capacidad < min || (max && capacidad > max)) {
                        mostrar = false;
                    }
                }

                // Filtro por texto
                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroSede, #filtroNivel, #filtroOcupacion, #filtroCapacidad').val('');
            $('#buscarAula').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarAula(id) {
            mostrarCarga();
            
            fetch('modales/aulas/procesar_aulas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionAula(data.aula);
                    $('#modalEditarAula').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del aula');
            });
        }

        function verDetalleAula(id) {
            mostrarCarga();
            
            fetch('modales/aulas/procesar_aulas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetalleAula(data.aula);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalle del aula');
            });
        }

        function mostrarDetalleAula(aula) {
            const ocupacionPorcentaje = aula.capacidad_maxima > 0 ? 
                Math.round((aula.estudiantes_matriculados / aula.capacidad_maxima) * 100) : 0;
            
            Swal.fire({
                title: `${aula.aula_asignada}`,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Información General:</strong><br>
                                <small>Código: ${aula.codigo || 'N/A'}</small><br>
                                <small>Nivel: ${aula.nivel_nombre}</small><br>
                                <small>Grado: ${aula.grado} - ${aula.seccion}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Ocupación:</strong><br>
                                <small>Capacidad: ${aula.capacidad_maxima} estudiantes</small><br>
                                <small>Ocupados: ${aula.estudiantes_matriculados} estudiantes</small><br>
                                <small>Porcentaje: ${ocupacionPorcentaje}%</small>
                            </div>
                        </div>
                        <hr>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar ${ocupacionPorcentaje > 80 ? 'bg-danger' : ocupacionPorcentaje > 60 ? 'bg-warning' : 'bg-success'}" 
                                 style="width: ${Math.min(ocupacionPorcentaje, 100)}%">
                                ${ocupacionPorcentaje}%
                            </div>
                        </div>
                    </div>
                `,
                width: '500px',
                confirmButtonText: 'Cerrar'
            });
        }

        function programarAula(id) {
            // Cargar modal de programación
            $('#modalProgramacionAula').modal('show');
        }

        function verProgramacionAula(id) {
            mostrarCarga();
            
            fetch('modales/aulas/procesar_aulas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=programacion&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarProgramacionAula(data.programacion);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar programación');
            });
        }

        function mostrarProgramacionAula(programacion) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Día</th><th>Hora</th><th>Actividad</th></tr></thead><tbody>';
            
            const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            dias.forEach(dia => {
                html += `<tr><td>${dia}</td><td>8:00 - 16:00</td><td>Clases Regulares</td></tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Programación de Aula',
                html: html,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function verEstudiantes(id) {
            mostrarCarga();
            
            fetch('modales/aulas/procesar_aulas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=estudiantes&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarEstudiantesAula(data.estudiantes);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar estudiantes');
            });
        }

        function mostrarEstudiantesAula(estudiantes) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Estudiante</th><th>Código</th><th>Estado</th></tr></thead><tbody>';
            
            estudiantes.forEach(estudiante => {
                html += `<tr>
                    <td>${estudiante.nombres} ${estudiante.apellidos}</td>
                    <td>${estudiante.codigo_estudiante}</td>
                    <td><span class="badge ${estudiante.activo ? 'bg-success' : 'bg-danger'}">${estudiante.activo ? 'Activo' : 'Inactivo'}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Estudiantes en el Aula',
                html: html,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function verProgramacionGeneral() {
            window.open('reportes/programacion_aulas.php', '_blank');
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