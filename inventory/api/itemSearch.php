<?php
	//setting prefix
	$PathPrefix='../../';
	//file include
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	//Handling Post Variables
	$category 		= $_POST['StockCat'];
	$stockid  		= $_POST['StockCode'];
	$qty  			= $_POST['qty'];
	$description 	= str_replace(" ", "%", $_POST['Description']);

	if($category == "All"){
		$category = "";
	}
	//SQL Queries
 	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.description,
					stockmaster.conditionID,
					manufacturers.manufacturers_name as mname,
					SUM(locstock.quantity) AS qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
			INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
			WHERE (stockmaster.stockid LIKE '%".$stockid."%'
				OR stockmaster.mnfcode LIKE '%".$stockid."%')
			AND stockmaster.stockid NOT LIKE '%\t%'
			AND stockmaster.description LIKE '%".$description."%'
			AND stockmaster.categoryid LIKE '%" . $category . "%'
			GROUP BY stockmaster.stockid";
	
	if($qty == "zero"){
		$SQL .= " HAVING qoh = 0";
	}else if($qty == "non-zero"){
		$SQL .= " HAVING qoh != 0";
	}

	$SQL .=	" ORDER BY stockmaster.stockid";

	$result = mysqli_query($db, $SQL);

	$response = [];

	while($row = mysqli_fetch_assoc($result)){

		$row['action'] = "<a class='btn btn-success actinf' href='../SelectProduct.php?Select=".$row['stockid']."' target='_blank'>View</a> ";
		$row['action'] .= "<a class='btn btn-info actinf' href='../StockStatus.php?StockID=".$row['stockid']."' target='_blank'>Status</a> ";

		// ====This is for testing purpose==== //

		$sql = "SELECT locations.locationname,
				locstock.quantity
		FROM locstock INNER JOIN locations
		ON locstock.loccode=locations.loccode
		WHERE locstock.stockid = '" . $row['stockid'] . "'";
		$locstockResult = mysqli_query($db, $sql);
	$check = [];
		while($data = mysqli_fetch_assoc($locstockResult)){
			$check[] = $data['quantity'];
		}
	$row['QOH'] = $check;
		// ====This is ending of for testing purpose==== //

		if($row['conditionID']==1)
			$row['stockid'] = '<span style="color:black !important">'.$row['stockid'].'</span>';
		if($myrow['conditionID']==2)
			$row['stockid'] = '<span style="color:blue !important">'.$row['stockid'].'</span>';
		if($row['conditionID']==3)
			$row['stockid'] = '<span style="color:brown !important">'.$row['stockid'].'</span>';
		if($row['conditionID']==4)
			$row['stockid'] = '<span style="color:orange !important">'.$row['stockid'].'</span>';
		$response[] = $row;
		
	}

	utf8_encode_deep($response);
	
	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);
	echo $eresponse;

	return;