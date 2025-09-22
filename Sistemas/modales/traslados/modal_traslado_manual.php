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
                                                      rows="3" placeholder="Descripción del motivo del traslado..."></textarea>
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
    // Cargar secciones por nivel (origen)
    $('#nivel_origen').on('change', function() {
        const nivelId = $(this).val();
        if (nivelId) {
            cargarSecciones(nivelId, '#seccion_origen');
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
            cargarEstudiantes(seccionId);
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
            cargarSecciones(nivelId, '#seccion_destino');
            $('#seccion_destino').prop('disabled', false);
        } else {
            $('#seccion_destino').prop('disabled', true).html('<option value="">Primero selecciona el nivel</option>');
        }
    });

    // Envío del formulario
    $('#formTrasladoManual').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'traslado_manual');
        
        if (!validarFormularioTraslado()) {
            return false;
        }

        mostrarCarga();
        $('#btnEjecutarTraslado').prop('disabled', true);

        $.ajax({
            url: 'modales/traslados/procesar_traslados.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnEjecutarTraslado').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Traslado Exitoso!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
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
            error: function() {
                ocultarCarga();
                $('#btnEjecutarTraslado').prop('disabled', false);
                mostrarError('Error al procesar el traslado');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalTrasladoManual').on('hidden.bs.modal', function() {
        $('#formTrasladoManual')[0].reset();
        $('#seccion_origen, #estudiante_id, #seccion_destino').prop('disabled', true);
        $('#infoEstudianteSeleccionado').hide();
    });
});

function cargarSecciones(nivelId, selector) {
    $.ajax({
        url: 'modales/traslados/procesar_traslados.php',
        type: 'POST',
        data: { accion: 'obtener_secciones', nivel_id: nivelId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Seleccionar sección</option>';
                response.secciones.forEach(function(seccion) {
                    const ocupacion = `${seccion.estudiantes_actuales}/${seccion.capacidad_maxima}`;
                    const disponible = seccion.estudiantes_actuales < seccion.capacidad_maxima ? '' : ' (COMPLETA)';
                    options += `<option value="${seccion.id}">${seccion.grado} - ${seccion.seccion} (${ocupacion})${disponible}</option>`;
                });
                $(selector).html(options);
            }
        }
    });
}

function cargarEstudiantes(seccionId) {
    $.ajax({
        url: 'modales/traslados/procesar_traslados.php',
        type: 'POST',
        data: { accion: 'obtener_estudiantes', seccion_id: seccionId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Seleccionar estudiante</option>';
                response.estudiantes.forEach(function(estudiante) {
                    options += `<option value="${estudiante.matricula_id}">${estudiante.apellidos}, ${estudiante.nombres} (${estudiante.codigo_estudiante})</option>`;
                });
                $('#estudiante_id').html(options);
            }
        }
    });
}

function mostrarInfoEstudiante(matriculaId) {
    $.ajax({
        url: 'modales/traslados/procesar_traslados.php',
        type: 'POST',
        data: { accion: 'obtener_info_estudiante', matricula_id: matriculaId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const estudiante = response.estudiante;
                const html = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nombre Completo:</strong><br>
                            ${estudiante.nombres} ${estudiante.apellidos}
                        </div>
                        <div class="col-md-6">
                            <strong>Código:</strong><br>
                            ${estudiante.codigo_estudiante}
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Documento:</strong><br>
                            ${estudiante.documento_numero}
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Fecha Nacimiento:</strong><br>
                            ${estudiante.fecha_nacimiento}
                        </div>
                        <div class="col-12 mt-2">
                            <strong>Sección Actual:</strong><br>
                            ${estudiante.nivel_nombre} - ${estudiante.grado} ${estudiante.seccion}
                        </div>
                    </div>
                `;
                $('#datosEstudiante').html(html);
                $('#infoEstudianteSeleccionado').show();
            }
        }
    });
}

function validarFormularioTraslado() {
    const seccionOrigen = $('#seccion_origen').val();
    const seccionDestino = $('#seccion_destino').val();
    
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