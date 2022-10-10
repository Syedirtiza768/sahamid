<?php
session_start();
require_once './config.php';
$salescaseref =$_SESSION['salescaseref'];
$h= $_POST['height'];
$w = $_POST['width'];
$d = $_POST['depth'];

$sql=" SELECT costNo FROM pannel_costing ORDER by id DESC LIMIT 1";
$PannelcostNo = mysqli_query($conn, $sql);
$costNoValue = mysqli_fetch_assoc($PannelcostNo);
$costNo = $costNoValue['costNo'];
$costNo = $costNo+1;


$sql = "INSERT INTO pannel_costing(salescaseref,costNo ,pannel_size_h,pannel_size_w,pannel_size_d)VALUES('$salescaseref','$costNo','$h','$w','$d')";
if (mysqli_query($conn, $sql)) {

  $id = mysqli_insert_id($conn);

  $sql = "INSERT INTO bus_bar_sheet(pannel_id,costNo)VALUES('$id','$costNo')";
  mysqli_query($conn, $sql);

  date_default_timezone_set("Asia/Karachi");
  $date = date('d-m-y h:i:s');
  $sql = "INSERT INTO panelcostingmodifications(panel_id,costNo,updateDate,pc_description)VALUES('$id','$costNo','$date','New panel costing created by ".$_SESSION['UsersRealName']." at')";
 
  mysqli_query($conn, $sql);

  $sql ="INSERT INTO cost_sheet(pannel_id,costNo)VALUES('$id','$costNo')";
if (mysqli_query($conn, $sql)) {
}


$_SESSION["name"] = $id;
    echo $id;
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

?>