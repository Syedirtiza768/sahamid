<?php

$PathPrefix = '../';
include('misc.php');
include('../includes/session.inc');
include('../includes/CountriesArray.php');

$salescase['salescaseref'] = $_GET['salescaseref'];

$_SESSION['salescaseref'] = $salescase['salescaseref'];

if (!isset($salescase['salescaseref'])) {

	echo "<script>";
	echo "window.location = '" . $RootPath . "/../salescase.php'";
	echo "</script>";

	return;
}

$db = createDBConnection();

$SQL 	= "SELECT * FROM salescase WHERE salescaseref='" . $salescase['salescaseref'] . "'";
$result = mysqli_query($db, $SQL);

if (!$result || mysqli_num_rows($result) == 0) {

	echo "<script>";
	echo "window.location = '" . $RootPath . "/../salescase.php';";
	echo "</script>";

	return;
}

$salescase = mysqli_fetch_assoc($result);

if (!($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10)) {

	$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
	$res = mysqli_query($db, $SQL);
	$canAccess = [];
	while ($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['can_access'];

	$SQL = 'SELECT * FROM salescase 
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salescase.salescaseref = "' . $_GET['salescaseref'] . '"
				AND ( salescase.salesman ="' . $_SESSION['UsersRealName'] . '"
				OR www_users.userid IN ("' . implode('","', $canAccess) . '") )';
	$res = mysqli_query($db, $SQL);

	if (!$res || mysqli_num_rows($res) == 0) {

		echo "<script>";
		echo "window.location = '" . $RootPath . "/../salescase.php';";
		echo "</script>";

		return;
	}
}

$SQL = "SELECT * FROM debtorsmaster WHERE debtorno='" . $salescase['debtorno'] . "'";
$result = mysqli_query($db, $SQL);

$clientInfo = mysqli_fetch_assoc($result);

$SQL 	= "SELECT * FROM salescasecomments WHERE salescaseref='" . $salescase['salescaseref'] . "'";
$comments = mysqli_query($db, $SQL);

$SQL = "SELECT * FROM custcontacts 
			INNER JOIN salescasecontacts 
			ON custcontacts.contid = salescasecontacts.contid
			WHERE salescasecontacts.salescaseref='" . $salescase['salescaseref'] . "'";
$contacts = mysqli_query($db, $SQL);

$SQL = 'SELECT count(*) as count FROM salesordersip WHERE salescaseref="' . $_GET['salescaseref'] . '"';
$result = mysqli_query($db, $SQL);
$quotationInProgress = mysqli_fetch_assoc($result)['count'];

$SQL = 'SELECT count(*) as count FROM panel_costing WHERE salescaseref="' . $_GET['salescaseref'] . '" AND closed="0" ';
$result = mysqli_query($db, $SQL);
$PanelInProgress = mysqli_fetch_assoc($result)['count'];

//Get Recent Comments
if (in_array($_SESSION['AccessLevel'], [8, 11, 27, 22, 23, 10])) {

	$SQL = "SELECT salescasecomments.salescaseref, salescasecomments.comment, salescasecomments.time,salescasecomments.username FROM salescase 
				INNER JOIN salescasecomments ON salescasecomments.salescaseref=salescase.salescaseref
				WHERE salescasecomments.commentcode 
					IN (SELECT max(commentcode) FROM salescasecomments GROUP BY salescaseref)
				ORDER BY `salescasecomments`.`time` DESC LIMIT 200";
	$recentComments = mysqli_query($db, $SQL);
} else {

	$SQL = "SELECT salescasecomments.salescaseref, salescasecomments.comment, salescasecomments.time,salescasecomments.username FROM salescase 
				INNER JOIN salescasecomments ON salescasecomments.salescaseref=salescase.salescaseref
				WHERE salescasecomments.commentcode 
					IN (SELECT max(commentcode) FROM salescasecomments GROUP BY salescaseref)
				AND salescase.salesman='" . $_SESSION['UsersRealName'] . "'
				ORDER BY `salescasecomments`.`time` DESC LIMIT 200";
	$recentComments = mysqli_query($db, $SQL);
}

$SQL = "SELECT * FROM salescase_watchlist 
			WHERE userid='" . $_SESSION['UserID'] . "'
			AND salescaseref='" . $salescase['salescaseref'] . "'
			AND deleted=0";
$isInWatchList = (mysqli_num_rows(mysqli_query($db, $SQL)) > 0);

$canMakeDuplicate = userHasPermission($db, 'make_duplicate_quotation');
$canCreateRevision = userHasPermission($db, 'create_quotation_revision');

?>

<!DOCTYPE html>
<html class="">

<head>
	<meta charset="UTF-8">

	<title>S A Hamid ERP</title>
	<meta name="keywords" content="HTML5 Admin Template" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
	<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

	<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style>
		.displaynone {
			display: none;
		}

		.bubble {
			padding: 6px 0 8px 0;
			padding-left: 9px;
			padding-right: 7px;
			min-width: 110px;
			width: calc(100% - 20px);
			box-sizing: border-box;
			background-color: white;
			display: inline-block;
			margin: 10px;
			border: 1px grey solid;
			border-radius: 6px;
			white-space: pre-wrap;
			/* CSS3 */
			white-space: -moz-pre-wrap;
			/* Firefox */
			white-space: -pre-wrap;
			/* Opera <7 */
			white-space: -o-pre-wrap;
			/* Opera 7 */
			word-wrap: break-word;
			/* IE */
		}

		.author {
			color: #424242;
			padding: 3px;
		}

		.message {
			color: black;
			padding: 3px;
		}

		.time {
			float: right;
			padding: 3px;
		}

		.toggleviewnone {
			display: none;
		}

		.sidebar {
			position: fixed;
			height: calc(100vh - 58px);
			right: 0;
			width: 350px;
			background-image: url('assets/images/background.png');
			background-size: contain;
			border-left: 1px black solid;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}

		.sidebar>h2 {
			background: whitesmoke;
			margin: 0;
			padding: 10px;
			text-align: center;
			border-bottom: 1px black solid;
		}

		.sidebar>.commentarea {
			margin: 0;
			height: calc(100vh - 3rem - 1em - 30px);
			overflow: auto;
			overflow-x: hidden;
			padding: 10px;
		}

		.maincontentarea {
			position: fixed;
			height: calc(100vh - 58px);
			width: calc(100vw - 350px);
			background-image: url('assets/images/cardboard.png');
			background-color: white;
			background-size: contain;
			overflow: auto;
			overflow-x: hidden;
		}

		.maincontentarea>h2 {
			background: whitesmoke;
			margin: 0;
			padding: 10px;
			text-align: center;
			border-bottom: 1px black solid;
		}

		.maincontentarea>.message {
			background: whitesmoke;
			margin: 0;
			padding: 5px;
			text-align: center;
			border-bottom: 1px black solid;
		}

		.maincontentarea>.tab {
			margin: 0;
			padding: 25px;
		}

		.maincontentarea>.tab h3 {
			font-weight: bold;
		}

		td,
		th {
			padding: 5px;
		}

		textarea {
			border: 2px grey solid;
			background: #e2f5ff;
		}
	</style>

	<style>
		.parent-comment-area {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100vh;
			background-color: rgba(0, 0, 0, 0.5);
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.child-comment-area {
			position: relative;
			background-color: rgba(255, 255, 255, 0.9);
			display: flex;
			padding: 20px;
			flex-direction: column;
		}

		.comment-text {
			width: 560px;
			min-width: 560px;
			max-width: 560px;
			height: 80px;
			min-height: 80px;
			max-height: 80px;
		}

		.button {
			margin: 5px 0;
			width: 100%;
			font-size: 2rem
		}
	</style>
	<script>
		var audio_context;
		var recorder;
		var parentStream;
		var blobtosend;
	</script>

</head>

<body style="overflow: hidden;">
	<section class="body" style="overflow: auto">

		<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
			<span style="color:white">
				<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?>
				&nbsp;|&nbsp;
				<span style="color:#ccc">
					<?php echo stripslashes($_SESSION['UsersRealName']); ?>
				</span>
				<span class="pull-right" style="background:#424242; padding: 0 10px;">
					<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
					<a class="bold" href="<?php echo $RootPath; ?>/../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
				</span>
			</span>
		</header>

		<div class="maincontentarea">
			<h2><?php echo ($clientInfo['name']) . " (" . $salescase['salescaseref'] . ")"; ?>
				<span style="position: absolute; left: 0; cursor: pointer;" class="commentssection">++</span>
			</h2>
			<?php

			if ($salescase['closed'] == 0 && $salescase['priority'] != "medium") {
				echo '<p class="';
				echo (($salescase['priority'] == "high") ? 'btn-danger' : 'btn-info');
				echo '" style="text-align: center; margin-bottom:0px; padding:10px">';
				echo 'Priority: ' . $salescase['priority'];
				echo '<br/>';
				echo 'Updated By: ' . $salescase['priority_updated_by'];
				echo '</p>';
			}

			if ($salescase['closed'] == 0 && $salescase['priority'] == "medium" && $salescase['priority_added'] == "1") {
				echo '<p class="btn-warning" style="text-align: center; margin-bottcontinuewom:0px; padding:10px">';
				echo 'Priority: ' . $salescase['priority'];
				echo '<br/>';
				echo 'Updated By: ' . $salescase['priority_updated_by'];
				echo '</p>';
			}

			if ($salescase['closed'] == 1) {

				echo '<p class="btn-danger" style="text-align: center;">';
				echo 'Case closed at ' . $salescase['stage'] . ' stage<br>';
				echo 'On : ' . date('d/m/Y h:i A', strtotime($salescase['closingdate'])) . '<br>';
				echo 'Reason: ' . $salescase['closingreason'] . '<br>';
				echo 'Remarks: ' . $salescase['closingremarks'] . '<br>';
				echo '</p>';
			}

			?>
			<input type="hidden" name="rootpath" id="rootpath" value="<?php echo $RootPath; ?>">
			<input type="hidden" name="salesref" id="salesref" value="<?php echo $salescase['salescaseref']; ?>">
			<div class="btn-group btn-group-justified">
				<a href="#detailsbutton" class="btn btn-default buttons active" role="button" id="detailsbutton">Details</a>
				<a href="#rppbutton" class="btn btn-default buttons" role="button" id="rppbutton">Files</a>
				<a href="#saleslogbutton" class="btn btn-default buttons" role="button" id="saleslogbutton">Sales log</a>
			</div>
			<div class="btn-group btn-group-justified">
				<a href="#quotationbutton" class="btn btn-default buttons" role="button" id="quotationbutton">Quotation <span class="label btn-success" id="quotCount"></span></a>
				<a href="#ocbutton" class="btn btn-default buttons" role="button" id="ocbutton">OC <span id="ocCount" class="label btn-success"></span></a>
				<a href="#dcbutton" class="btn btn-default buttons" role="button" id="dcbutton">DC <span id="dcCount" class="label btn-success"></span></a>
				<?php
                if(userHasPermission($db, 'panel_costing')) {
                    echo '<a href="#pcbutton" class="btn btn-default buttons" role="button" id="pcbutton">Panel Costing <span id="pcCount" class="label btn-success"></span></a>';
                }
				$stockrequest = $salescase['salescaseref'];
                ?>
				<!-- <a href="#ogpbutton" class="btn btn-default buttons" role="button" id="ogpbutton">OGP <span id="ogpCount" class="label btn-success"></span></a> -->
				<a class="btn btn-default buttons" onclick="stockrequest('<?php echo $stockrequest; ?>')" target = "_blank">Create Stock Request<span id="ogpCount" class="label btn-success"></span></a>

			</div>
			<div id="details" class="tab">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6">
							<?php
							if (in_array($_SESSION['AccessLevel'], [8, 10]) && $salescase['closed'] == 0) {
							?>
								<h3>Priority</h3>
								<h5>
									<select id="salescase_priority">
										<option value="high">High</option>
										<option value="medium">Medium</option>
										<option value="low">Low</option>
									</select>
								</h5>
							<?php }
							if ($salescase['closed'] == 0) { ?>
								<h3>Review On</h3>
								<h5>
									<input type="date" id="salescase_review">
								</h5>
							<?php } ?>
						</div>
						<div class="col-md-6" style="text-align: right;">
							<?php if ($isInWatchList) { ?>
								<a class="btn btn-danger" href="api/removeSalescaseFromWatchlist.php?salescaseref=<?php echo $salescase['salescaseref']; ?>">
									Remove From Watchlist</a>
							<?php } else { ?>
								<a class="btn btn-success" href="api/addSalescaseToWatchlist.php?salescaseref=<?php echo $salescase['salescaseref']; ?>">Add To Watchlist</a>
							<?php } ?>
						</div>
					</div>
				</div>
				<h3>DBA</h3>
				<h5><strong style="font-size:2rem">-- <?php echo $clientInfo['dba']; ?></strong></h5>
				<h3>Salesman</h3>
				<h5><strong>-- </strong><?php echo $salescase['salesman']; ?></h5>
				<h3>Commencement Date</h3>
				<h5><strong>-- </strong>
					<?php echo date('d/m/Y h:i', strtotime($salescase['commencementdate'])); ?>
				</h5>
				<h3>Enquiry Value</h3>
				<h5><input name="enquiryvalue" class="salescaseval" type="number" style="height: 30px; border: 2px grey solid; background: #e2f5ff" value="<?php echo $salescase['enquiryvalue']; ?>" <?php if ($salescase['closed'] == 1) echo "disabled"; ?>></input></h5>
				<?php


				$enquiryFiles = glob('../' . $_SESSION['part_pics_dir'] . '/enquiryfile_' . $salescase['salescaseref'] . "*");

				if (count($enquiryFiles) == 0) {

					echo '<h3>Enquiry File (PDF only)</h3>';
					echo '<h5>';
					echo '<form action="' . $RootPath . '/api/uploadEnquiry.php" enctype="multipart/form-data" method="post">';
					echo '<input type="file" style="height: 30px; border: 2px grey solid; background: #e2f5ff" name="enquiryfile" required=""';
					if ($salescase['closed'] == 1)
						echo "disabled";
					echo '></input></h5>';
					echo '<input type="hidden" value="' . $salescase['salescaseref'] . '" name="salescaseref">';
					echo '<input type="submit" class="btn btn-success" value="Upload"';
					if ($salescase['closed'] == 1)
						echo ' style="display:none"';
					echo '></input></form>';
				}

				foreach ($enquiryFiles as $enquiryFile) {

					$enquiryFilePath = explode("../", $enquiryFile)[1];
					echo '<br /><a id="viewenquiry" class="btn btn-warning" href = "' . $RootPath . '/' . $enquiryFile . '" target = "_blank" >' . $enquiryFile . '</a>';
				}

				?>
				<h3>Salescase Description</h3>
				<div style=" padding-left:0px;">
					<textarea name="salescasedescription" class="salescaseval" style="max-height: 100px; height: 100px; width: 100%;" <?php if ($salescase['closed'] == 1) echo "disabled"; ?>><?php echo $salescase['salescasedescription'] ?></textarea>
				</div><br>
				<h3 style="margin-top: 0px">Involved Contact Persons</h3>
				<div style="border: 2px grey solid; padding: 10px; margin-bottom: 10px;">
					<?php
					while ($row = mysqli_fetch_assoc($contacts)) {
						echo '<h5 style="background: #f5f5f5; padding: 5px;';
						echo 'border-bottom: 1px white solid; margin:1px;">';
						echo '<strong>-- </strong>';
						echo $row['contactname'] . ' (' . $row['role'] . ')  (' . $row['phoneno'] . ')';
						echo '</h5>';
					}
					?>
				</div>
				<div>
					<?php if ($salescase['closed'] == 0) { ?>
						<h4 id="updatecontact" style="padding: 10px; text-align: center; margin-bottom: 0px;" class="btn-primary">Update Contact Persons</h4>
						<div id="changecontact" class="displaynone" style="width: 100%; border:2px #424242 solid; border-top:0px; padding: 20px">
							<?php

							$SQL = "SELECT * FROM custcontacts 
							            INNER JOIN debtorsmaster 
							            ON custcontacts.debtorno = debtorsmaster.debtorno 
							            WHERE debtorsmaster.debtorno = '" . $salescase['debtorno'] . "'";

							$result = mysqli_query($db, $SQL);

							$contacts = [];

							while ($row = mysqli_fetch_assoc($result)) {

								$contact['id']      = $row['contid'];
								$contact['name']    = $row['contactname'];
								$contact['role']    = $row['role'];
								$contact['number']  = $row['phoneno'];

								$contacts[] = $contact;
							}

							echo '<select id="contactss" multiple="" required="required" name="custcontacts[]" size="5" style="width: 100%">';

							foreach ($contacts as $contact) {

								echo '<option value="' . $contact['id'] . '">' . $contact['name'] . ' &nbsp;[' . $contact['role'] . ']&nbsp;(' . $contact['number'] . ')</option>';
							}

							echo '</select>';

							?>
							<h4 id="submitcontacts" class="btn btn-success" style="width: 100%">Submit</h4>
						</div>
                        <?php
                                if(userHasPermission($db, 'close_salescase')) {

						        echo '<h4 id="closesalescasebutton" style="padding: 10px; text-align: center; margin-bottom: 0px;" class="btn-danger">Close Salescase</h4>';
						        }
                                ?>
						<div id="closesalescasediv" class="displaynone" style="width: 100%; border: 2px #424242 solid; border-top: 0px; padding: 20px">
							<?php

							$DCSQL = 'SELECT * FROM dcs WHERE salescaseref="' . $salescase['salescaseref'] . '"';
							$SOSQL = 'SELECT * FROM salesorders WHERE salescaseref="' . $salescase['salescaseref'] . '" and quotation=1';
							$OCSQL = 'SELECT * FROM ocs WHERE salescaseref="' . $salescase['salescaseref'] . '"';
							$POCOT = count(glob("../" . $_SESSION['part_pics_dir'] . '/' . 'pofile_' . $salescase['salescaseref'] . '*.pdf'));

							if (mysqli_num_rows(mysqli_query($db, $DCSQL)) > 0) {

								echo "<h4 style='text-align:center'>DC Level</h4>";

								echo "<form method='post' action='" . $RootPath . "/api/closeSalescase.php'>";
								echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
								echo '<input type="hidden" name="salescaseref" value="' . $salescase['salescaseref'] . '" />';
								echo '<input type="hidden" name="closesalescase" value="closesalescase" />';
								echo '<input type="submit" style="width:100%" class="btn btn-success" name="closesalescase" value="' . _('Close Salescase') . '" />';
								echo "</form>";
							} else if (mysqli_num_rows(mysqli_query($db, $DCSQL)) == 0 && $POCOT > 0) {
								$SQL = "SELECT reason FROM caseclosereasonspo";
								$res = mysqli_query($db, $SQL);

								echo "<h4 style='text-align:center'>PO Level</h4>";

								echo "<form method='post' action='" . $RootPath . "/api/closeSalescase.php'>";
								echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
								echo '<input type="hidden" name="salescaseref" value="' . $salescase['salescaseref'] . '" />';
								echo '<input type="hidden" name="stage" value="PO" />';
								echo '<select name="caseclosereason" style="width:100%" required>';
								while ($rRow = mysqli_fetch_assoc($res)) {
									echo '<option value="' . $rRow['reason'] . '">' . $rRow['reason'] . '</option>';
								}
								echo '</select>';
								echo '<textarea name="closingremarks" placeholder="Closing Remarks" style="width:100%; height:80px; max-height:80px; min-height:80px; margin-top:10px;" required></textarea>';
								echo '<input type="submit" class="btn btn-success" style="width:100%" value="Close Case">';
								echo "</form>";
							} else if (mysqli_num_rows(mysqli_query($db, $SOSQL)) > 0) {
								$SQL = "SELECT reason FROM caseclosereasonsquotation";
								$res = mysqli_query($db, $SQL);

								echo "<h4 style='text-align:center'>Quotation Level</h4>";

								echo "<form method='post' action='" . $RootPath . "/api/closeSalescase.php'>";
								echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
								echo '<input type="hidden" name="salescaseref" value="' . $salescase['salescaseref'] . '" />';
								echo '<input type="hidden" name="stage" value="Quotation" />';
								echo '<select name="caseclosereason" style="width:100%" required>';
								while ($rRow = mysqli_fetch_assoc($res)) {
									echo '<option value="' . $rRow['reason'] . '">' . $rRow['reason'] . '</option>';
								}
								echo '</select>';
								echo '<textarea name="closingremarks" placeholder="Closing Remarks" style="width:100%; height:80px; max-height:80px; min-height:80px; margin-top:10px;" required></textarea>';
								echo '<input type="submit" class="btn btn-success" style="width:100%" value="Close Case">';
								echo "</form>";
							} else if (mysqli_num_rows(mysqli_query($db, $SOSQL)) == 0) {
								$SQL = "SELECT reason FROM caseclosereasonsenquiry";
								$res = mysqli_query($db, $SQL);

								echo "<h4 style='text-align:center'>Enquiry Level</h4>";

								echo "<form method='post' action='" . $RootPath . "/api/closeSalescase.php'>";
								echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
								echo '<input type="hidden" name="salescaseref" value="' . $salescase['salescaseref'] . '" />';
								echo '<input type="hidden" name="stage" value="Enquiry" />';
								echo '<select name="caseclosereason" style="width:100%" required>';
								while ($rRow = mysqli_fetch_assoc($res)) {
									echo '<option value="' . $rRow['reason'] . '">' . $rRow['reason'] . '</option>';
								}
								echo '</select>';
								echo '<textarea name="closingremarks" placeholder="Closing Remarks" style="width:100%; height:80px; max-height:80px; min-height:80px; margin-top:10px;" required></textarea>';
								echo '<input type="submit" class="btn btn-success" style="width:100%" value="Close Case">';
								echo "</form>";
							}
							?>
						</div>
					<?php } ?>
				</div>
			</div>
			<div id="rpptab" class="tab toggleviewnone">
				<section class="panel panel-featured panel-featured-info">
					<header class="panel-heading">
						<h2 class="panel-title" style="cursor:pointer">RPP Files</h2>
					</header>
					<div class="panel-body">
						<div class="">
							<?php

							$rppFilePath = glob("../" . $_SESSION['part_pics_dir'] . '/' . 'rppfile_' . $salescase['salescaseref'] . '*.pdf');
							foreach ($rppFilePath as $rppFile) {
								echo '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $rppFile . '">' . substr($rppFile, 38) . '</a>';
							}

							?>
						</div>
						<?php if ($salescase['closed'] == 0) { ?>
							<div class="col-md-12" style="margin-top: 10px; border: 2px #424242 solid; padding: 10px">
								<h4 style="text-align: center;">Add New RPP File</h4>
								<form action="<?php echo $RootPath; ?>/api/uploadRpp.php" enctype="multipart/form-data" method="post">
									<input type="file" class="col-md-6" name="rppfile" required="">
									<input type="hidden" value="<?php echo $salescase['salescaseref']; ?>" name="salescaseref">
									<input type="submit" class="btn btn-success col-md-6">
								</form>
							</div>
						<?php } ?>
					</div>
				</section>
				<section class="panel panel-featured panel-featured-info">
					<header class="panel-heading">
						<h2 class="panel-title" style="cursor:pointer">Purchase Orders</h2>
					</header>
					<div class="panel-body">
						<?php

						$poFilePath = glob("../" . $_SESSION['part_pics_dir'] . '/' . 'pofile_' . $salescase['salescaseref'] . '*.pdf');
						$poCount = 0;
						foreach ($poFilePath as $poFile) {

							$poCount++;
							$SQL = 'SELECT pono FROM salescasepo WHERE salescaseref="' . $salescase['salescaseref'] . '" AND pocount =' . $poCount;
							$res = mysqli_query($db, $SQL);
							$pono = mysqli_fetch_assoc($res)['pono'];

							if ($pono != "Not Set Yet")
								echo '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $poFile . '">' . $pono . ' (' . substr($poFile, 38) . ')</a>';
							else {
								echo '<div class="col-md-12" style="border: 2px #424242 solid; margin:5px 0;">';
								echo '<a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $poFile . '">' . $pono . ' (' . substr($poFile, 38) . ')</a>';
								echo '<form action="' . $RootPath . '/api/updatePONO.php" method="post">';
								if ($salescase['closed'] == 0) {
									echo '<input type="text" class="col-md-12" name="pono" placeholder="Enter PO Name here" required>';
									echo '<input type="hidden" name="salescaseref" value="' . $salescase['salescaseref'] . '">';
									echo '<input type="hidden" name="count" value="' . $poCount . '">';
									echo '<input class="btn btn-success col-md-12" style="margin:5px 0" type="submit" value="Update">';
								}
								echo '</form>';
								echo '</div>';
							}
						}
						?>
						<?php if ($salescase['closed'] == 0) { ?>
							<div class="col-md-12" style="margin-top: 10px; border: 2px #424242 solid; padding: 10px">
								<h4 style="text-align: center;">Add New PO File</h4>
								<form action="<?php echo $RootPath; ?>/api/uploadPO.php" enctype="multipart/form-data" method="post">
									<input type="text" class="col-md-4" name="poname" placeholder="PO File Name" style="border: 1px #424242 solid" required="">
									<input type="file" class="col-md-4" name="pofile" required>
									<input type="hidden" name="salescaseref" value="<?php echo $salescase['salescaseref']; ?>">
									<input type="submit" class="btn btn-success col-md-4">
								</form>
							</div>
						<?php } ?>
					</div>
				</section>
				<section class="panel panel-featured panel-featured-info">
					<header class="panel-heading">
						<h2 class="panel-title" style="cursor:pointer">Drawings</h2>
					</header>
					<div class="panel-body">
						<button class="btn btn-primary pull-right">Add New Drawing</button>
					</div>
				</section>
			</div>



			<div id="quotationtab" class="tab toggleviewnone">
				<?php

				$SQL = 'SELECT * from salesorders where salescaseref="' . $salescase['salescaseref'] . '" and quotation=1';
				$quotationsResult = mysqli_query($db, $SQL);

				$quotations = [];
				while ($quotation = mysqli_fetch_assoc($quotationsResult)) {

					$quotations[] = $quotation;
					$lastQuotation = $quotation['orderno'];
				}

				$totalQuotationsCount = count($quotations);

				if ($salescase['closed'] == 0 && $quotationInProgress == 0) {

					echo '<a class="btn btn-success col-md-12" 
									style="margin: 0px 0"
									href = "' . $RootPath . '/../makequotation.php?NewOrder=Yes&
									salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] . '"
									>Make New Quotation</a>';

					if (userHasPermission($db, 'quick_quotation')) {

						echo '<a class="btn btn-success col-md-12" 
										style="margin: 5px 0"
										href = "' . $RootPath . '/../makequotation.php?NewOrder=Yes&
										salescaseref=' . $salescase['salescaseref'] .
							'&selectedcustomer=' . $salescase['debtorno'] .
							'&DebtorNo=' . $salescase['debtorno'] .
							'&BranchCode=' . $salescase['branchcode'] . '
										&quickQuotation=1"
										>Make Quick Quotation</a>';
					}

					if (count($quotations) > 0) {

						echo '<a class="btn btn-warning col-md-12" 
										style="margin-bottom: 5px" 
										target="_blank" 
										href="' . $RootPath . '/../quotation/api/editExisting.php?
										rootpath=' . $RootPath . '/..&orderno=' . $lastQuotation .
							'&salescaseref=' . $salescase['salescaseref'] . '"
										>Edit Last Quotation(' . $lastQuotation . ')</a>';
					}
				} else if ($salescase['closed'] == 0 && $quotationInProgress == 1) {

					$SQL = 'SELECT orderno,eorderno FROM salesordersip WHERE salescaseref="' . $_GET['salescaseref'] . '"';
					$result = mysqli_query($db, $SQL);
					$abcdef = mysqli_fetch_assoc($result);
					$ipQuotationNo = $abcdef['orderno'];
					$eIpQuotationNo = $abcdef['eorderno'];

					echo '<a id="discardquotation" class="btn btn-danger col-md-6" style="margin-bottom:10px"
									href = "' . $RootPath . '/../makequotation.php?orderno=' . $eIpQuotationNo .
						'&discard=true&salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] .
						'">Discard Quotation InProgress</a>';

					echo '<a class="btn btn-primary col-md-6" style="margin-bottom:10px" 
									target="_blank" 
							 		href ="' . $RootPath . '/../makequotation.php?orderno=' . $ipQuotationNo .
						'&salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] .
						'&ordernos='.$eIpQuotationNo.
						'">Continue Quotation InProgress (' . ($eIpQuotationNo ?: "New/Revision") . ')</a>';
				}

				if ($salescase['closed'] == 0 && $quotationInProgress == 0 && $canMakeDuplicate) {

					echo "<form method='post' action='api/makeDuplicateQuotation.php'>";
					echo "<input type='number' name='orderno' class='col-md-6' style='padding:7px; border:2px  solid #ccc; border-radius:7px; margin-bottom:10px;' placeholder='Existing Quotation No To Duplicate' required/>";
					echo "<input type='hidden' name='salescaseref' value='" . $salescase['salescaseref'] . "'/>";
					echo "<input type='hidden' name='FormID' value='" . $_SESSION['FormID'] . "'/>";
					echo "<button class='col-md-6 btn btn-primary' style='padding:7px; border:2px  solid #ccc; border-radius:7px; margin-bottom:10px;' type='submit'>Use Quotation Template</button>";
					echo "</form>";
				}

				foreach ($quotations as $quotation) {

					echo '<section class="panel panel-featured panel-featured-info col-md-12">';
					echo '<header class="panel-heading">';
					echo '<h2 class="panel-title" style="cursor:pointer">Quotation (' . $quotation['orderno'] . ')';

					if ($quotation['quickQuotation'] == "1") {
						echo ' Quick Quotation';
					}

					if ($quotation['revision'] != "") {
						echo " " . $quotation['revision'] . " Of (" . $quotation['revision_for'] . ")";
					}

					if ($quotation['withoutItems'] != "1" && $canCreateRevision && $quotationInProgress == 0 && $salescase['closed'] == 0) {
						echo '<a href="../quotation/api/createQuotationRevision.php?orderno=' . $quotation['orderno'] . '" class="btn btn-warning" style="float:right">Revise</a>';
					}

					echo '</h2>';
					echo '</header>';
					echo '<div class="panel-body">';

					if ($quotation['quickQuotation'] != "1") {

						echo '<a class="btn col-md-6" 
									style="color: white; background: #61998e"
									href="' . $RootPath . '/../PDFQuotationExternal.php?identifier=&
									QuotationNo=' . $quotation['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
									>External</a>';

						echo '<a class="btn col-md-6" 
									style="color: white; background: #626399"
									href="' . $RootPath . '/../PDFQuotation.php?identifier=&
									QuotationNo=' . $quotation['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
									>Internal</a>';
					} else {

						echo '<a class="btn col-md-6" 
										style="color: white; background: #61998e; margin-bottom:10px"
										href="' . $RootPath . '/../PDFQuotationExternalQuick.php?identifier=&
										QuotationNo=' . $quotation['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
										>External</a>';
						if ($quotation['withoutItems'] == "0") {
							echo '<a class="btn col-md-6" 
										style="color: white; background: #61998e; margin-bottom:10px"
										href="' . $RootPath . '/../PDFQuotation.php?identifier=&
										QuotationNo=' . $quotation['orderno'] .
								'&salescaseref=' . $salescase['salescaseref'] . '"
										>Internal</a>';
						}
					}

					if ($quotation['withoutItems'] == "1") {

						if ($salescase['closed'] == 0 && $quotationInProgress == 1) {

							echo '<h5 style="text-align:center">Quotation In Progress... (Discard or Save to edit)</h5>';
						} else if ($salescase['closed'] == 0 && $quotationInProgress == 0 && userHasPermission($db, 'quick_quotation')) {

							echo '<a class="btn btn-warning col-md-12" 
											style="margin-bottom: 5px" 
											target="_blank" 
											href="' . $RootPath . '/../quotation/api/editExisting.php?
											rootpath=' . $RootPath . '/..&orderno=' . $quotation['orderno'] .
								'&salescaseref=' . $salescase['salescaseref'] . '"
											>Add Items And Finalize</a>';
						}
					}

					echo '</div>';
					echo '</section>';
				}

				?>
			</div>
			<div id="octab" class="tab toggleviewnone">
				<?php

				$SQL = 'SELECT * FROM ocs WHERE salescaseref="' . $salescase['salescaseref'] . '"';
				$ocResult = mysqli_query($db, $SQL);

				$totalOcCount = mysqli_num_rows($ocResult);

				$SQL = "SELECT * FROM salesorders WHERE salescaseref='" . $salescase['salescaseref'] . "'";
				$soCountR = mysqli_query($db, $SQL);

				$SQL = 'SELECT pono FROM salescasepo 
								WHERE salescaseref="' . $salescase['salescaseref'] . '"
								AND pono NOT IN (SELECT pono from ocs where salescaseref="' . $salescase['salescaseref'] . '")';
				$poResult = mysqli_query($db, $SQL);

				if ($salescase['closed'] == 0 && mysqli_num_rows($ocResult) == 0 && mysqli_num_rows($poResult) == 0 && mysqli_num_rows($soCountR) == 0) {

					echo '<a id="createnewoc" class="col-md-12 btn btn-success" 
									style="margin-bottom: 10px"
									href = "' . $RootPath . '/../oc/createOC.php?NewOrder=Yes
									&salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] . '"
									>Make OC Document</a>';
				}

				if ($salescase['closed'] == 0 && mysqli_num_rows($poResult) > 0) {

					while ($po = mysqli_fetch_assoc($poResult)) {

						if ($po['pono'] != "Not Set Yet") {

							echo '<a id="createnewoc" class="col-md-12 btn btn-success" 
											style="margin-bottom: 10px"
											href="' . $RootPath . '/../oc/createOC.php?NewOrder=Yes
											&pono=' . $po['pono'] .
								'&salescaseref=' . $salescase['salescaseref'] .
								'&ocref=1&selectedcustomer=' . $salescase['debtorno'] .
								'&DebtorNo=' . $salescase['debtorno'] .
								'&BranchCode=' . $salescase['branchcode'] . '" 
											>Make OC Document for PO ' . $po['pono'] . '</a>';
						}
					}
				}

				$canCreateDC = false;

				if ($totalQuotationsCount == 0) {
					$canCreateDC = true;
				}

				while ($oc = mysqli_fetch_assoc($ocResult)) {

					echo '<section class="panel panel-featured panel-featured-info col-md-12">';
					echo '<header class="panel-heading">';
					echo '<h2 class="panel-title" style="cursor:pointer">OC (' . $oc['orderno'] . ')';
					if ($oc['pono'] != '')
						echo ' for PO (' . $oc['pono'] . ')';
					echo '</h2>';
					echo '</header>';
					echo '<div class="panel-body">';

					if ($oc['inprogress'] == 1) {

						echo '<div class="col-md-6"><a class="btn" 
									style="color: white; background: #61998e; width:100%"
									href="' . $RootPath . '/../PDFOCExternal.php?OrderConfirmationNo=' . $oc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
									>External</a></div>';

						echo '<div class="col-md-6"><a class="btn" 
									style="color: white; background: #626399; width:100%"
									href="' . $RootPath . '/../PDFOC.php?OrderConfirmationNo=' . $oc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
									>Internal</a></div>';
					}

					if ($salescase['closed'] == 0) {

						echo '<a class="btn col-md-12 btn-warning" 
								style="margin:5px 0;" 
								target="_blank" 
								href="' . $RootPath . '/../oc/makeoc.php?ModifyOrderNumber=' . $oc['orderno'] .
							'&pono=' . $oc['pono'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
								>Edit OC</a>';
					}

					echo '</div>';
					echo '</section>';

					if ($oc['inprogress'] == 1)
						$canCreateDC = true;
				}

				?>
			</div>
			<div id="dctab" class="tab toggleviewnone">
				<?php
				if ($salescase['closed'] == 0 && $canCreateDC) {

					echo '<a id="createnewdc" class="col-md-12 btn btn-success" 
									style="margin-bottom: 10px"
									href="' . $RootPath . '/../dc/createDC.php?NewOrder=Yes
									&salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] .
						'">Make DC</a>';
				}

				if ($salescase['closed'] == 0 && userHasPermission($db, 'make_shop_dc')) {
					$today = DATE('Y-m-d');
					$debtorno = $clientInfo['debtorno'];
					$SQL = "SELECT DATEDIFF('$today', dcs.orddate) as diff, dcs.debtorno FROM dcs 
                                  where dcs.shop=1 AND dcs.inprogress=0 AND dcs.debtorno = '$debtorno' GROUP BY dcs.debtorno ORDER BY `diff` DESC";
					$res = mysqli_query($db, $SQL);

					$row = mysqli_fetch_assoc($res);
					$diff = (int)$row['diff'];
					$shopdc = (int)$clientInfo['disableshopdc'];



					if ($diff > (int)14 && $shopdc == (int)0) {
						echo "Shop DC Disabled";
					} else {

						echo '<a id="createnewshopdc" class="col-md-12 btn btn-success" 
									style="margin-bottom: 10px"
									href="' . $RootPath . '/../shopdc/createDC.php?NewOrder=Yes
									&salescaseref=' . $salescase['salescaseref'] .
							'&selectedcustomer=' . $salescase['debtorno'] .
							'&DebtorNo=' . $salescase['debtorno'] .
							'&BranchCode=' . $salescase['branchcode'] .
							'">Make Shop DC</a>';
					}
				}

				$SQL = 'SELECT * from dcs where salescaseref="' . $salescase['salescaseref'] . '"';
				$dcResult = mysqli_query($db, $SQL);

				$totalDcCount = mysqli_num_rows($dcResult);

				while ($dc = mysqli_fetch_assoc($dcResult)) {

					if ($dc['courierslipdate'] == "0000-00-00 00:00:00")
						$dc['courierslipdate'] = "";
					if ($dc['grbdate'] == "0000-00-00 00:00:00")
						$dc['grbdate'] = "";

					echo '<section class="panel panel-featured panel-featured-info col-md-12">';
					echo '<header class="panel-heading">';
					echo '<h2 class="panel-title" style="cursor:pointer">DC (' . $dc['orderno'] . ') ';
					if ($dc['shop'] == 1) {
						if ($dc['inprogress'] == 1) {
							echo "Shop With Items";
						} else {
							echo "Shop Without Items";
						}
					}
					echo '</h2>';
					echo '</header>';
					echo '<div class="panel-body">';

					if ($dc['shop'] == 1 && $dc['inprogress'] == 0) {

						echo '<a class="btn col-md-12" 
										style="color: white; background: #61998e"
										href="' . $RootPath . '/../shopDC/shopDCPrint.php?DCNO=' . $dc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '&withoutPrice&orignal"
										>External Print Without Price</a>';

						echo '<a class="btn col-md-12" 
										style="color: white; background: #626399; margin-bottom:10px"
										href="' . $RootPath . '/../shopDC/shopDCPrint.php?DCNO=' . $dc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '&orignal"
										>External Print</a>';

						/*	echo '<a class="btn col-md-12"
										style="color: white; background: #61998e"
										href="'.$RootPath.'/../shopDC/shopDCPrint.php?DCNO='.$dc['orderno'].
										'&salescaseref='.$salescase['salescaseref'].'&withoutPrice"
										>External Print Without Price Duplicate</a>';
										
								echo '<a class="btn col-md-12" 
										style="color: white; background: #626399; margin-bottom:10px"
										href="'.$RootPath.'/../shopDC/shopDCPrint.php?DCNO='.$dc['orderno'].
										'&salescaseref='.$salescase['salescaseref'].'"
										>External Print Duplicate</a>';
							*/
					}

					if ($dc['inprogress'] == 1) {

						echo '<a class="btn col-md-6" 
										style="color: white; background: #61998e"
										href="' . $RootPath . '/../PDFDChExternal.php?DCNo=' . $dc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
										>External</a>';

						echo '<a class="btn col-md-6" 
										style="color: white; background: #626399"
										href="' . $RootPath . '/../PDFDCh.php?DCNo=' . $dc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
										>Internal</a>';

						echo '<a class="btn col-md-6" 
										style="color: white; background: #626399"
										href="' . $RootPath . '/../PDFDChExternalWithoutRates.php?identifier=&
										DCNo=' . $dc['orderno'] . '"
										>External without rates</a>';

						echo '<a class="btn col-md-6" 
										style="color: white; background: #61998e"
										href="' . $RootPath . '/../PDFDChExternalBill.php?identifier=&
										DCNo=' . $dc['orderno'] . '"
										>External Bill</a>';
					}

					/*if($salescase['closed'] == 0 && ($_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 8 OR $_SESSION['AccessLevel'] == 10 OR $dc['inprogress'] == 0)){
                           */
					if ($salescase['closed'] == 0 && (userHasPermission($db, 'can_edit_dc') or $dc['inprogress'] == 0)) {
						/*echo '<a class="btn col-md-12 btn-warning" 
										target="_blank" 
										href="'.$RootPath.'/../dc/makedc.php?ModifyOrderNumber='.$dc['orderno'].
										'&salescaseref='.$salescase['salescaseref'].'"
										>Edit DC</a>';*/
						echo '<a class="btn col-md-12 btn-warning" 
										target="_blank" 
										href="' . $RootPath . '/../';
						echo ($dc['shop'] == 0) ? 'dc' : 'shopdc';
						echo '/makedc.php?ModifyOrderNumber=' . $dc['orderno'] .
							'&salescaseref=' . $salescase['salescaseref'] . '"
										>Edit DC</a>';
					}
					if (userHasPermission($db, 'can_create_grb'))
						echo "<button class='btn col-md-12 btn-danger grb' onclick='createGRB(" . $dc['orderno'] . "," . $dc['shop'] . ",\"" . $dc['salescaseref'] . "\",\"" . $dc['debtorno'] . "\",\"" . $dc['branchcode'] . "\")'>GRB</button>";

					if ($dc['inprogress'] == 1) {

						echo '<div class="col-md-3">';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin-top: 5px; margin-bottom: 0;">';
						echo 'Courier Slip:';
						echo '</p>';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin: 0; border-top: 0;">';
						$courSlipFiles = glob('../' . $_SESSION['part_pics_dir'] . '/CourierSlip_' . $dc['orderno'] . "*");

						$index = 0;
						foreach ($courSlipFiles as $courSlipFile) {
							$index++;

							$courSlipFilePath = explode("../", $courSlipFile)[1];
							echo '<br /><a id="viewenquiry" style="width: available;" class="btn btn-warning" href = "' . $RootPath . '/' . $courSlipFile . '" target = "_blank" >' . 'attachment' . $index . '</a>';
						}
						echo '</p>';
						echo '</div>';
						echo '<div class="col-md-3">';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin-top: 5px; margin-bottom: 0;">';
						echo 'Sales TaxInvoice:';
						echo '</p>';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin: 0; border-top: 0;">';

						$invoiceFiles = glob('../' . $_SESSION['part_pics_dir'] . '/Invoice_' . $dc['orderno'] . "*");

						$index = 0;
						foreach ($invoiceFiles as $invoiceFile) {
							$index++;

							$invoiceFilePath = explode("../", $invoiceFile)[1];
							echo '<br /><a id="viewenquiry" style="width: available;" class="btn btn-warning" href = "' . $RootPath . '/' . $invoiceFile . '" target = "_blank" >' . 'attachment' . $index . '</a>';
						}
						echo '</div>';
						echo '<div class="col-md-3">';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin-top: 5px; margin-bottom: 0;">';

						echo 'Commercial Invoice:';
						echo '</p>';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin: 0; border-top: 0;">';

						$commercialinvoiceFiles = glob('../' . $_SESSION['part_pics_dir'] . '/CommercialInvoice_' . $dc['orderno'] . "*");

						$index = 0;
						foreach ($commercialinvoiceFiles as $commercialinvoiceFile) {
							$index++;

							$commercialinvoiceFilePath = explode("../", $commercialinvoiceFile)[1];
							echo '<br /><a id="viewenquiry" style="width: available;" class="btn btn-warning" href = "' . $RootPath . '/' . $commercialinvoiceFile . '" target = "_blank" >' . 'attachment' . $index . '</a>';
						}


						echo '</p>';
						echo '</div>';
						echo '<div class="col-md-3">';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin-top: 5px; margin-bottom: 0;">';

						echo 'GRB: ';
						echo '</p>';
						echo '<p style="width: 100%; padding: 5px; border: 1px #424242 solid; text-align: center; margin: 0; border-top: 0;">';
						$GRBFiles = glob('../' . $_SESSION['part_pics_dir'] . '/GRB_' . $dc['orderno'] . "*");

						$index = 0;
						foreach ($GRBFiles as $GRBFile) {
							$index++;

							$GRBFilePath = explode("../", $GRBFile)[1];
							echo '<br /><a id="viewenquiry" style="width: available;" class="btn btn-warning" href = "' . $RootPath . '/' . $GRBFile . '" target = "_blank" >' . 'attachment' . $index . '</a>';
						}
						echo '</p>';
						echo '</div>';
						echo '<div class="col-md-12">';
						echo '<h2>GRB Listing</h2>';

						$SQL = "SELECT orderno FROM grb where dcno='" . $dc['orderno'] . "'";
						$result = mysqli_query($db, $SQL);
						while ($row = mysqli_fetch_assoc($result)) {
							echo '<a class="btn col-md-6" target="_blank"
									style="color: white; background: #626399"
									href="' . $RootPath . '/../PDFGRB.php?identifier=&
									grbno=' . $row['orderno'] .
								'&salescaseref=' . $salescase['salescaseref'] . '"
									>GRB NO: ' . $row['orderno'] . '</a>';
							echo '<a class="btn col-md-6" target="_blank"
									style="color: white; background: #626399"
									href="' . $RootPath . '/../PDFGRBEXTERNAL.php?identifier=&
									grbno=' . $row['orderno'] .
								'&salescaseref=' . $salescase['salescaseref'] . '"
									>GRB NO: ' . $row['orderno'] . '[External]</a>';

							echo "<br/>";
						}
						echo '</div>';
					}


					echo '</div>';
					echo '</section>';
				}
				?>
			</div>
			<div id="pctab" class="tab toggleviewnone">
				<?php

				$SQL = 'SELECT id from panel_costing where salescaseref="' . $salescase['salescaseref'] . '" AND closed="1" ';
				$pc_reslut = mysqli_query($db, $SQL);

				// $panel_costing = [];
				$count = 0;
				while ($panel_costing = mysqli_fetch_array($pc_reslut)) {
					$count = $count + 1;
					$panel_costings[] = $panel_costing['id'];
					// $lastPC = $quotation['orderno'];

				}

				$totalpanelCostingCount = $count;

				if ($salescase['closed'] == 0 && $PanelInProgress == 0) {

					echo '<a class="btn btn-success col-md-12" 
									style="margin: 0px 0"
									href = "' . $RootPath . '/../panel_costing/index.php?NewOrder=Yes&
									salescaseref=' . $salescase['salescaseref'] .
						'&selectedcustomer=' . $salescase['debtorno'] .
						'&DebtorNo=' . $salescase['debtorno'] .
						'&BranchCode=' . $salescase['branchcode'] . '"
									target = "_blank"
									>Make New Panel Costing</a>';
				} else if ($salescase['closed'] == 0 && $PanelInProgress == 1) {

					$SQL = 'SELECT id FROM panel_costing  WHERE salescaseref="' . $_GET['salescaseref'] . '"AND closed="0" ';
					$result = mysqli_query($db, $SQL);
					while ($abcdef = mysqli_fetch_array($result)) {
						$costNo = $abcdef['id'];
					}

					echo '<a class="btn btn-primary col-md-12" style="margin-bottom:10px" 
									target="_blank" 
									onclick="pcContinue('. $costNo .')"
							 		>Continue Panel Cost InProgress (' . ($costNo ?: "New/Revision") . ')</a>';
				}
				if (!empty($panel_costings)) {
					foreach ($panel_costings as $panel_costing) {

						echo '<section class="panel panel-featured panel-featured-info col-md-12">';
						echo '<header class="panel-heading">';
						echo '<h2 class="panel-title" style="cursor:pointer">Panel Costing (' . $panel_costing . ')';


						// 	if($quotation['quickQuotation'] == "1"){
						// 		echo ' Quick Quotation';
						// 	}

						// 	if($quotation['revision'] != ""){
						// 		echo " ".$quotation['revision']." Of (".$quotation['revision_for'].")";
						// 	}

						// 	if($quotation['withoutItems'] != "1" && $canCreateRevision && $quotationInProgress == 0 && $salescase['closed'] == 0){
						// 		echo '<a href="../quotation/api/createQuotationRevision.php?orderno='.$quotation['orderno'].'" class="btn btn-warning" style="float:right">Revise</a>';
						// 	}

						echo '</h2>';
						echo '</header>';
						echo '<div class="panel-body">';

						// 	if($quotation['quickQuotation'] != "1") {

						echo '<a class="btn col-md-6" 
									style="color: white; background: #61998e"
									id="costNo"
									onclick="pdfExternal(' . $panel_costing . ')"
									>External</a>';

						echo '<a class="btn col-md-6" 
								style="color: white; background: #61998e"
								id="costNo"
								onclick="pdfInternal(' . $panel_costing . ')"
									>Internal</a>';

						echo '<a class="btn col-md-12" 
								style="color: white; background: #61998e; top:5px"
								id="costNo"
								onclick="pcContinue('. $panel_costing .')"
									>Edit Panel Cost</a>';

						// 	}else {

						// 		echo '<a class="btn col-md-6" 
						// 				style="color: white; background: #61998e; margin-bottom:10px"
						// 				href="'.$RootPath.'/../PDFQuotationExternalQuick.php?identifier=&
						// 				QuotationNo='.$quotation['orderno'].
						// 				'&salescaseref='.$salescase['salescaseref'].'"
						// 				>External</a>';
						//         if($quotation['withoutItems'] == "0") {
						//             echo '<a class="btn col-md-6" 
						// 				style="color: white; background: #61998e; margin-bottom:10px"
						// 				href="' . $RootPath . '/../PDFQuotation.php?identifier=&
						// 				QuotationNo=' . $quotation['orderno'] .
						//                 '&salescaseref=' . $salescase['salescaseref'] . '"
						// 				>Internal</a>';
						//         }
						// 	}

						// 	if($quotation['withoutItems'] == "1"){

						// 		if($salescase['closed'] == 0 && $quotationInProgress == 1){

						// 			echo '<h5 style="text-align:center">Quotation In Progress... (Discard or Save to edit)</h5>';

						// 		}else if($salescase['closed'] == 0 && $quotationInProgress == 0 && userHasPermission($db, 'quick_quotation')){

						// 			echo '<a class="btn btn-warning col-md-12" 
						// 					style="margin-bottom: 5px" 
						// 					target="_blank" 
						// 					href="'.$RootPath.'/../quotation/api/editExisting.php?
						// 					rootpath='.$RootPath.'/..&orderno='.$quotation['orderno'].
						// 					'&salescaseref='.$salescase['salescaseref'].'"
						// 					>Add Items And Finalize</a>';

						// 		}

						// 	}

						echo '</div>';
						echo '</section>';
					}
				}
				?>
			</div>
			<div id="salescaselog" class="tab toggleviewnone">
				<?php $salescaselog = displaySalescaseLog($db, $salescase['salescaseref']); ?>

				<table border="1" style="display: flex; justify-content: center; background: #424242; margin-bottom:10px; border: 1px white solid; color: white; text-align: center; padding: 10px;">
					<tbody>
						<tr>
							<td>Total Quotation Value</td>
							<td>Total OC Value</td>
							<td>Total DC Value</td>
						</tr>
						<tr>
							<td><?php echo $salescaselog['total']['sumq']; ?></td>
							<td><?php echo $salescaselog['total']['sumoc']; ?></td>
							<td><?php echo $salescaselog['total']['sumdc']; ?></td>
						</tr>
					</tbody>
				</table>
				<?php
				foreach ($salescaselog['itemdetails'] as $itemcode => $details) {
				?>
					<section class="panel panel-featured panel-featured-info">
						<header class="panel-heading">
							<h2 class="panel-title" style="cursor:pointer"><?php echo $itemcode; ?></h2>
						</header>
						<div class="panel-body">
							<div class="col-md-5">
								<div class="col-md-3"><strong>Model#:</strong></div>
								<div class="col-md-9"><?php echo $details['mnfCode']; ?></div>
								<div class="col-md-3"><strong>Brand:</strong></div>
								<div class="col-md-9 brandyo"><?php echo $details['manufacturers_name']; ?></div>
								<div class="col-md-3"><strong>ListPrice:</strong></div>
								<div class="col-md-9 brandyo"><?php echo $details['listPrice']; ?></div>
								<div class="col-md-3"><strong>Description:</strong></div>
								<div class="col-md-9 desc"><?php echo $details['description']; ?></div>
							</div>
							<div class="col-md-7">
								<table border="1" style="padding:5px">
									<tr>
										<th>Quotation Quantity</th>
										<td><?php echo $details['SUMQUOT']; ?></td>
										<th>Quotation Disc</th>
										<td><?php echo $details['discq'] . "%"; ?></td>
										<th>Quotation Net</th>
										<td><?php echo $details['quotationnet'] . " pkr"; ?></td>
									</tr>
									<tr>
										<th>OC Quantity</th>
										<td><?php echo $details['SUMOC']; ?></td>
										<th>OC Disc</th>
										<td><?php echo $details['discoc'] . "%"; ?></td>
										<th>OC Net</th>
										<td><?php echo $details['ocnet'] . " pkr"; ?></td>
									</tr>
									<tr>
										<th>DC Quantity</th>
										<td><?php echo $details['SUMDC']; ?></td>
										<th>DC Disc</th>
										<td><?php echo $details['discdc'] . "%"; ?></td>
										<th>DC Net</th>
										<td><?php echo $details['dcnet'] . " pkr"; ?></td>
									</tr>
									<tr>
										<th>Remarks</th>
										<td colspan="5"><textarea class="salescaselogremarks" name="<?php echo $itemcode . "-/-/-" . $salescase['salescaseref']; ?>" style="width: 100%; height: auto; min-height: 30px; max-height: 100px;"><?php echo $details['remarks']; ?></textarea></td>
									</tr>
								</table>
							</div>
						</div>
					</section>
				<?php
				}
				?>
			</div>
		</div>

		<div class="sidebar" id="recentcomments" style="visibility: hidden; left: 0;">
			<h2 style="cursor: pointer; border-right:1px black solid">Recent Comments<span style="position: absolute; left: 0" class="commentssection">--</span></h2>
			<div class="commentarea nano" id="shouldnotscroll" style="border-right:1px black solid">
				<div class="nano-content" style="display: block;  ">
					<?php
					while ($row = mysqli_fetch_assoc($recentComments)) {
						echo '<div class="bubble">';
						echo '<span class="author"><strong>By: ' . $row['username'] . '</strong></span><br>';
						echo '<span class="message"> On: <a href="salescaseview.php?salescaseref=' . $row['salescaseref'] . '">' . $row['salescaseref'] . '</a></span><br>';
						echo '<span class="time">' . date('d/m/Y h:i A', strtotime($row['time'])) . '</span><br>';
						echo '</div><br>';
					}
					?>
				</div>
			</div>
		</div>

		<div class="sidebar" id="commentsactual">
			<h2 class="" style="cursor: pointer;">Comments</h2>
			<div class="commentarea nano" id="shouldscroll">
				<div id="commentscontainer" class="nano-content" style="display: block;">
					<?php
					while ($row = mysqli_fetch_assoc($comments)) {
						echo '<div class="bubble">';
						echo '<span class="author"><strong>' . $row['username'] . '</strong></span><br>';
						echo '<span class="message">' . $row['comment'] . '</span><br>';
						if ($row['hasAudio']) {
							echo '<audio style="width:100%" preload="none" src="api/' . $row['audioPath'] . '" controls></audio>';
						}
						echo '<span class="time">' . date('d/m/Y h:i A', strtotime($row['time'])) . '</span><br>';
						echo '</div><br>';
					}
					?>
				</div>
			</div>
			<h4 id="addnewcomment" class="btn btn-success">Add Comment</h4>
		</div>

		<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center">
			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
		</footer>

		<div id="commentpopup" class="parent-comment-area" style="display: none">
			<div class="child-comment-area">
				<h3 style="margin: 0; padding: 0; text-align: center; padding-bottom: 10px">Add New Comment</h3>

				<div class="col-md-12">
					<textarea id="textcommentbox" name="" class="comment-text" placeholder="Type your comment here" style="border: 2px #424242 solid; border-radius: 4px;" maxlength="220"></textarea>
				</div>

				<div class="col-md-12">
					<div id="logplayparent" style="border: 1px #424242 solid; margin: 5px 0; padding: 5px; display: none">
						<p id="recordingstatus" style="text-align: center; margin: 0;">Recording...</p>
						<audio id="audiocommentelem" src="" autobuffer controls style="width: 100%; display: none;"></audio>
					</div>
				</div>

				<div class="col-md-12">
					<button id="addaudiobutton" class="btn btn-primary" style="margin: 5px 0; width: 100%">Add Audio</button>
				</div>

				<div id="audiorecordpre" style="display: none">
					<div class="col-md-6">
						<button id="cancelaudiobutton" class="btn btn-warning button">Cancel</button>
					</div>
					<div class="col-md-6">
						<button id="audiorecordbutton" class="btn btn-primary button"><i class="fa fa-microphone icon-2x" aria-hidden="true"></i></button>
					</div>
				</div>
				<div id="recordmenu" style="display: none">
					<div class="col-md-4">
						<button id="stoprecording" class="btn btn-danger button"><i class="fa fa-stop" aria-hidden="true"></i></button>
					</div>
					<div class="col-md-4">
						<button id="pauserecording" class="btn btn-warning button"><i class="fa fa-pause icon-2x" aria-hidden="true"></i></button>
					</div>
					<div class="col-md-4">
						<button id="continuerecording" class="btn btn-success button"><i class="fa fa-microphone icon-2x" aria-hidden="true"></i></button>
					</div>
				</div>
				<div id="uncharted">
					<div class="col-md-6">
						<button id="closecommentbox" class="col-md-12 btn btn-danger button" style="margin: 5px 0;"><i class="fa fa-close" aria-hidden="true"></i></button>
					</div>
					<div class="col-md-6">
						<button id="savecommentbutton" class="col-md-12 btn btn-success button" style="margin: 5px 0;"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
					</div>
				</div>
			</div>
		</div>
		<!--<div id="enquirypopup" class="parent-comment-area" style="display: none">
		  		<div style="width: 100%; align-items: center; justify-content: center;
   							display: flex; height: 100%;">
		  			<iframe id="previewframe" src = "../ViewerJS/#../<?php echo $enquiryFilePath; ?>" 
						width='50%' height='100vh' style="height: 100%; border: 1px black solid" 
				 		allowfullscreen webkitallowfullscreen>
				 	</iframe>
		  		</div>
		  	</div>-->

	</section>



	<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../quotation/assets/vendor/recorderjs/recorder.js"></script>
	<script src="../quotation/assets/javascripts/theme.js"></script>
	<script>
		$("#createnewoc").on("click", function(e) {
			e.preventDefault();
			var rootpath = $('#rootpath').val();
			var salesref = $('#salesref').val();
			$.ajax({
				type: 'POST',
				url: rootpath + "/api/retrieveDC.php",
				data: {
					salescaseref: salesref
				},
				dataType: "json",
				success: function(response) {

					var status = response.status;

					if (status == "success") {
						var data = response.data;
						console.log(response);
						window.totalOutstanding = parseInt(response.credit);
						window.flag = response.flag;
						window.currentCredit = window.totalOutstanding ? window.totalOutstanding : 0;
						window.creditLimit = parseInt(data.creditlimit);


						$('#clientnamebasic').html(data.name);
						$('#clientlocationbasic').html(data.locationname);

						window.debtorno = data.debtorno;

						let overLimit = "";
						if ((window.currentCredit) > window.creditLimit) {
							$('#totalquotationvalue').css("color", "red");
							overLimit = ` ( ${window.creditLimit - (window.currentCredit)} Over Credit Limit)`;
						} else {
							$('#totalquotationvalue').css("color", "#424242");
							overLimit = `${window.creditLimit - (window.currentCredit)}`;
						}


						formID = response.formid;
						window.formID = response.formid;
						let statementLink = `
                                <form id="printStatementForm" action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
                                    <input type="hidden" name="FormID" value="${window.formID}">
                                    <input type="hidden" name="cust" value="${window.debtorno}">
                                    <input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
                                </form>
                            `
						let html = "<span style='font-size: larger;'><b>Kindly share the ledger with the customer. Your cooperation is highly valuable for the company</b></span>";
						html += `<center><table border="2" style="font-size: large;color:red;">

					<tr><td> &nbsp;Total Outstanding &nbsp;</td><td> &nbsp;Credit Remaining &nbsp;</td></tr>` +
							`<tr><td>${(Math.round(window.totalOutstanding).toLocaleString())}</td><td>${(overLimit)}</td></tr></table></center><br/>`;
						html += `<center>${(statementLink)}</center>`;
						//	$('#totalquotationvalue').html(`<table>Total Outstanding (${(Math.round(window.totalOutstanding).toLocaleString())}) Document Total: `+Math.round(quotTotal).toLocaleString()+overLimit.toLocaleString());
						$('#totalquotationvalue').html(html);
						if (window.flag != "on") {
							swal({
								title: "Alert!!!",
								text: html,
								type: 'warning',
								confirmButtonColor: "#cc3f44",
								confirmButtonText: 'Print Statement',
								closeOnConfirm: true,
								html: true
							}, function() {
								confirmed = true;
								$("#printStatementForm").submit();
								$(this).unbind(e);
							});
						} else {
							$(this).unbind(e);
						}

					}

				},
				error: function() {
					swal("Error", "Some error occured ", "error");

				}
			})









			var href = this.href;

			// Slide up the content you want to slide up



		});
	</script>
	<script>
		function createGRB(dcno, shopdc, salescaseref, debtorno, branchcode) {

			swal({
				title: "GRB " + dcno,
				text: "Are you sure you want to create GRB?",
				type: "info",
				showCancelButton: true,
				closeOnConfirm: false,
				showLoaderOnConfirm: true,
			}, function() {

				$.post("../api/createGRB.php", {
					FormID: '<?php echo $_SESSION['FormID']; ?>',

					dcno,
					salescaseref,
					shopdc,
					debtorno,
					branchcode
				}, function(res, status, something) {

					res = JSON.parse(res);

					if (res.status == "success") {
						if (shopdc == 0)
							window.location.href = "../dc/grb.php?ModifyOrderNumber=" + dcno + "&salescaseref=" + salescaseref + "&grbno=" + res.grbno;
						if (shopdc == 1)
							window.location.href = "../shopdc/grb.php?ModifyOrderNumber=" + dcno + "&salescaseref=" + salescaseref + "&grbno=" + res.grbno;
					} else {
						swal("Error", res.message, "error");
					}

				});

			});

		}
	</script>

	<script>
		function pdfExternal($id) {
			let costNo = $id;
			$.ajax({
				url: "../panel_costing/cash_demand_external.php",
				method: "POST",
				data: {
					costNo: costNo
				},
				success: function(data) {
					window.open("../panel_costing/cash_demand_external.php");
				},
			});
		}

		function pdfInternal($id) {
			let costNo = $id;
			$.ajax({
				url: "../panel_costing/cash_demand_internal.php",
				method: "POST",
				data: {
					costNo: costNo
				},
				success: function(data) {
					window.open("../panel_costing/cash_demand_internal.php");
				},
			});
		}

		function pcContinue($id) {
			let costNo = $id;
			$.ajax({
				url: "../panel_costing/panel_costing.php",
				method: "POST",
				data: {
					costNo:costNo
				},
				success: function(data) {
					window.open("../panel_costing/panel_costing.php");
				},
			});
		}
		
		function stockrequest($id) {
			let stockrequest = $id;
			$.ajax({
				url: "../InterStoreStockRequest.php",
				method: "POST",
				data: {
					stockrequest:stockrequest
				},
				success: function(data) {
					window.open("../InterStoreStockRequest.php?New=Yes");
				},
			});
		}


		var lookup = [];
		lookup['detailsbutton'] = "#details";
		lookup['rppbutton'] = "#rpptab";
		lookup['quotationbutton'] = "#quotationtab";
		lookup['ocbutton'] = "#octab";
		lookup['dcbutton'] = "#dctab";
		lookup['pcbutton'] = "#pctab";
		lookup['saleslogbutton'] = "#salescaselog";
		lookup['ogpbutton'] = "#ogptab";
		$(document).ready(function() {
			$("#salescase_priority").val('<?php echo $salescase['priority'] ?>');
		});

		$("#salescase_priority").on("change", function() {
			let ref = $(this);
			$.post("api/updateSalescasePriority.php", {
				FormID: '<?php echo $_SESSION['FormID']; ?>',
				salescaseref: '<?php echo $_GET['salescaseref']; ?>',
				priority: ref.val()
			}, function(res, status) {
				res = JSON.parse(res);
				if (res.status == "success") {
					ref.css("border", "2px green solid");
				} else {
					ref.css("border", "2px red solid");
				}

			});

		});

		$("#salescase_review").on("change", function() {
			let ref = $(this);
			$.post("api/updateSalescaseReviewDate.php", {
				FormID: '<?php echo $_SESSION['FormID']; ?>',
				salescaseref: '<?php echo $_GET['salescaseref']; ?>',
				review: ref.val()
			}, function(res, status) {
				res = JSON.parse(res);
				if (res.status == "success") {
					ref.css("border", "2px green solid");
				} else {
					ref.css("border", "2px red solid");
				}

			});

		});

		$(".buttons").on("click", function(e) {
			for (btn in lookup) {
				if ($(this).attr("id") == btn) {
					if ($(lookup[btn]).hasClass("toggleviewnone"))
						$(lookup[btn]).removeClass("toggleviewnone");
					if (!$("#" + btn).hasClass("active"))
						$("#" + btn).addClass("active");
				} else {
					if (!$(lookup[btn]).hasClass("toggleviewnone"))
						$(lookup[btn]).addClass("toggleviewnone");
					$("#" + btn).removeClass("active");
				}
			}
		});

		$("#discardquotation").on("click", function(e) {
			e.preventDefault();

			var ref = $(this).attr("href");

			swal({
					title: "Are you sure?",
					text: "It will delete the quotation in progress and all changes will be lost.!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn",
					confirmButtonColor: '#8CD4F5',
					confirmButtonText: "Delete!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				},
				function() {

					window.location = ref;

				});

		});

		$("#createnewdc").on("click", function(e) {
			e.preventDefault();

			var ref = $(this).attr("href");

			swal({
					title: "Are you sure?",
					text: "It will create a new DC\nproceed only if you are sure you want to create a new DC.!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn",
					confirmButtonColor: '#8CD4F5',
					confirmButtonText: "Create DC!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				},
				function() {

					window.location = ref;

				});

		});



		$(document).ready(function() {
			var url = window.location.href;
			var index = url.split("#")[1];
			var active = false;
			for (btn in lookup) {

				if (index == btn) {
					if ($(lookup[btn]).hasClass("toggleviewnone"))
						$(lookup[btn]).removeClass("toggleviewnone");
					if (!$("#" + btn).hasClass("active"))
						$("#" + btn).addClass("active");
					active = true;
				} else {
					if (!$(lookup[btn]).hasClass("toggleviewnone"))
						$(lookup[btn]).addClass("toggleviewnone");
					$("#" + btn).removeClass("active");
				}

			}

			if (!active) {
				$(lookup['detailsbutton']).removeClass("toggleviewnone");
				$("#detailsbutton").addClass("active");
			}

		});

		$(".salescaseval").on("change", function() {
			var salescaseref = "<?php echo $salescase['salescaseref']; ?>";
			var name = $(this).attr("name");
			var val = $(this).val();

			if (name == "salescasedescription") {
				var testval = val.replace(/\s\s+/g, ' ');
				if (testval == " " || testval == "")
					return;
			}

			var formid = "<?php echo $_SESSION['FormID']; ?>";
			var ref = $(this);
			$.ajax({
				type: 'POST',
				url: "<?php echo $RootPath; ?>" + "/api/updatevalues.php",
				data: {
					FormID: formid,
					salescaseref: salescaseref,
					name: name,
					value: val
				},
				dataType: "json",
				success: function(response) {
					if (response.status == "success")
						ref.css("border", "2px green solid");
					else
						ref.css("border", "2px red solid");
				},
				error: function() {
					ref.css("border", "2px red solid");
				}
			});
		});
	</script>
	<script>
		$(document).ready(function() {

			$("#addnewcomment").on("click", function() {
				$("#commentpopup").css("display", "flex");
			});

			$('#addaudiobutton').on("click", function() {

				var pass = true;

				try {
					window.AudioContext = window.AudioContext || window.webkitAudioContext;
					navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
					window.URL = window.URL || window.webkitURL;
					audio_context = new AudioContext;
				} catch (e) {
					pass = false;
					alert('No web audio support in this browser!');
				}

				navigator.getUserMedia({
					audio: true
				}, startUserMedia, function(e) {
					pass = false;
					$("#logplayparent").css("display", "block");
					$("#recordingstatus").css("display", "block");
					$("#recordingstatus").html("HTTPS Required...<br>or access blocked");
					$("#addaudiobutton").css("display", "block");
					$("#uncharted").css("display", "block");
					$("#recordmenu").css("display", "none");
					$("#audiorecordpre").css("display", "none");
					$("#savecommentbutton").prop("disabled", false);
					$("#closecommentbox").prop("disabled", false);
					//__log('No live audio input: ' + e);
				});

				if (!pass)
					return;

				$(this).css("display", "none");
				$("#audiorecordpre").css("display", "block");

			});

			$("#audiorecordbutton").on("click", function() {

				startRecording();

				$("#audiorecordpre").css("display", "none");
				$("#logplayparent").css("display", "block");
				$("#recordmenu").css("display", "block");
				$("#audiocommentelem").css("display", "none");
				$("#uncharted").css("display", "none");
				$("#recordingstatus").css("display", "block");
				$("#recordingstatus").html("Recording...");
				$("#savecommentbutton").prop("disabled", "true");
				$("#continuerecording").prop("disabled", "true");
				$("#pauserecording").prop("disabled", false);
				$("#closecommentbox").prop("disabled", true);
			});

			$("#cancelaudiobutton").on("click", function() {
				var audio = $("#audiocommentelem");
				audio.attr("src", "");
				audio[0].pause();
				audio[0].load();

				console.log("Audio Source val: " + audio.attr("src"));
				console.log(audio.attr("src") == "");
				$("#audiorecordpre").css("display", "none");
				$("#addaudiobutton").css("display", "block");
				$("#logplayparent").css("display", "none");
			});

			$("#pauserecording").on("click", function() {

				pauseRecording();

				$("#continuerecording").prop("disabled", false);
				$("#audiocommentelem").css("display", "block");
				$("#logplayparent").css("display", "block");
				$("#recordingstatus").css("display", "block");
				$("#recordingstatus").html("Paused...");
				$(this).prop("disabled", "true");
			});

			$("#continuerecording").on("click", function() {

				startRecording();

				$("#pauserecording").prop("disabled", false);
				$("#audiocommentelem").css("display", "none");
				$("#recordingstatus").css("display", "block");
				$("#recordingstatus").html("Recording...");
				$(this).prop("disabled", true);
			});

			$("#stoprecording").on("click", function() {

				$("#audiocommentelem").css("display", "block");
				stopRecording();

				$("#recordmenu").css("display", "none");
				$("#logplayparent").css("display", "block");
				$("#audiorecordpre").css("display", "block");
				$("#recordingstatus").css("display", "none");
				$("#savecommentbutton").prop("disabled", false);
				$("#closecommentbox").prop("disabled", false);
				$("#uncharted").css("display", "block");

			});

			$("#closecommentbox").on("click", function() {
				$("#commentpopup").css("display", "none");
			});

			$("#savecommentbutton").on("click", function() {

				var textcomment = $("#textcommentbox").val();
				var audio = $("#audiocommentelem");

				textcomment = textcomment.replace(/\s\s+/g, ' ');

				if ((textcomment == "" || textcomment == " ") && audio.attr("src") == "") {
					$("#logplayparent").css("display", "block");
					$("#recordingstatus").css("display", "block");
					$("#recordingstatus").html("Text comment cannot be empty...");
					if (audio.attr("src") == "")
						$("#audiocommentelem").css("display", "none");
				} else {
					$("#recordingstatus").html("Processing...");
					$("#logplayparent").css("display", "block");
					$("#recordingstatus").css("display", "block");
					$(this).prop("disabled", true);
					$("#closecommentbox").prop("disabled", true);
					$("#addaudiobutton").prop("disabled", true);
					$("#audiorecordbutton").prop("disabled", true);
					$("#cancelaudiobutton").prop("disabled", true);

					var formData = new FormData();
					var formid = "<?php echo $_SESSION['FormID']; ?>";
					var salescaseref = "<?php echo $salescase['salescaseref']; ?>";
					var username = "<?php echo $_SESSION['UsersRealName']; ?>";

					if (audio.attr("src") != "")
						formData.append("audio", blobtosend);
					formData.append("comment", textcomment);
					formData.append("FormID", formid);
					formData.append("salescaseref", salescaseref);

					$.ajax({
						url: "<?php echo $RootPath; ?>" + "/api/addComment.php",
						type: 'POST',
						data: formData,
						async: false,
						success: function(data) {

							$("#recordingstatus").html("");
							$("#savecommentbutton").prop("disabled", false);
							$("#closecommentbox").prop("disabled", false);
							$("#addaudiobutton").prop("disabled", false);
							$("#audiorecordbutton").prop("disabled", false);
							$("#cancelaudiobutton").prop("disabled", false);

							$("#commentpopup").css("display", "none");
							$("#logplayparent").css("display", "none");

							var chtml = '<div class="bubble">';
							chtml += '<span class="author"><strong>';
							chtml += username;
							chtml += '</strong></span><br>';
							chtml += '<span class="message">';
							chtml += textcomment;
							chtml += '</span><br>';
							if (audio.attr("src") != "") {
								chtml += '<audio style="width:100%" src="';
								chtml += audio.attr("src");
								chtml += '" controls preload="none"></audio>';
							}
							chtml += '<span class="time">';
							//chtml += "".Date();
							chtml += '</span><br>';
							chtml += '</div><br>';

							$("#commentscontainer").append(chtml);
							try {
								$("#audiocommentelem").attr("src", "");
								$("#textcommentbox").val("");
							} catch (e) {}
						},
						error: function() {
							$("#recordingstatus").html("Comment Failed...");
							$("#savecommentbutton").prop("disabled", false);
							$("#closecommentbox").prop("disabled", false);
							$("#addaudiobutton").prop("disabled", false);
							$("#audiorecordbutton").prop("disabled", false);
							$("#cancelaudiobutton").prop("disabled", false);
						},
						cache: false,
						contentType: false,
						processData: false
					});

				}
			});

		});

		function startUserMedia(stream) {
			var input = audio_context.createMediaStreamSource(stream);
			//__log('Media stream created.');

			// Uncomment if you want the audio to feedback directly
			//input.connect(audio_context.destination);
			//__log('Input connected to audio context destination.');
			parentStream = stream;
			recorder = new Recorder(input);
			//__log('Recorder initialised.');
		}

		function startRecording() {
			recorder && recorder.record();
			console.log("Recording...");
			//__log('Recording...');
		}

		function pauseRecording() {
			recorder && recorder.stop();

			recorder && recorder.exportWAV(function(blob) {
				var url = URL.createObjectURL(blob);
				console.log(url);
				var audio = $("#audiocommentelem");
				audio.attr("src", url);
				audio[0].pause();
				audio[0].load();
				//audio[0].oncanplaythrough = audio[0].play();
			});

			console.log("Paused...");
		}

		function stopRecording() {
			recorder && recorder.stop();
			console.log("Stopped...");

			//__log('Stopped recording.');
			try {
				parentStream.getAudioTracks().map(callback);
			} catch (e) {
				$("#audiocommentelem").css("display", "none");
			}
			// create WAV download link using audio data blob
			//createDownloadLink();
			recorder && recorder.exportWAV(function(blob) {
				var url = URL.createObjectURL(blob);
				console.log(url);
				var audio = $("#audiocommentelem");
				audio.attr("src", url);
				audio[0].pause();
				audio[0].load();
				blobtosend = blob;
				//audio[0].oncanplaythrough = audio[0].play();
			});

			try {
				recorder.clear();
			} catch (e) {
				$("#audiocommentelem").css("display", "none");
			}
		}

		var callback = function(t) {
			t.stop();
		}

		$(document).ready(function() {
			$("#shouldscroll").nanoScroller({
				scroll: 'bottom'
			});
			$("#shouldnotscroll").nanoScroller();
		});

		$("#updatecontact").on("click", function() {
			if ($("#changecontact").hasClass("displaynone")) {
				$("#changecontact").removeClass("displaynone");
			} else {
				$("#changecontact").addClass("displaynone");
			}
		});

		$("#submitcontacts").on("click", function() {
			var selectedcontacts = $("#contactss").val();

			if (selectedcontacts == null)
				return;
			if (selectedcontacts.length <= 0)
				return;

			var salescaseref = "<?php echo $salescase['salescaseref']; ?>";
			var FormID = "<?php echo $_SESSION['FormID']; ?>";

			$.ajax({
				type: "POST",
				url: "<?php echo $RootPath ?>" + "/api/updateContacts.php",
				data: {
					FormID: FormID,
					salescaseref: salescaseref,
					contacts: selectedcontacts
				},
				success: function(response) {
					location.reload(true);
				},
				error: function() {
					console.log("contacts update failed");
				}
			});
		});

		$("#closesalescasebutton").on("click", function() {
			if ($("#closesalescasediv").hasClass("displaynone"))
				$("#closesalescasediv").removeClass("displaynone");
			else
				$("#closesalescasediv").addClass("displaynone")
		});

		$(".salescaselogremarks").on("change", function() {

			var ref = $(this);
			var raw = $(this).attr("name");
			var remarks = $(this).val();
			var itemcode = raw.split("-/-/-")[0];
			var salescaseref = raw.split("-/-/-")[1];
			var FormID = "<?php echo $_SESSION['FormID']; ?>";

			$.ajax({
				type: "POST",
				url: "<?php echo $RootPath ?>" + "/api/saveItemRemarks.php",
				data: {
					FormID: FormID,
					salescaseref: salescaseref,
					itemcode: itemcode,
					remarks
				},
				success: function(response) {
					ref.css("border", "2px green solid");
				},
				error: function() {
					ref.css("border", "2px red solid");
				}
			});
		});

		$("#viewenquiry").on("click", function(e) {

			//e.preventDefault();

			//var ref = $(this);

			//$("#enquirypopup").css("display","flex");


			//ViewerJS/#../

		});

		$("#enquirypopup").on("click", function() {

			$("#enquirypopup").css("display", "none");

		});

		$(document).ready(function() {
			var totalQuotationCount = <?php echo $totalQuotationsCount; ?>;
			var totalOcCount = <?php echo $totalOcCount; ?>;
			var totalDcCount = <?php echo $totalDcCount; ?>;
			var totalPanelCCount = <?php echo $totalpanelCostingCount; ?>;

			$("#pcCount").html(totalPanelCCount);

			$("#quotCount").html(totalQuotationCount);
			$("#ocCount").html(totalOcCount);
			$("#dcCount").html(totalDcCount);
		});



		$(".commentssection").on("click", function() {
			$("#recentcomments").toggle();
			//$("#commentsactual").toggle();
		});

		$(document).ready(function() {
			$("#recentcomments").css("visibility", "visible");
			$("#recentcomments").css("display", "none");
		});
	</script>
	<noscript>
		Javascript is required
	</noscript>

</body>

</html>

<?php

function displaySalescaseLog($db, $salescaseref)
{
	//echo	"In the function".$salescase['salescaseref'];

	$salescaselog = [];


	$SQLMAXQ = 'SELECT MAX(orderno) as maxq from salesorders WHERE salescaseref="' . $salescaseref . '"';
	$resultMAXQ = mysqli_query($db, $SQLMAXQ);
	$rowMAXQ = mysqli_fetch_assoc($resultMAXQ);
	$SQL = 'select salesorderdetails.stkcode
	FROM salesorderdetails where salesorderdetails.orderno=' . $rowMAXQ['maxq'] . ' UNION
	select ocdetails.stkcode
	FROM ocdetails INNER JOIN ocs ON ocdetails.orderno=ocs.orderno WHERE ocs.salescaseref="' . $salescaseref . '"
	UNION 
	select dcdetails.stkcode
	FROM dcdetails INNER JOIN dcs on dcs.orderno=dcdetails.orderno WHERE dcs.salescaseref="' . $salescaseref . '"';
	$result = mysqli_query($db, $SQL);

	$i = 0;
	$sumq = 0;
	$sumoc = 0;
	$sumdc = 0;
	error_reporting(0);
	while ($row = mysqli_fetch_assoc($result)) {


		$SQL0 = 'SELECT stockmaster.mnfCode,stockmaster.description,
stockmaster.materialcost,manufacturers.manufacturers_name FROM stockmaster INNER JOIN manufacturers ON 
stockmaster.brand=manufacturers.manufacturers_id WHERE stockmaster.stockid="' . $row['stkcode'] . '"';
		$result0 = mysqli_query($db, $SQL0);
		$row0 = mysqli_fetch_assoc($result0);
		$salescaselog['itemdetails'][$row['stkcode']]['mnfCode'] = $row0['mnfCode'];
		$salescaselog['itemdetails'][$row['stkcode']]['description'] = $row0['description'];
		$salescaselog['itemdetails'][$row['stkcode']]['manufacturers_name'] = $row0['manufacturers_name'];

		$salescaselog['itemdetails'][$row['stkcode']]['listPrice'] = $row0['materialcost'];

		$SUMQUOT = 0;
		$SQLlo = "SELECT quantity,orderlineno,lineoptionno FROM salesorderdetails WHERE 
			salesorderdetails.orderno=" . $rowMAXQ['maxq'] . "
			AND stkcode='" . $row['stkcode'] . "'
";

		$resultlo = mysqli_query($db, $SQLlo);
		while ($rowlo = mysqli_fetch_assoc($resultlo)) {
			$SQL1A = "SELECT quantity as qtyqopt FROM salesorderoptions WHERE 
			salesorderoptions.orderno=" . $rowMAXQ['maxq'] . "
			AND lineno=" . $rowlo['orderlineno'] . "
			AND optionno=" . $rowlo['lineoptionno'] . "
";

			$result1A = mysqli_query($db, $SQL1A);
			$row1A = mysqli_fetch_assoc($result1A);
			$SUMQUOT = $SUMQUOT + $rowlo['quantity'] * $row1A['qtyqopt'];
		}


		$SQL2 = "SELECT AVG(discountpercent)*100 as discq, AVG(unitprice) as rateq FROM salesorderdetails where salesorderdetails.orderno=" . $rowMAXQ['maxq'] . "
and stkcode='" . $row['stkcode'] . "'
";
		$result2 = mysqli_query($db, $SQL2);
		$row2 = mysqli_fetch_assoc($result2);

		$SUMDC = 0;
		$SQLlo = "SELECT dcs.orderno,quantity,orderlineno,lineoptionno FROM dcdetails INNER JOIN dcs ON dcdetails.orderno=dcs.orderno WHERE dcs.salescaseref='" . $salescaseref . "'
and stkcode='" . $row['stkcode'] . "'";


		$resultlo = mysqli_query($db, $SQLlo);
		while ($rowlo = mysqli_fetch_assoc($resultlo)) {
			$SQL1A = "SELECT quantity as qtyqopt FROM dcoptions WHERE 
dcoptions.orderno=" . $rowlo['orderno'] . "
AND lineno=" . $rowlo['orderlineno'] . "
AND optionno=" . $rowlo['lineoptionno'] . "
";

			$result1A = mysqli_query($db, $SQL1A);
			$row1A = mysqli_fetch_assoc($result1A);

			$SUMDC = $SUMDC + $rowlo['quantity'] * $row1A['qtyqopt'];
		}



		$SQL4 = "SELECT AVG(discountpercent)*100 as discdc, AVG(unitprice) as ratedc FROM dcdetails INNER JOIN dcs ON dcdetails.orderno=dcs.orderno WHERE dcs.salescaseref='" . $salescaseref . "'
and stkcode='" . $row['stkcode'] . "'
";
		$result4 = mysqli_query($db, $SQL4);
		$row4 = mysqli_fetch_assoc($result4);

		$SUMOC = 0;
		$SQLlo = "SELECT ocs.orderno,quantity,orderlineno,lineoptionno FROM ocdetails INNER JOIN ocs ON ocdetails.orderno=ocs.orderno WHERE ocs.salescaseref='" . $salescaseref . "'
and stkcode='" . $row['stkcode'] . "'";


		$resultlo = mysqli_query($db, $SQLlo);
		while ($rowlo = mysqli_fetch_assoc($resultlo)) {
			$SQL1A = "SELECT quantity as qtyqopt FROM ocoptions WHERE 
ocoptions.orderno=" . $rowlo['orderno'] . "
AND lineno=" . $rowlo['orderlineno'] . "
AND optionno=" . $rowlo['lineoptionno'] . "
";

			$result1A = mysqli_query($db, $SQL1A);
			$row1A = mysqli_fetch_assoc($result1A);

			$SUMOC = $SUMOC + $rowlo['quantity'] * $row1A['qtyqopt'];
		}


		$SQL6 = "SELECT AVG(discountpercent)*100 as discoc, AVG(unitprice) as rateoc FROM ocdetails INNER JOIN ocs ON ocdetails.orderno=ocs.orderno WHERE ocs.salescaseref='" . $salescaseref . "'
and stkcode='" . $row['stkcode'] . "'
";
		$result6 = mysqli_query($db, $SQL6);
		$row6 = mysqli_fetch_assoc($result6);
		$remark = 'remarks' . $i;
		if (isset($_POST[$remark])) {
			$SQLupdateremarks = "UPDATE salescaseremarks
SET remarks='" . $_POST[$remark] . "' WHERE salescaseref='" . $salescaseref . "' AND 
itemcode='" . $row['stkcode'] . "'
";
			DB_query($SQLupdateremarks, $db);
		}
		$SQLremarkstext = 'SELECT remarks FROM salescaseremarks WHERE salescaseref="' . $salescaseref . '"
AND itemcode="' . $row['stkcode'] . '"';
		$resultremarkstext = DB_query($SQLremarkstext, $db);
		$rowremarkstext = DB_fetch_array($resultremarkstext);
		$SUMQUOT = $SUMQUOT ?: 0;
		$salescaselog['itemdetails'][$row['stkcode']]['SUMQUOT'] = $SUMQUOT;
		$SUMOC = $SUMOC ?: 0;
		$salescaselog['itemdetails'][$row['stkcode']]['SUMOC'] = $SUMOC;
		$SUMDC = $SUMDC ?: 0;
		$salescaselog['itemdetails'][$row['stkcode']]['SUMDC'] = $SUMDC;
		$salescaselog['itemdetails'][$row['stkcode']]['discq'] = locale_number_format($row2['discq'], 2);
		$salescaselog['itemdetails'][$row['stkcode']]['discoc'] = locale_number_format($row6['discoc'], 2);
		$salescaselog['itemdetails'][$row['stkcode']]['discdc'] = locale_number_format($row4['discdc'], 2);

		$salescaselog['itemdetails'][$row['stkcode']]['quotationnet'] = locale_number_format($row2['rateq'] * (1 - $row2['discq'] / 100), 0);
		$salescaselog['itemdetails'][$row['stkcode']]['ocnet'] = locale_number_format($row6['rateoc'] * (1 - $row6['discoc'] / 100), 0);
		$salescaselog['itemdetails'][$row['stkcode']]['dcnet'] = locale_number_format($row4['ratedc'] * (1 - $row4['discdc'] / 100), 0);
		$salescaselog['itemdetails'][$row['stkcode']]['remarks'] = $rowremarkstext['remarks'];
		$sumq = $sumq + ($SUMQUOT * $row2['rateq'] * (1 - $row2['discq'] / 100));
		$sumoc = $sumoc + ($SUMOC * $row6['rateoc'] * (1 - $row6['discoc'] / 100));
		$sumdc = $sumdc + ($SUMDC * $row4['ratedc'] * (1 - $row4['discdc'] / 100));
		$SQLremarks = 'SELECT itemcode FROM salescaseremarks WHERE salescaseref="' . $salescaseref . '"
AND itemcode="' . $row['stkcode'] . '"';
		$resultremarks = DB_query($SQLremarks, $db);
		if (DB_num_rows($resultremarks) == 0) {

			$SQLinsertremarks = "INSERT into salescaseremarks(salescaseref,lineno,itemcode) VALUES ('" . $salescaseref . "',
	'" . $i . "','" . $row['stkcode'] . "') ";
			DB_query($SQLinsertremarks, $db);
		}

		$i++;
	}
	$salescaselog['total']['sumq'] = $sumq;
	$salescaselog['total']['sumoc'] = $sumoc;
	$salescaselog['total']['sumdc'] = $sumdc;
	return $salescaselog;
}
?>