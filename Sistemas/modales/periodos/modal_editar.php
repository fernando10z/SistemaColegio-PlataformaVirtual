<?php
// modales/periodos/modal_editar.php
?>
<!-- Modal Editar Período Académico -->
<div class="modal fade" id="modalEditarPeriodo" tabindex="-1" aria-labelledby="modalEditarPeriodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-dark">
                <h5 class="modal-title" id="modalEditarPeriodoLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Período Académico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarPeriodo" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Período <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" 
                                                   placeholder="Ejemplo: Año Académico 2025" required>
                                            <div class="form-text">Nombre descriptivo del período académico</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_anio" class="form-label">
                                                Año Académico <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_anio" name="anio" 
                                                   min="2020" max="2030" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_inicio" class="form-label">
                                                Fecha de Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_inicio" name="fecha_inicio" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_fin" class="form-label">
                                                Fecha de Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_fin" name="fecha_fin" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_tipo_periodo" class="form-label">
                                                Tipo de Período <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo_periodo" name="tipo_periodo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="BIMESTRE">Bimestre (4 períodos)</option>
                                                <option value="TRIMESTRE">Trimestre (3 períodos)</option>
                                                <option value="SEMESTRE">Semestre (2 períodos)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Información de duración -->
                                    <div class="alert alert-info" id="edit-info-duracion" style="display: none;">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <span id="edit-duracion-texto"></span>
                                    </div>

                                    <!-- Alertas de cambios importantes -->
                                    <div class="alert alert-warning" id="alerta-cambios" style="display: none;">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        <strong>¡Atención!</strong> Los cambios en las fechas o tipo de período pueden afectar 
                                        las matrículas, calificaciones y asistencias registradas.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Períodos de Evaluación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar-event me-2"></i>
                                        Períodos de Evaluación
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarPeriodoEval">
                                            <i class="ti ti-plus me-1"></i>
                                            Agregar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnRegenerarPeriodos">
                                            <i class="ti ti-refresh me-1"></i>
                                            Regenerar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="edit-contenedor-evaluaciones">
                                        <!-- Los períodos se cargarán aquí -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuraciones Adicionales -->
                        <div class="col-12 mt-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuraciones
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="edit_activo" name="activo">
                                                <label class="form-check-label" for="edit_activo">
                                                    Período Activo
                                                </label>
                                                <div class="form-text">El período estará disponible para uso</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="edit_actual" name="actual">
                                                <label class="form-check-label" for="edit_actual">
                                                    Establecer como Período Actual
                                                </label>
                                                <div class="form-text">Esto desactivará el período actual</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información adicional -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="ti ti-info-circle me-1"></i>
                                                <strong>Fecha de creación:</strong> <span id="fecha-creacion"></span>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="ti ti-users me-1"></i>
                                                <strong>Secciones/Matrículas:</strong> <span id="info-matriculas"></span>
                                            </small>
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
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarPeriodo">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Período
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicion(periodo) {
    // Cargar datos básicos
    $('#edit_id').val(periodo.id);
    $('#edit_nombre').val(periodo.nombre);
    $('#edit_anio').val(periodo.anio);
    $('#edit_fecha_inicio').val(periodo.fecha_inicio);
    $('#edit_fecha_fin').val(periodo.fecha_fin);
    $('#edit_tipo_periodo').val(periodo.tipo_periodo);
    $('#edit_activo').prop('checked', periodo.activo == 1);
    $('#edit_actual').prop('checked', periodo.actual == 1);

    // Cargar información adicional
    $('#fecha-creacion').text(formatearFecha(periodo.fecha_creacion));
    $('#info-matriculas').text(`${periodo.total_secciones || 0} secciones, ${periodo.total_matriculas || 0} matrículas`);

    // Cargar períodos de evaluación
    cargarPeriodosEvaluacion(periodo.periodos_evaluacion || []);
    
    // Calcular duración
    calcularDuracionEdicion();

    // Mostrar alerta si hay datos relacionados
    if (periodo.total_matriculas > 0 || periodo.total_secciones > 0) {
        $('#alerta-cambios').show();
    }
}

