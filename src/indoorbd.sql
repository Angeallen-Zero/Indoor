-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-03-2026 a las 03:20:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `indoorbd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control`
--

CREATE TABLE `control` (
  `dispositivo_id` int(11) NOT NULL,
  `umbral_humedad` int(11) DEFAULT 30,
  `ventilacion` tinyint(1) DEFAULT 0,
  `hora_encendido` time DEFAULT '08:00:00',
  `hora_apagado` time DEFAULT '18:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `control`
--

INSERT INTO `control` (`dispositivo_id`, `umbral_humedad`, `ventilacion`, `hora_encendido`, `hora_apagado`) VALUES
(1, 70, 1, '08:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('disponible','asignado') DEFAULT 'disponible',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `numero_serie`, `usuario_id`, `estado`, `fecha_creacion`) VALUES
(1, 'IND-0001', 1, 'asignado', '2026-02-26 01:53:23'),
(2, 'IND-0002', NULL, 'disponible', '2026-02-26 01:53:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_actual`
--

CREATE TABLE `estado_actual` (
  `dispositivo_id` int(11) NOT NULL,
  `bomba` tinyint(1) DEFAULT 0,
  `ventilador` tinyint(1) DEFAULT 0,
  `luces` tinyint(1) DEFAULT 0,
  `surtidor` tinyint(1) DEFAULT 0,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_actual`
--

INSERT INTO `estado_actual` (`dispositivo_id`, `bomba`, `ventilador`, `luces`, `surtidor`, `ultima_actualizacion`) VALUES
(1, 0, 1, 1, 0, '2026-02-26 02:12:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `humedad` decimal(5,2) DEFAULT NULL,
  `nutrientes` decimal(5,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecturas`
--

INSERT INTO `lecturas` (`id`, `dispositivo_id`, `humedad`, `nutrientes`, `fecha`) VALUES
(1, 1, 74.00, 90.00, '2026-02-26 02:18:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','cliente') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`) VALUES
(1, 'Angel Ramos', 'angel@email.com', '7ae36b932e31b0953c520f3f74686d8eb04cc0782a115be7d4ec3d245d2d3098', 'admin'),
(2, 'Pepe Fraid soleno', 'elpepe@email.com', '144418b44a0d993dce159bbe29eb34350ebc4c5b5a4d4b659689c8b02fd896e2', 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `control`
--
ALTER TABLE `control`
  ADD PRIMARY KEY (`dispositivo_id`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_serie` (`numero_serie`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `estado_actual`
--
ALTER TABLE `estado_actual`
  ADD PRIMARY KEY (`dispositivo_id`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispositivo_id` (`dispositivo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `control`
--
ALTER TABLE `control`
  ADD CONSTRAINT `control_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `dispositivos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `estado_actual`
--
ALTER TABLE `estado_actual`
  ADD CONSTRAINT `estado_actual_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `lecturas_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
