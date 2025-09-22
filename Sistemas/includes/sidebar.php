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
            <!-- ESTRUCTURA COMPLETA DEL SIDEBAR - SISTEMA EVA -->
            <!-- DASHBOARD -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:widget-3-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Dashboard</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="index.php" aria-expanded="false">
                <span class="d-flex">
                  <iconify-icon icon="solar:chart-line-duotone"></iconify-icon>
                </span>
                <span class="hide-menu">Panel Principal</span>
              </a>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.1 GESTIÓN ACADÉMICA -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:book-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Gestión Académica</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
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
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Períodos Académicos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="niveles.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Niveles y Grados</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="areas.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Áreas Curriculares</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="secciones.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Secciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="malla.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Malla Curricular</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
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
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Gestión Matrículas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="traslados.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Traslados</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
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
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Gestión Docentes</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="asignaciones.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Asignaciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="horarios.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Horarios</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.2 EVA/CONTENIDO TIPO BLACKBOARD -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:display-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">EVA/Contenido</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:book-bookmark-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Cursos</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/cursos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Mis Cursos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/unidades/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Unidades</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/lecciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Lecciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/recursos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Recursos</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:chat-round-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Foros y Anuncios</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/foros/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Foros</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="eva/anuncios/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Anuncios</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="eva/repositorio/index.php" aria-expanded="false">
                <span class="d-flex">
                  <iconify-icon icon="solar:folder-line-duotone"></iconify-icon>
                </span>
                <span class="hide-menu">Repositorio</span>
              </a>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.3 EVALUACIONES Y TAREAS -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:clipboard-check-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Evaluaciones y Tareas</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:question-circle-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Bancos de Preguntas</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/bancos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Mis Bancos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/preguntas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Gestión Preguntas</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:list-check-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Cuestionarios</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/cuestionarios/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Mis Cuestionarios</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/cuestionarios/crear.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Crear Cuestionario</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/cuestionarios/resultados.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Resultados</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:file-check-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Tareas</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/tareas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Mis Tareas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/tareas/crear.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Crear Tarea</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/tareas/entregas.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Revisar Entregas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/rubricas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Rúbricas</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Exámenes</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/examenes/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Mis Exámenes</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="evaluaciones/examenes/supervision.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Supervisión</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.4 CALIFICACIONES (GRADEBOOK) -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:calculator-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Calificaciones</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:notebook-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Criterios y Pesos</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/criterios/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Criterios Evaluación</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/instrumentos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Pesos por Instrumento</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Registro Notas</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/registro/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Registrar Calificaciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/registro/masivo.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Ingreso Masivo</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/cierre/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Cierre de Notas</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:document-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Reportes</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/reportes/libretas.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Libreta por Alumno</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/reportes/ranking.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Ranking</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calificaciones/reportes/competencias.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Evolución Competencias</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.5 ASISTENCIA Y DISCIPLINA -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:user-check-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Asistencia y Disciplina</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:checklist-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Control Asistencia</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="asistencia/pase-lista/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Pase de Lista</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="asistencia/tardanzas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Tardanzas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="asistencia/justificaciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Justificaciones</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:shield-warning-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Disciplina</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="disciplina/incidencias/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Incidencias</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="disciplina/tutoria/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Derivaciones Tutoría</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="disciplina/seguimientos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Acuerdos y Seguimiento</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.6 COMUNICACIÓN Y COLABORACIÓN -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:chat-dots-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Comunicación</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="comunicacion/mensajes/index.php" aria-expanded="false">
                <span class="d-flex">
                  <iconify-icon icon="solar:letter-line-duotone"></iconify-icon>
                </span>
                <span class="hide-menu">Mensajería Interna</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="comunicacion/anuncios-sistema/index.php" aria-expanded="false">
                <span class="d-flex">
                  <iconify-icon icon="solar:bell-line-duotone"></iconify-icon>
                </span>
                <span class="hide-menu">Anuncios Sistema</span>
              </a>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.7 ANALÍTICA Y TABLEROS -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:pie-chart-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Analítica y Tableros</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:graph-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Tableros</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="analitica/docente/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Dashboard Docente</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="analitica/estudiante/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Dashboard Estudiante</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="analitica/directivo/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Dashboard Directivo</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- 3.8 ADMINISTRACIÓN -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:settings-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Administración</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
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
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Gestión Usuarios</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="rolyper.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Roles y Permisos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="sedes.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Sedes y Aulas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="calendario.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Calendarios</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:settings-minimalistic-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Sistema</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="ria/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Auditoría</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="s/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Backups</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Parametrizaciones</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <span class="sidebar-divider lg"></span>
            </li>

            <!-- SERVICIOS INSTITUCIONALES -->
            <li class="nav-small-cap">
              <iconify-icon icon="solar:home-2-line-duotone" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Servicios Institucionales</span>
            </li>

            <!-- 3.11 ADMISIONES/INSCRIPCIONES ONLINE -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:user-plus-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Admisiones</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="admisiones/formulario/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Formulario Postulación</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="admisiones/evaluacion/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Evaluaciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="admisiones/lista-espera/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Lista de Espera</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="admisiones/comunicaciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Comunicaciones</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- 3.12 BIBLIOTECA ESCOLAR -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:book-2-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Biblioteca</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="biblioteca/catalogo/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Catálogo</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="biblioteca/prestamos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Préstamos y Devoluciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="biblioteca/multas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Multas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="biblioteca/inventario/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Inventario</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- 3.13 INVENTARIOS/ACTIVOS & MANTENIMIENTO -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Inventarios</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="inventarios/activos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Registro Activos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="inventarios/ordenes/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Órdenes de Trabajo</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="inventarios/bitacora/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Bitácora Incidentes</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- 3.14 COMEDOR/CAFETERÍA -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:chef-hat-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Comedor</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="comedor/menus/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Menús</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="comedor/pedidos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Pedidos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="comedor/saldos/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Control Saldos</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- 3.15 TRANSPORTE ESCOLAR -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:bus-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Transporte</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="transporte/rutas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Rutas y Paraderos</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="transporte/asignaciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Asignaciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="transporte/asistencia/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Asistencia en Bus</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- 3.16 SALUD/ENFERMERÍA -->
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg justify-content-between has-arrow" href="javascript:void(0)"
                aria-expanded="false">
                <div class="d-flex align-items-center gap-6">
                  <span class="d-flex">
                    <iconify-icon icon="solar:health-line-duotone"></iconify-icon>
                  </span>
                  <span class="hide-menu">Enfermería</span>
                </div>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="enfermeria/fichas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Fichas Médicas</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="enfermeria/atenciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Atenciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="enfermeria/autorizaciones/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Autorizaciones</span>
                    </div>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a class="sidebar-link primary-hover-bg" href="enfermeria/alertas/index.php">
                    <div class="d-flex align-items-center gap-6">
                      <span class="d-flex"><span class="icon-small"></span></span>
                      <span class="hide-menu">Alertas</span>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->