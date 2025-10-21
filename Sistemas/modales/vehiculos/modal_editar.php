<!-- Modal Editar Vehículo -->
<div class="modal fade" id="modalEditarVehiculo" tabindex="-1" aria-labelledby="modalEditarVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFE5B4 0%, #FFF0D4 100%);">
                <h5 class="modal-title" id="modalEditarVehiculoLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarVehiculo" novalidate>
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" id="id_editar" name="id">
                    
                    <!-- Información Básica -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="ti ti-info-circle me-2"></i>
                                Información Básica
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="placa_editar" class="form-label">
                                        Placa <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="placa_editar" 
                                           name="placa" 
                                           placeholder="Ej: ABC-123"
                                           maxlength="20"
                                           required>
                                    <div class="invalid-feedback">
                                        Ingrese una placa válida (3-20 caracteres)
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="modelo_editar" class="form-label">
                                        Modelo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="modelo_editar" 
                                           name="modelo" 
                                           placeholder="Ej: Toyota Coaster"
                                           maxlength="200"
                                           required>
                                    <div class="invalid-feedback">
                                        Ingrese el modelo del vehículo (3-200 caracteres)
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="capacidad_editar" class="form-label">
                                        Capacidad (Pasajeros) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="capacidad_editar" 
                                           name="capacidad" 
                                           placeholder="Ej: 30"
                                           min="1"
                                           max="999"
                                           required>
                                    <div class="invalid-feedback">
                                        Ingrese una capacidad válida (1-999)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Datos del Vehículo -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="ti ti-car-garage me-2"></i>
                                Datos del Vehículo
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="marca_editar" class="form-label">Marca</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="marca_editar" 
                                           name="marca" 
                                           placeholder="Ej: Toyota"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="anio_editar" class="form-label">Año</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="anio_editar" 
                                           name="anio" 
                                           placeholder="Ej: 2020"
                                           min="1900"
                                           max="2100">
                                    <div class="invalid-feedback">
                                        Ingrese un año válido (1900-2100)
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="color_editar" class="form-label">Color</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="color_editar" 
                                           name="color" 
                                           placeholder="Ej: Blanco"
                                           maxlength="50">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="num_motor_editar" class="form-label">N° Motor</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_motor_editar" 
                                           name="num_motor" 
                                           placeholder="Ej: 1GR123456"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="num_chasis_editar" class="form-label">N° Chasis</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_chasis_editar" 
                                           name="num_chasis" 
                                           placeholder="Ej: JTEBH3FJ..."
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="num_serie_editar" class="form-label">N° Serie</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_serie_editar" 
                                           name="num_serie" 
                                           placeholder="Ej: SER123456"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="combustible_editar" class="form-label">Tipo Combustible</label>
                                    <select class="form-select" id="combustible_editar" name="combustible">
                                        <option value="">Seleccionar...</option>
                                        <option value="Gasolina">Gasolina</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="GNV">GNV</option>
                                        <option value="Eléctrico">Eléctrico</option>
                                        <option value="Híbrido">Híbrido</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documentación -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="ti ti-file-text me-2"></i>
                                Documentación
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="tarjeta_propiedad_editar" class="form-label">Tarjeta de Propiedad</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tarjeta_propiedad_editar" 
                                           name="tarjeta_propiedad" 
                                           placeholder="N° Tarjeta"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_tarjeta_editar" class="form-label">Fecha Venc. Tarjeta</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_tarjeta_editar" 
                                           name="fecha_venc_tarjeta">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="soat_editar" class="form-label">SOAT</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="soat_editar" 
                                           name="soat" 
                                           placeholder="N° SOAT"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_soat_editar" class="form-label">Fecha Venc. SOAT</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_soat_editar" 
                                           name="fecha_venc_soat">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="revision_tecnica_editar" class="form-label">Revisión Técnica</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="revision_tecnica_editar" 
                                           name="revision_tecnica" 
                                           placeholder="N° Revisión"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_revision_editar" class="form-label">Fecha Venc. Revisión</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_revision_editar" 
                                           name="fecha_venc_revision">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Asignado -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="ti ti-users me-2"></i>
                                Personal Asignado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="conductor_nombre_editar" class="form-label">Nombre del Conductor</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conductor_nombre_editar" 
                                           name="conductor_nombre" 
                                           placeholder="Ej: Juan Pérez García"
                                           maxlength="200">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="conductor_licencia_editar" class="form-label">N° Licencia</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="conductor_licencia_editar" 
                                           name="conductor_licencia" 
                                           placeholder="Ej: Q12345678"
                                           maxlength="50">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="conductor_telefono_editar" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conductor_telefono_editar" 
                                           name="conductor_telefono" 
                                           placeholder="Ej: 987654321"
                                           maxlength="20">
                                    <div class="invalid-feedback">
                                        Ingrese un teléfono válido
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="copiloto_nombre_editar" class="form-label">Nombre del Copiloto</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_nombre_editar" 
                                           name="copiloto_nombre" 
                                           placeholder="Ej: María López Sánchez"
                                           maxlength="200">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="copiloto_dni_editar" class="form-label">DNI Copiloto</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_dni_editar" 
                                           name="copiloto_dni" 
                                           placeholder="Ej: 12345678"
                                           maxlength="20"
                                           pattern="[0-9]+">
                                    <div class="invalid-feedback">
                                        Ingrese un DNI válido (solo números)
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="copiloto_telefono_editar" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_telefono_editar" 
                                           name="copiloto_telefono" 
                                           placeholder="Ej: 987654321"
                                           maxlength="20">
                                    <div class="invalid-feedback">
                                        Ingrese un teléfono válido
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado y Observaciones -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">
                                <i class="ti ti-settings me-2"></i>
                                Estado y Observaciones
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="estado_editar" class="form-label">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="estado_editar" name="estado" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="ACTIVO">Activo</option>
                                        <option value="MANTENIMIENTO">Mantenimiento</option>
                                        <option value="INACTIVO">Inactivo</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un estado
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <label for="observaciones_editar" class="form-label">Observaciones</label>
                                    <textarea class="form-control" 
                                              id="observaciones_editar" 
                                              name="observaciones" 
                                              rows="3" 
                                              maxlength="500"
                                              placeholder="Observaciones adicionales sobre el vehículo..."></textarea>
                                    <small class="text-muted">Máximo 500 caracteres</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="actualizarVehiculo()">
                    <i class="ti ti-device-floppy me-1"></i>
                    Actualizar Vehículo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicion(vehiculo) {
    // Datos básicos
    document.getElementById('id_editar').value = vehiculo.id;
    document.getElementById('placa_editar').value = vehiculo.placa || '';
    document.getElementById('modelo_editar').value = vehiculo.modelo || '';
    document.getElementById('capacidad_editar').value = vehiculo.capacidad || '';
    document.getElementById('estado_editar').value = vehiculo.estado || 'ACTIVO';
    document.getElementById('observaciones_editar').value = vehiculo.observaciones || '';
    
    // Datos del vehículo (JSON)
    const datosVehiculo = vehiculo.datos_vehiculo || {};
    document.getElementById('marca_editar').value = datosVehiculo.marca || '';
    document.getElementById('anio_editar').value = datosVehiculo.anio || '';
    document.getElementById('color_editar').value = datosVehiculo.color || '';
    document.getElementById('num_motor_editar').value = datosVehiculo.num_motor || '';
    document.getElementById('num_chasis_editar').value = datosVehiculo.num_chasis || '';
    document.getElementById('num_serie_editar').value = datosVehiculo.num_serie || '';
    document.getElementById('combustible_editar').value = datosVehiculo.combustible || '';
    
    // Documentación (JSON)
    const documentacion = vehiculo.documentacion || {};
    document.getElementById('tarjeta_propiedad_editar').value = documentacion.tarjeta_propiedad || '';
    document.getElementById('fecha_venc_tarjeta_editar').value = documentacion.fecha_venc_tarjeta || '';
    document.getElementById('soat_editar').value = documentacion.soat || '';
    document.getElementById('fecha_venc_soat_editar').value = documentacion.fecha_venc_soat || '';
    document.getElementById('revision_tecnica_editar').value = documentacion.revision_tecnica || '';
    document.getElementById('fecha_venc_revision_editar').value = documentacion.fecha_venc_revision || '';
    
    // Personal (JSON)
    const personal = vehiculo.personal || {};
    document.getElementById('conductor_nombre_editar').value = personal.conductor_nombre || '';
    document.getElementById('conductor_licencia_editar').value = personal.conductor_licencia || '';
    document.getElementById('conductor_telefono_editar').value = personal.conductor_telefono || '';
    document.getElementById('copiloto_nombre_editar').value = personal.copiloto_nombre || '';
    document.getElementById('copiloto_dni_editar').value = personal.copiloto_dni || '';
    document.getElementById('copiloto_telefono_editar').value = personal.copiloto_telefono || '';
}

