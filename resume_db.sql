-- Web Programming II | Assignment 2: Dynamic Resume CMS
-- Fresh installation only. Do not import over an existing resume_db.
-- Uses the existing three-table schema and updated resume records.
-- The administrator password is stored only as a PHP password hash.
CREATE DATABASE IF NOT EXISTS `resume_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resume_db`;
SET NAMES utf8mb4;

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

-- Initial profile, resume entries and skills
START TRANSACTION;
INSERT INTO `users` (`id`, `full_name`, `headline`, `summary`, `email`, `password`, `avatar_path`) VALUES
(1,'Pandey Anouj','B.Sc. Intelligence Computing | Dong-eui University','Intelligence Computing undergraduate at Dong-eui University with an interest in mathematics, artificial intelligence, data analysis, and software development. Experienced in academic projects involving Python, C++, PHP, and MySQL. Focused on developing practical technical skills, analytical thinking, and problem-solving abilities through coursework and collaborative projects.','20253692@deu.ac.kr','$2y$12$jtttrkXEtRpBq/vH1O40eeRy4NHPmDFxTQt4HJECqbmKGwTnTGbbq','uploads/6dae42a31fa1046c5740b6554777d050.jpg');

INSERT INTO `resume_entries` VALUES
(1,1,'project','Campus Lost & Found System','PHP & MySQL | Dong-eui University','2026-11-01',NULL,'A database-driven campus application for reporting, searching, and claiming lost belongings.','https://github.com/AnoujPandey/Campus-Lost-Found-System'),
(2,1,'project','Library Borrowing System','C++ | Dong-eui University','2026-11-01',NULL,'An object-oriented library management system using inheritance, exception handling, and file operations.','https://github.com/AnoujPandey/Library-Borrowing-System'),
(3,1,'project','Authentication System','C++','2026-11-01',NULL,'A C++ application for user registration, credential storage, and login verification.','https://github.com/AnoujPandey/Authentication_System'),
(4,1,'project','Dynamic Resume CMS','PHP & MySQL | Web Programming II','2026-11-01',NULL,'A dynamic portfolio website featuring database-driven content, administrator authentication, and CRUD operations.','https://github.com/AnoujPandey/Dynamic-Resume-CMS'),
(5,1,'education','B.Sc. Intelligence Computing','Dong-eui University','2025-03-01',NULL,'Undergraduate student in Intelligence Computing, Busan, South Korea.',NULL),
(6,1,'education','Professional Graphics Design','Touch Institute','2024-01-01','2025-01-01','Training in graphic design, visual communication, and digital design tools.',NULL),
(7,1,'education','Science - Biology and Mathematics','Kalika High School','2022-01-01','2024-01-01','Higher secondary education with a focus on science and mathematics.',NULL),
(8,1,'experience','Academic Software Development Projects','Dong-eui University','2025-03-01',NULL,'Completed individual and collaborative academic programming projects using Python, C, C++, PHP, MySQL, HTML, and CSS. Applied object-oriented programming, database management, web development, debugging, and problem-solving techniques. Collaborated with classmates on software development projects using Git and GitHub.',NULL);

INSERT INTO `skills` VALUES
(1,1,'HTML / CSS',70,1),
(2,1,'PHP',55,2),
(3,1,'MySQL',55,3),
(4,1,'Python',60,4),
(5,1,'C / C++',45,5);
COMMIT;
