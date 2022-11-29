<?php
session_start();
require_once './config.php';
$salescaseref =$_SESSION['salescaseref'];
$h= $_POST['pc_h'];

// $sql=" SELECT pc_id FROM panel_costing ORDER by id DESC LIMIT 1";
// $PannelcostNo = mysqli_query($conn, $sql);
// $costNoValue = mysqli_fetch_assoc($PannelcostNo);
// $costNo = $costNoValue['pc_id'];
// $costNo = $costNo+1;




$sql = "INSERT INTO panel_costing(salescaseref ,pc_h)VALUES('$salescaseref','$h')";
if (mysqli_query($conn, $sql)) {

  $id = mysqli_insert_id($conn);
  $sql = "INSERT INTO pc_cash_demand(pc_id)VALUES('$id')";
  mysqli_query($conn, $sql);

  

  date_default_timezone_set("Asia/Karachi");
  $date = date('d-m-y h:i:s');
  $sql = "INSERT INTO panelcostingmodifications(panel_id,updateDate,pc_description)VALUES('$id','$date','New panel costing created by ".$_SESSION['UsersRealName']." at')";
  mysqli_query($conn, $sql);


$_SESSION["name"] = $id;
    echo $id;
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

?>