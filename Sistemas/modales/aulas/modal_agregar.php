<?php
// modales/aulas/modal_agregar.php

// Obtener períodos académicos activos
try {
    $stmt_periodos = $conexion->prepare("SELECT id, nombre, anio FROM periodos_academicos WHERE activo = 1 ORDER BY actual DESC, anio DESC");
    $stmt_periodos->execute();
    $periodos = $stmt_periodos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $periodos = [];
}
?>

<!-- Modal Agregar Aula -->
<div class="modal fade" id="modalAgregarAula" tabindex="-1" aria-labelledby="modalAgregarAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarAulaLabel">
                    <i class="ti ti-building me-2"></i>
                    Nueva Aula / Sección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarAula" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica
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
                                                    <option value="<?= $nivel['id'] ?>" data-grados='<?= json_encode($nivel['grados']) ?>'>
                                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_grado" name="grado" required>
                                                <option value="">Seleccionar grado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_seccion" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_seccion" name="seccion" required>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                                <option value="E">E</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_codigo" class="form-label">Código de Sección</label>
                                            <input type="text" class="form-control" id="add_codigo" name="codigo" 
                                                   placeholder="Se generará automáticamente" readonly>
                                            <div class="form-text">Se genera automáticamente basado en nivel, grado y sección</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_periodo_academico_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_periodo_academico_id" name="periodo_academico_id" required>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>" 
                                                            <?= $periodo['anio'] == date('Y') ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración del Aula -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-building me-2"></i>
                                        Configuración del Aula
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="add_aula_asignada" class="form-label">
                                                Nombre del Aula <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_aula_asignada" 
                                                   name="aula_asignada" placeholder="Ejemplo: Aula 101, Lab. Ciencias, etc." required>
                                            <div class="form-text">Nombre específico del espacio físico</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_capacidad_maxima" class="form-label">
                                                Capacidad Máxima <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_capacidad_maxima" 
                                                   name="capacidad_maxima" min="1" max="100" value="30" required>
                                            <div class="form-text">Número máximo de estudiantes</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Vista previa del código -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-info d-none" id="preview-codigo">
                                                <strong>Código generado:</strong> <span id="codigo-preview"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuraciones Adicionales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuraciones Adicionales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="add_activo" name="activo" checked>
                                                <label class="form-check-label" for="add_activo">
                                                    <strong>Aula Activa</strong>
                                                </label>
                                                <div class="form-text">El aula estará disponible para uso inmediato</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_tipo_aula" class="form-label">Tipo de Aula</label>
                                            <select class="form-select" id="add_tipo_aula" name="tipo_aula">
                                                <option value="REGULAR">Aula Regular</option>
                                                <option value="LABORATORIO">Laboratorio</option>
                                                <option value="TALLER">Taller</option>
                                                <option value="AUDITORIO">Auditorio</option>
                                                <option value="DEPORTIVA">Instalación Deportiva</option>
                                                <option value="BIBLIOTECA">Biblioteca</option>
                                                <option value="ESPECIAL">Uso Especial</option>
                                            </select>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarAula">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Aula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cargar grados cuando cambia el nivel
    $('#add_nivel_id').on('change', function() {
        const gradosData = $(this).find(':selected').data('grados');
        const gradoSelect = $('#add_grado');
        
        gradoSelect.empty().append('<option value="">Seleccionar grado</option>');
        
        if (gradosData && Array.isArray(gradosData)) {
            gradosData.forEach(grado => {
                gradoSelect.append(`<option value="${grado.nombre}">${grado.nombre}</option>`);
            });
        }
        
        actualizarCodigoPreview();
    });

    // Actualizar código cuando cambian los campos relevantes
    $('#add_nivel_id, #add_grado, #add_seccion').on('change', actualizarCodigoPreview);

    function actualizarCodigoPreview() {
        const nivel = $('#add_nivel_id option:selected').text();
        const grado = $('#add_grado').val();
        const seccion = $('#add_seccion').val();
        
        if (nivel && grado && seccion) {
            // Generar código basado en las selecciones
            let nivelCodigo = '';
            if (nivel.includes('Inicial')) nivelCodigo = 'I';
            else if (nivel.includes('Primaria')) nivelCodigo = 'P';
            else if (nivel.includes('Secundaria')) nivelCodigo = 'S';
            
            const codigo = `${grado}${nivelCodigo}${seccion}-${new Date().getFullYear()}`;
            $('#add_codigo').val(codigo);
            $('#codigo-preview').text(codigo);
            $('#preview-codigo').removeClass('d-none');
        } else {
            $('#add_codigo').val('');
            $('#preview-codigo').addClass('d-none');
        }
    }

    // Envío del formulario
    $('#formAgregarAula').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        if (!validarFormularioAgregarAula()) {
            return false;
        }

        mostrarCarga();
        $('#btnGuardarAula').prop('disabled', true);

        fetch('modales/aulas/procesar_aulas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            $('#btnGuardarAula').prop('disabled', false);
            
            if (data.success) {
                Swal.fire({
                    title: '¡Aula Creada!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalAgregarAula').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            ocultarCarga();
            $('#btnGuardarAula').prop('disabled', false);
            mostrarError('Error al procesar la solicitud');
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarAula').on('hidden.bs.modal', function() {
        $('#formAgregarAula')[0].reset();
        $('#add_grado').empty().append('<option value="">Seleccionar grado</option>');
        $('#preview-codigo').addClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioAgregarAula() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = ['nivel_id', 'grado', 'seccion', 'aula_asignada', 'capacidad_maxima'];
    
    camposRequeridos.forEach(campo => {
        const elemento = $(`#add_${campo}`);
        if (!elemento.val() || elemento.val().trim() === '') {
            mostrarErrorCampoAula(`#add_${campo}`, 'Este campo es requerido');
            isValid = false;
        }
    });
    
    // Validar capacidad
    const capacidad = parseInt($('#add_capacidad_maxima').val());
    if (capacidad < 1 || capacidad > 100) {
        mostrarErrorCampoAula('#add_capacidad_maxima', 'La capacidad debe estar entre 1 y 100 estudiantes');
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampoAula(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}
</script>