<!-- Modal Editar Unidad -->
<div class="modal fade" id="modalEditarUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-edit me-2"></i>
                    Editar Unidad Didáctica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formEditarUnidad" method="POST">
                <input type="hidden" id="edit_unidad_id" name="unidad_id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Curso -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Curso <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="edit_curso_id" name="curso_id" required>
                                <option value="">Seleccionar curso</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id'] ?>">
                                        <?= htmlspecialchars($curso['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Orden -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Orden <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_orden" name="orden" 
                                   min="1" max="50" required>
                            <small class="text-muted">Posición en el curso</small>
                        </div>

                        <!-- Título -->
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                   required maxlength="255">
                        </div>

                        <!-- Descripción -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                      rows="3" maxlength="500"></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="edit_fecha_inicio" name="fecha_inicio">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="edit_fecha_fin" name="fecha_fin">
                        </div>

                        <!-- Estado -->
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="edit_estado" name="estado" required>
                                <option value="BORRADOR">Borrador</option>
                                <option value="PUBLICADO">Publicado</option>
                            </select>
                        </div>

                        <!-- Información adicional -->
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Nota:</strong> Los cambios se aplicarán inmediatamente. 
                                Si cambia el orden, puede afectar la visualización de otras unidades.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnActualizarUnidad">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Unidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicionUnidad(unidad) {
    $('#edit_unidad_id').val(unidad.id);
    $('#edit_curso_id').val(unidad.curso_id);
    $('#edit_orden').val(unidad.orden);
    $('#edit_titulo').val(unidad.titulo);
    $('#edit_descripcion').val(unidad.descripcion || '');
    
    const config = unidad.configuraciones || {};
    $('#edit_fecha_inicio').val(config.fecha_inicio || '');
    $('#edit_fecha_fin').val(config.fecha_fin || '');
    $('#edit_estado').val(config.estado || 'BORRADOR');
}

$(document).ready(function() {
    // Validar fechas
    $('#edit_fecha_inicio, #edit_fecha_fin').on('change', function() {
        const inicio = $('#edit_fecha_inicio').val();
        const fin = $('#edit_fecha_fin').val();
        
        if (inicio && fin && inicio > fin) {
            mostrarError('La fecha de inicio no puede ser posterior a la fecha de fin');
            $(this).val('');
        }
    });

    // Envío del formulario
    $('#formEditarUnidad').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarUnidad').prop('disabled', true);

        $.ajax({
            url: 'modales/unidades/procesar_unidades.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarUnidad').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarUnidad').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarUnidad').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });
});
</script>