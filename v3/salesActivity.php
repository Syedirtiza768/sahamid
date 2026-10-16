<?php
$active = 'my_sales_activity';
$title = 'My Sales Activity';
include_once('config.php');
date_default_timezone_set('Asia/Karachi');

// Self-service page: each salesperson records activity on their own cases.
// The API enforces the same rules (case owner only), so this page is a convenience, not the control.
if (!userHasPermission($db, 'sales_performance_capture_events') || !userHasPermission($db, 'salesperson_daily_activity_report')) {
	header('Location: ' . $NewRootPath . 'v2/reportLinks.php');
	exit;
}

$today = date('Y-m-d');
$now = date('H:i');

include_once('includes/header.php');
include_once('includes/sidebar.php');
?>
<link rel="stylesheet" href="assets/salesPerformanceReport.css">
<link rel="stylesheet" href="assets/bower_components/select2/dist/css/select2.min.css">

<div class="content-wrapper sp-page" id="my-activity-app"
	 data-api-url="api/salesPerformanceReport.php"
	 data-form-id="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>"
	 data-now="<?php echo htmlspecialchars($now, ENT_QUOTES, 'UTF-8'); ?>">
	<section class="content-header sp-header">
		<h1>My Sales Activity <small>Record what you did on your own cases</small></h1>
		<p>Each entry is credited to you and linked to a sales case you own. Your team’s totals come from everyone’s entries on the <a href="salesPerformanceReport.php">Sales Performance report</a>.</p>
	</section>

	<section class="content">
		<div id="ma-message" class="sp-message" role="status" aria-live="polite"></div>
		<div id="ma-loading" class="sp-loading"><i class="fa fa-circle-o-notch fa-spin"></i> Loading your cases…</div>

		<div id="ma-content" style="display:none">
			<ul class="nav nav-tabs sp-tabs" role="tablist">
				<li class="active"><a href="#ma-followup-tab" data-toggle="tab">Follow-up</a></li>
				<li><a href="#ma-quote-tab" data-toggle="tab">Quotation sent</a></li>
			</ul>
			<div class="tab-content sp-tab-content">

				<div class="tab-pane active" id="ma-followup-tab">
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Record a follow-up</h3><p>Calls, visits, emails and meetings. Leave the case blank for general activity that is not tied to a case. You can edit or void only the follow-ups you recorded.</p></div></div>
						<div id="ma-edit-banner" class="sp-assumption-note" style="display:none"></div>
						<form id="ma-followup-form" class="sp-followup-form">
							<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="action" value="add_followup">
							<input type="hidden" name="idempotency_key" value="">
							<input type="hidden" name="corrected_from_id" value="">
							<div class="sp-followup-fields">
								<label>Date<input class="form-control" type="date" name="activity_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
								<label>Time<input class="form-control" type="time" name="activity_time" value="<?php echo htmlspecialchars($now, ENT_QUOTES, 'UTF-8'); ?>" required></label>
								<label>Sales case<select class="form-control ma-case-select" name="salescaseref"></select></label>
								<label>Quotation (optional)<select class="form-control ma-quote-select" name="quotation_orderno"><option value="">None</option></select></label>
								<label>Activity type<select class="form-control" name="activity_type"><option value="call">Call</option><option value="visit">Visit</option><option value="email">Email</option><option value="meeting">Meeting</option><option value="other">Other</option></select></label>
								<label>Outcome<input class="form-control" type="text" name="outcome" maxlength="255" placeholder="Brief outcome"></label>
								<label>Next action<input class="form-control" type="text" name="next_action" maxlength="500" placeholder="Optional next step"></label>
								<label>Next action date<input class="form-control" type="date" name="next_action_date"></label>
								<label class="sp-followup-notes">Notes<textarea class="form-control" name="notes" maxlength="4000" rows="2" placeholder="Optional notes"></textarea></label>
								<label class="sp-followup-notes ma-correction-only" style="display:none">Reason for the edit (required)<textarea class="form-control" name="correction_reason" maxlength="500" rows="2" placeholder="What was wrong with the original entry"></textarea></label>
							</div>
							<div class="sp-form-actions"><button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Save follow-up</button> <button type="button" id="ma-cancel-edit" class="btn btn-default" style="display:none">Cancel edit</button><span class="ma-form-message" aria-live="polite"></span></div>
						</form>
					</div>
				</div>

				<div class="tab-pane" id="ma-quote-tab">
					<div class="sp-card">
						<div class="sp-card-heading"><div><h3>Record a quotation sent</h3><p>Record the date you actually sent the quotation to the customer. Only your own quotations from the last 180 days are listed.</p></div></div>
						<form id="ma-quote-form" class="sp-followup-form">
							<input type="hidden" name="FormID" value="<?php echo htmlspecialchars($_SESSION['FormID'], ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="action" value="record_quote_issue">
							<input type="hidden" name="idempotency_key" value="">
							<div class="sp-followup-fields">
								<label>Quotation<select class="form-control" name="orderno" data-required="1"></select></label>
								<label>Issue type<select class="form-control" name="issue_kind"><option value="issued">First issue</option><option value="revised">Revised</option><option value="resent">Sent again</option></select></label>
								<label>Date sent<input class="form-control" type="date" name="issued_date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" max="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required></label>
								<label>Time<input class="form-control" type="time" name="issued_time" value="<?php echo htmlspecialchars($now, ENT_QUOTES, 'UTF-8'); ?>" required></label>
								<label class="sp-followup-notes">Evidence reference<input class="form-control" type="text" name="evidence_reference" maxlength="500" placeholder="Optional email or file reference"></label>
							</div>
							<div class="sp-form-actions"><button type="submit" class="btn btn-primary">Save quotation sent</button><span class="ma-form-message" aria-live="polite"></span></div>
						</form>
					</div>
				</div>
			</div>

			<div class="sp-card" id="ma-history">
				<div class="sp-card-heading"><div><h3>My recent entries</h3><p>Entries from the last 60 days. A voided entry stays in the audit history and no longer counts in reports.</p></div></div>
				<h4 class="sp-subtable-title">Follow-ups</h4>
				<div class="table-responsive"><table class="table table-striped sp-table" id="ma-followup-table"></table></div>
				<h4 class="sp-subtable-title">Enquiry events</h4>
				<div class="table-responsive"><table class="table table-striped sp-table" id="ma-enquiry-table"></table></div>
				<h4 class="sp-subtable-title">Customer POs</h4>
				<div class="table-responsive"><table class="table table-striped sp-table" id="ma-po-table"></table></div>
			</div>
		</div>
	</section>
</div>

<?php include_once('includes/footer.php'); ?>
<script src="assets/bower_components/select2/dist/js/select2.full.min.js"></script>
<script src="assets/salesActivity.js"></script>
