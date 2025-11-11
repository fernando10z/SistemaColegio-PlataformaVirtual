<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}
require_once 'conexion/bd.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['username'];

// Obtener todas las atenciones médicas con información del estudiante
try {
    $sql = "SELECT 
                am.*,
                e.codigo_estudiante,
                e.nombres as estudiante_nombres,
                e.apellidos as estudiante_apellidos,
                e.foto_url as estudiante_foto,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                u.nombres as enfermero_nombres,
                u.apellidos as enfermero_apellidos
            FROM atenciones_medicas am
            INNER JOIN estudiantes e ON am.estudiante_id = e.id
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            LEFT JOIN usuarios u ON am.enfermero_atiende = u.id
            ORDER BY am.fecha_atencion DESC, am.hora_atencion DESC";
    $stmt_atenciones = $conexion->prepare($sql);
    $stmt_atenciones->execute();
    $atenciones_medicas = $stmt_atenciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $atenciones_medicas = [];
    $error_atenciones = "Error al cargar atenciones: " . $e->getMessage();
}

// Obtener estudiantes activos
try {
    $sql_estudiantes = "SELECT 
                            e.id,
                            e.codigo_estudiante,
                            CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                            s.grado,
                            s.seccion,
                            n.nombre as nivel_nombre
                        FROM estudiantes e
                        LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
                        LEFT JOIN secciones s ON m.seccion_id = s.id
                        LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
                        WHERE e.activo = 1
                        ORDER BY e.apellidos ASC";
    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->execute();
    $estudiantes_activos = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes_activos = [];
}

// Obtener inventario activo
try {
    $sql_inventario = "SELECT 
                            id,
                            nombre_producto,
                            tipo,
                            stock_actual
                       FROM inventario_enfermeria
                       WHERE activo = 1 AND stock_actual > 0
                       ORDER BY nombre_producto ASC";
    $stmt_inventario = $conexion->prepare($sql_inventario);
    $stmt_inventario->execute();
    $inventario_disponible = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $inventario_disponible = [];
}

// Calcular estadísticas
$total_atenciones = count($atenciones_medicas);
$atenciones_hoy = 0;
$medicamentos_administrados = 0;

$fecha_hoy = date('Y-m-d');

