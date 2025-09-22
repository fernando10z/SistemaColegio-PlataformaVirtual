<?php
// modales/usuarios/modal_editar.php
?>
<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <i class="ti ti-user-edit me-2"></i>
                    Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarUsuario" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_user_id" name="user_id">
                
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
                                            <label for="edit_username" class="form-label">
                                                Usuario <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_username" name="username" 
                                                   placeholder="Ejemplo: juan.perez" required>
                                            <div class="form-text">Solo letras, números, puntos y guiones bajos</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_email" class="form-label">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="edit_email" name="email" 
                                                   placeholder="usuario@example.com" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_rol_id" class="form-label">
                                                Rol <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_rol_id" name="rol_id" required>
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
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_activo" class="form-label">Estado</label>
                                            <select class="form-select" id="edit_activo" name="activo">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Cambio de contraseña -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="cambiarPassword" name="cambiar_password">
                                                <label class="form-check-label" for="cambiarPassword">
                                                    <strong>Cambiar contraseña</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" id="password-section" style="display: none;">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_password" class="form-label">
                                                Nueva Contraseña <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="edit_password" 
                                                       name="password" placeholder="Mínimo 8 caracteres">
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        id="generatePasswordEdit">
                                                    <i class="ti ti-refresh"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        id="togglePasswordEdit">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="debe_cambiar_password" name="debe_cambiar_password" value="1">
                                                <label class="form-check-label" for="debe_cambiar_password">
                                                    Forzar cambio en próximo acceso
                                                </label>
                                            </div>
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
                                            <label for="edit_nombres" class="form-label">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombres" name="nombres" 
                                                   placeholder="Nombres completos" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_apellidos" class="form-label">
                                                Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_apellidos" name="apellidos" 
                                                   placeholder="Apellidos completos" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_documento_tipo" class="form-label">
                                                Tipo Documento <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_documento_tipo" 
                                                    name="documento_tipo" required>
                                                <option value="DNI">DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                                <option value="PASAPORTE">Pasaporte</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_documento_numero" class="form-label">
                                                Número Documento <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_documento_numero" 
                                                   name="documento_numero" placeholder="12345678" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_telefono" class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" id="edit_telefono" name="telefono" 
                                                   placeholder="999-123456">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="edit_direccion" class="form-label">Dirección</label>
                                            <textarea class="form-control" id="edit_direccion" name="direccion" 
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
                                        Foto de Perfil
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
                                            <div class="form-text">
                                                Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.
                                                <br>
                                                <small class="text-muted">Deja vacío para mantener la foto actual</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información del Sistema
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <strong>Código:</strong> 
                                                <span id="info_codigo_usuario">-</span>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <strong>Fecha Creación:</strong> 
                                                <span id="info_fecha_creacion">-</span>
                                            </small>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <small class="text-muted">
                                                <strong>Último Acceso:</strong> 
                                                <span id="info_ultimo_acceso">-</span>
                                            </small>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <small class="text-muted">
                                                <strong>Debe cambiar password:</strong> 
                                                <span id="info_debe_cambiar" class="badge bg-secondary">-</span>
                                            </small>
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
                    <button type="submit" class="btn btn-warning" id="btnActualizarUsuario">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle sección de cambio de contraseña
    $('#cambiarPassword').on('change', function() {
        if ($(this).is(':checked')) {
            $('#password-section').slideDown();
            $('#edit_password').attr('required', true);
        } else {
            $('#password-section').slideUp();
            $('#edit_password').attr('required', false).val('');
            $('#debe_cambiar_password').prop('checked', false);
        }
    });

    // Generar contraseña automática para edición
    $('#generatePasswordEdit').on('click', function() {
        const password = generarPassword();
        $('#edit_password').val(password);
        
        // Mostrar contraseña generada temporalmente
        $('#edit_password').attr('type', 'text');
        $('#togglePasswordEdit i').removeClass('ti-eye').addClass('ti-eye-off');
        
        // Mostrar alerta con la contraseña
        Swal.fire({
            title: 'Contraseña Generada',
            html: `<div class="password-generated">${password}</div>
                   <small class="text-muted">Asegúrate de guardar esta contraseña</small>`,
            icon: 'success',
            confirmButtonColor: '#0d6efd'
        });
    });

    // Toggle mostrar/ocultar contraseña en edición
    $('#togglePasswordEdit').on('click', function() {
        const passwordField = $('#edit_password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('ti-eye').addClass('ti-eye-off');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('ti-eye-off').addClass('ti-eye');
        }
    });

    // Preview de imagen en edición
    $('#edit_foto').on('change', function() {
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
                $('#preview-avatar-edit').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Validación de documento según tipo en edición
    $('#edit_documento_tipo').on('change', function() {
        const tipo = $(this).val();
        const docInput = $('#edit_documento_numero');
        
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

    // Envío del formulario de edición
    $('#formEditarUsuario').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        // Validaciones adicionales
        if (!validarFormularioEditar()) {
            return false;
        }

        mostrarCarga();
        $('#btnActualizarUsuario').prop('disabled', true);

        $.ajax({
            url: 'modales/usuarios/procesar_usuarios.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarUsuario').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Usuario Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarUsuario').modal('hide');
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
                $('#btnActualizarUsuario').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarUsuario').on('hidden.bs.modal', function() {
        $('#formEditarUsuario')[0].reset();
        $('#preview-avatar-edit').attr('src', '../assets/images/profile/user-default.jpg');
        $('#password-section').hide();
        $('#cambiarPassword').prop('checked', false);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

// Función para cargar datos de edición (llamada desde el archivo principal)
function cargarDatosEdicion(usuario) {
    // Datos básicos
    $('#edit_user_id').val(usuario.id);
    $('#edit_username').val(usuario.username);
    $('#edit_email').val(usuario.email);
    $('#edit_nombres').val(usuario.nombres);
    $('#edit_apellidos').val(usuario.apellidos);
    
    // Documento
    $('#edit_documento_tipo').val(usuario.documento_tipo || 'DNI');
    $('#edit_documento_numero').val(usuario.documento_numero || '');
    
    // Contacto
    $('#edit_telefono').val(usuario.telefono || '');
    $('#edit_direccion').val(usuario.direccion || '');
    
    // Sistema
    $('#edit_rol_id').val(usuario.rol_id);
    $('#edit_activo').val(usuario.activo ? '1' : '0');
    
    // Foto
    if (usuario.foto_url) {
        $('#preview-avatar-edit').attr('src', usuario.foto_url);
    } else {
        $('#preview-avatar-edit').attr('src', '../assets/images/profile/user-default.jpg');
    }
    
    // Información adicional
    $('#info_codigo_usuario').text(usuario.codigo_usuario || 'Sin código');
    $('#info_fecha_creacion').text(
        usuario.fecha_creacion ? 
        new Date(usuario.fecha_creacion).toLocaleDateString('es-PE') : 
        '-'
    );
    $('#info_ultimo_acceso').text(
        usuario.ultimo_acceso ? 
        new Date(usuario.ultimo_acceso).toLocaleString('es-PE') : 
        'Nunca'
    );
    
    // Badge para debe cambiar password
    if (usuario.debe_cambiar_password) {
        $('#info_debe_cambiar')
            .removeClass('bg-secondary bg-success')
            .addClass('bg-warning')
            .text('Sí');
    } else {
        $('#info_debe_cambiar')
            .removeClass('bg-secondary bg-warning')
            .addClass('bg-success')
            .text('No');
    }
}

function validarFormularioEditar() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar username
    const username = $('#edit_username').val().trim();
    if (!/^[a-zA-Z0-9._-]+$/.test(username)) {
        mostrarErrorCampo('#edit_username', 'El usuario solo puede contener letras, números, puntos y guiones');
        isValid = false;
    }
    
    // Validar email
    const email = $('#edit_email').val().trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        mostrarErrorCampo('#edit_email', 'Ingresa un email válido');
        isValid = false;
    }
    
    // Validar contraseña solo si se está cambiando
    if ($('#cambiarPassword').is(':checked')) {
        const password = $('#edit_password').val();
        if (password.length < 8) {
            mostrarErrorCampo('#edit_password', 'La contraseña debe tener al menos 8 caracteres');
            isValid = false;
        }
    }
    
    // Validar documento
    const docTipo = $('#edit_documento_tipo').val();
    const docNumero = $('#edit_documento_numero').val().trim();
    
    if (docTipo === 'DNI' && !/^[0-9]{8}$/.test(docNumero)) {
        mostrarErrorCampo('#edit_documento_numero', 'El DNI debe tener 8 dígitos');
        isValid = false;
    }
    
    return isValid;
}
</script>