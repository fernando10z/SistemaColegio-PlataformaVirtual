<!-- Modal Reenviar Comunicación -->
<div class="modal fade" id="modalReenviarComunicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <h5 class="modal-title">
                    <i class="ti ti-reload me-2"></i>
                    Reenviar Comunicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formReenviarComunicacion" method="POST">
                <input type="hidden" id="reenviar_comunicacion_id" name="comunicacion_id">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Esta comunicación será reenviada con la misma configuración original.
                    </div>

                    <div class="mb-3">
                        <label for="reenviar_destinatario" class="form-label">
                            Nuevo Destinatario (Opcional)
                        </label>
                        <input type="text" class="form-control" id="reenviar_destinatario" name="nuevo_destinatario" 
                               placeholder="Dejar vacío para usar el original">
                    </div>

                    <div class="mb-3">
                        <label for="reenviar_motivo" class="form-label">Motivo del Reenvío</label>
                        <textarea class="form-control" id="reenviar_motivo" name="motivo" rows="3" 
                                  placeholder="Opcional: Indique el motivo del reenvío"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="reenviar_confirmar" required>
                        <label class="form-check-label" for="reenviar_confirmar">
                            Confirmo que deseo reenviar esta comunicación
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnReenviar">
                        <i class="ti ti-reload me-2"></i>Reenviar Ahora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#formReenviarComunicacion').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('accion', 'reenviar');
    
    mostrarCarga();
    $('#btnReenviar').prop('disabled', true);

    $.ajax({
        url: 'modales/comunicaciones/procesar_comunicaciones.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            $('#btnReenviar').prop('disabled', false);
            
            if (response.success) {
                Swal.fire({
                    title: '¡Comunicación Reenviada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalReenviarComunicacion').modal('hide');
                    location.reload();
                });
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarCarga();
            $('#btnReenviar').prop('disabled', false);
            mostrarError('Error al procesar la solicitud');
        }
    });
});
</script>