<?php
// modales/areas/modal_editar.php
?>
<!-- Modal Editar Área Curricular -->
<div class="modal fade" id="modalEditarArea" tabindex="-1" aria-labelledby="modalEditarAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarAreaLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Área Curricular
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarArea" method="POST">
                <input type="hidden" id="edit_area_id" name="id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Área <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_codigo" class="form-label">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="edit_codigo" name="codigo" 
                                                   maxlength="10" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="edit_descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_activo" class="form-label">Estado</label>
                                            <select class="form-select" id="edit_activo" name="activo">
                                                <option value="1">Activa</option>
                                                <option value="0">Inactiva</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas del Área -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Estadísticas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="border rounded p-2">
                                                <h5 class="text-primary mb-1" id="edit_total_asignaciones">0</h5>
                                                <small class="text-muted">Asignaciones</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="border rounded p-2">
                                                <h5 class="text-success mb-1" id="edit_docentes_asignados">0</h5>
                                                <small class="text-muted">Docentes</small>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="border rounded p-2">
                                                <h5 class="text-info mb-1" id="edit_niveles_atendidos">0</h5>
                                                <small class="text-muted">Niveles Atendidos</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="edit_niveles_nombres" class="mt-2">
                                        <!-- Se llenan dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Competencias -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-target me-2"></i>
                                        Competencias Definidas
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="abrirGestionCompetencias()">
                                        <i class="ti ti-settings me-1"></i>
                                        Gestionar Competencias
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="edit_competencias_resumen">
                                        <!-- Se carga dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validaciones y Alertas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-alert-circle me-2"></i>
                                        Validaciones del Sistema
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="edit_validaciones">
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="ejecutarValidacionesArea()">
                                            <i class="ti ti-check me-1"></i>
                                            Ejecutar Validaciones
                                        </button>
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
                    <button type="button" class="btn btn-info" onclick="abrirGestionCompetencias()">
                        <i class="ti ti-target me-2"></i>
                        Gestionar Competencias
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarArea">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Convertir código a mayúsculas
    $('#edit_codigo').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Envío del formulario de edición
    $('#formEditarArea').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditarArea()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'editar');

        mostrarCarga();
        $('#btnActualizarArea').prop('disabled', true);

        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarArea').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Área Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarArea').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarArea').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    function validarFormularioEditarArea() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar nombre
        if (!$('#edit_nombre').val().trim()) {
            mostrarErrorCampo('#edit_nombre', 'El nombre es requerido');
            isValid = false;
        }
        
        // Validar código
        const codigo = $('#edit_codigo').val().trim();
        if (!codigo) {
            mostrarErrorCampo('#edit_codigo', 'El código es requerido');
            isValid = false;
        } else if (codigo.length < 2) {
            mostrarErrorCampo('#edit_codigo', 'El código debe tener al menos 2 caracteres');
            isValid = false;
        }
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    // Función para abrir gestión de competencias
    window.abrirGestionCompetencias = function() {
        const areaId = $('#edit_area_id').val();
        if (areaId) {
            $('#modalEditarArea').modal('hide');
            gestionarCompetencias(areaId);
        }
    };

    // Función para ejecutar validaciones del área
    window.ejecutarValidacionesArea = function() {
        const areaId = $('#edit_area_id').val();
        
        mostrarCarga();
        
        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: {
                accion: 'validar_area',
                id: areaId
            },
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                
                if (response.success) {
                    mostrarValidacionesArea(response);
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                mostrarError('Error al ejecutar validaciones');
            }
        });
    };

    function mostrarValidacionesArea(validaciones) {
        let html = '';
        
        if (validaciones.errores && validaciones.errores.length > 0) {
            html += '<div class="alert alert-danger">';
            html += '<strong>Errores encontrados:</strong><ul class="mb-0">';
            validaciones.errores.forEach(error => {
                html += `<li>${error}</li>`;
            });
            html += '</ul></div>';
        }
        
        if (validaciones.advertencias && validaciones.advertencias.length > 0) {
            html += '<div class="alert alert-warning">';
            html += '<strong>Advertencias:</strong><ul class="mb-0">';
            validaciones.advertencias.forEach(adv => {
                html += `<li>${adv}</li>`;
            });
            html += '</ul></div>';
        }
        
        if (validaciones.ok) {
            html += '<div class="alert alert-success">';
            html += '<i class="ti ti-check me-2"></i>Todas las validaciones pasaron correctamente';
            html += '</div>';
        }
        
        $('#edit_validaciones').html(html);
    }

    // Función global para cargar datos de edición
    window.cargarDatosEdicionArea = function(area) {
        $('#edit_area_id').val(area.id);
        $('#edit_nombre').val(area.nombre);
        $('#edit_codigo').val(area.codigo);
        $('#edit_descripcion').val(area.descripcion || '');
        $('#edit_activo').val(area.activo);
        
        // Cargar estadísticas
        $('#edit_total_asignaciones').text(area.total_asignaciones || 0);
        $('#edit_docentes_asignados').text(area.docentes_asignados || 0);
        $('#edit_niveles_atendidos').text(area.niveles_atendidos || 0);
        
        // Cargar niveles nombres
        if (area.niveles_nombres) {
            const niveles = area.niveles_nombres.split(', ');
            let nivelesHtml = '';
            niveles.forEach(nivel => {
                nivelesHtml += `<span class="badge bg-info me-1">${nivel}</span>`;
            });
            $('#edit_niveles_nombres').html(nivelesHtml);
        } else {
            $('#edit_niveles_nombres').html('<small class="text-muted">Sin asignaciones</small>');
        }
        
        // Cargar resumen de competencias
        cargarResumenCompetencias(area.id);
    };

    function cargarResumenCompetencias(areaId) {
        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: {
                accion: 'obtener_competencias',
                id: areaId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarResumenCompetencias(response.competencias);
                }
            }
        });
    }

    function mostrarResumenCompetencias(competencias) {
        let html = '';
        let totalCompetencias = 0;
        
        if (!competencias || Object.keys(competencias).length === 0) {
            html = '<div class="text-center text-muted"><i class="ti ti-target-off me-2"></i>Sin competencias definidas</div>';
        } else {
            html = '<div class="row">';
            
            Object.keys(competencias).forEach(nivel => {
                if (competencias[nivel] && typeof competencias[nivel] === 'object') {
                    let nivelCompetencias = 0;
                    
                    Object.keys(competencias[nivel]).forEach(grado => {
                        const comps = competencias[nivel][grado];
                        if (Array.isArray(comps)) {
                            nivelCompetencias += comps.length;
                        }
                    });
                    
                    totalCompetencias += nivelCompetencias;
                    
                    html += `<div class="col-md-4 mb-2">
                                <div class="text-center p-2 border rounded">
                                    <h6 class="mb-1">${nivel}</h6>
                                    <span class="badge bg-success">${nivelCompetencias} competencias</span>
                                </div>
                            </div>`;
                }
            });
            
            html += '</div>';
            html += `<div class="text-center mt-2">
                        <strong>Total: ${totalCompetencias} competencias definidas</strong>
                    </div>`;
        }
        
        $('#edit_competencias_resumen').html(html);
    }
});
</script>