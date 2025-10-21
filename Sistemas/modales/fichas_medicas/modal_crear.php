<!-- Modal Crear Ficha Médica -->
<div class="modal fade" id="modalCrearFicha" tabindex="-1" aria-labelledby="modalCrearFichaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #E5F0FF 0%, #FFE5F0 100%);">
                <h5 class="modal-title fw-bold" id="modalCrearFichaLabel">
                    <i class="ti ti-file-medical me-2"></i>
                    Nueva Ficha Médica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearFicha">
                    <!-- Selección de Estudiante -->
                    <div class="card mb-4" style="border-left: 4px solid #B4E5D4;">
                        <div class="card-body">
                            <h6 class="card-title fw-bold mb-3">
                                <i class="ti ti-user me-2"></i>
                                Seleccionar Estudiante
                            </h6>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label">Estudiante <span class="text-danger">*</span></label>
                                    <select class="form-select" id="crear_estudiante_id" name="estudiante_id" required>
                                        <option value="">Seleccione un estudiante</option>
                                        <?php foreach ($estudiantes_sin_ficha as $estudiante): ?>
                                            <option value="<?= $estudiante['id'] ?>">
                                                <?= htmlspecialchars($estudiante['codigo_estudiante']) ?> - 
                                                <?= htmlspecialchars($estudiante['nombre_completo']) ?>
                                                <?php if (!empty($estudiante['nivel_nombre'])): ?>
                                                    (<?= htmlspecialchars($estudiante['nivel_nombre']) ?> - 
                                                    <?= htmlspecialchars($estudiante['grado']) ?> "<?= htmlspecialchars($estudiante['seccion']) ?>")
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Debe seleccionar un estudiante</div>
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
                                    <select class="form-select" id="crear_tipo_sangre" name="tipo_sangre" required>
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
                                    <input type="number" class="form-control" id="crear_peso_kg" name="peso_kg" 
                                           min="5" max="150" step="0.1" required>
                                    <div class="invalid-feedback">Ingrese un peso válido (5-150 kg)</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Talla (cm) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="crear_talla_cm" name="talla_cm" 
                                           min="50" max="250" step="0.1" required>
                                    <div class="invalid-feedback">Ingrese una talla válida (50-250 cm)</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">IMC (calculado)</label>
                                    <input type="text" class="form-control" id="crear_imc" name="imc" readonly 
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
                                    <textarea class="form-control" id="crear_alergias" name="alergias_conocidas" 
                                              rows="3" maxlength="500" placeholder="Ej: Polen, maní, penicilina...">Ninguna</textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorAlergias">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Enfermedades Crónicas</label>
                                    <textarea class="form-control" id="crear_enfermedades" name="enfermedades_cronicas" 
                                              rows="3" maxlength="500" placeholder="Ej: Asma, diabetes...">Ninguna</textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorEnfermedades">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Medicamentos Actuales</label>
                                    <textarea class="form-control" id="crear_medicamentos" name="medicamentos_actuales" 
                                              rows="3" maxlength="500" placeholder="Ej: Ibuprofeno, omeprazol...">Ninguno</textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorMedicamentos">0</span>/500</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cirugías Previas</label>
                                    <textarea class="form-control" id="crear_cirugias" name="cirugias_previas" 
                                              rows="3" maxlength="500" placeholder="Ej: Apendicectomía...">Ninguna</textarea>
                                    <small class="text-muted">Máximo 500 caracteres. <span id="contadorCirugias">0</span>/500</small>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crear_vacunas_completas" 
                                               name="vacunas_completas" value="1" checked>
                                        <label class="form-check-label" for="crear_vacunas_completas">
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
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarContactoCrear()">
                                    <i class="ti ti-plus me-1"></i>
                                    Agregar Contacto
                                </button>
                            </div>
                            <div id="contactosContainerCrear">
                                <!-- Se agregarán dinámicamente -->
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
                                    <input type="text" class="form-control" id="crear_medico_nombre" 
                                           name="medico_nombre" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Especialidad</label>
                                    <input type="text" class="form-control" id="crear_medico_especialidad" 
                                           name="medico_especialidad" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="crear_medico_telefono" 
                                           name="medico_telefono" maxlength="20" pattern="[0-9\s\-\+\(\)]+">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dirección del Consultorio</label>
                                    <input type="text" class="form-control" id="crear_medico_direccion" 
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
                            <textarea class="form-control" id="crear_observaciones" name="observaciones_adicionales" 
                                      rows="4" maxlength="1000" 
                                      placeholder="Información adicional relevante..."></textarea>
                            <small class="text-muted">Máximo 1000 caracteres. <span id="contadorObservaciones">0</span>/1000</small>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="card" style="border-left: 4px solid #B4E5D4;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="crear_vigente" 
                                       name="vigente" value="1" checked>
                                <label class="form-check-label fw-bold" for="crear_vigente">
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
                <button type="button" class="btn btn-primary" onclick="guardarFicha()">
                    <i class="ti ti-device-floppy me-1"></i>
                    Guardar Ficha Médica
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Inicializar primer contacto al abrir modal
$('#modalCrearFicha').on('shown.bs.modal', function() {
    if ($('#contactosContainerCrear').children().length === 0) {
        agregarContactoCrear();
    }
});

