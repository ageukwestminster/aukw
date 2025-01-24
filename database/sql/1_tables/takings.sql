-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 24, 2025 at 02:32 PM
-- Server version: 10.6.20-MariaDB
-- PHP Version: 8.3.15

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
-- Table structure for table `takings`
--

DROP TABLE IF EXISTS `takings`;
CREATE TABLE `takings` (
  `takingsid` int(11) NOT NULL,
  `date` date NOT NULL COMMENT 'Sales date',
  `shopid` int(11) NOT NULL COMMENT '1 = HR, 2 = CS',
  `clothing_num` int(11) NOT NULL DEFAULT 0,
  `brica_num` int(11) NOT NULL DEFAULT 0 COMMENT 'Bric-a-Brac',
  `books_num` int(11) NOT NULL DEFAULT 0,
  `linens_num` int(11) NOT NULL DEFAULT 0,
  `donations_num` int(11) NOT NULL DEFAULT 0,
  `other_num` int(11) NOT NULL DEFAULT 0,
  `rag_num` int(11) NOT NULL DEFAULT 0,
  `clothing` decimal(8,2) NOT NULL DEFAULT 0.00,
  `brica` decimal(8,2) NOT NULL DEFAULT 0.00,
  `books` decimal(8,2) NOT NULL DEFAULT 0.00,
  `linens` decimal(8,2) NOT NULL DEFAULT 0.00,
  `donations` decimal(8,2) NOT NULL DEFAULT 0.00,
  `other` decimal(8,2) NOT NULL DEFAULT 0.00,
  `rag` decimal(8,2) NOT NULL DEFAULT 0.00,
  `customers_num_total` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of transactions',
  `cash_to_bank` decimal(8,2) NOT NULL DEFAULT 0.00,
  `credit_cards` decimal(8,2) NOT NULL DEFAULT 0.00,
  `operating_expenses` decimal(8,2) NOT NULL DEFAULT 0.00,
  `volunteer_expenses` decimal(8,2) NOT NULL DEFAULT 0.00,
  `other_adjustments` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Not used by the app',
  `cash_to_charity` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Not used by the app',
  `cash_difference` decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Difference between cash + credit cards + expenses and total of sales ',
  `comments` mediumtext DEFAULT NULL,
  `rags_paid_in_cash` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=daily rags were paid in cash rather than by credit card or bank transfer. Not used by the app.',
  `quickbooks` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Not yet transferred to QB',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `takings`
--
ALTER TABLE `takings`
  ADD PRIMARY KEY (`takingsid`),
  ADD UNIQUE KEY `takings_date_shop` (`date`,`shopid`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `takings`
--
ALTER TABLE `takings`
  MODIFY `takingsid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
