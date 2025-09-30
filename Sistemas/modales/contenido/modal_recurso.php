<!-- Modal Recurso -->
<div class="modal fade" id="modalAgregarRecurso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-file-plus me-2"></i>Nuevo Recurso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formRecurso">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Recurso <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo_recurso" id="tipo_recurso" required>
                            <option value="">Seleccionar...</option>
                            <option value="TAREA">Tarea</option>
                            <option value="CUESTIONARIO">Cuestionario</option>
                            <option value="ANUNCIO">Anuncio</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titulo_recurso" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion_recurso" rows="4"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Este recurso será creado y podrás configurarlo en detalle desde su módulo específico.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-2"></i>Crear Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#formRecurso').on('submit', function(e) {
    e.preventDefault();
    
    $.post('modales/contenido/procesar.php', $(this).serialize() + '&accion=crear_recurso', function(response) {
        if (response.success) {
            Swal.fire('Éxito', response.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }, 'json');
});
</script>