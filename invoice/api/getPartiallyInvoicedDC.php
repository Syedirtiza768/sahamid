<?php 

	include("../misc.php");

	$db = createDBConnection();

	$SQL = "SELECT * FROM dcgroups WHERE status = 0";
	$res = mysqli_query($db, $SQL);

	$pInv = [];

	while($row = mysqli_fetch_assoc($res)){

		$invoice = [];
		$invoice['group'] = $row['id'];
		$dcnos 	 = $row['dcnos'];

		$invoice['dc'] = [];
		$dcListOld  = explode(",",$dcnos);

		foreach($dcListOld as $dc)
			if(trim($dc) != "")
				$invoice['dc'][] = $dc;

		$SQL = "SELECT * FROM invoice 
				WHERE groupid='".$invoice['group']."'
				AND inprogress=1";
		$countResult = mysqli_query($db,$SQL);
		$invoiceCount = mysqli_num_rows($countResult);

		if($invoiceCount > 0){
			$invoice['existing'] = 1;
			$invoice['invoiceno'] =  mysqli_fetch_assoc($countResult)['invoiceno'];
		}

		$pInv[] = $invoice;

	}

	echo json_encode($pInv);