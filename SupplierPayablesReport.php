<?php

$PageSecurity = 2;
include('includes/session.inc');
include('includes/SupplierPayablesReport.inc');

$Title = _('Supplier & Payables Report');
$filters = SP_ReportReadFilters($_GET);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
$baseUrl = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
$pageSize = 50;
$offset = ($filters['page'] - 1) * $pageSize;
$companyCurrency = isset($_SESSION['CompanyRecord']['currencydefault']) ? $_SESSION['CompanyRecord']['currencydefault'] : '';
$companyDecimals = isset($_SESSION['CompanyRecord']['decimalplaces']) ? (int)$_SESSION['CompanyRecord']['decimalplaces'] : 2;

function SP_ReportPageDate($value) {
	$value = (string)$value;
	return ($value === '' || $value === '0000-00-00') ? '—' : substr($value, 0, 10);
}

function SP_ReportPageAmount($value, $decimals = 2, $currency = '') {
	$formatted = SP_ReportFormatAmount($value, $decimals);
	return $formatted . ($currency !== '' ? ' ' . SP_ReportH($currency) : '');
}

function SP_ReportPageUrl($baseUrl, $filters, $overrides = array()) {
	$query = SP_ReportFiltersQuery($filters, $overrides);
	return $baseUrl . ($query !== '' ? '?' . $query : '');
}

function SP_ReportPageMetric($baseUrl, $filters, $label, $value, $overrides, $detail = '') {
	$url = SP_ReportPageUrl($baseUrl, $filters, $overrides);
	return '<a class="sp-metric" href="' . $url . '"><span class="sp-metric-label">' . SP_ReportH($label) . '</span><strong>' . $value . '</strong>' . ($detail !== '' ? '<small>' . SP_ReportH($detail) . '</small>' : '') . '</a>';
}

function SP_ReportPageStatus($label) {
	$class = strtolower(preg_replace('/[^a-z0-9]+/', '-', (string)$label));
	return '<span class="sp-status sp-status-' . SP_ReportH($class) . '">' . SP_ReportH($label) . '</span>';
}

function SP_ReportPageBar($value, $max, $tone = '') {
	$percent = $max > 0 ? min(100, max(0, ((float)$value / (float)$max) * 100)) : 0;
	return '<span class="sp-bar"><i class="' . SP_ReportH($tone) . '" style="width:' . number_format($percent, 2, '.', '') . '%"></i></span>';
}

$summary = SP_ReportGetSummary($db, $filters);
$agingRows = SP_ReportGetAging($db, $filters);
$topSuppliers = SP_ReportGetTopSuppliers($db, $filters, 5);

$supplierRows = array();
$payableRows = array();
$paymentRows = array();
$supplierCount = 0;
$payableCount = 0;
$paymentCount = 0;
if ($filters['view'] === 'suppliers') {
	$supplierRows = SP_ReportGetSupplierSummary($db, $filters, $pageSize, $offset);
	$supplierCount = SP_ReportCountSupplierSummary($db, $filters);
} elseif ($filters['view'] === 'payables') {
	$payableRows = SP_ReportRunRows($db, SP_ReportGetPayables($db, $filters, $pageSize, $offset), _('Could not retrieve payable details'));
	$payableCount = SP_ReportCountPayables($db, $filters);
} elseif ($filters['view'] === 'payments') {
	$paymentRows = SP_ReportRunRows($db, SP_ReportGetPayments($db, $filters, $pageSize, $offset), _('Could not retrieve payment details'));
	$paymentCount = SP_ReportCountPayments($db, $filters);
}

$supplierDetail = null;
$paymentDetailAnalytics = array();
if (isset($_GET['supplier_detail']) && trim((string)$_GET['supplier_detail']) !== '') {
	$supplierDetail = SP_ReportGetSupplierDetail($db, $filters, trim((string)$_GET['supplier_detail']));
	$detailFilters = $filters;
	$detailFilters['supplier'] = trim((string)$_GET['supplier_detail']);
	$paymentDetailAnalytics = SP_ReportGetPaymentAnalytics($db, $detailFilters);
}

$analytics = array('by_method' => array(), 'by_currency' => array(), 'trend' => array());
if ($filters['view'] === 'payments' || $filters['view'] === 'overview') {
	$analytics = SP_ReportGetPaymentAnalytics($db, $filters);
}

$filterOptions = array('currencies' => array(), 'supplier_types' => array(), 'payment_methods' => array());
$result = DB_query('SELECT currabrev, currency FROM currencies ORDER BY currabrev', $db, '', '', false, false);
if ($result) while ($row = DB_fetch_assoc($result)) $filterOptions['currencies'][] = $row;
$result = DB_query('SELECT typeid, typename FROM suppliertype ORDER BY typename', $db, '', '', false, false);
if ($result) while ($row = DB_fetch_assoc($result)) $filterOptions['supplier_types'][] = $row;
$result = DB_query('SELECT paymentid, paymentname FROM paymentmethods ORDER BY paymentname', $db, '', '', false, false);
if ($result) while ($row = DB_fetch_assoc($result)) $filterOptions['payment_methods'][] = $row;
$today = new DateTime($filters['as_of']);
$due7 = clone $today;
$due7->modify('+7 days');
$due30 = clone $today;
$due30->modify('+30 days');
$due60 = clone $today;
$due60->modify('+60 days');
$due90 = clone $today;
$due90->modify('+90 days');
$upcomingCash = max(0, (float)$summary['upcoming_cash']);
$maxAging = 0;
foreach ($agingRows as $agingRow) $maxAging = max($maxAging, abs((float)$agingRow['amount']));
$maxMethod = 0;
	foreach ($analytics['by_method'] as $methodRow) $maxMethod = max($maxMethod, (float)$methodRow['amount']);

