<?php
// modales/periodos/modal_evaluaciones.php
?>
<!-- Modal Ver/Editar Evaluaciones -->
<div class="modal fade" id="modalEvaluaciones" tabindex="-1" aria-labelledby="modalEvaluacionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEvaluacionesLabel">
                    <i class="ti ti-calendar-event me-2"></i>
                    Períodos de Evaluación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0" id="periodo-info-titulo">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Información del Período
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Nombre del Período:</small>
                                        <div class="fw-bold" id="eval-periodo-nombre"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Año:</small>
                                        <div class="fw-bold" id="eval-periodo-anio"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Tipo:</small>
                                        <div class="fw-bold" id="eval-periodo-tipo"></div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted">Fecha Inicio:</small>
                                        <div class="fw-bold" id="eval-periodo-inicio"></div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <small class="text-muted">Fecha Fin:</small>
                                        <div class="fw-bold" id="eval-periodo-fin"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="ti ti-list me-2"></i>
                                    Períodos de Evaluación Configurados
                                </h6>
                                <span class="badge bg-primary" id="total-evaluaciones">0 períodos</span>
                            </div>
                            <div class="card-body">
                                <div id="lista-evaluaciones">
                                    <!-- Aquí se cargarán las evaluaciones -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas y Análisis -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="ti ti-chart-bar me-2"></i>
                                    Análisis de Distribución
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-primary mb-1" id="promedio-dias">0</h5>
                                            <small class="text-muted">Promedio de días por período</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-success mb-1" id="periodo-mas-largo">0</h5>
                                            <small class="text-muted">Período más largo (días)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-warning mb-1" id="periodo-mas-corto">0</h5>
                                            <small class="text-muted">Período más corto (días)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-info mb-1" id="total-dias-lectivos">0</h5>
                                            <small class="text-muted">Total días lectivos</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gráfico de línea de tiempo -->
                                <div class="mt-4">
                                    <h6 class="mb-3">Línea de Tiempo de Evaluaciones</h6>
                                    <div id="timeline-evaluaciones" class="position-relative">
                                        <!-- Timeline se generará aquí -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas y Recomendaciones -->
                <div class="row mt-3" id="alertas-evaluaciones" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Recomendaciones:</strong>
                            <ul class="mt-2 mb-0" id="lista-recomendaciones">
                                <!-- Las recomendaciones se generarán aquí -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnExportarEvaluaciones">
                    <i class="ti ti-download me-2"></i>
                    Exportar
                </button>
                <button type="button" class="btn btn-primary" id="btnEditarEvaluaciones">
                    <i class="ti ti-edit me-2"></i>
                    Editar Evaluaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let datosEvaluacionesActual = null;

function mostrarEvaluaciones(periodoId) {
    mostrarCarga();
    
    fetch('modales/periodos/procesar_periodos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_evaluaciones&id=${periodoId}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            cargarDatosEvaluaciones(data.periodo);
            $('#modalEvaluaciones').modal('show');
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al obtener datos de evaluaciones');
    });
}

function cargarDatosEvaluaciones(periodo) {
    datosEvaluacionesActual = periodo;
    
    // Cargar información del período
    $('#eval-periodo-nombre').text(periodo.nombre);
    $('#eval-periodo-anio').text(periodo.anio);
    $('#eval-periodo-tipo').text(periodo.tipo_periodo);
    $('#eval-periodo-inicio').text(formatearFecha(periodo.fecha_inicio));
    $('#eval-periodo-fin').text(formatearFecha(periodo.fecha_fin));

    // Cargar evaluaciones
    const evaluaciones = periodo.periodos_evaluacion || [];
    cargarListaEvaluaciones(evaluaciones);
    
    // Calcular estadísticas
    calcularEstadisticasEvaluaciones(evaluaciones);
    
    // Generar timeline
    generarTimelineEvaluaciones(evaluaciones);
    
    // Generar recomendaciones
    generarRecomendaciones(evaluaciones, periodo);
}

