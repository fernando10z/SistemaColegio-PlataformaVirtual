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
    $datosAreas = isset($_POST['datosAreas']) ? json_decode($_POST['datosAreas'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosAreas)) {
        die("<script>alert('No hay registros de áreas curriculares disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_areas = count($datosAreas);
    $areas_activas = 0;
    $areas_inactivas = 0;
    $areas_con_competencias = 0;
    $areas_sin_competencias = 0;
    $total_docentes = 0;
    $total_asignaciones = 0;
    $total_competencias = 0;
    
    $niveles_atendidos = [];
    
    foreach ($datosAreas as $row) {
        // Contadores de estado (columna 4)
        $estado = trim($row[4]);
        if ($estado === 'ACTIVA') {
            $areas_activas++;
        } else {
            $areas_inactivas++;
        }
        
        // Contadores de competencias (columna 2)
        $competencias_texto = trim($row[2]);
        if (strpos($competencias_texto, 'competencias') !== false) {
            $areas_con_competencias++;
            // Extraer número de competencias
            if (preg_match('/(\d+)\s+competencia/i', $competencias_texto, $matches)) {
                $total_competencias += intval($matches[1]);
            }
        } else {
            $areas_sin_competencias++;
        }
        
        // Contadores de docentes y asignaciones (columna 3)
        $docentes_texto = trim($row[3]);
        if (preg_match('/(\d+)\s+docente/i', $docentes_texto, $matches)) {
            $total_docentes += intval($matches[1]);
        }
        if (preg_match('/(\d+)\s+asignacion/i', $docentes_texto, $matches)) {
            $total_asignaciones += intval($matches[1]);
        }
        
        // Niveles atendidos (columna 1)
        $niveles_texto = trim($row[1]);
        if (!empty($niveles_texto) && $niveles_texto !== 'Sin asignaciones') {
            $niveles_array = explode('|||', $niveles_texto);
            foreach ($niveles_array as $nivel) {
                $nivel = trim($nivel);
                if (!empty($nivel) && !in_array($nivel, $niveles_atendidos)) {
                    $niveles_atendidos[] = $nivel;
                }
            }
        }
    }
    
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

    #tabla-cabecera, #tabla-areas {
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

    #tabla-areas td, #tabla-areas th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-areas th {
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

    .badge-codigo {
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        background-color: #A8D8EA;
        color: #333;
    }

    .badge-estado {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .estado-activa { background-color: #28a745; }
    .estado-inactiva { background-color: #6c757d; }

    .badge-nivel {
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
        background-color: #FFAAA5;
        color: #333;
        margin: 1px;
        display: inline-block;
    }

    .competencias-badge {
        background-color: #C7CEEA;
        color: #333;
        font-size: 6px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .docentes-badge {
        background-color: #FFDDC1;
        color: #856404;
        font-size: 7px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .col-area { width: 25%; }
    .col-niveles { width: 20%; }
    .col-competencias { width: 18%; }
    .col-docentes { width: 18%; }
    .col-estado { width: 10%; }

    .area-descripcion {
        font-size: 6px;
        color: #6c757d;
        font-style: italic;
        margin-top: 2px;
    }

    .niveles-resumen {
        background-color: #f8f9fa;
        padding: 8px;
        margin: 10px 0;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        font-size: 8px;
        text-align: center;
    }

    .competencia-item {
        font-size: 6px;
        color: #495057;
        margin: 1px 0;
        padding: 1px 3px;
        background-color: #f8f9fa;
        border-left: 2px solid #0d6efd;
        border-radius: 2px;
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
                <h4>ÁREAS CURRICULARES</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_areas . '</span>
                <span class="stat-label">Total Áreas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $areas_activas . '</span>
                <span class="stat-label">Áreas Activas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #6c757d;">' . $areas_inactivas . '</span>
                <span class="stat-label">Áreas Inactivas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $areas_con_competencias . '</span>
                <span class="stat-label">Con Competencias</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . $total_competencias . '</span>
                <span class="stat-label">Total Competencias</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . $total_docentes . '</span>
                <span class="stat-label">Docentes Asignados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $total_asignaciones . '</span>
                <span class="stat-label">Total Asignaciones</span>
            </td>
        </tr>
    </table>
</div>';

// **Resumen de niveles atendidos**
if (!empty($niveles_atendidos)) {
    $niveles_html = '<div class="niveles-resumen"><strong>Niveles Educativos Atendidos:</strong> ';
    foreach ($niveles_atendidos as $nivel) {
        $niveles_html .= htmlspecialchars($nivel) . ' | ';
    }
    $niveles_html = rtrim($niveles_html, ' | ');
    $niveles_html .= '</div>';
    $html .= $niveles_html;
}

// **Sección de listado**
$html .= '<div class="seccion-titulo">CATÁLOGO DE ÁREAS CURRICULARES</div>';
$html .= '<table id="tabla-areas">
    <thead>
        <tr>
            <th class="col-area">Área Curricular</th>
            <th class="col-niveles">Niveles Atendidos</th>
            <th class="col-competencias">Competencias Definidas</th>
            <th class="col-docentes">Docentes y Asignaciones</th>
            <th class="col-estado">Estado</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosAreas as $row) {
    // Extraer datos de cada columna
    $area_datos = trim($row[0]); // Contiene código y nombre
    $niveles = trim($row[1]);
    $competencias = trim($row[2]);
    $docentes = trim($row[3]);
    $estado = trim($row[4]);
    $descripcion = isset($row[5]) ? trim($row[5]) : '';
    $competencias_detalle = isset($row[6]) ? trim($row[6]) : '';
    
    // Separar código y nombre del área
    $codigo = '';
    $nombre_area = $area_datos;
    if (preg_match('/^([A-Z0-9\/]+)\s+(.+)$/i', $area_datos, $matches)) {
        $codigo = $matches[1];
        $nombre_area = $matches[2];
    }
    
    // Determinar clase de estado
    $estadoClass = $estado === 'ACTIVA' ? 'estado-activa' : 'estado-inactiva';
    
    // Procesar niveles
    $niveles_html = '';
    if (!empty($niveles) && $niveles !== 'Sin asignaciones') {
        $niveles_array = explode('|||', $niveles);
        foreach ($niveles_array as $nivel) {
            $nivel = trim($nivel);
            if (!empty($nivel)) {
                $niveles_html .= '<span class="badge-nivel">' . htmlspecialchars($nivel) . '</span> ';
            }
        }
    } else {
        $niveles_html = '<span style="font-size: 6px; color: #6c757d;">Sin asignaciones</span>';
    }
    
    // Procesar competencias
    $competencias_html = '';
    if (strpos($competencias, 'competencias') !== false) {
        $competencias_html = '<span class="competencias-badge">' . htmlspecialchars($competencias) . '</span>';
        
        // Agregar detalle de competencias si existe
        if (!empty($competencias_detalle)) {
            $comps_array = explode('|||', $competencias_detalle);
            $competencias_html .= '<br>';
            $count = 0;
            foreach ($comps_array as $comp) {
                $comp = trim($comp);
                if (!empty($comp) && $count < 3) { // Mostrar máximo 3
                    $competencias_html .= '<div class="competencia-item">• ' . htmlspecialchars($comp) . '</div>';
                    $count++;
                }
            }
            if (count($comps_array) > 3) {
                $competencias_html .= '<div class="competencia-item" style="text-align: center;">... y ' . (count($comps_array) - 3) . ' más</div>';
            }
        }
    } else {
        $competencias_html = '<span style="font-size: 6px; color: #6c757d;">Sin competencias</span>';
    }
    
    // Procesar docentes
    $docentes_html = '';
    if (!empty($docentes) && $docentes !== 'Sin docentes') {
        $docentes_html = '<span class="docentes-badge">' . htmlspecialchars($docentes) . '</span>';
    } else {
        $docentes_html = '<span style="font-size: 6px; color: #6c757d;">Sin docentes</span>';
    }
    
    // Código del área
    $codigo_html = !empty($codigo) ? '<span class="badge-codigo">' . htmlspecialchars($codigo) . '</span><br>' : '';
    
    // Descripción (truncar si es muy larga)
    $descripcion_html = '';
    if (!empty($descripcion) && $descripcion !== 'Sin descripción') {
        if (strlen($descripcion) > 80) {
            $descripcion = substr($descripcion, 0, 77) . '...';
        }
        $descripcion_html = '<div class="area-descripcion">' . htmlspecialchars($descripcion) . '</div>';
    }
    
    $html .= '<tr>
        <td class="col-area" style="font-size: 7px;">
            ' . $codigo_html . '
            <strong>' . htmlspecialchars($nombre_area) . '</strong>
            ' . $descripcion_html . '
        </td>
        <td class="col-niveles" style="font-size: 6px;">' . $niveles_html . '</td>
        <td class="col-competencias" style="font-size: 6px;">' . $competencias_html . '</td>
        <td class="col-docentes" style="text-align: center; font-size: 6px;">' . $docentes_html . '</td>
        <td class="col-estado" style="text-align: center;">
            <span class="badge-estado ' . $estadoClass . '">' . $estado . '</span>
        </td>
    </tr>';
}

$html .= '</tbody></table>';

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
</div>';

// **Configurar DomPDF**
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// **Enviar PDF al navegador**
$filename = 'Reporte_Areas_Curriculares_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>