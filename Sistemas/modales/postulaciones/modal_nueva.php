<!-- Modal Nueva Postulación -->
<div class="modal fade" id="modalNuevaPostulacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffd3a5 0%, #fd6585 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-school me-2"></i>
                    Nueva Postulación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formNuevaPostulacion" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Proceso de Admisión -->
                        <div class="col-12 mb-3">
                            <div class="card border-0" style="background-color: #fff8f0;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #ffa94d;">
                                        <i class="ti ti-bookmark me-2"></i>
                                        Proceso de Admisión
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="form-label">Proceso <span class="text-danger">*</span></label>
                                            <select class="form-select" id="add_proceso_id" name="proceso_id" required>
                                                <option value="">Seleccionar proceso...</option>
                                                <?php foreach ($procesos_admision as $proceso): 
                                                    if ($proceso['estado'] == 'ABIERTO'):
                                                ?>
                                                    <option value="<?= $proceso['id'] ?>">
                                                        <?= htmlspecialchars($proceso['nombre']) ?> - <?= $proceso['anio_academico'] ?>
                                                    </option>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Grado Solicitado <span class="text-danger">*</span></label>
                                            <select class="form-select" id="add_grado_solicitado" name="grado_solicitado" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="3 años - Inicial">3 años - Inicial</option>
                                                <option value="4 años - Inicial">4 años - Inicial</option>
                                                <option value="5 años - Inicial">5 años - Inicial</option>
                                                <option value="1ro Primaria">1ro Primaria</option>
                                                <option value="2do Primaria">2do Primaria</option>
                                                <option value="3ro Primaria">3ro Primaria</option>
                                                <option value="4to Primaria">4to Primaria</option>
                                                <option value="5to Primaria">5to Primaria</option>
                                                <option value="6to Primaria">6to Primaria</option>
                                                <option value="1ro Secundaria">1ro Secundaria</option>
                                                <option value="2do Secundaria">2do Secundaria</option>
                                                <option value="3ro Secundaria">3ro Secundaria</option>
                                                <option value="4to Secundaria">4to Secundaria</option>
                                                <option value="5to Secundaria">5to Secundaria</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Postulante -->
                        <div class="col-12 mb-3">
                            <div class="card border-0" style="background-color: #f0f8ff;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #4a90e2;">
                                        <i class="ti ti-user me-2"></i>
                                        Datos del Postulante
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_postulante_nombres" 
                                                   name="postulante_nombres" required placeholder="Nombres completos" 
                                                   maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{2,100}">
                                            <div class="form-text">Solo letras y espacios (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_postulante_apellidos" 
                                                   name="postulante_apellidos" required placeholder="Apellidos completos" 
                                                   maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{2,100}">
                                            <div class="form-text">Solo letras y espacios (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="add_postulante_documento_tipo" 
                                                    name="postulante_documento_tipo" required>
                                                <option value="DNI" selected>DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">N° Documento <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_postulante_documento_numero" 
                                                   name="postulante_documento_numero" required maxlength="8" 
                                                   placeholder="12345678" pattern="[0-9]{8}">
                                            <div class="form-text" id="postulante_doc_help">Exactamente 8 dígitos numéricos</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="add_postulante_fecha_nacimiento" 
                                                   name="postulante_fecha_nacimiento" required>
                                            <div class="form-text">Edad adecuada para el grado</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Lugar de Nacimiento</label>
                                            <input type="text" class="form-control" name="postulante_lugar_nacimiento" 
                                                   placeholder="Ciudad, País" maxlength="100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Colegio Procedencia</label>
                                            <input type="text" class="form-control" name="postulante_colegio_procedencia" 
                                                   placeholder="Nombre del colegio anterior" maxlength="150">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Apoderado -->
                        <div class="col-12">
                            <div class="card border-0" style="background-color: #f0fff4;">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3" style="color: #48bb78;">
                                        <i class="ti ti-users me-2"></i>
                                        Datos del Apoderado
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_apoderado_nombres" 
                                                   name="apoderado_nombres" required placeholder="Nombres completos" 
                                                   maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{2,100}">
                                            <div class="form-text">Solo letras y espacios (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_apoderado_apellidos" 
                                                   name="apoderado_apellidos" required placeholder="Apellidos completos" 
                                                   maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{2,100}">
                                            <div class="form-text">Solo letras y espacios (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="add_apoderado_documento_tipo" 
                                                    name="apoderado_documento_tipo" required>
                                                <option value="DNI" selected>DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">N° Documento <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="add_apoderado_documento_numero" 
                                                   name="apoderado_documento_numero" required maxlength="8" 
                                                   placeholder="12345678" pattern="[0-9]{8}">
                                            <div class="form-text" id="apoderado_doc_help">Exactamente 8 dígitos numéricos</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Parentesco <span class="text-danger">*</span></label>
                                            <select class="form-select" name="apoderado_parentesco" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="PADRE">Padre</option>
                                                <option value="MADRE">Madre</option>
                                                <option value="TUTOR">Tutor Legal</option>
                                                <option value="ABUELO">Abuelo/a</option>
                                                <option value="TIO">Tío/a</option>
                                                <option value="OTRO">Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="add_apoderado_email" 
                                                   name="apoderado_email" required placeholder="correo@ejemplo.com" 
                                                   maxlength="100">
                                            <div class="form-text">Formato: usuario@dominio.com</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="add_apoderado_telefono" 
                                                   name="apoderado_telefono" required placeholder="999123456" 
                                                   pattern="[0-9]{9}" maxlength="9">
                                            <div class="form-text">9 dígitos, debe empezar con 9</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="add_apoderado_direccion" 
                                                      name="apoderado_direccion" required rows="2" 
                                                      placeholder="Dirección completa" minlength="10" 
                                                      maxlength="200"></textarea>
                                            <div class="form-text">Mínimo 10 caracteres, máximo 200</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPostulacion">
                        <i class="ti ti-device-floppy me-2"></i>Registrar Postulación
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
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    // Configurar fechas máximas y mínimas para fecha de nacimiento
    const hoy = new Date();
    const fechaMaxima = new Date(hoy.getFullYear() - 2, hoy.getMonth(), hoy.getDate());
    const fechaMinima = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
    
    $('#add_postulante_fecha_nacimiento').attr('max', fechaMaxima.toISOString().split('T')[0]);
    $('#add_postulante_fecha_nacimiento').attr('min', fechaMinima.toISOString().split('T')[0]);

    // Validación documento postulante
    $('#add_postulante_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#add_postulante_documento_numero');
        const helpText = $('#postulante_doc_help');
        
        docInput.removeClass('is-invalid campo-error').val('');
        
        if (tipo === 'DNI') {
            docInput.attr('maxlength', '8').attr('minlength', '8')
                    .attr('pattern', '[0-9]{8}').attr('placeholder', '12345678');
            helpText.text('Exactamente 8 dígitos numéricos');
        } else if (tipo === 'CE') {
            docInput.attr('maxlength', '12').attr('minlength', '12')
                    .attr('pattern', '[0-9A-Za-z]{12}').attr('placeholder', 'ABC123456789');
            helpText.text('Exactamente 12 caracteres alfanuméricos');
        } else if (tipo === 'PASAPORTE') {
            docInput.attr('maxlength', '12').attr('minlength', '6')
                    .removeAttr('pattern').attr('placeholder', 'ABC123456');
            helpText.text('Entre 6 y 12 caracteres alfanuméricos');
        }
    });

    // Validación documento apoderado
    $('#add_apoderado_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#add_apoderado_documento_numero');
        const helpText = $('#apoderado_doc_help');
        
        docInput.removeClass('is-invalid campo-error').val('');
        
        if (tipo === 'DNI') {
            docInput.attr('maxlength', '8').attr('minlength', '8')
                    .attr('pattern', '[0-9]{8}').attr('placeholder', '12345678');
            helpText.text('Exactamente 8 dígitos numéricos');
        } else if (tipo === 'CE') {
            docInput.attr('maxlength', '12').attr('minlength', '12')
                    .attr('pattern', '[0-9A-Za-z]{12}').attr('placeholder', 'ABC123456789');
            helpText.text('Exactamente 12 caracteres alfanuméricos');
        } else if (tipo === 'PASAPORTE') {
            docInput.attr('maxlength', '12').attr('minlength', '6')
                    .removeAttr('pattern').attr('placeholder', 'ABC123456');
            helpText.text('Entre 6 y 12 caracteres alfanuméricos');
        }
    });

    // Validación en tiempo real - solo números para DNI
    $('#add_postulante_documento_numero, #add_apoderado_documento_numero').on('input', function() {
        const tipo = $(this).attr('id').includes('postulante') ? 
                     $('#add_postulante_documento_tipo').val() : 
                     $('#add_apoderado_documento_tipo').val();
        const valor = $(this).val();
        
        if (tipo === 'DNI') {
            $(this).val(valor.replace(/[^0-9]/g, ''));
        } else if (tipo === 'CE') {
            $(this).val(valor.replace(/[^0-9A-Za-z]/g, ''));
        }
    });

    // Validación teléfono - solo números
    $('#add_apoderado_telefono').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Validación nombres - solo letras
    $('#add_postulante_nombres, #add_postulante_apellidos, #add_apoderado_nombres, #add_apoderado_apellidos').on('input', function() {
        $(this).val($(this).val().replace(/[^A-Za-zÀ-ÿ\s]/g, ''));
    });

    // Envío del formulario con validaciones
    $('#formNuevaPostulacion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioPostulacion()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarPostulacion').prop('disabled', true);

        $.ajax({
            url: 'modales/postulaciones/procesar_postulaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarPostulacion').prop('disabled', false);
                
                if (response.success) {
                    mostrarExito(response.message);
                    $('#modalNuevaPostulacion').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarPostulacion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar al cerrar
    $('#modalNuevaPostulacion').on('hidden.bs.modal', function() {
        limpiarFormularioPostulacion();
    });

    // Inicializar validaciones de documento
    $('#add_postulante_documento_tipo, #add_apoderado_documento_tipo').trigger('change');
});

