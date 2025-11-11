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

// Obtener todas las fichas médicas con información del estudiante
try {
    $sql = "SELECT 
                fm.*,
                e.codigo_estudiante,
                e.nombres as estudiante_nombres,
                e.apellidos as estudiante_apellidos,
                e.foto_url as estudiante_foto,
                e.fecha_nacimiento,
                s.grado,
                s.seccion,
                n.nombre as nivel_nombre,
                u.nombres as usuario_nombres,
                u.apellidos as usuario_apellidos
            FROM fichas_medicas fm
            INNER JOIN estudiantes e ON fm.estudiante_id = e.id
            LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
            LEFT JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
            LEFT JOIN usuarios u ON fm.usuario_actualiza = u.id
            ORDER BY fm.fecha_actualizacion DESC, e.apellidos ASC";
    $stmt_fichas = $conexion->prepare($sql);
    $stmt_fichas->execute();
    $fichas_medicas = $stmt_fichas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $fichas_medicas = [];
    $error_fichas = "Error al cargar fichas médicas: " . $e->getMessage();
}

// Obtener estudiantes sin ficha médica para el modal de crear
try {
    $sql_estudiantes = "SELECT 
                            e.id,
                            e.codigo_estudiante,
                            CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
                            e.fecha_nacimiento,
                            s.grado,
                            s.seccion,
                            n.nombre as nivel_nombre
                        FROM estudiantes e
                        LEFT JOIN fichas_medicas fm ON e.id = fm.estudiante_id
                        LEFT JOIN matriculas m ON e.id = m.estudiante_id AND m.activo = 1
                        LEFT JOIN secciones s ON m.seccion_id = s.id
                        LEFT JOIN niveles_educativos n ON s.nivel_id = n.id
                        WHERE e.activo = 1 AND fm.id IS NULL
                        ORDER BY e.apellidos ASC";
    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->execute();
    $estudiantes_sin_ficha = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes_sin_ficha = [];
}

// Calcular estadísticas
$total_fichas = count($fichas_medicas);
$fichas_vigentes = 0;
$con_alergias = 0;
$con_condiciones_cronicas = 0;

