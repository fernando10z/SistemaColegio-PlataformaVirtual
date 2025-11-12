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
    $datosAsignaciones = isset($_POST['datosAsignaciones']) ? json_decode($_POST['datosAsignaciones'], true) : [];
    
    // Si no hay datos, mostrar alerta
    if (empty($datosAsignaciones)) {
        die("<script>alert('No hay registros de asignaciones disponibles para generar el reporte.'); window.close();</script>");
    }
    
    // Calcular estadísticas solo de datos filtrados
    $total_asignaciones = count($datosAsignaciones);
    $tutores_asignados = 0;
    $docentes_unicos = [];
    $carga_total = 0;
    $total_estudiantes = 0;
    
    $docentes_sobrecargados = [];
    $areas_conteo = [];
    $niveles_conteo = [];
    $carga_por_docente = [];
    
    foreach ($datosAsignaciones as $row) {
        // Docente (columna 0)
        $docente_texto = trim($row[0]);
        $lineas_docente = explode("\n", $docente_texto);
        $docente_nombre = isset($lineas_docente[0]) ? trim($lineas_docente[0]) : '';
        
        if (!in_array($docente_nombre, $docentes_unicos)) {
            $docentes_unicos[] = $docente_nombre;
        }
        
        // Área (columna 2)
        $area_texto = trim($row[2]);
        $lineas_area = explode("\n", $area_texto);
        $area_nombre = isset($lineas_area[1]) ? trim($lineas_area[1]) : '';
        if (!empty($area_nombre)) {
            $areas_conteo[$area_nombre] = ($areas_conteo[$area_nombre] ?? 0) + 1;
        }
        
        // Carga horaria (columna 3)
        $carga_texto = trim($row[3]);
        if (preg_match('/(\d+)h/', $carga_texto, $matches)) {
            $horas = intval($matches[1]);
            $carga_total += $horas;
            
            // Acumular por docente
            if (!isset($carga_por_docente[$docente_nombre])) {
                $carga_por_docente[$docente_nombre] = [
                    'horas' => 0,
                    'asignaciones' => 0
                ];
            }
            $carga_por_docente[$docente_nombre]['horas'] += $horas;
            $carga_por_docente[$docente_nombre]['asignaciones']++;
            
            // Detectar sobrecargados
            if ($horas > 30) {
                if (!in_array($docente_nombre, $docentes_sobrecargados)) {
                    $docentes_sobrecargados[] = $docente_nombre . ' (' . $horas . 'h)';
                }
            }
        }
        
        // Estudiantes (columna 4)
        $estudiantes_texto = trim($row[4]);
        if (preg_match('/(\d+)/', $estudiantes_texto, $matches)) {
            $total_estudiantes += intval($matches[1]);
        }
        
        // Tutoría (columna 5)
        $tutoria_texto = trim($row[5]);
        if (stripos($tutoria_texto, 'Tutor') !== false) {
            $tutores_asignados++;
        }
        
        // Nivel desde sección (columna 1)
        $seccion_texto = trim($row[1]);
        $lineas_seccion = explode("\n", $seccion_texto);
        $nivel_nombre = isset($lineas_seccion[0]) ? trim($lineas_seccion[0]) : '';
        if (!empty($nivel_nombre)) {
            $niveles_conteo[$nivel_nombre] = ($niveles_conteo[$nivel_nombre] ?? 0) + 1;
        }
    }
    
    $docentes_activos = count($docentes_unicos);
    $carga_promedio = $docentes_activos > 0 ? round($carga_total / $docentes_activos, 1) : 0;
    
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

    #tabla-cabecera, #tabla-asignaciones {
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

    #tabla-asignaciones td, #tabla-asignaciones th {
        border: 0.5px solid #333;
        padding: 4px;
        font-size: 7px;
        text-align: left;
        vertical-align: top;
    }

    #tabla-asignaciones th {
        background-color: #C7CEEA;
        font-weight: bold;
        text-align: center;
        color: #2C3E50;
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
        font-size: 14px;
        font-weight: bold;
        display: block;
    }

    .stat-label {
        font-size: 7px;
        color: #666;
    }

    .badge-tutor {
        padding: 2px 5px;
        border-radius: 6px;
        font-size: 6px;
        font-weight: bold;
        color: white;
    }

    .tutor-si { background-color: #28a745; }
    .tutor-no { background-color: #6c757d; }

    .badge-area {
        padding: 2px 5px;
        border-radius: 4px;
        font-size: 6px;
        font-weight: bold;
        background-color: #667eea;
        color: white;
    }

    .col-docente { width: 20%; }
    .col-seccion { width: 18%; }
    .col-area { width: 15%; }
    .col-carga { width: 10%; }
    .col-estudiantes { width: 10%; }
    .col-tutoria { width: 10%; }
    .col-horarios { width: 17%; }

    .docente-codigo {
        font-size: 6px;
        color: #6c757d;
        font-family: monospace;
    }

    .carga-visual {
        background-color: #e9ecef;
        border-radius: 6px;
        height: 8px;
        position: relative;
        margin: 2px 0;
    }

    .carga-barra {
        height: 100%;
        border-radius: 6px;
    }

    .barra-normal { background-color: #28a745; }
    .barra-alta { background-color: #fd7e14; }
    .barra-critica { background-color: #dc3545; }

    .alerta-sobrecarga {
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

    .resumen-carga {
        background-color: #e8f5e9;
        border: 1px solid #4caf50;
        border-radius: 5px;
        padding: 8px;
        margin: 10px 0;
        font-size: 9px;
        text-align: center;
        color: #2e7d32;
    }

    .horario-item {
        background-color: #f8f9fa;
        border-radius: 3px;
        padding: 1px 3px;
        font-size: 6px;
        margin: 1px;
        display: inline-block;
    }

    .carga-numero {
        font-weight: bold;
        font-size: 8px;
    }

    .carga-normal { color: #28a745; }
    .carga-alta { color: #fd7e14; }
    .carga-critica { color: #dc3545; }
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
                <h4>ASIGNACIONES DOCENTES</h4>
                <div>Fecha: ' . date('d/m/Y') . '</div>
                <div>Hora: ' . date('H:i:s') . '</div>
            </div>
        </td>
    </tr>
</table>';

// **Alerta de docentes sobrecargados**
if (count($docentes_sobrecargados) > 0) {
    $html .= '<div class="alerta-sobrecarga">';
    $html .= '⚠ ATENCIÓN: ' . count($docentes_sobrecargados) . ' docente(s) con más de 30 horas semanales. ';
    if (!empty($docentes_sobrecargados)) {
        $html .= 'Docentes: ' . implode(', ', array_slice($docentes_sobrecargados, 0, 3));
        if (count($docentes_sobrecargados) > 3) {
            $html .= ' y ' . (count($docentes_sobrecargados) - 3) . ' más';
        }
    }
    $html .= '</div>';
}

// **Resumen de carga académica**
$html .= '<div class="resumen-carga">';
$html .= 'CARGA PROMEDIO: ' . $carga_promedio . ' horas/docente | ';
$html .= 'TOTAL ESTUDIANTES: ' . number_format($total_estudiantes) . ' | ';
$html .= 'CARGA TOTAL SISTEMA: ' . number_format($carga_total) . ' horas semanales';
$html .= '</div>';

// **Estadísticas Resumidas**
$html .= '<div class="estadisticas-resumen">
    <table>
        <tr>
            <td>
                <span class="stat-numero" style="color: #A8D8EA;">' . $total_asignaciones . '</span>
                <span class="stat-label">Total Asignaciones</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFB3BA;">' . $docentes_activos . '</span>
                <span class="stat-label">Docentes Activos</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #BAE1FF;">' . $tutores_asignados . '</span>
                <span class="stat-label">Tutores Asignados</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #FFDDC1;">' . $carga_promedio . 'h</span>
                <span class="stat-label">Carga Promedio</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #C7CEEA;">' . $total_estudiantes . '</span>
                <span class="stat-label">Total Estudiantes</span>
            </td>
            <td>
                <span class="stat-numero" style="color: #667eea;">' . count($areas_conteo) . '</span>
                <span class="stat-label">Áreas Curriculares</span>
            </td>
        </tr>
    </table>
</div>';

// **Sección de listado**
$html .= '<div class="seccion-titulo">REGISTRO DETALLADO DE ASIGNACIONES DOCENTES</div>';
$html .= '<table id="tabla-asignaciones">
    <thead>
        <tr>
            <th class="col-docente">Docente</th>
            <th class="col-seccion">Sección</th>
            <th class="col-area">Área Curricular</th>
            <th class="col-carga">Carga Horaria</th>
            <th class="col-estudiantes">Estudiantes</th>
            <th class="col-tutoria">Tutoría</th>
            <th class="col-horarios">Horarios</th>
        </tr>
    </thead>
    <tbody>';

foreach ($datosAsignaciones as $row) {
    // Extraer datos de cada columna
    $docente = trim($row[0]);
    $seccion = trim($row[1]);
    $area = trim($row[2]);
    $carga = trim($row[3]);
    $estudiantes = trim($row[4]);
    $tutoria = trim($row[5]);
    $horarios = trim($row[6]);
    
    // Procesar docente (nombre + código)
    $lineas_docente = explode("\n", $docente);
    $nombre_docente = isset($lineas_docente[0]) ? trim($lineas_docente[0]) : '';
    $codigo_docente = isset($lineas_docente[1]) ? trim($lineas_docente[1]) : '';
    
    // Procesar sección (nivel + grado-sección + aula)
    $lineas_seccion = explode("\n", $seccion);
    $nivel_nombre = isset($lineas_seccion[0]) ? trim($lineas_seccion[0]) : '';
    $grado_seccion = isset($lineas_seccion[1]) ? trim($lineas_seccion[1]) : '';
    $aula = isset($lineas_seccion[2]) ? trim($lineas_seccion[2]) : '';
    
    // Procesar área (código + nombre)
    $lineas_area = explode("\n", $area);
    $area_codigo = isset($lineas_area[0]) ? trim($lineas_area[0]) : '';
    $area_nombre = isset($lineas_area[1]) ? trim($lineas_area[1]) : '';
    
    // Procesar carga horaria y crear barra visual
    $carga_visual = '';
    $horas = 0;
    $carga_class = 'carga-normal';
    if (preg_match('/(\d+)h/', $carga, $matches)) {
        $horas = intval($matches[1]);
        
        // Calcular porcentaje (30h = 100%)
        $porcentaje = min(($horas / 30) * 100, 100);
        
        $barra_class = 'barra-normal';
        $carga_class = 'carga-normal';
        if ($horas > 30) {
            $barra_class = 'barra-critica';
            $carga_class = 'carga-critica';
        } elseif ($horas > 20) {
            $barra_class = 'barra-alta';
            $carga_class = 'carga-alta';
        }
        
        $carga_visual = '<div class="carga-visual">
            <div class="carga-barra ' . $barra_class . '" style="width: ' . $porcentaje . '%"></div>
        </div>';
    }
    
    // Procesar tutoría
    $tutoria_class = 'tutor-no';
    $tutoria_texto = 'NO';
    if (stripos($tutoria, 'Tutor') !== false) {
        $tutoria_class = 'tutor-si';
        $tutoria_texto = 'TUTOR';
    }
    
    // Procesar horarios
    $horarios_html = '';
    if (!empty($horarios) && $horarios !== 'Sin horarios definidos') {
        // Dividir por saltos de línea
        $horarios_items = explode("\n", $horarios);
        foreach ($horarios_items as $horario_item) {
            $horario_item = trim($horario_item);
            if (!empty($horario_item)) {
                $horarios_html .= '<span class="horario-item">' . htmlspecialchars($horario_item) . '</span> ';
            }
        }
    } else {
        $horarios_html = '<span style="color: #6c757d; font-style: italic; font-size: 6px;">Sin horarios</span>';
    }
    
    $html .= '<tr>
        <td class="col-docente" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nombre_docente) . '</strong><br>
            <span class="docente-codigo">' . htmlspecialchars($codigo_docente) . '</span>
        </td>
        <td class="col-seccion" style="font-size: 7px;">
            <strong>' . htmlspecialchars($nivel_nombre) . '</strong><br>
            <span style="color: #495057;">' . htmlspecialchars($grado_seccion) . '</span><br>
            <span style="font-size: 6px; color: #6c757d;">' . htmlspecialchars($aula) . '</span>
        </td>
        <td class="col-area" style="font-size: 7px;">
            <span class="badge-area">' . htmlspecialchars($area_codigo) . '</span><br>
            <span style="font-size: 6px; margin-top: 2px;">' . htmlspecialchars($area_nombre) . '</span>
        </td>
        <td class="col-carga">
            ' . $carga_visual . '
            <div style="text-align: center; margin-top: 2px;">
                <span class="carga-numero ' . $carga_class . '">' . $horas . 'h</span><br>
                <span style="font-size: 6px; color: #6c757d;">por semana</span>
            </div>
        </td>
        <td class="col-estudiantes" style="text-align: center; font-size: 8px;">
            <strong style="color: #495057;">' . htmlspecialchars($estudiantes) . '</strong><br>
            <span style="font-size: 6px; color: #6c757d;">estudiantes</span>
        </td>
        <td class="col-tutoria" style="text-align: center;">
            <span class="badge-tutor ' . $tutoria_class . '">' . $tutoria_texto . '</span>
        </td>
        <td class="col-horarios" style="font-size: 6px;">
            ' . $horarios_html . '
        </td>
    </tr>';
}

$html .= '</tbody></table>';

// **Resumen por área curricular**
if (!empty($areas_conteo)) {
    $html .= '<div class="seccion-titulo" style="font-size: 12px; margin-top: 20px;">DISTRIBUCIÓN POR ÁREA CURRICULAR</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <thead>
            <tr style="background-color: #C7CEEA;">
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Área Curricular</th>
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Asignaciones</th>
            </tr>
        </thead>
        <tbody>';
    
    $fill = false;
    foreach ($areas_conteo as $area_nom => $count) {
        $bg = $fill ? '#f8f9fa' : 'white';
        $html .= '<tr style="background-color: ' . $bg . ';">
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px;">' . htmlspecialchars($area_nom) . '</td>
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px; text-align: center;"><strong>' . $count . '</strong></td>
        </tr>';
        $fill = !$fill;
    }
    
    $html .= '</tbody></table>';
}

// **Resumen de carga por docente**
if (!empty($carga_por_docente)) {
    $html .= '<div class="seccion-titulo" style="font-size: 12px; margin-top: 20px;">CARGA ACADÉMICA POR DOCENTE</div>';
    $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <thead>
            <tr style="background-color: #C7CEEA;">
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Docente</th>
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Asignaciones</th>
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Horas</th>
                <th style="border: 0.5px solid #333; padding: 6px; font-size: 9px; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>';
    
    $fill = false;
    foreach ($carga_por_docente as $docente_nom => $info) {
        $bg = $fill ? '#f8f9fa' : 'white';
        
        // Determinar estado
        $estado = 'NORMAL';
        $estado_color = '#28a745';
        if ($info['horas'] > 30) {
            $estado = 'CRÍTICA';
            $estado_color = '#dc3545';
        } elseif ($info['horas'] > 20) {
            $estado = 'ALTA';
            $estado_color = '#fd7e14';
        }
        
        $html .= '<tr style="background-color: ' . $bg . ';">
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px;">' . htmlspecialchars($docente_nom) . '</td>
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px; text-align: center;">' . $info['asignaciones'] . '</td>
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px; text-align: center;"><strong>' . $info['horas'] . ' h</strong></td>
            <td style="border: 0.5px solid #333; padding: 4px; font-size: 8px; text-align: center; color: ' . $estado_color . '; font-weight: bold;">' . $estado . '</td>
        </tr>';
        $fill = !$fill;
    }
    
    $html .= '</tbody></table>';
}

// **Pie de página**
$html .= '<div class="pie-pagina">
    <strong>Reporte generado por:</strong> ' . htmlspecialchars($nombreRol) . '<br>
    <strong>Fecha y hora de generación:</strong> ' . date('d/m/Y H:i:s') . '<br>
    <em style="font-size: 9px; color: #6c757d;">Este reporte contiene información confidencial de asignaciones docentes del periodo académico activo.</em>
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
$filename = 'Reporte_Asignaciones_Docentes_' . date('Y-m-d_H-i-s') . '.pdf';
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$filename\"");
echo $dompdf->output();

exit;
?>