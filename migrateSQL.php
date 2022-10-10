ALTER TABLE `cashdrawer_payments` ADD `batch` INT NOT NULL DEFAULT '1' AFTER `id`;

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('pendingDCs.php', '32', '');
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'display list price', 'display_list_price', 'display list price');

--------------------------------

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES ('outstanding invoices', 'outstanding_invoices', 'outstanding.php'), ('outstanding CRV', 'outstanding_CRV', 'outstandingCRV.php'), ('CRV Balance Sheet', 'CRV_Balance_Sheet', 'CRVBalanceSheet.php'), ('suppliers List', 'suppliers_List', 'suppliersList.php');

INSERT INTO `permissions` (`name`, `slug`, `description`) VALUES ('Supp Balance Sheet', 'Supp_Balance_Sheet', 'SuppBalanceSheet.php'), ( 'Supplier Allocations', 'Supplier_Allocations', 'SupplierAllocations.php'),('Supp Statement Filter', 'Supp_Statement_Filter', 'SuppStatementFilter.php'), ('ogp items filter.', 'ogp_items_filter', 'ogpitemsfilter.php'), ('igp items filter', 'igp_items_filter', 'igpitemsfilter.php'), ('dc items filter', 'dc_items_filter', 'dcitemsfilter.php'), ('payment Expected CRV', 'payment_Expected_CRV', 'paymentExpectedCRV.php'), ('payment Due', 'payment_Due', 'paymentDue.php'), ('payment Expected', 'payment_Expected', 'paymentExpected.php'), ('payment Due CRV', 'payment_Due_CRV', 'paymentDueCRV.php')
----------------------------------------------------------------

Add column discountFactor (double) in shopsalesitems

Add column linetotal (double) in shopsalesitems

UPDATE shopsalesitems,shopsalelines SET shopsalesitems.discountFactor=
(SELECT SUM(shopsalelines.quantity*shopsalelines.price) FROM shopsalelines
WHERE shopsalelines.id=shopsalesitems.lineno )

--------------------------------------------------------------------
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('updateCustomerGroupsSalesTargets.php', '32', 'updateCustomerGroupsSalesTargets.php');
----------------------------------------------------------------------
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustBalanceSheetSalesPerson.php', '32', 'Entry of sales order items with both quick entry and part search functions');
-----------------------------------------------------------------------
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustBalanceSheetAPISalesPerson.php', '32', '');
------------------------------------------------------------------------------
ALTER TABLE `shopsalesitems` ADD `discountpercent` DOUBLE NOT NULL DEFAULT '0' AFTER `rate`;
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'Last List Price update', 'last_list_price_update', 'Last List Price update');

----------------------------------------------------------------------------------------
ALTER TABLE `dcs` ADD `services` TINYINT(1) NOT NULL AFTER `GSTAdd`;
-------------------------------------------------------------------------------------------
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'Customer Balance Sheet SR SP', 'CustomerBalanceSheetSRSP', 'Customer Balance Sheet SRSP');
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'Customer Balance Sheet MT SP', 'CustomerBalanceSheetMTSP', 'Customer Balance Sheet MTSP');
////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `debtortrans` ADD `reverseHistory` TEXT NOT NULL AFTER `state`;
/////////////////////////////////////////////////////////////////////////////
ALTER TABLE `reversedallocationhistory` ADD `UserID` TEXT NOT NULL AFTER `revDate`;
.......................................................................................................................................................
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('grb.php', '32', '');
INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES ('514', 'DCGRB', '1');
////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `grb` ADD `dcno` INT NOT NULL AFTER `orderno`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFGRB.php', '26', '');
/////////////////////////////////////////////////////////////////////////////////////////////////////
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFGRBEXTERNAL.php', '26', '');
/////////////////////////////////////////////////////////////////////////////////////////////////////

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'can_create_grb', 'can_create_grb', 'can_create_grb');
///////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `salesorderdetailsip` ADD `unitrate` DOUBLE(20,2) NOT NULL AFTER `unitprice`;
ALTER TABLE `salesorderdetails` ADD `unitrate` DOUBLE(20,2) NOT NULL AFTER `unitprice`
////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `salesordersip` ADD `printexchange` TINYINT(1) NOT NULL AFTER `clause_rates`;
ALTER TABLE `salesorders` ADD `printexchange` TINYINT(1) NOT NULL AFTER `clause_rates`
/////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `salesorders` CHANGE `printexchange` `printexchange` TINYINT(1) NOT NULL DEFAULT '1';
UPDATE `salesorders` SET printexchange=1;

///////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `salesorderdetails` ADD `discountupdated` VARCHAR(20) NOT NULL AFTER `poline`;
ALTER TABLE `salesorderdetailsip` ADD `discountupdated` VARCHAR(20) NOT NULL AFTER `poline`;
////////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `salesorderdetails`  ADD `lastcostupdate` DATE NOT NULL  AFTER `discountupdated`,  ADD `lastupdatedby` VARCHAR(35) NOT NULL  AFTER `lastcostupdate`;
ALTER TABLE `salesorderdetailsip`  ADD `lastcostupdate` DATE NOT NULL  AFTER `discountupdated`,  ADD `lastupdatedby` VARCHAR(35) NOT NULL  AFTER `lastcostupdate`
////////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `inbox` ADD `createdBy` TEXT NOT NULL AFTER `createdAt`;
////////////////////////////////////////////////////////////////////////////////////////////////////////////
-- insert ignore scripts
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES (NULL, 'Master Market List', 'master_market_list', 'Lists the Market slips');
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `supptrans` ADD `chequefilepath` TEXT NOT NULL AFTER `diffonexch`, ADD `chequedepositfilepath` TEXT NOT NULL AFTER `chequefilepath`;
ALTER TABLE `supptrans` ADD `chequeno` TEXT NOT NULL AFTER `transtext`, ADD `remarks` TEXT NOT NULL AFTER `chequeno`;
--add script uploadedChequesVendors.php
//////////////////////////////////////////////////////////////////////////////////////////////
ALTER TABLE `supptrans` ADD `reverseHistory` TEXT NOT NULL AFTER `id`, ADD `bankaccount` INT NOT NULL AFTER `reverseHistory`;
//////////////////////////////////////////////////////////////////////////////////////////////
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierStatementRev.php', '32', '');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppStatementFilterRev.php', '32', '');
ALTER TABLE `supptrans` ADD `reversed` TINYINT(1) NOT NULL AFTER `id`;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('chequeCorrectionVendor.php', '32', 'chequeCorrectionVendor.php');
ALTER TABLE `supptrans` ADD `processed` INT NOT NULL AFTER `settled`;
ALTER TABLE `debtortrans` CHANGE `processed` `processed` INT(11) NOT NULL DEFAULT '-1';
ALTER TABLE `supptrans` CHANGE `processed` `processed` INT(11) NOT NULL DEFAULT '-1';
UPDATE supptrans set processed=-1 WHERE processed=0

--add reverseallocationhistoryvendor table
--add reversedallocationhistorycommentsvendor table
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('filteredListInwardBazarParchi.php', '41', '');
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppBalanceSheetAdjusted.php', '32', '');




























