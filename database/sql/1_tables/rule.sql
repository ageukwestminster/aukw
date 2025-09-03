-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 13, 2025 at 11:09 PM
-- Server version: 10.11.6-MariaDB-0+deb12u1-log
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `trade_entity` int(11) NOT NULL COMMENT '	New trade entity (such as Vendor, Customer) Id',
  `trade_taxable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'When 1 the new trade is Vatable.',
  `trade_description` int(11) DEFAULT NULL COMMENT 'Optional expense line description',
  `trade_memo` int(11) DEFAULT NULL COMMENT 'Optional memo for new trade',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define rules that are used for interco trade entry';

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
  MODIFY `ruleid` int(11) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