include('includes/header.inc');
echo '<style>
.sp-shell{max-width:1480px;margin:0 auto;padding:8px 0 36px;color:#24364b;font-family:Arial,Helvetica,sans-serif}.sp-head{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;margin:4px 0 18px}.sp-eyebrow{margin:0 0 4px;color:#2e7d78;font-size:11px;font-weight:bold;letter-spacing:1.7px;text-transform:uppercase}.sp-title{margin:0;color:#17324d;font-size:30px;line-height:1.15}.sp-subtitle{margin:7px 0 0;color:#667789;font-size:13px}.sp-export{display:inline-block;background:#2f756f;color:#fff!important;border-radius:5px;padding:11px 16px;text-decoration:none;font-weight:bold;font-size:13px;box-shadow:0 2px 5px rgba(21,50,77,.14)}.sp-export:hover{background:#245e59}.sp-filter{background:#f6f9fb;border:1px solid #d9e3e9;border-radius:7px;padding:14px 16px;margin-bottom:15px}.sp-filter-grid{display:grid;grid-template-columns:repeat(6,minmax(130px,1fr));gap:11px 12px}.sp-field label{display:block;color:#5d7184;font-size:11px;font-weight:bold;margin-bottom:4px}.sp-field input,.sp-field select{box-sizing:border-box;width:100%;height:32px;border:1px solid #c9d5de;border-radius:4px;background:white;color:#24364b;padding:5px 7px;font-size:12px}.sp-field-wide{grid-column:span 2}.sp-actions{display:flex;align-items:center;gap:9px;margin-top:12px}.sp-button{border:0;border-radius:4px;padding:8px 14px;background:#173e5b;color:white;font-weight:bold;cursor:pointer}.sp-reset{color:#2f756f;text-decoration:none;font-size:12px}.sp-filter-note{margin:9px 0 0;color:#7b8995;font-size:11px}.sp-active-filters{display:flex;flex-wrap:wrap;gap:5px;margin-top:10px}.sp-chip{background:#e8f1f1;color:#2e6864;border-radius:12px;padding:4px 9px;font-size:11px}.sp-note{border-left:3px solid #6c9da0;background:#f1f7f7;padding:9px 12px;margin:0 0 16px;color:#58707a;font-size:12px}.sp-metrics{display:grid;grid-template-columns:repeat(5,minmax(145px,1fr));gap:10px;margin-bottom:17px}.sp-metric{min-height:84px;display:flex;flex-direction:column;justify-content:space-between;background:white;border:1px solid #d9e3e9;border-radius:6px;padding:12px 13px;text-decoration:none;color:#24364b;box-shadow:0 2px 7px rgba(23,50,77,.05)}.sp-metric:hover{border-color:#6c9da0;box-shadow:0 3px 10px rgba(23,50,77,.12)}.sp-metric-label{font-size:11px;color:#6d7d8a;font-weight:bold;text-transform:uppercase;letter-spacing:.35px}.sp-metric strong{color:#173e5b;font-size:21px;line-height:1.2}.sp-metric small{font-size:11px;color:#84929c}.sp-tabs{display:flex;gap:4px;align-items:center;border-bottom:1px solid #d9e3e9;margin-bottom:16px}.sp-tab{padding:9px 13px;color:#617586;text-decoration:none;font-size:12px;font-weight:bold;border-bottom:3px solid transparent}.sp-tab:hover,.sp-tab-active{color:#173e5b;border-bottom-color:#2f756f}.sp-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:16px}.sp-panel{background:white;border:1px solid #d9e3e9;border-radius:6px;padding:15px;box-shadow:0 2px 7px rgba(23,50,77,.04)}.sp-panel h2{font-size:15px;color:#173e5b;margin:0 0 3px}.sp-panel-caption{font-size:11px;color:#7b8995;margin:0 0 13px}.sp-aging-row{display:grid;grid-template-columns:145px 1fr 110px 50px;gap:9px;align-items:center;margin:11px 0;font-size:12px}.sp-aging-row a{color:#2f756f;text-decoration:none}.sp-bar{height:7px;display:block;background:#edf1f3;border-radius:6px;overflow:hidden}.sp-bar i{display:block;height:100%;background:#5d9b9a;border-radius:6px}.sp-bar i.warn{background:#d59b52}.sp-bar i.danger{background:#bc6b6b}.sp-number{text-align:right;font-variant-numeric:tabular-nums}.sp-list{width:100%;border-collapse:collapse;font-size:12px}.sp-list th{background:#f4f7f9;color:#5d7184;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.35px;padding:9px 8px;border-bottom:1px solid #d9e3e9;white-space:nowrap}.sp-list td{padding:9px 8px;border-bottom:1px solid #edf1f3;vertical-align:top}.sp-list tr:hover td{background:#fbfdfd}.sp-list .number{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.sp-list a{color:#286f70;text-decoration:none}.sp-list a:hover{text-decoration:underline}.sp-status{display:inline-block;border-radius:10px;padding:3px 7px;font-size:10px;font-weight:bold;white-space:nowrap}.sp-status-active,.sp-status-open,.sp-status-completed{background:#e5f2ed;color:#27715c}.sp-status-overdue,.sp-status-on-hold{background:#f8e8e5;color:#9b4f48}.sp-status-paid{background:#edf0f4;color:#5d6c79}.sp-status-pending-not-recorded{background:#fff2d7;color:#916a24}.sp-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px}.sp-toolbar h2{margin:0}.sp-columns{font-size:11px;color:#6e7e8a}.sp-columns label{margin-left:7px;white-space:nowrap}.sp-columns input{vertical-align:middle}.sp-pagination{display:flex;justify-content:space-between;align-items:center;margin-top:12px;color:#6e7e8a;font-size:11px}.sp-pagination a{color:#2f756f;text-decoration:none;padding:5px 8px;border:1px solid #c9d5de;border-radius:4px;margin-left:4px}.sp-empty{padding:26px 10px;color:#7b8995;text-align:center}.sp-subtle{color:#84929c;font-size:11px}.sp-drawer-backdrop{position:fixed;inset:0;background:rgba(13,32,48,.38);z-index:20}.sp-drawer{position:fixed;top:0;right:0;width:min(520px,94vw);height:100vh;overflow:auto;background:#fff;z-index:21;box-shadow:-8px 0 25px rgba(13,32,48,.2);padding:24px;box-sizing:border-box}.sp-drawer-close{float:right;color:#617586;text-decoration:none;font-size:21px;line-height:1}.sp-drawer h2{margin:0;color:#173e5b;font-size:22px}.sp-drawer .sp-drawer-sub{color:#7b8995;font-size:12px;margin:5px 0 18px}.sp-drawer-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px}.sp-drawer-stat{background:#f5f8fa;border-radius:5px;padding:10px}.sp-drawer-stat span{display:block;font-size:10px;text-transform:uppercase;color:#71818e;font-weight:bold}.sp-drawer-stat strong{display:block;margin-top:5px;color:#173e5b;font-size:16px}.sp-divider{border:0;border-top:1px solid #e7edf0;margin:17px 0}.sp-small-table{font-size:11px}.sp-small-table td,.sp-small-table th{padding:6px}.sp-error-inline{background:#fff0ed;color:#9b4f48;padding:10px;font-size:12px;margin-bottom:14px}@media(max-width:1080px){.sp-filter-grid{grid-template-columns:repeat(3,minmax(130px,1fr))}.sp-metrics{grid-template-columns:repeat(3,minmax(145px,1fr))}}@media(max-width:700px){.sp-head{display:block}.sp-export{margin-top:14px}.sp-filter-grid{grid-template-columns:repeat(2,minmax(120px,1fr))}.sp-field-wide{grid-column:span 2}.sp-metrics{grid-template-columns:repeat(2,minmax(130px,1fr))}.sp-grid-2{grid-template-columns:1fr}.sp-tabs{overflow:auto}.sp-list{min-width:900px}.sp-panel{overflow:auto}}
.sp-loading{position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;background:rgba(13,32,48,.2)}.sp-loading[hidden]{display:none}.sp-loading-progress{position:fixed;top:0;left:0;width:100%;height:4px;background:#dce9eb;overflow:hidden}.sp-loading-progress i{display:block;width:35%;height:100%;background:#2f756f;transform:translateX(-120%);animation:sp-loading-slide 1.15s ease-in-out infinite}@keyframes sp-loading-slide{0%{transform:translateX(-120%)}100%{transform:translateX(390%)}}.sp-loading-card{display:flex;align-items:center;gap:11px;padding:12px 16px;border:1px solid #d9e3e9;border-radius:6px;background:#fff;box-shadow:0 5px 18px rgba(13,32,48,.18);color:#173e5b;font-size:12px;font-weight:bold}.sp-loading-spinner{width:16px;height:16px;border:3px solid #dce9eb;border-top-color:#2f756f;border-radius:50%;animation:sp-loading-spin .8s linear infinite}@keyframes sp-loading-spin{to{transform:rotate(360deg)}}
</style>';

echo '<div class="sp-shell">';
echo '<div id="sp-loading" class="sp-loading" hidden="hidden" role="status" aria-live="polite" aria-busy="true"><div class="sp-loading-progress" role="progressbar" aria-label="' . _('Loading report') . '"><i></i></div><div class="sp-loading-card"><span class="sp-loading-spinner" aria-hidden="true"></span><span>' . _('Refreshing report data…') . '</span></div></div>';
echo '<div class="sp-head"><div><p class="sp-eyebrow">' . _('Accounts payable') . '</p><h1 class="sp-title">' . _('Supplier & Payables') . '</h1><p class="sp-subtitle">' . _('Executive exposure, supplier relationships, aging, and payment behavior') . ' · ' . _('As of') . ' ' . SP_ReportH($filters['as_of']) . '</p></div><a class="sp-export" href="SupplierPayablesReportExport.php?' . SP_ReportFiltersQuery($filters, array('scope' => 'current')) . '">' . _('Export to Excel') . '</a></div>';

echo '<form id="sp-filter-form" class="sp-filter" method="get" action="' . $baseUrl . '"><div class="sp-filter-grid">';
echo '<div class="sp-field"><label for="as_of">' . _('Reporting / as-of date') . '</label><input id="as_of" type="date" name="as_of" value="' . SP_ReportH($filters['as_of']) . '" /></div>';
echo '<div class="sp-field"><label for="invoice_from">' . _('Invoice posted from') . '</label><input id="invoice_from" type="date" name="invoice_from" value="' . SP_ReportH($filters['invoice_from']) . '" /></div>';
echo '<div class="sp-field"><label for="invoice_to">' . _('Invoice posted to') . '</label><input id="invoice_to" type="date" name="invoice_to" value="' . SP_ReportH($filters['invoice_to']) . '" /></div>';
echo '<div class="sp-field"><label for="payment_from">' . _('Payments from') . '</label><input id="payment_from" type="date" name="payment_from" value="' . SP_ReportH($filters['payment_from']) . '" /></div>';
echo '<div class="sp-field"><label for="payment_to">' . _('Payments to') . '</label><input id="payment_to" type="date" name="payment_to" value="' . SP_ReportH($filters['payment_to']) . '" /></div>';
echo '<div class="sp-field sp-field-wide"><label for="supplier">' . _('Supplier / identifier') . '</label><input id="supplier" type="search" name="supplier" value="' . SP_ReportH($filters['supplier']) . '" placeholder="' . _('Search supplier name or code') . '" /></div>';
echo '<div class="sp-field"><label for="supplier_type">' . _('Supplier category') . '</label><select id="supplier_type" name="supplier_type"><option value="all">' . _('All categories') . '</option>';
foreach ($filterOptions['supplier_types'] as $option) echo '<option value="' . (int)$option['typeid'] . '"' . ((string)$filters['supplier_type'] === (string)$option['typeid'] ? ' selected="selected"' : '') . '>' . SP_ReportH($option['typename']) . '</option>';
echo '</select></div>';
echo '<div class="sp-field"><label for="currency">' . _('Currency') . '</label><select id="currency" name="currency"><option value="all">' . _('All currencies') . '</option>';
foreach ($filterOptions['currencies'] as $option) echo '<option value="' . SP_ReportH($option['currabrev']) . '"' . ($filters['currency'] === $option['currabrev'] ? ' selected="selected"' : '') . '>' . SP_ReportH($option['currabrev'] . ' · ' . $option['currency']) . '</option>';
echo '</select></div>';
echo '<div class="sp-field"><label for="invoice_status">' . _('Invoice status') . '</label><select id="invoice_status" name="invoice_status"><option value="all">' . _('All statuses') . '</option><option value="open"' . ($filters['invoice_status'] === 'open' ? ' selected="selected"' : '') . '>' . _('Open') . '</option><option value="overdue"' . ($filters['invoice_status'] === 'overdue' ? ' selected="selected"' : '') . '>' . _('Overdue') . '</option><option value="current"' . ($filters['invoice_status'] === 'current' ? ' selected="selected"' : '') . '>' . _('Current') . '</option><option value="paid"' . ($filters['invoice_status'] === 'paid' ? ' selected="selected"' : '') . '>' . _('Paid / settled') . '</option><option value="on_hold"' . ($filters['invoice_status'] === 'on_hold' ? ' selected="selected"' : '') . '>' . _('On hold') . '</option></select></div>';
echo '<div class="sp-field"><label for="aging_bucket">' . _('Aging bucket') . '</label><select id="aging_bucket" name="aging_bucket"><option value="all">' . _('All buckets') . '</option>';
foreach (array('current' => 'Current', '1_30' => '1–30 overdue', '31_60' => '31–60 overdue', '61_90' => '61–90 overdue', '90_plus' => '>90 overdue') as $key => $label) echo '<option value="' . $key . '"' . ($filters['aging_bucket'] === $key ? ' selected="selected"' : '') . '>' . _($label) . '</option>';
echo '</select></div>';
echo '<div class="sp-field"><label for="due_from">' . _('Due date from') . '</label><input id="due_from" type="date" name="due_from" value="' . SP_ReportH($filters['due_from']) . '" /></div><div class="sp-field"><label for="due_to">' . _('Due date to') . '</label><input id="due_to" type="date" name="due_to" value="' . SP_ReportH($filters['due_to']) . '" /></div>';
echo '<div class="sp-field"><label for="payment_status">' . _('Payment status') . '</label><select id="payment_status" name="payment_status"><option value="all">' . _('All payment records') . '</option><option value="completed"' . ($filters['payment_status'] === 'completed' ? ' selected="selected"' : '') . '>' . _('Completed / bank recorded') . '</option><option value="pending"' . ($filters['payment_status'] === 'pending' ? ' selected="selected"' : '') . '>' . _('Pending / not recorded') . '</option></select></div>';
echo '<div class="sp-field"><label for="payment_method">' . _('Payment method') . '</label><select id="payment_method" name="payment_method"><option value="all">' . _('All methods') . '</option>';
foreach ($filterOptions['payment_methods'] as $option) echo '<option value="' . (int)$option['paymentid'] . '"' . ((string)$filters['payment_method'] === (string)$option['paymentid'] ? ' selected="selected"' : '') . '>' . SP_ReportH($option['paymentname']) . '</option>';
echo '</select></div>';
echo '<div class="sp-field"><label for="project">' . _('Project / job reference') . '</label><input id="project" type="search" name="project" value="' . SP_ReportH($filters['project']) . '" /></div>';
echo '</div><div class="sp-actions"><button class="sp-button" type="submit">' . _('Apply filters') . '</button><a class="sp-reset" href="' . $baseUrl . '">' . _('Reset all') . '</a></div><p class="sp-filter-note">' . _('Invoice filters apply to invoice totals, aging, supplier exposure, and payable detail. Payment filters apply to payment totals, timing, supplier payments, and payment detail. Supplier, category, currency, and project filters apply to matching records.') . '</p></form>';

$activeChips = array();
foreach (array('supplier' => 'Supplier', 'supplier_type' => 'Category', 'currency' => 'Currency', 'invoice_status' => 'Invoice status', 'aging_bucket' => 'Aging', 'payment_status' => 'Payment status', 'payment_method' => 'Payment method', 'project' => 'Project') as $key => $label) {
	if ($filters[$key] !== '' && $filters[$key] !== 'all') $activeChips[] = '<span class="sp-chip">' . SP_ReportH($label . ': ' . $filters[$key]) . '</span>';
}
if (count($activeChips) > 0) echo '<div class="sp-active-filters">' . implode('', $activeChips) . '</div>';

echo '<div class="sp-note"><strong>' . _('How this report reconciles') . ':</strong> ' . _('Outstanding exposure is the supplier ledger amount less recorded allocations. Invoices include tax as stored in supptrans. The current app has no supplier active flag, dispute reason, legal-entity, department, or approver fields; all supplier master records are treated as active, and hold is shown as the available exception status. Project/job references come from GL job references when present.') . '</div>';

echo '<div class="sp-metrics">';
echo SP_ReportPageMetric($baseUrl, $filters, _('Total outstanding'), SP_ReportPageAmount($summary['total_outstanding'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'page' => 1), _('Base-currency equivalent'));
echo SP_ReportPageMetric($baseUrl, $filters, _('Overdue payables'), SP_ReportPageAmount($summary['total_overdue'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'overdue', 'page' => 1), _('Due before selected as-of date'));
echo SP_ReportPageMetric($baseUrl, $filters, _('Due in 7 days'), SP_ReportPageAmount($summary['due_7'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'due_from' => $filters['as_of'], 'due_to' => $due7->format('Y-m-d'), 'page' => 1));
echo SP_ReportPageMetric($baseUrl, $filters, _('Due in 30 days'), SP_ReportPageAmount($summary['due_30'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'due_from' => $filters['as_of'], 'due_to' => $due30->format('Y-m-d'), 'page' => 1));
echo SP_ReportPageMetric($baseUrl, $filters, _('Due in 60 days'), SP_ReportPageAmount($summary['due_60'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'due_from' => $filters['as_of'], 'due_to' => $due60->format('Y-m-d'), 'page' => 1));
echo SP_ReportPageMetric($baseUrl, $filters, _('Due in 90 days'), SP_ReportPageAmount($summary['due_90'], $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'due_from' => $filters['as_of'], 'due_to' => $due90->format('Y-m-d'), 'page' => 1));
echo SP_ReportPageMetric($baseUrl, $filters, _('Paid in selected period'), SP_ReportPageAmount($summary['paid_period'], $companyDecimals, $companyCurrency), array('view' => 'payments', 'page' => 1), SP_ReportPageDate($filters['payment_from']) . ' → ' . SP_ReportPageDate($filters['payment_to']));
echo SP_ReportPageMetric($baseUrl, $filters, _('Active suppliers'), SP_ReportH($summary['active_suppliers']), array('view' => 'suppliers', 'page' => 1), _('Supplier master records as of date'));
echo SP_ReportPageMetric($baseUrl, $filters, _('Unpaid invoices'), SP_ReportH($summary['unpaid_invoices']), array('view' => 'payables', 'invoice_status' => 'open', 'page' => 1));
echo SP_ReportPageMetric($baseUrl, $filters, _('On hold / blocked'), SP_ReportH($summary['on_hold_invoices']), array('view' => 'payables', 'invoice_status' => 'on_hold', 'page' => 1), _('Hold flag in supplier ledger'));
echo SP_ReportPageMetric($baseUrl, $filters, _('Average payment time'), $summary['average_payment_days'] === null ? '—' : SP_ReportH(number_format($summary['average_payment_days'], 1) . ' days'), array('view' => 'payments', 'page' => 1), _('Invoice date to payment date'));
echo SP_ReportPageMetric($baseUrl, $filters, _('On-time payment rate'), $summary['on_time_rate'] === null ? '—' : SP_ReportH(number_format($summary['on_time_rate'], 1) . '%'), array('view' => 'payments', 'page' => 1), _('Allocated invoice payments'));
echo SP_ReportPageMetric($baseUrl, $filters, _('Upcoming cash requirement'), SP_ReportPageAmount($upcomingCash, $companyDecimals, $companyCurrency), array('view' => 'payables', 'invoice_status' => 'open', 'due_from' => $filters['as_of'], 'due_to' => $due30->format('Y-m-d'), 'page' => 1), _('Next 30 days, excluding overdue'));
echo '</div>';

echo '<nav class="sp-tabs">';
foreach (array('overview' => 'Executive overview', 'suppliers' => 'Supplier summary', 'payables' => 'Payables detail', 'payments' => 'Payment analysis', 'aging' => 'Aging analysis') as $key => $label) echo '<a class="sp-tab' . ($filters['view'] === $key ? ' sp-tab-active' : '') . '" href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => $key, 'page' => 1)) . '">' . _($label) . '</a>';
echo '</nav>';

if ($filters['view'] === 'overview') {
	echo '<div class="sp-grid-2"><section class="sp-panel"><h2>' . _('Accounts payable aging') . '</h2><p class="sp-panel-caption">' . _('Selected as-of date') . ': ' . SP_ReportH($filters['as_of']) . ' · ' . _('click a bucket to inspect invoices') . '</p>';
	foreach ($agingRows as $row) {
		$bucketUrl = SP_ReportPageUrl($baseUrl, $filters, array('view' => 'payables', 'aging_bucket' => $row['aging_bucket'], 'page' => 1));
		$tone = in_array($row['aging_bucket'], array('61_90', '90_plus'), true) ? 'danger' : (in_array($row['aging_bucket'], array('31_60'), true) ? 'warn' : '');
		echo '<div class="sp-aging-row"><a href="' . $bucketUrl . '">' . SP_ReportH(SP_ReportAgeBucketLabel($row['aging_bucket'])) . '</a>' . SP_ReportPageBar(abs($row['amount']), $maxAging, $tone) . '<span class="sp-number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$row['invoice_count'] . '</span></div>';
	}
	echo '</section><section class="sp-panel"><h2>' . _('Payment behavior') . '</h2><p class="sp-panel-caption">' . _('Payments recorded between') . ' ' . SP_ReportH($filters['payment_from']) . ' ' . _('and') . ' ' . SP_ReportH($filters['payment_to']) . '</p>';
	if (count($analytics['by_method']) === 0) echo '<div class="sp-empty">' . _('No payment records match the selected period and filters.') . '</div>';
	foreach ($analytics['by_method'] as $row) echo '<div class="sp-aging-row"><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => 'payments', 'page' => 1)) . '">' . SP_ReportH($row['label']) . '</a>' . SP_ReportPageBar($row['amount'], $maxMethod) . '<span class="sp-number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$row['count'] . '</span></div>';
	if (count($analytics['by_currency']) > 0) {
		echo '<hr class="sp-divider"><p class="sp-panel-caption">' . _('Payments by currency') . '</p>';
		foreach ($analytics['by_currency'] as $row) echo '<div class="sp-aging-row"><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => 'payments', 'currency' => $row['label'], 'page' => 1)) . '">' . SP_ReportH($row['label']) . '</a><span></span><span class="sp-number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$row['count'] . '</span></div>';
	}
	echo '<hr class="sp-divider"><p class="sp-panel-caption">' . _('Timing allocation') . ': ' . SP_ReportH($summary['timing_allocated'] > 0 ? SP_ReportPageAmount($summary['timing_allocated'], $companyDecimals, $companyCurrency) . ' allocated' : 'No linked invoice payments') . '</p></section></div>';

	echo '<div class="sp-grid-2"><section class="sp-panel"><div class="sp-toolbar"><h2>' . _('Top suppliers by outstanding exposure') . '</h2><a class="sp-subtle" href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => 'suppliers', 'page' => 1)) . '">' . _('View all') . '</a></div><table class="sp-list"><thead><tr><th>' . _('Supplier') . '</th><th>' . _('Category') . '</th><th class="number">' . _('Outstanding') . '</th><th class="number">' . _('Overdue') . '</th><th>' . _('Next due') . '</th></tr></thead><tbody>';
	if (count($topSuppliers) === 0) echo '<tr><td colspan="5" class="sp-empty">' . _('No supplier exposure found for these filters.') . '</td></tr>';
	foreach ($topSuppliers as $row) echo '<tr><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('supplier_detail' => $row['supplierid'])) . '">' . SP_ReportH($row['suppname']) . '</a><br><span class="sp-subtle">' . SP_ReportH($row['supplierid']) . '</span></td><td>' . SP_ReportH($row['supplier_category']) . '</td><td class="number">' . SP_ReportPageAmount($row['outstanding'], $companyDecimals, $companyCurrency) . '</td><td class="number">' . SP_ReportPageAmount($row['overdue'], $companyDecimals, $companyCurrency) . '</td><td>' . SP_ReportPageDate($row['next_due']) . '</td></tr>';
	echo '</tbody></table></section><section class="sp-panel"><div class="sp-toolbar"><h2>' . _('Exceptions requiring attention') . '</h2><a class="sp-subtle" href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => 'payables', 'invoice_status' => 'on_hold', 'page' => 1)) . '">' . _('View holds') . '</a></div><div class="sp-drawer-stats"><div class="sp-drawer-stat"><span>' . _('Overdue exposure') . '</span><strong>' . SP_ReportPageAmount($summary['total_overdue'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('On-hold invoices') . '</span><strong>' . SP_ReportH($summary['on_hold_invoices']) . '</strong></div><div class="sp-drawer-stat"><span>' . _('>90 day exposure') . '</span><strong>' . SP_ReportPageAmount(end($agingRows)['amount'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Payments not linked') . '</span><strong>' . SP_ReportH(max(0, $summary['payment_count'] - (int)$summary['linked_payment_count'])) . '</strong></div></div><p class="sp-panel-caption">' . _('Holds are the available AP exception flag. Dispute reason, workflow owner, and approval status are not persisted in the current supplier ledger schema.') . '</p></section></div>';
}

if ($filters['view'] === 'aging') {
	echo '<section class="sp-panel"><div class="sp-toolbar"><div><h2>' . _('Aging analysis') . '</h2><p class="sp-panel-caption">' . _('Amounts are base-currency equivalents and include net ledger adjustments so the bucket totals reconcile to outstanding exposure.') . '</p></div></div><table class="sp-list"><thead><tr><th>' . _('Bucket') . '</th><th class="number">' . _('Amount') . '</th><th class="number">' . _('Invoices') . '</th><th>' . _('Trace') . '</th></tr></thead><tbody>';
	foreach ($agingRows as $row) echo '<tr><td>' . SP_ReportH(SP_ReportAgeBucketLabel($row['aging_bucket'])) . '</td><td class="number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</td><td class="number">' . (int)$row['invoice_count'] . '</td><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('view' => 'payables', 'aging_bucket' => $row['aging_bucket'], 'page' => 1)) . '">' . _('View invoices') . '</a></td></tr>';
	echo '</tbody></table></section>';
}

if ($filters['view'] === 'suppliers') {
	$sortUrl = function ($sort) use ($baseUrl, $filters) { return SP_ReportPageUrl($baseUrl, $filters, array('sort' => $sort, 'direction' => ($filters['sort'] === $sort && $filters['direction'] === 'desc') ? 'asc' : 'desc', 'page' => 1)); };
	echo '<section class="sp-panel"><div class="sp-toolbar"><div><h2>' . _('Supplier summary') . '</h2><p class="sp-panel-caption">' . (int)$supplierCount . ' ' . _('supplier records') . ' · ' . _('base-currency amounts') . '</p></div><div class="sp-columns"><label><input type="checkbox" data-column-toggle="supplier-optional" checked="checked" /> ' . _('Show dimensions') . '</label></div></div><div style="overflow:auto"><table class="sp-list" id="supplier-table"><thead><tr><th><a href="' . $sortUrl('supplier') . '">' . _('Supplier') . '</a></th><th>' . _('Status') . '</th><th>' . _('Category') . '</th><th>' . _('Contact / terms') . '</th><th class="number"><a href="' . $sortUrl('total_invoiced') . '">' . _('Total invoiced') . '</a></th><th class="number"><a href="' . $sortUrl('total_paid') . '">' . _('Paid') . '</a></th><th class="number"><a href="' . $sortUrl('outstanding') . '">' . _('Outstanding') . '</a></th><th class="number"><a href="' . $sortUrl('overdue') . '">' . _('Overdue') . '</a></th><th>' . _('Next due') . '</th><th>' . _('Last payment') . '</th><th class="number">' . _('Open / overdue') . '</th><th data-optional="supplier-optional">' . _('Projects / jobs') . '</th><th data-optional="supplier-optional">' . _('POs') . '</th></tr></thead><tbody>';
	if (count($supplierRows) === 0) echo '<tr><td colspan="13" class="sp-empty">' . _('No suppliers match these filters.') . '</td></tr>';
	foreach ($supplierRows as $row) {
		$contact = trim($row['contact_email'] . ($row['contact_phone'] !== '' ? ' · ' . $row['contact_phone'] : ''));
		$dimensions = trim($row['project_refs']);
		echo '<tr><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('supplier_detail' => $row['supplierid'])) . '">' . SP_ReportH($row['suppname']) . '</a><br><span class="sp-subtle">' . SP_ReportH($row['supplierid']) . '</span></td><td>' . SP_ReportPageStatus($row['status']) . '</td><td>' . SP_ReportH($row['supplier_category']) . '</td><td>' . SP_ReportH($contact !== '' ? $contact : '—') . '<br><span class="sp-subtle">' . SP_ReportH($row['payment_terms']) . '</span></td><td class="number">' . SP_ReportPageAmount($row['total_invoiced'], $companyDecimals, $companyCurrency) . '</td><td class="number">' . SP_ReportPageAmount($row['total_paid'], $companyDecimals, $companyCurrency) . '</td><td class="number">' . SP_ReportPageAmount($row['outstanding'], $companyDecimals, $companyCurrency) . '</td><td class="number">' . SP_ReportPageAmount($row['overdue'], $companyDecimals, $companyCurrency) . '</td><td>' . SP_ReportPageDate($row['next_due']) . '</td><td>' . SP_ReportPageDate($row['last_payment']) . '</td><td class="number">' . (int)$row['open_invoices'] . ' / ' . (int)$row['overdue_invoices'] . '</td><td data-optional="supplier-optional">' . SP_ReportH($dimensions !== '' ? $dimensions : '—') . '</td><td data-optional="supplier-optional" class="number">' . (int)$row['purchase_order_count'] . '</td></tr>';
	}
	echo '</tbody></table></div>';
	$totalPages = max(1, (int)ceil($supplierCount / $pageSize));
	echo '<div class="sp-pagination"><span>' . _('Page') . ' ' . (int)$filters['page'] . ' ' . _('of') . ' ' . $totalPages . '</span><span>';
	if ($filters['page'] > 1) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] - 1)) . '">' . _('Previous') . '</a>';
	if ($filters['page'] < $totalPages) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] + 1)) . '">' . _('Next') . '</a>';
	echo '</span></div></section>';
}

if ($filters['view'] === 'payables') {
	echo '<section class="sp-panel"><div class="sp-toolbar"><div><h2>' . _('Payables detail') . '</h2><p class="sp-panel-caption">' . (int)$payableCount . ' ' . _('records') . ' · ' . _('click a supplier or trace to the source transaction') . '</p></div><div class="sp-columns"><label><input type="checkbox" data-column-toggle="payable-dimensions" checked="checked" /> ' . _('Show dimensions') . '</label></div></div><div style="overflow:auto"><table class="sp-list"><thead><tr><th>' . _('Invoice / type') . '</th><th>' . _('Supplier') . '</th><th>' . _('PO') . '</th><th>' . _('Invoice date') . '</th><th>' . _('Due date') . '</th><th class="number">' . _('Original') . '</th><th class="number">' . _('Paid / allocated') . '</th><th class="number">' . _('Outstanding') . '</th><th>' . _('Currency') . '</th><th>' . _('Status') . '</th><th>' . _('Aging') . '</th><th>' . _('Terms') . '</th><th data-optional="payable-dimensions">' . _('Project / job') . '</th><th>' . _('Hold') . '</th></tr></thead><tbody>';
	if (count($payableRows) === 0) echo '<tr><td colspan="14" class="sp-empty">' . _('No payable transactions match these filters.') . '</td></tr>';
	foreach ($payableRows as $row) {
		$bucket = SP_ReportAgeBucket($row['effective_duedate'], $filters['as_of']);
		$paid = (float)$row['original_amount'] - (float)$row['outstanding_amount'];
		$daysOverdue = max(0, (int)(new DateTime($row['effective_duedate']))->diff(new DateTime($filters['as_of']))->format('%r%a'));
		$dimensions = trim($row['project_refs']);
		echo '<tr><td><a href="SupplierTransInquiry.php?TransType=' . (int)$row['type'] . '&amp;FromDate=' . SP_ReportH(substr($row['trandate'], 0, 10)) . '&amp;ToDate=' . SP_ReportH(substr($row['trandate'], 0, 10)) . '">' . SP_ReportH($row['suppreference'] !== '' ? $row['suppreference'] : $row['transno']) . '</a><br><span class="sp-subtle">' . SP_ReportH($row['type_name']) . ' #' . (int)$row['transno'] . '</span></td><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('supplier_detail' => $row['supplierno'])) . '">' . SP_ReportH($row['suppname']) . '</a><br><span class="sp-subtle">' . SP_ReportH($row['supplierno']) . '</span></td><td>—</td><td>' . SP_ReportPageDate($row['trandate']) . '</td><td>' . SP_ReportPageDate($row['effective_duedate']) . '</td><td class="number">' . SP_ReportPageAmount($row['original_amount'], (int)$row['decimalplaces'], $row['currency']) . '</td><td class="number">' . SP_ReportPageAmount($paid, (int)$row['decimalplaces'], $row['currency']) . '</td><td class="number">' . SP_ReportPageAmount($row['outstanding_amount'], (int)$row['decimalplaces'], $row['currency']) . '</td><td>' . SP_ReportH($row['currency']) . '</td><td>' . SP_ReportPageStatus($row['payment_status']) . '</td><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('aging_bucket' => $bucket, 'page' => 1)) . '">' . SP_ReportH(SP_ReportAgeBucketLabel($bucket)) . '</a>' . ($daysOverdue > 0 ? '<br><span class="sp-subtle">' . $daysOverdue . ' ' . _('days') . '</span>' : '') . '</td><td>' . SP_ReportH($row['payment_terms']) . '</td><td data-optional="payable-dimensions">' . SP_ReportH($dimensions !== '' ? $dimensions : '—') . '</td><td>' . ($row['hold'] ? SP_ReportPageStatus('On hold') : '—') . '</td></tr>';
	}
	echo '</tbody></table></div>';
	$totalPages = max(1, (int)ceil($payableCount / $pageSize));
	echo '<div class="sp-pagination"><span>' . _('Page') . ' ' . (int)$filters['page'] . ' ' . _('of') . ' ' . $totalPages . '</span><span>';
	if ($filters['page'] > 1) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] - 1)) . '">' . _('Previous') . '</a>';
	if ($filters['page'] < $totalPages) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] + 1)) . '">' . _('Next') . '</a>';
	echo '</span></div></section>';
}

