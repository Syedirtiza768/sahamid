<?php
require_once './config.php';
$h= $_POST['height'];
$w = $_POST['width'];
$q = $_POST['qty'];
$s=$_POST['sheet'];
$id=$_POST['pannel_costing_id'];
$sf=  $h/25.4/12*$w/25.4/12*$q;
if($s==12){
    $WBS = 62/32*$sf;
}
elseif($s==14){
    $WBS = 48.64/32*$sf;
}
elseif($s==16){
    $WBS = 38.4/32*$sf;
}
elseif($s==18){
    $WBS = 28.4/32*$sf;
}
elseif($s==20){
    $WBS = 23/32*$sf;
}
else{
    $WBS = 0;
}

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Vertical Structure Piece Front & Back ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE pannel_costing SET VS_piece_FB_sf = '".$sf."', VS_piece_FB_ws = '".$WBS."', VS_piece_FB_L = '".$h."', VS_piece_FB_w = '".$w."',VS_piece_FB_q = '".$q."', VS_piece_FB_ss = '".$s."'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(array("square_feet"=>$sf, "WBS"=> $WBS));die;
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>