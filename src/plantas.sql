-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-03-2026 a las 20:08:20
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
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id`, `nombre_comun`, `nombre_cientifico`, `familia`, `genero`, `confianza`, `organo`, `imagen_subida`, `imagen_referencia`, `fecha`) VALUES
(1, 'Manzanilla fina', 'Matricaria aurea (Loefl.) Sch.Bip.', 'Asteraceae', 'Matricaria', 38, 'No detectado', 'uploads/1772485464_plamnta.jpg', NULL, '2026-03-02 21:04:30'),
(2, 'No disponible', 'Helianthus bolanderi A.Gray', 'Asteraceae', 'Helianthus', 3, NULL, 'uploads/1772488083_girasols.jpg', NULL, '2026-03-02 21:48:05'),
(4, 'No disponible', 'Radyera farragei (F.Muell.) Fryxell & S.H.Hashmi', 'Malvaceae', 'Radyera', 7, NULL, 'uploads/1772488873_flort.jpg', NULL, '2026-03-02 22:01:18'),
(6, 'planta', 'nose', 'papap', 'masmakl', 100, 'mopse', 'uploads/1772489685_flort.jpg', NULL, '2026-03-02 22:14:45'),
(8, 'planta', 'nombre rosita', 'papap', 'macho', 100, '', 'uploads/1772490009_plamnta.jpg', NULL, '2026-03-02 22:20:09'),
(13, 'Rosa ', 'Rosa centifolia L.', 'Rosaceae', 'Rosa', 16, NULL, 'uploads/1773334629_flort.jpg', NULL, '2026-03-12 16:56:43'),
(14, 'Rosa PRUEBA', 'Rosa centifolia L.', 'Rosaceae', 'Rosa', 16, NULL, 'uploads/1773341858_1773333833_flort.jpg', NULL, '2026-03-12 17:13:14'),
(15, 'PRUEBA', 'Rosa centifolia L.', 'Rosaceae', 'Rosa', 16, NULL, 'uploads/1773335729_girasols.jpg', NULL, '2026-03-12 17:14:57'),
(16, 'Rosa PRUEBA CAMBIIO ', 'Rosa centifolia L.', 'Rosaceae', 'Rosa', 16, NULL, 'uploads/1773341845_sandia.webp', NULL, '2026-03-12 17:17:07'),
(17, 'CUALQUIER NOMBRE', 'NOSEW', 'TAMPOCO', 'ni idea', NULL, 'no se', 'uploads/1773341813_rosa.jpg', NULL, '2026-03-12 17:18:25'),
(18, 'Marihuana', 'Cannabis sativa L.', 'Cannabaceae', 'Cannabis', 40, NULL, 'uploads/1773336166_images.jpg', NULL, '2026-03-12 17:22:55'),
(19, 'Manzanilla fina', 'Matricaria aurea (Loefl.) Sch.Bip.', 'Asteraceae', 'Matricaria', 38, NULL, 'uploads/1773342057_1772487148_plamnta.jpg', NULL, '2026-03-12 19:00:59'),
(20, 'rosa grande', 'Radyera farragei (F.Muell.) Fryxell & S.H.Hashmi', 'Malvaceae', 'Radyera', 7, NULL, 'uploads/1773342468_flort.jpg', NULL, '2026-03-12 19:07:50');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
