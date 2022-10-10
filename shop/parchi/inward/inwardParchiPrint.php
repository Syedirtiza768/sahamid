<?php 

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	include('../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_GET['parchi']);
	
	if(isset($_GET['internal']) && !userHasPermission($db,"inward_parchi_internal")){
		echo "<div style='display:flex; justify-content:center; align-items:center; height:70vh; flex-direction: column;'><h1 style='color:grey'>403</h1><h2 style='color:grey'>Action Forbidden!</h2>"."<img src='http://images.clipartpanda.com/ass-clipart-www_Sticker_Tk_kiss_my_ass.svg' width='200' height='300'><h2 style='color:grey'>Permission Denied!</h2></div>";
		return;
	}

	if(!isset($parchi) || trim($parchi) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms'

			]);
		return;

	}

	$SQL = "SELECT bazar_parchi.*,suppliers.suppname as name FROM bazar_parchi 
			LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE parchino='".$parchi."' AND discarded=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo "<div style='display:flex; justify-content:center; align-items:center; height:70vh; flex-direction: column;'><h1 style='color:grey'>404</h1><h2 style='color:grey'>Market Slip NOT FOUND!</h2></div>";
		return;

	}

	$parchi = mysqli_fetch_assoc($res);
	
	if($parchi['svid'] == "")
		$parchi['name'] = $parchi['temp_vendor'];

	$SQL = "SELECT amount,created_at FROM bpledger WHERE parchino = '".$parchi['parchino']."'";
	$advance = mysqli_query($db, $SQL);

	$parchi['advance'] = 0;

	$SQL = "SELECT bpitems.*,stockmaster.mnfpno,manufacturers.manufacturers_name as bname, stockmaster.description,
			stockmaster.mnfCode 
			FROM bpitems 
			LEFT OUTER JOIN stockmaster ON bpitems.stockid = stockmaster.stockid
			LEFT OUTER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
			WHERE deleted_at IS NULL 
			AND parchino='".$parchi['parchino']."'";
	$res = mysqli_query($db, $SQL);

	$items = [];

	while($row = mysqli_fetch_assoc($res)){
		$items[] = $row;
	}

	$parchi['parchino'] = "MPI-".explode("-", $parchi['parchino'])[1]."-".$parchi['payment_terms'];

	$SQL = "SELECT suppallocs.amt,suppallocs.date
			FROM supptrans
			INNER JOIN suppallocs ON suppallocs.transid_allocto = supptrans.id
			WHERE type=601
			AND transno='".explode("-", $parchi['parchino'])[1]."'
			ORDER BY suppallocs.id ASC";
	$payments = mysqli_query($db, $SQL);

	$canAccessPrice = (userHasVendorPermission($db, $parchi['svid']) && $parchi['svid'] != "");

?>

<!DOCTYPE html>
<html>
<head>
	<title>Inward Parchi Print</title>
	<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<style type="text/css">
		body {
			background: rgb(204,204,204); 
		}
		page {
		  	background: white;
		  	display: block;
		  	margin: 0 auto;
		  	margin-bottom: 0.5cm;
		  	box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
		}
		page[size="A4"] {  
		  	width: 21cm;
		  	height: 29.65cm; 
		}
		page[size="A4"][layout="portrait"] {
		  	width: 29.7cm;
		  	height: 21cm;  
		}
		page[size="A3"] {
		  	width: 29.7cm;
		  	height: 42cm;
		}
		page[size="A3"][layout="portrait"] {
		  	width: 42cm;
		  	height: 29.7cm;  
		}
		page[size="A5"] {
		  	width: 14.8cm;
		  	height: 21cm;
		}
		page[size="A5"][layout="portrait"] {
		  width: 21cm;
		  height: 14.8cm;  
		}
		.watermark{
			position: absolute;
			background-image: url('../../../qrcodes/directDC/test.png');

		}
		.tdpa{
			padding:5px;
		}
		.break{
		    word-wrap: break-word;
		    white-space: normal;
		}
		.parchino{
	  		display: none;
	  	}
	  	.nonprint{
	  		display: inline-block;
	  	}
		@media print {
		  	body, page {
		    	margin: 0;
		    	box-shadow: 0;
		  	}
		  	.vendor{
		  		display: none;
		  	}
		  	.parchino{
		  		display: block;
		  	}
		  	.nonprint{
		  		display: none;
		  	}
		}

	</style>
