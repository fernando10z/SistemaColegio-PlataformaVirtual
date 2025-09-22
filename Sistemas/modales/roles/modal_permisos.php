<!-- Modal Gestión de Permisos -->
<div class="modal fade" id="modalGestionPermisos" tabindex="-1" aria-labelledby="modalGestionPermisosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalGestionPermisosLabel">
                    <i class="ti ti-key me-2"></i>
                    Gestión de Permisos: <span id="permisos-rol-nombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formGestionPermisos" method="POST">
                <input type="hidden" id="permisos_rol_id" name="id">
                
                <div class="modal-body">
                    <!-- Información del Rol -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-0">
                                            <i class="ti ti-info-circle me-2"></i>
                                            Configurando permisos para: <strong id="permisos-info-nombre"></strong>
                                        </h6>
                                        <small class="text-muted">
                                            Nivel de acceso: <span id="permisos-info-nivel"></span> | 
                                            Estado: <span id="permisos-info-estado"></span> |
                                            Usuarios asignados: <span id="permisos-info-usuarios"></span>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" id="permisos_todos_permisos" name="todos_permisos">
                                            <label class="form-check-label text-danger fw-bold" for="permisos_todos_permisos">
                                                TODOS LOS PERMISOS
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros y Búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Filtrar por categoría</label>
                            <select class="form-select" id="filtro-categoria-permisos">
                                <option value="">Todas las categorías</option>
                                <option value="academico">Académico</option>
                                <option value="administrativo">Administrativo</option>
                                <option value="evaluaciones">Evaluaciones</option>
                                <option value="comunicacion">Comunicación</option>
                                <option value="sistema">Sistema</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Buscar permiso</label>
                            <input type="text" class="form-control" id="buscar-permiso" placeholder="Buscar por nombre de permiso...">
                        </div>
                    </div>

                    <!-- Resumen de Selección -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-2">Resumen de Permisos Seleccionados:</h6>
                                        <div id="resumen-permisos-seleccionados">
                                            <small class="text-muted">No hay permisos seleccionados</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="seleccionarTodosPermisos()">
                                            <i class="ti ti-check-all"></i> Seleccionar Todos
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deseleccionarTodosPermisos()">
                                            <i class="ti ti-square"></i> Deseleccionar Todos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos por Categoría -->
                    <div id="contenedor-permisos">
                        <?php foreach ($permisos_sistema as $categoria_key => $categoria_data): ?>
                            <div class="categoria-permisos-gestion mb-4" data-categoria="<?= $categoria_key ?>">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check me-3">
                                                <input class="form-check-input categoria-check-permisos" type="checkbox" 
                                                       id="perm_cat_<?= $categoria_key ?>" data-categoria="<?= $categoria_key ?>">
                                                <label class="form-check-label fw-bold text-primary" for="perm_cat_<?= $categoria_key ?>">
                                                    <?= htmlspecialchars($categoria_data['nombre']) ?>
                                                </label>
                                            </div>
                                            <span class="badge bg-secondary categoria-contador" data-categoria="<?= $categoria_key ?>">
                                                0/<?= count($categoria_data['permisos']) ?>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse_<?= $categoria_key ?>">
                                            <i class="ti ti-chevron-down"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="collapse show" id="collapse_<?= $categoria_key ?>">
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($categoria_data['permisos'] as $permiso_key => $permiso_desc): ?>
                                                    <div class="col-md-6 col-lg-4 mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input permiso-individual-gestion" 
                                                                   type="checkbox" 
                                                                   name="permisos[]" 
                                                                   value="<?= htmlspecialchars($permiso_key) ?>"
                                                                   id="perm_<?= str_replace('.', '_', $permiso_key) ?>"
                                                                   data-categoria="<?= $categoria_key ?>"
                                                                   data-permiso="<?= htmlspecialchars($permiso_desc) ?>">
                                                            <label class="form-check-label" for="perm_<?= str_replace('.', '_', $permiso_key) ?>">
                                                                <strong><?= htmlspecialchars($permiso_desc) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($permiso_key) ?></small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-outline-info me-2" onclick="previsualizarPermisos()">
                        <i class="ti ti-eye me-2"></i>
                        Vista Previa
                    </button>
                    <button type="submit" class="btn btn-info" id="btnGuardarPermisos">
                        <i class="ti ti-device-floppy me-2"></i>
                        Guardar Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Vista Previa de Permisos -->
