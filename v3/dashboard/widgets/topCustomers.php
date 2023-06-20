<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "topCustomers";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$response = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as value, debtorsmaster.name,
						debtorsmaster.debtorno FROM invoice 
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
							AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
							AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				GROUP BY invoice.debtorno";

		$res = mysqli_query($db, $SQL);

		$customers = [];

		while($row = mysqli_fetch_assoc($res)){
			$customers[] = $row;
		}

		usort($customers, function ($a, $b){
		    if ($a['value'] == $b['value'])
		        return 0;
		    return ($a['value'] > $b['value']) ? -1 : 1;
		});

		$response = [];

		$count = 1;
		foreach ($customers as $customer) {
		
			if($count > 5)	break;
		
			$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as value FROM invoice 
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
							AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
							AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND invoice.debtorno = '".$customer['debtorno']."'
				AND invoice.invoicesdate >= '".date("Y-m-01")."'
				AND invoice.invoicesdate <= '".date("Y-m-t")."'
				GROUP BY invoice.debtorno";

			$customer['count'] = $count;
			$customer['value'] = round($customer['value'],2);
			$customer['thisMonth'] = round(mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'] ?:0,2);

			$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as value FROM invoice 
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
							AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
							AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND invoice.debtorno = '".$customer['debtorno']."'
				AND invoice.invoicesdate >= '".date("Y-m-01", strtotime('-3 month'))."'
				AND invoice.invoicesdate <= '".date("Y-m-t")."'
				GROUP BY invoice.debtorno";
				
			$customer['tMonths'] = round(mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'] ?:0,2);
			
			$response[] = $customer;
			
			$count++;

		}

		$value = json_encode($response);
		$refreshed_at = date('Y-m-d H:i:s');

		$SQL = "SELECT * FROM cache WHERE unique_key = '$key'";
		$ress = mysqli_query($db, $SQL);

		if(mysqli_num_rows($ress) == 0){
			$SQL = "INSERT INTO cache(unique_key,value,refreshed_at) 
					VALUES('$key','$value','$refreshed_at')";
		}else{
			$SQL = "UPDATE cache 
					SET value = '$value',
						refreshed_at = '$refreshed_at'
					WHERE unique_key = '$key'";
		}

		mysqli_query($db, $SQL);

	}

?>
<div class="col-md-6 item" data-code="topCustomers">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div class="box box-info" style="min-height: 250px; margin-bottom: 0;">
    <div class="box-header">
		<h3 class="box-title">
			Top 5 Customers&nbsp;&nbsp;&nbsp;
		</h3>
    </div>
    <div class="box-body no-padding item-content">
      <table class="table table-condensed table-striped">
        <thead>
        	<tr>
              <th style="">#</th>
              <th>Name</th>
              <th>Sales Total</th>
			  <th>3 Months</th>
			  <th>Current</th>
            </tr>
        </thead>
        <tbody id="topCustomer">
        	<?php foreach($response as $customer){ ?> 
        	<tr>
    		<?php if(!is_array($customer)){ ?>
        		<td><?php echo $customer->count; ?></td>
        		<td><?php echo $customer->name; ?></td>
        		<td><?php echo $customer->value; ?></td>
        		<td><?php echo $customer->tMonths; ?></td>
        		<td><?php echo $customer->thisMonth; ?></td>
        	<?php }else{ ?>
				<td><?php echo $customer['count']; ?></td>
        		<td><?php echo $customer['name']; ?></td>
        		<td><?php echo $customer['value']; ?></td>
        		<td><?php echo $customer['tMonths']; ?></td>
        		<td><?php echo $customer['thisMonth']; ?></td>
        	<?php } ?>
        	</tr>
            <?php } ?>
      	</tbody>
      </table>
    </div>
  </div>
</div>