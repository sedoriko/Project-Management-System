-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 05:04 PM
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
-- Database: `project_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `project_list`
--

CREATE TABLE `project_list` (
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `manager_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_list`
--

INSERT INTO `project_list` (`project_id`, `name`, `description`, `status`, `start_date`, `end_date`, `manager_id`, `date_created`) VALUES
(1, 'UNANG PROYEKTO', 'TERMINAL ASSESSMENT 1', 'In Progress', '2025-05-08', '2025-06-08', 5, '2025-05-08 09:55:56'),
(2, '2nd Project', '2nd haha', 'In Progress', '2025-05-08', '2025-05-30', 5, '2025-05-08 10:05:41'),
(3, '3rd', '3rd na to', 'Completed', '2025-05-08', '2025-05-22', 5, '2025-05-08 12:05:26');

-- --------------------------------------------------------

--
-- Table structure for table `project_users`
--

CREATE TABLE `project_users` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_users`
--

INSERT INTO `project_users` (`project_id`, `user_id`) VALUES
(1, 2),
(2, 2),
(2, 5),
(3, 2),
(3, 5),
(3, 7);

-- --------------------------------------------------------

--
-- Table structure for table `task_list`
--

CREATE TABLE `task_list` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `task_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_list`
--

INSERT INTO `task_list` (`task_id`, `project_id`, `task_name`, `description`, `status`, `date_created`) VALUES
(1, 2, 'start nyo na', 'simulan nyo na oy', 'In Progress', '2025-05-08 10:06:41'),
(2, 2, 'start nyo na', 'simulan nyo na oy', 'In Progress', '2025-05-08 10:06:42'),
(3, 3, 'start', 'hahah', 'Completed', '2025-05-08 12:05:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1=admin, 2=manager, 3=employee',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `firstname`, `lastname`, `email`, `password`, `type`, `date_created`) VALUES
(2, 'Emman', 'Chris', 'eman@gmail.com', '$2y$10$hUgceFh2nFGIT2qfAsDaWu74HBfRxn0k5mVxaVQFwvsFJ.XPp6yu6', 3, '2025-05-07 15:41:00'),
(4, 'John', 'Cedrick', 'ced@gmail.com', '$2y$10$sSYUge/IRf4.Sy8MsQvuRu2QemDWRGEqjjLQp7Ep.Cky.s2BoCjiq', 1, '2025-05-08 09:49:24'),
(5, 'Cedrick', 'John', 'john@gmail.com', '$2y$10$35x7To3GSoGGJStIl7bU0.BTg.1YQ3CiKcoa8rXCJ305MuQQoloP6', 2, '2025-05-08 09:51:06'),
(6, 'Admin', 'Cedrick', 'admin@gmail.com', '$2y$10$PHrSeypjcndlRq0wIi.tqO8hGUIq1u2ETQW5U1kKC/OE.VTNMiwIy', 1, '2025-05-08 10:25:58'),
(7, 'Princess', 'Angelyne', 'ces@gmail.com', '$2y$10$abpjTXW.jSPHnuobYM2RVezlPmz7tu5FP.GLr2tVs5mjMwGKBd82m', 2, '2025-05-08 10:26:39'),
(8, 'Chris', 'Johanson', 'cj@gmail.com', '$2y$10$daupf3Nd8P6iI2jOp1Ri6.Q40s3Qj/lNeoCvjGKIqEgN1kD0S99uK', 3, '2025-05-08 10:26:53'),
(12, 'Nasty', 'Boi', 'nas@gmail.com', '$2y$10$J1gfoSDMFMaxQHYG2orKKOs/b4IPNVzyW6MbFHFmrhPI2pAv9bR4i', 2, '2025-05-08 10:42:43'),
(13, 'Mai', 'Mai', 'mai@gmail.com', '$2y$10$fTlkZyhE9FMuaDX.d6bRnO98lYSHm6.8AGHdS07YwY/sLUEc47bai', 1, '2025-05-08 10:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_productivity`
--

CREATE TABLE `user_productivity` (
  `user_productivity_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_rendered` float NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_productivity`
--

INSERT INTO `user_productivity` (`user_productivity_id`, `project_id`, `task_id`, `comment`, `subject`, `date`, `start_time`, `end_time`, `user_id`, `time_rendered`, `date_created`) VALUES
(1, 3, 3, 'natapos na boss', 'game game', '2025-05-08', '12:07:00', '20:07:00', 5, 8, '2025-05-08 12:07:23'),
(2, 3, 3, 'natapos na boss', 'game game', '2025-05-08', '12:07:00', '20:07:00', 5, 8, '2025-05-08 12:07:26'),
(3, 2, 1, 'hihi tapos na po', 'game game', '2025-05-10', '07:35:00', '19:03:00', 2, 11.4667, '2025-05-10 11:03:44'),
(4, 2, 1, 'hihi tapos na po', 'game game', '2025-05-10', '07:35:00', '19:03:00', 2, 11.4667, '2025-05-10 11:03:47'),
(5, 2, 1, 'hihi tapos na po', 'game game', '2025-05-10', '07:35:00', '19:03:00', 2, 11.4667, '2025-05-10 11:04:17'),
(6, 2, 1, 'hehe', 'opo', '2025-05-10', '07:00:00', '19:00:00', 2, 12, '2025-05-10 11:04:47'),
(7, 2, 1, 'hehe', 'opo', '2025-05-10', '07:00:00', '19:00:00', 2, 12, '2025-05-10 11:04:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `project_list`
--
ALTER TABLE `project_list`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `project_users`
--
ALTER TABLE `project_users`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `task_list`
--
ALTER TABLE `task_list`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_productivity`
--
ALTER TABLE `user_productivity`
  ADD PRIMARY KEY (`user_productivity_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `project_list`
--
ALTER TABLE `project_list`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_list`
--
ALTER TABLE `task_list`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_productivity`
--
ALTER TABLE `user_productivity`
  MODIFY `user_productivity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_list`
--
ALTER TABLE `project_list`
  ADD CONSTRAINT `project_list_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `project_users`
--
ALTER TABLE `project_users`
  ADD CONSTRAINT `project_users_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project_list` (`project_id`),
  ADD CONSTRAINT `project_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `task_list`
--
ALTER TABLE `task_list`
  ADD CONSTRAINT `task_list_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project_list` (`project_id`);

--
-- Constraints for table `user_productivity`
--
ALTER TABLE `user_productivity`
  ADD CONSTRAINT `user_productivity_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project_list` (`project_id`),
  ADD CONSTRAINT `user_productivity_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `task_list` (`task_id`),
  ADD CONSTRAINT `user_productivity_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`users_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
