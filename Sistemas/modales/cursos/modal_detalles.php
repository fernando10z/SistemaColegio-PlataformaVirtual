<!-- modales/cursos/modal_detalles.php -->
<div class="modal fade" id="modalDetallesCurso" tabindex="-1" aria-labelledby="modalDetallesCursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalDetallesCursoLabel">
                    <i class="ti ti-info-circle me-2"></i>
                    Detalles Completos del Curso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="row">
                    <!-- Información Principal -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm" id="detalle_header_curso">
                            <div class="card-body text-center p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem;">
                                <div class="curso-codigo-badge mb-2">
                                    <span class="badge bg-white text-dark fs-5" id="detalle_codigo">CURSO-001</span>
                                </div>
                                <h3 class="fw-bold mb-2" id="detalle_nombre">Nombre del Curso</h3>
                                <p class="mb-3 opacity-90" id="detalle_descripcion">Descripción del curso</p>
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    <div class="badge bg-white text-dark">
                                        <i class="ti ti-book me-1"></i>
                                        <span id="detalle_area">Área</span>
                                    </div>
                                    <div class="badge bg-white text-dark">
                                        <i class="ti ti-users me-1"></i>
                                        <span id="detalle_seccion">Sección</span>
                                    </div>
                                    <div class="badge bg-white text-dark">
                                        <i class="ti ti-calendar me-1"></i>
                                        <span id="detalle_periodo">Periodo</span>
                                    </div>
                                    <div class="badge" id="detalle_estado_badge">
                                        <i class="ti ti-circle-check me-1"></i>
                                        <span id="detalle_estado">Estado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Docente Responsable -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-user-check me-2 text-primary"></i>
                                    Docente Responsable
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-docente me-3">
                                        <img src="../assets/images/profile/user-default.jpg" 
                                             class="rounded-circle" width="60" height="60" 
                                             id="detalle_docente_foto" alt="Docente">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold" id="detalle_docente_nombre">Nombre Docente</h6>
                                        <small class="text-muted" id="detalle_docente_email">email@example.com</small>
                                        <div class="mt-1">
                                            <span class="badge bg-info" id="detalle_docente_codigo">DOC001</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="info-adicional">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total de Asignaciones:</span>
                                        <strong id="detalle_docente_asignaciones">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Es Tutor:</span>
                                        <strong id="detalle_docente_tutor">No</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas Generales -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-chart-bar me-2 text-success"></i>
                                    Estadísticas del Curso
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center g-3">
                                    <div class="col-4">
                                        <div class="stat-box p-3 border rounded">
                                            <i class="ti ti-users fs-2 text-primary mb-2"></i>
                                            <div class="fs-3 fw-bold" id="detalle_total_estudiantes">0</div>
                                            <small class="text-muted">Estudiantes</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box p-3 border rounded">
                                            <i class="ti ti-chart-line fs-2 text-success mb-2"></i>
                                            <div class="fs-3 fw-bold" id="detalle_progreso_promedio">0%</div>
                                            <small class="text-muted">Progreso</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box p-3 border rounded">
                                            <i class="ti ti-activity fs-2 text-info mb-2"></i>
                                            <div class="fs-3 fw-bold" id="detalle_participacion">0%</div>
                                            <small class="text-muted">Participación</small>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="info-adicional">
                                    <div class="mb-2">
                                        <small class="text-muted">Fecha de Creación:</small>
                                        <strong class="float-end" id="detalle_fecha_creacion">--/--/----</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted">Última Actualización:</small>
                                        <strong class="float-end" id="detalle_fecha_actualizacion">--/--/----</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración del Curso -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-settings me-2 text-warning"></i>
                                    Configuración del Curso
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-muted small">Fecha Inicio</label>
                                        <div class="fw-bold">
                                            <i class="ti ti-calendar-event me-1 text-info"></i>
                                            <span id="detalle_fecha_inicio">--/--/----</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-muted small">Fecha Fin</label>
                                        <div class="fw-bold">
                                            <i class="ti ti-calendar-check me-1 text-danger"></i>
                                            <span id="detalle_fecha_fin">--/--/----</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-muted small">Duración</label>
                                        <div class="fw-bold">
                                            <i class="ti ti-clock me-1 text-success"></i>
                                            <span id="detalle_duracion">0 días</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label text-muted small">Color del Tema</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div id="detalle_color_preview" style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid #dee2e6;"></div>
                                            <span class="fw-bold" id="detalle_color_tema">#667eea</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Imagen de Portada</label>
                                        <div class="fw-bold text-truncate" id="detalle_imagen_portada">No configurada</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small">Inscripción Libre</label>
                                        <div class="fw-bold">
                                            <span class="badge" id="detalle_inscripcion_libre">No</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estudiantes Inscritos -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="ti ti-school me-2 text-primary"></i>
                                    Estudiantes Inscritos (<span id="detalle_count_estudiantes">0</span>)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportarEstudiantesCurso()">
                                    <i class="ti ti-download me-1"></i>
                                    Exportar
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="detalle_tabla_estudiantes_container">
                                    <p class="text-center text-muted py-4">
                                        <i class="ti ti-users-off fs-1"></i><br>
                                        No hay estudiantes inscritos
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido del Curso (Unidades) -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="ti ti-books me-2 text-info"></i>
                                    Estructura del Curso
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="gestionarContenido(detalleCursoId)">
                                    <i class="ti ti-edit me-1"></i>
                                    Gestionar Contenido
                                </button>
                            </div>
                            <div class="card-body" id="detalle_estructura_curso">
                                <p class="text-center text-muted py-4">
                                    <i class="ti ti-book-off fs-1"></i><br>
                                    No hay unidades creadas aún
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-info-square me-2 text-secondary"></i>
                                    Información Adicional
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">ID del Curso:</small>
                                        <div class="fw-bold" id="detalle_id">--</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">ID de Asignación:</small>
                                        <div class="fw-bold" id="detalle_asignacion_id">--</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Nivel Educativo:</small>
                                        <div class="fw-bold" id="detalle_nivel">--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-primary" onclick="imprimirDetallesCurso()">
                    <i class="ti ti-printer me-2"></i>
                    Imprimir
                </button>
                <button type="button" class="btn btn-outline-success" onclick="editarCurso(detalleCursoId)">
                    <i class="ti ti-edit me-2"></i>
                    Editar Curso
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    transition: all 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.avatar-docente img {
    object-fit: cover;
    border: 3px solid #667eea;
}

