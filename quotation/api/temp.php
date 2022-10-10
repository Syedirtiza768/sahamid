<?php 

	include('../misc.php');
		
	if (isset($_GET['Keywords']) AND mb_strlen($_GET['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_GET['Keywords'] = mb_strtoupper($_GET['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_GET['Keywords']) . '%';

		if ($_GET['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND stockmaster.mbflag <>'G'
					AND stockmaster.description LIKE '" . $SearchString . "'
					
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					AND stockmaster.description LIKE '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_GET['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} elseif (mb_strlen($_GET['StockCode'])>0){

		$_GET['StockCode'] = mb_strtoupper($_GET['StockCode']);
		$SearchString = '' . $_GET['StockCode'] . '';

		if ($_GET['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND (stockmaster.mnfCode LIKE '%" . $SearchString . "%'
				or stockmaster.stockid LIKE '%" . $SearchString. "%')
					
					
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND stockmaster.stockid LIKE '" . $SearchString . "'
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_GET['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_GET['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,stockmaster.materialcost,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' 
						OR stockcategory.stocktype='D' 
						OR stockcategory.stocktype='L')
					AND stockmaster.mbflag <>'G'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_GET['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		  }
	}

	echo $SQL;

	$db = createDBConnection();

	$result = mysqli_query($db, $SQL);

	$response = [];

	while($row = mysqli_fetch_assoc($result)){
		$response[] = $row;
	}

	$eresponse = json_encode($response);

	echo $eresponse;

	return;
	
	
?>