<?php

$active = "reports";
$AllowAnyone = true;

include_once("config.php");
include_once(__DIR__ . '/includes/salesExecutiveData.php'); // sxGroupedDcNumbers(), sxValidDate()

if (!userHasPermission($db, "pending_dcs")) {
	header("Location: /sahamid");
	return;
}

if (isset($_POST['to'])) {
	header('Content-Type: application/json; charset=utf-8');

	// Dates are optional and apply to everyone. Each one given must be a real date, which also
	// keeps anything else out of the SQL.
	$from = trim((string)($_POST['from'] ?? ''));
	$to   = trim((string)($_POST['to'] ?? ''));
	if (($from !== '' && !sxValidDate($from)) || ($to !== '' && !sxValidDate($to))) {
		echo json_encode(['error' => 'Enter valid From and To dates, or leave them empty to see every pending DC.']);
		return;
	}
	$where = '';
	if ($from !== '') $where .= " AND dcs.orddate >= '" . $from . "'";
	if ($to !== '')   $where .= " AND dcs.orddate <= '" . $to . "'";

	// Without '*', a user sees their own sales cases plus the salespeople granted to them in
	// salescase_permissions.
	if (!userHasPermission($db, "*")) {
		$names = [(string)$_SESSION['UsersRealName']];
		$res = mysqli_query($db, "SELECT u.realname FROM salescase_permissions p
			INNER JOIN www_users u ON u.userid = p.can_access
			WHERE p.user = '" . mysqli_real_escape_string($db, (string)$_SESSION['UserID']) . "'");
		while ($res && ($row = mysqli_fetch_assoc($res))) $names[] = $row['realname'];
		$where .= " AND salescase.salesman IN ('" . implode("','", array_map(function ($n) use ($db) {
			return mysqli_real_escape_string($db, $n);
		}, $names)) . "')";
	}

	// Pending: not couriered, invoiced, returned (GRB) or put in an invoice group; first option only.
	$SQL = "SELECT dcs.orderno AS dcno, dcs.orddate AS date, dcs.salescaseref, debtorsmaster.name AS client,
			salescase.salesman, debtorsmaster.dba,
			SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity) AS totalamount,
			dcs.gst,
			CASE WHEN dcs.gst LIKE '%inclusive%'
				THEN SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity) * 0.83
				ELSE SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity)
			END AS exclusivegsttotalamount
		FROM dcdetails
		INNER JOIN dcoptions ON (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno)
		INNER JOIN dcs ON dcs.orderno = dcdetails.orderno
		INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
		INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
		WHERE dcdetails.lineoptionno = 0
		AND dcoptions.optionno = 0
		AND dcs.courierslipdate = '0000-00-00 00:00:00'
		AND dcs.invoicedate = '0000-00-00 00:00:00'
		AND dcs.grbdate = '0000-00-00 00:00:00'
		AND dcs.invoicegroupid IS NULL
		$where
		GROUP BY dcs.orderno";
	$res = mysqli_query($db, $SQL);
	if (!$res) {
		echo json_encode(['error' => 'The pending DCs could not be read.']);
		return;
	}

	// DCs already listed in a DC group are on their way to an invoice, so they are not pending.
	$grouped = sxGroupedDcNumbers($db) ?: [];
	$response = [];
	while ($row = mysqli_fetch_assoc($res)) {
		if (!isset($grouped[(string)$row['dcno']])) $response[] = $row;
	}
	echo json_encode($response);
	return;
}

include_once("includes/header.php");
include_once("includes/sidebar.php");

?>

<style>
	.date {
		padding: 10px;
		border-radius: 7px;
	}

	thead tr,
	tfoot tr {
		background-color: #424242;
		color: white;
	}
</style>

<div class="content-wrapper">

	<section class="content-header">
		<div class="col-md-12">
			<h1>Pending DCs</h1>
		</div>
		<!-- <label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> -->
		FROM <input type="date" class="date fromDate">
		TO <input type="date" class="date toDate">
		<button class="btn btn-success date searchData">Search</button>
	</section>

	<section class="content">

		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>
					<th>DC No</th>
					<th>Date</th>
					<th>Salescaseref</th>
					<th>Customer Name</th>
					<th>DBA</th>
					<th>Salesman</th>
					<th>Amount</th>
					<th>exclusivegsttotalamount
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>DC No</th>
					<th>Date</th>
					<th>Salescaseref</th>
					<th>Customer Name</th>
					<th>DBA</th>
					<th>Salesman</th>
					<th>Amount</th>
					<th>exclusivegsttotalamount
					</th>
				</tr>
			</tfoot>
		</table>

	</section>

</div>

<?php
include_once("includes/footer.php");
?>
<script>
	$(document).ready(function() {
		let table = $('#datatable').DataTable({
			dom: 'Bfrtip',
			buttons: [
				'excelHtml5',
				'csvHtml5',
			],
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Search..."
			},
			columns: [{
					"data": "dcno"
				},
				{
					"data": "date"
				},
				{
					"data": "salescaseref"
				},
				{
					"data": "client"
				},
				{
					"data": "dba"
				},
				{
					"data": "salesman"
				},
				{
					"data": "totalamount"
				},
				{
					"data": "exclusivegsttotalamount"
				},
			],
			"columnDefs": [

				{
					"render": function(data, type, row) {
						let html = '<a href = "../PDFDCh.php?DCNo=' + data + '">' + data + '</a>';
						return html;

					},
					"targets": [0]
				},
				{
					"render": function(data, type, row) {
						let html = '<a href="../salescase/salescaseview.php?salescaseref=' + data + '" target="_blank">' + data + '</a>';
						return html;

					},
					"targets": [2]
				}
			]
		});

		$('#datatable tfoot th').each(function(i) {
			var title = $('#datatable thead th').eq($(this).index()).text();

			$(this).html('<input type="text" placeholder="Search ' + title + '" data-index="' + i + '" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />');

		});

		table.columns().every(function() {
			var that = this;
			$('input', this.footer()).on('keyup change', function() {
				if (that.search() !== this.value) {
					that.search(this.value).draw();
				}
			});
		});

		$(".searchData").on("click", function() {
			let from = $(".fromDate").val();
			let to = $(".toDate").val();
			let FormID = '<?php echo $_SESSION['FormID']; ?>';

			table.clear().draw();
			$.post("pendingDCs.php", {
				from,
				to,
				FormID
			}, function(res, status) {
				if (typeof res === 'string') res = JSON.parse(res);
				if (!Array.isArray(res)) {
					alert(res && res.error ? res.error : 'The pending DCs could not be loaded.');
					return;
				}
				table.rows.add(res).draw();
			});
		});

	});
</script>
<?php
include_once("includes/foot.php");
?>