.info-adicional {
    font-size: 0.9rem;
}

.curso-codigo-badge {
    animation: fadeInDown 0.5s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.estudiante-row {
    transition: background-color 0.2s ease;
}

.estudiante-row:hover {
    background-color: #f8f9fa;
}

.progreso-bar-detalle {
    height: 10px;
    border-radius: 5px;
    background: #e9ecef;
    overflow: hidden;
}

.progreso-fill-detalle {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.5s ease;
}

.unidad-item {
    border-left: 3px solid #667eea;
    padding-left: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.unidad-item:hover {
    border-left-color: #764ba2;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
    padding: 0.5rem 1rem;
}

.leccion-item {
    font-size: 0.9rem;
    padding: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.leccion-item:last-child {
    border-bottom: none;
}
</style>

<script>
let detalleCursoId = null;

function mostrarDetallesCompletos(curso) {
    detalleCursoId = curso.id;
    
    // Información principal
    $('#detalle_id').text(curso.id);
    $('#detalle_codigo').text(curso.codigo_curso || 'SIN CÓDIGO');
    $('#detalle_nombre').text(curso.nombre);
    $('#detalle_descripcion').text(curso.descripcion || 'Sin descripción');
    
    // Información de contexto
    $('#detalle_area').text(curso.area_nombre || 'N/A');
    $('#detalle_seccion').text(`${curso.grado || ''} - ${curso.seccion || ''}`);
    $('#detalle_periodo').text(curso.periodo_nombre || 'N/A');
    $('#detalle_nivel').text(curso.nivel_nombre || 'N/A');
    $('#detalle_asignacion_id').text(curso.asignacion_id || '--');
    
    // Configuraciones
    const config = curso.configuraciones || {};
    const estado = config.estado || 'ACTIVO';
    
    $('#detalle_estado').text(estado);
    $('#detalle_estado_badge').removeClass('bg-success bg-warning bg-secondary')
                               .addClass(estado === 'ACTIVO' ? 'bg-success' : 
                                        estado === 'BORRADOR' ? 'bg-warning' : 'bg-secondary');
    
    // Fechas
    const fechaInicio = config.fecha_inicio || '';
    const fechaFin = config.fecha_fin || '';
    
    $('#detalle_fecha_inicio').text(fechaInicio ? formatearFecha(fechaInicio) : '--/--/----');
    $('#detalle_fecha_fin').text(fechaFin ? formatearFecha(fechaFin) : '--/--/----');
    
    // Calcular duración
    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const diffDays = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        $('#detalle_duracion').text(`${diffDays} días`);
    } else {
        $('#detalle_duracion').text('N/A');
    }
    
    // Color del tema
    const colorTema = config.color_tema || '#667eea';
    $('#detalle_color_tema').text(colorTema);
    $('#detalle_color_preview').css('background-color', colorTema);
    $('#detalle_header_curso .card-body').css('background', `linear-gradient(135deg, ${colorTema} 0%, #764ba2 100%)`);
    
    // Imagen de portada
    const imagenPortada = config.imagen_portada || '';
    $('#detalle_imagen_portada').text(imagenPortada || 'No configurada');
    
    // Inscripción libre
    const inscripcionLibre = config.inscripcion_libre || false;
    $('#detalle_inscripcion_libre').text(inscripcionLibre ? 'Sí' : 'No')
                                     .removeClass('bg-success bg-danger')
                                     .addClass(inscripcionLibre ? 'bg-success' : 'bg-danger');
    
    // Fechas del sistema
    $('#detalle_fecha_creacion').text(formatearFechaHora(curso.fecha_creacion));
    $('#detalle_fecha_actualizacion').text(formatearFechaHora(curso.fecha_actualizacion));
    
    // Docente
    $('#detalle_docente_nombre').text(`${curso.docente_nombres || ''} ${curso.docente_apellidos || ''}`);
    $('#detalle_docente_email').text(curso.docente_email || 'No disponible');
    $('#detalle_docente_codigo').text(curso.docente_codigo || 'N/A');
    $('#detalle_docente_asignaciones').text(curso.docente_total_asignaciones || '0');
    $('#detalle_docente_tutor').text(curso.es_tutor ? 'Sí' : 'No');
    
    // Estadísticas
    const stats = curso.estadisticas || {};
    const estudiantes = curso.estudiantes_inscritos || [];
    
    $('#detalle_total_estudiantes').text(Array.isArray(estudiantes) ? estudiantes.length : 0);
    $('#detalle_count_estudiantes').text(Array.isArray(estudiantes) ? estudiantes.length : 0);
    $('#detalle_progreso_promedio').text((stats.progreso_promedio || 0) + '%');
    $('#detalle_participacion').text((stats.participacion_activa || 0) + '%');
    
    // Cargar estudiantes
    cargarEstudiantesDetalle(estudiantes);
    
    // Cargar unidades (simulado - implementar según tu estructura)
    cargarUnidadesDetalle(curso.id);
    
    // Mostrar modal
    $('#modalDetallesCurso').modal('show');
}

function cargarEstudiantesDetalle(estudiantes) {
    if (!Array.isArray(estudiantes) || estudiantes.length === 0) {
        $('#detalle_tabla_estudiantes_container').html(`
            <p class="text-center text-muted py-4">
                <i class="ti ti-users-off fs-1"></i><br>
                No hay estudiantes inscritos en este curso
            </p>
        `);
        return;
    }
    
    let html = `
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>Código</th>
                    <th>Estado</th>
                    <th>Progreso</th>
                    <th>Fecha Inscripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    estudiantes.forEach(est => {
        const estado = est.estado || 'ACTIVO';
        const progreso = est.progreso || 0;
        const fechaInscripcion = est.fecha_inscripcion || '';
        
        html += `
            <tr class="estudiante-row">
                <td>
                    <div class="d-flex align-items-center">
                        <img src="../assets/images/profile/user-default.jpg" 
                             class="rounded-circle me-2" width="32" height="32" alt="">
                        <span class="fw-bold">Estudiante ${est.estudiante_id}</span>
                    </div>
                </td>
                <td><span class="badge bg-info">EST-${String(est.estudiante_id).padStart(3, '0')}</span></td>
                <td><span class="badge ${estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary'}">${estado}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progreso-bar-detalle flex-grow-1">
                            <div class="progreso-fill-detalle" style="width: ${progreso}%"></div>
                        </div>
                        <small class="fw-bold">${progreso}%</small>
                    </div>
                </td>
                <td><small>${fechaInscripcion ? formatearFecha(fechaInscripcion) : 'N/A'}</small></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            onclick="verPerfilEstudiante(${est.estudiante_id})">
                        <i class="ti ti-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    $('#detalle_tabla_estudiantes_container').html(html);
}

function cargarUnidadesDetalle(cursoId) {
    // Simulación de carga de unidades - ajustar según tu base de datos
    mostrarCarga();
    
    fetch('modales/cursos/procesar_cursos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=obtener_unidades&curso_id=${cursoId}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success && data.unidades && data.unidades.length > 0) {
            mostrarUnidadesEnDetalle(data.unidades);
        } else {
            $('#detalle_estructura_curso').html(`
                <p class="text-center text-muted py-4">
                    <i class="ti ti-book-off fs-1"></i><br>
                    No hay unidades creadas para este curso
                </p>
            `);
        }
    })
    .catch(error => {
        ocultarCarga();
        $('#detalle_estructura_curso').html(`
            <p class="text-center text-muted py-4">
                <i class="ti ti-book-off fs-1"></i><br>
                No se pudo cargar la estructura del curso
            </p>
        `);
    });
}

