-- MariaDB dump 10.19  Distrib 10.4.19-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sahamid
-- ------------------------------------------------------
-- Server version	10.4.19-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accountgroups`
--

DROP TABLE IF EXISTS `accountgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT 0,
  `pandl` tinyint(4) NOT NULL DEFAULT 1,
  `sequenceintb` smallint(6) NOT NULL DEFAULT 0,
  `parentgroupname` varchar(30) NOT NULL,
  PRIMARY KEY (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  KEY `parentgroupname` (`parentgroupname`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accountsection`
--

DROP TABLE IF EXISTS `accountsection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT 0,
  `sectionname` text NOT NULL,
  PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `addperm`
--

DROP TABLE IF EXISTS `addperm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addperm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(255) DEFAULT NULL,
  `permission` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingadmin`
--

DROP TABLE IF EXISTS `advancedreportingadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingadmin` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingadnan_sattar`
--

DROP TABLE IF EXISTS `advancedreportingadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingadnan_sattar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingahmad_zaheer`
--

DROP TABLE IF EXISTS `advancedreportingahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingahmad_zaheer` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingali_imran`
--

DROP TABLE IF EXISTS `advancedreportingali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingali_imran` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingali_shabbar`
--

DROP TABLE IF EXISTS `advancedreportingali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingali_shabbar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingjamal`
--

DROP TABLE IF EXISTS `advancedreportingjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingjamal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingmuhammad_bilal`
--

DROP TABLE IF EXISTS `advancedreportingmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingmuhammad_bilal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `advancedreportingus_help`
--

DROP TABLE IF EXISTS `advancedreportingus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advancedreportingus_help` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`areacode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assetmanager`
--

DROP TABLE IF EXISTS `assetmanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(15) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT 0,
  `depn` double NOT NULL DEFAULT 0,
  `datepurchased` date DEFAULT NULL,
  `disposalvalue` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audittrail`
--

DROP TABLE IF EXISTS `audittrail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audittrail` (
  `transactiondate` datetime DEFAULT NULL,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text DEFAULT NULL,
  KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bankaccounts`
--

DROP TABLE IF EXISTS `bankaccounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL DEFAULT 0,
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  `importformat` varchar(10) NOT NULL DEFAULT '''''',
  PRIMARY KEY (`accountcode`),
  KEY `currcode` (`currcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bankaccountusers`
--

DROP TABLE IF EXISTS `bankaccountusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banktrans`
--

DROP TABLE IF EXISTS `banktrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `transno` bigint(20) NOT NULL DEFAULT 0,
  `bankact` varchar(20) NOT NULL DEFAULT '0',
  `ref` varchar(50) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT 0,
  `exrate` double NOT NULL DEFAULT 1 COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT 1 COMMENT 'Account currency to functional currency',
  `transdate` date DEFAULT NULL,
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  KEY `ref` (`ref`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=23385 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bazar_parchi`
--

DROP TABLE IF EXISTS `bazar_parchi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bazar_parchi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `transno` int(11) NOT NULL,
  `parchino` varchar(15) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `paid` double NOT NULL DEFAULT 0,
  `inprogress` tinyint(1) NOT NULL DEFAULT 1,
  `settled` tinyint(1) NOT NULL DEFAULT 0,
  `discarded` tinyint(1) NOT NULL DEFAULT 0,
  `returned` int(1) NOT NULL,
  `svid` varchar(10) NOT NULL,
  `temp_vendor` varchar(150) NOT NULL,
  `gstinvoice` varchar(20) NOT NULL DEFAULT 'none',
  `terms` varchar(250) NOT NULL,
  `payment_terms` varchar(1) NOT NULL DEFAULT 'G',
  `user_id` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `settled_at` datetime DEFAULT NULL,
  `discarded_at` datetime DEFAULT NULL,
  `settled_by` varchar(30) NOT NULL,
  `discarded_by` varchar(30) NOT NULL,
  `igp_created` tinyint(1) NOT NULL DEFAULT 0,
  `igp_id` int(11) DEFAULT NULL,
  `on_behalf_of` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parchino` (`parchino`)
) ENGINE=InnoDB AUTO_INCREMENT=13889 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bom`
--

DROP TABLE IF EXISTS `bom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom` (
  `parent` varchar(40) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT 0,
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date DEFAULT NULL,
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT 1,
  `autoissue` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bpcomments`
--

DROP TABLE IF EXISTS `bpcomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bpcomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parchino` varchar(30) NOT NULL,
  `comment` text NOT NULL,
  `hasAudio` tinyint(1) NOT NULL DEFAULT 0,
  `audioPath` varchar(100) NOT NULL,
  `author` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bpitems`
--

DROP TABLE IF EXISTS `bpitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bpitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parchino` varchar(15) NOT NULL,
  `stockid` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL,
  `quantity` float NOT NULL DEFAULT 0,
  `quantity_received` float NOT NULL DEFAULT 0,
  `listprice` double NOT NULL,
  `discount` double NOT NULL,
  `price` double NOT NULL DEFAULT 0,
  `comments` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27812 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bpitemupdates`
--

DROP TABLE IF EXISTS `bpitemupdates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bpitemupdates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(15) NOT NULL,
  `bpitemid` int(11) NOT NULL,
  `old_value` double NOT NULL,
  `new_value` double NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `obo` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60225 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bpledger`
--

DROP TABLE IF EXISTS `bpledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bpledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(2) NOT NULL DEFAULT 'IW',
  `parchino` varchar(30) NOT NULL,
  `amount` double NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `userid` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `branditemanalysisadmin`
--

DROP TABLE IF EXISTS `branditemanalysisadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branditemanalysisadmin` (
  `branditemanalysisindex` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `mnfCode` varchar(100) NOT NULL,
  `mnfpno` varchar(100) NOT NULL,
  `description` varchar(50) NOT NULL,
  `QuotationCount` int(11) NOT NULL,
  `OCCount` int(11) NOT NULL,
  `DCCount` int(11) NOT NULL,
  `QtyQuotation` int(11) NOT NULL,
  `QtyOC` int(11) NOT NULL,
  `QtyDC` int(11) NOT NULL,
  `AvgDiscountQ` double NOT NULL,
  `AvgDiscountO` double NOT NULL,
  `AvgDiscountD` double NOT NULL,
  `AvgPriceQ` double NOT NULL,
  `AvgPriceO` double NOT NULL,
  `AvgPriceD` double NOT NULL,
  `ListPrice` int(11) NOT NULL,
  `TotalOGPMTO` int(11) NOT NULL,
  `TotalOGPHO` int(11) NOT NULL,
  `QOH` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesadmin`
--

DROP TABLE IF EXISTS `brandwisesalesadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesadmin` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesadnan_sattar`
--

DROP TABLE IF EXISTS `brandwisesalesadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesadnan_sattar` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesahmad_zaheer`
--

DROP TABLE IF EXISTS `brandwisesalesahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesahmad_zaheer` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesali_shabbar`
--

DROP TABLE IF EXISTS `brandwisesalesali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesali_shabbar` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesjamal`
--

DROP TABLE IF EXISTS `brandwisesalesjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesjamal` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesmaaz_binzia`
--

DROP TABLE IF EXISTS `brandwisesalesmaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesmaaz_binzia` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesmuhammad_bilal`
--

DROP TABLE IF EXISTS `brandwisesalesmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesmuhammad_bilal` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brandwisesalesus_help`
--

DROP TABLE IF EXISTS `brandwisesalesus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brandwisesalesus_help` (
  `brandwisesalesindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL,
  `manufacturers_name` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bus_bar_sheet`
--

DROP TABLE IF EXISTS `bus_bar_sheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bus_bar_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pannel_id` int(11) NOT NULL,
  `costNo` int(11) NOT NULL,
  `20_5_foot_size` float NOT NULL DEFAULT 0,
  `20_5_sleeve_cost` float DEFAULT NULL,
  `20_5_factor` float DEFAULT NULL,
  `20_5_sleeve_total_cost` float DEFAULT NULL,
  `20_5_bbr_weight` float DEFAULT NULL,
  `25_5_foot_size` float NOT NULL,
  `25_5_sleeve_cost` float DEFAULT NULL,
  `25_5_factor` float DEFAULT NULL,
  `25_5_sleeve_total_cost` float DEFAULT NULL,
  `25_5_bbr_weight` float DEFAULT NULL,
  `25_10_foot_size` float NOT NULL,
  `25_10_sleeve_cost` float DEFAULT NULL,
  `25_10_factor` float DEFAULT NULL,
  `25_10_sleeve_total_cost` float DEFAULT NULL,
  `25_10_bbr_weight` float DEFAULT NULL,
  `30_5_foot_size` float NOT NULL,
  `30_5_sleeve_cost` float DEFAULT NULL,
  `30_5_factor` float DEFAULT NULL,
  `30_5_sleeve_total_cost` float DEFAULT NULL,
  `30_5_bbr_weight` float DEFAULT NULL,
  `30_10_foot_size` float NOT NULL,
  `30_10_sleeve_cost` float DEFAULT NULL,
  `30_10_factor` float DEFAULT NULL,
  `30_10_sleeve_total_cost` float DEFAULT NULL,
  `30_10_bbr_weight` float DEFAULT NULL,
  `40_5_foot_size` float NOT NULL,
  `40_5_sleeve_cost` float DEFAULT NULL,
  `40_5_factor` float DEFAULT NULL,
  `40_5_sleeve_total_cost` float DEFAULT NULL,
  `40_5_bbr_weight` float DEFAULT NULL,
  `40_10_foot_size` float NOT NULL,
  `40_10_sleeve_cost` float NOT NULL,
  `40_10_factor` float DEFAULT NULL,
  `40_10_sleeve_total_cost` float DEFAULT NULL,
  `40_10_bbr_weight` float DEFAULT NULL,
  `50_5_foot_size` float NOT NULL,
  `50_5_sleeve_cost` float DEFAULT NULL,
  `50_5_factor` float DEFAULT NULL,
  `50_5_sleeve_total_cost` float DEFAULT NULL,
  `50_5_bbr_weight` float DEFAULT NULL,
  `50_10_foot_size` float NOT NULL,
  `50_10_sleeve_cost` float DEFAULT NULL,
  `50_10_factor` float DEFAULT NULL,
  `50_10_sleeve_total_cost` float DEFAULT NULL,
  `50_10_bbr_weight` float DEFAULT NULL,
  `60_5_foot_size` float NOT NULL,
  `60_5_sleeve_cost` float DEFAULT NULL,
  `60_5_factor` float DEFAULT NULL,
  `60_5_sleeve_total_cost` float DEFAULT NULL,
  `60_5_bbr_weight` float DEFAULT NULL,
  `60_10_foot_size` float NOT NULL,
  `60_10_sleeve_cost` float DEFAULT NULL,
  `60_10_factor` float DEFAULT NULL,
  `60_10_sleeve_total_cost` float DEFAULT NULL,
  `60_10_bbr_weight` float DEFAULT NULL,
  `80_5_foot_size` float NOT NULL,
  `80_5_sleeve_cost` float DEFAULT NULL,
  `80_5_factor` float DEFAULT NULL,
  `80_5_sleeve_total_cost` float DEFAULT NULL,
  `80_5_bbr_weight` float DEFAULT NULL,
  `80_10_foot_size` float NOT NULL,
  `80_10_sleeve_cost` float DEFAULT NULL,
  `80_10_factor` float DEFAULT NULL,
  `80_10_sleeve_total_cost` float DEFAULT NULL,
  `80_10_bbr_weight` float DEFAULT NULL,
  `100_5_foot_size` float NOT NULL,
  `100_5_sleeve_cost` float DEFAULT NULL,
  `100_5_factor` float DEFAULT NULL,
  `100_5_sleeve_total_cost` float DEFAULT NULL,
  `100_5_bbr_weight` float DEFAULT NULL,
  `100_10_foot_size` float NOT NULL,
  `100_10_sleeve_cost` float DEFAULT NULL,
  `100_10_factor` float DEFAULT NULL,
  `100_10_sleeve_total_cost` float DEFAULT NULL,
  `100_10_bbr_weight` float DEFAULT NULL,
  `120_5_foot_size` float NOT NULL,
  `120_5_sleeve_cost` float DEFAULT NULL,
  `120_5_factor` float DEFAULT NULL,
  `120_5_sleeve_total_cost` float DEFAULT NULL,
  `120_5_bbr_weight` float DEFAULT NULL,
  `120_10_foot_size` float NOT NULL,
  `120_10_sleeve_cost` float DEFAULT NULL,
  `120_10_factor` float DEFAULT NULL,
  `120_10_sleeve_total_cost` float DEFAULT NULL,
  `120_10_bbr_weight` float DEFAULT NULL,
  `150_10_foot_size` float NOT NULL,
  `150_10_sleeve_cost` float DEFAULT NULL,
  `150_10_factor` float DEFAULT NULL,
  `150_10_sleeve_total_cost` float DEFAULT NULL,
  `150_10_bbr_weight` float DEFAULT NULL,
  `bus_bar_weight` float DEFAULT NULL,
  `bus_bar_cost` float DEFAULT NULL,
  `tin_cost` float DEFAULT NULL,
  `sleeve_cost` float DEFAULT NULL,
  `bus_bar_price` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_key` varchar(200) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `refreshed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`unique_key`)
) ENGINE=MyISAM AUTO_INCREMENT=17751 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cart_report_access`
--

DROP TABLE IF EXISTS `cart_report_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_report_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `can_access` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `caseclosereasonsenquiry`
--

DROP TABLE IF EXISTS `caseclosereasonsenquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caseclosereasonsenquiry` (
  `casecloseatenquiryindex` int(11) NOT NULL AUTO_INCREMENT,
  `caseclosereasonspoid` smallint(6) NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`casecloseatenquiryindex`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `caseclosereasonspo`
--

DROP TABLE IF EXISTS `caseclosereasonspo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caseclosereasonspo` (
  `caseclosereasonspoid` smallint(6) NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `caseclosereasonsquotation`
--

DROP TABLE IF EXISTS `caseclosereasonsquotation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caseclosereasonsquotation` (
  `caseclosereasonsquotationid` smallint(6) NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`caseclosereasonsquotationid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cashdrawer_payments`
--

DROP TABLE IF EXISTS `cashdrawer_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cashdrawer_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch` int(11) NOT NULL DEFAULT 1,
  `orderno` int(11) NOT NULL,
  `received` tinyint(1) NOT NULL,
  `received_at` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=931 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(30) NOT NULL,
  `cat_code` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category_perm`
--

DROP TABLE IF EXISTS `category_perm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_perm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `cat_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cgassignments`
--

DROP TABLE IF EXISTS `cgassignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cgassignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgid` int(11) NOT NULL,
  `salesman` varchar(4) NOT NULL,
  `target` int(11) NOT NULL,
  `year` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cgdetails`
--

DROP TABLE IF EXISTS `cgdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cgdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgid` int(11) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2195 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chartdetails`
--

DROP TABLE IF EXISTS `chartdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartdetails` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL DEFAULT 0,
  `budget` double NOT NULL DEFAULT 0,
  `actual` double NOT NULL DEFAULT 0,
  `bfwd` double NOT NULL DEFAULT 0,
  `bfwdbudget` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chartmaster`
--

DROP TABLE IF EXISTS `chartmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cogsglpostings`
--

DROP TABLE IF EXISTS `cogsglpostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL DEFAULT 1,
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `gstno` varchar(20) NOT NULL DEFAULT '',
  `companynumber` varchar(20) NOT NULL DEFAULT '0',
  `regoffice1` varchar(40) NOT NULL DEFAULT '',
  `regoffice2` varchar(40) NOT NULL DEFAULT '',
  `regoffice3` varchar(40) NOT NULL DEFAULT '',
  `regoffice4` varchar(40) NOT NULL DEFAULT '',
  `regoffice5` varchar(20) NOT NULL DEFAULT '',
  `regoffice6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `currencydefault` varchar(4) NOT NULL DEFAULT '',
  `debtorsact` varchar(20) NOT NULL DEFAULT '70000',
  `pytdiscountact` varchar(20) NOT NULL DEFAULT '55000',
  `creditorsact` varchar(20) NOT NULL DEFAULT '80000',
  `payrollact` varchar(20) NOT NULL DEFAULT '84000',
  `grnact` varchar(20) NOT NULL DEFAULT '72000',
  `exchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` varchar(20) NOT NULL DEFAULT '0',
  `retainedearnings` varchar(20) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT 1,
  `gllink_creditors` tinyint(1) DEFAULT 1,
  `gllink_stock` tinyint(1) DEFAULT 1,
  `freightact` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`coycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL,
  PRIMARY KEY (`confname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractbom`
--

DROP TABLE IF EXISTS `contractbom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 1,
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractcharges`
--

DROP TABLE IF EXISTS `contractcharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT 20,
  `transno` int(11) NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `contractref` (`contractref`,`transtype`,`transno`),
  KEY `contractcharges_ibfk_2` (`transtype`),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractreqts`
--

DROP TABLE IF EXISTS `contractreqts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 1,
  `costperunit` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT 0,
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT 1,
  `wo` int(11) NOT NULL DEFAULT 0,
  `requireddate` date DEFAULT NULL,
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT 1,
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cost_sheet`
--

DROP TABLE IF EXISTS `cost_sheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pannel_id` int(11) DEFAULT NULL,
  `costNo` int(11) NOT NULL,
  `pl_model` float DEFAULT NULL,
  `pl_mf` float DEFAULT NULL,
  `pl_qty` float DEFAULT NULL,
  `pl_cost` float DEFAULT NULL,
  `h_model` float DEFAULT NULL,
  `h_mf` float DEFAULT NULL,
  `h_qty` float DEFAULT NULL,
  `h_cost` float DEFAULT NULL,
  `as_qty` float DEFAULT NULL,
  `as_uc` float DEFAULT NULL,
  `as_cost` float DEFAULT NULL,
  `i_uc` float DEFAULT NULL,
  `gk_uc` float DEFAULT NULL,
  `gk_qty` float DEFAULT NULL,
  `gk_cost` float DEFAULT NULL,
  `i_qty` float DEFAULT NULL,
  `i_cost` float DEFAULT NULL,
  `cd_mf` float DEFAULT NULL,
  `cd_model` float DEFAULT NULL,
  `cd_qty` float DEFAULT NULL,
  `cd_cost` float DEFAULT NULL,
  `pc_mf` float DEFAULT NULL,
  `pc_model` float DEFAULT NULL,
  `pc_cost` float DEFAULT NULL,
  `polish` float DEFAULT NULL,
  `rent` float DEFAULT NULL,
  `wiring` float DEFAULT NULL,
  `labour` float DEFAULT NULL,
  `misc_exp` float DEFAULT NULL,
  `cost_12_SWG` float DEFAULT NULL,
  `percent_12_SWG` float DEFAULT NULL,
  `percent_price_12_SWG` float DEFAULT NULL,
  `cost_14_SWG` float DEFAULT NULL,
  `percent_14_SWG` float DEFAULT NULL,
  `percent_price_14_SWG` float DEFAULT NULL,
  `cost_16_SWG` float DEFAULT NULL,
  `percent_16_SWG` float DEFAULT NULL,
  `percent_price_16_SWG` float DEFAULT NULL,
  `cost_18_SWG` float DEFAULT NULL,
  `percent_18_SWG` float DEFAULT NULL,
  `percent_price_18_SWG` float DEFAULT NULL,
  `cost_20_SWG` float DEFAULT NULL,
  `percent_20_SWG` float DEFAULT NULL,
  `percent_price_20_SWG` float DEFAULT NULL,
  `cost_in_mult_gauge` float DEFAULT NULL,
  `increase_percent` float DEFAULT NULL,
  `percentage_price` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint(3) NOT NULL DEFAULT 2,
  `rate` double NOT NULL DEFAULT 1,
  `webcart` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'If 1 shown in weberp cart. if 0 no show',
  PRIMARY KEY (`currabrev`),
  KEY `Country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custallocns`
--

DROP TABLE IF EXISTS `custallocns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `WHT` int(11) NOT NULL,
  `GSTwithhold` int(11) NOT NULL,
  `datealloc` date NOT NULL,
  `transid_allocfrom` int(11) NOT NULL DEFAULT 0,
  `transid_allocto` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`)
) ENGINE=InnoDB AUTO_INCREMENT=22817 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custbranch`
--

DROP TABLE IF EXISTS `custbranch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(40) NOT NULL DEFAULT '',
  `lat` float(10,6) NOT NULL DEFAULT 0.000000,
  `lng` float(10,6) NOT NULL DEFAULT 0.000000,
  `estdeliverydays` smallint(6) NOT NULL DEFAULT 1,
  `area` char(3) NOT NULL,
  `salesman` varchar(4) NOT NULL DEFAULT '',
  `fwddate` smallint(6) NOT NULL DEFAULT 0,
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 1,
  `defaultshipvia` int(11) NOT NULL DEFAULT 1,
  `deliverblind` tinyint(1) DEFAULT 1,
  `disabletrans` tinyint(4) NOT NULL DEFAULT 0,
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(40) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custcontacts`
--

DROP TABLE IF EXISTS `custcontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL,
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB AUTO_INCREMENT=3326 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custitem`
--

DROP TABLE IF EXISTS `custitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT 1,
  PRIMARY KEY (`debtorno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `Debtorno` (`debtorno`),
  CONSTRAINT ` custitem _ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT ` custitem _ibfk_2` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custnotes`
--

DROP TABLE IF EXISTS `custnotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date DEFAULT NULL,
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customergroups`
--

DROP TABLE IF EXISTS `customergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2127 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesadmin`
--

DROP TABLE IF EXISTS `customerwisesalesadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesadmin` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesadnan_sattar`
--

DROP TABLE IF EXISTS `customerwisesalesadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesadnan_sattar` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesahmad_zaheer`
--

DROP TABLE IF EXISTS `customerwisesalesahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesahmad_zaheer` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesali_imran`
--

DROP TABLE IF EXISTS `customerwisesalesali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesali_imran` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesali_shabbar`
--

DROP TABLE IF EXISTS `customerwisesalesali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesali_shabbar` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesjamal`
--

DROP TABLE IF EXISTS `customerwisesalesjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesjamal` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerwisesalesmuhammad_bilal`
--

DROP TABLE IF EXISTS `customerwisesalesmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerwisesalesmuhammad_bilal` (
  `customerwisesalesindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dashboards`
--

DROP TABLE IF EXISTS `dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dashboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT 'Dashboard',
  `description` varchar(400) NOT NULL DEFAULT '',
  `username` varchar(50) NOT NULL,
  `badges` text NOT NULL,
  `widgets` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dba`
--

DROP TABLE IF EXISTS `dba`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dba` (
  `dbaid` int(11) NOT NULL AUTO_INCREMENT,
  `companyname` varchar(100) NOT NULL,
  `companyaddress` varchar(300) NOT NULL,
  PRIMARY KEY (`dbaid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dc`
--

DROP TABLE IF EXISTS `dc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dc` (
  `dispatchid` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) NOT NULL,
  `contid` int(11) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `despatchdate` date DEFAULT NULL,
  `narrative` text DEFAULT NULL,
  `salesperson` varchar(35) NOT NULL,
  `po` varchar(40) NOT NULL,
  `ref` varchar(40) NOT NULL,
  `dba` varchar(100) NOT NULL,
  `debtorno` varchar(10) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `dbaaddress` varchar(300) NOT NULL,
  `deliverto` varchar(300) NOT NULL,
  `dcstatus` varchar(50) NOT NULL,
  `dccomplete` varchar(50) NOT NULL,
  `podate` varchar(100) NOT NULL,
  `invoicedate` datetime NOT NULL,
  `courierslipdate` datetime NOT NULL,
  `grbdate` datetime NOT NULL,
  PRIMARY KEY (`dispatchid`),
  KEY `loccode` (`loccode`),
  KEY `salescaseref` (`salescaseref`)
) ENGINE=InnoDB AUTO_INCREMENT=38602 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcclient`
--

DROP TABLE IF EXISTS `dcclient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcclient` (
  `dispatchid` int(11) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `despatchdate` date DEFAULT NULL,
  `narrative` text DEFAULT NULL,
  `salesperson` varchar(35) NOT NULL,
  `po` varchar(40) NOT NULL,
  `ref` varchar(40) NOT NULL,
  `dba` varchar(100) NOT NULL,
  `dbaaddress` varchar(300) NOT NULL,
  `deliverto` varchar(300) NOT NULL,
  `gstclause` varchar(30) DEFAULT NULL,
  `dctype` varchar(30) NOT NULL DEFAULT 'Delivery Challan',
  `advance` int(11) NOT NULL,
  PRIMARY KEY (`dispatchid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcclientitems`
--

DROP TABLE IF EXISTS `dcclientitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcclientitems` (
  `dcclientitemsid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `quantity` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `rate` varchar(30) DEFAULT NULL,
  `dispatchid` int(11) NOT NULL,
  `dispatchitemsid` int(11) NOT NULL,
  `linetotal` int(11) NOT NULL,
  PRIMARY KEY (`dcclientitemsid`)
) ENGINE=InnoDB AUTO_INCREMENT=10238 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcdetails`
--

DROP TABLE IF EXISTS `dcdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcdetails` (
  `dcdetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `quantityoc` double NOT NULL,
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`dcdetailsindex`),
  KEY `orderno` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=64548 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcgroups`
--

DROP TABLE IF EXISTS `dcgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dcnos` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14157 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcitems`
--

DROP TABLE IF EXISTS `dcitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcitems` (
  `dcitemsindex` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `comments` varchar(200) NOT NULL,
  `rate` double NOT NULL,
  `brand` varchar(32) NOT NULL,
  `mnfCode` varchar(40) NOT NULL,
  `dispatchid` int(11) NOT NULL,
  `dispatchitemsid` int(11) NOT NULL,
  PRIMARY KEY (`dcitemsindex`),
  KEY `stockid` (`stockid`),
  KEY `stockid_2` (`stockid`)
) ENGINE=InnoDB AUTO_INCREMENT=10657 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dclines`
--

DROP TABLE IF EXISTS `dclines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dclines` (
  `lineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL,
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`lineindex`)
) ENGINE=InnoDB AUTO_INCREMENT=45703 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcoptions`
--

DROP TABLE IF EXISTS `dcoptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcoptions` (
  `optionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optionno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` double NOT NULL,
  `qtyinvoiced` int(11) NOT NULL,
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  `optprice` double NOT NULL,
  PRIMARY KEY (`optionindex`)
) ENGINE=InnoDB AUTO_INCREMENT=45703 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcs`
--

DROP TABLE IF EXISTS `dcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcs` (
  `dcindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date DEFAULT NULL,
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `confirmeddate` date DEFAULT NULL,
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date DEFAULT NULL,
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `quotedate` date DEFAULT NULL,
  `poplaced` tinyint(4) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL,
  `contactperson` varchar(40) NOT NULL DEFAULT '',
  `dcstatus` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `podeleted` tinyint(1) NOT NULL,
  `invoicedate` datetime NOT NULL,
  `invoicedeleted` tinyint(1) NOT NULL,
  `courierslipdate` datetime NOT NULL,
  `courierslipdeleted` tinyint(1) NOT NULL,
  `grbdate` datetime NOT NULL,
  `grbdeleted` tinyint(1) NOT NULL,
  `dccomplete` varchar(50) NOT NULL,
  `inprogress` tinyint(1) NOT NULL DEFAULT 1,
  `GSTAdd` varchar(60) NOT NULL,
  `services` tinyint(1) NOT NULL,
  `invoicegroupid` int(11) DEFAULT NULL,
  `shop` tinyint(1) NOT NULL DEFAULT 0,
  `mp` tinyint(1) NOT NULL DEFAULT 0,
  `dispatchthrough` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`dcindex`)
) ENGINE=InnoDB AUTO_INCREMENT=18209 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvalueadmin`
--

DROP TABLE IF EXISTS `dcvalueadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvalueadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvalueadnan_sattar`
--

DROP TABLE IF EXISTS `dcvalueadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvalueadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvalueahmad_zaheer`
--

DROP TABLE IF EXISTS `dcvalueahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvalueahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvalueali_shabbar`
--

DROP TABLE IF EXISTS `dcvalueali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvalueali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvalueammar_hafeez`
--

DROP TABLE IF EXISTS `dcvalueammar_hafeez`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvalueammar_hafeez` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandadmin`
--

DROP TABLE IF EXISTS `dcvaluebrandadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandadnan_sattar`
--

DROP TABLE IF EXISTS `dcvaluebrandadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandahmad_zaheer`
--

DROP TABLE IF EXISTS `dcvaluebrandahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandali_shabbar`
--

DROP TABLE IF EXISTS `dcvaluebrandali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandjamal`
--

DROP TABLE IF EXISTS `dcvaluebrandjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandmaaz_binzia`
--

DROP TABLE IF EXISTS `dcvaluebrandmaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandmaaz_binzia` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandmuhammad_bilal`
--

DROP TABLE IF EXISTS `dcvaluebrandmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluebrandus_help`
--

DROP TABLE IF EXISTS `dcvaluebrandus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluebrandus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomeradmin`
--

DROP TABLE IF EXISTS `dcvaluecustomeradmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomeradmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomeradnan_sattar`
--

DROP TABLE IF EXISTS `dcvaluecustomeradnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomeradnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomerahmad_zaheer`
--

DROP TABLE IF EXISTS `dcvaluecustomerahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomerahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomerali_imran`
--

DROP TABLE IF EXISTS `dcvaluecustomerali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomerali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomerali_shabbar`
--

DROP TABLE IF EXISTS `dcvaluecustomerali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomerali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomerjamal`
--

DROP TABLE IF EXISTS `dcvaluecustomerjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomerjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluecustomermuhammad_bilal`
--

DROP TABLE IF EXISTS `dcvaluecustomermuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluecustomermuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluejamal`
--

DROP TABLE IF EXISTS `dcvaluejamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluejamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluemaaz_binzia`
--

DROP TABLE IF EXISTS `dcvaluemaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluemaaz_binzia` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluemuhammad_arif`
--

DROP TABLE IF EXISTS `dcvaluemuhammad_arif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluemuhammad_arif` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluemuhammad_bilal`
--

DROP TABLE IF EXISTS `dcvaluemuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluemuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsadmin`
--

DROP TABLE IF EXISTS `dcvaluereportsadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsadnan_sattar`
--

DROP TABLE IF EXISTS `dcvaluereportsadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsahmad_zaheer`
--

DROP TABLE IF EXISTS `dcvaluereportsahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsali_imran`
--

DROP TABLE IF EXISTS `dcvaluereportsali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsali_shabbar`
--

DROP TABLE IF EXISTS `dcvaluereportsali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsjamal`
--

DROP TABLE IF EXISTS `dcvaluereportsjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsmuhammad_bilal`
--

DROP TABLE IF EXISTS `dcvaluereportsmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluereportsus_help`
--

DROP TABLE IF EXISTS `dcvaluereportsus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluereportsus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesajjad_ahmed`
--

DROP TABLE IF EXISTS `dcvaluesajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesajjad_ahmed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonadmin`
--

DROP TABLE IF EXISTS `dcvaluesalespersonadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonadnan_sattar`
--

DROP TABLE IF EXISTS `dcvaluesalespersonadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonahmad_zaheer`
--

DROP TABLE IF EXISTS `dcvaluesalespersonahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonali_imran`
--

DROP TABLE IF EXISTS `dcvaluesalespersonali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonali_shabbar`
--

DROP TABLE IF EXISTS `dcvaluesalespersonali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonjamal`
--

DROP TABLE IF EXISTS `dcvaluesalespersonjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonmuhammad_bilal`
--

DROP TABLE IF EXISTS `dcvaluesalespersonmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonsajjad_ahmed`
--

DROP TABLE IF EXISTS `dcvaluesalespersonsajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonsajjad_ahmed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluesalespersonus_help`
--

DROP TABLE IF EXISTS `dcvaluesalespersonus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluesalespersonus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dcvaluetechadmin`
--

DROP TABLE IF EXISTS `dcvaluetechadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dcvaluetechadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtorsmaster`
--

DROP TABLE IF EXISTS `debtorsmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL,
  `holdreason` smallint(6) NOT NULL DEFAULT 0,
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT 0,
  `pymtdiscount` double NOT NULL DEFAULT 0,
  `lastpaid` double NOT NULL DEFAULT 0,
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT 1000,
  `invaddrbranch` tinyint(4) NOT NULL DEFAULT 0,
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint(4) NOT NULL DEFAULT 0,
  `ediorders` tinyint(4) NOT NULL DEFAULT 0,
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT 0,
  `typeid` tinyint(4) NOT NULL DEFAULT 1,
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `dba` varchar(100) NOT NULL,
  `dueDays` int(4) NOT NULL DEFAULT 30,
  `paymentExpected` int(11) DEFAULT 30,
  `disabletrans` int(1) DEFAULT 0,
  `ntn` text NOT NULL,
  `gst` text NOT NULL,
  `salestaxinvoiceaddress` text NOT NULL,
  `disableshopdc` int(1) DEFAULT 0,
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`),
  KEY `debtorno` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortrans`
--

DROP TABLE IF EXISTS `debtortrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL,
  `inputdate` datetime NOT NULL,
  `prd` smallint(6) NOT NULL DEFAULT 0,
  `settled` tinyint(4) NOT NULL DEFAULT 0,
  `reference` varchar(20) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int(11) NOT NULL DEFAULT 0,
  `rate` double NOT NULL DEFAULT 0,
  `ovamount` double NOT NULL DEFAULT 0,
  `ovgst` double NOT NULL DEFAULT 0,
  `ovfreight` double NOT NULL DEFAULT 0,
  `ovdiscount` double NOT NULL DEFAULT 0,
  `diffonexch` double NOT NULL DEFAULT 0,
  `alloc` double NOT NULL DEFAULT 0,
  `invtext` text DEFAULT NULL,
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `edisent` tinyint(4) NOT NULL DEFAULT 0,
  `consignment` varchar(20) NOT NULL DEFAULT '',
  `packages` int(11) NOT NULL DEFAULT 1 COMMENT 'number of cartons',
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `WHT` double NOT NULL,
  `GSTwithhold` double NOT NULL,
  `WHTamt` double NOT NULL,
  `GSTamt` double NOT NULL,
  `GSTtotalamt` double NOT NULL,
  `chequefilepath` varchar(150) NOT NULL,
  `chequedepositfilepath` varchar(150) NOT NULL,
  `cashfilepath` varchar(150) NOT NULL,
  `bankaccount` int(11) NOT NULL,
  `processed` int(11) NOT NULL DEFAULT -1,
  `reversed` tinyint(1) NOT NULL DEFAULT 0,
  `state` varchar(200) NOT NULL DEFAULT '',
  `reverseHistory` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  KEY `salesperson` (`salesperson`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=31122 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortranstaxes`
--

DROP TABLE IF EXISTS `debtortranstaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxamount` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortype`
--

DROP TABLE IF EXISTS `debtortype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortypenotes`
--

DROP TABLE IF EXISTS `debtortypenotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(4) NOT NULL DEFAULT 0,
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date DEFAULT NULL,
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deliverynotes`
--

DROP TABLE IF EXISTS `deliverynotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `printed` tinyint(4) NOT NULL DEFAULT 0,
  `invoiced` tinyint(4) NOT NULL DEFAULT 0,
  `deliverydate` date DEFAULT NULL,
  PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
  KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `departmentid` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`departmentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `discountmatrix`
--

DROP TABLE IF EXISTS `discountmatrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT 1,
  `discountrate` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `doc`
--

DROP TABLE IF EXISTS `doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `d_name` varchar(255) NOT NULL,
  `d_number` varchar(11) NOT NULL,
  `d_revision` varchar(255) DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `word` varchar(255) DEFAULT NULL,
  `excel` varchar(255) DEFAULT NULL,
  `pdf_version` int(11) DEFAULT NULL,
  `excel_version` int(11) DEFAULT NULL,
  `word_version` int(11) DEFAULT NULL,
  `del_pdf` varchar(255) DEFAULT NULL,
  `del_excel` varchar(255) DEFAULT NULL,
  `del_word` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `d_date` date DEFAULT NULL,
  `del_version` varchar(255) DEFAULT NULL,
  `del_date` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edi_orders_seg_groups`
--

DROP TABLE IF EXISTS `edi_orders_seg_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT 0,
  `maxoccur` int(4) NOT NULL DEFAULT 0,
  `parentseggroup` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edi_orders_segs`
--

DROP TABLE IF EXISTS `edi_orders_segs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT 0,
  `maxoccur` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ediitemmapping`
--

DROP TABLE IF EXISTS `ediitemmapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edimessageformat`
--

DROP TABLE IF EXISTS `edimessageformat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT 0,
  `linetext` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `editperm`
--

DROP TABLE IF EXISTS `editperm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `editperm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(255) NOT NULL,
  `permission` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emailsettings`
--

DROP TABLE IF EXISTS `emailsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT 5,
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exchange_rate`
--

DROP TABLE IF EXISTS `exchange_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exchange_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aed` float NOT NULL DEFAULT 0,
  `usd` float NOT NULL DEFAULT 0,
  `euro` float NOT NULL DEFAULT 0,
  `pound` float NOT NULL DEFAULT 0,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expense_listing_access`
--

DROP TABLE IF EXISTS `expense_listing_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_listing_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `can_access` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `factorcompanies`
--

DROP TABLE IF EXISTS `factorcompanies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `contact` varchar(25) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassetcategories`
--

DROP TABLE IF EXISTS `fixedassetcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` varchar(20) NOT NULL DEFAULT '0',
  `depnact` varchar(20) NOT NULL DEFAULT '0',
  `disposalact` varchar(20) NOT NULL DEFAULT '80000',
  `accumdepnact` varchar(20) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT 0.2,
  `defaultdepntype` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassetlocations`
--

DROP TABLE IF EXISTS `fixedassetlocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassets`
--

DROP TABLE IF EXISTS `fixedassets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassets` (
  `assetid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT 0,
  `accumdepn` double NOT NULL DEFAULT 0,
  `datepurchased` date DEFAULT NULL,
  `disposalproceeds` double NOT NULL DEFAULT 0,
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int(11) NOT NULL DEFAULT 1,
  `depnrate` double NOT NULL,
  `disposaldate` date DEFAULT NULL,
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassettasks`
--

DROP TABLE IF EXISTS `fixedassettasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassettasks` (
  `taskid` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int(11) NOT NULL DEFAULT 365,
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`taskid`),
  KEY `assetid` (`assetid`),
  KEY `userresponsible` (`userresponsible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassettrans`
--

DROP TABLE IF EXISTS `fixedassettrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassettrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `transtype` tinyint(4) NOT NULL,
  `transdate` date NOT NULL,
  `transno` int(11) NOT NULL,
  `periodno` smallint(6) NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assetid` (`assetid`,`transtype`,`transno`),
  KEY `inputdate` (`inputdate`),
  KEY `transdate` (`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `freightcosts`
--

DROP TABLE IF EXISTS `freightcosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destinationcountry` varchar(40) NOT NULL,
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int(11) NOT NULL DEFAULT 0,
  `cubrate` double NOT NULL DEFAULT 0,
  `kgrate` double NOT NULL DEFAULT 0,
  `maxkgs` double NOT NULL DEFAULT 999999,
  `maxcub` double NOT NULL DEFAULT 999999,
  `fixedprice` double NOT NULL DEFAULT 0,
  `minimumchg` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geocode_param`
--

DROP TABLE IF EXISTS `geocode_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`geocodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gltrans`
--

DROP TABLE IF EXISTS `gltrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `typeno` bigint(16) NOT NULL DEFAULT 1,
  `chequeno` int(11) NOT NULL DEFAULT 0,
  `trandate` date DEFAULT NULL,
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT 0,
  `posted` tinyint(4) NOT NULL DEFAULT 0,
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `tag` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `Posted` (`posted`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`),
  KEY `tag` (`tag`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=246830 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grb`
--

DROP TABLE IF EXISTS `grb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grb` (
  `grbindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `dcno` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date DEFAULT NULL,
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `confirmeddate` date DEFAULT NULL,
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date DEFAULT NULL,
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `quotedate` date DEFAULT NULL,
  `poplaced` tinyint(4) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL,
  `contactperson` varchar(40) NOT NULL DEFAULT '',
  `dcstatus` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `invoicedate` datetime NOT NULL,
  `courierslipdate` datetime NOT NULL,
  `grbdate` datetime NOT NULL,
  `dccomplete` varchar(50) NOT NULL,
  `inprogress` tinyint(1) NOT NULL DEFAULT 1,
  `GSTAdd` varchar(60) NOT NULL,
  `services` tinyint(1) NOT NULL,
  `invoicegroupid` int(11) DEFAULT NULL,
  `shop` tinyint(1) NOT NULL DEFAULT 0,
  `mp` tinyint(1) NOT NULL DEFAULT 0,
  `dispatchthrough` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`grbindex`)
) ENGINE=InnoDB AUTO_INCREMENT=816 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grbdetails`
--

DROP TABLE IF EXISTS `grbdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grbdetails` (
  `grbdetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `quantityoc` double NOT NULL,
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`grbdetailsindex`),
  KEY `orderno` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=1375 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grbitems`
--

DROP TABLE IF EXISTS `grbitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grbitems` (
  `grbitemsindex` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `comments` varchar(200) NOT NULL,
  `rate` double NOT NULL,
  `brand` varchar(32) NOT NULL,
  `mnfCode` varchar(40) NOT NULL,
  `dispatchid` int(11) NOT NULL,
  `dispatchitemsid` int(11) NOT NULL,
  PRIMARY KEY (`grbitemsindex`),
  KEY `stockid` (`stockid`),
  KEY `stockid_2` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grblines`
--

DROP TABLE IF EXISTS `grblines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grblines` (
  `lineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL,
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`lineindex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grboptions`
--

DROP TABLE IF EXISTS `grboptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grboptions` (
  `optionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optionno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` int(11) NOT NULL,
  `qtyinvoiced` int(11) NOT NULL,
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  `optprice` double NOT NULL,
  PRIMARY KEY (`optionindex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grns`
--

DROP TABLE IF EXISTS `grns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL DEFAULT 0,
  `grnno` int(11) NOT NULL AUTO_INCREMENT,
  `podetailitem` int(11) NOT NULL DEFAULT 0,
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT 0,
  `quantityinv` double NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `holdreasons`
--

DROP TABLE IF EXISTS `holdreasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT 1,
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT -1,
  PRIMARY KEY (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `igp`
--

DROP TABLE IF EXISTS `igp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `igp` (
  `dispatchid` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(100) NOT NULL DEFAULT '',
  `reference` varchar(100) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int(11) NOT NULL DEFAULT 0,
  `despatchdate` date DEFAULT NULL,
  `authorised` tinyint(4) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` text NOT NULL,
  `storemanager` varchar(35) NOT NULL,
  `receivedfrom` varchar(100) NOT NULL,
  PRIMARY KEY (`dispatchid`),
  KEY `loccode` (`loccode`),
  KEY `departmentid` (`departmentid`),
  CONSTRAINT `igp_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=179550 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `igpitems`
--

DROP TABLE IF EXISTS `igpitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `igpitems` (
  `igpitemindex` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `comments` varchar(200) NOT NULL,
  `rate` double NOT NULL,
  `brand` varchar(32) NOT NULL,
  `mnfCode` varchar(40) NOT NULL,
  `dispatchitemsid` int(11) NOT NULL,
  `dispatchid` int(11) NOT NULL,
  `comment` varchar(100) NOT NULL,
  PRIMARY KEY (`igpitemindex`),
  KEY `stockid` (`stockid`),
  KEY `stockid_2` (`stockid`)
) ENGINE=InnoDB AUTO_INCREMENT=138755 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inbox`
--

DROP TABLE IF EXISTS `inbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `message` text NOT NULL,
  `messageStatus` tinyint(1) NOT NULL,
  `heading` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `createdBy` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39484 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internalstockcatrole`
--

DROP TABLE IF EXISTS `internalstockcatrole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internalstockcatrole` (
  `conditionID` int(11) NOT NULL,
  `secroleid` int(11) NOT NULL,
  PRIMARY KEY (`conditionID`,`secroleid`),
  KEY `internalstockcatrole_ibfk_1` (`conditionID`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_2` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_4` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `internalstockcatrole_ibfk_5` FOREIGN KEY (`conditionID`) REFERENCES `itemcondition` (`conditionID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `inverntoryprice`
--

DROP TABLE IF EXISTS `inverntoryprice`;
/*!50001 DROP VIEW IF EXISTS `inverntoryprice`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `inverntoryprice` (
  `brand` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `materialcost` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice` (
  `invoiceindex` int(11) NOT NULL AUTO_INCREMENT,
  `invoiceno` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `podate` date NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `invoicedate` date NOT NULL DEFAULT current_timestamp(),
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `gst` text NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `services` tinyint(1) DEFAULT NULL,
  `salesperson` varchar(4) NOT NULL,
  `inprogress` tinyint(1) NOT NULL DEFAULT 0,
  `shopinvoiceno` varchar(20) NOT NULL DEFAULT '',
  `invoicesdate` date NOT NULL,
  `due` date NOT NULL,
  `expected` date DEFAULT NULL,
  `returned` tinyint(1) NOT NULL DEFAULT 0,
  `comment` text NOT NULL,
  PRIMARY KEY (`invoiceindex`)
) ENGINE=InnoDB AUTO_INCREMENT=14702 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoicedetails`
--

DROP TABLE IF EXISTS `invoicedetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoicedetails` (
  `invoicedetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `invoicelineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `invoiceno` int(11) NOT NULL DEFAULT 0,
  `lineoptionno` int(11) NOT NULL,
  `invoiceoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `invoiceoptionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `invoiceinternalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `quantityoc` double NOT NULL,
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`invoicedetailsindex`)
) ENGINE=InnoDB AUTO_INCREMENT=50744 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoicelines`
--

DROP TABLE IF EXISTS `invoicelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoicelines` (
  `invoicelineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `invoiceno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `invoicelineno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL,
  `dispatchid` int(11) NOT NULL,
  PRIMARY KEY (`invoicelineindex`)
) ENGINE=InnoDB AUTO_INCREMENT=37591 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoiceoptions`
--

DROP TABLE IF EXISTS `invoiceoptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoiceoptions` (
  `invoiceoptionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `invoiceno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `invoicelineno` int(11) NOT NULL,
  `optionno` int(11) NOT NULL,
  `invoiceoptionno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` double NOT NULL,
  `qtyinvoiced` int(11) NOT NULL,
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`invoiceoptionindex`)
) ENGINE=InnoDB AUTO_INCREMENT=37591 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemcomments`
--

DROP TABLE IF EXISTS `itemcomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemcomments` (
  `itemcode` varchar(40) CHARACTER SET utf8 NOT NULL,
  `commentcode` int(40) NOT NULL AUTO_INCREMENT,
  `comment` varchar(200) CHARACTER SET utf8 NOT NULL,
  `username` varchar(35) CHARACTER SET utf8 NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`commentcode`)
) ENGINE=InnoDB AUTO_INCREMENT=566 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemcondition`
--

DROP TABLE IF EXISTS `itemcondition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemcondition` (
  `conditionID` int(11) NOT NULL AUTO_INCREMENT,
  `abbreviation` varchar(10) NOT NULL,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`conditionID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labelfields`
--

DROP TABLE IF EXISTS `labelfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labelfields` (
  `labelfieldid` int(11) NOT NULL AUTO_INCREMENT,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT 0,
  `hpos` double NOT NULL DEFAULT 0,
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labels` (
  `labelid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `pagewidth` double NOT NULL DEFAULT 0,
  `pageheight` double NOT NULL DEFAULT 0,
  `height` double NOT NULL DEFAULT 0,
  `width` double NOT NULL DEFAULT 0,
  `topmargin` double NOT NULL DEFAULT 0,
  `leftmargin` double NOT NULL DEFAULT 0,
  `rowheight` double NOT NULL DEFAULT 0,
  `columnwidth` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`labelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lastcostrollup`
--

DROP TABLE IF EXISTS `lastcostrollup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT 0,
  `matcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `labcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `oheadcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `newlabcost` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lastreading`
--

DROP TABLE IF EXISTS `lastreading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lastreading` (
  `number` varchar(11) NOT NULL,
  `lastreading` int(20) NOT NULL,
  `average` double NOT NULL,
  `expensecode` int(11) NOT NULL,
  PRIMARY KEY (`expensecode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `locationname` varchar(50) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `taxprovinceid` tinyint(4) NOT NULL DEFAULT 1,
  `cashsalecustomer` varchar(10) DEFAULT '',
  `managed` int(11) DEFAULT 0,
  `cashsalebranch` varchar(10) DEFAULT '',
  `internalrequest` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Allow (1) or not (0) internal request from this location',
  `defaultsubstore` int(11) NOT NULL,
  PRIMARY KEY (`loccode`),
  UNIQUE KEY `locationname` (`locationname`),
  KEY `taxprovinceid` (`taxprovinceid`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locationusers`
--

DROP TABLE IF EXISTS `locationusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT 0,
  `canupd` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locstock`
--

DROP TABLE IF EXISTS `locstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `reorderlevel` bigint(20) NOT NULL DEFAULT 0,
  `bin` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `bin` (`bin`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `locstockcheck`
--

DROP TABLE IF EXISTS `locstockcheck`;
/*!50001 DROP VIEW IF EXISTS `locstockcheck`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `locstockcheck` (
  `stockid` tinyint NOT NULL,
  `QTY` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loctransfers`
--

DROP TABLE IF EXISTS `loctransfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loctransfers` (
  `reference` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT 0,
  `recqty` double NOT NULL DEFAULT 0,
  `shipdate` datetime NOT NULL DEFAULT current_timestamp(),
  `recdate` datetime DEFAULT NULL,
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Shipments To And From Locations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailgroupdetails`
--

DROP TABLE IF EXISTS `mailgroupdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailgroupdetails` (
  `groupname` varchar(100) NOT NULL,
  `userid` varchar(20) NOT NULL,
  KEY `userid` (`userid`),
  KEY `groupname` (`groupname`),
  CONSTRAINT `mailgroupdetails_ibfk_1` FOREIGN KEY (`groupname`) REFERENCES `mailgroups` (`groupname`),
  CONSTRAINT `mailgroupdetails_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailgroups`
--

DROP TABLE IF EXISTS `mailgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupname` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manufacturers` (
  `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`manufacturers_id`),
  KEY `manufacturers_name` (`manufacturers_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1383 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpcalendar`
--

DROP TABLE IF EXISTS `mrpcalendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT 1,
  PRIMARY KEY (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpdemands`
--

DROP TABLE IF EXISTS `mrpdemands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `duedate` date DEFAULT NULL,
  PRIMARY KEY (`demandid`),
  KEY `StockID` (`stockid`),
  KEY `mrpdemands_ibfk_1` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpdemandtypes`
--

DROP TABLE IF EXISTS `mrpdemandtypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpplannedorders`
--

DROP TABLE IF EXISTS `mrpplannedorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpplannedorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocdetails`
--

DROP TABLE IF EXISTS `ocdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocdetails` (
  `ocdetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pono` varchar(100) NOT NULL,
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `quantity` double NOT NULL DEFAULT 0,
  `quantityremaining` double NOT NULL,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `checked` tinyint(1) NOT NULL DEFAULT 1,
  `regretreason` text NOT NULL,
  PRIMARY KEY (`ocdetailsindex`),
  KEY `orderno` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=59158 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oclines`
--

DROP TABLE IF EXISTS `oclines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oclines` (
  `lineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `lineno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`lineindex`)
) ENGINE=InnoDB AUTO_INCREMENT=44938 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocoptions`
--

DROP TABLE IF EXISTS `ocoptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocoptions` (
  `optionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optionno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` double NOT NULL,
  `quantityremaining` double NOT NULL,
  PRIMARY KEY (`optionindex`)
) ENGINE=InnoDB AUTO_INCREMENT=46592 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocs`
--

DROP TABLE IF EXISTS `ocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocs` (
  `orderno` int(11) NOT NULL,
  `pono` varchar(100) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `quotationno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` datetime NOT NULL DEFAULT current_timestamp(),
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `confirmeddate` date DEFAULT NULL,
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date DEFAULT NULL,
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `quotedate` date DEFAULT NULL,
  `poplaced` tinyint(4) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL,
  `updatecheck` smallint(6) NOT NULL DEFAULT 0,
  `optionupdatecheck` smallint(1) NOT NULL,
  `GSTadd` text NOT NULL,
  `services` tinyint(4) NOT NULL,
  `WHT` double NOT NULL,
  `inprogress` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`orderno`,`pono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvalueadmin`
--

DROP TABLE IF EXISTS `ocvalueadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvalueadmin` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvalueadnan_sattar`
--

DROP TABLE IF EXISTS `ocvalueadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvalueadnan_sattar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvalueahmad_zaheer`
--

DROP TABLE IF EXISTS `ocvalueahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvalueahmad_zaheer` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvalueali_shabbar`
--

DROP TABLE IF EXISTS `ocvalueali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvalueali_shabbar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvalueammar_hafeez`
--

DROP TABLE IF EXISTS `ocvalueammar_hafeez`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvalueammar_hafeez` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandadmin`
--

DROP TABLE IF EXISTS `ocvaluebrandadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandadmin` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandadnan_sattar`
--

DROP TABLE IF EXISTS `ocvaluebrandadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandadnan_sattar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandahmad_zaheer`
--

DROP TABLE IF EXISTS `ocvaluebrandahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandahmad_zaheer` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandali_shabbar`
--

DROP TABLE IF EXISTS `ocvaluebrandali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandali_shabbar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandjamal`
--

DROP TABLE IF EXISTS `ocvaluebrandjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandjamal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandmaaz_binzia`
--

DROP TABLE IF EXISTS `ocvaluebrandmaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandmaaz_binzia` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandmuhammad_bilal`
--

DROP TABLE IF EXISTS `ocvaluebrandmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandmuhammad_bilal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluebrandus_help`
--

DROP TABLE IF EXISTS `ocvaluebrandus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluebrandus_help` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomeradmin`
--

DROP TABLE IF EXISTS `ocvaluecustomeradmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomeradmin` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomeradnan_sattar`
--

DROP TABLE IF EXISTS `ocvaluecustomeradnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomeradnan_sattar` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomerahmad_zaheer`
--

DROP TABLE IF EXISTS `ocvaluecustomerahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomerahmad_zaheer` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomerali_imran`
--

DROP TABLE IF EXISTS `ocvaluecustomerali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomerali_imran` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomerali_shabbar`
--

DROP TABLE IF EXISTS `ocvaluecustomerali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomerali_shabbar` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomerjamal`
--

DROP TABLE IF EXISTS `ocvaluecustomerjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomerjamal` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluecustomermuhammad_bilal`
--

DROP TABLE IF EXISTS `ocvaluecustomermuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluecustomermuhammad_bilal` (
  `ocvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluejamal`
--

DROP TABLE IF EXISTS `ocvaluejamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluejamal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluemaaz_binzia`
--

DROP TABLE IF EXISTS `ocvaluemaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluemaaz_binzia` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluemuhammad_arif`
--

DROP TABLE IF EXISTS `ocvaluemuhammad_arif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluemuhammad_arif` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluemuhammad_bilal`
--

DROP TABLE IF EXISTS `ocvaluemuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluemuhammad_bilal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsadmin`
--

DROP TABLE IF EXISTS `ocvaluereportsadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsadmin` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsadnan_sattar`
--

DROP TABLE IF EXISTS `ocvaluereportsadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsadnan_sattar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsahmad_zaheer`
--

DROP TABLE IF EXISTS `ocvaluereportsahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsahmad_zaheer` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsali_imran`
--

DROP TABLE IF EXISTS `ocvaluereportsali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsali_imran` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsali_shabbar`
--

DROP TABLE IF EXISTS `ocvaluereportsali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsali_shabbar` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsjamal`
--

DROP TABLE IF EXISTS `ocvaluereportsjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsjamal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsmuhammad_bilal`
--

DROP TABLE IF EXISTS `ocvaluereportsmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsmuhammad_bilal` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluereportsus_help`
--

DROP TABLE IF EXISTS `ocvaluereportsus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluereportsus_help` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesajjad_ahmed`
--

DROP TABLE IF EXISTS `ocvaluesajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesajjad_ahmed` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonadmin`
--

DROP TABLE IF EXISTS `ocvaluesalespersonadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonadmin` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonadnan_sattar`
--

DROP TABLE IF EXISTS `ocvaluesalespersonadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonadnan_sattar` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonahmad_zaheer`
--

DROP TABLE IF EXISTS `ocvaluesalespersonahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonahmad_zaheer` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonali_imran`
--

DROP TABLE IF EXISTS `ocvaluesalespersonali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonali_imran` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonali_shabbar`
--

DROP TABLE IF EXISTS `ocvaluesalespersonali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonali_shabbar` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonjamal`
--

DROP TABLE IF EXISTS `ocvaluesalespersonjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonjamal` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonmuhammad_bilal`
--

DROP TABLE IF EXISTS `ocvaluesalespersonmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonmuhammad_bilal` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonsajjad_ahmed`
--

DROP TABLE IF EXISTS `ocvaluesalespersonsajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonsajjad_ahmed` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluesalespersonus_help`
--

DROP TABLE IF EXISTS `ocvaluesalespersonus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluesalespersonus_help` (
  `ocvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocvaluetechadmin`
--

DROP TABLE IF EXISTS `ocvaluetechadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ocvaluetechadmin` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `offerid` int(11) NOT NULL AUTO_INCREMENT,
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT 0,
  `expirydate` date DEFAULT NULL,
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  KEY `offers_ibfk_1` (`supplierid`),
  KEY `offers_ibfk_2` (`stockid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orderdeliverydifferenceslog`
--

DROP TABLE IF EXISTS `orderdeliverydifferenceslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT 0,
  `invoiceno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT 0,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN',
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `panel_costing`
--

DROP TABLE IF EXISTS `panel_costing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panel_costing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `14swg_percent` varchar(255) DEFAULT '1',
  `16swg_final` float DEFAULT NULL,
  `16swg_total` float DEFAULT NULL,
  `16swg_percent` varchar(255) DEFAULT '1',
  `14swg_final` float DEFAULT NULL,
  `18swg_total` float DEFAULT NULL,
  `18swg_percent` varchar(255) DEFAULT '1',
  `18swg_final` float DEFAULT NULL,
  `sheet_sheet_cd` varchar(255) NOT NULL,
  `closed` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `panelcostingmodifications`
--

DROP TABLE IF EXISTS `panelcostingmodifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panelcostingmodifications` (
  `modificationId` int(11) NOT NULL AUTO_INCREMENT,
  `panel_id` int(11) NOT NULL,
  `updateDate` varchar(255) NOT NULL,
  `pc_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`modificationId`)
) ENGINE=InnoDB AUTO_INCREMENT=720 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pannel_costing`
--

DROP TABLE IF EXISTS `pannel_costing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pannel_costing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) DEFAULT NULL,
  `costNo` int(11) DEFAULT NULL,
  `pannel_size_h` float DEFAULT NULL,
  `pannel_size_w` float DEFAULT NULL,
  `pannel_size_d` float DEFAULT NULL,
  `front_door_h` float DEFAULT NULL,
  `front_door_w` float DEFAULT NULL,
  `front_door_q` float DEFAULT NULL,
  `front_door_ss` float DEFAULT NULL,
  `front_door_sf` float DEFAULT NULL,
  `front_door_ws` float DEFAULT NULL,
  `front_door_h_two` float DEFAULT NULL,
  `front_door_w_two` float DEFAULT NULL,
  `front_door_q_two` float DEFAULT NULL,
  `front_door_ss_two` float DEFAULT NULL,
  `front_door_sf_two` float DEFAULT NULL,
  `front_door_ws_two` float DEFAULT NULL,
  `front_door_h_three` float DEFAULT NULL,
  `front_door_w_three` float DEFAULT NULL,
  `front_door_q_three` float DEFAULT NULL,
  `front_door_ss_three` float DEFAULT NULL,
  `front_door_sf_three` float DEFAULT NULL,
  `front_door_ws_three` float DEFAULT NULL,
  `front_door_h_four` float DEFAULT NULL,
  `front_door_w_four` float DEFAULT NULL,
  `front_door_q_four` float DEFAULT NULL,
  `front_door_ss_four` float DEFAULT NULL,
  `front_door_sf_four` float DEFAULT NULL,
  `front_door_ws_four` float DEFAULT NULL,
  `front_door_h_five` float DEFAULT NULL,
  `front_door_w_five` float DEFAULT NULL,
  `front_door_q_five` float DEFAULT NULL,
  `front_door_ss_five` float DEFAULT NULL,
  `front_door_sf_five` float DEFAULT NULL,
  `front_door_ws_five` float DEFAULT NULL,
  `front_door_h_six` float DEFAULT NULL,
  `front_door_w_six` float DEFAULT NULL,
  `front_door_q_six` float DEFAULT NULL,
  `front_door_ss_six` float DEFAULT NULL,
  `front_door_sf_six` float DEFAULT NULL,
  `front_door_ws_six` float DEFAULT NULL,
  `front_door_h_seven` float DEFAULT NULL,
  `front_door_w_seven` float DEFAULT NULL,
  `front_door_q_seven` float DEFAULT NULL,
  `front_door_ss_seven` float DEFAULT NULL,
  `front_door_sf_seven` float DEFAULT NULL,
  `front_door_ws_seven` float DEFAULT NULL,
  `front_door_h_eight` float DEFAULT NULL,
  `front_door_w_eight` float DEFAULT NULL,
  `front_door_q_eight` float DEFAULT NULL,
  `front_door_ss_eight` float DEFAULT NULL,
  `front_door_sf_eight` float DEFAULT NULL,
  `front_door_ws_eight` float DEFAULT NULL,
  `front_door_h_nine` float DEFAULT NULL,
  `front_door_w_nine` float DEFAULT NULL,
  `front_door_q_nine` float DEFAULT NULL,
  `front_door_ss_nine` float DEFAULT NULL,
  `front_door_sf_nine` float DEFAULT NULL,
  `front_door_ws_nine` float DEFAULT NULL,
  `back_door_h_four` float DEFAULT NULL,
  `back_door_w_four` float DEFAULT NULL,
  `back_door_q_four` float DEFAULT NULL,
  `back_door_ss_four` float DEFAULT NULL,
  `back_door_sf_four` float DEFAULT NULL,
  `back_door_ws_four` float DEFAULT NULL,
  `back_door_h_five` float DEFAULT NULL,
  `back_door_w_five` float DEFAULT NULL,
  `back_door_q_five` float DEFAULT NULL,
  `back_door_ss_five` float DEFAULT NULL,
  `back_door_sf_five` float DEFAULT NULL,
  `back_door_ws_five` float DEFAULT NULL,
  `back_door_h_six` float DEFAULT NULL,
  `back_door_w_six` float DEFAULT NULL,
  `back_door_q_six` float DEFAULT NULL,
  `back_door_ss_six` float DEFAULT NULL,
  `back_door_sf_six` float DEFAULT NULL,
  `back_door_ws_six` float DEFAULT NULL,
  `back_door_h_seven` float DEFAULT NULL,
  `back_door_w_seven` float DEFAULT NULL,
  `back_door_q_seven` float DEFAULT NULL,
  `back_door_ss_seven` float DEFAULT NULL,
  `back_door_sf_seven` float DEFAULT NULL,
  `back_door_ws_seven` float DEFAULT NULL,
  `back_door_h_eight` float DEFAULT NULL,
  `back_door_w_eight` float DEFAULT NULL,
  `back_door_q_eight` float DEFAULT NULL,
  `back_door_ss_eight` float DEFAULT NULL,
  `back_door_sf_eight` float DEFAULT NULL,
  `back_door_ws_eight` float DEFAULT NULL,
  `back_door_h_nine` float DEFAULT NULL,
  `back_door_w_nine` float DEFAULT NULL,
  `back_door_q_nine` float DEFAULT NULL,
  `back_door_ss_nine` float DEFAULT NULL,
  `back_door_sf_nine` float DEFAULT NULL,
  `back_door_ws_nine` float DEFAULT NULL,
  `back_door_h` float DEFAULT NULL,
  `back_door_w` float DEFAULT NULL,
  `back_door_q` float DEFAULT NULL,
  `back_door_ss` float DEFAULT NULL,
  `back_door_sf` float DEFAULT NULL,
  `back_door_ws` float DEFAULT NULL,
  `back_door_h_two` float DEFAULT NULL,
  `back_door_w_two` float DEFAULT NULL,
  `back_door_q_two` float DEFAULT NULL,
  `back_door_ss_two` float DEFAULT NULL,
  `back_door_sf_two` float DEFAULT NULL,
  `back_door_ws_two` float DEFAULT NULL,
  `back_door_h_three` float DEFAULT NULL,
  `back_door_w_three` float DEFAULT NULL,
  `back_door_q_three` float DEFAULT NULL,
  `back_door_ss_three` float DEFAULT NULL,
  `back_door_sf_three` float DEFAULT NULL,
  `back_door_ws_three` float DEFAULT NULL,
  `side_door_RL_h` float DEFAULT NULL,
  `side_door_RL_w` float DEFAULT NULL,
  `side_door_RL_q` float DEFAULT NULL,
  `side_door_RL_ss` float DEFAULT NULL,
  `side_door_RL_sf` float DEFAULT NULL,
  `side_door_RL_ws` float DEFAULT NULL,
  `VS_piece_FB_L` float DEFAULT NULL,
  `VS_piece_FB_w` float DEFAULT NULL,
  `VS_piece_FB_q` float DEFAULT NULL,
  `VS_piece_FB_ss` float DEFAULT NULL,
  `VS_piece_FB_sf` float DEFAULT NULL,
  `VS_piece_FB_ws` float DEFAULT NULL,
  `HS_piece_FB_L` float DEFAULT NULL,
  `HS_piece_FB_w` float DEFAULT NULL,
  `HS_piece_FB_q` float DEFAULT NULL,
  `HS_piece_FB_ss` float DEFAULT NULL,
  `HS_piece_FB_sf` float DEFAULT NULL,
  `HS_piece_FB_ws` float DEFAULT NULL,
  `HS_piece_RL_h` float DEFAULT NULL,
  `HS_piece_RL_w` float DEFAULT NULL,
  `HS_piece_RL_q` float DEFAULT NULL,
  `HS_piece_RL_ss` float DEFAULT NULL,
  `HS_piece_RL_sf` float DEFAULT NULL,
  `HS_piece_RL_ws` float DEFAULT NULL,
  `componnent_plate_h` float DEFAULT NULL,
  `componnent_plate_w` float DEFAULT NULL,
  `componnent_plate_q` float DEFAULT NULL,
  `componnent_plate_ss` float DEFAULT NULL,
  `componnent_plate_sf` float DEFAULT NULL,
  `componnent_plate_ws` float DEFAULT NULL,
  `componnent_plate_h_2` float DEFAULT NULL,
  `componnent_plate_w_2` float DEFAULT NULL,
  `componnent_plate_q_2` float DEFAULT NULL,
  `componnent_plate_ss_2` float DEFAULT NULL,
  `componnent_plate_sf_2` float DEFAULT NULL,
  `componnent_plate_ws_2` float DEFAULT NULL,
  `componnent_plate_h_3` float DEFAULT NULL,
  `componnent_plate_w_3` float DEFAULT NULL,
  `componnent_plate_q_3` float DEFAULT NULL,
  `componnent_plate_ss_3` float DEFAULT NULL,
  `componnent_plate_sf_3` float DEFAULT NULL,
  `componnent_plate_ws_3` float DEFAULT NULL,
  `componnent_plate_h_4` float DEFAULT NULL,
  `componnent_plate_w_4` float DEFAULT NULL,
  `componnent_plate_q_4` float DEFAULT NULL,
  `componnent_plate_ss_4` float DEFAULT NULL,
  `componnent_plate_sf_4` float DEFAULT NULL,
  `componnent_plate_ws_4` float DEFAULT NULL,
  `componnent_plate_h_5` float DEFAULT NULL,
  `componnent_plate_w_5` float DEFAULT NULL,
  `componnent_plate_q_5` float DEFAULT NULL,
  `componnent_plate_ss_5` float DEFAULT NULL,
  `componnent_plate_sf_5` float DEFAULT NULL,
  `componnent_plate_ws_5` float DEFAULT NULL,
  `componnent_plate_h_6` float DEFAULT NULL,
  `componnent_plate_w_6` float DEFAULT NULL,
  `componnent_plate_q_6` float DEFAULT NULL,
  `componnent_plate_ss_6` float DEFAULT NULL,
  `componnent_plate_sf_6` float DEFAULT NULL,
  `componnent_plate_ws_6` float DEFAULT NULL,
  `componnent_plate_h_7` float DEFAULT NULL,
  `componnent_plate_w_7` float DEFAULT NULL,
  `componnent_plate_q_7` float DEFAULT NULL,
  `componnent_plate_ss_7` float DEFAULT NULL,
  `componnent_plate_sf_7` float DEFAULT NULL,
  `componnent_plate_ws_7` float DEFAULT NULL,
  `componnent_plate_h_8` float DEFAULT NULL,
  `componnent_plate_w_8` float DEFAULT NULL,
  `componnent_plate_q_8` float DEFAULT NULL,
  `componnent_plate_ss_8` float DEFAULT NULL,
  `componnent_plate_sf_8` float DEFAULT NULL,
  `componnent_plate_ws_8` float DEFAULT NULL,
  `componnent_plate_h_9` float DEFAULT NULL,
  `componnent_plate_w_9` float DEFAULT NULL,
  `componnent_plate_q_9` float DEFAULT NULL,
  `componnent_plate_ss_9` float DEFAULT NULL,
  `componnent_plate_sf_9` float DEFAULT NULL,
  `componnent_plate_ws_9` float DEFAULT NULL,
  `pallet_L` float DEFAULT NULL,
  `pallet_w` float DEFAULT NULL,
  `pallet_q` float DEFAULT NULL,
  `pallet_ss` float DEFAULT NULL,
  `pallet_sf` float DEFAULT NULL,
  `pallet_ws` float DEFAULT NULL,
  `pallet_L_2` float DEFAULT NULL,
  `pallet_w_2` float DEFAULT NULL,
  `pallet_q_2` float DEFAULT NULL,
  `pallet_ss_2` float DEFAULT NULL,
  `pallet_sf_2` float DEFAULT NULL,
  `pallet_ws_2` float DEFAULT NULL,
  `FB_base_L` float DEFAULT NULL,
  `FB_base_w` float DEFAULT NULL,
  `FB_base_q` float DEFAULT NULL,
  `FB_base_ss` float DEFAULT NULL,
  `FB_base_sf` float DEFAULT NULL,
  `FB_base_ws` float DEFAULT NULL,
  `RL_base_L` float DEFAULT NULL,
  `RL_base_w` float DEFAULT NULL,
  `RL_base_q` float DEFAULT NULL,
  `RL_base_ss` float DEFAULT NULL,
  `RL_base_sf` float DEFAULT NULL,
  `RL_base_ws` float DEFAULT NULL,
  `VI_door_U_L` float DEFAULT NULL,
  `VI_door_U_w` float DEFAULT NULL,
  `VI_door_U_q` float DEFAULT NULL,
  `VI_door_U_ss` float DEFAULT NULL,
  `VI_door_U_sf` float DEFAULT NULL,
  `VI_door_U_ws` float DEFAULT NULL,
  `HI_door_u_L` float DEFAULT NULL,
  `HI_door_u_w` float DEFAULT NULL,
  `HI_door_u_q` float DEFAULT NULL,
  `HI_door_u_ss` float DEFAULT NULL,
  `HI_door_u_sf` float DEFAULT NULL,
  `HI_door_u_ws` float DEFAULT NULL,
  `VI_L_type_L` float DEFAULT NULL,
  `VI_L_type_w` float DEFAULT NULL,
  `VI_L_type_q` float DEFAULT NULL,
  `VI_L_type_ss` float DEFAULT NULL,
  `VI_L_type_sf` float DEFAULT NULL,
  `VI_L_type_ws` float DEFAULT NULL,
  `HI_U_type_L` float DEFAULT NULL,
  `HI_U_type_w` float DEFAULT NULL,
  `HI_U_type_q` float NOT NULL,
  `HI_U_type_ss` float NOT NULL,
  `HI_U_type_sf` float DEFAULT NULL,
  `HI_U_type_ws` float DEFAULT NULL,
  `top_L` float DEFAULT NULL,
  `top_w` float DEFAULT NULL,
  `top_q` float DEFAULT NULL,
  `top_ss` float DEFAULT NULL,
  `top_sf` float DEFAULT NULL,
  `top_ws` float DEFAULT NULL,
  `bottom_L` float DEFAULT NULL,
  `bottom_w` float DEFAULT NULL,
  `bottom_q` float DEFAULT NULL,
  `bottom_ss` float DEFAULT NULL,
  `bottom_sf` float DEFAULT NULL,
  `bottom_ws` float DEFAULT NULL,
  `protection_sheet_h` float DEFAULT NULL,
  `protection_sheet_w` float DEFAULT NULL,
  `protection_sheet_q` float DEFAULT NULL,
  `protection_sheet_ss` float DEFAULT NULL,
  `protection_sheet_sf` float DEFAULT NULL,
  `protection_sheet_ws` float DEFAULT NULL,
  `protection_sheet_h_2` float DEFAULT NULL,
  `protection_sheet_w_2` float DEFAULT NULL,
  `protection_sheet_q_2` float DEFAULT NULL,
  `protection_sheet_ss_2` float DEFAULT NULL,
  `protection_sheet_sf_2` float DEFAULT NULL,
  `protection_sheet_ws_2` float DEFAULT NULL,
  `miscellaneous_h` float DEFAULT NULL,
  `miscellaneous_w` float DEFAULT NULL,
  `miscellaneous_q` float DEFAULT NULL,
  `miscellaneous_ss` float DEFAULT NULL,
  `miscellaneous_sf` float DEFAULT NULL,
  `miscellaneous_ws` float DEFAULT NULL,
  `gauges_total_weight` float NOT NULL,
  `total_SF` float NOT NULL,
  `s_by_sf` float DEFAULT NULL,
  `sheet_consume` int(11) NOT NULL,
  `12_SWG_price` float DEFAULT NULL,
  `14_SWG_price` float DEFAULT NULL,
  `16_SWG_price` float DEFAULT NULL,
  `18_SWG_price` float DEFAULT NULL,
  `20_SWG_price` float DEFAULT NULL,
  `sheet_type` varchar(255) NOT NULL,
  `matal_s_price` float DEFAULT NULL,
  `stainless_s_price` float DEFAULT NULL,
  `galvanized_s_price` float DEFAULT NULL,
  `mult_gauge_price` float NOT NULL,
  `closed` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=292 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parchi_dc`
--

DROP TABLE IF EXISTS `parchi_dc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parchi_dc` (
  `parchino` varchar(30) NOT NULL,
  `dcno` varchar(30) NOT NULL,
  PRIMARY KEY (`parchino`,`dcno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymentmethods`
--

DROP TABLE IF EXISTS `paymentmethods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT 1,
  `receipttype` int(11) NOT NULL DEFAULT 1,
  `usepreprintedstationery` tinyint(4) NOT NULL DEFAULT 0,
  `opencashdrawer` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymentterms`
--

DROP TABLE IF EXISTS `paymentterms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT 0,
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pc_cash_demand`
--

DROP TABLE IF EXISTS `pc_cash_demand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pc_cash_demand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `cashdemand_total` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pc_rate`
--

DROP TABLE IF EXISTS `pc_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pc_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `h_h` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pc_rate_update`
--

DROP TABLE IF EXISTS `pc_rate_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pc_rate_update` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value_name` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `updated_value` float NOT NULL,
  `updated_date` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pcashdetails`
--

DROP TABLE IF EXISTS `pcashdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text DEFAULT NULL COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  `receiptimage` varchar(50) NOT NULL,
  `lastreading` int(20) NOT NULL,
  `meterreading` int(20) NOT NULL,
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB AUTO_INCREMENT=63977 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pcexpenses`
--

DROP TABLE IF EXISTS `pcexpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `tag` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codeexpense`),
  KEY `glaccount` (`glaccount`),
  CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctabexpenses`
--

DROP TABLE IF EXISTS `pctabexpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY `typetabcode` (`typetabcode`),
  KEY `codeexpense` (`codeexpense`),
  CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctabs`
--

DROP TABLE IF EXISTS `pctabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `assigner` varchar(20) NOT NULL COMMENT 'Cash assigner for the tab',
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tabcode`),
  KEY `usercode` (`usercode`),
  KEY `typetabcode` (`typetabcode`),
  KEY `currency` (`currency`),
  KEY `authorizer` (`authorizer`),
  KEY `glaccountassignment` (`glaccountassignment`),
  CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctypetabs`
--

DROP TABLE IF EXISTS `pctypetabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `lastdate_in_period` date DEFAULT NULL,
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `pettycashbalance`
--

DROP TABLE IF EXISTS `pettycashbalance`;
/*!50001 DROP VIEW IF EXISTS `pettycashbalance`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `pettycashbalance` (
  `tabcode` tinyint NOT NULL,
  `SUM(amount)` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `pflist`
--

DROP TABLE IF EXISTS `pflist`;
/*!50001 DROP VIEW IF EXISTS `pflist`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `pflist` (
  `stockid` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `materialcost` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `pflista`
--

DROP TABLE IF EXISTS `pflista`;
/*!50001 DROP VIEW IF EXISTS `pflista`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `pflista` (
  `brand` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `materialcost` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pickinglistdetails`
--

DROP TABLE IF EXISTS `pickinglistdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL DEFAULT 0,
  `qtypicked` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`pickinglistno`,`pickinglistlineno`),
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pickinglists`
--

DROP TABLE IF EXISTS `pickinglists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date DEFAULT NULL,
  `dateprinted` date DEFAULT NULL,
  `deliverynotedate` date DEFAULT NULL,
  PRIMARY KEY (`pickinglistno`),
  KEY `pickinglists_ibfk_1` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posdispatch`
--

DROP TABLE IF EXISTS `posdispatch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posdispatch` (
  `dispatchid` int(11) NOT NULL AUTO_INCREMENT,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `despatchdate` date DEFAULT NULL,
  `authorised` tinyint(4) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` text NOT NULL,
  `storemanager` varchar(35) NOT NULL,
  `deliveredto` varchar(100) NOT NULL,
  `authorizer` varchar(35) NOT NULL,
  `substore` int(11) NOT NULL,
  PRIMARY KEY (`dispatchid`),
  KEY `loccode` (`loccode`),
  CONSTRAINT `posdispatch_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=179553 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posdispatchitems`
--

DROP TABLE IF EXISTS `posdispatchitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posdispatchitems` (
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `comments` varchar(200) NOT NULL,
  `rate` double NOT NULL,
  `brand` varchar(32) NOT NULL,
  `mnfCode` varchar(40) NOT NULL,
  `dispatchid` int(11) NOT NULL,
  `dispatchitemsid` int(11) NOT NULL,
  KEY `stockid` (`stockid`),
  KEY `stockid_2` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pricefactor`
--

DROP TABLE IF EXISTS `pricefactor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pricefactor` (
  `stockid` varchar(40) CHARACTER SET utf8 NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `bysea` decimal(10,5) NOT NULL,
  `byair` decimal(10,5) NOT NULL,
  `bylocal` decimal(10,5) NOT NULL,
  `showbysea` varchar(4) CHARACTER SET utf8 NOT NULL,
  `showbyair` varchar(4) CHARACTER SET utf8 NOT NULL,
  `showbylocal` varchar(4) CHARACTER SET utf8 NOT NULL,
  `currency` varchar(4) CHARACTER SET utf8 NOT NULL,
  `lastcost` decimal(10,2) NOT NULL,
  `lastupdate` date NOT NULL,
  PRIMARY KEY (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pricelist`
--

DROP TABLE IF EXISTS `pricelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pricelist` (
  `brand` varchar(32) NOT NULL,
  `lastcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `materialcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `lastcostupdate` date DEFAULT NULL,
  `lastupdatedby` varchar(35) NOT NULL,
  `mnfCode` varchar(100) DEFAULT NULL,
  `mnfpno` varchar(100) NOT NULL,
  `itemcondition` varchar(10) NOT NULL,
  `categorydescription` char(100) NOT NULL,
  `QOH` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pricematrix`
--

DROP TABLE IF EXISTS `pricematrix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT 1,
  `price` double NOT NULL DEFAULT 0,
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL,
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`),
  KEY `SalesType` (`salestype`),
  KEY `currabrev` (`currabrev`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prices`
--

DROP TABLE IF EXISTS `prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL,
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procurement_document`
--

DROP TABLE IF EXISTS `procurement_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procurement_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierid` varchar(50) NOT NULL,
  `stage` varchar(50) NOT NULL,
  `commencement_date` datetime DEFAULT NULL,
  `canceled_date` datetime DEFAULT NULL,
  `received_date` datetime DEFAULT NULL,
  `publish_date` datetime DEFAULT NULL,
  `eta_date` datetime DEFAULT NULL,
  `timeline` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procurement_document_details`
--

DROP TABLE IF EXISTS `procurement_document_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procurement_document_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pdid` int(11) NOT NULL,
  `stockid` varchar(100) NOT NULL,
  `client_required` int(11) NOT NULL DEFAULT 0,
  `safety_inventory` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `quantity` float NOT NULL DEFAULT 0,
  `price` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2978 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prodspecs`
--

DROP TABLE IF EXISTS `prodspecs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodspecs` (
  `keyval` varchar(25) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `showoncert` tinyint(11) NOT NULL DEFAULT 1,
  `showonspec` tinyint(4) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`keyval`,`testid`),
  KEY `testid` (`testid`),
  CONSTRAINT `prodspecs_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchdata`
--

DROP TABLE IF EXISTS `purchdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL DEFAULT '',
  `stockid` char(20) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `suppliersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT 1,
  `supplierdescription` char(50) NOT NULL DEFAULT '',
  `leadtime` smallint(6) NOT NULL DEFAULT 1,
  `preferred` tinyint(4) NOT NULL DEFAULT 0,
  `effectivefrom` date NOT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `minorderqty` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorderauth`
--

DROP TABLE IF EXISTS `purchorderauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT 0,
  `authlevel` double NOT NULL DEFAULT 0,
  `offhold` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorderdetails`
--

DROP TABLE IF EXISTS `purchorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `itemdescription` varchar(100) NOT NULL,
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double NOT NULL DEFAULT 0,
  `actprice` double NOT NULL DEFAULT 0,
  `stdcostunit` double NOT NULL DEFAULT 0,
  `quantityord` double NOT NULL DEFAULT 0,
  `quantityrecd` double NOT NULL DEFAULT 0,
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `suppliersunit` varchar(50) DEFAULT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `assetid` int(11) NOT NULL DEFAULT 0,
  `conversionfactor` double NOT NULL DEFAULT 1,
  PRIMARY KEY (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorders`
--

DROP TABLE IF EXISTS `purchorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob DEFAULT NULL,
  `orddate` datetime DEFAULT NULL,
  `rate` double NOT NULL DEFAULT 1,
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint(4) NOT NULL DEFAULT 1,
  `initiator` varchar(20) DEFAULT NULL,
  `requisitionno` varchar(15) DEFAULT NULL,
  `intostocklocation` varchar(5) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress4` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress5` varchar(20) NOT NULL DEFAULT '',
  `suppdeladdress6` varchar(15) NOT NULL DEFAULT '',
  `suppliercontact` varchar(30) NOT NULL DEFAULT '',
  `supptel` varchar(30) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `version` decimal(3,2) NOT NULL DEFAULT 1.00,
  `revised` date DEFAULT NULL,
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT '',
  `stat_comment` text NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qasamples`
--

DROP TABLE IF EXISTS `qasamples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qasamples` (
  `sampleid` int(11) NOT NULL AUTO_INCREMENT,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date DEFAULT NULL,
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`sampleid`),
  KEY `prodspeckey` (`prodspeckey`,`lotkey`),
  CONSTRAINT `qasamples_ibfk_1` FOREIGN KEY (`prodspeckey`) REFERENCES `prodspecs` (`keyval`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qatests`
--

DROP TABLE IF EXISTS `qatests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qatests` (
  `testid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint(4) NOT NULL DEFAULT 0,
  `showoncert` int(11) NOT NULL DEFAULT 1,
  `showonspec` int(11) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `active` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`testid`),
  KEY `name` (`name`),
  KEY `groupname` (`groupby`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationmodifications`
--

DROP TABLE IF EXISTS `quotationmodifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationmodifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updatedat` datetime NOT NULL DEFAULT current_timestamp(),
  `lineno` int(11) NOT NULL,
  `orderno` int(11) NOT NULL,
  `eorderno` int(11) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=901464 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalue`
--

DROP TABLE IF EXISTS `quotationvalue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalue` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueaccountant`
--

DROP TABLE IF EXISTS `quotationvalueaccountant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueaccountant` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueadmin`
--

DROP TABLE IF EXISTS `quotationvalueadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueadnan_sattar`
--

DROP TABLE IF EXISTS `quotationvalueadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueahmad_sohail`
--

DROP TABLE IF EXISTS `quotationvalueahmad_sohail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueahmad_sohail` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueahmad_zaheer`
--

DROP TABLE IF EXISTS `quotationvalueahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueahsan_qureshi`
--

DROP TABLE IF EXISTS `quotationvalueahsan_qureshi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueahsan_qureshi` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueali_imran`
--

DROP TABLE IF EXISTS `quotationvalueali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueali_shabbar`
--

DROP TABLE IF EXISTS `quotationvalueali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueammar_hafeez`
--

DROP TABLE IF EXISTS `quotationvalueammar_hafeez`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueammar_hafeez` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueashan_qureshi`
--

DROP TABLE IF EXISTS `quotationvalueashan_qureshi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueashan_qureshi` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandadmin`
--

DROP TABLE IF EXISTS `quotationvaluebrandadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandadnan_sattar`
--

DROP TABLE IF EXISTS `quotationvaluebrandadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandahmad_zaheer`
--

DROP TABLE IF EXISTS `quotationvaluebrandahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandali_shabbar`
--

DROP TABLE IF EXISTS `quotationvaluebrandali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandjamal`
--

DROP TABLE IF EXISTS `quotationvaluebrandjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandmaaz_binzia`
--

DROP TABLE IF EXISTS `quotationvaluebrandmaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandmaaz_binzia` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandmuhammad_bilal`
--

DROP TABLE IF EXISTS `quotationvaluebrandmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluebrandus_help`
--

DROP TABLE IF EXISTS `quotationvaluebrandus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluebrandus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `manufacturers_id` int(11) DEFAULT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomeradmin`
--

DROP TABLE IF EXISTS `quotationvaluecustomeradmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomeradmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomeradnan_sattar`
--

DROP TABLE IF EXISTS `quotationvaluecustomeradnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomeradnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomerahmad_zaheer`
--

DROP TABLE IF EXISTS `quotationvaluecustomerahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomerahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomerali_imran`
--

DROP TABLE IF EXISTS `quotationvaluecustomerali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomerali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomerali_shabbar`
--

DROP TABLE IF EXISTS `quotationvaluecustomerali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomerali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomerjamal`
--

DROP TABLE IF EXISTS `quotationvaluecustomerjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomerjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluecustomermuhammad_bilal`
--

DROP TABLE IF EXISTS `quotationvaluecustomermuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluecustomermuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `customer` varchar(40) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueejaz_ahmed`
--

DROP TABLE IF EXISTS `quotationvalueejaz_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueejaz_ahmed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluefaisal_mehmood`
--

DROP TABLE IF EXISTS `quotationvaluefaisal_mehmood`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluefaisal_mehmood` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueimran_butt`
--

DROP TABLE IF EXISTS `quotationvalueimran_butt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueimran_butt` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueirfan_nasar`
--

DROP TABLE IF EXISTS `quotationvalueirfan_nasar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueirfan_nasar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluejalal`
--

DROP TABLE IF EXISTS `quotationvaluejalal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluejalal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluejamal`
--

DROP TABLE IF EXISTS `quotationvaluejamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluejamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemaaz_binzia`
--

DROP TABLE IF EXISTS `quotationvaluemaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemaaz_binzia` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemgmttest`
--

DROP TABLE IF EXISTS `quotationvaluemgmttest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemgmttest` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemohsin_iqbal`
--

DROP TABLE IF EXISTS `quotationvaluemohsin_iqbal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemohsin_iqbal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_ali`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_ali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_ali` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_arif`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_arif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_arif` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_bilal`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_mohsin`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_mohsin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_mohsin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_sarfraz`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_sarfraz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_sarfraz` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluemuhammad_shehzad`
--

DROP TABLE IF EXISTS `quotationvaluemuhammad_shehzad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluemuhammad_shehzad` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluenasir`
--

DROP TABLE IF EXISTS `quotationvaluenasir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluenasir` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluerao_saeed`
--

DROP TABLE IF EXISTS `quotationvaluerao_saeed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluerao_saeed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsadmin`
--

DROP TABLE IF EXISTS `quotationvaluereportsadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsadnan_sattar`
--

DROP TABLE IF EXISTS `quotationvaluereportsadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsahmad_zaheer`
--

DROP TABLE IF EXISTS `quotationvaluereportsahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsali_imran`
--

DROP TABLE IF EXISTS `quotationvaluereportsali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsali_shabbar`
--

DROP TABLE IF EXISTS `quotationvaluereportsali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsjamal`
--

DROP TABLE IF EXISTS `quotationvaluereportsjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsmuhammad_bilal`
--

DROP TABLE IF EXISTS `quotationvaluereportsmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluereportsus_help`
--

DROP TABLE IF EXISTS `quotationvaluereportsus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluereportsus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesajjad_ahmed`
--

DROP TABLE IF EXISTS `quotationvaluesajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesajjad_ahmed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonadmin`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonadnan_sattar`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonadnan_sattar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonahmad_zaheer`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonahmad_zaheer` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonali_imran`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonali_imran` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonali_shabbar`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonali_shabbar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonjamal`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonjamal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonmuhammad_bilal`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonmuhammad_bilal` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonsajjad_ahmed`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonsajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonsajjad_ahmed` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesalespersonus_help`
--

DROP TABLE IF EXISTS `quotationvaluesalespersonus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesalespersonus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueshahud_ali`
--

DROP TABLE IF EXISTS `quotationvalueshahud_ali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueshahud_ali` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluesohail_khaliq`
--

DROP TABLE IF EXISTS `quotationvaluesohail_khaliq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluesohail_khaliq` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluetechadmin`
--

DROP TABLE IF EXISTS `quotationvaluetechadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluetechadmin` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueumair_ahmad`
--

DROP TABLE IF EXISTS `quotationvalueumair_ahmad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueumair_ahmad` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueus_help`
--

DROP TABLE IF EXISTS `quotationvalueus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueus_help` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvalueusman_sarwar`
--

DROP TABLE IF EXISTS `quotationvalueusman_sarwar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvalueusman_sarwar` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationvaluezaheer_alam`
--

DROP TABLE IF EXISTS `quotationvaluezaheer_alam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationvaluezaheer_alam` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotationversion`
--

DROP TABLE IF EXISTS `quotationversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotationversion` (
  `qverindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` varchar(100) NOT NULL,
  `version` int(11) NOT NULL,
  PRIMARY KEY (`qverindex`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recovery_access`
--

DROP TABLE IF EXISTS `recovery_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recovery_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `can_access` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=347 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recurringsalesorders`
--

DROP TABLE IF EXISTS `recurringsalesorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date DEFAULT NULL,
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(25) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `freightcost` double NOT NULL DEFAULT 0,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `lastrecurrence` date DEFAULT NULL,
  `stopdate` date DEFAULT NULL,
  `frequency` tinyint(4) NOT NULL DEFAULT 1,
  `autoinvoice` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recurrsalesorderdetails`
--

DROP TABLE IF EXISTS `recurrsalesorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT 0,
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT 0,
  `quantity` double NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `narrative` text NOT NULL,
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `relateditems`
--

DROP TABLE IF EXISTS `relateditems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relateditems` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `related` varchar(20) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reorderitems`
--

DROP TABLE IF EXISTS `reorderitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reorderitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(100) DEFAULT NULL,
  `stockid` varchar(100) NOT NULL,
  `requested_qty` double NOT NULL,
  `requested_by` varchar(50) NOT NULL,
  `requested_date` datetime NOT NULL,
  `approved_qty` double NOT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `rejected_by` varchar(50) DEFAULT NULL,
  `rejected_reason` varchar(150) DEFAULT NULL,
  `rejected_date` datetime DEFAULT NULL,
  `fulfilled_date` datetime DEFAULT NULL,
  `comment` varchar(100) NOT NULL DEFAULT '',
  `resComment` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=642 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reorderlevel`
--

DROP TABLE IF EXISTS `reorderlevel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reorderlevel` (
  `reorderlevelindex` int(100) NOT NULL AUTO_INCREMENT,
  `categorydescription` char(100) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `mnfpno` varchar(100) NOT NULL,
  `mnfCode` varchar(100) NOT NULL,
  `conditionID` int(11) NOT NULL,
  `manufacturers_name` varchar(32) NOT NULL,
  `description` varchar(50) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `locationname` varchar(50) NOT NULL,
  `IGP` int(11) NOT NULL,
  `OGP` int(11) NOT NULL,
  `DC` int(11) NOT NULL,
  `quantity` double NOT NULL,
  `reorderlevel` bigint(20) NOT NULL,
  `needed` bigint(20) NOT NULL,
  PRIMARY KEY (`reorderlevelindex`),
  KEY `stockid` (`stockid`),
  KEY `loccode` (`loccode`)
) ENGINE=InnoDB AUTO_INCREMENT=510 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reorderleveltemp`
--

DROP TABLE IF EXISTS `reorderleveltemp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reorderleveltemp` (
  `reorderlevelindex` int(100) NOT NULL,
  `categorydescription` char(100) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `mnfpno` varchar(100) NOT NULL,
  `mnfCode` varchar(100) NOT NULL,
  `conditionID` int(11) NOT NULL,
  `manufacturers_name` varchar(32) NOT NULL,
  `description` varchar(50) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `locationname` varchar(50) NOT NULL,
  `quantity` double NOT NULL,
  `reorderlevel` bigint(20) NOT NULL,
  `needed` bigint(20) NOT NULL,
  KEY `stockid` (`stockid`),
  KEY `loccode` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportcolumns`
--

DROP TABLE IF EXISTS `reportcolumns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL DEFAULT 0,
  `colno` smallint(6) NOT NULL DEFAULT 0,
  `heading1` varchar(15) NOT NULL DEFAULT '',
  `heading2` varchar(15) DEFAULT NULL,
  `calculation` tinyint(1) NOT NULL DEFAULT 0,
  `periodfrom` smallint(6) DEFAULT NULL,
  `periodto` smallint(6) DEFAULT NULL,
  `datatype` varchar(15) DEFAULT NULL,
  `colnumerator` tinyint(4) DEFAULT NULL,
  `coldenominator` tinyint(4) DEFAULT NULL,
  `calcoperator` char(1) DEFAULT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT 0,
  `valformat` char(1) NOT NULL DEFAULT 'N',
  `constant` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportfields`
--

DROP TABLE IF EXISTS `reportfields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `reportid` int(5) NOT NULL DEFAULT 0,
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int(3) NOT NULL DEFAULT 0,
  `fieldname` varchar(80) NOT NULL DEFAULT '',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reportid` (`reportid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportheaders`
--

DROP TABLE IF EXISTS `reportheaders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL AUTO_INCREMENT,
  `reportheading` varchar(80) NOT NULL DEFAULT '',
  `groupbydata1` varchar(15) NOT NULL DEFAULT '',
  `newpageafter1` tinyint(1) NOT NULL DEFAULT 0,
  `lower1` varchar(10) NOT NULL DEFAULT '',
  `upper1` varchar(10) NOT NULL DEFAULT '',
  `groupbydata2` varchar(15) DEFAULT NULL,
  `newpageafter2` tinyint(1) NOT NULL DEFAULT 0,
  `lower2` varchar(10) DEFAULT NULL,
  `upper2` varchar(10) DEFAULT NULL,
  `groupbydata3` varchar(15) DEFAULT NULL,
  `newpageafter3` tinyint(1) NOT NULL DEFAULT 0,
  `lower3` varchar(10) DEFAULT NULL,
  `upper3` varchar(10) DEFAULT NULL,
  `groupbydata4` varchar(15) NOT NULL DEFAULT '',
  `newpageafter4` tinyint(1) NOT NULL DEFAULT 0,
  `upper4` varchar(10) NOT NULL DEFAULT '',
  `lower4` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`reportid`),
  KEY `ReportHeading` (`reportheading`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportlinks`
--

DROP TABLE IF EXISTS `reportlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `reportname` varchar(30) NOT NULL DEFAULT '',
  `reporttype` char(3) NOT NULL DEFAULT 'rpt',
  `groupname` varchar(9) NOT NULL DEFAULT 'misc',
  `defaultreport` enum('1','0') NOT NULL DEFAULT '0',
  `papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
  `margintop` int(3) NOT NULL DEFAULT 10,
  `marginbottom` int(3) NOT NULL DEFAULT 10,
  `marginleft` int(3) NOT NULL DEFAULT 10,
  `marginright` int(3) NOT NULL DEFAULT 10,
  `coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `coynamefontsize` int(3) NOT NULL DEFAULT 12,
  `coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `coynameshow` enum('1','0') NOT NULL DEFAULT '1',
  `title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
  `title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title1fontsize` int(3) NOT NULL DEFAULT 10,
  `title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title1show` enum('1','0') NOT NULL DEFAULT '1',
  `title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title2fontsize` int(3) NOT NULL DEFAULT 10,
  `title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title2show` enum('1','0') NOT NULL DEFAULT '1',
  `filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `filterfontsize` int(3) NOT NULL DEFAULT 8,
  `filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `datafontsize` int(3) NOT NULL DEFAULT 10,
  `datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
  `datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `totalsfontsize` int(3) NOT NULL DEFAULT 10,
  `totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `col1width` int(3) NOT NULL DEFAULT 25,
  `col2width` int(3) NOT NULL DEFAULT 25,
  `col3width` int(3) NOT NULL DEFAULT 25,
  `col4width` int(3) NOT NULL DEFAULT 25,
  `col5width` int(3) NOT NULL DEFAULT 25,
  `col6width` int(3) NOT NULL DEFAULT 25,
  `col7width` int(3) NOT NULL DEFAULT 25,
  `col8width` int(3) NOT NULL DEFAULT 25,
  `col9width` int(3) NOT NULL DEFAULT 25,
  `col10width` int(3) NOT NULL DEFAULT 25,
  `col11width` int(3) NOT NULL DEFAULT 25,
  `col12width` int(3) NOT NULL DEFAULT 25,
  `col13width` int(3) NOT NULL DEFAULT 25,
  `col14width` int(3) NOT NULL DEFAULT 25,
  `col15width` int(3) NOT NULL DEFAULT 25,
  `col16width` int(3) NOT NULL DEFAULT 25,
  `col17width` int(3) NOT NULL DEFAULT 25,
  `col18width` int(3) NOT NULL DEFAULT 25,
  `col19width` int(3) NOT NULL DEFAULT 25,
  `col20width` int(3) NOT NULL DEFAULT 25,
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) DEFAULT NULL,
  `table2criteria` varchar(75) DEFAULT NULL,
  `table3` varchar(25) DEFAULT NULL,
  `table3criteria` varchar(75) DEFAULT NULL,
  `table4` varchar(25) DEFAULT NULL,
  `table4criteria` varchar(75) DEFAULT NULL,
  `table5` varchar(25) DEFAULT NULL,
  `table5criteria` varchar(75) DEFAULT NULL,
  `table6` varchar(25) DEFAULT NULL,
  `table6criteria` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`reportname`,`groupname`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reversedallocationhistory`
--

DROP TABLE IF EXISTS `reversedallocationhistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversedallocationhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL,
  `client` text NOT NULL,
  `chequeDate` datetime NOT NULL,
  `revDate` datetime NOT NULL DEFAULT current_timestamp(),
  `UserID` text NOT NULL,
  `chequeno` text NOT NULL,
  `amount` double NOT NULL,
  `reason` text NOT NULL,
  `chequefilepath` text NOT NULL,
  `chequedepositfilepath` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reversedallocationhistorycomments`
--

DROP TABLE IF EXISTS `reversedallocationhistorycomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversedallocationhistorycomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reversedAllocationHistoryID` int(11) NOT NULL,
  `comment` text NOT NULL,
  `user` text NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reversedallocationhistorycommentsvendor`
--

DROP TABLE IF EXISTS `reversedallocationhistorycommentsvendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversedallocationhistorycommentsvendor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reversedAllocationHistoryID` int(11) NOT NULL,
  `comment` text NOT NULL,
  `user` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reversedallocationhistoryvendor`
--

DROP TABLE IF EXISTS `reversedallocationhistoryvendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversedallocationhistoryvendor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL,
  `client` text NOT NULL,
  `chequeDate` datetime NOT NULL,
  `revDate` datetime NOT NULL,
  `UserID` text NOT NULL,
  `chequeno` text NOT NULL,
  `amount` double NOT NULL,
  `reason` text NOT NULL,
  `chequefilepath` text NOT NULL,
  `chequedepositfilepath` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesanalysis`
--

DROP TABLE IF EXISTS `salesanalysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `periodno` smallint(6) NOT NULL DEFAULT 0,
  `amt` double NOT NULL DEFAULT 0,
  `cost` double NOT NULL DEFAULT 0,
  `cust` varchar(10) NOT NULL DEFAULT '',
  `custbranch` varchar(10) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 0,
  `disc` double NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `area` varchar(3) NOT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT 0,
  `salesperson` char(3) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `CustBranch` (`custbranch`),
  KEY `Cust` (`cust`),
  KEY `PeriodNo` (`periodno`),
  KEY `StkCategory` (`stkcategory`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `Area` (`area`),
  KEY `BudgetOrActual` (`budgetoractual`),
  KEY `Salesperson` (`salesperson`),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescase`
--

DROP TABLE IF EXISTS `salescase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescase` (
  `salescaseindex` int(40) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `value` varchar(10) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `closed` tinyint(1) NOT NULL,
  `closingreason` text NOT NULL,
  `closingremarks` text NOT NULL,
  `stage` text NOT NULL,
  `closingdate` datetime NOT NULL,
  `priority` varchar(10) NOT NULL DEFAULT 'medium',
  `priority_added` tinyint(1) NOT NULL DEFAULT 0,
  `priority_updated_by` varchar(40) NOT NULL,
  PRIMARY KEY (`salescaseindex`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `salescaseref` (`salescaseref`)
) ENGINE=InnoDB AUTO_INCREMENT=51879 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescase_permissions`
--

DROP TABLE IF EXISTS `salescase_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescase_permissions` (
  `user` varchar(100) NOT NULL,
  `can_access` varchar(100) NOT NULL,
  PRIMARY KEY (`user`,`can_access`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescase_watchlist`
--

DROP TABLE IF EXISTS `salescase_watchlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescase_watchlist` (
  `userid` varchar(30) NOT NULL,
  `salescaseref` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `review_on` date DEFAULT NULL,
  `priority` int(11) NOT NULL,
  `notes` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescaseclosedreporting`
--

DROP TABLE IF EXISTS `salescaseclosedreporting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescaseclosedreporting` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL,
  `closingreason` text NOT NULL,
  `closingremarks` text NOT NULL,
  `stage` text NOT NULL,
  `closingdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasecomments`
--

DROP TABLE IF EXISTS `salescasecomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasecomments` (
  `salescaseref` varchar(40) NOT NULL,
  `commentcode` int(40) NOT NULL AUTO_INCREMENT,
  `hasAudio` tinyint(1) NOT NULL DEFAULT 0,
  `audioPath` varchar(150) NOT NULL,
  `comment` varchar(200) NOT NULL,
  `username` varchar(35) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`commentcode`)
) ENGINE=InnoDB AUTO_INCREMENT=5596 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasecontacts`
--

DROP TABLE IF EXISTS `salescasecontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasecontacts` (
  `salescaseref` varchar(40) NOT NULL,
  `contid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasepo`
--

DROP TABLE IF EXISTS `salescasepo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasepo` (
  `salescasepoindex` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) NOT NULL,
  `pono` varchar(40) NOT NULL DEFAULT 'Not Set Yet',
  `povalue` int(11) NOT NULL DEFAULT 0,
  `pocount` int(11) NOT NULL,
  PRIMARY KEY (`salescasepoindex`)
) ENGINE=InnoDB AUTO_INCREMENT=11404 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasequotations`
--

DROP TABLE IF EXISTS `salescasequotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasequotations` (
  `salescaseref` varchar(40) NOT NULL,
  `quotationfile` varchar(50) NOT NULL,
  `quotationdate` datetime NOT NULL,
  `remarks` varchar(200) NOT NULL,
  `comments` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescaseremarks`
--

DROP TABLE IF EXISTS `salescaseremarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescaseremarks` (
  `salescaseremarksindex` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) NOT NULL,
  `lineno` int(11) NOT NULL,
  `itemcode` varchar(40) NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`salescaseremarksindex`)
) ENGINE=InnoDB AUTO_INCREMENT=174346 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereporting`
--

DROP TABLE IF EXISTS `salescasereporting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereporting` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL,
  KEY `salescaseref` (`salescaseref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingaccountant`
--

DROP TABLE IF EXISTS `salescasereportingaccountant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingaccountant` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingadmin`
--

DROP TABLE IF EXISTS `salescasereportingadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingadmin` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingadnan_sattar`
--

DROP TABLE IF EXISTS `salescasereportingadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingadnan_sattar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingahmad_sohail`
--

DROP TABLE IF EXISTS `salescasereportingahmad_sohail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingahmad_sohail` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingahmad_zaheer`
--

DROP TABLE IF EXISTS `salescasereportingahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingahmad_zaheer` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingahsan_qureshi`
--

DROP TABLE IF EXISTS `salescasereportingahsan_qureshi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingahsan_qureshi` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingali_imran`
--

DROP TABLE IF EXISTS `salescasereportingali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingali_imran` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingali_shabbar`
--

DROP TABLE IF EXISTS `salescasereportingali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingali_shabbar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingammar_hafeez`
--

DROP TABLE IF EXISTS `salescasereportingammar_hafeez`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingammar_hafeez` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingashan_qureshi`
--

DROP TABLE IF EXISTS `salescasereportingashan_qureshi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingashan_qureshi` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingawais_ahmad`
--

DROP TABLE IF EXISTS `salescasereportingawais_ahmad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingawais_ahmad` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingbilal_khalid`
--

DROP TABLE IF EXISTS `salescasereportingbilal_khalid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingbilal_khalid` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingehtisham_asghar`
--

DROP TABLE IF EXISTS `salescasereportingehtisham_asghar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingehtisham_asghar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingejaz_ahmed`
--

DROP TABLE IF EXISTS `salescasereportingejaz_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingejaz_ahmed` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingfaisal_mehmood`
--

DROP TABLE IF EXISTS `salescasereportingfaisal_mehmood`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingfaisal_mehmood` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingimran_butt`
--

DROP TABLE IF EXISTS `salescasereportingimran_butt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingimran_butt` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingirfan_nasar`
--

DROP TABLE IF EXISTS `salescasereportingirfan_nasar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingirfan_nasar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingjalal`
--

DROP TABLE IF EXISTS `salescasereportingjalal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingjalal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingjamal`
--

DROP TABLE IF EXISTS `salescasereportingjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingjamal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmaaz_binzia`
--

DROP TABLE IF EXISTS `salescasereportingmaaz_binzia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmaaz_binzia` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmgmttest`
--

DROP TABLE IF EXISTS `salescasereportingmgmttest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmgmttest` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmohsin_iqbal`
--

DROP TABLE IF EXISTS `salescasereportingmohsin_iqbal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmohsin_iqbal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_ali`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_ali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_ali` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_arif`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_arif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_arif` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_bilal`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_bilal` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_mohsin`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_mohsin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_mohsin` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_sarfraz`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_sarfraz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_sarfraz` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingmuhammad_shehzad`
--

DROP TABLE IF EXISTS `salescasereportingmuhammad_shehzad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingmuhammad_shehzad` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingnadir_naeem`
--

DROP TABLE IF EXISTS `salescasereportingnadir_naeem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingnadir_naeem` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingnasir`
--

DROP TABLE IF EXISTS `salescasereportingnasir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingnasir` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingrao_saeed`
--

DROP TABLE IF EXISTS `salescasereportingrao_saeed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingrao_saeed` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingsajjad_ahmed`
--

DROP TABLE IF EXISTS `salescasereportingsajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingsajjad_ahmed` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingsalesperson_test`
--

DROP TABLE IF EXISTS `salescasereportingsalesperson_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingsalesperson_test` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingshahud_ali`
--

DROP TABLE IF EXISTS `salescasereportingshahud_ali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingshahud_ali` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingsohail_khaliq`
--

DROP TABLE IF EXISTS `salescasereportingsohail_khaliq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingsohail_khaliq` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingtechadmin`
--

DROP TABLE IF EXISTS `salescasereportingtechadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingtechadmin` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingumair_ahmad`
--

DROP TABLE IF EXISTS `salescasereportingumair_ahmad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingumair_ahmad` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingus_help`
--

DROP TABLE IF EXISTS `salescasereportingus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingus_help` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `closingreason` text DEFAULT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingusman_sarwar`
--

DROP TABLE IF EXISTS `salescasereportingusman_sarwar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingusman_sarwar` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescasereportingzaheer_alam`
--

DROP TABLE IF EXISTS `salescasereportingzaheer_alam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescasereportingzaheer_alam` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
  `commentcode` int(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescat`
--

DROP TABLE IF EXISTS `salescat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `parentcatid` tinyint(4) DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1 COMMENT '1 if active 0 if inactive',
  PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescatprod`
--

DROP TABLE IF EXISTS `salescatprod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `manufacturers_id` int(11) NOT NULL,
  `featured` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  KEY `manufacturer_id` (`manufacturers_id`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescattranslations`
--

DROP TABLE IF EXISTS `salescattranslations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescattranslations` (
  `salescatid` tinyint(4) NOT NULL DEFAULT 0,
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL,
  PRIMARY KEY (`salescatid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesglpostings`
--

DROP TABLE IF EXISTS `salesglpostings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` varchar(20) NOT NULL DEFAULT '0',
  `salesglcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesman`
--

DROP TABLE IF EXISTS `salesman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT 0,
  `breakpoint` decimal(10,0) NOT NULL DEFAULT 0,
  `commissionrate2` double NOT NULL DEFAULT 0,
  `current` tinyint(4) NOT NULL COMMENT 'Salesman current (1) or not (0)',
  `target` int(11) NOT NULL DEFAULT 50000000,
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderdetails`
--

DROP TABLE IF EXISTS `salesorderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderdetails` (
  `salesorderdetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `unitrate` double(20,2) NOT NULL,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `discountupdated` varchar(20) NOT NULL,
  `lastcostupdate` date NOT NULL,
  `lastupdatedby` varchar(35) NOT NULL,
  PRIMARY KEY (`salesorderdetailsindex`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  KEY `orderno_2` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=437573 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderdetailsip`
--

DROP TABLE IF EXISTS `salesorderdetailsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderdetailsip` (
  `salesorderdetailsindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `lineoptionno` int(11) NOT NULL,
  `optionitemno` int(11) NOT NULL,
  `internalitemno` int(11) NOT NULL,
  `stkcode` varchar(40) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT 0,
  `unitprice` double(20,2) NOT NULL DEFAULT 0.00,
  `unitrate` double(20,2) NOT NULL,
  `quantity` double NOT NULL DEFAULT 0,
  `estimate` tinyint(4) NOT NULL DEFAULT 0,
  `discountpercent` double NOT NULL DEFAULT 0,
  `actualdispatchdate` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `discountupdated` varchar(20) NOT NULL,
  `lastcostupdate` date NOT NULL,
  `lastupdatedby` varchar(35) NOT NULL,
  PRIMARY KEY (`salesorderdetailsindex`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`)
) ENGINE=InnoDB AUTO_INCREMENT=428661 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderlines`
--

DROP TABLE IF EXISTS `salesorderlines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderlines` (
  `lineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`lineindex`)
) ENGINE=InnoDB AUTO_INCREMENT=269948 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderlinesip`
--

DROP TABLE IF EXISTS `salesorderlinesip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderlinesip` (
  `lineindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `clientrequirements` text CHARACTER SET utf32 NOT NULL,
  PRIMARY KEY (`lineindex`)
) ENGINE=InnoDB AUTO_INCREMENT=241498 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderoptions`
--

DROP TABLE IF EXISTS `salesorderoptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderoptions` (
  `optionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optionno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf8 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` double NOT NULL,
  `uom` varchar(50) NOT NULL DEFAULT '',
  `price` double NOT NULL,
  PRIMARY KEY (`optionindex`)
) ENGINE=InnoDB AUTO_INCREMENT=291478 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderoptionsip`
--

DROP TABLE IF EXISTS `salesorderoptionsip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderoptionsip` (
  `optionindex` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `optiontext` text CHARACTER SET utf32 NOT NULL,
  `stockstatus` varchar(150) CHARACTER SET utf8 NOT NULL,
  `quantity` int(11) NOT NULL,
  `uom` varchar(50) NOT NULL DEFAULT '',
  `price` double NOT NULL,
  PRIMARY KEY (`optionindex`)
) ENGINE=InnoDB AUTO_INCREMENT=265589 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorders`
--

DROP TABLE IF EXISTS `salesorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL,
  `orddate` date DEFAULT NULL,
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `confirmeddate` date DEFAULT NULL,
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date DEFAULT NULL,
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `quotedate` date DEFAULT NULL,
  `poplaced` tinyint(4) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL,
  `contactperson` varchar(40) NOT NULL DEFAULT '',
  `GSTadd` text NOT NULL,
  `services` tinyint(1) NOT NULL,
  `WHT` double NOT NULL,
  `validity` int(11) NOT NULL DEFAULT 15,
  `umqd` tinyint(1) NOT NULL DEFAULT 0,
  `quickQuotation` tinyint(1) NOT NULL DEFAULT 0,
  `withoutItems` tinyint(1) NOT NULL DEFAULT 0,
  `revision` varchar(20) NOT NULL DEFAULT '',
  `revision_for` varchar(20) NOT NULL DEFAULT '',
  `rate_clause` varchar(100) NOT NULL DEFAULT 'usd',
  `rate_validity` date NOT NULL,
  `clause_rates` varchar(150) NOT NULL DEFAULT '',
  `printexchange` tinyint(1) NOT NULL DEFAULT 1,
  `freightclause` varchar(10) NOT NULL,
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  KEY `poplaced` (`poplaced`),
  KEY `salesperson` (`salesperson`),
  KEY `salescaseref` (`salescaseref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesordersip`
--

DROP TABLE IF EXISTS `salesordersip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesordersip` (
  `orderno` int(11) NOT NULL AUTO_INCREMENT,
  `salescaseref` varchar(40) NOT NULL,
  `existing` tinyint(1) NOT NULL,
  `eorderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `orddate` date DEFAULT NULL,
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT 0,
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT 1,
  `freightcost` double NOT NULL DEFAULT 0,
  `advance` int(11) NOT NULL,
  `delivery` int(11) NOT NULL,
  `commisioning` int(11) NOT NULL,
  `after` int(11) NOT NULL,
  `gst` text NOT NULL,
  `afterdays` varchar(10) NOT NULL,
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date DEFAULT NULL,
  `confirmeddate` date DEFAULT NULL,
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT 0,
  `datepackingslipprinted` date DEFAULT NULL,
  `quotation` tinyint(4) NOT NULL DEFAULT 0,
  `quotedate` date DEFAULT NULL,
  `poplaced` tinyint(4) NOT NULL DEFAULT 0,
  `salesperson` varchar(4) NOT NULL,
  `contactperson` varchar(40) NOT NULL DEFAULT '',
  `GSTadd` text NOT NULL,
  `services` tinyint(1) NOT NULL,
  `WHT` double NOT NULL,
  `validity` int(11) NOT NULL DEFAULT 15,
  `umqd` tinyint(1) NOT NULL DEFAULT 0,
  `quickQuotation` tinyint(1) NOT NULL DEFAULT 0,
  `withoutItems` tinyint(1) NOT NULL DEFAULT 0,
  `revision` varchar(20) NOT NULL DEFAULT '',
  `revision_for` varchar(20) NOT NULL DEFAULT '',
  `rate_clause` varchar(100) NOT NULL DEFAULT 'usd',
  `rate_validity` date NOT NULL,
  `clause_rates` varchar(150) NOT NULL DEFAULT '',
  `printexchange` tinyint(1) NOT NULL DEFAULT 1,
  `freightclause` varchar(10) NOT NULL,
  PRIMARY KEY (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=57656 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesadmin`
--

DROP TABLE IF EXISTS `salespersonwisesalesadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesadmin` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesadnan_sattar`
--

DROP TABLE IF EXISTS `salespersonwisesalesadnan_sattar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesadnan_sattar` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesahmad_zaheer`
--

DROP TABLE IF EXISTS `salespersonwisesalesahmad_zaheer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesahmad_zaheer` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesali_imran`
--

DROP TABLE IF EXISTS `salespersonwisesalesali_imran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesali_imran` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesali_shabbar`
--

DROP TABLE IF EXISTS `salespersonwisesalesali_shabbar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesali_shabbar` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesjamal`
--

DROP TABLE IF EXISTS `salespersonwisesalesjamal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesjamal` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesmuhammad_bilal`
--

DROP TABLE IF EXISTS `salespersonwisesalesmuhammad_bilal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesmuhammad_bilal` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalessajjad_ahmed`
--

DROP TABLE IF EXISTS `salespersonwisesalessajjad_ahmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalessajjad_ahmed` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salespersonwisesalesus_help`
--

DROP TABLE IF EXISTS `salespersonwisesalesus_help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salespersonwisesalesus_help` (
  `salespersonwisesalesindex` int(11) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salestypes`
--

DROP TABLE IF EXISTS `salestypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sampleresults`
--

DROP TABLE IF EXISTS `sampleresults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sampleresults` (
  `resultid` bigint(20) NOT NULL AUTO_INCREMENT,
  `sampleid` int(11) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL,
  `targetvalue` varchar(30) NOT NULL,
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `testvalue` varchar(30) NOT NULL DEFAULT '',
  `testdate` date DEFAULT NULL,
  `testedby` varchar(15) NOT NULL DEFAULT '',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `isinspec` tinyint(4) NOT NULL DEFAULT 0,
  `showoncert` tinyint(4) NOT NULL DEFAULT 1,
  `showontestplan` tinyint(4) NOT NULL DEFAULT 1,
  `manuallyadded` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`resultid`),
  KEY `sampleid` (`sampleid`),
  KEY `testid` (`testid`),
  CONSTRAINT `sampleresults_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scripts`
--

DROP TABLE IF EXISTS `scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT 1,
  `description` text NOT NULL,
  PRIMARY KEY (`script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securitygroups`
--

DROP TABLE IF EXISTS `securitygroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT 0,
  `tokenid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securityroles`
--

DROP TABLE IF EXISTS `securityroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL AUTO_INCREMENT,
  `secrolename` text NOT NULL,
  PRIMARY KEY (`secroleid`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securitytokens`
--

DROP TABLE IF EXISTS `securitytokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT 0,
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sellthroughsupport`
--

DROP TABLE IF EXISTS `sellthroughsupport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sellthroughsupport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `narrative` varchar(20) NOT NULL DEFAULT '',
  `rebatepercent` double NOT NULL DEFAULT 0,
  `rebateamount` double NOT NULL DEFAULT 0,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `debtorno` (`debtorno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`),
  KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipmentcharges`
--

DROP TABLE IF EXISTS `shipmentcharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL AUTO_INCREMENT,
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `transtype` smallint(6) NOT NULL DEFAULT 0,
  `transno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT 0,
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime DEFAULT NULL,
  `accumvalue` double NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shippers`
--

DROP TABLE IF EXISTS `shippers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL AUTO_INCREMENT,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shop_vendors`
--

DROP TABLE IF EXISTS `shop_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shop_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vid` varchar(10) NOT NULL,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=567 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsale`
--

DROP TABLE IF EXISTS `shopsale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `orddate` date DEFAULT NULL,
  `payment` varchar(3) NOT NULL,
  `salesman` varchar(50) NOT NULL,
  `mp` tinyint(1) NOT NULL DEFAULT 0,
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `advance` float NOT NULL DEFAULT 0,
  `discount` double NOT NULL,
  `discountPKR` double NOT NULL DEFAULT 0,
  `crname` varchar(50) NOT NULL DEFAULT '',
  `created_by` varchar(50) NOT NULL,
  `dispatchedvia` varchar(50) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `paid` double NOT NULL DEFAULT 0,
  `accounts` tinyint(1) NOT NULL DEFAULT 1,
  `due` date DEFAULT NULL,
  `expected` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4782 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsalecomments`
--

DROP TABLE IF EXISTS `shopsalecomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsalecomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `username` varchar(60) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `orderno` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsalegrb`
--

DROP TABLE IF EXISTS `shopsalegrb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsalegrb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `shopsaleno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `orddate` date DEFAULT NULL,
  `payment` varchar(3) NOT NULL,
  `salesman` varchar(50) NOT NULL,
  `mp` tinyint(1) NOT NULL DEFAULT 0,
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `advance` float NOT NULL DEFAULT 0,
  `discount` double NOT NULL,
  `discountPKR` double NOT NULL DEFAULT 0,
  `crname` varchar(50) NOT NULL DEFAULT '',
  `created_by` varchar(50) NOT NULL,
  `dispatchedvia` varchar(50) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `paid` double NOT NULL DEFAULT 0,
  `accounts` tinyint(1) NOT NULL DEFAULT 1,
  `due` date DEFAULT NULL,
  `expected` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsalelines`
--

DROP TABLE IF EXISTS `shopsalelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsalelines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `notes` text NOT NULL,
  `quantity` double NOT NULL,
  `price` double NOT NULL,
  `uom` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11916 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsalesgrbitems`
--

DROP TABLE IF EXISTS `shopsalesgrbitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsalesgrbitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `rate` double NOT NULL,
  `discountpercent` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shopsalesitems`
--

DROP TABLE IF EXISTS `shopsalesitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shopsalesitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL,
  `lineno` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `rate` double NOT NULL,
  `discountpercent` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13006 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statement_access`
--

DROP TABLE IF EXISTS `statement_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statement_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `can_access` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcategory`
--

DROP TABLE IF EXISTS `stockcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(100) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `issueglact` varchar(20) NOT NULL DEFAULT '0',
  `purchpricevaract` varchar(20) NOT NULL DEFAULT '80000',
  `materialuseagevarac` varchar(20) NOT NULL DEFAULT '80000',
  `wipact` varchar(20) NOT NULL DEFAULT '0',
  `defaulttaxcatid` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcatproperties`
--

DROP TABLE IF EXISTS `stockcatproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT 0,
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT 999999999,
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT 0,
  `minimumvalue` double NOT NULL DEFAULT -999999999,
  `numericvalue` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`stkcatpropid`),
  KEY `categoryid` (`categoryid`),
  CONSTRAINT `stockcatproperties_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4581 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `stockcheckcount`
--

DROP TABLE IF EXISTS `stockcheckcount`;
/*!50001 DROP VIEW IF EXISTS `stockcheckcount`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `stockcheckcount` (
  `stockid` tinyint NOT NULL,
  `lqty` tinyint NOT NULL,
  `sqty` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `stockcheckfreeze`
--

DROP TABLE IF EXISTS `stockcheckfreeze`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT 0,
  `stockcheckdate` date DEFAULT NULL,
  PRIMARY KEY (`stockid`,`loccode`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcounts`
--

DROP TABLE IF EXISTS `stockcounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT 0,
  `reference` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockdescriptiontranslations`
--

DROP TABLE IF EXISTS `stockdescriptiontranslations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockissuance`
--

DROP TABLE IF EXISTS `stockissuance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockissuance` (
  `salesperson` varchar(40) CHARACTER SET utf8 NOT NULL,
  `stockid` varchar(40) CHARACTER SET utf8 NOT NULL,
  `issued` double NOT NULL,
  `returned` double NOT NULL,
  `dc` double NOT NULL,
  `stockissuanceindex` int(11) NOT NULL AUTO_INCREMENT,
  `adjusted` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`stockissuanceindex`)
) ENGINE=InnoDB AUTO_INCREMENT=46233 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockitemproperties`
--

DROP TABLE IF EXISTS `stockitemproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockitemproperties` (
  `stockid` varchar(40) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`stkcatpropid`),
  KEY `stockid` (`stockid`),
  KEY `value` (`value`),
  KEY `stkcatpropid` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_2` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_4` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_5` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stockitemproperties_ibfk_6` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmaster`
--

DROP TABLE IF EXISTS `stockmaster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmaster` (
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `brand` int(11) NOT NULL,
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `actualcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `lastcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `materialcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `labourcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `overheadcost` decimal(20,2) NOT NULL DEFAULT 0.00,
  `lowestlevel` smallint(6) NOT NULL DEFAULT 0,
  `discontinued` tinyint(4) NOT NULL DEFAULT 0,
  `controlled` tinyint(4) NOT NULL DEFAULT 0,
  `eoq` double NOT NULL DEFAULT 0,
  `volume` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `grossweight` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint(4) NOT NULL DEFAULT 1,
  `serialised` tinyint(4) NOT NULL DEFAULT 0,
  `appendfile` varchar(40) NOT NULL DEFAULT 'none',
  `perishable` tinyint(1) NOT NULL DEFAULT 0,
  `decimalplaces` tinyint(4) NOT NULL DEFAULT 0,
  `pansize` double NOT NULL DEFAULT 0,
  `shrinkfactor` double NOT NULL DEFAULT 0,
  `nextserialno` bigint(20) NOT NULL DEFAULT 0,
  `netweight` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `lastcostupdate` date DEFAULT NULL,
  `lastupdatedby` varchar(35) NOT NULL,
  `minimumqty` int(11) DEFAULT NULL,
  `minimumqtyupdatedby` text DEFAULT NULL,
  `minimumqtyupdatedat` datetime DEFAULT current_timestamp(),
  `mnfCode` varchar(100) DEFAULT NULL,
  `mnfpno` varchar(100) NOT NULL,
  `conditionID` int(11) NOT NULL,
  `discount` double NOT NULL DEFAULT 0,
  `scmrecommended` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmoves`
--

DROP TABLE IF EXISTS `stockmoves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT 0,
  `transno` int(11) NOT NULL DEFAULT 0,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT current_timestamp(),
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `prd` smallint(6) NOT NULL DEFAULT 0,
  `reference` varchar(100) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT 1,
  `discountpercent` double NOT NULL DEFAULT 0,
  `standardcost` double NOT NULL DEFAULT 0,
  `show_on_inv_crds` tinyint(4) NOT NULL DEFAULT 1,
  `newqoh` double NOT NULL DEFAULT 0,
  `hidemovt` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` text DEFAULT NULL,
  PRIMARY KEY (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  KEY `reference` (`reference`),
  KEY `stkmoveno` (`stkmoveno`)
) ENGINE=InnoDB AUTO_INCREMENT=531565 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmovestaxes`
--

DROP TABLE IF EXISTS `stockmovestaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxrate` double NOT NULL DEFAULT 0,
  `taxontax` tinyint(4) NOT NULL DEFAULT 0,
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`stkmoveno`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`),
  CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `stockmovestaxes_ibfk_2` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_3` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_4` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockrequest`
--

DROP TABLE IF EXISTS `stockrequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockrequest` (
  `dispatchid` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchdate` datetime NOT NULL DEFAULT current_timestamp(),
  `authorised` tinyint(4) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `narrative` text NOT NULL,
  `recloc` varchar(5) NOT NULL,
  `salesperson` varchar(35) NOT NULL,
  `authorizer` varchar(35) NOT NULL,
  `storemanager` varchar(35) NOT NULL,
  `shiploc` varchar(5) NOT NULL,
  `requestdate` datetime NOT NULL DEFAULT current_timestamp(),
  `receivedate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`dispatchid`)
) ENGINE=InnoDB AUTO_INCREMENT=179529 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockrequestitems`
--

DROP TABLE IF EXISTS `stockrequestitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockrequestitems` (
  `dispatchitemsid` int(11) NOT NULL DEFAULT 0,
  `dispatchid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `qtydelivered` double NOT NULL DEFAULT 0,
  `decimalplaces` int(11) NOT NULL DEFAULT 0,
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT 0,
  `comments` varchar(200) NOT NULL,
  `shipdate` datetime NOT NULL,
  `qtyreceived` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`dispatchitemsid`,`dispatchid`),
  KEY `dispatchid` (`dispatchid`),
  KEY `stockid` (`stockid`),
  KEY `dispatchid_2` (`dispatchid`),
  KEY `stockid_2` (`stockid`),
  CONSTRAINT `stockrequestitems_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockrequestitems_ibfk_3` FOREIGN KEY (`dispatchid`) REFERENCES `stockrequest` (`dispatchid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stockrequestitems_ibfk_4` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockserialitems`
--

DROP TABLE IF EXISTS `stockserialitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime DEFAULT NULL,
  `quantity` double NOT NULL DEFAULT 0,
  `qualitytext` text NOT NULL,
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockserialmoves`
--

DROP TABLE IF EXISTS `stockserialmoves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockmoveno` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockusage`
--

DROP TABLE IF EXISTS `stockusage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockusage` (
  `stockid` varchar(40) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `IGP` int(11) NOT NULL,
  `OGP` int(11) NOT NULL,
  `DC` int(11) NOT NULL,
  KEY `stockid` (`stockid`),
  KEY `loccode` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `substores`
--

DROP TABLE IF EXISTS `substores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `substores` (
  `substoreid` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `locid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`substoreid`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `substorestock`
--

DROP TABLE IF EXISTS `substorestock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `substorestock` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT 0,
  `substoreid` int(11) NOT NULL,
  PRIMARY KEY (`index`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`)
) ENGINE=InnoDB AUTO_INCREMENT=1018503 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `substorestockcheck`
--

DROP TABLE IF EXISTS `substorestockcheck`;
/*!50001 DROP VIEW IF EXISTS `substorestockcheck`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `substorestockcheck` (
  `stockid` tinyint NOT NULL,
  `QTY` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `suppallocs`
--

DROP TABLE IF EXISTS `suppallocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL DEFAULT 0,
  `datealloc` date NOT NULL,
  `transid_allocfrom` int(11) NOT NULL DEFAULT 0,
  `transid_allocto` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`)
) ENGINE=InnoDB AUTO_INCREMENT=10453 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliercontacts`
--

DROP TABLE IF EXISTS `suppliercontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supplierdiscounts`
--

DROP TABLE IF EXISTS `supplierdiscounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplierdiscounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `suppname` varchar(150) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `supptype` tinyint(4) NOT NULL DEFAULT 1,
  `lat` float(10,6) NOT NULL DEFAULT 0.000000,
  `lng` float(10,6) NOT NULL DEFAULT 0.000000,
  `currcode` char(3) NOT NULL DEFAULT '',
  `suppliersince` date DEFAULT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `lastpaid` double NOT NULL DEFAULT 0,
  `lastpaiddate` datetime DEFAULT NULL,
  `bankact` varchar(30) NOT NULL DEFAULT '',
  `bankref` varchar(12) NOT NULL DEFAULT '',
  `bankpartics` varchar(12) NOT NULL DEFAULT '',
  `remittance` tinyint(4) NOT NULL DEFAULT 1,
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 1,
  `factorcompanyid` int(11) NOT NULL DEFAULT 1,
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `url` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliertype`
--

DROP TABLE IF EXISTS `suppliertype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supptrans`
--

DROP TABLE IF EXISTS `supptrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL DEFAULT 0,
  `type` smallint(6) NOT NULL DEFAULT 0,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `suppreference` varchar(20) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL,
  `duedate` date NOT NULL,
  `inputdate` datetime NOT NULL,
  `settled` tinyint(4) NOT NULL DEFAULT 0,
  `processed` int(11) NOT NULL DEFAULT -1,
  `rate` double NOT NULL DEFAULT 1,
  `ovamount` double NOT NULL DEFAULT 0,
  `ovgst` double NOT NULL DEFAULT 0,
  `diffonexch` double NOT NULL DEFAULT 0,
  `chequefilepath` text NOT NULL,
  `chequedepositfilepath` text NOT NULL,
  `cashfilepath` text NOT NULL,
  `alloc` double NOT NULL DEFAULT 0,
  `transtext` text DEFAULT NULL,
  `chequeno` text NOT NULL,
  `remarks` text NOT NULL,
  `hold` tinyint(4) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reversed` tinyint(1) NOT NULL,
  `updated_by` varchar(100) NOT NULL,
  `reverseHistory` text NOT NULL,
  `bankaccount` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `DueDate` (`duedate`),
  KEY `Hold` (`hold`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Settled` (`settled`),
  KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  KEY `SuppReference` (`suppreference`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=15912 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supptranstaxes`
--

DROP TABLE IF EXISTS `supptranstaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `taxamount` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `svcontacts`
--

DROP TABLE IF EXISTS `svcontacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `svcontacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `svid` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `number` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `systypes`
--

DROP TABLE IF EXISTS `systypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT 0,
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tagref` int(11) NOT NULL AUTO_INCREMENT,
  `tagdescription` varchar(50) NOT NULL,
  PRIMARY KEY (`tagref`)
) ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `from_date` datetime NOT NULL,
  `to_date` datetime NOT NULL,
  `assigned_by` varchar(50) NOT NULL,
  `assigned_to` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Due',
  `created_by` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxauthorities`
--

DROP TABLE IF EXISTS `taxauthorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `description` varchar(20) NOT NULL DEFAULT '',
  `taxglcode` varchar(20) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxauthrates`
--

DROP TABLE IF EXISTS `taxauthrates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT 1,
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT 1,
  `taxcatid` tinyint(4) NOT NULL DEFAULT 0,
  `taxrate` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxcategories`
--

DROP TABLE IF EXISTS `taxcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxcatname` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxcatid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxgroups`
--

DROP TABLE IF EXISTS `taxgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxgrouptaxes`
--

DROP TABLE IF EXISTS `taxgrouptaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT 0,
  `taxauthid` tinyint(4) NOT NULL DEFAULT 0,
  `calculationorder` tinyint(4) NOT NULL DEFAULT 0,
  `taxontax` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxprovinces`
--

DROP TABLE IF EXISTS `taxprovinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxprovincename` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxprovinceid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `temp`
--

DROP TABLE IF EXISTS `temp`;
/*!50001 DROP VIEW IF EXISTS `temp`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `temp` (
  `stockid` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `longdescription` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `conditionID` tinyint NOT NULL,
  `mbflag` tinyint NOT NULL,
  `discontinued` tinyint NOT NULL,
  `units` tinyint NOT NULL,
  `decimalplaces` tinyint NOT NULL,
  `value` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tenderitems`
--

DROP TABLE IF EXISTS `tenderitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenders`
--

DROP TABLE IF EXISTS `tenders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int(2) NOT NULL DEFAULT 0,
  `requiredbydate` datetime DEFAULT NULL,
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tendersuppliers`
--

DROP TABLE IF EXISTS `tendersuppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT 0,
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tenderid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `todo`
--

DROP TABLE IF EXISTS `todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `title` text NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `date_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unitsofmeasure`
--

DROP TABLE IF EXISTS `unitsofmeasure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_dashboards`
--

DROP TABLE IF EXISTS `user_dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_dashboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `dashboard_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_permission`
--

DROP TABLE IF EXISTS `user_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `permission` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1036 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vendor_permission`
--

DROP TABLE IF EXISTS `vendor_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `permission` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=418 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `view_name1`
--

DROP TABLE IF EXISTS `view_name1`;
/*!50001 DROP VIEW IF EXISTS `view_name1`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `view_name1` (
  `stockid` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `longdescription` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `conditionID` tinyint NOT NULL,
  `mbflag` tinyint NOT NULL,
  `discontinued` tinyint NOT NULL,
  `units` tinyint NOT NULL,
  `decimalplaces` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `view_name2`
--

DROP TABLE IF EXISTS `view_name2`;
/*!50001 DROP VIEW IF EXISTS `view_name2`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `view_name2` (
  `stockid` tinyint NOT NULL,
  `description` tinyint NOT NULL,
  `longdescription` tinyint NOT NULL,
  `mnfCode` tinyint NOT NULL,
  `mnfpno` tinyint NOT NULL,
  `conditionID` tinyint NOT NULL,
  `mbflag` tinyint NOT NULL,
  `discontinued` tinyint NOT NULL,
  `units` tinyint NOT NULL,
  `decimalplaces` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `transno` int(11) NOT NULL,
  `supptransno` int(11) NOT NULL,
  `voucherno` varchar(15) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `ref` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `instrumentType` text DEFAULT NULL,
  `instrumentNo` text DEFAULT NULL,
  `instrumentDate` date NOT NULL,
  `pid` varchar(10) NOT NULL,
  `partyname` text NOT NULL,
  `dba` text NOT NULL,
  `partytype` text NOT NULL,
  `salesman` text NOT NULL,
  `booked` int(1) NOT NULL,
  `address1` text NOT NULL,
  `address2` text NOT NULL,
  `address3` text NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `bankaccount` varchar(20) NOT NULL,
  `narrative` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherno` (`voucherno`)
) ENGINE=InnoDB AUTO_INCREMENT=3954 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `woitems`
--

DROP TABLE IF EXISTS `woitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT 1,
  `qtyrecd` double NOT NULL DEFAULT 0,
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  `comments` longblob DEFAULT NULL,
  PRIMARY KEY (`wo`,`stockid`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `woproperties`
--

DROP TABLE IF EXISTS `woproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woproperties` (
  `wo` int(11) NOT NULL,
  `wopropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`wo`,`wopropid`),
  KEY `wo` (`wo`),
  KEY `value` (`value`),
  KEY `wopropid` (`wopropid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worequirements`
--

DROP TABLE IF EXISTS `worequirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(40) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `qtypu` double NOT NULL DEFAULT 1,
  `stdcost` double NOT NULL DEFAULT 0,
  `autoissue` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workcentres`
--

DROP TABLE IF EXISTS `workcentres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT 1,
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT 0,
  `overheadrecoveryact` varchar(20) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workorderissuance`
--

DROP TABLE IF EXISTS `workorderissuance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workorderissuance` (
  `engineer` varchar(20) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `issued` int(11) NOT NULL,
  `returned` int(11) NOT NULL,
  `wo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workorders`
--

DROP TABLE IF EXISTS `workorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `costissued` double NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `closecomments` longblob DEFAULT NULL,
  `wotemplateid` char(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `woserialnos`
--

DROP TABLE IF EXISTS `woserialnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT 1,
  `qualitytext` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wotemplate`
--

DROP TABLE IF EXISTS `wotemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wotemplate` (
  `wotemplateid` char(6) NOT NULL DEFAULT '',
  `wotemplatedescription` char(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wotemplateproperties`
--

DROP TABLE IF EXISTS `wotemplateproperties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wotemplateproperties` (
  `wotemplatepropid` int(11) NOT NULL,
  `wotemplateid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT 0,
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT 999999999,
  `minimumvalue` double NOT NULL DEFAULT -999999999,
  `numericvalue` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `ws`
--

DROP TABLE IF EXISTS `ws`;
/*!50001 DROP VIEW IF EXISTS `ws`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `ws` (
  `index` tinyint NOT NULL,
  `loccode` tinyint NOT NULL,
  `stockid` tinyint NOT NULL,
  `quantity` tinyint NOT NULL,
  `substoreid` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `wsr`
--

DROP TABLE IF EXISTS `wsr`;
/*!50001 DROP VIEW IF EXISTS `wsr`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `wsr` (
  `index` tinyint NOT NULL,
  `loccode` tinyint NOT NULL,
  `stockid` tinyint NOT NULL,
  `quantity` tinyint NOT NULL,
  `substoreid` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `www_users`
--

DROP TABLE IF EXISTS `www_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `fullaccess` int(11) NOT NULL DEFAULT 1,
  `cancreatetender` tinyint(1) NOT NULL DEFAULT 0,
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `modulesallowed` varchar(40) NOT NULL DEFAULT '',
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  `displayrecordsmax` int(11) NOT NULL DEFAULT 0,
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT 0,
  `department` int(11) NOT NULL DEFAULT 0,
  `showdashboard` tinyint(1) NOT NULL,
  `pic` blob NOT NULL,
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `inverntoryprice`
--

/*!50001 DROP TABLE IF EXISTS `inverntoryprice`*/;
/*!50001 DROP VIEW IF EXISTS `inverntoryprice`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `inverntoryprice` AS select `manufacturers`.`manufacturers_name` AS `brand`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`description` AS `description`,`stockmaster`.`materialcost` AS `materialcost` from (`stockmaster` join `manufacturers` on(`stockmaster`.`brand` = `manufacturers`.`manufacturers_id`)) order by `stockmaster`.`brand` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `locstockcheck`
--

/*!50001 DROP TABLE IF EXISTS `locstockcheck`*/;
/*!50001 DROP VIEW IF EXISTS `locstockcheck`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `locstockcheck` AS select `locstock`.`stockid` AS `stockid`,sum(`locstock`.`quantity`) AS `QTY` from `locstock` group by `locstock`.`stockid` order by `locstock`.`stockid` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `pettycashbalance`
--

/*!50001 DROP TABLE IF EXISTS `pettycashbalance`*/;
/*!50001 DROP VIEW IF EXISTS `pettycashbalance`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pettycashbalance` AS select `pcashdetails`.`tabcode` AS `tabcode`,sum(`pcashdetails`.`amount`) AS `SUM(amount)` from `pcashdetails` group by `pcashdetails`.`tabcode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `pflist`
--

/*!50001 DROP TABLE IF EXISTS `pflist`*/;
/*!50001 DROP VIEW IF EXISTS `pflist`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pflist` AS select `stockmaster`.`stockid` AS `stockid`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`materialcost` AS `materialcost` from `stockmaster` where `stockmaster`.`brand` = 100 order by `stockmaster`.`materialcost` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `pflista`
--

/*!50001 DROP TABLE IF EXISTS `pflista`*/;
/*!50001 DROP VIEW IF EXISTS `pflista`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `pflista` AS select `manufacturers`.`manufacturers_name` AS `brand`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`description` AS `description`,`stockmaster`.`materialcost` AS `materialcost` from (`stockmaster` join `manufacturers`) order by `stockmaster`.`brand` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `stockcheckcount`
--

/*!50001 DROP TABLE IF EXISTS `stockcheckcount`*/;
/*!50001 DROP VIEW IF EXISTS `stockcheckcount`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `stockcheckcount` AS select `locstockcheck`.`stockid` AS `stockid`,`locstockcheck`.`QTY` AS `lqty`,`substorestockcheck`.`QTY` AS `sqty` from (`substorestockcheck` join `locstockcheck` on(convert(`substorestockcheck`.`stockid` using utf8) = `locstockcheck`.`stockid`)) where `substorestockcheck`.`QTY` <> `locstockcheck`.`QTY` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `substorestockcheck`
--

/*!50001 DROP TABLE IF EXISTS `substorestockcheck`*/;
/*!50001 DROP VIEW IF EXISTS `substorestockcheck`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=TEMPTABLE */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `substorestockcheck` AS select `substorestock`.`stockid` AS `stockid`,sum(`substorestock`.`quantity`) AS `QTY` from `substorestock` group by `substorestock`.`stockid` order by `substorestock`.`stockid` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `temp`
--

/*!50001 DROP TABLE IF EXISTS `temp`*/;
/*!50001 DROP VIEW IF EXISTS `temp`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`irtiza`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `temp` AS select `stockmaster`.`stockid` AS `stockid`,`stockmaster`.`description` AS `description`,`stockmaster`.`longdescription` AS `longdescription`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`conditionID` AS `conditionID`,`stockmaster`.`mbflag` AS `mbflag`,`stockmaster`.`discontinued` AS `discontinued`,`stockmaster`.`units` AS `units`,`stockmaster`.`decimalplaces` AS `decimalplaces`,`stockitemproperties`.`value` AS `value` from (`stockmaster` join `stockitemproperties`) where `stockmaster`.`stockid` = `stockitemproperties`.`stockid` and `stockmaster`.`categoryid` like 'ACB' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_name1`
--

/*!50001 DROP TABLE IF EXISTS `view_name1`*/;
/*!50001 DROP VIEW IF EXISTS `view_name1`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`irtiza`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_name1` AS select `stockmaster`.`stockid` AS `stockid`,`stockmaster`.`description` AS `description`,`stockmaster`.`longdescription` AS `longdescription`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`conditionID` AS `conditionID`,`stockmaster`.`mbflag` AS `mbflag`,`stockmaster`.`discontinued` AS `discontinued`,`stockmaster`.`units` AS `units`,`stockmaster`.`decimalplaces` AS `decimalplaces` from (`stockmaster` join `stockitemproperties`) where `stockmaster`.`stockid` = `stockitemproperties`.`stockid` and `stockmaster`.`categoryid` like 'ACB' and `stockitemproperties`.`value` like '3 Pole' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_name2`
--

/*!50001 DROP TABLE IF EXISTS `view_name2`*/;
/*!50001 DROP VIEW IF EXISTS `view_name2`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`irtiza`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_name2` AS select `stockmaster`.`stockid` AS `stockid`,`stockmaster`.`description` AS `description`,`stockmaster`.`longdescription` AS `longdescription`,`stockmaster`.`mnfCode` AS `mnfCode`,`stockmaster`.`mnfpno` AS `mnfpno`,`stockmaster`.`conditionID` AS `conditionID`,`stockmaster`.`mbflag` AS `mbflag`,`stockmaster`.`discontinued` AS `discontinued`,`stockmaster`.`units` AS `units`,`stockmaster`.`decimalplaces` AS `decimalplaces` from (`stockmaster` join `stockitemproperties`) where `stockmaster`.`stockid` = `stockitemproperties`.`stockid` and `stockmaster`.`categoryid` like 'ACB' and `stockitemproperties`.`value` like ' 65KA' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `ws`
--

/*!50001 DROP TABLE IF EXISTS `ws`*/;
/*!50001 DROP VIEW IF EXISTS `ws`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`irtiza`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ws` AS select `substorestock`.`index` AS `index`,`substorestock`.`loccode` AS `loccode`,`substorestock`.`stockid` AS `stockid`,`substorestock`.`quantity` AS `quantity`,`substorestock`.`substoreid` AS `substoreid` from `substorestock` where `substorestock`.`loccode` = 'WS' and `substorestock`.`quantity` MOD 2 = 1 order by `substorestock`.`quantity` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `wsr`
--

/*!50001 DROP TABLE IF EXISTS `wsr`*/;
/*!50001 DROP VIEW IF EXISTS `wsr`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`irtiza`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `wsr` AS select `ws`.`index` AS `index`,`ws`.`loccode` AS `loccode`,`ws`.`stockid` AS `stockid`,`ws`.`quantity` AS `quantity`,`ws`.`substoreid` AS `substoreid` from `ws` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-12-19 16:40:33
