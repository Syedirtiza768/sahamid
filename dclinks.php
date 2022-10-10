<?php

include('includes/session.inc');
echo "<h3>DC Links</h3>";
echo "
<table border = 1>
<tr><td>DC Link</td>
<td>Date</td>
<td>Invoice Date</td>
<td>Courier Slip Date</td>

</tr>



";
$conn = mysqli_connect('localhost','irtiza','netetech321','sahamid');
$sql = 'SELECT distinct salescaseref FROM `dcs` 
where salescaseref in (SELECT salescaseref from dcs where orderno = '.$_GET['dclink'].')';
$result = mysqli_query($conn,$sql);
$myrow = mysqli_fetch_assoc($result);
$sqlA = 'SELECT * FROM `dcs` 
where salescaseref = "'.$myrow['salescaseref'].'"';
$resultA = mysqli_query($conn,$sqlA);



while($myrowA = mysqli_fetch_assoc($resultA))
{
	echo '
	<tr><td><a target = "_blank" href="'.$RootPath.'/PDFDC.php?RequestNo=' . $myrowA['orderno'] .  '">'.$myrowA['orderno'].'</a></td>
<td>'.$myrowA['orddate'].'</td>
<td>'.$myrowA['invoicedate'].'</td>
<td>'.$myrowA['courierslipdate'].'</td>


</tr>
';
	
	
	
}

echo "</table>";






?>