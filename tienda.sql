-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-04-2026 a las 06:40:12
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
-- Base de datos: `tienda`
--

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
(1, 'GrowSystem Starter', 2499.00, 'img/starter.jpg', 10, 'Sistema indoor básico para principiantes. Incluye ESP32, sensor DHT22, 2 relés y panel LED de 30W. Controla humedad. Cubre hasta 0.25m².'),
(2, 'GrowSystem Pro', 4999.00, 'img/pro.jpg', 8, 'Sistema intermedio con sensores de CO2, luz, humedad de suelo y temperatura. Panel LED full spectrum 60W. Cubre hasta 0.5m². Incluye app de monitoreo.'),
(3, 'GrowSystem Elite', 8999.00, 'img/elite.jpg', 5, 'Sistema avanzado con control total: CO2, pH, temperatura, humedad, luz y riego automatizado. Panel LED 120W. Cubre hasta 1m². Dashboard web incluido.'),
(4, 'GrowSystem Hydro', 11999.00, 'img/hydro.jpg', 4, 'Sistema hidropónico NFT completo con bomba, sensores de pH y EC, iluminación full spectrum y control de riego por ciclos. Capacidad para 12 plantas.'),
(5, 'Kit de Instalación Básico', 399.00, 'img/kit_instalacion.jpg', 20, 'Incluye caja IP65, fuente 12V 5A, cables, conectores y guía de instalación paso a paso.'),
(6, 'Kit Estructura Grow Tent 60x60cm', 1199.00, 'img/tent_60.jpg', 10, 'Carpa de cultivo 60x60x140cm con interior reflectante, ventana de inspección y entradas para cables y ductos.'),
(7, 'Kit Estructura Grow Tent 100x100cm', 1799.00, 'img/tent_100.jpg', 7, 'Carpa de cultivo 100x100x180cm. Compatible con GrowSystem Elite e Hydro. Interior Mylar 98% reflectante.'),
(8, 'Instalación y Configuración a Domicilio', 699.00, 'img/instalacion.jpg', 20, 'Servicio de instalación, configuración del dashboard y capacitación básica. Disponible en CDMX y área metropolitana.');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
