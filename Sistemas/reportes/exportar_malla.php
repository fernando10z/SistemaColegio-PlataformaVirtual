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
    $datosMalla = isset($_POST['datosMalla']) ? json_decode($_POST['datosMalla'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosMalla)) {
        die("<script>alert('No hay registros de malla curricular disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_asignaciones = count($datosMalla);
    $horas_totales_sistema = 0;
    $asignaciones_correctas = 0;
    $asignaciones_sobrecarga = 0;
    $asignaciones_subcarga = 0;
    
    $niveles_conteo = [];
    $areas_conteo = [];
    $horas_por_grado = [];
    $grados_con_problemas = [];
    
    foreach ($datosMalla as $row) {
        // Nivel (columna 0)
        $nivel_completo = trim($row[0]);
        if (!empty($nivel_completo)) {
            if (!isset($niveles_conteo[$nivel_completo])) {
                $niveles_conteo[$nivel_completo] = 0;
            }
            $niveles_conteo[$nivel_completo]++;
        }
        
        // Área (columna 1)
        $area = trim($row[1]);
        if (!empty($area)) {
            if (!isset($areas_conteo[$area])) {
                $areas_conteo[$area] = 0;
            }
            $areas_conteo[$area]++;
        }
        
        // Horas semanales (columna 2)
        $horas_texto = trim($row[2]);
        if (preg_match('/(\d+)\s*hrs?/i', $horas_texto, $matches)) {
            $horas = intval($matches[1]);
            $horas_totales_sistema += $horas;
        }
        
        // Validación (columna 5 y 6)
        $validacion = trim($row[5]);
        $total_horas_grado = trim($row[6]);
        
        if ($validacion === 'CORRECTO') {
            $asignaciones_correctas++;
        } elseif ($validacion === 'SOBRECARGA') {
            $asignaciones_sobrecarga++;
            // Registrar grado con problema
            if (!in_array($nivel_completo, $grados_con_problemas)) {
                $grados_con_problemas[] = $nivel_completo . ' (' . $total_horas_grado . ')';
            }
        } elseif ($validacion === 'SUBCARGA') {
            $asignaciones_subcarga++;
            if (!in_array($nivel_completo, $grados_con_problemas)) {
                $grados_con_problemas[] = $nivel_completo . ' (' . $total_horas_grado . ')';
            }
        }
        
        // Acumular horas por grado
        if (!isset($horas_por_grado[$nivel_completo])) {
            $horas_por_grado[$nivel_completo] = 0;
        }
        if (isset($horas)) {
            $horas_por_grado[$nivel_completo] += $horas;
        }
    }
    
    $promedio_horas = $total_asignaciones > 0 ? round($horas_totales_sistema / $total_asignaciones, 1) : 0;
    
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

    #tabla-cabecera, #tabla-malla {
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

    #tabla-malla td, #tabla-malla th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-malla th {
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
        color: white;
    }

    .nivel-ini { background-color: #28a745; }
    .nivel-pri { background-color: #A8D8EA; color: #333; }
    .nivel-sec { background-color: #C7CEEA; color: #333; }
    .nivel-otro { background-color: #6c757d; }

    .badge-horas {
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 7px;
        font-weight: bold;
    }

    .horas-baja { background-color: #f8f9fa; color: #333; border: 1px solid #dee2e6; }
    .horas-media { background-color: #A8D8EA; color: #333; }
    .horas-alta { background-color: #FFAAA5; color: #333; }
    .horas-muy-alta { background-color: #dc3545; color: white; }

    .badge-validacion {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .validacion-correcto { background-color: #28a745; }
    .validacion-sobrecarga { background-color: #dc3545; }
    .validacion-subcarga { background-color: #ffc107; color: #856404; }

    .col-nivel { width: 15%; }
    .col-area { width: 20%; }
    .col-horas { width: 10%; }
    .col-competencias { width: 25%; }
    .col-periodo { width: 12%; }
    .col-validacion { width: 12%; }

    .competencia-item {
        font-size: 6px;
        color: #495057;
        margin: 1px 0;
        padding: 1px 3px;
        background-color: #f8f9fa;
        border-left: 2px solid #C7CEEA;
        border-radius: 2px;
    }

    .area-codigo {
        font-size: 6px;
        color: #6c757d;
        font-style: italic;
    }

    .alerta-validacion {
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

    .resumen-horas {
        background-color: #e3f2fd;
        border: 1px solid #2196f3;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 8px;
        text-align: center;
        color: #1976d2;
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
                <h4>MALLA CURRICULAR</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Alertas de validación**
if ($asignaciones_sobrecarga > 0 || $asignaciones_subcarga > 0) {
    $html .= '<div class="alerta-validacion">';
    $html .= '⚠ ATENCIÓN: ';
    if ($asignaciones_sobrecarga > 0) {
        $html .= $asignaciones_sobrecarga . ' grado(s) con SOBRECARGA de horas. ';
    }
    if ($asignaciones_subcarga > 0) {
        $html .= $asignaciones_subcarga . ' grado(s) con SUBCARGA de horas. ';
    }
    if (!empty($grados_con_problemas)) {
        $html .= 'Grados afectados: ' . implode(', ', array_slice($grados_con_problemas, 0, 5));
        if (count($grados_con_problemas) > 5) {
            $html .= ' y ' . (count($grados_con_problemas) - 5) . ' más';
        }
    }
    $html .= '</div>';
}

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_asignaciones . '</span>
                <span class="stat-label">Asignaciones</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $asignaciones_correctas . '</span>
                <span class="stat-label">Correctas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #dc3545;">' . $asignaciones_sobrecarga . '</span>
                <span class="stat-label">Sobrecarga</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #ffc107;">' . $asignaciones_subcarga . '</span>
                <span class="stat-label">Subcarga</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . count($niveles_conteo) . '</span>
                <span class="stat-label">Niveles</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . count($areas_conteo) . '</span>
                <span class="stat-label">Áreas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . $horas_totales_sistema . '</span>
                <span class="stat-label">Horas Totales</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . $promedio_horas . '</span>
                <span class="stat-label">Promedio Horas</span>
            </td>
        </tr>
    </table>
</div>';

// **Sección de listado**
$html .= '<div class="seccion-titulo">ASIGNACIÓN DE ÁREAS CURRICULARES POR NIVEL Y GRADO</div>';
$html .= '<table id="tabla-malla">
    <thead>
        <tr>
            <th class="col-nivel">Nivel / Grado</th>
            <th class="col-area">Área Curricular</th>
            <th class="col-horas">Horas</th>
            <th class="col-competencias">Competencias Asociadas</th>
            <th class="col-periodo">Período</th>
            <th class="col-validacion">Validación</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosMalla as $row) {
    // Extraer datos de cada columna
    $nivel_grado = trim($row[0]);
    $area = trim($row[1]);
    $horas = trim($row[2]);
    $competencias = trim($row[3]);
    $periodo = htmlspecialchars(trim($row[4]));
    $validacion = trim($row[5]);
    $total_horas_grado = trim($row[6]);
    $codigo_nivel = isset($row[7]) ? trim($row[7]) : '';
    $codigo_area = isset($row[8]) ? trim($row[8]) : '';
    
    // Determinar clase de nivel según código
    $nivel_class = 'nivel-otro';
    if (stripos($codigo_nivel, 'INI') !== false) {
        $nivel_class = 'nivel-ini';
    } elseif (stripos($codigo_nivel, 'PRI') !== false) {
        $nivel_class = 'nivel-pri';
    } elseif (stripos($codigo_nivel, 'SEC') !== false) {
        $nivel_class = 'nivel-sec';
    }
    
    // Determinar clase de horas
    $horas_numero = 0;
    if (preg_match('/(\d+)/', $horas, $matches)) {
        $horas_numero = intval($matches[1]);
    }
    
    $horas_class = 'horas-baja';
    if ($horas_numero > 6) {
        $horas_class = 'horas-muy-alta';
    } elseif ($horas_numero > 4) {
        $horas_class = 'horas-alta';
    } elseif ($horas_numero > 2) {
        $horas_class = 'horas-media';
    }
    
    // Determinar clase de validación
    $validacion_class = 'validacion-correcto';
    if ($validacion === 'SOBRECARGA') {
        $validacion_class = 'validacion-sobrecarga';
    } elseif ($validacion === 'SUBCARGA') {
        $validacion_class = 'validacion-subcarga';
    }
    
    // Procesar competencias
    $competencias_html = '';
    if (!empty($competencias) && $competencias !== 'No definidas') {
        $comps_array = explode('|||', $competencias);
        $contador = 0;
        foreach ($comps_array as $comp) {
            $comp = trim($comp);
            if (!empty($comp) && $contador < 3) {
                $competencias_html .= '<div class="competencia-item">• ' . htmlspecialchars($comp) . '</div>';
                $contador++;
            }
        }
        if (count($comps_array) > 3) {
            $competencias_html .= '<div class="competencia-item" style="text-align: center; font-style: italic;">+' . (count($comps_array) - 3) . ' más...</div>';
        }
    } else {
        $competencias_html = '<div style="text-align: center; color: #6c757d; font-size: 6px;">No definidas</div>';
    }
    
    // Agregar código de área si existe
    $area_completo = $area;
    if (!empty($codigo_area) && $codigo_area !== 'S/C') {
        $area_completo .= '<br><span class="area-codigo">Código: ' . htmlspecialchars($codigo_area) . '</span>';
    }
    
    $html .= '<tr>
        <td class="col-nivel" style="font-size: 7px;">
            <span class="badge-nivel ' . $nivel_class . '">' . htmlspecialchars($codigo_nivel) . '</span><br>
            <strong>' . htmlspecialchars($nivel_grado) . '</strong>
        </td>
        <td class="col-area" style="font-size: 7px;">' . $area_completo . '</td>
        <td class="col-horas" style="text-align: center;">
            <span class="badge-horas ' . $horas_class . '">' . htmlspecialchars($horas) . '</span>
        </td>
        <td class="col-competencias">' . $competencias_html . '</td>
        <td class="col-periodo" style="font-size: 6px; text-align: center;">' . $periodo . '</td>
        <td class="col-validacion" style="text-align: center;">
            <span class="badge-validacion ' . $validacion_class . '">' . htmlspecialchars($validacion) . '</span>
            <br>
            <span style="font-size: 6px; color: #6c757d;">Total: ' . htmlspecialchars($total_horas_grado) . '</span>
        </td>
    </tr>';
}

$html .= '</tbody></table>';

// **Resumen de horas por grado**
if (!empty($horas_por_grado)) {
    $html .= '<div class="resumen-horas">';
    $html .= '<strong>RESUMEN DE HORAS POR GRADO:</strong> ';
    $contador = 0;
    foreach ($horas_por_grado as $grado => $horas) {
        if ($contador > 0) $html .= ' | ';
        $html .= htmlspecialchars($grado) . ': ' . $horas . 'h';
        $contador++;
        if ($contador >= 8) {
            $html .= ' | ...';
            break;
        }
    }
    $html .= '</div>';
}

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <strong>Total Asignaciones:</strong> ' . $total_asignaciones . ' | 
    <strong>Horas Totales Sistema:</strong> ' . $horas_totales_sistema . ' | 
    <strong>Promedio:</strong> ' . $promedio_horas . ' horas/asignación
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
$filename = 'Reporte_Malla_Curricular_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>