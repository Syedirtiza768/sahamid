-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2017 at 10:37 AM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sahamidtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `salesorderdetailsip`
--

CREATE TABLE `salesorderdetailsip` (
  `salesorderdetailsindex` int(11) NOT NULL,
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double(20,2) NOT NULL DEFAULT '0.00',
  `quantity` double NOT NULL DEFAULT '0',
  `estimate` tinyint(4) NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` text,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `salesorderlinesip`
--

CREATE TABLE `salesorderlinesip` (
  `lineindex` int(11) NOT NULL,
  `orderno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `salesorderoptionsip`
--

CREATE TABLE `salesorderoptionsip` (
  `optionindex` int(11) NOT NULL,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `salesordersip`
--

CREATE TABLE `salesordersip` (
  `orderno` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `existing` tinyint(1) NOT NULL,
  `eorderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` text,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT '1',
  `freightcost` double NOT NULL DEFAULT '0',
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `confirmeddate` date NOT NULL DEFAULT '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
  `quotation` tinyint(4) NOT NULL DEFAULT '0',
  `quotedate` date NOT NULL DEFAULT '0000-00-00',
  `poplaced` tinyint(4) NOT NULL DEFAULT '0',
  `salesperson` varchar(4) NOT NULL,
  `GSTadd` text NOT NULL,
  `services` tinyint(1) NOT NULL,
  `WHT` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `salesorderdetailsip`
--
ALTER TABLE `salesorderdetailsip`
  ADD PRIMARY KEY (`salesorderdetailsindex`),
  ADD KEY `OrderNo` (`orderno`),
  ADD KEY `StkCode` (`stkcode`),
  ADD KEY `Completed` (`completed`);

--
-- Indexes for table `salesorderlinesip`
--
ALTER TABLE `salesorderlinesip`
  ADD PRIMARY KEY (`lineindex`);

--
-- Indexes for table `salesorderoptionsip`
--
ALTER TABLE `salesorderoptionsip`
  ADD PRIMARY KEY (`optionindex`);

--
-- Indexes for table `salesordersip`
--
ALTER TABLE `salesordersip`
  ADD PRIMARY KEY (`orderno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `salesorderdetailsip`
--
ALTER TABLE `salesorderdetailsip`
  MODIFY `salesorderdetailsindex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=825;
--
-- AUTO_INCREMENT for table `salesorderlinesip`
--
ALTER TABLE `salesorderlinesip`
  MODIFY `lineindex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;
--
-- AUTO_INCREMENT for table `salesorderoptionsip`
--
ALTER TABLE `salesorderoptionsip`
  MODIFY `optionindex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=486;
--
-- AUTO_INCREMENT for table `salesordersip`
--
ALTER TABLE `salesordersip`
  MODIFY `orderno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