<div class="modal fade" id="modalVistaPreviaPermisos" tabindex="-1" aria-labelledby="modalVistaPreviaPermisosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalVistaPreviaPermisosLabel">
                    <i class="ti ti-eye me-2"></i>
                    Vista Previa de Permisos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contenido-vista-previa">
                <!-- Contenido generado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarPermisosRol(rol) {
    // Cargar información básica del rol
    $('#permisos_rol_id').val(rol.id);
    $('#permisos-rol-nombre').text(rol.nombre);
    $('#permisos-info-nombre').text(rol.nombre);
    $('#permisos-info-nivel').text(`Nivel ${rol.nivel_acceso}`);
    $('#permisos-info-estado').text(rol.activo == 1 ? 'Activo' : 'Inactivo');
    $('#permisos-info-usuarios').text(`${rol.total_usuarios} usuarios`);
    
    // Limpiar permisos anteriores
    $('.permiso-individual-gestion').prop('checked', false);
    $('.categoria-check-permisos').prop('checked', false);
    $('#permisos_todos_permisos').prop('checked', false);
    
    // Cargar permisos actuales
    const permisos = rol.permisos || [];
    
    if (permisos.includes('*')) {
        $('#permisos_todos_permisos').prop('checked', true);
        $('.permiso-individual-gestion').prop('disabled', true);
        $('.categoria-check-permisos').prop('disabled', true);
    } else {
        // Marcar permisos individuales
        permisos.forEach(permiso => {
            const permisoId = '#perm_' + permiso.replace(/\./g, '_');
            $(permisoId).prop('checked', true);
        });
        
        // Actualizar contadores y checkboxes de categoría
        actualizarContadoresPermisos();
    }
    
    actualizarResumenPermisos();
}

// Manejar checkbox "Todos los permisos"
$('#permisos_todos_permisos').on('change', function() {
    const todosMarcados = $(this).prop('checked');
    
    $('.permiso-individual-gestion').prop('disabled', todosMarcados);
    $('.categoria-check-permisos').prop('disabled', todosMarcados);
    
    if (todosMarcados) {
        $('.permiso-individual-gestion').prop('checked', false);
        $('.categoria-check-permisos').prop('checked', false);
    }
    
    actualizarContadoresPermisos();
    actualizarResumenPermisos();
});

// Manejar checkboxes de categoría
$(document).on('change', '.categoria-check-permisos', function() {
    const categoria = $(this).data('categoria');
    const marcado = $(this).prop('checked');
    
    $(`.permiso-individual-gestion[data-categoria="${categoria}"]`).prop('checked', marcado);
    
    actualizarContadoresPermisos();
    actualizarResumenPermisos();
});

// Manejar checkboxes individuales
$(document).on('change', '.permiso-individual-gestion', function() {
    const categoria = $(this).data('categoria');
    const totalCategoria = $(`.permiso-individual-gestion[data-categoria="${categoria}"]`).length;
    const marcadosCategoria = $(`.permiso-individual-gestion[data-categoria="${categoria}"]:checked`).length;
    
    // Actualizar checkbox de categoría
    const categoriaCheck = $(`.categoria-check-permisos[data-categoria="${categoria}"]`);
    
    if (marcadosCategoria === 0) {
        categoriaCheck.prop('checked', false).prop('indeterminate', false);
    } else if (marcadosCategoria === totalCategoria) {
        categoriaCheck.prop('checked', true).prop('indeterminate', false);
    } else {
        categoriaCheck.prop('checked', false).prop('indeterminate', true);
    }
    
    actualizarContadoresPermisos();
    actualizarResumenPermisos();
});

// Filtros de búsqueda
$('#filtro-categoria-permisos').on('change', function() {
    const categoriaSeleccionada = $(this).val();
    
    if (categoriaSeleccionada === '') {
        $('.categoria-permisos-gestion').show();
    } else {
        $('.categoria-permisos-gestion').hide();
        $(`.categoria-permisos-gestion[data-categoria="${categoriaSeleccionada}"]`).show();
    }
});

$('#buscar-permiso').on('input', function() {
    const busqueda = $(this).val().toLowerCase();
    
    $('.permiso-individual-gestion').each(function() {
        const permiso = $(this).data('permiso').toLowerCase();
        const valor = $(this).val().toLowerCase();
        const contenedor = $(this).closest('.form-check');
        
        if (permiso.includes(busqueda) || valor.includes(busqueda)) {
            contenedor.show();
        } else {
            contenedor.hide();
        }
    });
});

function actualizarContadoresPermisos() {
    $('.categoria-contador').each(function() {
        const categoria = $(this).data('categoria');
        const totalCategoria = $(`.permiso-individual-gestion[data-categoria="${categoria}"]`).length;
        const marcadosCategoria = $(`.permiso-individual-gestion[data-categoria="${categoria}"]:checked`).length;
        
        $(this).text(`${marcadosCategoria}/${totalCategoria}`);
        
        // Cambiar color del badge según el progreso
        $(this).removeClass('bg-secondary bg-success bg-warning');
        if (marcadosCategoria === 0) {
            $(this).addClass('bg-secondary');
        } else if (marcadosCategoria === totalCategoria) {
            $(this).addClass('bg-success');
        } else {
            $(this).addClass('bg-warning');
        }
    });
}

