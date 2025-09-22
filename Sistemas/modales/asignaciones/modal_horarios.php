<?php
// modales/asignaciones/modal_horarios.php
?>
<!-- Modal Horarios Asignación -->
<div class="modal fade" id="modalHorariosAsignacion" tabindex="-1" aria-labelledby="modalHorariosAsignacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalHorariosAsignacionLabel">
                    <i class="ti ti-calendar-time me-2"></i>
                    Gestión de Horarios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Información de la Asignación -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Docente:</strong><br>
                                    <span id="horarios_docente_info"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Sección:</strong><br>
                                    <span id="horarios_seccion_info"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Área:</strong><br>
                                    <span id="horarios_area_info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gestión de Horarios -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="ti ti-clock me-2"></i>
                                    Horarios de Clase
                                </h6>
                                <button type="button" class="btn btn-sm btn-primary" id="btnAgregarHorarioModal">
                                    <i class="ti ti-plus"></i> Agregar Horario
                                </button>
                            </div>
                            <div class="card-body">
                                <form id="formHorarios">
                                    <input type="hidden" id="horarios_asignacion_id" name="asignacion_id">
                                    
                                    <div id="horariosModalContainer">
                                        <!-- Se cargarán los horarios aquí -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Vista Previa Semanal -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="ti ti-calendar me-2"></i>
                                    Vista Semanal
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="horario-semanal">
                                    <div class="dia-horario" data-dia="1">
                                        <strong>Lunes</strong>
                                        <div class="horarios-dia" id="horarios_dia_1"></div>
                                    </div>
                                    <div class="dia-horario" data-dia="2">
                                        <strong>Martes</strong>
                                        <div class="horarios-dia" id="horarios_dia_2"></div>
                                    </div>
                                    <div class="dia-horario" data-dia="3">
                                        <strong>Miércoles</strong>
                                        <div class="horarios-dia" id="horarios_dia_3"></div>
                                    </div>
                                    <div class="dia-horario" data-dia="4">
                                        <strong>Jueves</strong>
                                        <div class="horarios-dia" id="horarios_dia_4"></div>
                                    </div>
                                    <div class="dia-horario" data-dia="5">
                                        <strong>Viernes</strong>
                                        <div class="horarios-dia" id="horarios_dia_5"></div>
                                    </div>
                                    <div class="dia-horario" data-dia="6">
                                        <strong>Sábado</strong>
                                        <div class="horarios-dia" id="horarios_dia_6"></div>
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
                <button type="button" class="btn btn-info" id="btnGuardarHorarios">
                    <i class="ti ti-device-floppy me-2"></i>
                    Guardar Horarios
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.horario-item-modal {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    background-color: #f8f9fa;
}

.horario-semanal {
    max-height: 400px;
    overflow-y: auto;
}

.dia-horario {
    margin-bottom: 1rem;
    padding: 0.5rem;
    border-left: 3px solid #0d6efd;
    background-color: #f8f9fa;
}

.horarios-dia {
    margin-top: 0.5rem;
}

.horario-bloque {
    background-color: #e7f1ff;
    border: 1px solid #b6d7ff;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.conflicto-horario {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}
</style>

<script>
let contadorHorariosModal = 0;
let horariosActuales = [];

// Función para cargar modal de horarios (llamada desde el PHP principal)
function cargarModalHorarios(asignacionId) {
    mostrarCarga();
    
    $.ajax({
        url: 'modales/asignaciones/procesar_asignaciones.php',
        type: 'POST',
        data: { 
            accion: 'obtener_horarios',
            id: asignacionId
        },
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            
            if (response.success) {
                cargarDatosHorarios(response.asignacion);
                $('#modalHorariosAsignacion').modal('show');
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarCarga();
            mostrarError('Error al cargar horarios');
        }
    });
}

function cargarDatosHorarios(asignacion) {
    // Información de la asignación
    $('#horarios_asignacion_id').val(asignacion.id);
    $('#horarios_docente_info').text(`${asignacion.docente_nombres} ${asignacion.docente_apellidos}`);
    $('#horarios_seccion_info').text(`${asignacion.nivel_nombre} - ${asignacion.grado} "${asignacion.seccion}"`);
    $('#horarios_area_info').text(`${asignacion.area_nombre} (${asignacion.area_codigo})`);
    
    // Cargar horarios existentes
    horariosActuales = asignacion.horarios || [];
    mostrarHorariosModal();
    actualizarVistaSemanal();
}

function mostrarHorariosModal() {
    const container = $('#horariosModalContainer');
    container.empty();
    contadorHorariosModal = 0;
    
    if (horariosActuales.length === 0) {
        container.html(`
            <div class="alert alert-warning">
                <i class="ti ti-calendar-off me-2"></i>
                No hay horarios configurados. Agrega un horario para comenzar.
            </div>
        `);
    } else {
        horariosActuales.forEach(function(horario, index) {
            agregarHorarioModal(horario);
        });
    }
}

function agregarHorarioModal(horario = null) {
    contadorHorariosModal++;
    const horarioData = horario || { dia: '', hora_inicio: '', hora_fin: '', aula: '' };
    
    const horarioHtml = `
        <div class="horario-item-modal" data-index="${contadorHorariosModal}">
            <div class="row align-items-center">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Día de la Semana</label>
                    <select class="form-select horario-dia" name="horarios[${contadorHorariosModal}][dia]" required>
                        <option value="">Seleccionar día</option>
                        <option value="1" ${horarioData.dia == 1 ? 'selected' : ''}>Lunes</option>
                        <option value="2" ${horarioData.dia == 2 ? 'selected' : ''}>Martes</option>
                        <option value="3" ${horarioData.dia == 3 ? 'selected' : ''}>Miércoles</option>
                        <option value="4" ${horarioData.dia == 4 ? 'selected' : ''}>Jueves</option>
                        <option value="5" ${horarioData.dia == 5 ? 'selected' : ''}>Viernes</option>
                        <option value="6" ${horarioData.dia == 6 ? 'selected' : ''}>Sábado</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Hora Inicio</label>
                    <input type="time" class="form-control horario-inicio" 
                           name="horarios[${contadorHorariosModal}][hora_inicio]" 
                           value="${horarioData.hora_inicio || ''}" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Hora Fin</label>
                    <input type="time" class="form-control horario-fin" 
                           name="horarios[${contadorHorariosModal}][hora_fin]" 
                           value="${horarioData.hora_fin || ''}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Aula (Opcional)</label>
                    <input type="text" class="form-control" 
                           name="horarios[${contadorHorariosModal}][aula]" 
                           value="${horarioData.aula || ''}" 
                           placeholder="Ej: Aula 101">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger d-block w-100 btn-eliminar-horario-modal">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#horariosModalContainer').append(horarioHtml);
}

function actualizarVistaSemanal() {
    // Limpiar vista semanal
    for (let i = 1; i <= 6; i++) {
        $(`#horarios_dia_${i}`).empty();
    }
    
    // Recopilar horarios actuales del formulario
    const horariosFormulario = [];
    $('.horario-item-modal').each(function() {
        const dia = $(this).find('.horario-dia').val();
        const inicio = $(this).find('.horario-inicio').val();
        const fin = $(this).find('.horario-fin').val();
        
        if (dia && inicio && fin) {
            horariosFormulario.push({ dia, inicio, fin });
        }
    });
    
    // Mostrar horarios en vista semanal
    horariosFormulario.forEach(function(horario) {
        const conflicto = detectarConflictoHorario(horario, horariosFormulario);
        const claseConflicto = conflicto ? 'conflicto-horario' : '';
        
        $(`#horarios_dia_${horario.dia}`).append(`
            <div class="horario-bloque ${claseConflicto}">
                ${horario.inicio} - ${horario.fin}
                ${conflicto ? '<i class="ti ti-alert-triangle ms-1" title="Conflicto de horario"></i>' : ''}
            </div>
        `);
    });
}

