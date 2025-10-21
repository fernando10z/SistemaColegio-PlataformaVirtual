<!-- modales/transporte/modal_asignar_estudiantes.php -->
<div class="modal fade" id="modalAsignarEstudiantes" tabindex="-1" aria-labelledby="modalAsignarEstudiantesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFD4B4 0%, #FFE5C9 100%);">
                <h5 class="modal-title" id="modalAsignarEstudiantesLabel">
                    <i class="ti ti-users me-2"></i>
                    Asignar Estudiantes a la Ruta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignarEstudiantes" method="POST">
                <input type="hidden" id="rutaIdEstudiantes" name="ruta_id">
                
                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-info-circle fs-4 me-2"></i>
                                    <div>
                                        <strong>Ruta:</strong> <span id="infoRutaNombreEst"></span><br>
                                        <small><strong>Código:</strong> <span id="infoRutaCodigoEst"></span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted d-block">Capacidad del Vehículo</small>
                                <strong class="fs-5"><span id="capacidadVehiculo">0</span> pasajeros</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card" style="background: #F8F9FA; border: 1px solid #DEE2E6; max-height: 500px; overflow-y: auto;">
                                <div class="card-header" style="background: #E9ECEF;">
                                    <h6 class="mb-0">
                                        <i class="ti ti-map-pin me-1"></i>
                                        Paraderos de la Ruta
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="paraderosListContainer"></div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-success py-2">
                                    <strong>Total Asignados:</strong> 
                                    <span id="totalAsignados" class="fs-5">0</span> estudiantes
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card" style="border: 1px solid #DEE2E6;">
                                <div class="card-header" style="background: linear-gradient(135deg, #E5F0FF 0%, #F0F5FF 100%);">
                                    <h6 class="mb-3">
                                        <i class="ti ti-users me-1"></i>
                                        Estudiantes Disponibles
                                    </h6>
                                    
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control form-control-sm" 
                                                   id="buscarEstudiante" 
                                                   placeholder="Buscar estudiante...">
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select form-select-sm" id="filtroGrado">
                                                <option value="">Todos los grados</option>
                                                <?php
                                                $grados_unicos = array_unique(array_filter(array_column($estudiantes, 'grado')));
                                                sort($grados_unicos);
                                                foreach ($grados_unicos as $grado):
                                                ?>
                                                    <option value="<?= htmlspecialchars($grado) ?>">
                                                        <?= htmlspecialchars($grado) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" 
                                                    onclick="limpiarFiltrosEstudiantes()">
                                                <i class="ti ti-refresh"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div id="estudiantesListContainer">
                                        <?php foreach ($estudiantes as $estudiante): ?>
                                            <div class="estudiante-item border rounded p-2 mb-2" 
                                                 data-id="<?= $estudiante['id'] ?>"
                                                 data-nombre="<?= strtolower($estudiante['nombre_completo']) ?>"
                                                 data-grado="<?= htmlspecialchars($estudiante['grado'] ?? '') ?>"
                                                 style="cursor: pointer; transition: all 0.2s; background: #FFFFFF;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?= htmlspecialchars($estudiante['nombre_completo']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php if (!empty($estudiante['grado'])): ?>
                                                                <?= htmlspecialchars($estudiante['grado']) ?> - 
                                                                <?= htmlspecialchars($estudiante['seccion']) ?>
                                                            <?php else: ?>
                                                                Sin matrícula
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <select class="form-select form-select-sm paradero-select" 
                                                                style="width: 150px;"
                                                                onchange="asignarEstudianteParadero(<?= $estudiante['id'] ?>, this.value, event)">
                                                            <option value="">Asignar a...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>
                        Guardar Asignaciones
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let paraderosData = [];
let asignacionesEstudiantes = {};

function cargarDatosAsignacionEstudiantes(ruta) {
    document.getElementById('rutaIdEstudiantes').value = ruta.id;
    document.getElementById('infoRutaNombreEst').textContent = ruta.nombre;
    document.getElementById('infoRutaCodigoEst').textContent = ruta.codigo_ruta;
    
    const capacidad = ruta.capacidad || 0;
    document.getElementById('capacidadVehiculo').textContent = capacidad;
    
    try {
        paraderosData = JSON.parse(ruta.paraderos || '[]');
    } catch (e) {
        paraderosData = [];
    }
    
    asignacionesEstudiantes = {};
    
    renderizarParaderos();
    actualizarSelectoresParaderos();
    cargarEstudiantesAsignados();
}

