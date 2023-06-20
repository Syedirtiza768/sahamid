<?php
// Create connection
require_once 'config.php';

        if (($_FILES["file"]["type"] == "application/pdf")
        || ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
        || ($_FILES["file"]["type"] == "application/vnd.ms-excel")
        || ($_FILES["file"]["type"] == "application/msword")){

            
                //geting Name of the upoaded file
                $filename = $_FILES['file']['name'];
                $d_name = $_POST["name"];
                $d_desc =$_POST["s_desc"];
                $version=1;
                //save the uploded file to local folder
                if($_FILES["file"]["type"]== "application/pdf"){
                    $location = "upload/pdf/$filename";
                move_uploaded_file($_FILES['file']['tmp_name'], $location);
                    $sql = "INSERT INTO doc(d_name,d_desc,pdf,pdf_version) VALUES ('$d_name','$d_desc','$filename','$version')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Your file has been uploaded successfully";
                    }
                }
                elseif($_FILES["file"]["type"] == "application/vnd.ms-excel" || $_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                    $location = "upload/excel/$filename";
                move_uploaded_file($_FILES['file']['tmp_name'], $location);
                    $sql = "INSERT INTO doc(d_name,d_desc,excel,excel_version) VALUES ('$d_name','$d_desc','$filename','$version')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Your file has been uploaded successfully";
                    }
                }
                else{
                    $location = "upload/word/$filename";
                move_uploaded_file($_FILES['file']['tmp_name'], $location);
                    $sql = "INSERT INTO doc(d_name,d_desc,word,word_version) VALUES ('$d_name','$d_desc','$filename','$version')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Your file has been uploaded successfully";
                    }
                }
            }

        else
            {
            echo "Invalid file type!
            Valid files: PDF, Excel, Word";
            }
?>