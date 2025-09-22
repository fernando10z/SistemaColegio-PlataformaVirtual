<!-- Modal Editar Docente -->
<div class="modal fade" id="modalEditarDocente" tabindex="-1" aria-labelledby="modalEditarDocenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #fd7e14 0%, #e67e22 100%); color: white;">
                <h5 class="modal-title" id="modalEditarDocenteLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarDocente" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_docente_id" name="docente_id">
                
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
                                            <label for="edit_codigo_docente" class="form-label">Código Docente</label>
                                            <input type="text" class="form-control" id="edit_codigo_docente" name="codigo_docente" 
                                                   maxlength="10" pattern="[A-Z0-9]{3,10}">
                                            <div class="form-text">3-10 caracteres alfanuméricos en mayúsculas</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_nombres" class="form-label">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombres" name="nombres" 
                                                   required maxlength="50" pattern="[A-Za-zÀ-ÿ\s]{2,50}">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="edit_apellidos" class="form-label">
                                                Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_apellidos" name="apellidos" 
                                                   required maxlength="50" pattern="[A-Za-zÀ-ÿ\s]{2,50}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_documento_tipo" class="form-label">
                                                Tipo Documento <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_documento_tipo" name="documento_tipo" required>
                                                <option value="DNI">DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_documento_numero" class="form-label">
                                                Número Documento <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_documento_numero" 
                                                   name="documento_numero" required>
                                            <div class="form-text" id="edit_doc_help_text">Validación según tipo de documento</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="edit_email" name="email" 
                                                   required maxlength="100">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_telefono" class="form-label">
                                                Teléfono <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" class="form-control" id="edit_telefono" name="telefono" 
                                                   required pattern="[0-9]{9}" maxlength="9" placeholder="999123456">
                                            <div class="form-text">9 dígitos, debe empezar con 9</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="edit_direccion" class="form-label">
                                                Dirección <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="edit_direccion" name="direccion" 
                                                      rows="2" required minlength="10" maxlength="200" 
                                                      placeholder="Dirección completa con numeración"></textarea>
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
                                            <label for="edit_grado_academico" class="form-label">
                                                Grado Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_grado_academico" name="grado_academico" required>
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
                                            <label for="edit_universidad" class="form-label">
                                                Universidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_universidad" name="universidad" 
                                                   required maxlength="100" pattern="[A-Za-zÀ-ÿ\s\-\.]{5,100}"
                                                   placeholder="Universidad de egreso">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_especialidad" class="form-label">
                                                Especialidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_especialidad" name="especialidad" 
                                                   required maxlength="100" placeholder="Especialidad profesional">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_colegiatura" class="form-label">Número de Colegiatura</label>
                                            <input type="text" class="form-control" id="edit_colegiatura" name="colegiatura" 
                                                   maxlength="15" pattern="[A-Za-z0-9]{4,15}" placeholder="CPPe12345">
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
                                            <label for="edit_categoria" class="form-label">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_categoria" name="categoria" required>
                                                <option value="">Seleccionar categoría</option>
                                                <option value="I">Categoría I</option>
                                                <option value="II">Categoría II</option>
                                                <option value="III">Categoría III</option>
                                                <option value="IV">Categoría IV</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_tipo_contrato" class="form-label">
                                                Tipo de Contrato <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo_contrato" name="tipo_contrato" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="NOMBRADO">Nombrado</option>
                                                <option value="CONTRATADO">Contratado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_nivel_magisterial" class="form-label">Nivel Magisterial</label>
                                            <select class="form-select" id="edit_nivel_magisterial" name="nivel_magisterial">
                                                <option value="">Seleccionar nivel</option>
                                                <option value="I">Nivel I</option>
                                                <option value="II">Nivel II</option>
                                                <option value="III">Nivel III</option>
                                                <option value="IV">Nivel IV</option>
                                                <option value="V">Nivel V</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_fecha_ingreso" class="form-label">
                                                Fecha de Ingreso <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_ingreso" name="fecha_ingreso" required>
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
                                    <div class="row" id="edit_areas_especialidad_container">
                                        <?php foreach ($areas_curriculares as $area): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="edit_area_<?= $area['id'] ?>" name="areas_especialidad[]" value="<?= $area['id'] ?>">
                                                    <label class="form-check-label" for="edit_area_<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="form-text">Seleccione entre 1 y 5 especialidades</div>
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
                                                <img id="preview-avatar-edit" 
                                                     src="../assets/images/profile/user-default.jpg" 
                                                     alt="Vista previa" 
                                                     class="rounded-circle" 
                                                     width="80" height="80">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <input type="file" class="form-control" id="edit_foto" name="foto" 
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
                    <button type="submit" class="btn btn-warning" id="btnActualizarDocente">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Docente
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