</head>
<body>
	
	<page size="A5" <?php if(isset($_GET['internal'])){ ?> layout="portrait" <?php } ?>>
		<div>
	         <button class="btn btn-primary hidden-print" onclick="window.print()" style="text-align: right !important; position: fixed; z-index: 200; right: 0"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print</button>
	    </div>
		<div class="col-md-12" style="padding:0; padding-top: 10px; padding-bottom: 10px; border-bottom: 2px solid black;">
			<div class="col-md-6">
				<table style="position: absolute;">
					<tr>
						<td>
							<img src="../../../qrcodes/bazar/MPIW/<?php echo $parchi['transno'].'-mpiwQR.png';  ?>" alt="QR Code" height="90px" style="display: inline-block;">
						</td>
						<td>
							<div style="text-align: left; vertical-align: top; display: block; position: absolute; left:2px; top: 83px; font-size: 10px;">
								<span style="font-weight: bold;">Dated: </span><?php echo date('d/m/Y',strtotime($parchi['created_at'])); ?><br/>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="col-md-6" style="text-align: right; min-height: 55px">
				<h3 
					<?php if(isset($_GET['internal'])){ ?>
						style="margin-top:0"
					<?php } ?>
				><span class="">( <?php echo $parchi['parchino']; ?> )</span></h3>
				<p class="">M/s: <?php echo $parchi['name']; ?></p>
				<?php if(isset($_GET['internal'])){ ?>
				<p style="margin-bottom:0">OBO: <?php echo $parchi['on_behalf_of']; ?></p>
				<?php } ?>
				<!-- <p class="parchino">( <?php echo $parchi['parchino']; ?> ) [ <?php echo date('d/m/Y',strtotime($parchi['created_at'])); ?> ]</p> -->
			</div>
		</div>
		<div class="col-md-12">
			<table border="2" style="margin-top: 10px; border-radius: 8px; width: 100%">
				<thead>
					<tr>
						<th colspan="8" style="text-align: center;" class="tdpa">Items</th>
					</tr>
					<tr style="font-size: 12px">
						<th class="tdpa" style="width: 1%; text-align: center;">Sr#</th>
						<?php if(isset($_GET['internal'])){ ?>
							<th class="tdpa">StockID</th>
						<?php } ?>
						<th class="tdpa" style="min-width: 4cm; max-width: 4cm">Item Name</th>
						<th class="tdpa" style="max-width: 1.2cm; text-align: center;">Quantity</th>
						<?php if($parchi['inprogress'] == 1){ ?>
							<th class="tdpa" style="max-width: 1.4cm; text-align: center;">Received</th>
						<?php } ?>
						<th class="tdpa" style="max-width: 1.3cm; text-align: center;">Unit Price</th>
						<th class="" style="max-width: 1cm; text-align: center;">Sub Total</th>
						<?php if(isset($_GET['internal'])){ ?>
							<th class="tdpa">Comments</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php $count = 1; if($parchi['inprogress'] == 1){ $tot = 0; foreach($items as $item){ $tot += ($item['price']*$item['quantity_received']); ?>
						<tr style="font-size: 11px">
							<td class="tdpa" style="text-align: center;"><?php echo $count; ?></td>
							<?php if(isset($_GET['internal'])){ ?>
								<td class="tdpa"><?php echo $item['stockid']; ?></td>
							<?php } ?>
							<td class="break tdpa" style="min-width: 4cm; max-width: 4cm"><?php echo $item['name']; ?></td>
							<td style="text-align: center;"><?php echo $item['quantity']; ?></td>
							<td style="text-align: center;"><?php echo $item['quantity_received']; ?></td>
							<td style="text-align: center;">
							<?php 
								if($canAccessPrice){
									echo ($item['price'] == 0) ? 
										($item['discount']."<sub>Dis</sub>") :
										($item['price']."<sub>PKR</sub>");
								}
							?>
							</td>
							<td style="text-align: center;">
								<?php 
									if($canAccessPrice){
										echo locale_number_format($item['quantity_received']*$item['price'],2)."<sub>PKR</sub>";
									} 
								?>
							</td>
							<?php if(isset($_GET['internal'])){ ?>
							<td class="break tdpa" style="min-width: 3.3cm; max-width: 3.3cm"><?php echo $item['comments']; ?></td>
							<?php } ?>
						</tr>
						<?php if(isset($_GET['internal']) && $item['stockid'] != ""){ ?>
							<tr style="font-size: 11px">
								<th colspan="2" style="text-align: center;" class="tdpa">Brand</th>
								<th class="tdpa" style="text-align: center;">Model No</th>
								<th class="tdpa" colspan="2" style="text-align: center;">MNFP NO</th>
								<th colspan="3" style="text-align: center;" class="tdpa">Short Description</th>
							</tr>
							<tr style="font-size: 11px; border-top:0px; border-bottom: 2px solid #424242">
								<td colspan="2" class="tpda" style="text-align: center;"><?php echo $item['bname']; ?></td>
								<td class="tpda" style="text-align: center;"><?php echo $item['mnfCode']; ?></td>
								<td class="tpda" colspan="2" style="text-align: center;"><?php echo $item['mnfpno']; ?></td>
								<td colspan="3" style="text-align: center;" class="tpda"> <?php echo $item['description']; ?></td>
							</tr>
						<?php } ?>
					<?php $count++; } ?>
					<tr style="font-size: 11px">
						<th colspan="5" class="tdpa" style="text-align: right;">Sub Total: </th>
						<td colspan="3" class="tdpa">
							<?php 
								if($canAccessPrice){
									echo locale_number_format($tot,2)."<sub>PKR</sub>";
								}
							?>
						</td>
					</tr>
					<?php while($row = mysqli_fetch_assoc($advance)){ ?>
					<tr style="font-size: 11px">
						<th colspan="5" class="tdpa" style="text-align: right;">
							(<?php echo date('d/m/Y h:i:s A',strtotime($row['created_at'])); ?>)
							Advance: 
						</th>
						<td colspan="3" class="tdpa">
							<?php 
								if($canAccessPrice){
									echo locale_number_format($row['amount'],2).'<span style="font-size: 11px"><sub>PKR</sub></span>'; 
								}
							?>
						</td>
					</tr>
					<?php $parchi['advance'] += $row['amount']; } ?>
					<tr style="font-size: 11px">
						<th colspan="5" class="tdpa" style="text-align: right;">Total: </th>
						<td colspan="3" class="tdpa">
							<?php 
								if($canAccessPrice){
									echo locale_number_format($tot-$parchi['advance'],2)."<sub>PKR</sub>";
								}
							?>
						</td>
					</tr>
					<?php }else{ $total = 0; foreach($items as $item){ ?>

						<tr style="font-size: 11px">
							<td class="tdpa" style="text-align: center;"><?php echo $count; ?></td>
							<?php if(isset($_GET['internal'])){ ?>
								<td class="tdpa"><?php echo $item['stockid']; ?></td>
							<?php } ?>
							<td class="break tdpa" style="min-width: 4cm; max-width: 4cm"><?php echo $item['name']; ?></td>
							<td style="text-align: center;"><?php echo $item['quantity_received']; ?></td>
							<td style="text-align: center;">
								<?php 
									if($canAccessPrice){
										echo locale_number_format($item['price'],2); 
									}
								?>
							</td>
							<td style="text-align: center;">
								<?php 
									if($canAccessPrice){
										echo locale_number_format(($item['quantity_received']*$item['price']),2);				
									}
								?>
							</td>
							<?php if(isset($_GET['internal'])){ ?>
							<td class="break tdpa" style="min-width: 3.3cm; max-width: 3.3cm"><?php echo $item['comments']; ?></td>
							<?php } ?>
						</tr>
						<?php if(isset($_GET['internal']) && $item['stockid'] != ""){ ?>
							<tr style="font-size: 11px">
								<th colspan="2" style="text-align: center;" class="tdpa">Brand</th>
								<th class="tdpa" style="text-align: center;">Model No</th>
								<th class="tdpa" style="text-align: center;">MNFP NO</th>
								<th colspan="3" style="text-align: center;" class="tdpa">Short Description</th>
							</tr>
							<tr style="font-size: 11px; border-top:0px; border-bottom: 2px solid #424242">
								<td colspan="2" class="tpda" style="text-align: center;"><?php echo $item['bname']; ?></td>
								<td class="tpda" style="text-align: center;"><?php echo $item['mnfCode']; ?></td>
								<td class="tpda" style="text-align: center;"><?php echo $item['mnfpno']; ?></td>
								<td colspan="3" style="text-align: center;" class="tpda"> <?php echo $item['description']; ?></td>
							</tr>
						<?php } ?>

					<?php $total += ($item['quantity_received']*$item['price']); $count++; } ?>
					<tr style="font-size: 11px">
						<th colspan="4" class="tdpa" style="text-align: right;">Sub Total: </th>
						<td colspan="3" class="tdpa">
							<?php
								if($canAccessPrice){
									echo locale_number_format($total,2).'<span style="font-size: 11px"><sub>PKR</sub></span>'; 
								} 
							?> 
						</td>
					</tr>
					<?php while($row = mysqli_fetch_assoc($advance)){ ?>
					<tr style="font-size: 11px">
						<th colspan="4" class="tdpa" style="text-align: right;">
							(<?php echo date('d/m/Y h:i:s A',strtotime($row['created_at'])); ?>)
							Advance: </th>
						<td colspan="3" class="tdpa">
							<?php
								if($canAccessPrice){
									echo locale_number_format($row['amount'],2).'<span style="font-size: 11px"><sub>PKR</sub></span>';
								} 
							?>
						</td>
					</tr>
					<?php $parchi['advance'] += $row['amount']; } ?>
					<?php $paid = 0; while($row = mysqli_fetch_assoc($payments) && $canAccessPrice){ ?>
						<tr style="font-size: 11px">
							<th colspan="4" class="tdpa" style="text-align: right;">
								(<?php echo date('d/m/Y h:i:s A',strtotime($row['date'])); ?>) 
								Paid: 
							</th>
							<td colspan="3" class="tdpa"><?php echo locale_number_format($row['amt'],2); ?> <span style="font-size: 11px"><sub>PKR</sub></span></td>
						</tr>
					<?php $paid += $row['amt']; } ?>
					<tr style="font-size: 11px">
						<th colspan="4" class="tdpa" style="text-align: right;">Total: </th>
						<td colspan="3" class="tdpa">
							<?php 
								if($canAccessPrice){
									echo locale_number_format($total-$paid,2).'<span style="font-size: 11px"><sub>PKR</sub></span>';
								}
							?> 
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class="col-md-12" style="margin-top: 40px">
				<div class="col-md-6" style="border: 0px solid black; margin-top: -27px; height: 40px">
					<div style="font-size: 12px; position: absolute; left: 2px; font-weight: bold;">Terms & Conditions :</div>
					<div style="font-size: 12px; position: absolute; top: 16px; width: 470px">
						<p style="margin-bottom: 0px"><?php echo ($parchi['gstinvoice'] == "i") ? "Inclusive of GST.":""; ?></p>
						<p style="margin-bottom: 0px"><?php echo ($parchi['gstinvoice'] == "e") ? "Exclusive of GST.":""; ?></p>
						<p><?php echo ($parchi['terms']); ?></p>
					</div>
				</div>
				<div class="col-md-6" style="text-align: right;">
					<div>____________________</div>
					<div style="text-align: right; font-size: 12px">Signatures&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
				</div>
			</div>
			<div class="col-md-12" style="">
				<div class="col-md-6"></div>
				<div class="col-md-6"></div>
			</div>
			<div style="clear: both;"></div>
			<div class="row" style="height: 1px; width: calc(100% + 30px); border-bottom: 1px dotted black; margin-top: 10px">
			</div>
			<!--<h5 style="padding:4px 0; margin:0 -15px; text-align: center; border-bottom: 1px dotted black;">
				Powered By:
				<img src="../../../reports/balance/logo.png" height="15px" width="15px">
				compresol.com</h5>-->
		</div>	
	</page>
	<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>

	<script type="text/javascript">
		// $(window).keydown(function (e){
		//     if (e.ctrlKey || e.metaKey) {
		//     	e.preventDefault();
		//     };
		// });
	</script>
</body>
</html>