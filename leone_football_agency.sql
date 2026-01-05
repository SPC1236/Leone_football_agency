-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 04:34 PM
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
-- Database: `leone_football_agency`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `years_experience` int(3) DEFAULT NULL,
  `represented_players` text DEFAULT NULL,
  `agency_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `user_id`, `license_number`, `years_experience`, `represented_players`, `agency_name`) VALUES
(1, 7, 'FIFA-AG-2023-001', 8, NULL, 'West Africa Sports Agency'),
(2, 8, 'FIFA-AG-2023-002', 5, NULL, 'Freetown Talent Management'),
(3, 14, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `club_managers`
--

CREATE TABLE `club_managers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `club_name` varchar(100) DEFAULT NULL,
  `club_location` varchar(100) DEFAULT NULL,
  `club_league` varchar(100) DEFAULT NULL,
  `club_type` enum('professional','academy','amateur') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_managers`
--

INSERT INTO `club_managers` (`id`, `user_id`, `club_name`, `club_location`, `club_league`, `club_type`) VALUES
(2, 9, 'Eastern United FC', 'Freetown', 'Sierra Leone Premier League', 'professional'),
(3, 10, 'Western Stars Academy', 'Bo', 'Youth Development League', 'academy');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'patrick cole', 'patrickcole@gmail.com', '+232145673', 'scouting-inquiry', 'i want to know the players avaliable', 'unread', '2025-12-02 15:19:21');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `height` varchar(10) DEFAULT NULL,
  `weight` varchar(10) DEFAULT NULL,
  `preferred_foot` enum('left','right','both') DEFAULT NULL,
  `current_club` varchar(100) DEFAULT NULL,
  `previous_clubs` text DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `user_id`, `position`, `nationality`, `age`, `height`, `weight`, `preferred_foot`, `current_club`, `previous_clubs`, `achievements`, `video_url`) VALUES
(1, 1, 'Striker', 'Sierra Leonean', 23, '182cm', '75kg', NULL, 'East End Lions', NULL, NULL, NULL),
(2, 1, 'Midfielder', 'Sierra Leonean', 25, '175cm', '70kg', NULL, 'Bo Rangers', NULL, NULL, NULL),
(3, 1, 'Defender', 'Sierra Leonean', 22, '185cm', '78kg', NULL, 'Mighty Blackpool', NULL, NULL, NULL),
(4, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 4, 'Striker', 'Sierra Leonean', 23, '182cm', '75kg', NULL, 'East End Lions', NULL, NULL, NULL),
(6, 5, 'Midfielder', 'Sierra Leonean', 25, '175cm', '70kg', NULL, 'Bo Rangers', NULL, NULL, NULL),
(7, 6, 'Defender', 'Sierra Leonean', 22, '185cm', '78kg', NULL, 'Mighty Blackpool', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','player','agent','manager') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `login_attempts` int(3) DEFAULT 0,
  `last_login_attempt` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `user_type`, `full_name`, `phone`, `status`, `login_attempts`, `last_login_attempt`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@freetownfa.com', '$2y$10$s1RZXXmZ0zkQ7D7JHZAeS.rvEG5QYc/SQfYtvtMmZ19dn.22FWsQi', 'admin', 'Administrator', NULL, 'approved', 0, NULL, NULL, '2025-12-02 10:53:45', '2025-12-02 14:00:51'),
(2, 'Sarah', 'sarahprincessokike@test.com', '$2y$10$T47lbwCYiKTal8tkOFACCuO0yK/Y6pSlWDY1sfmMHlAIFRa7H2ZPe', 'manager', 'Sarah Okike', '+232 142573', 'approved', 4, '2025-12-02 15:44:18', NULL, '2025-12-02 11:37:33', '2025-12-02 14:44:18'),
(3, 'AKK', 'alliek@gmail.com', '$2y$10$Z2lVqHtRV8GqjqiLuabC1uGs6pEX1vYHBKit2DSzUW8zZYLpA7Kse', 'player', 'Allie Kalokoh', '+232145678', 'approved', 0, NULL, NULL, '2025-12-02 14:46:01', '2025-12-02 14:46:53'),
(4, 'john_kamara', 'john.kamara@example.com', '$2y$10$qTwwZAhI/nL9yjZkktro7un8w77UHX5Pmrm8t.wrpajjnFF57foNy', 'player', 'John Kamara', '+232 76 111 222', 'approved', 0, NULL, NULL, '2025-12-02 15:02:45', '2025-12-02 15:02:45'),
(5, 'mohamed_bangura', 'mohamed.bangura@example.com', '$2y$10$FqWL0vinkj.1LtXYpEFFROPh.zZfaWQivIuyfaajkOutPJt3Ar9Zy', 'player', 'Mohamed Bangura', '+232 76 222 333', 'approved', 0, NULL, NULL, '2025-12-02 15:02:45', '2025-12-02 15:02:45'),
(6, 'sorie_kamara', 'sorie.kamara@example.com', '$2y$10$5OTat7qwbhLrraLeKmewp.TRtdz3HZCR.tlYXOc7L8KIBhrDCJ6Qu', 'player', 'Sorie Kamara', '+232 76 333 444', 'approved', 0, NULL, NULL, '2025-12-02 15:02:45', '2025-12-02 15:02:45'),
(7, 'david_johnson', 'david.johnson@example.com', '$2y$10$f41lZlV3j.krQMGcQyW09OUJ3VrUmYp3CCjRC0hUKcegAcWrOYjse', 'agent', 'David Johnson', '+232 76 444 555', 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:02:46'),
(8, 'fatmata_koroma', 'fatmata.koroma@example.com', '$2y$10$rMV56yOyIkKe2MX0pAKfKucQuBMdFdN.jpT/XXuLPHexOnUbqvCCC', 'agent', 'Fatmata Koroma', '+232 76 555 666', 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:02:46'),
(9, 'michael_stevens', 'michael.stevens@example.com', '$2y$10$9Gi0M04gcDX.4Dy0dUm0v.zZeRNZn48btK81ROjQjt7HVK5JjkXiG', 'manager', 'Michael Stevens', '+232 76 666 777', 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:02:46'),
(10, 'sarah_cole', 'sarah.cole@example.com', '$2y$10$lU5l8tXDuo9L0DSFlvD3WOV7meU75CdXcPzAQd0Mfzo4Y.5I.dKvK', 'manager', 'Sarah Cole', '+232 76 777 888', 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:02:46'),
(11, 'pending_player', 'pending.player@example.com', '$2y$10$NetTxqTha3tK0NkNoemuqej/A9d6D79huMYYn3bIRFwphVktZp6Km', 'player', 'Pending Player', NULL, 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:03:28'),
(12, 'pending_agent', 'pending.agent@example.com', '$2y$10$K3LdzJZJtk/21odwQqoITOwO/wpcTfeC.jpJY277slsjj4ei.NmVy', 'agent', 'Pending Agent', NULL, 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:03:26'),
(13, 'pending_manager', 'pending.manager@example.com', '$2y$10$ttcTCvVD7x5MMq74mYCydOEd5taj7Tp4rX8rgOtxcKcoQgVsZlnqy', 'manager', 'Pending Manager', NULL, 'approved', 0, NULL, NULL, '2025-12-02 15:02:46', '2025-12-02 15:03:25'),
(14, 'JC', 'juliacole@gmail.com', '$2y$10$DP1Br0YHeQ1Vp4Tf9e9.KeFO.8E2Qk39Y5nHZKrZq.L4L3xJc6kW6', 'agent', 'Julia Cole', '034442321', 'approved', 0, NULL, NULL, '2025-12-02 15:05:03', '2025-12-02 15:05:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `club_managers`
--
ALTER TABLE `club_managers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `club_managers`
--
ALTER TABLE `club_managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_managers`
--
ALTER TABLE `club_managers`
  ADD CONSTRAINT `club_managers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
