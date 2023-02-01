-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2023 at 12:17 PM
-- Server version: 10.1.34-MariaDB
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
  `bbr5_dimension` varchar(255) DEFAULT NULL,
  `bbr5_qty` float DEFAULT NULL,
  `bbr5_weight` float DEFAULT NULL,
  `bbr5_sleeve` float DEFAULT NULL,
  `bbr6_dimension` varchar(255) DEFAULT NULL,
  `bbr6_qty` float DEFAULT NULL,
  `bbr6_weight` float DEFAULT NULL,
  `bbr6_sleeve` float DEFAULT NULL,
  `bbr7_dimension` varchar(255) DEFAULT NULL,
  `bbr7_qty` float DEFAULT NULL,
  `bbr7_weight` float DEFAULT NULL,
  `bbr7_sleeve` float DEFAULT NULL,
  `bbr8_dimension` varchar(255) DEFAULT NULL,
  `bbr8_qty` float DEFAULT NULL,
  `bbr8_weight` float DEFAULT NULL,
  `bbr8_sleeve` float DEFAULT NULL,
  `bbr9_dimension` varchar(255) DEFAULT NULL,
  `bbr9_qty` float DEFAULT NULL,
  `bbr9_weight` float DEFAULT NULL,
  `bbr9_sleeve` float DEFAULT NULL,
  `bbr10_dimension` varchar(255) DEFAULT NULL,
  `bbr10_qty` float DEFAULT NULL,
  `bbr10_weight` float DEFAULT NULL,
  `bbr10_sleeve` float DEFAULT NULL,
  `bbr11_dimension` varchar(255) DEFAULT NULL,
  `bbr11_qty` float DEFAULT NULL,
  `bbr11_weight` float DEFAULT NULL,
  `bbr11_sleeve` float DEFAULT NULL,
  `bbr12_dimension` varchar(255) DEFAULT NULL,
  `bbr12_qty` float DEFAULT NULL,
  `bbr12_weight` float DEFAULT NULL,
  `bbr12_sleeve` float DEFAULT NULL,
  `bbr13_dimension` varchar(255) DEFAULT NULL,
  `bbr13_qty` float DEFAULT NULL,
  `bbr13_weight` float DEFAULT NULL,
  `bbr13_sleeve` float DEFAULT NULL,
  `bbr14_dimension` varchar(255) DEFAULT NULL,
  `bbr14_qty` float DEFAULT NULL,
  `bbr14_weight` float DEFAULT NULL,
  `bbr14_sleeve` float DEFAULT NULL,
  `bbr15_dimension` varchar(255) DEFAULT NULL,
  `bbr15_qty` float DEFAULT NULL,
  `bbr15_weight` float DEFAULT NULL,
  `bbr15_sleeve` float DEFAULT NULL,
  `busbar_weight` float DEFAULT NULL,
  `busbar_cost` float DEFAULT NULL,
  `busbar_sleeve` float DEFAULT NULL,
  `busbar_totalcost` float DEFAULT NULL,
  `14swg_total` int(11) NOT NULL,
  `14swg_percent` varchar(255) DEFAULT '1',
  `16swg_final` float DEFAULT NULL,
  `16swg_total` float DEFAULT NULL,
  `16swg_percent` varchar(255) DEFAULT '1',
  `14swg_final` float DEFAULT NULL,
  `18swg_total` float DEFAULT NULL,
  `18swg_percent` varchar(255) DEFAULT '1',
  `18swg_final` float DEFAULT NULL,
  `sheet_sheet_cd` varchar(255) NOT NULL,
  `closed` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `panel_costing`
--

