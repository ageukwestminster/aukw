-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 24, 2025 at 02:29 PM
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
-- Table structure for table `qbrealm`
--

DROP TABLE IF EXISTS `qbrealm`;
CREATE TABLE `qbrealm` (
  `realmid` varchar(50) NOT NULL,
  `companyName` varchar(100) NOT NULL,
  `isSandbox` tinyint(1) NOT NULL DEFAULT 0 COMMENT '‘1’ if a sandbox company. '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qbrealm`
--

INSERT INTO `qbrealm` (`realmid`, `companyName`, `isSandbox`) VALUES
('123145825016867', 'Age UK Westminster', 0),
('123145825993797', 'Sandbox Company_US_1', 1),
('4620816365027844190', 'Sandbox Company_GB_2', 1),
('9130350604308576', 'Age UK Enterprises (Westminster) Ltd', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qbrealm`
--
ALTER TABLE `qbrealm`
  ADD UNIQUE KEY `AK_qbrealm_realmid` (`realmid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
