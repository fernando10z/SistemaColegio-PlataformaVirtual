<?php
// Obtener periodo académico actual (desde el scope del archivo padre)
$periodo_id = $periodo_actual['id'] ?? 1;
?>

<!-- Modal Traslado Manual -->
<div class="modal fade" id="modalTrasladoManual" tabindex="-1" aria-labelledby="modalTrasladoManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalTrasladoManualLabel">
                    <i class="ti ti-transfer me-2"></i>
                    Traslado Manual de Estudiante
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formTrasladoManual" method="POST">
                <input type="hidden" name="periodo_academico_id" value="<?= $periodo_id ?>">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Selección de Estudiante -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-user me-2"></i>
                                        Seleccionar Estudiante
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nivel_origen" class="form-label">
                                                Nivel Actual <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="nivel_origen" name="nivel_origen" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="seccion_origen" class="form-label">
                                                Sección Actual <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="seccion_origen" name="seccion_origen" required disabled>
                                                <option value="">Primero selecciona el nivel</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="estudiante_id" class="form-label">
                                                Estudiante <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="estudiante_id" name="estudiante_id" required disabled>
                                                <option value="">Primero selecciona la sección</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Estudiante Seleccionado -->
                        <div class="col-12" id="infoEstudianteSeleccionado" style="display: none;">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información del Estudiante
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="datosEstudiante">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de Destino -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-target me-2"></i>
                                        Destino del Traslado
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nivel_destino" class="form-label">
                                                Nivel Destino <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="nivel_destino" name="nivel_destino" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>"><?= htmlspecialchars($nivel['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="seccion_destino" class="form-label">
                                                Sección Destino <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="seccion_destino" name="seccion_destino" required disabled>
                                                <option value="">Primero selecciona el nivel</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="motivo_traslado" class="form-label">Motivo del Traslado</label>
                                            <textarea class="form-control" id="motivo_traslado" name="motivo_traslado" 
                                                      rows="3" maxlength="500" placeholder="Descripción del motivo del traslado (opcional)..."></textarea>
                                            <div class="form-text">Caracteres restantes: <span id="contadorMotivo">500</span></div>
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
                    <button type="submit" class="btn btn-primary" id="btnEjecutarTraslado">
                        <i class="ti ti-transfer me-2"></i>
                        Ejecutar Traslado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    $(document).ready(function() {
        const periodoAcademicoId = <?= $periodo_id ?>;
        
        // Contador de caracteres para motivo
        $('#motivo_traslado').on('input', function() {
            const maxLength = 500;
            const currentLength = $(this).val().length;
            const remaining = maxLength - currentLength;
            $('#contadorMotivo').text(remaining);
        });
        
        // Cargar secciones por nivel (origen)
        $('#nivel_origen').on('change', function() {
            const nivelId = $(this).val();
            if (nivelId) {
                cargarSecciones(nivelId, '#seccion_origen', periodoAcademicoId);
                $('#seccion_origen').prop('disabled', false);
            } else {
                $('#seccion_origen').prop('disabled', true).html('<option value="">Primero selecciona el nivel</option>');
                $('#estudiante_id').prop('disabled', true).html('<option value="">Primero selecciona la sección</option>');
            }
            $('#infoEstudianteSeleccionado').hide();
        });

        // Cargar estudiantes por sección (origen)
        $('#seccion_origen').on('change', function() {
            const seccionId = $(this).val();
            if (seccionId) {
                cargarEstudiantes(seccionId, periodoAcademicoId);
                $('#estudiante_id').prop('disabled', false);
            } else {
                $('#estudiante_id').prop('disabled', true).html('<option value="">Primero selecciona la sección</option>');
            }
            $('#infoEstudianteSeleccionado').hide();
        });

        // Mostrar información del estudiante seleccionado
        $('#estudiante_id').on('change', function() {
            const matriculaId = $(this).val();
            if (matriculaId) {
                mostrarInfoEstudiante(matriculaId);
            } else {
                $('#infoEstudianteSeleccionado').hide();
            }
        });

        // Cargar secciones por nivel (destino)
        $('#nivel_destino').on('change', function() {
            const nivelId = $(this).val();
            if (nivelId) {
                cargarSecciones(nivelId, '#seccion_destino', periodoAcademicoId);
                $('#seccion_destino').prop('disabled', false);
            } else {
                $('#seccion_destino').prop('disabled', true).html('<option value="">Primero selecciona el nivel</option>');
            }
        });

        // Envío del formulario
        $('#formTrasladoManual').on('submit', function(e) {
            e.preventDefault();
            
            if (!validarFormularioTraslado()) {
                return false;
            }

            const formData = $(this).serialize();
            
            mostrarCarga();
            $('#btnEjecutarTraslado').prop('disabled', true);

            $.ajax({
                url: 'modales/traslados/procesar_traslados.php',
                type: 'POST',
                data: formData + '&accion=traslado_manual',
                dataType: 'json',
                success: function(response) {
                    ocultarCarga();
                    $('#btnEjecutarTraslado').prop('disabled', false);
                    
                    if (response.success) {
                        Swal.fire({
                            title: '¡Traslado Exitoso!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#198754',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalTrasladoManual').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error en Traslado',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    ocultarCarga();
                    $('#btnEjecutarTraslado').prop('disabled', false);
                    console.error('Error AJAX:', error);
                    Swal.fire({
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Limpiar formulario al cerrar modal
        $('#modalTrasladoManual').on('hidden.bs.modal', function() {
            $('#formTrasladoManual')[0].reset();
            $('#seccion_origen, #estudiante_id, #seccion_destino').prop('disabled', true);
            $('#infoEstudianteSeleccionado').hide();
            $('#contadorMotivo').text('500');
        });
    });

    function cargarSecciones(nivelId, selector, periodoId) {
        $(selector).html('<option value="">Cargando...</option>').prop('disabled', true);
        
        $.ajax({
            url: 'modales/traslados/procesar_traslados.php',
            type: 'POST',
            data: { 
                accion: 'obtener_secciones', 
                nivel_id: nivelId,
                periodo_academico_id: periodoId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccionar sección</option>';
                    
                    if (response.secciones.length === 0) {
                        options = '<option value="">No hay secciones disponibles</option>';
                    } else {
                        response.secciones.forEach(function(seccion) {
                            const ocupacion = `${seccion.estudiantes_actuales}/${seccion.capacidad_maxima}`;
                            const disponible = seccion.estudiantes_actuales < seccion.capacidad_maxima ? '' : ' (COMPLETA)';
                            const disabled = seccion.estudiantes_actuales >= seccion.capacidad_maxima && selector === '#seccion_destino' ? 'disabled' : '';
                            options += `<option value="${seccion.id}" ${disabled}>${seccion.grado} - Sección ${seccion.seccion} (${ocupacion})${disponible}</option>`;
                        });
                    }
                    
                    $(selector).html(options).prop('disabled', false);
                } else {
                    $(selector).html('<option value="">Error al cargar secciones</option>');
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                $(selector).html('<option value="">Error de conexión</option>');
                console.error('Error AJAX:', error);
            }
        });
    }

    function cargarEstudiantes(seccionId, periodoId) {
        $('#estudiante_id').html('<option value="">Cargando...</option>').prop('disabled', true);
        
        $.ajax({
            url: 'modales/traslados/procesar_traslados.php',
            type: 'POST',
            data: { 
                accion: 'obtener_estudiantes', 
                seccion_id: seccionId,
                periodo_academico_id: periodoId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta de estudiantes:', response); // Debug
                
                if (response.success) {
                    let options = '<option value="">Seleccionar estudiante</option>';
                    
                    if (response.estudiantes.length === 0) {
                        options = '<option value="">No hay estudiantes en esta sección</option>';
                    } else {
                        response.estudiantes.forEach(function(estudiante) {
                            options += `<option value="${estudiante.matricula_id}">${estudiante.apellidos}, ${estudiante.nombres} - ${estudiante.codigo_estudiante}</option>`;
                        });
                    }
                    
                    $('#estudiante_id').html(options).prop('disabled', false);
                } else {
                    $('#estudiante_id').html('<option value="">Error al cargar estudiantes</option>');
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#estudiante_id').html('<option value="">Error de conexión</option>');
                console.error('Error AJAX:', xhr.responseText);
            }
        });
    }

    function mostrarInfoEstudiante(matriculaId) {
        $.ajax({
            url: 'modales/traslados/procesar_traslados.php',
            type: 'POST',
            data: { 
                accion: 'obtener_info_estudiante', 
                matricula_id: matriculaId 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const e = response.estudiante;
                    const html = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border-start border-primary border-4 ps-3">
                                    <small class="text-muted d-block">Nombre Completo</small>
                                    <strong>${e.nombres} ${e.apellidos}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-primary border-4 ps-3">
                                    <small class="text-muted d-block">Código</small>
                                    <strong>${e.codigo_estudiante}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-success border-4 ps-3">
                                    <small class="text-muted d-block">Documento</small>
                                    <strong>${e.documento_tipo}: ${e.documento_numero}</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-start border-success border-4 ps-3">
                                    <small class="text-muted d-block">Fecha Nacimiento</small>
                                    <strong>${e.fecha_nacimiento}</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border-start border-info border-4 ps-3">
                                    <small class="text-muted d-block">Sección Actual</small>
                                    <strong class="text-primary">${e.nivel_nombre} - ${e.grado} Sección ${e.seccion}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#datosEstudiante').html(html);
                    $('#infoEstudianteSeleccionado').slideDown();
                } else {
                    console.error('Error al obtener info:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
            }
        });
    }

    function validarFormularioTraslado() {
        const seccionOrigen = $('#seccion_origen').val();
        const seccionDestino = $('#seccion_destino').val();
        const estudianteId = $('#estudiante_id').val();
        
        if (!estudianteId) {
            Swal.fire({
                title: 'Validación',
                text: 'Debe seleccionar un estudiante',
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }
        
        if (!seccionDestino) {
            Swal.fire({
                title: 'Validación',
                text: 'Debe seleccionar una sección de destino',
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }
        
        if (seccionOrigen === seccionDestino) {
            Swal.fire({
                title: 'Error de Validación',
                text: 'La sección de origen y destino no pueden ser la misma',
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }
        
        return true;
    }
</script>