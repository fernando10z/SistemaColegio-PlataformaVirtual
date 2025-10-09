<!-- Modal Detalle Postulación -->
<div class="modal fade" id="modalDetallePostulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #c7ecee 0%, #7ee8fa 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-file-info me-2"></i>
                    Detalle Completo de Postulación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Información General -->
                    <div class="col-12 mb-3">
                        <div class="card border-0" style="background-color: #fff8f0;">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #ffa94d;">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Información General
                                </h6>
                                <div id="detalle_general"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Postulante -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-0" style="background-color: #f0f8ff; height: 100%;">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #4a90e2;">
                                    <i class="ti ti-user me-2"></i>
                                    Datos del Postulante
                                </h6>
                                <div id="detalle_postulante"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Apoderado -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-0" style="background-color: #f0fff4; height: 100%;">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #48bb78;">
                                    <i class="ti ti-users me-2"></i>
                                    Datos del Apoderado
                                </h6>
                                <div id="detalle_apoderado"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-0" style="background-color: #fef7ff;">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #9c27b0;">
                                    <i class="ti ti-files me-2"></i>
                                    Documentos Presentados
                                </h6>
                                <div id="detalle_documentos"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluaciones -->
                    <div class="col-md-6 mb-3">
                        <div class="card border-0" style="background-color: #fffbf0;">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3" style="color: #ff9800;">
                                    <i class="ti ti-clipboard-check me-2"></i>
                                    Evaluaciones y Notas
                                </h6>
                                <div id="detalle_evaluaciones"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalle()">
                    <i class="ti ti-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDetallePostulacion(postulacion) {
    const datosPostulante = postulacion.datos_postulante || {};
    const datosApoderado = postulacion.datos_apoderado || {};
    const documentos = postulacion.documentos || {};
    const evaluaciones = postulacion.evaluaciones || {};
    
    // Información General
    $('#detalle_general').html(`
        <div class="row">
            <div class="col-md-3">
                <p class="mb-2"><strong>Código:</strong><br>${postulacion.codigo_postulacion || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-2"><strong>Proceso:</strong><br>${postulacion.proceso_nombre || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-2"><strong>Grado:</strong><br>${postulacion.grado_solicitado || 'N/A'}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-2"><strong>Estado:</strong><br><span class="badge bg-info">${postulacion.estado || 'N/A'}</span></p>
            </div>
        </div>
    `);
    
    // Datos Postulante
    $('#detalle_postulante').html(`
        <p class="mb-2"><strong>Nombres:</strong> ${datosPostulante.nombres || 'N/A'}</p>
        <p class="mb-2"><strong>Apellidos:</strong> ${datosPostulante.apellidos || 'N/A'}</p>
        <p class="mb-2"><strong>Documento:</strong> ${datosPostulante.documento_tipo || 'N/A'} - ${datosPostulante.documento_numero || 'N/A'}</p>
        <p class="mb-2"><strong>Fecha Nac.:</strong> ${datosPostulante.fecha_nacimiento || 'N/A'}</p>
        <p class="mb-2"><strong>Lugar Nac.:</strong> ${datosPostulante.lugar_nacimiento || 'N/A'}</p>
        <p class="mb-0"><strong>Colegio Anterior:</strong> ${datosPostulante.colegio_procedencia || 'N/A'}</p>
    `);
    
    // Datos Apoderado
    $('#detalle_apoderado').html(`
        <p class="mb-2"><strong>Nombres:</strong> ${datosApoderado.nombres || 'N/A'}</p>
        <p class="mb-2"><strong>Apellidos:</strong> ${datosApoderado.apellidos || 'N/A'}</p>
        <p class="mb-2"><strong>Documento:</strong> ${datosApoderado.documento_tipo || 'N/A'} - ${datosApoderado.documento_numero || 'N/A'}</p>
        <p class="mb-2"><strong>Parentesco:</strong> ${datosApoderado.parentesco || 'N/A'}</p>
        <p class="mb-2"><strong>Email:</strong> ${datosApoderado.email || 'N/A'}</p>
        <p class="mb-2"><strong>Teléfono:</strong> ${datosApoderado.telefono || 'N/A'}</p>
        <p class="mb-0"><strong>Dirección:</strong> ${datosApoderado.direccion || 'N/A'}</p>
    `);
    
    // Documentos
    let docsHTML = '<p class="text-muted">No se han registrado documentos</p>';
    if (documentos && Object.keys(documentos).length > 0) {
        docsHTML = '<ul class="list-unstyled mb-0">';
        for (const [key, value] of Object.entries(documentos)) {
            docsHTML += `<li class="mb-2"><i class="ti ti-file-check me-2 text-success"></i>${key}: ${value ? 'Presentado' : 'Pendiente'}</li>`;
        }
        docsHTML += '</ul>';
    }
    $('#detalle_documentos').html(docsHTML);
    
    // Evaluaciones
    let evalHTML = '<p class="text-muted">Sin evaluaciones registradas</p>';
    if (evaluaciones && Object.keys(evaluaciones).length > 0) {
        evalHTML = `
            <p class="mb-2"><strong>Nota Entrevista:</strong> <span class="evaluacion-score">${evaluaciones.nota_entrevista || 'N/A'}</span></p>
            <p class="mb-2"><strong>Nota Evaluación:</strong> <span class="evaluacion-score">${evaluaciones.nota_evaluacion || 'N/A'}</span></p>
            <p class="mb-2"><strong>Promedio Final:</strong> <span class="evaluacion-score">${evaluaciones.promedio_final || 'N/A'}</span></p>
            <p class="mb-2"><strong>Observaciones:</strong><br><small>${evaluaciones.observaciones_evaluacion || 'Sin observaciones'}</small></p>
            <p class="mb-0"><strong>Recomendaciones:</strong><br><small>${evaluaciones.recomendaciones || 'Sin recomendaciones'}</small></p>
        `;
    }
    $('#detalle_evaluaciones').html(evalHTML);
}

function imprimirDetalle() {
    window.print();
}
</script>