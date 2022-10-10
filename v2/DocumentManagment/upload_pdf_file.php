<?php
// Create connection
require_once 'config.php';
$maxFileSize = 25*1024*1024;
if($_FILES["filess"]["size"] < $maxFileSize){
        if (($_FILES["filess"]["type"] == "application/pdf")){

            
                //geting Name of the upoaded file
                $filename = $_FILES['filess']['name'];
                $id=$_POST['id'];
                //save the uploded file to local folder
                    $location = "upload/pdf/$filename";
                move_uploaded_file($_FILES['filess']['tmp_name'], $location);
                $query = "UPDATE doc SET pdf='$filename' WHERE id=".$_POST['id']."";
                    if (mysqli_query($conn, $query)) {
                        echo "Your file has been uploaded successfully";
                    }
                    
    $query = "UPDATE doc SET pdf_version=pdf_version+1 WHERE id=".$_POST['id']."";
    mysqli_query($conn, $query);
                }

        else
            {
            echo "Invalid file type! You can only add pdf file here...";
            }
        }
        else
        {
        echo "Please select file less then 25MB...";
        }  
?>