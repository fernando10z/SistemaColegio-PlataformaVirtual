<!-- Modal Crear Comunicación -->
<div class="modal fade" id="modalCrearComunicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <h5 class="modal-title">
                    <i class="ti ti-mail-plus me-2"></i>
                    Nueva Comunicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="formCrearComunicacion" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="crear_postulacion_id" class="form-label">
                                Postulación <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="crear_postulacion_id" name="postulacion_id" required>
                                <option value="">Seleccionar postulación...</option>
                                <?php foreach ($postulaciones_disponibles as $post): 
                                    $datos_post = json_decode($post['datos_postulante'], true);
                                ?>
                                    <option value="<?= $post['id'] ?>">
                                        <?= $post['codigo_postulacion'] ?> - 
                                        <?= htmlspecialchars($datos_post['nombres'] ?? '') ?> 
                                        <?= htmlspecialchars($datos_post['apellidos'] ?? '') ?>
                                        (<?= $post['grado_solicitado'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="crear_tipo" class="form-label">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="crear_tipo" name="tipo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="EMAIL">Email</option>
                                <option value="WHATSAPP">WhatsApp</option>
                                <option value="SMS">SMS</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="crear_destinatario" class="form-label">
                                Destinatario <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="crear_destinatario" name="destinatario" 
                                   placeholder="email@ejemplo.com" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="crear_asunto" class="form-label">
                                Asunto <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="crear_asunto" name="asunto" 
                                   placeholder="Asunto de la comunicación" required maxlength="200">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="crear_mensaje" class="form-label">
                                Mensaje <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="crear_mensaje" name="mensaje" rows="5" 
                                      placeholder="Contenido del mensaje" required maxlength="1000"></textarea>
                            <div class="form-text">Variables disponibles: {nombre}, {codigo}, {grado}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="crear_prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="crear_prioridad" name="prioridad">
                                <option value="NORMAL">Normal</option>
                                <option value="ALTA">Alta</option>
                                <option value="URGENTE">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="crear_enviar_inmediato" name="enviar_inmediato" checked>
                                <label class="form-check-label" for="crear_enviar_inmediato">
                                    Enviar inmediatamente
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnCrearComunicacion">
                        <i class="ti ti-send me-2"></i>Crear y Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#formCrearComunicacion').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('accion', 'crear');
    
    mostrarCarga();
    $('#btnCrearComunicacion').prop('disabled', true);

    $.ajax({
        url: 'modales/comunicaciones/procesar_comunicaciones.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            $('#btnCrearComunicacion').prop('disabled', false);
            
            if (response.success) {
                Swal.fire({
                    title: '¡Comunicación Creada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    $('#modalCrearComunicacion').modal('hide');
                    location.reload();
                });
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarCarga();
            $('#btnCrearComunicacion').prop('disabled', false);
            mostrarError('Error al procesar la solicitud');
        }
    });
});

$('#crear_tipo').on('change', function() {
    const tipo = $(this).val();
    const destinatarioInput = $('#crear_destinatario');
    
    if (tipo === 'EMAIL') {
        destinatarioInput.attr('type', 'email').attr('placeholder', 'email@ejemplo.com');
    } else if (tipo === 'WHATSAPP' || tipo === 'SMS') {
        destinatarioInput.attr('type', 'tel').attr('placeholder', '999123456');
    }
});
</script>