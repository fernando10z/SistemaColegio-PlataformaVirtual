<!-- Modal Editar Foro -->
<div class="modal fade" id="modalEditarForo" tabindex="-1" aria-labelledby="modalEditarForoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalEditarForoLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Foro de Discusión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarForo" method="POST">
                <input type="hidden" id="editar_foro_id" name="foro_id">
                
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
                                            <label for="editar_curso" class="form-label">
                                                Curso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_curso" name="curso_id" required disabled>
                                                <option value="">Seleccionar curso...</option>
                                                <?php foreach ($cursos as $curso): ?>
                                                    <option value="<?= $curso['id'] ?>">
                                                        <?= htmlspecialchars($curso['nombre_completo']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text text-warning">
                                                <i class="ti ti-alert-circle me-1"></i>
                                                El curso no puede modificarse una vez creado el foro
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="editar_titulo" class="form-label">
                                                Título del Foro <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="editar_titulo" name="titulo" 
                                                   required minlength="5" maxlength="255">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <label for="editar_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="editar_descripcion" name="descripcion" 
                                                      rows="4" required minlength="20" maxlength="1000"></textarea>
                                            <div class="form-text">
                                                <span id="contador_editar">0</span>/1000 caracteres (mínimo 20)
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
                                            <label for="editar_tipo" class="form-label">
                                                Tipo de Foro <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo...</option>
                                                <option value="GENERAL">General</option>
                                                <option value="PREGUNTA_RESPUESTA">Pregunta y Respuesta</option>
                                                <option value="DEBATE">Debate</option>
                                                <option value="ANUNCIO">Anuncio</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_estado" class="form-label">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_estado" name="estado" required>
                                                <option value="ABIERTO">Abierto</option>
                                                <option value="CERRADO">Cerrado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="editar_moderado" name="moderado">
                                                <label class="form-check-label" for="editar_moderado">
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
                        
                        <!-- Estadísticas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="fs-4 fw-bold text-primary" id="stats_mensajes">0</div>
                                            <small class="text-muted">Mensajes</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fs-4 fw-bold text-success" id="stats_participantes">0</div>
                                            <small class="text-muted">Participantes</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fs-6 text-muted" id="stats_ultimo">-</div>
                                            <small class="text-muted">Último mensaje</small>
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
                    <button type="submit" class="btn btn-primary" id="btnEditarForo">
                        <i class="ti ti-device-floppy me-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
