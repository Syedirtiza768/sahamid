-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2022 at 06:23 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 7.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sahamid`
--

-- --------------------------------------------------------

--
-- Table structure for table `pc_cash_demand`
--

DROP TABLE IF EXISTS `pc_cash_demand`;
CREATE TABLE `pc_cash_demand` (
  `id` int(11) NOT NULL,
  `pc_id` int(11) NOT NULL,
  `paintcost_qty` int(11) NOT NULL DEFAULT 1,
  `paintcost_budget` int(11) NOT NULL,
  `paintcost_actual` int(11) NOT NULL,
  `paintcost_profit` int(11) DEFAULT NULL,
  `rent_qty` int(11) NOT NULL DEFAULT 1,
  `rent_budget` int(11) NOT NULL,
  `rent_actual` float NOT NULL,
  `rent_profit` int(11) NOT NULL,
  `misc_exp_qty` int(11) NOT NULL DEFAULT 1,
  `misc_exp_actual` float NOT NULL,
  `misc_exp_profit` float NOT NULL,
  `labour_qty` int(11) NOT NULL DEFAULT 1,
  `labour_actual` float NOT NULL,
  `labour_profit` float NOT NULL,
  `bbr_sleeve_qty` int(11) NOT NULL DEFAULT 1,
  `bbr_sleeve_actual` float NOT NULL,
  `bbr_sleeve_profit` float NOT NULL,
  `ms_sheet_qty` int(11) NOT NULL DEFAULT 1,
  `ms_sheet_actual` float NOT NULL,
  `ms_sheet_profit` float NOT NULL,
  `cable_qty` int(11) NOT NULL DEFAULT 1,
  `cable_actual` float NOT NULL,
  `cable_profit` float NOT NULL,
  `hinges_qty` int(11) NOT NULL DEFAULT 1,
  `hinges_actual` float NOT NULL,
  `hinges_profit` float NOT NULL,
  `lock_qty` int(11) NOT NULL DEFAULT 1,
  `lock_actual` float NOT NULL,
  `lock_profit` float NOT NULL,
  `acrylic_qty` int(11) NOT NULL DEFAULT 1,
  `acrylic_actual` float NOT NULL,
  `acrylic_profit` float NOT NULL,
  `gaskit_qty` int(11) NOT NULL DEFAULT 1,
  `gaskit_actual` float NOT NULL,
  `gaskit_profit` float NOT NULL,
  `cd_qty` int(11) NOT NULL DEFAULT 1,
  `cd_actual` float NOT NULL,
  `cd_profit` float NOT NULL,
  `ibolt_qty` int(11) NOT NULL DEFAULT 1,
  `ibolt_actual` float NOT NULL,
  `ibolt_profit` float NOT NULL,
  `misc_exp_budget` float DEFAULT NULL,
  `labour_budget` float DEFAULT NULL,
  `bbr_sleeve_budget` float DEFAULT NULL,
  `ms_sheet_budget` float DEFAULT NULL,
  `cable_budget` float NOT NULL,
  `hinges_budget` float DEFAULT NULL,
  `lock_budget` float DEFAULT NULL,
  `acrylic_budget` float DEFAULT NULL,
  `gaskit_budget` float DEFAULT NULL,
  `cd_budget` float DEFAULT NULL,
  `ibolt_budget` float DEFAULT NULL,
  `cashdemand_total` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pc_cash_demand`
--
ALTER TABLE `pc_cash_demand`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pc_cash_demand`
--
ALTER TABLE `pc_cash_demand`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
