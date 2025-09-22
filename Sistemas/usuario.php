<?php 
require_once 'conexion/bd.php';

// Obtener roles para filtros y selects
try {
    $stmt_roles = $conexion->prepare("SELECT id, nombre, descripcion, nivel_acceso FROM roles WHERE activo = 1 ORDER BY nivel_acceso DESC");
    $stmt_roles->execute();
    $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $roles = [];
    $error_message = "Error al cargar roles: " . $e->getMessage();
}

// Obtener usuarios con información de roles
try {
    $sql = "SELECT u.*, r.nombre as rol_nombre, r.nivel_acceso, r.descripcion as rol_descripcion 
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            ORDER BY u.fecha_creacion DESC";
    $stmt_usuarios = $conexion->prepare($sql);
    $stmt_usuarios->execute();
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios = [];
    $error_usuarios = "Error al cargar usuarios: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
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
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .rol-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .usuario-info {
            line-height: 1.2;
        }
        .usuario-info .nombre {
            font-weight: 600;
            color: #495057;
        }
        .usuario-info .email {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .card-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
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
        .password-generated {
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 0.75rem;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'includes/sidebar.php'; ?>

        <div class="body-wrapper" style="top: 20px;">
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
                                <h4 class="fw-bold mb-0">Gestión de Usuarios</h4>
                                <p class="mb-0 text-muted">Administra usuarios del sistema y sus roles</p>
                            </div>
                            <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
                                <i class="ti ti-plus me-2"></i>
                                Nuevo Usuario
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Rol</label>
                                <select class="form-select" id="filtroRol">
                                    <option value="">Todos los roles</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filtrar por Estado</label>
                                <select class="form-select" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre, email, username...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                        <i class="ti ti-refresh me-1"></i>
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lista de Usuarios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaUsuarios">
                                <thead class="table-light">
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Información Personal</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr data-rol="<?= $usuario['rol_id'] ?>" data-estado="<?= $usuario['activo'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $usuario['foto_url'] ?: '../assets/images/profile/user-default.jpg' ?>" 
                                                         class="user-avatar me-2" alt="Avatar">
                                                    <div class="usuario-info">
                                                        <div class="nombre"><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></div>
                                                        <div class="email"><?= htmlspecialchars($usuario['email']) ?></div>
                                                        <small class="text-muted">@<?= htmlspecialchars($usuario['username']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">
                                                        <?= $usuario['documento_tipo'] ?>: <?= htmlspecialchars($usuario['documento_numero']) ?><br>
                                                        Tel: <?= htmlspecialchars($usuario['telefono'] ?: 'No registrado') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge rol-badge 
                                                    <?php 
                                                    switch($usuario['nivel_acceso']) {
                                                        case 10: echo 'bg-danger'; break;
                                                        case 9: echo 'bg-warning'; break;
                                                        case 8: echo 'bg-info'; break;
                                                        case 6: echo 'bg-primary'; break;
                                                        case 5: echo 'bg-success'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                    ?>">
                                                    <?= htmlspecialchars($usuario['rol_nombre'] ?: 'Sin rol') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge status-badge <?= $usuario['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                                <?php if ($usuario['debe_cambiar_password']): ?>
                                                    <br><span class="badge bg-warning status-badge mt-1">Cambiar Password</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= $usuario['ultimo_acceso'] ? 
                                                        date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 
                                                        'Nunca' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($usuario['fecha_creacion'])) ?>
                                                </small>
                                            </td>
                                            <td class="table-actions">
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarUsuario(<?= $usuario['id'] ?>)" 
                                                            title="Editar Usuario">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm <?= $usuario['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                            onclick="toggleEstadoUsuario(<?= $usuario['id'] ?>, <?= $usuario['activo'] ? 'false' : 'true' ?>)" 
                                                            title="<?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?> Usuario">
                                                        <i class="ti <?= $usuario['activo'] ? 'ti-user-off' : 'ti-user-check' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="resetearPassword(<?= $usuario['id'] ?>)" 
                                                            title="Resetear Contraseña">
                                                        <i class="ti ti-key"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
    <?php include 'modales/usuarios/modal_agregar.php'; ?>
    <?php include 'modales/usuarios/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let tablaUsuarios;

        $(document).ready(function() {
            // Inicializar DataTable
            tablaUsuarios = $('#tablaUsuarios').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 5,
                order: [[5, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });

            // Filtros personalizados
            $('#filtroRol, #filtroEstado').on('change', aplicarFiltros);
            $('#buscarUsuario').on('keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const rolFiltro = $('#filtroRol').val();
            const estadoFiltro = $('#filtroEstado').val();
            const busqueda = $('#buscarUsuario').val().toLowerCase();

            $('#tablaUsuarios tbody tr').each(function() {
                const fila = $(this);
                const rol = fila.data('rol');
                const estado = fila.data('estado');
                const texto = fila.text().toLowerCase();

                let mostrar = true;

                // Filtro por rol
                if (rolFiltro && rol != rolFiltro) {
                    mostrar = false;
                }

                // Filtro por estado
                if (estadoFiltro !== '' && estado != estadoFiltro) {
                    mostrar = false;
                }

                // Filtro por texto
                if (busqueda && !texto.includes(busqueda)) {
                    mostrar = false;
                }

                fila.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroRol, #filtroEstado').val('');
            $('#buscarUsuario').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarUsuario(id) {
            mostrarCarga();
            
            fetch('modales/usuarios/procesar_usuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEdicion(data.usuario);
                    $('#modalEditarUsuario').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del usuario');
            });
        }

        function toggleEstadoUsuario(id, nuevoEstado) {
            const accion = nuevoEstado === 'true' ? 'activar' : 'desactivar';
            const mensaje = nuevoEstado === 'true' ? '¿activar' : '¿desactivar';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${mensaje} este usuario?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado === 'true' ? '#198754' : '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarToggleEstado(id, nuevoEstado);
                }
            });
        }

        function ejecutarToggleEstado(id, estado) {
            mostrarCarga();

            fetch('modales/usuarios/procesar_usuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=toggle_estado&id=${id}&estado=${estado}`
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
                mostrarError('Error al cambiar estado del usuario');
            });
        }

        function resetearPassword(id) {
            Swal.fire({
                title: 'Resetear Contraseña',
                text: '¿Deseas generar una nueva contraseña para este usuario?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, generar nueva',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarResetPassword(id);
                }
            });
        }

        function ejecutarResetPassword(id) {
            mostrarCarga();

            fetch('modales/usuarios/procesar_usuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=reset_password&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    Swal.fire({
                        title: '¡Contraseña Reseteada!',
                        html: `<div class="mb-3">La nueva contraseña temporal es:</div>
                               <div class="password-generated">${data.nueva_password}</div>
                               <div class="mt-3 text-muted small">
                                   <i class="ti ti-info-circle"></i> 
                                   El usuario deberá cambiar esta contraseña en su primer acceso
                               </div>`,
                        icon: 'success',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al resetear contraseña');
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

        // Generar password automática
        function generarPassword() {
            const length = 10;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%&*";
            let password = "";
            
            // Asegurar al menos una mayúscula, minúscula, número y símbolo
            password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 26)];
            password += "abcdefghijklmnopqrstuvwxyz"[Math.floor(Math.random() * 26)];
            password += "0123456789"[Math.floor(Math.random() * 10)];
            password += "@#$%&*"[Math.floor(Math.random() * 6)];
            
            // Completar el resto
            for (let i = password.length; i < length; i++) {
                password += charset[Math.floor(Math.random() * charset.length)];
            }
            
            // Mezclar caracteres
            return password.split('').sort(() => 0.5 - Math.random()).join('');
        }
    </script>
</body>
</html>