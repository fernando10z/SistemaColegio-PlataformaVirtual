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
    $datosPeriodos = isset($_POST['datosPeriodos']) ? json_decode($_POST['datosPeriodos'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosPeriodos)) {
        die("<script>alert('No hay registros de períodos académicos disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_periodos = count($datosPeriodos);
    $periodos_activos = 0;
    $periodos_inactivos = 0;
    $periodos_actuales = 0;
    $total_secciones = 0;
    $total_matriculas = 0;
    
    $tipos_periodo = ['BIMESTRE' => 0, 'TRIMESTRE' => 0, 'SEMESTRE' => 0];
    $periodos_por_anio = [];
    
    foreach ($datosPeriodos as $row) {
        // Contadores de estado (columna 10)
        $estado = trim($row[10]);
        if ($estado === 'ACTIVO') {
            $periodos_activos++;
        } else {
            $periodos_inactivos++;
        }
        
        // Período actual (columna 11)
        if (trim($row[11]) === 'SÍ') {
            $periodos_actuales++;
        }
        
        // Contadores de tipo (columna 3)
        $tipo = trim($row[3]);
        if (isset($tipos_periodo[$tipo])) {
            $tipos_periodo[$tipo]++;
        }
        
        // Contadores por año (columna 2)
        $anio = trim($row[2]);
        if (!isset($periodos_por_anio[$anio])) {
            $periodos_por_anio[$anio] = 0;
        }
        $periodos_por_anio[$anio]++;
        
        // Totales (columnas 7 y 8)
        $total_secciones += intval(trim($row[7]));
        $total_matriculas += intval(trim($row[8]));
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

    #tabla-cabecera, #tabla-periodos {
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

    #tabla-periodos td, #tabla-periodos th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-periodos th {
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

    .badge-tipo {
        padding: 2px 5px;
        border-radius: 8px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .tipo-bimestre { background-color: #A8D8EA; color: #333; }
    .tipo-trimestre { background-color: #FFAAA5; color: #333; }
    .tipo-semestre { background-color: #C7CEEA; color: #333; }

    .badge-estado {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .estado-activo { background-color: #28a745; }
    .estado-inactivo { background-color: #6c757d; }

    .badge-actual {
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
        background-color: #0d6efd;
        color: white;
    }

    .col-id { width: 3%; }
    .col-periodo { width: 20%; }
    .col-anio { width: 5%; }
    .col-tipo { width: 8%; }
    .col-fechas { width: 15%; }
    .col-evaluaciones { width: 18%; }
    .col-secciones { width: 8%; }
    .col-matriculas { width: 8%; }
    .col-duracion { width: 7%; }
    .col-estado { width: 8%; }

    .tipos-resumen {
        background-color: #f8f9fa;
        padding: 8px;
        margin: 10px 0;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        font-size: 8px;
        text-align: center;
    }

    .evaluacion-mini {
        font-size: 6px;
        color: #495057;
        margin: 2px 0;
        padding: 1px 3px;
        background-color: #f8f9fa;
        border-radius: 3px;
    }

    .duracion-badge {
        background-color: #FFDDC1;
        color: #856404;
        font-size: 6px;
        padding: 2px 4px;
        border-radius: 4px;
        font-weight: bold;
    }

    .filtro-aplicado {
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
            <div' . htmlspecialchars($config['refran']) . '</div>
            <div class="info-empresa">RUC: ' . htmlspecialchars($config['ruc']) . '</div>
            <div class="info-empresa">' . htmlspecialchars($config['direccion']) . '</div>
        </td>
        <td style="width: 30%;">
            <div class="reporte-titulo">
                <h4>PERÍODOS ACADÉMICOS</h4>
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
                <span class="stat-numero">' . $total_periodos . '</span>
                <span class="stat-label">Total Períodos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $periodos_activos . '</span>
                <span class="stat-label">Períodos Activos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #6c757d;">' . $periodos_inactivos . '</span>
                <span class="stat-label">Períodos Inactivos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #0d6efd;">' . $periodos_actuales . '</span>
                <span class="stat-label">Período Actual</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $tipos_periodo['BIMESTRE'] . '</span>
                <span class="stat-label">Bimestres</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . $tipos_periodo['TRIMESTRE'] . '</span>
                <span class="stat-label">Trimestres</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $tipos_periodo['SEMESTRE'] . '</span>
                <span class="stat-label">Semestres</span>
            </td>
        </tr>
    </table>
</div>';

// **Resumen de años**
$anios_html = '<div class="tipos-resumen"><strong>Períodos por Año:</strong> ';
foreach ($periodos_por_anio as $anio => $cantidad) {
    $anios_html .= htmlspecialchars($anio) . ' (' . $cantidad . ') | ';
}
$anios_html = rtrim($anios_html, ' | ');
$anios_html .= ' | <strong>Total Secciones:</strong> ' . number_format($total_secciones) . ' | <strong>Total Matrículas:</strong> ' . number_format($total_matriculas) . '</div>';
$html .= $anios_html;

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE PERÍODOS ACADÉMICOS</div>';
$html .= '<table id="tabla-periodos">
    <thead>
        <tr>
            <th class="col-id">ID</th>
            <th class="col-periodo">Período Académico</th>
            <th class="col-anio">Año</th>
            <th class="col-tipo">Tipo</th>
            <th class="col-fechas">Fechas del Período</th>
            <th class="col-evaluaciones">Períodos de Evaluación</th>
            <th class="col-secciones">Secciones</th>
            <th class="col-matriculas">Matrículas</th>
            <th class="col-duracion">Duración</th>
            <th class="col-estado">Estado</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosPeriodos as $row) {
    // Extraer datos de cada columna
    $id = htmlspecialchars(trim($row[0]));
    $periodo = htmlspecialchars(trim($row[1]));
    $anio = htmlspecialchars(trim($row[2]));
    $tipo = trim($row[3]);
    $fechas = htmlspecialchars(trim($row[4]));
    $num_evaluaciones = htmlspecialchars(trim($row[5]));
    $evaluaciones_texto = trim($row[6]);
    $secciones = htmlspecialchars(trim($row[7]));
    $matriculas = htmlspecialchars(trim($row[8]));
    $duracion = htmlspecialchars(trim($row[9]));
    $estado = trim($row[10]);
    $es_actual = trim($row[11]);
    
    // Determinar clase de tipo
    $tipo_lower = strtolower($tipo);
    $tipoClass = 'tipo-' . $tipo_lower;
    
    // Determinar clase de estado
    $estadoClass = $estado === 'ACTIVO' ? 'estado-activo' : 'estado-inactivo';
    
    // Badge de período actual
    $badge_actual = $es_actual === 'SÍ' ? '<br><span class="badge-actual">ACTUAL</span>' : '';
    
    // Procesar evaluaciones
    $evaluaciones_html = '';
    if (!empty($evaluaciones_texto)) {
        $evals = explode('|||', $evaluaciones_texto);
        foreach ($evals as $eval) {
            $eval = trim($eval);
            if (!empty($eval)) {
                $evaluaciones_html .= '<div class="evaluacion-mini">• ' . htmlspecialchars($eval) . '</div>';
            }
        }
    }
    if (empty($evaluaciones_html)) {
        $evaluaciones_html = '<div class="evaluacion-mini">' . $num_evaluaciones . '</div>';
    }
    
    // Truncar nombre si es muy largo
    if (strlen($periodo) > 80) {
        $periodo = substr($periodo, 0, 77) . '...';
    }
    
    // Limpiar y formatear fechas
    $fechas = str_replace(['calendar-event', 'ti ti-'], '', $fechas);
    $fechas = preg_replace('/\s+/', ' ', $fechas);
    
    $html .= '<tr>
        <td class="col-id" style="text-align: center;"><strong>' . $id . '</strong></td>
        <td class="col-periodo" style="font-size: 6px;">' . $periodo . $badge_actual . '</td>
        <td class="col-anio" style="text-align: center; font-weight: bold;">' . $anio . '</td>
        <td class="col-tipo" style="text-align: center;">
            <span class="badge-tipo ' . $tipoClass . '">' . strtoupper($tipo) . '</span>
        </td>
        <td class="col-fechas" style="font-size: 6px; text-align: center;">' . $fechas . '</td>
        <td class="col-evaluaciones" style="font-size: 6px;">' . $evaluaciones_html . '</td>
        <td class="col-secciones" style="text-align: center; font-size: 8px; font-weight: bold;">' . $secciones . '</td>
        <td class="col-matriculas" style="text-align: center; font-size: 8px; font-weight: bold;">' . $matriculas . '</td>
        <td class="col-duracion" style="text-align: center;">
            <span class="duracion-badge">' . $duracion . '</span>
        </td>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// **Enviar PDF al navegador**
$filename = 'Reporte_Periodos_Academicos_Filtrado_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>