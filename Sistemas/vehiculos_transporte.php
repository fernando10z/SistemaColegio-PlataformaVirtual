<?php 
require_once 'conexion/bd.php';

// Obtener todos los vehículos activos
try {
    $sql = "SELECT 
                id,
                placa,
                modelo,
                capacidad,
                datos_vehiculo,
                documentacion,
                personal,
                estado,
                observaciones,
                activo,
                fecha_creacion
            FROM vehiculos_transporte 
            WHERE activo = 1
            ORDER BY placa ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $vehiculos = [];
    $error_vehiculos = "Error al cargar vehículos: " . $e->getMessage();
}

// Calcular estadísticas
$total_vehiculos = count($vehiculos);
$vehiculos_activos = 0;
$vehiculos_mantenimiento = 0;
$vehiculos_inactivos = 0;

foreach ($vehiculos as $vehiculo) {
    switch ($vehiculo['estado']) {
        case 'ACTIVO':
            $vehiculos_activos++;
            break;
        case 'MANTENIMIENTO':
            $vehiculos_mantenimiento++;
            break;
        case 'INACTIVO':
            $vehiculos_inactivos++;
            break;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Vehículos de Transporte | ANDRÉS AVELINO CÁCERES</title>
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

        .vehiculo-card {
            border-left: 4px solid;
            background: #ffffff;
        }

        .vehiculo-card.activo { border-left-color: #B4E5D4; }
        .vehiculo-card.mantenimiento { border-left-color: #FFD4B4; }
        .vehiculo-card.inactivo { border-left-color: #FFB4B4; }

        .vehiculo-header {
            background: linear-gradient(135deg, #D4E5FF 0%, #E5F0FF 100%);
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .vehiculo-placa {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .estado-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-activo { background: #B4E5D4; color: #006400; }
        .badge-mantenimiento { background: #FFD4B4; color: #8B4513; }
        .badge-inactivo { background: #FFB4B4; color: #8B0000; }

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

        .color-stats-1 { color: #B4D4FF; }
        .color-stats-2 { color: #B4E5D4; }
        .color-stats-3 { color: #FFD4B4; }
        .color-stats-4 { color: #FFB4B4; }

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

        .info-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
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
                                    <i class="ti ti-car me-2"></i>
                                    Gestión de Vehículos de Transporte
                                </h4>
                                <p class="mb-0 text-muted">Administra los vehículos disponibles para transporte escolar</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarVehiculo">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Vehículo
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
                                <?= $total_vehiculos ?>
                            </div>
                            <div class="stats-label">Total de Vehículos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $vehiculos_activos ?>
                            </div>
                            <div class="stats-label">Activos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $vehiculos_mantenimiento ?>
                            </div>
                            <div class="stats-label">En Mantenimiento</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $vehiculos_inactivos ?>
                            </div>
                            <div class="stats-label">Inactivos</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="ACTIVO">Activo</option>
                                    <option value="MANTENIMIENTO">Mantenimiento</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarVehiculo" placeholder="Buscar por placa o modelo...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Vehículos -->
                <div class="row" id="vehiculosContainer">
                    <?php if (empty($vehiculos)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ti ti-car"></i>
                                <h5 class="mt-3">No hay vehículos registrados</h5>
                                <p>Comienza agregando el primer vehículo de transporte</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalAgregarVehiculo">
                                    <i class="ti ti-plus me-2"></i>
                                    Agregar Primer Vehículo
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($vehiculos as $vehiculo): 
                            $datos_vehiculo = json_decode($vehiculo['datos_vehiculo'], true) ?: [];
                            $documentacion = json_decode($vehiculo['documentacion'], true) ?: [];
                            $personal = json_decode($vehiculo['personal'], true) ?: [];
                            
                            $estado_class = strtolower($vehiculo['estado']);
                            $estado_text = $vehiculo['estado'];
                            
                            // Información del vehículo
                            $marca = $datos_vehiculo['marca'] ?? '';
                            $anio = $datos_vehiculo['anio'] ?? '';
                            $color = $datos_vehiculo['color'] ?? '';
                            $num_motor = $datos_vehiculo['num_motor'] ?? '';
                            $num_chasis = $datos_vehiculo['num_chasis'] ?? '';
                            
                            // Información del personal
                            $conductor = $personal['conductor_nombre'] ?? '';
                            $copiloto = $personal['copiloto_nombre'] ?? '';
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4 vehiculo-card-wrapper" 
                                 data-estado="<?= $vehiculo['estado'] ?>" 
                                 data-busqueda="<?= strtolower($vehiculo['placa'] . ' ' . $vehiculo['modelo']) ?>">
                                <div class="card vehiculo-card <?= $estado_class ?> h-100">
                                    <div class="vehiculo-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="vehiculo-placa">
                                                    <?= htmlspecialchars($vehiculo['placa']) ?>
                                                </div>
                                                <small class="text-muted"><?= htmlspecialchars($vehiculo['modelo']) ?></small>
                                                <div class="mt-1">
                                                    <span class="estado-badge badge-<?= $estado_class ?>">
                                                        <?= $estado_text ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Información General -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 fw-bold">
                                                <i class="ti ti-info-circle me-1"></i>
                                                Información General
                                            </h6>
                                            <div class="info-item">
                                                <small class="text-muted">Capacidad:</small>
                                                <strong class="float-end"><?= htmlspecialchars($vehiculo['capacidad']) ?> pasajeros</strong>
                                            </div>
                                            <?php if (!empty($marca)): ?>
                                                <div class="info-item">
                                                    <small class="text-muted">Marca:</small>
                                                    <strong class="float-end"><?= htmlspecialchars($marca) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($anio)): ?>
                                                <div class="info-item">
                                                    <small class="text-muted">Año:</small>
                                                    <strong class="float-end"><?= htmlspecialchars($anio) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($color)): ?>
                                                <div class="info-item">
                                                    <small class="text-muted">Color:</small>
                                                    <strong class="float-end"><?= htmlspecialchars($color) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Personal Asignado -->
                                        <?php if (!empty($conductor) || !empty($copiloto)): ?>
                                            <div class="mb-3">
                                                <h6 class="mb-2 fw-bold">
                                                    <i class="ti ti-users me-1"></i>
                                                    Personal Asignado
                                                </h6>
                                                <?php if (!empty($conductor)): ?>
                                                    <div class="info-item">
                                                        <small class="text-muted">Conductor:</small>
                                                        <small class="float-end"><?= htmlspecialchars($conductor) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($copiloto)): ?>
                                                    <div class="info-item">
                                                        <small class="text-muted">Copiloto:</small>
                                                        <small class="float-end"><?= htmlspecialchars($copiloto) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Observaciones -->
                                        <?php if (!empty($vehiculo['observaciones'])): ?>
                                            <div class="mb-2">
                                                <h6 class="mb-2 fw-bold">
                                                    <i class="ti ti-notes me-1"></i>
                                                    Observaciones
                                                </h6>
                                                <small class="text-muted"><?= htmlspecialchars($vehiculo['observaciones']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-footer bg-light">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarVehiculo(<?= $vehiculo['id'] ?>)" 
                                                    title="Editar Vehículo">
                                                <i class="ti ti-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarVehiculo(<?= $vehiculo['id'] ?>)" 
                                                    title="Eliminar Vehículo">
                                                <i class="ti ti-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    <?php include 'modales/vehiculos/modal_agregar.php'; ?>
    <?php include 'modales/vehiculos/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroEstado, #buscarVehiculo').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarVehiculo').val().toLowerCase();

            $('.vehiculo-card-wrapper').each(function() {
                const card = $(this);
                const estado = card.data('estado');
                const textoB = card.data('busqueda');


                let mostrar = true;

                if (estadoFiltro !== '' && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !textoB.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #buscarVehiculo').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarVehiculo(id) {
            mostrarCarga();
            
            fetch('modales/vehiculos/procesar_vehiculos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.vehiculo);
                    $('#modalEditarVehiculo').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del vehículo');
            });
        }

        function eliminarVehiculo(id) {
            Swal.fire({
                title: '¿Eliminar este vehículo?',
                text: 'El vehículo quedará inactivo en el sistema',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCarga();
                    
                    fetch('modales/vehiculos/procesar_vehiculos.php', {
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
                        mostrarError('Error al eliminar el vehículo');
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