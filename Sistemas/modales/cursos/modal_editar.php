<!-- modales/cursos/modal_editar.php -->
<div class="modal fade" id="modalEditarCurso" tabindex="-1" aria-labelledby="modalEditarCursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalEditarCursoLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Curso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarCurso" method="POST">
                <input type="hidden" id="edit_curso_id" name="curso_id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica del Curso
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_codigo_curso" class="form-label">
                                                Código del Curso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_codigo_curso" name="codigo_curso" 
                                                   placeholder="MAT-1SA-2025" required maxlength="50" 
                                                   pattern="[A-Z0-9\-]{5,50}">
                                            <div class="form-text">Solo mayúsculas, números y guiones (5-50 caracteres)</div>
                                            <div class="invalid-feedback">Código inválido</div>
                                        </div>
                                        
                                        <div class="col-md-8 mb-3">
                                            <label for="edit_nombre" class="form-label">
                                                Nombre del Curso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_nombre" name="nombre" 
                                                   placeholder="Matemática - 1ro Secundaria A" required 
                                                   minlength="5" maxlength="200">
                                            <div class="invalid-feedback">El nombre debe tener entre 5 y 200 caracteres</div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="edit_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción breve del curso" required 
                                                      minlength="10" maxlength="500"></textarea>
                                            <div class="character-count text-muted">
                                                <small><span id="edit_desc_count">0</span>/500 caracteres</small>
                                            </div>
                                            <div class="invalid-feedback">La descripción debe tener entre 10 y 500 caracteres</div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="edit_asignacion_id" class="form-label">
                                                Asignación Docente (Área + Sección) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_asignacion_id" name="asignacion_id" required>
                                                <option value="">Seleccionar asignación...</option>
                                                <?php foreach ($asignaciones_disponibles as $asig): ?>
                                                    <option value="<?= $asig['id'] ?>">
                                                        <?= htmlspecialchars($asig['nivel_nombre']) ?> - 
                                                        <?= htmlspecialchars($asig['grado']) ?> "<?= htmlspecialchars($asig['seccion']) ?>" - 
                                                        <?= htmlspecialchars($asig['area_nombre']) ?> - 
                                                        Prof. <?= htmlspecialchars($asig['docente_apellidos']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Debe seleccionar una asignación válida</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuraciones -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración del Curso
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_estado" class="form-label">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_estado" name="estado" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="ACTIVO">Activo</option>
                                                <option value="BORRADOR">Borrador</option>
                                                <option value="FINALIZADO">Finalizado</option>
                                            </select>
                                            <div class="invalid-feedback">Debe seleccionar un estado</div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_fecha_inicio" class="form-label">
                                                Fecha Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_inicio" 
                                                   name="fecha_inicio" required>
                                            <div class="invalid-feedback">Fecha de inicio requerida</div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_fecha_fin" class="form-label">
                                                Fecha Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_fin" 
                                                   name="fecha_fin" required>
                                            <div class="invalid-feedback">Fecha de fin requerida y debe ser posterior al inicio</div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_color_tema" class="form-label">Color del Tema</label>
                                            <div class="d-flex gap-2">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="edit_color_tema" name="color_tema" value="#667eea">
                                                <input type="text" class="form-control" id="edit_color_hex" 
                                                       maxlength="7" pattern="#[0-9A-Fa-f]{6}" 
                                                       placeholder="#667eea">
                                            </div>
                                            <div class="form-text">Formato: #RRGGBB</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_imagen_portada" class="form-label">URL Imagen de Portada</label>
                                            <input type="text" class="form-control" id="edit_imagen_portada" 
                                                   name="imagen_portada" placeholder="/img/curso_portada.jpg" 
                                                   maxlength="255" pattern="^\/[\w\-\/\.]+\.(jpg|jpeg|png|gif|webp)$">
                                            <div class="form-text">Debe ser una ruta válida de imagen (jpg, png, gif, webp)</div>
                                            <div class="invalid-feedback">Formato de URL inválido</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="edit_inscripcion_libre" 
                                                       name="inscripcion_libre" value="1">
                                                <label class="form-check-label" for="edit_inscripcion_libre">
                                                    Permitir inscripción libre de estudiantes
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas Actuales (Solo Lectura) -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Estadísticas Actuales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="stat-box p-3">
                                                <i class="ti ti-users fs-1 text-primary"></i>
                                                <div class="fs-3 fw-bold mt-2" id="edit_total_estudiantes">0</div>
                                                <small class="text-muted">Estudiantes Inscritos</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-box p-3">
                                                <i class="ti ti-chart-line fs-1 text-success"></i>
                                                <div class="fs-3 fw-bold mt-2" id="edit_progreso_promedio">0%</div>
                                                <small class="text-muted">Progreso Promedio</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-box p-3">
                                                <i class="ti ti-activity fs-1 text-info"></i>
                                                <div class="fs-3 fw-bold mt-2" id="edit_participacion">0%</div>
                                                <small class="text-muted">Participación Activa</small>
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
                    <button type="submit" class="btn btn-primary" id="btnActualizarCurso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.campo-error {
    border: 2px solid #dc3545 !important;
    background-color: #fff5f5;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
    10%, 30%, 50%, 70% { transform: translateX(-5px); }
}

