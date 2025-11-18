<?php 
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}

require_once 'conexion/bd.php';

// ==========================================
// OBTENER ROL Y DATOS DEL USUARIO EN SESIÓN
// ==========================================
$usuario_id = $_SESSION['usuario_id'];
$rol_usuario = null;
$docente_id = null;
$es_docente = false;

try {
    // Obtener rol del usuario
    $stmt_rol = $conexion->prepare("SELECT rol_id FROM usuarios WHERE id = ? LIMIT 1");
    $stmt_rol->execute([$usuario_id]);
    $usuario_data = $stmt_rol->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario_data) {
        $rol_usuario = intval($usuario_data['rol_id']);
        $es_docente = ($rol_usuario === 4); // Rol 4 = Docente
        
        // Si es docente, obtener su docente_id
        if ($es_docente) {
            $stmt_docente = $conexion->prepare("SELECT id FROM docentes WHERE usuario_id = ? AND activo = 1 LIMIT 1");
            $stmt_docente->execute([$usuario_id]);
            $docente_data = $stmt_docente->fetch(PDO::FETCH_ASSOC);
            
            if ($docente_data) {
                $docente_id = intval($docente_data['id']);
            } else {
                die('Error: No se encontró registro de docente asociado a su usuario.');
            }
        }
    }
} catch (PDOException $e) {
    error_log("Error obteniendo datos de usuario: " . $e->getMessage());
    die('Error al verificar permisos de usuario.');
}

// Obtener datos del colegio
try {
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto, direccion, refran FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    if ($colegio) {
        $colegio_nombre = $colegio['nombre'] ?? '';
        $colegio_ruc = $colegio['ruc'] ?? '';
        $colegio_foto = $colegio['foto'] ?? '';
        $colegio_direccion = $colegio['direccion'] ?? '';
        $refran = $colegio['refran'] ?? '';
    }
} catch (PDOException $e) {
    error_log("Error fetching colegio_principal: " . $e->getMessage());
}

$nombre = $colegio_nombre;
$ruc = $colegio_ruc;
$foto = $colegio_foto;
$direccion = $colegio_direccion;

// ==========================================
// OBTENER UNIDADES (FILTRADO POR ROL)
// ==========================================
try {
    $sql = "SELECT u.*, 
                c.nombre as curso_nombre,
                c.codigo_curso,
                c.id as curso_id,
                ad.docente_id,
                COUNT(DISTINCT l.id) as total_lecciones,
                COUNT(DISTINCT CASE WHEN l.tipo = 'CONTENIDO' THEN l.id END) as lecciones_contenido,
                COUNT(DISTINCT CASE WHEN l.tipo = 'EVALUACION' THEN l.id END) as lecciones_evaluacion
            FROM unidades u
            INNER JOIN cursos c ON u.curso_id = c.id
            INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
            LEFT JOIN lecciones l ON u.id = l.unidad_id";
    
    // FILTRO: Si es docente, solo sus unidades
    if ($es_docente && $docente_id) {
        $sql .= " WHERE ad.docente_id = :docente_id";
    }
    
    $sql .= " GROUP BY u.id
             ORDER BY c.nombre ASC, u.orden ASC";
    
    $stmt_unidades = $conexion->prepare($sql);
    
    if ($es_docente && $docente_id) {
        $stmt_unidades->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
    }
    
    $stmt_unidades->execute();
    $unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unidades = [];
    $error_unidades = "Error al cargar unidades: " . $e->getMessage();
    error_log($error_unidades);
}

// ==========================================
// OBTENER CURSOS (FILTRADO POR ROL)
// ==========================================
try {
    $sql_cursos = "SELECT c.*, 
                       ad.docente_id,
                       d.nombres as docente_nombres, 
                       d.apellidos as docente_apellidos
                   FROM cursos c
                   INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id
                   INNER JOIN docentes d ON ad.docente_id = d.id";
    
    // FILTRO: Si es docente, solo sus cursos
    if ($es_docente && $docente_id) {
        $sql_cursos .= " WHERE ad.docente_id = :docente_id";
    }
    
    $sql_cursos .= " ORDER BY c.nombre ASC";
    
    $stmt_cursos = $conexion->prepare($sql_cursos);
    
    if ($es_docente && $docente_id) {
        $stmt_cursos->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
    }
    
    $stmt_cursos->execute();
    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cursos = [];
    error_log("Error al cargar cursos: " . $e->getMessage());
}

