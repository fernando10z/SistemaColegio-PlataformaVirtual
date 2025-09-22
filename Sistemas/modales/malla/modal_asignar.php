<?php
// modales/malla/modal_asignar.php
?>
<!-- Modal Asignar Área -->
<div class="modal fade" id="modalAsignarArea" tabindex="-1" aria-labelledby="modalAsignarAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAsignarAreaLabel">
                    <i class="ti ti-plus me-2"></i>
                    Asignar Área Curricular
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAsignarArea" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información de la Asignación
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
                                            <label for="add_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_grado" name="grado" required disabled>
                                                <option value="">Primero selecciona un nivel</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_area_id" class="form-label">
                                                Área Curricular <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_area_id" name="area_id" required>
                                                <option value="">Seleccionar área</option>
                                                <?php foreach ($areas as $area): ?>
                                                    <option value="<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?> (<?= htmlspecialchars($area['codigo']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_horas_semanales" class="form-label">
                                                Horas Semanales <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="add_horas_semanales" 
                                                       name="horas_semanales" min="1" max="10" value="2" required>
                                                <span class="input-group-text">hrs/sem</span>
                                            </div>
                                            <div class="form-text">Mínimo 1 hora, máximo 10 horas por semana</div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="add_periodo_academico_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_periodo_academico_id" name="periodo_academico_id" required>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>" 
                                                            <?= $periodo_actual && $periodo['id'] == $periodo_actual['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Competencias Iniciales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-target me-2"></i>
                                        Competencias del Grado (Opcional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="competencias-container">
                                        <div class="mb-3">
                                            <label class="form-label">Competencias a desarrollar</label>
                                            <div id="competencias-list">
                                                <div class="competencia-item mb-2">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="competencias[]" 
                                                               placeholder="Describe una competencia a desarrollar">
                                                        <button type="button" class="btn btn-outline-danger remove-competencia" disabled>
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-competencia">
                                                <i class="ti ti-plus me-1"></i>
                                                Agregar Competencia
                                            </button>
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Nota:</strong> Las competencias pueden agregarse o modificarse posteriormente.
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
                    <button type="submit" class="btn btn-primary" id="btnAsignarArea">
                        <i class="ti ti-device-floppy me-2"></i>
                        Asignar Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Cargar grados según nivel seleccionado
    $('#add_nivel_id').on('change', function() {
        const gradosData = $(this).find(':selected').data('grados');
        const gradoSelect = $('#add_grado');
        
        gradoSelect.empty().prop('disabled', true);
        
        if (gradosData) {
            try {
                const grados = typeof gradosData === 'string' ? JSON.parse(gradosData) : gradosData;
                gradoSelect.prop('disabled', false);
                gradoSelect.append('<option value="">Seleccionar grado</option>');
                
                grados.forEach(function(grado) {
                    gradoSelect.append(`<option value="${grado.nombre}">${grado.nombre}</option>`);
                });
            } catch (e) {
                console.error('Error parsing grados data:', e);
                gradoSelect.append('<option value="">Error al cargar grados</option>');
            }
        } else {
            gradoSelect.append('<option value="">Selecciona un nivel primero</option>');
        }
    });

    // Agregar competencia
    $('#add-competencia').on('click', function() {
        const competenciaHtml = `
            <div class="competencia-item mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" name="competencias[]" 
                           placeholder="Describe una competencia a desarrollar">
                    <button type="button" class="btn btn-outline-danger remove-competencia">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        `;
        $('#competencias-list').append(competenciaHtml);
        updateRemoveButtons();
    });

    // Remover competencia
    $(document).on('click', '.remove-competencia', function() {
        $(this).closest('.competencia-item').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const competencias = $('.competencia-item');
        if (competencias.length <= 1) {
            $('.remove-competencia').prop('disabled', true);
        } else {
            $('.remove-competencia').prop('disabled', false);
        }
    }

    // Envío del formulario
    $('#formAsignarArea').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAsignar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnAsignarArea').prop('disabled', true);

        $.ajax({
            url: 'modales/malla/procesar_malla.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnAsignarArea').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Área Asignada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAsignarArea').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnAsignarArea').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAsignarArea').on('hidden.bs.modal', function() {
        $('#formAsignarArea')[0].reset();
        $('#add_grado').empty().prop('disabled', true);
        $('#competencias-list').html(`
            <div class="competencia-item mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" name="competencias[]" 
                           placeholder="Describe una competencia a desarrollar">
                    <button type="button" class="btn btn-outline-danger remove-competencia" disabled>
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        `);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioAsignar() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = ['#add_nivel_id', '#add_grado', '#add_area_id', '#add_horas_semanales', '#add_periodo_academico_id'];
    
    camposRequeridos.forEach(function(campo) {
        if (!$(campo).val()) {
            $(campo).addClass('is-invalid');
            $(campo).after('<div class="invalid-feedback">Este campo es requerido</div>');
            isValid = false;
        }
    });
    
    // Validar horas semanales
    const horas = parseInt($('#add_horas_semanales').val());
    if (horas < 1 || horas > 10) {
        $('#add_horas_semanales').addClass('is-invalid');
        $('#add_horas_semanales').after('<div class="invalid-feedback">Las horas deben estar entre 1 y 10</div>');
        isValid = false;
    }
    
    return isValid;
}
</script>