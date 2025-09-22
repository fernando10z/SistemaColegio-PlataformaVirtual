<!-- Modal Editar Rol -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarRolLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Rol del Sistema
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarRol" method="POST">
                <input type="hidden" id="edit_rol_id" name="id">
                
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
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Rol <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" 
                                                   placeholder="Ejemplo: Coordinador Académico" required>
                                            <div class="form-text">Nombre descriptivo y único del rol</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_nivel_acceso" class="form-label">
                                                Nivel de Acceso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_nivel_acceso" name="nivel_acceso" required>
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
                                            <label for="edit_descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción detallada del rol y sus responsabilidades"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado del Rol -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración del Rol
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_activo" name="activo">
                                                <label class="form-check-label" for="edit_activo">
                                                    <strong>Rol Activo</strong>
                                                </label>
                                                <div class="form-text">El rol estará disponible para asignación</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-light p-3 rounded">
                                                <h6 class="mb-2">Información:</h6>
                                                <div id="info-usuarios-asignados" class="mb-2">
                                                    <small class="text-muted">Cargando información...</small>
                                                </div>
                                                <div id="info-fecha-creacion">
                                                    <small class="text-muted">Fecha de creación: --</small>
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
                    <button type="submit" class="btn btn-warning" id="btnActualizarRol">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicionRol(rol) {
    // Cargar datos básicos
    $('#edit_rol_id').val(rol.id);
    $('#edit_nombre').val(rol.nombre);
    $('#edit_descripcion').val(rol.descripcion);
    $('#edit_nivel_acceso').val(rol.nivel_acceso);
    $('#edit_activo').prop('checked', rol.activo == 1);
    
    // Mostrar información adicional
    $('#info-usuarios-asignados').html(
        `<strong>Usuarios asignados:</strong> ${rol.total_usuarios || 0} 
         <small class="text-muted">(${rol.usuarios_activos || 0} activos)</small>`
    );
    
    if (rol.fecha_creacion) {
        const fecha = new Date(rol.fecha_creacion).toLocaleDateString('es-PE');
        $('#info-fecha-creacion').html(`<small class="text-muted">Creado: ${fecha}</small>`);
    }
}

// Manejar envío del formulario de edición
$('#formEditarRol').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('accion', 'actualizar');
    
    mostrarCarga();
    
    fetch('modales/roles/procesar_roles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            $('#modalEditarRol').modal('hide');
            mostrarExito(data.message);
            
            // Recargar la tabla después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        console.error('Error:', error);
        mostrarError('Error al procesar la solicitud');
    });
});
</script>