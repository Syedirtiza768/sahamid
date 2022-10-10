<?php

$conn=mysqli_connect('localhost','irtiza','netetech321','sahamid');
$sql='SELECT * from www_users';
$result=mysqli_query($conn,$sql);

/*require( 'ssp.class.php' );
$whereAll = "salesman = '".$_SESSION['UsersRealName']."'";
if ($_SESSION['AccessLevel'] != 14)
{echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns)
);}
else
{echo json_encode(
	SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult = null, $whereAll)
);}
*/
while($row=mysqli_fetch_assoc($result))
echo json_encode($row);


