-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2019 at 03:49 PM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.2.13

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
-- Table structure for table `reversedallocationhistory`
--

CREATE TABLE `reversedallocationhistory` (
  `id` int(11) NOT NULL,
  `transno` int(11) NOT NULL,
  `client` text NOT NULL,
  `chequeDate` datetime NOT NULL,
  `revDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chequeno` text NOT NULL,
  `amount` double NOT NULL,
  `reason` text NOT NULL,
  `chequefilepath` text NOT NULL,
  `chequedepositfilepath` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reversedallocationhistory`
--
ALTER TABLE `reversedallocationhistory`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reversedallocationhistory`
--
ALTER TABLE `reversedallocationhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
