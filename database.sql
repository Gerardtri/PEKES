-- ========================================================
-- PEKES Pet Shop — Base de Datos Oficial MySQL / MariaDB
-- Compatible con Hostinger Single (phpMyAdmin)
-- Codificación: UTF8MB4
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Estructura de la tabla `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `description`, `display_order`) VALUES
('Gimnasios de piso', 'gimnasios-de-piso', 'Torres y estructuras de suelo con postes rascadores en cabuya natural.', 1),
('Rascadores', 'rascadores', 'Mobiliario complementario de rascado y descanso (Etapa 2).', 3),
('Gimnasios de pared', 'gimnasios-de-pared', 'Circuitos aéreos modulares en triplex de pino de 12 mm con domos panorámicos.', 2);

-- --------------------------------------------------------
-- 2. Estructura de la tabla `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` VARCHAR(100) NOT NULL,
  `sku` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `stage` TINYINT NOT NULL DEFAULT 1,
  `catalog_price` INT NULL,
  `price` INT NOT NULL,
  `dimensions` VARCHAR(255) NULL,
  `image` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `includes` TEXT NULL,
  `is_render` TINYINT(1) NOT NULL DEFAULT 0,
  `is_pending` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `tags` TEXT NULL,
  `page` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sku` (`sku`),
  INDEX `idx_category` (`category`),
  INDEX `idx_stage` (`stage`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `sku`, `name`, `category`, `stage`, `catalog_price`, `price`, `dimensions`, `image`, `description`, `includes`, `is_render`, `is_pending`, `is_active`, `tags`, `page`) VALUES
