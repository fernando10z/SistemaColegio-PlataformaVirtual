<!-- modales/transporte/modal_asignar_vehiculo.php -->
<div class="modal fade" id="modalAsignarVehiculo" tabindex="-1" aria-labelledby="modalAsignarVehiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #B4E5D4 0%, #D4F0E5 100%);">
                <h5 class="modal-title" id="modalAsignarVehiculoLabel">
                    <i class="ti ti-car me-2"></i>
                    Asignar Vehículo a la Ruta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignarVehiculo" method="POST">
                <input type="hidden" id="rutaIdVehiculo" name="ruta_id">
                <input type="hidden" name="periodo_id" value="<?= $periodo_activo['id'] ?? '' ?>">
                
                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-info-circle fs-4 me-2"></i>
                            <div>
                                <strong>Ruta:</strong> <span id="infoRutaNombre"></span><br>
                                <small><strong>Código:</strong> <span id="infoRutaCodigo"></span></small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        
                        <div class="col-12">
                            <label for="vehiculoSelect" class="form-label">
                                <i class="ti ti-car me-1"></i>
                                Seleccionar Vehículo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="vehiculoSelect" name="vehiculo_id" required>
                                <option value="">Seleccione un vehículo</option>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                    <option value="<?= $vehiculo['id'] ?>" 
                                            data-placa="<?= htmlspecialchars($vehiculo['placa']) ?>"
                                            data-modelo="<?= htmlspecialchars($vehiculo['modelo']) ?>"
                                            data-capacidad="<?= $vehiculo['capacidad'] ?>">
                                        <?= htmlspecialchars($vehiculo['placa']) ?> - 
                                        <?= htmlspecialchars($vehiculo['modelo']) ?> 
                                        (Capacidad: <?= $vehiculo['capacidad'] ?> pasajeros)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un vehículo</div>
                        </div>

                        <div class="col-12" id="infoVehiculoContainer" style="display: none;">
                            <div class="card" style="background: #F8F9FA; border: 1px solid #DEE2E6;">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Información del Vehículo
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Placa</small>
                                            <strong id="infoVehiculoPlaca">-</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Modelo</small>
                                            <strong id="infoVehiculoModelo">-</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Capacidad</small>
                                            <strong id="infoVehiculoCapacidad">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="observacionesVehiculo" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observacionesVehiculo" name="observaciones" 
                                      rows="3" placeholder="Observaciones adicionales sobre la asignación"></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>
                        Asignar Vehículo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('vehiculoSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const container = document.getElementById('infoVehiculoContainer');
    
    if (this.value) {
        document.getElementById('infoVehiculoPlaca').textContent = selectedOption.dataset.placa;
        document.getElementById('infoVehiculoModelo').textContent = selectedOption.dataset.modelo;
        document.getElementById('infoVehiculoCapacidad').textContent = selectedOption.dataset.capacidad + ' pasajeros';
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});

function cargarDatosAsignacionVehiculo(ruta) {
    document.getElementById('rutaIdVehiculo').value = ruta.id;
    document.getElementById('infoRutaNombre').textContent = ruta.nombre;
    document.getElementById('infoRutaCodigo').textContent = ruta.codigo_ruta;
    
    if (ruta.vehiculo_id) {
        document.getElementById('vehiculoSelect').value = ruta.vehiculo_id;
        document.getElementById('vehiculoSelect').dispatchEvent(new Event('change'));
    }
}

document.getElementById('formAsignarVehiculo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }

    const formData = new FormData(this);
    formData.append('accion', 'asignar_vehiculo');

    mostrarCarga();

    fetch('modales/transporte/procesar_transporte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
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
        Swal.fire({
            title: 'Error',
            text: 'Error al procesar la solicitud',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
});

document.getElementById('modalAsignarVehiculo').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formAsignarVehiculo').reset();
    document.getElementById('formAsignarVehiculo').classList.remove('was-validated');
    document.getElementById('infoVehiculoContainer').style.display = 'none';
});
</script>