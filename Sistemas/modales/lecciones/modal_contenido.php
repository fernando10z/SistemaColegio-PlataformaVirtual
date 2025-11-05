<!-- Modal Ver Contenido -->
<div class="modal fade" id="modalContenido" tabindex="-1" aria-labelledby="modalContenidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalContenidoLabel">
                    <i class="ti ti-eye me-2"></i>
                    Vista Previa de Contenido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Información de la Lección -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2" id="contenidoTitulo"></h3>
                                <p class="text-muted mb-0" id="contenidoDescripcion"></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div id="contenidoMetadatos"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas de Contenido -->
                <ul class="nav nav-tabs mb-4" id="contenidoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="contenido-tab" data-bs-toggle="tab" 
                                data-bs-target="#contenido-pane" type="button" role="tab">
                            <i class="ti ti-file-text me-2"></i>Contenido
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="recursos-tab" data-bs-toggle="tab" 
                                data-bs-target="#recursos-pane" type="button" role="tab">
                            <i class="ti ti-paperclip me-2"></i>Recursos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="progreso-tab" data-bs-toggle="tab" 
                                data-bs-target="#progreso-pane" type="button" role="tab">
                            <i class="ti ti-chart-bar me-2"></i>Progreso de Estudiantes
                        </button>
                    </li>
                </ul>

                <!-- Contenido de las Pestañas -->
                <div class="tab-content" id="contenidoTabsContent">
                    <!-- Pestaña Contenido -->
                    <div class="tab-pane fade show active" id="contenido-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div id="contenidoHTML" class="contenido-preview"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Recursos -->
                    <div class="tab-pane fade" id="recursos-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div id="recursosLista"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña Progreso -->
                    <div class="tab-pane fade" id="progreso-pane" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div id="progresoEstudiantes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnEditarDesdeContenido">
                    <i class="ti ti-edit me-2"></i>
                    Editar Lección
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.contenido-preview {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.8;
    color: #333;
}

