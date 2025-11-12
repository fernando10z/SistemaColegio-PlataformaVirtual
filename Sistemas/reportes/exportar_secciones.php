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
    $datosSecciones = isset($_POST['datosSecciones']) ? json_decode($_POST['datosSecciones'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosSecciones)) {
        die("<script>alert('No hay registros de secciones disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_secciones = count($datosSecciones);
    $secciones_activas = 0;
    $secciones_inactivas = 0;
    $secciones_disponibles = 0;
    $secciones_ocupadas = 0;
    $secciones_completas = 0;
    $capacidad_total = 0;
    $estudiantes_total = 0;
    $secciones_con_sobrecupo = 0;
    
    $niveles_conteo = [];
    $periodos_conteo = [];
    
    foreach ($datosSecciones as $row) {
        // Estado activo/inactivo (columna 5)
        $estado_general = trim($row[5]);
        if ($estado_general === 'ACTIVA') {
            $secciones_activas++;
        } else {
            $secciones_inactivas++;
        }
        
        // Estado de ocupación (columna 5 - segundo badge)
        $estado_ocupacion = trim($row[6]);
        switch ($estado_ocupacion) {
            case 'DISPONIBLE':
                $secciones_disponibles++;
                break;
            case 'OCUPADA':
                $secciones_ocupadas++;
                break;
            case 'COMPLETA':
                $secciones_completas++;
                break;
        }
        
        // Capacidad (columna 3)
        $capacidad = intval(trim($row[3]));
        $capacidad_total += $capacidad;
        
        // Estudiantes (columna 4 - formato: "XX/YY")
        $estudiantes_texto = trim($row[4]);
        if (preg_match('/(\d+)\/(\d+)/', $estudiantes_texto, $matches)) {
            $estudiantes = intval($matches[1]);
            $estudiantes_total += $estudiantes;
            
            // Detectar sobrecupo
            if ($estudiantes > $capacidad) {
                $secciones_con_sobrecupo++;
            }
        }
        
        // Niveles (columna 1)
        $nivel = trim($row[1]);
        if (!empty($nivel)) {
            if (!isset($niveles_conteo[$nivel])) {
                $niveles_conteo[$nivel] = 0;
            }
            $niveles_conteo[$nivel]++;
        }
        
        // Períodos (columna 7)
        $periodo = trim($row[7]);
        if (!empty($periodo)) {
            if (!isset($periodos_conteo[$periodo])) {
                $periodos_conteo[$periodo] = 0;
            }
            $periodos_conteo[$periodo]++;
        }
    }
    
    $ocupacion_promedio = $capacidad_total > 0 ? round(($estudiantes_total / $capacidad_total) * 100, 1) : 0;
    $cupos_disponibles = $capacidad_total - $estudiantes_total;
    
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

    #tabla-cabecera, #tabla-secciones {
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

    #tabla-secciones td, #tabla-secciones th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-secciones th {
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

    .badge-nivel {
        padding: 2px 5px;
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

    .estado-disponible { background-color: #28a745; }
    .estado-ocupada { background-color: #ffc107; color: #856404; }
    .estado-completa { background-color: #dc3545; }
    .estado-activa { background-color: #17a2b8; }
    .estado-inactiva { background-color: #6c757d; }

    .col-seccion { width: 15%; }
    .col-nivel { width: 12%; }
    .col-aula { width: 12%; }
    .col-capacidad { width: 8%; }
    .col-ocupacion { width: 18%; }
    .col-estado { width: 12%; }
    .col-periodo { width: 10%; }

    .ocupacion-visual {
        background-color: #e9ecef;
        border-radius: 8px;
        height: 12px;
        position: relative;
        margin: 2px 0;
    }

    .ocupacion-barra {
        height: 100%;
        border-radius: 8px;
    }

    .barra-disponible { background-color: #C7CEEA; }
    .barra-ocupada { background-color: #FFAAA5; }
    .barra-completa { background-color: #dc3545; }
    .barra-sobrecupo { background-color: #721c24; }

    .capacidad-badge {
        background-color: #FFDDC1;
        color: #856404;
        font-size: 7px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .sobrecupo-texto {
        color: #dc3545;
        font-weight: bold;
        font-size: 6px;
    }

    .aula-badge {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        font-size: 6px;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .resumen-ocupacion {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 9px;
        text-align: center;
        color: #856404;
        font-weight: bold;
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
                <h4>GESTIÓN DE SECCIONES</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Resumen de ocupación general**
$html .= '<div class="resumen-ocupacion">
    OCUPACIÓN PROMEDIO: ' . $ocupacion_promedio . '% | 
    ESTUDIANTES: ' . number_format($estudiantes_total) . '/' . number_format($capacidad_total) . ' | 
    CUPOS ' . ($cupos_disponibles >= 0 ? 'DISPONIBLES: ' . number_format($cupos_disponibles) : 'SOBRECUPO: ' . number_format(abs($cupos_disponibles))) . 
    ($secciones_con_sobrecupo > 0 ? ' | ⚠ ' . $secciones_con_sobrecupo . ' SECCIONES CON SOBRECUPO' : '') . '
</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_secciones . '</span>
                <span class="stat-label">Total Secciones</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #17a2b8;">' . $secciones_activas . '</span>
                <span class="stat-label">Secciones Activas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $secciones_disponibles . '</span>
                <span class="stat-label">Disponibles</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #ffc107;">' . $secciones_ocupadas . '</span>
                <span class="stat-label">Ocupadas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #dc3545;">' . $secciones_completas . '</span>
                <span class="stat-label">Completas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . number_format($capacidad_total) . '</span>
                <span class="stat-label">Capacidad Total</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . number_format($estudiantes_total) . '</span>
                <span class="stat-label">Estudiantes</span>
            </td>
        </tr>
    </table>
</div>';

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE SECCIONES</div>';
$html .= '<table id="tabla-secciones">
    <thead>
        <tr>
            <th class="col-seccion">Sección</th>
            <th class="col-nivel">Nivel / Grado</th>
            <th class="col-aula">Aula</th>
            <th class="col-capacidad">Capacidad</th>
            <th class="col-ocupacion">Ocupación</th>
            <th class="col-estado">Estado</th>
            <th class="col-periodo">Período</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosSecciones as $row) {
    // Extraer datos de cada columna
    $seccion_codigo = htmlspecialchars(trim($row[0]));
    $nivel_grado = htmlspecialchars(trim($row[1]));
    $aula = htmlspecialchars(trim($row[2]));
    $capacidad = intval(trim($row[3]));
    $ocupacion_texto = trim($row[4]); // Formato: "XX/YY"
    $estado_general = trim($row[5]);
    $estado_ocupacion = trim($row[6]);
    $periodo = htmlspecialchars(trim($row[7]));
    $porcentaje = isset($row[8]) ? floatval(trim($row[8])) : 0;
    
    // Procesar ocupación
    $estudiantes = 0;
    if (preg_match('/(\d+)\/(\d+)/', $ocupacion_texto, $matches)) {
        $estudiantes = intval($matches[1]);
    }
    
    // Determinar clases de estado
    $estado_ocupacion_class = 'estado-disponible';
    $barra_class = 'barra-disponible';
    
    if ($estado_ocupacion === 'COMPLETA') {
        $estado_ocupacion_class = 'estado-completa';
        $barra_class = 'barra-completa';
    } elseif ($estado_ocupacion === 'OCUPADA') {
        $estado_ocupacion_class = 'estado-ocupada';
        $barra_class = 'barra-ocupada';
    }
    
    if ($estudiantes > $capacidad) {
        $barra_class = 'barra-sobrecupo';
    }
    
    $estado_general_class = $estado_general === 'ACTIVA' ? 'estado-activa' : 'estado-inactiva';
    
    // Calcular ancho de barra (máximo 100%)
    $ancho_barra = min(($capacidad > 0 ? ($estudiantes / $capacidad) * 100 : 0), 100);
    
    // Cupos disponibles o sobrecupo
    $cupos = $capacidad - $estudiantes;
    $cupos_texto = '';
    if ($cupos < 0) {
        $cupos_texto = '<span class="sobrecupo-texto">Sobrecupo: ' . abs($cupos) . '</span>';
    } elseif ($cupos > 0) {
        $cupos_texto = '<span style="color: #28a745; font-size: 6px;">' . $cupos . ' libres</span>';
    }
    
    // Aula
    $aula_html = !empty($aula) && $aula !== 'Sin aula' 
        ? '<span class="aula-badge">' . $aula . '</span>' 
        : '<span style="font-size: 6px; color: #6c757d;">Sin aula</span>';
    
    $html .= '<tr>
        <td class="col-seccion" style="font-size: 7px;">
            <strong>' . $seccion_codigo . '</strong>
        </td>
        <td class="col-nivel" style="text-align: center;">
            <span class="badge-nivel">' . $nivel_grado . '</span>
        </td>
        <td class="col-aula" style="text-align: center;">' . $aula_html . '</td>
        <td class="col-capacidad" style="text-align: center;">
            <span class="capacidad-badge">' . $capacidad . '</span>
        </td>
        <td class="col-ocupacion">
            <div class="ocupacion-visual">
                <div class="ocupacion-barra ' . $barra_class . '" style="width: ' . $ancho_barra . '%"></div>
            </div>
            <div style="font-size: 6px; text-align: center;">
                ' . $ocupacion_texto . ' (' . $porcentaje . '%)<br>
                ' . $cupos_texto . '
            </div>
        </td>
        <td class="col-estado" style="text-align: center;">
            <span class="badge-estado ' . $estado_ocupacion_class . '">' . $estado_ocupacion . '</span>
            <br>
            <span class="badge-estado ' . $estado_general_class . ' " style="margin-top: 2px;">' . $estado_general . '</span>
        </td>
        <td class="col-periodo" style="font-size: 6px; text-align: center;">' . $periodo . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <strong>Total Secciones:</strong> ' . $total_secciones . ' | 
    <strong>Activas:</strong> ' . $secciones_activas . ' | 
    <strong>Ocupación Promedio:</strong> ' . $ocupacion_promedio . '% | 
    <strong>Estudiantes:</strong> ' . number_format($estudiantes_total) . '/' . number_format($capacidad_total) . '
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
$filename = 'Reporte_Secciones_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>