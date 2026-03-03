-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 03-03-2026 a las 02:50:13
-- Versión del servidor: 8.0.45
-- Versión de PHP: 8.3.26

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
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_copa` int NOT NULL
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
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
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
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `circuito_id` int NOT NULL,
  `veces_seleccionado` int DEFAULT '0',
  `veces_ganador` int DEFAULT '0',
  `fecha_ultima_seleccion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `estadisticas_usuario`
--

INSERT INTO `estadisticas_usuario` (`id`, `usuario_id`, `circuito_id`, `veces_seleccionado`, `veces_ganador`, `fecha_ultima_seleccion`, `fecha_registro`) VALUES
(1, 2, 3, 6, 3, '2026-03-03 02:49:00', '2026-03-03 02:37:48'),
(2, 2, 8, 1, 0, '2026-03-03 02:37:48', '2026-03-03 02:37:48'),
(3, 2, 7, 1, 0, '2026-03-03 02:37:48', '2026-03-03 02:37:48'),
(4, 2, 6, 1, 0, '2026-03-03 02:37:48', '2026-03-03 02:37:48'),
(5, 2, 12, 1, 0, '2026-03-03 02:38:20', '2026-03-03 02:38:20'),
(6, 2, 14, 5, 2, '2026-03-03 02:48:28', '2026-03-03 02:38:20'),
(7, 2, 19, 1, 0, '2026-03-03 02:38:20', '2026-03-03 02:38:20'),
(8, 2, 20, 1, 0, '2026-03-03 02:38:20', '2026-03-03 02:38:20'),
(9, 2, 28, 1, 0, '2026-03-03 02:38:30', '2026-03-03 02:38:30'),
(10, 2, 24, 3, 1, '2026-03-03 02:40:40', '2026-03-03 02:38:30'),
(11, 2, 27, 5, 4, '2026-03-03 02:48:15', '2026-03-03 02:38:59'),
(12, 2, 32, 1, 0, '2026-03-03 02:38:59', '2026-03-03 02:38:59'),
(13, 2, 29, 1, 0, '2026-03-03 02:38:59', '2026-03-03 02:38:59'),
(14, 2, 30, 1, 0, '2026-03-03 02:38:59', '2026-03-03 02:38:59'),
(15, 2, 25, 1, 0, '2026-03-03 02:49:00', '2026-03-03 02:49:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_tiradas`
--

CREATE TABLE `historial_tiradas` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `circuito1_id` int DEFAULT NULL,
  `circuito2_id` int DEFAULT NULL,
  `circuito3_id` int DEFAULT NULL,
  `circuito4_id` int DEFAULT NULL,
  `ganador_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `historial_tiradas`
--

INSERT INTO `historial_tiradas` (`id`, `usuario_id`, `fecha`, `circuito1_id`, `circuito2_id`, `circuito3_id`, `circuito4_id`, `ganador_id`) VALUES
(1, 2, '2026-03-03 02:37:48', 3, 8, 7, 6, 3),
(2, 2, '2026-03-03 02:38:20', 12, 14, 19, 20, 14),
(3, 2, '2026-03-03 02:38:30', 28, 24, NULL, NULL, 24),
(4, 2, '2026-03-03 02:38:59', 27, 32, 29, 30, 27),
(5, 2, '2026-03-03 02:39:28', 27, 24, 14, 3, 27),
(6, 2, '2026-03-03 02:40:40', 27, 24, 14, 3, 27),
(7, 2, '2026-03-03 02:47:54', 3, 27, NULL, NULL, 3),
(8, 2, '2026-03-03 02:48:15', 27, 14, NULL, NULL, 27),
(9, 2, '2026-03-03 02:48:28', 14, 3, NULL, NULL, 14),
(10, 2, '2026-03-03 02:49:00', 3, 25, NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('usuario','admin') COLLATE utf8mb4_general_ci DEFAULT 'usuario',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `pass`, `rol`, `fecha_registro`) VALUES
(1, 'admin', 'admin@rulemkw.com', '$2y$10$z5lMnmH3FBmqIgca0/6v0.H3Xg/7C/vzTZ4cXhSeOIbmNx2wdBuBe', 'admin', '2025-12-03 01:04:22'),
(2, 'Jesus', 'jahernandezg20@educarex.es', '$2y$10$flwgyS/OTHizGI0k7QVmV.lHbX2hWc8Z6T2Y9k7P4AfuUzzNmNYYu', 'usuario', '2026-02-06 01:33:33');

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
-- Indices de la tabla `historial_tiradas`
--
ALTER TABLE `historial_tiradas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `circuito1_id` (`circuito1_id`),
  ADD KEY `circuito2_id` (`circuito2_id`),
  ADD KEY `circuito3_id` (`circuito3_id`),
  ADD KEY `circuito4_id` (`circuito4_id`),
  ADD KEY `ganador_id` (`ganador_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `copas`
--
ALTER TABLE `copas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `estadisticas_usuario`
--
ALTER TABLE `estadisticas_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `historial_tiradas`
--
ALTER TABLE `historial_tiradas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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

--
-- Filtros para la tabla `historial_tiradas`
--
ALTER TABLE `historial_tiradas`
  ADD CONSTRAINT `historial_tiradas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_tiradas_ibfk_2` FOREIGN KEY (`circuito1_id`) REFERENCES `circuitos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historial_tiradas_ibfk_3` FOREIGN KEY (`circuito2_id`) REFERENCES `circuitos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historial_tiradas_ibfk_4` FOREIGN KEY (`circuito3_id`) REFERENCES `circuitos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historial_tiradas_ibfk_5` FOREIGN KEY (`circuito4_id`) REFERENCES `circuitos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `historial_tiradas_ibfk_6` FOREIGN KEY (`ganador_id`) REFERENCES `circuitos` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
