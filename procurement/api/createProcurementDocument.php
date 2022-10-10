<?php 
	
	$PathPrefix='../../';
	
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!userHasPermission($db,'create_procurement_document')){
		echo json_encode([
				'status' => 'error',
				'message' => 'Permission Denied'
			]);
		return;
	}

	if(!isset($_POST['supplier'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$supplierid = $_POST['supplier'];
	$date  		= date('Y-m-d H:i:s');
	$stage 		= 'Commencement';

	$timeline = [];
	$timeline[] = [
		'stage' => $stage,
		'date'  => $date
	];

	$timeline = json_encode($timeline);

	$SQL = "INSERT INTO procurement_document (supplierid, stage, commencement_date, timeline)
			VALUES ('$supplierid','$stage','$date','$timeline')";
	
	if(!DB_query($SQL, $db)){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Something went wrong.',
				'SQL'		=> $SQL
			]);
		return;
	}

	$pdid = $_SESSION['LastInsertId'];

	$SQL = "SELECT * FROM suppliers WHERE supplierid=$supplierid";
	$res = mysqli_query($db, $SQL);
	$supplier = mysqli_fetch_assoc($res);

	$document 				= [];
	$document['id'] 		= $pdid;
	$document['stage'] 		= $stage;
	$document['spermission']= $stages[$document['stage']];
	$document['nextstage']  = $stage;
	$document['supplier']	= $supplier['suppname'];
	$document['published']  = null;
	$document['canceled']   = null;
	$document['received']   = null;
	$document['timeline']	= [
								[
									'stage' => 'Commencement',
									'date'  => date('d/m/Y')
								]
							  ];
	$document['items']		= [];

	echo json_encode([
			'status' 	=> 'success',
			'document' 	=> $document
		]);
	return;