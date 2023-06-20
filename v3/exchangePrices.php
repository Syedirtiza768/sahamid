<?php

	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, 'view_exchange_rate')){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['updatePrices']) && userHasPermission($db, 'update_exchange_rate')){

		$AED = $_POST['aed'];
		$USD = $_POST['usd'];
		$EURO = $_POST['euro'];
		$POUND = $_POST['pound'];

		$SQL = "SELECT max(id) as id FROM exchange_rate";
		$res = mysqli_query($db, $SQL);
		$id = mysqli_fetch_assoc($res)['id'];

		$SQL = "UPDATE exchange_rate
				SET aed=$AED,
					usd=$USD,
					euro=$EURO,
					pound=$POUND
				WHERE id=$id";
		mysqli_query($db, $SQL);
			
		header("Location: exchangePrices.php");
		return;
	}

	$SQL = "SELECT max(id) as id FROM exchange_rate";
	$res = mysqli_query($db, $SQL);
	$id = mysqli_fetch_assoc($res)['id'];

	if(!$id){
		
		$SQL = "INSERT INTO exchange_rate (aed,usd,euro,pound)
				VALUES(0,0,0,0)";
		$res = mysqli_query($db, $SQL);

		$SQL = "SELECT max(id) as id FROM exchange_rate";
		$res = mysqli_query($db, $SQL);
		$id = mysqli_fetch_assoc($res)['id'];
		
	}

	$SQL = "SELECT * FROM exchange_rate WHERE id=$id";
	$res = mysqli_query($db, $SQL);
	$rates = mysqli_fetch_assoc($res);

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
	<div class="content-wrapper">
		<h1 class="text-center">Exchange Rates</h1>
		<section class="content" style="display: flex; align-items: center; justify-content: center; margin-top: 15%;">
			<div class="col-xs-4">
	            <form action="exchangePrices.php" method="post">
	            	<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	            	<input type="hidden" name="updatePrices" value="true">
	            	<div class="input-group">
		                <span class="input-group-addon">AED</span>
		                <input type="number" 
		                		name="aed" 
		                		class="form-control" 
		                		placeholder="AED Rate" 
		                		value="<?php echo $rates['aed']; ?>" required>
		            </div><br>
		            <div class="input-group">
		                <span class="input-group-addon">USD</span>
		                <input type="number" 
		                		name="usd" 
		                		class="form-control" 
		                		placeholder="USD Rate" 
		                		value="<?php echo $rates['usd']; ?>" required>
		            </div><br>
		            <div class="input-group">
		                <span class="input-group-addon">EURO</span>
		                <input type="number" 
		                		name="euro" 
		                		class="form-control" 
		                		placeholder="EURO Rate" 
		                		value="<?php echo $rates['euro']; ?>" required>
		            </div><br>
		            <div class="input-group">
		                <span class="input-group-addon">Pound</span>
		                <input type="number" 
		                		name="pound" 
		                		class="form-control" 
		                		placeholder="Pound Rate" 
		                		value="<?php echo $rates['pound']; ?>" required>
		            </div>
		            <div class="input-group">
		            	<br>
		                <button class="btn btn-success col-xs-12" type="submit">Submit</button>
		            </div>
	            </form>
	        </div>
		</section>
	</div>

<?php include_once("includes/footer.php"); ?>