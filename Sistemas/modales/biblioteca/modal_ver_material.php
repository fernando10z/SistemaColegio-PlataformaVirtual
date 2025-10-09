<!-- Modal Ver Material Bibliográfico -->
<div class="modal fade" id="modalVerMaterial" tabindex="-1" aria-labelledby="modalVerMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); color: #1565c0;">
                <h5 class="modal-title" id="modalVerMaterialLabel">
                    <i class="ti ti-book-2 me-2"></i>
                    Detalles del Material Bibliográfico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    
                    <!-- Información Principal -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <div class="libro-display" style="width: 100px; height: 140px; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                            <i class="ti ti-book" style="font-size: 3rem; color: #5e35b1;"></i>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-primary" id="ver_tipo_badge">LIBRO</span>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <h4 class="fw-bold text-dark mb-2" id="ver_titulo">-</h4>
                                        <h6 class="text-muted mb-3" id="ver_subtitulo">-</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">ISBN</small>
                                                <strong id="ver_isbn" style="font-family: 'Courier New', monospace;">-</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Código de Barras</small>
                                                <strong id="ver_codigo_barras" style="font-family: 'Courier New', monospace;">-</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">Estado</small>
                                                <span class="badge bg-success" id="ver_estado_badge">Activo</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Autores -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-users me-2"></i>
                                    Autoría
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="ver_autores_lista">
                                    <!-- Se llenará dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Publicación -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-calendar me-2"></i>
                                    Publicación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-row mb-2">
                                    <small class="text-muted">Editorial:</small>
                                    <strong class="ms-2" id="ver_editorial">-</strong>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Año:</small>
                                    <strong class="ms-2" id="ver_anio">-</strong>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Edición:</small>
                                    <strong class="ms-2" id="ver_edicion">-</strong>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Idioma:</small>
                                    <strong class="ms-2" id="ver_idioma">-</strong>
                                </div>
                                <div class="info-row">
                                    <small class="text-muted">Páginas:</small>
                                    <strong class="ms-2" id="ver_paginas">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clasificación -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-tag me-2"></i>
                                    Clasificación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-row mb-2">
                                    <small class="text-muted">Categoría:</small>
                                    <span class="badge bg-info ms-2" id="ver_categoria">-</span>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Código Dewey:</small>
                                    <span class="badge bg-success ms-2" id="ver_codigo_dewey" style="font-family: 'Courier New', monospace;">-</span>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Ubicación:</small>
                                    <span class="badge bg-warning text-dark ms-2" id="ver_ubicacion">-</span>
                                </div>
                                <div class="info-row">
                                    <small class="text-muted d-block mb-1">Palabras Clave:</small>
                                    <div id="ver_palabras_clave">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ejemplares y Estado -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-package me-2"></i>
                                    Ejemplares y Estado
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2" style="background: #e8f5e9; border-radius: 8px;">
                                            <h3 class="mb-0 fw-bold text-success" id="ver_total_ejemplares">0</h3>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2" style="background: #e3f2fd; border-radius: 8px;">
                                            <h3 class="mb-0 fw-bold text-primary" id="ver_ejemplares_disponibles">0</h3>
                                            <small class="text-muted">Disponibles</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2" style="background: #fff3e0; border-radius: 8px;">
                                            <h3 class="mb-0 fw-bold text-warning" id="ver_ejemplares_prestados">0</h3>
                                            <small class="text-muted">Prestados</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <small class="text-muted">Estado General:</small>
                                    <span class="badge bg-secondary ms-2" id="ver_estado_general">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adquisición -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-shopping-cart me-2"></i>
                                    Datos de Adquisición
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-row mb-2">
                                    <small class="text-muted">Fecha de Adquisición:</small>
                                    <strong class="ms-2" id="ver_fecha_adquisicion">-</strong>
                                </div>
                                <div class="info-row mb-2">
                                    <small class="text-muted">Precio:</small>
                                    <strong class="ms-2 text-success" id="ver_precio">-</strong>
                                </div>
                                <div class="info-row">
                                    <small class="text-muted">Proveedor:</small>
                                    <strong class="ms-2" id="ver_proveedor">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas del Sistema -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-clock me-2"></i>
                                    Información del Sistema
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="info-row mb-2">
                                    <small class="text-muted">Fecha de Registro:</small>
                                    <strong class="ms-2" id="ver_fecha_creacion">-</strong>
                                </div>
                                <div class="info-row">
                                    <small class="text-muted">ID del Material:</small>
                                    <span class="badge bg-dark ms-2" id="ver_material_id" style="font-family: 'Courier New', monospace;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Ejemplares -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                <h6 class="mb-0 text-dark">
                                    <i class="ti ti-list me-2"></i>
                                    Lista de Ejemplares
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>N° Ejemplar</th>
                                                <th>Código Inventario</th>
                                                <th>Estado</th>
                                                <th>Ubicación</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ver_ejemplares_tabla">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    No hay ejemplares registrados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirFicha()">
                    <i class="ti ti-printer me-2"></i>Imprimir Ficha
                </button>
                <button type="button" class="btn btn-warning" onclick="editarMaterialDesdeVer()">
                    <i class="ti ti-edit me-2"></i>Editar Material
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.info-row {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.libro-display {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

#ver_palabras_clave .badge {
    margin: 0.2rem;
}
</style>

<script>
let materialActualId = null;

function cargarDatosVisualizacion(material) {
    // Guardar ID actual
    materialActualId = material.id;
    
    // Decodificar JSON
    const datosBasicos = material.datos_basicos || {};
    const datosPublicacion = material.datos_publicacion || {};
    const clasificacion = material.clasificacion || {};
    const datosFisicos = material.datos_fisicos || {};
    const datosAdquisicion = material.datos_adquisicion || {};
    const autores = material.autores || [];
    
    // Información Principal
    $('#ver_titulo').text(datosBasicos.titulo || 'Sin título');
    $('#ver_subtitulo').text(datosBasicos.subtitulo || '-').toggle(!!datosBasicos.subtitulo);
    $('#ver_isbn').text(datosBasicos.isbn || '-');
    $('#ver_codigo_barras').text(material.codigo_barras || '-');
    $('#ver_tipo_badge').text(datosBasicos.tipo || 'LIBRO');
    $('#ver_material_id').text('#' + material.id);
    
    // Estado
    if (material.activo == 1) {
        $('#ver_estado_badge').removeClass('bg-danger').addClass('bg-success').text('Activo');
    } else {
        $('#ver_estado_badge').removeClass('bg-success').addClass('bg-danger').text('Inactivo');
    }
    
    // Autores
    $('#ver_autores_lista').empty();
    if (autores.length > 0) {
        autores.forEach((autor, index) => {
            const autorHTML = `
                <div class="d-flex align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 6px;">
                    <div class="flex-grow-1">
                        <strong>${autor.nombre || ''} ${autor.apellido || ''}</strong>
                        ${autor.principal ? '<span class="badge bg-primary ms-2" style="font-size: 0.65rem;">Principal</span>' : ''}
                    </div>
                </div>
            `;
            $('#ver_autores_lista').append(autorHTML);
        });
    } else {
        $('#ver_autores_lista').html('<p class="text-muted mb-0">No hay autores registrados</p>');
    }
    
    // Publicación
    $('#ver_editorial').text(datosPublicacion.editorial || '-');
    $('#ver_anio').text(datosPublicacion.anio_publicacion || '-');
    $('#ver_edicion').text(datosPublicacion.edicion || 'No especificada');
    $('#ver_idioma').text(datosPublicacion.idioma || '-');
    $('#ver_paginas').text(datosPublicacion.paginas ? datosPublicacion.paginas + ' págs.' : '-');
    
    // Clasificación
    $('#ver_categoria').text(clasificacion.categoria || '-');
    $('#ver_codigo_dewey').text(clasificacion.codigo_dewey || '-');
    $('#ver_ubicacion').text(datosFisicos.ubicacion || '-');
    
    // Palabras clave
    $('#ver_palabras_clave').empty();
    if (clasificacion.palabras_clave) {
        const palabras = clasificacion.palabras_clave.split(',').map(p => p.trim());
        palabras.forEach(palabra => {
            if (palabra) {
                $('#ver_palabras_clave').append(`<span class="badge bg-secondary">${palabra}</span> `);
            }
        });
    } else {
        $('#ver_palabras_clave').html('<small class="text-muted">Sin palabras clave</small>');
    }
    
    // Ejemplares
    $('#ver_total_ejemplares').text(material.total_ejemplares || 0);
    $('#ver_ejemplares_disponibles').text(material.ejemplares_disponibles || 0);
    $('#ver_ejemplares_prestados').text(material.ejemplares_prestados || 0);
    $('#ver_estado_general').text(datosFisicos.estado_general || '-');
    
    // Adquisición
    if (datosAdquisicion.fecha_adquisicion) {
        const fecha = new Date(datosAdquisicion.fecha_adquisicion);
        $('#ver_fecha_adquisicion').text(fecha.toLocaleDateString('es-PE'));
    } else {
        $('#ver_fecha_adquisicion').text('-');
    }
    
    if (datosAdquisicion.precio) {
        $('#ver_precio').text('S/ ' + parseFloat(datosAdquisicion.precio).toFixed(2));
    } else {
        $('#ver_precio').text('-');
    }
    
    $('#ver_proveedor').text(datosAdquisicion.proveedor || 'No especificado');
    
    // Fecha de creación
    if (material.fecha_creacion) {
        const fechaCreacion = new Date(material.fecha_creacion);
        $('#ver_fecha_creacion').text(fechaCreacion.toLocaleString('es-PE'));
    } else {
        $('#ver_fecha_creacion').text('-');
    }
    
    // Cargar ejemplares
    cargarEjemplaresTotales(material.id);
}

function cargarEjemplaresTotales(materialId) {
    $.ajax({
        url: 'modales/biblioteca/procesar_materiales.php',
        type: 'POST',
        data: {
            accion: 'obtener_ejemplares',
            material_id: materialId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.ejemplares.length > 0) {
                let html = '';
                response.ejemplares.forEach(ejemplar => {
                    let estadoBadge = '';
                    switch(ejemplar.estado) {
                        case 'DISPONIBLE':
                            estadoBadge = '<span class="badge bg-success">Disponible</span>';
                            break;
                        case 'PRESTADO':
                            estadoBadge = '<span class="badge bg-warning">Prestado</span>';
                            break;
                        case 'RESERVADO':
                            estadoBadge = '<span class="badge bg-info">Reservado</span>';
                            break;
                        case 'MANTENIMIENTO':
                            estadoBadge = '<span class="badge bg-secondary">Mantenimiento</span>';
                            break;
                        case 'BAJA':
                            estadoBadge = '<span class="badge bg-danger">Baja</span>';
                            break;
                        default:
                            estadoBadge = '<span class="badge bg-secondary">'+ejemplar.estado+'</span>';
                    }
                    
                    html += `
                        <tr>
                            <td><strong>${ejemplar.numero_ejemplar}</strong></td>
                            <td><code>${ejemplar.codigo_inventario || '-'}</code></td>
                            <td>${estadoBadge}</td>
                            <td>${ejemplar.ubicacion_especifica || '-'}</td>
                            <td><small>${ejemplar.observaciones || '-'}</small></td>
                        </tr>
                    `;
                });
                $('#ver_ejemplares_tabla').html(html);
            } else {
                $('#ver_ejemplares_tabla').html('<tr><td colspan="5" class="text-center text-muted">No hay ejemplares registrados</td></tr>');
            }
        },
        error: function() {
            $('#ver_ejemplares_tabla').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar ejemplares</td></tr>');
        }
    });
}

