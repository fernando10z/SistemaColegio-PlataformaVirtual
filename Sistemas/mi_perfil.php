<?php 
require_once 'conexion/bd.php';
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener información completa del usuario
try {
    $sql_usuario = "SELECT u.*,
                           r.nombre as rol_nombre
                    FROM usuarios u
                    INNER JOIN roles r ON u.rol_id = r.id
                    WHERE u.id = :usuario_id AND u.activo = 1";
    
    $stmt = $conexion->prepare($sql_usuario);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        die("No se encontró información del usuario. Contacta con el administrador.");
    }
    
} catch (PDOException $e) {
    die("Error al cargar datos del usuario: " . $e->getMessage());
}

// Calcular estadísticas generales
try {
    // Calcular días desde la creación
    $fecha_registro = new DateTime($usuario['fecha_creacion']);
    $fecha_actual = new DateTime();
    $dias_registrado = $fecha_actual->diff($fecha_registro)->days;
    
    // Contar total de accesos (si existe tabla de logs)
    $total_accesos = 0;
    try {
        $sql_accesos = "SELECT COUNT(*) as total FROM logs_acceso 
                        WHERE usuario_id = :usuario_id";
        $stmt_accesos = $conexion->prepare($sql_accesos);
        $stmt_accesos->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt_accesos->execute();
        $total_accesos = $stmt_accesos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (PDOException $e) {
        $total_accesos = 0;
    }
    
} catch (Exception $e) {
    $dias_registrado = 0;
    $total_accesos = 0;
}

// Procesar datos básicos
$nombre_completo = trim($usuario['nombres'] . ' ' . $usuario['apellidos']);
$foto_perfil = !empty($usuario['foto_url']) ? $usuario['foto_url'] : '../assets/images/profile/user-default.jpg';
$ultimo_acceso = !empty($usuario['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Primer acceso';
$rol_nombre = $usuario['rol_nombre'] ?? 'Usuario';

// Procesar datos adicionales si existen
$datos_adicionales = [];
if (!empty($usuario['datos_adicionales'])) {
    $datos_adicionales = json_decode($usuario['datos_adicionales'], true) ?? [];
}

// Información de contacto adicional
$direccion = $datos_adicionales['direccion'] ?? $usuario['direccion'] ?? 'No registrada';
$telefono_alternativo = $datos_adicionales['telefono_alternativo'] ?? '';
$fecha_nacimiento = $datos_adicionales['fecha_nacimiento'] ?? '';

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil - ANDRÉS AVELINO CÁCERES</title>
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
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
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
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        .profile-header {
            text-align: center;
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
            border-radius: 14px;
            margin-bottom: 2rem;
        }

        .profile-avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .avatar-edit-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .avatar-edit-btn:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #A0D7BB 0%, #C0E7D5 100%);
        }

        .avatar-edit-btn i {
            color: white;
            font-size: 1.2rem;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            background: linear-gradient(45deg, #B4E7CE, #D4F1E8);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .profile-email {
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
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
            background: linear-gradient(135deg, #A8D8EA 0%, #C8E6F5 100%);
        }

        .stat-box.tertiary {
            background: linear-gradient(135deg, #C7CEEA 0%, #E1E5F5 100%);
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

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        .info-row:hover {
            background: #f8f9fa;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
            text-align: right;
        }

        .badge-status {
            padding: 0.4rem 0.9rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-activo {
            background: linear-gradient(45deg, #4CAF50, #81C784);
            color: white;
        }

        /* Modal personalizado */
        .modal-content {
            border-radius: 14px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
            color: white;
            border-radius: 14px 14px 0 0;
            border: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .preview-container {
            text-align: center;
            margin: 1.5rem 0;
        }

        .preview-image {
            max-width: 250px;
            max-height: 250px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #B4E7CE;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: block;
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
            border: 2px dashed #B4E7CE;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            color: #2c3e50;
            font-weight: 600;
        }

        .file-upload-label:hover {
            background: linear-gradient(135deg, #D4F1E8 0%, #E8F5E9 100%);
            border-color: #A0D7BB;
        }

        .file-upload-label i {
            font-size: 2rem;
            color: #B4E7CE;
            display: block;
            margin-bottom: 0.5rem;
        }

        #file-upload-input {
            display: none;
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
            background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #A0D7BB 0%, #C0E7D5 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(180, 231, 206, 0.4);
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
                        <i class="ti ti-user-circle me-2"></i>
                        Mi Perfil
                    </h4>
                    <p>Información personal y configuración de tu cuenta en el sistema.</p>
                </div>

                <!-- Cabecera del Perfil -->
                <div class="profile-header">
                    <div class="profile-avatar-container">
                        <img src="modales/<?= htmlspecialchars($foto_perfil) ?>" 
                             alt="Foto de Perfil" 
                             class="profile-avatar" 
                             id="profileAvatarImg">
                        <div class="avatar-edit-btn" onclick="cambiarFotoPerfil()">
                            <i class="ti ti-camera"></i>
                        </div>
                    </div>
                    <h3 class="profile-name"><?= htmlspecialchars($nombre_completo) ?></h3>
                    <span class="profile-role">
                        <i class="ti ti-shield-check me-2"></i>
                        <?= htmlspecialchars($rol_nombre) ?>
                    </span>
                    <div class="profile-email">
                        <i class="ti ti-mail me-1"></i>
                        <?= htmlspecialchars($usuario['email']) ?>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?= $dias_registrado ?></div>
                        <div class="stat-label">
                            <i class="ti ti-calendar me-1"></i>
                            Días en el Sistema
                        </div>
                    </div>
                    <div class="stat-box secondary">
                        <div class="stat-number"><?= $total_accesos ?></div>
                        <div class="stat-label">
                            <i class="ti ti-login me-1"></i>
                            Total de Accesos
                        </div>
                    </div>
                    <div class="stat-box tertiary">
                        <div class="stat-number">
                            <?= !empty($usuario['ultimo_acceso']) ? date('d', strtotime($usuario['ultimo_acceso'])) : '0' ?>
                        </div>
                        <div class="stat-label">
                            <i class="ti ti-clock me-1"></i>
                            Último Acceso (Día)
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Información Personal -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <span>
                                    <i class="ti ti-user"></i>
                                    Información Personal
                                </span>
                            </div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-user"></i>
                                        Nombres
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['nombres']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-user"></i>
                                        Apellidos
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['apellidos']) ?></span>
                                </div>
                                <?php if (!empty($usuario['dni'])): ?>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-id"></i>
                                        DNI
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['dni']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($fecha_nacimiento)): ?>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-calendar"></i>
                                        Fecha de Nacimiento
                                    </span>
                                    <span class="info-value">
                                        <?= date('d/m/Y', strtotime($fecha_nacimiento)) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($direccion) && $direccion !== 'No registrada'): ?>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-map-pin"></i>
                                        Dirección
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($direccion) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <span>
                                    <i class="ti ti-phone"></i>
                                    Información de Contacto
                                </span>
                            </div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-mail"></i>
                                        Email Principal
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['email']) ?></span>
                                </div>
                                <?php if (!empty($usuario['telefono'])): ?>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-phone"></i>
                                        Teléfono
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['telefono']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($telefono_alternativo)): ?>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-phone-plus"></i>
                                        Teléfono Alternativo
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($telefono_alternativo) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Cuenta -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <span>
                                    <i class="ti ti-settings"></i>
                                    Información de la Cuenta
                                </span>
                            </div>
                            <div class="info-card-body">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-shield-check"></i>
                                        Rol del Usuario
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($rol_nombre) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-user-check"></i>
                                        Nombre de Usuario
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($usuario['username'] ?? $usuario['email']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-calendar-plus"></i>
                                        Fecha de Registro
                                    </span>
                                    <span class="info-value">
                                        <?= !empty($usuario['fecha_creacion']) ? date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) : 'No disponible' ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-login"></i>
                                        Último Acceso
                                    </span>
                                    <span class="info-value"><?= htmlspecialchars($ultimo_acceso) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="ti ti-shield-check"></i>
                                        Estado de Cuenta
                                    </span>
                                    <span class="badge-status badge-activo">
                                        <i class="ti ti-check me-1"></i>
                                        ACTIVA
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <span>
                                    <i class="ti ti-info-circle"></i>
                                    Información Adicional
                                </span>
                            </div>
                            <div class="info-card-body">
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong>Importante:</strong> Si necesitas actualizar tu información personal (nombres, apellidos, email, teléfono, etc.), por favor contacta con el administrador del sistema.
                                </div>
                                <div class="alert alert-success">
                                    <i class="ti ti-camera me-2"></i>
                                    <strong>Foto de Perfil:</strong> Puedes actualizar tu foto de perfil haciendo clic en el ícono de cámara sobre tu imagen.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Cambiar Foto de Perfil -->
    <?php include 'modales/mi_perfil/modal_cambiar_foto.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const usuario_id = <?= $usuario_id ?>;

        function cambiarFotoPerfil() {
            $('#modalCambiarFoto').modal('show');
        }

        // Preview de imagen al seleccionar archivo
        $('#file-upload-input').on('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validar tipo de archivo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    mostrarAlerta('error', 'Tipo de archivo no válido', 'Por favor selecciona una imagen (JPG, PNG o GIF)');
                    $(this).val('');
                    return;
                }
                
                // Validar tamaño (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    mostrarAlerta('error', 'Archivo muy grande', 'La imagen no debe superar los 5MB');
                    $(this).val('');
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-image').attr('src', e.target.result);
                    $('#preview-container').show();
                    $('#no-preview-text').hide();
                };
                reader.readAsDataURL(file);
                
                // Actualizar texto del label
                $('#file-upload-label-text').text(file.name);
            }
        });
    </script>
</body>
</html>