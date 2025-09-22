<?php
// modales/areas/modal_agregar.php
?>
<!-- Modal Agregar Área Curricular -->
<div class="modal fade" id="modalAgregarArea" tabindex="-1" aria-labelledby="modalAgregarAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarAreaLabel">
                    <i class="ti ti-target me-2"></i>
                    Nueva Área Curricular
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarArea" method="POST">
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
                                        <div class="col-md-8 mb-3">
                                            <label for="add_nombre" class="form-label">
                                                Nombre del Área <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre" 
                                                   placeholder="Ej: Matemática, Comunicación, Ciencia y Tecnología" required>
                                            <div class="form-text">Nombre oficial del área curricular</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_codigo" class="form-label">
                                                Código <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control text-uppercase" id="add_codigo" name="codigo" 
                                                   placeholder="Ej: MAT, COM, CYT" maxlength="10" required>
                                            <div class="form-text">Código único del área</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción del área curricular y sus objetivos"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración Inicial de Competencias -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-target me-2"></i>
                                        Configuración Inicial de Competencias
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Opcional:</strong> Puedes configurar las competencias básicas ahora o hacerlo más tarde 
                                        desde la gestión de competencias.
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="add_usar_predefinidas" class="form-label">
                                                <input type="checkbox" id="add_usar_predefinidas" class="form-check-input me-2">
                                                Usar competencias predefinidas
                                            </label>
                                            <div class="form-text">Carga automáticamente las competencias del DCN según el código del área</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_aplicar_niveles" class="form-label">Aplicar a niveles</label>
                                            <div class="border rounded p-2">
                                                <?php foreach ($niveles as $nivel): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input nivel-checkbox" type="checkbox" 
                                                               value="<?= $nivel['id'] ?>" id="nivel_<?= $nivel['id'] ?>">
                                                        <label class="form-check-label" for="nivel_<?= $nivel['id'] ?>">
                                                            <?= htmlspecialchars($nivel['nombre']) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preview de competencias predefinidas -->
                                    <div id="preview-competencias" style="display: none;">
                                        <h6 class="mt-3 mb-2">Vista previa de competencias predefinidas:</h6>
                                        <div id="competencias-preview-content" class="border rounded p-3 bg-light">
                                            <!-- Se carga dinámicamente -->
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarArea">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    // Convertir código a mayúsculas
    $('#add_codigo').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Preview de competencias predefinidas
    $('#add_usar_predefinidas, #add_codigo').on('change input', function() {
        mostrarPreviewCompetencias();
    });

    // Envío del formulario
    $('#formAgregarArea').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAgregarArea()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Agregar configuración de competencias
        if ($('#add_usar_predefinidas').is(':checked')) {
            const nivelesSeleccionados = [];
            $('.nivel-checkbox:checked').each(function() {
                nivelesSeleccionados.push($(this).val());
            });
            formData.append('usar_predefinidas', '1');
            formData.append('niveles_aplicar', JSON.stringify(nivelesSeleccionados));
        }

        mostrarCarga();
        $('#btnGuardarArea').prop('disabled', true);

        $.ajax({
            url: 'modales/areas/procesar_areas.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarArea').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Área Creada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarArea').modal('hide');
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
                $('#btnGuardarArea').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarArea').on('hidden.bs.modal', function() {
        $('#formAgregarArea')[0].reset();
        $('#preview-competencias').hide();
        $('.nivel-checkbox').prop('checked', false);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    function mostrarPreviewCompetencias() {
        const codigo = $('#add_codigo').val().toUpperCase();
        const usarPredefinidas = $('#add_usar_predefinidas').is(':checked');
        
        if (usarPredefinidas && codigo && competenciasPredefinidas[codigo]) {
            const competencias = competenciasPredefinidas[codigo];
            let html = '<h6>Competencias para ' + codigo + ':</h6><ul>';
            
            competencias.forEach(function(competencia) {
                html += '<li>' + competencia + '</li>';
            });
            
            html += '</ul>';
            html += '<small class="text-muted">Estas competencias se aplicarán a todos los grados de los niveles seleccionados</small>';
            
            $('#competencias-preview-content').html(html);
            $('#preview-competencias').show();
        } else {
            $('#preview-competencias').hide();
        }
    }

    function validarFormularioAgregarArea() {
        let isValid = true;
        
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validar nombre
        if (!$('#add_nombre').val().trim()) {
            mostrarErrorCampo('#add_nombre', 'El nombre es requerido');
            isValid = false;
        }
        
        // Validar código
        const codigo = $('#add_codigo').val().trim();
        if (!codigo) {
            mostrarErrorCampo('#add_codigo', 'El código es requerido');
            isValid = false;
        } else if (codigo.length < 2) {
            mostrarErrorCampo('#add_codigo', 'El código debe tener al menos 2 caracteres');
            isValid = false;
        }
        
        // Validar niveles si usa predefinidas
        if ($('#add_usar_predefinidas').is(':checked') && $('.nivel-checkbox:checked').length === 0) {
            mostrarError('Debes seleccionar al menos un nivel para aplicar las competencias predefinidas');
            isValid = false;
        }
        
        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }
});
</script>