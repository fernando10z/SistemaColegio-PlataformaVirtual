<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
require_once 'conexion/bd.php';

try {
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto, direccion, refran FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    if ($colegio) {
        $colegio_nombre = isset($colegio['nombre']) ? $colegio['nombre'] : '';
        $colegio_ruc    = isset($colegio['ruc']) ? $colegio['ruc'] : '';
        $colegio_foto   = isset($colegio['foto']) ? $colegio['foto'] : '';
        $colegio_direccion = isset($colegio['direccion']) ? $colegio['direccion'] : '';
        $refran = isset($colegio['refran']) ? $colegio['refran'] : '';
    }
} catch (PDOException $e) {
    error_log("Error fetching colegio_principal: " . $e->getMessage());
}

// Variables solicitadas (nombre, ruc, foto)
$nombre = $colegio_nombre;
$ruc    = $colegio_ruc;
$foto   = $colegio_foto;
$direccion = $colegio_direccion;
$refran = $refran;

try {
    $sql_periodo = "SELECT id, nombre FROM periodos_academicos WHERE activo = 1 LIMIT 1";
    $stmt_periodo = $conexion->prepare($sql_periodo);
    $stmt_periodo->execute();
    $periodo_activo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodo_activo = null;
}

try {
    $sql = "SELECT
                ax.id,
                ax.vehiculo_id,
                ax.ruta_id,
                ax.periodo_academico_id,
                ax.configuracion,
                ax.estudiantes,
                ax.activo,
                ax.fecha_creacion,
                rt.codigo_ruta,
                rt.nombre AS ruta_nombre,
                rt.paraderos,
                rt.tarifa,
                vt.placa,
                vt.datos_vehiculo,
                vt.estado AS vehiculo_estado,
                pa.nombre AS periodo_nombre
            FROM asignaciones_transporte ax
            INNER JOIN rutas_transporte rt ON ax.ruta_id = rt.id
            INNER JOIN vehiculos_transporte vt ON ax.vehiculo_id = vt.id
            INNER JOIN periodos_academicos pa ON ax.periodo_academico_id = pa.id
            ORDER BY ax.fecha_creacion DESC";
    $stmt_asignaciones = $conexion->prepare($sql);
    $stmt_asignaciones->execute();
    $asignaciones = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $asignaciones = [];
    $error_asignaciones = "Error al cargar asignaciones: " . $e->getMessage();
}

$total_asignaciones = count($asignaciones);
$asignaciones_activas = 0;
$total_estudiantes = 0;
$vehiculos_en_uso = [];

foreach ($asignaciones as $asignacion) {
    if ($asignacion['activo'] == 1) {
        $asignaciones_activas++;
    }
    
    if (!in_array($asignacion['vehiculo_id'], $vehiculos_en_uso)) {
        $vehiculos_en_uso[] = $asignacion['vehiculo_id'];
    }
    
    $paraderos = json_decode($asignacion['paraderos'], true);
    if (is_array($paraderos)) {
        foreach ($paraderos as $paradero) {
            if (isset($paradero['estudiantes']) && is_array($paradero['estudiantes'])) {
                $total_estudiantes += count($paradero['estudiantes']);
            }
        }
    }
}

