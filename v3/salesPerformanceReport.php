<?php
$active = 'reports';
$title = 'Sales Performance Report';
include_once('config.php');
date_default_timezone_set('Asia/Karachi');

if (!userHasPermission($db, 'salesperson_daily_activity_report')) {
	header('Location: ' . $NewRootPath . 'v2/reportLinks.php');
	exit;
}

$canViewCompany = userHasPermission($db, 'sales_performance_view_all');
$canViewTeams = userHasPermission($db, 'sales_performance_view_teams');
$canViewSelf = userHasPermission($db, 'sales_performance_view_self');
$canCaptureEvents = userHasPermission($db, 'sales_performance_capture_events');
if (!$canViewCompany && !$canViewTeams && !$canViewSelf) {
	header('Location: ' . $NewRootPath . 'v2/reportLinks.php');
	exit;
}

$teams = array();
$allowedSalespeople = array();
if ($canViewCompany) {
	$teamResult = mysqli_query($db, 'SELECT id, name, members FROM salesteam ORDER BY name');
	if ($teamResult) while ($row = mysqli_fetch_assoc($teamResult)) {
		$teams[] = $row;
		foreach (explode(',', (string) $row['members']) as $member) $allowedSalespeople[trim($member)] = true;
	}
} elseif ($canViewTeams) {
	$grantTable = mysqli_query($db, "SHOW TABLES LIKE 'sales_performance_scope_grants'");
	if ($grantTable && mysqli_num_rows($grantTable) > 0) {
		$grantStmt = mysqli_prepare($db, "SELECT DISTINCT team_id FROM sales_performance_scope_grants WHERE user_id = ? AND capability = 'view_team' AND revoked_at IS NULL AND team_id IS NOT NULL");
		if ($grantStmt) {
			mysqli_stmt_bind_param($grantStmt, 's', $_SESSION['UserID']);
			if (mysqli_stmt_execute($grantStmt)) {
				$grantResult = mysqli_stmt_get_result($grantStmt);
				$grantedIds = array();
				if ($grantResult) {
					while ($grant = mysqli_fetch_assoc($grantResult)) $grantedIds[(int) $grant['team_id']] = true;
					mysqli_free_result($grantResult);
				}
				$allTeams = mysqli_query($db, 'SELECT id, name, members FROM salesteam ORDER BY name');
				if ($allTeams) while ($row = mysqli_fetch_assoc($allTeams)) {
					if (!isset($grantedIds[(int) $row['id']])) continue;
					$teams[] = $row;
					foreach (explode(',', (string) $row['members']) as $member) $allowedSalespeople[trim($member)] = true;
				}
			}
			mysqli_stmt_close($grantStmt);
		}
	}
} elseif ($canViewSelf && isset($_SESSION['UsersRealName'])) {
	$allowedSalespeople[$_SESSION['UsersRealName']] = true;
}
if ($canViewSelf && isset($_SESSION['UsersRealName'])) $allowedSalespeople[$_SESSION['UsersRealName']] = true;
$salespeople = array();
$salespersonResult = mysqli_query($db, 'SELECT salesmanname FROM salesman ORDER BY salesmanname');
if ($salespersonResult) {
	while ($row = mysqli_fetch_assoc($salespersonResult)) {
		if ($canViewCompany || isset($allowedSalespeople[$row['salesmanname']])) $salespeople[] = $row['salesmanname'];
	}
}

$today = date('Y-m-d');
$currentFrom = date('Y-m-01');
$previousMonthStart = date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month'));
$comparisonEnd = date('Y-m-d', min(
	strtotime($previousMonthStart . ' +' . ((int) date('j') - 1) . ' days'),
	strtotime(date('Y-m-t', strtotime($previousMonthStart)))
));
$canManageAdjustments = userHasPermission($db, 'sales_performance_adjustments') && $canViewCompany;
$canApproveAdjustments = userHasPermission($db, 'sales_performance_adjustments_approve') && $canViewCompany;
$canAdminSetup = userHasPermission($db, 'sales_performance_admin') && $canViewCompany;
$canCreateRun = userHasPermission($db, 'sales_performance_create_run');
$membershipSalespeople = array();
if ($canAdminSetup) {
	$membershipResult = mysqli_query($db, 'SELECT salesmancode, salesmanname FROM salesman ORDER BY salesmanname');
	if ($membershipResult) while ($row = mysqli_fetch_assoc($membershipResult)) $membershipSalespeople[] = $row;
}

