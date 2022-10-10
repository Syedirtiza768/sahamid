<?php
        include 'config.php';
        $user_name= $_POST['user_id'];
		$cat =$_POST['cat_id'];

        $sql = "SELECT * FROM category_perm WHERE user_id='$user_name' AND cat_id='$cat'  ";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);

        if(empty($row)){
            echo"Its saving";
        $sql = "INSERT INTO `category_perm`( `user_id`, `cat_id`) 
		VALUES ('$user_name','$cat')";

		mysqli_query($conn, $sql);
    }

    else{
        echo"Its not saving";
        $sql = " DELETE FROM category_perm WHERE user_id='$user_name' AND cat_id='$cat' ";
        mysqli_query($conn, $sql);
    }
		mysqli_close($conn);
        $_SESSION['username'] = $_POST['user_id'];
?>