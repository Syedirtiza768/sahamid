-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2019 at 02:38 PM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sahamid`
--

--
-- Dumping data for table `permissions`
--

INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES
(1, 'create inward parchi', 'create_inward_parchi', 'User can create inwards parchi'),
(2, 'List Inward Market Slips', 'list_inward_parchi', 'Lists the Market slips made and in progress'),
(3, 'Edit Inward Market Slip', 'edit_inward_parchi', 'Permission to edit the inward market slip'),
(4, 'Inward Market Slip Internal Print', 'inward_parchi_internal', 'Permission To Print Inward Parchi Internal Print'),
(5, 'Attach Inward Market Slip SKU', 'attach_inward_slip_sku', 'User Can Attach Orignal SKU To Inward Market Slip'),
(6, 'Negotiate Price For Inward Market Slip', 'negotiate_inward_slip_price', 'User Can Negotiate the price of items for inward market slip'),
(7, 'Update Inward Market Slip Item Quantituty', 'inward_slip_item_quantity', 'User can Update Inward Market Slip Item Quantituty'),
(8, 'Discard Inward Market Slip', 'discard_inward_slip', 'User Can Discard Inward Market Slip In Progress'),
(9, 'Finalize Inward Market Slip (Save)', 'finalize_market_slip', 'User can finalize (Permanent Save) the inward Market slip'),
(10, 'Make IGP From Inward Market Slip', 'inward_slip_igp', 'User Can Make IGP for inward market slip'),
(11, 'Delete Inward Market Slip Item', 'delete_inward_parchi_item', 'User can delete inward market slip item'),
(12, 'Orignal Vendor Inward Market Slip', 'inward_slip_orignal_vendor', 'User can attach orignal vendor to internal market slip'),
(13, 'Inward Market Slip Ledger', 'inward_slip_ledger', 'User can pass entries in internal market slip ledger'),
(14, 'Attach Inward Parchi DC', 'inward_parchi_dc', 'User can view and attach dc with inward parchi'),
(15, 'All', '*', 'All Permissions Are Granted'),
(16, 'New Shop Vendor ', 'insert_shop_vendor', 'user can add new shop vendors'),
(17, 'Make Shop DC', 'make_shop_dc', 'User can make shop delivery chalan'),
(18, 'Save Shop DC', 'save_shop_dc', 'User can save shop dc'),
(19, 'Add new shop client', 'insert_shop_client', 'user can create shopsale with a new shop client'),
(20, 'Create Shop Sale (CSV/CRV)', 'create_shop_sale', 'User can create new shop sale'),
(21, 'Can Create CSV', 'can_create_csv', 'User can create shop sale as CSV'),
(22, 'Can Create CRV', 'can_create_crv', 'User Can Create CRV type shop sale'),
(23, 'Add Internal Items Shopsale(CSV/CRV)', 'internal_items_shopsale', 'User can add internal items in CSV/CRV'),
(24, 'Internal Items View CSV/CRV', 'shop_sale_internal_view', 'User Can open the add internal items page csv/crv'),
(25, 'Update Internal Item Quantity CSV/CRV', 'update_quantity_shopsale', 'User can update internal attached item quantity CSV/CRV'),
(26, 'Delete Internal Item CSV/CRV', 'delete_internal_item_shopsale', 'User can delete attached internal item'),
(27, 'Finalize ShopSale (CSV/CRV)', 'finalize_shopsale', 'User can finalize the shopsale after checking the internal items and quantity attached.'),
(28, 'Shopsale List', 'shopsale_list', 'User can view shopsale list'),
(29, 'Access Cash Drawer', 'cashDrawer', 'User can access cash drawer'),
(30, 'Quick Quotation Salescase', 'quick_quotation', 'User can create and edit quick quotation'),
(31, 'Reorder Item Approval', 'reorder_items_approval', 'User with this permission can access the reorder item approval page'),
(32, 'Delete Shopsale Line', 'delete_shopsale_line', 'User with this permission can delete shopsale line'),
(33, 'Update Shopsale Line Description', 'update_shopsale_line_desc', 'User with this permission can update shopsale line description'),
(34, 'Change Shopsale Line Quantity', 'change_shopsale_line_qty', 'User can change the line quantity for csv/crv before finalize'),
(35, 'Sent For Invoicing', 'sent_for_invoicing', 'User with this permission has access to invoice check box in reprint document new'),
(36, 'Make Duplicate Quotation', 'make_duplicate_quotation', 'User with this permission can make a duplicate quotation from existing'),
(37, 'Create Quotation Revision', 'create_quotation_revision', 'User with this permission can create Quotation Revision'),
(38, 'Stock Cost Update', 'stock_cost_update', 'user with this permission can update stock price'),
(39, 'Procurement Workspace', 'procurement_workspace', 'User With this permission can access procurement workspace'),
(40, 'Create Procurement Document', 'create_procurement_document', 'User with this permission can create procurement doucment '),
(41, 'Update Procurement Document Item Price', 'update_procurement_document_item_price', 'User with this permission can update procurement document item price'),
(42, 'Update Procurement Document Item Quantity', 'update_procurement_document_item_quantity', 'User with this permission can update procurement document item quantity'),
(43, 'Insert Item in procurement document', 'insert_item_procurement_document', 'User with this permission can insert item in procurement document'),
(44, 'Print Procurement Document', 'print_procurement_document', 'User with this permission can print procurement document'),
(45, 'Create Outward Parchi', 'create_outward_parchi', 'User with this permission can create outward parchi/slip'),
(46, 'Edit Outward Parchi', 'edit_outward_parchi', 'User with this permission can edit outward parchi'),
(47, 'Open Procurement Document', 'open_procurement_document', 'User with this permission can open procurement document'),
(48, 'Update Procurement Document Stage', 'update_procurement_document_stage', 'User with this permission can update procurement document stage'),
(49, 'Cancel Procurement Document', 'cancel_procurement_document', 'User with this permission can cancel procurement document'),
(50, 'Discard Procurement Document', 'discard_procurement_document', 'User with this Permission can discard Procurement Document'),
(51, 'Publish Procurement Document', 'publish_procurement_document', 'User with this permission can publish procurement document'),
(52, 'Outward Market Slip Internal Print', 'outward_parchi_internal', 'Permission To Print Outward Parchi Internal Print'),
(53, 'Attach Outward Market Slip SKU', 'attach_outward_slip_sku', 'User Can Attach Orignal SKU To Outward Market Slip'),
(54, 'Negotiate Price For Outward Market Slip', 'negotiate_outward_slip_price', 'User Can Negotiate the price of items for outward market slip'),
(55, 'Update Outward Market Slip Item Quantity', 'outward_slip_item_quantity', 'User can Update Outward Market Slip Item Quantity'),
(56, 'Discard Outward Market Slip', 'discard_outward_slip', 'User Can Discard Outward Market Slip In Progress'),
(57, 'Finalize Outward Market Slip (Save)', 'finalize_outward_market_slip', 'User can finalize (Permanent Save) the Outward Market slip'),
(58, 'Delete Outward Market Slip Item', 'delete_outward_parchi_item', 'User can delete outward market slip item'),
(59, 'Outward Market Slip Ledger', 'outward_slip_ledger', 'User with this permission can see and update outward market slip ledger'),
(60, 'Attach Outward Market Slip Orignal Client', 'outward_slip_orignal_client', 'User with this permission can attach outward market slip orignal client'),
(61, 'inward market slip external print', 'inward_market_slip_external_print', 'inward market slip external print'),
(62, 'Customer Balance Sheet SR', 'CustomerBalanceSheetSR', 'Customer Balance Sheet SR'),
(63, 'Customer Balance Sheet MT', 'CustomerBalanceSheetMT', 'Customer Balance Sheet MT'),
(64, 'Customer Balance Sheet All', 'CustomerBalanceSheetAll', 'Customer Balance Sheet All'),
(65, 'Update ShopSale Discount', 'update_shopsale_discount', 'User With this permission can update PKR and % Discount for shopsale'),
(66, 'Update Shopsale Line Price', 'update_shopsale_line_price', 'User with this permission can Update Shopsale Line Price'),
(67, 'Create New Dashboard', 'create_new_dashboard', 'User With this permission can create and modify existing dashboards'),
(68, 'Assign User Dashboard', 'assign_user_dashboard', 'User With this permission can assign dashboards to users'),
(69, 'MPI Customer Balance Sheet', 'mpi_cust_balance_sheet', 'User with this permission can access MPI Customer balance sheet'),
(70, 'payments expected report invoice', 'payments_expected_report', 'payments expected report invoice'),
(71, 'payments due report invoice', 'payments_due_report', 'payments due report invoice'),
(72, 'CRV Payments Due Report', 'payments_due_report_crv', 'CRV Payments Due Report'),
(73, 'CRV Payments Expected Report', 'payments_expected_report_crv', 'CRV Payments Expected Report'),
(74, 'MPI Recent Item Purchases', 'mpi_recent_purchases', 'MPI Recent Item Purchases'),
(75, 'display list price', 'display_list_price', 'display list price'),
(76, 'outstanding invoices', 'outstanding_invoices', 'outstanding.php'),
(77, 'outstanding CRV', 'outstanding_CRV', 'outstandingCRV.php'),
(78, 'CRV Balance Sheet', 'CRV_Balance_Sheet', 'CRVBalanceSheet.php'),
(79, 'suppliers List', 'suppliers_List', 'suppliersList.php'),
(80, '', '', ''),
(81, 'update Customer Payment Terms', 'update_Customer_Payment_Terms', 'updateCustomerPaymentTerms.php'),
(82, 'Supp Balance Sheet', 'Supp_Balance_Sheet', 'SuppBalanceSheet.php'),
(83, 'Supplier Allocations', 'Supplier_Allocations', 'SupplierAllocations.php'),
(84, 'test', 'test', ''),
(85, 'Supp Statement Filter', 'Supp_Statement_Filter', 'SuppStatementFilter.php'),
(86, 'ogp items filter.', 'ogp_items_filter', 'ogpitemsfilter.php'),
(87, 'igp items filter', 'igp_items_filter', 'igpitemsfilter.php'),
(88, 'dc items filter', 'dc_items_filter', 'dcitemsfilter.php'),
(89, 'payment Expected CRV', 'payment_Expected_CRV', 'paymentExpectedCRV.php'),
(90, 'payment Due', 'payment_Due', 'paymentDue.php'),
(91, 'payment Expected', 'payment_Expected', 'paymentExpected.php'),
(92, 'payment Due CRV', 'payment_Due_CRV', 'paymentDueCRV.php');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
