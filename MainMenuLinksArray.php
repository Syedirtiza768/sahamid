<?php

/* $Id: MainMenuLinksArray.php 6190 2013-08-12 02:12:02Z rchacon $*/

/* webERP menus with Captions and URLs. */

$ModuleLink = array('orders' => _('Sales'),
 					'AR' => _('Receivables'), 
 					'PO' => _('Purchases'), 
 					'AP' => _('Payables'), 
 					'stock' => _('Inventory'), 
 					'manuf' => _('Engineering'),  
 					'GL' => _('General Ledger'), 
 					'FA' => _('Asset Manager'), 
 					'PC' => _('Petty Cash'), 
 					'system' => _('Setup'), 
 					'Utilities' => _('Utilities'),
					'Shop' => _('Shop'));

$ReportList = array('orders'=>'ord',
					'AR'=>'ar',
					'PO'=>'prch',
					'AP'=>'ap',
					'stock'=>'inv',
					'manuf'=>'man',
					'GL'=>'gl',
					'FA'=>'fa',
					'PC'=>'pc',
					'system'=>'sys',
					'Utilities'=>'utils',
					'Shop'=>'Shop');

/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */

$MenuItems['orders']['Transactions'] = array(	_('') => '/SelectOrderItems.php?NewOrder=Yes',
												_('Enter Counter Sales') => '/CounterSales.php',
												_('Enter Counter Returns') => '/CounterReturns.php',
												_('Print Picking Lists') => '/PDFPickingList.php',
												_('Outstanding Sales Orders/Quotations') => '/SelectSalesOrder.php',
												_('Special Order') => '/SpecialOrder.php',
												_('Recurring Order Template') => '/SelectRecurringSalesOrder.php',
												_('Process Recurring Orders') => '/RecurringSalesOrdersProcess.php');


$MenuItems['orders']['Reports'] = array(_('Sales Order Inquiry') => '/SelectCompletedOrder.php',
										_('Print Price Lists') => '/PDFPriceList.php',
										_('Order Status Report') => '/PDFOrderStatus.php',
										_('Orders Invoiced Reports') => '/PDFOrdersInvoiced.php',
										_('Daily Sales Inquiry') => '/DailySalesInquiry.php',
										_('Sales By Sales Type Inquiry') => '/SalesByTypePeriodInquiry.php',
										_('Sales By Category Inquiry') => '/SalesCategoryPeriodInquiry.php',
										_('Top Sellers Inquiry') => '/SalesTopItemsInquiry.php',
										_('Order Delivery Differences Report') => '/PDFDeliveryDifferences.php',
										_('Delivery In Full On Time (DIFOT) Report') => '/PDFDIFOT.php',
										_('Sales Order Detail Or Summary Inquiries') => '/SalesInquiry.php',
										_('Top Sales Items Inquiry') => '/TopItems.php',
										_('Top Customers Inquiry') => '/SalesTopCustomersInquiry.php',
										_('Worst Sales Items Report') => '/NoSalesItems.php',
										_('Sales With Low Gross Profit Report') => '/PDFLowGP.php',
										_('Sell Through Support Claims Report') => '/PDFSellThroughSupportClaim.php',
										'DC List' => '/dclistfilter.php',
										'Brand Item Analysis' => '/branditemanalysisfilter.php');


$MenuItems['orders']['Maintenance'] = array(_('Create Sales Case') => '/salescase.php?NewSalesCase=Yes',
											_('Select Sales Case') => '/salescase/selectsalescase.php',
											_('Salescase Watchlist') => '/salescase/salescaseWatchlist.php',
											_('Analyse Sales Cases') => '/selectsalescasefilter.php',
											_('Sales Data Analysis') => '/selectsalescaseanalysefilter.php',
											_('Sales Data Analysis(New)') => '/selectsalescaseanalysefilternew.php',
											_('Sales Data Analysis(Director)') => '/directoranalysefilternew.php',
											_('Select Closed Sales Case') => '/selectsalescaseclosed.php',
											_('Create Contract') => '/Contracts.php',
											_('Select Contract') => '/SelectContract.php',
											_('Reopen Salescase') => '/reopenSalescase.php',
											_('Sell Through Support Deals') => '/SellThroughSupport.php');

$MenuItems['AR']['Transactions'] = array(	_('Select DC to Invoice') => '/invoice/newinvoice.php',
											_('Create A Credit Note') => '/SelectCreditItems.php?NewCredit=Yes',
											_('Enter Receipts') => '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',
											_('Allocate Receipts or Credit Notes') => '/CustomerAllocations.php',
											_('Invoice Return') => '/invoice/invoiceReturn.php');

