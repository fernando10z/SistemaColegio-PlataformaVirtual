<?php
// modales/niveles/modal_editar.php
?>
<!-- Modal Editar Nivel -->
<div class="modal fade" id="modalEditarNivel" tabindex="-1" aria-labelledby="modalEditarNivelLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-dark">
                <h5 class="modal-title" id="modalEditarNivelLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Nivel Educativo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarNivel" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
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
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Nivel <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" 
                                                   placeholder="Ej: Inicial, Primaria, Secundaria" required>
                                            <div class="form-text">Nombre descriptivo del nivel educativo</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_codigo" class="form-label">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="edit_codigo" name="codigo" 
                                                   placeholder="Ej: INI, PRI, SEC" maxlength="10" required>
                                            <div class="form-text">Código único del nivel</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_orden" class="form-label">
                                                Orden <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_orden" name="orden" 
                                                   min="1" max="20" required>
                                            <div class="form-text">Orden de visualización</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_activo" class="form-label">Estado</label>
                                            <select class="form-select" id="edit_activo" name="activo">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas del Nivel -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Estadísticas del Nivel
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-primary mb-1" id="edit_total_secciones">0</h4>
                                                <small class="text-muted">Secciones</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-success mb-1" id="edit_estudiantes_activos">0</h4>
                                                <small class="text-muted">Estudiantes Activos</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-info mb-1" id="edit_total_grados">0</h4>
                                                <small class="text-muted">Grados</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-warning mb-1" id="edit_rango_edades">-</h4>
                                                <small class="text-muted">Rango Edades</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Grados -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-list me-2"></i>
                                        Grados del Nivel
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnAgregarGradoEdit">
                                        <i class="ti ti-plus me-1"></i>
                                        Agregar Grado
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        <strong>Atención:</strong> Modificar los grados puede afectar a estudiantes ya matriculados. 
                                        Verifica las matrículas antes de realizar cambios importantes.
                                    </div>
                                    <div id="editGradosContainer">
                                        <!-- Grados se cargarán dinámicamente -->
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
                    <button type="button" class="btn btn-info" id="btnValidarCambios">
                        <i class="ti ti-check me-2"></i>
                        Validar Cambios
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarNivel">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Nivel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para grado (edición) -->
<template id="templateGradoEdit">
    <div class="grado-item card border mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="ti ti-grip-vertical me-2 drag-handle" style="cursor: move;"></i>
                Grado <span class="grado-numero"></span>
                <span class="badge bg-info ms-2 grado-estudiantes" style="display: none;">0 estudiantes</span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-danger eliminar-grado-edit">
                <i class="ti ti-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombre del Grado <span class="text-danger">*</span></label>
                    <input type="text" class="form-control grado-nombre" name="grados[__INDEX__][nombre]" 
                           placeholder="Ej: 1ro, 2do, 3ro" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase grado-codigo" name="grados[__INDEX__][codigo]" 
                           placeholder="Ej: 1P, 2P, 3P" maxlength="5" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Edad Mín <span class="text-danger">*</span></label>
                    <input type="number" class="form-control grado-edad-min" name="grados[__INDEX__][edad_min]" 
                           min="3" max="18" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Edad Máx <span class="text-danger">*</span></label>
                    <input type="number" class="form-control grado-edad-max" name="grados[__INDEX__][edad_max]" 
                           min="3" max="18" required>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
