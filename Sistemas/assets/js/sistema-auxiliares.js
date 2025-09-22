// assets/js/sistema-auxiliares.js
// Funciones auxiliares para el sistema AAC

/**
 * Mostrar overlay de carga
 */
function mostrarCarga() {
    $('#loadingOverlay').css('display', 'flex');
}

/**
 * Ocultar overlay de carga
 */
function ocultarCarga() {
    $('#loadingOverlay').hide();
}

/**
 * Mostrar mensaje de error con SweetAlert2
 * @param {string} mensaje - Mensaje de error a mostrar
 */
function mostrarError(mensaje) {
    Swal.fire({
        title: 'Error',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Entendido'
    });
}

/**
 * Mostrar mensaje de éxito con SweetAlert2
 * @param {string} mensaje - Mensaje de éxito a mostrar
 */
function mostrarExito(mensaje) {
    Swal.fire({
        title: '¡Éxito!',
        text: mensaje,
        icon: 'success',
        confirmButtonColor: '#198754',
        timer: 2000,
        showConfirmButton: false
    });
}

/**
 * Mostrar mensaje de información
 * @param {string} titulo - Título del mensaje
 * @param {string} mensaje - Contenido del mensaje
 */
function mostrarInfo(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'info',
        confirmButtonColor: '#0d6efd'
    });
}

/**
 * Mostrar mensaje de advertencia
 * @param {string} titulo - Título del mensaje
 * @param {string} mensaje - Contenido del mensaje
 */
function mostrarAdvertencia(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'warning',
        confirmButtonColor: '#ffc107'
    });
}

/**
 * Confirmar acción con SweetAlert2
 * @param {string} titulo - Título de la confirmación
 * @param {string} mensaje - Mensaje de confirmación
 * @param {function} callback - Función a ejecutar si se confirma
 * @param {string} tipoBoton - Tipo de botón (danger, success, etc.)
 */
function confirmarAccion(titulo, mensaje, callback, tipoBoton = 'danger') {
    const colores = {
        'danger': '#dc3545',
        'success': '#198754',
        'warning': '#ffc107',
        'info': '#0d6efd'
    };

    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: colores[tipoBoton] || '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
}

/**
 * Generar contraseña aleatoria
 * @param {number} longitud - Longitud de la contraseña (por defecto 12)
 * @returns {string} Contraseña generada
 */
function generarPassword(longitud = 12) {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&*';
    let password = '';
    
    // Asegurar al menos un carácter de cada tipo
    const mayuscula = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const minuscula = 'abcdefghijklmnopqrstuvwxyz';
    const numero = '0123456789';
    const especial = '@#$%&*';
    
    password += mayuscula.charAt(Math.floor(Math.random() * mayuscula.length));
    password += minuscula.charAt(Math.floor(Math.random() * minuscula.length));
    password += numero.charAt(Math.floor(Math.random() * numero.length));
    password += especial.charAt(Math.floor(Math.random() * especial.length));
    
    // Completar el resto de la contraseña
    for (let i = password.length; i < longitud; i++) {
        password += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    
    // Mezclar los caracteres
    return password.split('').sort(() => 0.5 - Math.random()).join('');
}

/**
 * Formatear fecha para mostrar
 * @param {string} fecha - Fecha en formato ISO o similar
 * @param {boolean} incluirHora - Si incluir la hora en el formato
 * @returns {string} Fecha formateada
 */
function formatearFecha(fecha, incluirHora = false) {
    if (!fecha) return '-';
    
    const fechaObj = new Date(fecha);
    const opciones = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };
    
    if (incluirHora) {
        opciones.hour = '2-digit';
        opciones.minute = '2-digit';
    }
    
    return fechaObj.toLocaleDateString('es-PE', opciones);
}

/**
 * Validar email
 * @param {string} email - Email a validar
 * @returns {boolean} True si es válido
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar DNI peruano
 * @param {string} dni - DNI a validar
 * @returns {boolean} True si es válido
 */
function validarDNI(dni) {
    return /^[0-9]{8}$/.test(dni);
}

/**
 * Validar teléfono
 * @param {string} telefono - Teléfono a validar
 * @returns {boolean} True si es válido
 */
function validarTelefono(telefono) {
    // Formato: 999-123456 o 999123456 o +51999123456
    const regex = /^(\+51|0051)?[0-9]{3}[-]?[0-9]{6}$/;
    return regex.test(telefono);
}

/**
 * Limpiar formulario y errores
 * @param {string} formularioId - ID del formulario a limpiar
 */
function limpiarFormulario(formularioId) {
    $(formularioId)[0].reset();
    $(formularioId + ' .is-invalid').removeClass('is-invalid');
    $(formularioId + ' .invalid-feedback').remove();
}

