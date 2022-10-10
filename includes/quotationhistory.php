<?php

include('includes/session.inc');

echo "<h3>Quotation History</h3>";
echo "
<table border = 1>
<tr><td>Quotation Link</td>
<td>Factor</td>
<td>Client Category</td>
<td>Client</td>
<td>Rate</td>
<td>Quantity</td>
<td>Salesperson</td>
<td>Date</td>

</tr>



";
$conn = mysqli_connect('localhost','irtiza','netetech321','sahamid');
 $sql = 'SELECT salesorders.orderno,salescase.salesman,debtortype.typename,debtorsmaster.name, 
discountpercent, unitprice*(1-discountpercent) as price,salesorderdetails.quantity*salesorderoptions.quantity as quantity, orddate FROM `salesorderdetails`
 inner join salesorders on salesorders.orderno = salesorderdetails.orderno inner join salescase on
 salesorders.salescaseref = salescase.salescaseref inner join debtorsmaster on salescase.debtorno = 
 debtorsmaster.debtorno inner join debtortype on debtorsmaster.typeid = debtortype.typeid 
 inner join salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno
AND salesorderdetails.lineoptionno = salesorderoptions.optionno ) 
where stkcode = "'.$_GET['stockid'].'"';
$result = mysqli_query($conn,$sql);
echo "hello".$_SESSION['AccessLevel'];
while($myrow = mysqli_fetch_assoc($result))
{
	echo '<tr>';
	if (($_SESSION[AccessLevel] == 10 OR $_SESSION[AccessLevel] == 8 OR $_SESSION[AccessLevel] == 22 OR $_SESSION[AccessLevel] == 27  OR ($myrow['salesman'] == $_SESSION[UsersRealName]))
	echo '<td><a target = "_blank" href="'.$RootPath.'/PDFQuotation.php?QuotationNo=' . $myrow['orderno'] .  '">'.$myrow['orderno'].'</a></td>';
	else echo '<td></td>';
	if (($_SESSION[AccessLevel] == 10 OR $_SESSION[AccessLevel] == 8 OR $_SESSION[AccessLevel] == 22 OR $_SESSION[AccessLevel] == 27  OR ($myrow['salesman'] == $_SESSION[UsersRealName]))
	echo '<td>'.$myrow['discountpercent'].'</td>';
	else echo '<td></td>';
	
	echo '<td>'.$myrow['typename'].'</td>';
if (($_SESSION[AccessLevel] == 10 OR $_SESSION[AccessLevel] == 8 OR $_SESSION[AccessLevel] == 22 OR $_SESSION[AccessLevel] == 27  OR ($myrow['salesman'] == $_SESSION[UsersRealName]))
		echo '<td>'.$myrow['name'].'</td>';
else echo '<td></td>';
if (($_SESSION[AccessLevel] == 10 OR $_SESSION[AccessLevel] == 8 OR $_SESSION[AccessLevel] == 22 OR $_SESSION[AccessLevel] == 27  OR ($myrow['salesman'] == $_SESSION[UsersRealName]))
	echo '<td>'.locale_number_format($myrow['price'], 2).'</td>';
else echo '<td></td>';

echo '<td>'.$myrow['quantity'].'</td>';
echo '<td>'.$myrow['salesman'].'</td>';
echo '<td>'.$myrow['orddate'].'</td>';

echo '</tr>';

	
	
	
}

echo "</table>";






?>