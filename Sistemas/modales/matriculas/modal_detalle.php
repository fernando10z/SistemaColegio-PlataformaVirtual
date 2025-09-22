<?php
// modales/matriculas/modal_detalle.php
?>
<!-- Modal Detalle Matrícula -->
<div class="modal fade" id="modalDetalleMatricula" tabindex="-1" aria-labelledby="modalDetalleMatriculaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalDetalleMatriculaLabel">
                    <i class="ti ti-eye me-2"></i>
                    Detalle de Matrícula
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Información Principal -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <img id="detalle_estudiante_foto" src="../assets/images/profile/user-default.jpg" 
                                             class="rounded-circle border border-3 border-success" width="80" height="80" alt="Foto">
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center mb-2">
                                            <h4 class="mb-0 me-3" id="detalle_estudiante_nombre">-</h4>
                                            <span class="badge bg-success fs-6" id="detalle_codigo_matricula">-</span>
                                        </div>
                                        <div class="text-muted" id="detalle_estudiante_detalles">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <div class="fs-1 text-success" id="detalle_estado_icon">
                                                <i class="ti ti-school"></i>
                                            </div>
                                            <div class="fw-bold" id="detalle_estado_texto">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Académica -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-school me-2"></i>
                                    Información Académica
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Sección Asignada:</label>
                                    <div class="fw-bold" id="detalle_seccion">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Aula:</label>
                                    <div class="fw-bold" id="detalle_aula">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Período Académico:</label>
                                    <div class="fw-bold" id="detalle_periodo">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Tipo de Matrícula:</label>
                                    <div>
                                        <span class="badge bg-secondary" id="detalle_tipo">-</span>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <label class="text-muted small">Capacidad de Sección:</label>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" id="detalle_capacidad_bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <span class="fw-bold" id="detalle_capacidad_texto">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-user me-2"></i>
                                    Información Personal
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Código Estudiante:</label>
                                    <div class="fw-bold font-monospace" id="detalle_codigo_estudiante">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Documento:</label>
                                    <div class="fw-bold" id="detalle_documento">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Fecha de Nacimiento:</label>
                                    <div class="fw-bold" id="detalle_fecha_nacimiento">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Edad:</label>
                                    <div class="fw-bold" id="detalle_edad">-</div>
                                </div>
                                <div class="info-group">
                                    <label class="text-muted small">Contacto:</label>
                                    <div id="detalle_contacto">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas Importantes -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-calendar me-2"></i>
                                    Fechas Importantes
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Fecha de Matrícula:</label>
                                    <div class="fw-bold" id="detalle_fecha_matricula">-</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Registro en Sistema:</label>
                                    <div class="fw-bold" id="detalle_fecha_creacion">-</div>
                                </div>
                                <div class="info-group">
                                    <label class="text-muted small">Última Actualización:</label>
                                    <div class="fw-bold" id="detalle_fecha_actualizacion">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado y Documentación -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-file-check me-2"></i>
                                    Estado y Documentación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Estado de Matrícula:</label>
                                    <div>
                                        <span class="badge fs-6" id="detalle_estado_badge">-</span>
                                    </div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Documentos:</label>
                                    <div class="d-flex align-items-center">
                                        <span id="detalle_documentos_icon">-</span>
                                        <span class="ms-2" id="detalle_documentos_texto">-</span>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <label class="text-muted small">Estado del Registro:</label>
                                    <div class="d-flex align-items-center">
                                        <span id="detalle_activo_icon">-</span>
                                        <span class="ms-2" id="detalle_activo_texto">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3" id="observaciones-card" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-note me-2"></i>
                                    Observaciones
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light mb-0" id="detalle_observaciones">
                                    No hay observaciones registradas
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Apoderados -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-users me-2"></i>
                                    Información de Apoderados
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="detalle_apoderados">
                                    <div class="text-center text-muted">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <div class="mt-2">Cargando información de apoderados...</div>
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
                    Cerrar
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnEditarDesdeDetalle">
                    <i class="ti ti-edit me-2"></i>
                    Editar Matrícula
                </button>
                <button type="button" class="btn btn-outline-success" id="btnImprimirDesdeDetalle">
                    <i class="ti ti-printer me-2"></i>
                    Imprimir Constancia
                </button>
                <button type="button" class="btn btn-primary" id="btnVerHistorialDetalle">
                    <i class="ti ti-history me-2"></i>
                    Ver Historial Completo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetalleMatricula(matricula) {
    // Información principal
    $('#detalle_estudiante_nombre').text(matricula.estudiante_nombres + ' ' + matricula.estudiante_apellidos);
    $('#detalle_codigo_matricula').text(matricula.codigo_matricula);
    $('#detalle_estudiante_detalles').text(`Código: ${matricula.codigo_estudiante} | Documento: ${matricula.documento_numero}`);
    
    if (matricula.foto_url) {
        $('#detalle_estudiante_foto').attr('src', matricula.foto_url);
    }
    
    // Estado
    const estadoConfig = getEstadoConfig(matricula.estado);
    $('#detalle_estado_icon i').removeClass().addClass(estadoConfig.icon);
    $('#detalle_estado_icon').removeClass().addClass('fs-1 ' + estadoConfig.color);
    $('#detalle_estado_texto').text(matricula.estado);
    
    // Información académica
    $('#detalle_seccion').text(`${matricula.nivel_nombre} - ${matricula.grado}${matricula.seccion}`);
    $('#detalle_aula').text(matricula.aula_asignada || 'Sin asignar');
    $('#detalle_periodo').text(`${matricula.periodo_nombre} (${matricula.anio})`);
    $('#detalle_tipo').text(matricula.tipo_matricula);
    
    // Capacidad de sección
    if (matricula.capacidad_maxima && matricula.companeros_seccion !== undefined) {
        const porcentaje = Math.round((matricula.companeros_seccion / matricula.capacidad_maxima) * 100);
        $('#detalle_capacidad_bar').css('width', porcentaje + '%');
        $('#detalle_capacidad_texto').text(`${matricula.companeros_seccion}/${matricula.capacidad_maxima}`);
        
        // Cambiar color según ocupación
        const barClass = porcentaje >= 90 ? 'bg-danger' : porcentaje >= 75 ? 'bg-warning' : 'bg-success';
        $('#detalle_capacidad_bar').removeClass('bg-success bg-warning bg-danger').addClass(barClass);
    }
    
    // Información personal
    $('#detalle_codigo_estudiante').text(matricula.codigo_estudiante);
    $('#detalle_documento').text(`${matricula.documento_tipo || 'DNI'}: ${matricula.documento_numero}`);
    
    if (matricula.fecha_nacimiento) {
        $('#detalle_fecha_nacimiento').text(formatearFecha(matricula.fecha_nacimiento));
        $('#detalle_edad').text(calcularEdad(matricula.fecha_nacimiento) + ' años');
    }
    
    // Información de contacto
    const datosPersonales = matricula.datos_personales ? JSON.parse(matricula.datos_personales) : {};
    let contactoHtml = '';
    if (datosPersonales.telefono) {
        contactoHtml += `<div><i class="ti ti-phone me-1"></i> ${datosPersonales.telefono}</div>`;
    }
    if (datosPersonales.email) {
        contactoHtml += `<div><i class="ti ti-mail me-1"></i> ${datosPersonales.email}</div>`;
    }
    if (datosPersonales.direccion) {
        contactoHtml += `<div><i class="ti ti-map-pin me-1"></i> ${datosPersonales.direccion}</div>`;
    }
    $('#detalle_contacto').html(contactoHtml || '<span class="text-muted">No registrado</span>');
    
    // Fechas importantes
    $('#detalle_fecha_matricula').text(formatearFecha(matricula.fecha_matricula));
    $('#detalle_fecha_creacion').text(formatearFecha(matricula.fecha_creacion, true));
    $('#detalle_fecha_actualizacion').text(formatearFecha(matricula.fecha_actualizacion, true));
    
    // Estado y documentación
    $('#detalle_estado_badge').removeClass().addClass('badge fs-6 ' + estadoConfig.badgeClass).text(matricula.estado);
    
    const datosMatricula = matricula.datos_matricula ? JSON.parse(matricula.datos_matricula) : {};
    const documentosCompletos = datosMatricula.documentos_completos;
    
    if (documentosCompletos) {
        $('#detalle_documentos_icon').html('<i class="ti ti-check text-success"></i>');
        $('#detalle_documentos_texto').text('Documentación completa');
    } else {
        $('#detalle_documentos_icon').html('<i class="ti ti-alert-triangle text-warning"></i>');
        $('#detalle_documentos_texto').text('Documentación pendiente');
    }
    
    if (matricula.activo == 1) {
        $('#detalle_activo_icon').html('<i class="ti ti-check text-success"></i>');
        $('#detalle_activo_texto').text('Registro activo');
    } else {
        $('#detalle_activo_icon').html('<i class="ti ti-x text-danger"></i>');
        $('#detalle_activo_texto').text('Registro inactivo');
    }
    
    // Observaciones
    if (datosMatricula.observaciones && datosMatricula.observaciones.trim()) {
        $('#detalle_observaciones').text(datosMatricula.observaciones);
        $('#observaciones-card').show();
    } else {
        $('#observaciones-card').hide();
    }
    
    // Configurar botones
    $('#btnEditarDesdeDetalle').off('click').on('click', function() {
        $('#modalDetalleMatricula').modal('hide');
        editarMatricula(matricula.id);
    });
    
    $('#btnImprimirDesdeDetalle').off('click').on('click', function() {
        imprimirConstancia(matricula.id);
    });
    
    $('#btnVerHistorialDetalle').off('click').on('click', function() {
        verHistorialCompleto(matricula.id);
    });
    
    // Cargar información de apoderados
    cargarApoderadosDetalle(matricula.estudiante_id);
    
    // Mostrar modal
    $('#modalDetalleMatricula').modal('show');
}

