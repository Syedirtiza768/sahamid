<?php

$active = "reports";
$AllowAnyone = true;

include_once("config.php");

if (!userHasPermission($db, "pending_dcs")) {
	header("Location: /sahamid");
	return;
}

// Fetch the can_access values for the given user
$SQL = "SELECT can_access FROM salescase_permissions WHERE user = '" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$can_access = [];

while ($row = mysqli_fetch_assoc($res)) {
	// Fetch the real names for each can_access value
	$SQL_realname = "SELECT realname FROM www_users WHERE userid = '" . $row['can_access'] . "'";
	$res_realname = mysqli_query($db, $SQL_realname);

	// Fetch the realname from the result and store it in the array
	if ($row_realname = mysqli_fetch_assoc($res_realname)) {
		$can_access[] = $row_realname['realname'];
	}
}

// Combine the results into the desired format: "Aamir Obaid","Maddy"
$can_access_string = '"' . implode('","', $can_access) . '"';

// Output or use $can_access_string as needed




if (isset($_POST['to'])) {

	if (userHasPermission($db, "pending_dcs") && !userHasPermission($db, "*")) {



		$from 	= $_POST['from'];
		$to 	= $_POST['to'];


		$SQL = 'SELECT 
dcs.orderno AS dcno,
dcs.orddate AS date,
dcs.salescaseref,
debtorsmaster.name AS client,
salescase.salesman,
debtorsmaster.dba,
SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity) AS totalamount,
dcs.gst,
CASE
	WHEN dcs.gst LIKE "%inclusive%" THEN 
		SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity) * 0.83
	ELSE 
		SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity)
END AS exclusivegsttotalamount
FROM 
dcdetails
INNER JOIN 
dcoptions ON (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno)
INNER JOIN 
dcs ON dcs.orderno = dcdetails.orderno
INNER JOIN 
salescase ON salescase.salescaseref = dcs.salescaseref
INNER JOIN 
debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
WHERE 
dcdetails.lineoptionno = 0
AND dcoptions.optionno = 0
AND salescase.salesman IN ("' . $_SESSION['UsersRealName'] . ' ",' . $can_access_string . ')
AND dcs.courierslipdate = "0000-00-00 00:00:00"
AND dcs.invoicedate = "0000-00-00 00:00:00"
AND dcs.grbdate = "0000-00-00 00:00:00"
AND dcs.invoicegroupid IS NULL
GROUP BY 
dcs.orderno;
';


		$res = mysqli_query($db, $SQL);

		// AND invoice.due >= '$from'

		$response = [];
		$SQLdcnos = "SELECT dcnos FROM dcgroups";
		$resdcnos = mysqli_query($db, $SQLdcnos);
		$dcnos = [];
		while ($rowdcnos = mysqli_fetch_assoc($resdcnos)) {
			//echo $rowdcnos['dcnos'];
			$dcnos[] = explode(",", $rowdcnos['dcnos']);
		}

		$dclist = [];
		foreach ($dcnos as $key => $value) {
			$dclist[] = $value;
		}
		//print_r($dclist);
		while ($row = mysqli_fetch_assoc($res)) {
			if (!in_array($row['dcno'], $dclist))
				$response[] = $row;
		}

		echo json_encode($response);
		return;
	} else {

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];

		$SQL = 'SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,
			salescase.salesman,debtorsmaster.dba,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	) as totalamount,dcs.gst, CASE  WHEN  dcs.gst LIKE "%inclusive%" THEN SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)*0.83 ELSE SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)   END as exclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno
		INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
		INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
		WHERE dcdetails.lineoptionno = 0  
		AND dcoptions.optionno = 0 
		AND dcs.orddate >= "' . $from . '"' . '
		AND dcs.orddate <= "' . $to . '"' . '
		AND dcs.courierslipdate = "0000-00-00 00:00:00" AND dcs.invoicedate="0000-00-00 00:00:00" 
		AND dcs.grbdate="0000-00-00 00:00:00"
		AND dcs.invoicegroupid is null
		GROUP BY dcs.orderno
		';

		$res = mysqli_query($db, $SQL);

		// AND invoice.due >= '$from'

		$response = [];
		$SQLdcnos = "SELECT dcnos FROM dcgroups";
		$resdcnos = mysqli_query($db, $SQLdcnos);
		$dcnos = [];
		while ($rowdcnos = mysqli_fetch_assoc($resdcnos)) {
			//echo $rowdcnos['dcnos'];
			$dcnos[] = explode(",", $rowdcnos['dcnos']);
		}
		$dclist = [];
		foreach ($dcnos as $key => $value) {
			$dclist[] = $value;
		}
		//print_r($dclist);
		while ($row = mysqli_fetch_assoc($res)) {
			if (!in_array($row['dcno'], $dclist))
				$response[] = $row;
		}

		echo json_encode($response);
		return;
	}
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
				res = JSON.parse(res);
				table.rows.add(res).draw();
			});
		});

	});
</script>
<?php
include_once("includes/foot.php");
?>