<!-- Modal Detalles Ficha Médica -->
<div class="modal fade" id="modalDetallesFicha" tabindex="-1" aria-labelledby="modalDetallesFichaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);">
                <h5 class="modal-title fw-bold" id="modalDetallesFichaLabel">
                    <i class="ti ti-file-medical me-2"></i>
                    Detalles de Ficha Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información del Estudiante -->
                <div class="card mb-4" style="border-left: 4px solid #B4E5D4;">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <img id="detalle_foto" src="" class="rounded-circle" 
                                 style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #FFE5F0;" alt="Estudiante">
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1" id="detalle_nombre_estudiante"></h5>
                                <p class="mb-1 text-muted"><strong>Código:</strong> <span id="detalle_codigo"></span></p>
                                <p class="mb-1 text-muted"><strong>Nivel/Grado:</strong> <span id="detalle_nivel_grado"></span></p>
                                <p class="mb-0 text-muted"><strong>Edad:</strong> <span id="detalle_edad"></span></p>
                            </div>
                            <div>
                                <span id="detalle_estado_badge"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos Médicos Básicos -->
                <div class="card mb-4" style="border-left: 4px solid #FFE5B4;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="ti ti-heart-rate-monitor me-2"></i>
                            Datos Médicos Básicos
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="p-3 rounded" style="background: #FFF0F5;">
                                    <div class="text-center">
                                        <i class="ti ti-droplet fs-1" style="color: #DC143C;"></i>
                                        <h4 class="mt-2 mb-0 fw-bold" id="detalle_tipo_sangre"></h4>
                                        <small class="text-muted">Tipo de Sangre</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded" style="background: #E5F0FF;">
                                    <div class="text-center">
                                        <i class="ti ti-weight fs-1" style="color: #4169E1;"></i>
                                        <h4 class="mt-2 mb-0 fw-bold" id="detalle_peso"></h4>
                                        <small class="text-muted">Peso (kg)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded" style="background: #FFE5F0;">
                                    <div class="text-center">
                                        <i class="ti ti-ruler-measure fs-1" style="color: #FF69B4;"></i>
                                        <h4 class="mt-2 mb-0 fw-bold" id="detalle_talla"></h4>
                                        <small class="text-muted">Talla (cm)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded" style="background: #E0FFE0;">
                                    <div class="text-center">
                                        <i class="ti ti-activity fs-1" style="color: #228B22;"></i>
                                        <h4 class="mt-2 mb-0 fw-bold" id="detalle_imc"></h4>
                                        <small class="text-muted">IMC</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial Médico -->
                <div class="card mb-4" style="border-left: 4px solid #FFD4B4;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="ti ti-clipboard-text me-2"></i>
                            Historial Médico
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded" style="background: #FFF5E6;">
                                    <strong class="d-block mb-2">
                                        <i class="ti ti-alert-circle me-1"></i>
                                        Alergias Conocidas
                                    </strong>
                                    <p class="mb-0" id="detalle_alergias"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded" style="background: #FFE5E5;">
                                    <strong class="d-block mb-2">
                                        <i class="ti ti-report-medical me-1"></i>
                                        Enfermedades Crónicas
                                    </strong>
                                    <p class="mb-0" id="detalle_enfermedades"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded" style="background: #E5F0FF;">
                                    <strong class="d-block mb-2">
                                        <i class="ti ti-pill me-1"></i>
                                        Medicamentos Actuales
                                    </strong>
                                    <p class="mb-0" id="detalle_medicamentos"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded" style="background: #F0E5FF;">
                                    <strong class="d-block mb-2">
                                        <i class="ti ti-cut me-1"></i>
                                        Cirugías Previas
                                    </strong>
                                    <p class="mb-0" id="detalle_cirugias"></p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded" style="background: #E5FFE5;">
                                    <strong class="d-block mb-2">
                                        <i class="ti ti-vaccine me-1"></i>
                                        Estado de Vacunas
                                    </strong>
                                    <p class="mb-0" id="detalle_vacunas"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contactos de Emergencia -->
                <div class="card mb-4" style="border-left: 4px solid #D4A5D4;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="ti ti-phone me-2"></i>
                            Contactos de Emergencia
                        </h6>
                        <div id="detalle_contactos_container"></div>
                    </div>
                </div>

                <!-- Médico Tratante -->
                <div class="card mb-4" style="border-left: 4px solid #B4E5D4;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="ti ti-stethoscope me-2"></i>
                            Médico Tratante
                        </h6>
                        <div id="detalle_medico_container"></div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="card mb-4" style="border-left: 4px solid #FFE5B4;">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">
                            <i class="ti ti-notes me-2"></i>
                            Observaciones Adicionales
                        </h6>
                        <div class="p-3 rounded" style="background: #FFFAF0;">
                            <p class="mb-0" id="detalle_observaciones"></p>
                        </div>
                    </div>
                </div>

                <!-- Metadatos -->
                <div class="card" style="border-left: 4px solid #E0E0E0;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="ti ti-calendar me-1"></i>
                                    <strong>Última actualización:</strong> <span id="detalle_fecha_actualizacion"></span>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">
                                    <i class="ti ti-user me-1"></i>
                                    <strong>Actualizado por:</strong> <span id="detalle_usuario_actualiza"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosDetalles(ficha) {
    // Datos del estudiante
    $('#detalle_foto').attr('src', ficha.estudiante_foto || '../assets/images/profile/user-1.jpg');
    $('#detalle_nombre_estudiante').text(`${ficha.estudiante_apellidos} ${ficha.estudiante_nombres}`);
    $('#detalle_codigo').text(ficha.codigo_estudiante);
    $('#detalle_nivel_grado').text(`${ficha.nivel_nombre || 'N/A'} - ${ficha.grado || ''} "${ficha.seccion || ''}"`);
    
    // Calcular edad
    if (ficha.fecha_nacimiento) {
        const nacimiento = new Date(ficha.fecha_nacimiento);
        const hoy = new Date();
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        $('#detalle_edad').text(`${edad} años`);
    } else {
        $('#detalle_edad').text('N/A');
    }
    
    // Estado
    const esVigente = ficha.vigente == 1;
    const badgeClass = esVigente ? 'badge-vigente' : 'badge-vencida';
    const badgeText = esVigente ? 'VIGENTE' : 'VENCIDA';
    $('#detalle_estado_badge').html(`<span class="estado-badge ${badgeClass}">${badgeText}</span>`);
    
    // Datos médicos
    const datos_medicos = typeof ficha.datos_medicos === 'string' ? JSON.parse(ficha.datos_medicos) : ficha.datos_medicos;
    $('#detalle_tipo_sangre').text(datos_medicos.tipo_sangre || 'N/A');
    $('#detalle_peso').text(datos_medicos.peso_kg ? parseFloat(datos_medicos.peso_kg).toFixed(1) : 'N/A');
    $('#detalle_talla').text(datos_medicos.talla_cm ? parseFloat(datos_medicos.talla_cm).toFixed(0) : 'N/A');
    $('#detalle_imc').text(datos_medicos.imc ? parseFloat(datos_medicos.imc).toFixed(1) : 'N/A');
    
    // Historial médico
    const historial = typeof ficha.historial_medico === 'string' ? JSON.parse(ficha.historial_medico) : ficha.historial_medico;
    $('#detalle_alergias').text(historial.alergias_conocidas || 'Ninguna');
    $('#detalle_enfermedades').text(historial.enfermedades_cronicas || 'Ninguna');
    $('#detalle_medicamentos').text(historial.medicamentos_actuales || 'Ninguno');
    $('#detalle_cirugias').text(historial.cirugias_previas || 'Ninguna');
    $('#detalle_vacunas').html(historial.vacunas_completas ? 
        '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Completas</span>' : 
        '<span class="badge bg-warning"><i class="ti ti-alert-triangle me-1"></i>Incompletas</span>');
    
    // Contactos de emergencia
    const contactos = typeof ficha.contactos_emergencia === 'string' ? JSON.parse(ficha.contactos_emergencia) : ficha.contactos_emergencia;
    let htmlContactos = '';
    if (contactos && contactos.length > 0) {
        contactos.forEach((contacto, index) => {
            const esPrincipal = contacto.es_principal ? '<span class="badge bg-primary ms-2">Principal</span>' : '';
            htmlContactos += `
                <div class="card mb-3" style="background: #F5F5F5;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">${contacto.nombre || 'N/A'}${esPrincipal}</h6>
                                <p class="mb-1"><strong>Parentesco:</strong> ${contacto.parentesco || 'N/A'}</p>
                                <p class="mb-0"><strong>Teléfono:</strong> ${contacto.telefono || 'N/A'}</p>
                            </div>
                            <i class="ti ti-user-check fs-1" style="color: #9370DB;"></i>
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        htmlContactos = '<p class="text-muted">No hay contactos registrados</p>';
    }
    $('#detalle_contactos_container').html(htmlContactos);
    
    // Médico tratante
    const medico = typeof ficha.medico_tratante === 'string' ? JSON.parse(ficha.medico_tratante) : ficha.medico_tratante;
    let htmlMedico = '';
    if (medico && (medico.nombre || medico.telefono)) {
        htmlMedico = `
            <div class="card" style="background: #F5F5F5;">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong class="d-block">Nombre:</strong>
                            <p class="mb-0">${medico.nombre || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="d-block">Especialidad:</strong>
                            <p class="mb-0">${medico.especialidad || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="d-block">Teléfono:</strong>
                            <p class="mb-0">${medico.telefono || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="d-block">Consultorio:</strong>
                            <p class="mb-0">${medico.direccion_consultorio || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        htmlMedico = '<p class="text-muted">No hay médico tratante registrado</p>';
    }
    $('#detalle_medico_container').html(htmlMedico);
    
    // Observaciones
    $('#detalle_observaciones').text(ficha.observaciones_adicionales || 'Sin observaciones adicionales');
    
    // Metadatos
    $('#detalle_fecha_actualizacion').text(ficha.fecha_actualizacion ? 
        new Date(ficha.fecha_actualizacion).toLocaleString('es-ES') : 'N/A');
    $('#detalle_usuario_actualiza').text(ficha.usuario_nombres && ficha.usuario_apellidos ? 
        `${ficha.usuario_nombres} ${ficha.usuario_apellidos}` : 'N/A');
}
</script>

<style>
.badge-vigente { background: #B4E5D4; color: #006400; }
.badge-vencida { background: #FFD4B4; color: #8B4513; }
</style>