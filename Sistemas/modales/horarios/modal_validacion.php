<?php
// modales/horarios/modal_validacion.php
?>
<!-- Modal Validación de Horarios -->
<div class="modal fade" id="modalValidacionHorarios" tabindex="-1" aria-labelledby="modalValidacionHorariosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalValidacionHorariosLabel">
                    <i class="ti ti-alert-triangle me-2"></i>
                    Validación de Horarios
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Resumen de Validación -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-chart-pie me-2"></i>
                                    Resumen de Validación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-success mb-1" id="total-validos">0</h4>
                                            <small class="text-muted">Válidos</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 border-warning">
                                            <h4 class="text-warning mb-1" id="total-advertencias">0</h4>
                                            <small class="text-muted">Advertencias</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 border-danger">
                                            <h4 class="text-danger mb-1" id="total-errores">0</h4>
                                            <small class="text-muted">Errores</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-info mb-1" id="total-horarios-validados">0</h4>
                                            <small class="text-muted">Total Horarios</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Validación -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-filter me-2"></i>
                                    Filtros de Validación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input filtro-validacion" type="checkbox" 
                                                   id="mostrar-errores" value="error" checked>
                                            <label class="form-check-label text-danger" for="mostrar-errores">
                                                <i class="ti ti-x-circle me-1"></i>
                                                Mostrar Errores
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input filtro-validacion" type="checkbox" 
                                                   id="mostrar-advertencias" value="warning" checked>
                                            <label class="form-check-label text-warning" for="mostrar-advertencias">
                                                <i class="ti ti-alert-triangle me-1"></i>
                                                Mostrar Advertencias
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input filtro-validacion" type="checkbox" 
                                                   id="mostrar-validos" value="success">
                                            <label class="form-check-label text-success" for="mostrar-validos">
                                                <i class="ti ti-check-circle me-1"></i>
                                                Mostrar Válidos
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados de Validación -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-list-check me-2"></i>
                                    Resultados de Validación
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnRevalidar">
                                    <i class="ti ti-refresh me-1"></i>
                                    Re-validar
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="resultados-validacion">
                                    <!-- Los resultados se cargarán aquí -->
                                </div>
                                
                                <!-- Loading state -->
                                <div id="validacion-loading" class="text-center py-4 d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Validando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Validando horarios...</p>
                                </div>
                                
                                <!-- Sin resultados -->
                                <div id="sin-resultados" class="text-center py-4 d-none">
                                    <i class="ti ti-check-circle text-success" style="font-size: 3rem;"></i>
                                    <h6 class="text-success mt-2">¡Todo está en orden!</h6>
                                    <p class="text-muted small">No se encontraron conflictos en los horarios</p>
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
                <button type="button" class="btn btn-info" id="btnExportarValidacion">
                    <i class="ti ti-file-export me-2"></i>
                    Exportar Reporte
                </button>
                <button type="button" class="btn btn-warning" id="btnCorregirTodos">
                    <i class="ti ti-tool me-2"></i>
                    Corregir Automáticamente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let resultadosValidacionActuales = [];

$(document).ready(function() {
    // Filtros de validación
    $('.filtro-validacion').on('change', function() {
        aplicarFiltrosValidacion();
    });

    // Re-validar
    $('#btnRevalidar').on('click', function() {
        ejecutarValidacionCompleta();
    });

    // Exportar validación
    $('#btnExportarValidacion').on('click', function() {
        exportarReporteValidacion();
    });

    // Corregir automáticamente
    $('#btnCorregirTodos').on('click', function() {
        corregirAutomaticamente();
    });
});

function mostrarValidacionHorarios(tipo = 'completa', docente_id = null) {
    $('#modalValidacionHorarios').modal('show');
    
    // Determinar qué validación ejecutar
    if (tipo === 'completa') {
        ejecutarValidacionCompleta();
    } else if (tipo === 'docente' && docente_id) {
        ejecutarValidacionDocente(docente_id);
    }
}

