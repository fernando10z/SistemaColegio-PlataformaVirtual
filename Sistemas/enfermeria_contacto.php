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

// Obtener información del estudiante usando usuario_id
try {
    $sql_estudiante = "SELECT e.*, 
                              u.nombres as usuario_nombres, 
                              u.apellidos as usuario_apellidos,
                              u.email as usuario_email,
                              u.telefono as usuario_telefono
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
    
} catch (PDOException $e) {
    die("Error al cargar datos del estudiante: " . $e->getMessage());
}

// Obtener información del personal de enfermería activo
try {
    $sql_personal = "SELECT id, 
                            nombre_completo,
                            cargo,
                            telefono,
                            email,
                            foto,
                            horario_atencion,
                            especialidad
                     FROM personal_enfermeria
                     WHERE activo = 1
                     ORDER BY FIELD(cargo, 'JEFE', 'ENFERMERA', 'AUXILIAR'), nombre_completo";
    
    $stmt_personal = $conexion->prepare($sql_personal);
    $stmt_personal->execute();
    $personal_enfermeria = $stmt_personal->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $personal_enfermeria = [];
    $error_personal = "Error al cargar personal de enfermería: " . $e->getMessage();
}

// Obtener historial de atenciones médicas del estudiante (últimos 6 meses)
try {
    $sql_atenciones = "SELECT am.*,
                              pe.nombre_completo as enfermera,
                              pe.cargo as cargo_enfermera,
                              DATE_FORMAT(am.fecha_atencion, '%d/%m/%Y') as fecha_formateada,
                              DATE_FORMAT(am.hora_atencion, '%H:%i') as hora_formateada
                       FROM atenciones_medicas am
                       INNER JOIN personal_enfermeria pe ON am.personal_id = pe.id
                       WHERE am.estudiante_id = :estudiante_id
                       AND am.fecha_atencion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                       ORDER BY am.fecha_atencion DESC, am.hora_atencion DESC
                       LIMIT 10";
    
    $stmt_atenciones = $conexion->prepare($sql_atenciones);
    $stmt_atenciones->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_atenciones->execute();
    $atenciones = $stmt_atenciones->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $atenciones = [];
    $error_atenciones = "Error al cargar historial de atenciones: " . $e->getMessage();
}

// Obtener citas programadas pendientes
try {
    $sql_citas = "SELECT cm.*,
                         pe.nombre_completo as enfermera,
                         pe.cargo as cargo_enfermera,
                         DATE_FORMAT(cm.fecha_cita, '%d/%m/%Y') as fecha_formateada,
                         DATE_FORMAT(cm.hora_cita, '%H:%i') as hora_formateada
                  FROM citas_medicas cm
                  INNER JOIN personal_enfermeria pe ON cm.personal_id = pe.id
                  WHERE cm.estudiante_id = :estudiante_id
                  AND cm.estado = 'PENDIENTE'
                  AND cm.fecha_cita >= CURDATE()
                  ORDER BY cm.fecha_cita ASC, cm.hora_cita ASC";
    
    $stmt_citas = $conexion->prepare($sql_citas);
    $stmt_citas->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
    $stmt_citas->execute();
    $citas_pendientes = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $citas_pendientes = [];
}

// Calcular estadísticas
$total_atenciones = count($atenciones);
$citas_programadas = count($citas_pendientes);

// Contar tipos de atención
$tipos_atencion = [];
foreach ($atenciones as $atencion) {
    $tipo = $atencion['tipo_atencion'] ?? 'NO_ESPECIFICADO';
    $tipos_atencion[$tipo] = ($tipos_atencion[$tipo] ?? 0) + 1;
}

