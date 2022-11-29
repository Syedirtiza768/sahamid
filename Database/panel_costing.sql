-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2022 at 06:22 PM
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
-- Table structure for table `panel_costing`
--

DROP TABLE IF EXISTS `panel_costing`;
CREATE TABLE `panel_costing` (
  `id` int(11) NOT NULL,
  `salescaseref` varchar(255) NOT NULL,
  `pc_h` int(11) DEFAULT NULL,
  `pc_w` int(11) DEFAULT NULL,
  `pc_d` int(11) DEFAULT NULL,
  `panel_type` varchar(255) DEFAULT NULL,
  `conopy` varchar(255) DEFAULT NULL,
  `d1_h` int(11) NOT NULL,
  `d1_w` int(11) NOT NULL,
  `d1_d` int(11) NOT NULL,
  `d2_h` int(11) NOT NULL,
  `d2_w` int(11) NOT NULL,
  `d2_d` int(11) NOT NULL,
  `d3_h` int(11) DEFAULT NULL,
  `d3_w` int(11) NOT NULL,
  `d3_d` int(11) NOT NULL,
  `d4_h` int(11) NOT NULL,
  `d4_w` int(11) NOT NULL,
  `d4_d` int(11) NOT NULL,
  `d5_h` int(11) NOT NULL,
  `d5_w` int(11) NOT NULL,
  `d5_d` int(11) NOT NULL,
  `d6_h` int(11) NOT NULL,
  `d6_w` int(11) NOT NULL,
  `d6_d` int(11) NOT NULL,
  `d7_h` int(11) NOT NULL,
  `d7_w` int(11) NOT NULL,
  `d7_d` int(11) NOT NULL,
  `d8_h` int(11) NOT NULL,
  `d8_w` int(11) NOT NULL,
  `d8_d` int(11) NOT NULL,
  `door1_cp` varchar(255) DEFAULT NULL,
  `door2_cp` varchar(255) DEFAULT NULL,
  `door3_cp` varchar(255) DEFAULT NULL,
  `door4_cp` varchar(255) DEFAULT NULL,
  `door5_cp` varchar(255) DEFAULT NULL,
  `door6_cp` varchar(255) DEFAULT NULL,
  `door7_cp` varchar(255) DEFAULT NULL,
  `door8_cp` varchar(255) DEFAULT NULL,
  `sheet_selection` varchar(255) DEFAULT NULL,
  `sheet_use` float DEFAULT NULL,
  `14swg_sw` float DEFAULT NULL,
  `14swg_sc` float DEFAULT NULL,
  `16swg_sw` float DEFAULT NULL,
  `16swg_sc` float DEFAULT NULL,
  `18swg_sw` float DEFAULT NULL,
  `18swg_sc` float DEFAULT NULL,
  `paintcost_model` varchar(255) DEFAULT NULL,
  `paintcost_total` float DEFAULT NULL,
  `rent` float DEFAULT NULL,
  `hinges_model` varchar(255) DEFAULT NULL,
  `hinges_qty` float DEFAULT NULL,
  `hinges_cost` float DEFAULT NULL,
  `lock_model` varchar(255) DEFAULT NULL,
  `lock_qty` float DEFAULT NULL,
  `lock_cost` float DEFAULT NULL,
  `acrylic_qty` float DEFAULT NULL,
  `acrylic_cost` float DEFAULT NULL,
  `gk_qty` float DEFAULT NULL,
  `gk_cost` float DEFAULT NULL,
  `ibolt_qty` float DEFAULT NULL,
  `ibolt_cost` float DEFAULT NULL,
  `cd_model` varchar(255) DEFAULT NULL,
  `cd_qty` float DEFAULT NULL,
  `cd_cost` float DEFAULT NULL,
  `wiring` float DEFAULT NULL,
  `labour` float DEFAULT NULL,
  `misc_exp` float DEFAULT NULL,
  `bbr1_dimension` varchar(255) DEFAULT NULL,
  `bbr1_qty` float DEFAULT NULL,
  `bbr1_weight` float DEFAULT NULL,
  `bbr1_sleeve` float DEFAULT NULL,
  `bbr2_dimension` varchar(255) DEFAULT NULL,
  `bbr2_qty` float DEFAULT NULL,
  `bbr2_weight` float DEFAULT NULL,
  `bbr2_sleeve` float DEFAULT NULL,
  `bbr3_dimension` varchar(255) DEFAULT NULL,
  `bbr3_qty` float DEFAULT NULL,
  `bbr3_weight` float DEFAULT NULL,
  `bbr3_sleeve` float DEFAULT NULL,
  `bbr4_dimension` varchar(255) DEFAULT NULL,
  `bbr4_qty` float DEFAULT NULL,
  `bbr4_weight` float DEFAULT NULL,
  `bbr4_sleeve` float DEFAULT NULL,
  `busbar_weight` float DEFAULT NULL,
  `busbar_cost` float DEFAULT NULL,
  `busbar_sleeve` float DEFAULT NULL,
  `busbar_totalcost` float DEFAULT NULL,
  `14swg_total` int(11) NOT NULL,
  `14swg_percent` varchar(255) DEFAULT NULL,
  `16swg_final` float DEFAULT NULL,
  `16swg_total` float DEFAULT NULL,
  `16swg_percent` varchar(255) DEFAULT NULL,
  `14swg_final` float DEFAULT NULL,
  `18swg_total` float DEFAULT NULL,
  `18swg_percent` varchar(255) DEFAULT NULL,
  `18swg_final` float DEFAULT NULL,
  `closed` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `panel_costing`
--
ALTER TABLE `panel_costing`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `panel_costing`
--
ALTER TABLE `panel_costing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
