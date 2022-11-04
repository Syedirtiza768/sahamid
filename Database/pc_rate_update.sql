-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2022 at 08:17 AM
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
-- Table structure for table `pc_rate_update`
--

DROP TABLE IF EXISTS `pc_rate_update`;
CREATE TABLE `pc_rate_update` (
  `id` int(11) NOT NULL,
  `value_name` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `updated_value` float NOT NULL,
  `updated_date` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pc_rate_update`
--

INSERT INTO `pc_rate_update` (`id`, `value_name`, `user`, `updated_value`, `updated_date`) VALUES
(107, 'ms_sheet ', 'Demonstration user', 2390, '04-11-22 12:16:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pc_rate_update`
--
ALTER TABLE `pc_rate_update`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pc_rate_update`
--
ALTER TABLE `pc_rate_update`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
