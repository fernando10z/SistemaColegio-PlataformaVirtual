<?php 
require_once 'conexion/bd.php';

// Obtener roles con estadísticas de usuarios
try {
    $sql = "SELECT r.*, 
                   COUNT(u.id) as total_usuarios,
                   COUNT(CASE WHEN u.activo = 1 THEN 1 END) as usuarios_activos
            FROM roles r 
            LEFT JOIN usuarios u ON r.id = u.rol_id 
            GROUP BY r.id 
            ORDER BY r.nivel_acceso DESC, r.nombre ASC";
    
    $stmt_roles = $conexion->prepare($sql);
    $stmt_roles->execute();
    $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $roles = [];
    $error_roles = "Error al cargar roles: " . $e->getMessage();
}

// Definir estructura de permisos del sistema
$permisos_sistema = [
    'academico' => [
        'nombre' => 'Académico',
        'permisos' => [
            'academico.cursos.ver' => 'Ver cursos',
            'academico.cursos.crear' => 'Crear cursos',
            'academico.cursos.editar' => 'Editar cursos',
            'academico.cursos.eliminar' => 'Eliminar cursos',
            'academico.calificaciones.ver' => 'Ver calificaciones',
            'academico.calificaciones.editar' => 'Editar calificaciones',
            'academico.asistencia.ver' => 'Ver asistencia',
            'academico.asistencia.registrar' => 'Registrar asistencia'
        ]
    ],
    'administrativo' => [
        'nombre' => 'Administrativo',
        'permisos' => [
            'admin.usuarios.ver' => 'Ver usuarios',
            'admin.usuarios.crear' => 'Crear usuarios',
            'admin.usuarios.editar' => 'Editar usuarios',
            'admin.usuarios.eliminar' => 'Eliminar usuarios',
            'admin.roles.ver' => 'Ver roles',
            'admin.roles.crear' => 'Crear roles',
            'admin.roles.editar' => 'Editar roles',
            'admin.reportes.ver' => 'Ver reportes',
            'admin.reportes.generar' => 'Generar reportes'
        ]
    ],
    'evaluaciones' => [
        'nombre' => 'Evaluaciones',
        'permisos' => [
            'eva.cuestionarios.ver' => 'Ver cuestionarios',
            'eva.cuestionarios.crear' => 'Crear cuestionarios',
            'eva.cuestionarios.editar' => 'Editar cuestionarios',
            'eva.tareas.ver' => 'Ver tareas',
            'eva.tareas.crear' => 'Crear tareas',
            'eva.tareas.calificar' => 'Calificar tareas'
        ]
    ],
    'comunicacion' => [
        'nombre' => 'Comunicación',
        'permisos' => [
            'com.mensajes.ver' => 'Ver mensajes',
            'com.mensajes.enviar' => 'Enviar mensajes',
            'com.anuncios.ver' => 'Ver anuncios',
            'com.anuncios.crear' => 'Crear anuncios',
            'com.notificaciones.enviar' => 'Enviar notificaciones'
        ]
    ],
    'sistema' => [
        'nombre' => 'Sistema',
        'permisos' => [
            'sistema.configuracion' => 'Configurar sistema',
            'sistema.auditoria' => 'Ver auditoría',
            'sistema.backups' => 'Gestionar respaldos',
            'sistema.mantenimiento' => 'Modo mantenimiento'
        ]
    ]
];

