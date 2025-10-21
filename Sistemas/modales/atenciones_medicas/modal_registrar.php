<!-- Modal Registrar Atención Médica -->
<div class="modal fade" id="modalRegistrarAtencion" tabindex="-1" aria-labelledby="modalRegistrarAtencionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #E5F0FF, #FFE5F0); border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="modalRegistrarAtencionLabel">
                    <i class="ti ti-plus-circle me-2"></i>
                    Registrar Atención Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarAtencion">
                    <div class="row">
                        <!-- Datos del Estudiante -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-user me-2"></i>
                                Datos del Estudiante
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estudiante <span class="text-danger">*</span></label>
                            <select class="form-select" name="estudiante_id" id="estudiante_id" required>
                                <option value="">Seleccione un estudiante</option>
                                <?php foreach ($estudiantes_activos as $est): ?>
                                    <option value="<?= $est['id'] ?>" 
                                            data-nombre="<?= htmlspecialchars($est['nombre_completo']) ?>"
                                            data-nivel="<?= htmlspecialchars($est['nivel_nombre'] ?? 'N/A') ?>"
                                            data-grado="<?= htmlspecialchars($est['grado'] ?? '') ?>"
                                            data-seccion="<?= htmlspecialchars($est['seccion'] ?? '') ?>">
                                        <?= htmlspecialchars($est['codigo_estudiante']) ?> - <?= htmlspecialchars($est['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un estudiante</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Atención <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tipo_atencion" id="tipo_atencion" 
                                   placeholder="Ej: Consulta General, Emergencia, Control" required>
                            <div class="invalid-feedback">Ingrese el tipo de atención</div>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>
                                Fecha y Hora de Atención
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Atención <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_atencion" id="fecha_atencion" required>
                            <div class="invalid-feedback">Ingrese la fecha de atención</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora de Atención <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="hora_atencion" id="hora_atencion" required>
                            <div class="invalid-feedback">Ingrese la hora de atención</div>
                        </div>

                        <!-- Motivo de Consulta -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-clipboard-text me-2"></i>
                                Motivo de Consulta
                            </h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Motivo de Consulta <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo_consulta" id="motivo_consulta" 
                                      rows="3" placeholder="Describa el motivo de la consulta..." required></textarea>
                            <div class="invalid-feedback">Ingrese el motivo de consulta</div>
                        </div>

                        <!-- Signos Vitales -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-heartbeat me-2"></i>
                                Signos Vitales
                            </h6>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Temperatura (°C)</label>
                            <input type="number" step="0.1" class="form-control" name="temperatura" id="temperatura" 
                                   placeholder="36.5">
                            <small class="text-muted">Rango normal: 36.1 - 37.2°C</small>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Presión Arterial</label>
                            <input type="text" class="form-control" name="presion_arterial" id="presion_arterial" 
                                   placeholder="120/80">
                            <small class="text-muted">Formato: Sistólica/Diastólica</small>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Frecuencia Cardíaca (lpm)</label>
                            <input type="number" class="form-control" name="frecuencia_cardiaca" id="frecuencia_cardiaca" 
                                   placeholder="70">
                            <small class="text-muted">Latidos por minuto</small>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.1" class="form-control" name="peso" id="peso" 
                                   placeholder="50.5">
                        </div>

                        <!-- Tratamiento -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-pill me-2"></i>
                                Tratamiento
                            </h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Tratamiento Indicado</label>
                            <textarea class="form-control" name="tratamiento_texto" id="tratamiento_texto" 
                                      rows="3" placeholder="Describa el tratamiento indicado..."></textarea>
                        </div>

                        <!-- Contacto de Apoderado -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-phone me-2"></i>
                                Contacto con Apoderado
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">¿Se contactó al apoderado?</label>
                            <select class="form-select" name="contacto_apoderado" id="contacto_apoderado">
                                <option value="NO">No</option>
                                <option value="SI">Sí</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Observaciones del Contacto</label>
                            <input type="text" class="form-control" name="obs_contacto" id="obs_contacto" 
                                   placeholder="Detalles del contacto...">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-register" onclick="guardarAtencion()">
                    <i class="ti ti-device-floppy me-2"></i>Guardar Atención
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Establecer fecha y hora actual por defecto
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const now = new Date().toTimeString().slice(0,5);
    document.getElementById('fecha_atencion').value = today;
    document.getElementById('hora_atencion').value = now;
});

function guardarAtencion() {
    const form = document.getElementById('formRegistrarAtencion');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        mostrarError('Por favor complete todos los campos requeridos');
        return;
    }

    // Recopilar datos
    const formData = new FormData(form);
    
    // Construir objeto de signos vitales
    const signosVitales = {
        temperatura: formData.get('temperatura') || null,
        presion_arterial: formData.get('presion_arterial') || null,
        frecuencia_cardiaca: formData.get('frecuencia_cardiaca') || null,
        peso: formData.get('peso') || null
    };

    // Construir objeto de tratamiento
    const tratamiento = {
        descripcion: formData.get('tratamiento_texto') || null,
        fecha: formData.get('fecha_atencion')
    };

    // Construir objeto de contacto apoderado
    const contactoApoderado = {
        contactado: formData.get('contacto_apoderado'),
        observaciones: formData.get('obs_contacto') || null,
        fecha_hora: formData.get('fecha_atencion') + ' ' + formData.get('hora_atencion')
    };

    // Preparar datos para enviar
    const datosEnvio = new URLSearchParams({
        accion: 'crear',
        estudiante_id: formData.get('estudiante_id'),
        fecha_atencion: formData.get('fecha_atencion'),
        hora_atencion: formData.get('hora_atencion'),
        tipo_atencion: formData.get('tipo_atencion'),
        motivo_consulta: formData.get('motivo_consulta'),
        signos_vitales: JSON.stringify(signosVitales),
        tratamiento: JSON.stringify(tratamiento),
        contacto_apoderado: JSON.stringify(contactoApoderado)
    });

    mostrarCarga();

    fetch('modales/atenciones_medicas/procesar_atenciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: datosEnvio
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            mostrarExito(data.message);
            $('#modalRegistrarAtencion').modal('hide');
            form.reset();
            form.classList.remove('was-validated');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al guardar la atención');
        console.error(error);
    });
}
</script>