$PrintInvoicesOrCreditNotesScript = '/PrintCustTransPortrait.php';

$MenuItems['AR']['Reports'] = array(	_('Where Allocated Inquiry') => '/CustWhereAlloc.php',
										_('Print Invoices or Credit Notes') => $PrintInvoicesOrCreditNotesScript,
										_('Print Statements') => '/reports/balance/custstatement/CustStatementFilter.php',
										_('Total Outstanding') => '/reports/balance/custbalance/CustBalanceSheet.php',
										_('Sales Analysis Reports') => '/SalesAnalRepts.php',
										_('Aged Customer Balances/Overdues Report') => '/AgedDebtors.php',
										_('Re-Print A Deposit Listing') => '/PDFBankingSummary.php',
										_('Debtor Balances At A Prior Month End') => '/DebtorsAtPeriodEnd.php',
										_('Customer Listing By Area/Salesperson') => '/PDFCustomerList.php',
										_('Sales Graphs') => '/SalesGraph.php',
										_('List Daily Transactions') => '/PDFCustTransListing.php',
										_('Customer Transaction Inquiries')	=> '/CustomerTransInquiry.php');


$MenuItems['AR']['Maintenance'] = array(	_('Add Customer') => '/Customers.php',
											_('Select Customer') => '/SelectCustomer.php');


$MenuItems['AP']['Transactions'] = array(	_('Select Supplier') => '/SelectSupplier.php',
											_('Supplier Allocations') => '/SupplierAllocations.php');


$MenuItems['AP']['Reports'] = array(	_('Aged Supplier Report') => '/AgedSuppliers.php',
										_('Payment Run Report') => '/SuppPaymentRun.php',
										_('Remittance Advices') => '/PDFRemittanceAdvice.php',
										_('Outstanding GRNs Report') => '/OutstandingGRNs.php',
										_('Supplier Balances At A Prior Month End') => '/SupplierBalsAtPeriodEnd.php',
										_('List Daily Transactions') => '/PDFSuppTransListing.php',
										_('Supplier Transaction Inquiries') => '/SupplierTransInquiry.php');


$MenuItems['AP']['Maintenance'] = array(	_('Add Supplier') => '/Suppliers.php',
											_('Select Supplier') => '/SelectSupplier.php',
											_('Maintain Factor Companies') => '/Factors.php');


$MenuItems['PO']['Transactions'] = array(_('New Purchase Order') => '/PO_Header.php?NewOrder=Yes',
													_('Purchase Orders') => '/PO_SelectOSPurchOrder.php',
													_('Purchase Order Grid Entry') => '/PurchaseByPrefSupplier.php',
													_('Create a New Tender') => '/SupplierTenderCreate.php?New=Yes',
													_('Edit Existing Tenders') => '/SupplierTenderCreate.php?Edit=Yes',
													_('Process Tenders and Offers') => '/OffersReceived.php',
													_('Orders to Authorise') => '/PO_AuthoriseMyOrders.php',
													_('Shipment Entry') => '/SelectSupplier.php',
													_('Select A Shipment') => '/Shipt_Select.php');


$MenuItems['PO']['Reports'] = array(	_('Purchase Order Inquiry') => '/PO_SelectPurchOrder.php',
												_('Purchase Order Detail Or Summary Inquiries') => '/POReport.php',
												_('Supplier Price List') => '/SuppPriceList.php');


$MenuItems['PO']['Maintenance'] = array(_('Maintain Supplier Price Lists') => '/SupplierPriceList.php');


$MenuItems['stock']['Transactions'] = array(	_('Receive Purchase Orders') => '/PO_SelectOSPurchOrder.php',
												_('Inventory Transfer') . ' - ' . _('Receive') => '/StockLocTransferReceive.php',
												_('Inventory SubStore Transfers') => '/StockSubStoreTransfers.php?New=Yes',
												_('Inventory Adjustments') => '/StockAdjustments.php?NewAdjustment=Yes',
												_('Reverse Goods Received') => '/ReverseGRN.php',
												_('Enter Stock Counts') => '/StockCounts.php',
												_('Inwards Gate Pass') => '/igp.php?New=Yes',
												_('Outwards Gate Pass') => '/pos.php?New=Yes',
												_('Create a New Stock Request') => '/InterStoreStockRequest.php?New=Yes',
												_('Authorise InterStore Stock Requests') => '/InterStoreStockRequestAuthorisation.php',
												_('Request Status') => '/requeststatus.php',
												_('Fulfill InterStore Stock Requests') => '/InterStoreStockRequestFulfill.php');


