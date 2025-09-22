<?php
// modales/horarios/modal_detalle.php
?>
<!-- Modal Detalle Horario -->
<div class="modal fade" id="modalDetalleHorario" tabindex="-1" aria-labelledby="modalDetalleHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetalleHorarioLabel">
                    <i class="ti ti-eye me-2"></i>
                    Detalle de Horario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Información del Docente -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-user me-2"></i>
                                    Información del Docente
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center me-3">
                                                <i class="ti ti-user text-white fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" id="detalle-docente-nombre">Cargando...</h6>
                                                <small class="text-muted" id="detalle-docente-codigo">-</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div id="detalle-tutor-badge" class="d-none">
                                            <span class="badge bg-warning text-dark">
                                                <i class="ti ti-crown me-1"></i>
                                                Tutor
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Asignación -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-book me-2"></i>
                                    Información de Asignación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Área Curricular:</label>
                                        <p class="mb-2" id="detalle-area">-</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Grado y Sección:</label>
                                        <p class="mb-2" id="detalle-seccion">-</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Horas Semanales:</label>
                                        <p class="mb-2">
                                            <span class="badge bg-success" id="detalle-horas">0</span> horas
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nivel Educativo:</label>
                                        <p class="mb-2" id="detalle-nivel">-</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Aula Asignada:</label>
                                        <p class="mb-2" id="detalle-aula-asignada">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Horarios Detallados -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-clock me-2"></i>
                                    Horarios de Clase
                                </h6>
                                <span class="badge bg-primary" id="total-horarios">0 horarios</span>
                            </div>
                            <div class="card-body">
                                <div id="detalle-horarios-container">
                                    <!-- Los horarios se cargarán aquí -->
                                </div>
                                
                                <!-- Mensaje cuando no hay horarios -->
                                <div id="sin-horarios" class="text-center py-4 d-none">
                                    <i class="ti ti-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">No hay horarios configurados</h6>
                                    <p class="text-muted small">Esta asignación aún no tiene horarios establecidos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Semanal -->
                    <div class="col-12 mt-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-chart-bar me-2"></i>
                                    Resumen Semanal
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-primary mb-1" id="resumen-total-horas">0</h5>
                                            <small class="text-muted">Horas Totales</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-success mb-1" id="resumen-dias-semana">0</h5>
                                            <small class="text-muted">Días por Semana</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-warning mb-1" id="resumen-hora-inicio">--:--</h5>
                                            <small class="text-muted">Hora Más Temprana</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h5 class="text-info mb-1" id="resumen-hora-fin">--:--</h5>
                                            <small class="text-muted">Hora Más Tardía</small>
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
                <button type="button" class="btn btn-warning" id="btnEditarDesdeDetalle">
                    <i class="ti ti-edit me-2"></i>
                    Editar Horarios
                </button>
                <button type="button" class="btn btn-success" id="btnExportarDetalle">
                    <i class="ti ti-download me-2"></i>
                    Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let detalleAsignacionActual = null;

$(document).ready(function() {
    // Editar desde detalle
    $('#btnEditarDesdeDetalle').on('click', function() {
        if (detalleAsignacionActual) {
            $('#modalDetalleHorario').modal('hide');
            editarHorarioDocente(detalleAsignacionActual.docente_id);
        }
    });

    // Exportar detalle
    $('#btnExportarDetalle').on('click', function() {
        if (detalleAsignacionActual) {
            exportarDetalleHorario(detalleAsignacionActual.id);
        }
    });
});

function mostrarDetalleHorario(asignacionId) {
    mostrarCarga();
    
    $.ajax({
        url: 'modales/horarios/procesar_horarios.php',
        method: 'POST',
        data: {
            accion: 'detalle',
            id: asignacionId
        },
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            
            if (response.success) {
                cargarDetalleHorario(response.asignacion);
                $('#modalDetalleHorario').modal('show');
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarCarga();
            mostrarError('Error al cargar el detalle del horario');
        }
    });
}

