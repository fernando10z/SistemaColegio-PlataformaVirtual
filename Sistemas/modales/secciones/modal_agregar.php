<?php
// modales/secciones/modal_agregar.php
?>
<!-- Modal Agregar Sección -->
<div class="modal fade" id="modalAgregarSeccion" tabindex="-1" aria-labelledby="modalAgregarSeccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalAgregarSeccionLabel">
                    <i class="ti ti-school me-2"></i>
                    Nueva Sección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarSeccion" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información de la Sección
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="add_nivel_id" class="form-label">
                                                Nivel Educativo <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_nivel_id" name="nivel_id" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>" data-grados='<?= htmlspecialchars($nivel['grados']) ?>'>
                                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_periodo_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_periodo_id" name="periodo_academico_id" required>
                                                <option value="">Seleccionar período</option>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>" <?= $periodo['actual'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_grado" name="grado" required disabled>
                                                <option value="">Primero seleccione el nivel</option>
                                            </select>
                                            <div class="form-text">Selecciona primero el nivel educativo</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_seccion" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="add_seccion" name="seccion" 
                                                   placeholder="A, B, C..." maxlength="10" required>
                                            <div class="form-text">Ej: A, B, C, D</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="add_codigo" class="form-label">
                                                Código de Sección
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="add_codigo" name="codigo" 
                                                   placeholder="Se generará automáticamente" readonly>
                                            <div class="form-text">Se genera automáticamente: [Grado][Nivel][Sección]-[Año]</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Capacidad y Aula -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-users me-2"></i>
                                        Capacidad y Aula
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="add_capacidad_maxima" class="form-label">
                                                Capacidad Máxima <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_capacidad_maxima" name="capacidad_maxima" 
                                                   min="1" max="50" value="30" required>
                                            <div class="form-text">Número máximo de estudiantes</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_aula_asignada" class="form-label">
                                                Aula Asignada
                                            </label>
                                            <input type="text" class="form-control" id="add_aula_asignada" name="aula_asignada" 
                                                   placeholder="Ej: Aula 101, Lab. Ciencias">
                                            <div class="form-text">Opcional: Se puede asignar después</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview de la sección -->
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="ti ti-info-circle me-2"></i>
                                            Vista Previa de la Sección
                                        </h6>
                                        <div id="previewSeccion">
                                            <p class="mb-1"><strong>Código:</strong> <span id="previewCodigo">-</span></p>
                                            <p class="mb-1"><strong>Descripción:</strong> <span id="previewDescripcion">-</span></p>
                                            <p class="mb-0"><strong>Capacidad:</strong> <span id="previewCapacidad">30</span> estudiantes</p>
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
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarSeccion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    // Cargar grados cuando se selecciona el nivel
    $('#add_nivel_id').on('change', function() {
        const nivelSeleccionado = $(this).find('option:selected');
        const gradosData = nivelSeleccionado.data('grados');
        const gradoSelect = $('#add_grado');
        
        gradoSelect.empty().append('<option value="">Seleccionar grado</option>');
        
        if (gradosData) {
            try {
                const grados = typeof gradosData === 'string' ? JSON.parse(gradosData) : gradosData;
                if (Array.isArray(grados)) {
                    grados.forEach(grado => {
                        gradoSelect.append(`<option value="${grado.nombre}">${grado.nombre}</option>`);
                    });
                    gradoSelect.prop('disabled', false);
                } else {
                    gradoSelect.prop('disabled', true);
                }
            } catch (e) {
                console.error('Error parsing grados:', e);
                gradoSelect.prop('disabled', true);
            }
        } else {
            gradoSelect.prop('disabled', true);
        }
        
        actualizarPreview();
    });

    // Actualizar preview cuando cambian los campos
    $('#add_grado, #add_seccion, #add_capacidad_maxima, #add_periodo_id').on('change', actualizarPreview);

    // Convertir sección a mayúsculas
    $('#add_seccion').on('input', function() {
        $(this).val($(this).val().toUpperCase());
        actualizarPreview();
    });

    // Envío del formulario
    $('#formAgregarSeccion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAgregar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');

        mostrarCarga();
        $('#btnGuardarSeccion').prop('disabled', true);

        $.ajax({
            url: 'modales/secciones/procesar_secciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarSeccion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Sección Creada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarSeccion').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarSeccion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarSeccion').on('hidden.bs.modal', function() {
        $('#formAgregarSeccion')[0].reset();
        $('#add_grado').empty().append('<option value="">Primero seleccione el nivel</option>').prop('disabled', true);
        $('#add_codigo').val('');
        actualizarPreview();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    function actualizarPreview() {
        const nivel = $('#add_nivel_id option:selected').text();
        const grado = $('#add_grado').val();
        const seccion = $('#add_seccion').val();
        const capacidad = $('#add_capacidad_maxima').val();
        const periodo = $('#add_periodo_id option:selected');
        const anio = periodo.length ? periodo.text().match(/\((\d{4})\)/) : null;
        
        // Generar código automático
        let codigo = '';
        if (grado && nivel && seccion && anio) {
            const nivelCodigo = $('#add_nivel_id option:selected').text().substring(0, 3).toUpperCase();
            codigo = `${grado}${nivelCodigo}${seccion}-${anio[1]}`;
            $('#add_codigo').val(codigo);
        }
        
        // Actualizar preview
        $('#previewCodigo').text(codigo || '-');
        $('#previewDescripcion').text(grado && seccion ? `${grado} - Sección ${seccion} (${nivel})` : '-');
        $('#previewCapacidad').text(capacidad || '30');
    }

    function validarFormularioAgregar() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar campos requeridos
        const camposRequeridos = ['#add_nivel_id', '#add_grado', '#add_seccion', '#add_capacidad_maxima', '#add_periodo_id'];
        
        camposRequeridos.forEach(campo => {
            if (!$(campo).val()) {
                mostrarErrorCampo(campo, 'Este campo es requerido');
                isValid = false;
            }
        });
        
        // Validar capacidad
        const capacidad = parseInt($('#add_capacidad_maxima').val());
        if (capacidad < 1 || capacidad > 50) {
            mostrarErrorCampo('#add_capacidad_maxima', 'La capacidad debe estar entre 1 y 50 estudiantes');
            isValid = false;
        }
        
        // Validar sección
        const seccion = $('#add_seccion').val().trim();
        if (seccion && !/^[A-Z]+$/.test(seccion)) {
            mostrarErrorCampo('#add_seccion', 'La sección debe contener solo letras');
            isValid = false;
        }
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    // Inicializar preview
    actualizarPreview();
});
</script>