function renderizarParaderos() {
    const container = document.getElementById('paraderosListContainer');
    
    if (paraderosData.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No hay paraderos registrados</p>';
        return;
    }
    
    let html = '';
    paraderosData.forEach((paradero, index) => {
        const estudiantesAsignados = paradero.estudiantes || [];
        
        html += `
            <div class="paradero-box mb-3 p-3 border rounded" style="background: #FFFFFF;">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="mb-0">📍 ${paradero.nombre || 'Sin nombre'}</h6>
                        <small class="text-muted">${paradero.direccion || 'Sin dirección'}</small>
                    </div>
                    <span class="badge bg-primary">${estudiantesAsignados.length} estudiantes</span>
                </div>
                <div id="estudiantes-paradero-${index}" class="mt-2">
        `;
        
        if (estudiantesAsignados.length > 0) {
            estudiantesAsignados.forEach(estId => {
                const estudiante = buscarEstudiantePorId(estId);
                if (estudiante) {
                    html += `
                        <div class="estudiante-asignado d-flex justify-content-between align-items-center p-2 mb-1 border-start border-3 border-success" 
                             style="background: #E8F5E9; border-radius: 4px;">
                            <div>
                                <small><strong>${estudiante.nombre_completo}</strong></small><br>
                                <small class="text-muted">${estudiante.grado || 'Sin grado'} - ${estudiante.seccion || 'Sin sección'}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="desasignarEstudiante(${estId}, ${index})">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    `;
                }
            });
        } else {
            html += '<small class="text-muted">Sin estudiantes asignados</small>';
        }
        
        html += `</div></div>`;
    });
    
    container.innerHTML = html;
    actualizarTotalAsignados();
}

function actualizarSelectoresParaderos() {
    const selectores = document.querySelectorAll('.paradero-select');
    
    selectores.forEach(select => {
        let options = '<option value="">Asignar a...</option>';
        
        paraderosData.forEach((paradero, index) => {
            options += `<option value="${index}">${paradero.nombre || 'Paradero ' + (index + 1)}</option>`;
        });
        
        select.innerHTML = options;
    });
}

function buscarEstudiantePorId(id) {
    const estudiantesArray = <?= json_encode($estudiantes) ?>;
    return estudiantesArray.find(e => e.id == id);
}

