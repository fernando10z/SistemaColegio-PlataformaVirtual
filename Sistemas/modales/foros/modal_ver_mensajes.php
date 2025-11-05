<!-- Modal Ver Mensajes -->
<div class="modal fade" id="modalVerMensajes" tabindex="-1" aria-labelledby="modalVerMensajesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div>
                    <h5 class="modal-title mb-0" id="modalVerMensajesLabel">
                        <i class="ti ti-messages me-2"></i>
                        <span id="titulo_foro_mensajes">Mensajes del Foro</span>
                    </h5>
                    <small class="opacity-75" id="curso_foro_mensajes"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <!-- Estadísticas del Foro -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center py-2">
                                <div class="fs-5 fw-bold" id="total_mensajes_foro">0</div>
                                <small>Total Mensajes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center py-2">
                                <div class="fs-5 fw-bold" id="total_participantes_foro">0</div>
                                <small>Participantes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center py-2">
                                <div class="fs-6" id="ultimo_mensaje_foro">-</div>
                                <small>Último Mensaje</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Mensajes -->
                <div id="listaMensajes" class="mb-4">
                    <!-- Se cargará dinámicamente -->
                </div>

                <!-- Formulario Nuevo Mensaje -->
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="ti ti-message-plus me-2"></i>
                            Nuevo Mensaje
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formNuevoMensaje">
                            <input type="hidden" id="mensaje_foro_id" name="foro_id">
                            <input type="hidden" id="mensaje_padre_id" name="mensaje_padre_id">
                            
                            <div class="mb-3" id="respuesta_info" style="display: none;">
                                <div class="alert alert-info d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="ti ti-corner-down-right me-2"></i>
                                        Respondiendo a: <strong id="respuesta_a"></strong>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelarRespuesta()">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje_titulo" class="form-label">
                                    Título (Opcional)
                                </label>
                                <input type="text" class="form-control" id="mensaje_titulo" name="titulo" 
                                       placeholder="Título del mensaje" maxlength="150">
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje_contenido" class="form-label">
                                    Contenido <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="mensaje_contenido" name="contenido" 
                                          rows="4" placeholder="Escribe tu mensaje..." 
                                          required minlength="10" maxlength="2000"></textarea>
                                <div class="form-text">
                                    <span id="contador_mensaje">0</span>/2000 caracteres (mínimo 10)
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" id="btnEnviarMensaje">
                                    <i class="ti ti-send me-2"></i>
                                    Enviar Mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

