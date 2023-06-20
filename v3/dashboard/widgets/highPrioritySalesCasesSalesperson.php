<?php 

	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}




		$SQL = "SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
					salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
					`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
					`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba,debtorsmaster.name as client, 
					custbranch.defaultlocation, salescase.priority, salescase.priority_updated_by 
				FROM salescase INNER JOIN salescase_watchlist ON salescase.salescaseref=salescase_watchlist.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
				LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
												AND salescase.branchcode = custbranch.branchcode)
				WHERE salescase.closed = 0
				AND salescase_watchlist.priority = 1
				AND salescase_watchlist.userid='".$_SESSION['UserID']."'
				ORDER BY salescase_watchlist.created_at DESC LIMIT 10";



	    $topSalescases = (mysqli_query($db, $SQL));
	
?>

<div class="col-md-12 item" data-code="highPrioritySalesCasesSalesperson">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div class="box box-info" style="min-height: 250px">
    <div class="box-header">
		<h3 class="box-title">High Priority Salescases (Individual)
			<a  class="btn btn-info" 
				href="../salescase/selectSalescaseFilter.php" 
				style="padding-top:3px; padding-bottom:3px;"
				target="_blank">
				View All Salescases
			</a>
		</h3>
    </div>
    <div class="box-body no-padding item-content">
      <table class="table table-condensed table-striped">
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
			<?php foreach($topSalescases as $salescase){ ?>
			<tr>
				<td style="font-size:14px"><?php echo ($salescase['salescaseindex']); ?></td>
				<td style="font-size:14px"><?php echo ($salescase['salescaseref']); ?></td>
				<td><?php echo (date('Y-m-d',strtotime($salescase['commencementdate']))); ?></td>
				<td style="font-size:14px"><?php echo ($salescase['client']); ?></td>
				<td style="font-size:14px"><?php echo ($salescase['salesman']); ?></td>
				<td style="font-size:14px"><?php echo ($salescase['dba']); ?></td>
				<td>
					<a  href="../salescase/salescaseview.php?salescaseref=<?php echo ($salescase['salescaseref']); ?>"
						target="_blank"
						style="font-size:12px"
						class="btn btn-info"
					>
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