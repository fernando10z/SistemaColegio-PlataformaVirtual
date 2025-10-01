<!-- Modal Agregar Unidad -->
<div class="modal fade" id="modalAgregarUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="ti ti-book-2 me-2"></i>
                    Nueva Unidad Didáctica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formAgregarUnidad" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Curso -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Curso <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="add_curso_id" name="curso_id" required>
                                <option value="">Seleccionar curso</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id'] ?>">
                                        <?= htmlspecialchars($curso['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Orden -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Orden <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="add_orden" name="orden" 
                                   min="1" max="50" required placeholder="1">
                            <small class="text-muted">Posición en el curso</small>
                        </div>

                        <!-- Título -->
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_titulo" name="titulo" 
                                   required maxlength="255" placeholder="Ej: Números Enteros y Racionales">
                        </div>

                        <!-- Descripción -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                      rows="3" maxlength="500" 
                                      placeholder="Descripción breve de la unidad..."></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="add_fecha_inicio" name="fecha_inicio">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="add_fecha_fin" name="fecha_fin">
                        </div>

                        <!-- Estado -->
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="add_estado" name="estado" required>
                                <option value="BORRADOR" selected>Borrador</option>
                                <option value="PUBLICADO">Publicado</option>
                            </select>
                            <small class="text-muted">Las unidades en borrador no son visibles para estudiantes</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarUnidad">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Unidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // --- Event Handlers ---

    // 📅 Validate dates on change
    $('#add_fecha_inicio, #add_fecha_fin').on('change', function() {
        const inicio = $('#add_fecha_inicio').val();
        const fin = $('#add_fecha_fin').val();
        if (inicio && fin && inicio > fin) {
            mostrarError('La fecha de inicio no puede ser posterior a la fecha de fin');
            $(this).val('');
        }
    });

    // 🚀 Handle form submission
    $('#formAgregarUnidad').on('submit', function(e) {
        e.preventDefault();

        // Validate form before submission
        if (!validarFormularioUnidad()) {
            return;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');

        mostrarCarga();
        $('#btnGuardarUnidad').prop('disabled', true);

        $.ajax({
            url: 'modales/unidades/procesar_unidades.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarUnidad').prop('disabled', false);

                if (response.success) {
                    Swal.fire({
                        title: '¡Unidad Creada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarUnidad').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarUnidad').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // 🗑️ Clear form on modal close
    $('#modalAgregarUnidad').on('hidden.bs.modal', function() {
        $('#formAgregarUnidad')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
    });

    // --- Helper Functions ---

    /**
     * @brief Validates the unit form fields.
     * @return {boolean} True if the form is valid, false otherwise.
     */
    function validarFormularioUnidad() {
        let isValid = true;
        
        // Remove previous validation errors
        $('.is-invalid').removeClass('is-invalid');

        // 📝 Validate course
        if (!$('#add_curso_id').val()) {
            marcarError('#add_curso_id', 'Debe seleccionar un curso');
            isValid = false;
        }

        // 📝 Validate title
        const titulo = $('#add_titulo').val().trim();
        if (!titulo || titulo.length < 3) {
            marcarError('#add_titulo', 'El título debe tener al menos 3 caracteres');
            isValid = false;
        }

        // 🔢 Validate order
        const orden = parseInt($('#add_orden').val(), 10);
        if (isNaN(orden) || orden < 1) {
            marcarError('#add_orden', 'El orden debe ser un número positivo');
            isValid = false;
        }

        return isValid;
    }

    /**
     * @brief Marks a form field as invalid and shows an error message.
     * @param {string} selector - The jQuery selector for the input field.
     * @param {string} mensaje - The error message to display.
     */
    function marcarError(selector, mensaje) {
        $(selector).addClass('is-invalid');
        Swal.fire({
            title: 'Error de Validación',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            timer: 3000 // Increased timer for better readability
        });
    }

    /**
     * @brief Displays an error using Swal.
     * @param {string} mensaje - The error message to display.
     */
    function mostrarError(mensaje) {
        Swal.fire({
            title: 'Error',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            timer: 3000
        });
    }

    // You also need to define `mostrarCarga()` and `ocultarCarga()` functions
    // For this example, we'll provide simple stubs.
    function mostrarCarga() {
        // Implement your loading indicator logic here
        // e.g., show a spinner or a loading message
        console.log("Cargando...");
    }

    function ocultarCarga() {
        // Implement your logic to hide the loading indicator
        console.log("Carga completa.");
    }
});
</script>