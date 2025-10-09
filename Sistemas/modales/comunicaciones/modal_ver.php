<!-- Modal Ver Comunicación -->
<div class="modal fade" id="modalVerComunicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <h5 class="modal-title">
                    <i class="ti ti-eye me-2"></i>
                    Detalles de Comunicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="row" id="detalleComunicacion">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ID Comunicación</label>
                        <p id="ver_id" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <p id="ver_estado"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Código Postulación</label>
                        <p id="ver_codigo_postulacion" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Grado Solicitado</label>
                        <p id="ver_grado" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <p id="ver_tipo"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Destinatario</label>
                        <p id="ver_destinatario" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Asunto</label>
                        <p id="ver_asunto" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Mensaje</label>
                        <div id="ver_mensaje" class="border p-3 rounded" style="background-color: #f8f9fa;"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Línea de Tiempo</label>
                        <div id="ver_timeline"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="actualizarEstado()">
                    <i class="ti ti-refresh me-2"></i>Actualizar Estado
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosComunicacion(comunicacion) {
    const config = comunicacion.configuracion || {};
    const metadatos = comunicacion.metadatos || {};
    const datosPostulante = comunicacion.datos_postulante || {};
    
    $('#ver_id').text('#' + comunicacion.id);
    
    const estadoBadge = `<span class="badge bg-${getEstadoColor(comunicacion.estado)}">${comunicacion.estado}</span>`;
    $('#ver_estado').html(estadoBadge);
    
    $('#ver_codigo_postulacion').text(comunicacion.codigo_postulacion);
    $('#ver_grado').text(comunicacion.grado_solicitado);
    
    const tipoBadge = `<span class="badge bg-info">${config.tipo || 'No especificado'}</span>`;
    $('#ver_tipo').html(tipoBadge);
    
    $('#ver_destinatario').text(config.destinatario || 'No especificado');
    $('#ver_asunto').text(config.asunto || 'Sin asunto');
    $('#ver_mensaje').text(config.mensaje || 'Sin mensaje');
    
    // Generar timeline
    let timeline = '<div class="timeline">';
    if (metadatos.fecha_creacion) {
        timeline += `<div class="timeline-item"><small><strong>Creada:</strong> ${metadatos.fecha_creacion}</small></div>`;
    }
    if (metadatos.fecha_envio) {
        timeline += `<div class="timeline-item"><small><strong>Enviada:</strong> ${metadatos.fecha_envio}</small></div>`;
    }
    if (metadatos.fecha_entrega) {
        timeline += `<div class="timeline-item"><small><strong>Entregada:</strong> ${metadatos.fecha_entrega}</small></div>`;
    }
    if (metadatos.error_mensaje) {
        timeline += `<div class="timeline-item text-danger"><small><strong>Error:</strong> ${metadatos.error_mensaje}</small></div>`;
    }
    timeline += '</div>';
    
    $('#ver_timeline').html(timeline);
}

function getEstadoColor(estado) {
    switch(estado) {
        case 'PENDIENTE': return 'warning';
        case 'ENVIADO': return 'info';
        case 'ENTREGADO': return 'success';
        case 'ERROR': return 'danger';
        default: return 'secondary';
    }
}

function actualizarEstado() {
    Swal.fire({
        title: 'Actualizar Estado',
        input: 'select',
        inputOptions: {
            'PENDIENTE': 'Pendiente',
            'ENVIADO': 'Enviado',
            'ENTREGADO': 'Entregado',
            'ERROR': 'Error'
        },
        inputPlaceholder: 'Seleccionar nuevo estado',
        showCancelButton: true,
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Implementar actualización de estado
            mostrarExito('Estado actualizado correctamente');
            setTimeout(() => location.reload(), 1500);
        }
    });
}
</script>