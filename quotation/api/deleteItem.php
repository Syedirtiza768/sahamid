<?php 

	include('../misc.php');

	$response = [];

	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['itemIndex'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}
    $db = createDBConnection();
	$index = $_POST['itemIndex'];
    $SQL="SELECT stkcode,orderno,orderlineno FROM salesorderdetailsip WHERE `salesorderdetailsindex`=".$index."";
    $row=mysqli_fetch_assoc(mysqli_query($db,$SQL));
    $line=$row['orderlineno'];
    $orderno=$row['orderno'];
    $item_id=$row['stkcode'];

	$SQL = "DELETE FROM `salesorderdetailsip` WHERE salesorderdetailsindex=".$index."";



	$result = mysqli_query($db, $SQL);

    $SQL = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$line,'Item Deleted (".$item_id.") by ".$_SESSION['UsersRealName']."','deleteitem')";

    mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response = [
			'status' => 'success',
			'data' => [
				'index' => $index
			]
		];

	echo json_encode($response);
	return;	

?>