$MenuItems['stock']['Reports'] = array(	_('Serial Item Research Tool') => '/StockSerialItemResearch.php',
										_('Print Price Labels') => '/PDFPrintLabel.php',
										_('Reprint GRN') => '/ReprintGRN.php',
										_('Inventory Item Movements') => '/StockMovements.php',
										_('Inventory Item Status') => '/StockStatus.php',
										_('Inventory Item Usage') => '/StockUsage.php',
										_('Inventory Quantities') => '/InventoryQuantities.php',
										_('Reorder Level') => '/ReorderLevel.php',
										_('Price List') => '/pricelist.php',
										_('Stock Dispatch') => '/StockDispatch.php',
										_('Inventory Valuation Report') => '/InventoryValuation.php',
										_('Mail Inventory Valuation Report') => '/MailInventoryValuation.php',
										_('Inventory Planning Report') => '/InventoryPlanning.php',
										_('Inventory Planning Based On Preferred Supplier Data') => '/InventoryPlanningPrefSupplier.php',
										_('Inventory Stock Check Sheets') => '/StockCheck.php',
										_('Make Inventory Quantities CSV') => '/StockQties_csv.php',
										_('Compare Counts Vs Stock Check Data') => '/PDFStockCheckComparison.php',
										_('All Inventory Movements By Location/Date') => '/StockLocMovements.php',
										_('List Inventory Status By Location/Category') => '/StockLocStatus.php',
										_('Cart Status') => '/cartstatus.php',
										_('Reprint Documents') => '/reprintdocuments.php',
										_('Reprint Documents New') => '/reprintdocumentsnew.php',
										_('Historical Stock Quantity By Location/Category') => '/StockQuantityByDate.php',
										_('List Negative Stocks') => '/PDFStockNegatives.php',
										_('Period Stock Transaction Listing') => '/PDFPeriodStockTransListing.php',
										_('Stock Transfer Note') => '/PDFStockTransfer.php',
										_('OGP Items List') => '/ogpitemsfilter.php',
										_('IGP Items List') => '/igpitemsfilter.php',
										_('DC Items List') => '/dcitemsfilter.php');

$MenuItems['stock']['Maintenance'] = array(	_('Add A New Item') => '/Stocks.php',
											_('Search Item') => '/inventory/itemSearch.php',
											_('Sales Category Maintenance') => '/SalesCategories.php',
											_('Brands Maintenance') => '/Manufacturers.php',
											_('Add or Update Prices Based On Costs') => '/PricesBasedOnMarkUp.php',
											_('View or Update Prices Based On Costs') => '/PricesByCost.php',
											_('Reorder Level By Category/Location') => '/ReorderLevelLocation.php');


$MenuItems['manuf']['Transactions'] = array(	_('Work Order Entry') => '/WorkOrderEntry.php',
												_('Select A Work Order') => '/SelectWorkOrder.php');


$MenuItems['manuf']['Reports'] = array(	_('Select A Work Order') => '/SelectWorkOrder.php',
										_('Costed Bill Of Material Inquiry') => '/BOMInquiry.php',
										_('Where Used Inquiry') => '/WhereUsedInquiry.php',
										_('Bill Of Material Listing') => '/BOMListing.php',
										_('Indented Bill Of Material Listing') => '/BOMIndented.php',
										_('List Components Required') => '/BOMExtendedQty.php',
										_('List Materials Not Used Anywhere') => '/MaterialsNotUsed.php',
										_('Indented Where Used Listing') => '/BOMIndentedReverse.php',
										_('MRP') => '/MRPReport.php',
										_('MRP Shortages') => '/MRPShortages.php',
										_('MRP Suggested Purchase Orders') => '/MRPPlannedPurchaseOrders.php',
										_('MRP Suggested Work Orders') => '/MRPPlannedWorkOrders.php',
										_('MRP Reschedules Required') => '/MRPReschedules.php');


$MenuItems['manuf']['Maintenance'] = array(	_('Work Centre') => '/WorkCentres.php',
											_('Work Order Templates') => '/wotemplates.php',
											_('Bills Of Material') => '/BOMs.php',
											_('Copy a Bill Of Materials Between Items') => '/CopyBOM.php',
											_('Master Schedule') => '/MRPDemands.php',
											_('Auto Create Master Schedule') => '/MRPCreateDemands.php',
											_('MRP Calculation') => '/MRP.php');