$(document).ready(function() {
    let editGradoIndex = 0;

    // Agregar nuevo grado en edición
    $('#btnAgregarGradoEdit').on('click', function() {
        const template = $('#templateGradoEdit').html();
        const gradoHtml = template.replace(/__INDEX__/g, editGradoIndex);
        
        $('#editGradosContainer').append(gradoHtml);
        actualizarNumerosGradosEdit();
        editGradoIndex++;
    });

    // Eliminar grado en edición
    $(document).on('click', '.eliminar-grado-edit', function() {
        const gradoItem = $(this).closest('.grado-item');
        const estudiantesCount = gradoItem.find('.grado-estudiantes').text();
        
        if (estudiantesCount && estudiantesCount !== '0 estudiantes') {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Este grado tiene <strong>${estudiantesCount}</strong>.<br>
                       Eliminar el grado puede afectar las matrículas existentes.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    gradoItem.remove();
                    actualizarNumerosGradosEdit();
                }
            });
        } else {
            gradoItem.remove();
            actualizarNumerosGradosEdit();
        }
    });

    // Validar cambios
    $('#btnValidarCambios').on('click', function() {
        const nivelId = $('#edit_id').val();
        
        mostrarCarga();
        
        $.ajax({
            url: 'modales/niveles/procesar_niveles.php',
            type: 'POST',
            data: {
                accion: 'validar_cambios',
                id: nivelId,
                grados: JSON.stringify(recopilarGradosEdit())
            },
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                
                if (response.success) {
                    let alertType = 'success';
                    let alertTitle = 'Validación Correcta';
                    
                    if (response.advertencias && response.advertencias.length > 0) {
                        alertType = 'warning';
                        alertTitle = 'Validación con Advertencias';
                    }
                    
                    let message = '';
                    if (response.advertencias) {
                        message += '<ul class="text-start">';
                        response.advertencias.forEach(adv => {
                            message += `<li>${adv}</li>`;
                        });
                        message += '</ul>';
                    }
                    
                    if (response.ok) {
                        message += '<p class="text-success"><i class="ti ti-check me-2"></i>Todos los cambios son válidos</p>';
                    }
                    
                    Swal.fire({
                        title: alertTitle,
                        html: message || 'Los cambios propuestos son válidos',
                        icon: alertType,
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    Swal.fire({
                        title: 'Errores de Validación',
                        html: response.errores ? response.errores.join('<br>') : response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                mostrarError('Error al validar los cambios');
            }
        });
    });

    // Envío del formulario de edición
    $('#formEditarNivel').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'editar');
        formData.append('grados', JSON.stringify(recopilarGradosEdit()));

        mostrarCarga();
        $('#btnActualizarNivel').prop('disabled', true);

        $.ajax({
            url: 'modales/niveles/procesar_niveles.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarNivel').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Nivel Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarNivel').modal('hide');
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
                $('#btnActualizarNivel').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Convertir código a mayúsculas
    $(document).on('input', '#edit_codigo, .grado-codigo', function() {
        $(this).val($(this).val().toUpperCase());
    });

    function actualizarNumerosGradosEdit() {
        $('.grado-item').each(function(index) {
            $(this).find('.grado-numero').text(index + 1);
            
            // Actualizar índices de campos
            $(this).find('input').each(function() {
                const name = $(this).attr('name');
                if (name && name.includes('[')) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    function recopilarGradosEdit() {
        const grados = [];
        $('.grado-item').each(function() {
            const grado = {
                nombre: $(this).find('.grado-nombre').val(),
                codigo: $(this).find('.grado-codigo').val(),
                edad_min: parseInt($(this).find('.grado-edad-min').val()),
                edad_max: parseInt($(this).find('.grado-edad-max').val())
            };
            grados.push(grado);
        });
        return grados;
    }

    function validarFormularioEditar() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar campos básicos
        if (!$('#edit_nombre').val().trim()) {
            mostrarErrorCampo('#edit_nombre', 'El nombre es requerido');
            isValid = false;
        }
        
        if (!$('#edit_codigo').val().trim()) {
            mostrarErrorCampo('#edit_codigo', 'El código es requerido');
            isValid = false;
        }
        
        // Validar que tenga al menos un grado
        if ($('.grado-item').length === 0) {
            mostrarError('Debe tener al menos un grado configurado');
            isValid = false;
        }
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    // Función global para cargar datos de edición
    window.cargarDatosEdicionNivel = function(nivel) {
        $('#edit_id').val(nivel.id);
        $('#edit_nombre').val(nivel.nombre);
        $('#edit_codigo').val(nivel.codigo);
        $('#edit_orden').val(nivel.orden);
        $('#edit_activo').val(nivel.activo);
        
        // Cargar estadísticas
        $('#edit_total_secciones').text(nivel.total_secciones || 0);
        $('#edit_estudiantes_activos').text(nivel.estudiantes_activos || 0);
        
        // Cargar grados
        editGradoIndex = 0;
        $('#editGradosContainer').empty();
        
        if (nivel.grados && nivel.grados.length > 0) {
            $('#edit_total_grados').text(nivel.grados.length);
            
            const edades = [];
            nivel.grados.forEach(function(grado, index) {
                const template = $('#templateGradoEdit').html();
                const gradoHtml = template.replace(/__INDEX__/g, index);
                $('#editGradosContainer').append(gradoHtml);
                
                const gradoItem = $('.grado-item').last();
                gradoItem.find('.grado-nombre').val(grado.nombre);
                gradoItem.find('.grado-codigo').val(grado.codigo);
                gradoItem.find('.grado-edad-min').val(grado.edad_min);
                gradoItem.find('.grado-edad-max').val(grado.edad_max);
                
                if (grado.edad_min) edades.push(grado.edad_min);
                if (grado.edad_max) edades.push(grado.edad_max);
                
                editGradoIndex++;
            });
            
            // Mostrar rango de edades
            if (edades.length > 0) {
                const minEdad = Math.min(...edades);
                const maxEdad = Math.max(...edades);
                $('#edit_rango_edades').text(`${minEdad} - ${maxEdad} años`);
            }
            
            actualizarNumerosGradosEdit();
        } else {
            $('#edit_total_grados').text(0);
            $('#edit_rango_edades').text('-');
        }
    };
});
</script>