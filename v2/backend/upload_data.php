<?php
// Create connection
include_once("../config1.php");
                //geting Name of the upoaded file
                $pdfFileName = null;
                $wordFileName = null;
                $excelFileName = null;
                $pptFileName = null;
                $d_name = $_POST["name"];
                $d_number = $_POST["number"];
                $d_category = $_POST["my_cat"];
                $d_date = $_POST["date"];
                $revision=$_POST["revision"];
                $version=1;

                if($_FILES['pdf']['name']){
                    $pdfFileName = $_FILES['pdf']['name'];
                    $pdfLocation = "../upload/pdf/$pdfFileName";
                move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfLocation);
                }

                if($_FILES['word']['name']){
                    $wordFileName = $_FILES['word']['name'];
                    $wordLocation = "../upload/word/$wordFileName";
                    move_uploaded_file($_FILES['word']['tmp_name'], $wordLocation);
                }

                if($_FILES['excel']['name']){
                    $excelFileName = $_FILES['excel']['name'];
                    $excelLocation = "../upload/excel/$excelFileName";
                    move_uploaded_file($_FILES['excel']['tmp_name'], $excelLocation);
                }
                
                if($_FILES['ppt']['name']){
                    $pptFileName = $_FILES['ppt']['name'];
                    $pptLocation = "../upload/ppt/$pptFileName";
                    move_uploaded_file($_FILES['ppt']['tmp_name'], $pptLocation);
                }

                    $sql = "INSERT INTO doc(d_name,pdf_number,word_number,excel_number,ppt_number,d_revision,category,pdf_date,word_date,excel_date,ppt_date,pdf,word,excel,ppt,pdf_version,word_version,excel_version,ppt_version)
                        VALUES
                        ('$d_name','$d_number','$d_number','$d_number','$d_number','$revision','$d_category','$d_date','$d_date','$d_date','$d_date','$pdfFileName','$wordFileName','$excelFileName','$pptFileName','$version','$version','$version','$version')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Your file has been uploaded successfully";
                    }
                    else {
                        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }
                    mysqli_close($conn);
                    
?>