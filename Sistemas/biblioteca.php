<?php 
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
    require_once 'conexion/bd.php';

    // Obtener materiales bibliográficos con estadísticas
    try {
        $sql = "SELECT mb.*, 
                    COUNT(DISTINCT e.id) as total_ejemplares,
                    SUM(CASE WHEN e.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as ejemplares_disponibles,
                    SUM(CASE WHEN e.estado = 'PRESTADO' THEN 1 ELSE 0 END) as ejemplares_prestados
                FROM material_bibliografico mb
                LEFT JOIN ejemplares e ON mb.id = e.material_id
                GROUP BY mb.id
                ORDER BY mb.fecha_creacion DESC";
        
        $stmt_materiales = $conexion->prepare($sql);
        $stmt_materiales->execute();
        $materiales = $stmt_materiales->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $materiales = [];
        $error_materiales = "Error al cargar materiales: " . $e->getMessage();
    }

    // Calcular estadísticas generales
    $total_materiales = count($materiales);
    $materiales_activos = count(array_filter($materiales, function($m) { return $m['activo']; }));
    $materiales_inactivos = $total_materiales - $materiales_activos;
    
    $total_ejemplares = array_sum(array_column($materiales, 'total_ejemplares'));
    $ejemplares_disponibles = array_sum(array_column($materiales, 'ejemplares_disponibles'));
    $ejemplares_prestados = array_sum(array_column($materiales, 'ejemplares_prestados'));

    // Estadísticas por categoría
    $categorias_count = [];
    foreach ($materiales as $material) {
        $clasificacion = json_decode($material['clasificacion'], true);
        $categoria = $clasificacion['categoria'] ?? 'Sin categoría';
        $categorias_count[$categoria] = ($categorias_count[$categoria] ?? 0) + 1;
    }

    // Estadísticas por tipo
    $tipos_count = [];
    foreach ($materiales as $material) {
        $datos_basicos = json_decode($material['datos_basicos'], true);
        $tipo = $datos_basicos['tipo'] ?? 'Sin tipo';
        $tipos_count[$tipo] = ($tipos_count[$tipo] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca - Material Bibliográfico</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        body { background-color: #ffffff; }
        
        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }
        
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #f0f0f0;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .stats-card {
            background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 100%);
            border: none;
            color: #2d5016;
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }

        .material-cover {
            width: 60px;
            height: 80px;
            border-radius: 4px;
            object-fit: cover;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #5e35b1;
        }

        .material-info {
            line-height: 1.4;
        }

        .material-titulo {
            font-weight: 600;
            color: #37474f;
            font-size: 0.95rem;
        }

        .material-autor {
            font-size: 0.85rem;
            color: #78909c;
        }

        .material-isbn {
            font-size: 0.75rem;
            color: #b0bec5;
            font-family: 'Courier New', monospace;
        }

        .badge-categoria {
            background: linear-gradient(135deg, #b3e5fc 0%, #e1bee7 100%);
            color: #4a148c;
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
        }

        .badge-tipo {
            background: linear-gradient(135deg, #ffccbc 0%, #ffe0b2 100%);
            color: #bf360c;
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
        }

        .badge-dewey {
            background: linear-gradient(135deg, #c5e1a5 0%, #dcedc8 100%);
            color: #33691e;
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-family: 'Courier New', monospace;
        }

        .ejemplares-stats {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .ejemplares-numero {
            font-size: 1.5rem;
            font-weight: 700;
            color: #37474f;
        }

        .ejemplares-label {
            font-size: 0.7rem;
            color: #78909c;
            text-transform: uppercase;
        }

        .disponible-badge {
            background: #c8e6c9;
            color: #2e7d32;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .prestado-badge {
            background: #ffccbc;
            color: #d84315;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .table-actions .btn {
            padding: 0.35rem 0.7rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .filter-section {
            background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.95);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .libro-icon {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            width: 60px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 1.8rem;
            color: #5e35b1;
        }

        .publicacion-info {
            background: #fafafa;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .ubicacion-badge {
            background: #fff9c4;
            color: #f57f17;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
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
                                    <span class="badge bg-primary fs-2 rounded-4 lh-sm">Sistema Biblioteca</span>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </header>

                <!-- Page Title -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="fw-bold mb-1">📚 Material Bibliográfico</h4>
                                <p class="mb-0 text-muted">Gestión del catálogo de la biblioteca</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="exportarCatalogo()">
                                    <i class="ti ti-download me-2"></i>Exportar
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMaterial">
                                    <i class="ti ti-plus me-2"></i>Nuevo Material
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-books"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $total_materiales ?></h3>
                                        <p class="mb-0">Total Materiales</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-book-2"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $total_ejemplares ?></h3>
                                        <p class="mb-0">Total Ejemplares</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $ejemplares_disponibles ?></h3>
                                        <p class="mb-0">Disponibles</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-user-check"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $ejemplares_prestados ?></h3>
                                        <p class="mb-0">En Préstamo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-section">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select class="form-select" id="filtroCategoria">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias_count as $categoria => $count): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>"><?= htmlspecialchars($categoria) ?> (<?= $count ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select class="form-select" id="filtroTipo">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tipos_count as $tipo => $count): ?>
                                    <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?> (<?= $count ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Buscar</label>
                            <input type="text" class="form-control" id="buscarMaterial" placeholder="Título, ISBN, autor...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Materiales -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Catálogo Bibliográfico</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaMateriales">
                                <thead class="table-light">
                                    <tr>
                                        <th>Material</th>
                                        <th>Clasificación</th>
                                        <th>Publicación</th>
                                        <th>Ubicación</th>
                                        <th>Ejemplares</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materiales as $material): 
                                        $datos_basicos = json_decode($material['datos_basicos'], true) ?: [];
                                        $datos_publicacion = json_decode($material['datos_publicacion'], true) ?: [];
                                        $clasificacion = json_decode($material['clasificacion'], true) ?: [];
                                        $datos_fisicos = json_decode($material['datos_fisicos'], true) ?: [];
                                        $autores = json_decode($material['autores'], true) ?: [];
                                        
                                        // Obtener autor principal
                                        $autor_principal = '';
                                        foreach ($autores as $autor) {
                                            if ($autor['principal'] ?? false) {
                                                $autor_principal = ($autor['nombre'] ?? '') . ' ' . ($autor['apellido'] ?? '');
                                                break;
                                            }
                                        }
                                        if (empty($autor_principal) && !empty($autores)) {
                                            $autor_principal = ($autores[0]['nombre'] ?? '') . ' ' . ($autores[0]['apellido'] ?? '');
                                        }
                                    ?>
                                        <tr data-categoria="<?= htmlspecialchars($clasificacion['categoria'] ?? '') ?>" 
                                            data-tipo="<?= htmlspecialchars($datos_basicos['tipo'] ?? '') ?>"
                                            data-estado="<?= $material['activo'] ?>">
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div class="libro-icon me-3">
                                                        <i class="ti ti-book"></i>
                                                    </div>
                                                    <div class="material-info flex-grow-1">
                                                        <div class="material-titulo"><?= htmlspecialchars($datos_basicos['titulo'] ?? 'Sin título') ?></div>
                                                        <?php if (!empty($datos_basicos['subtitulo'])): ?>
                                                            <small class="text-muted d-block"><?= htmlspecialchars($datos_basicos['subtitulo']) ?></small>
                                                        <?php endif; ?>
                                                        <div class="material-autor mt-1"><?= htmlspecialchars($autor_principal ?: 'Autor desconocido') ?></div>
                                                        <?php if (!empty($datos_basicos['isbn'])): ?>
                                                            <div class="material-isbn mt-1">ISBN: <?= htmlspecialchars($datos_basicos['isbn']) ?></div>
                                                        <?php endif; ?>
                                                        <div class="mt-2">
                                                            <span class="badge-tipo"><?= htmlspecialchars($datos_basicos['tipo'] ?? 'No especificado') ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge-categoria d-block mb-2">
                                                        <?= htmlspecialchars($clasificacion['categoria'] ?? 'Sin categoría') ?>
                                                    </span>
                                                    <?php if (!empty($clasificacion['codigo_dewey'])): ?>
                                                        <span class="badge-dewey">
                                                            Dewey: <?= htmlspecialchars($clasificacion['codigo_dewey']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="publicacion-info">
                                                    <div><strong><?= htmlspecialchars($datos_publicacion['editorial'] ?? 'No especificada') ?></strong></div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($datos_publicacion['anio_publicacion'] ?? 'S/F') ?> 
                                                        <?php if (!empty($datos_publicacion['edicion'])): ?>
                                                            | <?= htmlspecialchars($datos_publicacion['edicion']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                    <?php if (!empty($datos_publicacion['paginas'])): ?>
                                                        <br><small><?= $datos_publicacion['paginas'] ?> págs.</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($datos_fisicos['ubicacion'])): ?>
                                                    <span class="ubicacion-badge">
                                                        <i class="ti ti-map-pin"></i>
                                                        <?= htmlspecialchars($datos_fisicos['ubicacion']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <small class="text-muted">Sin ubicación</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="ejemplares-stats">
                                                    <div class="ejemplares-numero"><?= $material['total_ejemplares'] ?></div>
                                                    <div class="ejemplares-label">Total</div>
                                                    <div class="mt-2">
                                                        <span class="disponible-badge">
                                                            <?= $material['ejemplares_disponibles'] ?> Disp.
                                                        </span>
                                                        <?php if ($material['ejemplares_prestados'] > 0): ?>
                                                            <br><span class="prestado-badge mt-1">
                                                                <?= $material['ejemplares_prestados'] ?> Prest.
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $material['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $material['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verMaterial(<?= $material['id'] ?>)" 
                                                            title="Ver Detalles Completos">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarMaterial(<?= $material['id'] ?>)" 
                                                            title="Editar Material">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $material['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoMaterial(<?= $material['id'] ?>, <?= $material['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $material['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="ti <?= $material['activo'] ? 'ti-toggle-right' : 'ti-toggle-left' ?>"></i>
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

                <!-- Estadísticas por Categoría y Tipo -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Distribución por Categoría</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($categorias_count as $categoria => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($categoria) ?></span>
                                        <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Distribución por Tipo</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($tipos_count as $tipo => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($tipo) ?></span>
                                        <span class="badge bg-info rounded-pill"><?= $count ?></span>
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
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/biblioteca/modal_agregar_material.php'; ?>
    <?php include 'modales/biblioteca/modal_editar_material.php'; ?>
    <?php include 'modales/biblioteca/modal_ver_material.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaMateriales;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaMateriales = $('#tablaMateriales').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros personalizados
            $('#filtroCategoria, #filtroTipo, #filtroEstado').on('change', aplicarFiltros);
            $('#buscarMaterial').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const categoriaFiltro = $('#filtroCategoria').val().toLowerCase();
            const tipoFiltro = $('#filtroTipo').val().toLowerCase();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarMaterial').val().toLowerCase();

            $('#tablaMateriales tbody tr').each(function() {
                const fila = $(this);
                const categoria = fila.data('categoria').toString().toLowerCase();
                const tipo = fila.data('tipo').toString().toLowerCase();
                const estado = fila.data('estado');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (categoriaFiltro && !categoria.includes(categoriaFiltro)) {
                    mostrar = false;
                }

                if (tipoFiltro && !tipo.includes(tipoFiltro)) {
                    mostrar = false;
                }

                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroCategoria, #filtroTipo, #filtroEstado').val('');
            $('#buscarMaterial').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function verMaterial(id) {
            mostrarCarga();
            
            fetch('modales/biblioteca/procesar_materiales.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosVisualizacion(data.material);
                    $('#modalVerMaterial').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar material');
            });
        }

        function editarMaterial(id) {
            mostrarCarga();
            
            fetch('modales/biblioteca/procesar_materiales.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.material);
                    $('#modalEditarMaterial').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar material');
            });
        }

        function toggleEstadoMaterial(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este material?`,
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

            fetch('modales/biblioteca/procesar_materiales.php', {
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
                mostrarError('Error al cambiar estado');
            });
        }

        function exportarCatalogo() {
            window.open('reportes/exportar_catalogo.php', '_blank');
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