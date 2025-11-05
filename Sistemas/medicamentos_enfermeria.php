<?php
session_start();
require_once 'conexion/bd.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['username'];

// Obtener todo el inventario
try {
    $sql = "SELECT * FROM inventario_enfermeria WHERE activo = 1 ORDER BY id DESC";
    $stmt_inventario = $conexion->prepare($sql);
    $stmt_inventario->execute();
    $inventario = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $inventario = [];
    $error_inventario = "Error al cargar inventario: " . $e->getMessage();
}

// Calcular estadísticas
$total_items = count($inventario);
$stock_bajo = 0;
$stock_critico = 0;

foreach ($inventario as $item) {
    if ($item['stock_actual'] <= 10 && $item['stock_actual'] > 5) {
        $stock_bajo++;
    }
    if ($item['stock_actual'] <= 5) {
        $stock_critico++;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario Enfermería | ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .body-wrapper {
            margin-top: 0px !important;
            padding-top: 0px !important;
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .header-section {
            background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .color-stats-1 { color: #B4E5D4; }
        .color-stats-2 { color: #FFD4B4; }
        .color-stats-3 { color: #FFB6C1; }

        .btn-action {
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s;
        }

        .btn-add {
            background-color: #C8E6C9;
            color: #1B5E20;
        }

        .btn-add:hover {
            background-color: #A5D6A7;
            color: #1B5E20;
        }

        .badge-tipo {
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-medicamento {
            background-color: #E1BEE7;
            color: #4A148C;
        }

        .badge-material {
            background-color: #B3E5FC;
            color: #01579B;
        }

        .badge-equipo {
            background-color: #FFCCBC;
            color: #BF360C;
        }

        .stock-bajo {
            background-color: #FFF9C4;
            border-left: 4px solid #F57F17;
        }

        .stock-critico {
            background-color: #FFCDD2;
            border-left: 4px solid #C62828;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
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

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="header-section">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">
                                        <i class="ti ti-packages me-2"></i>
                                        Inventario de Enfermería
                                    </h4>
                                    <p class="mb-0 text-muted">Gestión de medicamentos, materiales y equipos médicos</p>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="ti ti-user"></i> <?= htmlspecialchars($usuario_nombre) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-1">
                                <?= $total_items ?>
                            </div>
                            <div class="stats-label">Total Items</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-2">
                                <?= $stock_bajo ?>
                            </div>
                            <div class="stats-label">Stock Bajo</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number color-stats-3">
                                <?= $stock_critico ?>
                            </div>
                            <div class="stats-label">Stock Crítico</div>
                        </div>
                    </div>
                </div>

                <!-- Botón Agregar -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <button class="btn btn-action btn-add" data-bs-toggle="modal" data-bs-target="#modalAgregarItem">
                            <i class="ti ti-plus-circle me-2"></i> Agregar Nuevo Item
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="MEDICAMENTO">Medicamento</option>
                                    <option value="MATERIAL_CURACION">Material de Curación</option>
                                    <option value="EQUIPO_MEDICO">Equipo Médico</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar Producto</label>
                                <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh me-2"></i>
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Inventario -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="ti ti-table me-2"></i>
                            Listado de Inventario
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaInventario">
                                <thead style="background: linear-gradient(135deg, #E5F0FF, #FFE5F0);">
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Tipo</th>
                                        <th>Stock Actual</th>
                                        <th>Proveedor</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyInventario">
                                    <?php if (empty($inventario)): ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <i class="ti ti-package-off"></i>
                                                    <h5 class="mt-3">No hay items en el inventario</h5>
                                                    <p>Comienza agregando el primer producto</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($inventario as $item): 
                                            $claseStock = '';
                                            if ($item['stock_actual'] <= 5) {
                                                $claseStock = 'stock-critico';
                                            } elseif ($item['stock_actual'] <= 10) {
                                                $claseStock = 'stock-bajo';
                                            }
                                        ?>
                                            <tr class="item-row <?= $claseStock ?>" 
                                                data-tipo="<?= $item['tipo'] ?>" 
                                                data-nombre="<?= strtolower($item['nombre_producto']) ?>">
                                                <td><?= htmlspecialchars($item['id']) ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($item['nombre_producto']) ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = 'badge-medicamento';
                                                    if ($item['tipo'] == 'MATERIAL_CURACION') $badgeClass = 'badge-material';
                                                    if ($item['tipo'] == 'EQUIPO_MEDICO') $badgeClass = 'badge-equipo';
                                                    ?>
                                                    <span class="badge-tipo <?= $badgeClass ?>">
                                                        <?= str_replace('_', ' ', htmlspecialchars($item['tipo'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?= number_format($item['stock_actual'], 2) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($item['proveedor'] ?? 'N/A') ?></td>
                                                <td><?= date('d/m/Y', strtotime($item['fecha_ingreso'])) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarItem(<?= $item['id'] ?>)" 
                                                            title="Editar">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="eliminarItem(<?= $item['id'] ?>)" 
                                                            title="Eliminar">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
    <?php include 'modales/inventario/modal_agregar.php'; ?>
    <?php include 'modales/inventario/modal_editar.php'; ?>

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>
    <script src="../assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#filtroTipo, #buscarProducto').on('change keyup', aplicarFiltros);
        });

        function aplicarFiltros() {
            const tipoFiltro = $('#filtroTipo').val();
            const busqueda = $('#buscarProducto').val().toLowerCase();

            $('.item-row').each(function() {
                const row = $(this);
                const tipo = row.data('tipo');
                const nombre = row.data('nombre');

                let mostrar = true;

                if (tipoFiltro && tipo !== tipoFiltro) {
                    mostrar = false;
                }

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                row.toggle(mostrar);
            });
        }

        function limpiarFiltros() {
            $('#filtroTipo, #buscarProducto').val('');
            aplicarFiltros();
        }

        function mostrarCarga() {
            $('#loadingOverlay').css('display', 'flex');
        }

        function ocultarCarga() {
            $('#loadingOverlay').hide();
        }

        function editarItem(id) {
            mostrarCarga();
            
            fetch('modales/inventario/procesar_inventario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    cargarDatosEditar(data.item);
                    $('#modalEditarItem').modal('show');
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al obtener datos del item');
            });
        }

        function eliminarItem(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "El item será marcado como inactivo",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    mostrarCarga();
                    
                    fetch('modales/inventario/procesar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `accion=eliminar&id=${id}`
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
                        mostrarError('Error al eliminar el item');
                    });
                }
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
    </script>
</body>
</html>