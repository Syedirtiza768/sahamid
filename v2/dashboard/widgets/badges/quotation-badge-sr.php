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

		$SQL = "SELECT count(*) as count FROM salesorders 
				WHERE orddate <= '".date("Y-m-t")."'
				AND orddate >= '".date("Y-m-01")."'
				AND salesorders.debtorno LIKE 'SR%'";

	} else {
	
		$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		$canAccess = [];

		while($row = mysqli_fetch_assoc($res))
			$canAccess[] = $row['can_access'];
		
		$SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '".date("Y-m-t")."'
				AND orddate >= '".date("Y-m-01")."'
				AND salescase.debtorno LIKE 'SR%'
				AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
					OR www_users.userid IN ('".implode("','", $canAccess)."') )";

	}

	$quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

?>

<div class="col-md-3 col-sm-6 col-xs-12 item" data-code="quotation-badge-sr">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15;">
		<i class="fa fa-trash removeBadge"></i>
	</div>
  	<div class="info-box item-content">
    	<span class="info-box-icon bg-aqua"><i class="ion ion-quote"></i></span>
    	<div class="info-box-content">
      		<span class="info-box-text">Quotations SR</span>
      		<span class="info-box-number"><?php echo $quotationCount; ?></span>
    	</div>
  	</div>
</div>
