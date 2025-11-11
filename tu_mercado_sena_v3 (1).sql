-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 03:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tu_mercado_sena_v3`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditorias`
--

CREATE TABLE `auditorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `administrador_id` int(10) UNSIGNED NOT NULL COMMENT 'quién hizo la acción',
  `suceso_id` int(10) UNSIGNED NOT NULL COMMENT 'cuál es la naturaleza de la acción',
  `descripcion` varchar(512) NOT NULL COMMENT 'detalla qué fué lo que sucedió, dando id de tablas implicadas',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bloqueados`
--

CREATE TABLE `bloqueados` (
  `id` int(10) UNSIGNED NOT NULL,
  `bloqueador_id` int(10) UNSIGNED NOT NULL COMMENT 'quien bloqueo al bloqueado',
  `bloqueado_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL COMMENT 'por ejemplo, electrodomesticos, mobiliario, comida, ropa, etc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'vestimenta'),
(2, 'alimento'),
(3, 'papelería'),
(4, 'herramienta'),
(5, 'cosmético'),
(6, 'deportivo'),
(7, 'dispositivo'),
(8, 'servicio'),
(9, 'social'),
(10, 'mobiliario'),
(11, 'vehículo'),
(12, 'mascota'),
(13, 'otro');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(10) UNSIGNED NOT NULL,
  `comprador_id` int(10) UNSIGNED NOT NULL COMMENT 'el usuario que inicia la conversación',
  `producto_id` int(10) UNSIGNED NOT NULL COMMENT 'los chats se inician mediante un producto en venta, aqui estaria ese producto',
  `estado_id` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'el estado dice si el chat esta eliminado, finalizado, activo, etc',
  `visto_comprador` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true indica que el último mensaje del vendedor ya fué visto por el comprador',
  `visto_vendedor` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true indica que el último mensaje del comprador ya fué visto por el vendedor',
  `precio` float DEFAULT NULL COMMENT 'el precio total final acordado, puede diferir del precio del producto, esto lo pone el vendedor',
  `cantidad` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'la cantidad de ítems transaccionados, esto lo pone el vendedor',
  `calificacion` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1 a 5 puesta por el comprador y se puede modificar luego',
  `comentario` varchar(255) DEFAULT NULL COMMENT 'escrito por el comprador y se puede modificar luego',
  `fecha_venta` timestamp NULL DEFAULT NULL COMMENT 'cuándo se hizo la transacción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `correos`
--

CREATE TABLE `correos` (
  `id` int(10) UNSIGNED NOT NULL,
  `correo` varchar(64) NOT NULL,
  `clave` varchar(32) NOT NULL COMMENT 'una combinación aleatoria que será enviada al correo, con un solo uso limitado por tiempo',
  `fecha_mail` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'guarda el momento en que se envio una solicitud al mail, para poder esperar y no enviarlas muy seguido'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `correos`
--

INSERT INTO `correos` (`id`, `correo`, `clave`, `fecha_mail`) VALUES
(1, 'brandon@gmail.com', '', '2025-08-25 01:50:50'),
(5, 'dani@gmail.com', '', '2025-08-25 02:00:15'),
(7, 'jean@gmail.com', '', '2025-08-26 04:54:04'),
(8, 'fredy@gmail.com', '', '2025-08-27 02:45:57'),
(9, 'johan12@gmail.com', '', '2025-10-17 03:48:39');

-- --------------------------------------------------------

--
-- Table structure for table `denuncias`
--

CREATE TABLE `denuncias` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'si fué creada a partir de un producto',
  `usuario_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'si fué creada a partir de un usuario',
  `chat_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'si fué creada a partir de un chat / mensaje de comprador',
  `motivo_id` int(10) UNSIGNED NOT NULL COMMENT 'indica qué naturaleza tiene la denuncia',
  `estado_id` int(10) UNSIGNED NOT NULL COMMENT 'indica si ya se ha procesado la denuncia',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estados`
--

CREATE TABLE `estados` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estados`
--

INSERT INTO `estados` (`id`, `nombre`, `descripcion`) VALUES
(1, 'activo', 'cuando funciona con completa normalidad'),
(2, 'invisible', 'cuando un producto es sacado temporalmente del mercado o un usuario quiere ocultarse temporalmente'),
(3, 'eliminado', 'ya no puede ser alcanzado por los usuarios nunca más'),
(4, 'bloqueado', 'se ha aplicado una censura a usuario o producto por parte del sistema'),
(5, 'vendido', 'aplicado a un chat cuando se hizo la transacción'),
(6, 'esperando', 'la transacción del chat espera el visto bueno del comprador'),
(7, 'devolviendo', 'el historial abre una solicitud de devolución, a espera de respuesta del vendedor'),
(8, 'devuelto', 'el chat finalizó con una transacción que fué cancelada'),
(9, 'censurado', 'el estado del chat era vendido, pero la administración baneó la calificación y comentario'),
(10, 'denunciado', 'cuando un usuario o producto ha sido denunciado repetidas veces, mientras se revisa el caso, no será listado públicamente, pero '),
(11, 'resuelto', 'para decir que una PQRS o denuncia ya fué tratada');

-- --------------------------------------------------------

--
-- Table structure for table `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(10) UNSIGNED NOT NULL,
  `votante_id` int(10) UNSIGNED NOT NULL COMMENT 'el votante dijo que el votado era su favorito',
  `votado_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integridad`
--

CREATE TABLE `integridad` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL COMMENT 'ejemplo, nuevo, de segunda pero bueno, en mal estado (digamos que vende un PC malo para sacarle componentes)',
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `integridad`
--

INSERT INTO `integridad` (`id`, `nombre`, `descripcion`) VALUES
(1, 'nuevo', 'alta calidad, recién hecho o sin desempacar, sin uso'),
(2, 'usado', 'el producto está en buena calidad pero ya ha sido usado o tiene algún tipo de desgaste'),
(3, 'reparado', 'el producto puede tener fallas pero aún funciona'),
(4, 'reciclable', 'el producto está inutilizable, pero puede ser reutilizado, reparado o desarmado');

-- --------------------------------------------------------

--
-- Table structure for table `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(10) UNSIGNED NOT NULL,
  `es_comprador` tinyint(3) UNSIGNED NOT NULL COMMENT 'el chat tiene usuario A y B, acá es 1 si lo escribió A o 0 si B',
  `chat_id` int(10) UNSIGNED NOT NULL COMMENT 'a que chat va, ahi estara el receptor',
  `mensaje` varchar(512) NOT NULL COMMENT 'el texto como tal',
  `fecha_registro` date NOT NULL DEFAULT current_timestamp() COMMENT 'automaticamente se establece cuando se creo el mensaje',
  `es_imagen` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'en true significa que habra un archivo JPG, su nombre debe llevar el id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `motivos`
--

CREATE TABLE `motivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motivos`
--

INSERT INTO `motivos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'pqrs_pregunta', 'mensaje de pregunta'),
(2, 'pqrs_queja', 'mensaje de queja'),
(3, 'pqrs_reclamo', 'mensaje de reclamo'),
(4, 'pqrs_sugerencia', 'mensaje de sugerencia'),
(5, 'pqrs_agradecimiento', 'mensaje de agradecimiento'),
(6, 'notifica_denuncia', 'se ha respondido algo ante una denuncia'),
(7, 'notifica_pqrs', 'se ha respondido algo a una PQRS'),
(8, 'notifica_comprador', 'un comprador potencial se ha puesto en contacto'),
(9, 'notifica_comunidad', 'ha llegado un mensaje enviado a todos los usuarios'),
(10, 'notifica_administrativa', 'un mensaje interno de la administración, por ejemplo, puedes haber sido baneado o eliminado'),
(11, 'notifica_bienvenida', 'mensaje de bienvenida al sistema'),
(12, 'notifica_oferta', 'un favorito ha publicado un nuevo producto'),
(13, 'notifica_venta', 'un vendedor ha enviado una solicitud de consolidar venta'),
(14, 'notifica_devolver', 'un comprador ha enviado una solicitud de cancelar una transacción'),
(15, 'notifica_exito', 'se ha llevado a cabo una compraventa exitosa'),
(16, 'notifica_cancela', 'se ha llevado a cabo una devolución exitosa, se cancelará la compraventa del historial'),
(17, 'notifica_califica', 'un comprador ha calificado o escrito un comentario, o lo ha modificado'),
(18, 'denuncia_acoso', 'comportamiento de acoso sexual en un chat o imágenes o descripciónes'),
(19, 'denuncia_bulling', 'comportamiento de burlas o insultos en un chat o imágenes o descripciónes'),
(20, 'denuncia_violencia', 'comportamiento que incita al odio o amenzada directamente'),
(21, 'denuncia_ilegal', 'comportamiento asociado a drogas, armas, prostitución y demás'),
(22, 'denuncia_troll', 'comportamiento enfocado en molestar y hacer perder el tiempo, por ejemplo, con negociaciónes por mamar gallo'),
(23, 'denuncia_fraude', 'se trata de vender algo malo o mediante trampas, tratan de tumbar al otro con fraudes'),
(24, 'denuncia_fake', 'un producto o perfil es meme o chisto o simplemente hace perder el tiempo al no ser una propuesta real'),
(25, 'denuncia_spam', 'un producto o perfil aparece muchas veces como si lo pusieran en demasia para llamar la atención'),
(26, 'denuncia_sexual', 'un perfil o producto exhibe temáticas sexuales o pornográficas que incomodan a la comunidad');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'quién recibirá la notificación',
  `motivo_id` int(10) UNSIGNED NOT NULL COMMENT 'naturaleza de la notificación',
  `mensaje` varchar(255) NOT NULL COMMENT 'cuerpo de la notificación',
  `visto` tinyint(3) UNSIGNED NOT NULL COMMENT 'true si ya fué abierta por el usuario',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pqrs`
--

CREATE TABLE `pqrs` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'quién hizo la PQRS',
  `mensaje` varchar(512) NOT NULL COMMENT 'mensaje del cuerpo',
  `motivo_id` int(10) UNSIGNED NOT NULL COMMENT 'para saber si es P, Q, R, S',
  `estado_id` int(10) UNSIGNED NOT NULL COMMENT 'para saber si ya se proceso la PQRS',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(64) NOT NULL,
  `con_imagen` tinyint(1) NOT NULL COMMENT 'si es true, significa que habra un archivo del tipo img_id_producto.jpg en la carpeta correspondiente',
  `subcategoria_id` int(10) UNSIGNED NOT NULL COMMENT 'la subcategoria incluye a la categoria, por ejemplo, electrodomesticos, mobiliario, alimento, etc',
  `integridad_id` int(10) UNSIGNED NOT NULL COMMENT 'para saber si el producto es nuevo, de segunda pero en buen estado o si es un producto con fallas',
  `vendedor_id` int(10) UNSIGNED NOT NULL COMMENT 'apunta al id de usuario que es su creador',
  `estado_id` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'l estado define si esta visible, eliminado, bloqueado',
  `descripcion` varchar(512) NOT NULL COMMENT 'texto amplio describiendo las caracteristicas del producto',
  `precio` float NOT NULL COMMENT 'cuanto cuesta en COP',
  `disponibles` smallint(5) UNSIGNED NOT NULL COMMENT 'cuandos articulos hay disponibles',
  `fecha_registro` date NOT NULL DEFAULT current_timestamp() COMMENT 'esto solo se coloca automaticamente al crear el registro, y se deja asi',
  `fecha_actualiza` date NOT NULL DEFAULT '2000-01-01' COMMENT 'esto se actualiza cuando hay edicion por parte del propietario, es para evitar actualizaciones muy seguidas, opcionalmente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `con_imagen`, `subcategoria_id`, `integridad_id`, `vendedor_id`, `estado_id`, `descripcion`, `precio`, `disponibles`, `fecha_registro`, `fecha_actualiza`) VALUES
(3, 'silla', 68, 1, 1, 3, 1, 'eeegggg', 2222, 1, '2025-08-24', '2025-08-24'),
(4, 'silla ergonomica', 68, 1, 3, 4, 1, 'buen uso', 399999, 1, '2025-08-25', '2025-08-25'),
(5, 'hkj', 68, 1, 1, 3, 1, 'hgcfhjkjhg', 1234, 1, '2025-08-26', '2025-08-26'),
(6, 'sillas nuevas', 68, 1, 2, 5, 1, 'buen uso', 44444, 1, '2025-08-26', '2025-08-26');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'master'),
(2, 'administrador'),
(3, 'usuario');

-- --------------------------------------------------------

--
-- Table structure for table `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL COMMENT 'por ejemplo, para la categoria ropa tenemos: calzado, pantalon, camisa, sombrero, etc',
  `categoria_id` int(10) UNSIGNED NOT NULL COMMENT 'a que categoria pertenece la subcategoria'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategorias`
--

INSERT INTO `subcategorias` (`id`, `nombre`, `categoria_id`) VALUES
(1, 'otro', 2),
(2, 'postre o helado', 2),
(3, 'fruta o verdura fresca', 2),
(4, 'carne o huevos', 2),
(5, 'especias o aditivos', 2),
(6, 'almuerzo o desayuno', 2),
(7, 'chatarra preparada', 2),
(8, 'chatarra industrial', 2),
(9, 'pan o pastel', 2),
(10, 'bebidas', 2),
(21, 'otro', 5),
(22, 'cuidado de la piel', 5),
(23, 'cuidado del pelo', 5),
(24, 'labial', 5),
(25, 'sombra', 5),
(26, 'delineador', 5),
(27, 'piercing', 5),
(28, 'tatuaje', 5),
(29, 'maniquiur', 5),
(30, 'peluqueria', 5),
(31, 'otro', 6),
(32, 'balón', 6),
(33, 'pesas', 6),
(34, 'suplemento alimenticio', 6),
(35, 'patineta o patines', 6),
(36, 'implementos acuaticos', 6),
(37, 'implementos terrestres', 6),
(38, 'implementos extremos', 6),
(39, 'arte marcial o lucha', 6),
(40, 'aseo deportivo', 6),
(41, 'otro', 7),
(42, 'computador de escritorio', 7),
(43, 'computador portátil', 7),
(44, 'periféricos para computador', 7),
(45, 'celular', 7),
(46, 'cámara fotográfica', 7),
(47, 'calculadora o mediciónes', 7),
(48, 'tableta para arte', 7),
(49, 'audifónos, reloj o corporales', 7),
(50, 'sistema de seguridad', 7),
(51, 'otro', 4),
(52, 'taladro, pulidora o similar', 4),
(53, 'martillo, alicate o similar', 4),
(54, 'licuadora, microondas o similar', 4),
(55, 'seguridad para ajustar bicicleta', 4),
(56, 'escuadra o regla para dibujo', 4),
(57, 'metro o medidores', 4),
(58, 'tijera, visturí o similar', 4),
(59, 'ventilador o aire acondicionado', 4),
(60, 'kit de mecánico', 4),
(61, 'otro', 4),
(62, 'taladro, pulidora o similar', 4),
(63, 'martillo, alicate o similar', 4),
(64, 'licuadora, microondas o similar', 4),
(65, 'seguridad para ajustar bicicleta', 4),
(66, 'escuadra o regla para dibujo', 4),
(67, 'metro o medidores', 4),
(68, 'tijera, visturí o similar', 4),
(69, 'ventilador o aire acondicionado', 4),
(70, 'kit de mecánico', 4),
(71, 'otro', 12),
(72, 'perro', 12),
(73, 'gato', 12),
(74, 'pez', 12),
(75, 'alimento', 12),
(76, 'correa', 12),
(77, 'juguete', 12),
(78, 'roedor', 12),
(79, 'tapete', 12),
(80, 'ropa', 12),
(81, 'otro', 10),
(82, 'silla regulable', 10),
(83, 'silla estática', 10),
(84, 'sillón grande', 10),
(85, 'mesa', 10),
(86, 'cama o colchón', 10),
(87, 'matera', 10),
(88, 'armario o nochero', 10),
(89, 'escritorio', 10),
(90, 'tapete', 10),
(91, 'otro', 13),
(92, 'otro', 3),
(93, 'hoja', 3),
(94, 'cartulina', 3),
(95, 'pegamento', 3),
(96, 'colores', 3),
(97, 'lápiz', 3),
(98, 'lapicero', 3),
(99, 'marcador', 3),
(100, 'borrador o sacapuntas', 3),
(101, 'fomi o tela', 3),
(102, 'otro', 8),
(103, 'entrenamiento deportivo', 8),
(104, 'eseñanza artística', 8),
(105, 'enseñanza tecnológica', 8),
(106, 'mantenimiento computadora', 8),
(107, 'reparación dispositivos', 8),
(108, 'preparación de comidas', 8),
(109, 'documentación', 8),
(110, 'creación de arte o manualidades', 8),
(111, 'cuidado y aseo', 8),
(112, 'otro', 9),
(113, 'juego deportivo', 9),
(114, 'juego videojuegos', 9),
(115, 'practicar idiomas', 9),
(116, 'fiesta y baile', 9),
(117, 'charlar', 9),
(118, 'emprendimiento', 9),
(119, 'paseos y viajes', 9),
(120, 'seguir en redes', 9),
(121, 'religión e ideologías', 9),
(122, 'otro', 11),
(123, 'bicicleta', 11),
(124, 'moto de gasolina', 11),
(125, 'casco de moto', 11),
(126, 'moto eléctrica', 11),
(127, 'bici o patín eléctricos', 11),
(128, 'carro', 11),
(129, 'chaleco, guantes o vestimenta', 11),
(130, 'repuesto', 11),
(131, 'parrillas o implementos', 11),
(132, 'otro', 1),
(133, 'calzado', 1),
(134, 'sombrero', 1),
(135, 'pantalón', 1),
(136, 'camisa', 1),
(137, 'vestido', 1),
(138, 'falda', 1),
(139, 'medias o guantes', 1),
(140, 'chaleco o buzo', 1),
(141, 'colgandijas', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sucesos`
--

