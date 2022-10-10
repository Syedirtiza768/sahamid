<?php 

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$text = $_POST["comment"];
	$salescaseref = $_POST['salescaseref'];
	$SQL="SELECT debtorsmaster.name from salescase INNER JOIN debtorsmaster ON
          salescase.debtorno=debtorsmaster.debtorno WHERE salescase.salescaseref='$salescaseref'";
	$name=mysqli_fetch_assoc(mysqli_query($db,$SQL))['name'];
	$text = htmlspecialchars($text);

	$hasAudio = 0;
	$audioPath = "";

	if(isset($_FILES['audio'])){
		
		$hasAudio = 1;

		$audioPath = "../audiocomments/".Date("dmYHis").".wav";
		
		move_uploaded_file($_FILES["audio"]['tmp_name'], $audioPath);

	}

	$db = createDBConnection();

	$SQL = "INSERT INTO salescasecomments (salescaseref,comment,username,hasAudio,audioPath)
			VALUES('".$salescaseref."','".$text."','".$_SESSION['UsersRealName']."',
			'".$hasAudio."','".$audioPath."')";

	mysqli_query($db, $SQL);



     $SQL="SELECT salescase.salescaseref,debtorsmaster.name, salescase_permissions.user FROM salescase_permissions 
    INNER JOIN www_users ON salescase_permissions.can_access=www_users.userid INNER JOIN 
    salescase ON www_users.realname= salescase.salesman INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
    WHERE www_users.fullaccess NOT IN (8,10,22) AND
    salescase.salescaseref='$salescaseref'";
    $result=mysqli_query($db, $SQL);
    while ($row=mysqli_fetch_assoc($result)) {
        $ip=$_SERVER["SERVER_ADDR"];
        $heading = "<a target='_blank' class='messagelink' href='http://$ip/sahamid/inbox.php?q=".$_SESSION['UsersRealName']."'>'".$_SESSION['UsersRealName']."' </a> commented on <a target='_blank' href='http://$ip/sahamid/inbox.php?q=".$name."'>".$name." </a>'s salescase <a target='_blank' href='http://$ip/sahamid/salescase/salescaseview.php?salescaseref=$salescaseref'>$salescaseref</a>
                        ";


        $SQL = "INSERT INTO inbox (heading,message,username,createdBy)
                VALUES(\"$heading\",\"$text\",\"".$row['user']."\",\"".$_SESSION['UserID']."\")";

        mysqli_query($db, $SQL);
    }
   $SQL="SELECT www_users.userid FROM www_users INNER JOIN salescase ON www_users.realname= salescase.salesman
    WHERE  salescase.salescaseref='$salescaseref'";
    $result=mysqli_query($db, $SQL);
    $row=mysqli_fetch_assoc($result);
    $SQL = "INSERT INTO inbox (heading,message,username,createdBy)
                    VALUES(\"$heading\",\"$text\",\"".$row['userid']."\",\"".$_SESSION['UserID']."\")";

    mysqli_query($db, $SQL);
    $SQL="SELECT * FROM www_users WHERE www_users.fullaccess IN (8,10,22)";
    $result=mysqli_query($db, $SQL);
    while ($row=mysqli_fetch_assoc($result)) {
       $ip=$_SERVER["SERVER_ADDR"];
        $heading = "<a target='_blank' class='messagelink' href='http://$ip/sahamid/inbox.php?q=".$_SESSION['UsersRealName']."'>'".$_SESSION['UsersRealName']."' </a> commented on <a target='_blank' href='http://$ip/sahamid/inbox.php?q=".$name."'>".$name." </a>'s salescase <a target='_blank' href='http://$ip/sahamid/salescase/salescaseview.php?salescaseref=$salescaseref'>$salescaseref</a>
                        ";

     $SQL = "INSERT INTO inbox (heading,message,username,createdBy)
                    VALUES(\"$heading\",\"$text\",\"".$row['userid']."\",\"".$_SESSION['UserID']."\")";

        mysqli_query($db, $SQL);
    }
	$response = [];
	$response['hasAudio'] = $hasAudio;
	$response['username'] = $_SESSION['UsersRealName'];
	$response['audioPath'] = $audioPath;
	$response['comment'] = $text;
	$response['createdBy']=$_SESSION['UserID'];
	$response['time'] = date('d/m/Y h:i A',time());
	echo json_encode($response);
	return;