// Calcular IMC automáticamente
$('#crear_peso_kg, #crear_talla_cm').on('input', function() {
    const peso = parseFloat($('#crear_peso_kg').val());
    const talla = parseFloat($('#crear_talla_cm').val()) / 100;
    
    if (peso > 0 && talla > 0) {
        const imc = (peso / (talla * talla)).toFixed(1);
        $('#crear_imc').val(imc);
    } else {
        $('#crear_imc').val('');
    }
});

// Contadores de caracteres
$('#crear_alergias').on('input', function() {
    $('#contadorAlergias').text($(this).val().length);
});
$('#crear_enfermedades').on('input', function() {
    $('#contadorEnfermedades').text($(this).val().length);
});
$('#crear_medicamentos').on('input', function() {
    $('#contadorMedicamentos').text($(this).val().length);
});
$('#crear_cirugias').on('input', function() {
    $('#contadorCirugias').text($(this).val().length);
});
$('#crear_observaciones').on('input', function() {
    $('#contadorObservaciones').text($(this).val().length);
});

let contadorContactosCrear = 0;

function agregarContactoCrear() {
    contadorContactosCrear++;
    const esUnico = $('#contactosContainerCrear').children().length === 0;
    
    const html = `
        <div class="contacto-item-crear card mb-3" id="contactoCrear_${contadorContactosCrear}" style="background: #F5F5F5;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold">Contacto ${contadorContactosCrear}</h6>
                    ${!esUnico ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarContactoCrear(${contadorContactosCrear})">
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
                                   name="contacto_principal" value="${contadorContactosCrear}" 
                                   ${esUnico ? 'checked' : ''}>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#contactosContainerCrear').append(html);
}

function eliminarContactoCrear(id) {
    const total = $('#contactosContainerCrear').children().length;
    if (total > 1) {
        $(`#contactoCrear_${id}`).remove();
    } else {
        Swal.fire({
            title: 'No se puede eliminar',
            text: 'Debe haber al menos un contacto de emergencia',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

function guardarFicha() {
    const form = document.getElementById('formCrearFicha');
    
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
    if ($('#contactosContainerCrear .contacto-item-crear').length === 0) {
        Swal.fire({
            title: 'Contacto Requerido',
            text: 'Debe agregar al menos un contacto de emergencia',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    // Validar campos de contacto
    let contactosValidos = true;
    $('#contactosContainerCrear .contacto-nombre').each(function() {
        if (!$(this).val().trim()) {
            contactosValidos = false;
        }
    });
    
    if (!contactosValidos) {
        Swal.fire({
            title: 'Datos Incompletos',
            text: 'Complete todos los datos de los contactos de emergencia',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    mostrarCarga();
    
    const formData = new FormData(form);
    formData.append('accion', 'crear');
    
    fetch('modales/fichas_medicas/procesar_fichas.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            $('#modalCrearFicha').modal('hide');
            mostrarExito(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al guardar la ficha médica');
    });
}

// Limpiar formulario al cerrar modal
$('#modalCrearFicha').on('hidden.bs.modal', function() {
    document.getElementById('formCrearFicha').reset();
    document.getElementById('formCrearFicha').classList.remove('was-validated');
    $('#contactosContainerCrear').empty();
    $('#crear_imc').val('');
    contadorContactosCrear = 0;
});
</script>