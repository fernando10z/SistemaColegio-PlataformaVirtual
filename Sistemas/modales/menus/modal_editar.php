<!-- modales/comedor/modal_editar_menu.php -->
<div class="modal fade" id="modalEditarMenu" tabindex="-1" aria-labelledby="modalEditarMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #BAE1FF 0%, #A8D8EA 100%); color: white;">
                <h5 class="modal-title" id="modalEditarMenuLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Menú del Día
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditarMenu" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="menu_id" id="edit_menu_id">
                
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
                                            <label for="edit_fecha" class="form-label">
                                                Fecha del Menú <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                                            <div class="form-text">Fecha para la cual se prepara el menú</div>
                                            <div class="invalid-feedback">Fecha requerida</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="edit_tipo_menu" class="form-label">
                                                Tipo de Menú <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo_menu" name="tipo_menu" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="REGULAR">Regular</option>
                                                <option value="VEGETARIANO">Vegetariano</option>
                                                <option value="ESPECIAL">Especial</option>
                                                <option value="DIETA">Dieta Especial</option>
                                            </select>
                                            <div class="invalid-feedback">Debe seleccionar un tipo de menú</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="edit_precio" class="form-label">
                                                Precio (S/) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_precio" name="precio" 
                                                   step="0.01" min="0" max="100" required placeholder="0.00">
                                            <div class="form-text">Precio por menú</div>
                                            <div class="invalid-feedback">Precio entre S/ 0 y S/ 100</div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="edit_descripcion_general" class="form-label">
                                                Descripción General
                                            </label>
                                            <textarea class="form-control" id="edit_descripcion_general" 
                                                      name="descripcion_general" rows="2" 
                                                      placeholder="Descripción breve del menú del día..."
                                                      maxlength="200"></textarea>
                                            <div class="character-count text-muted">
                                                <small><span id="edit_desc_count">0</span>/200 caracteres</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de Platos (Misma estructura que agregar) -->
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
                                            <label for="edit_entrada_nombre" class="form-label">
                                                Nombre del Plato <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_entrada_nombre" 
                                                   name="entrada_nombre" required minlength="3" maxlength="100">
                                            <div class="invalid-feedback">Nombre requerido (3-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_entrada_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="edit_entrada_calorias" 
                                                   name="entrada_calorias" min="0" max="1000">
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="edit_entrada_ingredientes" class="form-label">
                                                Ingredientes Principales
                                            </label>
                                            <input type="text" class="form-control" id="edit_entrada_ingredientes" 
                                                   name="entrada_ingredientes" maxlength="200">
                                        </div>
                                    </div>

                                    <!-- Plato Principal -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-meat me-2"></i>Plato Principal</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="edit_principal_nombre" class="form-label">
                                                Nombre del Plato <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_principal_nombre" 
                                                   name="principal_nombre" required minlength="3" maxlength="100">
                                            <div class="invalid-feedback">Nombre requerido (3-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_principal_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="edit_principal_calorias" 
                                                   name="principal_calorias" min="0" max="2000">
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="edit_principal_ingredientes" class="form-label">
                                                Ingredientes Principales
                                            </label>
                                            <input type="text" class="form-control" id="edit_principal_ingredientes" 
                                                   name="principal_ingredientes" maxlength="200">
                                        </div>
                                    </div>

                                    <!-- Guarnición -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-leaf me-2"></i>Guarnición</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="edit_guarnicion_nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="edit_guarnicion_nombre" 
                                                   name="guarnicion_nombre" minlength="3" maxlength="100">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_guarnicion_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="edit_guarnicion_calorias" 
                                                   name="guarnicion_calorias" min="0" max="500">
                                        </div>
                                    </div>

                                    <!-- Postre -->
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-ice-cream me-2"></i>Postre</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="edit_postre_nombre" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="edit_postre_nombre" 
                                                   name="postre_nombre" minlength="3" maxlength="100">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_postre_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="edit_postre_calorias" 
                                                   name="postre_calorias" min="0" max="800">
                                        </div>
                                    </div>

                                    <!-- Bebida -->
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted"><i class="ti ti-glass me-2"></i>Bebida</h6>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="edit_bebida_nombre" class="form-label">
                                                Nombre <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_bebida_nombre" 
                                                   name="bebida_nombre" required minlength="2" maxlength="100">
                                            <div class="invalid-feedback">Nombre requerido (2-100 caracteres)</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_bebida_calorias" class="form-label">Calorías</label>
                                            <input type="number" class="form-control" id="edit_bebida_calorias" 
                                                   name="bebida_calorias" min="0" max="500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Disponibilidad -->
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
                                            <label for="edit_cantidad_disponible" class="form-label">
                                                Cantidad Disponible <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_cantidad_disponible" 
                                                   name="cantidad_disponible" required min="0" max="500">
                                            <div class="form-text">Porciones disponibles</div>
                                            <div class="invalid-feedback">Cantidad entre 0 y 500</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="edit_cantidad_reservada" class="form-label">
                                                Cantidad Reservada
                                            </label>
                                            <input type="number" class="form-control" id="edit_cantidad_reservada" 
                                                   name="cantidad_reservada" min="0" readonly>
                                            <div class="form-text">Solo lectura</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="edit_hora_inicio" class="form-label">
                                                Hora Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="time" class="form-control" id="edit_hora_inicio" 
                                                   name="hora_inicio" required>
                                            <div class="invalid-feedback">Hora de inicio requerida</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="edit_hora_fin" class="form-label">
                                                Hora Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="time" class="form-control" id="edit_hora_fin" 
                                                   name="hora_fin" required>
                                            <div class="invalid-feedback">Hora de fin posterior al inicio</div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Estado del Menú</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="estado_menu" 
                                                       id="edit_estado_disponible" value="DISPONIBLE" required>
                                                <label class="btn btn-outline-success" for="edit_estado_disponible">
                                                    <i class="ti ti-check me-1"></i>Disponible
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="estado_menu" 
                                                       id="edit_estado_agotado" value="AGOTADO">
                                                <label class="btn btn-outline-warning" for="edit_estado_agotado">
                                                    <i class="ti ti-alert-circle me-1"></i>Agotado
                                                </label>
                                                
                                                <input type="radio" class="btn-check" name="estado_menu" 
                                                       id="edit_estado_cancelado" value="CANCELADO">
                                                <label class="btn btn-outline-danger" for="edit_estado_cancelado">
                                                    <i class="ti ti-x me-1"></i>Cancelado
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="edit_restricciones" class="form-label">
                                                Restricciones Alimentarias
                                            </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_sin_gluten" 
                                                       name="restricciones[]" value="SIN_GLUTEN">
                                                <label class="form-check-label" for="edit_sin_gluten">Sin Gluten</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_sin_lactosa" 
                                                       name="restricciones[]" value="SIN_LACTOSA">
                                                <label class="form-check-label" for="edit_sin_lactosa">Sin Lactosa</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_vegetariano" 
                                                       name="restricciones[]" value="VEGETARIANO">
                                                <label class="form-check-label" for="edit_vegetariano">Vegetariano</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_bajo_sodio" 
                                                       name="restricciones[]" value="BAJO_SODIO">
                                                <label class="form-check-label" for="edit_bajo_sodio">Bajo en Sodio</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="edit_alergenos" class="form-label">
                                                Alérgenos Presentes
                                            </label>
                                            <input type="text" class="form-control" id="edit_alergenos" 
                                                   name="alergenos" maxlength="200">
                                            <div class="form-text">Separar con comas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header" style="background-color: #F3E5F5;">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-pie me-2"></i>
                                        Resumen Nutricional
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center" id="edit_resumen_nutricional">
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #FFE0B2; border-radius: 8px;">
                                                <h4 class="mb-0" id="edit_total_calorias">0</h4>
                                                <small class="text-muted">Calorías Totales</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #C5E1A5; border-radius: 8px;">
                                                <h4 class="mb-0" id="edit_total_platos">0</h4>
                                                <small class="text-muted">Platos Incluidos</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #B3E5FC; border-radius: 8px;">
                                                <h4 class="mb-0" id="edit_total_porciones">0</h4>
                                                <small class="text-muted">Disponibles</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3" style="background-color: #F8BBD0; border-radius: 8px;">
                                                <h4 class="mb-0" id="edit_precio_display">S/ 0.00</h4>
                                                <small class="text-muted">Precio</small>
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
                    <button type="submit" class="btn btn-primary" id="btnActualizarMenu">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Menú
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function cargarDatosEdicionMenu(menu) {
        console.log('Cargando menú:', menu); // Para debug
        
        // Información básica
        $('#edit_menu_id').val(menu.id);
        $('#edit_fecha').val(menu.fecha);
        
        // Configuración
        const config = menu.configuracion || {};
        $('#edit_precio').val(config.precio || 0);
        $('#edit_tipo_menu').val(config.tipo_menu || 'REGULAR');
        
        // Descripción está en detalles, no en configuración
        const detalles = menu.detalles || {};
        $('#edit_descripcion_general').val(detalles.descripcion_general || '');
        $('#edit_desc_count').text((detalles.descripcion_general || '').length);
        
        // Limpiar campos de platos primero
        $('#edit_entrada_nombre, #edit_entrada_calorias, #edit_entrada_ingredientes').val('');
        $('#edit_principal_nombre, #edit_principal_calorias, #edit_principal_ingredientes').val('');
        $('#edit_guarnicion_nombre, #edit_guarnicion_calorias').val('');
        $('#edit_postre_nombre, #edit_postre_calorias').val('');
        $('#edit_bebida_nombre, #edit_bebida_calorias').val('');
        
        // Cargar platos desde el array
        if (detalles.platos && Array.isArray(detalles.platos)) {
            detalles.platos.forEach(function(plato) {
                switch(plato.tipo) {
                    case 'ENTRADA':
                        $('#edit_entrada_nombre').val(plato.nombre || '');
                        $('#edit_entrada_calorias').val(plato.calorias || 0);
                        $('#edit_entrada_ingredientes').val(plato.ingredientes || '');
                        break;
                        
                    case 'PRINCIPAL':
                        $('#edit_principal_nombre').val(plato.nombre || '');
                        $('#edit_principal_calorias').val(plato.calorias || 0);
                        $('#edit_principal_ingredientes').val(plato.ingredientes || '');
                        break;
                        
                    case 'GUARNICION':
                        $('#edit_guarnicion_nombre').val(plato.nombre || '');
                        $('#edit_guarnicion_calorias').val(plato.calorias || 0);
                        break;
                        
                    case 'POSTRE':
                        $('#edit_postre_nombre').val(plato.nombre || '');
                        $('#edit_postre_calorias').val(plato.calorias || 0);
                        break;
                        
                    case 'BEBIDA':
                        $('#edit_bebida_nombre').val(plato.nombre || '');
                        $('#edit_bebida_calorias').val(plato.calorias || 0);
                        break;
                }
            });
        }
        
        // Disponibilidad
        const disponibilidad = menu.disponibilidad || {};
        $('#edit_cantidad_disponible').val(disponibilidad.porciones_disponibles || 0);
        $('#edit_cantidad_reservada').val(disponibilidad.porciones_reservadas || 0);
        
        // Horas están en configuración, no en disponibilidad
        $('#edit_hora_inicio').val(config.hora_inicio || '12:00');
        $('#edit_hora_fin').val(config.hora_fin || '14:00');
        
        // Estado del menú
        const estado = disponibilidad.estado || 'DISPONIBLE';
        $('input[name="estado_menu"]').prop('checked', false);
        $('input[name="estado_menu"][value="' + estado + '"]').prop('checked', true);
        
        // Restricciones
        $('input[name="restricciones[]"]').prop('checked', false);
        if (config.restricciones && Array.isArray(config.restricciones)) {
            config.restricciones.forEach(function(restriccion) {
                $('input[name="restricciones[]"][value="' + restriccion + '"]').prop('checked', true);
            });
        }
        
        // Alérgenos
        let alergenosStr = '';
        if (config.alergenos && Array.isArray(config.alergenos)) {
            alergenosStr = config.alergenos.join(', ');
        }
        $('#edit_alergenos').val(alergenosStr);
        
        // Calcular totales
        calcularTotalesEdicion();
    }

    function calcularTotalesEdicion() {
        let totalCalorias = 0;
        let totalPlatos = 0;
        
        const caloriasInputs = [
            '#edit_entrada_calorias',
            '#edit_principal_calorias',
            '#edit_guarnicion_calorias',
            '#edit_postre_calorias',
            '#edit_bebida_calorias'
        ];
        
        caloriasInputs.forEach(input => {
            const valor = parseInt($(input).val()) || 0;
            if (valor > 0) {
                totalCalorias += valor;
                totalPlatos++;
            }
        });
        
        const cantidad = parseInt($('#edit_cantidad_disponible').val()) || 0;
        const precio = parseFloat($('#edit_precio').val()) || 0;
        
        $('#edit_total_calorias').text(totalCalorias);
        $('#edit_total_platos').text(totalPlatos);
        $('#edit_total_porciones').text(cantidad);
        $('#edit_precio_display').text('S/ ' + precio.toFixed(2));
    }

    $(document).ready(function() {
        // Contador de caracteres
        $('#edit_descripcion_general').on('input', function() {
            $('#edit_desc_count').text($(this).val().length);
        });
        
        // Actualizar totales al cambiar campos
        $('#formEditarMenu input[type="number"]').on('input', calcularTotalesEdicion);
        
        // Validar horas
        $('#edit_hora_inicio, #edit_hora_fin').on('change', function() {
            const inicio = $('#edit_hora_inicio').val();
            const fin = $('#edit_hora_fin').val();
            
            if (inicio && fin && fin <= inicio) {
                $('#edit_hora_fin')[0].setCustomValidity('La hora de fin debe ser posterior al inicio');
            } else {
                $('#edit_hora_fin')[0].setCustomValidity('');
            }
        });

        // Envío del formulario
        $('#formEditarMenu').on('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            $('#btnActualizarMenu').prop('disabled', true).html('<i class="ti ti-loader me-2"></i> Actualizando...');
            
            $.ajax({
                url: 'modales/menus/procesar_menu.php',
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
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error de conexión con el servidor',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                },
                complete: function() {
                    $('#btnActualizarMenu').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i> Actualizar Menú');
                }
            });
        });
    });
</script>