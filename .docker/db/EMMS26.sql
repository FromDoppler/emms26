-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 25-08-2025 a las 14:33:22
-- Versión del servidor: 10.5.18-MariaDB-1:10.5.18+maria~ubu2004
-- Versión de PHP: 8.0.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `EMMS26`
--
CREATE DATABASE IF NOT EXISTS `EMMS26` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `EMMS26`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `google_oauth`
--

CREATE TABLE IF NOT EXISTS `google_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(255) NOT NULL,
  `provider_value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_errors`
--

CREATE TABLE IF NOT EXISTS `log_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(150) NOT NULL,
  `function_name` varchar(300) NOT NULL,
  `description` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stripe_customers_jobs`
--

CREATE TABLE IF NOT EXISTS `stripe_customers_jobs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `registered_id` bigint(20) DEFAULT NULL,
  `stripe_customer_id` int(11) DEFAULT NULL,
  `user_snapshot` JSON NOT NULL,
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,
  `spreadsheet_saved` tinyint(1) NOT NULL DEFAULT 0,
  `list_added` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_registered_id` (`registered_id`),
  KEY `idx_stripe_customer_id` (`stripe_customer_id`),
  KEY `idx_email_sent` (`email_sent`),
  KEY `idx_spreadsheet_saved` (`spreadsheet_saved`),
  KEY `idx_list_added` (`list_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registered`
--

CREATE TABLE IF NOT EXISTS `registered` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `register` varchar(50) NOT NULL,
  `phase` varchar(150) NOT NULL,
  `email` varchar(250) NOT NULL,
  `firstname` varchar(150) DEFAULT NULL,
  `lastname` varchar(150) DEFAULT NULL,
  `country` varchar(150) DEFAULT NULL,
  `phone` varchar(300) DEFAULT NULL,
  `company` varchar(300) DEFAULT NULL,
  `jobPosition` varchar(150) DEFAULT NULL,
  `ecommerce` tinyint(1) NOT NULL DEFAULT 1,
  `ecommerce-vip` tinyint(1) NOT NULL DEFAULT 0,
  `digital-trends` tinyint(1) NOT NULL DEFAULT 0,
  `digital-trends-vip` tinyint(1) NOT NULL DEFAULT 0,
  `source_utm` text DEFAULT NULL,
  `medium_utm` text DEFAULT NULL,
  `campaign_utm` text DEFAULT NULL,
  `content_utm` text DEFAULT NULL,
  `term_utm` text DEFAULT NULL,
  `emms_ref` text DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `emailPlatform` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1783 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings_phase`
--

