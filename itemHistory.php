<?php

	$AllowAnyone = true;

	include('includes/session.inc');

	$SQL = "SELECT www_users.realname FROM salescase_permissions 
			INNER JOIN www_users ON www_users.userid = salescase_permissions.can_access
			WHERE user='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	$canAccess = [];

	while($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['realname'];

	$stockID = $_GET['stockid'];
	$salesman = $_SESSION['UsersRealName'];
	$canAccessString = implode('\',\'', $canAccess);
	$AccessLevelArray = [8,10,22];

	$SQL = "SELECT  salescase.salescaseref,
					debtorsmaster.name as client, 
					salescase.salesman, 
					AVG(salesorderdetails.discountpercent) as avgQuotDiscount, 
					AVG(salesorderdetails.unitprice*(1-salesorderdetails.discountpercent)) AS avgQuotPrice,
					SUM(salesorderdetails.quantity*salesorderoptions.quantity) AS totalQuotQuantity
			FROM salescase
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
			INNER JOIN salesorders ON salesorders.salescaseref = salescase.salescaseref
			INNER JOIN salesorderoptions ON salesorderoptions.orderno = salesorders.orderno
			INNER JOIN salesorderdetails ON (
				salesorderdetails.orderno = salesorderoptions.orderno AND 
				salesorderdetails.orderlineno = salesorderoptions.lineno AND 
				salesorderdetails.lineoptionno = salesorderoptions.optionno 
			)
			WHERE salesorderdetails.stkcode = '$stockID'";

	if(!in_array($_SESSION['AccessLevel'], $AccessLevelArray)){
		$SQL .= " AND ( 
					salescase.salesman ='$salesman' OR 
					salescase.salesman IN ('$canAccessString') 
				)";
	}
			
	$SQL .= " GROUP BY salescase.salescaseref";
	$res = mysqli_query($db, $SQL);

	$data = [];
	while($row = mysqli_fetch_assoc($res)){
		$data[$row['salescaseref']] = $row;
	}

	$SQL = "SELECT  salescase.salescaseref,
					debtorsmaster.name as client, 
					salescase.salesman, 
					AVG(ocdetails.discountpercent) as avgOCDiscount, 
					AVG(ocdetails.unitprice*(1-ocdetails.discountpercent)) AS avgOCPrice,
					SUM(ocdetails.quantity*ocoptions.quantity) AS totalOCQuantity
			FROM salescase
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
			INNER JOIN ocs ON ocs.salescaseref = salescase.salescaseref
			INNER JOIN ocoptions ON ocoptions.orderno = ocs.orderno
			INNER JOIN ocdetails ON (
				ocdetails.orderno = ocoptions.orderno AND 
				ocdetails.orderlineno = ocoptions.lineno AND 
				ocdetails.lineoptionno = ocoptions.optionno 
			)
			WHERE ocdetails.stkcode = '$stockID'";

	if(!in_array($_SESSION['AccessLevel'], $AccessLevelArray)){
		$SQL .= " AND ( 
					salescase.salesman ='$salesman' OR 
					salescase.salesman IN ('$canAccessString') 
				)";
	}
			
	$SQL .= " GROUP BY salescase.salescaseref";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){
		if(!isset($data[$row['salescaseref']]))
			$data[$row['salescaseref']] = $row;
		else{
			$data[$row['salescaseref']]['avgOCDiscount'] = $row['avgOCDiscount'];
			$data[$row['salescaseref']]['avgOCPrice'] = $row['avgOCPrice'];
			$data[$row['salescaseref']]['totalOCQuantity'] = $row['totalOCQuantity'];
		}
	}

	$SQL = "SELECT  salescase.salescaseref,
					debtorsmaster.name as client, 
					salescase.salesman,
					AVG(dcdetails.discountpercent) as avgDCDiscount, 
					AVG(dcdetails.unitprice*(1-dcdetails.discountpercent)) AS avgDCPrice,
					SUM(dcdetails.quantity*dcoptions.quantity) AS totalDCQuantity
			FROM salescase
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
			INNER JOIN dcs ON dcs.salescaseref = salescase.salescaseref
			INNER JOIN dcoptions ON dcoptions.orderno = dcs.orderno
			INNER JOIN dcdetails ON (
				dcdetails.orderno = dcoptions.orderno AND 
				dcdetails.orderlineno = dcoptions.lineno AND 
				dcdetails.lineoptionno = dcoptions.optionno
			)
			WHERE dcdetails.stkcode = '$stockID'";

	if(!in_array($_SESSION['AccessLevel'], $AccessLevelArray)){
		$SQL .= " AND ( 
					salescase.salesman ='$salesman' OR 
					salescase.salesman IN ('$canAccessString') 
				)";
	}
			
	$SQL .= " GROUP BY salescase.salescaseref";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){
		if(!isset($data[$row['salescaseref']]))
			$data[$row['salescaseref']] = $row;
		else{
			$data[$row['salescaseref']]['avgDCDiscount'] = $row['avgDCDiscount'];
			$data[$row['salescaseref']]['avgDCPrice'] = $row['avgDCPrice'];
			$data[$row['salescaseref']]['totalDCQuantity'] = $row['totalDCQuantity'];
		}
	}

	$data = array_map(function($row){
		if(!isset($row['avgQuotDiscount']))
			$row['avgQuotDiscount'] = 0;
		
		if(!isset($row['avgQuotPrice']))
			$row['avgQuotPrice'] = 0;

		if(!isset($row['totalQuotQuantity']))
			$row['totalQuotQuantity'] = 0;

		if(!isset($row['avgOCDiscount']))
			$row['avgOCDiscount'] = 0;

		if(!isset($row['avgOCPrice']))
			$row['avgOCPrice'] = 0;

		if(!isset($row['totalOCQuantity']))
			$row['totalOCQuantity'] = 0;

		if(!isset($row['avgDCDiscount']))
			$row['avgDCDiscount'] = 0;

		if(!isset($row['avgDCPrice']))
			$row['avgDCPrice'] = 0;

		if(!isset($row['totalDCQuantity']))
			$row['totalDCQuantity'] = 0;

		return $row;

	}, $data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Item History</title>
</head>
<body>
	<h3>Item History</h3>
	<table border="1">
		<thead>
			<tr>
				<th>Salescaseref</th>
				<th>Salesman</th>
				<th>Client</th>
				<th>avgQuotDiscount</th>
				<th>avgQuotPrice</th>
				<th>totalQuotQuantity</th>
				<th>avgOCDiscount</th>
				<th>avgOCPrice</th>
				<th>totalOCQuantity</th>
				<th>avgDCDiscount</th>
				<th>avgDCPrice</th>
				<th>totalDCQuantity</th>
			</tr>
		</thead>
		<tbody>	
		<?php foreach($data as $row){ ?>
			<tr>
				<td><a href="/sahamid/salescase/salescaseview.php?salescaseref=<?php echo $row['salescaseref']; ?>" href="_blank"><?php echo $row['salescaseref']; ?></a></td>
				<td><?php echo $row['salesman']; ?></td>
				<td><?php echo $row['client']; ?></td>
				<td><?php echo $row['avgQuotDiscount']; ?></td>
				<td><?php echo round($row['avgQuotPrice']); ?></td>
				<td><?php echo $row['totalQuotQuantity']; ?></td>
				<td><?php echo $row['avgOCDiscount']; ?></td>
				<td><?php echo round($row['avgOCPrice']); ?></td>
				<td><?php echo $row['totalOCQuantity']; ?></td>
				<td><?php echo $row['avgDCDiscount']; ?></td>
				<td><?php echo round($row['avgDCPrice']); ?></td>
				<td><?php echo $row['totalDCQuantity']; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</body>
</html>