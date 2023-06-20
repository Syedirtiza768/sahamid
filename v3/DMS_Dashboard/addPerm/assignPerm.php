<?php
include 'config.php';
$user_id = $_POST['user_id'];
$id = $_POST['id'];
$addPerm="";
$query = "SELECT permission FROM addPerm WHERE userid='$user_id'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$addPerm= $row['permission'];
if($addPerm==""){
    $sql = "INSERT INTO addPerm (userid, permission) 
		VALUES ('$user_id','1')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(array("statusCode"=>200));
} 
else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
}

elseif($addPerm==0){
    $sql = "UPDATE `addPerm` SET `permission`='1' WHERE userid='$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(array("statusCode"=>200));
    } 
    else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
else{
    $sql = "UPDATE `addPerm` SET `permission`='0' WHERE userid='$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(array("statusCode"=>200));
    } 
    else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

?>