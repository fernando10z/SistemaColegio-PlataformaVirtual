<?php
// modales/periodos/modal_agregar.php
?>
<!-- Modal Agregar Período Académico -->
<div class="modal fade" id="modalAgregarPeriodo" tabindex="-1" aria-labelledby="modalAgregarPeriodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalAgregarPeriodoLabel">
                    <i class="ti ti-calendar-plus me-2"></i>
                    Nuevo Período Académico
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarPeriodo" method="POST">
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
                                                Nombre del Período <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre" 
                                                   placeholder="Ejemplo: Año Académico 2025" required>
                                            <div class="form-text">Nombre descriptivo del período académico</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_anio" class="form-label">
                                                Año Académico <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_anio" name="anio" 
                                                   min="2020" max="2030" value="<?= date('Y') ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha_inicio" class="form-label">
                                                Fecha de Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_inicio" name="fecha_inicio" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha_fin" class="form-label">
                                                Fecha de Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_fin" name="fecha_fin" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_tipo_periodo" class="form-label">
                                                Tipo de Período <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo_periodo" name="tipo_periodo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="BIMESTRE">Bimestre (4 períodos)</option>
                                                <option value="TRIMESTRE">Trimestre (3 períodos)</option>
                                                <option value="SEMESTRE">Semestre (2 períodos)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Información de duración -->
                                    <div class="alert alert-info" id="info-duracion" style="display: none;">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <span id="duracion-texto"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Períodos de Evaluación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar-event me-2"></i>
                                        Períodos de Evaluación
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnGenerarPeriodos">
                                        <i class="ti ti-wand me-1"></i>
                                        Generar Automático
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="contenedor-evaluaciones">
                                        <div class="text-muted text-center py-4">
                                            <i class="ti ti-calendar-off" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mt-2">Los períodos de evaluación se generarán automáticamente</p>
                                            <p class="small">O puedes configurar las fechas manualmente</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuraciones Adicionales -->
                        <div class="col-12 mt-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuraciones
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="add_activo" name="activo" checked>
                                                <label class="form-check-label" for="add_activo">
                                                    Período Activo
                                                </label>
                                                <div class="form-text">El período estará disponible para uso</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="add_actual" name="actual">
                                                <label class="form-check-label" for="add_actual">
                                                    Establecer como Período Actual
                                                </label>
                                                <div class="form-text">Esto desactivará el período actual</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarPeriodo">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Período
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Calcular duración al cambiar fechas
    $('#add_fecha_inicio, #add_fecha_fin').on('change', function() {
        calcularDuracion();
    });

    // Generar períodos automáticamente
    $('#btnGenerarPeriodos').on('click', function() {
        const fechaInicio = $('#add_fecha_inicio').val();
        const fechaFin = $('#add_fecha_fin').val();
        const tipoPeriodo = $('#add_tipo_periodo').val();

        if (!fechaInicio || !fechaFin || !tipoPeriodo) {
            Swal.fire({
                title: 'Datos Incompletos',
                text: 'Por favor completa las fechas y el tipo de período',
                icon: 'warning'
            });
            return;
        }

        generarPeriodosEvaluacion(fechaInicio, fechaFin, tipoPeriodo);
    });

    // Envío del formulario
    $('#formAgregarPeriodo').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioPeriodo()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Agregar períodos de evaluación
        const periodosEval = obtenerPeriodosEvaluacion();
        formData.append('periodos_evaluacion', JSON.stringify(periodosEval));

        mostrarCarga();
        $('#btnGuardarPeriodo').prop('disabled', true);

        $.ajax({
            url: 'modales/periodos/procesar_periodos.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarPeriodo').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Período Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarPeriodo').modal('hide');
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
                $('#btnGuardarPeriodo').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarPeriodo').on('hidden.bs.modal', function() {
        $('#formAgregarPeriodo')[0].reset();
        $('#contenedor-evaluaciones').html(`
            <div class="text-muted text-center py-4">
                <i class="ti ti-calendar-off" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2">Los períodos de evaluación se generarán automáticamente</p>
                <p class="small">O puedes configurar las fechas manualmente</p>
            </div>
        `);
        $('#info-duracion').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function calcularDuracion() {
    const fechaInicio = $('#add_fecha_inicio').val();
    const fechaFin = $('#add_fecha_fin').val();
    
    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const diferencia = fin.getTime() - inicio.getTime();
        const dias = Math.ceil(diferencia / (1000 * 3600 * 24));
        
        if (dias > 0) {
            const meses = Math.round(dias / 30);
            $('#duracion-texto').text(`Duración: ${dias} días (aproximadamente ${meses} meses)`);
            $('#info-duracion').show();
        } else {
            $('#info-duracion').hide();
        }
    }
}

