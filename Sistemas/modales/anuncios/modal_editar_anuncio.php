<!-- Modal Editar Anuncio -->
<div class="modal fade" id="modalEditarAnuncio" tabindex="-1" aria-labelledby="modalEditarAnuncioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalEditarAnuncioLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Anuncio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarAnuncio" method="POST">
                <input type="hidden" id="edit_anuncio_id" name="anuncio_id">
                
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
                                            <label for="edit_curso_id" class="form-label">
                                                Curso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_curso_id" name="curso_id" required>
                                                <option value="">Seleccionar curso</option>
                                                <?php foreach ($cursos_activos as $curso): ?>
                                                    <option value="<?= $curso['id'] ?>">
                                                        <?= htmlspecialchars($curso['nombre']) ?> 
                                                        (<?= $curso['grado'] ?> - <?= $curso['seccion'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_titulo" class="form-label">
                                                Título del Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                                   placeholder="Título descriptivo del anuncio" required 
                                                   maxlength="255" minlength="5">
                                            <div id="contador_titulo_edit" class="form-text text-end">0/255</div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="edit_contenido" class="form-label">
                                                Contenido del Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="edit_contenido" name="contenido" 
                                                      rows="6" required minlength="10" maxlength="5000"></textarea>
                                            <div id="contador_contenido_edit" class="form-text text-end">0/5000</div>
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
                                            <label for="edit_tipo" class="form-label">
                                                Tipo de Anuncio <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="INFORMATIVO">Informativo</option>
                                                <option value="RECORDATORIO">Recordatorio</option>
                                                <option value="URGENTE">Urgente</option>
                                                <option value="EVENTO">Evento</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="edit_prioridad" class="form-label">
                                                Prioridad <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_prioridad" name="prioridad" required>
                                                <option value="">Seleccionar prioridad</option>
                                                <option value="BAJA">Baja</option>
                                                <option value="NORMAL">Normal</option>
                                                <option value="ALTA">Alta</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="edit_destinatario" class="form-label">
                                                Destinatario <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_destinatario" name="destinatario" required>
                                                <option value="">Seleccionar destinatario</option>
                                                <option value="ESTUDIANTES">Estudiantes</option>
                                                <option value="APODERADOS">Apoderados</option>
                                                <option value="TODOS">Todos</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_fecha_publicacion" class="form-label">
                                                Fecha de Publicación <span class="text-danger">*</span>
                                            </label>
                                            <input type="datetime-local" class="form-control" 
                                                   id="edit_fecha_publicacion" name="fecha_publicacion" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="edit_fecha_expiracion" class="form-label">
                                                Fecha de Expiración (Opcional)
                                            </label>
                                            <input type="datetime-local" class="form-control" 
                                                   id="edit_fecha_expiracion" name="fecha_expiracion">
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
                    <button type="submit" class="btn btn-primary" id="btnActualizarAnuncio">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Anuncio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>