<!-- Modal Agregar Material Bibliográfico -->
<div class="modal fade" id="modalAgregarMaterial" tabindex="-1" aria-labelledby="modalAgregarMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 100%); color: #2d5016;">
                <h5 class="modal-title" id="modalAgregarMaterialLabel">
                    <i class="ti ti-book-plus me-2"></i>
                    Nuevo Material Bibliográfico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarMaterial" method="POST">
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
                                            <label for="add_tipo" class="form-label">
                                                Tipo de Material <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo" name="tipo" required>
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
                                            <label for="add_titulo" class="form-label">
                                                Título <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_titulo" name="titulo" 
                                                   placeholder="Título del material" required maxlength="200" minlength="3">
                                            <div class="form-text">Mínimo 3 caracteres, máximo 200</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_subtitulo" class="form-label">Subtítulo</label>
                                            <input type="text" class="form-control" id="add_subtitulo" name="subtitulo" 
                                                   placeholder="Subtítulo (opcional)" maxlength="200">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_isbn" class="form-label">
                                                ISBN <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_isbn" name="isbn" 
                                                   placeholder="978-84-08-12345-6" required 
                                                   pattern="[0-9\-]{13,17}" maxlength="17">
                                            <div class="form-text">Formato: 978-XX-XX-XXXXX-X</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_codigo_barras" class="form-label">
                                                Código de Barras
                                            </label>
                                            <input type="text" class="form-control" id="add_codigo_barras" name="codigo_barras" 
                                                   placeholder="Se generará automáticamente" readonly>
                                            <div class="form-text">Autogenerado basado en ISBN</div>
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
                                    <div id="autores-container">
                                        <div class="autor-item mb-3 p-3" style="background: #fafafa; border-radius: 8px;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Nombre(s) <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control autor-nombre" name="autor_nombre[]" 
                                                           placeholder="Nombre del autor" required 
                                                           pattern="[A-Za-zÀ-ÿ\s]{2,50}" maxlength="50">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Apellido(s) <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control autor-apellido" name="autor_apellido[]" 
                                                           placeholder="Apellido del autor" required 
                                                           pattern="[A-Za-zÀ-ÿ\s]{2,50}" maxlength="50">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Principal</label>
                                                    <div class="form-check form-switch mt-2">
                                                        <input class="form-check-input autor-principal" type="checkbox" 
                                                               name="autor_principal[]" value="1" checked>
                                                        <label class="form-check-label">Autor principal</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                                            onclick="eliminarAutor(this)" style="display:none;">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarAutor()">
                                        <i class="ti ti-plus me-2"></i>Agregar Autor
                                    </button>
                                    <div class="form-text mt-2">Se requiere al menos un autor. Máximo 5 autores.</div>
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
                                            <label for="add_editorial" class="form-label">
                                                Editorial <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_editorial" name="editorial" 
                                                   placeholder="Editorial" required maxlength="100" minlength="3">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_anio_publicacion" class="form-label">
                                                Año <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_anio_publicacion" name="anio_publicacion" 
                                                   placeholder="2024" required min="1800" max="2025">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_edicion" class="form-label">Edición</label>
                                            <input type="text" class="form-control" id="add_edicion" name="edicion" 
                                                   placeholder="1ra Edición" maxlength="50">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_idioma" class="form-label">
                                                Idioma <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_idioma" name="idioma" required>
                                                <option value="">Seleccionar</option>
                                                <option value="Español" selected>Español</option>
                                                <option value="Inglés">Inglés</option>
                                                <option value="Francés">Francés</option>
                                                <option value="Portugués">Portugués</option>
                                                <option value="Alemán">Alemán</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_paginas" class="form-label">
                                                Páginas <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_paginas" name="paginas" 
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
                                            <label for="add_categoria" class="form-label">
                                                Categoría <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_categoria" name="categoria" required>
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
                                            <label for="add_codigo_dewey" class="form-label">
                                                Código Dewey <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_codigo_dewey" name="codigo_dewey" 
                                                   placeholder="510" required pattern="[0-9]{3}" maxlength="3">
                                            <div class="form-text">3 dígitos numéricos</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_ubicacion" class="form-label">
                                                Ubicación <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_ubicacion" name="ubicacion" 
                                                   placeholder="Estante A-1" required maxlength="50">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_palabras_clave" class="form-label">Palabras Clave</label>
                                            <input type="text" class="form-control" id="add_palabras_clave" name="palabras_clave" 
                                                   placeholder="algebra, geometria, secundaria" maxlength="200">
                                            <div class="form-text">Separadas por comas</div>
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
                                            <label for="add_ejemplares" class="form-label">
                                                Cantidad Ejemplares <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_ejemplares" name="ejemplares" 
                                                   placeholder="3" required min="1" max="100">
                                            <div class="form-text">Mínimo 1, máximo 100</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_estado_general" class="form-label">
                                                Estado General <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_estado_general" name="estado_general" required>
                                                <option value="">Seleccionar</option>
                                                <option value="Excelente">Excelente</option>
                                                <option value="Bueno" selected>Bueno</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Malo">Malo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_precio" class="form-label">Precio (S/)</label>
                                            <input type="number" class="form-control" id="add_precio" name="precio" 
                                                   placeholder="85.50" step="0.01" min="0" max="9999.99">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_fecha_adquisicion" class="form-label">
                                                Fecha Adquisición <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_adquisicion" name="fecha_adquisicion" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="add_proveedor" class="form-label">Proveedor</label>
                                            <input type="text" class="form-control" id="add_proveedor" name="proveedor" 
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarMaterial">
                        <i class="ti ti-device-floppy me-2"></i>Crear Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