function ejecutarValidacionCompleta() {
    mostrarLoadingValidacion();
    
    $.ajax({
        url: 'modales/horarios/procesar_horarios.php',
        method: 'POST',
        data: {
            accion: 'validar_todos'
        },
        dataType: 'json',
        success: function(response) {
            ocultarLoadingValidacion();
            
            if (response.success) {
                resultadosValidacionActuales = response.validaciones;
                mostrarResultadosValidacion(response.validaciones);
                actualizarResumenValidacion(response.resumen);
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarLoadingValidacion();
            mostrarError('Error al ejecutar la validación');
        }
    });
}

function ejecutarValidacionDocente(docenteId) {
    mostrarLoadingValidacion();
    
    $.ajax({
        url: 'modales/horarios/procesar_horarios.php',
        method: 'POST',
        data: {
            accion: 'validar_docente',
            docente_id: docenteId
        },
        dataType: 'json',
        success: function(response) {
            ocultarLoadingValidacion();
            
            if (response.success) {
                resultadosValidacionActuales = response.validaciones;
                mostrarResultadosValidacion(response.validaciones);
                actualizarResumenValidacion(response.resumen);
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarLoadingValidacion();
            mostrarError('Error al validar el docente');
        }
    });
}

function mostrarLoadingValidacion() {
    $('#resultados-validacion').addClass('d-none');
    $('#sin-resultados').addClass('d-none');
    $('#validacion-loading').removeClass('d-none');
}

function ocultarLoadingValidacion() {
    $('#validacion-loading').addClass('d-none');
}

function mostrarResultadosValidacion(validaciones) {
    if (validaciones.length === 0) {
        $('#resultados-validacion').addClass('d-none');
        $('#sin-resultados').removeClass('d-none');
        return;
    }
    
    $('#sin-resultados').addClass('d-none');
    $('#resultados-validacion').removeClass('d-none');
    
    let html = '';
    
    validaciones.forEach((validacion, index) => {
        const iconos = {
            'error': 'ti-x-circle text-danger',
            'warning': 'ti-alert-triangle text-warning',
            'success': 'ti-check-circle text-success'
        };
        
        const colores = {
            'error': 'border-danger',
            'warning': 'border-warning', 
            'success': 'border-success'
        };
        
        html += `
            <div class="validacion-item ${colores[validacion.tipo]} border rounded p-3 mb-2" 
                 data-tipo="${validacion.tipo}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="ti ${iconos[validacion.tipo]} fs-4"></i>
                    </div>
                    <div class="col">
                        <h6 class="mb-1">${validacion.titulo}</h6>
                        <p class="mb-1 text-muted">${validacion.descripcion}</p>
                        ${validacion.detalles ? `<small class="text-muted">${validacion.detalles}</small>` : ''}
                        ${validacion.afectados ? `
                            <div class="mt-2">
                                <small class="text-muted">Afecta a: </small>
                                ${validacion.afectados.map(afectado => `
                                    <span class="badge bg-secondary me-1">${afectado}</span>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                    <div class="col-auto">
                        ${validacion.tipo === 'error' ? `
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="corregirValidacion(${index})">
                                <i class="ti ti-tool me-1"></i>
                                Corregir
                            </button>
                        ` : ''}
                        ${validacion.docente_id ? `
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="verDocenteValidacion(${validacion.docente_id})">
                                <i class="ti ti-eye me-1"></i>
                                Ver
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#resultados-validacion').html(html);
    aplicarFiltrosValidacion();
}

function actualizarResumenValidacion(resumen) {
    $('#total-validos').text(resumen.validos || 0);
    $('#total-advertencias').text(resumen.advertencias || 0);
    $('#total-errores').text(resumen.errores || 0);
    $('#total-horarios-validados').text(resumen.total || 0);
}

function aplicarFiltrosValidacion() {
    const filtrosActivos = [];
    $('.filtro-validacion:checked').each(function() {
        filtrosActivos.push($(this).val());
    });
    
    $('.validacion-item').each(function() {
        const tipo = $(this).data('tipo');
        if (filtrosActivos.includes(tipo)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    // Mostrar mensaje si no hay elementos visibles
    const visibles = $('.validacion-item:visible').length;
    if (visibles === 0 && resultadosValidacionActuales.length > 0) {
        $('#resultados-validacion').html(`
            <div class="text-center py-4">
                <i class="ti ti-filter-off text-muted" style="font-size: 3rem;"></i>
                <h6 class="text-muted mt-2">No hay resultados con los filtros aplicados</h6>
                <p class="text-muted small">Modifica los filtros para ver más resultados</p>
            </div>
        `);
    }
}

function corregirValidacion(index) {
    const validacion = resultadosValidacionActuales[index];
    
    Swal.fire({
        title: '¿Corregir esta validación?',
        text: 'Se intentará corregir automáticamente este conflicto',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, corregir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarCorreccion(validacion);
        }
    });
}

function corregirAutomaticamente() {
    const errores = resultadosValidacionActuales.filter(v => v.tipo === 'error');
    
    if (errores.length === 0) {
        Swal.fire({
            title: 'No hay errores',
            text: 'No se encontraron errores que requieran corrección automática',
            icon: 'info'
        });
        return;
    }
    
    Swal.fire({
        title: `¿Corregir ${errores.length} error(es)?`,
        text: 'Se intentará corregir automáticamente todos los errores encontrados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, corregir todos',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarCorreccionMasiva();
        }
    });
}

function ejecutarCorreccion(validacion) {
    mostrarCarga();
    
    $.ajax({
        url: 'modales/horarios/procesar_horarios.php',
        method: 'POST',
        data: {
            accion: 'corregir_validacion',
            validacion: JSON.stringify(validacion)
        },
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            
            if (response.success) {
                Swal.fire({
                    title: '¡Corregido!',
                    text: response.message,
                    icon: 'success'
                }).then(() => {
                    ejecutarValidacionCompleta();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message,
                    icon: 'error'
                });
            }
        },
        error: function() {
            ocultarCarga();
            mostrarError('Error al corregir la validación');
        }
    });
}

function ejecutarCorreccionMasiva() {
    mostrarCarga();
    
    $.ajax({
        url: 'modales/horarios/procesar_horarios.php',
        method: 'POST',
        data: {
            accion: 'corregir_masivo'
        },
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            
            if (response.success) {
                Swal.fire({
                    title: '¡Correcciones Aplicadas!',
                    html: `
                        <p>Resultados de la corrección masiva:</p>
                        <ul class="text-start">
                            <li>Corregidos: <strong>${response.corregidos}</strong></li>
                            <li>Sin cambios: <strong>${response.sin_cambios}</strong></li>
                            <li>Errores: <strong>${response.errores}</strong></li>
                        </ul>
                    `,
                    icon: 'success'
                }).then(() => {
                    ejecutarValidacionCompleta();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message,
                    icon: 'error'
                });
            }
        },
        error: function() {
            ocultarCarga();
            mostrarError('Error en la corrección masiva');
        }
    });
}

function verDocenteValidacion(docenteId) {
    $('#modalValidacionHorarios').modal('hide');
    verHorarioDocente(docenteId);
}

function exportarReporteValidacion() {
    if (resultadosValidacionActuales.length === 0) {
        Swal.fire({
            title: 'Sin datos',
            text: 'No hay resultados de validación para exportar',
            icon: 'info'
        });
        return;
    }
    
    window.open('reportes/exportar_validacion_horarios.php', '_blank');
}
</script>