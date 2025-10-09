<style>
    .campo-error {
        background-color: #fff5f5 !important;
        border: 2px solid #dc3545 !important;
        animation: shake 0.5s ease-in-out, pulse-red 1s infinite !important;
    }

    @keyframes shake {
        0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
        10%, 30%, 50%, 70% { transform: translateX(-5px); }
    }

    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    }

    /* Remover flechas de input number para año */
    #add_anio_academico::-webkit-outer-spin-button,
    #add_anio_academico::-webkit-inner-spin-button,
    #edit_anio_academico::-webkit-outer-spin-button,
    #edit_anio_academico::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    #add_anio_academico,
    #edit_anio_academico {
        -moz-appearance: textfield;
    }
</style>   

<!-- Modal Agregar Proceso de Admisión -->
<div class="modal fade" id="modalAgregarProceso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8e6cf 0%, #84d2c5 100%); color: #2d3748;">
                <h5 class="modal-title">
                    <i class="ti ti-school me-2"></i>
                    Nuevo Proceso de Admisión
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formAgregarProceso" method="POST">
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
                                            <label for="add_nombre" class="form-label">
                                                Nombre del Proceso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre" 
                                                   placeholder="Ej: Proceso de Admisión 2026" required maxlength="255" 
                                                   pattern="[A-Za-zÀ-ÿ0-9\s\-]{5,255}">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_anio_academico" class="form-label">
                                                Año Académico <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_anio_academico" name="anio_academico" 
                                                placeholder="2026" required 
                                                min="<?= $anio_actual ?>" 
                                                max="<?= $anio_actual + 5 ?>"
                                                maxlength="4"
                                                oninput="validarAnioAcademico(this, 'add')">
                                            <div class="form-text">
                                                <i class="ti ti-alert-circle me-1"></i>
                                                Debe ser exactamente 4 dígitos (entre <?= $anio_actual ?> y <?= $anio_actual + 5 ?>)
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_estado" class="form-label">
                                                Estado Inicial <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_estado" name="estado" required>
                                                <option value="CONFIGURACION">Configuración</option>
                                                <option value="ABIERTO" selected>Abierto</option>
                                                <option value="CERRADO">Cerrado</option>
                                                <option value="FINALIZADO">Finalizado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                ¿Proceso Activo? <span class="text-danger">*</span>
                                            </label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="add_activo" name="activo" checked>
                                                <label class="form-check-label" for="add_activo">
                                                    Activar proceso al crearlo
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
                                            <label for="add_fecha_inicio" class="form-label">
                                                Fecha de Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_inicio" 
                                                   name="fecha_inicio" required>
                                            <div class="form-text">Inicio de inscripciones</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha_fin" class="form-label">
                                                Fecha de Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_fin" 
                                                   name="fecha_fin" required>
                                            <div class="form-text">Cierre de inscripciones</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha_examen" class="form-label">
                                                Fecha de Examen
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_examen" 
                                                   name="fecha_examen">
                                            <div class="form-text">Opcional</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_costo_inscripcion" class="form-label">
                                                Costo de Inscripción (S/.) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_costo_inscripcion" 
                                                   name="costo_inscripcion" placeholder="0.00" required 
                                                   min="0" max="9999.99" step="0.01">
                                            <div class="form-text">Entre 0.00 y 9999.99</div>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label for="add_requisitos" class="form-label">
                                                Requisitos de Inscripción
                                            </label>
                                            <textarea class="form-control" id="add_requisitos" name="requisitos" 
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
                                    <div class="row" id="vacantes_container">
                                        <?php foreach ($niveles_educativos as $nivel): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-2">
                                                        <label class="form-label mb-1">
                                                            <strong><?= htmlspecialchars($nivel['nombre']) ?></strong>
                                                        </label>
                                                        <input type="number" class="form-control form-control-sm vacante-input" 
                                                               name="vacantes[<?= $nivel['id'] ?>]" 
                                                               placeholder="0" min="0" max="999" value="0"
                                                               data-nivel="<?= htmlspecialchars($nivel['nombre']) ?>">
                                                        <div class="form-text">Cantidad de vacantes</div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <strong>Total de Vacantes:</strong> <span id="total_vacantes">0</span>
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
                                            <label for="add_descripcion" class="form-label">
                                                Descripción del Proceso
                                            </label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción detallada del proceso de admisión"
                                                      maxlength="2000"></textarea>
                                            <div class="form-text">Máximo 2000 caracteres</div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="add_observaciones" class="form-label">
                                                Observaciones
                                            </label>
                                            <textarea class="form-control" id="add_observaciones" name="observaciones" 
                                                      rows="2" placeholder="Observaciones adicionales"
                                                      maxlength="500"></textarea>
                                            <div class="form-text">Máximo 500 caracteres</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarProceso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Proceso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Configurar fechas
    const hoy = new Date().toISOString().split('T')[0];
    $('#add_fecha_inicio, #add_fecha_fin, #add_fecha_examen').attr('min', hoy);

    // Calcular total de vacantes en tiempo real
    $('.vacante-input').on('input', function() {
        let total = 0;
        $('.vacante-input').each(function() {
            const valor = parseInt($(this).val()) || 0;
            total += valor;
        });
        $('#total_vacantes').text(total);
    });

    // Validar fecha de fin mayor que inicio
    $('#add_fecha_fin').on('change', function() {
        const inicio = $('#add_fecha_inicio').val();
        const fin = $(this).val();
        
        if (inicio && fin && fin < inicio) {
            mostrarErrorValidacion('La fecha de fin debe ser posterior a la fecha de inicio', '#add_fecha_fin');
            $(this).val('');
        }
    });

    // Validar fecha de examen
    $('#add_fecha_examen').on('change', function() {
        const inicio = $('#add_fecha_inicio').val();
        const fin = $('#add_fecha_fin').val();
        const examen = $(this).val();
        
        if (inicio && examen && examen < inicio) {
            mostrarErrorValidacion('La fecha de examen debe ser posterior a la fecha de inicio', '#add_fecha_examen');
            $(this).val('');
        }
        
        if (fin && examen && examen > fin) {
            mostrarErrorValidacion('La fecha de examen debe ser anterior o igual a la fecha de fin', '#add_fecha_examen');
            $(this).val('');
        }
    });

    // Envío del formulario
    $('#formAgregarProceso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioProceso()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarProceso').prop('disabled', true);

        $.ajax({
            url: 'modales/admision/procesar_admision.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarProceso').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Proceso Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarProceso').modal('hide');
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
                $('#btnGuardarProceso').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarProceso').on('hidden.bs.modal', function() {
        $('#formAgregarProceso')[0].reset();
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
        $('#total_vacantes').text('0');
    });
});

