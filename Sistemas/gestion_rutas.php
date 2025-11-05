<?php 
require_once 'conexion/bd.php';

// Obtener período académico activo
try {
    $sql_periodo = "SELECT id, nombre FROM periodos_academicos WHERE activo = 1 LIMIT 1";
    $stmt_periodo = $conexion->prepare($sql_periodo);
    $stmt_periodo->execute();
    $periodo_activo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodo_activo = null;
}

// Obtener todas las rutas de transporte
try {
    $sql = "SELECT 
                rt.id,
                rt.codigo_ruta,
                rt.nombre,
                rt.configuracion,
                rt.paraderos,
                rt.tarifa,
                rt.activo,
                ax.vehiculo_id,
                vt.placa,
                vt.modelo,
                vt.capacidad
            FROM rutas_transporte rt
            LEFT JOIN asignaciones_transporte ax ON rt.id = ax.ruta_id AND ax.periodo_academico_id = :periodo_id AND ax.activo = 1
            LEFT JOIN vehiculos_transporte vt ON ax.vehiculo_id = vt.id
            ORDER BY rt.codigo_ruta ASC";
    $stmt_rutas = $conexion->prepare($sql);
    $stmt_rutas->bindParam(':periodo_id', $periodo_activo['id']);
    $stmt_rutas->execute();
    $rutas = $stmt_rutas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rutas = [];
    $error_rutas = "Error al cargar rutas: " . $e->getMessage();
}

// Obtener vehículos disponibles
try {
    $sql_vehiculos = "SELECT id, placa, modelo, capacidad FROM vehiculos_transporte WHERE activo = 1 ORDER BY placa ASC";
    $stmt_vehiculos = $conexion->prepare($sql_vehiculos);
    $stmt_vehiculos->execute();
    $vehiculos = $stmt_vehiculos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $vehiculos = [];
}

// Obtener estudiantes para asignación
try {
    $sql_estudiantes = "SELECT 
                            e.id,
                            CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                            e.codigo_estudiante,
                            s.grado,
                            s.seccion
                        FROM estudiantes e
                        LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
                        LEFT JOIN secciones s ON m.seccion_id = s.id
                        WHERE e.activo = 1
                        ORDER BY e.nombres ASC";
    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->execute();
    $estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes = [];
}

// Calcular estadísticas
$total_rutas = count($rutas);
$rutas_activas = 0;
$total_estudiantes_transporte = 0;
$vehiculos_asignados = 0;

foreach ($rutas as $ruta) {
    if ($ruta['activo'] == 1) {
        $rutas_activas++;
    }
    
    if (!empty($ruta['vehiculo_id'])) {
        $vehiculos_asignados++;
    }
    
    $paraderos = json_decode($ruta['paraderos'], true);
    if (is_array($paraderos)) {
        foreach ($paraderos as $paradero) {
            if (isset($paradero['estudiantes']) && is_array($paradero['estudiantes'])) {
                $total_estudiantes_transporte += count($paradero['estudiantes']);
            }
        }
    }
}

