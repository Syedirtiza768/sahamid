<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$rent_qty = $_POST['rent_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $rent_total= $panelCost['rent'];
  }

 $rent_budget = $rent_total*$rent_qty;
 

echo $rent_budget;


// session_start();
// date_default_timezone_set("Asia/Karachi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

// $id = $_POST['pc_id'];
// $sql ="UPDATE panel_costing SET acrylic_cost = '".$acrylic_cost."' WHERE id = '".$id."'";
// if (mysqli_query($conn, $sql)) {
//     echo $acrylic_cost;
//     }
?>