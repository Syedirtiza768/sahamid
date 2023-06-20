<?php
//including db.php file for database connection
include 'config.php';

    //query to delete data from database by id that we send from index.php via ajax.js
    $sql = "SELECT del_excel FROM doc WHERE id=".$_POST['id']."";
    $result = mysqli_query($conn, $sql);
    $row = $result->fetch_assoc();
    $pre_recd= $row['del_excel'];
    if(!empty($pre_recd)){
    $query = "UPDATE doc SET excel='' WHERE id=".$_POST['id']."";
    //execute query
    mysqli_query($conn, $query);
    $filename = $_POST['excel'];
    $query = "UPDATE doc SET del_excel='$pre_recd,$filename' WHERE id=".$_POST['id']."";
    if (mysqli_query($conn, $query)) {
        echo "Your file is deleted and becomes red now";
    } else {
        echo "Unable to Delete Data" . $conn->error;
    }
}
else{
    $query = "UPDATE doc SET excel='' WHERE id=".$_POST['id']."";
    //execute query
    mysqli_query($conn, $query);
    $filename = $_POST['excel'];
    $query = "UPDATE doc SET del_excel='$filename' WHERE id=".$_POST['id']."";
    if (mysqli_query($conn, $query)) {
        echo "Your file is deleted and becomes red now";
    } else {
        echo "Unable to Delete Data" . $conn->error;
    }
}
?>