$promedio_estudiantes = $rutas_activas > 0 ? round($total_estudiantes_transporte / $rutas_activas) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Transporte Escolar | ANDRÉS AVELINO CÁCERES</title>
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

        .ruta-card {
            border-left: 4px solid;
            background: #ffffff;
        }

        .ruta-card.activo { border-left-color: #B4E5D4; }
        .ruta-card.inactivo { border-left-color: #FFD4B4; }

        .ruta-header {
            background: linear-gradient(135deg, #D4E5FF 0%, #E5F0FF 100%);
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .ruta-nombre {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .estado-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-activo { background: #B4E5D4; color: #006400; }
        .badge-inactivo { background: #FFD4B4; color: #8B4513; }

        .paradero-item {
            padding: 0.6rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .paradero-item:last-child {
            border-bottom: none;
        }

        .paradero-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: #E3F2FD;
        }

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
        .color-stats-4 { color: #E5D4FF; }

        .vehiculo-info {
            background: #F5F5F5;
            padding: 0.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .horario-badge {
            background: linear-gradient(135deg, #E5F0FF, #D4E5FF);
            color: #00008B;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 0.1rem;
            display: inline-block;
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

        .capacidad-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .capacidad-fill {
            height: 100%;
            background: linear-gradient(90deg, #81C784, #66BB6A);
            transition: width 0.3s ease;
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
                                    <i class="ti ti-bus me-2"></i>
                                    Gestión de Transporte Escolar
                                </h4>
                                <p class="mb-0 text-muted">Administra rutas, vehículos y asignaciones de estudiantes</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRuta">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Ruta
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
                                <?= $total_rutas ?>
                            </div>
                            <div class="stats-label">Total de Rutas</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $rutas_activas ?>
                            </div>
                            <div class="stats-label">Rutas Activas</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $total_estudiantes_transporte ?>
                            </div>
                            <div class="stats-label">Estudiantes Asignados</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $vehiculos_asignados ?>
                            </div>
                            <div class="stats-label">Vehículos en Uso</div>
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
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarRuta" placeholder="Buscar ruta...">
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

                <!-- Lista de Rutas -->
                <div class="row" id="rutasContainer">
                    <?php if (empty($rutas)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ti ti-bus"></i>
                                <h5 class="mt-3">No hay rutas registradas</h5>
                                <p>Comienza agregando la primera ruta de transporte</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCrearRuta">
                                    <i class="ti ti-plus me-2"></i>
                                    Crear Primera Ruta
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rutas as $ruta): 
                            $configuracion = json_decode($ruta['configuracion'], true) ?: [];
                            $paraderos = json_decode($ruta['paraderos'], true) ?: [];
                            $estado_class = $ruta['activo'] == 1 ? 'activo' : 'inactivo';
                            $estado_text = $ruta['activo'] == 1 ? 'ACTIVO' : 'INACTIVO';
                            
                            $horario_salida = $configuracion['horario_salida'] ?? '';
                            $horario_retorno = $configuracion['horario_retorno'] ?? '';
                            
                            // Contar estudiantes en la ruta
                            $total_estudiantes_ruta = 0;
                            foreach ($paraderos as $paradero) {
                                if (isset($paradero['estudiantes']) && is_array($paradero['estudiantes'])) {
                                    $total_estudiantes_ruta += count($paradero['estudiantes']);
                                }
                            }
                            
                            // Calcular porcentaje de capacidad
                            $capacidad = $ruta['capacidad'] ?? 0;
                            $porcentaje_capacidad = $capacidad > 0 ? ($total_estudiantes_ruta / $capacidad) * 100 : 0;
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4 ruta-card-wrapper" 
                                 data-estado="<?= $ruta['activo'] ?>" 
                                 data-nombre="<?= strtolower($ruta['nombre']) ?>">
                                <div class="card ruta-card <?= $estado_class ?> h-100">
                                    <div class="ruta-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="ruta-nombre">
                                                    <?= htmlspecialchars($ruta['nombre']) ?>
                                                </div>
                                                <small class="text-muted"><?= htmlspecialchars($ruta['codigo_ruta']) ?></small>
                                                <div class="mt-1">
                                                    <span class="estado-badge badge-<?= $estado_class ?>">
                                                        <?= $estado_text ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Horarios -->
                                        <?php if (!empty($horario_salida) || !empty($horario_retorno)): ?>
                                            <div class="mb-3">
                                                <h6 class="mb-2 fw-bold">
                                                    <i class="ti ti-clock me-1"></i>
                                                    Horarios
                                                </h6>
                                                <div>
                                                    <?php if (!empty($horario_salida)): ?>
                                                        <span class="horario-badge">
                                                            <i class="ti ti-sun me-1"></i>
                                                            Salida: <?= htmlspecialchars($horario_salida) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($horario_retorno)): ?>
                                                        <span class="horario-badge">
                                                            <i class="ti ti-moon me-1"></i>
                                                            Retorno: <?= htmlspecialchars($horario_retorno) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Tarifa -->
                                        <?php if (!empty($ruta['tarifa'])): ?>
                                            <div class="mb-3">
                                                <h6 class="mb-2 fw-bold">
                                                    <i class="ti ti-cash me-1"></i>
                                                    Tarifa
                                                </h6>
                                                <div class="alert alert-success py-2 px-3 mb-0">
                                                    <strong>S/ <?= number_format($ruta['tarifa'], 2) ?></strong>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Vehículo -->
                                        <?php if (!empty($ruta['placa'])): ?>
                                            <div class="vehiculo-info mb-3">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="ti ti-car"></i>
                                                    <strong><?= htmlspecialchars($ruta['placa']) ?></strong>
                                                    <small class="text-muted"><?= htmlspecialchars($ruta['modelo']) ?></small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning py-2 px-3 mb-3">
                                                <small><i class="ti ti-alert-circle me-1"></i>Sin vehículo asignado</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Paraderos -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 fw-bold">
                                                <i class="ti ti-map-pin me-1"></i>
                                                Paraderos (<?= count($paraderos) ?>)
                                            </h6>
                                            <?php if (!empty($paraderos)): ?>
                                                <?php $count = 0; ?>
                                                <?php foreach ($paraderos as $paradero): 
                                                    if ($count >= 3) break;
                                                    $count++;
                                                ?>
                                                    <div class="paradero-item">
                                                        <div class="paradero-icon">📍</div>
                                                        <div class="flex-grow-1">
                                                            <small><?= htmlspecialchars($paradero['nombre'] ?? 'Sin nombre') ?></small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($paraderos) > 3): ?>
                                                    <div class="text-center mt-2">
                                                        <small class="text-muted">Y <?= count($paraderos) - 3 ?> más...</small>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <p class="text-muted small mb-0">Sin paraderos registrados</p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Capacidad -->
                                        <?php if ($capacidad > 0): ?>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">
                                                        <i class="ti ti-users me-1"></i>
                                                        Capacidad
                                                    </small>
                                                    <small class="fw-bold">
                                                        <?= $total_estudiantes_ruta ?>/<?= $capacidad ?>
                                                    </small>
                                                </div>
                                                <div class="capacidad-bar">
                                                    <div class="capacidad-fill" style="width: <?= min($porcentaje_capacidad, 100) ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-footer bg-light">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="asignarVehiculo(<?= $ruta['id'] ?>)" 
                                                    title="Asignar Vehículo">
                                                <i class="ti ti-car"></i> Vehículo
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="asignarEstudiantes(<?= $ruta['id'] ?>)" 
                                                    title="Asignar Estudiantes">
                                                <i class="ti ti-users"></i> Estudiantes
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarRuta(<?= $ruta['id'] ?>)" 
                                                    title="Eliminar Ruta">
                                                <i class="ti ti-trash"></i>
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
    <?php include 'modales/transporte/modal_crear_ruta.php'; ?>
    <?php include 'modales/transporte/modal_asignar_vehiculo.php'; ?>
    <?php include 'modales/transporte/modal_asignar_estudiantes.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroEstado, #buscarRuta').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarRuta').val().toLowerCase();

            $('.ruta-card-wrapper').each(function() {
                const card = $(this);
                const estado = card.data('estado');
                const nombre = card.data('nombre');

                let mostrar = true;

                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #buscarRuta').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function asignarVehiculo(rutaId) {
            mostrarCarga();
            
            fetch('modales/transporte/procesar_transporte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_ruta&id=${rutaId}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosAsignacionVehiculo(data.ruta);
                    $('#modalAsignarVehiculo').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la ruta');
            });
        }

        function asignarEstudiantes(rutaId) {
            mostrarCarga();
            
            fetch('modales/transporte/procesar_transporte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_ruta&id=${rutaId}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosAsignacionEstudiantes(data.ruta);
                    $('#modalAsignarEstudiantes').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la ruta');
            });
        }

        function eliminarRuta(id) {
            Swal.fire({
                title: '¿Eliminar esta ruta?',
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
                    
                    fetch('modales/transporte/procesar_transporte.php', {
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
                        mostrarError('Error al eliminar la ruta');
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