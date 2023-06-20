<?php
// Create connection
require_once 'config.php';
$maxFileSize = 25 * 1024 * 1024;
if ($_FILES["filess"]["size"] < $maxFileSize) {
    if (($_FILES["filess"]["type"] == "application/msword")) {

        //geting Name of the upoaded file
        $filename = $_FILES['filess']['name'];
        //save the uploded file to local folder
        $location = "upload/word/$filename";
        move_uploaded_file($_FILES['filess']['tmp_name'], $location);
        $query = "UPDATE doc SET word='$filename' WHERE id=" . $_POST['id'] . "";
        if (mysqli_query($conn, $query)) {
            echo "Your file has been uploaded successfully";
        }
        $query = "UPDATE doc SET word_version=word_version+1 WHERE id=" . $_POST['id'] . "";
        mysqli_query($conn, $query);
    } else {
        echo "Invalid file type! You can only add word file here...";
    }
} else {
    echo "Please select file less then 25MB...";
}
