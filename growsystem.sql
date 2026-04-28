-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-04-2026 a las 15:31:30
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
-- Base de datos: `growsystem`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_uso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `dispositivo_id`, `token`, `activo`, `creado_en`, `ultimo_uso`) VALUES
(1, 1, 'a3f8c2d1e4b7091f6a5d3c2e1b0f9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b3a2', 1, '2026-03-22 00:19:19', '2026-03-22 04:14:24'),
(4, 3, '24b873d69638c9bac529d4e3d6ded5dd8c8f47ce5072bafed00b897019e0c8a8', 1, '2026-03-22 04:13:30', '2026-03-22 23:15:01'),
(7, 4, '1e8d6367bdb19df76cb05c9ba41d883835b3249fa8f18dcf3a6a0685605c605f', 1, '2026-03-22 06:48:22', NULL),
(9, 6, 'bffa2edbbec2e964d0c1fc5638375a545508d1e2d5a80e044f0ae77416f1c8b9', 1, '2026-03-22 07:10:13', NULL),
(11, 5, 'd442036fa67ebf5fdc231f0fdd5e42e19566972137e24f22a8e39309db7907e8', 1, '2026-03-22 07:10:33', NULL),
(18, 2, '35e0eabfa81e48ae65cb853c712ce4f64fd05a50efe675ddf19383af67fc8303', 1, '2026-03-22 17:13:02', NULL),
(27, 7, '70f9288557bf7e74982bbcd2e28c796a062ca1ec676c68a23ab62583c2e4c3a8', 1, '2026-03-22 17:29:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control`
--

CREATE TABLE `control` (
  `instancia_id` int(11) NOT NULL,
  `umbral_humedad` int(11) DEFAULT 30,
  `umbral_nutrientes` int(11) DEFAULT 40,
  `ventilacion` tinyint(1) DEFAULT 0,
  `hora_encendido` time DEFAULT '08:00:00',
  `hora_apagado` time DEFAULT '18:00:00',
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `control`
--

INSERT INTO `control` (`instancia_id`, `umbral_humedad`, `umbral_nutrientes`, `ventilacion`, `hora_encendido`, `hora_apagado`, `ultima_actualizacion`) VALUES
(1, 56, 53, 0, '08:00:00', '18:00:00', '2026-03-22 04:09:00'),
(22, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-21 18:17:32'),
(23, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-21 19:27:32'),
(24, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-21 19:27:47'),
(25, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-21 23:08:56'),
(26, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-21 23:31:34'),
(27, 30, 40, 0, '08:00:00', '18:00:00', '2026-04-22 05:29:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio`) VALUES
(1, 1, 2, 1, 4999.00),
(2, 2, 3, 1, 8999.00),
(3, 2, 2, 1, 4999.00),
(4, 3, 3, 1, 8999.00),
(5, 4, 3, 1, 8999.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('disponible','asignado','baja') DEFAULT 'disponible',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_asignacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `numero_serie`, `usuario_id`, `estado`, `fecha_creacion`, `fecha_asignacion`) VALUES
(1, 'IND-0001', 2, 'asignado', '2026-03-22 00:19:19', '2026-03-22 00:19:19'),
(2, 'IND-0002', 7, 'asignado', '2026-03-22 00:19:19', '2026-04-21 18:17:32'),
(3, 'IND-0003', 13, 'asignado', '2026-03-22 00:19:19', '2026-04-22 05:29:28'),
(4, 'IND-0004', 9, 'asignado', '2026-03-22 00:19:19', '2026-04-21 19:27:32'),
(5, 'IND-0005', 9, 'asignado', '2026-03-22 00:19:19', '2026-04-21 19:27:47'),
(6, 'IND-0006', 11, 'asignado', '2026-03-22 07:10:13', '2026-04-21 23:08:56'),
(7, 'IND-0007', 7, 'asignado', '2026-03-22 17:29:33', '2026-04-21 23:31:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_actual`
--

CREATE TABLE `estado_actual` (
  `instancia_id` int(11) NOT NULL,
  `bomba` tinyint(1) DEFAULT 0,
  `ventilador` tinyint(1) DEFAULT 0,
  `luces` tinyint(1) DEFAULT 0,
  `surtidor` tinyint(1) DEFAULT 0,
  `humedad` decimal(5,2) DEFAULT NULL,
  `nutrientes` decimal(5,2) DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_actual`
--

INSERT INTO `estado_actual` (`instancia_id`, `bomba`, `ventilador`, `luces`, `surtidor`, `humedad`, `nutrientes`, `ultima_actualizacion`) VALUES
(1, 0, 0, 0, 0, 100.00, 54.00, '2026-03-22 04:14:24'),
(22, 0, 0, 0, 0, NULL, NULL, '2026-04-21 18:17:32'),
(23, 0, 0, 0, 0, NULL, NULL, '2026-04-21 19:27:32'),
(24, 0, 0, 0, 0, NULL, NULL, '2026-04-21 19:27:47'),
(25, 0, 0, 0, 0, NULL, NULL, '2026-04-21 23:08:56'),
(26, 0, 0, 0, 0, NULL, NULL, '2026-04-21 23:31:34'),
(27, 0, 0, 0, 0, NULL, NULL, '2026-04-22 05:29:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `comentario` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `usuario_id`, `fecha`, `comentario`, `imagen`) VALUES
(1, 2, '2026-03-23', 'prueba pepe1', '1774305101_flor1.jpg'),
(2, 3, '2026-03-23', 'prueba jose', '1774305197_flor 2.png'),
(3, 3, '2026-03-23', 'hola miguel', '1774305330_flor 3.webp'),
(4, 7, '2026-04-05', 'hola', ''),
(5, 7, '2026-04-11', 'saddsa', '1775449287_girasols.jpg'),
(6, 7, '2026-04-12', 'sdas', '1775449618_images.jpg'),
(7, 7, '2026-04-17', 'dadsasd', '1775449781_girasols.jpg'),
(8, 7, '2026-04-19', 'fswdffds', '1775449949_fondos.jpg'),
(9, 7, '2026-04-12', '', '1775450843_images.jpg'),
(10, 7, '2026-05-22', '', '1775451036_girasols.jpg'),
(11, 7, '2026-04-11', 'hoy se hizo riego', ''),
(12, 7, '2026-04-25', 'nose', ''),
(13, 7, '2026-04-26', '', '1776799524_spider-man-el-hombre-arana-marvels_5120x2880_xtrafondos.com.jpg'),
(14, 7, '2026-04-26', 'momom', ''),
(15, 11, '2026-04-30', 'Hola', ''),
(16, 7, '2026-04-17', '', '1776813217_girasols.jpg'),
(17, 12, '2026-04-30', 'Nose', '1776813655_images (12).jpeg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instancias`
--

CREATE TABLE `instancias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `planta_id` int(11) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instancias`
--

INSERT INTO `instancias` (`id`, `usuario_id`, `dispositivo_id`, `planta_id`, `alias`, `ubicacion`, `activa`, `fecha_creacion`) VALUES
(1, 2, 1, 1, 'Manzanilla Terraza', 'Terraza norte', 1, '2026-03-22 00:19:19'),
(22, 7, 2, 51, 'Girasol', NULL, 1, '2026-04-21 18:17:32'),
(23, 9, 4, 52, 'nose', NULL, 1, '2026-04-21 19:27:32'),
(24, 9, 5, 53, 'Hibisco', NULL, 1, '2026-04-21 19:27:47'),
(25, 11, 6, 54, 'NshslB', NULL, 1, '2026-04-21 23:08:56'),
(26, 7, 7, 55, 'Coleo', NULL, 1, '2026-04-21 23:31:34'),
(27, 13, 3, 57, 'dsadsa', NULL, 1, '2026-04-22 05:29:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id` int(11) NOT NULL,
  `instancia_id` int(11) NOT NULL,
  `humedad` decimal(5,2) DEFAULT NULL,
  `nutrientes` decimal(5,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecturas`
--

INSERT INTO `lecturas` (`id`, `instancia_id`, `humedad`, `nutrientes`, `fecha`) VALUES
(1, 1, 74.00, 90.00, '2026-03-17 16:51:43'),
(2, 1, 71.50, 87.00, '2026-03-17 16:52:00'),
(3, 1, 68.00, 85.50, '2026-03-17 16:52:30'),
(4, 1, 100.00, 77.00, '2026-03-22 00:43:09'),
(5, 1, 100.00, 75.00, '2026-03-22 00:43:19'),
(6, 1, 100.00, 73.00, '2026-03-22 00:43:29'),
(7, 1, 100.00, 70.00, '2026-03-22 00:43:39'),
(8, 1, 100.00, 67.00, '2026-03-22 00:43:49'),
(9, 1, 100.00, 65.00, '2026-03-22 00:43:59'),
(10, 1, 100.00, 62.00, '2026-03-22 00:44:09'),
(11, 1, 100.00, 60.00, '2026-03-22 00:44:19'),
(12, 1, 100.00, 59.00, '2026-03-22 00:44:29'),
(13, 1, 100.00, 65.00, '2026-03-22 00:44:39'),
(14, 1, 100.00, 70.00, '2026-03-22 00:44:49'),
(15, 1, 100.00, 75.00, '2026-03-22 00:44:59'),
(16, 1, 100.00, 81.00, '2026-03-22 00:45:09'),
(17, 1, 100.00, 87.00, '2026-03-22 00:45:19'),
(18, 1, 100.00, 89.00, '2026-03-22 00:45:29'),
(19, 1, 100.00, 95.00, '2026-03-22 00:45:39'),
(20, 1, 100.00, 100.00, '2026-03-22 00:45:49'),
(21, 1, 100.00, 98.00, '2026-03-22 00:45:59'),
(22, 1, 100.00, 100.00, '2026-03-22 00:46:09'),
(23, 1, 100.00, 97.00, '2026-03-22 00:46:19'),
(24, 1, 100.00, 100.00, '2026-03-22 00:46:29'),
(25, 1, 100.00, 97.00, '2026-03-22 00:46:39'),
(26, 1, 100.00, 100.00, '2026-03-22 00:46:49'),
(27, 1, 100.00, 98.00, '2026-03-22 00:46:59'),
(28, 1, 100.00, 100.00, '2026-03-22 00:47:09'),
(29, 1, 100.00, 98.00, '2026-03-22 00:47:19'),
(30, 1, 100.00, 100.00, '2026-03-22 00:47:29'),
(31, 1, 100.00, 98.00, '2026-03-22 00:47:39'),
(32, 1, 100.00, 100.00, '2026-03-22 00:47:49'),
(33, 1, 100.00, 99.00, '2026-03-22 00:47:59'),
(34, 1, 100.00, 100.00, '2026-03-22 00:48:09'),
(35, 1, 100.00, 98.00, '2026-03-22 00:48:19'),
(36, 1, 100.00, 100.00, '2026-03-22 00:48:29'),
(37, 1, 100.00, 99.00, '2026-03-22 00:48:39'),
(38, 1, 100.00, 100.00, '2026-03-22 00:48:49'),
(39, 1, 100.00, 98.00, '2026-03-22 00:48:59'),
(40, 1, 100.00, 100.00, '2026-03-22 00:49:09'),
(41, 1, 100.00, 99.00, '2026-03-22 00:49:19'),
(42, 1, 100.00, 100.00, '2026-03-22 00:49:29'),
(43, 1, 100.00, 97.00, '2026-03-22 00:49:39'),
(44, 1, 100.00, 100.00, '2026-03-22 00:49:49'),
(45, 1, 100.00, 98.00, '2026-03-22 00:49:59'),
(46, 1, 100.00, 100.00, '2026-03-22 00:50:09'),
(47, 1, 100.00, 99.00, '2026-03-22 00:50:19'),
(48, 1, 100.00, 100.00, '2026-03-22 00:50:29'),
(49, 1, 100.00, 99.00, '2026-03-22 00:50:39'),
(50, 1, 100.00, 100.00, '2026-03-22 00:50:49'),
(51, 1, 100.00, 98.00, '2026-03-22 00:50:59'),
(52, 1, 100.00, 100.00, '2026-03-22 00:51:09'),
(53, 1, 100.00, 98.00, '2026-03-22 00:51:19'),
(54, 1, 100.00, 100.00, '2026-03-22 00:51:29'),
(55, 1, 100.00, 99.00, '2026-03-22 00:51:39'),
(56, 1, 100.00, 100.00, '2026-03-22 00:51:49'),
(57, 1, 100.00, 99.00, '2026-03-22 00:51:59'),
(58, 1, 100.00, 100.00, '2026-03-22 00:52:09'),
(59, 1, 100.00, 99.00, '2026-03-22 00:52:19'),
(60, 1, 100.00, 100.00, '2026-03-22 00:52:29'),
(61, 1, 100.00, 98.00, '2026-03-22 00:52:39'),
(62, 1, 100.00, 100.00, '2026-03-22 00:52:49'),
(63, 1, 100.00, 99.00, '2026-03-22 00:52:59'),
(64, 1, 100.00, 100.00, '2026-03-22 00:53:09'),
(65, 1, 100.00, 99.00, '2026-03-22 00:53:19'),
(66, 1, 100.00, 100.00, '2026-03-22 00:53:29'),
(67, 1, 100.00, 97.00, '2026-03-22 00:53:40'),
(68, 1, 100.00, 100.00, '2026-03-22 00:53:49'),
(69, 1, 100.00, 99.00, '2026-03-22 00:53:59'),
(70, 1, 100.00, 100.00, '2026-03-22 00:54:09'),
(71, 1, 100.00, 98.00, '2026-03-22 00:54:19'),
(72, 1, 100.00, 100.00, '2026-03-22 00:54:29'),
(73, 1, 100.00, 99.00, '2026-03-22 00:54:39'),
(74, 1, 100.00, 100.00, '2026-03-22 00:54:49'),
(75, 1, 100.00, 97.00, '2026-03-22 00:54:59'),
(76, 1, 100.00, 100.00, '2026-03-22 00:55:09'),
(77, 1, 100.00, 98.00, '2026-03-22 00:55:19'),
(78, 1, 100.00, 100.00, '2026-03-22 00:55:29'),
(79, 1, 100.00, 99.00, '2026-03-22 00:55:39'),
(80, 1, 100.00, 100.00, '2026-03-22 00:55:49'),
(81, 1, 100.00, 98.00, '2026-03-22 00:55:59'),
(82, 1, 100.00, 100.00, '2026-03-22 00:56:09'),
(83, 1, 100.00, 99.00, '2026-03-22 00:56:19'),
(84, 1, 100.00, 100.00, '2026-03-22 00:56:29'),
(85, 1, 100.00, 97.00, '2026-03-22 00:56:39'),
(86, 1, 100.00, 100.00, '2026-03-22 00:56:49'),
(87, 1, 100.00, 98.00, '2026-03-22 00:56:59'),
(88, 1, 100.00, 100.00, '2026-03-22 00:57:09'),
(89, 1, 100.00, 98.00, '2026-03-22 00:57:19'),
(90, 1, 100.00, 100.00, '2026-03-22 00:57:29'),
(91, 1, 100.00, 99.00, '2026-03-22 00:57:39'),
(92, 1, 100.00, 100.00, '2026-03-22 00:57:49'),
(93, 1, 100.00, 99.00, '2026-03-22 00:57:59'),
(94, 1, 100.00, 100.00, '2026-03-22 00:58:09'),
(95, 1, 100.00, 98.00, '2026-03-22 00:58:19'),
(96, 1, 100.00, 100.00, '2026-03-22 00:58:29'),
(97, 1, 100.00, 97.00, '2026-03-22 00:58:39'),
(98, 1, 100.00, 100.00, '2026-03-22 00:58:49'),
(99, 1, 100.00, 97.00, '2026-03-22 00:59:00'),
(100, 1, 100.00, 100.00, '2026-03-22 00:59:09'),
(101, 1, 100.00, 99.00, '2026-03-22 00:59:19'),
(102, 1, 100.00, 100.00, '2026-03-22 00:59:29'),
(103, 1, 100.00, 98.00, '2026-03-22 00:59:39'),
(104, 1, 100.00, 100.00, '2026-03-22 00:59:49'),
(105, 1, 100.00, 98.00, '2026-03-22 00:59:59'),
(106, 1, 100.00, 100.00, '2026-03-22 01:00:10'),
(107, 1, 100.00, 97.00, '2026-03-22 01:00:19'),
(108, 1, 100.00, 100.00, '2026-03-22 01:00:29'),
(109, 1, 100.00, 97.00, '2026-03-22 01:00:39'),
(110, 1, 100.00, 99.00, '2026-03-22 01:03:13'),
(111, 1, 100.00, 100.00, '2026-03-22 01:03:19'),
(112, 1, 100.00, 99.00, '2026-03-22 01:03:29'),
(113, 1, 100.00, 100.00, '2026-03-22 01:03:39'),
(114, 1, 100.00, 98.00, '2026-03-22 01:03:49'),
(115, 1, 100.00, 100.00, '2026-03-22 01:03:59'),
(116, 1, 100.00, 97.00, '2026-03-22 01:04:09'),
(117, 1, 100.00, 100.00, '2026-03-22 01:04:19'),
(118, 1, 100.00, 98.00, '2026-03-22 01:04:29'),
(119, 1, 100.00, 100.00, '2026-03-22 01:04:39'),
(120, 1, 100.00, 98.00, '2026-03-22 01:04:49'),
(121, 1, 100.00, 100.00, '2026-03-22 01:04:59'),
(122, 1, 100.00, 98.00, '2026-03-22 01:05:09'),
(123, 1, 100.00, 100.00, '2026-03-22 01:05:19'),
(124, 1, 100.00, 99.00, '2026-03-22 01:05:29'),
(125, 1, 100.00, 100.00, '2026-03-22 01:05:39'),
(126, 1, 100.00, 98.00, '2026-03-22 01:05:49'),
(127, 1, 100.00, 100.00, '2026-03-22 01:05:59'),
(128, 1, 100.00, 99.00, '2026-03-22 01:06:09'),
(129, 1, 100.00, 100.00, '2026-03-22 01:06:19'),
(130, 1, 100.00, 99.00, '2026-03-22 01:06:29'),
(131, 1, 100.00, 100.00, '2026-03-22 01:06:39'),
(132, 1, 100.00, 99.00, '2026-03-22 01:06:49'),
(133, 1, 100.00, 100.00, '2026-03-22 01:06:59'),
(134, 1, 100.00, 98.00, '2026-03-22 01:07:09'),
(135, 1, 100.00, 100.00, '2026-03-22 01:07:19'),
(136, 1, 100.00, 97.00, '2026-03-22 01:07:29'),
(137, 1, 100.00, 100.00, '2026-03-22 01:07:39'),
(138, 1, 100.00, 98.00, '2026-03-22 01:07:49'),
(139, 1, 100.00, 100.00, '2026-03-22 01:07:59'),
(140, 1, 100.00, 97.00, '2026-03-22 01:08:09'),
(141, 1, 100.00, 100.00, '2026-03-22 01:08:19'),
(142, 1, 100.00, 97.00, '2026-03-22 01:08:29'),
(143, 1, 100.00, 100.00, '2026-03-22 01:08:39'),
(144, 1, 100.00, 98.00, '2026-03-22 01:08:49'),
(145, 1, 100.00, 100.00, '2026-03-22 01:08:59'),
(146, 1, 100.00, 97.00, '2026-03-22 01:09:09'),
(147, 1, 100.00, 79.00, '2026-03-22 04:04:49'),
(148, 1, 100.00, 85.00, '2026-03-22 04:04:59'),
(149, 1, 100.00, 90.00, '2026-03-22 04:05:09'),
(150, 1, 100.00, 77.00, '2026-03-22 04:08:14'),
(151, 1, 100.00, 82.00, '2026-03-22 04:08:24'),
(152, 1, 100.00, 84.00, '2026-03-22 04:08:34'),
(153, 1, 100.00, 89.00, '2026-03-22 04:08:44'),
(154, 1, 100.00, 93.00, '2026-03-22 04:08:54'),
(155, 1, 100.00, 95.00, '2026-03-22 04:09:04'),
(156, 1, 100.00, 93.00, '2026-03-22 04:09:14'),
(157, 1, 100.00, 90.00, '2026-03-22 04:09:24'),
(158, 1, 100.00, 88.00, '2026-03-22 04:09:34'),
(159, 1, 100.00, 86.00, '2026-03-22 04:09:44'),
(160, 1, 100.00, 83.00, '2026-03-22 04:09:54'),
(161, 1, 100.00, 82.00, '2026-03-22 04:10:04'),
(162, 1, 100.00, 81.00, '2026-03-22 04:10:14'),
(163, 1, 100.00, 79.00, '2026-03-22 04:10:24'),
(164, 1, 100.00, 78.00, '2026-03-22 04:10:34'),
(165, 1, 100.00, 77.00, '2026-03-22 04:10:44'),
(166, 1, 100.00, 74.00, '2026-03-22 04:10:54'),
(167, 1, 100.00, 72.00, '2026-03-22 04:11:04'),
(168, 1, 100.00, 69.00, '2026-03-22 04:11:14'),
(169, 1, 100.00, 66.00, '2026-03-22 04:11:24'),
(170, 1, 100.00, 63.00, '2026-03-22 04:11:34'),
(171, 1, 100.00, 62.00, '2026-03-22 04:11:44'),
(172, 1, 100.00, 60.00, '2026-03-22 04:11:54'),
(173, 1, 100.00, 59.00, '2026-03-22 04:12:04'),
(174, 1, 100.00, 58.00, '2026-03-22 04:12:14'),
(175, 1, 100.00, 57.00, '2026-03-22 04:12:24'),
(176, 1, 100.00, 56.00, '2026-03-22 04:12:34'),
(177, 1, 100.00, 53.00, '2026-03-22 04:12:44'),
(178, 1, 100.00, 50.00, '2026-03-22 04:12:54'),
(179, 1, 100.00, 53.00, '2026-03-22 04:13:04'),
(180, 1, 100.00, 52.00, '2026-03-22 04:13:14'),
(181, 1, 100.00, 57.00, '2026-03-22 04:13:25'),
(182, 1, 100.00, 55.00, '2026-03-22 04:13:34'),
(183, 1, 100.00, 53.00, '2026-03-22 04:13:44'),
(184, 1, 100.00, 50.00, '2026-03-22 04:13:54'),
(185, 1, 100.00, 53.00, '2026-03-22 04:14:04'),
(186, 1, 100.00, 50.00, '2026-03-22 04:14:14'),
(187, 1, 100.00, 54.00, '2026-03-22 04:14:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `instancia_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `tipo` enum('nota','riego','fertilizacion','poda','cosecha','alerta','otro') DEFAULT 'nota',
  `texto` text NOT NULL,
  `auto` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id`, `instancia_id`, `fecha`, `hora`, `tipo`, `texto`, `auto`, `creado_en`) VALUES
(1, 1, '2026-03-17', NULL, 'nota', 'Primera lectura del sensor, planta recién instalada.', 0, '2026-03-22 00:19:19'),
(2, 1, '2026-03-18', NULL, 'riego', 'Riego manual preventivo, humedad estaba en 28%.', 0, '2026-03-22 00:19:19'),
(3, 1, '2026-03-20', NULL, 'fertilizacion', 'Primera dosis de nutrientes vía surtidor automático.', 1, '2026-03-22 00:19:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `celular` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `nombre`, `correo`, `direccion`, `celular`, `total`, `fecha`, `estado`) VALUES
(2, 7, 'miguel', 'mpantoja637@gmail.com', 'dsadsasad', '43432423', 13998.00, '2026-04-21 20:59:03', 'pendiente'),
(3, 7, 'miguel', 'mpantoja637@gmail.com', 'miguerl', '565367283', 8999.00, '2026-04-21 21:08:48', 'pendiente'),
(4, 7, 'miguel', 'mpantoja637@gmail.com', 'nis', '34412442', 8999.00, '2026-04-21 21:11:15', 'pendiente'),
(5, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'muigue', '43243425', 22997.00, '2026-04-21 21:12:46', 'pendiente'),
(6, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'muigue', '43243425', 22997.00, '2026-04-21 21:15:02', 'pendiente'),
(7, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:16:09', 'pendiente'),
(8, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'muigue', '43243425', 22997.00, '2026-04-21 21:16:49', 'pendiente'),
(9, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'muigue', '43243425', 22997.00, '2026-04-21 21:19:02', 'pendiente'),
(10, 0, 'miguel', 'mpantoja637@gmail.com', 'sdadsad', '7296788288', 27996.00, '2026-04-21 21:19:17', 'pendiente'),
(11, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:20:13', 'pendiente'),
(12, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:21:29', 'pendiente'),
(13, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:23:52', 'pendiente'),
(14, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:23:54', 'pendiente'),
(15, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:24:24', 'pendiente'),
(16, 0, 'miguel', 'mpantoja637@gmail.com', 'sdadsad', '7296788288', 27996.00, '2026-04-21 21:26:29', 'pendiente'),
(17, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'noisenoe', '55563453', 15197.00, '2026-04-21 21:26:48', 'pendiente'),
(18, 0, 'miguel', 'mpantoja637@gmail.com', 'sdasadsa', '7296788288', 4999.00, '2026-04-21 21:31:46', 'pendiente'),
(19, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'nose', '555454732', 12197.00, '2026-04-21 21:32:04', 'pendiente'),
(20, 0, 'miguel', 'mpantoja637@gmail.com', 'nose', '423554235', 2499.00, '2026-04-21 21:34:41', 'pendiente'),
(21, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'nosenos', '554564738', 4999.00, '2026-04-21 21:36:50', 'pendiente'),
(22, 0, 'miguel', 'mpantoja637@gmail.com', 'nsoenose', '5545464783', 7498.00, '2026-04-21 21:51:39', 'pendiente'),
(23, 0, 'miguel', 'mpantoja637@gmail.com', 'noxednoq', '738942789', 13998.00, '2026-04-21 21:52:28', 'enviado'),
(24, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'nosneo', '554547673', 13998.00, '2026-04-21 21:57:17', 'enviado'),
(25, 0, 'miguel', 'mpantoja637@gmail.com', 'gfgd', '43534543', 4999.00, '2026-04-21 22:03:00', 'enviado'),
(27, 0, 'miguel', 'mpantoja637@gmail.com', 'santiago ahuizotla', '444433131', 2499.00, '2026-04-21 22:33:21', 'enviado'),
(28, 0, 'miguel', 'mpantoja637@gmail.com', 'saddnaso', '4378912689', 2499.00, '2026-04-21 22:33:48', 'enviado'),
(29, 0, 'miguel', 'mpantoja637@gmail.com', 'gdfdfd', '34545365', 2499.00, '2026-04-21 22:37:07', 'enviado'),
(30, 0, 'Miguel perez', 'mpantoja637@gmail.com', 'Hhslab', '581539', 2499.00, '2026-04-21 23:09:41', 'enviado'),
(31, 0, 'Angel', 'angel@email.com', 'Galeón 66', '727488362', 2499.00, '2026-04-22 00:11:05', 'enviado'),
(32, 0, 'Angel', 'angel.ramos.jain@estudiante.uacm.edu.mx', 'Galon66', '827482784', 2499.00, '2026-04-22 00:12:16', 'enviado'),
(33, 0, 'miguel', 'mpantoja637@gmail.com', 'nose que direccion', '623157812', 4998.00, '2026-04-22 04:42:59', 'pendiente'),
(34, 0, 'miguel', 'mpantoja637@gmail.com', '2131232', '123423123', 9998.00, '2026-04-22 05:00:32', 'pendiente'),
(35, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'dasdas', 'fasfas', 8999.00, '2026-04-22 05:01:17', 'pendiente'),
(36, 0, 'miguel', 'cambiardecuenta1@gmail.com', '12332', '4212312', 4999.00, '2026-04-22 05:02:18', 'pendiente'),
(37, 0, 'miguel', 'mpantoja637@gmail.com', 'bhjkghjkhjjk', '78578578', 2499.00, '2026-04-22 05:03:14', 'pendiente'),
(38, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'dsadas', '4212312', 2499.00, '2026-04-22 05:05:17', 'pendiente'),
(39, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'asSAs', '4212312', 2499.00, '2026-04-22 05:05:39', 'pendiente'),
(40, 0, 'miguel', 'cambiardecuenta1@gmail.com', 'sdadsa', 'dasdssa', 2499.00, '2026-04-22 05:08:55', 'pendiente'),
(41, 0, 'miguel', 'mpantoja637@gmail.com', 'dsadsads', '431232', 2499.00, '2026-04-22 05:11:56', 'pendiente'),
(42, 0, 'miguel', 'mpantoja637@gmail.com', 'dsadsasd', '3412312332', 2499.00, '2026-04-22 05:12:34', 'pendiente'),
(44, 0, 'prueba1', '1@gmail.com', 'dsadsa', 'fsafsfs', 2499.00, '2026-04-22 05:29:41', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_detalle`
--

INSERT INTO `pedido_detalle` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio`, `imagen`) VALUES
(1, 33, 1, 2, 2499.00, NULL),
(2, 34, 2, 2, 4999.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantas`
--

CREATE TABLE `plantas` (
  `id` int(11) NOT NULL,
  `nombre_comun` varchar(255) DEFAULT NULL,
  `nombre_cientifico` varchar(255) DEFAULT NULL,
  `familia` varchar(255) DEFAULT NULL,
  `genero` varchar(255) DEFAULT NULL,
  `confianza` int(11) DEFAULT NULL,
  `organo` varchar(100) DEFAULT NULL,
  `imagen_subida` varchar(255) DEFAULT NULL,
  `imagen_referencia` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id`, `nombre_comun`, `nombre_cientifico`, `familia`, `genero`, `confianza`, `organo`, `imagen_subida`, `imagen_referencia`, `fecha`) VALUES
(1, 'Manzanilla fina', 'Matricaria aurea (Loefl.) Sch.Bip.', 'Asteraceae', 'Matricaria', 38, 'No detectado', 'uploads/1774158321_manzanilla-beneficios-contraindicaciones.jpg', NULL, '2026-03-02 21:04:30'),
(2, 'No disponible', 'Helianthus bolanderi A.Gray', 'Asteraceae', 'Helianthus', 3, NULL, 'uploads/1772488083_girasols.jpg', NULL, '2026-03-02 21:48:05'),
(4, 'No disponible', 'Radyera farragei (F.Muell.) Fryxell & S.H.Hashmi', 'Malvaceae', 'Radyera', 7, NULL, 'uploads/1772488873_flort.jpg', NULL, '2026-03-02 22:01:18'),
(13, 'Rosa', 'Rosa centifolia L.', 'Rosaceae', 'Rosa', 16, NULL, 'uploads/1773334629_flort.jpg', NULL, '2026-03-12 16:56:43'),
(18, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 40, NULL, 'uploads/1773336166_images.jpg', NULL, '2026-03-12 17:22:55'),
(21, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1773766296_sunflower-7887955_1280.jpg', NULL, '2026-03-17 16:51:43'),
(23, 'sdasadas', 'sdas', 'asd', 'asd', NULL, 'asd', 'uploads/1774139485_icono.png', NULL, '2026-03-22 00:31:25'),
(24, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 99, NULL, 'uploads/1774141210_marihuana-zodiacal.jpg', NULL, '2026-03-22 01:00:15'),
(25, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 99, NULL, 'uploads/1774141528_marihuana-zodiacal.jpg', NULL, '2026-03-22 01:05:34'),
(26, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 99, NULL, 'uploads/1774151039_marihuana-zodiacal.jpg', NULL, '2026-03-22 03:44:00'),
(27, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1774157189_sunflower-7887955_1280.jpg', NULL, '2026-03-22 05:26:31'),
(28, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1774158098_sunflower-7887955_1280.jpg', NULL, '2026-03-22 05:41:39'),
(29, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1774158119_sunflower-7887955_1280.jpg', NULL, '2026-03-22 05:42:01'),
(30, 'mota', 'mota power', 'No especificada', 'macho', 100, NULL, 'uploads/1774158877_marihuana-zodiacal.jpg', NULL, '2026-03-22 05:52:25'),
(31, 'Rosa', 'rosa', 'nose', 'hembra', 100, NULL, NULL, NULL, '2026-03-22 05:55:39'),
(32, 'Mota', 'Mota Power', 'Chingona', 'Fluido', 100, NULL, 'uploads/1774162096_marihuana-zodiacal.jpg', NULL, '2026-03-22 06:48:18'),
(33, 'Artemisa', 'Tanacetum parthenium (L.) Sch.Bip.', 'Asteraceae', 'Tanacetum', 84, NULL, 'uploads/1774163069_manzanilla-beneficios-contraindicaciones.jpg', NULL, '2026-03-22 07:04:30'),
(34, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1774199517_sunflower-7887955_1280.jpg', NULL, '2026-03-22 17:11:58'),
(35, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 62, NULL, 'uploads/1774199724_sunflower-7887955_1280.jpg', NULL, '2026-03-22 17:15:25'),
(36, 'asd', 'sd', 'sad', 'sad', 100, NULL, NULL, NULL, '2026-03-22 17:15:56'),
(37, 'fsdfsdfd', 'sfdf', 'sdfs', 'dsf', 100, NULL, NULL, NULL, '2026-03-22 17:19:17'),
(38, 'dsdfsdfds', 'dsfssdfsf', 'sdfd', 'dsfd', 100, NULL, NULL, NULL, '2026-03-22 17:27:31'),
(39, 'asd', 'asdasd', 'sad', 'asd', 100, NULL, NULL, NULL, '2026-03-22 17:30:33'),
(40, 'ada', 'a', 's', 's', 100, NULL, NULL, NULL, '2026-03-22 17:32:00'),
(41, 'ada', 'a', 'a', 'a', 100, NULL, NULL, NULL, '2026-03-22 17:36:16'),
(42, 'ada', 'a', 'a', 'a', 100, NULL, 'uploads/1774208508_hermosas-rosas-jardin-cultivando-diferentes-variedades-flores-jardineria-como-pasatiempo_162895-915.avif', NULL, '2026-03-22 19:38:34'),
(43, 'ada', 'a', 'a', 'a', 100, NULL, NULL, NULL, '2026-03-22 19:44:06'),
(44, 'ada', 'a', 'a', 'a', 100, NULL, NULL, NULL, '2026-03-22 19:50:07'),
(45, 'ada', 'a', 'a', 'a', 100, NULL, NULL, NULL, '2026-03-22 19:52:02'),
(46, 'ada', 'a', 'a', 'a', 100, NULL, 'uploads/1774209596_manzanilla-beneficios-contraindicaciones.jpg', NULL, '2026-03-22 19:59:58'),
(47, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 82, NULL, 'uploads/1775447884_girasols.jpg', NULL, '2026-04-06 03:58:05'),
(48, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 82, NULL, 'uploads/1775447896_girasols.jpg', NULL, '2026-04-06 03:58:17'),
(49, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 90, NULL, 'uploads/1775449351_images.jpg', NULL, '2026-04-06 04:22:32'),
(50, 'Manzanilla común', 'Matricaria chamomilla L.', 'Asteraceae', 'Matricaria', 72, NULL, 'uploads/1775449497_plamnta.jpg', NULL, '2026-04-06 04:24:58'),
(51, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 82, NULL, 'uploads/1776795447_girasols.jpg', NULL, '2026-04-21 18:17:28'),
(52, 'nose', 'noe', 'no', 'no', 100, NULL, NULL, NULL, '2026-04-21 19:27:28'),
(53, 'Hibisco', 'Hibiscus rosa-sinensis L.', 'Malvaceae', 'Hibiscus', 78, NULL, 'uploads/1776799665_flort.jpg', NULL, '2026-04-21 19:27:46'),
(54, 'NshslB', 'Nsvdjs', 'Bdlans', 'Bdns', 100, NULL, NULL, NULL, '2026-04-21 23:08:55'),
(55, 'Coleo', 'Coleus spp.', 'Lamiaceae', 'Coleus', 38, NULL, 'uploads/1776814284_1776814265442249787001278252925.jpg', NULL, '2026-04-21 23:31:30'),
(56, 'dsadsa', 'dsadsa', 'sdads', 'dsadsa', 100, NULL, NULL, NULL, '2026-04-22 05:29:25'),
(57, 'dsadsa', 'dsadsa', 'sdads', 'dsadsa', 100, NULL, NULL, NULL, '2026-04-22 05:29:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `imagen`, `stock`, `descripcion`) VALUES
(1, 'GrowSystem Starter', 2499.00, 'uploads/productos/1776802280_leon-en-blanco-y-negro_2560x1600_xtrafondos.com.jpg', 10, 'Sistema indoor básico para principiantes. Incluye ESP32, sensor DHT22, 2 relés y panel LED de 30W. Controla humedad. Cubre hasta 0.25m².'),
(2, 'GrowSystem Pro', 4999.00, 'img/pro.jpg', 8, 'Sistema intermedio con sensores de CO2, luz, humedad de suelo y temperatura. Panel LED full spectrum 60W. Cubre hasta 0.5m². Incluye app de monitoreo.'),
(3, 'GrowSystem Elite', 8999.00, 'img/elite.jpg', 5, 'Sistema avanzado con control total: CO2, pH, temperatura, humedad, luz y riego automatizado. Panel LED 120W. Cubre hasta 1m². Dashboard web incluido.'),
(4, 'GrowSystem Hydro', 11999.00, 'img/hydro.jpg', 4, 'Sistema hidropónico NFT completo con bomba, sensores de pH y EC, iluminación full spectrum y control de riego por ciclos. Capacidad para 12 plantas.'),
(5, 'Kit de Instalación Básico', 399.00, 'img/kit_instalacion.jpg', 20, 'Incluye caja IP65, fuente 12V 5A, cables, conectores y guía de instalación paso a paso.'),
(6, 'Kit Estructura Grow Tent 60x60cm', 1199.00, 'img/tent_60.jpg', 10, 'Carpa de cultivo 60x60x140cm con interior reflectante, ventana de inspección y entradas para cables y ductos.'),
(8, 'Instalación y Configuración a Domicilio', 699.00, 'uploads/productos/1776802230_spider-man-el-hombre-arana-marvels_5120x2880_xtrafondos.com.jpg', 20, 'Servicio de instalación, configuración del dashboard y capacitación básica. Disponible en CDMX y área metropolitana.'),
(9, 'prueba', 1222.00, 'uploads/productos/1776810662_images.jpg', 0, NULL),
(10, 'herramienta', 5423.00, 'uploads/productos/1776810705_girasols.jpg', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','cliente') DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Angel Ramos', 'angel@email.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'admin', '2026-03-22 00:19:19'),
(2, 'Pepe Fraid Soleno', 'elpepe@email.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'cliente', '2026-03-22 00:19:19'),
(3, 'jose perez', 'jose@gmail.com', '$2y$10$mB3lOek/mVQ5ZYwsKQD.HOKrmfcatYLvFbaB1IrKc86JbJhq4UWJ2', 'cliente', '2026-03-22 03:39:48'),
(4, 'patricia ramos', 'pato@gmail.com', '$2y$10$0CcXBuCboLDKvkImWbE1COK1Ow4YJZvyWegGGjI.CK1cyMdjx1uTe', 'cliente', '2026-03-22 03:41:22'),
(5, 'federico', 'fede@gmail.com', '$2y$10$LzW21slFpAeATAzniDGiluPXnNFW5DczSBifprIx2yj2WgqW2w0Aq', 'cliente', '2026-03-22 05:25:55'),
(6, 'angel ramos jain', 'angel.ramos.jain@estudiante.uacm.edu.mx', '$2y$10$Cz68c8RSrVI08hkpIoxaju8n1JefHS7PP8bRS2t3ycqVbV04mPVLe', 'admin', '2026-03-22 21:13:18'),
(7, 'miguel', 'mpantoja637@gmail.com', '$2y$10$9AsD2KZP75CETJGSlIofzu7qNGxfVGb6gWZkS92Lhl5mpZ/2GBdHi', 'cliente', '2026-04-06 03:57:38'),
(8, 'MIGUEL ANGEL', 'm@gmail.com', '$2y$10$CNPzrNqRqLP3zoob/rO4R.Urf7MCzzYOoc0jn2KBWE.acyx9.h0DW', 'cliente', '2026-04-21 18:26:40'),
(9, 'p', 'P@gmail.com', '$2y$10$k9rujvlzAg8w7Xr7RwYKye0PZAQZESmfpG/eWBBTay7qvaJ6JbCeO', 'cliente', '2026-04-21 19:27:12'),
(10, 'miguel', 'mpantoj@gmail.com', '$2y$10$XcUHocfhG0mnI87nYFK8V.tOBVdnenKd9FsXfWZ6OYWABBctlHVY2', 'cliente', '2026-04-21 20:25:11'),
(11, 'Miguel perez', 'm7@gmail.com', '$2y$10$emlYp5zONhDUsr0SP4wTaeR6tmrySCmWZ9V0XRTwAmUEqG5o1KQx.', 'cliente', '2026-04-21 23:06:43'),
(12, 'Miguel pantoja', 'prueba1@gmail.com', '$2y$10$NkVVyD7xNVhJvjR7NoAo1.A3Yjek7XFIOnYfAj5yH354q.b0ZSAAm', 'cliente', '2026-04-21 23:20:38'),
(13, 'prueba1', '1@gmail.com', '$2y$10$znCQ67TX37YZp8EMnlyUcuftrPUin3ElQ32WwbBmNmG/dzPzBj1Iq', 'cliente', '2026-04-22 05:20:22');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_dashboard`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_dashboard` (
`instancia_id` int(11)
,`alias` varchar(100)
,`ubicacion` varchar(255)
,`activa` tinyint(1)
,`usuario_id` int(11)
,`usuario_nombre` varchar(100)
,`numero_serie` varchar(100)
,`dispositivo_estado` enum('disponible','asignado','baja')
,`nombre_comun` varchar(255)
,`nombre_cientifico` varchar(255)
,`imagen_subida` varchar(255)
,`umbral_humedad` int(11)
,`umbral_nutrientes` int(11)
,`ventilacion` tinyint(1)
,`hora_encendido` time
,`hora_apagado` time
,`bomba` tinyint(1)
,`ventilador` tinyint(1)
,`luces` tinyint(1)
,`surtidor` tinyint(1)
,`humedad_actual` decimal(5,2)
,`nutrientes_actual` decimal(5,2)
,`ultima_actualizacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_historial`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_historial` (
`instancia_id` int(11)
,`alias` varchar(100)
,`humedad` decimal(5,2)
,`nutrientes` decimal(5,2)
,`fecha` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_panel_admin`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_panel_admin` (
`id` int(11)
,`numero_serie` varchar(100)
,`estado` enum('disponible','asignado','baja')
,`fecha_creacion` timestamp
,`fecha_asignacion` timestamp
,`usuario_nombre` varchar(100)
,`usuario_email` varchar(150)
,`instancia_alias` varchar(100)
,`instancia_activa` tinyint(1)
,`api_token` varchar(64)
,`token_ultimo_uso` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_dashboard`
--
DROP TABLE IF EXISTS `v_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_dashboard`  AS SELECT `i`.`id` AS `instancia_id`, `i`.`alias` AS `alias`, `i`.`ubicacion` AS `ubicacion`, `i`.`activa` AS `activa`, `u`.`id` AS `usuario_id`, `u`.`nombre` AS `usuario_nombre`, `d`.`numero_serie` AS `numero_serie`, `d`.`estado` AS `dispositivo_estado`, `p`.`nombre_comun` AS `nombre_comun`, `p`.`nombre_cientifico` AS `nombre_cientifico`, `p`.`imagen_subida` AS `imagen_subida`, `c`.`umbral_humedad` AS `umbral_humedad`, `c`.`umbral_nutrientes` AS `umbral_nutrientes`, `c`.`ventilacion` AS `ventilacion`, `c`.`hora_encendido` AS `hora_encendido`, `c`.`hora_apagado` AS `hora_apagado`, `ea`.`bomba` AS `bomba`, `ea`.`ventilador` AS `ventilador`, `ea`.`luces` AS `luces`, `ea`.`surtidor` AS `surtidor`, `ea`.`humedad` AS `humedad_actual`, `ea`.`nutrientes` AS `nutrientes_actual`, `ea`.`ultima_actualizacion` AS `ultima_actualizacion` FROM (((((`instancias` `i` join `usuarios` `u` on(`i`.`usuario_id` = `u`.`id`)) join `dispositivos` `d` on(`i`.`dispositivo_id` = `d`.`id`)) left join `plantas` `p` on(`i`.`planta_id` = `p`.`id`)) left join `control` `c` on(`i`.`id` = `c`.`instancia_id`)) left join `estado_actual` `ea` on(`i`.`id` = `ea`.`instancia_id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_historial`
--
DROP TABLE IF EXISTS `v_historial`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_historial`  AS SELECT `l`.`instancia_id` AS `instancia_id`, `i`.`alias` AS `alias`, `l`.`humedad` AS `humedad`, `l`.`nutrientes` AS `nutrientes`, `l`.`fecha` AS `fecha` FROM (`lecturas` `l` join `instancias` `i` on(`l`.`instancia_id` = `i`.`id`)) ORDER BY `l`.`fecha` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_panel_admin`
--
DROP TABLE IF EXISTS `v_panel_admin`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_panel_admin`  AS SELECT `d`.`id` AS `id`, `d`.`numero_serie` AS `numero_serie`, `d`.`estado` AS `estado`, `d`.`fecha_creacion` AS `fecha_creacion`, `d`.`fecha_asignacion` AS `fecha_asignacion`, `u`.`nombre` AS `usuario_nombre`, `u`.`email` AS `usuario_email`, `i`.`alias` AS `instancia_alias`, `i`.`activa` AS `instancia_activa`, `t`.`token` AS `api_token`, `t`.`ultimo_uso` AS `token_ultimo_uso` FROM (((`dispositivos` `d` left join `usuarios` `u` on(`d`.`usuario_id` = `u`.`id`)) left join `instancias` `i` on(`d`.`id` = `i`.`dispositivo_id`)) left join `api_tokens` `t` on(`d`.`id` = `t`.`dispositivo_id` and `t`.`activo` = 1)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_token` (`token`),
  ADD UNIQUE KEY `uq_dispositivo` (`dispositivo_id`),
  ADD KEY `fk_token_disp` (`dispositivo_id`);

--
-- Indices de la tabla `control`
--
ALTER TABLE `control`
  ADD PRIMARY KEY (`instancia_id`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_serie` (`numero_serie`),
  ADD KEY `fk_disp_usuario` (`usuario_id`);

--
-- Indices de la tabla `estado_actual`
--
ALTER TABLE `estado_actual`
  ADD PRIMARY KEY (`instancia_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `instancias`
--
ALTER TABLE `instancias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dispositivo_activo` (`dispositivo_id`),
  ADD KEY `fk_inst_usuario` (`usuario_id`),
  ADD KEY `fk_inst_planta` (`planta_id`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lect_instancia` (`instancia_id`),
  ADD KEY `idx_lect_inst_fecha` (`instancia_id`,`fecha`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nota_instancia` (`instancia_id`),
  ADD KEY `idx_nota_fecha` (`instancia_id`,`fecha`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `instancias`
--
ALTER TABLE `instancias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1475;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `fk_token_disp` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `control`
--
ALTER TABLE `control`
  ADD CONSTRAINT `fk_ctrl_instancia` FOREIGN KEY (`instancia_id`) REFERENCES `instancias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `fk_disp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `estado_actual`
--
ALTER TABLE `estado_actual`
  ADD CONSTRAINT `fk_estado_instancia` FOREIGN KEY (`instancia_id`) REFERENCES `instancias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `instancias`
--
ALTER TABLE `instancias`
  ADD CONSTRAINT `fk_inst_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inst_planta` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inst_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `fk_lect_instancia` FOREIGN KEY (`instancia_id`) REFERENCES `instancias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `fk_nota_instancia` FOREIGN KEY (`instancia_id`) REFERENCES `instancias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `pedido_detalles_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_detalles_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
