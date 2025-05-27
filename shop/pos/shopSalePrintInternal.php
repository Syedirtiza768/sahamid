<?php

$PathPrefix = "../../";
include("../../includes/session.inc");
include('../../includes/SQL_CommonFunctions.inc');

if (!isset($_GET['internal'])) {
	$_GET['internal'] = "yes";
}

$orderno = trim($_GET['orderno']);

$SQL = "SELECT * FROM shopsale WHERE orderno='" . $orderno . "'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) == 1) {


	$sale = mysqli_fetch_assoc($res);

	$SQL = "SELECT brname FROM custbranch WHERE branchcode='" . $sale['branchcode'] . "'";
	$res = mysqli_query($db, $SQL);

	if (mysqli_num_rows($res) != 1) {
		echo json_encode([
			'status' => 'error2'
		]);
		return;
	}

	$sale['customer'] = mysqli_fetch_assoc($res);

	if ($sale['crname'] != "") {
		$sale['customer']['brname'] = html_entity_decode($sale['crname']);
	}

	$SQL = "SELECT * FROM shopsalelines WHERE orderno='" . $sale['orderno'] . "'";
	$res = mysqli_query($db, $SQL);

	$sale['lines'] = [];

	while ($row = mysqli_fetch_assoc($res)) {
		$sale['lines'][] = $row;
	}
} else {
	$SQL = "SELECT * FROM estimateshopsale WHERE orderno='" . $orderno . "'";
	$res = mysqli_query($db, $SQL);
	if (mysqli_num_rows($res) != 1) {
		echo json_encode([
			'status' => 'error1'
		]);
		return;
	}


	$sale = mysqli_fetch_assoc($res);

	$SQL = "SELECT brname FROM estimatecustbranch WHERE branchcode='" . $sale['branchcode'] . "'";
	$res = mysqli_query($db, $SQL);

	if (mysqli_num_rows($res) != 1) {
		echo json_encode([
			'status' => 'error2'
		]);
		return;
	}

	$sale['customer'] = mysqli_fetch_assoc($res);

	if ($sale['crname'] != "") {
		$sale['customer']['brname'] = html_entity_decode($sale['crname']);
	}

	$SQL = "SELECT * FROM estimateshopsalelines WHERE orderno='" . $sale['orderno'] . "'";
	$res = mysqli_query($db, $SQL);

	$sale['lines'] = [];

	while ($row = mysqli_fetch_assoc($res)) {
		$sale['lines'][] = $row;
	}
}

?>

<!DOCTYPE html>

