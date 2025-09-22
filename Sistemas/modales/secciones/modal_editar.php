<?php
// modales/secciones/modal_editar.php
?>
<!-- Modal Editar Sección -->
<div class="modal fade" id="modalEditarSeccion" tabindex="-1" aria-labelledby="modalEditarSeccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header  text-dark">
                <h5 class="modal-title" id="modalEditarSeccionLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Sección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarSeccion" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información de la Sección
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_nivel_id" class="form-label">
                                                Nivel Educativo <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_nivel_id" name="nivel_id" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>" data-grados='<?= htmlspecialchars($nivel['grados']) ?>'>
                                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_periodo_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_periodo_id" name="periodo_academico_id" required>
                                                <option value="">Seleccionar período</option>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>">
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_grado" name="grado" required>
                                                <option value="">Seleccionar grado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_seccion" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="edit_seccion" name="seccion" 
                                                   maxlength="10" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_codigo" class="form-label">
                                                Código de Sección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="edit_codigo" name="codigo" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de Capacidad y Aula -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-users me-2"></i>
                                        Capacidad y Aula
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_capacidad_maxima" class="form-label">
                                                Capacidad Máxima <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_capacidad_maxima" name="capacidad_maxima" 
                                                   min="1" max="50" required>
                                            <div class="form-text">Número máximo de estudiantes</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_aula_asignada" class="form-label">
                                                Aula Asignada
                                            </label>
                                            <input type="text" class="form-control" id="edit_aula_asignada" name="aula_asignada" 
                                                   placeholder="Ej: Aula 101, Lab. Ciencias">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_activo" class="form-label">Estado</label>
                                            <select class="form-select" id="edit_activo" name="activo">
                                                <option value="1">Activa</option>
                                                <option value="0">Inactiva</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas de la Sección -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Estadísticas Actuales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="h3 text-primary" id="edit_estudiantes_activos">0</div>
                                        <small class="text-muted">Estudiantes Matriculados</small>
                                    </div>
                                    
                                    <div class="ocupacion-bar mb-3">
                                        <div class="ocupacion-fill bg-info" id="edit_ocupacion_bar" style="width: 0%"></div>
                                        <div class="ocupacion-text" id="edit_ocupacion_text">0/0</div>
                                    </div>
                                    
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold text-success" id="edit_cupos_disponibles">0</div>
                                                <small class="text-muted">Cupos Libres</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold text-info" id="edit_porcentaje_ocupacion">0%</div>
                                                <small class="text-muted">Ocupación</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alerta de Cambios -->
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Atención:</strong> Los cambios en capacidad pueden afectar estudiantes matriculados.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-info" id="btnValidarCambiosSeccion">
                        <i class="ti ti-check me-2"></i>
                        Validar Cambios
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarSeccion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cargar grados cuando se selecciona el nivel en edición
    $('#edit_nivel_id').on('change', function() {
        const nivelSeleccionado = $(this).find('option:selected');
        const gradosData = nivelSeleccionado.data('grados');
        const gradoSelect = $('#edit_grado');
        const gradoActual = gradoSelect.data('grado-actual');
        
        gradoSelect.empty().append('<option value="">Seleccionar grado</option>');
        
        if (gradosData) {
            try {
                const grados = typeof gradosData === 'string' ? JSON.parse(gradosData) : gradosData;
                if (Array.isArray(grados)) {
                    grados.forEach(grado => {
                        const selected = grado.nombre === gradoActual ? 'selected' : '';
                        gradoSelect.append(`<option value="${grado.nombre}" ${selected}>${grado.nombre}</option>`);
                    });
                }
            } catch (e) {
                console.error('Error parsing grados:', e);
            }
        }
    });

    // Validar cambios antes de guardar
    $('#btnValidarCambiosSeccion').on('click', function() {
        const seccionId = $('#edit_id').val();
        const nuevaCapacidad = $('#edit_capacidad_maxima').val();
        
        mostrarCarga();
        
        $.ajax({
            url: 'modales/secciones/procesar_secciones.php',
            type: 'POST',
            data: {
                accion: 'validar_cambios',
                id: seccionId,
                capacidad_maxima: nuevaCapacidad
            },
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                
                if (response.success) {
                    let alertType = 'success';
                    let alertTitle = 'Validación Correcta';
                    let message = '';
                    
                    if (response.advertencias && response.advertencias.length > 0) {
                        alertType = 'warning';
                        alertTitle = 'Validación con Advertencias';
                        message = '<ul class="text-start">';
                        response.advertencias.forEach(adv => {
                            message += `<li>${adv}</li>`;
                        });
                        message += '</ul>';
                    }
                    
                    if (response.ok) {
                        message += '<p class="text-success"><i class="ti ti-check me-2"></i>Los cambios son válidos</p>';
                    }
                    
                    Swal.fire({
                        title: alertTitle,
                        html: message || 'Los cambios propuestos son válidos',
                        icon: alertType,
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    Swal.fire({
                        title: 'Errores de Validación',
                        html: response.errores ? response.errores.join('<br>') : response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                mostrarError('Error al validar los cambios');
            }
        });
    });

    // Actualizar ocupación en tiempo real
    $('#edit_capacidad_maxima').on('change', function() {
        actualizarEstadisticasEdit();
    });

    // Convertir texto a mayúsculas
    $('#edit_seccion, #edit_codigo').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Envío del formulario de edición
    $('#formEditarSeccion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'editar');

        mostrarCarga();
        $('#btnActualizarSeccion').prop('disabled', true);

        $.ajax({
            url: 'modales/secciones/procesar_secciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarSeccion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Sección Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarSeccion').modal('hide');
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
                $('#btnActualizarSeccion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    function validarFormularioEditar() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar campos requeridos
        const camposRequeridos = ['#edit_nivel_id', '#edit_grado', '#edit_seccion', '#edit_capacidad_maxima', '#edit_codigo'];
        
        camposRequeridos.forEach(campo => {
            if (!$(campo).val()) {
                mostrarErrorCampo(campo, 'Este campo es requerido');
                isValid = false;
            }
        });
        
        // Validar capacidad
        const capacidad = parseInt($('#edit_capacidad_maxima').val());
        if (capacidad < 1 || capacidad > 50) {
            mostrarErrorCampo('#edit_capacidad_maxima', 'La capacidad debe estar entre 1 y 50 estudiantes');
            isValid = false;
        }
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    function actualizarEstadisticasEdit() {
        const capacidadMaxima = parseInt($('#edit_capacidad_maxima').val()) || 0;
        const estudiantesActivos = parseInt($('#edit_estudiantes_activos').text()) || 0;
        
        if (capacidadMaxima > 0) {
            const porcentaje = Math.round((estudiantesActivos / capacidadMaxima) * 100);
            const cuposDisponibles = capacidadMaxima - estudiantesActivos;
            
            $('#edit_ocupacion_text').text(`${estudiantesActivos}/${capacidadMaxima}`);
            $('#edit_ocupacion_bar').css('width', `${Math.min(porcentaje, 100)}%`);
            $('#edit_porcentaje_ocupacion').text(`${porcentaje}%`);
            
            if (cuposDisponibles < 0) {
                $('#edit_cupos_disponibles').text(`${Math.abs(cuposDisponibles)} sobrecupo`).removeClass('text-success').addClass('text-danger');
            } else {
                $('#edit_cupos_disponibles').text(cuposDisponibles).removeClass('text-danger').addClass('text-success');
            }
            
            // Cambiar color de la barra según ocupación
            const bar = $('#edit_ocupacion_bar');
            bar.removeClass('bg-success bg-info bg-warning bg-danger');
            if (porcentaje >= 100) {
                bar.addClass('bg-danger');
            } else if (porcentaje >= 90) {
                bar.addClass('bg-warning');
            } else if (porcentaje >= 70) {
                bar.addClass('bg-info');
            } else {
                bar.addClass('bg-success');
            }
        }
    }

    // Función global para cargar datos de edición
    window.cargarDatosEdicionSeccion = function(seccion) {
        $('#edit_id').val(seccion.id);
        $('#edit_nivel_id').val(seccion.nivel_id);
        $('#edit_periodo_id').val(seccion.periodo_academico_id);
        $('#edit_seccion').val(seccion.seccion);
        $('#edit_codigo').val(seccion.codigo);
        $('#edit_capacidad_maxima').val(seccion.capacidad_maxima);
        $('#edit_aula_asignada').val(seccion.aula_asignada || '');
        $('#edit_activo').val(seccion.activo);
        
        // Guardar grado actual para cuando se carguen las opciones
        $('#edit_grado').data('grado-actual', seccion.grado);
        
        // Cargar estadísticas
        $('#edit_estudiantes_activos').text(seccion.estudiantes_activos || 0);
        
        // Disparar evento change para cargar grados
        $('#edit_nivel_id').trigger('change');
        
        // Actualizar estadísticas
        setTimeout(() => {
            actualizarEstadisticasEdit();
        }, 500);
    };
});
</script>