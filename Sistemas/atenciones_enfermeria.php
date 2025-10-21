<?php
session_start();
require_once 'conexion/bd.php';
// Obtener lista de estudiantes con sus matrículas activas
try {
    $sql_estudiantes = "SELECT 
        e.id, 
        CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo,
        s.grado,
        s.seccion
    FROM estudiantes e
    INNER JOIN matriculas m ON e.id = m.estudiante_id
    INNER JOIN secciones s ON m.seccion_id = s.id
    WHERE e.activo = 1 AND m.estado = 'MATRICULADO'
    ORDER BY nombre_completo";
    $stmt_estudiantes = $conexion->prepare($sql_estudiantes);
    $stmt_estudiantes->execute();
    $estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes = [];
}

// Obtener inventario de enfermería disponible
try {
    $sql_inventario = "SELECT * FROM inventario_enfermeria WHERE stock_actual > 0 ORDER BY nombre_producto";
    $stmt_inventario = $conexion->prepare($sql_inventario);
    $stmt_inventario->execute();
    $inventario = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $inventario = [];
}

// Obtener atenciones médicas recientes
try {
    $sql_atenciones = "SELECT 
        am.*,
        CONCAT(e.nombres, ' ', e.apellidos) as nombre_estudiante,
        s.grado,
        s.seccion
    FROM atenciones_medicas am
    INNER JOIN estudiantes e ON am.estudiante_id = e.id
    INNER JOIN matriculas m ON e.id = m.estudiante_id
    INNER JOIN secciones s ON m.seccion_id = s.id
    WHERE m.estado = 'MATRICULADO'
    ORDER BY am.fecha_atencion DESC, am.hora_atencion DESC
    LIMIT 50";
    $stmt_atenciones = $conexion->prepare($sql_atenciones);
    $stmt_atenciones->execute();
    $atenciones = $stmt_atenciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $atenciones = [];
}

// Calcular estadísticas
$total_atenciones = count($atenciones);
$atenciones_urgentes = 0;
$atenciones_normales = 0;
$atenciones_hoy = 0;

$fecha_hoy = date('Y-m-d');

