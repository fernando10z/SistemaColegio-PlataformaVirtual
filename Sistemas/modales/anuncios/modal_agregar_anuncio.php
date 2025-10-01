<!-- Modal Agregar Anuncio -->
<div class="modal fade" id="modalAgregarAnuncio" tabindex="-1" aria-labelledby="modalAgregarAnuncioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarAnuncioLabel">
                    <i class="ti ti-speakerphone me-2"></i>
                    Nuevo Anuncio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarAnuncio" method="POST">
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
                                            <label for="add_curso_id" class="form-label">
                                                Curso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_curso_id" name="curso_id" required>
                                                <option value="">Seleccionar curso</option>
                                                <?php foreach ($cursos_activos as $curso): ?>
                                                    <option value="<?= $curso['id'] ?>">
                                                        <?= htmlspecialchars($curso['nombre']) ?> 
                                                        (<?= $curso['grado'] ?> - <?= $curso['seccion'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Curso donde se publicará el anuncio</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="add_titulo" class="form-label">
                                                Título del Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_titulo" name="titulo" 
                                                   placeholder="Título descriptivo del anuncio" required 
                                                   maxlength="255" minlength="5">
                                            <div class="form-text">Entre 5 y 255 caracteres</div>
                                            <div id="contador_titulo" class="form-text text-end">0/255</div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="add_contenido" class="form-label">
                                                Contenido del Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="add_contenido" name="contenido" 
                                                      rows="6" placeholder="Escribe aquí el contenido del anuncio..." 
                                                      required minlength="10" maxlength="5000"></textarea>
                                            <div class="form-text">Entre 10 y 5000 caracteres. Sea claro y específico.</div>
                                            <div id="contador_contenido" class="form-text text-end">0/5000</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración del Anuncio -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración del Anuncio
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="add_tipo" class="form-label">
                                                Tipo de Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="INFORMATIVO">Informativo</option>
                                                <option value="RECORDATORIO">Recordatorio</option>
                                                <option value="URGENTE">Urgente</option>
                                                <option value="EVENTO">Evento</option>
                                            </select>
                                            <div class="form-text">Categoría del anuncio</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="add_prioridad" class="form-label">
                                                Prioridad <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_prioridad" name="prioridad" required>
                                                <option value="">Seleccionar prioridad</option>
                                                <option value="BAJA">Baja</option>
                                                <option value="NORMAL" selected>Normal</option>
                                                <option value="ALTA">Alta</option>
                                            </select>
                                            <div class="form-text">Nivel de importancia</div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="add_destinatario" class="form-label">
                                                Destinatario <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_destinatario" name="destinatario" required>
                                                <option value="">Seleccionar destinatario</option>
                                                <option value="ESTUDIANTES" selected>Estudiantes</option>
                                                <option value="APODERADOS">Apoderados</option>
                                                <option value="TODOS">Todos</option>
                                            </select>
                                            <div class="form-text">Quién recibirá el anuncio</div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="add_fecha_publicacion" class="form-label">
                                                Fecha de Publicación <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" class="form-control" 
                                                   id="add_fecha_publicacion" name="fecha_publicacion" required>
                                            <div class="form-text">Cuándo se publicará el anuncio</div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="add_fecha_expiracion" class="form-label">
                                                Fecha de Expiración (Opcional)
                                            </label>
                                            <input type="datetime-local" class="form-control" 
                                                   id="add_fecha_expiracion" name="fecha_expiracion">
                                            <div class="form-text">Cuándo dejará de mostrarse (opcional)</div>
                                        </div>

                                        <div class="col-12">
                                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                                <i class="ti ti-info-circle me-2 fs-4"></i>
                                                <div>
                                                    <strong>Importante:</strong> La fecha de publicación no puede ser anterior a hoy, 
                                                    y la fecha de expiración debe ser posterior a la fecha de publicación.
                                                </div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarAnuncio">
                        <i class="ti ti-device-floppy me-2"></i>
                        Publicar Anuncio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
$(document).ready(function() {
    // ==================== CONFIGURACIÓN INICIAL ====================
    
    // Configurar fechas mínimas
    const ahora = new Date();
    const fechaMinima = ahora.toISOString().slice(0, 16);
    $('#add_fecha_publicacion, #edit_fecha_publicacion').attr('min', fechaMinima);
    $('#add_fecha_publicacion').val(fechaMinima);

    // ==================== CONTADORES DE CARACTERES ====================
    
    $('#add_titulo').on('input', function() {
        const length = $(this).val().length;
        $('#contador_titulo').text(`${length}/255`);
        if (length > 255) {
            $(this).addClass('campo-error');
            $('#contador_titulo').addClass('text-danger');
        } else {
            $(this).removeClass('campo-error');
            $('#contador_titulo').removeClass('text-danger');
        }
    });

    $('#add_contenido').on('input', function() {
        const length = $(this).val().length;
        $('#contador_contenido').text(`${length}/5000`);
        if (length > 5000) {
            $(this).addClass('campo-error');
            $('#contador_contenido').addClass('text-danger');
        } else {
            $(this).removeClass('campo-error');
            $('#contador_contenido').removeClass('text-danger');
        }
    });

    $('#edit_titulo').on('input', function() {
        const length = $(this).val().length;
        $('#contador_titulo_edit').text(`${length}/255`);
    });

    $('#edit_contenido').on('input', function() {
        const length = $(this).val().length;
        $('#contador_contenido_edit').text(`${length}/5000`);
    });

    // ==================== VALIDACIÓN DE FECHAS ====================
    
    $('#add_fecha_publicacion, #edit_fecha_publicacion').on('change', function() {
        validarFechas($(this).attr('id').includes('add') ? 'add' : 'edit');
    });

    $('#add_fecha_expiracion, #edit_fecha_expiracion').on('change', function() {
        validarFechas($(this).attr('id').includes('add') ? 'add' : 'edit');
    });

    // ==================== FORM AGREGAR ANUNCIO ====================
    
    $('#formAgregarAnuncio').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioCompletoAgregar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarAnuncio').prop('disabled', true);

        $.ajax({
            url: 'modales/anuncios/procesar_anuncios.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarAnuncio').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Anuncio Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarAnuncio').modal('hide');
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
                $('#btnGuardarAnuncio').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // ==================== FORM EDITAR ANUNCIO ====================
    
    $('#formEditarAnuncio').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioCompletoEditar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarAnuncio').prop('disabled', true);

        $.ajax({
            url: 'modales/anuncios/procesar_anuncios.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarAnuncio').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Anuncio Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarAnuncio').modal('hide');
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
                $('#btnActualizarAnuncio').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // ==================== LIMPIAR AL CERRAR ====================
    
    $('#modalAgregarAnuncio').on('hidden.bs.modal', function() {
        limpiarFormulario('add');
    });

    $('#modalEditarAnuncio').on('hidden.bs.modal', function() {
        limpiarFormulario('edit');
    });
});

// ==================== FUNCIÓN DE VALIDACIÓN COMPLETA (AGREGAR) ====================

function validarFormularioCompletoAgregar() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar curso (obligatorio)
    const curso = $('#add_curso_id').val();
    if (!curso) {
        marcarCampoError('#add_curso_id', 'Debe seleccionar un curso');
        erroresEncontrados.push('Curso requerido');
        isValid = false;
    }
    
    // 2. Validar título (obligatorio, 5-255 caracteres)
    const titulo = $('#add_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#add_titulo', 'El título es obligatorio');
        erroresEncontrados.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5) {
        marcarCampoError('#add_titulo', 'El título debe tener al menos 5 caracteres');
        erroresEncontrados.push('Título muy corto (mínimo 5 caracteres)');
        isValid = false;
    } else if (titulo.length > 255) {
        marcarCampoError('#add_titulo', 'El título no puede superar los 255 caracteres');
        erroresEncontrados.push('Título muy largo (máximo 255 caracteres)');
        isValid = false;
    }
    
    // 3. Validar que el título no sea solo espacios
    if (titulo && titulo.replace(/\s/g, '').length === 0) {
        marcarCampoError('#add_titulo', 'El título no puede contener solo espacios');
        erroresEncontrados.push('Título: no puede ser solo espacios');
        isValid = false;
    }
    
    // 4. Validar que el título no tenga caracteres especiales excesivos
    if (titulo && /[<>{}[\]\\\/]/g.test(titulo)) {
        marcarCampoError('#add_titulo', 'El título contiene caracteres no permitidos');
        erroresEncontrados.push('Título: caracteres especiales no permitidos');
        isValid = false;
    }
    
    // 5. Validar contenido (obligatorio, 10-5000 caracteres)
    const contenido = $('#add_contenido').val().trim();
    if (!contenido) {
        marcarCampoError('#add_contenido', 'El contenido es obligatorio');
        erroresEncontrados.push('Contenido requerido');
        isValid = false;
    } else if (contenido.length < 10) {
        marcarCampoError('#add_contenido', 'El contenido debe tener al menos 10 caracteres');
        erroresEncontrados.push('Contenido muy corto (mínimo 10 caracteres)');
        isValid = false;
    } else if (contenido.length > 5000) {
        marcarCampoError('#add_contenido', 'El contenido no puede superar los 5000 caracteres');
        erroresEncontrados.push('Contenido muy largo (máximo 5000 caracteres)');
        isValid = false;
    }
    
    // 6. Validar que el contenido no sea solo espacios
    if (contenido && contenido.replace(/\s/g, '').length === 0) {
        marcarCampoError('#add_contenido', 'El contenido no puede contener solo espacios');
        erroresEncontrados.push('Contenido: no puede ser solo espacios');
        isValid = false;
    }
    
    // 7. Validar tipo (obligatorio)
    const tipo = $('#add_tipo').val();
    if (!tipo) {
        marcarCampoError('#add_tipo', 'Debe seleccionar un tipo de anuncio');
        erroresEncontrados.push('Tipo de anuncio requerido');
        isValid = false;
    }
    
    // 8. Validar que el tipo sea válido
    const tiposValidos = ['INFORMATIVO', 'RECORDATORIO', 'URGENTE', 'EVENTO'];
    if (tipo && !tiposValidos.includes(tipo)) {
        marcarCampoError('#add_tipo', 'Tipo de anuncio no válido');
        erroresEncontrados.push('Tipo de anuncio inválido');
        isValid = false;
    }
    
    // 9. Validar prioridad (obligatorio)
    const prioridad = $('#add_prioridad').val();
    if (!prioridad) {
        marcarCampoError('#add_prioridad', 'Debe seleccionar una prioridad');
        erroresEncontrados.push('Prioridad requerida');
        isValid = false;
    }
    
    // 10. Validar que la prioridad sea válida
    const prioridadesValidas = ['BAJA', 'NORMAL', 'ALTA'];
    if (prioridad && !prioridadesValidas.includes(prioridad)) {
        marcarCampoError('#add_prioridad', 'Prioridad no válida');
        erroresEncontrados.push('Prioridad inválida');
        isValid = false;
    }
    
    // 11. Validar destinatario (obligatorio)
    const destinatario = $('#add_destinatario').val();
    if (!destinatario) {
        marcarCampoError('#add_destinatario', 'Debe seleccionar un destinatario');
        erroresEncontrados.push('Destinatario requerido');
        isValid = false;
    }
    
    // 12. Validar que el destinatario sea válido
    const destinatariosValidos = ['ESTUDIANTES', 'APODERADOS', 'TODOS'];
    if (destinatario && !destinatariosValidos.includes(destinatario)) {
        marcarCampoError('#add_destinatario', 'Destinatario no válido');
        erroresEncontrados.push('Destinatario inválido');
        isValid = false;
    }
    
    // 13. Validar fecha de publicación (obligatorio)
    const fechaPub = $('#add_fecha_publicacion').val();
    if (!fechaPub) {
        marcarCampoError('#add_fecha_publicacion', 'La fecha de publicación es obligatoria');
        erroresEncontrados.push('Fecha de publicación requerida');
        isValid = false;
    }
    
    // 14. Validar que la fecha de publicación no sea pasada
    if (fechaPub) {
        const fechaPubDate = new Date(fechaPub);
        const ahora = new Date();
        ahora.setMinutes(ahora.getMinutes() - 5); // 5 minutos de tolerancia
        
        if (fechaPubDate < ahora) {
            marcarCampoError('#add_fecha_publicacion', 'La fecha de publicación no puede ser pasada');
            erroresEncontrados.push('Fecha de publicación: no puede ser pasada');
            isValid = false;
        }
    }
    
    // 15. Validar que la fecha de publicación no sea muy futura (más de 1 año)
    if (fechaPub) {
        const fechaPubDate = new Date(fechaPub);
        const unAnio = new Date();
        unAnio.setFullYear(unAnio.getFullYear() + 1);
        
        if (fechaPubDate > unAnio) {
            marcarCampoError('#add_fecha_publicacion', 'La fecha de publicación no puede ser mayor a 1 año');
            erroresEncontrados.push('Fecha de publicación: máximo 1 año adelante');
            isValid = false;
        }
    }
    
    // 16. Validar fecha de expiración (si está presente)
    const fechaExp = $('#add_fecha_expiracion').val();
    if (fechaExp) {
        const fechaExpDate = new Date(fechaExp);
        const fechaPubDate = new Date(fechaPub);
        
        // 17. Validar que la fecha de expiración sea posterior a la publicación
        if (fechaExpDate <= fechaPubDate) {
            marcarCampoError('#add_fecha_expiracion', 'La fecha de expiración debe ser posterior a la publicación');
            erroresEncontrados.push('Fecha de expiración: debe ser posterior a publicación');
            isValid = false;
        }
        
        // 18. Validar que la diferencia sea de al menos 1 hora
        const diferenciaHoras = (fechaExpDate - fechaPubDate) / (1000 * 60 * 60);
        if (diferenciaHoras < 1) {
            marcarCampoError('#add_fecha_expiracion', 'La expiración debe ser al menos 1 hora después de la publicación');
            erroresEncontrados.push('Fecha de expiración: mínimo 1 hora después');
            isValid = false;
        }
        
        // 19. Validar que la expiración no sea muy lejana (más de 2 años)
        const dosAnios = new Date(fechaPubDate);
        dosAnios.setFullYear(dosAnios.getFullYear() + 2);
        if (fechaExpDate > dosAnios) {
            marcarCampoError('#add_fecha_expiracion', 'La expiración no puede ser mayor a 2 años desde la publicación');
            erroresEncontrados.push('Fecha de expiración: máximo 2 años');
            isValid = false;
        }
    }
    
    // 20. Validar coherencia tipo-prioridad
    if (tipo === 'URGENTE' && prioridad !== 'ALTA') {
        marcarCampoError('#add_prioridad', 'Los anuncios URGENTES deben tener prioridad ALTA');
        erroresEncontrados.push('Anuncio urgente requiere prioridad alta');
        isValid = false;
    }
    
    // 21. Validar coherencia tipo-destinatario para recordatorios
    if (tipo === 'RECORDATORIO' && destinatario === 'APODERADOS') {
        const confirmar = confirm('Los RECORDATORIOS generalmente son para ESTUDIANTES. ¿Está seguro de enviarlo solo a APODERADOS?');
        if (!confirmar) {
            marcarCampoError('#add_destinatario', 'Reconsidere el destinatario para recordatorios');
            erroresEncontrados.push('Destinatario: reconsiderar para recordatorios');
            isValid = false;
        }
    }
    
    // 22. Validar longitud mínima de palabras en título (al menos 2 palabras)
    if (titulo) {
        const palabras = titulo.trim().split(/\s+/);
        if (palabras.length < 2) {
            marcarCampoError('#add_titulo', 'El título debe contener al menos 2 palabras');
            erroresEncontrados.push('Título: mínimo 2 palabras');
            isValid = false;
        }
    }
    
    // 23. Validar que el título no tenga palabras excesivamente largas
    if (titulo) {
        const palabras = titulo.split(/\s+/);
        const palabraLarga = palabras.find(p => p.length > 50);
        if (palabraLarga) {
            marcarCampoError('#add_titulo', 'El título contiene palabras excesivamente largas');
            erroresEncontrados.push('Título: palabras muy largas (máximo 50 caracteres por palabra)');
            isValid = false;
        }
    }
    
    // 24. Validar que el contenido tenga al menos 3 palabras
    if (contenido) {
        const palabras = contenido.trim().split(/\s+/);
        if (palabras.length < 3) {
            marcarCampoError('#add_contenido', 'El contenido debe contener al menos 3 palabras');
            erroresEncontrados.push('Contenido: mínimo 3 palabras');
            isValid = false;
        }
    }
    
    // 25. Validar que no haya caracteres de control en el título
    if (titulo && /[\x00-\x1F\x7F]/.test(titulo)) {
        marcarCampoError('#add_titulo', 'El título contiene caracteres no válidos');
        erroresEncontrados.push('Título: caracteres de control no permitidos');
        isValid = false;
    }
    
    // 26. Validar que no haya caracteres de control en el contenido
    if (contenido && /[\x00-\x1F\x7F]/.test(contenido)) {
        marcarCampoError('#add_contenido', 'El contenido contiene caracteres no válidos');
        erroresEncontrados.push('Contenido: caracteres de control no permitidos');
        isValid = false;
    }
    
    // 27. Validar que el título no sea todo mayúsculas (spam prevention)
    if (titulo && titulo === titulo.toUpperCase() && titulo.length > 10) {
        const confirmar = confirm('El título está completamente en MAYÚSCULAS. ¿Es correcto?');
        if (!confirmar) {
            marcarCampoError('#add_titulo', 'Reconsidere el uso de mayúsculas');
            erroresEncontrados.push('Título: verificar uso de mayúsculas');
            isValid = false;
        }
    }
    
    // 28. Validar que para eventos se sugiera fecha de expiración
    if (tipo === 'EVENTO' && !fechaExp) {
        const confirmar = confirm('Los EVENTOS normalmente tienen fecha de expiración. ¿Desea continuar sin ella?');
        if (!confirmar) {
            marcarCampoError('#add_fecha_expiracion', 'Considere agregar fecha de expiración');
            erroresEncontrados.push('Evento: se recomienda fecha de expiración');
            isValid = false;
        }
    }
    
    // 29. Validar que el contenido tenga puntuación adecuada para anuncios largos
    if (contenido && contenido.length > 100) {
        const tienePuntuacion = /[.!?]/.test(contenido);
        if (!tienePuntuacion) {
            const confirmar = confirm('El contenido es largo pero no tiene puntuación. ¿Es correcto?');
            if (!confirmar) {
                marcarCampoError('#add_contenido', 'Agregue puntuación al contenido');
                erroresEncontrados.push('Contenido: agregar puntuación');
                isValid = false;
            }
        }
    }
    
    // 30. Validar duplicados de título en el mismo curso
    if (titulo && curso) {
        // Esta validación se hace en el servidor, pero advertir al usuario
        const titulosExistentes = [];
        $('.anuncio-item').each(function() {
            if ($(this).data('curso') == curso) {
                const tituloExistente = $(this).find('.anuncio-header h5').text().trim();
                titulosExistentes.push(tituloExistente.toLowerCase());
            }
        });
        
        if (titulosExistentes.includes(titulo.toLowerCase())) {
            const confirmar = confirm('Ya existe un anuncio con título similar en este curso. ¿Desea continuar?');
            if (!confirmar) {
                marcarCampoError('#add_titulo', 'Título posiblemente duplicado');
                erroresEncontrados.push('Título: posible duplicado en el curso');
                isValid = false;
            }
        }
    }
    
    // Mostrar errores si los hay
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL ANUNCIO\n\nErrores encontrados (${erroresEncontrados.length}):\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            customClass: {
                popup: 'swal-wide'
            },
            footer: `Total de errores: ${erroresEncontrados.length}/30 validaciones`
        });
        
        // Hacer scroll al primer error
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

// ==================== FUNCIÓN DE VALIDACIÓN COMPLETA (EDITAR) ====================

function validarFormularioCompletoEditar() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // Validar ID del anuncio
    const anuncioId = $('#edit_anuncio_id').val();
    if (!anuncioId) {
        erroresEncontrados.push('ID de anuncio no encontrado');
        isValid = false;
    }
    
    // Aplicar las mismas 30 validaciones pero con prefijo 'edit_'
    
    // 1. Validar curso
    const curso = $('#edit_curso_id').val();
    if (!curso) {
        marcarCampoError('#edit_curso_id', 'Debe seleccionar un curso');
        erroresEncontrados.push('Curso requerido');
        isValid = false;
    }
    
    // 2-3. Validar título
    const titulo = $('#edit_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#edit_titulo', 'El título es obligatorio');
        erroresEncontrados.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5) {
        marcarCampoError('#edit_titulo', 'El título debe tener al menos 5 caracteres');
        erroresEncontrados.push('Título muy corto (mínimo 5 caracteres)');
        isValid = false;
    } else if (titulo.length > 255) {
        marcarCampoError('#edit_titulo', 'El título no puede superar los 255 caracteres');
        erroresEncontrados.push('Título muy largo (máximo 255 caracteres)');
        isValid = false;
    }
    
    if (titulo && titulo.replace(/\s/g, '').length === 0) {
        marcarCampoError('#edit_titulo', 'El título no puede contener solo espacios');
        erroresEncontrados.push('Título: no puede ser solo espacios');
        isValid = false;
    }
    
    // 4. Validar caracteres especiales en título
    if (titulo && /[<>{}[\]\\\/]/g.test(titulo)) {
        marcarCampoError('#edit_titulo', 'El título contiene caracteres no permitidos');
        erroresEncontrados.push('Título: caracteres especiales no permitidos');
        isValid = false;
    }
    
    // 5-6. Validar contenido
    const contenido = $('#edit_contenido').val().trim();
    if (!contenido) {
        marcarCampoError('#edit_contenido', 'El contenido es obligatorio');
        erroresEncontrados.push('Contenido requerido');
        isValid = false;
    } else if (contenido.length < 10) {
        marcarCampoError('#edit_contenido', 'El contenido debe tener al menos 10 caracteres');
        erroresEncontrados.push('Contenido muy corto (mínimo 10 caracteres)');
        isValid = false;
    } else if (contenido.length > 5000) {
        marcarCampoError('#edit_contenido', 'El contenido no puede superar los 5000 caracteres');
        erroresEncontrados.push('Contenido muy largo (máximo 5000 caracteres)');
        isValid = false;
    }
    
    if (contenido && contenido.replace(/\s/g, '').length === 0) {
        marcarCampoError('#edit_contenido', 'El contenido no puede contener solo espacios');
        erroresEncontrados.push('Contenido: no puede ser solo espacios');
        isValid = false;
    }
    
    // 7-8. Validar tipo
    const tipo = $('#edit_tipo').val();
    if (!tipo) {
        marcarCampoError('#edit_tipo', 'Debe seleccionar un tipo de anuncio');
        erroresEncontrados.push('Tipo de anuncio requerido');
        isValid = false;
    }
    
    const tiposValidos = ['INFORMATIVO', 'RECORDATORIO', 'URGENTE', 'EVENTO'];
    if (tipo && !tiposValidos.includes(tipo)) {
        marcarCampoError('#edit_tipo', 'Tipo de anuncio no válido');
        erroresEncontrados.push('Tipo de anuncio inválido');
        isValid = false;
    }
    
    // 9-10. Validar prioridad
    const prioridad = $('#edit_prioridad').val();
    if (!prioridad) {
        marcarCampoError('#edit_prioridad', 'Debe seleccionar una prioridad');
        erroresEncontrados.push('Prioridad requerida');
        isValid = false;
    }
    
    const prioridadesValidas = ['BAJA', 'NORMAL', 'ALTA'];
    if (prioridad && !prioridadesValidas.includes(prioridad)) {
        marcarCampoError('#edit_prioridad', 'Prioridad no válida');
        erroresEncontrados.push('Prioridad inválida');
        isValid = false;
    }
    
    // 11-12. Validar destinatario
    const destinatario = $('#edit_destinatario').val();
    if (!destinatario) {
        marcarCampoError('#edit_destinatario', 'Debe seleccionar un destinatario');
        erroresEncontrados.push('Destinatario requerido');
        isValid = false;
    }
    
    const destinatariosValidos = ['ESTUDIANTES', 'APODERADOS', 'TODOS'];
    if (destinatario && !destinatariosValidos.includes(destinatario)) {
        marcarCampoError('#edit_destinatario', 'Destinatario no válido');
        erroresEncontrados.push('Destinatario inválido');
        isValid = false;
    }
    
    // 13-15. Validar fecha de publicación
    const fechaPub = $('#edit_fecha_publicacion').val();
    if (!fechaPub) {
        marcarCampoError('#edit_fecha_publicacion', 'La fecha de publicación es obligatoria');
        erroresEncontrados.push('Fecha de publicación requerida');
        isValid = false;
    }
    
    // 16-19. Validar fecha de expiración
    const fechaExp = $('#edit_fecha_expiracion').val();
    if (fechaExp && fechaPub) {
        const fechaExpDate = new Date(fechaExp);
        const fechaPubDate = new Date(fechaPub);
        
        if (fechaExpDate <= fechaPubDate) {
            marcarCampoError('#edit_fecha_expiracion', 'La fecha de expiración debe ser posterior a la publicación');
            erroresEncontrados.push('Fecha de expiración: debe ser posterior a publicación');
            isValid = false;
        }
        
        const diferenciaHoras = (fechaExpDate - fechaPubDate) / (1000 * 60 * 60);
        if (diferenciaHoras < 1) {
            marcarCampoError('#edit_fecha_expiracion', 'La expiración debe ser al menos 1 hora después');
            erroresEncontrados.push('Fecha de expiración: mínimo 1 hora después');
            isValid = false;
        }
    }
    
    // 20. Validar coherencia tipo-prioridad
    if (tipo === 'URGENTE' && prioridad !== 'ALTA') {
        marcarCampoError('#edit_prioridad', 'Los anuncios URGENTES deben tener prioridad ALTA');
        erroresEncontrados.push('Anuncio urgente requiere prioridad alta');
        isValid = false;
    }
    
    // 22. Validar mínimo de palabras en título
    if (titulo) {
        const palabras = titulo.trim().split(/\s+/);
        if (palabras.length < 2) {
            marcarCampoError('#edit_titulo', 'El título debe contener al menos 2 palabras');
            erroresEncontrados.push('Título: mínimo 2 palabras');
            isValid = false;
        }
    }
    
    // 24. Validar mínimo de palabras en contenido
    if (contenido) {
        const palabras = contenido.trim().split(/\s+/);
        if (palabras.length < 3) {
            marcarCampoError('#edit_contenido', 'El contenido debe contener al menos 3 palabras');
            erroresEncontrados.push('Contenido: mínimo 3 palabras');
            isValid = false;
        }
    }
    
    // Mostrar errores si los hay
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE ACTUALIZAR EL ANUNCIO\n\nErrores encontrados (${erroresEncontrados.length}):\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            customClass: {
                popup: 'swal-wide'
            }
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

