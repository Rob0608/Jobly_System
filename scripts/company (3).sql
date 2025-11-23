-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 19, 2025 at 12:37 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `company`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE IF NOT EXISTS `applicants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `contact` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `birth_place` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `barangay` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `municipality` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `resume` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `verification_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `job_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `schedule_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `contact`, `email`, `birth_place`, `barangay`, `city`, `municipality`, `address`, `resume`, `password`, `verification_code`, `is_verified`, `created_at`, `job_title`, `status`, `schedule_date`) VALUES
(52, 'BABY AIZA', 'asas', 'DILAY', '2004-02-03', '', '', 'aizablnco@gmail.com', '', '', '', '', '', '', '$2y$10$XaVcRNTrWi4j6kK5grGg6eWd35QOCxVlScsByD9i/gfrAAlWxyU6q', NULL, 1, '2025-11-19 04:01:27', '', 'pending', NULL),
(53, 'Rob', 'Mendoza', 'Abarintos', '2004-06-08', 'Male', '9081384870', 'dilayaiza1@gmail.com', '', '', '', '', '', '', '$2y$10$FMLe3knKo/DC3Om5IDNBOum6WUNn4z.xiM.1F1FZ45BFtXlaxhFx2', NULL, 1, '2025-11-19 04:27:13', '', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `story` text,
  `mission` text,
  `vision` text,
  `products_services` text,
  `job_position` text,
  `avatar` varchar(255) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'pending',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `address`, `country_code`, `phone`, `email`, `password`, `website`, `story`, `mission`, `vision`, `products_services`, `job_position`, `avatar`, `verification_token`, `is_verified`, `verification_code`, `created_at`, `updated_at`, `status`, `latitude`, `longitude`) VALUES
(58, 'Accenture Inc.', 'Oriental Mindoro, Calapan City, Ilaya', '+63', '9081384870', 'robabarintos@gmail.com', '$2y$10$7FCmdwZk6B4Cmz2S7N59FuIJ/wkE1ovC2Zp2OzZlk4bijth570hp2', 'https://accenture.inc.com.ph', 'e', 'e', 'e', 'e', 'Software Engineer', 'logo_58_1763521186.jpg', NULL, 1, '8247', '2025-11-15 04:14:50', '2025-11-19 02:59:46', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_applications`
--

DROP TABLE IF EXISTS `company_applications`;
CREATE TABLE IF NOT EXISTS `company_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `applicant_email` varchar(255) NOT NULL,
  `position_id` int NOT NULL,
  `resume` varchar(255) NOT NULL,
  `message` text,
  `status` varchar(32) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_jobs`
--

DROP TABLE IF EXISTS `company_jobs`;
CREATE TABLE IF NOT EXISTS `company_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `position` varchar(255) NOT NULL,
  `description` text,
  `requirements` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `company_jobs`
--

INSERT INTO `company_jobs` (`id`, `company_id`, `position`, `description`, `requirements`, `created_at`) VALUES
(1, 58, 'Software Developer', 'ewew', 'College Graduate', '2025-11-15 08:18:19'),
(2, 58, 'Full Stack', 'YSYST', 'College Graduate', '2025-11-15 08:26:17');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
