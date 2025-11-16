<?php
require_once 'vendor/autoload.php';
require_once '../conexion/bd.php';
use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Lima');

try {
    // Obtener datos del sistema desde colegio_principal
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto, direccion, refran FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    
    $config = [
        'nombre_institucion' => $colegio['nombre'] ?? 'Sistema AAC',
        'ruc' => $colegio['ruc'] ?? '',
        'imagen' => $colegio['foto'] ?? 'assets/favicons/favicon-32x32.png',
        'direccion' => $colegio['direccion'] ?? '',
        'refran' => $colegio['refran'] ?? ''
    ];
    
    // Construir ruta completa de la imagen
    $rutaImagen = '../../' . $config['imagen'];
    
    // Convertir imagen a base64 para DomPDF
    $imagenBase64 = '';
    if (file_exists($rutaImagen)) {
        $imageData = file_get_contents($rutaImagen);
        $imagenBase64 = 'data:image/' . pathinfo($rutaImagen, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);
    } else {
        $imagenBase64 = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#667eea"/><text x="50%" y="50%" fill="white" text-anchor="middle" dy=".3em" font-size="20">AAC</text></svg>');
    }
    
    // Obtener rol del usuario desde sesión
    session_start();
    $nombreRol = isset($_SESSION['username']) ? $_SESSION['username'] : "Usuario del Sistema";
    
    // Obtener los datos filtrados desde POST
    $datosDocentes = isset($_POST['datosDocentes']) ? json_decode($_POST['datosDocentes'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosDocentes)) {
        die("<script>alert('No hay registros de docentes disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_docentes = count($datosDocentes);
    $docentes_activos = 0;
    $docentes_inactivos = 0;
    $docentes_con_usuario = 0;
    $docentes_con_asignaciones = 0;
    
    $categorias_count = [];
    $contratos_count = [];
    $especialidades_count = [];
    $grados_academicos_count = [];
    $anos_experiencia_total = 0;
    $anos_experiencia_count = 0;
    
    foreach ($datosDocentes as $row) {
        // Estado (columna 6)
        $estado = trim($row[6]);
        if (strpos($estado, 'Activo') !== false) {
            $docentes_activos++;
        } else {
            $docentes_inactivos++;
        }
        
        // Usuario (columna 5)
        $usuario = trim($row[5]);
        if (!empty($usuario) && strpos($usuario, 'Sin usuario') === false) {
            $docentes_con_usuario++;
        }
        
        // Asignaciones (columna 4)
        $asignaciones_texto = trim($row[4]);
        if (preg_match('/^(\d+)/', $asignaciones_texto, $matches)) {
            $num_asignaciones = intval($matches[1]);
            if ($num_asignaciones > 0) {
                $docentes_con_asignaciones++;
            }
        }
        
        // Datos Laborales (columna 3)
        $datos_laborales = trim($row[3]);
        
        // Categoría
        if (preg_match('/Cat\.\s*([IVX]+)/', $datos_laborales, $matches)) {
            $categoria = 'Categoría ' . $matches[1];
            if (!isset($categorias_count[$categoria])) {
                $categorias_count[$categoria] = 0;
            }
            $categorias_count[$categoria]++;
        }
        
        // Tipo de contrato
        if (preg_match('/(NOMBRADO|CONTRATADO)/', $datos_laborales, $matches)) {
            $contrato = $matches[1];
            if (!isset($contratos_count[$contrato])) {
                $contratos_count[$contrato] = 0;
            }
            $contratos_count[$contrato]++;
        }
        
        // Años de experiencia
        if (preg_match('/(\d+)\s+años/', $datos_laborales, $matches)) {
            $anos_experiencia_total += intval($matches[1]);
            $anos_experiencia_count++;
        }
        
        // Especialidades (columna 2)
        $especialidades = trim($row[2]);
        if (!empty($especialidades) && strpos($especialidades, 'Sin especialidades') === false) {
            // Extraer especialidades individuales
            preg_match_all('/([A-Za-záéíóúÁÉÍÓÚñÑ\s]+)(?=\n|$)/', $especialidades, $matches);
            foreach ($matches[0] as $esp) {
                $esp_limpia = trim($esp);
                if (!empty($esp_limpia) && !preg_match('/^Esp:/', $esp_limpia)) {
                    if (!isset($especialidades_count[$esp_limpia])) {
                        $especialidades_count[$esp_limpia] = 0;
                    }
                    $especialidades_count[$esp_limpia]++;
                }
            }
        }
        
        // Grados Académicos (columna 1)
        $datos_profesionales = trim($row[1]);
        $lineas = explode("\n", $datos_profesionales);
        if (isset($lineas[0])) {
            $grado = trim($lineas[0]);
            if (!empty($grado) && $grado !== 'No especificado') {
                if (!isset($grados_academicos_count[$grado])) {
                    $grados_academicos_count[$grado] = 0;
                }
                $grados_academicos_count[$grado]++;
            }
        }
    }
    
    $promedio_experiencia = $anos_experiencia_count > 0 ? 
        round($anos_experiencia_total / $anos_experiencia_count, 1) : 0;
    
    // Top especialidades
    arsort($especialidades_count);
    $top_especialidades = array_slice($especialidades_count, 0, 5, true);
    
} catch (PDOException $e) {
    die("<script>alert('Error al obtener datos: " . $e->getMessage() . "'); window.close();</script>");
}

// Estilos CSS del PDF
$html = '<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #333;
        margin: 0;
        padding: 0;
    }

    #tabla-cabecera, #tabla-docentes {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    #tabla-cabecera {
        text-align: center;
        letter-spacing: 0.5px;
        color: #333;
    }

    #tabla-cabecera h3 {
        font-size: 18px;
        margin-bottom: 2px;
        color: #444;
    }

    .logo-empresa {
        border: 1px solid #666;
        border-radius: 20px;
        text-align: center;
        padding: 12px;
        display: inline-block;
    }

    .info-empresa {
        font-size: 13px;
        color: #666;
    }

    .reporte-titulo {
        border: 1px solid #666;
        border-radius: 20px;
        text-align: center;
        padding: 12px;
        display: inline-block;
        background-color: #f8f9fa;
    }

    #tabla-docentes td, #tabla-docentes th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-docentes th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }

    .seccion-titulo {
        background-color: #f2f2f2;
        padding: 15px;
        margin: 25px 0 15px 0;
        font-weight: bold;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
    }

    .pie-pagina {
        margin-top: 25px;
        padding: 12px;
        font-size: 11px;
        border: 0.5px solid #333;
        border-radius: 10px;
        text-align: center;
        background-color: #f8f9fa;
    }

    .estadisticas-resumen {
        background-color: #e9ecef;
        padding: 10px;
        margin: 15px 0;
        border-radius: 5px;
        border: 1px solid #666;
    }

    .estadisticas-resumen table {
        width: 100%;
        border-collapse: collapse;
    }

    .estadisticas-resumen td {
        padding: 5px;
        font-size: 8px;
        text-align: center;
        border-right: 1px solid #999;
    }

    .estadisticas-resumen td:last-child {
        border-right: none;
    }

    .stat-numero {
        font-size: 12px;
        font-weight: bold;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 7px;
        color: #666;
    }

    .badge-categoria {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .cat-i { background-color: #dc3545; }
    .cat-ii { background-color: #ffc107; color: #856404; }
    .cat-iii { background-color: #17a2b8; }
    .cat-iv { background-color: #28a745; }

    .badge-contrato {
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
        background-color: #6c757d;
        color: white;
    }

    .badge-especialidad {
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 5px;
        font-weight: bold;
        background-color: #A8D8EA;
        color: #333;
        margin: 1px;
        display: inline-block;
    }

    .badge-experiencia {
        padding: 2px 4px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        background-color: #C7CEEA;
        color: #333;
    }

    .badge-estado {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .estado-activo { background-color: #28a745; }
    .estado-inactivo { background-color: #dc3545; }

    .col-docente { width: 18%; }
    .col-profesionales { width: 15%; }
    .col-especialidades { width: 15%; }
    .col-laborales { width: 15%; }
    .col-asignaciones { width: 10%; }
    .col-usuario { width: 12%; }
    .col-estado { width: 8%; }

    .docente-codigo {
        font-size: 6px;
        color: #6c757d;
        font-family: monospace;
    }

    .docente-documento {
        font-size: 5px;
        color: #6c757d;
    }

    .universidad-info {
        font-size: 6px;
        color: #495057;
        background-color: #f8f9fa;
        padding: 2px;
        margin-top: 2px;
    }

    .asignaciones-box {
        text-align: center;
        background-color: #e7f3ff;
        padding: 3px;
        border-radius: 3px;
    }

    .asignaciones-numero {
        font-size: 10px;
        font-weight: bold;
        color: #0d6efd;
    }

    .resumen-estadisticas {
        background-color: #e8f5e9;
        border: 1px solid #4caf50;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 9px;
        text-align: center;
        color: #2e7d32;
    }

    .top-especialidades {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 7px;
    }

    .top-titulo {
        font-weight: bold;
        color: #856404;
        margin-bottom: 5px;
        font-size: 8px;
    }
</style>';

// **Cabecera del reporte**
$html .= '<table id="tabla-cabecera">
    <tr>
        <td class="logo-empresa" style="width: 30%;">
            <img src="' . $imagenBase64 . '" alt="Logo" style="max-width: 100px; max-height: 100px; object-fit: contain;">
        </td>
        <td style="width: 40%;">
            <h3>' . htmlspecialchars($config['nombre_institucion']) . '</h3>
            <div class="info-empresa">' . htmlspecialchars($config['refran']) . '</div>
            <div class="info-empresa">RUC: ' . htmlspecialchars($config['ruc']) . '</div>
            <div class="info-empresa">' . htmlspecialchars($config['direccion']) . '</div>
        </td>
        <td style="width: 30%;">
            <div class="reporte-titulo">
                <h4>GESTIÓN DE DOCENTES</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Resumen general**
$html .= '<div class="resumen-estadisticas">';
$html .= 'PLANA DOCENTE: ' . $total_docentes . ' docentes | ';
$html .= 'ACTIVOS: ' . $docentes_activos . ' | ';
$html .= 'CON USUARIO: ' . $docentes_con_usuario . ' | ';
$html .= 'EXPERIENCIA PROMEDIO: ' . $promedio_experiencia . ' años';
$html .= '</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_docentes . '</span>
                <span class="stat-label">Total Docentes</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $docentes_activos . '</span>
                <span class="stat-label">Activos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #dc3545;">' . $docentes_inactivos . '</span>
                <span class="stat-label">Inactivos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #0d6efd;">' . $docentes_con_usuario . '</span>
                <span class="stat-label">Con Usuario</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #17a2b8;">' . $docentes_con_asignaciones . '</span>
                <span class="stat-label">Con Asignaciones</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . count($categorias_count) . '</span>
                <span class="stat-label">Categorías</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . count($especialidades_count) . '</span>
                <span class="stat-label">Especialidades</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . $promedio_experiencia . '</span>
                <span class="stat-label">Años Exp. Prom.</span>
            </td>
        </tr>
    </table>
</div>';

// **Top Especialidades**
if (!empty($top_especialidades)) {
    $html .= '<div class="top-especialidades">';
    $html .= '<div class="top-titulo">📚 TOP 5 ESPECIALIDADES MÁS FRECUENTES</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    $html .= '<tr>';
    
    $contador = 0;
    foreach ($top_especialidades as $especialidad => $cantidad) {
        if ($contador > 0 && $contador % 5 == 0) {
            $html .= '</tr><tr>';
        }
        $html .= '<td style="padding: 3px; text-align: center; border-right: 1px solid #ddd;">';
        $html .= '<strong>' . htmlspecialchars($especialidad) . ':</strong> ' . $cantidad;
        $html .= '</td>';
        $contador++;
    }
    
    $html .= '</tr></table>';
    $html .= '</div>';
}

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE PERSONAL DOCENTE</div>';
$html .= '<table id="tabla-docentes">
    <thead>
        <tr>
            <th class="col-docente">Docente</th>
            <th class="col-profesionales">Datos Profesionales</th>
            <th class="col-especialidades">Especialidades</th>
            <th class="col-laborales">Datos Laborales</th>
            <th class="col-asignaciones">Asignaciones</th>
            <th class="col-usuario">Usuario</th>
            <th class="col-estado">Estado</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosDocentes as $row) {
    // Extraer datos de cada columna
    $docente_info = trim($row[0]);
    $datos_profesionales = trim($row[1]);
    $especialidades = trim($row[2]);
    $datos_laborales = trim($row[3]);
    $asignaciones = trim($row[4]);
    $usuario = trim($row[5]);
    $estado = trim($row[6]);
    
    // Procesar docente (nombre + código + documento)
    $lineas_docente = explode("\n", $docente_info);
    $nombre_docente = isset($lineas_docente[0]) ? trim($lineas_docente[0]) : '';
    $codigo_docente = isset($lineas_docente[1]) ? trim($lineas_docente[1]) : '';
    $documento = isset($lineas_docente[2]) ? trim($lineas_docente[2]) : '';
    
    // Procesar datos profesionales
    $lineas_prof = explode("\n", $datos_profesionales);
    $grado_academico = isset($lineas_prof[0]) ? trim($lineas_prof[0]) : '';
    $universidad = isset($lineas_prof[1]) ? trim($lineas_prof[1]) : '';
    $colegiatura = '';
    if (preg_match('/Col:\s*(.+)/', $datos_profesionales, $matches)) {
        $colegiatura = trim($matches[1]);
    }
    
    // Procesar especialidades
    $especialidades_html = '';
    if (!empty($especialidades) && strpos($especialidades, 'Sin especialidades') === false) {
        $lineas_esp = explode("\n", $especialidades);
        foreach ($lineas_esp as $esp) {
            $esp_limpia = trim($esp);
            if (!empty($esp_limpia) && !preg_match('/^Esp:/', $esp_limpia)) {
                $especialidades_html .= '<span class="badge-especialidad">' . htmlspecialchars($esp_limpia) . '</span> ';
            }
        }
    } else {
        $especialidades_html = '<span style="font-size: 6px; color: #6c757d;">Sin especialidades</span>';
    }
    
    // Procesar datos laborales
    $categoria_html = '';
    if (preg_match('/Cat\.\s*([IVX]+)/', $datos_laborales, $matches)) {
        $cat_num = $matches[1];
        $cat_class = 'cat-i';
        if ($cat_num === 'II') $cat_class = 'cat-ii';
        elseif ($cat_num === 'III') $cat_class = 'cat-iii';
        elseif ($cat_num === 'IV') $cat_class = 'cat-iv';
        
        $categoria_html = '<span class="badge-categoria ' . $cat_class . '">Cat. ' . $cat_num . '</span>';
    }
    
    $contrato_html = '';
    if (preg_match('/(NOMBRADO|CONTRATADO)/', $datos_laborales, $matches)) {
        $contrato_html = '<br><span class="badge-contrato">' . $matches[1] . '</span>';
    }
    
    $experiencia_html = '';
    if (preg_match('/(\d+)\s+años/', $datos_laborales, $matches)) {
        $experiencia_html = '<br><span class="badge-experiencia">' . $matches[1] . ' años</span>';
    }
    
    // Procesar asignaciones
    $asignaciones_html = '<div class="asignaciones-box">';
    if (preg_match('/^(\d+)/', $asignaciones, $matches)) {
        $num_asignaciones = $matches[1];
        $asignaciones_html .= '<div class="asignaciones-numero">' . $num_asignaciones . '</div>';
        
        if (preg_match('/(\d+)\s+secciones/', $asignaciones, $matches2)) {
            $asignaciones_html .= '<small style="font-size: 5px;">' . $matches2[1] . ' secciones</small>';
        }
    } else {
        $asignaciones_html .= '<div style="font-size: 6px; color: #6c757d;">Sin asignaciones</div>';
    }
    $asignaciones_html .= '</div>';
    
    // Procesar usuario
    $usuario_html = '';
    if (!empty($usuario) && strpos($usuario, 'Sin usuario') === false) {
        $lineas_usuario = explode("\n", $usuario);
        $estado_usuario = isset($lineas_usuario[0]) ? trim($lineas_usuario[0]) : '';
        $username = isset($lineas_usuario[1]) ? trim($lineas_usuario[1]) : '';
        
        $usuario_class = strpos($estado_usuario, 'Activo') !== false ? 'estado-activo' : 'estado-inactivo';
        $usuario_html = '<span class="badge-estado ' . $usuario_class . '">' . htmlspecialchars($estado_usuario) . '</span>';
        if (!empty($username)) {
            $usuario_html .= '<br><small style="font-size: 5px;">' . htmlspecialchars($username) . '</small>';
        }
    } else {
        $usuario_html = '<span style="font-size: 6px; color: #856404;">Sin usuario</span>';
    }
    
    // Procesar estado
    $estado_class = strpos($estado, 'Activo') !== false ? 'estado-activo' : 'estado-inactivo';
    $estado_html = '<span class="badge-estado ' . $estado_class . '">' . htmlspecialchars($estado) . '</span>';
    
    $html .= '<tr>
        <td class="col-docente" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_docente) . '</strong><br>
            <span class="docente-codigo">' . htmlspecialchars($codigo_docente) . '</span><br>
            <span class="docente-documento">' . htmlspecialchars($documento) . '</span>
        </td>
        <td class="col-profesionales" style="font-size: 6px;">
            <strong>' . htmlspecialchars($grado_academico) . '</strong><br>
            <div class="universidad-info">' . htmlspecialchars($universidad) . '</div>';
    
    if (!empty($colegiatura)) {
        $html .= '<span style="font-size: 5px; color: #17a2b8;">Col: ' . htmlspecialchars($colegiatura) . '</span>';
    }
    
    $html .= '</td>
        <td class="col-especialidades">' . $especialidades_html . '</td>
        <td class="col-laborales" style="text-align: center;">' . $categoria_html . $contrato_html . $experiencia_html . '</td>
        <td class="col-asignaciones">' . $asignaciones_html . '</td>
        <td class="col-usuario" style="text-align: center;">' . $usuario_html . '</td>
        <td class="col-estado" style="text-align: center;">' . $estado_html . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <strong>Total Docentes:</strong> ' . $total_docentes . ' | 
    <strong>Activos:</strong> ' . $docentes_activos . ' | 
    <strong>Con Asignaciones:</strong> ' . $docentes_con_asignaciones . ' | 
    <strong>Experiencia Promedio:</strong> ' . $promedio_experiencia . ' años
</div>';

// **Configurar DomPDF**
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// **Enviar PDF al navegador**
$filename = 'Reporte_Docentes_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>