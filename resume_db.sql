-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: resume_db
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Current Database: `resume_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `resume_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `resume_db`;

--
-- Table structure for table `resume_entries`
--

DROP TABLE IF EXISTS `resume_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `entry_type` enum('experience','education','project') NOT NULL DEFAULT 'experience',
  `title` varchar(120) NOT NULL,
  `organization` varchar(120) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text NOT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_resume_user` (`user_id`),
  CONSTRAINT `fk_resume_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resume_entries`
--

LOCK TABLES `resume_entries` WRITE;
/*!40000 ALTER TABLE `resume_entries` DISABLE KEYS */;
INSERT INTO `resume_entries` VALUES (1,1,'project','Campus Lost & Found System','PHP & MySQL | Dong-eui University','2026-11-01',NULL,'A database-driven campus application for reporting, searching, and claiming lost belongings.','https://github.com/AnoujPandey/Campus-Lost-Found-System'),(2,1,'project','Library Borrowing System','C++ | Dong-eui University','2026-11-01',NULL,'An object-oriented library management system using inheritance, exception handling, and file operations.','https://github.com/AnoujPandey/Library-Borrowing-System'),(3,1,'project','Authentication System','C++','2026-11-01',NULL,'A C++ application for user registration, credential storage, and login verification.','https://github.com/AnoujPandey/Authentication_System'),(4,1,'project','Dynamic Resume CMS','PHP & MySQL | Web Programming II','2026-11-01',NULL,'A dynamic portfolio website featuring database-driven content, administrator authentication, and CRUD operations.','https://github.com/AnoujPandey/Dynamic-Resume-CMS'),(5,1,'education','B.Sc. Intelligence Computing','Dong-eui University','2025-03-01',NULL,'Undergraduate student in Intelligence Computing, Busan, South Korea.',NULL),(6,1,'education','Professional Graphics Design','Touch Institute','2024-01-01','2025-01-01','Training in graphic design, visual communication, and digital design tools.',NULL),(7,1,'education','Science - Biology and Mathematics','Kalika High School','2022-01-01','2024-01-01','Higher secondary education with a focus on science and mathematics.',NULL),(8,1,'experience','Web Development Intern','Example Tech Company','2026-06-01','2026-08-31','Assisted with website updates, tested web pages, fixed simple layout issues, and worked with team members on small development tasks.',NULL);
/*!40000 ALTER TABLE `resume_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `skill_level` tinyint(3) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_skill_user` (`user_id`),
  CONSTRAINT `fk_skill_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,1,'HTML / CSS',70,1),(2,1,'PHP',55,2),(3,1,'MySQL',55,3),(4,1,'Python',60,4),(5,1,'C / C++',45,5);
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `headline` varchar(150) NOT NULL,
  `summary` text NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Pandey Anouj','B.Sc. Intelligence Computing | Dong-eui University','Intelligence Computing student interested in technology, problem-solving, and practical projects.','20253692@deu.ac.kr','$2y$10$tMWNSkMsMOxNFbgvHPoSEOAiirPyuuCEi24gcHHaYj.ZIO.xWu2Ru','uploads/6dae42a31fa1046c5740b6554777d050.jpg');
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

-- Dump completed on 2026-11-23 17:59:44
