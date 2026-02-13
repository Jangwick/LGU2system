-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 03:41 PM
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
-- Database: `lrms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 17:08:36'),
(2, 1, 'login', NULL, NULL, 'User logged in successfully', '127.0.0.1', NULL, '2025-11-21 17:11:02'),
(3, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 17:21:53'),
(4, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 17:21:57'),
(5, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:16:18'),
(6, 2, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:16:25'),
(7, 2, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:16:39'),
(8, 3, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:16:48'),
(9, 3, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:17:02'),
(10, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:17:08'),
(11, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:19:01'),
(12, 3, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:20:11'),
(13, 3, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:22:06'),
(14, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:22:15'),
(15, 4, 'update', NULL, NULL, 'Updated account settings', NULL, NULL, '2025-11-21 18:36:49'),
(16, 4, 'update', NULL, NULL, 'Updated account settings', NULL, NULL, '2025-11-21 18:36:54'),
(17, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:51:08'),
(18, 3, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:51:15'),
(19, 3, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:51:23'),
(20, 2, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:51:33'),
(21, 2, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:51:53'),
(22, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-21 18:51:59'),
(23, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-21 18:52:22'),
(24, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 04:03:04'),
(25, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 04:47:21'),
(26, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 04:47:27'),
(27, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:13:14'),
(28, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:15:03'),
(29, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:16:17'),
(30, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:16:41'),
(31, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:17:53'),
(32, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:18:36'),
(33, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:18:41'),
(34, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:20:04'),
(35, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:20:15'),
(36, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:20:38'),
(37, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:24:45'),
(38, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:24:59'),
(39, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:25:40'),
(40, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:26:34'),
(41, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:26:42'),
(43, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:36:18'),
(44, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:36:37'),
(45, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:38:52'),
(46, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:39:01'),
(47, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:39:22'),
(48, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:40:20'),
(49, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:40:28'),
(50, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:40:55'),
(51, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:41:14'),
(52, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:41:25'),
(53, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:41:28'),
(54, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:42:02'),
(55, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:42:25'),
(56, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:42:29'),
(57, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:42:33'),
(58, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:42:36'),
(59, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 05:42:39'),
(60, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 05:42:42'),
(61, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 09:12:56'),
(62, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 18:06:31'),
(63, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 18:06:47'),
(64, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-22 18:07:48'),
(65, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-22 18:07:58'),
(66, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-23 18:48:57'),
(67, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-24 05:49:43'),
(68, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-24 06:06:33'),
(69, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-24 06:18:11'),
(70, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-24 06:22:07'),
(72, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-25 16:42:34'),
(73, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-26 07:21:29'),
(74, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-26 08:22:04'),
(75, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-26 08:23:03'),
(76, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-26 08:34:59'),
(77, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-26 08:35:15'),
(78, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-26 15:15:28'),
(79, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-28 20:49:27'),
(80, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-11-28 20:49:30'),
(81, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-28 20:49:34'),
(82, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-11-30 16:38:07'),
(83, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-01 15:46:26'),
(84, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-01 16:05:16'),
(85, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-01 16:05:52'),
(86, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-01 16:06:12'),
(87, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 03:02:49'),
(88, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 03:03:34'),
(89, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 03:03:46'),
(90, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 03:04:36'),
(91, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 03:07:15'),
(92, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 03:07:19'),
(93, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 05:59:10'),
(94, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 05:59:14'),
(95, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:05:06'),
(96, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:05:09'),
(97, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:05:12'),
(98, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:05:19'),
(99, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:13:14'),
(100, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:13:17'),
(101, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:16:08'),
(102, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:16:13'),
(103, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:18:52'),
(104, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:21:50'),
(105, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:21:53'),
(106, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:24:33'),
(107, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:24:40'),
(108, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:39:15'),
(109, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:41:28'),
(110, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:43:57'),
(111, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:44:02'),
(112, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:45:52'),
(113, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:46:04'),
(114, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:46:18'),
(115, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:46:22'),
(116, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:50:14'),
(117, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:50:18'),
(118, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 06:53:32'),
(119, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 06:53:57'),
(120, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:33:58'),
(121, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:48:57'),
(122, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:49:05'),
(123, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:49:19'),
(124, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:49:22'),
(125, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:49:39'),
(126, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:49:41'),
(127, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:49:45'),
(128, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:49:53'),
(129, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:51:23'),
(130, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:51:26'),
(131, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:52:43'),
(132, 4, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:52:52'),
(133, 4, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:53:06'),
(134, 3, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:53:11'),
(135, 3, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:53:19'),
(136, 2, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:54:21'),
(137, 2, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 07:56:11'),
(138, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 07:57:21'),
(139, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 08:22:27'),
(140, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 08:22:37'),
(141, 1, 'update', NULL, NULL, 'Updated profile information', NULL, NULL, '2025-12-02 08:24:54'),
(142, 1, 'update', NULL, NULL, 'Updated profile information', NULL, NULL, '2025-12-02 08:25:03'),
(143, 1, 'update', NULL, NULL, 'Updated profile picture', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 08:32:06'),
(144, 1, 'update', NULL, NULL, 'Updated profile picture', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-02 08:33:44'),
(145, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 08:44:52'),
(146, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 08:45:23'),
(147, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 15:36:16'),
(148, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 16:20:58'),
(149, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 16:23:33'),
(150, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 16:23:42'),
(151, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 16:24:18'),
(152, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-02 16:46:30'),
(153, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-02 16:46:38'),
(154, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-03 04:41:18'),
(155, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-03 05:40:46'),
(156, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-03 05:41:50'),
(157, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-03 09:07:12'),
(158, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-03 09:09:03'),
(159, 1, 'logout', NULL, NULL, 'User logged out', '::1', NULL, '2025-12-03 09:56:26'),
(160, 1, 'login', NULL, NULL, 'User logged in successfully', '::1', NULL, '2025-12-03 09:56:49'),
(161, 1, 'document_upload', 'documents', 15, 'User uploaded document: asdasdasdasd | Changes: New: {\"reference_number\":\"211111111111111\",\"document_type\":\"session\",\"file_size\":1475}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 10:07:42'),
(162, 1, 'document_export', 'documents', NULL, 'Exported 6 document(s) as CSV (list) | Changes: New: {\"export_type\":\"list\",\"format\":\"csv\",\"document_count\":6,\"filters\":{\"search\":\"\",\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"user_role\":\"administrator\",\"limit\":10000,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 10:10:37'),
(163, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:44:34'),
(164, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":9,\"session_duration_formatted\":\"00:00:09\"}', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-03 14:44:43'),
(165, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:44:59'),
(166, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":14,\"session_duration_formatted\":\"00:00:14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:45:13'),
(167, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:45:30'),
(168, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":152,\"session_duration_formatted\":\"00:02:32\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:48:02'),
(169, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:55:50'),
(170, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 05:42:46'),
(171, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 05:57:28'),
(172, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-09 10:34:24'),
(173, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-10 05:48:11'),
(174, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":8,\"session_duration_formatted\":\"00:00:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-10 05:48:19'),
(175, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-10 05:48:22'),
(176, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 08:59:13'),
(177, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 15:47:29'),
(178, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 16:37:26'),
(179, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":79,\"session_duration_formatted\":\"00:01:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 16:38:45'),
(180, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-12 16:47:35'),
(181, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 06:15:03'),
(182, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 05:17:58'),
(183, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":371,\"session_duration_formatted\":\"00:06:11\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 05:24:09'),
(184, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-21 05:24:44'),
(185, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":17,\"session_duration_formatted\":\"00:00:17\"}', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-21 05:25:01'),
(186, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-21 05:25:07'),
(187, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-17 09:23:00'),
(188, 13, 'USER_REGISTERED', 'sessions', NULL, 'User performed action: USER_REGISTERED | Details: {\"email\":\"Johnrick5609@gmail.com\",\"role\":\"MANAGER\",\"department\":\"Mayor\'s Office\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 15:54:03'),
(189, 13, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"Johnrick5609@gmail.com\",\"role\":\"\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 15:54:14'),
(190, 13, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"Johnrick5609@gmail.com\",\"role\":\"\",\"session_duration_seconds\":21,\"session_duration_formatted\":\"00:00:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 15:54:35'),
(191, 1, 'login_failed', 'sessions', NULL, 'Failed login attempt for: admin@lgu.gov.ph | Details: {\"email\":\"admin@lgu.gov.ph\",\"reason\":\"invalid_password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:04:23'),
(192, 1, 'login_failed', 'sessions', NULL, 'Failed login attempt for: admin@lgu.gov.ph | Details: {\"email\":\"admin@lgu.gov.ph\",\"reason\":\"invalid_password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:04:28'),
(193, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:04:31'),
(194, 1, 'login_failed', 'sessions', NULL, 'Failed login attempt for: admin@lgu.gov.ph | Details: {\"email\":\"admin@lgu.gov.ph\",\"reason\":\"invalid_password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:12:12'),
(195, 1, 'login_failed', 'sessions', NULL, 'Failed login attempt for: admin@lgu.gov.ph | Details: {\"email\":\"admin@lgu.gov.ph\",\"reason\":\"invalid_password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:12:14'),
(196, 1, 'login_failed', 'sessions', NULL, 'Failed login attempt for: admin@lgu.gov.ph | Details: {\"email\":\"admin@lgu.gov.ph\",\"reason\":\"invalid_password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:12:16'),
(197, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:12:19'),
(198, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":2,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:16:50'),
(199, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":2,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:19:26'),
(200, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":2,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:19:31'),
(201, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":2,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:29'),
(202, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":1,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:34'),
(203, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:35'),
(204, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":1,\"filters\":{\"type\":\"session\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:36'),
(205, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:37'),
(206, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"committee\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:37'),
(207, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:38'),
(208, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":1,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:40'),
(209, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:41'),
(210, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:41'),
(211, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":1,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:42'),
(212, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:47'),
(213, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"committee\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:48'),
(214, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"research\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:48'),
(215, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":0,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:49'),
(216, 1, 'search', 'search', NULL, 'Search query: asdasd | Changes: New: {\"query\":\"asdasd\",\"results_count\":1,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:24:52'),
(217, 1, 'search', 'search', NULL, 'Search query: 2 | Changes: New: {\"query\":\"2\",\"results_count\":6,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:25:39'),
(218, 1, 'search', 'search', NULL, 'Search query: 22 | Changes: New: {\"query\":\"22\",\"results_count\":0,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:25:44'),
(219, 1, 'search', 'search', NULL, 'Search query: 2 | Changes: New: {\"query\":\"2\",\"results_count\":6,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:25:47'),
(220, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":5,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:06'),
(221, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":5,\"filters\":{\"type\":\"\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:14'),
(222, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:15'),
(223, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:15'),
(224, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"session\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:16'),
(225, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":1,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:16'),
(226, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"committee\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:21'),
(227, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"research\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:21'),
(228, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"committee\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:22'),
(229, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"session\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:23'),
(230, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:26:23'),
(231, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:38'),
(232, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:39'),
(233, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:40'),
(234, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"session\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:40'),
(235, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":1,\"filters\":{\"type\":\"agenda\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:41'),
(236, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"committee\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:41'),
(237, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"research\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:41'),
(238, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":0,\"filters\":{\"type\":\"resolution\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:41'),
(239, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"ordinance\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:42'),
(240, 1, 'search', 'search', NULL, 'Search query: 123 | Changes: New: {\"query\":\"123\",\"results_count\":2,\"filters\":{\"type\":\"session\",\"status\":\"\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":20,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 16:27:42'),
(241, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 05:58:14'),
(242, 1, 'report_export', 'search', NULL, 'Exported search results for:  | Changes: New: {\"query\":\"\",\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\"},\"format\":\"csv\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:15'),
(243, 1, 'search', 'search', NULL, 'Search query: REF: asdasdasd (Mode: semantic) | Changes: New: {\"query\":\"REF: asdasdasd\",\"mode\":\"semantic\",\"results_count\":0,\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":10,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:41'),
(244, 1, 'search', 'search', NULL, 'Search query: REF: asdasdasd (Mode: semantic) | Changes: New: {\"query\":\"REF: asdasdasd\",\"mode\":\"semantic\",\"results_count\":0,\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":10,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:43'),
(245, 1, 'search', 'search', NULL, 'Search query: REF: asdasdasd (Mode: hybrid) | Changes: New: {\"query\":\"REF: asdasdasd\",\"mode\":\"hybrid\",\"results_count\":0,\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":10,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:44'),
(246, 1, 'search', 'search', NULL, 'Search query: REF: asdasdasd (Mode: hybrid) | Changes: New: {\"query\":\"REF: asdasdasd\",\"mode\":\"hybrid\",\"results_count\":0,\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":10,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:44'),
(247, 1, 'search', 'search', NULL, 'Search query: asdasdasd (Mode: hybrid) | Changes: New: {\"query\":\"asdasdasd\",\"mode\":\"hybrid\",\"results_count\":1,\"filters\":{\"type\":\"ordinance\",\"status\":\"approved\",\"date_from\":\"\",\"date_to\":\"\",\"tags\":\"\",\"limit\":10,\"offset\":0}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:13:50'),
(248, 1, 'document_download', 'documents', 15, 'User downloaded document: asdasdasdasd | Changes: New: {\"file_name\":\"top_uploaders_2025-12-02_091047.doc\",\"file_type\":\"application\\/msword\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:14:35'),
(249, 1, 'DOCUMENT_DOWNLOAD', 'documents', 15, 'Document accessed: download', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:14:35'),
(250, 1, 'DOCUMENT_VIEW', 'documents', 15, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:17:19'),
(251, 1, 'DOCUMENT_VIEW', 'documents', 15, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:17:24'),
(252, 1, 'document_download', 'documents', 15, 'User downloaded document: asdasdasdasd | Changes: New: {\"file_name\":\"top_uploaders_2025-12-02_091047.doc\",\"file_type\":\"application\\/msword\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:17:26'),
(253, 1, 'DOCUMENT_DOWNLOAD', 'documents', 15, 'Document accessed: download', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:17:26'),
(254, 1, 'role_change', 'users', 13, 'Changed role for Johnrick from  to viewer | Changes: Previous: {\"name\":\"Johnrick\",\"email\":\"Johnrick5609@gmail.com\",\"full_name\":\"Johnrick\",\"role\":\"\",\"department\":\"Mayor\'s Office\",\"status\":\"active\"} | New: {\"status\":\"active\",\"name\":\"Johnrick\",\"email\":\"Johnrick5609@gmail.com\",\"full_name\":\"Johnrick\",\"role\":\"viewer\",\"department\":\"Mayor\'s Office\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:24:37'),
(255, NULL, 'INTEGRATION_RECEIVED', 'integrated_records', 4, 'Received agendas from Public Records Portal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:36:33'),
(256, 1, 'DOCUMENT_VIEW', 'documents', 8, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:36:47'),
(257, NULL, 'INTEGRATION_RECEIVED', 'integrated_records', 5, 'Received research from Public Records Portal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:39:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(258, 1, 'INTEGRATION_IMPORTED', 'legislative_documents', 16, 'Imported agendas Record #4 as Document #16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:41:42'),
(259, NULL, 'INTEGRATION_RECEIVED', 'integrated_records', 6, 'Received agendas from Public Records Portal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:46:07'),
(260, 1, 'INTEGRATION_IMPORTED', 'legislative_documents', 17, 'Imported agendas Record #6 as Document #17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:46:11'),
(261, NULL, 'INTEGRATION_RECEIVED', 'integrated_records', 7, 'Received sessions from Sangguniang Office App', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:54:07'),
(262, 1, 'INTEGRATION_IMPORTED', 'legislative_documents', 18, 'Imported sessions Record #7 as Document #18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:54:11'),
(263, 1, 'DOCUMENT_VIEW', 'documents', 17, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 06:56:01'),
(264, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:01:25'),
(265, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:03:21'),
(266, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:03:35'),
(267, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:03:38'),
(268, 1, 'document_download', 'documents', 18, 'User downloaded document: 12312312 | Changes: New: {\"file_name\":\"Synced Data\",\"file_type\":\"application\\/json\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:03:56'),
(269, 1, 'DOCUMENT_DOWNLOAD', 'documents', 18, 'Document accessed: download', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:03:56'),
(270, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:20:39'),
(271, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:20:50'),
(272, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:21:21'),
(273, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:21:30'),
(274, 1, 'document_delete', 'documents', 17, 'User deleted document: ASDAD123ad | Changes: New: {\"document_type\":\"\",\"reference_number\":\"AGE-2026-0006\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:21:37'),
(275, 1, 'document_delete', 'documents', 16, 'User deleted document: asdasdasd | Changes: New: {\"document_type\":\"\",\"reference_number\":\"AGE-2026-0004\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:21:43'),
(276, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:22:56'),
(277, 1, 'DOCUMENT_VIEW', 'documents', 18, 'Document accessed: view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:22:57'),
(278, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":6475,\"session_duration_formatted\":\"01:47:55\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:46:09'),
(279, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 07:46:21'),
(280, 1, 'login', 'sessions', NULL, 'User logged into the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"remember_me\":false,\"login_method\":\"password\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 14:18:20'),
(281, 1, 'logout', 'sessions', NULL, 'User logged out of the system | Details: {\"email\":\"admin@lgu.gov.ph\",\"role\":\"administrator\",\"session_duration_seconds\":586,\"session_duration_formatted\":\"00:09:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 14:28:06');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','revoked') DEFAULT 'active',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_access_logs`
--

