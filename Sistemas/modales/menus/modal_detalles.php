<!-- modales/comedor/modal_detalles_menu.php -->
<div class="modal fade" id="modalDetallesMenu" tabindex="-1" aria-labelledby="modalDetallesMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #C5E1A5 0%, #A8D8EA 100%); color: white;">
                <h5 class="modal-title" id="modalDetallesMenuLabel">
                    <i class="ti ti-file-info me-2"></i>
                    Detalles del Menú
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Información General -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background-color: #FFF9C4;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Información General
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted small">Fecha del Menú</label>
                                        <div class="fw-bold" id="det_fecha"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted small">Tipo de Menú</label>
                                        <div class="fw-bold" id="det_tipo_menu"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="text-muted small">Precio</label>
                                        <div class="fw-bold text-success" id="det_precio"></div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small">Descripción</label>
                                        <div class="fw-bold" id="det_descripcion"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Composición del Menú -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background-color: #E1F5FE;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-chef-hat me-2"></i>
                                    Composición del Menú
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Entrada -->
                                <div class="plato-item mb-4 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge" style="background-color: #FFE0B2;">
                                                <i class="ti ti-bowl me-1"></i>Entrada
                                            </span>
                                            <h6 class="mb-1 mt-2" id="det_entrada_nombre"></h6>
                                        </div>
                                        <span class="badge bg-info" id="det_entrada_calorias"></span>
                                    </div>
                                    <div class="text-muted small" id="det_entrada_ingredientes"></div>
                                </div>

                                <!-- Plato Principal -->
                                <div class="plato-item mb-4 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge" style="background-color: #FFCCBC;">
                                                <i class="ti ti-meat me-1"></i>Plato Principal
                                            </span>
                                            <h6 class="mb-1 mt-2" id="det_principal_nombre"></h6>
                                        </div>
                                        <span class="badge bg-info" id="det_principal_calorias"></span>
                                    </div>
                                    <div class="text-muted small" id="det_principal_ingredientes"></div>
                                </div>

                                <!-- Guarnición -->
                                <div class="plato-item mb-4 pb-3 border-bottom" id="det_guarnicion_container" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge" style="background-color: #C5E1A5;">
                                                <i class="ti ti-leaf me-1"></i>Guarnición
                                            </span>
                                            <h6 class="mb-1 mt-2" id="det_guarnicion_nombre"></h6>
                                        </div>
                                        <span class="badge bg-info" id="det_guarnicion_calorias"></span>
                                    </div>
                                </div>

                                <!-- Postre -->
                                <div class="plato-item mb-4 pb-3 border-bottom" id="det_postre_container" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge" style="background-color: #F8BBD0;">
                                                <i class="ti ti-ice-cream me-1"></i>Postre
                                            </span>
                                            <h6 class="mb-1 mt-2" id="det_postre_nombre"></h6>
                                        </div>
                                        <span class="badge bg-info" id="det_postre_calorias"></span>
                                    </div>
                                </div>

                                <!-- Bebida -->
                                <div class="plato-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge" style="background-color: #B3E5FC;">
                                                <i class="ti ti-glass me-1"></i>Bebida
                                            </span>
                                            <h6 class="mb-1 mt-2" id="det_bebida_nombre"></h6>
                                        </div>
                                        <span class="badge bg-info" id="det_bebida_calorias"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Nutricional y Disponibilidad -->
                    <div class="col-md-4">
                        <!-- Información Nutricional -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background-color: #F3E5F5;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-chart-pie me-2"></i>
                                    Info Nutricional
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="p-3 mb-3" style="background-color: #FFE0B2; border-radius: 8px;">
                                    <h3 class="mb-0" id="det_total_calorias">0</h3>
                                    <small class="text-muted">Calorías Totales</small>
                                </div>
                                <div class="p-3" style="background-color: #C5E1A5; border-radius: 8px;">
                                    <h3 class="mb-0" id="det_total_platos">0</h3>
                                    <small class="text-muted">Platos</small>
                                </div>
                            </div>
                        </div>

                        <!-- Disponibilidad -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background-color: #E8F5E9;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-clock me-2"></i>
                                    Disponibilidad
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="text-muted small">Estado</label>
                                    <div id="det_estado"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small">Horario</label>
                                    <div class="fw-bold">
                                        <i class="ti ti-clock-hour-4 me-1"></i>
                                        <span id="det_horario"></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small">Disponibles</label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             id="det_progreso_disponibilidad" style="width: 0%">
                                            <span id="det_texto_disponibilidad"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small">Reservados</label>
                                    <div class="fw-bold text-primary" id="det_reservados">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Restricciones -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background-color: #FFF9C4;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    Restricciones
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2" id="det_restricciones_container"></div>
                                <div class="mt-3">
                                    <label class="text-muted small">Alérgenos</label>
                                    <div class="fw-bold text-danger" id="det_alergenos">
                                        Ninguno
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas y Pedidos -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background-color: #E1F5FE;">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-chart-bar me-2"></i>
                                    Estadísticas y Pedidos
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3" style="background-color: #B3E5FC; border-radius: 8px;">
                                            <h4 class="mb-0" id="det_total_pedidos">0</h4>
                                            <small class="text-muted">Total Pedidos</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3" style="background-color: #C5E1A5; border-radius: 8px;">
                                            <h4 class="mb-0" id="det_pedidos_confirmados">0</h4>
                                            <small class="text-muted">Confirmados</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3" style="background-color: #FFE0B2; border-radius: 8px;">
                                            <h4 class="mb-0" id="det_pedidos_pendientes">0</h4>
                                            <small class="text-muted">Pendientes</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3" style="background-color: #F8BBD0; border-radius: 8px;">
                                            <h4 class="mb-0" id="det_ingresos">S/ 0.00</h4>
                                            <small class="text-muted">Ingresos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirMenu()">
                    <i class="ti ti-printer me-2"></i>
                    Imprimir
                </button>
                <button type="button" class="btn btn-info" onclick="exportarMenuPDF()">
                    <i class="ti ti-file-export me-2"></i>
                    Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetallesMenu(menu) {
    // Información general
    const fecha = new Date(menu.fecha);
    $('#det_fecha').text(fecha.toLocaleDateString('es-PE', { 
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
    }));
    $('#det_tipo_menu').html('<span class="badge bg-info">' + menu.configuracion.tipo_menu + '</span>');
    $('#det_precio').text('S/ ' + parseFloat(menu.configuracion.precio).toFixed(2));
    $('#det_descripcion').text(menu.configuracion.descripcion_general || 'Sin descripción');

    // Platos
    const detalles = menu.detalles;
    let totalCalorias = 0;
    let totalPlatos = 0;

    // Entrada
    if (detalles.entrada) {
        $('#det_entrada_nombre').text(detalles.entrada.nombre);
        $('#det_entrada_calorias').text(detalles.entrada.calorias + ' kcal');
        $('#det_entrada_ingredientes').text('Ingredientes: ' + (detalles.entrada.ingredientes || 'No especificado'));
        totalCalorias += parseInt(detalles.entrada.calorias) || 0;
        totalPlatos++;
    }

    // Principal
    if (detalles.principal) {
        $('#det_principal_nombre').text(detalles.principal.nombre);
        $('#det_principal_calorias').text(detalles.principal.calorias + ' kcal');
        $('#det_principal_ingredientes').text('Ingredientes: ' + (detalles.principal.ingredientes || 'No especificado'));
        totalCalorias += parseInt(detalles.principal.calorias) || 0;
        totalPlatos++;
    }

    // Guarnición
    if (detalles.guarnicion && detalles.guarnicion.nombre) {
        $('#det_guarnicion_container').show();
        $('#det_guarnicion_nombre').text(detalles.guarnicion.nombre);
        $('#det_guarnicion_calorias').text(detalles.guarnicion.calorias + ' kcal');
        totalCalorias += parseInt(detalles.guarnicion.calorias) || 0;
        totalPlatos++;
    } else {
        $('#det_guarnicion_container').hide();
    }

    // Postre
    if (detalles.postre && detalles.postre.nombre) {
        $('#det_postre_container').show();
        $('#det_postre_nombre').text(detalles.postre.nombre);
        $('#det_postre_calorias').text(detalles.postre.calorias + ' kcal');
        totalCalorias += parseInt(detalles.postre.calorias) || 0;
        totalPlatos++;
    } else {
        $('#det_postre_container').hide();
    }

    // Bebida
    if (detalles.bebida) {
        $('#det_bebida_nombre').text(detalles.bebida.nombre);
        $('#det_bebida_calorias').text(detalles.bebida.calorias + ' kcal');
        totalCalorias += parseInt(detalles.bebida.calorias) || 0;
        totalPlatos++;
    }

    $('#det_total_calorias').text(totalCalorias);
    $('#det_total_platos').text(totalPlatos);

    // Disponibilidad
    const disponibilidad = menu.disponibilidad;
    const estadoBadges = {
        'DISPONIBLE': '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Disponible</span>',
        'AGOTADO': '<span class="badge bg-warning"><i class="ti ti-alert-circle me-1"></i>Agotado</span>',
        'CANCELADO': '<span class="badge bg-danger"><i class="ti ti-x me-1"></i>Cancelado</span>'
    };
    $('#det_estado').html(estadoBadges[disponibilidad.estado] || estadoBadges['DISPONIBLE']);
    
    $('#det_horario').text(disponibilidad.hora_inicio + ' - ' + disponibilidad.hora_fin);
    
    const disponibles = disponibilidad.cantidad_disponible || 0;
    const reservados = disponibilidad.cantidad_reservada || 0;
    const total = disponibles + reservados;
    const porcentajeDisponible = total > 0 ? (disponibles / total * 100) : 0;
    
    $('#det_progreso_disponibilidad').css('width', porcentajeDisponible + '%');
    $('#det_texto_disponibilidad').text(disponibles + ' / ' + total);
    $('#det_reservados').text(reservados);

    // Restricciones
    let restriccionesHTML = '';
    if (menu.configuracion.restricciones && menu.configuracion.restricciones.length > 0) {
        menu.configuracion.restricciones.forEach(function(restriccion) {
            const badges = {
                'SIN_GLUTEN': '<span class="badge bg-warning me-1 mb-1"><i class="ti ti-wheat-off me-1"></i>Sin Gluten</span>',
                'SIN_LACTOSA': '<span class="badge bg-info me-1 mb-1"><i class="ti ti-milk-off me-1"></i>Sin Lactosa</span>',
                'VEGETARIANO': '<span class="badge bg-success me-1 mb-1"><i class="ti ti-leaf me-1"></i>Vegetariano</span>',
                'BAJO_SODIO': '<span class="badge bg-primary me-1 mb-1"><i class="ti ti-salt me-1"></i>Bajo Sodio</span>'
            };
            restriccionesHTML += badges[restriccion] || '';
        });
    } else {
        restriccionesHTML = '<span class="text-muted">Sin restricciones especiales</span>';
    }
    $('#det_restricciones_container').html(restriccionesHTML);
    
    $('#det_alergenos').text(menu.configuracion.alergenos || 'Ninguno');

    // Estadísticas (simuladas - en producción vendrían de la BD)
    $('#det_total_pedidos').text(reservados);
    $('#det_pedidos_confirmados').text(Math.floor(reservados * 0.8));
    $('#det_pedidos_pendientes').text(Math.floor(reservados * 0.2));
    $('#det_ingresos').text('S/ ' + (reservados * parseFloat(menu.configuracion.precio)).toFixed(2));

    $('#modalDetallesMenu').modal('show');
}

function imprimirMenu() {
    window.print();
}

function exportarMenuPDF() {
    const menuId = $('#det_fecha').data('menu-id');
    window.open('reportes/menu_pdf.php?id=' + menuId, '_blank');
}
</script>

<style>
@media print {
    .modal-header, .modal-footer, .btn {
        display: none !important;
    }
    .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
    }
}
</style>