function generarPeriodosEvaluacion(fechaInicio, fechaFin, tipoPeriodo) {
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    const duracionTotal = Math.ceil((fin.getTime() - inicio.getTime()) / (1000 * 3600 * 24));
    
    let numPeriodos = 0;
    let nombreBase = '';
    
    switch(tipoPeriodo) {
        case 'BIMESTRE':
            numPeriodos = 4;
            nombreBase = 'Bimestre';
            break;
        case 'TRIMESTRE':
            numPeriodos = 3;
            nombreBase = 'Trimestre';
            break;
        case 'SEMESTRE':
            numPeriodos = 2;
            nombreBase = 'Semestre';
            break;
    }
    
    const diasPorPeriodo = Math.floor(duracionTotal / numPeriodos);
    let fechaActual = new Date(inicio);
    let html = '';
    
    for (let i = 1; i <= numPeriodos; i++) {
        const fechaFinPeriodo = new Date(fechaActual);
        
        if (i < numPeriodos) {
            fechaFinPeriodo.setDate(fechaFinPeriodo.getDate() + diasPorPeriodo);
        } else {
            fechaFinPeriodo.setTime(fin.getTime());
        }
        
        const numeroRomano = convertirARomano(i);
        
        html += `
            <div class="row mb-3 periodo-evaluacion">
                <div class="col-md-4">
                    <label class="form-label">Nombre del Período</label>
                    <input type="text" class="form-control" name="eval_nombre[]" 
                           value="${numeroRomano} ${nombreBase}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="eval_inicio[]" 
                           value="${fechaActual.toISOString().split('T')[0]}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="eval_fin[]" 
                           value="${fechaFinPeriodo.toISOString().split('T')[0]}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarPeriodo(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        fechaActual = new Date(fechaFinPeriodo);
        fechaActual.setDate(fechaActual.getDate() + 1);
    }
    
    $('#contenedor-evaluaciones').html(html);
}

function convertirARomano(num) {
    const valores = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1];
    const simbolos = ['M', 'CM', 'D', 'CD', 'C', 'XC', 'L', 'XL', 'X', 'IX', 'V', 'IV', 'I'];
    let resultado = '';
    
    for (let i = 0; i < valores.length; i++) {
        while (num >= valores[i]) {
            resultado += simbolos[i];
            num -= valores[i];
        }
    }
    
    return resultado;
}

function eliminarPeriodo(button) {
    $(button).closest('.periodo-evaluacion').remove();
}

function obtenerPeriodosEvaluacion() {
    const periodos = [];
    $('.periodo-evaluacion').each(function(index) {
        const nombre = $(this).find('input[name="eval_nombre[]"]').val();
        const inicio = $(this).find('input[name="eval_inicio[]"]').val();
        const fin = $(this).find('input[name="eval_fin[]"]').val();
        
        if (nombre && inicio && fin) {
            periodos.push({
                numero: index + 1,
                nombre: nombre,
                fecha_inicio: inicio,
                fecha_fin: fin
            });
        }
    });
    
    return periodos;
}

function validarFormularioPeriodo() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar fechas
    const fechaInicio = new Date($('#add_fecha_inicio').val());
    const fechaFin = new Date($('#add_fecha_fin').val());
    
    if (fechaFin <= fechaInicio) {
        mostrarErrorCampo('#add_fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio');
        isValid = false;
    }
    
    // Validar períodos de evaluación
    const periodos = obtenerPeriodosEvaluacion();
    if (periodos.length === 0) {
        Swal.fire({
            title: 'Períodos de Evaluación',
            text: 'Debes configurar al menos un período de evaluación',
            icon: 'warning'
        });
        isValid = false;
    }
    
    return isValid;
}

function mostrarErrorCampo(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}
</script>