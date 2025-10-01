<!-- Modal Crear Foro -->
<div class="modal fade" id="modalCrearForo" tabindex="-1" aria-labelledby="modalCrearForoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalCrearForoLabel">
                    <i class="ti ti-message-circle-plus me-2"></i>
                    Nuevo Foro de Discusión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formCrearForo" method="POST">
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
                                        <div class="col-12 mb-3">
                                            <label for="crear_curso" class="form-label">
                                                Curso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="crear_curso" name="curso_id" required>
                                                <option value="">Seleccionar curso...</option>
                                                <?php foreach ($cursos as $curso): ?>
                                                    <option value="<?= $curso['id'] ?>" 
                                                            data-docente="<?= htmlspecialchars($curso['docente_nombres'] . ' ' . $curso['docente_apellidos']) ?>">
                                                        <?= htmlspecialchars($curso['nombre_completo']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Curso al que pertenecerá el foro</div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="crear_titulo" class="form-label">
                                                Título del Foro <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="crear_titulo" name="titulo" 
                                                   placeholder="Ej: Dudas sobre Números Enteros" required 
                                                   minlength="5" maxlength="255">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="crear_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="crear_descripcion" name="descripcion" 
                                                      rows="4" placeholder="Describe el propósito y alcance del foro..." 
                                                      required minlength="20" maxlength="1000"></textarea>
                                            <div class="form-text">
                                                <span id="contador_crear">0</span>/1000 caracteres (mínimo 20)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración del Foro
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="crear_tipo" class="form-label">
                                                Tipo de Foro <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="crear_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo...</option>
                                                <option value="GENERAL">General</option>
                                                <option value="PREGUNTA_RESPUESTA">Pregunta y Respuesta</option>
                                                <option value="DEBATE">Debate</option>
                                                <option value="ANUNCIO">Anuncio</option>
                                            </select>
                                            <div class="form-text" id="tipo_help">Seleccione el tipo de foro</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="crear_estado" class="form-label">
                                                Estado Inicial <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="crear_estado" name="estado" required>
                                                <option value="ABIERTO" selected>Abierto</option>
                                                <option value="CERRADO">Cerrado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="crear_moderado" name="moderado">
                                                <label class="form-check-label" for="crear_moderado">
                                                    <i class="ti ti-shield-check me-1"></i>
                                                    Foro Moderado
                                                </label>
                                                <div class="form-text">Los mensajes requieren aprobación antes de publicarse</div>
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
                    <button type="submit" class="btn btn-primary" id="btnCrearForo">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Foro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/libs/jquery/dist/jquery.min.js"></script>

<script>
    // ==================== VALIDACIONES FORMULARIO CREAR FORO ====================
    $(document).ready(function() {
        // Contador de caracteres
        $('#crear_descripcion').on('input', function() {
            $('#contador_crear').text($(this).val().length);
        });

        $('#editar_descripcion').on('input', function() {
            $('#contador_editar').text($(this).val().length);
        });

        $('#mensaje_contenido').on('input', function() {
            $('#contador_mensaje').text($(this).val().length);
        });

        // Ayuda contextual según tipo de foro
        $('#crear_tipo, #editar_tipo').on('change', function() {
            const tipo = $(this).val();
            const helpDiv = $(this).closest('.col-md-6').find('.form-text');
            
            const descripciones = {
                'GENERAL': 'Discusión abierta sobre cualquier tema relacionado',
                'PREGUNTA_RESPUESTA': 'Formato de preguntas con respuestas específicas',
                'DEBATE': 'Espacio para argumentar diferentes puntos de vista',
                'ANUNCIO': 'Solo para publicar comunicados importantes'
            };
            
            helpDiv.text(descripciones[tipo] || 'Seleccione el tipo de foro');
        });

        // Envío formulario crear
        $('#formCrearForo').on('submit', function(e) {
            e.preventDefault();
            
            if (!validarFormularioCrearForo()) {
                return false;
            }

            const formData = new FormData(this);
            formData.append('accion', 'crear');
            
            mostrarCarga();
            $('#btnCrearForo').prop('disabled', true);

            $.ajax({
                url: 'modales/foros/procesar_foros.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    ocultarCarga();
                    $('#btnCrearForo').prop('disabled', false);
                    
                    if (response.success) {
                        Swal.fire({
                            title: '¡Foro Creado!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            $('#modalCrearForo').modal('hide');
                            location.reload();
                        });
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    ocultarCarga();
                    $('#btnCrearForo').prop('disabled', false);
                    mostrarError('Error al procesar la solicitud');
                }
            });
        });

        // Envío formulario editar
        $('#formEditarForo').on('submit', function(e) {
            e.preventDefault();
            
            if (!validarFormularioEditarForo()) {
                return false;
            }

            const formData = new FormData(this);
            formData.append('accion', 'actualizar');
            
            mostrarCarga();
            $('#btnEditarForo').prop('disabled', true);

            $.ajax({
                url: 'modales/foros/procesar_foros.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    ocultarCarga();
                    $('#btnEditarForo').prop('disabled', false);
                    
                    if (response.success) {
                        Swal.fire({
                            title: '¡Foro Actualizado!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            $('#modalEditarForo').modal('hide');
                            location.reload();
                        });
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    ocultarCarga();
                    $('#btnEditarForo').prop('disabled', false);
                    mostrarError('Error al procesar la solicitud');
                }
            });
        });

        // Envío formulario nuevo mensaje
        $('#formNuevoMensaje').on('submit', function(e) {
            e.preventDefault();
            
            if (!validarFormularioMensaje()) {
                return false;
            }

            const formData = new FormData(this);
            formData.append('accion', 'crear_mensaje');
            
            mostrarCarga();
            $('#btnEnviarMensaje').prop('disabled', true);

            $.ajax({
                url: 'modales/foros/procesar_foros.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    ocultarCarga();
                    $('#btnEnviarMensaje').prop('disabled', false);
                    
                    if (response.success) {
                        mostrarExito(response.message);
                        $('#formNuevoMensaje')[0].reset();
                        $('#contador_mensaje').text('0');
                        cancelarRespuesta();
                        // Recargar mensajes
                        const foroId = $('#mensaje_foro_id').val();
                        verMensajes(foroId);
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    ocultarCarga();
                    $('#btnEnviarMensaje').prop('disabled', false);
                    mostrarError('Error al enviar mensaje');
                }
            });
        });

        // Limpiar formularios al cerrar modales
        $('#modalCrearForo').on('hidden.bs.modal', function() {
            limpiarFormularioCrear();
        });

        $('#modalEditarForo').on('hidden.bs.modal', function() {
            limpiarFormularioEditar();
        });
    });

    // ==================== FUNCIÓN DE VALIDACIÓN COMPLETA CREAR FORO ====================
    function validarFormularioCrearForo() {
        let isValid = true;
        let erroresEncontrados = [];
        
        // Limpiar errores previos
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
        
        // 1. Validar curso (obligatorio)
        const curso = $('#crear_curso').val();
        if (!curso) {
            marcarCampoError('#crear_curso', 'Debe seleccionar un curso');
            erroresEncontrados.push('Curso requerido');
            isValid = false;
        }
        
        // 2-5. Validar título (obligatorio, 5-255 caracteres, sin caracteres especiales raros)
        const titulo = $('#crear_titulo').val().trim();
        if (!titulo) {
            marcarCampoError('#crear_titulo', 'El título es obligatorio');
            erroresEncontrados.push('Título requerido');
            isValid = false;
        } else if (titulo.length < 5) {
            marcarCampoError('#crear_titulo', 'El título debe tener al menos 5 caracteres');
            erroresEncontrados.push('Título muy corto (mínimo 5 caracteres)');
            isValid = false;
        } else if (titulo.length > 255) {
            marcarCampoError('#crear_titulo', 'El título no puede superar los 255 caracteres');
            erroresEncontrados.push('Título muy largo (máximo 255 caracteres)');
            isValid = false;
        } else if (/[<>{}[\]\\\/]/.test(titulo)) {
            marcarCampoError('#crear_titulo', 'El título contiene caracteres no permitidos');
            erroresEncontrados.push('Título: caracteres inválidos');
            isValid = false;
        } else if (titulo === titulo.toUpperCase() && titulo.length > 10) {
            marcarCampoError('#crear_titulo', 'No escriba todo en MAYÚSCULAS');
            erroresEncontrados.push('Título: no todo en mayúsculas');
            isValid = false;
        }
        
        // 6-10. Validar descripción (obligatorio, 20-1000 caracteres, contenido significativo)
        const descripcion = $('#crear_descripcion').val().trim();
        if (!descripcion) {
            marcarCampoError('#crear_descripcion', 'La descripción es obligatoria');
            erroresEncontrados.push('Descripción requerida');
            isValid = false;
        } else if (descripcion.length < 20) {
            marcarCampoError('#crear_descripcion', 'La descripción debe tener al menos 20 caracteres');
            erroresEncontrados.push('Descripción muy corta (mínimo 20 caracteres)');
            isValid = false;
        } else if (descripcion.length > 1000) {
            marcarCampoError('#crear_descripcion', 'La descripción no puede superar los 1000 caracteres');
            erroresEncontrados.push('Descripción muy larga (máximo 1000 caracteres)');
            isValid = false;
        } else if (/[<>{}[\]\\\/]/.test(descripcion)) {
            marcarCampoError('#crear_descripcion', 'La descripción contiene caracteres no permitidos');
            erroresEncontrados.push('Descripción: caracteres inválidos');
            isValid = false;
        } else if (descripcion.split(' ').length < 5) {
            marcarCampoError('#crear_descripcion', 'La descripción debe contener al menos 5 palabras');
            erroresEncontrados.push('Descripción: muy breve, añada más detalles');
            isValid = false;
        } else if (descripcion === descripcion.toUpperCase()) {
            marcarCampoError('#crear_descripcion', 'No escriba todo en MAYÚSCULAS');
            erroresEncontrados.push('Descripción: no todo en mayúsculas');
            isValid = false;
        }
        
        // 11. Validar tipo de foro
        const tipo = $('#crear_tipo').val();
        if (!tipo) {
            marcarCampoError('#crear_tipo', 'Debe seleccionar un tipo de foro');
            erroresEncontrados.push('Tipo de foro requerido');
            isValid = false;
        }
        
        // 12. Validar estado
        const estado = $('#crear_estado').val();
        if (!estado) {
            marcarCampoError('#crear_estado', 'Debe seleccionar un estado');
            erroresEncontrados.push('Estado requerido');
            isValid = false;
        }
        
        // 13. Validar coherencia: título no debe ser idéntico a descripción
        if (titulo && descripcion && titulo.toLowerCase() === descripcion.toLowerCase()) {
            marcarCampoError('#crear_descripcion', 'La descripción no puede ser idéntica al título');
            erroresEncontrados.push('Título y descripción no pueden ser iguales');
            isValid = false;
        }
        
        // 14. Validar que el título no tenga solo números
        if (titulo && /^\d+$/.test(titulo)) {
            marcarCampoError('#crear_titulo', 'El título no puede contener solo números');
            erroresEncontrados.push('Título: debe contener texto');
            isValid = false;
        }
        
        // 15. Validar que no haya palabras repetidas excesivamente en título
        if (titulo) {
            const palabras = titulo.toLowerCase().split(' ');
            const repetidas = palabras.filter((p, i, arr) => arr.indexOf(p) !== i && p.length > 3);
            if (repetidas.length > 2) {
                marcarCampoError('#crear_titulo', 'El título tiene demasiadas palabras repetidas');
                erroresEncontrados.push('Título: palabras repetidas');
                isValid = false;
            }
        }
        
        // Mostrar errores si los hay
        if (!isValid) {
            const mensajeError = `❌ NO SE PUEDE CREAR EL FORO\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
            
            Swal.fire({
                title: 'Formulario Incompleto',
                text: mensajeError,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Revisar Formulario',
                customClass: {
                    popup: 'swal-wide'
                },
                footer: `Total de errores: ${erroresEncontrados.length}`
            });
            
            // Hacer scroll al primer campo con error
            const primerError = $('.campo-error, .is-invalid').first();
            if (primerError.length) {
                primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => primerError.focus(), 300);
            }
        }
        
        return isValid;
    }

    // ==================== FUNCIÓN DE VALIDACIÓN COMPLETA EDITAR FORO ====================
    function validarFormularioEditarForo() {
        // Mismas validaciones que crear, excepto curso (está deshabilitado)
        let isValid = true;
        let erroresEncontrados = [];
        
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
        
        // Validaciones idénticas a crear foro pero con prefijo 'editar_'
        const titulo = $('#editar_titulo').val().trim();
        if (!titulo) {
            marcarCampoError('#editar_titulo', 'El título es obligatorio');
            erroresEncontrados.push('Título requerido');
            isValid = false;
        } else if (titulo.length < 5 || titulo.length > 255) {
            marcarCampoError('#editar_titulo', 'El título debe tener entre 5 y 255 caracteres');
            erroresEncontrados.push('Título: longitud incorrecta');
            isValid = false;
        }
        
        const descripcion = $('#editar_descripcion').val().trim();
        if (!descripcion) {
            marcarCampoError('#editar_descripcion', 'La descripción es obligatoria');
            erroresEncontrados.push('Descripción requerida');
            isValid = false;
        } else if (descripcion.length < 20 || descripcion.length > 1000) {
            marcarCampoError('#editar_descripcion', 'La descripción debe tener entre 20 y 1000 caracteres');
            erroresEncontrados.push('Descripción: longitud incorrecta');
            isValid = false;
        }
        
        const tipo = $('#editar_tipo').val();
        if (!tipo) {
            marcarCampoError('#editar_tipo', 'Debe seleccionar un tipo de foro');
            erroresEncontrados.push('Tipo de foro requerido');
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire({
                title: 'Errores en el Formulario',
                text: `Corrija los siguientes errores:\n• ${erroresEncontrados.join('\n• ')}`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
        
        return isValid;
    }

    // ==================== FUNCIÓN DE VALIDACIÓN MENSAJE ====================
    function validarFormularioMensaje() {
        let isValid = true;
        let erroresEncontrados = [];
        
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
        
        const contenido = $('#mensaje_contenido').val().trim();
        
        if (!contenido) {
            marcarCampoError('#mensaje_contenido', 'El contenido del mensaje es obligatorio');
            erroresEncontrados.push('Contenido requerido');
            isValid = false;
        } else if (contenido.length < 10) {
            marcarCampoError('#mensaje_contenido', 'El mensaje debe tener al menos 10 caracteres');
            erroresEncontrados.push('Mensaje muy corto (mínimo 10 caracteres)');
            isValid = false;
        } else if (contenido.length > 2000) {
            marcarCampoError('#mensaje_contenido', 'El mensaje no puede superar los 2000 caracteres');
            erroresEncontrados.push('Mensaje muy largo (máximo 2000 caracteres)');
            isValid = false;
        } else if (/[<>{}[\]\\]/.test(contenido)) {
            marcarCampoError('#mensaje_contenido', 'El mensaje contiene caracteres no permitidos');
            erroresEncontrados.push('Mensaje: caracteres inválidos');
            isValid = false;
        }
        
        if (!isValid) {
            Swal.fire({
                title: 'Error en el Mensaje',
                text: `Corrija lo siguiente:\n• ${erroresEncontrados.join('\n• ')}`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
        
        return isValid;
    }

    // ==================== FUNCIONES AUXILIARES ====================
    function marcarCampoError(selector, mensaje) {
        const campo = $(selector);
        campo.addClass('is-invalid campo-error');
        campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
    }

    function limpiarFormularioCrear() {
        $('#formCrearForo')[0].reset();
        $('#contador_crear').text('0');
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
    }

    function limpiarFormularioEditar() {
        $('#formEditarForo')[0].reset();
        $('#contador_editar').text('0');
        $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
        $('.invalid-feedback').remove();
    }

    // ==================== FUNCIONES PARA EDITAR FORO ====================
    function editarForo(id) {
        mostrarCarga();
        
        fetch('modales/foros/procesar_foros.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=obtener&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            
            if (data.success) {
                cargarDatosEdicionForo(data.foro);
                $('#modalEditarForo').modal('show');
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            ocultarCarga();
            mostrarError('Error al obtener datos del foro');
        });
    }

    function cargarDatosEdicionForo(foro) {
        $('#editar_foro_id').val(foro.id);
        $('#editar_curso').val(foro.curso_id);
        $('#editar_titulo').val(foro.titulo);
        $('#editar_descripcion').val(foro.descripcion);
        $('#contador_editar').text(foro.descripcion.length);
        
        const config = foro.configuraciones || {};
        $('#editar_tipo').val(config.tipo || 'GENERAL');
        $('#editar_estado').val(config.estado || 'ABIERTO');
        $('#editar_moderado').prop('checked', config.moderado || false);
        
        const stats = foro.estadisticas || {};
        $('#stats_mensajes').text(stats.total_mensajes || 0);
        $('#stats_participantes').text(stats.participantes || 0);
        $('#stats_ultimo').text(stats.mensaje_mas_reciente ? 
            new Date(stats.mensaje_mas_reciente).toLocaleString('es-PE') : '-');
    }

    // ==================== FUNCIONES PARA VER MENSAJES ====================
    function verMensajes(id) {
        mostrarCarga();
        
        fetch('modales/foros/procesar_foros.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=obtener_mensajes&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            ocultarCarga();
            
            if (data.success) {
                cargarMensajesForo(data.foro, data.mensajes);
                $('#modalVerMensajes').modal('show');
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            ocultarCarga();
            mostrarError('Error al cargar mensajes');
        });
    }

    function cargarMensajesForo(foro, mensajes) {
        $('#mensaje_foro_id').val(foro.id);
        $('#titulo_foro_mensajes').text(foro.titulo);
        $('#curso_foro_mensajes').text(foro.curso_nombre);
        
        const stats = foro.estadisticas || {};
        $('#total_mensajes_foro').text(mensajes.length);
        $('#total_participantes_foro').text(stats.participantes || 0);
        $('#ultimo_mensaje_foro').text(stats.mensaje_mas_reciente ? 
            new Date(stats.mensaje_mas_reciente).toLocaleString('es-PE') : '-');
        
        renderizarMensajes(mensajes);
    }

    function renderizarMensajes(mensajes) {
        const container = $('#listaMensajes');
        container.empty();
        
        if (mensajes.length === 0) {
            container.html(`
                <div class="alert alert-info text-center">
                    <i class="ti ti-message-off fs-1 mb-2"></i>
                    <p class="mb-0">No hay mensajes en este foro aún. ¡Sé el primero en participar!</p>
                </div>
            `);
            return;
        }
        
        mensajes.forEach(mensaje => {
            const mensajeHtml = crearHtmlMensaje(mensaje);
            container.append(mensajeHtml);
        });
    }

    function crearHtmlMensaje(mensaje) {
        const fecha = new Date(mensaje.fecha_creacion).toLocaleString('es-PE');
        const respuestas = mensaje.respuestas || [];
        
        let html = `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-0">${mensaje.usuario_nombre || 'Usuario'}</h6>
                            <small class="text-muted">${fecha}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="responderMensaje(${mensaje.id}, '${mensaje.usuario_nombre}')">
                            <i class="ti ti-corner-down-right"></i> Responder
                        </button>
                    </div>
                    ${mensaje.titulo ? `<h6 class="text-primary">${mensaje.titulo}</h6>` : ''}
                    <p class="mb-0">${mensaje.contenido}</p>
                </div>
            </div>
        `;
        
        if (respuestas.length > 0) {
            html += '<div class="ms-4">';
            respuestas.forEach(respuesta => {
                html += crearHtmlMensaje(respuesta);
            });
            html += '</div>';
        }
        
        return html;
    }

    function responderMensaje(mensajeId, nombreUsuario) {
        $('#mensaje_padre_id').val(mensajeId);
        $('#respuesta_a').text(nombreUsuario);
        $('#respuesta_info').show();
        $('#mensaje_contenido').focus();
    }

    function cancelarRespuesta() {
        $('#mensaje_padre_id').val('');
        $('#respuesta_info').hide();
    }
</script>

<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.campo-error {
    background-color: #fff5f5;
    border: 2px solid #dc3545 !important;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
    10%, 30%, 50%, 70% { transform: translateX(-5px); }
}

.swal-wide {
    width: 600px !important;
}
</style>