// Contar estadísticas
$total_roles = count($roles);
$roles_activos = count(array_filter($roles, function($r) { return $r['activo']; }));
$roles_inactivos = $total_roles - $roles_activos;
$total_usuarios_asignados = array_sum(array_column($roles, 'total_usuarios'));
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roles y Permisos - ANDRÉS AVELINO CÁCERES</title>
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
        .nivel-acceso-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }
        .permisos-preview {
            max-height: 100px;
            overflow-y: auto;
            font-size: 0.8rem;
        }
        .permiso-item {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            font-size: 0.7rem;
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
        .permisos-expandidos {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        .categoria-permisos {
            margin-bottom: 1rem;
        }
        .categoria-titulo {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.25rem;
        }
        .permiso-individual {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin: 0.1rem;
        }
        .rol-info {
            line-height: 1.3;
        }
        .rol-nombre {
            font-weight: 600;
            color: #495057;
        }
        .rol-descripcion {
            font-size: 0.85rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Roles y Permisos</h4>
                                <p class="mb-0 text-muted">Administra roles del sistema y permisos granulares</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="mostrarMatrizPermisos()">
                                    <i class="ti ti-table me-2"></i>
                                    Matriz de Permisos
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarRol">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Rol
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
                                <label class="form-label">Filtrar por Nivel</label>
                                <select class="form-select" id="filtroNivel">
                                    <option value="">Todos los niveles</option>
                                    <option value="10">Super Admin (10)</option>
                                    <option value="9">Director (9)</option>
                                    <option value="8">Subdirector (8)</option>
                                    <option value="6">Docente (6)</option>
                                    <option value="5">Tutor (5)</option>
                                    <option value="3">Apoderado (3)</option>
                                    <option value="2">Estudiante (2)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarRol" placeholder="Buscar por nombre o descripción...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh me-1"></i>
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Roles -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Roles y Permisos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaRoles">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rol</th>
                                        <th>Nivel Acceso</th>
                                        <th>Permisos</th>
                                        <th>Usuarios Asignados</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $rol): ?>
                                        <tr data-nivel="<?= $rol['nivel_acceso'] ?>" data-estado="<?= $rol['activo'] ?>">
                                            <td>
                                                <div class="rol-info">
                                                    <div class="rol-nombre"><?= htmlspecialchars($rol['nombre']) ?></div>
                                                    <div class="rol-descripcion"><?= htmlspecialchars($rol['descripcion'] ?: 'Sin descripción') ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge nivel-acceso-badge <?php
                                                    switch($rol['nivel_acceso']) {
                                                        case 10: echo 'bg-danger text-white'; break;
                                                        case 9: echo 'bg-warning text-dark'; break;
                                                        case 8: echo 'bg-info text-white'; break;
                                                        case 6: echo 'bg-primary text-white'; break;
                                                        case 5: echo 'bg-success text-white'; break;
                                                        case 3: echo 'bg-secondary text-white'; break;
                                                        case 2: echo 'bg-light text-dark'; break;
                                                        default: echo 'bg-secondary text-white';
                                                    }
                                                ?>">
                                                    Nivel <?= $rol['nivel_acceso'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="permisos-preview">
                                                    <?php 
                                                    $permisos = json_decode($rol['permisos'], true) ?: [];
                                                    if (in_array('*', $permisos)) {
                                                        echo '<span class="permiso-item bg-danger text-white">TODOS LOS PERMISOS</span>';
                                                    } else {
                                                        $count = 0;
                                                        foreach ($permisos as $permiso) {
                                                            if ($count < 3) {
                                                                echo '<span class="permiso-item">' . htmlspecialchars($permiso) . '</span>';
                                                                $count++;
                                                            }
                                                        }
                                                        if (count($permisos) > 3) {
                                                            echo '<span class="permiso-item bg-info text-white">+' . (count($permisos) - 3) . ' más</span>';
                                                        }
                                                    }
                                                    ?>
                                                    <?php if (empty($permisos)): ?>
                                                        <small class="text-muted">Sin permisos asignados</small>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                        onclick="togglePermisosDetalle(<?= $rol['id'] ?>)">
                                                    <i class="ti ti-eye"></i> Ver detalle
                                                </button>
                                                <div id="permisos-detalle-<?= $rol['id'] ?>" class="permisos-expandidos" style="display: none;">
                                                    <!-- Detalle de permisos se carga aquí -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="badge bg-primary fs-6"><?= $rol['total_usuarios'] ?></span>
                                                    <br>
                                                    <small class="text-muted">
                                                        (<?= $rol['usuarios_activos'] ?> activos)
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $rol['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $rol['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($rol['fecha_creacion'])) ?>
                                                </small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarRol(<?= $rol['id'] ?>)" 
                                                            title="Editar Rol">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="gestionarPermisos(<?= $rol['id'] ?>)" 
                                                            title="Gestionar Permisos">
                                                        <i class="ti ti-key"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $rol['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoRol(<?= $rol['id'] ?>, <?= $rol['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $rol['activo'] ? 'Desactivar' : 'Activar' ?> Rol">
                                                        <i class="ti <?= $rol['activo'] ? 'ti-eye-off' : 'ti-eye' ?>"></i>
                                                    </button>
                                                    <?php if ($rol['total_usuarios'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="verUsuariosRol(<?= $rol['id'] ?>)" 
                                                                title="Ver Usuarios">
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
    <?php include 'modales/roles/modal_agregar.php'; ?>
    <?php include 'modales/roles/modal_editar.php'; ?>
    <?php include 'modales/roles/modal_permisos.php'; ?>

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
        let tablaRoles;
        const permisosSistema = <?= json_encode($permisos_sistema) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaRoles = $('#tablaRoles').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros personalizados
            $('#filtroNivel, #filtroEstado').on('change', aplicarFiltros);
            $('#buscarRol').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const nivelFiltro = $('#filtroNivel').val();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarRol').val().toLowerCase();

            $('#tablaRoles tbody tr').each(function() {
                const fila = $(this);
                const nivel = fila.data('nivel');
                const estado = fila.data('estado');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtro por nivel
                if (nivelFiltro && nivel != nivelFiltro) {
                    mostrar = false;
                }

                // Filtro por estado
                if (estadoFiltro !== '' && estado != estadoFiltro) {
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
            $('#filtroNivel, #filtroEstado').val('');
            $('#buscarRol').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function togglePermisosDetalle(rolId) {
            const detalleDiv = $(`#permisos-detalle-${rolId}`);
            
            if (detalleDiv.is(':visible')) {
                detalleDiv.slideUp();
            } else {
                // Cargar permisos detallados
                mostrarCarga();
                
                fetch('modales/roles/procesar_roles.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=obtener_permisos&id=${rolId}`
                })
                .then(response => response.json())
                .then(data => {
                    ocultarCarga();
                    
                    if (data.success) {
                        mostrarPermisosDetalle(rolId, data.permisos);
                        detalleDiv.slideDown();
                    } else {
                        mostrarError(data.message);
                    }
                })
                .catch(error => {
                    ocultarCarga();
                    mostrarError('Error al cargar permisos');
                });
            }
        }

        function mostrarPermisosDetalle(rolId, permisos) {
            const detalleDiv = $(`#permisos-detalle-${rolId}`);
            let html = '';
            
            if (permisos.includes('*')) {
                html = '<div class="alert alert-danger"><strong>TODOS LOS PERMISOS DEL SISTEMA</strong></div>';
            } else {
                // Agrupar permisos por categoría
                Object.keys(permisosSistema).forEach(categoria => {
                    const permisosCategoria = permisos.filter(p => p.startsWith(categoria + '.'));
                    if (permisosCategoria.length > 0) {
                        html += `<div class="categoria-permisos">
                                    <div class="categoria-titulo">${permisosSistema[categoria].nombre}</div>
                                    <div>`;
                        permisosCategoria.forEach(permiso => {
                            const descripcion = permisosSistema[categoria].permisos[permiso] || permiso;
                            html += `<span class="permiso-individual">${descripcion}</span>`;
                        });
                        html += '</div></div>';
                    }
                });
                
                if (html === '') {
                    html = '<div class="text-muted">No hay permisos específicos asignados</div>';
                }
            }
            
            detalleDiv.html(html);
        }

        function editarRol(id) {
            mostrarCarga();
            
            fetch('modales/roles/procesar_roles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionRol(data.rol);
                    $('#modalEditarRol').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del rol');
            });
        }

        function gestionarPermisos(id) {
            mostrarCarga();
            
            fetch('modales/roles/procesar_roles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_permisos_completos&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarPermisosRol(data.rol);
                    $('#modalGestionPermisos').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar permisos del rol');
            });
        }

        function toggleEstadoRol(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este rol?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstadoRol(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstadoRol(id, estado) {
            mostrarCarga();

            fetch('modales/roles/procesar_roles.php', {
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
                mostrarError('Error al cambiar estado del rol');
            });
        }

        function verUsuariosRol(id) {
            mostrarCarga();
            
            fetch('modales/roles/procesar_roles.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=usuarios_rol&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarUsuariosRol(data.usuarios);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar usuarios del rol');
            });
        }

        function mostrarUsuariosRol(usuarios) {
            let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Usuario</th><th>Email</th><th>Estado</th></tr></thead><tbody>';
            
            usuarios.forEach(usuario => {
                html += `<tr>
                    <td>${usuario.nombres} ${usuario.apellidos}</td>
                    <td>${usuario.email}</td>
                    <td><span class="badge ${usuario.activo ? 'bg-success' : 'bg-danger'}">${usuario.activo ? 'Activo' : 'Inactivo'}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            Swal.fire({
                title: 'Usuarios con este Rol',
                html: html,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        }

        function mostrarMatrizPermisos() {
            window.open('reportes/matriz_permisos.php', '_blank');
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