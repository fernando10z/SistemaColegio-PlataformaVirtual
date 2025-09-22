<?php
// modales/malla/modal_competencias.php
?>
<!-- Modal Gestionar Competencias -->
<div class="modal fade" id="modalCompetencias" tabindex="-1" aria-labelledby="modalCompetenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCompetenciasLabel">
                    <i class="ti ti-target me-2"></i>
                    Gestionar Competencias del Grado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formCompetencias" method="POST">
                <input type="hidden" id="comp_id" name="id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información de la Asignación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información de la Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <label class="text-muted small">Nivel:</label>
                                                <div class="fw-bold" id="comp_nivel_info">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-item">
                                                <label class="text-muted small">Grado:</label>
                                                <div class="fw-bold" id="comp_grado_info">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-item">
                                                <label class="text-muted small">Área Curricular:</label>
                                                <div class="fw-bold" id="comp_area_info">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="info-item">
                                                <label class="text-muted small">Horas/Sem:</label>
                                                <div class="fw-bold text-primary" id="comp_horas_info">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gestión de Competencias -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-list me-2"></i>
                                        Competencias del Grado
                                    </h6>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="add-competencia-modal">
                                        <i class="ti ti-plus me-1"></i>
                                        Agregar Competencia
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="competencias-container-modal">
                                        <div class="alert alert-info" id="no-competencias-alert">
                                            <i class="ti ti-info-circle me-2"></i>
                                            No se han definido competencias para esta asignación.
                                        </div>
                                        <div id="competencias-list-modal">
                                            <!-- Las competencias se cargarán aquí dinámicamente -->
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="alert alert-light">
                                                    <h6 class="alert-heading mb-2">
                                                        <i class="ti ti-lightbulb me-2"></i>
                                                        Recomendaciones para Competencias:
                                                    </h6>
                                                    <ul class="mb-0 small">
                                                        <li>Define competencias específicas y medibles</li>
                                                        <li>Usa verbos de acción (analiza, resuelve, argumenta, etc.)</li>
                                                        <li>Considera el nivel cognitivo apropiado para el grado</li>
                                                        <li>Alinea con el currículo nacional</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title mb-1" id="total-competencias">0</h5>
                                                        <p class="card-text small mb-0">Competencias Definidas</p>
                                                    </div>
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
                        Cerrar
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnPreviewCompetencias">
                        <i class="ti ti-eye me-2"></i>
                        Vista Previa
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardarCompetencias">
                        <i class="ti ti-device-floppy me-2"></i>
                        Guardar Competencias
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let competenciasData = [];

function cargarCompetencias(asignacion) {
    // Cargar información de la asignación
    $('#comp_id').val(asignacion.id);
    $('#comp_nivel_info').text(asignacion.nivel_nombre);
    $('#comp_grado_info').text(asignacion.grado);
    $('#comp_area_info').text(asignacion.area_nombre);
    $('#comp_horas_info').text(asignacion.horas_semanales + ' hrs');
    
    // Cargar competencias existentes
    competenciasData = [];
    if (asignacion.competencias_grado) {
        try {
            competenciasData = typeof asignacion.competencias_grado === 'string' 
                ? JSON.parse(asignacion.competencias_grado) 
                : asignacion.competencias_grado;
        } catch (e) {
            console.error('Error parsing competencias:', e);
            competenciasData = [];
        }
    }
    
    renderizarCompetencias();
}

