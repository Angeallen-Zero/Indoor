-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-03-2026 a las 02:03:50
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
-- Base de datos: `indoor`
--

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
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id`, `nombre_comun`, `nombre_cientifico`, `familia`, `genero`, `confianza`, `organo`, `imagen_subida`, `imagen_referencia`, `fecha`, `usuario_id`) VALUES
(22, 'pruebas', 'das', 'das', 'asd', 100, 'dsa', 'uploads/1774136329_icono.png', NULL, '2026-03-21 23:38:49', 1),
(23, 'pruebas', 'biode', 'bio', 'bien', 100, 'mopse', 'uploads/1774136399_WhatsApp Image 2026-03-17 at 8.27.32 PM (1).jpeg', NULL, '2026-03-21 23:39:59', 2),
(24, 'Hibisco', 'Hibiscus rosa-sinensis L.', 'Malvaceae', 'Hibiscus', 76, NULL, 'uploads/1774136411_flort.jpg', NULL, '2026-03-21 23:40:13', NULL),
(25, 'Hibisco', 'Hibiscus rosa-sinensis L.', 'Malvaceae', 'Hibiscus', 76, NULL, 'uploads/1774136418_flort.jpg', NULL, '2026-03-21 23:40:20', NULL),
(26, 'Hibisco', 'Hibiscus rosa-sinensis L.', 'Malvaceae', 'Hibiscus', 76, NULL, 'uploads/1774136824_flort.jpg', NULL, '2026-03-21 23:47:06', NULL),
(27, 'Girasol', 'Helianthus annuus L.', 'Asteraceae', 'Helianthus', 82, NULL, 'uploads/1774137147_girasols.jpg', NULL, '2026-03-21 23:52:29', 2),
(28, 'nose otro nombre', 'Hibiscus rosa-sinensis L.', 'Malvaceae', 'Hibiscus', 76, NULL, 'uploads/1774137763_girasols.jpg', NULL, '2026-03-21 23:53:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `creado_en`) VALUES
(1, 'miguel', 'mpantoja637@gmail.com', '$2y$10$ETo.NfKozVLXRdtmw/gYbuPjc2zMFkBrbwkZM7M9zqcwXLcXnKNIy', '2026-03-21 23:33:18'),
(2, 'prueba1', 'cambiardecuenta1@gmail.com', '$2y$10$E5v2scVVxjAbKcRoDUbYy.T1ZLsAaJGF/F0SRGVrN4dcJulmVVb4.', '2026-03-21 23:39:17'),
(3, 'miguel', 'cambiardecuent1@gmail.com', '$2y$10$kPC10Wn7JoIHfs992jKyy.ZHGFB7wwdGFr5xsf8l8Hq6Qx52BXXJa', '2026-03-22 00:15:18');

--
-- Índices para tablas volcadas
--

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
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
