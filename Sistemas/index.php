<?php
session_start();

// Redirigir al index si no hay sesión iniciada
if (session_status() !== PHP_SESSION_ACTIVE
  || (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario_id']) && empty($_SESSION['login_time']))) {
  header('Location: ../index.php');
  exit;
}

// Incluir conexión a base de datos
require_once 'conexion/bd.php';

// ========== OBTENER KPIs PRINCIPALES ==========
try {
    // Total de estudiantes activos
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1");
    $total_estudiantes = $stmt->fetch()['total'];

    // Total de docentes activos
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM docentes WHERE activo = 1");
    $total_docentes = $stmt->fetch()['total'];

    // Total de cursos activos
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM cursos c 
                               INNER JOIN asignaciones_docentes ad ON c.asignacion_id = ad.id 
                               WHERE ad.activo = 1");
    $total_cursos = $stmt->fetch()['total'];

    // Matrículas del año actual
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM matriculas 
                               WHERE estado = 'MATRICULADO' 
                               AND YEAR(fecha_matricula) = YEAR(CURDATE())");
    $total_matriculas = $stmt->fetch()['total'];

} catch (PDOException $e) {
    $total_estudiantes = 0;
    $total_docentes = 0;
    $total_cursos = 0;
    $total_matriculas = 0;
}

// ========== ESTADÍSTICAS MENSUALES PARA GRÁFICO ==========
try {
    // Obtener matrículas por mes del año actual
    $stmt = $conexion->query("
        SELECT 
            MONTH(fecha_matricula) as mes,
            COUNT(*) as cantidad
        FROM matriculas 
        WHERE YEAR(fecha_matricula) = YEAR(CURDATE())
        AND estado = 'MATRICULADO'
        GROUP BY MONTH(fecha_matricula)
        ORDER BY mes ASC
    ");
    $matriculas_por_mes = $stmt->fetchAll();

    // Inicializar array con 12 meses en 0
    $datos_matriculas = array_fill(1, 12, 0);
    foreach ($matriculas_por_mes as $dato) {
        $datos_matriculas[$dato['mes']] = (int)$dato['cantidad'];
    }

} catch (PDOException $e) {
    $datos_matriculas = array_fill(1, 12, 0);
}

// ========== ACTIVIDADES RECIENTES ==========
try {
    $actividades_recientes = [];
    
    // Últimas evaluaciones publicadas
    $stmt = $conexion->query("
        SELECT 'evaluacion' as tipo, e.titulo, e.fecha_creacion, 
               CONCAT(u.nombres, ' ', u.apellidos) as usuario
        FROM evaluaciones e
        INNER JOIN usuarios u ON e.usuario_creacion = u.id
        WHERE e.estado = 'PUBLICADO'
        ORDER BY e.fecha_creacion DESC
        LIMIT 3
    ");
    $evaluaciones = $stmt->fetchAll();
    
    // Últimos anuncios publicados
    $stmt = $conexion->query("
        SELECT 'anuncio' as tipo, a.titulo, a.fecha_publicacion as fecha_creacion,
               CONCAT(u.nombres, ' ', u.apellidos) as usuario
        FROM anuncios a
        INNER JOIN usuarios u ON a.usuario_creacion = u.id
        WHERE a.activo = 1
        ORDER BY a.fecha_publicacion DESC
        LIMIT 2
    ");
    $anuncios = $stmt->fetchAll();
    
    // Combinar y ordenar
    $actividades_recientes = array_merge($evaluaciones, $anuncios);
    usort($actividades_recientes, function($a, $b) {
        return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
    });
    $actividades_recientes = array_slice($actividades_recientes, 0, 4);
    
} catch (PDOException $e) {
    $actividades_recientes = [];
}

// ========== DOCENTES CON MÁS CURSOS ==========
try {
    $stmt = $conexion->query("
        SELECT 
            d.id,
            d.nombres,
            d.apellidos,
            d.foto_url,
            COUNT(DISTINCT ad.id) as total_cursos,
            GROUP_CONCAT(DISTINCT ac.nombre ORDER BY ac.nombre SEPARATOR ', ') as areas
        FROM docentes d
        INNER JOIN asignaciones_docentes ad ON d.id = ad.docente_id
        INNER JOIN areas_curriculares ac ON ad.area_id = ac.id
        WHERE d.activo = 1 AND ad.activo = 1
        GROUP BY d.id, d.nombres, d.apellidos, d.foto_url
        ORDER BY total_cursos DESC
        LIMIT 4
    ");
    $docentes_principales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $docentes_principales = [];
}

// ========== SERVICIOS DEL COLEGIO ==========
try {
    // Préstamos biblioteca activos
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM prestamos_biblioteca WHERE estado = 'ACTIVO'");
    $prestamos_activos = $stmt->fetch()['total'];

    // Rutas de transporte activas
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM rutas_transporte WHERE activo = 1");
    $rutas_activas = $stmt->fetch()['total'];

    // Fichas médicas registradas
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM fichas_medicas WHERE activo = 1");
    $fichas_medicas = $stmt->fetch()['total'];

    // Material bibliográfico disponible
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM material_bibliografico WHERE activo = 1");
    $material_biblioteca = $stmt->fetch()['total'];

} catch (PDOException $e) {
    $prestamos_activos = 0;
    $rutas_activas = 0;
    $fichas_medicas = 0;
    $material_biblioteca = 0;
}

// ========== PORCENTAJES Y VARIACIONES ==========
$mes_anterior = date('Y-m', strtotime('-1 month'));
$mes_actual = date('Y-m');

try {
    // Estudiantes nuevos este mes
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM estudiantes 
        WHERE DATE_FORMAT(fecha_creacion, '%Y-%m') = ?
    ");
    $stmt->execute([$mes_actual]);
    $estudiantes_nuevos = $stmt->fetch()['total'];
    
    // Estudiantes mes anterior
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM estudiantes 
        WHERE DATE_FORMAT(fecha_creacion, '%Y-%m') = ?
    ");
    $stmt->execute([$mes_anterior]);
    $estudiantes_mes_anterior = $stmt->fetch()['total'];
    
    // Calcular variación
    if ($estudiantes_mes_anterior > 0) {
        $variacion_estudiantes = round((($estudiantes_nuevos - $estudiantes_mes_anterior) / $estudiantes_mes_anterior) * 100, 1);
    } else {
        $variacion_estudiantes = 100;
    }
    
} catch (PDOException $e) {
    $variacion_estudiantes = 0;
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - ANDRÉS AVELINO CÁCERES</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .body-wrapper {
      margin-top: 0px !important;
      padding-top: 0px !important;
    }
    
    .card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 15px;
      padding: 20px;
      color: white;
      margin-bottom: 20px;
    }

    .stat-card.success {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.info {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card.warning {
      background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    #chartContainer {
      height: 300px !important;
      max-height: 300px !important;
    }

    .left-sidebar {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      height: 100vh !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow: hidden;
      z-index: 1000;
    }
  </style>
    </style>
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
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include 'includes/sidebar.php'; ?>

    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid">
      <?php include 'includes/header.php'; ?>

          <!-- KPIs Principales -->
          <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
              <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1">Total Estudiantes</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($total_estudiantes); ?></h2>
                    <small>
                      <i class="ti ti-arrow-<?php echo $variacion_estudiantes >= 0 ? 'up' : 'down'; ?>"></i>
                      <?php echo abs($variacion_estudiantes); ?>% vs mes anterior
                    </small>
                  </div>
                  <i class="ti ti-users fs-1"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
              <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1">Docentes Activos</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($total_docentes); ?></h2>
                    <small>Profesores registrados</small>
                  </div>
                  <i class="ti ti-school fs-1"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
              <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1">Cursos Activos</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($total_cursos); ?></h2>
                    <small>En periodo académico</small>
                  </div>
                  <i class="ti ti-book fs-1"></i>
                </div>
              </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
              <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1">Matrículas 2025</h6>
                    <h2 class="mb-0 fw-bold"><?php echo number_format($total_matriculas); ?></h2>
                    <small>Año académico actual</small>
                  </div>
                  <i class="ti ti-file-text fs-1"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Gráfico y Servicios -->
          <div class="row">
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                      <h5 class="card-title fw-semibold">Matrículas por Mes</h5>
                      <p class="card-subtitle mb-0">Año <?php echo date('Y'); ?></p>
                    </div>
                  </div>
                  <div id="chartContainer">
                    <canvas id="matriculasChart"></canvas>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-lg-4">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title fw-semibold mb-4">Servicios Institucionales</h5>
                  
                  <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center">
                      <div class="bg-primary-subtle rounded p-2 me-3">
                        <i class="ti ti-book-2 text-primary fs-5"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Biblioteca</h6>
                        <small class="text-muted">Material disponible</small>
                      </div>
                    </div>
                    <h5 class="mb-0 fw-bold"><?php echo number_format($material_biblioteca); ?></h5>
                  </div>

                  <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center">
                      <div class="bg-success-subtle rounded p-2 me-3">
                        <i class="ti ti-books text-success fs-5"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Préstamos</h6>
                        <small class="text-muted">Activos actualmente</small>
                      </div>
                    </div>
                    <h5 class="mb-0 fw-bold"><?php echo number_format($prestamos_activos); ?></h5>
                  </div>

                  <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center">
                      <div class="bg-info-subtle rounded p-2 me-3">
                        <i class="ti ti-bus text-info fs-5"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Transporte</h6>
                        <small class="text-muted">Rutas activas</small>
                      </div>
                    </div>
                    <h5 class="mb-0 fw-bold"><?php echo number_format($rutas_activas); ?></h5>
                  </div>

                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                      <div class="bg-danger-subtle rounded p-2 me-3">
                        <i class="ti ti-heart text-danger fs-5"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Enfermería</h6>
                        <small class="text-muted">Fichas médicas</small>
                      </div>
                    </div>
                    <h5 class="mb-0 fw-bold"><?php echo number_format($fichas_medicas); ?></h5>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividades y Docentes -->
          <div class="row mt-4">
            <div class="col-lg-4 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h5 class="card-title fw-semibold mb-4">Actividades Recientes</h5>
                  
                  <?php if (empty($actividades_recientes)): ?>
                    <p class="text-muted">No hay actividades recientes</p>
                  <?php else: ?>
                    <ul class="timeline-widget mb-0 position-relative">
                      <?php foreach ($actividades_recientes as $actividad): 
                        $hora = date('H:i', strtotime($actividad['fecha_creacion']));
                        $color = $actividad['tipo'] === 'evaluacion' ? 'primary' : 'info';
                        $icono = $actividad['tipo'] === 'evaluacion' ? 'ti-file-check' : 'ti-bell';
                      ?>
                        <li class="timeline-item d-flex position-relative overflow-hidden mb-3">
                          <div class="timeline-time text-dark flex-shrink-0 text-end me-3" style="min-width: 60px;">
                            <?php echo $hora; ?>
                          </div>
                          <div class="timeline-badge-wrap d-flex flex-column align-items-center me-3">
                            <span class="bg-<?php echo $color; ?> rounded-circle" style="width: 8px; height: 8px;"></span>
                          </div>
                          <div class="timeline-desc fs-3 text-dark">
                            <i class="ti <?php echo $icono; ?>"></i>
                            <?php echo htmlspecialchars($actividad['titulo']); ?>
                            <small class="d-block text-muted">
                              Por: <?php echo htmlspecialchars($actividad['usuario']); ?>
                            </small>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h5 class="mb-3 fw-bold">Docentes con Más Cursos</h5>

                  <?php if (empty($docentes_principales)): ?>
                    <p class="text-muted">No hay docentes registrados</p>
                  <?php else: ?>
                    <div class="table-responsive">
                      <table class="table table-borderless align-middle text-nowrap">
                        <thead class="border-bottom">
                          <tr>
                            <th scope="col" class="ps-0">Docente</th>
                            <th scope="col">Áreas</th>
                            <th scope="col">Total Cursos</th>
                            <th scope="col">Estado</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($docentes_principales as $docente): 
                            $foto = !empty($docente['foto_url']) ? $docente['foto_url'] : '../assets/images/profile/user1.jpg';
                            $areas_array = explode(', ', $docente['areas']);
                            $areas_display = count($areas_array) > 2 
                              ? implode(', ', array_slice($areas_array, 0, 2)) . '...' 
                              : $docente['areas'];
                          ?>
                            <tr>
                              <td class="ps-0">
                                <div class="d-flex align-items-center">
                                  <div class="me-3">
                                    <img src="<?php echo htmlspecialchars($foto); ?>" width="45" class="rounded-circle" 
                                         onerror="this.src='../assets/images/profile/user1.jpg'" />
                                  </div>
                                  <div>
                                    <h6 class="mb-1 fw-bolder">
                                      <?php echo htmlspecialchars($docente['nombres'] . ' ' . $docente['apellidos']); ?>
                                    </h6>
                                    <p class="fs-3 mb-0 text-muted">Docente</p>
                                  </div>
                                </div>
                              </td>
                              <td>
                                <p class="fs-3 mb-0"><?php echo htmlspecialchars($areas_display); ?></p>
                              </td>
                              <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fs-3">
                                  <?php echo $docente['total_cursos']; ?> cursos
                                </span>
                              </td>
                              <td>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fs-3">
                                  Activo
                                </span>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <footer class="py-4 px-4 text-center border-top mt-5">
            <p class="mb-0 fs-4 text-muted">Dashboard Institucional - ANDRÉS AVELINO CÁCERES</p>
          </footer>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <script>
    // Gráfico de Matrículas
    const ctx = document.getElementById('matriculasChart').getContext('2d');
    const datosMatriculas = <?php echo json_encode(array_values($datos_matriculas)); ?>;
    
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [{
          label: 'Matrículas',
          data: datosMatriculas,
          backgroundColor: 'rgba(102, 126, 234, 0.8)',
          borderColor: 'rgba(102, 126, 234, 1)',
          borderWidth: 1,
          borderRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 5
            }
          }
        }
      }
    });

    // Sidebar toggle
    document.getElementById('headerCollapse')?.addEventListener('click', function() {
      document.getElementById('main-wrapper').classList.toggle('mini-sidebar');
    });
  </script>
</body>
</html>