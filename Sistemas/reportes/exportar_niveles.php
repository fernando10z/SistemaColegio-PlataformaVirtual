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
    $datosNiveles = isset($_POST['datosNiveles']) ? json_decode($_POST['datosNiveles'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosNiveles)) {
        die("<script>alert('No hay registros de niveles educativos disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_niveles = count($datosNiveles);
    $niveles_activos = 0;
    $niveles_inactivos = 0;
    $total_grados = 0;
    $total_secciones = 0;
    $total_estudiantes = 0;
    
    $edad_minima = null;
    $edad_maxima = null;
    
    foreach ($datosNiveles as $row) {
        // Contadores de estado (columna 6)
        $estado = trim($row[6]);
        if ($estado === 'ACTIVO') {
            $niveles_activos++;
        } else {
            $niveles_inactivos++;
        }
        
        // Contar grados (columna 2 - formato: "X grados")
        $grados_texto = trim($row[2]);
        if (preg_match('/(\d+)\s+grado/i', $grados_texto, $matches)) {
            $total_grados += intval($matches[1]);
        }
        
        // Totales (columnas 4 y 5)
        $total_secciones += intval(trim($row[4]));
        $total_estudiantes += intval(trim($row[5]));
        
        // Rango de edades (columna 3 - formato: "X - Y años")
        $edades_texto = trim($row[3]);
        if (preg_match('/(\d+)\s*-\s*(\d+)\s+años/i', $edades_texto, $matches)) {
            $min = intval($matches[1]);
            $max = intval($matches[2]);
            
            if ($edad_minima === null || $min < $edad_minima) {
                $edad_minima = $min;
            }
            if ($edad_maxima === null || $max > $edad_maxima) {
                $edad_maxima = $max;
            }
        }
    }
    
    $rango_edades_texto = ($edad_minima !== null && $edad_maxima !== null) 
        ? "$edad_minima - $edad_maxima años" 
        : "No definido";
    
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

    #tabla-cabecera, #tabla-niveles {
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

    #tabla-niveles td, #tabla-niveles th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-niveles th {
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

    .badge-orden {
        padding: 2px 6px;
        border-radius: 50%;
        font-size: 7px;
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

    .estado-activo { background-color: #28a745; }
    .estado-inactivo { background-color: #6c757d; }

    .col-orden { width: 5%; }
    .col-nivel { width: 20%; }
    .col-grados { width: 25%; }
    .col-edades { width: 12%; }
    .col-secciones { width: 10%; }
    .col-estudiantes { width: 12%; }
    .col-estado { width: 8%; }

    .grado-item {
        font-size: 6px;
        color: #495057;
        margin: 1px 0;
        padding: 1px 3px;
        background-color: #FFAAA5;
        border-radius: 3px;
        display: inline-block;
        margin-right: 2px;
    }

    .edad-badge {
        background-color: #C7CEEA;
        color: #333;
        font-size: 6px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .count-badge {
        background-color: #FFDDC1;
        color: #856404;
        font-size: 7px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .nivel-codigo {
        font-size: 6px;
        color: #6c757d;
        font-family: monospace;
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
                <h4>NIVELES Y GRADOS EDUCATIVOS</h4>
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
                <span class="stat-numero">' . $total_niveles . '</span>
                <span class="stat-label">Total Niveles</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $niveles_activos . '</span>
                <span class="stat-label">Niveles Activos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #6c757d;">' . $niveles_inactivos . '</span>
                <span class="stat-label">Niveles Inactivos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . $total_grados . '</span>
                <span class="stat-label">Total Grados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . number_format($total_secciones) . '</span>
                <span class="stat-label">Total Secciones</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . number_format($total_estudiantes) . '</span>
                <span class="stat-label">Total Estudiantes</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . htmlspecialchars($rango_edades_texto) . '</span>
                <span class="stat-label">Rango de Edades</span>
            </td>
        </tr>
    </table>
</div>';

// **Sección de listado**
$html .= '<div class="seccion-titulo">ESTRUCTURA DE NIVELES Y GRADOS EDUCATIVOS</div>';
$html .= '<table id="tabla-niveles">
    <thead>
        <tr>
            <th class="col-orden">Orden</th>
            <th class="col-nivel">Nivel Educativo</th>
            <th class="col-grados">Grados Configurados</th>
            <th class="col-edades">Rango Edades</th>
            <th class="col-secciones">Secciones</th>
            <th class="col-estudiantes">Estudiantes</th>
            <th class="col-estado">Estado</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosNiveles as $row) {
    // Extraer datos de cada columna
    $orden = htmlspecialchars(trim($row[0]));
    $nivel_nombre = trim($row[1]);
    $grados_texto = trim($row[2]);
    $edades = htmlspecialchars(trim($row[3]));
    $secciones = htmlspecialchars(trim($row[4]));
    $estudiantes = htmlspecialchars(trim($row[5]));
    $estado = trim($row[6]);
    $codigo = isset($row[7]) ? trim($row[7]) : '';
    $grados_detalle = isset($row[8]) ? trim($row[8]) : '';
    
    // Determinar clase de estado
    $estadoClass = $estado === 'ACTIVO' ? 'estado-activo' : 'estado-inactivo';
    
    // Procesar nombre del nivel (puede incluir código)
    $nombre_limpio = $nivel_nombre;
    $codigo_html = '';
    if (!empty($codigo) && $codigo !== 'Sin código') {
        $codigo_html = '<br><span class="nivel-codigo">Código: ' . htmlspecialchars($codigo) . '</span>';
    }
    
    // Procesar grados detallados
    $grados_html = '';
    if (!empty($grados_detalle)) {
        $grados_array = explode('|||', $grados_detalle);
        foreach ($grados_array as $grado) {
            $grado = trim($grado);
            if (!empty($grado)) {
                $grados_html .= '<span class="grado-item">' . htmlspecialchars($grado) . '</span> ';
            }
        }
    }
    if (empty($grados_html)) {
        $grados_html = '<span class="grado-item">' . htmlspecialchars($grados_texto) . '</span>';
    }
    
    // Truncar nombre si es muy largo
    if (strlen($nombre_limpio) > 60) {
        $nombre_limpio = substr($nombre_limpio, 0, 57) . '...';
    }
    
    $html .= '<tr>
        <td class="col-orden" style="text-align: center;">
            <span class="badge-orden">' . $orden . '</span>
        </td>
        <td class="col-nivel" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_limpio) . '</strong>' . $codigo_html . '
        </td>
        <td class="col-grados" style="font-size: 6px;">' . $grados_html . '</td>
        <td class="col-edades" style="text-align: center;">
            <span class="edad-badge">' . $edades . '</span>
        </td>
        <td class="col-secciones" style="text-align: center;">
            <span class="count-badge">' . $secciones . '</span>
        </td>
        <td class="col-estudiantes" style="text-align: center; font-size: 8px; font-weight: bold; color: #28a745;">' . $estudiantes . '</td>
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
    <strong>Total Niveles:</strong> ' . $total_niveles . ' | 
    <strong>Niveles Activos:</strong> ' . $niveles_activos . ' | 
    <strong>Total Grados:</strong> ' . $total_grados . ' | 
    <strong>Total Estudiantes:</strong> ' . number_format($total_estudiantes) . '
</div>';

// **Configurar DomPDF**
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Vertical
$dompdf->render();

// **Enviar PDF al navegador**
$filename = 'Reporte_Niveles_Grados_Educativos_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>