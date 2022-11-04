-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2022 at 08:18 AM
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
-- Table structure for table `pc_rate`
--

DROP TABLE IF EXISTS `pc_rate`;
CREATE TABLE `pc_rate` (
  `id` int(11) NOT NULL,
  `ms_sheet` float DEFAULT NULL,
  `ss_sheet` float DEFAULT NULL,
  `gi_sheet` float DEFAULT NULL,
  `h_7032` float DEFAULT NULL,
  `h_7035` float DEFAULT NULL,
  `tf_7032` float DEFAULT NULL,
  `tf_7035` float DEFAULT NULL,
  `thf_7032` float DEFAULT NULL,
  `thf_7035` float DEFAULT NULL,
  `f_7032` float DEFAULT NULL,
  `f_7035` float DEFAULT NULL,
  `s_7032` float DEFAULT NULL,
  `s_7035` float DEFAULT NULL,
  `n_7032` float DEFAULT NULL,
  `n_7035` float DEFAULT NULL,
  `hl_030` float DEFAULT NULL,
  `hl_027` float DEFAULT NULL,
  `hl_056` float DEFAULT NULL,
  `hl_051` float DEFAULT NULL,
  `ms_480` float DEFAULT NULL,
  `ms_408` float DEFAULT NULL,
  `bnl_22` float DEFAULT NULL,
  `pl_130` float DEFAULT NULL,
  `pl_150` float DEFAULT NULL,
  `acrylic_sheet` float DEFAULT NULL,
  `gas_kit` float DEFAULT NULL,
  `i_bolt` float DEFAULT NULL,
  `bus_bar` float DEFAULT NULL,
  `tf_tf` float DEFAULT NULL,
  `tf_f` float DEFAULT NULL,
  `tt_tt` float DEFAULT NULL,
  `f_f` float DEFAULT NULL,
  `f_s` float DEFAULT NULL,
  `s_f` float DEFAULT NULL,
  `s_s` float DEFAULT NULL,
  `e_e` float DEFAULT NULL,
  `h_h` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pc_rate`
--

INSERT INTO `pc_rate` (`id`, `ms_sheet`, `ss_sheet`, `gi_sheet`, `h_7032`, `h_7035`, `tf_7032`, `tf_7035`, `thf_7032`, `thf_7035`, `f_7032`, `f_7035`, `s_7032`, `s_7035`, `n_7032`, `n_7035`, `hl_030`, `hl_027`, `hl_056`, `hl_051`, `ms_480`, `ms_408`, `bnl_22`, `pl_130`, `pl_150`, `acrylic_sheet`, `gas_kit`, `i_bolt`, `bus_bar`, `tf_tf`, `tf_f`, `tt_tt`, `f_f`, `f_s`, `s_f`, `s_s`, `e_e`, `h_h`) VALUES
(1, 2390, 900, 295, 325, 437.5, 350, 468.75, 437.5, 562.5, 500, 625, 562.5, 687.5, 625, 750, 450, 300, 350, 150, 1100, 550, 90, 130, 150, 5000, 6.5, 110, 2200, 270, 370, 400, 530, 580, 650, 750, 1000, 2000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pc_rate`
--
ALTER TABLE `pc_rate`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pc_rate`
--
ALTER TABLE `pc_rate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