CREATE TABLE IF NOT EXISTS `settings_phase` (
  `event` varchar(255) NOT NULL,
  `pre` tinyint(4) NOT NULL,
  `during` tinyint(4) NOT NULL,
  `post` tinyint(4) NOT NULL,
  `transition` varchar(255) NOT NULL,
  `transmission` varchar(255) NOT NULL DEFAULT 'youtube',
  PRIMARY KEY (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `settings_phase`
--

INSERT INTO `settings_phase` (`event`, `pre`, `during`, `post`, `transition`, `transmission`) VALUES
('digital-trends26', 1, 0, 0, 'live-off', 'youtube'),
('ecommerce26', 1, 0, 0, 'live-off', 'youtube');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `speakers`
--

CREATE TABLE IF NOT EXISTS `speakers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) NOT NULL DEFAULT 'ecommerce',
  `exposes` varchar(255) NOT NULL DEFAULT 'conference',
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_image` varchar(255) NOT NULL,
  `job` varchar(255) NOT NULL,
  `sm_twitter` varchar(255) DEFAULT NULL,
  `sm_linkedin` varchar(255) DEFAULT NULL,
  `sm_instagram` varchar(255) DEFAULT NULL,
  `sm_facebook` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `bio` text NOT NULL,
  `image_company` varchar(255) NOT NULL,
  `alt_image_company` varchar(255) NOT NULL,
  `time` varchar(255) DEFAULT NULL,
  `link_time` varchar(500) DEFAULT NULL,
  `orden` varchar(255) DEFAULT NULL,
  `day` varchar(1) DEFAULT NULL,
  `youtube` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `slug` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `status` enum('0','1') DEFAULT NULL,
  `meta_title` varchar(350) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `meta_description` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `meta_image` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `meta_twitter` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `speakers`
--

INSERT INTO `speakers` (`id`, `event`, `exposes`, `name`, `image`, `alt_image`, `job`, `sm_twitter`, `sm_linkedin`, `sm_instagram`, `sm_facebook`, `title`, `description`, `bio`, `image_company`, `alt_image_company`, `time`, `link_time`, `orden`, `day`, `youtube`, `slug`, `status`, `meta_title`, `meta_description`, `meta_image`, `meta_twitter`) VALUES
(1, 'ecommerce', 'conference', 'Michel Capuano', 'michelcapuano-agenda-old.png', 'Michel Capuano', 'Director de Marketing de FedEx', '', 'https://www.linkedin.com/in/michelcapuano', '', '', 'Logística Inteligente: Claves para optimizar la entrega en Ecommerce', 'En esta conferencia, se explorarán las principales tendencias que están redefiniendo las entregas, desde la optimización de la última milla hasta el uso de logística predictiva para anticipar la demanda y reducir demoras. También se profundirzará en estrategias para acelerar los envíos sin aumentar costos, asegurando eficiencia y competitividad. Conoce de primera mano cómo FedEx está revolucionando la entrega en E-commerce y qué estrategias puedes aplicar en tu negocio. ', 'Ejecutivo orientado a resultados con 20 años de amplia experiencia en marketing en multinacionales de bienes de consumo, retail/QSR, trabajando en transformación digital y comercio electrónico con metodologías ágiles.', 'michelcapuano-agenda-logo-old.png', 'FedEx', '11:15', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Michel+Capuano+-+EMMS+E-commerce+2025&iso=20250428T1115&p1=51&am=25', '10', '1', '', '', NULL, '', '', '', ''),
(2, 'ecommerce', 'conference', 'Manuel Caro', 'manuelcaro-agenda-old.png', 'Manuel Caro', 'Fundador y CEO de la Agencia Digital MDE Consulting Group', 'https://x.com/manuelcaro', 'https://www.linkedin.com/in/caromanuel/ ', 'https://www.instagram.com/caromanuelr/', ' https://web.facebook.com/manuel.caro.mde', 'Los 5 Hacks de IA que aumentan las ventas de tu E-commerce  ', 'La Inteligencia Artificial está redefiniendo el e-commerce, pasando de ser una herramienta de soporte a convertirse en el motor que impulsa las ventas. En esta conferencia descubrirás cinco hacks de IA que están transformando la manera en que las marcas atraen, convierten y fidelizan clientes. Exploraremos cómo la IA puede automatizar la optimización de campañas publicitarias, personalizar experiencias en tiempo real, ajustar precios dinámicamente según cada usuario y hasta predecir qué productos comprará un cliente antes de que él mismo lo sepa.', 'Manuel Caro es un experto en Inteligencia Artificial, Marketing Digital y Transformación Digital, con más de 30 años de experiencia en el sector y 20 años liderando agencias digitales. Ha desarrollado más de 650 proyectos de innovación y crecimiento digital para marcas como Unilever, Kimberly-Clark, Pfizer, Universal Music y Reckitt, entre muchas otras. Su liderazgo en la integración de la IA en estrategias de negocio y marketing le ha valido reconocimientos internacionales, incluyendo su nombramiento como Business Fellow de Perplexity AI, programa que forma líderes en la aplicación estratégica de la inteligencia artificial en los negocios. ', 'manuelcaro-agenda-logo-old.png', 'Agencia Digital MDE Consulting Group', '11:50', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Manuel+Caro+%7C+EMMS+E-commerce&iso=20250428T1150&p1=51&am=25', '20', '1', '', '', NULL, '', '', '', ''),
(3, 'ecommerce', 'conference', 'Stephanie Pi Herrera', 'stephanieherrera-agenda-old.png', 'Stephanie Pi Herrera', 'Woo Developer Advocate', '', '', '', '', 'Escalabilidad en eCommerce: Cómo crecer sin límites', 'En el mundo del eCommerce, el crecimiento no debería ser un obstáculo, sino una oportunidad. Sin embargo, muchas tiendas online se enfrentan a desafíos técnicos cuando su volumen de tráfico y pedidos aumenta. En esta conferencia, se desmitificarán las creencias más comunes sobre la escalabilidad, se explorará qué configuraciones permiten que un E-commerce crezca sin límites, se analizará el papel clave del hosting, se verán estrategias de rendimiento esenciales y se explorará cómo manejar altos volúmenes de pedidos con procesos en segundo plano y un checkout optimizado. ', 'Cuando no está enfocada en mejorar la experiencia de desarrollo en WooCommerce, Pi se puede encontrar escalando rocas y tomando el sol.', 'stephanieherrera-agenda-logo-old.png', 'WordPress', '12:25', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Stephanie+Pi+Herrera+%7C+EMMS+E-commerce&iso=20250428T1225&p1=51&am=25', '30', '1', '', '', NULL, '', '', '', ''),
(4, 'ecommerce', 'conference', 'María Díaz', 'mariadiaz-agenda-old.png', 'María Díaz', 'Marketing Manager de Doppler', '', 'https://www.linkedin.com/in/mariajudithdiaz/', '', '', 'Hackea Tu eCommerce: Tácticas de Onsite Marketing que generan conversiones', 'En esta conferencia, María Díaz, de Doppler, revelará tácticas clave de Onsite Marketing para maximizar conversiones y mejorar la experiencia del usuario en tiempo real. Aprenderás cómo personalizar mensajes, captar la atención de tus visitantes en los momentos clave y utilizar estrategias basadas en datos para aumentar las ventas sin necesidad de más tráfico. Además, descubrirás cómo implementar pop-ups inteligentes, banners dinámicos y automatizaciones avanzadas que guían al usuario hacia la compra.', 'María es Marketing Manager en Doppler, con más de 10 años de experiencia en marketing digital. Máster en Marketing Estratégico, especialista en inbound marketing, estrategias de contenido, email marketing y automatizaciones. Profesora en diversas escuelas de negocio y speaker en eventos de la industria.', 'mariadiaz-agenda-logo-old.png', 'Doppler', '13:00', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Mar%C3%ADa+D%C3%ADaz+%7C+EMMS+E-commercer&iso=20250428T13&p1=51&am=25', '40', '1', '', '', NULL, '', '', '', ''),
(5, 'ecommerce', 'conference', 'Jaime Piedraita ', 'jaimepiedraita-agenda-old.png', 'Jaime Piedraita ', 'Director General Expertos en Retail', '', 'https://www.linkedin.com/in/jaime-andr%C3%A9s-piedrahita-lopera-13949130/', '', '', 'Tendencias en Retail para Latinoamérica y España', 'En esta conferencia se explorará cómo la proximidad se ha convertido en un factor decisivo para los consumidores, impulsando formatos más accesibles, eficientes y conectados con las necesidades locales. También se abordará la creciente demanda por precios bajos, un desafío que obliga a las marcas a optimizar costos sin sacrificar la calidad ni la experiencia del cliente. A través de casos reales y estrategias innovadoras, se descubrirá cómo las grandes y pequeñas empresas están adaptando su modelo de negocio a este nuevo escenario competitivo.', 'Administrador de Negocios bilingüe, con especialización en Mercadeo y Tecnología, MBA y Maestría en Dirección Comercial y Marketing. Líder con amplia experiencia en la elaboración y ejecución de planes estratégicos de mercadeo, trade marketing y comerciales, generación de nuevos negocios e innovación de productos en compañías de consumo masivo.', 'jaimepiedraita-logo-agenda-old.png', 'Expertos en Retail', '11:15', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Jaime+Piedrahita+%7C+EMMS+E-commerce&iso=20250429T1115&p1=51&am=25', '10', '2', '', '', NULL, '', '', '', ''),
(6, 'ecommerce', 'conference', 'Julián Ocampo', 'julianocampo-agenda-old.png', 'Julián Ocampo', 'Fundador de la Escuela de UGC', '', '', 'https://www.instagram.com/julianforfun/', '', 'Publicidad con UGC: La Estrategia Secreta para Aumentar las Ventas', 'En esta conferencia, Julián Ocampo, revelará cómo el User Generated Content (UGC) se ha convertido en la estrategia secreta de las marcas más exitosas para aumentar ventas y generar confianza. Descubrirás cómo seleccionar, incentivar y escalar el UGC para potenciar el impacto de tus estrategias de marketing sin necesidad de grandes presupuestos en producción. A través de casos reales y tácticas aplicables, conocerás por qué el contenido generado por la comunidad es la clave para conectar con tu audiencia y hacer crecer tu marca.', 'Julián Ocampo es educador digital y fundador de la Escuela de UGC, reconocida como la primera academia para creadores de contenido en el mundo. Esta institución ofrece formación integral en creación de contenido para plataformas digitales, con el objetivo de capacitar a individuos interesados en desarrollar material auténtico y efectivo para diversas marcas y audiencias.  Actualmente, la escuela cuenta con más de 800 alumnos de países como Argentina, Colombia, Chile y Estados Unidos, y colabora con reconocidas empresas como Avon, Albago, Netflix y Paramount.', 'julianocampo-agenda-logo-old-v3.png', 'Escuela de UGC', '11:50', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Juli%C3%A1n+Ocampo+%7C+EMMS+E-commerce&iso=20250429T1150&p1=51&am=25', '20', '2', '', '', NULL, '', '', '', ''),
(7, 'ecommerce', 'conference', 'Mario Del Pozo Garrido', 'mariodelpozo-agenda-old.png', 'Mario Del Pozo Garrido', 'Content creator en Don Dominio', '', '', '', '', 'Dominio y SEO: Claves para posicionar tu Tienda Online desde el nombre', 'En esta conferencia, Mario Del Pozo, de Don Dominio, explicará cómo la elección de un dominio estratégico impacta directamente en el SEO y la visibilidad de tu tienda online. Aprenderás a seleccionar el nombre perfecto para tu negocio, optimizando palabras clave y extensiones que potencien tu posicionamiento en buscadores. ', 'Mario del Pozo Garrido es especialista en marketing digital y colaboraciones estratégicas en DonDominio, donde gestiona alianzas clave y crea contenido enfocado en dominios, SEO y presencia online. Además, es anfitrión del podcast PlanetaM, un espacio donde explora tendencias, estrategias y novedades del mundo digital con expertos de la industria.', 'mariodelpozo-agenda-logo-old-v2.png', 'Don Dominio', '12:25', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Conferencia+Mario+Del+Pozo+Garrido+%7C+EMMS+E-commerce&iso=20250429T1225&p1=51&am=25', '30', '2', '', '', NULL, '', '', '', ''),
(8, 'ecommerce', 'workshop', 'Pablo Moratinos', 'pablomoratinos-agenda-old.png', 'Pablo Moratinos', 'Growth Data Lead en Product Hackers', '', 'https://www.linkedin.com/in/pmoratinos/', '', '', 'Análisis de E-commerce en WordPress', 'En esta sesión, se abordarán los fundamentos de la instrumentación analítica a través de la capa de datos, mostrando cómo crear un dataLayer en plataformas como WordPress, WooCommerce y Easy Digital Downloads. También se cubrirá la integración con Google Tag Manager para gestionar y activar etiquetas de manera eficiente. Finalmente, se aprenderá a configurar y analizar funnels de conversión en GA4, proporcionando herramientas prácticas para mejorar el rendimiento y la toma de decisiones en sus E-commerce.', 'Pablo Moratinos es el responsable del equipo de Data & Experimentation en Product Hackers, una agencia líder en Growth Marketing. Fundador de la agencia de marketing 3ymedia Comunicación y codirector de la academia online 3ymedia School, ha logrado consolidar una sólida carrera en el mundo del marketing digital. Además, es embajador de marca de WordPress.com y autor del exitoso libro \"Negocios online. Data Driven Marketing\", publicado en 2022 por Anaya Multimedia, que se convirtió en un top ventas en su categoría en Amazon durante su primera semana de lanzamiento.', 'stephanieherrera-agenda-logo-old (1).png', 'WordPress', '15:00', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Pablo+Moratinos+%7C+EMMS+E-commerce+2025&iso=20250428T15&p1=51&am=40', '80', '1', '', '', NULL, '', '', '', ''),
(9, 'ecommerce', 'workshop', 'Laura Barreto ', 'laurabarreto-agenda-old.png', 'Laura Barreto ', 'Large Business & Strategic Partners Manager', '', 'https://www.linkedin.com/in/marialaurabrt/', '', '', 'Onsite Marketing: Tácticas Avanzadas para Aumentar las Ventas en tu E-commerce', 'Se explorarán técnicas avanzadas como la creación de pop-ups inteligentes y mensajes dinámicos que se activan en momentos clave del proceso de compra, así como la personalización de contenidos en tiempo real según el comportamiento del usuario. Además, se enseñará a segmentar a los visitantes para ofrecerles promociones y ofertas personalizadas, mejorar la efectividad de los formularios de captura y optimizar el checkout para reducir el abandono del carrito. ', '', 'laurabarreto-agenda-logo-old.png', 'Doppler', '15:45', 'https://www.timeanddate.com/worldclock/fixedtime.html?msg=Workshop+Laura+Barreto+%7C+EMMS+E-commerce+2025&iso=20250428T1545&p1=51&am=40', '90', '1', '', '', NULL, '', '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sponsors`
--

CREATE TABLE IF NOT EXISTS `sponsors` (
  `sponsor_id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_type` enum('SPONSOR','PREMIUM','STARTER') DEFAULT NULL,
  `name_company` varchar(255) NOT NULL,
  `logo_company` varchar(255) DEFAULT NULL,
  `alt_logo_company` varchar(255) NOT NULL,
  `link_site` varchar(255) DEFAULT NULL,
  `priority_home` varchar(255) DEFAULT NULL,
  `conference_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_card` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `visible_card` tinyint(1) NOT NULL DEFAULT 0,
  `priority_card` varchar(255) DEFAULT NULL,
  `image_landing` varchar(255) DEFAULT NULL,
  `alt_image_landing` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `image_youtube` varchar(255) DEFAULT NULL,
  `alt_image_youtube` varchar(255) DEFAULT NULL,
  `title_magnet` text DEFAULT NULL,
  `description_magnet` text DEFAULT NULL,
  `link_magnet` varchar(255) DEFAULT NULL,
  `title_promo_company` text DEFAULT NULL,
  `description_promo_company` text DEFAULT NULL,
  `link_promo_company` varchar(255) DEFAULT NULL,
  `status` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`sponsor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sponsors`
--

INSERT INTO `sponsors` (`sponsor_id`, `sponsor_type`, `name_company`, `logo_company`, `alt_logo_company`, `link_site`, `priority_home`, `conference_name`, `title`, `description`, `description_card`, `slug`, `visible_card`, `priority_card`, `image_landing`, `alt_image_landing`, `youtube`, `image_youtube`, `alt_image_youtube`, `title_magnet`, `description_magnet`, `link_magnet`, `title_promo_company`, `description_promo_company`, `link_promo_company`, `status`) VALUES
(1, 'STARTER', 'China Rodriguez', '20230317T122856277Z710679.png', 'China Rodriguez', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(2, 'STARTER', 'Ultravioleta', '20230317T122926188Z978476.png', 'Ultravioleta', NULL, '20', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(3, 'STARTER', 'Infonegocios', '20230317T122954107Z502796.png', 'Infonegocios', NULL, '30', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(6, 'STARTER', 'Luis Maram', '20230329T111618059Z736116.png', 'Luis Maram', NULL, '40', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(7, 'STARTER', 'Mkt digital experience', '20250331T213620361Z458542.png', 'Marketing digital experience', NULL, '50', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(8, 'STARTER', 'Club de las Emprndedoras', '20230329T111710257Z090204.png', 'Club de las Emprndedoras', NULL, '60', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(9, 'STARTER', 'Ignacio Santiago', '20230329T111731344Z455405.png', 'Ignacio Santiago', NULL, '70', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(10, 'STARTER', 'Epico', '20230329T111747496Z898195.png', 'Epico', NULL, '80', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(11, 'STARTER', 'Micaela Sabja', '20230331T084320642Z732877.png', 'Micaela Sabja', NULL, '90', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(12, 'STARTER', 'MIMEC', '20230331T084414063Z501057.png', 'MIMEC', NULL, '100', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(13, 'STARTER', 'Cámara Argentina de Fintech', '20230331T084443405Z219766.png', 'Cámara Argentina de Fintech', NULL, '110', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(14, 'STARTER', 'Cámara dee Comercio de Córdoba', '20230331T084514301Z535073.png', 'Cámara dee Comercio de Córdoba', NULL, '120', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(15, 'STARTER', 'Growby', '20230331T084538115Z587466.png', 'Growby', NULL, '130', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(16, 'STARTER', 'Del querer al hacer', '20230331T084559291Z231911.png', 'Del querer al hacer', NULL, '140', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(17, 'STARTER', 'IT Ahora', '20230331T084624476Z612615.png', 'IT Ahora', NULL, '150', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(18, 'STARTER', 'Emprendedores News', '20230331T084738367Z487749.png', 'Emprendedores News', NULL, '160', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(19, 'STARTER', 'Grandes Pymes', '20230331T085247285Z879670.png', 'Grandes Pymes', NULL, '170', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(20, 'STARTER', 'Mundo Contact', '20230331T085308334Z486520.png', 'Mundo Contact', NULL, '195', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(21, 'STARTER', 'Marketing al Día', '20230331T085328868Z516303.png', 'Marketing al Día', NULL, '200', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(22, 'STARTER', 'Bulb', '20230331T085352836Z665289.png', 'Bulb', NULL, '210', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(23, 'STARTER', 'Moni en la Web', '20230331T085411802Z765799.png', 'Moni en la Web', NULL, '220', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(24, 'STARTER', 'Mi Pyme no Para', '20230331T085442178Z436410.png', 'Mi Pyme no Para', NULL, '230', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(25, 'STARTER', 'Entre Emprenedores Workshop', '20230331T085507776Z867777.png', 'Entre Emprenedores Workshop', NULL, '240', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(26, 'STARTER', 'Disruptivo TV', '20230331T085528208Z709702.png', 'Disruptivo TV', NULL, '245', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(27, 'STARTER', 'Caro Siri', '20230331T085719765Z754128.png', 'Caro Siri', NULL, '250', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(28, 'STARTER', 'SED Emprendedor', '20230331T085736043Z513096.png', 'SED Emprendedor', NULL, '260', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(29, 'STARTER', 'AD Media Rock', '20230331T085811273Z535760.png', 'AD Media Rock', NULL, '270', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(30, 'STARTER', 'AMDAR', '20230331T085823850Z432089.png', 'AMDAR', NULL, '280', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(31, 'STARTER', 'We Connect', '20230331T085842074Z226329.png', 'We Connect', NULL, '300', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(32, 'STARTER', 'Flor Lamas', '20230331T085855731Z953447.png', 'Flor Lamas', NULL, '310', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(33, 'STARTER', 'Somos Branders OK', '20230331T085912200Z517799.png', 'Somos Branders OK', NULL, '320', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(34, 'STARTER', 'Power Hub', '20230331T090009023Z036658.png', 'Power Hub', NULL, '330', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(35, 'STARTER', 'Mamita Power', '20230331T090023070Z980079.png', 'Mamita Power', NULL, '340', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(52, 'STARTER', 'EUDE', '20230403T082307950Z115064.png', 'EUDE', NULL, '150', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(53, 'STARTER', 'Círculo Empresarial', '20230403T082454058Z907380.png', 'Círculo Empresarial', NULL, '160', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(54, 'STARTER', 'CEVEC', '20230403T082528464Z053843.png', 'CEVEC', NULL, '170', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(55, 'STARTER', 'El PUblicista', '20230403T082602950Z164011.png', 'El PUblicista', NULL, '170', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(56, 'STARTER', 'Soy Emprendedora', '20230403T082639158Z504938.png', 'Soy Emprendedora', NULL, '180', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(57, 'STARTER', 'Mujeres en Tecnología', '20230403T082713061Z595231.png', 'Mujeres en Tecnología', NULL, '190', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(58, 'STARTER', 'el sponsor de Mati', '20250331T164442405Z214672.png', 'Convierte Agency', NULL, '12', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(59, 'SPONSOR', 'el sponsor de Mati', '20250331T164556452Z727892.png', 'Convierte Agency', '223', '23', 'Las 4 fases para aumentar tus ventas', '23', '232323', '23', 'Convierte Agency', 1, '23232', '20250331T164556452Z848988.png', 'Don Dominio ', 'dfgdfgdfgdfg', '20250331T164556452Z735187.png', 'Cómo crear un nombre de marca memorable', '23232', '2323', 'https://convierte.agency/checklist-emms/', 'Don Dominio', '232323', 'https://www.eude.com.ar/', '0'),
(60, 'SPONSOR', 'Don Dominio', '20250331T211648760Z590241.png', 'Don Dominio', 'https://www.dondominio.com/es', '3', '', '', '', '', '', 0, '', NULL, '', '', NULL, '', '', '', '', '', '', '', '1'),
(61, 'SPONSOR', 'Easycommerce', '20250331T211801349Z463399.png', 'Easycommerce', 'https://www.easycommerce.tech/', '2', '', '', '', '', '', 0, '', NULL, '', '', NULL, '', '', '', '', '', '', '', '1'),
(62, 'SPONSOR', 'Wordpress', '20250331T212431774Z724635.png', 'Wordpress', ' https://wordpress.com/es/wordcamp/', '1', '', '', '', '', '', 0, '', NULL, '', '', NULL, '', '', '', '', '', '', '', '1'),
(63, 'STARTER', 'Educacion IT', '20250331T213339027Z708225.png', 'Educacion IT', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(64, 'STARTER', 'China Rodríguez', '20250331T213427509Z521841.png', 'China Rodríguez', NULL, '2', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(65, 'STARTER', 'Info Negocios', '20250331T213532671Z424921.png', 'Info Negocios', NULL, '4', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(66, 'STARTER', 'Consejo de la comunicación', '20250331T213812721Z494559.png', 'Consejo de la comunicación', NULL, '6', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(67, 'STARTER', 'Sofi Alicio', '20250331T213914571Z053848.png', 'Sofia Alicio', NULL, '8', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(68, 'STARTER', 'Mujeres que emprenden', '20250331T214100447Z863493.png', 'Mujeres que emprenden', NULL, '10', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(69, 'STARTER', 'Digitalizadas ', '20250331T214158863Z960496.png', 'Digitalizadas ', NULL, '12', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(70, 'STARTER', 'Rampa Publicidad', '20250331T214246716Z250601.png', 'Rampa Publicidad', NULL, '13', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(71, 'STARTER', 'Interlat', '20250331T214348921Z710356.png', 'Interlat', NULL, '14', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(72, 'STARTER', 'Envio Pack', '20250331T214446432Z325562.png', 'Envio Pack', NULL, '15', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(73, 'STARTER', 'Capacitate EC', '20250331T214526182Z506457.png', 'Capacitate EC', NULL, '16', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(74, 'STARTER', 'Ecodiem', '20250331T214607057Z392553.png', 'Ecodiem', NULL, '18', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(75, 'STARTER', 'Publicitarias', '20250331T214659461Z562888.png', 'Publicitarias', NULL, '20', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(76, 'STARTER', 'Fecoba', '20250331T214738653Z817580.png', 'Fecoba', NULL, '22', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(77, 'STARTER', 'Voces Vitales', '20250331T214826130Z265128.png', 'Voces Vitales', NULL, '23', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(78, 'STARTER', 'Tanita Miguel ', '20250331T214911635Z887720.png', 'Tanita Miguel ', NULL, '24', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(79, 'STARTER', 'Zeke', '20250331T214951497Z776945.png', 'Zeke', NULL, '25', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(80, 'STARTER', 'Ladies Brunch', '20250331T215033073Z876383.png', 'Ladies Brunch', NULL, '26', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(81, 'STARTER', 'Mompreneurs', '20250331T215216495Z832963.png', 'Mompreneurs', NULL, '28', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(82, 'STARTER', 'Marketnews Perú', '20250331T215247943Z697448.png', 'Marketnews Perú', NULL, '30', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(83, 'STARTER', 'Partners Academy', '20250331T215327043Z669635.png', 'Partners Academy', NULL, '32', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(84, 'STARTER', 'Pymenoticias', '20250331T215417750Z300202.png', 'Pymenoticias', NULL, '34', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(85, 'STARTER', 'Esmadi ', '20250331T215449414Z042692.png', 'Esmadi ', NULL, '36', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(86, 'STARTER', 'Impulsate', '20250331T215550346Z916029.png', 'Impulsate', NULL, '38', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(87, 'STARTER', 'Circulo Empresarial', '20250331T215615755Z238628.png', 'Circulo Empresarial', NULL, '40', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(88, 'STARTER', 'Emprende con Juanma', '20250331T215644006Z003161.png', 'Emprende con Juanma', NULL, '42', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(89, 'STARTER', 'Rucula', '20250331T215711817Z622863.png', 'Rucula', NULL, '42', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(90, 'STARTER', 'Materia Biz', '20250331T215739316Z613303.png', 'Materia Biz', NULL, '46', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(91, 'STARTER', 'Micaela Sabja ', '20250331T215920302Z404787.png', 'Micaela Sabja ', NULL, '48', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(92, 'PREMIUM', 'Angie Sammartino', '20250401T150337585Z495097.png', 'Angie Sammartino', 'https://angiesammartino.com.ar/', '10', '', '', '', '', '', 0, '', NULL, '', '', NULL, '', '', '', '', '', '', '', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sponsorsdt24`
--

CREATE TABLE IF NOT EXISTS `sponsorsdt24` (
  `sponsor_id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_type` enum('SPONSOR','PREMIUM','STARTER') DEFAULT NULL,
  `name_company` varchar(255) NOT NULL,
  `logo_company` varchar(255) DEFAULT NULL,
  `alt_logo_company` varchar(255) NOT NULL,
  `link_site` varchar(255) DEFAULT NULL,
  `priority_home` varchar(255) DEFAULT NULL,
  `conference_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_card` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `visible_card` tinyint(1) NOT NULL DEFAULT 0,
  `priority_card` varchar(255) DEFAULT NULL,
  `image_landing` varchar(255) DEFAULT NULL,
  `alt_image_landing` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `image_youtube` varchar(255) DEFAULT NULL,
  `alt_image_youtube` varchar(255) DEFAULT NULL,
  `title_magnet` text DEFAULT NULL,
  `description_magnet` text DEFAULT NULL,
  `link_magnet` varchar(255) DEFAULT NULL,
  `title_promo_company` text DEFAULT NULL,
  `description_promo_company` text DEFAULT NULL,
  `link_promo_company` varchar(255) DEFAULT NULL,
  `status` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`sponsor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stripe_customers`
--

CREATE TABLE IF NOT EXISTS `stripe_customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_country` varchar(255) NOT NULL,
  `customer_tax` varchar(255) NOT NULL,
  `payment_status` varchar(50) NOT NULL,
  `coupon_id` varchar(255) DEFAULT NULL,
  `coupon_name` varchar(255) DEFAULT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_phase` varchar(255) NOT NULL,
  `ticket_name` varchar(255) NOT NULL,
  `ticket_price_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscriptions_doppler`
--

CREATE TABLE IF NOT EXISTS `subscriptions_doppler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(250) NOT NULL,
  `list` varchar(50) NOT NULL,
  `register` varchar(50) NOT NULL,
  `form_id` varchar(50) NOT NULL,
  `firstname` varchar(150) DEFAULT NULL,
  `lastname` varchar(150) DEFAULT NULL,
  `phone` varchar(300) DEFAULT NULL,
  `country` varchar(150) DEFAULT NULL,
  `company` varchar(300) DEFAULT NULL,
  `jobPosition` varchar(150) DEFAULT NULL,
  `ecommerce` tinyint(1) NOT NULL DEFAULT 1,
  `digital-trends` tinyint(1) NOT NULL DEFAULT 0,
  `ip` varchar(150) NOT NULL,
  `country_ip` varchar(150) NOT NULL,
  `privacy` tinyint(1) NOT NULL,
  `promotions` tinyint(1) DEFAULT NULL,
  `source_utm` text DEFAULT NULL,
  `medium_utm` text DEFAULT NULL,
  `campaign_utm` text DEFAULT NULL,
  `content_utm` text DEFAULT NULL,
  `term_utm` text DEFAULT NULL,
  `emms_ref` text DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `emailPlatform` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1972 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscription_doppler_list_errors`
--

CREATE TABLE IF NOT EXISTS `subscription_doppler_list_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `list` varchar(255) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `error_code` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