.stat-box {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.stat-box:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
</style>

<script>
$(document).ready(function() {
    // Contador de caracteres para descripción
    $('#edit_descripcion').on('input', function() {
        const count = $(this).val().length;
        $('#edit_desc_count').text(count);
        
        if (count > 500) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Sincronizar selector de color con input de texto
    $('#edit_color_tema').on('input', function() {
        $('#edit_color_hex').val($(this).val());
    });

    $('#edit_color_hex').on('input', function() {
        const hex = $(this).val();
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            $('#edit_color_tema').val(hex);
            $(this).removeClass('campo-error');
        } else {
            $(this).addClass('campo-error');
        }
    });

    // Validación de fechas en tiempo real
    $('#edit_fecha_inicio, #edit_fecha_fin').on('change', function() {
        validarFechasEdicion();
    });

    // Validación de código en tiempo real
    $('#edit_codigo_curso').on('input', function() {
        let valor = $(this).val().toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        $(this).val(valor);
        
        if (valor.length >= 5 && valor.length <= 50) {
            $(this).removeClass('campo-error');
        } else if (valor.length > 0) {
            $(this).addClass('campo-error');
        }
    });

    // Validación de URL de imagen
    $('#edit_imagen_portada').on('blur', function() {
        const url = $(this).val().trim();
        if (url && !validarURLImagen(url)) {
            $(this).addClass('campo-error');
            mostrarErrorCampo(this, 'La URL debe ser una ruta válida de imagen');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Envío del formulario con validaciones exhaustivas
    $('#formEditarCurso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEdicionCompleto()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        // Preparar configuraciones JSON
        const configuraciones = {
            estado: $('#edit_estado').val(),
            fecha_inicio: $('#edit_fecha_inicio').val(),
            fecha_fin: $('#edit_fecha_fin').val(),
            color_tema: $('#edit_color_tema').val(),
            imagen_portada: $('#edit_imagen_portada').val(),
            inscripcion_libre: $('#edit_inscripcion_libre').is(':checked')
        };
        
        formData.append('configuraciones', JSON.stringify(configuraciones));
        
        mostrarCarga();
        $('#btnActualizarCurso').prop('disabled', true);

        fetch('modales/cursos/procesar_cursos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            $('#btnActualizarCurso').prop('disabled', false);
            
            if (data.success) {
                Swal.fire({
                    title: '¡Curso Actualizado!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalEditarCurso').modal('hide');
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
            $('#btnActualizarCurso').prop('disabled', false);
            mostrarError('Error al procesar la solicitud: ' + error);
        });
    });

    // Limpiar al cerrar
    $('#modalEditarCurso').on('hidden.bs.modal', function() {
        limpiarFormularioEdicion();
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA CON 25 VALIDACIONES
function validarFormularioEdicionCompleto() {
    let isValid = true;
    let erroresEncontrados = [];
    
    // Limpiar errores previos
    $('.campo-error, .is-invalid').removeClass('campo-error is-invalid');
    $('.invalid-feedback').hide();
    
    // 1. Validar ID del curso
    const cursoId = $('#edit_curso_id').val();
    if (!cursoId || parseInt(cursoId) <= 0) {
        erroresEncontrados.push('ID de curso inválido');
        isValid = false;
    }
    
    // 2. Validar código del curso (obligatorio, formato específico)
    const codigo = $('#edit_codigo_curso').val().trim();
    if (!codigo) {
        marcarCampoError('#edit_codigo_curso', 'El código del curso es obligatorio');
        erroresEncontrados.push('Código del curso requerido');
        isValid = false;
    } else if (codigo.length < 5 || codigo.length > 50) {
        marcarCampoError('#edit_codigo_curso', 'El código debe tener entre 5 y 50 caracteres');
        erroresEncontrados.push('Código: longitud incorrecta (5-50 caracteres)');
        isValid = false;
    } else if (!/^[A-Z0-9\-]+$/.test(codigo)) {
        marcarCampoError('#edit_codigo_curso', 'El código solo puede contener mayúsculas, números y guiones');
        erroresEncontrados.push('Código: formato inválido (solo A-Z, 0-9, -)');
        isValid = false;
    }
    
    // 3. Validar nombre del curso (obligatorio, longitud)
    const nombre = $('#edit_nombre').val().trim();
    if (!nombre) {
        marcarCampoError('#edit_nombre', 'El nombre del curso es obligatorio');
        erroresEncontrados.push('Nombre del curso requerido');
        isValid = false;
    } else if (nombre.length < 5 || nombre.length > 200) {
        marcarCampoError('#edit_nombre', 'El nombre debe tener entre 5 y 200 caracteres');
        erroresEncontrados.push('Nombre: longitud incorrecta (5-200 caracteres)');
        isValid = false;
    }
    
    // 4. Validar descripción (obligatorio, longitud)
    const descripcion = $('#edit_descripcion').val().trim();
    if (!descripcion) {
        marcarCampoError('#edit_descripcion', 'La descripción es obligatoria');
        erroresEncontrados.push('Descripción requerida');
        isValid = false;
    } else if (descripcion.length < 10 || descripcion.length > 500) {
        marcarCampoError('#edit_descripcion', 'La descripción debe tener entre 10 y 500 caracteres');
        erroresEncontrados.push('Descripción: longitud incorrecta (10-500 caracteres)');
        isValid = false;
    }
    
    // 5. Validar asignación docente
    const asignacionId = $('#edit_asignacion_id').val();
    if (!asignacionId) {
        marcarCampoError('#edit_asignacion_id', 'Debe seleccionar una asignación docente');
        erroresEncontrados.push('Asignación docente requerida');
        isValid = false;
    }
    
    // 6. Validar estado
    const estado = $('#edit_estado').val();
    if (!estado) {
        marcarCampoError('#edit_estado', 'Debe seleccionar un estado');
        erroresEncontrados.push('Estado requerido');
        isValid = false;
    } else if (!['ACTIVO', 'BORRADOR', 'FINALIZADO'].includes(estado)) {
        marcarCampoError('#edit_estado', 'Estado no válido');
        erroresEncontrados.push('Estado: valor no permitido');
        isValid = false;
    }
    
    // 7-10. Validar fechas (obligatorias, formato, lógica)
    const fechaInicio = $('#edit_fecha_inicio').val();
    const fechaFin = $('#edit_fecha_fin').val();
    
    if (!fechaInicio) {
        marcarCampoError('#edit_fecha_inicio', 'La fecha de inicio es obligatoria');
        erroresEncontrados.push('Fecha de inicio requerida');
        isValid = false;
    }
    
    if (!fechaFin) {
        marcarCampoError('#edit_fecha_fin', 'La fecha de fin es obligatoria');
        erroresEncontrados.push('Fecha de fin requerida');
        isValid = false;
    }
    
    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        // Validar que las fechas sean válidas
        if (isNaN(inicio.getTime())) {
            marcarCampoError('#edit_fecha_inicio', 'Fecha de inicio inválida');
            erroresEncontrados.push('Fecha inicio: formato inválido');
            isValid = false;
        }
        
        if (isNaN(fin.getTime())) {
            marcarCampoError('#edit_fecha_fin', 'Fecha de fin inválida');
            erroresEncontrados.push('Fecha fin: formato inválido');
            isValid = false;
        }
        
        // Validar que fecha fin sea posterior a fecha inicio
        if (fin <= inicio) {
            marcarCampoError('#edit_fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio');
            erroresEncontrados.push('Fechas: fin debe ser posterior al inicio');
            isValid = false;
        }
        
        // Validar duración mínima (al menos 1 semana)
        const diffTime = Math.abs(fin - inicio);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
            marcarCampoError('#edit_fecha_fin', 'El curso debe durar al menos 1 semana');
            erroresEncontrados.push('Duración: mínimo 7 días');
            isValid = false;
        }
        
        // Validar duración máxima (no más de 1 año)
        if (diffDays > 365) {
            marcarCampoError('#edit_fecha_fin', 'El curso no puede durar más de 1 año');
            erroresEncontrados.push('Duración: máximo 365 días');
            isValid = false;
        }
    }
    
    // 11. Validar color del tema (formato hexadecimal)
    const colorTema = $('#edit_color_tema').val();
    if (colorTema && !/^#[0-9A-Fa-f]{6}$/.test(colorTema)) {
        marcarCampoError('#edit_color_tema', 'Color inválido, use formato #RRGGBB');
        erroresEncontrados.push('Color: formato hexadecimal inválido');
        isValid = false;
    }
    
    // 12. Validar URL de imagen (formato si está presente)
    const imagenPortada = $('#edit_imagen_portada').val().trim();
    if (imagenPortada) {
        if (!validarURLImagen(imagenPortada)) {
            marcarCampoError('#edit_imagen_portada', 'URL de imagen inválida');
            erroresEncontrados.push('Imagen: URL no válida');
            isValid = false;
        }
        
        if (imagenPortada.length > 255) {
            marcarCampoError('#edit_imagen_portada', 'URL demasiado larga (máx. 255 caracteres)');
            erroresEncontrados.push('Imagen: URL muy larga');
            isValid = false;
        }
    }
    
    // 13. Validar que el nombre no sea solo números
    if (nombre && /^\d+$/.test(nombre)) {
        marcarCampoError('#edit_nombre', 'El nombre no puede contener solo números');
        erroresEncontrados.push('Nombre: no puede ser solo numérico');
        isValid = false;
    }
    
    // 14. Validar que el código no tenga caracteres especiales prohibidos
    if (codigo && /[<>'"&]/.test(codigo)) {
        marcarCampoError('#edit_codigo_curso', 'El código contiene caracteres no permitidos');
        erroresEncontrados.push('Código: caracteres especiales prohibidos');
        isValid = false;
    }
    
    // 15. Validar que la descripción no sea repetitiva
    if (descripcion) {
        const palabras = descripcion.toLowerCase().split(/\s+/);
        const palabrasUnicas = new Set(palabras);
        const porcentajeUnicidad = (palabrasUnicas.size / palabras.length) * 100;
        
        if (porcentajeUnicidad < 50 && palabras.length > 10) {
            marcarCampoError('#edit_descripcion', 'La descripción parece muy repetitiva');
            erroresEncontrados.push('Descripción: contenido muy repetitivo');
            isValid = false;
        }
    }
    
    // 16. Validar que el nombre y descripción no sean idénticos
    if (nombre && descripcion && nombre.toLowerCase() === descripcion.toLowerCase()) {
        marcarCampoError('#edit_descripcion', 'La descripción no puede ser igual al nombre');
        erroresEncontrados.push('Descripción: no puede ser igual al nombre');
        isValid = false;
    }
    
    // 17. Validar que el código contenga al menos un separador
    if (codigo && !codigo.includes('-')) {
        marcarCampoError('#edit_codigo_curso', 'El código debe contener al menos un guión separador');
        erroresEncontrados.push('Código: debe incluir guión separador');
        isValid = false;
    }
    
    // 18. Validar longitud mínima del nombre por palabra
    if (nombre) {
        const palabrasNombre = nombre.split(/\s+/);
        if (palabrasNombre.some(p => p.length === 1 && p !== 'A' && p !== 'B' && p !== 'C' && p !== 'D')) {
            marcarCampoError('#edit_nombre', 'Evite palabras de una sola letra (excepto secciones A, B, C, D)');
            erroresEncontrados.push('Nombre: palabras muy cortas');
            isValid = false;
        }
    }
    
    // 19. Validar que el color no sea blanco puro (dificulta lectura)
    if (colorTema && (colorTema.toLowerCase() === '#ffffff' || colorTema.toLowerCase() === '#fff')) {
        marcarCampoError('#edit_color_tema', 'El color blanco no es recomendable para el tema');
        erroresEncontrados.push('Color: blanco no recomendado');
        isValid = false;
    }
    
    // 20. Validar coherencia entre estado y fechas
    if (estado === 'FINALIZADO' && fechaFin) {
        const hoy = new Date();
        const fin = new Date(fechaFin);
        if (fin > hoy) {
            marcarCampoError('#edit_estado', 'No puede marcar como FINALIZADO un curso con fecha futura');
            erroresEncontrados.push('Estado: incoherente con fecha fin');
            isValid = false;
        }
    }
    
    // 21. Validar coherencia entre estado ACTIVO y fecha inicio
    if (estado === 'ACTIVO' && fechaInicio) {
        const hoy = new Date();
        const inicio = new Date(fechaInicio);
        const diasDiferencia = Math.ceil((inicio - hoy) / (1000 * 60 * 60 * 24));
        
        if (diasDiferencia > 30) {
            marcarCampoError('#edit_estado', 'No se recomienda ACTIVO para cursos que inician en más de 30 días');
            erroresEncontrados.push('Estado: incoherente con fecha inicio lejana');
            // Solo warning, no bloquea
        }
    }
    
    // 22. Validar extensión de archivo en URL de imagen
    if (imagenPortada) {
        const extensionesValidas = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        const tieneExtensionValida = extensionesValidas.some(ext => 
            imagenPortada.toLowerCase().endsWith(ext)
        );
        
        if (!tieneExtensionValida) {
            marcarCampoError('#edit_imagen_portada', 'Extensión de imagen no válida (jpg, png, gif, webp)');
            erroresEncontrados.push('Imagen: extensión no permitida');
            isValid = false;
        }
    }
    
    // 23. Validar que la descripción contenga palabras clave relevantes
    if (descripcion && descripcion.length >= 50) {
        const palabrasClave = ['curso', 'estudiante', 'aprendizaje', 'enseñanza', 'materia', 'tema'];
        const contieneAlgunaClave = palabrasClave.some(palabra => 
            descripcion.toLowerCase().includes(palabra)
        );
        
        if (!contieneAlgunaClave) {
            // Solo advertencia, no bloquea
            console.warn('Descripción podría ser más descriptiva incluyendo términos educativos');
        }
    }
    
    // 24. Validar que el código y nombre sean coherentes
    if (codigo && nombre) {
        const codigoSinGuiones = codigo.replace(/-/g, '').toUpperCase();
        const nombreAbreviado = nombre.split(' ').map(p => p[0]).join('').toUpperCase();
        
        // Verificar que al menos una letra del nombre esté en el código
        const hayCoherencia = nombreAbreviado.split('').some(letra => 
            codigoSinGuiones.includes(letra)
        );
        
        if (!hayCoherencia && codigo.length < 15) {
            // Solo advertencia
            console.warn('El código y el nombre del curso podrían no ser coherentes');
        }
    }
    
    // 25. Validar límite de caracteres especiales en descripción
    if (descripcion) {
        const caracteresEspeciales = descripcion.match(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,;:¿?¡!()\-]/g);
        if (caracteresEspeciales && caracteresEspeciales.length > 10) {
            marcarCampoError('#edit_descripcion', 'Demasiados caracteres especiales en la descripción');
            erroresEncontrados.push('Descripción: exceso de caracteres especiales');
            isValid = false;
        }
    }
    
    // Mostrar resumen de errores si los hay
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE ACTUALIZAR EL CURSO\n\nErrores encontrados (${erroresEncontrados.length}):\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto o Inválido',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            width: '600px',
            footer: `Total de errores: ${erroresEncontrados.length}`
        });
        
        // Scroll al primer error
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

function validarFechasEdicion() {
    const inicio = new Date($('#edit_fecha_inicio').val());
    const fin = new Date($('#edit_fecha_fin').val());
    
    $('#edit_fecha_inicio, #edit_fecha_fin').removeClass('campo-error');
    
    if (inicio && fin) {
        if (fin <= inicio) {
            $('#edit_fecha_fin').addClass('campo-error');
            mostrarErrorCampo('#edit_fecha_fin', 'La fecha de fin debe ser posterior a la de inicio');
            return false;
        }
        
        const diffDays = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
            $('#edit_fecha_fin').addClass('campo-error');
            mostrarErrorCampo('#edit_fecha_fin', 'El curso debe durar al menos 1 semana');
            return false;
        }
    }
    
    return true;
}

function validarURLImagen(url) {
    if (!url || url.trim() === '') return true; // Opcional
    
    // Debe empezar con /
    if (!url.startsWith('/')) return false;
    
    // Debe terminar con extensión de imagen válida
    const extensionesValidas = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
    const tieneExtensionValida = extensionesValidas.some(ext => 
        url.toLowerCase().endsWith(ext)
    );
    
    return tieneExtensionValida;
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    
    // Mostrar feedback si existe
    const feedback = campo.siblings('.invalid-feedback');
    if (feedback.length) {
        feedback.text(mensaje).show();
    } else {
        campo.after(`<div class="invalid-feedback d-block">${mensaje}</div>`);
    }
}

function mostrarErrorCampo(selector, mensaje) {
    marcarCampoError(selector, mensaje);
}

function limpiarFormularioEdicion() {
    $('#formEditarCurso')[0].reset();
    $('.campo-error, .is-invalid').removeClass('campo-error is-invalid');
    $('.invalid-feedback').hide();
    $('#edit_desc_count').text('0');
}

function cargarDatosEdicionCurso(curso) {
    $('#edit_curso_id').val(curso.id);
    $('#edit_codigo_curso').val(curso.codigo_curso);
    $('#edit_nombre').val(curso.nombre);
    $('#edit_descripcion').val(curso.descripcion);
    $('#edit_asignacion_id').val(curso.asignacion_id);
    
    // Actualizar contador
    $('#edit_desc_count').text(curso.descripcion.length);
    
    // Configuraciones
    const config = curso.configuraciones || {};
    $('#edit_estado').val(config.estado || 'ACTIVO');
    $('#edit_fecha_inicio').val(config.fecha_inicio || '');
    $('#edit_fecha_fin').val(config.fecha_fin || '');
    $('#edit_color_tema').val(config.color_tema || '#667eea');
    $('#edit_color_hex').val(config.color_tema || '#667eea');
    $('#edit_imagen_portada').val(config.imagen_portada || '');
    $('#edit_inscripcion_libre').prop('checked', config.inscripcion_libre || false);
    
    // Estadísticas
    const stats = curso.estadisticas || {};
    const estudiantes = curso.estudiantes_inscritos || [];
    $('#edit_total_estudiantes').text(Array.isArray(estudiantes) ? estudiantes.length : 0);
    $('#edit_progreso_promedio').text((stats.progreso_promedio || 0) + '%');
    $('#edit_participacion').text((stats.participacion_activa || 0) + '%');
}
</script>