<?php
// modales/roles/modal_agregar.php
?>

<!-- Modal Agregar Rol -->
<div class="modal fade" id="modalAgregarRol" tabindex="-1" aria-labelledby="modalAgregarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarRolLabel">
                    <i class="ti ti-shield-plus me-2"></i>
                    Nuevo Rol del Sistema
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarRol" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica del Rol -->
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
                                        <div class="col-md-6 mb-3">
                                            <label for="add_nombre" class="form-label">
                                                Nombre del Rol <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre" 
                                                   placeholder="Ejemplo: Coordinador Académico" required>
                                            <div class="form-text">Nombre descriptivo y único del rol</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_nivel_acceso" class="form-label">
                                                Nivel de Acceso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_nivel_acceso" name="nivel_acceso" required>
                                                <option value="">Seleccionar nivel</option>
                                                <option value="10" class="bg-danger text-white">Nivel 10 - Super Administrador</option>
                                                <option value="9" class="bg-warning">Nivel 9 - Director</option>
                                                <option value="8" class="bg-info text-white">Nivel 8 - Subdirector</option>
                                                <option value="7" class="bg-success text-white">Nivel 7 - Coordinador</option>
                                                <option value="6" class="bg-primary text-white">Nivel 6 - Docente</option>
                                                <option value="5" class="bg-secondary text-white">Nivel 5 - Tutor</option>
                                                <option value="4" class="bg-dark text-white">Nivel 4 - Auxiliar</option>
                                                <option value="3">Nivel 3 - Apoderado</option>
                                                <option value="2">Nivel 2 - Estudiante</option>
                                                <option value="1">Nivel 1 - Invitado</option>
                                            </select>
                                            <div class="form-text">Mayor número = mayor nivel de acceso</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                                      rows="2" placeholder="Descripción detallada del rol y sus responsabilidades"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Permisos -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-key me-2"></i>
                                        Permisos del Sistema
                                    </h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="add_todos_permisos" name="todos_permisos">
                                        <label class="form-check-label text-danger fw-bold" for="add_todos_permisos">
                                            TODOS LOS PERMISOS
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body" id="permisos-container-add">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Selecciona los permisos específicos</strong> que tendrá este rol, o marca "TODOS LOS PERMISOS" para acceso completo.
                                    </div>
                                    
                                    <!-- Permisos por Categoría -->
                                    <?php foreach ($permisos_sistema as $categoria_key => $categoria_data): ?>
                                        <div class="categoria-permisos-add mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input categoria-check" type="checkbox" 
                                                           id="add_cat_<?= $categoria_key ?>" data-categoria="<?= $categoria_key ?>">
                                                    <label class="form-check-label fw-bold text-primary" for="add_cat_<?= $categoria_key ?>">
                                                        <?= htmlspecialchars($categoria_data['nombre']) ?>
                                                    </label>
                                                </div>
                                                <small class="text-muted ms-2">(Seleccionar toda la categoría)</small>
                                            </div>
                                            
                                            <div class="row ms-3">
                                                <?php foreach ($categoria_data['permisos'] as $permiso_key => $permiso_desc): ?>
                                                    <div class="col-md-6 col-lg-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input permiso-individual" 
                                                                   type="checkbox" 
                                                                   name="permisos[]" 
                                                                   value="<?= htmlspecialchars($permiso_key) ?>"
                                                                   id="add_<?= str_replace('.', '_', $permiso_key) ?>"
                                                                   data-categoria="<?= $categoria_key ?>">
                                                            <label class="form-check-label" for="add_<?= str_replace('.', '_', $permiso_key) ?>">
                                                                <small><?= htmlspecialchars($permiso_desc) ?></small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Estado del Rol -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración Adicional
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="add_activo" name="activo" checked>
                                                <label class="form-check-label" for="add_activo">
                                                    <strong>Rol Activo</strong>
                                                </label>
                                                <div class="form-text">El rol estará disponible para asignación inmediata</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="mb-2">Resumen de Selección:</h6>
                                                <div id="resumen-permisos-add">
                                                    <small class="text-muted">Selecciona permisos para ver el resumen</small>
                                                </div>
                                            </div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarRol">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle todos los permisos
    $('#add_todos_permisos').on('change', function() {
        const todosChecked = $(this).is(':checked');
        
        if (todosChecked) {
            $('#permisos-container-add .form-check-input').prop('checked', true);
            $('#permisos-container-add').addClass('opacity-50');
            updateResumenPermisos();
        } else {
            $('#permisos-container-add .form-check-input').prop('checked', false);
            $('#permisos-container-add').removeClass('opacity-50');
            updateResumenPermisos();
        }
    });

    // Toggle categoría completa
    $('.categoria-check').on('change', function() {
        const categoria = $(this).data('categoria');
        const checked = $(this).is(':checked');
        
        $(`.permiso-individual[data-categoria="${categoria}"]`).prop('checked', checked);
        updateResumenPermisos();
    });

    // Toggle permiso individual
    $('.permiso-individual').on('change', function() {
        const categoria = $(this).data('categoria');
        const totalCategoriaPermisos = $(`.permiso-individual[data-categoria="${categoria}"]`).length;
        const checkedCategoriaPermisos = $(`.permiso-individual[data-categoria="${categoria}"]:checked`).length;
        
        // Actualizar checkbox de categoría
        const categoriaCheck = $(`#add_cat_${categoria}`);
        if (checkedCategoriaPermisos === 0) {
            categoriaCheck.prop('indeterminate', false).prop('checked', false);
        } else if (checkedCategoriaPermisos === totalCategoriaPermisos) {
            categoriaCheck.prop('indeterminate', false).prop('checked', true);
        } else {
            categoriaCheck.prop('indeterminate', true);
        }
        
        updateResumenPermisos();
    });

    function updateResumenPermisos() {
        let resumen = '';
        let totalPermisos = 0;
        
        if ($('#add_todos_permisos').is(':checked')) {
            resumen = '<span class="badge bg-danger">TODOS LOS PERMISOS DEL SISTEMA</span>';
        } else {
            Object.keys(permisosSistema).forEach(categoria => {
                const permisosCategoria = $(`.permiso-individual[data-categoria="${categoria}"]:checked`).length;
                if (permisosCategoria > 0) {
                    totalPermisos += permisosCategoria;
                    resumen += `<span class="badge bg-info me-1 mb-1">${permisosSistema[categoria].nombre}: ${permisosCategoria}</span>`;
                }
            });
            
            if (totalPermisos === 0) {
                resumen = '<small class="text-muted">Sin permisos seleccionados</small>';
            } else {
                resumen = `<div class="mb-2"><strong>Total: ${totalPermisos} permisos</strong></div>${resumen}`;
            }
        }
        
        $('#resumen-permisos-add').html(resumen);
    }

    // Envío del formulario
    $('#formAgregarRol').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        if (!validarFormularioAgregarRol()) {
            return false;
        }

        mostrarCarga();
        $('#btnGuardarRol').prop('disabled', true);

        fetch('modales/roles/procesar_roles.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            $('#btnGuardarRol').prop('disabled', false);
            
            if (data.success) {
                Swal.fire({
                    title: '¡Rol Creado!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalAgregarRol').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            ocultarCarga();
            $('#btnGuardarRol').prop('disabled', false);
            mostrarError('Error al procesar la solicitud');
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarRol').on('hidden.bs.modal', function() {
        $('#formAgregarRol')[0].reset();
        $('#permisos-container-add .form-check-input').prop('checked', false);
        $('#permisos-container-add').removeClass('opacity-50');
        $('.categoria-check').prop('indeterminate', false);
        updateResumenPermisos();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioAgregarRol() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    if (!$('#add_nombre').val().trim()) {
        mostrarErrorCampoRol('#add_nombre', 'El nombre del rol es requerido');
        isValid = false;
    }
    
    if (!$('#add_nivel_acceso').val()) {
        mostrarErrorCampoRol('#add_nivel_acceso', 'El nivel de acceso es requerido');
        isValid = false;
    }
    
    // Validar que tenga al menos un permiso seleccionado
    const todosPermisos = $('#add_todos_permisos').is(':checked');
    const permisosIndividuales = $('.permiso-individual:checked').length;
    
    if (!todosPermisos && permisosIndividuales === 0) {
        Swal.fire({
            title: 'Sin Permisos',
            text: 'Debe seleccionar al menos un permiso o marcar "TODOS LOS PERMISOS"',
            icon: 'warning',
            confirmButtonColor: '#fd7e14'
        });
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampoRol(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}
</script>