function renderizarCompetencias() {
    const container = $('#competencias-list-modal');
    const alertNoCompetencias = $('#no-competencias-alert');
    
    container.empty();
    
    if (competenciasData.length === 0) {
        alertNoCompetencias.show();
        container.hide();
    } else {
        alertNoCompetencias.hide();
        container.show();
        
        competenciasData.forEach((competencia, index) => {
            const competenciaHtml = `
                <div class="competencia-item-modal mb-3" data-index="${index}">
                    <div class="card border-start border-success border-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="d-flex align-items-start">
                                        <div class="badge bg-success me-3 mt-1">${index + 1}</div>
                                        <div class="flex-grow-1">
                                            <textarea class="form-control competencia-text" 
                                                      rows="2" 
                                                      placeholder="Describe la competencia...">${competencia}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group-vertical">
                                        <button type="button" class="btn btn-outline-primary btn-sm move-up" 
                                                ${index === 0 ? 'disabled' : ''}>
                                            <i class="ti ti-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm move-down" 
                                                ${index === competenciasData.length - 1 ? 'disabled' : ''}>
                                            <i class="ti ti-arrow-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-competencia-modal">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(competenciaHtml);
        });
    }
    
    actualizarContadorCompetencias();
}

function actualizarContadorCompetencias() {
    $('#total-competencias').text(competenciasData.length);
}

function agregarCompetencia() {
    competenciasData.push('');
    renderizarCompetencias();
    
    // Enfocar la nueva competencia
    const nuevaCompetencia = $('.competencia-item-modal').last().find('.competencia-text');
    nuevaCompetencia.focus();
}

function moverCompetencia(index, direccion) {
    if (direccion === 'up' && index > 0) {
        [competenciasData[index], competenciasData[index - 1]] = [competenciasData[index - 1], competenciasData[index]];
    } else if (direccion === 'down' && index < competenciasData.length - 1) {
        [competenciasData[index], competenciasData[index + 1]] = [competenciasData[index + 1], competenciasData[index]];
    }
    renderizarCompetencias();
}

function eliminarCompetencia(index) {
    competenciasData.splice(index, 1);
    renderizarCompetencias();
}

$(document).ready(function() {
    // Agregar competencia
    $('#add-competencia-modal').on('click', function() {
        agregarCompetencia();
    });

    // Event listeners delegados para elementos dinámicos
    $(document).on('input', '.competencia-text', function() {
        const index = $(this).closest('.competencia-item-modal').data('index');
        competenciasData[index] = $(this).val();
    });

    $(document).on('click', '.move-up', function() {
        const index = $(this).closest('.competencia-item-modal').data('index');
        moverCompetencia(index, 'up');
    });

    $(document).on('click', '.move-down', function() {
        const index = $(this).closest('.competencia-item-modal').data('index');
        moverCompetencia(index, 'down');
    });

    $(document).on('click', '.remove-competencia-modal', function() {
        const index = $(this).closest('.competencia-item-modal').data('index');
        
        Swal.fire({
            title: '¿Eliminar competencia?',
            text: 'Esta acción no se puede deshacer',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarCompetencia(index);
            }
        });
    });

    // Vista previa
    $('#btnPreviewCompetencias').on('click', function() {
        if (competenciasData.length === 0) {
            Swal.fire({
                title: 'Sin Competencias',
                text: 'No hay competencias definidas para mostrar',
                icon: 'info'
            });
            return;
        }

        const competenciasHtml = competenciasData
            .filter(comp => comp.trim() !== '')
            .map((comp, index) => `<li class="mb-2">${comp}</li>`)
            .join('');

        Swal.fire({
            title: 'Vista Previa de Competencias',
            html: `<ol class="text-start">${competenciasHtml}</ol>`,
            icon: 'info',
            width: '600px'
        });
    });

    // Envío del formulario
    $('#formCompetencias').on('submit', function(e) {
        e.preventDefault();
        
        // Filtrar competencias vacías
        const competenciasFiltradas = competenciasData.filter(comp => comp.trim() !== '');
        
        const formData = new FormData();
        formData.append('accion', 'actualizar_competencias');
        formData.append('id', $('#comp_id').val());
        formData.append('competencias', JSON.stringify(competenciasFiltradas));
        
        mostrarCarga();
        $('#btnGuardarCompetencias').prop('disabled', true);

        $.ajax({
            url: 'modales/malla/procesar_malla.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarCompetencias').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Competencias Guardadas!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalCompetencias').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarCompetencias').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar al cerrar modal
    $('#modalCompetencias').on('hidden.bs.modal', function() {
        competenciasData = [];
        $('#competencias-list-modal').empty();
        $('#no-competencias-alert').show();
        actualizarContadorCompetencias();
    });
});
</script>

<style>
.info-item {
    margin-bottom: 0.5rem;
}

.competencia-item-modal .btn-group-vertical .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.competencia-item-modal .badge {
    font-size: 0.75rem;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-start {
    border-left-width: 4px !important;
}

.card-body .row {
    margin: 0;
}

.card-body .col,
.card-body .col-auto {
    padding: 0.5rem;
}
</style>