('piso-6', 'PEK-PIS-001', 'MELINA', 'Gimnasios de piso', 1, 63000, 82000, 'Alto: 60 cm | Ancho: 50 cm | Fondo: 40 cm', '/images/products/P06_MELINA.jpeg', 'Gimnasio compacto de piso con plataforma superior y poste rascador en fibra natural.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 6),
('piso-7', 'PEK-PIS-002', 'ROMA', 'Gimnasios de piso', 1, 94800, 123000, 'Alto: 60 cm | Ancho: 40 cm | Fondo: 40 cm', '/images/products/P07_ROMA.jpeg', 'Gimnasio de piso con base cuadrada, rascador central y bandeja de descanso.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 7),
('piso-8', 'PEK-PIS-003', 'AGATHA', 'Gimnasios de piso', 1, 106300, 138000, 'Alto: 80 cm | Ancho: 42 cm | Fondo: 32 cm', '/images/products/P08_AGATHA.jpeg', 'Estructura vertical media de descanso con dos niveles y poste en cabuya natural.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 8),
('piso-9', 'PEK-PIS-004', 'MATEO', 'Gimnasios de piso', 1, 129750, 169000, 'Alto: 55 cm | Ancho: 60 cm | Fondo: 30 cm', '/images/products/P09_MATEO.jpeg', 'Gimnasio de baja altura con túnel / refugio inferior y rascador.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 9),
('piso-10', 'PEK-PIS-005', 'ALELI', 'Gimnasios de piso', 1, 153650, 200000, 'Alto: 60 cm | Ancho: 50 cm | Fondo: 40 cm', '/images/products/P10_ALELI.jpeg', 'Gimnasio con casita cúbica inferior y plataforma acolchada superior.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 10),
('piso-11', 'PEK-PIS-006', 'ZAFIRO', 'Gimnasios de piso', 1, 166350, 216000, 'Alto: 95 cm | Base: diámetro 60 cm', '/images/products/P11_ZAFIRO.jpeg', 'Gimnasio de base circular con cesta cóncava tipo nido y rascador robusto.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 11),
('piso-12', 'PEK-PIS-007', 'RUFUS', 'Gimnasios de piso', 1, 171400, 223000, 'Alto: 90 cm | Ancho: 50 cm | Fondo: 50 cm', '/images/products/P12_RUFUS.jpeg', 'Gimnasio mediano con refugio cerrado y niveles escalonados.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 12),
('piso-13', 'PEK-PIS-008', 'SIMBA', 'Gimnasios de piso', 1, 179100, 233000, 'Alto: 100 cm | Ancho: 50 cm | Fondo: 40 cm', '/images/products/P13_SIMBA.jpeg', 'Estructura de 1 metro con casita de acceso redondo, niveles y juguete colgante.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 13),
('piso-14', 'PEK-PIS-009', 'ESMERALDA', 'Gimnasios de piso', 1, 198700, 258000, 'Alto: 100 cm | Ancho: 50 cm | Fondo: 40 cm', '/images/products/P14_ESMERALDA.jpeg', 'Gimnasio de dos niveles con casa inferior silueta gato y poste largo.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 14),
('piso-15', 'PEK-PIS-010', 'APOLO', 'Gimnasios de piso', 1, 205600, 267000, 'Alto: 110 cm | Ancho: 60 cm | Fondo: 45 cm', '/images/products/P15_APOLO.jpeg', 'Torre escalonada con casa semicerrada y tres niveles de observación.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 15),
('piso-16', 'PEK-PIS-011', 'SKY', 'Gimnasios de piso', 1, 213700, 278000, 'Alto: 95 cm | Ancho: 70 cm | Fondo: 40 cm', '/images/products/P16_SKY.jpeg', 'Gimnasio horizontal amplio con cama hamaca baja, rascador alto y mirador.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 16),
('piso-17', 'PEK-PIS-012', 'FENIX', 'Gimnasios de piso', 1, 219450, 285000, 'Alto: 110 cm | Ancho: 60 cm | Fondo: 45 cm', '/images/products/P17_FENIX.jpeg', 'Gimnasio con hamaca inferior, casa intermedia y mirador superior acolchado.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 17),
('piso-18', 'PEK-PIS-013', 'CANELA', 'Gimnasios de piso', 1, 225300, 293000, 'Alto: 130 cm | Ancho: 60 cm | Fondo: 45 cm', '/images/products/P18_CANELA.jpeg', 'Gimnasio de 1.30 m con hamaca colgante de descanso y mirador superior con reborde.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 18),
('piso-19', 'PEK-PIS-014', 'OSIRIS', 'Gimnasios de piso', 1, 269200, 350000, 'Alto: 115 cm | Ancho: 60 cm | Fondo: 60 cm', '/images/products/P19_OSIRIS.jpeg', 'Estructura cúbica amplia con rascadores dobles y plataformas amplias.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 19),
('piso-20', 'PEK-PIS-015', 'MORITA', 'Gimnasios de piso', 1, 311850, 405000, 'Alto: 155 cm | Ancho: 60 cm | Fondo: 40 cm', '/images/products/P20_MORITA.jpeg', 'Torre alta de 1.55 m con cilindro refugio y múltiples plataformas circulares.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 20),
('piso-21', 'PEK-PIS-016', 'ARCANO', 'Gimnasios de piso', 1, 324500, 422000, 'Alto: 135 cm | Ancho: 70 cm | Fondo: 40 cm', '/images/products/P21_ARCANO.jpeg', 'Gimnasio multinivel con casa intermedia y postes en cabuya de alta resistencia.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 21),
('piso-22', 'PEK-PIS-017', 'ARES', 'Gimnasios de piso', 1, 334950, 435000, 'Alto: 115 cm | Ancho: 90 cm | Fondo: 40 cm', '/images/products/P22_ARES.jpeg', 'Gimnasio ancho de 90 cm con hamaca, rampa y refugio integrado.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 22),
('piso-23', 'PEK-PIS-018', 'HONEY', 'Gimnasios de piso', 1, 346500, 450000, 'Alto: 135 cm | Ancho: 100 cm | Fondo: 60 cm', '/images/products/P23_HONEY.jpeg', 'Diseño temático floral con cojines tipo flor y cilindro de descanso.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 1, 0, 1, '["gatos", "piso", "etapa-1"]', 23),
('piso-24', 'PEK-PIS-019', 'RAFAEL', 'Gimnasios de piso', 1, 346500, 450000, 'Alto: 165 cm | Ancho: 90 cm | Fondo: 40 cm', '/images/products/P24_RAFAEL.jpeg', 'Gran árbol para gatos de 1.65 m con hamaca, casa central, mirador cuna y postes.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 24),
('piso-25', 'PEK-PIS-020', 'GARY', 'Gimnasios de piso', 1, 358050, 465000, 'Alto: 160 cm | Ancho: 50 cm | Fondo: 40 cm', '/images/products/P25_GARY.jpeg', 'Torre estilizada de 1.60 m diseñada para optimizar espacio vertical.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 25),
('piso-26', 'PEK-PIS-021', 'BALBOA', 'Gimnasios de piso', 1, 415800, 541000, 'Alto: 170 cm | Ancho: 60 cm | Fondo: 45 cm', '/images/products/P26_BALBOA.jpeg', 'Gimnasio completo de 1.70 m con dos casas cúbicas, escalera y hamaca.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 26),
('piso-27', 'PEK-PIS-022', 'CHRIS', 'Gimnasios de piso', 1, 429550, 558000, 'Alto: 215 cm | Ancho: 40 cm | Fondo: 40 cm', '/images/products/P27_CHRIS.jpeg', 'Poste rascador de gran altura (2.15 m) con cápsula transparente y plataformas espirales.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 1, 0, 1, '["gatos", "piso", "etapa-1"]', 27),
('piso-28', 'PEK-PIS-023', 'ORION', 'Gimnasios de piso', 1, 475900, 619000, 'Alto: 170 cm | Ancho: 70 cm | Fondo: 50 cm', '/images/products/P28_ORION.jpeg', 'Gimnasio robusto de 1.70 m con casita esquinera y múltiples miradores.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 28),
('piso-29', 'PEK-PIS-024', 'AURORA', 'Gimnasios de piso', 1, 485100, 631000, 'Alto: 174 cm | Ancho: 80 cm | Fondo: 75 cm', '/images/products/P29_AURORA.jpeg', 'Gimnasio amplio de gran base con hamacas y niveles para múltiples gatos.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 29),
('piso-30', 'PEK-PIS-025', 'TOKYO', 'Gimnasios de piso', 1, 485100, 631000, 'Alto: 170 cm | Ancho: 70 cm | Fondo: 50 cm', '/images/products/P30_TOKYO.jpeg', 'Torre alta con doble refugio, escalera y postes forrados en cabuya.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 30),
('piso-31', 'PEK-PIS-026', 'DALTON', 'Gimnasios de piso', 1, 496650, 646000, 'Alto: 160 cm | Ancho: 100 cm | Fondo: 45 cm', '/images/products/P31_DALTON.jpeg', 'Gimnasio ancho de 1 metro con hamaca incluida y doble columna de postes.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 31),
('piso-32', 'PEK-PIS-027', 'URANO', 'Gimnasios de piso', 1, 629000, 818000, 'Alto: 150 cm | Ancho: 110 cm | Fondo: 45 cm', '/images/products/P32_URANO.jpeg', 'Gimnasio premium en madera y textil con puente y cápsula acrílica.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 32),
('piso-33', 'PEK-PIS-028', 'LODUARDO', 'Gimnasios de piso', 1, 689000, 896000, 'Alto: 200 cm | Ancho: 90 cm | Fondo: 50 cm', '/images/products/P33_LODUARDO.jpeg', 'Torre monumental de 2 metros con 5 niveles escalonados y casa integrada.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 33),
('piso-34', 'PEK-PIS-029', 'SATURNO', 'Gimnasios de piso', 1, 679000, 883000, 'Alto: 165 cm | Ancho: 110 cm | Fondo: 60 cm', '/images/products/P34_SATURNO.jpeg', 'Gimnasio con domo panorámico transparente, plataformas de madera y postes.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 34),
('piso-35', 'PEK-PIS-030', 'HEFESTO', 'Gimnasios de piso', 1, 1039500, 1351000, 'Alto: 165 cm | Ancho: 110 cm | Fondo: 70 cm', '/images/products/P35_HEFESTO.jpeg', 'Estructura máxima en triplex de madera con dos burbujas acrílicas, casa y escaleras.', '["Estructura principal", "Postes rascadores en cabuya", "Accesorios de descanso"]', 0, 0, 1, '["gatos", "piso", "etapa-1"]', 35),
('pared-1', 'PEK-PAR-001', 'Combo #1', 'Gimnasios de pared', 1, 791200, 1029000, 'Composición modular para pared', '/images/products/P46_Combo_1.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u20221 Casa Domo Premium (60x40x35 cm) / \u2022 1 Repisa con comedor doble (45x32 cm) / \u2022 1 Repisa burbuja (45x45 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 46),
('pared-2', 'PEK-PAR-002', 'Combo #2', 'Gimnasios de pared', 1, 1119200, 1455000, 'Composición modular para pared', '/images/products/P47_Combo_2.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa sencilla con adaptaci\u00f3n de colch\u00f3n encima (1.20cm x 35cm x 35cm) / Dos repisas colch\u00f3n (45cm x 30 cm) / Dos escaleras (55cm x 15 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 47),
('pared-3', 'PEK-PAR-003', 'Combo #3', 'Gimnasios de pared', 1, 631200, 821000, 'Composición modular para pared', '/images/products/P48_Combo_3.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo premium (60 cm x 34.5 cm x 42 cm) / Repisa burbuja (45cm x 45 cm) / 3 repisas sencillas (35 cm x 20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 48),
('pared-4', 'PEK-PAR-004', 'Combo #4', 'Gimnasios de pared', 1, 598000, 777000, 'Composición modular para pared', '/images/products/P49_Combo_4.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u2022 1 Casa (40x35x35 cm) / \u2022 1 Repisa burbuja (45x45 cm) / \u2022 1 Repisa est\u00e1ndar (35x20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 49),
('pared-5', 'PEK-PAR-005', 'Combo #5', 'Gimnasios de pared', 1, 631200, 821000, 'Composición modular para pared', '/images/products/P50_Combo_5.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo (60 cm x 34.5cm x 42 cm) / Repisa burbuja (45 cm x 45 cm) / Repisa comedero doble (32 cm x 45 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 50),
('pared-6', 'PEK-PAR-006', 'Combo #6', 'Gimnasios de pared', 1, 479200, 623000, 'Composición modular para pared', '/images/products/P51_Combo_6.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa (1.20 cm x 35 cm x 35 cm) / Tres repisas sencillas de (35 cm x 20 cm) / Rascador (70 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 51),
('pared-7', 'PEK-PAR-007', 'Combo #7', 'Gimnasios de pared', 1, 712000, 926000, 'Composición modular para pared', '/images/products/P52_Combo_7.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["M\u00f3dulos de pared", "Rascadores y repisas"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 52),
('pared-8', 'PEK-PAR-008', 'Combo #8', 'Gimnasios de pared', 1, 479200, 623000, 'Composición modular para pared', '/images/products/P53_Combo_8.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo sencilla (40 cm x 42 cm x 35 cm) / Repisa colch\u00f3n (45 cm x 30cm) / Repisa sencilla (35 cm x 20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 53),
('pared-9', 'PEK-PAR-009', 'Combo #9', 'Gimnasios de pared', 1, 583200, 758000, 'Composición modular para pared', '/images/products/P54_Combo_9.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo sencilla (40 cm x 42 cm x 35 cm) / Repisa comedero doble (45 cm x 32cm) / Tres repisas sencillas (35 cm x 20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 54),
('pared-10', 'PEK-PAR-010', 'Combo #10', 'Gimnasios de pared', 1, 399200, 519000, 'Composición modular para pared', '/images/products/P55_Combo_10.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Dos repisas colch\u00f3n (45 cm x 30 cm) / Dos repisas sencillas (35 cm x 20 cm) / Rascador 70 cm"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 55),
('pared-11', 'PEK-PAR-011', 'Combo #11', 'Gimnasios de pared', 1, 368000, 478000, 'Composición modular para pared', '/images/products/P56_Combo_11.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u2022 1 Repisa burbuja (45x45 cm) / \u2022 3 Repisas (35x20 cm) / \u2022 1 Rascador de 70 cm"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 56),
('pared-12', 'PEK-PAR-012', 'Combo #12', 'Gimnasios de pared', 1, 708400, 921000, 'Composición modular para pared', '/images/products/P57_Combo_12.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u2022 2 Casas (40x35x35 cm) / \u2022 1 Repisa burbuja (45x45 cm) / \u2022 2 Repisas (35x20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 57),
('pared-13', 'PEK-PAR-013', 'Combo #13', 'Gimnasios de pared', 1, 483000, 628000, 'Composición modular para pared', '/images/products/P58_Combo_13.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa sencilla (45 cm x 35 cm x 35 cm) / Dos escaleras (55 cm x 15 cm) / Dos repisas sencillas (35 cmx 20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 58),
('pared-14', 'PEK-PAR-014', 'Combo #14', 'Gimnasios de pared', 1, 599200, 779000, 'Composición modular para pared', '/images/products/P59_Combo_14.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Dos casas sencillas (40 cm x 35 cm x 35 cm) / Dos repisas sencillas (35 cm x 20 cm) / Repisa (20 cm x 20 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 59),
('pared-15', 'PEK-PAR-015', 'Combo #15', 'Gimnasios de pared', 1, 663200, 862000, 'Composición modular para pared', '/images/products/P60_Combo_15.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo sencilla (40 cm x 42 cm x 35 cm) / Repisa burbuja (45 cm x 45cm) / Escalera (55 cm x 15 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 60),
('pared-16', 'PEK-PAR-016', 'Combo #16', 'Gimnasios de pared', 1, 579600, 753000, 'Composición modular para pared', '/images/products/P61_Combo_16.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u20221 Casa premium / \u20222 Repisas (35x20 cm) / \u20221 Repisa burbuja (45x45 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 61),
('pared-17', 'PEK-PAR-017', 'Combo #17', 'Gimnasios de pared', 1, 552000, 718000, 'Composición modular para pared', '/images/products/P62_Combo_17.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["\u2022 1 Casa (40x35x35 cm) / \u2022 3 Repisas (35x20 cm) / \u2022 1 Repisa comedero doble"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 62),
('pared-18', 'PEK-PAR-018', 'Combo #18', 'Gimnasios de pared', 1, 879200, 1143000, 'Composición modular para pared', '/images/products/P63_Combo_18.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["Casa domo sencilla (40 cm x 45 cm x 35 cm) / Casa sencilla (40 cm x 35 cm x 35 cm) / Repisa colch\u00f3n (45 cm x 30 cm)"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 63),
('pared-19', 'PEK-PAR-019', 'Combo #19', 'Gimnasios de pared', 1, 736000, 957000, 'Composición modular para pared', '/images/products/P64_Combo_19.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["M\u00f3dulos de pared", "Rascadores y repisas"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 64),
('pared-20', 'PEK-PAR-020', 'Combo #20', 'Gimnasios de pared', 1, 1012000, 1316000, 'Composición modular para pared', '/images/products/P65_Combo_20.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["M\u00f3dulos de pared", "Rascadores y repisas"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 65),
('pared-21', 'PEK-PAR-021', 'Combo #21', 'Gimnasios de pared', 1, 799200, 1039000, 'Composición modular para pared', '/images/products/P66_Combo_21.jpeg', 'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.', '["M\u00f3dulos de pared", "Rascadores y repisas"]', 0, 0, 1, '["gatos", "pared", "combos", "etapa-1"]', 66),
('rascador-37', 'PEK-RAS-037', 'PARIS', 'Rascadores', 2, 55550, 72000, 'Alto: 60 Cm | Ancho: 20 Cm | Fondo: 20 Cm', '/images/products/P37_PARIS.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 37),
('rascador-38', 'PEK-RAS-038', 'LILA', 'Rascadores', 2, 50850, 66000, 'Alto: 60 Cm | Ancho: 20 Cm | Fondo: 20 Cm', '/images/products/P38_LILA.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 38),
('rascador-39', 'PEK-RAS-039', 'ELVIS', 'Rascadores', 2, 218300, 284000, 'Alto: 1.30 CM | Base: 60Cm Diametro', '/images/products/P39_ELVIS.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 39),
('rascador-40', 'PEK-RAS-040', 'ZOE', 'Rascadores', 2, 47400, 62000, 'Alto: 72 CM | Base: 50 CM Diametro', '/images/products/P40_ZOE.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 40),
('rascador-41', 'PEK-RAS-041', 'MISHI', 'Rascadores', 2, 43900, 57000, 'Alto: 45 CM | Ancho: 30 Cm | Fondo: 30 Cm', '/images/products/P41_MISHI.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 41),
('rascador-42', 'PEK-RAS-042', 'FIGARO', 'Rascadores', 2, 24950, 32000, 'Alto: 36 CM | Ancho: 17 Cm | Fondo: 17 Cm', '/images/products/P42_FIGARO.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 42),
('rascador-43', 'PEK-RAS-043', 'ATHENA', 'Rascadores', 2, 16500, 21000, 'Alto: 25 CM | Ancho: 10 Cm | Fondo: 10 Cm', '/images/products/P43_ATHENA.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 43),
('rascador-44', 'PEK-RAS-044', 'GARFIELD', 'Rascadores', 2, 15400, 20000, 'Tipo 1: Largo: 23 CM | Tipo 2: Largo: 30Cm', '/images/products/P44_GARFIELD.jpeg', 'Rascador para gatos con estructura de soporte y cuerda natural.', '["Rascador principal"]', 0, 0, 1, '["gatos", "rascadores", "etapa-2"]', 44);

-- --------------------------------------------------------
-- 3. Estructura de la tabla `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'PEKES Pet Shop'),
('city', 'Cali, Colombia'),
('markup_factor', '1.30'),
('whatsapp_number', '573215427745');

-- --------------------------------------------------------
-- 4. Estructura de la tabla `admins`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;