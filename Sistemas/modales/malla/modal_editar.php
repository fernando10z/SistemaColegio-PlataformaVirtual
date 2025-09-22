<?php
// modales/malla/modal_editar.php
?>
<!-- Modal Editar Asignación -->
<div class="modal fade" id="modalEditarAsignacion" tabindex="-1" aria-labelledby="modalEditarAsignacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditarAsignacionLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Asignación de Área
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarAsignacion" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
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
                                            <label for="edit_nivel_id" class="form-label">
                                                Nivel Educativo <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_nivel_id" name="nivel_id" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>" data-grados='<?= htmlspecialchars($nivel['grados']) ?>'>
                                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_grado" name="grado" required>
                                                <option value="">Seleccionar grado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_area_id" class="form-label">
                                                Área Curricular <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_area_id" name="area_id" required>
                                                <option value="">Seleccionar área</option>
                                                <?php foreach ($areas as $area): ?>
                                                    <option value="<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?> (<?= htmlspecialchars($area['codigo']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_horas_semanales" class="form-label">
                                                Horas Semanales <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="edit_horas_semanales" 
                                                       name="horas_semanales" min="1" max="10" required>
                                                <span class="input-group-text">hrs/sem</span>
                                            </div>
                                            <div class="form-text">Mínimo 1 hora, máximo 10 horas por semana</div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_periodo_academico_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_periodo_academico_id" name="periodo_academico_id" required>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>">
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-toggle-left me-2"></i>
                                        Estado de la Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_activo" name="activo" value="1" checked>
                                        <label class="form-check-label" for="edit_activo">
                                            <strong>Asignación Activa</strong>
                                        </label>
                                        <div class="form-text">
                                            Si está desactivada, no aparecerá en los reportes ni horarios activos
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
                    <button type="button" class="btn btn-outline-secondary" id="btnCompetenciasEdit">
                        <i class="ti ti-target me-2"></i>
                        Gestionar Competencias
                    </button>
                    <button type="submit" class="btn btn-info" id="btnActualizarAsignacion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variable global para almacenar el grado original
let gradoOriginal = '';

function cargarDatosEdicion(asignacion) {
    // Cargar datos básicos
    $('#edit_id').val(asignacion.id);
    $('#edit_nivel_id').val(asignacion.nivel_id);
    $('#edit_area_id').val(asignacion.area_id);
    $('#edit_horas_semanales').val(asignacion.horas_semanales);
    $('#edit_periodo_academico_id').val(asignacion.periodo_academico_id);
    $('#edit_activo').prop('checked', asignacion.activo == 1);
    
    // Guardar grado original para cargar después
    gradoOriginal = asignacion.grado;
    
    // Cargar grados para el nivel seleccionado
    cargarGradosEdicion(asignacion.nivel_id, asignacion.grado);
    
    // Configurar botón de competencias
    $('#btnCompetenciasEdit').off('click').on('click', function() {
        $('#modalEditarAsignacion').modal('hide');
        gestionarCompetencias(asignacion.id);
    });
}

function cargarGradosEdicion(nivelId, gradoSeleccionado) {
    const nivelOption = $('#edit_nivel_id option[value="' + nivelId + '"]');
    const gradosData = nivelOption.data('grados');
    const gradoSelect = $('#edit_grado');
    
    gradoSelect.empty();
    
    if (gradosData) {
        try {
            const grados = typeof gradosData === 'string' ? JSON.parse(gradosData) : gradosData;
            gradoSelect.append('<option value="">Seleccionar grado</option>');
            
            grados.forEach(function(grado) {
                const selected = grado.nombre === gradoSeleccionado ? 'selected' : '';
                gradoSelect.append(`<option value="${grado.nombre}" ${selected}>${grado.nombre}</option>`);
            });
        } catch (e) {
            console.error('Error parsing grados data:', e);
            gradoSelect.append('<option value="">Error al cargar grados</option>');
        }
    }
}

$(document).ready(function() {
    // Cargar grados según nivel seleccionado (para cambios manuales)
    $('#edit_nivel_id').on('change', function() {
        const gradosData = $(this).find(':selected').data('grados');
        const gradoSelect = $('#edit_grado');
        
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
        }
    });

    // Envío del formulario
    $('#formEditarAsignacion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarAsignacion').prop('disabled', true);

        $.ajax({
            url: 'modales/malla/procesar_malla.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarAsignacion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Asignación Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarAsignacion').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarAsignacion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarAsignacion').on('hidden.bs.modal', function() {
        $('#formEditarAsignacion')[0].reset();
        $('#edit_grado').empty();
        gradoOriginal = '';
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioEditar() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = ['#edit_nivel_id', '#edit_grado', '#edit_area_id', '#edit_horas_semanales', '#edit_periodo_academico_id'];
    
    camposRequeridos.forEach(function(campo) {
        if (!$(campo).val()) {
            $(campo).addClass('is-invalid');
            $(campo).after('<div class="invalid-feedback">Este campo es requerido</div>');
            isValid = false;
        }
    });
    
    // Validar horas semanales
    const horas = parseInt($('#edit_horas_semanales').val());
    if (horas < 1 || horas > 10) {
        $('#edit_horas_semanales').addClass('is-invalid');
        $('#edit_horas_semanales').after('<div class="invalid-feedback">Las horas deben estar entre 1 y 10</div>');
        isValid = false;
    }
    
    return isValid;
}
</script>