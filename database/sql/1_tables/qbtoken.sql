-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 24, 2025 at 02:30 PM
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
-- Table structure for table `qbtoken`
--

DROP TABLE IF EXISTS `qbtoken`;
CREATE TABLE `qbtoken` (
  `userid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `realmid` varchar(50) NOT NULL COMMENT 'QBO company ID',
  `accesstoken` varchar(1000) NOT NULL,
  `accesstokenexpiry` datetime NOT NULL,
  `refreshtoken` varchar(100) NOT NULL,
  `refreshtokenexpiry` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Quickbooks SDK Tokens';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qbtoken`
--
ALTER TABLE `qbtoken`
  ADD UNIQUE KEY `AK_user_userid_realmid` (`userid`,`realmid`) USING BTREE;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `qbtoken`
--
ALTER TABLE `qbtoken`
  ADD CONSTRAINT `FK_user_id` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
