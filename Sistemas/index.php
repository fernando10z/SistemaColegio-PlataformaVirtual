<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ANDRÉS AVELINO CÁCERES</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
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
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include 'includes/sidebar.php'; ?>

    <!--  Main wrapper -->
    <div class="body-wrapper" style="top: 20px;">
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <!--  Header Start -->
          <header class="app-header">
            <nav class="navbar navbar-expand-lg navbar-light">
              <ul class="navbar-nav">
                <li class="nav-item d-block d-xl-none">
                  <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                  </a>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
                    <div class="notification bg-primary rounded-circle"></div>
                  </a>
                  <div class="dropdown-menu dropdown-menu-animate-up" aria-labelledby="drop1">
                    <div class="message-body">
                      <a href="javascript:void(0)" class="dropdown-item">
                        <div class="d-flex align-items-center">
                          <div class="me-3 flex-shrink-0">
                            <img src="../assets/images/profile/user1.jpg" alt="" class="rounded-circle" width="32" height="32">
                          </div>
                          <div>
                            <p class="mb-0 fs-3">Nueva notificación</p>
                            <span class="fs-2 text-muted">Hace 2 minutos</span>
                          </div>
                        </div>
                      </a>
                      <a href="javascript:void(0)" class="dropdown-item">
                        <div class="d-flex align-items-center">
                          <div class="me-3 flex-shrink-0">
                            <img src="../assets/images/profile/user2.jpg" alt="" class="rounded-circle" width="32" height="32">
                          </div>
                          <div>
                            <p class="mb-0 fs-3">Actualización del sistema</p>
                            <span class="fs-2 text-muted">Hace 5 minutos</span>
                          </div>
                        </div>
                      </a>
                    </div>
                  </div>
                </li>
              </ul>
              <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                  <li class="nav-item dropdown">
                    <a class="nav-link" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      <img src="../assets/images/profile/user1.jpg" alt="Usuario" width="35" height="35"
                        class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                      <div class="message-body">
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-user fs-6"></i>
                          <p class="mb-0 fs-3">Mi Perfil</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-mail fs-6"></i>
                          <p class="mb-0 fs-3">Mi Cuenta</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-list-check fs-6"></i>
                          <p class="mb-0 fs-3">Mis Tareas</p>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-outline-primary mx-3 mt-2 d-block">Cerrar Sesión</a>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </nav>
          </header>
          <!--  Header End -->

          <!--  Dashboard Content Start -->
          <div class="row">
            <!-- Chart Section -->
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                      <h5 class="card-title fw-semibold">Ganancias & Gastos</h5>
                      <p class="card-subtitle mb-0">Análisis mensual de rendimiento</p>
                    </div>
                    <div class="dropdown">
                      <button id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"
                        class="rounded-circle btn-transparent rounded-circle btn-sm px-1 btn shadow-none">
                        <i class="ti ti-dots-vertical fs-7 d-block"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#">Exportar datos</a></li>
                        <li><a class="dropdown-item" href="#">Ver detalles</a></li>
                        <li><a class="dropdown-item" href="#">Configurar</a></li>
                      </ul>
                    </div>
                  </div>
                  <div id="profit" style="height: 300px;">
                    <!-- Simulación de gráfico con CSS -->
                    <div class="d-flex align-items-end justify-content-around h-100 border-bottom">
                      <div class="bg-primary" style="width: 30px; height: 60%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-danger" style="width: 30px; height: 40%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-primary" style="width: 30px; height: 80%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-danger" style="width: 30px; height: 45%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-primary" style="width: 30px; height: 70%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-danger" style="width: 30px; height: 55%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                      <div class="bg-primary" style="width: 30px; height: 35%; margin-bottom: 10px; border-radius: 4px 4px 0 0;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12 col-sm-6 mb-3">
                  <div class="card overflow-hidden">
                    <div class="card-body p-4">
                      <h5 class="card-title mb-3 fw-semibold">Distribución de Tráfico</h5>
                      <div class="row align-items-center">
                        <div class="col-7">
                          <h4 class="fw-semibold mb-3">$36,358</h4>
                          <div class="d-flex align-items-center mb-3">
                            <span class="me-2 rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;">
                              <i class="ti ti-arrow-up text-success fs-4"></i>
                            </span>
                            <p class="text-dark me-1 fs-3 mb-0">+9%</p>
                            <p class="fs-3 mb-0 text-muted">vs año anterior</p>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center">
                              <span class="bg-primary rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                              <span class="fs-3">Orgánico</span>
                            </div>
                            <div class="d-flex align-items-center">
                              <span class="bg-warning rounded-circle me-2" style="width: 8px; height: 8px;"></span>
                              <span class="fs-3">Referencias</span>
                            </div>
                          </div>
                        </div>
                        <div class="col-5">
                          <div class="d-flex justify-content-center">
                            <!-- Simulación de gráfico de dona -->
                            <div style="width: 80px; height: 80px; border: 20px solid #e3f2fd; border-top: 20px solid #2196f3; border-radius: 50%;"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-lg-12 col-sm-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="row align-items-start">
                        <div class="col-8">
                          <h5 class="card-title mb-3 fw-semibold">Ventas de Productos</h5>
                          <h4 class="fw-semibold mb-3">$6,820</h4>
                          <div class="d-flex align-items-center">
                            <span class="me-2 rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;">
                              <i class="ti ti-arrow-down text-danger fs-4"></i>
                            </span>
                            <p class="text-dark me-1 fs-3 mb-0">-2%</p>
                            <p class="fs-3 mb-0 text-muted">vs mes anterior</p>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="d-flex justify-content-end">
                            <div class="text-white bg-warning rounded-circle p-3 d-flex align-items-center justify-content-center">
                              <i class="ti ti-currency-dollar fs-6"></i>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Second Row -->
          <div class="row mt-4">
            <!-- Timeline -->
            <div class="col-lg-4 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="mb-4">
                    <h5 class="card-title fw-semibold">Próximas Actividades</h5>
                  </div>
                  <ul class="timeline-widget mb-0 position-relative mb-n5">
                    <li class="timeline-item d-flex position-relative overflow-hidden mb-3">
                      <div class="timeline-time text-dark flex-shrink-0 text-end me-3" style="min-width: 60px;">09:30</div>
                      <div class="timeline-badge-wrap d-flex flex-column align-items-center me-3">
                        <span class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></span>
                      </div>
                      <div class="timeline-desc fs-3 text-dark">Pago recibido de John Doe por $385.90</div>
                    </li>
                    <li class="timeline-item d-flex position-relative overflow-hidden mb-3">
                      <div class="timeline-time text-dark flex-shrink-0 text-end me-3" style="min-width: 60px;">10:00</div>
                      <div class="timeline-badge-wrap d-flex flex-column align-items-center me-3">
                        <span class="bg-info rounded-circle" style="width: 8px; height: 8px;"></span>
                      </div>
                      <div class="timeline-desc fs-3 text-dark fw-semibold">Nueva venta registrada <a href="#" class="text-primary d-block fw-normal">#ML-3467</a></div>
                    </li>
                    <li class="timeline-item d-flex position-relative overflow-hidden mb-3">
                      <div class="timeline-time text-dark flex-shrink-0 text-end me-3" style="min-width: 60px;">12:00</div>
                      <div class="timeline-badge-wrap d-flex flex-column align-items-center me-3">
                        <span class="bg-success rounded-circle" style="width: 8px; height: 8px;"></span>
                      </div>
                      <div class="timeline-desc fs-3 text-dark">Pago realizado de $64.95 a Michael</div>
                    </li>
                    <li class="timeline-item d-flex position-relative overflow-hidden mb-3">
                      <div class="timeline-time text-dark flex-shrink-0 text-end me-3" style="min-width: 60px;">14:30</div>
                      <div class="timeline-badge-wrap d-flex flex-column align-items-center me-3">
                        <span class="bg-warning rounded-circle" style="width: 8px; height: 8px;"></span>
                      </div>
                      <div class="timeline-desc fs-3 text-dark fw-semibold">Reunión de equipo programada</div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            
            <!-- Table -->
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex mb-4 justify-content-between align-items-center">
                    <div>
                      <h5 class="mb-1 fw-bold">Clientes Principales</h5>
                      <p class="mb-0 text-muted">Gestión de profesores y sus actividades</p>
                    </div>
                    <div class="dropdown">
                      <button id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false"
                        class="rounded-circle btn-transparent rounded-circle btn-sm px-1 btn shadow-none">
                        <i class="ti ti-dots-vertical fs-7 d-block"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                        <li><a class="dropdown-item" href="#">Ver todos</a></li>
                        <li><a class="dropdown-item" href="#">Exportar</a></li>
                        <li><a class="dropdown-item" href="#">Configurar</a></li>
                      </ul>
                    </div>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-borderless align-middle text-nowrap">
                      <thead class="border-bottom">
                        <tr>
                          <th scope="col" class="ps-0">Perfil</th>
                          <th scope="col">Tarifa/Hora</th>
                          <th scope="col">Clases Extra</th>
                          <th scope="col">Estado</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="ps-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <img src="../assets/images/profile/user1.jpg" width="45" class="rounded-circle" alt="Mark Freeman" />
                              </div>
                              <div>
                                <h6 class="mb-1 fw-bolder">Mark J. Freeman</h6>
                                <p class="fs-3 mb-0 text-muted">Prof. Inglés</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0">$150/hora</p>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0 text-success">+53</p>
                          </td>
                          <td>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fs-3">Disponible</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="ps-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <img src="../assets/images/profile/user2.jpg" width="45" class="rounded-circle" alt="Nina Oldman" />
                              </div>
                              <div>
                                <h6 class="mb-1 fw-bolder">Nina R. Oldman</h6>
                                <p class="fs-3 mb-0 text-muted">Prof. Historia</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0">$150/hora</p>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0 text-success">+68</p>
                          </td>
                          <td>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fs-3">En Clase</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="ps-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <img src="../assets/images/profile/user3.jpg" width="45" class="rounded-circle" alt="Arya Shah" />
                              </div>
                              <div>
                                <h6 class="mb-1 fw-bolder">Arya H. Shah</h6>
                                <p class="fs-3 mb-0 text-muted">Prof. Matemáticas</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0">$150/hora</p>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0 text-success">+94</p>
                          </td>
                          <td>
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 fs-3">Ausente</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="ps-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <img src="../assets/images/profile/user4.jpg" width="45" class="rounded-circle" alt="June Smith" />
                              </div>
                              <div>
                                <h6 class="mb-1 fw-bolder">June R. Smith</h6>
                                <p class="fs-3 mb-0 text-muted">Prof. Artes</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0">$150/hora</p>
                          </td>
                          <td>
                            <p class="fs-3 fw-semibold mb-0 text-success">+27</p>
                          </td>
                          <td>
                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2 fs-3">De Permiso</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Products Row -->
          <div class="row mt-4">
            <div class="col-sm-6 col-xl-3 mb-4">
              <div class="card overflow-hidden h-100">
                <div class="position-relative">
                  <img src="../assets/images/products/s4.jpg" class="card-img-top" alt="Audífonos Boat" style="height: 200px; object-fit: cover;">
                  <a href="javascript:void(0)" class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al Carrito">
                    <i class="ti ti-basket fs-4"></i>
                  </a>
                </div>
                <div class="card-body">
                  <h6 class="fw-semibold fs-4">Audífonos Boat</h6>
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold fs-4 mb-0">$50 <span class="ms-2 fw-normal text-muted fs-3"><del>$65</del></span></h6>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-star-filled text-warning me-1"></i>
                      <span class="fs-3">4.5</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-sm-6 col-xl-3 mb-4">
              <div class="card overflow-hidden h-100">
                <div class="position-relative">
                  <img src="../assets/images/products/s5.jpg" class="card-img-top" alt="MacBook Air Pro" style="height: 200px; object-fit: cover;">
                  <a href="javascript:void(0)" class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al Carrito">
                    <i class="ti ti-basket fs-4"></i>
                  </a>
                </div>
                <div class="card-body">
                  <h6 class="fw-semibold fs-4">MacBook Air Pro</h6>
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold fs-4 mb-0">$650 <span class="ms-2 fw-normal text-muted fs-3"><del>$900</del></span></h6>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-star-filled text-warning me-1"></i>
                      <span class="fs-3">4.8</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-sm-6 col-xl-3 mb-4">
              <div class="card overflow-hidden h-100">
                <div class="position-relative">
                  <img src="../assets/images/products/s7.jpg" class="card-img-top" alt="Vestido Terciopelo" style="height: 200px; object-fit: cover;">
                  <a href="javascript:void(0)" class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al Carrito">
                    <i class="ti ti-basket fs-4"></i>
                  </a>
                </div>
                <div class="card-body">
                  <h6 class="fw-semibold fs-4">Vestido Terciopelo</h6>
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold fs-4 mb-0">$150 <span class="ms-2 fw-normal text-muted fs-3"><del>$200</del></span></h6>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-star-filled text-warning me-1"></i>
                      <span class="fs-3">4.2</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-sm-6 col-xl-3 mb-4">
              <div class="card overflow-hidden h-100">
                <div class="position-relative">
                  <img src="../assets/images/products/s11.jpg" class="card-img-top" alt="Oso de Peluche" style="height: 200px; object-fit: cover;">
                  <a href="javascript:void(0)" class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar al Carrito">
                    <i class="ti ti-basket fs-4"></i>
                  </a>
                </div>
                <div class="card-body">
                  <h6 class="fw-semibold fs-4">Oso de Peluche</h6>
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-semibold fs-4 mb-0">$285 <span class="ms-2 fw-normal text-muted fs-3"><del>$345</del></span></h6>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-star-filled text-warning me-1"></i>
                      <span class="fs-3">4.7</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <footer class="py-4 px-4 text-center border-top mt-5">
            <p class="mb-0 fs-4 text-muted">Dashboard optimizado para máximo rendimiento y usabilidad</p>
          </footer>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script>
    // Inicialización de tooltips
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });

    // Funcionalidad del sidebar toggle
    document.getElementById('headerCollapse').addEventListener('click', function() {
      document.getElementById('main-wrapper').classList.toggle('mini-sidebar');
    });
  </script>
</body>

</html>