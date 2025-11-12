<!-- Modal Gestión de Competencias -->
<div class="modal fade" id="modalGestionCompetencias" tabindex="-1" aria-labelledby="modalGestionCompetenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Header simplificado -->
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem;">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <h5 class="modal-title mb-0" id="modalGestionCompetenciasLabel" style="font-weight: 600; color: #1a5f7a;">
                        <span id="comp_area_nombre"></span>
                    </h5>
                    <span id="comp_total_badge" class="badge" style="background: #f3f4f6; color: #6b7280; font-weight: 500; font-size: 0.813rem;">0 competencias</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formGestionCompetencias" method="POST">
                <input type="hidden" id="comp_area_id" name="area_id">
                <input type="hidden" id="comp_area_codigo" name="area_codigo">
                
                <div class="modal-body" style="padding: 0; background: #f9fafb;">
                    <!-- Barra de herramientas superior -->
                    <div style="background: white; border-bottom: 1px solid #e5e7eb; padding: 0.875rem 1.5rem;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <!-- Navegación Nivel/Grado -->
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm" id="comp_nivel_selector" style="border: 1px solid #d1d5db; min-width: 140px; font-size: 0.875rem;">
                                    <option value="">Nivel</option>
                                    <?php foreach ($niveles as $nivel): ?>
                                        <option value="<?= strtolower($nivel['nombre']) ?>" data-id="<?= $nivel['id'] ?>">
                                            <?= htmlspecialchars($nivel['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <span style="color: #d1d5db;">/</span>
                                
                                <select class="form-select form-select-sm" id="comp_grado_selector" disabled style="border: 1px solid #d1d5db; min-width: 120px; font-size: 0.875rem;">
                                    <option value="">Grado</option>
                                </select>
                            </div>
                            
                            <div style="height: 20px; width: 1px; background: #e5e7eb;"></div>
                            
                            <!-- Acciones primarias -->
                            <button type="button" class="btn btn-sm" onclick="agregarCompetencia()" disabled id="btnAgregarCompetencia"
                                    style="background: #1a5f7a; color: white; border: none; padding: 0.375rem 0.875rem; font-size: 0.875rem; font-weight: 500;">
                                + Nueva competencia
                            </button>
                            
                            <button type="button" class="btn btn-sm" onclick="cargarCompetenciasPredefinidas()" disabled id="btnCargarPredefinidas"
                                    style="background: white; color: #374151; border: 1px solid #d1d5db; padding: 0.375rem 0.875rem; font-size: 0.875rem; font-weight: 500;">
                                Cargar predefinidas
                            </button>
                            
                            <!-- Acciones secundarias (menú) -->
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm" onclick="validarCompetencias()"
                                        style="background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                    <i class="ti ti-check" style="font-size: 1rem;"></i>
                                </button>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                            style="background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                                        <i class="ti ti-dots" style="font-size: 1rem;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
                                        <li><a class="dropdown-item" href="#" onclick="copiarCompetenciasGrado(); return false;">
                                            <i class="ti ti-copy me-2"></i>Copiar a otros grados
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="previewCompetencias(); return false;">
                                            <i class="ti ti-eye me-2"></i>Vista previa
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportarCompetencias(); return false;">
                                            <i class="ti ti-file-export me-2"></i>Exportar
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="verResumenGlobal(); return false;">
                                            <i class="ti ti-chart-bar me-2"></i>Ver resumen
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área de editor -->
                    <div style="max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem;">
                        <div id="competencias_editor">
                            <!-- Estado vacío -->
                            <div class="text-center" style="padding: 4rem 2rem; color: #9ca3af;">
                                <i class="ti ti-target" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                                <p style="font-size: 0.938rem; margin: 0;">Selecciona un nivel y grado para comenzar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer simplificado -->
                <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 0.875rem 1.5rem; background: white;">
                    <button type="button" class="btn" data-bs-dismiss="modal"
                            style="background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" id="btnGuardarCompetencias"
                            style="background: #1a5f7a; color: white; border: none; padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 500;">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para competencia (minimalista) -->
<template id="templateCompetencia">
    <div class="competencia-item" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; transition: all 0.2s;">
        <!-- Header de competencia -->
        <div class="d-flex align-items-start gap-3 mb-3">
            <span class="drag-handle" style="color: #d1d5db; cursor: grab; padding-top: 0.25rem;">
                <i class="ti ti-grip-vertical" style="font-size: 1.125rem;"></i>
            </span>
            
            <div class="flex-grow-1">
                <textarea class="form-control competencia-texto" name="competencias[]" 
                          rows="2" placeholder="Describe la competencia..." required
                          style="border: none; padding: 0; resize: none; font-size: 0.938rem; line-height: 1.5; color: #111827; background: transparent;"
                          oninput="autoResize(this)"></textarea>
            </div>
            
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn-icon toggle-detalles" onclick="toggleDetalles(this)" 
                        style="background: none; border: none; color: #9ca3af; padding: 0.25rem; cursor: pointer; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151';"
                        onmouseout="this.style.background='none'; this.style.color='#9ca3af';">
                    <i class="ti ti-chevron-down" style="font-size: 1rem; transition: transform 0.2s;"></i>
                </button>
                <button type="button" class="btn-icon eliminar-competencia" 
                        style="background: none; border: none; color: #9ca3af; padding: 0.25rem; cursor: pointer; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;"
                        onmouseover="this.style.background='#fee2e2'; this.style.color='#dc2626';"
                        onmouseout="this.style.background='none'; this.style.color='#9ca3af';">
                    <i class="ti ti-trash" style="font-size: 1rem;"></i>
                </button>
            </div>
        </div>
        
        <!-- Detalles expandibles (ocultos por defecto) -->
        <div class="competencia-detalles" style="display: none; padding-left: 2.25rem; border-top: 1px solid #f3f4f6; padding-top: 1rem; margin-top: 0.5rem;">
            <div class="mb-3">
                <label style="font-size: 0.813rem; color: #6b7280; font-weight: 500; margin-bottom: 0.375rem; display: block;">Capacidades</label>
                <textarea class="form-control capacidades-texto" name="capacidades[]" 
                          rows="2" placeholder="Describe las capacidades específicas..."
                          style="border: 1px solid #e5e7eb; font-size: 0.875rem; border-radius: 6px; padding: 0.5rem 0.75rem;"
                          oninput="autoResize(this)"></textarea>
            </div>
            <div>
                <label style="font-size: 0.813rem; color: #6b7280; font-weight: 500; margin-bottom: 0.375rem; display: block;">Estándares</label>
                <textarea class="form-control estandares-texto" name="estandares[]" 
                          rows="2" placeholder="Define los estándares de aprendizaje..."
                          style="border: 1px solid #e5e7eb; font-size: 0.875rem; border-radius: 6px; padding: 0.5rem 0.75rem;"
                          oninput="autoResize(this)"></textarea>
            </div>
        </div>
    </div>
</template>

<!-- Modal Preview simplificado -->
<div class="modal fade" id="modalPreviewCompetencias" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 600; color: #1a5f7a;">Vista Previa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div id="preview_content"></div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 0.875rem 1.5rem;">
                <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.5rem 1rem; font-size: 0.875rem;">
                    Cerrar
                </button>
                <button type="button" class="btn" onclick="imprimirPreview()"
                        style="background: #1a5f7a; color: white; border: none; padding: 0.5rem 1.25rem; font-size: 0.875rem;">
                    <i class="ti ti-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resumen Global -->
<div class="modal fade" id="modalResumenGlobal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding: 1rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 600; color: #1a5f7a;">Resumen Global</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div id="resumen_global_content"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para interacciones */
.competencia-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
}

.competencia-item.dragging {
    opacity: 0.5;
}

.competencia-texto:focus,
.capacidades-texto:focus,
.estandares-texto:focus {
    outline: none;
    border-color: #1a5f7a !important;
    box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1) !important;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    transition: all 0.15s;
}

