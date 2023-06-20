<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "topSalesPersonsThisMonthTotal";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$TopSalesPersons = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,
							custbranch.salesman, invoice.gst, invoice.services
							FROM invoice 
				INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
					AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
					AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND invoice.invoicesdate >= '".date("Y-m-01")."'
				AND invoice.invoicesdate <= '".date("Y-m-t")."'
				GROUP BY invoice.invoiceno";

		$res = mysqli_query($db, $SQL);

		$TopSalesPersons = [];

		while($row = mysqli_fetch_assoc($res)){

			if(!array_key_exists($row['salesman'], $TopSalesPersons)){
				$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$row['salesman']."'";
				$TopSalesPersons[$row['salesman']] = [];
				$TopSalesPersons[$row['salesman']]['name'] =
				mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];
				$TopSalesPersons[$row['salesman']]['sales'] = 0;
			}
			
			$percent = $row['services'] == 1 ? 1.16:1.17;
				
			$price = 0;
				
			if($row['gst'] == "inclusive"){
				$price += $row['price']/$percent;
			}else{
				$price += $row['price'];
			}

			$TopSalesPersons[$row['salesman']]['sales'] += $price;

		}

		usort($TopSalesPersons, function ($a, $b){
		    if ($a['sales'] == $b['sales'])
		        return 0;
		    return ($a['sales'] > $b['sales']) ? -1 : 1;
		});

		$value = json_encode($TopSalesPersons);
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

<div class="col-md-4 item" data-code="topSalesPersonsThisMonth">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div class="box box-info" style="min-height: 250px; margin-bottom: 0; ">
    <div class="box-header">
      <h3 class="box-title">Top 5 Sales Persons (This Month)</h3>
    </div>
    <div class="box-body no-padding item-content">
      <table class="table table-condensed table-striped">
        <thead>
        	<tr>
              <th style="width: 10px">#</th>
              <th>Salesman</th>
              <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; foreach ($TopSalesPersons as $key => $value) { if($count > 5) break; ?>
            <tr>
              <td style="width: 10px"><?php echo $count; ?></td>
              <td><?php echo $value->name; ?></td>
              <td><?php echo round($value->sales); ?> <sub>PKR</sub></td>
            </tr>
            <?php $count++; } ?>
      	</tbody>
      </table>
    </div>
  </div>
</div>