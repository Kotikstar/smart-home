-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: MySql-8.4
-- Generation Time: Jul 20, 2025 at 10:00 PM
-- Server version: 8.4.4
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trk_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `identifier` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fuel_limit` float DEFAULT '0',
  `used` float DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `name`, `identifier`, `fuel_limit`, `used`) VALUES
(14, 'Admin', '1', 1000200, 1052);

-- --------------------------------------------------------

--
-- Table structure for table `diesel_prices`
--

CREATE TABLE `diesel_prices` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `price` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diesel_prices`
--

INSERT INTO `diesel_prices` (`id`, `date`, `price`) VALUES
(5, '2025-07-17', 1.37),
(6, '2025-07-16', 1.38),
(7, '2025-07-15', 1.38),
(8, '2025-07-14', 1.40),
(9, '2025-07-11', 1.39),
(10, '2025-07-10', 1.40),
(11, '2025-07-08', 1.39),
(12, '2025-07-07', 1.39),
(13, '2025-07-04', 1.38),
(14, '2025-07-03', 1.37),
(15, '2025-07-02', 1.35),
(16, '2025-07-01', 1.34),
(17, '2025-06-30', 1.34),
(18, '2025-06-27', 1.36),
(19, '2025-06-26', 1.39),
(20, '2025-06-25', 1.35),
(21, '2025-06-23', 1.43),
(22, '2025-06-20', 1.46),
(23, '2025-06-19', 1.41),
(24, '2025-06-18', 1.40),
(25, '2025-06-17', 1.36),
(26, '2025-06-16', 1.36);

-- --------------------------------------------------------

--
-- Table structure for table `fuel`
--

CREATE TABLE `fuel` (
  `id` int NOT NULL,
  `amount` float NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `alert_flag` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel`
--

INSERT INTO `fuel` (`id`, `amount`, `updated_at`, `alert_flag`) VALUES
(2, 9539, '2025-07-07 12:41:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `card_id` int DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('dispense','refill') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dispense'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `card_id`, `amount`, `created_at`, `type`) VALUES
(6, NULL, 10, '2025-07-06 17:23:04', 'dispense'),
(7, NULL, 100, '2025-07-06 17:26:26', 'refill'),
(8, NULL, 100, '2025-07-06 17:27:02', 'refill'),
(9, NULL, 100, '2025-07-06 17:40:10', 'refill'),
(10, NULL, 100, '2025-07-06 17:40:17', 'refill'),
(11, 14, 10, '2025-07-06 17:46:23', 'refill'),
(12, 14, 52, '2025-07-07 12:12:39', 'dispense'),
(13, 14, 10, '2025-07-07 12:17:24', 'dispense'),
(14, 14, 10, '2025-07-07 12:26:20', 'dispense'),
(15, 14, 20, '2025-07-07 12:26:31', 'dispense'),
(16, 14, 20, '2025-07-07 12:39:34', 'refill'),
(17, 14, 10, '2025-07-07 12:39:42', 'refill'),
(18, NULL, 10000, '2025-07-07 12:40:31', 'refill'),
(19, 14, 999999, '2025-07-07 12:40:58', 'refill'),
(20, 14, 950, '2025-07-07 12:41:08', 'dispense'),
(21, 14, 10, '2025-07-07 12:41:19', 'dispense');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int NOT NULL,
  `filter_type` enum('coarse','fine') COLLATE utf8mb4_general_ci NOT NULL,
  `last_service` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ready','in_service') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ready',
  `liters_since_service` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`id`, `filter_type`, `last_service`, `status`, `liters_since_service`) VALUES
(1, 'coarse', '2025-07-07 11:06:12', 'ready', 0),
(2, 'fine', '2025-07-07 11:06:12', 'ready', 0);

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id` int NOT NULL,
  `type` enum('coarse','fine') COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('ok','in_service') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ok',
  `last_serviced` datetime DEFAULT NULL,
  `liters_at_service` float DEFAULT '0',
  `interval_liters` float DEFAULT '1000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`id`, `type`, `status`, `last_serviced`, `liters_at_service`, `interval_liters`) VALUES
(1, 'coarse', 'ok', '2025-07-07 15:03:09', 0, 1000),
(2, 'fine', 'ok', '2025-07-07 15:03:00', 0, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('low_fuel_alert_sent', '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`);

--
-- Indexes for table `diesel_prices`
--
ALTER TABLE `diesel_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indexes for table `fuel`
--
ALTER TABLE `fuel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_card` (`card_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `diesel_prices`
--
ALTER TABLE `diesel_prices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `fuel`
--
ALTER TABLE `fuel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `fk_card` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
