<!-- modales/cursos/modal_agregar.php -->
<div class="modal fade" id="modalAgregarCurso" tabindex="-1" aria-labelledby="modalAgregarCursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarCursoLabel">
                    <i class="ti ti-book-2 me-2"></i>
                    Crear Nuevo Curso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formAgregarCurso" method="POST">
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
                                            <label for="add_codigo_curso" class="form-label">
                                                Código del Curso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_codigo_curso" name="codigo_curso"
                                                   placeholder="MAT-1SA-2025" required maxlength="50"
                                                   pattern="[A-Z0-9\-]{5,50}">
                                            <div class="form-text">Solo mayúsculas, números y guiones (5-50 caracteres)</div>
                                            <div class="invalid-feedback">Código inválido. Debe tener entre 5 y 50 caracteres</div>
                                        </div>

                                        <div class="col-md-8 mb-3">
                                            <label for="add_nombre" class="form-label">
                                                Nombre del Curso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_nombre" name="nombre"
                                                   placeholder="Matemática - 1ro Secundaria A" required
                                                   minlength="5" maxlength="200">
                                            <div class="invalid-feedback">El nombre debe tener entre 5 y 200 caracteres</div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="add_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion"
                                                      rows="3" placeholder="Descripción breve del curso..." required
                                                      minlength="10" maxlength="500"></textarea>
                                            <div class="character-count text-muted">
                                                <small><span id="add_desc_count">0</span>/500 caracteres</small>
                                            </div>
                                            <div class="invalid-feedback">La descripción debe tener entre 10 y 500 caracteres</div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="add_asignacion_id" class="form-label">
                                                Asignación Docente (Área + Sección) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_asignacion_id" name="asignacion_id" required>
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
                                            <div class="form-text">Seleccione la asignación docente que corresponda a este curso</div>
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
                                            <label for="add_estado" class="form-label">
                                                Estado Inicial <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_estado" name="estado" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="BORRADOR" selected>Borrador</option>
                                                <option value="ACTIVO">Activo</option>
                                                <option value="FINALIZADO">Finalizado</option>
                                            </select>
                                            <div class="invalid-feedback">Debe seleccionar un estado</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_fecha_inicio" class="form-label">
                                                Fecha Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_inicio"
                                                   name="fecha_inicio" required>
                                            <div class="invalid-feedback">Fecha de inicio requerida</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_fecha_fin" class="form-label">
                                                Fecha Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_fin"
                                                   name="fecha_fin" required>
                                            <div class="invalid-feedback">Fecha de fin requerida y debe ser posterior al inicio</div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="add_color_tema" class="form-label">Color del Tema</label>
                                            <div class="d-flex gap-2">
                                                <input type="color" class="form-control form-control-color"
                                                       id="add_color_tema" name="color_tema" value="#667eea">
                                                <input type="text" class="form-control" id="add_color_hex"
                                                       maxlength="7" pattern="#[0-9A-Fa-f]{6}"
                                                       placeholder="#667eea" value="#667eea">
                                            </div>
                                            <div class="form-text">Formato: #RRGGBB</div>
                                        </div>

                                        <!-- <div class="col-md-6 mb-3">
                                            <label for="add_imagen_portada" class="form-label">URL Imagen de Portada</label>
                                            <input type="text" class="form-control" id="add_imagen_portada"
                                                   name="imagen_portada" placeholder="/img/curso_portada.jpg"
                                                   maxlength="255" pattern="^\/[\w\-\/\.]+\.(jpg|jpeg|png|gif|webp)$">
                                            <div class="form-text">Ruta de imagen (jpg, png, gif, webp) - Opcional</div>
                                            <div class="invalid-feedback">Formato de URL inválido</div>
                                        </div> -->

                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="add_inscripcion_libre"
                                                       name="inscripcion_libre" value="1">
                                                <label class="form-check-label" for="add_inscripcion_libre">
                                                    <i class="ti ti-users me-1"></i>
                                                    Permitir inscripción libre de estudiantes
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview del Curso -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-eye me-2"></i>
                                        Vista Previa
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="preview_curso" class="text-center text-muted">
                                        <i class="ti ti-file-description fs-1"></i>
                                        <p class="mt-2">Complete los campos para ver una vista previa</p>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarCurso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Curso
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

