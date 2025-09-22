<?php
// modales/usuarios/modal_agregar.php
?>
<!-- Modal Agregar Usuario -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalAgregarUsuarioLabel">
                    <i class="ti ti-user-plus me-2"></i>
                    Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarUsuario" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información de Acceso -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-key me-2"></i>
                                        Información de Acceso
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="add_username" class="form-label">
                                                Usuario <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_username" name="username" 
                                                   placeholder="Ejemplo: juan.perez" required>
                                            <div class="form-text">Solo letras, números, puntos y guiones bajos</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="add_email" name="email" 
                                                   placeholder="usuario@example.com" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_password" class="form-label">
                                                Contraseña <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="add_password" name="password" 
                                                       placeholder="Mínimo 8 caracteres" required>
                                                <button class="btn btn-outline-secondary" type="button" id="generatePassword">
                                                    <i class="ti ti-refresh"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">La contraseña debe tener al menos 8 caracteres</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_rol_id" class="form-label">
                                                Rol <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_rol_id" name="rol_id" required>
                                                <option value="">Seleccionar rol</option>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?= $rol['id'] ?>" 
                                                            data-nivel="<?= $rol['nivel_acceso'] ?>">
                                                        <?= htmlspecialchars($rol['nombre']) ?>
                                                        <?php if ($rol['descripcion']): ?>
                                                            - <?= htmlspecialchars($rol['descripcion']) ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                        <div class="col-md-6 mb-3">
                                            <label for="add_nombres" class="form-label">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombres" name="nombres" 
                                                   placeholder="Nombres completos" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_apellidos" class="form-label">
                                                Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_apellidos" name="apellidos" 
                                                   placeholder="Apellidos completos" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_documento_tipo" class="form-label">
                                                Tipo Documento <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_documento_tipo" name="documento_tipo" required>
                                                <option value="DNI" selected>DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_documento_numero" class="form-label">
                                                Número Documento <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_documento_numero" 
                                                   name="documento_numero" placeholder="12345678" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_telefono" class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" id="add_telefono" name="telefono" 
                                                   placeholder="999-123456">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_direccion" class="form-label">Dirección</label>
                                            <textarea class="form-control" id="add_direccion" name="direccion" 
                                                      rows="2" placeholder="Dirección completa"></textarea>
                                        </div>
                                    </div>
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
                                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Generar contraseña automática
    $('#generatePassword').on('click', function() {
        const password = generarPassword();
        $('#add_password').val(password);
        
        // Mostrar contraseña generada temporalmente
        $('#add_password').attr('type', 'text');
        $('#togglePassword i').removeClass('ti-eye').addClass('ti-eye-off');
        
        // Mostrar alerta con la contraseña
        Swal.fire({
            title: 'Contraseña Generada',
            html: `<div class="password-generated">${password}</div>
                   <small class="text-muted">Asegúrate de guardar esta contraseña</small>`,
            icon: 'success',
            confirmButtonColor: '#0d6efd'
        });
    });

    // Toggle mostrar/ocultar contraseña
    $('#togglePassword').on('click', function() {
        const passwordField = $('#add_password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('ti-eye').addClass('ti-eye-off');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('ti-eye-off').addClass('ti-eye');
        }
    });

    // Preview de imagen
    $('#add_foto').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validar tamaño (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: 'Archivo muy grande',
                    text: 'La imagen no debe superar los 2MB',
                    icon: 'error'
                });
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-avatar-add').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Validación de documento según tipo
    $('#add_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#add_documento_numero');
        
        if (tipo === 'DNI') {
            docInput.attr('maxlength', '8').attr('pattern', '[0-9]{8}');
            docInput.attr('placeholder', '12345678');
        } else if (tipo === 'CE') {
            docInput.attr('maxlength', '12').attr('pattern', '[0-9A-Za-z]{9,12}');
            docInput.attr('placeholder', 'ABC123456789');
        } else if (tipo === 'PASAPORTE') {
            docInput.attr('maxlength', '15').removeAttr('pattern');
            docInput.attr('placeholder', 'ABC123456');
        }
    });

    // Envío del formulario
    $('#formAgregarUsuario').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Validaciones adicionales
        if (!validarFormularioAgregar()) {
            return false;
        }

        mostrarCarga();
        $('#btnGuardarUsuario').prop('disabled', true);

        $.ajax({
            url: 'modales/usuarios/procesar_usuarios.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarUsuario').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Usuario Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarUsuario').modal('hide');
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
                $('#btnGuardarUsuario').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarUsuario').on('hidden.bs.modal', function() {
        $('#formAgregarUsuario')[0].reset();
        $('#preview-avatar-add').attr('src', '../assets/images/profile/user-default.jpg');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioAgregar() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar username
    const username = $('#add_username').val().trim();
    if (!/^[a-zA-Z0-9._-]+$/.test(username)) {
        mostrarErrorCampo('#add_username', 'El usuario solo puede contener letras, números, puntos y guiones');
        isValid = false;
    }
    
    // Validar email
    const email = $('#add_email').val().trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        mostrarErrorCampo('#add_email', 'Ingresa un email válido');
        isValid = false;
    }
    
    // Validar contraseña
    const password = $('#add_password').val();
    if (password.length < 8) {
        mostrarErrorCampo('#add_password', 'La contraseña debe tener al menos 8 caracteres');
        isValid = false;
    }
    
    // Validar documento
    const docTipo = $('#add_documento_tipo').val();
    const docNumero = $('#add_documento_numero').val().trim();
    
    if (docTipo === 'DNI' && !/^[0-9]{8}$/.test(docNumero)) {
        mostrarErrorCampo('#add_documento_numero', 'El DNI debe tener 8 dígitos');
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampo(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}
</script>