function editarMaterialDesdeVer() {
    $('#modalVerMaterial').modal('hide');
    setTimeout(() => {
        editarMaterial(materialActualId);
    }, 300);
}

function imprimirFicha() {
    const contenido = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ficha Bibliográfica</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h2 { color: #333; border-bottom: 2px solid #5e35b1; padding-bottom: 10px; }
                .info-row { margin: 10px 0; }
                .label { font-weight: bold; color: #666; }
                .value { color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #5e35b1; color: white; }
            </style>
        </head>
        <body>
            <h2>Ficha Bibliográfica</h2>
            <div class="info-row">
                <span class="label">Título:</span>
                <span class="value">${$('#ver_titulo').text()}</span>
            </div>
            <div class="info-row">
                <span class="label">ISBN:</span>
                <span class="value">${$('#ver_isbn').text()}</span>
            </div>
            <div class="info-row">
                <span class="label">Categoría:</span>
                <span class="value">${$('#ver_categoria').text()}</span>
            </div>
            <div class="info-row">
                <span class="label">Ubicación:</span>
                <span class="value">${$('#ver_ubicacion').text()}</span>
            </div>
            <div class="info-row">
                <span class="label">Total Ejemplares:</span>
                <span class="value">${$('#ver_total_ejemplares').text()}</span>
            </div>
            
            <h3>Ejemplares</h3>
            <table>
                ${$('#ver_ejemplares_tabla').html()}
            </table>
        </body>
        </html>
    `;
    
    const ventana = window.open('', '_blank');
    ventana.document.write(contenido);
    ventana.document.close();
    ventana.print();
}
</script>