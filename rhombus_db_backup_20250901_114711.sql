-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: rhombus_db
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ISS_SUMMARY_2024`
--

DROP TABLE IF EXISTS `ISS_SUMMARY_2024`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ISS_SUMMARY_2024` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CAPABILITY_SPONSOR_CODE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(500) NOT NULL,
  `DELTA_AMT` decimal(15,2) DEFAULT '0.00',
  `FISCAL_YEAR` int DEFAULT '2024',
  `PROGRAM_NAME` varchar(500) DEFAULT NULL,
  `RESOURCE_CATEGORY_CODE` varchar(100) DEFAULT NULL,
  `EXECUTION_MANAGER_CODE` varchar(100) DEFAULT NULL,
  `PROGRAM_CODE` varchar(100) DEFAULT NULL,
  `EOC_CODE` varchar(100) DEFAULT NULL,
  `OSD_PE_CODE` varchar(100) DEFAULT NULL,
  `APPROVAL_STATUS` varchar(50) DEFAULT 'PENDING',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_capability_sponsor` (`CAPABILITY_SPONSOR_CODE`),
  KEY `idx_event_name` (`EVENT_NAME`),
  KEY `idx_fiscal_year` (`FISCAL_YEAR`),
  KEY `idx_program_name` (`PROGRAM_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ISS_SUMMARY_2024`
--

LOCK TABLES `ISS_SUMMARY_2024` WRITE;
/*!40000 ALTER TABLE `ISS_SUMMARY_2024` DISABLE KEYS */;
/*!40000 ALTER TABLE `ISS_SUMMARY_2024` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ISS_SUMMARY_2025`
--

