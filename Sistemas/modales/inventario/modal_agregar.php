<!-- Modal Agregar Item -->
<div class="modal fade" id="modalAgregarItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #C8E6C9, #A5D6A7); border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-plus-circle me-2"></i>
                    Agregar Nuevo Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarItem" novalidate>
                    <div class="row g-3">
                        
                        <!-- Nombre Producto -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Nombre del Producto <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" 
                                   maxlength="200" required>
                            <div class="invalid-feedback">
                                El nombre del producto es obligatorio
                            </div>
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="MEDICAMENTO">Medicamento</option>
                                <option value="MATERIAL_CURACION">Material de Curación</option>
                                <option value="EQUIPO_MEDICO">Equipo Médico</option>
                            </select>
                            <div class="invalid-feedback">
                                Seleccione el tipo de producto
                            </div>
                        </div>

                        <!-- Stock Actual -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Stock Actual <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="stock_actual" name="stock_actual" 
                                   step="0.01" min="0" required>
                            <div class="invalid-feedback">
                                El stock actual es obligatorio y debe ser mayor o igual a 0
                            </div>
                        </div>

                        <!-- Proveedor -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Proveedor</label>
                            <input type="text" class="form-control" id="proveedor" name="proveedor" 
                                   maxlength="200">
                        </div>

                        <!-- Fecha Ingreso -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Fecha de Ingreso <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" 
                                   max="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">
                                La fecha de ingreso es obligatoria
                            </div>
                        </div>

                        <!-- Datos Item (JSON) -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Datos Adicionales del Item (JSON)
                            </label>
                            <textarea class="form-control" id="datos_item" name="datos_item" 
                                      rows="3" placeholder='{"lote": "12345", "vencimiento": "2025-12-31"}'></textarea>
                            <small class="text-muted">Formato JSON válido. Ejemplo: {"lote": "ABC123", "vencimiento": "2025-12-31"}</small>
                            <div class="invalid-feedback">
                                El formato JSON no es válido
                            </div>
                        </div>

                        <!-- Inventario (JSON) -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Inventario Detallado (JSON)
                            </label>
                            <textarea class="form-control" id="inventario" name="inventario" 
                                      rows="3" placeholder='{"ubicacion": "Estante A", "cantidad_minima": 10}'></textarea>
                            <small class="text-muted">Formato JSON válido. Ejemplo: {"ubicacion": "Estante A", "cantidad_minima": 10}</small>
                            <div class="invalid-feedback">
                                El formato JSON no es válido
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" 
                                      rows="3" maxlength="1000"></textarea>
                            <small class="text-muted">Máximo 1000 caracteres</small>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="guardarItem()">
                    <i class="ti ti-device-floppy me-1"></i> Guardar Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Establecer fecha actual por defecto
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('fecha_ingreso').value = new Date().toISOString().split('T')[0];
});

function validarJSON(texto) {
    if (!texto || texto.trim() === '') return true;
    try {
        JSON.parse(texto);
        return true;
    } catch (e) {
        return false;
    }
}

function guardarItem() {
    const form = document.getElementById('formAgregarItem');
    
    // Validar campos requeridos
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        mostrarError('Por favor complete todos los campos obligatorios');
        return;
    }

    // Validar JSON de datos_item
    const datosItem = document.getElementById('datos_item').value;
    if (datosItem && !validarJSON(datosItem)) {
        document.getElementById('datos_item').classList.add('is-invalid');
        mostrarError('El formato JSON de Datos Item no es válido');
        return;
    } else {
        document.getElementById('datos_item').classList.remove('is-invalid');
    }

    // Validar JSON de inventario
    const inventario = document.getElementById('inventario').value;
    if (inventario && !validarJSON(inventario)) {
        document.getElementById('inventario').classList.add('is-invalid');
        mostrarError('El formato JSON de Inventario no es válido');
        return;
    } else {
        document.getElementById('inventario').classList.remove('is-invalid');
    }

    // Validar stock
    const stock = parseFloat(document.getElementById('stock_actual').value);
    if (stock < 0) {
        mostrarError('El stock no puede ser negativo');
        return;
    }

    mostrarCarga();
    
    const formData = new FormData(form);
    formData.append('accion', 'crear');

    fetch('modales/inventario/procesar_inventario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            mostrarExito(data.message);
            $('#modalAgregarItem').modal('hide');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al guardar el item');
        console.error('Error:', error);
    });
}

// Limpiar validaciones al cerrar modal
$('#modalAgregarItem').on('hidden.bs.modal', function () {
    document.getElementById('formAgregarItem').reset();
    document.getElementById('formAgregarItem').classList.remove('was-validated');
    document.getElementById('datos_item').classList.remove('is-invalid');
    document.getElementById('inventario').classList.remove('is-invalid');
});
</script>