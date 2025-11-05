<!-- Modal Evaluar Postulación -->
<div class="modal fade" id="modalEvaluarPostulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8d8ea 0%, #aa96da 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-clipboard-check me-2"></i>
                    Evaluar Postulación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formEvaluarPostulacion" method="POST">
                <input type="hidden" id="eval_postulacion_id" name="postulacion_id">
                
                <div class="modal-body">
                    <!-- Info Postulante -->
                    <div class="card border-0 mb-3" style="background-color: #f0f8ff;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Información del Postulante</h6>
                            <div id="eval_info_postulante"></div>
                        </div>
                    </div>

                    <!-- Cambio de Estado -->
                    <div class="card border-0 mb-3" style="background-color: #fff8f0;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Estado de la Postulación</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nuevo Estado <span class="text-danger">*</span></label>
                                    <select class="form-select" id="eval_estado" name="estado" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="REGISTRADA">Registrada</option>
                                        <option value="EN_EVALUACION">En Evaluación</option>
                                        <option value="ADMITIDO">Admitido</option>
                                        <option value="LISTA_ESPERA">Lista de Espera</option>
                                        <option value="NO_ADMITIDO">No Admitido</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select" name="prioridad">
                                        <option value="NORMAL">Normal</option>
                                        <option value="ALTA">Alta</option>
                                        <option value="MUY_ALTA">Muy Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Evaluación -->
                    <div class="card border-0 mb-3" style="background-color: #f0fff4;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Resultado de Evaluación</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nota Entrevista</label>
                                    <input type="number" class="form-control" id="eval_nota_entrevista" 
                                           name="nota_entrevista" min="0" max="20" step="0.5" 
                                           placeholder="0-20">
                                    <div class="form-text">Escala vigesimal (0-20)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nota Evaluación</label>
                                    <input type="number" class="form-control" id="eval_nota_evaluacion" 
                                           name="nota_evaluacion" min="0" max="20" step="0.5" 
                                           placeholder="0-20">
                                    <div class="form-text">Escala vigesimal (0-20)</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Promedio Final</label>
                                    <input type="number" class="form-control" id="eval_promedio_final" 
                                           name="promedio_final" min="0" max="20" step="0.01" 
                                           placeholder="0-20" readonly>
                                    <div class="form-text">Se calcula automáticamente</div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Observaciones de Evaluación</label>
                                    <textarea class="form-control" name="observaciones_evaluacion" rows="3" 
                                              placeholder="Comentarios sobre el desempeño del postulante..." 
                                              maxlength="500"></textarea>
                                    <div class="form-text">Máximo 500 caracteres</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Recomendaciones</label>
                                    <textarea class="form-control" name="recomendaciones" rows="2" 
                                              placeholder="Recomendaciones o sugerencias..." 
                                              maxlength="300"></textarea>
                                    <div class="form-text">Máximo 300 caracteres</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEvaluacion">
                        <i class="ti ti-device-floppy me-2"></i>Guardar Evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEvaluacion(postulacion) {
    $('#eval_postulacion_id').val(postulacion.id);
    
    const datosPostulante = postulacion.datos_postulante || {};
    const infoHTML = `
        <p class="mb-1"><strong>Nombre:</strong> ${datosPostulante.nombres || ''} ${datosPostulante.apellidos || ''}</p>
        <p class="mb-1"><strong>Documento:</strong> ${datosPostulante.documento_tipo || ''} - ${datosPostulante.documento_numero || ''}</p>
        <p class="mb-0"><strong>Grado Solicitado:</strong> ${postulacion.grado_solicitado || ''}</p>
    `;
    $('#eval_info_postulante').html(infoHTML);
    
    $('#eval_estado').val(postulacion.estado);
    
    const evaluaciones = postulacion.evaluaciones || {};
    if (evaluaciones.nota_entrevista) $('#eval_nota_entrevista').val(evaluaciones.nota_entrevista);
    if (evaluaciones.nota_evaluacion) $('#eval_nota_evaluacion').val(evaluaciones.nota_evaluacion);
    if (evaluaciones.promedio_final) $('#eval_promedio_final').val(evaluaciones.promedio_final);
}

