<?php
// modales/aulas/modal_editar.php
?>

<!-- Modal Editar Aula -->
<div class="modal fade" id="modalEditarAula" tabindex="-1" aria-labelledby="modalEditarAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarAulaLabel">
                    <i class="ti ti-building-store me-2"></i>
                    Editar Aula / Sección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarAula" method="POST">
                <input type="hidden" id="edit_aula_id" name="aula_id">
                
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
                                            <label for="edit_nivel_id" class="form-label">
                                                Nivel Educativo <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_nivel_id" name="nivel_id" required>
                                                <option value="">Seleccionar nivel</option>
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <option value="<?= $nivel['id'] ?>" data-grados='<?= json_encode($nivel['grados']) ?>'>
                                                        <?= htmlspecialchars($nivel['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_grado" class="form-label">
                                                Grado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_grado" name="grado" required>
                                                <option value="">Seleccionar grado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_seccion" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_seccion" name="seccion" required>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                                <option value="E">E</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_codigo" class="form-label">Código de Sección</label>
                                            <input type="text" class="form-control" id="edit_codigo" name="codigo" readonly>
                                            <div class="form-text">Se genera automáticamente</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
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
                                            <label for="edit_aula_asignada" class="form-label">
                                                Nombre del Aula <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_aula_asignada" 
                                                   name="aula_asignada" placeholder="Ejemplo: Aula 101, Lab. Ciencias, etc." required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_capacidad_maxima" class="form-label">
                                                Capacidad Máxima <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_capacidad_maxima" 
                                                   name="capacidad_maxima" min="1" max="100" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Estadísticas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Estado y Configuración
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_activo" class="form-label">Estado del Aula</label>
                                            <select class="form-select" id="edit_activo" name="activo">
                                                <option value="1">Activa</option>
                                                <option value="0">Inactiva</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_tipo_aula" class="form-label">Tipo de Aula</label>
                                            <select class="form-select" id="edit_tipo_aula" name="tipo_aula">
                                                <option value="REGULAR">Aula Regular</option>
                                                <option value="LABORATORIO">Laboratorio</option>
                                                <option value="TALLER">Taller</option>
                                                <option value="AUDITORIO">Auditorio</option>
                                                <option value="DEPORTIVA">Instalación Deportiva</option>
                                                <option value="BIBLIOTECA">Biblioteca</option>
                                                <option value="ESPECIAL">Uso Especial</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="text-center">
                                                <label class="form-label">Estudiantes Actuales</label>
                                                <div class="fs-3 fw-bold text-primary" id="estudiantes_actuales">0</div>
                                                <small class="text-muted">matriculados</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-square me-2"></i>
                                        Información del Sistema
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <strong>ID Sistema:</strong> 
                                                <span id="info_id_sistema">-</span>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <strong>Código Actual:</strong> 
                                                <span id="info_codigo_actual">-</span>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <strong>Ocupación:</strong> 
                                                <span id="info_porcentaje_ocupacion" class="badge bg-secondary">0%</span>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <strong>Estado:</strong> 
                                                <span id="info_estado_ocupacion" class="badge bg-secondary">-</span>
                                            </small>
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
                    <button type="button" class="btn btn-info me-2" onclick="verEstudiantesAulaEditar()">
                        <i class="ti ti-users me-2"></i>
                        Ver Estudiantes
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarAula">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Aula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cargar grados cuando cambia el nivel en edición
    $('#edit_nivel_id').on('change', function() {
        const gradosData = $(this).find(':selected').data('grados');
        const gradoSelect = $('#edit_grado');
        const gradoActual = gradoSelect.data('current-value') || '';
        
        gradoSelect.empty().append('<option value="">Seleccionar grado</option>');
        
        if (gradosData && Array.isArray(gradosData)) {
            gradosData.forEach(grado => {
                const selected = grado.nombre === gradoActual ? 'selected' : '';
                gradoSelect.append(`<option value="${grado.nombre}" ${selected}>${grado.nombre}</option>`);
            });
        }
        
        actualizarCodigoPreviewEdit();
    });

    // Actualizar código cuando cambian los campos relevantes en edición
    $('#edit_nivel_id, #edit_grado, #edit_seccion').on('change', actualizarCodigoPreviewEdit);

    function actualizarCodigoPreviewEdit() {
        const nivel = $('#edit_nivel_id option:selected').text();
        const grado = $('#edit_grado').val();
        const seccion = $('#edit_seccion').val();
        
        if (nivel && grado && seccion) {
            let nivelCodigo = '';
            if (nivel.includes('Inicial')) nivelCodigo = 'I';
            else if (nivel.includes('Primaria')) nivelCodigo = 'P';
            else if (nivel.includes('Secundaria')) nivelCodigo = 'S';
            
            const codigo = `${grado}${nivelCodigo}${seccion}-${new Date().getFullYear()}`;
            $('#edit_codigo').val(codigo);
        }
    }

    // Envío del formulario de edición
    $('#formEditarAula').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        if (!validarFormularioEditarAula()) {
            return false;
        }

        mostrarCarga();
        $('#btnActualizarAula').prop('disabled', true);

        fetch('modales/aulas/procesar_aulas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            $('#btnActualizarAula').prop('disabled', false);
            
            if (data.success) {
                Swal.fire({
                    title: '¡Aula Actualizada!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalEditarAula').modal('hide');
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
            $('#btnActualizarAula').prop('disabled', false);
            mostrarError('Error al procesar la solicitud');
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarAula').on('hidden.bs.modal', function() {
        $('#formEditarAula')[0].reset();
        $('#edit_grado').empty().append('<option value="">Seleccionar grado</option>');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

// Función para cargar datos de edición del aula (llamada desde el archivo principal)
function cargarDatosEdicionAula(aula) {
    // Datos básicos
    $('#edit_aula_id').val(aula.id);
    $('#edit_nivel_id').val(aula.nivel_id);
    $('#edit_seccion').val(aula.seccion);
    $('#edit_codigo').val(aula.codigo);
    $('#edit_periodo_academico_id').val(aula.periodo_academico_id);
    $('#edit_aula_asignada').val(aula.aula_asignada);
    $('#edit_capacidad_maxima').val(aula.capacidad_maxima);
    $('#edit_activo').val(aula.activo ? '1' : '0');
    
    // Cargar grados disponibles y seleccionar el actual
    const nivelSelect = $('#edit_nivel_id');
    const gradosData = nivelSelect.find(':selected').data('grados');
    const gradoSelect = $('#edit_grado');
    
    // Almacenar el grado actual
    gradoSelect.data('current-value', aula.grado);
    
    // Cargar opciones de grado
    gradoSelect.empty().append('<option value="">Seleccionar grado</option>');
    
    if (gradosData && Array.isArray(gradosData)) {
        gradosData.forEach(grado => {
            const selected = grado.nombre === aula.grado ? 'selected' : '';
            gradoSelect.append(`<option value="${grado.nombre}" ${selected}>${grado.nombre}</option>`);
        });
    }
    
    // Tipo de aula (si existe en los datos)
    if (aula.tipo_aula) {
        $('#edit_tipo_aula').val(aula.tipo_aula);
    }
    
    // Información estadística
    $('#estudiantes_actuales').text(aula.estudiantes_matriculados || 0);
    
    // Información del sistema
    $('#info_id_sistema').text(aula.id);
    $('#info_codigo_actual').text(aula.codigo || 'Sin código');
    
    // Calcular porcentaje de ocupación
    const porcentajeOcupacion = aula.capacidad_maxima > 0 ? 
        Math.round((aula.estudiantes_matriculados / aula.capacidad_maxima) * 100) : 0;
    
    $('#info_porcentaje_ocupacion')
        .removeClass('bg-secondary bg-success bg-warning bg-danger')
        .addClass(porcentajeOcupacion > 80 ? 'bg-danger' : porcentajeOcupacion > 60 ? 'bg-warning' : 'bg-success')
        .text(porcentajeOcupacion + '%');
    
    // Estado de ocupación
    let estadoOcupacion = 'DISPONIBLE';
    let estadoClass = 'bg-success';
    
    if (aula.estudiantes_matriculados >= aula.capacidad_maxima) {
        estadoOcupacion = 'COMPLETA';
        estadoClass = 'bg-danger';
    } else if (aula.estudiantes_matriculados > 0) {
        estadoOcupacion = 'OCUPADA';
        estadoClass = 'bg-warning';
    }
    
    $('#info_estado_ocupacion')
        .removeClass('bg-secondary bg-success bg-warning bg-danger')
        .addClass(estadoClass)
        .text(estadoOcupacion);
}

function validarFormularioEditarAula() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = ['nivel_id', 'grado', 'seccion', 'aula_asignada', 'capacidad_maxima'];
    
    camposRequeridos.forEach(campo => {
        const elemento = $(`#edit_${campo}`);
        if (!elemento.val() || elemento.val().trim() === '') {
            mostrarErrorCampoAulaEdit(`#edit_${campo}`, 'Este campo es requerido');
            isValid = false;
        }
    });
    
    // Validar capacidad
    const capacidad = parseInt($('#edit_capacidad_maxima').val());
    if (capacidad < 1 || capacidad > 100) {
        mostrarErrorCampoAulaEdit('#edit_capacidad_maxima', 'La capacidad debe estar entre 1 y 100 estudiantes');
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampoAulaEdit(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function verEstudiantesAulaEditar() {
    const aulaId = $('#edit_aula_id').val();
    if (aulaId) {
        verEstudiantes(aulaId);
    }
}
</script>