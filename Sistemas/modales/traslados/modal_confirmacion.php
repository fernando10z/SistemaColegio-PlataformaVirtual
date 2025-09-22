<!-- Modal Confirmación Traslado -->
<div class="modal fade" id="modalConfirmacionTraslado" tabindex="-1" aria-labelledby="modalConfirmacionTrasladoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                <h5 class="modal-title" id="modalConfirmacionTrasladoLabel">
                    <i class="ti ti-alert-triangle me-2"></i>
                    Confirmar Traslado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>¡Atención!</strong> Esta acción trasladará al estudiante y actualizará su matrícula.
                </div>
                
                <div id="datosConfirmacion">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnConfirmarTraslado">
                    <i class="ti ti-check me-2"></i>
                    Confirmar Traslado
                </button>
            </div>
        </div>
    </div>
</div>