.dropdown-item:hover {
    background: #f3f4f6;
    color: #1a5f7a;
}

.drag-handle:active {
    cursor: grabbing;
}

/* Animación para detalles */
.competencia-detalles {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Auto-resize para textareas
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Toggle detalles de competencia
function toggleDetalles(btn) {
    const item = btn.closest('.competencia-item');
    const detalles = item.querySelector('.competencia-detalles');
    const icon = btn.querySelector('i');
    
    if (detalles.style.display === 'none') {
        detalles.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        detalles.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

// Inicializar
if (typeof window.competenciasData === 'undefined') {
    window.competenciasData = {};
}

$(document).ready(function() {
    let competenciasData = window.competenciasData;
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
            cargarCompetenciasGrado(nivel, grado);
        } else {
            $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', true);
            limpiarEditor();
        }
    });

    // Eliminar competencia
    $(document).on('click', '.eliminar-competencia', function() {
        const item = $(this).closest('.competencia-item');
        
        Swal.fire({
            title: '¿Eliminar competencia?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                item.fadeOut(200, function() {
                    $(this).remove();
                    actualizarContador();
                });
            }
        });
    });

    // Envío del formulario
    $('#formGestionCompetencias').on('submit', function(e) {
        e.preventDefault();
        guardarCompetenciasCompleto();
    });

    function cargarGradosNivel(nivel) {
        $('#comp_grado_selector').empty().append('<option value="">Grado</option>');
        
        if (nivel && competenciasBase[nivel]) {
            const grados = competenciasBase[nivel].grados;
            grados.forEach(function(grado) {
                $('#comp_grado_selector').append(`<option value="${grado}">${grado}</option>`);
            });
        }
    }

    function limpiarEditor() {
        $('#competencias_editor').html(`
            <div class="text-center" style="padding: 4rem 2rem; color: #9ca3af;">
                <i class="ti ti-target" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                <p style="font-size: 0.938rem; margin: 0;">Selecciona un nivel y grado para comenzar</p>
            </div>
        `);
    }

    function cargarCompetenciasGrado(nivel, grado) {
        if (!window.competenciasData[nivel]) window.competenciasData[nivel] = {};
        if (!window.competenciasData[nivel][grado]) window.competenciasData[nivel][grado] = [];

        mostrarCompetenciasEditor(window.competenciasData[nivel][grado]);
    }

    function mostrarCompetenciasEditor(competencias) {
        $('#competencias_editor').empty();
        competenciaIndex = 0;

        if (competencias.length === 0) {
            $('#competencias_editor').html(`
                <div class="text-center" style="padding: 3rem 2rem; background: white; border: 2px dashed #e5e7eb; border-radius: 8px;">
                    <i class="ti ti-target" style="font-size: 2.5rem; color: #d1d5db; display: block; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280; margin-bottom: 1rem; font-size: 0.938rem;">No hay competencias definidas</p>
                    <button type="button" class="btn" onclick="agregarCompetencia()"
                            style="background: #1a5f7a; color: white; border: none; padding: 0.5rem 1.25rem; font-size: 0.875rem;">
                        + Agregar primera competencia
                    </button>
                </div>
            `);
        } else {
            competencias.forEach(function(competencia) {
                agregarCompetenciaHTML(competencia);
            });
        }
        
        actualizarContador();
    }

    window.agregarCompetencia = function(competenciaData = null) {
        const template = $('#templateCompetencia').html();
        $('#competencias_editor').append(template);
        
        const nuevaCompetencia = $('.competencia-item').last();
        
        if (competenciaData) {
            nuevaCompetencia.find('.competencia-texto').val(competenciaData.texto || '');
            nuevaCompetencia.find('.capacidades-texto').val(competenciaData.capacidades || '');
            nuevaCompetencia.find('.estandares-texto').val(competenciaData.estandares || '');
            
            // Auto-expandir si tiene capacidades o estándares
            if (competenciaData.capacidades || competenciaData.estandares) {
                nuevaCompetencia.find('.competencia-detalles').show();
                nuevaCompetencia.find('.toggle-detalles i').css('transform', 'rotate(180deg)');
            }
        }
        
        competenciaIndex++;
        actualizarContador();
        
        // Focus y auto-resize
        const textarea = nuevaCompetencia.find('.competencia-texto')[0];
        textarea.focus();
        autoResize(textarea);
        
        // Scroll suave al nuevo elemento
        nuevaCompetencia[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    function agregarCompetenciaHTML(competenciaData) {
        agregarCompetencia(competenciaData);
    }

    function actualizarContador() {
        const total = $('.competencia-item').length;
        $('#comp_total_badge').text(`${total} competencia${total !== 1 ? 's' : ''}`);
    }

    window.cargarCompetenciasPredefinidas = function() {
        const codigo = $('#comp_area_codigo').val();
        
        if (codigo && competenciasPredefinidas[codigo]) {
            const competencias = competenciasPredefinidas[codigo];
            
            Swal.fire({
                title: 'Cargar competencias predefinidas',
                html: `<p style="color: #6b7280; font-size: 0.938rem;">Se cargarán ${competencias.length} competencias predefinidas para ${codigo}</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a5f7a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Cargar',
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
        
        mostrarModalCopiarCompetencias(nivelActual, gradoActual, competenciasActuales);
    };

    function mostrarModalCopiarCompetencias(nivelOrigen, gradoOrigen, competencias) {
        let opcionesHtml = '<div style="max-height: 300px; overflow-y: auto; text-align: left;">';
        
        Object.keys(competenciasBase).forEach(nivel => {
            if (competenciasBase[nivel].grados) {
                competenciasBase[nivel].grados.forEach(grado => {
                    if (!(nivel === nivelOrigen && grado === gradoOrigen)) {
                        opcionesHtml += `
                            <div class="form-check" style="padding: 0.5rem 0;">
                                <input class="form-check-input" type="checkbox" value="${nivel}|${grado}" id="copy_${nivel}_${grado}">
                                <label class="form-check-label" for="copy_${nivel}_${grado}" style="font-size: 0.875rem;">
                                    ${nivel.toUpperCase()} - ${grado}
                                </label>
                            </div>
                        `;
                    }
                });
            }
        });
        
        opcionesHtml += '</div>';
        
        Swal.fire({
            title: 'Copiar competencias',
            html: `<p style="color: #6b7280; font-size: 0.938rem; margin-bottom: 1rem;">Selecciona los grados destino:</p>${opcionesHtml}`,
            showCancelButton: true,
            confirmButtonText: 'Copiar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#1a5f7a',
            cancelButtonColor: '#6b7280',
            width: '500px',
            preConfirm: () => {
                const seleccionados = [];
                $('input[id^="copy_"]:checked').each(function() {
                    seleccionados.push($(this).val());
                });
                
                if (seleccionados.length === 0) {
                    Swal.showValidationMessage('Selecciona al menos un grado');
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
            
            if (!window.competenciasData[nivel]) window.competenciasData[nivel] = {};
            if (!window.competenciasData[nivel][grado]) window.competenciasData[nivel][grado] = [];
            
            competencias.forEach(comp => {
                const existe = window.competenciasData[nivel][grado].some(existing => 
                    existing.texto.trim().toLowerCase() === comp.texto.trim().toLowerCase()
                );
                
                if (!existe) {
                    window.competenciasData[nivel][grado].push({...comp});
                }
            });
        });
        
        mostrarExito(`Competencias copiadas a ${destinos.length} grado${destinos.length !== 1 ? 's' : ''}`);
    }

    function guardarCompetenciasCompleto() {
        window.recopilarCompetenciasActuales();
        
        mostrarCarga();
        $('#btnGuardarCompetencias').prop('disabled', true);
        
        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: {
                accion: 'guardar_competencias',
                area_id: $('#comp_area_id').val(),
                competencias: JSON.stringify(window.competenciasData)
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
        window.recopilarCompetenciasActuales();
        generarPreviewCompetencias();
        $('#modalPreviewCompetencias').modal('show');
    };

    function generarPreviewCompetencias() {
        let html = `<h4 style="color: #1a5f7a; font-weight: 600; margin-bottom: 1.5rem;">${$('#comp_area_nombre').text()}</h4>`;
        
        Object.keys(window.competenciasData).forEach(nivel => {
            if (window.competenciasData[nivel] && Object.keys(window.competenciasData[nivel]).length > 0) {
                html += `<h5 style="color: #374151; font-weight: 600; margin-top: 2rem; margin-bottom: 1rem;">${nivel.toUpperCase()}</h5>`;
                
                Object.keys(window.competenciasData[nivel]).forEach(grado => {
                    const competencias = window.competenciasData[nivel][grado];
                    if (competencias.length > 0) {
                        html += `<h6 style="color: #6b7280; font-weight: 500; margin-top: 1.5rem; margin-bottom: 0.75rem;">${grado}</h6>`;
                        html += '<ol style="padding-left: 1.5rem;">';
                        
                        competencias.forEach(comp => {
                            html += `<li style="margin-bottom: 1rem; line-height: 1.6;"><strong>${comp.texto}</strong>`;
                            if (comp.capacidades) {
                                html += `<br><small style="color: #6b7280;"><strong>Capacidades:</strong> ${comp.capacidades}</small>`;
                            }
                            if (comp.estandares) {
                                html += `<br><small style="color: #6b7280;"><strong>Estándares:</strong> ${comp.estandares}</small>`;
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

    window.cargarGestionCompetencias = function(area) {
        $('#comp_area_id').val(area.id);
        $('#comp_area_nombre').text(area.nombre);
        $('#comp_area_codigo').val(area.codigo);
        
        if (area.competencias) {
            try {
                window.competenciasData = JSON.parse(area.competencias) || {};
            } catch (e) {
                window.competenciasData = {};
            }
        } else {
            window.competenciasData = {};
        }
        
        $('#comp_nivel_selector, #comp_grado_selector').val('');
        $('#comp_grado_selector').prop('disabled', true);
        $('#btnCargarPredefinidas, #btnAgregarCompetencia').prop('disabled', true);
        
        limpiarEditor();
        actualizarContador();
    };

    window.verResumenGlobal = function() {
        generarResumenGlobal();
        $('#modalResumenGlobal').modal('show');
    };

    function generarResumenGlobal() {
        let totalCompetencias = 0;
        let resumenNiveles = {};
        
        Object.keys(window.competenciasData).forEach(nivel => {
            resumenNiveles[nivel] = 0;
            if (window.competenciasData[nivel]) {
                Object.keys(window.competenciasData[nivel]).forEach(grado => {
                    const competencias = window.competenciasData[nivel][grado];
                    if (Array.isArray(competencias)) {
                        resumenNiveles[nivel] += competencias.length;
                        totalCompetencias += competencias.length;
                    }
                });
            }
        });
        
        let html = `
            <div style="text-align: center; padding: 1.5rem; background: #f9fafb; border-radius: 8px; margin-bottom: 1.5rem;">
                <div style="font-size: 2.5rem; font-weight: 700; color: #1a5f7a; margin-bottom: 0.25rem;">${totalCompetencias}</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Total de competencias</div>
            </div>
        `;
        
        if (Object.keys(resumenNiveles).length > 0) {
            Object.keys(resumenNiveles).forEach(nivel => {
                if (resumenNiveles[nivel] > 0) {
                    html += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-weight: 500; color: #374151;">${nivel.toUpperCase()}</span>
                            <span style="background: #1a5f7a; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.813rem; font-weight: 500;">${resumenNiveles[nivel]}</span>
                        </div>
                    `;
                }
            });
        }
        
        $('#resumen_global_content').html(html);
    }
});
</script>