foreach ($atenciones_medicas as $atencion) {
    if ($atencion['fecha_atencion'] == $fecha_hoy) {
        $atenciones_hoy++;
    }
    
    $tratamiento = json_decode($atencion['tratamiento'], true);
    if (!empty($tratamiento)) {
        $medicamentos_administrados++;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atenciones y Control Médico | ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .atencion-card {
            border-left: 4px solid #B4E5D4;
            background: #ffffff;
        }

        .header-section {
            background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .color-stats-1 { color: #FFB6C1; }
        .color-stats-2 { color: #B4E5D4; }
        .color-stats-3 { color: #FFE5B4; }

        .btn-action {
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }

        .btn-register {
            background-color: #B3E5FC;
            color: #01579B;
        }

        .btn-register:hover {
            background-color: #81D4FA;
            color: #01579B;
        }

        .btn-medicine {
            background-color: #C8E6C9;
            color: #1B5E20;
        }

        .btn-medicine:hover {
            background-color: #A5D6A7;
            color: #1B5E20;
        }

        .btn-view {
            background-color: #F8BBD0;
            color: #880E4F;
        }

        .btn-view:hover {
            background-color: #F48FB1;
            color: #880E4F;
        }

        .foto-estudiante {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #FFE5F0;
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

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
        }

        .tipo-atencion-badge {
            background: linear-gradient(135deg, #E5F0FF, #D4E5FF);
            color: #01579B;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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
                        <div class="header-section">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">
                                        <i class="ti ti-heart-pulse me-2"></i>
                                        Atenciones y Control Médico
                                    </h4>
                                    <p class="mb-0 text-muted">Gestión de atenciones médicas e inventario de enfermería</p>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="ti ti-user"></i> <?= htmlspecialchars($usuario_nombre) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-1">
                                <?= $total_atenciones ?>
                            </div>
                            <div class="stats-label">Total Atenciones</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $atenciones_hoy ?>
                            </div>
                            <div class="stats-label">Atenciones Hoy</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $medicamentos_administrados ?>
                            </div>
                            <div class="stats-label">Con Medicamentos</div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <button class="btn btn-action btn-register w-100" data-bs-toggle="modal" data-bs-target="#modalRegistrarAtencion">
                            <i class="ti ti-plus-circle me-2"></i> Registrar Atención Médica
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-action btn-medicine w-100" data-bs-toggle="modal" data-bs-target="#modalAdministrarMedicamento">
                            <i class="ti ti-pill me-2"></i> Administrar Medicamento
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-action btn-view w-100" onclick="cargarAtenciones()">
                            <i class="ti ti-list me-2"></i> Ver Atenciones Registradas
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="filtroFecha">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar Estudiante</label>
                                <input type="text" class="form-control" id="buscarEstudiante" placeholder="Buscar estudiante...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Atenciones -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="ti ti-table me-2"></i>
                            Listado de Atenciones
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaAtenciones">
                                <thead style="background: linear-gradient(135deg, #E5F0FF, #FFE5F0);">
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Tipo Atención</th>
                                        <th>Motivo</th>
                                        <th>Enfermero</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyAtenciones">
                                    <?php if (empty($atenciones_medicas)): ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="empty-state">
                                                    <i class="ti ti-clipboard"></i>
                                                    <h5 class="mt-3">No hay atenciones registradas</h5>
                                                    <p>Comienza registrando la primera atención médica</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($atenciones_medicas as $atencion): ?>
                                            <tr class="atencion-row" 
                                                data-fecha="<?= $atencion['fecha_atencion'] ?>" 
                                                data-nombre="<?= strtolower($atencion['estudiante_apellidos'] . ' ' . $atencion['estudiante_nombres']) ?>">
                                                <td><?= htmlspecialchars($atencion['id']) ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?= !empty($atencion['estudiante_foto']) ? $atencion['estudiante_foto'] : '../assets/images/profile/user-1.jpg' ?>" 
                                                             class="foto-estudiante" alt="Estudiante">
                                                        <div>
                                                            <div class="fw-bold">
                                                                <?= htmlspecialchars($atencion['estudiante_apellidos']) ?>
                                                            </div>
                                                            <small class="text-muted">
                                                                <?= htmlspecialchars($atencion['estudiante_nombres']) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($atencion['fecha_atencion'])) ?></td>
                                                <td><?= date('H:i', strtotime($atencion['hora_atencion'])) ?></td>
                                                <td>
                                                    <span class="tipo-atencion-badge">
                                                        <?= htmlspecialchars($atencion['tipo_atencion']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars(substr($atencion['motivo_consulta'], 0, 50)) ?>...</td>
                                                <td>
                                                    <small>
                                                        <?= htmlspecialchars($atencion['enfermero_nombres'] ?? 'N/A') ?>
                                                        <?= htmlspecialchars($atencion['enfermero_apellidos'] ?? '') ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetallesAtencion(<?= $atencion['id'] ?>)" 
                                                            title="Ver Detalles">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
    <?php include 'modales/atenciones_medicas/modal_registrar.php'; ?>
    <?php include 'modales/atenciones_medicas/modal_administrar.php'; ?>
    <?php include 'modales/atenciones_medicas/modal_detalles.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroFecha, #buscarEstudiante').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const fechaFiltro = $('#filtroFecha').val();
            const busqueda = $('#buscarEstudiante').val().toLowerCase();

            $('.atencion-row').each(function() {
                const row = $(this);
                const fecha = row.data('fecha');
                const nombre = row.data('nombre');

                let mostrar = true;

                if (fechaFiltro && fecha !== fechaFiltro) {
                    mostrar = false;
                }

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                row.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroFecha, #buscarEstudiante').val('');
            aplicarFiltros();
        }

        function cargarAtenciones() {
            aplicarFiltros();
            $('html, body').animate({
                scrollTop: $("#tablaAtenciones").offset().top - 100
            }, 500);
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function verDetallesAtencion(id) {
            mostrarCarga();
            
            fetch('modales/atenciones_medicas/procesar_atenciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosDetalles(data.atencion);
                    $('#modalDetallesAtencion').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la atención');
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