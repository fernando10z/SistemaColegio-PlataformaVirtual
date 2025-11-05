<?php 
    require_once 'conexion/bd.php';

    // Obtener anuncios con información completa
    try {
        $sql = "SELECT a.*, 
                    c.nombre as curso_nombre,
                    c.codigo_curso,
                    u.nombres as creador_nombres,
                    u.apellidos as creador_apellidos,
                    COUNT(DISTINCT JSON_EXTRACT(a.configuraciones, '$.destinatario')) as tipo_destinatarios
                FROM anuncios a
                INNER JOIN cursos c ON a.curso_id = c.id
                INNER JOIN usuarios u ON a.usuario_creacion = u.id
                GROUP BY a.id
                ORDER BY a.fecha_publicacion DESC";
        
        $stmt_anuncios = $conexion->prepare($sql);
        $stmt_anuncios->execute();
        $anuncios = $stmt_anuncios->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $anuncios = [];
        $error_anuncios = "Error al cargar anuncios: " . $e->getMessage();
    }

    // Obtener cursos activos para filtros y selección
    try {
        $stmt_cursos = $conexion->prepare("
            SELECT c.*, 
                   s.grado, s.seccion,
                   ac.nombre as area_nombre
            FROM cursos c
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN secciones s ON ad.seccion_id = s.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            WHERE c.configuraciones->>'$.estado' = 'ACTIVO'
            ORDER BY s.grado ASC, s.seccion ASC, ac.nombre ASC
        ");
        $stmt_cursos->execute();
        $cursos_activos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cursos_activos = [];
    }

    // Calcular estadísticas
    $total_anuncios = count($anuncios);
    $anuncios_activos = count(array_filter($anuncios, function($a) { return $a['activo']; }));
    $anuncios_inactivos = $total_anuncios - $anuncios_activos;

    // Estadísticas por tipo
    $tipos_count = [];
    foreach ($anuncios as $anuncio) {
        $config = json_decode($anuncio['configuraciones'], true);
        $tipo = $config['tipo'] ?? 'INFORMATIVO';
        $tipos_count[$tipo] = ($tipos_count[$tipo] ?? 0) + 1;
    }

    // Estadísticas por prioridad
    $prioridades_count = [];
    foreach ($anuncios as $anuncio) {
        $config = json_decode($anuncio['configuraciones'], true);
        $prioridad = $config['prioridad'] ?? 'NORMAL';
        $prioridades_count[$prioridad] = ($prioridades_count[$prioridad] ?? 0) + 1;
    }

    // Anuncios por curso
    $anuncios_por_curso = [];
    foreach ($anuncios as $anuncio) {
        $curso = $anuncio['curso_nombre'];
        $anuncios_por_curso[$curso] = ($anuncios_por_curso[$curso] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Anuncios - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
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

      .anuncio-card {
        border-left: 4px solid #0d6efd;
        transition: all 0.3s ease;
      }

      .anuncio-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }

      .anuncio-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.375rem 0.375rem 0 0;
        padding: 1rem;
      }

      .badge-tipo {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
        font-weight: 600;
      }

      .badge-prioridad {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
        font-weight: 600;
      }

      .anuncio-contenido {
        max-height: 150px;
        overflow-y: auto;
        font-size: 0.9rem;
        line-height: 1.6;
        color: #495057;
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
      }

      .anuncio-footer {
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
      }

      .curso-badge {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 12px;
        font-weight: 600;
      }

      .creador-info {
        font-size: 0.8rem;
        color: #6c757d;
      }

      .fecha-info {
        font-size: 0.75rem;
        color: #6c757d;
      }

      .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
      }

      .stats-icon {
        font-size: 2.5rem;
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

      .anuncio-expiracion {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
      }

      .destinatario-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0.1rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Anuncios</h4>
                                <p class="mb-0 text-muted">Administra anuncios y comunicados para cursos</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarAnuncio">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Anuncio
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 text-white-50">Total Anuncios</h6>
                                    <h2 class="mb-0 fw-bold"><?= $total_anuncios ?></h2>
                                </div>
                                <i class="ti ti-speakerphone stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-white-50">Activos</h6>
                                        <h2 class="mb-0 fw-bold"><?= $anuncios_activos ?></h2>
                                    </div>
                                    <i class="ti ti-circle-check stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-white-50">Inactivos</h6>
                                        <h2 class="mb-0 fw-bold"><?= $anuncios_inactivos ?></h2>
                                    </div>
                                    <i class="ti ti-circle-x stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-white-50">Cursos con Anuncios</h6>
                                        <h2 class="mb-0 fw-bold"><?= count($anuncios_por_curso) ?></h2>
                                    </div>
                                    <i class="ti ti-book stats-icon"></i>
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
                                <label class="form-label">Curso</label>
                                <select class="form-select" id="filtroCurso">
                                    <option value="">Todos los cursos</option>
                                    <?php foreach ($cursos_activos as $curso): ?>
                                        <option value="<?= $curso['id'] ?>">
                                            <?= htmlspecialchars($curso['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="RECORDATORIO">Recordatorio</option>
                                    <option value="INFORMATIVO">Informativo</option>
                                    <option value="URGENTE">Urgente</option>
                                    <option value="EVENTO">Evento</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Prioridad</label>
                                <select class="form-select" id="filtroPrioridad">
                                    <option value="">Todas</option>
                                    <option value="ALTA">Alta</option>
                                    <option value="NORMAL">Normal</option>
                                    <option value="BAJA">Baja</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarAnuncio" placeholder="Buscar por título o contenido...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Anuncios -->
                <div class="row" id="listaAnuncios">
                    <?php foreach ($anuncios as $anuncio): 
                        $config = json_decode($anuncio['configuraciones'], true) ?: [];
                        $tipo = $config['tipo'] ?? 'INFORMATIVO';
                        $prioridad = $config['prioridad'] ?? 'NORMAL';
                        $destinatario = $config['destinatario'] ?? 'TODOS';
                        $fecha_expiracion = $config['fecha_expiracion'] ?? null;
                        
                        // Determinar color del borde según prioridad
                        $border_color = match($prioridad) {
                            'ALTA' => '#dc3545',
                            'NORMAL' => '#0d6efd',
                            'BAJA' => '#6c757d',
                            default => '#0d6efd'
                        };
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 anuncio-item" 
                             data-curso="<?= $anuncio['curso_id'] ?>"
                             data-tipo="<?= $tipo ?>"
                             data-prioridad="<?= $prioridad ?>"
                             data-estado="<?= $anuncio['activo'] ?>">
                            <div class="card anuncio-card h-100" style="border-left-color: <?= $border_color ?>;">
                                <div class="anuncio-header">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0 flex-grow-1"><?= htmlspecialchars($anuncio['titulo']) ?></h5>
                                        <span class="badge <?= $anuncio['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $anuncio['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge badge-tipo <?php
                                            echo match($tipo) {
                                                'RECORDATORIO' => 'bg-warning text-dark',
                                                'URGENTE' => 'bg-danger',
                                                'EVENTO' => 'bg-info',
                                                default => 'bg-primary'
                                            };
                                        ?>">
                                            <?= $tipo ?>
                                        </span>
                                        <span class="badge badge-prioridad <?php
                                            echo match($prioridad) {
                                                'ALTA' => 'bg-danger',
                                                'BAJA' => 'bg-secondary',
                                                default => 'bg-primary'
                                            };
                                        ?>">
                                            Prioridad: <?= $prioridad ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="curso-badge">
                                            <i class="ti ti-book me-1"></i>
                                            <?= htmlspecialchars($anuncio['curso_nombre']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="anuncio-contenido mb-3">
                                        <?= nl2br(htmlspecialchars($anuncio['contenido'])) ?>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="ti ti-users me-1"></i>
                                            Destinatario: <strong><?= $destinatario ?></strong>
                                        </small>
                                    </div>
                                    
                                    <?php if ($fecha_expiracion): ?>
                                        <div class="mb-2">
                                            <span class="badge anuncio-expiracion <?= strtotime($fecha_expiracion) < time() ? 'bg-danger' : 'bg-info' ?>">
                                                <i class="ti ti-clock me-1"></i>
                                                <?= strtotime($fecha_expiracion) < time() ? 'Expirado' : 'Expira' ?>: 
                                                <?= date('d/m/Y', strtotime($fecha_expiracion)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="anuncio-footer">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="creador-info">
                                            <i class="ti ti-user me-1"></i>
                                            <?= htmlspecialchars($anuncio['creador_nombres'] . ' ' . $anuncio['creador_apellidos']) ?>
                                        </div>
                                        <div class="fecha-info">
                                            <i class="ti ti-calendar me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($anuncio['fecha_publicacion'])) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" 
                                                onclick="editarAnuncio(<?= $anuncio['id'] ?>)" 
                                                title="Editar Anuncio">
                                            <i class="ti ti-edit"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-sm <?= $anuncio['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?> flex-fill" 
                                                onclick="toggleEstadoAnuncio(<?= $anuncio['id'] ?>, <?= $anuncio['activo'] ? 'false' : 'true' ?>)" 
                                                title="<?= $anuncio['activo'] ? 'Desactivar' : 'Activar' ?> Anuncio">
                                            <i class="ti <?= $anuncio['activo'] ? 'ti-eye-off' : 'ti-eye' ?>"></i> 
                                            <?= $anuncio['activo'] ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Distribución de Anuncios -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Tipo</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($tipos_count as $tipo => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= $tipo ?></span>
                                        <span class="badge bg-primary"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Prioridad</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($prioridades_count as $prioridad => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= $prioridad ?></span>
                                        <span class="badge bg-info"><?= $count ?></span>
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
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <?php include 'modales/anuncios/modal_agregar_anuncio.php'; ?>
    <?php include 'modales/anuncios/modal_editar_anuncio.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Filtros
            $('#filtroCurso, #filtroTipo, #filtroPrioridad, #filtroEstado').on('change', aplicarFiltros);
            $('#buscarAnuncio').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const cursoFiltro = $('#filtroCurso').val();
            const tipoFiltro = $('#filtroTipo').val();
            const prioridadFiltro = $('#filtroPrioridad').val();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarAnuncio').val().toLowerCase();

            $('.anuncio-item').each(function() {
                const item = $(this);
                const curso = item.data('curso').toString();
                const tipo = item.data('tipo');
                const prioridad = item.data('prioridad');
                const estado = item.data('estado');
                const texto = item.text().toLowerCase();

                let mostrar = true;

                if (cursoFiltro && curso !== cursoFiltro) mostrar = false;
                if (tipoFiltro && tipo !== tipoFiltro) mostrar = false;
                if (prioridadFiltro && prioridad !== prioridadFiltro) mostrar = false;
                if (estadoFiltro !== '' && estado != estadoFiltro) mostrar = false;
                if (busqueda && !texto.includes(busqueda)) mostrar = false;

                item.toggle(mostrar);
            });
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarAnuncio(id) {
            mostrarCarga();
            
            fetch('modales/anuncios/procesar_anuncios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionAnuncio(data.anuncio);
                    $('#modalEditarAnuncio').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del anuncio');
            });
        }

        function toggleEstadoAnuncio(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este anuncio?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstadoAnuncio(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstadoAnuncio(id, estado) {
            mostrarCarga();

            fetch('modales/anuncios/procesar_anuncios.php', {
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
                mostrarError('Error al cambiar estado del anuncio');
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