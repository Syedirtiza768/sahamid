<?php

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	
	if(!userHasPermission($db,"edit_outward_parchi")){ 
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}

	$SQL = "SELECT branchcode,brname 
			FROM custbranch 
			INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno 
			WHERE debtorsmaster.typeid=28";
	$svs = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM parchi_dc WHERE parchino='".trim($_GET['parchi'])."'";
	$attachedDC = mysqli_query($db, $SQL);

	$SQL 	= "SELECT * FROM bpcomments WHERE parchino='".$_GET['parchi']."'";
	$comments = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM bpledger WHERE parchino='".$_GET['parchi']."'";
	$ledger = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$_GET['parchi']."' AND inprogress=1 AND discarded=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		header("Location: listOutwardBazarParchi.php");
		return;
	}

	$parchiDetails = mysqli_fetch_assoc($res);

?>

<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid Bazar Parchi</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../../../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="assets/searchSelect.css" />

		<script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style type="text/css">
			table{
				margin-top: 20px;
				margin-bottom: 20px;
			}
			.inputstyle{
				height: 25px;
				border:1px solid #424242;
				border-radius: 5px;
				margin-left: 5px;
			}
			.qtyreceived{
				height: 25px;
				border:1px solid #424242;
				border-radius: 5px;
				margin-left: 5px;
				width: 50px;
			}
			.itemprice{
				height: 25px;
				border:1px solid #424242;
				border-radius: 5px;
				margin-left: 5px;
				width: 100px;
			}
			.itemrow{
				margin-top: 10px
			}
			.avoidbreaking{
				word-wrap: break-word;
			    white-space: normal;
			    word-break: break-all;
			}
			ul{
				list-style: none;
    			padding: 0;
			}
			#attacheddccontainer li{
				margin: 5px 18px;
				border: 1px solid black;
				border-radius: 7px;
				background: white;
				text-align: center;
			}
			#attacheddccontainer li span{
				position: absolute;
				right: 25px;
				cursor: pointer;
			}
			.detailsoverlay, .searchoverlay{
				width: calc( 100% + 2px); 
				min-height: calc( 100% + 2px); 
				background-color: #e6e6e6; 
				position: absolute; 
				margin: 0 -16px;
				border-radius: 6px;
				margin-bottom: 40px;
				margin-top: -1px;
				display: none;
				z-index: 2;
			}
			.detailsbody, .searchbody{
				position: relative;
			}
			.border{
				border:1px solid #424242; 
				border-radius: 7px;
			}
			.closeoverlay, .closeoverlayitem{
				margin-top: 10px; 
				margin-right: 10px;
				margin-bottom: 10px;
			}
			.searchbody td{
				border-bottom: 1px solid black;
			}
			.bubble{
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
		        white-space: pre-wrap;      /* CSS3 */   
			   	white-space: -moz-pre-wrap; /* Firefox */    
			   	white-space: -pre-wrap;     /* Opera <7 */   
			   	white-space: -o-pre-wrap;   /* Opera 7 */    
			   	word-wrap: break-word;      /* IE */
		    }

	      	.author{
	        	color: #424242;
	        	padding: 3px;
	      	}

	      	.message{
	        	color: black;
	        	padding: 3px;
	      	}

	      	.time{
	        	float: right;
	        	padding: 3px;
	      	}
	      	.tooltipss {
			    position: relative;
			    display: inline-block;
			    border-bottom: 1px dotted black;
			}

			.tooltipss .tooltiptext {
			    visibility: hidden;
			    width: 400px;
			    background-color: #555;
			    color: #fff;
			    text-align: center;
			    border-radius: 6px;
			    padding: 5px 0;
			    position: absolute;
			    z-index: 1;
			    bottom: 125%;
			    left: 50%;
			    margin-left: -200px;
			    opacity: 0;
			    transition: opacity 0.3s;
			}

			.tooltipss .tooltiptext::after {
			    content: "";
			    position: absolute;
			    top: 100%;
			    left: 50%;
			    margin-left: -5px;
			    border-width: 5px;
			    border-style: solid;
			    border-color: #555 transparent transparent transparent;
			}

			.tooltipss:hover .tooltiptext {
			    visibility: visible;
			    opacity: 1;
			}
			.tooltipss .tooltiptext table tr{
			    border-bottom: 1px #cecece solid;
			    margin-bottom: 3px;
			    padding-top: 3px;
			    padding-bottom: 3px;
			}
			footer{
				z-index: 10
			}
			.fo{
				user-select: none;
		        -moz-user-select: none;
		        -webkit-user-select: none;
		        -o-user-select: none;
			}
			.itempriceneg{
				visibility: hidden;
			}
			.itempriceneg td{
				position: absolute;
				width: 100%;
				height: auto;
			}
			.pi{
				border: 1px solid #424242;
				border-radius: 7px;
				text-align: center;
			}
			.gstinvoicea{
				display: flex;
			    align-items: center;
			    justify-content: center;
			    flex-direction: column;
			    margin: 10px;
			}
			.terms{
				border: 1px solid #424242;
				border-radius: 7px;
			}
			#saveorignalvendor{
				margin-top: 5px;
				width: 300px
			}
			.miniledgerinput{
				width: 150px; border: 1px solid #424242; border-radius: 7px; 
			}
			#saveledgervalue{
				width: 150px; margin-top: 5px;
			}
			#orignalvendor,#sltab,#adttab,#tgtab,#ftab,#cttab,.ovbtn{
				display: none;
			}
			.itemsearchterm{
				width: 300px;
				border: 1px solid #424242;
				padding: 4px;
				border-radius: 7px;
				display: block;
				margin: auto;
			}
			.searchitembtn{
				width: 300px;
				border: 1px solid #424242;
				padding: 4px;
				border-radius: 7px;
				display: block;
				margin: 4px auto;
			}
			.dataTables_wrapper .dataTables_filter label {
			    width: 100% !important;
			}
		</style>

	</head>
	<body>
		<p style="display: none;" id="selecteditemindex"></p>
		<section class="body" style="overflow: auto">

	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
	      			<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
	      			&nbsp;|&nbsp;
	      			<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a href="<?php echo $RootPath; ?>/../../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

	      	<h3 style="text-align: center; font-variant-caps: petite-caps;">
	      		<i class="fa fa-sign-in" aria-hidden="true"></i> 
	      		Outwards Bazar Parchi	
	      	</h3>

	      	<div style="display: flex; justify-content: center; width: 100%; margin-bottom: 50px">
	      		<div style="min-width: 75vw; max-width: 75vw; background-color: white;">
	      			<div class="col-md-12 border" style="min-width: 75vw;">
	      				<div class="detailsoverlay border">
	      					<div class="detailsbody">
	      						<table style="width: 100%; margin-top: 0px;">
	      							<tr>
	      								<td colspan="2">
											<?php if(userHasPermission($db, "outward_slip_orignal_client")){ ?>
	      									<button class="btn btn-success tab-btn ovbtn" data-tab="orignalvendor">
	      										Orignal Client
	      									</button>
											<?php } ?>
											<?php if(userHasPermission($db, "outward_slip_ledger")){ ?>
	      									<button class="btn btn-warning tab-btn" data-tab="sltab">
	      										Slip Ledger
	      									</button>
											<?php } ?>
	      									<button class="btn btn-info tab-btn" data-tab="tgtab">
	      										Terms & GST
	      									</button>
	      									<button class="btn btn-success tab-btn" data-tab="ftab">
	      										Files
	      									</button>
	      									<button class="btn btn-warning tab-btn" data-tab="cttab">
	      										Comments
	      									</button>
	      								</td>
	      								<td colspan="1" style="text-align: right;">
	      									<button class="btn btn-danger closeoverlay">X</button>
	      								</td>
	      							</tr>
									<?php if(userHasPermission($db, "outward_slip_orignal_client")){ ?>
	      							<tr style="border-top: 1px solid #424242; display: none;" id="orignalvendor" class="tabss">
	      								<td colspan="3" style="text-align: center; padding: 20px">
      										<div class="col-md-12">
	      										<h4 style="text-align: center;">Add Orignal Client</h4>
	      										<select id="orignalvendordropdown" 
	      												style="width: 300px; max-width: 300px; max-width: 300px; border: 1px solid black">
													<option value="">Select Client</option>
													<?php while($row = mysqli_fetch_assoc($svs)){ ?>
													<option value="<?php echo $row['branchcode']; ?>">
														<?php echo $row['brname']; ?>
													</option>
													<?php } ?>
												</select>
												<button id="saveorignalvendor" class="btn btn-success">
													Save Vendor
												</button>
	      									</div>
	      								</td>
	      							</tr>
									<?php } ?>
									<?php if(userHasPermission($db, "outward_slip_ledger")){ ?>
	      							<tr style="border-top: 1px solid #424242;" id="sltab" class="tabss">
	      								<td colspan="3" style="padding: 20px">
	      									<div class="col-md-6" style="display: flex; align-items: center; justify-content: center; flex-direction: column;">
	      										<h4 style="text-align: center;">Slip Ledger</h4>
	      										<input type="number" class="miniledgerinput" value="0">
	      										<button id="saveledgervalue" class="btn btn-success">Save</button>
	      									</div>
	      									<div class="col-md-6">
	      										<table class="table table-striped" border="1">
	      											<thead>
	      												<tr style="background-color: #424242; color: white">
	      													<th>Credit</th>
	      													<th>Debit</th>
	      													<th>Total</th>
	      													<th>Date</th>
	      												</tr>
	      											</thead>
	      											<tbody id="mini-ledger">
      												<?php $total = 0; while($row = mysqli_fetch_assoc($ledger)){ ?>
														<tr>
															<td>
																<?php echo ($row['amount'] > 0) ? $row['amount']:""; ?>
															</td>
															<td>
																<?php echo ($row['amount'] < 0) ? $row['amount']:""; ?>
															</td>
															<td><?php echo $total += $row['amount']; ?></td>
															<td>
																<?php echo date("d/m/Y",strtotime($row['created_at'])) ?>
															</td>
														</tr>
      												<?php } ?>
	      											</tbody>
	      										</table>
	      									</div>
	      								</td>
	      							</tr>
									<?php } ?>
	      							<tr style="border-top: 1px solid #424242" id="tgtab" class="tabss">
	      								<td colspan="3" style="text-align: center;">
	      									<div class="col-md-4">
	      										<h4 style="text-align: center;">Terms & Conditions</h4>
	      										<textarea name="" id="" style="width: 100%; max-height: 100px; min-height: 100px" maxlength="250" placeholder="Write Terms & Conditions here..." class="terms"></textarea>
	      									</div>
	      									<div class="col-md-4">
	      										<div class="gstinvoicea" style="padding: 30px;">
	      											<label for="gstinvoice"><h4 style="text-align: center;">GST Invoice</h4></label>
	      											<select name="gstinvoice" id="gstinvoice" style="width: 100px">
	      												<option value="none">None</option>
	      												<option value="i">Inclusive</option>
	      												<option value="e">Exclusive</option>
	      											</select>
	      										</div>
	      									</div>
	      									<div class="col-md-4">
	      										<div class="gstinvoicea" style="padding: 30px;">
	      											<label for="mpterms"><h4 style="text-align: center;">Payment Terms</h4></label>
	      											<select name="mpterms" id="mpterms" style="width: 100px">
	      												<option value="R">Immediate</option>
	      												<option value="G">Open Ended</option>
	      												<option value="B">As Per Terms</option>
	      											</select>
	      										</div>
	      									</div>
	      								</td>
	      							</tr>
	      							<tr style="border: 1px solid #424242" id="ftab" class="tabss">
	      								<td colspan="3">
	      									<h4 style="text-align: center;">Files</h4>
	      									<table style="width: 100%">
	      										<tr>
	      											<td colspan="2" class="col-md-12" style="text-align: center;">
	      												<?php

															$rppFilePath = glob('files/' .'*-MP_'.$_GET['parchi'].'.pdf');
															foreach($rppFilePath as $rppFile) {
																echo '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$rppFile.'">'.$rppFile.'</a>';
															}

														?>
	      											</td>
	      										</tr>
	      										<tr style="text-align: center;">
	      											<td class="col-md-16" style="text-align: center;">
	      												<h4 style="text-align: center;">Add New File</h4>
														<form action="<?php echo $RootPath; ?>/api/uploadFile.php" enctype="multipart/form-data" method="post">
															<input type="file" class="col-md-6" name="rppfile" required="">
															<input type="hidden" value="<?php echo $_GET['parchi']; ?>" name="parchi">	
															<input type="submit" class="btn btn-success col-md-6">	
														</form>
	      											</td>
	      										</tr>
	      									</table>
	      								</td>
	      							</tr>
	      							<tr id="cttab" class="tabss">
	      								<td colspan="3">
	      									<table style="width: 100%">
	      										<tr>
	      											<td colspan="3"><h4 style="text-align: center;">Comments</h4></td>
	      										</tr>
	      										<tr>
	      											<td class="col-md-6" style="vertical-align: top">
	      												<div class="child-comment-area">
															<div class="col-md-12" style="width: 100%">
																<textarea id="textcommentbox" name="" class="comment-text" placeholder="Type your comment here" style="border: 2px #424242 solid; border-radius: 4px; width: 100%" maxlength="220" ></textarea>
												    		</div>

												    		<div class="col-md-12">
												    			<div id="logplayparent" style="border: 1px #424242 solid; margin: 5px 0; padding: 5px; display: none">
													        		<p id="recordingstatus" style="text-align: center; margin: 0;">Recording...</p>
													        		<audio id="audiocommentelem" src="" autobuffer  controls style="width: 100%; display: none;"></audio>
													    		</div>
												    		</div>

												    		<div class="col-md-12">		
												      			<button id="addaudiobutton" class="btn btn-primary" style="margin: 5px 0; width: 100%">Add Audio</button>
												    		</div>
															
												    		<div id="audiorecordpre" style="display: none">
																<div class="col-md-6">
																	<button id="cancelaudiobutton" class="btn btn-warning button" style="width: 100%">Cancel</button>	
																</div>
																<div class="col-md-6">
																	<button id="audiorecordbutton" class="btn btn-primary button" style="width: 100%"><i class="fa fa-microphone icon-2x" aria-hidden="true"></i></button>	
																</div>
															</div>
												    		<div id="recordmenu" style="display: none">
														      	<div class="col-md-4">
														        	<button id="stoprecording" class="btn btn-danger button" style="width: 100%"><i class="fa fa-stop" aria-hidden="true"></i></button> 
														      	</div>
														      	<div class="col-md-4">
														        	<button id="pauserecording" class="btn btn-warning button" style="width: 100%"><i class="fa fa-pause icon-2x" aria-hidden="true"></i></button>  
														      	</div>
														      	<div class="col-md-4">
														        	<button id="continuerecording" class="btn btn-success button" style="width: 100%"><i class="fa fa-microphone icon-2x" aria-hidden="true"></i></button>  
														      	</div>
												    		</div>
												    		<div id="uncharted">
															    <div class="col-md-12">
															        <button id="savecommentbutton" class="col-md-12 btn btn-success button" style="margin: 5px 0; width: 100%"><i class="fa fa-floppy-o" aria-hidden="true"></i></button> 
															    </div>
														    </div>
												  		</div>		
	      											</td>
	      											<td class="col-md-6" id="commentscontainer" style="max-height: 100px; overflow: scroll;">
	      											<?php
							     						while($row = mysqli_fetch_assoc($comments)){
							     							echo '<div class="bubble">';
							     							echo '<span class="author"><strong>'.$row['author'].'</strong></span><br>';
							     							echo '<span class="message">'.$row['comment'].'</span><br>';
							     							if($row['hasAudio']){
							     								echo '<audio style="width:100%" preload="none" src="api/'.$row['audioPath'].'" controls></audio>';
							     							}
							     							echo '<span class="time">'.date('d/m/Y h:i A',strtotime($row['created_at'])).'</span><br>';
							     							echo '</div><br>';
							     						} 
									                ?>
	      											</td>
	      										</tr>
	      									</table>
	      								</td>
	      							</tr>
	      						</table>
	      					</div>
	      				</div>
						<?php if(userHasPermission($db, "attach_outward_slip_sku")){ ?>
	      				<div class="searchoverlay border">
	      					<div class="searchbody">
	      						<table style="width: 100%; margin-top: 0px;">
	      							<tr>
	      								<td style="text-align: right;">
	      									<button class="btn btn-danger closeoverlayitem">X</button>
	      								</td>
	      							</tr>
	      							<tr>
	      								<td>
	      									<div class="col-md-6" style="border-right: 1px solid black">
	      										<h5>Item Name : </h5>
	      										<span class="itemnamehere"></span>
	      									</div>
	      									<div class="col-md-6" style="border-left: 1px solid black">
	      										<h5>Item Comments : </h5>
	      										<span class="itemcommentshere"></span>
	      									</div>
	      								</td>
	      							</tr>
	      							<tr>
	      								<td>
	      									<div class="col-md-12">
	      										<h5 style="display: block; text-align: center;">Stockid/Desc</h5>
	      										<input type="text" class="itemsearchterm" placeholder="Search Item">
	      										<button class="btn btn-success searchitembtn">Search</button>
	      									</div>
	      								</td>
	      							</tr>
	      							<tr>
	      								<td style="border-bottom: 0">
	      									<div class="col-md-12" style="padding-top: 10px;">
	      										<table id="searchresultsdatatab" width="100%" class="responsive table table-striped">
													<thead>
														<tr style="background:#424242; color:white">
															<th>Item Code</th>
															<th>Model #</th>
															<th>Part #</th>
															<th>Manufacturer</th>
															<th>QOH</th>
															<th>Description</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody id="srb" style="color: black">
														
													</tbody>
													<tfoot>
														<tr style="background:#424242; color:white">
															<th>Item Code</th>
															<th>Model #</th>
															<th>Part #</th>
															<th>Manufacturer</th>
															<th>QOH</th>
															<th>Description</th>
															<th>Action</th>
														</tr>
													</tfoot>
												</table>
	      									</div>
	      								</td>
	      							</tr>
	      						</table>
	      					</div>
	      				</div>
						<?php } ?>
						<table style="width: 100%">
							<tr>
								<td colspan="2" style="text-align: right;">
									<h2 style="text-align:left">Total: 
										<span class="totalCalculatedAmount"></span>
									</h2>
									<?php if(userHasPermission($db, "outward_parchi_internal")){ ?>
										<a  class="btn btn-primary" 
											style="margin-right: 10px;" 
											target="_blank" 
											href="outwardParchiPrint.php?parchi=<?php echo $_GET['parchi']; ?>&internal">
											<i class="fa fa-print" aria-hidden="true"></i>
											Internal Print
										</a>
									<?php } ?>
									<a  class="btn btn-primary" 
										style="margin-right: 10px;" 
										target="_blank" 
										href="outwardParchiPrint.php?parchi=<?php echo $_GET['parchi']; ?>">
										<i class="fa fa-print" aria-hidden="true"></i>
										External Print
									</a>
									<button class="btn btn-info detailsbtn">+</button>
								</td>
							</tr>
							<tr>
								<td></td>
								<td style="text-align: right;">Parchi # <span id="parchino"></span></td>
							</tr>
							<tr>
								<td></td>
								<td style="text-align: right;">Date: <span id="parchidate">-------</span></td>
							</tr>
							<tr>
								<td></td>
								<td style="text-align: right;">Client: <span id="vendorabc">-------</span></td>
							</tr>
							<tr>
								<td></td>
								<td style="text-align: right;">On Behalf Of: <span id="obo">-------</span></td>
							</tr>
							<tr>
								<td></td>
								<td style="text-align: right;">Created By: <span id="createdby">-------</span></td>
							</tr>
							<tr>
								<td colspan="2" id="errormessage" style="text-align: center;"></td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;">
									<h3 style="font-variant-caps:petite-caps;">
										Items
									</h3>
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;">
									<table class="table table-striped">
										<thead>
											<th>Item Name</th>
											<th>Quantity</th>
											<th>Quantity Given</th>
											<th>Price</th>
											<th>Sub Total</th>
											<th>Details</th>
											<th colspan="2"></th>
										</thead>
										<tbody id="itemscontainer">
											
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="alert alert-danger" id="messagecontainer"  style="text-align:center; clear: both; margin: 20px 15px; display: none;">
										<strong id="errormessage2"></strong>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php if(userHasPermission($db, "discard_outward_slip")) { ?>
									<button class="btn btn-danger" style="width: 100%; margin-top: 50px" id="discardparchi">
										Discard
									</button>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php if(userHasPermission($db, "finalize_outward_market_slip")){ ?>
									<button class="btn btn-success" style="width: 100%; margin-top: 10px" id="saveparchi">
										Save
									</button>
									<?php } ?>
								</td>
							</tr>
						</table>
		      		</div>
	      		</div>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px">
      			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
      		</footer>
	      	
		</section>
      	
		<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../../quotation/assets/vendor/recorderjs/recorder.js"></script>
		<script type="text/javascript" src="assets/searchSelect.js"></script>

		<script src="assets/lame.all.js"></script>


		<script>

			var table;

			$(document).ready(function(){
				table = $('#searchresultsdatatab').DataTable({
					"columns": [
			            { "data": "stockid" },
			            { "data": "mnfcode" },
			            { "data": "mnfpno" },
			            { "data": "mname" },
			            { "data": "qoh" },
			            { "data": "description" },
			            { "data": "action" },
		        	]
				});

				$('#searchresultsdatatab tfoot th').each( function (i) {
			        var title = $('#searchresultsdatatab thead th').eq( $(this).index() ).text();
			        if($(this).html() != "Action"){
			        	$(this).html( '<input type="text" style="width:100%; padding:4px; text-align:center; color:black; border:1px solid #424242; border-radius:7px;" placeholder="Search '+title+'" data-index="'+i+'" />' );
			        }
			    });

				table.columns().every( function () {
			        var that = this;
			 
			        $('input', this.footer()).on('keyup change', function (){
			            if(that.search() !== this.value){
			                that.search(this.value).draw();
			            }
			        });
			    });

			});

			$(".searchitembtn").on("click", function(){
				
				let term = $(".itemsearchterm").val();
				let itemIndex = $("#selecteditemindex").html();
				var parchi = '<?php echo trim($_GET['parchi']); ?>';
				$.post("api/itemSearch.php", {
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					term: term,
					itemIndex: itemIndex,
					parchi: parchi,
					obo: '<?php echo $parchiDetails['on_behalf_of']; ?>'
				}, function(res, status, something){
					res = JSON.parse(res);
					table.clear().draw();	
		    		table.rows.add(res.data).draw();
				});

			});

			function attachSKU(itemIndex, stockID){
				
				swal({
					title: "Are you sure?",
					text: "This will be attach the selected item to Market Slip!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				}, function(){

					$.post('api/attachOrignalSKU.php',
						{

							FormID: '<?php echo $_SESSION['FormID']; ?>',
							itemIndex: itemIndex,
							stockid: stockID

						}, function(res, status){

							res = JSON.parse(res);

							if(res.status == "error"){
								swal("Error","Item Could not be Attached!!!","error");
							}else{
								swal("Success",res.message,"success");
								$(".searchoverlay").css("display","none");
								// $("#itm-"+itemIndex).remove();	
								//$("#itm-"+itemIndex).html("+ (...)");
								$("#itm-"+itemIndex).parent().find("p").html("("+stockID+") ");
								table.clear().draw();
							}

						}
					);

				});

			}

			function addNewItem(id,name,qty,rec,price,com,neg,lp,dis,stockid,igp_created,canAttachSKU,canNegotiatePrice,canUpdateQuantity,canDeleteItem){
				
				var html = '<tr class="itemrow" id="itm'+id+'">';
				html += '<td style="text-align:left" class="avoidbreaking">';

				/*if(stockid === ""){
					html += "<button class='btn btn-success attachsku' id='itm-"+id+"'>+</button> ";
				}else{
					html += "("+stockid+") ";
				}*/
				if(igp_created == 0 && canAttachSKU){
					html += "<button class='btn btn-success attachsku' id='itm-"+id+"'>+</button> ";
				}

				if(stockid != ""){
					html += "<p style='display:inline-block'>("+stockid+")</p> ";
				}else{
					html += "<p style='display:inline-block'></p> ";
				}

				html += "<span>"+name+"</span>";
				html += '</td>';
				html += '<td style="text-align:left">'+qty+'</td>';
				html += '<td style="text-align:left"><input type="number" class="qtyreceived" placeholder="Qty" value="'+rec+'"';
				if(!canUpdateQuantity){
					html += ' disabled';
				}
				html += '></td>';
				//html += '<td style="text-align:left"><input type="number" class="itemprice" placeholder="Price" value="'+price+'"></td>';
				html += '<td style="text-align:left"><span class="actPrice">'+Math.round(price*100)/100+'</span><sub>PKR</sub>';
				
				if(canNegotiatePrice){
					html += '&nbsp;<button class="btn btn-warning nego">+</button>';
				}
				
				html += '</td>';
				html += '<td style="text-align:left" class="subtotal">'+Math.round((price*rec)*100)/100+'</td>';
				html += '<td><textarea class="comments" style="min-width:150px; max-width:150px; max-height:70px">'+com+'</textarea></td>';
				html += '<td class="tooltipss fo"';
				
				if(neg.length > 50)
					html += 'style="color:green; font-weight:bolder"';
				
				html += '> Neg<span class="tooltiptext fo">'+neg+'</span></td>';
				html += '<td>';
				if(igp_created == 0 && canDeleteItem){
					
					html += '<button class="btn btn-danger inputstyle removeitem" style="padding-top: 2px">X</button>';		
				}
				html += '</td>';
				html += '</tr>';
				if(canNegotiatePrice){
					
				html += '<tr id="neg'+id+'" class="itempriceneg">';
				html += '<td colspan="8">';
				html += '<table style="width:100%">';
				html += '<tr>';
				html += '<td style="width:20%">List Price: <input type="number" class="pi lptu" placeholder="List Price" value="'+Math.round(lp,2)+'"></td>';
				html += '<td style="width:20%">Discount: <input type="number" class="pi dvtu" placeholder="Discount" value="'+dis+'"></td>';
				html += '<td style="width:20%">Price <input type="number" class="pi aptu" placeholder="Price" value="'+Math.round(price*100)/100+'"></td>';
				html += '<td style="width:20%">Person <input type="text" class="pi pntu" placeholder="Person Name"></td>';
				html += '<td style="width:20%"><button class="btn btn-success updateitemprice">Update</button></td>';
				html += '</tr>';
				html += '</table>';
				html += '</td>';
				html += '</tr>';
				
				}

				$("#itemscontainer").append(html);

			}

			$(document).ready(function(){

				$("#orignalvendordropdown").select2();
				
				$.post("api/retrieveOutwardBazarParchi.php",
						{
							FormID: '<?php echo $_SESSION['FormID']; ?>',
							parchi: '<?php echo $_GET['parchi']; ?>' 
						},function(res, status){

							res = JSON.parse(res);
							if(res.status == "success"){

								$("#parchino").html(res.data.parchino);
								$("#parchidate").html(res.data.created_at);
								$("#createdby").html(res.data.user);
								$("#vendorabc").html(res.data.name);
								$("#obo").html(res.data.on_behalf_of);
								$(".terms").html(res.data.terms);
								$("#gstinvoice").val(res.data.gstinvoice);
								$("#mpterms").val(res.data.payment_terms);

								if(res.data.svid == ""){
									$("#orignalvendor").css("display","table-row");
									$(".ovbtn").css("display","inline-block")
								}else{
									$("#sltab").css("display","table-row");
								}
							
								let canAttachSKU = res.data.canAttachSKU;
								let canNegotiatePrice = res.data.canNegotiatePrice;
								let canUpdateQuantity = res.data.canUpdateQuantity;
								let canDeleteItem = res.data.canDeleteItem;
								
								$.each(res.data.items,function(key, value){
									addNewItem(value.id,value.name,value.quantity,value.quantity_received,value.price,value.comments,value.neg,value.listprice,value.discount,value.stockid,res.data.igp_created,canAttachSKU,canNegotiatePrice,canUpdateQuantity,canDeleteItem);
								});
								
								recalculateTotal();
								
							}else{
								location.href = "listOutwardBazarParchi.php";		
							}

						});
			
			});

			$(document.body).on("click",".removeitem",function(){

				let ref = $(this);

				swal({
					title: "Are you sure?",
					text: "This will be removed from this parchi and cannot be added again!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes! Delete It!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				}, function(){

					let itemid = ref.parent().parent().attr("id").split("itm")[1];
					let parchi = $("#parchino").html();

					$.post('api/removeOutwardBazarParchiItem.php',
						{

							FormID: '<?php echo $_SESSION['FormID']; ?>',
							parchi: parchi,
							item: itemid

						}, function(res, status){

							res = JSON.parse(res);
							if(res.status == "error"){
								swal("Error","Item Delete Failed!!!","error");
							}else{
								swal("Success",res.message,"success");
								ref.parent().parent().remove();			
							}

						}
					);

				});
							
			});

			$("#discardparchi").on("click",function(){

				swal({
					title: "Are you sure?",
					text: "This action will remove this parchi from the system!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes! Discard It!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				}, function(){

					let parchi = $("#parchino").html();

					$.post('api/discardOutwardBazarParchi.php',
						{

							FormID: '<?php echo $_SESSION['FormID']; ?>',
							parchi: parchi

						}, function(res, status){

							res = JSON.parse(res);
							if(res.status == "error"){
								swal("Error",res.message,"error");
							}else{
								swal("Success",res.message,"success");
                                location.href = "../../../";
							}

						}
					);

				});

			});

			$(document.body).on('change','.qtyreceived', function(){
				
				let ref    = $(this);
				let parchi = $("#parchino").html();
				let itemid = ref.parent().parent().attr("id").split("itm")[1];
				let val    = $(this).val(); 

				$.post('api/updateOutwardBazarParchiItem.php',
					{

						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchi: parchi,
						item: itemid,
						type:'quantity',
						value: val

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							ref.css("border","3px solid red");
						}else{
							ref.css("border","3px solid green");
							ref.parent().parent().find(".subtotal")
									.html(ref.val()*ref.parent()
									.parent().find(".actPrice").html());
							recalculateTotal();
						}

					}
				);

			});

			$("#gstinvoice").on("change",function(){

				let parchi = $("#parchino").html();
				let value = $("#gstinvoice").val();
				let ref    = $(this);

				$.post('api/inwardGSTInvoiceRequired.php',
					{

						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchi: parchi,
						value: value

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							ref.parent().css("border","3px solid red");
						}else{
							ref.parent().css("border","3px solid green");
						}

					}
				);

			});

			$("#mpterms").on("change",function(){

				let parchi = $("#parchino").html();
				let value = $("#mpterms").val();
				let ref    = $(this);

				$.post('api/inwardGSTInvoiceRequired.php',
					{

						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchi: parchi,
						value: value,
						type: 'terms'

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							ref.parent().css("border","3px solid red");
						}else{
							ref.parent().css("border","3px solid green");
						}

					}
				);

			});

			$(document.body).on("change",".comments",function(){

				let ref    = $(this);
				let parchi = $("#parchino").html();
				let itemid = ref.parent().parent().attr("id").split("itm")[1];
				let val    = $(this).val(); 

				$.post('api/updateOutwardBazarParchiItemComment.php',
					{

						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchi: parchi,
						item: itemid,
						value: val

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							ref.css("border","3px solid red");
						}else{
							ref.css("border","3px solid green");
						}

					}
				);

			});

			$(document.body).on("change",".terms",function(){

				let ref    = $(this);
				let parchi = $("#parchino").html();
				let val    = $(this).val().replace(/\\n/g,"<br/>"); 

				$.post('api/updateTermsAndConditions.php',
					{

						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchino: parchi,
						terms: val

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							ref.css("border","3px solid red");
						}else{
							ref.css("border","3px solid green");
						}

					}
				);

			});

			$("#saveorignalvendor").on("click",function(){

				let parchi = '<?php echo trim($_GET['parchi']); ?>';
				let svid = $("#orignalvendordropdown").val();

				if(svid.trim() == ""){
					swal("Error","Vendor Not Selected.","error");
					return;
				}

				$.post("api/saveOrignalVendor.php",{
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					parchi: parchi,
					svid: svid
				}, function(res,status, something){
					console.log("res: "+res);
					console.log("status: "+status);
					console.log("something: "+something);
					$("#orignalvendor").css("display","none");
					//swal("Success","Vendor Updated Successfully.","success");
					window.location.reload();
				});

 			});

 			$("#saveledgervalue").on("click",function(){

 				$("#saveledgervalue").prop("disabled",true);

				let parchi = '<?php echo trim($_GET['parchi']); ?>';
				let amount = $(".miniledgerinput").val();

				amount = parseInt(amount);

				if(amount == 0){
					swal("Error","Amount Cannot be 0.","error");
					$("#saveledgervalue").prop("disabled", false);
					return;
				}

				$.post("api/updateSlipLedger.php",{
					FormID: '<?php echo $_SESSION['FormID']; ?>',
					parchi: parchi,
					amount: amount
				}, function(res,status, something){

					$("#saveledgervalue").prop("disabled", false);
					$(".miniledgerinput").val(0);
					
					res = JSON.parse(res);
					
					let credit = "";
					let debit = "";

					if(amount < 0){
						credit = amount;
					}else{
						debit = amount;
					}

					let total = $("#mini-ledger tr:last td:nth-child(3)").html();

					if(typeof total == "undefined"){
						total = 0;
					}

					total = parseInt(total);

					total += amount;

					let html = `<tr>
									<td>${debit}</td>
									<td>${credit}</td>
									<td>${total}</td>
									<td>${res.time}</td>
								</tr>`;
					$("#mini-ledger").append(html);
				});

 			});
			
			$("#saveparchi").on("click",function(){

				swal({
					title: "Are you sure?",
					text: "This will finalize Bazar Parchi and it wont be editable any longer!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes! Save It!",
					showLoaderOnConfirm: true,
					closeOnConfirm: false
				}, function(){

					let parchino = '<?php echo trim($_GET['parchi']); ?>';

					if(parchino.trim() == ""){
						swal("Error","Parchi # Not Provided","error");
						return;
					}

					$.post('api/finalizeOutwardBazarParchi.php',
						{
							FormID: '<?php echo $_SESSION['FormID']; ?>',
							parchino: parchino

						}, function(res, status){

							res = JSON.parse(res);
							if(res.status == "error"){
								$("#messagecontainer").css("display","block");
								$("#errormessage2").html(res.message);
								swal.close()
							}else{
								swal("Success",res.message,"success");
								location.href = "../../../";
							}

						}
					);

				});

			});

			$(".detailsbtn").on("click", function(){
				$(".detailsoverlay").css("display","block");
			});

			$(".closeoverlay").on("click", function(){
				$(".detailsoverlay").css("display","none");
			});

			$(document.body).on("click",".attachsku", function(){
				$(".searchoverlay").css("display","block");

				let ref = $(this);
				let name = ref.parent().find("span").text();
				let comments = ref.parent().parent().find("textarea").val();

				let itemIndex = ref.attr("id").split("-")[1];

				$(".itemnamehere").html(name);
				$(".itemcommentshere").html(comments);
				$("#selecteditemindex").html(itemIndex);

			});

			$(".closeoverlayitem").on("click", function(){
				$(".searchoverlay").css("display","none");
				table.clear().draw();
			});

			$(document).ready(function(){

		      	$("#addnewcomment").on("click",function(){
		        	$("#commentpopup").css("display","flex");
		      	});

	      		$('#addaudiobutton').on("click",function(){

	      			var pass = true;

	      			try{
						window.AudioContext = window.AudioContext || window.webkitAudioContext;
					    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
					    window.URL = window.URL || window.webkitURL;
					    audio_context = new AudioContext;
					}catch (e) {
						pass = false;
				    	alert('No web audio support in this browser!');
				  	}

				  	navigator.getUserMedia({audio: true}, startUserMedia, function(e) {
				      	pass = false;
			        	$("#logplayparent").css("display","block");
				      	$("#recordingstatus").css("display","block");
			        	$("#recordingstatus").html("HTTPS Required...<br>or access blocked");
			        	$("#addaudiobutton").css("display","block");
			        	$("#uncharted").css("display","block");
			        	$("#recordmenu").css("display","none");
			        	$("#audiorecordpre").css("display","none");
			        	$("#savecommentbutton").prop("disabled",false);
			        	$("#closecommentbox").prop("disabled",false);
				      	//__log('No live audio input: ' + e);
				    });

				  	if(!pass)
				  		return;

	        		$(this).css("display","none");
	        		$("#audiorecordpre").css("display","block");

	      		});

	      		$("#audiorecordbutton").on("click",function(){

	      			startRecording();

			        $("#audiorecordpre").css("display","none");
			        $("#logplayparent").css("display","block");
			        $("#recordmenu").css("display","block");
			        $("#audiocommentelem").css("display","none");
			        $("#uncharted").css("display","none");
			        $("#recordingstatus").css("display","block");
			        $("#recordingstatus").html("Recording...");
			        $("#savecommentbutton").prop("disabled","true");
			        $("#continuerecording").prop("disabled","true");
			        $("#pauserecording").prop("disabled",false);
			        $("#closecommentbox").prop("disabled",true);
	      		});

	      		$("#cancelaudiobutton").on("click",function(){
	      			var audio = $("#audiocommentelem");
	      			audio.attr("src","");
	      			audio[0].pause();
				    audio[0].load();

				    console.log("Audio Source val: "+audio.attr("src"));
				    console.log(audio.attr("src") == "");
			        $("#audiorecordpre").css("display","none");
			        $("#addaudiobutton").css("display","block");
			        $("#logplayparent").css("display","none");
	      		});

	      		$("#pauserecording").on("click",function(){

	      			pauseRecording();

			        $("#continuerecording").prop("disabled",false);
			        $("#audiocommentelem").css("display","block");
			        $("#logplayparent").css("display","block");
			        $("#recordingstatus").css("display","block");
			        $("#recordingstatus").html("Paused...");
			        $(this).prop("disabled","true");
	      		});

	      		$("#continuerecording").on("click",function(){

	      			startRecording();

			        $("#pauserecording").prop("disabled",false);
			        $("#audiocommentelem").css("display","none");
			        $("#recordingstatus").css("display","block");
			        $("#recordingstatus").html("Recording...");
			        $(this).prop("disabled",true);
	      		});

	      		$("#stoprecording").on("click",function(){

			        $("#audiocommentelem").css("display","block");
	      			stopRecording();

			        $("#recordmenu").css("display","none");
			        $("#logplayparent").css("display","block");
			        $("#audiorecordpre").css("display","block");
			        $("#recordingstatus").css("display","none");
			        $("#savecommentbutton").prop("disabled",false);
			        $("#closecommentbox").prop("disabled",false);
			        $("#uncharted").css("display","block");

	      		});

	      		$("#closecommentbox").on("click",function(){
	        		$("#commentpopup").css("display","none");
	      		});

	      		$("#savecommentbutton").on("click",function(){
	      			
	      			var textcomment = $("#textcommentbox").val();
	      			var audio = $("#audiocommentelem");

	      			textcomment = textcomment.replace(/\s\s+/g, ' ');

	      			if((textcomment == "" || textcomment == " " ) && audio.attr("src") == ""){
	      				$("#logplayparent").css("display","block");
				      	$("#recordingstatus").css("display","block");
			        	$("#recordingstatus").html("Text comment cannot be empty...");
			        	if(audio.attr("src") == "")
	      					$("#audiocommentelem").css("display","none");
	      			}
	      			else{
			        	$("#recordingstatus").html("Processing...");
			        	$("#logplayparent").css("display","block");
			        	$("#recordingstatus").css("display","block");
	      				$(this).prop("disabled",true);
	      				$("#closecommentbox").prop("disabled",true);
	      				$("#addaudiobutton").prop("disabled",true);
	      				$("#audiorecordbutton").prop("disabled",true);
	      				$("#cancelaudiobutton").prop("disabled",true);

			        	var formData = new FormData();
			        	var formid = "<?php echo $_SESSION['FormID']; ?>";
			        	var parchino = "<?php echo $_GET['parchi']; ?>"; //todoyes
			        	var username = "<?php echo $_SESSION['UsersRealName']; ?>";

			        	if(audio.attr("src") != "")
			        		formData.append("audio",blobtosend);
			        	formData.append("comment",textcomment);
			        	formData.append("FormID",formid);
			        	formData.append("parchino",parchino);

			        	$.ajax({
					        url: "<?php echo $RootPath; ?>"+"/api/addComment.php",
					        type: 'POST',
					        data: formData,
					        async: false,
					        success: function (data) {
					        	
			        			$("#recordingstatus").html("");
			        			$("#savecommentbutton").prop("disabled",false);
			      				$("#closecommentbox").prop("disabled",false);
			      				$("#addaudiobutton").prop("disabled",false);
			      				$("#audiorecordbutton").prop("disabled",false);
			      				$("#cancelaudiobutton").prop("disabled",false);
			      				
			      				$("#commentpopup").css("display","none");
			      				$("#logplayparent").css("display","none");

			      				var chtml = '<div class="bubble">';
			      				chtml += '<span class="author"><strong>';
			      				chtml += username;
			      				chtml += '</strong></span><br>';
			      				chtml += '<span class="message">';
			      				chtml += textcomment;
			      				chtml += '</span><br>';
			      				if(audio.attr("src") != ""){
			      					chtml += '<audio style="width:100%" src="';
			      					chtml += audio.attr("src");
			      					chtml += '" controls preload="none"></audio>';
			      				}
			      				chtml += '<span class="time">';
			      				chtml += '</span><br>';
			      				chtml += '</div><br>';

			      				$("#commentscontainer").append(chtml);
			      				try{
			      					$("#audiocommentelem").attr("src","");
			      					$("#textcommentbox").val("");
			      				}catch(e){}
					        },
					        error: function(){
			        			$("#recordingstatus").html("Comment Failed...");
			        			$("#savecommentbutton").prop("disabled",false);
			      				$("#closecommentbox").prop("disabled",false);
			      				$("#addaudiobutton").prop("disabled",false);
			      				$("#audiorecordbutton").prop("disabled",false);
			      				$("#cancelaudiobutton").prop("disabled",false);
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

			function pauseRecording(){
				recorder && recorder.stop();

				recorder && recorder.exportWAV(function(blob) {
			      	var url = URL.createObjectURL(blob);
			      	console.log(url);
			      	var audio = $("#audiocommentelem");
	      			audio.attr("src",url);
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
			    try{
			    	parentStream.getAudioTracks().map(callback);
			    }catch(e){
			        $("#audiocommentelem").css("display","none");
			    }
			    // create WAV download link using audio data blob
			    //createDownloadLink();
			    recorder && recorder.exportWAV(function(blob) {
			      	var url = URL.createObjectURL(blob);
			      	console.log(url);
			      	var audio = $("#audiocommentelem");
	      			audio.attr("src",url);
	      			audio[0].pause();
				    audio[0].load();
				    blobtosend = blob;
				    //audio[0].oncanplaythrough = audio[0].play();
			    });

			    try{
				    recorder.clear();
			    }catch(e){
			        $("#audiocommentelem").css("display","none");
			    }
			}

			var callback = function(t) {
			    t.stop();
			};

			$(document.body).on("click",".nego",function(){

				let id = $(this).parent().parent().attr("id").trim().split("itm")[1];
				console.log(id);

				if($("#neg"+id).css("visibility") == "hidden"){
					$("#neg"+id).find("td").css("position","relative");
					$("#neg"+id).css("visibility","visible");
					$(this).html("-");
				} else{
					$(this).html("+");
					$("#neg"+id).find("td").css("position","absolute");
					$("#neg"+id).css("visibility","hidden");
				}

			});

			$(document.body).on("change",".lptu",function(){

				let dis = $(this).parent().parent().find(".dvtu").val();
				let lp = $(this).parent().parent().find(".lptu").val();
				$(this).parent().parent().find(".aptu").val(Math.round(((1-(dis/100))*lp)*100)/100);

			});

			$(document.body).on("change",".dvtu",function(){

				let dis = $(this).parent().parent().find(".dvtu").val();
				let lp = $(this).parent().parent().find(".lptu").val();
				$(this).parent().parent().find(".aptu").val(Math.round(((1-(dis/100))*lp)*100)/100);

			});

			$(document.body).on("change",".aptu",function(){

				let dis = $(this).parent().parent().find(".dvtu").val();
				let ap = $(this).parent().parent().find(".aptu").val();

				if(dis != 0){
					let val = 0;
					val = (ap*100)/(100-dis);
					$(this).parent().parent().find(".lptu").val(Math.round(val*100)/100);
				}else{
					$(this).parent().parent().find(".lptu").val(Math.round(ap*100)/100);
				}

			});

			$(document.body).on("click",".updateitemprice",function(){

				let listprice = $(this).parent().parent().find(".lptu").val();
				let discount = $(this).parent().parent().find(".dvtu").val();
				let price = $(this).parent().parent().find(".aptu").val();
				let name = $(this).parent().parent().find(".pntu").val();
				let item = $(this).parent().parent().parent().parent().parent().parent().attr("id").split("neg")[1];

				if(price <= 0){
					swal("Error","Final Price of an item cannot be <= 0","error");
					return;
				}

				if(name.trim() == "" || name.length <2){
					swal("Error","Person name cannot be empty","error");
					return;
				}

				let ref = $(this);
				ref.prop("disabled",true);

				$.post('api/updateItemPrice.php',
					{
						FormID: '<?php echo $_SESSION['FormID']; ?>',
						parchino: '<?php echo trim($_GET['parchi']); ?>',
						listprice: listprice,
						discount: discount,
						price: price,
						name: name,
						item: item

					}, function(res, status){

						res = JSON.parse(res);
						if(res.status == "error"){
							swal("Error",res.message,"error");
							ref.prop("disabled",false);

						}else{
							
							ref.prop("disabled",false);
							let oldval = $("#itm"+item).find(".actPrice").html();

							$("#itm"+item).find(".actPrice").html(res.val);
							$("#itm"+item).find(".subtotal").html((res.val)*$("#itm"+item).find(".qtyreceived").val());

							let html = "<tr><td>";
							html += oldval+" <sub>PKR</sub></td><td> -> </td><td> ";
							html += price+" <sub>PKR</sub></td><td>  (";
							html += name+")</td><td>"+res.time+"</td></tr>";

							$("#itm"+item).find(".negcont").append(html);
							
							recalculateTotal();
							
						}

					}
				);

			});

			$(".tab-btn").on("click",function(){

				let tabToOpen = $(this).attr("data-tab");

				console.log(tabToOpen);

				$(".tabss").each(function(){

					if($(this).attr("id") == tabToOpen){
						$(this).css("display","table-row","important");
					}else{
						$(this).css("display","none");
					}

				});

			});
			
			function recalculateTotal(){
				let total = 0;
				$(".itemrow").each(function(){
					let val = $(this).find(".subtotal").html();
					total += parseInt(val);
				});
				$(".totalCalculatedAmount").html(total);
			}

		</script>

	</body>
</html>