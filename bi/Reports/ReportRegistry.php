<?php

namespace SAHamid\BI\Reports;

use SAHamid\BI\Exception\BIException;

/**
 * Catalog of the current Reports hub and the report-only links in the active
 * sidebar. This is a BI navigation contract; legacy pages remain the source
 * of calculation until a report family is deliberately migrated.
 */
class ReportRegistry
{
	private $reports = array();

	public function __construct()
	{
		$this->registerCurrentHub();
		$this->registerSidebarOnlyReports();
		$this->registerBiNativeReports();
	}

	public function all()
	{
		return array_values($this->reports);
	}

	public function get($id)
	{
		if (!isset($this->reports[$id])) {
			throw new BIException('unknown_report', 'The requested report is not in the BI catalog.', 404, array('report_id' => $id));
		}
		return $this->reports[$id];
	}

	private function registerCurrentHub()
	{
		$this->registerGroup('Sales', array(
			array('comprehensive_salesperson_history', 'Comprehensive Salesperson History', 'reports/sales/salesPersonHistoryFilter.php'),
			array('comprehensive_salesperson_history_shop', 'Comprehensive Salesperson History (Shop Sale)', 'reports/shopsale/salesPersonHistoryFilter.php'),
			array('salescase_watchlist', 'Salescase Watchlist', 'salescase/salescaseWatchlist.php'),
			array('analyse_sales_cases', 'Analyse Sales Cases', 'selectsalescasefilter.php'),
			array('sales_data_analysis', 'Sales Data Analysis', 'selectsalescaseanalysefilter.php'),
			array('sales_data_analysis_new', 'Sales Data Analysis(New)', 'selectsalescaseanalysefilternew.php'),
			array('sales_data_analysis_director', 'Sales Data Analysis(Director)', 'directoranalysefilternew.php'),
			array('brand_item_analysis', 'Brand Item Analysis', 'branditemanalysisfilter.php'),
			array('print_statements', 'Print Statements', 'reports/balance/custstatement/CustStatementFilter.php'),
			array('wht_cumulative', 'WHT cumulative', 'v2/WHTcumulative.php'),
			array('wht_statements', 'WHT Statements', 'reports/balance/custstatement/WHTStatementFilter.php'),
			array('reverse_allocation', 'Reverse Allocation', 'reports/balance/custstatement/CustStatementFilterRev.php'),
			array('customer_payment_terms', 'Customer Payment Terms', 'v2/updateCustomerPaymentTerms.php'),
			array('customer_ntn', 'Customer NTN', 'v2/updateCustomerNTN.php'),
			array('invoice_payments_due', 'Invoice Payments Due', 'v2/paymentsDue.php'),
			array('invoice_payments_expected', 'Invoice Payments Expected', 'v2/paymentsExpected.php'),
			array('crv_payments_due', 'CRV Payments Due', 'v2/paymentsDueCRV.php'),
			array('top_items_shopsale', 'Top Items Shopsale', 'v2/topItemsShopsale.php'),
			array('top_items_invoices', 'Top Items Invoices', 'v2/topItemsInvoices.php'),
			array('scm_report', 'SCM Report', 'v2/scmreport.php'),
			array('salesperson_daily_activity', 'Salesperson daily activity report', 'v2/salespersondailyactivityreport.php'),
			array('cross_sectional_stock_analysis', 'Cross-sectional Stock Analysis', 'v2/crosssection.php'),
			array('top_items_quotations', 'Top Items Quotations', 'v2/topItemsQuotation.php'),
			array('crv_payments_expected', 'CRV Payments Expected', 'v2/paymentsExpectedCRV.php'),
			array('total_outstanding_mt', 'Total Outstanding MT', 'reports/balance/custbalance/CustBalanceSheet.php?location=MT'),
			array('total_outstanding_mt_salesperson', 'Total Outstanding MT (SP)', 'reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=MT'),
			array('total_outstanding_sr', 'Total Outstanding SR', 'reports/balance/custbalance/CustBalanceSheet.php?location=SR'),
			array('total_outstanding_sr_salesperson', 'Total Outstanding SR SalesPerson (SP)', 'reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=SR'),
			array('crv_total_outstanding_sr', 'CRV Total Outstanding SR', 'reports/balance/crvbalance/CRVBalanceSheet.php?location=SR'),
			array('crv_total_outstanding_salesperson', 'CRV Total Outstanding (SP)', 'reports/balance/crvbalance/CRVBalanceSheetSalesPerson.php?location=SR'),
			array('dc_list', 'DC List', 'dclistfilter.php'),
			array('grb_list', 'GRB List', 'grblistfilter.php'),
			array('pending_dc_list', 'Pending DC List', 'v2/pendingDCs.php'),
			array('salesperson_monthly_invoices', 'SalesPerson Monthly Invoices', 'v2/salesPersonInvoices.php'),
			array('salesperson_monthly_crv', 'SalesPerson Monthly CRV', 'v2/salesPersonMonthlyCRV.php'),
			array('uploaded_cheques', 'Uploaded Cheques', 'v2/uploadedCheques.php'),
			array('uploaded_cheques_vendors', 'Uploaded Cheques Vendors', 'v2/uploadedChequesVendors.php'),
			array('reversed_cheques', 'Reversed Cheques', 'v2/reversedCheques.php'),
			array('reversed_cheques_vendors', 'Reversed Cheques Vendors', 'v2/reversedChequesVendors.php'),
			array('shop_dc_list', 'Shop DC List', 'shopDCList.php'),
			array('partial_quick_quotations', 'Partial Quick Quotations', 'quotation/partialQuickQuotations.php'),
			array('outstanding_invoices', 'Outstanding Invoices', 'v2/outstanding.php'),
			array('outstanding_crv', 'Outstanding CRV', 'v2/outstandingCRV.php'),
			array('pending_orders', 'pending Orders', 'reports/pending/pendingorders.php'),
			array('sale_teams_group', 'Sale Teams Group', 'v2/salesTeamGroup.php'),
		));

		$this->registerGroup('Inventory', array(
			array('inventory_item_movements', 'Inventory Item Movements', 'StockMovements.php'),
			array('price_list', 'Price List', 'pricelist.php'),
			array('price_list_is', 'Price List IS', 'pricelista.php'),
			array('price_list_ps', 'Price List PS', 'pricelistb.php'),
			array('reorder_level_ps', 'Reorder Level PS', 'ReorderLevelPS.php'),
			array('inventory_item_usage', 'Inventory Item Usage', 'StockUsage.php'),
			array('movement_by_location_date', 'Movement By Loc/Date', 'StockLocMovements.php'),
			array('cart_status', 'Cart Status', 'cartstatus.php'),
			array('cart_report', 'Cart Report', 'v2/cartreport.php'),
			array('new_cart_report', 'New Cart Report', 'v2/cartreport1.php'),
			array('reprint_documents', 'Reprint Documents', 'reprintdocuments.php'),
			array('reprint_documents_new', 'Reprint Documents (New)', 'reprintdocumentsnew.php'),
			array('dc_attachments', 'DC Attachments', 'v2/dcattachments.php'),
			array('download_negative_stocks', 'Download Negative Stocks', 'PDFStockNegatives.php'),
			array('ogp_items_list', 'OGP Items List', 'ogpitemsfilter.php'),
			array('igp_items_list', 'IGP Items List', 'igpitemsfilter.php'),
			array('dc_items_list', 'DC Items List', 'dcitemsfilter.php'),
			array('grb_items_list', 'GRB Items List', 'grbitemsfilter.php'),
			array('my_reorder_item_requests', 'My Reorder Item Requests', 'reorder/reorderRequest.php?existing'),
			array('all_reorder_item_requests', 'All Reorder Item Requests', 'reorder/reorderRequestAll.php'),
			array('mpiw_item_sales_reports', 'MPIW Item Sales Reports', 'reports/MPIWItemsList.php'),
			array('mpiw_wise_item_sales_reports', 'MPIW Wise Item Sales Reports', 'reports/MPIWWiseSalesReports.php'),
			array('mpiw_items_list', 'MPIW Items List', 'reports/MPIWItemsWiseList.php'),
			array('mpi_weekly_projections', 'MPI Weekly Projections', 'reports/mpiweeklyprojections.php'),
		));

		$this->registerGroup('General Ledger', array(
			array('account_inquiry', 'Account Inquiry', 'SelectGLAccount.php'),
			array('trial_balance', 'Trial Balance', 'GLTrialBalance.php'),
			array('account_listing', 'Account Listing', 'GLAccountReport.php'),
			array('daily_bank_transactions', 'Daily Bank Transactions', 'DailyBankTransactions.php'),
			array('recoveries', 'Recoveries', 'v2/recoveries.php'),
			array('monthly_recoveries', 'Monthly Recoveries Report', 'v2/monthlyrecoveryreport.php'),
			array('monthly_recoveries_clientwise', 'Monthly Recoveries Report (Clientwise)', 'v2/clientwisemonthlyrecoveryreport.php'),
			array('recoveries_shopsale', 'Recoveries (Shop Sale)', 'v2/recoveriesShopsale.php'),
		));

		$this->registerGroup('Accounts Payable', array(
			array('supplier_relationship_intelligence', 'Supplier Relationship Intelligence', 'v2/bi/suppliers.php', 'enhanced', 'v2/bi/suppliers.php'),
		));

		$this->registerGroup('Petty Cash', array(
			array('pc_tab_general_report', 'PC Tab General Report', 'PcReportTab.php'),
			array('expense_listing', 'Expense Listing', 'v2/expenselisting.php', 'enhanced', 'v2/bi/expenses.php'),
		));

		$this->registerGroup('Shop', array(
			array('enter_supplier_payment', 'Enter Supplier Payment', 'payables/suppliersList.php'),
			array('market_master_report', 'Market Master Report', 'shop/parchi/master/masterListBazarParchi.php'),
		));
	}

