-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-03-2026 a las 07:07:50
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
(2, 2, '803a75233010d46d1537f3a289c5d9243df66a39be337b0a11a666d9dca23f5a', 1, '2026-03-22 00:51:36', NULL),
(3, 3, '1b0e2229b37322a8b16a776fbd85ff9cdfe1cc56f1c9a6278cab607545ad83f9', 1, '2026-03-22 03:44:06', NULL),
(4, 3, 'f4c3b2a1e6d9c8b7a5f0e1d2c3b4a59687f0e1d2c3b4a5f6e7d8c9b0a1b2c3d4', 1, '2026-03-22 04:13:30', '2026-03-22 04:47:52'),
(5, 4, 'a7a89853cdeb61a203cbeaddefc6ca8cfa7630968446f452cbf4d44faf0decdb', 1, '2026-03-22 05:52:30', NULL),
(6, 5, '467a4d84cb330f35fe856fa19950494681d74eb2d8a0660050e12249cf33c193', 1, '2026-03-22 05:55:43', NULL);

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
(2, 30, 40, 0, '08:00:00', '18:00:00', '2026-03-22 00:51:36'),
(3, 30, 40, 0, '08:00:00', '18:00:00', '2026-03-22 03:44:06'),
(4, 30, 40, 0, '08:00:00', '18:00:00', '2026-03-22 05:52:30'),
(5, 30, 40, 0, '08:00:00', '18:00:00', '2026-03-22 05:55:43');

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
(2, 'IND-0002', 2, 'asignado', '2026-03-22 00:19:19', '2026-03-22 00:51:36'),
(3, 'IND-0003', 4, 'asignado', '2026-03-22 00:19:19', '2026-03-22 03:44:06'),
(4, 'IND-0004', 2, 'asignado', '2026-03-22 00:19:19', '2026-03-22 05:52:30'),
(5, 'IND-0005', 2, 'asignado', '2026-03-22 00:19:19', '2026-03-22 05:55:43');

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
(2, 0, 0, 0, 0, NULL, NULL, '2026-03-22 00:51:36'),
(3, 0, 0, 0, 0, 100.00, 38.00, '2026-03-22 04:47:52'),
(4, 0, 0, 0, 0, NULL, NULL, '2026-03-22 05:52:30'),
(5, 0, 0, 0, 0, NULL, NULL, '2026-03-22 05:55:43');

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
(2, 2, 2, NULL, 'Cuarto', '', 1, '2026-03-22 00:51:36'),
(3, 4, 3, 26, 'Marihuana', NULL, 1, '2026-03-22 03:44:06'),
(4, 2, 4, 30, 'mota', NULL, 1, '2026-03-22 05:52:30'),
(5, 2, 5, 31, 'Rosa', NULL, 1, '2026-03-22 05:55:43');

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
(187, 1, 100.00, 54.00, '2026-03-22 04:14:24'),
(188, 3, 100.00, 78.00, '2026-03-22 04:15:02'),
(189, 3, 100.00, 77.00, '2026-03-22 04:15:12'),
(190, 3, 100.00, 74.00, '2026-03-22 04:15:22'),
(191, 3, 100.00, 73.00, '2026-03-22 04:15:32'),
(192, 3, 100.00, 71.00, '2026-03-22 04:15:42'),
(193, 3, 100.00, 68.00, '2026-03-22 04:15:52'),
(194, 3, 100.00, 66.00, '2026-03-22 04:16:02'),
(195, 3, 100.00, 64.00, '2026-03-22 04:16:12'),
(196, 3, 100.00, 61.00, '2026-03-22 04:16:22'),
(197, 3, 100.00, 60.00, '2026-03-22 04:16:32'),
(198, 3, 100.00, 59.00, '2026-03-22 04:16:42'),
(199, 3, 100.00, 57.00, '2026-03-22 04:16:52'),
(200, 3, 100.00, 54.00, '2026-03-22 04:17:02'),
(201, 3, 100.00, 53.00, '2026-03-22 04:17:12'),
(202, 3, 100.00, 51.00, '2026-03-22 04:17:22'),
(203, 3, 100.00, 50.00, '2026-03-22 04:17:32'),
(204, 3, 100.00, 49.00, '2026-03-22 04:17:42'),
(205, 3, 100.00, 48.00, '2026-03-22 04:17:52'),
(206, 3, 100.00, 47.00, '2026-03-22 04:18:02'),
(207, 3, 100.00, 45.00, '2026-03-22 04:18:12'),
(208, 3, 100.00, 42.00, '2026-03-22 04:18:22'),
(209, 3, 100.00, 41.00, '2026-03-22 04:18:32'),
(210, 3, 100.00, 38.00, '2026-03-22 04:18:42'),
(211, 3, 100.00, 42.00, '2026-03-22 04:18:52'),
(212, 3, 100.00, 41.00, '2026-03-22 04:19:02'),
(213, 3, 100.00, 39.00, '2026-03-22 04:19:12'),
(214, 3, 100.00, 42.00, '2026-03-22 04:19:22'),
(215, 3, 100.00, 41.00, '2026-03-22 04:19:32'),
(216, 3, 100.00, 39.00, '2026-03-22 04:19:42'),
(217, 3, 100.00, 41.00, '2026-03-22 04:19:52'),
(218, 3, 100.00, 39.00, '2026-03-22 04:20:02'),
(219, 3, 100.00, 41.00, '2026-03-22 04:20:12'),
(220, 3, 100.00, 38.00, '2026-03-22 04:20:22'),
(221, 3, 100.00, 43.00, '2026-03-22 04:20:32'),
(222, 3, 100.00, 41.00, '2026-03-22 04:20:42'),
(223, 3, 100.00, 38.00, '2026-03-22 04:20:54'),
(224, 3, 100.00, 41.00, '2026-03-22 04:21:02'),
(225, 3, 100.00, 40.00, '2026-03-22 04:21:12'),
(226, 3, 100.00, 38.00, '2026-03-22 04:21:22'),
(227, 3, 100.00, 41.00, '2026-03-22 04:21:32'),
(228, 3, 100.00, 38.00, '2026-03-22 04:21:42'),
(229, 3, 100.00, 38.00, '2026-03-22 04:21:52'),
(230, 3, 100.00, 41.00, '2026-03-22 04:22:02'),
(231, 3, 100.00, 38.00, '2026-03-22 04:22:12'),
(232, 3, 100.00, 38.00, '2026-03-22 04:22:22'),
(233, 3, 100.00, 41.00, '2026-03-22 04:22:32'),
(234, 3, 100.00, 40.00, '2026-03-22 04:22:42'),
(235, 3, 100.00, 38.00, '2026-03-22 04:22:52'),
(236, 3, 100.00, 41.00, '2026-03-22 04:23:02'),
(237, 3, 100.00, 40.00, '2026-03-22 04:23:12'),
(238, 3, 100.00, 39.00, '2026-03-22 04:23:22'),
(239, 3, 100.00, 45.00, '2026-03-22 04:23:32'),
(240, 3, 100.00, 44.00, '2026-03-22 04:23:42'),
(241, 3, 100.00, 44.00, '2026-03-22 04:23:52'),
(242, 3, 100.00, 41.00, '2026-03-22 04:24:02'),
(243, 3, 100.00, 40.00, '2026-03-22 04:24:12'),
(244, 3, 100.00, 38.00, '2026-03-22 04:24:22'),
(245, 3, 100.00, 44.00, '2026-03-22 04:24:32'),
(246, 3, 100.00, 43.00, '2026-03-22 04:24:42'),
(247, 3, 100.00, 42.00, '2026-03-22 04:24:52'),
(248, 3, 100.00, 40.00, '2026-03-22 04:25:02'),
(249, 3, 100.00, 38.00, '2026-03-22 04:25:12'),
(250, 3, 100.00, 42.00, '2026-03-22 04:25:22'),
(251, 3, 100.00, 41.00, '2026-03-22 04:25:32'),
(252, 3, 100.00, 38.00, '2026-03-22 04:25:42'),
(253, 3, 100.00, 42.00, '2026-03-22 04:25:52'),
(254, 3, 100.00, 39.00, '2026-03-22 04:26:02'),
(255, 3, 100.00, 41.00, '2026-03-22 04:26:12'),
(256, 3, 100.00, 39.00, '2026-03-22 04:26:22'),
(257, 3, 100.00, 44.00, '2026-03-22 04:26:32'),
(258, 3, 100.00, 42.00, '2026-03-22 04:26:42'),
(259, 3, 100.00, 41.00, '2026-03-22 04:26:52'),
(260, 3, 100.00, 39.00, '2026-03-22 04:27:02'),
(261, 3, 100.00, 45.00, '2026-03-22 04:27:12'),
(262, 3, 100.00, 44.00, '2026-03-22 04:27:22'),
(263, 3, 100.00, 42.00, '2026-03-22 04:27:32'),
(264, 3, 100.00, 41.00, '2026-03-22 04:27:42'),
(265, 3, 100.00, 41.00, '2026-03-22 04:27:53'),
(266, 3, 100.00, 38.00, '2026-03-22 04:28:02'),
(267, 3, 100.00, 42.00, '2026-03-22 04:28:12'),
(268, 3, 100.00, 42.00, '2026-03-22 04:28:22'),
(269, 3, 100.00, 45.00, '2026-03-22 04:28:32'),
(270, 3, 100.00, 42.00, '2026-03-22 04:28:42'),
(271, 3, 100.00, 39.00, '2026-03-22 04:28:52'),
(272, 3, 100.00, 41.00, '2026-03-22 04:29:02'),
(273, 3, 100.00, 39.00, '2026-03-22 04:29:12'),
(274, 3, 100.00, 44.00, '2026-03-22 04:29:22'),
(275, 3, 100.00, 43.00, '2026-03-22 04:29:32'),
(276, 3, 100.00, 41.00, '2026-03-22 04:29:42'),
(277, 3, 100.00, 41.00, '2026-03-22 04:29:52'),
(278, 3, 100.00, 41.00, '2026-03-22 04:30:02'),
(279, 3, 100.00, 39.00, '2026-03-22 04:30:12'),
(280, 3, 100.00, 39.00, '2026-03-22 04:30:22'),
(281, 3, 100.00, 42.00, '2026-03-22 04:30:32'),
(282, 3, 100.00, 40.00, '2026-03-22 04:30:42'),
(283, 3, 100.00, 39.00, '2026-03-22 04:30:52'),
(284, 3, 100.00, 41.00, '2026-03-22 04:31:02'),
(285, 3, 100.00, 40.00, '2026-03-22 04:31:12'),
(286, 3, 100.00, 38.00, '2026-03-22 04:31:22'),
(287, 3, 100.00, 42.00, '2026-03-22 04:31:32'),
(288, 3, 100.00, 40.00, '2026-03-22 04:31:42'),
(289, 3, 100.00, 40.00, '2026-03-22 04:31:52'),
(290, 3, 100.00, 44.00, '2026-03-22 04:32:02'),
(291, 3, 100.00, 43.00, '2026-03-22 04:32:12'),
(292, 3, 100.00, 43.00, '2026-03-22 04:32:22'),
(293, 3, 100.00, 39.00, '2026-03-22 04:32:32'),
(294, 3, 100.00, 42.00, '2026-03-22 04:32:42'),
(295, 3, 100.00, 40.00, '2026-03-22 04:32:52'),
(296, 3, 100.00, 38.00, '2026-03-22 04:33:02'),
(297, 3, 100.00, 42.00, '2026-03-22 04:33:12'),
(298, 3, 100.00, 39.00, '2026-03-22 04:33:23'),
(299, 3, 100.00, 44.00, '2026-03-22 04:33:32'),
(300, 3, 100.00, 43.00, '2026-03-22 04:33:42'),
(301, 3, 100.00, 43.00, '2026-03-22 04:33:52'),
(302, 3, 100.00, 41.00, '2026-03-22 04:34:02'),
(303, 3, 100.00, 39.00, '2026-03-22 04:34:12'),
(304, 3, 100.00, 39.00, '2026-03-22 04:34:22'),
(305, 3, 100.00, 38.00, '2026-03-22 04:34:32'),
(306, 3, 100.00, 40.00, '2026-03-22 04:34:42'),
(307, 3, 100.00, 37.00, '2026-03-22 04:34:52'),
(308, 3, 100.00, 43.00, '2026-03-22 04:35:02'),
(309, 3, 100.00, 40.00, '2026-03-22 04:35:12'),
(310, 3, 100.00, 38.00, '2026-03-22 04:35:22'),
(311, 3, 100.00, 42.00, '2026-03-22 04:35:32'),
(312, 3, 100.00, 39.00, '2026-03-22 04:35:42'),
(313, 3, 100.00, 39.00, '2026-03-22 04:35:52'),
(314, 3, 100.00, 40.00, '2026-03-22 04:36:02'),
(315, 3, 100.00, 37.00, '2026-03-22 04:36:12'),
(316, 3, 100.00, 37.00, '2026-03-22 04:36:22'),
(317, 3, 100.00, 40.00, '2026-03-22 04:36:32'),
(318, 3, 100.00, 39.00, '2026-03-22 04:36:42'),
(319, 3, 100.00, 45.00, '2026-03-22 04:36:52'),
(320, 3, 100.00, 42.00, '2026-03-22 04:37:02'),
(321, 3, 100.00, 39.00, '2026-03-22 04:37:12'),
(322, 3, 100.00, 45.00, '2026-03-22 04:37:22'),
(323, 3, 100.00, 43.00, '2026-03-22 04:37:32'),
(324, 3, 100.00, 41.00, '2026-03-22 04:37:42'),
(325, 3, 100.00, 41.00, '2026-03-22 04:37:52'),
(326, 3, 100.00, 37.00, '2026-03-22 04:38:02'),
(327, 3, 100.00, 43.00, '2026-03-22 04:38:12'),
(328, 3, 100.00, 43.00, '2026-03-22 04:38:22'),
(329, 3, 100.00, 39.00, '2026-03-22 04:38:32'),
(330, 3, 100.00, 41.00, '2026-03-22 04:38:42'),
(331, 3, 100.00, 40.00, '2026-03-22 04:38:52'),
(332, 3, 100.00, 39.00, '2026-03-22 04:39:02'),
(333, 3, 100.00, 42.00, '2026-03-22 04:39:12'),
(334, 3, 100.00, 39.00, '2026-03-22 04:39:22'),
(335, 3, 100.00, 44.00, '2026-03-22 04:39:33'),
(336, 3, 100.00, 43.00, '2026-03-22 04:39:42'),
(337, 3, 100.00, 42.00, '2026-03-22 04:39:52'),
(338, 3, 100.00, 41.00, '2026-03-22 04:40:02'),
(339, 3, 100.00, 38.00, '2026-03-22 04:40:12'),
(340, 3, 100.00, 41.00, '2026-03-22 04:40:22'),
(341, 3, 100.00, 40.00, '2026-03-22 04:40:32'),
(342, 3, 100.00, 39.00, '2026-03-22 04:40:42'),
(343, 3, 100.00, 39.00, '2026-03-22 04:40:52'),
(344, 3, 100.00, 44.00, '2026-03-22 04:41:02'),
(345, 3, 100.00, 41.00, '2026-03-22 04:41:12'),
(346, 3, 100.00, 41.00, '2026-03-22 04:41:22'),
(347, 3, 100.00, 39.00, '2026-03-22 04:41:32'),
(348, 3, 100.00, 42.00, '2026-03-22 04:41:42'),
(349, 3, 100.00, 40.00, '2026-03-22 04:41:52'),
(350, 3, 100.00, 39.00, '2026-03-22 04:42:02'),
(351, 3, 100.00, 45.00, '2026-03-22 04:42:12'),
(352, 3, 100.00, 43.00, '2026-03-22 04:42:23'),
(353, 3, 100.00, 42.00, '2026-03-22 04:42:32'),
(354, 3, 100.00, 39.00, '2026-03-22 04:42:42'),
(355, 3, 100.00, 39.00, '2026-03-22 04:42:52'),
(356, 3, 100.00, 41.00, '2026-03-22 04:43:02'),
(357, 3, 100.00, 39.00, '2026-03-22 04:43:12'),
(358, 3, 100.00, 39.00, '2026-03-22 04:43:22'),
(359, 3, 100.00, 44.00, '2026-03-22 04:43:32'),
(360, 3, 100.00, 41.00, '2026-03-22 04:43:42'),
(361, 3, 100.00, 39.00, '2026-03-22 04:43:52'),
(362, 3, 100.00, 45.00, '2026-03-22 04:44:02'),
(363, 3, 100.00, 42.00, '2026-03-22 04:44:12'),
(364, 3, 100.00, 42.00, '2026-03-22 04:44:22'),
(365, 3, 100.00, 39.00, '2026-03-22 04:44:32'),
(366, 3, 100.00, 41.00, '2026-03-22 04:44:43'),
(367, 3, 100.00, 38.00, '2026-03-22 04:44:52'),
(368, 3, 100.00, 43.00, '2026-03-22 04:45:02'),
(369, 3, 100.00, 42.00, '2026-03-22 04:45:12'),
(370, 3, 100.00, 39.00, '2026-03-22 04:45:22'),
(371, 3, 100.00, 41.00, '2026-03-22 04:45:32'),
(372, 3, 100.00, 40.00, '2026-03-22 04:45:42'),
(373, 3, 100.00, 38.00, '2026-03-22 04:45:52'),
(374, 3, 100.00, 43.00, '2026-03-22 04:46:02'),
(375, 3, 100.00, 40.00, '2026-03-22 04:46:15'),
(376, 3, 100.00, 38.00, '2026-03-22 04:46:22'),
(377, 3, 100.00, 44.00, '2026-03-22 04:46:32'),
(378, 3, 100.00, 43.00, '2026-03-22 04:46:42'),
(379, 3, 100.00, 43.00, '2026-03-22 04:46:52'),
(380, 3, 100.00, 39.00, '2026-03-22 04:47:02'),
(381, 3, 100.00, 45.00, '2026-03-22 04:47:12'),
(382, 3, 100.00, 44.00, '2026-03-22 04:47:22'),
(383, 3, 100.00, 42.00, '2026-03-22 04:47:33'),
(384, 3, 100.00, 40.00, '2026-03-22 04:47:42'),
(385, 3, 100.00, 38.00, '2026-03-22 04:47:52');

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
(31, 'Rosa', 'rosa', 'nose', 'hembra', 100, NULL, NULL, NULL, '2026-03-22 05:55:39');

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
(5, 'federico', 'fede@gmail.com', '$2y$10$LzW21slFpAeATAzniDGiluPXnNFW5DczSBifprIx2yj2WgqW2w0Aq', 'cliente', '2026-03-22 05:25:55');

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
  ADD KEY `fk_token_disp` (`dispositivo_id`);

--
-- Indices de la tabla `control`
--
ALTER TABLE `control`
  ADD PRIMARY KEY (`instancia_id`);

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
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `instancias`
--
ALTER TABLE `instancias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=386;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
