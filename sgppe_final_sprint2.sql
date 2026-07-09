-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: sgppe
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administrador`
--

DROP TABLE IF EXISTS `administrador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrador` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrador`
--

LOCK TABLES `administrador` WRITE;
/*!40000 ALTER TABLE `administrador` DISABLE KEYS */;
INSERT INTO `administrador` VALUES (3,'Pedro','Gómez'),(8,'sebastian','vargas');
/*!40000 ALTER TABLE `administrador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignacion`
--

DROP TABLE IF EXISTS `asignacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignacion` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) NOT NULL,
  `id_coordinador` int(11) NOT NULL,
  `id_directivo` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id_asignacion`),
  KEY `idx_asignacion_estudiante` (`id_estudiante`),
  KEY `idx_asignacion_coordinador` (`id_coordinador`),
  KEY `idx_asignacion_directivo` (`id_directivo`),
  CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`id_coordinador`) REFERENCES `coordinador` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `asignacion_ibfk_3` FOREIGN KEY (`id_directivo`) REFERENCES `directivo` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacion`
--

LOCK TABLES `asignacion` WRITE;
/*!40000 ALTER TABLE `asignacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `asignacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_practica` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horas_realizadas` int(11) NOT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `id_practica` (`id_practica`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `modulo` varchar(100) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_auditoria_usuario` (`usuario`),
  KEY `idx_auditoria_modulo` (`modulo`),
  KEY `idx_auditoria_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'admin@ucsc.cl','Creacion de usuario','usuarios','::1','ID usuario: 12 | Rol: Estudiante | Institucion: 5','2026-06-18 14:51:37'),(2,'admin@ucsc.cl','Cambio de estado de usuario','usuarios','::1','ID usuario: 5 | Estado: inactiva','2026-06-18 15:18:35'),(3,'admin@ucsc.cl','Cambio de estado de usuario','usuarios','::1','ID usuario: 5 | Estado: activa','2026-06-18 15:31:10'),(4,'admin@ucsc.cl','Cambio de estado de usuario','usuarios','::1','ID usuario: 5 | Estado: inactiva','2026-06-18 15:33:55'),(5,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: inactivo','2026-06-18 15:34:03'),(6,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: activo','2026-06-18 15:34:05'),(7,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: inactivo','2026-06-18 15:34:06'),(8,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: activo','2026-06-18 15:34:06'),(9,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: inactivo','2026-06-18 15:34:06'),(10,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: activo','2026-06-18 15:34:06'),(11,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: inactivo','2026-06-18 15:34:06'),(12,'administrador','Cambio de estado de rol','roles','::1','ID rol: 1 | Estado: activo','2026-06-18 15:34:07'),(13,'admin@ucsc.cl','Creacion de usuario','usuarios','::1','ID usuario: 13 | Rol: Coordinador | Institucion: 5','2026-06-18 15:37:12'),(14,'administrador','Creacion de rol','roles','::1','Rol: Directivo | Estado: activo','2026-06-18 15:40:00'),(15,'admin@ucsc.cl','Creacion de usuario','usuarios','::1','ID usuario: 14 | Rol: Directivo | Institucion: 5','2026-06-18 15:40:28'),(16,'admin@ucsc.cl','Cambio de estado de usuario','usuarios','::1','ID usuario: 5 | Estado: activa','2026-07-05 20:42:16'),(17,'admin@ucsc.cl','Cambio de estado de usuario','usuarios','::1','ID usuario: 5 | Estado: inactiva','2026-07-05 20:42:17');
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `id_practica` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `actividades` text NOT NULL,
  `logros` text DEFAULT NULL,
  `horas_registradas` int(11) NOT NULL,
  PRIMARY KEY (`id_bitacora`),
  KEY `id_practica` (`id_practica`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrera`
--

DROP TABLE IF EXISTS `carrera`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrera` (
  `id_carrera` int(11) NOT NULL AUTO_INCREMENT,
  `id_institucion` int(11) NOT NULL,
  `nombre_carrera` varchar(255) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  PRIMARY KEY (`id_carrera`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `id_institucion` (`id_institucion`),
  CONSTRAINT `carrera_ibfk_1` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrera`
--

LOCK TABLES `carrera` WRITE;
/*!40000 ALTER TABLE `carrera` DISABLE KEYS */;
INSERT INTO `carrera` VALUES (1,5,'Ingeniería Civil Informática','INF-01'),(2,5,'Ingeniería Comercial','COM-01'),(3,5,'Psicología','PSI-01');
/*!40000 ALTER TABLE `carrera` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competencias`
--

DROP TABLE IF EXISTS `competencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `tipo` enum('técnica','blanda') DEFAULT 'técnica',
  PRIMARY KEY (`id`),
  KEY `id_carrera` (`id_carrera`),
  CONSTRAINT `competencias_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competencias`
--

LOCK TABLES `competencias` WRITE;
/*!40000 ALTER TABLE `competencias` DISABLE KEYS */;
INSERT INTO `competencias` VALUES (1,'PHP',1,'técnica'),(2,'LARAVEL',1,'técnica');
/*!40000 ALTER TABLE `competencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
  `clave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES ('correo_soporte','soporte@sgppe.cl','Correo de soporte del sistema','2026-06-18 14:39:07'),('estado_sistema','activo','Estado general del sistema','2026-06-18 14:39:07'),('tiempo_sesion','30','Tiempo de sesion en minutos','2026-06-18 14:39:07');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coordinador`
--

DROP TABLE IF EXISTS `coordinador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coordinador` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_carrera` (`id_carrera`),
  CONSTRAINT `coordinador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `coordinador_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coordinador`
--

LOCK TABLES `coordinador` WRITE;
/*!40000 ALTER TABLE `coordinador` DISABLE KEYS */;
INSERT INTO `coordinador` VALUES (13,1,'coordinador','coordinador');
/*!40000 ALTER TABLE `coordinador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directivo`
--

DROP TABLE IF EXISTS `directivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directivo` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_carrera` (`id_carrera`),
  CONSTRAINT `directivo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `directivo_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directivo`
--

LOCK TABLES `directivo` WRITE;
/*!40000 ALTER TABLE `directivo` DISABLE KEYS */;
INSERT INTO `directivo` VALUES (14,1,'sebastian','vargas');
/*!40000 ALTER TABLE `directivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa`
--

DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresa` (
  `id_usuario` int(11) NOT NULL,
  `razon_social` varchar(255) NOT NULL,
  `rut_empresa` varchar(12) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `nombre_encargado` varchar(100) DEFAULT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `rut_empresa` (`rut_empresa`),
  CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa`
--

LOCK TABLES `empresa` WRITE;
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
INSERT INTO `empresa` VALUES (5,'Tech Solutions SPA','76.123.456-7','Concepción','412345678','Carlos Pérez','Tech Solutions'),(15,'empresa spa','76.115.412.5','lincoyan 940','+56 9 7107 1530','Juan Perez','empresa spa'),(17,'junior web sql','94439496-6','lincoyan 940','+56 9 7107 1530','Juan Perez','junior web sql');
/*!40000 ALTER TABLE `empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante`
--

DROP TABLE IF EXISTS `estudiante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante` (
  `id_usuario` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `nivel_curricular` int(11) NOT NULL COMMENT 'Semestre o nivel de avance',
  `habilidades` text DEFAULT NULL COMMENT 'Habilidades declaradas para matching',
  `ramos_aprobados` int(11) NOT NULL COMMENT 'Ramos aprobados, validación BR-04',
  `cv_estudiante` varchar(255) DEFAULT NULL,
  `archivo_cedula` varchar(255) DEFAULT NULL,
  `archivo_alumno_regular` varchar(255) DEFAULT NULL,
  `documentos_aprobados` tinyint(1) DEFAULT 0,
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `id_carrera` (`id_carrera`),
  CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `estudiante_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante`
--

LOCK TABLES `estudiante` WRITE;
/*!40000 ALTER TABLE `estudiante` DISABLE KEYS */;
INSERT INTO `estudiante` VALUES (12,1,'Sebastian','Vargas',8,'PHP, Javascript',30,'cv.pdf','cedula.pdf','regular.pdf',1,NULL),(19,1,'Diego','Rojas',9,'Python, Django, PostgreSQL, Git',35,'cv_19.pdf','cedula_19.pdf','alumno_19.pdf',1,NULL),(20,1,'Camila','Fuentes',8,'React, Node.js, CSS, HTML',32,'cv_20.pdf','cedula_20.pdf','alumno_20.pdf',1,NULL),(21,1,'Nicolás','Soto',10,'Java, Spring Boot, MySQL',40,'cv_21.pdf','cedula_21.pdf','alumno_21.pdf',1,NULL),(22,2,'Javiera','Martínez',7,'Excel, Finanzas, Marketing',28,'cv_22.pdf','cedula_22.pdf','alumno_22.pdf',1,NULL),(23,2,'Matías','Valenzuela',9,'Administración, Contabilidad',34,'cv_23.pdf','cedula_23.pdf','alumno_23.pdf',1,NULL),(24,3,'Sofía','Benítez',8,'Psicología laboral, Selección de personal',31,'cv_24.pdf','cedula_24.pdf','alumno_24.pdf',1,NULL);
/*!40000 ALTER TABLE `estudiante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_competencias`
--

DROP TABLE IF EXISTS `estudiante_competencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_competencias` (
  `id_estudiante` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL,
  PRIMARY KEY (`id_estudiante`,`id_competencia`),
  KEY `id_competencia` (`id_competencia`),
  CONSTRAINT `estudiante_competencias_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `estudiante_competencias_ibfk_2` FOREIGN KEY (`id_competencia`) REFERENCES `competencias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_competencias`
--

LOCK TABLES `estudiante_competencias` WRITE;
/*!40000 ALTER TABLE `estudiante_competencias` DISABLE KEYS */;
INSERT INTO `estudiante_competencias` VALUES (12,2);
/*!40000 ALTER TABLE `estudiante_competencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluacion`
--

DROP TABLE IF EXISTS `evaluacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluacion` (
  `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_practica` int(11) NOT NULL,
  `id_bitacora` int(11) DEFAULT NULL,
  `nota_final` decimal(4,1) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_evaluacion` date NOT NULL,
  PRIMARY KEY (`id_evaluacion`),
  KEY `id_practica` (`id_practica`),
  KEY `id_bitacora` (`id_bitacora`),
  CONSTRAINT `evaluacion_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`),
  CONSTRAINT `evaluacion_ibfk_2` FOREIGN KEY (`id_bitacora`) REFERENCES `bitacora` (`id_bitacora`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluacion`
--

LOCK TABLES `evaluacion` WRITE;
/*!40000 ALTER TABLE `evaluacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluacion_empresa`
--

DROP TABLE IF EXISTS `evaluacion_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluacion_empresa` (
  `id_evaluacion_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `id_practica` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `nota_final` decimal(4,1) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `fecha_evaluacion` date NOT NULL,
  PRIMARY KEY (`id_evaluacion_empresa`),
  KEY `id_practica` (`id_practica`),
  KEY `id_estudiante` (`id_estudiante`),
  CONSTRAINT `evaluacion_empresa_ibfk_1` FOREIGN KEY (`id_practica`) REFERENCES `practica` (`id_practica`),
  CONSTRAINT `evaluacion_empresa_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluacion_empresa`
--

LOCK TABLES `evaluacion_empresa` WRITE;
/*!40000 ALTER TABLE `evaluacion_empresa` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluacion_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `institucion`
--

DROP TABLE IF EXISTS `institucion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la institucion',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la institución.',
  `logo_institucion` varchar(500) DEFAULT NULL COMMENT 'Logo asociado a la institución participante.',
  `estado_institucion` enum('activa','inactiva') NOT NULL DEFAULT 'activa' COMMENT 'Estado de la institución en el sistema.',
  `id_administrador` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_institucion`),
  UNIQUE KEY `Institucion_unique` (`nombre`),
  KEY `fk_institucion_administrador` (`id_administrador`),
  CONSTRAINT `fk_institucion_administrador` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Institucion participante del sistema de gestion de prácticas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institucion`
--

LOCK TABLES `institucion` WRITE;
/*!40000 ALTER TABLE `institucion` DISABLE KEYS */;
INSERT INTO `institucion` VALUES (5,'Universidad católica de la santísima Concepción','https://upload.wikimedia.org/wikipedia/commons/7/7a/UCSC%2C_Universidad_Cat%C3%B3lica_de_la_Sant%C3%ADsima_Concepci%C3%B3n.png','activa',8);
/*!40000 ALTER TABLE `institucion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intentos_envio`
--

DROP TABLE IF EXISTS `intentos_envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `intentos_envio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `fecha_intento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intentos_envio`
--

LOCK TABLES `intentos_envio` WRITE;
/*!40000 ALTER TABLE `intentos_envio` DISABLE KEYS */;
INSERT INTO `intentos_envio` VALUES (26,'::1','2026-06-18 15:22:43'),(27,'::1','2026-06-18 15:28:08'),(28,'::1','2026-06-18 15:50:13'),(29,'::1','2026-07-05 20:44:12');
/*!40000 ALTER TABLE `intentos_envio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_evento` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_notificacion`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion`
--

LOCK TABLES `notificacion` WRITE;
/*!40000 ALTER TABLE `notificacion` DISABLE KEYS */;
INSERT INTO `notificacion` VALUES (1,12,'¡Práctica Recomendada!','Tu coordinador te recomienda revisar la oferta: \'24234sd\'. Tu perfil hace match con lo que buscan.','2026-07-05 20:45:23',1,'recomendacion'),(3,12,'Práctica Evaluada','Tu coordinador ha evaluado tu práctica con nota 6.5.','2026-07-09 19:58:04',1,'evaluacion'),(4,12,'¡Práctica Recomendada!','Tu coordinador te recomienda revisar la oferta: \'24234sd\'. Tu perfil hace match con lo que buscan.','2026-07-09 20:01:45',1,'recomendacion'),(5,12,'¡Postulación Aceptada!','La empresa empresa spa ha aceptado tu postulación para \'24234sd\'. Tu práctica profesional se encuentra ahora EN CURSO.','2026-07-09 20:04:48',0,'confirmacion');
/*!40000 ALTER TABLE `notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oferta_competencias`
--

DROP TABLE IF EXISTS `oferta_competencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oferta_competencias` (
  `id_oferta` int(11) NOT NULL,
  `id_competencia` int(11) NOT NULL,
  PRIMARY KEY (`id_oferta`,`id_competencia`),
  KEY `id_competencia` (`id_competencia`),
  CONSTRAINT `oferta_competencias_ibfk_1` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`) ON DELETE CASCADE,
  CONSTRAINT `oferta_competencias_ibfk_2` FOREIGN KEY (`id_competencia`) REFERENCES `competencias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oferta_competencias`
--

LOCK TABLES `oferta_competencias` WRITE;
/*!40000 ALTER TABLE `oferta_competencias` DISABLE KEYS */;
INSERT INTO `oferta_competencias` VALUES (3,1),(3,2),(4,1);
/*!40000 ALTER TABLE `oferta_competencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oferta_practica`
--

DROP TABLE IF EXISTS `oferta_practica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oferta_practica` (
  `id_oferta` int(11) NOT NULL AUTO_INCREMENT,
  `id_carrera` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `requisitos` text NOT NULL,
  `cupos` int(11) NOT NULL,
  `duracion_meses` int(11) NOT NULL,
  `estado_oferta` enum('pendiente_aprobacion','activa','rechazada','pausada','cerrada') NOT NULL DEFAULT 'pendiente_aprobacion',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` date DEFAULT NULL,
  PRIMARY KEY (`id_oferta`),
  KEY `id_carrera` (`id_carrera`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `oferta_practica_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`),
  CONSTRAINT `oferta_practica_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oferta_practica`
--

LOCK TABLES `oferta_practica` WRITE;
/*!40000 ALTER TABLE `oferta_practica` DISABLE KEYS */;
INSERT INTO `oferta_practica` VALUES (2,1,5,'Desarrollador Web Junior','Apoyo en desarrollo web','PHP, HTML, CSS y MySQL',3,6,'rechazada','2026-06-16 21:40:34',NULL),(3,1,15,'24234sd','asdikjasdunbhjasnubh','Ver etiquetas de competencias',0,3,'cerrada','2026-06-18 15:50:13',NULL),(4,1,17,'asdasd','asd','Ver etiquetas de competencias',5,3,'activa','2026-07-05 20:44:12',NULL);
/*!40000 ALTER TABLE `oferta_practica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulacion`
--

DROP TABLE IF EXISTS `postulacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `postulacion` (
  `id_postulacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL,
  `fecha_postulacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_postulacion` enum('espera','aceptada','rechazada') NOT NULL DEFAULT 'espera',
  `cv_estudiante` varchar(255) NOT NULL,
  `token_confirmacion` varchar(255) DEFAULT NULL,
  `fecha_limite_confirmacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_postulacion`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_oferta` (`id_oferta`),
  CONSTRAINT `postulacion_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`),
  CONSTRAINT `postulacion_ibfk_2` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulacion`
--

LOCK TABLES `postulacion` WRITE;
/*!40000 ALTER TABLE `postulacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `postulacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practica`
--

DROP TABLE IF EXISTS `practica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `practica` (
  `id_practica` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL,
  `id_coordinador` int(11) DEFAULT NULL,
  `id_directivo` int(11) DEFAULT NULL,
  `estado_practica` enum('postulado','asignado','en_curso','informe_entregado','evaluado','finalizado','cancelada') NOT NULL DEFAULT 'postulado',
  `fecha_inicio` date NOT NULL,
  `fecha_termino` date DEFAULT NULL,
  `horas_totales` int(11) DEFAULT NULL,
  `nota_final` decimal(4,1) DEFAULT NULL,
  PRIMARY KEY (`id_practica`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_oferta` (`id_oferta`),
  KEY `id_coordinador` (`id_coordinador`),
  KEY `id_directivo` (`id_directivo`),
  CONSTRAINT `practica_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id_usuario`),
  CONSTRAINT `practica_ibfk_2` FOREIGN KEY (`id_oferta`) REFERENCES `oferta_practica` (`id_oferta`),
  CONSTRAINT `practica_ibfk_3` FOREIGN KEY (`id_coordinador`) REFERENCES `coordinador` (`id_usuario`),
  CONSTRAINT `practica_ibfk_4` FOREIGN KEY (`id_directivo`) REFERENCES `directivo` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practica`
--

LOCK TABLES `practica` WRITE;
/*!40000 ALTER TABLE `practica` DISABLE KEYS */;
/*!40000 ALTER TABLE `practica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del rol asignado al usuario',
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción asociado al rol.',
  `nombre_rol` varchar(50) NOT NULL COMMENT 'Nombre del rol.',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'Estado del rol',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `Rol_unique` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Rol asociado al usuario pensado como mantenedor';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Administrador de la institución','Administrador','activo'),(2,'Coordinador de prácticas','Coordinador','activo'),(3,'Empresa externa','Empresa','activo'),(4,'Alumno en práctica','Estudiante','activo'),(5,'permite ver todo el dashboard','Directivo','activo'),(6,'Acceso global a instituciones y administradores.','Super Administrador','activo');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `superadministrador`
--

DROP TABLE IF EXISTS `superadministrador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `superadministrador` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `rut` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `rut_unique` (`rut`),
  CONSTRAINT `superadministrador_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `superadministrador`
--

LOCK TABLES `superadministrador` WRITE;
/*!40000 ALTER TABLE `superadministrador` DISABLE KEYS */;
INSERT INTO `superadministrador` VALUES (18,'Super','Admin','99.999.999-9');
/*!40000 ALTER TABLE `superadministrador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `id_institucion` int(11) DEFAULT NULL,
  `rut` varchar(12) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `estado_cuenta` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  `ultimo_acceso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `rut` (`rut`),
  UNIQUE KEY `correo` (`correo`),
  KEY `id_rol` (`id_rol`),
  KEY `id_institucion` (`id_institucion`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`),
  CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (3,1,5,'16.222.222-1','pgomez@admin.com','$2y$10$ZhHD/FG4phU3bdjdliUqx.UlAV2zwwGY68SEqWyHdzzG6gU2hVety','activa','2026-06-16 01:25:36','2026-06-16 01:24:41'),(5,3,5,'11.111.111-1','empresa@test.cl','123456','inactiva','2026-07-05 20:42:17','2026-06-16 21:30:09'),(8,1,5,'191643690','admin@ucsc.cl','$2y$10$9qYjlfySb9LQaWl0aNTymO96bR.HAdK7w7N5D9pZkOJHBC90ehE.W','activa','2026-06-18 14:46:36','2026-06-18 14:46:36'),(12,4,5,'211428058','svargasn@ing.ucsc.cl','$2y$10$ctsO13cKyHL8PhI3QL/UuuSiPUFZVT5YupU55kBwUgVtS4aHR9ZtS','activa','2026-06-18 14:51:37','2026-06-18 14:51:37'),(13,2,5,'97034168','coord@coord.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-06-18 15:37:12','2026-06-18 15:37:12'),(14,5,5,'55782520','directivo@directivo.cl','$2y$10$2pNVV99bynwzOj2i455mLu1xCdkgooxMxJZw5CTHJXdTKfXJr29am','activa','2026-06-18 15:40:28','2026-06-18 15:40:28'),(15,3,5,'76.115.412.5','aq@aasd.cl','$2y$10$lvwTP5XH2z5QnCflTRrFpeqibhNLOPYyK7x7Zg92fUNDyqHJgV4Fm','activa','2026-06-18 15:50:13','2026-06-18 15:50:13'),(17,3,5,'94439496-6','as@asa.cs','$2y$10$1RPrZmTT.N4z0.H61GAjFeRb2DYyBq8asvr4fa.PZnCTORtJF1Yrm','activa','2026-07-05 20:44:12','2026-07-05 20:44:12'),(18,6,5,'99.999.999-9','superadmin@sgppe.cl','$2y$10$PIi0ft/nKYjvAY4Dr6zmnuZ3tepTN51LVZnsVzWo80oKV1NdKaJWG','activa','2026-07-05 21:11:02','2026-07-05 21:11:02'),(19,4,5,'20.111.111-1','drojas@ing.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19'),(20,4,5,'21.222.222-2','cfuentes@ing.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19'),(21,4,5,'22.333.333-3','nsoto@ing.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19'),(22,4,5,'23.444.444-4','jmartinez@comercial.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19'),(23,4,5,'24.555.555-5','mvalenzuela@comercial.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19'),(24,4,5,'25.666.666-6','sbenitez@psicologia.ucsc.cl','$2y$10$pJGo07dAuVIvNoDGuayC6ef/fqF9HesinNTuZ9COHxX3iMF3njsVO','activa','2026-07-09 20:01:19','2026-07-09 20:01:19');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sgppe.trg_usuario_bi_institucion BEFORE INSERT ON sgppe.usuario FOR EACH ROW BEGIN
         DECLARE rol_admin INT;
        SELECT id_rol INTO rol_admin FROM rol WHERE nombre_rol = 'Administrador' LIMIT 1;
       IF NEW.id_rol <> rol_admin AND NEW.id_institucion IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'id_institucion no puede ser NULL para este rol';
        END IF;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER sgppe.trg_usuario_bu_institucion BEFORE UPDATE ON sgppe.usuario FOR EACH ROW BEGIN
       DECLARE rol_admin INT;
        SELECT id_rol INTO rol_admin FROM rol WHERE nombre_rol = 'Administrador' LIMIT 1;
        IF NEW.id_rol <> rol_admin AND NEW.id_institucion IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'id_institucion no puede ser NULL para este rol';
        END IF;
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-09 16:06:57