// Calcular estadísticas
$total_unidades = count($unidades);
$unidades_publicadas = count(array_filter($unidades, function($u) { 
    $config = json_decode($u['configuraciones'], true);
    return ($config['estado'] ?? '') === 'PUBLICADO'; 
}));
$unidades_borrador = $total_unidades - $unidades_publicadas;
$total_lecciones = array_sum(array_column($unidades, 'total_lecciones'));

// Estadísticas por curso
$unidades_por_curso = [];
foreach ($unidades as $unidad) {
    $curso = $unidad['curso_nombre'];
    $unidades_por_curso[$curso] = ($unidades_por_curso[$curso] ?? 0) + 1;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $es_docente ? 'Mis Unidades' : 'Gestión de Unidades' ?> - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
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
        
        .unidad-card {
            border-left: 4px solid #5D87FF;
            margin-bottom: 1rem;
        }
        
        .unidad-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .unidad-orden {
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .lecciones-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }
        
        .estado-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .fecha-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .progress-thin {
            height: 5px;
        }
        
        .table-actions .btn {
            padding: 0.35rem 0.65rem;
            font-size: 0.8rem;
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

        .curso-info {
            font-size: 0.85rem;
            color: #495057;
        }

        .duracion-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
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
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="container-fluid">
                
                <!-- Header -->
                <?php include 'includes/header.php'; ?>

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-0">
                                    <?= $es_docente ? 'Mis Unidades Didácticas' : 'Gestión de Unidades Didácticas' ?>
                                </h4>
                                <p class="mb-0 text-muted">
                                    <?= $es_docente 
                                        ? 'Organiza el contenido de tus cursos en unidades temáticas' 
                                        : 'Organiza el contenido de los cursos en unidades temáticas' 
                                    ?>
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUnidad">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Unidad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 opacity-75">
                                        <?= $es_docente ? 'Mis Unidades' : 'Total Unidades' ?>
                                    </h6>
                                    <h3 class="mb-0 fw-bold"><?= $total_unidades ?></h3>
                                </div>
                                <i class="ti ti-book-2 stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 opacity-75">Publicadas</h6>
                                    <h3 class="mb-0 fw-bold"><?= $unidades_publicadas ?></h3>
                                </div>
                                <i class="ti ti-checks stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 opacity-75">En Borrador</h6>
                                    <h3 class="mb-0 fw-bold"><?= $unidades_borrador ?></h3>
                                </div>
                                <i class="ti ti-file-text stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 opacity-75">Total Lecciones</h6>
                                    <h3 class="mb-0 fw-bold"><?= $total_lecciones ?></h3>
                                </div>
                                <i class="ti ti-file-certificate stats-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-<?= $es_docente ? '5' : '4' ?>">
                                <label class="form-label">Curso</label>
                                <select class="form-select" id="filtroCurso">
                                    <option value="">Todos los cursos</option>
                                    <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-<?= $es_docente ? '3' : '3' ?>">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="PUBLICADO">Publicado</option>
                                    <option value="BORRADOR">Borrador</option>
                                </select>
                            </div>
                            <div class="col-md-<?= $es_docente ? '4' : '3' ?>">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarUnidad" placeholder="Buscar por título...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()" title="Limpiar Filtros">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <?php if (!$es_docente): ?>
                                    <button type="button" class="btn btn-outline-info flex-fill" onclick="exportarUnidades()" title="Exportar">
                                        <i class="ti ti-download"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Unidades -->
                <div class="row" id="unidadesContainer">
                    <?php if (empty($unidades)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="ti ti-info-circle me-2"></i>
                                <?= $es_docente 
                                    ? 'No tienes unidades didácticas creadas. Crea una unidad para organizar el contenido de tus cursos.' 
                                    : 'No hay unidades didácticas registradas en el sistema.' 
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unidades as $unidad): 
                            $configuraciones = json_decode($unidad['configuraciones'], true) ?: [];
                            $estado = $configuraciones['estado'] ?? 'BORRADOR';
                            $fecha_inicio = $configuraciones['fecha_inicio'] ?? null;
                            $fecha_fin = $configuraciones['fecha_fin'] ?? null;
                            
                            // Calcular progreso
                            $progreso = 0;
                            if ($fecha_inicio && $fecha_fin) {
                                $inicio = strtotime($fecha_inicio);
                                $fin = strtotime($fecha_fin);
                                $hoy = time();
                                
                                if ($hoy >= $inicio && $hoy <= $fin) {
                                    $total = $fin - $inicio;
                                    $transcurrido = $hoy - $inicio;
                                    $progreso = min(100, ($transcurrido / $total) * 100);
                                } elseif ($hoy > $fin) {
                                    $progreso = 100;
                                }
                            }
                        ?>
                            <div class="col-md-6 mb-3 unidad-item" 
                                 data-curso="<?= $unidad['curso_id'] ?>" 
                                 data-estado="<?= $estado ?>">
                                <div class="card unidad-card">
                                    <div class="unidad-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="unidad-orden"><?= $unidad['orden'] ?></div>
                                                <div>
                                                    <h5 class="mb-1"><?= htmlspecialchars($unidad['titulo']) ?></h5>
                                                    <p class="mb-0 opacity-75 small curso-info">
                                                        <i class="ti ti-book me-1"></i>
                                                        <?= htmlspecialchars($unidad['curso_nombre']) ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="badge estado-badge <?= $estado === 'PUBLICADO' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $estado ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <?php if ($unidad['descripcion']): ?>
                                            <p class="text-muted small mb-3">
                                                <?= htmlspecialchars(substr($unidad['descripcion'], 0, 120)) ?>
                                                <?= strlen($unidad['descripcion']) > 120 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge lecciones-badge bg-primary">
                                                        <i class="ti ti-file-text me-1"></i>
                                                        <?= $unidad['total_lecciones'] ?> Lecciones
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge lecciones-badge bg-info">
                                                        <i class="ti ti-clipboard-check me-1"></i>
                                                        <?= $unidad['lecciones_evaluacion'] ?> Evaluaciones
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($fecha_inicio && $fecha_fin): ?>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small class="fecha-info">
                                                        <i class="ti ti-calendar me-1"></i>
                                                        <?= date('d/m/Y', strtotime($fecha_inicio)) ?>
                                                    </small>
                                                    <small class="fecha-info">
                                                        <i class="ti ti-calendar-event me-1"></i>
                                                        <?= date('d/m/Y', strtotime($fecha_fin)) ?>
                                                    </small>
                                                </div>
                                                <div class="progress progress-thin">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $progreso ?>%" 
                                                         aria-valuenow="<?= $progreso ?>" 
                                                         aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted"><?= round($progreso) ?>% completado</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarUnidad(<?= $unidad['id'] ?>)">
                                                <i class="ti ti-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="gestionarLecciones(<?= $unidad['id'] ?>)">
                                                <i class="ti ti-list-details"></i> Lecciones
                                            </button>
                                            <?php if (!$es_docente): ?>
                                            <!-- Solo admin puede eliminar -->
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarUnidad(<?= $unidad['id'] ?>)">
                                                <i class="ti ti-trash"></i> Eliminar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Distribución por Curso -->
                <?php if (!empty($unidades_por_curso)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Distribución de Unidades por Curso</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($unidades_por_curso as $curso => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?= htmlspecialchars($curso) ?></span>
                                <span class="badge bg-primary"><?= $count ?> unidades</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
    <?php include 'modales/unidades/modal_agregar.php'; ?>
    <?php include 'modales/unidades/modal_editar.php'; ?>
    <?php include 'modales/unidades/modal_lecciones.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const cursos = <?= json_encode($cursos) ?>;
        const esDocente = <?= $es_docente ? 'true' : 'false' ?>;

        $(document).ready(function() {
            // Filtros personalizados
            $('#filtroCurso, #filtroEstado').on('change', aplicarFiltros);
            $('#buscarUnidad').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const cursoFiltro = $('#filtroCurso').val();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarUnidad').val().toLowerCase();

            $('.unidad-item').each(function() {
                const item = $(this);
                const curso = item.data('curso').toString();
                const estado = item.data('estado');
                const texto = item.text().toLowerCase();

                let mostrar = true;

                if (cursoFiltro && curso !== cursoFiltro) {
                    mostrar = false;
                }

                if (estadoFiltro && estado !== estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                item.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroCurso, #filtroEstado').val('');
            $('#buscarUnidad').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarUnidad(id) {
            mostrarCarga();
            
            fetch('modales/unidades/procesar_unidades.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicionUnidad(data.unidad);
                    $('#modalEditarUnidad').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la unidad');
            });
        }

        function gestionarLecciones(id) {
            window.location.href = `lecciones.php?unidad_id=${id}`;
        }

        <?php if (!$es_docente): ?>
        function eliminarUnidad(id) {
            Swal.fire({
                title: '¿Eliminar Unidad?',
                text: 'Esta acción también eliminará todas las lecciones asociadas. Esta operación no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarEliminarUnidad(id);
                }
            });
        }

        function ejecutarEliminarUnidad(id) {
            mostrarCarga();

            fetch('modales/unidades/procesar_unidades.php', {
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
                mostrarError('Error al eliminar la unidad');
            });
        }

        function exportarUnidades() {
            window.open('reportes/exportar_unidades.php', '_blank');
        }
        <?php endif; ?>

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