function actualizarVehiculo() {
    const form = document.getElementById('formEditarVehiculo');
    
    // Validación HTML5
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        
        Swal.fire({
            title: 'Campos Incompletos',
            text: 'Por favor complete todos los campos obligatorios correctamente',
            icon: 'warning',
            confirmButtonColor: '#f39c12'
        });
        return;
    }
    
    // Validaciones adicionales
    const placa = document.getElementById('placa_editar').value.trim();
    const modelo = document.getElementById('modelo_editar').value.trim();
    const capacidad = parseInt(document.getElementById('capacidad_editar').value);
    const estado = document.getElementById('estado_editar').value;
    
    if (placa.length < 3) {
        Swal.fire({
            title: 'Error',
            text: 'La placa debe tener al menos 3 caracteres',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    if (modelo.length < 3) {
        Swal.fire({
            title: 'Error',
            text: 'El modelo debe tener al menos 3 caracteres',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    if (isNaN(capacidad) || capacidad < 1 || capacidad > 999) {
        Swal.fire({
            title: 'Error',
            text: 'La capacidad debe estar entre 1 y 999',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    if (!estado) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un estado',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Validar año si se ingresó
    const anio = document.getElementById('anio_editar').value;
    if (anio && (anio < 1900 || anio > 2100)) {
        Swal.fire({
            title: 'Error',
            text: 'El año debe estar entre 1900 y 2100',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Validar teléfonos si se ingresaron
    const conductorTelefono = document.getElementById('conductor_telefono_editar').value.trim();
    const copilotoTelefono = document.getElementById('copiloto_telefono_editar').value.trim();
    const telefonoRegex = /^[0-9+\-\s()]+$/;
    
    if (conductorTelefono && !telefonoRegex.test(conductorTelefono)) {
        Swal.fire({
            title: 'Error',
            text: 'El teléfono del conductor no es válido',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    if (copilotoTelefono && !telefonoRegex.test(copilotoTelefono)) {
        Swal.fire({
            title: 'Error',
            text: 'El teléfono del copiloto no es válido',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Validar DNI si se ingresó
    const copilotoDni = document.getElementById('copiloto_dni_editar').value.trim();
    if (copilotoDni && !/^[0-9]+$/.test(copilotoDni)) {
        Swal.fire({
            title: 'Error',
            text: 'El DNI debe contener solo números',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Confirmación
    Swal.fire({
        title: '¿Actualizar vehículo?',
        text: 'Se actualizarán los datos del vehículo',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarCarga();
            
            const formData = new FormData(form);
            
            fetch('modales/vehiculos/procesar_vehiculos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                ocultarCarga();
                
                if (data.success) {
                    $('#modalEditarVehiculo').modal('hide');
                    form.reset();
                    form.classList.remove('was-validated');
                    
                    mostrarExito(data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                ocultarCarga();
                mostrarError('Error al actualizar el vehículo');
                console.error('Error:', error);
            });
        }
    });
}

// Limpiar validación al cerrar modal
$('#modalEditarVehiculo').on('hidden.bs.modal', function() {
    const form = document.getElementById('formEditarVehiculo');
    form.reset();
    form.classList.remove('was-validated');
});
</script>