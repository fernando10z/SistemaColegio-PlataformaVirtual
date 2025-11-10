<?php

// Obtener el rol del usuario de la sesión
$rol_id = $_SESSION['rol_id'] ?? 0;
$nombre_completo = $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'];
?>

<!-- Sidebar Start -->
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="./index.php" class="text-nowrap logo-img">
                <span class="logo-text">ANDRÉS AVELINO CÁCERES</span>
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