        <?php
        include_once("../config1.php");
        $sql = "SELECT * FROM doc WHERE id=" . $_POST['id'] . "";
        $result = mysqli_query($conn, $sql);
        $row = $result->fetch_assoc();
        $pre_recd = $row['del_pdf'];
        $id = $_POST['id'];
        $name =  $row['d_name'];
        $number = $_POST['d_number'];
        $category = $row['category'];
        $revision = $_POST['d_revision'];
        $date = $_POST['d_date'];
        if (isset($_FILES['pdf_file']['name'])) {
            $pdfFileName = $_FILES['pdf_file']['name'];
        }

        if (isset($_FILES['word_file']['name'])) {
            $wordFileName = $_FILES['word_file']['name'];
        }

        if (isset($_FILES['excel_file']['name'])) {
            $excelFileName = $_FILES['excel_file']['name'];
        }

        if (isset($_FILES['ppt_file']['name'])) {
            $pptFileName = $_FILES['ppt_file']['name'];
        }


        if (isset($pdfFileName)) {
            $pdfLocation = "../upload/pdf/$pdfFileName";
            move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdfLocation);
        }

        if (isset($wordFileName)) {
            $wordLocation = "../upload/word/$wordFileName";
            move_uploaded_file($_FILES['word_file']['tmp_name'], $wordLocation);
        }

        if (isset($excelFileName)) {
            $excelLocation = "../upload/excel/$excelFileName";
            move_uploaded_file($_FILES['excel_file']['tmp_name'], $excelLocation);
        }

        if (isset($pptFileName)) {
            $pptLocation = "../upload/ppt/$pptFileName";
            move_uploaded_file($_FILES['ppt_file']['tmp_name'], $pptLocation);
        }

        //query to delete pdf data from database by id
        if (isset($pdfFileName)) {
            $pre_recd = $row['del_pdf'];
            $pre_pdf = $row['pdf'];
            $pre_letest_version = $row['pdf_number'];
            $pre_letest_date = $row['pdf_date'];

            $pre_version = $row['pdf_del_version'];
            $pre_date = $row['pdf_del_date'];
            if (!empty($pre_recd)) {
                $query = "UPDATE doc SET pdf_number='$number', pdf_date='$date', del_pdf='$pre_recd,$pre_pdf', pdf_del_version= '$pre_version,$pre_letest_version', pdf_del_date = '$pre_date,$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            } else {
                $query = "UPDATE doc SET pdf_number='$number', pdf_date='$date', del_pdf='$pre_pdf', pdf_del_version= '$pre_letest_version',
                 pdf_del_date = '$pre_letest_date' WHERE id='" . $_POST['id'] . "'";
                if (mysqli_query($conn, $sql)) {
                    echo json_encode(array("statusCode" => 200));
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            }
        }

        //query to save deleted file into database
        if (isset($wordFileName)) {
            $pre_recd = $row['del_word'];
            $pre_word = $row['word'];
            $pre_letest_version = $row['word_number'];
            $pre_letest_date = $row['word_date'];
            $pre_version = $row['word_del_version'];
            $pre_date = $row['word_del_date'];
            if (!empty($pre_recd)) {
                $query = "UPDATE doc SET word_number='$number', word_date='$date', del_word='$pre_recd,$pre_word', word_del_version= '$pre_version,$pre_letest_version', word_del_date = '$pre_date,$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            } else {
                $query = "UPDATE doc SET word_number='$number', word_date='$date', del_word='$pre_word', word_del_version= '$pre_letest_version', word_del_date = '$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            }
        }

        //query to save deleted excel file into database
        if (isset($excelFileName)) {
            $pre_recd = $row['del_excel'];
            $pre_excel = $row['excel'];
            $pre_letest_version = $row['excel_number'];
            $pre_letest_date = $row['excel_date'];
            $pre_version = $row['excel_del_version'];
            $pre_date = $row['excel_del_date'];
            if (!empty($pre_recd)) {
                $query = "UPDATE doc SET excel_number='$number', excel_date='$date', excel_del_version= '$pre_version,$pre_letest_version', excel_del_date = '$pre_date,$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            } else {
                $query = "UPDATE doc SET excel_number='$number', excel_date='$date', del_excel='$pre_excel', excel_del_version= '$pre_letest_version', excel_del_date = '$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            }
        }

        //query to save deleted ppt file into database
        if (isset($pptFileName)) {
            $pre_recd = $row['del_ppt'];
            $pre_ppt = $row['ppt'];
            $pre_letest_version = $row['ppt_number'];
            $pre_letest_date = $row['ppt_date'];
            $pre_version = $row['ppt_del_version'];
            $pre_date = $row['ppt_del_date'];
            if (!empty($pre_recd)) {
                $query = "UPDATE doc SET ppt_number='$number', ppt_date='$date', del_ppt='$pre_recd,$pre_ppt', ppt_del_version= '$pre_version,$pre_letest_version', ppt_del_date = '$pre_date,$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            } else {
                $query = "UPDATE doc SET ppt_number='$number', ppt_date='$date', del_ppt='$pre_ppt' , ppt_del_version= '$pre_letest_version', ppt_del_date = '$pre_letest_date' WHERE id=" . $_POST['id'] . "";
                mysqli_query($conn, $query);
            }
        }

        $sql = "UPDATE `doc` SET `d_name`='$name',
        `category`='$category',
        `d_revision`='$revision'";
        if (isset($pdfFileName)) {
            $sql .= " ,`pdf`='$pdfFileName'";
        }
        if (isset($wordFileName)) {
            $sql .= ",`word`='$wordFileName'";
        }
        if (isset($excelFileName)) {
            $sql .= ",`excel`='$excelFileName'";
        }
        if (isset($pptFileName)) {
            $sql .= ",`ppt`='$pptFileName'";
        }
        $sql .= " WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(array("statusCode" => 200));
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
        ?>