-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-01-2026 a las 13:42:59
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
-- Base de datos: `rulemkw`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `circuitos`
--

CREATE TABLE `circuitos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_copa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `circuitos`
--

INSERT INTO `circuitos` (`id`, `nombre`, `id_copa`) VALUES
(1, 'Circuito Mario Bros.', 1),
(2, 'Ciudad Corona (1)', 1),
(3, 'Cañón Ferroviario', 1),
(4, 'Puerto Espacial DK', 1),
(5, 'Desierto Sol-Sol', 2),
(6, 'Bazar Shy Guy', 2),
(7, 'Estadio Wario', 2),
(8, 'Fortaleza Aérea', 2),
(9, 'DK Alpino', 3),
(10, 'Mirador Estelar', 3),
(11, 'Cielos Helados', 3),
(12, 'Galeón de Wario', 3),
(13, 'Playa Koopa', 4),
(14, 'Sabana Salpicante', 4),
(15, 'Ciudad Corona (2)', 4),
(16, 'Estadio Peach (1)', 4),
(17, 'Playa Peach', 5),
(18, 'Ciudad Salina', 5),
(19, 'Jungla Dino Dino', 5),
(20, 'Templo del Bloque ?', 5),
(21, 'Cascadas Cheep Cheep', 6),
(22, 'Gruta Diente de León', 6),
(23, 'Cine Boo', 6),
(24, 'Caverna Ósea', 6),
(25, 'Pradera Mu-Mu', 7),
(26, 'Monte Chocolate', 7),
(27, 'Fábrica de Toad', 7),
(28, 'Castillo de Bowser', 7),
(29, 'Aldea Arbórea', 8),
(30, 'Circuito Mario', 8),
(31, 'Estadio Peach (2)', 8),
(32, 'Senda Arco Iris', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `copas`
--

CREATE TABLE `copas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `copas`
--

INSERT INTO `copas` (`id`, `nombre`) VALUES
(1, 'Copa Champiñón'),
(2, 'Copa Flor'),
(3, 'Copa Estrella'),
(4, 'Copa Caparazón'),
(5, 'Copa Plátano'),
(6, 'Copa Hoja'),
(7, 'Copa Centella'),
(8, 'Copa Especial');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadisticas_usuario`
--

CREATE TABLE `estadisticas_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `circuito_id` int(11) NOT NULL,
  `veces_seleccionado` int(11) DEFAULT 0,
  `fecha_ultima_seleccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadisticas_usuario`
--

INSERT INTO `estadisticas_usuario` (`id`, `usuario_id`, `circuito_id`, `veces_seleccionado`, `fecha_ultima_seleccion`) VALUES
(1, 1, 6, 2, '2026-01-22 02:23:10'),
(2, 1, 27, 1, '2026-01-22 02:23:17'),
(3, 1, 5, 3, '2026-01-22 02:27:12'),
(4, 1, 1, 2, '2026-01-22 02:29:45'),
(5, 1, 25, 2, '2026-01-22 02:33:00'),
(6, 1, 14, 2, '2026-01-22 02:45:26'),
(7, 1, 30, 1, '2026-01-22 02:49:40'),
(9, 1, 9, 2, '2026-01-22 02:50:22'),
(10, 1, 32, 1, '2026-01-22 02:53:24'),
(11, 1, 2, 2, '2026-01-22 02:54:27'),
(12, 1, 3, 1, '2026-01-22 02:56:44'),
(14, 1, 12, 3, '2026-01-22 02:59:17'),
(15, 1, 29, 1, '2026-01-22 03:02:56'),
(19, 1, 23, 1, '2026-01-22 03:28:53'),
(20, 1, 24, 1, '2026-01-22 03:29:09'),
(22, 1, 11, 1, '2026-01-22 03:39:15'),
(23, 1, 28, 1, '2026-01-22 03:39:34'),
(24, 1, 18, 1, '2026-01-22 03:45:15'),
(28, 1, 21, 1, '2026-01-22 04:36:37'),
(29, 1, 8, 1, '2026-01-22 04:43:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `rol` enum('usuario','admin') DEFAULT 'usuario',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `pass`, `rol`, `fecha_registro`) VALUES
(1, 'admin', 'admin@rulemkw.com', '$2y$10$z5lMnmH3FBmqIgca0/6v0.H3Xg/7C/vzTZ4cXhSeOIbmNx2wdBuBe', 'admin', '2025-12-03 01:04:22');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `circuitos`
--
ALTER TABLE `circuitos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_copa` (`id_copa`);

--
-- Indices de la tabla `copas`
--
ALTER TABLE `copas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estadisticas_usuario`
--
ALTER TABLE `estadisticas_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_circuito` (`usuario_id`,`circuito_id`),
  ADD KEY `circuito_id` (`circuito_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `circuitos`
--
ALTER TABLE `circuitos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `copas`
--
ALTER TABLE `copas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `estadisticas_usuario`
--
ALTER TABLE `estadisticas_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `circuitos`
--
ALTER TABLE `circuitos`
  ADD CONSTRAINT `circuitos_ibfk_1` FOREIGN KEY (`id_copa`) REFERENCES `copas` (`id`);

--
-- Filtros para la tabla `estadisticas_usuario`
--
ALTER TABLE `estadisticas_usuario`
  ADD CONSTRAINT `estadisticas_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estadisticas_usuario_ibfk_2` FOREIGN KEY (`circuito_id`) REFERENCES `circuitos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
