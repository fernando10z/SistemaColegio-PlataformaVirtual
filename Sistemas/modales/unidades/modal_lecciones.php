<!-- Modal Gestión de Lecciones -->
<div class="modal fade" id="modalGestionLecciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-list-details me-2"></i>
                    Gestión de Lecciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Para gestionar las lecciones de esta unidad, será redirigido a la página de lecciones.
                </div>
                
                <div id="leccionesPreview" class="mb-3">
                    <!-- Aquí se cargará un preview de las lecciones -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="irALecciones()">
                    <i class="ti ti-external-link me-2"></i>
                    Ir a Lecciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let unidadActualLecciones = null;

function gestionarLecciones(unidadId) {
    unidadActualLecciones = unidadId;
    
    // Cargar preview de lecciones
    mostrarCarga();
    
    fetch('modales/unidades/procesar_unidades.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_lecciones&id=${unidadId}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            mostrarPreviewLecciones(data.lecciones);
            $('#modalGestionLecciones').modal('show');
        } else {
            // Si no hay lecciones, redirigir directamente
            window.location.href = `lecciones.php?unidad_id=${unidadId}`;
        }
    })
    .catch(error => {
        ocultarCarga();
        window.location.href = `lecciones.php?unidad_id=${unidadId}`;
    });
}

function mostrarPreviewLecciones(lecciones) {
    const container = $('#leccionesPreview');
    
    if (lecciones.length === 0) {
        container.html('<p class="text-muted">No hay lecciones creadas aún.</p>');
        return;
    }
    
    let html = '<div class="list-group">';
    lecciones.forEach(leccion => {
        const iconoTipo = leccion.tipo === 'CONTENIDO' ? 'ti-file-text' : 
                         leccion.tipo === 'EVALUACION' ? 'ti-clipboard-check' : 'ti-activity';
        const colorTipo = leccion.tipo === 'CONTENIDO' ? 'primary' : 
                         leccion.tipo === 'EVALUACION' ? 'danger' : 'info';
        
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-${colorTipo}">
                            <i class="ti ${iconoTipo}"></i>
                        </span>
                        <div>
                            <h6 class="mb-0">${leccion.titulo}</h6>
                            <small class="text-muted">Orden: ${leccion.orden}</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary">${leccion.tipo}</span>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.html(html);
}

function irALecciones() {
    if (unidadActualLecciones) {
        window.location.href = `lecciones.php?unidad_id=${unidadActualLecciones}`;
    }
}
</script>