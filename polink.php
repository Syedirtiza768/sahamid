<?php

include('includes/session.inc');
echo "<h3>Purchase orders for ".$_GET['salescaseref']."</h3>";
echo "
<table border = 1>
<tr>
<td>PO No</td>
<td>PO Value</td>

</tr>



";
$conn = mysqli_connect('localhost','root','','sahamid');
 $sql = 'SELECT * FROM `salescasepo` where salescaseref = "'.$_GET['salescaseref'].'"';
$result = mysqli_query($conn,$sql);
while($myrow = mysqli_fetch_assoc($result))
{
	echo '
	<tr>

<td>'.$myrow['pono'].'</td>
<td>'.$myrow['povalue'].'</td>

</tr>
';
	
	
	
}

echo "</table>";






?>