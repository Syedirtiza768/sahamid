<?php

$conn=mysqli_connect('localhost','irtiza','netetech321','sahamid');


echo "List of stock items with quantities available in multiple IS substores 
where quantity in atleast one store is negative</br>";
$SQL = "SELECT * FROM `substorestock` WHERE quantity < 0 ";
$result=mysqli_query($conn,$SQL);
$counter=0;
while ($row=mysqli_fetch_assoc($result))
{
	
	
	$stockid=$row['stockid'];
	$loccode=$row['loccode'];


	
	$SQL1="SELECT count(*) as count FROM `substorestock` WHERE `loccode` LIKE '$loccode' 
	AND `stockid` LIKE '$stockid' AND quantity!=0 ORDER BY `quantity` DESC";

	$res=mysqli_query($conn,$SQL1);
	$row1=mysqli_fetch_assoc($res);
	
	if($row1['count']>1)
	{
		$counter++;	
		echo"<b>$counter ---- $stockid ---- </b>";
		
		$SQL2 = "SELECT description,quantity FROM substorestock INNER JOIN substores ON substores.substoreid = substorestock.substoreid
		WHERE quantity !=0 AND stockid = '$stockid' AND (`loccode` LIKE '$loccode' OR `loccode` LIKE 'OS' )  ";
		$res1=mysqli_query($conn,$SQL2);
		echo "<table border='1'><tr>";	
		while($row2=mysqli_fetch_assoc($res1))
		{
			
		echo "<td>".$row2['description']." ".$row2['quantity']."</td>";
		
		}
		
		echo "</tr></table> <br><br>";
	}
	
	
}

?>