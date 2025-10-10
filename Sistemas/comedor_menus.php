<?php 
    require_once 'conexion/bd.php';

    // Obtener todos los menús con ordenamiento
    try {
        $sql = "SELECT * FROM menus_comedor ORDER BY fecha DESC";
        $stmt_menus = $conexion->prepare($sql);
        $stmt_menus->execute();
        $menus = $stmt_menus->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $menus = [];
        $error_menus = "Error al cargar menús: " . $e->getMessage();
    }

    // Calcular estadísticas
    $total_menus = count($menus);
    $menus_hoy = 0;
    $menus_semana = 0;
    $porciones_totales = 0;
    
    $hoy = new DateTime();
    $inicio_semana = (clone $hoy)->modify('monday this week');
    $fin_semana = (clone $hoy)->modify('sunday this week');
    
    foreach ($menus as $menu) {
        $fecha_menu = new DateTime($menu['fecha']);
        
        // Menús de hoy
        if ($fecha_menu->format('Y-m-d') === $hoy->format('Y-m-d')) {
            $menus_hoy++;
        }
        
        // Menús de esta semana
        if ($fecha_menu >= $inicio_semana && $fecha_menu <= $fin_semana) {
            $menus_semana++;
        }
        
        // Porciones disponibles
        $disponibilidad = json_decode($menu['disponibilidad'], true);
        if (isset($disponibilidad['porciones_disponibles'])) {
            $porciones_totales += $disponibilidad['porciones_disponibles'];
        }
    }

    // Promedio de porciones
    $promedio_porciones = $total_menus > 0 ? round($porciones_totales / $total_menus) : 0;

    // Menús por tipo
    $menus_por_tipo = [];
    foreach ($menus as $menu) {
        $config = json_decode($menu['configuracion'], true);
        $tipo = $config['tipo'] ?? 'ALMUERZO';
        $menus_por_tipo[$tipo] = ($menus_por_tipo[$tipo] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Menús - Comedor/Cafetería | ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .menu-card {
            border-left: 4px solid;
            background: #ffffff;
        }

        .menu-card.desayuno { border-left-color: #FFE5B4; }
        .menu-card.almuerzo { border-left-color: #B4E5FF; }
        .menu-card.cena { border-left-color: #FFB4E5; }
        .menu-card.snack { border-left-color: #E5FFB4; }

        .menu-header {
            background: linear-gradient(135deg, #FFE5B4 0%, #FFDAB9 100%);
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .menu-fecha {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .menu-tipo-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-desayuno { background: #FFE5B4; color: #8B4513; }
        .badge-almuerzo { background: #B4E5FF; color: #00008B; }
        .badge-cena { background: #FFB4E5; color: #8B008B; }
        .badge-snack { background: #E5FFB4; color: #006400; }

        .plato-item {
            padding: 0.6rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plato-item:last-child {
            border-bottom: none;
        }

        .plato-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .icon-entrada { background: #E8F5E9; }
        .icon-principal { background: #FFF3E0; }
        .icon-postre { background: #FCE4EC; }
        .icon-bebida { background: #E3F2FD; }

        .stats-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .color-stats-1 { color: #FFB3BA; }
        .color-stats-2 { color: #BAFFC9; }
        .color-stats-3 { color: #BAE1FF; }
        .color-stats-4 { color: #FFFFBA; }

        .alergeno-badge {
            background: #FFEBEE;
            color: #C62828;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 0.1rem;
            display: inline-block;
        }

        .precio-badge {
            background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
            color: #2E7D32;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .disponibilidad-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .disponibilidad-fill {
            height: 100%;
            background: linear-gradient(90deg, #81C784, #66BB6A);
            transition: width 0.3s ease;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
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
                                <h4 class="fw-bold mb-0">
                                    <i class="ti ti-tools-kitchen-2 me-2"></i>
                                    Gestión de Menús del Comedor
                                </h4>
                                <p class="mb-0 text-muted">Administra los menús diarios, información nutricional y disponibilidad</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMenu">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Menú
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-1">
                                <?= $total_menus ?>
                            </div>
                            <div class="stats-label">Total de Menús</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $menus_hoy ?>
                            </div>
                            <div class="stats-label">Menús de Hoy</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $menus_semana ?>
                            </div>
                            <div class="stats-label">Menús Esta Semana</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $promedio_porciones ?>
                            </div>
                            <div class="stats-label">Promedio Porciones</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Menú</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="DESAYUNO">Desayuno</option>
                                    <option value="ALMUERZO">Almuerzo</option>
                                    <option value="CENA">Cena</option>
                                    <option value="SNACK">Snack</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="filtroFechaHasta">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarMenu" placeholder="Buscar plato...">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Menús -->
                <div class="row" id="menusContainer">
                    <?php if (empty($menus)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ti ti-tools-kitchen-2"></i>
                                <h5 class="mt-3">No hay menús registrados</h5>
                                <p>Comienza agregando el primer menú del comedor</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalAgregarMenu">
                                    <i class="ti ti-plus me-2"></i>
                                    Crear Primer Menú
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($menus as $menu): 
                            $configuracion = json_decode($menu['configuracion'], true) ?: [];
                            $detalles = json_decode($menu['detalles'], true) ?: [];
                            $disponibilidad = json_decode($menu['disponibilidad'], true) ?: [];
                            
                            $tipo = $configuracion['tipo'] ?? 'ALMUERZO';
                            $precio = $configuracion['precio'] ?? 0;
                            $categoria = $configuracion['categoria'] ?? 'ESTANDAR';
                            
                            $platos = $detalles['platos'] ?? [];
                            $alergenos = $detalles['alergenos'] ?? [];
                            
                            $porciones_disponibles = $disponibilidad['porciones_disponibles'] ?? 0;
                            $porciones_totales = $disponibilidad['porciones_totales'] ?? 100;
                            $porcentaje_disponible = $porciones_totales > 0 ? ($porciones_disponibles / $porciones_totales) * 100 : 0;
                            
                            $fecha_obj = new DateTime($menu['fecha']);
                            $es_hoy = $fecha_obj->format('Y-m-d') === $hoy->format('Y-m-d');
                            
                            $tipo_lower = strtolower($tipo);
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4 menu-card-wrapper" 
                                 data-tipo="<?= $tipo ?>" 
                                 data-fecha="<?= $menu['fecha'] ?>"
                                 data-platos="<?= htmlspecialchars(json_encode($platos)) ?>">
                                <div class="card menu-card <?= $tipo_lower ?> h-100">
                                    <div class="menu-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="menu-fecha">
                                                    <?= $fecha_obj->format('d/m/Y') ?>
                                                    <?php if ($es_hoy): ?>
                                                        <span class="badge bg-success ms-2">HOY</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-1">
                                                    <span class="menu-tipo-badge badge-<?= $tipo_lower ?>">
                                                        <?= $tipo ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="precio-badge">
                                                S/ <?= number_format($precio, 2) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Platos -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 fw-bold">
                                                <i class="ti ti-chef-hat me-1"></i>
                                                Menú del Día
                                            </h6>
                                            <?php if (!empty($platos)): ?>
                                                <?php 
                                                $iconos_platos = [
                                                    'entrada' => ['icon' => '🥗', 'class' => 'icon-entrada'],
                                                    'plato_principal' => ['icon' => '🍛', 'class' => 'icon-principal'],
                                                    'postre' => ['icon' => '🍰', 'class' => 'icon-postre'],
                                                    'bebida' => ['icon' => '🥤', 'class' => 'icon-bebida']
                                                ];
                                                
                                                foreach ($platos as $tipo_plato => $nombre_plato):
                                                    if (empty($nombre_plato)) continue;
                                                    $info_icono = $iconos_platos[$tipo_plato] ?? ['icon' => '🍴', 'class' => 'icon-entrada'];
                                                ?>
                                                    <div class="plato-item">
                                                        <div class="plato-icon <?= $info_icono['class'] ?>">
                                                            <?= $info_icono['icon'] ?>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                                <?= ucfirst(str_replace('_', ' ', $tipo_plato)) ?>
                                                            </small>
                                                            <div><?= htmlspecialchars($nombre_plato) ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-muted small mb-0">Sin platos registrados</p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Alérgenos -->
                                        <?php if (!empty($alergenos)): ?>
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="ti ti-alert-triangle me-1"></i>
                                                    Alérgenos:
                                                </small>
                                                <?php foreach ($alergenos as $alergeno): ?>
                                                    <span class="alergeno-badge"><?= htmlspecialchars($alergeno) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Disponibilidad -->
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">
                                                    <i class="ti ti-users me-1"></i>
                                                    Disponibilidad
                                                </small>
                                                <small class="fw-bold">
                                                    <?= $porciones_disponibles ?>/<?= $porciones_totales ?>
                                                </small>
                                            </div>
                                            <div class="disponibilidad-bar">
                                                <div class="disponibilidad-fill" style="width: <?= $porcentaje_disponible ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-light">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetallesMenu(<?= $menu['id'] ?>)" 
                                                    title="Ver Detalles">
                                                <i class="ti ti-eye"></i> Detalles
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="editarMenu(<?= $menu['id'] ?>)" 
                                                    title="Editar Menú">
                                                <i class="ti ti-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarMenu(<?= $menu['id'] ?>)" 
                                                    title="Eliminar Menú">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Distribución por Tipo -->
                <?php if (!empty($menus_por_tipo)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-chart-pie me-2"></i>
                                    Distribución de Menús por Tipo
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($menus_por_tipo as $tipo => $cantidad): 
                                        $tipo_lower = strtolower($tipo);
                                        $colores = [
                                            'desayuno' => '#FFE5B4',
                                            'almuerzo' => '#B4E5FF',
                                            'cena' => '#FFB4E5',
                                            'snack' => '#E5FFB4'
                                        ];
                                        $color = $colores[$tipo_lower] ?? '#E0E0E0';
                                    ?>
                                        <div class="col-md-3">
                                            <div class="text-center p-3" style="background: <?= $color ?>; border-radius: 12px;">
                                                <div class="fs-2 fw-bold"><?= $cantidad ?></div>
                                                <div class="text-muted"><?= $tipo ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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
    <?php include 'modales/menus/modal_agregar.php'; ?>
    <?php include 'modales/menus/modal_editar.php'; ?>
    <?php include 'modales/menus/modal_detalles.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Aplicar filtros
            $('#filtroTipo, #filtroFechaDesde, #filtroFechaHasta, #buscarMenu').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const tipoFiltro = $('#filtroTipo').val();
            const fechaDesde = $('#filtroFechaDesde').val();
            const fechaHasta = $('#filtroFechaHasta').val();
            const busqueda = $('#buscarMenu').val().toLowerCase();

            $('.menu-card-wrapper').each(function() {
                const card = $(this);
                const tipo = card.data('tipo');
                const fecha = card.data('fecha');
                const platos = JSON.stringify(card.data('platos')).toLowerCase();

                let mostrar = true;

                if (tipoFiltro && tipo !== tipoFiltro) {
                    mostrar = false;
                }

                if (fechaDesde && fecha < fechaDesde) {
                    mostrar = false;
                }

                if (fechaHasta && fecha > fechaHasta) {
                    mostrar = false;
                }

                if (busqueda && !platos.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroTipo, #filtroFechaDesde, #filtroFechaHasta, #buscarMenu').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarMenu(id) {
            mostrarCarga();
            
            fetch('modales/menus/procesar_menus.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionMenu(data.menu);
                    $('#modalEditarMenu').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del menú');
            });
        }

        function verDetallesMenu(id) {
            mostrarCarga();
            
            fetch('modales/menus/procesar_menus.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalles_completos&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetallesCompletos(data.menu);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalles del menú');
            });
        }

        function eliminarMenu(id) {
            Swal.fire({
                title: '¿Eliminar este menú?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCarga();
                    
                    fetch('modales/menus/procesar_menus.php', {
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
                        mostrarError('Error al eliminar el menú');
                    });
                }
            });
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