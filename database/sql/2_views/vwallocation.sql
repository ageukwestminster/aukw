-- phpMyAdmin SQL Dump
-- version 5.2.2deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 13, 2025 at 05:16 PM
-- Server version: 11.8.3-MariaDB-0+deb13u1 from Debian
-- PHP Version: 8.3.27

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

--
-- VIEW `vwallocation`
-- Data: None
--


-- --------------------------------------------------------

--
-- Structure for view `vwallocation`
--

CREATE VIEW `vwallocation`  AS SELECT `allocation`.`quickbooksId` AS `quickbooksId`, `allocation`.`payrollNumber` AS `payrollNumber`, `allocation`.`percentage` AS `percentage`, `allocation`.`account` AS `account`, `allocation`.`class` AS `class`, `allocation`.`isShopEmployee` AS `isShopEmployee`, `allocation`.`timestamp` AS `validfrom`, current_timestamp() + interval 1 week AS `validto` FROM `allocation`union select `allocationamendment`.`quickbooksId` AS `quickbooksId`,`allocationamendment`.`payrollNumber` AS `payrollNumber`,`allocationamendment`.`percentage` AS `percentage`,`allocationamendment`.`account` AS `account`,`allocationamendment`.`class` AS `class`,`allocationamendment`.`isShopEmployee` AS `isShopEmployee`,`allocationamendment`.`validfrom` AS `validfrom`,`allocationamendment`.`validto` AS `validto` from `allocationamendment`  ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
