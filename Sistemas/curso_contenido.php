<?php 
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
    require_once 'conexion/bd.php';
    
    // Obtener ID del curso
    $curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$curso_id) {
        header('Location: cursos.php');
        exit;
    }

    // Obtener información del curso
    try {
        $sql_curso = "SELECT c.*, 
                      ad.docente_id,
                      d.nombres as docente_nombres, 
                      d.apellidos as docente_apellidos,
                      ac.nombre as area_nombre,
                      s.grado, s.seccion, s.codigo as seccion_codigo
                      FROM cursos c
                      INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
                      INNER JOIN docentes d ON ad.docente_id = d.id
                      INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                      INNER JOIN secciones s ON ad.seccion_id = s.id
                      WHERE c.id = ?";
        
        $stmt_curso = $conexion->prepare($sql_curso);
        $stmt_curso->execute([$curso_id]);
        $curso = $stmt_curso->fetch(PDO::FETCH_ASSOC);
        
        if (!$curso) {
            header('Location: cursos.php');
            exit;
        }
        
        $configuraciones = json_decode($curso['configuraciones'], true) ?: [];
        $estudiantes_inscritos = json_decode($curso['estudiantes_inscritos'], true) ?: [];
        
    } catch (PDOException $e) {
        die("Error al cargar curso: " . $e->getMessage());
    }

    // Obtener unidades del curso
    try {
        $sql_unidades = "SELECT u.*, 
                         COUNT(DISTINCT l.id) as total_lecciones
                         FROM unidades u
                         LEFT JOIN lecciones l ON u.id = l.unidad_id
                         WHERE u.curso_id = ?
                         GROUP BY u.id
                         ORDER BY u.orden ASC";
        
        $stmt_unidades = $conexion->prepare($sql_unidades);
        $stmt_unidades->execute([$curso_id]);
        $unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $unidades = [];
    }

    // Obtener recursos adicionales (tareas, cuestionarios, anuncios)
    try {
        // Tareas
        $stmt_tareas = $conexion->prepare("SELECT * FROM tareas WHERE curso_id = ? ORDER BY fecha_creacion DESC LIMIT 5");
        $stmt_tareas->execute([$curso_id]);
        $tareas = $stmt_tareas->fetchAll(PDO::FETCH_ASSOC);
        
        // Cuestionarios
        $stmt_cuestionarios = $conexion->prepare("SELECT * FROM cuestionarios WHERE curso_id = ? ORDER BY fecha_creacion DESC LIMIT 5");
        $stmt_cuestionarios->execute([$curso_id]);
        $cuestionarios = $stmt_cuestionarios->fetchAll(PDO::FETCH_ASSOC);
        
        // Anuncios
        $stmt_anuncios = $conexion->prepare("SELECT * FROM anuncios WHERE curso_id = ? AND activo = 1 ORDER BY fecha_publicacion DESC LIMIT 5");
        $stmt_anuncios->execute([$curso_id]);
        $anuncios = $stmt_anuncios->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $tareas = [];
        $cuestionarios = [];
        $anuncios = [];
    }

    // Estadísticas del curso
    $total_unidades = count($unidades);
    $total_lecciones = array_sum(array_column($unidades, 'total_lecciones'));
    $total_estudiantes = count($estudiantes_inscritos);
    $total_recursos = count($tareas) + count($cuestionarios) + count($anuncios);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contenido del Curso - <?= htmlspecialchars($curso['nombre']) ?></title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .body-wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        .curso-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .unidad-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .unidad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .leccion-item {
            padding: 0.75rem 1rem;
            border-left: 2px solid #e9ecef;
            margin-left: 1rem;
            transition: all 0.2s ease;
        }
        
        .leccion-item:hover {
            border-left-color: #667eea;
            background-color: #f8f9fa;
        }
        
        .recurso-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        . {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .leccion-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e7f3ff;
            color: #0d6efd;
        }
        
        .unidad-header {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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
                                    <a href="cursos.php" class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-arrow-left me-2"></i>Volver a Cursos
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </header>

                <!-- Curso Header -->
                <div class="curso-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2"><?= htmlspecialchars($curso['nombre']) ?></h3>
                            <p class="mb-0 opacity-75">
                                <i class="ti ti-user me-2"></i><?= htmlspecialchars($curso['docente_nombres'] . ' ' . $curso['docente_apellidos']) ?>
                                <span class="mx-2">|</span>
                                <i class="ti ti-school me-2"></i><?= htmlspecialchars($curso['area_nombre']) ?>
                                <span class="mx-2">|</span>
                                <i class="ti ti-users me-2"></i><?= $total_estudiantes ?> estudiantes
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalUnidad">
                                    <i class="ti ti-plus me-2"></i>Nueva Unidad
                                </button>
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAgregarRecurso">
                                    <i class="ti ti-file-plus me-2"></i>Nuevo Recurso
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
               <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #A8D8EA; margin-bottom: 0.5rem;">
                                <?= $total_unidades ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Unidades</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #FFB3BA; margin-bottom: 0.5rem;">
                                <?= $total_lecciones ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Lecciones</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #BAE1FF; margin-bottom: 0.5rem;">
                                <?= $total_recursos ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Recursos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #FFDDA1; margin-bottom: 0.5rem;">
                                <?= $total_estudiantes ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Estudiantes</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs de Contenido -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#unidades">
                            <i class="ti ti-book me-2"></i>Unidades y Lecciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#recursos">
                            <i class="ti ti-files me-2"></i>Recursos Adicionales
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Unidades -->
                    <div class="tab-pane fade show active" id="unidades">
                        <?php if (empty($unidades)): ?>
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                No hay unidades creadas. Comienza agregando tu primera unidad.
                            </div>
                        <?php else: ?>
                            <?php foreach ($unidades as $unidad): 
                                $config_unidad = json_decode($unidad['configuraciones'], true) ?: [];
                                
                                // Obtener lecciones de esta unidad
                                try {
                                    $stmt_lecciones = $conexion->prepare("SELECT * FROM lecciones WHERE unidad_id = ? ORDER BY orden ASC");
                                    $stmt_lecciones->execute([$unidad['id']]);
                                    $lecciones = $stmt_lecciones->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    $lecciones = [];
                                }
                            ?>
                                <div class="card unidad-card mb-3">
                                    <div class="card-body">
                                        <div class="unidad-header">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <span class="badge bg-primary me-2">Unidad <?= $unidad['orden'] ?></span>
                                                        <?= htmlspecialchars($unidad['titulo']) ?>
                                                    </h5>
                                                    <p class="mb-0 text-muted"><?= htmlspecialchars($unidad['descripcion'] ?: 'Sin descripción') ?></p>
                                                    <div class="mt-2">
                                                        <span class="badge <?= $config_unidad['estado'] === 'PUBLICADO' ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= $config_unidad['estado'] ?? 'BORRADOR' ?>
                                                        </span>
                                                        <?php if (isset($config_unidad['fecha_inicio'])): ?>
                                                            <small class="text-muted ms-2">
                                                                <i class="ti ti-calendar me-1"></i>
                                                                <?= date('d/m/Y', strtotime($config_unidad['fecha_inicio'])) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="agregarLeccion(<?= $unidad['id'] ?>)">
                                                        <i class="ti ti-plus"></i> Lección
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="editarUnidad(<?= $unidad['id'] ?>)">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarUnidad(<?= $unidad['id'] ?>)">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lecciones -->
                                        <div class="mt-3">
                                            <?php if (empty($lecciones)): ?>
                                                <div class="text-muted text-center py-3">
                                                    <i class="ti ti-file-off fs-5"></i>
                                                    <p class="mb-0">No hay lecciones en esta unidad</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($lecciones as $leccion): 
                                                    $config_leccion = json_decode($leccion['configuraciones'], true) ?: [];
                                                ?>
                                                    <div class="leccion-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center">
                                                                <div class="leccion-icon me-3">
                                                                    <i class="ti <?php
                                                                        switch($leccion['tipo']) {
                                                                            case 'CONTENIDO': echo 'ti-book';break;
                                                                            case 'ACTIVIDAD': echo 'ti-clipboard';break;
                                                                            case 'EVALUACION': echo 'ti-clipboard-check';break;
                                                                            default: echo 'ti-file';
                                                                        }
                                                                    ?>"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0"><?= htmlspecialchars($leccion['titulo']) ?></h6>
                                                                    <small class="text-muted">
                                                                        <?= $leccion['tipo'] ?>
                                                                        <?php if (isset($config_leccion['tiempo_estimado'])): ?>
                                                                            • <?= $config_leccion['tiempo_estimado'] ?> min
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" onclick="editarLeccion(<?= $leccion['id'] ?>)">
                                                                    <i class="ti ti-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" onclick="eliminarLeccion(<?= $leccion['id'] ?>)">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Recursos -->
                    <div class="tab-pane fade" id="recursos">
                        <div class="row">
                            <!-- Tareas -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-clipboard me-2"></i>Tareas</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($tareas)): ?>
                                            <p class="text-muted text-center">No hay tareas</p>
                                        <?php else: ?>
                                            <?php foreach ($tareas as $tarea): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <div class="d-flex justify-content-between">
                                                        <small><strong><?= htmlspecialchars($tarea['titulo']) ?></strong></small>
                                                        <span class="badge bg-<?= $tarea['estado'] === 'PUBLICADA' ? 'success' : 'secondary' ?>">
                                                            <?= $tarea['estado'] ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        <?= date('d/m/Y', strtotime($tarea['fecha_creacion'])) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Cuestionarios -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-clipboard-check me-2"></i>Cuestionarios</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($cuestionarios)): ?>
                                            <p class="text-muted text-center">No hay cuestionarios</p>
                                        <?php else: ?>
                                            <?php foreach ($cuestionarios as $cuestionario): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <div class="d-flex justify-content-between">
                                                        <small><strong><?= htmlspecialchars($cuestionario['titulo']) ?></strong></small>
                                                        <span class="badge bg-<?= $cuestionario['estado'] === 'PUBLICADO' ? 'success' : 'secondary' ?>">
                                                            <?= $cuestionario['estado'] ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block">
                                                        <?= date('d/m/Y', strtotime($cuestionario['fecha_creacion'])) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Anuncios -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-speakerphone me-2"></i>Anuncios</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($anuncios)): ?>
                                            <p class="text-muted text-center">No hay anuncios</p>
                                        <?php else: ?>
                                            <?php foreach ($anuncios as $anuncio): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <small><strong><?= htmlspecialchars($anuncio['titulo']) ?></strong></small>
                                                    <small class="text-muted d-block">
                                                        <?= date('d/m/Y', strtotime($anuncio['fecha_publicacion'])) ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <?php include 'modales/contenido/modal_unidad.php'; ?>
    <?php include 'modales/contenido/modal_leccion.php'; ?>
    <?php include 'modales/contenido/modal_recurso.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const cursoId = <?= $curso_id ?>;

        function agregarLeccion(unidadId) {
            $('#unidad_id_leccion').val(unidadId);
            $('#leccion_id').val('');
            $('#formLeccion')[0].reset();
            $('#modalLeccionLabel').text('Nueva Lección');
            $('#modalLeccion').modal('show');
        }

        function editarUnidad(id) {
            $.post('modales/contenido/procesar.php', {
                accion: 'obtener_unidad',
                id: id
            }, function(response) {
                if (response.success) {
                    cargarDatosUnidad(response.data);
                    $('#modalUnidad').modal('show');
                }
            }, 'json');
        }

        function editarLeccion(id) {
            $.post('modales/contenido/procesar.php', {
                accion: 'obtener_leccion',
                id: id
            }, function(response) {
                if (response.success) {
                    cargarDatosLeccion(response.data);
                    $('#modalLeccion').modal('show');
                }
            }, 'json');
        }

        function eliminarUnidad(id) {
            Swal.fire({
                title: '¿Eliminar unidad?',
                text: 'Esta acción eliminará también todas las lecciones asociadas',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('modales/contenido/procesar.php', {
                        accion: 'eliminar_unidad',
                        id: id
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }, 'json');
                }
            });
        }

        function eliminarLeccion(id) {
            Swal.fire({
                title: '¿Eliminar lección?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('modales/contenido/procesar.php', {
                        accion: 'eliminar_leccion',
                        id: id
                    }, function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }, 'json');
                }
            });
        }

        function cargarDatosUnidad(data) {
            $('#unidad_id').val(data.id);
            $('#titulo_unidad').val(data.titulo);
            $('#descripcion_unidad').val(data.descripcion);
            $('#orden_unidad').val(data.orden);
            $('#estado_unidad').val(data.configuraciones.estado || 'BORRADOR');
            $('#fecha_inicio_unidad').val(data.configuraciones.fecha_inicio || '');
            $('#fecha_fin_unidad').val(data.configuraciones.fecha_fin || '');
            $('#modalUnidadLabel').text('Editar Unidad');
        }

        function cargarDatosLeccion(data) {
            $('#leccion_id').val(data.id);
            $('#unidad_id_leccion').val(data.unidad_id);
            $('#titulo_leccion').val(data.titulo);
            $('#descripcion_leccion').val(data.descripcion);
            $('#tipo_leccion').val(data.tipo);
            $('#orden_leccion').val(data.orden);
            $('#tiempo_estimado').val(data.configuraciones.tiempo_estimado || '');
            $('#estado_leccion').val(data.configuraciones.estado || 'BORRADOR');
            $('#modalLeccionLabel').text('Editar Lección');
        }
    </script>
</body>
</html>