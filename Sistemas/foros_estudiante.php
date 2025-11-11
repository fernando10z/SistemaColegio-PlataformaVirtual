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

$usuario_id = $_SESSION['usuario_id'];

// Obtener información del estudiante usando usuario_id
try {
    $sql_estudiante = "SELECT e.*, 
                              u.nombres as usuario_nombres, 
                              u.apellidos as usuario_apellidos,
                              u.email as usuario_email
                       FROM estudiantes e
                       INNER JOIN usuarios u ON e.usuario_id = u.id
                       WHERE e.usuario_id = :usuario_id AND u.activo = 1";
    
    $stmt = $conexion->prepare($sql_estudiante);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        die("No se encontró información del estudiante. Contacta con el administrador.");
    }
    
    $estudiante_id = $estudiante['id'];
    $nombre_completo = $estudiante['nombres'] . ' ' . $estudiante['apellidos'];
    
} catch (PDOException $e) {
    die("Error al cargar datos del estudiante: " . $e->getMessage());
}

// Obtener foros de los cursos donde está inscrito el estudiante
try {
    $sql = "SELECT f.*, 
                c.nombre as curso_nombre,
                c.codigo_curso,
                ac.nombre as area_nombre,
                d.nombres as docente_nombres,
                d.apellidos as docente_apellidos
            FROM foros f
            INNER JOIN cursos c ON f.curso_id = c.id
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
            INNER JOIN docentes d ON ad.docente_id = d.id
            WHERE JSON_SEARCH(c.estudiantes_inscritos, 'one', :estudiante_id, NULL, '$[*].estudiante_id') IS NOT NULL
            ORDER BY f.fecha_creacion DESC";
    
    $stmt_foros = $conexion->prepare($sql);
    $stmt_foros->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_foros->execute();
    $foros = $stmt_foros->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $foros = [];
    $error_foros = "Error al cargar foros: " . $e->getMessage();
}

// Procesar estadísticas
$total_foros = count($foros);
$total_participaciones = 0;
$foros_activos = 0;
$mis_hilos = 0;

$cursos_unicos = [];