</style>

<script>
$(document).ready(function() {
    // Configurar fecha máxima
    const hoy = new Date().toISOString().split('T')[0];
    $('#add_fecha_adquisicion').attr('max', hoy);

    // Generar código de barras basado en ISBN
    $('#add_isbn').on('input', function() {
        let isbn = $(this).val().replace(/[^0-9]/g, '');
        if (isbn.length >= 13) {
            $('#add_codigo_barras').val(isbn);
        }
    });

    // Solo números en ISBN
    $('#add_isbn').on('input', function() {
        let valor = $(this).val().replace(/[^0-9\-]/g, '');
        $(this).val(valor);
    });

    // Validación de año
    const añoActual = new Date().getFullYear();
    $('#add_anio_publicacion').attr('max', añoActual);

    // Solo un autor principal
    $(document).on('change', '.autor-principal', function() {
        if ($(this).is(':checked')) {
            $('.autor-principal').not(this).prop('checked', false);
        }
    });

    // Validación de nombres y apellidos de autores
    $(document).on('input', '.autor-nombre, .autor-apellido', function() {
        $(this).val($(this).val().replace(/[^A-Za-zÀ-ÿ\s]/g, ''));
    });

    // Envío del formulario
    $('#formAgregarMaterial').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioMaterial()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Preparar autores
        const autores = [];
        $('.autor-item').each(function() {
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
        $('#btnGuardarMaterial').prop('disabled', true);

        $.ajax({
            url: 'modales/biblioteca/procesar_materiales.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarMaterial').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Material Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarMaterial').modal('hide');
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
                $('#btnGuardarMaterial').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarMaterial').on('hidden.bs.modal', function() {
        limpiarFormularioMaterial();
    });
});

function agregarAutor() {
    const totalAutores = $('.autor-item').length;
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
                            onclick="eliminarAutor(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#autores-container').append(autorHTML);
    
    // Mostrar botón eliminar en todos excepto el primero
    $('.autor-item').each(function(index) {
        if (index > 0) {
            $(this).find('.btn-outline-danger').show();
        }
    });
}

function eliminarAutor(btn) {
    const totalAutores = $('.autor-item').length;
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

function validarFormularioMaterial() {
    let isValid = true;
    let errores = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar tipo
    const tipo = $('#add_tipo').val();
    if (!tipo) {
        marcarCampoError('#add_tipo', 'Debe seleccionar un tipo de material');
        errores.push('Tipo de material requerido');
        isValid = false;
    }
    
    // 2. Validar título (3-200 caracteres)
    const titulo = $('#add_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#add_titulo', 'El título es obligatorio');
        errores.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 3 || titulo.length > 200) {
        marcarCampoError('#add_titulo', 'El título debe tener entre 3 y 200 caracteres');
        errores.push('Título: longitud incorrecta (3-200 caracteres)');
        isValid = false;
    }
    
    // 3. Validar ISBN (formato correcto)
    const isbn = $('#add_isbn').val().trim();
    if (!isbn) {
        marcarCampoError('#add_isbn', 'El ISBN es obligatorio');
        errores.push('ISBN requerido');
        isValid = false;
    } else if (!/^[0-9\-]{13,17}$/.test(isbn)) {
        marcarCampoError('#add_isbn', 'Formato de ISBN inválido');
        errores.push('ISBN: formato inválido');
        isValid = false;
    } else {
        const isbnSinGuiones = isbn.replace(/[^0-9]/g, '');
        if (isbnSinGuiones.length !== 13) {
            marcarCampoError('#add_isbn', 'El ISBN debe tener 13 dígitos');
            errores.push('ISBN: debe tener 13 dígitos');
            isValid = false;
        }
    }
    
    // 4. Validar autores (al menos uno)
    const autoresValidos = [];
    let hayAutorPrincipal = false;
    
    $('.autor-item').each(function() {
        const nombre = $(this).find('.autor-nombre').val().trim();
        const apellido = $(this).find('.autor-apellido').val().trim();
        const esPrincipal = $(this).find('.autor-principal').is(':checked');
        
        if (!nombre) {
            marcarCampoError($(this).find('.autor-nombre'), 'Nombre de autor requerido');
            errores.push('Nombre de autor requerido');
            isValid = false;
        } else if (nombre.length < 2 || nombre.length > 50) {
            marcarCampoError($(this).find('.autor-nombre'), 'Nombre debe tener entre 2 y 50 caracteres');
            errores.push('Nombre autor: longitud incorrecta');
            isValid = false;
        } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(nombre)) {
            marcarCampoError($(this).find('.autor-nombre'), 'Solo letras y espacios');
            errores.push('Nombre autor: solo letras permitidas');
            isValid = false;
        }
        
        if (!apellido) {
            marcarCampoError($(this).find('.autor-apellido'), 'Apellido de autor requerido');
            errores.push('Apellido de autor requerido');
            isValid = false;
        } else if (apellido.length < 2 || apellido.length > 50) {
            marcarCampoError($(this).find('.autor-apellido'), 'Apellido debe tener entre 2 y 50 caracteres');
            errores.push('Apellido autor: longitud incorrecta');
            isValid = false;
        } else if (!/^[A-Za-zÀ-ÿ\s]+$/.test(apellido)) {
            marcarCampoError($(this).find('.autor-apellido'), 'Solo letras y espacios');
            errores.push('Apellido autor: solo letras permitidas');
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
        errores.push('Debe agregar al menos un autor');
        isValid = false;
    }
    
    if (!hayAutorPrincipal && autoresValidos.length > 0) {
        errores.push('Debe marcar un autor como principal');
        isValid = false;
    }
    
    // 5. Validar editorial (3-100 caracteres)
    const editorial = $('#add_editorial').val().trim();
    if (!editorial) {
        marcarCampoError('#add_editorial', 'La editorial es obligatoria');
        errores.push('Editorial requerida');
        isValid = false;
    } else if (editorial.length < 3 || editorial.length > 100) {
        marcarCampoError('#add_editorial', 'Editorial debe tener entre 3 y 100 caracteres');
        errores.push('Editorial: longitud incorrecta');
        isValid = false;
    }
    
    // 6. Validar año de publicación (1800-2025)
    const anio = parseInt($('#add_anio_publicacion').val());
    const anioActual = new Date().getFullYear();
    if (!anio) {
        marcarCampoError('#add_anio_publicacion', 'El año es obligatorio');
        errores.push('Año de publicación requerido');
        isValid = false;
    } else if (anio < 1800 || anio > anioActual) {
        marcarCampoError('#add_anio_publicacion', `Año debe estar entre 1800 y ${anioActual}`);
        errores.push('Año: fuera de rango válido');
        isValid = false;
    }
    
    // 7. Validar idioma
    const idioma = $('#add_idioma').val();
    if (!idioma) {
        marcarCampoError('#add_idioma', 'Debe seleccionar un idioma');
        errores.push('Idioma requerido');
        isValid = false;
    }
    
    // 8. Validar páginas (1-9999)
    const paginas = parseInt($('#add_paginas').val());
    if (!paginas) {
        marcarCampoError('#add_paginas', 'El número de páginas es obligatorio');
        errores.push('Número de páginas requerido');
        isValid = false;
    } else if (paginas < 1 || paginas > 9999) {
        marcarCampoError('#add_paginas', 'Páginas debe estar entre 1 y 9999');
        errores.push('Páginas: valor fuera de rango');
        isValid = false;
    }
    
    // 9. Validar categoría
    const categoria = $('#add_categoria').val();
    if (!categoria) {
        marcarCampoError('#add_categoria', 'Debe seleccionar una categoría');
        errores.push('Categoría requerida');
        isValid = false;
    }
    
    // 10. Validar código Dewey (3 dígitos)
    const dewey = $('#add_codigo_dewey').val().trim();
    if (!dewey) {
        marcarCampoError('#add_codigo_dewey', 'El código Dewey es obligatorio');
        errores.push('Código Dewey requerido');
        isValid = false;
    } else if (!/^[0-9]{3}$/.test(dewey)) {
        marcarCampoError('#add_codigo_dewey', 'Debe ser exactamente 3 dígitos');
        errores.push('Código Dewey: formato inválido');
        isValid = false;
    }
    
    // 11. Validar ubicación
    const ubicacion = $('#add_ubicacion').val().trim();
    if (!ubicacion) {
        marcarCampoError('#add_ubicacion', 'La ubicación es obligatoria');
        errores.push('Ubicación requerida');
        isValid = false;
    } else if (ubicacion.length > 50) {
        marcarCampoError('#add_ubicacion', 'Ubicación máximo 50 caracteres');
        errores.push('Ubicación: muy larga');
        isValid = false;
    }
    
    // 12. Validar ejemplares (1-100)
    const ejemplares = parseInt($('#add_ejemplares').val());
    if (!ejemplares) {
        marcarCampoError('#add_ejemplares', 'La cantidad de ejemplares es obligatoria');
        errores.push('Cantidad de ejemplares requerida');
        isValid = false;
    } else if (ejemplares < 1 || ejemplares > 100) {
        marcarCampoError('#add_ejemplares', 'Ejemplares debe estar entre 1 y 100');
        errores.push('Ejemplares: valor fuera de rango');
        isValid = false;
    }
    
    // 13. Validar estado general
    const estadoGeneral = $('#add_estado_general').val();
    if (!estadoGeneral) {
        marcarCampoError('#add_estado_general', 'Debe seleccionar un estado');
        errores.push('Estado general requerido');
        isValid = false;
    }
    
    // 14. Validar fecha de adquisición
    const fechaAdquisicion = $('#add_fecha_adquisicion').val();
    if (!fechaAdquisicion) {
        marcarCampoError('#add_fecha_adquisicion', 'La fecha de adquisición es obligatoria');
        errores.push('Fecha de adquisición requerida');
        isValid = false;
    } else {
        const fecha = new Date(fechaAdquisicion);
        const hoy = new Date();
        if (fecha > hoy) {
            marcarCampoError('#add_fecha_adquisicion', 'La fecha no puede ser futura');
            errores.push('Fecha de adquisición: no puede ser futura');
            isValid = false;
        }
    }
    
    // 15. Validar precio (opcional, pero si está debe ser válido)
    const precio = $('#add_precio').val();
    if (precio && (parseFloat(precio) < 0 || parseFloat(precio) > 9999.99)) {
        marcarCampoError('#add_precio', 'Precio debe estar entre 0 y 9999.99');
        errores.push('Precio: valor fuera de rango');
        isValid = false;
    }
    
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL MATERIAL\n\nErrores encontrados:\n\n• ${errores.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            width: '600px',
            footer: `Total de errores: ${errores.length}`
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function limpiarFormularioMaterial() {
    $('#formAgregarMaterial')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#add_codigo_barras').val('');
    
    // Dejar solo un autor
    const primerAutor = $('.autor-item').first();
    $('.autor-item').not(primerAutor).remove();
    primerAutor.find('.autor-principal').prop('checked', true);
    primerAutor.find('.btn-outline-danger').hide();
}
</script>