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

    // Obtener malla curricular con relaciones
    try {
        $sql = "SELECT mc.*, 
                    ne.nombre as nivel_nombre, ne.codigo as nivel_codigo,
                    ac.nombre as area_nombre, ac.codigo as area_codigo,
                    pa.nombre as periodo_nombre, pa.anio as periodo_anio
                FROM malla_curricular mc
                INNER JOIN niveles_educativos ne ON mc.nivel_id = ne.id
                INNER JOIN areas_curriculares ac ON mc.area_id = ac.id
                INNER JOIN periodos_academicos pa ON mc.periodo_academico_id = pa.id
                WHERE mc.activo = 1 AND ne.activo = 1 AND ac.activo = 1
                ORDER BY ne.orden ASC, mc.grado ASC, ac.nombre ASC";
        
        $stmt_malla = $conexion->prepare($sql);
        $stmt_malla->execute();
        $malla_curricular = $stmt_malla->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $malla_curricular = [];
        $error_malla = "Error al cargar malla curricular: " . $e->getMessage();
    }

    // Obtener niveles educativos
    try {
        $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
        $stmt_niveles->execute();
        $niveles = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $niveles = [];
    }

    // Obtener áreas curriculares
    try {
        $stmt_areas = $conexion->prepare("SELECT * FROM areas_curriculares WHERE activo = 1 ORDER BY nombre ASC");
        $stmt_areas->execute();
        $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $areas = [];
    }

    // Obtener períodos académicos
    try {
        $stmt_periodos = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 ORDER BY anio DESC");
        $stmt_periodos->execute();
        $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $periodos = [];
    }

    // Calcular estadísticas por nivel y grado
    $estadisticas_nivel = [];
    $horas_por_grado = [];
    $total_asignaciones = count($malla_curricular);

    foreach ($malla_curricular as $item) {
        $nivel = $item['nivel_nombre'];
        $grado = $item['grado'];
        $clave_grado = $nivel . ' - ' . $grado;
        
        // Contar por nivel
        $estadisticas_nivel[$nivel] = ($estadisticas_nivel[$nivel] ?? 0) + 1;
        
        // Sumar horas por grado
        if (!isset($horas_por_grado[$clave_grado])) {
            $horas_por_grado[$clave_grado] = 0;
        }
        $horas_por_grado[$clave_grado] += $item['horas_semanales'];
    }

    $total_niveles = count($estadisticas_nivel);
    $total_areas = count($areas);
    $promedio_horas = $total_asignaciones > 0 ? round(array_sum(array_column($malla_curricular, 'horas_semanales')) / $total_asignaciones, 1) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Malla Curricular - ANDRÉS AVELINO CÁCERES</title>
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
        .horas-badge {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .nivel-badge {
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
        .competencias-preview {
            max-height: 80px;
            overflow-y: auto;
            font-size: 0.8rem;
            background-color: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        .grado-info {
            line-height: 1.2;
        }
        .grado-nombre {
            font-weight: 600;
            color: #495057;
        }
        .horas-totales {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            text-align: center;
            font-weight: 600;
        }
        .validacion-horas {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .malla-matriz {
            max-height: 400px;
            overflow-y: auto;
        }
        .matriz-cell {
            min-width: 60px;
            text-align: center;
            vertical-align: middle;
        }
        .periodo-actual {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Malla Curricular</h4>
                                <p class="mb-0 text-muted">Asignación de áreas curriculares por nivel, grado y horas académicas</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarArea">
                                    <i class="ti ti-plus me-2"></i>
                                    Asignar Área
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Período Actual -->
                <?php if ($periodo_actual): ?>
                <div class="periodo-actual">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="fw-bold text-primary fs-5"><?= htmlspecialchars($periodo_actual['nombre']) ?></div>
                            <div class="text-muted">
                                <i class="ti ti-calendar"></i>
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_inicio'])) ?> - 
                                <?= date('d/m/Y', strtotime($periodo_actual['fecha_fin'])) ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted small">Malla curricular vigente</div>
                            <div class="fw-bold"><?= $periodo_actual['anio'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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
                                <label class="form-label">Área Curricular</label>
                                <select class="form-select" id="filtroArea">
                                    <option value="">Todas las áreas</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
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
                                <label class="form-label">Rango de Horas</label>
                                <select class="form-select" id="filtroHoras">
                                    <option value="">Todas</option>
                                    <option value="1-2">1-2 horas</option>
                                    <option value="3-4">3-4 horas</option>
                                    <option value="5-6">5-6 horas</option>
                                    <option value="7+">7+ horas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarMalla" placeholder="Buscar en malla...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarMalla()">
                                    <i class="ti ti-download"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Malla Curricular -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Malla Curricular Detallada</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaMalla">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nivel / Grado</th>
                                        <th>Área Curricular</th>
                                        <th>Horas Semanales</th>
                                        <th>Competencias</th>
                                        <th>Período Académico</th>
                                        <th>Validación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($malla_curricular as $item): 
                                        // Validar horas totales por grado
                                        $clave_grado = $item['nivel_nombre'] . ' - ' . $item['grado'];
                                        $horas_totales_grado = $horas_por_grado[$clave_grado] ?? 0;
                                        $validacion_horas = 'success';
                                        $texto_validacion = 'Correcto';
                                        
                                        if ($horas_totales_grado > 35) {
                                            $validacion_horas = 'danger';
                                            $texto_validacion = 'Sobrecarga';
                                        } elseif ($horas_totales_grado < 25) {
                                            $validacion_horas = 'warning';
                                            $texto_validacion = 'Subcarga';
                                        }
                                    ?>
                                        <tr data-nivel="<?= $item['nivel_id'] ?>" 
                                            data-area="<?= $item['area_id'] ?>"
                                            data-periodo="<?= $item['periodo_academico_id'] ?>"
                                            data-horas="<?= $item['horas_semanales'] ?>">
                                            <td>
                                                <div class="grado-info">
                                                    <span class="badge nivel-badge <?php
                                                        switch($item['nivel_codigo']) {
                                                            case 'INI': echo 'bg-success'; break;
                                                            case 'PRI': echo 'bg-primary'; break;
                                                            case 'SEC': echo 'bg-info'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>"><?= htmlspecialchars($item['nivel_codigo']) ?></span>
                                                    <div class="grado-nombre"><?= htmlspecialchars($item['nivel_nombre']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($item['grado']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="area-info">
                                                    <div class="area-nombre"><?= htmlspecialchars($item['area_nombre']) ?></div>
                                                    <div class="area-codigo">Código: <?= htmlspecialchars($item['area_codigo']) ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge horas-badge <?php
                                                    if ($item['horas_semanales'] <= 2) echo 'bg-light text-dark';
                                                    elseif ($item['horas_semanales'] <= 4) echo 'bg-primary';
                                                    elseif ($item['horas_semanales'] <= 6) echo 'bg-warning text-dark';
                                                    else echo 'bg-danger';
                                                ?>">
                                                    <?= $item['horas_semanales'] ?> hrs/sem
                                                </span>
                                            </td>
                                            <td>
                                                <div class="competencias-preview">
                                                    <?php 
                                                    $competencias = json_decode($item['competencias_grado'], true);
                                                    if ($competencias && is_array($competencias)):
                                                        foreach (array_slice($competencias, 0, 2) as $comp):
                                                    ?>
                                                        <div class="small mb-1">• <?= htmlspecialchars($comp) ?></div>
                                                    <?php 
                                                        endforeach;
                                                        if (count($competencias) > 2):
                                                    ?>
                                                        <div class="small text-muted">+<?= count($competencias) - 2 ?> más...</div>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                        <small class="text-muted">No definidas</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($item['periodo_nombre']) ?><br>
                                                    <?= $item['periodo_anio'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge validacion-horas bg-<?= $validacion_horas ?>">
                                                    <?= $texto_validacion ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    Total: <?= $horas_totales_grado ?>h
                                                </small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarAsignacion(<?= $item['id'] ?>)" 
                                                            title="Editar Asignación">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="gestionarCompetencias(<?= $item['id'] ?>)" 
                                                            title="Gestionar Competencias">
                                                        <i class="ti ti-target"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="eliminarAsignacion(<?= $item['id'] ?>)" 
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

                <!-- Resumen por Nivel -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Horas Totales por Grado</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($horas_por_grado as $grado => $horas): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small"><?= htmlspecialchars($grado) ?></span>
                                        <div class="horas-totales">
                                            <?= $horas ?> horas
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Nivel</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($estadisticas_nivel as $nivel => $cantidad): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($nivel) ?></span>
                                        <span class="badge bg-primary"><?= $cantidad ?> áreas</span>
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
    <?php include 'modales/malla/modal_asignar.php'; ?>
    <?php include 'modales/malla/modal_editar.php'; ?>
    <?php include 'modales/malla/modal_competencias.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="assets/js/sistema-auxiliares.js"></script>                              
    
    <script>
        let tablaMalla;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaMalla = $('#tablaMalla').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[0, 'asc'], [1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros personalizados
            $('#filtroNivel, #filtroArea, #filtroPeriodo, #filtroHoras').on('change', aplicarFiltros);
            $('#buscarMalla').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const nivelFiltro = $('#filtroNivel').val();
            const areaFiltro = $('#filtroArea').val();
            const periodoFiltro = $('#filtroPeriodo').val();
            const horasFiltro = $('#filtroHoras').val();
            const busqueda = $('#buscarMalla').val().toLowerCase();

            $('#tablaMalla tbody tr').each(function() {
                const fila = $(this);
                const nivel = fila.data('nivel');
                const area = fila.data('area');
                const periodo = fila.data('periodo');
                const horas = parseInt(fila.data('horas'));
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtro por nivel
                if (nivelFiltro && nivel != nivelFiltro) {
                    mostrar = false;
                }

                // Filtro por área
                if (areaFiltro && area != areaFiltro) {
                    mostrar = false;
                }

                // Filtro por período
                if (periodoFiltro && periodo != periodoFiltro) {
                    mostrar = false;
                }

                // Filtro por horas
                if (horasFiltro) {
                    const [min, max] = horasFiltro.includes('+') ? 
                        [parseInt(horasFiltro.replace('+', '')), Infinity] :
                        horasFiltro.split('-').map(x => parseInt(x));
                    
                    if (horas < min || (max !== Infinity && horas > max)) {
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
            $('#filtroNivel, #filtroArea, #filtroHoras').val('');
            $('#buscarMalla').val('');
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
            
            fetch('modales/malla/procesar_malla.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.asignacion);
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

        function gestionarCompetencias(id) {
            mostrarCarga();
            
            fetch('modales/malla/procesar_malla.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_competencias&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarCompetencias(data.asignacion);
                    $('#modalCompetencias').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar competencias');
            });
        }

        function duplicarAsignacion(id) {
            Swal.fire({
                title: 'Duplicar Asignación',
                text: '¿Deseas copiar esta asignación a otros grados?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, duplicar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Abrir modal de duplicación
                    $('#modalDuplicarAsignacion').modal('show');
                }
            });
        }

        function eliminarAsignacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas eliminar esta asignación de la malla curricular?',
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

            fetch('modales/malla/procesar_malla.php', {
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



        function validarHorasTotales() {
            // Función para validar que las horas totales por grado estén en rangos apropiados
            const advertencias = [];
            
            $('#tablaMalla tbody tr').each(function() {
                const validacion = $(this).find('.validacion-horas');
                if (validacion.hasClass('bg-danger')) {
                    const grado = $(this).find('.grado-nombre').text() + ' ' + $(this).find('.text-muted').text();
                    advertencias.push(`${grado}: Sobrecarga de horas académicas`);
                }
            });
            
            if (advertencias.length > 0) {
                Swal.fire({
                    title: 'Advertencias de Validación',
                    html: '<ul class="text-left">' + advertencias.map(a => `<li>${a}</li>`).join('') + '</ul>',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
            }
        }

        function exportarMalla() {
            window.open('reportes/exportar_malla.php', '_blank');
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

        // Validar automáticamente al cargar
        $(document).ready(function() {
            setTimeout(validarHorasTotales, 1000);
        });
    </script>
</body>
</html>