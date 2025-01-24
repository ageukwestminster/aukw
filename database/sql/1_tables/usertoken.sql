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
-- Table structure for table `usertoken`
--

DROP TABLE IF EXISTS `usertoken`;
CREATE TABLE `usertoken` (
  `iduser` int(11) NOT NULL COMMENT 'FK to user table',
  `primaryKey` varchar(36) NOT NULL COMMENT 'jti for access token',
  `secondaryKey` varchar(36) NOT NULL COMMENT 'jti for refresh token',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'When 0 both tokens are invalid',
  `issuedAt` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'iat claim of both tokens',
  `expiresAt` datetime NOT NULL COMMENT 'exp claim for refresh token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Store of access/refresh token pairs';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `usertoken`
--
ALTER TABLE `usertoken`
  ADD KEY `fk_usertoken_user_idx` (`iduser`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `usertoken`
--
ALTER TABLE `usertoken`
  ADD CONSTRAINT `fk_usertoken_user_idx` FOREIGN KEY (`iduser`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