function actualizarResumenPermisos() {
    const todosLosPermisos = $('#permisos_todos_permisos').prop('checked');
    
    if (todosLosPermisos) {
        $('#resumen-permisos-seleccionados').html(
            '<span class="badge bg-danger fs-6">TODOS LOS PERMISOS DEL SISTEMA</span>'
        );
        return;
    }
    
    const permisosSeleccionados = $('.permiso-individual-gestion:checked');
    
    if (permisosSeleccionados.length === 0) {
        $('#resumen-permisos-seleccionados').html('<small class="text-muted">No hay permisos seleccionados</small>');
        return;
    }
    
    let resumen = `<strong>${permisosSeleccionados.length} permisos seleccionados:</strong><br>`;
    
    // Agrupar por categorías
    const categorias = {};
    permisosSeleccionados.each(function() {
        const categoria = $(this).data('categoria');
        const permiso = $(this).data('permiso');
        
        if (!categorias[categoria]) {
            categorias[categoria] = [];
        }
        categorias[categoria].push(permiso);
    });
    
    Object.keys(categorias).forEach(categoria => {
        const nombreCategoria = permisosSistema[categoria].nombre;
        resumen += `<small class="text-muted">${nombreCategoria}: ${categorias[categoria].length} permisos</small><br>`;
    });
    
    $('#resumen-permisos-seleccionados').html(resumen);
}

function seleccionarTodosPermisos() {
    if (!$('#permisos_todos_permisos').prop('checked')) {
        $('.permiso-individual-gestion:visible').prop('checked', true);
        $('.categoria-check-permisos').prop('checked', true);
        actualizarContadoresPermisos();
        actualizarResumenPermisos();
    }
}

function deseleccionarTodosPermisos() {
    $('.permiso-individual-gestion').prop('checked', false);
    $('.categoria-check-permisos').prop('checked', false).prop('indeterminate', false);
    $('#permisos_todos_permisos').prop('checked', false);
    
    $('.permiso-individual-gestion').prop('disabled', false);
    $('.categoria-check-permisos').prop('disabled', false);
    
    actualizarContadoresPermisos();
    actualizarResumenPermisos();
}

function previsualizarPermisos() {
    const todosLosPermisos = $('#permisos_todos_permisos').prop('checked');
    const permisosSeleccionados = $('.permiso-individual-gestion:checked');
    const rolNombre = $('#permisos-info-nombre').text();
    
    let contenido = `<h6>Permisos para: <strong>${rolNombre}</strong></h6><hr>`;
    
    if (todosLosPermisos) {
        contenido += '<div class="alert alert-danger"><strong>TODOS LOS PERMISOS DEL SISTEMA</strong></div>';
    } else if (permisosSeleccionados.length === 0) {
        contenido += '<div class="alert alert-warning">No hay permisos seleccionados</div>';
    } else {
        // Agrupar por categorías para vista previa
        const categorias = {};
        permisosSeleccionados.each(function() {
            const categoria = $(this).data('categoria');
            const permiso = $(this).data('permiso');
            const valor = $(this).val();
            
            if (!categorias[categoria]) {
                categorias[categoria] = [];
            }
            categorias[categoria].push({ nombre: permiso, valor: valor });
        });
        
        Object.keys(categorias).forEach(categoria => {
            const nombreCategoria = permisosSistema[categoria].nombre;
            contenido += `<div class="mb-3">
                            <h6 class="text-primary">${nombreCategoria}</h6>
                            <ul class="list-unstyled ms-3">`;
            
            categorias[categoria].forEach(permiso => {
                contenido += `<li><i class="ti ti-check text-success me-2"></i>${permiso.nombre}</li>`;
            });
            
            contenido += '</ul></div>';
        });
    }
    
    $('#contenido-vista-previa').html(contenido);
    $('#modalVistaPreviaPermisos').modal('show');
}

// Manejar envío del formulario de permisos
$('#formGestionPermisos').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('accion', 'actualizar_permisos');
    
    // Si está marcado "todos los permisos", agregar el asterisco
    if ($('#permisos_todos_permisos').prop('checked')) {
        formData.append('permisos[]', '*');
    }
    
    mostrarCarga();
    
    fetch('modales/roles/procesar_roles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            $('#modalGestionPermisos').modal('hide');
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

// Inicializar eventos cuando se abre el modal
$('#modalGestionPermisos').on('shown.bs.modal', function() {
    actualizarContadoresPermisos();
    actualizarResumenPermisos();
});
</script>