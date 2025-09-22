<!-- Modal Historial de Traslados -->
<div class="modal fade" id="modalHistorialTraslados" tabindex="-1" aria-labelledby="modalHistorialTrasladosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                <h5 class="modal-title" id="modalHistorialTrasladosLabel">
                    <i class="ti ti-history me-2"></i>
                    Historial de Traslados
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Filtros del Historial -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar Estudiante</label>
                                <input type="text" class="form-control" id="buscar_historial" placeholder="Nombre o código...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="cargarHistorial()">
                                    <i class="ti ti-search me-1"></i>
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Historial -->
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaHistorial">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Estudiante</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoHistorial">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Cargando historial...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-success" onclick="exportarHistorial()">
                    <i class="ti ti-download me-2"></i>
                    Exportar
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#modalHistorialTraslados').on('shown.bs.modal', function() {
        cargarHistorial();
    });
});

function cargarHistorial() {
    const fechaDesde = $('#fecha_desde').val();
    const fechaHasta = $('#fecha_hasta').val();
    const busqueda = $('#buscar_historial').val();
    
    $('#cuerpoHistorial').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Cargando historial...
            </td>
        </tr>
    `);
    
    $.ajax({
        url: 'modales/traslados/procesar_traslados.php',
        type: 'POST',
        data: { 
            accion: 'obtener_historial',
            fecha_desde: fechaDesde,
            fecha_hasta: fechaHasta,
            busqueda: busqueda
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                if (response.historial.length > 0) {
                    response.historial.forEach(function(traslado) {
                        html += `
                            <tr>
                                <td>
                                    <small>${traslado.fecha_traslado}</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>${traslado.estudiante_nombre}</strong>
                                        <br><small class="text-muted">${traslado.codigo_estudiante}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger">${traslado.seccion_origen}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">${traslado.seccion_destino}</span>
                                </td>
                                <td>
                                    <small>${traslado.motivo || 'No especificado'}</small>
                                </td>
                                <td>
                                    <small>${traslado.usuario_nombre || 'Sistema'}</small>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="ti ti-inbox me-2"></i>
                                No se encontraron traslados en el período seleccionado
                            </td>
                        </tr>
                    `;
                }
                $('#cuerpoHistorial').html(html);
            } else {
                $('#cuerpoHistorial').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="ti ti-alert-circle me-2"></i>
                            Error al cargar el historial
                        </td>
                    </tr>
                `);
            }
        },
        error: function() {
            $('#cuerpoHistorial').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        Error de conexión
                    </td>
                </tr>
            `);
        }
    });
}

function exportarHistorial() {
    const fechaDesde = $('#fecha_desde').val();
    const fechaHasta = $('#fecha_hasta').val();
    const busqueda = $('#buscar_historial').val();
    
    const params = new URLSearchParams({
        fecha_desde: fechaDesde,
        fecha_hasta: fechaHasta,
        busqueda: busqueda
    });
    
    window.open(`reportes/exportar_historial_traslados.php?${params}`, '_blank');
}
</script>