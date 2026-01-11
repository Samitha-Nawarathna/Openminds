-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 10, 2026 at 04:36 AM
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
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_id` int DEFAULT NULL COMMENT 'The admin user who created this announcement',
  `style` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary-accent' COMMENT 'Style for UI display (e.g., primary-accent, warning)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 for hidden/archived, 1 for active/visible',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_announcement_creator` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `creator_id`, `style`, `is_active`, `created_at`, `updated_at`) VALUES
(2, '[Admin] System Maintenance Scheduled', 'A critical database update is scheduled for Saturday at 2 AM UTC. Expect 1 hour of downtime. Please save your work.', 2, 'warning', 1, '2025-11-27 01:31:21', '2025-11-27 02:31:21'),
(3, 'New Note Topics Added', 'Exciting news! We have added new topics for Notes in Historical Linguistics and Quantum Physics. Start exploring!', 1, 'info', 0, '2025-11-20 02:31:21', '2025-11-30 03:52:33'),
(4, 'Old Announcement (Hidden)', 'This is an old test announcement that should be hidden from the public feeds.', 2, 'default', 0, '2025-10-27 02:31:21', '2025-11-30 03:52:18'),
(5, 'title', 'contenteb', 102, 'warning', 1, '2025-11-30 06:49:57', '2025-11-30 07:25:55'),
(6, 'new title', 'content', 102, 'info', 1, '2025-11-30 07:25:18', '2025-11-30 07:25:18');

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
CREATE TABLE IF NOT EXISTS `answer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `creator_id` int DEFAULT NULL,
  `q_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `chosen` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if this is the chosen or accepted answer, 0 otherwise',
  PRIMARY KEY (`id`),
  KEY `q_id` (`q_id`),
  KEY `fk_answer_creator` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`id`, `content`, `creator_id`, `q_id`, `created_at`, `chosen`) VALUES
(1, 'Machine learning uses algorithms that learn from data to make predictions.', 2, 1, '2025-10-16 06:18:32', 0),
(2, 'INNER JOIN returns matching rows; LEFT JOIN keeps all left-side rows.', 3, 2, '2025-10-16 06:18:32', 0),
(3, 'TCP is reliable but slower; UDP is faster but doesn’t guarantee delivery.', 4, 3, '2025-10-16 06:18:32', 0),
(4, 'Use flexbox or grid to center elements both vertically and horizontally.', 42, 4, '2025-10-16 06:18:32', 0),
(5, 'Action and reaction forces are equal and opposite.', 43, 5, '2025-10-16 06:18:32', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answer`
--

DROP TABLE IF EXISTS `attempt_answer`;
CREATE TABLE IF NOT EXISTS `attempt_answer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL COMMENT 'Foreign Key to exercise_attempt table',
  `question_id` int NOT NULL COMMENT 'Foreign Key to question table',
  `user_response` text NOT NULL COMMENT 'The user''s submitted answer, choice ID, or response text',
  `is_correct` tinyint(1) DEFAULT NULL COMMENT '1 if the response was correct, 0 if incorrect',
  `score_earned` decimal(5,2) DEFAULT NULL COMMENT 'Points earned for this specific question',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_attempt_question` (`attempt_id`,`question_id`),
  KEY `fk_answer_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores the user''s response for each question within an exercise attempt';

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'The ID of the user who initiated the event (FK to user.id)',
  `event_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The exact time the event occurred',
  `event_type` varchar(50) NOT NULL COMMENT 'e.g., note_created, question_asked, exercise_attempted, vote_given',
  `entity_type` varchar(50) NOT NULL COMMENT 'The type of entity involved (e.g., Note, Question, Exercise, Answer)',
  `entity_id` int DEFAULT NULL COMMENT 'The ID of the related entity in its respective table',
  `data` json DEFAULT NULL COMMENT 'Flexible storage for metric-critical data (e.g., score, subject_id, vote_direction)',
  PRIMARY KEY (`id`),
  KEY `idx_user_time_type` (`user_id`,`event_time`,`event_type`),
  KEY `idx_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `event_time`, `event_type`, `entity_type`, `entity_id`, `data`) VALUES
(1, 1, '2025-11-07 10:03:01', 'note_created', 'Note', 1, '{\"title\": \"My First Logged Note\", \"subject_id\": 5}'),
(2, 1, '2025-11-08 10:03:01', 'exercise_attempted', 'Exercise', 1, '{\"score\": 85.5, \"subject_id\": 5}'),
(3, 1, '2025-11-08 10:03:01', 'vote_given', 'Question', 2, '{\"direction\": \"upvote\"}'),
(4, 2, '2025-11-09 10:03:01', 'question_asked', 'Question', 3, '{\"subject_id\": 8}'),
(5, 2, '2025-11-09 10:03:01', 'note_created', 'Note', 2, '{\"title\": \"Second User Note\", \"subject_id\": 8}'),
(6, 3, '2025-11-10 10:03:01', 'exercise_attempted', 'Exercise', 2, '{\"score\": 92.0, \"subject_id\": 5}'),
(7, 3, '2025-11-10 10:03:01', 'note_updated', 'Note', 1, '{\"subject_id\": 5}'),
(8, 1, '2025-11-10 10:03:01', 'exercise_attempted', 'Exercise', 3, '{\"score\": 78.0, \"subject_id\": 5}'),
(9, 1, '2025-11-01 10:03:01', 'note_created', 'Note', 3, '{\"title\": \"Old Note 1\", \"subject_id\": 5}'),
(10, 1, '2025-11-02 10:03:01', 'exercise_attempted', 'Exercise', 4, '{\"score\": 75.0, \"subject_id\": 5}'),
(11, 2, '2025-11-03 10:03:01', 'note_created', 'Note', 4, '{\"title\": \"Old Note 2\", \"subject_id\": 8}'),
(12, 3, '2025-11-04 10:03:01', 'exercise_attempted', 'Exercise', 5, '{\"score\": 88.0, \"subject_id\": 5}'),
(13, 3, '2025-11-04 10:03:01', 'question_answered', 'Answer', 10, '{\"is_accepted\": true}'),
(14, 1, '2025-10-21 10:03:01', 'note_created', 'Note', 5, '{\"subject_id\": 1}'),
(15, 2, '2025-10-21 10:03:01', 'question_asked', 'Question', 5, '{\"subject_id\": 1}'),
(16, 3, '2025-10-21 10:03:01', 'exercise_attempted', 'Exercise', 6, '{\"score\": 95.0, \"subject_id\": 1}'),
(17, 1, '2025-10-14 10:03:01', 'note_created', 'Note', 6, '{\"subject_id\": 10}'),
(18, 2, '2025-10-14 10:03:01', 'exercise_attempted', 'Exercise', 7, '{\"score\": 65.0, \"subject_id\": 10}'),
(19, 3, '2025-10-14 10:03:01', 'note_deleted', 'Note', 4, '{}'),
(20, 1, '2025-11-11 10:03:01', 'vote_given', 'Answer', 5, '{\"direction\": \"downvote\"}'),
(21, 2, '2025-11-11 10:03:01', 'question_answered', 'Answer', 6, '{\"is_accepted\": false}'),
(22, 3, '2025-11-11 10:03:01', 'exercise_attempted', 'Exercise', 8, '{\"score\": 80.0, \"subject_id\": 8}'),
(23, 1, '2025-11-06 10:03:01', 'note_created', 'Note', 7, '{\"title\": \"Latest Note\", \"subject_id\": 2}'),
(24, 2, '2025-11-05 10:03:01', 'exercise_attempted', 'Exercise', 9, '{\"score\": 70.0, \"subject_id\": 2}'),
(25, 3, '2025-10-30 10:03:01', 'note_created', 'Note', 8, '{\"title\": \"Last Month Note\", \"subject_id\": 10}'),
(26, 1, '2025-10-27 10:03:01', 'question_asked', 'Question', 6, '{\"subject_id\": 2}'),
(27, 2, '2025-10-22 10:03:01', 'exercise_attempted', 'Exercise', 10, '{\"score\": 89.0, \"subject_id\": 1}'),
(28, 1, '2025-11-25 00:07:24', 'note_refered', 'notes', 101, '{\"duration_seconds\": 125}'),
(29, 2, '2025-11-25 00:07:24', 'note_refered', 'notes', 102, '{\"duration_seconds\": 305}'),
(30, 3, '2025-11-25 00:07:24', 'note_refered', 'notes', 101, '{\"duration_seconds\": 45}');

-- --------------------------------------------------------

--
-- Table structure for table `exerciseanswer`
--

DROP TABLE IF EXISTS `exerciseanswer`;
CREATE TABLE IF NOT EXISTS `exerciseanswer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `answer_text` varchar(1000) NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  `display_order` int NOT NULL,
  `question_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exerciseanswer`
--

INSERT INTO `exerciseanswer` (`id`, `answer_text`, `is_correct`, `display_order`, `question_id`) VALUES
(1, 'x = 2', 1, 0, 1),
(2, 'x = 3', 0, 0, 1),
(3, 'Newton’s Third Law', 1, 0, 2),
(4, 'Newton’s First Law', 0, 0, 2),
(7, 'for i in range(5):', 1, 0, 4),
(8, 'loop i from 1 to 5', 0, 0, 4),
(35, 'Option A', 0, 0, 18),
(36, 'Option B', 0, 0, 18),
(37, 'Option A', 0, 0, 19),
(38, 'Option B', 0, 0, 19),
(39, 'Option A', 0, 0, 20),
(40, 'Option B', 0, 0, 20),
(41, 'Option A', 0, 0, 21),
(42, 'Option B', 0, 0, 21),
(57, 'Option A', 0, 0, 27),
(58, 'Option B', 1, 0, 27),
(59, 'option c', 0, 0, 27),
(60, 'Option A', 1, 0, 28),
(61, 'Option B', 0, 0, 28),
(75, 'Option A', 1, 0, 34),
(76, 'Option B', 0, 0, 34),
(79, 'Option A', 0, 0, 36),
(80, 'Option B', 1, 0, 36),
(81, 'oc', 0, 0, 36),
(82, 'Option A', 1, 0, 37),
(83, 'Option c', 0, 0, 37),
(84, 'Option A', 1, 0, 38),
(85, 'Option B', 0, 0, 38),
(86, 'Option A', 0, 0, 39),
(87, 'Option B', 1, 0, 39),
(88, 'Option A', 0, 0, 40),
(89, 'Option B', 1, 0, 40),
(90, 'Option A', 0, 0, 41),
(91, 'Option B', 1, 0, 41),
(92, 'option C', 0, 0, 41),
(93, 'Option A', 1, 0, 42),
(94, 'Option B', 0, 0, 42),
(95, 'Option A', 0, 0, 43),
(96, 'Option B', 1, 0, 43),
(97, 'option c', 1, 0, 43),
(98, 'Option A', 0, 0, 44),
(99, 'Option B', 1, 0, 44),
(100, 'options c', 1, 0, 44),
(101, 'Option A', 1, 0, 45),
(102, 'Option B', 0, 0, 45),
(103, 'Option A', 1, 0, 46),
(104, 'Option B', 0, 0, 46),
(105, 'Option C', 1, 0, 46),
(106, 'Option A', 1, 0, 47),
(107, 'Option B', 0, 0, 47),
(108, 'Option A', 0, 0, 48),
(109, 'Option B', 1, 0, 48),
(110, 'Option A', 1, 0, 49),
(111, 'Option B', 0, 0, 49),
(112, '{\"ops\":[{\"insert\":\"1\\n\"}]}', 1, 0, 50),
(113, '{\"ops\":[{\"insert\":\"2\\n\"}]}', 0, 1, 50),
(114, '{\"ops\":[{\"insert\":\"1\\n\"}]}', 1, 0, 51),
(115, '{\"ops\":[{\"insert\":\"2\\n\"}]}', 0, 1, 51),
(116, '{\"ops\":[{\"insert\":\"1\\n\"}]}', 1, 0, 52),
(117, '{\"ops\":[{\"insert\":\"2\\n\"}]}', 0, 1, 52),
(118, '{\"ops\":[{\"insert\":\"\\n\"}]}', 1, 0, 53),
(119, '{\"ops\":[{\"insert\":\"\\n\"}]}', 0, 1, 53);

-- --------------------------------------------------------

--
-- Table structure for table `exercisequestion`
--

DROP TABLE IF EXISTS `exercisequestion`;
CREATE TABLE IF NOT EXISTS `exercisequestion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `explanation` text NOT NULL,
  `weight` int NOT NULL DEFAULT '1',
  `exercise_id` int NOT NULL,
  `display_order` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `exercise_id` (`exercise_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exercisequestion`
--

INSERT INTO `exercisequestion` (`id`, `question_text`, `explanation`, `weight`, `exercise_id`, `display_order`) VALUES
(1, 'What is the solution of 2x + 3 = 7?', '', 1, 1, 0),
(2, 'Which law states that for every action, there is an equal and opposite reaction?', '', 1, 2, 0),
(4, 'What is the correct Python syntax for a for loop?', '', 1, 4, 0),
(18, 'question 2', '', 1, 14, 0),
(19, 'question 1', '', 1, 14, 0),
(20, 'question 2', '', 1, 15, 0),
(21, 'question 1', '', 1, 15, 0),
(27, 'q1', '', 1, 19, 0),
(28, 'q1', '', 1, 20, 0),
(34, 'q1', '', 1, 25, 0),
(36, 'q2', '', 1, 27, 0),
(37, 'q1', '', 1, 27, 0),
(38, 'q1', '', 1, 28, 0),
(39, 'q2', '', 1, 28, 0),
(40, 'q1', '', 1, 29, 0),
(41, 'q1', '', 1, 30, 0),
(42, 'q2', '', 1, 31, 0),
(43, 'q1', '', 1, 31, 0),
(44, 'q1', '', 1, 32, 0),
(45, 'q2', '', 1, 32, 0),
(46, 'question 1', '', 1, 33, 0),
(47, 'question 1', '', 1, 34, 0),
(48, 'question 1', '', 1, 35, 0),
(49, 'q1', '', 1, 36, 0),
(50, '{\"ops\":[{\"insert\":\"science\\n\"}]}', '{\"ops\":[{\"insert\":\"d\\n\"}]}', 1, 37, 0),
(51, '{\"ops\":[{\"insert\":\"science\\n\"}]}', '{\"ops\":[{\"insert\":\"d\\n\"}]}', 1, 37, 1),
(52, '{\"ops\":[{\"insert\":\"science\\n\"}]}', '{\"ops\":[{\"insert\":\"d\\n\"}]}', 1, 37, 2),
(53, '{\"ops\":[{\"insert\":\"science\\n\"}]}', '{\"ops\":[{\"insert\":\"d\\n\"}]}', 1, 37, 3);

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

DROP TABLE IF EXISTS `exercises`;
CREATE TABLE IF NOT EXISTS `exercises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `creator_id` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `feedback` text,
  `reviewed_by` int DEFAULT NULL,
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `creator_id` (`creator_id`),
  KEY `reviewed_by` (`reviewed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `subject_id`, `title`, `created_at`, `creator_id`, `status`, `feedback`, `reviewed_by`, `description`, `updated_at`) VALUES
