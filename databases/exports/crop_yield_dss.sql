/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: crop_yield_dss
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0+deb12u2

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
-- Current Database: `crop_yield_dss`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `crop_yield_dss` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `crop_yield_dss`;

--
-- Table structure for table `predictions`
--

DROP TABLE IF EXISTS `predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `predictions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `crop_type` varchar(50) NOT NULL,
  `district` varchar(80) NOT NULL,
  `season` varchar(30) NOT NULL,
  `farm_size_acres` decimal(8,2) NOT NULL,
  `soil_type` enum('sandy','clay','loam','silt','other') NOT NULL DEFAULT 'other',
  `rainfall_mm` decimal(8,2) NOT NULL,
  `avg_temp_c` decimal(5,2) NOT NULL,
  `fertilizer_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `irrigation` enum('yes','no') NOT NULL DEFAULT 'no',
  `seed_type` enum('local','improved','hybrid','other') NOT NULL DEFAULT 'other',
  `predicted_yield_tons` decimal(10,2) NOT NULL,
  `predicted_yield_tpa` decimal(10,2) NOT NULL,
  `risk_level` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_predictions_created_at` (`created_at`),
  KEY `idx_predictions_crop` (`crop_type`),
  KEY `idx_predictions_district` (`district`),
  KEY `idx_predictions_season` (`season`),
  KEY `idx_predictions_user` (`user_id`),
  CONSTRAINT `fk_predictions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predictions`
--

LOCK TABLES `predictions` WRITE;
/*!40000 ALTER TABLE `predictions` DISABLE KEYS */;
INSERT INTO `predictions` VALUES
(1,1,'maize','Kawanda','2026A',5.00,'clay',240.00,28.00,500.00,'no','improved',7.64,1.53,'high','Rainfall is low. Consider irrigation, mulching, and drought-tolerant varieties. Clay soil can hold water. Ensure proper drainage to avoid waterlogging. If irrigation is possible, it can reduce risk during low-rain seasons.','2026-02-09 18:56:14'),
(2,1,'beans','Kawanda','2026A',6.00,'loam',500.00,26.00,600.00,'yes','local',6.14,1.02,'medium','Consider improved or hybrid seed for higher yield potential, if available and affordable.','2026-02-09 19:13:20');
/*!40000 ALTER TABLE `predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Admin','admin','$2y$12$svzbXyueL39tvCPe9C67J.EvY1NGBvTL/WgoCmu3kHclG8K1TkGKW','admin','2026-02-08 20:38:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-09 23:47:59
