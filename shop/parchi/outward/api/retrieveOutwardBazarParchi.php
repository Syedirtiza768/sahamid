<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['parchi']) || trim($_POST['parchi']) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Missing Parms',

			]);
		return;

	}

	$parchino = trim($_POST['parchi']);

	$SQL = "SELECT bazar_parchi.*,suppliers.suppname as name FROM bazar_parchi 
			LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE parchino='".$parchino."' AND inprogress=1 AND discarded=0 AND settled=0";
	$parchi = mysqli_query($db, $SQL);

	if(mysqli_num_rows($parchi) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid',

			]);
		return;

	}

	$parchi = mysqli_fetch_assoc($parchi);

	if($parchi['svid'] == ''){
		$parchi['name'] = "temp (".$parchi['temp_vendor'].")";
	}

	$SQL = "SELECT realname FROM www_users WHERE userid='".$parchi['user_id']."'";
	$res = mysqli_query($db, $SQL);
	$una = mysqli_fetch_assoc($res)['realname'];

	$parchi['terms'] = ($parchi['terms']);
	$parchi['created_at'] = date('d/m/Y',strtotime($parchi['created_at']));
	$parchi['user'] = $una;

	$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND parchino='".$parchi['parchino']."'";
	$res = mysqli_query($db, $SQL);

	$items = [];

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "SELECT * FROM bpitemupdates WHERE type='price' AND bpitemid='".$row['id']."'";
		$r = mysqli_query($db, $SQL);
		$neg = "<table style='width:100%' class='negcont'>";
		while($ro = mysqli_fetch_assoc($r)){
			$neg .= "<tr>";
			// $SQL = "SELECT realname FROM www_users WHERE userid='".$ro['user_id']."'";
			// $name = mysqli_fetch_assoc(mysqli_query($db, $SQL))['realname'];
			$neg .= "<td>".locale_number_format($ro['old_value'],2)." <sub>PKR</sub></td><td> -> </td><td> ".locale_number_format($ro['new_value'],2)." <sub>PKR</sub></td><td>  (".$ro['obo'].")</td>";
			$neg .= "<td>".date("d/m/Y h:i A",strtotime($ro['created_at']))."</td>";
			$neg .= "</tr>";
		}
		$neg .= "</table>";
		$row['neg'] = $neg;
		$items[$row['id']] = $row;
	}

	$parchi['items'] = $items;
	$parchi['canAttachSKU'] = userHasPermission($db, "attach_inward_slip_sku");
	$parchi['canNegotiatePrice'] = userHasPermission($db, "negotiate_inward_slip_price");
	$parchi['canUpdateQuantity'] = userHasPermission($db, "outward_slip_item_quantity");
	$parchi['canDeleteItem'] = userHasPermission($db, "delete_inward_parchi_item");

	echo json_encode([

			'status' => 'success',
			'data' => $parchi

		]);