if ($filters['view'] === 'payments') {
	echo '<div class="sp-grid-2"><section class="sp-panel"><h2>' . _('Payments by method') . '</h2><p class="sp-panel-caption">' . _('Bank transaction method labels from the existing payment methods table') . '</p>';
	foreach ($analytics['by_method'] as $row) echo '<div class="sp-aging-row"><span>' . SP_ReportH($row['label']) . '</span>' . SP_ReportPageBar($row['amount'], $maxMethod) . '<span class="sp-number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$row['count'] . '</span></div>';
	if (count($analytics['trend']) > 0) echo '<hr class="sp-divider"><h2>' . _('Payment trend') . '</h2>';
	foreach ($analytics['trend'] as $row) echo '<div class="sp-aging-row"><span>' . SP_ReportH($row['label']) . '</span>' . SP_ReportPageBar($row['amount'], $maxMethod) . '<span class="sp-number">' . SP_ReportPageAmount($row['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$row['count'] . '</span></div>';
	echo '</section><section class="sp-panel"><h2>' . _('Payment timing') . '</h2><p class="sp-panel-caption">' . _('Timing is measured on recorded allocations from invoice date to payment date.') . '</p><div class="sp-drawer-stats"><div class="sp-drawer-stat"><span>' . _('Early') . '</span><strong>' . SP_ReportPageAmount($summary['early_paid'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('On time') . '</span><strong>' . SP_ReportPageAmount($summary['on_time_paid'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Late') . '</span><strong>' . SP_ReportPageAmount($summary['late_paid'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Average days') . '</span><strong>' . ($summary['average_payment_days'] === null ? '—' : number_format($summary['average_payment_days'], 1)) . '</strong></div></div><p class="sp-panel-caption">' . _('Payments without a linked allocation are included in payment totals but not in timing rates.') . '</p></section></div>';
	echo '<section class="sp-panel"><div class="sp-toolbar"><div><h2>' . _('Payment detail') . '</h2><p class="sp-panel-caption">' . (int)$paymentCount . ' ' . _('records') . '</p></div></div><div style="overflow:auto"><table class="sp-list"><thead><tr><th>' . _('Payment') . '</th><th>' . _('Supplier') . '</th><th>' . _('Payment date') . '</th><th class="number">' . _('Amount') . '</th><th>' . _('Currency') . '</th><th>' . _('Method') . '</th><th>' . _('Payment reference') . '</th><th>' . _('Bank account') . '</th><th>' . _('Status') . '</th><th>' . _('Project / job') . '</th></tr></thead><tbody>';
	if (count($paymentRows) === 0) echo '<tr><td colspan="10" class="sp-empty">' . _('No payments match these filters.') . '</td></tr>';
	foreach ($paymentRows as $row) {
		$dimensions = trim($row['project_refs']);
		echo '<tr><td>' . SP_ReportH($row['payment_reference'] !== '' ? $row['payment_reference'] : $row['transno']) . '<br><span class="sp-subtle">#' . (int)$row['transno'] . '</span></td><td><a href="' . SP_ReportPageUrl($baseUrl, $filters, array('supplier_detail' => $row['supplierno'])) . '">' . SP_ReportH($row['suppname']) . '</a><br><span class="sp-subtle">' . SP_ReportH($row['supplierno']) . '</span></td><td>' . SP_ReportPageDate($row['payment_date']) . '</td><td class="number">' . SP_ReportPageAmount($row['payment_amount'], (int)$row['decimalplaces'], $row['currency']) . '<br><span class="sp-subtle">' . SP_ReportPageAmount($row['payment_amount_base'], $companyDecimals, $companyCurrency) . ' base</span></td><td>' . SP_ReportH($row['currency']) . '</td><td>' . SP_ReportH($row['payment_method']) . '</td><td>' . SP_ReportH($row['payment_reference'] !== '' ? $row['payment_reference'] : '—') . '</td><td>' . SP_ReportH($row['bank_account'] !== '' ? $row['bank_account'] : '—') . '</td><td>' . SP_ReportPageStatus($row['payment_status']) . '</td><td>' . SP_ReportH($dimensions !== '' ? $dimensions : '—') . '</td></tr>';
	}
	echo '</tbody></table></div>';
	$totalPages = max(1, (int)ceil($paymentCount / $pageSize));
	echo '<div class="sp-pagination"><span>' . _('Page') . ' ' . (int)$filters['page'] . ' ' . _('of') . ' ' . $totalPages . '</span><span>';
	if ($filters['page'] > 1) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] - 1)) . '">' . _('Previous') . '</a>';
	if ($filters['page'] < $totalPages) echo '<a href="' . SP_ReportPageUrl($baseUrl, $filters, array('page' => $filters['page'] + 1)) . '">' . _('Next') . '</a>';
	echo '</span></div></section>';
}

if ($supplierDetail !== null && !empty($supplierDetail['summary'])) {
	$row = $supplierDetail['summary'];
	echo '<div class="sp-drawer-backdrop"></div><aside class="sp-drawer"><a class="sp-drawer-close" href="' . SP_ReportPageUrl($baseUrl, $filters, array('supplier_detail' => '')) . '" aria-label="' . _('Close') . '">×</a><p class="sp-eyebrow">' . _('Supplier relationship profile') . '</p><h2>' . SP_ReportH($row['suppname']) . '</h2><p class="sp-drawer-sub">' . SP_ReportH($row['supplierid']) . ' · ' . SP_ReportH($row['supplier_category']) . ' · ' . SP_ReportPageStatus('Active') . '</p><div class="sp-drawer-stats"><div class="sp-drawer-stat"><span>' . _('Outstanding exposure') . '</span><strong>' . SP_ReportPageAmount($row['outstanding'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Total invoiced') . '</span><strong>' . SP_ReportPageAmount($row['total_invoiced'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Recorded payments') . '</span><strong>' . SP_ReportPageAmount($row['total_paid'], $companyDecimals, $companyCurrency) . '</strong></div><div class="sp-drawer-stat"><span>' . _('Avg. payment time') . '</span><strong>' . ($row['average_days'] === null ? '—' : number_format($row['average_days'], 1) . ' days') . '</strong></div></div><p><strong>' . _('Payment terms') . ':</strong> ' . SP_ReportH($row['payment_terms']) . '<br><strong>' . _('Currency') . ':</strong> ' . SP_ReportH($row['currency']) . '<br><strong>' . _('Contact') . ':</strong> ' . SP_ReportH(trim($row['contact_email'] . ' ' . $row['contact_phone']) !== '' ? trim($row['contact_email'] . ' ' . $row['contact_phone']) : '—') . '</p>';
	echo '<hr class="sp-divider"><h2>' . _('Payment methods') . '</h2>';
	$detailMax = 0; foreach ($paymentDetailAnalytics['by_method'] as $method) $detailMax = max($detailMax, (float)$method['amount']);
	foreach ($paymentDetailAnalytics['by_method'] as $method) echo '<div class="sp-aging-row"><span>' . SP_ReportH($method['label']) . '</span>' . SP_ReportPageBar($method['amount'], $detailMax) . '<span class="sp-number">' . SP_ReportPageAmount($method['amount'], $companyDecimals, $companyCurrency) . '</span><span class="sp-number">' . (int)$method['count'] . '</span></div>';
	echo '<hr class="sp-divider"><h2>' . _('Recent activity') . '</h2><table class="sp-list sp-small-table"><thead><tr><th>' . _('Date') . '</th><th>' . _('Type') . '</th><th class="number">' . _('Amount') . '</th><th>' . _('Status') . '</th></tr></thead><tbody>';
	foreach ($supplierDetail['transactions'] as $transaction) echo '<tr><td>' . SP_ReportPageDate($transaction['trandate']) . '</td><td>' . SP_ReportH($transaction['type_name']) . '<br><span class="sp-subtle">' . SP_ReportH($transaction['suppreference'] !== '' ? $transaction['suppreference'] : $transaction['transno']) . '</span></td><td class="number">' . SP_ReportPageAmount($transaction['original_amount'], $companyDecimals, $transaction['currency']) . '</td><td>' . SP_ReportPageStatus($transaction['status']) . '</td></tr>';
	if (count($supplierDetail['transactions']) === 0) echo '<tr><td colspan="4" class="sp-empty">' . _('No recent activity.') . '</td></tr>';
	echo '</tbody></table><hr class="sp-divider"><h2>' . _('Related purchase orders') . '</h2><table class="sp-list sp-small-table"><thead><tr><th>' . _('PO') . '</th><th>' . _('Date') . '</th><th>' . _('Status') . '</th><th>' . _('Initiator') . '</th></tr></thead><tbody>';
	foreach ($supplierDetail['purchase_orders'] as $po) echo '<tr><td>' . SP_ReportH($po['orderno']) . '</td><td>' . SP_ReportPageDate($po['orddate']) . '</td><td>' . SP_ReportH($po['status'] !== '' ? $po['status'] : '—') . '</td><td>' . SP_ReportH($po['initiator'] !== '' ? $po['initiator'] : '—') . '</td></tr>';
	if (count($supplierDetail['purchase_orders']) === 0) echo '<tr><td colspan="4" class="sp-empty">' . _('No related purchase orders found.') . '</td></tr>';
	echo '</tbody></table><p class="sp-subtle" style="margin-top:16px">' . _('Disputes, notes, account owner, and approval workflow are not stored for suppliers in the current schema.') . '</p></aside>';
}

echo '</div>';
echo '<script type="text/javascript">
(function(){
  function toggleColumns(input){var selector=input.getAttribute("data-column-toggle");var cells=document.querySelectorAll("[data-optional=\""+selector+"\"]");for(var i=0;i<cells.length;i++){cells[i].style.display=input.checked?"":"none";}}
  var toggles=document.querySelectorAll("[data-column-toggle]");for(var i=0;i<toggles.length;i++){toggleColumns(toggles[i]);toggles[i].addEventListener("change",function(){toggleColumns(this);});}
  var loading=document.getElementById("sp-loading");
  function showLoading(){if(loading){loading.removeAttribute("hidden");}}
  function hideLoading(){if(loading){loading.setAttribute("hidden","hidden");}}
  var form=document.getElementById("sp-filter-form");
  if(form){form.addEventListener("submit",function(){showLoading();var button=form.querySelector("button[type=submit]");if(button){button.disabled=true;button.setAttribute("aria-disabled","true");}});}
  var links=document.querySelectorAll(".sp-shell a[href]");
  for(var j=0;j<links.length;j++){
    links[j].addEventListener("click",function(event){
      if(this.classList.contains("sp-export") || this.getAttribute("target")==="_blank") return;
      var href=this.getAttribute("href")||"";
      if(href.charAt(0)==="#" || href.indexOf("javascript:")===0) return;
      showLoading();
    });
  }
  window.addEventListener("pageshow",hideLoading);
})();
</script>';
include('includes/footer.inc');
?>