<!-- Mantener funciones globales existentes -->
<script>
if (typeof window.competenciasData === 'undefined') {
    window.competenciasData = {};
}

window.recopilarCompetenciasActuales = function() {
    const nivel = $('#comp_nivel_selector').val();
    const grado = $('#comp_grado_selector').val();
    
    if (!nivel || !grado) return;
    
    if (!window.competenciasData) window.competenciasData = {};
    if (!window.competenciasData[nivel]) window.competenciasData[nivel] = {};
    window.competenciasData[nivel][grado] = [];
    
    $('.competencia-item').each(function() {
        const competencia = {
            texto: $(this).find('.competencia-texto').val().trim(),
            capacidades: $(this).find('.capacidades-texto').val().trim(),
            estandares: $(this).find('.estandares-texto').val().trim()
        };
        
        if (competencia.texto) {
            window.competenciasData[nivel][grado].push(competencia);
        }
    });
};

window.exportarCompetencias = function() {
    window.recopilarCompetenciasActuales();
    
    if (!window.competenciasData || typeof window.competenciasData !== 'object') {
        window.competenciasData = {};
    }
    
    let totalCompetencias = 0;
    Object.keys(window.competenciasData).forEach(nivel => {
        if (window.competenciasData[nivel]) {
            Object.keys(window.competenciasData[nivel]).forEach(grado => {
                if (Array.isArray(window.competenciasData[nivel][grado])) {
                    totalCompetencias += window.competenciasData[nivel][grado].length;
                }
            });
        }
    });
    
    if (totalCompetencias === 0) {
        Swal.fire({
            title: 'Sin datos',
            text: 'No hay competencias para exportar',
            icon: 'warning',
            confirmButtonColor: '#1a5f7a'
        });
        return;
    }
    
    const datosExportacion = {
        area_id: $('#comp_area_id').val(),
        area_nombre: $('#comp_area_nombre').text(),
        area_codigo: $('#comp_area_codigo').val(),
        competencias: window.competenciasData,
        fecha_exportacion: new Date().toISOString()
    };
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'reportes/exportar_competencias.php';
    form.target = '_blank';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'datosCompetencias';
    input.value = JSON.stringify(datosExportacion);
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    mostrarExito('Generando exportación...');
};

