<?php
	
	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchi']);
	$svid = trim($_POST['svid']);

	$SQL = "SELECT * FROM bazar_parchi 
			WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0 AND svid=''";
	$res = mysqli_query($db, $SQL);

/*	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Parchi Not Found or already saved.',

			]);
		return;


	}*/

	$SQL = "UPDATE bazar_parchi SET svid='".$svid."'  
			WHERE parchino='".$parchi."'";
    mysqli_query($db,$SQL);
	$SQL = "SELECT * FROM igp WHERE igp.narrative = 'Against ParchiNo: $parchi'";
	$res=mysqli_query($db,$SQL);
	$row=mysqli_fetch_assoc($res);

    $SQL = "SELECT * FROM stockmoves WHERE stockmoves.type = 510 AND stockmoves.transno = ".$row['dispatchid'];
    $res=mysqli_query($db,$SQL);


    while ($row=mysqli_fetch_assoc($res)){
echo        $SQL="UPDATE stockmoves SET reference= 'From ".DB_escape_string($svid)."' WHERE stkmoveno = ".$row['stkmoveno'];
        mysqli_query($db,$SQL);
    }

    $SQL="UPDATE supptrans SET supplierno = '$svid' WHERE (supptrans.type=601 OR supptrans.type=14) AND
          
          supptrans.transno= ".str_replace("MPIW-", "", $parchi);
    mysqli_query($db,$SQL);


    echo json_encode([
        'status' => 'success'
    ]);
    return;





