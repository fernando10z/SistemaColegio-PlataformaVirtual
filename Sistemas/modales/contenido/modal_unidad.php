<!-- Modal Unidad -->
<div class="modal fade" id="modalUnidad" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalUnidadLabel">
                    <i class="ti ti-book me-2"></i>Nueva Unidad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formUnidad">
                <input type="hidden" id="unidad_id" name="unidad_id">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo_unidad" name="titulo" required maxlength="255">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Orden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="orden_unidad" name="orden" required min="1">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion_unidad" name="descripcion" rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado_unidad" name="estado">
                                <option value="BORRADOR">Borrador</option>
                                <option value="PUBLICADO">Publicado</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio_unidad" name="fecha_inicio">
                            <div class="invalid-feedback" id="error_fecha_inicio"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin_unidad" name="fecha_fin">
                            <div class="invalid-feedback" id="error_fecha_fin"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarUnidad">
                        <i class="ti ti-device-floppy me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.invalid-feedback.show {
    display: block;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Configurar fecha mínima (hoy) al cargar el modal
    $('#modalUnidad').on('show.bs.modal', function() {
        const hoy = new Date().toISOString().split('T')[0];
        $('#fecha_inicio_unidad').attr('min', hoy);
        $('#fecha_fin_unidad').attr('min', hoy);
    });

    // Validación en tiempo real de fecha de inicio
    $('#fecha_inicio_unidad').on('change', function() {
        validarFechaInicio();
        // Si hay fecha fin, revalidarla también
        if ($('#fecha_fin_unidad').val()) {
            validarFechaFin();
        }
    });

    // Validación en tiempo real de fecha fin
    $('#fecha_fin_unidad').on('change', function() {
        validarFechaFin();
    });

    // Limpiar validaciones al cerrar el modal
    $('#modalUnidad').on('hidden.bs.modal', function() {
        limpiarFormularioUnidad();
    });

    // Submit del formulario con validación completa
    $('#formUnidad').on('submit', function(e) {
        e.preventDefault();
        
        // Validar todas las fechas antes de enviar
        const fechasValidas = validarTodasLasFechas();
        
        if (!fechasValidas) {
            Swal.fire({
                title: 'Error de Validación',
                text: 'Por favor, corrija los errores en las fechas antes de continuar',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }
        
        const formData = $(this).serialize();
        const unidadId = $('#unidad_id').val();
        const accion = unidadId ? 'actualizar_unidad' : 'crear_unidad';
        
        // Deshabilitar botón mientras se procesa
        $('#btnGuardarUnidad').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');
        
        $.post('modales/contenido/procesar.php', formData + '&accion=' + accion, function(response) {
            if (response.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
                $('#btnGuardarUnidad').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i>Guardar');
            }
        }, 'json').fail(function() {
            Swal.fire({
                title: 'Error',
                text: 'Error al procesar la solicitud',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
            $('#btnGuardarUnidad').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i>Guardar');
        });
    });
});

// Validar fecha de inicio
function validarFechaInicio() {
    const fechaInicio = $('#fecha_inicio_unidad').val();
    const inputFechaInicio = $('#fecha_inicio_unidad');
    const errorFechaInicio = $('#error_fecha_inicio');
    
    // Limpiar errores previos
    inputFechaInicio.removeClass('is-invalid');
    errorFechaInicio.removeClass('show').text('');
    
    if (!fechaInicio) {
        // Fecha inicio es opcional, no marcar error
        return true;
    }
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
    
    const fechaInicioObj = new Date(fechaInicio + 'T00:00:00');
    
    // Validar que no sea anterior a hoy
    if (fechaInicioObj < hoy) {
        inputFechaInicio.addClass('is-invalid');
        errorFechaInicio.addClass('show').text('La fecha de inicio no puede ser anterior a hoy');
        return false;
    }
    
    return true;
}

// Validar fecha fin
function validarFechaFin() {
    const fechaFin = $('#fecha_fin_unidad').val();
    const fechaInicio = $('#fecha_inicio_unidad').val();
    const inputFechaFin = $('#fecha_fin_unidad');
    const errorFechaFin = $('#error_fecha_fin');
    
    // Limpiar errores previos
    inputFechaFin.removeClass('is-invalid');
    errorFechaFin.removeClass('show').text('');
    
    if (!fechaFin) {
        // Fecha fin es opcional, no marcar error
        return true;
    }
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const fechaFinObj = new Date(fechaFin + 'T00:00:00');
    
    // Validar que no sea anterior a hoy
    if (fechaFinObj < hoy) {
        inputFechaFin.addClass('is-invalid');
        errorFechaFin.addClass('show').text('La fecha fin no puede ser anterior a hoy');
        return false;
    }
    
    // Si hay fecha de inicio, validar que fecha fin sea posterior
    if (fechaInicio) {
        const fechaInicioObj = new Date(fechaInicio + 'T00:00:00');
        
        if (fechaFinObj <= fechaInicioObj) {
            inputFechaFin.addClass('is-invalid');
            errorFechaFin.addClass('show').text('La fecha fin debe ser posterior a la fecha de inicio');
            return false;
        }
    }
    
    return true;
}

// Validar todas las fechas
function validarTodasLasFechas() {
    const fechaInicioValida = validarFechaInicio();
    const fechaFinValida = validarFechaFin();
    
    return fechaInicioValida && fechaFinValida;
}

// Limpiar formulario
function limpiarFormularioUnidad() {
    $('#formUnidad')[0].reset();
    $('#unidad_id').val('');
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').removeClass('show').text('');
    $('#btnGuardarUnidad').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i>Guardar');
}

// Función para abrir modal en modo agregar (llamada desde el botón principal)
function abrirModalUnidad() {
    limpiarFormularioUnidad();
    $('#modalUnidadLabel').text('Nueva Unidad');
    $('#modalUnidad').modal('show');
}
</script>