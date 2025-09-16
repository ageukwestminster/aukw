-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 16, 2025 at 05:51 PM
-- Server version: 11.8.3-MariaDB-0+deb13u1 from Debian
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aukworgu_dailytakings`
--

-- --------------------------------------------------------

--
-- Table structure for table `rule`
--

CREATE TABLE `rule` (
  `ruleid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `charity` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'When 1 then apply rule to charity trades, apply to enterprises otherwise.',
  `search_account` int(11) DEFAULT NULL COMMENT 'The Id of the QuickBooks account',
  `search_entity` int(11) DEFAULT NULL COMMENT 'The QB Id of the Vendor or Customer',
  `search_docnumber` varchar(50) DEFAULT NULL COMMENT 'Search string go QB DocNumber',
  `search_memo` varchar(50) DEFAULT NULL COMMENT 'Search string for QB memo (aka PrivateNote)',
  `trade_account` int(11) NOT NULL COMMENT 'The Id of the QB expense accoutn for the new trade',
  `trade_entity` int(11) NOT NULL COMMENT 'New trade entity (such as Vendor, Customer) Id',
  `trade_taxable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'When 1 the new trade is Vatable.',
  `trade_description` varchar(255) DEFAULT NULL COMMENT 'Optional expense line description',
  `trade_memo` varchar(255) NOT NULL COMMENT 'Required memo for transaction and associated transfer',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define rules that are used for interco trade entry';

--
-- Dumping data for table `rule`
--

INSERT INTO `rule` (`ruleid`, `name`, `charity`, `search_account`, `search_entity`, `search_docnumber`, `search_memo`, `trade_account`, `trade_entity`, `trade_taxable`, `trade_description`, `trade_memo`, `timestamp`) VALUES
(1, 'Pleo Morplan', 1, 429, NULL, NULL, 'morplan', 109, 67, 1, NULL, 'Morplan purchase via Pleo', '2025-09-16 17:04:14'),
(2, 'Iris FMP', 1, NULL, 220, NULL, NULL, 106, 45, 1, 'Staff payroll software costs', 'Iris FMP monthly payroll software chargeback', '2025-09-16 17:04:31'),
(3, 'EE', 1, NULL, 327, NULL, NULL, 63, 78, 1, 'Monthly mobile phone bill', 'Monthly mobile phone bill', '2025-09-16 17:04:41'),
(4, 'Gallaher', 1, NULL, 691, NULL, NULL, 44, 143, 0, 'Annual insurance bill', 'Insurance chargeback', '2025-09-16 17:04:54'),
(5, 'Goldwins', 1, NULL, 359, NULL, NULL, 114, 100, 1, 'Annual audit bill', 'Annual audit chargeback', '2025-09-16 17:05:04'),
(6, 'Ryman', 1, 429, NULL, NULL, 'ryman', 87, 158, 1, 'Stationary purchases for shop', 'Ryman stationary via Pleo', '2025-09-16 17:05:23'),
(7, 'Amazon', 1, 429, NULL, NULL, 'amazon', 109, 77, 1, 'Amazon purchases for shop', 'Amazon purchases via Pleo', '2025-09-16 17:05:32'),
(8, 'Royal Mail', 1, 429, NULL, NULL, 'royal mail', 56, 153, 0, 'Postage for shop', 'Royal mail postage via Pleo', '2025-09-16 17:05:41'),
(9, 'Pimlico Plumbers', 1, 429, NULL, NULL, 'pimlico plumbers', 61, 84, 1, NULL, 'Pimlico Plumbers via Pleo', '2025-09-16 17:05:52'),
(10, 'Companies Hse', 1, 429, NULL, NULL, 'companies house', 114, 58, 0, NULL, 'Companies House fees', '2025-09-16 17:05:59'),
(11, 'All Host', 1, 429, NULL, NULL, 'allhost', 55, 146, 1, 'Annual hosting bill for website', 'Allhost website hosting charges', '2025-09-16 17:06:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rule`
--
ALTER TABLE `rule`
  ADD PRIMARY KEY (`ruleid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rule`
--
ALTER TABLE `rule`
  MODIFY `ruleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
