<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
} 
    require_once 'conexion/bd.php';

    // Obtener procesos de admisión con estadísticas
    try {
        $sql = "SELECT p.*, 
                    COUNT(DISTINCT po.id) as total_postulaciones,
                    COUNT(DISTINCT CASE WHEN po.estado = 'ADMITIDO' THEN po.id END) as admitidos,
                    COUNT(DISTINCT CASE WHEN po.estado = 'LISTA_ESPERA' THEN po.id END) as lista_espera
                FROM procesos_admision p
                LEFT JOIN postulaciones po ON p.id = po.proceso_id
                GROUP BY p.id
                ORDER BY p.anio_academico DESC, p.fecha_creacion DESC";
        
        $stmt_procesos = $conexion->prepare($sql);
        $stmt_procesos->execute();
        $procesos = $stmt_procesos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $procesos = [];
        $error_procesos = "Error al cargar procesos: " . $e->getMessage();
    }

    // Obtener niveles educativos para configuración de vacantes
    try {
        $stmt_niveles = $conexion->prepare("SELECT * FROM niveles_educativos WHERE activo = 1 ORDER BY orden ASC");
        $stmt_niveles->execute();
        $niveles_educativos = $stmt_niveles->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $niveles_educativos = [];
    }

    // Calcular estadísticas
    $total_procesos = count($procesos);
    $procesos_activos = count(array_filter($procesos, function($p) { return $p['activo']; }));
    $procesos_abiertos = count(array_filter($procesos, function($p) { return $p['estado'] === 'ABIERTO'; }));
    $total_postulaciones = array_sum(array_column($procesos, 'total_postulaciones'));

    // Estadísticas por estado
    $estados_count = [];
    foreach ($procesos as $proceso) {
        $estado = $proceso['estado'];
        $estados_count[$estado] = ($estados_count[$estado] ?? 0) + 1;
    }

    // Año actual para configuración
    $anio_actual = date('Y');
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procesos de Admisión - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }
        
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .proceso-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #a8e6cf 0%, #84d2c5 100%);
            border: none;
            color: #2d3748;
        }
        
        .vacante-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            margin: 0.1rem;
        }
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
        
        .estado-badge-configuracion { background: #ffd166; color: #000; }
        .estado-badge-abierto { background: #06d6a0; color: #fff; }
        .estado-badge-cerrado { background: #ef476f; color: #fff; }
        .estado-badge-finalizado { background: #adb5bd; color: #fff; }
        
        .postulaciones-info {
            text-align: center;
        }
        
        .postulaciones-numero {
            font-weight: 600;
            color: #495057;
            font-size: 1.1rem;
        }
        
        .vacantes-list {
            max-height: 100px;
            overflow-y: auto;
        }
        
        .configuracion-info {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.5rem;
            margin-top: 0.25rem;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="container-fluid">
                
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
                                <h4 class="fw-bold mb-0">Procesos de Admisión</h4>
                                <p class="mb-0 text-muted">Gestiona los procesos de admisión y postulaciones</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProceso">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Proceso
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
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $total_procesos ?></h3>
                                        <p class="mb-0">Total Procesos</p>
                                    </div>
                                    <i class="ti ti-list-check" style="font-size: 2.5rem; opacity: 0.7;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $procesos_abiertos ?></h3>
                                        <p class="mb-0">Procesos Abiertos</p>
                                    </div>
                                    <i class="ti ti-door-enter" style="font-size: 2.5rem; opacity: 0.7;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $total_postulaciones ?></h3>
                                        <p class="mb-0">Total Postulaciones</p>
                                    </div>
                                    <i class="ti ti-users" style="font-size: 2.5rem; opacity: 0.7;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-0"><?= $procesos_activos ?></h3>
                                        <p class="mb-0">Procesos Activos</p>
                                    </div>
                                    <i class="ti ti-check" style="font-size: 2.5rem; opacity: 0.7;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Año Académico</label>
                                <select class="form-select" id="filtroAnio">
                                    <option value="">Todos los años</option>
                                    <?php 
                                    $anios_unicos = array_unique(array_column($procesos, 'anio_academico'));
                                    rsort($anios_unicos);
                                    foreach ($anios_unicos as $anio): 
                                    ?>
                                        <option value="<?= $anio ?>"><?= $anio ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="CONFIGURACION">Configuración</option>
                                    <option value="ABIERTO">Abierto</option>
                                    <option value="CERRADO">Cerrado</option>
                                    <option value="FINALIZADO">Finalizado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Activo</label>
                                <select class="form-select" id="filtroActivo">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarProceso" placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarProcesos()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Procesos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Procesos de Admisión</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaProcesos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Año Académico</th>
                                        <th>Vacantes por Nivel</th>
                                        <th>Configuración</th>
                                        <th>Postulaciones</th>
                                        <th>Estado</th>
                                        <th>Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($procesos as $proceso): 
                                        $configuracion = json_decode($proceso['configuracion'], true) ?: [];
                                        $vacantes = json_decode($proceso['vacantes'], true) ?: [];
                                        $total_vacantes = array_sum(array_column($vacantes, 'cantidad'));
                                    ?>
                                        <tr data-anio="<?= $proceso['anio_academico'] ?>" 
                                            data-estado="<?= $proceso['estado'] ?>"
                                            data-activo="<?= $proceso['activo'] ?>">
                                            <td>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($proceso['nombre']) ?></div>
                                                    <small class="text-muted">
                                                        Creado: <?= date('d/m/Y', strtotime($proceso['fecha_creacion'])) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info proceso-badge"><?= $proceso['anio_academico'] ?></span>
                                            </td>
                                            <td>
                                                <div class="vacantes-list">
                                                    <?php if (!empty($vacantes)): ?>
                                                        <?php foreach ($vacantes as $vacante): ?>
                                                            <span class="badge vacante-badge bg-primary">
                                                                <?= htmlspecialchars($vacante['nivel'] ?? 'N/A') ?>: 
                                                                <?= $vacante['cantidad'] ?? 0 ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                        <div class="mt-1">
                                                            <strong>Total: <?= $total_vacantes ?></strong>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted">Sin vacantes configuradas</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="configuracion-info">
                                                    <?php if (!empty($configuracion['fecha_inicio'])): ?>
                                                        <div><i class="ti ti-calendar-event"></i> 
                                                            Inicio: <?= date('d/m/Y', strtotime($configuracion['fecha_inicio'])) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($configuracion['fecha_fin'])): ?>
                                                        <div><i class="ti ti-calendar-event"></i> 
                                                            Fin: <?= date('d/m/Y', strtotime($configuracion['fecha_fin'])) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($configuracion['costo_inscripcion'])): ?>
                                                        <div><i class="ti ti-cash"></i> 
                                                            S/. <?= number_format($configuracion['costo_inscripcion'], 2) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="postulaciones-info">
                                                    <div class="postulaciones-numero"><?= $proceso['total_postulaciones'] ?></div>
                                                    <small class="text-success">Admitidos: <?= $proceso['admitidos'] ?></small><br>
                                                    <small class="text-warning">Lista espera: <?= $proceso['lista_espera'] ?></small>
                                                    <?php if ($proceso['total_postulaciones'] > 0): ?>
                                                        <br><button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                                                onclick="verPostulaciones(<?= $proceso['id'] ?>)">
                                                            <i class="ti ti-eye"></i> Ver
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge proceso-badge estado-badge-<?= strtolower($proceso['estado']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', strtolower($proceso['estado']))) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $proceso['activo'] ? 'bg-success' : 'bg-danger' ?> proceso-badge">
                                                    <?= $proceso['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarProceso(<?= $proceso['id'] ?>)" 
                                                            title="Editar Proceso">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalles(<?= $proceso['id'] ?>)" 
                                                            title="Ver Detalles">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $proceso['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoProceso(<?= $proceso['id'] ?>, <?= $proceso['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $proceso['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="ti <?= $proceso['activo'] ? 'ti-toggle-right' : 'ti-toggle-left' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                            onclick="cambiarEstadoProceso(<?= $proceso['id'] ?>, '<?= $proceso['estado'] ?>')" 
                                                            title="Cambiar Estado del Proceso">
                                                        <i class="ti ti-settings"></i>
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

                <!-- Resumen por Estados -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Estado</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($estados_count as $estado => $count): ?>
                                        <div class="col-md-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span><?= ucfirst(str_replace('_', ' ', strtolower($estado))) ?></span>
                                                <span class="badge estado-badge-<?= strtolower($estado) ?>"><?= $count ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <?php include 'modales/admision/modal_agregar.php'; ?>
    <?php include 'modales/admision/modal_editar.php'; ?>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaProcesos;
        const nivelesEducativos = <?= json_encode($niveles_educativos) ?>;

        $(document).ready(function() {
            tablaProcesos = $('#tablaProcesos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            $('#filtroAnio, #filtroEstado, #filtroActivo').on('change', aplicarFiltros);
            $('#buscarProceso').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const anioFiltro = $('#filtroAnio').val();
            const estadoFiltro = $('#filtroEstado').val();
            const activoFiltro = $('#filtroActivo').val();
            const busqueda = $('#buscarProceso').val().toLowerCase();

            $('#tablaProcesos tbody tr').each(function() {
                const fila = $(this);
                const anio = fila.data('anio');
                const estado = fila.data('estado');
                const activo = fila.data('activo');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (anioFiltro && anio != anioFiltro) mostrar = false;
                if (estadoFiltro && estado !== estadoFiltro) mostrar = false;
                if (activoFiltro !== '' && activo != activoFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroAnio, #filtroEstado, #filtroActivo').val('');
            $('#buscarProceso').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarProceso(id) {
            mostrarCarga();
            
            fetch('modales/admision/procesar_admision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionProceso(data.proceso);
                    $('#modalEditarProceso').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del proceso');
            });
        }

        function verDetalles(id) {
            mostrarCarga();
            
            fetch('modales/admision/procesar_admision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalles&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetallesProceso(data.proceso);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalles del proceso');
            });
        }

        function mostrarDetallesProceso(proceso) {
            const config = proceso.configuracion || {};
            const vacantes = proceso.vacantes || [];
            
            let vacantesHTML = '<ul class="list-group">';
            vacantes.forEach(v => {
                vacantesHTML += `<li class="list-group-item d-flex justify-content-between">
                    <span>${v.nivel}</span>
                    <span class="badge bg-primary">${v.cantidad}</span>
                </li>`;
            });
            vacantesHTML += '</ul>';
            
            Swal.fire({
                title: proceso.nombre,
                html: `
                    <div class="text-left">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Año Académico:</strong> ${proceso.anio_academico}<br>
                                <strong>Estado:</strong> ${proceso.estado}<br>
                                <strong>Activo:</strong> ${proceso.activo ? 'Sí' : 'No'}
                            </div>
                            <div class="col-md-6">
                                <strong>Postulaciones:</strong> ${proceso.total_postulaciones}<br>
                                <strong>Admitidos:</strong> ${proceso.admitidos}<br>
                                <strong>Lista Espera:</strong> ${proceso.lista_espera}
                            </div>
                        </div>
                        <hr>
                        <h6>Configuración:</h6>
                        <small>Inicio: ${config.fecha_inicio || 'N/A'}</small><br>
                        <small>Fin: ${config.fecha_fin || 'N/A'}</small><br>
                        <small>Costo: S/. ${config.costo_inscripcion || '0.00'}</small>
                        <hr>
                        <h6>Vacantes:</h6>
                        ${vacantesHTML}
                    </div>
                `,
                width: '700px',
                confirmButtonText: 'Cerrar'
            });
        }

        function toggleEstadoProceso(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este proceso?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstadoProceso(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstadoProceso(id, estado) {
            mostrarCarga();

            fetch('modales/admision/procesar_admision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=toggle_activo&id=${id}&activo=${estado}`
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
                mostrarError('Error al cambiar estado del proceso');
            });
        }

        function cambiarEstadoProceso(id, estadoActual) {
            const estados = ['CONFIGURACION', 'ABIERTO', 'CERRADO', 'FINALIZADO'];
            let opcionesHTML = '<select class="form-select" id="nuevoEstadoProceso">';
            
            estados.forEach(estado => {
                const selected = estado === estadoActual ? 'selected' : '';
                opcionesHTML += `<option value="${estado}" ${selected}>${estado}</option>`;
            });
            opcionesHTML += '</select>';

            Swal.fire({
                title: 'Cambiar Estado del Proceso',
                html: `<div class="mb-3">
                    <label class="form-label">Nuevo Estado:</label>
                    ${opcionesHTML}
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Cambiar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const nuevoEstado = document.getElementById('nuevoEstadoProceso').value;
                    if (!nuevoEstado) {
                        Swal.showValidationMessage('Debe seleccionar un estado');
                        return false;
                    }
                    return nuevoEstado;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarCambioEstadoProceso(id, result.value);
                }
            });
        }

        function ejecutarCambioEstadoProceso(id, estado) {
            mostrarCarga();

            fetch('modales/admision/procesar_admision.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=cambiar_estado&id=${id}&estado=${estado}`
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
                mostrarError('Error al cambiar estado del proceso');
            });
        }

        function verPostulaciones(procesoId) {
            window.location.href = `postulaciones.php?proceso_id=${procesoId}`;
        }

        function exportarProcesos() {
            window.open('reportes/exportar_procesos_admision.php', '_blank');
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

    <script>
        /**
         * Validar año académico en tiempo real
         * Permite SOLO 4 dígitos exactos
         */
        function validarAnioAcademico(input, prefijo) {
            // Convertir a string para validar longitud
            let valor = input.value.toString();
            
            // Limitar a máximo 4 dígitos mientras escribe
            if (valor.length > 4) {
                input.value = valor.slice(0, 4);
                valor = input.value;
            }
            
            // Validar cuando tiene 4 dígitos
            if (valor.length === 4) {
                const anio = parseInt(valor);
                const anioActual = <?= $anio_actual ?>;
                const anioMax = anioActual + 5;
                
                // Validar rango permitido
                if (anio < anioActual || anio > anioMax) {
                    Swal.fire({
                        title: '⚠️ Año Fuera de Rango',
                        html: `
                            <div class="text-center">
                                <p class="mb-3">El año académico <strong>${anio}</strong> no está dentro del rango permitido.</p>
                                <div class="alert alert-warning">
                                    <i class="ti ti-calendar-event me-2"></i>
                                    Rango válido: <strong>${anioActual}</strong> - <strong>${anioMax}</strong>
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonColor: '#fd7e14',
                        confirmButtonText: 'Entendido',
                        allowOutsideClick: false
                    }).then(() => {
                        input.value = '';
                        input.focus();
                    });
                    
                    // Marcar campo con error
                    $(input).addClass('campo-error');
                    setTimeout(() => {
                        $(input).removeClass('campo-error');
                    }, 2000);
                } else {
                    // Año válido, remover cualquier marca de error
                    $(input).removeClass('campo-error is-invalid');
                    $(input).next('.invalid-feedback').remove();
                }
            }
            
            // Si tiene menos de 4 dígitos, remover marcas de error
            if (valor.length < 4) {
                $(input).removeClass('campo-error is-invalid');
            }
        }

        /**
         * Validación adicional al enviar el formulario
         */
        function validarAnioAcademicoEnFormulario(anioInputId, erroresArray) {
            const anio = $(anioInputId).val();
            const anioActual = <?= $anio_actual ?>;
            const anioMax = anioActual + 5;
            
            // Validar que exista
            if (!anio || anio.trim() === '') {
                marcarCampoError(anioInputId, 'El año académico es obligatorio');
                erroresArray.push('Año académico requerido');
                return false;
            }
            
            // Validar exactamente 4 dígitos
            if (anio.length !== 4) {
                marcarCampoError(anioInputId, 'El año académico debe tener exactamente 4 dígitos');
                erroresArray.push('Año: debe tener exactamente 4 dígitos');
                
                Swal.fire({
                    title: '❌ Año Incompleto',
                    html: `
                        <div class="text-center">
                            <p class="mb-3">El año académico debe tener <strong>exactamente 4 dígitos</strong>.</p>
                            <div class="alert alert-danger">
                                <i class="ti ti-alert-triangle me-2"></i>
                                Dígitos ingresados: <strong>${anio.length}</strong> de 4
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Corregir',
                    allowOutsideClick: false
                });
                
                return false;
            }
            
            // Validar que sea numérico
            const anioNum = parseInt(anio);
            if (isNaN(anioNum)) {
                marcarCampoError(anioInputId, 'El año académico debe ser numérico');
                erroresArray.push('Año: debe contener solo números');
                return false;
            }
            
            // Validar rango
            if (anioNum < anioActual || anioNum > anioMax) {
                marcarCampoError(anioInputId, `El año debe estar entre ${anioActual} y ${anioMax}`);
                erroresArray.push(`Año: fuera del rango (${anioActual}-${anioMax})`);
                
                Swal.fire({
                    title: '⚠️ Año Fuera de Rango',
                    html: `
                        <div class="text-center">
                            <p class="mb-3">El año <strong>${anioNum}</strong> no está dentro del rango permitido.</p>
                            <div class="alert alert-warning">
                                <i class="ti ti-calendar-event me-2"></i>
                                Rango válido: <strong>${anioActual}</strong> - <strong>${anioMax}</strong>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonColor: '#fd7e14',
                    confirmButtonText: 'Corregir',
                    allowOutsideClick: false
                });
                
                return false;
            }
            
            return true;
        }

        // Prevenir pegado de más de 4 dígitos
        $(document).ready(function() {
            $('#add_anio_academico, #edit_anio_academico').on('paste', function(e) {
                setTimeout(() => {
                    let valor = $(this).val().toString();
                    if (valor.length > 4) {
                        $(this).val(valor.slice(0, 4));
                        
                        Swal.fire({
                            title: 'Texto Recortado',
                            text: 'Solo se permiten 4 dígitos para el año académico',
                            icon: 'info',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                }, 10);
            });
            
            // Prevenir entrada de caracteres no numéricos
            $('#add_anio_academico, #edit_anio_academico').on('keypress', function(e) {
                // Permitir solo números
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Solo Números',
                        text: 'El año académico debe contener solo números',
                        icon: 'warning',
                        timer: 1500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        });
    </script>
</body>
</html>