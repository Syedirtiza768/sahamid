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
				AND orddate >= '".date("Y-m-01")."'";

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
				AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
					OR www_users.userid IN ('".implode("','", $canAccess)."') )";

	}

	$quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

?>
<?php

$SQL = "SELECT * FROM user_permission WHERE userid='" . $_SESSION['UserID'] . "' AND permission='*' ";
$ressData = mysqli_query($db, $SQL);
while ($rowData = mysqli_fetch_assoc($ressData)) {
	$permission = $rowData['permission'];
}
?>

<div class="col-md-4 col-sm-6 col-xs-12 item" style="height:140px; overflow:auto; margin-top:8px;" data-code="quotation-badge">
	<div style="position: relative; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%;">
		<?php
		if ($permission == "*") {
		?>
			<select class="js-example-basic-multiple dataUASR" name="states[]" multiple="multiple" style="width:90%;">
				<?php
				$SQL = "SELECT * FROM salesman ";
				$result = mysqli_query($db, $SQL);
				while ($row_salesman = mysqli_fetch_assoc($result)) {
				?>
					<option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
				<?php }
				?>
			</select>
		<?php } else {
			$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "' ";
			$resss = mysqli_query($db, $SQL); ?>
			<select class="js-example-basic-multiple dataUASR" name="states[]" multiple="multiple" style="width:90%;">
				<?php while ($row = mysqli_fetch_assoc($resss)) {

					$SQL = "SELECT realname FROM www_users WHERE userid='" . $row['can_access'] . "' ";
					$result = mysqli_query($db, $SQL);
					while ($row_data = mysqli_fetch_assoc($result)) {
				?>
						<option value="<?php echo $row_data['realname']; ?>"><?php echo $row_data['realname']; ?></option>
				<?php }
				} ?>
			</select>
		<?php } ?>
		<span class="store-data" onclick=""><i style="color:red;" class="fa fa-search" aria-hidden="true"></i></span>
		<i class="fa fa-trash removeBadge"></i>
	</div>
  	<div class="info-box item-content">
    	<span class="info-box-icon bg-aqua"><i class="ion ion-quote"></i></span>
    	<div class="info-box-content">
      		<span class="info-box-text">Quotations</span>
      		<span class="info-box-number"><?php echo $quotationCount; ?></span>
    	</div>
  	</div>
</div>
