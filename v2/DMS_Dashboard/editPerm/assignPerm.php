<?php
include 'config.php';

$user_id = $_POST['user_id'];
$editPerm = $_POST['id'];
$editPerm="";


$query = "SELECT permission FROM editPerm WHERE userid='$user_id'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$editPerm= $row['permission'];


if($editPerm==""){
    $sql = "INSERT INTO editPerm (userid, permission) 
		VALUES ('$user_id','1')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(array("statusCode"=>200));
} 
else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
}

elseif($editPerm==0){
    $sql = "UPDATE `editPerm` SET `permission`='1' WHERE userid='$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(array("statusCode"=>200));
    } 
    else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
else{
    $sql = "UPDATE `editPerm` SET `permission`='0' WHERE userid='$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(array("statusCode"=>200));
    } 
    else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

?>