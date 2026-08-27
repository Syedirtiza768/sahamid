<?php

namespace SAHamid\BI\Metrics;

use SAHamid\BI\Domain\MetricDefinition;
use SAHamid\BI\Exception\BIException;

class MetricRegistry
{
	private $metrics = array();

	public function __construct()
	{
		$this->registerStarterMetrics();
	}

	public function all()
	{
		return $this->metrics;
	}

	public function get($id)
	{
		if (!isset($this->metrics[$id])) {
			throw new BIException('unknown_metric', 'The requested metric is not registered.', 400, array('metric_id' => $id));
		}
		return $this->metrics[$id];
	}

	private function registerStarterMetrics()
	{
		$this->register(new MetricDefinition(
			'sales.invoice_value', 1, 'Invoice Value',
			'Invoice line value for posted, non-returned operational invoices.',
			'awaiting_validation', 'sales_invoice_value',
			'one row per invoice detail and invoice option combination',
			'invoice.invoicesdate',
			'invoicedetails.unitprice × (1 − discountpercent) × invoicedetails.quantity × invoiceoptions.quantity',
			array('invoice.invoiceno', 'invoice.invoicesdate', 'invoice.returned', 'invoice.inprogress', 'invoiceoptions.invoiceno', 'invoiceoptions.invoicelineno', 'invoiceoptions.invoiceoptionno', 'invoicedetails.invoiceno', 'invoicedetails.invoicelineno', 'invoicedetails.invoiceoptionno', 'invoicedetails.unitprice', 'invoicedetails.discountpercent', 'invoicedetails.quantity', 'invoiceoptions.quantity', 'salescase.salescaseref', 'salescase.salesman', 'salesman.salesmanname', 'salesman.salesmancode'),
			array('salesperson'), 'sales_dashboard', 'Finance / Sales owner',
			array('Linked debtortrans.ovamount is gross for exclusive invoices and reconciles only after the date/versioned tax policy is applied; business approval is required.', 'Currency and GL posting-date behavior are not yet modeled.', 'invoice.salesperson is blank in the live dataset; salesperson scope uses salescase.salesman mapped through salesman.salesmanname/salesmancode.', 'Invoice detail rows have legacy zero line/option values; join coverage is observed but must remain under validation.')
		));

		$catalog = array(
			'sales.sales_target' => array('Sales Target', 'salesman.target', 'one row per current salesperson', null, 'Sales Management'),
			'sales.pending_dc_value' => array('Pending DC Value', 'dcs, dcdetails, dcoptions', 'one row per delivery-challan detail and option', 'dcs.orddate', 'Sales Operations'),
			'sales.po_value' => array('PO Value', 'salesorders, salesorderdetails', 'one row per sales-order detail', 'salesorders.orddate', 'Sales Operations'),
			'sales.crv_value' => array('CRV Value', 'shopsale, shopsalelines, debtortrans; shopsalesitems for components', 'one row per completed shop sale', 'shopsale.orddate', 'Finance / Sales owner', array('Use debtortrans.type=750 ovamount reconciled to shopsalelines header pricing; shopsalesitems are component detail, not the sale total.', 'Keep entered shopsale.salesman separate from current custbranch.salesman ownership.', 'Existing CRV reports require an explicit payment predicate and status boundary.')),
			'sales.csv_value' => array('CSV Value', 'shopsale, shopsalelines, debtortrans; shopsalesitems for components', 'one row per completed shop sale', 'shopsale.orddate', 'Finance / Sales owner', array('Use debtortrans.type=750 ovamount reconciled to shopsalelines header pricing; shopsalesitems are component detail, not the sale total.', 'Keep entered shopsale.salesman separate from current custbranch.salesman ownership.', 'Existing CSV reports require an explicit status boundary and must not be mixed with CRV.')),
			'ar.outstanding' => array('Outstanding Receivables', 'debtortrans, invoice, custallocns', 'one row per non-reversed open type-10 debtor transaction', 'invoice.invoicesdate for invoice aging; debtortrans.trandate for transaction analysis', 'Finance'),
			'sales.oc_value' => array('Order Confirmation Value', 'salesorders, salesorderdetails', 'one row per sales-order detail', 'salesorders.orddate', 'Sales Operations'),
			'sales.business_volume' => array('Business Volume', 'invoice, dcs, shopsale', 'composite; component grains require approval', 'component date roles', 'Management'),
			'sales.proper_sale' => array('Proper Sale', 'invoice and/or shop sale sources', 'definition not yet approved', null, 'Management'),
			'sales.shop_dc_value' => array('Shop DC Value', 'shopsale, shopsalelines, debtortrans', 'one row per completed shop sale', 'shopsale.orddate', 'Sales Operations'),
			'sales.cart_value' => array('Cart Value', 'stockissuance, stockmaster, OGP reference tables', 'one row per salesperson/item issuance', null, 'Sales Operations', array('Cart issuance has no source business date or document key; it is an operational allocation snapshot.', 'The existing value uses material cost and a DC-discount heuristic; returned/DC counters are displayed but not a governed deduction.', 'Physical dispatch must be evidenced by OGP state and type-511 stock movements; do not equate issuance with inventory on hand.')),
		);
		foreach ($catalog as $id => $definition) {
			$caveats = isset($definition[5]) ? $definition[5] : array('No numeric result is exposed until the formula, statuses, date role, and reconciliation are approved.');
			$this->register(new MetricDefinition(
				$id, 1, $definition[0], 'Starter catalog entry awaiting business definition and reconciliation.',
				'awaiting_validation', null, $definition[2], $definition[3], 'Not approved.', array($definition[1]), array(), 'sales_dashboard', $definition[4],
				$caveats
			));
		}
	}

	private function register(MetricDefinition $metric)
	{
		$this->metrics[$metric->getId()] = $metric;
	}
}
