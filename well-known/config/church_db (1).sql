-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 04:28 PM
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
-- Database: `church_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `carousel_images`
--

CREATE TABLE `carousel_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel_images`
--

INSERT INTO `carousel_images` (`id`, `image_path`, `title`, `description`, `display_order`, `active`, `created_at`) VALUES
(1, 'assets/images/image1.jpg', 'Welcome to Our Church', 'Join us in worship, community, and service', 1, 1, '2025-03-21 02:12:27'),
(2, 'assets/images/image2.jpg', 'Growing Together in Faith', 'Experience the love and fellowship of Christ', 2, 1, '2025-03-21 02:12:27'),
(3, 'assets/images/image3.jpg', 'Making a Difference', 'Serving our community with compassion and grace', 3, 1, '2025-03-21 02:12:27');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `active` tinyint(10) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if event is featured, 0 otherwise',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date`, `time`, `location`, `max_participants`, `is_featured`, `created_at`) VALUES
(1, 'Wedding Ceremony', 'Juliet wedding and prewedding', '2025-03-25', '10:00:00', 'bondeni nakuru', NULL, 0, '2025-03-25 16:54:35'),
(3, 'Baptism', 'both children and adult are welcome to join baptism class before they are baptised', '2025-04-07', '09:00:00', 'bondeni', NULL, 0, '2025-03-25 19:08:17'),
(4, '7yij', 'jbhvj', '2025-04-14', '07:06:00', 'bvnmbv', NULL, 0, '2025-04-01 06:09:34'),
(5, 'gbvn', 'vbnv', '7567-06-05', '04:53:00', '54', NULL, 0, '2025-04-04 12:11:33'),
(6, 'graduiation ceremony', 'john, bishop&#039;s son graduation ceremony will be held at the aic garden, all are welcome.', '2026-05-14', '11:00:00', 'aic bondeni nakuru', NULL, 1, '2025-04-18 12:55:10'),
(7, 'elder Milka ordination', 'elder Milka ordination elder Milka ordination elder Milka ordination elder Milka ordination', '2025-08-17', '08:00:00', 'aic bonden sanctuary', NULL, 0, '2025-04-18 12:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `family_join_requests`
--

CREATE TABLE `family_join_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_join_requests`
--

INSERT INTO `family_join_requests` (`id`, `name`, `email`, `phone`, `address`, `message`, `status`, `created_at`) VALUES
(1, 'joseph kadasia menja', 'test1@gmail.com', '682469346', NULL, 'every thing is possible', 'approved', '2025-03-26 14:01:49'),
(2, 'kamau', 'kamaujohn@gmail.com', '0987654321', NULL, 'y7ul;j.', 'approved', '2025-03-23 14:21:57'),
(3, 'dd', 'sds@gmail.com', 'sadsa', NULL, 'hello', 'pending', '2025-04-04 14:31:38'),
(4, 'peter', 'peter@gmail.com', '1234567890', NULL, 'rr', 'approved', '2025-04-17 19:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `created_at`) VALUES
(1, 'friday tours', 'aic youths visit fort jesus', 'assets/images/gallery/67e972e3d974a_0.png', '2025-03-30 16:35:47'),
(2, 'aic choir', 'our choir releases new song of worship', 'assets/images/gallery/67e97312d68d5_0.png', '2025-03-30 16:36:34');

-- --------------------------------------------------------

--
-- Table structure for table `ministries`
--

CREATE TABLE `ministries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `leader` varchar(100) DEFAULT NULL,
  `meeting_time` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ministries`
--

INSERT INTO `ministries` (`id`, `name`, `description`, `leader`, `meeting_time`, `location`, `image_url`, `is_active`, `created_at`) VALUES
(18, 'christian education deopartment', 'learning the way of christ', 'joseph', '4pm', 'aic sanctuary', 'assets/images/ministries/68025ddac1bc2.jpg', 1, '2025-04-18 14:12:42'),
(19, 'youth fellowship', 'empowering the grouwth of young people spiritually , mentally and physically to be part of a firm ministry.', '', '', '', 'assets/images/ministries/6802600c43060.png', 1, '2025-04-18 14:22:04'),
(20, 'aic choir', '', '', '', '', 'assets/images/ministries/6802602884816.png', 1, '2025-04-18 14:22:32'),
(21, 'women council', '', '', '', '', 'assets/images/ministries/68026107bcf01.png', 1, '2025-04-18 14:26:15'),
(22, 'leaders council', '', '', '', '', 'assets/images/ministries/6802613d39745.jpg', 1, '2025-04-18 14:27:09'),
(23, 'men fellowship', '', '', '', '', 'assets/images/ministries/68026153d1c08.jpg', 1, '2025-04-18 14:27:31');

-- --------------------------------------------------------

--
-- Table structure for table `ministry_members`
--

CREATE TABLE `ministry_members` (
  `id` int(11) NOT NULL,
  `ministry_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('member','volunteer','leader') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_attempts`
--

CREATE TABLE `password_reset_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_attempts`
--

INSERT INTO `password_reset_attempts` (`id`, `user_id`, `ip_address`, `attempted_at`) VALUES
(1, 4, '::1', '2025-03-26 14:05:22');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_participants`
--

CREATE TABLE `prayer_participants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `prayer_request_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `request_text` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_requests`
--

INSERT INTO `prayer_requests` (`id`, `user_id`, `request_text`, `is_private`, `created_at`) VALUES
(1, 1, 'Prayer request 1', 0, '2025-03-18 03:00:51'),
(2, 6, 'am sick need prayer', 0, '2025-03-26 15:44:47'),
(3, 6, 'i sinned', 1, '2025-03-26 15:45:07'),
(4, 6, 'am sick need prayer', 0, '2025-03-26 15:45:13'),
(5, 6, 'i have marriage issues', 1, '2025-03-26 15:46:17'),
(6, 9, 'prayer of thanks, am graduating next week', 0, '2025-03-29 15:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_responses`
--

CREATE TABLE `prayer_responses` (
  `id` int(11) NOT NULL,
  `prayer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_responses`
--

INSERT INTO `prayer_responses` (`id`, `prayer_id`, `user_id`, `created_at`) VALUES
(1, 4, 9, '2025-03-29 15:17:46'),
(2, 2, 9, '2025-03-29 15:17:48'),
(3, 1, 9, '2025-03-29 15:17:49'),
(4, 6, 9, '2025-03-29 15:19:14');

-- --------------------------------------------------------

--
-- Table structure for table `sermons`
--

CREATE TABLE `sermons` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `speaker` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scripture_reference` varchar(255) DEFAULT NULL,
  `series` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sermons`
--

INSERT INTO `sermons` (`id`, `title`, `description`, `video_url`, `speaker`, `date`, `youtube_url`, `created_at`, `scripture_reference`, `series`, `updated_at`) VALUES
(1, 'Finding Peace in Troubled Times', 'A message of hope and comfort', NULL, 'Pastor John Smith', '2025-03-19', 'https://youtu.be/zXEy-nCc1YY', '2025-03-21 02:30:31', NULL, NULL, '2025-03-23 17:19:48'),
(2, 'The Power of Community', 'Understanding the importance of fellowship', NULL, 'Pastor Sarah Johnson', '2025-03-16', 'https://youtube.com/watch?v=sample2', '2025-03-21 02:30:31', NULL, NULL, '2025-03-23 17:19:41'),
(3, 'Walking in Faith', 'Strengthening your spiritual journey', NULL, 'Pastor John Smith', '2025-03-12', 'https://youtube.com/watch?v=sample3', '2025-03-21 02:30:31', NULL, NULL, '2025-03-23 17:19:41'),
(4, 'Living with Purpose', 'Discovering God\'s plan for your life', NULL, 'Pastor Michael Brown', '2025-03-09', 'https://youtube.com/watch?v=sample4', '2025-03-21 02:30:31', NULL, NULL, '2025-03-23 17:19:41'),
(5, 'The Heart of Worship', 'Understanding true worship', NULL, 'Pastor Sarah Johnson', '2025-03-05', 'https://youtube.com/watch?v=sample5', '2025-03-21 02:30:31', NULL, NULL, '2025-03-23 17:19:41');

-- --------------------------------------------------------

--
-- Table structure for table `sermons_media`
--

CREATE TABLE `sermons_media` (
  `id` int(11) NOT NULL,
  `sermon_id` int(11) NOT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `notes_file` varchar(255) DEFAULT NULL,
  `document_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'leader_access_gallery', '1', '2025-04-17 11:58:18', '2025-04-18 12:07:12'),
(2, 'leader_access_manage_events', '1', '2025-04-17 11:58:18', '2025-04-17 15:40:51'),
(3, 'leader_access_manage_join_requests', '1', '2025-04-17 11:58:18', '2025-04-18 01:32:33'),
(4, 'leader_access_manage_ministries', '1', '2025-04-17 11:58:18', '2025-04-18 11:11:33'),
(5, 'leader_access_manage_prayers', '1', '2025-04-17 11:58:18', '2025-04-18 01:32:33'),
(6, 'leader_access_manage_sermons', '1', '2025-04-17 11:58:18', '2025-04-18 01:32:33'),
(7, 'leader_access_index', '1', '2025-04-17 11:58:18', '2025-04-17 15:58:36'),
(8, 'require_user_approval', '1', '2025-04-17 11:58:59', '2025-04-18 11:26:02'),
(168, 'leader_access_profile', '0', '2025-04-17 22:39:31', '2025-04-18 00:13:54'),
(187, 'leader_access_settings', '0', '2025-04-17 23:18:56', '2025-04-17 23:21:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_active` tinyint(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `role` enum('guest','member','volunteer','church_leader','admin') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','active','rejected') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `is_active`, `password`, `reset_token`, `reset_expires`, `role`, `created_at`, `status`) VALUES
(1, 'alice', '123in@church.com', 1, '$2y$10$H.osH.fKg52NVE69Oe65I.QZTlL1KUoQ6COTsWIlXR6hv7gtTCGvK', NULL, NULL, 'member', '2025-03-18 02:59:07', 'active'),
(4, 'aic church', 'admin@church.com', 1, '$2y$10$EKp6YjzP2fxxczX0KjfhBeQDqulZQa4fgJhm4kKY1eK6i7VNETOdW', '7ce7dc0fed025248cad6ed52170a3187bba4f9b350e457d1d302942518d05040', '2025-03-26 16:05:21', 'admin', '2025-03-25 20:05:22', 'active'),
(6, 'Timothy', 'kkkk@gmail.com', 1, '$2y$10$jTFyGeh7.UEW0Idyo1EtIu5YbRR/4/.LyeZlLZxvIC/XReY6FWstS', NULL, NULL, 'member', '2025-03-26 15:43:47', 'active'),
(7, 'peter', 'peter@gmail.com', 1, '$2y$10$eX4WxbMHdLK0bYG6YbQybu4B5lBKKvpajtcMVZB6RDO33mtWLrNNe', NULL, NULL, 'church_leader', '2025-03-28 17:02:09', 'active'),
(8, 'sese', 'ssss@gmail.com', 1, '$2y$10$PD2A6vUY8t13dj.0eXPO0OObdpyNHcOfLtJrdYIPAjZyLQBaNXSJ2', NULL, NULL, 'member', '2025-03-29 12:44:52', 'active'),
(9, 'gimmy', 'grace@gmail.com', 1, '$2y$10$qoAuL65pt6CmnLmtzTbDJuXuWm0SPFaWREXEePSBIsm6f2WWvQEHS', NULL, NULL, 'church_leader', '2025-03-29 15:13:29', 'active'),
(10, 'ESTHER', 'E@gmail.com', 1, '$2y$10$d4z9e1pKUjJ2ShA5xSIFbe/0FZEHDlmAZPe055a7.6N1KvaZawWQm', NULL, NULL, 'member', '2025-04-18 11:21:34', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carousel_images`
--
ALTER TABLE `carousel_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `family_join_requests`
--
ALTER TABLE `family_join_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ministries`
--
ALTER TABLE `ministries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ministry_members`
--
ALTER TABLE `ministry_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ministry_id` (`ministry_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prayer_participants`
--
ALTER TABLE `prayer_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prayer` (`user_id`,`prayer_request_id`),
  ADD KEY `prayer_request_id` (`prayer_request_id`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prayer_responses`
--
ALTER TABLE `prayer_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prayer_id` (`prayer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sermons_media`
--
ALTER TABLE `sermons_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sermon_id` (`sermon_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carousel_images`
--
ALTER TABLE `carousel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `family_join_requests`
--
ALTER TABLE `family_join_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ministries`
--
ALTER TABLE `ministries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ministry_members`
--
ALTER TABLE `ministry_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prayer_participants`
--
ALTER TABLE `prayer_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prayer_responses`
--
ALTER TABLE `prayer_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sermons_media`
--
ALTER TABLE `sermons_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ministry_members`
--
ALTER TABLE `ministry_members`
  ADD CONSTRAINT `ministry_members_ibfk_1` FOREIGN KEY (`ministry_id`) REFERENCES `ministries` (`id`),
  ADD CONSTRAINT `ministry_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  ADD CONSTRAINT `password_reset_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayer_participants`
--
ALTER TABLE `prayer_participants`
  ADD CONSTRAINT `prayer_participants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `prayer_participants_ibfk_2` FOREIGN KEY (`prayer_request_id`) REFERENCES `prayer_requests` (`id`);

--
-- Constraints for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD CONSTRAINT `prayer_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prayer_responses`
--
ALTER TABLE `prayer_responses`
  ADD CONSTRAINT `prayer_responses_ibfk_1` FOREIGN KEY (`prayer_id`) REFERENCES `prayer_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prayer_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