function cargarListaEvaluaciones(evaluaciones) {
    let html = '';
    
    if (evaluaciones && evaluaciones.length > 0) {
        evaluaciones.forEach((eval, index) => {
            const fechaInicio = new Date(eval.fecha_inicio);
            const fechaFin = new Date(eval.fecha_fin);
            const duracion = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
            
            // Calcular estado del período
            const hoy = new Date();
            let estado = '';
            let badgeClass = '';
            
            if (hoy < fechaInicio) {
                estado = 'Próximo';
                badgeClass = 'bg-secondary';
            } else if (hoy >= fechaInicio && hoy <= fechaFin) {
                estado = 'En curso';
                badgeClass = 'bg-success';
            } else {
                estado = 'Finalizado';
                badgeClass = 'bg-info';
            }
            
            html += `
                <div class="card mb-3 evaluacion-item">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1">${eval.nombre}</h6>
                                <small class="text-muted">Período ${eval.numero || index + 1}</small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Fecha Inicio:</small>
                                <div class="fw-bold">${formatearFecha(eval.fecha_inicio)}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Fecha Fin:</small>
                                <div class="fw-bold">${formatearFecha(eval.fecha_fin)}</div>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="badge ${badgeClass} mb-1">${estado}</span>
                                <div class="small text-muted">${duracion} días</div>
                            </div>
                        </div>
                        
                        <!-- Barra de progreso para período en curso -->
                        ${estado === 'En curso' ? generarBarraProgreso(fechaInicio, fechaFin) : ''}
                    </div>
                </div>
            `;
        });
        
        $('#total-evaluaciones').text(`${evaluaciones.length} período${evaluaciones.length !== 1 ? 's' : ''}`);
    } else {
        html = `
            <div class="text-center py-4">
                <i class="ti ti-calendar-off" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2 text-muted">No hay períodos de evaluación configurados</p>
            </div>
        `;
        $('#total-evaluaciones').text('0 períodos');
    }
    
    $('#lista-evaluaciones').html(html);
}

function generarBarraProgreso(fechaInicio, fechaFin) {
    const hoy = new Date();
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    
    const totalDias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
    const diasTranscurridos = Math.ceil((hoy - inicio) / (1000 * 60 * 60 * 24));
    const progreso = Math.min(100, Math.max(0, (diasTranscurridos / totalDias) * 100));
    
    return `
        <div class="mt-3">
            <div class="d-flex justify-content-between small text-muted">
                <span>Progreso del período</span>
                <span>${Math.round(progreso)}% (${diasTranscurridos}/${totalDias} días)</span>
            </div>
            <div class="progress mt-1" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: ${progreso}%" aria-valuenow="${progreso}" 
                     aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    `;
}

function calcularEstadisticasEvaluaciones(evaluaciones) {
    if (!evaluaciones || evaluaciones.length === 0) {
        $('#promedio-dias').text('0');
        $('#periodo-mas-largo').text('0');
        $('#periodo-mas-corto').text('0');
        $('#total-dias-lectivos').text('0');
        return;
    }
    
    let totalDias = 0;
    let diasPorPeriodo = [];
    
    evaluaciones.forEach(eval => {
        const fechaInicio = new Date(eval.fecha_inicio);
        const fechaFin = new Date(eval.fecha_fin);
        const duracion = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
        diasPorPeriodo.push(duracion);
        totalDias += duracion;
    });
    
    const promedio = Math.round(totalDias / evaluaciones.length);
    const masLargo = Math.max(...diasPorPeriodo);
    const masCorto = Math.min(...diasPorPeriodo);
    
    $('#promedio-dias').text(promedio);
    $('#periodo-mas-largo').text(masLargo);
    $('#periodo-mas-corto').text(masCorto);
    $('#total-dias-lectivos').text(totalDias);
}

