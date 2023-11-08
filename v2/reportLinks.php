<?php 

	$active = "reports";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	$links = [];
	
	$links['Sales'] = [
		'Comprehensive Salesperson History' 			=> '../reports/sales/salesPersonHistoryFilter.php',
		'Comprehensive Salesperson History (Shop Sale)' 			=> '../reports/shopsale/salesPersonHistoryFilter.php',
		'Salescase Watchlist' 			=> '../salescase/salescaseWatchlist.php',
		'Analyse Sales Cases' 			=> '../selectsalescasefilter.php',
		'Sales Data Analysis' 			=> '../selectsalescaseanalysefilter.php',
		'Sales Data Analysis(New)' 		=> '../selectsalescaseanalysefilternew.php',
		'Sales Data Analysis(Director)' => '../directoranalysefilternew.php',

		'Brand Item Analysis'			=> '../branditemanalysisfilter.php',
		'Print Statements' 				=> '../reports/balance/custstatement/CustStatementFilter.php',
        'WHT cumulative'			=> '../v2/WHTcumulative.php',
        'WHT Statements' 				=> '../reports/balance/custstatement/WHTStatementFilter.php',
        'Reverse Allocation' 				=> '../reports/balance/custstatement/CustStatementFilterRev.php',
		'Customer Payment Terms' 		=> '../v2/updateCustomerPaymentTerms.php',
        'Customer NTN' 		=> '../v2/updateCustomerNTN.php',
		'Invoice Payments Due' 			=> '../v2/paymentsDue.php',
		'Invoice Payments Expected' 	=> '../v2/paymentsExpected.php',
		'CRV Payments Due' 				=> '../v2/paymentsDueCRV.php',
		'Top Items Shopsale' 				=> '../v2/topItemsShopsale.php',
		'Top Items Invoices' 				=> '../v2/topItemsInvoices.php',
        'SCM Report' 				=> '../v2/scmreport.php',
        'salesperson daily activity report' 				=> '../v2/salespersondailyactivityreport.php',
        'Cross-sectional Stock Analysis ' 				=> '../v2/crosssection.php',
		'Top Items Quotations' 				=> '../v2/topItemsQuotation.php',
		'CRV Payments Expected' 		=> '../v2/paymentsExpectedCRV.php',
		'Total Outstanding MT'			=> '../reports/balance/custbalance/CustBalanceSheet.php?location=MT',
        'Total Outstanding MT (SP)'			=> '../reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=MT',
		'Total Outstanding SR'			=> '../reports/balance/custbalance/CustBalanceSheet.php?location=SR',
        'Total Outstanding SR SalesPerson (SP)'			=> '../reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=SR',
		'CRV Total Outstanding SR'		=> '../reports/balance/crvbalance/CRVBalanceSheet.php?location=SR',
        'CRV Total Outstanding (SP)'		=> '../reports/balance/crvbalance/CRVBalanceSheetSalesPerson.php?location=SR',
		'DC List'						=> '../dclistfilter.php',
        'GRB List'						=> '../grblistfilter.php',
		'Pending DC List'						=> '../v2/pendingDCs.php',
		'SalesPerson Monthly Invoices'	=> '../v2/salesPersonInvoices.php',
		'SalesPerson Monthly CRV'		=> '../v2/salesPersonMonthlyCRV.php',
		'Uploaded Cheques'				=> '../v2/uploadedCheques.php',
        'Uploaded Cheques Vendors'				=> '../v2/uploadedChequesVendors.php',
        'Reversed Cheques'				=> '../v2/reversedCheques.php',
        'Reversed Cheques Vendors'				=> '../v2/reversedChequesVendors.php',
		'Shop DC List'					=> '../shopDCList.php',
		'Partial Quick Quotations' 		=> '../quotation/partialQuickQuotations.php',
		'Outstanding Invoices'			=> '../v2/outstanding.php',
		'Outstanding CRV'				=> '../v2/outstandingCRV.php',
        'pending Orders'				=> '../reports/pending/pendingorders.php',
        'Sale Teams Group'				=> '../v2/salesTeamGroup.php'
	];
	
	$links['Inventory'] = [
		'Inventory Item Movements'  => '../StockMovements.php',
		'Price List' 				=> '../pricelist.php',
        'Price List IS' 				=> '../pricelista.php',
        'Price List PS' 				=> '../pricelistb.php',
        'Reorder Level PS' 			=> '../ReorderLevel.php',
		'Reorder Level PS' 			=> '../ReorderLevelPS.php',
		'Inventory Item Usage'		=> '../StockUsage.php',
		'Movement By Loc/Date'		=> '../StockLocMovements.php',
		'Cart Status'				=> '../cartstatus.php',
        'Cart Report'				=> '../v2/cartreport.php',
        'New Cart Report'				=> '../v2/cartreport1.php',
		'Reprint Documents'			=> '../reprintdocuments.php',
		'Reprint Documents (New)'	=> '../reprintdocumentsnew.php',
        'DC Attachments'			=> '../v2/dcattachments.php',
		'Download Negative Stocks'	=> '../PDFStockNegatives.php',
		'OGP Items List'			=> '../ogpitemsfilter.php',
		'IGP Items List'			=> '../igpitemsfilter.php',
		'DC Items List'				=> '../dcitemsfilter.php',
        'GRB Items List'				=> '../grbitemsfilter.php',
		'My Reorder Item Requests'	=> '../reorder/reorderRequest.php?existing',
		'All Reorder Item Requests'	=> '../reorder/reorderRequestAll.php',
		'MPIW Item Sales Reports'	=> '../reports/MPIWItemsList.php',
		'MPIW Wise Item Sales Reports'	=> '../reports/MPIWWiseSalesReports.php',
		'MPIW Items List'	=> '../reports/MPIWItemsWiseList.php',
        'MPI Weekly Projections'	=> '../reports/mpiweeklyprojections.php'
	];
	
	$links['General Ledger'] = [
		'Account Inquiry' 			=> '../SelectGLAccount.php',
		'Trial Balance' 			=> '../GLTrialBalance.php',
		'Account Listing' 			=> '../GLAccountReport.php',
		'Daily Bank Transactions' 	=> '../DailyBankTransactions.php',
		'Recoveries' 				=> '../v2/recoveries.php',
        'Monthly Recoveries Report' 				=> '../v2/monthlyrecoveryreport.php',
        'Monthly Recoveries Report (Clientwise)' 				=> '../v2/clientwisemonthlyrecoveryreport.php',
        'Recoveries (Shop Sale)' 				=> '../v2/recoveriesShopsale.php',
	];
	
	$links['Petty Cash'] = [
		'PC Tab General Report' => '../PcReportTab.php',
        'Expense Listing' => '../v2/expenselisting.php',
	];
	
	$links['Shop'] = [
		'Enter Supplier Payment' => '../payables/suppliersList.php',
        'Market Master Report' => '../shop/parchi/master/masterListBazarParchi.php',

	];
	
	$links['Setup'] = [
		'View Audit Trail' => '../AuditTrail.php',
		'Change Password' => '../changepassword.php',
		'Users Maintenance' => '../WWW_Users.php',
		'Maintain Security Tokens' => '../SecurityTokens.php',
		'Access Permissions Maintenance' => '../WWW_Access.php',
		'Page Security Setting' => '../PageSecurity.php',
		'Update Sales Targets' => '../v2/updateSalesTargets.php',
		'ReOpen Salescase'	=> '../reopenSalescase.php',
        'Panel Costing Rate'	=> '../pc_rate/index.php',
		'Sales Peoples' => '../SalesPeople.php',
		'Sales Areas' => '../Areas.php',
		'Maintain Companies' => '../dba.php',
		'Maintain Substores' => '../SubStores.php',
		'Inventory Location Maintenance' => '../Locations.php',
		'Inventory Category Maintenance' => '../StockCategories.php',
		'Salescase Access Control' => '../setup/SCPC/indexpermission.php',
		'Salesperson Targets (clients)' => '../v2/customerGroups.php',
		'Salesperson Teams' => '../v2/salesTeamGroup.php',
        'Salesperson Achieved Targets (clients)' => '../v2/updateCustomerGroupsSalesTargets.php',
		'User Permissions New' => '../v2/userPermissions.php',
        'Recovery Permissions' => '../v2/recoverypermissions.php',
        'Statement Permissions' => '../v2/statementpromptpermissions.php',
        'Cart Report Permissions' => '../v2/cartreportpermissions.php',
        'Expense Listing Permissions' => '../v2/expenselistingpermission.php',
		'User Permissions (Vendor)' => '../v2/vendorPermissions.php',
		'Currency Exchange Rates' => '../v2/exchangePrices.php',
		'Remove Invoice From InProgress' => '',
	];

?>

<style>

	.item{
		width:100%;
		padding:8px 8px;
		background-color:white;
		border-radius: 7px;
		margin-top: 5px;
	}

</style>

<div class="content-wrapper">

    <!--<section class="content-header">
      
    </section>-->

    <section class="content">
	    
		<div class="row">
			
			<?php foreach($links as $moduleName => $values){ ?>
			<div class="col-md-12 moduleabc">
				
				<h3><?php echo $moduleName; ?></h3>
				
				<div class="row">
					<div class="col-md-12">
					<?php 
						foreach($values as $name => $link){ 
							$ScriptNameArray = explode('?', substr($link,1));
			            	$ScriptNameArray = explode('/',$ScriptNameArray[0]);
							$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[count($ScriptNameArray)-1]];
							if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']))){
					?>
						
						<div class="col-md-3 linkabcd">
							<a href="<?php echo $link; ?>" target="_blank">
							<div class="item">
								<i class="fa fa-eye"></i>
								<?php echo $name; ?>
							</div>
							</a>
						</div>
						
					<?php
							}
						} 
					?>
					</div>
				</div>
			
			</div>
			<?php } ?>
			
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<?php
	include_once("includes/foot.php");
?>