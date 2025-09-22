<?php
// modales/horarios/modal_editar.php
?>
<!-- Modal Editar Horarios -->
<div class="modal fade" id="modalEditarHorario" tabindex="-1" aria-labelledby="modalEditarHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarHorarioLabel">
                    <i class="ti ti-calendar-time me-2"></i>
                    Editar Horarios Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarHorario" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Selección de Docente y Asignación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-user me-2"></i>
                                        Información de Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_docente_id" class="form-label">
                                                Docente <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_docente_id" name="docente_id" required>
                                                <option value="">Seleccionar docente</option>
                                                <?php foreach ($docentes as $docente): ?>
                                                    <option value="<?= $docente['id'] ?>">
                                                        <?= htmlspecialchars($docente['apellidos'] . ', ' . $docente['nombres']) ?>
                                                        (<?= htmlspecialchars($docente['codigo_docente']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_asignacion_id" class="form-label">
                                                Asignación <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_asignacion_id" name="asignacion_id" required disabled>
                                                <option value="">Seleccionar asignación</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_horas_semanales" class="form-label">
                                                Horas Semanales <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_horas_semanales" 
                                                   name="horas_semanales" min="1" max="40" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_es_tutor" name="es_tutor">
                                                <label class="form-check-label" for="edit_es_tutor">
                                                    Es tutor de la sección
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Horarios -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-clock me-2"></i>
                                        Horarios de Clase
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarHorario">
                                        <i class="ti ti-plus me-1"></i>
                                        Agregar Horario
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="horariosContainer">
                                        <!-- Los horarios se cargarán dinámicamente aquí -->
                                    </div>
                                    
                                    <!-- Template para nuevo horario -->
                                    <div id="horarioTemplate" style="display: none;">
                                        <div class="horario-item border rounded p-3 mb-3 bg-light">
                                            <div class="row align-items-center">
                                                <div class="col-md-2">
                                                    <label class="form-label">Día</label>
                                                    <select class="form-select horario-dia" name="horarios[__INDEX__][dia]" required>
                                                        <option value="">Seleccionar</option>
                                                        <option value="1">Lunes</option>
                                                        <option value="2">Martes</option>
                                                        <option value="3">Miércoles</option>
                                                        <option value="4">Jueves</option>
                                                        <option value="5">Viernes</option>
                                                        <option value="6">Sábado</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Hora Inicio</label>
                                                    <input type="time" class="form-control horario-inicio" 
                                                           name="horarios[__INDEX__][hora_inicio]" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Hora Fin</label>
                                                    <input type="time" class="form-control horario-fin" 
                                                           name="horarios[__INDEX__][hora_fin]" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Aula</label>
                                                    <input type="text" class="form-control horario-aula" 
                                                           name="horarios[__INDEX__][aula]" placeholder="Ej: Aula 1001">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Duración</label>
                                                    <input type="text" class="form-control duracion-display" readonly>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-eliminar-horario">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validaciones y Alertas -->
                        <div class="col-12">
                            <div class="alert alert-info d-none" id="alertValidaciones">
                                <h6><i class="ti ti-info-circle me-2"></i>Validaciones:</h6>
                                <ul id="listaValidaciones" class="mb-0"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="btnValidarHorarios">
                        <i class="ti ti-check me-2"></i>
                        Validar Horarios
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarHorarios">
                        <i class="ti ti-device-floppy me-2"></i>
                        Guardar Horarios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    let horarioIndex = 0;

    // Cargar asignaciones cuando se selecciona un docente
    $('#edit_docente_id').on('change', function() {
        const docenteId = $(this).val();
        if (docenteId) {
            cargarAsignacionesDocente(docenteId);
        } else {
            $('#edit_asignacion_id').empty().append('<option value="">Seleccionar asignación</option>').prop('disabled', true);
        }
    });

    // Cargar datos cuando se selecciona una asignación
    $('#edit_asignacion_id').on('change', function() {
        const asignacionId = $(this).val();
        if (asignacionId) {
            cargarDatosAsignacion(asignacionId);
        }
    });

    // Agregar nuevo horario
    $('#btnAgregarHorario').on('click', function() {
        agregarNuevoHorario();
    });

    // Validar horarios
    $('#btnValidarHorarios').on('click', function() {
        validarHorarios();
    });

    // Eliminar horario
    $(document).on('click', '.btn-eliminar-horario', function() {
        $(this).closest('.horario-item').remove();
        validarHorarios();
    });

    // Calcular duración automáticamente
    $(document).on('change', '.horario-inicio, .horario-fin', function() {
        const horarioItem = $(this).closest('.horario-item');
        calcularDuracion(horarioItem);
        validarHorarios();
    });

    // Envío del formulario
    $('#formEditarHorario').on('submit', function(e) {
        e.preventDefault();
        guardarHorarios();
    });

    function cargarAsignacionesDocente(docenteId) {
        $.ajax({
            url: 'modales/horarios/procesar_horarios.php',
            type: 'POST',
            data: {
                accion: 'cargar_asignaciones',
                docente_id: docenteId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccionar asignación</option>';
                    response.asignaciones.forEach(function(asignacion) {
                        options += `<option value="${asignacion.id}">
                            ${asignacion.area_nombre} - ${asignacion.grado} ${asignacion.seccion}
                        </option>`;
                    });
                    $('#edit_asignacion_id').html(options).prop('disabled', false);
                }
            }
        });
    }

    function cargarDatosAsignacion(asignacionId) {
        $.ajax({
            url: 'modales/horarios/procesar_horarios.php',
            type: 'POST',
            data: {
                accion: 'cargar_datos',
                asignacion_id: asignacionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const asignacion = response.asignacion;
                    $('#edit_horas_semanales').val(asignacion.horas_semanales);
                    $('#edit_es_tutor').prop('checked', asignacion.es_tutor == 1);
                    
                    // Cargar horarios existentes
                    cargarHorarios(asignacion.horarios);
                }
            }
        });
    }

    function cargarHorarios(horarios) {
        $('#horariosContainer').empty();
        horarioIndex = 0;
        
        if (horarios && horarios.length > 0) {
            horarios.forEach(function(horario) {
                agregarNuevoHorario(horario);
            });
        } else {
            agregarNuevoHorario();
        }
    }

    function agregarNuevoHorario(datos = null) {
        const template = $('#horarioTemplate').html();
        const nuevoHorario = template.replace(/__INDEX__/g, horarioIndex);
        
        $('#horariosContainer').append(nuevoHorario);
        
        if (datos) {
            const horarioItem = $('#horariosContainer .horario-item').last();
            horarioItem.find('.horario-dia').val(datos.dia);
            horarioItem.find('.horario-inicio').val(datos.hora_inicio);
            horarioItem.find('.horario-fin').val(datos.hora_fin);
            horarioItem.find('.horario-aula').val(datos.aula);
            calcularDuracion(horarioItem);
        }
        
        horarioIndex++;
    }

    function calcularDuracion(horarioItem) {
        const inicio = horarioItem.find('.horario-inicio').val();
        const fin = horarioItem.find('.horario-fin').val();
        
        if (inicio && fin) {
            const inicioTime = new Date('2000-01-01 ' + inicio);
            const finTime = new Date('2000-01-01 ' + fin);
            
            if (finTime > inicioTime) {
                const diff = finTime - inicioTime;
                const horas = Math.floor(diff / (1000 * 60 * 60));
                const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                horarioItem.find('.duracion-display').val(`${horas}h ${minutos}m`);
            } else {
                horarioItem.find('.duracion-display').val('');
            }
        }
    }

    function validarHorarios() {
        const horarios = [];
        let errores = [];
        
        $('#horariosContainer .horario-item').each(function() {
            const dia = $(this).find('.horario-dia').val();
            const inicio = $(this).find('.horario-inicio').val();
            const fin = $(this).find('.horario-fin').val();
            
            if (dia && inicio && fin) {
                // Validar que hora fin sea mayor que hora inicio
                const inicioTime = new Date('2000-01-01 ' + inicio);
                const finTime = new Date('2000-01-01 ' + fin);
                
                if (finTime <= inicioTime) {
                    errores.push('La hora de fin debe ser mayor que la hora de inicio');
                }
                
                horarios.push({ dia, inicio, fin });
            }
        });
        
        // Validar cruces de horarios
        for (let i = 0; i < horarios.length; i++) {
            for (let j = i + 1; j < horarios.length; j++) {
                if (horarios[i].dia === horarios[j].dia) {
                    const inicio1 = new Date('2000-01-01 ' + horarios[i].inicio);
                    const fin1 = new Date('2000-01-01 ' + horarios[i].fin);
                    const inicio2 = new Date('2000-01-01 ' + horarios[j].inicio);
                    const fin2 = new Date('2000-01-01 ' + horarios[j].fin);
                    
                    if ((inicio1 < fin2 && fin1 > inicio2)) {
                        errores.push('Hay cruces de horarios en el mismo día');
                        break;
                    }
                }
            }
        }
        
        if (errores.length > 0) {
            let listaErrores = '';
            errores.forEach(error => {
                listaErrores += `<li>${error}</li>`;
            });
            $('#listaValidaciones').html(listaErrores);
            $('#alertValidaciones').removeClass('d-none');
            return false;
        } else {
            $('#alertValidaciones').addClass('d-none');
            return true;
        }
    }

    function guardarHorarios() {
        if (!validarHorarios()) {
            Swal.fire({
                title: 'Errores de Validación',
                text: 'Corrige los errores antes de guardar',
                icon: 'error'
            });
            return;
        }

        const formData = new FormData($('#formEditarHorario')[0]);
        formData.append('accion', 'guardar_horarios');

        mostrarCarga();
        $('#btnGuardarHorarios').prop('disabled', true);

        $.ajax({
            url: 'modales/horarios/procesar_horarios.php',
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
                        title: '¡Horarios Guardados!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarHorario').modal('hide');
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
                mostrarError('Error al procesar la solicitud');
            }
        });
    }
});

// Función para abrir modal con datos específicos
function editarHorarioDocente(docenteId) {
    $('#edit_docente_id').val(docenteId).trigger('change');
    $('#modalEditarHorario').modal('show');
}
</script>