function generarTimelineEvaluaciones(evaluaciones) {
    let html = '';
    
    if (evaluaciones && evaluaciones.length > 0) {
        html = '<div class="timeline-container">';
        
        evaluaciones.forEach((eval, index) => {
            const fechaInicio = new Date(eval.fecha_inicio);
            const fechaFin = new Date(eval.fecha_fin);
            const hoy = new Date();
            
            let colorClass = 'secondary';
            if (hoy >= fechaInicio && hoy <= fechaFin) {
                colorClass = 'success';
            } else if (hoy > fechaFin) {
                colorClass = 'info';
            }
            
            html += `
                <div class="timeline-item d-flex align-items-center mb-3">
                    <div class="timeline-marker bg-${colorClass} rounded-circle me-3" 
                         style="width: 12px; height: 12px; flex-shrink: 0;"></div>
                    <div class="timeline-content flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${eval.nombre}</strong>
                                <div class="small text-muted">
                                    ${formatearFecha(eval.fecha_inicio)} - ${formatearFecha(eval.fecha_fin)}
                                </div>
                            </div>
                            <span class="badge bg-${colorClass}">
                                ${Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1} días
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    } else {
        html = '<p class="text-muted text-center">No hay períodos para mostrar</p>';
    }
    
    $('#timeline-evaluaciones').html(html);
}

function generarRecomendaciones(evaluaciones, periodo) {
    const recomendaciones = [];
    
    if (!evaluaciones || evaluaciones.length === 0) {
        recomendaciones.push('Configure períodos de evaluación para el año académico');
        $('#alertas-evaluaciones').show();
        $('#lista-recomendaciones').html(recomendaciones.map(r => `<li>${r}</li>`).join(''));
        return;
    }
    
    // Calcular duraciones
    let totalDias = 0;
    let diasPorPeriodo = [];
    
    evaluaciones.forEach(eval => {
        const fechaInicio = new Date(eval.fecha_inicio);
        const fechaFin = new Date(eval.fecha_fin);
        const duracion = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
        diasPorPeriodo.push(duracion);
        totalDias += duracion;
    });
    
    const promedio = totalDias / evaluaciones.length;
    const desviacion = Math.sqrt(diasPorPeriodo.reduce((sum, dias) => sum + Math.pow(dias - promedio, 2), 0) / evaluaciones.length);
    
    // Generar recomendaciones basadas en análisis
    if (desviacion > promedio * 0.2) {
        recomendaciones.push('Los períodos tienen duraciones muy desiguales. Considere redistribuir las fechas.');
    }
    
    if (totalDias < 180) {
        recomendaciones.push('El año académico tiene menos de 180 días lectivos. Verifique si cumple con los requisitos mínimos.');
    }
    
    if (evaluaciones.length < 2) {
        recomendaciones.push('Se recomienda tener al menos 2 períodos de evaluación por año académico.');
    }
    
    // Verificar gaps entre períodos
    for (let i = 0; i < evaluaciones.length - 1; i++) {
        const finActual = new Date(evaluaciones[i].fecha_fin);
        const inicioSiguiente = new Date(evaluaciones[i + 1].fecha_inicio);
        const gap = Math.ceil((inicioSiguiente - finActual) / (1000 * 60 * 60 * 24));
        
        if (gap > 7) {
            recomendaciones.push(`Hay un intervalo de ${gap} días entre "${evaluaciones[i].nombre}" y "${evaluaciones[i + 1].nombre}".`);
        }
    }
    
    if (recomendaciones.length > 0) {
        $('#alertas-evaluaciones').show();
        $('#lista-recomendaciones').html(recomendaciones.map(r => `<li>${r}</li>`).join(''));
    } else {
        $('#alertas-evaluaciones').hide();
    }
}

$(document).ready(function() {
    // Botón para editar evaluaciones
    $('#btnEditarEvaluaciones').on('click', function() {
        if (datosEvaluacionesActual) {
            $('#modalEvaluaciones').modal('hide');
            editarPeriodo(datosEvaluacionesActual.id);
        }
    });

    // Botón para exportar evaluaciones
    $('#btnExportarEvaluaciones').on('click', function() {
        if (datosEvaluacionesActual) {
            exportarEvaluaciones(datosEvaluacionesActual);
        }
    });

    // Limpiar datos al cerrar modal
    $('#modalEvaluaciones').on('hidden.bs.modal', function() {
        datosEvaluacionesActual = null;
        $('#lista-evaluaciones').empty();
        $('#timeline-evaluaciones').empty();
        $('#alertas-evaluaciones').hide();
    });
});

function exportarEvaluaciones(periodo) {
    const data = {
        periodo: periodo.nombre,
        anio: periodo.anio,
        tipo: periodo.tipo_periodo,
        evaluaciones: periodo.periodos_evaluacion || []
    };
    
    // Crear CSV
    let csv = 'Período,Nombre Evaluación,Fecha Inicio,Fecha Fin,Duración (días)\n';
    
    data.evaluaciones.forEach(eval => {
        const fechaInicio = new Date(eval.fecha_inicio);
        const fechaFin = new Date(eval.fecha_fin);
        const duracion = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
        
        csv += `"${data.periodo}","${eval.nombre}","${eval.fecha_inicio}","${eval.fecha_fin}",${duracion}\n`;
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `evaluaciones_${data.periodo.replace(/\s+/g, '_')}_${data.anio}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        title: 'Exportación Completada',
        text: 'El archivo CSV ha sido descargado exitosamente',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

function formatearFecha(fecha) {
    if (!fecha) return 'No disponible';
    return new Date(fecha).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}
</script>

<style>
.evaluacion-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.evaluacion-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.timeline-container {
    max-height: 300px;
    overflow-y: auto;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 20px;
    bottom: -15px;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    z-index: 1;
    position: relative;
}

@media (max-width: 768px) {
    .timeline-container {
        max-height: 200px;
    }
    
    .evaluacion-item .row > div {
        margin-bottom: 0.5rem;
    }
}
</style>