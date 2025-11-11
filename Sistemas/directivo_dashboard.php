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

// ==================== FILTROS ====================
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$filtro_tipo = $_GET['tipo_filtro'] ?? 'mes';

// Ajustar fechas según el tipo de filtro
switch ($filtro_tipo) {
    case 'dia':
        $filtro_fecha_inicio = $filtro_fecha_fin = date('Y-m-d');
        break;
    case 'mes':
        $filtro_fecha_inicio = date('Y-m-01');
        $filtro_fecha_fin = date('Y-m-t');
        break;
    case 'anio':
        $filtro_fecha_inicio = date('Y-01-01');
        $filtro_fecha_fin = date('Y-12-31');
        break;
}

// ==================== BIBLIOTECA: KPIs ====================
try {
    // Total material bibliográfico
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM material_bibliografico WHERE activo = 1");
    $stmt->execute();
    $total_material = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ejemplares disponibles
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM ejemplares WHERE estado = 'DISPONIBLE'");
    $stmt->execute();
    $ejemplares_disponibles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Préstamos activos
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM prestamos_biblioteca WHERE estado = 'ACTIVO'");
    $stmt->execute();
    $prestamos_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Préstamos vencidos
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM prestamos_biblioteca WHERE estado = 'VENCIDO'");
    $stmt->execute();
    $prestamos_vencidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_material = $ejemplares_disponibles = $prestamos_activos = $prestamos_vencidos = 0;
}

// ==================== COMEDOR: KPIs ====================
try {
    // Cuentas activas
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM cuentas_comedor WHERE activo = 1");
    $stmt->execute();
    $cuentas_activas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Saldo total
    $stmt = $conexion->prepare("SELECT SUM(saldo) as total FROM cuentas_comedor WHERE activo = 1");
    $stmt->execute();
    $saldo_total = round($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0, 2);
    
    // Transacciones del período
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total, SUM(JSON_EXTRACT(detalles, '$.monto')) as monto_total
        FROM transacciones_comedor t
        INNER JOIN cuentas_comedor c ON t.cuenta_id = c.id
        WHERE DATE(JSON_EXTRACT(t.detalles, '$.fecha')) BETWEEN :inicio AND :fin
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $data_comedor = $stmt->fetch(PDO::FETCH_ASSOC);
    $transacciones_periodo = $data_comedor['total'] ?? 0;
    $monto_transacciones = round($data_comedor['monto_total'] ?? 0, 2);
} catch (PDOException $e) {
    $cuentas_activas = $saldo_total = $transacciones_periodo = $monto_transacciones = 0;
}

// ==================== TRANSPORTE: KPIs ====================
try {
    // Vehículos activos
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM vehiculos_transporte WHERE activo = 1 AND estado = 'ACTIVO'");
    $stmt->execute();
    $vehiculos_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Rutas activas
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM rutas_transporte WHERE activo = 1");
    $stmt->execute();
    $rutas_activas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Asignaciones activas
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM asignaciones_transporte WHERE activo = 1");
    $stmt->execute();
    $asignaciones_transporte = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Registros de asistencia del período
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total
        FROM asistencia_transporte
        WHERE fecha BETWEEN :inicio AND :fin
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $asistencia_transporte = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $vehiculos_activos = $rutas_activas = $asignaciones_transporte = $asistencia_transporte = 0;
}

// ==================== ENFERMERÍA: KPIs ====================
try {
    // Atenciones del período
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total
        FROM atenciones_medicas
        WHERE fecha_atencion BETWEEN :inicio AND :fin
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $atenciones_periodo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Fichas médicas vigentes
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM fichas_medicas WHERE vigente = 1");
    $stmt->execute();
    $fichas_vigentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Inventario activo
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM inventario_enfermeria WHERE activo = 1");
    $stmt->execute();
    $items_inventario = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Stock bajo (menos de 10 unidades)
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total
        FROM inventario_enfermeria
        WHERE activo = 1 AND JSON_EXTRACT(inventario, '$.stock_actual') < 10
    ");
    $stmt->execute();
    $stock_bajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $atenciones_periodo = $fichas_vigentes = $items_inventario = $stock_bajo = 0;
}

