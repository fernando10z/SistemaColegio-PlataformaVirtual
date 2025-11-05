<!-- Modal Lección -->
<div class="modal fade" id="modalLeccion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalLeccionLabel">
                    <i class="ti ti-file me-2"></i>Nueva Lección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formLeccion">
                <input type="hidden" id="leccion_id" name="leccion_id">
                <input type="hidden" id="unidad_id_leccion" name="unidad_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo_leccion" name="titulo" required maxlength="255">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Orden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="orden_leccion" name="orden" required min="1">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion_leccion" name="descripcion" rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_leccion" name="tipo" required>
                                <option value="CONTENIDO">Contenido</option>
                                <option value="ACTIVIDAD">Actividad</option>
                                <option value="EVALUACION">Evaluación</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tiempo Estimado (min)</label>
                            <input type="number" class="form-control" id="tiempo_estimado" name="tiempo_estimado" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado_leccion" name="estado">
                                <option value="BORRADOR">Borrador</option>
                                <option value="PUBLICADO">Publicado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#formLeccion').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const leccionId = $('#leccion_id').val();
    const accion = leccionId ? 'actualizar_leccion' : 'crear_leccion';
    
    $.post('modales/contenido/procesar.php', formData + '&accion=' + accion, function(response) {
        if (response.success) {
            Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }, 'json');
});
</script>