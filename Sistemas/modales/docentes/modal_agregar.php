<!-- Modal Agregar Docente -->
<div class="modal fade" id="modalAgregarDocente" tabindex="-1" aria-labelledby="modalAgregarDocenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarDocenteLabel">
                    <i class="ti ti-user-plus me-2"></i>
                    Nuevo Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarDocente" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Personal -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-user me-2"></i>
                                        Información Personal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="add_codigo_docente" class="form-label">Código Docente</label>
                                            <input type="text" class="form-control" id="add_codigo_docente" name="codigo_docente" 
                                                   placeholder="DOC001" maxlength="10" pattern="[A-Z0-9]{3,10}">
                                            <div class="form-text">Se generará automáticamente si se deja vacío</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_nombres" class="form-label">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombres" name="nombres" 
                                                   placeholder="Nombres completos" required maxlength="50" 
                                                   pattern="[A-Za-zÀ-ÿ\s]{2,50}">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="add_apellidos" class="form-label">
                                                Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_apellidos" name="apellidos" 
                                                   placeholder="Apellidos completos" required maxlength="50"
                                                   pattern="[A-Za-zÀ-ÿ\s]{2,50}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_documento_tipo" class="form-label">
                                                Tipo Documento <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_documento_tipo" name="documento_tipo" required>
                                                <option value="DNI" selected>DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_documento_numero" class="form-label">
                                                Número Documento <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_documento_numero" 
                                                   name="documento_numero" placeholder="12345678" required 
                                                   maxlength="8" pattern="[0-9]{8}">
                                            <div class="form-text" id="doc_help_text">Exactamente 8 dígitos numéricos</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="add_email" name="email" 
                                                   placeholder="docente@example.com" required maxlength="100">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_telefono" class="form-label">
                                                Teléfono <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" class="form-control" id="add_telefono" name="telefono" 
                                                   placeholder="999123456" required pattern="[0-9]{9}" maxlength="9">
                                            <div class="form-text">9 dígitos sin espacios ni guiones</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_direccion" class="form-label">
                                                Dirección <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="add_direccion" name="direccion" 
                                                      rows="2" placeholder="Dirección completa" required 
                                                      minlength="10" maxlength="200"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Profesionales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-school me-2"></i>
                                        Datos Profesionales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="add_grado_academico" class="form-label">
                                                Grado Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_grado_academico" name="grado_academico" required>
                                                <option value="">Seleccionar grado</option>
                                                <option value="Licenciado en Educación">Licenciado en Educación</option>
                                                <option value="Magister en Educación">Magister en Educación</option>
                                                <option value="Doctor en Educación">Doctor en Educación</option>
                                                <option value="Licenciado en Matemática">Licenciado en Matemática</option>
                                                <option value="Licenciado en Biología">Licenciado en Biología</option>
                                                <option value="Licenciado en Historia">Licenciado en Historia</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_universidad" class="form-label">
                                                Universidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_universidad" name="universidad" 
                                                   placeholder="Universidad de egreso" required maxlength="100" 
                                                   pattern="[A-Za-zÀ-ÿ\s\-\.]{5,100}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_especialidad" class="form-label">
                                                Especialidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_especialidad" name="especialidad" 
                                                   placeholder="Especialidad profesional" required maxlength="100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_colegiatura" class="form-label">Número de Colegiatura</label>
                                            <input type="text" class="form-control" id="add_colegiatura" name="colegiatura" 
                                                   placeholder="CPPe12345" maxlength="15" pattern="[A-Za-z0-9]{4,15}">
                                            <div class="form-text">Mínimo 4 caracteres alfanuméricos (opcional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Laborales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-briefcase me-2"></i>
                                        Datos Laborales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="add_categoria" class="form-label">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_categoria" name="categoria" required>
                                                <option value="">Seleccionar categoría</option>
                                                <option value="I">Categoría I</option>
                                                <option value="II">Categoría II</option>
                                                <option value="III">Categoría III</option>
                                                <option value="IV">Categoría IV</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_tipo_contrato" class="form-label">
                                                Tipo de Contrato <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo_contrato" name="tipo_contrato" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="NOMBRADO">Nombrado</option>
                                                <option value="CONTRATADO">Contratado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_nivel_magisterial" class="form-label">Nivel Magisterial</label>
                                            <select class="form-select" id="add_nivel_magisterial" name="nivel_magisterial">
                                                <option value="">Seleccionar nivel</option>
                                                <option value="I">Nivel I</option>
                                                <option value="II">Nivel II</option>
                                                <option value="III">Nivel III</option>
                                                <option value="IV">Nivel IV</option>
                                                <option value="V">Nivel V</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_fecha_ingreso" class="form-label">
                                                Fecha de Ingreso <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_ingreso" name="fecha_ingreso" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Especialidades -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-book me-2"></i>
                                        Áreas de Especialidad <span class="text-danger">*</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="areas_especialidad_container">
                                        <?php foreach ($areas_curriculares as $area): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="area_<?= $area['id'] ?>" name="areas_especialidad[]" value="<?= $area['id'] ?>">
                                                    <label class="form-check-label" for="area_<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="form-text">Seleccione al menos una especialidad</div>
                                </div>
                            </div>
                        </div>

                        <!-- Foto de Perfil -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-photo me-2"></i>
                                        Foto de Perfil (Opcional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar-preview">
                                                <img id="preview-avatar-add" 
                                                     src="../assets/images/profile/user-default.jpg" 
                                                     alt="Vista previa" 
                                                     class="rounded-circle" 
                                                     width="80" height="80">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="file" class="form-control" id="add_foto" name="foto" 
                                                   accept="image/jpeg,image/png,image/gif">
                                            <div class="form-text">Formatos: JPG, PNG, GIF. Máximo 2MB. Mínimo 150x150px.</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarDocente">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Docente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Estilos para validaciones -->
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

.swal-wide {
    width: 600px !important;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Configurar fecha máxima y mínima
    const hoy = new Date().toISOString().split('T')[0];
    const fechaMinima = new Date();
    fechaMinima.setFullYear(fechaMinima.getFullYear() - 50);
    const fechaMinimaStr = fechaMinima.toISOString().split('T')[0];
    
    $('#add_fecha_ingreso').attr('max', hoy);
    $('#add_fecha_ingreso').attr('min', fechaMinimaStr);

    // Validación de documento según tipo (inicializar para DNI por defecto)
    $('#add_documento_tipo').trigger('change');

    // Validación de documento según tipo
    $('#add_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#add_documento_numero');
        const helpText = $('#doc_help_text');
        
        docInput.removeClass('is-invalid campo-error');
        docInput.val('');
        
        if (tipo === 'DNI') {
            docInput.attr('maxlength', '8')
                    .attr('minlength', '8')
                    .attr('pattern', '[0-9]{8}')
                    .attr('placeholder', '12345678');
            helpText.text('Exactamente 8 dígitos numéricos');
        } else if (tipo === 'CE') {
            docInput.attr('maxlength', '12')
                    .attr('minlength', '12')
                    .attr('pattern', '[0-9A-Za-z]{12}')
                    .attr('placeholder', 'ABC123456789');
            helpText.text('Exactamente 12 caracteres alfanuméricos');
        } else if (tipo === 'PASAPORTE') {
            docInput.attr('maxlength', '12')
                    .attr('minlength', '6')
                    .removeAttr('pattern')
                    .attr('placeholder', 'ABC123456');
            helpText.text('Entre 6 y 12 caracteres alfanuméricos');
        }
    });

    // Validación en tiempo real para documento
    $('#add_documento_numero').on('input', function() {
        const tipo = $('#add_documento_tipo').val();
        const valor = $(this).val();
        
        if (tipo === 'DNI') {
            $(this).val(valor.replace(/[^0-9]/g, ''));
        } else if (tipo === 'CE') {
            $(this).val(valor.replace(/[^0-9A-Za-z]/g, ''));
        }
    });

    // Validación en tiempo real para teléfono
    $('#add_telefono').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Validación en tiempo real para nombres y apellidos
    $('#add_nombres, #add_apellidos').on('input', function() {
        $(this).val($(this).val().replace(/[^A-Za-zÀ-ÿ\s]/g, ''));
    });

    // Preview de imagen con validaciones
    $('#add_foto').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validar tamaño
            if (file.size > 2 * 1024 * 1024) {
                mostrarErrorValidacion('La imagen no debe superar los 2MB', '#add_foto');
                $(this).val('');
                return;
            }

            // Validar formato
            const formatosPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
            if (!formatosPermitidos.includes(file.type)) {
                mostrarErrorValidacion('Solo se permiten archivos JPG, PNG o GIF', '#add_foto');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Validar dimensiones mínimas
                    if (this.width < 150 || this.height < 150) {
                        mostrarErrorValidacion('La imagen debe tener al menos 150x150 píxeles', '#add_foto');
                        $('#add_foto').val('');
                        return;
                    }
                    $('#preview-avatar-add').attr('src', e.target.result);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Envío del formulario con validaciones completas
    $('#formAgregarDocente').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioCompleto()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarDocente').prop('disabled', true);

        $.ajax({
            url: 'modales/docentes/procesar_docentes.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarDocente').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Docente Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarDocente').modal('hide');
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
                $('#btnGuardarDocente').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarDocente').on('hidden.bs.modal', function() {
        limpiarFormulario();
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA CON 28 VALIDACIONES
function validarFormularioCompleto() {
    let isValid = true;
    let erroresEncontrados = [];
    
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar nombres (obligatorio, solo letras y espacios, 2-50 caracteres)
    const nombres = $('#add_nombres').val().trim();
    if (!nombres) {
        marcarCampoError('#add_nombres', 'Los nombres son obligatorios');
        erroresEncontrados.push('Nombres requeridos');
        isValid = false;
    } else if (nombres.length < 2 || nombres.length > 50) {
        marcarCampoError('#add_nombres', 'Los nombres deben tener entre 2 y 50 caracteres');
        erroresEncontrados.push('Nombres: longitud incorrecta (2-50 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(nombres)) {
        marcarCampoError('#add_nombres', 'Los nombres solo pueden contener letras y espacios');
        erroresEncontrados.push('Nombres: solo letras y espacios permitidos');
        isValid = false;
    }
    
    // 2. Validar apellidos (obligatorio, solo letras y espacios, 2-50 caracteres)
    const apellidos = $('#add_apellidos').val().trim();
    if (!apellidos) {
        marcarCampoError('#add_apellidos', 'Los apellidos son obligatorios');
        erroresEncontrados.push('Apellidos requeridos');
        isValid = false;
    } else if (apellidos.length < 2 || apellidos.length > 50) {
        marcarCampoError('#add_apellidos', 'Los apellidos deben tener entre 2 y 50 caracteres');
        erroresEncontrados.push('Apellidos: longitud incorrecta (2-50 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(apellidos)) {
        marcarCampoError('#add_apellidos', 'Los apellidos solo pueden contener letras y espacios');
        erroresEncontrados.push('Apellidos: solo letras y espacios permitidos');
        isValid = false;
    }
    
    // 3. Validar tipo de documento
    const tipoDoc = $('#add_documento_tipo').val();
    if (!tipoDoc) {
        marcarCampoError('#add_documento_tipo', 'Debe seleccionar un tipo de documento');
        erroresEncontrados.push('Tipo de documento requerido');
        isValid = false;
    }
    
    // 4-6. Validar número de documento según tipo
    const numeroDoc = $('#add_documento_numero').val().trim();
    if (!numeroDoc) {
        marcarCampoError('#add_documento_numero', 'El número de documento es obligatorio');
        erroresEncontrados.push('Número de documento requerido');
        isValid = false;
    } else if (tipoDoc === 'DNI') {
        if (numeroDoc.length !== 8 || !/^[0-9]{8}$/.test(numeroDoc)) {
            marcarCampoError('#add_documento_numero', 'El DNI debe tener exactamente 8 dígitos numéricos');
            erroresEncontrados.push('DNI: debe tener exactamente 8 dígitos');
            isValid = false;
        }
    } else if (tipoDoc === 'CE') {
        if (numeroDoc.length !== 12 || !/^[0-9A-Za-z]{12}$/.test(numeroDoc)) {
            marcarCampoError('#add_documento_numero', 'El Carnet de Extranjería debe tener exactamente 12 caracteres alfanuméricos');
            erroresEncontrados.push('CE: debe tener exactamente 12 caracteres');
            isValid = false;
        }
    } else if (tipoDoc === 'PASAPORTE') {
        if (numeroDoc.length < 6 || numeroDoc.length > 12) {
            marcarCampoError('#add_documento_numero', 'El Pasaporte debe tener entre 6 y 12 caracteres');
            erroresEncontrados.push('Pasaporte: longitud incorrecta (6-12 caracteres)');
            isValid = false;
        }
    }
    
    // 7. Validar email (obligatorio, formato válido)
    const email = $('#add_email').val().trim();
    if (!email) {
        marcarCampoError('#add_email', 'El email es obligatorio');
        erroresEncontrados.push('Email requerido');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        marcarCampoError('#add_email', 'Ingrese un email válido');
        erroresEncontrados.push('Email: formato inválido');
        isValid = false;
    } else if (email.length > 100) {
        marcarCampoError('#add_email', 'El email no puede superar los 100 caracteres');
        erroresEncontrados.push('Email: muy largo (máximo 100 caracteres)');
        isValid = false;
    }
    
    // 8. Validar teléfono (obligatorio, 9 dígitos)
    const telefono = $('#add_telefono').val().trim();
    if (!telefono) {
        marcarCampoError('#add_telefono', 'El teléfono es obligatorio');
        erroresEncontrados.push('Teléfono requerido');
        isValid = false;
    } else if (!/^[0-9]{9}$/.test(telefono)) {
        marcarCampoError('#add_telefono', 'El teléfono debe tener exactamente 9 dígitos');
        erroresEncontrados.push('Teléfono: debe tener exactamente 9 dígitos');
        isValid = false;
    } else if (!telefono.startsWith('9')) {
        marcarCampoError('#add_telefono', 'Los teléfonos móviles deben empezar con 9');
        erroresEncontrados.push('Teléfono: debe empezar con 9');
        isValid = false;
    }
    
    // 9. Validar dirección (obligatorio, 10-200 caracteres)
    const direccion = $('#add_direccion').val().trim();
    if (!direccion) {
        marcarCampoError('#add_direccion', 'La dirección es obligatoria');
        erroresEncontrados.push('Dirección requerida');
        isValid = false;
    } else if (direccion.length < 10 || direccion.length > 200) {
        marcarCampoError('#add_direccion', 'La dirección debe tener entre 10 y 200 caracteres');
        erroresEncontrados.push('Dirección: longitud incorrecta (10-200 caracteres)');
        isValid = false;
    }
    
    // 10. Validar grado académico
    const grado = $('#add_grado_academico').val();
    if (!grado) {
        marcarCampoError('#add_grado_academico', 'Debe seleccionar un grado académico');
        erroresEncontrados.push('Grado académico requerido');
        isValid = false;
    }
    
    // 11. Validar universidad (obligatorio, 5-100 caracteres)
    const universidad = $('#add_universidad').val().trim();
    if (!universidad) {
        marcarCampoError('#add_universidad', 'La universidad es obligatoria');
        erroresEncontrados.push('Universidad requerida');
        isValid = false;
    } else if (universidad.length < 5 || universidad.length > 100) {
        marcarCampoError('#add_universidad', 'La universidad debe tener entre 5 y 100 caracteres');
        erroresEncontrados.push('Universidad: longitud incorrecta (5-100 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s\-\.]+$/.test(universidad)) {
        marcarCampoError('#add_universidad', 'La universidad solo puede contener letras, espacios, guiones y puntos');
        erroresEncontrados.push('Universidad: caracteres inválidos');
        isValid = false;
    }
    
    // 12. Validar especialidad (obligatorio)
    const especialidad = $('#add_especialidad').val().trim();
    if (!especialidad) {
        marcarCampoError('#add_especialidad', 'La especialidad es obligatoria');
        erroresEncontrados.push('Especialidad requerida');
        isValid = false;
    } else if (especialidad.length < 3 || especialidad.length > 100) {
        marcarCampoError('#add_especialidad', 'La especialidad debe tener entre 3 y 100 caracteres');
        erroresEncontrados.push('Especialidad: longitud incorrecta (3-100 caracteres)');
        isValid = false;
    }
    
    // 13. Validar colegiatura (opcional, pero si está debe tener formato correcto)
    const colegiatura = $('#add_colegiatura').val().trim();
    if (colegiatura && (colegiatura.length < 4 || colegiatura.length > 15 || !/^[A-Za-z0-9]+$/.test(colegiatura))) {
        marcarCampoError('#add_colegiatura', 'La colegiatura debe tener entre 4 y 15 caracteres alfanuméricos');
        erroresEncontrados.push('Colegiatura: formato inválido (4-15 caracteres alfanuméricos)');
        isValid = false;
    }
    
    // 14. Validar categoría
    const categoria = $('#add_categoria').val();
    if (!categoria) {
        marcarCampoError('#add_categoria', 'Debe seleccionar una categoría');
        erroresEncontrados.push('Categoría requerida');
        isValid = false;
    }
    
    // 15. Validar tipo de contrato
    const contrato = $('#add_tipo_contrato').val();
    if (!contrato) {
        marcarCampoError('#add_tipo_contrato', 'Debe seleccionar un tipo de contrato');
        erroresEncontrados.push('Tipo de contrato requerido');
        isValid = false;
    }
    
    // 16-17. Validar fecha de ingreso (obligatorio, no futura, no muy antigua)
    const fechaIngreso = $('#add_fecha_ingreso').val();
    if (!fechaIngreso) {
        marcarCampoError('#add_fecha_ingreso', 'La fecha de ingreso es obligatoria');
        erroresEncontrados.push('Fecha de ingreso requerida');
        isValid = false;
    } else {
        const fecha = new Date(fechaIngreso);
        const hoy = new Date();
        const fechaMinima = new Date();
        fechaMinima.setFullYear(fechaMinima.getFullYear() - 50);
        
        if (fecha > hoy) {
            marcarCampoError('#add_fecha_ingreso', 'La fecha de ingreso no puede ser futura');
            erroresEncontrados.push('Fecha de ingreso: no puede ser futura');
            isValid = false;
        } else if (fecha < fechaMinima) {
            marcarCampoError('#add_fecha_ingreso', 'La fecha de ingreso no puede ser mayor a 50 años');
            erroresEncontrados.push('Fecha de ingreso: muy antigua (máximo 50 años)');
            isValid = false;
        }
    }
    
    // 18. Validar que al menos una especialidad esté seleccionada
    const especialidadesSeleccionadas = $('input[name="areas_especialidad[]"]:checked').length;
    if (especialidadesSeleccionadas === 0) {
        $('#areas_especialidad_container').addClass('campo-error');
        erroresEncontrados.push('Debe seleccionar al menos una especialidad');
        isValid = false;
    }
    
    // 19. Validar código docente (opcional, pero si está debe tener formato correcto)
    const codigoDocente = $('#add_codigo_docente').val().trim();
    if (codigoDocente && (codigoDocente.length < 3 || codigoDocente.length > 10 || !/^[A-Z0-9]+$/.test(codigoDocente))) {
        marcarCampoError('#add_codigo_docente', 'El código debe tener entre 3 y 10 caracteres alfanuméricos en mayúsculas');
        erroresEncontrados.push('Código docente: formato inválido (3-10 chars mayúsculas)');
        isValid = false;
    }
    
    // 20. Validar que nombres y apellidos no sean iguales
    if (nombres && apellidos && nombres.toLowerCase() === apellidos.toLowerCase()) {
        marcarCampoError('#add_apellidos', 'Los apellidos no pueden ser iguales a los nombres');
        erroresEncontrados.push('Nombres y apellidos no pueden ser iguales');
        isValid = false;
    }
    
    // 21. Validar que el email no tenga dominios temporales
    const dominiosProhibidos = ['temp-mail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
    if (email) {
        const dominio = email.split('@')[1];
        if (dominiosProhibidos.includes(dominio)) {
            marcarCampoError('#add_email', 'No se permiten emails temporales');
            erroresEncontrados.push('Email: dominio temporal no permitido');
            isValid = false;
        }
    }
    
    // 22. Validar que no haya más de 3 especialidades seleccionadas
    if (especialidadesSeleccionadas > 5) {
        $('#areas_especialidad_container').addClass('campo-error');
        erroresEncontrados.push('No puede seleccionar más de 5 especialidades');
        isValid = false;
    }
    
    // 23. Validar coherencia entre grado académico y especialidad
    if (grado === 'Doctor en Educación' && universidad && universidad.length < 10) {
        marcarCampoError('#add_universidad', 'Para grado de Doctor, especifique universidad completa');
        erroresEncontrados.push('Universidad: especificar completa para Doctor');
        isValid = false;
    }
    
    // 24. Validar que el nombre no contenga números
    if (nombres && /\d/.test(nombres)) {
        marcarCampoError('#add_nombres', 'Los nombres no pueden contener números');
        erroresEncontrados.push('Nombres: no pueden contener números');
        isValid = false;
    }
    
    // 25. Validar que los apellidos no contengan números
    if (apellidos && /\d/.test(apellidos)) {
        marcarCampoError('#add_apellidos', 'Los apellidos no pueden contener números');
        erroresEncontrados.push('Apellidos: no pueden contener números');
        isValid = false;
    }
    
    // 26. Validar longitud mínima de nombres y apellidos por separado
    if (nombres) {
        const nombresArray = nombres.split(' ').filter(n => n.length > 0);
        if (nombresArray.length === 0 || nombresArray.some(n => n.length < 2)) {
            marcarCampoError('#add_nombres', 'Cada nombre debe tener al menos 2 caracteres');
            erroresEncontrados.push('Nombres: cada uno debe tener mínimo 2 caracteres');
            isValid = false;
        }
    }
    
    // 27. Validar longitud mínima de apellidos por separado
    if (apellidos) {
        const apellidosArray = apellidos.split(' ').filter(a => a.length > 0);
        if (apellidosArray.length === 0 || apellidosArray.some(a => a.length < 2)) {
            marcarCampoError('#add_apellidos', 'Cada apellido debe tener al menos 2 caracteres');
            erroresEncontrados.push('Apellidos: cada uno debe tener mínimo 2 caracteres');
            isValid = false;
        }
    }
    
    // 28. Validar que la dirección contenga información útil
    if (direccion && direccion.length >= 10) {
        if (!/\d/.test(direccion)) {
            marcarCampoError('#add_direccion', 'La dirección debe incluir al menos un número');
            erroresEncontrados.push('Dirección: debe incluir numeración');
            isValid = false;
        }
    }
    
    // Mostrar errores si los hay
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL DOCENTE\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
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
        
        // Hacer scroll al primer campo con error
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

// Función para marcar campo con error
function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

// Función para mostrar error específico de validación
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

// Funciones auxiliares que deben existir en tu sistema
function mostrarCarga() {
    // Implementar según tu sistema de loading
    $('#btnGuardarDocente').html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');
}

function ocultarCarga() {
    // Restaurar botón
    $('#btnGuardarDocente').html('<i class="ti ti-device-floppy me-2"></i>Crear Docente');
}

function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
}

function limpiarFormulario() {
    $('#formAgregarDocente')[0].reset();
    $('#preview-avatar-add').attr('src', '../assets/images/profile/user-default.jpg');
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#doc_help_text').text('Exactamente 8 dígitos numéricos');
    // Reinicializar validación de documento para DNI
    $('#add_documento_tipo').trigger('change');
}

// Función original mantenida para compatibilidad
function validarFormularioDocenteAgregar() {
    return validarFormularioCompleto();
}

function mostrarErrorCampoDocente(campo, mensaje) {
    marcarCampoError(campo, mensaje);
}
</script>