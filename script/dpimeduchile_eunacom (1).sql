-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-11-2025 a las 12:53:18
-- Versión del servidor: 5.7.44-log
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dpimeduchile_eunacom`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alternativas`
--

CREATE TABLE `alternativas` (
  `id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `opcion` char(1) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A, B, C, D, E',
  `texto_alternativa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `es_correcta` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`id`, `nombre`, `activo`, `created_at`) VALUES
(1, 'Medicina Interna', 1, '2025-11-09 17:39:25'),
(2, 'Pediatría', 1, '2025-11-09 17:39:25'),
(3, 'Obstetricia y Ginecología', 1, '2025-11-09 17:39:25'),
(4, 'Cirugía', 1, '2025-11-09 17:39:25'),
(5, 'Psiquiatría', 1, '2025-11-09 17:39:25'),
(6, 'Especialidades', 1, '2025-11-09 17:39:25'),
(7, 'Salud Pública', 1, '2025-11-09 17:39:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_estudio`
--

CREATE TABLE `documentos_estudio` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `especialidad_id` int(11) NOT NULL,
  `nombre_documento` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_relativa` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_web` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano_kb` int(11) DEFAULT '0',
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'pdf',
  `orden` int(11) DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `codigo_especialidad` int(11) NOT NULL COMMENT 'Número dentro del área (01, 02, etc)',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `area_id`, `codigo_especialidad`, `nombre`, `activo`, `created_at`) VALUES
(1, 1, 1, 'Cardiología', 1, '2025-11-09 17:39:25'),
(2, 1, 2, 'Diabetes y Nutrición', 1, '2025-11-09 17:39:25'),
(3, 1, 3, 'Endocrinología', 1, '2025-11-09 17:39:25'),
(4, 1, 4, 'Enfermedades Infecciosas', 1, '2025-11-09 17:39:25'),
(5, 1, 5, 'Enfermedades Respiratorias', 1, '2025-11-09 17:39:25'),
(6, 1, 6, 'Gastroenterología', 1, '2025-11-09 17:39:25'),
(7, 1, 7, 'Geriatría', 1, '2025-11-09 17:39:25'),
(8, 1, 8, 'Hémato-oncología', 1, '2025-11-09 17:39:25'),
(9, 1, 9, 'Nefrología', 1, '2025-11-09 17:39:25'),
(10, 1, 10, 'Neurología', 1, '2025-11-09 17:39:25'),
(11, 1, 11, 'Reumatología', 1, '2025-11-09 17:39:25'),
(12, 2, 1, 'Pediatría General', 1, '2025-11-09 17:39:25'),
(13, 3, 1, 'Obstetricia y Ginecología', 1, '2025-11-09 17:39:25'),
(14, 4, 1, 'Cirugía General y Anestesia', 1, '2025-11-09 17:39:25'),
(15, 4, 2, 'Traumatología', 1, '2025-11-09 17:39:25'),
(16, 4, 3, 'Urología', 1, '2025-11-09 17:39:25'),
(17, 5, 1, 'Psiquiatría General', 1, '2025-11-09 17:39:25'),
(18, 6, 1, 'Dermatología', 1, '2025-11-09 17:39:25'),
(19, 6, 2, 'Oftalmología', 1, '2025-11-09 17:39:25'),
(20, 6, 3, 'Otorrinolaringología', 1, '2025-11-09 17:39:25'),
(21, 7, 1, 'Salud Pública', 1, '2025-11-09 17:39:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes`
--

CREATE TABLE `examenes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo_examen` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del examen',
  `tipo_examen` enum('simulacro') COLLATE utf8mb4_unicode_ci DEFAULT 'simulacro',
  `estado` enum('en_curso','sesion1_completa','finalizado','abandonado') COLLATE utf8mb4_unicode_ci DEFAULT 'en_curso',
  `sesion_actual` tinyint(4) DEFAULT '1' COMMENT '1 o 2',
  `fecha_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin_sesion1` timestamp NULL DEFAULT NULL,
  `fecha_inicio_sesion2` timestamp NULL DEFAULT NULL,
  `fecha_finalizacion` timestamp NULL DEFAULT NULL,
  `tiempo_restante_sesion1` int(11) DEFAULT '5400' COMMENT 'Segundos (90 min = 5400)',
  `tiempo_restante_sesion2` int(11) DEFAULT '5400',
  `total_preguntas` int(11) DEFAULT '180',
  `preguntas_respondidas` int(11) DEFAULT '0',
  `respuestas_correctas` int(11) DEFAULT '0',
  `respuestas_incorrectas` int(11) DEFAULT '0',
  `preguntas_omitidas` int(11) DEFAULT '0',
  `puntaje_porcentaje` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen_preguntas`
--

CREATE TABLE `examen_preguntas` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `sesion` tinyint(4) NOT NULL COMMENT '1 o 2',
  `orden` int(11) NOT NULL COMMENT 'Orden dentro de la sesión (1-90)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `explicaciones`
--

CREATE TABLE `explicaciones` (
  `id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_correcta` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle_respuesta` text COLLATE utf8mb4_unicode_ci COMMENT 'Detalle breve de la pauta',
  `explicacion_completa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_actividad`
--

CREATE TABLE `log_actividad` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'login, logout, inicio_simulacro, etc',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de actividad del sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `numero_pregunta` int(11) NOT NULL COMMENT 'Número dentro del tema (1-20)',
  `texto_pregunta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `activa` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_estudiante`
--

CREATE TABLE `progreso_estudiante` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `temas_completados` int(11) DEFAULT '0',
  `total_preguntas_respondidas` int(11) DEFAULT '0',
  `preguntas_correctas` int(11) DEFAULT '0',
  `porcentaje_aciertos` decimal(5,2) DEFAULT '0.00',
  `tiempo_total_estudio_min` int(11) DEFAULT '0',
  `ultima_actividad` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Progreso de estudio por área';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_usuario`
--

CREATE TABLE `respuestas_usuario` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `alternativa_seleccionada` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A, B, C, D, E o NULL si omitió',
  `es_correcta` tinyint(1) DEFAULT '0',
  `tiempo_respuesta` int(11) DEFAULT NULL COMMENT 'Segundos desde inicio de sesión',
  `marcada_revision` tinyint(1) DEFAULT '0' COMMENT 'Usuario marcó para revisar',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token único de sesión',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión de sesiones de usuarios';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas`
--

CREATE TABLE `temas` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `especialidad_id` int(11) NOT NULL,
  `tipo_situacion_id` int(11) NOT NULL,
  `codigo_completo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: 1.01.3.006',
  `numero_correlativo` int(11) NOT NULL COMMENT 'Último número (006)',
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Embolia pulmonar',
  `total_preguntas` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_situacion`
--

CREATE TABLE `tipos_situacion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_situacion`
--

INSERT INTO `tipos_situacion` (`id`, `nombre`, `descripcion`, `activo`, `created_at`) VALUES
(1, 'Situaciones clínicas', 'Casos clínicos generales', 1, '2025-11-09 17:39:25'),
(2, 'Situaciones clínicas de urgencia', 'Casos de urgencia médica', 1, '2025-11-09 17:39:25'),
(3, 'Conocimientos generales', 'Conocimientos teóricos fundamentales', 1, '2025-11-09 17:39:25'),
(4, 'Exámenes e imagenología', 'Interpretación de exámenes y estudios', 1, '2025-11-09 17:39:25'),
(5, 'Procedimientos diagnósticos y terapéuticos', 'Procedimientos clínicos', 1, '2025-11-09 17:39:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_usuario` enum('estudiante','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'estudiante',
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_documentos_completos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_documentos_completos` (
`documento_id` int(11)
,`nombre_documento` varchar(300)
,`nombre_archivo` varchar(255)
,`ruta_relativa` varchar(500)
,`ruta_web` varchar(500)
,`tamano_kb` int(11)
,`orden` int(11)
,`area_id` int(11)
,`area_nombre` varchar(100)
,`especialidad_id` int(11)
,`codigo_especialidad` int(11)
,`especialidad_nombre` varchar(100)
,`activo` tinyint(1)
,`fecha_subida` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_resultados_examenes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_resultados_examenes` (
`examen_id` int(11)
,`codigo_examen` varchar(20)
,`usuario_nombre` varchar(100)
,`usuario_email` varchar(100)
,`estado` enum('en_curso','sesion1_completa','finalizado','abandonado')
,`fecha_inicio` timestamp
,`fecha_finalizacion` timestamp
,`total_preguntas` int(11)
,`respuestas_correctas` int(11)
,`respuestas_incorrectas` int(11)
,`preguntas_omitidas` int(11)
,`puntaje_porcentaje` decimal(5,2)
,`duracion_minutos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_temas_completos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_temas_completos` (
`tema_id` int(11)
,`codigo_completo` varchar(20)
,`tema_nombre` varchar(255)
,`total_preguntas` int(11)
,`numero_correlativo` int(11)
,`area_id` int(11)
,`area_nombre` varchar(100)
,`especialidad_id` int(11)
,`codigo_especialidad` int(11)
,`especialidad_nombre` varchar(100)
,`tipo_id` int(11)
,`tipo_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_documentos_completos`
--
DROP TABLE IF EXISTS `vista_documentos_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dpimeduchile`@`localhost` SQL SECURITY DEFINER VIEW `vista_documentos_completos`  AS SELECT `d`.`id` AS `documento_id`, `d`.`nombre_documento` AS `nombre_documento`, `d`.`nombre_archivo` AS `nombre_archivo`, `d`.`ruta_relativa` AS `ruta_relativa`, `d`.`ruta_web` AS `ruta_web`, `d`.`tamano_kb` AS `tamano_kb`, `d`.`orden` AS `orden`, `a`.`id` AS `area_id`, `a`.`nombre` AS `area_nombre`, `e`.`id` AS `especialidad_id`, `e`.`codigo_especialidad` AS `codigo_especialidad`, `e`.`nombre` AS `especialidad_nombre`, `d`.`activo` AS `activo`, `d`.`created_at` AS `fecha_subida` FROM ((`documentos_estudio` `d` join `areas` `a` on((`d`.`area_id` = `a`.`id`))) join `especialidades` `e` on((`d`.`especialidad_id` = `e`.`id`))) WHERE (`d`.`activo` = 1) ORDER BY `a`.`id` ASC, `e`.`codigo_especialidad` ASC, `d`.`orden` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_resultados_examenes`
--
DROP TABLE IF EXISTS `vista_resultados_examenes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dpimeduchile`@`localhost` SQL SECURITY DEFINER VIEW `vista_resultados_examenes`  AS SELECT `e`.`id` AS `examen_id`, `e`.`codigo_examen` AS `codigo_examen`, `u`.`nombre` AS `usuario_nombre`, `u`.`email` AS `usuario_email`, `e`.`estado` AS `estado`, `e`.`fecha_inicio` AS `fecha_inicio`, `e`.`fecha_finalizacion` AS `fecha_finalizacion`, `e`.`total_preguntas` AS `total_preguntas`, `e`.`respuestas_correctas` AS `respuestas_correctas`, `e`.`respuestas_incorrectas` AS `respuestas_incorrectas`, `e`.`preguntas_omitidas` AS `preguntas_omitidas`, `e`.`puntaje_porcentaje` AS `puntaje_porcentaje`, timestampdiff(MINUTE,`e`.`fecha_inicio`,`e`.`fecha_finalizacion`) AS `duracion_minutos` FROM (`examenes` `e` join `usuarios` `u` on((`e`.`usuario_id` = `u`.`id`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_temas_completos`
--
DROP TABLE IF EXISTS `vista_temas_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dpimeduchile`@`localhost` SQL SECURITY DEFINER VIEW `vista_temas_completos`  AS SELECT `t`.`id` AS `tema_id`, `t`.`codigo_completo` AS `codigo_completo`, `t`.`nombre` AS `tema_nombre`, `t`.`total_preguntas` AS `total_preguntas`, `t`.`numero_correlativo` AS `numero_correlativo`, `a`.`id` AS `area_id`, `a`.`nombre` AS `area_nombre`, `e`.`id` AS `especialidad_id`, `e`.`codigo_especialidad` AS `codigo_especialidad`, `e`.`nombre` AS `especialidad_nombre`, `ts`.`id` AS `tipo_id`, `ts`.`nombre` AS `tipo_nombre` FROM (((`temas` `t` join `areas` `a` on((`t`.`area_id` = `a`.`id`))) join `especialidades` `e` on((`t`.`especialidad_id` = `e`.`id`))) join `tipos_situacion` `ts` on((`t`.`tipo_situacion_id` = `ts`.`id`))) WHERE (`t`.`activo` = 1) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alternativas`
--
ALTER TABLE `alternativas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pregunta_opcion` (`pregunta_id`,`opcion`),
  ADD KEY `idx_pregunta` (`pregunta_id`),
  ADD KEY `idx_correcta` (`es_correcta`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id` (`id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `documentos_estudio`
--
ALTER TABLE `documentos_estudio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_especialidad` (`especialidad_id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_area_codigo` (`area_id`,`codigo_especialidad`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_codigo` (`codigo_especialidad`);

--
-- Indices de la tabla `examenes`
--
ALTER TABLE `examenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_examen` (`codigo_examen`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_codigo` (`codigo_examen`);

--
-- Indices de la tabla `examen_preguntas`
--
ALTER TABLE `examen_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_examen_pregunta` (`examen_id`,`pregunta_id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `idx_examen` (`examen_id`),
  ADD KEY `idx_sesion` (`examen_id`,`sesion`),
  ADD KEY `idx_orden` (`examen_id`,`sesion`,`orden`);

--
-- Indices de la tabla `explicaciones`
--
ALTER TABLE `explicaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pregunta` (`pregunta_id`),
  ADD KEY `idx_pregunta` (`pregunta_id`);

--
-- Indices de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tema_numero` (`tema_id`,`numero_pregunta`),
  ADD KEY `idx_tema` (`tema_id`),
  ADD KEY `idx_activa` (`activa`);

--
-- Indices de la tabla `progreso_estudiante`
--
ALTER TABLE `progreso_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuario_area` (`usuario_id`,`area_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_area` (`area_id`);

--
-- Indices de la tabla `respuestas_usuario`
--
ALTER TABLE `respuestas_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_examen_pregunta` (`examen_id`,`pregunta_id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `idx_examen` (`examen_id`),
  ADD KEY `idx_pregunta` (`examen_id`,`pregunta_id`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_token` (`token`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_activa` (`activa`),
  ADD KEY `idx_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `temas`
--
ALTER TABLE `temas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_completo` (`codigo_completo`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_especialidad` (`especialidad_id`),
  ADD KEY `idx_tipo_situacion` (`tipo_situacion_id`),
  ADD KEY `idx_codigo_completo` (`codigo_completo`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `tipos_situacion`
--
ALTER TABLE `tipos_situacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id` (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_activo` (`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alternativas`
--
ALTER TABLE `alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_estudio`
--
ALTER TABLE `documentos_estudio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `examenes`
--
ALTER TABLE `examenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examen_preguntas`
--
ALTER TABLE `examen_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `explicaciones`
--
ALTER TABLE `explicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `progreso_estudiante`
--
ALTER TABLE `progreso_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas_usuario`
--
ALTER TABLE `respuestas_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas`
--
ALTER TABLE `temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alternativas`
--
ALTER TABLE `alternativas`
  ADD CONSTRAINT `alternativas_ibfk_1` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documentos_estudio`
--
ALTER TABLE `documentos_estudio`
  ADD CONSTRAINT `fk_docs_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD CONSTRAINT `especialidades_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`);

--
-- Filtros para la tabla `examenes`
--
ALTER TABLE `examenes`
  ADD CONSTRAINT `examenes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `examen_preguntas`
--
ALTER TABLE `examen_preguntas`
  ADD CONSTRAINT `examen_preguntas_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examen_preguntas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `explicaciones`
--
ALTER TABLE `explicaciones`
  ADD CONSTRAINT `explicaciones_ibfk_1` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD CONSTRAINT `fk_log_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progreso_estudiante`
--
ALTER TABLE `progreso_estudiante`
  ADD CONSTRAINT `fk_progreso_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progreso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `respuestas_usuario`
--
ALTER TABLE `respuestas_usuario`
  ADD CONSTRAINT `respuestas_usuario_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respuestas_usuario_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `fk_sesiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `temas`
--
ALTER TABLE `temas`
  ADD CONSTRAINT `temas_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `temas_ibfk_2` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`),
  ADD CONSTRAINT `temas_ibfk_3` FOREIGN KEY (`tipo_situacion_id`) REFERENCES `tipos_situacion` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