	private function registerSidebarOnlyReports()
	{
		$this->registerGroup('Shop', array(
			array('mpi_balance_sheet', 'MPI Balance Sheet', 'reports/balance/custbalance/MPICustBalanceSheet.php'),
			array('supplier_balance_sheet', 'Supplier Balance Sheet', 'reports/balance/suppbalance/SuppBalanceSheet.php'),
			array('supplier_balance_sheet_adjusted', 'Supplier Balance Sheet Adjusted', 'reports/balance/suppbalance/SuppBalanceSheetAdjusted.php'),
			array('market_place_business_statements', 'Market Place Business Statements', 'reports/balance/suppstatement/SuppStatementFilter.php'),
			array('vendor_reverse_allocation', 'Vendor Reverse Allocation', 'reports/balance/suppstatement/SuppStatementFilterRev.php'),
		));
	}

	private function registerBiNativeReports()
	{
		$this->register(new ReportDefinition(array(
			'id' => 'bi.invoice_value',
			'title' => 'Invoice Value Analysis',
			'category' => 'Sales',
			'description' => 'Interactive invoice-detail analysis using the published raw invoice-value metric.',
			'legacy_route' => 'v2/invoiceValueReport.php',
			'bi_route' => 'v2/bi/invoice.php',
			'status' => 'enhanced',
			'source' => 'invoice + invoicedetails + invoiceoptions + salescase + salesman',
			'grain' => 'invoice detail and invoice option combination',
			'date_role' => 'invoice.invoicesdate',
			'date_fields' => array('Invoice date'),
			'filters' => array('Date range', 'Salesperson', 'Invoice number'),
			'group_by' => array('Invoice', 'Month', 'Salesperson'),
			'aggregations' => array('Sum', 'Count', 'Average'),
			'visualizations' => array('line', 'bar', 'table'),
			'notes' => array('Uses the existing published invoice-detail formula; linked receivable amounts remain a separate measure.'),
		)));
	}

