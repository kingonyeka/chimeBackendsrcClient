-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2024 at 12:19 AM
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
-- Database: `chime_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `status` enum('inactive','active','banned','deactivated') NOT NULL DEFAULT 'inactive',
  `joined_telegram` tinyint(4) NOT NULL DEFAULT 0,
  `verified_email` tinyint(4) NOT NULL DEFAULT 0,
  `last_logged_in` datetime DEFAULT NULL,
  `role_name` enum('super-admin','admin') NOT NULL DEFAULT 'admin',
  `created_by` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_last_modified` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `address`, `user_id`, `status`, `joined_telegram`, `verified_email`, `last_logged_in`, `role_name`, `created_by`, `created_at`, `date_last_modified`) VALUES
(1, 'Shifu-Nfor', 'Rhagninyui', '', 'nforshifu234.dev@gmail.com', '$2y$10$6dq/sZYTLip1snvtG8qgtOSihf/OfJ5OAVIhZs2blhiwuVpFOndQu', 'Dubai, UAE', 'suf9e8oib04p902-3oo6ybt9-utihk', 'active', 1, 1, NULL, 'super-admin', NULL, '2024-05-31 02:12:25', '2024-06-22 23:11:47'),
(2, '', '', '', 'shifubel@gmail.com', '$2y$10$1P9eZDBbna41CMWAreCSo.91KiniFgOCsuOyRDV7A8QeuTLseguxm', '', 'pcAUzmbsQcSEDKUKI2Cf', 'active', 0, 1, NULL, 'admin', 'suf9e8oib04p902-3oo6ybt9-utihk', '2024-05-31 02:12:50', '2024-05-31 02:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(30) DEFAULT NULL,
  `access_token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_refreshed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_sessions`
--

INSERT INTO `admin_sessions` (`id`, `user_id`, `access_token`, `refresh_token`, `expires_at`, `last_refreshed_at`, `created_at`, `updated_at`) VALUES
(5, 'suf9e8oib04p902-3oo6ybt9-utihk', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJJZCI6InN1ZjllOG9pYjA0cDkwMi0zb282eWJ0OS11dGloayIsImVtYWlsIjoibmZvcnNoaWZ1MjM0LmRldkBnbWFpbC5jb20ifSwiaWF0IjoxNzE3MTIwNzI4LCJleHAiOjE3MTcxMjQzMjh9.Y9db7xyaH0B4t7Dcaaa0UXS3OcZq8aIU09fzYk9V6bo', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiJzdWY5ZThvaWIwNHA5MDItM29vNnlidDktdXRpaGsiLCJpYXQiOjE3MTcxMjA3MjgsImV4cCI6MTczMDA4MDcyOH0.gSHYDRJIYSeL5I7FrPPhgtqjd6ks08xilaUJZzGLEL4', '2024-10-28 02:58:48', '2024-05-31 02:58:48', '2024-05-31 02:51:30', '2024-05-31 02:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`products`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `products`) VALUES
(1, 'pcAUzmbsQcSEDKUKI2Cf', '[{\"slug\":\"test-robot\",\"quantities\":1,\"type\":\"robot\",\"title\":\"Test Robot\",\"author\":\"pcAUzmbsQcSEDKUKI2Cf\",\"price\":12345}]');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cat_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`, `description`, `cat_id`, `created_at`, `last_updated_at`) VALUES
(1, 'robots', '', 'qZG7eeSngNIHXzqMjGPn', '2024-05-31 01:09:58', '2024-05-31 02:09:58'),
(2, 'courses', '', 'VWhKgpYfdaFz6r2NoJzu', '2024-06-01 06:46:02', '2024-06-01 07:46:02');

-- --------------------------------------------------------

--
-- Table structure for table `categories_type`
--

CREATE TABLE `categories_type` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `cat_id` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories_type`
--

INSERT INTO `categories_type` (`id`, `title`, `description`, `type_id`, `cat_id`, `created_at`, `updated_at`) VALUES
(1, 'forex trading', '', 'vwrher87698mt9097r5enbsvacr35tvs4d6bf75', 'VWhKgpYfdaFz6r2NoJzu', '2024-06-13 15:58:54', '2024-06-13 15:58:54'),
(2, 'buisness class', '', 'dtunjd8nofiymtngiponiftr5se', 'qZG7eeSngNIHXzqMjGPn', '2024-06-17 12:26:58', '2024-06-17 12:26:58');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `usd` double NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `video` varchar(255) NOT NULL,
  `cat_id` varchar(50) NOT NULL,
  `type_id` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `course_videos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`course_videos`)),
  `quiz_videos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`quiz_videos`)),
  `live_session_videos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`live_session_videos`)),
  `course_id` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(2, 'nforshifu.234@gmail.com', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImV4cGlyZXNBdCI6IjIwMjQtMDYtMTMgMTA6MjY6NDEifSwiaWF0IjoxNzE4MjY5OTAxLCJleHAiOjE3MTgyNzA4MDF9.lrgdLI6hhOs9lXP_5gYV4mtSyT9kgRbV-NI7E9A-l9Y', '2024-06-13 10:26:41'),
(3, 'nforshifu.234@gmail.com', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImV4cGlyZXNBdCI6IjIwMjQtMDYtMTMgMTA6MzM6NTUifSwiaWF0IjoxNzE4MjcwMzM1LCJleHAiOjE3MTgyNzEyMzV9.lGfcXfkzOPWmo5PVa0131yT0PZSCW0zDq7Vh2fyfq5I', '2024-06-13 10:33:55'),
(4, 'nforshifu.234@gmail.com', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImV4cGlyZXNBdCI6IjIwMjQtMDYtMTMgMTA6MzU6MjYifSwiaWF0IjoxNzE4MjcwNDI2LCJleHAiOjE3MTgyNzEzMjZ9.LOyssnWLRovEK0Av9IglUvvSa2EY7xUUwYeSJQL1UX8', '2024-06-13 10:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) DEFAULT NULL,
  `admin_id` varchar(25) DEFAULT NULL,
  `payment_id` varchar(25) NOT NULL,
  `amount` double NOT NULL,
  `currency` varchar(255) NOT NULL,
  `paid_at` datetime NOT NULL,
  `payment_provider` enum('paystack','flutterwave','stripe') NOT NULL,
  `payment_channel` varchar(255) NOT NULL,
  `authorization_code` varchar(255) NOT NULL,
  `card_type` text NOT NULL,
  `bank` varchar(255) NOT NULL,
  `card_last4` int(4) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `admin_id`, `payment_id`, `amount`, `currency`, `paid_at`, `payment_provider`, `payment_channel`, `authorization_code`, `card_type`, `bank`, `card_last4`, `created_at`) VALUES
(1, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'kabq3pz2km', 1603238, 'NGN', '2024-06-01 09:37:13', 'paystack', 'card', 'AUTH_ib1l3is7oj', 'visa ', 'TEST BANK', 4081, '2024-06-01 09:35:43'),
(2, 'pcAUzmbsQcSEDKUKI2Cf', NULL, '9ql60vzz4l', 1603238, 'NGN', '2024-06-01 09:41:53', 'paystack', 'bank', 'AUTH_896p5voowe', '', 'Zenith Bank', 0, '2024-06-01 09:41:20'),
(3, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'psyt7tbwbr', 1603238, 'NGN', '2024-06-01 10:00:59', 'paystack', 'card', 'AUTH_sp6dv75nhn', 'visa ', 'TEST BANK', 409, '2024-06-01 09:57:20'),
(4, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'wtnfnvn5nt', 1603238, 'NGN', '2024-06-01 10:02:27', 'paystack', 'card', 'AUTH_prtbyt3drt', 'visa ', 'TEST BANK', 4081, '2024-06-01 10:02:18'),
(5, 'pcAUzmbsQcSEDKUKI2Cf', NULL, '3gj556mbfp', 1603238, 'NGN', '2024-06-01 10:05:39', 'paystack', 'card', 'AUTH_lz61yefeto', 'visa ', 'TEST BANK', 4081, '2024-06-01 10:05:32'),
(6, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'yuyk36sl8c', 1603238, 'NGN', '2024-06-01 10:06:45', 'paystack', 'card', 'AUTH_8zhjlzmveh', 'visa ', 'TEST BANK', 4081, '2024-06-01 10:06:40'),
(7, 'pcAUzmbsQcSEDKUKI2Cf', NULL, '2r9ewtuezy', 1603238, 'NGN', '2024-06-01 10:10:50', 'paystack', 'card', 'AUTH_gnao3hw5r1', 'verve ', 'TEST BANK', 7804, '2024-06-01 10:08:35'),
(8, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'xhwxc4qkru', 14084876, 'NGN', '2024-06-01 18:27:50', 'paystack', 'card', 'AUTH_erymzrgj38', 'verve ', 'TEST BANK', 6666, '2024-06-01 18:23:26'),
(9, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'im7e9oe8j3', 1281628, 'NGN', '2024-06-01 18:37:20', 'paystack', 'card', 'AUTH_0kam49soze', 'visa ', 'TEST BANK', 4081, '2024-06-01 18:32:52'),
(11, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'mc74cboamq', 1281628, 'NGN', '2024-06-01 18:38:44', 'paystack', 'card', 'AUTH_6n8r7f8t2e', 'visa ', 'TEST BANK', 4081, '2024-06-01 18:38:18'),
(12, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'ffjbj6mm1c', 1283257, 'NGN', '2024-06-05 21:07:06', 'paystack', 'card', 'AUTH_sw57lmo9ut', 'visa ', 'TEST BANK', 4081, '2024-06-05 21:06:57'),
(13, 'FMQE12cXrc7IYWwHmAQr', NULL, 'xdjbtryj5t', 1283257, 'NGN', '2024-06-13 09:10:08', 'paystack', 'card', 'AUTH_k0plrxeh12', 'visa ', 'TEST BANK', 4081, '2024-06-13 09:09:54'),
(14, 'FMQE12cXrc7IYWwHmAQr', NULL, 't0g33oo2jn', 3703500, 'NGN', '2024-06-21 13:30:13', 'paystack', 'card', 'AUTH_eg0xvi9rpd', 'visa ', 'TEST BANK', 4081, '2024-06-21 13:30:03'),
(16, 'FMQE12cXrc7IYWwHmAQr', NULL, '48es8456e1', 3703500, 'NGN', '2024-06-21 13:34:06', 'paystack', 'card', 'AUTH_qbwt5jxugl', 'visa ', 'TEST BANK', 4081, '2024-06-21 13:33:56'),
(18, 'FMQE12cXrc7IYWwHmAQr', NULL, 'zr8cgbco7n', 3703500, 'NGN', '2024-06-21 13:35:43', 'paystack', 'card', 'AUTH_whw68q2ej8', 'visa ', 'TEST BANK', 4081, '2024-06-21 13:35:37'),
(19, 'FMQE12cXrc7IYWwHmAQr', NULL, 'FW|PHP_6676318cdd480', 24690, 'NGN', '2024-06-22 02:06:39', 'flutterwave', 'bank_transfer', '5845976', '', 'Access Bank', 0, '2024-06-22 02:06:39'),
(21, 'FMQE12cXrc7IYWwHmAQr', NULL, 'FW|PHP_667636b003cc5', 24690, 'NGN', '2024-06-22 02:29:26', 'flutterwave', 'card', '5845997', 'MASTERCARD', 'MASHREQ BANK CREDITSTANDARD', 229, '2024-06-22 02:29:26'),
(22, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_667639dda63a6', 12345, 'NGN', '2024-06-22 02:41:54', 'flutterwave', 'ussd', '5845999', '', '', 0, '2024-06-22 02:41:54'),
(23, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_66763a9800906', 24690, 'NGN', '2024-06-22 02:44:54', 'flutterwave', 'account', '5846000', '', '', 0, '2024-06-22 02:44:54'),
(24, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_66763c7c190ca', 24690, 'NGN', '2024-06-22 02:53:02', 'flutterwave', 'bank_transfer', '5846001', '', 'Access Bank', 0, '2024-06-22 02:53:02'),
(25, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_66763ea78e76d', 24690, 'NGN', '2024-06-22 03:02:21', 'flutterwave', 'bank_transfer', '5846011', '', 'Access Bank', 0, '2024-06-22 03:02:21'),
(26, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'T399262368375354', 100, 'NGN', '2024-06-23 00:29:07', 'paystack', 'card', 'AUTH_0weyk80b2c', 'visa ', 'TEST BANK', 4081, '2024-06-23 00:29:03'),
(27, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_66783d6aa5594', 12468, 'NGN', '2024-06-23 15:21:30', 'flutterwave', 'account', '5848297', '', '', 0, '2024-06-23 15:21:30'),
(28, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_6678406ea957e', 12345, 'NGN', '2024-06-23 15:34:20', 'flutterwave', 'account', '5848310', '', '', 0, '2024-06-23 15:34:20'),
(31, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_667841b863898', 12345, 'NGN', '2024-06-23 15:39:58', 'flutterwave', 'bank_transfer', '5848323', '', 'Access Bank', 0, '2024-06-23 15:39:58'),
(33, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_6678420a53e7b', 12345, 'NGN', '2024-06-23 15:41:18', 'flutterwave', 'bank_transfer', '5848328', '', 'Access Bank', 0, '2024-06-23 15:41:18'),
(35, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_667842c65bb74', 12345, 'NGN', '2024-06-23 15:44:27', 'flutterwave', 'bank_transfer', '5848331', '', 'Access Bank', 0, '2024-06-23 15:44:27'),
(36, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_66784337cc2de', 12345, 'NGN', '2024-06-23 15:46:19', 'flutterwave', 'bank_transfer', '5848335', '', 'Access Bank', 0, '2024-06-23 15:46:19'),
(37, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_667843b55c418', 12345, 'NGN', '2024-06-23 15:48:29', 'flutterwave', 'bank_transfer', '5848338', '', 'Access Bank', 0, '2024-06-23 15:48:29'),
(38, 'pcAUzmbsQcSEDKUKI2Cf', NULL, 'FW|PHP_667844aa2b00a', 12345, 'NGN', '2024-06-23 15:52:34', 'flutterwave', 'bank_transfer', '5848351', '', 'Access Bank', 0, '2024-06-23 15:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `robots`
--

CREATE TABLE `robots` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `usd` double NOT NULL,
  `author` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `zip` varchar(255) NOT NULL,
  `cat_id` varchar(25) NOT NULL,
  `type_id` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `robot_id` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `robots`
--

INSERT INTO `robots` (`id`, `title`, `slug`, `price`, `usd`, `author`, `description`, `zip`, `cat_id`, `type_id`, `image`, `robot_id`, `created_at`, `last_updated_at`) VALUES
(1, 'Test Robot', 'test-robot', 12345, 0, 'pcAUzmbsQcSEDKUKI2Cf', 'Test description', '/robots/zip_668071cf013bb1.13079719.zip', 'qZG7eeSngNIHXzqMjGPn', 'dtunjd8nofiymtngiponiftr5se', '/img/img_668071cf012343.75024767.png', 'D8D3GMlEz2KyqdMnZ4RM', '2024-06-29 21:42:55', '2024-06-29 21:42:55');

-- --------------------------------------------------------

--
-- Table structure for table `telegram`
--

CREATE TABLE `telegram` (
  `id` int(11) NOT NULL,
  `price` double NOT NULL,
  `usd` double NOT NULL,
  `telegram_id` varchar(255) NOT NULL DEFAULT 'telegram_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telegram`
