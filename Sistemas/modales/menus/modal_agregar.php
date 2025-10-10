<!-- modales/comedor/modal_agregar_menu.php -->
<div class="modal fade" id="modalAgregarMenu" tabindex="-1" aria-labelledby="modalAgregarMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFB3BA 0%, #FFDDA1 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarMenuLabel">
                    <i class="ti ti-chef-hat me-2"></i>
                    Crear Nuevo Menú del Día
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formAgregarMenu" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background-color: #FFF5E1;">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar me-2"></i>
                                        Información Básica del Menú
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha" class="form-label">
                                                Fecha del Menú <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha" name="fecha" required>
                                            <div class="form-text">Fecha para la cual se prepara el menú</div>
                                            <div class="invalid-feedback">Fecha requerida y debe ser futura</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="add_tipo_menu" class="form-label">
                                                Tipo de Menú <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo_menu" name="tipo_menu" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="REGULAR">Regular</option>
                                                <option value="VEGETARIANO">Vegetariano</option>
                                                <option value="ESPECIAL">Especial</option>
                                                <option value="DIETA">Dieta Especial</option>
                                            </select>
                                            <div class="invalid-feedback">Debe seleccionar un tipo de menú</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="add_precio" class="form-label">
                                                Precio (S/) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_precio" name="precio" 
                                                   step="0.01" min="0" max="100" required placeholder="0.00">
                                            <div class="form-text">Precio por menú (máximo S/ 100)</div>
                                            <div class="invalid-feedback">Precio entre S/ 0 y S/ 100</div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="add_descripcion_general" class="form-label">
                                                Descripción General
                                            </label>
                                            <textarea class="form-control" id="add_descripcion_general" 
                                                      name="descripcion_general" rows="2" 
                                                      placeholder="Descripción breve del menú del día..."
                                                      maxlength="200"></textarea>
                                            <div class="character-count text-muted">
                                                <small><span id="add_desc_count">0</span>/200 caracteres</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de Platos -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background-color: #E8F5E9;">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-tools-kitchen-2 me-2"></i>
                                        Platos del Menú
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Entrada -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-bowl me-2"></i>Entrada</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="add_entrada_nombre" class="form-label">
                                                Nombre del Plato <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_entrada_nombre" 
                                                   name="entrada_nombre" required minlength="3" maxlength="100"
                                                   placeholder="Ej: Ensalada mixta">
                                            <div class="invalid-feedback">Nombre requerido (3-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="add_entrada_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="add_entrada_calorias" 
                                                   name="entrada_calorias" min="0" max="1000" placeholder="0">
                                            <div class="form-text">Kcal (opcional)</div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="add_entrada_ingredientes" class="form-label">
                                                Ingredientes Principales
                                            </label>
                                            <input type="text" class="form-control" id="add_entrada_ingredientes" 
                                                   name="entrada_ingredientes" maxlength="200"
                                                   placeholder="Ej: Lechuga, tomate, pepino, zanahoria">
                                        </div>
                                    </div>

                                    <!-- Plato Principal -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-meat me-2"></i>Plato Principal</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="add_principal_nombre" class="form-label">
                                                Nombre del Plato <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_principal_nombre" 
                                                   name="principal_nombre" required minlength="3" maxlength="100"
                                                   placeholder="Ej: Pollo a la plancha con arroz">
                                            <div class="invalid-feedback">Nombre requerido (3-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="add_principal_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="add_principal_calorias" 
                                                   name="principal_calorias" min="0" max="2000" placeholder="0">
                                            <div class="form-text">Kcal (opcional)</div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="add_principal_ingredientes" class="form-label">
                                                Ingredientes Principales
                                            </label>
                                            <input type="text" class="form-control" id="add_principal_ingredientes" 
                                                   name="principal_ingredientes" maxlength="200"
                                                   placeholder="Ej: Pollo, arroz, verduras al vapor">
                                        </div>
                                    </div>

                                    <!-- Guarnición -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-leaf me-2"></i>Guarnición</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="add_guarnicion_nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="add_guarnicion_nombre" 
                                                   name="guarnicion_nombre" minlength="3" maxlength="100"
                                                   placeholder="Ej: Ensalada de verduras">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="add_guarnicion_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="add_guarnicion_calorias" 
                                                   name="guarnicion_calorias" min="0" max="500" placeholder="0">
                                        </div>
                                    </div>

                                    <!-- Postre -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-ice-cream me-2"></i>Postre</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="add_postre_nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="add_postre_nombre" 
                                                   name="postre_nombre" minlength="3" maxlength="100"
                                                   placeholder="Ej: Gelatina de frutas">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="add_postre_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="add_postre_calorias" 
                                                   name="postre_calorias" min="0" max="800" placeholder="0">
                                        </div>
                                    </div>

                                    <!-- Bebida -->
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-glass me-2"></i>Bebida</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="add_bebida_nombre" class="form-label">
                                                Nombre <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_bebida_nombre" 
                                                   name="bebida_nombre" required minlength="2" maxlength="100"
                                                   placeholder="Ej: Refresco de maracuyá">
                                            <div class="invalid-feedback">Nombre requerido (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="add_bebida_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="add_bebida_calorias" 
                                                   name="bebida_calorias" min="0" max="500" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Disponibilidad y Restricciones -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background-color: #E3F2FD;">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-building-store me-2"></i>
                                        Disponibilidad y Restricciones
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="add_cantidad_disponible" class="form-label">
                                                Cantidad Disponible <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_cantidad_disponible" 
                                                   name="cantidad_disponible" required min="1" max="500" placeholder="0">
                                            <div class="form-text">Porciones disponibles</div>
                                            <div class="invalid-feedback">Cantidad entre 1 y 500</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_hora_inicio" class="form-label">
                                                Hora Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="time" class="form-control" id="add_hora_inicio" 
                                                   name="hora_inicio" required value="12:00">
                                            <div class="invalid-feedback">Hora de inicio requerida</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_hora_fin" class="form-label">
                                                Hora Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="time" class="form-control" id="add_hora_fin" 
                                                   name="hora_fin" required value="14:00">
                                            <div class="invalid-feedback">Hora de fin posterior al inicio</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_limite_pedidos" class="form-label">
                                                Límite de Pedidos
                                            </label>
                                            <input type="number" class="form-control" id="add_limite_pedidos" 
                                                   name="limite_pedidos" min="1" max="500" placeholder="Sin límite">
                                            <div class="form-text">Por usuario (opcional)</div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="add_restricciones" class="form-label">
                                                Restricciones Alimentarias
                                            </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_sin_gluten" 
                                                       name="restricciones[]" value="SIN_GLUTEN">
                                                <label class="form-check-label" for="add_sin_gluten">Sin Gluten</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_sin_lactosa" 
                                                       name="restricciones[]" value="SIN_LACTOSA">
                                                <label class="form-check-label" for="add_sin_lactosa">Sin Lactosa</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_vegetariano" 
                                                       name="restricciones[]" value="VEGETARIANO">
                                                <label class="form-check-label" for="add_vegetariano">Vegetariano</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_bajo_sodio" 
                                                       name="restricciones[]" value="BAJO_SODIO">
                                                <label class="form-check-label" for="add_bajo_sodio">Bajo en Sodio</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="add_alergenos" class="form-label">
                                                Alérgenos Presentes
                                            </label>
                                            <input type="text" class="form-control" id="add_alergenos" 
                                                   name="alergenos" maxlength="200"
                                                   placeholder="Ej: Maní, huevo, leche, trigo">
                                            <div class="form-text">Separar con comas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Nutricional -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header" style="background-color: #F3E5F5;">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-pie me-2"></i>
                                        Resumen Nutricional (Calculado Automáticamente)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center" id="resumen_nutricional">
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #FFE0B2; border-radius: 8px;">
                                                <h4 class="mb-0" id="total_calorias">0</h4>
                                                <small class="text-muted">Calorías Totales</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #C5E1A5; border-radius: 8px;">
                                                <h4 class="mb-0" id="total_platos">0</h4>
                                                <small class="text-muted">Platos Incluidos</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #B3E5FC; border-radius: 8px;">
                                                <h4 class="mb-0" id="total_porciones">0</h4>
                                                <small class="text-muted">Porciones Disponibles</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #F8BBD0; border-radius: 8px;">
                                                <h4 class="mb-0" id="precio_display">S/ 0.00</h4>
                                                <small class="text-muted">Precio por Menú</small>
                                            </div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarMenu">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Menú
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Contador de caracteres
    $('#add_descripcion_general').on('input', function() {
        $('#add_desc_count').text($(this).val().length);
    });

    // Calcular totales automáticamente
    function calcularTotales() {
        let totalCalorias = 0;
        let totalPlatos = 0;
        
        const caloriasInputs = [
            '#add_entrada_calorias',
            '#add_principal_calorias',
            '#add_guarnicion_calorias',
            '#add_postre_calorias',
            '#add_bebida_calorias'
        ];
        
        caloriasInputs.forEach(input => {
            const valor = parseInt($(input).val()) || 0;
            if (valor > 0) {
                totalCalorias += valor;
                totalPlatos++;
            }
        });
        
        const cantidad = parseInt($('#add_cantidad_disponible').val()) || 0;
        const precio = parseFloat($('#add_precio').val()) || 0;
        
        $('#total_calorias').text(totalCalorias);
        $('#total_platos').text(totalPlatos);
        $('#total_porciones').text(cantidad);
        $('#precio_display').text('S/ ' + precio.toFixed(2));
    }
    
    // Actualizar totales al cambiar cualquier campo
    $('input[type="number"]').on('input', calcularTotales);
    
    // Validar horas
    $('#add_hora_inicio, #add_hora_fin').on('change', function() {
        const inicio = $('#add_hora_inicio').val();
        const fin = $('#add_hora_fin').val();
        
        if (inicio && fin && fin <= inicio) {
            $('#add_hora_fin')[0].setCustomValidity('La hora de fin debe ser posterior al inicio');
        } else {
            $('#add_hora_fin')[0].setCustomValidity('');
        }
    });
    
    // Validar fecha
    $('#add_fecha').on('change', function() {
        const fechaSeleccionada = new Date($(this).val());
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fechaSeleccionada < hoy) {
            $(this)[0].setCustomValidity('La fecha debe ser hoy o futura');
        } else {
            $(this)[0].setCustomValidity('');
        }
    });

    // Envío del formulario
    $('#formAgregarMenu').on('submit', function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        
        $('#btnGuardarMenu').prop('disabled', true).html('<i class="ti ti-loader me-2"></i> Guardando...');
        
        $.ajax({
            url: 'modales/comedor/procesar_menu.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
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
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión con el servidor',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                $('#btnGuardarMenu').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i> Crear Menú');
            }
        });
    });
});
</script>