// FUNCIÓN DE VALIDACIÓN COMPLETA CON 30 VALIDACIONES
function validarFormularioPostulacion() {
    let isValid = true;
    let erroresEncontrados = [];
    
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar proceso seleccionado
    const proceso = $('#add_proceso_id').val();
    if (!proceso) {
        marcarCampoError('#add_proceso_id', 'Debe seleccionar un proceso de admisión');
        erroresEncontrados.push('Proceso de admisión requerido');
        isValid = false;
    }
    
    // 2. Validar grado seleccionado
    const grado = $('#add_grado_solicitado').val();
    if (!grado) {
        marcarCampoError('#add_grado_solicitado', 'Debe seleccionar un grado');
        erroresEncontrados.push('Grado solicitado requerido');
        isValid = false;
    }
    
    // 3-5. Validar nombres del postulante
    const postulanteNombres = $('#add_postulante_nombres').val().trim();
    if (!postulanteNombres) {
        marcarCampoError('#add_postulante_nombres', 'Los nombres son obligatorios');
        erroresEncontrados.push('Nombres del postulante requeridos');
        isValid = false;
    } else if (postulanteNombres.length < 2 || postulanteNombres.length > 100) {
        marcarCampoError('#add_postulante_nombres', 'Los nombres deben tener entre 2 y 100 caracteres');
        erroresEncontrados.push('Nombres: longitud incorrecta (2-100)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(postulanteNombres)) {
        marcarCampoError('#add_postulante_nombres', 'Los nombres solo pueden contener letras y espacios');
        erroresEncontrados.push('Nombres: solo letras permitidas');
        isValid = false;
    } else if (/\d/.test(postulanteNombres)) {
        marcarCampoError('#add_postulante_nombres', 'Los nombres no pueden contener números');
        erroresEncontrados.push('Nombres: no pueden tener números');
        isValid = false;
    }
    
    // 6-8. Validar apellidos del postulante
    const postulanteApellidos = $('#add_postulante_apellidos').val().trim();
    if (!postulanteApellidos) {
        marcarCampoError('#add_postulante_apellidos', 'Los apellidos son obligatorios');
        erroresEncontrados.push('Apellidos del postulante requeridos');
        isValid = false;
    } else if (postulanteApellidos.length < 2 || postulanteApellidos.length > 100) {
        marcarCampoError('#add_postulante_apellidos', 'Los apellidos deben tener entre 2 y 100 caracteres');
        erroresEncontrados.push('Apellidos: longitud incorrecta (2-100)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(postulanteApellidos)) {
        marcarCampoError('#add_postulante_apellidos', 'Los apellidos solo pueden contener letras y espacios');
        erroresEncontrados.push('Apellidos: solo letras permitidas');
        isValid = false;
    } else if (/\d/.test(postulanteApellidos)) {
        marcarCampoError('#add_postulante_apellidos', 'Los apellidos no pueden contener números');
        erroresEncontrados.push('Apellidos: no pueden tener números');
        isValid = false;
    }
    
    // 9-11. Validar documento del postulante
    const postulanteDocTipo = $('#add_postulante_documento_tipo').val();
    const postulanteDocNumero = $('#add_postulante_documento_numero').val().trim();
    
    if (!postulanteDocNumero) {
        marcarCampoError('#add_postulante_documento_numero', 'El número de documento es obligatorio');
        erroresEncontrados.push('Documento postulante requerido');
        isValid = false;
    } else if (postulanteDocTipo === 'DNI') {
        if (postulanteDocNumero.length !== 8) {
            marcarCampoError('#add_postulante_documento_numero', 'El DNI debe tener exactamente 8 dígitos');
            erroresEncontrados.push('DNI postulante: debe tener exactamente 8 dígitos');
            isValid = false;
        } else if (!/^[0-9]{8}$/.test(postulanteDocNumero)) {
            marcarCampoError('#add_postulante_documento_numero', 'El DNI solo puede contener números');
            erroresEncontrados.push('DNI postulante: solo números permitidos');
            isValid = false;
        }
    } else if (postulanteDocTipo === 'CE') {
        if (postulanteDocNumero.length !== 12) {
            marcarCampoError('#add_postulante_documento_numero', 'El CE debe tener exactamente 12 caracteres');
            erroresEncontrados.push('CE postulante: debe tener 12 caracteres');
            isValid = false;
        } else if (!/^[0-9A-Za-z]{12}$/.test(postulanteDocNumero)) {
            marcarCampoError('#add_postulante_documento_numero', 'El CE solo puede contener letras y números');
            erroresEncontrados.push('CE postulante: formato inválido');
            isValid = false;
        }
    } else if (postulanteDocTipo === 'PASAPORTE') {
        if (postulanteDocNumero.length < 6 || postulanteDocNumero.length > 12) {
            marcarCampoError('#add_postulante_documento_numero', 'El Pasaporte debe tener entre 6 y 12 caracteres');
            erroresEncontrados.push('Pasaporte postulante: longitud incorrecta (6-12)');
            isValid = false;
        }
    }
    
    // 12-13. Validar fecha de nacimiento
    const fechaNacimiento = $('#add_postulante_fecha_nacimiento').val();
    if (!fechaNacimiento) {
        marcarCampoError('#add_postulante_fecha_nacimiento', 'La fecha de nacimiento es obligatoria');
        erroresEncontrados.push('Fecha de nacimiento requerida');
        isValid = false;
    } else {
        const fecha = new Date(fechaNacimiento);
        const hoy = new Date();
        const edad = hoy.getFullYear() - fecha.getFullYear();
        
        if (edad < 2 || edad > 18) {
            marcarCampoError('#add_postulante_fecha_nacimiento', 'La edad debe estar entre 2 y 18 años');
            erroresEncontrados.push('Edad fuera de rango (2-18 años)');
            isValid = false;
        }
        
        if (fecha > hoy) {
            marcarCampoError('#add_postulante_fecha_nacimiento', 'La fecha no puede ser futura');
            erroresEncontrados.push('Fecha de nacimiento futura');
            isValid = false;
        }
    }
    
    // 14-16. Validar nombres del apoderado
    const apoderadoNombres = $('#add_apoderado_nombres').val().trim();
    if (!apoderadoNombres) {
        marcarCampoError('#add_apoderado_nombres', 'Los nombres del apoderado son obligatorios');
        erroresEncontrados.push('Nombres del apoderado requeridos');
        isValid = false;
    } else if (apoderadoNombres.length < 2 || apoderadoNombres.length > 100) {
        marcarCampoError('#add_apoderado_nombres', 'Los nombres deben tener entre 2 y 100 caracteres');
        erroresEncontrados.push('Nombres apoderado: longitud incorrecta');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(apoderadoNombres)) {
        marcarCampoError('#add_apoderado_nombres', 'Los nombres solo pueden contener letras y espacios');
        erroresEncontrados.push('Nombres apoderado: solo letras');
        isValid = false;
    }
    
    // 17-19. Validar apellidos del apoderado
    const apoderadoApellidos = $('#add_apoderado_apellidos').val().trim();
    if (!apoderadoApellidos) {
        marcarCampoError('#add_apoderado_apellidos', 'Los apellidos del apoderado son obligatorios');
        erroresEncontrados.push('Apellidos del apoderado requeridos');
        isValid = false;
    } else if (apoderadoApellidos.length < 2 || apoderadoApellidos.length > 100) {
        marcarCampoError('#add_apoderado_apellidos', 'Los apellidos deben tener entre 2 y 100 caracteres');
        erroresEncontrados.push('Apellidos apoderado: longitud incorrecta');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(apoderadoApellidos)) {
        marcarCampoError('#add_apoderado_apellidos', 'Los apellidos solo pueden contener letras y espacios');
        erroresEncontrados.push('Apellidos apoderado: solo letras');
        isValid = false;
    }
    
    // 20-22. Validar documento del apoderado
    const apoderadoDocTipo = $('#add_apoderado_documento_tipo').val();
    const apoderadoDocNumero = $('#add_apoderado_documento_numero').val().trim();
    
    if (!apoderadoDocNumero) {
        marcarCampoError('#add_apoderado_documento_numero', 'El número de documento es obligatorio');
        erroresEncontrados.push('Documento apoderado requerido');
        isValid = false;
    } else if (apoderadoDocTipo === 'DNI') {
        if (apoderadoDocNumero.length !== 8) {
            marcarCampoError('#add_apoderado_documento_numero', 'El DNI debe tener exactamente 8 dígitos');
            erroresEncontrados.push('DNI apoderado: exactamente 8 dígitos');
            isValid = false;
        } else if (!/^[0-9]{8}$/.test(apoderadoDocNumero)) {
            marcarCampoError('#add_apoderado_documento_numero', 'El DNI solo puede contener números');
            erroresEncontrados.push('DNI apoderado: solo números');
            isValid = false;
        }
    } else if (apoderadoDocTipo === 'CE') {
        if (apoderadoDocNumero.length !== 12) {
            marcarCampoError('#add_apoderado_documento_numero', 'El CE debe tener exactamente 12 caracteres');
            erroresEncontrados.push('CE apoderado: 12 caracteres');
            isValid = false;
        }
    } else if (apoderadoDocTipo === 'PASAPORTE') {
        if (apoderadoDocNumero.length < 6 || apoderadoDocNumero.length > 12) {
            marcarCampoError('#add_apoderado_documento_numero', 'El Pasaporte debe tener entre 6 y 12 caracteres');
            erroresEncontrados.push('Pasaporte apoderado: 6-12 caracteres');
            isValid = false;
        }
    }
    
    // 23. Validar parentesco
    const parentesco = $('[name="apoderado_parentesco"]').val();
    if (!parentesco) {
        marcarCampoError('[name="apoderado_parentesco"]', 'Debe seleccionar el parentesco');
        erroresEncontrados.push('Parentesco requerido');
        isValid = false;
    }
    
    // 24-25. Validar email
    const email = $('#add_apoderado_email').val().trim();
    if (!email) {
        marcarCampoError('#add_apoderado_email', 'El email es obligatorio');
        erroresEncontrados.push('Email requerido');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        marcarCampoError('#add_apoderado_email', 'Formato de email inválido');
        erroresEncontrados.push('Email: formato inválido');
        isValid = false;
    } else if (email.length > 100) {
        marcarCampoError('#add_apoderado_email', 'El email no puede superar 100 caracteres');
        erroresEncontrados.push('Email: muy largo (max 100)');
        isValid = false;
    }
    
    // 26-27. Validar teléfono
    const telefono = $('#add_apoderado_telefono').val().trim();
    if (!telefono) {
        marcarCampoError('#add_apoderado_telefono', 'El teléfono es obligatorio');
        erroresEncontrados.push('Teléfono requerido');
        isValid = false;
    } else if (!/^[0-9]{9}$/.test(telefono)) {
        marcarCampoError('#add_apoderado_telefono', 'El teléfono debe tener exactamente 9 dígitos');
        erroresEncontrados.push('Teléfono: 9 dígitos exactos');
        isValid = false;
    } else if (!telefono.startsWith('9')) {
        marcarCampoError('#add_apoderado_telefono', 'El teléfono móvil debe empezar con 9');
        erroresEncontrados.push('Teléfono: debe empezar con 9');
        isValid = false;
    }
    
    // 28. Validar dirección
    const direccion = $('#add_apoderado_direccion').val().trim();
    if (!direccion) {
        marcarCampoError('#add_apoderado_direccion', 'La dirección es obligatoria');
        erroresEncontrados.push('Dirección requerida');
        isValid = false;
    } else if (direccion.length < 10 || direccion.length > 200) {
        marcarCampoError('#add_apoderado_direccion', 'La dirección debe tener entre 10 y 200 caracteres');
        erroresEncontrados.push('Dirección: longitud incorrecta (10-200)');
        isValid = false;
    }
    
    // 29. Validar que documentos no sean iguales
    if (postulanteDocNumero && apoderadoDocNumero && postulanteDocNumero === apoderadoDocNumero) {
        marcarCampoError('#add_apoderado_documento_numero', 'El documento del apoderado no puede ser igual al del postulante');
        erroresEncontrados.push('Documentos duplicados');
        isValid = false;
    }
    
    // 30. Validar email con dominios temporales
    const dominiosProhibidos = ['temp-mail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
    if (email) {
        const dominio = email.split('@')[1];
        if (dominiosProhibidos.includes(dominio)) {
            marcarCampoError('#add_apoderado_email', 'No se permiten emails temporales');
            erroresEncontrados.push('Email: dominio temporal no permitido');
            isValid = false;
        }
    }
    
    // Mostrar errores
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE REGISTRAR LA POSTULACIÓN\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            customClass: {
                popup: 'swal-wide'
            },
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

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function limpiarFormularioPostulacion() {
    $('#formNuevaPostulacion')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#postulante_doc_help').text('Exactamente 8 dígitos numéricos');
    $('#apoderado_doc_help').text('Exactamente 8 dígitos numéricos');
    $('#add_postulante_documento_tipo, #add_apoderado_documento_tipo').trigger('change');
}
</script>