window.imprimirPreview = function() {
    const contenidoImprimir = document.getElementById('preview_content').innerHTML;
    const areaNombre = $('#comp_area_nombre').text();
    
    const ventanaImpresion = window.open('', '_blank', 'width=800,height=600');
    
    ventanaImpresion.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Competencias - ${areaNombre}</title>
            <style>
                @page { size: A4; margin: 2cm; }
                body { font-family: 'Segoe UI', sans-serif; line-height: 1.6; color: #333; padding: 20px; }
                h4 { color: #1a5f7a; border-bottom: 2px solid #1a5f7a; padding-bottom: 10px; }
                h5 { color: #374151; margin-top: 30px; }
                h6 { color: #6b7280; margin-top: 20px; }
                li { margin-bottom: 12px; page-break-inside: avoid; }
                small { color: #6b7280; display: block; margin-top: 5px; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            ${contenidoImprimir}
            <script>window.onload = function() { window.print(); };<\/script>
        </body>
        </html>
    `);
    
    ventanaImpresion.document.close();
};

window.validarCompetencias = function() {
    window.recopilarCompetenciasActuales();
    
    if (!window.competenciasData || typeof window.competenciasData !== 'object') {
        window.competenciasData = {};
    }
    
    const errores = [];
    const advertencias = [];
    let totalCompetencias = 0;
    
    Object.keys(window.competenciasData).forEach(nivel => {
        if (window.competenciasData[nivel]) {
            Object.keys(window.competenciasData[nivel]).forEach(grado => {
                const competencias = window.competenciasData[nivel][grado];
                
                if (Array.isArray(competencias)) {
                    totalCompetencias += competencias.length;
                    
                    competencias.forEach((comp, index) => {
                        if (!comp.texto || comp.texto.trim().length < 10) {
                            errores.push(`${nivel} - ${grado}: Competencia ${index + 1} muy corta`);
                        }
                        
                        if (!comp.capacidades || comp.capacidades.trim().length === 0) {
                            advertencias.push(`${nivel} - ${grado}: Competencia ${index + 1} sin capacidades`);
                        }
                        
                        if (!comp.estandares || comp.estandares.trim().length === 0) {
                            advertencias.push(`${nivel} - ${grado}: Competencia ${index + 1} sin estándares`);
                        }
                    });
                }
            });
        }
    });
    
    let html = '<div style="text-align: left; font-size: 0.875rem;">';
    
    if (totalCompetencias === 0) {
        html += '<div style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">⚠️ No hay competencias definidas</div>';
    } else {
        html += `<div style="background: #dbeafe; color: #1e40af; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">ℹ️ Total: <strong>${totalCompetencias}</strong> competencias</div>`;
    }
    
    if (errores.length > 0) {
        html += `<div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <strong>❌ Errores (${errores.length})</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">`;
        errores.forEach(error => html += `<li>${error}</li>`);
        html += `</ul></div>`;
    }
    
    if (advertencias.length > 0) {
        html += `<div style="background: #fef3c7; color: #92400e; padding: 1rem; border-radius: 6px;">
                    <strong>⚠️ Advertencias (${advertencias.length})</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">`;
        advertencias.slice(0, 5).forEach(adv => html += `<li>${adv}</li>`);
        if (advertencias.length > 5) html += `<li><em>... y ${advertencias.length - 5} más</em></li>`;
        html += `</ul></div>`;
    }
    
    if (errores.length === 0 && advertencias.length === 0 && totalCompetencias > 0) {
        html += '<div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px;">✓ Todas las competencias están correctamente definidas</div>';
    }
    
    html += '</div>';
    
    Swal.fire({
        title: 'Validación',
        html: html,
        icon: errores.length > 0 ? 'error' : (advertencias.length > 0 ? 'warning' : 'success'),
        width: '600px',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#1a5f7a'
    });
};
</script>