CREATE TABLE `sucesos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sucesos`
--

INSERT INTO `sucesos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'estado_usuario', 'ha cambiado el estado de un usuario, por ejemplo a activo, eliminado, baneado'),
(2, 'rol_cambiado', 'se ha modificado que un usuario sea o deje de ser administrador'),
(3, 'ver_chat', 'buscando ilegalidades ha entrado a revisar una conversación'),
(4, 'enviar_mail', 'ha enviado un mail a un usuario, lo que también disparará una notificación'),
(5, 'constante_modificada', 'creó, destruyó o editó una constante de la DB por ejemplo, categorías'),
(6, 'cambio_password', 'obtuvo una clave de acceso para recuperar una contraseña o crear una cuenta sin correo institucional'),
(7, 'noticia_masiva', 'envió una notificación y email a todos los usuarios'),
(8, 'estado_producto', 'cambio un producto poniéndolo como eliminado o activo por ejemplo'),
(9, 'respuesta_pqrs', 'marcó una PQRS como resuelta ya que hizo alguna acción para atenderla'),
(10, 'respuesta_denuncia', 'marcó una denuncia como resuelta pues confirma que hizo algo para atenderla'),
(11, 'estado_chat', 'modificó la visibilidad de un historial de compraventa, posiblemente deshabilitando calificación y comentario');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `correo_id` int(10) UNSIGNED NOT NULL COMMENT 'apunta a la tabla donde se guardan los correos y claves de un uso',
  `password` varchar(127) NOT NULL COMMENT 'debe guardarse como un hash',
  `rol_id` int(10) UNSIGNED NOT NULL COMMENT 'administra los permisos de acceso al sistema',
  `nombre` varchar(32) NOT NULL COMMENT 'el nickname del usuario, pueden repetirse',
  `avatar` int(10) UNSIGNED NOT NULL COMMENT 'apunta a alguna configuracion de sprites (imagenes) o a un servicio web con ID de avatar',
  `descripcion` varchar(512) NOT NULL COMMENT 'para que el usuario diga algo sobre si mismo en su perfil',
  `link` varchar(128) NOT NULL COMMENT 'si el usuario quiere compartir redes sociales o algo asi',
  `estado_id` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'esto dice si el usuario esta pendiente de aprobacion, bloqueado del sistema, eliminado, etc',
  `notifica_correo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'en true significa que desea recibir correos cuando alguien se pone en contacto',
  `notifica_push` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'en true significa que quiere recibir notificaciones emergentes en celular o computadora cuando algo sucede',
  `uso_datos` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'en false reduce el consumo de datos de la aplicacion evitando cargar imagenes',
  `fecha_registro` date NOT NULL DEFAULT current_timestamp() COMMENT 'esto no se cambia, solo se pone automaticamente cuando el usuario se registra',
  `fecha_actualiza` date NOT NULL DEFAULT '2000-01-01' COMMENT 'se actualizara cada que el usuario edita su perfil, para dar una ventana de tiempo entre ediciones',
  `fecha_reciente` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'utilizado para saber si el usuario ha estado activo recientemente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo_id`, `password`, `rol_id`, `nombre`, `avatar`, `descripcion`, `link`, `estado_id`, `notifica_correo`, `notifica_push`, `uso_datos`, `fecha_registro`, `fecha_actualiza`, `fecha_reciente`) VALUES
