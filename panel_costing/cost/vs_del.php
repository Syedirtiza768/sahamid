<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE pannel_costing SET VS_piece_FB_sf = '0', VS_piece_FB_ws = '0', VS_piece_FB_L = '0', VS_piece_FB_w = '0',VS_piece_FB_q = '0', VS_piece_FB_ss = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Vertical Structure Piece Front & Back ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>