function detectarConflictoHorario(horarioCheck, todosHorarios) {
    const horariosDelMismoDia = todosHorarios.filter(h => 
        h.dia === horarioCheck.dia && 
        (h.inicio !== horarioCheck.inicio || h.fin !== horarioCheck.fin)
    );
    
    return horariosDelMismoDia.some(h => {
        const inicioCheck = new Date(`2000-01-01T${horarioCheck.inicio}`);
        const finCheck = new Date(`2000-01-01T${horarioCheck.fin}`);
        const inicioH = new Date(`2000-01-01T${h.inicio}`);
        const finH = new Date(`2000-01-01T${h.fin}`);
        
        return (inicioCheck < finH && finCheck > inicioH);
    });
}

$(document).ready(function() {
    // Agregar nuevo horario
    $('#btnAgregarHorarioModal').on('click', function() {
        agregarHorarioModal();
        actualizarVistaSemanal();
    });
    
    // Eliminar horario
    $(document).on('click', '.btn-eliminar-horario-modal', function() {
        $(this).closest('.horario-item-modal').remove();
        actualizarVistaSemanal();
    });
    
    // Actualizar vista semanal cuando cambien los horarios
    $(document).on('change', '.horario-dia, .horario-inicio, .horario-fin', function() {
        actualizarVistaSemanal();
    });
    
    // Guardar horarios
    $('#btnGuardarHorarios').on('click', function() {
        if (!validarHorarios()) {
            return false;
        }
        
        const formData = new FormData($('#formHorarios')[0]);
        formData.append('accion', 'actualizar_horarios');
        
        mostrarCarga();
        $(this).prop('disabled', true);
        
        $.ajax({
            url: 'modales/asignaciones/procesar_asignaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarHorarios').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Horarios Actualizados!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalHorariosAsignacion').modal('hide');
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
                $('#btnGuardarHorarios').prop('disabled', false);
                mostrarError('Error al guardar horarios');
            }
        });
    });
    
    function validarHorarios() {
        let isValid = true;
        let conflictos = 0;
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar cada horario
        $('.horario-item-modal').each(function() {
            const dia = $(this).find('.horario-dia').val();
            const inicio = $(this).find('.horario-inicio').val();
            const fin = $(this).find('.horario-fin').val();
            
            // Validar campos requeridos
            if (!dia || !inicio || !fin) {
                $(this).find('.form-select, .form-control').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    }
                });
            }
            
            // Validar que hora fin sea mayor que hora inicio
            if (inicio && fin && inicio >= fin) {
                $(this).find('.horario-fin').addClass('is-invalid')
                    .after('<div class="invalid-feedback">La hora de fin debe ser mayor que la de inicio</div>');
                isValid = false;
            }
        });
        
        // Contar conflictos
        conflictos = $('.conflicto-horario').length;
        
        if (conflictos > 0) {
            Swal.fire({
                title: 'Conflictos Detectados',
                text: `Se detectaron ${conflictos} conflictos de horario. ¿Deseas guardar de todas formas?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fd7e14',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Revisar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Forzar guardado
                    $('#btnGuardarHorarios').trigger('click');
                }
            });
            return false;
        }
        
        return isValid;
    }
    
    // Limpiar al cerrar modal
    $('#modalHorariosAsignacion').on('hidden.bs.modal', function() {
        $('#horariosModalContainer').empty();
        contadorHorariosModal = 0;
        horariosActuales = [];
    });
});
</script>