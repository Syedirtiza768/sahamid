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

<div class="col-md-8" style="padding:0px 50px" data-code="salesCaseReview">
	<div class="card ">
		<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
			<i class="fa fa-trash removeWidget"></i>
		</div>
		<div class="card-header ">
			<h3 class="box-title">
				Salescases To Be Reviewed Today&nbsp;&nbsp;&nbsp;
				<a class="btn btn-info" href="../salescase/salescaseWatchlist.php" style="padding-top:3px; padding-bottom:3px;" target="_blank">
					View Full Watchlist
				</a>
			</h3>
		</div>
		<div class="card-body ">
			<div class="row">
				<div class="col-md-12">
					<div class="table-responsive">
						<table class="table">
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
			</div>
		</div>
	</div>
</div>

