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
    $datosCompetencias = isset($_POST['datosCompetencias']) ? json_decode($_POST['datosCompetencias'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosCompetencias) || !isset($datosCompetencias['competencias'])) {
        die("<script>alert('No hay competencias disponibles para generar el reporte.'); window.close();</script>");
    }
    
    $area_nombre = htmlspecialchars($datosCompetencias['area_nombre'] ?? 'Área no especificada');
    $area_codigo = htmlspecialchars($datosCompetencias['area_codigo'] ?? 'Sin código');
    $competenciasData = $datosCompetencias['competencias'];
    
    // Calcular estadísticas
    $total_niveles = 0;
    $total_grados = 0;
    $total_competencias = 0;
    $total_con_capacidades = 0;
    $total_con_estandares = 0;
    $competencias_completas = 0;
    
    $niveles_presentes = [];
    
    foreach ($competenciasData as $nivel => $grados) {
        if (!empty($grados)) {
            $total_niveles++;
            $niveles_presentes[] = strtoupper($nivel);
            
            foreach ($grados as $grado => $competencias) {
                if (!empty($competencias)) {
                    $total_grados++;
                    
                    foreach ($competencias as $comp) {
                        $total_competencias++;
                        
                        $tiene_capacidades = !empty(trim($comp['capacidades'] ?? ''));
                        $tiene_estandares = !empty(trim($comp['estandares'] ?? ''));
                        
                        if ($tiene_capacidades) $total_con_capacidades++;
                        if ($tiene_estandares) $total_con_estandares++;
                        if ($tiene_capacidades && $tiene_estandares) $competencias_completas++;
                    }
                }
            }
        }
    }
    
    $porcentaje_completas = $total_competencias > 0 
        ? round(($competencias_completas / $total_competencias) * 100, 1) 
        : 0;
    
} catch (PDOException $e) {
    die("<script>alert('Error al obtener datos: " . $e->getMessage() . "'); window.close();</script>");
}

