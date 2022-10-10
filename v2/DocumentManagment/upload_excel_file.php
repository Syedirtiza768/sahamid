<?php
// Create connection
require_once 'config.php';
$maxFileSize = 25*1024*1024;
if($_FILES["filess"]["size"] < $maxFileSize){
if (($_FILES["filess"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
    || ($_FILES["filess"]["type"] == "application/vnd.ms-excel")) {

    //geting Name of the upoaded file
    $filename = $_FILES['filess']['name'];
    $id = $_POST['id'];
    if ($_FILES["filess"]["type"] == "application/vnd.ms-excel") {
        $location = "upload/excel/$filename";
        move_uploaded_file($_FILES['filess']['tmp_name'], $location);
        $query = "UPDATE doc SET excel='$filename' WHERE id=" . $_POST['id'] . "";
        if (mysqli_query($conn, $query)) {
            echo "Your file has been uploaded successfully";
        }
    } elseif ($_FILES["filess"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
        $location = "upload/excel/$filename";
        move_uploaded_file($_FILES['filess']['tmp_name'], $location);
        $query = "UPDATE doc SET excel='$filename' WHERE id=" . $_POST['id'] . "";
        if (mysqli_query($conn, $query)) {
            echo "Your file has been uploaded successfully";
        }
    }

    $query = "UPDATE doc SET excel_version=excel_version+1 WHERE id=" . $_POST['id'] . "";
    mysqli_query($conn, $query);
} else {
    echo "Invalid file type! You can only add excel file here...";
}
}
else
{
echo "Please select file less then 25MB...";
}
