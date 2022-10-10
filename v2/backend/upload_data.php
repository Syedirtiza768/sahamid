<?php
// Create connection
include_once("../config1.php");
                //geting Name of the upoaded file
                $pdfFileName = $_FILES['pdf']['name'];
                $wordFileName = $_FILES['word']['name'];
                $excelFileName = $_FILES['excel']['name'];
                $d_name = $_POST["name"];
                $d_number = $_POST["number"];
                $d_category = $_POST["my_cat"];
                $d_date = $_POST["date"];
                $revision=$_POST["revision"];
                $version=1;
                //save the uploded file to local folder
                    $pdfLocation = "../upload/pdf/$pdfFileName";
                move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfLocation);

                $wordLocation = "../upload/word/$wordFileName";
                move_uploaded_file($_FILES['word']['tmp_name'], $wordLocation);

                $excelLocation = "../upload/excel/$excelFileName";
                move_uploaded_file($_FILES['excel']['tmp_name'], $excelLocation);

                    $sql = "INSERT INTO doc(d_name,d_number,d_revision,category,d_date,pdf,word,excel,pdf_version,word_version,excel_version)
                        VALUES
                        ('$d_name','$d_number','$revision','$d_category','$d_date','$pdfFileName','$wordFileName','$excelFileName','$version','$version','$version')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Your file has been uploaded successfully";
                    }
                    else {
                        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }
                    mysqli_close($conn);
                    
?>