	private function registerGroup($category, array $definitions)
	{
		foreach ($definitions as $definition) {
			$status = isset($definition[3]) ? $definition[3] : 'compatibility';
			$biRoute = isset($definition[4]) ? $definition[4] : null;
			$description = $this->descriptionFor($category, $definition[1]);
			$source = 'Existing ' . $category . ' report implementation';
			$grain = 'Defined by the existing report implementation';
			$filters = array('Report-specific filters', 'Date range where supported');
			$groupBy = array('Report-specific dimensions');
			$aggregations = array('Report-specific totals');
			$visualizations = array('table');
			if ($definition[0] === 'expense_listing') {
				$description = 'Petty-cash claims with spend, trend, grouping, control, receipt, and data-quality analysis.';
			}
			if ($definition[0] === 'supplier_relationship_intelligence') {
				$description = 'A consolidated supplier relationship view combining ageing, due dates, payments, allocations, supplier contacts, activity, drill-through, and AP controls.';
				$source = 'suppliers + supptrans + suppallocs-backed allocations + paymentterms + systypes';
				$grain = 'Supplier portfolio and supplier transaction';
				$filters = array('Activity date range / as-of date', 'Searchable multi-supplier selection', 'Supplier type and payment terms', 'Transaction type', 'Outstanding / overdue / settled status', 'Ageing bucket', 'Due-date and outstanding-amount ranges', 'Control / attention state');
				$groupBy = array('Supplier', 'Currency', 'Payment terms', 'Ageing bucket', 'Transaction type', 'Month');
				$aggregations = array('Net balance', 'Open payables', 'Due now', 'Overdue', 'Paid / allocated', 'Payment activity', 'Supplier and transaction counts');
				$visualizations = array('interactive doughnut', 'interactive bar and line', 'configurable tables', 'saved views', 'XLSX export');
			}
			$this->register(new ReportDefinition(array(
				'id' => 'menu.' . $definition[0],
				'title' => $definition[1],
				'category' => $category,
				'description' => $description,
				'legacy_route' => $definition[2],
				'bi_route' => $biRoute,
				'status' => $status,
				'source' => $source,
				'grain' => $grain,
				'date_role' => null,
				'date_fields' => array(),
				'filters' => $filters,
				'group_by' => $groupBy,
				'aggregations' => $aggregations,
				'visualizations' => $visualizations,
				'notes' => $status === 'compatibility' ? array('Runs live inside BI using the existing production calculation, permissions, and report-specific filters.') : array(),
			)));
		}
	}

	private function descriptionFor($category, $title)
	{
		$descriptions = array(
			'Sales' => 'Sales, receivables, fulfilment, payment, and pipeline reporting.',
			'Inventory' => 'Inventory, stock movement, replenishment, documents, and MPIW reporting.',
			'General Ledger' => 'General-ledger, banking, recovery, and finance reporting.',
			'Accounts Payable' => 'Supplier balances, payment allocations, ageing, and payable controls.',
			'Petty Cash' => 'Petty-cash and expense-control reporting.',
			'Shop' => 'Shop, marketplace, supplier, and operational reporting.',
		);
		return isset($descriptions[$category]) ? $descriptions[$category] : 'Operational business report.';
	}

	private function register(ReportDefinition $report)
	{
		$this->reports[$report->getId()] = $report;
	}
}
