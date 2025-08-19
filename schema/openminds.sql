-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 02, 2025 at 06:56 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `openminds`
--

-- --------------------------------------------------------

--
-- Table structure for table `experts`
--

DROP TABLE IF EXISTS `experts`;
CREATE TABLE IF NOT EXISTS `experts` (
  `user_id` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `experts`
--

INSERT INTO `experts` (`user_id`, `subject_id`) VALUES
(3, 1),
(3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

DROP TABLE IF EXISTS `request`;
CREATE TABLE IF NOT EXISTS `request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subject` varchar(50) NOT NULL,
  `description` text,
  `proof_link` text,
  `review` enum('pending','approved','rejected') DEFAULT 'pending',
  `feedback` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`id`, `user_id`, `time`, `subject`, `description`, `proof_link`, `review`, `feedback`) VALUES
(1, 1, '2025-07-29 10:59:10', 'Mathematics', 'I’ve mentored peers and created problem sets for calculus.', 'https://example.com/proof/math-alice', 'pending', NULL),
(2, 2, '2025-07-29 10:59:10', 'Physics', 'Published 3 articles on quantum mechanics, and taught it in university.', 'https://example.com/proof/quantum-bob', 'approved', NULL),
(3, 5, '2025-07-29 10:59:10', 'Computer Science', 'I’ve been learning CS for 3 months. I want to help others.', 'https://example.com/proof/eva-cs', 'rejected', 'Insufficient experience. Consider reapplying after more contributions.'),
(4, 1, '2025-07-29 10:59:10', 'Art History', 'Created visual lecture series on modern art movements.', 'https://example.com/proof/art-alice', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `name`) VALUES
(1, 'student'),
(2, 'mentor'),
(3, 'expert'),
(4, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Mathematics'),
(2, 'Physics'),
(3, 'Biology'),
(4, 'Computer Science'),
(5, 'Art History');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(75) NOT NULL,
  `role` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `banned` tinyint(1) DEFAULT '0',
  `profile_pic` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `role`, `created_at`, `banned`, `profile_pic`, `is_deleted`) VALUES
(1, 'alice123', 'hashed_pw1', 'alice@example.com', 1, '2025-07-29 10:58:45', 0, NULL, 0),
(2, 'bob_mentor', 'hashed_pw2', 'bob@example.com', 2, '2025-07-29 10:58:45', 0, NULL, 0),
(3, 'carol_expert', 'hashed_pw3', 'carol@example.com', 3, '2025-07-29 10:58:45', 0, NULL, 0),
(4, 'dave_admin', 'hashed_pw4', 'dave@example.com', 4, '2025-07-29 10:58:45', 0, NULL, 0),
(5, 'eva_student', 'hashed_pw5', 'eva@example.com', 1, '2025-07-29 10:58:45', 0, NULL, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
