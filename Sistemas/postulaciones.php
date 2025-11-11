<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
} 
    require_once 'conexion/bd.php';

    // Obtener procesos de admisión activos
    try {
        $sql = "SELECT * FROM procesos_admision WHERE activo = 1 ORDER BY anio_academico DESC, fecha_creacion DESC";
        $stmt_procesos = $conexion->prepare($sql);
        $stmt_procesos->execute();
        $procesos_admision = $stmt_procesos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $procesos_admision = [];
        $error_procesos = "Error al cargar procesos: " . $e->getMessage();
    }

    // Obtener postulaciones con información completa
    try {
        $sql = "SELECT p.*, 
                    pa.nombre as proceso_nombre,
                    pa.anio_academico,
                    pa.estado as proceso_estado
                FROM postulaciones p
                INNER JOIN procesos_admision pa ON p.proceso_id = pa.id
                ORDER BY p.fecha_postulacion DESC";
        
        $stmt_postulaciones = $conexion->prepare($sql);
        $stmt_postulaciones->execute();
        $postulaciones = $stmt_postulaciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $postulaciones = [];
        $error_postulaciones = "Error al cargar postulaciones: " . $e->getMessage();
    }

    // Calcular estadísticas
    $total_postulaciones = count($postulaciones);
    $postulaciones_registradas = count(array_filter($postulaciones, function($p) { return $p['estado'] == 'REGISTRADA'; }));
    $postulaciones_evaluacion = count(array_filter($postulaciones, function($p) { return $p['estado'] == 'EN_EVALUACION'; }));
    $postulaciones_admitidos = count(array_filter($postulaciones, function($p) { return $p['estado'] == 'ADMITIDO'; }));
    $postulaciones_lista_espera = count(array_filter($postulaciones, function($p) { return $p['estado'] == 'LISTA_ESPERA'; }));
    $postulaciones_no_admitidos = count(array_filter($postulaciones, function($p) { return $p['estado'] == 'NO_ADMITIDO'; }));

    // Estadísticas por grado
    $grados_count = [];
    foreach ($postulaciones as $postulacion) {
        $grado = $postulacion['grado_solicitado'];
        $grados_count[$grado] = ($grados_count[$grado] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proceso de Postulación - ANDRÉS AVELINO CÁCERES</title>
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
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .estado-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 12px;
            font-weight: 500;
        }

        .postulante-info {
            line-height: 1.4;
        }

        .postulante-nombre {
            font-weight: 600;
            color: #2c3e50;
        }

        .postulante-codigo {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        .grado-badge {
            background: linear-gradient(135deg, #a8e6cf 0%, #dcedc1 100%);
            color: #2d5016;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stats-card {
            background: linear-gradient(135deg, #ffd3a5 0%, #fd6585 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            margin: 0.1rem;
        }

        .proceso-info {
            background-color: #fff8f0;
            border-left: 4px solid #ffa94d;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .apoderado-info {
            background-color: #f0f8ff;
            border-radius: 6px;
            padding: 0.6rem;
            font-size: 0.85rem;
        }

        .documento-item {
            display: inline-block;
            background: #e8f5e9;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            margin: 0.2rem;
            font-size: 0.75rem;
        }

        .evaluacion-score {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2ecc71;
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
                                <h4 class="fw-bold mb-0" style="color: #2c3e50;">
                                    <i class="ti ti-school me-2" style="color: #ffa94d;"></i>
                                    Proceso de Postulación
                                </h4>
                                <p class="mb-0 text-muted">Gestiona las postulaciones y procesos de admisión</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPostulacion"
                                        style="background: linear-gradient(135deg, #ffa94d 0%, #ff6b6b 100%); border: none;">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Postulación
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #a8e6cf 0%, #dcedc1 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #2d5016;"><?= $total_postulaciones ?></div>
                                <div class="stats-label" style="color: #2d5016;">Total Postulaciones</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #ffd3a5 0%, #fdab7d 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #7d3c00;"><?= $postulaciones_registradas ?></div>
                                <div class="stats-label" style="color: #7d3c00;">Registradas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #a8d8ea 0%, #aa96da 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #2d3561;"><?= $postulaciones_evaluacion ?></div>
                                <div class="stats-label" style="color: #2d3561;">En Evaluación</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #c7ecee 0%, #7ee8fa 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #0d5e63;"><?= $postulaciones_admitidos ?></div>
                                <div class="stats-label" style="color: #0d5e63;">Admitidos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #fcf6bd 0%, #ffeaa7 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #6c5a00;"><?= $postulaciones_lista_espera ?></div>
                                <div class="stats-label" style="color: #6c5a00;">Lista Espera</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card" style="background: linear-gradient(135deg, #ffb6b9 0%, #fec8d8 100%); border: none;">
                            <div class="card-body text-center">
                                <div class="stats-number" style="color: #7d0000;"><?= $postulaciones_no_admitidos ?></div>
                                <div class="stats-label" style="color: #7d0000;">No Admitidos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body" style="background-color: #fafafa;">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Proceso de Admisión</label>
                                <select class="form-select" id="filtroProceso">
                                    <option value="">Todos los procesos</option>
                                    <?php foreach ($procesos_admision as $proceso): ?>
                                        <option value="<?= $proceso['id'] ?>">
                                            <?= htmlspecialchars($proceso['nombre']) ?> - <?= $proceso['anio_academico'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="REGISTRADA">Registrada</option>
                                    <option value="EN_EVALUACION">En Evaluación</option>
                                    <option value="ADMITIDO">Admitido</option>
                                    <option value="LISTA_ESPERA">Lista de Espera</option>
                                    <option value="NO_ADMITIDO">No Admitido</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Grado Solicitado</label>
                                <select class="form-select" id="filtroGrado">
                                    <option value="">Todos los grados</option>
                                    <?php 
                                    $grados_unicos = array_unique(array_column($postulaciones, 'grado_solicitado'));
                                    sort($grados_unicos);
                                    foreach ($grados_unicos as $grado): 
                                    ?>
                                        <option value="<?= $grado ?>"><?= htmlspecialchars($grado) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Buscar</label>
                                <input type="text" class="form-control" id="buscarPostulacion" 
                                       placeholder="Buscar por nombre o código...">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-1"></i> Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Postulaciones -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffd3a5 0%, #fdab7d 100%);">
                        <h5 class="mb-0" style="color: #7d3c00;">
                            <i class="ti ti-list me-2"></i>
                            Lista de Postulaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaPostulaciones">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th>Código</th>
                                        <th>Postulante</th>
                                        <th>Grado</th>
                                        <th>Proceso</th>
                                        <th>Apoderado</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postulaciones as $postulacion): 
                                        $datos_postulante = json_decode($postulacion['datos_postulante'], true) ?: [];
                                        $datos_apoderado = json_decode($postulacion['datos_apoderado'], true) ?: [];
                                        $documentos = json_decode($postulacion['documentos'], true) ?: [];
                                        $evaluaciones = json_decode($postulacion['evaluaciones'], true) ?: [];
                                    ?>
                                        <tr data-proceso="<?= $postulacion['proceso_id'] ?>" 
                                            data-estado="<?= $postulacion['estado'] ?>"
                                            data-grado="<?= htmlspecialchars($postulacion['grado_solicitado']) ?>">
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($postulacion['codigo_postulacion'] ?: 'Sin código') ?></span>
                                            </td>
                                            <td>
                                                <div class="postulante-info">
                                                    <div class="postulante-nombre">
                                                        <?= htmlspecialchars($datos_postulante['nombres'] ?? 'Sin nombre') ?> 
                                                        <?= htmlspecialchars($datos_postulante['apellidos'] ?? '') ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        DNI: <?= htmlspecialchars($datos_postulante['documento_numero'] ?? 'N/A') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="grado-badge">
                                                    <?= htmlspecialchars($postulacion['grado_solicitado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="proceso-info">
                                                    <strong><?= htmlspecialchars($postulacion['proceso_nombre']) ?></strong><br>
                                                    <small>Año: <?= $postulacion['anio_academico'] ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="apoderado-info">
                                                    <strong><?= htmlspecialchars($datos_apoderado['nombres'] ?? 'Sin apoderado') ?></strong><br>
                                                    <small><?= htmlspecialchars($datos_apoderado['telefono'] ?? 'Sin teléfono') ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $estado_colors = [
                                                    'REGISTRADA' => 'bg-secondary',
                                                    'EN_EVALUACION' => 'bg-warning',
                                                    'ADMITIDO' => 'bg-success',
                                                    'LISTA_ESPERA' => 'bg-info',
                                                    'NO_ADMITIDO' => 'bg-danger'
                                                ];
                                                $estado_class = $estado_colors[$postulacion['estado']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge estado-badge <?= $estado_class ?>">
                                                    <?= str_replace('_', ' ', $postulacion['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($postulacion['fecha_postulacion'])) ?></small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetallePostulacion(<?= $postulacion['id'] ?>)" 
                                                            title="Ver Detalle">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="evaluarPostulacion(<?= $postulacion['id'] ?>)" 
                                                            title="Evaluar">
                                                        <i class="ti ti-clipboard-check"></i>
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

                <!-- Distribución por Grado -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header" style="background: linear-gradient(135deg, #a8e6cf 0%, #dcedc1 100%);">
                                <h6 class="mb-0" style="color: #2d5016;">
                                    <i class="ti ti-chart-bar me-2"></i>
                                    Distribución por Grado Solicitado
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($grados_count as $grado => $count): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center p-3" 
                                                 style="background-color: #f0f8ff; border-radius: 8px;">
                                                <span class="fw-semibold"><?= htmlspecialchars($grado) ?></span>
                                                <span class="badge bg-primary"><?= $count ?></span>
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

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/postulaciones/modal_nueva.php'; ?>
    <?php include 'modales/postulaciones/modal_evaluar.php'; ?>
    <?php include 'modales/postulaciones/modal_detalle.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaPostulaciones;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaPostulaciones = $('#tablaPostulaciones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[6, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7] }
                ]
            });

            // Filtros personalizados
            $('#filtroProceso, #filtroEstado, #filtroGrado').on('change', aplicarFiltros);
            $('#buscarPostulacion').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const procesoFiltro = $('#filtroProceso').val();
            const estadoFiltro = $('#filtroEstado').val();
            const gradoFiltro = $('#filtroGrado').val();
            const busqueda = $('#buscarPostulacion').val().toLowerCase();

            $('#tablaPostulaciones tbody tr').each(function() {
                const fila = $(this);
                const proceso = fila.data('proceso');
                const estado = fila.data('estado');
                const grado = fila.data('grado');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (procesoFiltro && proceso != procesoFiltro) mostrar = false;
                if (estadoFiltro && estado !== estadoFiltro) mostrar = false;
                if (gradoFiltro && grado !== gradoFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroProceso, #filtroEstado, #filtroGrado').val('');
            $('#buscarPostulacion').val('');
            aplicarFiltros();
        }

        function verDetallePostulacion(id) {
            mostrarCarga();
            
            fetch('modales/postulaciones/procesar_postulaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDetallePostulacion(data.postulacion);
                    $('#modalDetallePostulacion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener detalle');
            });
        }

        function evaluarPostulacion(id) {
            mostrarCarga();
            
            fetch('modales/postulaciones/procesar_postulaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_detalle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEvaluacion(data.postulacion);
                    $('#modalEvaluarPostulacion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar datos para evaluación');
            });
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function mostrarExito(mensaje) {
            Swal.fire({
                title: '¡Éxito!',
                text: mensaje,
                icon: 'success',
                confirmButtonColor: '#28a745',
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