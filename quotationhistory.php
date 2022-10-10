<?php

	include('includes/session.inc');

	$SQL = "SELECT www_users.realname FROM salescase_permissions 
			INNER JOIN www_users ON www_users.userid = salescase_permissions.can_access
			WHERE user='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	$canAccess = [];

	while($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['realname'];

	$SQL = 'SELECT salesorders.orderno,salescase.salesman,debtortype.typename,debtorsmaster.name, 
					AVG(discountpercent) discountpercent, AVG(unitprice*(1-discountpercent)) AS price,
					SUM(salesorderdetails.quantity*salesorderoptions.quantity) AS quantity, orddate 
			FROM `salesorderdetails`
			INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno 
			INNER JOIN salescase ON salesorders.salescaseref = salescase.salescaseref 
			INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
			INNER JOIN debtortype ON debtorsmaster.typeid = debtortype.typeid 
			INNER JOIN salesorderoptions ON (
				salesorderdetails.orderno = salesorderoptions.orderno AND 
				salesorderdetails.orderlineno = salesorderoptions.lineno AND 
				salesorderdetails.lineoptionno = salesorderoptions.optionno ) 
			WHERE stkcode = "'.$_GET['stockid'].'"
			GROUP BY salesorderdetails.orderno
			ORDER BY salesorderdetails.orderno DESC';

	echo "<h3>Quotation History</h3>";
	echo "<table border = 1>
			<tr>
				<td>Quotation Link</td>
				<td>Factor</td>
				<td>Client Category</td>
				<td>Client</td>
				<td>Rate</td>
				<td>Quantity</td>
				<td>Salesperson</td>
				<td>Date</td>
			</tr>";

	$result = mysqli_query($db,$SQL);

	while($myrow = mysqli_fetch_assoc($result)) {
		
		echo '<tr>';
		
		if (($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 8 OR $_SESSION['AccessLevel'] == 22) OR
			($myrow['salesman'] == $_SESSION['UsersRealName']) OR in_array($myrow['salesman'], $canAccess))
			echo '<td>
					<a target = "_blank" href="'.$RootPath.'/PDFQuotation.php?QuotationNo='.$myrow['orderno'].'">'
						.$myrow['orderno'].
					'</a>
				</td>';
		else echo '<td></td>';
		
		if (($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 8 OR $_SESSION['AccessLevel'] == 22) OR 
			($myrow['salesman'] == $_SESSION['UsersRealName']) OR in_array($myrow['salesman'], $canAccess))
			echo '<td>'.$myrow['discountpercent'].'</td>';
		else echo '<td></td>';
	
		echo '<td>'.$myrow['typename'].'</td>';

		if (($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 8 OR $_SESSION['AccessLevel'] == 22) OR 
			($myrow['salesman'] == $_SESSION['UsersRealName']) OR in_array($myrow['salesman'], $canAccess))
			echo '<td>'.$myrow['name'].'</td>';
		else echo '<td></td>';

		if (($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 8 OR $_SESSION['AccessLevel'] == 22) OR 
			($myrow['salesman'] == $_SESSION['UsersRealName']) OR in_array($myrow['salesman'], $canAccess))
			echo '<td>'.locale_number_format($myrow['price'], 2).'</td>';
		else echo '<td></td>';

		echo '<td>'.$myrow['quantity'].'</td>';
		echo '<td>'.$myrow['salesman'].'</td>';
		echo '<td>'.$myrow['orddate'].'</td>';

		echo '</tr>';

	}

	echo "</table>";