function mostrarUnidadesEnDetalle(unidades) {
    let html = '<div class="accordion" id="accordionUnidades">';
    
    unidades.forEach((unidad, index) => {
        const lecciones = unidad.lecciones || [];
        
        html += `
            <div class="accordion-item border-0 mb-2">
                <h2 class="accordion-header" id="heading${index}">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapse${index}">
                        <i class="ti ti-book-2 me-2"></i>
                        <strong>Unidad ${unidad.orden || index + 1}:</strong>&nbsp;${unidad.titulo}
                        <span class="badge bg-primary ms-2">${lecciones.length} lecciones</span>
                    </button>
                </h2>
                <div id="collapse${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                     data-bs-parent="#accordionUnidades">
                    <div class="accordion-body">
                        <p class="text-muted">${unidad.descripcion || 'Sin descripción'}</p>
                        ${lecciones.length > 0 ? `
                            <div class="lecciones-list">
                                ${lecciones.map((leccion, idx) => `
                                    <div class="leccion-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="ti ti-file-text me-2 text-info"></i>
                                            <strong>Lección ${idx + 1}:</strong> ${leccion.titulo}
                                            <span class="badge bg-secondary ms-2">${leccion.tipo || 'CONTENIDO'}</span>
                                        </div>
                                        <small class="text-muted">${leccion.tiempo_estimado || 0} min</small>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-muted"><i>No hay lecciones en esta unidad</i></p>'}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    $('#detalle_estructura_curso').html(html);
}

function formatearFecha(fecha) {
    if (!fecha) return '--/--/----';
    const f = new Date(fecha);
    const dia = String(f.getDate()).padStart(2, '0');
    const mes = String(f.getMonth() + 1).padStart(2, '0');
    const anio = f.getFullYear();
    return `${dia}/${mes}/${anio}`;
}

function formatearFechaHora(fecha) {
    if (!fecha) return '--/--/---- --:--';
    const f = new Date(fecha);
    const dia = String(f.getDate()).padStart(2, '0');
    const mes = String(f.getMonth() + 1).padStart(2, '0');
    const anio = f.getFullYear();
    const hora = String(f.getHours()).padStart(2, '0');
    const min = String(f.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${min}`;
}

function exportarEstudiantesCurso() {
    if (!detalleCursoId) {
        mostrarError('No se puede exportar sin un curso seleccionado');
        return;
    }
    
    window.open(`reportes/exportar_estudiantes_curso.php?curso_id=${detalleCursoId}`, '_blank');
}

function imprimirDetallesCurso() {
    window.print();
}

function verPerfilEstudiante(estudianteId) {
    // Implementar según tu sistema
    window.location.href = `estudiantes.php?ver=${estudianteId}`;
}

// Limpiar al cerrar
$('#modalDetallesCurso').on('hidden.bs.modal', function() {
    detalleCursoId = null;
    $('#detalle_estructura_curso').html(`
        <p class="text-center text-muted py-4">
            <i class="ti ti-book-off fs-1"></i><br>
            No hay unidades creadas aún
        </p>
    `);
});

// Estilos de impresión
const estilosImpresion = `
    @media print {
        .modal-header, .modal-footer, .btn, .accordion-button {
            display: none !important;
        }
        .modal-dialog {
            max-width: 100% !important;
            margin: 0 !important;
        }
        .modal-content {
            border: none !important;
            box-shadow: none !important;
        }
        .card {
            page-break-inside: avoid;
        }
    }
`;

// Agregar estilos de impresión
const styleSheet = document.createElement("style");
styleSheet.innerText = estilosImpresion;
document.head.appendChild(styleSheet);
</script>