$total_vehiculos_uso = count($vehiculos_en_uso);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignaciones de Transporte - <?php echo $nombre?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
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

        .asignacion-card {
            border-left: 4px solid;
            background: #ffffff;
        }

        .asignacion-card.activo { border-left-color: #B4E5D4; }
        .asignacion-card.inactivo { border-left-color: #FFD4B4; }

        .asignacion-header {
            background: linear-gradient(135deg, #E5D4FF 0%, #F0E5FF 100%);
            padding: 1rem;
            border-radius: 8px 8px 0 0;
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

        .color-stats-1 { color: #E5D4FF; }
        .color-stats-2 { color: #B4E5D4; }
        .color-stats-3 { color: #FFD4B4; }
        .color-stats-4 { color: #D4E5FF; }

        .estado-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-activo { background: #B4E5D4; color: #006400; }
        .badge-inactivo { background: #FFD4B4; color: #8B4513; }

        .info-box {
            background: #F8F9FA;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 0.95rem;
            color: #2c3e50;
            font-weight: 500;
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

        .estudiantes-count {
            background: linear-gradient(135deg, #E5F0FF, #D4E5FF);
            color: #00008B;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
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
                                    <i class="ti ti-clipboard-check me-2"></i>
                                    Asignaciones de Transporte
                                </h4>
                                <p class="mb-0 text-muted">Gestiona las asignaciones de vehículos a rutas por período académico</p>
                            </div>
                            <div>
                                <a href="transporte.php" class="btn btn-outline-secondary me-2">
                                    <i class="ti ti-arrow-left me-2"></i>
                                    Volver a Rutas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-1">
                                <?= $total_asignaciones ?>
                            </div>
                            <div class="stats-label">Total Asignaciones</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $asignaciones_activas ?>
                            </div>
                            <div class="stats-label">Asignaciones Activas</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $total_vehiculos_uso ?>
                            </div>
                            <div class="stats-label">Vehículos en Uso</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $total_estudiantes ?>
                            </div>
                            <div class="stats-label">Total Estudiantes</div>
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
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Período Académico</label>
                                <select class="form-select" id="filtroPeriodo">
                                    <option value="">Todos los períodos</option>
                                    <?php
                                    $periodos_unicos = array_unique(array_column($asignaciones, 'periodo_nombre'));
                                    foreach ($periodos_unicos as $periodo):
                                        if (!empty($periodo)):
                                    ?>
                                        <option value="<?= htmlspecialchars($periodo) ?>">
                                            <?= htmlspecialchars($periodo) ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarAsignacion" 
                                       placeholder="Buscar por ruta o vehículo...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Asignaciones -->
                <div class="row" id="asignacionesContainer">
                    <?php if (empty($asignaciones)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ti ti-clipboard-off"></i>
                                <h5 class="mt-3">No hay asignaciones registradas</h5>
                                <p>Las asignaciones se crean desde la gestión de rutas</p>
                                <a href="transporte.php" class="btn btn-primary mt-2">
                                    <i class="ti ti-bus me-2"></i>
                                    Ir a Gestión de Rutas
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($asignaciones as $asignacion): 
                    $configuracion = json_decode($asignacion['configuracion'], true) ?: [];
                    $paraderos = json_decode($asignacion['paraderos'], true) ?: [];
                    $datos_vehiculo = json_decode($asignacion['datos_vehiculo'], true) ?: [];
                    
                    $estado_class = $asignacion['activo'] == 1 ? 'activo' : 'inactivo';
                    $estado_text = $asignacion['activo'] == 1 ? 'ACTIVO' : 'INACTIVO';
                    
                    $conductor_id = $configuracion['conductor_id'] ?? null;
                    $observaciones = $configuracion['observaciones'] ?? '';
                    
                    // Extraer datos del vehículo desde el JSON
                    $tipo = $datos_vehiculo['tipo'] ?? 'N/A';
                    $modelo = $datos_vehiculo['modelo'] ?? 'N/A';
                    $capacidad = $datos_vehiculo['capacidad'] ?? 0;
                    
                    $total_estudiantes_asignacion = 0;
                    foreach ($paraderos as $paradero) {
                        if (isset($paradero['estudiantes']) && is_array($paradero['estudiantes'])) {
                            $total_estudiantes_asignacion += count($paradero['estudiantes']);
                        }
                    }
                    
                    $conductor_nombre = '';
                    if ($conductor_id) {
                        try {
                            $sql_conductor = "SELECT CONCAT(nombres, ' ', apellidos) as nombre FROM usuarios WHERE id = :id";
                            $stmt_conductor = $conexion->prepare($sql_conductor);
                            $stmt_conductor->bindParam(':id', $conductor_id);
                            $stmt_conductor->execute();
                            $conductor = $stmt_conductor->fetch(PDO::FETCH_ASSOC);
                            $conductor_nombre = $conductor['nombre'] ?? '';
                        } catch (PDOException $e) {
                            $conductor_nombre = '';
                        }
                    }
                ?>
                    <div class="col-md-6 col-lg-4 mb-4 asignacion-card-wrapper" 
                         data-estado="<?= $asignacion['activo'] ?>" 
                         data-periodo="<?= htmlspecialchars($asignacion['periodo_nombre']) ?>"
                         data-busqueda="<?= strtolower($asignacion['ruta_nombre'] . ' ' . $asignacion['placa']) ?>">
                        <div class="card asignacion-card <?= $estado_class ?> h-100">
                            <div class="asignacion-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">
                                            <?= htmlspecialchars($asignacion['ruta_nombre']) ?>
                                        </h6>
                                        <small class="text-muted"><?= htmlspecialchars($asignacion['codigo_ruta']) ?></small>
                                        <div class="mt-2">
                                            <span class="estado-badge badge-<?= $estado_class ?>">
                                                <?= $estado_text ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="info-box">
                                    <div class="info-label">
                                        <i class="ti ti-calendar me-1"></i>
                                        Período Académico
                                    </div>
                                    <div class="info-value">
                                        <?= htmlspecialchars($asignacion['periodo_nombre']) ?>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <div class="info-label">
                                        <i class="ti ti-car me-1"></i>
                                        Vehículo Asignado
                                    </div>
                                    <div class="info-value">
                                        <strong><?= htmlspecialchars($asignacion['placa']) ?></strong> - 
                                        <?= htmlspecialchars($modelo) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($tipo) ?> | 
                                        Capacidad: <?= $capacidad ?> pasajeros
                                    </small>
                                </div>

                                <?php if (!empty($conductor_nombre)): ?>
                                    <div class="info-box">
                                        <div class="info-label">
                                            <i class="ti ti-user me-1"></i>
                                            Conductor
                                        </div>
                                        <div class="info-value">
                                            <?= htmlspecialchars($conductor_nombre) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($asignacion['tarifa'])): ?>
                                    <div class="info-box">
                                        <div class="info-label">
                                            <i class="ti ti-cash me-1"></i>
                                            Tarifa Mensual
                                        </div>
                                        <div class="info-value">
                                            S/ <?= number_format($asignacion['tarifa'], 2) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="text-center mt-3">
                                    <div class="estudiantes-count">
                                        <i class="ti ti-users me-1"></i>
                                        <?= $total_estudiantes_asignacion ?> Estudiantes Asignados
                                    </div>
                                </div>

                                <?php if (!empty($observaciones)): ?>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="ti ti-note me-1"></i>
                                            <strong>Obs:</strong> <?= htmlspecialchars($observaciones) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        Creado: <?= date('d/m/Y H:i', strtotime($asignacion['fecha_creacion'])) ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-light">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="cambiarEstado(<?= $asignacion['id'] ?>, <?= $asignacion['activo'] ?>)">
                                        <i class="ti ti-toggle-<?= $asignacion['activo'] == 1 ? 'right' : 'left' ?>"></i>
                                        <?= $asignacion['activo'] == 1 ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="eliminarAsignacion(<?= $asignacion['id'] ?>)">
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

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroEstado, #filtroPeriodo, #buscarAsignacion').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const periodoFiltro = $('#filtroPeriodo').val();
            const busqueda = $('#buscarAsignacion').val().toLowerCase();

            $('.asignacion-card-wrapper').each(function() {
                const card = $(this);
                const estado = card.data('estado');
                const periodo = card.data('periodo');
                const busquedaData = card.data('busqueda');

                let mostrar = true;

                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                if (periodoFiltro && periodo !== periodoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !busquedaData.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #filtroPeriodo, #buscarAsignacion').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function cambiarEstado(id, estadoActual) {
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
            
            Swal.fire({
                title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} esta asignación?`,
                text: `La asignación será ${accion}da`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado == 1 ? '#198754' : '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCarga();
                    
                    fetch('modales/transporte/procesar_asignaciones.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `accion=cambiar_estado&id=${id}&estado=${nuevoEstado}`
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
                        mostrarError('Error al cambiar el estado');
                    });
                }
            });
        }

        function eliminarAsignacion(id) {
            Swal.fire({
                title: '¿Eliminar esta asignación?',
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
                    
                    fetch('modales/transporte/procesar_asignaciones.php', {
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
                        mostrarError('Error al eliminar la asignación');
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