<?php 

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	include('../../includes/SQL_CommonFunctions.inc');

	$orderno = trim($_GET['orderno']);
    $supptrans = trim($_GET['supptrans']);

	if(isset($_GET['json'])){
		
	    $SQL = "SELECT * FROM voucher WHERE id='".$orderno."'";
		$res = mysqli_query($db, $SQL);

		if(mysqli_num_rows($res) != 1){
			echo json_encode([
					'status' => 'error1'
				]);
			return;
		}

		$voucher = mysqli_fetch_assoc($res);
		
		$orignal = false;
		if(isset($_GET['orignal'])){
			$orignal = true;
		}
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $f->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");

		$voucher['amountInWords']=$f->format($voucher['amount']);


        echo json_encode($voucher);
		return;
		
	}
/*$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
$f->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");

echo $f->format((round(100422)));*/

?>

<!DOCTYPE html>
<head>
	<title>Shop Sale Print</title>
	<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
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
		  	height: 20.4cm;
		}
		page[size="A5"][layout="landscape"] {
		  width: 21cm;
		  height: 14.8cm;
		}
		page{
			padding: 10px 30px;
		}
	  	.nonprint{
	  		display: inline-block;
	  	}
		.pageHeader{
			display: flex;
		}
		.pageHeader .logo{
			flex: 1;
		}
		.pageHeader .logo img{
			height: 45px;
		}
		.pageHeader .title{
			display: flex;
			justify-content: flex-end;
			font-size: 2em;
			font-weight: bolder;
			color: #9a9a9a;
		}
		.details{
			display: flex;
			font-size: 14px;
		}
		.customerDetails{
			padding-top: 30px;
			flex:1;
		}
		.customerDetails .to{
			font-weight: bolder;
			font-weight: bolder;
			font-size: 14px;
		}
		.details .invoice{
			display: block;
		}
		.invnodate{
			flex: 1;
			font-size:14px;
			display: flex;
			flex-direction: column;
			align-items: flex-end;
		}
		.details2{
			padding-top: 10px;
			font-size: 14px;
		}
		.details2 table{
			width: 100%;
		}
		.details2 table th, .details2 table td{
			text-align: left;
		}
		.itemsHeader{
			margin-top: 10px;
			padding: 0;
			width: 100%;
			margin-bottom: 0;
			border: 1px solid black;
			font-size: 14px;
			display: flex;
		}
		.itemsHeader li{
			padding: 5px;
			font-weight: bold;
			display: inline-block;
			border-right: 1px solid black;
		}

		.item li{
			padding: 3px;
			display: inline-block;
			border-right: 1px solid black;
		}
		.footertop div{
			width: 100%;
			text-align: center;
		}

		.footer .orderNo{
			font-size: 10;
		}
		.blankSpace{
			width:100%;
		}
		.totalItem li{
			padding: 3px;
			display: inline-block;
			border-right: 1px solid black;
		}
		.totalItem .total{
			//border-bottom: 1px solid black;
		}
		.sahname{
			font-size: 2em;
			font-weight: bolder;
		}
		@media print {
		  	.nonprint, .hidden-print{
		  		display: none;
		  	}
		}
	</style>
