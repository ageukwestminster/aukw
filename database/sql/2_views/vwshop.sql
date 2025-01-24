-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 24, 2025 at 02:33 PM
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
-- Structure for view `vwshop`
--

DROP VIEW IF EXISTS `vwshop`;
CREATE VIEW `vwshop`  AS SELECT `shop`.`id` AS `id`, `shop`.`name` AS `name`, `shop`.`address` AS `address`, `shop`.`salestarget` AS `salestarget`, `shop`.`versionid` AS `versionid`, `shop`.`validfrom` AS `validfrom`, current_timestamp() + interval 1 week AS `validuntil` FROM `shop`union select `shopamendment`.`id` AS `id`,`shopamendment`.`name` AS `name`,`shopamendment`.`address` AS `address`,`shopamendment`.`salestarget` AS `salestarget`,`shopamendment`.`versionid` AS `versionid`,`shopamendment`.`validfrom` AS `validfrom`,`shopamendment`.`validuntil` AS `validuntil` from `shopamendment`  ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