<script>
// Variable global para almacenar datos originales
let datosOriginalesDocente = {};

$(document).ready(function() {
    // Configurar fecha máxima y mínima para edición
    const hoy = new Date().toISOString().split('T')[0];
    const fechaMinima = new Date();
    fechaMinima.setFullYear(fechaMinima.getFullYear() - 50);
    const fechaMinimaStr = fechaMinima.toISOString().split('T')[0];
    
    $('#edit_fecha_ingreso').attr('max', hoy);
    $('#edit_fecha_ingreso').attr('min', fechaMinimaStr);

    // Validación de documento según tipo para edición
    $('#edit_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#edit_documento_numero');
        const helpText = $('#edit_doc_help_text');
        
        docInput.removeClass('is-invalid campo-error');
        
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

    // Validación en tiempo real para documento en edición
    $('#edit_documento_numero').on('input', function() {
        const tipo = $('#edit_documento_tipo').val();
        const valor = $(this).val();
        
        if (tipo === 'DNI') {
            $(this).val(valor.replace(/[^0-9]/g, ''));
        } else if (tipo === 'CE') {
            $(this).val(valor.replace(/[^0-9A-Za-z]/g, ''));
        }
    });

    // Validación en tiempo real para teléfono en edición
    $('#edit_telefono').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Validación en tiempo real para nombres y apellidos en edición
    $('#edit_nombres, #edit_apellidos').on('input', function() {
        $(this).val($(this).val().replace(/[^A-Za-zÀ-ÿ\s]/g, ''));
    });

    // Preview de imagen para edición con validaciones
    $('#edit_foto').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validar tamaño
            if (file.size > 2 * 1024 * 1024) {
                mostrarErrorValidacionEdit('La imagen no debe superar los 2MB', '#edit_foto');
                $(this).val('');
                return;
            }

            // Validar formato
            const formatosPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
            if (!formatosPermitidos.includes(file.type)) {
                mostrarErrorValidacionEdit('Solo se permiten archivos JPG, PNG o GIF', '#edit_foto');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Validar dimensiones mínimas
                    if (this.width < 150 || this.height < 150) {
                        mostrarErrorValidacionEdit('La imagen debe tener al menos 150x150 píxeles', '#edit_foto');
                        $('#edit_foto').val('');
                        return;
                    }
                    $('#preview-avatar-edit').attr('src', e.target.result);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Envío del formulario de edición con validaciones completas
    $('#formEditarDocente').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEdicionCompleto()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarDocente').prop('disabled', true);

        $.ajax({
            url: 'modales/docentes/procesar_docentes.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarDocente').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Docente Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarDocente').modal('hide');
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
                $('#btnActualizarDocente').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal de edición
    $('#modalEditarDocente').on('hidden.bs.modal', function() {
        limpiarFormularioEdicion();
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA PARA EDICIÓN CON 30 VALIDACIONES
function validarFormularioEdicionCompleto() {
    let isValid = true;
    let erroresEncontrados = [];
    
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar ID del docente
    const docenteId = $('#edit_docente_id').val();
    if (!docenteId || isNaN(docenteId)) {
        erroresEncontrados.push('ID de docente inválido');
        isValid = false;
    }
    
    // 2. Validar nombres (obligatorio, solo letras y espacios, 2-50 caracteres)
    const nombres = $('#edit_nombres').val().trim();
    if (!nombres) {
        marcarCampoErrorEdit('#edit_nombres', 'Los nombres son obligatorios');
        erroresEncontrados.push('Nombres requeridos');
        isValid = false;
    } else if (nombres.length < 2 || nombres.length > 50) {
        marcarCampoErrorEdit('#edit_nombres', 'Los nombres deben tener entre 2 y 50 caracteres');
        erroresEncontrados.push('Nombres: longitud incorrecta (2-50 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(nombres)) {
        marcarCampoErrorEdit('#edit_nombres', 'Los nombres solo pueden contener letras y espacios');
        erroresEncontrados.push('Nombres: solo letras y espacios permitidos');
        isValid = false;
    }
    
    // 3. Validar apellidos (obligatorio, solo letras y espacios, 2-50 caracteres)
    const apellidos = $('#edit_apellidos').val().trim();
    if (!apellidos) {
        marcarCampoErrorEdit('#edit_apellidos', 'Los apellidos son obligatorios');
        erroresEncontrados.push('Apellidos requeridos');
        isValid = false;
    } else if (apellidos.length < 2 || apellidos.length > 50) {
        marcarCampoErrorEdit('#edit_apellidos', 'Los apellidos deben tener entre 2 y 50 caracteres');
        erroresEncontrados.push('Apellidos: longitud incorrecta (2-50 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(apellidos)) {
        marcarCampoErrorEdit('#edit_apellidos', 'Los apellidos solo pueden contener letras y espacios');
        erroresEncontrados.push('Apellidos: solo letras y espacios permitidos');
        isValid = false;
    }
    
    // 4. Validar tipo de documento
    const tipoDoc = $('#edit_documento_tipo').val();
    if (!tipoDoc) {
        marcarCampoErrorEdit('#edit_documento_tipo', 'Debe seleccionar un tipo de documento');
        erroresEncontrados.push('Tipo de documento requerido');
        isValid = false;
    }
    
    // 5-7. Validar número de documento según tipo
    const numeroDoc = $('#edit_documento_numero').val().trim();
    if (!numeroDoc) {
        marcarCampoErrorEdit('#edit_documento_numero', 'El número de documento es obligatorio');
        erroresEncontrados.push('Número de documento requerido');
        isValid = false;
    } else if (tipoDoc === 'DNI') {
        if (numeroDoc.length !== 8 || !/^[0-9]{8}$/.test(numeroDoc)) {
            marcarCampoErrorEdit('#edit_documento_numero', 'El DNI debe tener exactamente 8 dígitos numéricos');
            erroresEncontrados.push('DNI: debe tener exactamente 8 dígitos');
            isValid = false;
        }
    } else if (tipoDoc === 'CE') {
        if (numeroDoc.length !== 12 || !/^[0-9A-Za-z]{12}$/.test(numeroDoc)) {
            marcarCampoErrorEdit('#edit_documento_numero', 'El Carnet de Extranjería debe tener exactamente 12 caracteres alfanuméricos');
            erroresEncontrados.push('CE: debe tener exactamente 12 caracteres');
            isValid = false;
        }
    } else if (tipoDoc === 'PASAPORTE') {
        if (numeroDoc.length < 6 || numeroDoc.length > 12) {
            marcarCampoErrorEdit('#edit_documento_numero', 'El Pasaporte debe tener entre 6 y 12 caracteres');
            erroresEncontrados.push('Pasaporte: longitud incorrecta (6-12 caracteres)');
            isValid = false;
        }
    }
    
    // 8. Validar email (obligatorio, formato válido)
    const email = $('#edit_email').val().trim();
    if (!email) {
        marcarCampoErrorEdit('#edit_email', 'El email es obligatorio');
        erroresEncontrados.push('Email requerido');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        marcarCampoErrorEdit('#edit_email', 'Ingrese un email válido');
        erroresEncontrados.push('Email: formato inválido');
        isValid = false;
    } else if (email.length > 100) {
        marcarCampoErrorEdit('#edit_email', 'El email no puede superar los 100 caracteres');
        erroresEncontrados.push('Email: muy largo (máximo 100 caracteres)');
        isValid = false;
    }
    
    // 9. Validar teléfono (obligatorio, 9 dígitos)
    const telefono = $('#edit_telefono').val().trim();
    if (!telefono) {
        marcarCampoErrorEdit('#edit_telefono', 'El teléfono es obligatorio');
        erroresEncontrados.push('Teléfono requerido');
        isValid = false;
    } else if (!/^[0-9]{9}$/.test(telefono)) {
        marcarCampoErrorEdit('#edit_telefono', 'El teléfono debe tener exactamente 9 dígitos');
        erroresEncontrados.push('Teléfono: debe tener exactamente 9 dígitos');
        isValid = false;
    } else if (!telefono.startsWith('9')) {
        marcarCampoErrorEdit('#edit_telefono', 'Los teléfonos móviles deben empezar con 9');
        erroresEncontrados.push('Teléfono: debe empezar con 9');
        isValid = false;
    }
    
    // 10. Validar dirección (obligatorio, 10-200 caracteres)
    const direccion = $('#edit_direccion').val().trim();
    if (!direccion) {
        marcarCampoErrorEdit('#edit_direccion', 'La dirección es obligatoria');
        erroresEncontrados.push('Dirección requerida');
        isValid = false;
    } else if (direccion.length < 10 || direccion.length > 200) {
        marcarCampoErrorEdit('#edit_direccion', 'La dirección debe tener entre 10 y 200 caracteres');
        erroresEncontrados.push('Dirección: longitud incorrecta (10-200 caracteres)');
        isValid = false;
    }
    
    // 11. Validar grado académico
    const grado = $('#edit_grado_academico').val();
    if (!grado) {
        marcarCampoErrorEdit('#edit_grado_academico', 'Debe seleccionar un grado académico');
        erroresEncontrados.push('Grado académico requerido');
        isValid = false;
    }
    
    // 12. Validar universidad (obligatorio, 5-100 caracteres)
    const universidad = $('#edit_universidad').val().trim();
    if (!universidad) {
        marcarCampoErrorEdit('#edit_universidad', 'La universidad es obligatoria');
        erroresEncontrados.push('Universidad requerida');
        isValid = false;
    } else if (universidad.length < 5 || universidad.length > 100) {
        marcarCampoErrorEdit('#edit_universidad', 'La universidad debe tener entre 5 y 100 caracteres');
        erroresEncontrados.push('Universidad: longitud incorrecta (5-100 caracteres)');
        isValid = false;
    } else if (!/^[A-Za-zÀ-ÿ\s\-\.]+$/.test(universidad)) {
        marcarCampoErrorEdit('#edit_universidad', 'La universidad solo puede contener letras, espacios, guiones y puntos');
        erroresEncontrados.push('Universidad: caracteres inválidos');
        isValid = false;
    }
    
    // 13. Validar especialidad (obligatorio)
    const especialidad = $('#edit_especialidad').val().trim();
    if (!especialidad) {
        marcarCampoErrorEdit('#edit_especialidad', 'La especialidad es obligatoria');
        erroresEncontrados.push('Especialidad requerida');
        isValid = false;
    } else if (especialidad.length < 3 || especialidad.length > 100) {
        marcarCampoErrorEdit('#edit_especialidad', 'La especialidad debe tener entre 3 y 100 caracteres');
        erroresEncontrados.push('Especialidad: longitud incorrecta (3-100 caracteres)');
        isValid = false;
    }
    
    // 14. Validar colegiatura (opcional, pero si está debe tener formato correcto)
    const colegiatura = $('#edit_colegiatura').val().trim();
    if (colegiatura && (colegiatura.length < 4 || colegiatura.length > 15 || !/^[A-Za-z0-9]+$/.test(colegiatura))) {
        marcarCampoErrorEdit('#edit_colegiatura', 'La colegiatura debe tener entre 4 y 15 caracteres alfanuméricos');
        erroresEncontrados.push('Colegiatura: formato inválido (4-15 caracteres alfanuméricos)');
        isValid = false;
    }
    
    // 15. Validar categoría
    const categoria = $('#edit_categoria').val();
    if (!categoria) {
        marcarCampoErrorEdit('#edit_categoria', 'Debe seleccionar una categoría');
        erroresEncontrados.push('Categoría requerida');
        isValid = false;
    }
    
    // 16. Validar tipo de contrato
    const contrato = $('#edit_tipo_contrato').val();
    if (!contrato) {
        marcarCampoErrorEdit('#edit_tipo_contrato', 'Debe seleccionar un tipo de contrato');
        erroresEncontrados.push('Tipo de contrato requerido');
        isValid = false;
    }
    
    // 17-18. Validar fecha de ingreso (obligatorio, no futura, no muy antigua)
    const fechaIngreso = $('#edit_fecha_ingreso').val();
    if (!fechaIngreso) {
        marcarCampoErrorEdit('#edit_fecha_ingreso', 'La fecha de ingreso es obligatoria');
        erroresEncontrados.push('Fecha de ingreso requerida');
        isValid = false;
    } else {
        const fecha = new Date(fechaIngreso);
        const hoy = new Date();
        const fechaMinima = new Date();
        fechaMinima.setFullYear(fechaMinima.getFullYear() - 50);
        
        if (fecha > hoy) {
            marcarCampoErrorEdit('#edit_fecha_ingreso', 'La fecha de ingreso no puede ser futura');
            erroresEncontrados.push('Fecha de ingreso: no puede ser futura');
            isValid = false;
        } else if (fecha < fechaMinima) {
            marcarCampoErrorEdit('#edit_fecha_ingreso', 'La fecha de ingreso no puede ser mayor a 50 años');
            erroresEncontrados.push('Fecha de ingreso: muy antigua (máximo 50 años)');
            isValid = false;
        }
    }
    
    // 19. Validar que al menos una especialidad esté seleccionada
    const especialidadesSeleccionadas = $('input[name="areas_especialidad[]"]:checked').length;
    if (especialidadesSeleccionadas === 0) {
        $('#edit_areas_especialidad_container').addClass('campo-error');
        erroresEncontrados.push('Debe seleccionar al menos una especialidad');
        isValid = false;
    }
    
    // 20. Validar código docente (opcional, pero si está debe tener formato correcto)
    const codigoDocente = $('#edit_codigo_docente').val().trim();
    if (codigoDocente && (codigoDocente.length < 3 || codigoDocente.length > 10 || !/^[A-Z0-9]+$/.test(codigoDocente))) {
        marcarCampoErrorEdit('#edit_codigo_docente', 'El código debe tener entre 3 y 10 caracteres alfanuméricos en mayúsculas');
        erroresEncontrados.push('Código docente: formato inválido (3-10 chars mayúsculas)');
        isValid = false;
    }
    
    // 21. Validar que nombres y apellidos no sean iguales
    if (nombres && apellidos && nombres.toLowerCase() === apellidos.toLowerCase()) {
        marcarCampoErrorEdit('#edit_apellidos', 'Los apellidos no pueden ser iguales a los nombres');
        erroresEncontrados.push('Nombres y apellidos no pueden ser iguales');
        isValid = false;
    }
    
    // 22. Validar que el email no tenga dominios temporales
    const dominiosProhibidos = ['temp-mail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
    if (email) {
        const dominio = email.split('@')[1];
        if (dominiosProhibidos.includes(dominio)) {
            marcarCampoErrorEdit('#edit_email', 'No se permiten emails temporales');
            erroresEncontrados.push('Email: dominio temporal no permitido');
            isValid = false;
        }
    }
    
    // 23. Validar que no haya más de 5 especialidades seleccionadas
    if (especialidadesSeleccionadas > 5) {
        $('#edit_areas_especialidad_container').addClass('campo-error');
        erroresEncontrados.push('No puede seleccionar más de 5 especialidades');
        isValid = false;
    }
    
    // 24. Validar coherencia entre grado académico y universidad
    if (grado === 'Doctor en Educación' && universidad && universidad.length < 10) {
        marcarCampoErrorEdit('#edit_universidad', 'Para grado de Doctor, especifique universidad completa');
        erroresEncontrados.push('Universidad: especificar completa para Doctor');
        isValid = false;
    }
    
    // 25. Validar que el nombre no contenga números
    if (nombres && /\d/.test(nombres)) {
        marcarCampoErrorEdit('#edit_nombres', 'Los nombres no pueden contener números');
        erroresEncontrados.push('Nombres: no pueden contener números');
        isValid = false;
    }
    
    // 26. Validar que los apellidos no contengan números
    if (apellidos && /\d/.test(apellidos)) {
        marcarCampoErrorEdit('#edit_apellidos', 'Los apellidos no pueden contener números');
        erroresEncontrados.push('Apellidos: no pueden contener números');
        isValid = false;
    }
    
    // 27. Validar longitud mínima de nombres por separado
    if (nombres) {
        const nombresArray = nombres.split(' ').filter(n => n.length > 0);
        if (nombresArray.length === 0 || nombresArray.some(n => n.length < 2)) {
            marcarCampoErrorEdit('#edit_nombres', 'Cada nombre debe tener al menos 2 caracteres');
            erroresEncontrados.push('Nombres: cada uno debe tener mínimo 2 caracteres');
            isValid = false;
        }
    }
    
    // 28. Validar longitud mínima de apellidos por separado
    if (apellidos) {
        const apellidosArray = apellidos.split(' ').filter(a => a.length > 0);
        if (apellidosArray.length === 0 || apellidosArray.some(a => a.length < 2)) {
            marcarCampoErrorEdit('#edit_apellidos', 'Cada apellido debe tener al menos 2 caracteres');
            erroresEncontrados.push('Apellidos: cada uno debe tener mínimo 2 caracteres');
            isValid = false;
        }
    }
    
    // 29. Validar que la dirección contenga información útil
    if (direccion && direccion.length >= 10) {
        if (!/\d/.test(direccion)) {
            marcarCampoErrorEdit('#edit_direccion', 'La dirección debe incluir al menos un número');
            erroresEncontrados.push('Dirección: debe incluir numeración');
            isValid = false;
        }
    }
    
    // 30. Validar que se hayan hecho cambios (opcional pero recomendado)
    if (hayDatosOriginales() && !sePedenCambios()) {
        erroresEncontrados.push('No se detectaron cambios en los datos');
        Swal.fire({
            title: 'Sin Cambios Detectados',
            text: 'No ha realizado ningún cambio en los datos del docente.',
            icon: 'info',
            confirmButtonColor: '#17a2b8'
        });
        isValid = false;
    }
    
    // Mostrar errores si los hay
    if (!isValid && erroresEncontrados.length > 0) {
        const mensajeError = `❌ NO SE PUEDE ACTUALIZAR EL DOCENTE\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario con Errores',
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

// Función para marcar campo con error en edición
function marcarCampoErrorEdit(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

// Función para mostrar error específico de validación en edición
function mostrarErrorValidacionEdit(mensaje, selector) {
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

// Función para verificar si hay datos originales guardados
function hayDatosOriginales() {
    return Object.keys(datosOriginalesDocente).length > 0;
}

// Función para verificar si se realizaron cambios
function sePedenCambios() {
    if (!hayDatosOriginales()) return true;
    
    const datosActuales = {
        nombres: $('#edit_nombres').val().trim(),
        apellidos: $('#edit_apellidos').val().trim(),
        documento_tipo: $('#edit_documento_tipo').val(),
        documento_numero: $('#edit_documento_numero').val().trim(),
        email: $('#edit_email').val().trim(),
        telefono: $('#edit_telefono').val().trim(),
        direccion: $('#edit_direccion').val().trim(),
        grado_academico: $('#edit_grado_academico').val(),
        universidad: $('#edit_universidad').val().trim(),
        especialidad: $('#edit_especialidad').val().trim(),
        categoria: $('#edit_categoria').val(),
        tipo_contrato: $('#edit_tipo_contrato').val(),
        fecha_ingreso: $('#edit_fecha_ingreso').val()
    };
    
    // Comparar cada campo
    for (let campo in datosActuales) {
        if (datosActuales[campo] !== (datosOriginalesDocente[campo] || '')) {
            return true;
        }
    }
    
    return false;
}

function limpiarFormularioEdicion() {
    $('#formEditarDocente')[0].reset();
    $('#preview-avatar-edit').attr('src', '../assets/images/profile/user-default.jpg');
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#edit_doc_help_text').text('Validación según tipo de documento');
    datosOriginalesDocente = {};
}

// Función original de carga de datos con almacenamiento de originales
function cargarDatosEdicionDocente(docente) {
    // Guardar datos originales para comparación
    datosOriginalesDocente = {
        nombres: docente.nombres || '',
        apellidos: docente.apellidos || '',
        documento_tipo: docente.documento_tipo || 'DNI',
        documento_numero: docente.documento_numero || '',
        email: (docente.datos_personales && docente.datos_personales.email) || '',
        telefono: (docente.datos_personales && docente.datos_personales.telefono) || '',
        direccion: (docente.datos_personales && docente.datos_personales.direccion) || '',
        grado_academico: (docente.datos_profesionales && docente.datos_profesionales.grado_academico) || '',
        universidad: (docente.datos_profesionales && docente.datos_profesionales.universidad) || '',
        especialidad: (docente.datos_profesionales && docente.datos_profesionales.especialidad) || '',
        categoria: (docente.datos_laborales && docente.datos_laborales.categoria) || '',
        tipo_contrato: (docente.datos_laborales && docente.datos_laborales.tipo_contrato) || '',
        fecha_ingreso: (docente.datos_laborales && docente.datos_laborales.fecha_ingreso) || ''
    };
    
    // Cargar datos básicos
    $('#edit_docente_id').val(docente.id);
    $('#edit_codigo_docente').val(docente.codigo_docente);
    $('#edit_nombres').val(docente.nombres);
    $('#edit_apellidos').val(docente.apellidos);
    $('#edit_documento_tipo').val(docente.documento_tipo);
    $('#edit_documento_numero').val(docente.documento_numero);
    
    // Datos personales
    const personales = docente.datos_personales || {};
    $('#edit_email').val(personales.email || '');
    $('#edit_telefono').val(personales.telefono || '');
    $('#edit_direccion').val(personales.direccion || '');
    
    // Datos profesionales
    const profesionales = docente.datos_profesionales || {};
    $('#edit_grado_academico').val(profesionales.grado_academico || '');
    $('#edit_universidad').val(profesionales.universidad || '');
    $('#edit_especialidad').val(profesionales.especialidad || '');
    $('#edit_colegiatura').val(profesionales.colegiatura || '');
    
    // Datos laborales
    const laborales = docente.datos_laborales || {};
    $('#edit_categoria').val(laborales.categoria || '');
    $('#edit_tipo_contrato').val(laborales.tipo_contrato || '');
    $('#edit_nivel_magisterial').val(laborales.nivel_magisterial || '');
    $('#edit_fecha_ingreso').val(laborales.fecha_ingreso || '');
    
    // Especialidades
    $('#edit_areas_especialidad_container input[type="checkbox"]').prop('checked', false);
    if (docente.areas_especialidad && Array.isArray(docente.areas_especialidad)) {
        docente.areas_especialidad.forEach(areaId => {
            $(`#edit_area_${areaId}`).prop('checked', true);
        });
    }
    
    // Foto
    if (docente.foto_url) {
        $('#preview-avatar-edit').attr('src', docente.foto_url);
    }
    
    // Configurar validación según tipo de documento actual
    $('#edit_documento_tipo').trigger('change');
}

// Funciones auxiliares (deben existir en tu sistema)
function mostrarCarga() {
    $('#btnActualizarDocente').html('<i class="spinner-border spinner-border-sm me-2"></i>Actualizando...');
}

function ocultarCarga() {
    $('#btnActualizarDocente').html('<i class="ti ti-device-floppy me-2"></i>Actualizar Docente');
}

function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
}
</script>