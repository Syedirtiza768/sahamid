<?php

$PathPrefix = "../../";
include("../../includes/session.inc");
include('../../includes/SQL_CommonFunctions.inc');

$orderno = trim($_GET['orderno']);

if (isset($_GET['json'])) {

	$SQL = "SELECT * FROM shopsale WHERE orderno='" . $orderno . "'";
	$res = mysqli_query($db, $SQL);

	if (mysqli_num_rows($res) == 1) {

		$sale = mysqli_fetch_assoc($res);

		$orignal = false;
		if (isset($_GET['orignal'])) {
			$orignal = true;
		}

		$SQL = "SELECT brname, braddress1, braddress2, braddress3, braddress4, braddress5, braddress6
				FROM custbranch WHERE branchcode='" . $sale['branchcode'] . "'";
		$res = mysqli_query($db, $SQL);

		if (mysqli_num_rows($res) != 1) {
			echo json_encode([
				'status' => 'error2'
			]);
			return;
		}

		$sale['customer'] = mysqli_fetch_assoc($res);
		$details = "";

		if ($sale['crname'] != "") {
			$sale['customer']['brname'] = html_entity_decode($sale['crname']);
		} else {

			if (trim($sale['customer']['braddress1']) != "") {
				$details = $sale['customer']['braddress1'] . ",<br>";
			}

			if (trim($sale['customer']['braddress2']) != "") {
				$details .= $sale['customer']['braddress2'] . ",<br>";
			}

			if (trim($sale['customer']['braddress3']) != "") {
				$details .= $sale['customer']['braddress3'] . ",";
			}

			if (trim($sale['customer']['braddress4']) != "") {
				$details .= $sale['customer']['braddress4'] . ",<br>";
			}

			if (trim($sale['customer']['braddress5']) != "") {
				$details .= $sale['customer']['braddress5'] . "";
			}
		}

		$SQL = "SELECT * FROM shopsalelines WHERE orderno='" . $sale['orderno'] . "'";
		$res = mysqli_query($db, $SQL);

		$sale['lines'] = [];

		while ($row = mysqli_fetch_assoc($res)) {
			$row['description'] = str_replace("<br>", ", ", html_entity_decode($row['description']));
			$sale['lines'][] = $row;
		}

		$sale['invoice'] = (($sale['payment'] == "csv") ? ("CS-") : ("CR-")) . sprintf("%'.04d\n", $sale['orderno']);

		$sale['header'] = [];
		$sale['header']['orignal'] = $orignal ? "<b>Orignal<b/>" : "<b>Duplicate</b>";
		$sale['header']['customer'] = $sale['customer']['brname'];
		$sale['header']['cdetails'] = (($sale['payment'] == "crv") ? $details : "");
		$sale['header']['payment']  = $sale['payment'];
		$sale['header']['invoice']  = (($sale['payment'] == "csv") ? ("CS-") : ("CR-")) . sprintf("%'.04d\n", $sale['orderno']);
		$sale['header']['date']		= date('d/m/Y', strtotime($sale['orddate']));
		$sale['header']['salesman'] = $sale['salesman'];
		$sale['header']['customerref'] 		= $sale['customerref'] ?: "";
		$sale['header']['dispatchedvia'] 	= $sale['dispatchedvia'] ?: "";

		$sale['creditLimit'] = getCreditLimit($db, $sale['debtorno']);
		$sale['currentCredit'] = getCurrentCredit($db, $sale['debtorno']);

		echo json_encode($sale);
		return;
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

		$orignal = false;
		if (isset($_GET['orignal'])) {
			$orignal = true;
		}

		$SQL = "SELECT brname, braddress1, braddress2, braddress3, braddress4, braddress5, braddress6, phoneno
				FROM estimatecustbranch WHERE branchcode='" . $sale['branchcode'] . "'";
		$res = mysqli_query($db, $SQL);

		if (mysqli_num_rows($res) != 1) {
			echo json_encode([
				'status' => 'error2'
			]);
			return;
		}

		$sale['customer'] = mysqli_fetch_assoc($res);
		$details = "";

		if ($sale['crname'] != "") {
			$sale['customer']['brname'] = html_entity_decode($sale['crname']);
		} else {

			if (trim($sale['customer']['braddress1']) != "") {
				$details = $sale['customer']['braddress1'] . ",<br>";
			}

			if (trim($sale['customer']['braddress2']) != "") {
				$details .= $sale['customer']['braddress2'] . ",<br>";
			}

			if (trim($sale['customer']['braddress3']) != "") {
				$details .= $sale['customer']['braddress3'] . ",";
			}

			if (trim($sale['customer']['braddress4']) != "") {
				$details .= $sale['customer']['braddress4'] . ",<br>";
			}

			if (trim($sale['customer']['braddress5']) != "") {
				$details .= $sale['customer']['braddress5'] . "";
			}
		}

		$SQL = "SELECT * FROM estimatedebtormaster WHERE debtorno='" . $sale['debtorno'] . "'";
		$res = mysqli_query($db, $SQL);

		$sale['dba'] = [];

		while ($row = mysqli_fetch_assoc($res)) {

			$sale['dba'] = $row['dba'];
		}

		$SQL = "SELECT * FROM estimateshopsalelines WHERE orderno='" . $sale['orderno'] . "'";
		$res = mysqli_query($db, $SQL);

		$sale['lines'] = [];

		while ($row = mysqli_fetch_assoc($res)) {
			$row['description'] = str_replace("<br>", ", ", html_entity_decode($row['description']));
			$sale['lines'][] = $row;
		}

		$sale['invoice'] = (($sale['payment'] == "estimate") ? ("ESTIMATE-") : ("ESTIMATE-")) . sprintf("%'.04d\n", $sale['id']);

		$sale['header'] = [];
		$sale['header']['orignal'] = $orignal ? "<b>Orignal<b/>" : "<b>Duplicate</b>";
		$sale['header']['customer'] = $sale['customer']['brname'];
		$sale['header']['details'] = $sale['customer']['braddress1'];
		$sale['header']['phone'] = $sale['customer']['phoneno'];
		$sale['header']['cdetails'] = (($sale['payment'] == "csv") ? $details : "");
		$sale['header']['payment']  = $sale['payment'];
		$sale['header']['invoice']  = (($sale['payment'] == "estimate") ? ("ESTIMATE-") : ("ESTIMATE-")) . sprintf("%'.04d\n", $sale['id']);
		$sale['header']['date']		= date('d/m/Y', strtotime($sale['orddate']));
		$sale['header']['salesman'] = $sale['salesman'];
		$sale['header']['dba'] = $sale['dba'];
		$sale['header']['customerref'] 		= $sale['customerref'] ?: "";
		$sale['header']['dispatchedvia'] 	= $sale['dispatchedvia'] ?: "";
		$sale['header']['advance'] 	= $sale['advance'] ?: "";
		$sale['header']['ondelivery'] 	= $sale['ondelivery'] ?: "";
		$sale['header']['commision'] 	= $sale['commision'] ?: "";
		$sale['header']['paymentin'] 	= $sale['paymentin'] ?: "";
		$sale['header']['expectedin'] 	= $sale['expectedin'] ?: "";


		$sale['creditLimit'] = getCreditLimit($db, $sale['debtorno']);
		$sale['currentCredit'] = getCurrentCredit($db, $sale['debtorno']);

		echo json_encode($sale);
		return;
	}
}
?>
<?php
$SQL = "SELECT * FROM estimateshopsale WHERE orderno='" . $orderno . "'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) == 1) {
?>
	<!DOCTYPE html>
	<html>

	<head>
		<title>Shop Sale Print</title>
		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<style type="text/css">
			body {
				background: rgb(204, 204, 204);
				font-family: 'Arial', sans-serif;
				font-size: 12px;
				color: #333;
				margin: 0;
				padding: 0;
				display: flex;
				justify-content: center;
				align-items: flex-start;
				min-height: 100vh;
			}

			.pagesContainer {
				display: flex;
				flex-direction: column;
				align-items: center;
				width: 100%;
			}

			page {
				background: white;
				display: block;
				margin: 0 auto;
				padding: 25px 30px;
				box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
				position: relative;
				width: 14.8cm;
				height: 21cm;
			}

			/* Center content within the page */
			.page-content {
				width: 100%;
				max-width: 13.8cm;
				margin: 0 auto;
			}

			/* Header */
			.pageHeader {
				text-align: center;
				padding-bottom: 10px;
				margin-bottom: 15px;
				border-bottom: 2px solid #2c3e50;
			}

			.pageHeader .logo img {
				height: 70px;
				width: auto;
				max-width: 200px;
				object-fit: contain;
			}

			/* Sales Info */
			.sales-info {
				width: 100%;
				border-collapse: collapse;
				margin: 10px 0 15px 0;
				font-size: 11px;
			}

			.sales-info th {
				background: #2c3e50;
				color: white;
				padding: 6px 8px;
				text-align: center;
				border: 1px solid #2c3e50;
				font-weight: bold;
			}

			.sales-info td {
				padding: 6px 8px;
				border: 1px solid #bdc3c7;
				text-align: center;
				background: #f8f9fa;
			}

			/* Items Table */
			.items-table {
				width: 100%;
				border-collapse: collapse;
				margin: 10px 0;
				font-size: 10px;
				table-layout: fixed;
			}

			.items-table th {
				background: #34495e;
				color: white;
				padding: 6px 4px;
				border: 1px solid #2c3e50;
				font-weight: bold;
				text-align: center;
			}

			.items-table td {
				padding: 6px 4px;
				border: 1px solid #ddd;
				text-align: center;
				vertical-align: top;
			}

			.items-table .description {
				text-align: left;
				padding-left: 6px;
			}

			.delivery-status {
				background: #f8f9fa;
				padding: 4px 6px;
				font-size: 9px;
				text-align: left;
				border: 1px solid #e9ecef;
				border-top: none;
				color: #666;
				font-style: italic;
			}

			.delivery-status span {
				font-weight: bold;
				color: #e74c3c;
			}

			/* Totals Section */
			.totals-section {
				margin: 15px 0;
				border-top: 2px solid #2c3e50;
				padding-top: 8px;
			}

			.total-row {
				display: flex;
				justify-content: space-between;
				padding: 4px 0;
				border-bottom: 1px solid #ecf0f1;
				font-size: 11px;
			}

			.total-label {
				font-weight: 600;
				color: #2c3e50;
			}

			.total-value {
				font-weight: bold;
				color: #2c3e50;
			}

			.grand-total {
				border-top: 2px solid #2c3e50;
				margin-top: 5px;
				padding-top: 5px;
				font-size: 12px;
				font-weight: bold;
				color: #e74c3c;
			}

			/* Payment Terms */
			.payment-terms {
				background: #f8f9fa;
				border: 1px solid #bdc3c7;
				border-radius: 4px;
				padding: 10px 12px;
				margin: 12px 0;
				font-size: 10px;
			}

			.payment-terms strong {
				color: #2c3e50;
				display: block;
				margin-bottom: 6px;
				font-size: 11px;
				border-bottom: 1px solid #bdc3c7;
				padding-bottom: 3px;
			}

			.payment-term-item {
				display: flex;
				justify-content: space-between;
				padding: 2px 0;
				border-bottom: 1px dotted #ddd;
			}

			.payment-term-item:last-child {
				border-bottom: none;
			}

			/* Footer */
			.footer {
				margin-top: 20px;
				border-top: 2px solid #2c3e50;
				padding-top: 8px;
			}

			.footer-top {
				text-align: center;
				padding: 6px 0;
				font-style: italic;
				color: #7f8c8d;
				font-size: 11px;
				font-weight: 600;
			}

			.footer-bottom {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding-top: 6px;
				border-top: 1px solid #bdc3c7;
				font-size: 9px;
				color: #7f8c8d;
			}

			.invoice-number {
				font-weight: bold;
				color: #2c3e50;
				font-size: 10px;
			}

			.page-number {
				font-weight: 600;
			}

			.document-type {
				font-weight: bold;
				color: #e74c3c;
			}

			/* Print Controls */
			.print-controls {
				position: fixed;
				top: 20px;
				right: 20px;
				z-index: 1000;
				display: flex;
				flex-direction: column;
				gap: 8px;
			}

			.print-controls .btn {
				box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
				font-size: 11px;
				padding: 5px 10px;
			}

			/* Utility Classes */
			.text-right {
				text-align: right;
			}

			.text-center {
				text-align: center;
			}

			.text-left {
				text-align: left;
			}

			.bold {
				font-weight: bold;
			}

			.hidden {
				display: none;
			}

			/* Print Styles - Centered on page */
			@media print {
				body {
					background: none;
					margin: 0 !important;
					padding: 0 !important;
					display: block !important;
					width: 100% !important;
				}

				.pagesContainer {
					display: block !important;
					width: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
				}

				page {
					margin: 0 auto !important;
					padding: 25px 30px !important;
					box-shadow: none !important;
					page-break-after: always;
					width: 14.8cm !important;
					height: 21cm !important;
					position: relative !important;
					left: 0 !important;
					right: 0 !important;
				}

				page:last-child {
					page-break-after: auto;
				}

				.nonprint,
				.hidden-print {
					display: none !important;
				}

				/* Ensure content is centered in print */
				.page-content {
					margin: 0 auto !important;
					width: 100% !important;
					max-width: 13.8cm !important;
				}
			}

			/* Column Widths */
			.col-sr {
				width: 6%;
			}

			.col-desc {
				width: 38%;
			}

			.col-qty {
				width: 10%;
			}

			.col-unit {
				width: 10%;
			}

			.col-price {
				width: 14%;
			}

			.col-total {
				width: 12%;
			}
		</style>
	</head>

	<body>
		<div class="print-controls nonprint">
			<button class="btn btn-primary hidden-print" onclick="window.print()">
				<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print
			</button>
			<!-- <button class="btn btn-primary hidden-print changeCompanyName">
            <span class="glyphicon glyphicon-print" aria-hidden="true"></span> Change CompanyName
        </button> -->
		</div>

		<div class="pagesContainer"></div>

		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				window.paymentType = "";
				let pageCount = 1;

				$.get("shopSalePrint.php?orderno=<?php echo $_GET['orderno']; ?>&json<?php echo (isset($_GET['orignal']) ? "&orignal" : ""); ?>", function(res, status) {
					try {
						res = JSON.parse(res);
						console.log('Sale Data:', res);

						let currentPage = "page-" + pageCount;
						$(".pagesContainer").append(createPage(currentPage));

						window.paymentType = res.header.payment;
						$("." + currentPage).append(createHeader(res.header, true));
						$("." + currentPage).append(createItemsTable());

						let sr = 1;
						let totalValue = 0;

						// Add items to table
						res.lines.forEach(function(item) {
							const itemTotal = item.quantity * item.price;
							totalValue += itemTotal;

							$("." + currentPage + " .items-table tbody")
								.append(createTableRow(sr, item.description, item.quantity, item.uom, item.price, item.deliveryStatus, itemTotal));

							sr++;
						});

						// Add totals and payment terms
						$("." + currentPage).append(createTotalsSection(totalValue, res));
						$("." + currentPage).append(createFooter(res.invoice, pageCount));

					} catch (error) {
						console.error('Error processing data:', error);
					}
				});

				function createPage(className) {
					return `
                    <page size="A5" class="${className}">
                        <div class="page-content">
                            <!-- Content will be inserted here -->
                        </div>
                    </page>`;
				}

				function createHeader(options, firstPage = false) {
					if (firstPage) {
						return `
                        <div class="header">
                            <div class="pageHeader">
                                <div class="logo">
								${(options.dba === 'SA HAMID AND COMPANY') ? 
                    				`<img src="../../companies/sahamid/EDI_Incoming_Orders/companylogos/SA HAMID AND COMPANY.jpg" 
                         			alt="SA HAMID AND COMPANY" 
                         			style="height: 90px;"
                         			class="${(window.paymentType == 'csv') ? 'hidden' : ''} sahname">` 
                    			: ''}
								${(options.dba === 'CESCO INTERNATIONAL') ? 
                    				`<img src="../../companies/sahamid/EDI_Incoming_Orders/companylogos/CESCO INTERNATIONAL.jpg" 
                         			alt="CESCO INTERNATIONAL" 
                         			style="height: 70px;"
                         			class="${(window.paymentType == 'csv') ? 'hidden' : ''} sahname">` 
                    			: ''}
								${(options.dba === 'SAH') ? 
                    				`<img src="../../companies/sahamid/EDI_Incoming_Orders/companylogos/SAH.jpg" 
                         			alt="CESCO INTERNATIONAL" 
                         			style="height: 70px;"
                         			class="${(window.paymentType == 'csv') ? 'hidden' : ''} sahname">` 
                    			: ''}
                                </div>
                            </div>
                            
                            <table class="sales-info">
                                <tr>
                                    <th style="width: 50%;">Salesperson</th>
                                    <th style="width: 50%;">Customer Reference</th>
                                </tr>
                                <tr>
                                    <td>${options.salesman || 'N/A'}</td>
                                    <td>${options.customerref || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                    `;
					}

					return `
                    <div class="header">
                        <div class="pageHeader">
                            <div style="font-size: 11px; color: #7f8c8d;">Page Continuation</div>
                        </div>
                    </div>
                `;
				}

				function createItemsTable() {
					return `
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th class="col-sr">SR #</th>
                                <th class="col-desc">DESCRIPTION</th>
                                <th class="col-qty">QUANTITY</th>
                                <th class="col-unit">UNIT</th>
                                <th class="col-price">UNIT PRICE</th>
                                <th class="col-total">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items will be inserted here -->
                        </tbody>
                    </table>
                `;
				}

				function createTableRow(sr, desc, qty, unit, unitPrice, deliveryStatus, total) {
					return `
                    <tr>
                        <td class="col-sr">${sr}.</td>
                        <td class="col-desc description">${desc}</td>
                        <td class="col-qty">${numberWithCommas(qty)}</td>
                        <td class="col-unit">${unit}</td>
                        <td class="col-price">${numberWithCommas(unitPrice)}</td>
                        <td class="col-total bold">${numberWithCommas(total)}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="delivery-status">
                            <strong>Delivery Status:</strong> <span>${deliveryStatus || 'Not Specified'}</span>
                        </td>
                    </tr>
                `;
				}

				function createTotalsSection(totalValue, res) {
					// Calculate discounts properly
					const discountAmount = totalValue * (parseFloat(res.discount || 0) / 100);
					const discountPKR = parseFloat(res.discountPKR || 0);
					const finalTotal = totalValue - discountAmount - discountPKR;

					let html = `
                    <div class="totals-section">
                        <div class="total-row">
                            <span class="total-label">Sub Total:</span>
                            <span class="total-value">${numberWithCommas(totalValue.toFixed(2))} PKR</span>
                        </div>
                `;

					// Add discounts if applicable
					if (parseFloat(res.discount) > 0) {
						html += `
                        <div class="total-row">
                            <span class="total-label">Discount (${res.discount}%):</span>
                            <span class="total-value">- ${numberWithCommas(discountAmount.toFixed(2))} PKR</span>
                        </div>
                    `;
					}

					if (parseFloat(res.discountPKR) > 0) {
						html += `
                        <div class="total-row">
                            <span class="total-label">Discount (PKR):</span>
                            <span class="total-value">- ${numberWithCommas(discountPKR.toFixed(2))} PKR</span>
                        </div>
                    `;
					}

					html += `
                        <div class="total-row grand-total">
                            <span class="total-label">GRAND TOTAL:</span>
                            <span class="total-value">${numberWithCommas(finalTotal.toFixed(2))} PKR</span>
                        </div>
                    </div>
                `;

					// Add payment terms for estimates
					if (res.header.payment === 'estimate') {
						html += createPaymentTerms(res);
					}

					return html;
				}

				function createPaymentTerms(res) {
					return `
                    <div class="payment-terms">
                        <strong>PAYMENT TERMS & CONDITIONS</strong>
                        <div class="payment-term-item">
                            <span>Advance Payment:</span>
                            <span class="bold">${res.header.advance || 0}%</span>
                        </div>
                        <div class="payment-term-item">
                            <span>On Delivery:</span>
                            <span class="bold">${res.header.ondelivery || 0}%</span>
                        </div>
                        <div class="payment-term-item">
                            <span>Commission:</span>
                            <span class="bold">${res.header.commision || 0}%</span>
                        </div>
                        <div class="payment-term-item">
                            <span>Payment Terms:</span>
                            <span class="bold">${res.header.paymentin || 'N/A'} Days</span>
                        </div>
                        <div class="payment-term-item">
                            <span>Expected Delivery:</span>
                            <span class="bold">${res.header.expectedin || 'N/A'} Days</span>
                        </div>
                    </div>
                `;
				}

				function createFooter(invoiceNumber, pageNumber) {
					return `
                    <div class="footer">
                        <div class="footer-top">
                            We look forward to doing business with you!
                        </div>
                        <div class="footer-bottom">
                            <div class="document-type">ESTIMATE</div>
                            <div class="invoice-number">${invoiceNumber}</div>
                            <div class="page-number">Page ${pageNumber}</div>
                        </div>
                    </div>
                `;
				}

				// Company name toggle functionality
				$(".changeCompanyName").on("click", function() {
					$("page").each(function() {
						let logo = $(this).find(".logo");
						if (window.paymentType == "cash") {
							if (logo.find("img").hasClass("hidden")) {
								logo.find("img").removeClass("hidden");
							} else {
								logo.find("img").addClass("hidden");
							}
						}
					});
				});

				// Utility function for number formatting
				const numberWithCommas = (x) => {
					if (!x) return '0';
					let parts = parseFloat(x).toFixed(2).toString().split(".");
					parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					return parts.join(".");
				}
			});
		</script>
	</body>

	</html>
