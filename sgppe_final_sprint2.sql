-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 18-06-2026 a las 19:21:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sgppe`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_usuario`, `nombre`, `apellido`) VALUES
(3, 'Pedro', 'Gómez'),
(8, 'sebastian', 'vargas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `id_asignacion` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_coordinador` int(11) NOT NULL,
  `id_directivo` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_practica` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horas_realizadas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `modulo` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id_auditoria`, `usuario`, `accion`, `modulo`, `ip`, `detalle`, `fecha`) VALUES
(1, 'admin@ucsc.cl', 'Creacion de usuario', 'usuarios', '::1', 'ID usuario: 12 | Rol: Estudiante | Institucion: 5', '2026-06-18 14:51:37'),
(2, 'admin@ucsc.cl', 'Cambio de estado de usuario', 'usuarios', '::1', 'ID usuario: 5 | Estado: inactiva', '2026-06-18 15:18:35'),
(3, 'admin@ucsc.cl', 'Cambio de estado de usuario', 'usuarios', '::1', 'ID usuario: 5 | Estado: activa', '2026-06-18 15:31:10'),
(4, 'admin@ucsc.cl', 'Cambio de estado de usuario', 'usuarios', '::1', 'ID usuario: 5 | Estado: inactiva', '2026-06-18 15:33:55'),
(5, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: inactivo', '2026-06-18 15:34:03'),
(6, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: activo', '2026-06-18 15:34:05'),
(7, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: inactivo', '2026-06-18 15:34:06'),
(8, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: activo', '2026-06-18 15:34:06'),
(9, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: inactivo', '2026-06-18 15:34:06'),
(10, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: activo', '2026-06-18 15:34:06'),
(11, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: inactivo', '2026-06-18 15:34:06'),
(12, 'administrador', 'Cambio de estado de rol', 'roles', '::1', 'ID rol: 1 | Estado: activo', '2026-06-18 15:34:07'),
(13, 'admin@ucsc.cl', 'Creacion de usuario', 'usuarios', '::1', 'ID usuario: 13 | Rol: Coordinador | Institucion: 5', '2026-06-18 15:37:12'),
(14, 'administrador', 'Creacion de rol', 'roles', '::1', 'Rol: Directivo | Estado: activo', '2026-06-18 15:40:00'),
(15, 'admin@ucsc.cl', 'Creacion de usuario', 'usuarios', '::1', 'ID usuario: 14 | Rol: Directivo | Institucion: 5', '2026-06-18 15:40:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `id_practica` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `actividades` text NOT NULL,
  `logros` text DEFAULT NULL,
  `horas_registradas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `id_practica`, `fecha_registro`, `actividades`, `logros`, `horas_registradas`) VALUES
(6, 1, '2026-01-02', 'actividades_test', 'logros_test', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera`
--

CREATE TABLE `carrera` (
  `id_carrera` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `nombre_carrera` varchar(255) NOT NULL,
  `codigo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrera`
--

INSERT INTO `carrera` (`id_carrera`, `id_institucion`, `nombre_carrera`, `codigo`) VALUES
(1, 5, 'Ingeniería Civil Informática', 'INF-01'),
(2, 5, 'Ingeniería Comercial', 'COM-01'),
(3, 5, 'Psicología', 'PSI-01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencias`
--

CREATE TABLE `competencias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `tipo` enum('técnica','blanda') DEFAULT 'técnica'
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `competencias`
--

INSERT INTO `competencias` (`id`, `nombre`, `id_carrera`, `tipo`) VALUES
(1, 'PHP', 1, 'técnica'),
(2, 'LARAVEL', 1, 'técnica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`, `descripcion`, `actualizado_en`) VALUES
('correo_soporte', 'soporte@sgppe.cl', 'Correo de soporte del sistema', '2026-06-18 14:39:07'),
('estado_sistema', 'activo', 'Estado general del sistema', '2026-06-18 14:39:07'),
('tiempo_sesion', '30', 'Tiempo de sesion en minutos', '2026-06-18 14:39:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordinador`
--

CREATE TABLE `coordinador` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coordinador`
--

INSERT INTO `coordinador` (`id_usuario`, `id_carrera`, `nombre`, `apellido`) VALUES
(13, 1, 'coordinador', 'coordinador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directivo`
--

CREATE TABLE `directivo` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `directivo`
--

INSERT INTO `directivo` (`id_usuario`, `id_carrera`, `nombre`, `apellido`) VALUES
(14, 1, 'sebastian', 'vargas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_usuario` int(11) NOT NULL,
  `razon_social` varchar(255) NOT NULL,
  `rut_empresa` varchar(12) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `nombre_encargado` varchar(100) DEFAULT NULL,
  `nombre_empresa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_usuario`, `razon_social`, `rut_empresa`, `direccion`, `telefono`, `nombre_encargado`, `nombre_empresa`) VALUES
(5, 'Tech Solutions SPA', '76.123.456-7', 'Concepción', '412345678', 'Carlos Pérez', 'Tech Solutions'),
(15, 'empresa spa', '76.115.412.5', 'lincoyan 940', '+56 9 7107 1530', 'Juan Perez', 'empresa spa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nivel_curricular` int(11) NOT NULL COMMENT 'Semestre o nivel de avance',
  `habilidades` text DEFAULT NULL COMMENT 'Habilidades declaradas para matching',
  `ramos_aprobados` int(11) NOT NULL COMMENT 'Ramos aprobados, validación BR-04'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`id_usuario`, `id_carrera`, `nombre`, `apellido`, `nivel_curricular`, `habilidades`, `ramos_aprobados`) VALUES
(3, 1, 'Jeremy', 'Mendoza', 8, 'HTML, CSS, PHP', 35),
(12, 1, 'sebastian', 'vargas', 1, 'LARAVEL', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante_competencias`
--

CREATE TABLE `estudiante_competencias` (
  `id_estudiante` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `estudiante_competencias`
--

INSERT INTO `estudiante_competencias` (`id_estudiante`, `id_competencia`) VALUES
(12, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `id_evaluacion` int(11) NOT NULL,
  `id_practica` int(11) NOT NULL,
  `id_bitacora` int(11) NOT NULL,
  `nota_final` decimal(4,1) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_evaluacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion_empresa`
--

CREATE TABLE `evaluacion_empresa` (
  `id_evaluacion_empresa` int(11) NOT NULL,
  `id_practica` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `nota_final` decimal(4,1) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_evaluacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL COMMENT 'Identificador de la institucion',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la institución.',
  `logo_institucion` varchar(500) DEFAULT NULL COMMENT 'Logo asociado a la institución participante.',
  `estado_institucion` enum('activa','inactiva') NOT NULL DEFAULT 'activa' COMMENT 'Estado de la institución en el sistema.',
  `id_administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Institucion participante del sistema de gestion de prácticas';

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `nombre`, `logo_institucion`, `estado_institucion`, `id_administrador`) VALUES
(5, 'Universidad católica de la santísima Concepción', 'https://upload.wikimedia.org/wikipedia/commons/7/7a/UCSC%2C_Universidad_Cat%C3%B3lica_de_la_Sant%C3%ADsima_Concepci%C3%B3n.png', 'activa', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_envio`
--

CREATE TABLE `intentos_envio` (
  `id` int(11) NOT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `intentos_envio`
--

INSERT INTO `intentos_envio` (`id`, `ip_usuario`, `fecha_intento`) VALUES
(26, '::1', '2026-06-18 15:22:43'),
(27, '::1', '2026-06-18 15:28:08'),
(28, '::1', '2026-06-18 15:50:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_evento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_competencias`
--

CREATE TABLE `oferta_competencias` (
  `id_oferta` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `oferta_competencias`
--

INSERT INTO `oferta_competencias` (`id_oferta`, `id_competencia`) VALUES
(3, 1),
(3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta_practica`
--

CREATE TABLE `oferta_practica` (
  `id_oferta` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `requisitos` text NOT NULL,
  `cupos` int(11) NOT NULL,
  `duracion_meses` int(11) NOT NULL,
  `estado_oferta` enum('pendiente_aprobacion','activa','rechazada','pausada','cerrada') NOT NULL DEFAULT 'pendiente_aprobacion',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oferta_practica`
--

INSERT INTO `oferta_practica` (`id_oferta`, `id_carrera`, `id_empresa`, `titulo`, `descripcion`, `requisitos`, `cupos`, `duracion_meses`, `estado_oferta`, `fecha_publicacion`, `fecha_cierre`) VALUES
(2, 1, 5, 'Desarrollador Web Junior', 'Apoyo en desarrollo web', 'PHP, HTML, CSS y MySQL', 3, 6, 'rechazada', '2026-06-16 21:40:34', NULL),
(3, 1, 15, '24234sd', 'asdikjasdunbhjasnubh', 'Ver etiquetas de competencias', 0, 3, 'cerrada', '2026-06-18 15:50:13', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulacion`
--

CREATE TABLE `postulacion` (
  `id_postulacion` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL,
  `fecha_postulacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_postulacion` enum('espera','aceptada','rechazada') NOT NULL DEFAULT 'espera',
  `cv_estudiante` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulacion`
--

INSERT INTO `postulacion` (`id_postulacion`, `id_estudiante`, `id_oferta`, `fecha_postulacion`, `estado_postulacion`, `cv_estudiante`) VALUES
(3, 3, 2, '2026-06-17 07:10:35', 'espera', 'cv.pdf'),
(6, 12, 3, '2026-06-18 15:57:33', 'espera', 'cv.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `practica`
--

CREATE TABLE `practica` (
  `id_practica` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL,
  `id_coordinador` int(11) DEFAULT NULL,
  `id_directivo` int(11) DEFAULT NULL,
  `estado_practica` enum('postulado','asignado','en_curso','informe_entregado','evaluado','finalizado','cancelada') NOT NULL DEFAULT 'postulado',
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date DEFAULT NULL,
  `horas_totales` int(11) DEFAULT NULL,
  `nota_final` decimal(4,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `practica`
--

INSERT INTO `practica` (`id_practica`, `id_estudiante`, `id_oferta`, `id_coordinador`, `id_directivo`, `estado_practica`, `fecha_inicio`, `fecha_termino`, `horas_totales`, `nota_final`) VALUES
(1, 3, 2, NULL, NULL, 'postulado', '2026-06-17', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL COMMENT 'Identificador único del rol asignado al usuario',
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción asociado al rol.',
  `nombre_rol` varchar(50) NOT NULL COMMENT 'Nombre del rol.',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'Estado del rol'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rol asociado al usuario pensado como mantenedor';

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `descripcion`, `nombre_rol`, `estado`) VALUES
(1, 'Administrador de la institución', 'Administrador', 'activo'),
(2, 'Coordinador de prácticas', 'Coordinador', 'activo'),
(3, 'Empresa externa', 'Empresa', 'activo'),
(4, 'Alumno en práctica', 'Estudiante', 'activo'),
(5, 'permite ver todo el dashboard', 'Directivo', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `superadministrador`
--

CREATE TABLE `superadministrador` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `rut` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_institucion` int(11) DEFAULT NULL,
  `rut` varchar(12) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `estado_cuenta` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_rol`, `id_institucion`, `rut`, `correo`, `contrasena_hash`, `estado_cuenta`, `ultimo_acceso`, `fecha_creacion`) VALUES
(3, 1, 5, '16.222.222-1', 'pgomez@admin.com', '$2y$10$ZhHD/FG4phU3bdjdliUqx.UlAV2zwwGY68SEqWyHdzzG6gU2hVety', 'activa', '2026-06-16 01:25:36', '2026-06-16 01:24:41'),
(5, 3, 5, '11.111.111-1', 'empresa@test.cl', '123456', 'inactiva', '2026-06-18 15:33:55', '2026-06-16 21:30:09'),
(8, 1, 5, '191643690', 'admin@ucsc.cl', '$2y$10$9qYjlfySb9LQaWl0aNTymO96bR.HAdK7w7N5D9pZkOJHBC90ehE.W', 'activa', '2026-06-18 14:46:36', '2026-06-18 14:46:36'),
(12, 4, 5, '211428058', 'svargasn@ing.ucsc.cl', '$2y$10$ctsO13cKyHL8PhI3QL/UuuSiPUFZVT5YupU55kBwUgVtS4aHR9ZtS', 'activa', '2026-06-18 14:51:37', '2026-06-18 14:51:37'),
(13, 2, 5, '97034168', 'coord@coord.cl', '$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO', 'activa', '2026-06-18 15:37:12', '2026-06-18 15:37:12'),
(14, 5, 5, '55782520', 'directivo@directivo.cl', '$2y$10$2pNVV99bynwzOj2i455mLu1xCdkgooxMxJZw5CTHJXdTKfXJr29am', 'activa', '2026-06-18 15:40:28', '2026-06-18 15:40:28'),
(15, 3, 5, '76.115.412.5', 'aq@aasd.cl', '$2y$10$lvwTP5XH2z5QnCflTRrFpeqibhNLOPYyK7x7Zg92fUNDyqHJgV4Fm', 'activa', '2026-06-18 15:50:13', '2026-06-18 15:50:13');

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `trg_usuario_bi_institucion` BEFORE INSERT ON `usuario` FOR EACH ROW BEGIN
         DECLARE rol_admin INT;
        SELECT id_rol INTO rol_admin FROM rol WHERE nombre_rol = 'Administrador' LIMIT 1;
       IF NEW.id_rol <> rol_admin AND NEW.id_institucion IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'id_institucion no puede ser NULL para este rol';
        END IF;
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_usuario_bu_institucion` BEFORE UPDATE ON `usuario` FOR EACH ROW BEGIN
       DECLARE rol_admin INT;
        SELECT id_rol INTO rol_admin FROM rol WHERE nombre_rol = 'Administrador' LIMIT 1;
        IF NEW.id_rol <> rol_admin AND NEW.id_institucion IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'id_institucion no puede ser NULL para este rol';
        END IF;
    END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `idx_asignacion_estudiante` (`id_estudiante`),
  ADD KEY `idx_asignacion_coordinador` (`id_coordinador`),
  ADD KEY `idx_asignacion_directivo` (`id_directivo`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_practica` (`id_practica`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_auditoria_usuario` (`usuario`),
  ADD KEY `idx_auditoria_modulo` (`modulo`),
  ADD KEY `idx_auditoria_fecha` (`fecha`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `id_practica` (`id_practica`);

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`id_carrera`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- Indices de la tabla `competencias`
--
ALTER TABLE `competencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `coordinador`
--
ALTER TABLE `coordinador`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `directivo`
--
ALTER TABLE `directivo`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `rut_empresa` (`rut_empresa`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `estudiante_competencias`
--
ALTER TABLE `estudiante_competencias`
  ADD PRIMARY KEY (`id_estudiante`,`id_competencia`),
  ADD KEY `id_competencia` (`id_competencia`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD KEY `id_practica` (`id_practica`),
  ADD KEY `id_bitacora` (`id_bitacora`);

--
-- Indices de la tabla `evaluacion_empresa`
--
ALTER TABLE `evaluacion_empresa`
  ADD PRIMARY KEY (`id_evaluacion_empresa`),
  ADD KEY `id_practica` (`id_practica`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id_institucion`),
  ADD UNIQUE KEY `Institucion_unique` (`nombre`),
  ADD KEY `fk_institucion_administrador` (`id_administrador`);

--
-- Indices de la tabla `intentos_envio`
--
ALTER TABLE `intentos_envio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `oferta_competencias`
--
ALTER TABLE `oferta_competencias`
  ADD PRIMARY KEY (`id_oferta`,`id_competencia`),
  ADD KEY `id_competencia` (`id_competencia`);

--
-- Indices de la tabla `oferta_practica`
--
ALTER TABLE `oferta_practica`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `id_carrera` (`id_carrera`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD PRIMARY KEY (`id_postulacion`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_oferta` (`id_oferta`);

--
-- Indices de la tabla `practica`
--
ALTER TABLE `practica`
  ADD PRIMARY KEY (`id_practica`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_oferta` (`id_oferta`),
  ADD KEY `id_coordinador` (`id_coordinador`),
  ADD KEY `id_directivo` (`id_directivo`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `Rol_unique` (`nombre_rol`);

--
-- Indices de la tabla `superadministrador`
--
ALTER TABLE `superadministrador`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `rut_unique` (`rut`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `rut` (`rut`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_institucion` (`id_institucion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `carrera`
--
ALTER TABLE `carrera`
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `competencias`
--
ALTER TABLE `competencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evaluacion_empresa`
--
ALTER TABLE `evaluacion_empresa`
  MODIFY `id_evaluacion_empresa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la institucion', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `intentos_envio`
--
ALTER TABLE `intentos_envio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `oferta_practica`
--
ALTER TABLE `oferta_practica`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  MODIFY `id_postulacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `practica`
--
ALTER TABLE `practica`
  MODIFY `id_practica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del rol asignado al usuario', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`id_coordinador`) REFERENCES `coordinador` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_ibfk_3` FOREIGN KEY (`id_directivo`) REFERENCES `directivo` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`);

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`);

--
-- Filtros para la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD CONSTRAINT `carrera_ibfk_1` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`);

--
-- Filtros para la tabla `competencias`
--
ALTER TABLE `competencias`
  ADD CONSTRAINT `competencias_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`) ON DELETE CASCADE;

--
-- Filtros para la tabla `coordinador`
--
ALTER TABLE `coordinador`
  ADD CONSTRAINT `coordinador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `coordinador_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`);

--
-- Filtros para la tabla `directivo`
--
ALTER TABLE `directivo`
  ADD CONSTRAINT `directivo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `directivo_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`);

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `estudiante_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`);

--
-- Filtros para la tabla `estudiante_competencias`
--
ALTER TABLE `estudiante_competencias`
  ADD CONSTRAINT `estudiante_competencias_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `estudiante_competencias_ibfk_2` FOREIGN KEY (`id_competencia`) REFERENCES `competencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `evaluacion_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`),
  ADD CONSTRAINT `evaluacion_ibfk_2` FOREIGN KEY (`id_bitacora`) REFERENCES `bitacora` (`id_bitacora`);

--
-- Filtros para la tabla `evaluacion_empresa`
--
ALTER TABLE `evaluacion_empresa`
  ADD CONSTRAINT `evaluacion_empresa_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`),
  ADD CONSTRAINT `evaluacion_empresa_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`);

--
-- Filtros para la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD CONSTRAINT `fk_institucion_administrador` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `oferta_competencias`
--
ALTER TABLE `oferta_competencias`
  ADD CONSTRAINT `oferta_competencias_ibfk_1` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`) ON DELETE CASCADE,
  ADD CONSTRAINT `oferta_competencias_ibfk_2` FOREIGN KEY (`id_competencia`) REFERENCES `competencias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `oferta_practica`
--
ALTER TABLE `oferta_practica`
  ADD CONSTRAINT `oferta_practica_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`),
  ADD CONSTRAINT `oferta_practica_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_usuario`);

--
-- Filtros para la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD CONSTRAINT `postulacion_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`),
  ADD CONSTRAINT `postulacion_ibfk_2` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`);

--
-- Filtros para la tabla `practica`
--
ALTER TABLE `practica`
  ADD CONSTRAINT `practica_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`),
  ADD CONSTRAINT `practica_ibfk_2` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`),
  ADD CONSTRAINT `practica_ibfk_3` FOREIGN KEY (`id_coordinador`) REFERENCES `coordinador` (`id_usuario`),
  ADD CONSTRAINT `practica_ibfk_4` FOREIGN KEY (`id_directivo`) REFERENCES `directivo` (`id_usuario`);

--
-- Filtros para la tabla `superadministrador`
--
ALTER TABLE `superadministrador`
  ADD CONSTRAINT `superadministrador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
