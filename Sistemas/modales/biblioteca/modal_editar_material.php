<!-- Modal Editar Material Bibliográfico -->
<div class="modal fade" id="modalEditarMaterial" tabindex="-1" aria-labelledby="modalEditarMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #fff9c4 0%, #ffe0b2 100%); color: #e65100;">
                <h5 class="modal-title" id="modalEditarMaterialLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Material Bibliográfico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarMaterial" method="POST">
                <input type="hidden" id="edit_material_id" name="material_id">
                
                <div class="modal-body">
                    <div class="row">
                        
                        <!-- Datos Básicos -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_tipo" class="form-label">
                                                Tipo de Material <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="LIBRO">Libro</option>
                                                <option value="REVISTA">Revista</option>
                                                <option value="PERIODICO">Periódico</option>
                                                <option value="TESIS">Tesis</option>
                                                <option value="MANUAL">Manual</option>
                                                <option value="AUDIOVISUAL">Audiovisual</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="edit_titulo" class="form-label">
                                                Título <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                                   placeholder="Título del material" required maxlength="200" minlength="3">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_subtitulo" class="form-label">Subtítulo</label>
                                            <input type="text" class="form-control" id="edit_subtitulo" name="subtitulo" 
                                                   placeholder="Subtítulo (opcional)" maxlength="200">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_isbn" class="form-label">
                                                ISBN <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_isbn" name="isbn" 
                                                   placeholder="978-84-08-12345-6" required 
                                                   pattern="[0-9\-]{13,17}" maxlength="17">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_codigo_barras" class="form-label">
                                                Código de Barras
                                            </label>
                                            <input type="text" class="form-control" id="edit_codigo_barras" name="codigo_barras" 
                                                   placeholder="Código de barras" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Autores -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-users me-2"></i>
                                        Autores <span class="text-danger">*</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="edit-autores-container">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarAutorEdit()">
                                        <i class="ti ti-plus me-2"></i>Agregar Autor
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Publicación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar me-2"></i>
                                        Datos de Publicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_editorial" class="form-label">
                                                Editorial <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_editorial" name="editorial" 
                                                   placeholder="Editorial" required maxlength="100" minlength="3">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_anio_publicacion" class="form-label">
                                                Año <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_anio_publicacion" name="anio_publicacion" 
                                                   placeholder="2024" required min="1800" max="2025">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_edicion" class="form-label">Edición</label>
                                            <input type="text" class="form-control" id="edit_edicion" name="edicion" 
                                                   placeholder="1ra Edición" maxlength="50">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_idioma" class="form-label">
                                                Idioma <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_idioma" name="idioma" required>
                                                <option value="">Seleccionar</option>
                                                <option value="Español">Español</option>
                                                <option value="Inglés">Inglés</option>
                                                <option value="Francés">Francés</option>
                                                <option value="Portugués">Portugués</option>
                                                <option value="Alemán">Alemán</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_paginas" class="form-label">
                                                Páginas <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_paginas" name="paginas" 
                                                   placeholder="320" required min="1" max="9999">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clasificación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-tag me-2"></i>
                                        Clasificación y Ubicación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_categoria" class="form-label">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_categoria" name="categoria" required>
                                                <option value="">Seleccionar categoría</option>
                                                <option value="Matemáticas">Matemáticas</option>
                                                <option value="Lengua y Literatura">Lengua y Literatura</option>
                                                <option value="Ciencias Naturales">Ciencias Naturales</option>
                                                <option value="Ciencias Sociales">Ciencias Sociales</option>
                                                <option value="Historia">Historia</option>
                                                <option value="Geografía">Geografía</option>
                                                <option value="Filosofía">Filosofía</option>
                                                <option value="Arte y Cultura">Arte y Cultura</option>
                                                <option value="Tecnología">Tecnología</option>
                                                <option value="Deportes">Deportes</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_codigo_dewey" class="form-label">
                                                Código Dewey <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_codigo_dewey" name="codigo_dewey" 
                                                   placeholder="510" required pattern="[0-9]{3}" maxlength="3">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_ubicacion" class="form-label">
                                                Ubicación <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_ubicacion" name="ubicacion" 
                                                   placeholder="Estante A-1" required maxlength="50">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_palabras_clave" class="form-label">Palabras Clave</label>
                                            <input type="text" class="form-control" id="edit_palabras_clave" name="palabras_clave" 
                                                   placeholder="algebra, geometria, secundaria" maxlength="200">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Físicos y Adquisición -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header" style="background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-package me-2"></i>
                                        Datos Físicos y Adquisición
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_ejemplares_actuales" class="form-label">
                                                Ejemplares Actuales
                                            </label>
                                            <input type="number" class="form-control" id="edit_ejemplares_actuales" 
                                                   readonly style="background: #f8f9fa;">
                                            <div class="form-text">Solo lectura - gestionar en módulo ejemplares</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_estado_general" class="form-label">
                                                Estado General <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_estado_general" name="estado_general" required>
                                                <option value="">Seleccionar</option>
                                                <option value="Excelente">Excelente</option>
                                                <option value="Bueno">Bueno</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Malo">Malo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_precio" class="form-label">Precio (S/)</label>
                                            <input type="number" class="form-control" id="edit_precio" name="precio" 
                                                   placeholder="85.50" step="0.01" min="0" max="9999.99">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_fecha_adquisicion" class="form-label">
                                                Fecha Adquisición <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_adquisicion" name="fecha_adquisicion" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_proveedor" class="form-label">Proveedor</label>
                                            <input type="text" class="form-control" id="edit_proveedor" name="proveedor" 
                                                   placeholder="Nombre del proveedor" maxlength="200">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarMaterial">
                        <i class="ti ti-device-floppy me-2"></i>Actualizar Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicion(material) {
    // Limpiar formulario
    $('#formEditarMaterial')[0].reset();
    $('#edit-autores-container').empty();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // Decodificar JSON
    const datosBasicos = material.datos_basicos || {};
    const datosPublicacion = material.datos_publicacion || {};
    const clasificacion = material.clasificacion || {};
    const datosFisicos = material.datos_fisicos || {};
    const datosAdquisicion = material.datos_adquisicion || {};
    const autores = material.autores || [];
    
    // Cargar ID
    $('#edit_material_id').val(material.id);
    
    // Datos Básicos
    $('#edit_tipo').val(datosBasicos.tipo || '');
    $('#edit_titulo').val(datosBasicos.titulo || '');
    $('#edit_subtitulo').val(datosBasicos.subtitulo || '');
    $('#edit_isbn').val(datosBasicos.isbn || '');
    $('#edit_codigo_barras').val(material.codigo_barras || '');
    
    // Autores
    if (autores.length > 0) {
        autores.forEach((autor, index) => {
            const autorHTML = `
                <div class="autor-item mb-3 p-3" style="background: #fafafa; border-radius: 8px;">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Nombre(s) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control autor-nombre" name="autor_nombre[]" 
                                   value="${autor.nombre || ''}" placeholder="Nombre del autor" required 
                                   pattern="[A-Za-zÀ-ÿ\\s]{2,50}" maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellido(s) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control autor-apellido" name="autor_apellido[]" 
                                   value="${autor.apellido || ''}" placeholder="Apellido del autor" required 
                                   pattern="[A-Za-zÀ-ÿ\\s]{2,50}" maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Principal</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input autor-principal" type="checkbox" 
                                       name="autor_principal[]" value="1" ${autor.principal ? 'checked' : ''}>
                                <label class="form-check-label">Autor principal</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="eliminarAutorEdit(this)" ${index === 0 ? 'style="display:none;"' : ''}>
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#edit-autores-container').append(autorHTML);
        });
    } else {
        agregarAutorEdit();
    }
    
    // Publicación
    $('#edit_editorial').val(datosPublicacion.editorial || '');
    $('#edit_anio_publicacion').val(datosPublicacion.anio_publicacion || '');
    $('#edit_edicion').val(datosPublicacion.edicion || '');
    $('#edit_idioma').val(datosPublicacion.idioma || '');
    $('#edit_paginas').val(datosPublicacion.paginas || '');
    
    // Clasificación
    $('#edit_categoria').val(clasificacion.categoria || '');
    $('#edit_codigo_dewey').val(clasificacion.codigo_dewey || '');
    $('#edit_ubicacion').val(datosFisicos.ubicacion || '');
    $('#edit_palabras_clave').val(clasificacion.palabras_clave || '');
    
    // Datos Físicos
    $('#edit_ejemplares_actuales').val(material.total_ejemplares || 0);
    $('#edit_estado_general').val(datosFisicos.estado_general || '');
    $('#edit_precio').val(datosAdquisicion.precio || '');
    $('#edit_fecha_adquisicion').val(datosAdquisicion.fecha_adquisicion || '');
    $('#edit_proveedor').val(datosAdquisicion.proveedor || '');
}

function agregarAutorEdit() {
    const totalAutores = $('#edit-autores-container .autor-item').length;
    if (totalAutores >= 5) {
        Swal.fire({
            title: 'Límite Alcanzado',
            text: 'Solo se permiten hasta 5 autores por material',
            icon: 'warning',
            confirmButtonColor: '#fd7e14'
        });
        return;
    }

    const autorHTML = `
        <div class="autor-item mb-3 p-3" style="background: #fafafa; border-radius: 8px;">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Nombre(s) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control autor-nombre" name="autor_nombre[]" 
                           placeholder="Nombre del autor" required 
                           pattern="[A-Za-zÀ-ÿ\\s]{2,50}" maxlength="50">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Apellido(s) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control autor-apellido" name="autor_apellido[]" 
                           placeholder="Apellido del autor" required 
                           pattern="[A-Za-zÀ-ÿ\\s]{2,50}" maxlength="50">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Principal</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input autor-principal" type="checkbox" 
                               name="autor_principal[]" value="1">
                        <label class="form-check-label">Autor principal</label>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                            onclick="eliminarAutorEdit(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#edit-autores-container').append(autorHTML);
}

function eliminarAutorEdit(btn) {
    const totalAutores = $('#edit-autores-container .autor-item').length;
    if (totalAutores > 1) {
        $(btn).closest('.autor-item').remove();
    } else {
        Swal.fire({
            title: 'No se puede eliminar',
            text: 'Debe haber al menos un autor',
            icon: 'warning',
            confirmButtonColor: '#fd7e14'
        });
    }
}

$(document).ready(function() {
    // Configurar fecha máxima
    const hoy = new Date().toISOString().split('T')[0];
    $('#edit_fecha_adquisicion').attr('max', hoy);
    
    const añoActual = new Date().getFullYear();
    $('#edit_anio_publicacion').attr('max', añoActual);
    
    // Solo un autor principal
    $(document).on('change', '#edit-autores-container .autor-principal', function() {
        if ($(this).is(':checked')) {
            $('#edit-autores-container .autor-principal').not(this).prop('checked', false);
        }
    });
    
    // Validación nombres/apellidos
    $(document).on('input', '#edit-autores-container .autor-nombre, #edit-autores-container .autor-apellido', function() {
        $(this).val($(this).val().replace(/[^A-Za-zÀ-ÿ\s]/g, ''));
    });

    // Envío del formulario
    $('#formEditarMaterial').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioMaterialEdit()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        // Preparar autores
        const autores = [];
        $('#edit-autores-container .autor-item').each(function() {
            const nombre = $(this).find('.autor-nombre').val();
            const apellido = $(this).find('.autor-apellido').val();
            const principal = $(this).find('.autor-principal').is(':checked');
            
            if (nombre && apellido) {
                autores.push({
                    nombre: nombre,
                    apellido: apellido,
                    principal: principal
                });
            }
        });
        formData.append('autores', JSON.stringify(autores));
        
        mostrarCarga();
        $('#btnActualizarMaterial').prop('disabled', true);

        $.ajax({
            url: 'modales/biblioteca/procesar_materiales.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarMaterial').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Material Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarMaterial').modal('hide');
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
                $('#btnActualizarMaterial').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });
});

function validarFormularioMaterialEdit() {
    let isValid = true;
    let errores = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // Reutilizar validaciones del modal agregar con prefijo edit_
    const tipo = $('#edit_tipo').val();
    if (!tipo) {
        marcarCampoError('#edit_tipo', 'Debe seleccionar un tipo de material');
        errores.push('Tipo de material requerido');
        isValid = false;
    }
    
    const titulo = $('#edit_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#edit_titulo', 'El título es obligatorio');
        errores.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 3 || titulo.length > 200) {
        marcarCampoError('#edit_titulo', 'El título debe tener entre 3 y 200 caracteres');
        errores.push('Título: longitud incorrecta');
        isValid = false;
    }
    
    const isbn = $('#edit_isbn').val().trim();
    if (!isbn) {
        marcarCampoError('#edit_isbn', 'El ISBN es obligatorio');
        errores.push('ISBN requerido');
        isValid = false;
    } else if (!/^[0-9\-]{13,17}$/.test(isbn)) {
        marcarCampoError('#edit_isbn', 'Formato de ISBN inválido');
        errores.push('ISBN: formato inválido');
        isValid = false;
    }
    
    // Validar autores
    const autoresValidos = [];
    let hayAutorPrincipal = false;
    
    $('#edit-autores-container .autor-item').each(function() {
        const nombre = $(this).find('.autor-nombre').val().trim();
        const apellido = $(this).find('.autor-apellido').val().trim();
        const esPrincipal = $(this).find('.autor-principal').is(':checked');
        
        if (!nombre || nombre.length < 2 || nombre.length > 50) {
            marcarCampoError($(this).find('.autor-nombre'), 'Nombre inválido');
            errores.push('Nombre de autor inválido');
            isValid = false;
        }
        
        if (!apellido || apellido.length < 2 || apellido.length > 50) {
            marcarCampoError($(this).find('.autor-apellido'), 'Apellido inválido');
            errores.push('Apellido de autor inválido');
            isValid = false;
        }
        
        if (nombre && apellido) {
            autoresValidos.push({nombre, apellido});
        }
        
        if (esPrincipal) {
            hayAutorPrincipal = true;
        }
    });
    
    if (autoresValidos.length === 0) {
        errores.push('Debe tener al menos un autor');
        isValid = false;
    }
    
    if (!hayAutorPrincipal && autoresValidos.length > 0) {
        errores.push('Debe marcar un autor como principal');
        isValid = false;
    }
    
    // Validaciones restantes similares al modal agregar...
    const editorial = $('#edit_editorial').val().trim();
    if (!editorial || editorial.length < 3) {
        marcarCampoError('#edit_editorial', 'Editorial inválida');
        errores.push('Editorial inválida');
        isValid = false;
    }
    
    const anio = parseInt($('#edit_anio_publicacion').val());
    const anioActual = new Date().getFullYear();
    if (!anio || anio < 1800 || anio > anioActual) {
        marcarCampoError('#edit_anio_publicacion', 'Año inválido');
        errores.push('Año de publicación inválido');
        isValid = false;
    }
    
    const paginas = parseInt($('#edit_paginas').val());
    if (!paginas || paginas < 1 || paginas > 9999) {
        marcarCampoError('#edit_paginas', 'Páginas inválidas');
        errores.push('Número de páginas inválido');
        isValid = false;
    }
    
    const categoria = $('#edit_categoria').val();
    if (!categoria) {
        marcarCampoError('#edit_categoria', 'Categoría requerida');
        errores.push('Categoría requerida');
        isValid = false;
    }
    
    const dewey = $('#edit_codigo_dewey').val().trim();
    if (!dewey || !/^[0-9]{3}$/.test(dewey)) {
        marcarCampoError('#edit_codigo_dewey', 'Código Dewey inválido');
        errores.push('Código Dewey inválido');
        isValid = false;
    }
    
    const ubicacion = $('#edit_ubicacion').val().trim();
    if (!ubicacion) {
        marcarCampoError('#edit_ubicacion', 'Ubicación requerida');
        errores.push('Ubicación requerida');
        isValid = false;
    }
    
    if (!isValid) {
        Swal.fire({
            title: 'Formulario Incompleto',
            text: `Errores:\n• ${errores.join('\n• ')}`,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    }
    
    return isValid;
}
</script>