#preview_curso.active {
    padding: 1.5rem;
    border: 2px solid #667eea;
    border-radius: 0.5rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Contador de caracteres para descripción
    $('#add_descripcion').on('input', function() {
        const count = $(this).val().length;
        $('#add_desc_count').text(count);

        if (count > 500) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }

        actualizarPreview();
    });

    // Sincronizar selector de color con input de texto
    $('#add_color_tema').on('input', function() {
        $('#add_color_hex').val($(this).val());
        actualizarPreview();
    });

    $('#add_color_hex').on('input', function() {
        const hex = $(this).val();
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            $('#add_color_tema').val(hex);
            $(this).removeClass('campo-error');
            actualizarPreview();
        } else {
            $(this).addClass('campo-error');
        }
    });

    // Validación de fechas en tiempo real
    $('#add_fecha_inicio, #add_fecha_fin').on('change', function() {
        validarFechasAgregar();
        actualizarPreview();
    });

    // Validación de código en tiempo real
    $('#add_codigo_curso').on('input', function() {
        let valor = $(this).val().toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        $(this).val(valor);

        if (valor.length >= 5 && valor.length <= 50) {
            $(this).removeClass('campo-error');
        } else if (valor.length > 0) {
            $(this).addClass('campo-error');
        }

        actualizarPreview();
    });

    // Validación de nombre
    $('#add_nombre').on('input', function() {
        actualizarPreview();
    });

    // Validación de URL de imagen
    $('#add_imagen_portada').on('blur', function() {
        const url = $(this).val().trim();
        if (url && !validarURLImagen(url)) {
            $(this).addClass('campo-error');
            mostrarErrorCampo(this, 'La URL debe ser una ruta válida de imagen');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Establecer fecha mínima como hoy
    const hoy = new Date().toISOString().split('T')[0];
    $('#add_fecha_inicio').attr('min', hoy);

    // Envío del formulario con 25+ validaciones
    $('#formAgregarCurso').on('submit', function(e) {
        e.preventDefault();

        if (!validarFormularioAgregarCompleto()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');

        // Preparar configuraciones JSON
        const configuraciones = {
            estado: $('#add_estado').val(),
            fecha_inicio: $('#add_fecha_inicio').val(),
            fecha_fin: $('#add_fecha_fin').val(),
            color_tema: $('#add_color_tema').val(),
            imagen_portada: $('#add_imagen_portada').val(),
            inscripcion_libre: $('#add_inscripcion_libre').is(':checked')
        };

        formData.append('configuraciones', JSON.stringify(configuraciones));

        mostrarCarga();
        $('#btnGuardarCurso').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Guardando...');

        fetch('modales/cursos/procesar_cursos.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            $('#btnGuardarCurso').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i>Crear Curso');

            if (data.success) {
                Swal.fire({
                    title: '¡Curso Creado!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#modalAgregarCurso').modal('hide');
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
            $('#btnGuardarCurso').prop('disabled', false).html('<i class="ti ti-device-floppy me-2"></i>Crear Curso');
            mostrarError('Error al procesar la solicitud: ' + error);
        });
    });

    // Limpiar al cerrar
    $('#modalAgregarCurso').on('hidden.bs.modal', function() {
        limpiarFormularioAgregar();
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA CON 28 VALIDACIONES
function validarFormularioAgregarCompleto() {
    let isValid = true;
    let erroresEncontrados = [];

    // Limpiar errores previos
    $('.campo-error, .is-invalid').removeClass('campo-error is-invalid');
    $('.invalid-feedback').hide();

    // 1. Validar código del curso (obligatorio, formato)
    const codigo = $('#add_codigo_curso').val().trim();
    if (!codigo) {
        marcarCampoError('#add_codigo_curso', 'El código del curso es obligatorio');
        erroresEncontrados.push('Código del curso requerido');
        isValid = false;
    } else if (codigo.length < 5 || codigo.length > 50) {
        marcarCampoError('#add_codigo_curso', 'El código debe tener entre 5 y 50 caracteres');
        erroresEncontrados.push('Código: longitud incorrecta (5-50)');
        isValid = false;
    } else if (!/^[A-Z0-9\-]+$/.test(codigo)) {
        marcarCampoError('#add_codigo_curso', 'Solo mayúsculas, números y guiones');
        erroresEncontrados.push('Código: formato inválido');
        isValid = false;
    } else if (!codigo.includes('-')) {
        marcarCampoError('#add_codigo_curso', 'Debe contener al menos un guión separador');
        erroresEncontrados.push('Código: falta guión separador');
        isValid = false;
    } else if (/[<>'"&]/.test(codigo)) {
        marcarCampoError('#add_codigo_curso', 'Contiene caracteres prohibidos');
        erroresEncontrados.push('Código: caracteres prohibidos');
        isValid = false;
    }

    // 2-3. Validar nombre del curso
    const nombre = $('#add_nombre').val().trim();
    if (!nombre) {
        marcarCampoError('#add_nombre', 'El nombre del curso es obligatorio');
        erroresEncontrados.push('Nombre requerido');
        isValid = false;
    } else if (nombre.length < 5 || nombre.length > 200) {
        marcarCampoError('#add_nombre', 'El nombre debe tener entre 5 y 200 caracteres');
        erroresEncontrados.push('Nombre: longitud incorrecta (5-200)');
        isValid = false;
    } else if (/^\d+$/.test(nombre)) {
        marcarCampoError('#add_nombre', 'El nombre no puede ser solo números');
        erroresEncontrados.push('Nombre: no puede ser numérico');
        isValid = false;
    } else if (nombre.split(/\s+/).some(p => p.length === 1 && !/^[ABCD]$/i.test(p))) {
        marcarCampoError('#add_nombre', 'Evite palabras de una letra (excepto A,B,C,D)');
        erroresEncontrados.push('Nombre: palabras muy cortas');
        isValid = false;
    }

    // 4-6. Validar descripción
    const descripcion = $('#add_descripcion').val().trim();
    if (!descripcion) {
        marcarCampoError('#add_descripcion', 'La descripción es obligatoria');
        erroresEncontrados.push('Descripción requerida');
        isValid = false;
    } else if (descripcion.length < 10 || descripcion.length > 500) {
        marcarCampoError('#add_descripcion', 'Debe tener entre 10 y 500 caracteres');
        erroresEncontrados.push('Descripción: longitud incorrecta (10-500)');
        isValid = false;
    } else if (nombre && descripcion.toLowerCase() === nombre.toLowerCase()) {
        marcarCampoError('#add_descripcion', 'No puede ser igual al nombre');
        erroresEncontrados.push('Descripción: igual al nombre');
        isValid = false;
    } else {
        // Validar repetitividad
        const palabras = descripcion.toLowerCase().split(/\s+/);
        const palabrasUnicas = new Set(palabras);
        const porcentajeUnicidad = (palabrasUnicas.size / palabras.length) * 100;

        if (porcentajeUnicidad < 50 && palabras.length > 10) {
            marcarCampoError('#add_descripcion', 'La descripción es muy repetitiva');
            erroresEncontrados.push('Descripción: muy repetitiva');
            isValid = false;
        }

        // Validar caracteres especiales
        const caracteresEspeciales = descripcion.match(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,;:¿?¡!()\-]/g);
        if (caracteresEspeciales && caracteresEspeciales.length > 10) {
            marcarCampoError('#add_descripcion', 'Demasiados caracteres especiales');
            erroresEncontrados.push('Descripción: exceso de caracteres especiales');
            isValid = false;
        }
    }

    // 7. Validar asignación docente
    const asignacionId = $('#add_asignacion_id').val();
    if (!asignacionId) {
        marcarCampoError('#add_asignacion_id', 'Debe seleccionar una asignación');
        erroresEncontrados.push('Asignación requerida');
        isValid = false;
    }

    // 8. Validar estado
    const estado = $('#add_estado').val();
    if (!estado) {
        marcarCampoError('#add_estado', 'Debe seleccionar un estado');
        erroresEncontrados.push('Estado requerido');
        isValid = false;
    } else if (!['ACTIVO', 'BORRADOR', 'FINALIZADO'].includes(estado)) {
        marcarCampoError('#add_estado', 'Estado no válido');
        erroresEncontrados.push('Estado: valor inválido');
        isValid = false;
    }

    // 9-15. Validar fechas exhaustivamente
    const fechaInicio = $('#add_fecha_inicio').val();
    const fechaFin = $('#add_fecha_fin').val();

    if (!fechaInicio) {
        marcarCampoError('#add_fecha_inicio', 'Fecha de inicio obligatoria');
        erroresEncontrados.push('Fecha inicio requerida');
        isValid = false;
    }

    if (!fechaFin) {
        marcarCampoError('#add_fecha_fin', 'Fecha de fin obligatoria');
        erroresEncontrados.push('Fecha fin requerida');
        isValid = false;
    }

    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        // Validar formato
        if (isNaN(inicio.getTime())) {
            marcarCampoError('#add_fecha_inicio', 'Formato de fecha inválido');
            erroresEncontrados.push('Fecha inicio: formato inválido');
            isValid = false;
        }

        if (isNaN(fin.getTime())) {
            marcarCampoError('#add_fecha_fin', 'Formato de fecha inválido');
            erroresEncontrados.push('Fecha fin: formato inválido');
            isValid = false;
        }

        // Validar que fin sea posterior a inicio
        if (fin <= inicio) {
            marcarCampoError('#add_fecha_fin', 'Debe ser posterior a la fecha de inicio');
            erroresEncontrados.push('Fechas: fin debe ser posterior');
            isValid = false;
        }

        // Validar duración mínima (7 días)
        const diffTime = Math.abs(fin - inicio);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
            marcarCampoError('#add_fecha_fin', 'El curso debe durar al menos 1 semana');
            erroresEncontrados.push('Duración: mínimo 7 días');
            isValid = false;
        }

        // Validar duración máxima (365 días)
        if (diffDays > 365) {
            marcarCampoError('#add_fecha_fin', 'El curso no puede durar más de 1 año');
            erroresEncontrados.push('Duración: máximo 365 días');
            isValid = false;
        }

        // Validar coherencia con estado ACTIVO
        if (estado === 'ACTIVO') {
            const diasHastaInicio = Math.ceil((inicio - hoy) / (1000 * 60 * 60 * 24));
            if (diasHastaInicio > 30) {
                marcarCampoError('#add_estado', 'No recomendado ACTIVO con inicio lejano (>30 días)');
                erroresEncontrados.push('Estado: incoherente con fecha inicio');
                // Solo warning
            }
        }

        // Validar coherencia con estado FINALIZADO
        if (estado === 'FINALIZADO' && fin > hoy) {
            marcarCampoError('#add_estado', 'No puede estar FINALIZADO con fecha futura');
            erroresEncontrados.push('Estado: incoherente con fecha fin');
            isValid = false;
        }
    }

    // 16-17. Validar color del tema
    const colorTema = $('#add_color_tema').val();
    if (colorTema && !/^#[0-9A-Fa-f]{6}$/.test(colorTema)) {
        marcarCampoError('#add_color_tema', 'Formato hexadecimal inválido (#RRGGBB)');
        erroresEncontrados.push('Color: formato inválido');
        isValid = false;
    } else if (colorTema && (colorTema.toLowerCase() === '#ffffff' || colorTema.toLowerCase() === '#fff')) {
        marcarCampoError('#add_color_tema', 'Blanco no recomendado para temas');
        erroresEncontrados.push('Color: blanco no recomendado');
        isValid = false;
    }

    // 18-20. Validar URL de imagen
    const imagenPortada = $('#add_imagen_portada').val().trim();
    if (imagenPortada) {
        if (!validarURLImagen(imagenPortada)) {
            marcarCampoError('#add_imagen_portada', 'URL de imagen inválida');
            erroresEncontrados.push('Imagen: URL inválida');
            isValid = false;
        }

        if (imagenPortada.length > 255) {
            marcarCampoError('#add_imagen_portada', 'URL muy larga (máx. 255)');
            erroresEncontrados.push('Imagen: URL muy larga');
            isValid = false;
        }

        // Validar extensión
        const extensionesValidas = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        const tieneExtensionValida = extensionesValidas.some(ext =>
            imagenPortada.toLowerCase().endsWith(ext)
        );

        if (!tieneExtensionValida) {
            marcarCampoError('#add_imagen_portada', 'Extensión no válida (jpg,png,gif,webp)');
            erroresEncontrados.push('Imagen: extensión inválida');
            isValid = false;
        }
    }

    // 21. Validar que código contenga referencia al nombre
    if (codigo && nombre) {
        const codigoLimpio = codigo.replace(/-/g, '').toUpperCase();
        const nombreAbrev = nombre.split(' ').map(p => p[0]).join('').toUpperCase();

        const hayCoherencia = nombreAbrev.split('').some(letra =>
            codigoLimpio.includes(letra)
        );

        if (!hayCoherencia && codigo.length < 15) {
            console.warn('Código y nombre podrían no ser coherentes');
        }
    }

    // 22. Validar que no haya duplicados en el nombre
    if (nombre) {
        const palabrasNombre = nombre.toLowerCase().split(/\s+/);
        const palabrasUnicas = new Set(palabrasNombre);
        if (palabrasUnicas.size < palabrasNombre.length - 2) {
            marcarCampoError('#add_nombre', 'Nombre con demasiadas repeticiones');
            erroresEncontrados.push('Nombre: muy repetitivo');
            isValid = false;
        }
    }

    // 23. Validar longitud razonable del código (no muy largo)
    if (codigo && codigo.length > 30) {
        marcarCampoError('#add_codigo_curso', 'Código muy largo, considere abreviarlo');
        erroresEncontrados.push('Código: demasiado largo');
        isValid = false;
    }

    // 24. Validar que el nombre contenga información del grado/nivel
    if (nombre && !/\d|primero|segundo|tercero|cuarto|quinto|sexto|inicial|primaria|secundaria/i.test(nombre)) {
        console.warn('El nombre podría incluir información del grado/nivel');
    }

    // 25. Validar que la descripción no sea solo el nombre extendido
    if (nombre && descripcion) {
        const similitud = calcularSimilitud(nombre.toLowerCase(), descripcion.toLowerCase());
        if (similitud > 80) {
            marcarCampoError('#add_descripcion', 'Descripción muy similar al nombre');
            erroresEncontrados.push('Descripción: muy similar al nombre');
            isValid = false;
        }
    }

    // 26. Validar límite de guiones en código
    if (codigo) {
        const guiones = (codigo.match(/-/g) || []).length;
        if (guiones > 5) {
            marcarCampoError('#add_codigo_curso', 'Demasiados guiones en el código');
            erroresEncontrados.push('Código: exceso de guiones');
            isValid = false;
        }
    }

    // 27. Validar que el año esté en el código si corresponde
    const anioActual = new Date().getFullYear();
    if (codigo && !codigo.includes(anioActual.toString())) {
        console.warn('Considere incluir el año en el código del curso');
    }

    // 28. Validar coherencia total del formulario
    if (isValid) {
        // Verificación final de coherencia
        if (!codigo || !nombre || !descripcion || !asignacionId || !estado || !fechaInicio || !fechaFin) {
            erroresEncontrados.push('Faltan campos obligatorios por completar');
            isValid = false;
        }
    }

    // Mostrar resumen si hay errores
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL CURSO\n\nErrores encontrados (${erroresEncontrados.length}):\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;

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

function validarFechasAgregar() {
    const inicio = new Date($('#add_fecha_inicio').val());
    const fin = new Date($('#add_fecha_fin').val());

    $('#add_fecha_inicio, #add_fecha_fin').removeClass('campo-error');

    if (inicio && fin) {
        if (fin <= inicio) {
            $('#add_fecha_fin').addClass('campo-error');
            mostrarErrorCampo('#add_fecha_fin', 'Debe ser posterior a la fecha de inicio');
            return false;
        }

        const diffDays = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        if (diffDays < 7) {
            $('#add_fecha_fin').addClass('campo-error');
            mostrarErrorCampo('#add_fecha_fin', 'El curso debe durar al menos 1 semana');
            return false;
        }
    }

    return true;
}

function validarURLImagen(url) {
    if (!url || url.trim() === '') return true;
    if (!url.startsWith('/')) return false;

    const extensionesValidas = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
    return extensionesValidas.some(ext => url.toLowerCase().endsWith(ext));
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');

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

function calcularSimilitud(str1, str2) {
    const longer = str1.length > str2.length ? str1 : str2;
    const shorter = str1.length > str2.length ? str2 : str1;

    if (longer.length === 0) return 100.0;

    const editDistance = levenshteinDistance(longer, shorter);
    return ((longer.length - editDistance) / longer.length) * 100;
}

function levenshteinDistance(str1, str2) {
    const matrix = [];

    for (let i = 0; i <= str2.length; i++) {
        matrix[i] = [i];
    }

    for (let j = 0; j <= str1.length; j++) {
        matrix[0][j] = j;
    }

    for (let i = 1; i <= str2.length; i++) {
        for (let j = 1; j <= str1.length; j++) {
            if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }

    return matrix[str2.length][str1.length];
}

function actualizarPreview() {
    const codigo = $('#add_codigo_curso').val();
    const nombre = $('#add_nombre').val();
    const descripcion = $('#add_descripcion').val();
    const color = $('#add_color_tema').val();

    if (nombre && descripcion) {
        const preview = `
            <div class="text-start">
                <div class="p-3 rounded" style="background: ${color}; color: white;">
                    <small>${codigo || 'SIN-CODIGO'}</small>
                    <h5 class="mt-1 mb-0">${nombre}</h5>
                </div>
                <div class="mt-3">
                    <p class="text-muted">${descripcion}</p>
                </div>
            </div>
        `;
        $('#preview_curso').html(preview).addClass('active');
    } else {
        $('#preview_curso').html(`
            <i class="ti ti-file-description fs-1"></i>
            <p class="mt-2">Complete los campos para ver una vista previa</p>
        `).removeClass('active');
    }
}

function limpiarFormularioAgregar() {
    $('#formAgregarCurso')[0].reset();
    $('.campo-error, .is-invalid').removeClass('campo-error is-invalid');
    $('.invalid-feedback').hide();
    $('#add_desc_count').text('0');
    $('#add_color_hex').val('#667eea');
    $('#preview_curso').html(`
        <i class="ti ti-file-description fs-1"></i>
        <p class="mt-2">Complete los campos para ver una vista previa</p>
    `).removeClass('active');
}
</script>