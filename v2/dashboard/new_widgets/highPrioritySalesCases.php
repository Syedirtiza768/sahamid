<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$allowed = [8, 10, 22];
if (in_array($_SESSION['AccessLevel'], $allowed)) {

	$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
					salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
					`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
					`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba,debtorsmaster.name as client, 
					custbranch.defaultlocation, salescase.priority, salescase.priority_updated_by 
				FROM salescase
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
				LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
												AND salescase.branchcode = custbranch.branchcode)
				WHERE salescase.closed = 0
				AND salescase.priority = "high"
				ORDER BY `salescase`.`salescaseindex` DESC LIMIT 10';
} else {

	$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
	$res = mysqli_query($db, $SQL);

	$canAccess = [];

	while ($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['can_access'];

	$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
					salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
					`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
					`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba,debtorsmaster.name as client, 
					custbranch.defaultlocation, salescase.priority, salescase.priority_updated_by 
				FROM salescase
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
				LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
												AND salescase.branchcode = custbranch.branchcode)
				WHERE salescase.closed = 0
				AND ( salescase.salesman ="' . $_SESSION['UsersRealName'] . '"
					OR www_users.userid IN ("' . implode('","', $canAccess) . '") )
				AND salescase.priority = "high"
				ORDER BY `salescase`.`salescaseindex` DESC LIMIT 10';
}

$topSalescases = (mysqli_query($db, $SQL));

?>

	<div class="col-md-8" style="padding:0px 50px" data-code="highPrioritySalesCases">
		<div class="card ">
			<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
				<i class="fa fa-trash removeWidget"></i>
			</div>
			<div class="card-header ">
				<h4 class="card-title">High Priority Salescases</h4>
				<a class="btn btn-info" href="../salescase/selectSalescaseFilter.php" style="padding-top:3px; padding-bottom:3px;" target="_blank">
					View All Salescases
				</a>
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
										<th>Start Date</th>
										<th>Client</th>
										<th>Salesman</th>
										<th>DBA</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($topSalescases as $salescase) { ?>
								<tbody>
									<tr>
										<td style="font-size:10px; padding:1px"><?php echo ($salescase['salescaseindex']); ?></td>
										<td style="font-size:10px; padding:1px"><?php echo ($salescase['salescaseref']); ?></td>
										<td><?php echo (date('Y-m-d', strtotime($salescase['commencementdate']))); ?></td>
										<td style="font-size:10px; padding:1px"><?php echo ($salescase['client']); ?></td>
										<td style="font-size:10px; padding:1px"><?php echo ($salescase['salesman']); ?></td>
										<td style="font-size:10px; padding:1px"><?php echo ($salescase['dba']); ?></td>
										<td>
											<a href="../salescase/salescaseview.php?salescaseref=<?php echo ($salescase['salescaseref']); ?>" target="_blank" style="font-size:12px" class="btn btn-info">
												View
											</a>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>