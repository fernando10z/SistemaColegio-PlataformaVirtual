<!-- Modal Editar Proceso de Admisión -->
<div class="modal fade" id="modalEditarProceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-edit me-2"></i>
                    Editar Proceso de Admisión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formEditarProceso" method="POST">
                <input type="hidden" id="edit_proceso_id" name="proceso_id">
                
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
                                        <div class="col-md-8 mb-3">
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Proceso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" 
                                                   placeholder="Ej: Proceso de Admisión 2026" required maxlength="255" 
                                                   pattern="[A-Za-zÀ-ÿ0-9\s\-]{5,255}">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_anio_academico" class="form-label">
                                                Año Académico <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_anio_academico" name="anio_academico" 
                                                placeholder="2026" required 
                                                min="<?= $anio_actual ?>" 
                                                max="<?= $anio_actual + 5 ?>"
                                                maxlength="4"
                                                oninput="validarAnioAcademico(this, 'edit')">
                                            <div class="form-text">
                                                <i class="ti ti-alert-circle me-1"></i>
                                                Debe ser exactamente 4 dígitos (entre <?= $anio_actual ?> y <?= $anio_actual + 5 ?>)
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_estado" class="form-label">
                                                Estado del Proceso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_estado" name="estado" required>
                                                <option value="CONFIGURACION">Configuración</option>
                                                <option value="ABIERTO">Abierto</option>
                                                <option value="CERRADO">Cerrado</option>
                                                <option value="FINALIZADO">Finalizado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                ¿Proceso Activo? <span class="text-danger">*</span>
                                            </label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_activo" name="activo">
                                                <label class="form-check-label" for="edit_activo">
                                                    Proceso activo/habilitado
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Fechas y Costos -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar me-2"></i>
                                        Configuración de Fechas y Costos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_inicio" class="form-label">
                                                Fecha de Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_inicio" 
                                                   name="fecha_inicio" required>
                                            <div class="form-text">Inicio de inscripciones</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_fin" class="form-label">
                                                Fecha de Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_fin" 
                                                   name="fecha_fin" required>
                                            <div class="form-text">Cierre de inscripciones</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_examen" class="form-label">
                                                Fecha de Examen
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_examen" 
                                                   name="fecha_examen">
                                            <div class="form-text">Opcional</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_costo_inscripcion" class="form-label">
                                                Costo de Inscripción (S/.) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_costo_inscripcion" 
                                                   name="costo_inscripcion" placeholder="0.00" required 
                                                   min="0" max="9999.99" step="0.01">
                                            <div class="form-text">Entre 0.00 y 9999.99</div>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label for="edit_requisitos" class="form-label">
                                                Requisitos de Inscripción
                                            </label>
                                            <textarea class="form-control" id="edit_requisitos" name="requisitos" 
                                                      rows="3" placeholder="Lista de requisitos separados por enter"
                                                      maxlength="1000"></textarea>
                                            <div class="form-text">Máximo 1000 caracteres</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vacantes por Nivel -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-users me-2"></i>
                                        Vacantes por Nivel Educativo <span class="text-danger">*</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="edit_vacantes_container">
                                        <?php foreach ($niveles_educativos as $nivel): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-2">
                                                        <label class="form-label mb-1">
                                                            <strong><?= htmlspecialchars($nivel['nombre']) ?></strong>
                                                        </label>
                                                        <input type="number" class="form-control form-control-sm edit-vacante-input" 
                                                               name="vacantes[<?= $nivel['id'] ?>]" 
                                                               placeholder="0" min="0" max="999" value="0"
                                                               data-nivel="<?= htmlspecialchars($nivel['nombre']) ?>"
                                                               data-nivel-id="<?= $nivel['id'] ?>">
                                                        <div class="form-text">Cantidad de vacantes</div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <strong>Total de Vacantes:</strong> <span id="edit_total_vacantes">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-notes me-2"></i>
                                        Información Adicional
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_descripcion" class="form-label">
                                                Descripción del Proceso
                                            </label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción detallada del proceso de admisión"
                                                      maxlength="2000"></textarea>
                                            <div class="form-text">Máximo 2000 caracteres</div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_observaciones" class="form-label">
                                                Observaciones
                                            </label>
                                            <textarea class="form-control" id="edit_observaciones" name="observaciones" 
                                                      rows="2" placeholder="Observaciones adicionales"
                                                      maxlength="500"></textarea>
                                            <div class="form-text">Máximo 500 caracteres</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Postulaciones -->
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Postulaciones asociadas:</strong> <span id="edit_info_postulaciones">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnActualizarProceso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Proceso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para cargar datos en el modal de edición
function cargarDatosEdicionProceso(proceso) {
    $('#edit_proceso_id').val(proceso.id);
    $('#edit_nombre').val(proceso.nombre);
    $('#edit_anio_academico').val(proceso.anio_academico);
    $('#edit_estado').val(proceso.estado);
    $('#edit_activo').prop('checked', proceso.activo == 1);
    
    // Cargar configuración
    const config = proceso.configuracion || {};
    $('#edit_fecha_inicio').val(config.fecha_inicio || '');
    $('#edit_fecha_fin').val(config.fecha_fin || '');
    $('#edit_fecha_examen').val(config.fecha_examen || '');
    $('#edit_costo_inscripcion').val(config.costo_inscripcion || '0.00');
    $('#edit_requisitos').val(config.requisitos || '');
    $('#edit_descripcion').val(config.descripcion || '');
    $('#edit_observaciones').val(config.observaciones || '');
    
    // Cargar vacantes
    const vacantes = proceso.vacantes || [];
    $('.edit-vacante-input').val(0);
    
    vacantes.forEach(function(vacante) {
        $(`.edit-vacante-input[data-nivel="${vacante.nivel}"]`).val(vacante.cantidad);
    });
    
    // Calcular total de vacantes
    calcularTotalVacantesEdicion();
    
    // Mostrar información de postulaciones
    $('#edit_info_postulaciones').html(`
        <strong>${proceso.total_postulaciones || 0}</strong> postulaciones totales 
        (<strong>${proceso.admitidos || 0}</strong> admitidos, 
        <strong>${proceso.lista_espera || 0}</strong> en lista de espera)
    `);
}

