<?php
// Obtener el rol del usuario de la sesión
$rol_id = $_SESSION['rol_id'] ?? 0;
$nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];
?>

<style>
    :root {
        /* Colores institucionales */
        --primary: #1a5f7a;
        --primary-dark: #134556;
        --primary-light: #2a7a96;
        --accent: #c8102e;
        --accent-hover: #a00d25;
        --gold: #d4af37;
        
        /* Neutrales */
        --sidebar-bg: #ffffff;
        --sidebar-border: #e5e7eb;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --text-muted: #9ca3af;
        
        /* Estados */
        --hover-bg: #f9fafb;
        --active-bg: #eff6ff;
        --active-border: var(--primary);
        --divider: #f3f4f6;
        
        /* Dimensiones */
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 72px;
        --header-height: 72px;
        
        /* Transiciones */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Reset y base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Sidebar Container */
    .left-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: var(--sidebar-bg);
        border-right: 1px solid var(--sidebar-border);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        transition: width var(--transition-base);
    }

    /* Logo/Header Area */
    .brand-logo {
        height: var(--header-height);
        padding: 20px;
        border-bottom: 1px solid var(--sidebar-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .logo-img {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: opacity var(--transition-fast);
    }

    .logo-img:hover {
        opacity: 0.8;
    }

    /* Logo institucional */
    .logo-img::before {
        content: '';
        width: 40px;
        height: 40px;
        background: url('../<?php echo htmlspecialchars($foto); ?>') center/contain no-repeat;
        flex-shrink: 0;
    }

    .logo-text {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        letter-spacing: -0.2px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Close button (mobile) */
    .close-btn {
        width: 32px;
        height: 32px;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        transition: background var(--transition-fast);
        color: var(--text-secondary);
    }

    .close-btn:hover {
        background: var(--hover-bg);
        color: var(--text-primary);
    }

    /* Navigation Container */
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 16px 12px;
    }

    /* Scrollbar personalizado */
    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: var(--divider);
        border-radius: 3px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
    }

    /* Lista principal */
    #sidebarnav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* Category Headers */
    .nav-small-cap {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 12px 8px;
        margin-top: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
    }

    .nav-small-cap:first-child {
        margin-top: 0;
    }

    .nav-small-cap-icon {
        font-size: 16px;
        opacity: 0.7;
    }

    .hide-menu {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Sidebar Items */
    .sidebar-item {
        margin-bottom: 2px;
    }

    /* Links principales */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-secondary);
        font-size: 14px;
        font-weight: 500;
        transition: all var(--transition-fast);
        cursor: pointer;
        position: relative;
    }

    .sidebar-link:hover {
        background: var(--hover-bg);
        color: var(--text-primary);
    }

    .sidebar-link.active,
    .sidebar-link.primary-hover-bg:active {
        background: var(--active-bg);
        color: var(--primary);
        font-weight: 600;
    }


    /* Iconos */
    .sidebar-link iconify-icon {
        font-size: 20px;
        flex-shrink: 0;
        opacity: 0.8;
        transition: opacity var(--transition-fast);
    }

    .sidebar-link:hover iconify-icon,
    .sidebar-link.active iconify-icon {
        opacity: 1;
    }

    /* Arrow para submenús */
    .sidebar-link.has-arrow::after {
        content: '';
        margin-left: auto;
        width: 16px;
        height: 16px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236b7280'%3E%3Cpath d='M5.5 4l4 4-4 4'/%3E%3C/svg%3E") center/contain no-repeat;
        transition: transform var(--transition-fast);
        opacity: 0.6;
    }

    .sidebar-link.has-arrow[aria-expanded="true"]::after {
        transform: rotate(90deg);
    }

    /* Submenús */
    .collapse {
        max-height: 0;
        overflow: hidden;
        transition: max-height var(--transition-base);
    }

    .collapse.show {
        max-height: 1000px;
    }

    .first-level {
        list-style: none;
        padding: 4px 0 8px 0;
        margin: 0;
    }

    .first-level .sidebar-item {
        margin-bottom: 1px;
    }

    .first-level .sidebar-link {
        padding: 8px 12px 8px 44px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .first-level .sidebar-link:hover {
        background: var(--hover-bg);
        color: var(--text-primary);
    }

    .first-level .sidebar-link.active {
        background: transparent;
        color: var(--primary);
        font-weight: 600;
    }

    .first-level .sidebar-link.active::before {
        display: none;
    }

    .first-level .sidebar-link.active::after {
        content: '';
        position: absolute;
        left: 32px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 4px;
        background: var(--primary);
        border-radius: 50%;
    }

    /* Dividers */
    .sidebar-divider {
        display: block;
        height: 1px;
        background: var(--divider);
        margin: 16px 12px;
        border: none;
    }

    /* Estados de carga y transición */
    .sidebar-link:active {
        transform: scale(0.98);
    }

    /* Responsive */
    @media (max-width: 1199px) {
        .left-sidebar {
            transform: translateX(-100%);
        }
        
        .left-sidebar.show {
            transform: translateX(0);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
        }
        
        .close-btn {
            display: flex;
        }
    }

    @media (max-width: 768px) {
        .left-sidebar {
            width: 100%;
            max-width: 320px;
        }
        
        .brand-logo {
            padding: 16px;
            height: 64px;
        }
        
        .logo-text {
            font-size: 14px;
        }
    }

    /* Collapsed state (opcional) */
    .left-sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .left-sidebar.collapsed .logo-text,
    .left-sidebar.collapsed .hide-menu,
    .left-sidebar.collapsed .nav-small-cap span,
    .left-sidebar.collapsed .sidebar-link.has-arrow::after {
        opacity: 0;
        visibility: hidden;
    }

    .left-sidebar.collapsed .nav-small-cap {
        justify-content: center;
    }

    .left-sidebar.collapsed .sidebar-link {
        justify-content: center;
        padding: 10px;
    }

    .left-sidebar.collapsed .first-level .sidebar-link {
        padding-left: 10px;
    }

    /* Animación suave de entrada */
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .sidebar-item {
        animation: slideInLeft 0.3s ease-out backwards;
    }

    .sidebar-item:nth-child(1) { animation-delay: 0.05s; }
    .sidebar-item:nth-child(2) { animation-delay: 0.1s; }
    .sidebar-item:nth-child(3) { animation-delay: 0.15s; }
    .sidebar-item:nth-child(4) { animation-delay: 0.2s; }
    .sidebar-item:nth-child(5) { animation-delay: 0.25s; }

    /* Focus visible para accesibilidad */
    .sidebar-link:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: -2px;
    }

    /* Mejoras de rendimiento */
    .sidebar-nav,
    .collapse {
        will-change: auto;
    }

    .sidebar-link iconify-icon {
        will-change: opacity;
    }