CREATE TABLE `document_access_logs` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_type` enum('view','download','edit','delete') DEFAULT 'view',
  `accessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_access_logs`
--

INSERT INTO `document_access_logs` (`id`, `document_id`, `user_id`, `access_type`, `accessed_at`) VALUES
(1, 1, 1, 'download', '2025-11-21 18:11:30'),
(2, 1, 4, 'download', '2025-11-21 18:36:13'),
(3, 7, 1, 'download', '2025-11-26 08:23:54'),
(4, 6, 1, 'download', '2025-11-26 08:25:16'),
(5, 11, 1, 'download', '2025-12-02 17:39:23'),
(6, 15, 1, 'download', '2026-02-08 06:14:35'),
(7, 15, 1, 'view', '2026-02-08 06:17:19'),
(8, 15, 1, 'view', '2026-02-08 06:17:24'),
(9, 15, 1, 'download', '2026-02-08 06:17:26'),
(10, 8, 1, 'view', '2026-02-08 06:36:47'),
(11, 17, 1, 'view', '2026-02-08 06:56:01'),
(12, 18, 1, 'view', '2026-02-08 07:01:25'),
(13, 18, 1, 'view', '2026-02-08 07:03:21'),
(14, 18, 1, 'view', '2026-02-08 07:03:35'),
(15, 18, 1, 'view', '2026-02-08 07:03:38'),
(16, 18, 1, 'download', '2026-02-08 07:03:56'),
(17, 18, 1, 'view', '2026-02-08 07:20:39'),
(18, 18, 1, 'view', '2026-02-08 07:20:50'),
(19, 18, 1, 'view', '2026-02-08 07:21:21'),
(20, 18, 1, 'view', '2026-02-08 07:21:30'),
(21, 18, 1, 'view', '2026-02-08 07:22:56'),
(22, 18, 1, 'view', '2026-02-08 07:22:57');

-- --------------------------------------------------------

--
-- Table structure for table `document_links`
--

CREATE TABLE `document_links` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `linked_document_id` int(11) NOT NULL,
  `link_type` enum('related','supersedes','superseded_by','amends','amended_by','reference') DEFAULT 'related',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_tags`
