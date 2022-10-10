
<?php

	include("../../quotation/misc.php");
	session_start();

	$category = $_POST['cat'];

	$db = createDBConnection();

	if($category == "ALL"){
		$SQL = "SELECT manufacturers_id,manufacturers_name FROM manufacturers";
	}else{
		$SQL = "SELECT DISTINCT manufacturers_id,manufacturers_name FROM manufacturers, stockmaster, stockcategory
		WHERE stockmaster.brand = manufacturers.manufacturers_id
		AND stockmaster.categoryid = stockcategory.categoryid
		AND stockcategory.categoryid = '".$category."'
		ORDER BY manufacturers_name";
	}

	$res = mysqli_query($db, $SQL);

	echo "<select class='search' id='searchbrand' style='width:100%'>";	        			
	echo '<option value="ALL">ALL</option>';

	while($r = mysqli_fetch_assoc($res)){

		echo '<option value="' . $r['manufacturers_id'] . '">' . $r['manufacturers_name'] . '</option>';

	}

	echo "</select>";