function cargarApoderadosDetalle(estudianteId) {
    fetch('modales/matriculas/procesar_matriculas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_apoderados&estudiante_id=${estudianteId}`
    })
    .then(response => response.json())
    .then(data => {
        let apoderadosHtml = '';
        
        if (data.success && data.apoderados && data.apoderados.length > 0) {
            data.apoderados.forEach(apoderado => {
                const esPrincipal = apoderado.es_principal == 1;
                apoderadosHtml += `
                    <div class="border rounded p-3 mb-3 ${esPrincipal ? 'border-primary' : ''}">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="fw-bold">${apoderado.nombres} ${apoderado.apellidos}</div>
                                <div class="text-muted">${apoderado.parentesco}</div>
                                ${esPrincipal ? '<span class="badge bg-primary">Apoderado Principal</span>' : ''}
                            </div>
                            <div class="col-auto">
                                <div class="text-end">
                                    <div><i class="ti ti-phone me-1"></i> ${apoderado.telefono || 'No registrado'}</div>
                                    <div><i class="ti ti-mail me-1"></i> ${apoderado.email || 'No registrado'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            apoderadosHtml = '<div class="text-center text-muted">No hay apoderados registrados</div>';
        }
        
        $('#detalle_apoderados').html(apoderadosHtml);
    })
    .catch(error => {
        $('#detalle_apoderados').html('<div class="text-center text-danger">Error al cargar información de apoderados</div>');
    });
}

function getEstadoConfig(estado) {
    const configs = {
        'MATRICULADO': {
            icon: 'ti ti-check-circle',
            color: 'text-success',
            badgeClass: 'bg-success'
        },
        'RETIRADO': {
            icon: 'ti ti-x-circle',
            color: 'text-danger',
            badgeClass: 'bg-danger'
        },
        'TRASLADADO': {
            icon: 'ti ti-arrow-right-circle',
            color: 'text-warning',
            badgeClass: 'bg-warning text-dark'
        },
        'RESERVADO': {
            icon: 'ti ti-clock-circle',
            color: 'text-info',
            badgeClass: 'bg-info'
        }
    };
    
    return configs[estado] || configs['MATRICULADO'];
}

function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const diferenciaMes = hoy.getMonth() - nacimiento.getMonth();
    
    if (diferenciaMes < 0 || (diferenciaMes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

function verHistorialCompleto(id) {
    Swal.fire({
        title: 'Historial Completo de Matrícula',
        html: '<div class="text-center"><div class="spinner-border" role="status"></div><br>Cargando historial...</div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    fetch('modales/matriculas/procesar_matriculas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=historial_completo&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let historialHtml = '<div class="text-left">';
            
            if (data.historial && data.historial.length > 0) {
                data.historial.forEach(evento => {
                    historialHtml += `
                        <div class="border-start border-3 border-primary ps-3 mb-3">
                            <div class="fw-bold">${evento.accion}</div>
                            <div class="text-muted small">${formatearFecha(evento.fecha, true)}</div>
                            ${evento.usuario ? `<div class="small">Por: ${evento.usuario}</div>` : ''}
                            ${evento.detalles ? `<div class="small text-muted">${evento.detalles}</div>` : ''}
                        </div>
                    `;
                });
            } else {
                historialHtml += '<div class="text-center text-muted">No hay cambios registrados</div>';
            }
            
            historialHtml += '</div>';
            
            Swal.fire({
                title: 'Historial Completo',
                html: historialHtml,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo cargar el historial',
            icon: 'error'
        });
    });
}
</script>

<style>
.info-group {
    margin-bottom: 0.75rem;
}

.info-group label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    display: block;
}

.modal-xl .card {
    border: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.border-start {
    border-left-width: 4px !important;
}

.font-monospace {
    font-family: 'Courier New', monospace !important;
}

#detalle_apoderados .border {
    transition: all 0.2s ease;
}

#detalle_apoderados .border:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>