<?php
} else { ?>
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
				height: 20.4cm;
			}

			page[size="A5"][layout="portrait"] {
				width: 21cm;
				height: 14.8cm;
			}

			page {
				padding: 10px 30px;
			}

			.nonprint {
				display: inline-block;
			}

			.pageHeader {
				display: flex;
			}

			.pageHeader .logo {
				flex: 1;
			}

			.pageHeader .logo img {
				height: 45px;
			}

			.pageHeader .title {
				display: flex;
				justify-content: flex-end;
				font-size: 2em;
				font-weight: bolder;
				color: #9a9a9a;
			}

			.details {
				display: flex;
				font-size: 12px;
			}



			.customerDetails {
				padding-top: 30px;
				flex: 1;
			}

			.customerDetails .to {
				font-weight: bolder;
				font-weight: bolder;
				font-size: 12px;
			}

			.details .invoice {
				display: block;
			}

			.invnodate {
				flex: 1;
				font-size: 12px;
				display: flex;
				flex-direction: column;
				align-items: flex-end;
			}

			.details2 {
				padding-top: 10px;
				font-size: 12px;
			}

			.details2 table {
				width: 100%;
			}

			.details2 table th,
			.details2 table td {
				text-align: center;
			}

			.itemsHeader {
				margin-top: 10px;
				padding: 0;
				width: 100%;
				margin-bottom: 0;
				border: 1px solid black;
				font-size: 12px;
				display: flex;
			}

			.itemsHeader li {
				padding: 5px;
				font-weight: bold;
				display: inline-block;
				border-right: 1px solid black;
			}

			.itemsContainer {
				width: 100%;
			}

			.item {
				padding: 0;
				width: 100%;
				margin-bottom: 0;
				border: 1px solid black;
				border-top: 0px;
				font-size: 12px;
				display: flex;
				min-height: 30px;
			}

			.item li {
				padding: 3px;
				display: inline-block;
				border-right: 1px solid black;
			}

			.sr {
				width: 8%;
				min-width: 8%;
				max-width: 8%;
				text-align: center;
			}

			.description {
				width: 47%;
				min-width: 47%;
				max-width: 47%;
				text-align: center;
			}

			.qty {
				width: 8%;
				min-width: 8%;
				max-width: 8%;
				text-align: center;
				display: flex !important;
				flex-direction: column;
			}

			.unitprice {
				width: 17%;
				min-width: 17%;
				max-width: 17%;
				text-align: center;
			}

			.total {
				width: 17%;
				min-width: 17%;
				max-width: 17%;
				text-align: center;
				border-right: 0px !important;
			}

			.footer {
				width: 100%;
				display: flex;
				margin-top: 10px;
				font-size: 11px;
				flex-direction: column;
				justify-content: space-between;
			}

			.footertop div {
				width: 100%;
				text-align: center;
			}

			.asdas {
				width: 100%;
				text-align: center;
				font-size: 15px;
				font-weight: bolder;
			}

			.hidden {
				display: none;
			}

			.footerbottom {
				display: flex;
				height: 30px;
				width: 100%;
				border-top: 1px solid black;
				justify-content: space-between;
			}

			.footer .orderNo {

				font-size: 6;
			}

			.blankSpace {
				width: 100%;
			}

			.totalArea {
				width: 100%;
			}

			.totalItem {
				padding: 0;
				width: 100%;
				margin-bottom: 0;
				border: 1px solid black;
				border-top: 0px;
				font-size: 12px;
				border-bottom: 1px solid #424242;
				border-left: 0px;
				position: relative;
			}

			.totalItem li {
				padding: 3px;
				display: inline-block;
				border-right: 1px solid black;
			}

			.totalItem .total {
				//border-bottom: 1px solid black;
			}

			.totalTitle {
				width: 80%;
				font-weight: bolder;
				text-align: right;
			}

			.totalTitle:after {
				content: '';
				width: 80%;
				border-bottom: 1px solid white;
				height: 30px;
				position: absolute;
				left: -1px;
				top: -5px;
			}

			.sahname {
				font-size: 2em;
				font-weight: bolder;
			}

			@media print {

				.nonprint,
				.hidden-print {
					display: none;
				}
			}
		</style>
	</head>

	<body>
		<button class="btn btn-primary hidden-print" onclick="window.print()" style="text-align: right !important; position: fixed; z-index: 200; right: 0; top:50px;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print</button>
		<button class="btn btn-primary hidden-print changeCompanyName" style="text-align: right !important; position: fixed; z-index: 200; right: 0"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Change CompanyName</button>

		<div class="pagesContainer"></div>
		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				window.paymentType = "";
				let pageCount = 1;
				let margins = 20;

				$.get("shopSalePrint.php?orderno=<?php echo $_GET['orderno']; ?>&json<?php echo (isset($_GET['orignal']) ? "&orignal" : ""); ?>", function(res, status) {

					res = JSON.parse(res);

					let currentPage = "page-" + pageCount;

					$(".pagesContainer").append(page(currentPage));

					window.paymentType = res.header.payment;
					$("." + currentPage).append(header(res.header, true));
					$("." + currentPage).append(itemsHeader());
					$("." + currentPage).append(itemsContainer());
					$("." + currentPage).append(footer(res.invoice, pageCount));

					let sr = 1;
					let totalValue = 0;
					res.lines.forEach(function(i) {

						$("." + currentPage + " .itemsContainer")
							.append(item(sr, i.description, i.quantity, i.uom, i.price, i.quantity * i.price));

						totalValue += (i.quantity * i.price);

						let height = $("." + currentPage).height();
						height -= $("." + currentPage + " .header").height();
						height -= $("." + currentPage + " .itemsHeader").height();
						height -= $("." + currentPage + " .itemsContainer").height();
						height -= $("." + currentPage + " .footer").height();
						height -= margins;

						if (height <= 15) {
							pageCount += 1;
							currentPage = "page-" + pageCount;
							$(".pagesContainer").append(page(currentPage));
							$("." + currentPage).append(header(res.header));
							$("." + currentPage).append(itemsHeader());
							$("." + currentPage).append(itemsContainer());
							$("." + currentPage).append(footer(res.invoice, pageCount));
						}

						sr++;

					});

					$("." + currentPage + " .itemsContainer").after(totalSection(totalValue, res.discount, res.discountPKR, res.advance, res.payment, res.paid, res.creditLimit, res.currentCredit));

					let height = $("." + currentPage).height();
					height -= $("." + currentPage + " .header").height();
					height -= $("." + currentPage + " .itemsHeader").height();
					height -= $("." + currentPage + " .itemsContainer").height();
					height -= $("." + currentPage + " .totalArea").height();
					height -= $("." + currentPage + " .footer").height();
					height -= margins;

					if (height < 0) {

						$("." + currentPage + " .totalArea").remove();

						pageCount += 1;
						currentPage = "page-" + pageCount;
						$(".pagesContainer").append(page(currentPage));
						$("." + currentPage).append(header(res.header));
						//$("."+currentPage).append(itemsHeader());
						$("." + currentPage).append(itemsContainer());
						$("." + currentPage + " .itemsContainer").after(totalSection(totalValue, res.discount, res.discountPKR, res.advance, res.payment, res.paid, res.creditLimit, res.currentCredit));
						$("." + currentPage).append(footer(res.invoice, pageCount));

					}

					$("." + currentPage + " .footer .footertop").css("display", "block");

					height = $("." + currentPage).height();
					height -= $("." + currentPage + " .header").height();
					height -= $("." + currentPage + " .itemsHeader").height();
					height -= $("." + currentPage + " .itemsContainer").height();
					height -= $("." + currentPage + " .totalArea").height();
					height -= $("." + currentPage + " .footer").height();
					height -= margins;

					$("." + currentPage + " .totalArea").after(`<div class="blankSpace" style="height: ${height}px"></div>`);


				});


			});

			function page(currentPage) {
				return `<page size="A5" class="${currentPage}"></page>`;
			}

			function header(options, firstPage = false) {

				let first = `
				<div class="pageHeader">
					<div class="logo"><span class="${(window.paymentType == "csv") ? "hidden":""} sahname"></span></div>
					<span class="title">${(options['payment'] == "csv") ? "":""}</span>
				</div>
				<div class="details">
					<div class="customerDetails">
						<div class="to">For:</div>
						<div class="info">${options['customer']}<br>${options['cdetails']}</div>
					</div>
					<div class="invnodate">
						<div class="invoice">${options['invoice']}</div>
						<div class="datetime">Date: ${options['date']}</div>
					</div>
				</div>
				<div class="details2">
					<table border="1">
						<thead>
							<tr>
								<th>Salesperson</th>
								<th>CustomerRef</th>
								<th>Dispatched Via</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>${options['salesman']}</td>
								<td>${options['customerref']}</td>
								<td>${options['dispatchedvia']}</td>
							</tr>
						</tbody>
					</table>
				</div>
			`;

				let other = `
				<div class="pageHeader">
					<div class="logo">
						<img src="logo.png" class="${(window.paymentType == "crv") ? "hidden":""}">
						<span class="sahname ${(window.paymentType == "csv") ? "hidden":""}"></span>
					</div>
					<span class="title">${(options['payment'] == "csv") ? "":""}</span>
				</div>
				<div class="details">
					<div class="customerDetails">
						<div class="to"></div>
						<div class="info"></div>
					</div>
					<div class="invnodate">
						<div class="invoice">${options['invoice']}</div>
						<div class="datetime">Date: ${options['date']}</div>
					</div>
				</div>
			`;

				return `<div class="header">${firstPage ? first : other}</div>`;

			}

			function itemsHeader() {

				let html = `
				<ul class="itemsHeader">
					<li class="sr">SR.#</li>
					<li class="description">DESCRIPTION</li>
					<li class="qty">QTY</li>
					<li class="unitprice">UNIT PRICE</li>
					<li class="total">TOTAL</li>
				</ul>
			`;

				return html;

			}

			function itemsContainer() {

				return `<div class="itemsContainer"></div>`

			}

			function item(sr, desc, qty, unit, unitp, total) {

				let html = `
				<ul class="item">
					<li class="sr">${sr}</li>
					<li class="description">${desc}</li>
					<li class="qty">${qty}<sub>${unit}</sub></li>
					<li class="unitprice">${numberWithCommas(unitp)}</li>
					<li class="total">${numberWithCommas(total)}</li>
				</ul>
			`;

				return html;

			}

			function totalSection(total, discount, discountPKR, advance, type, paid, creditLimit, currentCredit) {

				let html = `<div class="totalArea">`;

				if (parseFloat(discount) != 0 || parseFloat(advance) != 0 || parseFloat(discountPKR) != 0) {

					html += `
					<ul class="totalItem">
						<li class="totalTitle">SubTotal:</li>
						<li class="total">${numberWithCommas(total)}</li>
					</ul>
				`;

				}

				if (parseFloat(discount) != 0) {

					html += `
					<ul class="totalItem">
						<li class="totalTitle">Discount<sub>%</sub>: </li>
						<li class="total">${numberWithCommas(discount)}<sub>%</sub></li>
					</ul>
				`;

				}

				if (parseFloat(discountPKR) != 0) {

					html += `
					<ul class="totalItem">
						<li class="totalTitle">Discount <sub>PKR</sub> : </li>
						<li class="total">${numberWithCommas(discountPKR)}<sub>PKR</sub></li>
					</ul>
				`;

				}

				if (parseFloat(discount) != 0 || parseFloat(discountPKR) != 0) {

					html += `<ul class="totalItem">
							<li class="totalTitle">${(type == "csv") ? "Grand ":""}Total: </li>
							<li class="total">${numberWithCommas(total*(1 - (discount/100))-discountPKR)}<sub>PKR</sub></li>
						</ul>`;

				}

				if (((parseFloat(total) * (1 - (discount / 100)) - parseFloat(discountPKR)) + parseFloat(currentCredit)) > parseFloat(creditLimit)) {
					$(document.body).html(`<div style="display:flex; justify-content:center; align-items:center"><h1 style="text-align:center">Over Credit Limit!!!</h1></div>`);
				}

				if (parseFloat(discount) == 0 && parseFloat(discountPKR) == 0 && type == "csv") {

					html += `
					<ul class="totalItem">
						<li class="totalTitle">Grand Total: </li>
						<li class="total">${numberWithCommas(total)}<sub>PKR</sub></li>
					</ul>
				`;

				}

				if (type == "crv" || parseFloat(advance) != 0) {

					html += `
					<ul class="totalItem">
						<li class="totalTitle">Advance: </li>
						<li class="total">${numberWithCommas(advance)}<sub>PKR</sub></li>
					</ul>
				`;

				}

				if (type == "crv") {

					html += `<ul class="totalItem">
					<li class="totalTitle">Remaining Amount: </li>
					<li class="total">${numberWithCommas((total*(1 - (discount/100)))- discountPKR - advance)}<sub>PKR</sub></li>
				</ul>`;

				}

				if ((type == "csv") && (parseFloat(paid) != 0)) {

					html += `<ul class="totalItem">
							<li class="totalTitle">Cash Paid: </li>
							<li class="total">${numberWithCommas(paid)}<sub>PKR</sub></li>
						</ul>
						<ul class="totalItem">
							<li class="totalTitle">Balance: </li>
							<li class="total">${(advance != 0) ? numberWithCommas(paid-advance):numberWithCommas(paid-((total*(1 - (discount/100))) -discountPKR) )}<sub>PKR</sub></li>
						</ul>`;

				}

				html += "</div>";

				return html;

			}

			function footer(orderno, page) {

				return `
				
				<div class="footer">
				
					<div class="footertop" style="display: none">
						<div></div>

						<div class="asdas">We look forward to doing business with you!</div>
					</div>

					<div class="footerbottom">


                        <div class="orderNo" style="font-size: 6px;">Estimate</div>
						<div class="orderNo">${orderno}</div>
						<div class="pageNo">Page: ${page}</div>
					</div>
				
				</div>
				
			`;

			}

			$(".changeCompanyName").on("click", function() {

				$("page").each(function() {

					let logo = $(this).find(".logo");

					if (window.paymentType == "csv") {
						if (logo.find("img").hasClass("hidden")) {
							logo.find("img").removeClass("hidden");
							logo.find("span").addClass("hidden");
						} else {
							logo.find("img").addClass("hidden");
							logo.find("span").removeClass("hidden");
						}
					}

				})


			});

			$(document).ready(function() {
				setTimeout(function() {
					$("page").each(function() {
						let logo = $(this).find(".logo");

						if (logo.find("img").hasClass("hidden")) {
							logo.find("img").removeClass("hidden");
							logo.find("span").addClass("hidden");
						} else {
							logo.find("img").addClass("hidden");
							logo.find("span").removeClass("hidden");
						}
					});
				}, 500);
			});

			const numberWithCommas = (x) => {
				let parts = x.toString().split(".");
				parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				return parts.join(".");
			}
		</script>
	</body>

	</html>
<?php } ?>