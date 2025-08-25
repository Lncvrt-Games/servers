-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 25, 2025 at 07:02 AM
-- Server version: 11.8.3-MariaDB-ubu2404
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `berrydashdatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatroom_reports`
--

CREATE TABLE `chatroom_reports` (
  `id` int(11) NOT NULL,
  `chatId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `reason` text NOT NULL,
  `timestamp` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `content` text NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `deleted_at` bigint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Table structure for table `launcherversions`
--

CREATE TABLE `launcherversions` (
  `id` int(11) NOT NULL,
  `version` text NOT NULL,
  `displayName` text DEFAULT NULL,
  `releaseDate` bigint(20) NOT NULL,
  `downloadUrls` text NOT NULL,
  `platforms` text NOT NULL,
  `executables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Table structure for table `marketplaceicons`
--

CREATE TABLE `marketplaceicons` (
  `id` int(11) NOT NULL,
  `uuid` text DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `data` text NOT NULL,
  `hash` text NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 0,
  `price` int(11) NOT NULL DEFAULT 0,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `token` varchar(512) NOT NULL,
  `latest_ip` varchar(255) DEFAULT NULL,
  `register_time` int(11) DEFAULT NULL,
  `highScore` bigint(20) NOT NULL DEFAULT 0,
  `totalNormalBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalPoisonBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalSlowBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalUltraBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalSpeedyBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalCoinBerries` bigint(20) NOT NULL DEFAULT 0,
  `totalAttempts` bigint(20) NOT NULL DEFAULT 0,
  `icon` int(11) NOT NULL DEFAULT 1,
  `overlay` int(11) NOT NULL DEFAULT 0,
  `banned` tinyint(1) NOT NULL DEFAULT 0,
  `leaderboardsBanned` tinyint(1) NOT NULL DEFAULT 0,
  `birdColor` text NOT NULL DEFAULT '[255,255,255]',
  `overlayColor` text NOT NULL DEFAULT '[255,255,255]',
  `marketplaceData` text NOT NULL DEFAULT '{}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPRESSED;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatroom_reports`
--
ALTER TABLE `chatroom_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `chatId` (`chatId`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userId` (`userId`);

--
-- Indexes for table `launcherversions`
--
ALTER TABLE `launcherversions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketplaceicons`
--
ALTER TABLE `marketplaceicons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userId` (`userId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatroom_reports`
--
ALTER TABLE `chatroom_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `launcherversions`
--
ALTER TABLE `launcherversions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketplaceicons`
--
ALTER TABLE `marketplaceicons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chatroom_reports`
--
ALTER TABLE `chatroom_reports`
  ADD CONSTRAINT `chatId` FOREIGN KEY (`chatId`) REFERENCES `chats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `userId` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `fk_userId` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `marketplaceicons`
--
ALTER TABLE `marketplaceicons`
  ADD CONSTRAINT `fk_marketplaceicons_userId` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
