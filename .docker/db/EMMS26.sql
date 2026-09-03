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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registered_email` (`email`)
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
  `image_modal` varchar(255) DEFAULT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `speakers`
--

INSERT INTO `speakers` (`id`, `event`, `exposes`, `name`, `image`, `image_modal`, `alt_image`, `job`, `sm_twitter`, `sm_linkedin`, `sm_instagram`, `sm_facebook`, `title`, `description`, `bio`, `image_company`, `alt_image_company`, `time`, `link_time`, `orden`, `day`, `youtube`, `slug`, `status`, `meta_title`, `meta_description`, `meta_image`, `meta_twitter`, `created_at`, `updated_at`) VALUES
(2, 'digital-trends', 'conference', '🇨🇴 Manuela Villegas', 'Manuela_Villegas.png', NULL, 'Manuela Villegas', 'CEO & Founder', '', 'https://www.linkedin.com/in/manuelavillegas/', '', '', 'Lead Growth: Storytelling para Marcas Personales', 'Convierte la autenticidad y el Storytelling en herramientas para construir una marca personal que conecte, genere confianza y potencie tu crecimiento en Redes Sociales.', 'Manuela Villegas es Fundadora y CEO de Sí Señor Agencia, agencia de Growth Marketing con operaciones en Colombia, Estados Unidos y Europa. Cuenta con más de 15 años de experiencia en estrategias digitales, Growth, Inbound, analítica, automatización, datos y experimentación. Además, es Top Voice de LinkedIn, donde comparte contenido sobre Marketing, negocios y emprendimiento.', 'Si_senor.png', 'Sí Señor Agencia', '11:15', 'http://evt.to/gmaogodsw', '10', '1', '', 'manuela-villegas', NULL, '', '', '', '', NULL, NULL),
(3, 'digital-trends', 'conference', '🇲🇽 Alfonso Basulto', 'Alfonso_Basulto.png', NULL, 'Alfonso Basulto', 'CEO & Founder', '', 'https://www.linkedin.com/in/alfonsobasulto/', '', '', 'Creatividad en la era de la IA: ideas que conectan', 'Explora cómo potenciar la creatividad con IA para crear ideas más relevantes, conectar con tu audiencia y transformar la forma de hacer Marketing.', 'Alfonso Basulto es especialista en Marketing Digital, comunicación y creatividad, con más de 10 años de experiencia en comunicación institucional y una amplia trayectoria como emprendedor. Es CEO de Kino Laboratorio Creativo, agencia y academia de Marketing Digital, y desde 2015 se desempeña como académico, asesor y consultor, capacitando a empresas y profesionales.', 'Kino.png', 'Kino Laboratorio Creativo', '11:15', 'http://evt.to/gmaooiauw', '060', '2', '', 'alfonso-basulto', NULL, '', '', '', '', NULL, NULL),
(4, 'digital-trends', 'conference', '🇲🇽 Philippe Castellvi', 'Phill2.png', NULL, 'Philippe Alexandre Castellvi', 'Nuevos Negocios y Partnerships de TikTok Shop México', '', 'https://www.linkedin.com/in/philippecastellvi/', '', '', 'Descubre cómo escalar tus ventas con TikTok Shop', 'Descubre cómo convertir TikTok y su marketplace en motores de crecimiento, con estrategias de LIVE Commerce, tiendas en perfiles y creadores afiliados para impulsar tus ventas de forma rentable.', 'Philippe Castellvi cuenta con experiencia en estrategia, expansión y operaciones. Desde 2023 forma parte de TikTok, donde participó en el lanzamiento de TikTok Shop en Reino Unido y España. Actualmente lidera el equipo de Nuevos Negocios y Partnerships de TikTok Shop México.', 'Tik_tok_Logo.png', 'TikTok', '11:50', 'http://evt.to/gmaooieow', '20', '1', '', 'philippe-castellvi', NULL, '', '', '', '', NULL, NULL),
(5, 'digital-trends', 'conference', '🇦🇷 Laura Barreto', 'Laura_Barreto.png', NULL, 'Laura Barreto', 'Large Business & Strategic Partners Manager', '', 'https://www.linkedin.com/in/marialaurabrt/', '', '', 'Cercanía a escala: qué automatizar y qué no cuando la IA atiende a tus clientes', 'Aprende cómo un agente de IA puede sostener miles de Conversaciones sin sonar a robot. Conoce qué automatizar, dónde poner límites y cómo liberar a tu equipo de las tareas repetitivas.', 'Laura Barreto lidera Revenue Operations y AI comercial en Doppler. Con más de una década de experiencia en marketing y ventas B2B, hoy desarrolla soluciones de IA para el área comercial, desde agentes conversacionales y auditoría de llamadas hasta modelos de forecast y atribución. Es miembro de la Comisión de IA de la DMA y escribe sobre agentes autónomos aplicados al marketing. Su foco: la IA que ya funciona en producción.', 'Doppler.png', 'Doppler', '11:50', 'http://evt.to/gmaooigmw', '070', '2', '', 'laura-barreto', NULL, '', '', '', '', NULL, NULL),
(6, 'digital-trends', 'conference', '🇨🇴 Gustavo Bustos', 'Gustavo_Bustos_Hostinger.png', NULL, 'Gustavo Bustos', 'Country Manager LATAM', '', 'https://www.linkedin.com/in/gustavobustosmo/', '', '', 'Solopreneurs: cómo la IA transforma ideas en negocios', '"¿Alguna vez sentiste que necesitabas un equipo entero para tener una presencia digital profesional? Diseño web, redacción de contenidos, progrmación de aplicaciones, SEO… tareas que antes requerían contratar especialistas hoy pueden ser realizadas por una sola persona ambiciosa que cuente con las herramientas adecuadas.\r\n\r\nEn esta charla vas a descubrir cómo la inteligencia artificial está cambiando las reglas del juego para emprendedores, freelancers y negocios unipersonales: menos barreras, más autonomía, y resultados reales sin depender de nadie más.\r\n\r\nVivimos un cambio de paradigma pasando de «Necesito contratar a alguien» a «Puedo hacer esto yo mismo, hoy mismo»."', 'Gustavo Bustos es Country Manager LATAM de Hostinger, donde impulsa el crecimiento de una de las principales plataformas de presencia online y desarrollo digital. Su trabajo se enfoca en acercar herramientas de creación de sitios, E-commerce e IA a personas y empresas, y en analizar las tendencias que están transformando el emprendimiento y los negocios digitales en Latinoamérica.', 'Hostinger.png', 'Hostinger', '12:25', 'http://evt.to/gmaooighw', '30', '1', '', 'gustavo-bustos', NULL, '', '', '', '', NULL, NULL),
(7, 'digital-trends', 'conference', '🇦🇷 Julian Ocampo', 'Julian_Ocampo.png', NULL, 'Julian Ocampo', 'Director en Escuela de UGC', '', 'https://www.linkedin.com/in/julianforfun/', '', '', 'UGC que vende: cómo convertir contenido en resultados', 'Descubre por qué el contenido creado con celular puede convertir hasta 7 veces más que la publicidad tradicional.', 'Julián Ocampo es empresario argentino y fundador de PLAY FOR FUN, tienda de videojuegos que se convirtió en la número uno del país gracias a sus estrategias de marketing digital. Actualmente capacita a emprendedores, PyMEs y empresas de Latinoamérica para desarrollar sus marcas en internet. Además, fundó la primera escuela de UGC del mundo, donde forma creadores de contenido y los conecta con marcas y agencias. Cuenta con más de 1.500 alumnos de todo el mundo.', 'Escuela_UGC.png', 'Escuela UGC', '12:25', 'http://evt.to/gmaooiuaw', '080', '2', '', 'julian-ocampo', NULL, '', '', '', '', NULL, NULL),
(8, 'digital-trends', 'conference', '🇨🇴 Edwin Zacipa', 'Edwin Zácipa.png', NULL, 'Edwin Zacipa', 'CEO Latam Fintech Hub', '', 'https://www.linkedin.com/in/edwinzacipa/', '', '', 'Growth en Fintech: estrategias para crecer en LATAM ', 'Analiza las estrategias de Growth que están ayudando a Fintechs y Neobancos a escalar, diferenciarse y ganar terreno en la industria financiera de LATAM.', 'Edwin Zacipa es Administrador de Negocios Internacionales y Executive MBA por INALDE Business School. Emprendedor Fintech y especialista en Inteligencia de Negocios, fue cofundador y director ejecutivo de la Asociación de Fintech de Colombia y ha trabajado en Open Finance, asesorando a entidades financieras, retailers, gremios y Fintechs. También es docente universitario y referente del ecosistema Fintech latinoamericano.', 'latam_fintech_hub.png', 'Latam Fintech Hub', '13:00', 'http://evt.to/gmaooiiew', '40', '1', '', 'edwin-zacipa', NULL, '', '', '', '', NULL, NULL),
(9, 'digital-trends', 'conference', '🇲🇽 Andrés Nájera', 'Andres_Nájera.png', NULL, 'Andrés Nájera', 'CEO & Founder', '', 'https://www.linkedin.com/in/andresnajerap/', '', '', 'Crecer 30% en 90 días, sin capital extra ', 'Potencia tu negocio aprovechando al máximo los recursos que ya tienes, aplicando una fórmula de hipercrecimiento y creando un sistema de ventas que impulse tus resultados.', 'Andrés Nájera es CEO de Revu, donde ha ayudado a más de 165 empresas B2B y startups a escalar sus ventas, generando más de USD 55 millones en resultados. Fue Director de Ventas y primer empleado comercial de Jeeves, fintech que alcanzó una valuación de USD 2.1 mil millones. También es mentor de aceleradoras y speaker internacional, con más de 2.500 asistentes.', 'Andres_Najera_Logo.png', 'REVU', '13:35', 'http://evt.to/gmaooioaw', '090', '2', '', 'andres-najera', NULL, '', '', '', '', NULL, NULL),
(10, 'digital-trends', 'conference', '🇦🇷 Carolina Dubiansky', 'Carolina Dubiansky.png', NULL, 'Carolina Dubiansky', ' CEO & Founder', '', 'https://www.linkedin.com/in/carodubi/', '', '', 'Hackeando el algoritmo de Meta con IA', 'Descubre cómo funciona Andromeda, el nuevo algoritmo de Meta, y aprende a crear una estrategia de anuncios diseñada para trabajar a su favor. Conoce qué necesita el algoritmo para optimizar tus campañas y cómo aplicar IA para potenciar tus resultados.', 'Carolina Dubiansky es Fundadora y CEO de Giver Solutions, agencia de marketing de performance especializada en e-commerce. Además, es creadora de contenido detrás de @carodubi, donde comparte estrategias de escalado basadas en datos con una comunidad de más de 220.000 dueños de negocios.', 'giver_solutions.png', 'Giver Solutions', '14:10', '100', '100', '2', '', 'caro-dubiansky ', NULL, '', '', '', '', NULL, NULL),
(11, 'digital-trends', 'conference', '🇨🇱 Lolo Álvarez Díaz', 'Loreto _Lolo_ Álvarez Díaz.png', NULL, 'Lolo Álvarez Díaz', 'Jefe de Comunicaciones Corporativas', '', 'https://www.linkedin.com/in/loreto-lolo-alvarez/', '', '', 'Más allá de los Arcos: viviendo el propósito con cada historia', 'Descubre cómo la comunicación estratégica puede convertir el propósito de una organización en un relato que conecte. A partir de la experiencia de Arcos Dorados, conoce cómo las historias reales pueden darle voz y alma a una marca.', 'Lolo, magíster en Comunicación Estratégica y en Filosofía, Economía y Política, con 15 años de experiencia en estrategias de reputación y advocacy. Su trabajo combina visión de negocio con la creación de vínculos genuinos entre marcas y comunidades, con la autenticidad y la diversidad como pilares. También se desempeña como docente universitario.', 'Arcos_dorados.png', 'Arcos Dorados', '15:20', 'http://evt.to/gmaooidgw', '50', '1', '', 'lolo-alvarez', NULL, '', '', '', '', NULL, NULL),
(12, 'digital-trends', 'conference', '🇲🇽 Alex Santana', 'Alex_Santana_Amazon.png', NULL, 'Alex Santana', 'Senior Investor Manager en AWS | Cofundador de México Tech Week', '', 'https://www.linkedin.com/in/alexsantanag/', '', '', 'Herramientas de IA y Cloud para startups', 'Explora las principales herramientas de IA y Cloud que pueden ayudar a las startups a aprovechar mejor la tecnología y potenciar sus negocios.', 'Alex Santana es Senior Investor Manager en Amazon Web Services y cofundador de México Tech Week.', 'aws.png', 'Amazon', '15:55', 'http://evt.to/gmaooisaw', '110', '2', '', 'alex-santana', NULL, '', '', '', '', NULL, NULL),
(13, 'digital-trends', 'workshop', '🇦🇷 Daniel Dron', 'Daniel_Dron (1).png', NULL, 'Daniel Dron', 'CEO, Authority for Growth™️', '', 'https://www.linkedin.com/in/danieldron/', '', '', 'Sin Marca Personal no hay Conversión', 'Convierte la reputación del founder en confianza, demanda y crecimiento. Aprende a usar Personal Branding, Thought Leadership, Social Selling e IA para transformar tu experiencia en una ventaja competitiva.', 'Daniel Dron es Executive Coach internacional, estratega de marca personal y fundador de Zohara.io, plataforma de IA aplicada al liderazgo y la cultura organizacional. Es creador de Authority for Growth™, metodología que combina reputación, IA y capital humano para convertir líderes en activos visibles que generan crecimiento y atraen talento. Con más de 20 años de trayectoria y presencia en 12 países, también es Líder de Innovación en AMEDIRH y fundador de GeniAll.', 'Daniel_Dron.png', 'Geniall', '11:00', 'http://evt.to/gmaooomsw', '120', '3', '', 'daniel-dron', NULL, '', '', '', '', NULL, NULL),
(14, 'digital-trends', 'workshop', '🇦🇷 Agustina Miranda', 'Agustina_Miranda.png', NULL, 'Agustina Miranda', 'Customer Success Representative', '', 'https://www.linkedin.com/in/agustina-miranda/', '', '', 'Tu primer Agente IA en 40 minutos', 'Crea y entrena, paso a paso, tu primer Agente IA para que conozca tu negocio, respete tu tono de comunicación e interactúe con tus clientes de forma automatizada.', 'Agustina Miranda forma parte del equipo de Customer Success de Doppler, donde acompaña a clientes B2B en el uso estratégico de la plataforma. Cuenta con experiencia en Customer Experience, Email Marketing, entregabilidad y automatizaciones, y combina su formación en Economía y Gestión con conocimientos de tecnología y programación', 'Doppler.png', 'Doppler', '11:40', 'http://evt.to/gmaoooeuw', '130', '3', '', 'agustina-miranda', NULL, '', '', '', '', NULL, NULL),
(15, 'digital-trends', 'workshop', '🇻🇪 Gian - El Rapero Marketero', 'Gian_El_Rapero_Marketero.png', NULL, 'Gian - El Rapero Marketero', 'Founder Botlivery', '', 'https://www.linkedin.com/in/elraperomarketero/', '', '', 'IA para hacer crecer tu marca', 'Impulsa el crecimiento de tu marca con IA, descubre cómo hacer crecer tu comunidad y aplica el sistema de El Rapero Marketero para duplicarla en solo 30 días.', 'Jesús Giangregorio, conocido como El Rapero Marketero, es especialista en Inteligencia Artificial y fundador de Botlivery. Es creador del Método SWITCH, que combina IA, marketing y creatividad para convertir la tecnología en una herramienta de generación de leads y ventas. Como conferencista internacional, lleva estrategias prácticas de IA a escenarios de Latinoamérica y Estados Unidos.', 'El_rapero_marketero.png', 'El rapero marketero', '12:20', 'http://evt.to/gmaoohmhw', '140', '3', '', 'rapero-marketero', NULL, '', '', '', '', NULL, NULL),
(16, 'digital-trends', 'conference', '🇵🇪 Candy Risco', 'Candy Risco.png', NULL, 'Candy Risco', 'Content Marketer, Canvassador y Cofounder Club Canva Perú', '', 'https://www.linkedin.com/in/candyrisco/', '', '', 'Diseña con Canva + IA y potencia tu creatividad', 'Combina Canva e inteligencia artificial para crear diseños más rápido, potenciar tu creatividad y convertir tus ideas en proyectos visuales con mayor impacto.', 'Candy Risco es especialista en contenidos digitales, actualmente Content Marketer en Chazki, embajadora de Canva y cofundadora de Club Canva Perú, la primera comunidad de Canva en el país.', 'canva.png', 'Canva', '13:35', 'http://evt.to/gmaoohagw', '48', '1', '', 'candy-risco', NULL, '', '', '', '', NULL, NULL),
(17, 'digital-trends', 'workshop', '🇦🇷 Benjamin Aranciaga', 'Benjamin_Aranciaga_2.png', NULL, 'Benjamin Aranciaga', 'Brand Manager', '', 'https://www.linkedin.com/in/benjam%C3%ADn-aranciaga-653b5023/', '', '', 'Del dato a la decisión: demo en vivo de Master Métrics', 'Conecta tus datos y campañas con Master Metrics para automatizar tareas, centralizar información y obtener recomendaciones que te ayuden a tomar mejores decisiones.', 'Benjamin Aranciaga es Brand Manager en Master Metrics, donde lidera la comunicación de marca, las relaciones con partners y la generación de contenidos para los distintos canales de la empresa.', 'master_metric.png', 'Master Metrics', '13:40', 'http://evt.to/gmaoohaow', '160', '3', '', 'benjamin-aranciaga', NULL, '', '', '', '', NULL, NULL),
(18, 'digital-trends', 'workshop', '🇦🇷 Mónica Franco', 'Monica Franco.png', NULL, 'Mónica Franco', 'Acquisition & Performance Marketing Lead', '', 'https://www.linkedin.com/in/m%C3%B3nica-franco/', '', '', 'De la planificación a la optimización: las decisiones que hacen crecer tus campañas', 'Aprende a convertir los objetivos y números de tu negocio en una Estrategia de Publicidad efectiva, desde la planificación y el lanzamiento hasta la prueba, medición y optimización de tus Campañas.', 'Mónica Franco es Acquisition & Performance Marketing Lead, especializada en desarrollar estrategias de Paid Media, Adquisición y Performance enfocadas en generar ventas y Leads. Cuenta con experiencia en publicidad Digital, E-commerce, Automatización y Optimización de Campañas.', 'moni_en_la_web.png', 'Moni en la web', '14:20', 'http://evt.to/gmaohgaew', '200', '3', '', 'monica-franco', NULL, '', '', '', '', NULL, NULL),
(19, 'digital-trends', 'debate', 'EROS MARIOTTI - ALBERTO MUSALI - MARTIN BEAS ', 'Mesa_de_debate.png', NULL, 'Panel Startups', '🇦🇷 Director de Crecimiento, Rentennials · 🇲🇽 Co-Founder, Wonder Brands · 🇵🇪 CEO & Founder, GetLavado', '', '', '', '', 'Construir para crecer: los desafíos reales de escalar una startup en Latinoamérica', '¿Qué hace falta para convertir una buena idea en un negocio capaz de crecer y competir en mercados cada vez más exigentes? En este panel, líderes de startups de Argentina, Perú y México compartirán sus experiencias, desafíos y aprendizajes sobre crecimiento, innovación, adquisición de clientes y expansión en Latinoamérica. Una conversación sobre lo que realmente implica construir y escalar una startup en la región.', 'Eros Mariotti es CGO de Rentennials, donde se especializa en crecimiento y contribuye a impulsar la expansión y el desarrollo de la compañía en Latinoamérica.\r\n\r\nAlberto Mussali es cofundador de Wonderbrands, una casa de marcas de consumo masivo con base en México. Desde la dirección general lidera una compañía que desarrolla y escala productos propios en categorías como hogar, electrodomésticos, deportes y equipaje.\r\n\r\nMartín Beas Núñez es Founder de GetLavado y actualmente General Manager LATAM de Laundryheap, donde lidera su expansión en Perú, Colombia y México. ', 'northvalley2.png', 'Northvalley', '14:45', 'http://evt.to/gmegaooiw', '120', '2', '', '', NULL, 'Startups', '', 'northvalley2.png', '', NULL, NULL);

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
(12, 'STARTER', 'MIMEC', '20230331T084414063Z501685.png', 'MIMEC', NULL, '100', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
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
(32, 'STARTER', 'Flor Lamas', '20230331T085855731344Z953447.png', 'Flor Lamas', NULL, '310', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
(33, 'STARTER', 'Somos Branders OK', '20230331T085912200Z517799.png', 'Somos Branders OK', NULL, '320', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(34, 'STARTER', 'Power Hub', '20230331T090009023Z036658.png', 'Power Hub', NULL, '330', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(35, 'STARTER', 'Mamita Power', '20230331T090023070Z980079.png', 'Mamita Power', NULL, '340', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(52, 'STARTER', 'EUDE', '20230403T082307950Z736116.png', 'EUDE', NULL, '150', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
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
(79, 'STARTER', 'Zeke', '20250331T214951576Z776945.png', 'Zeke', NULL, '25', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1'),
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
-- Estructura de tabla para la tabla `payment_tickets`
--

CREATE TABLE IF NOT EXISTS `payment_tickets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event_key` varchar(100) NOT NULL,
  `ticket_code` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_tickets_event_code` (`event_key`, `ticket_code`),
  KEY `idx_payment_tickets_event_active` (`event_key`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_tickets`
--

INSERT INTO `payment_tickets`
  (`event_key`, `ticket_code`, `name`, `price`, `currency`, `is_active`)
VALUES
  ('DIGITALTRENDS', 'VIP', 'VIP', 10.00, 'USD', 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `price` = VALUES(`price`),
  `currency` = VALUES(`currency`),
  `is_active` = VALUES(`is_active`),
  `updated_at` = current_timestamp();

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_coupons`
--

CREATE TABLE IF NOT EXISTS `payment_coupons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(150) NOT NULL,
  `link_code` varchar(150) DEFAULT NULL,
  `discount_type` varchar(50) NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `event_key` varchar(100) DEFAULT NULL,
  `event_vip_id` varchar(150) DEFAULT NULL,
  `ticket_id` bigint(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_coupons_code` (`code`),
  UNIQUE KEY `uq_payment_coupons_link_code` (`link_code`),
  KEY `idx_payment_coupons_scope` (`event_key`, `event_vip_id`, `ticket_id`),
  KEY `idx_payment_coupons_active` (`event_vip_id`, `is_active`),
  CONSTRAINT `fk_payment_coupons_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `payment_tickets` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_coupons`
--

INSERT INTO `payment_coupons`
  (`code`, `link_code`, `discount_type`, `discount_value`, `event_key`, `event_vip_id`, `ticket_id`, `is_active`, `starts_at`, `expires_at`)
VALUES
  ('EMMSV1P250FF', 'EMMSV1P250FF', 'percentage', 25.00, 'DIGITALTRENDS', 'digital-trends26-vip', (SELECT id FROM payment_tickets WHERE event_key = 'DIGITALTRENDS' AND ticket_code = 'VIP' LIMIT 1), 1, NULL, NULL),
  ('EMMSV1P500FF', 'EMMSV1P500FF', 'percentage', 50.00, 'DIGITALTRENDS', 'digital-trends26-vip', (SELECT id FROM payment_tickets WHERE event_key = 'DIGITALTRENDS' AND ticket_code = 'VIP' LIMIT 1), 1, NULL, NULL),
  ('EMMSV1PF3EE', 'EMMSV1PF3EE', 'percentage', 100.00, 'DIGITALTRENDS', 'digital-trends26-vip', (SELECT id FROM payment_tickets WHERE event_key = 'DIGITALTRENDS' AND ticket_code = 'VIP' LIMIT 1), 1, NULL, NULL)
ON DUPLICATE KEY UPDATE
  `link_code` = VALUES(`link_code`),
  `discount_type` = VALUES(`discount_type`),
  `discount_value` = VALUES(`discount_value`),
  `event_key` = VALUES(`event_key`),
  `event_vip_id` = VALUES(`event_vip_id`),
  `ticket_id` = VALUES(`ticket_id`),
  `is_active` = VALUES(`is_active`),
  `updated_at` = current_timestamp();

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_transactions`
--

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `payment_id` char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `correlation_id` varchar(64) NOT NULL,
  `status` varchar(50) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `customer_email` varchar(250) NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(300) DEFAULT NULL,
  `customer_ip` varchar(45) DEFAULT NULL,
  `registered_id` bigint(20) DEFAULT NULL,
  `ticket_id` bigint(20) NOT NULL,
  `ticket_code` varchar(100) NOT NULL,
  `ticket_name` varchar(150) NOT NULL,
  `coupon_id` bigint(20) DEFAULT NULL,
  `coupon_code` varchar(150) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `event_key` varchar(100) NOT NULL,
  `event_free_id` varchar(150) NOT NULL,
  `event_vip_id` varchar(150) NOT NULL,
  `event_phase` varchar(150) NOT NULL,
  `provider_approved_at` timestamp NULL DEFAULT NULL,
  `authorization_number` varchar(255) DEFAULT NULL,
  `transaction_link_id` varchar(255) DEFAULT NULL,
  `authorization_response_code` varchar(100) DEFAULT NULL,
  `purchase_response_code` varchar(100) DEFAULT NULL,
  `response_code` varchar(100) DEFAULT NULL,
  `raw_request` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_transactions_payment_id` (`payment_id`),
  KEY `idx_payment_transactions_correlation_id` (`correlation_id`),
  KEY `idx_payment_transactions_ticket_id` (`ticket_id`),
  KEY `idx_payment_transactions_coupon_id` (`coupon_id`),
  CONSTRAINT `fk_payment_transactions_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `payment_tickets` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_payment_transactions_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `payment_coupons` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_event_jobs`
--

CREATE TABLE IF NOT EXISTS `user_event_jobs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `job_type` varchar(100) NOT NULL,
  `aggregate_type` varchar(100) NOT NULL,
  `aggregate_id` bigint(20) NOT NULL,
  `registered_id` bigint(20) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payload` JSON NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `available_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `idempotency_key` varchar(255) NOT NULL,
  `correlation_id` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_event_jobs_idempotency` (`idempotency_key`),
  KEY `idx_user_event_jobs_status` (`status`),
  KEY `idx_user_event_jobs_type_status` (`job_type`, `status`),
  KEY `idx_user_event_jobs_aggregate` (`aggregate_type`, `aggregate_id`),
  KEY `idx_user_event_jobs_correlation_id` (`correlation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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