DROP TABLE IF EXISTS `ISS_SUMMARY_2025`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ISS_SUMMARY_2025` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CAPABILITY_SPONSOR_CODE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(500) NOT NULL,
  `DELTA_AMT` decimal(15,2) DEFAULT '0.00',
  `FISCAL_YEAR` int DEFAULT '2025',
  `PROGRAM_NAME` varchar(500) DEFAULT NULL,
  `RESOURCE_CATEGORY_CODE` varchar(100) DEFAULT NULL,
  `EXECUTION_MANAGER_CODE` varchar(100) DEFAULT NULL,
  `PROGRAM_CODE` varchar(100) DEFAULT NULL,
  `EOC_CODE` varchar(100) DEFAULT NULL,
  `OSD_PE_CODE` varchar(100) DEFAULT NULL,
  `APPROVAL_STATUS` varchar(50) DEFAULT 'PENDING',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_capability_sponsor` (`CAPABILITY_SPONSOR_CODE`),
  KEY `idx_event_name` (`EVENT_NAME`),
  KEY `idx_fiscal_year` (`FISCAL_YEAR`),
  KEY `idx_program_name` (`PROGRAM_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ISS_SUMMARY_2025`
--

LOCK TABLES `ISS_SUMMARY_2025` WRITE;
/*!40000 ALTER TABLE `ISS_SUMMARY_2025` DISABLE KEYS */;
/*!40000 ALTER TABLE `ISS_SUMMARY_2025` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LOOKUP_ASSESSMENT_AREA`
--

DROP TABLE IF EXISTS `LOOKUP_ASSESSMENT_AREA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `LOOKUP_ASSESSMENT_AREA` (
  `id` int NOT NULL AUTO_INCREMENT,
  `AREA_CODE` varchar(100) NOT NULL,
  `AREA_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `ACTIVE` tinyint(1) DEFAULT '1',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `AREA_CODE` (`AREA_CODE`),
  KEY `idx_area_code` (`AREA_CODE`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LOOKUP_ASSESSMENT_AREA`
--

LOCK TABLES `LOOKUP_ASSESSMENT_AREA` WRITE;
/*!40000 ALTER TABLE `LOOKUP_ASSESSMENT_AREA` DISABLE KEYS */;
INSERT INTO `LOOKUP_ASSESSMENT_AREA` VALUES (1,'RESEARCH_DEV','Research & Development','Research and Development activities',1,'2025-09-01 02:05:35'),(2,'ACQUISITION','Acquisition','Acquisition and procurement activities',1,'2025-09-01 02:05:35'),(3,'OPERATIONS','Operations','Operational activities',1,'2025-09-01 02:05:35'),(4,'MAINTENANCE','Maintenance','Maintenance and sustainment activities',1,'2025-09-01 02:05:35');
/*!40000 ALTER TABLE `LOOKUP_ASSESSMENT_AREA` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LOOKUP_RESOURCE_CATEGORY`
--

DROP TABLE IF EXISTS `LOOKUP_RESOURCE_CATEGORY`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `LOOKUP_RESOURCE_CATEGORY` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CATEGORY_CODE` varchar(100) NOT NULL,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `ACTIVE` tinyint(1) DEFAULT '1',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `CATEGORY_CODE` (`CATEGORY_CODE`),
  KEY `idx_category_code` (`CATEGORY_CODE`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LOOKUP_RESOURCE_CATEGORY`
--

LOCK TABLES `LOOKUP_RESOURCE_CATEGORY` WRITE;
/*!40000 ALTER TABLE `LOOKUP_RESOURCE_CATEGORY` DISABLE KEYS */;
INSERT INTO `LOOKUP_RESOURCE_CATEGORY` VALUES (1,'RESEARCH_DEV','Research & Development','Research and Development activities',1,'2025-09-01 02:05:35'),(2,'ACQUISITION','Acquisition','Acquisition and procurement activities',1,'2025-09-01 02:05:35'),(3,'OPERATIONS','Operations','Operational activities',1,'2025-09-01 02:05:35'),(4,'MAINTENANCE','Maintenance','Maintenance and sustainment activities',1,'2025-09-01 02:05:35');
/*!40000 ALTER TABLE `LOOKUP_RESOURCE_CATEGORY` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LOOKUP_SPONSOR`
--

DROP TABLE IF EXISTS `LOOKUP_SPONSOR`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `LOOKUP_SPONSOR` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SPONSOR_CODE` varchar(100) NOT NULL,
  `SPONSOR_NAME` varchar(255) NOT NULL,
  `SPONSOR_TYPE` enum('CAPABILITY','POM') NOT NULL,
  `ACTIVE` tinyint(1) DEFAULT '1',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SPONSOR_CODE` (`SPONSOR_CODE`),
  KEY `idx_sponsor_code` (`SPONSOR_CODE`),
  KEY `idx_sponsor_type` (`SPONSOR_TYPE`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `LOOKUP_SPONSOR`
--

LOCK TABLES `LOOKUP_SPONSOR` WRITE;
/*!40000 ALTER TABLE `LOOKUP_SPONSOR` DISABLE KEYS */;
INSERT INTO `LOOKUP_SPONSOR` VALUES (1,'SORDAC','SOF AT&L','CAPABILITY',1,'2025-09-01 02:05:35'),(2,'USSOCOM','USSOCOM','CAPABILITY',1,'2025-09-01 02:05:35'),(3,'SOF_ATL','SOF AT&L','POM',1,'2025-09-01 02:05:35'),(4,'USSOCOM_POM','USSOCOM POM','POM',1,'2025-09-01 02:05:35');
/*!40000 ALTER TABLE `LOOKUP_SPONSOR` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ZBT_SUMMARY_2024`
--

DROP TABLE IF EXISTS `ZBT_SUMMARY_2024`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ZBT_SUMMARY_2024` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CAPABILITY_SPONSOR_CODE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(500) NOT NULL,
  `DELTA_AMT` decimal(15,2) DEFAULT '0.00',
  `FISCAL_YEAR` int DEFAULT '2024',
  `PROGRAM_NAME` varchar(500) DEFAULT NULL,
  `RESOURCE_CATEGORY_CODE` varchar(100) DEFAULT NULL,
  `EXECUTION_MANAGER_CODE` varchar(100) DEFAULT NULL,
  `PROGRAM_CODE` varchar(100) DEFAULT NULL,
  `EOC_CODE` varchar(100) DEFAULT NULL,
  `OSD_PE_CODE` varchar(100) DEFAULT NULL,
  `APPROVAL_STATUS` varchar(50) DEFAULT 'PENDING',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_capability_sponsor` (`CAPABILITY_SPONSOR_CODE`),
  KEY `idx_event_name` (`EVENT_NAME`),
  KEY `idx_fiscal_year` (`FISCAL_YEAR`),
  KEY `idx_program_name` (`PROGRAM_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ZBT_SUMMARY_2024`
--

LOCK TABLES `ZBT_SUMMARY_2024` WRITE;
/*!40000 ALTER TABLE `ZBT_SUMMARY_2024` DISABLE KEYS */;
/*!40000 ALTER TABLE `ZBT_SUMMARY_2024` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ZBT_SUMMARY_2025`
--

DROP TABLE IF EXISTS `ZBT_SUMMARY_2025`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ZBT_SUMMARY_2025` (
  `id` int NOT NULL AUTO_INCREMENT,
  `CAPABILITY_SPONSOR_CODE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(500) NOT NULL,
  `DELTA_AMT` decimal(15,2) DEFAULT '0.00',
  `FISCAL_YEAR` int DEFAULT '2025',
  `PROGRAM_NAME` varchar(500) DEFAULT NULL,
  `RESOURCE_CATEGORY_CODE` varchar(100) DEFAULT NULL,
  `EXECUTION_MANAGER_CODE` varchar(100) DEFAULT NULL,
  `PROGRAM_CODE` varchar(100) DEFAULT NULL,
  `EOC_CODE` varchar(100) DEFAULT NULL,
  `OSD_PE_CODE` varchar(100) DEFAULT NULL,
  `APPROVAL_STATUS` varchar(50) DEFAULT 'PENDING',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_capability_sponsor` (`CAPABILITY_SPONSOR_CODE`),
  KEY `idx_event_name` (`EVENT_NAME`),
  KEY `idx_fiscal_year` (`FISCAL_YEAR`),
  KEY `idx_program_name` (`PROGRAM_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ZBT_SUMMARY_2025`
--

LOCK TABLES `ZBT_SUMMARY_2025` WRITE;
/*!40000 ALTER TABLE `ZBT_SUMMARY_2025` DISABLE KEYS */;
/*!40000 ALTER TABLE `ZBT_SUMMARY_2025` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `account_type` varchar(50) DEFAULT 'USER',
  `timestamp` int DEFAULT '0',
  `image` varchar(255) DEFAULT '',
  `status` varchar(50) DEFAULT 'Active',
  `login_attempts` int DEFAULT '0',
  `password_hash` varchar(255) NOT NULL,
  `saltiness` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `login_layers` varchar(10) DEFAULT '00000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@dev.local','Development Admin','ADMIN',0,'','Active::::Reset_password',3,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','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','2025-08-30 23:51:43','00000'),(2,'user@dev.local','Development User','USER',0,'','Active',0,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','','2025-08-30 23:51:43','00000'),(3,'test@test.com','Test User','USER',1756681757,'','Active',0,'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','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','2025-08-31 21:53:31','00000');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_dump`
--

DROP TABLE IF EXISTS `users_dump`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_dump` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `new_info` text,
  `old_info` text,
  `timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_dump`
--

LOCK TABLES `users_dump` WRITE;
/*!40000 ALTER TABLE `users_dump` DISABLE KEYS */;
INSERT INTO `users_dump` VALUES (1,NULL,'unauthorized_email','test@example.com','185.85.0.29',1756676815),(2,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756676849),(3,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756676894),(4,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"password\"}','185.85.0.29',1756676922),(5,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"test123\"}','185.85.0.29',1756676938),(6,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756677020),(7,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756677084),(8,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"Password\"}','185.85.0.29',1756677100),(9,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"Password\"}','185.85.0.29',1756677124),(10,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677217),(11,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677258),(12,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677370),(13,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677445),(14,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677471),(15,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756677525),(16,NULL,'unauthorized_email','test@rhombus.local','185.85.0.29',1756677539),(17,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677618),(18,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677698),(19,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677713),(20,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677934),(21,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677972),(22,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756677998),(23,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756678019),(24,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756678109),(25,3,'account_blocked_login_attempt','test@test.com','185.85.0.29',1756678278),(26,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756678302),(27,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"password\"}','185.85.0.29',1756678364),(28,3,'account_expired_login_attempt','test@test.com','185.85.0.29',1756678595),(29,3,'account_expired_login_attempt','test@test.com','185.85.0.29',1756678608),(30,3,'last_login','185.85.0.29','185.85.0.29',1756678627),(31,3,'last_login','185.85.0.29','185.85.0.29',1756678676),(32,3,'last_login','185.85.0.29','185.85.0.29',1756678702),(33,3,'last_login','185.85.0.29','185.85.0.29',1756679115),(34,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756680909),(35,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756680929),(36,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756680955),(37,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756681013),(38,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756681054),(39,3,'last_login','185.85.0.29','185.85.0.29',1756681180),(40,3,'invalid_password_login_attempt','{\"username\":\"test@test.com\",\"attempted_password\":\"Password\"}','185.85.0.29',1756681450),(41,3,'last_login','185.85.0.29','185.85.0.29',1756681757),(42,NULL,'unauthorized_email','admin@rhombus.local','185.85.0.29',1756685140),(43,NULL,'unauthorized_email','admin@rhombus.local','185.85.0.29',1756685152),(44,NULL,'unauthorized_email','admin@rhombus.local','185.85.0.29',1756685168),(45,NULL,'unauthorized_email','socom_admin@rhombus.local','185.85.0.29',1756685204),(46,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"password\"}','185.85.0.29',1756686030),(47,1,'invalid_password_login_attempt','{\"username\":\"admin@dev.local\",\"attempted_password\":\"password\"}','185.85.0.29',1756688756),(48,1,'reset_password','admin@dev.local','185.85.0.29',1756688760);
/*!40000 ALTER TABLE `users_dump` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'rhombus_db'
--

--
-- Dumping routines for database 'rhombus_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-01 15:47:12