INSERT INTO `panel_costing` (`id`, `salescaseref`, `pc_h`, `pc_w`, `pc_d`, `panel_type`, `conopy`, `d1_h`, `d1_w`, `d1_d`, `d2_h`, `d2_w`, `d2_d`, `d3_h`, `d3_w`, `d3_d`, `d4_h`, `d4_w`, `d4_d`, `d5_h`, `d5_w`, `d5_d`, `d6_h`, `d6_w`, `d6_d`, `d7_h`, `d7_w`, `d7_d`, `d8_h`, `d8_w`, `d8_d`, `door1_cp`, `door2_cp`, `door3_cp`, `door4_cp`, `door5_cp`, `door6_cp`, `door7_cp`, `door8_cp`, `sheet_selection`, `sheet_use`, `14swg_sw`, `14swg_sc`, `16swg_sw`, `16swg_sc`, `18swg_sw`, `18swg_sc`, `paintcost_model`, `paintcost_total`, `rent`, `hinges_model`, `hinges_qty`, `hinges_cost`, `lock_model`, `lock_qty`, `lock_cost`, `acrylic_qty`, `acrylic_cost`, `gk_qty`, `gk_cost`, `ibolt_qty`, `ibolt_cost`, `cd_model`, `cd_qty`, `cd_cost`, `wiring`, `labour`, `misc_exp`, `bbr1_dimension`, `bbr1_qty`, `bbr1_weight`, `bbr1_sleeve`, `bbr2_dimension`, `bbr2_qty`, `bbr2_weight`, `bbr2_sleeve`, `bbr3_dimension`, `bbr3_qty`, `bbr3_weight`, `bbr3_sleeve`, `bbr4_dimension`, `bbr4_qty`, `bbr4_weight`, `bbr4_sleeve`, `bbr5_dimension`, `bbr5_qty`, `bbr5_weight`, `bbr5_sleeve`, `bbr6_dimension`, `bbr6_qty`, `bbr6_weight`, `bbr6_sleeve`, `bbr7_dimension`, `bbr7_qty`, `bbr7_weight`, `bbr7_sleeve`, `bbr8_dimension`, `bbr8_qty`, `bbr8_weight`, `bbr8_sleeve`, `bbr9_dimension`, `bbr9_qty`, `bbr9_weight`, `bbr9_sleeve`, `bbr10_dimension`, `bbr10_qty`, `bbr10_weight`, `bbr10_sleeve`, `bbr11_dimension`, `bbr11_qty`, `bbr11_weight`, `bbr11_sleeve`, `bbr12_dimension`, `bbr12_qty`, `bbr12_weight`, `bbr12_sleeve`, `bbr13_dimension`, `bbr13_qty`, `bbr13_weight`, `bbr13_sleeve`, `bbr14_dimension`, `bbr14_qty`, `bbr14_weight`, `bbr14_sleeve`, `bbr15_dimension`, `bbr15_qty`, `bbr15_weight`, `bbr15_sleeve`, `busbar_weight`, `busbar_cost`, `busbar_sleeve`, `busbar_totalcost`, `14swg_total`, `14swg_percent`, `16swg_final`, `16swg_total`, `16swg_percent`, `14swg_final`, `18swg_total`, `18swg_percent`, `18swg_final`, `sheet_sheet_cd`, `closed`) VALUES
(62, 'MT-2964-2022-01-01--101924', 1800, 1400, 700, 'floor_mount', 'yes', 1800, 600, 700, 1800, 700, 700, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', 'yes', NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 8.37941, 407.575, 366817, 321.77, 289593, 234.624, 211161, '7035', 18648.5, 4000, 'hl_030', 2, 900, 'ms_480', 2, 2200, 0.1, 500, 4, 26, 8, 880, '60*40', 5, 3250, 8000, 7000, 400, '30*5', 8, 3.6, 720, '100*5', 18, 25.2, 3375, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 28.8, 63360, 4095, NULL, 480077, '5', 422995, 402852, '5', 504081, 324421, '5', 340642, '', 1),
(63, 'MT-2341A-2022-01-03--030126', 800, 600, 600, 'wall_mount', 'no', 800, 600, 600, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'no', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ms_sheet', 1.27725, 62.1254, 18016.4, 49.0464, 14223.5, 35.763, 10371.3, '7035', 3229.17, 4000, 'hl_027', 2, 600, 'ms_480', 1, 1100, NULL, 0, 30, 195, NULL, 0, '40*40', 2, 1060, 8000, 3000, 2000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 41201, '10', 41148.4, 37407.6, '10', 45320.6, 33555.4, '10', 36911, '', 1),
(64, 'MT-1299-2022-12-02--031412', 800, 600, 600, 'floor_mount', 'yes', 800, 600, 600, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 2.40516, 116.987, 106458, 92.3581, 84045.9, 67.3445, 61283.5, '7032', 2583.34, 1000, 'hl_027', 1, 300, 'ms_408', 1, 550, 1, 5000, 30, 195, 20, 2200, NULL, NULL, 0, 4000, 5000, 4000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 131286, '10', 119762, 108874, '10', 144415, 86111.8, '10', 94723, '', 1),
(65, 'MT-1299-2022-12-02--031412', 800, NULL, 125, 'floor_mount', 'yes', 800, 650, 125, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'no', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ms_sheet', 1.07035, 52.0619, 15618.6, 41.1015, 12330.5, 29.9699, 8990.96, NULL, 0, 2000, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, NULL, 0, 4000, 4000, 4000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, 29919, '19', 30625, 26630.5, '15', 35603.1, 23291, '15', 26784.6, '', 1),
(66, 'MT-2964-2022-01-01--102032', 200, 480, 500, 'floor_mount', 'yes', 15415, 44154, 54, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ms_sheet', 1342.95, 65321, 19596300, 51569.2, 15470800, 37602.6, 11280800, '7032', 516.668, 4000, 'hl_051', 2, 300, 'ms_408', 9, 4950, 1151, 5755000, 51, 331.5, 1, 110, '40*40', 1, 530, 44, 51, 545, '80*10', 15, 33, 2587.5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 33, 72600, 2587.5, NULL, 25437872, '5', 22378000, 21312300, '5', 26709800, 17122300, '5', 17978500, 'ss_sheet', 1),
(67, 'MT-2964-2022-01-01--102032', 21212, NULL, NULL, NULL, NULL, 121, 12, 1223, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 0, 0, 0, 0, 0, 0, 0, '7035', 0, 212, 'hl_027', 212, 63600, 'pl_130', 21, 2730, 21212, 106060000, 212, 1378, 122, 13420, '25*40', 21212, 7848440, 211, 212, 1212, '100*5', 12, 16.8, 2250, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 16.8, 36960, 2250, NULL, 114030625, '1', 115171000, 114031000, '1', 115171000, 114031000, '1', 115171000, 'gi_sheet', 1),
(68, 'MT-125-2020-01-01--124811', 1231, 12, 200, 'wall_mount', 'yes', 12313, 132, 515, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 7.96463, 387.399, 352533, 305.842, 278316, 223.01, 202939, '7032', 51.6765, 8800, 'hl_030', 2632, 1184400, 'bnl_22', 654, 58860, 121, 605000, 131, 851.5, 1321, 145310, '33*33', 564, 225600, 2000, 500, 4000, '20*5', 123, 36.9, 7933.5, '25*5', 132, 52.8, 9504, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 89.7, 197340, 17437.5, NULL, 2802684, '1', 2755750, 2728470, '1', 2830710, 2653090, '1', 2679620, 'ss_sheet', 1),
(69, 'MT-2728-2022-01-01--110809', 800, 450, 200, 'wall_mount', 'yes', 800, 450, 200, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'no', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 0.646812, 22.5006, 20475.5, 17.7636, 16164.9, 12.9526, 11786.9, '7032', 1255.5, 2000, 'hl_027', 2, 600, 'ms_480', 2, 2200, 0.5, 2500, 25, 162.5, 25, 2750, '25*25', 2, 540, 4000, 4000, 4000, '25*5', 25, 10, 1800, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10, 22000, 1800, NULL, 68284, '1.1', 64676.6, 63972.9, '1.1', 69034.6, 59594.9, '1.1', 60250.4, 'ss_sheet', 1),
(70, 'MT-2731-2022-02-08--040344', 850, 850, 750, 'floor_mount', 'yes', 850, 850, 750, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ss_sheet', 3.29874, 160.451, 146010, 126.672, 115271, 92.3648, 84052, '7032', 5346.64, 2000, 'hl_027', 2, 600, 'ms_408', 1, 550, 2, 10000, 2, 13, 6, 660, '40*40', 1, 530, 4000, 1000, 2000, '50*5', 5, 3.5, 562.5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3.5, 7700, 562.5, NULL, 180972, '1', 151736, 150233, '1', 182782, 119014, '1', 120204, 'ss_sheet', 1),
(75, 'SR-2296-2022-01-01--023219', 2200, 1800, 150, 'floor_mount', 'yes', 1200, 256, 584, 0, 0, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ms_sheet', 1.79742, 87.4265, 21856.6, 69.021, 17255.2, 50.3278, 12581.9, '7032', 13810.5, 4541, 'hl_027', 121, 36300, 'ms_408', 564, 310200, 123, 615000, 321, 2086.5, 5, 550, '25*40', 45, 16650, 121, 1231, 1215, '40*10', 2200, 2420, 247500, '20*5', 2266, 679.8, 146157, '100*5', 5400, 7560, 1012500, '30*5', 250, 112.5, 22500, '20*5', 450, 135, 29025, '25*10', 540, 378, 44550, '30*5', 1005, 452.25, 90450, '30*5', 12004, 5401.8, 1080360, '30*5', 250, 112.5, 22500, '120*10', 540, 1782, 125550, '120*5', 454, 758.18, 98745, '80*5', 450, 495, 74250, '60*5', 12454, 10585.9, 1587880, '80*5', 154, 169.4, 25410, '25*10', 1417, 991.9, 116902, 32034.2, 70475300, 4724280, NULL, 76223152, '1', 76980700, 76218600, '1', 76985400, 76213900, '1', 76976000, 'ss_sheet', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