</style>

<!-- Sidebar Start -->
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="./index.php" class="text-nowrap logo-img">
                <span class="logo-text"><?php echo htmlspecialchars($nombre)?></span>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">
                
                <!-- DASHBOARD - TODOS LOS ROLES -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:widget-3-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Dashboard</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="index.php" aria-expanded="false">
                        <span class="d-flex">
                            <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">
                            <?php 
                            switch($rol_id) {
                                case 2: echo 'Panel Director'; break;
                                case 4: echo 'Panel Docente'; break;
                                case 7: echo 'Panel Estudiante'; break;
                                default: echo 'Panel Principal';
                            }
                            ?>
                        </span>
                    </a>
                </li>

                <li><span class="sidebar-divider lg"></span></li>

                <?php if($rol_id == 2): // DIRECTOR - VE TODO ?>
                
                <!-- GESTIÓN ACADÉMICA - SOLO DIRECTOR -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:book-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Gestión Académica</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Catálogos</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="periodos.php">
                                <span class="hide-menu">Períodos Académicos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="niveles.php">
                                <span class="hide-menu">Niveles y Grados</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="areas.php">
                                <span class="hide-menu">Áreas Curriculares</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="secciones.php">
                                <span class="hide-menu">Secciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="malla.php">
                                <span class="hide-menu">Malla Curricular</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:user-id-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Matrícula</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="matricula.php">
                                <span class="hide-menu">Gestión Matrículas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="traslados.php">
                                <span class="hide-menu">Traslados</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:user-speak-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Docentes</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="docente.php">
                                <span class="hide-menu">Gestión Docentes</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="asignaciones.php">
                                <span class="hide-menu">Asignaciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="horarios.php">
                                <span class="hide-menu">Horarios</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li><span class="sidebar-divider lg"></span></li>
                <?php endif; ?>

                <?php if($rol_id == 2 || $rol_id == 4): // DIRECTOR Y DOCENTE ?>
                <!-- EVA/CONTENIDO - DIRECTOR Y DOCENTE -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:display-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">EVA/Contenido</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:book-bookmark-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Cursos</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="cursos.php">
                                <span class="hide-menu">Mis Cursos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="unidades.php">
                                <span class="hide-menu">Unidades</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="lecciones.php">
                                <span class="hide-menu">Lecciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="recursos.php">
                                <span class="hide-menu">Recursos</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:chat-round-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Foros y Anuncios</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="foros.php">
                                <span class="hide-menu">Foros</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="anuncios.php">
                                <span class="hide-menu">Anuncios</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if($rol_id == 7): // ESTUDIANTE ?>
                <!-- CONTENIDO ACADÉMICO - ESTUDIANTE -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:book-bookmark-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Mi Aprendizaje</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="mis_cursos.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:notebook-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Mis Cursos</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="mis_tareas.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:clipboard-check-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Tareas y Actividades</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="mis_calificaciones.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:star-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Mis Calificaciones</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="foros_estudiante.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:chat-dots-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Foros de Clase</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="recursos_estudiante.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:folder-open-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Recursos de Estudio</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if($rol_id == 2): // DIRECTOR Y DOCENTE ?>
                <li><span class="sidebar-divider lg"></span></li>
                <!-- ANALÍTICA Y TABLEROS -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:pie-chart-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Analítica y Tableros</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:graph-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Tableros</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <?php if($rol_id == 4): // Solo docente ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="docente_dashboard.php">
                                <span class="hide-menu">Mi Dashboard</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if($rol_id == 2): // Solo director ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="docente_dashboard.php">
                                <span class="hide-menu">Dashboard Docente</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="estudiante_dashboard.php">
                                <span class="hide-menu">Dashboard Estudiante</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="directivo_dashboard.php">
                                <span class="hide-menu">Dashboard Directivo</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if($rol_id == 2): // SOLO DIRECTOR ?>
                <li><span class="sidebar-divider lg"></span></li>
                <!-- ADMINISTRACIÓN - SOLO DIRECTOR -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:settings-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Administración</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:users-group-two-rounded-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Usuarios y Roles</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="usuario.php">
                                <span class="hide-menu">Gestión Usuarios</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="rolyper.php">
                                <span class="hide-menu">Roles y Permisos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="sedes.php">
                                <span class="hide-menu">Sedes y Aulas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="calendario.php">
                                <span class="hide-menu">Calendarios</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <li><span class="sidebar-divider lg"></span></li>

                <!-- SERVICIOS INSTITUCIONALES - TODOS VEN ALGUNOS -->
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:home-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Servicios Institucionales</span>
                </li>

                <?php if($rol_id == 2): // SOLO DIRECTOR ?>
                <!-- ADMISIONES - SOLO DIRECTOR -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:user-plus-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Admisiones</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="postulaciones.php">
                                <span class="hide-menu">Postulaciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="procesos_admision.php">
                                <span class="hide-menu">Procesos</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="comunicaciones_admision.php">
                                <span class="hide-menu">Comunicaciones</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- BIBLIOTECA - TODOS -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="biblioteca.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:book-2-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Biblioteca</span>
                    </a>
                </li>

                <!-- COMEDOR - TODOS PUEDEN VER MENÚS -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="comedor_menus.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:chef-hat-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Comedor Escolar</span>
                    </a>
                </li>

                <?php if($rol_id == 2 || $rol_id == 7): // DIRECTOR Y ESTUDIANTES ?>
                <!-- TRANSPORTE - DIRECTOR Y ESTUDIANTES -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:bus-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Transporte</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <?php if($rol_id == 2): // Solo director ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="gestion_rutas.php">
                                <span class="hide-menu">Gestión de Rutas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="vehiculos_transporte.php">
                                <span class="hide-menu">Vehículos</span>
                            </a>
                        </li>
                        <?php else: // Estudiante ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="mi_ruta.php">
                                <span class="hide-menu">Mi Ruta Escolar</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if($rol_id == 2): // SOLO DIRECTOR ?>
                <!-- ENFERMERÍA - SOLO DIRECTOR GESTIONA -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
                        <div class="d-flex align-items-center gap-6">
                            <span class="d-flex">
                                <iconify-icon icon="solar:health-line-duotone"></iconify-icon>
                            </span>
                            <span class="hide-menu">Enfermería</span>
                        </div>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level">
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="fichas_medicas.php">
                                <span class="hide-menu">Fichas Médicas</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="atenciones_enfermeria.php">
                                <span class="hide-menu">Atenciones</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link primary-hover-bg" href="medicamentos_enfermeria.php">
                                <span class="hide-menu">Medicamentos</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php elseif($rol_id == 7): // ESTUDIANTE ?>
                <!-- ENFERMERÍA - ESTUDIANTE SOLO VE CONTACTO -->
                <li class="sidebar-item">
                    <a class="sidebar-link primary-hover-bg" href="enfermeria_contacto.php">
                        <span class="d-flex">
                            <iconify-icon icon="solar:health-line-duotone"></iconify-icon>
                        </span>
                        <span class="hide-menu">Enfermería</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
