<?php
require '../vendor/autoload.php';
use Carbon\Carbon;
$AllowAnyone=true;
$PathPrefix='../';


include('../includes/session.inc');
include('../includes/SQL_CommonFunctions.inc');


if(isset($_POST['view'])){

// $con = mysqli_connect("localhost", "root", "", "notif");

if($_POST["view"] != '')
{
   $update_query = "UPDATE inbox SET messageStatus = 1 WHERE messageStatus=0 AND username='".$_SESSION['UserID']."'";
    mysqli_query($db, $update_query);
}
$query = "SELECT * FROM inbox WHERE inbox.username= '".$_SESSION['UserID']."' ORDER BY id DESC";
$result = mysqli_query($db, $query);

$output = '<span class="pull-right glyphicon glyphicon-cog clickable-space" style="font-size:18px;display: block;"></span><hr/>';
if(mysqli_num_rows($result) > 0)
{
 while($row = mysqli_fetch_array($result))
 {
     $SQL = "SELECT pic FROM www_users WHERE www_users.userid= '".$row['createdBy']."'";

     $pic=mysqli_fetch_assoc(mysqli_query($db,$SQL))['pic'];
   /* $diff=Carbon::now()->subDays(5)->diffForHumans();*/


     $end_date = Carbon::parse(strtotime($row['createdAt']));

     $diff = $end_date->diffForHumans();
   /*  $diff=Carbon::now()->strtotime($row['createdAt'])->diffForHumans();*/
   $output .= $diff.'
   <li style="word-wrap:break-word">';
     $output .='<br/><img class="img-circle" src="data:image/jpeg;base64,'.base64_encode( $pic ).'" height="50px" width="50px"/>';

   $output .= '<strong>'.$row["heading"].'</strong>"
   <small><em>'.$row["message"].'</em></small>"
   <hr>
   </li>
   ';

 }
 $output.='<center><h4><a href="/sahamid/v2/inbox.php" target="_blank">Inbox</a></h4></center>';
}
else{
     $output .= '
     <li><a href="#" class="text-bold text-italic">No Notification Found</a></li>';
}



 $status_query = "SELECT * FROM inbox INNER JOIN www_users ON inbox.username=www_users.userid 
WHERE inbox.username= '".$_SESSION['UserID']."'  AND inbox.messageStatus=0 ";
$result_query = mysqli_query($db, $status_query);
$count = mysqli_num_rows($result_query);
$data = array(
    'notification' => $output,
    'unseen_notification'  => $count
);

echo json_encode($data);

}

?>