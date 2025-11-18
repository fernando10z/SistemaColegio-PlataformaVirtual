<?php
// Si no está definido $usuario o $foto_perfil, cargarlos
if (!isset($usuario) || !isset($foto_perfil)) {
    try {
        if (isset($_SESSION['usuario_id'])) {
            $sql_header = "SELECT u.*, r.nombre as rol_nombre 
                          FROM usuarios u 
                          INNER JOIN roles r ON u.rol_id = r.id 
                          WHERE u.id = :usuario_id AND u.activo = 1";
            $stmt_header = $conexion->prepare($sql_header);
            $stmt_header->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $stmt_header->execute();
            $usuario_header = $stmt_header->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario_header) {
                $nombre_completo = trim($usuario_header['nombres'] . ' ' . $usuario_header['apellidos']);
                $foto_perfil_header = !empty($usuario_header['foto_url']) ? '../Sistemas/modales/' . $usuario_header['foto_url'] : '../assets/images/profile/user-default.jpg';
                $rol_nombre = $usuario_header['rol_nombre'] ?? 'Usuario';
            } else {
                $nombre_completo = 'Usuario';
                $foto_perfil_header = '../assets/images/profile/user-default.jpg';
                $rol_nombre = 'Usuario';
            }
        }
    } catch (PDOException $e) {
        $nombre_completo = 'Usuario';
        $foto_perfil_header = '../assets/images/profile/user-default.jpg';
        $rol_nombre = 'Usuario';
    }
} else {
    // Si ya están definidas, usar esas variables
    $foto_perfil_header = !empty($foto_perfil) ? (strpos($foto_perfil, '../Sistemas/modales/') === 0 ? $foto_perfil : '../Sistemas/modales/' . $foto_perfil) : '../assets/images/profile/user-default.jpg';
}
?>

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
                <li class="nav-item dropdown">
                    <a class="nav-link" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown">
                        <img src="<?= htmlspecialchars($foto_perfil_header) ?>" 
                             alt="<?= htmlspecialchars($nombre_completo ?? 'Usuario') ?>" 
                             width="35" 
                             height="35" 
                             class="rounded-circle"
                             id="headerProfileImg"
                             style="object-fit: cover;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                        <div class="message-body">
                            <div class="px-3 py-2 border-bottom">
                                <h6 class="mb-0"><?= htmlspecialchars($nombre_completo ?? 'Usuario') ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($rol_nombre ?? 'Usuario') ?></small>
                            </div>
                            <a href="mi_perfil.php" class="d-flex align-items-center gap-2 dropdown-item mt-2">
                                <i class="ti ti-user fs-6"></i>
                                <p class="mb-0 fs-3">Mi Perfil</p>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-outline-danger mx-3 mt-2 d-block logout-btn" data-href="includes/logout.php">
                                <i class="ti ti-logout me-1"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>

<style>
/* Estilos para la imagen de perfil del header */
#headerProfileImg {
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
}

#headerProfileImg:hover {
    border-color: #B4E7CE;
    transform: scale(1.05);
}

.dropdown-menu {
    min-width: 250px;
}

.dropdown-menu .border-bottom {
    background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
}
</style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logout con confirmación
    document.querySelectorAll('.logout-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = btn.getAttribute('data-href') || btn.getAttribute('href');
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas cerrar sesión?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});
</script>