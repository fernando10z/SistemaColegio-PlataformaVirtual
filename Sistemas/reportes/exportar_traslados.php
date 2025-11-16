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
    
    // Obtener período actual
    $stmt_periodo = $conexion->prepare("SELECT * FROM periodos_academicos WHERE activo = 1 AND actual = 1 LIMIT 1");
    $stmt_periodo->execute();
    $periodo_actual = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
    
    $periodo_nombre = $periodo_actual['nombre'] ?? 'Período Académico';
    $periodo_anio = $periodo_actual['anio'] ?? date('Y');
    
    // Obtener los datos filtrados desde POST
    $datosTraslados = isset($_POST['datosTraslados']) ? json_decode($_POST['datosTraslados'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosTraslados)) {
        die("<script>alert('No hay registros de traslados disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_traslados = count($datosTraslados);
    $traslados_ultimo_mes = 0;
    $traslados_ultima_semana = 0;
    $traslados_hoy = 0;
    
    $niveles_afectados = [];
    $secciones_origen_conteo = [];
    $secciones_destino_conteo = [];
    $estudiantes_trasladados = [];
    
    $fecha_hoy = date('Y-m-d');
    $fecha_semana = date('Y-m-d', strtotime('-7 days'));
    $fecha_mes = date('Y-m-d', strtotime('-30 days'));
    
    foreach ($datosTraslados as $row) {
        // Fecha (columna 4)
        $fecha_texto = trim($row[4]);
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $fecha_texto, $matches)) {
            $fecha_traslado = $matches[1];
            
            if ($fecha_traslado === $fecha_hoy) {
                $traslados_hoy++;
            }
            if ($fecha_traslado >= $fecha_semana) {
                $traslados_ultima_semana++;
            }
            if ($fecha_traslado >= $fecha_mes) {
                $traslados_ultimo_mes++;
            }
        }
        
        // Estudiante (columna 0)
        $estudiante = trim($row[0]);
        if (!in_array($estudiante, $estudiantes_trasladados)) {
            $estudiantes_trasladados[] = $estudiante;
        }
        
        // Nivel (columna 1)
        $nivel = trim($row[1]);
        if (!empty($nivel)) {
            if (!isset($niveles_afectados[$nivel])) {
                $niveles_afectados[$nivel] = 0;
            }
            $niveles_afectados[$nivel]++;
        }
        
        // Sección origen (columna 2)
        $seccion_origen = trim($row[2]);
        if (!empty($seccion_origen)) {
            if (!isset($secciones_origen_conteo[$seccion_origen])) {
                $secciones_origen_conteo[$seccion_origen] = 0;
            }
            $secciones_origen_conteo[$seccion_origen]++;
        }
        
        // Sección destino (columna 3)
        $seccion_destino = trim($row[3]);
        if (!empty($seccion_destino)) {
            if (!isset($secciones_destino_conteo[$seccion_destino])) {
                $secciones_destino_conteo[$seccion_destino] = 0;
            }
            $secciones_destino_conteo[$seccion_destino]++;
        }
    }
    
    $total_estudiantes_unicos = count($estudiantes_trasladados);
    $total_niveles_afectados = count($niveles_afectados);
    
    // Encontrar secciones más afectadas
    arsort($secciones_origen_conteo);
    $top_secciones_origen = array_slice($secciones_origen_conteo, 0, 3, true);
    
    arsort($secciones_destino_conteo);
    $top_secciones_destino = array_slice($secciones_destino_conteo, 0, 3, true);
    
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

    #tabla-cabecera, #tabla-traslados {
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

    #tabla-traslados td, #tabla-traslados th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-traslados th {
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

    .badge-seccion {
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
    }

    .seccion-origen {
        background-color: #FFAAA5;
        color: #721c24;
    }

    .seccion-destino {
        background-color: #C7CEEA;
        color: #0c5460;
    }

    .col-estudiante { width: 20%; }
    .col-nivel { width: 10%; }
    .col-origen { width: 15%; }
    .col-destino { width: 15%; }
    .col-fecha { width: 12%; }
    .col-capacidad { width: 15%; }
    .col-motivo { width: 20%; }

    .estudiante-codigo {
        font-size: 6px;
        color: #6c757d;
        font-family: monospace;
    }

    .capacidad-info {
        font-size: 6px;
        color: #495057;
        background-color: #f8f9fa;
        padding: 2px 4px;
        border-radius: 3px;
        text-align: center;
    }

    .capacidad-origen { border-left: 3px solid #dc3545; }
    .capacidad-destino { border-left: 3px solid #28a745; }

    .flecha-traslado {
        color: #0d6efd;
        font-size: 10px;
        font-weight: bold;
    }

    .alerta-traslados {
        background-color: #e3f2fd;
        border: 1px solid #2196f3;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 8px;
        text-align: center;
        color: #1976d2;
        font-weight: bold;
    }

    .resumen-periodo {
        background-color: #e8f5e9;
        border: 1px solid #4caf50;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 9px;
        text-align: center;
        color: #2e7d32;
    }

    .top-secciones {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 7px;
    }

    .top-secciones-titulo {
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
                <h4>GESTIÓN DE TRASLADOS</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Resumen de período**
$html .= '<div class="resumen-periodo">';
$html .= 'PERÍODO ACADÉMICO: ' . htmlspecialchars($periodo_nombre) . ' | ';
$html .= 'AÑO: ' . $periodo_anio . ' | ';
$html .= 'TOTAL TRASLADOS REGISTRADOS: ' . $total_traslados;
$html .= '</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_traslados . '</span>
                <span class="stat-label">Total Traslados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $traslados_hoy . '</span>
                <span class="stat-label">Hoy</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #17a2b8;">' . $traslados_ultima_semana . '</span>
                <span class="stat-label">Última Semana</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #ffc107;">' . $traslados_ultimo_mes . '</span>
                <span class="stat-label">Último Mes</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $total_estudiantes_unicos . '</span>
                <span class="stat-label">Estudiantes Únicos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $total_niveles_afectados . '</span>
                <span class="stat-label">Niveles Afectados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . count($secciones_origen_conteo) . '</span>
                <span class="stat-label">Secciones Origen</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . count($secciones_destino_conteo) . '</span>
                <span class="stat-label">Secciones Destino</span>
            </td>
        </tr>
    </table>
</div>';

// **Top Secciones más afectadas**
if (!empty($top_secciones_origen) || !empty($top_secciones_destino)) {
    $html .= '<div class="top-secciones">';
    $html .= '<div class="top-secciones-titulo">📊 SECCIONES MÁS AFECTADAS</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse;">';
    $html .= '<tr>';
    
    // Top Secciones Origen
    $html .= '<td style="width: 50%; padding: 5px; vertical-align: top;">';
    $html .= '<strong style="color: #dc3545;">▼ ORIGEN (Salidas):</strong><br>';
    foreach ($top_secciones_origen as $seccion => $cantidad) {
        $html .= htmlspecialchars($seccion) . ': <strong>' . $cantidad . '</strong> traslados<br>';
    }
    $html .= '</td>';
    
    // Top Secciones Destino
    $html .= '<td style="width: 50%; padding: 5px; vertical-align: top;">';
    $html .= '<strong style="color: #28a745;">▲ DESTINO (Entradas):</strong><br>';
    foreach ($top_secciones_destino as $seccion => $cantidad) {
        $html .= htmlspecialchars($seccion) . ': <strong>' . $cantidad . '</strong> recepciones<br>';
    }
    $html .= '</td>';
    
    $html .= '</tr></table>';
    $html .= '</div>';
}

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE TRASLADOS</div>';
$html .= '<table id="tabla-traslados">
    <thead>
        <tr>
            <th class="col-estudiante">Estudiante</th>
            <th class="col-nivel">Nivel</th>
            <th class="col-origen">Sección Origen</th>
            <th class="col-destino">Sección Destino</th>
            <th class="col-fecha">Fecha Traslado</th>
            <th class="col-capacidad">Capacidades</th>
            <th class="col-motivo">Motivo / Observaciones</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosTraslados as $row) {
    // Extraer datos de cada columna
    $estudiante_info = trim($row[0]);
    $nivel = htmlspecialchars(trim($row[1]));
    $seccion_origen = htmlspecialchars(trim($row[2]));
    $seccion_destino = htmlspecialchars(trim($row[3]));
    $fecha = htmlspecialchars(trim($row[4]));
    $capacidades = trim($row[5]);
    $motivo = trim($row[6]);
    
    // Procesar estudiante (nombre + código + documento)
    $lineas_estudiante = explode("\n", $estudiante_info);
    $nombre_estudiante = isset($lineas_estudiante[0]) ? trim($lineas_estudiante[0]) : '';
    $codigo_estudiante = isset($lineas_estudiante[1]) ? trim($lineas_estudiante[1]) : '';
    $documento = isset($lineas_estudiante[2]) ? trim($lineas_estudiante[2]) : '';
    
    // Procesar capacidades
    $capacidades_html = '';
    if (!empty($capacidades) && $capacidades !== 'N/A') {
        $lineas_capacidad = explode("\n", $capacidades);
        if (count($lineas_capacidad) >= 2) {
            $capacidades_html = '<div class="capacidad-info capacidad-origen">' . htmlspecialchars($lineas_capacidad[0]) . '</div>';
            $capacidades_html .= '<div class="capacidad-info capacidad-destino" style="margin-top: 2px;">' . htmlspecialchars($lineas_capacidad[1]) . '</div>';
        } else {
            $capacidades_html = '<div class="capacidad-info">' . htmlspecialchars($capacidades) . '</div>';
        }
    } else {
        $capacidades_html = '<div style="text-align: center; color: #6c757d; font-size: 6px;">N/A</div>';
    }
    
    // Limpiar motivo
    if (empty($motivo) || $motivo === 'Sin especificar' || $motivo === 'N/A') {
        $motivo = '<span style="color: #6c757d; font-style: italic;">Sin motivo especificado</span>';
    } else {
        if (strlen($motivo) > 120) {
            $motivo = substr($motivo, 0, 117) . '...';
        }
        $motivo = htmlspecialchars($motivo);
    }
    
    $html .= '<tr>
        <td class="col-estudiante" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_estudiante) . '</strong><br>
            <span class="estudiante-codigo">' . htmlspecialchars($codigo_estudiante) . '</span><br>
            <span style="font-size: 6px; color: #6c757d;">' . htmlspecialchars($documento) . '</span>
        </td>
        <td class="col-nivel" style="text-align: center;">
            <span class="badge-nivel">' . $nivel . '</span>
        </td>
        <td class="col-origen" style="text-align: center;">
            <span class="badge-seccion seccion-origen">' . $seccion_origen . '</span>
        </td>
        <td class="col-destino" style="text-align: center;">
            <span class="badge-seccion seccion-destino">' . $seccion_destino . '</span>
        </td>
        <td class="col-fecha" style="text-align: center; font-size: 7px;">' . $fecha . '</td>
        <td class="col-capacidad">' . $capacidades_html . '</td>
        <td class="col-motivo" style="font-size: 6px;">' . $motivo . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <strong>Período:</strong> ' . htmlspecialchars($periodo_nombre) . ' | 
    <strong>Total Traslados:</strong> ' . $total_traslados . ' | 
    <strong>Estudiantes Únicos:</strong> ' . $total_estudiantes_unicos . ' | 
    <strong>Niveles Afectados:</strong> ' . $total_niveles_afectados . '
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
$filename = 'Reporte_Traslados_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>