--

INSERT INTO `telegram` (`id`, `price`, `usd`, `telegram_id`, `created_at`, `updated_at`) VALUES
(1, 1000, 20.25, 'telegram_id', '2024-07-06 21:56:37', '2024-07-06 21:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `status` enum('inactive','active','banned','deactivated') NOT NULL DEFAULT 'inactive',
  `courses_purchased` int(11) NOT NULL,
  `robots_purchased` int(11) NOT NULL,
  `joined_telegram` tinyint(4) NOT NULL DEFAULT 0,
  `last_logged_in` datetime DEFAULT NULL,
  `verified_email` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_last_modified` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `address`, `user_id`, `status`, `courses_purchased`, `robots_purchased`, `joined_telegram`, `last_logged_in`, `verified_email`, `created_at`, `date_last_modified`) VALUES
(1, 'Shifu-Nfor', 'Nyuiring-yoh Rhagninyui', 'Miracle', 'nforshifu.234@gmail.com', '$2y$10$ApAXkOuotNAGVc8KOeAjv.JkwVaeHrkq.JJQGtL6W0chprfkRiY1C', '123 Street, City', 'FMQE12cXrc7IYWwHmAQr', 'active', 3, 2, 0, '2024-06-16 13:15:25', 1, '2024-05-31 00:49:25', '2024-06-22 02:29:32'),
(2, '', '', '', 'shifubel@gmail.com', '$2y$10$1P9eZDBbna41CMWAreCSo.91KiniFgOCsuOyRDV7A8QeuTLseguxm', '', 'pcAUzmbsQcSEDKUKI2Cf', 'inactive', 3, 2, 1, '2024-06-22 03:55:36', 0, '2024-05-31 02:01:23', '2024-06-23 15:52:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_courses`
--

CREATE TABLE `user_courses` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`courses`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_payment`
--

CREATE TABLE `user_payment` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `payment_id` varchar(25) NOT NULL,
  `type` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_robots`
--

CREATE TABLE `user_robots` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `robots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`robots`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_refreshed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `access_token`, `refresh_token`, `expires_at`, `last_refreshed_at`, `created_at`, `updated_at`) VALUES
(1, 'FMQE12cXrc7IYWwHmAQr', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJJZCI6IkZNUUUxMmNYcmM3SVlXd0htQVFyIn0sImlhdCI6MTcxODU0MDEyNCwiZXhwIjoxNzM0MzIwMjIwfQ.5HVpwjs4JbIDRjMlXG6YJD4Wtpv5Tjn9HqcebZyX8H8', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiJGTVFFMTJjWHJjN0lZV3dIbUFRciIsImlhdCI6MTcxODU0MDEyNCwiZXhwIjoxNzMxNTAwMTI0fQ.X0Ird8ATPTQKvUNYjIIHc41jaLBhmrYbku4A8PjaeUU', '2024-06-16 14:15:24', '2024-06-16 13:15:24', '2024-06-13 10:37:40', '2024-06-16 13:15:24'),
(3, 'pcAUzmbsQcSEDKUKI2Cf', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJJZCI6InBjQVV6bWJzUWNTRURLVUtJMkNmIn0sImlhdCI6MTcxOTAyNDkzNiwiZXhwIjoxNzM0ODA1MDMyfQ.KjmWKxElmkvnFx0MuScQwyLtvcl7lzGPp_lmFYnA8tU', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VySWQiOiJwY0FVem1ic1FjU0VES1VLSTJDZiIsImlhdCI6MTcxOTAyNDkzNiwiZXhwIjoxNzMxOTg0OTM2fQ.4Kc5MdWZmXxkE8lkFbE9XlhryBJI8G5DNltQLZ3qQG0', '2024-06-22 04:55:36', '2024-06-22 03:55:36', '2024-06-22 03:55:36', '2024-06-22 03:55:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cat_id` (`cat_id`);

--
-- Indexes for table `categories_type`
--
ALTER TABLE `categories_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_id` (`type_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author` (`author`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_id` (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `robots`
--
ALTER TABLE `robots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `author` (`author`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `telegram`
--
ALTER TABLE `telegram`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_payment`
--
ALTER TABLE `user_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `user_robots`
--
ALTER TABLE `user_robots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories_type`
--
ALTER TABLE `categories_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `robots`
--
ALTER TABLE `robots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `telegram`
--
ALTER TABLE `telegram`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_payment`
--
ALTER TABLE `user_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_robots`
--
ALTER TABLE `user_robots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`user_id`);

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admins` (`user_id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `categories_type`
--
ALTER TABLE `categories_type`
  ADD CONSTRAINT `categories_type_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`author`) REFERENCES `admins` (`user_id`),
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`),
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `categories_type` (`type_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`user_id`);

--
-- Constraints for table `robots`
--
ALTER TABLE `robots`
  ADD CONSTRAINT `robots_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`),
  ADD CONSTRAINT `robots_ibfk_2` FOREIGN KEY (`author`) REFERENCES `admins` (`user_id`),
  ADD CONSTRAINT `robots_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `categories_type` (`type_id`);

--
-- Constraints for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD CONSTRAINT `user_courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_payment`
--
ALTER TABLE `user_payment`
  ADD CONSTRAINT `user_payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `user_payment_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`);

--
-- Constraints for table `user_robots`
--
ALTER TABLE `user_robots`
  ADD CONSTRAINT `user_robots_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
