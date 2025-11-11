<?php
    session_start();

    // Redirigir al index si no hay sesión iniciada
    if (session_status() !== PHP_SESSION_ACTIVE
    || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
    header('Location: ../index.php');
    exit;
    }
    require_once 'conexion/bd.php';

    // Obtener comunicaciones con información completa
    try {
        $sql = "SELECT ca.*, 
                    p.codigo_postulacion,
                    p.grado_solicitado,
                    p.datos_postulante,
                    p.datos_apoderado,
                    p.estado as estado_postulacion,
                    pa.nombre as proceso_nombre,
                    pa.anio_academico
                FROM comunicaciones_admision ca
                INNER JOIN postulaciones p ON ca.postulacion_id = p.id
                INNER JOIN procesos_admision pa ON p.proceso_id = pa.id
                ORDER BY ca.id DESC";
        
        $stmt_comunicaciones = $conexion->prepare($sql);
        $stmt_comunicaciones->execute();
        $comunicaciones = $stmt_comunicaciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $comunicaciones = [];
        $error_comunicaciones = "Error al cargar comunicaciones: " . $e->getMessage();
    }

    // Obtener postulaciones activas para crear nuevas comunicaciones
    try {
        $stmt_postulaciones = $conexion->prepare("
            SELECT p.*, pa.nombre as proceso_nombre, pa.anio_academico 
            FROM postulaciones p
            INNER JOIN procesos_admision pa ON p.proceso_id = pa.id
            WHERE p.estado IN ('REGISTRADA', 'EN_EVALUACION', 'ADMITIDO', 'LISTA_ESPERA', 'NO_ADMITIDO')
            ORDER BY p.fecha_postulacion DESC
        ");
        $stmt_postulaciones->execute();
        $postulaciones_disponibles = $stmt_postulaciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $postulaciones_disponibles = [];
    }

    // Calcular estadísticas
    $total_comunicaciones = count($comunicaciones);
    $comunicaciones_pendientes = count(array_filter($comunicaciones, function($c) { return $c['estado'] == 'PENDIENTE'; }));
    $comunicaciones_enviadas = count(array_filter($comunicaciones, function($c) { return $c['estado'] == 'ENVIADO'; }));
    $comunicaciones_entregadas = count(array_filter($comunicaciones, function($c) { return $c['estado'] == 'ENTREGADO'; }));
    $comunicaciones_error = count(array_filter($comunicaciones, function($c) { return $c['estado'] == 'ERROR'; }));

    // Estadísticas por tipo de comunicación
    $tipos_count = [];
    foreach ($comunicaciones as $comunicacion) {
        $config = json_decode($comunicacion['configuracion'], true);
        $tipo = $config['tipo'] ?? 'No especificado';
        $tipos_count[$tipo] = ($tipos_count[$tipo] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comunicaciones Automatizadas - ANDRÉS AVELINO CÁCERES</title>
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

        .comunicacion-info {
            line-height: 1.4;
        }

        .postulante-nombre {
            font-weight: 600;
            color: #495057;
        }

        .codigo-postulacion {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .tipo-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .stats-card {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #2c3e50;
            border: none;
            border-radius: 15px;
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

        .metadatos-info {
            font-size: 0.8rem;
            background-color: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }

        .timeline-item {
            padding: 0.5rem;
            border-left: 2px solid #dee2e6;
            margin-left: 1rem;
        }

        .estado-icon {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .estado-pendiente { background-color: #ffc107; }
        .estado-enviado { background-color: #17a2b8; }
        .estado-entregado { background-color: #28a745; }
        .estado-error { background-color: #dc3545; }
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
                                <h4 class="fw-bold mb-0">Comunicaciones Automatizadas</h4>
                                <p class="mb-0 text-muted">Gestión de notificaciones y comunicaciones del proceso de admisión</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearComunicacion">
                                    <i class="ti ti-mail-plus me-2"></i>
                                    Nueva Comunicación
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
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-mail fs-8"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= $total_comunicaciones ?></h5>
                                        <small>Total Comunicaciones</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-clock fs-8"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= $comunicaciones_pendientes ?></h5>
                                        <small>Pendientes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-check fs-8"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= $comunicaciones_entregadas ?></h5>
                                        <small>Entregadas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ti ti-alert-circle fs-8"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0"><?= $comunicaciones_error ?></h5>
                                        <small>Con Error</small>
                                    </div>
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
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="ENVIADO">Enviado</option>
                                    <option value="ENTREGADO">Entregado</option>
                                    <option value="ERROR">Error</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="EMAIL">Email</option>
                                    <option value="WHATSAPP">WhatsApp</option>
                                    <option value="SMS">SMS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarComunicacion" placeholder="Buscar por código o nombre...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Comunicaciones -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Comunicaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaComunicaciones">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Postulante</th>
                                        <th>Proceso</th>
                                        <th>Configuración</th>
                                        <th>Estado Postulación</th>
                                        <th>Estado Comunicación</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comunicaciones as $comunicacion): 
                                        $config = json_decode($comunicacion['configuracion'], true) ?: [];
                                        $metadatos = json_decode($comunicacion['metadatos'], true) ?: [];
                                        $datos_postulante = json_decode($comunicacion['datos_postulante'], true) ?: [];
                                        $datos_apoderado = json_decode($comunicacion['datos_apoderado'], true) ?: [];
                                    ?>
                                        <tr data-estado="<?= $comunicacion['estado'] ?>" 
                                            data-tipo="<?= $config['tipo'] ?? '' ?>">
                                            <td><strong>#<?= $comunicacion['id'] ?></strong></td>
                                            <td>
                                                <div class="comunicacion-info">
                                                    <div class="postulante-nombre">
                                                        <?= htmlspecialchars($datos_postulante['nombres'] ?? 'No especificado') ?>
                                                        <?= htmlspecialchars($datos_postulante['apellidos'] ?? '') ?>
                                                    </div>
                                                    <div class="codigo-postulacion">
                                                        Código: <?= htmlspecialchars($comunicacion['codigo_postulacion']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        Grado: <?= htmlspecialchars($comunicacion['grado_solicitado']) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($comunicacion['proceso_nombre']) ?></strong><br>
                                                <small class="text-muted">Año <?= $comunicacion['anio_academico'] ?></small>
                                            </td>
                                            <td>
                                                <?php if (isset($config['tipo'])): ?>
                                                    <span class="badge tipo-badge bg-info">
                                                        <?= htmlspecialchars($config['tipo']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (isset($config['asunto'])): ?>
                                                    <div class="mt-1">
                                                        <small><?= htmlspecialchars($config['asunto']) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($config['destinatario'])): ?>
                                                    <div class="mt-1">
                                                        <small class="text-muted">Para: <?= htmlspecialchars($config['destinatario']) ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php
                                                    switch($comunicacion['estado_postulacion']) {
                                                        case 'REGISTRADA': echo 'bg-secondary'; break;
                                                        case 'EN_EVALUACION': echo 'bg-warning text-dark'; break;
                                                        case 'ADMITIDO': echo 'bg-success'; break;
                                                        case 'LISTA_ESPERA': echo 'bg-info'; break;
                                                        case 'NO_ADMITIDO': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= $comunicacion['estado_postulacion'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="estado-icon estado-<?= strtolower($comunicacion['estado']) ?>"></span>
                                                <span class="badge <?php
                                                    switch($comunicacion['estado']) {
                                                        case 'PENDIENTE': echo 'bg-warning text-dark'; break;
                                                        case 'ENVIADO': echo 'bg-info'; break;
                                                        case 'ENTREGADO': echo 'bg-success'; break;
                                                        case 'ERROR': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?= $comunicacion['estado'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($metadatos['fecha_envio'])): ?>
                                                    <small><?= date('d/m/Y H:i', strtotime($metadatos['fecha_envio'])) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">Sin enviar</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="verComunicacion(<?= $comunicacion['id'] ?>)" 
                                                            title="Ver Detalles">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <?php if ($comunicacion['estado'] == 'ERROR'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="reenviarComunicacion(<?= $comunicacion['id'] ?>)" 
                                                                title="Reenviar">
                                                            <i class="ti ti-reload"></i>
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
    <?php include 'modales/comunicaciones/modal_crear.php'; ?>
    <?php include 'modales/comunicaciones/modal_ver.php'; ?>
    <?php include 'modales/comunicaciones/modal_reenviar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaComunicaciones;
        const postulacionesDisponibles = <?= json_encode($postulaciones_disponibles) ?>;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaComunicaciones = $('#tablaComunicaciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Filtros personalizados
            $('#filtroEstado, #filtroTipo').on('change', aplicarFiltros);
            $('#buscarComunicacion').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const tipoFiltro = $('#filtroTipo').val();
            const busqueda = $('#buscarComunicacion').val().toLowerCase();

            $('#tablaComunicaciones tbody tr').each(function() {
                const fila = $(this);
                const estado = fila.data('estado');
                const tipo = fila.data('tipo');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (tipoFiltro && tipo !== tipoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #filtroTipo').val('');
            $('#buscarComunicacion').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function verComunicacion(id) {
            mostrarCarga();
            
            fetch('modales/comunicaciones/procesar_comunicaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosComunicacion(data.comunicacion);
                    $('#modalVerComunicacion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la comunicación');
            });
        }

        function reenviarComunicacion(id) {
            Swal.fire({
                title: '¿Reenviar Comunicación?',
                text: '¿Desea reenviar esta comunicación?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reenviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarReenvio(id);
                }
            });
        }

        function ejecutarReenvio(id) {
            mostrarCarga();

            fetch('modales/comunicaciones/procesar_comunicaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=reenviar&id=${id}`
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
                mostrarError('Error al reenviar comunicación');
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