/**
 * Mostrar error en campo específico
 * @param {string} campo - Selector del campo
 * @param {string} mensaje - Mensaje de error
 */
function mostrarErrorCampo(campo, mensaje) {
    $(campo).addClass('is-invalid');
    $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
}

/**
 * Capitalizar primera letra
 * @param {string} texto - Texto a capitalizar
 * @returns {string} Texto capitalizado
 */
function capitalize(texto) {
    if (!texto) return '';
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
}

/**
 * Formatear número con separadores de miles
 * @param {number} numero - Número a formatear
 * @returns {string} Número formateado
 */
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-PE').format(numero);
}

/**
 * Debounce para funciones de búsqueda
 * @param {function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en ms
 * @returns {function} Función con debounce
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Truncar texto con puntos suspensivos
 * @param {string} texto - Texto a truncar
 * @param {number} longitud - Longitud máxima
 * @returns {string} Texto truncado
 */
function truncarTexto(texto, longitud = 50) {
    if (!texto) return '';
    if (texto.length <= longitud) return texto;
    return texto.substring(0, longitud) + '...';
}

/**
 * Validar archivo subido
 * @param {File} archivo - Archivo a validar
 * @param {Array} tiposPermitidos - Tipos MIME permitidos
 * @param {number} tamañoMaxMB - Tamaño máximo en MB
 * @returns {Object} {valido: boolean, mensaje: string}
 */
function validarArchivo(archivo, tiposPermitidos = [], tamañoMaxMB = 2) {
    if (!archivo) {
        return { valido: false, mensaje: 'No se ha seleccionado ningún archivo' };
    }
    
    // Validar tipo
    if (tiposPermitidos.length > 0 && !tiposPermitidos.includes(archivo.type)) {
        return { valido: false, mensaje: 'Tipo de archivo no permitido' };
    }
    
    // Validar tamaño
    const tamañoMaxBytes = tamañoMaxMB * 1024 * 1024;
    if (archivo.size > tamañoMaxBytes) {
        return { valido: false, mensaje: `El archivo no debe superar los ${tamañoMaxMB}MB` };
    }
    
    return { valido: true, mensaje: 'Archivo válido' };
}

/**
 * Exportar tabla a Excel (requiere SheetJS)
 * @param {string} tableId - ID de la tabla
 * @param {string} fileName - Nombre del archivo
 */
function exportarTablaExcel(tableId, fileName = 'datos') {
    try {
        const table = document.getElementById(tableId);
        const workbook = XLSX.utils.table_to_book(table);
        XLSX.writeFile(workbook, `${fileName}.xlsx`);
    } catch (error) {
        mostrarError('Error al exportar: Biblioteca de exportación no disponible');
    }
}

/**
 * Copiar texto al portapapeles
 * @param {string} texto - Texto a copiar
 */
function copiarAlPortapapeles(texto) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarExito('Texto copiado al portapapeles');
        }).catch(() => {
            mostrarError('Error al copiar al portapapeles');
        });
    } else {
        // Fallback para navegadores más antiguos
        const textArea = document.createElement('textarea');
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            mostrarExito('Texto copiado al portapapeles');
        } catch (err) {
            mostrarError('Error al copiar al portapapeles');
        }
        document.body.removeChild(textArea);
    }
}

/**
 * Inicializar tooltips de Bootstrap
 */
function inicializarTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializar componentes al cargar la página
 */
$(document).ready(function() {
    // Inicializar tooltips
    inicializarTooltips();
    
    // Manejar errores AJAX globalmente
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        if (xhr.status === 401) {
            mostrarError('Sesión expirada. Por favor, inicie sesión nuevamente.');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else if (xhr.status === 403) {
            mostrarError('No tiene permisos para realizar esta acción.');
        } else if (xhr.status === 500) {
            mostrarError('Error interno del servidor. Contacte al administrador.');
        }
        
        // Ocultar carga en caso de error
        ocultarCarga();
    });
    
    // Auto-ocultar alertas después de 5 segundos
    $('.alert[data-auto-dismiss="true"]').each(function() {
        const alert = $(this);
        setTimeout(() => {
            alert.fadeOut();
        }, 5000);
    });
});

// Exportar funciones si se usa como módulo
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        mostrarCarga,
        ocultarCarga,
        mostrarError,
        mostrarExito,
        mostrarInfo,
        mostrarAdvertencia,
        confirmarAccion,
        generarPassword,
        formatearFecha,
        validarEmail,
        validarDNI,
        validarTelefono,
        limpiarFormulario,
        mostrarErrorCampo,
        capitalize,
        formatearNumero,
        debounce,
        truncarTexto,
        validarArchivo,
        exportarTablaExcel,
        copiarAlPortapapeles
    };
}