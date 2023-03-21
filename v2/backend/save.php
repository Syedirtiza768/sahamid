        <?php
        include_once("../config1.php");
        $sql = "SELECT * FROM doc WHERE id=".$_POST['id']."";
        $result = mysqli_query($conn, $sql);
        $row = $result->fetch_assoc();
        $pre_recd= $row['del_pdf'];
        $id= $_POST['id'];
        $name=  $row['d_name'];
        $number=$_POST['d_number'];
        $category= $row['category'];
        $revision=$_POST['d_revision'];
        $date=$_POST['d_date'];
        $pdfFileName = $_FILES['pdf_file']['name'];
        $wordFileName = $_FILES['word_file']['name'];
        $excelFileName = $_FILES['excel_file']['name'];
        $pptFileName = $_FILES['ppt_file']['name'];

        $pdfLocation = "../upload/pdf/$pdfFileName";
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdfLocation);

        $wordLocation = "../upload/word/$wordFileName";
        move_uploaded_file( $_FILES['word_file']['tmp_name'], $wordLocation);

        $excelLocation = "../upload/excel/$excelFileName";
        move_uploaded_file($_FILES['excel_file']['tmp_name'], $excelLocation);
        
        $pptLocation = "../upload/ppt/$pptFileName";
        move_uploaded_file($_FILES['excel_file']['tmp_name'], $pptLocation);


        //query to delete pdf data from database by id
    $pre_recd= $row['del_pdf'];
    $pre_pdf= $row['pdf'];
    $pre_version = $row['del_version'];
    $pre_letest_version = $row['d_number'];
    $pre_date = $row['del_date'];
    $pre_letest_date = $row['d_date']; 
    if(!empty($pre_recd)){
        $query = "UPDATE doc SET del_pdf='$pre_recd,$pre_pdf', del_version= '$pre_version,$pre_letest_version', del_date = '$pre_date,$pre_letest_date' WHERE id=".$_POST['id']."";
        mysqli_query($conn, $query);
        }
        else{
            $query = "UPDATE doc SET del_pdf='$pre_pdf', del_version= '$pre_letest_version', del_date = '$pre_letest_date' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }


        //query to save deleted file into database
        $pre_recd= $row['del_word'];
        $pre_word= $row['word'];
        if(!empty($pre_recd)){
            $query = "UPDATE doc SET del_word='$pre_recd,$pre_word' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }
        else{
            $query = "UPDATE doc SET del_word='$pre_word' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }

        //query to save deleted excel file into database
        $pre_recd= $row['del_excel'];
        $pre_excel= $row['excel'];
        if(!empty($pre_recd)){
            $query = "UPDATE doc SET del_excel='$pre_recd,$pre_excel' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }
        else{
            $query = "UPDATE doc SET del_excel='$pre_excel' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }
        
        
        //query to save deleted ppt file into database
        $pre_recd= $row['del_ppt'];
        $pre_ppt= $row['ppt'];
        if(!empty($pre_recd)){
            $query = "UPDATE doc SET del_ppt='$pre_recd,$pre_ppt' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }
        else{
            $query = "UPDATE doc SET del_ppt='$pre_ppt' WHERE id=".$_POST['id']."";
            mysqli_query($conn, $query);
        }


		$sql = "UPDATE `doc` SET `d_name`='$name',
        `d_number`='$number',
        `category`='$category',
        `d_date`='$date',
        `d_revision`='$revision',
        `pdf`='$pdfFileName',
        `word`='$wordFileName',
        `excel`='$excelFileName' 
        `ppt`='$pptFileName' WHERE id=$id"; 
		if (mysqli_query($conn, $sql)) {
			echo json_encode(array("statusCode"=>200));
		} 
		else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
		mysqli_close($conn);
        ?>