.contenido-preview h2 {
    color: #667eea;
    font-size: 1.75rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.contenido-preview h3 {
    color: #495057;
    font-size: 1.4rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
}

.contenido-preview p {
    margin-bottom: 1rem;
    text-align: justify;
}

.contenido-preview ul,
.contenido-preview ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.contenido-preview li {
    margin-bottom: 0.5rem;
}

.contenido-preview strong {
    color: #212529;
    font-weight: 600;
}

.contenido-preview em {
    font-style: italic;
    color: #6c757d;
}

.recurso-card {
    border-left: 4px solid #0d6efd;
    transition: all 0.3s ease;
}

.recurso-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.recurso-icon {
    font-size: 2rem;
    color: #0d6efd;
}

.progreso-estudiante {
    padding: 1rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.progreso-estudiante:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.progreso-bar-container {
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.progreso-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.5s ease;
}

.estado-completado {
    color: #198754;
    font-weight: 600;
}

.estado-en-progreso {
    color: #0d6efd;
    font-weight: 600;
}

.estado-no-iniciado {
    color: #6c757d;
}

.metadato-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
}

.metadato-icon {
    font-size: 1.25rem;
    color: #667eea;
}

.metadato-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.metadato-valor {
    font-size: 1.1rem;
    color: #212529;
    font-weight: 600;
}
</style>

<script>
let leccionActualId = null;

function mostrarContenidoLeccion(leccion) {
    leccionActualId = leccion.id;
    
    // Cargar título y descripción
    $('#contenidoTitulo').text(leccion.titulo);
    $('#contenidoDescripcion').text(leccion.descripcion);
    
    // Cargar metadatos
    const config = leccion.configuraciones || {};
    const metadatosHTML = `
        <div class="metadato-item">
            <i class="ti ti-clock metadato-icon"></i>
            <div>
                <div class="metadato-label">Tiempo Estimado</div>
                <div class="metadato-valor">${config.tiempo_estimado || 0} min</div>
            </div>
        </div>
        <div class="metadato-item">
            <i class="ti ti-file-type metadato-icon"></i>
            <div>
                <div class="metadato-label">Tipo</div>
                <div class="metadato-valor">${leccion.tipo}</div>
            </div>
        </div>
        <div class="metadato-item">
            <i class="ti ti-circle-check metadato-icon"></i>
            <div>
                <div class="metadato-label">Estado</div>
                <div class="metadato-valor">${config.estado || 'BORRADOR'}</div>
            </div>
        </div>
    `;
    $('#contenidoMetadatos').html(metadatosHTML);
    
    // Cargar contenido HTML con validación
    if (leccion.contenido && leccion.contenido.trim()) {
        const contenidoSanitizado = sanitizarHTML(leccion.contenido);
        $('#contenidoHTML').html(contenidoSanitizado);
    } else {
        $('#contenidoHTML').html('<div class="alert alert-info"><i class="ti ti-info-circle me-2"></i>Esta lección no tiene contenido aún.</div>');
    }
    
    // Cargar recursos
    const recursos = leccion.recursos || [];
    if (recursos.length > 0) {
        let recursosHTML = '<div class="row">';
        recursos.forEach(recurso => {
            const iconoRecurso = obtenerIconoRecurso(recurso.tipo);
            const colorRecurso = obtenerColorRecurso(recurso.tipo);
            
            recursosHTML += `
                <div class="col-md-6 mb-3">
                    <div class="card recurso-card" style="border-left-color: ${colorRecurso};">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <i class="${iconoRecurso} recurso-icon" style="color: ${colorRecurso};"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${escapeHTML(recurso.titulo)}</h6>
                                    <small class="text-muted">${recurso.tipo}</small>
                                </div>
                                <a href="${escapeHTML(recurso.url)}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-external-link"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        recursosHTML += '</div>';
        $('#recursosLista').html(recursosHTML);
    } else {
        $('#recursosLista').html('<div class="alert alert-info"><i class="ti ti-info-circle me-2"></i>Esta lección no tiene recursos adicionales.</div>');
    }
    
    // Cargar progreso de estudiantes
    cargarProgresoEstudiantes(leccion.id);
    
    // Configurar botón de editar
    $('#btnEditarDesdeContenido').off('click').on('click', function() {
        $('#modalContenido').modal('hide');
        setTimeout(() => {
            editarLeccion(leccionActualId);
        }, 300);
    });
    
    // Mostrar modal
    $('#modalContenido').modal('show');
}

function cargarProgresoEstudiantes(leccionId) {
    mostrarCarga();
    
    fetch('modales/lecciones/procesar_lecciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_progreso&leccion_id=${leccionId}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success && data.progreso && data.progreso.length > 0) {
            let progresoHTML = '';
            
            data.progreso.forEach(est => {
                const estadoClase = est.estado === 'COMPLETADO' ? 'estado-completado' : 
                                   est.estado === 'EN_PROGRESO' ? 'estado-en-progreso' : 'estado-no-iniciado';
                const estadoTexto = est.estado === 'COMPLETADO' ? 'Completado' : 
                                   est.estado === 'EN_PROGRESO' ? 'En Progreso' : 'No Iniciado';
                
                progresoHTML += `
                    <div class="progreso-estudiante">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>${escapeHTML(est.estudiante_nombre)}</strong>
                                <br><small class="text-muted">${escapeHTML(est.codigo_estudiante)}</small>
                            </div>
                            <div class="text-end">
                                <span class="${estadoClase}">${estadoTexto}</span>
                                <br><small class="text-muted">${est.tiempo_dedicado || 0} min dedicados</small>
                            </div>
                        </div>
                        <div class="progreso-bar-container">
                            <div class="progreso-bar-fill" style="width: ${est.progreso || 0}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Progreso</small>
                            <small class="fw-bold">${est.progreso || 0}%</small>
                        </div>
                    </div>
                `;
            });
            
            $('#progresoEstudiantes').html(progresoHTML);
        } else {
            $('#progresoEstudiantes').html('<div class="alert alert-info"><i class="ti ti-info-circle me-2"></i>Aún no hay estudiantes trabajando en esta lección.</div>');
        }
    })
    .catch(error => {
        ocultarCarga();
        $('#progresoEstudiantes').html('<div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i>Error al cargar el progreso de estudiantes.</div>');
    });
}

function sanitizarHTML(html) {
    // Lista blanca de tags permitidos
    const tagsPermitidos = ['h2', 'h3', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'br'];
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Eliminar scripts y tags no permitidos
    const allElements = tempDiv.getElementsByTagName('*');
    for (let i = allElements.length - 1; i >= 0; i--) {
        const tagName = allElements[i].tagName.toLowerCase();
        if (!tagsPermitidos.includes(tagName)) {
            allElements[i].remove();
        }
    }
    
    return tempDiv.innerHTML;
}

function obtenerIconoRecurso(tipo) {
    switch(tipo) {
        case 'PDF': return 'ti ti-file-text';
        case 'VIDEO': return 'ti ti-video';
        case 'ENLACE': return 'ti ti-link';
        case 'IMAGEN': return 'ti ti-photo';
        default: return 'ti ti-file';
    }
}

function obtenerColorRecurso(tipo) {
    switch(tipo) {
        case 'PDF': return '#dc3545';
        case 'VIDEO': return '#0d6efd';
        case 'ENLACE': return '#198754';
        case 'IMAGEN': return '#fd7e14';
        default: return '#6c757d';
    }
}

function escapeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>