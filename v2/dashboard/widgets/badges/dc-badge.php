<?php 

	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$allowed = [8,10,22];
	if(in_array($_SESSION['AccessLevel'], $allowed)){

		$SQL = "SELECT count(*) as count FROM dcs 
				WHERE orddate <= '".date("Y-m-t")."'
				AND orddate >= '".date("Y-m-01")."'";
	

	} else {
	
		$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		$canAccess = [];

		while($row = mysqli_fetch_assoc($res))
			$canAccess[] = $row['can_access'];
		
		$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '".date("Y-m-31")."'
			AND orddate >= '".date("Y-m-01")."'
			AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
				OR www_users.userid IN ('".implode("','", $canAccess)."') )";

	}

	$dcCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

?>

<div class="col-md-3 col-sm-6 col-xs-12 item" data-code="dc-badge"> 
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15;">
		<i class="fa fa-trash removeBadge"></i>
	</div>
  	<div class="info-box item-content">
    	<span class="info-box-icon bg-yellow"><i class="ion ion-ios-cart-outline"></i></span>
    	<div class="info-box-content">
      		<span class="info-box-text">Delivery Chalans</span>
      		<span class="info-box-number"><?php echo $dcCount; ?></span>
    	</div>
  	</div>
</div>