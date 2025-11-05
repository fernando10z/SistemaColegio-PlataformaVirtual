<!-- Modal Agregar Vehículo -->
<div class="modal fade" id="modalAgregarVehiculo" tabindex="-1" aria-labelledby="modalAgregarVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #D4E5FF 0%, #E5F0FF 100%);">
                <h5 class="modal-title" id="modalAgregarVehiculoLabel">
                    <i class="ti ti-car me-2"></i>
                    Agregar Nuevo Vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarVehiculo" novalidate>
                    <input type="hidden" name="accion" value="agregar">
                    
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
                                    <label for="placa_agregar" class="form-label">
                                        Placa <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="placa_agregar" 
                                           name="placa" 
                                           placeholder="Ej: ABC-123"
                                           maxlength="20"
                                           required>
                                    <div class="invalid-feedback">
                                        Ingrese una placa válida (3-20 caracteres)
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="modelo_agregar" class="form-label">
                                        Modelo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="modelo_agregar" 
                                           name="modelo" 
                                           placeholder="Ej: Toyota Coaster"
                                           maxlength="200"
                                           required>
                                    <div class="invalid-feedback">
                                        Ingrese el modelo del vehículo (3-200 caracteres)
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="capacidad_agregar" class="form-label">
                                        Capacidad (Pasajeros) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="capacidad_agregar" 
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
                                    <label for="marca_agregar" class="form-label">Marca</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="marca_agregar" 
                                           name="marca" 
                                           placeholder="Ej: Toyota"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="anio_agregar" class="form-label">Año</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="anio_agregar" 
                                           name="anio" 
                                           placeholder="Ej: 2020"
                                           min="1900"
                                           max="2100">
                                    <div class="invalid-feedback">
                                        Ingrese un año válido (1900-2100)
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="color_agregar" class="form-label">Color</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="color_agregar" 
                                           name="color" 
                                           placeholder="Ej: Blanco"
                                           maxlength="50">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="num_motor_agregar" class="form-label">N° Motor</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_motor_agregar" 
                                           name="num_motor" 
                                           placeholder="Ej: 1GR123456"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="num_chasis_agregar" class="form-label">N° Chasis</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_chasis_agregar" 
                                           name="num_chasis" 
                                           placeholder="Ej: JTEBH3FJ..."
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="num_serie_agregar" class="form-label">N° Serie</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="num_serie_agregar" 
                                           name="num_serie" 
                                           placeholder="Ej: SER123456"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="combustible_agregar" class="form-label">Tipo Combustible</label>
                                    <select class="form-select" id="combustible_agregar" name="combustible">
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
                                    <label for="tarjeta_propiedad_agregar" class="form-label">Tarjeta de Propiedad</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tarjeta_propiedad_agregar" 
                                           name="tarjeta_propiedad" 
                                           placeholder="N° Tarjeta"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_tarjeta_agregar" class="form-label">Fecha Venc. Tarjeta</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_tarjeta_agregar" 
                                           name="fecha_venc_tarjeta">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="soat_agregar" class="form-label">SOAT</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="soat_agregar" 
                                           name="soat" 
                                           placeholder="N° SOAT"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_soat_agregar" class="form-label">Fecha Venc. SOAT</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_soat_agregar" 
                                           name="fecha_venc_soat">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="revision_tecnica_agregar" class="form-label">Revisión Técnica</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="revision_tecnica_agregar" 
                                           name="revision_tecnica" 
                                           placeholder="N° Revisión"
                                           maxlength="100">
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="fecha_venc_revision_agregar" class="form-label">Fecha Venc. Revisión</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_venc_revision_agregar" 
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
                                    <label for="conductor_nombre_agregar" class="form-label">Nombre del Conductor</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conductor_nombre_agregar" 
                                           name="conductor_nombre" 
                                           placeholder="Ej: Juan Pérez García"
                                           maxlength="200">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="conductor_licencia_agregar" class="form-label">N° Licencia</label>
                                    <input type="text" 
                                           class="form-control text-uppercase" 
                                           id="conductor_licencia_agregar" 
                                           name="conductor_licencia" 
                                           placeholder="Ej: Q12345678"
                                           maxlength="50">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="conductor_telefono_agregar" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="conductor_telefono_agregar" 
                                           name="conductor_telefono" 
                                           placeholder="Ej: 987654321"
                                           maxlength="20">
                                    <div class="invalid-feedback">
                                        Ingrese un teléfono válido
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="copiloto_nombre_agregar" class="form-label">Nombre del Copiloto</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_nombre_agregar" 
                                           name="copiloto_nombre" 
                                           placeholder="Ej: María López Sánchez"
                                           maxlength="200">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="copiloto_dni_agregar" class="form-label">DNI Copiloto</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_dni_agregar" 
                                           name="copiloto_dni" 
                                           placeholder="Ej: 12345678"
                                           maxlength="20"
                                           pattern="[0-9]+">
                                    <div class="invalid-feedback">
                                        Ingrese un DNI válido (solo números)
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="copiloto_telefono_agregar" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="copiloto_telefono_agregar" 
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
                                    <label for="estado_agregar" class="form-label">
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="estado_agregar" name="estado" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="ACTIVO" selected>Activo</option>
                                        <option value="MANTENIMIENTO">Mantenimiento</option>
                                        <option value="INACTIVO">Inactivo</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un estado
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <label for="observaciones_agregar" class="form-label">Observaciones</label>
                                    <textarea class="form-control" 
                                              id="observaciones_agregar" 
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
                <button type="button" class="btn btn-primary" onclick="guardarVehiculo()">
                    <i class="ti ti-device-floppy me-1"></i>
                    Guardar Vehículo
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function guardarVehiculo() {
    const form = document.getElementById('formAgregarVehiculo');
    
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
    const placa = document.getElementById('placa_agregar').value.trim();
    const modelo = document.getElementById('modelo_agregar').value.trim();
    const capacidad = parseInt(document.getElementById('capacidad_agregar').value);
    const estado = document.getElementById('estado_agregar').value;
    
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
    
    // Confirmación
    Swal.fire({
        title: '¿Guardar vehículo?',
        text: 'Se registrará el nuevo vehículo en el sistema',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#loadingOverlay').css('display', 'flex');
            
            const formData = new FormData(form);
            
            fetch('modales/vehiculos/procesar_vehiculos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $('#loadingOverlay').hide();
                
                if (data.success) {
                    $('#modalAgregarVehiculo').modal('hide');
                    form.reset();
                    form.classList.remove('was-validated');
                    
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#198754',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1500);
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
                $('#loadingOverlay').hide();
                Swal.fire({
                    title: 'Error',
                    text: 'Error al guardar el vehículo',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
                console.error('Error:', error);
            });
        }
    });
}

// Limpiar validación al cerrar modal
$('#modalAgregarVehiculo').on('hidden.bs.modal', function() {
    const form = document.getElementById('formAgregarVehiculo');
    form.reset();
    form.classList.remove('was-validated');
});
</script>