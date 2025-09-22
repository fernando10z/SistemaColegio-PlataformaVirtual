-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-09-2025 a las 04:22:47
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `eva_colegio_aac`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `id` int NOT NULL,
  `codigo_activo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `datos_basicos` json DEFAULT NULL,
  `datos_adquisicion` json DEFAULT NULL,
  `ubicacion` json DEFAULT NULL,
  `estado` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPERATIVO',
  `configuracion` json DEFAULT NULL,
  `recursos` json DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `id` int NOT NULL,
  `curso_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuraciones` json DEFAULT NULL,
  `fecha_publicacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`id`, `curso_id`, `titulo`, `contenido`, `configuraciones`, `fecha_publicacion`, `usuario_creacion`, `activo`) VALUES
(1, 1, 'Evaluación Números Enteros - 25 de Marzo', 'Estimados estudiantes,\n\nLes recuerdo que este lunes 25 de marzo tendremos la evaluación sobre números enteros.\n\nTemas a evaluar:\n- Concepto de números enteros\n- Operaciones: suma, resta, multiplicación y división\n- Resolución de problemas\n\nLa evaluación será de 8:00 a 9:30 AM.\n\n¡Estudien mucho y éxitos!', '{\"tipo\": \"RECORDATORIO\", \"prioridad\": \"ALTA\", \"destinatario\": \"ESTUDIANTES\", \"fecha_expiracion\": \"2025-03-25 23:59:59\"}', '2025-03-22 15:00:00', 5, 1),
(2, 2, 'Bienvenidos al curso de Comunicación', '¡Queridos estudiantes!\n\nSean bienvenidos al curso de Comunicación. Durante este año trabajaremos juntos para mejorar sus habilidades de lectura, escritura y expresión oral.\n\nRecuerden que siempre pueden contar conmigo para resolver sus dudas.\n\n¡Que tengan un excelente año académico!', '{\"tipo\": \"INFORMATIVO\", \"prioridad\": \"NORMAL\", \"destinatario\": \"ESTUDIANTES\", \"fecha_expiracion\": null}', '2025-03-11 13:00:00', 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apoderados`
--

CREATE TABLE `apoderados` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `documento_tipo` enum('DNI','CE','PASAPORTE') COLLATE utf8mb4_unicode_ci DEFAULT 'DNI',
  `documento_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_personales` json DEFAULT NULL,
  `datos_laborales` json DEFAULT NULL,
  `datos_adicionales` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `apoderados`
--

INSERT INTO `apoderados` (`id`, `usuario_id`, `documento_tipo`, `documento_numero`, `nombres`, `apellidos`, `datos_personales`, `datos_laborales`, `datos_adicionales`, `activo`, `fecha_creacion`) VALUES
(1, 9, 'DNI', '08111111', 'Carlos Andrés', 'Gómez Valdez', '{\"email\": \"cgomez@gmail.com\", \"genero\": \"M\", \"telefono\": \"956-111111\", \"direccion\": \"Av. Los Maestros 123, Ica\", \"fecha_nacimiento\": \"1980-05-15\", \"telefono_trabajo\": \"056-111111\"}', '{\"ocupacion\": \"Ingeniero Civil\", \"centro_trabajo\": \"Constructora Ica SAC\", \"ingresos_mensuales\": 4500.0}', '{\"estado_civil\": \"CASADO\", \"grado_instruccion\": \"Superior Universitaria\"}', 1, '2025-09-03 04:25:41'),
(2, 10, 'DNI', '08222222', 'Lucía Mercedes', 'Pérez Guerrero', '{\"email\": \"lperez@hotmail.com\", \"genero\": \"F\", \"telefono\": \"956-222222\", \"direccion\": \"Jr. San Martín 456, Ica\", \"fecha_nacimiento\": \"1985-08-22\", \"telefono_trabajo\": \"056-222222\"}', '{\"ocupacion\": \"Contadora\", \"centro_trabajo\": \"Estudio Contable López\", \"ingresos_mensuales\": 3200.0}', '{\"estado_civil\": \"CASADO\", \"grado_instruccion\": \"Superior Universitaria\"}', 1, '2025-09-03 04:25:41'),
(3, 11, 'DNI', '08333333', 'Roberto Miguel', 'Martínez Silva', '{\"email\": \"rmartinez@gmail.com\", \"genero\": \"M\", \"telefono\": \"956-333333\", \"direccion\": \"Ca. Libertad 789, Ica\", \"fecha_nacimiento\": \"1978-12-10\", \"telefono_trabajo\": \"056-333333\"}', '{\"ocupacion\": \"Comerciante\", \"centro_trabajo\": \"Mercado Central Ica\", \"ingresos_mensuales\": 2800.0}', '{\"estado_civil\": \"CASADO\", \"grado_instruccion\": \"Secundaria Completa\"}', 1, '2025-09-03 04:25:41'),
(4, 12, 'DNI', '08444444', 'Ana Sofía', 'Castro Mendoza', '{\"email\": \"acastro@outlook.com\", \"genero\": \"F\", \"telefono\": \"956-444444\", \"direccion\": \"Av. Cutervo 321, Ica\", \"fecha_nacimiento\": \"1987-03-18\", \"telefono_trabajo\": \"056-444444\"}', '{\"ocupacion\": \"Enfermera\", \"centro_trabajo\": \"Hospital Santa María del Socorro\", \"ingresos_mensuales\": 2500.0}', '{\"estado_civil\": \"SOLTERA\", \"grado_instruccion\": \"Superior Técnica\"}', 1, '2025-09-03 04:25:41'),
(5, 13, 'DNI', '08555555', 'Jorge Luis', 'Vargas Ramos', '{\"email\": \"jvargas@yahoo.com\", \"genero\": \"M\", \"telefono\": \"956-555555\", \"direccion\": \"Jr. Tacna 654, Ica\", \"fecha_nacimiento\": \"1982-07-25\", \"telefono_trabajo\": \"056-555555\"}', '{\"ocupacion\": \"Profesor\", \"centro_trabajo\": \"I.E. José de la Torre Ugarte\", \"ingresos_mensuales\": 2200.0}', '{\"estado_civil\": \"CASADO\", \"grado_instruccion\": \"Superior Universitaria\"}', 1, '2025-09-03 04:25:41'),
(6, 14, 'DNI', '08666666', 'María Elena', 'Fernández Torres', '{\"email\": \"mfernandez@gmail.com\", \"genero\": \"F\", \"telefono\": \"956-666666\", \"direccion\": \"Av. Grau 987, Ica\", \"fecha_nacimiento\": \"1983-11-08\", \"telefono_trabajo\": \"056-666666\"}', '{\"ocupacion\": \"Administradora\", \"centro_trabajo\": \"Municipalidad Provincial de Ica\", \"ingresos_mensuales\": 3800.0}', '{\"estado_civil\": \"CASADO\", \"grado_instruccion\": \"Superior Universitaria\"}', 1, '2025-09-03 04:25:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_curriculares`
--

CREATE TABLE `areas_curriculares` (
  `id` int NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `competencias` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas_curriculares`
--

INSERT INTO `areas_curriculares` (`id`, `nombre`, `codigo`, `descripcion`, `competencias`, `activo`) VALUES
(1, 'Matemática', 'MAT', 'Área de Matemática', NULL, 1),
(2, 'Comunicación', 'COM', 'Área de Comunicación', NULL, 1),
(3, 'Ciencia y Tecnología', 'CYT', 'Área de Ciencia y Tecnología', NULL, 1),
(4, 'Personal Social', 'PS', 'Área de Personal Social', NULL, 1),
(5, 'Arte y Cultura', 'AYC', 'Área de Arte y Cultura', NULL, 1),
(6, 'Educación Física', 'EF', 'Área de Educación Física', NULL, 1),
(7, 'Educación Religiosa', 'ER', 'Área de Educación Religiosa', NULL, 1),
(8, 'Inglés', 'ING', 'Área de Inglés como lengua extranjera', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_docentes`
--

CREATE TABLE `asignaciones_docentes` (
  `id` int NOT NULL,
  `docente_id` int NOT NULL,
  `seccion_id` int NOT NULL,
  `area_id` int NOT NULL,
  `periodo_academico_id` int NOT NULL,
  `es_tutor` tinyint(1) DEFAULT '0',
  `horas_semanales` int DEFAULT '2',
  `horarios` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignaciones_docentes`
--

INSERT INTO `asignaciones_docentes` (`id`, `docente_id`, `seccion_id`, `area_id`, `periodo_academico_id`, `es_tutor`, `horas_semanales`, `horarios`, `activo`, `fecha_creacion`) VALUES
(1, 1, 12, 1, 1, 1, 6, '[{\"dia\": 1, \"aula\": \"Aula 1001\", \"hora_fin\": \"09:30\", \"hora_inicio\": \"08:00\"}, {\"dia\": 2, \"aula\": \"Aula 1001\", \"hora_fin\": \"11:30\", \"hora_inicio\": \"10:00\"}, {\"dia\": 4, \"aula\": \"Aula 1001\", \"hora_fin\": \"09:30\", \"hora_inicio\": \"08:00\"}]', 1, '2025-09-03 04:25:41'),
(2, 2, 12, 2, 1, 0, 5, '[{\"dia\": 1, \"aula\": \"Aula 1001\", \"hora_fin\": \"11:30\", \"hora_inicio\": \"10:00\"}, {\"dia\": 3, \"aula\": \"Aula 1001\", \"hora_fin\": \"09:30\", \"hora_inicio\": \"08:00\"}, {\"dia\": 5, \"aula\": \"Aula 1001\", \"hora_fin\": \"11:30\", \"hora_inicio\": \"10:00\"}]', 1, '2025-09-03 04:25:41'),
(3, 3, 12, 3, 1, 0, 4, '[{\"dia\": 2, \"aula\": \"Aula 1001\", \"hora_fin\": \"09:30\", \"hora_inicio\": \"08:00\"}, {\"dia\": 4, \"aula\": \"Aula 1001\", \"hora_fin\": \"11:30\", \"hora_inicio\": \"10:00\"}]', 1, '2025-09-03 04:25:41'),
(4, 4, 12, 4, 1, 0, 3, '[{\"dia\": 3, \"aula\": \"Aula 1001\", \"hora_fin\": \"11:30\", \"hora_inicio\": \"10:00\"}, {\"dia\": 5, \"aula\": \"Aula 1001\", \"hora_fin\": \"09:30\", \"hora_inicio\": \"08:00\"}]', 1, '2025-09-03 04:25:41'),
(5, 5, 12, 6, 1, 0, 2, '[{\"dia\": 1, \"aula\": \"Patio Deportivo\", \"hora_fin\": \"15:30\", \"hora_inicio\": \"14:00\"}, {\"dia\": 4, \"aula\": \"Patio Deportivo\", \"hora_fin\": \"15:30\", \"hora_inicio\": \"14:00\"}]', 1, '2025-09-03 04:25:41'),
(6, 1, 13, 1, 1, 0, 6, '[{\"dia\": 1, \"aula\": \"Aula 1002\", \"hora_fin\": \"13:15\", \"hora_inicio\": \"11:45\"}, {\"dia\": 3, \"aula\": \"Aula 1002\", \"hora_fin\": \"13:15\", \"hora_inicio\": \"11:45\"}, {\"dia\": 5, \"aula\": \"Aula 1002\", \"hora_fin\": \"13:15\", \"hora_inicio\": \"11:45\"}]', 1, '2025-09-03 04:25:41'),
(7, 2, 7, 2, 1, 1, 8, '[{\"dia\": 1, \"aula\": \"Aula 201\", \"hora_fin\": \"12:00\", \"hora_inicio\": \"08:00\"}, {\"dia\": 2, \"aula\": \"Aula 201\", \"hora_fin\": \"12:00\", \"hora_inicio\": \"08:00\"}]', 1, '2025-09-03 04:25:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_transporte`
--

CREATE TABLE `asignaciones_transporte` (
  `id` int NOT NULL,
  `vehiculo_id` int NOT NULL,
  `ruta_id` int NOT NULL,
  `periodo_academico_id` int NOT NULL,
  `configuracion` json DEFAULT NULL,
  `estudiantes` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_asignacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `asignacion_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `estado` enum('PRESENTE','TARDANZA','FALTA','FALTA_JUSTIFICADA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` json DEFAULT NULL,
  `justificacion` json DEFAULT NULL,
  `docente_id` int DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `estudiante_id`, `asignacion_id`, `fecha`, `hora`, `estado`, `detalles`, `justificacion`, `docente_id`, `fecha_registro`) VALUES
(1, 1, 1, '2025-03-15', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual y atento\"}', NULL, 1, '2025-09-03 04:28:44'),
(2, 1, 2, '2025-03-15', '10:00:00', 'PRESENTE', '{\"observaciones\": \"Participó en clase\"}', NULL, 2, '2025-09-03 04:28:44'),
(3, 1, 1, '2025-03-18', '08:00:00', 'TARDANZA', '{\"observaciones\": \"Llegó a las 8:10\", \"minutos_tardanza\": 10}', '{\"motivo\": \"Tráfico en la avenida\", \"evidencia_url\": null, \"estado_aprobacion\": \"PENDIENTE\"}', 1, '2025-09-03 04:28:44'),
(4, 1, 1, '2025-03-20', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual\"}', NULL, 1, '2025-09-03 04:28:44'),
(5, 1, 1, '2025-03-22', '08:00:00', 'FALTA', '{\"observaciones\": \"No asistió\"}', '{\"motivo\": \"Enfermedad - gripe\", \"evidencia_url\": \"/docs/certificado_medico_diego.pdf\", \"estado_aprobacion\": \"APROBADA\"}', 1, '2025-09-03 04:28:44'),
(6, 2, 1, '2025-03-15', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual\"}', NULL, 1, '2025-09-03 04:28:44'),
(7, 2, 2, '2025-03-15', '10:00:00', 'PRESENTE', '{\"observaciones\": \"Muy participativa\"}', NULL, 2, '2025-09-03 04:28:44'),
(8, 2, 1, '2025-03-18', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual y preparada\"}', NULL, 1, '2025-09-03 04:28:44'),
(9, 2, 1, '2025-03-20', '08:00:00', 'TARDANZA', '{\"observaciones\": \"Llegó a las 8:05\", \"minutos_tardanza\": 5}', NULL, 1, '2025-09-03 04:28:44'),
(10, 2, 1, '2025-03-22', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual\"}', NULL, 1, '2025-09-03 04:28:44'),
(11, 6, 7, '2025-03-15', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Muy alegre y participativa\"}', NULL, 2, '2025-09-03 04:28:44'),
(12, 6, 7, '2025-03-18', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Trajo sus materiales completos\"}', NULL, 2, '2025-09-03 04:28:44'),
(13, 6, 7, '2025-03-20', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Ayudó a sus compañeros\"}', NULL, 2, '2025-09-03 04:28:44'),
(14, 6, 7, '2025-03-22', '08:00:00', 'PRESENTE', '{\"observaciones\": \"Puntual como siempre\"}', NULL, 2, '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_transporte`
--

CREATE TABLE `asistencia_transporte` (
  `id` int NOT NULL,
  `asignacion_id` int NOT NULL,
  `fecha` date NOT NULL,
  `registros` json DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `usuario_registra` int DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atenciones_medicas`
--

CREATE TABLE `atenciones_medicas` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `fecha_atencion` date NOT NULL,
  `hora_atencion` time NOT NULL,
  `datos_atencion` json DEFAULT NULL,
  `signos_vitales` json DEFAULT NULL,
  `tratamiento` json DEFAULT NULL,
  `seguimiento` json DEFAULT NULL,
  `contacto_apoderado` json DEFAULT NULL,
  `autorizaciones` json DEFAULT NULL,
  `enfermero_atiende` int NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `atenciones_medicas`
--

INSERT INTO `atenciones_medicas` (`id`, `estudiante_id`, `fecha_atencion`, `hora_atencion`, `datos_atencion`, `signos_vitales`, `tratamiento`, `seguimiento`, `contacto_apoderado`, `autorizaciones`, `enfermero_atiende`, `fecha_registro`) VALUES
(1, 2, '2025-04-10', '10:30:00', '{\"tipo\": \"Consulta por Malestar\", \"sintomas\": \"Cefalea leve, rinorrea, estornudos frecuentes\", \"motivo_consulta\": \"Dolor de cabeza y estornudos constantes\", \"diagnostico_presuntivo\": \"Crisis de rinitis alérgica\"}', '{\"temperatura\": 36.8, \"presion_sistolica\": 110, \"presion_diastolica\": 70, \"saturacion_oxigeno\": 98, \"frecuencia_cardiaca\": 85, \"frecuencia_respiratoria\": 18}', '{\"observaciones\": \"Se recomendó evitar áreas con mucho polvo\", \"tratamiento_aplicado\": \"Reposo por 30 minutos en enfermería\", \"medicamentos_administrados\": \"Ninguno\"}', '{\"urgente\": false, \"fecha_seguimiento\": null, \"motivo_derivacion\": null, \"requiere_seguimiento\": false, \"derivado_centro_medico\": null}', '{\"hora_contacto\": \"10:45:00\", \"apoderado_nombre\": \"Roberto Martínez\", \"apoderado_telefono\": \"956-333333\", \"apoderado_contactado\": true}', NULL, 1, '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_sistema`
--

CREATE TABLE `auditoria_sistema` (
  `id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `modulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tabla_afectada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `datos_cambio` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL,
  `fecha_evento` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos_preguntas`
--

CREATE TABLE `bancos_preguntas` (
  `id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `area_id` int NOT NULL,
  `configuraciones` json DEFAULT NULL,
  `docente_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bancos_preguntas`
--

INSERT INTO `bancos_preguntas` (`id`, `titulo`, `descripcion`, `area_id`, `configuraciones`, `docente_id`, `activo`, `fecha_creacion`) VALUES
(1, 'Banco Matemática 1ro Sec - Números Enteros', 'Preguntas sobre números enteros para primer año de secundaria', 1, '{\"publico\": false, \"unidad_id\": 1, \"competencia_id\": null}', 1, 1, '2025-09-03 04:25:42'),
(2, 'Banco Comunicación 1ro Sec - Comprensión Lectora', 'Preguntas de comprensión lectora para primer año de secundaria', 2, '{\"publico\": true, \"unidad_id\": 4, \"competencia_id\": null}', 2, 1, '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `asignacion_id` int NOT NULL,
  `periodo_evaluacion` int NOT NULL,
  `actividad_origen_id` int DEFAULT NULL,
  `tipo_origen` enum('TAREA','CUESTIONARIO','EXAMEN','PARTICIPACION','MANUAL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instrumento` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calificacion` decimal(5,2) NOT NULL,
  `calificacion_literal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT '1.00',
  `fecha_evaluacion` date NOT NULL,
  `metadatos` json DEFAULT NULL,
  `docente_id` int NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id`, `estudiante_id`, `asignacion_id`, `periodo_evaluacion`, `actividad_origen_id`, `tipo_origen`, `instrumento`, `calificacion`, `calificacion_literal`, `peso`, `fecha_evaluacion`, `metadatos`, `docente_id`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 1, 1, 1, 1, 'CUESTIONARIO', 'Evaluación Números Enteros', 20.00, 'A', 1.00, '2025-03-25', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Excelente comprensión de los conceptos\"}', 1, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(2, 1, 1, 1, NULL, 'PARTICIPACION', 'Participación en Clase', 18.00, 'A', 0.50, '2025-04-02', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Muy participativo y resuelve ejercicios en pizarra\"}', 1, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(3, 1, 2, 1, 1, 'TAREA', 'Ensayo Mi Familia', 18.50, 'A', 1.50, '2025-04-09', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Excelente redacción y creatividad\"}', 2, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(4, 1, 2, 1, NULL, 'MANUAL', 'Comprensión Lectora', 17.00, 'A', 1.00, '2025-04-15', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Buena comprensión pero puede mejorar análisis\"}', 2, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(5, 2, 1, 1, 1, 'CUESTIONARIO', 'Evaluación Números Enteros', 10.00, 'C', 1.00, '2025-03-25', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Debe reforzar multiplicación con signos\"}', 1, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(6, 2, 1, 1, NULL, 'PARTICIPACION', 'Participación en Clase', 16.00, 'A', 0.50, '2025-04-02', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Participa activamente, muestra interés\"}', 1, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(7, 2, 2, 1, 1, 'TAREA', 'Ensayo Mi Familia', 13.00, 'B', 1.50, '2025-04-10', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Entrega tardía, cuidar ortografía\"}', 2, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(8, 4, 1, 1, NULL, 'MANUAL', 'Práctica Números Enteros', 19.00, 'A', 1.00, '2025-04-01', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Excelente dominio del tema\"}', 1, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(9, 6, 7, 1, NULL, 'MANUAL', 'Lectura Comprensiva', 18.00, 'A', 1.00, '2025-03-28', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Lee muy bien para su edad\"}', 2, '2025-09-03 04:28:44', '2025-09-03 04:28:44'),
(10, 6, 7, 1, NULL, 'MANUAL', 'Escritura y Ortografía', 16.00, 'A', 1.00, '2025-04-05', '{\"exonerado\": false, \"recuperacion\": false, \"observaciones\": \"Buena caligrafía, mejorar ortografía\"}', 2, '2025-09-03 04:28:44', '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicaciones_admision`
--

CREATE TABLE `comunicaciones_admision` (
  `id` int NOT NULL,
  `postulacion_id` int NOT NULL,
  `configuracion` json DEFAULT NULL,
  `estado` enum('PENDIENTE','ENVIADO','ENTREGADO','ERROR') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `metadatos` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_sistema`
--

CREATE TABLE `configuracion_sistema` (
  `id` int NOT NULL,
  `nombre_institucion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colegio Andrés Avelino Cáceres',
  `codigo_modular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colores` json DEFAULT NULL,
  `tema_oscuro` tinyint(1) DEFAULT '0',
  `idioma_predeterminado` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'es',
  `zona_horaria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/Lima',
  `moneda` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'PEN',
  `formato_fecha` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'dd/mm/yyyy',
  `maximo_intentos_login` int DEFAULT '3',
  `tiempo_sesion_minutos` int DEFAULT '120',
  `backup_automatico` tinyint(1) DEFAULT '1',
  `mantenimiento_activo` tinyint(1) DEFAULT '0',
  `mensaje_mantenimiento` text COLLATE utf8mb4_unicode_ci,
  `parametros_adicionales` json DEFAULT NULL,
  `version_sistema` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0.0',
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuario_actualizacion` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_sistema`
--

INSERT INTO `configuracion_sistema` (`id`, `nombre_institucion`, `codigo_modular`, `ruc`, `direccion`, `telefono`, `email`, `sitio_web`, `logo_url`, `colores`, `tema_oscuro`, `idioma_predeterminado`, `zona_horaria`, `moneda`, `formato_fecha`, `maximo_intentos_login`, `tiempo_sesion_minutos`, `backup_automatico`, `mantenimiento_activo`, `mensaje_mantenimiento`, `parametros_adicionales`, `version_sistema`, `fecha_actualizacion`, `usuario_actualizacion`) VALUES
(1, 'Colegio Andrés Avelino Cáceres', '0000000', '12345678901', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'es', 'America/Lima', 'PEN', 'dd/mm/yyyy', 3, 120, 1, 0, NULL, NULL, '1.0.0', '2025-09-03 04:17:23', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_comedor`
--

CREATE TABLE `cuentas_comedor` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `saldo` decimal(10,2) DEFAULT '0.00',
  `configuracion` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuestionarios`
--

CREATE TABLE `cuestionarios` (
  `id` int NOT NULL,
  `curso_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `configuracion` json DEFAULT NULL,
  `configuracion_presentacion` json DEFAULT NULL,
  `preguntas` json DEFAULT NULL,
  `calificacion_config` json DEFAULT NULL,
  `estado` enum('BORRADOR','PUBLICADO','CERRADO') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `usuario_creacion` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuestionarios`
--

INSERT INTO `cuestionarios` (`id`, `curso_id`, `titulo`, `descripcion`, `configuracion`, `configuracion_presentacion`, `preguntas`, `calificacion_config`, `estado`, `usuario_creacion`, `fecha_creacion`) VALUES
(1, 1, 'Evaluación: Números Enteros - Básico', 'Primera evaluación sobre números enteros y sus operaciones básicas', '{\"tipo\": \"EVALUACION\", \"fecha_cierre\": \"2025-03-25 18:00:00\", \"instrucciones\": \"Lee cuidadosamente cada pregunta antes de responder. Tienes 30 minutos para completar la evaluación.\", \"tiempo_limite\": 30, \"fecha_apertura\": \"2025-03-25 08:00:00\", \"intentos_permitidos\": 2}', '{\"barajar_opciones\": true, \"barajar_preguntas\": true, \"mostrar_resultados\": true, \"retroalimentacion_inmediata\": false}', '[{\"orden\": 1, \"puntaje\": 2.0, \"pregunta_id\": 1}, {\"orden\": 2, \"puntaje\": 2.0, \"pregunta_id\": 2}]', '{\"peso\": 1.0, \"escala\": \"vigesimal\", \"calificacion_maxima\": 4.0}', 'PUBLICADO', 5, '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int NOT NULL,
  `codigo_curso` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `asignacion_id` int NOT NULL,
  `configuraciones` json DEFAULT NULL,
  `estudiantes_inscritos` json DEFAULT NULL,
  `estadisticas` json DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `codigo_curso`, `nombre`, `descripcion`, `asignacion_id`, `configuraciones`, `estudiantes_inscritos`, `estadisticas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'MAT-1SA-2025', 'Matemática - 1ro Secundaria A', 'Curso de matemática para primer año de secundaria sección A', 1, '{\"estado\": \"ACTIVO\", \"fecha_fin\": \"2025-12-20\", \"color_tema\": \"#4CAF50\", \"fecha_inicio\": \"2025-03-11\", \"imagen_portada\": \"/img/matematica.jpg\", \"inscripcion_libre\": false}', '[{\"estado\": \"ACTIVO\", \"progreso\": 15.5, \"estudiante_id\": 1, \"fecha_inscripcion\": \"2025-03-11\"}, {\"estado\": \"ACTIVO\", \"progreso\": 22.0, \"estudiante_id\": 2, \"fecha_inscripcion\": \"2025-03-11\"}, {\"estado\": \"ACTIVO\", \"progreso\": 18.7, \"estudiante_id\": 4, \"fecha_inscripcion\": \"2025-03-11\"}]', '{\"progreso_promedio\": 18.73, \"total_estudiantes\": 3, \"participacion_activa\": 85.5}', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(2, 'COM-1SA-2025', 'Comunicación - 1ro Secundaria A', 'Curso de comunicación integral para primer año de secundaria sección A', 2, '{\"estado\": \"ACTIVO\", \"fecha_fin\": \"2025-12-20\", \"color_tema\": \"#2196F3\", \"fecha_inicio\": \"2025-03-11\", \"imagen_portada\": \"/img/comunicacion.jpg\", \"inscripcion_libre\": false}', '[{\"estado\": \"ACTIVO\", \"progreso\": 25.3, \"estudiante_id\": 1, \"fecha_inscripcion\": \"2025-03-11\"}, {\"estado\": \"ACTIVO\", \"progreso\": 31.2, \"estudiante_id\": 2, \"fecha_inscripcion\": \"2025-03-11\"}, {\"estado\": \"ACTIVO\", \"progreso\": 28.8, \"estudiante_id\": 4, \"fecha_inscripcion\": \"2025-03-11\"}]', '{\"progreso_promedio\": 28.43, \"total_estudiantes\": 3, \"participacion_activa\": 92.1}', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(3, 'COM-2PA-2025', 'Comunicación - 2do Primaria A', 'Curso de comunicación integral para segundo año de primaria sección A', 7, '{\"estado\": \"ACTIVO\", \"fecha_fin\": \"2025-12-20\", \"color_tema\": \"#FF9800\", \"fecha_inicio\": \"2025-03-11\", \"imagen_portada\": \"/img/comunicacion_primaria.jpg\", \"inscripcion_libre\": false}', '[{\"estado\": \"ACTIVO\", \"progreso\": 42.1, \"estudiante_id\": 6, \"fecha_inscripcion\": \"2025-03-11\"}]', '{\"progreso_promedio\": 42.1, \"total_estudiantes\": 1, \"participacion_activa\": 88.0}', '2025-09-03 04:25:42', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id` int NOT NULL,
  `codigo_docente` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `documento_tipo` enum('DNI','CE','PASAPORTE') COLLATE utf8mb4_unicode_ci DEFAULT 'DNI',
  `documento_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_personales` json DEFAULT NULL,
  `datos_profesionales` json DEFAULT NULL,
  `datos_laborales` json DEFAULT NULL,
  `areas_especialidad` json DEFAULT NULL,
  `foto_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id`, `codigo_docente`, `usuario_id`, `documento_tipo`, `documento_numero`, `nombres`, `apellidos`, `datos_personales`, `datos_profesionales`, `datos_laborales`, `areas_especialidad`, `foto_url`, `activo`, `fecha_creacion`) VALUES
(1, 'DOC001', 3, 'DNI', '08345678', 'Luis Fernando', 'Correa Mendoza', '{\"email\": \"lcorrea@aac.edu.pe\", \"genero\": \"M\", \"telefono\": \"956-123456\", \"direccion\": \"Jr. Lima 456, Ica\", \"fecha_nacimiento\": \"1985-03-15\"}', '{\"colegiatura\": \"CPPe12345\", \"universidad\": \"Universidad Nacional de Ica\", \"especialidad\": \"Matemática y Física\", \"grado_academico\": \"Licenciado en Educación\"}', '{\"categoria\": \"I\", \"fecha_ingreso\": \"2020-03-01\", \"tipo_contrato\": \"CONTRATADO\", \"nivel_magisterial\": \"II\"}', '[1]', NULL, 1, '2025-09-03 04:25:41'),
(2, 'DOC002', 4, 'DNI', '08456789', 'María Isabel', 'Rojas Castillo', '{\"email\": \"mrojas@aac.edu.pe\", \"genero\": \"F\", \"telefono\": \"956-234567\", \"direccion\": \"Av. Grau 789, Ica\", \"fecha_nacimiento\": \"1988-07-22\"}', '{\"colegiatura\": \"CPPe23456\", \"universidad\": \"Universidad San Luis Gonzaga\", \"especialidad\": \"Comunicación y Literatura\", \"grado_academico\": \"Magister en Educación\"}', '{\"categoria\": \"III\", \"fecha_ingreso\": \"2019-03-01\", \"tipo_contrato\": \"NOMBRADO\", \"nivel_magisterial\": \"III\"}', '[2]', NULL, 1, '2025-09-03 04:25:41'),
(3, 'DOC003', 5, 'DNI', '08567890', 'José Antonio', 'Herrera Díaz', '{\"email\": \"jherrera@aac.edu.pe\", \"genero\": \"M\", \"telefono\": \"956-345678\", \"direccion\": \"Ca. Tacna 123, Ica\", \"fecha_nacimiento\": \"1982-11-10\"}', '{\"colegiatura\": \"CBP34567\", \"universidad\": \"Universidad Nacional Mayor de San Marcos\", \"especialidad\": \"Ciencias Naturales\", \"grado_academico\": \"Licenciado en Biología\"}', '{\"categoria\": \"IV\", \"fecha_ingreso\": \"2018-03-01\", \"tipo_contrato\": \"NOMBRADO\", \"nivel_magisterial\": \"IV\"}', '[3]', NULL, 1, '2025-09-03 04:25:41'),
(4, 'DOC004', 6, 'DNI', '08678901', 'Ana Lucía', 'García Morales', '{\"email\": \"agarcia@aac.edu.pe\", \"genero\": \"F\", \"telefono\": \"956-456789\", \"direccion\": \"Av. Municipalidad 567, Ica\", \"fecha_nacimiento\": \"1990-05-18\"}', '{\"colegiatura\": \"CPPe45678\", \"universidad\": \"Universidad Nacional de Ica\", \"especialidad\": \"Ciencias Sociales e Historia\", \"grado_academico\": \"Licenciada en Historia\"}', '{\"categoria\": \"I\", \"fecha_ingreso\": \"2021-03-01\", \"tipo_contrato\": \"CONTRATADO\", \"nivel_magisterial\": \"I\"}', '[4]', NULL, 1, '2025-09-03 04:25:41'),
(5, 'DOC005', 7, 'DNI', '08789012', 'Ricardo Manuel', 'Torres Sánchez', '{\"email\": \"rtorres@aac.edu.pe\", \"genero\": \"M\", \"telefono\": \"956-567890\", \"direccion\": \"Jr. Ayacucho 890, Ica\", \"fecha_nacimiento\": \"1987-09-25\"}', '{\"colegiatura\": \"CPPe56789\", \"universidad\": \"Universidad Nacional de Educación\", \"especialidad\": \"Educación Física\", \"grado_academico\": \"Licenciado en Educación Física\"}', '{\"categoria\": \"II\", \"fecha_ingreso\": \"2020-08-01\", \"tipo_contrato\": \"CONTRATADO\", \"nivel_magisterial\": \"II\"}', '[6]', NULL, 1, '2025-09-03 04:25:41'),
(6, 'DOC006', 8, 'DNI', '08890123', 'Patricia Elena', 'López Rivera', '{\"email\": \"plopez@aac.edu.pe\", \"genero\": \"F\", \"telefono\": \"956-678901\", \"direccion\": \"Av. San Martín 234, Ica\", \"fecha_nacimiento\": \"1989-12-03\"}', '{\"colegiatura\": \"CPPe67890\", \"universidad\": \"Conservatorio Nacional de Música\", \"especialidad\": \"Arte y Música\", \"grado_academico\": \"Licenciada en Educación Artística\"}', '{\"categoria\": \"I\", \"fecha_ingreso\": \"2022-03-01\", \"tipo_contrato\": \"CONTRATADO\", \"nivel_magisterial\": \"I\"}', '[5]', NULL, 1, '2025-09-03 04:25:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejemplares`
--

CREATE TABLE `ejemplares` (
  `id` int NOT NULL,
  `material_id` int NOT NULL,
  `numero_ejemplar` int NOT NULL,
  `codigo_inventario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('DISPONIBLE','PRESTADO','RESERVADO','MANTENIMIENTO','BAJA') COLLATE utf8mb4_unicode_ci DEFAULT 'DISPONIBLE',
  `ubicacion_especifica` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ejemplares`
--

INSERT INTO `ejemplares` (`id`, `material_id`, `numero_ejemplar`, `codigo_inventario`, `estado`, `ubicacion_especifica`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 1, 'MAT-001-01', 'DISPONIBLE', 'Estante A-1, Fila 1', 'En excelente estado', '2025-09-03 04:28:44'),
(2, 1, 2, 'MAT-001-02', 'PRESTADO', 'Estante A-1, Fila 1', 'Prestado a docente', '2025-09-03 04:28:44'),
(3, 1, 3, 'MAT-001-03', 'DISPONIBLE', 'Estante A-1, Fila 1', 'En buen estado', '2025-09-03 04:28:44'),
(4, 2, 1, 'COM-002-01', 'DISPONIBLE', 'Estante B-2, Fila 1', 'Nuevo', '2025-09-03 04:28:44'),
(5, 2, 2, 'COM-002-02', 'PRESTADO', 'Estante B-2, Fila 1', 'Prestado a estudiante', '2025-09-03 04:28:44'),
(6, 2, 3, 'COM-002-03', 'DISPONIBLE', 'Estante B-2, Fila 1', 'En buen estado', '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_tareas`
--

CREATE TABLE `entregas_tareas` (
  `id` int NOT NULL,
  `tarea_id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `contenido` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL,
  `calificacion` json DEFAULT NULL,
  `estado` enum('BORRADOR','ENVIADA','CALIFICADA','DEVUELTA') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `docente_calificador` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `entregas_tareas`
--

INSERT INTO `entregas_tareas` (`id`, `tarea_id`, `estudiante_id`, `contenido`, `metadatos`, `calificacion`, `estado`, `docente_calificador`) VALUES
(1, 1, 1, '{\"archivos\": [{\"url\": \"/uploads/ensayo_diego_2025.docx\", \"nombre\": \"ensayo_mi_familia_diego.docx\", \"tamanio\": 52480}], \"comentarios\": \"Adjunto mi ensayo sobre mi familia. Espero cumplir con los requisitos solicitados.\"}', '{\"ip_address\": \"192.168.1.15\", \"fecha_entrega\": \"2025-04-07 20:30:00\", \"entrega_tardia\": false}', '{\"puntaje\": 18.5, \"comentarios\": \"Excelente trabajo Diego. Tu descripción es muy emotiva y el uso del lenguaje es apropiado. Sigue así.\", \"rubrica_aplicada\": {\"Creatividad y Estilo\": {\"nivel\": \"Bueno\", \"puntaje\": 16}, \"Contenido y Coherencia\": {\"nivel\": \"Excelente\", \"puntaje\": 19}, \"Ortografía y Gramática\": {\"nivel\": \"Excelente\", \"puntaje\": 18}}, \"fecha_calificacion\": \"2025-04-09 15:30:00\"}', 'CALIFICADA', 2),
(2, 1, 2, '{\"archivos\": [{\"url\": \"/uploads/ensayo_sofia_2025.pdf\", \"nombre\": \"mi_familia_sofia.pdf\", \"tamanio\": 384512}], \"comentarios\": \"Profesora, aquí está mi ensayo. Traté de seguir todas las indicaciones.\"}', '{\"ip_address\": \"192.168.1.22\", \"fecha_entrega\": \"2025-04-09 22:15:00\", \"entrega_tardia\": true}', '{\"puntaje\": 15.0, \"comentarios\": \"Buen trabajo Sofía, pero entregaste tarde. El contenido es bueno pero cuida más la ortografía.\", \"rubrica_aplicada\": {\"Creatividad y Estilo\": {\"nivel\": \"Bueno\", \"puntaje\": 15}, \"Contenido y Coherencia\": {\"nivel\": \"Bueno\", \"puntaje\": 16}, \"Ortografía y Gramática\": {\"nivel\": \"Regular\", \"puntaje\": 13}}, \"fecha_calificacion\": \"2025-04-10 16:45:00\"}', 'CALIFICADA', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int NOT NULL,
  `codigo_estudiante` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `documento_tipo` enum('DNI','CE','PASAPORTE') COLLATE utf8mb4_unicode_ci DEFAULT 'DNI',
  `documento_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F','O') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_personales` json DEFAULT NULL,
  `datos_medicos` json DEFAULT NULL,
  `foto_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `codigo_estudiante`, `usuario_id`, `documento_tipo`, `documento_numero`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `datos_personales`, `datos_medicos`, `foto_url`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, '2025001', 15, 'DNI', '09111111', 'Diego Sebastián', 'Gómez Pérez', '2012-03-15', 'M', '{\"email\": \"diego.gomez@aac.edu.pe\", \"telefono\": \"956-111112\", \"direccion\": \"Av. Los Maestros 123, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Ninguna\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"EsSalud\", \"numero_seguro\": \"12345678901\", \"grupo_sanguineo\": \"O+\", \"contacto_emergencia\": {\"nombre\": \"Carlos Gómez\", \"telefono\": \"956-111111\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(2, '2025002', 16, 'DNI', '09222222', 'Sofía Isabella', 'Martínez García', '2012-07-22', 'F', '{\"email\": \"sofia.martinez@aac.edu.pe\", \"telefono\": \"956-222223\", \"direccion\": \"Ca. Libertad 789, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Polvo\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"SIS\", \"numero_seguro\": \"22345678902\", \"grupo_sanguineo\": \"A+\", \"contacto_emergencia\": {\"nombre\": \"Roberto Martínez\", \"telefono\": \"956-333333\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(3, '2025003', 17, 'DNI', '09333333', 'Mateo Alejandro', 'Castro Morales', '2012-11-08', 'M', '{\"email\": \"mateo.castro@aac.edu.pe\", \"telefono\": \"956-333334\", \"direccion\": \"Av. Cutervo 321, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Mariscos\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"Particular\", \"numero_seguro\": \"33345678903\", \"grupo_sanguineo\": \"B+\", \"contacto_emergencia\": {\"nombre\": \"Ana Castro\", \"telefono\": \"956-444444\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(4, '2025004', 18, 'DNI', '09444444', 'Valeria Camila', 'Vargas Herrera', '2012-05-30', 'F', '{\"email\": \"valeria.vargas@aac.edu.pe\", \"telefono\": \"956-444445\", \"direccion\": \"Jr. Tacna 654, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Ninguna\", \"medicamentos\": \"Vitaminas\", \"seguro_salud\": \"EsSalud\", \"numero_seguro\": \"44345678904\", \"grupo_sanguineo\": \"AB+\", \"contacto_emergencia\": {\"nombre\": \"Jorge Vargas\", \"telefono\": \"956-555555\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(5, '2025005', 19, 'DNI', '09555555', 'Lucas Emilio', 'Fernández Díaz', '2012-09-12', 'M', '{\"email\": \"lucas.fernandez@aac.edu.pe\", \"telefono\": \"956-555556\", \"direccion\": \"Av. Grau 987, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Ninguna\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"EsSalud\", \"numero_seguro\": \"55345678905\", \"grupo_sanguineo\": \"O-\", \"contacto_emergencia\": {\"nombre\": \"María Fernández\", \"telefono\": \"956-666666\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(6, '2025006', 20, 'DNI', '09666666', 'Isabella Nicole', 'Torres Mendoza', '2012-12-25', 'F', '{\"email\": \"isabella.torres@aac.edu.pe\", \"telefono\": \"956-666667\", \"direccion\": \"Jr. San Martín 456, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Polen\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"SIS\", \"numero_seguro\": \"66345678906\", \"grupo_sanguineo\": \"A-\", \"contacto_emergencia\": {\"nombre\": \"Lucía Pérez\", \"telefono\": \"956-222222\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(7, '2025007', 21, 'DNI', '09777777', 'Adrián Joaquín', 'López Ruiz', '2009-04-18', 'M', '{\"email\": \"adrian.lopez@aac.edu.pe\", \"telefono\": \"956-777778\", \"direccion\": \"Av. Municipal 234, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Ninguna\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"Particular\", \"numero_seguro\": \"77345678907\", \"grupo_sanguineo\": \"B-\", \"contacto_emergencia\": {\"nombre\": \"Patricia López\", \"telefono\": \"956-777777\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(8, '2025008', 22, 'DNI', '09888888', 'Camila Antonella', 'Rodríguez Silva', '2009-08-14', 'F', '{\"email\": \"camila.rodriguez@aac.edu.pe\", \"telefono\": \"956-888889\", \"direccion\": \"Ca. Ayacucho 567, Ica\", \"lugar_nacimiento\": \"Ica, Perú\"}', '{\"alergias\": \"Ninguna\", \"medicamentos\": \"Ninguno\", \"seguro_salud\": \"EsSalud\", \"numero_seguro\": \"88345678908\", \"grupo_sanguineo\": \"AB-\", \"contacto_emergencia\": {\"nombre\": \"Carmen Rodríguez\", \"telefono\": \"956-888888\"}}', NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante_apoderados`
--

CREATE TABLE `estudiante_apoderados` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `apoderado_id` int NOT NULL,
  `parentesco` enum('PADRE','MADRE','TUTOR','ABUELO','TIO','HERMANO','OTRO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_principal` tinyint(1) DEFAULT '0',
  `permisos` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiante_apoderados`
--

INSERT INTO `estudiante_apoderados` (`id`, `estudiante_id`, `apoderado_id`, `parentesco`, `es_principal`, `permisos`, `activo`, `fecha_creacion`) VALUES
(1, 1, 1, 'PADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(2, 1, 2, 'MADRE', 0, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(3, 2, 3, 'PADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(4, 3, 4, 'MADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(5, 4, 5, 'PADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(6, 5, 6, 'MADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(7, 6, 2, 'MADRE', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42'),
(8, 7, 1, 'PADRE', 0, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": false}', 1, '2025-09-03 04:25:42'),
(9, 8, 3, 'TUTOR', 1, '{\"puede_recoger\": true, \"autorizado_emergencia\": true, \"recibe_notificaciones\": true}', 1, '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichas_medicas`
--

CREATE TABLE `fichas_medicas` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `datos_medicos` json DEFAULT NULL,
  `historial_medico` json DEFAULT NULL,
  `contactos_emergencia` json DEFAULT NULL,
  `medico_tratante` json DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_actualizacion` date NOT NULL,
  `vigente` tinyint(1) DEFAULT '1',
  `usuario_actualiza` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fichas_medicas`
--

INSERT INTO `fichas_medicas` (`id`, `estudiante_id`, `datos_medicos`, `historial_medico`, `contactos_emergencia`, `medico_tratante`, `observaciones`, `fecha_actualizacion`, `vigente`, `usuario_actualiza`) VALUES
(1, 1, '{\"imc\": 18.9, \"peso_kg\": 45.5, \"talla_cm\": 155, \"tipo_sangre\": \"O+\", \"seguro_salud\": \"EsSalud\", \"numero_seguro\": \"12345678901\"}', '{\"cirugias_previas\": \"Ninguna\", \"vacunas_completas\": true, \"alergias_conocidas\": \"Ninguna\", \"enfermedades_cronicas\": \"Ninguna\", \"medicamentos_actuales\": \"Ninguno\"}', '[{\"nombre\": \"Carlos Gómez Valdez\", \"telefono\": \"956-111111\", \"parentesco\": \"Padre\", \"es_principal\": true}, {\"nombre\": \"Lucía Pérez Guerrero\", \"telefono\": \"956-222222\", \"parentesco\": \"Madre\", \"es_principal\": false}]', '{\"clinica\": \"Clínica San Juan de Dios\", \"pediatra\": \"Dr. Roberto Mendoza\", \"telefono\": \"056-123456\"}', 'Estudiante en buen estado de salud general. Practica deporte regularmente.', '2025-03-01', 1, 1),
(2, 2, '{\"imc\": 18.7, \"peso_kg\": 42.0, \"talla_cm\": 150, \"tipo_sangre\": \"A+\", \"seguro_salud\": \"SIS\", \"numero_seguro\": \"22345678902\"}', '{\"cirugias_previas\": \"Ninguna\", \"vacunas_completas\": true, \"alergias_conocidas\": \"Polvo doméstico (leve)\", \"enfermedades_cronicas\": \"Rinitis alérgica\", \"medicamentos_actuales\": \"Ninguno\"}', '[{\"nombre\": \"Roberto Martínez Silva\", \"telefono\": \"956-333333\", \"parentesco\": \"Padre\", \"es_principal\": true}, {\"nombre\": \"Carmen Silva López\", \"telefono\": \"956-777777\", \"parentesco\": \"Madre\", \"es_principal\": false}]', '{\"clinica\": \"Hospital Santa María del Socorro\", \"pediatra\": \"Dra. Ana Castillo\", \"telefono\": \"056-234567\"}', 'Estudiante con rinitis alérgica estacional. Evitar exposición excesiva al polvo.', '2025-02-28', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foros`
--

CREATE TABLE `foros` (
  `id` int NOT NULL,
  `curso_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `configuraciones` json DEFAULT NULL,
  `mensajes` json DEFAULT NULL,
  `estadisticas` json DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `foros`
--

INSERT INTO `foros` (`id`, `curso_id`, `titulo`, `descripcion`, `configuraciones`, `mensajes`, `estadisticas`, `fecha_creacion`, `usuario_creacion`) VALUES
(1, 1, 'Dudas sobre Números Enteros', 'Espacio para resolver dudas sobre el tema de números enteros y sus operaciones', '{\"tipo\": \"PREGUNTA_RESPUESTA\", \"estado\": \"ABIERTO\", \"moderado\": false}', '[{\"id\": 1, \"titulo\": \"Duda sobre multiplicación de negativos\", \"contenido\": \"Profesor, no entiendo por qué (-3) x (-4) = +12. ¿Podrían explicarlo nuevamente?\", \"respuestas\": [{\"id\": 2, \"contenido\": \"¡Excelente pregunta Diego! Cuando multiplicas dos números negativos, el resultado siempre es positivo. Piénsalo así: (-3) significa que restas 3, cuatro veces. Al restar un número negativo, en realidad estás sumando. (-3) x (-4) = +12.\", \"usuario_id\": 5, \"fecha_creacion\": \"2025-03-28 16:15:00\", \"usuario_nombre\": \"Prof. Luis Correa\"}, {\"id\": 3, \"contenido\": \"Gracias profesor, esa explicación me ayudó mucho también. Ahora entiendo mejor.\", \"usuario_id\": 14, \"fecha_creacion\": \"2025-03-28 17:20:00\", \"usuario_nombre\": \"Sofía Martínez\"}], \"usuario_id\": 13, \"fecha_creacion\": \"2025-03-28 15:30:00\", \"usuario_nombre\": \"Diego Gómez\"}]', '{\"participantes\": 3, \"total_mensajes\": 3, \"mensaje_mas_reciente\": \"2025-03-28 17:20:00\"}', '2025-09-03 04:28:44', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias_disciplinarias`
--

CREATE TABLE `incidencias_disciplinarias` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `fecha_incidencia` datetime NOT NULL,
  `tipo_incidencia` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` json DEFAULT NULL,
  `medida_adoptada` text COLLATE utf8mb4_unicode_ci,
  `seguimiento` json DEFAULT NULL,
  `docente_reporta` int NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `incidencias_disciplinarias`
--

INSERT INTO `incidencias_disciplinarias` (`id`, `estudiante_id`, `fecha_incidencia`, `tipo_incidencia`, `descripcion`, `detalles`, `medida_adoptada`, `seguimiento`, `docente_reporta`, `fecha_registro`) VALUES
(1, 3, '2025-03-20 10:30:00', 'Interrupción de Clase', 'El estudiante interrumpió constantemente la explicación haciendo bromas', '{\"lugar\": \"Aula 1002\", \"testigos\": [\"Sofía Martínez\", \"Lucas Fernández\"], \"nivel_gravedad\": \"LEVE\"}', 'Conversación con el estudiante sobre respeto y responsabilidad en clase', '{\"estado\": \"RESUELTA\", \"derivaciones\": [], \"requiere_tutoria\": false}', 1, '2025-09-03 04:28:44'),
(2, 2, '2025-04-02 11:15:00', 'Falta de Material', 'La estudiante no trajo sus útiles escolares por tercera vez en la semana', '{\"lugar\": \"Aula 1001\", \"testigos\": [], \"nivel_gravedad\": \"LEVE\"}', 'Llamada de atención y notificación al apoderado', '{\"estado\": \"EN_PROCESO\", \"derivaciones\": [], \"requiere_tutoria\": false}', 2, '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_cuestionarios`
--

CREATE TABLE `intentos_cuestionarios` (
  `id` int NOT NULL,
  `cuestionario_id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `numero_intento` int NOT NULL,
  `respuestas` json DEFAULT NULL,
  `resultados` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `intentos_cuestionarios`
--

INSERT INTO `intentos_cuestionarios` (`id`, `cuestionario_id`, `estudiante_id`, `numero_intento`, `respuestas`, `resultados`, `metadatos`) VALUES
(1, 1, 1, 1, '[{\"respuesta\": \"A\", \"es_correcta\": true, \"pregunta_id\": 1, \"fecha_respuesta\": \"2025-03-25 09:15:00\", \"tiempo_respuesta\": 120}, {\"respuesta\": \"B\", \"es_correcta\": true, \"pregunta_id\": 2, \"fecha_respuesta\": \"2025-03-25 09:18:00\", \"tiempo_respuesta\": 180}]', '{\"estado\": \"CALIFICADO\", \"comentarios\": \"Excelente trabajo, todas las respuestas correctas\", \"calificacion\": 4.0, \"tiempo_dedicado\": 5}', '{\"ip_address\": \"192.168.1.15\", \"fecha_envio\": \"2025-03-25 09:18:00\", \"fecha_inicio\": \"2025-03-25 09:10:00\"}'),
(2, 1, 2, 1, '[{\"respuesta\": \"A\", \"es_correcta\": true, \"pregunta_id\": 1, \"fecha_respuesta\": \"2025-03-25 10:20:00\", \"tiempo_respuesta\": 90}, {\"respuesta\": \"A\", \"es_correcta\": false, \"pregunta_id\": 2, \"fecha_respuesta\": \"2025-03-25 10:23:00\", \"tiempo_respuesta\": 150}]', '{\"estado\": \"CALIFICADO\", \"comentarios\": \"Revisa las reglas de multiplicación con signos\", \"calificacion\": 2.0, \"tiempo_dedicado\": 4}', '{\"ip_address\": \"192.168.1.22\", \"fecha_envio\": \"2025-03-25 10:23:00\", \"fecha_inicio\": \"2025-03-25 10:15:00\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_enfermeria`
--

CREATE TABLE `inventario_enfermeria` (
  `id` int NOT NULL,
  `tipo` enum('MEDICAMENTO','MATERIAL_CURACION','EQUIPO_MEDICO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_item` json DEFAULT NULL,
  `inventario` json DEFAULT NULL,
  `proveedor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_ingreso` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecciones`
--

CREATE TABLE `lecciones` (
  `id` int NOT NULL,
  `unidad_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `contenido` longtext COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL,
  `tipo` enum('CONTENIDO','ACTIVIDAD','EVALUACION') COLLATE utf8mb4_unicode_ci DEFAULT 'CONTENIDO',
  `configuraciones` json DEFAULT NULL,
  `recursos` json DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lecciones`
--

INSERT INTO `lecciones` (`id`, `unidad_id`, `titulo`, `descripcion`, `contenido`, `orden`, `tipo`, `configuraciones`, `recursos`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'Introducción a los Números Enteros', 'Conceptos básicos de números enteros positivos y negativos', '<h2>Números Enteros</h2>\r\n<p>Los números enteros son el conjunto de números que incluye:</p>\r\n<ul>\r\n<li>Números naturales: 1, 2, 3, 4, ...</li>\r\n<li>El cero: 0</li>\r\n<li>Números enteros negativos: -1, -2, -3, -4, ...</li>\r\n</ul>\r\n<p>Los representamos en la recta numérica...</p>', 1, 'CONTENIDO', '{\"estado\": \"PUBLICADO\", \"obligatorio\": true, \"tiempo_estimado\": 45}', '[{\"url\": \"/recursos/numeros_enteros_teoria.pdf\", \"tipo\": \"PDF\", \"orden\": 1, \"titulo\": \"Números Enteros - Teoría\", \"descargable\": true}, {\"url\": \"/recursos/video_numeros_enteros.mp4\", \"tipo\": \"VIDEO\", \"orden\": 2, \"titulo\": \"Video Explicativo - Números Enteros\", \"duracion\": 300}]', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(2, 1, 'Operaciones con Números Enteros', 'Suma, resta, multiplicación y división de números enteros', '<h2>Operaciones con Números Enteros</h2>\r\n<h3>Suma de Números Enteros</h3>\r\n<p>Para sumar números enteros:</p>\r\n<ul>\r\n<li>Si tienen el mismo signo, se suman y se conserva el signo</li>\r\n<li>Si tienen signos diferentes, se restan y se conserva el signo del mayor</li>\r\n</ul>', 2, 'CONTENIDO', '{\"estado\": \"PUBLICADO\", \"obligatorio\": true, \"tiempo_estimado\": 60}', '[{\"url\": \"/recursos/operaciones_ejercicios.pdf\", \"tipo\": \"PDF\", \"orden\": 1, \"titulo\": \"Operaciones - Ejercicios\", \"descargable\": true}, {\"url\": \"https://calculator.net\", \"tipo\": \"ENLACE\", \"orden\": 2, \"titulo\": \"Calculadora Online\"}]', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(3, 1, 'Evaluación: Números Enteros', 'Evaluación de conocimientos sobre números enteros', '', 3, 'EVALUACION', '{\"estado\": \"PUBLICADO\", \"obligatorio\": true, \"tiempo_estimado\": 90}', '[]', '2025-09-03 04:25:42', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `malla_curricular`
--

CREATE TABLE `malla_curricular` (
  `id` int NOT NULL,
  `nivel_id` int NOT NULL,
  `grado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area_id` int NOT NULL,
  `horas_semanales` int DEFAULT '2',
  `competencias_grado` json DEFAULT NULL,
  `periodo_academico_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales_repuestos`
--

CREATE TABLE `materiales_repuestos` (
  `id` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `datos_basicos` json DEFAULT NULL,
  `inventario` json DEFAULT NULL,
  `proveedor` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_bibliografico`
--

CREATE TABLE `material_bibliografico` (
  `id` int NOT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos_basicos` json NOT NULL,
  `datos_publicacion` json DEFAULT NULL,
  `clasificacion` json DEFAULT NULL,
  `datos_fisicos` json DEFAULT NULL,
  `autores` json DEFAULT NULL,
  `datos_adquisicion` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `material_bibliografico`
--

INSERT INTO `material_bibliografico` (`id`, `codigo_barras`, `datos_basicos`, `datos_publicacion`, `clasificacion`, `datos_fisicos`, `autores`, `datos_adquisicion`, `activo`, `fecha_creacion`) VALUES
(1, '978-84-08-12345-6', '{\"isbn\": \"978-84-08-12345-6\", \"tipo\": \"LIBRO\", \"titulo\": \"Matemática 1 - Secundaria\", \"subtitulo\": \"Álgebra y Geometría\"}', '{\"idioma\": \"Español\", \"edicion\": \"2da Edición\", \"paginas\": 320, \"editorial\": \"Editorial Santillana\", \"anio_publicacion\": 2024}', '{\"categoria\": \"Matemáticas\", \"codigo_dewey\": \"510\", \"palabras_clave\": \"algebra, geometria, secundaria\"}', '{\"ubicacion\": \"Estante A-1\", \"ejemplares\": 5, \"estado_general\": \"Bueno\"}', '[{\"nombre\": \"Juan Carlos\", \"apellido\": \"Pérez Mendoza\", \"principal\": true}]', '{\"precio\": 85.0, \"proveedor\": \"Distribuidora Educativa SAC\", \"fecha_adquisicion\": \"2024-12-15\"}', 1, '2025-09-03 04:28:44'),
(2, '978-84-15-67890-1', '{\"isbn\": \"978-84-15-67890-1\", \"tipo\": \"LIBRO\", \"titulo\": \"Comunicación Integral 1\", \"subtitulo\": \"Comprensión Lectora y Producción de Textos\"}', '{\"idioma\": \"Español\", \"edicion\": \"3ra Edición\", \"paginas\": 280, \"editorial\": \"Editorial Norma\", \"anio_publicacion\": 2024}', '{\"categoria\": \"Lengua y Literatura\", \"codigo_dewey\": \"460\", \"palabras_clave\": \"comunicacion, lectura, redaccion\"}', '{\"ubicacion\": \"Estante B-2\", \"ejemplares\": 8, \"estado_general\": \"Excelente\"}', '[{\"nombre\": \"María Elena\", \"apellido\": \"García Torres\", \"principal\": true}, {\"nombre\": \"Carlos\", \"apellido\": \"Ruiz Díaz\", \"principal\": false}]', '{\"precio\": 78.5, \"proveedor\": \"Libros Educativos Ica\", \"fecha_adquisicion\": \"2025-01-10\"}', 1, '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int NOT NULL,
  `codigo_matricula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estudiante_id` int NOT NULL,
  `seccion_id` int NOT NULL,
  `periodo_academico_id` int NOT NULL,
  `fecha_matricula` date NOT NULL,
  `estado` enum('MATRICULADO','TRASLADADO','RETIRADO','RESERVADO') COLLATE utf8mb4_unicode_ci DEFAULT 'MATRICULADO',
  `tipo_matricula` enum('NUEVO','CONTINUADOR','TRASLADO') COLLATE utf8mb4_unicode_ci DEFAULT 'NUEVO',
  `datos_matricula` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `matriculas`
--

INSERT INTO `matriculas` (`id`, `codigo_matricula`, `estudiante_id`, `seccion_id`, `periodo_academico_id`, `fecha_matricula`, `estado`, `tipo_matricula`, `datos_matricula`, `activo`, `fecha_creacion`) VALUES
(1, 'MAT2025001', 1, 12, 1, '2025-02-15', 'MATRICULADO', 'NUEVO', '{\"observaciones\": \"Ingreso por examen de admisión\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(2, 'MAT2025002', 2, 12, 1, '2025-02-16', 'MATRICULADO', 'NUEVO', '{\"observaciones\": \"Traslado desde otro colegio\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(3, 'MAT2025003', 3, 13, 1, '2025-02-17', 'MATRICULADO', 'CONTINUADOR', '{\"observaciones\": \"Estudiante regular\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(4, 'MAT2025004', 4, 12, 1, '2025-02-18', 'MATRICULADO', 'CONTINUADOR', '{\"observaciones\": \"Estudiante regular\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(5, 'MAT2025005', 5, 13, 1, '2025-02-19', 'MATRICULADO', 'NUEVO', '{\"observaciones\": \"Hermano de ex-alumno\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(6, 'MAT2025006', 6, 7, 1, '2025-02-20', 'MATRICULADO', 'CONTINUADOR', '{\"observaciones\": \"Estudiante regular\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(7, 'MAT2025007', 7, 16, 1, '2025-02-21', 'MATRICULADO', 'CONTINUADOR', '{\"observaciones\": \"Estudiante destacado\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42'),
(8, 'MAT2025008', 8, 16, 1, '2025-02-22', 'MATRICULADO', 'NUEVO', '{\"observaciones\": \"Excelente rendimiento previo\", \"documentos_completos\": true}', 1, '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajeria`
--

CREATE TABLE `mensajeria` (
  `id` int NOT NULL,
  `remitente_id` int NOT NULL,
  `destinatarios` json NOT NULL,
  `asunto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracion` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL,
  `estadisticas` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mensajeria`
--

INSERT INTO `mensajeria` (`id`, `remitente_id`, `destinatarios`, `asunto`, `mensaje`, `configuracion`, `metadatos`, `estadisticas`) VALUES
(1, 1, '[{\"estado\": \"leido\", \"user_id\": 5, \"fecha_lectura\": \"2025-04-01 09:15:00\"}, {\"estado\": \"leido\", \"user_id\": 6, \"fecha_lectura\": \"2025-04-01 10:30:00\"}, {\"estado\": \"leido\", \"user_id\": 7, \"fecha_lectura\": \"2025-04-01 11:45:00\"}, {\"estado\": \"no_leido\", \"user_id\": 8, \"fecha_lectura\": null}]', 'Reunión Pedagógica - Viernes 5 de Abril', 'Estimados docentes,\n\nLes recuerdo que este viernes 5 de abril tendremos nuestra reunión pedagógica mensual a las 3:00 PM en la sala de profesores.\n\nTemas a tratar:\n- Evaluación del I Bimestre\n- Planificación de actividades por el Día del Niño\n- Capacitación en nuevas herramientas digitales\n\nPor favor confirmen su asistencia.\n\nSaludos cordiales,\nDr. Carlos Mendoza\nDirector', '{\"tipo\": \"MENSAJE\", \"prioridad\": \"ALTA\", \"archivos_adjuntos\": []}', '{\"fecha_envio\": \"2025-04-01 08:30:00\", \"fecha_expiracion\": null, \"mensaje_padre_id\": null}', '{\"leidos\": 3, \"archivados\": 0, \"total_destinatarios\": 4}'),
(2, 6, '[{\"estado\": \"leido\", \"user_id\": 9, \"fecha_lectura\": \"2025-04-09 20:45:00\"}]', 'Felicitaciones por el rendimiento de Diego', 'Estimado Sr. Gómez,\n\nEs un placer comunicarle que Diego ha obtenido excelentes calificaciones en el curso de Comunicación. Su ensayo \"Mi Familia\" fue destacado por su creatividad y buena redacción.\n\nDiego demuestra mucho interés en la lectura y participación activa en clase. Los felicito por el apoyo que le brindan en casa.\n\nCualquier consulta, quedo a su disposición.\n\nCordialmente,\nProf. María Rojas\nDocente de Comunicación', '{\"tipo\": \"MENSAJE\", \"prioridad\": \"NORMAL\", \"archivos_adjuntos\": []}', '{\"fecha_envio\": \"2025-04-09 16:00:00\", \"fecha_expiracion\": null, \"mensaje_padre_id\": null}', '{\"leidos\": 1, \"archivados\": 0, \"total_destinatarios\": 1}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus_comedor`
--

CREATE TABLE `menus_comedor` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `configuracion` json DEFAULT NULL,
  `detalles` json DEFAULT NULL,
  `disponibilidad` json DEFAULT NULL,
  `imagen_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_sistema`
--

CREATE TABLE `metricas_sistema` (
  `id` int NOT NULL,
  `fecha` date NOT NULL,
  `metricas_uso` json DEFAULT NULL,
  `metricas_academicas` json DEFAULT NULL,
  `metricas_sistema` json DEFAULT NULL,
  `fecha_calculo` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metricas_sistema`
--

INSERT INTO `metricas_sistema` (`id`, `fecha`, `metricas_uso`, `metricas_academicas`, `metricas_sistema`, `fecha_calculo`) VALUES
(1, '2025-04-01', '{\"usuarios_activos\": 45, \"docentes_conectados\": 6, \"apoderados_conectados\": 4, \"estudiantes_conectados\": 8}', '{\"tareas_entregadas\": 2, \"participacion_foros\": 3, \"evaluaciones_tomadas\": 2, \"recursos_descargados\": 15}', '{\"horario_pico\": \"10:00-11:00\", \"recursos_mas_usados\": [\"videos_matematica\", \"pdf_comunicacion\"], \"tiempo_promedio_sesion_minutos\": 35}', '2025-09-03 04:28:44'),
(2, '2025-04-10', '{\"usuarios_activos\": 52, \"docentes_conectados\": 6, \"apoderados_conectados\": 8, \"estudiantes_conectados\": 12}', '{\"tareas_entregadas\": 3, \"participacion_foros\": 5, \"evaluaciones_tomadas\": 1, \"recursos_descargados\": 22}', '{\"horario_pico\": \"15:00-16:00\", \"recursos_mas_usados\": [\"ejercicios_matematica\", \"lecturas_comunicacion\"], \"tiempo_promedio_sesion_minutos\": 42}', '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_activos`
--

CREATE TABLE `movimientos_activos` (
  `id` int NOT NULL,
  `activo_id` int NOT NULL,
  `tipo_movimiento` enum('ASIGNACION','TRANSFERENCIA','BAJA','MANTENIMIENTO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` json DEFAULT NULL,
  `fecha_movimiento` date NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `usuario_registra` int NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multas_biblioteca`
--

CREATE TABLE `multas_biblioteca` (
  `id` int NOT NULL,
  `prestamo_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `configuracion` json DEFAULT NULL,
  `estado` enum('PENDIENTE','PAGADA','CONDONADA') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `fechas` json DEFAULT NULL,
  `usuario_genera` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `niveles_educativos`
--

CREATE TABLE `niveles_educativos` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grados` json DEFAULT NULL,
  `orden` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `niveles_educativos`
--

INSERT INTO `niveles_educativos` (`id`, `nombre`, `codigo`, `grados`, `orden`, `activo`) VALUES
(1, 'Inicial', 'INI', '[{\"codigo\": \"3I\", \"nombre\": \"3 años\", \"edad_max\": 3, \"edad_min\": 3}, {\"codigo\": \"4I\", \"nombre\": \"4 años\", \"edad_max\": 4, \"edad_min\": 4}, {\"codigo\": \"5I\", \"nombre\": \"5 años\", \"edad_max\": 5, \"edad_min\": 5}]', 1, 1),
(2, 'Primaria', 'PRI', '[{\"codigo\": \"1P\", \"nombre\": \"1ro\", \"edad_max\": 6, \"edad_min\": 6}, {\"codigo\": \"2P\", \"nombre\": \"2do\", \"edad_max\": 7, \"edad_min\": 7}, {\"codigo\": \"3P\", \"nombre\": \"3ro\", \"edad_max\": 8, \"edad_min\": 8}, {\"codigo\": \"4P\", \"nombre\": \"4to\", \"edad_max\": 9, \"edad_min\": 9}, {\"codigo\": \"5P\", \"nombre\": \"5to\", \"edad_max\": 10, \"edad_min\": 10}, {\"codigo\": \"6P\", \"nombre\": \"6to\", \"edad_max\": 11, \"edad_min\": 11}]', 2, 1),
(3, 'Secundaria', 'SEC', '[{\"codigo\": \"1S\", \"nombre\": \"1ro\", \"edad_max\": 12, \"edad_min\": 12}, {\"codigo\": \"2S\", \"nombre\": \"2do\", \"edad_max\": 13, \"edad_min\": 13}, {\"codigo\": \"3S\", \"nombre\": \"3ro\", \"edad_max\": 14, \"edad_min\": 14}, {\"codigo\": \"4S\", \"nombre\": \"4to\", \"edad_max\": 15, \"edad_min\": 15}, {\"codigo\": \"5S\", \"nombre\": \"5to\", \"edad_max\": 16, \"edad_min\": 16}]', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_sistema`
--

CREATE TABLE `notificaciones_sistema` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('TAREA','CALIFICACION','ASISTENCIA','ANUNCIO','SISTEMA','RECORDATORIO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origen` json DEFAULT NULL,
  `configuracion` json DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones_sistema`
--

INSERT INTO `notificaciones_sistema` (`id`, `usuario_id`, `titulo`, `mensaje`, `tipo`, `origen`, `configuracion`, `fecha_creacion`) VALUES
(1, 13, 'Nueva Tarea Asignada', 'Se ha asignado una nueva tarea en el curso de Comunicación: \"Ensayo: Mi Familia\". Fecha de entrega: 8 de abril.', 'TAREA', '{\"id\": 1, \"tipo\": \"tarea\", \"datos_adicionales\": {\"curso\": \"Comunicación\", \"docente\": \"María Rojas\"}}', '{\"leida\": false, \"activa\": true, \"fecha_lectura\": null, \"fecha_expiracion\": \"2025-04-08 23:59:59\"}', '2025-09-03 04:28:44'),
(2, 9, 'Calificación Registrada', 'Se ha registrado una nueva calificación para Diego Gómez en Matemática: 20 puntos en \"Evaluación Números Enteros\".', 'CALIFICACION', '{\"id\": 1, \"tipo\": \"calificacion\", \"datos_adicionales\": {\"nota\": \"20\", \"curso\": \"Matemática\", \"estudiante\": \"Diego Gómez\"}}', '{\"leida\": true, \"activa\": true, \"fecha_lectura\": \"2025-03-25 19:30:00\", \"fecha_expiracion\": null}', '2025-09-03 04:28:44'),
(3, 14, 'Recordatorio: Tarea Próxima a Vencer', 'Recuerda que tienes una tarea pendiente en Comunicación que vence mañana: \"Ensayo: Mi Familia\".', 'RECORDATORIO', '{\"id\": 1, \"tipo\": \"tarea\", \"datos_adicionales\": {\"curso\": \"Comunicación\", \"fecha_vencimiento\": \"2025-04-08\"}}', '{\"leida\": false, \"activa\": true, \"fecha_lectura\": null, \"fecha_expiracion\": \"2025-04-08 08:00:00\"}', '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_trabajo`
--

CREATE TABLE `ordenes_trabajo` (
  `id` int NOT NULL,
  `numero_orden` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo_id` int NOT NULL,
  `datos_orden` json DEFAULT NULL,
  `planificacion` json DEFAULT NULL,
  `ejecucion` json DEFAULT NULL,
  `personal` json DEFAULT NULL,
  `materiales` json DEFAULT NULL,
  `estado` enum('SOLICITADA','PROGRAMADA','EN_PROCESO','COMPLETADA','CANCELADA') COLLATE utf8mb4_unicode_ci DEFAULT 'SOLICITADA',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_academicos`
--

CREATE TABLE `periodos_academicos` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo_periodo` enum('BIMESTRE','TRIMESTRE','SEMESTRE') COLLATE utf8mb4_unicode_ci DEFAULT 'BIMESTRE',
  `periodos_evaluacion` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `actual` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `periodos_academicos`
--

INSERT INTO `periodos_academicos` (`id`, `nombre`, `anio`, `fecha_inicio`, `fecha_fin`, `tipo_periodo`, `periodos_evaluacion`, `activo`, `actual`, `fecha_creacion`) VALUES
(1, 'Año Académico 2025', 2025, '2025-03-11', '2025-12-20', 'BIMESTRE', '[{\"nombre\": \"I Bimestre\", \"numero\": 1, \"fecha_fin\": \"2025-05-16\", \"fecha_inicio\": \"2025-03-11\"}, {\"nombre\": \"II Bimestre\", \"numero\": 2, \"fecha_fin\": \"2025-07-25\", \"fecha_inicio\": \"2025-05-19\"}, {\"nombre\": \"III Bimestre\", \"numero\": 3, \"fecha_fin\": \"2025-10-17\", \"fecha_inicio\": \"2025-08-11\"}, {\"nombre\": \"IV Bimestre\", \"numero\": 4, \"fecha_fin\": \"2025-12-20\", \"fecha_inicio\": \"2025-10-20\"}]', 1, 1, '2025-09-03 04:25:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id` int NOT NULL,
  `proceso_id` int NOT NULL,
  `codigo_postulacion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grado_solicitado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_postulante` json NOT NULL,
  `datos_apoderado` json NOT NULL,
  `documentos` json DEFAULT NULL,
  `evaluaciones` json DEFAULT NULL,
  `estado` enum('REGISTRADA','EN_EVALUACION','ADMITIDO','LISTA_ESPERA','NO_ADMITIDO') COLLATE utf8mb4_unicode_ci DEFAULT 'REGISTRADA',
  `metadatos` json DEFAULT NULL,
  `fecha_postulacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int NOT NULL,
  `banco_id` int NOT NULL,
  `enunciado` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('MULTIPLE_CHOICE','VERDADERO_FALSO','EMPAREJAMIENTO','ENSAYO','ARCHIVO','NUMERICA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracion` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL,
  `estado` enum('BORRADOR','ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `banco_id`, `enunciado`, `tipo`, `configuracion`, `metadatos`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, '¿Cuál es el resultado de (-5) + (+8)?', 'MULTIPLE_CHOICE', '{\"puntaje\": 2.0, \"opciones\": [{\"letra\": \"A\", \"texto\": \"+3\", \"correcta\": true}, {\"letra\": \"B\", \"texto\": \"-3\", \"correcta\": false}, {\"letra\": \"C\", \"texto\": \"+13\", \"correcta\": false}, {\"letra\": \"D\", \"texto\": \"-13\", \"correcta\": false}], \"dificultad\": \"FACIL\", \"tiempo_estimado\": 2, \"respuesta_correcta\": \"A\"}', '{\"etiquetas\": [\"suma\", \"signos\"], \"explicacion\": \"Al sumar números con signos diferentes, restamos y conservamos el signo del mayor: 8 - 5 = 3, como 8 es positivo, el resultado es +3\", \"competencias\": []}', 'ACTIVO', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(2, 1, 'Resuelve: (-12) × (+3)', 'MULTIPLE_CHOICE', '{\"puntaje\": 2.0, \"opciones\": [{\"letra\": \"A\", \"texto\": \"+36\", \"correcta\": false}, {\"letra\": \"B\", \"texto\": \"-36\", \"correcta\": true}, {\"letra\": \"C\", \"texto\": \"+15\", \"correcta\": false}, {\"letra\": \"D\", \"texto\": \"-9\", \"correcta\": false}], \"dificultad\": \"MEDIO\", \"tiempo_estimado\": 3, \"respuesta_correcta\": \"B\"}', '{\"etiquetas\": [\"multiplicacion\", \"signos\"], \"explicacion\": \"Al multiplicar números con signos diferentes, el resultado es negativo: 12 × 3 = 36, con signo negativo = -36\", \"competencias\": []}', 'ACTIVO', '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(3, 2, 'Lee el siguiente texto y responde: ¿Cuál es la idea principal del primer párrafo?', 'ENSAYO', '{\"puntaje\": 4.0, \"dificultad\": \"MEDIO\", \"tiempo_estimado\": 10, \"respuesta_correcta\": \"\"}', '{\"etiquetas\": [\"comprension\", \"idea_principal\"], \"explicacion\": \"Se evaluará la capacidad de identificar la idea principal del texto\", \"competencias\": []}', 'ACTIVO', '2025-09-03 04:25:42', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos_biblioteca`
--

CREATE TABLE `prestamos_biblioteca` (
  `id` int NOT NULL,
  `ejemplar_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `tipo_usuario` enum('ESTUDIANTE','DOCENTE','ADMINISTRATIVO','APODERADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_prestamo` json DEFAULT NULL,
  `datos_devolucion` json DEFAULT NULL,
  `estado` enum('ACTIVO','DEVUELTO','VENCIDO','PERDIDO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `usuarios_gestion` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prestamos_biblioteca`
--

INSERT INTO `prestamos_biblioteca` (`id`, `ejemplar_id`, `usuario_id`, `tipo_usuario`, `datos_prestamo`, `datos_devolucion`, `estado`, `usuarios_gestion`) VALUES
(1, 2, 5, 'DOCENTE', '{\"renovaciones\": 0, \"fecha_prestamo\": \"2025-04-01\", \"fecha_devolucion_programada\": \"2025-04-15\"}', NULL, 'ACTIVO', '{\"usuario_prestamo\": 1, \"usuario_devolucion\": null}'),
(2, 5, 13, 'ESTUDIANTE', '{\"renovaciones\": 0, \"fecha_prestamo\": \"2025-04-05\", \"fecha_devolucion_programada\": \"2025-04-12\"}', NULL, 'ACTIVO', '{\"usuario_prestamo\": 1, \"usuario_devolucion\": null}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procesos_admision`
--

CREATE TABLE `procesos_admision` (
  `id` int NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio_academico` int NOT NULL,
  `configuracion` json DEFAULT NULL,
  `vacantes` json DEFAULT NULL,
  `estado` enum('CONFIGURACION','ABIERTO','CERRADO','FINALIZADO') COLLATE utf8mb4_unicode_ci DEFAULT 'CONFIGURACION',
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_estudiantes`
--

CREATE TABLE `progreso_estudiantes` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `leccion_id` int NOT NULL,
  `estado` enum('NO_INICIADO','EN_PROGRESO','COMPLETADO') COLLATE utf8mb4_unicode_ci DEFAULT 'NO_INICIADO',
  `progreso` decimal(5,2) DEFAULT '0.00',
  `tiempo_dedicado` int DEFAULT '0',
  `datos_progreso` json DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `progreso_estudiantes`
--

INSERT INTO `progreso_estudiantes` (`id`, `estudiante_id`, `leccion_id`, `estado`, `progreso`, `tiempo_dedicado`, `datos_progreso`, `fecha_actualizacion`) VALUES
(1, 1, 1, 'COMPLETADO', 100.00, 45, '{\"intentos\": 1, \"calificacion\": 18.5, \"fecha_inicio\": \"2025-03-15 09:00:00\", \"fecha_completado\": \"2025-03-15 10:15:00\"}', '2025-09-03 04:25:42'),
(2, 1, 2, 'EN_PROGRESO', 65.00, 35, '{\"intentos\": 1, \"fecha_inicio\": \"2025-03-18 10:00:00\", \"ultima_actividad\": \"2025-03-18 10:35:00\"}', '2025-09-03 04:25:42'),
(3, 2, 1, 'COMPLETADO', 100.00, 52, '{\"intentos\": 2, \"calificacion\": 16.8, \"fecha_inicio\": \"2025-03-15 14:00:00\", \"fecha_completado\": \"2025-03-15 15:30:00\"}', '2025-09-03 04:25:42'),
(4, 2, 2, 'COMPLETADO', 100.00, 58, '{\"intentos\": 1, \"calificacion\": 19.2, \"fecha_inicio\": \"2025-03-18 08:30:00\", \"fecha_completado\": \"2025-03-18 09:45:00\"}', '2025-09-03 04:25:42'),
(5, 4, 1, 'COMPLETADO', 100.00, 42, '{\"intentos\": 1, \"calificacion\": 17.5, \"fecha_inicio\": \"2025-03-16 11:00:00\", \"fecha_completado\": \"2025-03-16 12:00:00\"}', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_configurados`
--

CREATE TABLE `reportes_configurados` (
  `id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('ACADEMICO','ADMINISTRATIVO','DISCIPLINARIO','FINANCIERO','PERSONALIZADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracion` json DEFAULT NULL,
  `permisos` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `usuario_creacion` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `nivel_acceso` int DEFAULT '1',
  `permisos` json DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `nivel_acceso`, `permisos`, `activo`, `fecha_creacion`) VALUES
(1, 'Super Administrador', 'Acceso total al sistema', 10, '[\"*\"]', 1, '2025-09-03 04:17:23'),
(2, 'Director', 'Gestión académica y administrativa completa', 9, '[\"academico.*\", \"administrativo.*\", \"reportes.*\"]', 1, '2025-09-03 04:17:23'),
(3, 'Subdirector Académico', 'Gestión académica y pedagógica', 8, '[\"academico.*\", \"eva.*\", \"evaluaciones.*\"]', 1, '2025-09-03 04:17:23'),
(4, 'Docente', 'Gestión de cursos y evaluaciones', 6, '[\"eva.cursos\", \"evaluaciones.*\", \"calificaciones.*\"]', 1, '2025-09-03 04:17:23'),
(5, 'Tutor', 'Gestión tutorial y disciplinaria', 5, '[\"asistencia.*\", \"disciplina.*\", \"comunicacion.*\"]', 1, '2025-09-03 04:17:23'),
(6, 'Apoderado', 'Seguimiento académico del estudiante', 3, '[\"consulta.notas\", \"consulta.asistencia\", \"mensajeria.*\"]', 1, '2025-09-03 04:17:23'),
(7, 'Estudiante', 'Acceso a contenidos y actividades', 2, '[\"eva.contenidos\", \"eva.actividades\", \"consulta.notas\"]', 1, '2025-09-03 04:17:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutas_transporte`
--

CREATE TABLE `rutas_transporte` (
  `id` int NOT NULL,
  `codigo_ruta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracion` json DEFAULT NULL,
  `paraderos` json DEFAULT NULL,
  `tarifa` decimal(8,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id` int NOT NULL,
  `nivel_id` int NOT NULL,
  `grado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seccion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad_maxima` int DEFAULT '30',
  `aula_asignada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodo_academico_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `nivel_id`, `grado`, `seccion`, `codigo`, `capacidad_maxima`, `aula_asignada`, `periodo_academico_id`, `activo`) VALUES
(1, 1, '3 años', 'A', '3IA-2025', 20, 'Aula Inicial 01', 1, 1),
(2, 1, '4 años', 'A', '4IA-2025', 22, 'Aula Inicial 02', 1, 1),
(3, 1, '5 años', 'A', '5IA-2025', 25, 'Aula Inicial 03', 1, 1),
(4, 1, '5 años', 'B', '5IB-2025', 25, 'Aula Inicial 04', 1, 1),
(5, 2, '1ro', 'A', '1PA-2025', 30, 'Aula 101', 1, 1),
(6, 2, '1ro', 'B', '1PB-2025', 30, 'Aula 102', 1, 1),
(7, 2, '2do', 'A', '2PA-2025', 28, 'Aula 201', 1, 1),
(8, 2, '3ro', 'A', '3PA-2025', 26, 'Aula 301', 1, 1),
(9, 2, '4to', 'A', '4PA-2025', 28, 'Aula 401', 1, 1),
(10, 2, '5to', 'A', '5PA-2025', 25, 'Aula 501', 1, 1),
(11, 2, '6to', 'A', '6PA-2025', 24, 'Aula 601', 1, 1),
(12, 3, '1ro', 'A', '1SA-2025', 32, 'Aula 1001', 1, 1),
(13, 3, '1ro', 'B', '1SB-2025', 30, 'Aula 1002', 1, 1),
(14, 3, '2do', 'A', '2SA-2025', 29, 'Aula 1101', 1, 1),
(15, 3, '3ro', 'A', '3SA-2025', 27, 'Aula 1201', 1, 1),
(16, 3, '4to', 'A', '4SA-2025', 26, 'Aula 1301', 1, 1),
(17, 3, '5to', 'A', '5SA-2025', 25, 'Aula 1401', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuario`
--

CREATE TABLE `sesiones_usuario` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token_sesion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `fecha_inicio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` timestamp NOT NULL,
  `activa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int NOT NULL,
  `curso_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `instrucciones` text COLLATE utf8mb4_unicode_ci,
  `configuracion` json DEFAULT NULL,
  `configuracion_entrega` json DEFAULT NULL,
  `rubricas` json DEFAULT NULL,
  `estado` enum('BORRADOR','PUBLICADA','CERRADA') COLLATE utf8mb4_unicode_ci DEFAULT 'BORRADOR',
  `usuario_creacion` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `curso_id`, `titulo`, `descripcion`, `instrucciones`, `configuracion`, `configuracion_entrega`, `rubricas`, `estado`, `usuario_creacion`, `fecha_creacion`) VALUES
(1, 2, 'Ensayo: Mi Familia', 'Redacción de un ensayo descriptivo sobre la familia', 'Redacta un ensayo de 2 páginas (mínimo 500 palabras) donde describas a tu familia, sus características, tradiciones y lo que más valoras de ella. Utiliza conectores apropiados y cuida la ortografía.', '{\"peso\": 1.5, \"tipo_entrega\": \"ARCHIVO\", \"fecha_entrega\": \"2025-04-08 23:59:59\", \"fecha_apertura\": \"2025-04-01 08:00:00\", \"fecha_asignacion\": \"2025-04-01 08:00:00\", \"calificacion_maxima\": 20.0, \"fecha_limite_gracia\": \"2025-04-10 23:59:59\"}', '{\"descuento_tardio\": 2.0, \"permite_entregas_tardias\": true, \"retroalimentacion_automatica\": false}', '[{\"peso\": 40, \"niveles\": [{\"max\": 20, \"min\": 18, \"nombre\": \"Excelente\", \"descripcion\": \"Contenido muy rico y texto muy coherente\"}, {\"max\": 17, \"min\": 14, \"nombre\": \"Bueno\", \"descripcion\": \"Contenido adecuado y texto coherente\"}, {\"max\": 13, \"min\": 11, \"nombre\": \"Regular\", \"descripcion\": \"Contenido básico, coherencia parcial\"}, {\"max\": 10, \"min\": 0, \"nombre\": \"Deficiente\", \"descripcion\": \"Contenido pobre, poca coherencia\"}], \"criterio\": \"Contenido y Coherencia\", \"descripcion\": \"Calidad del contenido y coherencia del texto\"}, {\"peso\": 30, \"niveles\": [{\"max\": 20, \"min\": 18, \"nombre\": \"Excelente\", \"descripcion\": \"Sin errores ortográficos ni gramaticales\"}, {\"max\": 17, \"min\": 14, \"nombre\": \"Bueno\", \"descripcion\": \"Mínimos errores\"}, {\"max\": 13, \"min\": 11, \"nombre\": \"Regular\", \"descripcion\": \"Algunos errores que no afectan comprensión\"}, {\"max\": 10, \"min\": 0, \"nombre\": \"Deficiente\", \"descripcion\": \"Muchos errores que dificultan comprensión\"}], \"criterio\": \"Ortografía y Gramática\", \"descripcion\": \"Corrección en el uso del idioma\"}, {\"peso\": 30, \"niveles\": [{\"max\": 20, \"min\": 18, \"nombre\": \"Excelente\", \"descripcion\": \"Muy creativo y estilo personal\"}, {\"max\": 17, \"min\": 14, \"nombre\": \"Bueno\", \"descripcion\": \"Creativo con buen estilo\"}, {\"max\": 13, \"min\": 11, \"nombre\": \"Regular\", \"descripcion\": \"Algo creativo, estilo básico\"}, {\"max\": 10, \"min\": 0, \"nombre\": \"Deficiente\", \"descripcion\": \"Poco creativo, sin estilo\"}], \"criterio\": \"Creatividad y Estilo\", \"descripcion\": \"Originalidad y estilo personal en la redacción\"}]', 'PUBLICADA', 6, '2025-09-03 04:28:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_comedor`
--

CREATE TABLE `transacciones_comedor` (
  `id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `tipo` enum('RECARGA','CONSUMO','AJUSTE','DEVOLUCION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` json DEFAULT NULL,
  `pedido` json DEFAULT NULL,
  `metadatos` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutoria_seguimientos`
--

CREATE TABLE `tutoria_seguimientos` (
  `id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `incidencia_id` int DEFAULT NULL,
  `tipo` enum('ACADEMICO','DISCIPLINARIO','PERSONAL','FAMILIAR') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sesiones` json DEFAULT NULL,
  `estado` enum('ABIERTO','EN_PROCESO','CERRADO') COLLATE utf8mb4_unicode_ci DEFAULT 'ABIERTO',
  `tutor_id` int DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tutoria_seguimientos`
--

INSERT INTO `tutoria_seguimientos` (`id`, `estudiante_id`, `incidencia_id`, `tipo`, `motivo`, `sesiones`, `estado`, `tutor_id`, `fecha_creacion`) VALUES
(1, 3, 1, 'DISCIPLINARIO', 'Seguimiento por interrupciones constantes en clase de matemática', '[{\"fecha\": \"2025-03-25\", \"acuerdos\": \"El estudiante se compromete a levantar la mano antes de hablar y a respetar las participaciones de sus compañeros\", \"duracion\": 30, \"observaciones\": \"Mostró buena disposición para mejorar. Se establecieron reglas claras.\", \"proximo_seguimiento\": \"2025-04-08\"}]', 'EN_PROCESO', 1, '2025-09-03 04:28:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades`
--

CREATE TABLE `unidades` (
  `id` int NOT NULL,
  `curso_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL,
  `configuraciones` json DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unidades`
--

INSERT INTO `unidades` (`id`, `curso_id`, `titulo`, `descripcion`, `orden`, `configuraciones`, `fecha_creacion`) VALUES
(1, 1, 'Números Enteros y Racionales', 'Introducción a los números enteros, operaciones básicas y números racionales', 1, '{\"estado\": \"PUBLICADO\", \"fecha_fin\": \"2025-04-30\", \"fecha_inicio\": \"2025-03-11\"}', '2025-09-03 04:25:42'),
(2, 1, 'Álgebra Básica', 'Introducción al álgebra, expresiones algebraicas y ecuaciones simples', 2, '{\"estado\": \"PUBLICADO\", \"fecha_fin\": \"2025-06-20\", \"fecha_inicio\": \"2025-05-01\"}', '2025-09-03 04:25:42'),
(3, 1, 'Geometría Plana', 'Figuras geométricas, perímetros, áreas y teorema de Pitágoras', 3, '{\"estado\": \"BORRADOR\", \"fecha_fin\": \"2025-10-15\", \"fecha_inicio\": \"2025-08-11\"}', '2025-09-03 04:25:42'),
(4, 2, 'Comprensión Lectora', 'Técnicas de lectura y comprensión de textos diversos', 1, '{\"estado\": \"PUBLICADO\", \"fecha_fin\": \"2025-05-10\", \"fecha_inicio\": \"2025-03-11\"}', '2025-09-03 04:25:42'),
(5, 2, 'Producción de Textos', 'Redacción de textos narrativos, descriptivos y expositivos', 2, '{\"estado\": \"PUBLICADO\", \"fecha_fin\": \"2025-07-20\", \"fecha_inicio\": \"2025-05-11\"}', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `codigo_usuario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento_tipo` enum('DNI','CE','PASAPORTE') COLLATE utf8mb4_unicode_ci DEFAULT 'DNI',
  `documento_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `foto_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos_personales` json DEFAULT NULL,
  `rol_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `configuraciones` json DEFAULT NULL,
  `debe_cambiar_password` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `codigo_usuario`, `username`, `email`, `password_hash`, `nombres`, `apellidos`, `documento_tipo`, `documento_numero`, `telefono`, `direccion`, `foto_url`, `datos_personales`, `rol_id`, `activo`, `ultimo_acceso`, `configuraciones`, `debe_cambiar_password`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'DIR001', 'director.aac', 'director@aac.edu.pe', '$2y$12$LKmN9pQrS3tU4vWxY5zA6e', 'Carlos Eduardo', 'Mendoza Reyes', 'DNI', '08123456', '056-234567', 'Av. Cutervo 1245, Ica', NULL, NULL, 2, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(2, 'SUB001', 'subdirector.aac', 'subdirector@aac.edu.pe', '$2y$12$MnOp9qRsT4uV5wXyZ6aB7f', 'María Teresa', 'Vásquez Flores', 'DNI', '08234567', '056-345678', NULL, NULL, NULL, 3, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(3, 'DOC001', 'lcorrea', 'lcorrea@aac.edu.pe', '$2y$12$NoP0rQsT5vW6xYzA7bC8g', 'Luis Fernando', 'Correa Mendoza', 'DNI', '08345678', '956-123456', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(4, 'DOC002', 'mrojas', 'mrojas@aac.edu.pe', '$2y$12$OpQ1sRtU6wX7yZaB8cD9h', 'María Isabel', 'Rojas Castillo', 'DNI', '08456789', '956-234567', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(5, 'DOC003', 'jherrera', 'jherrera@aac.edu.pe', '$2y$12$PqR2tSuV7xY8zAbC9dE0i', 'José Antonio', 'Herrera Díaz', 'DNI', '08567890', '956-345678', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(6, 'DOC004', 'agarcia', 'agarcia@aac.edu.pe', '$2y$12$QrS3uTvW8yZ9aBcD0eF1j', 'Ana Lucía', 'García Morales', 'DNI', '08678901', '956-456789', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(7, 'DOC005', 'rtorres', 'rtorres@aac.edu.pe', '$2y$12$RsT4vUwX9zA0bCdE1fG2k', 'Ricardo Manuel', 'Torres Sánchez', 'DNI', '08789012', '956-567890', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(8, 'DOC006', 'plopez', 'plopez@aac.edu.pe', '$2y$12$StU5wVxY0aB1cDeF2gH3l', 'Patricia Elena', 'López Rivera', 'DNI', '08890123', '956-678901', NULL, NULL, NULL, 4, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(9, 'APO001', 'cgomez', 'cgomez@gmail.com', '$2y$12$TuV6wXyZ1aB2cDeF3gH4m', 'Carlos Andrés', 'Gómez Valdez', 'DNI', '08111111', '956-111111', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(10, 'APO002', 'lperez', 'lperez@hotmail.com', '$2y$12$UvW7xYzA2bC3dEfG4hI5n', 'Lucía Mercedes', 'Pérez Guerrero', 'DNI', '08222222', '956-222222', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(11, 'APO003', 'rmartinez', 'rmartinez@gmail.com', '$2y$12$VwX8yZaB3cD4eFgH5iJ6o', 'Roberto Miguel', 'Martínez Silva', 'DNI', '08333333', '956-333333', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(12, 'APO004', 'acastro', 'acastro@outlook.com', '$2y$12$WxY9zAbC4dE5fGhI6jK7p', 'Ana Sofía', 'Castro Mendoza', 'DNI', '08444444', '956-444444', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(13, 'APO005', 'jvargas', 'jvargas@yahoo.com', '$2y$12$XyZ0aBcD5eF6gHiJ7kL8q', 'Jorge Luis', 'Vargas Ramos', 'DNI', '08555555', '956-555555', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(14, 'APO006', 'mfernandez', 'mfernandez@gmail.com', '$2y$12$YzA1bCdE6fG7hIjK8lM9r', 'María Elena', 'Fernández Torres', 'DNI', '08666666', '956-666666', NULL, NULL, NULL, 6, 1, NULL, NULL, 0, '2025-09-03 04:25:41', '2025-09-03 04:25:41'),
(15, 'EST001', 'diego.gomez', 'diego.gomez@aac.edu.pe', '$2y$12$ZaB2cDeF4gH5iJ6kL7mN8', 'Diego Sebastián', 'Gómez Pérez', 'DNI', '09111111', '956-111112', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(16, 'EST002', 'sofia.martinez', 'sofia.martinez@aac.edu.pe', '$2y$12$AbC3dEfG5hI6jK7lM8nO9', 'Sofía Isabella', 'Martínez García', 'DNI', '09222222', '956-222223', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(17, 'EST003', 'mateo.castro', 'mateo.castro@aac.edu.pe', '$2y$12$BcD4eFgH6iJ7kL8mN9oP0', 'Mateo Alejandro', 'Castro Morales', 'DNI', '09333333', '956-333334', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(18, 'EST004', 'valeria.vargas', 'valeria.vargas@aac.edu.pe', '$2y$12$CdE5fGhI7jK8lM9nO0pQ1', 'Valeria Camila', 'Vargas Herrera', 'DNI', '09444444', '956-444445', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(19, 'EST005', 'lucas.fernandez', 'lucas.fernandez@aac.edu.pe', '$2y$12$DeF6gHiJ8kL9mN0oP1qR2', 'Lucas Emilio', 'Fernández Díaz', 'DNI', '09555555', '956-555556', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(20, 'EST006', 'isabella.torres', 'isabella.torres@aac.edu.pe', '$2y$12$EfG7hIjK9lM0nO1pQ2rS3', 'Isabella Nicole', 'Torres Mendoza', 'DNI', '09666666', '956-666667', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(21, 'EST007', 'adrian.lopez', 'adrian.lopez@aac.edu.pe', '$2y$12$FgH8iJkL0mN1oP2qR3sT4', 'Adrián Joaquín', 'López Ruiz', 'DNI', '09777777', '956-777778', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42'),
(22, 'EST008', 'camila.rodriguez', 'camila.rodriguez@aac.edu.pe', '$2y$12$GhI9jKlM1nO2pQ3rS4tU5', 'Camila Antonella', 'Rodríguez Silva', 'DNI', '09888888', '956-888889', NULL, NULL, NULL, 7, 1, NULL, NULL, 1, '2025-09-03 04:25:42', '2025-09-03 04:25:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos_transporte`
--

CREATE TABLE `vehiculos_transporte` (
  `id` int NOT NULL,
  `placa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_vehiculo` json DEFAULT NULL,
  `documentacion` json DEFAULT NULL,
  `personal` json DEFAULT NULL,
  `estado` enum('ACTIVO','MANTENIMIENTO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_activo` (`codigo_activo`),
  ADD KEY `idx_activos_codigo` (`codigo_activo`);

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_creacion` (`usuario_creacion`);

--
-- Indices de la tabla `apoderados`
--
ALTER TABLE `apoderados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD UNIQUE KEY `documento_numero` (`documento_numero`);

--
-- Indices de la tabla `areas_curriculares`
--
ALTER TABLE `areas_curriculares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `asignaciones_docentes`
--
ALTER TABLE `asignaciones_docentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `periodo_academico_id` (`periodo_academico_id`),
  ADD KEY `idx_asignaciones_docente` (`docente_id`),
  ADD KEY `idx_asignaciones_seccion` (`seccion_id`);

--
-- Indices de la tabla `asignaciones_transporte`
--
ALTER TABLE `asignaciones_transporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `ruta_id` (`ruta_id`),
  ADD KEY `periodo_academico_id` (`periodo_academico_id`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asignacion_id` (`asignacion_id`),
  ADD KEY `docente_id` (`docente_id`),
  ADD KEY `idx_asistencias_estudiante_fecha` (`estudiante_id`,`fecha`),
  ADD KEY `idx_asistencias_fecha` (`fecha`);

--
-- Indices de la tabla `asistencia_transporte`
--
ALTER TABLE `asistencia_transporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asignacion_id` (`asignacion_id`),
  ADD KEY `usuario_registra` (`usuario_registra`);

--
-- Indices de la tabla `atenciones_medicas`
--
ALTER TABLE `atenciones_medicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `enfermero_atiende` (`enfermero_atiende`);

--
-- Indices de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_usuario_fecha` (`usuario_id`,`fecha_evento`);

--
-- Indices de la tabla `bancos_preguntas`
--
ALTER TABLE `bancos_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `docente_id` (`docente_id`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `docente_id` (`docente_id`),
  ADD KEY `idx_calificaciones_estudiante` (`estudiante_id`),
  ADD KEY `idx_calificaciones_asignacion` (`asignacion_id`);

--
-- Indices de la tabla `comunicaciones_admision`
--
ALTER TABLE `comunicaciones_admision`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postulacion_id` (`postulacion_id`);

--
-- Indices de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuentas_comedor`
--
ALTER TABLE `cuentas_comedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_creacion` (`usuario_creacion`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_curso` (`codigo_curso`),
  ADD KEY `idx_cursos_asignacion` (`asignacion_id`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_docente` (`codigo_docente`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD UNIQUE KEY `documento_numero` (`documento_numero`);

--
-- Indices de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_inventario` (`codigo_inventario`),
  ADD KEY `material_id` (`material_id`);

--
-- Indices de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `docente_calificador` (`docente_calificador`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_estudiante` (`codigo_estudiante`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD UNIQUE KEY `documento_numero` (`documento_numero`),
  ADD KEY `idx_estudiantes_codigo` (`codigo_estudiante`);

--
-- Indices de la tabla `estudiante_apoderados`
--
ALTER TABLE `estudiante_apoderados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `apoderado_id` (`apoderado_id`);

--
-- Indices de la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `usuario_actualiza` (`usuario_actualiza`);

--
-- Indices de la tabla `foros`
--
ALTER TABLE `foros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_creacion` (`usuario_creacion`);

--
-- Indices de la tabla `incidencias_disciplinarias`
--
ALTER TABLE `incidencias_disciplinarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `docente_reporta` (`docente_reporta`);

--
-- Indices de la tabla `intentos_cuestionarios`
--
ALTER TABLE `intentos_cuestionarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `idx_intentos_cuestionario` (`cuestionario_id`);

--
-- Indices de la tabla `inventario_enfermeria`
--
ALTER TABLE `inventario_enfermeria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unidad_id` (`unidad_id`);

--
-- Indices de la tabla `malla_curricular`
--
ALTER TABLE `malla_curricular`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nivel_id` (`nivel_id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `periodo_academico_id` (`periodo_academico_id`);

--
-- Indices de la tabla `materiales_repuestos`
--
ALTER TABLE `materiales_repuestos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `material_bibliografico`
--
ALTER TABLE `material_bibliografico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `idx_material_codigo` (`codigo_barras`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_matricula` (`codigo_matricula`),
  ADD KEY `seccion_id` (`seccion_id`),
  ADD KEY `idx_matriculas_estudiante` (`estudiante_id`),
  ADD KEY `idx_matriculas_periodo` (`periodo_academico_id`);

--
-- Indices de la tabla `mensajeria`
--
ALTER TABLE `mensajeria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remitente_id` (`remitente_id`);

--
-- Indices de la tabla `menus_comedor`
--
ALTER TABLE `menus_comedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `metricas_sistema`
--
ALTER TABLE `metricas_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fecha_metrica` (`fecha`);

--
-- Indices de la tabla `movimientos_activos`
--
ALTER TABLE `movimientos_activos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activo_id` (`activo_id`),
  ADD KEY `usuario_registra` (`usuario_registra`);

--
-- Indices de la tabla `multas_biblioteca`
--
ALTER TABLE `multas_biblioteca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `usuario_genera` (`usuario_genera`);

--
-- Indices de la tabla `niveles_educativos`
--
ALTER TABLE `niveles_educativos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `notificaciones_sistema`
--
ALTER TABLE `notificaciones_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_orden` (`numero_orden`),
  ADD KEY `activo_id` (`activo_id`);

--
-- Indices de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_postulacion` (`codigo_postulacion`),
  ADD KEY `proceso_id` (`proceso_id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banco_id` (`banco_id`);

--
-- Indices de la tabla `prestamos_biblioteca`
--
ALTER TABLE `prestamos_biblioteca`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ejemplar_id` (`ejemplar_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `procesos_admision`
--
ALTER TABLE `procesos_admision`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `progreso_estudiantes`
--
ALTER TABLE `progreso_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_estudiante_leccion` (`estudiante_id`,`leccion_id`),
  ADD KEY `idx_progreso_estudiante` (`estudiante_id`),
  ADD KEY `idx_progreso_leccion` (`leccion_id`);

--
-- Indices de la tabla `reportes_configurados`
--
ALTER TABLE `reportes_configurados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_creacion` (`usuario_creacion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rutas_transporte`
--
ALTER TABLE `rutas_transporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_ruta` (`codigo_ruta`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `nivel_id` (`nivel_id`),
  ADD KEY `periodo_academico_id` (`periodo_academico_id`);

--
-- Indices de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_sesion` (`token_sesion`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_sesiones_token` (`token_sesion`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_creacion` (`usuario_creacion`);

--
-- Indices de la tabla `transacciones_comedor`
--
ALTER TABLE `transacciones_comedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cuenta_id` (`cuenta_id`);

--
-- Indices de la tabla `tutoria_seguimientos`
--
ALTER TABLE `tutoria_seguimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `incidencia_id` (`incidencia_id`),
  ADD KEY `tutor_id` (`tutor_id`);

--
-- Indices de la tabla `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `codigo_usuario` (`codigo_usuario`),
  ADD UNIQUE KEY `documento_numero` (`documento_numero`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `idx_usuarios_username` (`username`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_documento` (`documento_numero`),
  ADD KEY `idx_usuarios_activo` (`activo`);

--
-- Indices de la tabla `vehiculos_transporte`
--
ALTER TABLE `vehiculos_transporte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `apoderados`
--
ALTER TABLE `apoderados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `areas_curriculares`
--
ALTER TABLE `areas_curriculares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `asignaciones_docentes`
--
ALTER TABLE `asignaciones_docentes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `asignaciones_transporte`
--
ALTER TABLE `asignaciones_transporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `asistencia_transporte`
--
ALTER TABLE `asistencia_transporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atenciones_medicas`
--
ALTER TABLE `atenciones_medicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bancos_preguntas`
--
ALTER TABLE `bancos_preguntas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `comunicaciones_admision`
--
ALTER TABLE `comunicaciones_admision`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_sistema`
--
ALTER TABLE `configuracion_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuentas_comedor`
--
ALTER TABLE `cuentas_comedor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `estudiante_apoderados`
--
ALTER TABLE `estudiante_apoderados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `foros`
--
ALTER TABLE `foros`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `incidencias_disciplinarias`
--
ALTER TABLE `incidencias_disciplinarias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `intentos_cuestionarios`
--
ALTER TABLE `intentos_cuestionarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventario_enfermeria`
--
ALTER TABLE `inventario_enfermeria`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lecciones`
--
ALTER TABLE `lecciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `malla_curricular`
--
ALTER TABLE `malla_curricular`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materiales_repuestos`
--
ALTER TABLE `materiales_repuestos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `material_bibliografico`
--
ALTER TABLE `material_bibliografico`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mensajeria`
--
ALTER TABLE `mensajeria`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `menus_comedor`
--
ALTER TABLE `menus_comedor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metricas_sistema`
--
ALTER TABLE `metricas_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `movimientos_activos`
--
ALTER TABLE `movimientos_activos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `multas_biblioteca`
--
ALTER TABLE `multas_biblioteca`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `niveles_educativos`
--
ALTER TABLE `niveles_educativos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificaciones_sistema`
--
ALTER TABLE `notificaciones_sistema`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `periodos_academicos`
--
ALTER TABLE `periodos_academicos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `prestamos_biblioteca`
--
ALTER TABLE `prestamos_biblioteca`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `procesos_admision`
--
ALTER TABLE `procesos_admision`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `progreso_estudiantes`
--
ALTER TABLE `progreso_estudiantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reportes_configurados`
--
ALTER TABLE `reportes_configurados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `rutas_transporte`
--
ALTER TABLE `rutas_transporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `transacciones_comedor`
--
ALTER TABLE `transacciones_comedor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tutoria_seguimientos`
--
ALTER TABLE `tutoria_seguimientos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `vehiculos_transporte`
--
ALTER TABLE `vehiculos_transporte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD CONSTRAINT `anuncios_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `anuncios_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `apoderados`
--
ALTER TABLE `apoderados`
  ADD CONSTRAINT `apoderados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `asignaciones_docentes`
--
ALTER TABLE `asignaciones_docentes`
  ADD CONSTRAINT `asignaciones_docentes_ibfk_1` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`),
  ADD CONSTRAINT `asignaciones_docentes_ibfk_2` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`),
  ADD CONSTRAINT `asignaciones_docentes_ibfk_3` FOREIGN KEY (`area_id`) REFERENCES `areas_curriculares` (`id`),
  ADD CONSTRAINT `asignaciones_docentes_ibfk_4` FOREIGN KEY (`periodo_academico_id`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `asignaciones_transporte`
--
ALTER TABLE `asignaciones_transporte`
  ADD CONSTRAINT `asignaciones_transporte_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos_transporte` (`id`),
  ADD CONSTRAINT `asignaciones_transporte_ibfk_2` FOREIGN KEY (`ruta_id`) REFERENCES `rutas_transporte` (`id`),
  ADD CONSTRAINT `asignaciones_transporte_ibfk_3` FOREIGN KEY (`periodo_academico_id`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones_docentes` (`id`),
  ADD CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `asistencia_transporte`
--
ALTER TABLE `asistencia_transporte`
  ADD CONSTRAINT `asistencia_transporte_ibfk_1` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones_transporte` (`id`),
  ADD CONSTRAINT `asistencia_transporte_ibfk_2` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `atenciones_medicas`
--
ALTER TABLE `atenciones_medicas`
  ADD CONSTRAINT `atenciones_medicas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `atenciones_medicas_ibfk_2` FOREIGN KEY (`enfermero_atiende`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `auditoria_sistema`
--
ALTER TABLE `auditoria_sistema`
  ADD CONSTRAINT `auditoria_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `bancos_preguntas`
--
ALTER TABLE `bancos_preguntas`
  ADD CONSTRAINT `bancos_preguntas_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas_curriculares` (`id`),
  ADD CONSTRAINT `bancos_preguntas_ibfk_2` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones_docentes` (`id`),
  ADD CONSTRAINT `calificaciones_ibfk_3` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `comunicaciones_admision`
--
ALTER TABLE `comunicaciones_admision`
  ADD CONSTRAINT `comunicaciones_admision_ibfk_1` FOREIGN KEY (`postulacion_id`) REFERENCES `postulaciones` (`id`);

--
-- Filtros para la tabla `cuentas_comedor`
--
ALTER TABLE `cuentas_comedor`
  ADD CONSTRAINT `cuentas_comedor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `cuestionarios`
--
ALTER TABLE `cuestionarios`
  ADD CONSTRAINT `cuestionarios_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `cuestionarios_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones_docentes` (`id`);

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `docentes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD CONSTRAINT `ejemplares_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `material_bibliografico` (`id`);

--
-- Filtros para la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD CONSTRAINT `entregas_tareas_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`),
  ADD CONSTRAINT `entregas_tareas_ibfk_2` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `entregas_tareas_ibfk_3` FOREIGN KEY (`docente_calificador`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `estudiante_apoderados`
--
ALTER TABLE `estudiante_apoderados`
  ADD CONSTRAINT `estudiante_apoderados_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `estudiante_apoderados_ibfk_2` FOREIGN KEY (`apoderado_id`) REFERENCES `apoderados` (`id`);

--
-- Filtros para la tabla `fichas_medicas`
--
ALTER TABLE `fichas_medicas`
  ADD CONSTRAINT `fichas_medicas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `fichas_medicas_ibfk_2` FOREIGN KEY (`usuario_actualiza`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `foros`
--
ALTER TABLE `foros`
  ADD CONSTRAINT `foros_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `foros_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `incidencias_disciplinarias`
--
ALTER TABLE `incidencias_disciplinarias`
  ADD CONSTRAINT `incidencias_disciplinarias_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `incidencias_disciplinarias_ibfk_2` FOREIGN KEY (`docente_reporta`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `intentos_cuestionarios`
--
ALTER TABLE `intentos_cuestionarios`
  ADD CONSTRAINT `intentos_cuestionarios_ibfk_1` FOREIGN KEY (`cuestionario_id`) REFERENCES `cuestionarios` (`id`),
  ADD CONSTRAINT `intentos_cuestionarios_ibfk_2` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`);

--
-- Filtros para la tabla `lecciones`
--
ALTER TABLE `lecciones`
  ADD CONSTRAINT `lecciones_ibfk_1` FOREIGN KEY (`unidad_id`) REFERENCES `unidades` (`id`);

--
-- Filtros para la tabla `malla_curricular`
--
ALTER TABLE `malla_curricular`
  ADD CONSTRAINT `malla_curricular_ibfk_1` FOREIGN KEY (`nivel_id`) REFERENCES `niveles_educativos` (`id`),
  ADD CONSTRAINT `malla_curricular_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas_curriculares` (`id`),
  ADD CONSTRAINT `malla_curricular_ibfk_3` FOREIGN KEY (`periodo_academico_id`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_3` FOREIGN KEY (`periodo_academico_id`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `mensajeria`
--
ALTER TABLE `mensajeria`
  ADD CONSTRAINT `mensajeria_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `movimientos_activos`
--
ALTER TABLE `movimientos_activos`
  ADD CONSTRAINT `movimientos_activos_ibfk_1` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`),
  ADD CONSTRAINT `movimientos_activos_ibfk_2` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `multas_biblioteca`
--
ALTER TABLE `multas_biblioteca`
  ADD CONSTRAINT `multas_biblioteca_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos_biblioteca` (`id`),
  ADD CONSTRAINT `multas_biblioteca_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `multas_biblioteca_ibfk_3` FOREIGN KEY (`usuario_genera`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notificaciones_sistema`
--
ALTER TABLE `notificaciones_sistema`
  ADD CONSTRAINT `notificaciones_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ordenes_trabajo`
--
ALTER TABLE `ordenes_trabajo`
  ADD CONSTRAINT `ordenes_trabajo_ibfk_1` FOREIGN KEY (`activo_id`) REFERENCES `activos` (`id`);

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `postulaciones_ibfk_1` FOREIGN KEY (`proceso_id`) REFERENCES `procesos_admision` (`id`);

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`banco_id`) REFERENCES `bancos_preguntas` (`id`);

--
-- Filtros para la tabla `prestamos_biblioteca`
--
ALTER TABLE `prestamos_biblioteca`
  ADD CONSTRAINT `prestamos_biblioteca_ibfk_1` FOREIGN KEY (`ejemplar_id`) REFERENCES `ejemplares` (`id`),
  ADD CONSTRAINT `prestamos_biblioteca_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `progreso_estudiantes`
--
ALTER TABLE `progreso_estudiantes`
  ADD CONSTRAINT `progreso_estudiantes_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `progreso_estudiantes_ibfk_2` FOREIGN KEY (`leccion_id`) REFERENCES `lecciones` (`id`);

--
-- Filtros para la tabla `reportes_configurados`
--
ALTER TABLE `reportes_configurados`
  ADD CONSTRAINT `reportes_configurados_ibfk_1` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD CONSTRAINT `secciones_ibfk_1` FOREIGN KEY (`nivel_id`) REFERENCES `niveles_educativos` (`id`),
  ADD CONSTRAINT `secciones_ibfk_2` FOREIGN KEY (`periodo_academico_id`) REFERENCES `periodos_academicos` (`id`);

--
-- Filtros para la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD CONSTRAINT `sesiones_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `transacciones_comedor`
--
ALTER TABLE `transacciones_comedor`
  ADD CONSTRAINT `transacciones_comedor_ibfk_1` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_comedor` (`id`);

--
-- Filtros para la tabla `tutoria_seguimientos`
--
ALTER TABLE `tutoria_seguimientos`
  ADD CONSTRAINT `tutoria_seguimientos_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`),
  ADD CONSTRAINT `tutoria_seguimientos_ibfk_2` FOREIGN KEY (`incidencia_id`) REFERENCES `incidencias_disciplinarias` (`id`),
  ADD CONSTRAINT `tutoria_seguimientos_ibfk_3` FOREIGN KEY (`tutor_id`) REFERENCES `docentes` (`id`);

--
-- Filtros para la tabla `unidades`
--
ALTER TABLE `unidades`
  ADD CONSTRAINT `unidades_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
