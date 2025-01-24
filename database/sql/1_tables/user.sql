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
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `shopid` int(11) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `suspended` tinyint(1) NOT NULL,
  `failedloginattempts` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(255) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `quickbooksUserId` varchar(50) DEFAULT NULL COMMENT 'The identifier for users’ Intuit accounts.',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `firstname`, `surname`, `shopid`, `isAdmin`, `suspended`, `failedloginattempts`, `email`, `title`, `quickbooksUserId`, `timestamp`) VALUES
(1, 'nsc', '$2y$10$HQshBD7OtWsGQBY8aL4qgecWZLLPWd8pdRv.lD1mQAxva0sVSOIMO', 'Neil', 'Carthy', 1, 1, 0, 0, 'neil.carthy@ageukwestminster.org.uk', 'Treasurer', '3bdfb5a7-cbd0-4a0e-a801-74c560681fad', '2024-03-10 22:34:55'),
(2, 'breda', '$2y$10$fIqvcARGtdZMQVI5g.Ei2eUEKlaua5ZXo/cqoI.ojCW/Xzl98X/5a', 'Breda', 'Bentley', 1, 0, 0, 0, '', '', NULL, '2022-12-05 13:47:10'),
(3, 'ayten', '$2y$10$sx7ZXXRH4IEo9W6VEaNDfu28DegZbtm5lI4yhL17VPw8qGg9olt5W', 'Ayten', 'Abrahams', 1, 1, 0, 0, 'ayten.abrahams@ageukwestminster.org.uk', '', NULL, '2023-03-27 11:22:05'),
(5, 'fardus', '$2y$10$qqwe3Eyyz34ZQrWWp/XDlOVW.bLAgYLDQ.JLGC3YTmf81VW1iyy.O', 'Fardus', 'Aburgegha', 1, 0, 0, 0, NULL, NULL, NULL, '2021-05-07 09:30:43'),
(6, 'mehfuz', '$2y$10$tfRNitP5akw6woFp0K0I..5RrUEMywLPeOGqvQ05IMo.wi3fuATke', 'Mehfuz', 'Ahmed', 1, 0, 0, 0, '', '', NULL, '2021-09-15 22:03:47'),
(7, 'arjun', '$2y$10$T9x.prU1x/Mm3aHb45r9/eKs4bTJ1.tZQBLVE16I44LNoWQ8ECjue', 'Arjun', 'Bhatt', 1, 0, 1, 0, '', '', NULL, '2023-08-24 22:04:26'),
(8, 'nabeel', '$2y$10$a1B1NS.QXb9jtX1TH9DIDun/qulP..hGzRkGYOFKd.OKuPnBxuUzC', 'Nabeel', 'Shahzad', 1, 0, 1, 0, '', '', NULL, '2023-09-14 21:51:37'),
(13, 'ageuk', '$2y$10$dAKdpXAH.opiSBiXgbHIj.MQINOL/2wbB0hQIODVS/fHSYyfTCli2', 'Staff', 'Member', 1, 0, 0, 0, '', '', NULL, '2023-04-11 14:43:51'),
(14, 'kaya', '$2y$10$Khv3DKZgpiqOzWbZUeIfqOlGC2y2OKIJ29sH3Hg.uElWao5u.MoE2', 'Kaya', 'Taylor', 1, 1, 0, 0, 'klt.business.management@gmail.com', 'Finance Manager', NULL, '2024-11-20 19:11:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_shop` (`shopid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_user_shop` FOREIGN KEY (`shopid`) REFERENCES `shop` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
