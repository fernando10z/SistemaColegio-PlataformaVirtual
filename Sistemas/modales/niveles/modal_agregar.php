<?php
// modales/niveles/modal_agregar.php
?>
<!-- Modal Agregar Nivel -->
<div class="modal fade" id="modalAgregarNivel" tabindex="-1" aria-labelledby="modalAgregarNivelLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalAgregarNivelLabel">
                    <i class="ti ti-school me-2"></i>
                    Nuevo Nivel Educativo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarNivel" method="POST">
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
                                            <label for="add_nombre" class="form-label">
                                                Nombre del Nivel <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre" 
                                                   placeholder="Ej: Inicial, Primaria, Secundaria" required>
                                            <div class="form-text">Nombre descriptivo del nivel educativo</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_codigo" class="form-label">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="add_codigo" name="codigo" 
                                                   placeholder="Ej: INI, PRI, SEC" maxlength="10" required>
                                            <div class="form-text">Código único del nivel</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_orden" class="form-label">
                                                Orden <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_orden" name="orden" 
                                                   min="1" max="20" value="1" required>
                                            <div class="form-text">Orden de visualización</div>
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
                                    <button type="button" class="btn btn-sm btn-primary" id="btnAgregarGrado">
                                        <i class="ti ti-plus me-1"></i>
                                        Agregar Grado
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="gradosContainer">
                                        <div class="alert alert-info">
                                            <i class="ti ti-info-circle me-2"></i>
                                            Haz clic en "Agregar Grado" para configurar los grados de este nivel educativo.
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarNivel">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Nivel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para grado -->
<template id="templateGrado">
    <div class="grado-item card border mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="ti ti-grip-vertical me-2 drag-handle" style="cursor: move;"></i>
                Grado <span class="grado-numero"></span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-danger eliminar-grado">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    let gradoIndex = 0;

    // Agregar nuevo grado
    $('#btnAgregarGrado').on('click', function() {
        const template = $('#templateGrado').html();
        const gradoHtml = template.replace(/__INDEX__/g, gradoIndex);
        
        if (gradoIndex === 0) {
            $('#gradosContainer').empty();
        }
        
        $('#gradosContainer').append(gradoHtml);
        actualizarNumerosGrados();
        gradoIndex++;
    });

    // Eliminar grado
    $(document).on('click', '.eliminar-grado', function() {
        $(this).closest('.grado-item').remove();
        actualizarNumerosGrados();
        
        if ($('.grado-item').length === 0) {
            $('#gradosContainer').html(`
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Haz clic en "Agregar Grado" para configurar los grados de este nivel educativo.
                </div>
            `);
        }
    });

    // Convertir código a mayúsculas
    $(document).on('input', '#add_codigo, .grado-codigo', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Validar edades
    $(document).on('change', '.grado-edad-min, .grado-edad-max', function() {
        const container = $(this).closest('.card-body');
        const edadMin = parseInt(container.find('.grado-edad-min').val());
        const edadMax = parseInt(container.find('.grado-edad-max').val());
        
        if (edadMin && edadMax && edadMin > edadMax) {
            mostrarError('La edad mínima no puede ser mayor que la edad máxima');
            $(this).val('');
        }
    });

    // Envío del formulario
    $('#formAgregarNivel').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAgregar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Recopilar datos de grados
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
        
        formData.append('grados', JSON.stringify(grados));

        mostrarCarga();
        $('#btnGuardarNivel').prop('disabled', true);

        $.ajax({
            url: 'modales/niveles/procesar_niveles.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarNivel').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Nivel Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarNivel').modal('hide');
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
                $('#btnGuardarNivel').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarNivel').on('hidden.bs.modal', function() {
        $('#formAgregarNivel')[0].reset();
        $('#gradosContainer').html(`
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                Haz clic en "Agregar Grado" para configurar los grados de este nivel educativo.
            </div>
        `);
        gradoIndex = 0;
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    function actualizarNumerosGrados() {
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

    function validarFormularioAgregar() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar campos básicos
        if (!$('#add_nombre').val().trim()) {
            mostrarErrorCampo('#add_nombre', 'El nombre es requerido');
            isValid = false;
        }
        
        if (!$('#add_codigo').val().trim()) {
            mostrarErrorCampo('#add_codigo', 'El código es requerido');
            isValid = false;
        }
        
        // Validar que tenga al menos un grado
        if ($('.grado-item').length === 0) {
            mostrarError('Debe agregar al menos un grado al nivel educativo');
            isValid = false;
        }
        
        // Validar grados
        $('.grado-item').each(function() {
            const nombre = $(this).find('.grado-nombre').val().trim();
            const codigo = $(this).find('.grado-codigo').val().trim();
            const edadMin = $(this).find('.grado-edad-min').val();
            const edadMax = $(this).find('.grado-edad-max').val();
            
            if (!nombre || !codigo || !edadMin || !edadMax) {
                mostrarError('Todos los campos de los grados son requeridos');
                isValid = false;
                return false;
            }
            
            if (parseInt(edadMin) > parseInt(edadMax)) {
                mostrarError('La edad mínima no puede ser mayor que la máxima');
                isValid = false;
                return false;
            }
        });
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }
});
</script>