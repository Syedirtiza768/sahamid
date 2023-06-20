<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$SQL = "SELECT salescase_watchlist.salescaseref,
				salescase_watchlist.review_on,
				salescase_watchlist.notes,
				salescase_watchlist.priority,
				debtorsmaster.name as client_name,
				debtorsmaster.dba,
				salescase.priority as salescase_priority,
				salescase.closed as salescase_closed,
				salescase.closingreason as salescase_closingreason,
				salescase.closingremarks as salescase_closingremarks,
				salesman.salesmanname,
				salescase.salescasedescription,
				salescase.commencementdate as salescase_created_at,
				salescase_watchlist.created_at
			FROM salescase_watchlist 
			INNER JOIN salescase ON salescase_watchlist.salescaseref = salescase.salescaseref
			INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno
				AND custbranch.branchcode = salescase.branchcode)
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE salescase_watchlist.userid='" . $_SESSION['UserID'] . "'
			AND salescase_watchlist.deleted=0
			AND salescase_watchlist.review_on = '" . date('Y-m-d') . "' 
			ORDER BY salescase_watchlist.priority";

$res = mysqli_query($db, $SQL);

$response = [];

?>

<?php

$SQL = "SELECT * FROM user_permission WHERE userid='" . $_SESSION['UserID'] . "' AND permission='*' ";
$ressData = mysqli_query($db, $SQL);
while ($rowData = mysqli_fetch_assoc($ressData)) {
	$permission = $rowData['permission'];
}

?>


<div class="col-md-8 item" style="overflow:auto;" data-code="salesCaseReview">
<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%; top:10px;">
	<?php
		if ($permission == "*") {
		?>
		<select class="js-example-basic-multiple shopdata" name="states[]" multiple="multiple" style="width:95%;">
		<?php
				$SQL = "SELECT * FROM salesman ";
				$result = mysqli_query($db, $SQL);
				while ($row_salesman = mysqli_fetch_assoc($result)) {
				?>
					<option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
				<?php }
				?>
			</select>
		<?php } else {
			$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "' ";
			$resss = mysqli_query($db, $SQL); ?>
			<select class="js-example-basic-multiple shopdata" name="states[]" multiple="multiple" style="width:95%;">
				<?php while ($row = mysqli_fetch_assoc($resss)) {

					$SQL = "SELECT realname FROM www_users WHERE userid='" . $row['can_access'] . "' ";
					$result = mysqli_query($db, $SQL);
					while ($row_data = mysqli_fetch_assoc($result)) {
				?>
						<option value="<?php echo $row_data['realname']; ?>"><?php echo $row_data['realname']; ?></option>
				<?php }
				} ?>
			</select>
		<?php } ?>
		<span class="store-data" onclick="myFunctionShop()"><i style="color:red;" class="fa fa-search" aria-hidden="true"> </i></span>
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div class="box box-info" style="min-height: 250px; margin-bottom: 0px">
		<div class="box-header">
			<h3 class="box-title">
				Salescases To Be Reviewed Today&nbsp;&nbsp;&nbsp;
				<a class="btn btn-info" href="../salescase/salescaseWatchlist.php" style="padding-top:3px; padding-bottom:3px;" target="_blank">
					View Full Watchlist
				</a>
			</h3>
		</div>
		<div class="box-body no-padding">
			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th style="">#</th>
						<th>Salescaseref</th>
						<th>Client</th>
						<th>Salesman</th>
						<th></th>
					</tr>
				</thead>
				<tbody id="watchlistcontainer">
					<?php $count = 1;
					while ($row = mysqli_fetch_assoc($res)) {
						if ($count > 5)	break; ?>
						<tr>
							<td style='width: 1%; font-size:12px'><?php echo $count; ?></td>
							<td style='width: 10px; font-size:12px' class="salescaseref"><?php echo $row['salescaseref']; ?></td>
							<td style='width: 10px; font-size:12px' class="client"><?php echo $row['client_name']; ?></td>
							<td style='width: 10px; font-size:12px' class="salesman"><?php echo $row['salesmanname']; ?></td>
							<td style='width: 10px; font-size:12px'>
								<a style='font-size:10px; padding:4px 8px' href='../salescase/salescaseview.php?salescaseref="<?php echo $row['salescaseref']; ?>"' class='btn-info' target='_blank'>View</a>
							</td>
						</tr>
					<?php $count++;
					} ?>
				</tbody>
			</table>
		</div>
	</div>
	<script>
			$(document).ready(function() {
				$('.js-example-basic-multiple').select2();
			});
	</script>
</div>