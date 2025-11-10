<?php 
    session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
    
    require_once 'conexion/bd.php';

    // Obtener lecciones con información completa
    try {
        $sql = "SELECT l.*, 
                    u.titulo as unidad_titulo,
                    c.nombre as curso_nombre,
                    COUNT(DISTINCT pe.id) as total_estudiantes_progreso,
                    AVG(pe.progreso) as progreso_promedio
                FROM lecciones l
                INNER JOIN unidades u ON l.unidad_id = u.id
                INNER JOIN cursos c ON u.curso_id = c.id
                LEFT JOIN progreso_estudiantes pe ON l.id = pe.leccion_id
                GROUP BY l.id
                ORDER BY u.orden ASC, l.orden ASC";
        
        $stmt_lecciones = $conexion->prepare($sql);
        $stmt_lecciones->execute();
        $lecciones = $stmt_lecciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $lecciones = [];
        $error_lecciones = "Error al cargar lecciones: " . $e->getMessage();
    }

    // Obtener unidades para filtros y selección
    try {
        $sql_unidades = "SELECT u.*, c.nombre as curso_nombre 
                        FROM unidades u
                        INNER JOIN cursos c ON u.curso_id = c.id
                        ORDER BY c.nombre ASC, u.orden ASC";
        $stmt_unidades = $conexion->prepare($sql_unidades);
        $stmt_unidades->execute();
        $unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $unidades = [];
    }

    // Obtener cursos para filtros
    try {
        $stmt_cursos = $conexion->prepare("SELECT * FROM cursos ORDER BY nombre ASC");
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cursos = [];
    }

    // Calcular estadísticas
    $total_lecciones = count($lecciones);
    $lecciones_publicadas = count(array_filter($lecciones, function($l) { 
        $config = json_decode($l['configuraciones'], true);
        return isset($config['estado']) && $config['estado'] === 'PUBLICADO'; 
    }));
    $lecciones_borrador = $total_lecciones - $lecciones_publicadas;
    
    // Estadísticas por tipo
    $tipos_count = [
        'CONTENIDO' => 0,
        'ACTIVIDAD' => 0,
        'EVALUACION' => 0
    ];
    foreach ($lecciones as $leccion) {
        $tipos_count[$leccion['tipo']]++;
    }

    // Promedio general de progreso
    $progreso_general = 0;
    $count_progreso = 0;
    foreach ($lecciones as $leccion) {
        if ($leccion['progreso_promedio'] > 0) {
            $progreso_general += $leccion['progreso_promedio'];
            $count_progreso++;
        }
    }
    $progreso_general = $count_progreso > 0 ? round($progreso_general / $count_progreso, 2) : 0;
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Lecciones - ANDRÉS AVELINO CÁCERES</title>
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

      .leccion-card {
        border-left: 4px solid #dee2e6;
        margin-bottom: 1rem;
      }
      
      .leccion-card.tipo-contenido { border-left-color: #0d6efd; }
      .leccion-card.tipo-actividad { border-left-color: #198754; }
      .leccion-card.tipo-evaluacion { border-left-color: #dc3545; }
      
      .orden-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
      }
      
      .tipo-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
      }
      
      .estado-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.7rem;
      }
      
      .progreso-wrapper {
        position: relative;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
      }
      
      .progreso-bar {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
      }
      
      .tiempo-estimado {
        font-size: 0.8rem;
        color: #6c757d;
      }
      
      .recursos-count {
        font-size: 0.75rem;
        color: #0d6efd;
      }
      
      .leccion-titulo {
        font-weight: 600;
        color: #495057;
        line-height: 1.3;
      }
      
      .unidad-info {
        font-size: 0.85rem;
        color: #6c757d;
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
      
      .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
      }
      
      .stats-icon {
        font-size: 2.5rem;
        opacity: 0.8;
      }
      
      .content-preview {
        max-height: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.85rem;
        color: #6c757d;
      }
      
      .obligatorio-badge {
        background: #ffc107;
        color: #000;
      }
      
      .table-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Lecciones</h4>
                                <p class="mb-0 text-muted">Administra el contenido educativo de cada unidad</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarLeccion">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Lección
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
                                        <h3 class="fw-bold mb-1"><?= $total_lecciones ?></h3>
                                        <p class="mb-0 opacity-75">Total Lecciones</p>
                                    </div>
                                    <i class="ti ti-book stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-1 text-success"><?= $lecciones_publicadas ?></h3>
                                        <p class="mb-0 text-muted">Publicadas</p>
                                    </div>
                                    <i class="ti ti-circle-check text-success" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-1 text-warning"><?= $lecciones_borrador ?></h3>
                                        <p class="mb-0 text-muted">En Borrador</p>
                                    </div>
                                    <i class="ti ti-edit text-warning" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bold mb-1 text-info"><?= $progreso_general ?>%</h3>
                                        <p class="mb-0 text-muted">Progreso Promedio</p>
                                    </div>
                                    <i class="ti ti-progress text-info" style="font-size: 2.5rem;"></i>
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
                                    <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unidad</label>
                                <select class="form-select" id="filtroUnidad">
                                    <option value="">Todas las unidades</option>
                                    <?php foreach ($unidades as $unidad): ?>
                                        <option value="<?= $unidad['id'] ?>"><?= htmlspecialchars($unidad['titulo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="CONTENIDO">Contenido</option>
                                    <option value="ACTIVIDAD">Actividad</option>
                                    <option value="EVALUACION">Evaluación</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="PUBLICADO">Publicado</option>
                                    <option value="BORRADOR">Borrador</option>
                                </select>
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

                <!-- Lista de Lecciones -->
                <div class="row" id="leccionesContainer">
                    <?php foreach ($lecciones as $leccion): 
                        $configuraciones = json_decode($leccion['configuraciones'], true) ?: [];
                        $recursos = json_decode($leccion['recursos'], true) ?: [];
                        $estado = $configuraciones['estado'] ?? 'BORRADOR';
                        $tiempo_estimado = $configuraciones['tiempo_estimado'] ?? 0;
                        $obligatorio = $configuraciones['obligatorio'] ?? false;
                    ?>
                        <div class="col-md-6 mb-4 leccion-item" 
                             data-curso="<?= $leccion['curso_nombre'] ?>"
                             data-unidad="<?= $leccion['unidad_id'] ?>"
                             data-tipo="<?= $leccion['tipo'] ?>"
                             data-estado="<?= $estado ?>">
                            <div class="card leccion-card tipo-<?= strtolower($leccion['tipo']) ?> h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="orden-badge bg-light text-primary">
                                            <?= str_pad($leccion['orden'], 2, '0', STR_PAD_LEFT) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h5 class="leccion-titulo mb-1"><?= htmlspecialchars($leccion['titulo']) ?></h5>
                                                    <div class="unidad-info">
                                                        <i class="ti ti-folder me-1"></i>
                                                        <?= htmlspecialchars($leccion['curso_nombre']) ?> › <?= htmlspecialchars($leccion['unidad_titulo']) ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="tipo-badge badge <?php
                                                        switch($leccion['tipo']) {
                                                            case 'CONTENIDO': echo 'bg-primary'; break;
                                                            case 'ACTIVIDAD': echo 'bg-success'; break;
                                                            case 'EVALUACION': echo 'bg-danger'; break;
                                                        }
                                                    ?>"><?= $leccion['tipo'] ?></span>
                                                    <?php if ($obligatorio): ?>
                                                        <br><span class="badge obligatorio-badge mt-1">OBLIGATORIO</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($leccion['descripcion'])): ?>
                                                <p class="content-preview mb-2"><?= htmlspecialchars(substr($leccion['descripcion'], 0, 100)) ?>...</p>
                                            <?php endif; ?>

                                            <div class="d-flex gap-3 mb-3">
                                                <div class="tiempo-estimado">
                                                    <i class="ti ti-clock me-1"></i><?= $tiempo_estimado ?> min
                                                </div>
                                                <div class="recursos-count">
                                                    <i class="ti ti-paperclip me-1"></i><?= count($recursos) ?> recursos
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="ti ti-users me-1"></i><?= $leccion['total_estudiantes_progreso'] ?> estudiantes
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">Progreso promedio</small>
                                                    <small class="fw-bold">
                                                        <?= is_numeric($leccion['progreso_promedio']) ? round($leccion['progreso_promedio'], 1) : '0' ?>%
                                                    </small>                                               
                                                </div>
                                                <div class="progreso-wrapper">
                                                    <div class="progreso-bar" style="width: <?= $leccion['progreso_promedio'] ?>%"></div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="estado-badge badge <?= $estado === 'PUBLICADO' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= $estado ?>
                                                </span>
                                                <div class="table-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarLeccion(<?= $leccion['id'] ?>)" title="Editar">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verContenido(<?= $leccion['id'] ?>)" title="Ver Contenido">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="eliminarLeccion(<?= $leccion['id'] ?>)" title="Eliminar">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Distribución por Tipo -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Distribución por Tipo de Lección</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="border-end">
                                            <h3 class="text-primary"><?= $tipos_count['CONTENIDO'] ?></h3>
                                            <p class="text-muted mb-0">Contenido</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border-end">
                                            <h3 class="text-success"><?= $tipos_count['ACTIVIDAD'] ?></h3>
                                            <p class="text-muted mb-0">Actividades</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-danger"><?= $tipos_count['EVALUACION'] ?></h3>
                                        <p class="text-muted mb-0">Evaluaciones</p>
                                    </div>
                                </div>
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

    <!-- Incluir Modales -->
    <?php include 'modales/lecciones/modal_agregar.php'; ?>
    <?php include 'modales/lecciones/modal_editar.php'; ?>
    <?php include 'modales/lecciones/modal_contenido.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const unidadesData = <?= json_encode($unidades) ?>;

        $(document).ready(function() {
            // Filtros en tiempo real
            $('#filtroCurso, #filtroUnidad, #filtroTipo, #filtroEstado').on('change', aplicarFiltros);
        });

        function aplicarFiltros() {
            const cursoFiltro = $('#filtroCurso').val();
            const unidadFiltro = $('#filtroUnidad').val();
            const tipoFiltro = $('#filtroTipo').val();
            const estadoFiltro = $('#filtroEstado').val();

            $('.leccion-item').each(function() {
                const item = $(this);
                const curso = item.data('curso');
                const unidad = item.data('unidad').toString();
                const tipo = item.data('tipo');
                const estado = item.data('estado');

                let mostrar = true;

                if (cursoFiltro && !curso.includes(cursoFiltro)) mostrar = false;
                if (unidadFiltro && unidad !== unidadFiltro) mostrar = false;
                if (tipoFiltro && tipo !== tipoFiltro) mostrar = false;
                if (estadoFiltro && estado !== estadoFiltro) mostrar = false;

                item.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroCurso, #filtroUnidad, #filtroTipo, #filtroEstado').val('');
            $('.leccion-item').show();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarLeccion(id) {
            mostrarCarga();
            
            fetch('modales/lecciones/procesar_lecciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                if (data.success) {
                    cargarDatosEdicionLeccion(data.leccion);
                    $('#modalEditarLeccion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la lección');
            });
        }

        function verContenido(id) {
            mostrarCarga();
            
            fetch('modales/lecciones/procesar_lecciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                if (data.success) {
                    mostrarContenidoLeccion(data.leccion);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar contenido');
            });
        }

        function mostrarContenidoLeccion(leccion) {
            $('#modalContenido').modal('show');
            // Cargar datos en el modal
            $('#contenidoTitulo').text(leccion.titulo);
            $('#contenidoDescripcion').text(leccion.descripcion);
            $('#contenidoHTML').html(leccion.contenido || '<p class="text-muted">Sin contenido</p>');
        }

        function eliminarLeccion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarEliminacion(id);
                }
            });
        }

        function ejecutarEliminacion(id) {
            mostrarCarga();

            fetch('modales/lecciones/procesar_lecciones.php', {
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
                mostrarError('Error al eliminar lección');
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