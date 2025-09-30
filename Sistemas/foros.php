<?php 
    require_once 'conexion/bd.php';

    // Obtener cursos disponibles
    try {
        $sql_cursos = "SELECT c.*, 
                    CONCAT(ac.nombre, ' - ', s.grado, ' ', s.seccion) as nombre_completo,
                    d.nombres as docente_nombres,
                    d.apellidos as docente_apellidos,
                    COUNT(f.id) as total_foros
                FROM cursos c
                INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                INNER JOIN docentes d ON ad.docente_id = d.id
                LEFT JOIN foros f ON c.id = f.curso_id
                WHERE c.configuraciones->>'$.estado' = 'ACTIVO'
                GROUP BY c.id
                ORDER BY ac.nombre ASC, s.grado ASC";
        
        $stmt_cursos = $conexion->prepare($sql_cursos);
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cursos = [];
        $error_cursos = "Error al cargar cursos: " . $e->getMessage();
    }

    // Obtener foros con información completa
    try {
        $sql = "SELECT f.*, 
                    c.nombre as curso_nombre,
                    c.codigo_curso,
                    u.nombres as creador_nombres,
                    u.apellidos as creador_apellidos,
                    CONCAT(ac.nombre, ' - ', s.grado, ' ', s.seccion) as curso_completo
                FROM foros f
                INNER JOIN cursos c ON f.curso_id = c.id
                INNER JOIN usuarios u ON f.usuario_creacion = u.id
                INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                ORDER BY f.fecha_creacion DESC";
        
        $stmt_foros = $conexion->prepare($sql);
        $stmt_foros->execute();
        $foros = $stmt_foros->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $foros = [];
        $error_foros = "Error al cargar foros: " . $e->getMessage();
    }

    // Calcular estadísticas generales
    $total_foros = count($foros);
    $foros_abiertos = 0;
    $foros_cerrados = 0;
    $total_mensajes = 0;
    $total_participantes = 0;

    foreach ($foros as $foro) {
        $config = json_decode($foro['configuraciones'], true) ?: [];
        $stats = json_decode($foro['estadisticas'], true) ?: [];
        $mensajes = json_decode($foro['mensajes'], true) ?: [];
        
        if (($config['estado'] ?? 'ABIERTO') === 'ABIERTO') {
            $foros_abiertos++;
        } else {
            $foros_cerrados++;
        }
        
        $total_mensajes += $stats['total_mensajes'] ?? count($mensajes);
        $total_participantes += $stats['participantes'] ?? 0;
    }

    // Estadísticas por curso
    $stats_por_curso = [];
    foreach ($foros as $foro) {
        $curso_id = $foro['curso_id'];
        if (!isset($stats_por_curso[$curso_id])) {
            $stats_por_curso[$curso_id] = [
                'nombre' => $foro['curso_completo'],
                'total' => 0,
                'mensajes' => 0
            ];
        }
        $stats_por_curso[$curso_id]['total']++;
        
        $stats = json_decode($foro['estadisticas'], true) ?: [];
        $stats_por_curso[$curso_id]['mensajes'] += $stats['total_mensajes'] ?? 0;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Foros - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
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

      .foro-card {
        border-left: 4px solid #667eea;
        margin-bottom: 1rem;
      }

      .mensaje-count {
        font-size: 1.5rem;
        font-weight: 600;
        color: #667eea;
      }

      .participante-count {
        font-size: 1.1rem;
        color: #6c757d;
      }

      .estado-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
      }

      .tipo-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
      }

      .mensaje-preview {
        max-height: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
      }

      .curso-info {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 0.5rem;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
      }

      .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
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

      .mensaje-tree {
        border-left: 2px solid #e9ecef;
        padding-left: 1rem;
        margin-left: 1rem;
      }

      .mensaje-item {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
      }

      .mensaje-autor {
        font-weight: 600;
        color: #495057;
      }

      .mensaje-fecha {
        font-size: 0.75rem;
        color: #6c757d;
      }

      .tabla-responsive-custom {
        overflow-x: auto;
      }

      @media (max-width: 768px) {
        .stats-card {
          margin-bottom: 1rem;
        }
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
                                <h4 class="fw-bold mb-0">Gestión de Foros</h4>
                                <p class="mb-0 text-muted">Administra espacios de discusión y participación estudiantil</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearForo">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Foro
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
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 opacity-75">Total Foros</h6>
                                        <h2 class="mb-0 mt-2"><?= $total_foros ?></h2>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="ti ti-message-circle-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 opacity-75">Foros Abiertos</h6>
                                        <h2 class="mb-0 mt-2"><?= $foros_abiertos ?></h2>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="ti ti-lock-open"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 opacity-75">Total Mensajes</h6>
                                        <h2 class="mb-0 mt-2"><?= $total_mensajes ?></h2>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="ti ti-messages"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 opacity-75">Participantes</h6>
                                        <h2 class="mb-0 mt-2"><?= $total_participantes ?></h2>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="ti ti-users"></i>
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
                            <div class="col-md-4">
                                <label class="form-label">Curso</label>
                                <select class="form-select" id="filtroCurso">
                                    <option value="">Todos los cursos</option>
                                    <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nombre_completo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="ABIERTO">Abiertos</option>
                                    <option value="CERRADO">Cerrados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarForo" placeholder="Buscar por título o descripción...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarForos()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Foros -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Foros</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaForos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Foro</th>
                                        <th>Curso</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Mensajes</th>
                                        <th class="text-center">Participantes</th>
                                        <th>Último Mensaje</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($foros as $foro): 
                                        $config = json_decode($foro['configuraciones'], true) ?: [];
                                        $stats = json_decode($foro['estadisticas'], true) ?: [];
                                        $mensajes = json_decode($foro['mensajes'], true) ?: [];
                                        
                                        $estado = $config['estado'] ?? 'ABIERTO';
                                        $tipo = $config['tipo'] ?? 'GENERAL';
                                        $moderado = $config['moderado'] ?? false;
                                        
                                        $total_msgs = $stats['total_mensajes'] ?? count($mensajes);
                                        $participantes = $stats['participantes'] ?? 0;
                                        $ultimo_mensaje = $stats['mensaje_mas_reciente'] ?? null;
                                    ?>
                                        <tr data-curso="<?= $foro['curso_id'] ?>" data-estado="<?= $estado ?>">
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                                            <i class="ti ti-message-circle text-primary fs-5"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1"><?= htmlspecialchars($foro['titulo']) ?></h6>
                                                        <p class="text-muted mb-0 small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            <?= htmlspecialchars($foro['descripcion']) ?>
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="ti ti-user me-1"></i>
                                                            <?= htmlspecialchars($foro['creador_nombres'] . ' ' . $foro['creador_apellidos']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold"><?= htmlspecialchars($foro['curso_nombre']) ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($foro['codigo_curso']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-info badge-sm"><?= $tipo ?></span>
                                                    <?php if ($moderado): ?>
                                                        <span class="badge bg-warning badge-sm">
                                                            <i class="ti ti-shield-check"></i> Moderado
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="fs-4 fw-bold text-primary"><?= $total_msgs ?></span>
                                                    <small class="text-muted">mensajes</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="fs-5 fw-semibold text-secondary"><?= $participantes ?></span>
                                                    <small class="text-muted">usuarios</small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($ultimo_mensaje): ?>
                                                    <small class="text-muted">
                                                        <i class="ti ti-clock me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($ultimo_mensaje)) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">Sin mensajes</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $estado === 'ABIERTO' ? 'bg-success' : 'bg-secondary' ?> badge-sm">
                                                    <i class="ti <?= $estado === 'ABIERTO' ? 'ti-lock-open' : 'ti-lock' ?> me-1"></i>
                                                    <?= $estado ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="verMensajes(<?= $foro['id'] ?>)" 
                                                            title="Ver Mensajes"
                                                            data-bs-toggle="tooltip">
                                                        <i class="ti ti-messages"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarForo(<?= $foro['id'] ?>)" 
                                                            title="Editar Foro"
                                                            data-bs-toggle="tooltip">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-<?= $estado === 'ABIERTO' ? 'warning' : 'success' ?>" 
                                                            onclick="toggleEstadoForo(<?= $foro['id'] ?>, '<?= $estado === 'ABIERTO' ? 'CERRADO' : 'ABIERTO' ?>')" 
                                                            title="<?= $estado === 'ABIERTO' ? 'Cerrar' : 'Abrir' ?> Foro"
                                                            data-bs-toggle="tooltip">
                                                        <i class="ti <?= $estado === 'ABIERTO' ? 'ti-lock' : 'ti-lock-open' ?>"></i>
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

                <!-- Estadísticas por Curso -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución de Foros por Curso</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Curso</th>
                                                <th class="text-center">Total Foros</th>
                                                <th class="text-center">Total Mensajes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats_por_curso as $stat): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($stat['nombre']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary"><?= $stat['total'] ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info"><?= $stat['mensajes'] ?></span>
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
        </div>
    </div>

    <?php include 'modales/foros/modal_crear_foro.php'; ?>
    <?php include 'modales/foros/modal_ver_mensajes.php'; ?>
    <?php include 'modales/foros/modal_editar_foro.php'; ?>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>


<script>
    let tablaForos;
    const cursosDisponibles = <?= json_encode($cursos) ?>;

    $(document).ready(function() {
        // Inicializar DataTable
        tablaForos = $('#tablaForos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[5, 'desc']], // Ordenar por último mensaje
            columnDefs: [
                { orderable: false, targets: [7] }, // Columna de acciones no ordenable
                { className: 'text-center', targets: [3, 4, 7] }
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            responsive: true,
            drawCallback: function() {
                // Reinicializar tooltips después de cada redibujado
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        // Filtros personalizados
        $('#filtroCurso, #filtroEstado').on('change', aplicarFiltros);
        $('#buscarForo').on('keyup', aplicarFiltros);

        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    function aplicarFiltros() {
        const cursoFiltro = $('#filtroCurso').val();
        const estadoFiltro = $('#filtroEstado').val();
        const busqueda = $('#buscarForo').val();

        // Limpiar búsqueda de DataTable
        tablaForos.search('').draw();

        // Aplicar filtros personalizados
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = tablaForos.row(dataIndex).node();
                const curso = $(row).data('curso');
                const estado = $(row).data('estado');

                let mostrar = true;

                if (cursoFiltro && curso != cursoFiltro) {
                    mostrar = false;
                }

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                return mostrar;
            }
        );

        // Aplicar búsqueda de texto
        if (busqueda) {
            tablaForos.search(busqueda);
        }

        tablaForos.draw();

        // Limpiar filtros personalizados
        $.fn.dataTable.ext.search.pop();
    }

    function limpiarFiltros() {
        $('#filtroCurso, #filtroEstado').val('');
        $('#buscarForo').val('');
        tablaForos.search('').draw();
    }

    function mostrarCarga() {
        $('#loadingOverlay').css('display', 'flex');
    }

    function ocultarCarga() {
        $('#loadingOverlay').hide();
    }

    function verMensajes(id) {
        // Se implementará con el modal
        mostrarCarga();
        console.log('Ver mensajes foro:', id);
        // Aquí irá la carga del modal de mensajes
        setTimeout(() => {
            ocultarCarga();
        }, 500);
    }

    function editarForo(id) {
        // Se implementará con el modal
        mostrarCarga();
        console.log('Editar foro:', id);
        // Aquí irá la carga del modal de edición
        setTimeout(() => {
            ocultarCarga();
        }, 500);
    }

    function toggleEstadoForo(id, nuevoEstado) {
        const accion = nuevoEstado === 'ABIERTO' ? 'abrir' : 'cerrar';
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accion} este foro?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: nuevoEstado === 'ABIERTO' ? '#198754' : '#fd7e14',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, ' + accion,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarToggleEstadoForo(id, nuevoEstado);
            }
        });
    }

    function ejecutarToggleEstadoForo(id, estado) {
        mostrarCarga();

        fetch('modales/foros/procesar_foros.php', {
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
            mostrarError('Error al cambiar estado del foro');
        });
    }

    function exportarForos() {
        window.open('reportes/exportar_foros.php', '_blank');
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