include_once('includes/header.php');
include_once('includes/sidebar.php');
?>
<link rel="stylesheet" href="assets/salesPerformanceReport.css">
<link rel="stylesheet" href="assets/bower_components/select2/dist/css/select2.min.css">

<div class="content-wrapper sp-page" id="sales-performance-app"
	 data-api-url="api/salesPerformanceReport.php"
	 data-currency="PKR"
	 data-form-id="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>"
	 data-can-create-run="<?php echo $canCreateRun ? '1' : '0'; ?>"
	 data-can-capture-events="<?php echo $canCaptureEvents ? '1' : '0'; ?>"
	 data-can-manage-adjustments="<?php echo $canManageAdjustments ? '1' : '0'; ?>"
	 data-can-approve-adjustments="<?php echo $canApproveAdjustments ? '1' : '0'; ?>">
	<section class="content-header sp-header">
		<h1>Sales Performance <small>Month, week and team reporting</small></h1>
		<p>Review activity counts and values across selected periods. Raw values and approved normalized values are shown separately.</p>
	</section>

	<section class="content">
		<div class="sp-filter-card">
			<form id="sp-filter-form">
				<div class="sp-filter-grid">
					<div class="sp-filter-period">
						<strong>Reporting period</strong>
						<div class="sp-date-pair">
							<label>From<input type="date" name="from" value="<?php echo htmlspecialchars($currentFrom, ENT_QUOTES, 'UTF-8'); ?>" required></label>
							<label>Through<input type="date" name="to" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						</div>
					</div>
					<div class="sp-filter-period">
						<strong>Compare with</strong>
						<div class="sp-date-pair">
							<label>From<input type="date" name="compare_from" value="<?php echo htmlspecialchars($previousMonthStart, ENT_QUOTES, 'UTF-8'); ?>" required></label>
							<label>Through<input type="date" name="compare_to" value="<?php echo htmlspecialchars($comparisonEnd, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						</div>
					</div>
					<div class="sp-filter-period sp-cutoff-filter">
						<strong>Historical cutoff</strong>
						<label>Report through<input type="date" name="business_cutoff" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
					</div>
					<?php if ($canViewCompany) { ?><div class="sp-filter-select">
						<label for="sp-population-filter">Approved team population</label>
						<select id="sp-population-filter" name="population_scope"><option value="">No named population</option></select>
					</div><?php } ?>
					<div class="sp-filter-select">
						<label for="sp-team-filter">Teams</label>
						<select id="sp-team-filter" name="team_ids[]" multiple size="4">
							<?php foreach ($teams as $team) { ?>
								<option value="<?php echo (int) $team['id']; ?>"><?php echo htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8'); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="sp-filter-select">
						<label for="sp-salesperson-filter">Salespeople</label>
						<select id="sp-salesperson-filter" name="salespeople[]" multiple size="4">
							<?php foreach ($salespeople as $salesperson) { ?>
								<option value="<?php echo htmlspecialchars($salesperson, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($salesperson, ENT_QUOTES, 'UTF-8'); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="sp-filter-actions">
					<div class="sp-filter-hint"><i class="fa fa-info-circle"></i> Weeks run Monday–Sunday; per-day averages count Monday–Saturday reporting days.</div>
					<div>
						<button type="button" id="sp-reset-filters" class="btn btn-default">Reset</button>
						<button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply filters</button>
						<button type="button" id="sp-freeze-run" class="btn btn-default" <?php echo $canCreateRun ? '' : 'disabled title="Ask an ERP administrator to grant frozen report access."'; ?>><i class="fa fa-lock"></i> Freeze report</button>
						<button type="button" id="sp-export-csv" class="btn btn-success" <?php echo $canCreateRun ? '' : 'disabled title="Frozen report access is required for exports."'; ?>><i class="fa fa-download"></i> Export CSV</button>
						<button type="button" id="sp-export-xlsx" class="btn btn-default" <?php echo $canCreateRun ? '' : 'disabled title="Frozen report access is required for exports."'; ?>><i class="fa fa-file-excel-o"></i> Export Excel</button>
						<button type="button" id="sp-export-pdf" class="btn btn-default" <?php echo $canCreateRun ? '' : 'disabled title="Frozen report access is required for exports."'; ?>><i class="fa fa-file-pdf-o"></i> Download PDF</button>
					</div>
				</div>
			</form>
		</div>

		<div id="sp-message" class="sp-message" role="status" aria-live="polite"></div>
		<?php if ($canCreateRun) { ?><div class="sp-run-loader"><label for="sp-run-key">Frozen report key</label><input type="text" id="sp-run-key" class="form-control" maxlength="36" placeholder="Paste a previously saved report key"><button type="button" id="sp-load-run" class="btn btn-default">Load saved report</button></div><?php } ?>
		<div id="sp-loading" class="sp-loading"><i class="fa fa-circle-o-notch fa-spin"></i> Loading sales activity…</div>

		<div id="sp-report-content" style="display:none">
			<div class="sp-report-meta" id="sp-report-meta"></div>
			<div class="sp-assumption-note" id="sp-assumptions"></div>
			<ul class="nav nav-tabs sp-tabs" role="tablist">
				<li class="active"><a href="#sp-month-view" data-toggle="tab">Month View</a></li>
				<li><a href="#sp-week-view" data-toggle="tab">Weekly Overview</a></li>
				<li><a href="#sp-team-view" data-toggle="tab">Team Comparison</a></li>
			</ul>
			<div class="tab-content sp-tab-content">
				<div class="tab-pane active" id="sp-month-view">
					<div class="sp-kpi-grid" id="sp-kpis"></div>
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Daily activity</h3><p>Quotation counts open scoped supporting ERP documents. Unanswered and outcome counts use captured event history; older event coverage is incomplete.</p></div></div>
						<div class="table-responsive"><table class="table table-striped sp-table" id="sp-daily-table"></table></div>
					</div>
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Period comparison</h3><p>Values are in PKR. A dash means the comparison value is zero or unavailable.</p></div></div>
						<div class="table-responsive"><table class="table table-striped sp-table" id="sp-period-table"></table></div>
					</div>
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Daily activity trend</h3><p>ERP document values are shown as provisional source totals; OC activity remains a purchase-order proxy.</p></div></div>
						<div id="sp-month-chart" class="sp-chart"></div>
					</div>
				</div>

				<div class="tab-pane" id="sp-week-view">
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Weekly activity</h3><p>Weeks are grouped Monday through Sunday. Incomplete weeks remain visible.</p></div></div>
						<div id="sp-week-chart" class="sp-chart"></div>
						<div class="table-responsive"><table class="table table-striped sp-table" id="sp-week-table"></table></div>
					</div>
				</div>

				<div class="tab-pane" id="sp-team-view">
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Team totals</h3><p>Captured events keep their recorded team. Legacy ERP documents use current membership as a provisional fallback; ambiguous owners remain unassigned.</p></div></div>
						<div id="sp-team-chart" class="sp-chart"></div>
						<div class="table-responsive"><table class="table table-striped sp-table" id="sp-team-table"></table></div>
						<h4 class="sp-subtable-title">Weekly team detail</h4>
						<div class="table-responsive"><table class="table table-striped sp-table" id="sp-team-week-table"></table></div>
					</div>
					<div class="sp-card">
				<div class="sp-card-heading sp-matrix-heading">
					<div><h3>Weekday comparison</h3><p>Future dates, days after the selected cutoff, and dates outside the selected period are labeled.</p></div>
							<div class="sp-matrix-controls">
								<label>Team<select id="sp-matrix-team" class="form-control"></select></label>
								<label>Measure<select id="sp-matrix-metric" class="form-control">
									<option value="quotations">Quotations</option><option value="revised_quotations">Revised quotations</option><option value="purchase_orders">POs</option><option value="delivery_challans">DCs</option><option value="followups">Follow-ups</option>
								</select></label>
								<label>Display<select id="sp-matrix-value" class="form-control"><option value="normalized_value">Normalized value</option><option value="raw_value">Raw value</option><option value="count">Count</option></select></label>
							</div>
						</div>
						<div class="table-responsive" id="sp-team-matrix"></div>
					</div>
				</div>
			</div>

			<div class="sp-card" id="sp-followup-card">
					<div class="sp-card-heading"><div><h3>Follow-up activity</h3><p>Captured follow-ups can be corrected or voided with a reason. The prior version stays in the audit history; frozen reports do not change.</p></div></div>
						<?php if ($canCaptureEvents) { ?><form id="sp-followup-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="add_followup">
					<input type="hidden" name="idempotency_key" value="">
					<input type="hidden" name="corrected_from_id" value="">
					<div class="sp-followup-fields">
						<label>Date<input class="form-control" type="date" name="activity_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Time<input class="form-control" type="time" name="activity_time" value="<?php echo htmlspecialchars(date('H:i'), ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Sales case reference (optional)<input class="form-control" type="text" name="salescaseref" maxlength="40" placeholder="e.g. SC-12345"></label>
						<label>Previous quotation number (optional)<input class="form-control" type="text" name="quotation_orderno" maxlength="12" placeholder="Must belong to this case and predate the follow-up"></label>
						<label>Activity type<select class="form-control" name="activity_type"><option value="call">Call</option><option value="visit">Visit</option><option value="email">Email</option><option value="meeting">Meeting</option><option value="other">Other</option></select></label>
						<label>Outcome<input class="form-control" type="text" name="outcome" maxlength="255" placeholder="Brief outcome"></label>
						<label>Next action<input class="form-control" type="text" name="next_action" maxlength="500" placeholder="Optional next step"></label>
						<label>Next action date<input class="form-control" type="date" name="next_action_date"></label>
						<label class="sp-followup-notes">Notes<textarea class="form-control" name="notes" maxlength="4000" rows="2" placeholder="Optional notes"></textarea></label>
						<label class="sp-followup-notes">Correction reason (only when correcting)<textarea class="form-control" name="correction_reason" maxlength="500" rows="2" placeholder="Explain the correction; this is saved with the prior version"></textarea></label>
					</div>
					<div class="sp-form-actions"><button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Record follow-up</button><span id="sp-followup-message" aria-live="polite"></span></div>
				</form><?php } else { ?><p class="text-muted">Follow-up capture is hidden because the event-capture capability has not been granted.</p><?php } ?>
				<?php if ($canCaptureEvents) { ?><form id="sp-void-followup-form" style="display:none">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="void_followup">
					<input type="hidden" name="record_id" value="">
					<input type="hidden" name="reason" value="">
				</form><?php } ?>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-followup-table"></table></div>
			</div>

			<?php if ($canCaptureEvents) { ?>
			<div class="sp-card" id="sp-enquiry-event-card">
				<div class="sp-card-heading"><div><h3>Enquiry events</h3><p>Record actual receipt, response and outcome events. These entries create prospective history; they do not reconstruct older response timelines.</p></div></div>
				<form id="sp-enquiry-event-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="capture_enquiry_event">
					<input type="hidden" name="idempotency_key" value="">
					<input type="hidden" name="event_id" value="">
					<div class="sp-followup-fields">
						<label>Sales case reference<input class="form-control" type="text" name="salescaseref" maxlength="40" required></label>
						<label>Event<select class="form-control" name="event_type"><option value="received">Enquiry received</option><option value="response">Response made</option><option value="company_decline">Company declined</option><option value="customer_rejection">Customer rejected</option><option value="cancellation">Cancelled</option><option value="duplicate">Duplicate</option><option value="success">Successful close</option><option value="reopen">Reopen</option></select></label>
						<label>Date<input class="form-control" type="date" name="event_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Time<input class="form-control" type="time" name="event_time" value="<?php echo htmlspecialchars(date('H:i'), ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Response type<select class="form-control" name="response_code"><option value="">Not applicable</option><option value="contacted">Customer contacted</option><option value="quotation_issued">Quotation issued</option><option value="clarification">Clarification sent</option><option value="other">Other response</option></select></label>
						<label>Outcome reason<input class="form-control" type="text" name="reason_code" maxlength="40" placeholder="Required for company decline"></label>
						<label>Evidence reference<input class="form-control" type="text" name="evidence_reference" maxlength="500" placeholder="Optional ERP document or file reference"></label>
						<label class="sp-followup-notes">Notes<textarea class="form-control" name="notes" maxlength="2000" rows="2"></textarea></label>
						<label class="sp-followup-notes">Correction reason (only when correcting)<textarea class="form-control" name="correction_reason" maxlength="500" rows="2" placeholder="Explain what was wrong in the prior entry"></textarea></label>
					</div>
					<div class="sp-form-actions"><button id="sp-enquiry-event-submit" type="submit" class="btn btn-primary">Record enquiry event</button><span id="sp-enquiry-event-message" aria-live="polite"></span></div>
				</form>
				<form id="sp-void-enquiry-event-form" style="display:none">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="void_enquiry_event">
					<input type="hidden" name="event_id" value="">
					<input type="hidden" name="void_reason" value="">
					<input type="hidden" name="idempotency_key" value="">
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-enquiry-event-table"></table></div>
			</div>

			<div class="sp-card" id="sp-po-receipt-card">
				<div class="sp-card-heading"><div><h3>Customer PO receipt</h3><p>Count the customer’s actual PO receipt separately from ERP order confirmations. Reuploads with the same customer, branch and PO reference are rejected as duplicates.</p></div></div>
				<form id="sp-po-receipt-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="capture_po_receipt">
					<input type="hidden" name="idempotency_key" value="">
					<input type="hidden" name="receipt_id" value="">
					<div class="sp-followup-fields">
						<label>Sales case reference<input class="form-control" type="text" name="salescaseref" maxlength="40" required></label>
						<label>Customer PO reference<input class="form-control" type="text" name="customer_reference" maxlength="150" required></label>
						<label>Actually received date<input class="form-control" type="date" name="received_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Time<input class="form-control" type="time" name="received_time" value="<?php echo htmlspecialchars(date('H:i'), ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>PO stated amount<input class="form-control" type="number" name="stated_amount" min="0" step="0.01" placeholder="Optional"></label>
						<label>Currency<input class="form-control" type="text" name="currency" maxlength="3" value="PKR"></label>
						<label>Amount basis<select class="form-control" name="tax_basis"><option value="unknown">Unknown</option><option value="exclusive">Excludes GST</option><option value="inclusive">Includes GST</option></select></label>
						<label>Linked quotation number (optional)<input class="form-control" type="text" name="quotation_orderno" maxlength="12"></label>
						<label>Linked OC number (optional)<input class="form-control" type="text" name="oc_orderno" maxlength="12"></label>
						<label>Linked OC PO number (optional)<input class="form-control" type="text" name="oc_pono" maxlength="12"></label>
						<label>Evidence reference<input class="form-control" type="text" name="evidence_reference" maxlength="500" placeholder="Optional ERP document or file reference"></label>
						<label class="sp-followup-notes">Correction reason (only when correcting)<textarea class="form-control" name="correction_reason" maxlength="500" rows="2" placeholder="Explain what was wrong in the prior receipt"></textarea></label>
					</div>
					<div class="sp-form-actions"><button id="sp-po-receipt-submit" type="submit" class="btn btn-primary">Record PO receipt</button><span id="sp-po-receipt-message" aria-live="polite"></span></div>
				</form>
				<form id="sp-void-po-receipt-form" style="display:none">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="void_po_receipt">
					<input type="hidden" name="receipt_id" value="">
					<input type="hidden" name="void_reason" value="">
					<input type="hidden" name="idempotency_key" value="">
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-po-receipt-table"></table></div>
			</div>
			<?php } ?>

			<?php if ($canAdminSetup) { ?>
			<div class="sp-card" id="sp-coverage-card">
				<div class="sp-card-heading"><div><h3>Reporting coverage</h3><p>Review the calculated figures for the selected date before submitting coverage. The saved review snapshot is bound to the loaded full-company report and the active calendar version.</p></div></div>
				<form id="sp-coverage-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="submit_coverage">
					<input type="hidden" name="coverage_review_metrics_json" value="">
					<input type="hidden" name="coverage_review_context_json" value="">
					<input type="hidden" name="coverage_review_signature" value="">
					<div class="sp-followup-fields">
						<input type="hidden" name="scope_key" value="erp_company">
						<label>Team (optional)<select class="form-control" name="team_id"><option value="">Whole company</option><?php foreach ($teams as $team) { ?><option value="<?php echo (int) $team['id']; ?>"><?php echo htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></label>
						<label>Date<input class="form-control" type="date" name="coverage_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>State<select class="form-control" name="coverage_state"><option value="complete">Complete</option><option value="partial">Partial</option><option value="unresolved">Unresolved</option><option value="not_submitted">Not submitted</option></select></label>
						<label class="sp-followup-notes">Evidence note<input class="form-control" type="text" name="coverage_note" maxlength="1000" required></label>
					</div>
					<div id="sp-coverage-review-preview" class="sp-coverage-review-preview" aria-live="polite"></div>
					<div class="sp-form-actions"><button id="sp-coverage-submit" type="submit" class="btn btn-primary" disabled>Review report first</button><span id="sp-coverage-message" aria-live="polite"></span></div>
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-coverage-table"></table></div>
			</div>
			<div class="sp-card" id="sp-named-scope-card">
				<div class="sp-card-heading"><div><h3>Named team population</h3><p>Create a versioned team mapping proposal. A separately permissioned administrator must review it; only its latest approved version appears in company report filters. This setup does not grant report access or approve metric definitions.</p></div></div>
				<form id="sp-named-scope-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="save_named_scope">
					<label>Population label<input class="form-control" type="text" name="scope_label" maxlength="120" value="North, South, Central and Projects" required></label>
					<fieldset class="sp-scope-team-list"><legend>Included ERP teams</legend><div>
						<?php foreach ($teams as $team) { ?><label><input type="checkbox" name="team_ids[]" value="<?php echo (int) $team['id']; ?>"> <?php echo htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8'); ?></label><?php } ?>
					</div></fieldset>
					<label class="sp-scope-unassigned"><input type="checkbox" name="include_unassigned" value="1"> Include activity with no unique team attribution. Activity assigned to other teams stays excluded.</label>
					<label>Change evidence / decision reference<input class="form-control" type="text" name="scope_change_note" maxlength="1000" required placeholder="Decision reference or source evidence for this mapping"></label>
					<div class="sp-form-actions"><button type="submit" class="btn btn-primary">Save proposal for review</button><span id="sp-named-scope-message" aria-live="polite"></span></div>
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-named-scope-table"></table></div>
			</div>
			<div class="sp-card" id="sp-team-membership-card">
				<div class="sp-card-heading"><div><h3>Effective team membership</h3><p>Record a prospective open-ended start for a salesperson already listed in exactly one matching ERP team. Unassigned memberships, cross-team moves, end dates and historical backfills are unavailable while team-view access is not date-scoped.</p></div></div>
				<form id="sp-team-membership-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="save_team_membership">
					<div class="sp-followup-fields">
						<label>Salesperson<select class="form-control" name="salesmancode" required><option value="">Choose salesperson</option><?php foreach ($membershipSalespeople as $person) { ?><option value="<?php echo htmlspecialchars($person['salesmancode'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($person['salesmanname'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></label>
						<label>Team<select class="form-control" name="team_id" required><option value="">Choose team</option><?php foreach ($teams as $team) { ?><option value="<?php echo (int) $team['id']; ?>"><?php echo htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></label>
						<label>Effective from<input class="form-control" type="date" name="valid_from" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" min="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label class="sp-followup-notes">Approval or evidence reference<input class="form-control" type="text" name="provenance" maxlength="120" required placeholder="Management decision or source record"></label>
					</div>
					<div class="sp-form-actions"><button type="submit" class="btn btn-primary">Save membership</button><span id="sp-team-membership-message" aria-live="polite"></span></div>
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-team-membership-table"></table></div>
			</div>
			<div class="sp-card" id="sp-calendar-card">
				<div class="sp-card-heading"><div><h3>Reporting calendar overrides <small id="sp-calendar-version"></small></h3><p>Dates default to Monday–Saturday workdays. Overrides change reporting-day denominators and create a new calendar version; frozen reports keep their original version. Sunday workdays are not supported in the current six-day weekday view.</p></div></div>
				<form id="sp-calendar-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="save_calendar_override">
					<div class="sp-followup-fields">
						<label>Date<input class="form-control" type="date" name="business_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
						<label>Planned workday<select class="form-control" name="planned_workday"><option value="1">Workday</option><option value="0">Non-working day</option></select></label>
						<label class="sp-followup-notes">Reason / evidence<input class="form-control" type="text" name="override_reason" maxlength="255" required placeholder="Public holiday or approved working Saturday"></label>
					</div>
					<div class="sp-form-actions"><button type="submit" class="btn btn-primary">Save calendar date</button><span id="sp-calendar-message" aria-live="polite"></span></div>
				</form>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-calendar-table"></table></div>
			</div>
			<div class="sp-card" id="sp-reference-card">
				<div class="sp-card-heading"><div><h3>Reference observations and reconciliation</h3><p>Preview and review source figures before importing them as unverified evidence. Compare an exact-period company, single-team or single-salesperson observation against a frozen report with the same scope and end-of-day cutoff.</p></div></div>
				<form id="sp-reference-import-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="preview_reference_observations">
					<input type="hidden" name="preview_token" value="">
					<div class="sp-followup-fields">
						<label>Source name<input class="form-control" type="text" name="source_name" maxlength="255" required placeholder="September weekly performance sheet"></label>
						<label>Source version<input class="form-control" type="text" name="source_version" maxlength="80" required placeholder="Revised 2026-09-24"></label>
						<label>Evidence location<input class="form-control" type="text" name="evidence_location" maxlength="120" required placeholder="PDF page, workbook tab or file reference"></label>
					</div>
					<label>Observation CSV<textarea class="form-control" name="observations_csv" rows="8" maxlength="200000" required spellcheck="false" placeholder="observation_key,period_from,period_through,cutoff_at,granularity,scope_key,team_name,salesperson_name,metric_key,observed_count,observed_value,value_basis,value_currency,denominator,notes&#10;W39-quote-total,2026-09-21,2026-09-26,,weekly,erp_company,,,quotations,70,535850000.00,raw,PKR,,Transcribed; verify against source"></textarea></label>
					<p class="text-muted">Use metric keys shown by the report (for example <code>quotations</code>). Counts and values may be blank. Value basis is raw, net or normalized; imported rows always remain marked transcribed_unverified.</p>
					<div class="sp-form-actions"><button id="sp-reference-preview-button" type="submit" class="btn btn-default">Preview CSV</button><button id="sp-reference-import" type="button" class="btn btn-primary" disabled>Import reviewed rows</button><span id="sp-reference-import-message" aria-live="polite"></span></div>
				</form>
				<div id="sp-reference-preview" class="sp-reference-preview" aria-live="polite"></div>
				<div class="table-responsive"><table class="table table-striped sp-table" id="sp-reference-table"></table></div>
				<form id="sp-reconciliation-form" class="sp-followup-form" style="display:none">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="create_reconciliation">
					<input type="hidden" name="reference_observation_id" value="">
					<div class="sp-followup-fields">
						<label>Frozen report key<input class="form-control" type="text" name="run_key" maxlength="36" required placeholder="Freeze the matching report first"></label>
						<label>Classification<select class="form-control" name="classification"><option value="unresolved">Unresolved</option><option value="source_variance">Source variance</option><option value="definition_difference">Definition difference</option><option value="data_quality">Data quality</option><option value="rounding">Rounding</option><option value="explained">Explained</option></select></label>
						<label>Owner user ID (optional)<input class="form-control" type="text" name="owner_user_id" maxlength="50"></label>
						<label class="sp-followup-notes">Supporting documents<input class="form-control" type="text" name="supporting_documents" maxlength="2000" placeholder="Document IDs or evidence references"></label>
					</div>
					<div class="sp-form-actions"><button type="submit" class="btn btn-default">Create discrepancy record</button><span id="sp-reconciliation-message" aria-live="polite"></span></div>
				</form>
				<div id="sp-reconciliation-register"></div>
			</div>
			<?php } ?>

			<?php if ($canManageAdjustments || $canApproveAdjustments) { ?>
			<div class="sp-card" id="sp-adjustment-card">
				<div class="sp-card-heading"><div><h3>Normalized value adjustments</h3><p>Exclusions keep document counts intact. Requests affect normalized values only after a second authorized user approves them.</p></div></div>
				<?php if ($canManageAdjustments) { ?>
				<form id="sp-adjustment-form" class="sp-followup-form">
					<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="action" value="request_adjustment">
					<div class="sp-followup-fields sp-adjustment-fields">
						<label>Measure<select class="form-control" name="metric_key"><option value="quotation">Quotation</option><option value="purchase_order">OC proxy</option><option value="delivery_challan">Delivery challan</option></select></label>
						<label>Document key<input class="form-control" type="text" name="source_key" maxlength="100" required placeholder="Quote/DC number or OC|PO number"></label>
						<label>Amount to exclude (PKR)<input class="form-control" type="number" name="excluded_amount" min="0.01" step="0.01" required></label>
						<label class="sp-followup-notes">Reason<input class="form-control" type="text" name="reason" maxlength="500" required placeholder="Explain the duplicate or legacy value"></label>
					</div>
					<div class="sp-form-actions"><button type="submit" class="btn btn-default"><i class="fa fa-send"></i> Submit for approval</button><span id="sp-adjustment-message" aria-live="polite"></span></div>
				</form>
				<?php } ?>
				<?php if ($canApproveAdjustments && !$canManageAdjustments) { ?>
				<div class="sp-form-actions"><span id="sp-adjustment-message" aria-live="polite"></span></div>
				<?php } ?>
				<div id="sp-adjustment-queue"></div>
			</div>
			<?php } ?>

			<div class="sp-footnote"><i class="fa fa-info-circle"></i> Same-period ratios are directional comparisons, not cohort conversion rates. Values use line price after discount and exclude GST additions. Approved exclusions affect normalized totals while counts remain unchanged.</div>
		</div>
	</section>
</div>

<div class="modal fade" id="sp-documents-modal" tabindex="-1" role="dialog" aria-labelledby="sp-documents-title">
	<div class="modal-dialog modal-lg" role="document"><div class="modal-content">
		<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="sp-documents-title">Quotation supporting documents</h4></div>
	<div class="modal-body">
		<?php if ($canCaptureEvents) { ?><div class="sp-issue-capture"><label>Actual sent date<input class="form-control" type="date" id="sp-issued-date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></label><label>Time<input class="form-control" type="time" id="sp-issued-time" value="<?php echo htmlspecialchars(date('H:i'), ENT_QUOTES, 'UTF-8'); ?>"></label><span>Record an issue only after confirming that the quotation was sent.</span></div><?php } ?>
		<div id="sp-documents-message" class="text-muted" role="status"></div><div class="table-responsive"><table class="table table-striped sp-table" id="sp-documents-table"></table></div><button type="button" class="btn btn-default" id="sp-documents-more" style="display:none">Load more</button>
	</div>
	</div></div>
</div>

<?php include_once('includes/footer.php'); ?>
<script src="assets/bower_components/select2/dist/js/select2.full.min.js"></script>
<script src="assets/salesPerformanceReport.js"></script>