(3, 5, '$2y$10$Dxd2qKJYfC9rlRpIQvrnf.4ZqTaPc/yreCFpAbFJd7FvfFHqpSgt6', 2, 'daniel', 0, '', '', 1, 1, 1, 1, '2025-08-24', '2000-01-01', '2025-08-24 19:11:04'),
(4, 7, '$2y$10$vqy2CeNA2WWwfJ5yhgnQaOs8DCFjsJW4iRxCLOZnGSHBuP7REnSgS', 2, 'Jean', 0, '', '', 1, 1, 1, 1, '2025-08-25', '2000-01-01', '2025-08-25 21:54:04'),
(5, 8, '$2y$10$m3ajAAWVr08MvLVeXWcZ5evzIfgnvRQNhsDl/0V3vAhZ8oiGf7XeG', 2, 'fredy', 0, '', '', 1, 1, 1, 1, '2025-08-26', '2000-01-01', '2025-08-26 19:45:57'),
(6, 9, '$2y$10$Qwz3/C3RuK4f/v1ztyv5Auc7DhInsUbJyMFqS1XYcumkk.vXfWgLW', 2, 'johan', 0, '', '', 1, 1, 1, 1, '2025-10-16', '2000-01-01', '2025-10-16 20:48:39');

-- --------------------------------------------------------