$MenuItems['GL']['Transactions'] = array(	_('Bank Account Payments Entry') => '/Payments.php?NewPayment=Yes',
											_('Bank Account Receipts Entry') => '/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',
											_('Import Bank Transactions') => '/ImportBankTrans.php',
											_('Bank Account Payments Matching') => '/BankMatching.php?Type=Payments',
											_('Bank Account Receipts Matching') => '/BankMatching.php?Type=Receipts',
											_('Journal Entry') => '/GLJournal.php?NewJournal=Yes');

$MenuItems['GL']['Reports'] = array(	_('Trial Balance') => '/GLTrialBalance.php',
										_('Account Inquiry') => '/SelectGLAccount.php',
										_('Account Listing') => '/GLAccountReport.php',
										_('Account Listing to CSV File') => '/GLAccountCSV.php',
										_('General Ledger Journal Inquiry') => '/GLJournalInquiry.php',
										_('Bank Account Reconciliation Statement') => '/BankReconciliation.php',
										_('Cheque Payments Listing') => '/PDFChequeListing.php',
										_('Daily Bank Transactions') => '/DailyBankTransactions.php',
										_('Profit and Loss Statement') => '/GLProfit_Loss.php',
										_('Balance Sheet') => '/GLBalanceSheet.php',
										_('Tag Reports') => '/GLTagProfit_Loss.php',
										_('Tax Reports') => '/Tax.php');


$MenuItems['GL']['Maintenance'] = array(	_('Account Sections') => '/AccountSections.php',
											_('Account Groups') => '/AccountGroups.php',
											_('GL Accounts') => '/GLAccounts.php',
											_('GL Budgets') => '/GLBudgets.php',
											_('GL Tags') => '/GLTags.php',
											_('Bank Accounts') => '/BankAccounts.php',
											_('Bank Account Authorised Users') => '/BankAccountUsers.php');


$MenuItems['FA']['Transactions'] = array(	_('Add a new Asset') => '/FixedAssetItems.php',
											_('Select an Asset') => '/SelectAsset.php',
											_('Change Asset Location') => '/FixedAssetTransfer.php',
											_('Depreciation Journal') => '/FixedAssetDepreciation.php');


$MenuItems['FA']['Reports'] = array(	_('Asset Register') => '/FixedAssetRegister.php',
										_('My Maintenance Schedule') => '/MaintenanceUserSchedule.php',
										_('Maintenance Reminder Emails') => '/MaintenanceReminders.php');


$MenuItems['FA']['Maintenance'] = array(	_('Asset Categories Maintenance') => '/FixedAssetCategories.php',
											_('Add or Maintain Asset Locations') => '/FixedAssetLocations.php',
											_('Maintenance Tasks') => '/MaintenanceTasks.php');


$MenuItems['PC']['Transactions'] = array(	_('Assign Cash to PC Tab') => '/PcAssignCashToTab.php',
											_('Claim Expenses From PC Tab') => '/PcClaimExpensesFromTab.php',
											_('Expenses Authorisation') => '/PcAuthorizeExpenses.php');



$MenuItems['PC']['Reports'] = array(_('PC Tab General Report') => '/PcReportTab.php', );


$MenuItems['PC']['Maintenance'] = array(	_('Types of PC Tabs') => '/PcTypeTabs.php',
											_('PC Tabs') => '/PcTabs.php',
											_('PC Expenses') => '/PcExpenses.php',
											_('Expenses for Type of PC Tab') => '/PcExpensesTypeTab.php');


$MenuItems['system']['Transactions'] = array(	_('Company Preferences') => '/CompanyPreferences.php',
												_('System Parameters') => '/SystemParameters.php',
												_('Users Maintenance') => '/WWW_Users.php',
												_('Change Password') => '/changepassword.php',
												_('Maintain Security Tokens') => '/SecurityTokens.php',
												_('Access Permissions Maintenance') => '/WWW_Access.php',
												_('Page Security Settings') => '/PageSecurity.php',
												_('Currencies Maintenance') => '/Currencies.php',
												_('Tax Authorities and Rates Maintenance') => '/TaxAuthorities.php',
												_('Tax Group Maintenance') => '/TaxGroups.php',
												_('Dispatch Tax Province Maintenance') => '/TaxProvinces.php',
												_('Tax Category Maintenance') => '/TaxCategories.php',
												_('List Periods Defined') => '/PeriodsInquiry.php',
												_('Report Builder Tool') => '/reportwriter/admin/ReportCreator.php',
												_('View Audit Trail') => '/AuditTrail.php',
												_('Geocode Maintenance') => '/GeocodeSetup.php',
												_('Form Designer') => '/FormDesigner.php',
												_('Web-Store Configuration') => '/ShopParameters.php',
												_('SMTP Server Details') => '/SMTPServer.php',
										       	_('Mailing Group Maintenance') => '/MailingGroupMaintenance.php');


