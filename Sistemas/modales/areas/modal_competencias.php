<?php
// modales/areas/modal_competencias.php
?>
<!-- Modal Gestión de Competencias -->
<div class="modal fade" id="modalGestionCompetencias" tabindex="-1" aria-labelledby="modalGestionCompetenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalGestionCompetenciasLabel">
                    <i class="ti ti-target me-2"></i>
                    Gestión de Competencias - <span id="comp_area_nombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formGestionCompetencias" method="POST">
                <input type="hidden" id="comp_area_id" name="area_id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Panel de Control -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ti ti-settings me-2"></i>
                                        Panel de Control
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nivel Educativo</label>
                                        <select class="form-select" id="comp_nivel_selector">
                                            <option value="">Seleccionar nivel</option>
                                            <?php foreach ($niveles as $nivel): ?>
                                                <option value="<?= strtolower($nivel['nombre']) ?>" data-id="<?= $nivel['id'] ?>">
                                                    <?= htmlspecialchars($nivel['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Grado</label>
                                        <select class="form-select" id="comp_grado_selector" disabled>
                                            <option value="">Seleccionar grado</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary btn-sm w-100" 
                                                onclick="cargarCompetenciasPredefinidas()" disabled id="btnCargarPredefinidas">
                                            <i class="ti ti-download me-1"></i>
                                            Cargar Predefinidas
                                        </button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-success btn-sm w-100" 
                                                onclick="agregarCompetencia()" disabled id="btnAgregarCompetencia">
                                            <i class="ti ti-plus me-1"></i>
                                            Nueva Competencia
                                        </button>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-warning btn-sm w-100" 
                                                onclick="copiarCompetenciasGrado()">
                                            <i class="ti ti-copy me-1"></i>
                                            Copiar a otros grados
                                        </button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-info btn-sm w-100" 
                                                onclick="exportarCompetencias()">
                                            <i class="ti ti-file-export me-1"></i>
                                            Exportar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumen Global -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Resumen Global
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="resumen_global">
                                        <!-- Se carga dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editor de Competencias -->
                        <div class="col-md-9">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="ti ti-edit me-2"></i>
                                        Editor de Competencias
                                        <span id="comp_contexto" class="badge bg-info ms-2"></span>
                                    </h6>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="previewCompetencias()">
                                            <i class="ti ti-eye me-1"></i>
                                            Vista Previa
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" onclick="guardarCompetenciasRapido()">
                                            <i class="ti ti-device-floppy me-1"></i>
                                            Guardar Rápido
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="competencias_editor">
                                        <div class="text-center text-muted py-5">
                                            <i class="ti ti-target" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <h5 class="mt-3">Selecciona un nivel y grado para comenzar</h5>
                                            <p>Usa el panel de control para navegar entre niveles y grados</p>
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
                    <button type="button" class="btn btn-info" onclick="validarCompetencias()">
                        <i class="ti ti-check me-2"></i>
                        Validar
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

<!-- Template para competencia -->
<template id="templateCompetencia">
    <div class="competencia-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center">
                <span class="drag-handle me-2" style="cursor: move;">
                    <i class="ti ti-grip-vertical"></i>
                </span>
                <span class="competencia-numero badge bg-primary"></span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger eliminar-competencia">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
        <div class="mb-2">
            <textarea class="form-control competencia-texto" name="competencias[]" 
                      rows="2" placeholder="Escriba la competencia..." required></textarea>
        </div>
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">Capacidades (opcional):</small>
                <textarea class="form-control capacidades-texto" name="capacidades[]" 
                          rows="2" placeholder="Capacidades específicas..."></textarea>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Estándares (opcional):</small>
                <textarea class="form-control estandares-texto" name="estandares[]" 
                          rows="2" placeholder="Estándares de aprendizaje..."></textarea>
            </div>
        </div>
    </div>
</template>

<!-- Modal Preview Competencias -->
<div class="modal fade" id="modalPreviewCompetencias" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Vista Previa de Competencias</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview_content">
                    <!-- Se carga dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" onclick="imprimirPreview()">
                    <i class="ti ti-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let competenciasData = {};
    let competenciaIndex = 0;

    // Cambio de nivel
    $('#comp_nivel_selector').on('change', function() {
        const nivel = $(this).val();
        cargarGradosNivel(nivel);
        $('#comp_grado_selector').prop('disabled', !nivel);
        $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', true);
        limpiarEditor();
    });

    // Cambio de grado
    $('#comp_grado_selector').on('change', function() {
        const grado = $(this).val();
        const nivel = $('#comp_nivel_selector').val();
        
        if (nivel && grado) {
            $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', false);
            $('#comp_contexto').text(`${nivel.toUpperCase()} - ${grado}`);
            cargarCompetenciasGrado(nivel, grado);
        } else {
            $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', true);
            $('#comp_contexto').text('');
            limpiarEditor();
        }
    });

    // Eliminar competencia
    $(document).on('click', '.eliminar-competencia', function() {
        $(this).closest('.competencia-item').remove();
        actualizarNumerosCompetencias();
    });

    // Envío del formulario
    $('#formGestionCompetencias').on('submit', function(e) {
        e.preventDefault();
        guardarCompetenciasCompleto();
    });

    function cargarGradosNivel(nivel) {
        $('#comp_grado_selector').empty().append('<option value="">Seleccionar grado</option>');
        
        if (nivel && competenciasBase[nivel]) {
            const grados = competenciasBase[nivel].grados;
            grados.forEach(function(grado) {
                $('#comp_grado_selector').append(`<option value="${grado}">${grado}</option>`);
            });
        }
    }

    function limpiarEditor() {
        $('#competencias_editor').html(`
            <div class="text-center text-muted py-5">
                <i class="ti ti-target" style="font-size: 3rem; opacity: 0.3;"></i>
                <h5 class="mt-3">Selecciona un nivel y grado para comenzar</h5>
                <p>Usa el panel de control para navegar entre niveles y grados</p>
            </div>
        `);
    }

    function cargarCompetenciasGrado(nivel, grado) {
        if (!competenciasData[nivel]) competenciasData[nivel] = {};
        if (!competenciasData[nivel][grado]) competenciasData[nivel][grado] = [];

        mostrarCompetenciasEditor(competenciasData[nivel][grado]);
    }

    function mostrarCompetenciasEditor(competencias) {
        $('#competencias_editor').empty();
        competenciaIndex = 0;

        if (competencias.length === 0) {
            $('#competencias_editor').html(`
                <div class="text-center text-muted py-4">
                    <p>No hay competencias definidas para este grado</p>
                    <button type="button" class="btn btn-primary" onclick="agregarCompetencia()">
                        <i class="ti ti-plus me-2"></i>Agregar Primera Competencia
                    </button>
                </div>
            `);
        } else {
            competencias.forEach(function(competencia) {
                agregarCompetenciaHTML(competencia);
            });
        }
    }

    window.agregarCompetencia = function(competenciaData = null) {
        const template = $('#templateCompetencia').html();
        $('#competencias_editor').append(template);
        
        const nuevaCompetencia = $('.competencia-item').last();
        
        if (competenciaData) {
            nuevaCompetencia.find('.competencia-texto').val(competenciaData.texto || '');
            nuevaCompetencia.find('.capacidades-texto').val(competenciaData.capacidades || '');
            nuevaCompetencia.find('.estandares-texto').val(competenciaData.estandares || '');
        }
        
        competenciaIndex++;
        actualizarNumerosCompetencias();
        
        // Focus en el nuevo campo
        nuevaCompetencia.find('.competencia-texto').focus();
    };

    function agregarCompetenciaHTML(competenciaData) {
        agregarCompetencia(competenciaData);
    }

    function actualizarNumerosCompetencias() {
        $('.competencia-item').each(function(index) {
            $(this).find('.competencia-numero').text(index + 1);
        });
    }

    window.cargarCompetenciasPredefinidas = function() {
        const codigo = $('#comp_area_codigo').val();
        
        if (codigo && competenciasPredefinidas[codigo]) {
            const competencias = competenciasPredefinidas[codigo];
            
            Swal.fire({
                title: 'Cargar Competencias Predefinidas',
                html: `¿Deseas cargar las ${competencias.length} competencias predefinidas para ${codigo}?<br>
                       <small class="text-muted">Esto agregará las competencias a las existentes</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cargar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    competencias.forEach(function(competencia) {
                        agregarCompetencia({ texto: competencia });
                    });
                    
                    mostrarExito('Competencias predefinidas cargadas');
                }
            });
        } else {
            mostrarError('No hay competencias predefinidas para este código de área');
        }
    };

    window.copiarCompetenciasGrado = function() {
        const nivelActual = $('#comp_nivel_selector').val();
        const gradoActual = $('#comp_grado_selector').val();
        
        if (!nivelActual || !gradoActual) {
            mostrarError('Selecciona un nivel y grado primero');
            return;
        }
        
        // Recopilar competencias actuales
        const competenciasActuales = [];
        $('.competencia-item').each(function() {
            const competencia = {
                texto: $(this).find('.competencia-texto').val(),
                capacidades: $(this).find('.capacidades-texto').val(),
                estandares: $(this).find('.estandares-texto').val()
            };
            if (competencia.texto.trim()) {
                competenciasActuales.push(competencia);
            }
        });
        
        if (competenciasActuales.length === 0) {
            mostrarError('No hay competencias para copiar');
            return;
        }
        
        // Mostrar modal de selección
        mostrarModalCopiarCompetencias(nivelActual, gradoActual, competenciasActuales);
    };

    function mostrarModalCopiarCompetencias(nivelOrigen, gradoOrigen, competencias) {
        let opcionesHtml = '';
        
        Object.keys(competenciasBase).forEach(nivel => {
            if (competenciasBase[nivel].grados) {
                competenciasBase[nivel].grados.forEach(grado => {
                    if (!(nivel === nivelOrigen && grado === gradoOrigen)) {
                        opcionesHtml += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${nivel}|${grado}" id="copy_${nivel}_${grado}">
                                <label class="form-check-label" for="copy_${nivel}_${grado}">
                                    ${nivel.toUpperCase()} - ${grado}
                                </label>
                            </div>
                        `;
                    }
                });
            }
        });
        
        Swal.fire({
            title: 'Copiar Competencias',
            html: `
                <p>Selecciona los grados donde copiar las ${competencias.length} competencias:</p>
                <div class="text-start" style="max-height: 300px; overflow-y: auto;">
                    ${opcionesHtml}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Copiar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const seleccionados = [];
                $('input[id^="copy_"]:checked').each(function() {
                    seleccionados.push($(this).val());
                });
                
                if (seleccionados.length === 0) {
                    Swal.showValidationMessage('Selecciona al menos un grado destino');
                }
                
                return seleccionados;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCopiaCompetencias(competencias, result.value);
            }
        });
    }

    function ejecutarCopiaCompetencias(competencias, destinos) {
        destinos.forEach(destino => {
            const [nivel, grado] = destino.split('|');
            
            if (!competenciasData[nivel]) competenciasData[nivel] = {};
            if (!competenciasData[nivel][grado]) competenciasData[nivel][grado] = [];
            
            // Agregar competencias (evitar duplicados por texto)
            competencias.forEach(comp => {
                const existe = competenciasData[nivel][grado].some(existing => 
                    existing.texto.trim().toLowerCase() === comp.texto.trim().toLowerCase()
                );
                
                if (!existe) {
                    competenciasData[nivel][grado].push({...comp});
                }
            });
        });
        
        mostrarExito(`Competencias copiadas a ${destinos.length} grados`);
    }

    window.guardarCompetenciasRapido = function() {
        recopilarCompetenciasActuales();
        mostrarExito('Competencias guardadas en memoria (usar "Guardar Competencias" para persistir)');
    };

    function recopilarCompetenciasActuales() {
        const nivel = $('#comp_nivel_selector').val();
        const grado = $('#comp_grado_selector').val();
        
        if (!nivel || !grado) return;
        
        if (!competenciasData[nivel]) competenciasData[nivel] = {};
        competenciasData[nivel][grado] = [];
        
        $('.competencia-item').each(function() {
            const competencia = {
                texto: $(this).find('.competencia-texto').val().trim(),
                capacidades: $(this).find('.capacidades-texto').val().trim(),
                estandares: $(this).find('.estandares-texto').val().trim()
            };
            
            if (competencia.texto) {
                competenciasData[nivel][grado].push(competencia);
            }
        });
    }

    function guardarCompetenciasCompleto() {
        recopilarCompetenciasActuales();
        
        mostrarCarga();
        $('#btnGuardarCompetencias').prop('disabled', true);
        
        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: {
                accion: 'guardar_competencias',
                area_id: $('#comp_area_id').val(),
                competencias: JSON.stringify(competenciasData)
            },
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarCompetencias').prop('disabled', false);
                
                if (response.success) {
                    mostrarExito(response.message);
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarCompetencias').prop('disabled', false);
                mostrarError('Error al guardar competencias');
            }
        });
    }

    window.previewCompetencias = function() {
        recopilarCompetenciasActuales();
        generarPreviewCompetencias();
        $('#modalPreviewCompetencias').modal('show');
    };

    function generarPreviewCompetencias() {
        let html = `<h4>Competencias del Área: ${$('#comp_area_nombre').text()}</h4>`;
        
        Object.keys(competenciasData).forEach(nivel => {
            if (competenciasData[nivel] && Object.keys(competenciasData[nivel]).length > 0) {
                html += `<h5 class="mt-4 text-primary">${nivel.toUpperCase()}</h5>`;
                
                Object.keys(competenciasData[nivel]).forEach(grado => {
                    const competencias = competenciasData[nivel][grado];
                    if (competencias.length > 0) {
                        html += `<h6 class="mt-3">${grado}</h6>`;
                        html += '<ol>';
                        
                        competencias.forEach(comp => {
                            html += `<li class="mb-2"><strong>${comp.texto}</strong>`;
                            if (comp.capacidades) {
                                html += `<br><small><strong>Capacidades:</strong> ${comp.capacidades}</small>`;
                            }
                            if (comp.estandares) {
                                html += `<br><small><strong>Estándares:</strong> ${comp.estandares}</small>`;
                            }
                            html += '</li>';
                        });
                        
                        html += '</ol>';
                    }
                });
            }
        });
        
        $('#preview_content').html(html);
    }

    // Función global para cargar gestión de competencias
    window.cargarGestionCompetencias = function(area) {
        $('#comp_area_id').val(area.id);
        $('#comp_area_nombre').text(area.nombre);
        $('#comp_area_codigo').val(area.codigo);
        
        // Cargar competencias existentes
        if (area.competencias) {
            try {
                competenciasData = JSON.parse(area.competencias) || {};
            } catch (e) {
                competenciasData = {};
            }
        } else {
            competenciasData = {};
        }
        
        // Reset selectors
        $('#comp_nivel_selector, #comp_grado_selector').val('');
        $('#comp_grado_selector').prop('disabled', true);
        $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', true);
        $('#comp_contexto').text('');
        
        limpiarEditor();
        actualizarResumenGlobal();
    };

    function actualizarResumenGlobal() {
        let totalCompetencias = 0;
        let resumenNiveles = {};
        
        Object.keys(competenciasData).forEach(nivel => {
            resumenNiveles[nivel] = 0;
            if (competenciasData[nivel]) {
                Object.keys(competenciasData[nivel]).forEach(grado => {
                    const competencias = competenciasData[nivel][grado];
                    if (Array.isArray(competencias)) {
                        resumenNiveles[nivel] += competencias.length;
                        totalCompetencias += competencias.length;
                    }
                });
            }
        });
        
        let html = `<div class="text-center mb-3">
                        <h5 class="text-primary">${totalCompetencias}</h5>
                        <small class="text-muted">Total Competencias</small>
                    </div>`;
        
        if (Object.keys(resumenNiveles).length > 0) {
            Object.keys(resumenNiveles).forEach(nivel => {
                if (resumenNiveles[nivel] > 0) {
                    html += `<div class="d-flex justify-content-between border-bottom py-1">
                                <span>${nivel.toUpperCase()}</span>
                                <span class="badge bg-info">${resumenNiveles[nivel]}</span>
                            </div>`;
                }
            });
        }
        
        $('#resumen_global').html(html);
    }
});
</script>