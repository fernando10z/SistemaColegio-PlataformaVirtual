<?php 
    require_once 'conexion/bd.php';

    // Obtener docentes con información completa
    try {
        $sql = "SELECT d.*, 
                    u.username, u.email as user_email, u.activo as usuario_activo,
                    COUNT(DISTINCT ad.id) as total_asignaciones,
                    COUNT(DISTINCT ad.seccion_id) as secciones_asignadas,
                    GROUP_CONCAT(DISTINCT ac.nombre SEPARATOR ', ') as areas_nombres
                FROM docentes d
                LEFT JOIN usuarios u ON d.usuario_id = u.id
                LEFT JOIN asignaciones_docentes ad ON d.id = ad.docente_id AND ad.activo = 1
                LEFT JOIN areas_curriculares ac ON JSON_CONTAINS(d.areas_especialidad, CAST(ac.id AS JSON))
                GROUP BY d.id
                ORDER BY d.activo DESC, d.nombres ASC";
        
        $stmt_docentes = $conexion->prepare($sql);
        $stmt_docentes->execute();
        $docentes = $stmt_docentes->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $docentes = [];
        $error_docentes = "Error al cargar docentes: " . $e->getMessage();
    }

    // Obtener áreas curriculares para filtros
    try {
        $stmt_areas = $conexion->prepare("SELECT * FROM areas_curriculares WHERE activo = 1 ORDER BY nombre ASC");
        $stmt_areas->execute();
        $areas_curriculares = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $areas_curriculares = [];
    }

    // Obtener usuarios disponibles (sin asignar a otros docentes)
    try {
        $sql_usuarios = "SELECT u.* FROM usuarios u 
                        LEFT JOIN docentes d ON u.id = d.usuario_id 
                        WHERE d.usuario_id IS NULL AND u.activo = 1 AND u.rol_id = 4
                        ORDER BY u.nombres ASC";
        $stmt_usuarios = $conexion->prepare($sql_usuarios);
        $stmt_usuarios->execute();
        $usuarios_disponibles = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $usuarios_disponibles = [];
    }

    // Calcular estadísticas
    $total_docentes = count($docentes);
    $docentes_activos = count(array_filter($docentes, function($d) { return $d['activo']; }));
    $docentes_inactivos = $total_docentes - $docentes_activos;
    $docentes_con_usuario = count(array_filter($docentes, function($d) { return $d['usuario_id']; }));
    $docentes_con_asignaciones = count(array_filter($docentes, function($d) { return $d['total_asignaciones'] > 0; }));

    // Estadísticas por categoría
    $categorias_count = [];
    foreach ($docentes as $docente) {
        $datos_laborales = json_decode($docente['datos_laborales'], true);
        $categoria = $datos_laborales['categoria'] ?? 'Sin categoría';
        $categorias_count[$categoria] = ($categorias_count[$categoria] ?? 0) + 1;
    }

    // Estadísticas por tipo de contrato
    $contratos_count = [];
    foreach ($docentes as $docente) {
        $datos_laborales = json_decode($docente['datos_laborales'], true);
        $contrato = $datos_laborales['tipo_contrato'] ?? 'Sin definir';
        $contratos_count[$contrato] = ($contratos_count[$contrato] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Docentes - ANDRÉS AVELINO CÁCERES</title>
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
        .docente-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        .docente-info {
            line-height: 1.3;
        }
        .docente-nombre {
            font-weight: 600;
            color: #495057;
        }
        .docente-codigo {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .categoria-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .especialidad-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            margin: 0.1rem;
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
        .colegiatura-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .asignaciones-info {
            text-align: center;
        }
        .asignaciones-numero {
            font-weight: 600;
            color: #495057;
            font-size: 1.1rem;
        }
        .datos-profesionales {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .datos-laborales {
            background-color: #e7f3ff;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .usuario-badge {
            font-size: 0.7rem;
        }
        .experiencia-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
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
                                <h4 class="fw-bold mb-0">Gestión de Docentes</h4>
                                <p class="mb-0 text-muted">Administra el personal docente y sus especialidades</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarDocente">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Docente
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
                                <label class="form-label">Especialidad</label>
                                <select class="form-select" id="filtroEspecialidad">
                                    <option value="">Todas las especialidades</option>
                                    <?php foreach ($areas_curriculares as $area): ?>
                                        <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo Contrato</label>
                                <select class="form-select" id="filtroContrato">
                                    <option value="">Todos</option>
                                    <option value="NOMBRADO">Nombrado</option>
                                    <option value="CONTRATADO">Contratado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" id="filtroCategoria">
                                    <option value="">Todas</option>
                                    <option value="I">Categoría I</option>
                                    <option value="II">Categoría II</option>
                                    <option value="III">Categoría III</option>
                                    <option value="IV">Categoría IV</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarDocente" placeholder="Buscar por nombre, código, colegiatura...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarDocentes()">
                                    <i class="ti ti-download me-2"></i>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Docentes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Docentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaDocentes">
                                <thead class="table-light">
                                    <tr>
                                        <th>Docente</th>
                                        <th>Datos Profesionales</th>
                                        <th>Especialidades</th>
                                        <th>Datos Laborales</th>
                                        <th>Asignaciones</th>
                                        <th>Usuario</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($docentes as $docente): 
                                        $datos_personales = json_decode($docente['datos_personales'], true) ?: [];
                                        $datos_profesionales = json_decode($docente['datos_profesionales'], true) ?: [];
                                        $datos_laborales = json_decode($docente['datos_laborales'], true) ?: [];
                                        $areas_especialidad = json_decode($docente['areas_especialidad'], true) ?: [];
                                        
                                        // Calcular años de experiencia
                                        $fecha_ingreso = $datos_laborales['fecha_ingreso'] ?? null;
                                        $anos_experiencia = 0;
                                        if ($fecha_ingreso) {
                                            $anos_experiencia = date('Y') - date('Y', strtotime($fecha_ingreso));
                                        }
                                    ?>
                                        <tr data-especialidad="<?= implode(',', $areas_especialidad) ?>" 
                                            data-estado="<?= $docente['activo'] ?>"
                                            data-contrato="<?= $datos_laborales['tipo_contrato'] ?? '' ?>"
                                            data-categoria="<?= $datos_laborales['categoria'] ?? '' ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $docente['foto_url'] ?: '../assets/images/profile/user-default.jpg' ?>" 
                                                         class="docente-avatar me-3" alt="Avatar">
                                                    <div class="docente-info">
                                                        <div class="docente-nombre"><?= htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']) ?></div>
                                                        <div class="docente-codigo"><?= htmlspecialchars($docente['codigo_docente'] ?: 'Sin código') ?></div>
                                                        <small class="text-muted">
                                                            <?= $docente['documento_tipo'] ?>: <?= htmlspecialchars($docente['documento_numero']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($datos_profesionales['grado_academico'] ?? 'No especificado') ?></strong><br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($datos_profesionales['universidad'] ?? 'Universidad no registrada') ?>
                                                    </small>
                                                    <?php if (!empty($datos_profesionales['colegiatura'])): ?>
                                                        <div class="colegiatura-info mt-1">
                                                            <span class="badge bg-info usuario-badge">
                                                                Col: <?= htmlspecialchars($datos_profesionales['colegiatura']) ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php if (!empty($docente['areas_nombres'])): ?>
                                                        <?php 
                                                        $areas_array = explode(', ', $docente['areas_nombres']);
                                                        foreach ($areas_array as $area):
                                                        ?>
                                                            <span class="badge especialidad-badge bg-primary"><?= htmlspecialchars($area) ?></span>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <small class="text-muted">Sin especialidades asignadas</small>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($datos_profesionales['especialidad'])): ?>
                                                        <div class="mt-1">
                                                            <small class="text-muted">
                                                                Esp: <?= htmlspecialchars($datos_profesionales['especialidad']) ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php if (!empty($datos_laborales['categoria'])): ?>
                                                        <span class="badge categoria-badge <?php
                                                            switch($datos_laborales['categoria']) {
                                                                case 'I': echo 'bg-danger'; break;
                                                                case 'II': echo 'bg-warning text-dark'; break;
                                                                case 'III': echo 'bg-info'; break;
                                                                case 'IV': echo 'bg-success'; break;
                                                                default: echo 'bg-secondary';
                                                            }
                                                        ?>">
                                                            Cat. <?= $datos_laborales['categoria'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($datos_laborales['tipo_contrato'])): ?>
                                                        <br><span class="badge bg-secondary usuario-badge mt-1">
                                                            <?= $datos_laborales['tipo_contrato'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($anos_experiencia > 0): ?>
                                                        <br><span class="experiencia-badge mt-1">
                                                            <?= $anos_experiencia ?> años
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="asignaciones-info">
                                                    <div class="asignaciones-numero"><?= $docente['total_asignaciones'] ?></div>
                                                    <small class="text-muted">
                                                        <?= $docente['secciones_asignadas'] ?> secciones
                                                    </small>
                                                    <?php if ($docente['total_asignaciones'] > 0): ?>
                                                        <br><button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                                onclick="verAsignaciones(<?= $docente['id'] ?>)">
                                                            <i class="ti ti-eye"></i> Ver
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($docente['usuario_id']): ?>
                                                    <div>
                                                        <span class="badge <?= $docente['usuario_activo'] ? 'bg-success' : 'bg-danger' ?> usuario-badge">
                                                            <?= $docente['usuario_activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                        <br><small class="text-muted">@<?= htmlspecialchars($docente['username']) ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-warning usuario-badge">Sin usuario</span>
                                                    <br><button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                            onclick="asignarUsuario(<?= $docente['id'] ?>)">
                                                        <i class="ti ti-user-plus"></i> Asignar
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $docente['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $docente['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarDocente(<?= $docente['id'] ?>)" 
                                                            title="Editar Docente">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verPerfilCompleto(<?= $docente['id'] ?>)" 
                                                            title="Ver Perfil">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $docente['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoDocente(<?= $docente['id'] ?>, <?= $docente['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $docente['activo'] ? 'Desactivar' : 'Activar' ?> Docente">
                                                        <i class="ti <?= $docente['activo'] ? 'ti-user-off' : 'ti-user-check' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            onclick="gestionarEspecialidades(<?= $docente['id'] ?>)" 
                                                            title="Gestionar Especialidades">
                                                        <i class="ti ti-school"></i>
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

                <!-- Resumen por Categorías y Contratos -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Categoría</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($categorias_count as $categoria => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= $categoria ?></span>
                                        <span class="badge bg-primary"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Contrato</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($contratos_count as $contrato => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= $contrato ?></span>
                                        <span class="badge bg-info"><?= $count ?></span>
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
    <?php include 'modales/docentes/modal_agregar.php'; ?>
    <?php include 'modales/docentes/modal_editar.php'; ?>
    <?php include 'modales/docentes/modal_especialidades.php'; ?>

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
        let tablaDocentes;
        const usuariosDisponibles = <?= json_encode($usuarios_disponibles) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaDocentes = $('#tablaDocentes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Filtros personalizados
            $('#filtroEspecialidad, #filtroEstado, #filtroContrato, #filtroCategoria').on('change', aplicarFiltros);
            $('#buscarDocente').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const especialidadFiltro = $('#filtroEspecialidad').val();
            const estadoFiltro = $('#filtroEstado').val();
            const contratoFiltro = $('#filtroContrato').val();
            const categoriaFiltro = $('#filtroCategoria').val();
            const busqueda = $('#buscarDocente').val().toLowerCase();

            $('#tablaDocentes tbody tr').each(function() {
                const fila = $(this);
                const especialidades = fila.data('especialidad').toString().split(',');
                const estado = fila.data('estado');
                const contrato = fila.data('contrato');
                const categoria = fila.data('categoria');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtro por especialidad
                if (especialidadFiltro && !especialidades.includes(especialidadFiltro)) {
                    mostrar = false;
                }

                // Filtro por estado
                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                // Filtro por contrato
                if (contratoFiltro && contrato !== contratoFiltro) {
                    mostrar = false;
                }

                // Filtro por categoría
                if (categoriaFiltro && categoria !== categoriaFiltro) {
                    mostrar = false;
                }

                // Filtro por texto
                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEspecialidad, #filtroEstado, #filtroContrato, #filtroCategoria').val('');
            $('#buscarDocente').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarDocente(id) {
            mostrarCarga();
            
            fetch('modales/docentes/procesar_docentes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionDocente(data.docente);
                    $('#modalEditarDocente').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del docente');
            });
        }

        function verPerfilCompleto(id) {
            mostrarCarga();
            
            fetch('modales/docentes/procesar_docentes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=perfil_completo&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarPerfilCompleto(data.docente);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar perfil del docente');
            });
        }

        function mostrarPerfilCompleto(docente) {
            const profesionales = docente.datos_profesionales || {};
            const laborales = docente.datos_laborales || {};
            const personales = docente.datos_personales || {};
            
            Swal.fire({
                title: `${docente.nombres} ${docente.apellidos}`,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Información Personal:</strong><br>
                                <small>Código: ${docente.codigo_docente || 'N/A'}</small><br>
                                <small>Email: ${personales.email || 'No registrado'}</small><br>
                                <small>Teléfono: ${personales.telefono || 'No registrado'}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Datos Profesionales:</strong><br>
                                <small>Grado: ${profesionales.grado_academico || 'N/A'}</small><br>
                                <small>Universidad: ${profesionales.universidad || 'N/A'}</small><br>
                                <small>Colegiatura: ${profesionales.colegiatura || 'N/A'}</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Datos Laborales:</strong><br>
                                <small>Categoría: ${laborales.categoria || 'N/A'}</small><br>
                                <small>Contrato: ${laborales.tipo_contrato || 'N/A'}</small><br>
                                <small>Nivel Magisterial: ${laborales.nivel_magisterial || 'N/A'}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Asignaciones:</strong><br>
                                <small>Total: ${docente.total_asignaciones || 0}</small><br>
                                <small>Secciones: ${docente.secciones_asignadas || 0}</small>
                            </div>
                        </div>
                    </div>
                `,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function toggleEstadoDocente(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este docente?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstadoDocente(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstadoDocente(id, estado) {
            mostrarCarga();

            fetch('modales/docentes/procesar_docentes.php', {
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
                mostrarError('Error al cambiar estado del docente');
            });
        }

        function asignarUsuario(docenteId) {
            if (usuariosDisponibles.length === 0) {
                mostrarError('No hay usuarios disponibles para asignar');
                return;
            }

            let opciones = '<select class="form-select" id="usuarioSeleccionado">';
            opciones += '<option value="">Seleccionar usuario...</option>';
            usuariosDisponibles.forEach(usuario => {
                opciones += `<option value="${usuario.id}">${usuario.nombres} ${usuario.apellidos} (${usuario.email})</option>`;
            });
            opciones += '</select>';

            Swal.fire({
                title: 'Asignar Usuario',
                html: `<div class="mb-3">
                    <label class="form-label">Usuario disponible:</label>
                    ${opciones}
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Asignar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const usuarioId = document.getElementById('usuarioSeleccionado').value;
                    if (!usuarioId) {
                        Swal.showValidationMessage('Debe seleccionar un usuario');
                        return false;
                    }
                    return usuarioId;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarAsignacionUsuario(docenteId, result.value);
                }
            });
        }

        function ejecutarAsignacionUsuario(docenteId, usuarioId) {
            mostrarCarga();

            fetch('modales/docentes/procesar_docentes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=asignar_usuario&docente_id=${docenteId}&usuario_id=${usuarioId}`
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
                mostrarError('Error al asignar usuario');
            });
        }

        function gestionarEspecialidades(id) {
            // Cargar modal de especialidades
            $('#modalGestionEspecialidades').modal('show');
        }

        function verAsignaciones(id) {
            mostrarCarga();
            
            fetch('modales/docentes/procesar_docentes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=asignaciones&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarAsignaciones(data.asignaciones);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar asignaciones');
            });
        }

        function mostrarAsignaciones(asignaciones) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Área</th><th>Sección</th><th>Horas</th><th>Tutor</th></tr></thead><tbody>';
            
            asignaciones.forEach(asignacion => {
                html += `<tr>
                    <td>${asignacion.area_nombre}</td>
                    <td>${asignacion.grado} - ${asignacion.seccion}</td>
                    <td>${asignacion.horas_semanales}h</td>
                    <td><span class="badge ${asignacion.es_tutor ? 'bg-success' : 'bg-secondary'}">${asignacion.es_tutor ? 'Sí' : 'No'}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Asignaciones del Docente',
                html: html,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function exportarDocentes() {
            window.open('reportes/exportar_docentes.php', '_blank');
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