<head>
	<title>Shop Sale Print</title>
	<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<style type="text/css">
		body {
			background: rgb(204, 204, 204);
		}

		page {
			background: white;
			display: block;
			margin: 0 auto;
			margin-bottom: 0.5cm;
			box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
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

		.watermark {
			position: absolute;
			background-image: url('../../../qrcodes/directDC/test.png');

		}

		.tdpa {
			padding: 5px;
		}

		.break {
			word-wrap: break-word;
			white-space: normal;
		}

		.parchino {
			display: none;
		}

		.nonprint {
			display: inline-block;
		}

		@media print {

			body,
			page {
				margin: 0;
				box-shadow: 0;
			}

			.vendor {
				display: none;
			}

			.parchino {
				display: block;
			}

			.nonprint {
				display: none;
			}
		}

		.header {
			margin-top: 15px;
			display: flex;
		}

		.header img {
			height: 100px;
			width: 100px;
		}

		.header .namedet {
			flex: 1;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
		}

		.serno {
			width: 100px;
			display: flex;
			justify-content: space-between;
			align-items: flex-end;
			flex-direction: column;
		}

		.serno h4 {
			margin: 0;
			margin-right: 2px;
		}

		.ddasda {
			top: -38px;
		}

		.abc {
			text-align: right;
			border-left: 1px solid white;
			border-bottom: 1px solid white;
			text-align: right !important;
		}
	</style>
</head>

<body>

	<page size="A4">
		<div>
			<button class="btn btn-primary hidden-print" onclick="window.print()" style="text-align: right !important; position: fixed; z-index: 200; right: 0; top:50px;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print</button>
			<button class="btn btn-primary hidden-print changeCompanyName" style="text-align: right !important; position: fixed; z-index: 200; right: 0"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Change CompanyName</button>
			<?php if ($sale['complete'] == 0) { ?>
				<a class="btn btn-primary hidden-print" href="editShopSale.php?orderno=<?php echo $sale['orderno']; ?>" style="text-align: right !important; position: fixed; z-index: 200; right: 0; top:100px;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Add Internal Item</a>
			<?php } ?>
		</div>
		<!--
		<div style="display: flex; position: absolute; width: 21cm">
			<h3 style="text-align: center; width: 21cm" class="companyname">SAH</h3>
		</div>
		<div class="col-md-12" style="padding-top: 10px; padding-bottom: 10px; border-bottom: 2px solid black; padding-left: 0px; padding-right: 0px;">
			<div class="col-md-6">
				<table style="position: absolute;">
					<tr>
						<td>
							<img src="../../qrcodes/shopsale/<?php echo $sale['orderno'] . '-shopSaleQR.png';  ?>" 
								 alt="QR Code" 
								 height="90px" 
								 style="display: inline-block;">
						</td>
						<td>
							<div style="text-align: left; vertical-align: top; display: block; position: absolute; left:2px; top: 83px; font-size: 10px;">
								<span style="font-weight: bold;">
									Dated: 
								</span>
								<?php echo date('d/m/Y', strtotime($sale['orddate'])); ?><br/>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="col-md-6" style="text-align: right; min-height: 55px">
				<h3><?php echo ($sale['payment'] == "csv") ? "Cash" : ""; ?> Bill</h3>
				<p class="">Customer: <?php echo $sale['customer']['brname']; ?></p>
			</div>
		</div>-->
		<div class="col-md-12 header">

			<div class="namedet">
				<h3 style="font-weight:bolder; border-bottom: 2px solid #424242; margin-bottom:0px;" class="companyname1">S.A. HAMID & Co.</h3>
				<h6 style="margin-bottom:0px;" class="companyaddress">7 Nishter Road, Lahore, Ph: 042-37650475, 37650099</h6>
				<h6 style="border-bottom: 1px dashed #424242; margin-bottom: 0;"><span style="font-variant-caps: petite-caps; font-weight: bold; font-size: 15px;">Customer:</span> <?php echo $sale['customer']['brname']; ?></h6>
				<h6 style="border-bottom: 1px dashed #424242; margin-bottom: 0;"><span style="font-variant-caps: petite-caps; font-weight: bold; font-size: 15px;">SalesPerson:</span> <?php echo $sale['salesman']; ?></h6>
				<h6 style="border-bottom: 1px dashed #424242; margin-bottom: 0;"><span style="font-variant-caps: petite-caps; font-weight: bold; font-size: 15px;">Created By:</span> <?php echo $sale['created_by']; ?></h6>
			</div>
			<div class="serno">
				<h6><?php echo date('d/m/Y', strtotime($sale['orddate'])); ?></h6>
				<h6>
					<?php
					if ($sale['payment'] == "csv") {
						echo "CS-" . $sale['orderno'];
					} elseif ($sale['payment'] == "estimate") {
						echo "ESTIMATE-" . $sale['orderno'];
					} else {
						echo "CR-" . $sale['orderno'];
					}
					?>
				</h6>
			</div>
		</div>
		<div class="col-md-12">
			<table border="1" style="margin-top: 15px; border-radius: 8px; width: 100%">
				<thead>
					<tr>
						<th colspan="7" style="text-align: center;" class="tdpa">Items</th>
					</tr>
					<tr style="font-size: 12px">
						<th class="tdpa" style="width: 1%; text-align: center;">Sr#</th>
						<th colspan="3" class="tdpa" style="min-width: 4cm; max-width: 4cm">Item Name</th>
						<th class="tdpa" style="max-width: 1cm; text-align: center;">Quantity</th>
						<th class="tdpa" style="max-width: 1cm; text-align: center;">Price</th>
						<th class="tdpa" style="max-width: 1cm; text-align: center;">Sub Total</th>
					</tr>
				</thead>
				<tbody>
					<?php $count = 1;
					$total = 0;
					foreach ($sale['lines'] as $item) { ?>

						<tr style="font-size: 11px">
							<td class="tdpa" style="text-align: center;"><?php echo $count; ?></td>
							<td colspan="3" class="break tdpa" style="min-width: 4cm; max-width: 4cm">
								<?php echo html_entity_decode($item['description']); ?>
							</td>
							<td style="text-align: center;">
								<?php echo $item['quantity']; ?>
								<sub><?php echo $item['uom']; ?></sub>
							</td>
							<td style="text-align: center;"><?php echo locale_number_format($item['price'], 2); ?></td>
							<td style="text-align: center;">
								<?php echo locale_number_format(($item['quantity'] * $item['price']), 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>

						<?php

						if (isset($_GET['internal'])) {

							$SQL = "SELECT stockmaster.mnfpno,manufacturers.manufacturers_name as bname, stockmaster.description,
									stockmaster.mnfCode, stockmaster.stockid,shopsalesitems.quantity,shopsalesitems.discountpercent
									FROM shopsalesitems 
									LEFT OUTER JOIN stockmaster ON shopsalesitems.stockid = stockmaster.stockid
									LEFT OUTER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
									WHERE shopsalesitems.orderno=$orderno
									AND lineno='" . $item['id'] . "'";
							$res = mysqli_query($db, $SQL);

							if (mysqli_num_rows($res) > 0) {

						?>

								<tr style="font-size: 11px">
									<th style="text-align: center;">QTY</th>
									<th colspan="2" class="tdpa" style="text-align: center;">StockID</th>
									<th colspan="2" class="tdpa" style="text-align: center;">Discount</th>
									<th class="tdpa" style="text-align: center;">MNFP NO</th>
									<th colspan="3" style="text-align: center;" class="tdpa">Short Description</th>
								</tr>

								<?php
								while ($row = mysqli_fetch_assoc($res)) {
								?>

									<tr style="font-size: 11px">
										<td style="text-align: center;"><?php echo $row['quantity']; ?></td>
										<td colspan="2" class="tdpa" style="text-align: center;"><?php echo $row['stockid']; ?></td>
										<td colspan="2" class="tdpa" style="text-align: center;"><?php echo $row['discountpercent']; ?></td>
										<td class="tdpa" style="text-align: center;"><?php echo $row['mnfpno']; ?></td>
										<td colspan="3" style="text-align: center;" class="tdpa"><?php echo $row['description']; ?></td>
									</tr>

						<?php
								}
							}
						}
						?>
					<?php $total += ($item['quantity'] * $item['price']);
						$count++;
					} ?>
					<?php if ($sale['discount'] != 0 || $sale['advance'] != 0) { ?>
						<tr style="font-size: 11px">
							<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
							<th class="tdpa abc" style="text-align: center;">SubTotal: </th>
							<td colspan="2" class="tdpa" style="text-align: center;">
								<?php echo locale_number_format($total, 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>
					<?php } ?>
					<?php if ($sale['discount'] != 0 || $sale['discountPKR']) { ?>
						<?php if ($sale['discount'] != 0) { ?>
							<tr style="font-size: 11px">
								<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
								<th class="tdpa abc" style="text-align: center;">Discount<sub>%</sub>: </th>
								<td colspan="2" class="tdpa" style="text-align: center;">
									<?php echo locale_number_format($sale['discount'], 2); ?>
									<sub>%</sub>
								</td>
							</tr>
						<?php } ?>
						<?php if ($sale['discountPKR'] != 0) { ?>
							<tr style="font-size: 11px">
								<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
								<th class="tdpa abc" style="text-align: center;">Discount<sub>PKR</sub>: </th>
								<td colspan="2" class="tdpa" style="text-align: center;">
									<?php echo locale_number_format($sale['discountPKR'], 2); ?>
									<sub>PKR</sub>
								</td>
							</tr>
						<?php } ?>
						<tr style="font-size: 11px">
							<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
							<th class="tdpa abc" style="text-align: center;"><?php if ($sale['payment'] == "csv") echo "Grand "; ?>Total: </th>
							<td colspan="2" class="tdpa" style="text-align: center;">
								<?php echo locale_number_format(($total * (1 - ($sale['discount'] / 100)) - $sale['discountPKR']), 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>
					<?php } else if ($sale['discount'] == 0 && $sale['discountPKR'] == 0 && $sale['payment'] == "csv") { ?>
						<tr style="font-size: 11px">
							<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
							<th class="tdpa abc" style="text-align: center;">Grand Total: </th>
							<td colspan="2" class="tdpa" style="text-align: center;">
								<?php echo locale_number_format(($total * (1 - ($sale['discount'] / 100))), 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>
					<?php } ?>
					<?php if ($sale['advance'] != 0 && $sale['payment'] == "crv") { ?>
						<tr style="font-size: 11px">
							<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
							<th class="tdpa abc" style="text-align: center;">Advance: </th>
							<td colspan="2" class="tdpa" style="text-align: center;">
								<?php echo locale_number_format($sale['advance'], 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>
					<?php } ?>
					<?php if ($sale['payment'] == "crv") { ?>
						<tr style="font-size: 11px">
							<th colspan="5" class="tdpa abc" style="text-align: right;"></th>
							<th class="tdpa abc" style="text-align: center;">Remaining Balance: </th>
							<td colspan="2" class="tdpa" style="text-align: center;">
								<?php echo locale_number_format(($total * (1 - ($sale['discount'] / 100))) - $sale['advance'] - $sale['discountPKR'], 2); ?>
								<sub>PKR</sub>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class="col-md-12" style="margin-top: 40px; padding-left:0px">
				<div class="col-md-12" style="border: 0px solid black; margin-top: -27px; height: 40px;">
					<div style="font-size: 12px; position: absolute; left: 2px; font-weight: bold;">Terms & Conditions :</div>
					<div style="font-size: 12px; position: absolute; top: 16px;">
						<p style="margin-bottom: 0px"><sup>1. </sup>We do not undertake any risk of breakage or loss of goods in transit when once the delivery has been effected.</p>
						<p style="margin-bottom: 0px"><sup>2. </sup>This is a computer generated bill and does not require any signature or stamp.</p>
					</div>
				</div>
				<div class="col-md-6 ddasda " style="text-align: right; position: absolute; right: 0; padding-right: 0; display:none;">
					<img src="../../qrcodes/shopsale/<?php echo $sale['orderno'] . '-shopSaleQR.png';  ?>"
						alt="QR Code"
						height="90px"
						style="display: inline-block;">
				</div>
			</div>
			<div class="col-md-12" style="">
				<div class="col-md-6"></div>
				<div class="col-md-6"></div>
			</div>
			<div style="clear: both;"></div>
			<div class="row" style="height: 1px; width: calc(100% + 30px); border-bottom: 1px dotted black; margin-top: 40px">
			</div>
			<!--<h5 style="padding:4px 0; margin:0 -15px; text-align: center; border-bottom: 1px dotted black;">Powered By: 
				<img src="../../reports/balance/logo.png" height="15px" width="15px"> 
				Compresol
			</h5>-->
		</div>
	</page>
	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>

	<script type="text/javascript">
		// $(window).keydown(function (e){
		//     if (e.ctrlKey || e.metaKey) {
		//     	e.preventDefault();
		//     };
		// });

		$(".changeCompanyName").on("click", function() {
			if ($(".companyname1").html() == "SAH") {
				$(".companyname1").html("S.A HAMID & Co.");
				$(".companyaddress").html("7 Nishter Road, Lahore, Ph: 042-37650475, 37650099");
				$(".hidethisshit").css("visibility", "visible");
			} else {
				$(".companyname1").html("SAH");
				$(".companyaddress").html("");
				$(".hidethisshit").css("visibility", "hidden");
			}
		});
	</script>
</body>

</html>