// ==================== GRÁFICO 1: PRÉSTAMOS POR TIPO DE USUARIO ====================
try {
    $stmt = $conexion->prepare("
        SELECT tipo_usuario, COUNT(*) as total
        FROM prestamos_biblioteca
        WHERE JSON_EXTRACT(datos_prestamo, '$.fecha_prestamo') BETWEEN :inicio AND :fin
        GROUP BY tipo_usuario
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $prestamos_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $prestamos_tipo = [];
}

// ==================== GRÁFICO 2: ATENCIONES MÉDICAS POR TIPO ====================
try {
    $stmt = $conexion->prepare("
        SELECT JSON_EXTRACT(datos_atencion, '$.tipo') as tipo, COUNT(*) as total
        FROM atenciones_medicas
        WHERE fecha_atencion BETWEEN :inicio AND :fin
        GROUP BY tipo
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $atenciones_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $atenciones_tipo = [];
}

// ==================== GRÁFICO 3: TRANSACCIONES COMEDOR POR TIPO ====================
try {
    $stmt = $conexion->prepare("
        SELECT tipo, COUNT(*) as total, SUM(JSON_EXTRACT(detalles, '$.monto')) as monto
        FROM transacciones_comedor
        WHERE DATE(JSON_EXTRACT(detalles, '$.fecha')) BETWEEN :inicio AND :fin
        GROUP BY tipo
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $transacciones_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $transacciones_tipo = [];
}

// ==================== GRÁFICO 4: ESTADO DE EJEMPLARES ====================
try {
    $stmt = $conexion->prepare("
        SELECT estado, COUNT(*) as total
        FROM ejemplares
        GROUP BY estado
    ");
    $stmt->execute();
    $ejemplares_estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ejemplares_estado = [];
}

// ==================== TABLA RESUMEN ====================
try {
    // Últimas atenciones médicas
    $stmt = $conexion->prepare("
        SELECT 
            am.fecha_atencion,
            am.hora_atencion,
            e.nombres,
            e.apellidos,
            JSON_EXTRACT(am.datos_atencion, '$.tipo') as tipo,
            JSON_EXTRACT(am.datos_atencion, '$.motivo_consulta') as motivo
        FROM atenciones_medicas am
        INNER JOIN estudiantes e ON am.estudiante_id = e.id
        WHERE am.fecha_atencion BETWEEN :inicio AND :fin
        ORDER BY am.fecha_atencion DESC, am.hora_atencion DESC
        LIMIT 10
    ");
    $stmt->execute([':inicio' => $filtro_fecha_inicio, ':fin' => $filtro_fecha_fin]);
    $ultimas_atenciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ultimas_atenciones = [];
}

// ==================== CONVERTIR A JSON ====================
$json_prestamos = json_encode($prestamos_tipo);
$json_atenciones = json_encode($atenciones_tipo);
$json_transacciones = json_encode($transacciones_tipo);
$json_ejemplares = json_encode($ejemplares_estado);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Servicios | ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            background-color: #FFFFFF;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 50%, #E5FFE5 100%);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .module-section {
            background: #F5F5F5;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .module-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid;
        }

        .module-title.biblioteca { border-bottom-color: #B3E5FC; color: #01579B; }
        .module-title.comedor { border-bottom-color: #C8E6C9; color: #1B5E20; }
        .module-title.transporte { border-bottom-color: #FFE5B4; color: #E65100; }
        .module-title.enfermeria { border-bottom-color: #F8BBD0; color: #880E4F; }

        .kpi-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 1.2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }

        .kpi-card.kpi-1 { border-left-color: #B3E5FC; }
        .kpi-card.kpi-2 { border-left-color: #81D4FA; }
        .kpi-card.kpi-3 { border-left-color: #4FC3F7; }
        .kpi-card.kpi-4 { border-left-color: #29B6F6; }
        
        .kpi-card.kpi-5 { border-left-color: #C8E6C9; }
        .kpi-card.kpi-6 { border-left-color: #A5D6A7; }
        .kpi-card.kpi-7 { border-left-color: #81C784; }
        .kpi-card.kpi-8 { border-left-color: #66BB6A; }

        .kpi-card.kpi-9 { border-left-color: #FFE5B4; }
        .kpi-card.kpi-10 { border-left-color: #FFCC80; }
        .kpi-card.kpi-11 { border-left-color: #FFB74D; }
        .kpi-card.kpi-12 { border-left-color: #FFA726; }

        .kpi-card.kpi-13 { border-left-color: #F8BBD0; }
        .kpi-card.kpi-14 { border-left-color: #F48FB1; }
        .kpi-card.kpi-15 { border-left-color: #EC407A; }
        .kpi-card.kpi-16 { border-left-color: #E91E63; }

        .kpi-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .kpi-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-icon {
            font-size: 2rem;
            opacity: 0.15;
            position: absolute;
            right: 0.8rem;
            top: 0.8rem;
        }

        .chart-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #E5F0FF;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .filter-card {
            background: linear-gradient(135deg, #E5F0FF, #FFE5F0);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn-filter {
            background-color: #B3E5FC;
            color: #01579B;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            background-color: #81D4FA;
            color: #01579B;
        }

        .btn-filter.active {
            background-color: #01579B;
            color: #FFFFFF;
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, #E5F0FF, #FFE5F0);
            padding: 0.8rem;
            font-weight: 600;
            color: #333;
            border: none;
            font-size: 0.85rem;
        }

        .table-modern tbody tr {
            background: #FFFFFF;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .table-modern tbody tr:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table-modern tbody td {
            padding: 0.8rem;
            border: none;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .no-data i {
            font-size: 3rem;
            color: #DDD;
            margin-bottom: 0.5rem;
        }

        .alert-stock {
            background: #FFEBEE;
            border-left: 4px solid #EF5350;
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
                                    <span class="badge bg-primary fs-2 rounded-4 lh-sm">Sistema AAC</span>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </header>

                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bold mb-2">
                                <i class="ti ti-building-community me-2"></i>
                                Dashboard de Servicios Institucionales
                            </h3>
                            <p class="mb-0 text-muted">Biblioteca • Comedor • Transporte • Enfermería</p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-1"><?= htmlspecialchars($usuario_nombre) ?></h5>
                            <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filter-card">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Período</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="tipo_filtro" value="dia" 
                                        class="btn btn-filter <?= $filtro_tipo == 'dia' ? 'active' : '' ?>">
                                    Hoy
                                </button>
                                <button type="submit" name="tipo_filtro" value="mes" 
                                        class="btn btn-filter <?= $filtro_tipo == 'mes' ? 'active' : '' ?>">
                                    Este Mes
                                </button>
                                <button type="submit" name="tipo_filtro" value="anio" 
                                        class="btn btn-filter <?= $filtro_tipo == 'anio' ? 'active' : '' ?>">
                                    Este Año
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" 
                                   value="<?= $filtro_fecha_inicio ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" 
                                   value="<?= $filtro_fecha_fin ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-filter w-100">
                                <i class="ti ti-filter me-2"></i>Aplicar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- MÓDULO BIBLIOTECA -->
                <div class="module-section">
                    <h4 class="module-title biblioteca">
                        <i class="ti ti-books me-2"></i>Biblioteca
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="kpi-card kpi-1 position-relative">
                                <i class="ti ti-book kpi-icon"></i>
                                <div class="kpi-value" style="color: #01579B;"><?= $total_material ?></div>
                                <div class="kpi-label">Material Total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-2 position-relative">
                                <i class="ti ti-book-2 kpi-icon"></i>
                                <div class="kpi-value" style="color: #0277BD;"><?= $ejemplares_disponibles ?></div>
                                <div class="kpi-label">Disponibles</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-3 position-relative">
                                <i class="ti ti-bookmarks kpi-icon"></i>
                                <div class="kpi-value" style="color: #0288D1;"><?= $prestamos_activos ?></div>
                                <div class="kpi-label">Préstamos Activos</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-4 position-relative">
                                <i class="ti ti-alert-circle kpi-icon"></i>
                                <div class="kpi-value" style="color: #E53935;"><?= $prestamos_vencidos ?></div>
                                <div class="kpi-label">Préstamos Vencidos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO COMEDOR -->
                <div class="module-section">
                    <h4 class="module-title comedor">
                        <i class="ti ti-tools-kitchen-2 me-2"></i>Comedor
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="kpi-card kpi-5 position-relative">
                                <i class="ti ti-users kpi-icon"></i>
                                <div class="kpi-value" style="color: #1B5E20;"><?= $cuentas_activas ?></div>
                                <div class="kpi-label">Cuentas Activas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-6 position-relative">
                                <i class="ti ti-wallet kpi-icon"></i>
                                <div class="kpi-value" style="color: #2E7D32;">S/ <?= $saldo_total ?></div>
                                <div class="kpi-label">Saldo Total</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-7 position-relative">
                                <i class="ti ti-receipt kpi-icon"></i>
                                <div class="kpi-value" style="color: #388E3C;"><?= $transacciones_periodo ?></div>
                                <div class="kpi-label">Transacciones</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-8 position-relative">
                                <i class="ti ti-cash kpi-icon"></i>
                                <div class="kpi-value" style="color: #43A047;">S/ <?= $monto_transacciones ?></div>
                                <div class="kpi-label">Monto Total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO TRANSPORTE -->
                <div class="module-section">
                    <h4 class="module-title transporte">
                        <i class="ti ti-bus me-2"></i>Transporte
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="kpi-card kpi-9 position-relative">
                                <i class="ti ti-car kpi-icon"></i>
                                <div class="kpi-value" style="color: #E65100;"><?= $vehiculos_activos ?></div>
                                <div class="kpi-label">Vehículos Activos</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-10 position-relative">
                                <i class="ti ti-route kpi-icon"></i>
                                <div class="kpi-value" style="color: #EF6C00;"><?= $rutas_activas ?></div>
                                <div class="kpi-label">Rutas Activas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-11 position-relative">
                                <i class="ti ti-users-group kpi-icon"></i>
                                <div class="kpi-value" style="color: #F57C00;"><?= $asignaciones_transporte ?></div>
                                <div class="kpi-label">Asignaciones</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-12 position-relative">
                                <i class="ti ti-clipboard-check kpi-icon"></i>
                                <div class="kpi-value" style="color: #FB8C00;"><?= $asistencia_transporte ?></div>
                                <div class="kpi-label">Reg. Asistencia</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO ENFERMERÍA -->
                <div class="module-section">
                    <h4 class="module-title enfermeria">
                        <i class="ti ti-heart-pulse me-2"></i>Enfermería
                    </h4>
                    
                    <?php if ($stock_bajo > 0): ?>
                        <div class="alert-stock">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Alerta:</strong> Hay <?= $stock_bajo ?> productos con stock bajo (menos de 10 unidades)
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="kpi-card kpi-13 position-relative">
                                <i class="ti ti-stethoscope kpi-icon"></i>
                                <div class="kpi-value" style="color: #880E4F;"><?= $atenciones_periodo ?></div>
                                <div class="kpi-label">Atenciones</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-14 position-relative">
                                <i class="ti ti-file-text kpi-icon"></i>
                                <div class="kpi-value" style="color: #AD1457;"><?= $fichas_vigentes ?></div>
                                <div class="kpi-label">Fichas Vigentes</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-15 position-relative">
                                <i class="ti ti-package kpi-icon"></i>
                                <div class="kpi-value" style="color: #C2185B;"><?= $items_inventario ?></div>
                                <div class="kpi-label">Items Inventario</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card kpi-16 position-relative">
                                <i class="ti ti-alert-triangle kpi-icon"></i>
                                <div class="kpi-value" style="color: #E91E63;"><?= $stock_bajo ?></div>
                                <div class="kpi-label">Stock Bajo</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row g-3 mb-3">
                    <!-- Préstamos por Tipo -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-pie me-2"></i>
                                Préstamos por Tipo de Usuario
                            </h5>
                            <?php if (!empty($prestamos_tipo)): ?>
                                <div class="chart-container">
                                    <canvas id="chartPrestamos"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-pie"></i>
                                    <p>No hay préstamos en el período</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Atenciones Médicas -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-bar me-2"></i>
                                Atenciones Médicas por Tipo
                            </h5>
                            <?php if (!empty($atenciones_tipo)): ?>
                                <div class="chart-container">
                                    <canvas id="chartAtenciones"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-bar"></i>
                                    <p>No hay atenciones en el período</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gráficos 2 -->
                <div class="row g-3 mb-3">
                    <!-- Transacciones Comedor -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-donut me-2"></i>
                                Transacciones Comedor por Tipo
                            </h5>
                            <?php if (!empty($transacciones_tipo)): ?>
                                <div class="chart-container">
                                    <canvas id="chartTransacciones"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-donut"></i>
                                    <p>No hay transacciones en el período</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estado Ejemplares -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <h5 class="chart-title">
                                <i class="ti ti-chart-pie-2 me-2"></i>
                                Estado de Ejemplares
                            </h5>
                            <?php if (!empty($ejemplares_estado)): ?>
                                <div class="chart-container">
                                    <canvas id="chartEjemplares"></canvas>
                                </div>
                            <?php else: ?>
                                <div class="no-data">
                                    <i class="ti ti-chart-pie-2"></i>
                                    <p>No hay ejemplares registrados</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabla Últimas Atenciones -->
                <div class="chart-card">
                    <h5 class="chart-title">
                        <i class="ti ti-list me-2"></i>
                        Últimas Atenciones Médicas
                    </h5>
                    
                    <?php if (!empty($ultimas_atenciones)): ?>
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estudiante</th>
                                        <th>Tipo de Atención</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_atenciones as $atencion): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($atencion['fecha_atencion'])) ?></td>
                                            <td><?= date('H:i', strtotime($atencion['hora_atencion'])) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($atencion['apellidos']) ?></strong>, 
                                                <?= htmlspecialchars($atencion['nombres']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars(trim((string)($atencion['tipo'] ?? ''), '"')) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(substr(trim((string)($atencion['motivo'] ?? ''), '"'), 0, 50)) ?>...</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="ti ti-clipboard-off"></i>
                            <p>No hay atenciones en el período seleccionado</p>
                        </div>
                    <?php endif; ?>
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


    <script>
        // Datos de PHP a JavaScript
        const dataPrestamos = <?= $json_prestamos ?>;
        const dataAtenciones = <?= $json_atenciones ?>;
        const dataTransacciones = <?= $json_transacciones ?>;
        const dataEjemplares = <?= $json_ejemplares ?>;

        // Colores pastel
        const coloresPastel = [
            '#B3E5FC', '#C8E6C9', '#FFE5B4', '#F8BBD0', 
            '#E1BEE7', '#FFCCBC', '#D1C4E9', '#B2DFDB'
        ];

        // ========== GRÁFICO 1: PRÉSTAMOS ==========
        <?php if (!empty($prestamos_tipo)): ?>
        const ctxPrestamos = document.getElementById('chartPrestamos');
        if (ctxPrestamos) {
            new Chart(ctxPrestamos, {
                type: 'doughnut',
                data: {
                    labels: dataPrestamos.map(d => d.tipo_usuario),
                    datasets: [{
                        data: dataPrestamos.map(d => d.total),
                        backgroundColor: ['#B3E5FC', '#C8E6C9', '#FFE5B4', '#F8BBD0'],
                        borderColor: '#FFFFFF',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 2: ATENCIONES ==========
        <?php if (!empty($atenciones_tipo)): ?>
        const ctxAtenciones = document.getElementById('chartAtenciones');
        if (ctxAtenciones) {
            new Chart(ctxAtenciones, {
                type: 'bar',
                data: {
                    labels: dataAtenciones.map(d => d.tipo?.replace(/"/g, '') || 'Sin tipo'),
                    datasets: [{
                        label: 'Cantidad',
                        data: dataAtenciones.map(d => d.total),
                        backgroundColor: '#F8BBD0',
                        borderColor: '#F48FB1',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 3: TRANSACCIONES ==========
        <?php if (!empty($transacciones_tipo)): ?>
        const ctxTransacciones = document.getElementById('chartTransacciones');
        if (ctxTransacciones) {
            new Chart(ctxTransacciones, {
                type: 'doughnut',
                data: {
                    labels: dataTransacciones.map(d => d.tipo),
                    datasets: [{
                        data: dataTransacciones.map(d => d.total),
                        backgroundColor: ['#C8E6C9', '#FFE5B4', '#B3E5FC', '#E1BEE7'],
                        borderColor: '#FFFFFF',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // ========== GRÁFICO 4: EJEMPLARES ==========
        <?php if (!empty($ejemplares_estado)): ?>
        const ctxEjemplares = document.getElementById('chartEjemplares');
        if (ctxEjemplares) {
            new Chart(ctxEjemplares, {
                type: 'pie',
                data: {
                    labels: dataEjemplares.map(d => d.estado),
                    datasets: [{
                        data: dataEjemplares.map(d => d.total),
                        backgroundColor: coloresPastel,
                        borderColor: '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: { font: { size: 11 } }
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>