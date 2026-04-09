-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 01:38 AM
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
-- Database: `webseclab`
--

-- --------------------------------------------------------

--
-- Table structure for table `secure_csrf_tokens`
--

CREATE TABLE `secure_csrf_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secure_notes`
--

CREATE TABLE `secure_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `secure_notes`
--

INSERT INTO `secure_notes` (`id`, `user_id`, `title`, `content`, `created_at`) VALUES
(3, 1, 'Bilješka', 'ovo nije test', '2026-03-25 02:08:10');

-- --------------------------------------------------------

--
-- Table structure for table `secure_sessions`
--

CREATE TABLE `secure_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secure_uploads`
--

CREATE TABLE `secure_uploads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `secure_uploads`
--

INSERT INTO `secure_uploads` (`id`, `user_id`, `original_name`, `stored_name`, `mime_type`, `file_size`, `uploaded_at`) VALUES
(1, 3, 'secure_app_test.png', 'bc83826103161ef3badefbb74624e372.png', 'image/png', 6783, '2026-04-08 13:13:27');

-- --------------------------------------------------------

--
-- Table structure for table `secure_users`
--

CREATE TABLE `secure_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `login_attempts` tinyint(3) UNSIGNED DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `secure_users`
--

INSERT INTO `secure_users` (`id`, `username`, `password_hash`, `email`, `login_attempts`, `locked_until`, `created_at`) VALUES
(1, 'Danijel', '$2y$12$bPtOsHHUg/nPyWPs3x737e/S6GTVBiUgfdkwucpSmbAawKbljeBpK', 'Danijeliglic5@gmail.com', 0, NULL, '2026-03-25 01:47:04'),
(3, 'Danijel_01', '$2y$12$E8RG0HnVIi0r4w8c2dFza.KJ2FygASW822Nc9/veToJ8TJiSOmTvO', 'Diglic00@fesb.hr', 0, NULL, '2026-04-08 13:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `vuln_notes`
--

CREATE TABLE `vuln_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vuln_sessions`
--

CREATE TABLE `vuln_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vuln_users`
--

CREATE TABLE `vuln_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vuln_users`
--

INSERT INTO `vuln_users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'danije', 'a', 'iglicdanijel5@gmail.com', '2026-03-25 01:11:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `secure_csrf_tokens`
--
ALTER TABLE `secure_csrf_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_csrf_user` (`user_id`);

--
-- Indexes for table `secure_notes`
--
ALTER TABLE `secure_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_secure_notes_user` (`user_id`);

--
-- Indexes for table `secure_sessions`
--
ALTER TABLE `secure_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_secure_sessions_user` (`user_id`),
  ADD KEY `idx_secure_sessions_token` (`token`);

--
-- Indexes for table `secure_uploads`
--
ALTER TABLE `secure_uploads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stored_name` (`stored_name`),
  ADD KEY `idx_uploads_user` (`user_id`);

--
-- Indexes for table `secure_users`
--
ALTER TABLE `secure_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_secure_users_username` (`username`),
  ADD KEY `idx_secure_users_email` (`email`);

--
-- Indexes for table `vuln_notes`
--
ALTER TABLE `vuln_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vuln_sessions`
--
ALTER TABLE `vuln_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vuln_users`
--
ALTER TABLE `vuln_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `secure_csrf_tokens`
--
ALTER TABLE `secure_csrf_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secure_notes`
--
ALTER TABLE `secure_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `secure_sessions`
--
ALTER TABLE `secure_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secure_uploads`
--
ALTER TABLE `secure_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `secure_users`
--
ALTER TABLE `secure_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vuln_notes`
--
ALTER TABLE `vuln_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vuln_sessions`
--
ALTER TABLE `vuln_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vuln_users`
--
ALTER TABLE `vuln_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `secure_csrf_tokens`
--
ALTER TABLE `secure_csrf_tokens`
  ADD CONSTRAINT `fk_csrf_user` FOREIGN KEY (`user_id`) REFERENCES `secure_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secure_notes`
--
ALTER TABLE `secure_notes`
  ADD CONSTRAINT `fk_secure_notes_user` FOREIGN KEY (`user_id`) REFERENCES `secure_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secure_sessions`
--
ALTER TABLE `secure_sessions`
  ADD CONSTRAINT `fk_secure_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `secure_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secure_uploads`
--
ALTER TABLE `secure_uploads`
  ADD CONSTRAINT `fk_upload_user` FOREIGN KEY (`user_id`) REFERENCES `secure_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