// Estilos CSS del PDF
$html = '<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        color: #333;
        margin: 0;
        padding: 0;
    }

    #tabla-cabecera, #tabla-competencias {
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
        font-size: 9px;
        text-align: center;
        border-right: 1px solid #999;
    }

    .estadisticas-resumen td:last-child {
        border-right: none;
    }

    .stat-numero {
        font-size: 14px;
        font-weight: bold;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 8px;
        color: #666;
    }

    .nivel-header {
        background-color: #A8D8EA;
        padding: 8px;
        margin-top: 15px;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 13px;
        border-radius: 5px;
        text-align: center;
        color: #333;
    }

    .grado-header {
        background-color: #E8F4F8;
        padding: 6px 10px;
        margin-top: 8px;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 11px;
        border-left: 4px solid #A8D8EA;
        color: #495057;
    }

    .competencia-item {
        margin-bottom: 10px;
        padding: 8px;
        border: 0.5px solid #dee2e6;
        border-radius: 5px;
        background-color: #ffffff;
        page-break-inside: avoid;
    }

    .competencia-numero {
        display: inline-block;
        width: 25px;
        height: 25px;
        background-color: #667eea;
        color: white;
        text-align: center;
        line-height: 25px;
        border-radius: 50%;
        font-weight: bold;
        font-size: 11px;
        margin-right: 8px;
    }

    .competencia-texto {
        font-weight: bold;
        color: #212529;
        font-size: 10px;
        margin-bottom: 5px;
        line-height: 1.4;
    }

    .competencia-detalle {
        margin-top: 5px;
        padding-left: 35px;
    }

    .detalle-label {
        font-weight: bold;
        color: #6c757d;
        font-size: 8px;
        display: inline-block;
        min-width: 80px;
    }

    .detalle-texto {
        color: #495057;
        font-size: 8px;
        line-height: 1.3;
    }

    .badge-capacidades {
        background-color: #FFAAA5;
        color: #333;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 7px;
        font-weight: bold;
    }

    .badge-estandares {
        background-color: #C7CEEA;
        color: #333;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 7px;
        font-weight: bold;
    }

    .pie-pagina {
        margin-top: 20px;
        padding: 12px;
        font-size: 9px;
        border: 0.5px solid #333;
        border-radius: 10px;
        text-align: center;
        background-color: #f8f9fa;
    }

    .info-area {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 10px;
    }

    .sin-datos {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        font-size: 9px;
        padding: 5px;
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
                <h4>COMPETENCIAS POR ÁREA</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Información del Área**
$html .= '<div class="info-area">
    <strong>Área Curricular:</strong> ' . $area_nombre . ' | 
    <strong>Niveles:</strong> ' . implode(', ', $niveles_presentes) . '
</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero">' . $total_niveles . '</span>
                <span class="stat-label">Niveles Educativos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFAAA5;">' . $total_grados . '</span>
                <span class="stat-label">Grados Configurados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #667eea;">' . $total_competencias . '</span>
                <span class="stat-label">Total Competencias</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $total_con_capacidades . '</span>
                <span class="stat-label">Con Capacidades</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $total_con_estandares . '</span>
                <span class="stat-label">Con Estándares</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #28a745;">' . $competencias_completas . '</span>
                <span class="stat-label">Completas</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #ffc107;">' . $porcentaje_completas . '%</span>
                <span class="stat-label">% Completas</span>
            </td>
        </tr>
    </table>
</div>';

// **Contenido de Competencias**
$numero_global = 1;

foreach ($competenciasData as $nivel => $grados) {
    if (empty($grados)) continue;
    
    $html .= '<div class="nivel-header">' . strtoupper($nivel) . '</div>';
    
    foreach ($grados as $grado => $competencias) {
        if (empty($competencias)) continue;
        
        $html .= '<div class="grado-header">' . htmlspecialchars($grado) . '</div>';
        
        foreach ($competencias as $comp) {
            $texto_competencia = htmlspecialchars(trim($comp['texto']));
            $capacidades = trim($comp['capacidades'] ?? '');
            $estandares = trim($comp['estandares'] ?? '');
            
            $html .= '<div class="competencia-item">';
            $html .= '<div>';
            $html .= '<span class="competencia-numero">' . $numero_global . '</span>';
            $html .= '<span class="competencia-texto">' . $texto_competencia . '</span>';
            $html .= '</div>';
            
            // Capacidades
            if (!empty($capacidades)) {
                $html .= '<div class="competencia-detalle">';
                $html .= '<span class="badge-capacidades">CAPACIDADES</span><br>';
                $html .= '<span class="detalle-texto">' . nl2br(htmlspecialchars($capacidades)) . '</span>';
                $html .= '</div>';
            }
            
            // Estándares
            if (!empty($estandares)) {
                $html .= '<div class="competencia-detalle">';
                $html .= '<span class="badge-estandares">ESTÁNDARES</span><br>';
                $html .= '<span class="detalle-texto">' . nl2br(htmlspecialchars($estandares)) . '</span>';
                $html .= '</div>';
            }
            
            // Indicar si falta información
            if (empty($capacidades) && empty($estandares)) {
                $html .= '<div class="competencia-detalle sin-datos">Sin capacidades ni estándares definidos</div>';
            } elseif (empty($capacidades)) {
                $html .= '<div class="competencia-detalle sin-datos">Sin capacidades definidas</div>';
            } elseif (empty($estandares)) {
                $html .= '<div class="competencia-detalle sin-datos">Sin estándares definidos</div>';
            }
            
            $html .= '</div>';
            
            $numero_global++;
        }
    }
}

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <strong>Área:</strong> ' . $area_nombre . ' | 
    <strong>Total Competencias:</strong> ' . $total_competencias . ' | 
    <strong>Competencias Completas:</strong> ' . $competencias_completas . ' (' . $porcentaje_completas . '%)
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
$area_filename = preg_replace('/[^A-Za-z0-9_]/', '_', $area_nombre);
$filename = 'Competencias_' . $area_filename . '_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>