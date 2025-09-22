<?php 
    require_once 'conexion/bd.php';

    // Obtener áreas curriculares con estadísticas de uso
    try {
        $sql = "SELECT ac.*, 
                    COUNT(DISTINCT ad.id) as total_asignaciones,
                    COUNT(DISTINCT ad.docente_id) as docentes_asignados,
                    COUNT(DISTINCT s.nivel_id) as niveles_atendidos,
                    GROUP_CONCAT(DISTINCT ne.nombre SEPARATOR ', ') as niveles_nombres
                FROM areas_curriculares ac
                LEFT JOIN asignaciones_docentes ad ON ac.id = ad.area_id AND ad.activo = 1
                LEFT JOIN secciones s ON ad.seccion_id = s.id AND s.activo = 1
                LEFT JOIN niveles_educativos ne ON s.nivel_id = ne.id AND ne.activo = 1
                GROUP BY ac.id
                ORDER BY ac.nombre ASC";
        
        $stmt_areas = $conexion->prepare($sql);
        $stmt_areas->execute();
        $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $areas = [];
        $error_areas = "Error al cargar áreas curriculares: " . $e->getMessage();
    }

    // Obtener niveles educativos para gestión de competencias
    try {
        $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
        $stmt_niveles->execute();
        $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $niveles = [];
    }

    // Estructura base de competencias por nivel educativo peruano
    $competencias_base = [
        'inicial' => [
            'descripcion' => 'Competencias para Educación Inicial',
            'grados' => ['3 años', '4 años', '5 años']
        ],
        'primaria' => [
            'descripcion' => 'Competencias para Educación Primaria', 
            'grados' => ['1ro', '2do', '3ro', '4to', '5to', '6to']
        ],
        'secundaria' => [
            'descripcion' => 'Competencias para Educación Secundaria',
            'grados' => ['1ro', '2do', '3ro', '4to', '5to']
        ]
    ];

    // Competencias predefinidas por área (según DCN Perú)
    $competencias_predefinidas = [
        'MAT' => [
            'Resuelve problemas de cantidad',
            'Resuelve problemas de regularidad, equivalencia y cambio',
            'Resuelve problemas de forma, movimiento y localización',
            'Resuelve problemas de gestión de datos e incertidumbre'
        ],
        'COM' => [
            'Se comunica oralmente en lengua materna',
            'Lee diversos tipos de textos en lengua materna',
            'Escribe diversos tipos de textos en lengua materna'
        ],
        'CYT' => [
            'Indaga mediante métodos científicos',
            'Explica el mundo físico basándose en conocimientos científicos',
            'Diseña y construye soluciones tecnológicas'
        ],
        'PS' => [
            'Construye su identidad',
            'Convive y participa democráticamente',
            'Construye interpretaciones históricas',
            'Gestiona responsablemente el espacio y el ambiente',
            'Gestiona responsablemente los recursos económicos'
        ]
    ];

    // Calcular estadísticas
    $total_areas = count($areas);
    $areas_activas = count(array_filter($areas, function($a) { return $a['activo']; }));
    $areas_inactivas = $total_areas - $areas_activas;
    $areas_con_competencias = count(array_filter($areas, function($a) { 
        return !empty($a['competencias']) && $a['competencias'] !== 'null'; 
    }));
    $total_docentes_asignados = array_sum(array_column($areas, 'docentes_asignados'));
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Áreas Curriculares - ANDRÉS AVELINO CÁCERES</title>
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
        .area-info {
            line-height: 1.3;
        }
        .area-nombre {
            font-weight: 600;
            color: #495057;
        }
        .area-codigo {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .competencia-item {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            font-size: 0.75rem;
            border-left: 3px solid #0d6efd;
        }
        .competencias-preview {
            max-height: 80px;
            overflow-y: auto;
        }
        .nivel-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
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
        .competencias-detalle {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-top: 0.5rem;
            display: none;
        }
        .nivel-competencias {
            margin-bottom: 1rem;
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
        }
        .nivel-titulo {
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .grado-competencias {
            margin-bottom: 0.75rem;
        }
        .grado-nombre {
            font-weight: 500;
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        .competencia-texto {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            color: #495057;
        }
        .sin-competencias {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 2rem;
        }
        .codigo-area {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 0.8rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Áreas Curriculares</h4>
                                <p class="mb-0 text-muted">Administra áreas y competencias por grado educativo</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarArea">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Área
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
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="1">Activas</option>
                                    <option value="0">Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Con Competencias</label>
                                <select class="form-select" id="filtroCompetencias">
                                    <option value="">Todas</option>
                                    <option value="1">Con competencias</option>
                                    <option value="0">Sin competencias</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nivel Educativo</label>
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos los niveles</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= $nivel['nombre'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Docentes Asignados</label>
                                <select class="form-select" id="filtroDocentes">
                                    <option value="">Todos</option>
                                    <option value="1+">Con docentes</option>
                                    <option value="0">Sin docentes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarArea" placeholder="Buscar por nombre o código...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarAreas()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Áreas Curriculares -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Catálogo de Áreas Curriculares</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaAreas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Área Curricular</th>
                                        <th>Niveles Atendidos</th>
                                        <th>Competencias</th>
                                        <th>Docentes Asignados</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($areas as $area): 
                                        $competencias = !is_null($area['competencias']) ? json_decode($area['competencias'], true) : [];
                                        $tiene_competencias = !empty($competencias);
                                        $total_competencias = 0;
                                        
                                        if ($tiene_competencias) {
                                            foreach ($competencias as $nivel => $grados) {
                                                if (is_array($grados)) {
                                                    foreach ($grados as $grado => $comps) {
                                                        if (is_array($comps)) {
                                                            $total_competencias += count($comps);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    ?>
                                        <tr data-estado="<?= $area['activo'] ?>" 
                                            data-competencias="<?= $tiene_competencias ? '1' : '0' ?>"
                                            data-niveles="<?= htmlspecialchars($area['niveles_nombres'] ?: '') ?>"
                                            data-docentes="<?= $area['docentes_asignados'] ?>">
                                            <td>
                                                <div class="area-info">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="codigo-area me-2"><?= htmlspecialchars($area['codigo'] ?: 'S/C') ?></span>
                                                        <div class="area-nombre"><?= htmlspecialchars($area['nombre']) ?></div>
                                                    </div>
                                                    <div class="area-codigo">
                                                        <?= htmlspecialchars($area['descripcion'] ?: 'Sin descripción') ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($area['niveles_nombres']): ?>
                                                    <?php foreach (explode(', ', $area['niveles_nombres']) as $nivel): ?>
                                                        <span class="badge nivel-badge bg-info me-1"><?= htmlspecialchars($nivel) ?></span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <small class="text-muted">Sin asignaciones</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($tiene_competencias): ?>
                                                    <div class="competencias-preview">
                                                        <span class="badge bg-success"><?= $total_competencias ?> competencias</span>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                                onclick="toggleCompetenciasDetalle(<?= $area['id'] ?>)">
                                                            <i class="ti ti-eye"></i> Ver detalle
                                                        </button>
                                                    </div>
                                                    <div id="competencias-detalle-<?= $area['id'] ?>" class="competencias-detalle">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center">
                                                        <small class="text-muted">Sin competencias definidas</small>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                                onclick="asignarCompetencias(<?= $area['id'] ?>)">
                                                            <i class="ti ti-plus"></i> Asignar
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <?php if ($area['docentes_asignados'] > 0): ?>
                                                        <span class="badge bg-primary fs-6"><?= $area['docentes_asignados'] ?></span>
                                                        <br>
                                                        <small class="text-muted"><?= $area['total_asignaciones'] ?> asignaciones</small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Sin docentes</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $area['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $area['activo'] ? 'Activa' : 'Inactiva' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarArea(<?= $area['id'] ?>)" 
                                                            title="Editar Área">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="gestionarCompetencias(<?= $area['id'] ?>)" 
                                                            title="Gestionar Competencias">
                                                        <i class="ti ti-target"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $area['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoArea(<?= $area['id'] ?>, <?= $area['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $area['activo'] ? 'Desactivar' : 'Activar' ?> Área">
                                                        <i class="ti <?= $area['activo'] ? 'ti-eye-off' : 'ti-eye' ?>"></i>
                                                    </button>
                                                    <?php if ($area['docentes_asignados'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="verDocentes(<?= $area['id'] ?>)" 
                                                                title="Ver Docentes">
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
    <?php include 'modales/areas/modal_agregar.php'; ?>
    <?php include 'modales/areas/modal_editar.php'; ?>
    <?php include 'modales/areas/modal_competencias.php'; ?>

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
        let tablaAreas;
        const competenciasBase = <?= json_encode($competencias_base) ?>;
        const competenciasPredefinidas = <?= json_encode($competencias_predefinidas) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaAreas = $('#tablaAreas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 15,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [5] }
                ]
            });

            // Filtros
            $('#filtroEstado, #filtroCompetencias, #filtroNivel, #filtroDocentes').on('change', aplicarFiltros);
            $('#buscarArea').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const competenciasFiltro = $('#filtroCompetencias').val();
            const nivelFiltro = $('#filtroNivel').val();
            const docentesFiltro = $('#filtroDocentes').val();
            const busqueda = $('#buscarArea').val().toLowerCase();

            $('#tablaAreas tbody tr').each(function() {
                const fila = $(this);
                const estado = fila.data('estado');
                const competencias = fila.data('competencias');
                const niveles = fila.data('niveles').toLowerCase();
                const docentes = parseInt(fila.data('docentes'));
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (estadoFiltro !== '' && estado != estadoFiltro) mostrar = false;
                if (competenciasFiltro !== '' && competencias != competenciasFiltro) mostrar = false;
                if (nivelFiltro && !niveles.includes(nivelFiltro.toLowerCase())) mostrar = false;
                if (docentesFiltro === '1+' && docentes === 0) mostrar = false;
                if (docentesFiltro === '0' && docentes > 0) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #filtroCompetencias, #filtroNivel, #filtroDocentes').val('');
            $('#buscarArea').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function toggleCompetenciasDetalle(areaId) {
            const detalleDiv = $(`#competencias-detalle-${areaId}`);
            
            if (detalleDiv.is(':visible')) {
                detalleDiv.slideUp();
            } else {
                mostrarCarga();
                
                fetch('modales/areas/procesar_areas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=obtener_competencias&id=${areaId}`
                })
                .then(response => response.json())
                .then(data => {
                    ocultarCarga();
                    
                    if (data.success) {
                        mostrarCompetenciasDetalle(areaId, data.competencias);
                        detalleDiv.slideDown();
                    } else {
                        mostrarError(data.message);
                    }
                })
                .catch(error => {
                    ocultarCarga();
                    mostrarError('Error al cargar competencias');
                });
            }
        }

        function mostrarCompetenciasDetalle(areaId, competencias) {
            const detalleDiv = $(`#competencias-detalle-${areaId}`);
            let html = '';
            
            if (!competencias || Object.keys(competencias).length === 0) {
                html = '<div class="sin-competencias">No hay competencias definidas</div>';
            } else {
                Object.keys(competencias).forEach(nivel => {
                    if (competencias[nivel] && typeof competencias[nivel] === 'object') {
                        html += `<div class="nivel-competencias">
                                    <div class="nivel-titulo">${nivel}</div>`;
                        
                        Object.keys(competencias[nivel]).forEach(grado => {
                            const comps = competencias[nivel][grado];
                            if (Array.isArray(comps) && comps.length > 0) {
                                html += `<div class="grado-competencias">
                                            <div class="grado-nombre">${grado}</div>`;
                                comps.forEach(competencia => {
                                    html += `<div class="competencia-texto">${competencia}</div>`;
                                });
                                html += '</div>';
                            }
                        });
                        
                        html += '</div>';
                    }
                });
            }
            
            detalleDiv.html(html);
        }

        function editarArea(id) {
            mostrarCarga();
            
            fetch('modales/areas/procesar_areas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionArea(data.area);
                    $('#modalEditarArea').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del área');
            });
        }

        function gestionarCompetencias(id) {
            mostrarCarga();
            
            fetch('modales/areas/procesar_areas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_completo&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarGestionCompetencias(data.area);
                    $('#modalGestionCompetencias').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar gestión de competencias');
            });
        }

        function asignarCompetencias(id) {
            gestionarCompetencias(id);
        }

        function toggleEstadoArea(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} esta área curricular?`,
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

            fetch('modales/areas/procesar_areas.php', {
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
                mostrarError('Error al cambiar estado del área');
            });
        }

        function verDocentes(id) {
            mostrarCarga();
            
            fetch('modales/areas/procesar_areas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=docentes_area&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDocentesArea(data.docentes);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar docentes');
            });
        }

        function mostrarDocentesArea(docentes) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Docente</th><th>Sección</th><th>Horas</th><th>Es Tutor</th></tr></thead><tbody>';
            
            docentes.forEach(docente => {
                html += `<tr>
                    <td>${docente.nombres} ${docente.apellidos}</td>
                    <td>${docente.grado} ${docente.seccion}</td>
                    <td>${docente.horas_semanales}h</td>
                    <td>${docente.es_tutor ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Docentes Asignados al Área',
                html: html,
                width: '700px',
                confirmButtonText: 'Cerrar'
            });
        }

        function exportarAreas() {
            window.open('reportes/exportar_areas.php', '_blank');
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