--
-- Table structure for table `vistos`
--

CREATE TABLE `vistos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'quien observo el producto',
  `producto_id` int(10) UNSIGNED NOT NULL COMMENT 'que producto fue observado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auditoria_usuario` (`administrador_id`),
  ADD KEY `auditoria_suceso` (`suceso_id`);

--
-- Indexes for table `bloqueados`
--
ALTER TABLE `bloqueados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_bloqueado` (`bloqueado_id`),
  ADD KEY `usuario_bloqueador` (`bloqueador_id`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_usuario_a` (`comprador_id`),
  ADD KEY `chat_producto` (`producto_id`),
  ADD KEY `chat_estado` (`estado_id`);

--
-- Indexes for table `correos`
--
ALTER TABLE `correos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_unico` (`correo`);

--
-- Indexes for table `denuncias`
--
ALTER TABLE `denuncias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `denuncia_chat` (`chat_id`),
  ADD KEY `denuncia_usuario` (`usuario_id`),
  ADD KEY `denuncia_producto` (`producto_id`),
  ADD KEY `denuncia_estado` (`estado_id`),
  ADD KEY `denuncia_motivo` (`motivo_id`);

--
-- Indexes for table `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_votante` (`votante_id`),
  ADD KEY `usuario_votado` (`votado_id`) USING BTREE;

--
-- Indexes for table `integridad`
--
ALTER TABLE `integridad`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mensaje_chat` (`chat_id`);

--
-- Indexes for table `motivos`
--
ALTER TABLE `motivos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifi_motivo` (`motivo_id`),
  ADD KEY `notifi_usuario` (`usuario_id`);

--
-- Indexes for table `pqrs`
--
ALTER TABLE `pqrs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pqrs_estado` (`estado_id`),
  ADD KEY `pqrs_motivo` (`motivo_id`),
  ADD KEY `pqrs_usuario` (`usuario_id`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_usuario` (`vendedor_id`),
  ADD KEY `producto_subcategoria` (`subcategoria_id`),
  ADD KEY `producto_uso` (`integridad_id`),
  ADD KEY `producto_estado` (`estado_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategoria_categoria` (`categoria_id`);

--
-- Indexes for table `sucesos`
--
ALTER TABLE `sucesos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_estado` (`estado_id`),
  ADD KEY `usuarios_correo` (`correo_id`),
  ADD KEY `usuario_rol` (`rol_id`);

--
-- Indexes for table `vistos`
--
ALTER TABLE `vistos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visto_producto` (`producto_id`),
  ADD KEY `visto_usuario` (`usuario_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bloqueados`
--
ALTER TABLE `bloqueados`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `correos`
--
ALTER TABLE `correos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `denuncias`
--
ALTER TABLE `denuncias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `integridad`
--
ALTER TABLE `integridad`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `motivos`
--
ALTER TABLE `motivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pqrs`
--
ALTER TABLE `pqrs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `sucesos`
--
ALTER TABLE `sucesos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vistos`
--
ALTER TABLE `vistos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditorias`
--
ALTER TABLE `auditorias`
  ADD CONSTRAINT `auditoria_suceso` FOREIGN KEY (`suceso_id`) REFERENCES `sucesos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `auditoria_usuario` FOREIGN KEY (`administrador_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `bloqueados`
--
ALTER TABLE `bloqueados`
  ADD CONSTRAINT `usuario_bloqueado` FOREIGN KEY (`bloqueado_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_bloqueador` FOREIGN KEY (`bloqueador_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chat_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `chat_usuario_a` FOREIGN KEY (`comprador_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `denuncias`
--
ALTER TABLE `denuncias`
  ADD CONSTRAINT `denuncia_chat` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `denuncia_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `denuncia_motivo` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `denuncia_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `denuncia_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `usuario_votado` FOREIGN KEY (`votado_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_votante` FOREIGN KEY (`votante_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensaje_chat` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notifi_motivo` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `notifi_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pqrs`
--
ALTER TABLE `pqrs`
  ADD CONSTRAINT `pqrs_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pqrs_motivo` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pqrs_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `producto_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_subcategoria` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategorias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_uso` FOREIGN KEY (`integridad_id`) REFERENCES `integridad` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_usuario` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `subcategoria_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuario_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_correo` FOREIGN KEY (`correo_id`) REFERENCES `correos` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `vistos`
--
ALTER TABLE `vistos`
  ADD CONSTRAINT `visto_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `visto_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
