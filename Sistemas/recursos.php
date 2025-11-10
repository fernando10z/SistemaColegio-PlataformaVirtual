<?php 
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
    require_once 'conexion/bd.php';

    // Obtener recursos con información completa
    try {
        $sql = "SELECT r.*, 
                    u.username as usuario_nombre,
                    COUNT(DISTINCT cr.curso_id) as cursos_vinculados,
                    COUNT(DISTINCT lr.leccion_id) as lecciones_vinculadas
                FROM recursos r
                LEFT JOIN usuarios u ON r.usuario_creacion = u.id
                LEFT JOIN curso_recursos cr ON r.id = cr.recurso_id
                LEFT JOIN leccion_recursos lr ON r.id = lr.recurso_id
                GROUP BY r.id
                ORDER BY r.activo DESC, r.fecha_creacion DESC";
        
        $stmt_recursos = $conexion->prepare($sql);
        $stmt_recursos->execute();
        $recursos = $stmt_recursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $recursos = [];
        $error_recursos = "Error al cargar recursos: " . $e->getMessage();
    }

    // Obtener cursos para filtros y vinculación
    try {
        $stmt_cursos = $conexion->prepare("
            SELECT c.id, c.nombre, c.codigo_curso, 
                   s.grado, s.seccion, ac.nombre as area_nombre
            FROM cursos c
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN secciones s ON ad.seccion_id = s.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            ORDER BY s.grado ASC, s.seccion ASC, ac.nombre ASC
        ");
        $stmt_cursos->execute();
        $cursos_disponibles = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cursos_disponibles = [];
    }

    // Calcular estadísticas
    $total_recursos = count($recursos);
    $recursos_activos = count(array_filter($recursos, function($r) { return $r['activo']; }));
    $recursos_inactivos = $total_recursos - $recursos_activos;
    
    // Estadísticas por tipo
    $tipos_count = [
        'VIDEO' => 0,
        'PDF' => 0,
        'IMAGEN' => 0,
        'AUDIO' => 0,
        'ENLACE' => 0,
        'DOCUMENTO' => 0,
        'PRESENTACION' => 0,
        'OTRO' => 0
    ];
    
    foreach ($recursos as $recurso) {
        $tipo = $recurso['tipo'] ?? 'OTRO';
        if (isset($tipos_count[$tipo])) {
            $tipos_count[$tipo]++;
        } else {
            $tipos_count['OTRO']++;
        }
    }

    // Calcular tamaño total
    $tamano_total = 0;
    foreach ($recursos as $recurso) {
        $metadata = json_decode($recurso['metadata'], true);
        $tamano_total += $metadata['tamano_bytes'] ?? 0;
    }
    $tamano_total_mb = round($tamano_total / 1048576, 2);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Recursos - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
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
        
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .recurso-preview {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .recurso-info {
            line-height: 1.3;
        }
        
        .recurso-titulo {
            font-weight: 600;
            color: #495057;
        }
        
        .recurso-tipo {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .tipo-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .stats-card {
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
        
        .vinculacion-info {
            text-align: center;
        }
        
        .vinculacion-numero {
            font-weight: 600;
            color: #495057;
            font-size: 1.1rem;
        }
        
        .file-icon {
            font-size: 2rem;
            margin-right: 0.5rem;
        }
        
        .metadata-info {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-size: 0.8rem;
        }
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

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

        .left-sidebar > div {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            margin: 0 !important;
            padding: 0 !important;
        }

        .left-sidebar .brand-logo {
            flex-shrink: 0;
            padding: 20px 24px;
            margin: 0 !important;
            border-bottom: 1px solid #e9ecef;
        }

        .left-sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 !important;
            padding: 0 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .card {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper" style="top: 20px;">
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
                                <h4 class="fw-bold mb-0">Gestión de Recursos</h4>
                                <p class="mb-0 text-muted">Administra archivos, videos, enlaces y materiales de apoyo</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarRecurso">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Recurso
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
                                        <i class="ti ti-file"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-white">Total Recursos</h6>
                                        <h3 class="mb-0 text-white fw-bold"><?= $total_recursos ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-check"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Activos</h6>
                                        <h3 class="mb-0 fw-bold"><?= $recursos_activos ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-database"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Espacio Usado</h6>
                                        <h3 class="mb-0 fw-bold"><?= $tamano_total_mb ?> MB</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="ti ti-video"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Videos</h6>
                                        <h3 class="mb-0 fw-bold"><?= $tipos_count['VIDEO'] ?></h3>
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
                                <label class="form-label">Tipo de Recurso</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="VIDEO">Videos</option>
                                    <option value="PDF">PDFs</option>
                                    <option value="IMAGEN">Imágenes</option>
                                    <option value="AUDIO">Audios</option>
                                    <option value="ENLACE">Enlaces</option>
                                    <option value="DOCUMENTO">Documentos</option>
                                    <option value="PRESENTACION">Presentaciones</option>
                                    <option value="OTRO">Otros</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Curso</label>
                                <select class="form-select" id="filtroCurso">
                                    <option value="">Todos los cursos</option>
                                    <?php foreach ($cursos_disponibles as $curso): ?>
                                        <option value="<?= $curso['id'] ?>">
                                            <?= htmlspecialchars($curso['area_nombre']) ?> - 
                                            <?= htmlspecialchars($curso['grado']) ?><?= htmlspecialchars($curso['seccion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarRecurso" 
                                       placeholder="Buscar por título, descripción...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Recursos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Recursos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaRecursos">
                                <thead class="table-light">
                                    <tr>
                                        <th>Recurso</th>
                                        <th>Tipo</th>
                                        <th>Información</th>
                                        <th>Vinculaciones</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recursos as $recurso): 
                                        $metadata = json_decode($recurso['metadata'], true) ?: [];
                                        $tipo_icono = [
                                            'VIDEO' => 'ti-video',
                                            'PDF' => 'ti-file-text',
                                            'IMAGEN' => 'ti-photo',
                                            'AUDIO' => 'ti-music',
                                            'ENLACE' => 'ti-link',
                                            'DOCUMENTO' => 'ti-file-description',
                                            'PRESENTACION' => 'ti-presentation',
                                            'OTRO' => 'ti-file'
                                        ];
                                        $icono = $tipo_icono[$recurso['tipo']] ?? 'ti-file';
                                    ?>
                                        <tr data-tipo="<?= $recurso['tipo'] ?>" 
                                            data-estado="<?= $recurso['activo'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti <?= $icono ?> file-icon text-primary"></i>
                                                    <div class="recurso-info">
                                                        <div class="recurso-titulo">
                                                            <?= htmlspecialchars($recurso['titulo']) ?>
                                                        </div>
                                                        <div class="recurso-tipo">
                                                            <?= htmlspecialchars($recurso['descripcion'] ?: 'Sin descripción') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge tipo-badge bg-primary">
                                                    <?= $recurso['tipo'] ?>
                                                </span>
                                                <?php if ($recurso['publico']): ?>
                                                    <br><span class="badge tipo-badge bg-success mt-1">Público</span>
                                                <?php else: ?>
                                                    <br><span class="badge tipo-badge bg-secondary mt-1">Privado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="metadata-info">
                                                    <?php if (!empty($metadata['tamano_bytes'])): ?>
                                                        <div>
                                                            <i class="ti ti-database"></i>
                                                            <?= round($metadata['tamano_bytes'] / 1048576, 2) ?> MB
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($metadata['duracion'])): ?>
                                                        <div>
                                                            <i class="ti ti-clock"></i>
                                                            <?= $metadata['duracion'] ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="text-muted mt-1">
                                                        <small>Por: <?= htmlspecialchars($recurso['usuario_nombre']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="vinculacion-info">
                                                    <div class="vinculacion-numero">
                                                        <?= $recurso['cursos_vinculados'] + $recurso['lecciones_vinculadas'] ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $recurso['cursos_vinculados'] ?> cursos, 
                                                        <?= $recurso['lecciones_vinculadas'] ?> lecciones
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $recurso['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $recurso['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarRecurso(<?= $recurso['id'] ?>)" 
                                                            title="Editar">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verRecurso('<?= htmlspecialchars($recurso['url']) ?>', '<?= $recurso['tipo'] ?>')" 
                                                            title="Ver/Descargar">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $recurso['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoRecurso(<?= $recurso['id'] ?>, <?= $recurso['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $recurso['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="ti <?= $recurso['activo'] ? 'ti-toggle-right' : 'ti-toggle-left' ?>"></i>
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

                <!-- Distribución por Tipo -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Tipo de Recurso</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($tipos_count as $tipo => $count): ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><?= $tipo ?></span>
                                                <span class="badge bg-primary"><?= $count ?></span>
                                            </div>
                                            <div class="progress mt-2" style="height: 5px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $total_recursos > 0 ? ($count / $total_recursos * 100) : 0 ?>%">
                                                </div>
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

    <?php include 'modales/recursos/modal_agregar.php'; ?>
    <?php include 'modales/recursos/modal_editar.php'; ?>
    <?php include 'modales/recursos/modal_vincular.php'; ?>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaRecursos;

        $(document).ready(function() {
            tablaRecursos = $('#tablaRecursos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [5] }
                ]
            });

            $('#filtroTipo, #filtroEstado, #filtroCurso').on('change', aplicarFiltros);
            $('#buscarRecurso').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const tipoFiltro = $('#filtroTipo').val();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarRecurso').val().toLowerCase();

            $('#tablaRecursos tbody tr').each(function() {
                const fila = $(this);
                const tipo = fila.data('tipo');
                const estado = fila.data('estado');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                if (tipoFiltro && tipo !== tipoFiltro) {
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

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarRecurso(id) {
            mostrarCarga();
            
            fetch('modales/recursos/procesar_recursos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionRecurso(data.recurso);
                    $('#modalEditarRecurso').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del recurso');
            });
        }

        function verRecurso(url, tipo) {
            if (tipo === 'ENLACE') {
                window.open(url, '_blank');
            } else {
                window.open(url, '_blank');
            }
        }

        function toggleEstadoRecurso(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este recurso?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstadoRecurso(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstadoRecurso(id, estado) {
            mostrarCarga();

            fetch('modales/recursos/procesar_recursos.php', {
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
                mostrarError('Error al cambiar estado del recurso');
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