foreach ($atenciones as $atencion) {
    if ($atencion['tipo_atencion'] === 'urgente') {
        $atenciones_urgentes++;
    } else {
        $atenciones_normales++;
    }
    
    if ($atencion['fecha_atencion'] === $fecha_hoy) {
        $atenciones_hoy++;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atenciones y Control | ANDRÉS AVELINO CÁCERES</title>
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

        .color-stats-1 { color: #A8D8EA; }
        .color-stats-2 { color: #C1E1C1; }
        .color-stats-3 { color: #FFB6C1; }
        .color-stats-4 { color: #FFFACD; }

        .badge-urgente {
            background-color: #FFB6C1;
            color: #c62828;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-normal {
            background-color: #C1E1C1;
            color: #2e7d32;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .table-custom {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-custom thead {
            background: linear-gradient(135deg, #A8D8EA, #E6E6FA);
            color: #2c3e50;
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

        .modal-header {
            background: linear-gradient(135deg, #FFB6C1, #FFFACD);
            border-radius: 15px 15px 0 0;
            color: #2c3e50;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #E6E6FA;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #A8D8EA;
            box-shadow: 0 0 0 0.2rem rgba(168, 216, 234, 0.25);
        }

        .btn-action {
            background: linear-gradient(135deg, #C1E1C1, #A8D8EA);
            border: none;
            color: #2c3e50;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            background: linear-gradient(135deg, #A8D8EA, #C1E1C1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                                    <i class="ti ti-heart-pulse me-2"></i>
                                    Atenciones y Control de Enfermería
                                </h4>
                                <p class="mb-0 text-muted">Gestión de atenciones médicas y control de inventario</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-action me-2" data-bs-toggle="modal" data-bs-target="#modalNuevaAtencion">
                                    <i class="ti ti-plus me-2"></i>
                                    Nueva Atención
                                </button>
                                <button type="button" class="btn btn-action me-2" data-bs-toggle="modal" data-bs-target="#modalAdministrarMedicamento">
                                    <i class="ti ti-pill me-2"></i>
                                    Medicamento
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
                                <?= $total_atenciones ?>
                            </div>
                            <div class="stats-label">Total Atenciones</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $atenciones_normales ?>
                            </div>
                            <div class="stats-label">Atenciones Normales</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $atenciones_urgentes ?>
                            </div>
                            <div class="stats-label">Atenciones Urgentes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number color-stats-4">
                                <?= $atenciones_hoy ?>
                            </div>
                            <div class="stats-label">Atenciones Hoy</div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Atenciones -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="ti ti-clipboard-pulse me-2"></i>
                            Registro de Atenciones Recientes
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-custom table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estudiante</th>
                                        <th>Grado/Sección</th>
                                        <th>Motivo</th>
                                        <th>Tipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($atenciones)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No hay atenciones registradas</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($atenciones as $atencion): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($atencion['fecha_atencion'])) ?></td>
                                            <td><?= date('H:i', strtotime($atencion['hora_atencion'])) ?></td>
                                            <td><?= htmlspecialchars($atencion['nombre_estudiante']) ?></td>
                                            <td><?= htmlspecialchars($atencion['grado'] . ' - ' . $atencion['seccion']) ?></td>
                                            <td><?= htmlspecialchars($atencion['motivo_consulta']) ?></td>
                                            <td>
                                                <span class="badge-<?= $atencion['tipo_atencion'] ?>">
                                                    <?= strtoupper($atencion['tipo_atencion']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="verDetalle(<?= $atencion['id'] ?>)">
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

    <!-- Modal 1: Nueva Atención -->
    <div class="modal fade" id="modalNuevaAtencion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-plus-circle me-2"></i>Registrar Nueva Atención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevaAtencion" method="POST" action="procesar_atencion.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estudiante</label>
                                <select name="estudiante_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($estudiantes as $est): ?>
                                    <option value="<?= $est['id'] ?>">
                                        <?= htmlspecialchars($est['nombre_completo'] . ' - ' . $est['grado'] . ' ' . $est['seccion']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Atención</label>
                                <select name="tipo_atencion" class="form-select" required>
                                    <option value="normal">Normal</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Motivo de Consulta</label>
                            <textarea name="motivo_consulta" class="form-control" rows="2" required></textarea>
                        </div>

                        <h6 class="mt-4 mb-3">Signos Vitales</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" name="temperatura" class="form-control" step="0.1" placeholder="36.5">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Presión Arterial</label>
                                <input type="text" name="presion_arterial" class="form-control" placeholder="120/80">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Frecuencia Cardíaca</label>
                                <input type="number" name="frecuencia_cardiaca" class="form-control" placeholder="70">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" name="peso" class="form-control" step="0.1" placeholder="45.5">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnóstico / Observaciones</label>
                            <textarea name="diagnostico" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tratamiento Indicado</label>
                            <textarea name="tratamiento" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-action">
                            <i class="ti ti-device-floppy me-2"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Administrar Medicamento -->
    <div class="modal fade" id="modalAdministrarMedicamento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-pill me-2"></i>Administrar Medicamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAdministrarMedicamento" method="POST" action="procesar_medicamento.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Estudiante</label>
                            <select name="estudiante_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($estudiantes as $est): ?>
                                <option value="<?= $est['id'] ?>">
                                    <?= htmlspecialchars($est['nombre_completo']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Medicamento</label>
                            <select name="medicamento_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($inventario as $med): ?>
                                <option value="<?= $med['id'] ?>">
                                    <?= htmlspecialchars($med['nombre_producto'] . ' (Stock: ' . $med['stock_actual'] . ')') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dosis</label>
                                <input type="text" name="dosis" class="form-control" placeholder="1 tableta" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-action">
                            <i class="ti ti-check me-2"></i>Administrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 3: Ver Detalle -->
    <div class="modal fade" id="modalDetalleAtencion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-file-medical me-2"></i>Detalle de Atención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleAtencionContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function verDetalle(atencionId) {
            $('#loadingOverlay').css('display', 'flex');
            
            fetch(`obtener_detalle_atencion.php?id=${atencionId}`)
                .then(response => response.text())
                .then(data => {
                    $('#loadingOverlay').hide();
                    $('#detalleAtencionContent').html(data);
                    $('#modalDetalleAtencion').modal('show');
                })
                .catch(error => {
                    $('#loadingOverlay').hide();
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                });
        }
    </script>
</body>
</html>