// Obtener horario de atención de enfermería (general)
$horario_general = "Lunes a Viernes: 7:00 AM - 3:00 PM";
$telefono_emergencia = "(01) 234-5678";
$email_enfermeria = "enfermeria@aac.edu.pe";

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacto Enfermería - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
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
        
        /* Optimizaciones adicionales */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* CSS para left-sidebar */
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

        .left-sidebar > div {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            margin: 0 !important;
            padding: 0 !important;
        }

        .left-sidebar .brand-logo {
            flex-shrink: 0;
            padding: 20px 24px;
            margin: 0 !important;
            border-bottom: 1px solid #e9ecef;
        }

        .left-sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            margin: 0 !important;
            padding: 0 !important;
        }

        .left-sidebar #sidebarnav {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none;
        }

        .left-sidebar .sidebar-item {
            margin: 0 !important;
            padding: 0 !important;
        }

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

        .left-sidebar .sidebar-link:hover {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
        }

        .left-sidebar .sidebar-link.active {
            background-color: #e7f1ff !important;
            color: #0d6efd !important;
            font-weight: 500;
        }

        @media (max-width: 1199.98px) {
            .left-sidebar {
                margin-left: -270px;
                transition: margin-left 0.25s ease;
            }
            
            .left-sidebar.show {
                margin-left: 0;
            }
        }
    </style>
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        .welcome-card {
            background: linear-gradient(135deg, #FFB6C1 0%, #FFD4DC 100%);
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

        .info-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .info-card-header {
            background: linear-gradient(135deg, #FFB6C1 0%, #FFD4DC 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: linear-gradient(135deg, #FFB6C1 0%, #FFD4DC 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
        }

        .stat-box.secondary {
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
        }

        .stat-box.tertiary {
            background: linear-gradient(135deg, #C7CEEA 0%, #E1E5F5 100%);
        }

        .stat-box.quaternary {
            background: linear-gradient(135deg, #FFDDC1 0%, #FFE9D6 100%);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            opacity: 0.95;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .personal-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .personal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .personal-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFB6C1, #FFD4DC);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: 700;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }

        .personal-info h5 {
            margin-bottom: 0.25rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .cargo-badge {
            background: linear-gradient(45deg, #FFB6C1, #FFD4DC);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .cargo-badge.jefe {
            background: linear-gradient(45deg, #FF6B6B, #FF8E8E);
        }

        .cargo-badge.enfermera {
            background: linear-gradient(45deg, #4CAF50, #81C784);
        }

        .cargo-badge.auxiliar {
            background: linear-gradient(45deg, #2196F3, #64B5F6);
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .atencion-item {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid #FFB6C1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .atencion-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .atencion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .atencion-fecha {
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tipo-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .tipo-consulta {
            background: linear-gradient(45deg, #2196F3, #64B5F6);
            color: white;
        }

        .tipo-emergencia {
            background: linear-gradient(45deg, #FF6B6B, #FF8E8E);
            color: white;
        }

        .tipo-seguimiento {
            background: linear-gradient(45deg, #FFA726, #FFB74D);
            color: white;
        }

        .tipo-medicacion {
            background: linear-gradient(45dx, #4CAF50, #81C784);
            color: white;
        }

        .atencion-detalle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .cita-card {
            background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid #4CAF50;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .cita-fecha {
            font-weight: 700;
            color: #2e7d32;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cita-info {
            color: #558b2f;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .emergencia-box {
            background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid #FF6B6B;
        }

        .emergencia-box h5 {
            color: #c62828;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .emergencia-box .telefono {
            font-size: 1.5rem;
            font-weight: 700;
            color: #d32f2f;
            margin-bottom: 0.5rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #FFB6C1 0%, #FFD4DC 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #FFA0AD 0%, #FFBEC8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 182, 193, 0.4);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);
            color: white;
        }

        .btn-success-custom:hover {
            background: linear-gradient(135deg, #45a049 0%, #66BB6A 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }

        .btn-info-custom {
            background: linear-gradient(135deg, #2196F3 0%, #64B5F6 100%);
            color: white;
        }

        .btn-info-custom:hover {
            background: linear-gradient(135deg, #1976D2 0%, #42A5F5 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
        }

        .horario-box {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .horario-box i {
            font-size: 2rem;
            color: #1976D2;
            margin-bottom: 0.5rem;
        }

        .horario-box .horario-text {
            font-weight: 700;
            color: #1565C0;
            font-size: 1.1rem;
        }

        .no-registros {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .no-registros i {
            font-size: 4rem;
            color: #d0d0d0;
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
                        <i class="ti ti-heart-plus me-2"></i>
                        Contacto con Enfermería
                    </h4>
                    <p>Información del personal de enfermería, solicitud de citas y tu historial de atenciones médicas.</p>
                </div>

                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?= $total_atenciones ?></div>
                        <div class="stat-label">
                            <i class="ti ti-clipboard-check me-1"></i>
                            Atenciones Registradas
                        </div>
                    </div>
                    <div class="stat-box secondary">
                        <div class="stat-number"><?= $citas_programadas ?></div>
                        <div class="stat-label">
                            <i class="ti ti-calendar-event me-1"></i>
                            Citas Programadas
                        </div>
                    </div>
                    <div class="stat-box tertiary">
                        <div class="stat-number"><?= count($personal_enfermeria) ?></div>
                        <div class="stat-label">
                            <i class="ti ti-user-heart me-1"></i>
                            Personal Disponible
                        </div>
                    </div>
                    <div class="stat-box quaternary">
                        <div class="stat-number">6</div>
                        <div class="stat-label">
                            <i class="ti ti-clock me-1"></i>
                            Meses de Historial
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Personal de Enfermería -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="ti ti-users"></i>
                                Personal de Enfermería
                            </div>
                            <div class="info-card-body">
                                <?php if (empty($personal_enfermeria)): ?>
                                    <div class="no-registros">
                                        <i class="ti ti-user-off"></i>
                                        <p class="mb-0">No hay personal registrado</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($personal_enfermeria as $personal): 
                                        $iniciales = '';
                                        $nombres = explode(' ', $personal['nombre_completo']);
                                        foreach ($nombres as $nombre) {
                                            if (!empty($nombre)) {
                                                $iniciales .= strtoupper(substr($nombre, 0, 1));
                                            }
                                        }
                                        $iniciales = substr($iniciales, 0, 2);
                                        
                                        $cargo_class = strtolower($personal['cargo']);
                                    ?>
                                    <div class="personal-card">
                                        <div class="d-flex align-items-start">
                                            <div class="personal-avatar">
                                                <?= $iniciales ?>
                                            </div>
                                            <div class="personal-info flex-grow-1">
                                                <h5><?= htmlspecialchars($personal['nombre_completo']) ?></h5>
                                                <span class="cargo-badge <?= $cargo_class ?>">
                                                    <?= htmlspecialchars($personal['cargo']) ?>
                                                </span>
                                                <?php if (!empty($personal['especialidad'])): ?>
                                                    <div class="contact-info">
                                                        <i class="ti ti-stethoscope"></i>
                                                        <span><?= htmlspecialchars($personal['especialidad']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="contact-info">
                                                    <i class="ti ti-phone"></i>
                                                    <span><?= htmlspecialchars($personal['telefono']) ?></span>
                                                </div>
                                                <div class="contact-info">
                                                    <i class="ti ti-mail"></i>
                                                    <span><?= htmlspecialchars($personal['email']) ?></span>
                                                </div>
                                                <?php if (!empty($personal['horario_atencion'])): ?>
                                                    <div class="contact-info">
                                                        <i class="ti ti-clock"></i>
                                                        <span><?= htmlspecialchars($personal['horario_atencion']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-info-custom me-2" 
                                                            onclick="verDetallesPersonal(<?= htmlspecialchars(json_encode($personal)) ?>)">
                                                        <i class="ti ti-eye me-1"></i>
                                                        Ver Detalles
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Información de Contacto y Emergencias -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="ti ti-info-circle"></i>
                                Información de Contacto
                            </div>
                            <div class="info-card-body">
                                <div class="horario-box">
                                    <i class="ti ti-clock"></i>
                                    <div class="horario-text"><?= $horario_general ?></div>
                                </div>
                                
                                <div class="emergencia-box">
                                    <h5>
                                        <i class="ti ti-alert-circle me-2"></i>
                                        Emergencias
                                    </h5>
                                    <div class="telefono">
                                        <i class="ti ti-phone me-2"></i>
                                        <?= $telefono_emergencia ?>
                                    </div>
                                    <p class="mb-0 text-muted">Disponible 24/7</p>
                                </div>

                                <div class="mt-3 text-center">
                                    <button class="btn btn-action btn-success-custom btn-lg w-100" 
                                            onclick="solicitarCita()">
                                        <i class="ti ti-calendar-plus me-2"></i>
                                        Solicitar Cita Médica
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial y Citas -->
                    <div class="col-md-6">
                        <!-- Citas Programadas -->
                        <?php if (!empty($citas_pendientes)): ?>
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="ti ti-calendar-event"></i>
                                Mis Próximas Citas
                            </div>
                            <div class="info-card-body">
                                <?php foreach ($citas_pendientes as $cita): ?>
                                <div class="cita-card">
                                    <div class="cita-fecha">
                                        <i class="ti ti-calendar"></i>
                                        <?= $cita['fecha_formateada'] ?> a las <?= $cita['hora_formateada'] ?>
                                    </div>
                                    <div class="cita-info">
                                        <i class="ti ti-user-heart"></i>
                                        <strong>Con:</strong> <?= htmlspecialchars($cita['enfermera']) ?>
                                    </div>
                                    <?php if (!empty($cita['motivo'])): ?>
                                    <div class="cita-info">
                                        <i class="ti ti-notes"></i>
                                        <strong>Motivo:</strong> <?= htmlspecialchars($cita['motivo']) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="text-end mt-2">
                                        <button class="btn btn-sm btn-primary-custom" 
                                                onclick="verDetalleCita(<?= $cita['id'] ?>)">
                                            <i class="ti ti-eye me-1"></i>
                                            Ver Detalles
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Historial de Atenciones -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <i class="ti ti-clipboard-list"></i>
                                Mi Historial de Atenciones
                            </div>
                            <div class="info-card-body">
                                <?php if (empty($atenciones)): ?>
                                    <div class="no-registros">
                                        <i class="ti ti-clipboard-off"></i>
                                        <p class="mb-0">No tienes atenciones registradas en los últimos 6 meses</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($atenciones as $atencion): 
                                        $tipo = $atencion['tipo_atencion'] ?? 'CONSULTA';
                                        $tipo_class = 'tipo-' . strtolower($tipo);
                                    ?>
                                    <div class="atencion-item">
                                        <div class="atencion-header">
                                            <div class="atencion-fecha">
                                                <i class="ti ti-calendar"></i>
                                                <?= $atencion['fecha_formateada'] ?> - <?= $atencion['hora_formateada'] ?>
                                            </div>
                                            <span class="tipo-badge <?= $tipo_class ?>">
                                                <?= htmlspecialchars($tipo) ?>
                                            </span>
                                        </div>
                                        <div class="atencion-detalle">
                                            <strong>Atendido por:</strong> <?= htmlspecialchars($atencion['enfermera']) ?>
                                        </div>
                                        <?php if (!empty($atencion['motivo_atencion'])): ?>
                                        <div class="atencion-detalle">
                                            <strong>Motivo:</strong> <?= htmlspecialchars($atencion['motivo_atencion']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($atencion['diagnostico'])): ?>
                                        <div class="atencion-detalle">
                                            <strong>Diagnóstico:</strong> <?= htmlspecialchars($atencion['diagnostico']) ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="text-end mt-2">
                                            <button class="btn btn-sm btn-primary-custom" 
                                                    onclick="verDetalleAtencion(<?= $atencion['id'] ?>)">
                                                <i class="ti ti-eye me-1"></i>
                                                Ver Completo
                                            </button>
                                        </div>
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

    <!-- Incluir Modales -->
    <?php include 'modales/enfermeria_contacto/modal_solicitar_cita.php'; ?>
    <?php include 'modales/enfermeria_contacto/modal_detalle_atencion.php'; ?>
    <?php include 'modales/enfermeria_contacto/modal_detalle_personal.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const estudiante_id = <?= $estudiante_id ?>;
        const estudiante_nombre = "<?= htmlspecialchars($estudiante['usuario_nombres'] . ' ' . $estudiante['usuario_apellidos']) ?>";

        function solicitarCita() {
            $('#modalSolicitarCita').modal('show');
        }

        function verDetalleAtencion(atencion_id) {
            // Cargar detalles completos de la atención
            $.ajax({
                url: 'procesar_enfermeria_contacto.php',
                type: 'POST',
                data: {
                    accion: 'obtener_detalle_atencion',
                    atencion_id: atencion_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarDetalleAtencion(response.data);
                    } else {
                        mostrarAlerta('error', 'Error', response.mensaje);
                    }
                },
                error: function() {
                    mostrarAlerta('error', 'Error', 'No se pudo cargar el detalle de la atención');
                }
            });
        }

        function mostrarDetalleAtencion(data) {
            $('#detalleAtencionContenido').html(`
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> ${data.fecha_formateada}</p>
                        <p><strong>Hora:</strong> ${data.hora_formateada}</p>
                        <p><strong>Tipo:</strong> ${data.tipo_atencion}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Atendido por:</strong> ${data.enfermera}</p>
                        <p><strong>Cargo:</strong> ${data.cargo_enfermera}</p>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Motivo de Atención:</strong>
                    <p>${data.motivo_atencion || 'No especificado'}</p>
                </div>
                <div class="mb-3">
                    <strong>Diagnóstico:</strong>
                    <p>${data.diagnostico || 'No especificado'}</p>
                </div>
                <div class="mb-3">
                    <strong>Tratamiento:</strong>
                    <p>${data.tratamiento || 'No especificado'}</p>
                </div>
                ${data.observaciones ? `
                <div class="mb-3">
                    <strong>Observaciones:</strong>
                    <p>${data.observaciones}</p>
                </div>
                ` : ''}
            `);
            $('#modalDetalleAtencion').modal('show');
        }

        function verDetalleCita(cita_id) {
            // Similar a verDetalleAtencion pero para citas
            mostrarAlerta('info', 'Información', 'Función de detalle de cita en desarrollo');
        }

        function verDetallesPersonal(personal) {
            $('#personalNombre').text(personal.nombre_completo);
            $('#personalCargo').text(personal.cargo);
            $('#personalEspecialidad').text(personal.especialidad || 'No especificado');
            $('#personalTelefono').text(personal.telefono);
            $('#personalEmail').text(personal.email);
            $('#personalHorario').text(personal.horario_atencion || 'Consultar');
            
            $('#modalDetallePersonal').modal('show');
        }

        function mostrarAlerta(tipo, titulo, mensaje) {
            Swal.fire({
                icon: tipo,
                title: titulo,
                text: mensaje,
                confirmButtonColor: '#FFB6C1',
                confirmButtonText: 'Entendido'
            });
        }
    </script>
</body>
</html>