function validarFormularioProceso() {
    let isValid = true;
    let errores = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar nombre
    const nombre = $('#add_nombre').val().trim();
    if (!nombre || nombre.length < 5 || nombre.length > 255) {
        marcarCampoError('#add_nombre', 'El nombre debe tener entre 5 y 255 caracteres');
        errores.push('Nombre: longitud incorrecta (5-255 caracteres)');
        isValid = false;
    }
    
    // 2. Validar año académico CON VALIDACIÓN ESTRICTA DE 4 DÍGITOS
    if (!validarAnioAcademicoEnFormulario('#add_anio_academico', errores)) {
        isValid = false;
    }
    
    // 3. Validar fechas
    const fechaInicio = $('#add_fecha_inicio').val();
    const fechaFin = $('#add_fecha_fin').val();
    
    if (!fechaInicio) {
        marcarCampoError('#add_fecha_inicio', 'La fecha de inicio es obligatoria');
        errores.push('Fecha de inicio requerida');
        isValid = false;
    }
    
    if (!fechaFin) {
        marcarCampoError('#add_fecha_fin', 'La fecha de fin es obligatoria');
        errores.push('Fecha de fin requerida');
        isValid = false;
    }
    
    // 4. Validar que fecha fin sea mayor que inicio
    if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
        marcarCampoError('#add_fecha_fin', 'La fecha de fin debe ser posterior a la de inicio');
        errores.push('Fecha de fin debe ser posterior a inicio');
        isValid = false;
    }
    
    // 5. Validar costo
    const costo = parseFloat($('#add_costo_inscripcion').val());
    if (isNaN(costo) || costo < 0 || costo > 9999.99) {
        marcarCampoError('#add_costo_inscripcion', 'El costo debe estar entre 0.00 y 9999.99');
        errores.push('Costo: fuera del rango válido (0.00-9999.99)');
        isValid = false;
    }
    
    // 6. Validar que al menos haya una vacante
    let totalVacantes = 0;
    $('.vacante-input').each(function() {
        totalVacantes += parseInt($(this).val()) || 0;
    });
    
    if (totalVacantes === 0) {
        $('#vacantes_container').addClass('campo-error');
        errores.push('Debe asignar al menos una vacante');
        isValid = false;
    }
    
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL PROCESO\n\nErrores encontrados:\n\n• ${errores.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            footer: `Total de errores: ${errores.length}`
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function mostrarErrorValidacion(mensaje, selector) {
    $(selector).addClass('campo-error');
    Swal.fire({
        title: 'Error de Validación',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545',
        timer: 3000,
        timerProgressBar: true
    });
}
</script>

<style>
.campo-error {
    background-color: #fff5f5;
    border: 2px solid #dc3545 !important;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
    10%, 30%, 50%, 70% { transform: translateX(-5px); }
}
</style>