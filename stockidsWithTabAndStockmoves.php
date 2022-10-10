<?php
	$db=mysqli_connect("localhost","irtiza","netetech321","sahamid");

	$sql="SELECT DISTINCT (stockmaster.stockid) as stockid FROM stockmaster INNER JOIN stockmoves ON stockmaster.stockid=stockmoves.stockid WHERE stockmaster.stockid LIKE '%\t%'";	
	$result=mysqli_query($db,$sql);
	$stockid=[];
	while($row=mysqli_fetch_assoc($result))
		$stockid[]=$row['stockid'];
	echo"<table border='1'>";
	foreach($stockid as $item){
		$sqlLocStock="SELECT * FROM locstock WHERE stockid='$item'";
		$resultLocStock=mysqli_query($db,$sqlLocStock);
		$sqlSubstoreStock="SELECT * FROM substores INNER JOIN `substorestock` ON substores.substoreid=substorestock.substoreid WHERE stockid='$item'";
		$resultSubstoreStock=mysqli_query($db,$sqlSubstoreStock);

		echo"<tr>
		<td>$item</td><td>";
		while($rowLocStock=mysqli_fetch_assoc($resultLocStock)){

			echo "<table><tr><td>".$rowLocStock['loccode']."</td><td>".$rowLocStock['quantity']."</td></tr></table>";


		}
		echo"</td><td>";
		while($rowSubstoreStock=mysqli_fetch_assoc($resultSubstoreStock)){

			echo $rowSubstoreStock['description']."|".$rowSubstoreStock['quantity']."<br/>";


		}
		echo"</td></tr>";
		

	}
	echo"</table>";




?>