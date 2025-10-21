<!-- Modal Administrar Medicamento -->
<div class="modal fade" id="modalAdministrarMedicamento" tabindex="-1" aria-labelledby="modalAdministrarMedicamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #C8E6C9, #E8F5E9); border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="modalAdministrarMedicamentoLabel">
                    <i class="ti ti-pill me-2"></i>
                    Administrar Medicamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAdministrarMedicamento">
                    <div class="row">
                        <!-- Selección de Atención -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-clipboard me-2"></i>
                                Atención Médica
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estudiante <span class="text-danger">*</span></label>
                            <select class="form-select" name="estudiante_id_med" id="estudiante_id_med" required onchange="cargarAtencionesEstudiante()">
                                <option value="">Seleccione un estudiante</option>
                                <?php foreach ($estudiantes_activos as $est): ?>
                                    <option value="<?= $est['id'] ?>">
                                        <?= htmlspecialchars($est['codigo_estudiante']) ?> - <?= htmlspecialchars($est['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un estudiante</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Atención Médica <span class="text-danger">*</span></label>
                            <select class="form-select" name="atencion_id" id="atencion_id" required disabled>
                                <option value="">Primero seleccione un estudiante</option>
                            </select>
                            <div class="invalid-feedback">Seleccione una atención</div>
                        </div>

                        <!-- Información de la Atención Seleccionada -->
                        <div class="col-12 mb-3" id="infoAtencionSeleccionada" style="display:none;">
                            <div class="alert alert-info">
                                <strong><i class="ti ti-info-circle me-2"></i>Información de la Atención:</strong>
                                <div id="detalleAtencionSeleccionada"></div>
                            </div>
                        </div>

                        <!-- Selección de Producto -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-package me-2"></i>
                                Medicamento o Material
                            </h6>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select class="form-select" name="producto_id" id="producto_id" required onchange="mostrarInfoProducto()">
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($inventario_disponible as $prod): ?>
                                    <option value="<?= $prod['id'] ?>" 
                                            data-nombre="<?= htmlspecialchars($prod['nombre_producto']) ?>"
                                            data-tipo="<?= htmlspecialchars($prod['tipo']) ?>"
                                            data-stock="<?= $prod['stock_actual'] ?>">
                                        <?= htmlspecialchars($prod['nombre_producto']) ?> - 
                                        <?= htmlspecialchars($prod['tipo']) ?> 
                                        (Stock: <?= $prod['stock_actual'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Seleccione un producto</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="cantidad" id="cantidad" 
                                   placeholder="1" required min="0.01">
                            <div class="invalid-feedback">Ingrese una cantidad válida</div>
                        </div>

                        <!-- Información del Producto -->
                        <div class="col-12 mb-3" id="infoProducto" style="display:none;">
                            <div class="alert alert-success">
                                <strong><i class="ti ti-package me-2"></i>Información del Producto:</strong>
                                <div id="detalleProducto"></div>
                            </div>
                        </div>

                        <!-- Detalles de Administración -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-notes me-2"></i>
                                Detalles de Administración
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vía de Administración</label>
                            <select class="form-select" name="via_administracion" id="via_administracion">
                                <option value="ORAL">Oral</option>
                                <option value="TOPICA">Tópica</option>
                                <option value="INTRAVENOSA">Intravenosa</option>
                                <option value="INTRAMUSCULAR">Intramuscular</option>
                                <option value="SUBCUTANEA">Subcutánea</option>
                                <option value="OTRA">Otra</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dosis Administrada</label>
                            <input type="text" class="form-control" name="dosis" id="dosis" 
                                   placeholder="Ej: 500mg, 1 tableta, 5ml">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones_admin" id="observaciones_admin" 
                                      rows="3" placeholder="Observaciones sobre la administración del medicamento..."></textarea>
                        </div>

                        <!-- Autorización -->
                        <div class="col-12 mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-shield-check me-2"></i>
                                Autorización
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">¿Cuenta con autorización del apoderado?</label>
                            <select class="form-select" name="autorizacion_apoderado" id="autorizacion_apoderado">
                                <option value="SI">Sí</option>
                                <option value="NO">No (Emergencia)</option>
                                <option value="PENDIENTE">Pendiente</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Autorizador</label>
                            <input type="text" class="form-control" name="nombre_autorizador" id="nombre_autorizador" 
                                   placeholder="Nombre de quien autoriza">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-medicine" onclick="guardarAdministracion()">
                    <i class="ti ti-check me-2"></i>Administrar Medicamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cargarAtencionesEstudiante() {
    const estudianteId = document.getElementById('estudiante_id_med').value;
    const atencionSelect = document.getElementById('atencion_id');
    
    if (!estudianteId) {
        atencionSelect.disabled = true;
        atencionSelect.innerHTML = '<option value="">Primero seleccione un estudiante</option>';
        document.getElementById('infoAtencionSeleccionada').style.display = 'none';
        return;
    }

    mostrarCarga();

    fetch('modales/atenciones_medicas/procesar_atenciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_atenciones_estudiante&estudiante_id=${estudianteId}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            atencionSelect.innerHTML = '<option value="">Seleccione una atención</option>';
            
            data.atenciones.forEach(atencion => {
                const option = document.createElement('option');
                option.value = atencion.id;
                option.textContent = `${atencion.fecha_atencion} - ${atencion.tipo_atencion} - ${atencion.motivo_consulta.substring(0, 50)}...`;
                option.dataset.fecha = atencion.fecha_atencion;
                option.dataset.tipo = atencion.tipo_atencion;
                option.dataset.motivo = atencion.motivo_consulta;
                atencionSelect.appendChild(option);
            });
            
            atencionSelect.disabled = false;
            
            if (data.atenciones.length === 0) {
                atencionSelect.innerHTML = '<option value="">No hay atenciones registradas</option>';
            }
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al cargar atenciones');
        console.error(error);
    });
}

function mostrarInfoProducto() {
    const productoSelect = document.getElementById('producto_id');
    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
    
    if (productoSelect.value) {
        const nombre = selectedOption.dataset.nombre;
        const tipo = selectedOption.dataset.tipo;
        const stock = selectedOption.dataset.stock;
        
        document.getElementById('detalleProducto').innerHTML = `
            <div><strong>Nombre:</strong> ${nombre}</div>
            <div><strong>Tipo:</strong> ${tipo}</div>
            <div><strong>Stock Disponible:</strong> ${stock} unidades</div>
        `;
        document.getElementById('infoProducto').style.display = 'block';
    } else {
        document.getElementById('infoProducto').style.display = 'none';
    }
}

function guardarAdministracion() {
    const form = document.getElementById('formAdministrarMedicamento');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        mostrarError('Por favor complete todos los campos requeridos');
        return;
    }

    const formData = new FormData(form);
    
    // Validar stock
    const productoSelect = document.getElementById('producto_id');
    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
    const stockDisponible = parseFloat(selectedOption.dataset.stock);
    const cantidadSolicitada = parseFloat(formData.get('cantidad'));
    
    if (cantidadSolicitada > stockDisponible) {
        mostrarError(`Stock insuficiente. Disponible: ${stockDisponible}`);
        return;
    }

    // Construir objeto de tratamiento actualizado
    const tratamientoActualizado = {
        medicamento: selectedOption.dataset.nombre,
        cantidad: cantidadSolicitada,
        via_administracion: formData.get('via_administracion'),
        dosis: formData.get('dosis') || null,
        observaciones: formData.get('observaciones_admin') || null,
        fecha_administracion: new Date().toISOString()
    };

    // Construir objeto de autorización
    const autorizacion = {
        autorizado: formData.get('autorizacion_apoderado'),
        autorizador: formData.get('nombre_autorizador') || null,
        fecha: new Date().toISOString()
    };

    // Preparar datos para enviar
    const datosEnvio = new URLSearchParams({
        accion: 'administrar_medicamento',
        atencion_id: formData.get('atencion_id'),
        producto_id: formData.get('producto_id'),
        cantidad: cantidadSolicitada,
        tratamiento: JSON.stringify(tratamientoActualizado),
        autorizaciones: JSON.stringify(autorizacion)
    });

    mostrarCarga();

    fetch('modales/atenciones_medicas/procesar_atenciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: datosEnvio
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            mostrarExito(data.message);
            $('#modalAdministrarMedicamento').modal('hide');
            form.reset();
            form.classList.remove('was-validated');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al administrar medicamento');
        console.error(error);
    });
}
</script>