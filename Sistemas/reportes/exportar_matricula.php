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
    $datosMatriculas = isset($_POST['datosMatriculas']) ? json_decode($_POST['datosMatriculas'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosMatriculas)) {
        die("<script>alert('No hay registros de matrículas disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_matriculas = count($datosMatriculas);
    $matriculas_activas = 0;
    $matriculas_retiradas = 0;
    $matriculas_trasladadas = 0;
    $matriculas_reservadas = 0;
    $estudiantes_nuevos = 0;
    $estudiantes_continuadores = 0;
    $estudiantes_traslado = 0;
    
    $periodos_conteo = [];
    $secciones_conteo = [];
    $capacidad_total = 0;
    $ocupacion_total = 0;
    $secciones_completas = 0;
    $secciones_con_problemas = [];
    
    foreach ($datosMatriculas as $row) {
        // Estados (columna 3)
        $estados_tipos = trim($row[3]);
        
        if (strpos($estados_tipos, 'MATRICULADO') !== false) {
            $matriculas_activas++;
        } elseif (strpos($estados_tipos, 'RETIRADO') !== false) {
            $matriculas_retiradas++;
        } elseif (strpos($estados_tipos, 'TRASLADADO') !== false) {
            $matriculas_trasladadas++;
        } elseif (strpos($estados_tipos, 'RESERVADO') !== false) {
            $matriculas_reservadas++;
        }
        
        // Tipos de matrícula
        if (strpos($estados_tipos, 'NUEVO') !== false) {
            $estudiantes_nuevos++;
        } elseif (strpos($estados_tipos, 'CONTINUADOR') !== false) {
            $estudiantes_continuadores++;
        } elseif (strpos($estados_tipos, 'TRASLADO') !== false) {
            $estudiantes_traslado++;
        }
        
        // Períodos (columna 0)
        $periodo_texto = trim($row[0]);
        if (preg_match('/\n(.+)$/', $periodo_texto, $matches)) {
            $periodo = trim($matches[1]);
            if (!isset($periodos_conteo[$periodo])) {
                $periodos_conteo[$periodo] = 0;
            }
            $periodos_conteo[$periodo]++;
        }
        
        // Secciones (columna 2)
        $seccion = trim($row[2]);
        if (!empty($seccion)) {
            $seccion_nombre = explode("\n", $seccion)[0];
            if (!isset($secciones_conteo[$seccion_nombre])) {
                $secciones_conteo[$seccion_nombre] = 0;
            }
            $secciones_conteo[$seccion_nombre]++;
        }
        
        // Capacidad (columna 4)
        $capacidad_texto = trim($row[4]);
        if (preg_match('/(\d+)\/(\d+)/', $capacidad_texto, $matches)) {
            $ocupados = intval($matches[1]);
            $capacidad = intval($matches[2]);
            $capacidad_total += $capacidad;
            $ocupacion_total += $ocupados;
            
            // Porcentaje de ocupación
            if (preg_match('/(\d+\.?\d*)%/', $capacidad_texto, $pct_match)) {
                $porcentaje = floatval($pct_match[1]);
                if ($porcentaje >= 100) {
                    $secciones_completas++;
                    if (!in_array($seccion_nombre, $secciones_con_problemas)) {
                        $secciones_con_problemas[] = $seccion_nombre;
                    }
                }
            }
        }
    }
    
    $ocupacion_promedio = $capacidad_total > 0 ? round(($ocupacion_total / $capacidad_total) * 100, 1) : 0;
    $cupos_disponibles = $capacidad_total - $ocupacion_total;
    
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

    #tabla-cabecera, #tabla-matriculas {
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

    #tabla-matriculas td, #tabla-matriculas th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-matriculas th {
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

    .badge-estado {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .estado-matriculado { background-color: #28a745; }
    .estado-retirado { background-color: #dc3545; }
    .estado-trasladado { background-color: #ffc107; color: #856404; }
    .estado-reservado { background-color: #17a2b8; }

    .badge-tipo {
        padding: 2px 5px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
        background-color: #6c757d;
        color: white;
    }

    .col-codigo { width: 12%; }
    .col-estudiante { width: 22%; }
    .col-seccion { width: 18%; }
    .col-estado { width: 12%; }
    .col-capacidad { width: 15%; }
    .col-fecha { width: 10%; }
    .col-observaciones { width: 18%; }

    .codigo-matricula {
        font-family: monospace;
        font-weight: bold;
        color: #0d6efd;
        font-size: 7px;
    }

    .estudiante-codigo {
        font-size: 6px;
        color: #6c757d;
        font-family: monospace;
    }

    .capacidad-visual {
        background-color: #e9ecef;
        border-radius: 6px;
        height: 10px;
        position: relative;
        margin: 2px 0;
    }

    .capacidad-barra {
        height: 100%;
        border-radius: 6px;
    }

    .barra-normal { background-color: #A8D8EA; }
    .barra-casi-llena { background-color: #FFAAA5; }
    .barra-completa { background-color: #dc3545; }

    .alerta-ocupacion {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 8px;
        text-align: center;
        color: #856404;
        font-weight: bold;
    }

    .resumen-ocupacion {
        background-color: #e8f5e9;
        border: 1px solid #4caf50;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 9px;
        text-align: center;
        color: #2e7d32;
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
                <h4>GESTIÓN DE MATRÍCULAS</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Alerta de secciones completas**
if ($secciones_completas > 0) {
    $html .= '<div class="alerta-ocupacion">';
    $html .= '⚠ ATENCIÓN: ' . $secciones_completas . ' sección(es) al 100% de capacidad o más. ';
    if (!empty($secciones_con_problemas)) {
        $html .= 'Secciones: ' . implode(', ', array_slice($secciones_con_problemas, 0, 4));
        if (count($secciones_con_problemas) > 4) {
            $html .= ' y ' . (count($secciones_con_problemas) - 4) . ' más';
        }
    }
    $html .= '</div>';
}

// **Resumen de ocupación**
$html .= '<div class="resumen-ocupacion">';
$html .= 'OCUPACIÓN GLOBAL: ' . $ocupacion_promedio . '% | ';
$html .= 'MATRICULADOS: ' . number_format($ocupacion_total) . '/' . number_format($capacidad_total) . ' | ';
$html .= 'CUPOS ' . ($cupos_disponibles >= 0 ? 'DISPONIBLES: ' . number_format($cupos_disponibles) : 'SOBRECUPO: ' . number_format(abs($cupos_disponibles)));
$html .= '</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_matriculas . '</span>
                <span class="stat-label">Total Matrículas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $matriculas_activas . '</span>
                <span class="stat-label">Matriculados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #dc3545;">' . $matriculas_retiradas . '</span>
                <span class="stat-label">Retirados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #ffc107;">' . $matriculas_trasladadas . '</span>
                <span class="stat-label">Trasladados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #17a2b8;">' . $matriculas_reservadas . '</span>
                <span class="stat-label">Reservados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $estudiantes_nuevos . '</span>
                <span class="stat-label">Nuevos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $estudiantes_continuadores . '</span>
                <span class="stat-label">Continuadores</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . $estudiantes_traslado . '</span>
                <span class="stat-label">Traslados</span>
            </td>
        </tr>
    </table>
</div>';

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE MATRÍCULAS</div>';
$html .= '<table id="tabla-matriculas">
    <thead>
        <tr>
            <th class="col-codigo">Código Matrícula</th>
            <th class="col-estudiante">Estudiante</th>
            <th class="col-seccion">Sección Asignada</th>
            <th class="col-estado">Estado / Tipo</th>
            <th class="col-capacidad">Capacidad Sección</th>
            <th class="col-fecha">Fecha</th>
            <th class="col-observaciones">Observaciones</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosMatriculas as $row) {
    // Extraer datos de cada columna
    $codigo_periodo = trim($row[0]);
    $estudiante = trim($row[1]);
    $seccion = trim($row[2]);
    $estado_tipo = trim($row[3]);
    $capacidad = trim($row[4]);
    $fecha = htmlspecialchars(trim($row[5]));
    $observaciones = trim($row[6]);
    
    // Separar código y período
    $lineas_codigo = explode("\n", $codigo_periodo);
    $codigo_matricula = isset($lineas_codigo[0]) ? trim($lineas_codigo[0]) : '';
    $periodo = isset($lineas_codigo[1]) ? trim($lineas_codigo[1]) : '';
    
    // Procesar estudiante (nombre + código + documento)
    $lineas_estudiante = explode("\n", $estudiante);
    $nombre_estudiante = isset($lineas_estudiante[0]) ? trim($lineas_estudiante[0]) : '';
    $codigo_estudiante = isset($lineas_estudiante[1]) ? trim($lineas_estudiante[1]) : '';
    $documento = isset($lineas_estudiante[2]) ? trim($lineas_estudiante[2]) : '';
    
    // Procesar sección
    $lineas_seccion = explode("\n", $seccion);
    $nombre_seccion = isset($lineas_seccion[0]) ? trim($lineas_seccion[0]) : '';
    $aula = isset($lineas_seccion[1]) ? trim($lineas_seccion[1]) : '';
    $capacidad_detalle = isset($lineas_seccion[2]) ? trim($lineas_seccion[2]) : '';
    
    // Procesar estado y tipo
    $lineas_estado = explode("\n", $estado_tipo);
    $estado = isset($lineas_estado[0]) ? trim($lineas_estado[0]) : '';
    $tipo = isset($lineas_estado[1]) ? trim($lineas_estado[1]) : '';
    
    // Determinar clase de estado
    $estado_class = 'estado-matriculado';
    if (stripos($estado, 'RETIRADO') !== false) {
        $estado_class = 'estado-retirado';
    } elseif (stripos($estado, 'TRASLADADO') !== false) {
        $estado_class = 'estado-trasladado';
    } elseif (stripos($estado, 'RESERVADO') !== false) {
        $estado_class = 'estado-reservado';
    }
    
    // Procesar capacidad y barra visual
    $capacidad_visual = '';
    $porcentaje = 0;
    if (preg_match('/(\d+)\/(\d+)/', $capacidad, $matches)) {
        $ocupados = intval($matches[1]);
        $total_cap = intval($matches[2]);
        $porcentaje = $total_cap > 0 ? round(($ocupados / $total_cap) * 100, 1) : 0;
        
        $barra_class = 'barra-normal';
        if ($porcentaje >= 100) {
            $barra_class = 'barra-completa';
        } elseif ($porcentaje >= 90) {
            $barra_class = 'barra-casi-llena';
        }
        
        $capacidad_visual = '<div class="capacidad-visual">
            <div class="capacidad-barra ' . $barra_class . '" style="width: ' . min($porcentaje, 100) . '%"></div>
        </div>';
    }
    
    // Limpiar observaciones (truncar si son muy largas)
    if (strlen($observaciones) > 100) {
        $observaciones = substr($observaciones, 0, 97) . '...';
    }
    if (empty($observaciones) || $observaciones === 'Sin observaciones') {
        $observaciones = '<span style="color: #6c757d; font-style: italic;">Sin observaciones</span>';
    }
    
    $html .= '<tr>
        <td class="col-codigo">
            <div class="codigo-matricula">' . htmlspecialchars($codigo_matricula) . '</div>
            <div style="font-size: 6px; color: #6c757d;">' . htmlspecialchars($periodo) . '</div>
        </td>
        <td class="col-estudiante" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_estudiante) . '</strong><br>
            <span class="estudiante-codigo">' . htmlspecialchars($codigo_estudiante) . '</span><br>
            <span style="font-size: 6px; color: #6c757d;">' . htmlspecialchars($documento) . '</span>
        </td>
        <td class="col-seccion" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_seccion) . '</strong><br>
            <span style="font-size: 6px; color: #6c757d;">' . htmlspecialchars($aula) . '</span>
        </td>
        <td class="col-estado" style="text-align: center;">
            <span class="badge-estado ' . $estado_class . '">' . htmlspecialchars($estado) . '</span><br>
            <span class="badge-tipo" style="margin-top: 2px;">' . htmlspecialchars($tipo) . '</span>
        </td>
        <td class="col-capacidad">
            ' . $capacidad_visual . '
            <div style="font-size: 6px; text-align: center; margin-top: 2px;">
                ' . htmlspecialchars($capacidad) . '<br>
                <span style="color: ' . ($porcentaje >= 100 ? '#dc3545' : ($porcentaje >= 90 ? '#ffc107' : '#28a745')) . '; font-weight: bold;">' . $porcentaje . '%</span>
            </div>
        </td>
        <td class="col-fecha" style="text-align: center; font-size: 7px;">' . $fecha . '</td>
        <td class="col-observaciones" style="font-size: 6px;">' . $observaciones . '</td>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// **Enviar PDF al navegador**
$filename = 'Reporte_Matriculas_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>