// ==================== FUNCIONES AUXILIARES ====================

function validarFechas(prefijo) {
    const fechaPub = $(`#${prefijo}_fecha_publicacion`).val();
    const fechaExp = $(`#${prefijo}_fecha_expiracion`).val();
    
    if (!fechaPub) return;
    
    const fechaPubDate = new Date(fechaPub);
    const ahora = new Date();
    
    // Limpiar errores previos
    $(`#${prefijo}_fecha_publicacion`).removeClass('campo-error is-invalid');
    $(`#${prefijo}_fecha_expiracion`).removeClass('campo-error is-invalid');
    
    // Validar que no sea muy pasada
    if (fechaPubDate < ahora.setMinutes(ahora.getMinutes() - 5)) {
        $(`#${prefijo}_fecha_publicacion`).addClass('campo-error');
    }
    
    // Validar expiración si existe
    if (fechaExp) {
        const fechaExpDate = new Date(fechaExp);
        $(`#${prefijo}_fecha_expiracion`).attr('min', fechaPub);
        
        if (fechaExpDate <= fechaPubDate) {
            $(`#${prefijo}_fecha_expiracion`).addClass('campo-error');
        }
    }
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function limpiarFormulario(prefijo) {
    $(`#form${prefijo === 'add' ? 'Agregar' : 'Editar'}Anuncio`)[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    if (prefijo === 'add') {
        $('#contador_titulo').text('0/255');
        $('#contador_contenido').text('0/5000');
        const ahora = new Date().toISOString().slice(0, 16);
        $('#add_fecha_publicacion').val(ahora);
    } else {
        $('#contador_titulo_edit').text('0/255');
        $('#contador_contenido_edit').text('0/5000');
    }
}

function cargarDatosEdicionAnuncio(anuncio) {
    $('#edit_anuncio_id').val(anuncio.id);
    $('#edit_curso_id').val(anuncio.curso_id);
    $('#edit_titulo').val(anuncio.titulo);
    $('#edit_contenido').val(anuncio.contenido);
    
    const config = anuncio.configuraciones || {};
    $('#edit_tipo').val(config.tipo || 'INFORMATIVO');
    $('#edit_prioridad').val(config.prioridad || 'NORMAL');
    $('#edit_destinatario').val(config.destinatario || 'ESTUDIANTES');
    
    // Formatear fechas para datetime-local
    if (anuncio.fecha_publicacion) {
        const fechaPub = new Date(anuncio.fecha_publicacion);
        $('#edit_fecha_publicacion').val(fechaPub.toISOString().slice(0, 16));
    }
    
    if (config.fecha_expiracion) {
        const fechaExp = new Date(config.fecha_expiracion);
        $('#edit_fecha_expiracion').val(fechaExp.toISOString().slice(0, 16));
    }
    
    // Actualizar contadores
    $('#contador_titulo_edit').text(`${anuncio.titulo.length}/255`);
    $('#contador_contenido_edit').text(`${anuncio.contenido.length}/5000`);
}

function mostrarCarga() {
    $('#loadingOverlay').css('display', 'flex');
}

function ocultarCarga() {
    $('#loadingOverlay').hide();
}

function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
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