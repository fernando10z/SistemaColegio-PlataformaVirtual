<?php 
    require_once 'conexion/bd.php';

    // Obtener niveles educativos con estadísticas de uso
    try {
        $sql = "SELECT ne.*, 
                    COUNT(DISTINCT s.id) as total_secciones,
                    COUNT(DISTINCT m.id) as total_estudiantes,
                    COUNT(DISTINCT CASE WHEN m.estado = 'MATRICULADO' AND m.activo = 1 THEN m.id END) as estudiantes_activos
                FROM niveles_educativos ne
                LEFT JOIN secciones s ON ne.id = s.nivel_id AND s.activo = 1
                LEFT JOIN matriculas m ON s.id = m.seccion_id
                GROUP BY ne.id
                ORDER BY ne.orden ASC, ne.nombre ASC";
        
        $stmt_niveles = $conexion->prepare($sql);
        $stmt_niveles->execute();
        $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $niveles = [];
        $error_niveles = "Error al cargar niveles: " . $e->getMessage();
    }

    // Obtener información adicional para estadísticas
    $estadisticas_generales = [
        'total_niveles' => 0,
        'niveles_activos' => 0,
        'total_grados' => 0,
        'estudiantes_sistema' => 0,
        'rango_edades' => ['min' => null, 'max' => null]
    ];

    foreach ($niveles as $nivel) {
        $estadisticas_generales['total_niveles']++;
        if ($nivel['activo']) {
            $estadisticas_generales['niveles_activos']++;
        }
        $estadisticas_generales['estudiantes_sistema'] += $nivel['estudiantes_activos'];
        
        // Contar grados y analizar edades
        $grados = json_decode($nivel['grados'], true) ?: [];
        $estadisticas_generales['total_grados'] += count($grados);
        
        foreach ($grados as $grado) {
            if (isset($grado['edad_min'])) {
                if ($estadisticas_generales['rango_edades']['min'] === null || 
                    $grado['edad_min'] < $estadisticas_generales['rango_edades']['min']) {
                    $estadisticas_generales['rango_edades']['min'] = $grado['edad_min'];
                }
            }
            if (isset($grado['edad_max'])) {
                if ($estadisticas_generales['rango_edades']['max'] === null || 
                    $grado['edad_max'] > $estadisticas_generales['rango_edades']['max']) {
                    $estadisticas_generales['rango_edades']['max'] = $grado['edad_max'];
                }
            }
        }
    }

    // Preparar datos para gráficos
    $distribucion_estudiantes = [];
    foreach ($niveles as $nivel) {
        if ($nivel['estudiantes_activos'] > 0) {
            $distribucion_estudiantes[] = [
                'nombre' => $nivel['nombre'],
                'estudiantes' => $nivel['estudiantes_activos']
            ];
        }
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Niveles y Grados Educativos - ANDRÉS AVELINO CÁCERES</title>
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
        .nivel-info {
            line-height: 1.3;
        }
        .nivel-nombre {
            font-weight: 600;
            color: #495057;
            font-size: 1rem;
        }
        .nivel-codigo {
            font-size: 0.8rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        .grados-preview {
            max-height: 80px;
            overflow-y: auto;
        }
        .grado-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            margin: 0.1rem;
            border-radius: 0.25rem;
        }
        .orden-badge {
            font-size: 0.75rem;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
        .grados-expandidos {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .grado-detalle {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.5rem;
            margin: 0.25rem 0;
        }
        .grado-detalle-header {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        .grado-detalle-edad {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .estudiantes-count {
            font-weight: 600;
            color: #28a745;
        }
        .drag-handle {
            cursor: move;
            opacity: 0.5;
        }
        .drag-handle:hover {
            opacity: 1;
        }
        .sortable-ghost {
            opacity: 0.4;
        }
        .edad-range {
            font-size: 0.8rem;
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
        }
        .nivel-inactive {
            opacity: 0.6;
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
                                <h4 class="fw-bold mb-0">Gestión de Niveles y Grados Educativos</h4>
                                <p class="mb-0 text-muted">Configuración de estructura académica institucional</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="reorganizarNiveles()">
                                    <i class="ti ti-arrows-sort me-2"></i>
                                    Reorganizar
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarNivel">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Nivel
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
                                <label class="form-label">Filtrar por Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ordenar por</label>
                                <select class="form-select" id="filtroOrden">
                                    <option value="">Orden predeterminado</option>
                                    <option value="nombre_asc">Nombre A-Z</option>
                                    <option value="nombre_desc">Nombre Z-A</option>
                                    <option value="estudiantes_desc">Más estudiantes</option>
                                    <option value="estudiantes_asc">Menos estudiantes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarNivel" placeholder="Buscar por nombre o código...">
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

                <!-- Tabla de Niveles -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Estructura de Niveles y Grados</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaNiveles">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">Orden</th>
                                        <th>Nivel Educativo</th>
                                        <th>Grados Configurados</th>
                                        <th>Rango de Edades</th>
                                        <th>Secciones</th>
                                        <th>Estudiantes</th>
                                        <th>Estado</th>
                                        <th width="200">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaNiveles">
                                    <?php foreach ($niveles as $index => $nivel): 
                                        $grados = json_decode($nivel['grados'], true) ?: [];
                                        $edades = [];
                                        foreach ($grados as $grado) {
                                            if (isset($grado['edad_min'])) $edades[] = $grado['edad_min'];
                                            if (isset($grado['edad_max'])) $edades[] = $grado['edad_max'];
                                        }
                                        $edad_min = !empty($edades) ? min($edades) : 0;
                                        $edad_max = !empty($edades) ? max($edades) : 0;
                                    ?>
                                        <tr data-estado="<?= $nivel['activo'] ?>" data-estudiantes="<?= $nivel['estudiantes_activos'] ?>" class="<?= !$nivel['activo'] ? 'nivel-inactive' : '' ?>">
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="drag-handle me-2" title="Arrastrar para reordenar">
                                                        <i class="ti ti-grip-vertical"></i>
                                                    </span>
                                                    <span class="orden-badge bg-primary text-white">
                                                        <?= $nivel['orden'] ?: ($index + 1) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="nivel-info">
                                                    <div class="nivel-nombre"><?= htmlspecialchars($nivel['nombre']) ?></div>
                                                    <div class="nivel-codigo">Código: <?= htmlspecialchars($nivel['codigo'] ?: 'Sin código') ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="grados-preview">
                                                    <?php if (!empty($grados)): ?>
                                                        <?php foreach ($grados as $grado): ?>
                                                            <span class="badge grado-badge bg-info"><?= htmlspecialchars($grado['nombre']) ?></span>
                                                        <?php endforeach; ?>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                                onclick="toggleGradosDetalle(<?= $nivel['id'] ?>)">
                                                            <i class="ti ti-eye"></i> Ver detalles (<?= count($grados) ?>)
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin grados configurados</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div id="grados-detalle-<?= $nivel['id'] ?>" class="grados-expandidos" style="display: none;">
                                                    <?php foreach ($grados as $grado): ?>
                                                        <div class="grado-detalle">
                                                            <div class="grado-detalle-header">
                                                                <?= htmlspecialchars($grado['nombre']) ?> 
                                                                <span class="badge bg-secondary"><?= htmlspecialchars($grado['codigo']) ?></span>
                                                            </div>
                                                            <div class="grado-detalle-edad">
                                                                Edad: <?= $grado['edad_min'] ?><?= $grado['edad_min'] != $grado['edad_max'] ? ' - ' . $grado['edad_max'] : '' ?> años
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($edades)): ?>
                                                    <span class="edad-range"><?= $edad_min ?> - <?= $edad_max ?> años</span>
                                                <?php else: ?>
                                                    <span class="text-muted">No definido</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary fs-6"><?= $nivel['total_secciones'] ?></span>
                                                <br><small class="text-muted">secciones</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="estudiantes-count"><?= $nivel['estudiantes_activos'] ?></div>
                                                <small class="text-muted">estudiantes</small>
                                            </td>
                                            <td>
                                                <span class="badge <?= $nivel['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $nivel['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarNivel(<?= $nivel['id'] ?>)" 
                                                            title="Editar Nivel">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $nivel['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoNivel(<?= $nivel['id'] ?>, <?= $nivel['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $nivel['activo'] ? 'Desactivar' : 'Activar' ?> Nivel">
                                                        <i class="ti <?= $nivel['activo'] ? 'ti-eye-off' : 'ti-eye' ?>"></i>
                                                    </button>
                                                    <?php if ($nivel['estudiantes_activos'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="verEstudiantesNivel(<?= $nivel['id'] ?>)" 
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

                <!-- Distribución de Estudiantes -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución de Estudiantes por Nivel</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="graficoDistribucion" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Validaciones del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div id="validaciones-resultado">
                                    <p class="text-muted">Haga clic en "Validar Edades" para ejecutar validaciones</p>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm w-100" onclick="ejecutarValidaciones()">
                                    <i class="ti ti-check me-1"></i>
                                    Ejecutar Validaciones
                                </button>
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
    <?php include 'modales/niveles/modal_agregar.php'; ?>
    <?php include 'modales/niveles/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let tablaNiveles;
        let sortableNiveles;
        let graficoDistribucion;

        const datosDistribucion = <?= json_encode($distribucion_estudiantes) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaNiveles = $('#tablaNiveles').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Inicializar Sortable para reordenar
            inicializarSortable();
            
            // Inicializar gráfico
            inicializarGrafico();

            // Filtros
            $('#filtroEstado, #filtroOrden').on('change', aplicarFiltros);
            $('#buscarNivel').on('keyup', aplicarFiltros);
        });

        function inicializarSortable() {
            const tbody = document.getElementById('cuerpoTablaNiveles');
            sortableNiveles = Sortable.create(tbody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    actualizarOrden();
                }
            });
        }

        function inicializarGrafico() {
            const ctx = document.getElementById('graficoDistribucion').getContext('2d');
            
            graficoDistribucion = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: datosDistribucion.map(item => item.nombre),
                    datasets: [{
                        data: datosDistribucion.map(item => item.estudiantes),
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const ordenFiltro = $('#filtroOrden').val();
            const busqueda = $('#buscarNivel').val().toLowerCase();

            let filas = Array.from($('#tablaNiveles tbody tr'));

            // Filtrar por estado
            filas.forEach(fila => {
                const estado = $(fila).data('estado');
                const texto = fila.textContent.toLowerCase();

                let mostrar = true;

                if (estadoFiltro !== '' && estado != estadoFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                $(fila).toggle(mostrar);
            });

            // Ordenar si es necesario
            if (ordenFiltro) {
                const filasVisibles = filas.filter(fila => $(fila).is(':visible'));
                
                filasVisibles.sort((a, b) => {
                    switch (ordenFiltro) {
                        case 'nombre_asc':
                            return $(a).find('.nivel-nombre').text().localeCompare($(b).find('.nivel-nombre').text());
                        case 'nombre_desc':
                            return $(b).find('.nivel-nombre').text().localeCompare($(a).find('.nivel-nombre').text());
                        case 'estudiantes_desc':
                            return $(b).data('estudiantes') - $(a).data('estudiantes');
                        case 'estudiantes_asc':
                            return $(a).data('estudiantes') - $(b).data('estudiantes');
                        default:
                            return 0;
                    }
                });

                // Reordenar en el DOM
                const tbody = $('#cuerpoTablaNiveles');
                filasVisibles.forEach(fila => tbody.append(fila));
            }
        }

        function limpiarFiltros() {
            $('#filtroEstado, #filtroOrden').val('');
            $('#buscarNivel').val('');
            aplicarFiltros();
        }

        function toggleGradosDetalle(nivelId) {
            const detalle = $(`#grados-detalle-${nivelId}`);
            detalle.slideToggle();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarNivel(id) {
            mostrarCarga();
            
            fetch('modales/niveles/procesar_niveles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionNivel(data.nivel);
                    $('#modalEditarNivel').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del nivel');
            });
        }

        function toggleEstadoNivel(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = `¿Deseas ${accion} este nivel educativo?`;

            Swal.fire({
                title: '¿Estás seguro?',
                text: mensaje,
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

            fetch('modales/niveles/procesar_niveles.php', {
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
                mostrarError('Error al cambiar estado del nivel');
            });
        }

        function verEstudiantesNivel(id) {
            mostrarCarga();
            
            fetch('modales/niveles/procesar_niveles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=estudiantes_nivel&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarEstudiantesNivel(data.estudiantes, data.nivel_nombre);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar estudiantes');
            });
        }

        function mostrarEstudiantesNivel(estudiantes, nivelNombre) {
            let html = `<div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            estudiantes.forEach(est => {
                html += `<tr>
                    <td>${est.nombres} ${est.apellidos}</td>
                    <td>${est.grado}</td>
                    <td>${est.seccion}</td>
                    <td><span class="badge ${est.activo ? 'bg-success' : 'bg-danger'}">${est.activo ? 'Activo' : 'Inactivo'}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: `Estudiantes - ${nivelNombre}`,
                html: html,
                width: '700px',
                confirmButtonText: 'Cerrar'
            });
        }

        function reorganizarNiveles() {
            Swal.fire({
                title: 'Reorganizar Niveles',
                text: 'Arrastra las filas de la tabla para cambiar el orden de los niveles educativos.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }

        function actualizarOrden() {
            const filas = Array.from($('#cuerpoTablaNiveles tr'));
            const nuevoOrden = filas.map((fila, index) => ({
                id: $(fila).find('.btn-outline-primary').attr('onclick').match(/\d+/)[0],
                orden: index + 1
            }));

            mostrarCarga();

            fetch('modales/niveles/procesar_niveles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=actualizar_orden&orden=${JSON.stringify(nuevoOrden)}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarExito('Orden actualizado correctamente');
                    // Actualizar badges de orden
                    filas.forEach((fila, index) => {
                        $(fila).find('.orden-badge').text(index + 1);
                    });
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al actualizar orden');
            });
        }

        function mostrarValidaciones(validaciones) {
            let html = '';
            
            if (validaciones.errores && validaciones.errores.length > 0) {
                html += '<div class="alert alert-danger">';
                html += '<strong>Errores encontrados:</strong><ul class="mb-0">';
                validaciones.errores.forEach(error => {
                    html += `<li>${error}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (validaciones.advertencias && validaciones.advertencias.length > 0) {
                html += '<div class="alert alert-warning">';
                html += '<strong>Advertencias:</strong><ul class="mb-0">';
                validaciones.advertencias.forEach(adv => {
                    html += `<li>${adv}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (validaciones.ok) {
                html += '<div class="alert alert-success">';
                html += '<i class="ti ti-check me-2"></i>Todas las validaciones pasaron correctamente';
                html += '</div>';
            }
            
            $('#validaciones-resultado').html(html);
        }

        function exportarNiveles() {
            window.open('reportes/exportar_niveles.php', '_blank');
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