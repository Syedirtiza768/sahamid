<?php 
	
	$PathPrefix='../../';
	
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!isset($_GET['document'])){
		echo "<h2 class='text-center'>Missing Parms!</h2>";
		return;
	}

	if(!userHasPermission($db, "print_procurement_document")){
		echo "<h2 class='text-center'>User Does Not Have Permission<br>To Print This Document!</h2>";
		return;
	}

	$id = $_GET['document'];

	$SQL = "SELECT 
				procurement_document.id,
				suppliers.suppname as supplier,
				procurement_document.commencement_date as startdate,
				procurement_document.stage,
				procurement_document.timeline,
				procurement_document.publish_date as published,
				procurement_document.canceled_date as canceled,
				procurement_document.received_date as received,
				procurement_document.eta_date as eta
			FROM procurement_document 
			INNER JOIN suppliers ON suppliers.supplierid = procurement_document.supplierid
			WHERE id=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo "<h2 class='text-center'>Document Not Found!</h2>";
		return;
	}

	$document = mysqli_fetch_assoc($res);

	echo "<div style='position:relative;'>";

	if($document['canceled']){
		echo "<h3 style='display:inline-block; color:red; position:absolute; left:28%; top:50%; transform:rotate(-35deg); font-weight:bolder; font-size:4em; opacity:.2;'>CANCELED!!!</h3>";
	}
	
	echo "<table class='table table-responsive table-striped' border='1'>";
	echo "<thead>";
	echo "<tr><td colspan='6' class='text-right'>";
	echo "Document# ".$document['id'].", <br>";
	echo "Supplier: ".$document['supplier'].", <br>";
	echo "Stage: ".$document['stage'].", ";
	if(!$document['canceled'] && $document['eta']){
		echo "<br>ETA: ".date("d/m/Y",strtotime($document['eta']));
	}
	echo "</td></tr>";
	echo "<tr>";
	echo "<td>Sr#</td>";
	echo "<td>Item Code</td>";
	echo "<td>Manufacturer</td>";
	echo "<td>MNFCode</td>";
	echo "<td>MNFPNo</td>";
	echo "<td>Quantity</td>";
	echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
	
	$SQL = "SELECT 
				stockmaster.stockid,
				stockmaster.mnfcode,
				stockmaster.mnfpno,
				stockmaster.description,
				stockmaster.units,
				manufacturers.manufacturers_name as mname,
				procurement_document_details.quantity,
				procurement_document_details.price
			FROM procurement_document_details 
			INNER JOIN stockmaster ON stockmaster.stockid = procurement_document_details.stockid
			INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
			WHERE pdid=$id
			ORDER BY procurement_document_details.id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){
		echo "<tr><td colspan='6'><h3 class='text-center'>No Items Found!</h3></td></tr>";
	}

	$count = 1;
	while($row = mysqli_fetch_assoc($res)){
		echo "<tr>";
		echo "<td>".$count."</td>";
		echo "<td>".$row['stockid']."</td>";
		echo "<td>".$row['mname']."</td>";
		echo "<td>".$row['mnfcode']."</td>";
		echo "<td>".$row['mnfpno']."</td>";
		echo "<td>".$row['quantity']."</td>";
		echo "</tr>";
		$count++;
	}

	echo "</tbody>";
	echo "</table></div>";