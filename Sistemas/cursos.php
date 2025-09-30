<?php 
    require_once 'conexion/bd.php';

    // Obtener cursos con toda su información relacionada
    try {
        $sql = "SELECT c.*, 
                    ad.id as asignacion_id,
                    d.nombres as docente_nombres, d.apellidos as docente_apellidos,
                    ac.nombre as area_nombre, ac.codigo as area_codigo,
                    s.grado, s.seccion, s.codigo as seccion_codigo,
                    n.nombre as nivel_nombre,
                    pa.nombre as periodo_nombre
                FROM cursos c
                INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
                INNER JOIN docentes d ON ad.docente_id = d.id
                INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                INNER JOIN secciones s ON ad.seccion_id = s.id
                INNER JOIN niveles_educativos n ON s.nivel_id = n.id
                INNER JOIN periodos_academicos pa ON ad.periodo_academico_id = pa.id
                ORDER BY c.fecha_creacion DESC";
        
        $stmt_cursos = $conexion->prepare($sql);
        $stmt_cursos->execute();
        $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $cursos = [];
        $error_cursos = "Error al cargar cursos: " . $e->getMessage();
    }

    // Obtener asignaciones docentes activas para crear nuevos cursos
    try {
        $sql_asignaciones = "SELECT ad.*, 
                                d.nombres as docente_nombres, d.apellidos as docente_apellidos,
                                ac.nombre as area_nombre,
                                s.grado, s.seccion, s.codigo as seccion_codigo,
                                n.nombre as nivel_nombre
                            FROM asignaciones_docentes ad
                            INNER JOIN docentes d ON ad.docente_id = d.id
                            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
                            INNER JOIN secciones s ON ad.seccion_id = s.id
                            INNER JOIN niveles_educativos n ON s.nivel_id = n.id
                            WHERE ad.activo = 1
                            ORDER BY n.orden, s.grado, s.seccion, ac.nombre";
        $stmt_asignaciones = $conexion->prepare($sql_asignaciones);
        $stmt_asignaciones->execute();
        $asignaciones_disponibles = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $asignaciones_disponibles = [];
    }

    // Obtener áreas curriculares para filtros
    try {
        $stmt_areas = $conexion->prepare("SELECT * FROM areas_curriculares WHERE activo = 1 ORDER BY nombre ASC");
        $stmt_areas->execute();
        $areas_curriculares = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $areas_curriculares = [];
    }

    // Obtener docentes activos para filtros
    try {
        $stmt_docentes = $conexion->prepare("SELECT id, nombres, apellidos FROM docentes WHERE activo = 1 ORDER BY apellidos ASC");
        $stmt_docentes->execute();
        $docentes_activos = $stmt_docentes->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $docentes_activos = [];
    }

    // Calcular estadísticas
    $total_cursos = count($cursos);
    $cursos_activos = 0;
    $total_estudiantes = 0;
    $promedio_progreso = 0;
    
    foreach ($cursos as $curso) {
        $config = json_decode($curso['configuraciones'], true);
        if (isset($config['estado']) && $config['estado'] === 'ACTIVO') {
            $cursos_activos++;
        }
        
        $estudiantes = json_decode($curso['estudiantes_inscritos'], true);
        if (is_array($estudiantes)) {
            $total_estudiantes += count($estudiantes);
        }
        
        $stats = json_decode($curso['estadisticas'], true);
        if (isset($stats['progreso_promedio'])) {
            $promedio_progreso += $stats['progreso_promedio'];
        }
    }
    
    $promedio_progreso = $total_cursos > 0 ? round($promedio_progreso / $total_cursos, 2) : 0;

    // Estadísticas por área
    $cursos_por_area = [];
    foreach ($cursos as $curso) {
        $area = $curso['area_nombre'];
        $cursos_por_area[$area] = ($cursos_por_area[$area] ?? 0) + 1;
    }

    // Estadísticas por nivel
    $cursos_por_nivel = [];
    foreach ($cursos as $curso) {
        $nivel = $curso['nivel_nombre'];
        $cursos_por_nivel[$nivel] = ($cursos_por_nivel[$nivel] ?? 0) + 1;
    }
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Cursos - ANDRÉS AVELINO CÁCERES</title>
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
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .curso-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .curso-codigo {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .curso-nombre {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .curso-info-item {
        padding: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .curso-info-item:last-child {
        border-bottom: none;
    }

    .estudiantes-badge {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .progreso-bar {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progreso-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }

    . {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
    }

    .stats-label {
        opacity: 0.9;
        font-size: 0.9rem;
        margin-top: 0.5rem;
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

    .fecha-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .config-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
        margin: 0.1rem;
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
                                <h4 class="fw-bold mb-0">Gestión de Cursos</h4>
                                <p class="mb-0 text-muted">Administra los cursos, unidades y contenidos del periodo académico</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarCurso">
                                    <i class="ti ti-plus me-2"></i>
                                    Nuevo Curso
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
                                <?= $total_cursos ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Total de Cursos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #FFB3BA; margin-bottom: 0.5rem;">
                                <?= $cursos_activos ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Cursos Activos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #BAE1FF; margin-bottom: 0.5rem;">
                                <?= $total_estudiantes ?>
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Estudiantes Inscritos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); padding: 2rem; text-align: center; border: 1px solid #e0e0e0; margin-bottom: 1.5rem;">
                            <div class="stats-number" style="font-size: 2.5rem; font-weight: 700; color: #FFDDA1; margin-bottom: 0.5rem;">
                                <?= $promedio_progreso ?>%
                            </div>
                            <div class="stats-label" style="font-size: 1rem; font-weight: 500; color: #888; text-transform: uppercase; letter-spacing: 1px;">Progreso Promedio</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Área Curricular</label>
                                <select class="form-select" id="filtroArea">
                                    <option value="">Todas las áreas</option>
                                    <?php foreach ($areas_curriculares as $area): ?>
                                        <option value="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Docente</label>
                                <select class="form-select" id="filtroDocente">
                                    <option value="">Todos los docentes</option>
                                    <?php foreach ($docentes_activos as $docente): ?>
                                        <option value="<?= $docente['id'] ?>">
                                            <?= htmlspecialchars($docente['apellidos'] . ', ' . $docente['nombres']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="ACTIVO">Activos</option>
                                    <option value="FINALIZADO">Finalizados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarCurso" placeholder="Buscar curso...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarCursos()">
                                        <i class="ti ti-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Cursos -->
                <div class="row" id="cursosContainer">
                    <?php foreach ($cursos as $curso): 
                        $configuraciones = json_decode($curso['configuraciones'], true) ?: [];
                        $estudiantes = json_decode($curso['estudiantes_inscritos'], true) ?: [];
                        $estadisticas = json_decode($curso['estadisticas'], true) ?: [];
                        
                        $estado = $configuraciones['estado'] ?? 'ACTIVO';
                        $color_tema = $configuraciones['color_tema'] ?? '#667eea';
                        $fecha_inicio = $configuraciones['fecha_inicio'] ?? '';
                        $fecha_fin = $configuraciones['fecha_fin'] ?? '';
                        
                        $total_estudiantes_curso = count($estudiantes);
                        $progreso_promedio = $estadisticas['progreso_promedio'] ?? 0;
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4 curso-card" 
                             data-area="<?= $curso['area_nombre'] ?>" 
                             data-docente="<?= $curso['docente_nombres'] . ' ' . $curso['docente_apellidos'] ?>"
                             data-estado="<?= $estado ?>">
                            <div class="card h-100">
                                <div class="curso-header" style="background: <?= htmlspecialchars($color_tema) ?>;">
                                    <div class="curso-codigo"><?= htmlspecialchars($curso['codigo_curso']) ?></div>
                                    <div class="curso-nombre mt-1"><?= htmlspecialchars($curso['nombre']) ?></div>
                                    <div class="mt-2">
                                        <span class="badge bg-white text-dark"><?= htmlspecialchars($curso['area_nombre']) ?></span>
                                        <span class="badge bg-light text-dark ms-1">
                                            <?= htmlspecialchars($curso['grado'] . ' - ' . $curso['seccion']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Descripción -->
                                    <?php if (!empty($curso['descripcion'])): ?>
                                        <p class="text-muted small mb-3">
                                            <?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) ?>
                                            <?= strlen($curso['descripcion']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Docente -->
                                    <div class="curso-info-item">
                                        <i class="ti ti-user me-2"></i>
                                        <strong>Docente:</strong> 
                                        <?= htmlspecialchars($curso['docente_nombres'] . ' ' . $curso['docente_apellidos']) ?>
                                    </div>
                                    
                                    <!-- Periodo -->
                                    <div class="curso-info-item">
                                        <i class="ti ti-calendar me-2"></i>
                                        <strong>Periodo:</strong> <?= htmlspecialchars($curso['periodo_nombre']) ?>
                                    </div>
                                    
                                    <!-- Estudiantes -->
                                    <div class="curso-info-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><i class="ti ti-users me-2"></i><strong>Estudiantes:</strong></span>
                                            <span class="estudiantes-badge"><?= $total_estudiantes_curso ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progreso -->
                                    <div class="curso-info-item">
                                        <div class="mb-1"><i class="ti ti-chart-line me-2"></i><strong>Progreso Promedio</strong></div>
                                        <div class="progreso-bar">
                                            <div class="progreso-fill" style="width: <?= $progreso_promedio ?>%"></div>
                                        </div>
                                        <div class="text-end mt-1"><small><?= $progreso_promedio ?>%</small></div>
                                    </div>
                                    
                                    <!-- Fechas -->
                                    <div class="mt-3">
                                        <?php if ($fecha_inicio): ?>
                                            <span class="badge bg-info fecha-badge me-1">
                                                Inicio: <?= date('d/m/Y', strtotime($fecha_inicio)) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($fecha_fin): ?>
                                            <span class="badge bg-warning fecha-badge">
                                                Fin: <?= date('d/m/Y', strtotime($fecha_fin)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Estado -->
                                    <div class="mt-2">
                                        <span class="badge <?= $estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $estado ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-light">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetallesCurso(<?= $curso['id'] ?>)" 
                                                title="Ver Detalles Completos">
                                            <i class="ti ti-eye"></i> Detalles
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="editarCurso(<?= $curso['id'] ?>)" 
                                                title="Editar Curso">
                                            <i class="ti ti-edit"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="gestionarContenido(<?= $curso['id'] ?>)" 
                                                title="Gestionar Unidades">
                                            <i class="ti ti-books"></i> Contenido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Distribución por Área y Nivel -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Distribución por Área Curricular</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($cursos_por_area as $area => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($area) ?></span>
                                        <span class="badge bg-primary"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Distribución por Nivel Educativo</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($cursos_por_nivel as $nivel => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($nivel) ?></span>
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

    <!-- Incluir Modales -->
    <?php include 'modales/cursos/modal_agregar.php'; ?>
    <?php include 'modales/cursos/modal_editar.php'; ?>
    <?php include 'modales/cursos/modal_detalles.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const asignacionesDisponibles = <?= json_encode($asignaciones_disponibles) ?>;

        $(document).ready(function() {
            // Aplicar filtros
            $('#filtroArea, #filtroDocente, #filtroEstado, #buscarCurso').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const areaFiltro = $('#filtroArea option:selected').text().trim();
            const docenteFiltro = $('#filtroDocente option:selected').text().trim();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarCurso').val().toLowerCase();

            $('.curso-card').each(function() {
                const card = $(this);
                const area = card.data('area');
                const docente = card.data('docente');
                const estado = card.data('estado');
                const texto = card.text().toLowerCase();

                let mostrar = true;

                if (areaFiltro && areaFiltro !== 'Todas las áreas' && area !== areaFiltro) {
                    mostrar = false;
                }

                if (docenteFiltro && docenteFiltro !== 'Todos los docentes' && !docente.includes(docenteFiltro)) {
                    mostrar = false;
                }

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroArea, #filtroDocente, #filtroEstado').val('');
            $('#buscarCurso').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarCurso(id) {
            mostrarCarga();
            
            fetch('modales/cursos/procesar_cursos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionCurso(data.curso);
                    $('#modalEditarCurso').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del curso');
            });
        }

        function verDetallesCurso(id) {
            mostrarCarga();
            
            fetch('modales/cursos/procesar_cursos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalles_completos&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    mostrarDetallesCompletos(data.curso);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al cargar detalles del curso');
            });
        }

        function gestionarContenido(id) {
            window.location.href = `curso_contenido.php?id=${id}`;
        }

        function exportarCursos() {
            window.open('reportes/exportar_cursos.php', '_blank');
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