<!--  Sidebar End -->

<script>
// Toggle collapse de submenús
document.addEventListener('DOMContentLoaded', function() {
    const hasArrowLinks = document.querySelectorAll('.sidebar-link.has-arrow');
    
    hasArrowLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const submenu = this.nextElementSibling;
            
            // Cerrar otros submenús del mismo nivel
            const parentLi = this.closest('.sidebar-item');
            const siblings = Array.from(parentLi.parentElement.children);
            
            siblings.forEach(sibling => {
                if (sibling !== parentLi) {
                    const siblingLink = sibling.querySelector('.has-arrow');
                    const siblingSubmenu = sibling.querySelector('.collapse');
                    
                    if (siblingLink && siblingSubmenu) {
                        siblingLink.setAttribute('aria-expanded', 'false');
                        siblingSubmenu.classList.remove('show');
                    }
                }
            });
            
            // Toggle actual
            this.setAttribute('aria-expanded', !isExpanded);
            submenu.classList.toggle('show');
        });
    });
    
    // Marcar link activo según URL actual
    const currentPath = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.sidebar-link[href]');
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath) {
            link.classList.add('active');
            
            // Si está en un submenú, expandirlo
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const parentLink = parentCollapse.previousElementSibling;
                if (parentLink) {
                    parentLink.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
    
    // Toggle sidebar en mobile
    const sidebarToggle = document.getElementById('sidebarCollapse');
    const sidebar = document.querySelector('.left-sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Cerrar sidebar al hacer click fuera (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 1200) {
            if (!sidebar.contains(e.target) && sidebar.classList.contains('show')) {
                const sidebarToggler = document.querySelector('.sidebartoggler');
                if (!sidebarToggler || !sidebarToggler.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        }
    });
});
</script>