function asignarEstudianteParadero(estudianteId, paraderoIndex, event) {
    if (paraderoIndex === '') return;
    
    const estudiante = buscarEstudiantePorId(estudianteId);
    if (!estudiante) return;
    
    const totalAsignados = obtenerTotalEstudiantesAsignados();
    const capacidad = parseInt(document.getElementById('capacidadVehiculo').textContent);
    
    if (capacidad > 0 && totalAsignados >= capacidad) {
        Swal.fire({
            title: 'Capacidad Alcanzada',
            text: 'El vehículo ha alcanzado su capacidad máxima',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        
        event.target.value = '';
        return;
    }
    
    if (estaEstudianteAsignado(estudianteId)) {
        Swal.fire({
            title: 'Estudiante Ya Asignado',
            text: 'Este estudiante ya está asignado a un paradero',
            icon: 'info',
            confirmButtonColor: '#0dcaf0'
        });
        
        event.target.value = '';
        return;
    }
    
    if (!paraderosData[paraderoIndex].estudiantes) {
        paraderosData[paraderoIndex].estudiantes = [];
    }
    
    paraderosData[paraderoIndex].estudiantes.push(parseInt(estudianteId));
    asignacionesEstudiantes[estudianteId] = parseInt(paraderoIndex);
    
    event.target.value = '';
    renderizarParaderos();
    
    Swal.fire({
        title: 'Asignado',
        text: `${estudiante.nombre_completo} asignado correctamente`,
        icon: 'success',
        timer: 1500,
        showConfirmButton: false
    });
}

function desasignarEstudiante(estudianteId, paraderoIndex) {
    if (paraderosData[paraderoIndex] && paraderosData[paraderoIndex].estudiantes) {
        paraderosData[paraderoIndex].estudiantes = paraderosData[paraderoIndex].estudiantes.filter(
            id => id != estudianteId
        );
    }
    
    delete asignacionesEstudiantes[estudianteId];
    renderizarParaderos();
}

function estaEstudianteAsignado(estudianteId) {
    return asignacionesEstudiantes.hasOwnProperty(estudianteId);
}

function obtenerTotalEstudiantesAsignados() {
    let total = 0;
    paraderosData.forEach(paradero => {
        if (paradero.estudiantes && Array.isArray(paradero.estudiantes)) {
            total += paradero.estudiantes.length;
        }
    });
    return total;
}

function actualizarTotalAsignados() {
    const total = obtenerTotalEstudiantesAsignados();
    document.getElementById('totalAsignados').textContent = total;
}

function cargarEstudiantesAsignados() {
    paraderosData.forEach((paradero, index) => {
        if (paradero.estudiantes && Array.isArray(paradero.estudiantes)) {
            paradero.estudiantes.forEach(estId => {
                asignacionesEstudiantes[estId] = index;
            });
        }
    });
}

document.getElementById('buscarEstudiante').addEventListener('keyup', filtrarEstudiantes);
document.getElementById('filtroGrado').addEventListener('change', filtrarEstudiantes);

function filtrarEstudiantes() {
    const busqueda = document.getElementById('buscarEstudiante').value.toLowerCase();
    const gradoFiltro = document.getElementById('filtroGrado').value;
    
    const items = document.querySelectorAll('.estudiante-item');
    
    items.forEach(item => {
        const nombre = item.dataset.nombre;
        const grado = item.dataset.grado;
        
        let mostrar = true;
        
        if (busqueda && !nombre.includes(busqueda)) {
            mostrar = false;
        }
        
        if (gradoFiltro && grado !== gradoFiltro) {
            mostrar = false;
        }
        
        item.style.display = mostrar ? 'block' : 'none';
    });
}

function limpiarFiltrosEstudiantes() {
    document.getElementById('buscarEstudiante').value = '';
    document.getElementById('filtroGrado').value = '';
    filtrarEstudiantes();
}

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('estudiantesListContainer');
    if (container) {
        container.addEventListener('mouseover', function(e) {
            const item = e.target.closest('.estudiante-item');
            if (item) {
                item.style.background = '#F0F5FF';
            }
        });
        
        container.addEventListener('mouseout', function(e) {
            const item = e.target.closest('.estudiante-item');
            if (item) {
                item.style.background = '#FFFFFF';
            }
        });
    }
});

document.getElementById('formAsignarEstudiantes').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const totalAsignados = obtenerTotalEstudiantesAsignados();
    
    if (totalAsignados === 0) {
        Swal.fire({
            title: 'Sin Asignaciones',
            text: 'Debe asignar al menos un estudiante a la ruta',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
        return;
    }
    
    const formData = new FormData(this);
    formData.append('accion', 'asignar_estudiantes');
    formData.append('paraderos_json', JSON.stringify(paraderosData));

    mostrarCarga();

    fetch('modales/transporte/procesar_transporte.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#198754',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
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
        Swal.fire({
            title: 'Error',
            text: 'Error al procesar la solicitud',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
});

document.getElementById('modalAsignarEstudiantes').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formAsignarEstudiantes').reset();
    paraderosData = [];
    asignacionesEstudiantes = {};
    document.getElementById('paraderosListContainer').innerHTML = '';
    limpiarFiltrosEstudiantes();
});
</script>

<style>
.estudiante-item {
    background: #FFFFFF;
}

.estudiante-item:hover {
    background: #F0F5FF !important;
    border-color: #B4D4FF !important;
}

.paradero-box {
    transition: all 0.3s ease;
}

.paradero-box:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.estudiante-asignado {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.paradero-select {
    border: 1px solid #D4E5FF;
}

.paradero-select:focus {
    border-color: #B4D4FF;
    box-shadow: 0 0 0 0.2rem rgba(180, 212, 255, 0.25);
}
</style>