(1, 1, 'Basic Algebra Practice', '2025-10-17 22:06:22', 1, 'approved', NULL, 3, NULL, '2025-11-19 03:34:17'),
(2, 2, 'Newton Laws Challenge', '2025-10-17 22:06:22', 5, 'approved', NULL, 3, NULL, '2025-11-19 03:34:17'),
(4, 3, 'Python Loop Exercises', '2025-10-17 22:06:22', 40, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(14, 2, 'Test exercise', '2025-10-21 01:48:59', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(15, 2, 'Test exercise', '2025-10-21 01:51:23', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(19, 2, 'physics', '2025-10-22 03:40:02', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(20, 2, 'physics', '2025-10-22 03:46:26', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(25, 2, 'title', '2025-10-22 23:01:51', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(27, 2, 'title', '2025-10-23 00:23:14', 94, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(28, 2, 'title', '2025-10-23 00:34:56', 94, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(29, 2, 'title', '2025-10-23 00:55:56', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(30, 2, 'title', '2025-10-23 02:03:00', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(31, 2, 'title', '2025-10-23 02:37:15', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(32, 2, 'title', '2025-10-23 02:59:03', 91, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(33, 2, 'title', '2025-11-06 19:41:38', 1, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(34, 2, 'title', '2025-11-06 22:48:23', 1, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(35, 2, 'title', '2025-11-07 07:17:16', 1, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(36, 2, 'title', '2025-11-14 02:14:02', 101, 'pending', NULL, NULL, NULL, '2025-11-19 03:34:17'),
(37, 7, 'science', '2025-12-30 21:19:06', 1, 'pending', NULL, NULL, NULL, '2025-12-31 02:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `exercisetag`
--

DROP TABLE IF EXISTS `exercisetag`;
CREATE TABLE IF NOT EXISTS `exercisetag` (
  `exercise_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`exercise_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exercisetag`
--

INSERT INTO `exercisetag` (`exercise_id`, `tag_id`) VALUES
(4, 8),
(1, 9),
(2, 9),
(1, 10),
(2, 10),
(14, 18),
(15, 18),
(33, 18),
(29, 24),
(34, 25),
(20, 27),
(27, 27),
(14, 28),
(15, 28),
(33, 28),
(34, 28),
(35, 28),
(36, 28),
(19, 32),
(19, 33),
(19, 34),
(25, 39),
(28, 43),
(31, 43),
(32, 43),
(30, 46),
(37, 53);

-- --------------------------------------------------------

--
-- Table structure for table `exercise_attempt`
--

DROP TABLE IF EXISTS `exercise_attempt`;
CREATE TABLE IF NOT EXISTS `exercise_attempt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exe_id` int NOT NULL COMMENT 'Foreign Key to exercises table',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time of the attempt',
  `u_id` int NOT NULL COMMENT 'Foreign Key to user table',
  `score` decimal(5,2) DEFAULT NULL COMMENT 'Score achieved in this attempt',
  `latest` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if this is the user''s latest attempt for the exercise',
  PRIMARY KEY (`id`),
  KEY `idx_exe_u_latest` (`exe_id`,`u_id`,`latest`),
  KEY `fk_attempt_user` (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores each attempt a user makes on an exercise';

-- --------------------------------------------------------

--
-- Stand-in structure for view `exercise_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `exercise_summary`;
CREATE TABLE IF NOT EXISTS `exercise_summary` (
`attempt_count` bigint
,`created_at` timestamp
,`creator_id` int
,`exercise_id` int
,`exercise_title` varchar(255)
,`question_count` bigint
,`subject_name` varchar(50)
,`tag_id` int
,`tag_name` varchar(255)
);

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
(3, 4),
(102, 6);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `topic_id` int DEFAULT NULL,
  `owner_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pinned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if the note is pinned, 0 otherwise',
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `fk_note_topic` (`topic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `topic_id`, `owner_id`, `created_at`, `updated_at`, `pinned`) VALUES
(1, 'Vector Spaces', 'A vector space is a collection of objects called vectors...', 1, 1, '2025-10-18 03:51:38', '2025-10-18 03:51:38', 0),
(2, 'Matrix Transformations', 'Matrix multiplication can be seen as a linear transformation...', 1, 1, '2025-10-18 03:51:38', '2025-10-18 03:51:38', 0),
(3, 'Gradient Descent', 'An optimization algorithm used to minimize loss functions...', 2, 5, '2025-10-18 03:51:38', '2025-10-18 03:51:38', 0),
(4, 'Bias vs Variance', 'Bias is systematic error; variance is sensitivity to data...', 2, 5, '2025-10-18 03:51:38', '2025-10-18 03:51:38', 0),
(6, 'Relativity Overview', 'Einstein’s relativity redefined space and time...', 4, 40, '2025-10-18 03:51:38', '2025-10-18 03:51:38', 0),
(13, 't', 's', NULL, 91, '2025-10-21 12:31:01', '2025-10-21 12:31:01', 0),
(14, 'title', 'contente', NULL, 91, '2025-10-21 12:43:00', '2025-10-21 12:43:00', 0),
(16, 'tvbygbybhuun', 'ygbnu', NULL, 91, '2025-10-22 14:23:57', '2025-10-22 14:23:57', 0),
(20, 'oefkv', 'wd.vwkh', NULL, 91, '2025-10-23 09:43:49', '2025-10-23 09:43:49', 0),
(21, 'title', 'content', NULL, 91, '2025-10-23 09:56:20', '2025-10-23 09:56:20', 0),
(28, 'title', 'content', NULL, 94, '2025-10-23 11:16:45', '2025-10-23 11:16:45', 0),
(29, 'a', 'c', NULL, 94, '2025-10-23 11:17:30', '2025-10-23 11:17:30', 0),
(31, 'title', 'content', NULL, 94, '2025-10-23 13:33:03', '2025-10-23 13:33:03', 0),
(32, 'title', 'note', NULL, 94, '2025-10-23 13:54:40', '2025-10-23 13:54:40', 0),
(33, 'title', 'note', NULL, 94, '2025-10-23 15:01:38', '2025-10-23 15:01:38', 0),
(34, 'title', 'content', NULL, 1, '2025-10-28 05:59:16', '2025-10-28 05:59:16', 0),
(35, 'title', '{\"ops\":[{\"insert\":\"science s\\n\"}]}', 6, 101, '2025-11-20 05:19:04', '2025-11-20 05:19:04', 0),
(36, 'title', '{\"ops\":[{\"insert\":\"s\\n\"}]}', 6, 101, '2025-11-20 05:22:47', '2025-11-20 05:22:47', 0),
(38, 'sciene', '{\"ops\":[{\"insert\":\"sss\\n\"}]}', 7, 102, '2025-11-26 07:15:26', '2025-11-26 07:15:26', 0),
(39, 'science note', '{\"ops\":[{\"insert\":\"something \"},{\"attributes\":{\"bold\":true},\"insert\":\"here\"},{\"insert\":\"\\n\"}]}', 8, 102, '2025-11-26 07:19:28', '2025-11-26 07:19:28', 0),
(40, 'science', '{\"ops\":[{\"insert\":\"sci \\n\"}]}', 9, 1, '2025-12-09 16:03:21', '2025-12-09 16:03:21', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `note_detail_view`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `note_detail_view`;
CREATE TABLE IF NOT EXISTS `note_detail_view` (
`content` text
,`last_update` datetime
,`note_id` int
,`share_count` bigint
,`tag_id` int
,`tag_name` varchar(255)
,`title` varchar(255)
,`topic_id` int
,`topic_name` varchar(255)
,`total_refer_time` double
,`user_id` int
);

-- --------------------------------------------------------

--
-- Table structure for table `note_shares`
--

DROP TABLE IF EXISTS `note_shares`;
CREATE TABLE IF NOT EXISTS `note_shares` (
  `note_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`note_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `note_shares`
--

INSERT INTO `note_shares` (`note_id`, `user_id`) VALUES
(3, 1),
(4, 2),
(1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `note_tags`
--

DROP TABLE IF EXISTS `note_tags`;
CREATE TABLE IF NOT EXISTS `note_tags` (
  `note_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`note_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `note_tags`
--

INSERT INTO `note_tags` (`note_id`, `tag_id`) VALUES
(1, 2),
(2, 2),
(3, 3),
(4, 6),
(13, 9),
(14, 9),
(20, 9),
(34, 9),
(6, 10),
(28, 17),
(29, 17),
(16, 18),
(13, 25),
(16, 25),
(39, 25),
(13, 28),
(14, 28),
(21, 36),
(21, 37),
(31, 37),
(33, 37),
(31, 47),
(32, 48),
(33, 48),
(32, 49),
(34, 49),
(34, 51),
(36, 51),
(38, 51),
(39, 51),
(40, 51),
(35, 52),
(39, 52);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(1, 0, 39, 'You have successfully logged in.', 0, '2025-09-14 00:48:43'),
(2, 0, 39, 'You have successfully logged in.', 0, '2025-09-14 01:23:44'),
(3, 0, 39, 'You have successfully logged in.', 0, '2025-09-15 01:02:26'),
(4, 0, 39, 'You have successfully logged in.', 0, '2025-09-16 00:55:33'),
(5, 0, 39, 'You have successfully logged in.', 0, '2025-10-15 07:17:46'),
(6, 0, 39, 'You have successfully logged in.', 0, '2025-10-16 03:50:09'),
(7, 0, 39, 'You have successfully logged in.', 0, '2025-10-16 14:08:09'),
(8, 0, 39, 'You have successfully logged in.', 0, '2025-10-16 14:13:11'),
(9, 0, 39, 'You have successfully logged in.', 0, '2025-10-17 08:32:00'),
(10, 0, 39, 'You have successfully logged in.', 0, '2025-10-18 22:08:49'),
(11, 0, 39, 'You have successfully logged in.', 0, '2025-10-20 08:03:01'),
(12, 0, 39, 'You have successfully logged in.', 0, '2025-10-20 08:08:09'),
(13, 0, 39, 'You have successfully logged in.', 0, '2025-10-20 08:53:43'),
(14, 0, 39, 'You have successfully logged in.', 0, '2025-10-21 00:25:18'),
(15, 92, 39, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-21 00:59:11'),
(16, 0, 39, 'You have successfully logged in.', 0, '2025-10-21 12:33:14'),
(17, 0, 39, 'You have successfully logged in.', 0, '2025-10-22 02:05:54'),
(18, 0, 92, 'You have successfully logged in.', 0, '2025-10-22 02:09:22'),
(19, 0, 91, 'You have successfully logged in.', 0, '2025-10-22 02:17:02'),
(20, 39, 93, 'Your user role has been changed to mentor.', 0, '2025-10-22 02:29:33'),
(21, 39, 91, 'Your user role has been changed to mentor.', 0, '2025-10-22 02:30:24'),
(22, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-22 02:30:40'),
(23, 0, 39, 'You have successfully logged in.', 0, '2025-10-22 07:09:57'),
(24, 39, 91, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-22 07:15:36'),
(25, 39, 91, 'Your user role has been changed to mentor.', 0, '2025-10-22 07:17:24'),
(26, 0, 39, 'You have successfully logged in.', 0, '2025-10-22 08:48:44'),
(27, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-22 08:49:04'),
(28, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-22 21:20:12'),
(29, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-22 21:21:15'),
(30, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-22 21:25:01'),
(31, 0, 39, 'You have successfully logged in.', 0, '2025-10-22 23:05:43'),
(32, 39, 93, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 04:37:25'),
(33, 39, 1, 'Your user role has been changed to expert.', 0, '2025-10-23 04:48:10'),
(34, 39, 91, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 05:01:21'),
(35, 39, 93, 'Your user role has been changed to mentor.', 0, '2025-10-23 05:07:34'),
(36, 39, 91, 'Your user role has been changed to student.', 0, '2025-10-23 05:07:52'),
(37, 39, 91, 'Your user role has been changed to mentor.', 0, '2025-10-23 05:16:28'),
(38, 39, 93, 'Your user role has been changed to student.', 0, '2025-10-23 05:16:49'),
(39, 39, 95, 'Your user role has been changed to mentor.', 0, '2025-10-23 05:37:46'),
(40, 39, 94, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 05:57:16'),
(41, 39, 96, 'Your user role has been changed to expert.', 0, '2025-10-23 06:18:24'),
(42, 0, 92, 'You have successfully logged in.', 0, '2025-10-23 06:30:40'),
(43, 92, 91, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 06:32:27'),
(44, 92, 94, 'Your user role has been changed to student.', 0, '2025-10-23 06:36:13'),
(45, 92, 91, 'Your user role has been changed to mentor.', 0, '2025-10-23 06:36:41'),
(46, 0, 97, 'You have successfully logged in.', 0, '2025-10-23 07:12:28'),
(47, 0, 94, 'You have successfully logged in.', 0, '2025-10-23 07:28:55'),
(48, 97, 98, 'Your user role has been changed to mentor.', 0, '2025-10-23 08:00:36'),
(49, 97, 98, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 08:11:29'),
(50, 97, 99, 'Your user role has been changed to expert.', 0, '2025-10-23 08:22:08'),
(51, 97, 91, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 08:32:02'),
(52, 97, 91, 'Your user role has been changed to mentor.', 0, '2025-10-23 08:34:56'),
(53, 97, 100, 'Your user role has been changed to expert.', 0, '2025-10-23 09:29:49'),
(54, 97, 92, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-10-23 09:39:23'),
(55, 97, 2, 'Your user role has been changed to student.', 0, '2025-10-25 06:11:22'),
(56, 101, 101, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-11-14 08:14:52'),
(57, 102, 102, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-11-25 10:28:25'),
(58, 102, 102, 'Your expert request has been approved. You can now access expert features on our platform.', 0, '2025-11-25 11:18:39'),
(59, 102, 2, 'Your user role has been changed to mentor.', 0, '2025-12-02 05:59:15'),
(60, 102, 102, 'Your user role has been changed to expert.', 0, '2025-12-02 06:20:27'),
(61, 102, 102, 'Your user role has been changed to student.', 0, '2025-12-02 06:21:49'),
(62, 102, 102, 'Your user role has been changed to student.', 0, '2025-12-02 06:22:29'),
(63, 102, 102, 'Your user role has been changed to expert.', 0, '2025-12-02 06:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

DROP TABLE IF EXISTS `otp_codes`;
CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `type` enum('registration','login','change_password','accountverification') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'registration',
  `is_used` tinyint(1) DEFAULT '0',
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `username`, `email`, `code`, `type`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 'Samitha', 'samithanawarathna528@gmail.com', '525646', 'registration', 0, '2025-08-02 22:01:38', '2025-08-03 03:26:42'),
(2, 'Samitha', 'samithanawarathna528@gmail.com', '974084', 'registration', 0, '2025-08-02 22:04:04', '2025-08-03 03:29:09'),
(3, 'Samitha', 'samithanawarathna528@gmail.com', '951284', 'registration', 0, '2025-08-02 22:04:24', '2025-08-03 03:29:29'),
(4, 'Samitha', 'samithanawarathna528@gmail.com', '779521', 'registration', 0, '2025-08-02 22:07:08', '2025-08-03 03:32:12'),
(5, 'Samitha', 'samithanawarathna528@gmail.com', '475925', 'registration', 0, '2025-08-02 22:38:26', '2025-08-03 04:03:30'),
(6, 'Samitha', 'samithanawarathna528@gmail.com', '357273', 'registration', 0, '2025-08-02 22:40:07', '2025-08-03 04:05:12'),
(7, 'Samitha', 'samithanawarathna528@gmail.com', '144553', 'registration', 0, '2025-08-02 22:44:18', '2025-08-03 04:09:23'),
(8, 'Samitha', 'samithanawarathna528@gmail.com', '861246', 'registration', 0, '2025-08-03 02:12:35', '2025-08-03 07:37:39'),
(9, 'Samitha', 'samithanawarathna528@gmail.com', '703051', 'registration', 0, '2025-08-03 02:14:26', '2025-08-03 07:39:30'),
(10, 'Samitha', 'samithanawarathna528@gmail.com', '327515', NULL, 0, '2025-08-03 02:22:58', '2025-08-03 07:48:03'),
(11, 'Samitha', 'samithanawarathna528@gmail.com', '874868', NULL, 0, '2025-08-03 02:24:18', '2025-08-03 07:49:22'),
(12, 'Samitha', 'samithanawarathna528@gmail.com', '928269', NULL, 0, '2025-08-03 02:25:30', '2025-08-03 07:50:34'),
(13, 'Samitha', 'samithanawarathna528@gmail.com', '233948', NULL, 0, '2025-08-03 02:29:01', '2025-08-03 07:54:05'),
(14, 'Samitha', 'samithanawarathna528@gmail.com', '322668', 'registration', 0, '2025-08-03 02:30:13', '2025-08-03 07:55:16'),
(15, 'Samitha', 'samithanawarathna528@gmail.com', '796309', 'registration', 0, '2025-08-03 02:30:28', '2025-08-03 07:55:32'),
(16, 'Samitha', 'samithanawarathna528@gmail.com', '455002', 'registration', 0, '2025-08-03 02:38:38', '2025-08-03 08:03:43'),
(17, 'Samitha', 'samithanawarathna528@gmail.com', '791745', 'registration', 0, '2025-08-03 04:02:24', '2025-08-03 09:27:29'),
(18, 'Samitha', 'samithanawarathna528@gmail.com', '663253', 'registration', 0, '2025-08-03 04:11:34', '2025-08-03 09:36:38'),
(19, 'Saitha', 'samitanawarathna528@gmail.com', '938334', 'registration', 0, '2025-08-03 08:16:39', '2025-08-03 13:41:44'),
(20, 'Samitha', 'samithanawarathna528@gmail.com', '820142', 'login', 0, '2025-08-03 11:10:45', '2025-08-03 16:35:50'),
(21, 'Samitha', 'samithanawarathna528@gmail.com', '688735', 'login', 0, '2025-08-03 11:14:20', '2025-08-03 16:39:24'),
(22, 'Samitha2', 'animelearnin528@gmail.com', '174443', 'registration', 0, '2025-08-04 00:01:09', '2025-08-04 05:26:14'),
(23, 'Samitha2', 'animelearnin528@gmail.com', '845708', 'login', 0, '2025-08-04 00:07:01', '2025-08-04 05:32:06'),
(24, 'Samitha2', 'animelearnin528@gmail.com', '480262', 'login', 0, '2025-08-04 00:11:18', '2025-08-04 05:36:23'),
(25, 'Samitha2', 'animelearnin528@gmail.com', '308468', 'login', 0, '2025-08-04 00:12:58', '2025-08-04 05:38:03'),
(26, 'Samitha2', 'animelearnin528@gmail.com', '979277', 'login', 0, '2025-08-04 00:14:50', '2025-08-04 05:39:54'),
(27, 'Samitha2', 'animelearnin528@gmail.com', '139528', 'login', 0, '2025-08-04 00:15:26', '2025-08-04 05:40:31'),
(28, 'Samitha2', 'animelearnin528@gmail.com', '972942', 'login', 0, '2025-08-04 00:15:37', '2025-08-04 05:40:42'),
(29, 'Samitha', 'samithanawarathna528@gmail.com', '922359', 'login', 0, '2025-08-04 10:48:54', '2025-08-04 16:13:59'),
(30, 'Samitha', 'samithanawarathna528@gmail.com', '157205', 'login', 0, '2025-08-04 10:59:29', '2025-08-04 16:24:33'),
(31, 'Samitha', 'Samithanawarathna528@gmail.com', '276011', 'registration', 0, '2025-08-05 00:31:49', '2025-08-05 05:56:54'),
(32, 'Samitha', 'Samithanawarathna528@gmail.com', '625283', 'login', 0, '2025-08-05 00:35:39', '2025-08-05 06:00:44'),
(33, 'Samitha', 'Samithanawarathna528@gmail.com', '720437', 'registration', 0, '2025-08-05 00:37:43', '2025-08-05 06:02:47'),
(34, 'Samitha', 'Samithanawarathna528@gmail.com', '815724', 'login', 0, '2025-08-05 00:42:59', '2025-08-05 06:08:03'),
(35, 'Samitha', 'Samithanawarathna528@gmail.com', '635251', 'login', 0, '2025-08-05 00:43:55', '2025-08-05 06:09:00'),
(36, 'Samitha', 'Samithanawarathna528@gmail.com', '884869', 'login', 0, '2025-08-05 00:49:27', '2025-08-05 06:14:32'),
(37, 'Samitha', 'Samithanawarathna528@gmail.com', '764754', 'login', 0, '2025-08-05 00:51:00', '2025-08-05 06:16:04'),
(38, 'Samitha', 'Samithanawarathna528@gmail.com', '342871', 'login', 0, '2025-08-05 00:51:35', '2025-08-05 06:16:39'),
(39, 'Samitha', 'Samithanawarathna528@gmail.com', '755146', 'login', 0, '2025-08-05 00:52:24', '2025-08-05 06:17:29'),
(40, 'Samitha', 'Samithanawarathna528@gmail.com', '646016', 'login', 0, '2025-08-05 00:53:01', '2025-08-05 06:18:05'),
(41, 'Samitha', 'Samithanawarathna528@gmail.com', '212150', 'login', 0, '2025-08-05 00:53:54', '2025-08-05 06:18:58'),
(42, 'Samitha', 'Samithanawarathna528@gmail.com', '189874', 'login', 0, '2025-08-05 00:54:23', '2025-08-05 06:19:27'),
(43, 'Samitha', 'Samithanawarathna528@gmail.com', '178664', 'login', 0, '2025-08-05 00:55:03', '2025-08-05 06:20:08'),
(44, 'Samitha', 'Samithanawarathna528@gmail.com', '582254', 'login', 0, '2025-08-05 04:50:58', '2025-08-05 10:16:04'),
(45, 'Samitha', 'Samithanawarathna528@gmail.com', '535872', 'login', 0, '2025-08-05 04:52:01', '2025-08-05 10:17:06'),
(46, 'Samitha', 'Samithanawarathna528@gmail.com', '251370', 'login', 0, '2025-08-05 04:55:28', '2025-08-05 10:20:32'),
(47, 'Samitha', 'Samithanawarathna528@gmail.com', '868987', 'login', 0, '2025-08-05 04:56:17', '2025-08-05 10:21:21'),
(48, 'Samitha', 'Samithanawarathna528@gmail.com', '194350', 'login', 0, '2025-08-05 04:56:44', '2025-08-05 10:21:48'),
(49, 'Samitha', 'Samithanawarathna528@gmail.com', '329524', 'login', 0, '2025-08-05 04:57:29', '2025-08-05 10:22:33'),
(50, 'Samitha', 'Samithanawarathna528@gmail.com', '299810', 'login', 0, '2025-08-05 06:53:38', '2025-08-05 12:18:42'),
(51, 'Samitha', 'Samithanawarathna528@gmail.com', '830371', 'login', 0, '2025-08-05 06:56:12', '2025-08-05 12:21:16'),
(52, 'Samitha', 'Samithanawarathna528@gmail.com', '221073', 'login', 0, '2025-08-05 06:56:25', '2025-08-05 12:21:29'),
(53, 'Samitha', 'Samithanawarathna528@gmail.com', '143287', 'login', 0, '2025-08-05 06:58:10', '2025-08-05 12:23:14'),
(54, 'Samitha', 'Samithanawarathna528@gmail.com', '175890', 'login', 0, '2025-08-05 07:00:17', '2025-08-05 12:25:21'),
(55, 'Samitha', 'Samithanawarathna528@gmail.com', '483801', 'login', 0, '2025-08-05 07:00:41', '2025-08-05 12:25:45'),
(56, 'Samitha', 'Samithanawarathna528@gmail.com', '241231', 'login', 0, '2025-08-05 07:01:14', '2025-08-05 12:26:19'),
(57, 'Samitha', 'Samithanawarathna528@gmail.com', '811032', 'login', 0, '2025-08-05 10:40:25', '2025-08-05 16:05:29'),
(58, 'Samitha', 'Samithanawarathna528@gmail.com', '611124', 'login', 0, '2025-08-05 10:44:30', '2025-08-05 16:09:38'),
(59, 'Samitha', 'Samithanawarathna528@gmail.com', '968893', 'login', 0, '2025-08-05 10:45:36', '2025-08-05 16:10:41'),
(60, 'samitha3', 'test@gmail.com', '477872', 'registration', 0, '2025-08-05 14:48:58', '2025-08-05 20:14:03'),
(61, 'samitha3', 'test@gmail.com', '896497', 'registration', 0, '2025-08-05 14:50:50', '2025-08-05 20:15:54'),
(62, 'samitha3', 'test@gmail.com', '263374', 'registration', 0, '2025-08-05 14:51:26', '2025-08-05 20:16:30'),
(63, 'Samitha', 'Samithanawarathna528@gmail.com', '306267', 'login', 0, '2025-08-06 09:24:01', '2025-08-06 14:49:06'),
(64, 'Samitha', 'Samithanawarathna528@gmail.com', '579708', 'login', 0, '2025-08-06 09:26:27', '2025-08-06 14:51:32'),
(65, 'Samitha', 'Samithanawarathna528@gmail.com', '653022', 'login', 0, '2025-08-06 09:27:24', '2025-08-06 14:52:29'),
(66, 'samitha', 'Samithanawarathna528@gmail.com', '195859', 'registration', 0, '2025-08-06 10:38:18', '2025-08-06 16:03:23'),
(67, 'samitha', 'Samithanawarathna528@gmail.com', '497853', 'registration', 0, '2025-08-06 10:39:27', '2025-08-06 16:04:32'),
(68, 'samitha', 'Samithanawarathna528@gmail.com', '922421', 'login', 0, '2025-08-06 10:43:37', '2025-08-06 16:08:41'),
(69, 'samitha', 'Samithanawarathna528@gmail.com', '214998', 'login', 0, '2025-08-06 12:11:21', '2025-08-06 17:36:26'),
(70, 'samitha', 'samithanawarathna528@gmail.com', '771861', 'registration', 0, '2025-08-06 12:13:22', '2025-08-06 17:38:27'),
(71, 'samitha', 'Samithanawarathna528@gmail.com', '653681', 'login', 0, '2025-08-07 00:39:08', '2025-08-07 06:04:12'),
(72, 'samitha', 'Samithanawarathna528@gmail.com', '601719', 'login', 0, '2025-08-08 07:00:18', '2025-08-08 12:25:24'),
(73, 'samitha', 'Samithanawarathna528@gmail.com', '904079', 'login', 0, '2025-08-08 08:28:06', '2025-08-08 13:53:18'),
(74, 'samitha', 'samithanawarathna528@gmail.com', '261314', 'registration', 0, '2025-08-08 08:30:02', '2025-08-08 13:55:10'),
(75, 'samitha', 'Samithanawarathna528@gmail.com', '748006', 'registration', 0, '2025-08-09 05:11:04', '2025-08-09 10:36:10'),
(76, 'samitha', 'Samithanawarathna528@gmail.com', '364870', 'registration', 0, '2025-08-09 05:13:51', '2025-08-09 10:38:57'),
(77, 'samitha', 'samithanawarathna528@gmail.com', '325133', 'registration', 0, '2025-08-09 05:19:47', '2025-08-09 10:44:53'),
(78, 'samitha', 'samithanawarathna528@gmail.com', '729191', 'registration', 0, '2025-08-09 05:22:17', '2025-08-09 10:47:22'),
(79, 'samitha', 'samithanawarathna528@gmail.com', '157083', 'registration', 0, '2025-08-09 05:25:15', '2025-08-09 10:50:21'),
(80, 'samitha', 'samithanawarathna528@gmail.com', '260779', 'registration', 0, '2025-08-09 05:25:36', '2025-08-09 10:50:42'),
(81, 'samitha', 'samithanawarathna528@gmail.com', '881906', 'registration', 0, '2025-08-09 05:29:35', '2025-08-09 10:54:40'),
(82, 'samitha', 'samithanawarathna528@gmail.com', '267091', 'registration', 0, '2025-08-09 05:39:48', '2025-08-09 11:04:53'),
(83, 'samitha', 'Samithanawarathna528@gmail.com', '290610', 'login', 0, '2025-08-09 07:06:50', '2025-08-09 12:31:56'),
(84, 'samitha', 'Samithanawarathna528@gmail.com', '631491', 'login', 0, '2025-08-09 07:14:57', '2025-08-09 12:40:02'),
(85, 'Samitha', 'Samithanawarathna528@gmail.com', '880088', 'registration', 0, '2025-08-09 07:16:26', '2025-08-09 12:41:31'),
(86, 'samitha', 'Samithanawarathna528@gmail.com', '754123', 'registration', 0, '2025-08-09 07:24:45', '2025-08-09 12:49:50'),
(87, 'samitha', 'Samithanawarathna528@gmail.com', '682779', 'registration', 0, '2025-08-09 07:32:09', '2025-08-09 12:57:15'),
(88, 'samitha', 'Samithanawarathna528@gmail.com', '861700', 'login', 0, '2025-08-09 07:33:51', '2025-08-09 12:58:56'),
(89, 'samitha', 'Samithanawarathna528@gmail.com', '907258', 'login', 0, '2025-08-09 07:34:01', '2025-08-09 12:59:05'),
(90, 'samitha', 'Samithanawarathna528@gmail.com', '920871', 'login', 0, '2025-08-09 07:36:54', '2025-08-09 13:01:59'),
(91, 'samitha', 'Samithanawarathna528@gmail.com', '751400', 'login', 0, '2025-08-09 07:57:26', '2025-08-09 13:22:31'),
(92, 'samitha', 'Samithanawarathna528@gmail.com', '563324', 'login', 0, '2025-08-09 07:59:04', '2025-08-09 13:24:09'),
(93, 'samitha', 'Samithanawarathna528@gmail.com', '666324', 'login', 0, '2025-08-09 08:01:11', '2025-08-09 13:26:17'),
(94, 'samitha', 'samithanawarathna528@gmail.com', '542046', 'registration', 0, '2025-08-09 14:27:33', '2025-08-09 19:52:38'),
(95, 'samitha', 'samithanawarathna@gmail.com', '647775', 'registration', 0, '2025-08-09 14:44:11', '2025-08-09 20:09:16'),
(96, 'samitha', 'samithanawarathna@gmail.com', '261959', 'registration', 0, '2025-08-09 14:44:16', '2025-08-09 20:09:21'),
(97, 'samitha', 'samithanawarathna@gmail.com', '506250', 'registration', 0, '2025-08-09 14:45:01', '2025-08-09 20:10:07'),
(98, 'samitha', 'samithanawarathna@gmail.com', '965041', 'registration', 0, '2025-08-09 14:48:26', '2025-08-09 20:13:31'),
(99, 'samitha', 'samithanawarathna528@gmail.com', '211339', 'registration', 0, '2025-08-09 14:49:24', '2025-08-09 20:14:29'),
(100, 'samitha', 'Samithanawarathna528@gmail.com', '212857', 'login', 0, '2025-08-10 02:59:25', '2025-08-10 08:24:31'),
(101, 'samitha', 'Samithanawarathna528@gmail.com', '629565', 'login', 0, '2025-08-10 03:00:03', '2025-08-10 08:25:08'),
(102, 'samitha', 'Samithanawarathna528@gmail.com', '148821', 'login', 0, '2025-08-10 03:18:35', '2025-08-10 08:43:40'),
(103, 'samitha', 'Samithanawarathna528@gmail.com', '312426', 'login', 0, '2025-08-10 04:43:36', '2025-08-10 10:08:41'),
(104, 'samitha', 'Samithanawarathna528@gmail.com', '160657', 'login', 0, '2025-08-10 05:16:14', '2025-08-10 10:41:27'),
(105, 'samitha', 'Samithanawarathna528@gmail.com', '120790', 'login', 0, '2025-08-10 05:16:27', '2025-08-10 10:41:34'),
(106, 'samitha', 'Samithanawarathna528@gmail.com', '106260', 'login', 0, '2025-08-10 05:16:34', '2025-08-10 10:41:42'),
(107, 'samitha', 'Samithanawarathna528@gmail.com', '402087', 'login', 0, '2025-08-10 09:28:52', '2025-08-10 14:53:57'),
(108, 'samitha_test', 'samithanawarathna322@gmail.com', '792258', 'registration', 0, '2025-08-10 09:35:16', '2025-08-10 15:00:22'),
(109, 'samitha', 'Samithanawarathna528@gmail.com', '194500', 'login', 0, '2025-08-11 07:39:06', '2025-08-11 13:04:11'),
(110, 'samitha', 'Samithanawarathna528@gmail.com', '690535', 'login', 0, '2025-08-11 10:15:16', '2025-08-11 15:40:21'),
(111, 'samitha', 'Samithanawarathna528@gmail.com', '898967', 'login', 0, '2025-08-11 10:16:15', '2025-08-11 15:41:20'),
(112, 'samitha', 'Samithanawarathna528@gmail.com', '242061', 'login', 0, '2025-08-11 10:40:54', '2025-08-11 16:05:58'),
(113, 'samitha', 'Samithanawarathna528@gmail.com', '516927', 'login', 0, '2025-08-11 10:42:58', '2025-08-11 16:08:02'),
(114, 'samitha', 'Samithanawarathna528@gmail.com', '359187', 'login', 0, '2025-08-11 10:44:50', '2025-08-11 16:09:54'),
(115, 'samitha', 'Samithanawarathna528@gmail.com', '480577', 'login', 0, '2025-08-11 11:27:06', '2025-08-11 16:52:11'),
(116, 'samitha', 'Samithanawarathna528@gmail.com', '245423', 'login', 0, '2025-08-11 23:07:52', '2025-08-12 04:32:59'),
(117, 'samitha', 'Samithanawarathna528@gmail.com', '516435', 'login', 0, '2025-08-11 23:41:10', '2025-08-12 05:06:14'),
(118, 'samitha', 'Samithanawarathna528@gmail.com', '332592', 'login', 0, '2025-08-12 10:54:14', '2025-08-12 16:19:19'),
(119, 'samitha', 'Samithanawarathna528@gmail.com', '781212', 'accountverification', 0, '2025-08-12 12:31:59', '2025-08-12 17:57:04'),
(120, 'samitha', 'Samithanawarathna528@gmail.com', '981686', 'login', 0, '2025-08-12 23:52:41', '2025-08-13 05:17:46'),
(121, 'samitha', 'Samithanawarathna528@gmail.com', '142172', 'accountverification', 0, '2025-08-12 23:54:18', '2025-08-13 05:19:22'),
(122, 'samitha', 'Samithanawarathna528@gmail.com', '808350', 'registration', 0, '2025-08-13 00:06:37', '2025-08-13 05:31:41'),
(123, 'samitha', 'Samithanawarathna528@gmail.com', '762024', 'accountverification', 0, '2025-08-13 00:07:37', '2025-08-13 05:32:41'),
(124, 'samitha', 'Samithanawarathna528@gmail.com', '961377', 'registration', 0, '2025-08-13 04:39:52', '2025-08-13 10:04:56'),
(125, 'samitha', 'Samithanawarathna528@gmail.com', '585370', 'login', 0, '2025-08-13 07:27:42', '2025-08-13 12:52:47'),
(126, 'samitha', 'Samithanawarathna528@gmail.com', '624646', 'accountverification', 0, '2025-08-13 07:28:26', '2025-08-13 12:53:30'),
(127, 'samitha', 'samithanawarathna528@gmail.com', '607664', 'registration', 0, '2025-08-13 07:32:37', '2025-08-13 12:57:42'),
(128, 'samitha', 'Samithanawarathna528@gmail.com', '690212', 'accountverification', 0, '2025-08-13 07:33:41', '2025-08-13 12:58:46'),
(129, 'samitha', 'Samithanawarathna528@gmail.com', '790082', 'accountverification', 0, '2025-08-13 07:35:14', '2025-08-13 13:00:19'),
(130, 'samitha', 'Samithanawarathna528@gmail.com', '587370', 'accountverification', 0, '2025-08-13 07:39:23', '2025-08-13 13:04:28'),
(131, 'samitha', 'Samithanawarathna528@gmail.com', '352561', 'accountverification', 0, '2025-08-13 07:56:33', '2025-08-13 13:21:37'),
(132, 'samitha', 'samithanawarathna528@gmail.com', '912242', 'registration', 0, '2025-08-13 14:29:59', '2025-08-13 19:55:03'),
(133, 'samitha', 'Samithanawarathna528@gmail.com', '301614', 'accountverification', 0, '2025-08-13 14:31:14', '2025-08-13 19:56:18'),
(134, 'bhasu', 'bhasujw@gmail.com', '276310', 'registration', 0, '2025-08-14 07:03:47', '2025-08-14 12:28:51'),
(135, 'bhasu', 'bhasujw@gmail.com', '968605', 'accountverification', 0, '2025-08-14 07:05:38', '2025-08-14 12:30:43'),
(136, 'samitha', 'samithanawarathna528@gmail.com', '939534', 'registration', 0, '2025-08-14 15:05:36', '2025-08-14 20:30:42'),
(137, 'samitha', 'Samithanawarathna528@gmail.com', '240187', 'login', 0, '2025-08-16 12:40:05', '2025-08-16 18:05:09'),
(138, 'samitha', 'Samithanawarathna528@gmail.com', '771697', 'login', 0, '2025-08-19 03:32:52', '2025-08-19 08:57:57'),
(139, 'samitha', 'Samithanawarathna528@gmail.com', '217352', 'login', 0, '2025-08-19 04:08:33', '2025-08-19 09:33:38'),
(140, 'samitha', 'Samithanawarathna528@gmail.com', '560512', 'login', 0, '2025-08-19 04:08:38', '2025-08-19 09:33:43'),
(141, 'samitha', 'Samithanawarathna528@gmail.com', '629368', 'login', 0, '2025-08-19 11:15:13', '2025-08-19 16:40:17'),
(142, 'samitha', 'Samithanawarathna528@gmail.com', '298873', 'login', 0, '2025-08-19 11:46:07', '2025-08-19 17:11:11'),
(143, 'samitha', 'Samithanawarathna528@gmail.com', '788218', 'login', 0, '2025-08-20 00:48:57', '2025-08-20 06:14:06'),
(144, 'samitha', 'Samithanawarathna528@gmail.com', '886331', 'login', 0, '2025-08-20 10:13:24', '2025-08-20 15:38:29'),
(145, 'samitha', 'Samithanawarathna528@gmail.com', '363710', 'login', 0, '2025-08-20 23:46:16', '2025-08-21 05:11:21'),
(146, 'samitha', 'Samithanawarathna528@gmail.com', '324207', 'login', 0, '2025-08-21 02:45:04', '2025-08-21 08:10:08'),
(147, 'samitha', 'Samithanawarathna528@gmail.com', '477231', 'login', 0, '2025-08-21 06:05:15', '2025-08-21 11:30:20'),
(148, 'samitha', 'Samithanawarathna528@gmail.com', '419104', 'login', 0, '2025-08-23 11:55:13', '2025-08-23 17:20:19'),
(149, 'samitha', 'Samithanawarathna528@gmail.com', '153028', 'login', 0, '2025-08-23 13:36:11', '2025-08-23 19:01:15'),
(150, 'samitha', 'Samithanawarathna528@gmail.com', '421855', 'login', 0, '2025-08-23 14:19:34', '2025-08-23 19:44:41'),
(151, 'samitha', 'Samithanawarathna528@gmail.com', '946018', 'login', 0, '2025-08-24 06:52:28', '2025-08-24 12:17:34'),
(152, 'samitha', 'Samithanawarathna528@gmail.com', '858718', 'login', 0, '2025-08-24 07:44:43', '2025-08-24 13:09:57'),
(153, 'samitha', 'Samithanawarathna528@gmail.com', '880688', 'login', 0, '2025-08-24 08:58:22', '2025-08-24 14:23:26'),
(154, 'samitha', 'Samithanawarathna528@gmail.com', '831955', 'login', 0, '2025-08-24 09:28:28', '2025-08-24 14:53:34'),
(155, 'samitha', 'Samithanawarathna528@gmail.com', '192380', 'login', 0, '2025-08-25 07:16:57', '2025-08-25 12:42:02'),
(156, 'samitha', 'Samithanawarathna528@gmail.com', '106888', 'login', 0, '2025-08-25 08:01:54', '2025-08-25 13:26:58'),
(157, 'samitha', 'Samithanawarathna528@gmail.com', '375670', 'login', 0, '2025-08-25 13:36:49', '2025-08-25 19:01:54'),
(158, 'samitha', 'Samithanawarathna528@gmail.com', '715059', 'login', 0, '2025-08-25 14:07:27', '2025-08-25 19:32:32'),
(159, 'samitha', 'Samithanawarathna528@gmail.com', '599338', 'login', 0, '2025-08-26 00:36:52', '2025-08-26 06:01:56'),
(160, 'samitha', 'Samithanawarathna528@gmail.com', '750436', 'login', 0, '2025-08-26 08:42:51', '2025-08-26 14:07:56'),
(161, 'samitha', 'Samithanawarathna528@gmail.com', '385585', 'login', 0, '2025-08-27 03:20:48', '2025-08-27 08:45:53'),
(162, 'samitha', 'Samithanawarathna528@gmail.com', '306718', 'login', 0, '2025-08-27 03:23:41', '2025-08-27 08:48:45'),
(163, 'samitha', 'Samithanawarathna528@gmail.com', '578909', 'login', 0, '2025-08-28 02:05:34', '2025-08-28 07:30:38'),
(164, 'samitha', 'Samithanawarathna528@gmail.com', '676567', 'login', 0, '2025-08-28 03:29:31', '2025-08-28 08:54:36'),
(165, 'samitha', 'Samithanawarathna528@gmail.com', '996010', 'login', 0, '2025-08-28 04:05:13', '2025-08-28 09:30:19'),
(166, 'samitha', 'Samithanawarathna528@gmail.com', '932370', 'login', 0, '2025-08-28 09:06:12', '2025-08-28 14:31:17'),
(167, 'samitha', 'Samithanawarathna528@gmail.com', '287133', 'login', 0, '2025-08-29 01:50:37', '2025-08-29 07:15:41'),
(168, 'samitha', 'Samithanawarathna528@gmail.com', '694519', 'login', 0, '2025-08-29 02:51:30', '2025-08-29 08:16:35'),
(169, 'samitha', 'Samithanawarathna528@gmail.com', '448334', 'login', 0, '2025-08-29 03:31:06', '2025-08-29 08:56:11'),
(170, 'samitha', 'Samithanawarathna528@gmail.com', '706943', 'login', 0, '2025-08-29 06:33:09', '2025-08-29 11:58:14'),
(171, 'samitha', 'Samithanawarathna528@gmail.com', '199930', 'login', 0, '2025-08-29 07:32:35', '2025-08-29 12:57:39'),
(172, 'samitha', 'Samithanawarathna528@gmail.com', '276306', 'login', 0, '2025-08-30 06:34:10', '2025-08-30 11:59:16'),
(173, 'samitha', 'Samithanawarathna528@gmail.com', '638117', 'login', 0, '2025-09-11 01:39:54', '2025-09-11 07:04:59'),
(174, 'samitha', 'Samithanawarathna528@gmail.com', '368830', 'login', 0, '2025-09-14 00:48:06', '2025-09-14 06:13:12'),
(175, 'samitha', 'Samithanawarathna528@gmail.com', '167621', 'login', 0, '2025-09-14 00:49:56', '2025-09-14 06:15:01'),
(176, 'samitha', 'Samithanawarathna528@gmail.com', '660564', 'login', 0, '2025-09-14 00:53:25', '2025-09-14 06:18:29'),
(177, 'samitha', 'Samithanawarathna528@gmail.com', '236433', 'login', 0, '2025-09-14 01:28:20', '2025-09-14 06:53:26'),
(178, 'samitha', 'Samithanawarathna528@gmail.com', '582225', 'login', 0, '2025-09-15 01:07:01', '2025-09-15 06:32:09'),
(179, 'samitha', 'Samithanawarathna528@gmail.com', '786577', 'login', 0, '2025-09-16 00:59:57', '2025-09-16 06:25:03'),
(180, 'samitha', 'Samithanawarathna528@gmail.com', '645289', 'login', 0, '2025-09-16 01:03:17', '2025-09-16 06:28:22'),
(181, 'samitha', 'Samithanawarathna528@gmail.com', '117541', 'login', 0, '2025-10-15 07:22:14', '2025-10-15 12:47:20'),
(182, 'samitha', 'Samithanawarathna528@gmail.com', '235221', 'login', 0, '2025-10-16 03:54:23', '2025-10-16 09:19:30'),
(183, 'samitha', 'Samithanawarathna528@gmail.com', '357482', 'login', 0, '2025-10-16 14:12:35', '2025-10-16 19:37:42'),
(184, 'samitha', 'Samithanawarathna528@gmail.com', '314032', 'login', 0, '2025-10-16 14:17:48', '2025-10-16 19:42:53'),
(185, 'samitha', 'Samithanawarathna528@gmail.com', '393135', 'login', 0, '2025-10-17 08:36:28', '2025-10-17 14:01:34'),
(186, 'samitha', 'Samithanawarathna528@gmail.com', '600060', 'login', 0, '2025-10-18 22:13:21', '2025-10-19 03:38:26'),
(187, 'samitha4', 'samithanawarathna322@gmail.com', '292859', 'registration', 0, '2025-10-20 07:08:55', '2025-10-20 12:34:01'),
(188, 'samitha', 'Samithanawarathna528@gmail.com', '342651', 'accountverification', 0, '2025-10-20 07:15:05', '2025-10-20 12:40:10'),
(189, 'samitha', 'Samithanawarathna528@gmail.com', '122961', 'login', 0, '2025-10-20 08:07:18', '2025-10-20 13:32:24'),
(190, 'samitha', 'Samithanawarathna528@gmail.com', '435204', 'login', 0, '2025-10-20 08:12:50', '2025-10-20 13:37:57'),
(191, 'samitha', 'Samithanawarathna528@gmail.com', '944277', 'login', 0, '2025-10-20 08:58:17', '2025-10-20 14:23:24'),
(192, 'student_test', 'animelearnin528@gmail.com', '566505', 'registration', 0, '2025-10-21 00:27:31', '2025-10-21 05:52:37'),
(193, 'samitha', 'samithanawarathna528@gmail.com', '467200', 'login', 0, '2025-10-21 00:29:35', '2025-10-21 05:54:41'),
(194, 'expert_test', 'samithanawarathna322@gmail.com', '369870', 'registration', 0, '2025-10-21 00:38:24', '2025-10-21 06:03:29'),
(195, 'samitha', 'Samithanawarathna528@gmail.com', '690407', 'login', 0, '2025-10-21 12:37:31', '2025-10-21 18:02:39'),
(196, 'samitha', 'samithanawarathna528@gmail.com', '254988', 'login', 0, '2025-10-22 02:09:56', '2025-10-22 07:35:02'),
(197, 'expert_test', 'samithanawarathna322@gmail.com', '176012', 'login', 0, '2025-10-22 02:12:34', '2025-10-22 07:37:39'),
(198, 'student_test', 'animelearnin528@gmail.com', '715202', 'login', 0, '2025-10-22 02:21:21', '2025-10-22 07:46:26'),
(199, 'student_test_real', '2023cs120@stu.ucsc.cmb.ac.lk', '588908', 'registration', 0, '2025-10-22 02:24:38', '2025-10-22 07:49:43'),
(200, 'samitha', 'Samithanawarathna528@gmail.com', '848591', 'login', 0, '2025-10-22 07:14:08', '2025-10-22 12:39:14'),
(201, 'samitha', 'Samithanawarathna528@gmail.com', '694029', 'login', 0, '2025-10-22 07:41:51', '2025-10-22 13:06:57'),
(202, 'samitha', 'Samithanawarathna528@gmail.com', '236241', 'login', 0, '2025-10-22 08:53:16', '2025-10-22 14:18:21'),
(203, 'samitha', 'Samithanawarathna528@gmail.com', '955424', 'login', 0, '2025-10-22 23:10:13', '2025-10-23 04:35:19'),
(204, 'student_test', 'animelearnin528@gmail.com', '324483', 'accountverification', 0, '2025-10-23 04:29:09', '2025-10-23 09:54:15'),
(205, 'student_test', 'animelearnin528@gmail.com', '624984', 'accountverification', 0, '2025-10-23 04:52:18', '2025-10-23 10:17:24'),
(206, 'student_test_final', 'methmalinavodya@gmail.com', '538849', 'registration', 0, '2025-10-23 05:16:11', '2025-10-23 10:41:17'),
(207, 'usename_test', 'samithanawarathna528@gmail.com', '349151', 'registration', 0, '2025-10-23 05:41:04', '2025-10-23 11:06:08'),
(208, 'usename_test', 'samithanawarathna528@gmail.com', '695397', 'accountverification', 0, '2025-10-23 05:43:37', '2025-10-23 11:08:42'),
(209, 'usename_test', 'samithanawarathna528@gmail.com', '655800', 'registration', 0, '2025-10-23 06:21:35', '2025-10-23 11:46:41'),
(210, 'expert_test', 'Samithanawarathna322@gmail.com', '664964', 'login', 0, '2025-10-23 06:34:50', '2025-10-23 11:59:55'),
(211, 'usename_test', 'samithanawarathna528@gmail.com', '629770', 'accountverification', 0, '2025-10-23 06:39:21', '2025-10-23 12:04:26'),
(212, 'usename_test', 'samithanawarathna528@gmail.com', '252910', 'accountverification', 0, '2025-10-23 06:39:52', '2025-10-23 12:04:57'),
(213, 'admin_test', '2023cs120@stu.ucsc.cmb.ac.lk', '841366', 'registration', 0, '2025-10-23 07:07:06', '2025-10-23 12:32:11'),
(214, 'admin_test', '2023cs120@stu.ucsc.cmb.ac.lk', '524683', 'login', 0, '2025-10-23 07:17:02', '2025-10-23 12:42:08'),
(215, 'student_test_final', 'methmalinavodya@gmail.com', '510425', 'login', 0, '2025-10-23 07:23:04', '2025-10-23 12:48:21'),
(216, 'student_test_final', 'methmalinavodya@gmail.com', '324097', 'login', 0, '2025-10-23 07:32:37', '2025-10-23 12:57:42'),
(217, 'usename_test', 'samithanawarathna528@gmail.com', '820901', 'registration', 0, '2025-10-23 08:04:01', '2025-10-23 13:29:06'),
(218, 'student_test_final', 'methmalinavodya@gmail.com', '451730', 'accountverification', 0, '2025-10-23 08:05:49', '2025-10-23 13:30:53'),
(219, 'usename_test', 'samithanawarathna528@gmail.com', '397082', 'accountverification', 0, '2025-10-23 08:19:47', '2025-10-23 13:44:52'),
(220, 'usename_test', 'samithanawarathna528@gmail.com', '652344', 'registration', 0, '2025-10-23 08:25:59', '2025-10-23 13:51:05'),
(221, 'usename_test', 'samithanawarathna528@gmail.com', '782431', 'accountverification', 0, '2025-10-23 08:38:53', '2025-10-23 14:03:58'),
(222, 'usename_test', 'samithanawarathna528@gmail.com', '135093', 'registration', 0, '2025-10-23 09:33:20', '2025-10-23 14:58:25'),
(223, 'admin_test2', 'samithanawarathna@gmail.com', '132310', 'change_password', 0, '2025-11-24 05:13:15', '2025-11-24 10:38:31'),
(224, 'admin_test2', 'samithanawarathna@gmail.com', '700223', 'change_password', 0, '2025-11-24 05:25:54', '2025-11-24 10:51:27'),
(225, 'samitha', 'samithanawarathna528@gmail.com', '774172', 'change_password', 0, '2025-11-25 08:08:45', '2025-11-25 13:34:39'),
(226, 'samitha', 'samithanawarathna528@gmail.com', '630009', 'change_password', 0, '2025-11-25 08:13:27', '2025-11-25 13:38:59'),
(227, 'samitha', 'samithanawarathna528@gmail.com', '731538', 'change_password', 0, '2025-11-25 08:16:28', '2025-11-25 13:41:43'),
(228, 'samitha', 'samithanawarathna528@gmail.com', '252901', 'change_password', 0, '2025-11-25 08:21:44', '2025-11-25 13:46:59'),
(229, 'samitha', 'samithanawarathna528@gmail.com', '958325', 'change_password', 0, '2025-11-25 08:32:39', '2025-11-25 13:58:00'),
(230, 'samitha', 'samithanawarathna528@gmail.com', '197768', 'change_password', 0, '2025-11-25 08:35:14', '2025-11-25 14:00:28'),
(231, 'samitha', 'samithanawarathna528@gmail.com', '768274', 'change_password', 0, '2025-11-25 08:36:25', '2025-11-25 14:01:37'),
(232, 'samitha', 'samithanawarathna528@gmail.com', '649159', 'change_password', 0, '2025-11-25 08:38:22', '2025-11-25 14:03:29');

-- --------------------------------------------------------

--
-- Stand-in structure for view `profile_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `profile_summary`;
CREATE TABLE IF NOT EXISTS `profile_summary` (
`banned` tinyint
,`created_at` timestamp
,`display_name` varchar(100)
,`profile_id` int
,`profile_picture` varchar(200)
,`role_name` varchar(50)
,`subject_id` int
,`subject_name` varchar(50)
,`username` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
CREATE TABLE IF NOT EXISTS `question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `creator_id` int DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_question_creator` (`creator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`id`, `title`, `content`, `creator_id`, `is_deleted`, `created_at`) VALUES
(1, 'What is machine learning?', 'I want to understand how machines learn from data.', 1, 0, '2025-10-16 06:18:32'),
(2, 'How does SQL JOIN work?', 'Can someone explain INNER JOIN and LEFT JOIN?', 5, 0, '2025-10-16 06:18:32'),
(3, 'What is the difference between TCP and UDP?', 'Which one is faster and why?', NULL, 0, '2025-10-16 06:18:32'),
(4, 'How to center a div in CSS?', 'My div is not centering properly.', 40, 0, '2025-10-16 06:18:32'),
(5, 'Explain Newton’s Third Law.', 'Why do we feel recoil when we push something?', 41, 0, '2025-10-16 06:18:32'),
(6, 'test_q', 'q_content', NULL, 0, '2025-10-18 02:01:23'),
(7, 'q', '1', NULL, 0, '2025-10-18 17:50:22'),
(8, 'a', 'q', NULL, 0, '2025-10-18 18:15:38'),
(9, 'q1', 'content', NULL, 0, '2025-10-20 05:42:17'),
(10, 'title', 'content', 92, 0, '2025-10-20 23:41:04'),
(11, 'title', 'content', 92, 0, '2025-10-20 23:43:22'),
(12, 'title', 'content', 92, 0, '2025-10-20 23:43:50'),
(13, 'question', 'description', 91, 0, '2025-10-21 01:45:12'),
(14, 'question', 'description', 91, 0, '2025-10-21 01:46:39'),
(15, 'jhvdf', '\'odhfag', 91, 0, '2025-10-22 03:30:21'),
(16, 'ljkshgouqe', 'Paiehg\r\nqepig', 91, 0, '2025-10-22 22:58:55'),
(17, 'i7t.w9635\'', '-49t8\r\n4t3yt', NULL, 0, '2025-10-22 23:22:39'),
(18, 'kluwet', '/lkeh\r\ne', 94, 0, '2025-10-23 00:20:13'),
(19, ';iug\'9r', 'oue[\'fiygef', NULL, 0, '2025-10-23 00:52:16'),
(20, 'ti', 'con', 94, 0, '2025-10-23 02:35:08'),
(21, 'ti', 'con', 94, 0, '2025-10-23 02:56:52'),
(22, 'ti', 'contet', 94, 0, '2025-10-23 04:03:38');

-- --------------------------------------------------------

--
-- Table structure for table `questiontag`
--

DROP TABLE IF EXISTS `questiontag`;
CREATE TABLE IF NOT EXISTS `questiontag` (
  `question_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`question_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questiontag`
--

INSERT INTO `questiontag` (`question_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 6),
(4, 7),
(4, 8),
(5, 9),
(5, 10),
(6, 14),
(6, 15),
(12, 18),
(13, 18),
(14, 18),
(15, 18),
(7, 21),
(8, 22),
(9, 24),
(9, 25),
(13, 25),
(11, 27),
(10, 29),
(20, 30),
(22, 30),
(16, 38),
(17, 40),
(17, 41),
(18, 42),
(19, 45),
(21, 50);

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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`id`, `user_id`, `time`, `subject`, `description`, `proof_link`, `review`, `feedback`) VALUES
(1, 1, '2025-07-29 10:59:10', 'Mathematics', 'I’ve mentored peers and created problem sets for calculus.', 'https://example.com/proof/math-alice', 'approved', NULL),
(2, 2, '2025-07-29 10:59:10', 'Physics', 'Published 3 articles on quantum mechanics, and taught it in university.', 'https://example.com/proof/quantum-bob', 'approved', NULL),
(3, 5, '2025-07-29 10:59:10', 'Computer Science', 'I’ve been learning CS for 3 months. I want to help others.', 'https://example.com/proof/eva-cs', 'pending', NULL),
(4, 1, '2025-07-29 10:59:10', 'Art History', 'Created visual lecture series on modern art movements.', 'https://example.com/proof/art-alice', 'pending', NULL),
(5, 1, '2025-08-19 10:58:21', 'Math Help', 'Need assistance with integrals.', 'http://docs.com/proof1.pdf', 'pending', NULL),
(6, 2, '2025-08-19 10:58:21', 'Physics', 'Help with lab report review.', 'http://docs.com/proof2.pdf', 'approved', 'Good work'),
(7, 3, '2025-08-19 10:58:21', 'Expert Request - Physics', 'I hold MSc in Physics and research publications.', 'http://docs.com/expert_physics.pdf', 'pending', NULL),
(8, 4, '2025-08-19 10:58:21', 'History', 'Cross-checking sources.', 'http://docs.com/proof3.pdf', 'pending', NULL),
(9, 5, '2025-08-19 10:58:21', 'Expert Request - Computer Science', '5 years backend development, with certifications.', 'http://docs.com/expert_cs.pdf', 'pending', NULL),
(10, 6, '2025-08-19 10:58:21', 'Economics', 'Feedback on market analysis.', 'http://docs.com/proof4.pdf', 'approved', 'Well reasoned'),
(11, 7, '2025-08-19 10:58:21', 'Art', 'Portfolio critique.', 'http://docs.com/proof5.pdf', 'rejected', 'Unclear theme'),
(12, 8, '2025-08-19 10:58:21', 'Biology', 'Feedback on essay.', 'http://docs.com/proof6.pdf', 'pending', NULL),
(13, 9, '2025-08-19 10:58:21', 'Philosophy', 'Debate prep help.', 'http://docs.com/proof7.pdf', 'approved', 'Solid points'),
(14, 10, '2025-08-19 10:58:21', 'Sociology', 'Survey design feedback.', 'http://docs.com/proof8.pdf', 'pending', NULL),
(15, 11, '2025-08-19 10:58:21', 'Expert Request - Math', 'PhD in Pure Mathematics, multiple conference talks.', 'http://docs.com/expert_math.pdf', 'pending', NULL),
(16, 12, '2025-08-19 10:58:21', 'Physics', 'Need relativity explanation.', 'http://docs.com/proof9.pdf', 'pending', NULL),
(17, 13, '2025-08-19 10:58:21', 'Computer Science', 'Code optimization help.', 'http://docs.com/proof10.pdf', 'approved', 'Clean solution'),
(18, 14, '2025-08-19 10:58:21', 'Art', 'Painting critique.', 'http://docs.com/proof11.pdf', 'pending', NULL),
(19, 15, '2025-08-19 10:58:21', 'Economics', 'Game theory paper.', 'http://docs.com/proof12.pdf', 'approved', 'Well structured'),
(20, 16, '2025-08-19 10:58:21', 'History', 'Essay review.', 'http://docs.com/proof13.pdf', 'rejected', 'Weak argument'),
(21, 17, '2025-08-19 10:58:21', 'Expert Request - Biology', 'Published 3 biology journals, MSc graduate.', 'http://docs.com/expert_biology.pdf', 'pending', NULL),
(22, 18, '2025-08-19 10:58:21', 'Math Help', 'Need help with matrices.', 'http://docs.com/proof14.pdf', 'pending', NULL),
(23, 19, '2025-08-19 10:58:21', 'Computer Science', 'Debugging assistance.', 'http://docs.com/proof15.pdf', 'approved', 'Good fix'),
(24, 20, '2025-08-19 10:58:21', 'Philosophy', 'Metaphysics essay review.', 'http://docs.com/proof16.pdf', 'pending', NULL),
(25, 1, '2025-08-20 00:18:00', 'subject', 'desc\r\n', NULL, 'pending', NULL),
(26, 1, '2025-08-20 00:18:36', 'subject', 'desc\r\n', NULL, 'pending', NULL),
(27, 1, '2025-08-20 00:22:44', 'subject', 'desc\r\n', NULL, 'pending', NULL),
(28, 1, '2025-08-20 00:23:02', 'subject', 'descc', NULL, 'pending', NULL),
(29, 1, '2025-08-20 00:23:55', 'subject', 'descc', NULL, 'pending', NULL),
(30, 1, '2025-08-20 00:24:22', 'subject', 'descc', NULL, 'pending', NULL),
(31, 1, '2025-08-20 00:25:23', 'subject', 'desc', NULL, 'pending', NULL),
(32, 1, '2025-08-20 00:30:35', 'subject', 'desc', NULL, 'pending', NULL),
(33, 1, '2025-08-20 00:42:02', 'subject', 'dex', 'C:\\wamp64\\www\\Openminds\\app\\controllers/../../private/uploads/requests//request.pdf', 'pending', NULL),
(40, 39, '2025-08-25 13:33:12', 'psychology', 'nice description edited', 'Solving paint problem.pdf', 'approved', NULL),
(44, 39, '2025-08-29 01:51:59', 'psychology', 'Psychology Expertise Demonstration:\r\nThis individual exhibits a deep and systematic understanding of human behavior, cognition, and emotion, grounded in both classical and contemporary psychological theory. They can accurately interpret complex psychological phenomena, design and execute research studies, and critically analyze empirical data. Their expertise is evidenced by the ability to:\r\n\r\nApply theoretical models to real-world behavioral and mental health scenarios.\r\n\r\nDiagnose, assess, and provide evidence-based interventions across diverse populations.\r\n\r\nIntegrate knowledge from neuroscience, developmental psychology, cognitive psychology, and social psychology to form comprehensive insights.\r\n\r\nCommunicate psychological concepts clearly to both professionals and lay audiences.\r\n\r\nCritically evaluate research methods, identify biases, and synthesize findings to inform practice or policy.\r\n\r\nThey have substantiated their expertise through a combination of advanced education, peer-reviewed publications, applied clinical experience, or recognized certifications, demonstrating both practical skill and scholarly mastery in the field of psychology.', NULL, 'approved', NULL),
(45, 39, '2025-08-29 03:11:45', 'physics', 'This individual possesses a profound understanding of the fundamental principles governing matter, energy, and the universe, spanning classical mechanics, electromagnetism, thermodynamics, quantum mechanics, and modern physics. Their expertise is evidenced by the ability to:\r\n\r\nAnalyze and model complex physical systems using both theoretical frameworks and mathematical rigor.\r\n\r\nDesign and conduct experiments, interpret empirical data, and reconcile observations with established physical laws.\r\n\r\nApply computational and analytical methods to solve challenging problems in physics and related fields.\r\n\r\nCommunicate complex concepts clearly to both specialists and general audiences, translating abstract phenomena into understandable explanations.\r\n\r\nCritically evaluate scientific literature, identify assumptions, and propose innovative solutions or new approaches.\r\n\r\nThey substantiate their expertise through advanced education, peer-reviewed publications, research experience, or recognized certifications, demonstrating mastery in both the theoretical and applied dimensions of physics.', NULL, 'approved', NULL),
(46, 39, '2025-08-29 03:29:43', 'psychology', 'desctiption', NULL, 'approved', NULL),
(47, 39, '2025-08-29 06:30:25', 'psychology', 'ad', '', 'approved', NULL),
(49, 39, '2025-09-15 01:18:57', 'psychology', 'notification-item', NULL, 'approved', NULL),
(51, 39, '2025-10-18 22:03:16', 'A', 'N', NULL, 'approved', NULL),
(52, 90, '2025-10-20 07:08:22', 'science', 'test', NULL, 'approved', NULL),
(53, 92, '2025-10-21 00:43:04', 'sucs', 'abc', NULL, 'approved', NULL),
(54, 91, '2025-10-22 07:12:57', 'subject name', 'test', NULL, 'approved', NULL),
(55, 39, '2025-10-22 23:09:11', 'maths', 'content', '', 'pending', NULL),
(56, 93, '2025-10-23 04:34:08', 'science', 'descr', NULL, 'approved', NULL),
(57, 91, '2025-10-23 05:00:26', 'subject name', 'des', NULL, 'approved', NULL),
(58, 94, '2025-10-23 05:56:18', 'sub', 't', '', 'approved', NULL),
(59, 91, '2025-10-23 06:27:59', 'subject name', 'des', NULL, 'approved', NULL),
(60, 98, '2025-10-23 08:09:12', 'science', 'desc', NULL, 'approved', NULL),
(61, 91, '2025-10-23 08:31:15', 'subject name', 'desc', '', 'approved', NULL),
(62, 92, '2025-10-23 09:38:27', 'subject', 'desc', '', 'approved', NULL),
(63, 101, '2025-11-14 08:12:37', 'subject', 'd', '', 'approved', NULL),
(64, 102, '2025-11-25 10:28:02', 'theology', 'something', NULL, 'approved', NULL),
(65, 102, '2025-11-25 10:30:21', 'theology', 'something', NULL, 'approved', NULL),
(66, 102, '2025-12-02 05:31:17', 'psychology', 'desc', '', 'rejected', 'f');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Mathematics'),
(2, 'Physics'),
(3, 'Biology'),
(4, 'Computer Science'),
(5, 'Art History'),
(6, 'theology'),
(7, 'zxczcx');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(27, ''),
(21, '4'),
(48, 'biology'),
(26, 'chem'),
(28, 'chemistry'),
(36, 'computer'),
(44, 'cs'),
(7, 'CSS'),
(32, 'cvbnm'),
(22, 'd'),
(2, 'Data Science'),
(3, 'Databases'),
(49, 'design'),
(33, 'e'),
(23, 'fff'),
(20, 'field theory'),
(45, 'gdyt'),
(51, 'History'),
(6, 'Internet Protocols'),
(42, 'jf.'),
(38, 'jogiy'),
(40, 'jyf'),
(1, 'Machine Learning'),
(25, 'maths'),
(10, 'Mechanics'),
(35, 'motion'),
(5, 'Networking'),
(46, 'p'),
(19, 'particle physics'),
(39, 'phycics'),
(47, 'physic'),
(9, 'Physics'),
(37, 'Psychology'),
(17, 'Quantum Computing'),
(15, 're'),
(14, 'sc'),
(24, 'sci'),
(18, 'science'),
(31, 'sciene'),
(4, 'SQL'),
(50, 'ta'),
(30, 'tag'),
(11, 'test_t1'),
(12, 'test_t2'),
(13, 'test_t3'),
(29, 'txt'),
(41, 'u'),
(43, 'wave therory'),
(34, 'wave theroy'),
(52, 'Web Dev'),
(8, 'Web Development'),
(16, 'xc'),
(53, 'zxczc');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `creator_id` int NOT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if the topic is pinned, 0 otherwise',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `name`, `creator_id`, `pinned`) VALUES
(1, 'Linear Algebra', 0, 0),
(2, 'Machine Learning Basics', 0, 0),
(3, 'Web Development', 0, 0),
(4, 'Modern Physics', 0, 0),
(5, '', 39, 0),
(6, 's', 101, 0),
(7, 'd', 102, 0),
(8, 'theo', 102, 0),
(9, 'science', 1, 0);

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
  `role` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `banned` tinyint(1) DEFAULT '0',
  `profile_pic` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `profile_picture` varchar(200) NOT NULL DEFAULT '\\uploads\\\\0\\profile.avif',
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `role`, `created_at`, `banned`, `profile_pic`, `is_deleted`, `profile_picture`, `display_name`) VALUES
(1, 'alice', 'hashed_pw1', 'abc@gmail.com', 3, '2025-07-29 10:58:45', 1, NULL, 0, './uploads/1/profile.png', 'alice'),
(2, 'bob_mentor', 'hashed_pw2', 'bob@example.com', 2, '2025-07-29 10:58:45', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', ''),
(3, 'carol_expert', 'hashed_pw3', 'carol@example.com', 3, '2025-07-29 10:58:45', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', ''),
(4, 'dave_admin', 'hashed_pw4', 'dave@example.com', 4, '2025-07-29 10:58:45', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', ''),
(5, 'eva_student', 'hashed_pw5', 'eva@example.com', 1, '2025-07-29 10:58:45', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', ''),
(40, 'user1', 'pw1', 'user1@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Alice Wonder'),
(41, 'user2', 'pw2', 'user2@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Bob Stone'),
(42, 'user3', 'pw3', 'user3@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Charlie Kim'),
(43, 'user4', 'pw4', 'user4@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Diana Ray'),
(44, 'user5', 'pw5', 'user5@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Edward Blake'),
(45, 'user6', 'pw6', 'user6@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Frank Yu'),
(46, 'user7', 'pw7', 'user7@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Grace Li'),
(47, 'user8', 'pw8', 'user8@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Harry West'),
(48, 'user9', 'pw9', 'user9@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Irene Cho'),
(49, 'user10', 'pw10', 'user10@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'John Doe'),
(50, 'user11', 'pw11', 'user11@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Sophia Lane'),
(51, 'user12', 'pw12', 'user12@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Michael Cruz'),
(52, 'user13', 'pw13', 'user13@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Emma Patel'),
(53, 'user14', 'pw14', 'user14@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Daniel Green'),
(54, 'user15', 'pw15', 'user15@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Olivia Brooks'),
(55, 'user16', 'pw16', 'user16@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Ethan Hayes'),
(56, 'user17', 'pw17', 'user17@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Ava Carter'),
(57, 'user18', 'pw18', 'user18@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Noah James'),
(58, 'user19', 'pw19', 'user19@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Mia Flores'),
(59, 'user20', 'pw20', 'user20@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Lucas Turner'),
(60, 'user21', 'pw21', 'user21@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Chloe Adams'),
(61, 'user22', 'pw22', 'user22@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Mason Hill'),
(62, 'user23', 'pw23', 'user23@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Isabella Wright'),
(63, 'user24', 'pw24', 'user24@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Liam Scott'),
(64, 'user25', 'pw25', 'user25@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Amelia Reed'),
(65, 'user26', 'pw26', 'user26@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Benjamin Clark'),
(66, 'user27', 'pw27', 'user27@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Harper Ross'),
(67, 'user28', 'pw28', 'user28@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Jacob Lee'),
(68, 'user29', 'pw29', 'user29@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Lily Parker'),
(69, 'user30', 'pw30', 'user30@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Samuel Young'),
(70, 'user31', 'pw31', 'user31@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Victoria Ward'),
(71, 'user32', 'pw32', 'user32@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Alexander Hall'),
(72, 'user33', 'pw33', 'user33@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Zoe Fisher'),
(73, 'user34', 'pw34', 'user34@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Matthew Price'),
(74, 'user35', 'pw35', 'user35@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Natalie Howard'),
(75, 'user36', 'pw36', 'user36@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Ryan Morgan'),
(76, 'user37', 'pw37', 'user37@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Ella Bennett'),
(77, 'user38', 'pw38', 'user38@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Jack Hughes'),
(78, 'user39', 'pw39', 'user39@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Aria Rivera'),
(79, 'user40', 'pw40', 'user40@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Gabriel Collins'),
(80, 'user41', 'pw41', 'user41@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Hannah Mitchell'),
(81, 'user42', 'pw42', 'user42@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'David Kelly'),
(82, 'user43', 'pw43', 'user43@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Scarlett Long'),
(83, 'user44', 'pw44', 'user44@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Andrew Cooper'),
(84, 'user45', 'pw45', 'user45@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Leah Torres'),
(85, 'user46', 'pw46', 'user46@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Joseph Gray'),
(86, 'user47', 'pw47', 'user47@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Stella Watson'),
(87, 'user48', 'pw48', 'user48@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'William Sanders'),
(88, 'user49', 'pw49', 'user49@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'Layla Ramirez'),
(89, 'user50', 'pw50', 'user50@example.com', 1, '2025-08-19 10:56:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'James Foster'),
(91, 'student_test', '$2y$10$4mbTnYhM0RpIJelv1iB3IurJTtlvXlpaQIi0f6hlz6u4Vu.1LCu/a', 'animelearnin528@gmail.com', 2, '2025-10-21 00:22:59', 0, NULL, 0, './uploads/91/profile.', 'mentor_test'),
(92, 'expert_test', '$2y$10$jBvl192tUZr0DDxlWPUwoeyZnoxU7Fg0CKG/z2XmWx.Ya49Rqshuq', 'samithanawarathna322@gmail.com', 3, '2025-10-21 00:34:01', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'expert_test'),
(94, 'student_test_final', '$2y$10$u.qOVCjZQ7UFH58IQAg7ye9NWQnlYDuB0l4/WD9OxSVP.C46bZv0.', 'methmalinavodya@gmail.com', 1, '2025-10-23 05:11:35', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'student_test'),
(97, 'admin_test', '$2y$10$hqg7slA4gMGqSlt52fdAwuk0BoynbQ2UO6GBOO8UE5KUWdepIJ.q6', '2023cs120@stu.ucsc.cmb.ac.lk', 4, '2025-10-23 07:02:47', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'admin_test'),
(101, 'admin_test2', '$2y$10$1aWXMpvq0/WjSn3fwrsbHOzyFQHZbt01YQN41MXRe4GwuOldZBmE2', 'samithanawarathna@gmail.com', 3, '2025-11-14 07:08:02', 0, NULL, 0, './uploads/101/profile.jpg', 'admin_test3'),
(102, 'samitha', '$2y$10$Kb/kVnoKUc2ExeNMRSJf4.Tz/HwjQi/9RgGcETNPbsuPosUCmxshS', 'samithanawarathna528@gmail.com', 3, '2025-11-25 07:59:00', 0, NULL, 0, '\\uploads\\\\0\\profile.avif', 'samitha');

-- --------------------------------------------------------

--
-- Table structure for table `uservoteanswer`
--

DROP TABLE IF EXISTS `uservoteanswer`;
CREATE TABLE IF NOT EXISTS `uservoteanswer` (
  `a_id` int NOT NULL,
  `u_id` int NOT NULL,
  `votetype` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`a_id`,`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `uservoteanswer`
--

INSERT INTO `uservoteanswer` (`a_id`, `u_id`, `votetype`, `created_at`) VALUES
(1, 53, 'upvote', '2025-10-16 06:18:32'),
(1, 54, 'upvote', '2025-10-16 06:18:32'),
(2, 55, 'downvote', '2025-10-16 06:18:32'),
(2, 56, 'upvote', '2025-10-16 06:18:32'),
(3, 57, 'upvote', '2025-10-16 06:18:32'),
(4, 58, 'upvote', '2025-10-16 06:18:32'),
(4, 59, 'downvote', '2025-10-16 06:18:32'),
(5, 60, 'upvote', '2025-10-16 06:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `uservoteexercise`
--

DROP TABLE IF EXISTS `uservoteexercise`;
CREATE TABLE IF NOT EXISTS `uservoteexercise` (
  `exercise_id` int NOT NULL,
  `u_id` int NOT NULL,
  `votetype` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exercise_id`,`u_id`),
  KEY `u_id` (`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `uservoteexercise`
--

INSERT INTO `uservoteexercise` (`exercise_id`, `u_id`, `votetype`, `created_at`) VALUES
(1, 44, 'upvote', '2025-10-17 22:06:22'),
(1, 45, 'upvote', '2025-10-17 22:06:22'),
(2, 46, 'upvote', '2025-10-17 22:06:22'),
(4, 48, 'upvote', '2025-10-17 22:06:22');

-- --------------------------------------------------------

--
-- Table structure for table `uservotequestion`
--

DROP TABLE IF EXISTS `uservotequestion`;
CREATE TABLE IF NOT EXISTS `uservotequestion` (
  `q_id` int NOT NULL,
  `u_id` int NOT NULL,
  `votetype` enum('upvote','downvote') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`q_id`,`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `uservotequestion`
--

INSERT INTO `uservotequestion` (`q_id`, `u_id`, `votetype`, `created_at`) VALUES
(1, 44, 'upvote', '2025-10-16 06:18:32'),
(1, 45, 'upvote', '2025-10-16 06:18:32'),
(1, 46, 'downvote', '2025-10-16 06:18:32'),
(2, 47, 'upvote', '2025-10-16 06:18:32'),
(2, 48, 'upvote', '2025-10-16 06:18:32'),
(3, 49, 'upvote', '2025-10-16 06:18:32'),
(3, 50, 'downvote', '2025-10-16 06:18:32'),
(4, 51, 'upvote', '2025-10-16 06:18:32'),
(5, 52, 'upvote', '2025-10-16 06:18:32');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_requests_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_requests_summary`;
CREATE TABLE IF NOT EXISTS `user_requests_summary` (
`description` text
,`request_id` int
,`review` enum('pending','approved','rejected')
,`subject` varchar(50)
,`user_id` int
,`user_name` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_topic_activity`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_topic_activity`;
CREATE TABLE IF NOT EXISTS `user_topic_activity` (
`last_update` datetime
,`note_count` bigint
,`topic_name` varchar(255)
,`total_referred_time` double
,`user_id` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_questions_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_questions_summary`;
CREATE TABLE IF NOT EXISTS `view_questions_summary` (
`answer_count` bigint
,`asked_date` timestamp
,`asked_user_id` int
,`content` text
,`has_accepted_answer` tinyint(1)
,`last_answered_date` timestamp
,`question_id` int
,`tags` text
,`title` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure for view `exercise_summary`
--
DROP TABLE IF EXISTS `exercise_summary`;

DROP VIEW IF EXISTS `exercise_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `exercise_summary`  AS SELECT `et`.`exercise_id` AS `exercise_id`, `et`.`tag_id` AS `tag_id`, `e`.`title` AS `exercise_title`, `e`.`creator_id` AS `creator_id`, `t`.`name` AS `tag_name`, `s`.`name` AS `subject_name`, `e`.`created_at` AS `created_at`, count(`ea`.`id`) AS `attempt_count`, coalesce(`eqc`.`question_count`,0) AS `question_count` FROM (((((`exercisetag` `et` join `exercises` `e` on((`et`.`exercise_id` = `e`.`id`))) join `tags` `t` on((`et`.`tag_id` = `t`.`id`))) join `subjects` `s` on((`e`.`subject_id` = `s`.`id`))) left join `exercise_attempt` `ea` on((`e`.`id` = `ea`.`exe_id`))) left join (select `exercisequestion`.`exercise_id` AS `exercise_id`,count(`exercisequestion`.`id`) AS `question_count` from `exercisequestion` group by `exercisequestion`.`exercise_id`) `eqc` on((`et`.`exercise_id` = `eqc`.`exercise_id`))) GROUP BY `et`.`exercise_id`, `et`.`tag_id`, `e`.`title`, `e`.`creator_id`, `t`.`name`, `s`.`name`, `e`.`created_at` ;

-- --------------------------------------------------------

--
-- Structure for view `note_detail_view`
--
DROP TABLE IF EXISTS `note_detail_view`;

DROP VIEW IF EXISTS `note_detail_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `note_detail_view`  AS SELECT `n`.`owner_id` AS `user_id`, `n`.`id` AS `note_id`, `n`.`topic_id` AS `topic_id`, `nt`.`tag_id` AS `tag_id`, `n`.`title` AS `title`, `n`.`content` AS `content`, `n`.`updated_at` AS `last_update`, `t`.`name` AS `topic_name`, `tg`.`name` AS `tag_name`, (select count(0) from `note_shares` `ns` where (`ns`.`note_id` = `n`.`id`)) AS `share_count`, (select sum(json_extract(`e`.`data`,'$.duration_seconds')) from `events` `e` where ((`e`.`entity_type` = 'notes') and (`e`.`entity_id` = `n`.`id`) and (`e`.`event_type` = 'note_refered'))) AS `total_refer_time` FROM (((`notes` `n` left join `note_tags` `nt` on((`nt`.`note_id` = `n`.`id`))) left join `tags` `tg` on((`tg`.`id` = `nt`.`tag_id`))) left join `topics` `t` on((`t`.`id` = `n`.`topic_id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `profile_summary`
--
DROP TABLE IF EXISTS `profile_summary`;

DROP VIEW IF EXISTS `profile_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `profile_summary`  AS SELECT `u`.`id` AS `profile_id`, `us`.`subject_id` AS `subject_id`, `u`.`username` AS `username`, `u`.`display_name` AS `display_name`, `r`.`name` AS `role_name`, `u`.`created_at` AS `created_at`, `s`.`name` AS `subject_name`, coalesce(`u`.`profile_picture`,'uploads\\0profile.avif') AS `profile_picture`, `u`.`banned` AS `banned` FROM (((`user` `u` join `roles` `r` on((`u`.`role` = `r`.`role_id`))) join `experts` `us` on((`u`.`id` = `us`.`user_id`))) join `subjects` `s` on((`us`.`subject_id` = `s`.`id`))) WHERE (`r`.`name` = 'expert')union all select `u`.`id` AS `profile_id`,NULL AS `subject_id`,`u`.`username` AS `username`,`u`.`display_name` AS `display_name`,`r`.`name` AS `role_name`,`u`.`created_at` AS `created_at`,NULL AS `subject_name`,NULL AS `profile_picture`,`u`.`banned` AS `banned` from (`user` `u` join `roles` `r` on((`u`.`role` = `r`.`role_id`))) where (`r`.`name` <> 'expert')  ;

-- --------------------------------------------------------

--
-- Structure for view `user_requests_summary`
--
DROP TABLE IF EXISTS `user_requests_summary`;

DROP VIEW IF EXISTS `user_requests_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_requests_summary`  AS SELECT `q`.`user_id` AS `user_id`, `q`.`id` AS `request_id`, `q`.`subject` AS `subject`, `q`.`description` AS `description`, `q`.`review` AS `review`, `u`.`username` AS `user_name` FROM (`request` `q` join `user` `u` on((`q`.`user_id` = `u`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `user_topic_activity`
--
DROP TABLE IF EXISTS `user_topic_activity`;

DROP VIEW IF EXISTS `user_topic_activity`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_topic_activity`  AS SELECT `u`.`id` AS `user_id`, `t`.`name` AS `topic_name`, count(`n`.`id`) AS `note_count`, max(`n`.`updated_at`) AS `last_update`, sum((case when ((`e`.`event_type` = 'note_refered') and (`e`.`entity_type` = 'notes') and (`e`.`entity_id` = `n`.`id`)) then json_extract(`e`.`data`,'$.duration_seconds') else 0 end)) AS `total_referred_time` FROM (((`user` `u` left join `notes` `n` on((`n`.`owner_id` = `u`.`id`))) left join `topics` `t` on((`t`.`id` = `n`.`topic_id`))) left join `events` `e` on(((`e`.`user_id` = `u`.`id`) and (`e`.`entity_type` = 'notes') and (`e`.`entity_id` = `n`.`id`) and (`e`.`event_type` = 'note_refered')))) GROUP BY `u`.`id`, `t`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `view_questions_summary`
--
DROP TABLE IF EXISTS `view_questions_summary`;

DROP VIEW IF EXISTS `view_questions_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_questions_summary`  AS SELECT `q`.`id` AS `question_id`, `q`.`creator_id` AS `asked_user_id`, `q`.`title` AS `title`, `q`.`content` AS `content`, `q`.`created_at` AS `asked_date`, group_concat(distinct `t`.`name` order by `t`.`name` ASC separator ', ') AS `tags`, count(`a`.`id`) AS `answer_count`, max(`a`.`chosen`) AS `has_accepted_answer`, max(`a`.`created_at`) AS `last_answered_date` FROM (((`question` `q` left join `questiontag` `qt` on((`qt`.`question_id` = `q`.`id`))) left join `tags` `t` on((`t`.`id` = `qt`.`tag_id`))) left join `answer` `a` on((`a`.`q_id` = `q`.`id`))) GROUP BY `q`.`id`, `q`.`creator_id`, `q`.`title`, `q`.`content`, `q`.`created_at` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcement_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`q_id`) REFERENCES `question` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answer_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attempt_answer`
--
ALTER TABLE `attempt_answer`
  ADD CONSTRAINT `fk_answer_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `exercise_attempt` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answer_question` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_event_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exerciseanswer`
--
ALTER TABLE `exerciseanswer`
  ADD CONSTRAINT `exerciseanswer_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `exercisequestion` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercisequestion`
--
ALTER TABLE `exercisequestion`
  ADD CONSTRAINT `exercisequestion_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `exercises_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exercises_ibfk_2` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exercises_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exercisetag`
--
ALTER TABLE `exercisetag`
  ADD CONSTRAINT `exercisetag_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exercisetag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exercise_attempt`
--
ALTER TABLE `exercise_attempt`
  ADD CONSTRAINT `fk_attempt_exercise` FOREIGN KEY (`exe_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attempt_user` FOREIGN KEY (`u_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_note_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notes_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_shares`
--
ALTER TABLE `note_shares`
  ADD CONSTRAINT `note_shares_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_tags`
--
ALTER TABLE `note_tags`
  ADD CONSTRAINT `note_tags_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `fk_question_creator` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `questiontag`
--
ALTER TABLE `questiontag`
  ADD CONSTRAINT `questiontag_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questiontag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uservoteanswer`
--
ALTER TABLE `uservoteanswer`
  ADD CONSTRAINT `uservoteanswer_ibfk_1` FOREIGN KEY (`a_id`) REFERENCES `answer` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uservoteexercise`
--
ALTER TABLE `uservoteexercise`
  ADD CONSTRAINT `uservoteexercise_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `uservoteexercise_ibfk_2` FOREIGN KEY (`u_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uservotequestion`
--
ALTER TABLE `uservotequestion`
  ADD CONSTRAINT `uservotequestion_ibfk_1` FOREIGN KEY (`q_id`) REFERENCES `question` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