$(document).ready(function() {
    // Cálculo automático de promedio
    $('#eval_nota_entrevista, #eval_nota_evaluacion').on('input', function() {
        const notaEntrevista = parseFloat($('#eval_nota_entrevista').val()) || 0;
        const notaEvaluacion = parseFloat($('#eval_nota_evaluacion').val()) || 0;
        
        if (notaEntrevista > 0 || notaEvaluacion > 0) {
            const promedio = ((notaEntrevista + notaEvaluacion) / 2).toFixed(2);
            $('#eval_promedio_final').val(promedio);
        } else {
            $('#eval_promedio_final').val('');
        }
    });

    $('#formEvaluarPostulacion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEvaluacion()) {
            return false;
        }
        
        const formData = new FormData(this);
        formData.append('accion', 'evaluar');
        
        mostrarCarga();
        $('#btnGuardarEvaluacion').prop('disabled', true);

        $.ajax({
            url: 'modales/postulaciones/procesar_postulaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarEvaluacion').prop('disabled', false);
                
                if (response.success) {
                    mostrarExito(response.message);
                    $('#modalEvaluarPostulacion').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarEvaluacion').prop('disabled', false);
                mostrarError('Error al procesar la evaluación');
            }
        });
    });
});

// VALIDACIÓN DE EVALUACIÓN CON 10 VALIDACIONES
function validarFormularioEvaluacion() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar estado seleccionado
    const estado = $('#eval_estado').val();
    if (!estado) {
        marcarCampoError('#eval_estado', 'Debe seleccionar un estado');
        erroresEncontrados.push('Estado requerido');
        isValid = false;
    }
    
    // 2-3. Validar nota entrevista
    const notaEntrevista = $('#eval_nota_entrevista').val();
    if (notaEntrevista !== '' && notaEntrevista !== null) {
        const nota = parseFloat(notaEntrevista);
        if (isNaN(nota) || nota < 0 || nota > 20) {
            marcarCampoError('#eval_nota_entrevista', 'La nota debe estar entre 0 y 20');
            erroresEncontrados.push('Nota entrevista: rango inválido (0-20)');
            isValid = false;
        }
    }
    
    // 4-5. Validar nota evaluación
    const notaEvaluacion = $('#eval_nota_evaluacion').val();
    if (notaEvaluacion !== '' && notaEvaluacion !== null) {
        const nota = parseFloat(notaEvaluacion);
        if (isNaN(nota) || nota < 0 || nota > 20) {
            marcarCampoError('#eval_nota_evaluacion', 'La nota debe estar entre 0 y 20');
            erroresEncontrados.push('Nota evaluación: rango inválido (0-20)');
            isValid = false;
        }
    }
    
    // 6. Validar que al menos una nota esté registrada si el estado es ADMITIDO o NO_ADMITIDO
    if ((estado === 'ADMITIDO' || estado === 'NO_ADMITIDO') && !notaEntrevista && !notaEvaluacion) {
        Swal.fire({
            title: 'Advertencia',
            text: 'Para el estado seleccionado se recomienda registrar al menos una nota',
            icon: 'warning',
            confirmButtonColor: '#ffa94d'
        });
    }
    
    // 7. Validar promedio final
    const promedioFinal = $('#eval_promedio_final').val();
    if (promedioFinal !== '' && promedioFinal !== null) {
        const promedio = parseFloat(promedioFinal);
        if (isNaN(promedio) || promedio < 0 || promedio > 20) {
            marcarCampoError('#eval_promedio_final', 'El promedio debe estar entre 0 y 20');
            erroresEncontrados.push('Promedio final: rango inválido (0-20)');
            isValid = false;
        }
    }
    
    // 8. Validar observaciones
    const observaciones = $('[name="observaciones_evaluacion"]').val();
    if (observaciones && observaciones.length > 500) {
        marcarCampoError('[name="observaciones_evaluacion"]', 'Las observaciones no pueden superar 500 caracteres');
        erroresEncontrados.push('Observaciones: muy largas (max 500)');
        isValid = false;
    }
    
    // 9. Validar recomendaciones
    const recomendaciones = $('[name="recomendaciones"]').val();
    if (recomendaciones && recomendaciones.length > 300) {
        marcarCampoError('[name="recomendaciones"]', 'Las recomendaciones no pueden superar 300 caracteres');
        erroresEncontrados.push('Recomendaciones: muy largas (max 300)');
        isValid = false;
    }
    
    // 10. Validar coherencia entre estado y notas
    if (estado === 'ADMITIDO' && promedioFinal && parseFloat(promedioFinal) < 11) {
        Swal.fire({
            title: '⚠️ Advertencia',
            text: 'El promedio es menor a 11 pero el estado es ADMITIDO. ¿Está seguro?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Revisar'
        }).then((result) => {
            if (!result.isConfirmed) {
                isValid = false;
            }
        });
    }
    
    if (!isValid && erroresEncontrados.length > 0) {
        const mensajeError = `❌ ERRORES EN LA EVALUACIÓN\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar',
            footer: `Total de errores: ${erroresEncontrados.length}`
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}
</script>