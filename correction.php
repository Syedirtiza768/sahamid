<?php

$conn=mysqli_connect('localhost','irtiza','netetech321','sahamid');




$alltables = mysqli_query($conn,"SHOW TABLES");

while ($table = mysqli_fetch_assoc($alltables))
{
   foreach ($table as $db => $tablename)
   {
       mysqli_query($conn,"OPTIMIZE TABLE '".$tablename."'")
      ;

   }
}

$SQL='SELECT substorestockcheck.stockid,substorestockcheck.QTY-locstockcheck.QTY as difference FROM `substorestockcheck` 
INNER JOIN locstockcheck ON substorestockcheck.stockid=locstockcheck.stockid 
WHERE substorestockcheck.QTY-locstockcheck.QTY!=0';


$result=mysqli_query($conn,$SQL);
echo mysqli_num_rows($result);
echo'<table>';

while($row=mysqli_fetch_assoc($result))
{
	
	echo '<tr><td>'.$row['stockid'].'</td><td> '.$row['difference'].'</td></tr>';
	
	

	
	
	
}

echo'</table>';

?>