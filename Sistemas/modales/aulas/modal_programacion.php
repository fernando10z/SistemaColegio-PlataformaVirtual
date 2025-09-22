<?php
// modales/aulas/modal_programacion.php
?>

<!-- Modal Programación de Aula -->
<div class="modal fade" id="modalProgramacionAula" tabindex="-1" aria-labelledby="modalProgramacionAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalProgramacionAulaLabel">
                    <i class="ti ti-calendar-time me-2"></i>
                    Programación de Aula
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Información del Aula -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1" id="prog_aula_nombre">Cargando...</h6>
                                <small class="text-muted" id="prog_aula_detalles">Información del aula</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Capacidad</small>
                                        <strong id="prog_capacidad">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Ocupación</small>
                                        <strong id="prog_ocupacion">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horario Semanal -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Horario Semanal</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Hora</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_horario">
                                    <!-- Horarios generados dinámicamente -->
                                    <tr>
                                        <td class="fw-bold">8:00 - 8:45</td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-success">
                                            <div class="slot-content">
                                                <strong>Ciencias</strong><br>
                                                <small>Prof. José Herrera</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">8:45 - 9:30</td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-success">
                                            <div class="slot-content">
                                                <strong>Ciencias</strong><br>
                                                <small>Prof. José Herrera</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-warning">9:30 - 10:00</td>
                                        <td colspan="5" class="text-center bg-light-warning">
                                            <strong>RECREO</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">10:00 - 10:45</td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-success">
                                            <div class="slot-content">
                                                <strong>Ciencias</strong><br>
                                                <small>Prof. José Herrera</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-warning">
                                            <div class="slot-content">
                                                <strong>Personal Social</strong><br>
                                                <small>Prof. Ana García</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-danger">
                                            <div class="slot-content">
                                                <strong>Educación Física</strong><br>
                                                <small>Prof. Ricardo Torres</small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">10:45 - 11:30</td>
                                        <td class="horario-slot bg-light-info">
                                            <div class="slot-content">
                                                <strong>Comunicación</strong><br>
                                                <small>Prof. María Rojas</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-primary">
                                            <div class="slot-content">
                                                <strong>Matemática</strong><br>
                                                <small>Prof. Luis Correa</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-success">
                                            <div class="slot-content">
                                                <strong>Ciencias</strong><br>
                                                <small>Prof. José Herrera</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-warning">
                                            <div class="slot-content">
                                                <strong>Personal Social</strong><br>
                                                <small>Prof. Ana García</small>
                                            </div>
                                        </td>
                                        <td class="horario-slot bg-light-danger">
                                            <div class="slot-content">
                                                <strong>Educación Física</strong><br>
                                                <small>Prof. Ricardo Torres</small>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leyenda -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6>Leyenda de Áreas:</h6>
                        <div class="row">
                            <div class="col-md-2">
                                <span class="badge bg-primary me-1">■</span> Matemática
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-info me-1">■</span> Comunicación
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-success me-1">■</span> Ciencias
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-warning me-1">■</span> Personal Social
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-danger me-1">■</span> Ed. Física
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-secondary me-1">■</span> Otros
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
                <button type="button" class="btn btn-outline-success" onclick="exportarHorario()">
                    <i class="ti ti-download me-2"></i>
                    Exportar PDF
                </button>
                <button type="button" class="btn btn-info" onclick="editarProgramacion()">
                    <i class="ti ti-edit me-2"></i>
                    Editar Programación
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.horario-slot {
    height: 60px;
    vertical-align: middle;
    cursor: pointer;
    transition: all 0.2s ease;
}

.horario-slot:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.slot-content {
    padding: 4px;
    font-size: 0.8rem;
    line-height: 1.2;
}

.bg-light-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-light-info { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-light-success { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-light-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-light-danger { background-color: rgba(220, 53, 69, 0.1) !important; }
</style>

<script>
function exportarHorario() {
    Swal.fire({
        title: 'Exportar Horario',
        text: 'Esta funcionalidad se implementará próximamente',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

function editarProgramacion() {
    Swal.fire({
        title: 'Editar Programación',
        text: 'Esta funcionalidad se implementará próximamente',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

// Cargar información del aula en el modal de programación
function cargarInfoProgramacionAula(aula) {
    $('#prog_aula_nombre').text(aula.aula_asignada || 'Aula sin nombre');
    $('#prog_aula_detalles').text(`${aula.nivel_nombre} - ${aula.grado} "${aula.seccion}"`);
    $('#prog_capacidad').text(aula.capacidad_maxima || 0);
    
    const ocupacion = `${aula.estudiantes_matriculados || 0}/${aula.capacidad_maxima || 0}`;
    $('#prog_ocupacion').text(ocupacion);
}
</script>