function cargarDetalleHorario(asignacion) {
    detalleAsignacionActual = asignacion;
    
    // Información del docente
    $('#detalle-docente-nombre').text(asignacion.docente_nombres + ' ' + asignacion.docente_apellidos);
    $('#detalle-docente-codigo').text('Código: ' + asignacion.codigo_docente);
    
    // Badge de tutor
    if (asignacion.es_tutor == 1) {
        $('#detalle-tutor-badge').removeClass('d-none');
    } else {
        $('#detalle-tutor-badge').addClass('d-none');
    }
    
    // Información de asignación
    $('#detalle-area').text(asignacion.area_nombre);
    $('#detalle-seccion').text(asignacion.grado + ' ' + asignacion.seccion);
    $('#detalle-horas').text(asignacion.horas_semanales);
    $('#detalle-nivel').text(asignacion.nivel_nombre);
    $('#detalle-aula-asignada').text(asignacion.aula_asignada || 'No asignada');
    
    // Procesar horarios
    const horarios = JSON.parse(asignacion.horarios || '[]');
    
    if (horarios.length > 0) {
        cargarHorariosDetalle(horarios);
        calcularResumenSemanal(horarios);
        $('#sin-horarios').addClass('d-none');
        $('#total-horarios').text(horarios.length + ' horario' + (horarios.length !== 1 ? 's' : ''));
    } else {
        $('#detalle-horarios-container').empty();
        $('#sin-horarios').removeClass('d-none');
        $('#total-horarios').text('0 horarios');
        limpiarResumenSemanal();
    }
}

function cargarHorariosDetalle(horarios) {
    const diasSemana = {
        1: 'Lunes',
        2: 'Martes', 
        3: 'Miércoles',
        4: 'Jueves',
        5: 'Viernes',
        6: 'Sábado'
    };
    
    // Ordenar horarios por día y hora
    horarios.sort((a, b) => {
        if (a.dia !== b.dia) return a.dia - b.dia;
        return a.hora_inicio.localeCompare(b.hora_inicio);
    });
    
    let horariosHtml = '';
    
    horarios.forEach((horario, index) => {
        const duracion = calcularDuracionHorario(horario.hora_inicio, horario.hora_fin);
        
        horariosHtml += `
            <div class="horario-detalle-item border rounded p-3 mb-2 ${index % 2 === 0 ? 'bg-light' : ''}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded bg-primary text-white d-flex align-items-center justify-content-center me-2">
                                ${horario.dia}
                            </div>
                            <div>
                                <h6 class="mb-0">${diasSemana[horario.dia]}</h6>
                                <small class="text-muted">Día ${horario.dia}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6 class="mb-0 text-primary">${horario.hora_inicio}</h6>
                        <small class="text-muted">Hora inicio</small>
                    </div>
                    <div class="col-md-3">
                        <h6 class="mb-0 text-danger">${horario.hora_fin}</h6>
                        <small class="text-muted">Hora fin</small>
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-success">${duracion}</span>
                        <br>
                        <small class="text-muted">${horario.aula || 'Sin aula'}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#detalle-horarios-container').html(horariosHtml);
}

function calcularDuracionHorario(inicio, fin) {
    const inicioTime = new Date('2000-01-01 ' + inicio);
    const finTime = new Date('2000-01-01 ' + fin);
    
    if (finTime > inicioTime) {
        const diff = finTime - inicioTime;
        const horas = Math.floor(diff / (1000 * 60 * 60));
        const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        if (horas > 0 && minutos > 0) {
            return `${horas}h ${minutos}m`;
        } else if (horas > 0) {
            return `${horas}h`;
        } else {
            return `${minutos}m`;
        }
    }
    return '0m';
}

function calcularResumenSemanal(horarios) {
    if (horarios.length === 0) {
        limpiarResumenSemanal();
        return;
    }
    
    // Calcular total de horas
    let totalMinutos = 0;
    const diasUnicos = new Set();
    let horasMasTempranas = [];
    let horasMasTardias = [];
    
    horarios.forEach(horario => {
        const inicioTime = new Date('2000-01-01 ' + horario.hora_inicio);
        const finTime = new Date('2000-01-01 ' + horario.hora_fin);
        
        if (finTime > inicioTime) {
            totalMinutos += (finTime - inicioTime) / (1000 * 60);
        }
        
        diasUnicos.add(horario.dia);
        horasMasTempranas.push(horario.hora_inicio);
        horasMasTardias.push(horario.hora_fin);
    });
    
    const totalHoras = Math.floor(totalMinutos / 60);
    const minutosRestantes = totalMinutos % 60;
    
    // Actualizar resumen
    $('#resumen-total-horas').text(totalHoras + (minutosRestantes > 0 ? '.' + Math.round(minutosRestantes/60*10) : ''));
    $('#resumen-dias-semana').text(diasUnicos.size);
    $('#resumen-hora-inicio').text(Math.min(...horasMasTempranas.map(h => h)));
    $('#resumen-hora-fin').text(Math.max(...horasMasTardias.map(h => h)));
}

function limpiarResumenSemanal() {
    $('#resumen-total-horas').text('0');
    $('#resumen-dias-semana').text('0');
    $('#resumen-hora-inicio').text('--:--');
    $('#resumen-hora-fin').text('--:--');
}

function exportarDetalleHorario(asignacionId) {
    window.open(`reportes/exportar_horario_detalle.php?asignacion_id=${asignacionId}`, '_blank');
}
</script>