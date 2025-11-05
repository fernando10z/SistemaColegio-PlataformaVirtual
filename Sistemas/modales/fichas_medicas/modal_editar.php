<!-- Modal Editar Ficha Médica -->
<div class="modal fade" id="modalEditarFicha" tabindex="-1" aria-labelledby="modalEditarFichaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);">
                <h5 class="modal-title fw-bold" id="modalEditarFichaLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Ficha Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarFicha">
                    <input type="hidden" id="editar_ficha_id" name="ficha_id">
                    
                    <!-- Información del Estudiante (Solo lectura) -->
                    <div class="card mb-4" style="border-left: 4px solid #B4E5D4;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3">
                                <i class="ti ti-user me-2"></i>
                                Estudiante
                            </h6>
                            <div class="d-flex align-items-center gap-3">
                                <img id="editar_estudiante_foto" src="" class="rounded-circle" 
                                     style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #FFE5F0;" alt="Estudiante">
                                <div>
                                    <h6 class="mb-0" id="editar_estudiante_nombre"></h6>
                                    <small class="text-muted" id="editar_estudiante_info"></small>
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
                                    <label class="form-label">Tipo de Sangre <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editar_tipo_sangre" name="tipo_sangre" required>
                                        <option value="">Seleccione</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                    <div class="invalid-feedback">Seleccione un tipo de sangre</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editar_peso_kg" name="peso_kg" 
                                           min="5" max="150" step="0.1" required>
                                    <div class="invalid-feedback">Ingrese un peso válido (5-150 kg)</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Talla (cm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="editar_talla_cm" name="talla_cm" 
                                           min="50" max="250" step="0.1" required>
                                    <div class="invalid-feedback">Ingrese una talla válida (50-250 cm)</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">IMC (calculado)</label>
                                    <input type="text" class="form-control" id="editar_imc" name="imc" readonly 
                                           style="background-color: #F5F5F5;">
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
                                    <label class="form-label">Alergias Conocidas</label>
                                    <textarea class="form-control" id="editar_alergias" name="alergias_conocidas" 
                                              rows="3" maxlength="500" placeholder="Ej: Polen, maní, penicilina..."></textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorAlergiasEditar">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Enfermedades Crónicas</label>
                                    <textarea class="form-control" id="editar_enfermedades" name="enfermedades_cronicas" 
                                              rows="3" maxlength="500" placeholder="Ej: Asma, diabetes..."></textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorEnfermedadesEditar">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Medicamentos Actuales</label>
                                    <textarea class="form-control" id="editar_medicamentos" name="medicamentos_actuales" 
                                              rows="3" maxlength="500" placeholder="Ej: Ibuprofeno, omeprazol..."></textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorMedicamentosEditar">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cirugías Previas</label>
                                    <textarea class="form-control" id="editar_cirugias" name="cirugias_previas" 
                                              rows="3" maxlength="500" placeholder="Ej: Apendicectomía..."></textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorCirugiasEditar">0</span>/500</small>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editar_vacunas_completas" 
                                               name="vacunas_completas" value="1">
                                        <label class="form-check-label" for="editar_vacunas_completas">
                                            Vacunas completas según calendario nacional
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contactos de Emergencia -->
                    <div class="card mb-4" style="border-left: 4px solid #D4A5D4;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title fw-bold mb-0">
                                    <i class="ti ti-phone me-2"></i>
                                    Contactos de Emergencia <span class="text-danger">*</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarContactoEditar()">
                                    <i class="ti ti-plus me-1"></i>
                                    Agregar Contacto
                                </button>
                            </div>
                            <div id="contactosContainerEditar">
                                <!-- Se cargarán dinámicamente -->
                            </div>
                            <small class="text-muted">Al menos un contacto de emergencia es obligatorio</small>
                        </div>
                    </div>

                    <!-- Médico Tratante -->
                    <div class="card mb-4" style="border-left: 4px solid #B4E5D4;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3">
                                <i class="ti ti-stethoscope me-2"></i>
                                Médico Tratante (Opcional)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del Médico</label>
                                    <input type="text" class="form-control" id="editar_medico_nombre" 
                                           name="medico_nombre" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Especialidad</label>
                                    <input type="text" class="form-control" id="editar_medico_especialidad" 
                                           name="medico_especialidad" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="editar_medico_telefono" 
                                           name="medico_telefono" maxlength="20" pattern="[0-9\s\-\+\(\)]+">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dirección del Consultorio</label>
                                    <input type="text" class="form-control" id="editar_medico_direccion" 
                                           name="medico_direccion" maxlength="200">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card mb-4" style="border-left: 4px solid #FFE5B4;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3">
                                <i class="ti ti-notes me-2"></i>
                                Observaciones Adicionales
                            </h6>
                            <textarea class="form-control" id="editar_observaciones" name="observaciones_adicionales" 
                                      rows="4" maxlength="1000" 
                                      placeholder="Información adicional relevante..."></textarea>
                            <small class="text-muted">Máximo 1000 caracteres. <span id="contadorObservacionesEditar">0</span>/1000</small>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="card" style="border-left: 4px solid #B4E5D4;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editar_vigente" 
                                       name="vigente" value="1">
                                <label class="form-check-label fw-bold" for="editar_vigente">
                                    Marcar como ficha vigente
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="actualizarFicha()">
                    <i class="ti ti-device-floppy me-1"></i>
                    Actualizar Ficha Médica
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular IMC automáticamente en edición
$('#editar_peso_kg, #editar_talla_cm').on('input', function() {
    const peso = parseFloat($('#editar_peso_kg').val());
    const talla = parseFloat($('#editar_talla_cm').val()) / 100;
    
    if (peso > 0 && talla > 0) {
        const imc = (peso / (talla * talla)).toFixed(1);
        $('#editar_imc').val(imc);
    } else {
        $('#editar_imc').val('');
    }
});

// Contadores de caracteres para edición
$('#editar_alergias').on('input', function() {
    $('#contadorAlergiasEditar').text($(this).val().length);
});
$('#editar_enfermedades').on('input', function() {
    $('#contadorEnfermedadesEditar').text($(this).val().length);
});
$('#editar_medicamentos').on('input', function() {
    $('#contadorMedicamentosEditar').text($(this).val().length);
});
$('#editar_cirugias').on('input', function() {
    $('#contadorCirugiasEditar').text($(this).val().length);
});
$('#editar_observaciones').on('input', function() {
    $('#contadorObservacionesEditar').text($(this).val().length);
});

let contadorContactosEditar = 0;

function agregarContactoEditar() {
    contadorContactosEditar++;
    const esUnico = $('#contactosContainerEditar').children().length === 0;
    
    const html = `
        <div class="contacto-item-editar card mb-3" id="contactoEditar_${contadorContactosEditar}" style="background: #F5F5F5;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Contacto ${contadorContactosEditar}</h6>
                    ${!esUnico ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarContactoEditar(${contadorContactosEditar})">
                        <i class="ti ti-trash"></i>
                    </button>` : ''}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control contacto-nombre" name="contacto_nombre[]" 
                               required maxlength="100" placeholder="Ej: María González">
                        <div class="invalid-feedback">Ingrese el nombre</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Parentesco <span class="text-danger">*</span></label>
                        <select class="form-select" name="contacto_parentesco[]" required>
                            <option value="">Seleccione</option>
                            <option value="Madre">Madre</option>
                            <option value="Padre">Padre</option>
                            <option value="Tutor">Tutor</option>
                            <option value="Abuelo/a">Abuelo/a</option>
                            <option value="Tío/a">Tío/a</option>
                            <option value="Hermano/a">Hermano/a</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <div class="invalid-feedback">Seleccione el parentesco</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="contacto_telefono[]" 
                               required maxlength="20" pattern="[0-9\s\-\+\(\)]+" 
                               placeholder="Ej: 999888777">
                        <div class="invalid-feedback">Ingrese un teléfono válido</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Principal</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input contacto-principal" type="radio" 
                                   name="contacto_principal" value="${contadorContactosEditar}" 
                                   ${esUnico ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#contactosContainerEditar').append(html);
}

function eliminarContactoEditar(id) {
    const total = $('#contactosContainerEditar').children().length;
    if (total > 1) {
        $(`#contactoEditar_${id}`).remove();
    } else {
        Swal.fire({
            title: 'No se puede eliminar',
            text: 'Debe haber al menos un contacto de emergencia',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

function cargarDatosEdicion(ficha) {
    // ID de la ficha
    $('#editar_ficha_id').val(ficha.id);
    
    // Información del estudiante (solo lectura)
    $('#editar_estudiante_foto').attr('src', ficha.estudiante_foto || '../assets/images/profile/user-1.jpg');
    $('#editar_estudiante_nombre').text(`${ficha.estudiante_apellidos} ${ficha.estudiante_nombres}`);
    $('#editar_estudiante_info').text(`${ficha.codigo_estudiante} - ${ficha.nivel_nombre || 'N/A'} - ${ficha.grado || ''} "${ficha.seccion || ''}"`);
    
    // Datos médicos
    const datos_medicos = typeof ficha.datos_medicos === 'string' ? JSON.parse(ficha.datos_medicos) : ficha.datos_medicos;
    $('#editar_tipo_sangre').val(datos_medicos.tipo_sangre || '');
    $('#editar_peso_kg').val(datos_medicos.peso_kg || '');
    $('#editar_talla_cm').val(datos_medicos.talla_cm || '');
    $('#editar_imc').val(datos_medicos.imc || '');
    
    // Historial médico
    const historial = typeof ficha.historial_medico === 'string' ? JSON.parse(ficha.historial_medico) : ficha.historial_medico;
    $('#editar_alergias').val(historial.alergias_conocidas || '');
    $('#editar_enfermedades').val(historial.enfermedades_cronicas || '');
    $('#editar_medicamentos').val(historial.medicamentos_actuales || '');
    $('#editar_cirugias').val(historial.cirugias_previas || '');
    $('#editar_vacunas_completas').prop('checked', historial.vacunas_completas == 1);
    
    // Actualizar contadores
    $('#contadorAlergiasEditar').text($('#editar_alergias').val().length);
    $('#contadorEnfermedadesEditar').text($('#editar_enfermedades').val().length);
    $('#contadorMedicamentosEditar').text($('#editar_medicamentos').val().length);
    $('#contadorCirugiasEditar').text($('#editar_cirugias').val().length);
    
    // Contactos de emergencia
    $('#contactosContainerEditar').empty();
    contadorContactosEditar = 0;
    const contactos = typeof ficha.contactos_emergencia === 'string' ? JSON.parse(ficha.contactos_emergencia) : ficha.contactos_emergencia;
    if (contactos && contactos.length > 0) {
        contactos.forEach((contacto, index) => {
            agregarContactoEditar();
            const contenedor = $(`#contactoEditar_${contadorContactosEditar}`);
            contenedor.find('input[name="contacto_nombre[]"]').val(contacto.nombre || '');
            contenedor.find('select[name="contacto_parentesco[]"]').val(contacto.parentesco || '');
            contenedor.find('input[name="contacto_telefono[]"]').val(contacto.telefono || '');
            if (contacto.es_principal) {
                contenedor.find('.contacto-principal').prop('checked', true);
            }
        });
    } else {
        agregarContactoEditar();
    }
    
    // Médico tratante
    const medico = typeof ficha.medico_tratante === 'string' ? JSON.parse(ficha.medico_tratante) : ficha.medico_tratante;
    $('#editar_medico_nombre').val(medico.nombre || '');
    $('#editar_medico_especialidad').val(medico.especialidad || '');
    $('#editar_medico_telefono').val(medico.telefono || '');
    $('#editar_medico_direccion').val(medico.direccion_consultorio || '');
    
    // Observaciones
    $('#editar_observaciones').val(ficha.observaciones_adicionales || '');
    $('#contadorObservacionesEditar').text($('#editar_observaciones').val().length);
    
    // Estado
    $('#editar_vigente').prop('checked', ficha.vigente == 1);
}

function actualizarFicha() {
    const form = document.getElementById('formEditarFicha');
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        Swal.fire({
            title: 'Formulario Incompleto',
            text: 'Por favor complete todos los campos obligatorios',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    // Validar que haya al menos un contacto
    if ($('#contactosContainerEditar .contacto-item-editar').length === 0) {
        Swal.fire({
            title: 'Contacto Requerido',
            text: 'Debe agregar al menos un contacto de emergencia',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    mostrarCarga();
    
    const formData = new FormData(form);
    formData.append('accion', 'actualizar');
    
    fetch('modales/fichas_medicas/procesar_fichas.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            $('#modalEditarFicha').modal('hide');
            mostrarExito(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al actualizar la ficha médica');
    });
}

// Limpiar formulario al cerrar modal
$('#modalEditarFicha').on('hidden.bs.modal', function() {
    document.getElementById('formEditarFicha').reset();
    document.getElementById('formEditarFicha').classList.remove('was-validated');
    $('#contactosContainerEditar').empty();
    contadorContactosEditar = 0;
});
</script>