foreach ($foros as &$foro) {
    $config = json_decode($foro['configuraciones'], true) ?? [];
    $mensajes = json_decode($foro['mensajes'], true) ?? [];
    $estadisticas = json_decode($foro['estadisticas'], true) ?? [];
    
    $foro['tipo'] = $config['tipo'] ?? 'GENERAL';
    $foro['estado'] = $config['estado'] ?? 'CERRADO';
    $foro['moderado'] = $config['moderado'] ?? false;
    
    $foro['total_mensajes'] = $estadisticas['total_mensajes'] ?? count($mensajes);
    $foro['participantes'] = $estadisticas['participantes'] ?? 0;
    
    if ($foro['estado'] === 'ABIERTO') {
        $foros_activos++;
    }
    
    // Contar participaciones del estudiante
    foreach ($mensajes as $mensaje) {
        if ($mensaje['usuario_id'] == $usuario_id) {
            $total_participaciones++;
            $mis_hilos++;
        }
        if (isset($mensaje['respuestas']) && is_array($mensaje['respuestas'])) {
            foreach ($mensaje['respuestas'] as $respuesta) {
                if ($respuesta['usuario_id'] == $usuario_id) {
                    $total_participaciones++;
                }
            }
        }
    }
    
    $cursos_unicos[$foro['curso_nombre']] = true;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Foros de Discusión - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        /* Eliminación completa del espacio superior */
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
        
        .app-header {
        margin-top: 0 !important;
        }
        
        /* Optimizaciones adicionales para mejor rendimiento */
        .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
        }
        
        .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
        background: transparent;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 3px;
        }
        
        /* Mejoras de accesibilidad */
        .btn:focus,
        .nav-link:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
        }

        /* CSS para left-sidebar - Eliminación de huecos y optimización */
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

        /* Contenedor interno del sidebar */
        .left-sidebar > div {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Brand logo area */
        .left-sidebar .brand-logo {
            flex-shrink: 0;
            padding: 20px 24px;
            margin: 0 !important;
            border-bottom: 1px solid #e9ecef;
        }

        /* Navegación del sidebar */
        .left-sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Lista de navegación */
        .left-sidebar #sidebarnav {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none;
        }

        /* Items del sidebar */
        .left-sidebar .sidebar-item {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Links del sidebar */
        .left-sidebar .sidebar-link {
            display: flex;
            align-items: center;
            padding: 12px 24px !important;
            margin: 0 !important;
            text-decoration: none;
            color: #495057;
            border: none !important;
            background: transparent !important;
            transition: all 0.15s ease;
        }

        /* Hover effects */
        .left-sidebar .sidebar-link:hover {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
        }

        /* Active link */
        .left-sidebar .sidebar-link.active {
            background-color: #e7f1ff !important;
            color: #0d6efd !important;
            font-weight: 500;
        }

        /* Categorías pequeñas */
        .left-sidebar .nav-small-cap {
            padding: 20px 24px 8px 24px !important;
            margin: 0 !important;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Dividers */
        .left-sidebar .sidebar-divider {
            margin: 16px 24px !important;
            height: 1px;
            background-color: #e9ecef;
            border: none;
        }

        /* Badges Pro */
        .left-sidebar .badge {
            font-size: 0.625rem !important;
            padding: 4px 8px !important;
        }

        /* Submenús colapsables */
        .left-sidebar .collapse {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Items de submenú */
        .left-sidebar .first-level .sidebar-item .sidebar-link {
            padding-left: 48px !important;
            font-size: 0.875rem;
        }

        /* Scrollbar personalizado */
        .left-sidebar .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.1);
            border-radius: 2px;
        }

        .left-sidebar .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0,0,0,0.2);
        }

        /* Firefox scrollbar */
        .left-sidebar .sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(0,0,0,0.1) transparent;
        }

        /* Responsive - Mobile */
        @media (max-width: 1199.98px) {
            .left-sidebar {
            margin-left: -270px;
            transition: margin-left 0.25s ease;
            }
            
            .left-sidebar.show {
            margin-left: 0;
            }
        }

        /* Mini sidebar state */
        .mini-sidebar .left-sidebar {
            width: 80px !important;
        }

        .mini-sidebar .left-sidebar .hide-menu {
            display: none !important;
        }

        .mini-sidebar .left-sidebar .brand-logo img {
            width: 40px !important;
        }
        
        /* Optimización de animaciones */
        @media (prefers-reduced-motion: reduce) {
        .card {
            transition: none;
        }
        }
  </style>
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }

        .stats-card {
            background: linear-gradient(135deg, #A8D8EA 0%, #D4A5A5 100%);
            color: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            margin-bottom: 1.5rem;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .stats-card.secondary {
            background: linear-gradient(135deg, #FFAAA5 0%, #FFD3B6 100%);
        }

        .stats-card.tertiary {
            background: linear-gradient(135deg, #C7CEEA 0%, #B8C6DB 100%);
        }

        .stats-card.quaternary {
            background: linear-gradient(135deg, #FFDDC1 0%, #FAD0C4 100%);
        }

        .stats-number {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            opacity: 0.95;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .foro-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .foro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            cursor: pointer;
        }

        .foro-header {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .foro-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .foro-titulo {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .foro-curso {
            font-size: 0.85rem;
            opacity: 0.95;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .foro-body {
            padding: 1.25rem;
        }

        .foro-descripcion {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .foro-stats {
            display: flex;
            gap: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .stat-item i {
            color: #A8D8EA;
            font-size: 1.1rem;
        }

        .stat-value {
            font-weight: 700;
            color: #2c3e50;
        }

        .estado-badge {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 2;
        }

        .estado-abierto {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
        }

        .estado-cerrado {
            background: linear-gradient(45deg, #FF6B6B, #FF4757);
            color: white;
        }

        .tipo-badge {
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .tipo-pregunta {
            background: linear-gradient(45deg, #A8D8EA, #9AC8D8);
            color: white;
        }

        .tipo-discusion {
            background: linear-gradient(45deg, #FFAAA5, #FF9A95);
            color: white;
        }

        .tipo-general {
            background: linear-gradient(45deg, #C7CEEA, #B7BFD8);
            color: white;
        }

        .btn-action {
            padding: 0.65rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #9AC8D8 0%, #8AB8C8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(168, 216, 234, 0.4);
        }

        .filtros-container {
            background: #fafafa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e8e8e8;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d0d0d0;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #A8D8EA;
            box-shadow: 0 0 0 0.2rem rgba(168, 216, 234, 0.25);
        }

        .welcome-card {
            background: linear-gradient(135deg, #A8D8EA 0%, #9AC8D8 100%);
            color: white;
            border-radius: 14px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-card h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            opacity: 0.95;
            margin-bottom: 0;
        }

        .mensaje-thread {
            background: #f8f9fa;
            border-left: 3px solid #A8D8EA;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .mensaje-autor {
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .mensaje-fecha {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .mensaje-contenido {
            color: #495057;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .respuesta {
            margin-left: 2rem;
            margin-top: 0.75rem;
            padding-left: 1rem;
            border-left: 2px solid #C7CEEA;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #A8D8EA;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
            <?php include 'includes/header.php'; ?>

            <div class="container-fluid">
                
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h4>
                        <i class="ti ti-messages me-2"></i>
                        Foros de Discusión
                    </h4>
                    <p>Participa en debates académicos, resuelve dudas y colabora con tus compañeros y profesores.</p>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?= $total_foros ?></div>
                            <div class="stats-label">
                                <i class="ti ti-message-circle me-1"></i>
                                Foros Disponibles
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card secondary">
                            <div class="stats-number"><?= $foros_activos ?></div>
                            <div class="stats-label">
                                <i class="ti ti-circle-check me-1"></i>
                                Foros Activos
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card tertiary">
                            <div class="stats-number"><?= $total_participaciones ?></div>
                            <div class="stats-label">
                                <i class="ti ti-message-dots me-1"></i>
                                Mis Participaciones
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card quaternary">
                            <div class="stats-number"><?= $mis_hilos ?></div>
                            <div class="stats-label">
                                <i class="ti ti-message-plus me-1"></i>
                                Hilos Creados
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-book me-1"></i>
                                Curso
                            </label>
                            <select class="form-select" id="filtroCurso">
                                <option value="">Todos los cursos</option>
                                <?php foreach (array_keys($cursos_unicos) as $curso): ?>
                                    <option value="<?= htmlspecialchars($curso) ?>"><?= htmlspecialchars($curso) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-filter me-1"></i>
                                Estado
                            </label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="ABIERTO">Abiertos</option>
                                <option value="CERRADO">Cerrados</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="ti ti-search me-1"></i>
                                Buscar
                            </label>
                            <input type="text" class="form-control" id="buscarForo" placeholder="Buscar foro...">
                        </div>
                    </div>
                </div>

                <!-- Lista de Foros -->
                <div class="row" id="forosContainer">
                    <?php if (empty($foros)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert" style="border-radius: 12px; padding: 2rem;">
                                <i class="ti ti-info-circle fs-1 mb-3" style="color: #A8D8EA;"></i>
                                <h5>No hay foros disponibles</h5>
                                <p class="mb-0">Aún no se han creado foros en tus cursos.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($foros as $foro): 
                            $estado_clase = strtolower($foro['estado']);
                            $tipo_clase = 'tipo-' . strtolower(str_replace('_', '-', $foro['tipo']));
                        ?>
                        <div class="col-md-6 foro-card-wrapper"
                             data-curso="<?= htmlspecialchars($foro['curso_nombre']) ?>"
                             data-estado="<?= htmlspecialchars($foro['estado']) ?>"
                             data-titulo="<?= htmlspecialchars($foro['titulo']) ?>"
                             onclick="verForo(<?= $foro['id'] ?>)">
                            <div class="foro-card">
                                <div class="foro-header">
                                    <span class="estado-badge estado-<?= $estado_clase ?>">
                                        <?= $foro['estado'] ?>
                                    </span>
                                    <span class="tipo-badge <?= $tipo_clase ?>">
                                        <?= str_replace('_', ' ', $foro['tipo']) ?>
                                    </span>
                                    <div class="foro-titulo"><?= htmlspecialchars($foro['titulo']) ?></div>
                                    <div class="foro-curso">
                                        <i class="ti ti-book me-1"></i>
                                        <?= htmlspecialchars($foro['curso_nombre']) ?>
                                    </div>
                                </div>
                                <div class="foro-body">
                                    <div class="foro-descripcion">
                                        <?= htmlspecialchars(substr($foro['descripcion'], 0, 150)) ?>
                                        <?= strlen($foro['descripcion']) > 150 ? '...' : '' ?>
                                    </div>
                                    <div class="foro-stats">
                                        <div class="stat-item">
                                            <i class="ti ti-message"></i>
                                            <span class="stat-value"><?= $foro['total_mensajes'] ?></span>
                                            <span>mensajes</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="ti ti-users"></i>
                                            <span class="stat-value"><?= $foro['participantes'] ?></span>
                                            <span>participantes</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="ti ti-user"></i>
                                            <span style="font-size: 0.8rem;">
                                                Prof. <?= htmlspecialchars($foro['docente_apellidos']) ?>
                                            </span>
                                        </div>
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

    <!-- Incluir Modales -->
    <?php include 'modales/foros_estudiante/modal_ver_foro.php'; ?>
    <?php include 'modales/foros_estudiante/modal_nuevo_hilo.php'; ?>
    <?php include 'modales/foros_estudiante/modal_responder.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const forosData = <?= json_encode($foros, JSON_UNESCAPED_UNICODE) ?>;
        const estudianteId = <?= $estudiante_id ?>;
        const usuarioId = <?= $usuario_id ?>;
        const nombreCompleto = <?= json_encode($nombre_completo, JSON_UNESCAPED_UNICODE) ?>;

        $(document).ready(function() {
            $('#filtroCurso, #filtroEstado, #buscarForo').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const cursoFiltro = $('#filtroCurso').val().toLowerCase();
            const estadoFiltro = $('#filtroEstado').val().toUpperCase();
            const busqueda = $('#buscarForo').val().toLowerCase();

            let forosVisibles = 0;

            $('.foro-card-wrapper').each(function() {
                const card = $(this);
                const curso = card.data('curso').toString().toLowerCase();
                const estado = card.data('estado').toString().toUpperCase();
                const titulo = card.data('titulo').toString().toLowerCase();
                
                let mostrar = true;

                if (cursoFiltro && !curso.includes(cursoFiltro)) {
                    mostrar = false;
                }

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !titulo.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
                if (mostrar) forosVisibles++;
            });

            if (forosVisibles === 0 && $('.foro-card-wrapper').length > 0) {
                if ($('#noResultados').length === 0) {
                    $('#forosContainer').append(`
                        <div class="col-12" id="noResultados">
                            <div class="alert alert-warning text-center">
                                <i class="ti ti-search-off fs-4 mb-2"></i>
                                <h5>No se encontraron foros</h5>
                                <p class="mb-0">Intenta con otros criterios de búsqueda</p>
                            </div>
                        </div>
                    `);
                }
            } else {
                $('#noResultados').remove();
            }
        }

        function verForo(foroId) {
            const foro = forosData.find(f => f.id == foroId);
            if (foro) {
                cargarForoCompleto(foro);
                $('#modalVerForo').modal('show');
            }
        }

        function cargarForoCompleto(foro) {
            const mensajes = foro.mensajes ? JSON.parse(foro.mensajes) : [];
            
            $('#foroTitulo').text(foro.titulo);
            $('#foroDescripcion').text(foro.descripcion);
            $('#foroCurso').text(foro.curso_nombre);
            
            let htmlMensajes = '';
            
            if (mensajes.length === 0) {
                htmlMensajes = `
                    <div class="empty-state">
                        <i class="ti ti-message-off"></i>
                        <h5>No hay mensajes aún</h5>
                        <p>Sé el primero en participar en este foro</p>
                    </div>
                `;
            } else {
                mensajes.forEach(mensaje => {
                    htmlMensajes += renderMensaje(mensaje);
                });
            }
            
            $('#mensajesContainer').html(htmlMensajes);
            
            // Configurar botones
            if (foro.estado === 'ABIERTO') {
                $('#btnNuevoHilo').data('foro-id', foro.id).show();
            } else {
                $('#btnNuevoHilo').hide();
            }
        }

        function renderMensaje(mensaje) {
            let html = `
                <div class="mensaje-thread">
                    <div class="mensaje-autor">${mensaje.usuario_nombre || 'Usuario'}</div>
                    <div class="mensaje-fecha">${formatearFecha(mensaje.fecha_creacion)}</div>
                    ${mensaje.titulo ? `<div style="font-weight: 600; margin-bottom: 0.5rem;">${mensaje.titulo}</div>` : ''}
                    <div class="mensaje-contenido">${mensaje.contenido}</div>
            `;
            
            if (mensaje.respuestas && mensaje.respuestas.length > 0) {
                mensaje.respuestas.forEach(respuesta => {
                    html += `
                        <div class="respuesta">
                            <div class="mensaje-autor">${respuesta.usuario_nombre || 'Usuario'}</div>
                            <div class="mensaje-fecha">${formatearFecha(respuesta.fecha_creacion)}</div>
                            <div class="mensaje-contenido">${respuesta.contenido}</div>
                        </div>
                    `;
                });
            }
            
            html += `
                    <div class="mt-2">
                        <button class="btn btn-sm btn-primary-custom" onclick="responderMensaje(${mensaje.id})">
                            <i class="ti ti-message-reply me-1"></i>
                            Responder
                        </button>
                    </div>
                </div>
            `;
            
            return html;
        }

        function formatearFecha(fechaStr) {
            const fecha = new Date(fechaStr);
            return fecha.toLocaleString('es-PE', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function nuevoHilo() {
            const foroId = $('#btnNuevoHilo').data('foro-id');
            $('#nuevoHiloForoId').val(foroId);
            $('#modalVerForo').modal('hide');
            $('#modalNuevoHilo').modal('show');
        }

        function responderMensaje(mensajeId) {
            $('#responderMensajeId').val(mensajeId);
            $('#modalVerForo').modal('hide');
            $('#modalResponder').modal('show');
        }

        function mostrarAlerta(tipo, titulo, mensaje) {
            Swal.fire({
                icon: tipo,
                title: titulo,
                text: mensaje,
                confirmButtonColor: '#A8D8EA',
                confirmButtonText: 'Entendido'
            });
        }
    </script>
</body>
</html>