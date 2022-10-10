<?php 

	include('../misc.php');
		

	$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
	$SearchString = '%' . $_POST['StockCode'] . '%';

	if ($_POST['StockCat']=='All'){
		$SQL = "SELECT  stockmaster.stockid,
						stockmaster.mnfcode,
						stockmaster.mnfpno,
						stockmaster.description,
						SUM(locstock.quantity) AS qoh,
						manufacturers.manufacturers_name as mname
				FROM stockmaster INNER JOIN locstock 
				ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers
				ON stockmaster.brand = manufacturers.manufacturers_id
				INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockcategory.stocktype='F' 
					OR stockcategory.stocktype='D' 
					OR stockcategory.stocktype='L')
				AND (stockmaster.mnfCode LIKE '%" . $SearchString . "%'
				OR stockmaster.stockid LIKE '%" . $SearchString. "%'
				OR stockmaster.description LIKE '%" . $SearchString. "%')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.discontinued=0
				AND stockmaster.stockid NOT LIKE '%\t%'
				GROUP BY locstock.stockid
				ORDER BY stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.mnfcode,
						stockmaster.mnfpno,
						stockmaster.description,
						SUM(locstock.quantity) AS qoh,
						manufacturers.manufacturers_name as mname
				FROM stockmaster INNER JOIN locstock 
				ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers
				ON stockmaster.brand = manufacturers.manufacturers_id
				INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockcategory.stocktype='F' 
					OR stockcategory.stocktype='D' 
					OR stockcategory.stocktype='L')
				AND (stockmaster.stockid LIKE '%" . $SearchString. "%'
					OR stockmaster.description LIKE '%" . $SearchString. "%')
				AND stockmaster.mbflag <>'G'
				AND stockmaster.discontinued=0
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				AND stockmaster.stockid NOT LIKE '%\t%'
				GROUP BY locstock.stockid
				ORDER BY stockmaster.stockid";
	}
 

	$db = createDBConnection();

	$result = mysqli_query($db, $SQL);

	$response = [];

	while($row = mysqli_fetch_assoc($result)){
		$row['action'] = "<button class='btn btn-success' style='width:100%; height:100%; margin:0' onclick='insertitem(\"".$row['stockid'] ."\",\"".$row['mname']."\")'>Add To Option</button>";

		$SQL = "SELECT  SUM(locstock.quantity) as qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			WHERE stockmaster.stockid = '".$row['stockid']."'
			AND (locstock.loccode = 'HO'
				OR locstock.loccode = 'MT'
				OR locstock.loccode = 'SR')
			GROUP BY stockmaster.stockid";

		$is = mysqli_query($db, $SQL);
		$row['is'] = mysqli_fetch_assoc($is)['qoh'];

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