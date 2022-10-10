<?php 

	include('../misc.php');

	$db = createDBConnection();
		
	$SQL = "SELECT salesman FROM salescase 
			WHERE salescaseref='".$_POST['salescaseref']."'";

	$result = mysqli_query($db,$SQL);

	$row = mysqli_fetch_assoc($result);

	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.description,
					stockmaster.mnfcode,
					stockmaster.longdescription,
					stockmaster.mnfCode,
					stockmaster.mnfpno,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockissuance.issued AS qoh,
					stockmaster.units,
					manufacturers.manufacturers_name as mname,
					stockmaster.decimalplaces
				FROM stockmaster 
				INNER JOIN manufacturers
				ON stockmaster.brand = manufacturers.manufacturers_id
				INNER JOIN stockissuance
				ON stockmaster.stockid=stockissuance.stockid
				where stockissuance.salesperson = '".$row['salesman']."'
				AND stockmaster.stockid LIKE '%".$_POST['StockCode']."%'
				AND stockissuance.issued > 0
				ORDER BY stockissuance.issued desc";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){

		$row['action'] = "<button class='btn btn-success' style='width:100%; height:100%; margin:0' onclick='insertitem(\"".$row['stockid'] ."\",\"".$row['mname']."\")'>Add To Option</button>";
		$response[] = $row;

	}

	utf8_encode_deep($response);

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;

	return;
	
	
?>