$MenuItems['system']['Reports'] = array(	_('Sales Types') => '/SalesTypes.php',
											_('Customer Types') => '/CustomerTypes.php',
											_('Supplier Types') => '/SupplierTypes.php',
											_('Credit Status') => '/CreditStatus.php',
											_('Payment Terms') => '/PaymentTerms.php',
											_('Set Purchase Order Authorisation levels') => '/PO_AuthorisationLevels.php',
											_('Payment Methods') => '/PaymentMethods.php',
											_('Sales People') => '/SalesPeople.php',
											_('Sales Areas') => '/Areas.php',
											_('Shippers') => '/Shippers.php',
											_('Sales GL Interface Postings') => '/SalesGLPostings.php',
											_('COGS GL Interface Postings') => '/COGSGLPostings.php',
											_('Freight Costs Maintenance') => '/FreightCosts.php',
											_('Discount Matrix') => '/DiscountMatrix.php',
											_('Price Matrix') => '/PriceMatrix.php');


$MenuItems['system']['Maintenance'] = array(	_('Inventory Categories Maintenance') => '/StockCategories.php',
												_('Inventory Locations Maintenance') => '/Locations.php',
												_('Discount Category Maintenance') => '/DiscountCategories.php',
												_('Price Factors Maintenance') => '/pricefactors.php',
												_('Units of Measure') => '/UnitsOfMeasure.php',
												_('MRP Available Production Days') => '/MRPCalendar.php',
												_('MRP Demand Types') => '/MRPDemandTypes.php',
												_('Maintain Sub Stores') => '/SubStores.php',
												_('Maintain Companies') => '/dba.php',
												_('Maintain Internal Stock Categories to User Roles') => '/InternalStockCategoriesByRole.php',
												_('Label Templates Maintenance') => '/Labels.php',
												_('Salescase Access Control') => '/setup/SCPC/indexpermission.php');

$MenuItems['Utilities']['Transactions'] = array(	_('Import GL Payments Receipts Or Journals From CSV') => '/Z_ImportGLTransactions.php',
													_('Change A Customer Code') => '/Z_ChangeCustomerCode.php',
													_('Change A Customer Branch Code') => '/Z_ChangeBranchCode.php',
													_('Change A Supplier Code') => '/Z_ChangeSupplierCode.php',
													_('Change An Inventory Item Code') => '/Z_ChangeStockCode.php',
													_('Change A GL Account Code') => '/Z_ChangeGLAccountCode.php',
													_('Change A Location Code') => '/Z_ChangeLocationCode.php',
													_('Update costs for all BOM items, from the bottom up') => '/Z_BottomUpCosts.php',
													_('Re-apply costs to Sales Analysis') => '/Z_ReApplyCostToSA.php',
													_('Delete sales transactions') => '/Z_DeleteSalesTransActions.php',
													_('Reverse all supplier payments on a specified date') => '/Z_ReverseSuppPaymentRun.php',
													_('Update sales analysis with latest customer data') => '/Z_UpdateSalesAnalysisWithLatestCustomerData.php');


$MenuItems['Utilities']['Reports'] = array(	_('Show Local Currency Total Debtor Balances') => '/Z_CurrencyDebtorsBalances.php',
											_('Show Local Currency Total Suppliers Balances') => '/Z_CurrencySuppliersBalances.php',
											_('Show General Transactions That Do Not Balance') => '/Z_CheckGLTransBalance.php',
											_('List of items without picture') => '/Z_ItemsWithoutPicture.php');


$MenuItems['Utilities']['Maintenance'] = array(	_('Maintain Language Files') => '/Z_poAdmin.php',
												_('Make New Company') => '/Z_MakeNewCompany.php',
												_('Data Export Options') => '/Z_DataExport.php',
												_('Import Stock Items from .csv') => '/Z_ImportStocks.php',
												_('Import Fixed Assets from .csv file') => '/Z_ImportFixedAssets.php',
												_('Create new company template SQL file and submit to webERP') => '/Z_CreateCompanyTemplateFile.php',
												_('Re-calculate brought forward amounts in GL') => '/Z_UpdateChartDetailsBFwd.php',
												_('Re-Post all GL transactions from a specified period') => '/Z_RePostGLFromPeriod.php',
												_('Purge all old prices') => '/Z_DeleteOldPrices.php',
												_('Import Price List from CSV file') => '/Z_ImportPriceList.php');

?>
