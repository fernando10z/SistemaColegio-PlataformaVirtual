<!-- Modal Ver Detalles de Atención -->
<div class="modal fade" id="modalDetallesAtencion" tabindex="-1" aria-labelledby="modalDetallesAtencionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #F8BBD0, #FCE4EC); border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="modalDetallesAtencionLabel">
                    <i class="ti ti-eye me-2"></i>
                    Detalles de la Atención Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Datos del Estudiante -->
                    <div class="col-12 mb-3">
                        <div class="card" style="background: linear-gradient(135deg, #E5F0FF, #FFE5F0); border: none;">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="ti ti-user me-2"></i>
                                    Información del Estudiante
                                </h6>
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <img id="detalle_foto" src="../assets/images/profile/user-1.jpg" 
                                             class="rounded-circle" width="100" height="100" 
                                             style="object-fit: cover; border: 3px solid #FFE5F0;">
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>Código:</strong>
                                                <p id="detalle_codigo" class="mb-1"></p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Nombres:</strong>
                                                <p id="detalle_nombres" class="mb-1"></p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Apellidos:</strong>
                                                <p id="detalle_apellidos" class="mb-1"></p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Nivel/Grado:</strong>
                                                <p id="detalle_nivel_grado" class="mb-1"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la Atención -->
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ti ti-clipboard-text me-2"></i>
                            Datos de la Atención
                        </h6>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card border-0" style="background: #FFF3E0;">
                            <div class="card-body">
                                <small class="text-muted d-block">Fecha de Atención</small>
                                <h6 id="detalle_fecha" class="mb-0"></h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card border-0" style="background: #E8F5E9;">
                            <div class="card-body">
                                <small class="text-muted d-block">Hora de Atención</small>
                                <h6 id="detalle_hora" class="mb-0"></h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card border-0" style="background: #F3E5F5;">
                            <div class="card-body">
                                <small class="text-muted d-block">Tipo de Atención</small>
                                <h6 id="detalle_tipo" class="mb-0"></h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <strong>Motivo de Consulta:</strong>
                        <p id="detalle_motivo" class="text-muted"></p>
                    </div>

                    <!-- Signos Vitales -->
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ti ti-heartbeat me-2"></i>
                            Signos Vitales
                        </h6>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-0" style="background: #FFEBEE;">
                            <div class="card-body text-center">
                                <i class="ti ti-temperature" style="font-size: 2rem; color: #D32F2F;"></i>
                                <h5 id="detalle_temperatura" class="mt-2 mb-0">--</h5>
                                <small class="text-muted">Temperatura (°C)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-0" style="background: #E3F2FD;">
                            <div class="card-body text-center">
                                <i class="ti ti-activity" style="font-size: 2rem; color: #1976D2;"></i>
                                <h5 id="detalle_presion" class="mt-2 mb-0">--</h5>
                                <small class="text-muted">Presión Arterial</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-0" style="background: #E8F5E9;">
                            <div class="card-body text-center">
                                <i class="ti ti-heart" style="font-size: 2rem; color: #388E3C;"></i>
                                <h5 id="detalle_frecuencia" class="mt-2 mb-0">--</h5>
                                <small class="text-muted">Frecuencia (lpm)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card border-0" style="background: #FFF3E0;">
                            <div class="card-body text-center">
                                <i class="ti ti-weight" style="font-size: 2rem; color: #F57C00;"></i>
                                <h5 id="detalle_peso" class="mt-2 mb-0">--</h5>
                                <small class="text-muted">Peso (kg)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tratamiento -->
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ti ti-pill me-2"></i>
                            Tratamiento Indicado
                        </h6>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-0" style="background: #F5F5F5;">
                            <div class="card-body">
                                <p id="detalle_tratamiento" class="mb-0 text-muted">Sin tratamiento registrado</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto Apoderado -->
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ti ti-phone me-2"></i>
                            Contacto con Apoderado
                        </h6>
                    </div>

                    <div class="col-12 mb-3">
                        <div id="detalle_contacto_apoderado"></div>
                    </div>

                    <!-- Enfermero que Atiende -->
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="ti ti-user-check me-2"></i>
                            Atendido Por
                        </h6>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-0" style="background: #E1F5FE;">
                            <div class="card-body">
                                <p id="detalle_enfermero" class="mb-0"></p>
                                <small class="text-muted" id="detalle_fecha_registro"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalles()">
                    <i class="ti ti-printer me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosDetalles(atencion) {
    // Datos del estudiante
    document.getElementById('detalle_foto').src = atencion.estudiante_foto || '../assets/images/profile/user-1.jpg';
    document.getElementById('detalle_codigo').textContent = atencion.codigo_estudiante || 'N/A';
    document.getElementById('detalle_nombres').textContent = atencion.estudiante_nombres || 'N/A';
    document.getElementById('detalle_apellidos').textContent = atencion.estudiante_apellidos || 'N/A';
    document.getElementById('detalle_nivel_grado').textContent = 
        `${atencion.nivel_nombre || 'N/A'} - ${atencion.grado || ''} "${atencion.seccion || ''}"`;

    // Datos de la atención
    document.getElementById('detalle_fecha').textContent = 
        new Date(atencion.fecha_atencion).toLocaleDateString('es-PE');
    document.getElementById('detalle_hora').textContent = atencion.hora_atencion || 'N/A';
    document.getElementById('detalle_tipo').textContent = atencion.tipo_atencion || 'N/A';
    document.getElementById('detalle_motivo').textContent = atencion.motivo_consulta || 'No especificado';

    // Signos vitales
    const signosVitales = atencion.signos_vitales ? JSON.parse(atencion.signos_vitales) : {};
    document.getElementById('detalle_temperatura').textContent = signosVitales.temperatura || '--';
    document.getElementById('detalle_presion').textContent = signosVitales.presion_arterial || '--';
    document.getElementById('detalle_frecuencia').textContent = signosVitales.frecuencia_cardiaca || '--';
    document.getElementById('detalle_peso').textContent = signosVitales.peso || '--';

    // Tratamiento
    const tratamiento = atencion.tratamiento ? JSON.parse(atencion.tratamiento) : {};
    document.getElementById('detalle_tratamiento').textContent = 
        tratamiento.descripcion || tratamiento.medicamento || 'Sin tratamiento registrado';

    // Contacto apoderado
    const contacto = atencion.contacto_apoderado ? JSON.parse(atencion.contacto_apoderado) : {};
    let htmlContacto = '<div class="alert alert-info">';
    if (contacto.contactado === 'SI') {
        htmlContacto += '<strong><i class="ti ti-check me-2"></i>Apoderado contactado</strong>';
        if (contacto.observaciones) {
            htmlContacto += `<p class="mb-0 mt-2">${contacto.observaciones}</p>`;
        }
    } else {
        htmlContacto += '<strong><i class="ti ti-x me-2"></i>Apoderado no contactado</strong>';
    }
    htmlContacto += '</div>';
    document.getElementById('detalle_contacto_apoderado').innerHTML = htmlContacto;

    // Enfermero
    document.getElementById('detalle_enfermero').textContent = 
        `${atencion.enfermero_nombres || ''} ${atencion.enfermero_apellidos || 'No especificado'}`;
    document.getElementById('detalle_fecha_registro').textContent = 
        `Registrado el: ${new Date(atencion.fecha_registro).toLocaleString('es-PE')}`;
}

function imprimirDetalles() {
    window.print();
}
</script>