function calcularTotalVacantesEdicion() {
    let total = 0;
    $('.edit-vacante-input').each(function() {
        total += parseInt($(this).val()) || 0;
    });
    $('#edit_total_vacantes').text(total);
}

$(document).ready(function() {
    // Calcular total de vacantes en tiempo real
    $('.edit-vacante-input').on('input', calcularTotalVacantesEdicion);

    // Validar fecha de fin mayor que inicio
    $('#edit_fecha_fin').on('change', function() {
        const inicio = $('#edit_fecha_inicio').val();
        const fin = $(this).val();
        
        if (inicio && fin && fin < inicio) {
            mostrarErrorValidacion('La fecha de fin debe ser posterior a la fecha de inicio', '#edit_fecha_fin');
            $(this).val('');
        }
    });

    // Validar fecha de examen
    $('#edit_fecha_examen').on('change', function() {
        const inicio = $('#edit_fecha_inicio').val();
        const fin = $('#edit_fecha_fin').val();
        const examen = $(this).val();
        
        if (inicio && examen && examen < inicio) {
            mostrarErrorValidacion('La fecha de examen debe ser posterior a la fecha de inicio', '#edit_fecha_examen');
            $(this).val('');
        }
        
        if (fin && examen && examen > fin) {
            mostrarErrorValidacion('La fecha de examen debe ser anterior o igual a la fecha de fin', '#edit_fecha_examen');
            $(this).val('');
        }
    });

    // Envío del formulario de edición
    $('#formEditarProceso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioProcesoEdicion()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarProceso').prop('disabled', true);

        $.ajax({
            url: 'modales/admision/procesar_admision.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarProceso').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Proceso Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarProceso').modal('hide');
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
                $('#btnActualizarProceso').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarProceso').on('hidden.bs.modal', function() {
        $('#formEditarProceso')[0].reset();
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
        $('#edit_total_vacantes').text('0');
    });
});

function validarFormularioProcesoEdicion() {
    let isValid = true;
    let errores = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar nombre
    const nombre = $('#edit_nombre').val().trim();
    if (!nombre || nombre.length < 5 || nombre.length > 255) {
        marcarCampoError('#edit_nombre', 'El nombre debe tener entre 5 y 255 caracteres');
        errores.push('Nombre: longitud incorrecta (5-255 caracteres)');
        isValid = false;
    }
    
    // 2. Validar año académico CON VALIDACIÓN ESTRICTA DE 4 DÍGITOS
    if (!validarAnioAcademicoEnFormulario('#edit_anio_academico', errores)) {
        isValid = false;
    }
    
    // ... resto de validaciones igual
    
    return isValid;
}
</script>