--

CREATE TABLE `document_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_tag_relationships`
--

CREATE TABLE `document_tag_relationships` (
  `document_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `change_description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integrated_records`
--

CREATE TABLE `integrated_records` (
  `id` int(11) NOT NULL,
  `module_type` varchar(50) NOT NULL,
  `external_id` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `data_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_payload`)),
  `status` enum('pending','processed','synced','failed') DEFAULT 'pending',
  `source_system` varchar(100) DEFAULT 'External API',
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `integrated_records`
--

INSERT INTO `integrated_records` (`id`, `module_type`, `external_id`, `title`, `summary`, `data_payload`, `status`, `source_system`, `received_at`, `processed_at`) VALUES
(1, 'ordinance', NULL, 'City Ordinance No. 2024-001', 'An ordinance prohibiting single-use plastics.', '{\"enacted_date\": \"2024-01-15\", \"proponent\": \"Hon. John Doe\"}', 'synced', 'E-Legislative System', '2026-02-08 06:32:53', NULL),
(2, 'session', NULL, 'Regular Session #42', 'Weekly budget deliberation session.', '{\"date\": \"2024-02-01\", \"attendees\": 12}', 'processed', 'Session Manager Pro', '2026-02-08 06:32:53', NULL),
(3, 'agenda', NULL, 'Environment Committee Meeting', 'Discussion on waste management.', '{\"priority\": \"high\"}', 'pending', 'Agenda Planner', '2026-02-08 06:32:53', NULL),
(4, 'agendas', NULL, 'asdasdasd', 'asdasd', '[]', 'synced', 'Public Records Portal', '2026-02-08 06:36:33', '2026-02-08 06:41:42'),
(5, 'research', NULL, 'ordinance0223', '213123', '[]', 'pending', 'Public Records Portal', '2026-02-08 06:39:36', NULL),
(6, 'agendas', '21321322', 'ASDAD123ad', 'asdasd', '{\"document_date\":\"2026-02-08\",\"tags\":\"bydget\"}', 'synced', 'Public Records Portal', '2026-02-08 06:46:07', '2026-02-08 06:46:11'),
(7, 'sessions', '21321322', '12312312', '12312', '{\"document_date\":\"2026-02-08\",\"tags\":\"asdasdasd\"}', 'synced', 'Sangguniang Office App', '2026-02-08 06:54:07', '2026-02-08 06:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `integration_api_keys`
--

CREATE TABLE `integration_api_keys` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL COMMENT 'Name of the integration module',
  `api_key` varchar(64) NOT NULL COMMENT 'Unique API key',
  `api_secret` varchar(128) NOT NULL COMMENT 'Hashed API secret',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Allowed permissions' CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `integration_api_keys`
--

INSERT INTO `integration_api_keys` (`id`, `module_name`, `api_key`, `api_secret`, `permissions`, `is_active`, `last_used_at`, `created_at`, `created_by`) VALUES
(6, 'committee-management', 'cm_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', '4626d995062df9c621d00c464a8792a179cc31b24f04454ad4761b3854f867d4', '{\"send_notification\": true, \"send_file\": true}', 1, NULL, '2025-12-03 16:44:18', NULL),
(7, 'public-hearings', 'ph_b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7', '8be798ba30f526de09d7850007196f85b4e4737d67adee55961c5fdbba5fccad', '{\"send_notification\": true, \"send_file\": true}', 1, NULL, '2025-12-03 16:44:18', NULL),
(8, 'archives', 'ar_c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8', '08c0d9e16a158a788ad2f9c62bb71cabf6bf48b284196fdc6d3e61fc667b256d', '{\"send_notification\": true, \"send_file\": true}', 1, NULL, '2025-12-03 16:44:18', NULL),
(9, 'consultations', 'cn_d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9', 'f49724f3d5d8919327daa9049fb2b3e6fd77d461f76f5a7d551204464901e80f', '{\"send_notification\": true, \"send_message\": true}', 1, NULL, '2025-12-03 16:44:18', NULL),
(10, 'research', 'rs_e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0', '864e2177708a3ddc86e2f0ccd52d497912bd6872f0ab981525c6b53524ba6afc', '{\"send_notification\": true, \"send_file\": true}', 1, NULL, '2025-12-03 16:44:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `legislative_documents`
--

CREATE TABLE `legislative_documents` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `title` varchar(500) NOT NULL,
  `document_type` enum('ordinance','resolution','session','agenda','committee','voting','hearing','archive','consultation','research') NOT NULL,
  `document_date` date NOT NULL,
  `status` enum('draft','pending','approved','rejected','archived','superseded') DEFAULT 'draft',
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `source_module` varchar(100) DEFAULT 'manual',
  `source_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Flexible metadata storage for custom fields per document type' CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `legislative_documents`
--

INSERT INTO `legislative_documents` (`id`, `reference_number`, `title`, `document_type`, `document_date`, `status`, `file_path`, `file_name`, `file_size`, `file_type`, `description`, `tags`, `source_module`, `source_id`, `uploaded_by`, `created_at`, `updated_at`, `deleted_at`, `metadata`) VALUES
(1, 'sdasdasdasd23232', 'asdasdasd', 'resolution', '2025-11-21', 'rejected', 'C:\\xampp\\htdocs\\LLRMSystem/storage/documents/resolution/1763745509_ddec8991d667e59e.docx', 'mean median mode.docx', 401331, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'asdasdasd', NULL, 'manual', NULL, 1, '2025-11-21 17:18:29', '2025-11-26 08:26:16', '2025-11-26 08:26:16', NULL),
(4, 'sdasdasdasd23232asdasdasd', 'asdasdasdasdasd', 'consultation', '2025-11-21', 'approved', 'C:\\xampp\\htdocs\\LLRMSystem/storage/documents/consultation/1763745620_d5f67151fd7955e2.docx', 'cover softbind.docx', 8435588, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'asdasdasd', NULL, 'manual', NULL, 1, '2025-11-21 17:20:20', '2025-11-21 17:21:49', '2025-11-21 17:21:49', NULL),
(5, 'asdasdsd', 'asdasd', 'committee', '2025-11-22', 'pending', 'C:\\xampp\\htdocs\\LLRMSystem/storage/documents/committee/1763790591_9826e69d8744276e.docx', 'IDEA-FOR-MODULES.docx', 8727, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'asdasd', '', 'manual', NULL, 1, '2025-11-22 05:49:51', '2025-11-26 08:26:16', '2025-11-26 08:26:16', NULL),
(6, 'asdasd', 'asdasdas', 'research', '2025-11-22', 'pending', 'C:\\xampp\\htdocs\\LLRMSystem/storage/documents/research/1763790676_19203d5ad4156846.docx', 'Imarket4110-Capstone_template-12345.docx', 8576006, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'dasdasd', '', 'manual', NULL, 1, '2025-11-22 05:51:16', '2025-11-26 08:26:09', '2025-11-26 08:26:09', NULL),
(7, '123123123213', '12312312', 'session', '2025-11-26', 'draft', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/session/1764145429_0d5f0e681973f16b.docx', 'mean median mode (1).docx', 401331, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '3213213', 'asdasdasd', 'manual', NULL, 1, '2025-11-26 08:23:49', '2025-11-26 08:23:49', NULL, NULL),
(8, 'asd123123', '123123', 'agenda', '2025-11-21', 'pending', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/agenda/1764145547_432c984de12c59eb.docx', 'Imarket4110-Capstone_template-12345 (1).docx', 582, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '123123', '', 'manual', NULL, 1, '2025-11-26 08:25:47', '2025-11-26 08:25:47', NULL, NULL),
(9, '213123123', '123123', 'hearing', '2025-11-28', 'archived', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/hearing/1764145598_791be490f628c7d3.docx', 'IT_ELECTIVE_3_RESEARCH_CHAPTER_3.docx', 170951, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '123123', '', 'manual', NULL, 1, '2025-11-26 08:26:38', '2025-12-02 07:52:37', '2025-12-02 07:52:37', NULL),
(10, 'asdasdasd', '2123123', 'ordinance', '2025-12-03', 'approved', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/ordinance/1764661660_9e667880d6459903.pdf', 'QUESTIONNAIRE.pdf', 217706, 'application/pdf', 'asdasdasd', NULL, 'manual', NULL, 1, '2025-12-02 07:47:40', '2025-12-02 07:49:35', NULL, NULL),
(11, '123123123123', '123123', 'ordinance', '2025-03-08', 'approved', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/ordinance/1764661950_77bf82a87773a603.pdf', 'QUESTIONNAIRE.pdf', 217706, 'application/pdf', '12312321', '', 'manual', NULL, 1, '2025-12-02 07:52:30', '2025-12-02 07:52:30', NULL, NULL),
(14, '213213123123', '123123', 'session', '2025-12-03', 'approved', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/session/1764756310_990e50904a536c6c.pdf', 'QUESTIONNAIRE (1).pdf', 217706, 'application/pdf', 'sadasd', '', 'manual', NULL, 1, '2025-12-03 10:05:10', '2025-12-03 10:05:10', NULL, NULL),
(15, '211111111111111', 'asdasdasdasd', 'session', '2025-12-03', 'approved', 'C:\\xampp\\htdocs\\LGU2system\\LLRMSystem/storage/documents/session/1764756462_b2701d7bce35cb26.doc', 'top_uploaders_2025-12-02_091047.doc', 1475, 'application/msword', 'asdasd', '', 'manual', NULL, 1, '2025-12-03 10:07:42', '2025-12-03 10:07:42', NULL, NULL),
(16, 'AGE-2026-0004', 'asdasdasd', '', '2026-02-08', 'draft', 'external_sync', 'Synced Data', 0, 'application/json', 'asdasd', NULL, 'integration', 4, 1, '2026-02-08 06:41:42', '2026-02-08 07:21:43', '2026-02-08 07:21:43', NULL),
(17, 'AGE-2026-0006', 'ASDAD123ad', '', '2026-02-08', 'draft', 'external_sync', 'Synced Data', 0, 'application/json', 'asdasd', 'bydget', 'integration', 6, 1, '2026-02-08 06:46:11', '2026-02-08 07:21:37', '2026-02-08 07:21:37', NULL),
(18, 'SES-2026-0007', '12312312', 'session', '2026-02-08', 'draft', 'external_sync', 'Synced Data', 0, 'application/json', '12312', 'asdasdasd', 'integration', 7, 1, '2026-02-08 06:54:11', '2026-02-08 06:54:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Target user (NULL for broadcast)',
  `type` varchar(50) NOT NULL COMMENT 'notification type: file, message, alert, system, integration',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `source_module` varchar(100) DEFAULT NULL COMMENT 'Source integration module',
  `source_id` varchar(100) DEFAULT NULL COMMENT 'External reference ID from source',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional JSON data' CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `source_module`, `source_id`, `priority`, `data`, `is_read`, `read_at`, `created_at`, `expires_at`) VALUES
(1, 1, 'integration', 'Welcome to Notifications', 'The notification system is now active and ready for integration with external modules.', 'system', NULL, 'normal', NULL, 1, '2025-12-12 09:10:46', '2025-12-03 16:46:35', NULL),
(2, 1, 'file', 'New Resolution Received', 'Resolution No. 2025-001 has been submitted from Committee Management for review.', 'committee-management', NULL, 'high', '{\"file_name\": \"resolution_2025_001.pdf\", \"file_type\": \"resolution\", \"committee\": \"Finance Committee\"}', 1, '2025-12-03 16:57:32', '2025-12-03 16:49:56', NULL),
(3, 1, 'integration', 'Public Hearing Scheduled', 'A new public hearing has been scheduled for Ordinance No. 2025-015 on December 15, 2025.', 'public-hearings', NULL, 'normal', '{\"hearing_date\": \"2025-12-15\", \"ordinance\": \"2025-015\", \"venue\": \"City Hall Session Room\"}', 1, '2025-12-12 09:10:46', '2025-12-03 16:19:56', NULL),
(4, 1, 'message', 'Citizen Feedback Received', 'Juan Dela Cruz submitted feedback regarding the proposed traffic ordinance.', 'consultations', NULL, 'normal', '{\"sender_name\": \"Juan Dela Cruz\", \"sender_email\": \"juan@email.com\", \"feedback_type\": \"suggestion\"}', 1, '2025-12-12 09:10:46', '2025-12-03 14:49:56', NULL),
(5, NULL, 'system', 'System Maintenance Notice', 'Scheduled maintenance will occur on December 10, 2025 from 10PM to 12MN.', NULL, NULL, 'urgent', '{\"maintenance_date\": \"2025-12-10\", \"duration\": \"2 hours\"}', 1, '2025-12-03 16:57:29', '2025-12-02 16:49:56', NULL),
(6, 1, 'file', 'Archive Document Available', 'Historical Ordinance from 2020 has been digitized and added to the archives.', 'archives', NULL, 'low', '{\"file_name\": \"ordinance_2020_045.pdf\", \"year\": \"2020\", \"document_type\": \"ordinance\"}', 1, '2025-12-12 09:10:46', '2025-12-03 13:49:56', NULL),
(7, 1, 'alert', 'Document Expiring Soon', 'Resolution No. 2024-089 is set to expire in 7 days. Please review for renewal.', NULL, NULL, 'high', '{\"document_id\": \"2024-089\", \"expiry_date\": \"2025-12-11\", \"action_required\": \"renewal\"}', 1, '2025-12-06 17:37:22', '2025-12-03 16:44:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(2, 'admin@lgu.gov.ph', 'ea021ac7c6bec5b2ebe4e72e4fd67d07c20754f7a9bfcec30a90424cf08c6bfd', '2025-11-25 18:01:54', '2025-11-25 16:01:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrator','officer','staff','viewer') DEFAULT 'viewer',
  `department` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `full_name`, `password`, `role`, `department`, `phone`, `position`, `profile_picture`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@lgu.gov.ph', 'admin', 'Admin User', '$2y$10$NvyzUV4NJ2dsuuTZrvC0UOzSzFBiiZaeOm6q8WUejcexdVnUV4lye', 'administrator', 'IT Department', '1954654564', 'secretary', 'profile_1_1764664424.jpg', 'active', '2025-11-21 17:03:16', '2025-12-02 08:33:44'),
(2, 'Legislative Officer', 'officer@lgu.gov.ph', 'officer', 'Legislative Officer', '$2y$10$EXa.a1nL.gOexyKHJ0Sh0OGYBpr6Qn/UZ.tN9clzzzDwd46yIqA/W', 'officer', 'Legislative Office', NULL, NULL, NULL, 'active', '2025-11-21 17:03:16', '2025-11-22 05:38:30'),
(3, 'Staff Member', 'staff@lgu.gov.ph', 'staff', 'Staff Member', '$2y$10$DOvtMUSOZymhr.YJi0Z/je8lhABKTYLnxxPfvgPFI9I3Bk4edmdDC', 'staff', 'Document Management', NULL, NULL, NULL, 'active', '2025-11-21 17:03:16', '2025-11-22 05:38:30'),
(4, 'Document Viewer', 'viewer@lgu.gov.ph', 'viewer', 'Document Viewer', '$2y$10$Rdo9/rShRsUDLhbUc4My0ObBqFosc6AXkhkl4evpT0UxaU4ifR2X.', 'viewer', 'Public Services', NULL, NULL, NULL, 'active', '2025-11-21 17:03:16', '2025-11-22 05:38:30'),
(13, 'Johnrick', 'Johnrick5609@gmail.com', 'Johnrick5609', 'Johnrick', '$2y$10$0ZkztixnM/5ebw3cTcBo1ef8M.u6/i1Wk5kFxI3YBxXLXsjJ9dhv2', 'viewer', 'Mayor\'s Office', NULL, 'manager', NULL, 'active', '2026-02-07 15:54:03', '2026-02-08 06:24:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(10) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'Asia/Manila',
  `items_per_page` int(11) DEFAULT 20,
  `notifications_email` tinyint(1) DEFAULT 1,
  `notifications_browser` tinyint(1) DEFAULT 1,
  `notifications_documents` tinyint(1) DEFAULT 1,
  `notifications_system` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `theme`, `language`, `timezone`, `items_per_page`, `notifications_email`, `notifications_browser`, `notifications_documents`, `notifications_system`, `created_at`, `updated_at`) VALUES
(1, 1, 'light', 'en', 'Asia/Manila', 20, 1, 1, 1, 1, '2025-11-21 17:10:11', '2025-11-21 17:10:11'),
(2, 2, 'light', 'en', 'Asia/Manila', 20, 1, 1, 1, 1, '2025-11-21 17:10:11', '2025-11-21 17:10:11'),
(3, 3, 'light', 'en', 'Asia/Manila', 20, 1, 1, 1, 1, '2025-11-21 17:10:11', '2025-11-21 17:10:11'),
(4, 4, 'light', 'en', 'Asia/Manila', 20, 1, 1, 1, 1, '2025-11-21 17:10:11', '2025-11-21 18:36:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table_name` (`table_name`),
  ADD KEY `idx_record_id` (`record_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_api_key` (`api_key`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `document_access_logs`
--
ALTER TABLE `document_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_document` (`document_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_accessed` (`accessed_at`);

--
-- Indexes for table `document_links`
--
ALTER TABLE `document_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_document` (`document_id`),
  ADD KEY `idx_linked` (`linked_document_id`);

--
-- Indexes for table `document_tags`
--
ALTER TABLE `document_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `document_tag_relationships`
--
ALTER TABLE `document_tag_relationships`
  ADD PRIMARY KEY (`document_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_document` (`document_id`),
  ADD KEY `idx_version` (`version_number`);

--
-- Indexes for table `integrated_records`
--
ALTER TABLE `integrated_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `integration_api_keys`
--
ALTER TABLE `integration_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_api_key` (`api_key`),
  ADD UNIQUE KEY `unique_module_name` (`module_name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `legislative_documents`
--
ALTER TABLE `legislative_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `idx_reference` (`reference_number`),
  ADD KEY `idx_type` (`document_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`document_date`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_source` (`source_module`,`source_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`);
ALTER TABLE `legislative_documents` ADD FULLTEXT KEY `idx_search` (`title`,`description`,`tags`);
ALTER TABLE `legislative_documents` ADD FULLTEXT KEY `ft_search` (`title`,`description`,`tags`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_source_module` (`source_module`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_department` (`department`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=282;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_access_logs`
--
ALTER TABLE `document_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `document_links`
--
ALTER TABLE `document_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_tags`
--
ALTER TABLE `document_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `integrated_records`
--
ALTER TABLE `integrated_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `integration_api_keys`
--
ALTER TABLE `integration_api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `legislative_documents`
--
ALTER TABLE `legislative_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_access_logs`
--
ALTER TABLE `document_access_logs`
  ADD CONSTRAINT `document_access_logs_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legislative_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_access_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_links`
--
ALTER TABLE `document_links`
  ADD CONSTRAINT `document_links_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legislative_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_links_ibfk_2` FOREIGN KEY (`linked_document_id`) REFERENCES `legislative_documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_tag_relationships`
--
ALTER TABLE `document_tag_relationships`
  ADD CONSTRAINT `document_tag_relationships_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legislative_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_tag_relationships_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `document_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legislative_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `legislative_documents`
--
ALTER TABLE `legislative_documents`
  ADD CONSTRAINT `legislative_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
