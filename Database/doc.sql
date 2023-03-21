-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2023 at 01:52 PM
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
-- Table structure for table `doc`
--

DROP TABLE IF EXISTS `doc`;
CREATE TABLE `doc` (
  `id` int(11) NOT NULL,
  `d_name` varchar(255) NOT NULL,
  `d_number` varchar(11) NOT NULL,
  `d_revision` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `word` varchar(255) DEFAULT NULL,
  `excel` varchar(255) DEFAULT NULL,
  `ppt` varchar(255) DEFAULT NULL,
  `pdf_version` int(11) DEFAULT NULL,
  `excel_version` int(11) DEFAULT NULL,
  `word_version` int(11) DEFAULT NULL,
  `ppt_version` int(11) DEFAULT NULL,
  `del_pdf` varchar(255) DEFAULT NULL,
  `del_excel` varchar(255) DEFAULT NULL,
  `del_word` varchar(255) DEFAULT NULL,
  `del_ppt` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `d_date` date DEFAULT NULL,
  `del_version` varchar(255) DEFAULT NULL,
  `del_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `doc`
--
ALTER TABLE `doc`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `doc`
--
ALTER TABLE `doc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