foreach ($fichas_medicas as $ficha) {
    if ($ficha['vigente'] == 1) {
        $fichas_vigentes++;
    }
    
    $historial = json_decode($ficha['historial_medico'], true);
    if (!empty($historial['alergias_conocidas']) && $historial['alergias_conocidas'] !== 'Ninguna') {
        $con_alergias++;
    }
    if (!empty($historial['enfermedades_cronicas']) && $historial['enfermedades_cronicas'] !== 'Ninguna') {
        $con_condiciones_cronicas++;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fichas Médicas - <?php echo $nombre; ?></title>
    <?php
        $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="../<?php echo $favicon; ?>">
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

        .ficha-card {
            border-left: 4px solid;
            background: #ffffff;
        }

        .ficha-card.vigente { border-left-color: #B4E5D4; }
        .ficha-card.vencida { border-left-color: #FFD4B4; }

        .ficha-header {
            background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .estudiante-nombre {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .estado-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-vigente { background: #B4E5D4; color: #006400; }
        .badge-vencida { background: #FFD4B4; color: #8B4513; }
        .badge-alerta {
            background: #FFE5B4;
            color: #8b6914;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .dato-medico {
            padding: 0.6rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dato-medico:last-child {
            border-bottom: none;
        }

        .dato-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: #FFE5F0;
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
        .color-stats-4 { color: #D4A5D4; }

        .tipo-sangre-badge {
            background: linear-gradient(135deg, #FFE5F0, #FFD4E5);
            color: #8B0000;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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

        .foto-estudiante {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #FFE5F0;
        }

        .contacto-item {
            background: #F5F5F5;
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
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
                                <h4 class="fw-bold mb-0">
                                    <i class="ti ti-file-medical me-2"></i>
                                    Fichas Médicas
                                </h4>
                                <p class="mb-0 text-muted">Gestión de datos médicos y contactos de emergencia</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearFicha">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Ficha Médica
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-1">
                                <?= $total_fichas ?>
                            </div>
                            <div class="stats-label">Total Fichas</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $fichas_vigentes ?>
                            </div>
                            <div class="stats-label">Fichas Vigentes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $con_alergias ?>
                            </div>
                            <div class="stats-label">Con Alergias</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $con_condiciones_cronicas ?>
                            </div>
                            <div class="stats-label">Condiciones Crónicas</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Vigente</option>
                                    <option value="0">Vencida</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
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

                <!-- Lista de Fichas Médicas -->
                <div class="row" id="fichasContainer">
                    <?php if (empty($fichas_medicas)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="ti ti-file-medical"></i>
                                <h5 class="mt-3">No hay fichas médicas registradas</h5>
                                <p>Comienza creando la primera ficha médica para un estudiante</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCrearFicha">
                                    <i class="ti ti-plus me-2"></i>
                                    Crear Primera Ficha
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($fichas_medicas as $ficha): 
                            $datos_medicos = json_decode($ficha['datos_medicos'], true) ?: [];
                            $historial = json_decode($ficha['historial_medico'], true) ?: [];
                            $contactos = json_decode($ficha['contactos_emergencia'], true) ?: [];
                            $medico = json_decode($ficha['medico_tratante'], true) ?: [];
                            
                            $estado_class = $ficha['vigente'] == 1 ? 'vigente' : 'vencida';
                            $estado_text = $ficha['vigente'] == 1 ? 'VIGENTE' : 'VENCIDA';
                            
                            $tiene_alergias = !empty($historial['alergias_conocidas']) && $historial['alergias_conocidas'] !== 'Ninguna';
                            
                            $tipo_sangre = $datos_medicos['tipo_sangre'] ?? 'N/A';
                            $peso = $datos_medicos['peso_kg'] ?? 0;
                            $talla = $datos_medicos['talla_cm'] ?? 0;
                            $imc = $datos_medicos['imc'] ?? 0;
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4 ficha-card-wrapper" 
                                 data-estado="<?= $ficha['vigente'] ?>" 
                                 data-nombre="<?= strtolower($ficha['estudiante_apellidos'] . ' ' . $ficha['estudiante_nombres']) ?>">
                                <div class="card ficha-card <?= $estado_class ?> h-100">
                                    <div class="ficha-header">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                <img src="<?= !empty($ficha['estudiante_foto']) ? $ficha['estudiante_foto'] : '../assets/images/profile/user-1.jpg' ?>" 
                                                     class="foto-estudiante" alt="Estudiante">
                                                <div>
                                                    <div class="estudiante-nombre">
                                                        <?= htmlspecialchars($ficha['estudiante_apellidos']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($ficha['estudiante_nombres']) ?></small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-info text-white">
                                                            <?= htmlspecialchars($ficha['codigo_estudiante']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <!-- Nivel/Grado -->
                                        <div class="mb-3">
                                            <small class="text-muted d-block">
                                                <i class="ti ti-school me-1"></i>
                                                <?= htmlspecialchars($ficha['nivel_nombre'] ?? 'N/A') ?> - 
                                                <?= htmlspecialchars($ficha['grado'] ?? '') ?> "<?= htmlspecialchars($ficha['seccion'] ?? '') ?>"
                                            </small>
                                        </div>

                                        <!-- Estado -->
                                        <div class="mb-3">
                                            <span class="estado-badge badge-<?= $estado_class ?>">
                                                <?= $estado_text ?>
                                            </span>
                                            <small class="text-muted ms-2">
                                                Actualizado: <?= date('d/m/Y', strtotime($ficha['fecha_actualizacion'])) ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Tipo de Sangre -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 fw-bold">
                                                <i class="ti ti-droplet me-1"></i>
                                                Tipo de Sangre
                                            </h6>
                                            <span class="tipo-sangre-badge">
                                                <?= htmlspecialchars($tipo_sangre) ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Datos Básicos -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 fw-bold">
                                                <i class="ti ti-ruler me-1"></i>
                                                Datos Físicos
                                            </h6>
                                            <div class="dato-medico">
                                                <div class="dato-icon">⚖️</div>
                                                <div class="flex-grow-1">
                                                    <small><strong>Peso:</strong> <?= number_format($peso, 1) ?> kg</small>
                                                </div>
                                            </div>
                                            <div class="dato-medico">
                                                <div class="dato-icon">📏</div>
                                                <div class="flex-grow-1">
                                                    <small><strong>Talla:</strong> <?= number_format($talla, 0) ?> cm</small>
                                                </div>
                                            </div>
                                            <div class="dato-medico">
                                                <div class="dato-icon">📊</div>
                                                <div class="flex-grow-1">
                                                    <small><strong>IMC:</strong> <?= number_format($imc, 1) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Alergias -->
                                        <?php if ($tiene_alergias): ?>
                                            <div class="alert alert-warning py-2 px-3 mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="ti ti-alert-triangle"></i>
                                                    <div>
                                                        <small class="fw-bold d-block">⚠️ ALERTA DE ALERGIA</small>
                                                        <small><?= htmlspecialchars($historial['alergias_conocidas']) ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Contacto de Emergencia -->
                                        <?php if (!empty($contactos)): 
                                            $contacto_principal = null;
                                            foreach ($contactos as $contacto) {
                                                if (isset($contacto['es_principal']) && $contacto['es_principal']) {
                                                    $contacto_principal = $contacto;
                                                    break;
                                                }
                                            }
                                            if (!$contacto_principal && count($contactos) > 0) {
                                                $contacto_principal = $contactos[0];
                                            }
                                        ?>
                                            <?php if ($contacto_principal): ?>
                                                <div class="mb-3">
                                                    <h6 class="mb-2 fw-bold">
                                                        <i class="ti ti-phone me-1"></i>
                                                        Contacto Emergencia
                                                    </h6>
                                                    <div class="contacto-item">
                                                        <small class="fw-bold d-block"><?= htmlspecialchars($contacto_principal['nombre'] ?? 'N/A') ?></small>
                                                        <small class="text-muted"><?= htmlspecialchars($contacto_principal['parentesco'] ?? 'N/A') ?></small>
                                                        <small class="d-block">📱 <?= htmlspecialchars($contacto_principal['telefono'] ?? 'N/A') ?></small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-footer bg-light">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetallesFicha(<?= $ficha['id'] ?>)" 
                                                    title="Ver Detalles">
                                                <i class="ti ti-eye"></i> Detalles
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="editarFicha(<?= $ficha['id'] ?>)" 
                                                    title="Editar">
                                                <i class="ti ti-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarFicha(<?= $ficha['id'] ?>)" 
                                                    title="Eliminar">
                                                <i class="ti ti-trash"></i> Eliminar
                                            </button>
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
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Incluir Modales -->
    <?php include 'modales/fichas_medicas/modal_crear.php'; ?>
    <?php include 'modales/fichas_medicas/modal_detalles.php'; ?>
    <?php include 'modales/fichas_medicas/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroEstado, #buscarEstudiante').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarEstudiante').val().toLowerCase();

            $('.ficha-card-wrapper').each(function() {
                const card = $(this);
                const estado = card.data('estado');
                const nombre = card.data('nombre');

                let mostrar = true;

                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                card.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroEstado, #buscarEstudiante').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function verDetallesFicha(id) {
            mostrarCarga();
            
            fetch('modales/fichas_medicas/procesar_fichas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=detalles_completos&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosDetalles(data.ficha);
                    $('#modalDetallesFicha').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la ficha');
            });
        }

        function editarFicha(id) {
            mostrarCarga();
            
            fetch('modales/fichas_medicas/procesar_fichas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.ficha);
                    $('#modalEditarFicha').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos de la ficha');
            });
        }

        function eliminarFicha(id) {
            Swal.fire({
                title: '¿Eliminar esta ficha médica?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCarga();
                    
                    fetch('modales/fichas_medicas/procesar_fichas.php', {
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
                        mostrarError('Error al eliminar la ficha');
                    });
                }
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