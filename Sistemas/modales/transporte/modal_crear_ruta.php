<!-- modales/transporte/modal_crear_ruta.php -->
<div class="modal fade" id="modalCrearRuta" tabindex="-1" aria-labelledby="modalCrearRutaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #D4E5FF 0%, #E5F0FF 100%);">
                <h5 class="modal-title" id="modalCrearRutaLabel">
                    <i class="ti ti-bus me-2"></i>
                    Nueva Ruta de Transporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearRuta" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        
                        <!-- Código de Ruta -->
                        <div class="col-md-6">
                            <label for="codigoRuta" class="form-label">
                                Código de la Ruta <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="codigoRuta" name="codigo_ruta" 
                                   placeholder="Ej: RT-001" required>
                            <div class="invalid-feedback">El código es obligatorio</div>
                        </div>

                        <!-- Nombre de la Ruta -->
                        <div class="col-md-6">
                            <label for="nombreRuta" class="form-label">
                                Nombre de la Ruta <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombreRuta" name="nombre" 
                                   placeholder="Ej: Ruta Centro" required>
                            <div class="invalid-feedback">El nombre es obligatorio</div>
                        </div>

                        <!-- Horario de Salida -->
                        <div class="col-md-6">
                            <label for="horarioSalida" class="form-label">
                                Horario de Salida <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" id="horarioSalida" name="horario_salida" required>
                            <div class="invalid-feedback">El horario de salida es obligatorio</div>
                        </div>

                        <!-- Horario de Retorno -->
                        <div class="col-md-6">
                            <label for="horarioRetorno" class="form-label">
                                Horario de Retorno <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control" id="horarioRetorno" name="horario_retorno" required>
                            <div class="invalid-feedback">El horario de retorno es obligatorio</div>
                        </div>

                        <!-- Tarifa -->
                        <div class="col-md-6">
                            <label for="tarifaRuta" class="form-label">
                                Tarifa Mensual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control" id="tarifaRuta" name="tarifa" 
                                       step="0.01" min="0" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">La tarifa es obligatoria</div>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <label for="estadoRuta" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="estadoRuta" name="activo" required>
                                <option value="">Seleccione estado</option>
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un estado</div>
                        </div>

                        <!-- Paraderos -->
                        <div class="col-12">
                            <label class="form-label">
                                <i class="ti ti-map-pin me-1"></i>
                                Paraderos <span class="text-danger">*</span>
                            </label>
                            <div id="paraderosContainer">
                                <div class="paradero-item-form mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text">📍</span>
                                        <input type="text" class="form-control paradero-nombre" 
                                               placeholder="Nombre del paradero" name="paraderos[]" required>
                                        <input type="text" class="form-control paradero-direccion" 
                                               placeholder="Dirección" name="direcciones[]">
                                        <button type="button" class="btn btn-outline-danger" onclick="eliminarParadero(this)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="agregarParadero()">
                                <i class="ti ti-plus me-1"></i>
                                Agregar Paradero
                            </button>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        Crear Ruta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function agregarParadero() {
    const container = document.getElementById('paraderosContainer');
    const nuevoParadero = document.createElement('div');
    nuevoParadero.className = 'paradero-item-form mb-2';
    nuevoParadero.innerHTML = `
        <div class="input-group">
            <span class="input-group-text">📍</span>
            <input type="text" class="form-control paradero-nombre" 
                   placeholder="Nombre del paradero" name="paraderos[]" required>
            <input type="text" class="form-control paradero-direccion" 
                   placeholder="Dirección" name="direcciones[]">
            <button type="button" class="btn btn-outline-danger" onclick="eliminarParadero(this)">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(nuevoParadero);
}

function eliminarParadero(btn) {
    const container = document.getElementById('paraderosContainer');
    if (container.children.length > 1) {
        btn.closest('.paradero-item-form').remove();
    } else {
        Swal.fire({
            title: 'Advertencia',
            text: 'Debe haber al menos un paradero',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    }
}

document.getElementById('formCrearRuta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }

    const horarioSalida = document.getElementById('horarioSalida').value;
    const horarioRetorno = document.getElementById('horarioRetorno').value;
    
    if (horarioSalida && horarioRetorno && horarioRetorno <= horarioSalida) {
        Swal.fire({
            title: 'Error de Validación',
            text: 'El horario de retorno debe ser posterior al de salida',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    const paraderos = [];
    const nombresParaderos = document.querySelectorAll('.paradero-nombre');
    const direccionesParaderos = document.querySelectorAll('.paradero-direccion');
    
    let paraderoValido = false;
    for (let i = 0; i < nombresParaderos.length; i++) {
        const nombre = nombresParaderos[i].value.trim();
        const direccion = direccionesParaderos[i].value.trim();
        
        if (nombre) {
            paraderoValido = true;
            paraderos.push({
                nombre: nombre,
                direccion: direccion,
                estudiantes: []
            });
        }
    }

    if (!paraderoValido) {
        Swal.fire({
            title: 'Error de Validación',
            text: 'Debe agregar al menos un paradero con nombre',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    const formData = new FormData(this);
    formData.append('accion', 'crear');
    formData.append('paraderos_json', JSON.stringify(paraderos));

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

document.getElementById('modalCrearRuta').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formCrearRuta').reset();
    document.getElementById('formCrearRuta').classList.remove('was-validated');
    
    const container = document.getElementById('paraderosContainer');
    while (container.children.length > 1) {
        container.removeChild(container.lastChild);
    }
    container.querySelector('.paradero-nombre').value = '';
    container.querySelector('.paradero-direccion').value = '';
});
</script>

<style>
.paradero-item-form {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>