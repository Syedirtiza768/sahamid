<?php

namespace SAHamid\BI\Commercial;

/** Draft dictionary; none of these entries imply management publication approval. */
class CommercialDefinitionRegistry
{
	private $definitions;

	public function __construct()
	{
		$this->definitions = array(
			'enquiry_received' => $this->definition('Enquiries received', 'One case is a provisional legacy proxy; prospective reporting uses received events.', 'one salescase or one received event', 'salescase.enquirydate; fallback salescase.commencementdate; sales_performance_enquiry_events.received', 'legacy enquiry date or actual received timestamp', 'awaiting_validation', array('DEC-01', 'DEC-02')),
			'enquiry_unanswered_new' => $this->definition('New enquiries unanswered at cutoff', 'Received enquiry episodes with no qualifying response before the cutoff.', 'one enquiry episode', 'sales_performance_enquiry_events', 'received_at and response events', 'unavailable', array('DEC-03', 'DEC-04')),
			'enquiry_unanswered_backlog' => $this->definition('Unanswered backlog at cutoff', 'Open captured enquiry episodes with no qualifying response at the selected point in time; incomplete capture is disclosed.', 'one open enquiry episode at cutoff', 'sales_performance_enquiry_events', 'episode state at business cutoff', 'provisional_incomplete_capture', array('DEC-03', 'DEC-04')),
			'enquiry_responses' => $this->definition('Enquiry responses', 'Dated captured response events.', 'one response event', 'sales_performance_enquiry_events', 'actual event timestamp', 'provisional_incomplete_capture', array('DEC-04')),
			'enquiry_company_declined' => $this->definition('Company-regretted enquiries', 'Dated company-declined enquiry events with a reason.', 'one company decline event', 'sales_performance_enquiry_events', 'actual event timestamp', 'provisional_incomplete_capture', array('DEC-05')),
			'quotations' => $this->definition('Initial quotation documents', 'Saved quotation documents without a revision marker; legacy date proxy, not confirmed customer delivery.', 'one salesorders.orderno', 'salesorders + salesorderdetails/salesorderoptions', 'salesorders.orddate document-date proxy', 'provisional', array('DEC-06', 'DEC-08', 'DEC-10', 'DEC-11')),
			'revised_quotations' => $this->definition('Revised quotation documents', 'Saved revision documents; full net value as represented by the current source document.', 'one salesorders.orderno', 'salesorders + salesorderdetails/salesorderoptions', 'salesorders.orddate document-date proxy', 'provisional', array('DEC-06', 'DEC-08', 'DEC-09', 'DEC-10', 'DEC-11')),
			'purchase_orders' => $this->definition('Customer purchase orders received', 'Canonical customer PO receipt; OC records remain a separately labelled legacy proxy.', 'one customer PO receipt', 'sales_performance_po_receipts; legacy proxy: ocs', 'actual received_at; legacy proxy: ocs.orddate', 'provisional_incomplete_capture', array('DEC-12')),
			'order_confirmations' => $this->definition('Order confirmations (legacy PO proxy)', 'Count and value ERP OC documents separately; these are not canonical customer PO receipts.', 'one (orderno, pono)', 'ocs + ocdetails + ocoptions', 'ocs.orddate document-date proxy', 'provisional', array('DEC-12')),
			'delivery_challans' => $this->definition('Delivery challans', 'One DC document under a status and date policy; valuation remains a separate stage.', 'one dcs.orderno', 'dcs + dcdetails + dcoptions', 'document activity date; dispatch dates separate', 'provisional', array('DEC-13')),
			'followups' => $this->definition('Structured follow-up activity', 'One captured interaction; qualifies for prior-quotation follow-up only with evidence of an earlier issued quotation.', 'one follow-up event', 'sales_performance_followups', 'actual_activity_at; legacy activity_date', 'provisional', array('DEC-14'))
		);
	}

	public function all() { return $this->definitions; }
	public function get($id) { return isset($this->definitions[$id]) ? $this->definitions[$id] : null; }

	private function definition($label, $meaning, $grain, $source, $dateRole, $status, array $decisions)
	{
		return array(
			'id' => 'sales.commercial.' . $label,
			'label' => $label,
			'meaning' => $meaning,
			'version' => 1,
			'status' => $status,
			'grain' => $grain,
			'distinct_key' => $grain,
			'source' => $source,
			'business_date_role' => $dateRole,
			'report_timezone' => 'Asia/Karachi',
			'value_basis' => $label === 'Structured follow-up activity' ? null : 'PKR, GST removed under provisional source policy',
			'count_overlap' => 'Initial and revised quotations are disjoint; total versions are their union.',
			'attribution' => 'Current case owner and team membership until effective-dated evidence is approved.',
			'coverage' => 'Not captured historically unless a reviewed coverage row states otherwise.',
			'required_permission' => 'sales_performance_view_self or scoped team/company permission',
			'decision_references' => $decisions,
			'approver' => null,
			'effective_date' => null,
			'caveats' => array('Definition is provisional and not approved for external publication.')
		);
	}
}