function cargarPeriodosEvaluacion(periodosEval) {
    let html = '';
    
    if (periodosEval && periodosEval.length > 0) {
        periodosEval.forEach((periodo, index) => {
            html += `
                <div class="row mb-3 periodo-evaluacion">
                    <div class="col-md-4">
                        <label class="form-label">Nombre del Período</label>
                        <input type="text" class="form-control" name="eval_nombre[]" 
                               value="${periodo.nombre || ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="eval_inicio[]" 
                               value="${periodo.fecha_inicio || ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="eval_fin[]" 
                               value="${periodo.fecha_fin || ''}" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarPeriodoEdit(this)">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    } else {
        html = `
            <div class="text-muted text-center py-4">
                <i class="ti ti-calendar-off" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2">No hay períodos de evaluación configurados</p>
                <p class="small">Usa el botón "Regenerar" para crear períodos automáticamente</p>
            </div>
        `;
    }
    
    $('#edit-contenedor-evaluaciones').html(html);
}

$(document).ready(function() {
    // Calcular duración al cambiar fechas
    $('#edit_fecha_inicio, #edit_fecha_fin').on('change', function() {
        calcularDuracionEdicion();
    });

    // Agregar período de evaluación manual
    $('#btnAgregarPeriodoEval').on('click', function() {
        const numPeriodos = $('.periodo-evaluacion').length + 1;
        const numeroRomano = convertirARomano(numPeriodos);
        const tipoPeriodo = $('#edit_tipo_periodo').val();
        let nombreBase = 'Período';
        
        switch(tipoPeriodo) {
            case 'BIMESTRE': nombreBase = 'Bimestre'; break;
            case 'TRIMESTRE': nombreBase = 'Trimestre'; break;
            case 'SEMESTRE': nombreBase = 'Semestre'; break;
        }

        const html = `
            <div class="row mb-3 periodo-evaluacion">
                <div class="col-md-4">
                    <label class="form-label">Nombre del Período</label>
                    <input type="text" class="form-control" name="eval_nombre[]" 
                           value="${numeroRomano} ${nombreBase}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="eval_inicio[]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="eval_fin[]" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarPeriodoEdit(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        $('#edit-contenedor-evaluaciones').append(html);
    });

    // Regenerar períodos automáticamente
    $('#btnRegenerarPeriodos').on('click', function() {
        const fechaInicio = $('#edit_fecha_inicio').val();
        const fechaFin = $('#edit_fecha_fin').val();
        const tipoPeriodo = $('#edit_tipo_periodo').val();

        if (!fechaInicio || !fechaFin || !tipoPeriodo) {
            Swal.fire({
                title: 'Datos Incompletos',
                text: 'Por favor completa las fechas y el tipo de período',
                icon: 'warning'
            });
            return;
        }

        Swal.fire({
            title: '¿Regenerar períodos?',
            text: 'Esto eliminará los períodos de evaluación actuales y creará nuevos',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, regenerar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                generarPeriodosEvaluacionEdit(fechaInicio, fechaFin, tipoPeriodo);
            }
        });
    });

    // Envío del formulario
    $('#formEditarPeriodo').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEdicion()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        // Agregar períodos de evaluación
        const periodosEval = obtenerPeriodosEvaluacionEdit();
        formData.append('periodos_evaluacion', JSON.stringify(periodosEval));

        mostrarCarga();
        $('#btnActualizarPeriodo').prop('disabled', true);

        $.ajax({
            url: 'modales/periodos/procesar_periodos.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarPeriodo').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Período Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarPeriodo').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarPeriodo').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarPeriodo').on('hidden.bs.modal', function() {
        $('#formEditarPeriodo')[0].reset();
        $('#edit-contenedor-evaluaciones').empty();
        $('#edit-info-duracion').hide();
        $('#alerta-cambios').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function calcularDuracionEdicion() {
    const fechaInicio = $('#edit_fecha_inicio').val();
    const fechaFin = $('#edit_fecha_fin').val();
    
    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const diferencia = fin.getTime() - inicio.getTime();
        const dias = Math.ceil(diferencia / (1000 * 3600 * 24));
        
        if (dias > 0) {
            const meses = Math.round(dias / 30);
            $('#edit-duracion-texto').text(`Duración: ${dias} días (aproximadamente ${meses} meses)`);
            $('#edit-info-duracion').show();
        } else {
            $('#edit-info-duracion').hide();
        }
    }
}

function generarPeriodosEvaluacionEdit(fechaInicio, fechaFin, tipoPeriodo) {
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    const duracionTotal = Math.ceil((fin.getTime() - inicio.getTime()) / (1000 * 3600 * 24));
    
    let numPeriodos = 0;
    let nombreBase = '';
    
    switch(tipoPeriodo) {
        case 'BIMESTRE':
            numPeriodos = 4;
            nombreBase = 'Bimestre';
            break;
        case 'TRIMESTRE':
            numPeriodos = 3;
            nombreBase = 'Trimestre';
            break;
        case 'SEMESTRE':
            numPeriodos = 2;
            nombreBase = 'Semestre';
            break;
    }
    
    const diasPorPeriodo = Math.floor(duracionTotal / numPeriodos);
    let fechaActual = new Date(inicio);
    let html = '';
    
    for (let i = 1; i <= numPeriodos; i++) {
        const fechaFinPeriodo = new Date(fechaActual);
        
        if (i < numPeriodos) {
            fechaFinPeriodo.setDate(fechaFinPeriodo.getDate() + diasPorPeriodo);
        } else {
            fechaFinPeriodo.setTime(fin.getTime());
        }
        
        const numeroRomano = convertirARomano(i);
        
        html += `
            <div class="row mb-3 periodo-evaluacion">
                <div class="col-md-4">
                    <label class="form-label">Nombre del Período</label>
                    <input type="text" class="form-control" name="eval_nombre[]" 
                           value="${numeroRomano} ${nombreBase}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="eval_inicio[]" 
                           value="${fechaActual.toISOString().split('T')[0]}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="eval_fin[]" 
                           value="${fechaFinPeriodo.toISOString().split('T')[0]}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarPeriodoEdit(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        fechaActual = new Date(fechaFinPeriodo);
        fechaActual.setDate(fechaActual.getDate() + 1);
    }
    
    $('#edit-contenedor-evaluaciones').html(html);
}

function eliminarPeriodoEdit(button) {
    $(button).closest('.periodo-evaluacion').remove();
}

function obtenerPeriodosEvaluacionEdit() {
    const periodos = [];
    $('.periodo-evaluacion').each(function(index) {
        const nombre = $(this).find('input[name="eval_nombre[]"]').val();
        const inicio = $(this).find('input[name="eval_inicio[]"]').val();
        const fin = $(this).find('input[name="eval_fin[]"]').val();
        
        if (nombre && inicio && fin) {
            periodos.push({
                numero: index + 1,
                nombre: nombre,
                fecha_inicio: inicio,
                fecha_fin: fin
            });
        }
    });
    
    return periodos;
}

function validarFormularioEdicion() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar fechas
    const fechaInicio = new Date($('#edit_fecha_inicio').val());
    const fechaFin = new Date($('#edit_fecha_fin').val());
    
    if (fechaFin <= fechaInicio) {
        mostrarErrorCampoEdit('#edit_fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio');
        isValid = false;
    }
    
    // Validar períodos de evaluación
    const periodos = obtenerPeriodosEvaluacionEdit();
    if (periodos.length === 0) {
        Swal.fire({
            title: 'Períodos de Evaluación',
            text: 'Debes configurar al menos un período de evaluación',
            icon: 'warning'
        });
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampoEdit(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function formatearFecha(fecha) {
    if (!fecha) return 'No disponible';
    return new Date(fecha).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>