</head>
<body>
	<button class="btn btn-primary hidden-print" onclick="window.print()" style="text-align: right !important; position: fixed; z-index: 200; right: 0; top:50px;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print</button>
	 <button class="btn btn-primary hidden-print" onclick="window.location.href='/sahamid'" style="text-align: right !important; position: fixed; z-index: 200; right: 0; top:100px;"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Home Page</button>

    <div class="pagesContainer"></div>
	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			window.paymentType = "";
			let pageCount = 1;
			let margins   = 20;
			
			$.get("voucherPrint.php?orderno=<?php echo $_GET['orderno']; ?>&json",function(res, status){
				
				res = JSON.parse(res);
				
				let currentPage = "page-"+pageCount;
				
				$(".pagesContainer").append(page(currentPage));
				
				window.type = res.type;


				$("."+currentPage).append(header(res, true));

				

				let height = $("."+currentPage).height();
				height -= $("."+currentPage+" .header").height();
				height -= margins;
				
				if(height < 0){
					
					$("."+currentPage+" .totalArea").remove();
					
					pageCount += 1;
					currentPage = "page-"+pageCount;
					$(".pagesContainer").append(page(currentPage));
					$("."+currentPage).append(header(res));

				}
				

				height = $("."+currentPage).height();
				height -= $("."+currentPage+" .header").height();
				height -= margins;
				
				$("."+currentPage+" .totalArea").after(`<div class="blankSpace" style="height: ${height}px"></div>`);
				
				
			});
			
			
		});
		
		function page(currentPage){
			return `<page size="A4" layout="landscape" class="${currentPage}"></page>`;
		}
		
		function header(options, firstPage=false){
            let dateTime=options.updated_at;
            let dateTimeParts= dateTime.split(/[- :]/); // regular expression split that creates array with: year, month, day, hour, minutes, seconds values
            dateTimeParts[1]--; // monthIndex begins with 0 for January and ends with 11 for December so we need to decrement by one

            const dateObject = new Date(...dateTimeParts).toLocaleDateString();

			let first = `
				<div class="pageHeader">
					<div class="title">${(window.type == 604) ? "<h2>Receipt Voucher</h2>":"<h2>Payment Voucher</h2>"}

                </div>

				</div>
				<div class="details">
					<div class="customerDetails">
						<div class="to">${(window.type == 604) ? "<h4>Received From</h4>":"<h4>Paid To</h4>"}</div>
						<div class="info" style="width:100%; border-style:dashed; border-width: 1px;"><h3>${options['partyname']}</h3></div>
					</div>
					<div class="invnodate">

                        <?php if ($_GET['duplicate']==1) echo'<h3 style="text-align: right;">Duplicate</h3>'; ?>
						<div class="invoice"><h3>${options['voucherno']}</h3></div>
						<div class="datetime">Date: ${dateObject}</div>
					</div>
				</div>
				<div class="details2" >
                    <hr/>
					<table style="width:100%; border-style:dashed; border-width: 1px;">
						<thead>

                        <tr>
								<td>${options['instrumentType']} <b>${options['instrumentNo']}</b> dated ${options['instrumentDate']}</td>


                        </tr>
                        <tr>

								<tr><td>${options['ref']}</td></tr>
                                <td>${options['description']}</tr>


                        </tr>
						</thead>
						<tbody>

						</tbody>
					</table>
                    <hr/>
                    <table style="width:100%; border-style:dashed; border-width: 1px;">
                    <tr>
								<td><b>A sum of PKR ${options['amountInWords']} only</b></td>


                        </tr>
                    <tr>
								<td>Rs. ${numberWithCommas(options['amount'])}/-</td>

							</tr>
                    </table>
                    <hr/>

                    <table>
                        <tr style="font-weight:bold;"><td>Date</td><td>Market Slip #</td><td>Amount PKR</td><td>This Voucher</td><td>Paid PKR</td></tr>

                        <?php
                            $SQL = "SELECT *,suppallocs.amt as thisvoucher  FROM suppallocs INNER JOIN supptrans s on suppallocs.transid_allocto = s.id WHERE transid_allocfrom='".$supptrans."'";
                            $res = mysqli_query($db, $SQL);

                            while ($row=mysqli_fetch_assoc($res)){
                                echo "<tr><td>".date('d/m/Y',strtotime($row['trandate']))."</td><td>"."MPIW-".$row['transno']."</td><td>".locale_number_format($row['ovamount'])."</td><td>".locale_number_format($row['thisvoucher'])."</td><td>".locale_number_format($row['alloc'])."</td></tr>";
                            }
                        ?>
                    </table>
                    <div style="width:100%; border-style:dashed; border-width: 1px;">
                    ${(window.type == 604) ? "<b>Accountant</b> <br/><br/>":"<table><tr><td>Accountant</td><td>Received By</td></tr></table><br/><br/><br/>"}
                    </div>
				</div>
                <span style="font-size:8px;align: right;">Created By: ${options['user_name']} </span>
			`;
			
		/*	let other = `
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
			`;*/
		
			return `<div class="header">${firstPage ? first : other}</div>`;
			
		}
		


		


		

		
		$(".changeCompanyName").on("click", function(){
			
			$("page").each(function(){
			
				let logo = $(this).find(".logo");
				
				if(window.paymentType == "csv"){
					if(logo.find("img").hasClass("hidden")){
						logo.find("img").removeClass("hidden");
						logo.find("span").addClass("hidden");
					}else{
						logo.find("img").addClass("hidden");
						logo.find("span").removeClass("hidden");
					}
				}
				
			})
			
			
		});
		
		$(document).ready(function(){
			setTimeout(function(){
				$("page").each(function(){
					let logo = $(this).find(".logo");
					
					if(logo.find("img").hasClass("hidden")){
						logo.find("img").removeClass("hidden");
						logo.find("span").addClass("hidden");
					}else{
						logo.find("img").addClass("hidden");
						logo.find("span").removeClass("hidden");
					}
				});
			},500);
		});
		
		const numberWithCommas = (x) => {
			let parts = x.toString().split(".");
			parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			return parts.join(".");
		}
	</script>
    <script>
        window.location.hash="no-back-button";
        window.location.hash="Again-No-back-button";//again because google chrome don't insert first hash into history
        window.onhashchange=function(){window.location.hash="no-back-button";}
    </script>
</body>
</html>